<?php
require_once "db.php"; // Connexion PDO

$conn->begin_transaction();
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Aucune donnée reçue']);
    exit;
}

try {
    // --- Patient ---
    $stmt = $conn->prepare("INSERT INTO Patient 
        (Num_SecuSocial_Patient, Civilité_Patient, Nom_Naissance, Nom_Epouse, Prénom_Patient, Date_Naissance, Num_Adresse, Rue_Adresse, Code_Postal, Ville_Adresse, Adresse_Mail, Telephone_Patient)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param(
        "sissssisssii",
        $data['numSecuSocial'],
        $data['civilité'],
        $data['nomNaissance'],
        $data['nomEpouse'],
        $data['prenom'],
        $data['datenaissance'],
        $data['NumAdresse'],
        $data['Rue'],
        $data['CP'],
        $data['Ville'],
        $data['Email'],
        $data['Telephone']
    );
    $stmt->execute();
    $stmt->close();

    // --- Couverture Social ---
    $stmt = $conn->prepare("INSERT INTO CouvertureSocial
        (Numero_Sec_Social, Nom_OrganismeSecuSocial, Patient_Assuré, Patient_ADL, Nom_Mutuelle, Numéro_Adhérent)
        VALUES (?,?,?,?,?,?)");
    $stmt->bind_param(
        "ssiiis",
        $data['numSecuSocial'],
        $data['nomOrgaSocial'],
        $data['isAssure'],
        $data['isADL'],
        $data['nomMutuelle'],
        $data['numAdherent']
    );
    $stmt->execute();
    $stmt->close();

    // --- Personne à prévenir ---
    $stmt = $conn->prepare("INSERT INTO Personne_prevenir
        (Nom_Pers, Prénom_Pers, Telephone_Pers, Num_Adresse, Rue_Adresse, Ville_Adresse, Code_Postal_Pers)
        VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param(
        "sssssss",
        $data['NomPP'],
        $data['PrenomPP'],
        $data['TelPP'],
        $data['NumAdressePP'],
        $data['RuePP'],
        $data['VillePP'],
        $data['CPPP']
    );
    $stmt->execute();
    $idPrev = $conn->insert_id;
    $stmt->close();

    $idConf = $idPrev;
    if (!empty($data['NomPC']) || !empty($data['PrenomPC']) || !empty($data['TelPC'])) {
        // --- Personne de confiance ---
        $stmt = $conn->prepare("INSERT INTO Personne_prevenir
        (Nom_Pers, Prénom_Pers, Telephone_Pers, Num_Adresse, Rue_Adresse, Ville_Adresse, Code_Postal_Pers)
        VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param(
            "sssssss",
            $data['NomPC'],
            $data['PrenomPC'],
            $data['TelPC'],
            $data['NumAdressePC'],
            $data['RuePC'],
            $data['VillePC'],
            $data['CPPC']
        );
        $stmt->execute();
        $idConf = $conn->insert_id;
        $stmt->close();
    }

    $idResp = null;
    if (!empty($data['NomResp']) || !empty($data['PrenomResp']) || !empty($data['TelResp'])) {
        // --- Responsable ---
        $stmt = $conn->prepare("INSERT INTO Responsable
        (Nom_Responsable, Prenom_Responsable, Telephone_Responsable, AdresseMail_Responsable, Num_Adresse_Responsable, Rue_Responsable, Ville_Responsable, Code_Postal_Responsable)
        VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param(
            "ssssssss",
            $data['NomResp'],
            $data['PrenomResp'],
            $data['TelResp'],
            $data['MailResp'],
            $data['NumAdresseResp'],
            $data['RueResp'],
            $data['VilleResp'],
            $data['CPResp']
        );
        $stmt->execute();
        $idResp = $conn->insert_id;
        $stmt->close();
    }

    // --- Hospitalisation ---
    $stmt = $conn->prepare("INSERT INTO Hospitalisation
        (Date_Hospitalisation, Heure_Hospitalisation, TypeHospitalisation, ChambreOccupé, Medecin_En_Charge, ID_Patient)
        VALUES (?,?,?,?,?,?)");
    $stmt->bind_param(
        "ssiiii",
        $data['date'],
        $data['heure'],
        $data['typeHosp'],
        $data['chambre'],
        $data['medecin'],
        $data['numSecuSocial']
    );
    $stmt->execute();
    $idHospi = $conn->insert_id;
    $stmt->close();

    // --- PreAdmission ---
    $stmt = $conn->prepare("INSERT INTO PreAdmission
        (Patient_PreAdmi, Hospitalisation_PreAdmi, Personne_aprev, Personne_deconf)
        VALUES (?,?,?,?)");
    $stmt->bind_param(
        "siii",
        $data['numSecuSocial'],
        $idHospi,
        $idPrev,
        $idConf
    );
    $stmt->execute();
    $stmt->close();

    // --- SousTutelle ---
    if ($idResp !== null) {
        $stmt = $conn->prepare("INSERT INTO SousTutellede (ID_Responsable, ID_Patient) VALUES (?,?)");
        $stmt->bind_param("is", $idResp, $data['numSecuSocial']);
        $stmt->execute();
        $stmt->close();
    }

    // --- 9. PiecesJoints (blobs) ---
    $stmt = $conn->prepare("INSERT INTO PiecesJoints
        (Numéro_SecSocial_Document, Carte_Identité, Carte_Vitale, Carte_mutuelle, Livret_Famille, Autorisation_soin, Decision_juge)
        VALUES (?,?,?,?,?,?,?)");
    
    $stmt->send_long_data(1, $data['CI'] ?? null);
    $stmt->send_long_data(2, $data['CV'] ?? null);
    $stmt->send_long_data(3, $data['CM'] ?? null);
    $stmt->send_long_data(4, $data['LF'] ?? null);
    $stmt->send_long_data(5, $data['AS'] ?? null);
    $stmt->send_long_data(6, $data['DJ'] ?? null);
    
    $stmt->bind_param("sssssss",
        $data['numSecuSocial'],
        $data['CI'] ?? null,
        $data['CV'] ?? null,
        $data['CM'] ?? null,
        $data['LF'] ?? null,
        $data['AS'] ?? null,
        $data['DJ'] ?? null
    );
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
