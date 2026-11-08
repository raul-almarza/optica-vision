// Óptica Vision — main.js

// ---- Menú hamburguesa (móvil) ----
(function () {
    var btn = document.getElementById('hamburger');
    var nav = document.getElementById('main-nav');
    if (!btn || !nav) return;

    btn.addEventListener('click', function () {
        var open = nav.classList.toggle('nav-open');
        btn.classList.toggle('open', open);
        btn.setAttribute('aria-expanded', open);
    });

    // Cerrar al hacer clic en un enlace del menú
    nav.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', function () {
            nav.classList.remove('nav-open');
            btn.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
        });
    });

    // Cerrar al hacer clic fuera del menú
    document.addEventListener('click', function (e) {
        if (!btn.contains(e.target) && !nav.contains(e.target)) {
            nav.classList.remove('nav-open');
            btn.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
        }
    });
})();

// ---- Confirmación antes de eliminar ----
document.addEventListener('click', function (e) {
    if (e.target.matches('[data-confirm]')) {
        if (!confirm(e.target.dataset.confirm || '¿Estás seguro?')) {
            e.preventDefault();
        }
    }
});

// ---- Auto-ocultar alertas tras 4 segundos ----
document.querySelectorAll('.alert').forEach(function (el) {
    setTimeout(function () {
        el.style.transition = 'opacity .5s';
        el.style.opacity = '0';
        setTimeout(function () { el.remove(); }, 500);
    }, 4000);
});

// ---- Marcar enlace de nav activo ----
(function () {
    var path = window.location.pathname;
    document.querySelectorAll('.main-nav a').forEach(function (a) {
        if (a.getAttribute('href') && path.endsWith(a.getAttribute('href').split('/').pop())) {
            a.classList.add('active');
        }
    });
})();
