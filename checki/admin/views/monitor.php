<?php $titulo = 'Monitor'; $seccion = 'monitor'; include __DIR__ . '/_layout.php'; ?>

<style>
/* ── Monitor — estilos específicos ── */
.salon-charla-activa {
  margin-top: 1rem;
  padding: .85rem 1rem;
  background: var(--surface-2);
  border-radius: var(--radius-sm);
  border-left: 3px solid var(--accent);
}
.salon-en-curso {
  font-size: .62rem; font-weight: 800;
  letter-spacing: .1em; color: var(--accent);
  margin-bottom: .25rem;
  display: flex; align-items: center; gap: .4rem;
}
.salon-sin-charla {
  margin-top: 1rem; font-size: .85rem; color: var(--text-3);
}

/* Tótem cards */
.totem-card {
  padding: 1rem;
  background: #131318;
  border-radius: var(--radius-sm);
  border: 1px solid rgba(255,255,255,.06);
  transition: transform .2s ease, border-color .2s ease;
  cursor: default;
}
.totem-card:hover { transform: translateY(-2px); border-color: rgba(255,255,255,.12); }
.totem-card.is-online { border-color: rgba(74,222,128,.22); }
.totem-salon   { font-size: .7rem; color: rgba(255,255,255,.32); font-weight: 500; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.totem-nombre  { font-size: .9rem; font-weight: 700; color: rgba(255,255,255,.88); margin: .45rem 0 .3rem; }
.totem-meta    { font-size: .73rem; color: rgba(255,255,255,.28); }
.totem-ip      { font-size: .7rem; color: rgba(255,255,255,.2); margin-top: .2rem; font-family: monospace; }
.totem-act     { font-size: .68rem; color: rgba(255,255,255,.2); margin-top: .35rem; }
.totem-card.is-online .totem-act { color: rgba(74,222,128,.45); }

/* Lecturas hora col */
.hora-col { font-family: monospace; font-size: .8rem; color: var(--text-2); }

/* Paginación lecturas */
.pag-bar {
  display: flex; align-items: center; justify-content: space-between;
  margin-top: 1rem; padding-top: .9rem;
  border-top: 1px solid rgba(0,0,0,.06);
  gap: .6rem;
}
body.dark .pag-bar { border-top-color: rgba(255,255,255,.06); }
.pag-info { font-size: .78rem; color: var(--text-3); }
.pag-btns { display: flex; gap: .4rem; align-items: center; }
</style>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.4rem;">
  <h1 style="font-size:1.15rem; font-weight:800; color:var(--text-1); letter-spacing:-.01em;">Monitor en tiempo real</h1>
  <div style="display:flex; align-items:center; gap:.8rem;">
    <span class="spin" id="spinner"></span>
    <span id="ultima-act" style="font-size:.78rem; color:var(--text-3);">—</span>
  </div>
</div>

<!-- SALONES -->
<div class="grid-2" id="salones-grid" style="margin-bottom:1.5rem;"></div>

<!-- ÚLTIMAS LECTURAS -->
<div class="card" style="margin-bottom:1rem;">
  <h2>Últimas lecturas</h2>
  <table>
    <thead>
      <tr><th>Hora</th><th>Nombre</th><th>Tipo</th><th>Salón</th><th>Tótem</th><th>Flags</th></tr>
    </thead>
    <tbody id="lecturas-body"></tbody>
  </table>
  <div class="pag-bar">
    <span class="pag-info" id="pag-info">—</span>
    <div class="pag-btns">
      <button class="btn btn-ghost btn-sm" id="pag-prev" onclick="cambiarPagina(-1)" disabled>← Anterior</button>
      <button class="btn btn-ghost btn-sm" id="pag-next" onclick="cambiarPagina(+1)" disabled>Siguiente →</button>
    </div>
  </div>
</div>

<!-- TÓTEMS + refresh bar -->
<div class="card" style="overflow:hidden; padding-bottom:0;">
  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; padding-bottom:0;">
    <h2 style="margin-bottom:0;">Estado tótems</h2>
    <span style="font-size:.72rem; color:var(--text-3);" id="refresh-label">actualizando...</span>
  </div>
  <div class="grid-3" id="totems-grid"></div>
  <div class="refresh-bar-wrap">
    <div class="refresh-bar" id="refresh-bar"></div>
  </div>
</div>

<script>
const REFRESH_MS = 4000;
let firstLoad    = true;
let barTween     = null;
let paginaActual = 1;
let totalPaginas = 1;

// ── Refresh bar countdown ────────────────────────────
function startRefreshBar() {
  const bar = document.getElementById('refresh-bar');
  if (!bar) return;
  if (barTween) barTween.kill();
  gsap.set(bar, { scaleX: 1, transformOrigin: 'left' });
  barTween = gsap.to(bar, {
    scaleX: 0, duration: REFRESH_MS / 1000,
    ease: 'none', transformOrigin: 'left'
  });
}

// ── Fetch & render ────────────────────────────────────
async function cargar(pagina) {
  pagina = pagina || paginaActual;
  try {
    const r = await fetch(API_BASE + '/monitor?page=' + pagina);
    const d = await r.json();
    renderSalones(d.salones);
    renderLecturas(d.lecturas, d.lecturas_page, d.lecturas_pages, d.lecturas_total);
    renderTotems(d.totems);
    document.getElementById('ultima-act').textContent    = 'Act. ' + d.timestamp.split(' ')[1];
    document.getElementById('refresh-label').textContent = 'auto-refresh ' + (REFRESH_MS/1000) + 's';
    startRefreshBar();
    if (firstLoad) { firstLoad = false; }
  } catch(e) {
    document.getElementById('ultima-act').textContent = 'Error de conexión';
  }
}

function cambiarPagina(delta) {
  const nueva = paginaActual + delta;
  if (nueva < 1 || nueva > totalPaginas) return;
  paginaActual = nueva;
  cargar(paginaActual);
}

// ── Salones ────────────────────────────────────────────
function renderSalones(salones) {
  const grid = document.getElementById('salones-grid');
  grid.innerHTML = salones.map(s => `
    <div class="card">
      <h2>${esc(s.nombre)}</h2>
      <div class="big-number">${s.personas_dentro}</div>
      <div class="sub-label">personas dentro</div>
      ${s.charla_titulo ? `
        <div class="salon-charla-activa">
          <div class="salon-en-curso">
            <span class="pulse-dot"></span> EN CURSO
          </div>
          <div style="font-weight:700; font-size:.9rem; color:var(--text-1)">${esc(s.charla_titulo)}</div>
          ${s.charla_ponente ? `<div style="font-size:.8rem;color:var(--text-2);margin-top:.15rem">${esc(s.charla_ponente)}</div>` : ''}
          <div style="font-size:.76rem;color:var(--text-3);margin-top:.35rem">
            ${s.charla_inicio} – ${s.charla_fin}
            &nbsp;·&nbsp;
            <span style="color:#22c55e;font-weight:600">${s.minutos_restantes} min restantes</span>
          </div>
        </div>
      ` : `<div class="salon-sin-charla">Sin charla activa</div>`}
    </div>
  `).join('');
  if (firstLoad) staggerIn('#salones-grid .card', { duration: 0.35, stagger: 0.07 });
}

// ── Lecturas ───────────────────────────────────────────
function renderLecturas(lecturas, page, pages, total) {
  // Si estamos en página 1 y llega el auto-refresh, actualizamos
  // Si estamos en otra página, no pisamos (el usuario está navegando)
  if (page !== undefined) {
    paginaActual = page;
    totalPaginas = pages;
  }

  document.getElementById('lecturas-body').innerHTML = lecturas.map(l => `
    <tr>
      <td class="hora-col">${l.hora}</td>
      <td style="font-weight:500">${esc(l.nombre)}</td>
      <td><span class="badge ${l.tipo === 'checkin' ? 'badge-verde' : 'badge-rojo'}">${l.tipo}</span></td>
      <td>${esc(l.salon)}</td>
      <td style="font-size:.78rem;color:var(--text-2)">${esc(l.totem || '—')}</td>
      <td style="font-size:.73rem;color:var(--text-3)">${l.flags || ''}</td>
    </tr>
  `).join('');

  // Paginación
  const info = document.getElementById('pag-info');
  const prev = document.getElementById('pag-prev');
  const next = document.getElementById('pag-next');
  if (info && pages !== undefined) {
    const desde = total === 0 ? 0 : (paginaActual - 1) * 30 + 1;
    const hasta = Math.min(paginaActual * 30, total);
    info.textContent = total === 0
      ? 'Sin lecturas'
      : `${desde}–${hasta} de ${total}  ·  Pág. ${paginaActual} / ${pages}`;
    prev.disabled = paginaActual <= 1;
    next.disabled = paginaActual >= pages;
  }
}

// ── Tótems ─────────────────────────────────────────────
function renderTotems(totems) {
  const ahora = Date.now();
  const grid  = document.getElementById('totems-grid');
  grid.style.paddingBottom = '1.2rem';
  grid.innerHTML = totems.map(t => {
    const online = t.ultima_actividad
      ? (ahora - new Date(t.ultima_actividad).getTime()) < 300000
      : false;
    const hora = t.ultima_actividad ? t.ultima_actividad.split(' ')[1] : null;
    return `
      <div class="totem-card${online ? ' is-online' : ''}">
        <div style="display:flex; align-items:center; gap:.5rem; margin-bottom:.1rem;">
          <span class="totem-salon">${esc(t.salon)}</span>
          <span class="badge ${online ? 'badge-online' : 'badge-idle'}" style="flex-shrink:0">
            ${online ? '<span class="pulse-dot" style="margin-right:.3rem"></span>online' : 'idle'}
          </span>
        </div>
        <div class="totem-nombre">${esc(t.nombre)}</div>
        <div class="totem-meta">${t.tipo}</div>
        ${t.ip_local ? `<div class="totem-ip">${t.ip_local}</div>` : ''}
        <div class="totem-act">${hora ? 'Últ. actividad: ' + hora : 'Sin actividad'}</div>
      </div>`;
  }).join('');
  if (firstLoad) staggerIn('#totems-grid .totem-card', { duration: 0.28, stagger: 0.05 });
}

function esc(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }

cargar(1);
// Auto-refresh: si el usuario está en pág. 1 actualiza lecturas en vivo;
// si navega a otra página, solo refresca salones y tótems (no resetea la página)
setInterval(function() {
  if (paginaActual === 1) {
    cargar(1);
  } else {
    // Refresca solo salones y tótems sin tocar la tabla de lecturas
    fetch(API_BASE + '/monitor?page=1').then(r => r.json()).then(d => {
      renderSalones(d.salones);
      renderTotems(d.totems);
      document.getElementById('ultima-act').textContent    = 'Act. ' + d.timestamp.split(' ')[1];
      document.getElementById('refresh-label').textContent = 'auto-refresh ' + (REFRESH_MS/1000) + 's';
      startRefreshBar();
    }).catch(() => {});
  }
}, REFRESH_MS);
</script>

</main></body></html>
