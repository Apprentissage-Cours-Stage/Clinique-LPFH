<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once "../INCLUDES/db.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: list-user.php');
    exit;
}

$id_personnel = intval($_GET['id']);

try {
    // 1. On récupère le rôle de l'employé ET son compte SQL s'il existe
    $sqlInfos = "SELECT p.Role_Personnel, u.CompteSQL 
                 FROM personnel p 
                 LEFT JOIN utilisateurs u ON u.ID_Employé = p.ID_Personnel 
                 WHERE p.ID_Personnel = ?";
                 
    $stmtInfos = mysqli_prepare($conn, $sqlInfos);
    mysqli_stmt_bind_param($stmtInfos, "i", $id_personnel);
    mysqli_stmt_execute($stmtInfos);
    $resInfos = mysqli_stmt_get_result($stmtInfos);
    $infos = mysqli_fetch_assoc($resInfos);

    if (!$infos) {
        throw new Exception("Employé introuvable.");
    }

    $sql_username = $infos['CompteSQL']; // Sauvegarde du nom d'utilisateur SQL pour plus tard

    // 2. Traitement spécifique si c'est un Praticien (Rôle 4)
    if ($infos['Role_Personnel'] == 4) {
        
        // Vérification des Hospitalisations futures ou du jour même
        $sqlCheckActive = "SELECT COUNT(*) AS en_cours 
                           FROM preadmission p
                           INNER JOIN hospitalisation h ON p.Hospitalisation_PreAdmi = h.ID_Hospitalisation
                           WHERE h.Medecin_En_Charge = ? 
                           AND h.Date_Hospitalisation >= CURDATE()";

        $stmtCheckActive = mysqli_prepare($conn, $sqlCheckActive);
        mysqli_stmt_bind_param($stmtCheckActive, "i", $id_personnel);
        mysqli_stmt_execute($stmtCheckActive);
        $resActive = mysqli_stmt_get_result($stmtCheckActive);
        $activeCount = mysqli_fetch_assoc($resActive);

        if ($activeCount['en_cours'] > 0) {
            header("Location: list-user.php?error=medecin_actif&count=" . $activeCount['en_cours']);
            exit;
        }

        mysqli_begin_transaction($conn);

        // Suppression des pré-admissions passées
        $sqlCleanOld = "DELETE p FROM preadmission p
                        INNER JOIN hospitalisation h ON p.Hospitalisation_PreAdmi = h.ID_Hospitalisation
                        WHERE h.Medecin_En_Charge = ? 
                        AND h.Date_Hospitalisation < CURDATE()";

        $stmtCleanOld = mysqli_prepare($conn, $sqlCleanOld);
        mysqli_stmt_bind_param($stmtCleanOld, "i", $id_personnel);
        mysqli_stmt_execute($stmtCleanOld);

    } else {
        mysqli_begin_transaction($conn);
    }

    // 3. Suppression locale (PHP / Application)
    $stmtDelUser = mysqli_prepare($conn, "DELETE FROM utilisateurs WHERE ID_Employé = ?");
    mysqli_stmt_bind_param($stmtDelUser, "i", $id_personnel);
    mysqli_stmt_execute($stmtDelUser);

    $stmtDelPers = mysqli_prepare($conn, "DELETE FROM personnel WHERE ID_Personnel = ?");
    mysqli_stmt_bind_param($stmtDelPers, "i", $id_personnel);
    mysqli_stmt_execute($stmtDelPers);


    // 4. Suppression du compte SQL MariaDB/MySQL physique (si existant)
    if (!empty($sql_username)) {
        // Comme DROP USER ne supporte pas nativement les requêtes préparées MySQLi en PHP, 
        // on l'exécute via une requête nettoyée (nous avons déjà fait un preg_replace à l'insertion)
        $escaped_sql_username = mysqli_real_escape_string($conn, $sql_username);
        $sqlDrop = "DROP USER IF EXISTS '$escaped_sql_username'@'%'";
        mysqli_query($conn, $sqlDrop);
    }

    mysqli_commit($conn);

    header("Location: list-user.php?msg=deleted");
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    header("Location: list-user.php?error=sql_error");
    exit;
}