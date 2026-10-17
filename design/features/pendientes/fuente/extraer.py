import io, re, json, hashlib

SRC = r"C:\laragon\www\APP EVENTOS\docs\living\PENDIENTES.md"
OUT = r"C:\Users\KASPRO~1\AppData\Local\Temp\claude\C--laragon-www-APP-EVENTOS\d48b02a8-536c-405b-be6d-313a2be22e0d\scratchpad\tareas.json"

L = io.open(SRC, encoding="utf-8-sig").read().replace("\r\n", "\n").split("\n")

def clean(t):
    t = t.replace("`", "")
    t = re.sub(r"\*\*(.+?)\*\*", r"\1", t)
    t = re.sub(r"\[\[(.+?)\]\]", r"\1", t)
    t = t.replace(" · ", ", ").replace("·", ",")
    t = re.sub(r"\s+", " ", t).strip()
    return t

tasks = []
sec = sub = None
secintro = {}
i = 0
skip = False
while i < len(L):
    l = L[i]
    m = re.match(r"^## (\d+)\. (.+)$", l)
    if m:
        n = int(m.group(1))
        skip = n in (0, 14)
        sec = {"n": n, "t": clean(m.group(2))}
        sub = None
        i += 1
        continue
    m = re.match(r"^#{3,4} (.+)$", l)
    if m:
        sub = clean(m.group(1))
        i += 1
        continue
    if l.startswith("> ") and sec and not skip and sub is None:
        secintro.setdefault(sec["n"], []).append(clean(l[2:]))
    m = re.match(r"^(\s*)- \[( |~)\] (.*)$", l)
    if m and sec and not skip:
        ind = len(m.group(1))
        parcial = m.group(2) == "~"
        text = [m.group(3)]
        j = i + 1
        while j < len(L) and L[j].strip() and not re.match(r"^\s*- \[", L[j]) and not L[j].startswith("#") \
                and (len(L[j]) - len(L[j].lstrip())) > ind:
            text.append(L[j].strip())
            j += 1
        raw = " ".join(text)
        etiqueta = None
        for tag in ("[local+medir]", "[local]", "[servidor]"):
            if tag in raw:
                etiqueta = tag.strip("[]")
                raw = raw.replace("**" + tag + "**", "").replace(tag, "")
        raw = raw.strip()
        fuentes = re.findall(r"`([^`]+\.(?:md|php|tsx|ts|js|sh|json)(?::[\d\-,: ]+)?)`", raw)
        mb = re.match(r"^\*\*(.+?)\*\*[:.]?\s*(.*)$", raw)
        if mb:
            titulo, desc = clean(mb.group(1)).rstrip(":."), clean(mb.group(2))
        else:
            c = clean(raw)
            k, dep = -1, 0
            for ii, ch in enumerate(c):
                if ch == "(": dep += 1
                elif ch == ")": dep = max(0, dep - 1)
                elif ch == ":" and dep == 0 and c[ii + 1:ii + 2] == " ":
                    k = ii; break
            if 0 < k <= 90:
                titulo, desc = c[:k], c[k + 1:].strip()
            else:
                cut = re.search(r"\. |\(|;", c[30:])
                k2 = 30 + cut.start() if cut and 30 + cut.start() <= 95 else None
                if k2:
                    titulo, desc = c[:k2].strip(), c[k2:].lstrip(". ").strip()
                elif len(c) <= 95:
                    titulo, desc = c, ""
                else:
                    w = c[:90].rsplit(" ", 1)[0]
                    titulo, desc = w + "…", c
        if titulo.count("(") > titulo.count(")"):
            cut2 = titulo.rfind("(")
            desc = (titulo[cut2:] + " " + desc).strip() if desc else clean(raw)[cut2:].strip()
            titulo = titulo[:cut2].strip()
        when = "despues" if sec["n"] in (12, 13) else "antes"
        hid = hashlib.sha1((sec["t"] + "|" + clean(raw)).encode()).hexdigest()[:12]
        tasks.append({"id": hid, "sec": sec["n"], "secT": sec["t"], "sub": sub, "titulo": titulo, "desc": desc,
                      "fuentes": fuentes, "etiqueta": etiqueta, "parcial": parcial, "cuando": when})
        i = j
        continue
    i += 1

ids = [t["id"] for t in tasks]
assert len(ids) == len(set(ids)), "ids duplicados"
secs = {}
for t in tasks:
    secs.setdefault(t["sec"], {"n": t["sec"], "t": t["secT"], "intro": " ".join(secintro.get(t["sec"], []))[:600]})
json.dump({"tareas": tasks, "secciones": list(secs.values())}, io.open(OUT, "w", encoding="utf-8"), ensure_ascii=False)
print(len(tasks), "tareas")
for s in secs.values():
    print(s["n"], s["t"], sum(1 for t in tasks if t["sec"] == s["n"]))
sin = [t for t in tasks if not t["desc"]]
print("sin descripcion:", len(sin))
for t in sin[:60]:
    print("  ", t["sec"], "|", t["titulo"])
