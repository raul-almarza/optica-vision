<?php
define('BASE_URL', '/Optica-web');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

// Eliminar revisión
if (isset($_GET['delete']) && ctype_digit($_GET['delete'])) {
    $db->prepare('DELETE FROM revisiones WHERE id = ?')->execute([(int)$_GET['delete']]);
    header('Location: revisiones.php?ok=1');
    exit;
}

$msg = isset($_GET['ok']) ? 'Revisión eliminada.' : '';

// Filtro por paciente
$filtroPac = (int)($_GET['paciente_id'] ?? 0);

$sql = 'SELECT r.*, u.nombre, u.apellidos, p.id AS pac_id
        FROM revisiones r
        JOIN pacientes p ON p.id = r.paciente_id
        JOIN usuarios  u ON u.id = p.usuario_id';
$params = [];
if ($filtroPac) {
    $sql .= ' WHERE r.paciente_id = ?';
    $params[] = $filtroPac;
}
$sql .= ' ORDER BY r.fecha DESC, r.id DESC';

$revisiones = $db->prepare($sql);
$revisiones->execute($params);
$revisiones = $revisiones->fetchAll();

$pacientes = $db->query('SELECT p.id, u.nombre, u.apellidos FROM pacientes p JOIN usuarios u ON u.id = p.usuario_id ORDER BY u.apellidos')->fetchAll();

$pageTitle = 'Historial de revisiones — Óptica Vision';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="page-header">
        <h1>Historial de revisiones</h1>
        <a href="nueva_revision.php" class="btn btn-primary">+ Nueva revisión</a>
    </div>

    <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <!-- Filtro -->
    <form method="GET" style="margin-bottom:1.25rem;display:flex;gap:.75rem;align-items:center">
        <label style="font-weight:600;color:var(--primary)">Filtrar por paciente:</label>
        <select name="paciente_id" onchange="this.form.submit()" style="padding:.4rem .7rem;border:1px solid var(--border);border-radius:var(--radius)">
            <option value="0">— Todos —</option>
            <?php foreach ($pacientes as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $p['id'] === $filtroPac ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['apellidos'] . ', ' . $p['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($filtroPac): ?><a href="revisiones.php" class="btn btn-sm btn-outline">Limpiar</a><?php endif; ?>
    </form>

    <div class="card">
        <?php if ($revisiones): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr><th>Paciente</th><th>Fecha</th><th>Optometrista</th><th>OD</th><th>OI</th><th>Diagnóstico</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                <?php foreach ($revisiones as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['nombre'] . ' ' . $r['apellidos']) ?></td>
                        <td><?= date('d/m/Y', strtotime($r['fecha'])) ?></td>
                        <td><?= htmlspecialchars($r['optometrista'] ?? '—') ?></td>
                        <td style="font-size:.83rem;white-space:nowrap">
                            <?= $r['od_esfera'] !== null ? sprintf('%+.2f', $r['od_esfera']) : '—' ?>
                            <?= $r['od_cilindro'] !== null ? ' / ' . sprintf('%+.2f', $r['od_cilindro']) : '' ?>
                            <?= $r['od_eje'] !== null ? ' × ' . $r['od_eje'] . '°' : '' ?>
                        </td>
                        <td style="font-size:.83rem;white-space:nowrap">
                            <?= $r['oi_esfera'] !== null ? sprintf('%+.2f', $r['oi_esfera']) : '—' ?>
                            <?= $r['oi_cilindro'] !== null ? ' / ' . sprintf('%+.2f', $r['oi_cilindro']) : '' ?>
                            <?= $r['oi_eje'] !== null ? ' × ' . $r['oi_eje'] . '°' : '' ?>
                        </td>
                        <td><?= htmlspecialchars(mb_strimwidth($r['diagnostico'] ?? '—', 0, 45, '…')) ?></td>
                        <td>
                            <a href="?delete=<?= $r['id'] ?>"
                               class="btn btn-sm btn-danger"
                               data-confirm="¿Eliminar esta revisión?">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <span class="empty-icon">&#128203;</span>
                <p>No hay revisiones registradas<?= $filtroPac ? ' para este paciente' : '' ?>.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
