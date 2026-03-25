<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$basePath = "../";   
$context = "HEAD";
$shownContext = "Chef de Service";

require_once "../INCLUDES/db.php";

$user_id = $_SESSION['user_id']; // ID du Chef connecté

// 1. Récupérer le service du Chef connecté
$sqlService = "SELECT ID_Service FROM personnel WHERE ID_Personnel = ?";
$stmtServ = mysqli_prepare($conn, $sqlService);
mysqli_stmt_bind_param($stmtServ, "i", $user_id);
mysqli_stmt_execute($stmtServ);
$resServ = mysqli_stmt_get_result($stmtServ);
$rowServ = mysqli_fetch_assoc($resServ);
$service_id = $rowServ ? $rowServ['ID_Service'] : 0;

// 2. Requête filtrée uniquement sur le service du chef connecté
$sqlPersonnel = "SELECT 
                    p.ID_Personnel, 
                    p.Nom_Personnel, 
                    p.Prénom_Personnel, 
                    r.Libellé_Role, 
                    s.Libellé_Service
                 FROM personnel p
                 INNER JOIN service s ON p.ID_Service = s.ID_Service
                 LEFT JOIN role r ON p.Role_Personnel = r.ID_Role
                 WHERE p.ID_Service = ?
                 ORDER BY p.Nom_Personnel ASC, p.Prénom_Personnel ASC";

$stmtPersonnel = mysqli_prepare($conn, $sqlPersonnel);
mysqli_stmt_bind_param($stmtPersonnel, "i", $service_id);
mysqli_stmt_execute($stmtPersonnel);
$resultPersonnel = mysqli_stmt_get_result($stmtPersonnel);

// Variable pour afficher le nom du service en titre principal
$nomServiceAffiche = "Mon Service";
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualisation du Personnel - Chef de Service</title>
    <link rel="stylesheet" href="../CSS/list-user.css"> 
    <link rel="stylesheet" href="../INCLUDES/CSS/header.css">
</head>

<body>
    <div class="dashboard-container">
        <?php require_once "../INCLUDES/header.php"; ?>
        <div class="content">
            
            <?php if (mysqli_num_rows($resultPersonnel) > 0): 
                // Récupération du nom du service pour le titre de la page via le premier employé trouvé
                $firstRow = mysqli_fetch_assoc($resultPersonnel);
                $nomServiceAffiche = $firstRow['Libellé_Service'];
                // On remet le pointeur au début du résultat pour la boucle while suivante
                mysqli_data_seek($resultPersonnel, 0); 
            endif; ?>

            <h1>Personnel rattaché au service : <?= htmlspecialchars($nomServiceAffiche) ?></h1>

            <div class="card-container">
                <?php if (mysqli_num_rows($resultPersonnel) === 0): ?>
                    <p class="no-data">Aucun employé n'est rattaché à votre service actuellement.</p>
                <?php else: ?>
                    <?php while ($p = mysqli_fetch_assoc($resultPersonnel)): ?>
                        <div class="card">
                            <h3><?= htmlspecialchars(mb_strtoupper($p['Nom_Personnel']) . " " . $p['Prénom_Personnel']) ?></h3>
                            
                            <p><strong>Rôle :</strong> <?= htmlspecialchars($p['Libellé_Role'] ?? 'Non défini') ?></p>
                            <p><strong>Service :</strong> <?= htmlspecialchars($p['Libellé_Service']) ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</body>
</html>