<?php
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$dbname = "cliniquelpfs";
$master_user = "AuthentificationLPFS2025";
$master_pass = "AuthLPFS2025";

// 1. Connexion maître (Sert uniquement à s'authentifier)
$masterConn = new mysqli($host, $master_user, $master_pass, $dbname);
if ($masterConn->connect_error) {
    die("Connexion maître échouée: " . $masterConn->connect_error);
}
$masterConn->set_charset("utf8");

// 2. Si l'utilisateur est authentifié sur le portail (on a son email) mais qu'on a pas encore son username SQL
if (isset($_SESSION['username']) && !isset($_SESSION['sql_user'])) {
    $username = $_SESSION['username'];

    $stmt = $masterConn->prepare("SELECT CompteSQL FROM utilisateurs WHERE LOWER(Identifiant_User) = LOWER(?) LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($sql_user);

    if ($stmt->fetch()) {
        $_SESSION['sql_user'] = $sql_user; // On le garde en session pour la suite
    }
    $stmt->close();
}

// 3. Établissement de la connexion finale ($conn)
if (isset($_SESSION['sql_user']) && isset($_SESSION['clear_password'])) {
    
    // On se connecte IMMÉDIATEMENT avec le compte nominatif de l'employé
    $sql_user = $_SESSION['sql_user'];
    $sql_pass = $_SESSION['clear_password'];

    $nominalConn = new mysqli($host, $sql_user, $sql_pass, $dbname);

    if ($nominalConn->connect_error) {
        session_destroy();
        die("Connexion échouée avec le compte nominatif SQL. Veuillez vous reconnecter.");
    }

    $nominalConn->set_charset("utf8");
    $conn = $nominalConn; // $conn prend les droits métiers de l'employé (Secrétaire, Admin, etc.)
} else {
    // Si l'utilisateur n'est pas pleinement connecté (ex: page index.php), on laisse le compte Maître
    $conn = $masterConn;
}
?>