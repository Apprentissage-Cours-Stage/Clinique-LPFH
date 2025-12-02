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
                                            Chambre <?= htmlspecialchars($row['NumeroChambre']) ?> (Etage n°<?= htmlspecialchars($row['ID_Etage']) ?>) - <?= htmlspecialchars($row['Type_Chambre']) ?>
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
                                            <?= htmlspecialchars($row['Libellé_Civilité']) ?>
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
                <div class="form-step" id="step4" style="display: none;">
                    <h3>Personnes à prévenir et personne de confiance</h3>
                    <form id="formStep4">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="isMultiPersonne">La personne à prévenir est-elle aussi la personne de confiance
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
                        <div id="formResponsable">
                            <h3>Responsable légale</h3>
                            <div class="form-group">
                                <div class="form-group">
                                    <label for="NomResp">Nom du Responsable :</label>
                                    <input type="text" id="NomResp" name="NomResp">
                                </div>
                                <div class="form-group">
                                    <label for="PrenomResp">Prénom du Responsable :</label>
                                    <input type="text" id="PrenomResp" name="PrenomResp">
                                </div>
                                <div class="form-group">
                                    <label for="TelResp">Télephone du Responsable :</label>
                                    <input type="text" id="TelResp" name="TelResp">
                                </div>
                                <div class="form-group">
                                    <label for="MailResp">Adresse mail du Responsable :</label>
                                    <input type="text" id="MailResp" name="MailResp">
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
                        </div>
                        <div class="button-row">
                            <button type="button" id="prevStep4">← Étape précédente</button>
                            <button type="button" id="nextStep4">Étape précédente →</button>
                        </div>
                    </form>
                </div>
                <div class="form-step" id="step5" style="display: none;">
                    <h3>Documents à fournir obligatoirement</h3>
                    <form id="formStep5">
                        <div class="form-group doc-group">
                            <label for="CarteID">Pièce d'identité :</label>
                            <div class="doc-line">
                                <input type="file" id="CarteID" name="CarteID" required>
                                <small>Carte d'identité, Permis de conduire, Passport, Carte de séjour, etc... fonctionnent</small>
                                <div class="documentsPreview" id="previewCI"></div>
                            </div>
                        </div>
                        <div class="form-group doc-group">
                            <label for="CarteVitale">Carte Vitale :</label>
                            <div class="doc-line">
                                <input type="file" id="CarteVitale" name="CarteVitale" required>
                                <small>La carte vitale du parent si le patient est mineur...</small>
                                <div class="documentsPreview" id="previewCV"></div>
                            </div>
                        </div>
                        <div class="form-group doc-group">
                            <label for="CarteMutuelle">Carte de mutuelle :</label>
                            <div class="doc-line">
                                <input type="file" id="CarteMutuelle" name="CarteMutuelle" required>
                                <small>La carte de mutuelle connecter au numéro cité précédement...</small>
                                <div class="documentsPreview" id="previewCM"></div>
                            </div>
                        </div>
                        <div id="formMineur">
                            <h3>Documents à fournir si l'enfant est mineur</h3>
                            <div class="form-group doc-group">
                                <label for="LivretFamille">Livret de Famille :</label>
                                <div class="doc-line">
                                    <input type="file" id="LivretFamille" name="LivretFamille" required>
                                    <small>Le livret de famille du patient et de son responsable...</small>
                                    <div class="documentsPreview" id="previewLF"></div>
                                </div>
                            </div>
                            <div class="form-group doc-group">
                                <label for="AutoSoin">Autorisation de soin :</label>
                                <div class="doc-line">
                                    <input type="file" id="AutoSoin" name="AutoSoin" required>
                                    <small>L'autorisation de soin crée et mise en place par ses responsables légaux...</small>
                                    <div class="documentsPreview" id="previewAS"></div>
                                </div>
                            </div>
                            <div class="form-group doc-group">
                                <label for="DecisionJuge">Decision du juge :</label>
                                <div class="doc-line">
                                    <input type="file" id="DecisionJuge" name="DecisionJuge" required>
                                    <small>La decision du juge pour les soins prodiguée par la clinique...</small>
                                    <div class="documentsPreview" id="previewDJ"></div>
                                </div>
                            </div>
                        </div>
                        <div class="button-row">
                            <button type="button" id="prevStep5">← Étape précédente</button>
                            <button type="button" id="nextStep5">Étape précédente →</button>
                        </div>
                    </form>
                </div>
                <div class="form-step" id="step6" style="display: none;">
                    <form id="formStep6">
                        <h3>Informations du Patient</h2>
                        <div class="reviewCards">
                            <div class="card">
                                <h3>Informations personnelles</h3>
                                <p>Nom : <span id="cardNom"></span></p>
                                <p>Nom d'épouse : <span id="cardEpouse"></span></p>
                                <p>Prénom : <span id="cardPrenom"></span></p>
                                <p>Date de naissance : <span id="cardDOB"></span></p>
                                <p>Adresse : <span id="cardAdresse"></span></p>
                                <p>Code Postal : <span id="cardCP"></span></p>
                                <p>Ville : <span id="cardVille"></span></p>
                                <p>Téléphone : <span id="cardTel"></span></p>
                                <p>Email : <span id="cardMail"></span></p>
                                <button type="button" onclick="editSection('step3')">Modifier</button>
                            </div>
                            <div class="card">
                                <h3>Couverture social</h3>
                                <p>Organisation de sécurité social : <span id="cardNomOrga"></span></p>
                                <p>Numero de sécurité social : <span id="cardNumSecu"></span></p>
                                <p>Assuré ? : <span id="cardAssure"></span></p>
                                <p>ADL ? : <span id="cardADL"></span></p>
                                <p>Nom de Mutuelle : <span id="cardNomMut"></span></p>
                                <p>Numéro d'adhérent : <span id="cardNumMut"></span></p>
                                <p>Chambre utilisé : <span id="cardChambre"></span></p>
                                <button type="button" onclick="editSection('step2')">Modifier</button>
                            </div>
                            <div class="card">
                                <h3>Documents</h3>
                                <p>Carte d'identité : <span id="cardCI"></span></p>
                                <p>Carte vitale : <span id="cardCV"></span></p>
                                <p>Carte de mutuelle : <span id="cardCM"></span></p>
                                <p>Livret de Famille : <span id="cardLF"></span></p>
                                <p>Autorisation de soin : <span id="cardAS"></span></p>
                                <p>Decision du juge : <span id="cardDJ"></span></p>
                                <button type="button" onclick="editSection('step5')">Modifier</button>
                            </div>
                            <div class="card">
                                <h3>Personne à Prevenir et de confiance</h3>
                                <div id="cardPrev">
                                    <p>Nom de la personne à prevenir : <span id="cardNomPrev"></span></p>
                                    <p>Prénom de la personne à prevenir : <span id="cardPrenomPrev"></span></p>
                                    <p>Téléphone de la personne à prevenir : <span id="cardTelPrev"></span></p>
                                    <p>Rue d'habitation de la personne à prevenir : <span id="cardRuePrev"></span></p>
                                    <p>Code Postal de la personne à prevenir : <span id="cardCPPrev"></span></p>
                                    <p>Ville de la personne à prevenir : <span id="cardVillePrev"></span></p>
                                </div>
                                <div id="cardConf">
                                    <p>Nom de la personne de confiance : <span id="cardNomConf"></span></p>
                                    <p>Prénom de la personne de confiance : <span id="cardPrenomConf"></span></p>
                                    <p>Téléphone de la personne de confiance : <span id="cardTelConf"></span></p>
                                    <p>Rue d'habitation de la personne de confiance : <span id="cardRueConf"></span></p>
                                    <p>Code Postal de la personne de confiance : <span id="cardCPConf"></span></p>
                                    <p>Ville de la personne de confiance : <span id="cardVilleConf"></span></p>
                                </div>
                                <button type="button" onclick="editSection('step4')">Modifier</button>
                            </div>
                            <div class="card" id="cardResp">
                                <h3>Responsable</h3>
                                <p>Nom du responsable : <span id="cardNomResp"></span></p>
                                <p>Prénom du responsable : <span id="cardPrenomResp"></span></p>
                                <p>Téléphone du responsable : <span id="cardTelResp"></span></p>
                                <p>Rue d'habitation du responsable : <span id="cardRueResp"></span></p>
                                <p>Code Postal du responsable : <span id="cardCPResp"></span></p>
                                <p>Ville du responsable : <span id="cardVilleResp"></span></p>
                                <button type="button" onclick="editSection('step4')">Modifier</button>
                            </div>
                        </div>
                            <h3>Information de l'hospitalisation</h2>
                        <div class="reviewCards">
                            <div class="card">
                                <h3>Hospitalisation</h3>
                                <p>Date de l'hospitalisation : <span id="cardDateHospi"></span></p>
                                <p>Heure de l'hospitalisation : <span id="cardHeureHospi"></span></p>
                                <button type="button" onclick="editSection('step1')">Modifier</button>
                            </div>
                        </div>
                        <button type="button" id="submitAll">Envoyer</button>
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