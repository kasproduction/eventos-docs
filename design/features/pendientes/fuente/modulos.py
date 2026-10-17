import io,json,re
d=json.load(io.open('tareas.json',encoding='utf-8'))
MODS=[("servidor","Servidor y capacidad"),("torre","Torre de control"),("registro","Registro, landing y correo"),
("agenda","Agenda"),("streaming","Streaming y chat"),("social","Social y networking"),("juegos","Juegos y gamificación"),
("admin","Admin y organizador"),("app","App Expo"),("seguridad","Seguridad"),("calidad","Calidad de la webapp"),
("bugs","Bugs"),("decisiones","Decisiones tuyas"),("manual","Manual y documentación"),("fase3","Producto a futuro")]
KW=[("torre",r"torre|alerta|logs? central|sentry|pulse como panel|canario|escalar con|monitoreo|pagina de estado"),
("juegos",r"juego|trivia|gamif|ranking|rank|puntos|ruleta|racha|unity|subasta|speed-dating"),
("agenda",r"agenda|sesion|favorito|calendar|calificar"),
("streaming",r"streaming|chat|q&a|emoji|player|w\.19|spatial|stream|video|replay"),
("social",r"social|wall|networking|conexi|muro|contacto|sugerid|attendee|foto"),
("registro",r"registro|landing|turnstile|correo|resend|widget|escarapela|credencial|tarjeta al compartir|waitlist|referral|social login|mail"),
("seguridad",r"seguridad|rate limit|2fa|passkey|composer audit|cabeceras|security:check|anomaly"),
("admin",r"admin|filament|mission control|event pulse|data center|highlights|destacados|onboarding|paginas custom|qr|kiosk|spa|chat monitor|display|attendance"),
("app",r"expo|app movil|eas|tiendas|keystore|ios|android|banners|documentos\.tsx|skia|crop"),
("manual",r"manual|document|arquitectura socket"),
]
def mod(t):
    s=t["sec"]; txt=(t["titulo"]+" "+t["desc"]+" "+(t["sub"] or "")).lower()
    if s==2: return "decisiones"
    if s==3: return "bugs"
    if s==4: return "registro"
    if s==6: return "calidad" if False else "qa"
    if s==7: return "app"
    if s==9: return "manual"
    if s==10: return "seguridad"
    if s==13: return "servidor"
    if s==1:
        if re.match(r"b4\.",t["titulo"].lower()) or re.search(r"torre",txt): return "torre"
        return "servidor"
    if s==5:
        if (t["sub"] or "").startswith("W.12") or "BLOQUE 5" in (t["sub"] or ""): return "calidad"
        if "BLOQUE 8" in (t["sub"] or ""): return "streaming"
    if s==12 and (t["sub"] or "").startswith("Fase 3"): return "fase3"
    for k,rx in KW:
        if re.search(rx,txt): return k
    return {5:"calidad",8:"admin",11:"calidad",12:"fase3"}.get(s,"servidor")
MODS.insert(11,("qa","QA presencial"))
for t in d["tareas"]: t["mod"]=mod(t)
d["modulos"]=[{"k":k,"t":v,"n":sum(1 for t in d["tareas"] if t["mod"]==k)} for k,v in MODS]
json.dump(d,io.open('tareas.json','w',encoding='utf-8'),ensure_ascii=False)
for m in d["modulos"]: print(m["n"],m["t"])
for t in d["tareas"]:
    if t["mod"] in("fase3",) : print("  F3",t["titulo"][:70])
