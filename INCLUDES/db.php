<?php
//Changer le compte selon le compte utiliser (SECRETARY, SECRET@RYLPFS2025)/(ADMINISTRATEUR)
$host = "192.168.100.14";
$user = "root";
$password = "sio2024";
$dbname = "CliniqueLPFS";

$conn = new mysqli($host, $user, $password, $dbname);
//Verification de la connexion
if ($conn->connect_error) {
    die("Connexion échouée: ".$conn->connect_error);
}
//Encodage UTF-8
$conn->set_charset("utf8");
?>