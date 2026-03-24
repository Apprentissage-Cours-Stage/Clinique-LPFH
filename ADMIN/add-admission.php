<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$basePath = "../";
$context = "ADMIN";
$shownContext = "Administrateur";

// Utilisation de la BDD
require_once "../INCLUDES/db.php";

try {
    // 1. Types d'hospitalisation
    $sql_hospitype = "SELECT ID_TypeHospitalisation, Libellé_TypeHospitalisation
                      FROM typehospitalisation
                      ORDER BY Libellé_TypeHospitalisation ASC;";
    $result_hospitype = $conn->query($sql_hospitype);
    // Utilisation de fetch_all (MySQLi) au lieu de fetchAll (PDO)
    $hospitypes = $result_hospitype ? $result_hospitype->fetch_all(MYSQLI_ASSOC) : [];

    // 2. Médecins
    $sql_medecin = "SELECT P.ID_Personnel, P.Nom_Personnel, S.Libellé_Service
                    FROM personnel P
                    INNER JOIN service S ON P.ID_Service = S.ID_Service
                    INNER JOIN role R ON P.Role_Personnel = R.ID_Role
                    WHERE R.Libellé_Role = 'Praticien';";
    $result_medecin = $conn->query($sql_medecin);
    $medecins = $result_medecin ? $result_medecin->fetch_all(MYSQLI_ASSOC) : [];

    // 3. Numéros des chambres réelles (Pour lier à la FK de la table Hospitalisation)
    $sql_chambre = "SELECT NumeroChambre 
                    FROM chambre 
                    ORDER BY NumeroChambre ASC";
    $result_chambre = $conn->query($sql_chambre);
    $chambres = $result_chambre ? $result_chambre->fetch_all(MYSQLI_ASSOC) : [];

    // 4. Civilités
    $sql_civilité = "SELECT ID_Civilité, Libellé_Civilité
                     FROM civilité";
    $result_civilité = $conn->query($sql_civilité);
    $civilités = $result_civilité ? $result_civilité->fetch_all(MYSQLI_ASSOC) : [];

} catch (Exception $e) { // Remplacé PDOException par Exception générale car vous êtes sous MySQLi
    die("Erreur de connexion ou d'exécution : " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajout de Pré-admission - <?= htmlspecialchars($shownContext) ?></title>
    <link rel="stylesheet" href="../CSS/add-admission.css">
    <link rel="stylesheet" href="../INCLUDES/CSS/header.css">
</head>

<body>
    <div class="dashboard-container">
        <?php require_once "../INCLUDES/header.php"; ?>
        <div class="main-content">
            <div class="progress-container">
                <div class="progress-line" style="width: 0%;"></div>
                <div class="progress-step active" data-label="Patient">1</div>
                <div class="progress-step" data-label="Couverture Sociale">2</div>
                <div class="progress-step" data-label="Hospitalisation">3</div>
                <div class="progress-step" data-label="Personnes à Prévenir/de confiance">4</div>
                <div class="progress-step" data-label="Documents">5</div>
            </div>
            <br>

            <div class="form-container">
                
                <div class="form-step" id="step1">
                    <h3>Informations personnelles du patient</h3>
                    <form id="formStep1">
                        <div class="form-group">
                            <label for="civilité">Civilité :</label>
                            <select id="civilité" name="civilité" required>
                                <option value="" selected disabled hidden>-- Choisir la civilité --</option>
                                <?php foreach ($civilités as $row): ?>
                                    <option value="<?= htmlspecialchars($row['ID_Civilité']) ?>">
                                        <?= htmlspecialchars($row['Libellé_Civilité']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nomNaissance">Nom de naissance :</label>
                            <input type="text" id="nomNaissance" name="nomNaissance" maxlength="150" required>
                        </div>
                        <div class="form-group married-row">
                            <label>
                                <input type="checkbox" id="isMarried"> Marié(e)
                            </label>
                            <label for="nomEpouse">Nom d'épouse :</label>
                            <input type="text" id="nomEpouse" name="nomEpouse" maxlength="150" disabled>
                        </div>
                        <div class="form-group">
                            <label for="prenom">Prénom :</label>
                            <input type="text" id="prenom" name="prenom" maxlength="150" required>
                        </div>
                        <div class="form-group">
                            <label for="datenaissance">Date de naissance :</label>
                            <input type="date" id="datenaissance" name="datenaissance" required>
                        </div>
                        <div class="form-group">
                            <label for="addresse">Adresse :</label>
                            <input type="text" id="addresse" name="addresse" maxlength="150" required>
                        </div>
                        <div class="form-group">
                            <label for="cp">Code Postal :</label>
                            <input type="text" id="cp" name="cp" maxlength="5" pattern="[0-9]{5}" required>
                        </div>
                        <div class="form-group">
                            <label for="ville">Ville :</label>
                            <input type="text" id="ville" name="ville" maxlength="100" required>
                        </div>
                        <div class="form-group">
                            <label for="telephone">Téléphone :</label>
                            <input type="text" id="telephone" name="telephone" maxlength="10" pattern="[0-9]{10}" required>
                        </div>
                        <div class="form-group">
                            <label for="mail">Email :</label>
                            <input type="email" id="mail" name="mail" maxlength="150" required>
                        </div>
                        <div class="button-row">
                            <button type="button" id="nextStep1">Étape suivante →</button>
                        </div>
                    </form>
                </div>

                <div class="form-step" id="step2" style="display:none;">
                    <h3>Couverture sociale du patient</h3>
                    <form id="formStep2">
                        <div class="form-group">
                            <label for="nom_orga_social">Organisme de sécurité sociale / Nom de la caisse :</label>
                            <input type="text" id="nom_orga_social" name="nom_orga_social" maxlength="150" required>
                        </div>
                        <div class="form-group">
                            <label for="num_secusocial">Numéro de sécurité sociale (15 chiffres) :</label>
                            <input type="text" id="num_secusocial" name="num_secusocial" maxlength="15" pattern="[0-9]{15}" required>
                        </div>
                        <div class="form-group">
                            <label for="assure">Le patient est-il assuré ?</label>
                            <select id="assure" name="assure" required>
                                <option value="" selected disabled hidden>-- Sélectionner une réponse --</option>
                                <option value="1">Oui</option>
                                <option value="0">Non</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="adl">Le patient est-il en ALD ?</label>
                            <select id="adl" name="adl" required>
                                <option value="" selected disabled hidden>-- Sélectionner une réponse --</option>
                                <option value="1">Oui</option>
                                <option value="0">Non</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nom_mutuelle">Nom de la mutuelle ou de l'assurance :</label>
                            <input type="text" id="nom_mutuelle" name="nom_mutuelle" maxlength="150" required>
                        </div>
                        <div class="form-group">
                            <label for="num_adhérent">Numéro d'adhérent :</label>
                            <input type="text" id="num_adhérent" name="num_adhérent" maxlength="50" pattern="[0-9]{1,50}" required>
                        </div>
                        <div class="button-row">
                            <button type="button" id="nextStep2">Étape suivante →</button>
                        </div>
                    </form>
                </div>

                <div class="form-step" id="step3" style="display: none;">
                    <h3>Planification de l’hospitalisation</h3>
                    <form id="formStep3">
                        <div class="form-group">
                            <label for="type_hosp">Type d’hospitalisation :</label>
                            <select id="type_hosp" name="type_hosp" required>
                                <option value="" selected disabled hidden>-- Sélectionner un type --</option>
                                <?php foreach ($hospitypes as $row): ?>
                                    <option value="<?= htmlspecialchars($row['ID_TypeHospitalisation']) ?>">
                                        <?= htmlspecialchars($row['Libellé_TypeHospitalisation']) ?>
                                    </option>
                                <?php endforeach; ?>
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
                                <?php foreach ($medecins as $row): ?>
                                    <option value="<?= htmlspecialchars($row["ID_Personnel"]) ?>">
                                        Dr <?= htmlspecialchars($row['Nom_Personnel']) ?> (Service <?= htmlspecialchars($row['Libellé_Service']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="chambre">Chambre sélectionnée :</label>
                            <select id="chambre" name="chambre" required>
                                <option value="" selected disabled hidden>-- Sélectionner une chambre --</option>
                                <?php foreach ($chambres as $row): ?>
                                    <option value="<?= htmlspecialchars($row['NumeroChambre']) ?>">
                                        Chambre n°<?= htmlspecialchars($row['NumeroChambre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="button-row">
                            <button type="button" id="nextStep3">Étape suivante →</button>
                        </div>
                    </form>
                </div>

                <div class="form-step" id="step4" style="display: none;">
                    <h3>Personnes à prévenir et personne de confiance</h3>
                    <form id="formStep4">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="isMultiPersonne"> La personne à prévenir est aussi la personne de confiance
                            </label>
                        </div>
                        <div id="personnesWrapper" class="personnes-wrapper">
                            <div class="personne-form" id="formPP">
                                <h4 id="titlePPPC">Personne à prévenir</h4>
                                <div class="form-group">
                                    <label for="NomPP">Nom du contact :</label>
                                    <input type="text" id="NomPP" name="NomPP" required>
                                </div>
                                <div class="form-group">
                                    <label for="PrenomPP">Prénom du contact :</label>
                                    <input type="text" id="PrenomPP" name="PrenomPP" required>
                                </div>
                                <div class="form-group">
                                    <label for="TelPP">Téléphone du contact :</label>
                                    <input type="text" id="TelPP" name="TelPP" required>
                                </div>
                                <div class="form-group">
                                    <label for="RuePP">Rue du contact :</label>
                                    <input type="text" id="RuePP" name="RuePP" required>
                                </div>
                                <div class="form-group">
                                    <label for="CPPP">Code postal :</label>
                                    <input type="text" id="CPPP" name="CPPP" required>
                                </div>
                                <div class="form-group">
                                    <label for="VillePP">Ville :</label>
                                    <input type="text" id="VillePP" name="VillePP" required>
                                </div>
                            </div>

                            <div class="personne-form" id="formPC">
                                <h4>Personne de confiance</h4>
                                <div class="form-group">
                                    <label for="NomPC">Nom du contact de confiance :</label>
                                    <input type="text" id="NomPC" name="NomPC">
                                </div>
                                <div class="form-group">
                                    <label for="PrenomPC">Prénom du contact de confiance :</label>
                                    <input type="text" id="PrenomPC" name="PrenomPC">
                                </div>
                                <div class="form-group">
                                    <label for="TelPC">Téléphone du contact de confiance :</label>
                                    <input type="text" id="TelPC" name="TelPC">
                                </div>
                                <div class="form-group">
                                    <label for="RuePC">Rue du contact de confiance :</label>
                                    <input type="text" id="RuePC" name="RuePC">
                                </div>
                                <div class="form-group">
                                    <label for="CPPC">Code postal :</label>
                                    <input type="text" id="CPPC" name="CPPC">
                                </div>
                                <div class="form-group">
                                    <label for="VillePC">Ville :</label>
                                    <input type="text" id="VillePC" name="VillePC">
                                </div>
                            </div>
                        </div>

                        <div id="formResponsable" style="display:none;">
                            <h3>Responsable légal</h3>
                            <div class="form-group">
                                <label for="NomResp">Nom du Responsable :</label>
                                <input type="text" id="NomResp" name="NomResp">
                            </div>
                            <div class="form-group">
                                <label for="PrenomResp">Prénom du Responsable :</label>
                                <input type="text" id="PrenomResp" name="PrenomResp">
                            </div>
                            <div class="form-group">
                                <label for="TelResp">Téléphone du Responsable :</label>
                                <input type="text" id="TelResp" name="TelResp">
                            </div>
                            <div class="form-group">
                                <label for="MailResp">Adresse mail du Responsable :</label>
                                <input type="email" id="MailResp" name="MailResp">
                            </div>
                            <div class="form-group">
                                <label for="RueResp">Rue d'habitation du Responsable :</label>
                                <input type="text" id="RueResp" name="RueResp">
                            </div>
                            <div class="form-group">
                                <label for="VilleResp">Ville du Responsable :</label>
                                <input type="text" id="VilleResp" name="VilleResp">
                            </div>
                            <div class="form-group">
                                <label for="CPResp">Code Postal du Responsable :</label>
                                <input type="text" id="CPResp" name="CPResp">
                            </div>
                        </div>

                        <div class="button-row">
                            <button type="button" id="nextStep4">Étape suivante →</button>
                        </div>
                    </form>
                </div>

                <div class="form-step" id="step5" style="display: none;">
                    <h3>Documents à fournir obligatoirement</h3>
                    <form id="formStep5" enctype="multipart/form-data"> <div class="form-group doc-group">
                            <label for="CarteID">Pièce d'identité :</label>
                            <div class="doc-line">
                                <input type="file" id="CarteID" name="CarteID" required>
                                <small>CNI, Passeport, Titre de séjour...</small>
                                <div class="documentsPreview" id="previewCI"></div>
                            </div>
                        </div>
                        <div class="form-group doc-group">
                            <label for="CarteVitale">Carte Vitale :</label>
                            <div class="doc-line">
                                <input type="file" id="CarteVitale" name="CarteVitale" required>
                                <div class="documentsPreview" id="previewCV"></div>
                            </div>
                        </div>
                        <div class="form-group doc-group">
                            <label for="CarteMutuelle">Carte de mutuelle :</label>
                            <div class="doc-line">
                                <input type="file" id="CarteMutuelle" name="CarteMutuelle" required>
                                <div class="documentsPreview" id="previewCM"></div>
                            </div>
                        </div>

                        <div id="formMineur" style="display:none;">
                            <h3>Documents à fournir si l'enfant est mineur</h3>
                            <div class="form-group doc-group">
                                <label for="LivretFamille">Livret de Famille :</label>
                                <div class="doc-line">
                                    <input type="file" id="LivretFamille" name="LivretFamille">
                                    <div class="documentsPreview" id="previewLF"></div>
                                </div>
                            </div>
                            <div class="form-group doc-group">
                                <label for="AutoSoin">Autorisation de soins :</label>
                                <div class="doc-line">
                                    <input type="file" id="AutoSoin" name="AutoSoin">
                                    <div class="documentsPreview" id="previewAS"></div>
                                </div>
                            </div>
                            <div class="form-group doc-group">
                                <label for="DecisionJuge">Décision du juge :</label>
                                <div class="doc-line">
                                    <input type="file" id="DecisionJuge" name="DecisionJuge">
                                    <div class="documentsPreview" id="previewDJ"></div>
                                </div>
                            </div>
                        </div>

                        <div class="button-row">
                            <button type="button" id="nextStep5">Terminer la pré-admission</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    <script src="../JAVASCRIPT/add-admission.js"></script>
</body>

</html>