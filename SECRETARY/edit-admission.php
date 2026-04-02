<?php
session_start();

$basePath = "../";
$context = "SECRETARY";
$shownContext = "Secrétaire";

require_once "../INCLUDES/db.php";

$id_pa = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_pa <= 0) die("ID d'admission invalide.");

// 1. Récupération des données référentielles pour les menus déroulants
$liste_medecins = $conn->query("SELECT ID_Personnel, Nom_Personnel, Prénom_Personnel FROM personnel WHERE Role_Personnel = 4 ORDER BY Nom_Personnel")->fetch_all(MYSQLI_ASSOC);
$liste_civilites = $conn->query("SELECT ID_Civilité, Libellé_Civilité FROM civilité")->fetch_all(MYSQLI_ASSOC);
$liste_types_hosp = $conn->query("SELECT ID_TypeHospitalisation, Libellé_TypeHospitalisation FROM typehospitalisation")->fetch_all(MYSQLI_ASSOC);

// 2. Requête principale avec ALIAS pour Personne à Prévenir (pp) et Personne de Confiance (pc)
// 2. Requête principale mise à jour
$sql = "SELECT pa.*, p.*, h.*, cs.*, 
               /* Alias Personne à Prévenir */
               pp.ID_Personne AS id_pp, pp.Nom_Pers AS nom_pp, pp.Prénom_Pers AS prenom_pp, 
               pp.Telephone_Pers AS tel_pp, pp.Num_Adresse AS num_add_pp, pp.Rue_Adresse AS rue_add_pp, 
               pp.Ville_Adresse AS ville_add_pp, pp.Code_Postal_Pers AS cp_pp,
               /* Alias Personne de Confiance - AJOUT DES CHAMPS ADRESSE ICI */
               pc.ID_Personne AS id_pc, pc.Nom_Pers AS nom_pc, pc.Prénom_Pers AS prenom_pc, 
               pc.Telephone_Pers AS tel_pc, pc.Num_Adresse AS num_add_pc, pc.Rue_Adresse AS rue_pc, 
               pc.Ville_Adresse AS ville_pc, pc.Code_Postal_Pers AS cp_pc,
               /* Responsable & Documents */
               resp.Nom_Responsable, resp.Prenom_Responsable, resp.Telephone_Responsable, 
               resp.AdresseMail_Responsable, resp.Num_Adresse_Responsable, 
               resp.Rue_Responsable, resp.Ville_Responsable, resp.Code_Postal_Responsable,

               pj.Carte_Identité, pj.Carte_Vitale, pj.Carte_mutuelle, 
               pj.Livret_Famille, pj.Autorisation_soin, pj.Decision_juge
        FROM preadmission pa
        INNER JOIN patient p ON pa.Patient_PreAdmi = p.Num_SecuSocial_Patient
        LEFT JOIN hospitalisation h ON pa.Hospitalisation_PreAdmi = h.ID_Hospitalisation
        LEFT JOIN couverturesocial cs ON cs.Numero_Sec_Social = p.Num_SecuSocial_Patient
        LEFT JOIN personne_prevenir pp ON pp.ID_Personne = pa.Personne_aprev
        LEFT JOIN personne_prevenir pc ON pc.ID_Personne = pa.Personne_deconf
        LEFT JOIN soustutellede st ON st.ID_Patient = p.Num_SecuSocial_Patient
        LEFT JOIN responsable resp ON resp.ID_Responsable = st.ID_Responsable
        LEFT JOIN piecesjoints pj ON pj.Numéro_SecSocial_Document = p.Num_SecuSocial_Patient
        WHERE pa.Id_PreAdmin = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_pa);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) die("Dossier introuvable.");

// 3. Calcul de la majorité (18 ans)
$est_majeur = false;
if (!empty($data['Date_Naissance'])) {
    $birthDate = new DateTime($data['Date_Naissance']);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
    $est_majeur = ($age >= 18);
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Édition Admission #<?php echo $id_pa; ?></title>
    <link rel="stylesheet" href="../INCLUDES/CSS/header.css">
    <link rel="stylesheet" href="../CSS/edit-admission.css">
</head>

<body>
    <div class="dashboard-container">
        <?php require_once "../INCLUDES/header.php"; ?>

        <div class="main-content">
            <div class="action-header" style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
                <a href="list-admission.php" class="btn-back" style="text-decoration: none; color: #555; display: flex; align-items: center; gap: 8px; font-weight: 600; transition: 0.3s;">
                    <span style="font-size: 1.2em;">←</span> Retour à la liste
                </a>
            </div>
            <h1>Dossier Patient : <?php echo htmlspecialchars($data['Nom_Naissance'] . " " . $data['Prénom_Patient']); ?></h1>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert-success">✓ Toutes les modifications ont été enregistrées.</div>
            <?php endif; ?>

            <div class="form-card">
                <h3><span class="icon">👤</span> État Civil & Coordonnées</h3>
                <form action="../INCLUDES/updateAdmission.php" method="POST">
                    <input type="hidden" name="from" value="SECRETARY">
                    <input type="hidden" name="type" value="patient">
                    <input type="hidden" name="id_pa" value="<?php echo $id_pa; ?>">
                    <input type="hidden" name="pk" value="<?php echo $data['Num_SecuSocial_Patient']; ?>">

                    <div class="grid">
                        <div class="form-group">
                            <label>Civilité</label>
                            <select name="Civilité_Patient">
                                <?php foreach ($liste_civilites as $c): ?>
                                    <option value="<?php echo $c['ID_Civilité']; ?>" <?php echo ($c['ID_Civilité'] == $data['Civilité_Patient']) ? 'selected' : ''; ?>><?php echo $c['Libellé_Civilité']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nom de naissance</label>
                            <input type="text" name="Nom_Naissance" value="<?php echo htmlspecialchars($data['Nom_Naissance']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Nom d'épouse</label>
                            <input type="text" name="Nom_Epouse" value="<?php echo htmlspecialchars($data['Nom_Epouse'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Prénom</label>
                            <input type="text" name="Prénom_Patient" value="<?php echo htmlspecialchars($data['Prénom_Patient']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Date de Naissance</label>
                            <input type="date" name="Date_Naissance" value="<?php echo $data['Date_Naissance']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="Adresse_Mail" value="<?php echo htmlspecialchars($data['Adresse_Mail']); ?>">
                        </div>
                        <div class="form-group">
                            <label>Téléphone</label>
                            <input type="text" name="Telephone_Patient" value="<?php echo htmlspecialchars($data['Telephone_Patient']); ?>">
                        </div>
                    </div>
                    <fieldset style="margin-top:20px;">
                        <legend>Adresse Postale Patient</legend>
                        <div class="grid">
                            <div class="form-group"><label>N°</label><input type="text" name="Num_Adresse" value="<?php echo $data['Num_Adresse']; ?>"></div>
                            <div class="form-group"><label>Rue</label><input type="text" name="Rue_Adresse" value="<?php echo htmlspecialchars($data['Rue_Adresse']); ?>"></div>
                            <div class="form-group"><label>Ville</label><input type="text" name="Ville_Patient" value="<?php echo htmlspecialchars($data['Ville_Adresse']); ?>"></div>
                            <div class="form-group"><label>Code Postal</label><input type="text" name="Code_Postal" value="<?php echo $data['Code_Postal']; ?>"></div>
                        </div>
                    </fieldset>
                    <button type="submit" class="btn-save">Mettre à jour le profil</button>
                </form>
            </div>

            <div class="form-card">
                <h3><span class="icon">🏥</span> Détails de l'Hospitalisation</h3>
                <form action="../INCLUDES/updateAdmission.php" method="POST">
                    <input type="hidden" name="from" value="SECRETARY">
                    <input type="hidden" name="type" value="hospitalisation">
                    <input type="hidden" name="id_pa" value="<?php echo $id_pa; ?>">
                    <input type="hidden" name="pk" value="<?php echo $data['ID_Hospitalisation']; ?>">

                    <div class="grid">
                        <div class="form-group">
                            <label>Type d'Hospitalisation</label>
                            <select name="TypeHospitalisation">
                                <?php foreach ($liste_types_hosp as $th): ?>
                                    <option value="<?php echo $th['ID_TypeHospitalisation']; ?>" <?php echo ($th['ID_TypeHospitalisation'] == $data['TypeHospitalisation']) ? 'selected' : ''; ?>><?php echo $th['Libellé_TypeHospitalisation']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date Entrée</label>
                            <input type="date" name="Date_Hospitalisation" value="<?php echo $data['Date_Hospitalisation']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Heure Entrée</label>
                            <input type="time" name="Heure_Hospitalisation" value="<?php echo $data['Heure_Hospitalisation']; ?>">
                        </div>
                        <div class="form-group">
                            <label>Médecin Responsable</label>
                            <select name="Medecin_En_Charge">
                                <?php foreach ($liste_medecins as $med): ?>
                                    <option value="<?php echo $med['ID_Personnel']; ?>" <?php echo ($med['ID_Personnel'] == $data['Medecin_En_Charge']) ? 'selected' : ''; ?>>
                                        Dr. <?php echo htmlspecialchars($med['Nom_Personnel'] . " " . $med['Prénom_Personnel']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-save">Mettre à jour le séjour</button>
                </form>
            </div>

            <div class="form-card">
                <h3><span class="icon">💳</span> Couverture Sociale</h3>
                <form action="../INCLUDES/updateAdmission.php" method="POST">
                    <input type="hidden" name="from" value="SECRETARY">
                    <input type="hidden" name="type" value="couverture">
                    <input type="hidden" name="id_pa" value="<?php echo $id_pa; ?>">
                    <input type="hidden" name="pk" value="<?php echo $data['Num_SecuSocial_Patient']; ?>">

                    <div class="grid">
                        <div class="form-group">
                            <label>Organisme Sécurité Sociale</label>
                            <input type="text" name="Nom_OrganismeSecuSocial" value="<?php echo htmlspecialchars($data['Nom_OrganismeSecuSocial'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Nom de la Mutuelle</label>
                            <input type="text" name="Nom_Mutuelle" value="<?php echo htmlspecialchars($data['Nom_Mutuelle'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Numéro Adhérent Mutuelle</label>
                            <input type="text" name="Numéro_Adhérent" value="<?php echo htmlspecialchars($data['Numéro_Adhérent'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="grid" style="margin-top:15px;">
                        <div class="form-group">
                            <label>Assuré ?</label>
                            <select name="Patient_Assuré">
                                <option value="1" <?php echo ($data['Patient_Assuré'] == 1) ? 'selected' : ''; ?>>Oui</option>
                                <option value="0" <?php echo ($data['Patient_Assuré'] == 0) ? 'selected' : ''; ?>>Non</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Prise en charge ALD ?</label>
                            <select name="Patient_ADL">
                                <option value="1" <?php echo ($data['Patient_ADL'] == 1) ? 'selected' : ''; ?>>Oui</option>
                                <option value="0" <?php echo ($data['Patient_ADL'] == 0) ? 'selected' : ''; ?>>Non</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-save">Mettre à jour la couverture</button>
                </form>
            </div>

            <div class="form-card">
                <h3><span class="icon">👥</span> Entourage & Responsables</h3>
                <form action="../INCLUDES/updateAdmission.php" method="POST">
                    <input type="hidden" name="from" value="SECRETARY">
                    <input type="hidden" name="type" value="entourage_full">
                    <input type="hidden" name="id_pa" value="<?php echo $id_pa; ?>">

                    <input type="hidden" name="pk_pp" value="<?php echo $data['id_pp']; ?>">
                    <input type="hidden" name="pk_pc" value="<?php echo $data['id_pc']; ?>">

                    <div class="grid">
                        <fieldset>
                            <legend>Personne à prévenir (Urgence)</legend>
                            <div class="grid">
                                <div class="form-group"><label>Nom</label><input type="text" name="nom_pp" value="<?php echo htmlspecialchars($data['nom_pp'] ?? ''); ?>"></div>
                                <div class="form-group"><label>Prénom</label><input type="text" name="prenom_pp" value="<?php echo htmlspecialchars($data['prenom_pp'] ?? ''); ?>"></div>
                                <div class="form-group"><label>Tél</label><input type="text" name="tel_pp" value="<?php echo htmlspecialchars($data['tel_pp'] ?? ''); ?>"></div>
                            </div>
                            <div class="grid" style="margin-top:10px;">
                                <div class="form-group" style="flex: 0 0 80px;">
                                    <label>N°</label>
                                    <input type="text" name="num_pp" value="<?php echo htmlspecialchars($data['num_add_pp'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Rue</label>
                                    <input type="text" name="rue_pp" value="<?php echo htmlspecialchars($data['rue_add_pp'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Ville</label>
                                    <input type="text" name="ville_pp" value="<?php echo htmlspecialchars($data['ville_add_pp'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>CP</label>
                                    <input type="text" name="cp_pp" value="<?php echo htmlspecialchars($data['cp_pp'] ?? ''); ?>">
                                </div>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>Personne de confiance (Loi Kouchner)</legend>
                            <div class="grid">
                                <div class="form-group"><label>Nom</label><input type="text" name="nom_pc" value="<?php echo htmlspecialchars($data['nom_pc'] ?? ''); ?>"></div>
                                <div class="form-group"><label>Prénom</label><input type="text" name="prenom_pc" value="<?php echo htmlspecialchars($data['prenom_pc'] ?? ''); ?>"></div>
                                <div class="form-group"><label>Tél</label><input type="text" name="tel_pc" value="<?php echo htmlspecialchars($data['tel_pc'] ?? ''); ?>"></div>
                            </div>
                            <div class="grid" style="margin-top:10px;">
                                <div class="form-group" style="flex: 0 0 80px;">
                                    <label>N°</label>
                                    <input type="text" name="num_pc" value="<?php echo htmlspecialchars($data['num_add_pc'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Rue</label>
                                    <input type="text" name="rue_pc" value="<?php echo htmlspecialchars($data['rue_pc'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Ville</label>
                                    <input type="text" name="ville_pc" value="<?php echo htmlspecialchars($data['ville_pc'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>CP</label>
                                    <input type="text" name="cp_pc" value="<?php echo htmlspecialchars($data['cp_pc'] ?? ''); ?>">
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <?php if (!$est_majeur): ?>
                        <fieldset style="margin-top: 15px;">
                            <legend>Responsable Légal (Patient Mineur)</legend>
                            <div class="grid">
                                <div class="form-group">
                                    <label>Nom</label>
                                    <input type="text" name="Nom_Responsable" value="<?php echo htmlspecialchars($data['Nom_Responsable'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Prénom</label>
                                    <input type="text" name="Prenom_Responsable" value="<?php echo htmlspecialchars($data['Prenom_Responsable'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Téléphone</label>
                                    <input type="text" name="Telephone_Responsable" value="<?php echo htmlspecialchars($data['Telephone_Responsable'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="AdresseMail_Responsable" value="<?php echo htmlspecialchars($data['AdresseMail_Responsable'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="grid" style="margin-top:10px;">
                                <div class="form-group" style="flex: 0 0 80px;">
                                    <label>N°</label>
                                    <input type="text" name="Num_Adresse_Responsable" value="<?php echo htmlspecialchars($data['Num_Adresse_Responsable'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Rue</label>
                                    <input type="text" name="Rue_Responsable" value="<?php echo htmlspecialchars($data['Rue_Responsable'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Ville</label>
                                    <input type="text" name="Ville_Responsable" value="<?php echo htmlspecialchars($data['Ville_Responsable'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Code Postal</label>
                                    <input type="text" name="Code_Postal_Responsable" value="<?php echo htmlspecialchars($data['Code_Postal_Responsable'] ?? ''); ?>">
                                </div>
                            </div>
                        </fieldset>
                    <?php endif; ?>

                    <button type="submit" class="btn-save">Mettre à jour l'entourage</button>
                </form>
            </div>

            <div class="form-card">
                <h3><span class="icon">📂</span> Pièces Jointes</h3>
                <form action="../INCLUDES/updateAdmission.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="type" value="documents">
                    <input type="hidden" name="from" value="SECRETARY">
                    <input type="hidden" name="id_pa" value="<?php echo $id_pa; ?>">
                    <input type="hidden" name="pk" value="<?php echo $data['Num_SecuSocial_Patient']; ?>">

                    <div class="grid">
                        <div class="form-group">
                            <label>Carte d'Identité</label>
                            <div class="doc-status"><?php echo !empty($data['Carte_Identité']) ? "✅ Présent" : "❌ Absent"; ?> <input type="file" name="Carte_Identité"></div>
                        </div>
                        <div class="form-group">
                            <label>Carte Vitale</label>
                            <div class="doc-status"><?php echo !empty($data['Carte_Vitale']) ? "✅ Présent" : "❌ Absent"; ?> <input type="file" name="Carte_Vitale"></div>
                        </div>
                        <div class="form-group">
                            <label>Carte Mutuelle</label>
                            <div class="doc-status"><?php echo !empty($data['Carte_mutuelle']) ? "✅ Présent" : "❌ Absent"; ?> <input type="file" name="Carte_mutuelle"></div>
                        </div>
                    </div>

                    <?php if (!$est_majeur): ?>
                        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
                        <p><strong>Documents spécifiques (Patient Mineur) :</strong></p>
                        <div class="grid">
                            <div class="form-group">
                                <label>Livret de Famille</label>
                                <div class="doc-status"><?php echo !empty($data['Livret_Famille']) ? "✅ Présent" : "❌ Absent"; ?> <input type="file" name="Livret_Famille"></div>
                            </div>
                            <div class="form-group">
                                <label>Autorisation de soin</label>
                                <div class="doc-status"><?php echo !empty($data['Autorisation_soin']) ? "✅ Présent" : "❌ Absent"; ?> <input type="file" name="Autorisation_soin"></div>
                            </div>
                            <div class="form-group">
                                <label>Décision du juge</label>
                                <div class="doc-status"><?php echo !empty($data['Decision_juge']) ? "✅ Présent" : "❌ Absent"; ?> <input type="file" name="Decision_juge"></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn-save" style="margin-top: 15px;">Télécharger les fichiers</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>