<?php
define('BASE_URL', '/Optica-web');
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Por favor, introduce tu email y contraseña.';
    } else {
        try {
            $db   = getDB();
            $stmt = $db->prepare('SELECT id, nombre, apellidos, password, rol FROM usuarios WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['nombre']     = $user['nombre'];
                $_SESSION['apellidos']  = $user['apellidos'];
                $_SESSION['rol']        = $user['rol'];

                header('Location: ' . BASE_URL . '/index.php');
                exit;
            } else {
                $error = 'Email o contraseña incorrectos.';
            }
        } catch (PDOException $e) {
            $error = 'Error de conexión con la base de datos. Comprueba que MySQL está activo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — Óptica Vision</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="login-page">
<div class="login-card">
    <div class="login-logo">
        <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Óptica Vision" class="login-logo-img" width="100" height="100" style="width:100px;height:100px;max-width:100px;max-height:100px;object-fit:contain;display:block;margin:0 auto .75rem;">
        <h1>Óptica <strong>Vision</strong></h1>
        <p>Plataforma de gestión</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="form-group" style="margin-bottom:1rem">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   placeholder="usuario@ejemplo.com" required autofocus>
        </div>
        <div class="form-group" style="margin-bottom:1.5rem">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:.7rem">
            Iniciar sesión
        </button>
    </form>

    <p style="text-align:center;margin-top:1.25rem;font-size:.82rem;color:var(--text-muted)">
        ¿Olvidaste tu contraseña? Contacta con el administrador de la óptica.
    </p>
</div>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
