/* ══════════════════════════════════════════════
   Mission Control — Prototype (production-ready)
   EventOS · Lumina Noir · Accent White
   ══════════════════════════════════════════════ */

// ── Hash-based name color ──────────────────────────────
function nc(name) {
  var h = 0;
  for (var i = 0; i < name.length; i++) h = name.charCodeAt(i) + ((h << 5) - h);
  return 'c' + ((Math.abs(h) % 8) + 1);
}
function ini(s) { return s.split(' ').map(function(w) { return w[0]; }).slice(0, 2).join('').toUpperCase(); }
function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

// ── Toast ──────────────────────────────────────────────
var _tt;
function toast(t) {
  var e = document.getElementById('toast');
  e.textContent = t; e.classList.add('show');
  clearTimeout(_tt);
  _tt = setTimeout(function() { e.classList.remove('show'); }, 2200);
}

// ── Seed data ──────────────────────────────────────────
var NAMES = [
  { n: 'Andrea Salazar',   r: '' },
  { n: 'Diego Ramirez',    r: '' },
  { n: 'Sofia Torres',     r: '' },
  { n: 'Carlos Vega',      r: '' },
  { n: 'Laura Mendoza',    r: '' },
  { n: 'Juan Torres',      r: 'mod' },
  { n: 'Ana Rojas',        r: '' },
  { n: 'Carlos R. Yepes',  r: 'speaker' },
];

var MSGS = [
  { w: 0, t: '10:04', m: 'Excelente keynote, muy alineado con la estrategia digital' },
  { w: 1, t: '10:04', m: 'Alguien sabe si despues hay networking?' },
  { w: 2, t: '10:05', m: 'Si, salon B de 12 a 13. Esta en la agenda.' },
  { w: 3, t: '10:05', m: 'Muy interesante el tema de banca conversacional' },
  { w: 5, t: '10:05', m: 'Recuerden que pueden enviar preguntas en el tab Q&A' },
  { w: 7, t: '10:06', m: 'Gracias a todos, vamos con la segunda parte del analisis' },
  { w: 4, t: '10:06', m: 'Se puede integrar con los sistemas legacy que tenemos?' },
  { w: 6, t: '10:06', m: 'El roadmap de APIs es prometedor para el sector' },
  { w: 0, t: '10:07', m: 'Los datos del mercado colombiano son reveladores' },
  { w: 1, t: '10:07', m: 'Alguien tiene el enlace a la presentacion?' },
  { w: 5, t: '10:07', m: 'Se compartira al finalizar la sesion por email' },
  { w: 2, t: '10:08', m: 'Increible la velocidad de adopcion digital post-pandemia' },
  // Banned user message demo
  { w: -1, t: '10:08', m: 'SPAM SPAM compra ahora!!!', banned: true, fakeName: 'spambot_99' },
  { w: 3, t: '10:08', m: 'Los numeros de Nequi vs banca tradicional son impactantes' },
  { w: 4, t: '10:09', m: 'Pregunta: cual es la inversion proyectada para IA en 2027?' },
  { w: 6, t: '10:09', m: 'Segundo esa pregunta, muy relevante para nuestro sector' },
  { w: 0, t: '10:10', m: 'Excelente sesion. La mejor del summit sin duda.' },
];

var QNA = [
  { w: 2, t: 'hace 2m', v: 47, q: 'Cual es la estrategia para competir con fintechs como Nequi y Daviplata en el segmento joven?' },
  { w: 4, t: 'hace 4m', v: 38, q: 'Van a integrar IA generativa en el asistente de la app durante 2026?' },
  { w: 1, t: 'hace 6m', v: 24, q: 'Como manejan seguridad para operaciones superiores a 10M COP desde movil?' },
  { w: 3, t: 'hace 8m', v: 19, q: 'Habra expansion a mercados regionales como Centroamerica?' },
  { w: 6, t: 'hace 11m', v: 16, q: 'Cuando podremos esperar soporte para pagos QR universales?' },
  { w: 0, t: 'hace 14m', v: 12, q: 'La API para desarrolladores externos: existe un roadmap publico?' },
];

var POLLS = [
  {
    id: 1, title: 'Que funcionalidad te gustaria ver primero?',
    sub: 'Seleccion unica · 3 opciones', status: 'live', votes: 1842, timeLeft: '2:14',
    type: 'mc',
    opts: [
      { l: 'Pagos QR universales', p: 52 },
      { l: 'Asistente IA personalizado', p: 28 },
      { l: 'Inversiones desde la app', p: 20 },
    ]
  },
  {
    id: 2, title: 'Calificacion de la keynote',
    sub: 'Calificacion 1-5 estrellas', status: 'live', votes: 847,
    type: 'star', avg: 4.3, dist: { 5: 312, 4: 289, 3: 156, 2: 58, 1: 32 }
  },
  {
    id: 3, title: 'Que tema te gustaria profundizar?',
    sub: 'Respuesta abierta', status: 'closed', votes: 234,
    type: 'text',
    answers: [
      'Integracion con APIs de terceros',
      'Seguridad biometrica avanzada',
      'Expansion a mercados internacionales',
      'Banca conversacional con IA',
      'Tokenizacion de activos',
      'Open banking y regulacion',
    ]
  },
];

var TL = [
  { d: 'live', t: '10:24', b: 'Sesion <b>EN VIVO</b> — 8,214 conectados' },
  { d: 'ok',   t: '10:22', b: 'Poll lanzado: <b>Calificacion keynote</b>' },
  { d: 'blue', t: '10:18', b: 'Poll lanzado: <b>Funcionalidad banca</b>' },
  { d: 'ok',   t: '10:15', b: '3 preguntas aprobadas' },
  { d: 'blue', t: '10:12', b: 'Mensaje anclado por <b>Juan Torres</b>' },
  { d: 'warn', t: '10:08', b: 'Slow mode: <b>10 segundos</b>' },
  { d: '',     t: '10:04', b: 'Config: <b>emoji off</b>' },
  { d: '',     t: '10:00', b: 'Sesion iniciada' },
];

var BANNED = [{ n: 'spambot_99' }, { n: 'troll_user_x' }];

// ── Render Chat ───────────────────────────────────────
function renderFeed() {
  var el = document.getElementById('feed');
  el.innerHTML = MSGS.map(function(m) {
    var isBanned = m.banned;
    var p = m.w >= 0 ? NAMES[m.w] : { n: m.fakeName, r: '' };
    var isMod = p.r === 'mod';
    var isSpeaker = p.r === 'speaker';
    var cls = 'mc-msg' + (isMod ? ' is-mod' : '') + (isBanned ? ' banned' : '');
    var badge = '';
    if (isMod) badge = '<span class="mc-role">MOD</span>';
    else if (isSpeaker) badge = '<span class="mc-role">SPEAKER</span>';
    return '<div class="' + cls + '">' +
      '<span class="mc-msg-t">' + m.t + '</span>' +
      '<div class="mc-msg-b">' + badge +
        '<span class="mc-msg-n ' + nc(p.n) + '">' + esc(p.n) + '</span>' + esc(m.m) +
      '</div>' +
      (isBanned ? '' :
      '<div class="mc-msg-a">' +
        '<button class="mc-msg-ab" title="Anclar" onclick="pinMsg(this)"><span class="icon">push_pin</span></button>' +
        '<button class="mc-msg-ab dng" title="Eliminar" onclick="delMsg(this)"><span class="icon">delete</span></button>' +
        '<button class="mc-msg-ab dng" title="Banear" onclick="banMsg(\'' + esc(p.n) + '\')"><span class="icon">block</span></button>' +
      '</div>') +
    '</div>';
  }).join('');
  el.scrollTop = el.scrollHeight;
}
renderFeed();

// Chat actions
function pinMsg(btn) {
  var body = btn.closest('.mc-msg').querySelector('.mc-msg-b');
  var text = body.textContent.trim();
  document.querySelector('.mc-pin-body').innerHTML = '<b>Anclado</b> ' + esc(text.substring(0, 80));
  document.getElementById('pinBar').hidden = false;
  toast('Mensaje anclado');
}
function delMsg(btn) {
  btn.closest('.mc-msg').remove();
  toast('Mensaje eliminado');
}
function promptPin() {
  var t = prompt('Mensaje para anclar:');
  if (t && t.trim()) {
    document.querySelector('.mc-pin-body').innerHTML = '<b>Moderador</b> ' + esc(t.trim());
    toast('Pin actualizado');
  }
}
function unpin() {
  document.getElementById('pinBar').hidden = true;
  toast('Pin removido');
}

// Ban flow
var _banTarget = '';
function banMsg(name) {
  _banTarget = name;
  document.getElementById('banWho').textContent = name;
  document.getElementById('banConfirm').hidden = false;
}
function cancelBan() { document.getElementById('banConfirm').hidden = true; }
function confirmBan() {
  document.getElementById('banConfirm').hidden = true;
  BANNED.push({ n: _banTarget });
  renderBanned();
  toast('Baneado: ' + _banTarget);
}

// ── Render Q&A ────────────────────────────────────────
function renderQnA() {
  var el = document.getElementById('qnaList');
  if (!QNA.length) {
    el.innerHTML = '<div class="mc-qna-empty"><span class="icon">forum</span>Sin preguntas pendientes</div>';
    return;
  }
  el.innerHTML = QNA.map(function(q) {
    var p = NAMES[q.w];
    return '<div class="qc">' +
      '<div class="qc-body">' + esc(q.q) + '</div>' +
      '<div class="qc-meta">' +
        '<div class="qc-info">' +
          '<span class="who">' + esc(p.n) + '</span>' +
          '<span>' + q.t + '</span>' +
          (q.v > 0 ? '<span class="votes"><span class="icon">arrow_upward</span>' + q.v + '</span>' : '') +
        '</div>' +
        '<div class="qc-acts">' +
          '<button class="qc-ab project" title="Proyectar en pantalla" onclick="projectQna(\'' + esc(q.q.substring(0,40)) + '\')"><span class="icon">cast</span></button>' +
          '<button class="qc-ab" onclick="toast(\'Aprobada\')"><span class="icon">check</span>Aprobar</button>' +
          '<button class="qc-ab" onclick="toast(\'Respondida\')"><span class="icon">done_all</span>Respondida</button>' +
          '<button class="qc-ab" onclick="toast(\'Descartada\')"><span class="icon">close</span></button>' +
        '</div>' +
      '</div>' +
    '</div>';
  }).join('');
}
renderQnA();

function projectQna(text) {
  showProjection('Pregunta: ' + text + '...');
  updateLed('qna', text);
  toast('Pregunta proyectada');
}

// ── Render Polls ──────────────────────────────────────
function renderPolls() {
  var el = document.getElementById('pollsList');
  if (!POLLS.length) {
    el.innerHTML = '<div class="mc-polls-empty"><span class="icon">bar_chart</span>Sin encuestas creadas</div>';
    return;
  }
  el.innerHTML = POLLS.map(function(p) {
    var cls = 'pc' + (p.status === 'live' ? ' live' : '') + (p.status === 'closed' ? ' closed' : '');
    var stLabel = p.status === 'live' ? 'En vivo' : (p.status === 'draft' ? 'Borrador' : 'Cerrada');
    var content = '';

    // Multiple choice
    if (p.type === 'mc') {
      var mx = Math.max.apply(null, p.opts.map(function(o) { return o.p; }));
      content = p.opts.map(function(o) {
        var lead = o.p === mx && p.status === 'live';
        return '<div class="pc-opt' + (lead ? ' lead' : '') + '">' +
          '<div class="pc-opt-bar"><div class="pc-opt-fill" style="width:' + o.p + '%"></div></div>' +
          '<div class="pc-opt-l">' + o.l + '</div>' +
          '<div class="pc-opt-p">' + o.p + '%</div>' +
        '</div>';
      }).join('');
    }

    // Star rating
    if (p.type === 'star') {
      var stars = '';
      for (var i = 0; i < 5; i++) stars += i < Math.round(p.avg) ? '\u2605' : '\u2606';
      var dMax = Math.max(p.dist[5], p.dist[4], p.dist[3], p.dist[2], p.dist[1]);
      var dH = '';
      for (var s = 5; s >= 1; s--) {
        var c = p.dist[s], pW = dMax > 0 ? Math.round(c / dMax * 100) : 0;
        dH += '<div class="pc-dist-r">' +
          '<span class="pc-dist-l">' + s + '\u2605</span>' +
          '<div class="pc-dist-bar"><div class="pc-dist-fill" style="width:' + pW + '%"></div></div>' +
          '<span class="pc-dist-c">' + c + '</span>' +
        '</div>';
      }
      content = '<div class="pc-stars">' +
        '<div class="pc-stars-row"><span class="pc-stars-avg">' + p.avg + '</span><span class="pc-stars-of">/ 5</span><span class="pc-stars-v">' + stars + '</span></div>' +
        '<div class="pc-dist">' + dH + '</div></div>';
    }

    // Open text
    if (p.type === 'text') {
      content = '<div class="pc-resp">' + p.answers.map(function(a) {
        return '<div class="pc-resp-item">' + esc(a) + '</div>';
      }).join('') + '</div>';
    }

    // Footer
    var foot = '<div class="pc-foot"><div class="pc-meta">';
    if (p.status === 'live') {
      foot += '<b>' + p.votes.toLocaleString() + '</b> ' + (p.type === 'mc' ? 'votos' : 'respuestas');
      if (p.timeLeft) foot += ' &middot; cierra en <b>' + p.timeLeft + '</b>';
      foot += '</div><div class="pc-acts">' +
        '<button class="mc-btn-g" onclick="projectPoll(\'' + esc(p.title.substring(0,30)) + '\',\'' + p.type + '\',' + (p.avg || 0) + ')"><span class="icon">cast</span> Proyectar</button>' +
        '<button class="mc-btn-g" onclick="toast(\'Encuesta cerrada\')">Cerrar</button></div>';
    } else if (p.status === 'draft') {
      foot += 'Listo para lanzar</div><div class="pc-acts">' +
        '<button class="mc-btn-g">Editar</button><button class="mc-btn-w"><span class="icon">bolt</span> Lanzar</button></div>';
    } else {
      foot += '<b>' + p.votes + '</b> respuestas &middot; cerrada</div><div class="pc-acts">' +
        '<button class="mc-btn-g" onclick="projectPoll(\'' + esc(p.title.substring(0,30)) + '\',\'' + p.type + '\',' + (p.avg || 0) + ')"><span class="icon">cast</span> Proyectar</button>' +
        '<button class="mc-btn-g"><span class="icon">download</span> CSV</button></div>';
    }
    foot += '</div>';

    return '<div class="' + cls + '"><div class="pc-head"><div><div class="pc-title">' + esc(p.title) + '</div><div class="pc-sub">' + p.sub + '</div></div><span class="pc-st ' + p.status + '">' + stLabel + '</span></div>' + content + foot + '</div>';
  }).join('');
}
renderPolls();

// ���─ Projection ────────────────────────────────────────
function showProjection(label) {
  document.getElementById('projectionLabel').textContent = label;
  document.getElementById('projectionBar').hidden = false;
}
function stopProjection() {
  document.getElementById('projectionBar').hidden = true;
  document.getElementById('ledBody').innerHTML = '<span class="mc-led-standby">Standby</span>';
  toast('Proyeccion detenida');
}
function projectPoll(title, type, avg) {
  showProjection(title + '...');
  updateLed(type, title, avg);
  toast('Encuesta proyectada');
}
function updateLed(type, title, avg) {
  var body = document.getElementById('ledBody');
  if (type === 'star') {
    body.innerHTML = '<div class="mc-led-active"><div class="led-title">' + esc(title) + '</div><div class="led-avg">' + (avg || '4.3') + ' / 5 \u2605</div></div>';
  } else if (type === 'mc') {
    body.innerHTML = '<div class="mc-led-active"><div class="led-title">' + esc(title) + '</div>' +
      '<div class="led-bar"><div class="led-bar-fill" style="width:52%"></div></div>' +
      '<div style="margin-top:2px">52% — Pagos QR</div></div>';
  } else if (type === 'qna') {
    body.innerHTML = '<div class="mc-led-active"><div class="led-title">Pregunta destacada</div><div style="margin-top:4px;font-size:8px;line-height:1.3">' + esc(title) + '...</div></div>';
  } else {
    body.innerHTML = '<div class="mc-led-active"><div class="led-title">' + esc(title) + '</div><div style="margin-top:4px">6 respuestas</div></div>';
  }
}

// ── Render Timeline ───────────────────────────────────
document.getElementById('timeline').innerHTML = TL.map(function(t) {
  return '<div class="mc-tl-i"><div class="mc-tl-d ' + t.d + '"></div><div><div class="mc-tl-b">' + t.b + '</div><div class="mc-tl-t">' + t.t + '</div></div></div>';
}).join('');

// ── Render Banned ─────────────────────────────────────
function renderBanned() {
  var el = document.getElementById('banList');
  document.getElementById('banCt').textContent = BANNED.length;
  if (!BANNED.length) { el.innerHTML = '<div style="padding:12px 6px;text-align:center;font-size:10px;color:var(--t5)">Sin baneados</div>'; return; }
  el.innerHTML = BANNED.map(function(b) {
    return '<div class="mc-ban-item"><div class="av">' + ini(b.n.replace(/_/g, ' ')) + '</div><span class="nm">' + esc(b.n) + '</span><button class="ub">Reactivar</button></div>';
  }).join('');
}
renderBanned();

// ── Tabs ──────────────────────────────────────────────
function setTab(name) {
  document.querySelectorAll('.mc-tab').forEach(function(t) { t.classList.toggle('on', t.dataset.tab === name); });
  document.querySelectorAll('.mc-pnl').forEach(function(p) { p.hidden = p.dataset.panel !== name; });
}
document.querySelectorAll('.mc-tab').forEach(function(t) {
  t.addEventListener('click', function() { setTab(t.dataset.tab); });
});

// ── Toggles ───────────────────────────────────────────
document.getElementById('swEmoji').addEventListener('click', function() {
  this.classList.toggle('on');
  toast(this.classList.contains('on') ? 'Emoji only activado — guardado' : 'Emoji only desactivado — guardado');
});
document.getElementById('selSlow').addEventListener('change', function() {
  toast('Slow mode: ' + (this.value === '0' ? 'Off' : this.value + 's') + ' — guardado');
});

document.querySelectorAll('.mc-qspd').forEach(function(b) {
  b.addEventListener('click', function() {
    document.querySelectorAll('.mc-qspd').forEach(function(x) { x.classList.remove('on'); });
    b.classList.add('on');
  });
});

document.getElementById('btnPause').addEventListener('click', function() {
  this.classList.toggle('on');
  var paused = this.classList.contains('on');
  this.innerHTML = paused ? '<span class="icon">play_arrow</span> Reanudar' : '<span class="icon">pause</span> Pausar';
  toast(paused ? 'Chat pausado' : 'Chat reanudado');
});

document.querySelectorAll('.mc-qf').forEach(function(b) {
  b.addEventListener('click', function() {
    document.querySelectorAll('.mc-qf').forEach(function(x) { x.classList.remove('on'); });
    b.classList.add('on');
  });
});

function updatePollFormType() {
  var val = document.getElementById('pollType').value;
  var isMC = val.startsWith('mc');
  document.getElementById('pollOptsContainer').style.display = isMC ? 'block' : 'none';
  document.getElementById('pollAddOpt').style.display = isMC ? 'flex' : 'none';
}
document.getElementById('pollType').addEventListener('change', updatePollFormType);
document.getElementById('pollNewBtn').addEventListener('click', function() {
  document.getElementById('pollOverlay').hidden = false;
  document.getElementById('pollType').value = 'mc_single';
  updatePollFormType();
});
document.getElementById('pollCloseBtn').addEventListener('click', closePollForm);
function closePollForm() {
  document.getElementById('pollOverlay').hidden = true;
}

// Save button states
function doSave() {
  var btn = document.getElementById('saveBtn');
  btn.classList.add('saving');
  btn.innerHTML = '<span class="icon">hourglass_empty</span> Guardando...';
  setTimeout(function() {
    btn.classList.remove('saving');
    btn.classList.add('saved');
    btn.innerHTML = '<span class="icon">check</span> Guardado';
    toast('Cambios aplicados en vivo');
    setTimeout(function() {
      btn.classList.remove('saved');
      btn.innerHTML = '<span class="icon">bolt</span> Guardar';
    }, 2000);
  }, 800);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
  if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') return;
  if (e.key === '1') setTab('chat');
  if (e.key === '2') setTab('qna');
  if (e.key === '3') setTab('polls');
  if (e.key === '4') setTab('custom');
  if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); doSave(); }
});

// ── Live timer ────────────────────────────────────────
var _sec = 1 * 3600 + 24 * 60 + 8;
function fmt(s) { return String(Math.floor(s / 3600)).padStart(2, '0') + ':' + String(Math.floor(s % 3600 / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0'); }
setInterval(function() {
  _sec++;
  var s = fmt(_sec);
  document.getElementById('liveTime').textContent = s;
  document.getElementById('sbTime').textContent = s;
}, 1000);

// MPM flicker
var _mpm = 42;
setInterval(function() {
  _mpm += Math.round((Math.random() - 0.5) * 4);
  _mpm = Math.max(30, Math.min(58, _mpm));
  document.getElementById('mpmVal').textContent = _mpm;
}, 2500);
