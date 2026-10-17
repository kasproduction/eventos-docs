import io, json, re
SP = r"C:\Users\KASPRO~1\AppData\Local\Temp\claude\C--laragon-www-APP-EVENTOS\d48b02a8-536c-405b-be6d-313a2be22e0d\scratchpad\\"
d = json.load(io.open(SP + "tareas-lab.json", encoding="utf-8"))
T = d["tareas"]

POR = {
    "ya_srv": "Es el arranque de todo lo de servidor: sin esto nada se puede medir y faltan 51 días.",
    "ya_local": "Se hace en local sin esperar al servidor y es de lo que más pesa para 5.000 personas.",
    "ya_bug": "Es un error que puede tumbar o confundir el evento y se arregla rápido.",
    "ya_dec": "Es una decisión tuya de pocos minutos que destraba trabajo.",
    "antes_srv": "Hace parte del plan para aguantar 5.000 personas; va después del arranque del servidor.",
    "antes_reg": "Hay que tenerlo listo antes de abrir el registro al público.",
    "antes_qa": "Se prueba contigo en teléfonos y navegadores reales antes del evento.",
    "antes_seg": "Seguridad que conviene cerrar antes de exponer la plataforma al público.",
    "antes_gen": "Suma al evento, pero no destraba nada: va después de lo urgente.",
    "despues_f2": "No hace falta para el 6 de diciembre; sirve si un cliente lo pide.",
    "despues_mejora": "Es una mejora: lo que hay hoy funciona. Mejor después del evento.",
    "despues_infra": "Infraestructura para eventos más grandes o para después de diciembre.",
    "despues_pausa": "Está en pausa por decisión tuya hasta después de producción.",
    "descartar_dup": "Está repetida con otra tarea del tablero.",
    "descartar_bajo": "Aporta poco: lo mismo ya lo cubre otra prueba.",
}

YA_PREF = ("P.0", "P.1", "P.2", "P.3", "B2.1", "B2.2", "B4.3", "B4.5", "B5.1", "B5.2", "B.1 ", "B3.7", "BUG-346",
           "Horas en vistas", "Sin rate limit")
YA_DEC = ("¿Cuándo abre", "¿El cliente", "Costo de pruebas", "¿Carga y caída", "Umbrales")
DESCARTAR = {"edbed4ddff81": "descartar_dup", "a8005e15ca61": "descartar_dup"}
QUALITY_LATER = ("Keyboard nav", "Tab order", "ARIA labels", "Empty states", "Loading transitions", "Bundle",
                 "next/image", "Streaming + Q&A cross-tab", "Social conectar cross-tab", "Validar tablet portrait")

def sug(t):
    ti, m, sec = t["titulo"], t["mod"], t["sec"]
    if t["id"] in DESCARTAR: return "descartar", DESCARTAR[t["id"]]
    if ti.startswith("Opcional: 3 vitest"): return "descartar", "descartar_bajo"
    if t["cuando"] == "despues": return "despues", ("despues_infra" if sec == 13 else "despues_f2")
    if any(ti.startswith(x) for x in YA_PREF):
        return "ya", ("ya_bug" if m == "bugs" else "ya_srv" if ti.startswith(("P.", "B2.")) else "ya_local")
    if m == "decisiones":
        if any(ti.startswith(x) for x in YA_DEC): return "ya", "ya_dec"
        return "despues", "despues_mejora"
    if m == "manual": return "despues", "despues_pausa"
    if m == "fase3": return "despues", "despues_f2"
    if (t["sub"] or "").startswith("BLOQUE 8"): return "despues", "despues_mejora"
    if m == "streaming" and "W.19" in ti: return "despues", "despues_mejora"
    if m == "calidad" and any(ti.startswith(x) for x in QUALITY_LATER): return "despues", "despues_mejora"
    if m == "calidad" and sec == 11: return "despues", "despues_mejora"
    if m == "app" and (t["sub"] or "").startswith(("Backlog Expo", "Lux V2")): return "despues", "despues_mejora"
    if m == "app" and ti.startswith("Staff app"): return "despues", "despues_mejora"
    if m == "admin" and (t["sub"] or "").startswith("Paridad"): return "despues", "despues_mejora"
    if m == "admin" and ti.startswith(("Mission Control", "Branded", "Configuracion canales", "Configuración canales")): return "despues", "despues_mejora"
    if m == "registro" and ti.startswith(("Credencial", "Tarjeta al compartir")): return "despues", "despues_mejora"
    if m == "seguridad" and ti.startswith("Passkeys"): return "despues", "despues_mejora"
    if m in ("servidor", "torre"): return "antes", "antes_srv"
    if m == "registro": return "antes", "antes_reg"
    if m == "qa": return "antes", "antes_qa"
    if m == "seguridad": return "antes", "antes_seg"
    if m == "bugs": return "antes", "ya_bug"
    return "antes", "antes_gen"

out = {}
for i, t in enumerate(T):
    p, k = sug(t)
    out[t["id"]] = {"p": p, "o": i, "por": POR[k]}
cnt = {}
for v in out.values(): cnt[v["p"]] = cnt.get(v["p"], 0) + 1
json.dump(out, io.open(SP + "sugerencias.json", "w", encoding="utf-8"), ensure_ascii=False)
print(cnt)
ya = [t["titulo"] for t in T if out[t["id"]]["p"] == "ya"]
print(len(ya)); [print("  ", x[:80]) for x in ya]

# orden dentro de "Ya": decisiones, arranque del servidor, bugs, trabajo local
ORDEN_YA = ["¿Cuándo abre", "¿El cliente", "Costo de pruebas", "¿Carga y caída", "Umbrales", "P.0", "P.1", "P.2", "P.3",
            "BUG-346", "Horas en vistas", "Sin rate limit", "B.1 ", "B3.7", "B4.5", "B4.3", "B5.1", "B5.2", "B2.1", "B2.2"]
for t in T:
    if out[t["id"]]["p"] == "ya":
        for j, pre in enumerate(ORDEN_YA):
            if t["titulo"].startswith(pre):
                out[t["id"]]["o"] = j
json.dump(out, io.open(SP + "sugerencias.json", "w", encoding="utf-8"), ensure_ascii=False)
print("orden ya ok")
