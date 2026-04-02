<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$basePath = "../";
$context = "HEAD";
$shownContext = "Chef de Service";
$username = $_SESSION['username'];

require_once "../INCLUDES/db.php"; // Utilise $conn (mysqli)

/**
 * 1. Récupération des informations du service du Chef connecté
 */
$id_service = null;
$nom_service = "Non assigné";

// On lie Utilisateurs -> Personnel -> Service
$sql_info = "SELECT s.ID_Service, s.Libellé_Service 
             FROM Personnel p
             JOIN Service s ON p.ID_Service = s.ID_Service
             WHERE p.Nom_Personnel = ? OR p.ID_Personnel = (SELECT ID_Employé FROM Utilisateurs WHERE Identifiant_User = ?)
             LIMIT 1";

$stmt = $conn->prepare($sql_info);
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $id_service = $row['ID_Service'];
    $nom_service = $row['Libellé_Service'];
}
$stmt->close();

/**
 * 2. Statistiques filtrées par ID_Service
 */
$nbEmployees = 0;
$nbAdmissions = 0;

if ($id_service) {
    // Compter le personnel du service
    $stmtEmp = $conn->prepare("SELECT COUNT(*) as total FROM Personnel WHERE ID_Service = ?");
    $stmtEmp->bind_param("i", $id_service);
    $stmtEmp->execute();
    $nbEmployees = $stmtEmp->get_result()->fetch_assoc()['total'];
    $stmtEmp->close();

    // Compter les hospitalisations/admissions liées au service
    // Note : Selon votre schéma, le lien service est dans Hospitalisation
    $stmtAdm = $conn->prepare("SELECT COUNT(*) as total FROM Hospitalisation WHERE Medecin_En_Charge IN (SELECT ID_Personnel FROM Personnel WHERE ID_Service = ?)");
    // OU si vous avez ajouté ID_Service directement dans Hospitalisation/PreAdmission :
    // $stmtAdm = $conn->prepare("SELECT COUNT(*) as total FROM Hospitalisation WHERE ID_Service = ?"); 
    
    $stmtAdm->bind_param("i", $id_service);
    $stmtAdm->execute();
    $nbAdmissions = $stmtAdm->get_result()->fetch_assoc()['total'];
    $stmtAdm->close();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo $shownContext; ?></title>
    <link rel="stylesheet" href="../CSS/dashboard.css">
    <link rel="stylesheet" href="../INCLUDES/CSS/header.css">
</head>
<body>
    <div class="dashboard-container">
        <?php require_once "../INCLUDES/header.php"; ?>
        
        <div class="main-content">
            <h1>Tableau de bord : <?php echo htmlspecialchars($nom_service); ?></h1>
            <p>Chef de service : <strong><?php echo htmlspecialchars($username); ?></strong></p>
            
            <div class="cards">
                <div class="card">
                    <h3>Personnel du service</h3>
                    <span class="stat-number"><?php echo $nbEmployees; ?></span>
                    <p>Membres actifs</p>
                </div>

                <div class="card">
                    <h3>Admissions Service</h3>
                    <span class="stat-number"><?php echo $nbAdmissions; ?></span>
                    <p>Hospitalisations rattachées</p>
                </div>

                <div class="card">
                    <h3>Status Service</h3>
                    <span class="stat-number" style="font-size: 1.5rem; color: #28a745;">Actif</span>
                    <p>Répartition optimale</p>
                </div>
            </div>

            <div class="section">
                <h2>Actions rapides</h2>
                <div class="action-buttons">
                    <a href="list-serviceadmission.php" class="btn-action">Liste des hospitalisations</a>
                    <a href="list-serviceusers.php" class="btn-action">Liste de mon personnel</a>
                </div>
            </div>
        </div>

        <div class="background-shape"></div>
        <div class="background-shape2"></div>
    </div>
</body>
</html>