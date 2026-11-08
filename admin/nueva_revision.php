<?php
define('BASE_URL', '/Optica-web');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db  = getDB();
$msg = '';
$err = '';

$pacientes = $db->query(
    'SELECT p.id, u.nombre, u.apellidos FROM pacientes p JOIN usuarios u ON u.id = p.usuario_id ORDER BY u.apellidos, u.nombre'
)->fetchAll();

$preselected = (int)($_GET['paciente_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pacId   = (int)($_POST['paciente_id'] ?? 0);
    $fecha   = $_POST['fecha']         ?? '';
    $optom   = trim($_POST['optometrista'] ?? '');
    $odEsf   = $_POST['od_esfera']   !== '' ? (float)$_POST['od_esfera']   : null;
    $odCil   = $_POST['od_cilindro'] !== '' ? (float)$_POST['od_cilindro'] : null;
    $odEje   = $_POST['od_eje']      !== '' ? (int)$_POST['od_eje']        : null;
    $oiEsf   = $_POST['oi_esfera']   !== '' ? (float)$_POST['oi_esfera']   : null;
    $oiCil   = $_POST['oi_cilindro'] !== '' ? (float)$_POST['oi_cilindro'] : null;
    $oiEje   = $_POST['oi_eje']      !== '' ? (int)$_POST['oi_eje']        : null;
    $diag    = trim($_POST['diagnostico']   ?? '');
    $obs     = trim($_POST['observaciones'] ?? '');

    if (!$pacId || !$fecha) {
        $err = 'Paciente y fecha son obligatorios.';
    } else {
        $db->prepare(
            'INSERT INTO revisiones
             (paciente_id, fecha, optometrista, od_esfera, od_cilindro, od_eje, oi_esfera, oi_cilindro, oi_eje, diagnostico, observaciones)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)'
        )->execute([$pacId,$fecha,$optom,$odEsf,$odCil,$odEje,$oiEsf,$oiCil,$oiEje,$diag,$obs]);
        $msg = 'Revisión registrada correctamente.';
        $preselected = $pacId;
    }
}

$pageTitle = 'Nueva revisión ocular — Óptica Vision';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container" style="max-width:800px">
    <div class="page-header">
        <h1>Nueva revisión ocular</h1>
        <a href="revisiones.php" class="btn btn-outline">← Ver historial</a>
    </div>

    <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

    <div class="card">
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Paciente *</label>
                    <select name="paciente_id" required>
                        <option value="">— Selecciona paciente —</option>
                        <?php foreach ($pacientes as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= $p['id'] === $preselected ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['apellidos'] . ', ' . $p['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fecha de la revisión *</label>
                    <input type="date" name="fecha" value="<?= $_POST['fecha'] ?? date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label>Optometrista</label>
                    <input type="text" name="optometrista" value="<?= htmlspecialchars($_POST['optometrista'] ?? '') ?>" placeholder="Dra./Dr. Apellido">
                </div>
            </div>

            <!-- Graduación -->
            <h3 style="margin:1.5rem 0 .75rem;color:var(--primary);font-size:1rem">Graduación</h3>
            <div class="table-wrapper">
                <table class="grad-table">
                    <thead>
                        <tr><th></th><th>Esfera</th><th>Cilindro</th><th>Eje (0-180°)</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ojo-label">OD — Ojo derecho</td>
                            <td><input type="number" name="od_esfera"   step="0.25" value="<?= $_POST['od_esfera']   ?? '' ?>" style="width:80px" placeholder="+/-0.00"></td>
                            <td><input type="number" name="od_cilindro" step="0.25" value="<?= $_POST['od_cilindro'] ?? '' ?>" style="width:80px" placeholder="+/-0.00"></td>
                            <td><input type="number" name="od_eje"      min="0" max="180" value="<?= $_POST['od_eje'] ?? '' ?>" style="width:70px"></td>
                        </tr>
                        <tr>
                            <td class="ojo-label">OI — Ojo izquierdo</td>
                            <td><input type="number" name="oi_esfera"   step="0.25" value="<?= $_POST['oi_esfera']   ?? '' ?>" style="width:80px" placeholder="+/-0.00"></td>
                            <td><input type="number" name="oi_cilindro" step="0.25" value="<?= $_POST['oi_cilindro'] ?? '' ?>" style="width:80px" placeholder="+/-0.00"></td>
                            <td><input type="number" name="oi_eje"      min="0" max="180" value="<?= $_POST['oi_eje'] ?? '' ?>" style="width:70px"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="form-grid" style="margin-top:1.25rem">
                <div class="form-group" style="grid-column:1/-1">
                    <label>Diagnóstico</label>
                    <textarea name="diagnostico" rows="2"><?= htmlspecialchars($_POST['diagnostico'] ?? '') ?></textarea>
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label>Observaciones</label>
                    <textarea name="observaciones" rows="2"><?= htmlspecialchars($_POST['observaciones'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar revisión</button>
                <a href="revisiones.php" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
