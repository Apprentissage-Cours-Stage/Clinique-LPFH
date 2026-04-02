<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$basePath = "../";
$context = "SECRETARY";
$shownContext = "Secrétaire";
$username = $_SESSION['username'];

require_once "../INCLUDES/db.php"; // Utilise $conn (mysqli)

/**
 * 1. Statistiques Globales pour le Secrétariat
 */

// Compter le nombre total de patients enregistrés
$resPatients = $conn->query("SELECT COUNT(*) as total FROM Patient");
$nbPatients = $resPatients->fetch_assoc()['total'];

// Compter les pré-admissions en attente (si vous avez un flag ou simplement le total)
$resPreAdmissions = $conn->query("SELECT COUNT(*) as total FROM PreAdmission");
$nbPreAdmissions = $resPreAdmissions->fetch_assoc()['total'];

// Compter les hospitalisations prévues aujourd'hui
$today = date('Y-m-d');
$stmtToday = $conn->prepare("SELECT COUNT(*) as total FROM Hospitalisation WHERE Date_Hospitalisation = ?");
$stmtToday->bind_param("s", $today);
$stmtToday->execute();
$nbHospitalisationsJour = $stmtToday->get_result()->fetch_assoc()['total'];
$stmtToday->close();
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
            <h1>Espace Secrétariat</h1>
            <p>Connecté en tant que : <strong><?php echo htmlspecialchars($username); ?></strong></p>
            
            <div class="cards">
                <div class="card">
                    <h3>Patients</h3>
                    <span class="stat-number"><?php echo $nbPatients; ?></span>
                    <p>Dossiers enregistrés</p>
                </div>

                <div class="card">
                    <h3>Pré-admissions</h3>
                    <span class="stat-number"><?php echo $nbPreAdmissions; ?></span>
                    <p>Dossiers à traiter</p>
                </div>

                <div class="card">
                    <h3>Entrées du jour</h3>
                    <span class="stat-number"><?php echo $nbHospitalisationsJour; ?></span>
                    <p><?php echo date('d/m/Y'); ?></p>
                </div>
            </div>

            <div class="section">
                <h2>Gestion Administrative</h2>
                <div class="action-buttons">
                    <a href="add-admission.php" class="btn-action">Créer une Pré-admission</a>
                    <a href="list-admission.php" class="btn-action">Liste des Hospitalisations</a>
                </div>
            </div>
        </div>

        <div class="background-shape"></div>
        <div class="background-shape2"></div>
    </div>
</body>
</html>