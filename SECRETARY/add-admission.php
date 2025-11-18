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

    $sql_chambre = "SELECT C.NumeroChambre, C.ID_Etage, CT.Type_Chambre
                    FROM Chambre C
                    INNER JOIN TypeChambre CT ON CT.ID_TypeChambre = C.ID_TypeChambre";
    $result_chambre = $conn->query($sql_chambre);

    $sql_civilité = "SELECT ID_Civilité, Libellé_Civilité
                     FROM Civilité";
    $result_civilité = $conn->query($sql_civilité);
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
                <div class="progress-step" data-label="Couverture Social">2</div>
                <div class="progress-step" data-label="Patient">3</div>
                <div class="progress-step" data-label="Personnes à Pervenir/de confiance">4</div>
                <div class="progress-step" data-label="Documents">5</div>
                <div class="progress-step" data-label="Validation et Vérification">6</div>
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
                            <label for="hospidate">Date de l'hospitalisation :</label>
                            <input type="date" id="hospidate" name="hospidate" required>
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
                    <h3>Couverture social du patient</h3>
                    <form id="formStep2">
                        <div class="form-group">
                            <label for="nom_orga_social">Organisme de sécurité social / Nom de la caisse d'assurance maladie :</label>
                            <input type="text" id="nom_orga_social" name="nom_orga_social" required>
                        </div>
                        <div class="form-group">
                            <label for="num_secusocial">Numéro de sécurité social :</label>
                            <input type="text" id="num_secusocial" name="num_secusocial" required>
                        </div>
                        <div class="form-group">
                            <label for="assure">Le patient est-il assuré ?</label>
                            <select id="assure" name="assure" required>
                                <option value="" selected disabled hidden> -- Sélectionner une réponse --</option>
                                <option value="1">Oui</option>
                                <option value="0">Non</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="adl">Le patient est-il en ADL ?</label>
                            <select id="adl" name="adl" required>
                                <option value="" selected disabled hidden> -- Sélectionner une réponse --</option>
                                <option value="1">Oui</option>
                                <option value="0">Non</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nom_mutuelle">Nom de la mutuelle ou de l'assurance :</label>
                            <input type="text" id="nom_mutuelle" name="nom_mutuelle" required>
                        </div>
                        <div class="form-group">
                            <label for="num_adhérent">Numéro d'adhérent :</label>
                            <input type="text" id="num_adhérent" name="num_adhérent" required>
                        </div>
                        <div class="form-group">
                            <label for="chambre">Chambre sélectionné :</label>
                            <select id="chambre" name="chambre" required>
                                <option value="" selected disabled hidden>-- Sélectionner une chambre --</option>
                                <?php if ($result_chambre && $result_chambre->num_rows > 0): ?>
                                    <?php while ($row = $result_chambre->fetch_assoc()): ?>
                                        <option value="<?= htmlspecialchars($row['NumeroChambre']) ?>">
                                            Chambre <?= htmlspecialchars($row['NumeroChambre'])?> (Etage n°<?=htmlspecialchars($row['ID_Etage'])?>) - <?= htmlspecialchars($row['Type_Chambre']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="button-row">
                            <button type="button" id="prevStep2">← Étape précédente</button>
                            <button type="button" id="nextStep2">Étape précédente →</button>
                        </div>
                    </form>
                </div>
                <div class="form-step" id="step3" style="display: none;">
                    <h3>Informations personnelles du patient</h3>
                    <form id="formStep3">
                        <div class="form-group">
                            <label for="civilité">Civilité :</label>
                            <select id="civilité" name="civilité" required>
                                <option value="" selected disabled hidden>-- Choisir la civilité --</option>
                                <?php if ($result_civilité && $result_civilité->num_rows > 0): ?>
                                    <?php while ($row = $result_civilité->fetch_assoc()): ?>
                                        <option value="<?= htmlspecialchars($row['ID_Civilité']) ?>">
                                            <?= htmlspecialchars($row['Libellé_Civilité'])?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nomNaissance">Nom de naissance :</label>
                            <input type="text" id="nomNaissance" name="nomNaissance" required>
                        </div>
                        <div class="form-group married-row">
                            <label>
                                <input type="checkbox" id="isMarried"> Marié(e)
                            </label>
                            <label for="nomEpouse">Nom d'épouse :</label>
                            <input type="text" id="nomEpouse" name="nomEpouse" disabled>
                        </div>
                        <div class="form-group">
                            <label for="prenom">Prénom :</label>
                            <input type="text" id="prenom" name="prenom" required>
                        </div>
                        <div class="form-group">
                            <label for="datenaissance">Date de naissance :</label>
                            <input type="date" id="datenaissance" name="datenaissance" required>
                        </div>
                        <div class="form-group">
                            <label for="addresse">Adresse :</label>
                            <input type="text" id="addresse" name="addresse" required>
                        </div>
                        <div class="form-group">
                            <label for="cp">Code Postal :</label>
                            <input type="text" id="cp" name="cp" required>
                        </div>
                        <div class="form-group">
                            <label for="ville">Ville :</label>
                            <input type="text" id="ville" name="ville" required>
                        </div>
                        <div class="form-group">
                            <label for="telephone">Téléphone :</label>
                            <input type="text" id="telephone" name="telephone" required>
                        </div>
                        <div class="form-group">
                            <label for="mail">Email :</label>
                            <input type="text" id="mail" name="mail" required>
                        </div>
                        <div class="button-row">
                            <button type="button" id="prevStep3">← Étape précédente</button>
                            <button type="button" id="nextStep3">Étape précédente →</button>
                        </div>
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