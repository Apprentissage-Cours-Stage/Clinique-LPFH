<?php
session_start();
require 'INCLUDES/db.php'; // contient la connexion mysqli
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recaptcha_secret = '6LcvLOErAAAAACv0bWt8HKRlj0jjE8lkkUUijHSK';
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    if (empty($_POST['g-recaptcha-response'])) {
        $error = "Captcha non rempli.";
    } else {
        $verify_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'secret' => $recaptcha_secret,
                    'response' => $recaptcha_response
                ])
            ]
        ]));
        $captcha_success = json_decode($verify_response);
        if (!$captcha_success->success) {
            $error = "CAPTCHA invalide. Veuillez réessayer.";
        }
    }
    if (!$error) {
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
                $_SESSION['password'] = $user['MDP'];
            } else {
                $error = "Mot de passe incorrect";
            }
        } else {
            $error = "Identifiant introuvable";
        }

        $stmtrole = $conn->prepare("SELECT R.Libellé_Role 
                                FROM Role R
                                INNER JOIN Personnel P ON P.Role_Personnel = R.ID_Role
                                INNER JOIN Utilisateurs U ON U.ID_Employé = P.ID_Personnel
                                WHERE U.ID_Employé = (?);");
        $stmtrole->bind_param("i", $user['ID_Employé']);
        $stmtrole->execute();
        $resultrole = $stmtrole->get_result();
        $role = $resultrole->fetch_assoc();

        if($role) {
            $_SESSION['role'] = $role['Libellé_Role'];
            switch (strtolower($_SESSION['role'])) {
                case 'secrétaire':
                    header('Location: SECRETARY/dashboard-secretary.php');
                    exit;
                case 'administrateur':
                    header('Location: ADMIN/dashboard-admin.php');
                    exit;
                default:
                    $error = "Rôle non reconnu";
            }
        } else {
            $error = "Impossible de déterminer le rôle";
        }
    }
}
?>
<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Portail Intranet - CliniqueLPFS</title>
    <link rel="stylesheet" href="CSS/index.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
             <div class="g-recaptcha" data-sitekey="6LcvLOErAAAAAIN3FVSUcGg0r2JL7ImVeRAiJ3fn"></div>
             <br>
             <button type="submit" class="login-btn">Se connecter</button>
        </form>
        <div class="footer">
            © 2025 Clinique LPFS - Portail sécurisé
        </div>
    </div>
</body>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>