<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
//Utilisation de la BDD
require_once "../INCLUDES/db.php";
try {
    $sql_hospitype = "SELECT ID_TypeHospitalisation, Libellé_TypeHospitalisation
                      FROM TypeHospitalisation
                      ORDER BY Libellé_TypeHospitalisation ASC;";
    $result_hospitype = $conn->query($sql_hospitype);

    $sql_medecin = "SELECT P.ID_Personnel, P.Nom_Personnel, S.Libellé_Service
                    FROM Personnel P
                    INNER JOIN Service S ON P.ID_Service = S.ID_Service
                    INNER JOIN Role R ON P.Role_Personnel = R.ID_Role
                    WHERE R.Libellé_Role = 'Medecin';";
    $result_medecin = $conn->query($sql_medecin);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajout de Pré-admission - Secrétaire</title>
    <link rel="stylesheet" href="../CSS/add-admission.css">
</head>

<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <img src="../INCLUDES/IMAGES/LPFSLogo.png" alt="Logo Clinique" class="logo">
            <h2>Panel Secrétaire</h2>
            <ul class="menu">
                <li><a href="dashboard-secretary.php" style="color:#fff; text-decoration:none;">Accueil</a></li>
                <li><a href="add-admission.php" style="color:#fff; text-decoration:none;">Enregistrer une Pré-admission</a></li>
                <li>Liste des Pré-admissions</li>
                <li><a href="../logout.php" style="color:#fff; text-decoration:none;">Déconnexion</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="progress-container">
                <div class="progress-line" style="width: 0%;"></div> <!-- adapte la largeur pour refléter la progression -->
                <div class="progress-step active" data-label="Hospitalisation">1</div>
                <div class="progress-step" data-label="Patient">2</div>
                <div class="progress-step" data-label="Couverture Social">3</div>
                <div class="progress-step" data-label="Documents">4</div>
                <div class="progress-step" data-label="Validation et Vérification">5</div>
            </div>
            <br>
            <!-- Formulaire -->
            <div class="form-container">
                <!-- Step 1 -->
                <div class="form-step" id="step1">
                    <h3>Planification de l’hospitalisation</h3>
                    <form id="formStep1">
                        <div class="form-group">
                            <label for="type_hosp">Type d’hospitalisation :</label>
                            <select id="type_hosp" name="type_hosp" required>
                                <option value="" selected disabled hidden>-- Sélectionner un type --</option>
                                <?php if ($result_hospitype && $result_hospitype->num_rows > 0): ?>
                                    <?php while ($row = $result_hospitype->fetch_assoc()): ?>
                                        <option value="<?= htmlspecialchars($row['ID_TypeHospitalisation']) ?>">
                                            <?= htmlspecialchars($row['Libellé_TypeHospitalisation']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date">Date de l'hospitalisation :</label>
                            <input type="date" id="date" name="date" required>
                        </div>
                        <div class="form-group">
                            <label for="heure">Heure :</label>
                            <input type="time" id="heure" name="heure" required>
                            <small id="time-error" style="color: red; display: none;">L'heure ne peut pas être dans le passé !</small>
                        </div>
                        <div class="form-group">
                            <label for="medecin">Médecin :</label>
                            <select id="medecin" name="medecin" required>
                                <option value="" selected disabled hidden>-- Sélectionner un médecin --</option>
                                <?php if ($result_medecin && $result_medecin->num_rows > 0): ?>
                                    <?php while ($row = $result_medecin->fetch_assoc()): ?>
                                        <option value="<?= htmlspecialchars($row["ID_Personnel"]) ?>">
                                            Dr <?= htmlspecialchars($row['Nom_Personnel']) ?> (Service <?= htmlspecialchars($row['Libellé_Service']) ?>)
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <button type="button" id="nextStep1">Étape suivante →</button>
                    </form>
                </div>
                <!-- Step 2 -->
                <div class="form-step" id="step2" style="display:none;">
                    <h3>Informations du patient</h3>
                    <form id="formStep2">
                        <div class="form-group">
                            <label for="patient_nom">Nom :</label>
                            <input type="text" id="patient_nom" name="patient_nom" required>
                        </div>
                        <div class="form-group">
                            <label for="patient_prenom">Prénom :</label>
                            <input type="text" id="patient_prenom" name="patient_prenom" required>
                        </div>
                        <button type="button" id="prevStep2">← Étape précédente</button>
                                <button type="submit">Valider et envoyer</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="background-shape"></div>
        <div class="background-shape2"></div>
    </div>
</body>
<script src="../JAVASCRIPT/add-admission.js"></script>

</html>