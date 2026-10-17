import io, json
P = r"C:\Users\KASPRO~1\AppData\Local\Temp\claude\C--laragon-www-APP-EVENTOS\d48b02a8-536c-405b-be6d-313a2be22e0d\scratchpad\tareas.json"
d = json.load(io.open(P, encoding="utf-8"))

DESC = {
 "B6.4 Ensayo general": "Una semana antes del evento (28-29 de noviembre) se monta el nivel elegido con el pipeline, se le mete la carga real y se tumba un nodo con gente adentro. Si nadie lo nota, \"evento mañana\" es si. Despues se desmonta.",
 "¿Cuando abre el registro": "Esa fecha decide cuando tiene que existir produccion de verdad, con correo verificado y Turnstile real. Si el registro abre en noviembre, el servidor tiene que estar arriba antes.",
 "¿El cliente del 6 de diciembre": "Si esta confirmado se justifica pagar las pruebas de servidor y medir 5.000. Si no, se puede priorizar el demo vendible.",
 "3 viewports en device real": "Recorrer la webapp en un portatil, un iPad y un iPhone de verdad, no en el emulador de Chrome. Es la parte de W.12 Fase C que solo se puede hacer contigo.",
 "Validar tablet portrait warning": "En iPad vertical la webapp muestra un aviso; confirmar que sale bien y que no tapa nada.",
 "Validar Edge corporativo": "Los clientes enterprise suelen usar Edge con politicas de empresa. Abrir la webapp ahi y revisar que login, streaming y sockets funcionen.",
 "Validar Firefox": "Recorrido completo en Firefox 115 o superior: login, agenda, streaming, social.",
 "Empty states consistentes": "Que todos los modulos muestren el mismo tipo de estado vacio (sin sesiones, sin fotos, sin sponsors) y no uno distinto en cada pantalla.",
 "Loading transitions": "Pasar del esqueleto de carga al contenido real sin salto brusco en todos los modulos.",
 "WCAG AA contraste": "Revisar que textos y botones cumplan contraste 4.5:1 en Noir y en Lux. Lo pide cualquier cliente enterprise.",
 "Keyboard nav completa": "Poder usar la webapp solo con teclado: llegar a todo con Tab y activar con Enter.",
 "Tab order logico": "El orden en que salta el foco con Tab sigue el orden visual de la pantalla.",
 "ARIA labels iconos sin texto": "Los botones que son solo un icono necesitan nombre para lectores de pantalla.",
 "Bundle <200KB": "Medir el peso del JavaScript inicial y bajarlo de 200 KB comprimido; afecta a quien entra con datos moviles en el recinto.",
 "next/image sizes correcto": "Que las imagenes pidan el tamaño que de verdad se muestra, no la original completa.",
 "Lighthouse Performance >=85 desktop": "Correr Lighthouse autenticado en escritorio y llegar a 85 o mas.",
 "Lighthouse Performance >=75 mobile": "Correr Lighthouse autenticado en celular y llegar a 75 o mas.",
 "TTI <3s 4G Bogota": "Que la webapp sea usable en menos de 3 segundos con una red 4G como la de Bogota.",
 "Streaming + Q&A cross-tab": "Prueba automatica con dos pestañas: una pregunta o un mensaje en una aparece en la otra. Necesita el socket real corriendo en la prueba.",
 "Social conectar cross-tab": "Prueba automatica: una persona envia solicitud de conexion y la otra la ve llegar en vivo.",
 "DSN prod": "Poner la llave de Sentry de produccion y confirmar que los errores llegan. La configuracion ya esta en el codigo.",
 "W.9 Share rank social": "Boton para compartir tu posicion del ranking en redes. Quedo para despues al cerrar el modulo.",
 "W.X pelicula de bienvenida": "Ver la pelicula de bienvenida completa de principio a fin; hasta ahora se probo escena por escena.",
 "Landing en iPad con Kamilo": "Revisar contigo la landing en iPad vertical y horizontal (empezada el 12 de octubre).",
 "iOS + Android con Sentry Performance": "Medir en telefonos reales con red 4G de Bogota cuanto tardan las pantallas, usando Sentry Performance.",
 "E2E con Chromium en el servidor": "El recap genera la imagen con Chromium; probarlo en el servidor y ver el diseño en una build de desarrollo del telefono.",
 "Validar si banners.tsx": "Revisar si la pantalla de banners del Expo se usa sola o solo dentro del carrusel, antes de borrarla.",
 "Branded QR codes con logo": "QR del asistente con el logo del evento en el centro.",
 "Passkeys": "Entrar al admin con huella o Face ID ademas del codigo de la app autenticadora. Ampliacion pedida el 1 de agosto, sin fecha.",
 "Emoji skip-self": "Cuando mandas un emoji en el streaming, que no se te muestre dos veces (el tuyo y el que rebota del socket). Unos 30 minutos.",
 "Q&A upvote anti-parpadeo": "Al votar una pregunta el contador parpadea; dejarlo estable. Unos 15 minutos.",
 "Migrar Event Pulse": "Alinear los colores del Event Pulse con los tokens de Noir y Lux de las demas pantallas.",
}
SKIP = ("Memoria", "Counter PARITY-MATRIX")
out = []
for t in d["tareas"]:
    if any(t["titulo"].startswith(s) for s in SKIP):
        continue
    if not t["desc"]:
        for k, v in DESC.items():
            if t["titulo"].startswith(k):
                t["desc"] = v
                break
    out.append(t)
d["tareas"] = out
json.dump(d, io.open(P, "w", encoding="utf-8"), ensure_ascii=False)
print(len(out), "tareas; sin descripcion antes de dic:",
      [t["titulo"] for t in out if not t["desc"] and t["cuando"] == "antes"])
