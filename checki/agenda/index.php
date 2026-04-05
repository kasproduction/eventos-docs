<?php
$screen_id    = (int)($_GET['screen'] ?? 0);
$direct_salon = (int)($_GET['salon']  ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<title>Agenda</title>
<style>
/* ── Reset ──────────────────────────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --bg:          #0d1117;
    --surface:     #161b22;
    --border:      #2d333b;
    --text:        #ffffff;
    --secondary:   #adbac7;
    --muted:       #768390;
    --dim:         #636e7b;
    --green:       #1a7a4a;
    --green-glow:  rgba(26,122,74,0.35);

    /* Fluid type — base 1080px */
    --fs-badge:    clamp(13px, 1.6vw, 18px);
    --fs-etiqueta: clamp(15px, 2vw, 22px);
    --fs-titulo:   clamp(32px, 5.5vw, 62px);
    --fs-ponente:  clamp(20px, 3vw, 34px);
    --fs-hora:     clamp(17px, 2.4vw, 26px);
    --fs-next-tit: clamp(18px, 2.8vw, 32px);
    --fs-next-sub: clamp(14px, 1.8vw, 20px);
    --fs-btn:      clamp(13px, 1.7vw, 19px);

    --fs-ag-day:   clamp(12px, 1.5vw, 17px);
    --fs-ag-hora:  clamp(16px, 2.2vw, 25px);
    --fs-ag-tit:   clamp(17px, 2.4vw, 28px);
    --fs-ag-pon:   clamp(14px, 1.8vw, 20px);
    --fs-ag-head:  clamp(20px, 2.8vw, 32px);

    --pad-x:       clamp(28px, 5vw, 64px);
    --pad-y:       clamp(24px, 4vh, 56px);
    --radius:      clamp(10px, 1.2vw, 16px);
    --gap-main:    clamp(16px, 2.5vh, 32px);
}

html, body {
    width: 100%; height: 100%;
    background: var(--bg);
    color: var(--text);
    font-family: -apple-system, 'Segoe UI', system-ui, sans-serif;
    overflow: hidden;
    -webkit-font-smoothing: antialiased;
}

/* ── App shell ──────────────────────────────────────────────────────────────── */
#app {
    position: relative;
    width: 100%; height: 100%;
    overflow: hidden;
}

.view {
    position: absolute;
    inset: 0;
    will-change: transform;
}

/* Initial positions via GSAP in JS — no CSS transform here */

/* ── IDLE VIEW ──────────────────────────────────────────────────────────────── */
#idle-view {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: var(--pad-y) var(--pad-x);
    gap: var(--gap-main);
}

/* Subtle background radial */
#idle-view::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 60% 50% at 50% 40%, rgba(26,122,74,0.06) 0%, transparent 70%);
    pointer-events: none;
}

/* ── Bloque EN CURSO ─────────────────────────────────────────────────────────── */
.idle-main {
    width: 100%;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    gap: var(--gap-main);
    position: relative;
}

.etiqueta-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.live-dot {
    width: clamp(8px, 1vw, 11px);
    height: clamp(8px, 1vw, 11px);
    background: var(--green);
    border-radius: 50%;
    box-shadow: 0 0 0 0 var(--green-glow);
    flex-shrink: 0;
}

.etiqueta {
    font-size: var(--fs-etiqueta);
    font-weight: 600;
    letter-spacing: clamp(4px, 0.6vw, 8px);
    text-transform: uppercase;
    color: var(--text);
}

.etiqueta.proxima-label { color: var(--muted); }

.titulo {
    font-size: var(--fs-titulo);
    font-weight: 700;
    line-height: 1.15;
    color: var(--text);
    max-width: 90%;
}

.titulo.titulo-proxima { font-size: clamp(26px, 4.2vw, 48px); color: var(--secondary); }
.titulo.sin-charlas    { font-size: clamp(22px, 3.5vw, 40px); color: var(--dim); font-weight: 400; }

.ponente {
    font-size: var(--fs-ponente);
    color: var(--secondary);
}

.hora-idle {
    font-size: var(--fs-hora);
    color: var(--muted);
}

/* ── Bloque PRÓXIMA ──────────────────────────────────────────────────────────── */
#idle-next-wrap {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
}

.idle-next {
    width: 100%;
    max-width: 700px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: var(--radius);
    padding: clamp(16px, 2.5vh, 28px) clamp(20px, 3vw, 36px);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: clamp(6px, 1vh, 12px);
}

.next-label {
    font-size: var(--fs-next-sub);
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--dim);
}

.next-titulo {
    font-size: var(--fs-next-tit);
    font-weight: 600;
    color: var(--secondary);
    line-height: 1.3;
}

.next-hora {
    font-size: var(--fs-next-sub);
    color: var(--dim);
}

.next-ponente {
    font-size: var(--fs-next-sub);
    color: var(--muted);
}

/* ── Botón agenda ────────────────────────────────────────────────────────────── */
.btn-agenda {
    padding: clamp(14px, 2vh, 22px) clamp(36px, 6vw, 64px);
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--dim);
    font-size: var(--fs-btn);
    font-weight: 500;
    letter-spacing: 2px;
    text-transform: uppercase;
    cursor: pointer;
    flex-shrink: 0;
    transition: border-color 0.2s, color 0.2s, background 0.2s;
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
}

.btn-agenda:active, .btn-agenda:hover {
    border-color: var(--muted);
    color: var(--secondary);
    background: rgba(255,255,255,0.03);
}

/* ── AGENDA VIEW ────────────────────────────────────────────────────────────── */
#agenda-view {
    display: flex;
    flex-direction: column;
    background: var(--bg);
}

/* Timer bar */
.timer-wrap {
    height: 3px;
    background: var(--border);
    flex-shrink: 0;
    overflow: hidden;
}

.timer-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--green), #2ea96a);
    width: 100%;
    transform-origin: left;
}

/* Header */
.agenda-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: clamp(18px, 3vh, 36px) var(--pad-x);
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
    background: rgba(13,17,23,0.8);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
}

.agenda-salon-name {
    font-size: var(--fs-ag-head);
    font-weight: 700;
    color: var(--text);
}

.btn-cerrar {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 50%;
    width: clamp(40px, 5.5vw, 60px);
    height: clamp(40px, 5.5vw, 60px);
    color: var(--muted);
    font-size: clamp(16px, 2.2vw, 24px);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
    transition: background 0.2s, color 0.2s;
}

.btn-cerrar:active { background: var(--border); color: var(--text); }

/* Scroll */
.agenda-scroll {
    flex: 1;
    overflow-y: auto;
    padding: 0 var(--pad-x) clamp(40px, 6vh, 80px);
    -webkit-overflow-scrolling: touch;
}

.agenda-scroll::-webkit-scrollbar { width: 3px; }
.agenda-scroll::-webkit-scrollbar-track { background: transparent; }
.agenda-scroll::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }

/* Día */
.dia-section { margin-top: clamp(28px, 4vh, 48px); }

.dia-header {
    font-size: var(--fs-ag-day);
    font-weight: 600;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--dim);
    padding-bottom: clamp(10px, 1.5vh, 18px);
    border-bottom: 1px solid var(--border);
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.dia-header.hoy { color: var(--green); }

.dia-dot {
    width: 7px; height: 7px;
    background: var(--green);
    border-radius: 50%;
    flex-shrink: 0;
}

/* Ítem charla */
.charla-item {
    display: flex;
    align-items: flex-start;
    gap: clamp(16px, 2.5vw, 32px);
    padding: clamp(16px, 2.5vh, 26px) 0;
    border-bottom: 1px solid rgba(45,51,59,0.6);
    position: relative;
    transition: background 0.2s;
}

.charla-item:last-child { border-bottom: none; }

/* Activa */
.charla-item.activa {
    background: rgba(26,122,74,0.06);
    margin: 0 calc(-1 * var(--pad-x));
    padding-left: var(--pad-x);
    padding-right: var(--pad-x);
    border-bottom: 1px solid rgba(26,122,74,0.15);
    border-top: 1px solid rgba(26,122,74,0.15);
}

.charla-item.activa::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    background: linear-gradient(180deg, var(--green), #2ea96a);
    border-radius: 0 2px 2px 0;
}

/* Pasada */
.charla-item.pasada { opacity: 0.45; }

/* Cancelada */
.charla-item.cancelada .ag-titulo { text-decoration: line-through; }

/* Hora col */
.ag-hora-col {
    min-width: clamp(70px, 10vw, 120px);
    flex-shrink: 0;
    padding-top: 2px;
}

.ag-hora {
    font-size: var(--fs-ag-hora);
    font-weight: 600;
    color: var(--muted);
    line-height: 1;
    font-variant-numeric: tabular-nums;
}

.charla-item.activa .ag-hora { color: var(--green); }

/* Info charla */
.ag-info { flex: 1; }

.ag-badge {
    font-size: var(--fs-badge);
    font-weight: 600;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: var(--green);
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 7px;
}

.ag-badge-dot {
    width: 6px; height: 6px;
    background: var(--green);
    border-radius: 50%;
    flex-shrink: 0;
}

.ag-titulo {
    font-size: var(--fs-ag-tit);
    font-weight: 600;
    color: var(--text);
    line-height: 1.3;
    margin-bottom: 5px;
}

.ag-ponente {
    font-size: var(--fs-ag-pon);
    color: var(--secondary);
}

.sin-agenda {
    text-align: center;
    color: var(--dim);
    font-size: var(--fs-ponente);
    padding: clamp(40px, 8vh, 80px) 0;
}
</style>
</head>
<body>
<div id="app">

    <!-- IDLE VIEW -->
    <div id="idle-view" class="view">
        <div class="idle-main" id="idle-main"><!-- JS --></div>
        <div id="idle-next-wrap" style="display:none">
            <div class="idle-next" id="idle-next"><!-- JS --></div>
        </div>
        <button class="btn-agenda" id="btn-agenda" onclick="abrirAgenda()">
            Ver agenda completa
        </button>
    </div>

    <!-- AGENDA VIEW -->
    <div id="agenda-view" class="view">
        <div class="timer-wrap">
            <div class="timer-bar" id="timer-bar"></div>
        </div>
        <div class="agenda-header">
            <div class="agenda-salon-name" id="ag-salon">Agenda</div>
            <button class="btn-cerrar" onclick="cerrarAgenda()">✕</button>
        </div>
        <div class="agenda-scroll" id="agenda-scroll"><!-- JS --></div>
    </div>

    <!-- IMAGEN VIEW -->
    <div id="imagen-view" class="view" style="display:none; background:#000;">
        <img id="imagen-fullscreen" style="width:100%;height:100%;object-fit:cover;display:block;" src="" alt="">
    </div>

    <!-- VIDEO VIEW -->
    <div id="video-view" class="view" style="display:none; background:#000;">
        <video id="video-fullscreen" style="width:100%;height:100%;object-fit:cover;display:block;"
               playsinline muted></video>
    </div>

</div>
<script src="js/gsap.min.js"></script>
<script>
// ── Config ────────────────────────────────────────────────────────────────────
const SCREEN_ID    = <?= $screen_id ?>;
const DIRECT_SALON = <?= $direct_salon ?>;
const REFRESH_MS   = 30_000;
const IDLE_TIMEOUT = 30_000;
const API_BASE     = '../api';

// ── Estado ────────────────────────────────────────────────────────────────────
let salonId     = DIRECT_SALON || null;
let data        = null;
let agendaOpen  = false;
let idleTimer   = null;
let breathTween = null;
let timerTween  = null;
let modoActual  = null;
let imagenUrl   = null;
let videoUrl    = null;
let videoLoop   = true;
let videoFit    = 'contain';

// ── Init posiciones con GSAP ──────────────────────────────────────────────────
gsap.set('#idle-view',   { x: 0 });
gsap.set('#agenda-view', { x: '100%' });
gsap.set('#imagen-view', { opacity: 1 });
gsap.set('#video-view',  { opacity: 1 });
gsap.set('#idle-main > *', { opacity: 0 });
gsap.set('#btn-agenda',    { opacity: 0 });

// ── Pantalla apagada ──────────────────────────────────────────────────────────
function mostrarApagada() {
    document.getElementById('idle-view').style.display    = 'none';
    document.getElementById('agenda-view').style.display  = 'none';
    document.getElementById('imagen-view').style.display  = 'none';
    const vv = document.getElementById('video-view');
    vv.style.display = 'none';
    const v = document.getElementById('video-fullscreen');
    v.pause();
    v.removeAttribute('src');
    v.removeAttribute('data-src');
    v.load();
}

// ── Pantalla con imagen fullscreen ────────────────────────────────────────────
function mostrarImagen(url) {
    document.getElementById('idle-view').style.display    = 'none';
    document.getElementById('agenda-view').style.display  = 'none';
    const vv = document.getElementById('video-view');
    vv.style.display = 'none';
    document.getElementById('video-fullscreen').pause();
    const view = document.getElementById('imagen-view');
    const img  = document.getElementById('imagen-fullscreen');
    img.src = url || '';
    view.style.display = '';
    gsap.fromTo(view, { opacity: 0 }, { opacity: 1, duration: 0.8, ease: 'power2.inOut' });
}

// ── Pantalla con video fullscreen ─────────────────────────────────────────────
function mostrarVideo(url, loop, fit) {
    // Sin URL válida no se puede reproducir nada
    if (!url) { mostrarApagada(); return; }

    document.getElementById('idle-view').style.display    = 'none';
    document.getElementById('agenda-view').style.display  = 'none';
    document.getElementById('imagen-view').style.display  = 'none';
    const view  = document.getElementById('video-view');
    const video = document.getElementById('video-fullscreen');

    video.loop            = !!loop;
    video.muted           = true;
    video.style.objectFit = fit || 'contain';

    // Comparar solo el path+query sin host para evitar falsos cambios por normalización
    const urlActual = video.getAttribute('data-src') || '';
    if (urlActual !== url) {
        video.setAttribute('data-src', url);
        video.src = url;
        video.load();
        video.addEventListener('canplay', () => video.play().catch(() => {}), { once: true });
    }

    view.style.display = '';
    gsap.fromTo(view, { opacity: 0 }, { opacity: 1, duration: 0.8, ease: 'power2.inOut' });
}

// ── Restaurar vistas normales (idle) ─────────────────────────────────────────
function mostrarIdle() {
    document.getElementById('imagen-view').style.display  = 'none';
    const vv = document.getElementById('video-view');
    vv.style.display = 'none';
    document.getElementById('video-fullscreen').pause();
    document.getElementById('idle-view').style.display    = '';
    // agenda-view permanece oculta (se muestra solo al pulsar el botón)
}

// ── Config de pantalla (screen_id) ────────────────────────────────────────────
async function refreshConfig() {
    try {
        const r   = await fetch(`${API_BASE}/agenda-config?screen_id=${SCREEN_ID}`);
        const cfg = await r.json();
        const nuevoModo   = cfg.modo       || (cfg.apagada ? 'apagada' : 'agenda');
        const nuevaImagen = cfg.imagen_url || null;
        const nuevoVideo  = cfg.video_url  || null;
        const nuevoLoop   = cfg.loop !== undefined ? !!cfg.loop : true;
        const nuevoFit    = cfg.video_fit  || 'contain';
        const nuevoSalon  = cfg.salon_id   || null;

        const cambiado = nuevoModo  !== modoActual
            || nuevaImagen !== imagenUrl
            || nuevoVideo  !== videoUrl
            || nuevoLoop   !== videoLoop
            || nuevoFit    !== videoFit
            || nuevoSalon  !== salonId;

        if (!cambiado) return;

        modoActual = nuevoModo;
        imagenUrl  = nuevaImagen;
        videoUrl   = nuevoVideo;
        videoLoop  = nuevoLoop;
        videoFit   = nuevoFit;
        salonId    = nuevoSalon;

        if (modoActual === 'apagada') {
            mostrarApagada();
        } else if (modoActual === 'imagen') {
            mostrarImagen(imagenUrl);
        } else if (modoActual === 'video') {
            mostrarVideo(videoUrl, videoLoop, videoFit);
        } else {
            // 'agenda'
            mostrarIdle();
            if (salonId) { data = null; await fetchData(); }
            else          { mostrarApagada(); }
        }
    } catch (e) {
        console.warn('[agenda] config:', e.message);
    }
}

// ── Fetch datos del salón ─────────────────────────────────────────────────────
async function fetchData() {
    if (modoActual !== 'agenda') return;
    if (!salonId) return;
    try {
        const res   = await fetch(`${API_BASE}/agenda-salon?salon_id=${salonId}`);
        if (!res.ok) return;
        const fresh = await res.json();
        const changed = JSON.stringify(fresh) !== JSON.stringify(data);
        data = fresh;
        if (changed) {
            renderIdle();
            if (agendaOpen) renderAgenda();
        }
    } catch (e) {
        console.warn('[agenda] fetch:', e.message);
    }
}

// ── Init ─────────────────────────────────────────────────────────────────────
async function init() {
    if (SCREEN_ID) {
        await refreshConfig();
        setInterval(refreshConfig, 60_000);
        // fetchData corre siempre pero tiene guard interno
        setInterval(fetchData, REFRESH_MS);
    } else {
        salonId    = DIRECT_SALON || null;
        modoActual = salonId ? 'agenda' : 'apagada';
        if (!salonId) { mostrarApagada(); return; }
        fetchData();
        setInterval(fetchData, REFRESH_MS);
    }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const esc = s => s ? String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') : '';

function fmtHora(dt) {
    if (!dt) return '';
    const t = dt.includes(' ') ? dt.split(' ')[1] : dt;
    return t.substring(0, 5);
}

function fmtFecha(f) {
    const [,m,d] = f.split('-');
    return `${parseInt(d)} ${['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'][+m-1]}`;
}

function esHoy(f) { return f === (data?.now ?? new Date().toISOString()).slice(0,10); }

function esPasada(fin) {
    const now = data?.now ? new Date(data.now.replace(' ','T')) : new Date();
    return new Date(fin.replace(' ','T')) < now;
}

// ── Render idle (solo DOM, sin animar) ───────────────────────────────────────
function renderIdle() {
    if (!data) return;
    const { charla, proxima } = data;
    const main      = document.getElementById('idle-main');
    const nextWrap  = document.getElementById('idle-next-wrap');
    const nextEl    = document.getElementById('idle-next');

    // Bloque principal
    if (charla) {
        main.innerHTML = `
            <div class="etiqueta-wrap">
                <div class="live-dot" id="live-dot"></div>
                <div class="etiqueta">En curso</div>
            </div>
            <div class="titulo">${esc(charla.titulo)}</div>
            ${charla.ponente ? `<div class="ponente">${esc(charla.ponente)}</div>` : ''}
            <div class="hora-idle">${fmtHora(charla.hora_inicio)} – ${fmtHora(charla.hora_fin)}</div>
        `;
    } else if (proxima) {
        main.innerHTML = `
            <div class="etiqueta proxima-label">Próxima charla</div>
            <div class="titulo titulo-proxima">${esc(proxima.titulo)}</div>
            ${proxima.ponente ? `<div class="ponente">${esc(proxima.ponente)}</div>` : ''}
            <div class="hora-idle">Comienza a las ${fmtHora(proxima.hora_inicio)}</div>
        `;
    } else {
        main.innerHTML = `<div class="titulo sin-charlas">No hay más charlas<br>programadas hoy</div>`;
    }

    // Bloque próxima
    if (charla && proxima) {
        nextWrap.style.display = 'flex';
        nextEl.innerHTML = `
            <div class="next-label">A continuación</div>
            <div class="next-titulo">${esc(proxima.titulo)}</div>
            ${proxima.ponente ? `<div class="next-ponente">${esc(proxima.ponente)}</div>` : ''}
            <div class="next-hora">${fmtHora(proxima.hora_inicio)}</div>
        `;
    } else {
        nextWrap.style.display = 'none';
    }

    // Solo animar si la vista idle está visible
    if (!agendaOpen) animarIdleEntrada();
}

// ── Animación entrada idle ────────────────────────────────────────────────────
function animarIdleEntrada() {
    if (breathTween) { breathTween.kill(); breathTween = null; }

    const tl = gsap.timeline();

    // Elementos del bloque principal
    tl.fromTo('#idle-main > *',
        { opacity: 0, y: 28 },
        { opacity: 1, y: 0, stagger: 0.08, duration: 0.65, ease: 'power3.out' }
    );

    // Bloque próxima (si existe)
    const nextWrap = document.getElementById('idle-next-wrap');
    if (nextWrap.style.display !== 'none') {
        tl.fromTo('#idle-next',
            { opacity: 0, y: 20, scale: 0.97 },
            { opacity: 1, y: 0, scale: 1, duration: 0.55, ease: 'power3.out' },
            '-=0.35'
        );
    }

    // Botón
    tl.fromTo('#btn-agenda',
        { opacity: 0, y: 14 },
        { opacity: 1, y: 0, duration: 0.5, ease: 'power2.out' },
        '-=0.3'
    );

    // Breathing en el dot "En curso"
    tl.add(() => {
        const dot = document.getElementById('live-dot');
        if (dot && data?.charla) {
            breathTween = gsap.timeline({ repeat: -1 })
                .to(dot, { scale: 1.5, opacity: 0.4, duration: 0.9, ease: 'sine.inOut' })
                .to(dot, { scale: 1,   opacity: 1,   duration: 0.9, ease: 'sine.inOut' });
        }
    });
}

// ── Render agenda (solo DOM) ──────────────────────────────────────────────────
function renderAgenda() {
    if (!data) return;

    document.getElementById('ag-salon').textContent =
        `Agenda — ${data.salon?.nombre ?? ''}`;

    const scroll   = document.getElementById('agenda-scroll');
    const activaId = data.charla?.id ?? null;

    if (!data.dias?.length) {
        scroll.innerHTML = `<div class="sin-agenda">Sin charlas programadas</div>`;
        return;
    }

    let html = '';
    for (const dia of data.dias) {
        const hoy = esHoy(dia.fecha);
        html += `<div class="dia-section">
            <div class="dia-header ${hoy ? 'hoy' : ''}">
                ${hoy ? '<span class="dia-dot"></span>' : ''}
                ${esc(dia.nombre ?? '')} · ${fmtFecha(dia.fecha)}
            </div>`;

        for (const c of (dia.charlas ?? [])) {
            const activa  = c.id === activaId;
            const pasada  = !activa && esPasada(c.hora_fin);
            const cls     = [activa?'activa':'', pasada?'pasada':'', c.cancelada?'cancelada':''].filter(Boolean).join(' ');

            html += `
            <div class="charla-item ${cls}" id="ci-${c.id}">
                <div class="ag-hora-col">
                    <div class="ag-hora">${fmtHora(c.hora_inicio)}</div>
                </div>
                <div class="ag-info">
                    ${activa ? '<div class="ag-badge"><span class="ag-badge-dot"></span>En curso</div>' : ''}
                    <div class="ag-titulo">${esc(c.titulo)}</div>
                    ${c.ponente ? `<div class="ag-ponente">${esc(c.ponente)}</div>` : ''}
                </div>
            </div>`;
        }
        html += `</div>`;
    }

    scroll.innerHTML = html;

    // Scroll al ítem activo
    requestAnimationFrame(() => {
        const target = document.getElementById(`ci-${activaId}`)
            ?? scroll.querySelector('.dia-header.hoy')?.closest('.dia-section');
        if (target) target.scrollIntoView({ block: 'start', behavior: 'smooth' });
    });
}

// ── Animación entrada agenda ──────────────────────────────────────────────────
function animarAgendaEntrada() {
    const items = document.querySelectorAll('.charla-item');
    if (!items.length) return;

    gsap.fromTo(items,
        { opacity: 0, x: -24 },
        { opacity: 1, x: 0, stagger: 0.035, duration: 0.45, ease: 'power2.out', clearProps: 'transform' }
    );

    // Breathing en badge activa
    const badge = document.querySelector('.ag-badge-dot');
    if (badge) {
        gsap.timeline({ repeat: -1 })
            .to(badge, { scale: 1.8, opacity: 0.3, duration: 1, ease: 'sine.inOut' })
            .to(badge, { scale: 1,   opacity: 1,   duration: 1, ease: 'sine.inOut' });
    }
}

// ── Navegación de vistas ──────────────────────────────────────────────────────
function abrirAgenda() {
    if (agendaOpen || !data) return;
    agendaOpen = true;
    if (breathTween) { breathTween.kill(); breathTween = null; }
    renderAgenda();

    gsap.timeline()
        .to('#idle-view',   { x: '-100%', duration: 0.52, ease: 'power2.inOut' }, 0)
        .to('#agenda-view', { x: 0,       duration: 0.52, ease: 'power2.inOut' }, 0)
        .add(() => { animarAgendaEntrada(); iniciarTimerRetorno(); });
}

function cerrarAgenda() {
    if (!agendaOpen) return;
    agendaOpen = false;
    clearTimeout(idleTimer);
    if (timerTween) { timerTween.kill(); timerTween = null; }

    gsap.timeline()
        .to('#agenda-view', { x: '100%', duration: 0.52, ease: 'power2.inOut' }, 0)
        .to('#idle-view',   { x: 0,      duration: 0.52, ease: 'power2.inOut' }, 0)
        .add(() => animarIdleEntrada());
}

// ── Timer retorno automático ──────────────────────────────────────────────────
function iniciarTimerRetorno() {
    clearTimeout(idleTimer);
    idleTimer = setTimeout(cerrarAgenda, IDLE_TIMEOUT);
    animarTimerBar();
}

function animarTimerBar() {
    if (timerTween) timerTween.kill();
    const bar = document.getElementById('timer-bar');
    gsap.set(bar, { scaleX: 1, transformOrigin: 'left' });
    timerTween = gsap.to(bar, {
        scaleX: 0,
        duration: IDLE_TIMEOUT / 1000,
        ease: 'none',
        transformOrigin: 'left'
    });
}

function reiniciarTimer() {
    if (!agendaOpen) return;
    clearTimeout(idleTimer);
    idleTimer = setTimeout(cerrarAgenda, IDLE_TIMEOUT);
    animarTimerBar();
}

// Resetear en interacción con agenda
document.getElementById('agenda-view').addEventListener('touchstart', reiniciarTimer, { passive: true });
document.getElementById('agenda-view').addEventListener('click', reiniciarTimer);

init();
</script>
</body>
</html>
