<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin(): void {
    if (empty($_SESSION['usuario_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['rol'] !== 'admin') {
        header('Location: ' . BASE_URL . '/paciente/dashboard.php');
        exit;
    }
}

function requirePaciente(): void {
    requireLogin();
    if ($_SESSION['rol'] !== 'paciente') {
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
        exit;
    }
}

function isLoggedIn(): bool {
    return !empty($_SESSION['usuario_id']);
}

function isAdmin(): bool {
    return isLoggedIn() && $_SESSION['rol'] === 'admin';
}
