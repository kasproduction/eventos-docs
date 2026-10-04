/* QA automatico de lab-landing-v3.html (Noir y Lux) en celular, iPad y escritorio.
   Uso:  node "design/features/landing/qa/qa-landing-v3.cjs"            -> corre las pruebas
         node "design/features/landing/qa/qa-landing-v3.cjs" --medir    -> ademas imprime la tabla de espacios
   Requiere Laragon sirviendo http://localhost/APP%20EVENTOS/ y Playwright de eventos-web. */
const PW = process.env.PW_PATH || 'C:/laragon/www/eventos-web/node_modules/@playwright/test';
const { chromium } = require(PW);
const URL = process.env.LV3_URL || 'http://localhost/APP%20EVENTOS/design/features/landing/lab-landing-v3.html';
const MEDIR = process.argv.includes('--medir');

/* tamanos: celular, iPads en los dos sentidos, escritorio */
const VIEWS = [
  { k: 'iPhone', w: 390, h: 844, touch: true },
  { k: 'iPad vertical', w: 768, h: 1024, touch: true },
  { k: 'iPad horizontal', w: 1024, h: 768, touch: true },
  { k: 'iPad Air vertical', w: 820, h: 1180, touch: true },
  { k: 'iPad Air horizontal', w: 1180, h: 820, touch: true },
  { k: 'iPad Pro vertical', w: 1024, h: 1366, touch: true },
  { k: 'iPad Pro horizontal', w: 1366, h: 1024, touch: true },
  { k: 'Laptop', w: 1440, h: 900, touch: false },
  { k: 'Escritorio', w: 1920, h: 1080, touch: false }
];
const SECS = {
  dark: ['#top', '#speakers', '#agenda', '#experiencia', '#patrocinadores', '#lugar', '#preguntas', '#final'],
  light: ['#top', '#lx-speakers', '#lx-agenda', '#lx-exp', '#lx-spn', '#lx-lugar', '#lx-faq', '#final']
};

/* topes de espacio entre el contenido de una seccion y el de la siguiente */
const maxGap = v => v.w <= 600 ? 170 : v.w <= 1366 && v.touch ? 220 : 300;

let fails = 0, passes = 0;
const results = [];
function check(ctx, name, ok, detail) {
  if (ok) passes++; else { fails++; results.push(`  FALLA  [${ctx}] ${name}${detail ? ' -> ' + detail : ''}`); }
}

/* --------- medidas dentro de la pagina --------- */
function measure(secs) {
  const vis = el => { for (let e = el; e && e !== document.body; e = e.parentElement) { const cs = getComputedStyle(e); if (cs.display === 'none' || cs.visibility === 'hidden') return false; } return true; };
  /* "tinta" de una seccion: texto, imagenes, iPhone, tarjetas. Sin el relleno de la seccion. */
  const ink = sec => {
    let top = Infinity, bot = -Infinity;
    const add = r => { if (r.width < 1 || r.height < 1) return; top = Math.min(top, r.top); bot = Math.max(bot, r.bottom); };
    const tw = document.createTreeWalker(sec, NodeFilter.SHOW_TEXT, { acceptNode: n => n.textContent.trim() && vis(n.parentElement) ? 1 : 3 });
    for (let n; (n = tw.nextNode());) { const rg = document.createRange(); rg.selectNodeContents(n); for (const r of rg.getClientRects()) add(r); }
    sec.querySelectorAll('img, .iphone, .plx, .col, .fc, details, .row, .lama, .spk, .lxs, .ticket, .marq, .vx, .venue > *').forEach(e => { if (vis(e)) add(e.getBoundingClientRect()); });
    /* lo que va entre esta seccion y la siguiente (las cintas de patrocinadores) cuenta como parte de esta */
    for (let n = sec.nextElementSibling; n && n.tagName !== 'SECTION'; n = n.nextElementSibling) if (vis(n) && n.matches('.marq')) add(n.getBoundingClientRect());
    return { top: top + scrollY, bot: bot + scrollY };
  };
  /* los elementos fijos (sticky) se miden en su lugar natural, no donde los deja el scroll */
  const sticky = [...document.querySelectorAll('*')].filter(e => getComputedStyle(e).position === 'sticky');
  /* y se ubican donde la persona los ve por ultima vez: al soltarse, pegados al final de su contenedor */
  sticky.forEach(e => { e.dataset.qaPos = e.style.position; e.style.position = 'relative'; });
  sticky.forEach(e => { const pr = e.parentElement.getBoundingClientRect(), pb = parseFloat(getComputedStyle(e.parentElement).paddingBottom) || 0, r = e.getBoundingClientRect(); e.dataset.qaTf = e.style.transform; e.style.transform = `translateY(${pr.bottom - pb - r.bottom}px)`; });
  const list = secs.map(s => document.querySelector(s)).filter(Boolean).filter(vis);
  const gaps = [];
  for (let i = 0; i < list.length - 1; i++) {
    const a = ink(list[i]), b = ink(list[i + 1]);
    gaps.push({ from: '#' + list[i].id, to: '#' + list[i + 1].id, gap: Math.round(b.top - a.bot) });
  }
  sticky.forEach(e => { e.style.position = e.dataset.qaPos; e.style.transform = e.dataset.qaTf; delete e.dataset.qaPos; delete e.dataset.qaTf; });
  const o = { gaps, overflowX: document.documentElement.scrollWidth - innerWidth };
  /* patrocinadores: tarjetas iguales */
  const pl = [...document.querySelectorAll('.plat')].find(p => p.getBoundingClientRect().width > 0);
  if (pl) o.plx = [...pl.querySelectorAll('.plx')].map(c => { const r = c.getBoundingClientRect(); return [Math.round(r.width), Math.round(r.height)]; });
  /* speakers: columnas */
  const sg = [...document.querySelectorAll('.spk-grid, .lxs-grid')].find(p => p.getBoundingClientRect().width > 0);
  if (sg) o.spkCols = getComputedStyle(sg).gridTemplateColumns.split(' ').length;
  /* Noir: espacio entre pasos de la experiencia */
  const st = [...document.querySelectorAll('#experiencia .step')].filter(vis);
  if (st.length) o.stepGaps = st.slice(1).map((s, i) => Math.round(s.querySelector('.n').getBoundingClientRect().top - st[i].querySelector('p').getBoundingClientRect().bottom));
  /* Lux: el orbe cabe en una pantalla */
  const orb = document.querySelector('#lx-exp .orb');
  if (orb && vis(orb)) o.orbH = Math.round(orb.getBoundingClientRect().height);
  /* agenda de Lux: dias visibles, selector y alto */
  const ag = document.querySelector('#lx-agenda');
  if (ag && vis(ag)) o.lxAg = { cols: [...ag.querySelectorAll('.board .col')].filter(vis).length, days: vis(document.querySelector('#lxDays')), h: Math.round(ag.getBoundingClientRect().height) };
  /* invitaciones */
  o.copy = [...document.querySelectorAll('.sec-head p, .lx-head p')].filter(vis).map(p => p.textContent);
  /* textos que se salen de su caja */
  o.clipped = [...document.querySelectorAll('h1, h2, h3, .plx b, .fc h3, .step h3')].filter(vis).filter(e => e.scrollWidth > e.clientWidth + 2).map(e => e.textContent.trim().slice(0, 30));
  return o;
}

/* --------- iPhone de Noir no tapa el texto del paso activo --------- */
async function noirPhoneOverlap(page) {
  return page.evaluate(async () => {
    const steps = [...document.querySelectorAll('#experiencia .step')];
    const bad = [];
    for (const s of steps) {
      const r = s.getBoundingClientRect(); scrollTo({ top: r.top + scrollY + r.height / 2 - innerHeight / 2, behavior: 'instant' });
      await new Promise(f => requestAnimationFrame(() => requestAnimationFrame(f)));
      const ph = document.querySelector('#iphone').getBoundingClientRect();
      if (ph.top < -2 || ph.bottom > innerHeight + 2) bad.push('iPhone fuera de pantalla en: ' + s.querySelector('h3').textContent);
      for (const el of s.querySelectorAll('h3, p')) {
        const t = el.getBoundingClientRect();
        const ix = Math.min(ph.right, t.right) - Math.max(ph.left, t.left), iy = Math.min(ph.bottom, t.bottom) - Math.max(ph.top, t.top);
        if (ix > 4 && iy > 4) bad.push(s.querySelector('h3').textContent);
      }
    }
    return [...new Set(bad)];
  });
}

/* --------- anclas: la pildora no tapa el titulo al saltar --------- */
async function anchorCovered(page, theme) {
  return page.evaluate(async theme => {
    const ids = theme === 'dark' ? ['speakers', 'agenda', 'experiencia', 'patrocinadores', 'preguntas'] : ['lx-speakers', 'lx-agenda', 'lx-exp', 'lx-spn', 'lx-faq'];
    const bad = [];
    for (const id of ids) {
      const sec = document.getElementById(id); sec.scrollIntoView({ behavior: 'instant', block: 'start' });
      await new Promise(f => setTimeout(f, 700));
      const nav = document.querySelector('.nav').getBoundingClientRect();
      const kick = sec.querySelector('.kick').getBoundingClientRect();
      if (kick.top < nav.bottom + 8 && nav.bottom > 0) bad.push(id);
    }
    return bad;
  }, theme);
}

(async () => {
  const browser = await chromium.launch();
  const table = [];
  for (const v of VIEWS) for (const theme of ['dark', 'light']) {
    const ctx = await browser.newContext({ viewport: { width: v.w, height: v.h }, hasTouch: v.touch, isMobile: v.touch && v.w < 1100, reducedMotion: 'reduce' });
    const page = await ctx.newPage();
    if (v.touch) await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto(`${URL}?t=${theme}`, { waitUntil: 'load' });
    await page.evaluate(() => { document.body.classList.add('dx-hero'); scrollTo({ top: 0, behavior: 'instant' }); });
    await page.waitForTimeout(600);
    const tag = `${v.k} ${v.w}x${v.h} ${theme === 'dark' ? 'Noir' : 'Lux'}`;
    const m = await page.evaluate(measure, SECS[theme]);
    table.push({ tag, m });

    /* 1. espacio entre secciones */
    for (const g of m.gaps) check(tag, `espacio ${g.from} a ${g.to}`, g.gap <= maxGap(v) && g.gap >= 40, `${g.gap}px (tope ${maxGap(v)})`);
    /* 2. sin scroll horizontal */
    check(tag, 'sin scroll horizontal', m.overflowX <= 0, `${m.overflowX}px de mas`);
    /* 3. patrocinadores iguales y en la grilla correcta */
    if (m.plx) {
      const same = m.plx.every(([w, h]) => Math.abs(w - m.plx[0][0]) <= 1 && Math.abs(h - m.plx[0][1]) <= 1);
      check(tag, 'patrocinadores: tarjetas iguales', same, JSON.stringify(m.plx));
    }
    /* 4. columnas de speakers */
    const wantCols = v.w <= 600 ? 2 : v.w <= 1100 ? 3 : 4;
    if (m.spkCols) check(tag, 'speakers: columnas', m.spkCols === wantCols, `${m.spkCols} (esperado ${wantCols})`);
    /* 5. Noir: pasos juntos en iPad y celular */
    if (m.stepGaps && v.touch) check(tag, 'experiencia: espacio entre pasos', m.stepGaps.every(g => g <= 130), m.stepGaps.join(', '));
    /* 6. Lux: el orbe cabe en una pantalla de iPad */
    if (m.orbH && v.touch && v.w >= 700) check(tag, 'experiencia Lux: cabe en una pantalla', m.orbH <= v.h, `${m.orbH}px en ${v.h}`);
    /* 6b. agenda de Lux: un dia a la vez en iPad vertical y celular, tablero de tres en horizontal y escritorio */
    if (m.lxAg) {
      const narrow = v.w <= 600 || (v.w <= 1100 && v.h > v.w);
      check(tag, 'agenda Lux: dias visibles', m.lxAg.cols === (narrow ? 1 : 3), `${m.lxAg.cols} (esperado ${narrow ? 1 : 3})`);
      check(tag, 'agenda Lux: selector de dia', m.lxAg.days === narrow, `visible=${m.lxAg.days}`);
      if (narrow) {
        /* referencia: la agenda de Noir mide 1,9 pantallas en celular y 1,4 en iPad vertical con un dia de siete charlas */
        const tope = v.w <= 600 ? 2.0 : 1.6;
        check(tag, 'agenda Lux: alto como la de Noir', m.lxAg.h <= v.h * tope, `${m.lxAg.h}px en ${v.h} (tope ${tope} pantallas)`);
        const sw = await page.evaluate(async () => { const b = document.querySelectorAll('#lxDays button')[1]; b.click(); await new Promise(f => setTimeout(f, 100)); const c = [...document.querySelectorAll('#lx-agenda .board .col')]; return { on: c.findIndex(x => x.classList.contains('on')), btn: document.querySelectorAll('#lxDays button')[1].classList.contains('on'), visible: c.filter(x => getComputedStyle(x).display !== 'none').length }; });
        check(tag, 'agenda Lux: tocar el dia 2 lo muestra', sw.on === 1 && sw.btn && sw.visible === 1, JSON.stringify(sw));
      }
    }
    /* 7. invitaciones en tactil */
    if (v.touch) check(tag, 'tactil: invitaciones dicen "Toca"', m.copy.every(t => !t.includes('Pasa el mouse')), m.copy.filter(t => t.includes('Pasa el mouse')).join(' | '));
    /* 8. textos sin cortar */
    check(tag, 'textos sin cortar', m.clipped.length === 0, m.clipped.join(' | '));
    /* 9. Noir: el iPhone no tapa el texto en iPad */
    if (theme === 'dark' && v.w >= 700 && v.touch) { const bad = await noirPhoneOverlap(page); check(tag, 'experiencia Noir: el iPhone se ve completo y no tapa texto', bad.length === 0, bad.join(' | ')); }
    /* 10. anclas */
    const cov = await anchorCovered(page, theme); check(tag, 'anclas: la pildora no tapa el titulo', cov.length === 0, cov.join(', '));
    await ctx.close();
  }
  await browser.close();

  if (MEDIR) {
    console.log('\nESPACIO ENTRE SECCIONES (px, del ultimo texto o imagen de una al primero de la siguiente)');
    for (const { tag, m } of table) console.log(`${tag.padEnd(36)} ${m.gaps.map(g => g.gap).join('  ')}${m.stepGaps ? '   pasos: ' + m.stepGaps.join(' ') : ''}`);
  }
  console.log(`\n${passes} pasan, ${fails} fallan`);
  results.forEach(r => console.log(r));
  process.exit(fails ? 1 : 0);
})().catch(e => { console.error(e); process.exit(2); });
