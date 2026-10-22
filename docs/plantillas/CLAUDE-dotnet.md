# CLAUDE.md — eventos-api-dotnet

> Plantilla. Cuando exista el repo `eventos-api-dotnet` (M1.1.1) este archivo se copia a su raiz como `CLAUDE.md`.
> Version operativa de `APP EVENTOS/docs/BUENAS-PRACTICAS-MIGRACION.md`; si chocan, manda ese documento y se corrige
> este. Decisiones: `APP EVENTOS/docs/decisiones.md`. Plan: `APP EVENTOS/docs/roadmaps/ROADMAP-MIGRACION-DOTNET.md`.

## Que es este repo

El backend de EventOS en C# (.NET 10 LTS) que reemplaza a Laravel ruta por ruta, con los dos vivos sobre la misma base
MySQL y el mismo Redis. La app (Expo) y la webapp (Next) no cambian: C# responde exactamente lo que respondia PHP.
El contrato es lo que dicen los snapshots de `eventos-backend/tests/Contratos/` (247 rutas, 330 respuestas), no lo
que diga el codigo PHP.

## Estructura

- **Proyectos = procesos que se despliegan, no capas:** `Api` (HTTP), `Worker` (Hangfire), `Hub` (SignalR, desde la
  fase 3), `Contracts` (DTOs y politica JSON compartidos entre los tres) y `Tests`. No se agregan proyectos por capa.
- **Dentro de cada proyecto, por modulo** (`Agenda/`, `Social/`, `CheckIn/`...), los 16 del inventario de
  `migracion-dotnet.md` §1.1. Cada modulo trae sus endpoints, servicios, DTOs y tests. No hay carpetas globales
  `Controllers/`, `Services/` ni `Repositories/`.
- **Efectos secundarios:** un `XxxEffects` por entidad (`SessionEffects`, `AttendeeEffects`...), un metodo por efecto,
  llamado desde el servicio. Es donde viven los observers, hooks `booted` y closures del provider de PHP.

## Como portar un endpoint (siempre en este orden)

1. **Leer el snapshot** de la ruta en `eventos-backend/tests/.pest/snapshots/Contratos/`: estado, cabeceras, cuerpo y
   los casos de error. Eso es lo que hay que reproducir.
2. **Leer el PHP y sus comentarios largos** (controlador, servicio, observer, Resource, FormRequest, rate limiter). Se lee
   para entender reglas y efectos (que borra de cache, que emite al socket, que job dispara), no para traducir linea a
   linea. Los comentarios largos explican incidentes reales: la regla que protegen se conserva.
3. **Escribir en el modulo:** endpoint fino, servicio con nombre, DTO con `Select` y `AsNoTracking()`, efectos en su
   `XxxEffects`.
4. **`ORDER BY` explicito** en toda lista, igual al de PHP. Si PHP no lo tiene, se agrega primero en PHP y se regenera el
   snapshot.
5. **Correr el snapshot contra C#:** `CONTRATO_BASE_URL=<c#> ./vendor/bin/pest tests/Contratos --filter=<ruta>` desde
   `eventos-backend`, con la base reiniciada por el endpoint de pruebas. Si escribe: prueba cruzada (escribe C#, lee
   PHP, mismo snapshot).
6. **Medir en el MSI** con el k6 de agosto, PHP contra C# en la misma maquina, y anotar el numero en el roadmap junto a
   la tarea. Recien ahi la ruta puede cruzar (`cruzar.ps1`; `descruzar.ps1` probado).

## Prohibido

- `.Result` y `.Wait()` (el analizador de async falla el build).
- `DateTime.Now` / `DateTime.UtcNow` para datos: fechas con `DateTimeOffset` en el offset de la zona de la app
  (`APP_TIMEZONE`, hoy Bogota -05:00), sin fracciones. Las 22 columnas que en Laravel tienen `useCurrent()` se escriben
  explicitas.
- Abstracciones nuevas: repositorios, unit of work, mediadores, capas, interfaces con una sola implementacion.
- **Mejorar el contrato:** ni renombrar campos, ni quitar duplicados (`session_id` + `sessionId`), ni corregir un texto
  en ingles (`Too Many Attempts.`), ni cambiar un codigo de estado. Si el contrato esta mal, se anota y lo decide Kamilo;
  el cambio se hace primero en PHP con su snapshot.
- Migraciones de EF Core antes de la fase 5: el esquema lo manda Laravel; una columna nueva = migracion Laravel +
  `scaffold.ps1`.
- Tocar Next o Expo, salvo la capa `realtime` (fase 3) y el admin nuevo (fase 2).
- Sobreescribir la politica JSON en un endpoint.
- Leer claves de Redis de PHP (van serializadas en PHP); solo borrarlas por nombre.
- "Aprovecho y tambien": un bug que aparece se registra y se arregla solo si bloquea o si Kamilo lo decide.

## Obligatorio

- Un solo `ConnectionMultiplexer` (singleton) y `IHttpClientFactory` para todo cliente HTTP.
- Jobs idempotentes; unicidad con el filtro propio de `Worker` (no `DisableConcurrentExecution`).
- `X-Request-Id` en logs y Sentry; `X-Backend: dotnet` en toda respuesta.
- Versiones fijas en los `.csproj`; `dotnet list package --vulnerable` limpio.
- Una tarea del roadmap = un commit. Un bloque por sesion.

## Politica JSON

Global, en `Contracts/`: `JsonNamingPolicy.SnakeCaseLower`, nulos incluidos, campos condicionales (los `when()` de los
Resources de Laravel) omitidos, fechas con offset. **Excepciones por campo en una lista cerrada** (`Contracts/ExcepcionesJson.cs`);
nada entra sin decision en `decisiones.md`. Lista al 2026-10-22, sacada de los snapshots y del codigo PHP:

| # | Campo | Donde viaja | Origen en PHP |
|---|---|---|---|
| 1 | `eventId` | API: encuestas (junto a `event_id`); socket | `PollController.php:400`; `InvalidationService`, `WallController`, `SessionConfigController` |
| 2 | `sessionId` | API: encuestas (junto a `session_id`); push; socket | `PollController.php:398`; `SendAgendaRemindersJob:125`, `SendSessionChangedNotificationJob:57`; `QuestionController:214`, `EventSessionObserver:108` |
| 3 | `attendeeId` | socket | `BanController:187`, `InvalidationService:181`, `StaffNotificationService` |
| 4 | `targetAttendeeId` | socket | `NetworkingController:708`, `StaffCheckinController:553`, `InvalidationService:207`, `StaffNotificationService:88` |
| 5 | `fromName` | socket | `NetworkingController:710` |
| 6 | `fromAttendeeId` | socket | `NetworkingController:711` |
| 7 | `streamUrl` | socket | `EventSessionObserver:110` |
| 8 | `invitationId` | socket | `StaffNotificationService` |
| 9 | `sponsorId` | socket | `StaffNotificationService` |
| 10 | `sponsorName` | socket | `StaffNotificationService` |
| 11 | `sponsorLogo` | socket | `StaffNotificationService` |
| 12 | `sponsorTier` | socket | `StaffNotificationService` |
| 13 | `inviterName` | socket | `StaffNotificationService` |
| 14 | `attendeeName` | socket | `StaffNotificationService` |
| 15 | `expiresAt` | socket | `StaffNotificationService` |

- En la API HTTP solo existen 1 y 2. Los del socket son lo que PHP le manda a Node; los nombres que llegan al cliente se
  confirman en M0.4.1 contra `eventos-socket/src/types.ts` y la lista se corrige ahi.
- No son excepciones (no se copian): los campos de botones de Filament que hoy salen en `GET data-center/{id}/exports`
  (`dispatchDirection`, `isOutlined`, `shouldClose`...) son un filtrado interno del panel; el admin nuevo define esa
  respuesta. Tampoco `storeUrlIos`, `expiresIn`, `logoUrl`: son datos de vistas Blade, no JSON.

## Formas de Laravel que se copian tal cual

- 401 `{"message": "Unauthenticated."}`; 422 `{message, errors}` con los textos de `lang/es`; 429 `Too Many Attempts.`
  con `Retry-After`; 404 de modelo `No query results for model [App\Models\X] N` (sale en produccion: se copia el texto).
- Paginacion: `{data, meta: {current_page, last_page, total}}` donde PHP la arma a mano; revisar el snapshot de cada ruta.
- Codigos de error de negocio en `code` (`FORBIDDEN`, `QR_INVALID`, `DUPLICATE_LEAD`, `staff_uses_panel`...).

## Comandos

- Contratos contra PHP: `./vendor/bin/pest tests/Contratos` (en `eventos-backend`, ~90 s).
- Contratos contra C#: `CONTRATO_BASE_URL=http://localhost:<puerto> ./vendor/bin/pest tests/Contratos`.
- Regenerar snapshots: `--update-snapshots` SOLO sin `--filter` (con filtro borra todos y regenera solo los filtrados);
  para uno solo, borrar su `.snap` y correr.
