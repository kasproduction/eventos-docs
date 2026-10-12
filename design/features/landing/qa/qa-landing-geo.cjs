/* Uso: node "design/features/landing/qa/qa-landing-geo.cjs" [1568x744,390x780]  (lab en Laragon + webapp en :3000) */
/* Compara la geometria del lab v3 y la landing de la webapp: para cada seccion, en varios puntos del scroll,
   mide donde queda cada pieza en pantalla (relativo a la ventana, quitando la barra de 44px del lab).
   Reporta diferencias de mas de 24px. Los datos difieren (cuantos speakers, etc.): se comparan las primeras piezas. */
const { chromium } = require('C:/laragon/www/eventos-web/node_modules/@playwright/test');
const SECS = {
  noir: ['speakers', 'agenda', 'experiencia', 'patrocinadores', 'lugar', 'preguntas', 'final'],
  lux: ['lx-speakers', 'lx-agenda', 'lx-exp', 'lx-spn', 'lx-lugar', 'lx-faq', 'final'],
};
const PIECES = ['.sec-head', '.lx-head', '.phone-wrap', '.iphone', '.step', '.spk', '.lxs', '.ag-bar', '.row', '.board', '.col', '.plat', '.plx', '.venue > div', '.hours', '.vx > div', '.tl', '.faq > div', '.qa', '.fq', '.final-in', '.orb', '.fc'];
(async () => {
  const b = await chromium.launch(); const issues = [];
  const sizes = (process.argv[2] || '1568x744,1440x900,1180x820,820x1180,390x780').split(',').map(s => s.split('x').map(Number));
  for (const th of ['noir', 'lux']) for (const [w, h] of sizes) {
    const mob = w <= 1180;
    const pages = {};
    for (const [n, u] of [['lab', 'http://localhost/APP%20EVENTOS/design/features/landing/lab-landing-v3.html'], ['web', `http://localhost:3000/es/e/summit-empresarial-2026?tema=${th}`]]) {
      const p = await b.newPage({ viewport: { width: w, height: h }, isMobile: mob, hasTouch: mob });
      if (n === 'lab') await p.addInitScript(t => { try { localStorage.setItem('lv3-theme', t === 'lux' ? 'light' : 'dark'); } catch (e) {} document.addEventListener('DOMContentLoaded', () => { const st = document.createElement('style'); st.textContent = '.lab-bar{display:none!important} :root{--bar:0px!important}'; document.head.appendChild(st); }); }, th);
      await p.goto(u); await p.waitForTimeout(2500);
      await p.evaluate(() => { document.documentElement.style.scrollBehavior = 'auto'; });
      pages[n] = p;
    }
    for (const id of SECS[th]) {
      for (const f of [0, 0.5, 1, 1.5, 2]) {
        const snap = {};
        for (const n of ['lab', 'web']) {
          const p = pages[n], bar = 0;
          snap[n] = await p.evaluate(({ id, f, PIECES, bar, h }) => {
            const s = document.getElementById(id); if (!s) return null;
            window.scrollTo(0, s.getBoundingClientRect().top + scrollY - bar + f * h);
            const out = {};
            for (const sel of PIECES) {
              const els = [...s.querySelectorAll(sel)].slice(0, 3);
              els.forEach((e, i) => { const r = e.getBoundingClientRect(); out[`${sel}#${i}`] = [Math.round(r.top - bar), Math.round(r.left), Math.round(r.width), Math.round(r.height)]; });
            }
            return out;
          }, { id, f, PIECES, bar, h });
        }
        if (!snap.lab || !snap.web) { issues.push(`${th} ${w}x${h} #${id}: falta en ${snap.lab ? 'web' : 'lab'}`); continue; }
        for (const k of Object.keys(snap.lab)) {
          const a = snap.lab[k], c = snap.web[k];
          if (!c) { issues.push(`${th} ${w}x${h} #${id} +${f}vh ${k}: no existe en web`); continue; }
          const d = a.map((v, i) => Math.abs(v - c[i]));
          // alto distinto por cantidad de datos es esperable en filas/tarjetas; lo importante es la posicion
          if (d[0] > 24 || d[1] > 24 || d[2] > 24) issues.push(`${th} ${w}x${h} #${id} +${f}vh ${k}: lab top/left/w=${a.slice(0, 3)} web=${c.slice(0, 3)}`);
        }
      }
    }
    for (const p of Object.values(pages)) await p.close();
  }
  console.log(issues.length ? issues.join('\n') : 'geometria igual al lab');
  await b.close();
})();
