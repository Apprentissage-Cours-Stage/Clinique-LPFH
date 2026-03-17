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

// Utilisation du regroupement correct pour éviter les erreurs SQL (s.ID_Service)
$sqlServices = "SELECT s.ID_Service, s.Libellé_Service, COUNT(p.ID_Personnel) AS NbPraticients
                FROM service s
                LEFT JOIN personnel p ON p.ID_Service = s.ID_Service
                GROUP BY s.ID_Service, s.Libellé_Service
                ORDER BY s.Libellé_Service ASC";

$stmtService = mysqli_prepare($conn, $sqlServices);
mysqli_stmt_execute($stmtService);
$resultService = mysqli_stmt_get_result($stmtService);

$message = "";
$messageClass = "";

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') {
        $message = "Le service a été supprimé avec succès.";
        $messageClass = "success";
    }
}

if (isset($_GET['error'])) {
    $messageClass = "error";
    switch ($_GET['error']) {
        case 'not_empty':
            $count = isset($_GET['count']) ? intval($_GET['count']) : "?";
            $message = "Impossible de supprimer : ce service contient encore <strong>$count</strong> membre(s) du personnel. Veuillez les changer de service puis réessayer.";
            break;
        case 'sql_error':
            $message = "Une erreur technique est survenue lors de la suppression.";
            break;
        default:
            $message = "Une erreur inconnue est survenue.";
    }
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services de l'établissement - Administrateur</title>
    <link rel="stylesheet" href="../CSS/list-service.css">
    <link rel="stylesheet" href="../INCLUDES/CSS/header.css">
</head>

<body>
    <div class="dashboard-container">
        <?php require_once "../INCLUDES/header.php"; ?>
        <div class="content">
            <h1>Services de l'établissement</h1>

            <?php if ($message): ?>
                <div class="alert <?= $messageClass ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="card-container">
                <?php while ($s = mysqli_fetch_assoc($resultService)): ?>
                    <div class="card">
                        <div class="card-actions">
                            <a href="edit-service.php?id=<?= $s['ID_Service'] ?>" title="Modifier">
                                <img src="../INCLUDES/ICONS/edit.png" alt="Modifier">
                            </a>
                            <a href="delete-service.php?id=<?= $s['ID_Service'] ?>"
                                title="Supprimer"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer le service <?= addslashes($s['Libellé_Service']) ?> ?');">
                                <img src="../INCLUDES/ICONS/delete.png" alt="Supprimer">
                            </a>
                        </div>
                        <h3><?= htmlspecialchars($s['Libellé_Service']) ?></h3>
                        <p><strong>Activité :</strong> <?= $s['NbPraticients'] ?> utilisateur(s) enregistré(s)</p>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php if (mysqli_num_rows($resultService) == 0): ?>
                <p>Aucun service n'est configuré dans la base de données.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>