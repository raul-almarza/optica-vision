<?php
define('BASE_URL', '/Optica-web');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db  = getDB();
$msg = '';
$err = '';

// Cambiar estado
if (isset($_GET['estado'], $_GET['id']) && ctype_digit($_GET['id'])) {
    $estados = ['pendiente','confirmada','cancelada','completada'];
    if (in_array($_GET['estado'], $estados, true)) {
        $db->prepare('UPDATE citas SET estado = ? WHERE id = ?')->execute([$_GET['estado'], (int)$_GET['id']]);
        header('Location: citas.php?ok=1');
        exit;
    }
}
// Eliminar
if (isset($_GET['delete']) && ctype_digit($_GET['delete'])) {
    $db->prepare('DELETE FROM citas WHERE id = ?')->execute([(int)$_GET['delete']]);
    header('Location: citas.php?ok=1');
    exit;
}

$msg = isset($_GET['ok']) ? 'Operación realizada correctamente.' : '';

// Nueva cita
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pacId    = (int)($_POST['paciente_id'] ?? 0);
    $fechaHora= trim($_POST['fecha_hora'] ?? '');
    $motivo   = trim($_POST['motivo']     ?? '');
    $estado   = $_POST['estado']          ?? 'pendiente';

    if (!$pacId || !$fechaHora) {
        $err = 'Paciente y fecha/hora son obligatorios.';
    } else {
        $db->prepare('INSERT INTO citas (paciente_id,fecha_hora,motivo,estado) VALUES (?,?,?,?)')
           ->execute([$pacId,$fechaHora,$motivo,$estado]);
        $msg = 'Cita creada correctamente.';
    }
}

$citas = $db->query(
    "SELECT c.id, c.fecha_hora, c.motivo, c.estado,
            u.nombre, u.apellidos, p.id AS pac_id
     FROM citas c
     JOIN pacientes p ON p.id = c.paciente_id
     JOIN usuarios  u ON u.id = p.usuario_id
     ORDER BY c.fecha_hora DESC"
)->fetchAll();

$pacientes = $db->query('SELECT p.id, u.nombre, u.apellidos FROM pacientes p JOIN usuarios u ON u.id = p.usuario_id ORDER BY u.apellidos')->fetchAll();

$pageTitle = 'Gestión de citas — Óptica Vision';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="page-header">
        <h1>Citas</h1>
    </div>

    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <!-- Tabla citas -->
    <div class="card" style="margin-bottom:2rem">
        <div class="card-title">Todas las citas (<?= count($citas) ?>)</div>
        <?php if ($citas): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Paciente</th><th>Fecha y hora</th><th>Motivo</th><th>Estado</th><th>Cambiar estado</th><th>Eliminar</th></tr>
                </thead>
                <tbody>
                <?php foreach ($citas as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellidos']) ?></td>
                        <td style="white-space:nowrap"><?= date('d/m/Y H:i', strtotime($c['fecha_hora'])) ?></td>
                        <td><?= htmlspecialchars($c['motivo'] ?? '—') ?></td>
                        <td><span class="badge badge-<?= $c['estado'] ?>"><?= $c['estado'] ?></span></td>
                        <td style="white-space:nowrap">
                            <?php
                            $otros = array_filter(['pendiente','confirmada','cancelada','completada'], fn($s) => $s !== $c['estado']);
                            foreach ($otros as $s):
                            ?>
                                <a href="?estado=<?= $s ?>&id=<?= $c['id'] ?>" class="btn btn-sm" style="margin:.1rem;
                                    background:<?= $s==='confirmada'?'#d1e7dd;color:#0f5132' : ($s==='cancelada'?'#f8d7da;color:#842029' : ($s==='completada'?'#d3d3f7;color:#3d3d8a':'#fff3cd;color:#856404')) ?>">
                                    <?= ucfirst($s) ?>
                                </a>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            <a href="?delete=<?= $c['id'] ?>" class="btn btn-sm btn-danger"
                               data-confirm="¿Eliminar esta cita?">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state"><span class="empty-icon">&#128197;</span><p>No hay citas registradas.</p></div>
        <?php endif; ?>
    </div>

    <!-- Nueva cita -->
    <div class="card">
        <div class="card-title">Crear nueva cita</div>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Paciente *</label>
                    <select name="paciente_id" required>
                        <option value="">— Selecciona paciente —</option>
                        <?php foreach ($pacientes as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['apellidos'] . ', ' . $p['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fecha y hora *</label>
                    <input type="datetime-local" name="fecha_hora" required>
                </div>
                <div class="form-group">
                    <label>Motivo</label>
                    <input type="text" name="motivo" placeholder="Revisión anual, adaptación, etc.">
                </div>
                <div class="form-group">
                    <label>Estado inicial</label>
                    <select name="estado">
                        <option value="pendiente">Pendiente</option>
                        <option value="confirmada">Confirmada</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Crear cita</button>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
