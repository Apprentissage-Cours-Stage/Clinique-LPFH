<?php
//Changer le compte selon le compte utiliser (SECRETARY, SECRET@RYLPFS2025)/(ADMINISTRATEUR)
$host = "192.168.100.14";
$dbname = "CliniqueLPFS";
$master_user = "AuthentificationLPFS2025";
$master_pass = "AuthLPFS2025";

$conn = new mysqli($host, $master_user, $master_pass, $dbname);
//Verification de la connexion
if ($conn->connect_error) {
    die("Connexion échouée: ".$conn->connect_error);
}
//Encodage UTF-8
$conn->set_charset("utf8");

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = $conn->prepare("SELECT CompteSQL, MDP FROM Utilisateurs WHERE Identifiant_User = (?) AND MDP = (?)");
    $sql->bind_param("ss", $username, $password);
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $username;

        $sql_user = $row['CompteSQL'];
        $sql_pass = $row['MDP'];
        $conn = new mysqli($host, $sql_user, $sql_pass, $dbname);
        if ($conn->connect_error) {
            die("Connexion échouée avec le compte nominatif : ".$conn->connect_error);
        }
        $conn->set_charset("utf8");
        echo "Connexion réussie pour $username avec le rôle".$_SESSION['role'];
    } else {
        die("Identifiants incorrects");
    }
}
?>