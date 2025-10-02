<?php
session_start();
require 'INCLUDES/db.php'; // contient la connexion mysqli
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Préparation de la requête
    $stmt = $conn->prepare("SELECT * FROM Utilisateurs WHERE LOWER(Identifiant_User) = LOWER(?)");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if ($password === $user['MDP']) { // si en clair
            $_SESSION['user_id'] = $user['ID_Employé'];
            $_SESSION['username'] = $user['Identifiant_User'];
            header('Location: SECRETARY/dashboard-secretary.php');
            exit;
        } else {
            $error = "Mot de passe incorrect";
        }
    } else {
        $error = "Identifiant introuvable";
    }
}
?>
<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Portail Intranet - CliniqueLPFS</title>
    <link rel="stylesheet" href="CSS/index.css">
</head>
<body>
    <div class="login-container">
        <div class="background-shape"></div>
        <div class="background-shape2"></div>
        <img src="INCLUDES/IMAGES/LPFSLogo.png" alt="Logo LPFS">
        <h2>Connexion Intranet</h2>
        <form method="POST">
             <div class="input-group">
                <label for="username">Identifiant</label>
                <input type="text" id="username" name="username" placeholder="Votre identifiant" required>
             </div>
             <div class="input-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
             </div>
             <?php if($error): ?>
                <p class="error-message"><?= htmlspecialchars($error) ?></p>
             <?php endif; ?>
             <button type="submit" class="login-btn">Se connecter</button>
        </form>
        <div class="footer">
            © 2025 Clinique LPFS - Portail sécurisé
        </div>
    </div>
</body>