<?php
define('BASE_URL', '/Optica-web');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requirePaciente();

$db  = getDB();
$uid = $_SESSION['usuario_id'];

$paciente = $db->prepare(
    'SELECT p.*, u.nombre, u.apellidos, u.email FROM pacientes p JOIN usuarios u ON u.id = p.usuario_id WHERE u.id = ?'
);
$paciente->execute([$uid]);
$pac = $paciente->fetch();

if (!$pac) {
    echo '<p>Error: perfil de paciente no encontrado.</p>';
    exit;
}

$numRevisiones = $db->prepare('SELECT COUNT(*) FROM revisiones WHERE paciente_id = ?');
$numRevisiones->execute([$pac['id']]);
$numRev = $numRevisiones->fetchColumn();

$ultimaRevision = $db->prepare(
    'SELECT * FROM revisiones WHERE paciente_id = ? ORDER BY fecha DESC, id DESC LIMIT 1'
);
$ultimaRevision->execute([$pac['id']]);
$rev = $ultimaRevision->fetch();

$proximaCita = $db->prepare(
    "SELECT * FROM citas WHERE paciente_id = ? AND fecha_hora >= NOW() AND estado != 'cancelada' ORDER BY fecha_hora ASC LIMIT 1"
);
$proximaCita->execute([$pac['id']]);
$cita = $proximaCita->fetch();

$pageTitle = 'Mi panel — Óptica Vision';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="page-header">
        <h1>Bienvenido, <?= htmlspecialchars($pac['nombre']) ?></h1>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <img src="<?= BASE_URL ?>/assets/img/revisiones.png" alt="Revisiones" class="stat-img">
            </div>
            <div>
                <div class="stat-value"><?= $numRev ?></div>
                <div class="stat-label">Revisiones realizadas</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <img src="<?= BASE_URL ?>/assets/img/citas.png" alt="Citas" class="stat-img">
            </div>
            <div>
                <div class="stat-value"><?= $cita ? date('d/m', strtotime($cita['fecha_hora'])) : '—' ?></div>
                <div class="stat-label">Próxima cita</div>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Última revisión -->
        <div class="card">
            <div class="card-title">Mi última revisión</div>
            <?php if ($rev): ?>
                <p style="color:var(--text-muted);font-size:.88rem;margin-bottom:.75rem">
                    <?= date('d/m/Y', strtotime($rev['fecha'])) ?>
                    <?= $rev['optometrista'] ? ' — ' . htmlspecialchars($rev['optometrista']) : '' ?>
                </p>
                <table class="grad-table" style="margin-bottom:.75rem">
                    <thead><tr><th></th><th>Esfera</th><th>Cilindro</th><th>Eje</th></tr></thead>
                    <tbody>
                        <tr>
                            <td class="ojo-label">OD</td>
                            <td><?= $rev['od_esfera'] !== null ? sprintf('%+.2f', $rev['od_esfera']) : '—' ?></td>
                            <td><?= $rev['od_cilindro'] !== null ? sprintf('%+.2f', $rev['od_cilindro']) : '—' ?></td>
                            <td><?= $rev['od_eje'] !== null ? $rev['od_eje'] . '°' : '—' ?></td>
                        </tr>
                        <tr>
                            <td class="ojo-label">OI</td>
                            <td><?= $rev['oi_esfera'] !== null ? sprintf('%+.2f', $rev['oi_esfera']) : '—' ?></td>
                            <td><?= $rev['oi_cilindro'] !== null ? sprintf('%+.2f', $rev['oi_cilindro']) : '—' ?></td>
                            <td><?= $rev['oi_eje'] !== null ? $rev['oi_eje'] . '°' : '—' ?></td>
                        </tr>
                    </tbody>
                </table>
                <?php if ($rev['diagnostico']): ?>
                    <p><strong>Diagnóstico:</strong> <?= htmlspecialchars($rev['diagnostico']) ?></p>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state"><span class="empty-icon">&#128203;</span><p>Sin revisiones registradas aún.</p></div>
            <?php endif; ?>
            <div style="margin-top:1rem">
                <a href="revisiones.php" class="btn btn-sm btn-accent">Ver historial completo</a>
            </div>
        </div>

        <!-- Datos personales -->
        <div class="card">
            <div class="card-title">Mis datos</div>
            <table style="font-size:.9rem;width:100%">
                <tr><td style="color:var(--text-muted);width:40%;padding:.3rem 0">Nombre</td><td><?= htmlspecialchars($pac['nombre'] . ' ' . $pac['apellidos']) ?></td></tr>
                <tr><td style="color:var(--text-muted);padding:.3rem 0">Email</td><td><?= htmlspecialchars($pac['email']) ?></td></tr>
                <tr><td style="color:var(--text-muted);padding:.3rem 0">DNI</td><td><?= htmlspecialchars($pac['dni']) ?></td></tr>
                <tr><td style="color:var(--text-muted);padding:.3rem 0">F. nacimiento</td><td><?= date('d/m/Y', strtotime($pac['fecha_nacimiento'])) ?></td></tr>
                <tr><td style="color:var(--text-muted);padding:.3rem 0">Teléfono</td><td><?= htmlspecialchars($pac['telefono'] ?? '—') ?></td></tr>
                <tr><td style="color:var(--text-muted);padding:.3rem 0">Dirección</td><td><?= htmlspecialchars($pac['direccion'] ?? '—') ?></td></tr>
            </table>
            <div style="margin-top:1rem">
                <a href="citas.php" class="btn btn-sm btn-accent">Ver mis citas</a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
