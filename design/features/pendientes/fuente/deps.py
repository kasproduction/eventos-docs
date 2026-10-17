import io, json, re
P = r"C:\Users\KASPRO~1\AppData\Local\Temp\claude\C--laragon-www-APP-EVENTOS\d48b02a8-536c-405b-be6d-313a2be22e0d\scratchpad\tareas-lab.json"
d = json.load(io.open(P, encoding="utf-8"))
T = d["tareas"]

def find(prefix):
    for t in T:
        if t["titulo"].startswith(prefix):
            return t["id"]
    raise SystemExit("no encontrada: " + prefix)

P0, P2, P3 = find("P.0"), find("P.2"), find("P.3")
B21, B23 = find("B2.1"), find("B2.3")
B51, B52, B53, B55 = find("B5.1"), find("B5.2"), find("B5.3"), find("B5.5")

EXPL = {  # tarea -> tareas de las que depende (razon corta)
    "P.1": [P0], "P.2": [P0], "P.3": [P2], "P.4": [P3],
    "B2.3": [B21], "B2.4": [B23], "B2.5": [B23],
    "B4.4": [B51], "B5.3": [B52, B23], "B5.4": [B53], "B5.5": [B53],
    "B6.1": [B23, B53], "B6.4": [B55],
}
for t in T:
    deps = []
    for k, v in EXPL.items():
        if t["titulo"].startswith(k + " "):
            deps += v
    if not deps and t.get("etiqueta") in ("servidor", "local+medir") and t["mod"] != "decisiones":
        deps = [P2]
    if t.get("parcial") and P3 not in deps:
        deps.append(P3)
    t["dep"] = [x for x in dict.fromkeys(deps) if x != t["id"]]

# alerta por modulo: de que otro modulo depende para cerrarse
MODDEP = {
    "torre": ("servidor", "Para ver datos reales hace falta el servidor de prueba arriba."),
    "registro": ("servidor", "Abrir el registro al público exige el servidor de producción y el correo listo."),
    "app": ("servidor", "Publicar en tiendas exige que la app apunte al servidor de producción."),
    "qa": ("servidor", "Probar en teléfono con gente real necesita la webapp en el servidor de prueba."),
    "seguridad": ("servidor", "El último chequeo de seguridad se corre contra el servidor real."),
}
d["moddep"] = {k: {"mod": v[0], "texto": v[1]} for k, v in MODDEP.items()}
json.dump(d, io.open(P, "w", encoding="utf-8"), ensure_ascii=False)
print("con dependencias:", sum(1 for t in T if t["dep"]))
