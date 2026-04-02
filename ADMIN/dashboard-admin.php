<?php
session_start();
require_once "../INCLUDES/db.php";

$id_pa = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Requête exhaustive avec noms de tables et colonnes en minuscules
$sql = "SELECT pa.*, p.*, h.*, cs.*, pp.*, rp.*, pj.*
        FROM preadmission pa
        JOIN patient p ON pa.patient_preadmi = p.num_secusocial_patient
        JOIN hospitalisation h ON pa.hospitalisation_preadmi = h.id_hospitalisation
        LEFT JOIN couverturesocial cs ON p.num_secusocial_patient = cs.numero_sec_social
        LEFT JOIN personne_prevenir pp ON pa.personne_aprev = pp.id_personne
        LEFT JOIN soustutellede st ON p.num_secusocial_patient = st.id_patient
        LEFT JOIN responsable rp ON st.id_responsable = rp.id_responsable
        LEFT JOIN piecesjoints pj ON p.num_secusocial_patient = pj.numero_secsocial_document
        WHERE pa.id_preadmin = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_pa);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) die("Erreur : Admission introuvable.");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Dossier : <?php echo htmlspecialchars($data['nom_naissance']); ?></title>
    <link rel="stylesheet" href="../CSS/dashboard.css">
    <link rel="stylesheet" href="../INCLUDES/CSS/header.css">
    <style>
        .form-card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-left: 5px solid #005f99; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #333; }
        input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .btn-save { background: #005f99; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; margin-top: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="main-content">
        <h1>Dossier Patient : <?php echo htmlspecialchars($data['nom_naissance'] . " " . $data['prenom_patient']); ?></h1>

        <div class="form-card">
            <h3>État Civil du Patient</h3>
            <form action="update_admission.php" method="POST">
                <input type="hidden" name="type" value="patient">
                <input type="hidden" name="id_pa" value="<?php echo $id_pa; ?>">
                <input type="hidden" name="pk" value="<?php echo $data['num_secusocial_patient']; ?>">
                <div class="grid">
                    <div><label>Nom</label><input type="text" name="nom_naissance" value="<?php echo $data['nom_naissance']; ?>"></div>
                    <div><label>Prénom</label><input type="text" name="prenom_patient" value="<?php echo $data['prenom_patient']; ?>"></div>
                    <div><label>Téléphone</label><input type="text" name="telephone_patient" value="<?php echo $data['telephone_patient']; ?>"></div>
                    <div><label>Email</label><input type="email" name="adresse_mail" value="<?php echo $data['adresse_mail']; ?>"></div>
                </div>
                <button type="submit" class="btn-save">Mettre à jour l'identité</button>
            </form>
        </div>

        <div class="form-card">
            <h3>Détails du Séjour</h3>
            <form action="update_admission.php" method="POST">
                <input type="hidden" name="type" value="hospitalisation">
                <input type="hidden" name="id_pa" value="<?php echo $id_pa; ?>">
                <input type="hidden" name="pk" value="<?php echo $data['id_hospitalisation']; ?>">
                <div class="grid">
                    <div><label>Date</label><input type="date" name="date_hospitalisation" value="<?php echo $data['date_hospitalisation']; ?>"></div>
                    <div><label>Heure</label><input type="time" name="heure_hospitalisation" value="<?php echo $data['heure_hospitalisation']; ?>"></div>
                    <div><label>ID Médecin</label><input type="number" name="medecin_en_charge" value="<?php echo $data['medecin_en_charge']; ?>"></div>
                </div>
                <button type="submit" class="btn-save">Mettre à jour le séjour</button>
            </form>
        </div>

        <div class="form-card">
            <h3>Entourage & Responsable</h3>
            <form action="update_admission.php" method="POST">
                <input type="hidden" name="type" value="entourage">
                <input type="hidden" name="id_pa" value="<?php echo $id_pa; ?>">
                <div class="grid">
                    <fieldset>
                        <legend>À prévenir</legend>
                        <label>Nom</label><input type="text" name="nom_pers" value="<?php echo $data['nom_pers']; ?>">
                        <label>Tel</label><input type="text" name="telephone_pers" value="<?php echo $data['telephone_pers']; ?>">
                    </fieldset>
                    <fieldset>
                        <legend>Responsable Légal</legend>
                        <label>Nom</label><input type="text" name="nom_responsable" value="<?php echo $data['nom_responsable']; ?>">
                        <label>Tel</label><input type="text" name="telephone_responsable" value="<?php echo $data['telephone_responsable']; ?>">
                    </fieldset>
                </div>
                <button type="submit" class="btn-save">Mettre à jour l'entourage</button>
            </form>
        </div>

        <div class="form-card">
            <h3>Pièces Jointes</h3>
            <form action="update_admission.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="type" value="documents">
                <input type="hidden" name="id_pa" value="<?php echo $id_pa; ?>">
                <input type="hidden" name="pk" value="<?php echo $data['num_secusocial_patient']; ?>">
                <p>Status : <?php echo $data['carte_identite'] ? "CI ✅" : "CI ❌"; ?> | <?php echo $data['carte_vitale'] ? "Vitale ✅" : "Vitale ❌"; ?></p>
                <div class="grid">
                    <div><label>Carte d'identité</label><input type="file" name="carte_identite"></div>
                    <div><label>Carte Vitale</label><input type="file" name="carte_vitale"></div>
                </div>
                <button type="submit" class="btn-save" style="background:#28a745;">Télécharger les fichiers</button>
            </form>
        </div>
    </div>
</body>
</html>