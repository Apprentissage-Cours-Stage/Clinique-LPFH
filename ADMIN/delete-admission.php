<?php
session_start();
require_once "../INCLUDES/db.php";

$id_pa = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_pa <= 0) {
    header("Location: list-admission.php?error=id_invalide");
    exit;
}

try {
    $conn->begin_transaction();

    // 1. Collecter toutes les informations liées avant de commencer les suppressions
    $sql_info = "SELECT Patient_PreAdmi, Hospitalisation_PreAdmi, Personne_aprev, Personne_deconf 
                 FROM preadmission WHERE Id_PreAdmin = ?";
    $stmt_info = $conn->prepare($sql_info);
    $stmt_info->bind_param("i", $id_pa);
    $stmt_info->execute();
    $data = $stmt_info->get_result()->fetch_assoc();

    if (!$data) throw new Exception("Dossier introuvable.");

    $num_secu = $data['Patient_PreAdmi'];
    $id_hosp = $data['Hospitalisation_PreAdmi'];
    $id_pp = $data['Personne_aprev'];
    $id_pc = $data['Personne_deconf'];

    // 2. Supprimer la Pré-admission en premier (libère les liens)
    $conn->query("DELETE FROM preadmission WHERE Id_PreAdmin = $id_pa");

    // 3. Supprimer l'Hospitalisation
    if ($id_hosp) {
        $conn->query("DELETE FROM hospitalisation WHERE ID_Hospitalisation = $id_hosp");
    }

    // 4. Vérifier si le patient a d'autres dossiers en cours
    $check_other = $conn->query("SELECT COUNT(*) as nb FROM preadmission WHERE Patient_PreAdmi = '$num_secu'");
    $has_other_pa = ($check_other->fetch_assoc()['nb'] > 0);

    if (!$has_other_pa) {
        // --- LE PATIENT N'A PLUS D'AUTRES DOSSIERS : ON NETTOIE TOUT ---

        // A. Supprimer les documents (Pièces Jointes)
        $conn->query("DELETE FROM piecesjoints WHERE Numéro_SecSocial_Document = '$num_secu'");

        // B. Supprimer la Couverture Sociale
        $conn->query("DELETE FROM couverturesocial WHERE Numero_Sec_Social = '$num_secu'");

        // C. Supprimer le Responsable Légal
        // On cherche d'abord s'il existe un responsable lié
        $res_resp = $conn->query("SELECT ID_Responsable FROM soustutellede WHERE ID_Patient = '$num_secu'");
        if ($row_r = $res_resp->fetch_assoc()) {
            $id_resp = $row_r['ID_Responsable'];
            // Supprimer le lien de tutelle
            $conn->query("DELETE FROM soustutellede WHERE ID_Patient = '$num_secu'");
            // Supprimer le responsable
            $conn->query("DELETE FROM responsable WHERE ID_Responsable = $id_resp");
        }

        // D. Supprimer les Personnes de l'entourage (PP et PC)
        // On vérifie qu'elles ne sont plus utilisées par PERSONNE d'autre avant de supprimer
        $contacts_a_verifier = array_filter([$id_pp, $id_pc]); // Enlève les IDs nuls
        
        foreach ($contacts_a_verifier as $id_pers) {
            $check_usage = $conn->query("SELECT COUNT(*) as nb FROM preadmission WHERE Personne_aprev = $id_pers OR Personne_deconf = $id_pers");
            if ($check_usage->fetch_assoc()['nb'] == 0) {
                $conn->query("DELETE FROM personne_prevenir WHERE ID_Personne = $id_pers");
            }
        }

        // E. Enfin, supprimer le Patient
        $conn->query("DELETE FROM patient WHERE Num_SecuSocial_Patient = '$num_secu'");
    }

    $conn->commit();
    header("Location: list-admission.php?success=delete");

} catch (Exception $e) {
    $conn->rollback();
    die("Erreur fatale lors de la suppression : " . $e->getMessage());
}