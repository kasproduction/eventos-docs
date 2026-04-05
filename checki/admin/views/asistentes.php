<?php $titulo = 'Asistentes'; $seccion = 'asistentes'; include __DIR__ . '/_layout.php'; ?>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem; flex-wrap:wrap; gap:.8rem;">
  <h1 style="font-size:1.1rem; color:var(--text-2);">Asistentes</h1>
  <button class="btn btn-ghost btn-sm" onclick="abrirImportar()">Importar CSV/JSON</button>
</div>

<!-- FILTROS -->
<div style="display:flex; gap:.8rem; margin-bottom:1rem; flex-wrap:wrap; align-items:center;">
  <input id="buscar" type="text" placeholder="Buscar nombre, email o QR…"
    style="flex:1; min-width:200px; padding:.55rem .9rem; background:var(--surface-2);
           border:1px solid rgba(0,0,0,.1); border-radius:6px; color:var(--text-1); font-size:.88rem;"
    oninput="debounce(cargar, 350)()">
  <select id="filtro-estado" onchange="cargar()"
    style="padding:.55rem .9rem; background:var(--surface-2); border:1px solid rgba(0,0,0,.1);
           border-radius:6px; color:var(--text-1); font-size:.88rem;">
    <option value="">Todos</option>
    <option value="dentro">Dentro ahora</option>
    <option value="fuera">Fuera</option>
  </select>
  <select id="filtro-limite" onchange="cargar(1)"
    style="padding:.55rem .9rem; background:var(--surface-2); border:1px solid rgba(0,0,0,.1);
           border-radius:6px; color:var(--text-1); font-size:.88rem;">
    <option value="10">10 por página</option>
    <option value="25">25 por página</option>
    <option value="50" selected>50 por página</option>
  </select>
</div>

<div class="card">
  <div id="resumen" style="font-size:.82rem; color:var(--text-2); margin-bottom:.8rem;"></div>
  <table>
    <thead>
      <tr><th>Nombre</th><th>Email</th><th>Empresa</th><th>UID QR</th><th>Estado</th><th>Salón actual</th><th>Fuente</th><th></th></tr>
    </thead>
    <tbody id="tabla-body"></tbody>
  </table>
  <div id="paginacion" style="margin-top:1rem; display:flex; gap:.5rem; flex-wrap:wrap;"></div>
</div>

<!-- MODAL CORRECCIÓN MANUAL -->
<div class="modal-bg" id="modal-manual">
  <div class="modal">
    <h3>Corrección manual</h3>
    <input type="hidden" id="man-asistente-id">
    <div id="man-asistente-nombre" style="color:var(--text-2); margin-bottom:1rem;"></div>
    <label>Salón</label>
    <select id="man-salon"></select>
    <label>Tipo</label>
    <select id="man-tipo">
      <option value="checkin">Check-in</option>
      <option value="checkout">Check-out</option>
    </select>
    <label>Motivo (obligatorio)</label>
    <textarea id="man-motivo" placeholder="Ej: El lector no respondió…"></textarea>
    <label>Tu PIN</label>
    <input type="password" id="man-pin" placeholder="••••" inputmode="numeric">
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="cerrarModal('modal-manual')">Cancelar</button>
      <button class="btn btn-danger" onclick="enviarManual()">Registrar</button>
    </div>
  </div>
</div>

<!-- MODAL IMPORTAR -->
<div class="modal-bg" id="modal-importar">
  <div class="modal">
    <h3>Importar asistentes</h3>
    <label>Formato</label>
    <select id="imp-fuente">
      <option value="csv">CSV</option>
      <option value="json">JSON</option>
    </select>
    <label>Archivo</label>
    <input type="file" id="imp-archivo" accept=".csv,.json,.txt">
    <div style="font-size:.78rem; color:var(--text-2); margin-top:.5rem;">
      El CSV debe tener columnas: uid_qr (o qr, badge…), nombre (o name…). El resto es opcional.
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="cerrarModal('modal-importar')">Cancelar</button>
      <button class="btn btn-primary" onclick="enviarImportar()">Importar</button>
    </div>
  </div>
</div>

<div id="flash-container"></div>

<script>
let paginaActual = 1;
let salones = [];

async function cargarSalones() {
  const r = await fetch(API_BASE + '/agenda');
  const d = await r.json();
  salones = d.salones || [];
}

async function cargar(pag) {
  if (pag) paginaActual = pag;
  const q      = document.getElementById('buscar').value;
  const estado = document.getElementById('filtro-estado').value;
  const limite = document.getElementById('filtro-limite').value;
  const params = new URLSearchParams({ pagina: paginaActual, q, estado, limite });
  const r = await fetch(API_BASE + '/asistentes?' + params);
  const d = await r.json();

  document.getElementById('resumen').textContent =
    `${d.total} asistentes · página ${d.pagina} de ${d.paginas}`;

  document.getElementById('tabla-body').innerHTML = d.asistentes.map(a => `
    <tr>
      <td style="font-weight:500">${esc(a.nombre)}</td>
      <td style="font-size:.82rem;color:var(--text-2)">${esc(a.email||'')}</td>
      <td style="font-size:.82rem;color:var(--text-2)">${esc(a.empresa||'')}</td>
      <td style="font-size:.78rem;font-family:monospace;color:var(--text-3)">${esc(a.uid_qr||'—')}</td>
      <td><span class="badge ${a.estado==='dentro'?'badge-verde':'badge-gris'}">${a.estado}</span></td>
      <td style="font-size:.82rem">${esc(a.salon_actual||'—')}</td>
      <td style="font-size:.75rem;color:var(--text-3)">${esc(a.fuente)}</td>
      <td><button class="btn btn-ghost btn-sm" onclick="abrirManual(${a.id}, ${JSON.stringify(a.nombre).replace(/"/g,'&quot;')})">Corregir</button></td>
    </tr>
  `).join('');

  // Paginación mejorada
  const pg = d;
  let htmlPag = '';
  
  if (pg.paginas > 1) {
    // Botón anterior
    if (pg.pagina > 1) {
      htmlPag += `<button class="btn btn-ghost btn-sm" onclick="cargar(${pg.pagina - 1})">← Anterior</button>`;
    }
    
    // Números de página
    const maxVisibles = 5;
    let inicio = Math.max(1, pg.pagina - Math.floor(maxVisibles / 2));
    let fin = Math.min(pg.paginas, inicio + maxVisibles - 1);
    
    if (fin - inicio < maxVisibles - 1) {
      inicio = Math.max(1, fin - maxVisibles + 1);
    }
    
    if (inicio > 1) {
      htmlPag += `<button class="btn btn-ghost btn-sm" onclick="cargar(1)">1</button>`;
      if (inicio > 2) htmlPag += `<span style="padding:0 .4rem;color:var(--text-3)">…</span>`;
    }
    
    for (let i = inicio; i <= fin; i++) {
      htmlPag += `<button class="btn btn-sm ${i === pg.pagina ? 'btn-primary' : 'btn-ghost'}" onclick="cargar(${i})">${i}</button>`;
    }
    
    if (fin < pg.paginas) {
      if (fin < pg.paginas - 1) htmlPag += `<span style="padding:0 .4rem;color:var(--text-3)">…</span>`;
      htmlPag += `<button class="btn btn-ghost btn-sm" onclick="cargar(${pg.paginas})">${pg.paginas}</button>`;
    }
    
    // Botón siguiente
    if (pg.pagina < pg.paginas) {
      htmlPag += `<button class="btn btn-ghost btn-sm" onclick="cargar(${pg.pagina + 1})">Siguiente →</button>`;
    }
  }
  
  document.getElementById('paginacion').innerHTML = htmlPag;
  if (paginaActual === 1) scrollToEl('#tabla-body');
}

async function abrirManual(asistente_id, nombre) {
  if (!salones.length) await cargarSalones();
  document.getElementById('man-asistente-id').value  = asistente_id;
  document.getElementById('man-asistente-nombre').textContent = nombre;
  document.getElementById('man-salon').innerHTML = salones.map(s =>
    `<option value="${s.id}">${esc(s.nombre)}</option>`
  ).join('');
  document.getElementById('man-motivo').value = '';
  document.getElementById('man-pin').value    = '';
  abrirModal('modal-manual');
}

async function enviarManual() {
  const body = {
    asistente_id: parseInt(document.getElementById('man-asistente-id').value),
    salon_id:     parseInt(document.getElementById('man-salon').value),
    tipo:         document.getElementById('man-tipo').value,
    motivo:       document.getElementById('man-motivo').value,
    pin:          document.getElementById('man-pin').value,
  };
  if (!body.motivo) { alert('El motivo es obligatorio'); return; }
  const r = await fetch(API_BASE + '/movimiento-manual', {
    method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)
  });
  const d = await r.json();
  if (d.ok) { cerrarModal('modal-manual'); cargar(); flash('Movimiento registrado', 'ok'); }
  else flash('Error: ' + (d.mensaje||JSON.stringify(d)), 'err');
}

function abrirImportar() { abrirModal('modal-importar'); }

async function enviarImportar() {
  const archivo = document.getElementById('imp-archivo').files[0];
  if (!archivo) { alert('Selecciona un archivo'); return; }
  const fuente = document.getElementById('imp-fuente').value;
  const form = new FormData();
  form.append('tipo',    'asistentes');
  form.append('fuente',  fuente);
  form.append('archivo', archivo);
  const r = await fetch(API_BASE + '/importar', { method:'POST', body: form });
  const d = await r.json();
  cerrarModal('modal-importar');
  if (d.ok) {
    flash(`Importados: ${d.insertados} nuevos, ${d.actualizados} actualizados, ${d.omitidos} omitidos`, 'ok');
    cargar();
  } else flash('Error: ' + JSON.stringify(d), 'err');
}

function flash(msg, tipo) {
  const el = document.createElement('div');
  el.className = `flash flash-${tipo}`;
  el.textContent = msg;
  document.getElementById('flash-container').prepend(el);
  setTimeout(() => el.remove(), 5000);
}

function abrirModal(id)  { document.getElementById(id).classList.add('open'); }
function cerrarModal(id) { document.getElementById(id).classList.remove('open'); }
function esc(s) { const d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }

function debounce(fn, ms) {
  let t;
  return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
}

cargar();
</script>

</main></body></html>
