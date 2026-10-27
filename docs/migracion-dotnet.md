# Migracion del backend de EventOS: Laravel a ASP.NET Core

Analisis de solo lectura hecho el 2026-10-21 sobre los repos `eventos-backend` (Laravel 11.31, PHP 8.3), `eventos-socket` (Node, Socket.IO 4), `eventos-web` (Next 16), `eventos-app` (Expo SDK 55) y `eventos-kiosko` (Vite). No se cambio nada. Todas las cifras salen de contar en el codigo (`php artisan route:list --json`, `find`, `grep`); donde una cifra es estimada se dice.

Premisas del encargo: los frontends se quedan; MySQL, Redis y R2 se quedan; Ubuntu en DigitalOcean (Sao Paulo), idealmente Docker; Socket.IO pasa a SignalR con Redis como backplane; una sola persona con ayuda de IA; migracion gradual detras de un reverse proxy (strangler).

---

## 0. Resumen

| Que | Cifra |
|---|---|
| Lineas PHP en `app/` | 48.939 (15.439 son el admin Filament) |
| Rutas registradas | 412: 247 endpoints de API, 117 del admin Filament, 16 web sueltas, el resto Horizon, Telescope y Livewire |
| Tablas MySQL | 105 (164 migraciones), 84 modelos Eloquent |
| Jobs en cola | 72 (50 son exportaciones), 6 colas, 3 supervisores Horizon |
| Observers y hooks de modelo | 14 observers, 6 hooks `booted`, 3 listeners anonimos en el provider |
| Middlewares propios | 7, mas 12 limitadores de tasa con nombre |
| Tareas programadas | 16 |
| Correos | 20 mailables, 16 tipos editables por evento, SMTP por organizacion |
| Servicio Node | 1.950 lineas TS, 17 endpoints internos HTTP, 10 eventos cliente a servidor, 34 eventos servidor a cliente |
| Clientes del socket | 8 (webapp, Expo, kiosko y cinco paginas estaticas servidas por Laravel) |
| Tests | 111 archivos Pest, unas 1.100 pruebas, 21.528 lineas |

**Veredicto corto.** La API publica es portable y esta bien cubierta por tests. Lo que encarece la migracion no son las 247 rutas: son (1) la logica repartida en observers, jobs y closures que no se ve desde las rutas, (2) el admin Filament, que es PHP puro y no tiene equivalente en .NET, y (3) el cambio de protocolo de tiempo real, que toca ocho clientes y obliga a correr los dos servidores en paralelo mientras la app movil se actualiza en las tiendas.

**Estimacion.** Sin reescribir el admin: 23 semanas en el escenario optimista, 43 en el realista. Reescribiendo el admin: 35 a 39 optimista, 61 a 69 realista. El detalle y los supuestos estan en la seccion 5.

**Una advertencia antes de empezar.** El evento de 5.000 personas es el 6 de diciembre de 2026 y el backend actual ya esta medido a 300 personas por maquina con cero errores. La migracion no entrega ninguna funcion visible al cliente y durante el doble despliegue duplica la superficie de fallo. Si se hace, conviene arrancar despues del evento, y arrancar por la fase 0 (preparacion dentro de Laravel), que vale la pena aunque la migracion nunca se complete.

---

## 1. Inventario

### 1.1 Endpoints de la API por modulo

247 rutas bajo `api/v1` (sin contar HEAD). Agrupadas por lo que hacen, no por archivo:

| Modulo | Endpoints | Controladores | Auth |
|---|---|---|---|
| Autenticacion y cuenta | 18 | AuthController (14), ExpoToken, WebPushSubscription | 10 publicos con limitadores propios, 8 con token |
| Publico pre-login (landing, widget, slides, presets, campos) | 12 | PublicEvent, PublicLanding, PublicRegistration, PublicHandoff, Preset, RegistrationField, Onboarding | sin token, `throttle:public_read` o `public_register` |
| Perfil y lo mio (`/me/*`, `/events/{id}/mine`) | 20 | Profile, RegistrationField, ChangeEmail, Onboarding, LoMio, Gamification | token, sin `check.approval` en onboarding |
| Contenido del evento (agenda, speakers, sponsors, documentos, FAQ, paginas, highlights, anuncios, tracks, branding, modulos, ahora) | 31 | Agenda, Speaker, Sponsor, Document, DocumentZip, Faq, CustomPage, Highlight, Announcement, SessionTrack, EventBranding, Module, HappeningNow | token |
| Social (muro, fotos, historias, calificaciones, preguntas) | 29 | Wall, EventPhoto, AttendeeStory, Rating, SpeakerRating, Question | token |
| Encuestas y juegos en vivo | 22 | PollController (12), GameController (14, parte admin) | token; 10 son de admin |
| Gamificacion, pasaporte y premios | 13 | Gamification, Passport, Reward | token |
| Networking | 11 | NetworkingController | token |
| Check-in de evento (QR propio) | 3 | CheckinController | token |
| Salas, totems y staff de puerta | 25 | RoomCheckinController (13), StaffCheckinController (10), 2 de SessionStats | 4 rutas de totem con token de totem, resto Sanctum |
| Stand, leads e invitaciones de staff | 23 | StandController (13), LeadController (5), 3 de staff-invitations, 2 de SponsorService | token |
| Admin por API (Mission Control) | 37 | Ban, Attendee, Notification, SessionConfig, Upload, Chat, Game, Poll | token con rol admin |
| Data Center | 19 | DataCenterController | cookie de sesion via `auth:sanctum` |
| Event Pulse | 9 | PulseController | `pulse_token` por query (`check.pulse`) |
| Streaming y sesion (chat REST, stats, live-config, viewers, export) | 8 | Chat, SessionStats, SessionConfig | token |
| Webhooks entrantes | 2 | WebhookInboundController | `X-Webhook-Key` |
| Soporte, tracking, salud | 5 | Support, Track, Health | mixto |

Reparto de middlewares sobre esas 247 rutas: 191 pasan por `AuthenticateApi:sanctum`, 190 por `CheckBan`, 172 por `CheckApproval`, 182 por `throttle:api`. 24 usan `auth:sanctum` a secas (Data Center y rewards, que aceptan cookie de sesion ademas de bearer).

La lista completa ruta por ruta (metodo, URI, controlador, auth, limitador) esta en el apendice A.

### 1.2 Rutas fuera de la API

**16 rutas web sueltas en `routes/web.php` (561 lineas de closures con logica adentro):**

- Paginas servidas: `/data-center/{any}` (SPA compilada en `public/data-center`, protegida por sesion web + 2FA), `/dc-embed/{token}`, `/display/polls/{id}`, `/display/session/{id}`, `/monitor/{sessionId}`, `/join/{token}`, `/join-team/{token}`, `/r/{code}` (verificacion de recap), `/password/reset/{token}`, `/auth/verify-email/{id}/{hash}`.
- Endpoints internos que usa el servicio Node (protegidos por `X-Internal-Secret`): `POST /internal/chat/message`, `POST /internal/chat/messages-batch`, `POST /internal/chat/delete`, `POST /internal/validate-session-access`, `GET /internal/chat/blocked-words`.
- Rutas de totem sin CSRF bajo `api/v1/rooms/*` (scan, scan/batch, ping, manifest) que viven en web.php y no en api.php.

**117 rutas del admin Filament:** 45 resources, 10 clusters (Agenda, Anuncios, Asistentes, Emails, Entrada, Gamificacion, Identidad, Social, Sponsors, Webhooks), 6 paginas propias (Dashboard, AccountSecurity, LandingWidget, LiveGameResults, ModulosPanel, PrizeRedemptions), login propio con 2FA (TwoFactorChallenge, TwoFactorSetup), un componente Livewire (EventContextSwitcher), 40 vistas Blade. 15.439 lineas.

**Paginas estaticas en `public/` que son clientes del socket:** `mission-control/`, `event-pulse/` (26 archivos), `display/`, `chat-monitor.html`, `attendance-check.html`. No pasan por Next ni por Expo.

### 1.3 Jobs y colas

72 clases en `app/Jobs`. Conexion de cola: Redis con Horizon. Tres supervisores:

| Supervisor | Colas | Procesos en produccion | Timeout |
|---|---|---|---|
| supervisor-1 | `default` | hasta 10, balance auto | 60 s |
| supervisor-mail | `mail-priority`, `mail` | 3, sin balanceo, `tries 0` | 60 s |
| supervisor-heavy | `exports`, `documents`, `pdf` | 3 | 900 s |

Ademas hay un worker Docker separado (`docker/Dockerfile.worker`) que solo consume `exports` leyendo de la replica MySQL y escribiendo en R2.

Jobs por familia:

- **Exportaciones (50):** 45 `Jobs/Exports/*` heredan de `BaseExportJob` (CSV con `fputcsv` o XLSX con maatwebsite/excel, suben a disco publico o R2, mandan correo opcional), mas `ExportMasterZipJob` (ZIP con todo), `ExportPollResponsesJob`, `ExportRoomAttendanceJob`, `ExportSessionStatsJob`.
- **Correo (3):** `SendEmailJob` (generico, elige mailer por organizacion, middleware `RateLimited('mail')`, cola `mail-priority` o `mail` segun si el correo es esencial, `retryUntil`), `SendRecapReadyEmailJob`, `CsvImportCompletedMail` lo dispara la importacion.
- **Push (4):** `SendPushToAttendeeJob` (Expo + Web Push, un log por asistente), `SendWebPushJob` (VAPID), `SendSilentPushJob` (push silencioso al cambiar modulos), `CheckExpoReceiptsJob`.
- **Recordatorios y notificaciones (6):** `DispatchScheduledNotificationsJob`, `SendEventRemindersJob`, `SendSessionRemindersJob`, `SendAgendaRemindersJob`, `SendSessionChangedNotificationJob` (en C# desde M2.3.2c2: `AvisoDeCambioDeHorario` en Hangfire; ya no existe en PHP), `ExpireAttendeeBansJob`.
- **Asistencia y salas (4):** `FlushSessionAttendanceJob` (lee los sets `session:{id}:viewers/joined/left` que escribe Node en Redis y los vuelca a MySQL), `AwardSessionAttendancePointsJob`, `AutoCheckoutEndOfDayJob`, `SmartAutoCheckoutJob`.
- **Juegos y premios (4):** `ProcessSpinRewardsJob`, `ProcessTriviaRewardsJob`, `ExpireRedemptionsJob`, `DispatchWebhookJob` (3 intentos con backoff 60/300/1800 s, respeta `Retry-After`).
- **Documentos y recap (4):** `RegenerateDocumentsZipJob` (unico, `ShouldBeUnique`, cola `documents`), `GenerateRecapImageJob` (cola `pdf`, Browsershot), `GenerateRecapsForEventJob`, `RecapReady`.

### 1.4 Eventos, listeners, observers y hooks

No hay clases en `app/Events` ni `app/Listeners`. Toda la reaccion a cambios vive en observers y hooks, que es justamente la logica que no se ve desde las rutas:

| Observer | Modelos | Que hace ademas de guardar |
|---|---|---|
| EventSessionObserver | EventSession | borra cache de agenda, emite `data:sync` con la sesion, `data:invalidate agenda`; si cambia la hora dispara `SendSessionChangedNotificationJob` (desde M2.3.2c2 lo hace C#, `AvisoDeCambioDeHorario`); si cambia `interactive_mode` o `stream_url` hace POST al socket `/internal/session/mode_changed`; si el admin edita `end_datetime` limpia `adjusted_end_at` |
| AnnouncementObserver | Announcement | `data:sync` + `data:invalidate announcements`, borra cache por rol |
| DocumentObserver | Document | dispara `RegenerateDocumentsZipJob` y `data:invalidate documents` en created, updated, deleted, restored y forceDeleted |
| EventObserver | Event | borra cache de branding y modulos por rol, `data:invalidate branding`, dispara `GenerateRecapsForEventJob` cuando el evento termina |
| ModuleObserver | Module | invalida modulos y manda `SendSilentPushJob` a toda la app |
| Speaker, Sponsor, Highlight, SessionTrack, EventRoom, EventLoginSlide | uno cada uno | cache + `data:invalidate` de su entidad |
| ContentObserver | EventSession, Speaker, Document, Announcement, CustomPage | solo limpieza de cache por rol |
| DataCenterCacheObserver | Attendee, EventSession, Sponsor, Lead, AttendeeBan, AttendeeRoleChange, WallPost, EventPhoto, LiveGame | borra `dc:stats:{event}:*` |
| AttendeeWebhookObserver | Attendee | emite webhooks `attendee.registered`, `approved`, `checked_in`, `updated`, `cancelled` segun que campo cambio |

Hooks `booted` en modelos: ChatMessage (creating), PushSubscription (saving), RoomTotem (creating, genera token), User (updated, revoca cosas si pierde acceso), WebhookApiKey y WebhookEndpoint (creating, generan secreto).

Listeners anonimos en `AppServiceProvider::boot`: al `Logout` borra la fila de `staff_sessions`; al borrarse un `PersonalAccessToken` borra `socketauth:t:{sha256}` en Redis; al crearse un `AttendeeBan` olvida al usuario en la cache de auth del socket; y `Sanctum::authenticateAccessTokensUsing` rechaza y borra tokens de usuarios con acceso al panel (el staff no usa la API con token).

### 1.5 Middlewares y limitadores

Propios: `AuthenticateApi` (401 JSON sin redirect), `CheckBan` (403 con `{message, ban:{reason, expires_at}}`), `CheckApproval` (403 con `{message, approval:{status:'pending'}}`), `CheckPulseToken`, `RequireTwoFactor` (panel y Data Center, registra `StaffSession`), `SecurityHeaders` (CSP solo en `api/*`), `SetFilamentEventContext`. Globales: `statefulApi()` de Sanctum (cookies para el Data Center), CSRF excepto `api/*`.

Limitadores con nombre, todos con claves pensadas para un recinto con una sola IP publica: `login` (5/min por correo), `entry_check` (10/min por correo + techo por IP), `cuenta_correo`, `cuenta_enlace`, `public_read`, `public_register` (5/h por correo, 600/h por IP), `change_email`, `magic_link` (3/h por correo), `magic_verify` (10/h por token), `api` (300/min por usuario), `upload` (10/min), `mail` (por segundo, para el job de correo). Ademas `AuthController::login` cuenta solo fallos con `config('auth.puerta')`. `TrustProxies` se configura desde el provider porque en `bootstrap/app.php` el `env()` se ignoraba con config cacheada.

### 1.6 Policies

Cero clases Policy. La autorizacion es por rol dentro de cada controlador: `App\Support\Roles` define los roles de plataforma (spatie/laravel-permission: `super_admin`, `org_admin`, `event_admin`, `moderator`, `staff_checkin`) y los roles de asistente en la columna `attendees.role` (`attendee`, `vendedor`, `admin`, `admin_content`, `admin_notifs`, `admin_analytics`). El socket decide con `role.startsWith('admin')`.

### 1.7 Tareas programadas (16)

| Frecuencia | Tarea |
|---|---|
| cada minuto | DispatchScheduledNotifications, SendAgendaReminders, ExpireRedemptions, FlushSessionAttendance |
| cada 5 min | CheckExpoReceipts, SendSessionReminders, `dc:scheduled-exports` (closure que lee `dc_scheduled_exports`, despacha el job segun `EXPORT_MAP` y recalcula `next_run_at`) |
| cada 15 min | SmartAutoCheckout |
| cada hora | ExpireAttendeeBans, `stories:cleanup` |
| diarias | SendEventReminders 08:00, AutoCheckoutEndOfDay 23:30, `webhook:prune-logs` 02:00, poda de `staff_security_events` 02:30, `exports:cleanup` 03:00 |

Todas con `onOneServer()` y casi todas con `withoutOverlapping()`.

### 1.8 Correos y notificaciones

20 mailables heredan de `BaseEventosMail`, que resuelve una `EmailTemplate` por evento y tipo (16 tipos en `EmailCatalog`: invitation, welcome, welcome_access, registration_pending, registration_approval, magic_link, session_reminder, contact_request, sponsor_lead, recap_ready, cancelled, password_reset, email_verification, csv_import_completed, banned, staff_account_locked), reemplaza `{{placeholders}}` con `str_replace`, envuelve en `EmailLayout` y registra `EmailLog` al enviar o fallar. `EmailService::mailerFor($orgId)` construye un `EsmtpTransport` con las credenciales de `organization_email_settings`; si no hay, usa el mailer por defecto (Resend o SMTP). 2 notifications de Laravel (reset y verificacion personalizadas).

### 1.9 Integraciones externas

| Integracion | Donde | Detalle |
|---|---|---|
| Cloudflare R2 | StorageService | driver S3 de Flysystem, con fallback a disco publico |
| Expo Push | SendPushToAttendeeJob, CheckExpoReceiptsJob | `exp.host/--/api/v2/push/send` y `getReceipts` |
| Web Push | SendWebPushJob | minishlink/web-push con VAPID |
| Cloudflare Turnstile | TurnstileVerifier | `siteverify` en registro publico |
| Resend / SMTP por organizacion | EmailService | ver 1.8 |
| Sentry | sentry-laravel | trazas y errores |
| Chrome headless | RecapImageRenderer | spatie/browsershot (Puppeteer) para la imagen del recap |
| Webhooks salientes | WebhookDispatchService, DispatchWebhookJob | HMAC SHA-256 del JSON con el secreto del endpoint, cabeceras `X-EventOS-Signature` y `X-EventOS-Event`, log en `webhook_logs` |
| Webhooks entrantes | WebhookInboundController | `X-Webhook-Key` contra `webhook_api_keys`, check-in individual y por lote |
| Webapp Next | InvalidationService | `POST {WEBAPP_URL}/api/internal/invalidate` con `WEBAPP_INTERNAL_SECRET` para las entidades del marco |
| Servicio Node | InvalidationService, GameService, PollController, observers | 17 endpoints `/internal/*` con `X-Internal-Secret` |
| Google2FA | TwoFactorService | TOTP del staff, codigos de recuperacion, dispositivos de confianza |

Secretos criptograficos que hay que portar byte a byte: `APP_QR_SECRET` (HMAC del QR estatico `attendee|event|timestamp` y del dinamico `attendee|event|ventana` recortado a 32), `APP_KEY` (HMAC del recap, `encrypted` casts de 2FA, cookies de sesion), secretos por endpoint de webhook, tokens de magic link y handoff guardados como SHA-256.

### 1.10 Que hace el servicio de Node

`eventos-socket`, 1.950 lineas de TypeScript, PM2 en cluster (un proceso por nucleo), adaptador Redis en DB 2, solo transporte WebSocket (sin polling, decision medida el 2026-08-01).

**Autenticacion del handshake.** Lee `auth.token` o `Authorization`. Si empieza por `ep_` valida contra `GET /api/v1/pulse/validate`. Si no, busca `socketauth:t:{sha256(parte despues del |)}` en Redis (ficha que deja `SocketAuthCache` al emitir el token, TTL deslizante 15 min, tope 24 h), y solo si no esta llama a `GET /api/v1/auth/me`. Guarda en memoria 60 s. Maximo 5 conexiones por usuario.

**Salas.** `event:{id}`, `pulse:{id}`, `session:{id}`, `chat:session:{id}`, `display:session:{id}`. `join:event` valida que el evento sea el del token; `join:session` valida acceso con `POST /internal/validate-session-access` (cache 5 min, fail-closed), manda historial de chat (Redis `chat:history:session:{id}`, ultimos 20), mensaje anclado, contador y estado de proyeccion; registra asistencia en `session:{id}:viewers/joined/left` que Laravel vuelca cada minuto.

**Chat.** Rate limit en Redis (1 msg por 2 s o modo lento por sesion, 3 emojis por 2 s, 10 joins por minuto), palabras bloqueadas por evento (cache 5 min, se invalida por pub/sub `socket:config`), pausa, throttle de 50 ms por sala, persistencia a Laravel por lotes de 200 ms con keep-alive, borrado y anclado solo admin.

**17 endpoints internos HTTP** que Laravel llama: checkin, poll/broadcast, question/broadcast, session/mode_changed, wall/broadcast, data/sync, data/invalidate, emit-to-user, chat/invalidate-config, ban/enforce, networking/notify, staff/notify, broadcast (generico, usado para `game:*`, `attendance:*`, `session:started/ended/cancelled`, `agenda:*`), session-metrics, session/viewers, session-config.

**Eventos.** 10 de cliente a servidor (`chat:send`, `chat:emoji`, `chat:delete`, `chat:pin`, `chat:unpin`, `join:event`, `join:session`, `leave:session`, `display:project`, `display:stop`) y 34 tipados de servidor a cliente mas los que pasan por `/internal/broadcast`. Audiencia por sesion con debounce de 500 ms; `pulse:active_users` cada 10 s.

**Quien lo consume** (eventos distintos que escucha cada cliente): webapp 23, Expo 26, Mission Control 20, chat-monitor 11, Event Pulse 6, display 6, kiosko 1, attendance-check 1.

---

## 2. Complejidad por modulo

| Modulo | Complejidad | Logica escondida que no se ve desde las rutas |
|---|---|---|
| Publico pre-login | Baja | `Cache::remember` por slug y por rol; `PublicLanding` arma speakers, agenda, sponsors y FAQ en una llamada; el handoff es un pase de un solo uso con hash en Redis |
| Contenido del evento | Baja a media | 12 claves de cache por evento y rol que invalidan los observers; `LoMioService` compone 6 piezas con puntos; `publicEnd()` de la sesion mezcla `end_datetime` y `adjusted_end_at`; `.ics` generado a mano |
| Perfil y campos de registro | Media | campos dinamicos por evento (`registration_fields` + `registration_field_values`), `CheckApproval` se salta en onboarding, cambio de correo manda correo y tiene limitador propio |
| Autenticacion | Alta | 14 endpoints con `check-email` y `verify-identity` como puerta de entrada; magic link SHA-256 de un solo uso 15 min; `refresh` rota el token; `SocketAuthCache` escribe la ficha en Redis al emitir; expiracion 7 dias; invitaciones de stand enlazadas al activar; conteo de fallos por cuenta en config `auth.puerta`; rechazo de tokens a usuarios con panel |
| Registro publico | Media | Turnstile, limitador por correo, un solo correo `welcome_access`, prueba de carrera (`RegistrationRaceTest`) |
| Social (muro, fotos, historias, Q&A, ratings) | Media | cada like o post otorga puntos via `PointsService::tryAward` con dedupe por referencia y tope diario; `LikesMilestoneService`; moderacion de preguntas emite al socket; historias caducan por comando |
| Encuestas en vivo | Media a alta | transacciones al crear y votar, aprobacion de votos por lote, `my_answers` por usuario, broadcast `poll:*`, encuesta post evento |
| Juegos en vivo (trivia, ruleta, jackpot) | Alta | 821 lineas en un controlador; pool de elegibles en Redis (`sadd`, TTL 1 h), cooldown por sesion, `weightedRandom`, premios por job, auto post en el muro, anuncio al ganador y limpieza de cache por rol |
| Gamificacion y premios | Alta | `PointsService` con config por evento y rol; canje con transaccion y `lockForUpdate`, token HMAC de canje, expiracion por minuto; Golden Ticket; pasaporte de sellos |
| Networking | Media | 717 lineas; directorio con bloqueos, solicitudes con notificacion dirigida por socket y correo, sugerencias por intereses del onboarding |
| Check-in de evento | Media | QR estatico HMAC guardado y QR dinamico por ventana de tiempo; `processCheckin` en transaccion; broadcast a kiosko; webhook `attendee.checked_in` sale por observer |
| Salas, totems y staff de puerta | Alta | `RoomCheckinService::processScan`: estado en Redis con fallback a DB, toggle entrada/salida, debounce configurable por evento, lock no bloqueante, auto-salida de otra sala, todo en transaccion; totem autenticado por token cacheado; asignaciones de staff con aceptacion; verificaciones de asistencia con ventana; dos jobs de auto-checkout |
| Stand, leads, staff de sponsor | Alta | 884 lineas; invitaciones con token, transferencia de propiedad, resolucion de QR, estadisticas por stand, `LeadEdit` como historial, exportacion CSV, notificaciones dirigidas por socket y correo |
| Admin por API (Mission Control) | Alta | `SessionConfigController` cambia estado de sesion (start, end, cancel, delay con `adjusted_end_at`) y avisa al socket y a los recordatorios; baneos con `ban:enforced` dirigido y expiracion; notificaciones programadas; uploads a R2 |
| Streaming y chat | Media | el chat vive en Node; Laravel solo persiste por lote (`/internal/chat/*`), lista historial REST y borra; `SessionStatsService` calcula engagement por asistente y linea de tiempo |
| Data Center | Alta | 441 lineas de controlador + 722 de `ExportService`; cache `dc:stats`, objetivos, embeds por token UUID, exportaciones programadas, 50 jobs de exportacion con un worker propio sobre la replica |
| Event Pulse | Media | token `ep_` por evento, 8 agregaciones de lectura, y el socket le manda audiencia y usuarios activos |
| Webhooks | Media | observer sobre Attendee decide el evento por campos cambiados; firma HMAC; reintentos con `Retry-After`; entrante por lote con transaccion |
| Correos | Media a alta | plantillas por evento con placeholders, SMTP por organizacion, dos colas con prioridad, limitador por segundo compartido entre workers, log por correo, `retryUntil` |
| Push | Media | Expo + Web Push detras de un solo job por asistente, recibos de Expo, preferencias por tipo |
| Recap | Media | imagen con Chrome headless, verificacion por codigo HMAC con `APP_KEY`, correo al terminar |
| Seguridad del staff (2FA, bloqueo, dispositivos, sesiones) | Alta | 327 lineas de `TwoFactorService` + 4 clases de soporte; secreto cifrado con `APP_KEY`; solo aplica al panel y al Data Center |
| Admin Filament | Muy alta | 15.439 lineas, 45 resources, wizard de primer ingreso, importacion CSV de asistentes, contexto de evento en sesion, 14 despachos de jobs y correos desde acciones del panel |

---

## 3. Equivalentes en .NET

Version recomendada: **.NET 10 (LTS, salio en noviembre de 2025)**. .NET 8 termina soporte el 10 de noviembre de 2026, antes de que esta migracion pudiera terminar.

| Pieza Laravel | Equivalente .NET | Notas |
|---|---|---|
| Eloquent, 84 modelos, 105 tablas | EF Core 10 con Pomelo.EntityFrameworkCore.MySql, scaffold database-first | No crear migraciones EF mientras Laravel sea dueño del esquema. Casts `array` (38) se mapean a `JsonDocument` o `string` con conversor; `datetime` (84) requiere conversor de zona (ver 4.3) |
| Sanctum (bearer) | `AuthenticationHandler` propio que lee `personal_access_tokens` | Compatible con los tokens existentes sin migrar nada (ver 4.2). Identity o JWT solo despues de apagar Laravel, si se quiere |
| Sanctum stateful (cookie del Data Center) | Sin equivalente directo | La sesion de Laravel esta cifrada con `APP_KEY` y serializada en PHP. El Data Center debe pasar a bearer o quedarse en Laravel hasta el final |
| spatie/laravel-permission | Claims de rol en el principal + `[Authorize(Roles)]` leyendo `model_has_roles` | Mismas tablas, solo lectura |
| Form Requests (14) + validacion en controlador (465 `response()->json`) | FluentValidation o DataAnnotations con un filtro que devuelva la forma de Laravel | Hay que replicar `{message, errors:{campo:[...]}}` y los mensajes en español de `lang/es` |
| API Resources (14) | DTOs `record` + `System.Text.Json` | Ver politica de nombres en 4.3 |
| Horizon + colas Redis | Hangfire con almacenamiento Redis (Hangfire.Pro.Redis es de pago; Hangfire.Redis.StackExchange es comunitario) o colas propias sobre Redis Streams con `BackgroundService` | Hangfire da panel, reintentos, `DisableConcurrentExecution` y jobs recurrentes (reemplaza tambien al scheduler). Las 6 colas pasan a 3 servidores Hangfire con colas nombradas |
| Scheduler (`routes/console.php`) | Hangfire `RecurringJob` o Quartz.NET | `onOneServer()` = lock distribuido; Hangfire lo trae |
| Observers y hooks | Interceptor de `SaveChanges` + eventos de dominio en el `DbContext`, o llamadas explicitas en los servicios | Sin equivalente automatico. Es donde mas logica se pierde si se porta solo por rutas |
| `Cache::remember`, `Cache::lock` | `IDistributedCache` o `HybridCache` (.NET 9+) sobre Redis; RedLock.net para locks | Laravel serializa con `serialize()` de PHP: .NET no puede leer las entradas de Laravel ni al reves. Cada lado usa prefijo propio |
| `RateLimiter::for` (12 limitadores) | `Microsoft.AspNetCore.RateLimiting` es solo en memoria por proceso | Hay que escribir un limitador en Redis (INCR + EXPIRE, o ventana deslizante en Lua) con las mismas claves por correo, token y usuario. Es pieza critica: la configuracion actual se midio con 1.000 personas detras de una IP |
| Mailables + EmailTemplate | MailKit + plantillas con `str.Replace` de `{{placeholders}}` | Portable tal cual; el SMTP por organizacion es construir `SmtpClient` por envio |
| minishlink/web-push | WebPush (paquete `WebPush` de web-push-libs) | VAPID compatible |
| Expo Push | `HttpClient` | Es REST puro |
| Flysystem S3 (R2) | AWSSDK.S3 con endpoint de R2 | Igual |
| spatie/browsershot | Microsoft.Playwright o PuppeteerSharp | Playwright pesa 300 MB en la imagen Docker; aislar en el worker |
| maatwebsite/excel | ClosedXML (XLSX) y CsvHelper | `fputcsv` tiene reglas de comillas propias; verificar con un diff de archivos |
| ZipStream | `System.IO.Compression.ZipArchive` en streaming | Igual |
| Google2FA | Otp.NET | TOTP estandar; los codigos de recuperacion estan hasheados, portables. El secreto esta cifrado con `encrypted` cast: hay que portar el descifrado AES-256-CBC con MAC de Laravel o re-cifrar |
| bcrypt (`$2y$`) | BCrypt.Net-Next | Verifica `$2y$` sin cambios |
| `hash_hmac` | `HMACSHA256` | Portable si se respeta la concatenacion exacta |
| Sentry | Sentry.AspNetCore | Igual |
| Telescope, Horizon UI, Pulse | Hangfire Dashboard + OpenTelemetry + Seq o Grafana | Sin equivalente uno a uno |
| Socket.IO + redis-adapter | SignalR + `Microsoft.AspNetCore.SignalR.StackExchangeRedis` | Ver 4.1. El backplane reparte mensajes pero no comparte el estado de conexiones (maximo por usuario, mapa asistente a sockets): eso va a Redis a mano |
| 17 endpoints `/internal/*` de Node | Minimal API en el mismo proceso del hub, o `IHubContext` llamado directamente | Si el hub y la API viven en el mismo servicio .NET, los endpoints internos desaparecen. Mientras Laravel exista, hay que mantener una fachada HTTP identica |
| Filament | **Sin equivalente** | Opciones: dejar el admin en Laravel sobre la misma base de datos, o reescribirlo en Next.js contra una API admin nueva. ABP, Blazor con Radzen o similares no ahorran la reescritura de 45 resources con sus acciones |
| Livewire, Blade (40 vistas) | Razor Pages o nada | Solo si se reescribe el admin |
| Pest (1.100 pruebas) | xUnit + Testcontainers (MySQL, Redis) + `WebApplicationFactory` | Las pruebas no se portan; se reemplazan por pruebas de contrato (seccion 6) y pruebas nuevas por modulo |
| k6 en `tests/load` | Se reutiliza tal cual | Son clientes HTTP: sirven de aceptacion contra .NET sin cambios |

---

## 4. Riesgos

### 4.1 Cambio de Socket.IO a SignalR

- **Ocho clientes cambian, no uno.** Webapp, Expo, kiosko y cinco paginas estaticas en `public/`. Las estaticas usan `io()` global; pasan a `@microsoft/signalr` UMD. Expo necesita el paquete con `skipNegotiation: true` y transporte WebSockets; funciona en React Native, pero hay que probar reconexion en segundo plano, que hoy esta afinada (ref-count de salas, rejoin al reconectar).
- **Doble despliegue obligatorio.** La app movil se actualiza por tiendas: durante semanas habra telefonos con Socket.IO. Hay que correr Node y SignalR a la vez y emitir por los dos, o condicionar por version de app. Esto suma tiempo y es la fuente mas probable de "a unos les llega y a otros no".
- **Semantica distinta.** Socket.IO tiene `socket.rooms` consultable (el chat lo usa para exigir `join:session` antes de `chat:send`); SignalR no expone los grupos de una conexion, hay que llevar el registro. El evento `error` de Socket.IO pasa a ser un mensaje normal. Los acks no se usan hoy, lo cual ayuda.
- **Afinidad.** Hoy no hay sticky sessions porque solo se usa WebSocket. En SignalR se logra lo mismo con `skipNegotiation` en todos los clientes; si alguno negocia, el balanceador de Cloudflare necesita afinidad.
- **Estado compartido entre procesos.** El backplane de Redis solo reenvia mensajes. El limite de 5 conexiones por usuario, el mapa asistente a conexiones (para `emit-to-user`), el contador de audiencia por sesion y `pulse:active_users` hoy viven en memoria de cada proceso Node y ya tienen bugs conocidos por eso (el canal `socket:config` existe por uno). En .NET hay que llevarlos a Redis desde el dia uno.
- **Lo que no cambia.** La ficha de auth `socketauth:t:{sha256}` es JSON crudo en Redis: .NET la lee igual. Las claves `chat:history`, `session:{id}:viewers` y los rate limits `rl:*` son strings y listas: portables.

### 4.2 Tokens de autenticacion durante la transicion

- **Compatibilidad total es posible.** Un token Sanctum es `id|secreto`; la tabla guarda `sha256(secreto)`, `abilities`, `last_used_at` y `expires_at` (7 dias). Un `AuthenticationHandler` en .NET que haga la misma consulta acepta todos los tokens vivos sin que nadie vuelva a entrar. Hay que replicar tambien: actualizar `last_used_at`, rechazar tokens de usuarios con acceso al panel, y al revocar borrar la ficha del socket.
- **No pasar a JWT durante la transicion.** JWT cambia `refresh`, `logout` (revocacion por lista) y rompe la ficha del socket, que esta indexada por el hash del token. Si se quiere JWT, es una fase posterior, con los dos formatos aceptados un tiempo.
- **Emision de tokens.** Mientras `/auth/login` siga en Laravel, Laravel emite y .NET valida. Cuando `/auth/*` pase a .NET, .NET debe escribir en la misma tabla con el mismo formato y dejar la ficha en Redis con el mismo JSON (`SocketAuthCache`), porque Node o SignalR la leen.
- **Sesion web del staff.** Filament y el Data Center usan cookie de sesion de Laravel con 2FA y `StaffSession`. Nada de eso es legible desde .NET. Mientras el admin siga en Laravel, el Data Center y cualquier endpoint que use `auth:sanctum` por cookie se quedan en Laravel, o el SPA del Data Center cambia a bearer.
- **Secretos.** `two_factor_secret` esta cifrado con el `encrypted` cast de Laravel (AES-256-CBC + HMAC, payload base64 JSON). Portar el descifrado es un dia de trabajo con pruebas cruzadas; equivocarse deja al staff fuera del panel.

### 4.3 Serializacion JSON

- **Nombres de campo.** ASP.NET Core serializa en camelCase por defecto. El contrato es snake_case en casi todo (`is_favorite`, `start_datetime`, `checked_in_at`) con una minoria en camelCase (`eventId`, `sessionId`, `fromName`, `storeUrlIos`, `expiresIn`, unos 15 campos) sobre todo en lo que viaja al socket. Hay que fijar `JsonNamingPolicy.SnakeCaseLower` globalmente y marcar con `JsonPropertyName` los camelCase uno a uno.
- **Fechas, dos formatos conviviendo.** Donde se llama `toIso8601String()` sale con offset local: `2026-10-21T13:15:00-05:00` (agenda, evento, baneos, sesiones). Donde se llama `toISOString()` o se serializa un modelo directo sale en UTC con microsegundos: `2026-10-21T18:15:00.123456Z` (muro, fotos, solicitudes, recap). `System.Text.Json` emite `DateTime` sin sufijo si el `Kind` es `Unspecified` y con 7 decimales si hay fraccion. Se necesita un conversor por formato y una decision por campo. Ademas, las columnas `DATETIME` de MySQL guardan hora de Bogota (`APP_TIMEZONE=America/Bogota`), asi que EF debe leer con `Kind=Unspecified` y convertir explicitamente; `DateTimeOffset` directo contra Pomelo da UTC y corre todo cinco horas.
- **Nulos y campos condicionales.** Laravel incluye `null`. Los Resources usan `when()` y `whenLoaded()`: el campo se omite, no va en `null` (`is_favorite`, `favorites_count`, `speakers`, `track`). El cliente a veces distingue "no viene" de "viene null". `System.Text.Json` necesita `JsonIgnore(WhenWritingDefault)` por propiedad o DTOs distintos.
- **Numeros y booleanos.** Los casts `boolean` (69) e `integer` (45) de Eloquent garantizan tipos; las consultas con `DB::table` y los agregados (`count`, `sum`) pueden salir como string segun el driver. Hay 9 archivos con `Redis::` y varios con `DB::table` en Data Center y Pulse. Las pruebas de contrato lo detectan; a ojo no.
- **Envoltorios y errores.** 106 respuestas van en `{data: ...}`, 37 por Resource (que tambien envuelve en `data`), y el resto devuelve el objeto directo. Paginacion: 3 `paginate` (forma `data, links, meta`) y 2 `cursorPaginate` (`next_cursor`). Errores: 401 `{"message":"Unauthenticated."}`, 403 de baneo y de aprobacion con sus claves, 422 `{message, errors}`, 429 con `Retry-After`. ASP.NET devuelve `ProblemDetails` por defecto: hay que desactivarlo y escribir las formas de Laravel.
- **Mensajes en español.** Los 422 llevan los textos de `lang/es` y los frontends los muestran tal cual. FluentValidation trae mensajes en ingles; hay que cargar los de Laravel.

### 4.4 Infraestructura compartida durante el strangler

- **Colas.** Un job despachado por Laravel esta serializado en PHP y solo lo corre un worker Laravel; lo mismo al reves. Durante la transicion cada lado corre lo suyo, y una accion que hoy despacha un job (por ejemplo, un observer de Laravel que dispara `RegenerateDocumentsZipJob`) sigue necesitando el worker PHP hasta que ese flujo entero se mueva.
- **Scheduler.** Un solo dueño por tarea. Cuando una tarea pasa a Hangfire se quita de `routes/console.php` en el mismo despliegue.
- **Cache.** Prefijos distintos. El problema real es la invalidacion cruzada: si el admin Filament guarda un speaker, el observer de Laravel borra la cache de Laravel, pero .NET no se entera. Solucion barata en la fase 0: que `InvalidationService` publique ademas en un canal Redis (`eventos:invalidate`) y .NET se suscriba.
- **Esquema.** Las migraciones son de Laravel hasta el final. EF Core en modo scaffold; cualquier columna nueva se agrega con una migracion Laravel y se re-scaffoldea.
- **Proxy.** El enrutamiento por prefijo funciona para casi todo, pero hay rutas mezcladas en un mismo prefijo (`/events/{id}/onboarding` es publico y `/events/{id}/onboarding/survey` lleva token; `api/v1/rooms/*` vive en web.php). Hay que enrutar por ruta exacta, no por prefijo, y poner una cabecera `X-Backend` para saber quien respondio.

### 4.5 Riesgo de equipo y calendario

Una persona con IA porta rapido codigo con tests; lo que no porta rapido es lo que no esta escrito en ningun lado: por que `TrustProxies` vive en el provider, por que el limitador cuenta fallos y no intentos, por que el chat batchea a 200 ms. El repo tiene 390 commits en siete meses y muchas de esas decisiones estan en comentarios largos dentro del codigo; conviene leerlos antes de portar cada archivo, no despues. El evento del 6 de diciembre no deja ventana para un doble despliegue en noviembre.

---

## 5. Orden de migracion por fases

Criterio: primero lo que no escribe y no depende de observers; despues lo que escribe; el tiempo real en el medio porque todo lo posterior emite; la autenticacion despues de tener contratos probados; la operacion del evento (salas, juegos, stand) al final de la API porque es lo mas complejo y lo que mas se ha medido; el admin como decision aparte.

| Fase | Contenido | Optimista | Realista |
|---|---|---|---|
| 0. Preparar en Laravel | OpenAPI generado, pruebas de contrato con snapshots, canal Redis de invalidacion, capa `realtime` en los clientes, fijar formato de fechas (seccion 6) | 2 | 3 |
| 1. Cimientos .NET | Solucion, Docker, EF scaffold de 105 tablas, handler Sanctum, politica JSON y formas de error, limitador en Redis, cache con prefijo, Sentry, CI con las pruebas de contrato, reverse proxy por ruta, `/health` y `/version` | 2 | 4 |
| 2. Lectura publica y contenido | 12 publicos + 31 de contenido + `/events/{id}/mine`: cache, invalidacion por canal, `.ics`, landing | 2 | 3 |
| 3. Perfil, social y gamificacion | `/me/*`, muro, fotos, historias, ratings, preguntas, votos, networking, premios, pasaporte, `PointsService`, webhooks de Attendee como eventos de dominio | 3 | 5 |
| 4. Tiempo real | Hub SignalR con backplane, estado en Redis, fachada `/internal/*` para Laravel, chat con persistencia propia, librerias cliente para los 8 consumidores, release de Expo, periodo de doble emision | 3 | 6 |
| 5. Autenticacion, registro y correo | 18 de auth + registro publico + handoff + Turnstile, emision de tokens compatible, ficha del socket, 20 correos con plantillas por evento, SMTP por organizacion, colas de correo con limitador, push Expo y Web, notificaciones programadas y recordatorios (6 jobs, 6 tareas) | 3 | 5 |
| 6. Operacion del evento | Check-in, salas y totems, staff de puerta, verificaciones de asistencia, stand y leads, juegos en vivo, config de sesion, baneos, uploads, Pulse, webhooks entrantes, auto-checkouts | 4 | 8 |
| 7. Data Center, exportaciones y recap | 19 endpoints, 50 jobs de exportacion con worker propio, programadas, embeds, ZIP de documentos, recap con Playwright, el SPA pasa a bearer | 3 | 6 |
| 8. Apagar Laravel (sin admin) | Migraciones EF toman el esquema, quitar proxies, scheduler y colas PHP, Node fuera, limpieza | 1 | 3 |
| **Total sin reescribir el admin** | | **23** | **43** |
| 9. Admin (si se reescribe) | API admin nueva + Next.js: 45 resources, wizard, importacion CSV, contexto de evento, 2FA y seguridad del staff, plantillas de correo, modulos, landing y widget | 12 a 16 | 18 a 26 |
| **Total con admin** | | **35 a 39** | **61 a 69** |

Semanas de una persona a tiempo completo. Si la misma persona opera eventos o sigue desarrollando funciones en paralelo, multiplicar por 1,5 a 2.

### Que separa el escenario optimista del realista

1. **Quien se adapta al contrato.** Optimista: los frontends aceptan ajustes pequeños (un formato de fecha, un campo que ahora viene `null`). Realista: .NET debe ser byte a byte compatible porque hay versiones de Expo viejas en la calle durante meses.
2. **Doble emision del socket.** Optimista: dos semanas con Node y SignalR a la vez. Realista: seis a ocho semanas hasta que la version vieja de la app baje de un porcentaje aceptable, con los bugs de "a unos les llega" que eso trae.
3. **Dedicacion.** Optimista: tiempo completo, sin evento en el medio. Realista: hay eventos, soporte y funciones nuevas que entran en Laravel y hay que portar dos veces.
4. **Logica no documentada.** Optimista: las pruebas de contrato de la fase 0 capturan el comportamiento real y la IA porta contra ellas. Realista: cada modulo grande (salas, juegos, stand, auth) tiene dos o tres comportamientos que solo aparecen en produccion y cuestan una semana cada uno.
5. **Data Center y sesion web.** Optimista: el SPA pasa a bearer en dos dias. Realista: el SPA depende de la sesion compartida con Filament y hay que tocar el login del staff.
6. **Rendimiento.** Optimista: .NET iguala las mediciones de Laravel (300 por maquina, TTFB 170 a 335 ms) a la primera. Realista: hay que repetir la bateria de `tests/load` por fase y afinar EF (consultas N+1 que Eloquent ya tenia resueltas con `with`), que es una semana por bloque grande.

---

## 6. Que preparar ya en Laravel

Todo esto mejora el backend actual aunque la migracion no arranque, y es lo que vuelve portable el codigo:

1. **OpenAPI generado desde el codigo.** `dedoc/scramble` lee controladores, Form Requests y Resources sin anotaciones. Publicar `/docs/api.json` en CI y versionarlo. Sin esto la IA porta a ciegas desde 465 `response()->json`.
2. **Pruebas de contrato con snapshots.** Un seeder determinista (fecha fija, evento fijo, 10 asistentes) y una prueba por endpoint que guarde la respuesta JSON como archivo dorado. Mismas pruebas, apuntadas a .NET por una variable de entorno, son el criterio de "esta ruta ya se puede enrutar". Los 111 archivos Pest existentes prueban comportamiento; estos prueban la forma.
3. **Un solo formato de fecha.** Decidir hoy entre UTC con `Z` y offset local, aplicarlo con `serializeDate` en un modelo base y en los Resources, y anunciarlo a los clientes. Es un cambio de contrato pequeño en Laravel y uno grande si se descubre a mitad de la migracion.
4. **DTOs explicitos.** Pasar los 106 `['data' => ...]` a Resources y sacar los arrays armados a mano de los cinco controladores grandes (Stand 884 lineas, Game 821, RoomCheckin 764, Networking 717, StaffCheckin 588) a servicios que devuelvan objetos tipados. Eso es lo que se porta uno a uno.
5. **Vaciar `routes/web.php`.** Las 16 closures (561 lineas, incluidos los 5 endpoints internos del chat y las paginas de display) a controladores con nombre. Una closure no aparece en OpenAPI ni se prueba facil.
6. **Catalogo de eventos de tiempo real.** Un archivo JSON con los 44 nombres de evento, su payload y quien lo escucha, consumido por `types.ts` del socket y por los tipos de los 8 clientes. Hoy `types.ts` es la unica fuente y las paginas estaticas no la usan.
7. **Capa `realtime` en cada cliente.** Un modulo por cliente (`realtime.ts`) que envuelva `getSocket`, `on`, `off`, `emit`, `joinSession`. Webapp y Expo ya tienen el singleton; faltan kiosko y las cinco paginas estaticas. Con eso el cambio a SignalR es un archivo por cliente.
8. **Canal Redis de invalidacion.** Que `InvalidationService::broadcast` publique tambien en `eventos:invalidate` (JSON `{eventId, entity, op}`), ademas del POST al socket. Cuesta diez lineas y es lo que permite que .NET y Laravel compartan cache sin pisarse.
9. **Catalogo de claves de Redis y de variables de entorno.** Hoy hay 9 archivos con `Redis::` directo y 60 variables en `.env`. Una tabla con clave, dueño, formato y TTL evita que .NET lea algo serializado en PHP.
10. **Sacar la logica de los observers a servicios con nombre.** `EventSessionObserver::updated` hace cuatro cosas distintas. Si cada una es un metodo de un servicio (`SessionChanged::notify`, `::broadcastMode`), el observer queda de dos lineas y en .NET se llama lo mismo desde el `SaveChanges`.
11. **Cabecera `X-Backend` y metrica por ruta.** Poner la cabecera desde ya en Laravel y registrar TTFB por ruta en Pulse; es la linea base contra la que se compara cada fase.
12. **Outbox para webhooks y correos.** Hoy el observer de Attendee despacha el webhook en el mismo request. Una tabla outbox que un job drena hace que el flujo sea igual en los dos backends y sobreviva al cambio de colas.
13. **Congelar funciones durante cada fase.** Cada funcion nueva que entra en Laravel en un modulo ya portado se escribe dos veces. Acordar que lo nuevo entra solo en modulos todavia no migrados, o directamente en .NET una vez pasada la fase 1.

---

## Apendice A. Las 247 rutas de la API

Formato: metodo, URI bajo `api/`, controlador, `A` si exige Sanctum, limitador o guardia especial.

```
POST   v1/admin/attendees/{attendeeId}/ban            BanController@ban                         A api
DELETE v1/admin/attendees/{attendeeId}/ban            BanController@unban                       A api
GET    v1/admin/attendees/{attendeeId}/ban-history    BanController@history                     A api
PATCH  v1/admin/attendees/{attendeeId}/role           AttendeeController@updateRole             A api
DELETE v1/admin/chat/messages/{id}                    ChatController@destroy                    A api
GET    v1/admin/events/{eventId}/attendees            AttendeeController@index                  A api
GET    v1/admin/events/{event}/notifications          NotificationController@index              A api
POST   v1/admin/events/{event}/notifications/schedule NotificationController@schedule           A api
POST   v1/admin/events/{event}/notifications/send     NotificationController@send               A api
DELETE v1/admin/events/{event}/notifications/{notification} NotificationController@destroy      A api
POST   v1/admin/games                                 GameController@store                      A api
PATCH  v1/admin/games/{id}                            GameController@update                     A api
DELETE v1/admin/games/{id}                            GameController@destroy                    A api
POST   v1/admin/games/{id}/close-round                GameController@closeRound                 A api
POST   v1/admin/games/{id}/draw                       GameController@draw                       A api
GET    v1/admin/games/{id}/export                     GameController@export                     A api
POST   v1/admin/games/{id}/launch                     GameController@launch                     A api
POST   v1/admin/games/{id}/next-question              GameController@nextQuestion               A api
GET    v1/admin/games/{id}/results                    GameController@results                    A api
POST   v1/admin/games/{id}/spin                       GameController@spin                       A api
GET    v1/admin/polls                                 PollController@index                      A api
POST   v1/admin/polls                                 PollController@store                      A api
POST   v1/admin/polls/votes/approve-batch             PollController@approveVoteBatch           A api
DELETE v1/admin/polls/votes/{vote}                    PollController@rejectVote                 A api
POST   v1/admin/polls/votes/{vote}/approve            PollController@approveVote                A api
POST   v1/admin/polls/{poll}/close                    PollController@close                      A api
GET    v1/admin/polls/{poll}/results                  PollController@results                    A api
POST   v1/admin/polls/{poll}/start                    PollController@start                      A api
POST   v1/admin/sessions/{sessionId}/cancel           SessionConfigController@cancel            A api
POST   v1/admin/sessions/{sessionId}/delay            SessionConfigController@delayRoom         A api
POST   v1/admin/sessions/{sessionId}/end              SessionConfigController@end               A api
GET    v1/admin/sessions/{sessionId}/games            GameController@bySession                  A api
GET    v1/admin/sessions/{sessionId}/live-config      SessionConfigController@show              A api
PATCH  v1/admin/sessions/{sessionId}/live-config      SessionConfigController@update            A api
POST   v1/admin/sessions/{sessionId}/start            SessionConfigController@start             A api
POST   v1/admin/uploads                               UploadController@store                    A api
DELETE v1/admin/uploads                               UploadController@destroy                  A api
GET    v1/attendees/{attendeeId}/profile              NetworkingController@profile              A api
POST   v1/auth/activate                               AuthController@activate                   - cuenta_enlace
POST   v1/auth/check-email                            AuthController@checkEmail                 - entry_check
POST   v1/auth/expo-token                             ExpoTokenController@update                A
POST   v1/auth/forgot-password                        AuthController@forgotPassword             - cuenta_correo
POST   v1/auth/login                                  AuthController@login                      - login
POST   v1/auth/logout                                 AuthController@logout                     A
POST   v1/auth/magic-link                             AuthController@requestMagicLink           - magic_link
GET    v1/auth/me                                     AuthController@me                         A
POST   v1/auth/refresh                                AuthController@refresh                    A
POST   v1/auth/register                               AuthController@register                   - cuenta_correo
POST   v1/auth/reset-password                         AuthController@resetPassword              - cuenta_enlace
POST   v1/auth/verify-email                           AuthController@resendVerification         A
GET    v1/auth/verify-email/{token}                   AuthController@verifyEmail                -
POST   v1/auth/verify-identity                        AuthController@verifyIdentity             - entry_check
POST   v1/auth/verify-magic-link                      AuthController@verifyMagicLink            - magic_verify
GET    v1/auth/web-push-key                           WebPushSubscriptionController@key         A
POST   v1/auth/web-push-subscription                  WebPushSubscriptionController@store       A
DELETE v1/auth/web-push-subscription                  WebPushSubscriptionController@destroy     A
POST   v1/checkin                                     CheckinController@checkin                 A api
POST   v1/contacts/block/{attendeeId}                 NetworkingController@block                A api
DELETE v1/contacts/block/{attendeeId}                 NetworkingController@unblock              A api
POST   v1/contacts/request                            NetworkingController@sendRequest          A api
PUT    v1/contacts/request/{id}                       NetworkingController@respondRequest       A api
GET    v1/data-center/events                          DataCenterController@events               A (cookie o bearer)
GET    v1/data-center/multi                           DataCenterController@multiEventStats      A
GET    v1/data-center/{event}/embeds                  DataCenterController@listEmbeds           A
POST   v1/data-center/{event}/embeds                  DataCenterController@createEmbed          A
DELETE v1/data-center/{event}/embeds/{token}          DataCenterController@deleteEmbed          A
POST   v1/data-center/{event}/export                  DataCenterController@export               A
POST   v1/data-center/{event}/export-all              DataCenterController@exportAll            A
DELETE v1/data-center/{event}/export/{id}             DataCenterController@cancelExport         A
GET    v1/data-center/{event}/exports                 DataCenterController@listExports          A
GET    v1/data-center/{event}/goals                   DataCenterController@listGoals            A
POST   v1/data-center/{event}/goals                   DataCenterController@saveGoal             A
DELETE v1/data-center/{event}/goals/{metric}          DataCenterController@deleteGoal           A
GET    v1/data-center/{event}/notifications           DataCenterController@notifications        A
DELETE v1/data-center/{event}/notifications/clear     DataCenterController@clearNotifications   A
POST   v1/data-center/{event}/notifications/read-all  DataCenterController@markNotificationsRead A
GET    v1/data-center/{event}/scheduled               DataCenterController@listScheduled        A
POST   v1/data-center/{event}/scheduled               DataCenterController@createScheduled      A
DELETE v1/data-center/{event}/scheduled/{id}          DataCenterController@deleteScheduled      A
GET    v1/data-center/{event}/stats                   DataCenterController@stats                A
GET    v1/events/attendance-checks/active             RoomCheckinController@activeCheck         A api
GET    v1/events/attendance-checks/history            RoomCheckinController@checkHistory        A api
GET    v1/events/attendance-checks/pending            RoomCheckinController@pendingCheck        A api
GET    v1/events/attendance-checks/report             RoomCheckinController@report              A api
POST   v1/events/attendance-checks/trigger            RoomCheckinController@triggerCheck        A api
POST   v1/events/attendance-checks/{checkId}/confirm  RoomCheckinController@confirmCheck        A api
GET    v1/events/attendance-checks/{checkId}/results  RoomCheckinController@checkResults        A api
GET    v1/events/by-slug/{slug}                       PublicEventController@show                - public_read
POST   v1/events/games/{id}/answer                    GameController@answer                     A api
POST   v1/events/games/{id}/join                      GameController@join                       A api
POST   v1/events/polls/{poll}/vote                    PollController@vote                       A api
GET    v1/events/rooms/{eventId}/occupancy            RoomCheckinController@occupancy           A api
GET    v1/events/rooms/{roomId}/attendees             RoomCheckinController@attendeesInRoom     A api
GET    v1/events/sessions/{sessionId}/export          SessionStatsController@export             A api
GET    v1/events/sessions/{sessionId}/game/active     GameController@active                     A api
GET    v1/events/sessions/{sessionId}/poll/active     PollController@activePoll                 A api
GET    v1/events/sessions/{sessionId}/stats           SessionStatsController@stats              A api
GET    v1/events/sessions/{sessionId}/viewers         SessionStatsController@viewers            A api
POST   v1/events/staff-checkin/accept-assignment      StaffCheckinController@acceptAssignment   A api
POST   v1/events/staff-checkin/assign                 StaffCheckinController@assign             A api
GET    v1/events/staff-checkin/my-rooms               StaffCheckinController@myRooms            A api
GET    v1/events/staff-checkin/pending-assignment     StaffCheckinController@pendingAssignment  A api
POST   v1/events/staff-checkin/reassign               StaffCheckinController@reassign           A api
POST   v1/events/staff-checkin/reject-assignment      StaffCheckinController@rejectAssignment   A api
GET    v1/events/staff-checkin/rooms                  StaffCheckinController@listRooms          A api
POST   v1/events/staff-checkin/scan                   StaffCheckinController@scan               A api
POST   v1/events/staff-checkin/scan-batch             StaffCheckinController@scanBatch          A api
POST   v1/events/staff-checkin/unassign               StaffCheckinController@unassign           A api
POST   v1/events/support                              SupportController@store                   A api
GET    v1/events/support/mine                         SupportController@mine                    A api
GET    v1/events/{eventId}/agenda                     AgendaController@index                    A api
GET    v1/events/{eventId}/agenda/mine                AgendaController@mine                     A api
GET    v1/events/{eventId}/agenda/shared              AgendaController@shared                   A api
POST   v1/events/{eventId}/agenda/{sessionId}/favorite AgendaController@toggleFavorite          A api
GET    v1/events/{eventId}/announcements              AnnouncementController@index              A api
GET    v1/events/{eventId}/attendance                 CheckinController@attendance              A api
GET    v1/events/{eventId}/attendees                  NetworkingController@directory            A api
GET    v1/events/{eventId}/branding                   EventBrandingController@show              A api
GET    v1/events/{eventId}/documents                  DocumentController@index                  A api
GET    v1/events/{eventId}/documents/zip              DocumentZipController@show                A api
GET    v1/events/{eventId}/faqs                       FaqController@index                       A api
GET    v1/events/{eventId}/gamification-config        GamificationController@config             A api
GET    v1/events/{eventId}/gamification/rules         GamificationController@rules              A api
GET    v1/events/{eventId}/happening-now              HappeningNowController@index              A api
GET    v1/events/{eventId}/highlights                 HighlightController@index                 A api
GET    v1/events/{eventId}/leaderboard                GamificationController@leaderboard        A api
GET    v1/events/{eventId}/leaderboard/mine           GamificationController@leaderboardMine    A api
GET    v1/events/{eventId}/leaderboard/shared         GamificationController@leaderboardShared  A api
GET    v1/events/{eventId}/mine                       LoMioController@index                     A api
GET    v1/events/{eventId}/modules                    ModuleController@index                    A api
GET    v1/events/{eventId}/my-interests               OnboardingController@myInterests          A api
PUT    v1/events/{eventId}/my-interests               OnboardingController@updateInterests      A api
GET    v1/events/{eventId}/my-passport                PassportController@show                   A api
GET    v1/events/{eventId}/my-ratings                 RatingController@myRatings                A api
GET    v1/events/{eventId}/my-recap                   RecapController@myRecap                   A api
GET    v1/events/{eventId}/my-speaker-ratings         SpeakerRatingController@myRatings         A api
GET    v1/events/{eventId}/onboarding                 OnboardingController@index                - public_read
GET    v1/events/{eventId}/onboarding-fields          RegistrationFieldController@onboardingFields - public_read
POST   v1/events/{eventId}/onboarding/survey          OnboardingController@storeSurvey          A api (sin check.approval)
GET    v1/events/{eventId}/pages                      CustomPageController@index                A api
GET    v1/events/{eventId}/pages/{pageId}             CustomPageController@show                 A api
GET    v1/events/{eventId}/photos                     EventPhotoController@index                A api
POST   v1/events/{eventId}/photos                     EventPhotoController@store                A api
GET    v1/events/{eventId}/photos/contest             EventPhotoController@contest              A api
GET    v1/events/{eventId}/photos/mine                EventPhotoController@mine                 A api
POST   v1/events/{eventId}/photos/{photoId}/like      EventPhotoController@like                 A api
DELETE v1/events/{eventId}/photos/{photoId}/like      EventPhotoController@unlike               A api
GET    v1/events/{eventId}/post-event-survey          PollController@postEventSurvey            A api
GET    v1/events/{eventId}/registration-fields        RegistrationFieldController@index         - public_read
GET    v1/events/{eventId}/rewards                    RewardController@index                    A api
POST   v1/events/{eventId}/rewards/{rewardId}/redeem  RewardController@redeem                   A api
GET    v1/events/{eventId}/sessions/{sessionId}/calendar.ics AgendaController@calendar          A api
GET    v1/events/{eventId}/sessions/{sessionId}/live-config SessionConfigController@publicShow  A api
GET    v1/events/{eventId}/sessions/{sessionId}/questions QuestionController@index              A api
POST   v1/events/{eventId}/sessions/{sessionId}/questions QuestionController@store              A api
GET    v1/events/{eventId}/sessions/{sessionId}/questions/pending QuestionController@pending    A api
PATCH  v1/events/{eventId}/sessions/{sessionId}/questions/{questionId}/moderate QuestionController@moderate A api
POST   v1/events/{eventId}/sessions/{sessionId}/questions/{questionId}/upvote QuestionController@upvote A api
POST   v1/events/{eventId}/sessions/{sessionId}/rate  RatingController@store                    A api
GET    v1/events/{eventId}/sessions/{sessionId}/ratings RatingController@adminIndex             A api
GET    v1/events/{eventId}/speakers                   SpeakerController@index                   A api
GET    v1/events/{eventId}/speakers/{speakerId}       SpeakerController@show                    A api
POST   v1/events/{eventId}/speakers/{speakerId}/rate  SpeakerRatingController@store             A api
GET    v1/events/{eventId}/sponsors                   SponsorController@index                   A api
GET    v1/events/{eventId}/sponsors/mine              SponsorController@mine                    A api
GET    v1/events/{eventId}/sponsors/shared            SponsorController@shared                  A api
POST   v1/events/{eventId}/sponsors/{sponsorId}/contact SponsorController@contact               A api
POST   v1/events/{eventId}/sponsors/{sponsorId}/favorite SponsorController@favorite             A api
DELETE v1/events/{eventId}/sponsors/{sponsorId}/favorite SponsorController@unfavorite           A api
POST   v1/events/{eventId}/sponsors/{sponsorId}/view  SponsorController@recordView              A api
GET    v1/events/{eventId}/stories                    AttendeeStoryController@index             A api
POST   v1/events/{eventId}/stories                    AttendeeStoryController@store             A api
DELETE v1/events/{eventId}/stories/{storyId}          AttendeeStoryController@destroy           A api
GET    v1/events/{eventId}/suggested-contacts         NetworkingController@suggestedContacts    A api
GET    v1/events/{eventId}/surveys                    PollController@surveys                    A api
GET    v1/events/{eventId}/tracks                     SessionTrackController@index              A api
POST   v1/events/{eventId}/trivia/{triviaId}/answer   GamificationController@answerTrivia       A api
POST   v1/events/{eventId}/visit-stand/{sponsorId}    GamificationController@visitStand         A api
GET    v1/events/{eventId}/wall                       WallController@index                      A api
POST   v1/events/{eventId}/wall                       WallController@store                      A api
GET    v1/events/{eventId}/wall/mine                  WallController@mine                       A api
GET    v1/events/{eventId}/wall/shared                WallController@shared                     A api
GET    v1/events/{eventId}/wall/{postId}/comments     WallController@comments                   A api
POST   v1/events/{eventId}/wall/{postId}/comments     WallController@storeComment               A api
POST   v1/events/{eventId}/wall/{postId}/like         WallController@like                       A api
DELETE v1/events/{eventId}/wall/{postId}/like         WallController@unlike                     A api
GET    v1/events/{slug}/login-slides                  PublicEventController@loginSlides         - public_read
GET    v1/health                                      HealthController@health                   -
GET    v1/leads                                       LeadController@index                      A api
POST   v1/leads                                       LeadController@store                      A api
PUT    v1/leads/{lead}                                LeadController@update                     A api
GET    v1/leads/{lead}/edits                          LeadController@edits                      A api
GET    v1/me/blocked                                  NetworkingController@blockedList          A api
GET    v1/me/contact-requests                         NetworkingController@receivedRequests     A api
GET    v1/me/contact-requests/sent                    NetworkingController@sentRequests         A api
GET    v1/me/contacts                                 NetworkingController@myContacts           A api
PATCH  v1/me/email                                    ChangeEmailController@update              A change_email
GET    v1/me/leads/export                             LeadController@export                     A api
GET    v1/me/onboarding-data                          ProfileController@onboardingData          A api
PUT    v1/me/onboarding-data                          ProfileController@updateOnboardingData    A api
GET    v1/me/pending-invitations                      StandController@pendingInvitations        A api
POST   v1/me/photo                                    ProfileController@uploadPhoto             A api
DELETE v1/me/photo                                    ProfileController@deletePhoto             A api
GET    v1/me/points                                   GamificationController@myPoints           A api (sin check.approval)
GET    v1/me/prizes                                   RewardController@myPrizes                 A api
GET    v1/me/profile                                  ProfileController@show                    A api
PUT    v1/me/profile                                  ProfileController@update                  A api
GET    v1/me/qr                                       CheckinController@qr                      A api
GET    v1/me/redemptions                              RewardController@myRedemptions            A api
GET    v1/me/registration-fields                      RegistrationFieldController@myFields      A api
PUT    v1/me/registration-fields                      RegistrationFieldController@updateMyFields A api
GET    v1/me/stand                                    StandController@show                      A api
GET    v1/me/stand/contacts                           StandController@contacts                  A api
POST   v1/me/stand/members                            StandController@addMember                 A api
DELETE v1/me/stand/members/{attendeeId}               StandController@removeMember              A api
POST   v1/me/stand/resolve-qr                         StandController@resolveQr                 A api
GET    v1/me/stand/search-attendees                   StandController@searchAttendees           A api
POST   v1/me/stand/share-link                         StandController@generateShareLink         A api
GET    v1/me/stand/stats                              StandController@stats                     A api
POST   v1/me/stand/transfer                           StandController@transferOwnership         A api
GET    v1/presets/cities/{countryCode}                PresetController@cities                   - public_read
GET    v1/presets/{type}                              PresetController@preset                   - public_read
GET    v1/public/events/{slug}/landing                PublicLandingController@show              - public_read
POST   v1/public/events/{slug}/register               PublicRegistrationController@store        - public_register
POST   v1/public/handoff                              PublicHandoffController@store             - public_register
GET    v1/pulse/validate                              Closure                                   - (lo llama el socket)
GET    v1/pulse/{slug}/bootstrap                      PulseController@bootstrap                 - check.pulse
GET    v1/pulse/{slug}/checkins                       PulseController@checkins                  - check.pulse
GET    v1/pulse/{slug}/connections                    PulseController@connections               - check.pulse
GET    v1/pulse/{slug}/leaderboard                    PulseController@leaderboard               - check.pulse
GET    v1/pulse/{slug}/leads                          PulseController@leads                     - check.pulse
GET    v1/pulse/{slug}/ratings                        PulseController@ratings                   - check.pulse
GET    v1/pulse/{slug}/rooms                          PulseController@rooms                     - check.pulse
GET    v1/pulse/{slug}/social                         PulseController@social                    - check.pulse
POST   v1/rewards/confirm                             RewardController@confirm                  A api
GET    v1/rooms/manifest                              RoomCheckinController@manifest            - token de totem (web.php)
GET    v1/rooms/ping                                  RoomCheckinController@ping                - token de totem (web.php)
POST   v1/rooms/scan                                  RoomCheckinController@scan                - token de totem (web.php)
POST   v1/rooms/scan/batch                            RoomCheckinController@scanBatch           - token de totem (web.php)
GET    v1/sessions/{sessionId}/chat/messages          ChatController@index                      A api
POST   v1/staff-invitations/{token}/accept            StandController@acceptInvitation          A api
GET    v1/staff-invitations/{token}/info              StandController@invitationInfo            A api
POST   v1/staff-invitations/{token}/reject            StandController@rejectInvitation          A api
POST   v1/track                                       TrackController@store                     A api
GET    v1/version                                     HealthController@version                  -
POST   v1/webhooks/checkin                            WebhookInboundController@checkin          - X-Webhook-Key
POST   v1/webhooks/checkin/batch                      WebhookInboundController@checkinBatch     - X-Webhook-Key
```

## Apendice B. Eventos del socket por cliente

> **2026-10-22:** la fuente es `docs/contratos/eventos-socket.json` (M0.4.1). Esta tabla quedo con errores: Mission
> Control no escucha `display:*` y si `session:*`; faltan `game:round-result`, `game:answer-count`, `room:occupancy` y los
> 4 `staff:*` de salas.

| Cliente | Eventos que escucha |
|---|---|
| Webapp (Next) | ban:enforced, chat:deleted, chat:emoji, chat:history, chat:message, chat:pinned, chat:unpinned, data:invalidate, data:sync, game:result, networking:notify, poll:closed, poll:new, poll:updated, question:answered, question:approved, question:upvoted, session:config_updated, session:mode_changed, staff:invited, staff:removed, wall:comment, wall:post |
| Expo | los 23 de la webapp mas checkin:update, staff:accepted, staff:rejected |
| Mission Control (`public/mission-control`) | attendance:check:update, chat:deleted, chat:history, chat:message, chat:pinned, chat:unpinned, display:project, display:stop, game:result, game:update, poll:closed, poll:new, poll:updated, question:answered, question:approved, question:submitted, session:audience, session:ended, session:metrics, session:started |
| chat-monitor | chat:deleted, chat:history, chat:message, chat:pinned, chat:unpinned, poll:closed, poll:new, poll:updated, question:answered, question:approved, question:submitted |
| Event Pulse | checkin:update, data:invalidate, pulse:active_users, session:audience, session:config_updated, wall:post |
| display | display:project, display:stop, game:result, game:update, poll:closed, poll:updated |
| Kiosko | checkin:update |
| attendance-check | attendance:check:update |

Emitidos por Laravel a traves de `/internal/broadcast` y no tipados en `types.ts`: agenda:delayed, agenda:updated, attendance:check, attendance:check:update, game:finished, game:launched, game:question, game:result, game:update, session:cancelled, session:ended, session:started.
