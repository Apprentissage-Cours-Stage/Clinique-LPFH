<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$basePath = "../";
$context = "DOCTOR";
$shownContext = "Docteur";
$username = $_SESSION['username'];

require_once "../INCLUDES/db.php"; // Utilise $conn (mysqli)

/**
 * 1. Récupération de l'ID_Personnel du docteur connecté
 */
$id_docteur = null;
$nom_docteur = "Non identifié";

$sql_doc = "SELECT p.ID_Personnel, p.Nom_Personnel, p.Prénom_Personnel 
            FROM Personnel p
            JOIN Utilisateurs u ON p.ID_Personnel = u.ID_Employé
            WHERE u.Identifiant_User = ? LIMIT 1";

$stmt = $conn->prepare($sql_doc);
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $id_docteur = $row['ID_Personnel'];
    $nom_docteur = $row['Prénom_Personnel'] . " " . $row['Nom_Personnel'];
}
$stmt->close();

/**
 * 2. Statistiques personnelles du docteur
 */
$nbMesPatients = 0;
$nbHospitalisationsJour = 0;

if ($id_docteur) {
    // Nombre total de patients dont il est en charge
    $stmtPatients = $conn->prepare("SELECT COUNT(DISTINCT ID_Patient) as total FROM Hospitalisation WHERE Medecin_En_Charge = ?");
    $stmtPatients->bind_param("i", $id_docteur);
    $stmtPatients->execute();
    $nbMesPatients = $stmtPatients->get_result()->fetch_assoc()['total'];
    $stmtPatients->close();

    // Hospitalisations prévues ou débutant aujourd'hui
    $today = date('Y-m-d');
    $stmtToday = $conn->prepare("SELECT COUNT(*) as total FROM Hospitalisation WHERE Medecin_En_Charge = ? AND Date_Hospitalisation >= ?");
    $stmtToday->bind_param("is", $id_docteur, $today);
    $stmtToday->execute();
    $nbHospitalisationsJour = $stmtToday->get_result()->fetch_assoc()['total'];
    $stmtToday->close();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo $shownContext; ?></title>
    <link rel="stylesheet" href="../CSS/dashboard.css">
    <link rel="stylesheet" href="../INCLUDES/CSS/header.css">
</head>
<body>
    <div class="dashboard-container">
        <?php require_once "../INCLUDES/header.php"; ?>
        
        <div class="main-content">
            <h1>Bonjour, Dr. <?php echo htmlspecialchars($nom_docteur); ?></h1>
            <p>Voici le suivi de vos activités pour aujourd'hui.</p>
            
            <div class="cards">
                <div class="card">
                    <h3>Mes Patients</h3>
                    <span class="stat-number"><?php echo $nbMesPatients; ?></span>
                    <p>Total dossiers suivis</p>
                </div>

                <div class="card">
                    <h3>Aujourd'hui</h3>
                    <span class="stat-number"><?php echo $nbHospitalisationsJour; ?></span>
                    <p>Nouvelles admissions</p>
                </div>

                <div class="card">
                    <h3>Disponibilité</h3>
                    <span class="stat-number" style="font-size: 1.5rem; color: #28a745;">En Service</span>
                    <p>Poste : Medecin / Clinique LPFS</p>
                </div>
            </div>

            <div class="section">
                <h2>Outils de soin</h2>
                <div class="action-buttons">
                    <a href="list-ownadmission.php" class="btn-action">Voir mes admissions</a>
                </div>
            </div>
        </div>

        <div class="background-shape"></div>
        <div class="background-shape2"></div>
    </div>
</body>
</html>