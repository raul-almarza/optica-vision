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

$revisiones = $db->prepare(
    'SELECT * FROM revisiones WHERE paciente_id = ? ORDER BY fecha DESC, id DESC'
);
$revisiones->execute([$pacId]);
$revisiones = $revisiones->fetchAll();

$pageTitle = 'Mis revisiones — Óptica Vision';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="page-header">
        <h1>Mi historial de revisiones</h1>
    </div>

    <?php if ($revisiones): ?>
        <?php foreach ($revisiones as $r): ?>
        <div class="card" style="margin-bottom:1.25rem">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                <h2 style="font-size:1.05rem;color:var(--primary)">
                    Revisión del <?= date('d/m/Y', strtotime($r['fecha'])) ?>
                </h2>
                <?php if ($r['optometrista']): ?>
                    <span style="font-size:.85rem;color:var(--text-muted)"><?= htmlspecialchars($r['optometrista']) ?></span>
                <?php endif; ?>
            </div>

            <table class="grad-table" style="margin-bottom:1rem;max-width:500px">
                <thead><tr><th></th><th>Esfera</th><th>Cilindro</th><th>Eje</th></tr></thead>
                <tbody>
                    <tr>
                        <td class="ojo-label">OD — Ojo derecho</td>
                        <td><?= $r['od_esfera'] !== null ? sprintf('%+.2f', $r['od_esfera']) : '—' ?></td>
                        <td><?= $r['od_cilindro'] !== null ? sprintf('%+.2f', $r['od_cilindro']) : '—' ?></td>
                        <td><?= $r['od_eje'] !== null ? $r['od_eje'] . '°' : '—' ?></td>
                    </tr>
                    <tr>
                        <td class="ojo-label">OI — Ojo izquierdo</td>
                        <td><?= $r['oi_esfera'] !== null ? sprintf('%+.2f', $r['oi_esfera']) : '—' ?></td>
                        <td><?= $r['oi_cilindro'] !== null ? sprintf('%+.2f', $r['oi_cilindro']) : '—' ?></td>
                        <td><?= $r['oi_eje'] !== null ? $r['oi_eje'] . '°' : '—' ?></td>
                    </tr>
                </tbody>
            </table>

            <?php if ($r['diagnostico']): ?>
                <p style="margin-bottom:.4rem"><strong>Diagnóstico:</strong> <?= htmlspecialchars($r['diagnostico']) ?></p>
            <?php endif; ?>
            <?php if ($r['observaciones']): ?>
                <p style="color:var(--text-muted);font-size:.9rem"><strong>Observaciones:</strong> <?= htmlspecialchars($r['observaciones']) ?></p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card">
            <div class="empty-state">
                <span class="empty-icon">&#128203;</span>
                <p>Aún no tienes revisiones registradas.</p>
                <p style="font-size:.85rem;margin-top:.5rem">Contacta con la óptica para programar tu primera revisión.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
