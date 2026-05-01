/* ============================================================
   Event Pulse — Moment Engine
   Max 1 moment in DOM. Moment killer on section change / ESC.
   Spacing: 8s between moments. Only plays during ambient.
   ============================================================ */

(function () {
  'use strict';

  var stageEl = document.getElementById('momentStage');
  var ambientEl = document.getElementById('ambient');
  var corners = document.querySelectorAll('.corner');
  var bottomCenter = document.getElementById('bottomCenter');
  var activeMoment = null;
  var momentTimer = null;
  var showing = false;

  function dimAmbient(dim) {
    ambientEl.classList.toggle('dimmed', dim);
    corners.forEach(function (c) {
      if (!c.classList.contains('section-hidden')) {
        c.classList.toggle('dimmed', dim);
      }
    });
    if (bottomCenter) bottomCenter.classList.toggle('dimmed', dim);
  }

  // Kill any active moment immediately
  window.killMoment = function () {
    if (activeMoment) {
      activeMoment.remove();
      activeMoment = null;
    }
    showing = false;
    dimAmbient(false);
    clearTimeout(momentTimer);
  };

  function playMoment(html) {
    if (showing || window.currentSection !== 'ambient') return;
    showing = true;
    dimAmbient(true);

    var node = document.createElement('div');
    node.className = 'moment show';
    node.innerHTML = html;
    stageEl.appendChild(node);
    activeMoment = node;

    momentTimer = setTimeout(function () {
      if (activeMoment === node) {
        node.remove();
        activeMoment = null;
      }
      dimAmbient(false);
      showing = false;
    }, 5000);
  }

  // --- Moment builders ---

  function momentCheckin() {
    var n = EP.rName(), a = EP.AV(EP.rAv()), r = EP.pick(EP.ROOMS);
    return '<div class="m-checkin"><div class="m-eyebrow">Nuevo check-in</div>' +
      '<div class="m-avatar-xl" style="background-image:url(\'' + a + '\')"></div>' +
      '<div class="m-big-name">' + n + '</div>' +
      '<div class="m-sub">acaba de llegar &middot; se dirige a <b>' + r.name + '</b></div></div>';
  }

  function momentLead() {
    var s = EP.pick(EP.SPONSORS), n = EP.rName(), r = EP.pick(EP.ROLES);
    var cl = s.color === 'teal' ? 'teal' : s.color === 'plat' ? 'platinum' : '';
    return '<div class="m-lead">' +
      '<div class="side"><div class="m-avatar-xl" style="background-image:url(\'' + EP.AV(EP.rAv()) + '\')"></div><div class="person-name">' + n + '</div><div class="person-role">' + r + '</div></div>' +
      '<div class="center"><div class="connect-line"></div><div class="plus-big"><span>+</span>1</div><div class="lead-label">Lead capturado</div></div>' +
      '<div class="side"><div class="sponsor-badge ' + cl + '">' + s.mark + '</div><div class="person-name">' + s.name + '</div><div class="person-role">Sponsor</div></div></div>';
  }

  function momentMatch() {
    var a = EP.rName(), b = EP.rName();
    return '<div class="m-match"><div class="m-eyebrow">Conexion networking</div>' +
      '<div class="pair"><div class="av left" style="background-image:url(\'' + EP.AV(EP.rAv()) + '\')"></div><div class="spark"></div><div class="spark-core"></div><div class="av right" style="background-image:url(\'' + EP.AV(EP.rAv()) + '\')"></div></div>' +
      '<div class="names">' + a + '<span class="amp">&amp;</span>' + b + '</div>' +
      '<div class="m-sub" style="margin-top:24px">acaban de conectar en <b>' + EP.pick(['Track IA', 'Track Producto', 'Circulo Founders']) + '</b></div></div>';
  }

  function momentRating() {
    var r = EP.pick(EP.SESSIONS), sc = (4.6 + Math.random() * 0.4).toFixed(1);
    var stars = '';
    for (var i = 0; i < 5; i++) {
      stars += '<svg class="star" viewBox="0 0 24 24" fill="currentColor" style="animation-delay:' + (0.15 + i * 0.15) + 's"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>';
    }
    return '<div class="m-rating"><div class="m-eyebrow">Evaluacion de sesion</div>' +
      '<div class="stars">' + stars + '</div>' +
      '<div class="score">' + sc + '<span class="of"> / 5.0</span></div>' +
      '<div class="session">' + r.title + '</div>' +
      '<div class="speaker">' + r.speaker + ' &middot; ' + r.role + '</div></div>';
  }

  function momentPoints(gold) {
    var n = EP.rName();
    var pts = gold ? 250 : EP.pick([25, 50, 75, 100]);
    var reason = gold ? 'primer lugar del leaderboard' : EP.pick(['circuito de sponsors completo', '3 trivias correctas', '5 badges escaneados', 'ruleta en vivo']);
    return '<div class="m-points ' + (gold ? 'gold' : '') + '">' +
      '<div class="m-eyebrow"' + (gold ? ' style="color:var(--platinum)"' : '') + '>' + (gold ? 'Leaderboard — Nuevo #1' : 'Gamificacion') + '</div>' +
      '<div class="burst">+' + pts + '</div>' +
      '<div class="who">' + n + '</div>' +
      '<div class="reason">' + reason + ' &middot; <b style="color:var(--ink)">pts</b></div>' +
      (gold ? '<div class="badge">Platinum tier</div>' : '') + '</div>';
  }

  function momentMessage() {
    var r = EP.pick(EP.ROOMS), q = EP.pick(EP.QUOTES), n = EP.rName();
    return '<div class="m-message">' +
      '<div class="av" style="background-image:url(\'' + EP.AV(EP.rAv()) + '\')"></div>' +
      '<div><div class="bubble">"' + q + '"</div>' +
      '<div class="attr"><b>' + n + '</b> &nbsp;&middot;&nbsp; en <b>' + r.name + '</b></div></div></div>';
  }

  function momentSalon() {
    var liveRooms = EP.ROOMS.filter(function (x) { return x.live; });
    var r = EP.pick(liveRooms);
    return '<div class="m-salon"><div class="room">' + r.name + ' &middot; ahora</div>' +
      '<h2>' + r.title + '</h2>' +
      '<div class="speaker-row"><div class="av" style="background-image:url(\'' + EP.AV(r.av) + '\')"></div><div class="info"><div class="name">' + r.speaker + '</div><div class="role">' + r.role + '</div></div></div>' +
      '<div class="live-tag"><span class="dot"></span>En vivo &middot; ' + (r.pres + r.virt) + ' personas</div></div>';
  }

  // Weighted selection
  var moments = [
    { fn: momentCheckin, w: 3 },
    { fn: momentLead, w: 3 },
    { fn: momentMatch, w: 2 },
    { fn: momentRating, w: 1 },
    { fn: function () { return momentPoints(false); }, w: 2 },
    { fn: function () { return momentPoints(true); }, w: 0.4 },
    { fn: momentMessage, w: 1.5 },
    { fn: momentSalon, w: 1 }
  ];

  function weightedPick() {
    var total = moments.reduce(function (s, m) { return s + m.w; }, 0);
    var r = Math.random() * total;
    for (var i = 0; i < moments.length; i++) {
      r -= moments[i].w;
      if (r <= 0) return moments[i];
    }
    return moments[0];
  }

  // Start: first moment after 1s, then every 8s
  setTimeout(function () { playMoment(weightedPick().fn()); }, 1000);

  setInterval(function () {
    if (window.currentSection === 'ambient') {
      playMoment(weightedPick().fn());
    }
  }, 8000);
})();
