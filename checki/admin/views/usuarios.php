<?php
// Solo admins pueden acceder
if (!Auth::esAdmin()) {
    $base = preg_match('#^(.*?/admin)#', $_SERVER['REQUEST_URI'], $mx) ? $mx[1] : '/admin';
    header("Location: {$base}/");
    exit;
}
$titulo = 'Usuarios'; $seccion = 'usuarios'; include __DIR__ . '/_layout.php'; ?>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.4rem;">
  <h1 style="font-size:1.15rem; font-weight:800; color:var(--text-1); letter-spacing:-.01em;">Usuarios del panel</h1>
  <button class="btn btn-primary" onclick="abrirModalCrear()">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Nuevo usuario
  </button>
</div>

<div class="card">
  <h2>Operadores activos e inactivos</h2>
  <table>
    <thead>
      <tr>
        <th>Nombre</th>
        <th>Rol</th>
        <th>Estado</th>
        <th style="width:130px"></th>
      </tr>
    </thead>
    <tbody id="usuarios-body">
      <tr><td colspan="4" style="text-align:center;color:var(--text-3);padding:2rem">Cargando…</td></tr>
    </tbody>
  </table>
</div>

<!-- Modal crear/editar -->
<div class="modal-bg" id="modal-usuario">
  <div class="modal">
    <h3 id="modal-titulo">Nuevo usuario</h3>

    <label>Nombre</label>
    <input type="text" id="u-nombre" placeholder="Ej. Ana López" maxlength="80">

    <div id="u-rol-wrap">
      <label>Rol</label>
      <select id="u-rol">
        <option value="supervisor">Supervisor</option>
        <option value="admin">Administrador</option>
        <option value="viewer">Solo lectura</option>
      </select>
    </div>

    <label id="u-pin-label">PIN (mín. 4 dígitos)</label>
    <input type="password" id="u-pin" placeholder="••••" inputmode="numeric" maxlength="8"
           style="letter-spacing:.25em; text-align:center;">
    <p id="u-pin-hint" style="font-size:.72rem; color:var(--text-3); margin-top:.3rem; display:none;">
      Dejar vacío para no cambiar el PIN actual
    </p>

    <div id="u-activo-wrap" style="display:none; margin-top:.9rem;">
      <label style="display:inline-flex; align-items:center; gap:.5rem; cursor:pointer;">
        <input type="checkbox" id="u-activo" style="width:auto; padding:0; font-size:inherit; letter-spacing:0; text-align:left;">
        Usuario activo
      </label>
    </div>

    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="cerrarModal()">Cancelar</button>
      <button class="btn btn-primary" id="modal-btn-ok" onclick="guardar()">Crear</button>
    </div>
  </div>
</div>

<script>
const MI_ID    = <?= Auth::operadorId() ?>;
let editandoId = null;

// ── Cargar lista ──────────────────────────────────────
async function cargar() {
  const r = await fetch(API_BASE + '/operadores');
  if (!r.ok) { showToast('Error al cargar usuarios', 'err'); return; }
  const d = await r.json();
  renderTabla(d.operadores);
}

function renderTabla(ops) {
  const tbody = document.getElementById('usuarios-body');
  if (!ops.length) {
    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:var(--text-3);padding:2rem">Sin usuarios</td></tr>';
    return;
  }
  tbody.innerHTML = ops.map(op => `
    <tr>
      <td style="font-weight:600">${esc(op.nombre)}</td>
      <td>
        <span class="badge ${op.rol === 'admin' ? 'badge-naranja' : op.rol === 'viewer' ? 'badge-gris' : 'badge-azul'}">${op.rol}</span>
      </td>
      <td>
        <span class="badge ${op.activo ? 'badge-verde' : 'badge-gris'}">${op.activo ? 'activo' : 'inactivo'}</span>
      </td>
      <td style="text-align:right; display:flex; gap:.4rem; justify-content:flex-end;">
        <button class="btn btn-ghost btn-sm" onclick="abrirModalEditar(${op.id}, '${esc(op.nombre)}', '${op.rol}', ${op.activo})">
          Editar
        </button>
        <button class="btn btn-danger btn-sm" onclick="eliminar(${op.id}, '${esc(op.nombre)}')">
          Eliminar
        </button>
      </td>
    </tr>
  `).join('');
  staggerIn('#usuarios-body tr');
}

// ── Modal crear ───────────────────────────────────────
function abrirModalCrear() {
  editandoId = null;
  document.getElementById('modal-titulo').textContent    = 'Nuevo usuario';
  document.getElementById('modal-btn-ok').textContent    = 'Crear';
  document.getElementById('u-nombre').value              = '';
  document.getElementById('u-rol-wrap').style.display    = 'block';
  document.getElementById('u-rol').value                 = 'supervisor';
  document.getElementById('u-pin').value                 = '';
  document.getElementById('u-pin-label').textContent     = 'PIN (mín. 4 dígitos)';
  document.getElementById('u-pin-hint').style.display    = 'none';
  document.getElementById('u-activo-wrap').style.display = 'none';
  document.getElementById('modal-usuario').classList.add('open');
  setTimeout(() => document.getElementById('u-nombre').focus(), 200);
}

// ── Modal editar ──────────────────────────────────────
function abrirModalEditar(id, nombre, rol, activo) {
  editandoId = id;
  const esPropioUsuario = (id === MI_ID);

  document.getElementById('modal-titulo').textContent    = 'Editar usuario';
  document.getElementById('modal-btn-ok').textContent    = 'Guardar';
  document.getElementById('u-nombre').value              = nombre;
  document.getElementById('u-pin').value                 = '';
  document.getElementById('u-pin-label').textContent     = 'Nuevo PIN (opcional)';
  document.getElementById('u-pin-hint').style.display    = 'block';
  document.getElementById('u-activo-wrap').style.display = esPropioUsuario ? 'none' : 'block';
  document.getElementById('u-activo').checked            = !!activo;

  // Ocultar campo rol cuando el usuario edita su propia cuenta
  const rolWrap = document.getElementById('u-rol-wrap');
  if (esPropioUsuario) {
    rolWrap.style.display = 'none';
  } else {
    rolWrap.style.display = 'block';
    document.getElementById('u-rol').value = rol;
  }

  document.getElementById('modal-usuario').classList.add('open');
  setTimeout(() => document.getElementById('u-nombre').focus(), 200);
}

function cerrarModal() {
  document.getElementById('modal-usuario').classList.remove('open');
}

// ── Guardar (crear o editar) ──────────────────────────
async function guardar() {
  const nombre = document.getElementById('u-nombre').value.trim();
  const rol    = document.getElementById('u-rol').value;
  const pin    = document.getElementById('u-pin').value.trim();
  const activo = document.getElementById('u-activo').checked;

  if (!nombre) { showToast('El nombre es obligatorio', 'err'); return; }

  if (editandoId === null) {
    // Crear
    if (pin.length < 4) { showToast('El PIN debe tener al menos 4 dígitos', 'err'); return; }
    const r = await fetch(API_BASE + '/operador', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ nombre, rol, pin })
    });
    const d = await r.json();
    if (!r.ok) { showToast(apiErr(d, 'Error al crear'), 'err'); return; }
    showToast('Usuario creado');
  } else {
    // Editar
    const body = { nombre, rol, activo };
    if (pin.length >= 4) body.pin = pin;
    const r = await fetch(API_BASE + '/operador/' + editandoId, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });
    const d = await r.json();
    if (!r.ok) { showToast(apiErr(d, 'Error al guardar'), 'err'); return; }
    showToast('Usuario actualizado');
  }

  cerrarModal();
  cargar();
}

// ── Eliminar ──────────────────────────────────────────
async function eliminar(id, nombre) {
  const ok = await confirmDialog(
    `¿Eliminar al usuario "${nombre}"? Esta acción no se puede deshacer.`,
    { title: 'Eliminar usuario', ok: 'Sí, eliminar' }
  );
  if (!ok) return;
  const r = await fetch(API_BASE + '/operador/' + id, { method: 'DELETE' });
  const d = await r.json();
  if (!r.ok) { showToast(apiErr(d, 'Error al eliminar'), 'err'); return; }
  showToast('Usuario eliminado');
  cargar();
}

function esc(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }

// Cerrar modal al hacer click en el fondo
document.getElementById('modal-usuario').addEventListener('click', function(e) {
  if (e.target === this) cerrarModal();
});

cargar();
</script>

</main></body></html>
