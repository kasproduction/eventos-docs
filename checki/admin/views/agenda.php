<?php $titulo = 'Agenda'; $seccion = 'agenda'; include __DIR__ . '/_layout.php'; ?>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem; flex-wrap:wrap; gap:.8rem;">
  <h1 style="font-size:1.1rem; color:var(--text-2);">Agenda</h1>
  <div style="display:flex; gap:.8rem; flex-wrap:wrap;">
    <select id="sel-dia" class="btn btn-ghost" onchange="cargar()">
      <option value="">Cargando días...</option>
    </select>
    <button class="btn btn-ghost btn-sm" onclick="abrirRetrasar()">Retrasar salón...</button>
    <button class="btn btn-primary btn-sm" onclick="abrirNuevaCharla()">+ Nueva charla</button>
    <button class="btn btn-ghost btn-sm" onclick="location.reload()">Actualizar</button>
  </div>
</div>

<div id="charlas-container"></div>

<!-- MODAL EDITAR CHARLA -->
<div class="modal-bg" id="modal-editar">
  <div class="modal">
    <h3>Editar charla</h3>
    <input type="hidden" id="edit-id">
    <label>Salón</label>
    <select id="edit-salon"></select>
    <label>Título</label>
    <input type="text" id="edit-titulo">
    <label>Ponente</label>
    <input type="text" id="edit-ponente">
    <label>Hora inicio</label>
    <input type="datetime-local" id="edit-inicio">
    <label>Hora fin</label>
    <input type="datetime-local" id="edit-fin">
    <label>Cancelada</label>
    <select id="edit-cancelada">
      <option value="0">No</option>
      <option value="1">Sí — cancelar charla</option>
    </select>
    <label>Motivo del cambio</label>
    <textarea id="edit-motivo" placeholder="Opcional…"></textarea>
    <div class="modal-footer">
      <button class="btn btn-danger" style="margin-right:auto" onclick="eliminarCharla()">Eliminar</button>
      <button class="btn btn-ghost" onclick="cerrarModal('modal-editar')">Cancelar</button>
      <button class="btn btn-primary" onclick="guardarCharla()">Guardar</button>
    </div>
  </div>
</div>

<!-- MODAL NUEVA CHARLA -->
<div class="modal-bg" id="modal-nueva">
  <div class="modal">
    <h3>Nueva charla</h3>
    <label>Día</label>
    <select id="nueva-dia"></select>
    <label>Salón</label>
    <select id="nueva-salon"></select>
    <label>Título</label>
    <input type="text" id="nueva-titulo" placeholder="Título de la charla">
    <label>Ponente</label>
    <input type="text" id="nueva-ponente" placeholder="Nombre del ponente (opcional)">
    <label>Hora inicio</label>
    <input type="datetime-local" id="nueva-inicio">
    <label>Hora fin</label>
    <input type="datetime-local" id="nueva-fin">
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="cerrarModal('modal-nueva')">Cancelar</button>
      <button class="btn btn-primary" onclick="guardarNuevaCharla()">Crear</button>
    </div>
  </div>
</div>

<!-- MODAL RETRASAR -->
<div class="modal-bg" id="modal-retrasar">
  <div class="modal">
    <h3>Retrasar agenda de un salón</h3>
    <label>Salón</label>
    <select id="ret-salon"></select>
    <label>Minutos (positivo = retrasar, negativo = adelantar)</label>
    <input type="number" id="ret-min" value="15" min="-240" max="240">
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="cerrarModal('modal-retrasar')">Cancelar</button>
      <button class="btn btn-danger" onclick="confirmarRetrasar()">Aplicar</button>
    </div>
  </div>
</div>

<script>
let diaIdActual = 0;
let salonesList = [];
let diasList = [];
let charlasMap = {};

const ESTADOS = {
  en_curso:  ['badge-verde',   'En curso'],
  proxima:   ['badge-azul',    'Próxima'],
  finalizada:['badge-gris',    'Finalizada'],
  cancelada: ['badge-rojo',    'Cancelada'],
};

async function cargar() {
  const sel = document.getElementById('sel-dia');
  diaIdActual = sel.value || 0;
  const r = await fetch(API_BASE + '/agenda?dia_id=' + diaIdActual);
  const d = await r.json();

  // Poblar selector de días (solo primera vez)
  if (sel.options[0]?.text === 'Cargando días...') {
    sel.innerHTML = d.dias.map(dia =>
      `<option value="${dia.id}" ${dia.id == d.dia_id ? 'selected' : ''}>${dia.nombre || dia.fecha}</option>`
    ).join('');
    diaIdActual = d.dia_id;
    diasList = d.dias;
  }

  // Salones completos desde la API (incluye salones sin charlas)
  salonesList = d.salones;

  // Agrupar charlas por salón y guardar en mapa para lookup seguro
  charlasMap = {};
  const porSalon = {};
  for (const c of d.charlas) {
    charlasMap[c.id] = c;
    if (!porSalon[c.salon_id]) porSalon[c.salon_id] = { nombre: c.salon_nombre, charlas: [] };
    porSalon[c.salon_id].charlas.push(c);
  }

  // Render
  document.getElementById('charlas-container').innerHTML = Object.entries(porSalon).map(([sid, salon]) => `
    <div class="card" style="margin-bottom:1rem;">
      <h2>${esc(salon.nombre)}</h2>
      <table>
        <thead>
          <tr><th>Hora</th><th>Charla</th><th>Ponente</th><th>Estado</th><th>Dentro</th><th></th></tr>
        </thead>
        <tbody>
          ${salon.charlas.map(c => {
            const [cls, label] = ESTADOS[c.estado] || ['badge-gris', c.estado];
            const inicio = c.hora_inicio.replace('T',' ').substring(0,16);
            const fin    = c.hora_fin.replace('T',' ').substring(0,16);
            const modf   = c.hora_inicio !== c.hora_inicio_original ? ' *' : '';
            return `<tr>
              <td style="font-family:monospace;font-size:.82rem;color:#6060a0">
                ${inicio.split(' ')[1]}–${fin.split(' ')[1]}${modf}
              </td>
              <td style="font-weight:500">${esc(c.titulo)}</td>
              <td style="font-size:.82rem;color:#6060a0">${esc(c.ponente||'')}</td>
              <td><span class="badge ${cls}">${label}</span></td>
              <td style="text-align:center">${c.asistentes_dentro}</td>
              <td><button class="btn btn-ghost btn-sm" onclick="abrirEditar(${c.id})">Editar</button></td>
            </tr>`;
          }).join('')}
        </tbody>
      </table>
    </div>
  `).join('');
}

function abrirEditar(id) {
  const c = charlasMap[id];
  if (!c) return;
  document.getElementById('edit-id').value = c.id;
  let salonOpts = salonesList.map(s =>
    `<option value="${s.id}" ${s.id == c.salon_id ? 'selected' : ''}>${esc(s.nombre)}</option>`
  ).join('');
  if (!salonesList.find(s => s.id == c.salon_id)) {
    salonOpts = `<option value="${c.salon_id}" selected>${esc(c.salon_nombre)} (inactivo)</option>` + salonOpts;
  }
  document.getElementById('edit-salon').innerHTML = salonOpts;
  document.getElementById('edit-titulo').value   = c.titulo;
  document.getElementById('edit-ponente').value  = c.ponente || '';
  document.getElementById('edit-inicio').value   = c.hora_inicio.replace(' ','T').substring(0,16);
  document.getElementById('edit-fin').value      = c.hora_fin.replace(' ','T').substring(0,16);
  document.getElementById('edit-cancelada').value= c.cancelada;
  document.getElementById('edit-motivo').value   = '';
  abrirModal('modal-editar');
}

async function eliminarCharla() {
  const id = document.getElementById('edit-id').value;
  const titulo = document.getElementById('edit-titulo').value;
  if (!confirm(`¿Eliminar la charla "${titulo}"?\nSolo es posible si aún no tiene asistencia calculada.`)) return;
  const r = await fetch(`${API_BASE}/charla/${id}`, { method: 'DELETE' });
  const d = await r.json();
  if (d.ok) { cerrarModal('modal-editar'); cargar(); }
  else alert('No se puede eliminar: ' + (d.mensaje || JSON.stringify(d)));
}

async function guardarCharla() {
  const id = document.getElementById('edit-id').value;
  const body = {
    salon_id:  parseInt(document.getElementById('edit-salon').value),
    titulo:    document.getElementById('edit-titulo').value,
    ponente:   document.getElementById('edit-ponente').value,
    hora_inicio: document.getElementById('edit-inicio').value.replace('T',' ') + ':00',
    hora_fin:    document.getElementById('edit-fin').value.replace('T',' ')    + ':00',
    cancelada: parseInt(document.getElementById('edit-cancelada').value),
    motivo:    document.getElementById('edit-motivo').value,
  };
  const r = await fetch(`${API_BASE}/charla/${id}`, {
    method: 'PATCH', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body)
  });
  const d = await r.json();
  if (d.ok) { cerrarModal('modal-editar'); cargar(); }
  else alert('Error: ' + (d.mensaje || JSON.stringify(d)));
}

function abrirRetrasar() {
  document.getElementById('ret-salon').innerHTML = salonesList.map(s =>
    `<option value="${s.id}">${esc(s.nombre)}</option>`
  ).join('');
  abrirModal('modal-retrasar');
}

async function confirmarRetrasar() {
  const salon_id = document.getElementById('ret-salon').value;
  const minutos  = parseInt(document.getElementById('ret-min').value);
  if (!confirm(`¿Retrasar ${minutos > 0 ? '+' : ''}${minutos} minutos todas las charlas del salón?`)) return;
  const r = await fetch(API_BASE + '/agenda/retrasar', {
    method: 'POST', headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ salon_id: parseInt(salon_id), dia_id: diaIdActual, minutos })
  });
  const d = await r.json();
  if (d.ok) { cerrarModal('modal-retrasar'); cargar(); }
  else alert('Error: ' + JSON.stringify(d));
}

function abrirNuevaCharla() {
  // Poblar selector de días
  document.getElementById('nueva-dia').innerHTML = diasList.map(dia =>
    `<option value="${dia.id}" ${dia.id == diaIdActual ? 'selected' : ''}>${dia.nombre || dia.fecha}</option>`
  ).join('');
  // Poblar selector de salones
  document.getElementById('nueva-salon').innerHTML = salonesList.map(s =>
    `<option value="${s.id}">${esc(s.nombre)}</option>`
  ).join('');
  // Hora por defecto: ahora redondeada a la media hora siguiente
  const now = new Date();
  now.setMinutes(now.getMinutes() >= 30 ? 60 : 30, 0, 0);
  const pad = n => String(n).padStart(2,'0');
  const fmt = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
  document.getElementById('nueva-inicio').value = fmt(now);
  const fin = new Date(now.getTime() + 60*60*1000);
  document.getElementById('nueva-fin').value = fmt(fin);
  document.getElementById('nueva-titulo').value  = '';
  document.getElementById('nueva-ponente').value = '';
  abrirModal('modal-nueva');
}

async function guardarNuevaCharla() {
  const body = {
    dia_evento_id: parseInt(document.getElementById('nueva-dia').value),
    salon_id:      parseInt(document.getElementById('nueva-salon').value),
    titulo:        document.getElementById('nueva-titulo').value.trim(),
    ponente:       document.getElementById('nueva-ponente').value.trim(),
    hora_inicio:   document.getElementById('nueva-inicio').value.replace('T',' ') + ':00',
    hora_fin:      document.getElementById('nueva-fin').value.replace('T',' ')    + ':00',
  };
  if (!body.titulo) { alert('El título es obligatorio.'); return; }
  const r = await fetch(API_BASE + '/charla', {
    method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(body)
  });
  const d = await r.json();
  if (d.ok) { cerrarModal('modal-nueva'); cargar(); }
  else alert('Error: ' + (d.mensaje || JSON.stringify(d)));
}

function abrirModal(id)  { document.getElementById(id).classList.add('open'); }
function cerrarModal(id) { document.getElementById(id).classList.remove('open'); }
function esc(s) { const d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }

cargar();
</script>

</main></body></html>
