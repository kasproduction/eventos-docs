<?php
// Uso: include __DIR__ . '/_layout.php'; con $titulo y $seccion definidos
preg_match('#^(.*?/admin)#', $_SERVER['REQUEST_URI'], $_m);
$__base = $_m[1] ?? '/admin';
$__sec  = $seccion ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($titulo ?? 'Admin') ?> — Control de Eventos</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= htmlspecialchars($__base) ?>/assets/css/main.css">

<script>
  const ADMIN_BASE = <?= json_encode($__base) ?>;
  const API_BASE   = ADMIN_BASE + '/api';
</script>
<script src="<?= htmlspecialchars($__base) ?>/assets/js/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>

<!-- Overlay backdrop (tablet portrait / móvil) -->
<div class="sb-overlay-backdrop" id="sb-overlay-backdrop" onclick="closeSidebarOverlay()"></div>

<!-- Hamburger (solo visible ≤768px) -->
<button class="sb-hamburger" id="sb-hamburger" onclick="toggleSidebar()">
  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
    <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
  </svg>
</button>

<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <span class="brand-dot">CE</span>
    <span class="nav-label brand-name">Control Eventos</span>
  </div>

  <nav class="sidebar-nav">
    <span class="sidebar-section"><span class="nav-label">Principal</span></span>

    <a href="<?= $__base ?>/" data-label="Monitor"
       class="nav-item <?= $__sec === 'monitor' ? 'active' : '' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
        <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
      </svg>
      <span class="nav-label">Monitor</span>
    </a>

    <a href="<?= $__base ?>/dashboard" data-label="Dashboard"
       class="nav-item <?= $__sec === 'dashboard' ? 'active' : '' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
        <line x1="6" y1="20" x2="6" y2="14"/>
        <circle cx="18" cy="8" r="2"/><circle cx="12" cy="2" r="2"/><circle cx="6" cy="12" r="2"/>
      </svg>
      <span class="nav-label">Dashboard</span>
    </a>

    <?php if (!Auth::esViewer()): ?>
    <a href="<?= $__base ?>/agenda" data-label="Agenda"
       class="nav-item <?= $__sec === 'agenda' ? 'active' : '' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
      <span class="nav-label">Agenda</span>
    </a>

    <a href="<?= $__base ?>/asistentes" data-label="Asistentes"
       class="nav-item <?= $__sec === 'asistentes' ? 'active' : '' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
      </svg>
      <span class="nav-label">Asistentes</span>
    </a>
    <?php endif; ?>

    <span class="sidebar-section"><span class="nav-label">Gestión</span></span>

    <a href="<?= $__base ?>/reportes" data-label="Reportes"
       class="nav-item <?= $__sec === 'reportes' ? 'active' : '' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
        <line x1="6" y1="20" x2="6" y2="14"/>
      </svg>
      <span class="nav-label">Reportes</span>
    </a>

    <?php if (!Auth::esViewer()): ?>
    <a href="<?= $__base ?>/importar" data-label="Importar"
       class="nav-item <?= $__sec === 'importar' ? 'active' : '' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
      </svg>
      <span class="nav-label">Importar</span>
    </a>

    <a href="<?= $__base ?>/configuracion" data-label="Configuración"
       class="nav-item <?= $__sec === 'configuracion' ? 'active' : '' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="3"/>
        <path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41M12 2v2M12 20v2M2 12h2M20 12h2"/>
      </svg>
      <span class="nav-label">Configuración</span>
    </a>
    <?php endif; ?>

    <?php if (Auth::esAdmin()): ?>
    <a href="<?= $__base ?>/usuarios" data-label="Usuarios"
       class="nav-item <?= $__sec === 'usuarios' ? 'active' : '' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="9" cy="7" r="4"/>
        <path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/>
        <line x1="19" y1="8" x2="19" y2="14"/><line x1="16" y1="11" x2="22" y2="11"/>
      </svg>
      <span class="nav-label">Usuarios</span>
    </a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer">

    <!-- Usuario logueado -->
    <?php 
    $rol = $_SESSION['admin_rol'] ?? '';
    $rolBadge = match($rol) {
        'admin' => '<span class="badge" style="background:#3B82F6;color:#fff;padding:1px 6px;font-size:.55rem;">ADMIN</span>',
        'supervisor' => '<span class="badge badge-naranja" style="padding:1px 6px;font-size:.55rem;">SUPER</span>',
        default => '<span class="badge badge-gris" style="padding:1px 6px;font-size:.55rem;">VIEW</span>',
    };
    ?>
    <div class="sb-user" data-label="<?= htmlspecialchars($_SESSION['admin_nombre'] ?? '') ?>">
      <div class="sb-user-avatar"><?= htmlspecialchars(mb_substr($_SESSION['admin_nombre'] ?? 'U', 0, 1)) ?></div>
      <div class="sb-user-info nav-label">
        <div class="sb-user-nombre"><?= htmlspecialchars($_SESSION['admin_nombre'] ?? '') ?></div>
        <div class="sb-user-rol" style="display:flex;align-items:center;gap:.35rem;">
          <?= $rolBadge ?>
        </div>
      </div>
    </div>

    <!-- Mi cuenta -->
    <button onclick="abrirMiCuenta()" data-label="Mi cuenta"
      class="nav-item" style="width:100%; border:none; cursor:pointer; background:none; text-align:left;">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
        <circle cx="12" cy="7" r="4"/>
      </svg>
      <span class="nav-label">Mi cuenta</span>
    </button>

    <!-- Selector de tema de color -->
    <button onclick="abrirSelectorTema()" data-label="Tema de color"
      class="nav-item" style="width:100%; border:none; cursor:pointer; background:none; text-align:left;">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="13.5" cy="6.5" r="2.5"/><circle cx="19" cy="12" r="2.5"/>
        <circle cx="6" cy="12" r="2.5"/><circle cx="17" cy="18.5" r="2.5"/>
        <circle cx="8.5" cy="18.5" r="2.5"/>
      </svg>
      <span class="nav-label">Tema</span>
    </button>

    <a href="<?= $__base ?>/logout" data-label="Cerrar sesión"
       class="nav-item nav-item-logout">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
        <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
      <span class="nav-label">Cerrar sesión</span>
    </a>
  </div>
</aside>

<!-- Modal Mi Cuenta -->
<div class="modal-bg" id="modal-mi-cuenta">
  <div class="modal" style="max-width:380px;">
    <h3>Mi cuenta</h3>
    <label>PIN actual</label>
    <input type="password" id="mc-pin-actual" placeholder="••••" inputmode="numeric" maxlength="8"
           style="letter-spacing:.25em; text-align:center;">
    <label>Nuevo PIN</label>
    <input type="password" id="mc-pin-nuevo" placeholder="••••" inputmode="numeric" maxlength="8"
           style="letter-spacing:.25em; text-align:center;">
    <label>Confirmar nuevo PIN</label>
    <input type="password" id="mc-pin-confirmar" placeholder="••••" inputmode="numeric" maxlength="8"
           style="letter-spacing:.25em; text-align:center;">
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="cerrarMiCuenta()">Cancelar</button>
      <button class="btn btn-primary" onclick="guardarMiCuenta()">Cambiar PIN</button>
    </div>
  </div>
</div>

<!-- Modal Selector de Tema -->
<div class="modal-bg" id="modal-tema">
  <div class="modal">
    <h3>Tema de color</h3>
    <p style="color:var(--text-2);font-size:.85rem;margin-bottom:.5rem;">Elige un color principal para la interfaz</p>
    <div class="tema-grid" id="tema-grid"></div>
    <div class="modal-footer">
      <button class="btn btn-primary" onclick="cerrarSelectorTema()">Cerrar</button>
    </div>
  </div>
</div>

<!-- Confirm Dialog Global -->
<div class="modal-bg" id="confirm-dialog">
  <div class="modal">
    <h3 id="confirm-title">¿Estás seguro?</h3>
    <p class="confirm-msg" id="confirm-msg"></p>
    <div class="modal-footer">
      <button class="btn btn-ghost" id="confirm-cancel">Cancelar</button>
      <button class="btn btn-danger" id="confirm-ok">Confirmar</button>
    </div>
  </div>
</div>

<!-- Floating sidebar toggle — flota en el borde del sidebar -->
<button class="sb-toggle-btn" id="sb-toggle-btn" onclick="toggleSidebar()" title="Menú  (tecla [)">
  <svg id="sb-arrow" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
    <polyline points="15 18 9 12 15 6"/>
  </svg>
</button>

<!-- Toast container — fuera de main para no verse afectado por el scroll -->
<div id="toast-container"></div>

<script>
// ── SIDEBAR GSAP ──────────────────────────────────────
(function() {
  const W_OPEN = 220, W_MINI = 68;
  let sbOpen = localStorage.getItem('sb') !== '0';

  function isOverlay() { return window.innerWidth <= 768; }
  function isTablet()  { return window.innerWidth > 768 && window.innerWidth <= 1100; }

  function initSidebar() {
    const SB   = document.getElementById('sidebar');
    const MAIN = document.querySelector('main');
    const BTN  = document.getElementById('sb-toggle-btn');
    if (!SB || !MAIN) return;

    if (isOverlay()) {
      // Overlay: sidebar oculto via CSS transform, GSAP no interviene en width
      SB.classList.remove('collapsed');
      gsap.set(SB,   { clearProps: 'width' });
      gsap.set(MAIN, { marginLeft: 0 });
      gsap.set('.nav-label', { opacity: 1 });
      return;
    }

    if (isTablet()) {
      // Tablet landscape: auto-colapsa a mini
      SB.classList.add('collapsed');
      gsap.set(SB,           { width: W_MINI });
      gsap.set(MAIN,         { marginLeft: W_MINI });
      gsap.set('.nav-label', { opacity: 0 });
      gsap.set('#sb-arrow',  { rotation: 180 });
      if (BTN) gsap.set(BTN, { left: W_MINI - 13 });
      return;
    }

    // Desktop: comportamiento original
    if (!sbOpen) {
      SB.classList.add('collapsed');
      gsap.set(SB,           { width: W_MINI });
      gsap.set(MAIN,         { marginLeft: W_MINI });
      gsap.set('.nav-label', { opacity: 0 });
      gsap.set('#sb-arrow',  { rotation: 180 });
      if (BTN) gsap.set(BTN, { left: W_MINI - 13 });
    } else {
      if (BTN) gsap.set(BTN, { left: W_OPEN - 13 });
      gsap.from('.nav-item', {
        x: -14, opacity: 0,
        stagger: 0.045, duration: 0.4,
        ease: 'power2.out', delay: 0.05,
        clearProps: 'all'
      });
    }
  }

  window.toggleSidebar = function() {
    const SB       = document.getElementById('sidebar');
    const MAIN     = document.querySelector('main');
    const BTN      = document.getElementById('sb-toggle-btn');
    const BACKDROP = document.getElementById('sb-overlay-backdrop');

    if (isOverlay()) {
      const open = SB.classList.contains('overlay-open');
      SB.classList.toggle('overlay-open', !open);
      if (BACKDROP) BACKDROP.classList.toggle('open', !open);
      return;
    }

    // Desktop / tablet landscape: toggle mini ↔ full
    sbOpen = !sbOpen;
    localStorage.setItem('sb', sbOpen ? '1' : '0');

    if (sbOpen) {
      SB.classList.remove('collapsed');
      gsap.to(SB,           { width: W_OPEN, duration: 0.32, ease: 'power3.inOut' });
      gsap.to(MAIN,         { marginLeft: W_OPEN, duration: 0.32, ease: 'power3.inOut' });
      gsap.to('.nav-label', { opacity: 1, duration: 0.2, delay: 0.18, stagger: 0.025, ease: 'power2.out' });
      gsap.to('#sb-arrow',  { rotation: 0, duration: 0.3, ease: 'power2.inOut' });
      if (BTN) gsap.to(BTN, { left: W_OPEN - 13, duration: 0.32, ease: 'power3.inOut' });
    } else {
      gsap.to('.nav-label', {
        opacity: 0, duration: 0.12, stagger: 0.01, ease: 'power2.in',
        onComplete: () => SB.classList.add('collapsed')
      });
      gsap.to(SB,           { width: W_MINI, duration: 0.28, delay: 0.1, ease: 'power3.inOut' });
      gsap.to(MAIN,         { marginLeft: W_MINI, duration: 0.28, delay: 0.1, ease: 'power3.inOut' });
      gsap.to('#sb-arrow',  { rotation: 180, duration: 0.3, ease: 'power2.inOut' });
      if (BTN) gsap.to(BTN, { left: W_MINI - 13, duration: 0.28, delay: 0.1, ease: 'power3.inOut' });
    }
  };

  window.closeSidebarOverlay = function() {
    const SB       = document.getElementById('sidebar');
    const BACKDROP = document.getElementById('sb-overlay-backdrop');
    SB.classList.remove('overlay-open');
    if (BACKDROP) BACKDROP.classList.remove('open');
  };

  // Keyboard shortcut: tecla [
  document.addEventListener('keydown', function(e) {
    if (e.key === '[' && !['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) {
      toggleSidebar();
    }
  });

  // Re-init si cambia el tamaño de ventana (ej: rotar tablet)
  window.addEventListener('resize', function() {
    closeSidebarOverlay();
    initSidebar();
  });

  document.addEventListener('DOMContentLoaded', initSidebar);
})();

// ── TOAST ─────────────────────────────────────────────
window.showToast = function(msg, type) {
  type = type || 'ok';
  const icons = { ok: '✓', err: '✕', warn: '!' };
  const container = document.getElementById('toast-container');
  if (!container) return;

  const el = document.createElement('div');
  el.className = 'toast' + (type !== 'ok' ? ' toast-' + type : '');

  const closeId = 'tc-' + Date.now();
  el.innerHTML =
    '<span class="toast-icon">' + (icons[type] || '·') + '</span>' +
    '<span>' + String(msg).replace(/</g, '&lt;') + '</span>' +
    '<button class="toast-close" onclick="document.getElementById(\'' + closeId + '\') && gsap.to(document.getElementById(\'' + closeId + '\'),{opacity:0,x:12,duration:.18,onComplete:function(){document.getElementById(\'' + closeId + '\').remove()}})">✕</button>';
  el.id = closeId;

  gsap.set(el, { opacity: 0, x: 16, scale: 0.96 });
  container.appendChild(el);
  gsap.to(el, { opacity: 1, x: 0, scale: 1, duration: 0.28, ease: 'back.out(1.6)' });

  const dismiss = setTimeout(function() {
    if (!el.isConnected) return;
    gsap.to(el, { opacity: 0, x: 12, scale: 0.96, duration: 0.2, onComplete: function() { el.remove(); } });
  }, 3800);

  el.addEventListener('mouseenter', function() { clearTimeout(dismiss); });
  return el;
};

// Override window.alert → toast (elimina los alert() nativos)
window.alert = function(msg) {
  const m = String(msg);
  const type = /error|no se pued|inválid|falt/i.test(m) ? 'err'
             : /aviso|warn/i.test(m)                     ? 'warn'
             : 'ok';
  window.showToast(m, type);
};

// ── API ERROR HELPER ──────────────────────────────────
// Extrae el mensaje de error de cualquier respuesta del admin API
// Soporta { error: '...' } y el formato del tótem { mensaje: '...' }
window.apiErr = function(d, fallback) {
  return (d && (d.error || d.mensaje)) || fallback || 'Error desconocido';
};

// ── STAGGER HELPER ────────────────────────────────────
// Uso: staggerIn('.card') o staggerIn(nodeList)
window.staggerIn = function(selector, opts) {
  const els = typeof selector === 'string'
    ? document.querySelectorAll(selector)
    : selector;
  if (!els || !els.length) return;
  gsap.from(els, Object.assign({
    y: 18, opacity: 0,
    stagger: 0.07, duration: 0.42,
    ease: 'power2.out', clearProps: 'all'
  }, opts || {}));
};

// ── SMOOTH SCROLL HELPER ───────────────────────────────
// Uso: scrollToEl('#mi-seccion')
window.scrollToEl = function(target) {
  const el = typeof target === 'string' ? document.querySelector(target) : target;
  if (!el) return;
  const y = el.getBoundingClientRect().top + window.pageYOffset - 24;
  window.scrollTo({ top: y, behavior: 'smooth' });
};

// ── DARK MODE ──────────────────────────────────────────
(function() {
  function applyDark(dark) {
    document.body.classList.toggle('dark', dark);
    const label = document.getElementById('dm-label');
    const icon  = document.getElementById('dm-icon');
    if (label) label.textContent = dark ? 'Modo claro' : 'Modo oscuro';
    if (icon) {
      icon.innerHTML = dark
        ? '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>'
        : '<circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>';
    }
  }

  window.toggleDarkMode = function() {
    const dark = !document.body.classList.contains('dark');
    localStorage.setItem('dm', dark ? '1' : '0');
    applyDark(dark);
  };

  // Apply on load (before DOMContentLoaded to prevent flash)
  const savedDark = localStorage.getItem('dm') === '1';
  if (savedDark) document.documentElement.classList.add('dark-preload');

  document.addEventListener('DOMContentLoaded', function() {
    applyDark(localStorage.getItem('dm') === '1');
  });
})();

// ── SELECTOR DE TEMAS ─────────────────────────────────
(function() {
  const temas = [
    { id: 'blue',   nombre: 'Azul',   colores: ['#3B82F6','#1D4ED8','#93C5FD'] },
    { id: 'green',  nombre: 'Verde',  colores: ['#22C55E','#15803D','#86EFAC'] },
    { id: 'purple', nombre: 'Morado', colores: ['#A855F7','#7E22CE','#D8B4FE'] },
    { id: 'rose',   nombre: 'Rosa',   colores: ['#F43F5E','#BE123C','#FDA4AF'] },
    { id: 'orange', nombre: 'Naranja',colores: ['#F97316','#C2410C','#FDBA74'] },
    { id: 'cyan',   nombre: 'Cian',   colores: ['#06B6D4','#0E7490','#67E8F9'] },
  ];

  window.abrirSelectorTema = function() {
    const grid = document.getElementById('tema-grid');
    const actual = localStorage.getItem('tema') || 'blue';
    
    grid.innerHTML = temas.map(t => `
      <div class="tema-opcion ${t.id === actual ? 'seleccionado' : ''}" 
           onclick="seleccionarTema('${t.id}')">
        <div class="tema-colores">
          ${t.colores.map(c => `<div class="tema-color" style="background:${c}"></div>`).join('')}
        </div>
        <span>${t.nombre}</span>
      </div>
    `).join('');
    
    document.getElementById('modal-tema').classList.add('open');
  };

  window.seleccionarTema = function(id) {
    localStorage.setItem('tema', id);
    aplicarTema(id);
    
    // Actualizar visual
    document.querySelectorAll('.tema-opcion').forEach(el => {
      el.classList.toggle('seleccionado', el.onclick.toString().includes("'" + id + "'"));
    });
    // O simplemente recargar
    document.querySelectorAll('.tema-opcion').forEach(el => {
      el.classList.remove('seleccionado');
    });
    document.querySelector('.tema-opcion[onclick*="' + id + '"]')?.classList.add('seleccionado');
  };

  window.cerrarSelectorTema = function() {
    document.getElementById('modal-tema').classList.remove('open');
  };

  window.aplicarTema = function(id) {
    const root = document.documentElement;
    const tema = temas.find(t => t.id === id) || temas[0];
    const color = tema.colores[0];
    
    root.style.setProperty('--accent', color);
    // Badges en modo claro
    root.style.setProperty('--badge-azul', tema.colores[2]);
  };

  // Aplicar tema al cargar
  document.addEventListener('DOMContentLoaded', function() {
    aplicarTema(localStorage.getItem('tema') || 'blue');
  });
})();

// ── CONFIRM DIALOG ─────────────────────────────────────
// Uso: const ok = await confirmDialog('¿Eliminar este elemento?', { title: '¿Eliminar?', ok: 'Sí, eliminar' })
window.confirmDialog = function(msg, opts) {
  opts = opts || {};
  return new Promise(function(resolve) {
    const bg     = document.getElementById('confirm-dialog');
    const title  = document.getElementById('confirm-title');
    const msgEl  = document.getElementById('confirm-msg');
    const okBtn  = document.getElementById('confirm-ok');
    const canBtn = document.getElementById('confirm-cancel');
    if (!bg) { resolve(window._nativeConfirm ? window._nativeConfirm(msg) : true); return; }

    title.textContent  = opts.title  || '¿Estás seguro?';
    msgEl.textContent  = msg         || '';
    okBtn.textContent  = opts.ok     || 'Confirmar';
    canBtn.textContent = opts.cancel || 'Cancelar';
    okBtn.className    = 'btn ' + (opts.danger !== false ? 'btn-danger' : 'btn-primary');

    bg.classList.add('open');

    function cleanup() {
      bg.classList.remove('open');
      okBtn .removeEventListener('click',   onOk);
      canBtn.removeEventListener('click',   onCancel);
      bg    .removeEventListener('click',   onBg);
    }
    function onOk()    { cleanup(); resolve(true);  }
    function onCancel(){ cleanup(); resolve(false); }
    function onBg(e)   { if (e.target === bg) { cleanup(); resolve(false); } }

    okBtn .addEventListener('click', onOk);
    canBtn.addEventListener('click', onCancel);
    bg    .addEventListener('click', onBg);
  });
};

// ── MI CUENTA ──────────────────────────────────────────
window.abrirMiCuenta = function() {
  document.getElementById('mc-pin-actual').value    = '';
  document.getElementById('mc-pin-nuevo').value     = '';
  document.getElementById('mc-pin-confirmar').value = '';
  document.getElementById('modal-mi-cuenta').classList.add('open');
  setTimeout(() => document.getElementById('mc-pin-actual').focus(), 200);
};
window.cerrarMiCuenta = function() {
  document.getElementById('modal-mi-cuenta').classList.remove('open');
};
window.guardarMiCuenta = async function() {
  const actual     = document.getElementById('mc-pin-actual').value.trim();
  const nuevo      = document.getElementById('mc-pin-nuevo').value.trim();
  const confirmar  = document.getElementById('mc-pin-confirmar').value.trim();
  if (!actual)            { showToast('Ingresa tu PIN actual', 'err'); return; }
  if (nuevo.length < 4)   { showToast('El nuevo PIN debe tener al menos 4 dígitos', 'err'); return; }
  if (nuevo !== confirmar) { showToast('Los PINs no coinciden', 'err'); return; }
  const r = await fetch(API_BASE + '/mi-cuenta', {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ pin_actual: actual, pin_nuevo: nuevo })
  });
  const d = await r.json();
  if (!r.ok) { showToast(apiErr(d, 'Error al cambiar PIN'), 'err'); return; }
  showToast('PIN actualizado correctamente');
  cerrarMiCuenta();
};
document.addEventListener('DOMContentLoaded', function() {
  const bg = document.getElementById('modal-mi-cuenta');
  if (bg) bg.addEventListener('click', function(e) { if (e.target === bg) cerrarMiCuenta(); });
});

// ── SKELETON LOADER HELPER ─────────────────────────────
// Uso: showSkeletons('#container', 3)  |  showSkeletons('#container', 3, 'card')
window.showSkeletons = function(container, count, type) {
  const el = typeof container === 'string' ? document.querySelector(container) : container;
  if (!el) return;
  count = count || 4;
  type  = type  || 'lines';
  const widths = [100, 80, 90, 65, 75, 85, 55];
  if (type === 'lines') {
    el.innerHTML = Array.from({ length: count }, function(_, i) {
      const w = i === 0 ? 55 : widths[i % widths.length];
      const cls = i === 0 ? 'lg' : (i === count - 1 ? 'sm' : '');
      return '<div class="skeleton sk-line ' + cls + '" style="width:' + w + '%"></div>';
    }).join('');
  } else if (type === 'card') {
    el.innerHTML = Array.from({ length: count }, function() {
      return '<div class="card" style="gap:.6rem;display:flex;flex-direction:column;">' +
        '<div class="skeleton sk-line lg" style="width:55%"></div>' +
        '<div class="skeleton sk-line" style="width:80%;margin-top:.25rem"></div>' +
        '<div class="skeleton sk-line sm" style="margin-top:.5rem"></div>' +
        '</div>';
    }).join('');
  }
};
</script>

<main>

