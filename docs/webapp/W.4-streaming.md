# W.4 — Streaming + Q&A + Chat + Polls + Trivia + Anuncios in-stream + Replay

> Experiencia virtual core. Player polimorfico (YouTube / Vimeo / iframe custom / URL directa) + Q&A + chat + polls + Trivia + anuncios in-stream + replay (`recording_url`). Reusa la logica probada de la app movil (`session-stream/[id].tsx`).
>
> **Estimacion:** ~14h.
> **Dependencias:** W.0, W.1, W.3 (agenda da contexto sesion), W.16 (Trivia comparte engine).
> **Estado:** **IMPLEMENTADO 2026-05-07** (Fases 0-9 + 11 cerradas, Trivia delegada a W.16).

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`, `AUTH-SPEC.md`
- `W.0-spatial-ui.md` (panels)
- `BACKEND-API-MAP.md` (shapes y endpoints)
- App movil: `app/(app)/session-stream/[id].tsx` + `hooks/useSessionMode.ts` + `hooks/useSessionConfig.ts` + `components/screens/{Chat,QnA,Poll,Trivia}Panel.tsx` — patron validado
- `eventos-socket/src/types.ts` — eventos reales (LISTA AUTORITATIVA, no asumir)

---

## Drift detectado vs roadmap original (2026-05-07)

Antes de codear se audito backend + mobile + socket. Cambios respecto a la version previa del W.4:

| Asuncion previa | Realidad | Accion |
|---|---|---|
| Solo Vimeo embed | Mobile soporta YouTube + Vimeo + iframe custom (Restream/OBS) + URL directa | Player polimorfico, NO `@vimeo/player` mandatorio |
| `GET /sessions/{id}/stream` | NO existe — datos vienen del SessionResource (`stream_url`, `stream_iframe`, `recording_url`) | Reusar agenda response |
| `GET /sessions/{id}/replay` | NO existe — `recording_url` viene en SessionResource | Detectar via `status === 'finished' && recording_url` |
| `GET /sessions/{id}/announcements?stream=true` | NO existe filtro stream — anuncios in-stream llegan via socket `chat:pinned` o `announcement:new` o `display:project` | Suscribirse a esos 3 eventos |
| `GET /sessions/{id}/messages?after=cursor` | Real: `?page=1&per_page=50` | Paginacion normal |
| `GET /sessions/{id}/trivia/active` | NO existe — trivia llega via `session:config_updated` (panel switch) + `display:project { type:'game_trivia' }` | Reusar useSessionConfig |
| Sockets `qa.question.*`, `poll.*`, `trivia.*`, `stream.announcement.broadcast` | **NO existen** — los reales son: `question:submitted/approved/answered/upvoted`, `poll:new/updated/closed`, `chat:pinned/unpinned`, `display:project` | Renombrar todos los listeners |
| `chat:message:new` | Real: `chat:message` | Renombrar |
| Endpoint user live-config | Existe pero no estaba en BACKEND-API-MAP: `GET /events/{eventId}/sessions/{sessionId}/live-config` | Documentado aqui, anadir a BACKEND-API-MAP en pulido futuro |

---

## Alcance

1. **Player polimorfico** (no solo Vimeo) — embed iframe segun source: YouTube / Vimeo / iframe custom / URL directa / empty state
2. **Q&A live**: ver preguntas + upvote + enviar pregunta + moderacion
3. **Chat live**: mensajes con tempId (optimistic) + ack + `chat:emoji` reactions + `chat:pinned` banner
4. **Polls live**: votar encuesta activa de la sesion
5. **Trivia Kahoot-style**: panel delegado a W.16, aqui solo integracion (panel switch via `session:config_updated`)
6. **Anuncios in-stream**: 3 fuentes — `chat:pinned` (pin del chat), `announcement:new` (anuncio global del evento durante la sesion), `display:project` (overlay desde Mission Control)
7. **Replay**: si `session.status === 'finished'` y `recording_url !== null`, mismo player con la URL de grabacion + indicador "GRABACION"
8. **Custom panel**: `activePanel === 'custom'` muestra iframe externo (URL configurada por moderador)
9. **Layout spatial**:
   - Mobile: stack (player full / tabs Chat/Q&A/Polls/Trivia debajo)
   - Tablet: player + 1 panel a la vez con tabs internos
   - Desktop: spatial — player primario + chat secondary + Q&A secondary

---

## Endpoints reales (verificados 2026-05-07)

Todos van por API routes proxy de Next.js (`/api/...`) que inyectan cookie `eventos_auth` como Bearer.

**Stream config** (no hay endpoint dedicado — viene en agenda):
- Datos de sesion ya cargados desde `GET /events/{eventId}/agenda` o `useSession(sessionId)` derivado.
- `EventSessionResource` incluye: `stream_url`, `stream_iframe`, `recording_url`, `interactive_mode`, `status`, `room_id`, `silent_disco_group_id`.

**Live config (Filament + Mission Control settings):**
- `GET /events/{eventId}/sessions/{sessionId}/live-config` — retorna `SessionLiveConfig` (panel activo, emoji_only, slow_mode_seconds, custom_url).

**Q&A:**
- `GET /events/{eventId}/sessions/{sessionId}/questions?approved=true` — lista (publico).
- `POST /events/{eventId}/sessions/{sessionId}/questions` body `{body, is_anonymous?}`.
- `POST /events/{eventId}/sessions/{sessionId}/questions/{qId}/upvote`.

**Chat:**
- `GET /sessions/{sessionId}/chat/messages?page=1&per_page=50` (paginado).
- Envio en tiempo real via socket `chat:send` (no REST).

**Polls:**
- `GET /sessions/{sessionId}/poll/active` — poll activo o null.
- `POST /polls/{poll}/vote` body `{option_id}`.

**Ratings (ya wireado en W.3 — reutilizar):**
- `POST /events/{eventId}/sessions/{sessionId}/rate` body `{rating: 1-5, comment?}`.
- `GET /events/{eventId}/my-ratings`.

**Anuncios globales del evento:**
- `GET /events/{eventId}/announcements`.

**Tracking analytics (paridad con mobile):**
- `POST /track` body `{event_id, module_slug:'agenda', action:'session_stream_view', target_id:sessionId, target_type:'session', duration_seconds, metadata:{interactive_mode}}`.

**Replay:** mismo `EventSessionResource.recording_url` — no hay endpoint extra.

---

## Eventos socket (LISTA REAL)

Confirmados en `eventos-socket/src/types.ts`. Suscribirse desde W.11 (W.4 puede usar fetch+invalidar TanStack hasta entonces, o adelantar la conexion para esta vista — decision en F.6).

**Sesion:**
| Evento | Trigger | Payload | Uso en W.4 |
|---|---|---|---|
| `session:started` | Admin start | `{id, title, eventId, startsAt}` | Refrescar status |
| `session:ended` | Admin end | idem | Toggle a "GRABACION" si recording_url, mostrar rating modal |
| `session:mode_changed` | Filament cambia interactive_mode/streamUrl | `{sessionId, mode, streamUrl}` | Cambiar streamUrl + activePanel |
| `session:config_updated` | Mission Control cambia config | `SessionConfigPayload` | Update emoji_only, slow_mode, custom_enabled, etc. |
| `session:audience` | Audience count | `{sessionId, count}` | Viewer count en player |
| `session:metrics` | Chat count snapshot | `{sessionId, chatCount}` | Opcional |

**Chat (sesion):**
- `chat:message` (NO `chat:message:new`) — nuevo mensaje del chat
- `chat:emoji` — reaction emoji flotante
- `chat:deleted` — `{id}` mod borra mensaje
- `chat:history` — `[]` al join
- `chat:pinned` — `{sessionId, message, author, pinnedAt}` ← **anuncio in-stream pinned**
- `chat:unpinned` — `{sessionId}`

**Q&A:**
- `question:submitted` / `question:approved` / `question:answered` / `question:upvoted` — todos `QuestionPayload`

**Polls:**
- `poll:new` / `poll:updated` / `poll:closed` — todos `PollPayload`

**Anuncios:**
- `announcement:new` — anuncio global del evento (room `event:{eventId}`)

**Display Mission Control:**
- `display:project` — `{sessionId, type:'poll_results'|'question'|'game_spin'|'game_trivia'|'game_jackpot', pollId?, gameData?, data?}`
- `display:stop` — `{sessionId}`

**Cliente → server (chat send):**
- `chat:send` `{sessionId, message}`
- `chat:emoji` `{sessionId, emoji}`
- `join:event` `{eventId}`, `join:session` `{sessionId}`, `leave:session` `{sessionId}`

---

## Refs visuales

- App movil streaming layout (`features/Screenshot 2026-...`)
- `design/showcase-onboarding-v6.html` `str-wrap` — concepto

---

## Fase 0 — Setup (~30min) — 0/3

### 0.1 Decision player iframe simple vs SDK — 0/1
- [ ] Decidir si necesitamos `@vimeo/player` (eventos play/pause/ended para tracking fino) o basta iframe HTML. Mobile usa iframe simple via WebView. Default: iframe HTML, agregar SDK solo si tracking lo justifica. **Si va SDK:** `pnpm add @vimeo/player`.

### 0.2 CSP — 0/2
- [ ] CSP `frame-src` whitelist: `*.vimeo.com`, `*.youtube.com`, `*.youtube-nocookie.com`, `*.restream.io`, plus dominios custom del cliente
- [ ] CSP `connect-src` no requiere ajuste (socket ya cubierto)

---

## Fase 1 — Player polimorfico (~2h) — 0/6

### 1.1 Detector de source — 0/2
- [ ] `lib/streaming/detectSource.ts` — funcion que recibe `(stream_url, stream_iframe)` y retorna `{kind: 'iframe-html'|'youtube'|'vimeo'|'generic'|'empty', payload}`
- [ ] Tests Vitest: youtu.be, youtube.com/watch?v=, youtube.com/live/, vimeo.com/123, restream.io, iframe code completo, vacio

### 1.2 Componente — 0/3
- [ ] `<StreamPlayer source={...} />` en `components/streaming/StreamPlayer.tsx`
  - `iframe-html` → render iframe con sandbox + srcdoc del codigo embed
  - `youtube` → `https://www.youtube-nocookie.com/embed/{id}?autoplay=1&modestbranding=1`
  - `vimeo` → `https://player.vimeo.com/video/{id}?autoplay=1`
  - `generic` → `<iframe src={url}>` con `referrerPolicy="no-referrer"` y `allow="autoplay; fullscreen; picture-in-picture"`
  - `empty` → empty state "Transmision no disponible aun"
- [ ] Aspect-ratio 16/9 con tailwind + responsive (mobile full width, desktop respeta panel size)
- [ ] Indicador "EN VIVO" pulsante (status === 'live') o "GRABACION" (status === 'finished' && recording_url)

### 1.3 Tracking view — 0/1
- [ ] Hook `useStreamViewTracking(sessionId, mode)` — al unmount o tab background, dispara `POST /track` con duration_seconds (paridad mobile, threshold 3s)

---

## Fase 2 — Hooks data layer (~1.5h) — 0/4

### 2.1 useSessionDetail — 0/1
- [ ] Hook que obtiene datos de la sesion desde la cache de TanStack (ya cargada por W.3 agenda) o fetch fallback. Retorna `{session, isLoading}`.

### 2.2 useSessionLiveConfig — 0/2
- [ ] Hook TanStack: `GET /events/{eventId}/sessions/{sessionId}/live-config`
- [ ] Aceptar updates externos (callback `applyConfigUpdate` igual que mobile) para futuro wiring socket en W.11

### 2.3 useSessionRating — 0/1
- [ ] Reusar el hook ya creado en W.3 (web). Si no existe en webapp todavia, portar de `eventos-app/hooks/useSessionRating.ts`

---

## Fase 3 — Q&A panel (~3h) — 0/8

### 3.1 Lista — 0/3
- [ ] `<QAPanel sessionId eventId />` con preguntas ordenadas por `upvotes` desc
- [ ] Item: avatar/nombre (o "Anonimo") + body + count upvotes + boton heart
- [ ] Filtros: "Todas", "Mas votadas", "Mias"

### 3.2 Enviar pregunta — 0/3
- [ ] Input + boton enviar (max 280 chars)
- [ ] Toggle "Enviar como anonimo"
- [ ] Estado `pending` (esperando moderacion) si moderacion activa

### 3.3 Upvote — 0/2
- [ ] Optimistic update count via `queryClient.setQueryData`
- [ ] Anti-parpadeo: si llega `question:upvoted` durante mutation, merge sin regresar count

---

## Fase 4 — Chat panel (~3h) — 0/8

### 4.1 Lista — 0/3
- [ ] `<ChatPanel sessionId emojiOnly slowModeSeconds />` mensajes scrollable virtualizados
- [ ] Item: avatar + nombre + role badge + texto + timestamp + delete (admin)
- [ ] Auto-scroll al final cuando llega nuevo mensaje (con stick-to-bottom inteligente)

### 4.2 Enviar — 0/3
- [ ] Input + boton enviar (respeta `slow_mode_seconds`)
- [ ] Optimistic con tempId — id format `dbId-attendeeId-ts` (paridad backend)
- [ ] Si fallo: tap para reintentar

### 4.3 Pinned banner — 0/1
- [ ] Banner sticky arriba del panel cuando hay `chat:pinned` (suscripcion via useChat / W.11)

### 4.4 Moderacion + emoji_only — 0/1
- [ ] Si banned: toast + input deshabilitado
- [ ] Si `emoji_only=true`: input solo acepta emojis (regex), placeholder "Solo emojis"

---

## Fase 5 — Polls panel (~2h) — 0/5

### 5.1 Lista activa — 0/2
- [ ] `<PollPanel sessionId />` muestra poll activo (1 a la vez)
- [ ] Header: titulo + tiempo restante (si poll tiene `expires_at`)

### 5.2 Voting — 0/2
- [ ] Suporte a `multiple_choice`, `open_text`, `star_rating` (per `PollQuestion.question_type`)
- [ ] Click opcion → mutation `POST /polls/{id}/vote` → muestra resultados con barra
- [ ] Si ya voto (`my_answers` no vacio): mostrar resultados directamente

### 5.3 Empty — 0/1
- [ ] Si `null`: empty state "Sin encuestas activas"

---

## Fase 6 — Trivia integration (~1.5h) — 0/3

### 6.1 Panel switch — 0/2
- [ ] `<TriviaPanel sessionId />` (componente delegado a W.16) montado cuando `activePanel === 'trivia'`
- [ ] Listener `display:project { type:'game_trivia' }` → switch tab automatico + sound alert con mute toggle

### 6.2 UX — 0/1
- [ ] Pregunta + 4 opciones + countdown bar — implementacion completa en `W.16-live-moments.md` Fase 1

---

## Fase 7 — Anuncios in-stream (~1h) — 0/4

### 7.1 Pinned banner (chat:pinned) — 0/1
- [ ] Ya cubierto en Fase 4.3 (banner del chat)

### 7.2 Announcement overlay (announcement:new) — 0/2
- [ ] `<StreamAnnouncementOverlay />` slide-down sutil sobre player con titulo+body + 5s auto-dismiss
- [ ] Solo dispara si la sesion esta `live` (no en replay)

### 7.3 Display project overlay (display:project) — 0/1
- [ ] Si `type === 'poll_results' | 'question'` y la sesion es la actual, overlay spotlight grande sobre el player (Mission Control esta proyectando algo). Apariencia tipo "lower third". `display:stop` lo cierra.

---

## Fase 8 — Custom panel (~30min) — 0/2

- [ ] Si `activePanel === 'custom'` y `custom_url` no es null → `<iframe src={custom_url} sandbox="allow-scripts allow-same-origin" referrerPolicy="no-referrer">`. CSP debe permitir el dominio.
- [ ] Empty state si `custom_url` es null

---

## Fase 9 — Replay grabacion (~1h) — 0/4

### 9.1 Detection — 0/2
- [ ] Si `session.status === 'finished'` y `recording_url !== null` → `<StreamPlayer source={detectSource(recording_url, null)} />` (reusa detector)
- [ ] Indicador "GRABACION" en lugar de "EN VIVO"

### 9.2 Comportamiento — 0/2
- [ ] Player con controles full (play, pause, seek, speed) — el iframe del provider ya los trae si es YouTube/Vimeo
- [ ] Rating modal aparece 1.5s despues si user no rateo todavia (paridad mobile)

---

## Fase 10 — Layout spatial (~1.5h) — 0/4

### 10.1 Desktop — 0/2
- [ ] Player primary (60% width) + Chat secondary (20%) + Q&A secondary (20%)
- [ ] Tab interna en panel secondary para alternar Q&A / Polls / Trivia segun activePanel

### 10.2 Mobile/tablet — 0/2
- [ ] Mobile: player full width arriba + bottom tabs (Chat / Q&A / Polls / Trivia / Custom segun activePanel)
- [ ] Tablet: player full width + 1 panel debajo con tabs

---

## Fase 11 — Tests (~1.5h) — 0/6

### 11.1 Vitest — 0/3
- [ ] `detectSource` con todos los formatos
- [ ] Optimistic chat con tempId + ack
- [ ] Optimistic upvote anti-parpadeo

### 11.2 Playwright — 0/3
- [ ] Happy path: ver stream + enviar Q&A + chat + votar poll
- [ ] Edge case: chat banned → input deshabilitado
- [ ] Edge case: stream sin URL → empty state correcto

---

## Edge cases

- [ ] `stream_url` y `stream_iframe` ambos null → empty state, NO crash
- [ ] User sin permiso (sesion premium) → CTA "Acceder con codigo"
- [ ] Conexion lenta → buffering visible, sin parpadeo
- [ ] Poll cierra mientras user vota → toast "Encuesta cerrada"
- [ ] Q&A moderada → estado "Pendiente aprobacion" + filtro "Mias" la muestra
- [ ] Chat con `slow_mode_seconds > 0` → cooldown visual + tooltip "Espera Xs"
- [ ] Multiple polls (no debe pasar por backend, pero defensivo): mostrar solo el activo
- [ ] User abandona panel y vuelve → reuso del socket (no duplicar conexion), refetch panels
- [ ] CSP bloquea iframe → log Sentry + fallback con link "Abrir en nueva pestana"
- [ ] `custom_url` con dominio no whitelisted en CSP → mensaje + log
- [ ] Replay sin `recording_url` → empty state "Grabacion no disponible"
- [ ] `display:project` durante replay → ignorar (overlay solo en live)

---

## Cierre

- [x] Tests Vitest verde — 83/83 (incluye 9 nuevos: detectSource 19 + dedup chat/upvote 9)
- [ ] Tests Playwright happy + edge case — pendiente sesión dedicada de E2E
- [x] Validado escritorio (1366×768, 1920×1080) — refactor canvas + chat panel + Q&A + polls
- [ ] Tablet H + mobile (mobile = port directo de la app, sesión dedicada)
- [ ] Lighthouse — pendiente
- [x] Endpoint `GET /events/{eventId}/sessions/{sessionId}/live-config` documentado en este W.4
- [x] Commit + memoria + actualizar este doc

---

## Implementación 2026-05-07 — resumen técnico

**Fase base — socket singleton:**
- `lib/streaming/socket.ts` con `getSocket()` cached + auth bearer via cookie
- `/api/auth/socket-token` lee cookie httpOnly y la pasa al cliente solo para handshake
- 1 sola conexión por session del browser (regla `feedback_no_extra_sockets`)

**Fase 0 + 1 — StreamPlayer polimórfico:**
- `lib/streaming/detectSource.ts` con 5 kinds: youtube/vimeo/iframe-html/generic/empty
- `<StreamPlayer>` con badges EN VIVO / GRABACIÓN, fluido 16:9 estricto via `min(100cqw, calc((100cqh - reserve) * 16/9))` + container queries
- CSP `frame-src` whitelist (youtube/youtube-nocookie/vimeo/restream/twitch/zoom)
- iframe **sin** `referrerPolicy` (rompe verificación embed YouTube → error 153)
- iframe `allow="autoplay; fullscreen"` (mismo que Mission Control)

**Fase 2 + 2.5 — hooks data layer + shell:**
- `useSessionLiveConfig` con TanStack Query + **socket listeners RT** (`session:config_updated` + `session:mode_changed`) — los cambios admin se reflejan instantáneo
- `useStreamViewTracking` con duration analytics (paridad mobile, threshold 3s, dispara en visibilitychange/pagehide/unmount via `keepalive: true`)
- API routes proxy: `/api/streaming/{live-config,chat-history,poll-active,poll-vote,questions/...}/`
- Render dedicado por viewport — Desktop spatial, Tablet H compact, Mobile estilo app

**Fase 3 — QAPanel + useQnA:**
- Replica 1:1 `eventos-app/hooks/useQnA.ts`
- Socket events `question:approved/answered/upvoted`
- 3 filtros: Todas / Top / Mías
- Submit con `is_anonymous` toggle
- Upvote optimistic con revert si falla

**Fase 4 — ChatPanel + useChat:**
- Replica 1:1 `eventos-app/hooks/useChat.ts` (344 líneas portadas)
- Socket events `chat:message/history/deleted/pinned/unpinned/emoji`
- Dedup por `socketMsgIds` Set
- Slow mode countdown
- Pinned banner con border-left accent (no gold)
- Bubbles: propio surface-high, otro surface-medium (sin tinte accent — feo con accent rojo)
- Floating emojis con **Framer Motion** (5 paths con keyframes interpolados, scale/rotation random)

**Fase 5 — PollPanel:**
- Reusa `useChat` para `activePoll/myAnswers/votePoll` (mismo socket sirve chat + polls)
- 3 tipos: multiple_choice (single vota inmediato, multi con submit), open_text, star_rating
- Grace period 8s al cerrar (paridad mobile)

**Fase 7 — Anuncios in-stream:**
- `useAnnouncementOverlay` listener `announcement:new`
- Overlay slide-down sobre player con border-left accent + backdrop blur, auto-dismiss 5s

**Fase 8 — Custom panel:**
- iframe sandbox `allow-scripts allow-same-origin allow-popups allow-forms`
- Mismo `allow="autoplay; fullscreen"` que el player

**Fase 9 — Replay:**
- StreamPlayer detecta `status: 'finished'/'ended' + recording_url`
- Auto-show RatingModal 1.5s después si user no ha calificado (paridad mobile)

**Trivia (Fase 6):** placeholder con copy "Trivia activa — la jugada completa llega en W.16 (Live Moments)". Backend dispara la trivia via `display:project { type: 'game_trivia' }` desde Mission Control.

**Mod chat (admin only):** fuera de scope — el panel del asistente no maneja delete/ban.

**Mobile:** port directo de la app móvil — sesión dedicada (no se hace ahora con el resto).

---

## Drift de tipos descubierto y corregido

Al implementar:
- ENUM real de DB para `event_sessions.status`: `'scheduled' | 'live' | 'cancelled' | 'finished'` (mobile y types web usaban 'ended' por drift histórico — ambos aceptados ahora).
- Endpoint `GET /events/{eventId}/sessions/{sessionId}/live-config` (público) NO estaba en BACKEND-API-MAP, se agregó referencia aquí.
- `live-config` payload incluye `stream_url/iframe/recording_url` además de las flags interactive — Mission Control puede cambiarlos en vivo.

---

## Bugs cerrados durante el shipping

- **Error 153 YouTube embed**: causado por `referrerPolicy="no-referrer"`. Quitado.
- **Player con border pixelado**: doble border-radius (wrapper + child). Override `border-radius: 0 !important` en child + isolation/translateZ/contain en wrapper.
- **Player NO 16:9 estricto**: aspect-ratio + max-height conflict. Arreglado con `width: min(100cqw, calc((100cqh - reserve) * 16/9))`.
- **Modal Calificar sin estilos**: agenda.css solo se importaba en AgendaView. Importado también en StreamShell.
- **Modal Calificar fuera del viewport**: wrapper `agenda-root` era inline. Cambiado a `position: fixed; inset: 0; z-index: 100`.
- **Polls no carga al refresh**: fetch del poll activo estaba dentro del handler `connect` con race contra `s.connected` ya true. Separado a useEffect propio.
- **Config no se refresca en RT**: `useSessionLiveConfig` no escuchaba `session:config_updated`. Agregado listener — cambios MC instantáneos en web.
- **Listeners socket cross-talk**: hooks compartiendo el socket singleton hacían `s.off("event")` sin handler ref → borraban listeners de otros hooks. Cambiado a `s.off(event, fn)` con referencias explícitas.

---

## Sesiones del seeder W.4 para QA

- 82: `live + qna + YouTube` — validar Q&A + upvote
- 83: `live + chat + slow_mode 10s + Vimeo` — validar chat + cooldown
- 84: `live + poll + iframe Twitch` — validar votación
- 88: `scheduled + interactive_mode none` — empty state interaction column
- 89: `scheduled + custom_url Mentimeter` — validar iframe custom
- 90: `scheduled SIN stream_url` — empty state del player
- 93: `finished + recording_url YouTube` — replay + auto-rate modal
