<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once "db.php";
require_once __DIR__ . '/LIBRAIRIES/vendor/autoload.php'; // Chemins du vendor
require_once "SERVICES/ExcelExporterService.php"; // Chemin vers notre nouvelle classe

$user_id = $_SESSION['user_id'];

// 1. On cherche l'ID et le nom du Service du Chef
$sqlService = "SELECT p.ID_Service, s.Libellé_Service 
               FROM personnel p 
               INNER JOIN service s ON p.ID_Service = s.ID_Service 
               WHERE p.ID_Personnel = ?";
$stmtServ = mysqli_prepare($conn, $sqlService);
mysqli_stmt_bind_param($stmtServ, "i", $user_id);
mysqli_stmt_execute($stmtServ);
$resServ = mysqli_stmt_get_result($stmtServ);
$rowServ = mysqli_fetch_assoc($resServ);

$service_id = $rowServ['ID_Service'] ?? 0;
$service_name = $rowServ['Libellé_Service'] ?? 'Service Inconnu';

if ($service_id === 0) {
    die("Erreur : Impossible de trouver le service.");
}

// 2. On rassemble toutes les admissions du service dans un tableau PHP classique
$sqlAdmi = "SELECT prs.Nom_Personnel AS Medecin, p.Nom_Naissance, p.Nom_Epouse, p.Prénom_Patient,
                   h.Date_Hospitalisation, h.Heure_Hospitalisation, ht.Libellé_TypeHospitalisation
            FROM preadmission pa
            INNER JOIN hospitalisation h ON h.ID_Hospitalisation = pa.Hospitalisation_PreAdmi
            INNER JOIN typehospitalisation ht ON ht.ID_TypeHospitalisation = h.TypeHospitalisation
            INNER JOIN personnel prs ON prs.ID_Personnel = h.Medecin_En_Charge
            INNER JOIN patient p ON pa.Patient_PreAdmi = p.Num_SecuSocial_Patient
            WHERE prs.ID_Service = ?
            ORDER BY prs.Nom_Personnel ASC, h.Date_Hospitalisation ASC";

$stmt = mysqli_prepare($conn, $sqlAdmi);
mysqli_stmt_bind_param($stmt, "i", $service_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$admissions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $admissions[] = $row;
}

// 3. On instancie notre classe et on lance le téléchargement
$exporter = new AdmissionExcelExporter($service_name, $admissions);
$exporter->renderAndDownload();