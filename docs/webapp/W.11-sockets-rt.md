# W.11 — Sockets RT

> Socket.IO client integrado en toda la webapp. RT en agenda, Q&A, chat, social wall, encuestas, networking, gamification, notificaciones. Long-polling fallback para proxy corporativo.
>
> **Estimacion:** ~6h.
> **Dependencias:** W.0-W.10 (todos los modulos consumiendo eventos).
> **Estado:** Pendiente.

---

## Lectura obligatoria

- `PLAN.md`, `DECISIONS.md`
- `eventos-socket/` repo — eventos existentes (NO inventar)
- App movil: `lib/socket.ts` + memorias `project_socket_*` (4 capas RT invalidation)

---

## Alcance

1. Socket.IO client con 1 sola conexion global (no multiples)
2. Reconnect automatico con exponential backoff
3. Long-polling fallback si WebSocket bloqueado (Bancolombia firewall)
4. Auth: handshake con Bearer token (mismo cookie)
5. Rooms: join/leave por evento, sesion, sponsor segun pantalla activa
6. RT invalidation 4 capas (alineado con app movil):
   - Layer 1: socket event listener
   - Layer 2: TanStack Query invalidation
   - Layer 3: optimistic update merge
   - Layer 4: focus refetch fallback
7. Dedup: socket event no debe duplicar update si ya llego via REST optimistic

---

## Eventos socket existentes (verificar en `eventos-socket/`)

### Sesiones / Streaming
- `session.updated`, `session.cancelled`
- `chat.message.new`, `chat.message.banned`
- `qa.question.new`, `qa.question.upvoted`, `qa.question.approved`
- `poll.activated`, `poll.results.updated`, `poll.closed`

### Social
- `wall.post.new`, `wall.post.banned`
- `wall.comment.new`
- `wall.like.updated`

### Networking
- `connection.request.new`, `connection.accepted`
- `chat.dm.message.new`

### Gamification
- `points.awarded`
- `leaderboard.updated`
- `badge.unlocked`

### Notificaciones
- `notification.new`

### Auth
- `session.revoked` (logout multi-device)

---

## Fase 0 — Setup cliente (~1h) — 0/4

### 0.1 Instalacion — 0/2
- [ ] `pnpm add socket.io-client`
- [ ] Crear `src/lib/socket.ts` con singleton

### 0.2 Handshake — 0/2
- [ ] Conectar con auth handshake: `auth: { token: <bearer> }` (extraer de cookie via API route helper)
- [ ] URL: `wss://socket.eventos.app` con `transports: ['websocket', 'polling']`

---

## Fase 1 — Connection management (~1h) — 0/4

### 1.1 Reconnect — 0/2
- [ ] Exponential backoff: 1s, 2s, 4s, 8s, 16s, 30s max
- [ ] Si reconnect exitoso → re-join rooms + invalidate queries afectadas

### 1.2 Disconnect — 0/2
- [ ] Si disconnect inesperado → toast "Conexion perdida, reconectando..."
- [ ] Si falla 5 veces → mostrar banner "Sin conexion en tiempo real, ciertas funciones limitadas"

---

## Fase 2 — Rooms management (~1h) — 0/3

### 2.1 Auto join/leave — 0/3
- [ ] Hook `useSocketRoom(roomKey)` join al mount, leave al unmount
- [ ] Rooms: `event:{id}`, `session:{id}`, `sponsor:{id}`, `connection:{id}`
- [ ] Aislamiento estricto: no escuchar eventos de rooms no joineadas

---

## Fase 3 — Listeners + invalidation (~2h) — 0/6

### 3.1 Helper generico — 0/2
- [ ] `useSocketListener(event, handler, deps)` envuelve subscribe + unsubscribe
- [ ] Handler tipico: `qc.invalidateQueries(['key'])` o merge optimistic

### 3.2 Por modulo — 0/4
- [ ] Agenda: subscribe `session.updated/cancelled` → invalidate
- [ ] Streaming: subscribe `chat.message.new`, `qa.question.new`, `poll.*` → optimistic merge
- [ ] Social: subscribe `wall.post.new`, `wall.comment.new`, `wall.like.updated` → dedup + merge
- [ ] Notificaciones: subscribe `notification.new` → toast + invalidate

---

## Fase 4 — Dedup logic (~1h) — 0/3

### 4.1 Like dedup — 0/2
- [ ] Cuando user hace like → optimistic update inmediato + POST
- [ ] Backend confirma + broadcast socket event
- [ ] Cliente recibe socket event PROPIO → debe ignorar (skip-self via `from: socket.id` field)
- [ ] Cliente recibe socket event de OTRO → aplica update

### 4.2 Chat dedup — 0/1
- [ ] Mensaje enviado con tempId → al recibir socket event, match por tempId → reemplaza optimistic con real

---

## Fase 5 — Long-polling fallback (~30min) — 0/2

### 5.1 Test bloqueo — 0/1
- [ ] Detectar si WebSocket falla → fallback a polling automatico (Socket.IO lo hace nativo)

### 5.2 Banner — 0/1
- [ ] Si esta en polling → banner sutil "Conexion en modo compatibilidad"

---

## Fase 6 — Tests (~30min) — 0/3

### 6.1 Vitest — 0/1
- [ ] `useSocketListener` subscribe/unsubscribe correcto

### 6.2 Playwright — 0/2
- [ ] Happy path: 2 tabs, accion en tab 1 → tab 2 recibe RT update
- [ ] Edge case: simular disconnect → reconnect + recupera estado

---

## Edge cases

- [ ] Token expirado durante sesion → socket auth falla → redirect login
- [ ] Multiples tabs abiertas → cada tab tiene su socket (Bancolombia hard limit no aplica si <5 conexiones por user)
- [ ] User cambia de pestana → socket sigue activo (no desconectar en blur)
- [ ] Emoji burst (50 likes en 5s) → throttle visual con `requestAnimationFrame`
- [ ] Socket event antes de TanStack Query inicial → buffer o ignore segun caso
- [ ] Network change (WiFi → 4G) → reconnect transparente
- [ ] Socket caido completamente → falls back a polling REST refetchOnFocus

---

## Cierre

- [ ] Tests verde
- [ ] Validado proxy corporativo (simular firewall WebSocket bloqueado → polling)
- [ ] Validado 4 capas RT por modulo
- [ ] Lighthouse no degradado por socket
- [ ] Commit DaVinci + memoria + PENDIENTES.md
