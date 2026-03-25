<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$basePath = "../";
$context = "DOCTOR";
$shownContext = "Docteur";

require_once "../INCLUDES/db.php";

$today = date('Y-m-d');
$user_id = $_SESSION['user_id']; // ID du médecin connecté

/* 1. Pré-admissions à venir de l'utilisateur connecté */
$sqlAVenir = "SELECT p.Num_SecuSocial_Patient, c.Libellé_Civilité, p.Nom_Naissance, 
                    p.Nom_Epouse, h.Date_Hospitalisation, h.Heure_Hospitalisation, 
                    ht.Libellé_TypeHospitalisation
              FROM preadmission pa
              INNER JOIN hospitalisation h ON h.ID_Hospitalisation = pa.Hospitalisation_PreAdmi
              INNER JOIN typehospitalisation ht ON ht.ID_TypeHospitalisation = h.TypeHospitalisation
              INNER JOIN personnel prs ON prs.ID_Personnel = h.Medecin_En_Charge
              INNER JOIN patient p ON pa.Patient_PreAdmi = p.Num_SecuSocial_Patient
              INNER JOIN civilité c ON c.ID_Civilité = p.Civilité_Patient
              WHERE h.Date_Hospitalisation >= ? 
              AND h.Medecin_En_Charge = ? 
              ORDER BY h.Date_Hospitalisation ASC";

$stmtAVenir = mysqli_prepare($conn, $sqlAVenir);
mysqli_stmt_bind_param($stmtAVenir, "si", $today, $user_id); // "si" car string pour date, int pour ID
mysqli_stmt_execute($stmtAVenir);
$resultAVenir = mysqli_stmt_get_result($stmtAVenir);

/* 2. Pré-admissions terminées de l'utilisateur connecté */
$sqlTerminees = "SELECT p.Num_SecuSocial_Patient, c.Libellé_Civilité, p.Nom_Naissance, 
                    p.Nom_Epouse, h.Date_Hospitalisation, h.Heure_Hospitalisation, 
                    ht.Libellé_TypeHospitalisation 
                 FROM preadmission pa
                 INNER JOIN hospitalisation h ON h.ID_Hospitalisation = pa.Hospitalisation_PreAdmi
                 INNER JOIN typehospitalisation ht ON ht.ID_TypeHospitalisation = h.TypeHospitalisation
                 INNER JOIN personnel prs ON prs.ID_Personnel = h.Medecin_En_Charge
                 INNER JOIN patient p ON pa.Patient_PreAdmi = p.Num_SecuSocial_Patient
                 INNER JOIN civilité c ON c.ID_Civilité = p.Civilité_Patient
                 WHERE h.Date_Hospitalisation < ? 
                 AND h.Medecin_En_Charge = ? 
                 ORDER BY h.Date_Hospitalisation ASC";

$stmtTerminees = mysqli_prepare($conn, $sqlTerminees);
mysqli_stmt_bind_param($stmtTerminees, "si", $today, $user_id);
mysqli_stmt_execute($stmtTerminees);
$resultTerminees = mysqli_stmt_get_result($stmtTerminees);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Pré-admission - Mes patients</title>
    <link rel="stylesheet" href="../CSS/list-admission.css">
    <link rel="stylesheet" href="../INCLUDES/CSS/header.css">
</head>

<body>
    <div class="dashboard-container">
        <?php require_once "../INCLUDES/header.php"; ?>
        <div class="content">

            <h1>Mes pré-admissions à venir</h1>
            <div class="card-container">
                <?php if (mysqli_num_rows($resultAVenir) === 0): ?>
                    <p class="no-data">Aucune pré-admission à venir.</p>
                <?php else: ?>
                    <?php while ($p = mysqli_fetch_assoc($resultAVenir)): ?>
                        <div class="card">
                            <div class="card-actions">
                                <a href="generer_pdf.php?id=<?= urlencode($p['Num_SecuSocial_Patient']) ?>" title="Exporter en PDF" target="_blank">
                                    <img src="../INCLUDES/ICONS/export.png" alt="PDF">
                                </a>
                            </div>
                            
                            <h3><?= htmlspecialchars($p['Libellé_Civilité']) ?> <?= htmlspecialchars(!empty($p['Nom_Epouse']) ? $p['Nom_Epouse'] : $p['Nom_Naissance']) ?></h3>
                            <p><strong>Date et Heure :</strong> <?= htmlspecialchars($p['Date_Hospitalisation']) ?> - <?= htmlspecialchars($p['Heure_Hospitalisation']) ?></p>
                            <p><strong>Type d'hospitalisation :</strong> <?= htmlspecialchars($p['Libellé_TypeHospitalisation']) ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>

            <h1>Mes pré-admissions terminées</h1>
            <div class="card-container">
                <?php if (mysqli_num_rows($resultTerminees) === 0): ?>
                    <p class="no-data">Aucune pré-admission terminée.</p>
                <?php else: ?>
                    <?php while ($p = mysqli_fetch_assoc($resultTerminees)): ?>
                        <div class="card finished">
                            <h3><?= htmlspecialchars($p['Libellé_Civilité']) ?> <?= htmlspecialchars(!empty($p['Nom_Epouse']) ? $p['Nom_Epouse'] : $p['Nom_Naissance']) ?></h3>
                            <p><strong>Date et Heure :</strong> <?= htmlspecialchars($p['Date_Hospitalisation']) ?> - <?= htmlspecialchars($p['Heure_Hospitalisation']) ?></p>
                            <p><strong>Type d'hospitalisation :</strong> <?= htmlspecialchars($p['Libellé_TypeHospitalisation']) ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>