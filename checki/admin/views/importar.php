<?php $titulo = 'Importar'; $seccion = 'importar'; include __DIR__ . '/_layout.php'; ?>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem; flex-wrap:wrap; gap:.8rem;">
  <h1 style="font-size:1.1rem; color:var(--text-2);">Importar datos</h1>
  <?php
    preg_match('#^(.*?/admin)#', $_SERVER['REQUEST_URI'], $_mi);
    $__api = ($_mi[1] ?? '/admin') . '/api';
  ?>
  <div style="display:flex; gap:.6rem;">
    <a href="<?= $__api ?>/plantillas?tipo=asistentes" class="btn btn-ghost btn-sm">Plantilla asistentes</a>
    <a href="<?= $__api ?>/plantillas?tipo=agenda"     class="btn btn-ghost btn-sm">Plantilla agenda</a>
  </div>
</div>

<!-- PASO 1: SELECCIÓN -->
<div id="paso-1">
  <div class="card" style="max-width:640px;">
    <h2>Paso 1 — Seleccionar archivo</h2>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.2rem;">
      <div>
        <label style="display:block;font-size:.8rem;color:#6060a0;margin-bottom:.4rem;">Tipo de datos</label>
        <select id="tipo" style="width:100%;padding:.6rem .8rem;background:#0f0f1a;border:1px solid #2a2a4a;border-radius:6px;color:#e0e0e0;">
          <option value="asistentes">Asistentes</option>
          <option value="agenda">Agenda</option>
        </select>
      </div>
      <div>
        <label style="display:block;font-size:.8rem;color:#6060a0;margin-bottom:.4rem;">Formato</label>
        <select id="fuente" style="width:100%;padding:.6rem .8rem;background:#0f0f1a;border:1px solid #2a2a4a;border-radius:6px;color:#e0e0e0;">
          <option value="csv">CSV / Excel</option>
          <option value="json">JSON</option>
        </select>
      </div>
    </div>

    <!-- Solo para agenda: selector de día -->
    <div id="sel-dia-container" style="display:none;margin-bottom:1.2rem;">
      <label style="display:block;font-size:.8rem;color:#6060a0;margin-bottom:.4rem;">Día del evento</label>
      <select id="dia-import" style="width:100%;padding:.6rem .8rem;background:#0f0f1a;border:1px solid #2a2a4a;border-radius:6px;color:#e0e0e0;">
        <option value="">Cargando...</option>
      </select>
    </div>

    <!-- Zona de drop -->
    <div id="drop-zone"
      style="border:2px dashed #2a2a4a;border-radius:10px;padding:2.5rem;text-align:center;cursor:pointer;transition:border-color .2s;"
      ondragover="event.preventDefault();this.style.borderColor='#5050c0'"
      ondragleave="this.style.borderColor='#2a2a4a'"
      ondrop="onDrop(event)">
      <div style="font-size:2rem;margin-bottom:.6rem;">📂</div>
      <div style="color:#a0a0c0;font-size:.9rem;">Arrastra tu archivo aquí</div>
      <div style="color:#505070;font-size:.8rem;margin:.4rem 0">o</div>
      <label class="btn btn-ghost btn-sm" style="cursor:pointer;">
        Seleccionar archivo
        <input type="file" id="archivo" accept=".csv,.json,.txt,.xlsx" style="display:none" onchange="onFileSelect(this)">
      </label>
      <div id="archivo-nombre" style="margin-top:.8rem;font-size:.82rem;color:#6060a0;"></div>
    </div>

    <button id="btn-preview" class="btn btn-primary" style="margin-top:1.2rem;width:100%;display:none;" onclick="previsualizarImportacion()">
      Verificar archivo →
    </button>
  </div>
</div>

<!-- PASO 2: PREVIEW -->
<div id="paso-2" style="display:none;">
  <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;">
    <button class="btn btn-ghost btn-sm" onclick="volverPaso1()">← Volver</button>
    <h2 style="font-size:1rem;color:var(--text-2);">Paso 2 — Verificar datos</h2>
  </div>

  <!-- Columnas detectadas -->
  <div id="columnas-card" class="card" style="margin-bottom:1rem;display:none;">
    <h2>Columnas detectadas</h2>
    <div id="columnas-content"></div>
  </div>

  <!-- Errores y advertencias -->
  <div id="errores-container"></div>

  <!-- Muestra de datos -->
  <div class="card" style="margin-bottom:1rem;">
    <h2>Vista previa <span id="preview-total" style="color:#3060a0;font-weight:400;font-size:.85rem;"></span></h2>
    <div id="preview-tabla" style="overflow-x:auto;"></div>
  </div>

  <!-- Mapeo manual (si hay columnas no detectadas) -->
  <div id="mapeo-card" class="card" style="margin-bottom:1rem;display:none;">
    <h2>Mapeo de columnas <span style="color:#ffa040;font-size:.8rem;font-weight:400;">⚠ Algunas columnas no se detectaron</span></h2>
    <p style="font-size:.82rem;color:#6060a0;margin-bottom:1rem;">Indica qué columna del archivo corresponde a cada campo:</p>
    <div id="mapeo-content"></div>
  </div>

  <div style="display:flex;gap:.8rem;justify-content:flex-end;margin-top:1rem;">
    <button class="btn btn-ghost" onclick="volverPaso1()">Cancelar</button>
    <button id="btn-importar" class="btn btn-primary" onclick="confirmarImportacion()" disabled>
      Importar ahora
    </button>
  </div>
</div>

<!-- PASO 3: RESULTADO -->
<div id="paso-3" style="display:none;">
  <div class="card" style="max-width:560px;">
    <div id="resultado-icono" style="font-size:3rem;text-align:center;margin-bottom:1rem;"></div>
    <div id="resultado-titulo" style="font-size:1.1rem;font-weight:600;text-align:center;margin-bottom:1.5rem;"></div>
    <div id="resultado-stats"></div>
    <div id="resultado-errores" style="margin-top:1rem;"></div>
    <div style="margin-top:1.5rem;display:flex;gap:.8rem;justify-content:center;">
      <button class="btn btn-ghost" onclick="nuevaImportacion()">Nueva importación</button>
      <a href="/admin/asistentes" class="btn btn-primary">Ver asistentes</a>
    </div>
  </div>
</div>

<!-- HISTORIAL -->
<div class="card" style="margin-top:2rem;" id="historial-card">
  <h2>Historial de importaciones</h2>
  <div id="historial-content"><div style="color:#404060;font-size:.85rem;">Cargando...</div></div>
</div>

<script>
let archivoActual     = null;
let rawActual         = null;
let previewData       = null;
let columnasHeader    = [];  // columnas que tiene el CSV
let mapeoCampos       = {};  // mapeo manual si fue necesario

document.getElementById('tipo').addEventListener('change', function() {
  document.getElementById('sel-dia-container').style.display =
    this.value === 'agenda' ? 'block' : 'none';
});

// ── Carga de días ─────────────────────────────────────────
(async () => {
  const r = await fetch(API_BASE + '/agenda');
  const d = await r.json();
  document.getElementById('dia-import').innerHTML = d.dias.map(dia =>
    `<option value="${dia.id}">${dia.nombre || dia.fecha}</option>`
  ).join('');
})();

// ── Historial ─────────────────────────────────────────────
async function cargarHistorial() {
  const r = await fetch(API_BASE + '/sync-historial');
  const d = await r.json();
  if (!d.historial?.length) {
    document.getElementById('historial-content').innerHTML =
      '<div style="color:#404060;font-size:.85rem;">Sin importaciones anteriores.</div>';
    return;
  }
  document.getElementById('historial-content').innerHTML = `
    <table>
      <thead><tr><th>Fecha</th><th>Tipo</th><th>Fuente</th><th>Registros</th><th>Errores</th><th>Estado</th></tr></thead>
      <tbody>
        ${d.historial.map(h => `<tr>
          <td style="font-size:.8rem;font-family:monospace;color:#6060a0">${h.timestamp}</td>
          <td>${h.tipo}</td><td>${h.fuente}</td>
          <td>${h.registros}</td>
          <td style="color:${h.errores>0?'#ff6040':'#505070'}">${h.errores}</td>
          <td><span class="badge ${h.estado==='ok'?'badge-verde':'badge-naranja'}">${h.estado}</span></td>
        </tr>`).join('')}
      </tbody>
    </table>`;
}
cargarHistorial();

// ── Drag & drop ───────────────────────────────────────────
function onDrop(e) {
  e.preventDefault();
  document.getElementById('drop-zone').style.borderColor = '#2a2a4a';
  const file = e.dataTransfer.files[0];
  if (file) setArchivo(file);
}

function onFileSelect(input) {
  if (input.files[0]) setArchivo(input.files[0]);
}

function setArchivo(file) {
  archivoActual = file;
  document.getElementById('archivo-nombre').textContent = `📄 ${file.name} (${formatBytes(file.size)})`;
  document.getElementById('btn-preview').style.display = 'block';

  // Leer contenido para preview
  const reader = new FileReader();
  reader.onload = e => { rawActual = e.target.result; };
  reader.readAsText(file, 'UTF-8');
}

// ── Preview ───────────────────────────────────────────────
async function previsualizarImportacion() {
  if (!archivoActual) return;

  const btn = document.getElementById('btn-preview');
  btn.textContent = 'Analizando...';
  btn.disabled = true;

  const form = new FormData();
  form.append('tipo',    document.getElementById('tipo').value);
  form.append('fuente',  document.getElementById('fuente').value);
  form.append('archivo', archivoActual);

  const r = await fetch(API_BASE + '/importar-preview', { method: 'POST', body: form });
  previewData = await r.json();

  btn.textContent = 'Verificar archivo →';
  btn.disabled = false;

  renderPaso2(previewData);
}

function renderPaso2(d) {
  document.getElementById('paso-1').style.display = 'none';
  document.getElementById('paso-2').style.display = '';

  // Columnas detectadas
  if (d.columnas_detectadas) {
    const col = d.columnas_detectadas;
    const noDetectadas = Object.entries(col).filter(([k,v]) => v === null && ['uid_qr','nombre','titulo','salon_nombre','hora_inicio','hora_fin'].includes(k));

    document.getElementById('columnas-card').style.display = '';
    document.getElementById('columnas-content').innerHTML = `
      <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
        ${Object.entries(col).map(([campo, col_csv]) => `
          <div style="padding:.3rem .7rem;background:${col_csv?'#0a2a1a':'#2a1010'};border-radius:5px;font-size:.78rem;">
            <span style="color:${col_csv?'#30d080':'#ff5050'}">${col_csv || '—'}</span>
            <span style="color:#405050"> → ${campo}</span>
          </div>
        `).join('')}
      </div>`;

    // Si hay campos obligatorios no detectados → mostrar mapeo manual
    if (noDetectadas.length > 0) {
      mostrarMapeoManual(noDetectadas, rawActual);
    } else {
      document.getElementById('mapeo-card').style.display = 'none';
      document.getElementById('btn-importar').disabled = false;
    }
  } else {
    document.getElementById('btn-importar').disabled = false;
  }

  // Errores y advertencias
  const errContainer = document.getElementById('errores-container');
  errContainer.innerHTML = '';
  if (d.errores_validacion?.length) {
    errContainer.innerHTML += `<div class="flash flash-err" style="margin-bottom:.8rem;">
      <strong>Errores de validación (${d.errores_validacion.length}):</strong><br>
      ${d.errores_validacion.slice(0,10).map(e => `• ${esc(e)}`).join('<br>')}
      ${d.errores_validacion.length > 10 ? `<br>... y ${d.errores_validacion.length - 10} más` : ''}
    </div>`;
  }
  if (d.advertencias?.length) {
    errContainer.innerHTML += `<div class="flash" style="background:#2a2010;color:#ffa040;margin-bottom:.8rem;">
      ${d.advertencias.map(a => `⚠ ${esc(a)}`).join('<br>')}
    </div>`;
  }

  // Stats de preview
  document.getElementById('preview-total').textContent =
    `— ${d.total_validos} de ${d.total_parseados} filas válidas`;

  // Tabla de muestra
  if (d.muestra?.length) {
    const keys = Object.keys(d.muestra[0]);
    document.getElementById('preview-tabla').innerHTML = `
      <table>
        <thead><tr>${keys.map(k => `<th>${esc(k)}</th>`).join('')}</tr></thead>
        <tbody>
          ${d.muestra.map(row => `<tr>
            ${keys.map(k => `<td style="font-size:.82rem">${esc(String(row[k] ?? ''))}</td>`).join('')}
          </tr>`).join('')}
          ${d.total_validos > 5 ? `<tr><td colspan="${keys.length}" style="color:#404060;font-size:.78rem;text-align:center">... ${d.total_validos - 5} filas más</td></tr>` : ''}
        </tbody>
      </table>`;
  }
}

function mostrarMapeoManual(noDetectadas, raw) {
  // Obtener columnas reales del CSV para el selector
  const rawLimpio = raw.replace(/^\xEF\xBB\xBF/, '');
  const linea1    = rawLimpio.split('\n')[0];
  const delimitador = [',',';','\t'].sort((a,b) =>
    (linea1.split(b).length) - (linea1.split(a).length)
  )[0];
  columnasHeader = linea1.split(delimitador).map(h => h.trim().replace(/"/g,''));

  const card = document.getElementById('mapeo-card');
  card.style.display = '';
  document.getElementById('mapeo-content').innerHTML = noDetectadas.map(([campo]) => `
    <div style="display:flex;align-items:center;gap:.8rem;margin-bottom:.6rem;">
      <span style="min-width:120px;font-size:.85rem;color:#a0a0c0">${campo}</span>
      <span style="color:#404060">←</span>
      <select id="map-${campo}" style="flex:1;padding:.4rem .6rem;background:#0f0f1a;border:1px solid #2a2a4a;border-radius:5px;color:#e0e0e0;font-size:.82rem;"
        onchange="actualizarMapeo()">
        <option value="">— No disponible —</option>
        ${columnasHeader.map(c => `<option value="${esc(c)}">${esc(c)}</option>`).join('')}
      </select>
    </div>
  `).join('');
}

function actualizarMapeo() {
  const tipo = document.getElementById('tipo').value;
  const obligatorios = tipo === 'asistentes' ? ['uid_qr', 'nombre'] : ['titulo', 'salon_nombre', 'hora_inicio', 'hora_fin'];
  mapeoCampos = {};
  let todosMapeados = true;

  for (const campo of obligatorios) {
    const sel = document.getElementById(`map-${campo}`);
    if (!sel) continue;
    if (!sel.value) { todosMapeados = false; break; }
    mapeoCampos[campo] = sel.value;
  }

  document.getElementById('btn-importar').disabled = !todosMapeados;
}

// ── Importar ──────────────────────────────────────────────
async function confirmarImportacion() {
  const tipo   = document.getElementById('tipo').value;
  const fuente = document.getElementById('fuente').value;
  const dia_id = document.getElementById('dia-import').value;

  const btn = document.getElementById('btn-importar');
  btn.textContent = 'Importando...';
  btn.disabled = true;

  const form = new FormData();
  form.append('tipo',    tipo);
  form.append('fuente',  fuente);
  form.append('archivo', archivoActual);
  if (tipo === 'agenda' && dia_id) form.append('dia_id', dia_id);

  // Si hubo mapeo manual, pasar como campos extras
  if (Object.keys(mapeoCampos).length) {
    form.append('mapeo', JSON.stringify(mapeoCampos));
  }

  const r = await fetch(API_BASE + '/importar', { method: 'POST', body: form });
  const d = await r.json();

  btn.textContent = 'Importar ahora';
  btn.disabled = false;

  renderPaso3(d, tipo);
}

function renderPaso3(d, tipo) {
  document.getElementById('paso-2').style.display = 'none';
  document.getElementById('paso-3').style.display = '';

  const ok = d.ok && (!d.errores || d.errores.length === 0);
  document.getElementById('resultado-icono').textContent  = ok ? '✅' : '⚠️';
  document.getElementById('resultado-titulo').textContent = ok ? 'Importación completada' : 'Completado con advertencias';
  document.getElementById('resultado-titulo').style.color = ok ? '#30d080' : '#ffa040';

  const ins = d.insertados   ?? d.insertadas   ?? 0;
  const act = d.actualizados ?? d.actualizadas ?? 0;
  const omi = d.omitidos     ?? d.omitidas     ?? 0;

  document.getElementById('resultado-stats').innerHTML = `
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.8rem;text-align:center;">
      <div style="padding:.8rem;background:#0a2a1a;border-radius:8px;">
        <div style="font-size:1.8rem;font-weight:700;color:#30d080">${ins}</div>
        <div style="font-size:.78rem;color:#304040">${tipo === 'asistentes' ? 'Nuevos' : 'Nuevas charlas'}</div>
      </div>
      <div style="padding:.8rem;background:#0a1a3a;border-radius:8px;">
        <div style="font-size:1.8rem;font-weight:700;color:#4080ff">${act}</div>
        <div style="font-size:.78rem;color:#203050">Actualizados</div>
      </div>
      <div style="padding:.8rem;background:#1a1a1a;border-radius:8px;">
        <div style="font-size:1.8rem;font-weight:700;color:#606070">${omi}</div>
        <div style="font-size:.78rem;color:#404040">Omitidos</div>
      </div>
    </div>`;

  if (d.errores?.length) {
    document.getElementById('resultado-errores').innerHTML = `
      <div class="flash flash-err">
        <strong>Errores (${d.errores.length}):</strong><br>
        ${d.errores.slice(0, 10).map(e => `• ${esc(e)}`).join('<br>')}
      </div>`;
  }

  cargarHistorial();
}

function volverPaso1() {
  document.getElementById('paso-2').style.display = 'none';
  document.getElementById('paso-1').style.display = '';
}

function nuevaImportacion() {
  document.getElementById('paso-3').style.display = 'none';
  document.getElementById('paso-1').style.display = '';
  document.getElementById('archivo-nombre').textContent = '';
  document.getElementById('btn-preview').style.display = 'none';
  archivoActual = null; rawActual = null; previewData = null;
}

function formatBytes(b) {
  return b > 1048576 ? (b/1048576).toFixed(1)+'MB' : (b/1024).toFixed(0)+'KB';
}
function esc(s) { const d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }
</script>

</main></body></html>
