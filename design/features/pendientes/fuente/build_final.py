import io, json, re, os
SP = r"C:\Users\KASPRO~1\AppData\Local\Temp\claude\C--laragon-www-APP-EVENTOS\d48b02a8-536c-405b-be6d-313a2be22e0d\scratchpad\\"
OUT = SP + "pendientes-eventos.html"
s = io.open(SP + "lab-c.tpl.html", encoding="utf-8").read()

def R(a, b, n=1):
    global s
    assert s.count(a) == n, (a[:90], s.count(a))
    s = s.replace(a, b)

# ---------- documento: sin esqueleto propio (el Artifact lo agrega) ----------
head_end = s.index("<style>")
s = ('<title>Pendientes EventOS</title>\n<meta name="claude:viewport-fit-sides" content="cover">\n'
     '<link rel="preconnect" href="https://fonts.googleapis.com">\n'
     '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Urbanist:wght@400;500;600;700&display=swap">\n'
     '<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>\n') + s[head_end:]
R("</style>\n</head>\n<body>", "</style>")
R("</script>\n</body>\n</html>", "</script>")
# Lux unico: el bloque Lux pasa a ser :root (gana por ir despues)
R(':root[data-theme="lux"]{', ':root{')
R('/* Lab: 3 direcciones del tablero de pendientes.', '/* Tablero de pendientes EventOS, version Lux (decision Kamilo 2026-10-16).')
s = re.sub(r'  <div class="labbar">.*?\n  </div>\n', '', s, count=1, flags=re.S)
R('.app{height:100%;display:flex;flex-direction:column}', '''.app{height:100%;display:flex;flex-direction:column}
.status{font-size:13.5px;color:var(--t1);background:var(--ya-s);padding:9px 12px;border-radius:10px;max-width:760px}
.status[hidden]{display:none}
.tech{border-top:1px solid var(--line2);padding-top:12px}
.tech summary{cursor:pointer;color:var(--t2);font-weight:700;font-size:13.5px;list-style:none;min-height:36px;display:flex;align-items:center}
.tech summary::-webkit-details-marker{display:none}
.tech summary::after{content:"⌄";margin-left:8px;transition:transform .2s ease}
.tech[open] summary::after{transform:rotate(180deg)}
.tech .tbody{display:flex;flex-direction:column;gap:10px;padding-top:6px;font-size:13.5px;color:var(--t2)}
.tech .tbody b{color:var(--t1);font-weight:600}''')
R('    <div class="mods" id="mods"></div>', '    <div class="status" id="status" hidden></div>\n    <div class="mods" id="mods"></div>')

# ---------- estado y guardado en la base del artifact ----------
R('let S = {dir:"C", theme:"noir", mod:"todos", sel:null, acol:"sin", closed:{}, filt:"C1", lang:"simple", blk:"todas", ord:"normal"};',
  'let S = {dir:"C", mod:"todos", sel:null, acol:"sin", closed:{}, filt:"C1", lang:"simple", blk:"todas", ord:"normal"};')
R('''let P = {};
try { P = JSON.parse(localStorage.getItem("lab-prios")||"{}"); } catch(e) {}
try { Object.assign(S, JSON.parse(localStorage.getItem("lab-c-state")||"{}")); S.sel = null; S.dir = "C"; } catch(e) {}
const save = () => { try { localStorage.setItem("lab-prios", JSON.stringify(P)); localStorage.setItem("lab-c-state", JSON.stringify({theme:S.theme,mod:S.mod,closed:S.closed,filt:S.filt,lang:S.lang,blk:S.blk,ord:S.ord})); } catch(e) {} };''',
'''let P = {};
let DB = null, canWrite = false, typing = false, dragging = false, pendingRender = false;
try { const u = JSON.parse(localStorage.getItem("pend-ui")||"{}"); for (const k of ["mod","closed","blk","ord"]) if (u[k] != null) S[k] = u[k]; } catch(e) {}
const save = () => { try { localStorage.setItem("pend-ui", JSON.stringify({mod:S.mod,closed:S.closed,blk:S.blk,ord:S.ord})); } catch(e) {} };
const setStatus = msg => { const n = document.getElementById("status"); n.textContent = msg || ""; n.hidden = !msg; };
const ro = () => DB !== null && !canWrite;
const dirty = new Set(); let flushT = null;
const touch = id => { dirty.add(id); clearTimeout(flushT); flushT = setTimeout(flush, 500); };
async function flush(){
  if (!DB || !canWrite) return;
  const ids = [...dirty]; dirty.clear();
  for (const id of ids) {
    const v = Object.assign({}, P[id] || {}, {at: new Date().toISOString()});
    try { await DB.collection("prioridades").doc(id).set(v); setStatus(""); }
    catch(e) {
      if (e && e.code === "invalid_argument") { canWrite = false; setStatus("Puedes ver el tablero, pero no cambiarlo con esta cuenta."); render(); return; }
      dirty.add(id); setStatus("No se pudo guardar el último cambio. Revisa la conexión; se vuelve a intentar solo.");
      clearTimeout(flushT); flushT = setTimeout(flush, 4000); return;
    }
  }
}''')
R('function setPrio(id, p){ P[id] = Object.assign({}, P[id]||{}, {p}); if (p === "sin") delete P[id].p; save(); }',
  'function setPrio(id, p){ if (ro()) return; P[id] = Object.assign({}, P[id]||{}, {p}); if (p === "sin") delete P[id].p; touch(id); }')
R('function reorder(listEl, p){ [...listEl.querySelectorAll("[data-id]")].forEach((n,i) => { const id = n.dataset.id; P[id] = Object.assign({}, P[id]||{}, {o:i}); if (p) { if (p === "sin") delete P[id].p; else P[id].p = p; } }); save(); }',
  'function reorder(listEl, p){ [...listEl.querySelectorAll("[data-id]")].forEach((n,i) => { const id = n.dataset.id; const before = JSON.stringify(P[id]||{}); P[id] = Object.assign({}, P[id]||{}, {o:i}); if (p) { if (p === "sin") delete P[id].p; else P[id].p = p; } if (JSON.stringify(P[id]) !== before) touch(id); }); }')
R('const SORT_OPTS = {animation:180,', 'const SORT_OPTS = {onStart: () => { dragging = true; }, animation:180,')
s = s.replace("Object.assign({}, SORT_OPTS, {", "Object.assign({}, SORT_OPTS, {disabled: ro(), ")
s = s.replace("onEnd: e => { reorder(", "onEnd: e => { dragging = false; reorder(")

# ---------- sin filtro de ejemplos (ya todo esta en lenguaje simple) ----------
R('rb("todos","Todos los módulos",T.length), rb("ejemplos","Ejemplos de lenguaje",ej), el("div",{cls:"rsep"}), ', 'rb("todos","Todos los módulos",T.length), el("div",{cls:"rsep"}), ')

# ---------- detalle: nota con guardado y texto tecnico plegado ----------
R('''    el("div",{cls:"dk"}, el("small",{text:"Origen"}), el("div",{text: t.secT + (t.sub ? ", " + t.sub : "")})),
    t.fuentes.length ? el("div",{cls:"dk"}, el("small",{text:"Fuente"}), el("div",{text:t.fuentes.join(", ")})) : null,''', '')
R('''(() => { const n = el("textarea",{cls:"dnote",placeholder:"Por qué, de qué depende, qué cambia"}); n.value = (P[t.id]&&P[t.id].nota)||""; n.addEventListener("input",()=>{ P[t.id]=Object.assign({},P[t.id]||{},{nota:n.value}); save(); }); return n; })())''',
'''(() => { const n = el("textarea",{cls:"dnote",placeholder:"Por qué, de qué depende, qué cambia",id:"nota-"+t.id}); n.value = (P[t.id]&&P[t.id].nota)||""; if (ro()) n.disabled = true;
      n.addEventListener("focus",()=>{ typing = true; });
      n.addEventListener("blur",()=>{ typing = false; if (pendingRender) { pendingRender = false; render(); } });
      n.addEventListener("input",()=>{ P[t.id]=Object.assign({},P[t.id]||{},{nota:n.value}); touch(t.id); }); return n; })()),
    el("details",{cls:"tech"}, el("summary",{},"Ver el texto técnico"), el("div",{cls:"tbody"},
      el("div",{}, el("b",{text:t.titulo})),
      t.desc ? el("div",{text:t.desc}) : null,
      el("div",{text:"Origen: " + t.secT + (t.sub ? ", " + t.sub : "")}),
      t.fuentes.length ? el("div",{text:"Fuente: " + t.fuentes.join(", ")}) : null))''')

# ---------- render sin barra de lab ----------
R('''  document.documentElement.dataset.theme = S.theme;
  document.querySelectorAll("#segFilt button").forEach(b => b.classList.toggle("on", b.dataset.f === S.filt));
  document.querySelectorAll("#segLang button").forEach(b => b.classList.toggle("on", b.dataset.l === S.lang));
  document.querySelectorAll("#segTheme button").forEach(b => b.classList.toggle("on", b.dataset.t === S.theme));
''', '')
R('''document.getElementById("segFilt").addEventListener("click", e => { const b = e.target.closest("button"); if (b) { S.filt = b.dataset.f; save(); render(); } });
document.getElementById("segLang").addEventListener("click", e => { const b = e.target.closest("button"); if (b) { S.lang = b.dataset.l; save(); render(); } });
document.getElementById("segTheme").addEventListener("click", e => { const b = e.target.closest("button"); if (b) { S.theme = b.dataset.t; save(); render(); } });
''', '')
R('''render();
})();''', '''render();
setStatus("Cargando las prioridades guardadas.");
(async function(){
  const use = window.claude && window.claude.use;
  if (!use) { setStatus("Abre esta página desde claude.ai para guardar prioridades. Aquí solo se puede leer."); DB = false; canWrite = false; render(); return; }
  const [db, user] = await Promise.all([use("db"), use("user")]);
  if (!db) { setStatus("Inicia sesión en claude.ai para guardar prioridades. Aquí solo se puede leer."); DB = false; canWrite = false; render(); return; }
  DB = db; canWrite = true;
  try { const w = user ? await user.can("data.write") : null; if (w === false) canWrite = false; } catch(e) {}
  db.collection("prioridades").onSnapshot(snap => {
    const m = {}; for (const d of snap.docs) { const v = d.data(); if (v) m[d.id] = v; }
    for (const id of dirty) if (P[id]) m[id] = P[id];
    P = m; setStatus(canWrite ? "" : "Puedes ver el tablero, pero no cambiarlo con esta cuenta.");
    if (typing || dragging) { pendingRender = true; return; }
    render();
  }, () => setStatus("Se perdió la conexión con lo guardado. Recarga la página."));
  flush();
})();
})();''')

d = json.load(io.open(SP + "tareas-lab.json", encoding="utf-8"))
simples = json.load(io.open(SP + "simples.json", encoding="utf-8"))
for g in ("g1", "g2", "g3", "g4"):
    f = SP + "reescribir\\" + g + "-salida.json"
    if os.path.exists(f):
        simples.update(json.load(io.open(f, encoding="utf-8")))
falt = [t["id"] for t in d["tareas"] if t["id"] not in simples]
print("simples:", len(simples), "faltan:", len(falt))
d["simples"] = simples
io.open(OUT, "w", encoding="utf-8").write(s.replace("__DATA__", json.dumps(d, ensure_ascii=False).replace("</", "<\\/")))
for bad in ("lab-prios", "segTheme", "labbar\"", "Ejemplos de lenguaje\",ej"):
    if bad in s and bad != "segTheme": print("OJO queda:", bad)
print("ok", len(s))

# pildora de bloqueo sin codigos internos
h = io.open(OUT, encoding="utf-8").read()
a = 'text:"Espera a " + (ob.length === 1 ? (code(ob[0]) || "otra tarea") : ob.length + " tareas")'
assert h.count(a) == 1
h = h.replace(a, 'text:"Bloqueada por " + (ob.length === 1 ? "1 tarea" : ob.length + " tareas")')
io.open(OUT, "w", encoding="utf-8").write(h)
print("pildora ok")

# ---------- sugerencias de Claude (2026-10-16) ----------
h = io.open(OUT, encoding="utf-8").read()
def H(a, b):
    global h
    assert h.count(a) == 1, (a[:90], h.count(a))
    h = h.replace(a, b)
SUG = json.load(io.open(SP + "sugerencias.json", encoding="utf-8"))
H('const SIMPLE = D.simples || {}, MODDEP = D.moddep || {};', 'const SIMPLE = D.simples || {}, MODDEP = D.moddep || {}, SUG = D.sug || {};')
H('const pr = id => (P[id] && P[id].p) || "sin";',
  'const isSug = id => !P[id] || !!P[id].sug;\nconst pr = id => P[id] ? (P[id].p || (P[id].sug && SUG[id] ? SUG[id].p : "sin")) : (SUG[id] ? SUG[id].p : "sin");')
H('const ord = id => (P[id] && P[id].o != null) ? P[id].o : 1e6;', 'const ord = id => (P[id] && P[id].o != null) ? P[id].o : (SUG[id] ? SUG[id].o : 1e6);')
H('function setPrio(id, p){ if (ro()) return; P[id] = Object.assign({}, P[id]||{}, {p}); if (p === "sin") delete P[id].p; touch(id); }',
  'function setPrio(id, p){ if (ro()) return; P[id] = Object.assign({}, P[id]||{}, {p}); delete P[id].sug; if (p === "sin") delete P[id].p; touch(id); }')
H('function reorder(listEl, p){ [...listEl.querySelectorAll("[data-id]")].forEach((n,i) => { const id = n.dataset.id; const before = JSON.stringify(P[id]||{}); P[id] = Object.assign({}, P[id]||{}, {o:i}); if (p) { if (p === "sin") delete P[id].p; else P[id].p = p; } if (JSON.stringify(P[id]) !== before) touch(id); }); }',
  'function reorder(listEl, p, moved){ [...listEl.querySelectorAll("[data-id]")].forEach((n,i) => { const id = n.dataset.id; const before = JSON.stringify(P[id]||{}); const wasSug = isSug(id); const cur = p || pr(id); P[id] = Object.assign({}, P[id]||{}, {o:i}); if (cur === "sin") delete P[id].p; else P[id].p = cur; if (id !== moved && wasSug) P[id].sug = true; else delete P[id].sug; if (JSON.stringify(P[id]) !== before) touch(id); }); }')
h = h.replace("reorder(e.to, e.to.dataset.p)", "reorder(e.to, e.to.dataset.p, e.item.dataset.id)")
h = h.replace("reorder(e.from, null)", "reorder(e.from, null, e.item.dataset.id)")
H('  if (P[t.id] && P[t.id].nota) m.append(el("span",{cls:"pill nota",text:"Con nota"}));',
  '  if (P[t.id] && P[t.id].nota) m.append(el("span",{cls:"pill nota",text:"Con nota"}));\n  if (isSug(t.id) && SUG[t.id]) m.append(el("span",{cls:"pill sug",text:"Sugerida por Claude"}));')
H('.pill.nota{', '.pill.sug{background:transparent;color:var(--t3);border:1px dashed var(--line2)}\n.pill.nota{')
H('el("div",{cls:"kpi"}, el("b",{text:dec+"/"+v.length}), el("span",{text:"priorizadas"})));',
  'el("div",{cls:"kpi"}, el("b",{text:v.filter(t=>!isSug(t.id)).length+"/"+v.length}), el("span",{text:"decididas por ti"})));')
H('". Arrastra para priorizar o toca una tarea para ver el detalle."',
  '". Ya vienen ordenadas con mi sugerencia: muévelas y quedan como tuyas. Toca una tarea para ver el detalle."')
H('''    el("div",{cls:"dk"}, el("small",{text:"Prioridad"}),''', '''    SUG[t.id] ? el("div",{cls:"lsec"}, el("small",{text: isSug(t.id) ? "Por qué la puse en " + COLN[SUG[t.id].p] : "Yo la había puesto en " + COLN[SUG[t.id].p]}), el("div",{text:SUG[t.id].por})) : null,
    (isSug(t.id) && SUG[t.id] && !ro()) ? el("button",{cls:"dx",style:"align-self:flex-start",onclick:()=>{ setPrio(t.id, SUG[t.id].p); render(); }},"Confirmar la sugerencia") : null,
    el("div",{cls:"dk"}, el("small",{text:"Prioridad"}),''')
H('    el("div",{cls:"dk"}, el("small",{text:"Prioridad"}), (() => { const g = el("div",{cls:"dprio"}); COLS.slice(1).forEach(c => g.append(el("button",{cls:c.v+(p===c.v?" on":""),',
  '    el("div",{cls:"dk"}, el("small",{text:"Prioridad"}), (() => { const g = el("div",{cls:"dprio"}); COLS.slice(1).forEach(c => g.append(el("button",{cls:c.v+(p===c.v && !isSug(t.id)?" on":""),')
dd = json.loads(re.search(r'<script id="data" type="application/json">(.*?)</script>', h, re.S).group(1).replace("<\/", "</"))
dd["sug"] = SUG
h = re.sub(r'(<script id="data" type="application/json">)(.*?)(</script>)', lambda m: m.group(1) + json.dumps(dd, ensure_ascii=False).replace("</", "<\/") + m.group(3), h, count=1, flags=re.S)
io.open(OUT, "w", encoding="utf-8").write(h)
print("sugerencias ok", len(SUG))
