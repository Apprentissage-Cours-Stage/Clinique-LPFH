<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

echo "Bienvenue, ".htmlspecialchars($_SESSION['username']);
?>