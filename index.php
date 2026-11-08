<?php
define('BASE_URL', '/Optica-web');
require_once __DIR__ . '/includes/auth.php';

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

if (isAdmin()) {
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
} else {
    header('Location: ' . BASE_URL . '/paciente/dashboard.php');
}
exit;
