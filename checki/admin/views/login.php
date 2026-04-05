<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Acceso — Control de Eventos</title>
<style>
/* ── TOKENS (mismos que _layout.php) ── */
:root {
  --bg:          #E6E2D8;
  --surface:     #FFFFFF;
  --surface-2:   #F7F5F0;
  --dark:        #1A1A1A;
  --accent:      #F0C040;
  --accent-red:  #E85050;
  --text-1:      #1A1A1A;
  --text-2:      #777777;
  --text-3:      #AAAAAA;
  --radius-card: 20px;
  --radius-sm:   10px;
  --shadow-lg:   0 12px 48px rgba(0,0,0,.14), 0 2px 8px rgba(0,0,0,.06);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  background: var(--bg);
  color: var(--text-1);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
  font-size: 14px;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ── DARK MODE ── */
body.dark {
  --bg:        #13131A;
  --surface:   #1C1C28;
  --surface-2: #252535;
  --text-1:    #E8E8F0;
  --text-2:    #8888A0;
  --text-3:    #505068;
  --shadow-lg: 0 20px 60px rgba(0,0,0,.6);
}

/* ── CARD ── */
.login-card {
  background: var(--surface);
  border-radius: var(--radius-card);
  padding: 2.5rem 2.25rem 2rem;
  width: min(360px, 95vw);
  box-shadow: var(--shadow-lg);
}

/* ── LOGO / BRAND ── */
.login-brand {
  display: flex;
  align-items: center;
  gap: .7rem;
  margin-bottom: 2rem;
}
.login-brand .brand-dot {
  width: 36px; height: 36px;
  background: var(--dark);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  color: var(--accent);
  font-size: .7rem;
  font-weight: 900;
  flex-shrink: 0;
}
body.dark .login-brand .brand-dot {
  background: rgba(240,192,64,.15);
  color: var(--accent);
}
.login-brand-text {
  font-size: .92rem;
  font-weight: 800;
  color: var(--text-1);
  letter-spacing: -.01em;
  line-height: 1.2;
}
.login-brand-sub {
  font-size: .72rem;
  color: var(--text-3);
  font-weight: 500;
  margin-top: .1rem;
}

/* ── FORM ── */
label {
  display: block;
  font-size: .63rem;
  font-weight: 700;
  color: var(--text-3);
  text-transform: uppercase;
  letter-spacing: .08em;
  margin-bottom: .35rem;
}
/* ── INPUTS (base compartida) ── */
input[type="text"],
input[type="password"] {
  width: 100%;
  padding: .75rem 1rem;
  background: var(--surface-2);
  border: 1.5px solid rgba(0,0,0,.08);
  border-radius: var(--radius-sm);
  color: var(--text-1);
  font-size: .9rem;
  font-family: inherit;
  outline: none;
  transition: border-color .15s, box-shadow .15s, background .15s;
  margin-bottom: .9rem;
}
input[type="text"]:focus,
input[type="password"]:focus {
  border-color: var(--dark);
  box-shadow: 0 0 0 3px rgba(26,26,26,.07);
  background: #fff;
  margin-bottom: .9rem;
}
body.dark input[type="text"],
body.dark input[type="password"] {
  background: var(--surface-2);
  border-color: rgba(255,255,255,.08);
  color: var(--text-1);
}
body.dark input[type="text"]:focus,
body.dark input[type="password"]:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(240,192,64,.12);
  background: rgba(255,255,255,.04);
}

/* ── PIN: centrado y grande ── */
input[type="password"] {
  font-size: 1.5rem;
  letter-spacing: .35em;
  text-align: center;
  margin-bottom: 0;
}
input[type="password"]:focus { margin-bottom: 0; }

button[type="submit"] {
  width: 100%;
  margin-top: 1.1rem;
  padding: .72rem 1rem;
  background: var(--dark);
  color: #fff;
  border: none;
  border-radius: var(--radius-sm);
  font-size: .88rem;
  font-weight: 700;
  cursor: pointer;
  transition: opacity .12s, transform .1s;
  font-family: inherit;
  letter-spacing: .01em;
}
body.dark button[type="submit"] {
  background: var(--accent);
  color: #1A1A1A;
}
button[type="submit"]:hover  { opacity: .82; }
button[type="submit"]:active { transform: scale(.98); }

/* ── ERROR ── */
.login-error {
  margin-top: .9rem;
  padding: .65rem .9rem;
  background: rgba(232,80,80,.08);
  border: 1px solid rgba(232,80,80,.2);
  border-radius: var(--radius-sm);
  color: var(--accent-red);
  font-size: .83rem;
  font-weight: 500;
  text-align: center;
}
body.dark .login-error {
  background: rgba(232,80,80,.15);
  color: #f87171;
  border-color: rgba(232,80,80,.25);
}

/* ── DARK MODE TOGGLE ── */
.dm-btn {
  position: fixed;
  top: 1rem; right: 1rem;
  width: 34px; height: 34px;
  background: var(--surface);
  border: 1px solid rgba(0,0,0,.08);
  border-radius: 50%;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  color: var(--text-2);
  box-shadow: 0 2px 8px rgba(0,0,0,.08);
  transition: background .15s, color .15s;
  padding: 0;
}
body.dark .dm-btn { border-color: rgba(255,255,255,.1); }
.dm-btn:hover { background: var(--dark); color: #fff; }
body.dark .dm-btn:hover { background: var(--accent); color: #1A1A1A; }
</style>
</head>
<body>

<button class="dm-btn" onclick="toggleDark()" title="Cambiar tema">
  <svg id="dm-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="12" cy="12" r="5"/>
    <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
    <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
  </svg>
</button>

<div class="login-card">
  <div class="login-brand">
    <span class="brand-dot">CE</span>
    <div>
      <div class="login-brand-text">Control de Eventos</div>
      <div class="login-brand-sub">Panel de administración</div>
    </div>
  </div>

  <?php
    preg_match('#^(.*?/admin)#', $_SERVER['REQUEST_URI'], $m);
    $base = $m[1] ?? '/admin';
  ?>
  <form method="POST" action="<?= $base ?>/login">
    <input type="hidden" name="evento_id" value="<?= EVENTO_ID_ACTIVO ?>">
    <label>Nombre de usuario</label>
    <input type="text" name="nombre" placeholder="Ej. Administrador" autofocus autocomplete="username">
    <label>PIN</label>
    <input type="password" name="pin" placeholder="••••" maxlength="8" inputmode="numeric" autocomplete="current-password">
    <button type="submit">Entrar</button>
    <?php if (isset($_GET['error'])): ?>
      <div class="login-error">Usuario o PIN incorrectos.</div>
    <?php endif; ?>
  </form>
</div>

<script>
(function() {
  function applyDark(dark) {
    document.body.classList.toggle('dark', dark);
    const icon = document.getElementById('dm-icon');
    if (icon) {
      icon.innerHTML = dark
        ? '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>'
        : '<circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>';
    }
  }
  window.toggleDark = function() {
    const dark = !document.body.classList.contains('dark');
    localStorage.setItem('dm', dark ? '1' : '0');
    applyDark(dark);
  };
  applyDark(localStorage.getItem('dm') === '1');
})();
</script>
</body>
</html>
