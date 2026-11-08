<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!defined('BASE_URL')) {
    define('BASE_URL', '/Optica-web');
}
$rol = $_SESSION['rol'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Óptica Vision') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a href="<?= BASE_URL ?>/index.php" class="logo">
            <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Óptica Vision" class="logo-img" width="36" height="36" style="width:36px;height:36px;max-width:36px;max-height:36px;min-width:0;min-height:0;flex-shrink:0;object-fit:contain;">
            <span class="logo-text">Óptica <strong>Vision</strong></span>
        </a>
        <?php if (isLoggedIn()): ?>
        <div class="header-user">
            <span class="user-name"><?= htmlspecialchars($_SESSION['nombre'] ?? '') ?></span>
            <a href="<?= BASE_URL ?>/logout.php" class="btn-logout">Cerrar sesión</a>
        </div>
        <button class="hamburger" id="hamburger" aria-label="Menú" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <nav class="main-nav" id="main-nav">
            <?php if ($rol === 'admin'): ?>
                <a href="<?= BASE_URL ?>/admin/dashboard.php">Panel</a>
                <a href="<?= BASE_URL ?>/admin/pacientes.php">Pacientes</a>
                <a href="<?= BASE_URL ?>/admin/revisiones.php">Revisiones</a>
                <a href="<?= BASE_URL ?>/admin/citas.php">Citas</a>
                <a href="<?= BASE_URL ?>/admin/calendario.php">Calendario</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/paciente/dashboard.php">Mi Panel</a>
                <a href="<?= BASE_URL ?>/paciente/revisiones.php">Mis Revisiones</a>
                <a href="<?= BASE_URL ?>/paciente/citas.php">Mis Citas</a>
                <a href="<?= BASE_URL ?>/paciente/calendario.php">Calendario</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>
</header>
<main class="site-main">
