<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$basePath = "../";
$context = "ADMIN";
$shownContext = "Administrateur";

require_once "../INCLUDES/db.php";
$message = "";
$error = "";

// Vérification de l'ID de l'employé passé en paramètre
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: list-employes.php'); // Redirection si pas d'ID
    exit;
}

$id_personnel = intval($_GET['id']);

/* ==========================================
   TRAITEMENT DES FORMULAIRES (POST)
   ========================================== */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- FORMULAIRE 1 : Mise à jour de l'employé ---
    if (isset($_POST['update_personnel'])) {
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $id_service = $_POST['service'];
        $id_role = $_POST['role'];

        $stmtUpdate = mysqli_prepare($conn, "UPDATE personnel SET Nom_Personnel = ?, Prénom_Personnel = ?, ID_Service = ?, Role_Personnel = ? WHERE ID_Personnel = ?");
        mysqli_stmt_bind_param($stmtUpdate, "ssiii", $nom, $prenom, $id_service, $id_role, $id_personnel);

        if (mysqli_stmt_execute($stmtUpdate)) {
            $message = "Informations de l'employé mises à jour avec succès.";
        } else {
            $error = "Échec de la mise à jour de l'employé.";
        }
    }

    // --- FORMULAIRE 2 : Création ou Mise à jour des accès ---
    if (isset($_POST['update_acces'])) {
        $email_portail = $_POST['email_portail'];
        $password_commun = $_POST['db_password'];
        $sql_user = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['db_username']);
        $id_role = $_POST['role_sql']; // Récupéré pour les droits SQL
        $wildcard_host = "%";

        try {
            $escaped_password = mysqli_real_escape_string($conn, $password_commun);

            // 1. Gestion de l'utilisateur MariaDB/MySQL (Garde le mdp clair ici pour la commande SQL système)
            $sqlCreate = "CREATE USER IF NOT EXISTS '$sql_user'@'$wildcard_host' IDENTIFIED BY '$escaped_password'";
            $sqlAlter = "ALTER USER '$sql_user'@'$wildcard_host' IDENTIFIED BY '$escaped_password'";

            mysqli_query($conn, $sqlCreate);
            mysqli_query($conn, $sqlAlter); // Met à jour le mot de passe s'il existait déjà

            // 2. Assignation des privilèges selon le rôle
            switch ($id_role) {
                case '1': // ADMIN
                    mysqli_query($conn, "GRANT ALL PRIVILEGES ON `$dbname`.* TO '$sql_user'@'$wildcard_host' WITH GRANT OPTION");
                    break;
                case '2': // SECRETAIRE
                    $t_sec_select = ['typehospitalisation', 'personnel', 'civilité', 'chambre', 'typechambre', 'role', 'service'];
                    foreach ($t_sec_select as $t) mysqli_query($conn, "GRANT SELECT ON `$dbname`.`$t` TO '$sql_user'@'$wildcard_host'");

                    $t_sec_full = ['couverturesocial', 'personne_prevenir', 'patient', 'hospitalisation', 'preadmission', 'soustutellede', 'piecesjoints', 'responsable'];
                    foreach ($t_sec_full as $t) mysqli_query($conn, "GRANT SELECT, INSERT, UPDATE, DELETE ON `$dbname`.`$t` TO '$sql_user'@'$wildcard_host'");
                    break;
                case '3': // CHEF DE SERVICE
                case '4': // PRATICIEN
                    $t_read_only = ['typehospitalisation', 'couverturesocial', 'civilité', 'chambre', 'typechambre', 'personne_prevenir', 'role', 'patient', 'hospitalisation', 'preadmission', 'soustutellede', 'piecesjoints', 'responsable', 'service', 'personnel'];
                    foreach ($t_read_only as $t) mysqli_query($conn, "GRANT SELECT ON `$dbname`.`$t` TO '$sql_user'@'$wildcard_host'");
                    break;
            }

            mysqli_query($conn, "FLUSH PRIVILEGES");

            // --- NOUVEAU : HACHAGE DU MOT DE PASSE POUR LE PORTAIL ---
            $password_hache = password_hash($password_commun, PASSWORD_BCRYPT);

            // 3. Liaison locale dans la table `utilisateurs`
            $checkUser = mysqli_prepare($conn, "SELECT ID_Employé FROM utilisateurs WHERE ID_Employé = ?");
            mysqli_stmt_bind_param($checkUser, "i", $id_personnel);
            mysqli_stmt_execute($checkUser);
            $resCheck = mysqli_stmt_get_result($checkUser);

            if (mysqli_num_rows($resCheck) > 0) {
                $stmtSync = mysqli_prepare($conn, "UPDATE utilisateurs SET Identifiant_User = ?, MDP = ?, CompteSQL = ? WHERE ID_Employé = ?");
                mysqli_stmt_bind_param($stmtSync, "sssi", $email_portail, $password_hache, $sql_user, $id_personnel);
            } else {
                $stmtSync = mysqli_prepare($conn, "INSERT INTO utilisateurs (Identifiant_User, MDP, CompteSQL, ID_Employé) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmtSync, "sssi", $email_portail, $password_hache, $sql_user, $id_personnel);
            }
            mysqli_stmt_execute($stmtSync);

            $message = "Accès portail (haché) et base de données mis à jour avec succès.";
        } catch (Exception $e) {
            $error = "Échec de la configuration des accès SQL : " . $e->getMessage();
        }
    }
}

/* ==========================================
   RECUPERATION DES DONNEES ACTUELLES
   ========================================== */
$stmtEmp = mysqli_prepare($conn, "SELECT * FROM personnel WHERE ID_Personnel = ?");
mysqli_stmt_bind_param($stmtEmp, "i", $id_personnel);
mysqli_stmt_execute($stmtEmp);
$emp = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtEmp));

if (!$emp) {
    header('Location: list-employes.php');
    exit;
}

$stmtU = mysqli_prepare($conn, "SELECT * FROM utilisateurs WHERE ID_Employé = ?");
mysqli_stmt_bind_param($stmtU, "i", $id_personnel);
mysqli_stmt_execute($stmtU);
$userAccount = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtU));

$services = mysqli_query($conn, "SELECT * FROM service");
$roles = mysqli_query($conn, "SELECT * FROM role");
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Administration - Modifier l'Employé</title>
    <link rel="stylesheet" href="../CSS/edit-user.css">
    <link rel="stylesheet" href="../INCLUDES/CSS/header.css">
</head>

<body>
    <div class="dashboard-container">
        <?php require_once "../INCLUDES/header.php"; ?>

        <div class="main-content">
            <div class="form-container">
                <div class="top-navigation">
                    <a href="list-user.php" class="btn-back">
                        ← Retour à la liste du personnel
                    </a>
                </div>
                <h1>Fiche de Modification : <?= htmlspecialchars($emp['Prénom_Personnel'] . " " . $emp['Nom_Personnel']) ?></h1>

                <?php if ($message): ?> <div class="message"><?= htmlspecialchars($message) ?></div> <?php endif; ?>
                <?php if ($error): ?> <div class="error"><?= htmlspecialchars($error) ?></div> <?php endif; ?>

                <form method="POST" class="separate-form">
                    <input type="hidden" name="update_personnel" value="1">
                    <div class="form-section">
                        <h3>1. Modifier l'Identité & Fonction</h3>
                        <div class="grid-identity">
                            <div class="form-group">
                                <label>Prénom</label>
                                <input type="text" name="prenom" id="prenom" required value="<?= htmlspecialchars($emp['Prénom_Personnel']) ?>">
                            </div>
                            <div class="form-group">
                                <label>Nom</label>
                                <input type="text" name="nom" id="nom" required value="<?= htmlspecialchars($emp['Nom_Personnel']) ?>">
                            </div>
                            <div class="form-group">
                                <label>Service</label>
                                <select name="service" required>
                                    <?php
                                    mysqli_data_seek($services, 0);
                                    while ($s = mysqli_fetch_assoc($services)):
                                        $selected = ($s['ID_Service'] == $emp['ID_Service']) ? "selected" : "";
                                    ?>
                                        <option value="<?= $s['ID_Service'] ?>" <?= $selected ?>><?= htmlspecialchars($s['Libellé_Service']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Rôle (Permissions SQL)</label>
                                <select name="role" id="roleSelect" required>
                                    <?php
                                    mysqli_data_seek($roles, 0);
                                    while ($r = mysqli_fetch_assoc($roles)):
                                        $selected = ($r['ID_Role'] == $emp['Role_Personnel']) ? "selected" : "";
                                    ?>
                                        <option value="<?= $r['ID_Role'] ?>" <?= $selected ?>><?= htmlspecialchars($r['Libellé_Role']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn-primary">METTRE À JOUR L'IDENTITÉ</button>
                    </div>
                </form>

                <form method="POST" class="separate-form">
                    <input type="hidden" name="update_acces" value="1">
                    <input type="hidden" name="role_sql" id="role_sql_hidden" value="<?= $emp['Role_Personnel'] ?>">

                    <div class="form-section">
                        <h3>2. Configurer / Modifier les Accès (Intranet & BDD)</h3>
                        <div class="dual-columns">

                            <div class="col portal-col">
                                <h4>Accès Portail Web</h4>
                                <div class="form-group">
                                    <label>Identifiant (Email)</label>
                                    <input type="email" name="email_portail" id="email_portail" required value="<?= htmlspecialchars($userAccount['Identifiant_User'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label>Réinitialiser le mot de passe</label>
                                    <input type="password" name="db_password" id="main_pass" required placeholder="Nouveau mot de passe">
                                </div>
                            </div>

                            <div class="col sql-col">
                                <h4>Accès Base de Données</h4>
                                <div class="form-group">
                                    <label>Username SQL (Auto)</label>
                                    <input type="text" name="db_username" id="db_username" readonly class="readonly-input" value="<?= htmlspecialchars($userAccount['CompteSQL'] ?? '') ?>">
                                </div>
                                <div class="rights-preview">
                                    <strong>Privilèges SQL actuels :</strong>
                                    <p id="rightsDesc">Chargement...</p>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn-success">METTRE À JOUR LES ACCÈS</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="../JAVASCRIPT/add-user.js"></script>
</body>

</html>