<?php
ini_set('display_errors', 0); // Désactivé en production pour ne pas casser le JSON
header('Content-Type: application/json');
error_reporting(E_ALL);
session_start();

define('UPLOAD_DIR', __DIR__ . '/../DOCUMENTS_UPLOADEDS/');
require_once "db.php";

$raw_data = file_get_contents('php://input');
$data = [];
if (!empty($raw_data)) {
    $data = json_decode($raw_data, true);
}

$step = 0;

// Détection de l'étape (FormData ou JSON)
if (isset($_POST['step'])) {
    $step = (int)$_POST['step'];
} else if (isset($data['step'])) {
    $step = (int)$data['step'];
}

if ($step === 0) {
    echo json_encode(['success' => false, 'message' => 'Étape de traitement manquante ou invalide.']);
    exit;
}

$stmt = null; 

switch ($step) {
    case 2:
        $p = $data['data'] ?? [];
        try {
            // --- 1. INSERTION OU MISE À JOUR PATIENT ---
            $sqlPatient = "INSERT INTO Patient 
                (Num_SecuSocial_Patient, Civilité_Patient, Nom_Naissance, Nom_Epouse, Prénom_Patient, Date_Naissance, Num_Adresse, Rue_Adresse, Code_Postal, Ville_Adresse, Adresse_Mail, Telephone_Patient) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE Nom_Epouse=VALUES(Nom_Epouse), Telephone_Patient=VALUES(Telephone_Patient), Adresse_Mail=VALUES(Adresse_Mail)";

            if (!($stmt = $conn->prepare($sqlPatient))) {
                echo json_encode(['success' => false, 'message' => 'Erreur de préparation Patient : ' . $conn->error]);
                exit;
            }

            $civilité = (int)$p['civilité'];
            $stmt->bind_param(
                'sissssisssss',
                $p['numSecuSocial'],
                $civilité,
                $p['nomNaissance'],
                $p['nomEpouse'],
                $p['prenom'],
                $p['datenaissance'],
                $p['numAddresse'],
                $p['rueAddresse'],
                $p['cp'],
                $p['ville'],
                $p['mail'],
                $p['telephone']
            );

            if (!$stmt->execute()) {
                echo json_encode(['success' => false, 'message' => 'Erreur d\'exécution Patient : ' . $stmt->error]);
                exit;
            }
            $stmt->close();

            // --- 2. INSERTION COUVERTURE SOCIALE ---
            $sqlCouverture = "INSERT INTO CouvertureSocial 
                (Numero_Sec_Social, Nom_OrganismeSecuSocial, Patient_Assuré, Patient_ADL, Nom_Mutuelle, Numéro_Adhérent) 
                VALUES (?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE Nom_Mutuelle=VALUES(Nom_Mutuelle), Numéro_Adhérent=VALUES(Numéro_Adhérent)";

            if (!($stmt = $conn->prepare($sqlCouverture))) {
                echo json_encode(['success' => false, 'message' => 'Erreur de préparation Couverture : ' . $conn->error]);
                exit;
            }

            $ADL = (int)$p['isADL'];
            $Assure = (int)$p['isAssure'];
            $stmt->bind_param(
                'ssiiss',
                $p['numSecuSocial'],
                $p['nomOrgaSocial'],
                $Assure,
                $ADL,
                $p['nomMutuelle'],
                $p['numAdherent']
            );

            if ($stmt->execute()) {
                $_SESSION['NumSecu'] = $p['numSecuSocial'];
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur d\'exécution Couverture : ' . $stmt->error]);
            }
            $stmt->close();
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur du serveur (Etape 2) : ' . $e->getMessage()]);
            exit;
        }
        break;

    case 3:
        $p = $data['data'] ?? [];
        try {
            if (!isset($_SESSION['NumSecu'])) {
                echo json_encode(['success' => false, 'message' => 'ID Patient manquant en session.']);
                exit;
            }
            $NumSecu = $_SESSION['NumSecu'];

            if (!isset($p['Chambre']) || empty($p['Chambre'])) {
                echo json_encode(['success' => false, 'message' => 'Le numéro de chambre est manquant dans la requête.']);
                exit;
            }

            $TypeHospi = (int)$p['TypeHospi'];
            $Chambre = (int)$p['Chambre']; // Conversion forcée en entier (ID)
            $Medecin = (int)$p['Medecin'];

            // 🎯 VERIFICATION SÉCURISÉE : La chambre existe-t-elle vraiment dans la table 'chambre' ?
            $checkChambre = $conn->prepare("SELECT NumeroChambre FROM chambre WHERE NumeroChambre = ?");
            $checkChambre->bind_param("i", $Chambre);
            $checkChambre->execute();
            $checkChambre->store_result();

            if ($checkChambre->num_rows === 0) {
                echo json_encode(['success' => false, 'message' => "La chambre ID [ $Chambre ] n'existe pas en BDD."]);
                $checkChambre->close();
                exit;
            }
            $checkChambre->close();

            // --- INSERTION HOSPITALISATION ---
            $sqlHospi = "INSERT INTO Hospitalisation 
                (Date_Hospitalisation, Heure_Hospitalisation, TypeHospitalisation, ChambreOccupé, Medecin_En_Charge, ID_Patient) 
                VALUES (?, ?, ?, ?, ?, ?)";

            if (!($stmt = $conn->prepare($sqlHospi))) {
                echo json_encode(['success' => false, 'message' => 'Erreur de préparation Hospitalisation : ' . $conn->error]);
                exit;
            }

            $stmt->bind_param(
                'ssiiis',
                $p['DateHospi'],
                $p['HeureHospi'],
                $TypeHospi,
                $Chambre,
                $Medecin,
                $NumSecu
            );

            if ($stmt->execute()) {
                $_SESSION['ID_Hospitalisation'] = $conn->insert_id;
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur d\'exécution Hospitalisation : ' . $stmt->error]);
            }
            $stmt->close();
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur du serveur (Etape 3) : ' . $e->getMessage()]);
            exit;
        }
        break;

    case 4:
        $p = $data['data'] ?? [];
        $personneAprevenir = $p['PersonnePrev'] ?? [];
        $personneDeConfiance = $p['PersonneConf'] ?? null;
        $responsableLegal = $p['ResponsableLegal'] ?? null;

        try {
            if (!isset($_SESSION['NumSecu'])) {
                echo json_encode(['success' => false, 'message' => 'Patient manquant en session.']);
                exit;
            }

            // --- 1. PERSONNE À PRÉVENIR ---
            $sqlPP = "INSERT INTO Personne_Prevenir 
                (Nom_Pers, Prénom_Pers, Telephone_Pers, Num_Adresse, Rue_Adresse, Ville_Adresse, Code_Postal_Pers) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

            if (!($stmt = $conn->prepare($sqlPP))) {
                echo json_encode(['success' => false, 'message' => 'Erreur préparation PP : ' . $conn->error]);
                exit;
            }

            $stmt->bind_param(
                'ssssssi',
                $personneAprevenir['Nom'],
                $personneAprevenir['Prenom'],
                $personneAprevenir['Telephone'],
                $personneAprevenir['NumAdresse'],
                $personneAprevenir['RueAdresse'],
                $personneAprevenir['Ville'],
                $personneAprevenir['CP']
            );

            if (!$stmt->execute()) {
                echo json_encode(['success' => false, 'message' => 'Erreur exécution PP : ' . $stmt->error]);
                exit;
            }
            $_SESSION['ID_Personne_Prevenir'] = $conn->insert_id;
            $stmt->close();

            // --- 2. PERSONNE DE CONFIANCE ---
            if ($personneDeConfiance !== null && !empty($personneDeConfiance['Nom'])) {
                if (!($stmt = $conn->prepare($sqlPP))) {
                    echo json_encode(['success' => false, 'message' => 'Erreur préparation PC : ' . $conn->error]);
                    exit;
                }

                $stmt->bind_param(
                    'ssssssi',
                    $personneDeConfiance['Nom'],
                    $personneDeConfiance['Prenom'],
                    $personneDeConfiance['Telephone'],
                    $personneDeConfiance['NumAdresse'],
                    $personneDeConfiance['RueAdresse'],
                    $personneDeConfiance['Ville'],
                    $personneDeConfiance['CP']
                );

                if (!$stmt->execute()) {
                    echo json_encode(['success' => false, 'message' => 'Erreur exécution PC : ' . $stmt->error]);
                    exit;
                }
                $_SESSION['ID_Personne_Confiance'] = $conn->insert_id;
                $stmt->close();
            } else {
                $_SESSION['ID_Personne_Confiance'] = $_SESSION['ID_Personne_Prevenir'];
            }

            // --- 3. RESPONSABLE LÉGAL ---
            if ($responsableLegal !== null && !empty($responsableLegal['Nom'])) {
                $sqlResp = "INSERT INTO Responsable 
                    (Nom_Responsable, Prenom_Responsable, Telephone_Responsable, AdresseMail_Responsable, Num_Adresse_Responsable, Rue_Responsable, Ville_Responsable, Code_Postal_Responsable) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                if (!($stmt = $conn->prepare($sqlResp))) {
                    echo json_encode(['success' => false, 'message' => 'Erreur préparation RESP : ' . $conn->error]);
                    exit;
                }

                $stmt->bind_param(
                    'sssssiss',
                    $responsableLegal['Nom'],
                    $responsableLegal['Prenom'],
                    $responsableLegal['Telephone'],
                    $responsableLegal['Mail'],
                    $responsableLegal['NumAdresse'],
                    $responsableLegal['RueAdresse'],
                    $responsableLegal['Ville'],
                    $responsableLegal['CP']
                );

                if (!$stmt->execute()) {
                    echo json_encode(['success' => false, 'message' => 'Erreur exécution RESP : ' . $stmt->error]);
                    exit;
                }
                $ID_RESP = $conn->insert_id;
                $stmt->close();

                $sqlTutelle = "INSERT INTO SousTutelleDe (ID_Responsable, ID_Patient) VALUES (?, ?)";
                if (!($stmt = $conn->prepare($sqlTutelle))) {
                    echo json_encode(['success' => false, 'message' => 'Erreur préparation Tutelle : ' . $conn->error]);
                    exit;
                }

                $stmt->bind_param('is', $ID_RESP, $_SESSION['NumSecu']);
                $stmt->execute();
                $stmt->close();
            }

            echo json_encode(['success' => true]);
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur du serveur (Etape 4) : ' . $e->getMessage()]);
            exit;
        }
        break;

    case 5:
        if (!isset($_SESSION['NumSecu'])) {
            echo json_encode(['success' => false, 'message' => 'Patient manquant en session.']);
            exit;
        }
        $NumSecuPatient = $_SESSION['NumSecu'];

        $colonnesBDD = [
            'Numéro_SecSocial_Document', 'Carte_Identité', 'Carte_Vitale', 'Carte_mutuelle', 'Livret_Famille', 'Autorisation_soin', 'Decision_juge'
        ];

        $fieldMapping = [
            'CarteID'       => 1,
            'CarteVitale'   => 2,
            'CarteMutuelle' => 3,
            'LivretFamille' => 4,
            'AutoSoin'      => 5,
            'DecisionJuge'  => 6
        ];

        try {
            if (empty($_FILES)) {
                echo json_encode(['success' => false, 'message' => 'Aucun fichier reçu (Vérifiez la config post_max_size du serveur).']);
                exit;
            }

            $sqlFiles = "INSERT INTO PiecesJoints (" . implode(', ', $colonnesBDD) . ") VALUES (?, ?, ?, ?, ?, ?, ?)";
            if (!($stmt = $conn->prepare($sqlFiles))) {
                echo json_encode(['success' => false, 'message' => 'Erreur préparation fichiers : ' . $conn->error]);
                exit;
            }

            $null = null; 
            $stmt->bind_param('sbbbbbb', $NumSecuPatient, $null, $null, $null, $null, $null, $null);

            foreach ($_FILES as $fieldName => $file) {
                if (isset($fieldMapping[$fieldName]) && $file['error'] === UPLOAD_ERR_OK) {
                    $index = $fieldMapping[$fieldName];
                    
                    $fp = fopen($file['tmp_name'], "r");
                    while (!feof($fp)) {
                        $stmt->send_long_data($index, fread($fp, 8192));
                    }
                    fclose($fp);
                }
            }

            if (!$stmt->execute()) {
                echo json_encode(['success' => false, 'message' => 'Erreur d\'insertion fichiers : ' . $stmt->error]);
                exit;
            }
            $stmt->close();

            // --- FINALISATION : PRÉ-ADMISSION ---
            if (!isset($_SESSION['ID_Hospitalisation']) || !isset($_SESSION['ID_Personne_Prevenir'])) {
                echo json_encode(['success' => false, 'message' => 'Informations d\'étapes précédentes manquantes.']);
                exit;
            }

            $sqlPreAdmi = "INSERT INTO PreAdmission (Patient_PreAdmi, Hospitalisation_PreAdmi, Personne_aprev, Personne_deconf) VALUES (?, ?, ?, ?)";
            if (!($stmt = $conn->prepare($sqlPreAdmi))) {
                echo json_encode(['success' => false, 'message' => 'Erreur préparation PreAdmi : ' . $conn->error]);
                exit;
            }

            $stmt->bind_param(
                'siii',
                $_SESSION['NumSecu'],
                $_SESSION['ID_Hospitalisation'],
                $_SESSION['ID_Personne_Prevenir'],
                $_SESSION['ID_Personne_Confiance']
            );

            if (!$stmt->execute()) {
                echo json_encode(['success' => false, 'message' => 'Erreur exécution PreAdmi : ' . $stmt->error]);
                exit;
            }
            $stmt->close();

            // Nettoyage session
            unset($_SESSION['NumSecu'], $_SESSION['ID_Hospitalisation'], $_SESSION['ID_Personne_Prevenir'], $_SESSION['ID_Personne_Confiance']);

            echo json_encode(['success' => true, 'message' => 'Pré-admission enregistrée avec succès !']);
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur du serveur (Etape 5) : ' . $e->getMessage()]);
            exit;
        }
        break;
}

if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}