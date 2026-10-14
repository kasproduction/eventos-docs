# Referencia Bintec 2026 — que hizo Bancolombia y que nos sirve

> Revision pasiva (sin login, sin pruebas de ataque) del sitio publico
> `bintec.eventosbancolombia.com` el 2026-10-14, con el evento en curso (13, 14 y 15 de octubre,
> Bogota y Medellin). Fuente: HTML, bundles `/_astro/*.js`, cabeceras HTTP y `/api/agenda` publica.
> Contexto: Bintec era el evento para el que EventOS se penso al inicio (via Eventos Efectivos,
> 14.000 personas, multi-sede, Silent Disco). Ver `ANALISIS-COMPETITIVO.md`.

## 1. Que montaron

| Pieza | Tecnologia | Quien carga |
|---|---|---|
| Sitio | Astro + React + Tailwind, contenido en Sanity | Vercel (iad1, EE.UU.) |
| Video | iframe de Vimeo Events (`vimeo.com/event/<id>/embed/interaction`) pegado como HTML en Sanity, uno por idioma | Vimeo |
| Chat | Propio sobre Ably Chat SDK (ably-js 2.28), una sala por auditorio | Ably |
| Datos | Endpoints `/api/*` (funciones de Vercel) | Vercel |

Lectura de Kamilo: a EventOS le pedian una plataforma completa y al final fue "un player y un chat".
Probablemente armado rapido (posible IA): piezas de terceros, copy reciclado ("taller" en una
conferencia), "Descripcion no disponible", links vacios. No se puede afirmar.

## 2. Agenda

- Grilla tipo calendario: eje horario 08:00-17:00, columnas = auditorios, agrupada por ciudad.
  Tabs por dia. Filtros ciudad, hora, auditorio. Talleres online en columna sin auditorio. Recesos.
- **Una sola peticion publica por dia:** `GET /api/agenda?date=2026-10-14` (~33 KB, ~58 sesiones).
- Respuesta ya armada para pintar:
  `{ success, grid: { startMinutes, endMinutes, cities: [{ id, title, venues: [{ id, title, breaks, events: [...] }] }] } }`
- Cada evento viene plano: `id, title, slug, startDate, endDate, startMinutes, endMinutes,
  timeRange ("08:00 - 08:30"), modality, speaker {fullName, oneLiner}, isPanel, isForChildren,
  sponsor, category {id, title, color}, isLive`. El cliente no calcula nada.
- Falla: `Cache-Control: public, max-age=0` → cada visita pega a la funcion, sin cache de CDN.

## 3. Ficha de sesion

- Datos: titulo, speaker con foto y empresa, hora, dia, ciudad, auditorio, duracion, idioma, modalidad.
- Acciones: "Me interesa", favorito, "Asistire presencialmente" (`POST /api/user/participation`),
  Google Calendar + Outlook (links prellenados).
- Si esta en vivo: video de Vimeo + chat del auditorio al lado. `/api/live-event?event&auditorium`
  se consulta cada ~20 s para saber que sesion corre y avisar "la siguiente".
- Links aparte para trivia (`triviaUrl`) y Bintec play (videos grabados).

## 4. Chat

- Solo con sesion. `GET /api/chat/token?event=` (cookie) → `{ token Ably, clientId, displayName, roomName }`.
  El nombre lo pone el servidor, el navegador no lo puede falsear. Sin sesion → 401.
- Cada mensaje pasa antes por `POST /api/chat/moderation` (puede responder `ROOM_BANNED`).
- `/api/chat/history` para historial. Typing, reacciones y avatares con iniciales vienen del kit de Ably.

## 5. Seguridad (pasiva)

- Bien: token del chat del lado del servidor, moderacion previa, reCAPTCHA en login, HSTS 2 años.
- Mal: **el numero de documento ES la contraseña** ("Sera tu contraseña; podras cambiarla desde
  tu perfil"). Sin 2FA. Sin CSP, X-Frame-Options, nosniff ni Referrer-Policy. Muchos datos
  personales (documento, telefono, edad, genero, cargo) detras de esa contraseña. Datos en EE.UU.

## 6. Que tomamos para EventOS (el punto: estamos pesados en peticiones)

Medido en agosto: ~9 peticiones por persona por pantalla, ~300 personas por maquina. Ellos casi no
cargan sus servidores porque repartieron: video en Vimeo, tiempo real en Ably, paginas en la CDN,
y cada pantalla pide UNA cosa ya armada.

1. **Una peticion por pantalla, ya armada para pintar.** Empezar por la agenda: un endpoint por dia
   que devuelva todo agrupado (sede → sala → sesiones) con horas y textos ya calculados.
   Luego medir el resto de pantallas con el mismo criterio.
2. **Cachear en CDN lo que casi no cambia** (agenda, speakers, sponsors, landing) 30-60 s. Lo vivo
   (en vivo, contadores) va por socket, no rompe la cache. Mejor que ellos (max-age=0).
3. **Video siempre afuera** (Vimeo/YouTube/Mux), nunca en nuestra maquina.
4. **Respaldo de chat:** evaluar Ably (o similar) solo para el chat de eventos grandes si la
   medicion de 5.000 muestra que nuestro socket no aguanta. Decision por evento, no reescritura.
5. **Paquete minimo por evento:** encender solo los modulos que el evento usa; lo apagado no cuesta.
6. **Copiar lo bueno de la ficha:** triple estado (me interesa / favorito / asistire presencialmente)
   y Outlook ademas de Google Calendar. Vista grilla por auditorio para eventos multi-sala.
7. **No copiar:** documento como contraseña, falta de cabeceras de seguridad. Revisar que las
   nuestras (eventos-web y API) tengan CSP, X-Frame-Options, nosniff, Referrer-Policy.

Estado: **por decidir** (Kamilo, 2026-10-14). Nada implementado. Encaja en la ruta al evento del
6 de diciembre (`ROADMAP-INFRAESTRUCTURA.md`), antes de la medicion de 5.000.
