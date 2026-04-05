<?php $titulo = 'Reportes'; $seccion = 'reportes'; include __DIR__ . '/_layout.php'; ?>

<style>
.rep-select {
  padding: .5rem .8rem;
  background: var(--surface-2);
  border: 1.5px solid rgba(0,0,0,.08);
  border-radius: var(--radius-sm);
  color: var(--text-1);
  font-size: .84rem;
  font-family: inherit;
  outline: none;
  transition: border-color .15s;
  min-width: 160px;
}
.rep-select:focus { border-color: var(--dark); }
body.dark .rep-select {
  background: var(--surface-2);
  border-color: rgba(255,255,255,.08);
  color: var(--text-1);
}
body.dark .rep-select:focus { border-color: var(--accent); }

.rep-input {
  flex: 1; min-width: 200px;
  padding: .5rem .8rem;
  background: var(--surface-2);
  border: 1.5px solid rgba(0,0,0,.08);
  border-radius: var(--radius-sm);
  color: var(--text-1);
  font-size: .84rem;
  font-family: inherit;
  outline: none;
  transition: border-color .15s;
}
.rep-input:focus { border-color: var(--dark); }
body.dark .rep-input { background: var(--surface-2); border-color: rgba(255,255,255,.08); color: var(--text-1); }
body.dark .rep-input:focus { border-color: var(--accent); }

.tab-btn { transition: background .15s, color .15s; }
.tab-btn.active { background: var(--dark) !important; color: #fff !important; }
body.dark .tab-btn.active { background: var(--accent) !important; color: #1A1A1A !important; }

.rep-hint {
  font-size: .82rem;
  color: var(--text-2);
  margin-bottom: 1rem;
  line-height: 1.5;
}
.rep-note {
  margin-top: 1rem;
  padding: .8rem 1rem;
  background: var(--surface-2);
  border-radius: var(--radius-sm);
  font-size: .78rem;
  color: var(--text-3);
  line-height: 1.6;
}
</style>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem;">
  <h1 style="font-size:1.15rem; font-weight:800; color:var(--text-1); letter-spacing:-.01em;">Reportes</h1>
</div>

<!-- TABS -->
<div style="display:flex; gap:.4rem; margin-bottom:1.2rem; flex-wrap:wrap;">
  <button class="btn btn-ghost btn-sm tab-btn active" id="tab-btn-dia"        onclick="showTab('dia')">Por día</button>
  <button class="btn btn-ghost btn-sm tab-btn"         id="tab-btn-charla"     onclick="showTab('charla')">Por charla</button>
  <button class="btn btn-ghost btn-sm tab-btn"         id="tab-btn-asistente"  onclick="showTab('asistente')">Por asistente</button>
  <button class="btn btn-ghost btn-sm tab-btn"         id="tab-btn-exportar"   onclick="showTab('exportar')">Exportar</button>
  <?php if (Auth::puedeEditar()): ?>
  <button class="btn btn-ghost btn-sm tab-btn"         id="tab-btn-recalcular" onclick="showTab('recalcular')">Recalcular</button>
  <?php endif; ?>
</div>

<!-- TAB DÍA -->
<div id="tab-dia" class="tab-content">
  <div class="card">
    <h2>Resumen por día</h2>
    <div style="display:flex; gap:.8rem; margin-bottom:1rem; flex-wrap:wrap; align-items:center;">
      <select id="sel-dia-rep" class="rep-select">
        <option value="">Seleccionar día…</option>
      </select>
      <button class="btn btn-primary btn-sm" onclick="cargarDia()">Ver</button>
    </div>
    <div id="resultado-dia"></div>
  </div>
</div>

<!-- TAB CHARLA -->
<div id="tab-charla" class="tab-content" style="display:none">
  <div class="card">
    <h2>Asistentes de una charla</h2>
    <div style="display:flex; gap:.8rem; margin-bottom:1rem; flex-wrap:wrap; align-items:center;">
      <select id="sel-dia-charla" class="rep-select" onchange="cargarCharlasDelDia()">
        <option value="">Seleccionar día…</option>
      </select>
      <select id="sel-charla" class="rep-select" style="flex:1; min-width:220px;">
        <option value="">— elige un día primero —</option>
      </select>
      <button class="btn btn-primary btn-sm" onclick="cargarCharla()">Ver</button>
    </div>
    <div id="resultado-charla"></div>
  </div>
</div>

<!-- TAB ASISTENTE -->
<div id="tab-asistente" class="tab-content" style="display:none">
  <div class="card">
    <h2>Historial de asistente</h2>
    <div style="display:flex; gap:.8rem; margin-bottom:1rem; flex-wrap:wrap; align-items:center;">
      <input id="buscar-asistente" class="rep-input" type="text" placeholder="Buscar nombre, email o QR…"
        onkeydown="if(event.key==='Enter') buscarAsistentes()">
      <button class="btn btn-ghost btn-sm" onclick="buscarAsistentes()">Buscar</button>
      <select id="sel-asistente" class="rep-select" style="flex:1; min-width:220px;">
        <option value="">— resultados de búsqueda —</option>
      </select>
      <button class="btn btn-primary btn-sm" onclick="cargarAsistente()">Ver</button>
    </div>
    <div id="resultado-asistente"></div>
  </div>
</div>

<!-- TAB EXPORTAR -->
<div id="tab-exportar" class="tab-content" style="display:none">
  <div style="display:flex; align-items:center; gap:.8rem; margin-bottom:1rem; flex-wrap:wrap;">
    <label style="font-size:.75rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--text-3); margin:0">Filtrar por día:</label>
    <select id="exp-dia" class="rep-select">
      <option value="">Todos los días</option>
    </select>
  </div>

  <div class="grid-2" style="gap:1rem;">
    <div class="card">
      <h2>Detalle completo</h2>
      <p class="rep-hint">Una fila por <strong>asistente × charla</strong>.<br>Incluye check-in, check-out, minutos y calidad del dato.<br>Ideal para filtrar y pivotar en Excel.</p>
      <button class="btn btn-primary" onclick="exportar('detalle')">Descargar CSV</button>
    </div>
    <div class="card">
      <h2>Matriz por asistente</h2>
      <p class="rep-hint">Una fila por <strong>asistente</strong>, una columna por charla.<br>Muestra SI/NO + minutos en cada celda.<br>Vista rápida de quién asistió a qué.</p>
      <button class="btn btn-primary" onclick="exportar('por_asistente')">Descargar CSV</button>
    </div>
    <div class="card">
      <h2>Resumen por charla</h2>
      <p class="rep-hint">Una fila por <strong>charla</strong> con totales:<br>cuántos asistieron, promedio de minutos, % asistencia.<br>Para el reporte ejecutivo al cliente.</p>
      <button class="btn btn-primary" onclick="exportar('por_charla')">Descargar CSV</button>
    </div>
    <div class="card">
      <h2>Resumen por asistente</h2>
      <p class="rep-hint">Una fila por <strong>asistente</strong> con totales:<br>días asistidos, charlas completas, minutos totales.<br>Vista consolidada de la participación de cada persona.</p>
      <button class="btn btn-primary" onclick="exportar('resumen')">Descargar CSV</button>
    </div>
  </div>

  <div class="rep-note">
    Todos los CSV usan <strong>punto y coma (;)</strong> como separador y <strong>UTF-8 con BOM</strong>
    para que Excel los abra correctamente sin configuración adicional.<br>
    Los datos marcados con <strong>*</strong> en la matriz son inferidos (sin checkout registrado).
  </div>
</div>

<!-- TAB RECALCULAR -->
<?php if (Auth::puedeEditar()): ?>
<div id="tab-recalcular" class="tab-content" style="display:none">
  <div class="card">
    <h2>Recalcular asistencia</h2>
    <p style="font-size:.855rem; color:var(--text-2); margin-bottom:1rem; line-height:1.55;">
      Regenera <code style="background:var(--surface-2);padding:.1rem .4rem;border-radius:4px;font-size:.82rem">asistencia_calculada</code>
      desde los movimientos reales × la agenda actual.<br>
      Úsalo después de cambiar la agenda o al final de cada día.
    </p>
    <div style="display:flex; gap:.8rem; flex-wrap:wrap; align-items:center;">
      <select id="rec-dia" class="rep-select">
        <option value="">Todos los días del evento</option>
      </select>
      <button class="btn btn-danger" onclick="recalcular()">Recalcular ahora</button>
    </div>
    <div id="resultado-rec" style="margin-top:1rem;"></div>
  </div>
</div>
<?php endif; ?>

<script>
// ── Helpers ────────────────────────────────────────────────
function esc(s) { const d = document.createElement('div'); d.textContent = s||''; return d.innerHTML; }

function fmtHora(dt) {
  if (!dt) return '—';
  // Soporta tanto "HH:MM:SS" como "YYYY-MM-DD HH:MM:SS"
  const partes = dt.split(' ');
  return (partes[1] || partes[0]).substring(0, 5);
}

function sinDatosHtml(msg) {
  return `<div class="flash flash-err" style="margin-top:.5rem">${msg}</div>`;
}

function recalcularHintHtml() {
  return `<div style="margin-top:.8rem; padding:.7rem 1rem; background:rgba(240,192,64,.1);
    border:1px solid rgba(240,192,64,.3); border-radius:var(--radius-sm);
    font-size:.82rem; color:var(--text-2); line-height:1.55;">
    ⚠️ No hay datos de asistencia calculados para este período.<br>
    Ve a la pestaña <strong>Recalcular</strong> y ejecuta el cálculo primero.
  </div>`;
}

// ── Cargar días en todos los selectores ───────────────────
async function cargarDias() {
  const r = await fetch(API_BASE + '/agenda');
  if (!r.ok) return;
  const d = await r.json();
  const ids = ['sel-dia-rep','sel-dia-charla','exp-dia','rec-dia'];
  for (const id of ids) {
    const el = document.getElementById(id);
    if (!el) continue;
    const first = el.querySelector('[value=""]');
    const firstHtml = first ? first.outerHTML : '';
    el.innerHTML = firstHtml + d.dias.map(dia =>
      `<option value="${dia.id}">${esc(dia.nombre || dia.fecha)}</option>`
    ).join('');
  }
}

// ── TAB: Por día ───────────────────────────────────────────
async function cargarDia() {
  const dia_id = document.getElementById('sel-dia-rep').value;
  if (!dia_id) return;

  const el = document.getElementById('resultado-dia');
  el.innerHTML = '<p style="color:var(--text-2);font-size:.85rem">Cargando…</p>';
  scrollToEl('#resultado-dia');

  const r = await fetch(`${API_BASE}/reporte/dia/${dia_id}`);
  if (!r.ok) {
    el.innerHTML = sinDatosHtml('Error al cargar los datos del día.');
    return;
  }
  const d = await r.json();

  if (!d.charlas?.length) {
    el.innerHTML = '<p style="color:var(--text-2)">No hay charlas para este día.</p>';
    return;
  }

  // Detectar si asistencia_calculada está vacía para este día
  const sinCalculo = d.asistentes_unicos === 0 &&
    d.charlas.every(c => !parseInt(c.total_registros));

  el.innerHTML = `
    <p style="font-size:.855rem; color:var(--text-2); margin-bottom:.8rem">
      Asistentes únicos ese día: <strong style="color:var(--text-1)">${d.asistentes_unicos}</strong>
    </p>
    ${sinCalculo ? recalcularHintHtml() : ''}
    <table>
      <thead>
        <tr>
          <th>Charla</th><th>Salón</th><th>Horario</th>
          <th>Registros</th><th>Cuentan</th><th>Prom. min</th><th>Inferidos</th>
        </tr>
      </thead>
      <tbody>
        ${d.charlas.map(c => `<tr>
          <td style="font-weight:500">${esc(c.titulo)}</td>
          <td>${esc(c.salon)}</td>
          <td style="font-size:.8rem; color:var(--text-2); font-family:monospace">
            ${fmtHora(c.hora_inicio)} – ${fmtHora(c.hora_fin)}
          </td>
          <td>${parseInt(c.total_registros) || 0}</td>
          <td>${parseInt(c.total_cuentan)   || 0}</td>
          <td>${parseFloat(c.promedio_minutos) || 0}</td>
          <td>
            <span class="badge ${parseInt(c.inferidos) > 0 ? 'badge-naranja' : 'badge-gris'}">
              ${parseInt(c.inferidos) || 0}
            </span>
          </td>
        </tr>`).join('')}
      </tbody>
    </table>`;
}

// ── TAB: Por charla ────────────────────────────────────────
async function cargarCharlasDelDia() {
  const dia_id = document.getElementById('sel-dia-charla').value;
  const sel    = document.getElementById('sel-charla');
  if (!dia_id) { sel.innerHTML = '<option value="">— elige un día primero —</option>'; return; }
  sel.innerHTML = '<option value="">Cargando…</option>';
  const r = await fetch(`${API_BASE}/agenda?dia_id=${dia_id}`);
  if (!r.ok) { sel.innerHTML = '<option value="">Error al cargar</option>'; return; }
  const d = await r.json();
  if (!d.charlas?.length) {
    sel.innerHTML = '<option value="">Sin charlas ese día</option>';
    return;
  }
  sel.innerHTML = '<option value="">Seleccionar charla…</option>' +
    d.charlas.map(c => `<option value="${c.id}">[${esc(c.salon_nombre)}] ${esc(c.titulo)}</option>`).join('');
}

async function cargarCharla() {
  const id = document.getElementById('sel-charla').value;
  if (!id) return;

  const el = document.getElementById('resultado-charla');
  el.innerHTML = '<p style="color:var(--text-2);font-size:.85rem">Cargando…</p>';
  scrollToEl('#resultado-charla');

  const r = await fetch(`${API_BASE}/reporte/charla/${id}`);
  if (!r.ok) {
    const d = await r.json().catch(() => ({}));
    el.innerHTML = sinDatosHtml(apiErr(d, 'Error al cargar la charla.'));
    return;
  }
  const d = await r.json();

  const infoBox = `
    <div style="margin-bottom:1rem; padding:.8rem 1rem; background:var(--surface-2);
      border-radius:var(--radius-sm); border-left:3px solid var(--accent);">
      <strong>${esc(d.charla.titulo)}</strong>
      <span style="color:var(--text-2)"> · ${esc(d.charla.salon_nombre)}</span>
    </div>`;

  if (!d.asistentes?.length) {
    el.innerHTML = infoBox + recalcularHintHtml();
    return;
  }

  el.innerHTML = infoBox + `
    <table>
      <thead>
        <tr><th>Nombre</th><th>Email</th><th>Check-in</th><th>Check-out</th><th>Min</th><th>Cuenta</th><th>Calidad</th></tr>
      </thead>
      <tbody>
        ${d.asistentes.map(a => `<tr>
          <td style="font-weight:500">${esc(a.nombre)}</td>
          <td style="font-size:.8rem; color:var(--text-2)">${esc(a.email||'')}</td>
          <td style="font-size:.78rem; font-family:monospace; color:var(--text-2)">${fmtHora(a.checkin_real)}</td>
          <td style="font-size:.78rem; font-family:monospace; color:var(--text-2)">${fmtHora(a.checkout_real)}</td>
          <td>${parseInt(a.minutos_presentes) || 0}</td>
          <td>
            <span class="badge ${a.cuenta_asistencia ? 'badge-verde' : 'badge-rojo'}">
              ${a.cuenta_asistencia ? 'SÍ' : 'NO'}
            </span>
          </td>
          <td style="font-size:.78rem; color:var(--text-3)">${esc(a.calidad_dato||'')}</td>
        </tr>`).join('')}
      </tbody>
    </table>`;
}

// ── TAB: Por asistente ─────────────────────────────────────
async function buscarAsistentes() {
  const q   = document.getElementById('buscar-asistente').value.trim();
  if (!q) return;
  const sel = document.getElementById('sel-asistente');
  sel.innerHTML = '<option value="">Buscando…</option>';
  const r = await fetch(`${API_BASE}/asistentes?q=${encodeURIComponent(q)}&pagina=1`);
  if (!r.ok) { sel.innerHTML = '<option value="">Error en la búsqueda</option>'; return; }
  const d = await r.json();
  if (!d.asistentes?.length) {
    sel.innerHTML = '<option value="">Sin resultados</option>';
    return;
  }
  sel.innerHTML = d.asistentes.map(a =>
    `<option value="${a.id}">${esc(a.nombre)}${a.email ? ' — ' + esc(a.email) : ''}${a.empresa ? ' (' + esc(a.empresa) + ')' : ''}</option>`
  ).join('');
}

async function cargarAsistente() {
  const id = document.getElementById('sel-asistente').value;
  if (!id) return;

  const el = document.getElementById('resultado-asistente');
  el.innerHTML = '<p style="color:var(--text-2);font-size:.85rem">Cargando…</p>';
  scrollToEl('#resultado-asistente');

  const r = await fetch(`${API_BASE}/reporte/asistente/${id}`);
  if (!r.ok) {
    const d = await r.json().catch(() => ({}));
    el.innerHTML = sinDatosHtml(apiErr(d, 'Error al cargar el asistente.'));
    return;
  }
  const d = await r.json();

  const infoBox = `
    <div style="margin-bottom:1rem; padding:.8rem 1rem; background:var(--surface-2);
      border-radius:var(--radius-sm); border-left:3px solid var(--accent);">
      <strong>${esc(d.asistente.nombre)}</strong>
      ${d.asistente.email   ? `<span style="color:var(--text-2)"> · ${esc(d.asistente.email)}</span>` : ''}
      ${d.asistente.empresa ? `<span style="color:var(--text-3)"> · ${esc(d.asistente.empresa)}</span>` : ''}
    </div>`;

  if (!d.charlas?.length) {
    el.innerHTML = infoBox + recalcularHintHtml();
    return;
  }

  el.innerHTML = infoBox + `
    <table>
      <thead>
        <tr><th>Día</th><th>Salón</th><th>Charla</th><th>Check-in</th><th>Check-out</th><th>Min</th><th>Cuenta</th></tr>
      </thead>
      <tbody>
        ${d.charlas.map(c => `<tr>
          <td style="font-size:.78rem; color:var(--text-3); font-family:monospace">${c.fecha}</td>
          <td style="font-size:.8rem">${esc(c.salon_nombre)}</td>
          <td style="font-weight:500">${esc(c.titulo)}</td>
          <td style="font-size:.78rem; font-family:monospace; color:var(--text-2)">${fmtHora(c.checkin_real)}</td>
          <td style="font-size:.78rem; font-family:monospace; color:var(--text-2)">${fmtHora(c.checkout_real)}</td>
          <td>${parseInt(c.minutos_presentes) || 0}</td>
          <td>
            <span class="badge ${c.cuenta_asistencia ? 'badge-verde' : 'badge-rojo'}">
              ${c.cuenta_asistencia ? 'SÍ' : 'NO'}
            </span>
          </td>
        </tr>`).join('')}
      </tbody>
    </table>`;
}

// ── Exportar ───────────────────────────────────────────────
function exportar(formato) {
  const dia_id = document.getElementById('exp-dia').value;
  window.location.href = `${API_BASE}/exportar?formato=${formato}&dia_id=${dia_id}`;
}

// ── Recalcular ─────────────────────────────────────────────
async function recalcular() {
  const dia_id = document.getElementById('rec-dia').value;
  const ok = await confirmDialog(
    '¿Recalcular asistencia? Los datos anteriores se reemplazarán con los movimientos actuales.',
    { title: 'Recalcular asistencia', ok: 'Sí, recalcular', danger: true }
  );
  if (!ok) return;

  const elRes = document.getElementById('resultado-rec');
  elRes.innerHTML = '<p style="color:var(--text-2);font-size:.85rem">Calculando…</p>';

  const r = await fetch(API_BASE + '/recalcular-asistencia', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ evento_id: 0, dia_id: parseInt(dia_id)||0 })
  });
  const d = await r.json();

  elRes.innerHTML = d.ok
    ? `<div class="flash flash-ok">
        Listo — <strong>${d.charlas_procesadas}</strong> charlas procesadas,
        <strong>${d.registros_calculados}</strong> registros calculados.
        ${d.errores?.length ? `<br><span style="color:var(--text-2)">Advertencias: ${d.errores.join(', ')}</span>` : ''}
       </div>`
    : `<div class="flash flash-err">Error: ${apiErr(d, JSON.stringify(d))}</div>`;
}

// ── Tabs ───────────────────────────────────────────────────
function showTab(id) {
  document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
  document.getElementById('tab-' + id).style.display = '';
  const btn = document.getElementById('tab-btn-' + id);
  if (btn) btn.classList.add('active');
}

cargarDias();
</script>

</main></body></html>
