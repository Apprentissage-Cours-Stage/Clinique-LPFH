<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$dbname = "CliniqueLPFS";
$master_user = "AuthentificationLPFS2025";
$master_pass = "AuthLPFS2025";

// Connexion maître
$masterConn = new mysqli($host, $master_user, $master_pass, $dbname);
if ($masterConn->connect_error) {
    die("Connexion échouée: " . $masterConn->connect_error);
}
$masterConn->set_charset("utf8");

// Vérifier si l'utilisateur est connecté via le formulaire d'authentification
if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
    $username = $_SESSION['username'];
    $password = $_SESSION['password'];

    // Recherche dans la table users du compte SQL nominatif
    $stmt = $masterConn->prepare("SELECT CompteSQL, MDP FROM Utilisateurs WHERE Identifiant_User = ? AND MDP = ? LIMIT 1");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $stmt->bind_result($sql_user, $sql_pass);

    if ($stmt->fetch()) {
        // On a trouvé un compte SQL nominatif
        $_SESSION['sql_user'] = $sql_user;
        $_SESSION['sql_pass'] = $sql_pass;
    }
    $stmt->close();
}

// Si l'utilisateur est déjà connecté et qu'on a enregistré le compte SQL nominatif :
if (isset($_SESSION['sql_user']) && isset($_SESSION['sql_pass'])) {
    $sql_user = $_SESSION['sql_user'];
    $sql_pass = $_SESSION['sql_pass'];
    $nominalConn = new mysqli($host, $sql_user, $sql_pass, $dbname);

    if ($nominalConn->connect_error) {
        die("Connexion échouée avec le compte nominatif : " . $nominalConn->connect_error);
    }

    $nominalConn->set_charset("utf8");
    $conn = $nominalConn; // <- variable universelle accessible partout
} else {
    // Si pas encore connecté, on reste sur la connexion maître
    $conn = $masterConn;
}
?>
