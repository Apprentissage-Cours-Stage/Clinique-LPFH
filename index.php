<?php
session_start();
require 'INCLUDES/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    //Préparation et execution de la requete SQL
    $stmt = $conn->prepare("SELECT * FROM Utilisateurs WHERE Identifiant_User = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        //Connexion établi
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: SECRETARY/dashboard-secretary.php');
        exit;
    } else {
        echo "Identifiants incorrects";
    }
}
?>
<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Portail Intranet - CliniqueLPFS</title>
    <link rel="stylesheet" href="CSS/signin.css">
</head>
<body>
    <div class="login-container">
        <div class="background-shape"></div>
        <div class="background-shape2"></div>
        <img src="INCLUDES/IMAGES/LPFSLogo.png" alt="Logo LPFS">
        <h2>Connexion Intranet</h2>
        <form>
             <div class="input-group">
                <label for="username">Identifiant</label>
                <input type="text" id="username" placeholder="Votre identifiant" required>
             </div>
             <div class="input-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
             </div>
             <button type="submit" class="login-btn">Se connecter</button>
        </form>
        <div class="footer">
            © 2025 Clinique LPFS - Portail sécurisé
        </div>
    </div>
</body>