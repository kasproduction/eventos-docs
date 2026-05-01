/* ============================================================
   Event Pulse — Clock
   ============================================================ */

(function () {
  'use strict';

  var hhEl = document.getElementById('hh');
  var mmEl = document.getElementById('mm');
  var ssEl = document.getElementById('ss');
  var datelineEl = document.getElementById('dateline');
  var lastMM = null;

  function pad(n) { return String(n).padStart(2, '0'); }

  function tick() {
    var d = new Date();
    var hh = pad(d.getHours());
    var mm = pad(d.getMinutes());
    var ss = pad(d.getSeconds());

    hhEl.textContent = hh;

    if (mm !== lastMM) {
      mmEl.textContent = mm;
      mmEl.classList.remove('flip');
      void mmEl.offsetWidth;
      mmEl.classList.add('flip');
      lastMM = mm;
    }

    ssEl.textContent = ss;
    ssEl.classList.remove('flip');
    void ssEl.offsetWidth;
    ssEl.classList.add('flip');

    if (datelineEl) {
      datelineEl.textContent = new Intl.DateTimeFormat('en-US', {
        weekday: 'long',
        day: '2-digit',
        month: 'long'
      }).format(d);
    }
  }

  tick();
  setInterval(tick, 1000);
})();
