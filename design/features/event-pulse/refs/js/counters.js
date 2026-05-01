/* ============================================================
   Event Pulse — Live Counters
   Simulates real-time counter updates (demo).
   In production: driven by socket events.
   ============================================================ */

(function () {
  'use strict';

  function setK(id, val) {
    var el = document.getElementById(id);
    if (!el) return;
    el.textContent = typeof val === 'number' ? val.toLocaleString() : val;
    var corner = el.closest('.corner');
    if (corner) {
      corner.classList.remove('tick');
      void corner.offsetWidth;
      corner.classList.add('tick');
    }
  }

  setInterval(function () {
    if (Math.random() < 0.55) {
      EP.state.ci++;
      setK('m-ci', EP.state.ci);
    }
    EP.state.msg += Math.floor(Math.random() * 3) + 1;
    setK('m-msg', EP.state.msg);
    EP.state.on += Math.random() < 0.5 ? 1 : -1;
    EP.state.on = Math.max(170, Math.min(210, EP.state.on));
    setK('m-on', EP.state.on);
    if (Math.random() < 0.3) {
      EP.state.conn++;
      setK('m-conn', EP.state.conn);
    }
    if (Math.random() < 0.2) {
      EP.state.ratings++;
      setK('m-ratings', EP.state.ratings);
    }
  }, 2600);

  setInterval(function () {
    if (Math.random() < 0.5) {
      EP.state.ld++;
      setK('m-ld', EP.state.ld);
    }
  }, 6200);
})();
