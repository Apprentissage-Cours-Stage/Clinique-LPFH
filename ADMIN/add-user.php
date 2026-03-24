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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $id_role = $_POST['role'];
    $id_service = $_POST['service'];

    // 1. Insertion de l'employé
    $stmtUser = mysqli_prepare($conn, "INSERT INTO personnel (Nom_Personnel, Prénom_Personnel, Role_Personnel, ID_Service) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmtUser, "ssii", $nom, $prenom, $id_role, $id_service);

    if (mysqli_stmt_execute($stmtUser)) {
        $new_emp_id = mysqli_insert_id($conn);
        $message = "Employé créé avec succès (ID: $new_emp_id). ";

        if (isset($_POST['create_sql_user'])) {
            $email_portail = $_POST['email_portail'];
            $password_commun = $_POST['db_password'];
            $sql_user = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['db_username']);
            
            // On définit le host sur % pour autoriser toutes les connexions distantes
            $wildcard_host = "%"; 

            try {
                $escaped_password = mysqli_real_escape_string($conn, $password_commun);

                // IMPORTANT : Création de l'utilisateur SQL MariaDB avec le mot de passe en clair pour le système
                $sqlCreate = "CREATE USER IF NOT EXISTS '$sql_user'@'$wildcard_host' IDENTIFIED BY '$escaped_password'";

                if (mysqli_query($conn, $sqlCreate)) {
                    
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
                            foreach ($t_read_only as $t) {
                                mysqli_query($conn, "GRANT SELECT ON `$dbname`.`$t` TO '$sql_user'@'$wildcard_host'");
                            }
                            break;
                    }

                    mysqli_query($conn, "FLUSH PRIVILEGES");

                    // --- NOUVEAU : Hachage sécurisé du mot de passe pour la table utilisateurs ---
                    $password_hache = password_hash($password_commun, PASSWORD_BCRYPT);

                    $stmtSync = mysqli_prepare($conn, "INSERT INTO utilisateurs (Identifiant_User, MDP, CompteSQL, ID_Employé) VALUES (?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmtSync, "sssi", $email_portail, $password_hache, $sql_user, $new_emp_id);
                    mysqli_stmt_execute($stmtSync);

                    $message .= "Accès distants (%) et SQL configurés.";
                } else {
                    throw new Exception(mysqli_error($conn));
                }
            } catch (Exception $e) {
                $error = "Employé créé, mais échec des accès SQL : " . $e->getMessage();
            }
        }
    } else {
        $error = "Erreur lors de l'insertion de l'employé.";
    }
}

$services = mysqli_query($conn, "SELECT * FROM service");
$roles = mysqli_query($conn, "SELECT * FROM role");
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Administration - Nouvel Employé</title>
    <link rel="stylesheet" href="../CSS/add-user.css">
    <link rel="stylesheet" href="../INCLUDES/CSS/header.css">
</head>

<body>
    <div class="dashboard-container">
        <?php require_once "../INCLUDES/header.php"; ?>

        <div class="main-content">
            <div class="form-container">
                <h1>Fiche de Nouvel Employé</h1>

                <?php if ($message): ?> <div class="message"><?= htmlspecialchars($message) ?></div> <?php endif; ?>
                <?php if ($error): ?> <div class="error"><?= htmlspecialchars($error) ?></div> <?php endif; ?>

                <form method="POST">
                    <div class="form-section">
                        <h3>1. Identité & Fonction</h3>
                        <div class="grid-identity">
                            <div class="form-group">
                                <label>Prénom</label>
                                <input type="text" name="prenom" id="prenom" required>
                            </div>
                            <div class="form-group">
                                <label>Nom</label>
                                <input type="text" name="nom" id="nom" required>
                            </div>
                            <div class="form-group">
                                <label>Service</label>
                                <select name="service" required>
                                    <option value="" disabled selected>Choisir...</option>
                                    <?php while ($s = mysqli_fetch_assoc($services)) echo "<option value='{$s['ID_Service']}'>" . htmlspecialchars($s['Libellé_Service']) . "</option>"; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Rôle (Permissions SQL)</label>
                                <select name="role" id="roleSelect" required>
                                    <option value="" disabled selected>Choisir un rôle...</option>
                                    <?php while ($r = mysqli_fetch_assoc($roles)) echo "<option value='{$r['ID_Role']}'>" . htmlspecialchars($r['Libellé_Role']) . "</option>"; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="flex-label access-toggle-bar">
                        <input type="checkbox" name="create_sql_user" id="toggleSql" checked>
                        <label for="toggleSql">Générer les accès au portail intranet</label>
                    </div>

                    <div id="accessSection" class="dual-columns">
                        <div class="col portal-col">
                            <h4>Accès Portail Web</h4>
                            <div class="form-group">
                                <label>Identifiant (Email)</label>
                                <input type="email" name="email_portail" id="email_portail" required>
                            </div>
                            <div class="form-group">
                                <label>Mot de passe unique</label>
                                <input type="password" name="db_password" id="main_pass" required>
                            </div>
                        </div>

                        <div class="col sql-col">
                            <h4>Accès Base de Données</h4>
                            <div class="form-group">
                                <label>Username SQL (Auto)</label>
                                <input type="text" name="db_username" id="db_username" readonly class="readonly-input">
                            </div>
                            <div class="rights-preview">
                                <strong>Privilèges SQL appliqués :</strong>
                                <p id="rightsDesc">Veuillez sélectionner un rôle.</p>
                            </div>
                        </div>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn-primary">ENREGISTRER L'EMPLOYÉ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../JAVASCRIPT/add-user.js"></script>
</body>
</html>