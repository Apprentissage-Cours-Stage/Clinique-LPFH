<?php
session_start();
require_once "db.php";
require_once "SERVICES/PDFService.php";

$numSecuPatient = $_GET['id'] ?? null;

if (!$numSecuPatient) {
    die("Erreur : Aucun ID de patient fourni.");
}

$sql = "SELECT 
            p.*, c.Libellé_Civilité,
            cs.Nom_OrganismeSecuSocial, cs.Patient_Assuré, cs.Patient_ADL, cs.Nom_Mutuelle, cs.Numéro_Adhérent,
            h.Date_Hospitalisation, h.Heure_Hospitalisation, h.ChambreOccupé,
            th.Libellé_TypeHospitalisation,
            pers.Nom_Personnel, pers.Prénom_Personnel,
            pp.Nom_Pers, pp.Prénom_Pers, pp.Telephone_Pers, pp.Num_Adresse AS Num_Add_PP, pp.Rue_Adresse AS Rue_Add_PP, pp.Ville_Adresse AS Ville_Add_PP, pp.Code_Postal_Pers,
            resp.Nom_Responsable, resp.Prenom_Responsable, resp.Telephone_Responsable, resp.AdresseMail_Responsable
        FROM patient p
        INNER JOIN civilité c ON c.ID_Civilité = p.Civilité_Patient
        LEFT JOIN couvertureSocial cs ON cs.Numero_Sec_Social = p.Num_SecuSocial_Patient
        LEFT JOIN preadmission pa ON pa.Patient_PreAdmi = p.Num_SecuSocial_Patient
        LEFT JOIN hospitalisation h ON h.ID_Hospitalisation = pa.Hospitalisation_PreAdmi
        LEFT JOIN typehospitalisation th ON th.ID_TypeHospitalisation = h.TypeHospitalisation
        LEFT JOIN personnel pers ON pers.ID_Personnel = h.Medecin_En_Charge
        LEFT JOIN personne_prevenir pp ON pp.ID_Personne = pa.Personne_aprev
        LEFT JOIN soustutellede st ON st.ID_Patient = p.Num_SecuSocial_Patient
        LEFT JOIN responsable resp ON resp.ID_Responsable = st.ID_Responsable
        WHERE p.Num_SecuSocial_Patient = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $numSecuPatient);
$stmt->execute();
$result = $stmt->get_result();
$patientData = $result->fetch_assoc();

if (!$patientData) {
    die("Patient introuvable.");
}

// 🎯 On s'assure que le nom de l'instanciation est synchrone avec le fichier requis
$pdfService = new PDFService();

$pdfService->genererDepuisTemplate(
    __DIR__ . '/ficheP_template.php', 
    $patientData, 
    "Fiche_" . ($patientData['Nom_Naissance'] ?? 'Patient') . ".pdf"
);