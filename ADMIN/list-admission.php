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

$today = date('Y-m-d');

/* Pré-admissions à venir */
$sqlAVenir = "SELECT pa.Id_PreAdmin, p.Num_SecuSocial_Patient, c.Libellé_Civilité, p.Nom_Naissance, 
                    p.Nom_Epouse, h.Date_Hospitalisation, h.Heure_Hospitalisation, 
                    ht.Libellé_TypeHospitalisation, prs.Nom_Personnel 
              FROM preadmission pa
              INNER JOIN hospitalisation h ON h.ID_Hospitalisation = pa.Hospitalisation_PreAdmi
              INNER JOIN typehospitalisation ht ON ht.ID_TypeHospitalisation = h.TypeHospitalisation
              INNER JOIN personnel prs ON prs.ID_Personnel = h.Medecin_En_Charge
              INNER JOIN patient p ON pa.Patient_PreAdmi = p.Num_SecuSocial_Patient
              INNER JOIN civilité c ON c.ID_Civilité = p.Civilité_Patient
              WHERE Date_Hospitalisation >= ? 
              ORDER BY Date_Hospitalisation ASC";

$stmtAVenir = mysqli_prepare($conn, $sqlAVenir);
mysqli_stmt_bind_param($stmtAVenir, "s", $today);
mysqli_stmt_execute($stmtAVenir);
$resultAVenir = mysqli_stmt_get_result($stmtAVenir);

/* Pré-admissions terminées */
$sqlTerminees = "SELECT pa.Id_PreAdmin, p.Num_SecuSocial_Patient, c.Libellé_Civilité, p.Nom_Naissance, 
                    p.Nom_Epouse, h.Date_Hospitalisation, h.Heure_Hospitalisation, 
                    ht.Libellé_TypeHospitalisation, prs.Nom_Personnel 
                 FROM preadmission pa
                 INNER JOIN hospitalisation h ON h.ID_Hospitalisation = pa.Hospitalisation_PreAdmi
                 INNER JOIN typehospitalisation ht ON ht.ID_TypeHospitalisation = h.TypeHospitalisation
                 INNER JOIN personnel prs ON prs.ID_Personnel = h.Medecin_En_Charge
                 INNER JOIN patient p ON pa.Patient_PreAdmi = p.Num_SecuSocial_Patient
                 INNER JOIN civilité c ON c.ID_Civilité = p.Civilité_Patient
                 WHERE Date_Hospitalisation < ? 
                 ORDER BY Date_Hospitalisation ASC";

$stmtTerminees = mysqli_prepare($conn, $sqlTerminees);
mysqli_stmt_bind_param($stmtTerminees, "s", $today);
mysqli_stmt_execute($stmtTerminees);
$resultTerminees = mysqli_stmt_get_result($stmtTerminees);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Pré-admission - Administrateur</title>
    <link rel="stylesheet" href="../CSS/list-admission.css">
    <link rel="stylesheet" href="../INCLUDES/CSS/header.css">
    <style>
        /* Styles rapides pour les alertes si non présents dans votre CSS */
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <?php require_once "../INCLUDES/header.php"; ?>
        <div class="content">

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                        if($_GET['success'] == 'delete') echo "✓ Le dossier et ses dépendances ont été supprimés avec succès.";
                        else echo "✓ Opération réussie.";
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                        if($_GET['error'] == 'id_invalide') echo "⚠ Erreur : ID de dossier invalide.";
                        else echo "⚠ Une erreur est survenue lors de l'opération.";
                    ?>
                </div>
            <?php endif; ?>

            <h1>Pré-admissions à venir</h1>
            <div class="card-container">
                <?php while ($p = mysqli_fetch_assoc($resultAVenir)): ?>
                    <div class="card">
                        <div class="card-actions">
                            <a href="edit-admission.php?id=<?php echo $p['Id_PreAdmin']; ?>" title="Modifier l'admision">
                                <img src="../INCLUDES/ICONS/edit.png" alt="Modifier">
                            </a>
                            <a href="../INCLUDES/generatePDF.php?id=<?= urlencode($p['Num_SecuSocial_Patient']) ?>" title="Exporter en PDF" target="_blank">
                                <img src="../INCLUDES/ICONS/export.png" alt="PDF">
                            </a>
                            <a href="delete-admission.php?id=<?php echo $p['Id_PreAdmin']; ?>" 
                               title="Supprimer l'admission" 
                               onclick="return confirm('Attention : Cette action est irréversible. Si le patient n\'a pas d\'autre dossier, ses données personnelles et son responsable seront également supprimés. Confirmer ?')">
                                <img src="../INCLUDES/ICONS/delete.png" alt="Supprimer">
                            </a>
                        </div>
                        <h3><?= htmlspecialchars($p['Libellé_Civilité']) ?> <?= htmlspecialchars(!empty($p['Nom_Epouse']) ? $p['Nom_Epouse'] : $p['Nom_Naissance']) ?></h3>
                        <p><strong>Date et Heure :</strong> <?= $p['Date_Hospitalisation'] ?> - <?= $p['Heure_Hospitalisation'] ?></p>
                        <p><strong>Type :</strong> <?= htmlspecialchars($p['Libellé_TypeHospitalisation']) ?></p>
                        <p><strong>Docteur :</strong> <?= htmlspecialchars($p['Nom_Personnel']) ?></p>
                    </div>
                <?php endwhile; ?>
            </div>

            <h1>Pré-admissions terminées</h1>
            <div class="card-container">
                <?php while ($p = mysqli_fetch_assoc($resultTerminees)): ?>
                    <div class="card finished">
                        <div class="card-actions">
                            <a href="delete-admission.php?id=<?php echo $p['Id_PreAdmin']; ?>" 
                               title="Supprimer l'admission"
                               onclick="return confirm('Confirmer la suppression de ce dossier archivé ?')">
                                <img src="../INCLUDES/ICONS/delete.png" alt="Supprimer">
                            </a>
                        </div>
                        <h3><?= htmlspecialchars($p['Libellé_Civilité']) ?> <?= htmlspecialchars(!empty($p['Nom_Epouse']) ? $p['Nom_Epouse'] : $p['Nom_Naissance']) ?></h3>
                        <p><strong>Date et Heure :</strong> <?= $p['Date_Hospitalisation'] ?> - <?= $p['Heure_Hospitalisation'] ?></p>
                        <p><strong>Type :</strong> <?= htmlspecialchars($p['Libellé_TypeHospitalisation']) ?></p>
                        <p><strong>Docteur :</strong> <?= htmlspecialchars($p['Nom_Personnel']) ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>