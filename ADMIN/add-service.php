<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$basePath = "../";   // IMPORTANT
$context = "ADMIN";
$shownContext = "Administrateur";

require_once "../INCLUDES/db.php";
$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $serviceName = trim($_POST["serviceName"] ?? "");

    if (empty($serviceName)) {
        $error = "Le nom du service est obligatoire.";
    } else {
        try {
            $checkSQL = "SELECT COUNT(*) FROM service WHERE Libellé_Service = ?";
            $stmtCheck = $conn->prepare($checkSQL);
            $stmtCheck->bind_param("s", $serviceName);
            $stmtCheck->execute();
            $stmtCheck->bind_result($count);
            $stmtCheck->fetch();
            $stmtCheck->close();

            if ($count > 0) {
                $error = "Ce service existe déjà.";
            } else {
                $InsertSQL = "INSERT INTO service (Libellé_Service) VALUES (?)";
                $stmtInsert = $conn->prepare($InsertSQL);
                $stmtInsert->bind_param("s", $serviceName);

                if ($stmtInsert->execute()) {
                    $message = "Le service a été crée avec succès.";
                }

                $stmtInsert->close();
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de l'insertion :" . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajout d'un Service - Administrateur</title>
    <link rel="stylesheet" href="../CSS/add-service.css">
    <link rel="stylesheet" href="../INCLUDES/CSS/header.css">
</head>

<body>
    <div class="dashboard-container">
        <?php require_once "../INCLUDES/header.php"; ?>
        <div class="main-content">
            <div class="form-container">
                <h1>Ajouter un nouveau service</h1>
                <?php if (!empty($message)): ?>
                    <p class="message"><?= htmlspecialchars($message) ?></p>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="serviceName">Nom du service</label>
                        <input type="text" id="serviceName" name="serviceName" required>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">Créer le service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>