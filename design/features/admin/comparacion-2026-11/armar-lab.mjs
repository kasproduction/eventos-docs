import fs from "node:fs";
import { TRANSVERSAL, SECCIONES, ARREGLADO, ERRORES_DE_LA_PASADA } from "./datos-lab.mjs";

const NOMBRE = { defecto: "Defecto", falta: "Falta", decision: "Decisión de diseño" };
const esc = (s) => String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
const chip = (c) => `<span class="estado ${c}">${NOMBRE[c]}</span>`;

const cuenta = { defecto: 0, falta: 0, decision: 0 };
TRANSVERSAL.forEach(([c]) => cuenta[c]++);
SECCIONES.forEach(([, , ds]) => ds.forEach(([c]) => cuenta[c]++));
const sinVista = SECCIONES.filter(([, , , p]) => p.vista === "no").length;
const lista = (a) => `<ul class="puntos">${a.map((t) => `<li>${esc(t)}</li>`).join("")}</ul>`;

const filasT = TRANSVERSAL.map(([c, que, det, donde]) => `<tr><td>${chip(c)}</td><td><strong>${esc(que)}</strong><br>${esc(det)}</td><td>${esc(donde)}</td></tr>`).join("\n");
const filasS = SECCIONES.map(([sec, n, ds, p]) => {
  const difs = ds.length ? `<ul>${ds.map(([c, t]) => `<li>${chip(c)} ${esc(t)}</li>`).join("")}</ul>` : `<span class="igual">Sin diferencias medidas fuera de lo que vale para todo el panel</span>`;
  const vista = p.vista === "no" ? `<span class="sin">Solo afirmación</span>` : esc(p.vista);
  return `<tr><td><strong>${esc(sec)}</strong><br><span class="tenue">${esc(n)} pantallas</span></td><td>${difs}</td><td>${esc(p.datos)}</td><td>${esc(p.uso)}</td><td>${vista}</td></tr>`;
}).join("\n");

const html = `<title>Panel nuevo contra Filament</title>
<meta name="claude:viewport-fit-sides" content="cover">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Urbanist:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap">
<style>
/* Inventario: resumen arriba, lo transversal, y una fila por seccion con sus diferencias y que la prueba. Paleta del propio panel. */
:root {
  --bg: #F6F8FA; --sup: #FFFFFF; --t: #131417; --t2: rgba(19,20,23,0.66); --t3: rgba(19,20,23,0.45); --linea: rgba(19,20,23,0.10);
  --rojo: #B4232A; --rojo-bg: rgba(180,35,42,0.09); --ambar: #8A5A00; --ambar-bg: rgba(170,110,0,0.11);
  --verde: #2F6B3A; --verde-bg: rgba(47,107,58,0.10); --azul: #2E5BCA; --azul-bg: rgba(46,91,202,0.10);
  --f: "Urbanist", system-ui, -apple-system, sans-serif; --fd: "Plus Jakarta Sans", "Urbanist", system-ui, sans-serif;
}
@media (prefers-color-scheme: dark) { :root:not([data-theme="light"]) {
  --bg: #0A0A0A; --sup: #121212; --t: #FFFFFF; --t2: rgba(255,255,255,0.66); --t3: rgba(255,255,255,0.42); --linea: rgba(255,255,255,0.09);
  --rojo: #FF8A8F; --rojo-bg: rgba(255,90,95,0.13); --ambar: #F2C14E; --ambar-bg: rgba(242,193,78,0.12);
  --verde: #7FD08E; --verde-bg: rgba(127,208,142,0.12); --azul: #8AB0FF; --azul-bg: rgba(138,176,255,0.12); color-scheme: dark; } }
:root[data-theme="dark"] {
  --bg: #0A0A0A; --sup: #121212; --t: #FFFFFF; --t2: rgba(255,255,255,0.66); --t3: rgba(255,255,255,0.42); --linea: rgba(255,255,255,0.09);
  --rojo: #FF8A8F; --rojo-bg: rgba(255,90,95,0.13); --ambar: #F2C14E; --ambar-bg: rgba(242,193,78,0.12);
  --verde: #7FD08E; --verde-bg: rgba(127,208,142,0.12); --azul: #8AB0FF; --azul-bg: rgba(138,176,255,0.12); color-scheme: dark; }
body { background: var(--bg); color: var(--t); font: 400 15px/1.55 var(--f); }
.envoltura { max-width: 1280px; margin: 0 auto; padding-block: 32px 64px; padding-left: max(16px, env(safe-area-inset-left, 0px)); padding-right: max(16px, env(safe-area-inset-right, 0px)); display: grid; gap: 32px; }
h1 { font: 800 28px/1.15 var(--fd); letter-spacing: -0.5px; margin: 0; text-wrap: balance; }
h2 { font: 800 19px/1.25 var(--fd); letter-spacing: -0.3px; margin: 0; }
p { margin: 0; max-width: 78ch; color: var(--t2); }
header, section { display: grid; gap: 12px; }
.resumen { display: flex; flex-wrap: wrap; gap: 12px; }
.cifra { background: var(--sup); border: 1px solid var(--linea); border-radius: 10px; padding: 14px 18px; min-width: 150px; display: grid; gap: 2px; }
.cifra b { font: 800 26px/1 var(--fd); font-variant-numeric: tabular-nums; }
.cifra span { color: var(--t2); font-size: 14px; }
.tabla { overflow-x: auto; background: var(--sup); border: 1px solid var(--linea); border-radius: 10px; }
table { border-collapse: collapse; width: 100%; min-width: 900px; }
th { text-align: left; font-size: 13px; font-weight: 600; color: var(--t3); padding: 12px 16px; border-bottom: 1px solid var(--linea); }
td { padding: 14px 16px; border-bottom: 1px solid var(--linea); vertical-align: top; }
tr:last-child td { border-bottom: 0; }
ul { margin: 0; padding: 0; list-style: none; display: grid; gap: 8px; }
li { line-height: 1.5; }
.estado { display: inline-block; font-size: 12.5px; font-weight: 600; padding: 1px 9px; border-radius: 999px; white-space: nowrap; margin-right: 4px; }
.defecto { color: var(--rojo); background: var(--rojo-bg); }
.falta { color: var(--ambar); background: var(--ambar-bg); }
.decision { color: var(--azul); background: var(--azul-bg); }
.puntos { display: grid; gap: 6px; padding-left: 20px; list-style: disc; color: var(--t2); max-width: 78ch; }
.igual { color: var(--verde); }
.sin { color: var(--rojo); font-weight: 600; }
.tenue { color: var(--t3); font-size: 13px; }
</style>

<div class="envoltura">
  <header>
    <h1>Panel nuevo contra Filament</h1>
    <p>Corrida final del 25 de noviembre, después del bloque de arreglos (los 3 defectos que dejó, BUG-573 a 575, se arreglaron el mismo día con su prueba): los mismos 91 pares de pantallas de las 26 secciones que la pasada del 24, en Noir y a 1440 px. Cada pantalla se midió en el navegador (título, pestañas, columnas, campos y su tipo, subidas, fotos, paginación, filtros y buscador) y lo que cambió se revisó en las capturas. Las 7 diferencias que Kamilo decidió dejar cuentan como decisión de diseño, no como defecto. Lo arreglado sale de la lista y queda abajo.</p>
  </header>

  <div class="resumen">
    <div class="cifra"><b>${cuenta.defecto}</b><span>Defectos</span></div>
    <div class="cifra"><b>${cuenta.falta}</b><span>Faltas</span></div>
    <div class="cifra"><b>${cuenta.decision}</b><span>Decisiones de diseño</span></div>
    <div class="cifra"><b>${SECCIONES.length - sinVista} de ${SECCIONES.length}</b><span>secciones comparadas contra Filament</span></div>
  </div>

  <section>
    <h2>Cómo leer la lista</h2>
    <p><strong>Defecto:</strong> existe y se ve o funciona distinto de Filament, y no está escrito que sea a propósito. <strong>Falta:</strong> Filament lo tiene y el nuevo no. <strong>Decisión de diseño:</strong> el cambio está escrito (decisiones.md, el mapa de pantallas o una regla de Kamilo).</p>
    <p>En las pruebas: <strong>datos</strong> son los contratos de C# y las cruzadas con PHP (lo que se guarda y se lee), <strong>uso</strong> las e2e del panel contra el C# de verdad (crear, editar, borrar, permisos), y <strong>vista</strong> una comparación medida contra Filament. La vista de todas las secciones se comparó en dos corridas (24 y 25 de noviembre); solo el menú tiene además una prueba que lo vigila (marco.spec.ts). Una comparación no es una prueba: si una pantalla cambia después, nada avisa.</p>
  </section>

  <section>
    <h2>Vale para todo el panel</h2>
    <div class="tabla"><table>
      <thead><tr><th>Estado</th><th>Qué</th><th>Dónde</th></tr></thead>
      <tbody>
${filasT}
      </tbody>
    </table></div>
  </section>

  <section>
    <h2>Por sección</h2>
    <div class="tabla"><table>
      <thead><tr><th>Sección</th><th>Diferencias</th><th>Prueba de datos</th><th>Prueba de uso</th><th>Vista</th></tr></thead>
      <tbody>
${filasS}
      </tbody>
    </table></div>
  </section>

  <section>
    <h2>Arreglado desde la pasada</h2>
    ${lista(ARREGLADO)}
  </section>

  <section>
    <h2>Errores de la pasada</h2>
    ${lista(ERRORES_DE_LA_PASADA)}
  </section>

  <section>
    <h2>Lo que la medición no cubrió</h2>
    <p>Los formularios de crear y editar que Filament ya no muestra (quedó de solo lectura: 403 o 404) se comparan contra sus capturas de octubre, sin ficha medida; Kamilo confirmó que ninguno perdió un campo editable. Landing y widget no tiene ficha (la vista previa en vivo cortó la medición). Las plantillas de correo no estaban en la lista de pares y se midieron a mano. Lux no se midió pantalla por pantalla: el menú ya se probó en los dos temas. Las capturas y las fichas están en design/features/admin/comparacion-2026-11.</p>
  </section>
</div>`;

fs.writeFileSync(process.argv[2], html);
console.log("ok", cuenta, "sin vista", sinVista);
