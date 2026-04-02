<?php
session_start();
require_once "../INCLUDES/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pa = intval($_POST['id_pa']);
    $type = $_POST['type'];
    $pk = $_POST['pk'] ?? null;

    // Détermination du dossier de retour (par défaut vide ou dossier parent)
    // Dans votre formulaire, ajoutez <input type="hidden" name="from" value="ADMIN"> ou "USER"
    $from = $_POST['from'] ?? '';
    $redirect_url = "edit_admission.php?id=$id_pa&success=1";

    if ($from === 'ADMIN') {
        $redirect_url = "../ADMIN/edit-admission.php?id=$id_pa&success=1";
    } elseif ($from === 'SECRETARY') {
        $redirect_url = "../SECRETARY/edit-admission.php?id=$id_pa&success=1";
    }

    switch ($type) {
        case 'patient':
            $sql = "UPDATE patient SET 
                    Civilité_Patient = ?, Nom_Naissance = ?, Nom_Epouse = ?, 
                    Prénom_Patient = ?, Date_Naissance = ?, Adresse_Mail = ?, 
                    Telephone_Patient = ?, Num_Adresse = ?, Rue_Adresse = ?, 
                    Ville_Adresse = ?, Code_Postal = ? 
                    WHERE Num_SecuSocial_Patient = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "isssssssssss",
                $_POST['Civilité_Patient'],
                $_POST['Nom_Naissance'],
                $_POST['Nom_Epouse'],
                $_POST['Prénom_Patient'],
                $_POST['Date_Naissance'],
                $_POST['Adresse_Mail'],
                $_POST['Telephone_Patient'],
                $_POST['Num_Adresse'],
                $_POST['Rue_Adresse'],
                $_POST['Ville_Patient'],
                $_POST['Code_Postal'],
                $pk
            );
            break;

        case 'hospitalisation':
            $sql = "UPDATE hospitalisation SET 
                    TypeHospitalisation = ?, Date_Hospitalisation = ?, 
                    Heure_Hospitalisation = ?, Medecin_En_Charge = ? 
                    WHERE ID_Hospitalisation = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "issii",
                $_POST['TypeHospitalisation'],
                $_POST['Date_Hospitalisation'],
                $_POST['Heure_Hospitalisation'],
                $_POST['Medecin_En_Charge'],
                $pk
            );
            break;

        case 'couverture':
            $sql = "UPDATE couverturesocial SET 
                    Nom_OrganismeSecuSocial = ?, Nom_Mutuelle = ?, 
                    Numéro_Adhérent = ?, Patient_Assuré = ?, Patient_ADL = ? 
                    WHERE Numero_Sec_Social = ?";
            $stmt = $conn->prepare($sql);

            // Correction ici : "sssiii" (6 caractères pour 6 variables)
            // s = string, i = integer
            $stmt->bind_param(
                "sssiii",
                $_POST['Nom_OrganismeSecuSocial'],
                $_POST['Nom_Mutuelle'],
                $_POST['Numéro_Adhérent'],
                $_POST['Patient_Assuré'],
                $_POST['Patient_ADL'],
                $pk
            );
            break;

        case 'entourage_full':
            // 1. Personne à Prévenir (PP)
            $stmt1 = $conn->prepare("UPDATE personne_prevenir SET 
                Nom_Pers = ?, Prénom_Pers = ?, Telephone_Pers = ?, 
                Num_Adresse = ?, Rue_Adresse = ?, Ville_Adresse = ?, Code_Postal_Pers = ? 
                WHERE ID_Personne = ?");
            $stmt1->bind_param(
                "sssssssi",
                $_POST['nom_pp'],
                $_POST['prenom_pp'],
                $_POST['tel_pp'],
                $_POST['num_pp'],
                $_POST['rue_pp'],
                $_POST['ville_pp'],
                $_POST['cp_pp'],
                $_POST['pk_pp']
            );
            $stmt1->execute();

            // 2. Personne de Confiance (PC)
            $stmt2 = $conn->prepare("UPDATE personne_prevenir SET 
                Nom_Pers = ?, Prénom_Pers = ?, Telephone_Pers = ?, 
                Num_Adresse = ?, Rue_Adresse = ?, Ville_Adresse = ?, Code_Postal_Pers = ? 
                WHERE ID_Personne = ?");
            $stmt2->bind_param(
                "sssssssi",
                $_POST['nom_pc'],
                $_POST['prenom_pc'],
                $_POST['tel_pc'],
                $_POST['num_pc'],
                $_POST['rue_pc'],
                $_POST['ville_pc'],
                $_POST['cp_pc'],
                $_POST['pk_pc']
            );
            $stmt2->execute();

            // 3. Responsable Légal
            if (isset($_POST['Nom_Responsable'])) {
                $stmt3 = $conn->prepare("UPDATE responsable r
                    INNER JOIN soustutellede st ON r.ID_Responsable = st.ID_Responsable
                    SET r.Nom_Responsable = ?, r.Prenom_Responsable = ?, 
                        r.Telephone_Responsable = ?, r.AdresseMail_Responsable = ?,
                        r.Num_Adresse_Responsable = ?, r.Rue_Responsable = ?, 
                        r.Ville_Responsable = ?, r.Code_Postal_Responsable = ?
                    WHERE st.ID_Patient = (SELECT Patient_PreAdmi FROM preadmission WHERE Id_PreAdmin = ?)");
                $stmt3->bind_param(
                    "ssssssssi",
                    $_POST['Nom_Responsable'],
                    $_POST['Prenom_Responsable'],
                    $_POST['Telephone_Responsable'],
                    $_POST['AdresseMail_Responsable'],
                    $_POST['Num_Adresse_Responsable'],
                    $_POST['Rue_Responsable'],
                    $_POST['Ville_Responsable'],
                    $_POST['Code_Postal_Responsable'],
                    $id_pa
                );
                $stmt3->execute();
            }
            header("Location: $redirect_url");
            exit;

        case 'documents':
            $doc_fields = ['Carte_Identité', 'Carte_Vitale', 'Carte_mutuelle', 'Livret_Famille', 'Autorisation_soin', 'Decision_juge'];
            foreach ($doc_fields as $field) {
                if (!empty($_FILES[$field]['tmp_name'])) {
                    $blob = file_get_contents($_FILES[$field]['tmp_name']);
                    $stmtDoc = $conn->prepare("UPDATE piecesjoints SET $field = ? WHERE Numéro_SecSocial_Document = ?");
                    $null = NULL;
                    $stmtDoc->bind_param("bs", $null, $pk);
                    $stmtDoc->send_long_data(0, $blob);
                    $stmtDoc->execute();
                }
            }
            header("Location: $redirect_url");
            exit;
    }

    // Exécution pour les cas simples
    if (isset($stmt) && $stmt->execute()) {
        header("Location: $redirect_url");
    } else {
        echo "Erreur lors de la mise à jour : " . $conn->error;
    }
}
