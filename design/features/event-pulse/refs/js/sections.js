/* ============================================================
   Event Pulse — Sections
   Navigation + 7 section builders with HTML cache.
   display:none for unmounted sections (no invisible GPU layers).
   ============================================================ */

(function () {
  'use strict';

  window.currentSection = 'ambient';

  var ambientEl = document.getElementById('ambient');
  var corners = document.querySelectorAll('.corner');
  var bottomCenter = document.getElementById('bottomCenter');
  var frameBot = document.getElementById('frameBot');
  var pills = document.querySelectorAll('.nav-pill');

  // Cache: once built, reuse HTML
  var cache = {};

  window.showSection = function (id) {
    // Kill any active moment
    if (typeof window.killMoment === 'function') window.killMoment();

    window.currentSection = id;
    var isAmb = id === 'ambient';

    // Ambient
    ambientEl.classList.toggle('hidden', !isAmb);

    // Corners + bottom center + frame-bot
    corners.forEach(function (c) {
      c.classList.toggle('dimmed', !isAmb);
      c.classList.toggle('section-hidden', !isAmb);
    });
    bottomCenter.classList.toggle('dimmed', !isAmb);
    frameBot.classList.toggle('dimmed', !isAmb);

    // Sections: unmount all, mount target
    document.querySelectorAll('.section').forEach(function (s) {
      s.classList.remove('active');
      // Delay unmount for transition
      setTimeout(function () {
        if (!s.classList.contains('active')) s.classList.remove('mounted');
      }, 500);
    });

    if (!isAmb) {
      var sec = document.getElementById('sec-' + id);
      if (sec) {
        populateSection(id);
        sec.classList.add('mounted');
        // Force reflow before adding active for transition
        void sec.offsetWidth;
        sec.classList.add('active');
      }
    }

    // Pills
    pills.forEach(function (p) {
      p.classList.toggle('active', p.dataset.sec === id);
    });
  };

  // ESC handler
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') window.showSection('ambient');
  });

  // --- Populate with cache ---
  function populateSection(id) {
    var el = document.getElementById('sec-' + id);
    if (!el) return;
    var body = el.querySelector('.section-body > *');
    if (!body) return;

    // If cached, restore and skip rebuild
    if (cache[id]) {
      body.innerHTML = cache[id];
      return;
    }

    // Build fresh
    switch (id) {
      case 'charlas': buildCharlas(body); break;
      case 'checkins': buildCheckins(body); break;
      case 'leads': buildLeads(body); break;
      case 'networking': buildNetworking(body); break;
      case 'social': buildSocial(body); break;
      case 'leaderboard': buildLeaderboard(body); break;
      case 'ratings': buildRatings(body); break;
    }

    cache[id] = body.innerHTML;
  }

  // ===== BUILDERS =====

  function buildCharlas(el) {
    el.innerHTML = EP.ROOMS.map(function (r, i) {
      var total = r.pres + r.virt;
      if (!r.live) {
        return '<div class="room-card idle" style="animation-delay:' + (i * 0.1) + 's"><div class="room-idle-label">Proximo &middot; 11:15</div><div class="room-top"><div class="room-name">' + r.name + '</div></div></div>';
      }
      var avs = '';
      var showPres = Math.min(r.pres, 10);
      var showVirt = Math.min(r.virt, 4);
      for (var j = 0; j < showPres; j++) avs += '<div class="room-av" style="background-image:url(\'' + EP.AV(EP.rAv()) + '\');animation-delay:' + (j * 50) + 'ms"></div>';
      for (var k = 0; k < showVirt; k++) avs += '<div class="room-av virtual" style="background-image:url(\'' + EP.AV(EP.rAv()) + '\');animation-delay:' + ((showPres + k) * 50) + 'ms"></div>';
      if (total > 14) avs += '<div class="room-av overflow" style="animation-delay:700ms">+' + (total - 14) + '</div>';
      var pct = Math.round(total / r.cap * 100);
      var pp = total ? Math.round(r.pres / total * 100) : 0;
      return '<div class="room-card" style="animation-delay:' + (i * 0.1) + 's">' +
        '<div class="room-top"><div class="room-name"><span class="live-dot"></span>' + r.name + '</div><div class="room-count">' + total + '</div></div>' +
        '<div class="room-session">' + r.title + '</div>' +
        '<div class="room-speaker"><div class="av" style="background-image:url(\'' + EP.AV(r.av) + '\')"></div><div><div class="name">' + r.speaker + '</div><div class="role">' + r.role + '</div></div></div>' +
        '<div class="room-people">' + avs + '</div>' +
        '<div class="room-bar"><div class="room-bar-inner" style="width:' + pct + '%"><div class="room-bar-pres" style="width:' + pp + '%"></div><div class="room-bar-virt" style="width:' + (100 - pp) + '%"></div></div></div>' +
        '<div class="room-legend"><span class="p">' + r.pres + ' presencial</span><span class="v">' + r.virt + ' virtual</span></div></div>';
    }).join('');
  }

  function buildCheckins(el) {
    var arr = [
      { name: EP.rName(), role: EP.pick(EP.ROLES), time: 'hace 1 min', type: 'pres', av: EP.rAv() },
      { name: EP.rName(), role: EP.pick(EP.ROLES), time: 'hace 4 min', type: 'pres', av: EP.rAv() },
      { name: EP.rName(), role: EP.pick(EP.ROLES), time: 'hace 7 min', type: 'virt', av: EP.rAv() },
      { name: EP.rName(), role: EP.pick(EP.ROLES), time: 'hace 12 min', type: 'pres', av: EP.rAv() }
    ];
    var tl = [
      { time: '09:00', count: 45, pct: 50 },
      { time: '09:30', count: 89, pct: 100 },
      { time: '10:00', count: 72, pct: 81 },
      { time: '10:30', count: 38, pct: 43, now: true }
    ];
    el.innerHTML =
      '<div class="checkin-arrivals">' + arr.map(function (a, i) {
        return '<div class="arrival-card" style="animation-delay:' + (i * 0.15) + 's">' +
          '<div class="arrival-av" style="background-image:url(\'' + EP.AV(a.av) + '\')"></div>' +
          '<div class="arrival-info"><div class="arrival-name">' + a.name + '</div><div class="arrival-role">' + a.role + '</div><div class="arrival-time">' + a.time + '</div></div>' +
          '<div class="arrival-badge ' + a.type + '">' + (a.type === 'pres' ? 'Presencial' : 'Virtual') + '</div></div>';
      }).join('') + '</div>' +
      '<div class="checkin-right">' +
        '<div class="checkin-stats"><div class="cs-card"><div class="cs-val">241</div><div class="cs-label">Presenciales</div></div><div class="cs-card"><div class="cs-val" style="color:var(--blue)">106</div><div class="cs-label">Virtuales</div></div><div class="cs-card"><div class="cs-val" style="color:var(--green)">87%</div><div class="cs-label">Tasa</div></div></div>' +
        '<div class="timeline"><div class="timeline-title">Llegadas por hora</div>' + tl.map(function (t) {
          return '<div class="tl-row ' + (t.now ? 'now' : '') + '"><div class="tl-time">' + t.time + '</div><div class="tl-bar"><div class="tl-fill" style="width:' + t.pct + '%"></div></div><div class="tl-count">' + t.count + '</div></div>';
        }).join('') + '</div>' +
      '</div>';
  }

  function buildLeads(el) {
    var mx = Math.max.apply(null, EP.SPONSORS.map(function (s) { return s.leads; }));
    el.innerHTML = EP.SPONSORS.map(function (s, i) {
      var feat = i < 2;
      var avs = '';
      var sh = Math.min(s.leads, 5);
      for (var j = 0; j < sh; j++) avs += '<div class="lead-av" style="background-image:url(\'' + EP.AV(EP.rAv()) + '\')"></div>';
      if (s.leads > sh) avs += '<div class="lead-more">+' + (s.leads - sh) + '</div>';
      return '<div class="lead-card ' + (feat ? 'featured' : '') + '" style="animation-delay:' + (i * 0.1) + 's">' +
        '<div class="lead-top"><div class="lead-logo ' + s.color + '">' + s.mark + '</div><div class="lead-name">' + s.name + '</div><div class="lead-count">' + s.leads + '</div></div>' +
        (feat ? '<div class="lead-bar"><div class="lead-bar-fill" style="width:' + Math.round(s.leads / mx * 100) + '%"></div></div><div class="lead-recent"><div class="lead-recent-label">Recientes</div><div class="lead-av-row">' + avs + '</div><div class="lead-last">Ultimo: <b>' + EP.rName() + '</b> &middot; ' + EP.pick(['ahora', '2m', '5m']) + '</div></div>' : '') + '</div>';
    }).join('');
  }

  function buildNetworking(el) {
    var pairs = [];
    for (var i = 0; i < 3; i++) pairs.push({ a: { name: EP.rName(), av: EP.rAv() }, b: { name: EP.rName(), av: EP.rAv() }, time: EP.pick(['hace 3 min', 'hace 7 min', 'hace 12 min']) });
    var mA = { name: EP.rName(), role: EP.pick(EP.ROLES), av: EP.rAv(), int: EP.pick(EP.INTERESTS) + ', ' + EP.pick(EP.INTERESTS) };
    var mB = { name: EP.rName(), role: EP.pick(EP.ROLES), av: EP.rAv(), int: EP.pick(EP.INTERESTS) + ', ' + EP.pick(EP.INTERESTS) };

    el.innerHTML =
      '<div class="net-featured">' +
        '<div class="net-person"><div class="net-av" style="background-image:url(\'' + EP.AV(mA.av) + '\')"></div><div class="net-name">' + mA.name + '</div><div class="net-role">' + mA.role + '</div><div class="net-interests">' + mA.int.split(', ').map(function (t) { return '<div class="net-tag">' + t + '</div>'; }).join('') + '</div></div>' +
        '<div class="net-center" style="min-width:180px"><div class="conn-line"><div class="conn-line-bg"></div><div class="conn-particle"></div><div class="conn-particle reverse"></div></div><div class="net-spark-text">Match!</div><div class="net-spark-sub">Conexion establecida</div></div>' +
        '<div class="net-person"><div class="net-av" style="background-image:url(\'' + EP.AV(mB.av) + '\')"></div><div class="net-name">' + mB.name + '</div><div class="net-role">' + mB.role + '</div><div class="net-interests">' + mB.int.split(', ').map(function (t) { return '<div class="net-tag">' + t + '</div>'; }).join('') + '</div></div>' +
      '</div>' +
      '<div class="net-recent">' + pairs.map(function (p) {
        return '<div class="net-pair"><div class="mini" style="background-image:url(\'' + EP.AV(p.a.av) + '\')"></div><div class="arrow">&harr;</div><div class="mini" style="background-image:url(\'' + EP.AV(p.b.av) + '\')"></div><div class="net-pair-info"><div class="names">' + p.a.name.split(' ')[0] + ' & ' + p.b.name.split(' ')[0] + '</div><div class="time">' + p.time + '</div></div></div>';
      }).join('') + '</div>' +
      '<div class="net-stats"><div class="net-stat"><div class="val">42</div><div class="label">Matches hoy</div></div><div class="net-stat"><div class="val">84%</div><div class="label">Tasa aceptacion</div></div><div class="net-stat"><div class="val">3.2</div><div class="label">Promedio / persona</div></div></div>';
  }

  function buildSocial(el) {
    var posts = [
      { text: EP.SOCIAL_QUOTES[0], img: EP.IMGS[0], likes: 18, comments: 5, name: EP.rName(), av: EP.rAv(), hero: true },
      { text: EP.SOCIAL_QUOTES[1], img: EP.IMGS[1], likes: 12, comments: 3, name: EP.rName(), av: EP.rAv() },
      { text: EP.SOCIAL_QUOTES[2], img: EP.IMGS[2], likes: 9, comments: 2, name: EP.rName(), av: EP.rAv() },
      { text: EP.SOCIAL_QUOTES[3], img: null, likes: 7, comments: 1, name: EP.rName(), av: EP.rAv() },
      { text: EP.SOCIAL_QUOTES[4], img: EP.IMGS[3], likes: 5, comments: 0, name: EP.rName(), av: EP.rAv() }
    ];
    el.innerHTML = posts.map(function (p, i) {
      return '<div class="social-post ' + (p.hero ? 'hero' : '') + '" style="animation-delay:' + (i * 0.1) + 's">' +
        (p.img ? '<div class="social-img" style="background-image:url(\'' + p.img + '\')"></div>' : '') +
        '<div class="social-content"><div class="social-author"><div class="av" style="background-image:url(\'' + EP.AV(p.av) + '\')"></div><div class="nm">' + p.name + '</div></div>' +
        '<div class="social-text">' + p.text + '</div>' +
        '<div class="social-meta"><span class="social-heart">&hearts; ' + p.likes + '</span><span>' + p.comments + ' comentarios</span></div></div></div>';
    }).join('');
  }

  function buildLeaderboard(el) {
    var top = [
      { name: EP.rName(), pts: 850, av: EP.rAv() },
      { name: EP.rName(), pts: 720, av: EP.rAv() },
      { name: EP.rName(), pts: 680, av: EP.rAv() }
    ];
    var rest = [];
    for (var i = 0; i < 7; i++) rest.push({ name: EP.rName(), pts: 520 - i * 40, av: EP.rAv(), action: EP.pick(['trivia correcta', 'circuito sponsors', '5 badges', 'ruleta en vivo', '3 conexiones']) });

    el.innerHTML =
      '<div class="lb-podium"><div class="podium-row">' +
        '<div class="podium-block second"><div class="podium-av" style="background-image:url(\'' + EP.AV(top[1].av) + '\')"></div><div class="podium-name">' + top[1].name + '</div><div class="podium-pts">' + top[1].pts + ' pts</div><div class="podium-pillar"><div class="podium-rank">#2</div></div></div>' +
        '<div class="podium-block first"><div class="podium-av" style="background-image:url(\'' + EP.AV(top[0].av) + '\')"></div><div class="podium-name">' + top[0].name + '</div><div class="podium-pts">' + top[0].pts + ' pts</div><div class="podium-pillar"><div class="podium-rank">#1</div></div></div>' +
        '<div class="podium-block third"><div class="podium-av" style="background-image:url(\'' + EP.AV(top[2].av) + '\')"></div><div class="podium-name">' + top[2].name + '</div><div class="podium-pts">' + top[2].pts + ' pts</div><div class="podium-pillar"><div class="podium-rank">#3</div></div></div>' +
      '</div></div>' +
      '<div class="lb-list">' + rest.map(function (r, i) {
        return '<div class="lb-row"><div class="lb-rank">#' + (i + 4) + '</div><div class="lb-av" style="background-image:url(\'' + EP.AV(r.av) + '\')"></div><div class="lb-info"><div class="lb-name">' + r.name + '</div><div class="lb-action">Ultimo: ' + r.action + '</div></div><div class="lb-dots"></div><div class="lb-pts">' + r.pts + '</div></div>';
      }).join('') +
      '<div class="lb-live"><div class="pip"></div><div class="lb-live-text"><b>' + EP.rName() + '</b> acaba de ganar <b style="color:var(--teal)">+50 pts</b> por trivia correcta</div></div></div>';
  }

  function buildRatings(el) {
    var hero = EP.SESSIONS[0];
    var starSVG = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>';

    el.innerHTML =
      '<div class="rating-hero">' +
        '<div class="rating-stars">' + Array(5).fill(0).map(function () { return '<div class="rating-star">' + starSVG + '</div>'; }).join('') + '</div>' +
        '<div class="rating-score">' + hero.score + '<span class="of"> / 5.0</span></div>' +
        '<div class="rating-session">' + hero.title + '</div>' +
        '<div class="rating-speaker"><div class="av" style="background-image:url(\'' + EP.AV(hero.av) + '\')"></div><div class="info"><div class="nm">' + hero.speaker + '</div><div class="rl">' + hero.role + '</div></div></div>' +
        '<div class="rating-count">' + hero.count + ' evaluaciones</div>' +
      '</div>' +
      '<div class="ratings-list">' +
        EP.SESSIONS.slice(1).map(function (s, i) {
          var stars = '';
          for (var j = 0; j < 5; j++) {
            var filled = j < Math.round(s.score);
            stars += '<svg viewBox="0 0 24 24" fill="' + (filled ? 'currentColor' : 'none') + '" stroke="' + (filled ? 'none' : 'currentColor') + '" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>';
          }
          return '<div class="rating-card" style="animation-delay:' + (i * 0.1) + 's"><div class="rc-av" style="background-image:url(\'' + EP.AV(s.av) + '\')"></div><div class="rc-info"><div class="rc-title">' + s.title + '</div><div class="rc-spk">' + s.speaker + ' &middot; ' + s.role + '</div></div><div class="rc-score"><div class="rc-stars">' + stars + '</div><div class="rc-val">' + s.score + '</div><div class="rc-count">' + s.count + ' ratings</div></div></div>';
        }).join('') +
        '<div class="rating-avg"><div class="val">4.5 / 5.0 &middot; Promedio del evento</div><div class="label">"Mejor que el 92% de eventos similares"</div></div>' +
      '</div>';
  }
})();
