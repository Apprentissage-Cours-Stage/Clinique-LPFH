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

$today = date('Y-m-d');
$user_id = $_SESSION['user_id']; // ID du Chef connecté

// 1. Récupérer le service du Chef connecté
$sqlService = "SELECT ID_Service FROM personnel WHERE ID_Personnel = ?";
$stmtServ = mysqli_prepare($conn, $sqlService);
mysqli_stmt_bind_param($stmtServ, "i", $user_id);
mysqli_stmt_execute($stmtServ);
$resServ = mysqli_stmt_get_result($stmtServ);
$rowServ = mysqli_fetch_assoc($resServ);
$service_id = $rowServ ? $rowServ['ID_Service'] : 0;

// 2. Gestion du filtre par mois
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : ''; // Format attendu : "YYYY-MM"

// Clause SQL dynamique pour le mois
$monthCondition = "";
if (!empty($selectedMonth)) {
    $monthCondition = " AND DATE_FORMAT(h.Date_Hospitalisation, '%Y-%m') = ? ";
}

/* --- 1. Pré-admissions à venir du SERVICE --- */
$sqlAVenir = "SELECT p.Num_SecuSocial_Patient, c.Libellé_Civilité, p.Nom_Naissance, 
                    p.Nom_Epouse, h.Date_Hospitalisation, h.Heure_Hospitalisation, 
                    ht.Libellé_TypeHospitalisation, prs.Nom_Personnel
              FROM preadmission pa
              INNER JOIN hospitalisation h ON h.ID_Hospitalisation = pa.Hospitalisation_PreAdmi
              INNER JOIN typehospitalisation ht ON ht.ID_TypeHospitalisation = h.TypeHospitalisation
              INNER JOIN personnel prs ON prs.ID_Personnel = h.Medecin_En_Charge
              INNER JOIN patient p ON pa.Patient_PreAdmi = p.Num_SecuSocial_Patient
              INNER JOIN civilité c ON c.ID_Civilité = p.Civilité_Patient
              WHERE h.Date_Hospitalisation >= ? 
              AND prs.ID_Service = ? 
              $monthCondition
              ORDER BY h.Date_Hospitalisation ASC";

$stmtAVenir = mysqli_prepare($conn, $sqlAVenir);

if (!empty($selectedMonth)) {
    mysqli_stmt_bind_param($stmtAVenir, "sis", $today, $service_id, $selectedMonth);
} else {
    mysqli_stmt_bind_param($stmtAVenir, "si", $today, $service_id);
}
mysqli_stmt_execute($stmtAVenir);
$resultAVenir = mysqli_stmt_get_result($stmtAVenir);


/* --- 2. Pré-admissions terminées du SERVICE --- */
$sqlTerminees = "SELECT p.Num_SecuSocial_Patient, c.Libellé_Civilité, p.Nom_Naissance, 
                    p.Nom_Epouse, h.Date_Hospitalisation, h.Heure_Hospitalisation, 
                    ht.Libellé_TypeHospitalisation, prs.Nom_Personnel
                 FROM preadmission pa
                 INNER JOIN hospitalisation h ON h.ID_Hospitalisation = pa.Hospitalisation_PreAdmi
                 INNER JOIN typehospitalisation ht ON ht.ID_TypeHospitalisation = h.TypeHospitalisation
                 INNER JOIN personnel prs ON prs.ID_Personnel = h.Medecin_En_Charge
                 INNER JOIN patient p ON pa.Patient_PreAdmi = p.Num_SecuSocial_Patient
                 INNER JOIN civilité c ON c.ID_Civilité = p.Civilité_Patient
                 WHERE h.Date_Hospitalisation < ? 
                 AND prs.ID_Service = ? 
                 $monthCondition
                 ORDER BY h.Date_Hospitalisation ASC";

$stmtTerminees = mysqli_prepare($conn, $sqlTerminees);

if (!empty($selectedMonth)) {
    mysqli_stmt_bind_param($stmtTerminees, "sis", $today, $service_id, $selectedMonth);
} else {
    mysqli_stmt_bind_param($stmtTerminees, "si", $today, $service_id);
}
mysqli_stmt_execute($stmtTerminees);
$resultTerminees = mysqli_stmt_get_result($stmtTerminees);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Pré-admission - Gestionnaire de service</title>
    <link rel="stylesheet" href="../CSS/list-admission.css">
    <link rel="stylesheet" href="../INCLUDES/CSS/header.css">
</head>

<body>
    <div class="dashboard-container">
        <?php require_once "../INCLUDES/header.php"; ?>
        <div class="content">

            <h1>Tableau de bord du service</h1>

            <form method="GET" action="" class="filter-bar">
                <label for="month">Filtrer par mois d'admission :</label>
                <select name="month" id="month">
                    <option value="">-- Tous les mois --</option>
                    <?php
                    // On génère la liste des 6 derniers mois et des 6 mois à venir
                    for ($i = -6; $i <= 6; $i++) {
                        $timestamp = strtotime("$i months");
                        $val = date('Y-m', $timestamp);
                        
                        // Traduction sommaire en français
                        $txtMonths = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
                        $monthIndex = (int)date('m', $timestamp) - 1;
                        $formattedText = $txtMonths[$monthIndex] . ' ' . date('Y', $timestamp);

                        $selected = ($val === $selectedMonth) ? 'selected' : '';
                        echo "<option value=\"$val\" $selected>$formattedText</option>";
                    }
                    ?>
                </select>
                <button type="submit">Filtrer</button>
                <?php if (!empty($selectedMonth)): ?>
                    <a href="?" class="reset-btn">❌ Réinitialiser</a>
                <?php endif; ?>
            </form>


            <h2>Pré-admissions à venir</h2>
            <div class="card-container">
                <?php if (mysqli_num_rows($resultAVenir) === 0): ?>
                    <p class="no-data">Aucune pré-admission à venir pour ce mois.</p>
                <?php else: ?>
                    <?php while ($p = mysqli_fetch_assoc($resultAVenir)): ?>
                        <div class="card">
                            <div class="card-actions">
                                <a href="../INCLUDES/generatePDF.php?id=<?= urlencode($p['Num_SecuSocial_Patient']) ?>" title="Exporter en PDF" target="_blank">
                                    <img src="../INCLUDES/ICONS/export.png" alt="PDF">
                                </a>
                            </div>
                            <h3><?= htmlspecialchars($p['Libellé_Civilité']) ?> <?= htmlspecialchars(!empty($p['Nom_Epouse']) ? $p['Nom_Epouse'] : $p['Nom_Naissance']) ?></h3>
                            <p><strong>Date et Heure :</strong> <?= htmlspecialchars($p['Date_Hospitalisation']) ?> - <?= htmlspecialchars($p['Heure_Hospitalisation']) ?></p>
                            <p><strong>Type :</strong> <?= htmlspecialchars($p['Libellé_TypeHospitalisation']) ?></p>
                            <p><strong>Docteur en charge :</strong> <?= htmlspecialchars($p['Nom_Personnel']) ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>

            <h2>Pré-admissions terminées</h2>
            <div class="card-container">
                <?php if (mysqli_num_rows($resultTerminees) === 0): ?>
                    <p class="no-data">Aucune pré-admission passée trouvée.</p>
                <?php else: ?>
                    <?php while ($p = mysqli_fetch_assoc($resultTerminees)): ?>
                        <div class="card finished">
                            <h3><?= htmlspecialchars($p['Libellé_Civilité']) ?> <?= htmlspecialchars(!empty($p['Nom_Epouse']) ? $p['Nom_Epouse'] : $p['Nom_Naissance']) ?></h3>
                            <p><strong>Date et Heure :</strong> <?= htmlspecialchars($p['Date_Hospitalisation']) ?> - <?= htmlspecialchars($p['Heure_Hospitalisation']) ?></p>
                            <p><strong>Type :</strong> <?= htmlspecialchars($p['Libellé_TypeHospitalisation']) ?></p>
                            <p><strong>Docteur :</strong> <?= htmlspecialchars($p['Nom_Personnel']) ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>