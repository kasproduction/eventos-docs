<?php $titulo = 'Dashboard'; $seccion = 'dashboard'; include __DIR__ . '/_layout.php'; ?>

<style>
/* ── Dashboard ─────────────────────────────────────── */
.db-kpi-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-bottom: 1.4rem;
}
@media (max-width: 700px) {
  .db-kpi-grid { grid-template-columns: 1fr; }
}

.db-kpi {
  background: var(--surface);
  border-radius: var(--radius-card);
  padding: 1.4rem 1.6rem;
  box-shadow: var(--shadow-card);
  display: flex;
  flex-direction: column;
  gap: .3rem;
}
.db-kpi-num {
  font-size: 3rem;
  font-weight: 900;
  color: var(--text-1);
  line-height: 1;
  letter-spacing: -.03em;
}
.db-kpi-num.accent { color: #16a34a; }
.db-kpi-label {
  font-size: .72rem;
  font-weight: 700;
  color: var(--text-3);
  text-transform: uppercase;
  letter-spacing: .08em;
}

/* ── Salones grid ──────────────────────────────────── */
.db-salon-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 1rem;
  margin-bottom: 1.4rem;
}
.db-salon {
  background: var(--surface);
  border-radius: var(--radius-card);
  padding: 1.4rem 1.5rem;
  box-shadow: var(--shadow-card);
}
.db-salon-nombre {
  font-size: .68rem;
  font-weight: 700;
  color: var(--text-3);
  text-transform: uppercase;
  letter-spacing: .08em;
  margin-bottom: .6rem;
}
.db-salon-num {
  font-size: 2.6rem;
  font-weight: 900;
  color: var(--text-1);
  line-height: 1;
  letter-spacing: -.03em;
}
.db-salon-cap {
  font-size: .82rem;
  color: var(--text-3);
  margin-top: .2rem;
}

/* Barra de capacidad */
.db-bar-wrap {
  height: 6px;
  background: rgba(0,0,0,.07);
  border-radius: 99px;
  margin: .9rem 0 .8rem;
  overflow: hidden;
}
body.dark .db-bar-wrap { background: rgba(255,255,255,.08); }
.db-bar {
  height: 100%;
  border-radius: 99px;
  transition: width .6s ease;
}
.db-bar.ok      { background: #22c55e; }
.db-bar.warn    { background: #f59e0b; }
.db-bar.full    { background: #ef4444; }
.db-bar.no-cap  { background: var(--accent); width: 100%; }

/* Charla dentro del salón */
.db-charla {
  padding: .7rem .85rem;
  background: var(--surface-2);
  border-radius: var(--radius-sm);
  border-left: 3px solid var(--accent);
  margin-top: .5rem;
}
.db-charla-label {
  font-size: .58rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .1em;
  color: var(--accent);
  display: flex;
  align-items: center;
  gap: .35rem;
  margin-bottom: .3rem;
}
.db-charla-titulo {
  font-size: .88rem;
  font-weight: 700;
  color: var(--text-1);
}
.db-charla-meta {
  font-size: .74rem;
  color: var(--text-3);
  margin-top: .2rem;
}
.db-sin-charla {
  font-size: .82rem;
  color: var(--text-3);
  margin-top: .6rem;
}

/* ── Feed lecturas ─────────────────────────────────── */
.db-feed {
  background: var(--surface);
  border-radius: var(--radius-card);
  padding: 1.4rem 1.5rem;
  box-shadow: var(--shadow-card);
  overflow: hidden;
}
.db-feed-titulo {
  font-size: .68rem;
  font-weight: 700;
  color: var(--text-3);
  text-transform: uppercase;
  letter-spacing: .08em;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.db-feed-row {
  display: flex;
  align-items: center;
  gap: .75rem;
  padding: .5rem 0;
  border-bottom: 1px solid rgba(0,0,0,.04);
  font-size: .875rem;
}
body.dark .db-feed-row { border-bottom-color: rgba(255,255,255,.04); }
.db-feed-row:last-child { border-bottom: none; }
.db-feed-hora  { font-family: monospace; font-size: .78rem; color: var(--text-3); min-width: 52px; }
.db-feed-nombre { flex: 1; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.db-feed-salon { font-size: .78rem; color: var(--text-2); min-width: 80px; text-align: right; }

/* ── Header barra ──────────────────────────────────── */
.db-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.4rem;
  flex-wrap: wrap;
  gap: .8rem;
}
.db-header-izq {
  display: flex;
  align-items: center;
  gap: 1rem;
}
.db-refresh-info {
  font-size: .78rem;
  color: var(--text-3);
  display: flex;
  align-items: center;
  gap: .5rem;
}

/* ── Fullscreen ────────────────────────────────────── */
#btn-fullscreen {
  background: rgba(0,0,0,.06);
  border: none;
  border-radius: 8px;
  width: 34px; height: 34px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  color: var(--text-2);
  transition: background .15s, color .15s;
}
#btn-fullscreen:hover { background: var(--dark); color: #fff; }
body.dark #btn-fullscreen { background: rgba(255,255,255,.08); }
body.dark #btn-fullscreen:hover { background: var(--accent); color: #1a1a1a; }
</style>

<div class="db-header">
  <div class="db-header-izq">
    <h1 style="font-size:1.15rem; font-weight:800; color:var(--text-1); letter-spacing:-.01em;">
      Dashboard del evento
    </h1>
    <div class="db-refresh-info">
      <span class="spin" id="db-spinner" style="display:none;"></span>
      <span id="db-ts">—</span>
    </div>
  </div>
  <button id="btn-fullscreen" title="Pantalla completa" onclick="toggleFullscreen()">
    <svg id="fs-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>
    </svg>
  </button>
</div>

<!-- KPIs -->
<div class="db-kpi-grid" id="db-kpis">
  <div class="db-kpi">
    <div class="db-kpi-num accent" id="kpi-dentro">—</div>
    <div class="db-kpi-label">Personas dentro ahora</div>
  </div>
  <div class="db-kpi">
    <div class="db-kpi-num" id="kpi-hoy">—</div>
    <div class="db-kpi-label">Accesos registrados hoy</div>
  </div>
  <div class="db-kpi">
    <div class="db-kpi-num" id="kpi-total">—</div>
    <div class="db-kpi-label">Total registrados en el evento</div>
  </div>
</div>

<!-- Gráficos -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:1rem; margin-bottom:1.4rem;">
  <div class="card" style="padding:1.2rem;">
    <h2 style="font-size:.68rem; font-weight:700; color:var(--text-3); text-transform:uppercase; letter-spacing:.08em; margin-bottom:1rem;">
      Check-ins por hora (hoy)
    </h2>
    <div style="height:180px; position:relative;">
      <canvas id="chart-hora"></canvas>
    </div>
  </div>
  <div class="card" style="padding:1.2rem;">
    <h2 style="font-size:.68rem; font-weight:700; color:var(--text-3); text-transform:uppercase; letter-spacing:.08em; margin-bottom:1rem;">
      Distribución por salón
    </h2>
    <div style="height:180px; position:relative;">
      <canvas id="chart-salon"></canvas>
    </div>
  </div>
</div>

<!-- Salones -->
<div class="db-salon-grid" id="db-salones"></div>

<!-- Feed -->
<div class="db-feed">
  <div class="db-feed-titulo">
    <span>Últimos accesos</span>
    <span id="db-feed-count" style="font-size:.72rem; color:var(--text-3);"></span>
  </div>
  <div id="db-feed-lista"></div>
</div>

<!-- Refresh bar -->
<div class="refresh-bar-wrap" style="margin: 1rem 0 0; border-radius: var(--radius-sm);">
  <div class="refresh-bar" id="db-refresh-bar"></div>
</div>

<script>
const REFRESH_MS = 10000;
let dbBarTween   = null;
let firstLoad    = true;
let chartHora    = null;
let chartSalon   = null;

function getChartColors() {
  const isDark = document.body.classList.contains('dark');
  return {
    primary: isDark ? '#60A5FA' : '#3B82F6',
    text: isDark ? '#94A3B8' : '#64748B',
    grid: isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)',
  };
}

function initCharts() {
  const colors = getChartColors();
  
  // Chart: Check-ins por hora
  const ctxHora = document.getElementById('chart-hora').getContext('2d');
  chartHora = new Chart(ctxHora, {
    type: 'line',
    data: {
      labels: [],
      datasets: [{
        label: 'Check-ins',
        data: [],
        borderColor: colors.primary,
        backgroundColor: colors.primary + '20',
        fill: true,
        tension: 0.4,
        pointRadius: 3,
        pointHoverRadius: 5,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: colors.grid }, ticks: { color: colors.text, maxRotation: 0 } },
        y: { grid: { color: colors.grid }, ticks: { color: colors.text }, beginAtZero: true }
      }
    }
  });

  // Chart: Distribución por salón
  const ctxSalon = document.getElementById('chart-salon').getContext('2d');
  const palette = ['#3B82F6','#22C55E','#F59E0B','#EF4444','#8B5CF6','#EC4899','#06B6D4','#84CC16'];
  chartSalon = new Chart(ctxSalon, {
    type: 'doughnut',
    data: {
      labels: [],
      datasets: [{
        data: [],
        backgroundColor: palette,
        borderWidth: 0,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { 
        legend: { position: 'right', labels: { color: colors.text, padding: 12, usePointStyle: true } }
      }
    }
  });
}

function updateCharts(d) {
  const colors = getChartColors();
  
  // Hora
  if (chartHora) {
    chartHora.data.labels = d.grafico_hora.labels;
    chartHora.data.datasets[0].data = d.grafico_hora.data;
    chartHora.options.scales.x.grid.color = colors.grid;
    chartHora.options.scales.x.ticks.color = colors.text;
    chartHora.options.scales.y.grid.color = colors.grid;
    chartHora.options.scales.y.ticks.color = colors.text;
    chartHora.update('none');
  }
  
  // Salones
  if (chartSalon) {
    chartSalon.data.labels = d.grafico_salones.map(s => s.nombre);
    chartSalon.data.datasets[0].data = d.grafico_salones.map(s => parseInt(s.dentro));
    chartSalon.update('none');
  }
}

function startBar() {
  const bar = document.getElementById('db-refresh-bar');
  if (!bar) return;
  if (dbBarTween) dbBarTween.kill();
  gsap.set(bar, { scaleX: 1, transformOrigin: 'left' });
  dbBarTween = gsap.to(bar, {
    scaleX: 0, duration: REFRESH_MS / 1000,
    ease: 'none', transformOrigin: 'left'
  });
}

async function cargar() {
  document.getElementById('db-spinner').style.display = '';
  try {
    const r = await fetch(API_BASE + '/dashboard');
    const d = await r.json();

    // KPIs
    animNum('kpi-dentro', d.total_dentro);
    animNum('kpi-hoy',    d.checkins_hoy);
    animNum('kpi-total',  d.total_asistentes);
    document.getElementById('db-ts').textContent = 'Act. ' + d.timestamp.split(' ')[1];

    // Salones
    renderSalones(d.salones);

    // Feed
    renderFeed(d.lecturas);

    // Gráficos
    updateCharts(d);

    startBar();
    firstLoad = false;
  } catch(e) {
    document.getElementById('db-ts').textContent = 'Error de conexión';
  }
  document.getElementById('db-spinner').style.display = 'none';
}

function animNum(id, val) {
  const el = document.getElementById(id);
  if (!el) return;
  const from = parseInt(el.dataset.val || '0');
  el.dataset.val = val;
  if (firstLoad) { el.textContent = val; return; }
  gsap.fromTo({ n: from }, { n: val }, {
    duration: .6, ease: 'power2.out',
    onUpdate: function() { el.textContent = Math.round(this.targets()[0].n); }
  });
}

function barClass(dentro, cap) {
  if (!cap) return 'no-cap';
  const pct = dentro / cap;
  if (pct >= 1)   return 'full';
  if (pct >= .8)  return 'warn';
  return 'ok';
}

function renderSalones(salones) {
  const grid = document.getElementById('db-salones');
  grid.innerHTML = salones.map(s => {
    const cap    = s.capacidad ? parseInt(s.capacidad) : null;
    const dentro = parseInt(s.personas_dentro);
    const pct    = cap ? Math.min(100, Math.round(dentro / cap * 100)) : null;
    const cls    = barClass(dentro, cap);

    return `
      <div class="db-salon">
        <div class="db-salon-nombre">${esc(s.nombre)}</div>
        <div class="db-salon-num">${dentro}</div>
        <div class="db-salon-cap">${cap ? `de ${cap} · ${pct}% ocupación` : 'sin límite de aforo'}</div>
        <div class="db-bar-wrap">
          <div class="db-bar ${cls}" style="width:${pct !== null ? pct : 100}%"></div>
        </div>
        ${s.charla_titulo ? `
          <div class="db-charla">
            <div class="db-charla-label">
              <span class="pulse-dot"></span> EN CURSO
            </div>
            <div class="db-charla-titulo">${esc(s.charla_titulo)}</div>
            <div class="db-charla-meta">
              ${s.charla_inicio} – ${s.charla_fin}
              &nbsp;·&nbsp;
              <span style="color:#22c55e; font-weight:600">${s.minutos_restantes} min restantes</span>
            </div>
          </div>
        ` : `<div class="db-sin-charla">Sin charla activa</div>`}
      </div>`;
  }).join('');

  if (firstLoad) staggerIn('#db-salones .db-salon', { duration: .35, stagger: .07 });
}

function renderFeed(lecturas) {
  document.getElementById('db-feed-count').textContent = lecturas.length + ' más recientes';
  document.getElementById('db-feed-lista').innerHTML = lecturas.map(l => `
    <div class="db-feed-row">
      <span class="db-feed-hora">${l.hora}</span>
      <span class="badge ${l.tipo === 'checkin' ? 'badge-verde' : 'badge-rojo'}" style="flex-shrink:0">${l.tipo}</span>
      <span class="db-feed-nombre">${esc(l.nombre)}</span>
      <span class="db-feed-salon">${esc(l.salon)}</span>
    </div>
  `).join('') || '<div style="color:var(--text-3);font-size:.85rem;padding:.5rem 0">Sin movimientos hoy</div>';
}

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
  } else {
    document.exitFullscreen();
  }
}

document.addEventListener('fullscreenchange', function() {
  const icon = document.getElementById('fs-icon');
  if (!icon) return;
  if (document.fullscreenElement) {
    icon.innerHTML = '<path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"/>';
  } else {
    icon.innerHTML = '<path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>';
  }
});

function esc(s) { const d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }

// Inicializar gráficos
initCharts();

// Actualizar gráficos al cambiar modo oscuro
window.addEventListener('storage', (e) => {
  if (e.key === 'dm') {
    location.reload();
  }
});

cargar();
setInterval(cargar, REFRESH_MS);
</script>

</main></body></html>
