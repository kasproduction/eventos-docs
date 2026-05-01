/* ============================================================
   Event Pulse — App (init + responsive scaling)
   ============================================================ */

(function () {
  'use strict';

  var canvas = document.getElementById('canvas');

  function scale() {
    var s = Math.min(window.innerWidth / 1920, window.innerHeight / 1080);
    canvas.style.transform = 'scale(' + s + ')';
  }

  window.addEventListener('resize', scale);
  scale();
})();
