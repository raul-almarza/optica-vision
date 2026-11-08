<?php
define('BASE_URL', '/Optica-web');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

$totalPacientes  = $db->query('SELECT COUNT(*) FROM pacientes')->fetchColumn();
$totalRevisiones = $db->query('SELECT COUNT(*) FROM revisiones')->fetchColumn();
$citasPendientes = $db->query("SELECT COUNT(*) FROM citas WHERE estado = 'pendiente'")->fetchColumn();
$citasHoy        = $db->query("SELECT COUNT(*) FROM citas WHERE DATE(fecha_hora) = CURDATE()")->fetchColumn();

$proximasCitas = $db->query(
    "SELECT c.fecha_hora, c.motivo, c.estado,
            u.nombre, u.apellidos
     FROM citas c
     JOIN pacientes p ON p.id = c.paciente_id
     JOIN usuarios  u ON u.id = p.usuario_id
     WHERE c.fecha_hora >= NOW()
     ORDER BY c.fecha_hora ASC
     LIMIT 5"
)->fetchAll();

$ultimasRevisiones = $db->query(
    "SELECT r.fecha, r.optometrista, r.diagnostico,
            u.nombre, u.apellidos
     FROM revisiones r
     JOIN pacientes p ON p.id = r.paciente_id
     JOIN usuarios  u ON u.id = p.usuario_id
     ORDER BY r.fecha DESC, r.id DESC
     LIMIT 5"
)->fetchAll();

$pageTitle = 'Panel de administración — Óptica Vision';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="page-header">
        <h1>Panel de administración</h1>
        <span style="color:var(--text-muted);font-size:.9rem">
            Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?>
        </span>
    </div>

    <!-- Estadísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <img src="<?= BASE_URL ?>/assets/img/pacientes.png" alt="Pacientes" class="stat-img">
            </div>
            <div>
                <div class="stat-value"><?= $totalPacientes ?></div>
                <div class="stat-label">Pacientes registrados</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <img src="<?= BASE_URL ?>/assets/img/revisiones.png" alt="Revisiones" class="stat-img">
            </div>
            <div>
                <div class="stat-value"><?= $totalRevisiones ?></div>
                <div class="stat-label">Revisiones realizadas</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <img src="<?= BASE_URL ?>/assets/img/citas.png" alt="Citas" class="stat-img">
            </div>
            <div>
                <div class="stat-value"><?= $citasPendientes ?></div>
                <div class="stat-label">Citas pendientes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <img src="<?= BASE_URL ?>/assets/img/cita-hoy.png" alt="Citas hoy" class="stat-img">
            </div>
            <div>
                <div class="stat-value"><?= $citasHoy ?></div>
                <div class="stat-label">Citas hoy</div>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Próximas citas -->
        <div class="card">
            <div class="card-title">Próximas citas</div>
            <?php if ($proximasCitas): ?>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Paciente</th><th>Fecha</th><th>Estado</th></tr></thead>
                    <tbody>
                    <?php foreach ($proximasCitas as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellidos']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($c['fecha_hora'])) ?></td>
                            <td><span class="badge badge-<?= $c['estado'] ?>"><?= $c['estado'] ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p class="empty-state"><span class="empty-icon">&#128197;</span><br>Sin citas próximas</p>
            <?php endif; ?>
            <div style="margin-top:1rem">
                <a href="<?= BASE_URL ?>/admin/citas.php" class="btn btn-sm btn-accent">Ver todas las citas</a>
            </div>
        </div>

        <!-- Últimas revisiones -->
        <div class="card">
            <div class="card-title">Últimas revisiones</div>
            <?php if ($ultimasRevisiones): ?>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Paciente</th><th>Fecha</th><th>Diagnóstico</th></tr></thead>
                    <tbody>
                    <?php foreach ($ultimasRevisiones as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['nombre'] . ' ' . $r['apellidos']) ?></td>
                            <td><?= date('d/m/Y', strtotime($r['fecha'])) ?></td>
                            <td><?= htmlspecialchars(mb_strimwidth($r['diagnostico'] ?? '—', 0, 40, '…')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p class="empty-state"><span class="empty-icon">&#128203;</span><br>Sin revisiones</p>
            <?php endif; ?>
            <div style="margin-top:1rem">
                <a href="<?= BASE_URL ?>/admin/revisiones.php" class="btn btn-sm btn-accent">Ver todas</a>
                <a href="<?= BASE_URL ?>/admin/nueva_revision.php" class="btn btn-sm btn-primary" style="margin-left:.5rem">+ Nueva revisión</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
