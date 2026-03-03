<?php
if (!isset($basePath)) {
    $basePath = "";
}

if (!isset($context)) {
    exit;
} 
?>
<div class="sidebar">
    <img src="<?= $basePath ?>/INCLUDES/IMAGES/LPFSLogo.png" alt="Logo Clinique" class="logo">
    <h2>Panel <?= htmlspecialchars($shownContext) ?></h2>
    <ul class="menu">
        <li><a href="dashboard-<?= strtolower($context) ?>.php">Accueil</a></li>
        <li><a href="add-admission.php">Enregistrer une Pré-admission</a></li>
        <li><a href="list-admission.php">Liste des Pré-admissions</a></li>
        <?php if ($context === "ADMIN"): ?>
            <li><a href="#">Enregistrer un nouveau personnel/utilisateur</a></li>
            <li><a href="#">Liste du personnel/utilisateurs</a></li>
            <li><a href="add-service.php">Enregistrer un nouveau service</a></li>
            <li><a href="list-service.php">Liste des services</a></li>
        <?php endif; ?>
        <li><a href="../logout.php">Déconnexion</a></li>
    </ul>
</div>