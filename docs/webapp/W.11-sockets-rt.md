# W.11 — Sockets RT

> Socket.IO client integrado en toda la webapp. RT en agenda, Q&A, chat, social wall, encuestas, networking, sesiones live, anuncios, gamification (via invalidacion). Long-polling fallback para proxy corporativo.
>
> **Estimacion:** ~6h.
> **Dependencias:** W.0-W.10 (todos los modulos consumen eventos).
> **Estado:** Pendiente — catalogo backend audit completado 2026-05-07.

---

## Lectura obligatoria

- `PLAN.md`, `DECISIONS.md`
- **`eventos-socket/src/types.ts`** — interfaz `ServerToClientEvents` y `ClientToServerEvents` son la LISTA AUTORITATIVA. NO inventar nombres.
- `eventos-socket/src/index.ts` — handlers + endpoints internos `/internal/*` que Laravel usa para emitir
- App movil: `lib/socket.ts` (si existe) + `hooks/useChat.ts` + `hooks/useSessionMode.ts` + `hooks/useDataInvalidation.ts` (4 capas RT validadas)

---

## Drift corregido (2026-05-07)

Version previa de este doc usaba naming dot-notation (`session.updated`, `qa.question.new`, `wall.post.banned`) y listaba eventos inventados (`points.awarded`, `leaderboard.updated`, `badge.unlocked`, `notification.new`, `wall.like.updated`, `chat.dm.message.new`, etc.). El backend usa **colon-notation** (`session:started`, `question:submitted`, `wall:post`) y muchos eventos no existen — la gamification y notif in-app llegan via `data:invalidate` (invalidacion generica de queries).

---

## Catalogo real de eventos

Lista verificada contra `eventos-socket/src/types.ts` (2026-05-07). 28 eventos server→client + 7 client→server.

### Server → Client

#### Sesiones / Streaming
| Evento | Payload | Disparado por |
|---|---|---|
| `session:started` | `SessionPayload` `{id, title, eventId, startsAt}` | Admin start |
| `session:ended` | idem | Admin end |
| `session:mode_changed` | `{sessionId, mode:'chat'\|'qna'\|'poll'\|'none', streamUrl}` | Filament cambia interactive_mode/streamUrl |
| `session:config_updated` | `SessionConfigPayload` (chat_enabled, qna_enabled, polls_enabled, emoji_only, slow_mode_seconds, custom_enabled, custom_url) | Mission Control cambia config live |
| `session:audience` | `{sessionId, count}` | Audience cambio (join/leave) |
| `session:metrics` | `{sessionId, chatCount}` | Snapshot metricas |

#### Chat (por sesion)
| Evento | Payload | Notas |
|---|---|---|
| `chat:message` | `ChatMessagePayload` `{id, dbId?, attendeeId, userName, role, message, sessionId, sentAt}` | Nuevo mensaje |
| `chat:history` | `ChatMessagePayload[]` | Al unirse |
| `chat:emoji` | `{emoji, attendeeId, sessionId}` | Reaction emoji flotante |
| `chat:deleted` | `{id}` | Mod borra mensaje |
| `chat:pinned` | `{sessionId, message, author, pinnedAt}` | **Anuncio in-stream pinned** |
| `chat:unpinned` | `{sessionId}` | Quita pin |

#### Q&A
| Evento | Payload | Notas |
|---|---|---|
| `question:submitted` | `QuestionPayload` (id, body, status, upvotes, is_anonymous, my_upvote, author, created_at, sessionId) | Nueva pregunta enviada |
| `question:approved` | idem | Mod aprueba |
| `question:answered` | idem | Mod marca respondida |
| `question:upvoted` | idem | Update count upvotes |

#### Polls
| Evento | Payload | Notas |
|---|---|---|
| `poll:new` | `PollPayload` (id, title, scope, status, session_id, event_id, questions, my_answers) | Activado |
| `poll:updated` | idem | Resultados cambian |
| `poll:closed` | idem | Cerrado |

#### Social Wall
| Evento | Payload | Notas |
|---|---|---|
| `wall:post` | `WallPostPayload` `{id, body, photo_url, likes_count, comments_count, author, author_photo, created_at}` | Nuevo post |
| `wall:comment` | `WallCommentPayload` `{id, post_id, body, author, author_photo, created_at}` | Nuevo comentario |

> **Nota:** NO hay evento dedicado para likes (mobile recalcula desde re-fetch). Tampoco hay `wall:banned` — el moderado simplemente no se broadcast.

#### Anuncios
| Evento | Payload | Notas |
|---|---|---|
| `announcement:new` | `AnnouncementPayload` `{id, title, body, eventId, createdAt}` | Anuncio global del evento (room `event:{id}`) |

#### Networking
| Evento | Payload | Notas |
|---|---|---|
| `networking:notify` | `{type:'request_received'\|'request_accepted', fromName, fromAttendeeId}` | Directed event a un attendee especifico |

#### Check-in (kiosk + admin)
| Evento | Payload | Notas |
|---|---|---|
| `checkin:update` | `{checkedIn, total, attendee:{id,name,role,stand_name}}` | Aforo actualizado en room `event:{id}` |

#### Mission Control display
| Evento | Payload | Notas |
|---|---|---|
| `display:project` | `{sessionId, type:'poll_results'\|'question'\|'game_spin'\|'game_trivia'\|'game_jackpot', pollId?, gameData?, data?}` | MC proyecta algo en pantalla grande |
| `display:stop` | `{sessionId}` | Detiene proyeccion |

#### Staff / Stand invitations (W.15)
| Evento | Payload | Notas |
|---|---|---|
| `staff:invited` | `StaffInvitePayload` `{invitationId, token, sponsorName, sponsorLogo, sponsorTier, inviterName, expiresAt}` | Recibo invite a stand |
| `staff:accepted` | `StaffResponsePayload` `{invitationId, attendeeName, attendeeId, sponsorId, accepted}` | Notif al owner |
| `staff:rejected` | idem | Notif al owner |
| `staff:removed` | `{sponsorName, sponsorId}` | Owner me removio |

#### Moderacion
| Evento | Payload | Notas |
|---|---|---|
| `ban:enforced` | `{reason, expires_at}` | El backend forzo desconexion + ban |

#### Generico (gamification, perfil, etc. usan esto)
| Evento | Payload | Notas |
|---|---|---|
| `data:invalidate` | `{entity: string}` | Invalidacion generica de queries. Mobile reconcilia con TanStack Query refetch |

#### Pulse dashboard
| Evento | Payload | Notas |
|---|---|---|
| `pulse:active_users` | `{count}` | Solo en room `pulse:{eventId}` |

#### Errores
| Evento | Payload | Notas |
|---|---|---|
| `error` | `string` | `'EVENT_ACCESS_DENIED'`, `'MAX_CONNECTIONS_EXCEEDED'`, etc. |

### Client → Server

| Evento | Payload | Notas |
|---|---|---|
| `chat:send` | `{sessionId, message}` | Envia mensaje al chat |
| `chat:emoji` | `{sessionId, emoji}` | Lanza reaction |
| `chat:delete` | `{sessionId, messageId}` | Mod borra (admin only) |
| `chat:pin` | `{sessionId, message, author?}` | Pin (admin only) |
| `chat:unpin` | `{sessionId}` | Unpin |
| `join:event` | `{eventId}` | Une al room del evento |
| `join:session` | `{sessionId}` | Une al room de la sesion |
| `leave:session` | `{sessionId}` | Sale del room |
| `display:project` | `{sessionId, type, pollId?, questionData?, gameData?}` | MC proyecta (admin) |
| `display:stop` | `{sessionId}` | MC detiene (admin) |

---

## Eventos NO existentes (no usar — antes estaban inventados aqui)

Lista para referencia historica + auditoria de otros docs que aun los mencionen:

- ~~`points.awarded`, `points:awarded`~~ — usar `data:invalidate {entity:'points'}`
- ~~`leaderboard.updated`, `leaderboard:updated`~~ — usar `data:invalidate {entity:'leaderboard'}`
- ~~`badge.unlocked`, `badge:unlocked`~~ — usar `data:invalidate {entity:'badges'}`
- ~~`passport.stamp.new`~~ — usar `data:invalidate {entity:'passport'}`
- ~~`streak.bonus.awarded`~~ — usar `data:invalidate {entity:'streak'}`
- ~~`notification.new`~~ — no existe modulo notif in-app (ver W.10 scope decision)
- ~~`session.revoked`~~ — el backend no emite esto; logout multi-device se valida REST en cada request
- ~~`agenda:updated`, `agenda:delayed`~~ — el BACKEND-API-MAP los lista pero NO estan en `types.ts`. Se manejan via `data:invalidate {entity:'agenda'}`
- ~~`wall.like.updated`~~ — los likes se reconcilian por refetch on focus / invalidacion
- ~~`chat.dm.message.new`~~ — chat 1:1 NO existe
- ~~`connection.request.new`, `connection.accepted`~~ — usar `networking:notify`
- ~~`stream.announcement.broadcast`~~ — anuncios in-stream llegan via `chat:pinned` o `announcement:new`
- ~~`qa.question.*`~~ — naming real `question:*`
- ~~`poll.*` (dot)~~ — naming real `poll:*` (colon)
- ~~`wall.post.new`, `wall.post.banned`, `wall.comment.new`~~ — naming real `wall:post`, `wall:comment`
- ~~`session.updated`, `session.cancelled`~~ — naming real `session:started`, `session:ended`, `session:mode_changed`, `session:config_updated`

---

## Rooms

| Room | Quien se une | Evento client |
|---|---|---|
| `event:{eventId}` | Cualquier user del evento | `join:event` |
| `session:{sessionId}` | Asistente que abre stream | `join:session` |
| `pulse:{eventId}` | Auto-join si role=`pulse` | (auto) |

NO hay rooms `sponsor:{id}`, `connection:{id}` (los listaba la version previa, no existen). Sponsors no tienen RT scoped y connections usan eventos directed via `attendeeConnections` map.

---

## Limites de seguridad

- **MAX_CONNECTIONS_PER_USER = 5** (`eventos-socket/src/index.ts:542`) — multiples tabs OK hasta 5
- **SEC-1.1**: `join:event` valida `eventId === user.eventId` (los backends comparten la verificacion, no se hace HTTP call)
- **SEC-2.2**: CORS fail-closed (whitelist explicita, sin wildcard)
- **SEC-3.4**: Por-user connection limit con cleanup en disconnect

---

## Fase 0 — Setup cliente (~1h) — 0/4

### 0.1 Instalacion — 0/2
- [ ] `pnpm add socket.io-client`
- [ ] Crear `src/lib/socket/client.ts` con singleton

### 0.2 Handshake — 0/2
- [ ] Conectar con auth handshake: `auth: { token: <bearer> }` (extraer cookie via API route helper `/api/auth/socket-token` que retorna el bearer)
- [ ] URL: `process.env.NEXT_PUBLIC_SOCKET_URL` con `transports: ['websocket', 'polling']`

---

## Fase 1 — Connection management (~1h) — 0/4

### 1.1 Reconnect — 0/2
- [ ] Exponential backoff: 1s, 2s, 4s, 8s, 16s, 30s max (Socket.IO lo trae nativo, configurar `reconnectionDelay` y `reconnectionDelayMax`)
- [ ] Si reconnect exitoso → re-join rooms + invalidate queries afectadas

### 1.2 Disconnect — 0/2
- [ ] Toast Sonner sutil "Sin conexion en tiempo real, reintentando..." si falla 3 veces
- [ ] Manejar `error` event: `MAX_CONNECTIONS_EXCEEDED` → mensaje claro, `EVENT_ACCESS_DENIED` → redirect

---

## Fase 2 — Rooms management (~1h) — 0/3

### 2.1 Auto join/leave — 0/3
- [ ] Hook `useSocketRoom('event'|'session', id)` join al mount, leave al unmount
- [ ] Re-emit `join:*` despues de reconnect (Socket.IO no las recuerda)
- [ ] Aislamiento estricto: no escuchar eventos de rooms no joineadas

---

## Fase 3 — Listeners + invalidation (~2h) — 0/8

### 3.1 Helper generico — 0/2
- [ ] `useSocketListener<T>(event, handler, deps)` envuelve subscribe + unsubscribe + cleanup
- [ ] Helper `invalidateBy(entity)` para `data:invalidate` → mapa de entity → queryKeys (`points`, `leaderboard`, `badges`, `passport`, `streak`, `agenda`, etc.)

### 3.2 Por modulo — 0/6
- [ ] **Agenda (W.3):** `data:invalidate {entity:'agenda'}` → invalidate
- [ ] **Streaming (W.4):** `chat:message`, `chat:history`, `chat:emoji`, `chat:deleted`, `chat:pinned`, `chat:unpinned`, `question:*`, `poll:*`, `session:mode_changed`, `session:config_updated`, `session:audience`, `display:project`, `display:stop`
- [ ] **Social Wall (W.6):** `wall:post`, `wall:comment` → dedup + merge
- [ ] **Networking (W.8):** `networking:notify` → toast + invalidate `me/contact-requests`
- [ ] **Anuncios (W.14):** `announcement:new` → toast + invalidate `announcements`
- [ ] **Stand (W.15):** `staff:invited`, `staff:accepted`, `staff:rejected`, `staff:removed` → invalidate `me/stand` + `me/pending-invitations`
- [ ] **Gamification (W.9):** `data:invalidate {entity:'points'|'leaderboard'|'badges'|'passport'|'streak'}` → invalidate por entidad
- [ ] **Auth/Ban:** `ban:enforced` → modal + redirect, `error 'BAN'` global handler

---

## Fase 4 — Dedup logic (~1h) — 0/3

### 4.1 Chat dedup tempId — 0/2
- [ ] Cliente envia `chat:send` → render optimistic con `tempId = "tmp-{ts}-{rnd}"`
- [ ] Backend persiste y broadcast `chat:message` con id real `dbId-attendeeId-ts`
- [ ] Cliente match por `attendeeId + sentAt` cercano → reemplaza optimistic

### 4.2 Skip-self pattern — 0/1
- [ ] Para acciones propias (ej. like): el cliente recibe su propio broadcast → comparar `attendeeId === me.attendeeId` → si igual, ignorar (ya aplicado optimistic)

---

## Fase 5 — Long-polling fallback (~30min) — 0/2

### 5.1 Bloqueo WebSocket — 0/1
- [ ] Si WebSocket falla → Socket.IO baja a polling automatico (configurado en transports)

### 5.2 Banner — 0/1
- [ ] Si esta en polling → banner sutil "Conexion en modo compatibilidad" (Bancolombia firewall)

---

## Fase 6 — Tests (~30min) — 0/3

### 6.1 Vitest — 0/1
- [ ] `useSocketListener` subscribe/unsubscribe correcto + cleanup en unmount

### 6.2 Playwright — 0/2
- [ ] Happy path: 2 tabs, accion en tab 1 (chat send) → tab 2 recibe RT update
- [ ] Edge case: simular disconnect → reconnect + recupera estado (re-join automatico)

---

## Edge cases

- [ ] Token expirado durante sesion → socket auth falla en handshake → redirect login
- [ ] Multiples tabs (max 5) → cada tab tiene su socket. Tab 6 → server emite `error 'MAX_CONNECTIONS_EXCEEDED'`
- [ ] User cambia de pestana → socket sigue activo (no desconectar en blur)
- [ ] Emoji burst (50 likes en 5s) → throttle visual con `requestAnimationFrame` + buffer
- [ ] Socket event antes de TanStack Query inicial → buffer o ignore segun caso (el patron mobile usa `setQueryData` con merge defensivo)
- [ ] Network change (WiFi → 4G) → reconnect transparente
- [ ] `data:invalidate` con entity desconocido → ignorar (forward-compat con backend)
- [ ] `display:project` durante replay (sesion ended) → ignorar overlay (solo en live)

---

## Cierre

- [ ] Tests verde
- [ ] Validado proxy corporativo (firewall WebSocket bloqueado → polling)
- [ ] Validado en cada modulo W.x con su tabla de eventos
- [ ] Lighthouse no degradado por socket
- [ ] Anadir gaps detectados a `BACKEND-API-MAP.md` seccion sockets (la version actual lista solo 7 eventos)
- [ ] Commit DaVinci + memoria + PENDIENTES.md
