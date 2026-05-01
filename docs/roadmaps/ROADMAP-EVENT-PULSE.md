# Roadmap — Event Pulse (Dashboard Live para Cliente)

> Dashboard visual standalone que el organizador mira en una pantalla/TV.
> Canvas adaptativo (width 1920, height dinamica segun viewport).
> NO es admin, NO es Filament, NO es Mission Control.
> El organizador solo mira, no controla. Es el acuario del evento.
> Prioridad: P1 (diferenciador — ningun competidor tiene esto)
> Diseno aprobado: `design/event-pulse-FINAL.html`
> Estado: **COMPLETO** — backend, socket, frontend, temas, responsive, auditoria 30 bugs
> Actualizado: 2026-04-24

---

## Resumen sesion 2026-04-24

### COMPLETADO

**Tema Noir (EP-3):**
- Tokens duales Lumina Noir (matches Mission Control): `#0A0A0A` bg, `#0E0E0E` surface, `#FFFFFF` ink
- Teal `#39D2C0`, green `#3BBF7A`, red `#DC4A4A`, blue `#5B8DEF` (Noir brighter)
- `[data-theme="noir"]` override completo: shadows, pills, moments, rings
- Toggle sol/luna bottom-right con localStorage + URL param `?theme=noir` + `default_theme` del evento

**Responsive (EP-1):**
- Canvas adaptativo: width 1920, height = `1920 / (vw/vh)`, scale = `vw/1920`
- Sin franjas en ninguna resolucion: iPad 1180x820, laptop 1366x768, 2K, ultrawide
- Portrait → overlay "Rota el dispositivo" con animacion
- Stage + body bg usa `var(--bg)` (sin contraste letterbox)

**Charlas reescrito (EP-12):**
- Panel izquierdo: agenda timeline agrupada por sala, sesiones live/futuras
- Panel derecho: drill-down con titulo, horario, track badge, speakers HD, descripcion, audiencia RT
- Endpoint `rooms` devuelve `agenda[]` (agrupado) + `rooms[]` (flat)
- Campos nuevos: `description`, `track`, `type`, `speakers[]`, `past`
- Auto-selecciona primera sesion live, sesiones past filtradas

**Error handling (EP-5):**
- Pip status en nav "Inicio": rojo (offline), amarillo (reconectando), verde (conectado)
- Auto-recovery: bootstrap refresh completo on reconnect, 3 retries con backoff 5s
- API fetch con AbortController timeout 8s
- Bootstrap fail muestra "Reintentar" link

**Moments RT (EP-6):**
- `updateMomentData(key, item)` / `replaceMomentData(key, items)` expuestos
- `checkin:update` → pushea al pool checkins
- `wall:post` → pushea al pool posts
- `data:invalidate` leaderboard/ratings → refetch y reemplaza pool
- Constantes extraidas: MOMENT_DURATION, MOMENT_INTERVAL, MOMENT_FIRST_DELAY, MOMENT_POOL_MAX

**Leads rediseno:**
- Header: 3 stat cards (total leads, sponsors activos, top sponsor con logo)
- Tabla ranking: filas con rank, logo 36px, nombre, count, avatares recientes + tiempo
- Barra proporcional sutil (absolute, ink-05) detras de cada fila
- Auto-scroll ping-pong cuando desborda, solo activo en seccion leads

**Usuarios activos en app:**
- Corner superior derecho: "En la app" — usuarios con app abierta (sockets activos)
- Socket server emite `pulse:active_users` cada 10s al pulse room
- Cuenta `userConnections` Map en memoria (connKey > 0 = attendees reales)
- Zero HTTP, zero DB — solo Map.size en memoria

**Limpieza UI:**
- Eliminado "Event Pulse - En vivo" (brand-sub)
- Eliminado "Transmitiendo" + dot verde
- Eliminado nombre duplicado en idle (bot-left)
- Eliminado dot doble en agenda charlas
- Teal restringido a gamificacion: live→green, leads→platinum, networking→platinum
- Acciones gamificacion muestran label humanizado (no slug)

**Weather (EP-9):**
- DESCARTADO — sin valor suficiente, evita dependencia API externa

**Test E2E (EP-7):**
- PulseSimulate probado en vivo: 6 tipos (checkin, lead, connection, post, rating, points)
- Flujo completo verificado: simulate → socket → frontend → counter + moment

**Auditoria 30 bugs (BUG-237 a BUG-267):**
- 6 criticos: null checks, race conditions, guards
- 8 altos: N+1 queries (leads, leaderboard), retry, timeout, validacion
- 8 medios: memory leak, status pip, teal color, inline styles, logging
- 8 bajos: typos, dead HTML/CSS, magic numbers, accessibility, mensajes

### BUGS PENDIENTES

Ninguno.

---

## Archivos clave (estado actual 2026-04-24)

### Backend (eventos-backend)
| Archivo | Proposito |
|---------|-----------|
| `app/Http/Controllers/PulseController.php` | 8 endpoints, 0 N+1, labels humanizados |
| `app/Http/Middleware/CheckPulseToken.php` | Valida pulse_token en query string |
| `app/Console/Commands/PulseSimulate.php` | Simula 6 tipos de evento RT |
| `app/Services/PointsService.php` | Config labels para leaderboard |
| `tests/Feature/Pulse/PulseTest.php` | 20 tests, 79 assertions |

### Socket server (eventos-socket)
| Archivo | Proposito |
|---------|-----------|
| `src/rooms.ts` | `Rooms.pulse(eventId)` — room aislado |
| `src/auth.ts` | `validatePulseToken()` — auth ep_* con length check |
| `src/types.ts` | `pulse:active_users` event type |
| `src/chat.ts` | `broadcastAudience` → Rooms.session (MC) + Rooms.pulse (EP) |
| `src/index.ts` | Middleware ep_*, pulse room, active users broadcast 10s, cleanup logging |

### Frontend (eventos-backend/public/event-pulse/)
| Archivo | Proposito |
|---------|-----------|
| `index.html` | Canvas adaptativo, 7 secciones, nav pills, rotate overlay, toggle, pip status |
| `css/tokens.css` | Tokens duales Lux + Noir Lumina |
| `css/toggle.css` | Theme toggle bottom-right, especificidad protegida |
| `css/layout.css` | Canvas adaptativo, responsive, reduced-motion, rotate overlay |
| `css/sections.css` | Charlas agenda+detail, leads tabla, teal solo gamificacion |
| `css/moments.css` | 5 tipos activos, 2 CSS ready (lead, match) |
| `css/nav.css` | Pills + pip status (rojo/amarillo/verde) |
| `js/data.js` | Utils, sessionViewers init, initials() safe |
| `js/app.js` | Bootstrap con validacion, fetch timeout 8s, theme toggle, retry link |
| `js/sections.js` | Agenda+detail, leads ranking+autoscroll, null guards, memory leak fix |
| `js/socket.js` | RT events, pip status, moment pool updates, bootstrap retry 3x |
| `js/moments.js` | Constantes, interval managed, pool update/replace expuestos |

---

## Principios de diseno (NO NEGOCIABLES)

1. **Tipografia**: PlusJakartaSans (display) + Urbanist (body). Locales. NUNCA Inter/Space Grotesk.
2. **Tema**: Noir (Lumina Noir) y Lux. Segun `default_theme` del evento. Toggle manual.
3. **Teal solo gamificacion**: leaderboard, points. Todo lo demas usa green (live), platinum (accent), ink.
4. **Avatares grandes**: minimo 48px listas, 100px+ protagonistas.
5. **Sin data falsa**: todo del API o socket. Estados vacios elegantes.
6. **Sin counters simulados**: numeros solo cambian con socket event real.
7. **Responsive**: canvas adaptativo, portrait overlay, sin franjas.
8. **Sin collage fotos flotantes**: eliminado por rendimiento.
9. **Sin auto-rotacion secciones**: navegacion manual con pills.
10. **Zero polling**: todo via socket. Bootstrap refresh 5min como fallback.
11. **Offline resiliente**: pip status, retry 3x, timeout 8s, recovery on reconnect.

---

## API Endpoints

Base: `GET /api/v1/pulse/{slug}/{endpoint}?token={pulse_token}`

| Endpoint | Respuesta |
|----------|-----------|
| `bootstrap` | event{id,name,slug,venue,dates,color,logo,default_theme}, sections[], stats |
| `rooms` | rooms[] (flat, todas las sesiones), agenda[] (agrupado por sala con sessions[]) |
| `checkins` | recent[], total, registered, timeline{hour:count} |
| `leads` | sponsors[] (0 N+1, eager load) |
| `connections` | total, accept_rate, recent[] |
| `social` | posts[] (solo status=published) |
| `leaderboard` | top[] (labels humanizados, 0 N+1), total_points |
| `ratings` | sessions[] (eager speakers), event_avg |

## Socket Events (Pulse escucha)

| Evento | Room | Que hace Pulse |
|--------|------|---------------|
| `checkin:update` | `Rooms.event` | Counter, moment, pool update |
| `data:invalidate` | `Rooms.event` | Invalida seccion, re-fetch stat, pool refresh |
| `wall:post` | `Rooms.event` | Counter, live insert masonry, moment, pool update |
| `session:audience` | `Rooms.pulse` | Viewers virtuales, invalida charlas |
| `session:config_updated` | `Rooms.event` | Invalida charlas |
| `pulse:active_users` | `Rooms.pulse` | Counter "En la app" cada 10s |
