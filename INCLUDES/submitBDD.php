<?php
ini_set('display_errors', 1);
header('Content-Type: application/json');
error_reporting(E_ALL);
session_start();
define('UPLOAD_DIR', __DIR__ . '/../DOCUMENTS_UPLOADEDS/');
require_once "db.php";
$raw_data = file_get_contents('php://input');
if (!empty($raw_data)) {
    $data = json_decode($raw_data, true);
}

// Nouveau code pour supporter FormData (fichiers)
if (isset($_POST['step'])) {
    // Si la requête est FormData (upload de fichiers, généralement Étape 5), l'étape est dans $_POST.
    $step = (int)$_POST['step'];
} else if (isset($data['step'])) {
    // Si la requête est JSON pure (étapes sans fichier), l'étape est dans $data.
    $step = (int)$data['step'];
}

// Vérification finale de l'étape
if ($step === 0) {
    echo json_encode(['success' => false, 'message' => 'Étape de traitement manquante ou invalide.']);
    exit;
}

$stmt = null; // Initialiser $stmt à null pour la vérification finale

switch ($step) {
    case 2:
        $p = $data['data'];
        try {
            // La préparation de la requête est essentielle
            if (!($stmt = $conn->prepare("INSERT INTO Patient
            (Num_SecuSocial_Patient, Civilité_Patient, Nom_Naissance, Nom_Epouse, Prénom_Patient, Date_Naissance, Num_Adresse, Rue_Adresse, Code_Postal, Ville_Adresse, Adresse_Mail, Telephone_Patient)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?);"))) {
                // Échec de la préparation (ex: colonne mal nommée)
                echo json_encode(['success' => false, 'message' => 'Erreur de préparation : ' . $conn->error]);
            } else {
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
                    echo json_encode(['success' => false, 'message' => 'Erreur d\'exécution : ' . $stmt->error]);
                    exit;
                }
            }
            if (isset($stmt)) $stmt->close();
            if (!($stmt = $conn->prepare("INSERT INTO CouvertureSocial
            (Numero_Sec_Social, Nom_OrganismeSecuSocial, Patient_Assuré, Patient_ADL, Nom_Mutuelle, Numéro_Adhérent)
            VALUES (?, ?, ?, ?, ?, ?);"))) {
                echo json_encode(['success' => false, 'message' => 'Erreur de préparation : ' . $conn->error]);
            } else {
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
                    exit;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erreur d\'exécution : ' . $stmt->error]);
                    exit;
                }
            }
        } catch (Exception $e) {
            // Erreur PHP générale
            echo json_encode(['success' => false, 'message' => 'Erreur du serveur : ' . $e->getMessage()]);
            exit;
        }
    case 3:
        $p = $data['data'];
        try {
            if (!isset($_SESSION['NumSecu'])) {
                echo json_encode(['success' => false, 'message' => 'ID Patient manquant en session. Veuillez recommencer l\'enregistrement.']);
                exit;
            }
            $NumSecu = $_SESSION['NumSecu'];
            if (!($stmt = $conn->prepare("INSERT INTO Hospitalisation
            (Date_Hospitalisation, Heure_Hospitalisation, TypeHospitalisation, ChambreOccupé, Medecin_En_Charge, ID_Patient)
            VALUES (?, ?, ?, ?, ?, ?);"))) {
                echo json_encode(['success' => false, 'message' => 'Erreur de préparation : ' . $conn->error]);
            } else {
                $TypeHospi = (int)$p['TypeHospi'];
                $Chambre = (int)$p['Chambre'];
                $Medecin = (int)$p['Medecin'];
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
                    exit;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erreur d\'exécution : ' . $stmt->error]);
                    exit;
                }
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur du serveur : ' . $e->getMessage()]);
            exit;
        }
    case 4:
        $p = $data['data'];
        $personneAprevenir = $p['PersonnePrev'];
        $personneDeConfiance = $p['PersonneConf'];
        $responsableLegal = $p['ResponsableLegal'];

        $responseIDs = [];

        try {
            if (!isset($_SESSION['NumSecu'])) {
                echo json_encode(['success' => false, 'message' => 'ID Patient manquant en session. Veuillez recommencer l\'enregistrement.']);
                exit;
            }
            $NumSecu = $_SESSION['NumSecu'];

            // La PP est toujours présente, on l'insère dans la table PersonneContact
            if (!($stmt = $conn->prepare("INSERT INTO Personne_Prevenir
            (Nom_Pers, Prénom_Pers, Telephone_Pers, Num_Adresse, Rue_Adresse, Ville_Adresse, Code_Postal_Pers)
            VALUES (?, ?, ?, ?, ?, ?, ?);"))) {

                echo json_encode(['success' => false, 'message' => 'Erreur de préparation PP: ' . $conn->error]);
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
                $personneAprevenir['CP'],
            );

            if (!$stmt->execute()) {
                echo json_encode(['success' => false, 'message' => 'Erreur d\'exécution PP: ' . $stmt->error]);
                exit;
            }
            $_SESSION['ID_Personne_Prevenir'] = $conn->insert_id;
            if (isset($stmt)) $stmt->close();


            // 3. INSERTION DE LA PERSONNE DE CONFIANCE (PC) - SI ELLE EST DISTINCTE

            // $personneDeConfiance sera NULL si isMultiPersonne.checked était TRUE (PP est aussi PC)
            if ($personneDeConfiance !== null) {

                if (!($stmt = $conn->prepare("INSERT INTO Personne_Prevenir 
                (Nom_Pers, Prénom_Pers, Telephone_Pers, Num_Adresse, Rue_Adresse, Ville_Adresse, Code_Postal_Pers)
                VALUES (?, ?, ?, ?, ?, ?, ?);"))) {

                    echo json_encode(['success' => false, 'message' => 'Erreur de préparation PC: ' . $conn->error]);
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
                    $personneDeConfiance['CP'],
                );

                if (!$stmt->execute()) {
                    echo json_encode(['success' => false, 'message' => 'Erreur d\'exécution PC: ' . $stmt->error]);
                    exit;
                }
                $_SESSION['ID_Personne_Confiance'] = $conn->insert_id;
                if (isset($stmt)) $stmt->close();
            } else {
                $_SESSION['ID_Personne_Confiance'] = $conn->insert_id;
            }


            // 4. INSERTION DU RESPONSABLE LÉGAL (RESP) - SI PRÉSENT (Mineur)

            // $responsableLegal sera NULL si le formulaire n'était pas affiché (majeur)
            if ($responsableLegal !== null) {

                if (!($stmt = $conn->prepare("INSERT INTO Responsable 
                (Nom_Responsable, Prenom_Responsable, Telephone_Responsable, AdresseMail_Responsable, Num_Adresse_Responsable, Rue_Responsable, Ville_Responsable, Code_Postal_Responsable)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?);"))) {
                    echo json_encode(['success' => false, 'message' => 'Erreur de préparation RESP: ' . $conn->error]);
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
                    $responsableLegal['CP'],
                );

                if (!$stmt->execute()) {
                    echo json_encode(['success' => false, 'message' => 'Erreur d\'exécution RESP: ' . $stmt->error]);
                    exit;
                }
                $ID_RESP = $conn->insert_id;
                if (isset($stmt)) $stmt->close();

                if (!($stmt = $conn->prepare("INSERT INTO SousTutelleDe
                (ID_Responsable, ID_Patient)
                VALUES (?, ?);"))) {
                    echo json_encode(['success' => false, 'message' => 'Erreur de préparation SousTutelle: ' . $conn->error]);
                    exit;
                }

                $stmt->bind_param(
                    'is',
                    $ID_RESP,
                    $NumSecu,
                );
                if (!$stmt->execute()) {
                    echo json_encode(['success' => false, 'message' => 'Erreur d\'exécution RESP: ' . $stmt->error]);
                    exit;
                }
                if (isset($stmt)) $stmt->close();
            }

            // 5. SUCCÈS : Toutes les insertions ont réussi
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            // Erreur PHP générale (connexion, etc.)
            echo json_encode(['success' => false, 'message' => 'Erreur du serveur (Etape 4) : ' . $e->getMessage()]);
            exit;
        }
    case 5:
        if (!isset($_SESSION['NumSecu'])) {
            echo json_encode(['success' => false, 'message' => 'ID Patient manquant en session. Enregistrement incomplet.']);
            exit;
        }
        $NumSecuPatient = $_SESSION['NumSecu'];

        // --- 2. DÉFINITION DE LA REQUÊTE ET DES PARAMÈTRES ---

        // Liste des colonnes de la table Document DANS L'ORDRE D'INSERTION
        $colonnesBDD = [
            'Numéro_SecSocial_Document',
            'Carte_Identité',
            'Carte_Vitale',
            'Carte_mutuelle',
            'Livret_Famille',
            'Autorisation_soin',
            'Decision_juge'
        ];

        // Correspondance entre le 'name' HTML et l'INDEX dans le tableau $colonnesBDD (pour les fichiers)
        // Numéro_SecSocial_Document est à l'index 0
        $fieldIndexMapping = [
            'CarteID'       => 1,
            'CarteVitale'   => 2,
            'CarteMutuelle' => 3,
            'LivretFamille' => 4,
            'AutoSoin'      => 5,
            'DecisionJuge'  => 6
        ];
        // Initialisation du tableau des paramètres avec NULL pour tous les BLOBs
        // et le Numéro de Sécu pour le premier élément.
        $insertParams = array_fill(0, count($colonnesBDD), NULL);
        $insertParams[0] = $NumSecuPatient; // Le premier paramètre est toujours le numéro de sécu
        $types = 'sbbbbbb'; // s (string) pour Numéro de Sécu, bbbbbb (6 BLOBs) pour les documents
        try {
            if (empty($_FILES)) {
                // Si la requête est reçue mais que $_FILES est vide, c'est presque toujours une limite PHP
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur critique : Aucun fichier reçu par PHP. La requête dépasse les limites du serveur (post_max_size / upload_max_filesize).'
                ]);
                exit;
            }
            // --- 3. LECTURE DES FICHIERS ET MISE À JOUR DES PARAMÈTRES ---
            foreach ($_FILES as $fieldName => $file) {
                // On vérifie que le champ est un document que nous devons traiter
                if (isset($fieldIndexMapping[$fieldName]) && $file['error'] === UPLOAD_ERR_OK) {

                    $index = $fieldIndexMapping[$fieldName];

                    // On lit le contenu binaire du fichier temporaire
                    $fileContent = file_get_contents($file['tmp_name']);

                    if ($fileContent === false) {
                        throw new Exception("Erreur de lecture du contenu binaire pour le fichier {$fieldName}.");
                    }

                    // Remplacement de la valeur NULL par le contenu binaire du fichier
                    $insertParams[$index] = $fileContent;
                }
            }
            // --- 4. EXÉCUTION DE L'INSERT UNIQUE ---
            // Construction de la requête INSERT (colonnes + 7 placeholders ?)
            $sql = "INSERT INTO PiecesJoints (`"
                . implode('`, `', $colonnesBDD)
                . "`) VALUES (?, ?, ?, ?, ?, ?, ?)";

            if (!($stmt = $conn->prepare($sql))) {
                echo json_encode(['success' => false, 'message' => 'Erreur de préparation INSERT Document: ' . $conn->error]);
                exit;
            }
            // Lier les paramètres de manière dynamique (nécessaire avec les BLOBs et le nombre variable de paramètres)
            // Construction du tableau d'arguments pour bind_param
            $bindArgs = array_merge([$types], $insertParams);
            // mysqli_stmt::bind_param nécessite des références
            $references = [];
            foreach ($bindArgs as $key => &$value) {
                $references[] = &$value; // On lie par référence
            }
            // Exécuter la liaison
            call_user_func_array([$stmt, 'bind_param'], $references);
            foreach ($insertParams as $i => $value) {
                // On saute l'index 0 (Numéro de sécu, type string)
                if ($i > 0 && $value !== null) {
                    $stmt->send_long_data($i, $value);
                }
            }
            if (!$stmt->execute()) {
                echo json_encode(['success' => false, 'message' => 'Erreur d\'exécution INSERT Document: ' . $stmt->error]);
                exit;
            }
            if (isset($stmt)) $stmt->close();

            // --- 1. RÉCUPÉRATION DES IDENTIFIANTS NÉCESSAIRES ---

            if (
                !isset($_SESSION['NumSecu']) ||
                !isset($_SESSION['ID_Hospitalisation']) || // Supposé stocké à l'étape 3
                !isset($_SESSION['ID_Personne_Prevenir']) || // Supposé stocké à l'étape 4
                !isset($_SESSION['ID_Personne_Confiance'])
            ) {
                echo json_encode(['success' => false, 'message' => 'Identifiants de session manquants pour finaliser la Pré-Admission.']);
                // NOTE : Il pourrait aussi manquer l'ID du Responsable Légal si le patient est mineur
                exit;
            }

            $Patient_PreAdmi = $_SESSION['NumSecu'];
            $Hospitalisation_PreAdmi = $_SESSION['ID_Hospitalisation'];
            $Personne_aprev = $_SESSION['ID_Personne_Prevenir'];
            $Personne_deconf = $_SESSION['ID_Personne_Confiance'];

            // --- 2. PRÉPARATION ET EXÉCUTION DE L'INSERTION ---

            $sql = "INSERT INTO PreAdmission (
                    Patient_PreAdmi, 
                    Hospitalisation_PreAdmi, 
                    Personne_aprev, 
                    Personne_deconf
                ) VALUES (?, ?, ?, ?)";

            if (!($stmt = $conn->prepare($sql))) {
                echo json_encode(['success' => false, 'message' => 'Erreur de préparation PreAdmission: ' . $conn->error]);
                exit;
            }

            // Liaison des 4 paramètres: s (string pour NumSecu), i (int), i (int), i (int)
            $stmt->bind_param(
                'siii',
                $Patient_PreAdmi,
                $Hospitalisation_PreAdmi,
                $Personne_aprev,
                $Personne_deconf
            );

            if (!$stmt->execute()) {
                echo json_encode(['success' => false, 'message' => 'Erreur d\'exécution PreAdmission: ' . $stmt->error]);
                exit;
            }

            // --- 3. FINALISATION ET NETTOYAGE ---
            $stmt->close();

            unset($_SESSION['NumSecu']);
            unset($_SESSION['ID_Hospitalisation']);
            unset($_SESSION['ID_Personne_Prevenir']);
            unset($_SESSION['ID_Personne_Confiance']);

            echo json_encode(['success' => true, 'message' => 'Pré-admission enregistrée avec succès.']);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur du serveur (Etape 6) : ' . $e->getMessage()]);
            exit;
        }
    default:
        // Gère les valeurs 'step' non reconnues ou manquantes
        echo json_encode(['success' => false, 'message' => 'Étape de traitement non reconnue']);
        exit;
}
// FERMETURE SÉCURISÉE DES RESSOURCES
if ($stmt !== null) { // Vérifie si le statement a été créé
    $stmt->close();
}
// Assurez-vous que $conn existe (elle devrait si db.php a réussi)
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
// Note : Si l'un des 'echo json_encode' précédents a été exécuté, 
// il est préférable d'ajouter 'exit;' après, mais ici c'est géré par le flux.
