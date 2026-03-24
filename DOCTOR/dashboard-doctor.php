<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$basePath = "../";   // IMPORTANT
$context = "DOCTOR";
$shownContext = "Docteur";

$username = $_SESSION['username'];
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
            <h1>Bienvenue !</h1>
            <p>Votre ID utilisateur : <?php echo htmlspecialchars($username); ?></p>
        </div>
        <div class="background-shape"></div>
        <div class="background-shape2"></div>
    </div>
</body>
</html>