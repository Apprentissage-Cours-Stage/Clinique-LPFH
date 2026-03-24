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

$sqlPersonnel = "SELECT 
                    p.ID_Personnel, 
                    p.Nom_Personnel, 
                    p.Prénom_Personnel, 
                    r.Libellé_Role, 
                    s.Libellé_Service,
                    u.ID_Employé AS HasAccount
                 FROM personnel p
                 LEFT JOIN service s ON p.ID_Service = s.ID_Service
                 LEFT JOIN utilisateurs u ON u.ID_Employé = p.ID_Personnel
                 LEFT JOIN role r ON p.Role_Personnel = r.ID_Role
                 ORDER BY (u.ID_Employé IS NOT NULL) DESC,p.Nom_Personnel ASC, p.Prénom_Personnel ASC";

$stmtPersonnel = mysqli_prepare($conn, $sqlPersonnel);
mysqli_stmt_execute($stmtPersonnel);
$resultPersonnel = mysqli_stmt_get_result($stmtPersonnel);

$message = "";
$messageClass = "";

// Gestion des messages de retour (suppression, modification, etc.)
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'deleted') {
        $message = "L'employé a été supprimé avec succès.";
        $messageClass = "success";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du Personnel - Administrateur</title>
    <link rel="stylesheet" href="../CSS/list-user.css"> 
    <link rel="stylesheet" href="../INCLUDES/CSS/header.css">
</head>

<body>
    <div class="dashboard-container">
        <?php require_once "../INCLUDES/header.php"; ?>
        <div class="content">
            <h1>Liste du personnel employé</h1>

            <?php if ($message): ?>
                <div class="alert <?= $messageClass ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="card-container">
                <?php while ($p = mysqli_fetch_assoc($resultPersonnel)): ?>
                    <div class="card">
                        <div class="card-actions">
                            <a href="edit-employe.php?id=<?= $p['ID_Personnel'] ?>" title="Modifier">
                                <img src="../INCLUDES/ICONS/edit.png" alt="Modifier">
                            </a>
                            <a href="delete-employe.php?id=<?= $p['ID_Personnel'] ?>"
                                title="Supprimer"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer <?= addslashes($p['Prénom_Personnel'] . ' ' . $p['Nom_Personnel']) ?> ?');">
                                <img src="../INCLUDES/ICONS/delete.png" alt="Supprimer">
                            </a>
                        </div>
                        
                        <h3><?= htmlspecialchars(mb_strtoupper($p['Nom_Personnel']) . " " . $p['Prénom_Personnel']) ?></h3>
                        
                        <p><strong>Rôle :</strong> <?= htmlspecialchars($p['Libellé_Role'] ?? 'Non défini') ?></p>
                        <p><strong>Service :</strong> <?= htmlspecialchars($p['Libellé_Service'] ?? 'Aucun service') ?></p>
                        
                        <p>
                            <strong>Compte d'accès :</strong><br>
                            <?php if ($p['HasAccount']): ?>
                                <span class="badge success">✓ Compte actif</span>
                            <?php else: ?>
                                <span class="badge warning">✗ Pas de compte</span>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php if (mysqli_num_rows($resultPersonnel) == 0): ?>
                <p>Aucun employé n'est enregistré dans la base de données.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>