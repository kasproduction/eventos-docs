# Gaps Analysis — Optimistic UI EventOS

> Para cada accion sin optimistic UI o parcial: comportamiento actual, deseado,
> archivos a tocar, riesgos. Verificado contra codigo real.
> Generado: 2026-04-25

---

## 1. Gaps por accion

### GAP-1: Chat send (A7) — PRIORIDAD CRITICA

**Comportamiento actual:**
- Usuario toca enviar → `socket.emit('chat:send', { sessionId, message })` (useChat.ts:251)
- Nada aparece en pantalla hasta que el server broadcastea `chat:message` de vuelta
- Server genera tempId: `${sessionId}-${attendeeId}-${Date.now()}` (chat.ts:363)
- Broadcast con `io.to(Rooms.chat(sessionId))` (chat.ts:381) — incluye al sender
- Cliente recibe su propio mensaje via `chat:message` listener (useChat.ts:160-164)
- Dedup existe via Set `socketMsgIds` (useChat.ts:70), pero NO es para optimistic — es para evitar duplicados entre history y live
- **Latencia percibida: 300-500ms** (network ida + server + network vuelta)

**Comportamiento deseado (patron 4.2 del brief — TempId):**
1. Usuario toca enviar → mensaje aparece inmediatamente con status "sending" (~16ms)
2. Cliente genera tempId, lo envia al server
3. Server preserva tempId en broadcast
4. Cliente reconcilia: al recibir `chat:message` con mismo tempId, actualiza status a "sent" (no duplica)
5. Si timeout 5s, marca status "failed" con opcion de reintentar
6. Haptic feedback al tocar enviar

**Archivos a tocar:**
- `eventos-app/hooks/useChat.ts` — logica de envio + reconciliacion
- `eventos-socket/src/chat.ts` — aceptar tempId del cliente (linea 325+), preservar en payload
- `eventos-socket/src/types.ts` — actualizar ClientToServerEvents para incluir tempId
- `eventos-app/components/screens/ChatPanel.tsx` — UI de status (sending/sent/failed)

**Riesgos:**
- **Duplicados:** Si el tempId del cliente no matchea con el broadcast, el mensaje aparece 2 veces. Mitigacion: dedup por tempId en el listener de `chat:message`
- **Orden:** Mensajes optimistas pueden aparecer fuera de orden si el server reordena. Mitigacion: usar timestamp local para sort, server timestamp para reconciliar
- **Blocked words:** El server silencia mensajes con palabras bloqueadas sin notificar (chat.ts). Con optimistic, el mensaje apareceria y luego desapareceria. Mitigacion: agregar ack con error code "blocked", o mantener el mensaje local como "no enviado"
- **Rate limit:** Si el server rechaza por slow mode (1 msg/2s), el mensaje optimista desaparece. Mitigacion: el cliente debe conocer el slow_mode_seconds y prevenirlo localmente
- **Retrocompatibilidad:** Si el server recibe tempId nuevo pero un cliente viejo no lo envia, no debe romper. Mitigacion: tempId como campo opcional

---

### GAP-2: Emoji reaction en chat (A8) — PRIORIDAD MEDIA

**Comportamiento actual:**
- `socket.emit('chat:emoji', { sessionId, emoji })` (useChat.ts)
- Server valida whitelist, broadcastea con `io.to()` (chat.ts:433-445)
- Sender recibe su propio emoji de vuelta
- **Latencia percibida: 200-400ms**

**Comportamiento deseado:**
1. Emoji aparece inmediatamente en local (~16ms)
2. Server broadcastea a otros via `socket.to()` (skip-self)
3. Haptic feedback ligero

**Archivos a tocar:**
- `eventos-app/hooks/useChat.ts` — insertar emoji local antes de emit
- `eventos-socket/src/chat.ts` — cambiar `io.to()` a `socket.to()` en linea 444

**Riesgos:**
- Bajo. Emojis son efimeros, no persisten. Un duplicado visual es menor.
- Si cambiamos a `socket.to()`, el sender nunca recibe confirmacion del server. Aceptable para emojis.

---

### GAP-3: Enviar pregunta Q&A (A9) — PRIORIDAD MEDIA

**Comportamiento actual:**
- `POST /events/{id}/sessions/{id}/questions` via REST (useQnA.ts:109-123)
- Pregunta se agrega a `myQuestions` DESPUES de que el server responde (onSuccess, linea 117-118)
- **Latencia percibida: 400-800ms**
- Server puede silenciar (blocked words, linea 79 de QuestionController) — retorna 201 pero no crea row

**Comportamiento deseado:**
1. Pregunta aparece en "mis preguntas" inmediatamente con status "pending"
2. Si server confirma, actualiza con ID real
3. Si server rechaza (blocked), remueve con toast sutil

**Archivos a tocar:**
- `eventos-app/hooks/useQnA.ts` — agregar onMutate con pregunta optimista
- Posiblemente QnAPanel.tsx si la UI de "mis preguntas" no soporta status pending

**Riesgos:**
- **Blocked words silenciosa:** El server retorna 201 sin crear la pregunta. El cliente pensaria que se guardo. Mitigacion: el server deberia retornar un campo `created: false` o un status 422
- **Moderacion:** Las preguntas requieren aprobacion del admin. El optimistic debe mostrar "pendiente de aprobacion", no "publicada"
- **Duplicado socket:** El server broadcastea `question:submitted`. Si el admin aprueba rapido, llega `question:approved` mientras el cliente aun tiene el optimista. Dedup por ID necesario. Ya existe dedup en question:approved (useQnA.ts:66)

---

### GAP-4: Publicar post en wall (A5) — PRIORIDAD BAJA

**Comportamiento actual:**
- REST POST, espera respuesta del server
- Server broadcastea `wall:post` via socket a todo el evento
- Awards gamification points

**Comportamiento deseado:**
- Post aparece en feed local inmediatamente con indicador "publicando..."
- Al confirmar server, reemplaza con version final (con ID real, timestamps)

**Archivos a tocar:**
- `eventos-app/hooks/useWall.ts` — agregar onMutate en mutacion de store
- `eventos-app/components/social/PostCard.tsx` — UI de status "publicando"

**Riesgos:**
- **Moderacion:** Si hay moderacion activa, el post podria no publicarse. Status misleading
- **Broadcast duplicado:** El sender recibe `wall:post` via socket despues del optimistic. Necesita dedup por tempId o post ID
- **Fotos/media:** Si el post incluye fotos, el upload binario debe completar primero — no puede ser full optimistic. Solo el texto puede ser optimistic

---

### GAP-5: Update lead (A22) — PRIORIDAD MEDIA

**Comportamiento actual:**
- `PUT /v1/leads/{id}` via REST (useLeads.ts:16-24)
- Solo actualiza cache en onSuccess:19-23. No tiene onMutate
- **Latencia percibida: 400-800ms**

**Comportamiento deseado:**
- Cambio de notas/tier refleja inmediatamente
- Si falla, rollback con toast

**Archivos a tocar:**
- `eventos-app/hooks/useLeads.ts` — agregar onMutate con patch optimista

**Riesgos:**
- Bajo. Es una actualizacion simple de campos editables. El vendedor ve su cambio inmediatamente.

---

## 2. Riesgo de duplicacion en socket

### 2.1 chat:message

| Aspecto | Estado |
|---------|--------|
| Listener | useChat.ts:160-164 |
| Dedup actual | Set `socketMsgIds` (useChat.ts:70) — previene duplicados del mismo broadcast, NO para optimistic reconciliacion |
| Con optimistic UI | **REQUIERE TEMPID** — el cliente inserta mensaje local, luego recibe broadcast con tempId. Debe matchear por tempId y actualizar status, no duplicar |
| Cambio requerido | Cliente genera tempId, server lo preserva. Listener chequea `if (prev.some(m => m.tempId === msg.tempId))` antes de agregar |

### 2.2 wall:post

| Aspecto | Estado |
|---------|--------|
| Listener | useWall.ts:68-74 |
| Dedup actual | Merge directo, no hay check de duplicado |
| Con optimistic UI | **REQUIERE DEDUP** — si el sender publica optimista y luego recibe `wall:post` via socket, aparece doble |
| Cambio requerido | Dedup por post ID o senderId+timestamp window |

### 2.3 wall:comment

| Aspecto | Estado |
|---------|--------|
| Listener | useWall.ts:75-80 |
| Dedup actual | No |
| Con optimistic UI | **YA USA TEMPID** — useWall.ts:174 usa `tempId = -Date.now()`. Pero el listener de `wall:comment` no chequea tempId |
| Cambio requerido | Listener debe ignorar comentarios del sender si ya tiene optimista con mismo contenido |

### 2.4 question:submitted / question:approved

| Aspecto | Estado |
|---------|--------|
| Listener | useQnA.ts:62-73 |
| Dedup actual | Si: `if (prev.find(x => x.id === q.id))` en linea 66 |
| Con optimistic UI | Parcialmente cubierto. El optimista no tiene ID real, necesita reconciliar cuando llega el ID del server |
| Cambio requerido | El onSuccess del REST debe asignar el ID real al optimista, luego el socket dedup por ID funciona |

### 2.5 question:upvoted

| Aspecto | Estado |
|---------|--------|
| Listener | useQnA.ts:87-97 |
| Dedup actual | Reemplaza por ID (no duplica, pisa) |
| Con optimistic UI | **POTENCIAL PARPADEO** — optimista pone upvotes=N+1, broadcast trae upvotes=N+1 (correcto) o N+2 si otro voto llego. El piso es correcto pero puede causar flash |
| Cambio requerido | Preservar `my_upvote` local cuando llega broadcast. Solo actualizar `upvotes` count |

### 2.6 chat:emoji

| Aspecto | Estado |
|---------|--------|
| Listener | useChat.ts:176-180 |
| Dedup actual | No |
| Con optimistic UI + skip-self | No necesita dedup si el server usa `socket.to()` (sender no recibe broadcast) |
| Cambio requerido | Cambiar `io.to()` a `socket.to()` en chat.ts:444 |

---

## 3. Coordinacion REST + Socket invalidation

### 3.1 data:invalidate (hub central)

**Mecanismo actual (useDataInvalidation.ts:236-259):**
- Socket evento `data:invalidate` con entity name
- Debounce 800ms por entity
- Limpia MMKV cache, luego invalida React Query keys

**Entidades que triggean data:invalidate desde Observers:**
- `branding` — EventObserver
- `agenda` — EventSessionObserver, EventRoomObserver
- `announcements` — AnnouncementObserver
- `sponsors` — SponsorObserver
- `modules` — ModuleObserver
- `highlights` — HighlightObserver
- `speakers` — SpeakerObserver

**Impacto en optimistic UI:** Estas entidades son READ-ONLY para el attendee (las modifica el admin desde Filament). No hay conflicto con optimistic UI del attendee.

### 3.2 Acciones REST que disparan broadcasts Socket

| Accion REST | Broadcast Socket | Riesgo de conflicto con optimistic |
|-------------|-----------------|-------------------------------------|
| WallController@store | `wall:post` via `/internal/wall/broadcast` | **MEDIO** — post optimista local + broadcast = duplicado |
| WallController@storeComment | `wall:comment` via `/internal/wall/broadcast` | **MEDIO** — comentario optimista + broadcast = duplicado |
| QuestionController@store | `question:submitted` via `/internal/question/broadcast` | **BAJO** — pregunta va a lista de admin (pending), no aparece en la del attendee hasta aprobada |
| QuestionController@upvote | `question:upvoted` via `/internal/question/broadcast` | **MEDIO** — optimistic pisa + broadcast pisa = parpadeo posible |
| PollController@vote | `poll:updated` via `/internal/poll/broadcast` | **BAJO** — optimistic solo toca myAnswers, broadcast toca contadores. Canales separados |
| NetworkingController@sendRequest | `networking:notify` directo al target | **NINGUNO** — notificacion va al otro usuario, no al sender |
| NetworkingController@respondRequest | `networking:notify` directo al target | **NINGUNO** |

### 3.3 Acciones REST que NO disparan broadcast

| Accion | Broadcast? | Nota |
|--------|-----------|------|
| AgendaController@toggleFavorite | No | Safe para optimistic |
| SponsorController@favorite/unfavorite | No | Safe |
| EventPhotoController@like/unlike | No | Safe |
| WallController@like/unlike | No | Safe |
| RatingController@store | No | Safe |
| LeadController@update | No | Safe |
| RewardController@redeem | No | Safe (y no debe ser optimistic) |

### 3.4 Estrategia para evitar parpadeo

Para las acciones que SI disparan broadcast (Wall post, Wall comment, Q&A upvote):

**Opcion A — Sender-aware broadcast (recomendada):**
- El server incluye `senderId` o `senderAttendeeId` en el payload del broadcast
- El cliente ignora broadcasts donde senderId === mi attendeeId (ya tiene el optimista)
- Implementacion: agregar campo en payload de `/internal/wall/broadcast` y `/internal/question/broadcast`

**Opcion B — TempId reconciliation:**
- El cliente genera tempId, lo envia al server REST
- El server lo incluye en el broadcast socket
- El cliente chequea si ya tiene item con ese tempId y actualiza en vez de duplicar
- Mas robusto pero requiere cambios en ambos lados

**Opcion C — Delay-based dedup:**
- Despues de un onSuccess de mutacion, ignorar el proximo broadcast de ese tipo por 2s
- Fragil, no recomendada

**Recomendacion:** Opcion A para wall (simple senderId check). Opcion B para chat (ya tiene tempId framework).

---

## 4. Mission Control — acciones admin

### 4.1 Acciones admin que necesitan optimistic UI

| Accion | Endpoint | Optimistic? | Justificacion |
|--------|----------|-------------|---------------|
| Moderar pregunta Q&A | `PATCH /questions/{id}/moderate` | Si (Nivel 1) | Admin aprueba/rechaza, debe ver cambio inmediato |
| Toggle chat/Q&A/polls | `PATCH /admin/sessions/{id}/live-config` | Si (Nivel 1) | Toggle debe sentirse instantaneo |
| Start/End/Cancel sesion | `POST /admin/sessions/{id}/start\|end\|cancel` | No (Nivel 3) | Accion critica, irreversible, afecta a todos |
| Delay sesion | `POST /admin/sessions/{id}/delay` | No (Nivel 3) | Cascada compleja, necesita confirmacion server |
| Ban attendee | `POST /admin/attendees/{id}/ban` | Si (Nivel 2) | Admin debe ver confirmacion rapida, pero ban real necesita server |
| Lanzar juego | `POST /admin/games/{id}/launch` | No (Nivel 3) | Accion critica, afecta a todos los attendees |
| Activar poll | `POST /admin/polls/{id}/start` | No (Nivel 3) | Broadcast a todos |
| Cerrar poll | `POST /admin/polls/{id}/close` | Si (Nivel 2) | Admin ve cierre inmediato |
| Delete chat message | `socket.emit('chat:delete')` | Si (Nivel 1) | Mensaje debe desaparecer inmediato |
| Pin/Unpin message | `socket.emit('chat:pin/unpin')` | Si (Nivel 1) | Visual inmediato |

### 4.2 Diferencia con attendee

- **MC es una SPA standalone** (HTML/JS/CSS pre-compilada) servida desde `eventos-backend/public/mission-control/`. NO es Filament, NO es Livewire, NO es React con build pipeline
- **El MC opera desde web browser**, su optimistic UI se maneja dentro de ese bundle compilado (app.js ~132KB)
- **Fuera del scope** de esta auditoria mobile. Si se necesita audit de MC, seria un esfuerzo separado leyendo el bundle JS

---

## 5. Hallazgos cross-cutting

### 5.1 Zero haptic feedback

En los 10 hooks auditados, **cero** llamadas a `Haptics.impactAsync()`. Todas las acciones se sienten "mudas".

### 5.2 Zero retry automatico

El API client (lib/api.ts) solo reintenta en 401 (refresh token). No hay retry para 5xx o timeouts. Si Cloudflare hace failover (30s), todos los requests de ese periodo fallan sin reintentar.

### 5.3 Timeout de 8s

El timeout del API client es 8 segundos (api.ts:93). Para acciones optimistic, esto es irrelevante (el usuario ya ve el cambio). Pero para acciones Nivel 3, 8 segundos de spinner es malo. Considerar reducir a 5s con retry.

### 5.4 InvalidationService NO usada en controllers de acciones de usuario

Verificado: AgendaController@toggleFavorite, WallController@like, SponsorController@favorite — **ninguno** llama InvalidationService. Los broadcasts directos a socket se hacen solo en WallController@store/storeComment y QuestionController@store/upvote. Esto simplifica el analisis: la mayoria de acciones de usuario son fire-and-forget sin broadcast socket.

---

_GAPS-ANALYSIS.md — EventOS | 2026-04-25_
_Fase 3 completada._
