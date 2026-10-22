# Claves de Redis (M0.4.4)

> Catalogo de cada clave que PHP o Node escriben en Redis, con su dueño, formato y vida (ROADMAP-MIGRACION-DOTNET M0.4.4,
> 2026-10-22). Fuente: los 8 archivos con `Redis::` del backend, las llamadas a `Cache::` y `eventos-socket/src/*.ts`.
> Regla de convivencia 2: C# puede **borrar** claves de PHP, nunca **leerlas** (van serializadas en PHP); las que van en
> JSON o texto si se pueden compartir.

## Bases y prefijos (lo que hay que copiar exacto)

| Base | Conexion | Prefijo real de la clave | Quien |
|---|---|---|---|
| 0 | `default` de Laravel | `{REDIS_PREFIX}` (hoy `eventos_database_`, sale de `APP_NAME`) | PHP: contadores, idempotencia, sorteo, colas de Horizon (`{APP_NAME}_horizon:`), sesiones si `SESSION_DRIVER=redis` |
| 1 | `cache` de Laravel | `{REDIS_PREFIX}{CACHE_PREFIX}` (hoy `eventos_database_eventos_`) | PHP: todo lo de `Cache::` |
| 2 | `socket` de Laravel y el socket de Node | **sin prefijo** | Node y PHP comparten: auth del socket, chat, presencia; adaptador de Socket.IO (pub/sub) |
| 15 | solo pruebas de contrato | igual que la 0 | `ModoContratos::redisAparte` (BUG-365) |

Comprobado contra el Redis local (2026-10-22): `Cache::put('dc:stats:1:x')` queda como
`eventos_database_eventos_dc:stats:1:x`. Los prefijos dependen del `.env` de produccion: C# los lee de configuracion, no
los escribe a mano.

## Base 2: compartida entre Node y PHP (JSON o texto, sin prefijo)

| Clave | Formato | Vida | Escribe | Lee / borra | Para que |
|---|---|---|---|---|---|
| `socketauth:t:{sha256 del token}` | JSON `{user, iat}` | 900 s (se renueva al usarla) | PHP `SocketAuthCache::put` (login); Node `auth.ts:89` | Node `auth.ts:37`; PHP borra al revocar el token (`AppServiceProvider:92`) y al banear | Ficha del token para no preguntarle a Laravel en cada conexion |
| `socketauth:u:{userId}` | set de hashes | 1.800 s | PHP `SocketAuthCache:79`; Node `auth.ts:94` | PHP `forgetUser` (baneo, cierre de sesion) | Indice inverso para revocar por persona |
| `chat:history:session:{id}` | lista de JSON (`chat:message`), 20 | sin vida | Node `chat.ts:147` | Node (al entrar, al borrar) | Historial del chat |
| `chat:pinned:session:{id}` | JSON (`chat:pinned`) | 86.400 s | Node `chat.ts:515` | Node (al entrar, al quitar) | Mensaje fijado |
| `chat:count:session:{id}` | entero | sin vida | Node `chat.ts:442` | Node; `/internal/session-metrics` | Contador de mensajes (Mission Control) |
| `display:active:session:{id}` | JSON (`display:project`) | 14.400 s | Node `chat.ts:551` | Node (al entrar, `restored: true`) | Lo que esta proyectado en la pantalla |
| `access:session:{s}:event:{e}` | `'1'` o `'0'` | 300 s | Node `chat.ts:50` | Node | Si una sesion es del evento (SEC-1.1) |
| `session:{id}:viewers` | set de attendeeId | sin vida | Node `chat.ts:298` | Node | Quien esta viendo la sesion |
| `session:{id}:joined` | hash attendeeId → ISO 8601 | sin vida | Node `chat.ts:299` | **PHP** `FlushSessionAttendanceJob` (pasa a `session_attendances`) | Asistencia virtual por sesion |
| `session:{id}:left` | hash attendeeId → ISO 8601 | sin vida | Node `chat.ts:333`, `index.ts:707` | **PHP** `FlushSessionAttendanceJob` | Idem, salida |
| `rl:join:session:{attendeeId}` | entero | 60 s | Node `rateLimit.ts` | Node | 10 entradas a sesiones por minuto |
| `rl:chat:{attendeeId}:{sessionId}` | entero | slow mode (2 s por defecto) | Node | Node | Ritmo del chat |
| `rl:emoji:{attendeeId}:{sessionId}` | entero | 2 s | Node | Node | 3 emojis cada 2 s |
| canal `socket:config` | pub/sub JSON `{tipo, ...}` | — | Node (`/internal/session-config`, `/internal/chat/invalidate-config`) | todos los procesos de Node | Slow mode y palabras bloqueadas en todos los procesos |
| `socket.io#*` | adaptador de Socket.IO | — | `@socket.io/redis-adapter` | Node | Reparto de emisiones entre procesos; desaparece con SignalR (su backplane usa otro canal) |

En memoria de cada proceso Node (no en Redis, se pierde al reiniciar): `attendeeConnections`, `userConnections`, la
cache de palabras bloqueadas y el slow mode por sesion. `/internal/session/viewers` lo arma con los sockets vivos.

## Base 0: PHP (con prefijo)

| Clave | Formato | Vida | Escribe y lee | Para que |
|---|---|---|---|---|
| `checkin:{eventId}:count` | entero | sin vida | `CheckinService:152` (incr), `:187` (si falta, la cuenta de la base) | Contador del kiosko |
| `idempotency:checkin:{clave}` | JSON de la respuesta | 86.400 s | `CheckinController:75/115` | Cabecera `Idempotency-Key` |
| `idempotency:lead:{clave}` | JSON de la respuesta | 86.400 s | `LeadController:47/174` | Idem, leads del stand |
| `game:{id}:pool` | set de attendeeId | 3.600 s | `GameController::join` | Quien toco "unirse" en el sorteo (solo cuenta; el ganador sale de la sala) |
| Horizon (`{APP_NAME}_horizon:*`) y colas (`queues:*`) | de Laravel | — | Horizon | Colas; pasan a Hangfire en la fase 4 |

## Base 1: cache de Laravel (`Cache::`, serializado PHP, C# solo borra)

| Clave | Vida | Se borra en | Para que |
|---|---|---|---|
| `event:{id}:agenda` | — | observers de sesion, sala, track | **Nadie la escribe**: solo se borra (resto de la cache de antes de partir la agenda). C# no la necesita |
| `event:{id}:speakers` | 300 s | observer de speaker | Speakers |
| `event:{id}:documents` | 300 s | `InvalidationService` | Documentos |
| `event:{id}:faqs` | 300 s | `FaqsRelationManager` | FAQ |
| `event:{id}:branding` | — | `EditEventBranding` | **Nadie la escribe**: solo se borra |
| `event:{id}:announcements:{role}` | 30 s | `InvalidationService`, juegos | Anuncios por rol |
| `event:{id}:pages:{role}` | 300 s | — (TTL) | Paginas propias |
| `event:{id}:modules:ver` | sin vida | se **incrementa** al cambiar modulos | Version de la cache de modulos |
| `event:{id}:modules:v{ver}:{role}:{in/out}:{md5 tags}` y `:guest` | 30 s | queda vieja al subir `ver` | Modulos por persona |
| `event:{id}:modules:v{ver}:vendedor_extra` | — | idem | Modulos del vendedor |
| `event:{id}:{recurso}:{role}` | — | `InvalidationService` | Patron generico de invalidacion |
| `event:{id}:room_debounce` | 300 s | — | Segundos de debounce del escaneo en sala (del evento) |
| `public-landing:{id}` | 60 s | — | Landing publica |
| `login-slides:event:{id}` | 300 s | observer de slides | Carrusel del login |
| `dc:stats:{id}:v{ver}:{hash de filtros o none}`, `:curr30`, `:prev30` | 60 s / 300 s | `ExportService::statsKey`; `DataCenterCacheObserver` sube la version (BUG-367 resuelto) | Data Center |
| `dc:stats:{id}:ver` | sin vencimiento | `ExportService::invalidateStats` (INCR en cada cambio del evento y al guardar o borrar metas) | Data Center |
| `dc:exports:pending:{id}` | 600 s | `ExportService` | Exports en cola |
| `dc:master_export:{id}` | 1.800 s | — | Freno del export maestro |
| `recap:{eventId}:{attendeeId}` | `RecapService::CACHE_TTL` | `RecapService` | Recap personal |
| `reg-handoff:{sha256}` | `TTL_MINUTES` | `Cache::pull` (un uso) | Pase del widget a la confirmacion |
| `room:state:{attendeeId}:{roomId}` | sin vida | — | Estado en sala (respaldo de `room_attendee_states`) |
| `room_scan:{attendeeId}:{roomId}:{tipo}` | debounce | — | Doble escaneo |
| `room_scan_lock:{attendeeId}` | lock 3 s | — | Escaneo concurrente |
| `room:totem:{token}` | 3.600 s | — | Id del totem por su token |
| `staff_assigned:{attendeeId}:{roomId}` | — | `StaffCheckinController` | Staff asignado a sala |
| `staff_attendee:{userId}` | — | `StaffCheckinController` | Ficha del staff |
| `game_cooldown:session:{id}` | 120 s en produccion, 10 s fuera | — | Tiempo minimo entre juegos |
| `agenda_reminder_{sessionId}_{n}m` | 600 s | — | No repetir el recordatorio |
| `webhook_rate:{apiKeyId}` | 3.600 s | — | Limite del webhook entrante |
| `invalidate:{eventId}:{entity}` | 1 s | — | Un aviso de invalidacion por segundo por entidad (`InvalidationService:51`) |
| `RateLimiter` de Laravel (`throttle:*`) | por ventana | — | 429 de la API; C# usa su propio limitador |

## Lo que esto le dice a C#

1. Base 2 se comparte tal cual: mismas claves, JSON, sin prefijo. Cuando el hub de SignalR reemplace a Node (fase 3),
   escribe `session:{id}:joined/left` igual mientras `FlushSessionAttendanceJob` siga en PHP.
2. Cuando C# escriba algo que PHP tiene en cache (agenda, speakers, modulos, anuncios), borra la clave de la base 1 con
   el prefijo completo, o sube `event:{id}:modules:ver` (ese es un entero, se puede tocar).
3. `socketauth:*` lo escriben los dos: C# respeta el JSON y la vida de 900 s, y borra al revocar un token.
4. Lo de Horizon y `RateLimiter` no se comparte: cada lado tiene el suyo.

## Canales pub/sub

| Canal | Quien publica | Mensaje | Quien escucha |
|---|---|---|---|
| `eventos:invalidate` (conexion `socket`, sin prefijo) | `InvalidationService::publicarEnRedis` en cada `broadcast` y `sync` (M0.4.5, 2026-10-22) | JSON `{eventId, entity, op}`; `op` = `invalidate` o la operacion de sync | Nadie todavia; la cache de C# (M1.4.4) |
