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

$sqlServices = "SELECT s.ID_Service, s.Libellé_Service, COUNT(p.ID_Personnel) AS NbPraticients
                FROM service s
                LEFT JOIN personnel p ON p.Id_Service = s.ID_Service
                GROUP BY ID_Service
                ORDER BY Libellé_Service ASC";
$stmtService = mysqli_prepare($conn, $sqlServices);
mysqli_stmt_execute($stmtService);
$resultService = mysqli_stmt_get_result($stmtService)
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajout d'un Service - Administrateur</title>
    <link rel="stylesheet" href="../CSS/list-service.css">
    <link rel="stylesheet" href="../INCLUDES/CSS/header.css">
</head>

<body>
    <div class="dashboard-container">
        <?php require_once "../INCLUDES/header.php"; ?>
        <div class="content">
            <h1>Services de l'établissement</h1>

            <div class="card-container">
                <?php while ($s = mysqli_fetch_assoc($resultService)): ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($s['Libellé_Service']) ?></h3>
                        <p><strong>Activité :</strong> <?= $s['NbPraticients'] ?> utilisateurs enregistrées</p>

                        <p style="margin-top:15px; text-align:right;">
                            <a href="details_service.php?id=<?= $s['ID_Service'] ?>"
                                style="color: #005f99; font-weight: bold; text-decoration: none; font-size: 0.85em;">
                                Voir détails →
                            </a>
                        </p>
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