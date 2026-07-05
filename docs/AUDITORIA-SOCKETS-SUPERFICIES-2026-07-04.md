# Auditoria integral — TODAS las superficies, TODOS los modulos, TODOS los sockets

> Fable 5, 2026-07-04 (tercera pasada). Kamilo detecto que la auditoria espejo
> (`AUDITORIA-ESPEJO-2026-07-04.md`) solo cubrio Expo + webapp + socket server +
> emisores backend — y NO los consumidores organizador (Event Pulse, Mission
> Control, Display LED, Kiosko...). **Tenia razon.** Al auditar las superficies
> faltantes aparecieron 2 bugs reales de contrato, 1 gap de produccion en Event
> Pulse y 1 riesgo Expo — ninguno visible desde la pasada anterior.
>
> Complementa (no reemplaza) la auditoria espejo. Todo verificado con archivo:linea.

---

## 1. Mapa de superficies — el producto completo

EventOS son **10 superficies** que comparten backend + socket server:

| # | Superficie | Donde vive | Usuario | Que hace | Sockets |
|---|---|---|---|---|---|
| 1 | **Expo app** | `eventos-app` (SDK 55) | Asistente | La experiencia completa del evento en el celular. Fuente de verdad del comportamiento | SI — 6 puntos de conexion (ver hallazgo D) |
| 2 | **Webapp** | `eventos-web` (Next 16) | Asistente | Espejo Expo para laptop/desktop. 12 modulos cerrados, W.11 sockets en plan | SI — singleton (streaming hoy; global tras W.11) |
| 3 | **Admin Filament** | `eventos-backend /admin` | Organizador (back-office) | Configurar TODO el evento: 50 resources (ver seccion 3) | NO (Livewire server-side; emite via observers) |
| 4 | **Event Pulse** | `eventos-backend/public/event-pulse/` | Cliente/organizador (pantalla bonita) | "Ojo de dios" live: 7 secciones, counters animados, moments cinematograficos. Auth `pulse_token` (`ep_`), 8 endpoints REST read-only | SI — 6 listeners (ver GAP-C) |
| 5 | **Mission Control** | `public/mission-control/` via `/monitor/{sessionId}?token=HMAC` | Operador de sesion live | Control total de UNA sesion: chat mod (ban/delete/pin/slow), Q&A mod, polls crear/lanzar/proyectar, games, metricas RT, config live | SI — chat:*, question:*, poll:new, session:audience/metrics + emite display:project/stop |
| 6 | **Display LED** | `public/display/session.html` via `/display/session/{id}?token=HMAC` | Pantalla gigante de la sala | Proyeccion: polls con barras, Q&A destacada, trivia/spin/jackpot ceremony. Estado persiste Redis TTL 4h | SI — display:project/stop, poll:updated/closed, game:result/update/answer-count |
| 7 | **Chat Monitor** | `public/chat-monitor.html` | Moderador liviano | Version compacta de moderacion: chat + Q&A + polls sin el resto del MC | SI — chat:*, question:*, poll:* |
| 8 | **Attendance Check** | `public/attendance-check.html` | Staff silent disco | Lanza checks de asistencia por room y ve respuestas en vivo | SI — attendance:check:update (**ROTO — BUG-B**) |
| 9 | **Kiosko** | `eventos-kiosko` (Vite+React) | Tablet en la entrada | Check-in con QR fisico (USB scanner), aforo en vivo | SI — checkin:update (**ROTO — BUG-A**) |
| 10 | **Data Center** | `public/data-center/` via `/data-center` | Organizador post-evento | 44 exports historicos, 9 tabs, queue propio | NO (REST + jobs, cero sockets — verificado) |

---

## 2. Modulos por superficie — que hace cada uno

### 2.1 Expo (asistente) — pantallas agrupadas

**Nucleo:** Home (hero + anuncios + agenda viva + GamificationHud + modulos dinamicos), Agenda (+Mi Agenda favoritos, detalle sesion, rating), Speakers (directorio + featured + detalle + rating), Sponsors (brand wall tiers + detalle + trivia + contacto + favorito), Session-stream (player multiformat + chat + Q&A + polls + trivia panel), Session-chat standalone, Encuestas.

**Social/engagement:** Social (wall + memorias + stories + photo contest), Networking (directorio + sugeridos + solicitudes + bloqueados), Attendee profile, Leaderboard/Desafio (ranking + logros + passport + rewards + Golden Ticket), Passport (stamps QR fisico).

**Info/comunicacion:** Anuncios (+deep links), Banners, Documentos, FAQ, My-support, Pages CMS, About, Recap post-evento.

**Perfil:** Profile (datos + intereses + tema + logout), Mi QR (solo mobile), Banned screen.

**Vendor (rol sponsor):** Mi Stand, Leads (+detalle+edicion), Mi Equipo (invitar 3 vias + transfer), Stand stats (KPIs+export), Stand contacts, Join-team por token.

**Staff (rol staff/admin):** Scanner invite/stand, Staff checkin, Assign-staff QR, Room check-in (silent disco).

### 2.2 Webapp (asistente espejo) — modulos W.X

Cerrados 100%: W.0 Spatial UI, W.1 Setup+Auth, W.1B backend magic link, W.5 Speakers, W.7 Sponsors, W.8 Networking, W.9 Desafio, W.10 Live Hub, W.13 Asistente+Documentos, W.14 Anuncios+Cartel+Bell, W.17 Soporte, W.18 Perfil. Parciales: W.2 Home (60%), W.3 Agenda (83%), W.4 Streaming (75%), W.6 Social (45%). Proximo: **W.11 Sockets** (plan listo). Skip documentado: W.16 Live Moments (mobile-first). Opcional: W.15 Vendor. Ventana operativa: `docs/living/PENDIENTES-WEBAPP.md`.

### 2.3 Admin Filament — 50 resources por dominio

**Evento base:** EventResource, EventBrandingResource, EventRoomResource, EventSessionResource, SessionTrackResource, SessionTypeResource, ModuleResource (que modulos ve el asistente), SpeakerResource, SponsorResource.

**Asistentes/acceso:** AttendeeAdminResource, UserResource, AccessCodeResource, RegistrationFieldResource, RegistrationSettingsResource, EventOnboardingResource, OnboardingSurveyOptionResource, LoginSlideResource.

**Contenido/comunicacion:** AnnouncementResource, BannerResource, HighlightResource, DocumentResource, EventFaqResource, CustomPageResource, EventRecapResource, ScheduledNotificationResource, ReminderSettingsResource, EmailTemplateResource, EmailLogResource, OrganizationEmailSettingsResource.

**Engagement/gamification:** GamificationSettingsResource, RewardResource, GoldenTicketResource, PassportSettingsResource, LivePollResource, PostEventSurveyResource, SessionRatingResource, EventPhotoResource (moderacion galeria), EventPhotoSettingsResource, WallPostResource (moderacion wall — su approve EMITE wall:post), WallSettingsResource.

**Moderacion/soporte:** AttendeeBanResource, ChatSettingsResource (blocked words + slow mode), SupportRequestResource (responder crea announcement privado → data:invalidate).

**Vendor/leads:** LeadResource.

**Infra:** EventPulseResource (config del Pulse), RoomTotemResource, RateLimitSettingsResource, WebhookApiKeyResource, WebhookEndpointResource, WebhookLogResource.

Filament NO usa sockets directamente: los observers/actions disparan HTTP a `/internal/*` del socket server.

---

## 3. Matriz socket COMPLETA — evento × emisor × consumidores × estado

| Evento | Emisor real | Expo | Webapp (post W.11) | Pulse | MC | Display | ChatMon | AttCheck | Kiosko | Estado |
|---|---|---|---|---|---|---|---|---|---|---|
| `data:invalidate` (10 entities reales) | 9 observers + 2 jobs + PointsService + LeadController | ✓ | ✓ plan | ✓ (6 entities, 4 SIN emisor real — GAP-C) | — | — | — | — | — | OK con GAP-C |
| `wall:post` / `wall:comment` | WallController + WallPostResource approve | ✓ useWall | ✓ plan (bus) | ✓ post (moments) | — | — | — | — | — | OK |
| `networking:notify` | NetworkingController :308/:354 | ✓ | ✓ plan | — | — | — | — | — | — | OK |
| `ban:enforced` | BanController :82 | ✓ | ✓ plan | — | — | — | — | — | — | OK |
| `agenda:delayed` | SessionConfigController :393 | ✓ toast | ✓ plan (6to) | — | — | — | — | — | — | OK |
| `checkin:update` | CheckinService `/internal/checkin` | ✓ (self→modules) | Fase 2 (falta attendeeId en AuthUser) | ✓ counter+moment | — | — | — | — | **✗ BUG-A** | ROTO en kiosko |
| `attendance:check` | RoomCheckinController :704 | ✓ (verifica via API) | skip W.16 | — | — | — | — | — | — | OK |
| `attendance:check:update` | RoomCheckinController :753 | — | — | — | — | — | — | **✗ BUG-B** | — | ROTO (unico consumidor no joinea) |
| `game:launched/question/round-result/finished/result` | GameController via GameService | ✓ 5 (trivia store + toasts) | skip W.16 | — | — | ✓ result | — | — | — | OK |
| `game:update` / `game:answer-count` | GameController :577/:460 | — | — | — | — | ✓ | — | — | — | OK (consumidor = Display, corregido vs auditoria 1) |
| `chat:*` (message/history/emoji/deleted/pinned/unpinned) | socket server chat.ts | ✓ useChat | ✓ useChat W.4 | — | ✓ | — | ✓ | — | — | OK |
| `question:*` (submitted/approved/answered/upvoted) | QnAController via `/internal/question/broadcast` | ✓ useQnA | ✓ useQnA W.4 | — | ✓ | — | ✓ | — | — | OK |
| `poll:new/updated/closed` | PollController via `/internal/poll/broadcast` | ✓ | ✓ useChat W.4 | — | ✓ | ✓ | ✓ | — | — | OK |
| `session:mode_changed` | Filament via `/internal/session/mode_changed` | ✓ useSessionMode | ✓ W.4 | — | — | — | — | — | — | OK |
| `session:config_updated` | Mission Control via `/internal/session-config` (room `event:{id}`) | ✓ | ✓ useSessionLiveConfig | ✓ | ✓ | — | — | — | — | OK |
| `session:audience` | chat.ts broadcastAudience (rooms session + pulse) | ✓ | ✓ W.4 | ✓ (viewers bubbles) | ✓ | — | — | — | — | OK |
| `session:metrics` | chat.ts al join | — | — | — | ✓ init msgCount | — | — | — | — | OK |
| `display:project` / `display:stop` | MC emite (admin) → room display | trivia via display? (W.16 Expo TriviaPanel usa game:*) | skip | — | emite | ✓ | — | — | — | OK |
| `pulse:active_users` | socket server cada 10s → room pulse | — | — | ✓ | — | — | — | — | — | OK |
| `session:started/ended/cancelled`, `agenda:updated` | SessionConfigController :172/:228/:266/:300 | — | — | — | — | — | — | — | — | **Dead emits** (el lifecycle real viaja por data:invalidate agenda, emitido en paralelo) |
| `room:occupancy` | RoomCheckinService :373 → room `event:{id}:admin` | — | — | — | — | — | — | — | — | **Dead emit total** (ver seccion 5) |
| `staff:*` (7 eventos) | StaffNotificationService + controllers | ✓ (6 listeners) | W.15 opcional | — | — | — | — | — | — | OK |
| `announcement:new` | NADIE (dead type removido de types.ts hoy) | — | — | — | — | — | — | — | — | Eliminado |

---

## 4. Bugs y gaps NUEVOS de esta pasada

### BUG-A — Kiosko: aforo en vivo roto por DOBLE contrato incorrecto — BUG REAL

`eventos-kiosko/src/hooks/useAttendance.ts`:

1. **Linea 27:** `socket.emit('join:event', { event_id: eventId })` — el server destructura
   `{ eventId }` (`index.ts:583`) → recibe `undefined` → `EVENT_ACCESS_DENIED` → el kiosko
   **nunca entra al room** `event:{id}`.
2. **Lineas 29-30:** aunque entrara, lee `payload.checked_in` pero el server emite
   `checkedIn` camelCase (`index.ts:55-57`) → counter quedaria `undefined`.

**Efecto:** el aforo del kiosko queda congelado en `0 / 0` — solo se veria bien si algo
mas lo hidrata por REST. Fix: 2 lineas (`{ eventId }` + `payload.checkedIn`), y emitir
el join dentro de `socket.on('connect')` como hacen las demas superficies.

### BUG-B — Attendance Check admin: counters en vivo muertos — BUG REAL

`eventos-backend/public/attendance-check.html:275`:
`socket.emit('join:event', { event_id: EVENT_ID })` — mismo mismatch `event_id` vs
`eventId`. El server responde `EVENT_ACCESS_DENIED` y esta pantalla **nunca recibe
`attendance:check:update`** (que se emite al room del evento). Los counters de
respuestas del silent disco no suben en vivo. Fix: 1 palabra (`eventId`).

Nota: ambos bugs dejan rastro en el log del socket server como
`[security] user=X tried to join event=undefined...` — verificable en vivo.

### GAP-C — Event Pulse: 4 metricas NO son realtime en un evento real — GAP DE PRODUCCION

`public/event-pulse/js/socket.js:95-118` escucha `data:invalidate` con entityMap:
`connections`, `leaderboard`, `social`, `agenda`, `leads`, `ratings`.

Cruce contra emisores reales (grep estricto en `eventos-backend/app`):

| Entity que Pulse espera | Emisor en produccion | Emisor en demo |
|---|---|---|
| `agenda` | ✓ observers reales | ✓ |
| `social` | ninguno (cubierto por `wall:post` que Pulse escucha aparte) | — |
| `leads` | **NINGUNO** (LeadController solo emite `passport` directed al attendee) | Solo `PulseSimulate:122` |
| `connections` | **NINGUNO** (NetworkingController solo emite `networking:notify` directed) | Solo `PulseSimulate:159` |
| `ratings` | **NINGUNO** | Solo `PulseSimulate:247` |
| `leaderboard` | **NINGUNO** (PointsService emite `gamification` — entity que Pulse NO mapea) | Solo `PulseSimulate:276` |

**Efecto real:** en el demo con `pulse:simulate` el dashboard se ve perfecto (por eso
paso los 20 tests y 2 sesiones de QA). En un **evento real**, los counters de
leads / conexiones / ratings / puntos solo se actualizan por el bootstrap fallback
**cada 5 minutos** (`socket.js:267`) o al reconectar. Check-ins, wall, audiencia y
active users SI son instantaneos.

**Fix barato (backend, 4 lineas + throttle ya incluido en InvalidationService):**
- `NetworkingController` accept → `InvalidationService::broadcast($eventId, 'connections')`
- `LeadController@store` (lead scan) → `broadcast($eventId, 'leads')`
- Controller de ratings (sessions/speakers rate) → `broadcast($eventId, 'ratings')`
- `PointsService::award` → sumar `broadcast($eventId, 'leaderboard')` junto al
  directed `gamification` existente

Con el throttle 1s/entity del InvalidationService, ni 10K users generan estampida.

### RIESGO-D — Expo: 6 puntos de conexion socket vs limite server de 5 — RIESGO REAL

Expo abre un socket independiente (`io()` propio) en **6 lugares**:
`useDataInvalidation` (global), `useChat`, `useQnA`, `useSessionMode` (los 3 en
session-stream), `useWall` (social), `encuestas.tsx`. El server corta en
`MAX_CONNECTIONS_PER_USER = 5` (`index.ts:542`).

Escenario real: user con app abierta (1) entra a session-stream (+3 = 4) con
encuestas activas (+1 = 5) y el tab Social quedo montado (React Navigation no
desmonta tabs) (+1 = **6**) → el sexto socket recibe `MAX_CONNECTIONS_EXCEEDED`
y se desconecta — que hook pierde RT depende del orden de conexion (ruleta).
La webapp NO tiene este problema (singleton compartido). Fix Expo futuro: migrar
los 6 a un modulo socket compartido tipo `lib/streaming/socket.ts` de la webapp.

### Contexto `room:occupancy` (la intuicion de Kamilo, resuelta)

Kamilo recordaba "la ocupacion se usaba en el dashboard del cliente (Event Pulse)".
Verificado: **Event Pulse usa `checkin:update`** (aforo GLOBAL del evento, counter
m-ci + moment "Nuevo check-in") y `session:audience` (viewers por sesion) — pero
**NO `room:occupancy`**, que es ocupacion FISICA por room (silent disco check-in/out)
y se emite a un room socket (`event:{id}:admin`) que no existe en `Rooms.ts` ni
tiene join handler. Grep en las 10 superficies: cero consumidores. Probablemente
quedo preparado para un dashboard de rooms/totem futuro. La deuda D.13 del plan
W.11 queda validada CON este contexto completo: no era del Pulse.

---

## 5. Recomendaciones priorizadas — ESTADO (aplicadas 2026-07-04, aprobacion Kamilo)

1. **BUG-A — APLICADO** (`eventos-kiosko/src/hooks/useAttendance.ts`): join dentro de
   `connect` (sobrevive reconexiones) + `{ eventId }` + `payload.checkedIn`.
   Typecheck verde.
2. **BUG-B — APLICADO** (`attendance-check.html:275`): `{ eventId: EVENT_ID }`.
3. **GAP-C — APLICADO** (backend, 4 puntos + 2 imports):
   - `NetworkingController` accept → `broadcast(event_id, 'connections')`
   - `LeadController@store` → `broadcast(event_id, 'leads')`
   - `RatingController@store` + `SpeakerRatingController@store` → `broadcast(eventId, 'ratings')`
   - `PointsService::award` → `broadcast(eventId, 'leaderboard')` junto al directed
   PHP lint verde en los 5 archivos. Expo/webapp ignoran estas entities (verificado
   contra sus mapas) — unico consumidor: Event Pulse.
4. **RIESGO-D Expo — APLICADO** (Kamilo lo subio a prioridad 1, commit `0d9a754`):
   `lib/socket.ts` singleton nuevo + 6 consumidores migrados. Decisiones de diseno:
   - `join:event` centralizado en el singleton (lee eventId del authStore en cada connect)
   - `join:session` con REF-COUNT: re-emite por consumidor (el server es idempotente
     y re-envia chat:history — mismo comportamiento que cuando cada hook tenia socket
     propio); `leave:session` solo al salir el ULTIMO (sin esto el unmount de Q&A
     sacaba al user del room del chat compartido)
   - Re-join automatico de rooms tras reconexion (Socket.IO no los recuerda)
   - `disposeSocket()` solo por el owner `useDataInvalidation` (login/logout)
   - Cada consumidor registra/remueve SOLO sus listeners (patron useChat webapp)
   - Reconnection unificada a Infinity (antes: 5 en chat/qna/mode, 3 en encuestas —
     una tab con red inestable perdia esos hooks para siempre)
   Typecheck: los 7 archivos del refactor limpios (5 errores pre-existentes del WIP
   recap/leaderboard, verificado con git stash).
   **PENDIENTE verificacion viva**: regresion streaming (chat + Q&A + polls + emojis
   + pinned) + wall RT + encuestas + log server debe mostrar `conns=1` estable.
5. **Dead emits** (`session:started/ended/cancelled`, `agenda:updated`,
   `room:occupancy`) — decision futura: consumirlos (MC podria usar session lifecycle)
   o removerlos. No rompen nada hoy.

**Verificacion viva pendiente (proximo arranque de ambiente):** kiosko + attendance-check
conectados deben loguear `joined event:X` en el socket server (antes: `[security] ...
tried to join event=undefined`). Pulse con accion real (aceptar contacto / rating)
debe mover el counter en <2s sin esperar el bootstrap de 5min.
