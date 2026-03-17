<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once "../INCLUDES/db.php";

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $serviceID = intval($_GET['id']);

    $staffSQL = "SELECT COUNT(*) FROM personnel WHERE ID_Service = ?";
    $stmtStaff = mysqli_prepare($conn, $staffSQL);
    mysqli_stmt_bind_param($stmtStaff, "i", $serviceID);
    mysqli_stmt_execute($stmtStaff);
    mysqli_stmt_bind_result($stmtStaff, $staffCount);
    mysqli_stmt_fetch($stmtStaff);
    mysqli_stmt_close($stmtStaff);

    if ($staffCount > 0) {
        header("Location: list-service.php?error=not_empty&count=".$staffCount);
        exit();
    } else {
        $deleteSQL = "DELETE FROM service WHERE ID_Service = ?";
        $stmtDel = mysqli_prepare($conn, $deleteSQL);
        mysqli_stmt_bind_param($stmtDel, "i", $serviceID);

        if (mysqli_stmt_execute($stmtDel)) {
            header("Location: list-service.php?msg=deleted");
        } else {
            header("Location: list-service.php?error=sql_error");
        }
        mysqli_stmt_close($stmtDel);
        exit();
    }
} else {
    header("Location: list-service.php");
    exit();
}
?>