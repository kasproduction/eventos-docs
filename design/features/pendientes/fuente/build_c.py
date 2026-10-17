import io, json
SP = r"C:\Users\KASPRO~1\AppData\Local\Temp\claude\C--laragon-www-APP-EVENTOS\d48b02a8-536c-405b-be6d-313a2be22e0d\scratchpad\\"
OUT = r"C:\laragon\www\APP EVENTOS\design\features\pendientes\lab-tablero-c.html"
s = io.open(SP + "lab-tablero.tpl.html", encoding="utf-8").read()

def R(a, b):
    global s
    assert s.count(a) == 1, (a[:80], s.count(a))
    s = s.replace(a, b)

R('<title>Lab tablero de pendientes</title>', '<title>Lab tablero C</title>')
R('<b>Lab tablero</b>', '<b>Lab tablero C</b>')
R('    <div class="seg" id="segDir"><button data-d="A" class="on">A Por prioridad</button><button data-d="B">B Por módulo</button><button data-d="C">C Módulo y prioridad</button></div>',
  '    <div class="seg" id="segFilt"><button data-f="C1" class="on">C1 Lista lateral</button><button data-f="C2">C2 Chips en filas</button></div>\n'
  '    <div class="seg" id="segLang"><button data-l="simple" class="on">Lenguaje simple</button><button data-l="actual">Lenguaje actual</button></div>')
R('Datos reales de PENDIENTES.md. En el lab las prioridades se guardan solo en este navegador.',
  '15 tareas ya están en lenguaje simple: búscalas en "Ejemplos de lenguaje".')

# ---------- CSS ----------
R('.stage{flex:1 1 auto;min-height:0;position:relative;display:flex}', '''.stage{flex:1 1 auto;min-height:0;position:relative;display:flex}
.rail{flex:0 0 212px;border-right:1px solid var(--line2);overflow-y:auto;padding:12px 10px 24px max(10px,env(safe-area-inset-left));display:flex;flex-direction:column;gap:2px}
.rail[hidden]{display:none}
.rail button{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:5px 8px;text-align:left;padding:9px 10px;border-radius:10px;min-height:44px;color:var(--t2)}
.rail button:hover{background:var(--sin-s)}
.rail button.on{background:var(--ink);color:var(--onink)}
.rail button b{font-weight:600;font-size:14px;line-height:1.25}
.rail button span{font-size:12.5px;font-variant-numeric:tabular-nums;opacity:.7}
.rail button i{grid-column:1/-1;height:3px;border-radius:99px;background:var(--sin-s);overflow:hidden;display:flex}
.rail button i em{display:block;height:100%;background:currentColor;opacity:.55}
.rail .rsep{height:1px;background:var(--line2);margin:8px 6px}
.modbtn{display:none;align-self:flex-start;padding:8px 14px;border-radius:99px;border:1px solid var(--line2);font-weight:700;font-size:14px;min-height:40px}
.mods.wrap{flex-wrap:wrap;overflow:visible}
.tools2{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.tools2 .lab2{color:var(--t3);font-size:13px;font-weight:600}
.tseg{display:flex;background:var(--sin-s);border-radius:10px;padding:2px;gap:2px;flex-wrap:wrap}
.tseg button{padding:6px 11px;border-radius:8px;font-size:13px;font-weight:600;color:var(--t2);min-height:34px}
.tseg button.on{background:var(--card);color:var(--t1);box-shadow:var(--shadow)}
:root:not([data-theme="lux"]) .tseg button.on{background:var(--ink);color:var(--onink)}
.sheet{position:fixed;inset:auto 0 0 0;z-index:60;background:var(--bg);border-top:1px solid var(--line2);border-radius:20px 20px 0 0;max-height:75%;overflow-y:auto;padding:14px 16px max(20px,env(safe-area-inset-bottom));box-shadow:0 -20px 50px rgba(0,0,0,.35)}
.sheet h3{font-size:16px;font-weight:800;margin:4px 4px 10px}
.sheet[hidden]{display:none}
.lsec{display:flex;flex-direction:column;gap:4px}
.lsec small{color:var(--t3);font-size:12.5px;font-weight:700}
.lsec div{font-size:15px;line-height:1.55;color:var(--t1)}
.alert{display:flex;gap:10px;align-items:flex-start;background:var(--ya-s);color:var(--t1);border-radius:12px;padding:10px 12px;font-size:13.5px;line-height:1.45;margin:2px 0 8px;max-width:760px;position:sticky;left:0}
.alert b{color:var(--ya);font-weight:700;white-space:nowrap}
.pill.blk{background:var(--ya-s);color:var(--ya)}
.deps{display:flex;flex-direction:column;gap:6px}
.deps button{text-align:left;padding:10px 12px;border-radius:10px;border:1px solid var(--line2);font-size:14px;font-weight:600;min-height:44px}''')
R('.C{padding:0 max(20px,env(safe-area-inset-right)) 32px max(20px,env(safe-area-inset-left));min-width:1100px}',
  '.C{padding:0 max(20px,env(safe-area-inset-right)) 32px max(20px,env(safe-area-inset-left));min-width:900px}')
R('@media (max-width:899px){', '@media (max-width:899px){\n  .rail{display:none!important}\n  .modbtn.c1{display:inline-flex}')

# ---------- HTML ----------
R('    <div class="mods" id="mods"></div>',
  '    <div class="mods" id="mods"></div>\n    <button class="modbtn" id="modbtn"></button>\n'
  '    <div class="tools2"><span class="lab2">Mostrar</span><div class="tseg" id="segBlk"></div><span class="lab2">Ordenar módulos</span><div class="tseg" id="segOrd"></div></div>')
R('  <div class="stage">\n    <div class="view" id="view"></div>', '  <div class="stage">\n    <aside class="rail" id="rail" hidden></aside>\n    <div class="view" id="view"></div>')
R('    <aside class="detail" id="detail" hidden></aside>', '    <aside class="detail" id="detail" hidden></aside>\n    <div class="sheet" id="sheet" hidden></div>')

# ---------- JS ----------
R('const D = JSON.parse(document.getElementById("data").textContent);',
  'const D = JSON.parse(document.getElementById("data").textContent);\nconst SIMPLE = D.simples || {}, MODDEP = D.moddep || {};')
R('let S = {dir:"A", theme:"noir", mod:"todos", sel:null, acol:"sin", closed:{}};',
  'let S = {dir:"C", theme:"noir", mod:"todos", sel:null, acol:"sin", closed:{}, filt:"C1", lang:"simple", blk:"todas", ord:"normal"};')
R('try { Object.assign(S, JSON.parse(localStorage.getItem("lab-state")||"{}")); S.sel = null; } catch(e) {}',
  'try { Object.assign(S, JSON.parse(localStorage.getItem("lab-c-state")||"{}")); S.sel = null; S.dir = "C"; } catch(e) {}')
R('localStorage.setItem("lab-state", JSON.stringify({dir:S.dir,theme:S.theme,mod:S.mod,acol:S.acol,closed:S.closed}));',
  'localStorage.setItem("lab-c-state", JSON.stringify({theme:S.theme,mod:S.mod,closed:S.closed,filt:S.filt,lang:S.lang,blk:S.blk,ord:S.ord}));')
R('const visible = () => T.filter(t => S.mod === "todos" || t.mod === S.mod);', '''const BYID = Object.fromEntries(T.map(t => [t.id, t]));
const isEj = t => !!SIMPLE[t.id];
const openBlockers = t => (t.dep || []).map(id => BYID[id]).filter(b => b && pr(b.id) !== "descartar");
const blocked = t => openBlockers(t).length > 0;
const code = t => { const m = t.titulo.match(/^([A-Z]+\\d*(?:\\.\\d+)+)/); return m ? m[1] : null; };
const inMod = t => S.mod === "todos" || (S.mod === "ejemplos" ? isEj(t) : t.mod === S.mod);
const inBlk = t => S.blk === "todas" || (S.blk === "bloq" ? blocked(t) : !blocked(t));
const visible = () => T.filter(t => inMod(t) && inBlk(t));
const TT = t => (S.lang === "simple" && SIMPLE[t.id]) ? SIMPLE[t.id].titulo : t.titulo;
const DD = t => (S.lang === "simple" && SIMPLE[t.id]) ? SIMPLE[t.id].que : t.desc;''')
R('  c.append(el("div",{cls:"ct",text:t.titulo}));\n  if (t.desc) c.append(el("div",{cls:"cd",text:t.desc}));\n', '  c.append(el("div",{cls:"ct",text:TT(t)}));\n  if (DD(t)) c.append(el("div",{cls:"cd",text:DD(t)}));\n')
R('  if (S.dir !== "C" && S.mod === "todos") m.append(el("span",{cls:"lbl",text:MODN[t.mod]}));',
  '  if (S.mod === "ejemplos") m.append(el("span",{cls:"lbl",text:MODN[t.mod]}));\n'
  '  const ob = openBlockers(t); if (ob.length) m.append(el("span",{cls:"pill blk",text:"Espera a " + (ob.length === 1 ? (code(ob[0]) || "otra tarea") : ob.length + " tareas")}));')

# cabecera: rail C1, chips C2, filtros de bloqueo y orden
R('''  const m = document.getElementById("mods");
  m.replaceChildren(''', '''  const ej = T.filter(isEj).length;
  const prog = k => { const its = T.filter(t => k === "todos" || (k === "ejemplos" ? isEj(t) : t.mod === k)); return its.length ? its.filter(t=>pr(t.id)!=="sin").length/its.length : 0; };
  const rail = document.getElementById("rail"); rail.hidden = S.filt !== "C1";
  const rb = (k, label, n) => el("button",{cls:S.mod===k?"on":"",onclick:()=>{S.mod=k; closeSheet(); render();}}, el("b",{text:label}), el("span",{text:String(n)}));
  const items = () => [rb("todos","Todos los módulos",T.length), rb("ejemplos","Ejemplos de lenguaje",ej), el("div",{cls:"rsep"}), ...MODS.map(x => rb(x.k, x.t, x.n))];
  rail.replaceChildren(...items());
  const mb = document.getElementById("modbtn"); mb.className = "modbtn" + (S.filt === "C1" ? " c1" : "");
  mb.textContent = "Módulo: " + (S.mod === "todos" ? "Todos" : S.mod === "ejemplos" ? "Ejemplos de lenguaje" : MODN[S.mod]) + "  ⌄";
  mb.onclick = () => { const sh = document.getElementById("sheet"); const r = el("div",{cls:"rail"}); r.style.border = "0"; r.style.padding = "0"; r.append(...items()); sh.replaceChildren(el("h3",{text:"Elige un módulo"}), r); sh.hidden = false; document.getElementById("scrim").hidden = false; };
  const nb = T.filter(t => inMod(t) && blocked(t)).length, nl = T.filter(t => inMod(t) && !blocked(t)).length;
  document.getElementById("segBlk").replaceChildren(...[["todas","Todas"],["libres","Se pueden hacer ya ("+nl+")"],["bloq","Bloqueadas ("+nb+")"]].map(([v,l]) => el("button",{cls:S.blk===v?"on":"",onclick:()=>{S.blk=v;save();render();}},l)));
  document.getElementById("segOrd").replaceChildren(...[["normal","Normal"],["cantidad","Más tareas"],["urgencia","Más urgentes"],["bloqueos","Más bloqueadas"]].map(([v,l]) => el("button",{cls:S.ord===v?"on":"",onclick:()=>{S.ord=v;save();render();}},l)));
  const m = document.getElementById("mods");
  m.className = "mods wrap";
  m.style.display = S.filt === "C1" ? "none" : "";
  if (S.filt === "C1") return;
  m.replaceChildren(''')
R('''    ...MODS.map(x => el("button",{cls:"mod-chip"+(S.mod===x.k?" on":""),onclick:()=>{S.mod=x.k;render();}}, x.t, el("span",{text:String(x.n)}))));
}''', '''    el("button",{cls:"mod-chip"+(S.mod==="ejemplos"?" on":""),onclick:()=>{S.mod="ejemplos";render();}},"Ejemplos de lenguaje",el("span",{text:String(ej)})),
    ...MODS.map(x => el("button",{cls:"mod-chip"+(S.mod===x.k?" on":""),onclick:()=>{S.mod=x.k;render();}}, x.t, el("span",{text:String(x.n)}))));
}
function closeSheet(){ const sh = document.getElementById("sheet"); if (sh) sh.hidden = true; if (!S.sel) document.getElementById("scrim").hidden = true; }''')

# carriles de C: filtro, orden y alerta de dependencia
R('''  for (const m of MODS) {
    if (S.mod !== "todos" && S.mod !== m.k) continue;
    const items = T.filter(t => t.mod === m.k);
    const lane = el("section",{cls:"lane"+(S.closed["c"+m.k]?" closed":"")});
    lane.append(el("button",{cls:"laneh",onclick:()=>{S.closed["c"+m.k]=!S.closed["c"+m.k]; save(); render();}}, el("span",{cls:"chev",text:"⌄"}), el("h3",{text:m.t}), el("span",{cls:"cnt",text:items.length+" tareas"})));''',
'''  const score = k => { const its = T.filter(t => t.mod === k && inMod(t) && inBlk(t));
    return S.ord === "cantidad" ? its.length : S.ord === "urgencia" ? its.filter(t => ["ya","antes"].includes(pr(t.id))).length : S.ord === "bloqueos" ? its.filter(blocked).length : 0; };
  const lanes = S.ord === "normal" ? MODS : [...MODS].sort((a,b) => score(b.k) - score(a.k));
  for (const m of lanes) {
    if (S.mod !== "todos" && S.mod !== "ejemplos" && S.mod !== m.k) continue;
    const items = T.filter(t => t.mod === m.k && inMod(t) && inBlk(t));
    if (!items.length) continue;
    const lane = el("section",{cls:"lane"+(S.closed["c"+m.k]?" closed":"")});
    const nbl = items.filter(blocked).length;
    lane.append(el("button",{cls:"laneh",onclick:()=>{S.closed["c"+m.k]=!S.closed["c"+m.k]; save(); render();}}, el("span",{cls:"chev",text:"⌄"}), el("h3",{text:m.t}), el("span",{cls:"cnt",text:items.length+" tareas" + (nbl ? ", " + nbl + " bloqueadas" : "")})));
    const md = MODDEP[m.k];
    if (md) { const open = T.filter(t => t.mod === md.mod && !["descartar"].includes(pr(t.id))).length; if (open) lane.append(el("div",{cls:"alert"}, el("b",{text:"Depende de " + MODN[md.mod]}), el("span",{text: md.texto + " Quedan " + open + " tareas ahí."}))); }''')

R('      const cell = el("div",{cls:"cell","data-p":c.v,"data-m":m.k});', '      const cell = el("div",{cls:"cell","data-p":c.v,"data-m":m.k,"data-label":c.l});')
R('@media (max-width:600px){', '@media (max-width:600px){\n  .C{min-width:0}\n  .C .chead{display:none}\n  .cells{grid-template-columns:1fr}\n  .cell::before{content:attr(data-label);font-family:var(--display);font-weight:700;font-size:13px;color:var(--t2);padding:2px 4px}\n  .cell{min-height:44px}\n  .alert{flex-wrap:wrap;position:static}')
# detalle: secciones simples + dependencias
R('''    el("h2",{text:t.titulo}),
    t.desc ? el("div",{cls:"ddesc",text:t.desc}) : null,''', '''    el("h2",{text:TT(t)}),
    ...((S.lang === "simple" && SIMPLE[t.id]) ? [
      el("div",{cls:"lsec"}, el("small",{text:"Qué es"}), el("div",{text:SIMPLE[t.id].que})),
      el("div",{cls:"lsec"}, el("small",{text:"Por qué importa"}), el("div",{text:SIMPLE[t.id].porque})),
      el("div",{cls:"lsec"}, el("small",{text:"Cuándo está lista"}), el("div",{text:SIMPLE[t.id].lista}))
    ] : [t.desc ? el("div",{cls:"ddesc",text:t.desc}) : null]),
    openBlockers(t).length ? el("div",{cls:"dk"}, el("small",{text:"Antes hay que terminar"}), el("div",{cls:"deps"}, ...openBlockers(t).map(b => el("button",{onclick:()=>openDetail(b.id)}, TT(b))))) : null,
    (() => { const w = T.filter(x => (x.dep||[]).includes(t.id)); return w.length ? el("div",{cls:"dk"}, el("small",{text:"Cuando esté lista desbloquea " + w.length + (w.length === 1 ? " tarea" : " tareas")}), el("div",{text: w.slice(0,6).map(TT).join(", ") + (w.length > 6 ? " y " + (w.length-6) + " más" : "")})) : null; })(),''')
R('document.getElementById("scrim").addEventListener("click", closeDetail);',
  'document.getElementById("scrim").addEventListener("click", () => { const sh = document.getElementById("sheet"); if (!sh.hidden) closeSheet(); else closeDetail(); });')
R('document.getElementById("segDir").addEventListener("click", e => { const b = e.target.closest("button"); if (b) { S.dir = b.dataset.d; save(); render(); } });',
  'document.getElementById("segFilt").addEventListener("click", e => { const b = e.target.closest("button"); if (b) { S.filt = b.dataset.f; save(); render(); } });\n'
  'document.getElementById("segLang").addEventListener("click", e => { const b = e.target.closest("button"); if (b) { S.lang = b.dataset.l; save(); render(); } });')
R('  document.querySelectorAll("#segDir button").forEach(b => b.classList.toggle("on", b.dataset.d === S.dir));',
  '  document.querySelectorAll("#segFilt button").forEach(b => b.classList.toggle("on", b.dataset.f === S.filt));\n'
  '  document.querySelectorAll("#segLang button").forEach(b => b.classList.toggle("on", b.dataset.l === S.lang));')
R('addEventListener("keydown", e => { if (e.key === "Escape") { closeMenu(); closeDetail(); } });',
  'addEventListener("keydown", e => { if (e.key === "Escape") { closeMenu(); closeSheet(); closeDetail(); } });')
R('  document.getElementById("atabs").style.display = S.dir === "A" ? "" : "none";', '  document.getElementById("atabs").style.display = "none";')
R('  document.getElementById("sub").textContent = v.length + " tareas abiertas" + (S.mod === "todos" ? " en " + MODS.length + " módulos" : " en " + MODN[S.mod]) + ". Arrastra para priorizar o toca una tarea para ver el detalle.";',
  '  document.getElementById("sub").textContent = v.length + " tareas" + (S.mod === "todos" ? " en " + MODS.length + " módulos" : S.mod === "ejemplos" ? " con lenguaje simple de ejemplo" : " en " + MODN[S.mod]) + ". Arrastra para priorizar o toca una tarea para ver el detalle.";')

io.open(SP + "lab-c.tpl.html", "w", encoding="utf-8").write(s)
d = json.load(io.open(SP + "tareas-lab.json", encoding="utf-8"))
d["simples"] = json.load(io.open(SP + "simples.json", encoding="utf-8"))
io.open(OUT, "w", encoding="utf-8").write(s.replace("__DATA__", json.dumps(d, ensure_ascii=False).replace("</", "<\\/")))
print("ok")

# Lux definitivo (decision Kamilo 2026-10-16)
s2 = io.open(OUT, encoding="utf-8").read()
s2 = s2.replace('<html lang="es" data-theme="noir">', '<html lang="es" data-theme="lux">')
s2 = s2.replace('    <div class="seg" id="segTheme"><button data-t="noir" class="on">Noir</button><button data-t="lux">Lux</button></div>\n', '    <div class="seg" id="segTheme" hidden></div>\n')
s2 = s2.replace('let S = {dir:"C", theme:"noir",', 'let S = {dir:"C", theme:"lux",')
s2 = s2.replace('S.sel = null; S.dir = "C"; }', 'S.sel = null; S.dir = "C"; S.theme = "lux"; }')
io.open(OUT, "w", encoding="utf-8").write(s2)
print("lux ok", s2.count('data-t="noir"'))
