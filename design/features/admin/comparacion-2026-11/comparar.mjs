// Pasada de medicion del panel de EventOS contra Filament (Kamilo 2026-11-24, decisiones.md): una sola pasada, sin arreglar
// nada. Para cada par de pantallas guarda la captura de pagina completa y una ficha medida en el navegador. Kamilo entra a mano a
// los dos paneles (clave + codigo); el script no toca credenciales ni cambia nada: solo navega y lee.
// Se corre desde la raiz de eventos-web: node "../APP EVENTOS/design/features/admin/comparacion-2026-11/comparar.mjs"
import { chromium } from "@playwright/test";
import fs from "node:fs";

const FIL = "http://eventos-backend.test/admin/";
const NUE = "http://localhost:3000/admin/";
const E = "eventos/1/";
const OUT = "C:/laragon/www/APP EVENTOS/design/features/admin/comparacion-2026-11";

// filament | nuevo (sin la raiz). "-" = no tiene par. Los ids son los de eventos_db (los mismos de las capturas de octubre).
const PARES = `
| ${E}
mi-seguridad | cuenta/seguridad
agenda/event-sessions | ${E}agenda
agenda/event-sessions/create | ${E}agenda/nueva
agenda/event-sessions/235/edit | ${E}agenda/235
agenda/session-tracks | ${E}agenda/tracks
agenda/session-tracks/create | ${E}agenda/tracks/nuevo
agenda/session-tracks/6/edit | ${E}agenda/tracks/6
agenda/session-types | ${E}agenda/tipos
agenda/session-types/create | ${E}agenda/tipos/nuevo
agenda/session-ratings | -
speakers | ${E}speakers
speakers/create | ${E}speakers/nuevo
speakers/19/edit | ${E}speakers/19
sponsors/sponsors | ${E}sponsors
sponsors/sponsors/create | ${E}sponsors/nuevo
sponsors/sponsors/1/edit | ${E}sponsors/1
documents | ${E}documentos
documents/create | ${E}documentos/nuevo
documents/7/edit | ${E}documentos/7
custom-pages | ${E}paginas
custom-pages/create | ${E}paginas/nuevo
custom-pages/1/edit | ${E}paginas/1
asistentes/attendee-admins | ${E}asistentes
asistentes/attendee-admins/2/edit | ${E}asistentes/2
asistentes/attendee-bans | -
- | ${E}asistentes/que-le-paso
entrada/registration-settings/1/edit | ${E}entrada
entrada/registration-fields | ${E}entrada/campos
entrada/registration-fields/create | ${E}entrada/campos/nuevo
entrada/registration-fields/1/edit | ${E}entrada/campos/1
entrada/access-codes | ${E}entrada/codigos
- | ${E}entrada/codigos/nuevo
entrada/landing-widget | ${E}entrada/landing
anuncios/announcements | ${E}anuncios
anuncios/announcements/create | ${E}anuncios/nuevo
anuncios/announcements/1/edit | ${E}anuncios/1
anuncios/scheduled-notifications | ${E}anuncios/push-programadas
anuncios/scheduled-notifications/create | ${E}anuncios/push-programadas/nueva
anuncios/reminder-settings/1/edit | ${E}anuncios/recordatorios
emails/email-templates | ${E}emails
emails/organization-email-settings/1/edit | ${E}emails/envio
emails/email-logs | ${E}emails/historial
identidad/event-branding/1/edit | ${E}identidad
- | ${E}identidad/onboarding
- | ${E}identidad/escarapela
identidad/login-slides | ${E}identidad/slides
identidad/login-slides/1/edit | ${E}identidad/slides/1
identidad/highlights | ${E}identidad/destacados
identidad/highlights/create | ${E}identidad/destacados/nuevo
identidad/highlights/1/edit | ${E}identidad/destacados/1
identidad/event-faqs | ${E}identidad/preguntas
identidad/event-faqs/create | ${E}identidad/preguntas/nuevo
identidad/event-faqs/1/edit | ${E}identidad/preguntas/1
identidad/event-recap-settings/1/edit | ${E}identidad/recap
modulos | ${E}modulos
social/wall-posts | ${E}social
social/wall-posts/1 | ${E}social/1
social/event-photos | ${E}social/fotos
social/event-photos/1 | ${E}social/fotos/1
social/wall-settings/1/edit | ${E}social/ajustes
gamificacion/gamification-settings/1/edit | ${E}gamificacion
gamificacion/golden-tickets | ${E}gamificacion/golden-tickets
- | ${E}gamificacion/golden-tickets/nuevo
gamificacion/rewards | ${E}gamificacion/premios
gamificacion/rewards/create | ${E}gamificacion/premios/nuevo
gamificacion/rewards/7/edit | ${E}gamificacion/premios/7
gamificacion/prize-redemptions | ${E}gamificacion/ganados
salones | ${E}salones
salones/1/edit | ${E}salones/1
chat-settings/1/edit | ${E}chat
event-pulse/1/edit | ${E}eventPulse
support-requests | ${E}soporte
support-requests/4/edit | ${E}soporte/4
encuestas | ${E}encuestas
encuestas/8/edit | ${E}encuestas/8
live-game-results | -
events | ${E}eventos
events/create | eventos/nuevo
users | ${E}staff
users/create | ${E}staff/nuevo
users/2/edit | ${E}staff/2
rate-limit-settings/1/edit | ${E}limites
webhooks/webhook-endpoints | ${E}webhooks
webhooks/webhook-endpoints/create | ${E}webhooks/nuevo
webhooks/webhook-endpoints/1/edit | ${E}webhooks/1
webhooks/webhook-api-keys | ${E}webhooks/llaves
webhooks/webhook-api-keys/create | ${E}webhooks/llaves/nueva
webhooks/webhook-logs | ${E}webhooks/historial
- | ${E}ayuda
- | torre
`
  .trim()
  .split("\n")
  .map((l) => l.split("|").map((s) => s.trim()));

// La ficha de una pantalla: lo que se puede comparar sin mirar. Corre dentro de la pagina.
function ficha() {
  const main = document.querySelector("main") || document.body;
  const visible = (el) => {
    const r = el.getBoundingClientRect();
    const s = getComputedStyle(el);
    return r.width > 0 && r.height > 0 && s.visibility !== "hidden" && s.display !== "none";
  };
  const txt = (el) => ((el && (el.innerText || el.textContent)) || "").trim().replace(/\s+/g, " ").slice(0, 90);
  const q = (sel) => [...main.querySelectorAll(sel)].filter(visible);
  const unicos = (a) => [...new Set(a.filter(Boolean))];
  const control = (l) => {
    const c = l.htmlFor ? document.getElementById(l.htmlFor) : l.querySelector("input,select,textarea,[contenteditable]");
    if (!c) return "";
    if (c.getAttribute("role") === "switch" || c.closest(".fi-fo-toggle")) return "interruptor";
    if (c.tagName === "INPUT") return c.type;
    return c.tagName.toLowerCase();
  };
  const contenido = main.querySelector(".fi-page, .adm-main > *") || main;
  return {
    titulo: txt(main.querySelector("h1") || document.querySelector("h1")),
    migas: txt(document.querySelector(".fi-breadcrumbs, .adm-ruta, nav[aria-label*='readcrumb' i]")),
    pestanas: q("[role=tab], .fi-tabs-item, .adm-pestanas a, .adm-pestanas button").map((t) => txt(t) + (t.querySelector("svg") ? " (icono)" : "")),
    columnas: unicos(q("thead th").map(txt)),
    filas: q("tbody tr").length,
    accionesDeFila: unicos(q("tbody tr:first-child a, tbody tr:first-child button").map(txt)),
    botones: unicos(q(".fi-header button, .fi-header a.fi-btn, .fi-ac-btn-action, .adm-cabecera button, .adm-cabecera a, .adm-acciones button, .adm-acciones a").map(txt)),
    secciones: unicos(q("h2, h3, .fi-section-header-heading, legend").map(txt)).slice(0, 30),
    campos: unicos(q("label").map((l) => txt(l) + (control(l) ? ` [${control(l)}]` : ""))).filter((x) => x.length > 1).slice(0, 70),
    subidas: q("input[type=file], .filepond--root").length + [...main.querySelectorAll("input[type=file]")].filter((i) => !visible(i)).length,
    arrastrar: /arrastra|drag & drop|suelta/i.test(main.innerText),
    editorDeTexto: q(".ProseMirror, trix-editor, .tiptap, [contenteditable=true]").length,
    fotos: q("img").slice(0, 6).map((i) => `${Math.round(i.getBoundingClientRect().width)}x${Math.round(i.getBoundingClientRect().height)} r=${getComputedStyle(i).borderRadius}`),
    iconos: q("svg").length,
    insignias: unicos(q(".fi-badge, .adm-insignia").map(txt)).slice(0, 12),
    vacio: txt(main.querySelector(".fi-ta-empty-state, .adm-vacio")),
    paginacion: txt(main.querySelector(".fi-pagination, .fi-ta-pagination, .adm-paginacion, nav[aria-label*='aginaci' i]")),
    buscar: q("input[type=search], input[placeholder*='uscar']").length,
    filtros: unicos(q(".fi-ta-filters label, .fi-dropdown-trigger, .adm-filtros label, .adm-filtros select").map(txt)).slice(0, 10),
    anchoDelContenido: Math.round(contenido.getBoundingClientRect().width),
    textoLargo: (main.innerText || "").length,
  };
}

const browser = await chromium.launch({ headless: false });
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 });
const fil = await ctx.newPage();
await fil.goto(FIL + "login");
console.log("1) Entra a Filament en la primera pestaña...");
await fil.waitForURL((u) => /\/admin\/?$/.test(u.pathname), { timeout: 15 * 60_000 });
const nue = await ctx.newPage();
await nue.goto(NUE + "login");
console.log("2) Ahora entra al panel de EventOS en la segunda pestaña...");
await nue.waitForURL((u) => /\/admin\/eventos\/\d+/.test(u.pathname), { timeout: 15 * 60_000 });
// Noir en los dos: Filament guarda "dark" y el panel nuevo "noir".
await fil.evaluate(() => localStorage.setItem("theme", "dark"));
await nue.evaluate(() => localStorage.setItem("theme", "noir"));

fs.mkdirSync(`${OUT}/capturas`, { recursive: true });
const resultado = [];
async function medir(page, base, ruta, nombre) {
  if (ruta === "-") return null;
  try {
    const r = await page.goto(base + ruta, { waitUntil: "networkidle", timeout: 45_000 });
    await page.waitForTimeout(800);
    await page.screenshot({ path: `${OUT}/capturas/${nombre}.png`, fullPage: true });
    return { url: page.url().replace(/^https?:\/\/[^/]+/, ""), estado: r?.status() ?? 0, ...(await page.evaluate(ficha)) };
  } catch (e) {
    return { url: base + ruta, error: e.message.split("\n")[0] };
  }
}
for (const [i, [f, n]] of PARES.entries()) {
  const id = String(i + 1).padStart(2, "0");
  const par = { id, filament: f, nuevo: n, f: await medir(fil, FIL, f, `${id}-f`), n: await medir(nue, NUE, n, `${id}-n`) };
  resultado.push(par);
  console.log(id, f, "|", n, "|", par.f?.estado ?? "-", par.n?.estado ?? "-", par.f?.error || par.n?.error || "");
  fs.writeFileSync(`${OUT}/fichas.json`, JSON.stringify(resultado, null, 1));
}
console.log("Listo:", resultado.length, "pares en", OUT);
await browser.close();
