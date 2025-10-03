<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Secrétaire</title>
    <link rel="stylesheet" href="../CSS/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <img src="../INCLUDES/IMAGES/LPFSLogo.png" alt="Logo Clinique" class="logo">
            <h2>Secrétaire</h2>
            <ul class="menu">
                <li>Accueil</li>
                <li>Enregistrer une Pré-admission</li>
                <li>Liste des Patients</li>
                <li>Liste des Pré-admissions</li>
                <li><a href="../logout.php" style="color:#fff; text-decoration:none;">Déconnexion</a></li>
            </ul>
        </div>
        <div class="main-content">
            <h1>Bienvenue !</h1>
            <p>Votre ID utilisateur : <?php echo htmlspecialchars($user_id); ?></p>
        </div>
        <div class="background-shape"></div>
        <div class="background-shape2"></div>
    </div>
</body>
</html>