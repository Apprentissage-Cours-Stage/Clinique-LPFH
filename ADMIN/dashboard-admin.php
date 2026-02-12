<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administrateur</title>
    <link rel="stylesheet" href="../CSS/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <img src="../INCLUDES/IMAGES/LPFSLogo.png" alt="Logo Clinique" class="logo">
            <h2>Panel Administrateur</h2>
            <ul class="menu">
                <li><a href="dashboard-admin.php" style="color:#fff; text-decoration:none;">Accueil</a></li>
                <li><a href="add-admission.php" style="color:#fff; text-decoration:none;">Enregistrer une Pré-admission</a></li>
                <li><a href="list-admission.php" style="color:#fff; text-decoration:none;">Liste des Pré-admissions</a></li>
                <li>Enregistrer un nouveau personnel/utilisateur</li>
                <li>Liste du personnels/utilisateurs</li>
                <li><a href="add-service.php" style="color:#fff; text-decoration:none;">Enregistrer un nouveau service</a></li>
                <li>Liste des services</li>
                <li><a href="../logout.php" style="color:#fff; text-decoration:none;">Déconnexion</a></li>
            </ul>
        </div>
        <div class="main-content">
            <h1>Bienvenue !</h1>
            <p>Votre ID utilisateur : <?php echo htmlspecialchars($username); ?></p>
        </div>
        <div class="background-shape"></div>
        <div class="background-shape2"></div>
    </div>
</body>
</html>