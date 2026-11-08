<?php
define('BASE_URL', '/Optica-web');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();

$citas = $db->query(
    "SELECT c.id, c.fecha_hora, c.motivo, c.estado,
            u.nombre, u.apellidos, p.id AS pac_id
     FROM citas c
     JOIN pacientes p ON p.id  = c.paciente_id
     JOIN usuarios  u ON u.id  = p.usuario_id
     ORDER BY c.fecha_hora"
)->fetchAll();

$colorMap = [
    'pendiente'  => ['bg' => '#f59e0b', 'border' => '#d97706', 'text' => '#1c1917'],
    'confirmada' => ['bg' => '#22c55e', 'border' => '#16a34a', 'text' => '#ffffff'],
    'cancelada'  => ['bg' => '#ef4444', 'border' => '#dc2626', 'text' => '#ffffff'],
    'completada' => ['bg' => '#8b5cf6', 'border' => '#7c3aed', 'text' => '#ffffff'],
];

$events = [];
foreach ($citas as $c) {
    $col = $colorMap[$c['estado']] ?? ['bg' => '#4a90d9', 'border' => '#2a70b9', 'text' => '#fff'];
    $events[] = [
        'id'              => $c['id'],
        'title'           => $c['nombre'] . ' ' . $c['apellidos'],
        'start'           => $c['fecha_hora'],
        'backgroundColor' => $col['bg'],
        'borderColor'     => $col['border'],
        'textColor'       => $col['text'],
        'extendedProps'   => [
            'motivo'   => $c['motivo']   ?? '—',
            'estado'   => $c['estado'],
            'paciente' => $c['nombre'] . ' ' . $c['apellidos'],
            'pac_id'   => $c['pac_id'],
            'hora'     => date('H:i', strtotime($c['fecha_hora'])),
            'fecha'    => date('d/m/Y', strtotime($c['fecha_hora'])),
        ],
    ];
}
$eventsJson = json_encode($events, JSON_UNESCAPED_UNICODE);

$pageTitle = 'Calendario de citas — Óptica Vision';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- FullCalendar CDN -->
<link  href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

<div class="container">
    <div class="page-header">
        <h1>Calendario de citas</h1>
        <a href="citas.php" class="btn btn-primary">+ Nueva cita</a>
    </div>

    <!-- Leyenda de estados -->
    <div class="cal-legend">
        <span class="legend-dot" style="background:#f59e0b"></span>Pendiente
        <span class="legend-dot" style="background:#22c55e"></span>Confirmada
        <span class="legend-dot" style="background:#ef4444"></span>Cancelada
        <span class="legend-dot" style="background:#8b5cf6"></span>Completada
    </div>

    <div class="card cal-card">
        <div id="calendar"></div>
    </div>
</div>

<!-- Modal detalle de cita -->
<div id="cal-modal" class="cal-modal-overlay" style="display:none">
    <div class="cal-modal-box">
        <button class="cal-modal-close" id="cal-close" aria-label="Cerrar">&times;</button>
        <div class="cal-modal-header" id="cal-modal-color">
            <h2 id="cal-modal-paciente"></h2>
            <span class="badge" id="cal-modal-badge"></span>
        </div>
        <div class="cal-modal-body">
            <div class="cal-detail"><span>📅 Fecha</span><strong id="cal-modal-fecha"></strong></div>
            <div class="cal-detail"><span>🕐 Hora</span><strong id="cal-modal-hora"></strong></div>
            <div class="cal-detail"><span>📋 Motivo</span><strong id="cal-modal-motivo"></strong></div>
        </div>
        <div class="cal-modal-actions">
            <a id="cal-modal-link" href="#" class="btn btn-accent">Ver historial del paciente</a>
            <a id="cal-modal-citas" href="citas.php" class="btn btn-outline">Gestionar citas</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var events  = <?= $eventsJson ?>;
    var baseUrl = '<?= BASE_URL ?>';

    var cal = new FullCalendar.Calendar(document.getElementById('calendar'), {
        locale:          'es',
        initialView:     'dayGridMonth',
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,listMonth'
        },
        buttonText: {
            today:    'Hoy',
            month:    'Mes',
            week:     'Semana',
            list:     'Lista'
        },
        events:          events,
        height:          'auto',
        eventDisplay:    'block',
        dayMaxEvents:    3,
        navLinks:        true,
        nowIndicator:    true,
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },

        eventClick: function (info) {
            var p  = info.event.extendedProps;
            var el = document.getElementById;

            el('cal-modal-paciente').textContent = p.paciente;
            el('cal-modal-fecha').textContent    = p.fecha;
            el('cal-modal-hora').textContent     = p.hora;
            el('cal-modal-motivo').textContent   = p.motivo;

            var badge = el('cal-modal-badge');
            badge.textContent  = p.estado;
            badge.className    = 'badge badge-' + p.estado;

            var header = el('cal-modal-color');
            header.style.borderLeftColor = info.event.borderColor;

            el('cal-modal-link').href  = baseUrl + '/admin/revisiones.php?paciente_id=' + p.pac_id;

            document.getElementById('cal-modal').style.display = 'flex';
        }
    });

    cal.render();

    // Cerrar modal
    document.getElementById('cal-close').addEventListener('click', closeModal);
    document.getElementById('cal-modal').addEventListener('click', function (e) {
        if (e.target === this) closeModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModal();
    });

    function closeModal() {
        document.getElementById('cal-modal').style.display = 'none';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
