---
name: EventOS — notas técnicas del socket server (Sesión 0.4)
description: Gotchas y decisiones técnicas del servidor Node.js + Socket.IO
type: project
---

Notas de la Sesión 0.4 (2026-03-28).

## Ruta del proyecto

`C:\laragon\www\eventos-socket`

## Versiones instaladas

- socket.io@4.8.3
- @socket.io/redis-adapter@8.3.0
- ioredis@5.10.1
- axios@1.14.0, dotenv@17.3.1
- typescript@6.0.2, ts-node@10.9.2, nodemon@3.1.14

## ioredis v5 — API distinta a redis v4

ioredis v5 usa `new Redis(options)` (import default). No tiene `createClient`.
La conexión es automática al instanciar. Para esperar que esté lista usar el evento `ready`:

```ts
import Redis from 'ioredis';
const client = new Redis({ host, port, db });
await new Promise<void>((resolve, reject) => {
  client.once('ready', resolve);
  client.once('error', reject);
});
```

**Why:** Confundí con el paquete `redis` (npm) que sí tiene `createClient`. Son distintos.

## Redis DB 2 reservada para Socket.IO

- DB 0 → cache Laravel
- DB 1 → queues Laravel (Horizon)
- DB 2 → pub/sub Socket.IO (redis-adapter)

**How to apply:** No cambiar el DB en .env sin revisar que no colisione con los otros usos.

## Auth middleware — depende de Sesión 1.1

El middleware valida el token llamando a `GET /api/v1/auth/me`. Ese endpoint no existe aún (se implementa en Sesión 1.1). Hasta entonces, todos los handshakes devolverán `Invalid or expired token` — es correcto.

**How to apply:** No intentar conectar un cliente real de la app hasta que Sesión 1.1 esté completa.

## Puerto y comandos

- Dev: `npm run dev` (nodemon + ts-node, puerto 3001)
- Prod: `npm run build` → `npm start` (o pm2 con ecosystem.config.js)
- Health: `GET http://localhost:3001/health`

## Estructura de archivos

```
src/
  index.ts    ← servidor principal, bootstrap, handlers
  config.ts   ← config desde .env
  types.ts    ← ServerToClientEvents, ClientToServerEvents, payloads
  auth.ts     ← validateSanctumToken()
  rooms.ts    ← Rooms.event(), Rooms.session(), Rooms.chat()
  chat.ts     ← registerChatHandlers(io, socket, redis) — S1.9a
```

## S1.9a — Chat implementado (2026-03-30)

- `chat.ts` módulo: `registerChatHandlers(io, socket, redis)`
- `join:session` → LRANGE Redis → emit `chat:history`
- `chat:send` → ban check → rate limit (2s) → throttle (50ms) → broadcast `chat:message` → pushHistory → persistMessage (fire-and-forget)
- `chat:emoji` → ban check → `ALLOWED_EMOJIS` → throttle (1s) → broadcast
- `Rooms.chat(sessionId)` = `chat:session:{sessionId}`
- `auth.ts` mapea `attendeeId`, `eventId`, `isBanned` desde /me response

## Backend S1.9a — endpoints y gotchas

- `POST /internal/chat/message` (en `web.php`, no `api.php`) — requiere `X-Internal-Secret` header, sin CSRF
- `GET /api/v1/sessions/{id}/chat/messages` — cursor pagination con `before_id`
- `DELETE /api/v1/admin/chat/messages/{id}` — soft delete, solo admins del evento
- `ChatMessage::sessionRoom(int $sessionId)` — helper estático
- `type` enum: `['text', 'image', 'system']` — usar `'text'` para mensajes normales (NO `'chat'`)
- `EventSession` tenía `start_datetime`/`end_datetime` (no `starts_at`/`ends_at`) — `EventSessionFactory` creada con nombres correctos

## Chat S1.7 — patrones de PROYECTOS/eventos a reutilizar

Referencia: `C:\laragon\www\PROYECTOS\eventos\server\eventos.js`

**Historial de contexto al conectar:**
- Al join a `chat:session:{sessionId}` → emitir `chat:history` con últimos 20 mensajes
- En EventOS usar Redis (no solo RAM) para que sobreviva reinicios: `LRANGE chat:history:{sessionId} 0 19`
- Cada mensaje nuevo: `LPUSH` + `LTRIM` a 20 — O(1)

**Rate limiting por usuario:**
- `lastMsgTs[userId]` — rechazar si han pasado < 2 segundos desde el último mensaje
- Silent: no notificar al usuario, simplemente ignorar

**Throttle global por sala:**
- Máx 20 broadcasts/seg por sala (50ms entre emits)
- Protege el event loop cuando hay picos de mensajes simultáneos

**Emojis animados:**
- Event: `chat:emoji` con `{ emoji, userId, sessionId }`
- Solo broadcast a la sala — sin persistencia en DB
- Throttle: 5 emojis/seg por usuario
- Emojis: ❤️ 👏 🔥 😂 😮 🎉 ⭐ 💯 (configurables por evento)
- Animación CSS float-up en cliente — cero carga servidor

**Persistencia fire-and-forget:**
- INSERT en `chat_messages` sin await — no bloquea el socket
- Si falla el INSERT → el mensaje ya llegó a los clientes, solo se pierde en DB

**Salas por sesión (no por evento):**
- Room: `chat:session:{sessionId}` — una por sesión de agenda
- N sesiones simultáneas = N salas independientes, sin límite
- Usuarios en sesión A no reciben mensajes de sesión B
