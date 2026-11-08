<?php
define('BASE_URL', '/Optica-web');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requirePaciente();

$db  = getDB();
$uid = $_SESSION['usuario_id'];

$pac = $db->prepare('SELECT id FROM pacientes WHERE usuario_id = ?');
$pac->execute([$uid]);
$pacId = $pac->fetchColumn();

$citas = $db->prepare(
    'SELECT * FROM citas WHERE paciente_id = ? ORDER BY fecha_hora DESC'
);
$citas->execute([$pacId]);
$citas = $citas->fetchAll();

$pageTitle = 'Mis citas — Óptica Vision';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="page-header">
        <h1>Mis citas</h1>
    </div>

    <?php if ($citas): ?>
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Fecha y hora</th><th>Motivo</th><th>Estado</th></tr>
                </thead>
                <tbody>
                <?php foreach ($citas as $c): ?>
                    <tr>
                        <td style="white-space:nowrap"><?= date('d/m/Y H:i', strtotime($c['fecha_hora'])) ?></td>
                        <td><?= htmlspecialchars($c['motivo'] ?? '—') ?></td>
                        <td><span class="badge badge-<?= $c['estado'] ?>"><?= $c['estado'] ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
        <div class="card">
            <div class="empty-state">
                <span class="empty-icon">&#128197;</span>
                <p>No tienes citas programadas.</p>
                <p style="font-size:.85rem;margin-top:.5rem">Contacta con la óptica para solicitar una cita.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
