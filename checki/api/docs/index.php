<?php
// Construir base URL dinámica
$scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base_url = $scheme . '://' . $host . '/checkin/api';
$spec_url = $scheme . '://' . $host . '/checkin/api/docs/openapi.json';
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QR Check-In — API Docs</title>
<style>
  :root {
    --bg:        #0d1117;
    --bg2:       #161b22;
    --bg3:       #21262d;
    --border:    #30363d;
    --text:      #e6edf3;
    --muted:     #8b949e;
    --green:     #3fb950;
    --green-bg:  #0d2a16;
    --blue:      #58a6ff;
    --blue-bg:   #0d1f3c;
    --orange:    #d29922;
    --orange-bg: #2a1f0d;
    --red:       #f85149;
    --red-bg:    #2a0d0d;
    --purple:    #bc8cff;
    --radius:    8px;
    --mono:      'SF Mono', 'Fira Code', 'Cascadia Code', monospace;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    font-size: 15px;
    line-height: 1.6;
    display: flex;
    min-height: 100vh;
  }

  /* ── Sidebar ── */
  nav {
    width: 260px;
    min-width: 260px;
    background: var(--bg2);
    border-right: 1px solid var(--border);
    padding: 24px 0;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
  }
  .nav-logo {
    padding: 0 24px 24px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 16px;
  }
  .nav-logo h1 { font-size: 16px; font-weight: 700; color: var(--text); }
  .nav-logo span { font-size: 12px; color: var(--muted); }
  .nav-section { padding: 8px 24px 4px; font-size: 11px; font-weight: 700;
    letter-spacing: .08em; text-transform: uppercase; color: var(--muted); }
  nav a {
    display: flex; align-items: center; gap: 10px;
    padding: 7px 24px; color: var(--muted); text-decoration: none;
    font-size: 13px; transition: color .15s, background .15s;
  }
  nav a:hover { color: var(--text); background: var(--bg3); }
  nav a .badge {
    font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;
    font-family: var(--mono); letter-spacing: .03em;
  }
  .get  { background: var(--blue-bg);   color: var(--blue); }
  .post { background: var(--green-bg);  color: var(--green); }

  /* ── Main content ── */
  main {
    flex: 1;
    max-width: 860px;
    padding: 48px 48px 96px;
    overflow-x: hidden;
  }

  /* ── Header ── */
  .page-header { margin-bottom: 48px; padding-bottom: 32px; border-bottom: 1px solid var(--border); }
  .page-header h1 { font-size: 28px; font-weight: 800; margin-bottom: 8px; }
  .page-header p { color: var(--muted); max-width: 600px; }
  .meta-row { display: flex; gap: 24px; margin-top: 20px; flex-wrap: wrap; }
  .meta-item { display: flex; flex-direction: column; gap: 2px; }
  .meta-item label { font-size: 11px; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); }
  .meta-item code { font-family: var(--mono); font-size: 13px; color: var(--blue);
    background: var(--blue-bg); padding: 4px 10px; border-radius: 4px; border: 1px solid var(--border); }
  .version-badge { display: inline-flex; align-items: center; padding: 3px 10px;
    background: var(--green-bg); color: var(--green); border-radius: 20px;
    font-size: 12px; font-weight: 600; border: 1px solid rgba(63,185,80,.2); }

  /* ── Section titles ── */
  .section { margin-bottom: 64px; }
  .section-title {
    font-size: 20px; font-weight: 700; margin-bottom: 24px;
    padding-bottom: 12px; border-bottom: 1px solid var(--border);
  }

  /* ── Endpoint card ── */
  .endpoint { background: var(--bg2); border: 1px solid var(--border);
    border-radius: var(--radius); margin-bottom: 24px; overflow: hidden; }
  .endpoint-header {
    display: flex; align-items: center; gap: 14px;
    padding: 16px 20px; cursor: pointer;
    user-select: none;
  }
  .endpoint-header:hover { background: var(--bg3); }
  .method-badge {
    font-family: var(--mono); font-size: 12px; font-weight: 700;
    padding: 4px 10px; border-radius: 5px; min-width: 52px; text-align: center;
    letter-spacing: .05em;
  }
  .endpoint-path { font-family: var(--mono); font-size: 14px; color: var(--text); }
  .endpoint-summary { color: var(--muted); font-size: 13px; margin-left: auto; }
  .endpoint-body { padding: 0 20px 20px; border-top: 1px solid var(--border); }
  .endpoint-desc { color: var(--muted); font-size: 14px; margin: 16px 0; line-height: 1.7; }

  /* ── Parameters / fields ── */
  .param-title { font-size: 13px; font-weight: 600; color: var(--muted);
    text-transform: uppercase; letter-spacing: .07em; margin: 20px 0 10px; }
  .param-table { width: 100%; border-collapse: collapse; font-size: 13px; }
  .param-table th { text-align: left; padding: 8px 12px; color: var(--muted);
    font-size: 11px; text-transform: uppercase; letter-spacing: .07em;
    border-bottom: 1px solid var(--border); font-weight: 600; }
  .param-table td { padding: 10px 12px; border-bottom: 1px solid rgba(48,54,61,.5); vertical-align: top; }
  .param-table tr:last-child td { border-bottom: none; }
  .param-name { font-family: var(--mono); color: var(--blue); font-size: 13px; }
  .param-type { font-family: var(--mono); color: var(--purple); font-size: 12px; }
  .required-badge { font-size: 10px; background: var(--red-bg); color: var(--red);
    padding: 2px 6px; border-radius: 4px; font-weight: 600; margin-left: 6px; }
  .optional-badge { font-size: 10px; background: var(--bg3); color: var(--muted);
    padding: 2px 6px; border-radius: 4px; margin-left: 6px; }
  .param-desc { color: var(--muted); font-size: 13px; }

  /* ── Response tabs ── */
  .response-tabs { display: flex; gap: 8px; margin: 20px 0 12px; flex-wrap: wrap; }
  .response-tab {
    padding: 6px 14px; border-radius: 5px; font-size: 12px; font-weight: 600;
    font-family: var(--mono); cursor: pointer; border: 1px solid var(--border);
    background: var(--bg3); color: var(--muted); transition: all .15s;
  }
  .response-tab.active-200 { background: var(--green-bg); color: var(--green); border-color: rgba(63,185,80,.3); }
  .response-tab.active-400 { background: var(--orange-bg); color: var(--orange); border-color: rgba(210,153,34,.3); }
  .response-tab.active-500 { background: var(--red-bg); color: var(--red); border-color: rgba(248,81,73,.3); }
  .response-tab.active-204 { background: var(--bg3); color: var(--muted); }

  /* ── Code blocks ── */
  .code-block {
    background: var(--bg); border: 1px solid var(--border); border-radius: 6px;
    padding: 16px; font-family: var(--mono); font-size: 13px; line-height: 1.7;
    overflow-x: auto; position: relative;
  }
  .code-block .label {
    font-size: 11px; color: var(--muted); margin-bottom: 10px;
    text-transform: uppercase; letter-spacing: .08em; font-family: sans-serif;
  }
  .code-block .copy-btn {
    position: absolute; top: 12px; right: 12px;
    background: var(--bg3); border: 1px solid var(--border); color: var(--muted);
    padding: 4px 10px; border-radius: 4px; font-size: 11px; cursor: pointer;
    font-family: sans-serif; transition: all .15s;
  }
  .code-block .copy-btn:hover { color: var(--text); border-color: var(--muted); }
  .code-block .copy-btn.copied { color: var(--green); border-color: var(--green); }

  /* JSON syntax */
  .json-key   { color: var(--blue); }
  .json-str   { color: #a5d6ff; }
  .json-num   { color: var(--purple); }
  .json-bool  { color: var(--orange); }
  .json-null  { color: var(--muted); }

  /* ── Alert boxes ── */
  .alert {
    padding: 12px 16px; border-radius: 6px; font-size: 13px;
    display: flex; gap: 10px; align-items: flex-start; margin: 16px 0;
  }
  .alert-info  { background: var(--blue-bg);   border: 1px solid rgba(88,166,255,.2); color: #79c0ff; }
  .alert-warn  { background: var(--orange-bg); border: 1px solid rgba(210,153,34,.2); color: var(--orange); }
  .alert-icon  { font-size: 16px; line-height: 1.4; }

  /* ── Schema ── */
  .schema-table { width: 100%; border-collapse: collapse; font-size: 13px; }
  .schema-table th { text-align: left; padding: 8px 12px; color: var(--muted);
    font-size: 11px; text-transform: uppercase; letter-spacing: .07em;
    border-bottom: 1px solid var(--border); font-weight: 600; }
  .schema-table td { padding: 10px 12px; border-bottom: 1px solid rgba(48,54,61,.5); }
  .schema-table tr:last-child td { border-bottom: none; }
  .enum-pill { display: inline-block; font-family: var(--mono); font-size: 11px;
    padding: 2px 7px; background: var(--bg3); border: 1px solid var(--border);
    border-radius: 4px; color: var(--muted); margin: 1px; }

  /* ── Footer ── */
  footer { margin-top: 64px; padding-top: 24px; border-top: 1px solid var(--border);
    color: var(--muted); font-size: 13px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
  footer a { color: var(--blue); text-decoration: none; }
  footer a:hover { text-decoration: underline; }

  @media (max-width: 768px) {
    nav { display: none; }
    main { padding: 24px 20px 64px; }
  }
</style>
</head>
<body>

<!-- ══ SIDEBAR ══════════════════════════════════════════════════════ -->
<nav>
  <div class="nav-logo">
    <h1>QR Check-In</h1>
    <span>API Reference v1.0</span>
  </div>
  <div class="nav-section">Sistema</div>
  <a href="#ping"><span class="badge get">GET</span> /ping</a>
  <div class="nav-section">Tótem</div>
  <a href="#charla-activa"><span class="badge get">GET</span> /charla-activa</a>
  <a href="#lectura"><span class="badge post">POST</span> /lectura</a>
  <div class="nav-section">Referencia</div>
  <a href="#modelos">Modelos</a>
  <a href="#colores">Códigos de color</a>
  <a href="#integracion">Integración externa</a>
  <a href="openapi.json" target="_blank">↗ openapi.json</a>
</nav>

<!-- ══ MAIN ═════════════════════════════════════════════════════════ -->
<main>

  <!-- Header -->
  <div class="page-header">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
      <h1>QR Check-In API</h1>
      <span class="version-badge">v1.0.0</span>
    </div>
    <p>API REST para el sistema de control de asistencia en eventos corporativos multi-salón. Diseñada para tótems Android con lector QR HID en red local sin dependencia de internet.</p>
    <div class="meta-row">
      <div class="meta-item">
        <label>Base URL</label>
        <code><?= htmlspecialchars($base_url) ?></code>
      </div>
      <div class="meta-item">
        <label>Formato</label>
        <code>application/json</code>
      </div>
      <div class="meta-item">
        <label>Autenticación</label>
        <code>Ninguna (red local)</code>
      </div>
    </div>
    <div class="alert alert-info" style="margin-top:20px">
      <span class="alert-icon">ℹ</span>
      <span>Esta API está diseñada para operar en una red local cerrada. No requiere token ni API key. Para producción, asegúrate de que solo sea accesible desde la red del evento.</span>
    </div>
  </div>

  <!-- ── GET /ping ─────────────────────────────────────────────── -->
  <div class="section" id="ping">
    <h2 class="section-title">Sistema</h2>

    <div class="endpoint">
      <div class="endpoint-header">
        <span class="method-badge get">GET</span>
        <span class="endpoint-path">/ping</span>
        <span class="endpoint-summary">Health check</span>
      </div>
      <div class="endpoint-body">
        <p class="endpoint-desc">Verifica que el servidor está operativo. Útil para diagnóstico de conectividad desde los tótems antes de iniciar operaciones, o desde herramientas de monitoreo.</p>

        <div class="param-title">Respuesta 200</div>
        <div class="code-block">
          <div class="label">JSON</div>
          <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
          <pre>{
  <span class="json-key">"ok"</span>: <span class="json-bool">true</span>,
  <span class="json-key">"timestamp"</span>: <span class="json-str">"2026-02-25 16:50:07"</span>
}</pre>
        </div>

        <div class="param-title">Ejemplo cURL</div>
        <div class="code-block">
          <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
          <pre>curl <?= htmlspecialchars($base_url) ?>/ping</pre>
        </div>
      </div>
    </div>
  </div>

  <!-- ── GET /charla-activa ──────────────────────────────────────── -->
  <div class="section" id="charla-activa">
    <h2 class="section-title">Tótem</h2>

    <div class="endpoint">
      <div class="endpoint-header">
        <span class="method-badge get">GET</span>
        <span class="endpoint-path">/charla-activa</span>
        <span class="endpoint-summary">Charla activa en un salón</span>
      </div>
      <div class="endpoint-body">
        <p class="endpoint-desc">
          Devuelve la charla que está en curso ahora mismo en el salón del tótem, y la próxima del día si no hay ninguna activa. Los tótems llaman a este endpoint cada 30 segundos para mantener la pantalla de espera actualizada.
        </p>

        <div class="alert alert-info">
          <span class="alert-icon">ℹ</span>
          <span>Acepta <code style="font-size:12px;background:rgba(88,166,255,.1);padding:1px 5px;border-radius:3px">totem_id</code> o <code style="font-size:12px;background:rgba(88,166,255,.1);padding:1px 5px;border-radius:3px">salon_id</code>. Con <code style="font-size:12px;background:rgba(88,166,255,.1);padding:1px 5px;border-radius:3px">totem_id</code> el servidor resuelve automáticamente el salón.</span>
        </div>

        <div class="param-title">Parámetros query</div>
        <table class="param-table">
          <tr><th>Nombre</th><th>Tipo</th><th>Descripción</th></tr>
          <tr>
            <td><span class="param-name">totem_id</span><span class="optional-badge">opcional</span></td>
            <td><span class="param-type">integer</span></td>
            <td class="param-desc">ID del tótem. El servidor obtiene su salón de la tabla <code>totems</code>.</td>
          </tr>
          <tr>
            <td><span class="param-name">salon_id</span><span class="optional-badge">opcional</span></td>
            <td><span class="param-type">integer</span></td>
            <td class="param-desc">ID del salón directamente. Alternativa a <code>totem_id</code>.</td>
          </tr>
        </table>

        <div class="param-title">Respuestas</div>
        <div class="response-tabs">
          <span class="response-tab active-200">200 Con charla activa</span>
          <span class="response-tab active-200">200 Próxima charla</span>
          <span class="response-tab active-200">200 Sin charlas</span>
          <span class="response-tab active-400">400 Error</span>
        </div>

        <div class="code-block">
          <div class="label">200 — Charla en curso ahora mismo</div>
          <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
          <pre>{
  <span class="json-key">"charla"</span>: {
    <span class="json-key">"id"</span>: <span class="json-num">3</span>,
    <span class="json-key">"titulo"</span>: <span class="json-str">"Innovación en RRHH"</span>,
    <span class="json-key">"ponente"</span>: <span class="json-str">"Dr. García"</span>,
    <span class="json-key">"hora_inicio"</span>: <span class="json-str">"10:00:00"</span>,
    <span class="json-key">"hora_fin"</span>: <span class="json-str">"11:00:00"</span>
  },
  <span class="json-key">"proxima"</span>: <span class="json-null">null</span>
}</pre>
        </div>

        <div class="code-block" style="margin-top:12px">
          <div class="label">200 — Sin charla activa, pero hay una próxima hoy</div>
          <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
          <pre>{
  <span class="json-key">"charla"</span>: <span class="json-null">null</span>,
  <span class="json-key">"proxima"</span>: {
    <span class="json-key">"id"</span>: <span class="json-num">4</span>,
    <span class="json-key">"titulo"</span>: <span class="json-str">"Marketing Digital"</span>,
    <span class="json-key">"ponente"</span>: <span class="json-str">"Sara López"</span>,
    <span class="json-key">"hora_inicio"</span>: <span class="json-str">"11:30:00"</span>,
    <span class="json-key">"hora_fin"</span>: <span class="json-str">"12:30:00"</span>
  }
}</pre>
        </div>

        <div class="code-block" style="margin-top:12px">
          <div class="label">200 — No hay más charlas hoy</div>
          <pre>{ <span class="json-key">"charla"</span>: <span class="json-null">null</span>, <span class="json-key">"proxima"</span>: <span class="json-null">null</span> }</pre>
        </div>

        <div class="param-title">Ejemplo cURL</div>
        <div class="code-block">
          <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
          <pre>curl "<?= htmlspecialchars($base_url) ?>/charla-activa?totem_id=1"</pre>
        </div>
      </div>
    </div>

    <!-- ── POST /lectura ── -->
    <div class="endpoint" id="lectura">
      <div class="endpoint-header">
        <span class="method-badge post">POST</span>
        <span class="endpoint-path">/lectura</span>
        <span class="endpoint-summary">Registrar escaneo QR</span>
      </div>
      <div class="endpoint-body">
        <p class="endpoint-desc">
          Procesa un escaneo QR y registra el checkin o checkout del asistente. El servidor determina automáticamente el tipo según el estado actual del asistente en ese salón. Si el asistente está <em>fuera</em> → checkin. Si está <em>dentro</em> → checkout.
        </p>

        <div class="alert alert-warn">
          <span class="alert-icon">⚠</span>
          <span><strong>Debounce de 5 segundos:</strong> si el mismo asistente realiza el mismo tipo de movimiento (checkin→checkin o checkout→checkout) en menos de 5 segundos, el servidor responde con HTTP 200 y body vacío. El cliente debe ignorarlo silenciosamente. Esto evita dobles registros por lecturas accidentales del lector HID.</span>
        </div>

        <div class="param-title">Body (application/json)</div>
        <table class="param-table">
          <tr><th>Campo</th><th>Tipo</th><th>Descripción</th></tr>
          <tr>
            <td><span class="param-name">uid_qr</span><span class="required-badge">requerido</span></td>
            <td><span class="param-type">string</span></td>
            <td class="param-desc">Código escaneado. Debe coincidir con el campo <code>uid_qr</code> de un asistente activo en la BD.</td>
          </tr>
          <tr>
            <td><span class="param-name">totem_id</span><span class="required-badge">requerido</span></td>
            <td><span class="param-type">integer</span></td>
            <td class="param-desc">ID del tótem que realiza el escaneo. Determina el salón donde se registra el movimiento.</td>
          </tr>
          <tr>
            <td><span class="param-name">timestamp_totem</span><span class="optional-badge">opcional</span></td>
            <td><span class="param-type">string</span></td>
            <td class="param-desc">Timestamp del dispositivo en formato <code>YYYY-MM-DD HH:MM:SS</code>. Se guarda para auditoría pero no afecta la lógica de negocio.</td>
          </tr>
        </table>

        <div class="param-title">Request de ejemplo</div>
        <div class="code-block">
          <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
          <pre>{
  <span class="json-key">"uid_qr"</span>: <span class="json-str">"QR-TEST-001"</span>,
  <span class="json-key">"totem_id"</span>: <span class="json-num">1</span>,
  <span class="json-key">"timestamp_totem"</span>: <span class="json-str">"2026-02-25 16:50:07"</span>
}</pre>
        </div>

        <div class="param-title">Respuestas</div>

        <div class="code-block" style="margin-top:12px">
          <div class="label">200 — Checkin exitoso</div>
          <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
          <pre>{
  <span class="json-key">"tipo"</span>:    <span class="json-str">"checkin"</span>,
  <span class="json-key">"nombre"</span>:  <span class="json-str">"Ana García"</span>,
  <span class="json-key">"charla"</span>:  <span class="json-str">"Innovación en RRHH"</span>,  <span style="color:var(--muted)">// null si está fuera de horario</span>
  <span class="json-key">"color"</span>:   <span class="json-str">"verde"</span>,
  <span class="json-key">"mensaje"</span>: <span class="json-str">"Bienvenido"</span>
}</pre>
        </div>

        <div class="code-block" style="margin-top:12px">
          <div class="label">200 — Checkout exitoso</div>
          <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
          <pre>{
  <span class="json-key">"tipo"</span>:    <span class="json-str">"checkout"</span>,
  <span class="json-key">"nombre"</span>:  <span class="json-str">"Ana García"</span>,
  <span class="json-key">"minutos"</span>: <span class="json-num">47</span>,
  <span class="json-key">"color"</span>:   <span class="json-str">"verde"</span>,
  <span class="json-key">"mensaje"</span>: <span class="json-str">"Hasta luego"</span>
}</pre>
        </div>

        <div class="code-block" style="margin-top:12px">
          <div class="label">200 — QR no encontrado</div>
          <pre>{
  <span class="json-key">"tipo"</span>:    <span class="json-str">"error"</span>,
  <span class="json-key">"color"</span>:   <span class="json-str">"rojo"</span>,
  <span class="json-key">"mensaje"</span>: <span class="json-str">"QR no válido"</span>
}</pre>
        </div>

        <div class="code-block" style="margin-top:12px">
          <div class="label">200 vacío — Debounce activo (ignorar)</div>
          <pre style="color:var(--muted);font-style:italic">(sin body)</pre>
        </div>

        <div class="param-title">Ejemplo cURL</div>
        <div class="code-block">
          <button class="copy-btn" onclick="copyCode(this)">Copiar</button>
          <pre>curl -X POST <?= htmlspecialchars($base_url) ?>/lectura \
  -H "Content-Type: application/json" \
  -d '{"uid_qr":"QR-TEST-001","totem_id":1}'</pre>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Modelos ──────────────────────────────────────────────────── -->
  <div class="section" id="modelos">
    <h2 class="section-title">Modelos</h2>

    <div class="endpoint">
      <div class="endpoint-header" style="cursor:default">
        <span style="font-family:var(--mono);font-size:14px;font-weight:700">CharlaData</span>
      </div>
      <div class="endpoint-body">
        <table class="schema-table">
          <tr><th>Campo</th><th>Tipo</th><th>Descripción</th></tr>
          <tr><td><span class="param-name">id</span></td><td><span class="param-type">integer</span></td><td class="param-desc">Identificador único de la charla</td></tr>
          <tr><td><span class="param-name">titulo</span></td><td><span class="param-type">string</span></td><td class="param-desc">Nombre de la charla o ponencia</td></tr>
          <tr><td><span class="param-name">ponente</span></td><td><span class="param-type">string</span></td><td class="param-desc">Nombre del ponente</td></tr>
          <tr><td><span class="param-name">hora_inicio</span></td><td><span class="param-type">string</span></td><td class="param-desc">Hora de inicio en formato <code>HH:MM:SS</code></td></tr>
          <tr><td><span class="param-name">hora_fin</span></td><td><span class="param-type">string</span></td><td class="param-desc">Hora de fin en formato <code>HH:MM:SS</code></td></tr>
        </table>
      </div>
    </div>

    <div class="endpoint">
      <div class="endpoint-header" style="cursor:default">
        <span style="font-family:var(--mono);font-size:14px;font-weight:700">LecturaResponse</span>
      </div>
      <div class="endpoint-body">
        <table class="schema-table">
          <tr><th>Campo</th><th>Tipo</th><th>Descripción</th></tr>
          <tr><td><span class="param-name">tipo</span></td><td><span class="param-type">string</span></td><td class="param-desc"><span class="enum-pill">checkin</span><span class="enum-pill">checkout</span><span class="enum-pill">error</span></td></tr>
          <tr><td><span class="param-name">nombre</span></td><td><span class="param-type">string</span></td><td class="param-desc">Nombre del asistente. Ausente si <code>tipo = error</code></td></tr>
          <tr><td><span class="param-name">charla</span></td><td><span class="param-type">string | null</span></td><td class="param-desc">Título de la charla activa. Solo en checkin. <code>null</code> si fuera de horario.</td></tr>
          <tr><td><span class="param-name">minutos</span></td><td><span class="param-type">integer</span></td><td class="param-desc">Minutos que el asistente estuvo en sala. Solo en checkout.</td></tr>
          <tr><td><span class="param-name">color</span></td><td><span class="param-type">string</span></td><td class="param-desc"><span class="enum-pill">verde</span><span class="enum-pill">naranja</span><span class="enum-pill">rojo</span></td></tr>
          <tr><td><span class="param-name">mensaje</span></td><td><span class="param-type">string</span></td><td class="param-desc">Texto para mostrar en pantalla del tótem</td></tr>
        </table>
      </div>
    </div>
  </div>

  <!-- ── Colores ──────────────────────────────────────────────────── -->
  <div class="section" id="colores">
    <h2 class="section-title">Códigos de color</h2>
    <p style="color:var(--muted);margin-bottom:20px;font-size:14px">El campo <code>color</code> en las respuestas indica qué color de fondo debe mostrar el tótem.</p>
    <table class="schema-table" style="background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden">
      <tr><th>Valor</th><th>Hex</th><th>Situación</th></tr>
      <tr>
        <td><span class="enum-pill" style="background:#0d2a16;color:#3fb950;border-color:rgba(63,185,80,.3)">verde</span></td>
        <td><span class="param-type">#1a7a4a</span></td>
        <td class="param-desc">Checkin o checkout exitoso</td>
      </tr>
      <tr>
        <td><span class="enum-pill" style="background:#2a1f0d;color:#d29922;border-color:rgba(210,153,34,.3)">naranja</span></td>
        <td><span class="param-type">#c25d00</span></td>
        <td class="param-desc">Operación registrada con advertencia (ej: fuera de horario)</td>
      </tr>
      <tr>
        <td><span class="enum-pill" style="background:#2a0d0d;color:#f85149;border-color:rgba(248,81,73,.3)">rojo</span></td>
        <td><span class="param-type">#8b1a1a</span></td>
        <td class="param-desc">QR no válido o error de negocio</td>
      </tr>
    </table>
  </div>

  <!-- ── Integración externa ─────────────────────────────────────── -->
  <div class="section" id="integracion">
    <h2 class="section-title">Integración externa</h2>
    <p style="color:var(--muted);margin-bottom:20px;font-size:14px">Para importar asistentes desde sistemas externos sin pasar por el admin panel.</p>

    <div class="alert alert-warn">
      <span class="alert-icon">🚧</span>
      <span><strong>En desarrollo.</strong> Actualmente la ingesta de asistentes se hace mediante CSV desde el admin panel (<code>/admin/importar</code>). El endpoint REST <code>POST /asistentes</code> está planificado para una futura versión.</span>
    </div>

    <p style="color:var(--muted);font-size:14px;margin-top:16px">Mientras tanto, el CSV de importación acepta las columnas:</p>
    <div class="code-block" style="margin-top:12px">
      <div class="label">CSV formato soportado</div>
      <pre>uid_qr,nombre,email,empresa,external_id
QR-001,Ana García,ana@empresa.com,Demo Corp,1001
QR-002,Luis Martínez,luis@empresa.com,Demo Corp,1002</pre>
    </div>

    <p style="color:var(--muted);font-size:14px;margin-top:20px">Para importar desde Postman o scripts externos, descarga el spec OpenAPI:</p>
    <div style="margin-top:12px">
      <a href="openapi.json" style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;background:var(--bg3);border:1px solid var(--border);border-radius:6px;color:var(--text);text-decoration:none;font-size:14px;transition:all .15s" onmouseover="this.style.borderColor='#58a6ff'" onmouseout="this.style.borderColor='var(--border)'">
        ↓ Descargar openapi.json
      </a>
    </div>
  </div>

  <footer>
    <span>QR Check-In API · v1.0.0 · Red local del evento</span>
    <span>Base URL: <a href="<?= htmlspecialchars($base_url) ?>"><?= htmlspecialchars($base_url) ?></a></span>
  </footer>

</main>

<script>
function copyCode(btn) {
  const pre = btn.closest('.code-block').querySelector('pre');
  const text = pre.innerText;
  navigator.clipboard.writeText(text).then(() => {
    btn.textContent = '✓ Copiado';
    btn.classList.add('copied');
    setTimeout(() => { btn.textContent = 'Copiar'; btn.classList.remove('copied'); }, 2000);
  });
}
</script>
</body>
</html>
