<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$basePath = "../";
$context = "ADMIN";
$shownContext = "Administrateur";
$username = $_SESSION['username'];

// Inclusion de votre fichier de connexion (qui crée $conn via mysqli)
require_once "../INCLUDES/db.php"; 

/**
 * Récupération des statistiques via MySQLi
 */
// 1. Nombre total d'utilisateurs
$resUsers = $conn->query("SELECT COUNT(*) as total FROM utilisateurs");
$rowUsers = $resUsers->fetch_assoc();
$nbUsers = $rowUsers['total'];

// 2. Nombre total d'employés (Table: personnel)
$resEmployees = $conn->query("SELECT COUNT(*) as total FROM personnel");
$rowEmployees = $resEmployees->fetch_assoc();
$nbEmployees = $rowEmployees['total'];

// 3. Nombre total de services (Table: service)
$resServices = $conn->query("SELECT COUNT(*) as total FROM service");
$rowServices = $resServices->fetch_assoc();
$nbServices = $rowServices['total'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administrateur</title>
    <link rel="stylesheet" href="../CSS/dashboard.css">
    <link rel="stylesheet" href="../INCLUDES/CSS/header.css">
</head>
<body>
    <div class="dashboard-container">
        <?php require_once "../INCLUDES/header.php"; ?>
        
        <div class="main-content">
            <h1>Bienvenue, <?php echo htmlspecialchars($username); ?> !</h1>
            
            <div class="cards">
                <div class="card">
                    <h3>Utilisateurs</h3>
                    <p><?php echo $nbUsers; ?></p>
                </div>
                <div class="card">
                    <h3>Employés</h3>
                    <p><?php echo $nbEmployees; ?></p>
                </div>
                <div class="card">
                    <h3>Services</h3>
                    <p><?php echo $nbServices; ?></p>
                </div>
            </div>

            <div class="section">
                <h2>Actions rapides</h2>
                <div class="action-buttons">
                    <a href="add-admission.php" class="btn-action">Ajouter une pré-admission</a>
                    <a href="list-admission.php" class="btn-action">Voir les admissions</a>
                    <a href="list-user.php" class="btn-action">Gérer les utilisateurs</a>
                    <a href="list-services.php" class="btn-action">Gérer les services</a>
                </div>
            </div>
        </div>

        <div class="background-shape"></div>
        <div class="background-shape2"></div>
    </div>
</body>
</html>