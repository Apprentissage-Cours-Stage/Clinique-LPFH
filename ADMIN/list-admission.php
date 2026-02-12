<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
//Utilisation de la BDD
require_once "../INCLUDES/db.php";

$today = date('Y-m-d');

/* Pré-admissions à venir */
$sqlAVenir = "SELECT c.Libellé_Civilité, p.Nom_Naissance, 
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
$sqlTerminees = "SELECT c.Libellé_Civilité, p.Nom_Naissance, 
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
</head>

<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <img src="../INCLUDES/IMAGES/LPFSLogo.png" alt="Logo Clinique" class="logo">
            <h2>Panel Administrateur</h2>
            <ul class="menu">
                <li><a href="dashboard-admin.php" style="color:#fff; text-decoration:none;">Accueil</a></li>
                <li><a href="add-admission.php" style="color:#fff; text-decoration:none;">Enregistrer une Pré-admission</a></li>
                <li><a href="list-admission.php" style="color:#fff; text-decoration:none;">Liste des Pré-admissions</a></li>
                <li>Enregistrer un nouveau personnel/utilisateur</li>
                <li>Liste du personnels/utilisateurs</li>
                <li>Enregistrer un nouveau service</li>
                <li>Liste des services</li>
                <li><a href="../logout.php" style="color:#fff; text-decoration:none;">Déconnexion</a></li>
            </ul>
        </div>
        <div class="content">

            <h1>Pré-admissions à venir</h1>
            <div class="card-container">
                <?php while ($p = mysqli_fetch_assoc($resultAVenir)): ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($p['Libellé_Civilité']) ?> <?= htmlspecialchars(!empty($p['Nom_Epouse']) ? $p['Nom_Epouse'] : $p['Nom_Naissance']) ?></h3>
                        <p><strong>Date et Heure de l'hospitalisation :</strong> <?= $p['Date_Hospitalisation'] ?> - <?= $p['Heure_Hospitalisation'] ?></p>
                        <p><strong>Type d'hospitalisation :</strong> <?= htmlspecialchars($p['Libellé_TypeHospitalisation']) ?></p>
                        <p><strong>Docteur :</strong> <?= htmlspecialchars($p['Nom_Personnel']) ?></p>
                    </div>
                <?php endwhile; ?>
            </div>

            <h1>Pré-admissions terminées</h1>
            <div class="card-container">
                <?php while ($p = mysqli_fetch_assoc($resultTerminees)): ?>
                    <div class="card finished">
                        <h3><?= htmlspecialchars($p['Libellé_Civilité']) ?> <?= htmlspecialchars(!empty($p['Nom_Epouse']) ? $p['Nom_Epouse'] : $p['Nom_Naissance']) ?></h3>
                        <p><strong>Date et Heure de l'hospitalisation :</strong> <?= $p['Date_Hospitalisation'] ?> - <?= $p['Heure_Hospitalisation'] ?></p>
                        <p><strong>Type d'hospitalisation :</strong> <?= htmlspecialchars($p['Libellé_TypeHospitalisation']) ?></p>
                        <p><strong>Docteur :</strong> <?= htmlspecialchars($p['Nom_Personnel']) ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>