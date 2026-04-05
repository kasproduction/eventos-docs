<?php
// Dashboard V2 — Tailwind + DaisyUI (tema night)
// Copia independiente para comparar con dashboard.php original
// Auth ya manejada por el router (admin/index.php)
preg_match('#^(.*?/admin)#', $_SERVER['REQUEST_URI'], $_m);
$__base = $_m[1] ?? '/admin';
?>
<!DOCTYPE html>
<html lang="es" id="html-root" data-theme="nord">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard V2 — Control de Eventos</title>
<link rel="stylesheet" href="<?= $__base ?>/assets/css/app.css">
</head>
<body class="bg-base-200 min-h-screen flex">

<!-- ── Sidebar ──────────────────────────────────────── -->
<aside class="w-64 min-h-screen bg-base-100 flex flex-col shrink-0 border-r border-base-300">
  <div class="px-6 py-5 border-b border-base-300">
    <span class="text-xs font-bold uppercase tracking-widest text-primary">Control</span>
    <div class="text-lg font-black text-base-content mt-0.5">Eventos</div>
  </div>

  <nav class="flex-1 px-3 py-4 flex flex-col gap-1">
    <a href="<?= $__base ?>/dashboard_v2" class="btn btn-ghost btn-sm justify-start gap-3 bg-primary/10 text-primary font-semibold">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Dashboard
    </a>
    <a href="<?= $__base ?>/monitor" class="btn btn-ghost btn-sm justify-start gap-3 text-base-content/70">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      Monitor
    </a>
    <a href="<?= $__base ?>/agenda" class="btn btn-ghost btn-sm justify-start gap-3 text-base-content/70">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      Agenda
    </a>
    <a href="<?= $__base ?>/asistentes" class="btn btn-ghost btn-sm justify-start gap-3 text-base-content/70">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      Asistentes
    </a>
    <a href="<?= $__base ?>/reportes" class="btn btn-ghost btn-sm justify-start gap-3 text-base-content/70">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      Reportes
    </a>
    <div class="divider my-1 text-xs text-base-content/30">Config</div>
    <a href="<?= $__base ?>/configuracion" class="btn btn-ghost btn-sm justify-start gap-3 text-base-content/70">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 0-14.14 0M4.93 19.07a10 10 0 0 0 14.14 0"/></svg>
      Configuración
    </a>
  </nav>

  <div class="px-4 py-4 border-t border-base-300">
    <div class="flex items-center gap-2">
      <div class="avatar placeholder">
        <div class="bg-neutral text-neutral-content rounded-full w-8">
          <span class="text-xs"><?= strtoupper(substr($_SESSION['admin']['nombre'] ?? 'A', 0, 1)) ?></span>
        </div>
      </div>
      <div class="flex-1 min-w-0">
        <div class="text-xs font-semibold truncate"><?= htmlspecialchars($_SESSION['admin']['nombre'] ?? 'Admin') ?></div>
        <a href="<?= $__base ?>/?logout=1" class="text-xs text-error hover:underline">Cerrar sesión</a>
      </div>
    </div>
  </div>
</aside>

<!-- ── Main ──────────────────────────────────────────── -->
<main class="flex-1 p-6 overflow-y-auto">

  <!-- Header -->
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-xl font-black text-base-content">Dashboard del evento</h1>
      <p class="text-xs text-base-content/40 mt-0.5 flex items-center gap-2">
        <span id="db-spinner" class="loading loading-spinner loading-xs hidden"></span>
        <span id="db-ts">Cargando...</span>
      </p>
    </div>
    <div class="flex items-center gap-2">
      <div class="join">
        <button onclick="setTheme('nord')"     class="join-item btn btn-xs" id="t-nord">Nord</button>
        <button onclick="setTheme('business')" class="join-item btn btn-xs" id="t-business">Business</button>
        <button onclick="setTheme('night')"    class="join-item btn btn-xs" id="t-night">Night</button>
      </div>
      <a href="<?= $__base ?>/dashboard" class="btn btn-ghost btn-xs">← Original</a>
      <button class="btn btn-ghost btn-square btn-sm" title="Pantalla completa" onclick="toggleFullscreen()">
        <svg id="fs-icon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- KPIs -->
  <div class="stats stats-horizontal shadow w-full mb-6 bg-base-100" id="db-kpis">
    <div class="stat">
      <div class="stat-title">Personas dentro ahora</div>
      <div class="stat-value text-success" id="kpi-dentro">—</div>
      <div class="stat-desc">en tiempo real</div>
    </div>
    <div class="stat">
      <div class="stat-title">Accesos hoy</div>
      <div class="stat-value" id="kpi-hoy">—</div>
      <div class="stat-desc">checkins registrados</div>
    </div>
    <div class="stat">
      <div class="stat-title">Total registrados</div>
      <div class="stat-value" id="kpi-total">—</div>
      <div class="stat-desc">en el evento</div>
    </div>
  </div>

  <!-- Salones -->
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6" id="db-salones"></div>

  <!-- Feed -->
  <div class="card bg-base-100 shadow">
    <div class="card-body p-5">
      <div class="flex items-center justify-between mb-2">
        <h2 class="card-title text-sm">Últimos accesos</h2>
        <span class="text-xs text-base-content/40" id="db-feed-count"></span>
      </div>
      <div id="db-feed-lista" class="divide-y divide-base-200"></div>
    </div>
  </div>

  <!-- Refresh bar -->
  <div class="w-full h-1 bg-base-300 rounded-full mt-4 overflow-hidden">
    <div class="h-full bg-primary rounded-full transition-none" id="db-refresh-bar" style="width:100%"></div>
  </div>

</main>

<script>
const API_BASE = '<?= $__base ?>/api';
const REFRESH_MS = 10000;
let firstLoad = true;
let barInterval = null;

function startBar() {
  const bar = document.getElementById('db-refresh-bar');
  if (!bar) return;
  bar.style.transition = 'none';
  bar.style.width = '100%';
  requestAnimationFrame(() => {
    bar.style.transition = `width ${REFRESH_MS}ms linear`;
    bar.style.width = '0%';
  });
}

async function cargar() {
  document.getElementById('db-spinner').classList.remove('hidden');
  try {
    const r = await fetch(API_BASE + '/dashboard');
    const d = await r.json();

    animNum('kpi-dentro', d.total_dentro);
    animNum('kpi-hoy',    d.checkins_hoy);
    animNum('kpi-total',  d.total_asistentes);
    document.getElementById('db-ts').textContent = 'Actualizado ' + d.timestamp.split(' ')[1];

    renderSalones(d.salones);
    renderFeed(d.lecturas);
    startBar();
    firstLoad = false;
  } catch(e) {
    document.getElementById('db-ts').textContent = 'Error de conexión';
  }
  document.getElementById('db-spinner').classList.add('hidden');
}

function animNum(id, val) {
  const el = document.getElementById(id);
  if (!el) return;
  if (firstLoad) { el.textContent = val; el.dataset.val = val; return; }
  const from = parseInt(el.dataset.val || '0');
  el.dataset.val = val;
  const start = performance.now();
  const dur = 600;
  function step(now) {
    const p = Math.min((now - start) / dur, 1);
    el.textContent = Math.round(from + (val - from) * p);
    if (p < 1) requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
}

function barClass(dentro, cap) {
  if (!cap) return 'progress-warning';
  const pct = dentro / cap;
  if (pct >= 1)  return 'progress-error';
  if (pct >= .8) return 'progress-warning';
  return 'progress-success';
}

function renderSalones(salones) {
  document.getElementById('db-salones').innerHTML = salones.map(s => {
    const cap    = s.capacidad ? parseInt(s.capacidad) : null;
    const dentro = parseInt(s.personas_dentro);
    const pct    = cap ? Math.min(100, Math.round(dentro / cap * 100)) : null;
    const cls    = barClass(dentro, cap);

    return `
      <div class="card bg-base-100 shadow">
        <div class="card-body p-5">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-xs font-bold uppercase tracking-widest text-base-content/40">${esc(s.nombre)}</p>
              <p class="text-4xl font-black text-base-content mt-1">${dentro}</p>
              <p class="text-xs text-base-content/40 mt-0.5">
                ${cap ? `de ${cap} · ${pct}% ocupación` : 'sin límite de aforo'}
              </p>
            </div>
            <div class="badge ${pct !== null && pct >= 100 ? 'badge-error' : pct !== null && pct >= 80 ? 'badge-warning' : 'badge-success'} badge-outline">
              ${pct !== null ? pct + '%' : '∞'}
            </div>
          </div>

          <progress class="progress ${cls} w-full mt-3" value="${pct !== null ? pct : 100}" max="100"></progress>

          ${s.charla_titulo ? `
            <div class="mt-3 rounded-lg bg-base-200 border-l-4 border-primary p-3">
              <p class="text-xs font-bold text-primary uppercase tracking-widest flex items-center gap-1.5 mb-1">
                <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse-dot inline-block"></span>
                EN CURSO
              </p>
              <p class="text-sm font-semibold">${esc(s.charla_titulo)}</p>
              <p class="text-xs text-base-content/40 mt-0.5">
                ${s.charla_inicio} – ${s.charla_fin}
                &nbsp;·&nbsp;
                <span class="text-success font-semibold">${s.minutos_restantes} min restantes</span>
              </p>
            </div>
          ` : `<p class="text-xs text-base-content/30 mt-3">Sin charla activa</p>`}
        </div>
      </div>`;
  }).join('');
}

function renderFeed(lecturas) {
  document.getElementById('db-feed-count').textContent = lecturas.length + ' más recientes';
  document.getElementById('db-feed-lista').innerHTML = lecturas.map(l => `
    <div class="flex items-center gap-3 py-2.5 text-sm">
      <span class="font-mono text-xs text-base-content/40 w-14 shrink-0">${l.hora}</span>
      <span class="badge badge-xs ${l.tipo === 'checkin' ? 'badge-success' : 'badge-error'} shrink-0">${l.tipo}</span>
      <span class="flex-1 font-medium truncate">${esc(l.nombre)}</span>
      <span class="text-xs text-base-content/40 shrink-0">${esc(l.salon)}</span>
    </div>
  `).join('') || '<p class="text-sm text-base-content/30 py-2">Sin movimientos hoy</p>';
}

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
  } else {
    document.exitFullscreen();
  }
}

function esc(s) { const d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }

function setTheme(theme) {
  document.getElementById('html-root').setAttribute('data-theme', theme);
  localStorage.setItem('admin-theme', theme);
  ['nord','business','night'].forEach(t => {
    document.getElementById('t-' + t).classList.toggle('btn-active', t === theme);
  });
}

// Restaurar tema guardado
const savedTheme = localStorage.getItem('admin-theme') || 'nord';
setTheme(savedTheme);

cargar();
setInterval(cargar, REFRESH_MS);
</script>

</body>
</html>
