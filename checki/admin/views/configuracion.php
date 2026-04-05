<?php $titulo = 'Configuración'; $seccion = 'configuracion'; include __DIR__ . '/_layout.php'; ?>

<!-- TABS -->
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem; flex-wrap:wrap; gap:.8rem;">
  <h1 style="font-size:1.1rem; color:var(--text-2);">Configuración del evento</h1>
</div>

<div style="display:flex; gap:.5rem; margin-bottom:1.2rem; flex-wrap:wrap;">
  <button class="btn btn-ghost" id="tab-dias"      onclick="mostrarTab('dias')">Días del evento</button>
  <button class="btn btn-ghost" id="tab-salones"   onclick="mostrarTab('salones')">Salones</button>
  <button class="btn btn-ghost" id="tab-totems"    onclick="mostrarTab('totems')">Tótems / Puntos check-in</button>
  <button class="btn btn-ghost" id="tab-pantallas" onclick="mostrarTab('pantallas')">Pantallas de agenda</button>
  <button class="btn btn-ghost" id="tab-demo"      onclick="mostrarTab('demo')" style="color:var(--accent-red);">Demo / Reset</button>
</div>

<!-- SECCIÓN DÍAS -->
<div id="sec-dias" class="card" style="margin-bottom:1rem;">
  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
    <h2>Días del evento</h2>
    <button class="btn btn-primary btn-sm" onclick="abrirNuevoDia()">+ Nuevo día</button>
  </div>
  <table id="tabla-dias">
    <thead><tr><th>#</th><th>Fecha</th><th>Nombre</th><th></th></tr></thead>
    <tbody></tbody>
  </table>
</div>

<!-- SECCIÓN SALONES -->
<div id="sec-salones" class="card" style="margin-bottom:1rem; display:none;">
  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
    <h2>Salones</h2>
    <button class="btn btn-primary btn-sm" onclick="abrirNuevoSalon()">+ Nuevo salón</button>
  </div>
  <table id="tabla-salones">
    <thead><tr><th>ID</th><th>Nombre</th><th>Capacidad</th><th>Estado</th><th></th></tr></thead>
    <tbody></tbody>
  </table>
</div>

<!-- SECCIÓN PANTALLAS -->
<div id="sec-pantallas" class="card" style="margin-bottom:1rem; display:none;">

  <!-- Control global -->
  <div style="margin-bottom:1.4rem; padding-bottom:1.2rem; border-bottom:1px solid #2a2a4a;">
    <h2 style="margin-bottom:.6rem;">Control global de pantallas</h2>
    <p style="color:var(--text-2); font-size:.85rem; margin-bottom:.9rem;">
      Sobreescribe la configuración individual de todas las pantallas a la vez.
    </p>
    <div id="override-btns" style="display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:.7rem;"></div>

    <!-- Panel imagen global -->
    <div id="override-imagen-panel" style="display:none; margin-top:.8rem; padding:1rem; background:#12122a; border-radius:8px; border:1px solid #2a2a4a;">
      <div style="display:flex; gap:1rem; align-items:flex-start; flex-wrap:wrap;">
        <div>
          <label style="display:block; font-size:.82rem; color:var(--text-3); margin-bottom:.4rem;">Imagen (jpg/png/webp)</label>
          <input type="file" id="override-imagen-file" accept="image/*" onchange="previewOverrideImagen(this)">
          <div id="override-img-preview-wrap" style="margin-top:.6rem; display:none;">
            <img id="override-img-preview" style="max-width:180px; max-height:100px; border-radius:4px; object-fit:cover; border:1px solid #3a3a6a;">
          </div>
        </div>
        <div>
          <label style="display:block; font-size:.82rem; color:var(--text-3); margin-bottom:.4rem;">Volver a agenda en</label>
          <select id="override-retorno-select" style="min-width:140px;">
            <option value="0">Sin retorno automático</option>
            <option value="15">15 minutos</option>
            <option value="30">30 minutos</option>
            <option value="60">1 hora</option>
            <option value="120">2 horas</option>
          </select>
        </div>
        <div style="display:flex; align-items:flex-end;">
          <button class="btn btn-primary btn-sm" onclick="subirOverrideImagen()" style="margin-top:auto;">Subir y aplicar</button>
        </div>
      </div>
    </div>

    <!-- Panel video global -->
    <div id="override-video-panel" style="display:none; margin-top:.8rem; padding:1rem; background:#12122a; border-radius:8px; border:1px solid #2a2a4a;">
      <div style="display:flex; gap:1rem; align-items:flex-start; flex-wrap:wrap;">
        <div>
          <label style="display:block; font-size:.82rem; color:var(--text-3); margin-bottom:.4rem;">Video (mp4/webm)</label>
          <input type="file" id="override-video-file" accept="video/mp4,video/webm" onchange="previewOverrideVideo(this)">
          <div id="override-video-preview-wrap" style="margin-top:.6rem; display:none;">
            <video id="override-video-preview" controls style="max-width:200px; max-height:110px; border-radius:4px; border:1px solid #3a3a6a;"></video>
          </div>
          <div style="margin-top:.5rem; display:flex; align-items:center; gap:.5rem;">
            <input type="checkbox" id="override-loop-check" checked>
            <label for="override-loop-check" style="margin:0; font-size:.82rem; cursor:pointer; color:var(--text-3);">Reproducir en bucle</label>
          </div>
          <label style="display:block; font-size:.82rem; color:var(--text-3); margin-top:.5rem; margin-bottom:.3rem;">Ajuste en pantalla</label>
          <select id="override-video-fit-select" style="min-width:200px; font-size:.82rem;">
            <option value="contain">Completo (barras negras)</option>
            <option value="cover">Rellenar pantalla (recorta)</option>
          </select>
        </div>
        <div>
          <label style="display:block; font-size:.82rem; color:var(--text-3); margin-bottom:.4rem;">Volver a agenda en</label>
          <select id="override-video-retorno-select" style="min-width:140px;">
            <option value="0">Sin retorno automático</option>
            <option value="15">15 minutos</option>
            <option value="30">30 minutos</option>
            <option value="60">1 hora</option>
            <option value="120">2 horas</option>
          </select>
        </div>
        <div style="display:flex; align-items:flex-end;">
          <button class="btn btn-primary btn-sm" onclick="subirOverrideVideo()" style="margin-top:auto;">Subir y aplicar</button>
        </div>
      </div>
    </div>

    <div id="override-status" style="font-size:.85rem; color:#adbac7; margin-top:.5rem;"></div>
  </div>

  <!-- Tabla pantallas -->
  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
    <h2>Pantallas registradas</h2>
    <button class="btn btn-primary btn-sm" onclick="abrirNuevaPantalla()">+ Nueva pantalla</button>
  </div>
  <table id="tabla-pantallas">
    <thead><tr><th>ID</th><th>Nombre</th><th>Vista previa</th><th>Estado</th><th>URL de acceso</th><th></th></tr></thead>
    <tbody></tbody>
  </table>
</div>

<!-- SECCIÓN TÓTEMS -->
<div id="sec-totems" class="card" style="margin-bottom:1rem; display:none;">
  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
    <h2>Tótems / Puntos de check-in</h2>
    <button class="btn btn-primary btn-sm" onclick="abrirNuevoTotem()">+ Nuevo tótem</button>
  </div>
  <table id="tabla-totems">
    <thead><tr><th>ID</th><th>Salón</th><th>Nombre</th><th>Tipo</th><th>IP local</th><th>Señal</th><th>Estado</th><th></th></tr></thead>
    <tbody></tbody>
  </table>
</div>

<!-- SECCIÓN DEMO / RESET -->
<div id="sec-demo" class="card" style="margin-bottom:1rem; display:none;">
  <h2 style="margin-bottom:.6rem;">Zona de Demo / Reset</h2>
  <p style="color:var(--text-2); font-size:.85rem; margin-bottom:1rem;">
    Crea un evento ficticio completo para pruebas, demos o dry-run. 
    <strong style="color:var(--accent-red);">¡Esto borrará todos los datos actuales!</strong>
  </p>
  
  <div style="background:#1a1a2a; padding:1.2rem; border-radius:8px; border:1px solid #3a3a5a; margin-bottom:1rem;">
    <h3 style="margin:0 0 .8rem 0; font-size:1rem; color:var(--accent-red);">Datos que se crearán:</h3>
    <ul style="margin:0; padding-left:1.2rem; color:var(--text-2); font-size:.85rem; line-height:1.6;">
      <li><strong>Evento:</strong> Tech Summit 2026 (16-18 marzo 2026)</li>
      <li><strong>Salones:</strong> Auditorium Principal, Sala A, Sala B, Laboratorio Tech</li>
      <li><strong>Charlas:</strong> 23 charlas repartidas en 3 días</li>
      <li><strong>Asistentes:</strong> 50 registros de ejemplo</li>
      <li><strong>Tótems:</strong> 8 puntos de check-in</li>
      <li><strong>Operadores:</strong> Admin, Operador Demo, Recepción (PIN: 1234)</li>
    </ul>
  </div>

  <button class="btn btn-danger" onclick="resetDemo()" id="btn-reset-demo">
    🗑️ Resetear a Evento Demo
  </button>
  <div id="reset-demo-status" style="margin-top:.8rem; font-size:.85rem;"></div>
</div>

<!-- ═══ MODAL PANTALLAS ═══ -->
<div class="modal-bg" id="modal-pantalla">
  <div class="modal">
    <h3 id="pantalla-modal-titulo">Nueva pantalla</h3>
    <input type="hidden" id="pantalla-id">
    <label>Nombre <span style="color:var(--text-3)">(ej: "TV Sala A", "Monitor Lobby")</span></label>
    <input type="text" id="pantalla-nombre" placeholder="TV Sala A">

    <!-- Selector de modo -->
    <label style="margin-top:.8rem;">Modo de visualización</label>
    <div style="display:flex; gap:.4rem; margin-bottom:.8rem; flex-wrap:wrap;" id="pantalla-modo-btns">
      <button type="button" class="btn btn-sm" id="modo-btn-agenda"   onclick="seleccionarModo('agenda')"  >Agenda</button>
      <button type="button" class="btn btn-sm" id="modo-btn-imagen"   onclick="seleccionarModo('imagen')"  >Imagen</button>
      <button type="button" class="btn btn-sm" id="modo-btn-video"    onclick="seleccionarModo('video')"   >Video</button>
      <button type="button" class="btn btn-sm" id="modo-btn-apagada"  onclick="seleccionarModo('apagada')" >Apagada</button>
    </div>
    <input type="hidden" id="pantalla-modo" value="agenda">

    <!-- Salón (solo cuando modo=agenda) -->
    <div id="pantalla-salon-row">
      <label>Salón asignado <span style="color:var(--text-3)">(obligatorio para mostrar contenido)</span></label>
      <select id="pantalla-salon">
        <option value="">— Sin asignar —</option>
      </select>
    </div>

    <!-- Imagen (solo cuando modo=imagen) -->
    <div id="pantalla-imagen-row" style="display:none;">
      <label>Imagen (jpg/png/webp)</label>
      <div id="pantalla-img-actual-wrap" style="display:none; margin-bottom:.5rem;">
        <img id="pantalla-img-preview" style="max-width:180px; max-height:100px; border-radius:4px; object-fit:cover; border:1px solid #3a3a6a;">
      </div>
      <input type="file" id="pantalla-imagen-file" accept="image/*" onchange="previewPantallaImagen(this)">
    </div>

    <!-- Video (solo cuando modo=video) -->
    <div id="pantalla-video-row" style="display:none;">
      <label>Video (mp4 / webm)</label>
      <div id="pantalla-video-actual-wrap" style="display:none; margin-bottom:.5rem;">
        <video id="pantalla-video-preview" controls style="max-width:220px; max-height:120px; border-radius:4px; border:1px solid #3a3a6a;"></video>
      </div>
      <input type="file" id="pantalla-video-file" accept="video/mp4,video/webm" onchange="previewPantallaVideo(this)">
      <div style="margin-top:.6rem; display:flex; align-items:center; gap:.5rem;">
        <input type="checkbox" id="pantalla-loop" checked>
        <label for="pantalla-loop" style="margin:0; font-size:.85rem; cursor:pointer;">Reproducir en bucle (loop)</label>
      </div>
      <label style="margin-top:.6rem;">Ajuste en pantalla</label>
      <select id="pantalla-video-fit">
        <option value="contain">Completo — muestra todo el video (barras negras si hay diferencia de proporción)</option>
        <option value="cover">Rellenar pantalla — recorta si la proporción no coincide</option>
      </select>
    </div>

    <!-- Retorno automático (cuando modo != agenda) -->
    <div id="pantalla-retorno-row" style="display:none;">
      <label style="margin-top:.8rem;">Volver a agenda en</label>
      <select id="pantalla-retorno-select">
        <option value="0">Sin retorno automático</option>
        <option value="15">15 minutos</option>
        <option value="30">30 minutos</option>
        <option value="60">1 hora</option>
        <option value="120">2 horas</option>
      </select>
    </div>

    <div id="pantalla-activo-row" style="display:none; margin-top:.5rem;">
      <label>Estado</label>
      <select id="pantalla-activa">
        <option value="1">Activa</option>
        <option value="0">Inactiva</option>
      </select>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="cerrarModal('modal-pantalla')">Cancelar</button>
      <button class="btn btn-primary" onclick="guardarPantalla()">Guardar</button>
    </div>
  </div>
</div>

<!-- ═══ MODALES DÍAS ═══ -->
<div class="modal-bg" id="modal-dia">
  <div class="modal">
    <h3 id="dia-modal-titulo">Nuevo día</h3>
    <input type="hidden" id="dia-id">
    <label>Fecha</label>
    <input type="date" id="dia-fecha">
    <label>Nombre / etiqueta <span style="color:var(--text-3)">(opcional, ej: "Día 1 — Apertura")</span></label>
    <input type="text" id="dia-nombre" placeholder="Día 1 — Apertura">
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="cerrarModal('modal-dia')">Cancelar</button>
      <button class="btn btn-primary" onclick="guardarDia()">Guardar</button>
    </div>
  </div>
</div>

<!-- ═══ MODALES SALONES ═══ -->
<div class="modal-bg" id="modal-salon">
  <div class="modal">
    <h3 id="salon-modal-titulo">Nuevo salón</h3>
    <input type="hidden" id="salon-id">
    <label>Nombre</label>
    <input type="text" id="salon-nombre" placeholder="Salón A">
    <label>Capacidad <span style="color:var(--text-3)">(opcional)</span></label>
    <input type="number" id="salon-capacidad" placeholder="200" min="1">
    <div id="salon-activo-row" style="display:none;">
      <label>Estado</label>
      <select id="salon-activo">
        <option value="1">Activo</option>
        <option value="0">Inactivo</option>
      </select>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="cerrarModal('modal-salon')">Cancelar</button>
      <button class="btn btn-primary" onclick="guardarSalon()">Guardar</button>
    </div>
  </div>
</div>

<!-- ═══ MODALES TÓTEMS ═══ -->
<div class="modal-bg" id="modal-totem">
  <div class="modal">
    <h3 id="totem-modal-titulo">Nuevo tótem</h3>
    <input type="hidden" id="totem-id">
    <label>Salón</label>
    <select id="totem-salon"></select>
    <label>Nombre <span style="color:var(--text-3)">(ej: "Salón A – Entrada")</span></label>
    <input type="text" id="totem-nombre" placeholder="Salón A – Entrada">
    <label>Tipo</label>
    <select id="totem-tipo">
      <option value="bidireccional">Bidireccional (entrada y salida)</option>
      <option value="entrada">Solo entrada</option>
      <option value="salida">Solo salida</option>
    </select>
    <label>IP local <span style="color:var(--text-3)">(opcional, para diagnóstico)</span></label>
    <input type="text" id="totem-ip" placeholder="192.168.1.100">
    <div id="totem-activo-row" style="display:none;">
      <label>Estado</label>
      <select id="totem-activo">
        <option value="1">Activo</option>
        <option value="0">Inactivo</option>
      </select>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="cerrarModal('modal-totem')">Cancelar</button>
      <button class="btn btn-primary" onclick="guardarTotem()">Guardar</button>
    </div>
  </div>
</div>

<script>
let datos = { dias: [], salones: [], totems: [], pantallas: [], agenda_override: 'none', override_imagen: null, override_video: null, override_loop_video: true, override_retorno_en: null };

async function cargar() {
  const r = await fetch(API_BASE + '/configuracion');
  datos = await r.json();
  renderDias();
  renderSalones();
  renderTotems();
  renderPantallas();
  renderOverride();
}

// ─── TABS ────────────────────────────────────────────────
let _tabActual = 'dias';
function mostrarTab(tab) {
  ['dias','salones','totems','pantallas','demo'].forEach(t => {
    document.getElementById('sec-' + t).style.display  = t === tab ? '' : 'none';
    const btn = document.getElementById('tab-' + t);
    btn.classList.toggle('tab-active', t === tab);
  });
  _tabActual = tab;
}
mostrarTab('dias');

// ─── RENDER DÍAS ─────────────────────────────────────────
function renderDias() {
  document.querySelector('#tabla-dias tbody').innerHTML = datos.dias.map(d => `
    <tr>
      <td style="color:var(--text-2)">${d.orden}</td>
      <td style="font-family:monospace">${d.fecha}</td>
      <td>${esc(d.nombre || '—')}</td>
      <td style="white-space:nowrap">
        <button class="btn btn-ghost btn-sm" onclick="abrirEditarDia(${d.id})">Editar</button>
        <button class="btn btn-danger  btn-sm" onclick="eliminarDia(${d.id})">Eliminar</button>
      </td>
    </tr>
  `).join('') || '<tr><td colspan="4" style="color:var(--text-3);padding:1rem">Sin días registrados</td></tr>';
}

// ─── RENDER SALONES ───────────────────────────────────────
function renderSalones() {
  const activos    = datos.salones.filter(s =>  s.activo);
  const archivados = datos.salones.filter(s => !s.activo);

  const filaActivo = s => `
    <tr>
      <td style="color:var(--text-2)">${s.id}</td>
      <td style="font-weight:500">${esc(s.nombre)}</td>
      <td>${s.capacidad ?? '—'}</td>
      <td><span class="badge badge-verde">Activo</span></td>
      <td style="white-space:nowrap">
        <button class="btn btn-ghost btn-sm" onclick="abrirEditarSalon(${s.id})">Editar</button>
        <button class="btn btn-danger btn-sm" onclick="archivarSalon(${s.id})">Archivar</button>
      </td>
    </tr>`;

  const filaArchivado = s => `
    <tr style="opacity:.45;">
      <td style="color:var(--text-2)">${s.id}</td>
      <td>${esc(s.nombre)}</td>
      <td>${s.capacidad ?? '—'}</td>
      <td><span class="badge badge-gris">Archivado</span></td>
      <td style="white-space:nowrap">
        <button class="btn btn-ghost btn-sm" onclick="restaurarSalon(${s.id})">Restaurar</button>
      </td>
    </tr>`;

  let html = activos.map(filaActivo).join('');

  if (archivados.length) {
    html += `
      <tr id="fila-toggle-archivados">
        <td colspan="5" style="padding:.5rem 0;">
          <button class="btn btn-ghost btn-sm" style="font-size:.78rem;color:var(--text-3);"
                  onclick="toggleArchivados()">
            <span id="toggle-archivados-label">▶ Ver ${archivados.length} salón${archivados.length>1?'es':''} archivado${archivados.length>1?'s':''}</span>
          </button>
        </td>
      </tr>
      <tbody id="tbody-archivados" style="display:none;">
        ${archivados.map(filaArchivado).join('')}
      </tbody>`;
  }

  const tbody = document.querySelector('#tabla-salones tbody');
  tbody.innerHTML = html || '<tr><td colspan="5" style="color:var(--text-3);padding:1rem">Sin salones registrados</td></tr>';
}

function toggleArchivados() {
  const tbody = document.getElementById('tbody-archivados');
  const label = document.getElementById('toggle-archivados-label');
  if (!tbody) return;
  const visible = tbody.style.display !== 'none';
  tbody.style.display = visible ? 'none' : '';
  const n = tbody.querySelectorAll('tr').length;
  label.textContent = (visible ? '▶' : '▼') + ` Ver ${n} salón${n>1?'es':''} archivado${n>1?'s':''}`;
}

// ─── RENDER TÓTEMS ────────────────────────────────────────
const TIPO_LABEL = { entrada:'Solo entrada', salida:'Solo salida', bidireccional:'Bidireccional' };

function heartbeatBadge(seg) {
  if (seg === null || seg === undefined || seg === '')
    return '<span class="badge badge-gris">Sin señal</span>';
  seg = parseInt(seg);
  if (seg < 90)
    return '<span class="badge badge-verde"><span class="pulse-dot" style="margin-right:.35rem"></span>Online</span>';
  if (seg < 300)
    return '<span class="badge badge-naranja">Hace ' + Math.round(seg/60) + ' min</span>';
  return '<span class="badge badge-rojo">Offline</span>';
}

function renderTotems() {
  document.querySelector('#tabla-totems tbody').innerHTML = datos.totems.map(t => `
    <tr>
      <td style="color:var(--text-2)">${t.id}</td>
      <td>${esc(t.salon_nombre)}</td>
      <td style="font-weight:500">${esc(t.nombre)}</td>
      <td><span class="badge badge-azul">${TIPO_LABEL[t.tipo] || t.tipo}</span></td>
      <td style="font-family:monospace;font-size:.8rem">${esc(t.ip_local || '—')}</td>
      <td>${heartbeatBadge(t.segundos_desde_ping)}</td>
      <td><span class="badge ${t.activo ? 'badge-verde' : 'badge-gris'}">${t.activo ? 'Activo' : 'Inactivo'}</span></td>
      <td style="white-space:nowrap">
        <button class="btn btn-ghost btn-sm" onclick="abrirEditarTotem(${t.id})">Editar</button>
        <button class="btn btn-danger btn-sm"  onclick="eliminarTotem(${t.id})">Eliminar</button>
      </td>
    </tr>
  `).join('') || '<tr><td colspan="8" style="color:var(--text-3);padding:1rem">Sin tótems registrados</td></tr>';
}

// ─── DÍAS ─────────────────────────────────────────────────
function abrirNuevoDia() {
  document.getElementById('dia-modal-titulo').textContent = 'Nuevo día';
  document.getElementById('dia-id').value    = '';
  document.getElementById('dia-fecha').value = '';
  document.getElementById('dia-nombre').value = '';
  abrirModal('modal-dia');
}

function abrirEditarDia(id) {
  const d = datos.dias.find(x => x.id == id);
  if (!d) return;
  document.getElementById('dia-modal-titulo').textContent = 'Editar día';
  document.getElementById('dia-id').value    = d.id;
  document.getElementById('dia-fecha').value = d.fecha;
  document.getElementById('dia-nombre').value = d.nombre || '';
  abrirModal('modal-dia');
}

async function guardarDia() {
  const id    = document.getElementById('dia-id').value;
  const body  = {
    fecha:  document.getElementById('dia-fecha').value,
    nombre: document.getElementById('dia-nombre').value.trim(),
  };
  if (!body.fecha) { alert('La fecha es obligatoria.'); return; }

  const url    = id ? `${API_BASE}/dia/${id}` : `${API_BASE}/dia`;
  const method = id ? 'PATCH' : 'POST';
  const r = await fetch(url, { method, headers: {'Content-Type':'application/json'}, body: JSON.stringify(body) });
  const d = await r.json();
  if (d.ok) { cerrarModal('modal-dia'); cargar(); }
  else alert('Error: ' + (d.mensaje || JSON.stringify(d)));
}

async function eliminarDia(id) {
  const dia = datos.dias.find(x => x.id == id);
  if (!confirm(`¿Eliminar el día "${dia?.fecha || id}"?\nSolo es posible si no tiene charlas.`)) return;
  const r    = await fetch(`${API_BASE}/dia/${id}`, { method: 'DELETE' });
  const resp = await r.json();
  if (resp.ok) cargar();
  else alert('No se puede eliminar: ' + (resp.mensaje || JSON.stringify(resp)));
}

// ─── SALONES ──────────────────────────────────────────────
function abrirNuevoSalon() {
  document.getElementById('salon-modal-titulo').textContent = 'Nuevo salón';
  document.getElementById('salon-id').value        = '';
  document.getElementById('salon-nombre').value    = '';
  document.getElementById('salon-capacidad').value = '';
  document.getElementById('salon-activo-row').style.display = 'none';
  abrirModal('modal-salon');
}

function abrirEditarSalon(id) {
  const s = datos.salones.find(x => x.id == id);
  if (!s) return;
  document.getElementById('salon-modal-titulo').textContent = 'Editar salón';
  document.getElementById('salon-id').value        = s.id;
  document.getElementById('salon-nombre').value    = s.nombre;
  document.getElementById('salon-capacidad').value = s.capacidad ?? '';
  document.getElementById('salon-activo').value    = s.activo;
  document.getElementById('salon-activo-row').style.display = '';
  abrirModal('modal-salon');
}

async function guardarSalon() {
  const id   = document.getElementById('salon-id').value;
  const cap  = document.getElementById('salon-capacidad').value;
  const body = {
    nombre:    document.getElementById('salon-nombre').value.trim(),
    capacidad: cap !== '' ? parseInt(cap) : null,
  };
  if (id) body.activo = parseInt(document.getElementById('salon-activo').value);
  if (!body.nombre) { alert('El nombre es obligatorio.'); return; }

  const url    = id ? `${API_BASE}/salon/${id}` : `${API_BASE}/salon`;
  const method = id ? 'PATCH' : 'POST';
  const r = await fetch(url, { method, headers: {'Content-Type':'application/json'}, body: JSON.stringify(body) });
  const d = await r.json();
  if (d.ok) { cerrarModal('modal-salon'); cargar(); }
  else alert('Error: ' + (d.mensaje || JSON.stringify(d)));
}

async function archivarSalon(id) {
  const s = datos.salones.find(x => x.id == id);
  if (!confirm(`¿Archivar el salón "${s?.nombre || id}"?\nEl historial se conserva. Puedes restaurarlo después.`)) return;
  const r = await fetch(`${API_BASE}/salon/${id}`, { method: 'DELETE' });
  const d = await r.json();
  if (d.ok) cargar();
  else alert('No se puede archivar: ' + (d.mensaje || JSON.stringify(d)));
}

async function restaurarSalon(id) {
  const s = datos.salones.find(x => x.id == id);
  if (!confirm(`¿Restaurar el salón "${s?.nombre || id}"?`)) return;
  const r = await fetch(`${API_BASE}/salon/${id}`, {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ activo: 1 }),
  });
  const d = await r.json();
  if (d.ok) cargar();
  else alert('Error: ' + (d.mensaje || JSON.stringify(d)));
}

// ─── TÓTEMS ───────────────────────────────────────────────
function abrirNuevoTotem() {
  document.getElementById('totem-modal-titulo').textContent = 'Nuevo tótem';
  document.getElementById('totem-id').value    = '';
  document.getElementById('totem-nombre').value = '';
  document.getElementById('totem-tipo').value  = 'bidireccional';
  document.getElementById('totem-ip').value    = '';
  document.getElementById('totem-activo-row').style.display = 'none';
  document.getElementById('totem-salon').innerHTML = datos.salones
    .filter(s => s.activo)
    .map(s => `<option value="${s.id}">${esc(s.nombre)}</option>`)
    .join('');
  abrirModal('modal-totem');
}

function abrirEditarTotem(id) {
  const t = datos.totems.find(x => x.id == id);
  if (!t) return;
  document.getElementById('totem-modal-titulo').textContent = 'Editar tótem';
  document.getElementById('totem-id').value    = t.id;
  document.getElementById('totem-nombre').value = t.nombre;
  document.getElementById('totem-tipo').value  = t.tipo;
  document.getElementById('totem-ip').value    = t.ip_local || '';
  document.getElementById('totem-activo').value = t.activo;
  document.getElementById('totem-activo-row').style.display = '';
  document.getElementById('totem-salon').innerHTML = datos.salones
    .map(s => `<option value="${s.id}" ${s.id == t.salon_id ? 'selected' : ''}>${esc(s.nombre)}${s.activo ? '' : ' (inactivo)'}</option>`)
    .join('');
  abrirModal('modal-totem');
}

async function guardarTotem() {
  const id   = document.getElementById('totem-id').value;
  const body = {
    salon_id: parseInt(document.getElementById('totem-salon').value),
    nombre:   document.getElementById('totem-nombre').value.trim(),
    tipo:     document.getElementById('totem-tipo').value,
    ip_local: document.getElementById('totem-ip').value.trim() || null,
  };
  if (id) body.activo = parseInt(document.getElementById('totem-activo').value);
  if (!body.nombre) { alert('El nombre es obligatorio.'); return; }

  const url    = id ? `${API_BASE}/totem/${id}` : `${API_BASE}/totem`;
  const method = id ? 'PATCH' : 'POST';
  const r = await fetch(url, { method, headers: {'Content-Type':'application/json'}, body: JSON.stringify(body) });
  const d = await r.json();
  if (d.ok) {
    if (d.advertencia) alert('Aviso: ' + d.advertencia);
    cerrarModal('modal-totem');
    cargar();
  } else alert('Error: ' + (d.mensaje || JSON.stringify(d)));
}

async function eliminarTotem(id) {
  const t = datos.totems.find(x => x.id == id);
  if (!confirm(`¿Eliminar el tótem "${t?.nombre || id}"?\nSi tiene movimientos registrados, solo se desactivará.`)) return;
  const r = await fetch(`${API_BASE}/totem/${id}`, { method: 'DELETE' });
  const d = await r.json();
  if (d.ok) {
    if (d.advertencia) alert('Aviso: ' + d.advertencia);
    cargar();
  } else alert('Error: ' + (d.mensaje || JSON.stringify(d)));
}

// ─── PANTALLAS ────────────────────────────────────────────
function agendaUrl(screenId) {
  return window.location.href.replace(/\/admin.*$/, '/agenda/') + '?screen=' + screenId;
}

// URL base para imágenes de pantalla (uploads/pantallas/...)
function pantallaImgUrl(id, filename) {
  if (!filename) return null;
  return window.location.href.replace(/\/admin.*$/, '/uploads/pantallas/' + id + '/' + filename);
}

function overrideImgUrl(filename) {
  if (!filename) return null;
  return window.location.href.replace(/\/admin.*$/, '/uploads/pantallas/override/' + filename);
}

function retornoBadge(retorno_en) {
  if (!retorno_en) return '';
  const dt = new Date(retorno_en.replace(' ', 'T'));
  const mins = Math.max(0, Math.round((dt - Date.now()) / 60000));
  return `<span class="badge badge-naranja" style="font-size:.72rem;margin-left:.3rem">↩ ${mins > 0 ? 'en ' + mins + 'min' : 'ahora'}</span>`;
}

function renderPantallas() {
  const tbody = document.querySelector('#tabla-pantallas tbody');
  if (!datos.pantallas?.length) {
    tbody.innerHTML = '<tr><td colspan="6" style="color:var(--text-3);padding:1rem">Sin pantallas registradas</td></tr>';
    return;
  }
  tbody.innerHTML = datos.pantallas.map(p => {
    const url = agendaUrl(p.id);
    const modo = p.modo || 'agenda';

    let vistaPrevia = '';
    if (modo === 'imagen' && p.imagen_path) {
      const imgSrc = pantallaImgUrl(p.id, p.imagen_path);
      vistaPrevia = `<img src="${esc(imgSrc)}" style="width:52px;height:32px;object-fit:cover;border-radius:3px;vertical-align:middle;border:1px solid #3a3a6a;margin-right:.4rem">
                     <span class="badge badge-azul">Imagen</span>`;
    } else if (modo === 'video') {
      const loopLabel = parseInt(p.loop_video) !== 0 ? '↻ Loop' : '→ 1×';
      vistaPrevia = `<span class="badge badge-azul" style="font-size:.75rem;">▶ Video</span>
                     <span class="badge badge-gris" style="font-size:.72rem;margin-left:.3rem">${loopLabel}</span>`;
    } else if (modo === 'apagada') {
      vistaPrevia = `<span class="badge badge-gris">Apagada</span>`;
    } else {
      const salonLabel = p.salon_nombre ? esc(p.salon_nombre) : '<span style="color:var(--text-3)">Sin asignar</span>';
      vistaPrevia = `<span class="badge badge-verde">${salonLabel}</span>`;
    }

    const retorno = retornoBadge(p.retorno_en);

    return `
    <tr>
      <td style="color:var(--text-2)">${p.id}</td>
      <td style="font-weight:500">${esc(p.nombre)}</td>
      <td style="white-space:nowrap">${vistaPrevia}${retorno}</td>
      <td><span class="badge ${p.activa ? 'badge-verde' : 'badge-gris'}">${p.activa ? 'Activa' : 'Inactiva'}</span></td>
      <td style="font-family:monospace;font-size:.78rem;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
        <a href="${esc(url)}" target="_blank" style="color:var(--accent)">${esc(url)}</a>
      </td>
      <td style="white-space:nowrap">
        <button class="btn btn-ghost btn-sm" onclick="abrirEditarPantalla(${p.id})">Editar</button>
        <button class="btn btn-danger btn-sm" onclick="eliminarPantalla(${p.id})">Eliminar</button>
      </td>
    </tr>`;
  }).join('');
}

function renderOverride() {
  const ov      = datos.agenda_override || 'none';
  const salones = (datos.salones || []).filter(s => s.activo);

  let btns = '';
  btns += `<button class="btn ${ov==='none'?'btn-primary':'btn-ghost'} btn-sm" onclick="setOverride('none')">Normal</button>`;
  for (const s of salones) {
    btns += `<button class="btn ${String(ov)===String(s.id)?'btn-primary':'btn-ghost'} btn-sm" onclick="setOverride('${s.id}')">Todas → ${esc(s.nombre)}</button>`;
  }
  btns += `<button class="btn ${ov==='imagen'?'btn-primary':'btn-ghost'} btn-sm" onclick="toggleOverridePanel('imagen')">Imagen global</button>`;
  btns += `<button class="btn ${ov==='video'?'btn-primary':'btn-ghost'} btn-sm" onclick="toggleOverridePanel('video')">Video global</button>`;
  btns += `<button class="btn ${ov==='off'?'btn-danger':'btn-ghost'} btn-sm" onclick="setOverride('off')">Apagar todas</button>`;
  document.getElementById('override-btns').innerHTML = btns;

  // Mostrar/ocultar paneles de media según override actual
  document.getElementById('override-imagen-panel').style.display = ov === 'imagen' ? '' : 'none';
  document.getElementById('override-video-panel').style.display  = ov === 'video'  ? '' : 'none';

  let status = '';
  if (ov === 'none')        status = 'Cada pantalla muestra su salón configurado individualmente.';
  else if (ov === 'off')    status = '⚫ Todas las pantallas están apagadas (pantalla en negro).';
  else if (ov === 'imagen') {
    const imgUrl = overrideImgUrl(datos.override_imagen);
    const thumb  = imgUrl ? `<img src="${esc(imgUrl)}" style="width:40px;height:25px;object-fit:cover;border-radius:3px;vertical-align:middle;margin-right:.4rem;border:1px solid #3a3a6a;">` : '';
    const retorno = retornoBadge(datos.override_retorno_en);
    status = `${thumb}🖼️ Todas las pantallas muestran una imagen estática.${retorno}`;
  } else if (ov === 'video') {
    const loopLabel = datos.override_loop_video ? '↻ Loop' : '→ 1×';
    const retorno = retornoBadge(datos.override_retorno_en);
    status = `▶ Todas las pantallas reproducen un video. <span class="badge badge-gris" style="font-size:.75rem">${loopLabel}</span>${retorno}`;
  } else {
    const s = salones.find(x => String(x.id) === String(ov));
    status = s
      ? `🟢 Todas las pantallas muestran: <strong>${esc(s.nombre)}</strong>`
      : `Override activo: salón ID ${esc(ov)}`;
  }
  document.getElementById('override-status').innerHTML = status;
}

function toggleOverridePanel(tipo) {
  const panelImagen = document.getElementById('override-imagen-panel');
  const panelVideo  = document.getElementById('override-video-panel');
  if (tipo === 'imagen') {
    const visible = panelImagen.style.display !== 'none';
    panelImagen.style.display = visible ? 'none' : '';
    panelVideo.style.display  = 'none';
  } else {
    const visible = panelVideo.style.display !== 'none';
    panelVideo.style.display  = visible ? 'none' : '';
    panelImagen.style.display = 'none';
  }
}

function previewOverrideImagen(input) {
  const wrap = document.getElementById('override-img-preview-wrap');
  const img  = document.getElementById('override-img-preview');
  if (input.files && input.files[0]) {
    img.src = URL.createObjectURL(input.files[0]);
    wrap.style.display = '';
  }
}

function previewOverrideVideo(input) {
  const wrap  = document.getElementById('override-video-preview-wrap');
  const video = document.getElementById('override-video-preview');
  if (input.files && input.files[0]) {
    video.src = URL.createObjectURL(input.files[0]);
    wrap.style.display = '';
  }
}

async function subirOverrideVideo() {
  const fileEl = document.getElementById('override-video-file');
  if (!fileEl.files || !fileEl.files[0]) { alert('Selecciona un video primero.'); return; }

  const fd = new FormData();
  fd.append('video', fileEl.files[0]);
  fd.append('loop_video',       document.getElementById('override-loop-check').checked ? '1' : '0');
  fd.append('video_fit',        document.getElementById('override-video-fit-select').value);
  fd.append('retorno_minutos',  document.getElementById('override-video-retorno-select').value);

  try {
    const r = await fetch(API_BASE + '/agenda-override-video', { method: 'POST', body: fd });
    const d = await r.json();
    if (d.ok) {
      await cargar();
      document.getElementById('override-status').innerHTML =
        '<span style="color:#3fa86e">✓ Video global aplicado</span>';
      setTimeout(() => renderOverride(), 1200);
    } else {
      alert('Error: ' + (d.error || d.mensaje || JSON.stringify(d)));
    }
  } catch (e) {
    alert('Error al subir: ' + e.message);
  }
}

async function subirOverrideImagen() {
  const fileEl = document.getElementById('override-imagen-file');
  if (!fileEl.files || !fileEl.files[0]) { alert('Selecciona una imagen primero.'); return; }

  const fd = new FormData();
  fd.append('imagen', fileEl.files[0]);
  fd.append('retorno_minutos', document.getElementById('override-retorno-select').value);

  try {
    const r = await fetch(API_BASE + '/agenda-override-imagen', { method: 'POST', body: fd });
    const d = await r.json();
    if (d.ok) {
      await cargar();
      document.getElementById('override-status').innerHTML =
        '<span style="color:#3fa86e">✓ Imagen global aplicada</span>';
      setTimeout(() => renderOverride(), 1200);
    } else {
      alert('Error: ' + (d.error || d.mensaje || JSON.stringify(d)));
    }
  } catch (e) {
    alert('Error al subir: ' + e.message);
  }
}

async function setOverride(valor) {
  try {
    const r = await fetch(API_BASE + '/agenda-override', {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ override: String(valor) }),
    });
    const d = await r.json();
    if (d.ok) {
      datos.agenda_override = d.override;
      renderOverride();
      const el = document.getElementById('override-status');
      if (el) {
        const prev = el.innerHTML;
        el.innerHTML = '<span style="color:#3fa86e">✓ Guardado</span>';
        setTimeout(() => { el.innerHTML = prev; renderOverride(); }, 800);
      }
    } else {
      alert('Error: ' + (d.error || d.mensaje || JSON.stringify(d)));
    }
  } catch (e) {
    alert('Error al guardar: ' + e.message + '\n\n¿Ejecutaste setup/migrate_agenda_pantallas.sql?');
  }
}

// ─── Modal pantalla: selector de modo ─────────────────────
function seleccionarModo(modo) {
  document.getElementById('pantalla-modo').value = modo;

  ['agenda','imagen','video','apagada'].forEach(m => {
    const btn = document.getElementById('modo-btn-' + m);
    if (btn) btn.className = 'btn btn-sm ' + (m === modo ? 'btn-primary' : 'btn-ghost');
  });

  document.getElementById('pantalla-salon-row').style.display   = modo === 'agenda' ? '' : 'none';
  document.getElementById('pantalla-imagen-row').style.display  = modo === 'imagen' ? '' : 'none';
  document.getElementById('pantalla-video-row').style.display   = modo === 'video'  ? '' : 'none';
  document.getElementById('pantalla-retorno-row').style.display = modo !== 'agenda' ? '' : 'none';
}

function previewPantallaImagen(input) {
  const wrap = document.getElementById('pantalla-img-actual-wrap');
  const img  = document.getElementById('pantalla-img-preview');
  if (input.files && input.files[0]) {
    img.src = URL.createObjectURL(input.files[0]);
    wrap.style.display = '';
  }
}

function previewPantallaVideo(input) {
  const wrap  = document.getElementById('pantalla-video-actual-wrap');
  const video = document.getElementById('pantalla-video-preview');
  if (input.files && input.files[0]) {
    video.src = URL.createObjectURL(input.files[0]);
    wrap.style.display = '';
  }
}

function abrirNuevaPantalla() {
  document.getElementById('pantalla-modal-titulo').textContent = 'Nueva pantalla';
  document.getElementById('pantalla-id').value     = '';
  document.getElementById('pantalla-nombre').value = '';
  document.getElementById('pantalla-activo-row').style.display = 'none';
  document.getElementById('pantalla-imagen-file').value = '';
  document.getElementById('pantalla-video-file').value  = '';
  document.getElementById('pantalla-img-actual-wrap').style.display   = 'none';
  document.getElementById('pantalla-video-actual-wrap').style.display = 'none';
  document.getElementById('pantalla-retorno-select').value = '0';
  document.getElementById('pantalla-loop').checked = true;
  document.getElementById('pantalla-video-fit').value = 'contain';
  const sel = document.getElementById('pantalla-salon');
  sel.innerHTML = '<option value="">— Sin asignar —</option>'
    + datos.salones.filter(s => s.activo)
      .map(s => `<option value="${s.id}">${esc(s.nombre)}</option>`).join('');
  seleccionarModo('agenda');
  abrirModal('modal-pantalla');
}

function abrirEditarPantalla(id) {
  const p = datos.pantallas.find(x => x.id == id);
  if (!p) return;
  const modo = p.modo || 'agenda';

  document.getElementById('pantalla-modal-titulo').textContent = 'Editar pantalla';
  document.getElementById('pantalla-id').value     = p.id;
  document.getElementById('pantalla-nombre').value = p.nombre;
  document.getElementById('pantalla-activa').value = p.activa;
  document.getElementById('pantalla-activo-row').style.display = '';
  document.getElementById('pantalla-imagen-file').value = '';
  document.getElementById('pantalla-video-file').value  = '';
  document.getElementById('pantalla-retorno-select').value = '0';
  document.getElementById('pantalla-loop').checked = parseInt(p.loop_video) !== 0;
  document.getElementById('pantalla-video-fit').value = p.video_fit || 'contain';

  const sel = document.getElementById('pantalla-salon');
  sel.innerHTML = '<option value="">— Sin asignar —</option>'
    + datos.salones
      .map(s => `<option value="${s.id}" ${s.id == p.salon_id ? 'selected' : ''}>${esc(s.nombre)}${s.activo?'':' (inactivo)'}</option>`).join('');

  // Imagen actual
  const wrapImg = document.getElementById('pantalla-img-actual-wrap');
  const img     = document.getElementById('pantalla-img-preview');
  if (modo === 'imagen' && p.imagen_path) {
    img.src = pantallaImgUrl(p.id, p.imagen_path);
    wrapImg.style.display = '';
  } else { wrapImg.style.display = 'none'; }

  // Video actual
  const wrapVid = document.getElementById('pantalla-video-actual-wrap');
  const vid     = document.getElementById('pantalla-video-preview');
  if (modo === 'video' && p.video_path) {
    vid.src = pantallaImgUrl(p.id, p.video_path); // misma ruta base
    wrapVid.style.display = '';
  } else { wrapVid.style.display = 'none'; }

  seleccionarModo(modo);
  abrirModal('modal-pantalla');
}

async function guardarPantalla() {
  const id      = document.getElementById('pantalla-id').value;
  const nombre  = document.getElementById('pantalla-nombre').value.trim();
  const modo    = document.getElementById('pantalla-modo').value;
  const salonEl = document.getElementById('pantalla-salon');
  const salon_id = (modo === 'agenda' && salonEl.value !== '') ? parseInt(salonEl.value) : null;
  const retorno_minutos = parseInt(document.getElementById('pantalla-retorno-select').value) || 0;
  if (!nombre) { alert('El nombre es obligatorio.'); return; }

  const loop_video = document.getElementById('pantalla-loop').checked ? 1 : 0;
  const video_fit  = document.getElementById('pantalla-video-fit').value;
  const body = { nombre, salon_id, modo, loop_video, video_fit, retorno_minutos: retorno_minutos > 0 ? retorno_minutos : null };
  if (id) body.activa = parseInt(document.getElementById('pantalla-activa').value);

  const url    = id ? `${API_BASE}/pantalla/${id}` : `${API_BASE}/pantalla`;
  const method = id ? 'PATCH' : 'POST';
  try {
    const r = await fetch(url, { method, headers: {'Content-Type':'application/json'}, body: JSON.stringify(body) });
    const d = await r.json();
    if (!d.ok) { alert('Error: ' + (d.error || d.mensaje || JSON.stringify(d))); return; }

    const savedId = id || d.id;

    // Subir imagen si modo=imagen y hay archivo
    const imgEl = document.getElementById('pantalla-imagen-file');
    if (savedId && modo === 'imagen' && imgEl.files && imgEl.files[0]) {
      const fd = new FormData();
      fd.append('imagen', imgEl.files[0]);
      const ri = await fetch(`${API_BASE}/pantalla/${savedId}/imagen`, { method: 'POST', body: fd });
      const di = await ri.json();
      if (!di.ok) alert('Datos guardados pero error al subir imagen: ' + (di.error || di.mensaje));
    }

    // Subir video si modo=video y hay archivo
    const vidEl = document.getElementById('pantalla-video-file');
    if (savedId && modo === 'video' && vidEl.files && vidEl.files[0]) {
      const fd = new FormData();
      fd.append('video', vidEl.files[0]);
      const rv = await fetch(`${API_BASE}/pantalla/${savedId}/video`, { method: 'POST', body: fd });
      const dv = await rv.json();
      if (!dv.ok) alert('Datos guardados pero error al subir video: ' + (dv.error || dv.mensaje));
    }

    cerrarModal('modal-pantalla');
    cargar();
  } catch (e) {
    alert('Error: ' + e.message + '\n\n¿Ejecutaste setup/migrate_agenda_pantallas.sql?');
  }
}

async function eliminarPantalla(id) {
  const p = datos.pantallas.find(x => x.id == id);
  if (!confirm(`¿Eliminar la pantalla "${p?.nombre || id}"?`)) return;
  const r = await fetch(`${API_BASE}/pantalla/${id}`, { method: 'DELETE' });
  const d = await r.json();
  if (d.ok) cargar();
  else alert('Error: ' + (d.mensaje || JSON.stringify(d)));
}

// ─── HELPERS ─────────────────────────────────────────────
function abrirModal(id)  { document.getElementById(id).classList.add('open'); }
function cerrarModal(id) { document.getElementById(id).classList.remove('open'); }
function esc(s) { const d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }

cargar();

// Auto-refresh heartbeat cada 30s (solo cuando la pestaña tótems está activa)
setInterval(async () => {
  if (_tabActual !== 'totems') return;
  const r = await fetch(API_BASE + '/configuracion');
  const d = await r.json();
  datos.totems = d.totems;
  renderTotems();
}, 30000);

// ─── RESET DEMO ───────────────────────────────────────────
async function resetDemo() {
  const msg = '¿Estás seguro?\n\nEsto BORRARÁ todos los datos actuales del evento y creará un evento demo ficticio (Tech Summit 2026, 30 mar - 1 abr).\n\nEscribe "RESET" para confirmar:';
  const confirmar = prompt(msg);
  if (confirmar !== 'RESET') {
    if (confirmar !== null) alert('Operación cancelada. Debes escribir exactamente "RESET".');
    return;
  }

  const btn = document.getElementById('btn-reset-demo');
  const status = document.getElementById('reset-demo-status');
  btn.disabled = true;
  btn.textContent = '⏳ Ejecutando reset...';
  status.innerHTML = '<span style="color:#f0a060">Reiniciando base de datos...</span>';

  try {
    const r = await fetch(API_BASE + '/reset-demo', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ confirmado: true }),
    });
    const d = await r.json();

    if (d.ok) {
      status.innerHTML = '<span style="color:#4ade80">✓ ' + d.mensaje + '</span><br><span style="color:var(--text-2)">' + d.detalle + '</span>';
      setTimeout(() => {
        alert('✓ Evento demo creado correctamente. Serás redirigido al login.');
        window.location.href = window.location.href.replace('/configuracion', '/login');
      }, 800);
    } else {
      status.innerHTML = '<span style="color:#f87171">✗ Error: ' + (d.error || d.mensaje || JSON.stringify(d)) + '</span>';
      btn.disabled = false;
      btn.textContent = '🗑️ Resetear a Evento Demo';
    }
  } catch (e) {
    status.innerHTML = '<span style="color:#f87171">✗ Error de conexión: ' + e.message + '</span>';
    btn.disabled = false;
    btn.textContent = '🗑️ Resetear a Evento Demo';
  }
}
</script>

</main></body></html>
