<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$basePath = "../";
$context = "ADMIN";
$shownContext = "Administrateur";

require_once "../INCLUDES/db.php";
$message = "";
$error = "";

$idService = $_GET['id'] ?? null;

if (!$idService) {
    header('Location: list-service.php');
    exit;
}

$sqlGET = "SELECT Libellé_Service FROM service WHERE ID_Service = ?";
$stmtGET = mysqli_prepare($conn, $sqlGET);
mysqli_stmt_bind_param($stmtGET, "i", $idService);
mysqli_stmt_execute($stmtGET);
$resultGET = mysqli_stmt_get_result($stmtGET);
if ($row = mysqli_fetch_assoc($resultGET)) {
    $currentName = $row['Libellé_Service'];
} else {
    header('Location: list-service.php');
    exit;
}
mysqli_stmt_close($stmtGET);

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $newName = trim($_POST['serviceName'] ?? "");

    if (empty($newName)) {
        $error = "Le nom du service est obligatoire.";
    } else {
        $checkSQL = "SELECT COUNT(*) FROM service WHERE Libellé_Service = ? AND ID_Service != ?";
        $stmtCheck = mysqli_prepare($conn, $checkSQL);
        mysqli_stmt_bind_param($stmtCheck, "si", $newName, $idService);
        mysqli_stmt_execute($stmtCheck);
        mysqli_stmt_bind_result($stmtCheck, $count);
        mysqli_stmt_fetch($stmtCheck);
        mysqli_stmt_close($stmtCheck);

        if ($count > 0) {
            $error = "Ce nom de service existe déjà pour un autre service.";
        } else {
            $sqlUPD = "UPDATE service SET Libellé_Service = ? WHERE ID_Service = ?";
            $stmtUPD = mysqli_prepare($conn, $sqlUPD);
            mysqli_stmt_bind_param($stmtUPD, "si", $newName, $idService);

            if (mysqli_stmt_execute($stmtUPD)) {
                $message = "Le service a été mis à jour avec succès.";
            } else {
                $error = "Une erreur est survenu lors de l'enregistrement.";
            }
            mysqli_stmt_close($stmtUPD);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Service - Administrateur</title>
    <link rel="stylesheet" href="../CSS/edit-service.css">
    <link rel="stylesheet" href="../INCLUDES/CSS/header.css">
</head>

<body>
    <div class="dashboard-container">
        <?php require_once "../INCLUDES/header.php"; ?>
        <div class="main-content">
            <div class="form-container">
                <h1>Modifier le service</h1>
                
                <?php if (!empty($message)): ?>
                    <p class="message" style="color: green; margin-bottom: 10px;"><?= htmlspecialchars($message) ?></p>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <p class="error" style="color: red; margin-bottom: 10px;"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="serviceName">Nom du service</label>
                        <input type="text" id="serviceName" name="serviceName" 
                               value="<?= htmlspecialchars($currentName) ?>" required>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        <a href="list-service.php" class="btn btn-secondary" style="text-decoration: none; margin-left: 10px; color: #666;">Retour</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>