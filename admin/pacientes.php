<?php
define('BASE_URL', '/Optica-web');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db  = getDB();
$msg = '';
$err = '';

// --- ELIMINAR ---
if (isset($_GET['delete']) && ctype_digit($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $db->prepare('DELETE FROM usuarios WHERE id = (SELECT usuario_id FROM pacientes WHERE id = ?)')->execute([$id]);
        $msg = 'Paciente eliminado correctamente.';
    } catch (PDOException $e) {
        $err = 'Error al eliminar el paciente.';
    }
}

// --- CREAR / EDITAR ---
$editPaciente = null;
$editUsuario  = null;
if (isset($_GET['edit']) && ctype_digit($_GET['edit'])) {
    $editPaciente = $db->prepare(
        'SELECT p.*, u.nombre, u.apellidos, u.email FROM pacientes p JOIN usuarios u ON u.id = p.usuario_id WHERE p.id = ?'
    );
    $editPaciente->execute([(int)$_GET['edit']]);
    $editPaciente = $editPaciente->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']   ?? '');
    $apellidos= trim($_POST['apellidos']?? '');
    $email    = trim($_POST['email']    ?? '');
    $dni      = strtoupper(trim($_POST['dni'] ?? ''));
    $fnac     = $_POST['fecha_nacimiento'] ?? '';
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion= trim($_POST['direccion']?? '');
    $pacId    = (int)($_POST['pac_id'] ?? 0);

    if (!$nombre || !$apellidos || !$email || !$dni || !$fnac) {
        $err = 'Nombre, apellidos, email, DNI y fecha de nacimiento son obligatorios.';
    } elseif (!preg_match('/^[0-9]{8}[A-Z]$/', $dni)) {
        $err = 'El DNI no es válido. Debe tener exactamente 8 dígitos seguidos de una letra (ej: 12345678A). No se admite la Ñ.';
    } else {
        try {
            if ($pacId > 0) {
                // EDITAR
                $stmt = $db->prepare(
                    'SELECT usuario_id FROM pacientes WHERE id = ?'
                );
                $stmt->execute([$pacId]);
                $uid = $stmt->fetchColumn();
                $db->prepare('UPDATE usuarios SET nombre=?,apellidos=?,email=? WHERE id=?')
                   ->execute([$nombre,$apellidos,$email,$uid]);
                $db->prepare('UPDATE pacientes SET dni=?,fecha_nacimiento=?,telefono=?,direccion=? WHERE id=?')
                   ->execute([$dni,$fnac,$telefono,$direccion,$pacId]);
                $msg = 'Paciente actualizado correctamente.';
            } else {
                // NUEVO — contraseña temporal "Paciente1234!"
                $hash = password_hash('Paciente1234!', PASSWORD_DEFAULT);
                $db->prepare('INSERT INTO usuarios (nombre,apellidos,email,password,rol) VALUES (?,?,?,?,?)')
                   ->execute([$nombre,$apellidos,$email,$hash,'paciente']);
                $uid = $db->lastInsertId();
                $db->prepare('INSERT INTO pacientes (usuario_id,dni,fecha_nacimiento,telefono,direccion) VALUES (?,?,?,?,?)')
                   ->execute([$uid,$dni,$fnac,$telefono,$direccion]);
                $msg = 'Paciente registrado. Contraseña temporal: <strong>Paciente1234!</strong>';
            }
            $editPaciente = null;
        } catch (PDOException $e) {
            $err = str_contains($e->getMessage(), 'Duplicate')
                ? 'El email o DNI ya está registrado.'
                : 'Error al guardar: ' . $e->getMessage();
        }
    }
}

$pacientes = $db->query(
    'SELECT p.id, p.dni, p.fecha_nacimiento, p.telefono,
            u.nombre, u.apellidos, u.email,
            (SELECT COUNT(*) FROM revisiones r WHERE r.paciente_id = p.id) AS num_rev
     FROM pacientes p
     JOIN usuarios u ON u.id = p.usuario_id
     ORDER BY u.apellidos, u.nombre'
)->fetchAll();

$pageTitle = 'Gestión de pacientes — Óptica Vision';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="page-header">
        <h1>Pacientes</h1>
        <button class="btn btn-primary" onclick="document.getElementById('form-pac').scrollIntoView({behavior:'smooth'})">
            + Nuevo paciente
        </button>
    </div>

    <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <!-- Tabla -->
    <div class="card" style="margin-bottom:2rem">
        <div class="card-title">Listado de pacientes (<?= count($pacientes) ?>)</div>
        <?php if ($pacientes): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Nombre</th><th>Email</th><th>DNI</th><th>F. Nacimiento</th><th>Teléfono</th><th>Revisiones</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                <?php foreach ($pacientes as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?></td>
                        <td><?= htmlspecialchars($p['email']) ?></td>
                        <td><?= htmlspecialchars($p['dni']) ?></td>
                        <td><?= date('d/m/Y', strtotime($p['fecha_nacimiento'])) ?></td>
                        <td><?= htmlspecialchars($p['telefono'] ?? '—') ?></td>
                        <td style="text-align:center"><?= $p['num_rev'] ?></td>
                        <td>
                            <a href="?edit=<?= $p['id'] ?>" class="btn btn-sm btn-accent">Editar</a>
                            <a href="nueva_revision.php?paciente_id=<?= $p['id'] ?>" class="btn btn-sm btn-success">+ Revisión</a>
                            <a href="?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger"
                               data-confirm="¿Eliminar a <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?> y todos sus datos?">
                               Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state"><span class="empty-icon">&#128101;</span><p>No hay pacientes registrados aún.</p></div>
        <?php endif; ?>
    </div>

    <!-- Formulario alta/edición -->
    <div class="card" id="form-pac">
        <div class="card-title"><?= $editPaciente ? 'Editar paciente' : 'Registrar nuevo paciente' ?></div>
        <form method="POST">
            <?php if ($editPaciente): ?>
                <input type="hidden" name="pac_id" value="<?= $editPaciente['id'] ?>">
            <?php endif; ?>
            <div class="form-grid">
                <div class="form-group">
                    <label>Nombre *</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($editPaciente['nombre'] ?? $_POST['nombre'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Apellidos *</label>
                    <input type="text" name="apellidos" value="<?= htmlspecialchars($editPaciente['apellidos'] ?? $_POST['apellidos'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($editPaciente['email'] ?? $_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>DNI *</label>
                    <input type="text" name="dni" maxlength="9"
                           pattern="[0-9]{8}[A-Za-z]"
                           title="8 dígitos seguidos de una letra (ej: 12345678A)"
                           placeholder="12345678A"
                           style="text-transform:uppercase"
                           value="<?= htmlspecialchars($editPaciente['dni'] ?? $_POST['dni'] ?? '') ?>"
                           required>
                    <small style="color:var(--text-muted)">8 dígitos + 1 letra (A-Z)</small>
                </div>
                <div class="form-group">
                    <label>Fecha de nacimiento *</label>
                    <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($editPaciente['fecha_nacimiento'] ?? $_POST['fecha_nacimiento'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" name="telefono" value="<?= htmlspecialchars($editPaciente['telefono'] ?? $_POST['telefono'] ?? '') ?>">
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label>Dirección</label>
                    <input type="text" name="direccion" value="<?= htmlspecialchars($editPaciente['direccion'] ?? $_POST['direccion'] ?? '') ?>">
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $editPaciente ? 'Guardar cambios' : 'Registrar paciente' ?>
                </button>
                <?php if ($editPaciente): ?>
                    <a href="pacientes.php" class="btn btn-outline">Cancelar</a>
                <?php endif; ?>
            </div>
            <?php if (!$editPaciente): ?>
                <p style="margin-top:.75rem;font-size:.82rem;color:var(--text-muted)">
                    La contraseña inicial será <strong>Paciente1234!</strong> y deberá ser cambiada en el primer acceso.
                </p>
            <?php endif; ?>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
