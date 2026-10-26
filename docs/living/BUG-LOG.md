# Bug Log — EventOS

> Registro completo de bugs encontrados y corregidos. Ordenado por fecha, mas reciente primero.
> Severidades: CRITICA (seguridad/crash/data) | ALTA (feature roto) | MEDIA (visual/UX) | BAJA (cosmetic/warning)

## 2026-10-25 — Encontrados al armar el bloque 2.1 (admin nuevo)

### BUG-474: En el admin nuevo, un evento ajeno mostraba la pagina de "no existe" con estado 200 (RESUELTO 2026-10-26, antes de desplegar)
- **Severidad propuesta:** BAJA (el contenido era correcto; el estado no)
- **Que pasa:** el layout del panel envolvia todo en `<Suspense>` (por `useSearchParams`); la respuesta empezaba a salir antes de que el layout del evento llamara `notFound()`, y el 404 quedaba en 200.
- **Verificado a mano:** si, prueba de punta a punta "un evento que la persona no opera no existe: 404".
- **Resuelto:** sin Suspense en el panel; la ruta de vuelta de la sesion vencida se arma al hacer clic desde `window.location`. Web `43d7635`.

### BUG-473: La activacion del 2FA declaraba dos formas para el mismo 200 y el OpenAPI perdia una (RESUELTO 2026-10-26, antes de desplegar)
- **Severidad propuesta:** MEDIA (los tipos de Next no traian la forma real de la respuesta)
- **Que pasa:** `POST /api/admin/v1/auth/two-factor/setup` respondia `ActivacionRespuesta` o `IngresoRespuesta` segun el caso; el OpenAPI de C# solo guardaba la ultima declarada.
- **Verificado a mano:** si, al usar los tipos generados en el login de Next (M2.2.7).
- **Resuelto:** una sola forma, `ActivacionDatos(status, secret, otpauth_uri)`, con secreto y enlace vacios si el 2FA ya estaba activo. C# `92299b9`; `IngresoDelStaffTest` afirma el status.

### BUG-472: El reloj congelado del modo contratos dejaba a PHP sin codigo TOTP valido en las pruebas cruzadas (RESUELTO 2026-10-25, antes de desplegar)
- **Severidad propuesta:** BAJA (solo pruebas)
- **Que pasa:** el C# en modo contratos usa el reloj de ContratoSeeder (6 de diciembre de 2026). Al activar el 2FA marca
  como usado un intervalo del futuro, y PHP (Google2FA mira la hora real, no el reloj congelado de Carbon) ya no acepta
  ningun codigo de hoy. Ademas C# rechazaba el codigo que PHP calculaba con la hora real.
- **Verificado a mano:** si, primera corrida de `tests/Cruzadas` (422 "Ese codigo no coincide").
- **Resuelto:** `Contratos:RelojReal=true` para el C# de las pruebas cruzadas (`scripts/cruzadas-contra-csharp.sh`); los
  contratos siguen con el reloj congelado. C# `8eb07e0`.

### BUG-471: Las pruebas de C# que escriben en la base sembrada rompian a las que la cuentan (RESUELTO 2026-10-25, antes de desplegar)
- **Severidad propuesta:** BAJA (solo pruebas; falla intermitente)
- **Que pasa:** `ModeloTest` y `ReportesDbTest` cuentan las 13 personas de ContratoSeeder; `IngresoDelStaffTest` y
  `AccesoDelStaffTest` crean personas (y la de acceso mueve el evento 2) en paralelo. Segun el orden salian 17 o 18.
- **Verificado a mano:** si, en la suite completa con MySQL (aisladas pasaban).
- **Resuelto:** coleccion de xUnit `EscribeEnLaBaseSembrada` sin paralelo para las clases que escriben. Tres corridas
  seguidas en verde. C# `bf71075`.

### BUG-470: En C#, una ruta sin grupo salia en todos los documentos OpenAPI (RESUELTO 2026-10-25, antes de desplegar)
- **Severidad propuesta:** BAJA (documentacion generada; no llego a ningun lado)
- **Que pasa:** al sumar el documento `admin`, `Microsoft.AspNetCore.OpenApi` incluye por defecto en cada documento las
  rutas sin `GroupName`: `/api/v1/auth/me`, `/health` y `/version` salian tambien en el OpenAPI del admin.
- **Verificado a mano:** si, lo atrapo `ApiDelAdminTest.Cada_documento_lleva_solo_sus_rutas` antes del commit.
- **Resuelto:** `ShouldInclude` explicito en los dos documentos (`OpenApiDeLaApi.cs`). C# `304b993` (M2.1.4).

### BUG-469: No se encontro por donde llega el asistente a una pagina personalizada (POR VERIFICAR)
- **Severidad propuesta:** MEDIA si se confirma (el cliente las usa para cargar su contenido en un iframe, segun Kamilo)
- **Que pasa:** la app de Expo registra `app/(app)/pages/[id].tsx` y tiene `hooks/usePages.ts`, pero ninguna pantalla
  enlaza a `/pages/`; la webapp no llama `GET /api/v1/events/{eventId}/pages`. Puede que llegue por un modulo o un
  deeplink que la busqueda no vio.
- **Verificado a mano:** no. Busqueda de texto en eventos-app (app, components, hooks, lib) y eventos-web/src, 2026-10-25.
- **Archivos:** `eventos-app/app/(app)/pages/[id].tsx`, `eventos-app/hooks/usePages.ts`,
  `eventos-backend/app/Http/Controllers/Api/V1/CustomPageController.php`.
- **Cuando se verifica:** en M2.3.3, al construir la pantalla de paginas del admin nuevo (abrir una pagina con iframe
  desde la app y la webapp).

## 2026-10-25 — Encontrados al construir la fase 1 en C# (bloques 1.3 a 1.5)

### BUG-468: El `api.json` de PHP describe `/health` como texto (ABIERTO)
- **Severidad propuesta:** BAJA (documentacion generada; la respuesta real esta bien y la fija el snapshot)
- **Que pasa:** Scramble infiere `{"type": "string"}` para la respuesta 200 de `GET /api/v1/health`; la real es
  `{status, checks: {database, redis, queue}, timestamp}` (snapshot `PublicoTest/GET__api_v1_health`). Tampoco documenta el 503.
- **Verificado a mano:** si, al armar M1.5.7 (`OpenApiContraPhpTest` lo tiene como excepcion con motivo).
- **Archivos:** `app/Http/Controllers/Api/V1/HealthController.php`, `docs/api.json`.
- **Cuando se arregla:** cuando se toque `HealthController` (un tipo de retorno o anotacion para Scramble) o al apagar PHP
  (fase 5); hasta entonces la excepcion queda en la prueba.

### BUG-467: C# escuchaba en todas las interfaces con la red de la maquina (RESUELTO 2026-10-25, antes de desplegar)
- **Severidad propuesta:** ALTA (seguridad; no llego a ningun servidor)
- **Que pasa:** `docker-compose.yml` usaba `ASPNETCORE_HTTP_PORTS: 8080` con `network_mode: host`: C# quedaba abierto en
  0.0.0.0:8080 y se podia llegar sin pasar por Nginx (sin el reparto por ruta ni la IP real de Cloudflare).
- **Resuelto:** `ASPNETCORE_URLS=http://127.0.0.1:8080`, como PHP-FPM detras de su socket. C# `90d30bd` (M1.5.1).

### BUG-466: Con `TRUSTED_PROXIES` vacio, C# confiaba en cualquier proxy (RESUELTO 2026-10-25, antes de desplegar)
- **Severidad propuesta:** ALTA (seguridad; no llego a ningun servidor)
- **Que pasa:** en ASP.NET, `ForwardedHeadersOptions` con las listas de proxies y redes vacias confia en todos: cualquier
  cliente podia inventar su IP con `X-Forwarded-For` y saltarse los limites por red. PHP con la lista vacia no confia en nadie.
- **Como se encontro:** un control de M1.4.3 (romper la lista de rangos) dejo pasar un `X-Forwarded-For` de afuera.
- **Resuelto:** lista vacia = no se leen esos encabezados, con su prueba (`Sin_lista_no_se_confia_en_ningun_proxy`) y
  control. C# `54178fa`.

### BUG-465: Las rutas GET de C# respondian 405 a HEAD; en Laravel responden (RESUELTO 2026-10-25, antes de desplegar)
- **Severidad propuesta:** MEDIA (monitores de salud y algunos clientes usan HEAD)
- **Que pasa:** `MapGet` de ASP.NET no atiende HEAD; `Route::get` de Laravel registra GET y HEAD.
- **Como se encontro:** la prueba con Nginx real de M1.5.1 (`scripts/probar-proxy-local.sh`).
- **Resuelto:** `HeadComoLaravel` (cada GET suma HEAD, sin cuerpo) y su prueba. C# `90d30bd`.

### BUG-464: PHP elegia "el registro mas nuevo" solo por fecha: con empate, cualquiera (RESUELTO 2026-10-25)
- **Severidad propuesta:** MEDIA (con dos registros de la persona creados el mismo segundo, el ban o la aprobacion se
  podian revisar sobre el evento equivocado)
- **Que pasa:** `SocketAuthCache::asistenteDe`, `EventoDeLaPeticion::asistente` y `Attendee::activeBan` ordenaban solo con
  `latest()` (`created_at`); MySQL elige cualquiera entre empates (regla 9).
- **Como se encontro:** al portar `/auth/me` y `check.ban` a C# (M1.3.4), que necesitan el mismo orden en los dos lados.
- **Resuelto:** `->latest()->latest('id')` en los tres; C# ordena igual. Backend `28f9daa`; suite PHP 1.257 en verde.

## 2026-10-25 — Encontrados al arrancar la fase 1 (M1.1.4 y parche de PHP)

### BUG-463: Pruebas de PHP con datos que MySQL rechaza (RESUELTO 2026-10-25)
- **Severidad propuesta:** BAJA (solo pruebas; el codigo de la app estaba bien)
- **Que pasa:** Al correr la suite en MySQL (M1.2.6): `session_type 'talk'` en SessionThumbnailTest; `banned_by` con id de asistente (es de usuario) en 3 pruebas; `assigned_by` con id de usuario (es de asistente) en StandsYCheckinTest; un canje pendiente creado sin subir `redeemed_count`, que al vencer bajaba a negativo (en MySQL la columna es sin signo y explota). El flujo real sube el contador al canjear y Filament no lo deja editar: no pasa en produccion.
- **Resuelto 2026-10-25:** pruebas con datos validos. Backend `f134c56`.

### BUG-462: El codigo PHP revisaba roles de asistente que no existen (`organizer`, `moderator`) (RESUELTO 2026-10-25)
- **Severidad propuesta:** MEDIA (codigo muerto que aparentaba permisos)
- **Que pasa:** `attendees.role` (enum de MySQL) solo admite attendee, vendedor y los admin*. QuestionController, RatingController, SessionQuestionResource, AdminDelEvento::EN_VIVO, EnlaceDeOperador::OPERADORES y TokenDeOperador aceptaban `organizer` y `moderator`: nunca se cumplia, solo `admin` entraba. `moderator` existe pero como rol de staff (spatie), otro sistema. El autor de una pregunta anonima se mostraba "si es admin" con roles inexistentes: en produccion siempre quedaba oculto.
- **Fuente:** suite en MySQL (M1.2.6): las pruebas creaban asistentes `organizer` que SQLite aceptaba.
- **Resuelto 2026-10-25:** fuera esos valores, sin cambio de comportamiento; 3 pruebas pasan a `admin`. El rol Organizador para el cliente (solo lectura) se hace en el admin nuevo, M2.2.6 (decisiones.md). Backend `d91ed89`.

### BUG-461: Listas sin orden fijo que MySQL devuelve distinto (RESUELTO 2026-10-25)
- **Severidad propuesta:** MEDIA
- **Que pasa:** Rankings con empate y conteos agrupados sin `ORDER BY`: en MySQL salen en otro orden que en SQLite. Rutas: `data-center/{id}/stats` (sesiones top, distribucion de calificaciones, publicadores top), `sessions/{id}/stats` (`qna.by_status`), `sessions/{id}/export` (preguntas a la misma hora).
- **Fuente:** primera corrida de los contratos contra MySQL.
- **Resuelto 2026-10-25:** orden explicito en todo el panel de `ExportService::stats` (16 consultas: rankings con desempate por id ascendente, conteos por su clave), `SessionStatsService` (`by_status` por estado, linea de tiempo por hora e id) y `PointsService::leaderboard` (empate por asistente). Cambia un snapshot: en publicadores top con empate sale primero el id menor (Bruno antes que Fabio). Decision en `decisiones.md`.

### BUG-460: MySQL reordena las llaves de las columnas JSON y los snapshots comparaban ese orden (RESUELTO 2026-10-25)
- **Severidad propuesta:** BAJA (solo pruebas)
- **Que pasa:** Trivia y jackpot devuelven objetos guardados en columnas JSON; MySQL los guarda con las llaves reordenadas (`correct` antes de `options`).
- **Resuelto 2026-10-25:** `normalizar()` ordena las llaves de los objetos; las listas se comparan estrictas. Snapshots regenerados en commit aparte (backend `21ecc17`). Mismo caso en la columna `meta` de los eventos de seguridad (4 pruebas a `toEqual`, `955cd6f`) con `scripts/comparar-snapshots.py`: 191 cambiados, 191 solo en el orden de llaves, 0 con otro cambio; control con un valor alterado: rojo.

### BUG-459: En produccion `total_points` del leaderboard sale como texto ("75") (RESUELTO 2026-10-25)
- **Severidad propuesta:** MEDIA
- **Que pasa:** `PointsService::leaderboard` devuelve `SUM()` crudo; MySQL lo entrega como texto. La webapp lo declara numero y llama `toLocaleString("es-CO")`: con texto no pone separador de miles (3250 en vez de 3.250). La app (Expo) lo muestra igual; kiosko no lo usa; el Pulse usa otra ruta que ya convertia. Se revisaron los demas `SUM`/`AVG`/`->sum()` del backend: todos convierten o solo comparan.
- **Resuelto 2026-10-25:** `(int)` en `leaderboard()`. Prueba roja: contrato de `leaderboard` y `leaderboard/shared` contra MySQL; control: 302 en verde en MySQL y SQLite.

### BUG-458: El seeder de contratos tenia datos que MySQL rechaza, y los contratos solo corrian en SQLite (RESUELTO 2026-10-25)
- **Severidad propuesta:** ALTA (los snapshots fijaban respuestas imposibles en produccion)
- **Que pasa:** `session_type = 'talk'` (el enum de MySQL no lo tiene) y un `auth_token` de totem de 66 caracteres (la columna admite 64). SQLite no valida enums ni largos.
- **Fuente:** primera siembra de `ContratoSeeder` en MySQL para el modo contratos de C# (M1.1.5).
- **Resuelto 2026-10-25:** seeder con `panel` y token de 64; los contratos corren contra MySQL en una base `*_pruebas` (la proteccion de BUG-344 sigue: otra base MySQL se rechaza, control con `eventos_db`); en MySQL se vacian solo las tablas usadas (`tests/TruncaSoloLoUsado.php`, ~1 s por prueba en el MSI) y la siembra va en una transaccion. Cambia un snapshot (`pulse/rooms`: `talk` a `panel`).

### BUG-457: PHP2Shell (CVE-2026-109618 y CVE-2026-109619) en el servidor real sin parchar (ABIERTO, servidor en pausa)
- **Severidad propuesta:** CRITICA
- **Que pasa:** Ejecucion de codigo sin sesion en PHP (CVSS 9,2, publicada 2026-10-21). Corregida en 8.3.36, 8.4.27, 8.5.12. `docs/infra/deploy.sh` instala `php8.3-*` por apt y el worker usa `php:8.3-cli-alpine`.
- **Fuente:** aviso de Kamilo 2026-10-25; php.net ChangeLog 8.4.27.
- **Hecho 2026-10-25:** MSI 8.4.26 a 8.4.27 (apt); Windows Laragon/Apache 8.3.26 a 8.3.36 (zip oficial verificado con sha256, `php_redis` copiada de 8.3.26, la 8.3.26 queda para volver); PHP de la terminal 8.4.12 a 8.4.27. Contratos 302 y suite 1257 en verde.
- **Falta:** al prender el servidor real, antes de todo: `apt upgrade` (php8.3 a 8.3.36), `systemctl reload php8.3-fpm` y reconstruir la imagen del worker.

### BUG-456: Los contratos solo pasaban con el `.env` de Kamilo; en una copia limpia fallaban los 302 (RESUELTO 2026-10-25)
- **Severidad propuesta:** ALTA (bloqueaba M1.1.4)
- **Que pasa:** Los snapshots salen de valores del `.env` local que `.env.example` trae distinto: `APP_TIMEZONE` (UTC contra Bogota), `APP_LOCALE` (en contra es), `REDIS_CLIENT` (phpredis, que el MSI no tiene) y `VAPID_PUBLIC_KEY` (vacia).
- **Fuente:** primera corrida de `tests/Contratos` en el MSI con un clon limpio.
- **Resuelto 2026-10-25 (M1.1.4):** `phpunit.xml` fija los cuatro (la llave VAPID es la publica, igual a la del snapshot; ningun snapshot cambia). Prueba roja: clon limpio 302 fallidos; control: el mismo clon con `.env.example` de fabrica, 302 en verde en los dos ordenes; suite completa 1257 en verde. Backend `b27279f`.

## 2026-10-23 — Encontrados al arreglar M0.8.6

### BUG-455: La webapp usa un evento fijo (`DEFAULT_SLUG`) en vez del evento de la persona, como hace Expo (RESUELTO 2026-10-24)
- **Severidad propuesta:** MEDIA
- **Que pasa:** Expo toma el evento del asistente de la sesion (`user.eventId`, `eventos-app/app/(app)/_layout.tsx:20`) y si no hay asistente no pide nada. La webapp siempre pide `DEFAULT_SLUG` (`eventos-web/src/app/[locale]/(app)/layout.tsx:51`) y sus gates solo miran ban y aprobacion (`src/lib/auth.ts:144`). Alguien con sesion de otro evento entra igual: antes veia el contenido (BUG-426); desde M0.8.6 le llega 403 `NOT_IN_EVENT` y ve secciones vacias o el mensaje generico de error (`src/lib/apiErrors.ts:87`).
- **Fuente:** pregunta de Kamilo al revisar M0.8.6 ("en Expo estaba contemplado"), verificado leyendo los dos layouts.
- **Arreglo propuesto:** la webapp toma el evento de la persona como Expo; si no tiene asistente en ningun evento, la misma salida que baneado o pendiente (pantalla con lab antes de codear).
- **Archivos:** `eventos-web/src/app/[locale]/(app)/layout.tsx`, `eventos-web/src/lib/auth.ts`, `eventos-web/src/lib/publicEvent.ts`
- **Resuelto 2026-10-24 (M0.6.1):** La webapp toma el evento de la persona (`event_slug` de /auth/me, `fetchMyEvent`) en toda el area autenticada y en push/showcase; sesion sin asistente en ningun evento activo va a `/sin-inscripcion` (lab M0.6.1 opcion A: Noir con borde, Lux con sombra sin borde). Prueba `tests/lib/miEvento.test.ts` (3, con control) y QA con Playwright en Noir y Lux (redireccion y control con asistente). Webapp `cee7ec6`, `b02d206`.

### BUG-454: Sin sesion, las descargas del admin (`/admin/...`) daban 500 en vez de mandar al login (RESUELTO 2026-10-23)
- **Severidad propuesta:** BAJA
- **Que pasa:** El middleware `auth` buscaba la ruta `login`, que no existe (el login es el de Filament): `Route [login] not defined`.
- **Fuente:** al probar la descarga firmada de exports (BUG-408, M0.8.6).
- **Resuelto 2026-10-23 (M0.8.6):** `redirectGuestsTo` al login del panel (la API sigue en 401). Prueba `ExportesPrivadosTest.php`. Backend `9980228`.

### BUG-453: El export de asistencia por sala apuntaba a una ruta `export.download` que no existe (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA
- **Que pasa:** `ExportRoomAttendanceJob` armaba el aviso con `route('export.download')`: el aviso fallaba y nadie recibia el archivo.
- **Fuente:** al mover los exports al disco privado (BUG-408, M0.8.6).
- **Resuelto 2026-10-23 (M0.8.6):** usa la descarga firmada de `App\Support\Exportes`. Backend `9980228`.

## 2026-10-23 — Auditoria de seguridad M0.8.1 (57 nuevos, BUG-396 a BUG-452)

> Auditoria estandar de eventos-backend (`a23884a`) y eventos-socket (`c090f80`), solo lectura de codigo, 54 agentes.
> 65 pistas, todas `needs_validation`: el camino esta en el codigo y lo revisaron dos agentes independientes, pero sin
> sandbox no hubo prueba que lo muestre. Por eso van como **por verificar con prueba**; la prueba que falle se escribe
> al arreglar. 8 pistas ya tenian numero (BUG-373, 374, 376, 385, 386, 387, 389, 391: se les agrego la nota).
> Informe: `~/security-audit-skill/eventos-backend/run-1/REPORT.md` y `NEEDS-VALIDATION.md` (fuera del repo, no se sube).
> Prioridad sugerida -> tarea: A (cruce entre eventos, toma de cuenta o de rol, codigo en el origen del admin) -> M0.8.6;
> B (datos personales o controles dentro del evento) -> M0.8.7; C (puntos, conteos, endurecimiento) -> M0.8.8.
> Nuevos: A 20, B 21, C 16.
> **Validacion 2026-10-23: las 65 CONFIRMADAS con prueba, 0 descartadas** (eventos-backend `tests/Auditoria`, rama
> `auditoria/validacion`; el socket con `tests/Auditoria/navegador/socket-auditoria.cjs`). BUG-452 (bloquear no corta
> contactos e historias): Kamilo decidio que bloquear corta todo, como si nunca hubieran sido contactos (`decisiones.md`).

### BUG-452: Bloquear a un contacto aceptado no le quita el acceso: sigue en Mis contactos y sigue viendo las historias de quien lo bloqueo (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Cuando el asistente B bloquea al asistente A, con quien ya tenia un contacto aceptado, block() crea el ContactBlock pero solo borra las ContactRequest pendientes. La solicitud aceptada sigue existiendo. GET /me/contacts (myContacts) y GET /events/{eventId}/stories (AttendeeStoryController::index) dan acceso con solo esa solicitud aceptada y no consultan ContactBlock.
- **Fuente:** auditoria de seguridad M0.8.1, pista #65 (`eventos-backend/NetworkingController.block:accepted-contact-survives-block`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/StandsYNetworkingTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/networking.php`, `backend: app/Http/Controllers/Api/V1/NetworkingController.php`, `backend: app/Http/Controllers/Api/V1/AttendeeStoryController.php`, `backend: app/Models/ContactBlock.php`, `backend: routes/api/events.php`
- **Decision Kamilo 2026-10-23:** bloquear corta todo en las dos direcciones, como si nunca hubieran sido contactos: nada en mis contactos, historias, directorio, sugerencias ni perfil, y sin solicitudes nuevas. El arreglo: `block()` borra tambien la solicitud aceptada (o `myContacts` e historias filtran `ContactBlock`), y `respondRequest` revisa el bloqueo antes de aceptar.
- **Resuelto 2026-10-23 (M0.8.7):** Bloquear borra la solicitud entre los dos en cualquier estado (tambien la aceptada): sale de contactos e historias en las dos direcciones. Prueba `tests/Feature/Security/BloqueoYBanTest.php`. Backend `1e044de`.

### BUG-451: checkin:update reparte a todo asistente conectado el nombre, rol, id y stand de cada persona que hace check-in, y session:audience reparte hasta 20 espectadores por sesion (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** CheckinService::broadcastCheckin manda en cada check-in de puerta el asistente con id, nombre, rol y stand (CheckinService.php:220-225) y el socket lo emite como checkin:update a event:{id} (index.ts:55), sala comentada como 'kiosk + admin' (54) pero a la que entra cualquier asistente con solo coincidir el evento del token (index.ts:675-683).
- **Fuente:** auditoria de seguridad M0.8.1, pista #64 (`eventos-socket:fanout:attendee-identity-to-shared-rooms`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/navegador/socket-auditoria.cjs`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `socket: src/index.ts`, `backend: app/Services/CheckinService.php`, `socket: src/chat.ts`, `backend: app/Http/Controllers/Api/V1/NetworkingController.php`, `backend: app/Filament/Resources/AttendeeAdminResource.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.7):** checkin:update a la sala solo con conteos (attendee vacio para apps instaladas); Pulse y la propia persona reciben el detalle; session:audience con nombres solo al staff. Socket `2f99985`, arnes del socket (rojo con el viejo).

### BUG-450: La asistencia virtual que decide el certificado y el nivel sale de la sala del socket sin atarse al horario, al estado ni a una sesion a la vez: unirse a muchas sesiones infla sesiones y horas (RESUELTO 2026-10-24)
- **Severidad propuesta:** BAJA (prioridad C de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** El recap y su tarjeta 'Certificado de asistencia' salen de session_attendances con source 'app', que FlushSessionAttendanceJob arma desde los hashes Redis session:{id}:joined|left que escribe el socket. join:session (eventos-socket/src/chat.ts:252) para un rol no admin solo pide a Laravel que la sesion sea del evento del token (InternalChatController.php:162-164): no mira cancelled_at, horario ni otras sesiones abiertas, y escribe la entrada con la hora actual (chat.ts:299).
- **Fuente:** auditoria de seguridad M0.8.1, pista #63 (`eventos-backend:recap-attendance:socket-presence-duration-unbounded`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/navegador/socket-auditoria.cjs`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.8 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `socket: src/chat.ts`, `backend: app/Http/Controllers/Web/InternalChatController.php`, `socket: src/index.ts`, `backend: app/Jobs/FlushSessionAttendanceJob.php`, `backend: app/Services/RecapService.php` (y mas)
- **Resuelto 2026-10-24 (M0.8.8):** La presencia cuenta en una sesion a la vez (`presencia:{asistente}` en Redis cierra la anterior, socket `57f3e1e`); FlushSessionAttendanceJob recorta al horario efectivo y no cuenta sesiones canceladas ni tiempo fuera de horario. Prueba `tests/Feature/Security/PuntosJuegosYChatTest.php` y arnes del socket. Backend `7f20329`.

### BUG-449: Las preguntas de Q&A pendientes de moderacion (texto y autor) se reparten a toda la sala de la sesion (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** QuestionController::store crea la pregunta con status 'pending' (91) y la difunde como question:submitted (100) con id, texto, estado, votos y nombre del autor; el autor solo va nulo si la pregunta es anonima (222).
- **Fuente:** auditoria de seguridad M0.8.1, pista #62 (`eventos-backend:qna:pending-question-broadcast-to-session-room`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/SocketLadoLaravelTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: app/Http/Controllers/Api/V1/QuestionController.php`, `socket: src/index.ts`, `socket: src/chat.ts`, `backend: public/mission-control/app.js`, `backend: public/chat-monitor.html`
- **Resuelto 2026-10-23 (M0.8.7):** Pregunta sin moderar con audiencia staff; el socket solo la manda a admins y moderadores (socket `2f99985`). Prueba `DatosExpuestosTest.php` y arnes `socket-auditoria.cjs`. Backend `0df110a`.

### BUG-448: Borrar un mensaje del chat por la API (DELETE /admin/chat/messages/{id}) no lo saca del historial que el socket reenvia a cada persona que entra (RESUELTO 2026-10-24)
- **Severidad propuesta:** BAJA (prioridad C de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** El chat tiene dos copias: MySQL (fuente de GET /sessions/{id}/chat/messages, que excluye borrados en ChatController.php:35) y la lista Redis chat:history:session:{id} con los ultimos 20 mensajes y sin vencimiento (chat.ts:145-149), que join:session reenvia a cada socket que entra (280-282) y que eventos-web muestra (useChat.ts:340). El borrado por socket (chat:delete) emite chat:deleted (457) y quita la entrada de Redis (468).
- **Fuente:** auditoria de seguridad M0.8.1, pista #61 (`eventos-backend:chat:rest-delete-keeps-socket-history-copy`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/SocketLadoLaravelTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.8 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `socket: src/chat.ts`, `backend: app/Http/Controllers/Api/V1/ChatController.php`, `backend: routes/api/chat.php`, `eventos-web/src/hooks/streaming/useChat.ts`, `backend: tests/Feature/Chat/ChatTest.php`
- **Resuelto 2026-10-24 (M0.8.8):** Al borrar por la API, Laravel llama a `/internal/chat/delete-message` del socket, que lo saca del historial y avisa chat:deleted (socket `ead3c31`). Prueba `tests/Feature/Security/DatosYContenidoTest.php` y arnes `socket-auditoria.cjs`. Backend `6c4abd8`.

### BUG-447: /internal/session-config acepta un secreto vacio si INTERNAL_SECRET no esta definido (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Verificado en el codigo. Los otros 15 endpoints internos del socket rechazan la peticion si config.internalSecret esta vacio, y leen el secreto de la cabecera X-Internal-Secret antes de leer el cuerpo. /internal/session-config lo toma del cuerpo JSON, despues de leer el cuerpo completo, y compara `secret !== config.internalSecret` sin esa guardia.
- **Fuente:** auditoria de seguridad M0.8.1, pista #60 (`eventos-socket:internal:session-config-body-secret-fail-open`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/navegador/socket-auditoria.cjs`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `socket: src/index.ts`, `socket: src/config.ts`, `socket: .env.example`, `backend: app/Console/Commands/SecurityCheckCommand.php`, `backend: app/Http/Controllers/Api/V1/Admin/SessionConfigController.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.7):** /internal/session-config ya no acepta secreto vacio (socket `eab5820`). Arnes del socket con control.

### BUG-446: Un evento de socket sin payload tumba el proceso del servidor (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Verificado en el codigo. Los manejadores desestructuran el payload en la firma y no validan su forma. En Socket.IO 4.8.3, Socket.dispatch llama a los oyentes dentro de process.nextTick y sin try/catch, a traves de EventEmitter de node:events. Correccion: no es @socket.io/component-emitter, que solo usa socket.io-parser. Una excepcion sincronica en un oyente se vuelve uncaughtException, y src no registra ningun process.on.
- **Fuente:** auditoria de seguridad M0.8.1, pista #59 (`eventos-socket:events:unvalidated-payload-crashes-worker`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/navegador/socket-auditoria.cjs`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `socket: src/index.ts`, `socket: node_modules/socket.io/dist/socket.js`, `socket: node_modules/socket.io/dist/typed-events.js`, `socket: ecosystem.config.js`, `socket: src/chat.ts` (y mas)
- **Resuelto 2026-10-23 (M0.8.6):** `socket.use` descarta eventos sin payload. Socket `1f148dd`, arnes del socket.

### BUG-445: Un mensaje de un remitente sin asistente (Pulse o cuenta sin evento activo) hace fallar el guardado en lote de los mensajes de otros (RESUELTO 2026-10-24)
- **Severidad propuesta:** BAJA (prioridad C de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Verificado en el codigo. chat:send no exige attendeeId > 0. Lo pueden enviar un visor de Event Pulse (token ep_ de un panel que la API declara de solo lectura, con attendeeId 0, que puede unirse a las sesiones de su evento) o una cuenta sin asistente activo. persistMessage encola el mensaje con attendee_id 0 en el lote del proceso, que junta los mensajes de todas las sesiones de ese proceso en la ventana de 200 ms.
- **Fuente:** auditoria de seguridad M0.8.1, pista #58 (`eventos-socket:chat:attendee-zero-sender-poisons-persist-batch`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/SocketLadoLaravelTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.8 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `socket: src/chat.ts`, `backend: app/Http/Controllers/Web/InternalChatController.php`, `backend: database/migrations/2026_03_28_200015_create_chat_and_polls_tables.php`, `socket: src/auth.ts`, `backend: routes/api.php`
- **Resuelto 2026-10-24 (M0.8.8):** El socket no deja escribir sin asistente (CHAT_NOT_ATTENDEE, socket `431ff50`); Laravel salta del lote los remitentes que no son del evento de la sesion y el guardado individual responde 422. Prueba `tests/Feature/Security/PuntosJuegosYChatTest.php`. Backend `a8b6172`.

### BUG-444: Las acciones de moderacion y proyeccion del socket no se atan al evento de la sesion (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Verificado en el codigo. Un asistente con rol admin* de un evento A (admin, admin_content, admin_notifs o admin_analytics) puede unirse desde el socket a sesiones de cualquier evento B, porque join:session no valida la sesion para los roles admin*. Una vez en la sala de B puede borrar mensajes (se emite chat:deleted y se borra en la base de B; si el mensaje no esta en el historial de Redis, borrarMensaje borra el ultimo de la sala), anclar y desanclar.
- **Fuente:** auditoria de seguridad M0.8.1, pista #57 (`eventos-socket:chat:admin-actions-not-bound-to-session-event`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/navegador/socket-auditoria.cjs`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `socket: src/chat.ts`, `backend: app/Http/Controllers/Web/InternalChatController.php`, `backend: app/Http/Controllers/Api/V1/ChatController.php`, `backend: app/Services/GameService.php`, `backend: app/Http/Controllers/Api/V1/Admin/GameController.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.6):** join:session valida el evento tambien para admins y la proyeccion pide estar en la sesion. Socket `1f148dd`, arnes del socket.

### BUG-443: Los tokens revocados en bloque siguen abriendo el socket hasta 24 horas (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Verificado en el codigo; este candidato incluye el duplicado fusionado eventos-backend:auth:password-reset-bulk-token-delete-skips-socket-revocation. El socket acepta un token si encuentra su ficha socketauth:t:{hash} en Redis. No consulta personal_access_tokens ni expires_at, y le renueva el TTL de 15 minutos en cada lectura, con un tope de 24 h desde iat. La limpieza depende solo del evento de modelo PersonalAccessToken::deleted (AppServiceProvider.php:90-94).
- **Fuente:** auditoria de seguridad M0.8.1, pista #55 (`eventos-socket:auth:shared-ficha-survives-mass-token-revocation`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/SocketLadoLaravelTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: app/Http/Controllers/Api/V1/AuthController.php`, `backend: app/Providers/AppServiceProvider.php`, `socket: src/index.ts`, `socket: src/auth.ts`, `backend: vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.7):** `App\Models\PersonalAccessToken`: el borrado en bloque limpia primero las fichas. Prueba `CuentasYTokensTest.php`. Backend `5636d38`.

### BUG-442: El token de solo lectura del display, y el acotado de Mission Control, dan poderes de admin en el socket (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Verificado en el codigo. En Laravel, TokenDeOperador limita el token del display de sala a GET auth/me y GET admin/polls/{poll}/results (BUG-370), y el de Mission Control a sus rutas, su evento y su sesion. El socket no conoce esos permisos. SocketAuthCache::put guarda la ficha del dueño sin abilities, y auth.ts arma la identidad con el asistente activo mas reciente del dueño.
- **Fuente:** auditoria de seguridad M0.8.1, pista #54 (`eventos-socket:auth:operator-token-abilities-ignored`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/SocketLadoLaravelTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: app/Http/Controllers/Web/OperadorController.php`, `backend: app/Support/TokenDeOperador.php`, `backend: app/Services/SocketAuthCache.php`, `backend: public/display/session.html`, `socket: src/auth.ts` (y mas)
- **Resuelto 2026-10-23 (M0.8.6):** La ficha del socket y /auth/me llevan el alcance del token de operador (evento y sesion); el display entra con rol "display". Backend `5e68371`, socket `1f148dd`. Prueba `SocketFichaYAlcanceTest.php` y arnes del socket.

### BUG-441: Una cuenta sin asistente en un evento activo recibe el rol 'admin' en el socket y puede moderar y proyectar en cualquier sesion (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Verificado en el codigo. Si la ficha de Redis (SocketAuthCache::buildPayload) o GET /auth/me traen attendee null, auth.ts arma la identidad con role 'admin', eventId 0 y attendeeId 0 (lineas 61-65 y 196-200). attendee es null cuando el usuario no tiene asistente en un evento con is_active=true. El codigo muestra tres caminos.
- **Fuente:** auditoria de seguridad M0.8.1, pista #53 (`eventos-socket:auth:default-admin-role-without-attendee`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/navegador/socket-auditoria.cjs`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: app/Http/Controllers/Api/V1/AuthController.php`, `backend: app/Models/User.php`, `backend: app/Services/SocketAuthCache.php`, `socket: src/auth.ts`, `socket: src/chat.ts` (y mas)
- **Resuelto 2026-10-23 (M0.8.6):** Sin asistente el rol en el socket es "none". eventos-socket `1f148dd`. Arnes `tests/Auditoria/navegador/socket-auditoria.cjs` con control (el admin legitimo opera su sesion).

### BUG-440: Un registro pendiente de aprobacion entra al socket con acceso completo al evento (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Verificado en el codigo. En un evento con registration_requires_approval, CheckApproval responde 403 en las rutas del evento, incluido el chat HTTP, hasta que un admin aprueba. Sin embargo, POST /api/v1/auth/register le entrega el token al pendiente en la misma respuesta y escribe su ficha. Por la landing, el token se borra pero se envia un enlace magico, y verifyMagicLink emite el token sin revisar la aprobacion. El socket no consulta registration_approved_at.
- **Fuente:** auditoria de seguridad M0.8.1, pista #52 (`eventos-socket:auth:approval-gate-not-enforced`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/SocketLadoLaravelTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/auth.php`, `backend: app/Services/AuthService.php`, `backend: app/Services/SocketAuthCache.php`, `socket: src/index.ts`, `socket: src/chat.ts` (y mas)
- **Resuelto 2026-10-23 (M0.8.7):** Ficha con attendee.pending; el socket rechaza la conexion (socket `dbe2713`). Prueba `PendientesDeAprobacionTest.php` y arnes del socket. Backend `b2dd075`.

### BUG-439: El lider de un stand obtiene correo, empresa y rol de cualquier asistente del evento sin que este haya dado su consentimiento (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Un asistente que el staff designa como lider (owner_attendee_id) de un patrocinador puede llamar GET /api/v1/me/stand/search-attendees. El endpoint busca entre todos los asistentes del evento con user.name LIKE '%q%' (q de 2 caracteres o mas, sin escapar % ni _) y no filtra networking_visible ni ContactBlock. Devuelve email, company, photo_url y role: 15 por consulta, sin orden ni paginacion. q='%%' devuelve siempre los mismos 15.
- **Fuente:** auditoria de seguridad M0.8.1, pista #50 (`eventos-backend:stand:owner-harvests-attendee-contact-pii`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/StandsYNetworkingTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/stand.php`, `backend: app/Http/Controllers/Api/V1/StandController.php`, `backend: app/Http/Resources/V1/StandMemberResource.php`, `backend: app/Services/StaffNotificationService.php`, `backend: app/Http/Controllers/Api/V1/NetworkingController.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.7):** Busqueda del lider sin ocultos ni bloqueos, sin rol y con correo enmascarado. Prueba `DatosExpuestosTest.php`. Backend `0df110a`.

### BUG-438: El cupo de colaboradores del stand no se revalida al aceptar y ni el lider ni el staff pueden retirar invitaciones pendientes (RESUELTO 2026-10-24)
- **Severidad propuesta:** BAJA (prioridad C de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** El staff fija max_collaborators por patrocinador (de 1 a 20, por defecto 4). addMember y generateShareLink solo comparan los miembros activos al crear la invitacion, sin contar las pendientes, y acceptInvitation no revalida el cupo. Al aceptar, activa al miembro y le da has_vendor_access y sponsor_id, que abren el pool de leads del stand: la lista con correo, telefono, empresa y cargo, la exportacion CSV y la captura por QR.
- **Fuente:** auditoria de seguridad M0.8.1, pista #49 (`eventos-backend:stand:invitation-cap-not-enforced-on-accept`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/StandsYNetworkingTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.8 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/stand.php`, `backend: app/Http/Controllers/Api/V1/StandController.php`, `backend: app/Filament/Resources/SponsorResource.php`, `backend: app/Http/Controllers/Api/V1/LeadController.php`, `backend: database/migrations/2026_04_15_100000_add_staff_invitation_fields.php` (y mas)
- **Resuelto 2026-10-24 (M0.8.8):** El cupo se revalida al aceptar, con la fila del stand bloqueada (422 STAND_FULL). **Queda fuera:** retirar invitaciones pendientes no tiene pantalla; va al admin nuevo (M2.8.5). Prueba `tests/Feature/Security/DatosYContenidoTest.php`. Backend `6c4abd8`.

### BUG-437: Cambiar o quitar el lider del stand desde el admin no le quita el acceso al pool de leads: el lider anterior, y cualquiera elegido de paso en el selector, sigue listando y exportando los leads con correo y telefono (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** En EditSponsor, el selector 'Lider' tiene live() y afterStateUpdated. Cada vez que el staff elige a alguien, ese asistente recibe has_vendor_access=true y el sponsor_id del stand, aunque el staff no guarde y aunque despues abandone el formulario sin guardar. Al cambiar de lider o vaciar el campo, nadie le quita esas banderas al anterior ni a quien se eligio de paso.
- **Fuente:** auditoria de seguridad M0.8.1, pista #48 (`eventos-backend:stand-leads:leader-select-grants-vendor-access-without-revocation`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/StandsYNetworkingTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: app/Filament/Resources/SponsorResource.php`, `backend: app/Filament/Resources/SponsorResource/Pages/EditSponsor.php`, `backend: app/Http/Controllers/Api/V1/LeadController.php`, `backend: vendor/filament/forms/src/Concerns/InteractsWithForms.php`, `backend: resources/views/filament/forms/sponsor-team-members.blade.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.7):** SponsorObserver: acceso al guardar el lider y se le quita al anterior salvo que siga en el equipo. Prueba `StandsYCheckinTest.php`. Backend `8c64c30`.

### BUG-436: Registrar la vista de un patrocinador no valida que sea del evento ni deduplica, e infla las estadisticas de patrocinadores de otros eventos (RESUELTO 2026-10-24)
- **Severidad propuesta:** BAJA (prioridad C de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** POST /api/v1/events/{eventId}/sponsors/{sponsorId}/view solo exige que el usuario sea asistente de eventId. Guarda un ActivityLog con event_id = eventId y target_id = sponsorId sin comprobar que el patrocinador pertenezca a ese evento ni que este habilitado, y sin deduplicar por asistente. Las 'Visitas al perfil' que ve el patrocinador en /me/stand/stats y en el CSV de su stand se cuentan por target_id, sin filtrar por event_id ni por asistentes distintos.
- **Fuente:** auditoria de seguridad M0.8.1, pista #47 (`eventos-backend:sponsors:record-view-not-bound-to-event`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/StandsYNetworkingTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.8 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/sponsors.php`, `backend: app/Http/Controllers/Api/V1/SponsorController.php`, `backend: app/Http/Controllers/Api/V1/StandController.php`, `backend: app/Http/Controllers/Api/V1/LeadController.php`, `backend: app/Providers/AppServiceProvider.php`
- **Resuelto 2026-10-24 (M0.8.8):** La vista exige que el patrocinador sea del evento (404) y cuenta una por persona cada 30 minutos. Prueba `tests/Feature/Security/DatosYContenidoTest.php`. Backend `6c4abd8`.

### BUG-435: Trivia en vivo: el puntaje usa el tiempo que manda el cliente y la ronda sigue abierta despues de revelar la respuesta (RESUELTO 2026-10-24)
- **Severidad propuesta:** BAJA (prioridad C de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Cualquier asistente autenticado y aprobado del evento del juego manda POST /api/v1/events/games/{id}/answer con cuerpo JSON {option: <correcta como entero>, time_elapsed: -20000}. La opcion debe ir como entero JSON porque la comparacion es estricta (GameController.php:416).
- **Fuente:** auditoria de seguridad M0.8.1, pista #46 (`eventos-backend:gamification:trivia-client-asserted-time-score`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AsistenteYLogicaTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.8 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/events.php`, `backend: app/Http/Controllers/Api/V1/Admin/GameController.php`, `backend: app/Jobs/ProcessTriviaRewardsJob.php`, `backend: app/Services/GameService.php`, `backend: database/migrations/2026_04_21_194545_create_live_game_participants_table.php` (y mas)
- **Resuelto 2026-10-24 (M0.8.8):** `App\Support\RondaDeTrivia`: reloj de la ronda en el servidor (el tiempo del cliente se acota a [0, limite] y nunca es menor que el del servidor menos 2 s de red) y la ronda revelada responde 409. Prueba `tests/Feature/Security/PuntosJuegosYChatTest.php`. Backend `a8b6172`.

### BUG-434: Canje de premios: el saldo se calcula con SUM sin bloqueo y canjes simultaneos de premios distintos gastan los mismos puntos (RESUELTO 2026-10-24)
- **Severidad propuesta:** BAJA (prioridad C de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** RewardService::redeem abre una transaccion y bloquea solo la fila del premio pedido. El saldo del asistente es un SUM de points_log con lectura no bloqueante y la deduccion es un INSERT posterior. Si el mismo asistente pide a la vez dos premios distintos A y B, cada transaccion bloquea una fila diferente y ninguna espera a la otra.
- **Fuente:** auditoria de seguridad M0.8.1, pista #45 (`eventos-backend:gamification:reward-redeem-unlocked-balance`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AsistenteYLogicaTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.8 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/rewards.php`, `backend: app/Http/Controllers/Api/V1/RewardController.php`, `backend: app/Services/RewardService.php`, `backend: database/migrations/2026_04_23_095924_add_unique_index_to_points_log.php`
- **Resuelto 2026-10-24 (M0.8.8):** El canje bloquea la fila de la persona antes de leer el saldo y revisa que no quede negativo despues de descontar. Prueba `tests/Feature/Security/PuntosJuegosYChatTest.php`. Backend `a8b6172`.

### BUG-433: Acciones de una sola vez (completar intereses y check-in) se premian sin referencia y no se deduplican (RESUELTO 2026-10-24)
- **Severidad propuesta:** BAJA (prioridad C de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** PointsService::award solo busca duplicados si recibe reference_type y reference_id, y el indice unico points_log_unique_award incluye esas dos columnas nullable; con NULL, MySQL no las trata como duplicadas.
- **Fuente:** auditoria de seguridad M0.8.1, pista #44 (`eventos-backend:gamification:one-time-award-without-reference`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AsistenteYLogicaTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.8 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api.php`, `backend: app/Http/Controllers/Api/V1/OnboardingController.php`, `backend: app/Traits/AwardsPoints.php`, `backend: app/Services/PointsService.php`, `backend: app/Filament/Resources/GamificationSettingsResource.php` (y mas)
- **Resuelto 2026-10-24 (M0.8.8):** checkin y complete_interests sin referencia se guardan con referencia al evento: el chequeo y el indice unico los deduplican. Prueba `tests/Feature/Security/PuntosJuegosYChatTest.php`. Backend `7f20329`.

### BUG-432: La restriccion 'Quienes ganan estos puntos' del admin no se aplica al otorgar puntos (RESUELTO 2026-10-24)
- **Severidad propuesta:** BAJA (prioridad C de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** El organizador define por accion quienes ganan puntos (gamification_config[accion]['roles']). El panel guarda siempre esa lista al editar la accion y avisa 'Sin ninguno marcado, nadie gana esta accion'. PointsService::isEnabled solo aplica la lista si recibe un rol, pero tryAward lo llama sin rol y no hay otro llamador de isEnabled en app/.
- **Fuente:** auditoria de seguridad M0.8.1, pista #43 (`eventos-backend:gamification:award-ignores-role-restriction`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AsistenteYLogicaTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.8 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/events.php`, `backend: app/Http/Controllers/Api/V1/GamificationController.php`, `backend: app/Services/PointsService.php`, `backend: app/Filament/Resources/GamificationSettingsResource.php`
- **Resuelto 2026-10-24 (M0.8.8):** tryAward mira la lista de roles con el rol de la persona. Prueba `tests/Feature/Security/PuntosJuegosYChatTest.php`. Backend `7f20329`.

### BUG-431: staff-checkin/unassign deja a un staff de check-in bajar a 'attendee' a cualquier asistente del evento, incluidos los admins (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** POST /api/v1/events/staff-checkin/unassign usa requireAdmin, que acepta a quien canAssignStaff(), y eso incluye attendees.role 'staff_checkin' (la documentacion del endpoint dice que es solo para admin: QA-MASTER.md:862). El objetivo se busca solo por id dentro del evento de quien llama, sin mirar su rol ni si tiene asignaciones de salon.
- **Fuente:** auditoria de seguridad M0.8.1, pista #42 (`eventos-backend:checkin:staff-unassign-demotes-any-role`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AsistenteYLogicaTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/events.php`, `backend: app/Http/Controllers/Api/V1/StaffCheckinController.php`, `backend: app/Models/Attendee.php`, `backend: app/Support/Roles.php`, `backend: app/Http/Controllers/Api/V1/Admin/AttendeeController.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.7):** El staff de check-in no cambia a un admin; quitar la ultima sala solo devuelve a attendee a quien es staff_checkin; un admin que acepta sigue admin. Prueba `StandsYCheckinTest.php`. Backend `8c64c30`.

### BUG-430: El check-in de puerta acepta registros pendientes de aprobacion con el QR estatico que reciben al registrarse o iniciar sesion (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** En eventos con registration_requires_approval, AuthService::register crea el asistente con registration_approved_at = null y genera un QR estatico permanente. AuthController::register, login y activate devuelven AttendeeResource con qr_token aunque el registro siga pendiente.
- **Fuente:** auditoria de seguridad M0.8.1, pista #41 (`eventos-backend:checkin:pending-registration-checked-in`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AsistenteYLogicaTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/checkin.php`, `backend: app/Http/Controllers/Api/V1/CheckinController.php`, `backend: app/Services/CheckinService.php`, `backend: app/Services/AuthService.php`, `backend: app/Http/Controllers/Api/V1/AuthController.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.7):** Puerta: REGISTRATION_PENDING si falta aprobar; el kiosko lo muestra (kiosko `0f46ee3`). Prueba `PendientesDeAprobacionTest.php`. Backend `b2dd075`.

### BUG-429: Banear no borra la ficha de autenticacion del socket si el indice del usuario ya vencio: el baneado reconecta y sigue en el chat (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** SocketAuthCache guarda dos cosas: la ficha por token (socketauth:t:{hash}) y un indice por usuario (socketauth:u:{id}). forgetUser solo revoca a traves de ese indice. El indice nace con un TTL fijo de 1.800 s, en put() al emitir el token o en writeSharedAuth al volver de /auth/me.
- **Fuente:** auditoria de seguridad M0.8.1, pista #40 (`eventos-backend:attendee-api:socket-auth-index-expires-before-token`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/SocketLadoLaravelTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `socket: src/index.ts`, `socket: src/auth.ts`, `backend: app/Services/SocketAuthCache.php`, `socket: src/chat.ts`, `backend: app/Providers/AppServiceProvider.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.7):** `SocketAuthCache::forgetUser` encuentra las fichas tambien por los tokens de la base. Prueba `BloqueoYBanTest.php`. Backend `1e044de`.

### BUG-428: Quitar el rol de admin a un asistente no invalida su ficha del socket: conserva poderes de admin en el chat y en las pantallas (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** La ficha de SocketAuthCache copia attendee.role al emitir el token. Los cambios de rol no llaman a forgetUser ni borran la ficha: API Admin/AttendeeController::updateRole, la accion 'Cambiar rol' de Filament y StaffCheckinController (acceptAssignment y unassign). Attendee tiene dos observers, el de webhooks y DataCenterCacheObserver, y ninguno toca la ficha del socket. Solo el hook de AttendeeBan la invalida.
- **Fuente:** auditoria de seguridad M0.8.1, pista #39 (`eventos-backend:attendee-api:socket-auth-cache-role-change-not-invalidated`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/SocketLadoLaravelTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: app/Http/Controllers/Api/V1/Admin/AttendeeController.php`, `backend: app/Services/SocketAuthCache.php`, `socket: src/auth.ts`, `socket: src/chat.ts`, `backend: app/Providers/AppServiceProvider.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.7):** Cambiar rol, aprobacion o evento de un asistente borra su ficha del socket. Prueba `CuentasYTokensTest.php`. Backend `5636d38`.

### BUG-427: PUT /me/registration-fields deja cambiar campos que el organizador bloqueo ('Editable despues del registro' apagado) (RESUELTO 2026-10-24)
- **Severidad propuesta:** BAJA (prioridad C de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** El admin de campos promete: 'Apagado: el asistente no podra cambiarlo desde su perfil.' Pero updateMyFields considera escribible todo campo con show_in onboarding o both aunque editable_after sea false, y lo deja reescribir en cualquier momento, no solo en el primer llenado del onboarding. El docblock del metodo dice lo contrario: 'updates only editable_after=true fields'.
- **Fuente:** auditoria de seguridad M0.8.1, pista #38 (`eventos-backend:attendee-api:registration-field-lock-ignored`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AsistenteYLogicaTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.8 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api.php`, `backend: app/Http/Controllers/Api/V1/RegistrationFieldController.php`, `backend: app/Filament/Resources/RegistrationFieldResource.php`, `backend: app/Imports/AttendeesImport.php`, `backend: app/Http/Controllers/Api/V1/AuthController.php` (y mas)
- **Resuelto 2026-10-24 (M0.8.8):** Un campo con 'Editable despues del registro' apagado se llena una vez en el onboarding y despues no cambia. Prueba `tests/Feature/Security/DatosYContenidoTest.php`. Backend `6c4abd8`.

### BUG-426: Rutas de contenido del evento solo piden sesion: un asistente de cualquier otro evento lee enlaces de transmision, documentos, anuncios, paginas y preguntas (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Basta un token de cualquier evento. Por ejemplo, el que el registro publico de cualquier evento abierto entrega al instante (PublicRegistrationController.php:81). Con ese token, GET /api/v1/events/{X}/sessions/{S}/live-config devuelve stream_url, stream_iframe, recording_url y custom_url de cualquier sesion de la plataforma: publicShow ignora eventId y hace EventSession::findOrFail(S).
- **Fuente:** auditoria de seguridad M0.8.1, pista #37 (`eventos-backend:attendee-api:event-content-without-membership`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AsistenteYLogicaTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/events.php`, `backend: app/Http/Controllers/Api/V1/Admin/SessionConfigController.php`, `backend: app/Http/Controllers/Api/V1/PublicLandingController.php`, `backend: app/Http/Controllers/Api/V1/AgendaController.php`, `backend: app/Http/Controllers/Api/V1/DocumentController.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.6):** Middleware `miembro.evento` en `/events` y sponsors: inscrito en el evento de la ruta (o de su sesion, encuesta o juego) y la sesion de la ruta del mismo evento. Prueba `CruceEntreEventosTest.php`. Backend `1a48d93`.

### BUG-425: El ban y la aprobacion se revisan sobre el ultimo evento activo del usuario, no sobre el evento de la ruta (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Un asistente baneado en el evento A sigue usando las rutas HTTP de A si tiene un attendee mas nuevo (por created_at) en otro evento activo B. CheckBan (CheckBan.php:20-26) evalua solo ese attendee de B. Los controladores de A atan el attendee al evento de la ruta pero no miran activeBan: WallController::requireAttendee (290-296), QuestionController::requireAttendee (185-190) y ChatController::index (22-28). Las form requests devuelven authorize() true.
- **Fuente:** auditoria de seguridad M0.8.1, pista #36 (`eventos-backend:attendee-api:ban-checked-on-latest-attendee`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AsistenteYLogicaTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api.php`, `backend: app/Http/Middleware/CheckBan.php`, `backend: app/Http/Controllers/Api/V1/WallController.php`, `backend: app/Http/Controllers/Api/V1/Admin/BanController.php`, `backend: app/Http/Middleware/CheckApproval.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.7):** `App\Support\EventoDeLaPeticion`: check.ban y check.approval miran el evento de la ruta. Prueba `BloqueoYBanTest.php`. Backend `1e044de`.

### BUG-424: Las restricciones de audiencia se aplican solo al listar: /pages/{id} ignora 'Quien la ve' y los datos de los modulos ignoran grupos, presencia y rol (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** CustomPageController::index filtra las paginas por rol (whereNull('roles') o roles que contengan 'attendee' o 'vendedor', lineas 25-28). show() trae cualquier pagina habilitada del evento por id sin ese filtro (38-39). Asi, un asistente comun lee paginas que el organizador marco solo para 'Equipos de stands' (body HTML e iframe_url), y los ids son enteros consecutivos. El dato expuesto es del mismo evento y no cruza organizaciones.
- **Fuente:** auditoria de seguridad M0.8.1, pista #35 (`eventos-backend:attendee-api:audience-filter-only-on-listing`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AsistenteYLogicaTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/events.php`, `backend: app/Http/Controllers/Api/V1/CustomPageController.php`, `backend: app/Http/Resources/V1/CustomPageResource.php`, `backend: app/Filament/Resources/CustomPageResource.php`, `backend: app/Http/Controllers/Api/V1/ModuleController.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.7):** Paginas por id respetan "Quien la ve" (`e71162d`) y, decision Kamilo, la audiencia de los modulos (grupos y presencia) corta sus datos: middleware `audiencia.modulo` (`112717c`). Pruebas `VariosDelEventoTest.php` y `AudienciaDelModuloTest.php`.

### BUG-423: Un anuncio 'Programado' llega a los asistentes antes de su hora: la API y el socket solo miran que published_at no sea nulo (RESUELTO 2026-10-24)
- **Severidad propuesta:** BAJA (prioridad C de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Un asistente del evento recibe el titulo, el cuerpo, la imagen y el enlace de un anuncio que el staff dejo 'Programado' para mas tarde. GET /api/v1/events/{id}/announcements lo devuelve apenas se guarda, porque solo exige published_at no nulo. Si el anuncio es para todos, AnnouncementObserver ademas lo reparte completo por socket (data:sync a event:{id}) al crearlo. Si tiene roles, llega antes de hora a los asistentes de ese rol.
- **Fuente:** auditoria de seguridad M0.8.1, pista #34 (`eventos-backend/announcements:anuncio-programado-visible-antes-de-published_at`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AsistenteYLogicaTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.8 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/events.php`, `backend: app/Http/Controllers/Api/V1/AnnouncementController.php`, `backend: app/Filament/Resources/AnnouncementResource.php`, `backend: app/Observers/AnnouncementObserver.php`, `socket: src/index.ts` (y mas)
- **Resuelto 2026-10-24 (M0.8.8):** La API lista solo anuncios con published_at ya cumplido; el observer no reparte uno programado; DispatchScheduledNotificationsJob limpia la cache y avisa a la sala cuando llega la hora (sin push en ese momento). Prueba `tests/Feature/Security/DatosYContenidoTest.php`. Backend `6c4abd8`.

### BUG-422: StorageService::upload conserva la extension del cliente: en el disco public un .php subido por /admin/uploads quedaria ejecutable y un poliglota imagen/HTML del muro, fotos o historias se serviria como HTML en el origen de la API (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** upload arma el nombre con getClientOriginalExtension(). admin/uploads valida solo 'file', y shouldBlockPhpUpload solo corre en mimes, mimetypes y extensions, asi que acepta .php, .phtml, .html y .svg. Las rutas de asistente (muro, fotos, historias) validan 'image', que compara la extension adivinada por contenido y bloquea extensiones php del cliente. Aun asi acepta un poliglota GIF89a/HTML llamado x.html y lo guarda como {uuid}.html.
- **Fuente:** auditoria de seguridad M0.8.1, pista #33 (`eventos-backend:uploads:client-extension-preserved`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/StaffYAdminApiTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/admin.php`, `backend: app/Http/Controllers/Api/V1/Admin/UploadController.php`, `backend: app/Services/StorageService.php`, `backend: vendor/laravel/framework/src/Illuminate/Validation/Concerns/ValidatesAttributes.php`, `backend: app/Http/Requests/Api/V1/StoreWallPostRequest.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.6):** La extension sale del contenido y nunca una ejecutable o HTML. Prueba `AdminApiPorEventoTest.php`. Backend `4cf9f7d`.

### BUG-421: POST y DELETE /api/v1/admin/uploads no exigen ningun rol: cualquier asistente sube archivos de cualquier tipo al almacenamiento publico y borra cualquier objeto del bucket R2 o del disco public por URL (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Quien ataca: cualquier usuario con token Sanctum normal. Por la regla D.1 los tokens del staff del panel se borran, asi que el camino real es un asistente de cualquier evento. El registro publico de la landing entrega ese token al instante en los eventos sin aprobacion. store valida solo file|max:10240 y un directory con regex. Sube con visibilidad public a R2, o al disco public si R2 no esta configurado o la subida a R2 falla, y devuelve la URL.
- **Fuente:** auditoria de seguridad M0.8.1, pista #32 (`eventos-backend:admin-api:uploads-without-authorization`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/StaffYAdminApiTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/admin.php`, `backend: routes/api.php`, `backend: app/Http/Controllers/Api/V1/Admin/UploadController.php`, `backend: app/Services/StorageService.php`, `backend: app/Http/Controllers/Api/V1/ProfileController.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.6):** Uploads solo para admins de un evento activo, bajo `uploads/`; borrar solo lo de `uploads/`. Prueba `AdminApiPorEventoTest.php`. Backend `4cf9f7d`.

### BUG-420: Las acciones admin de encuestas no revisan rol ni evento: cualquier asistente crea, activa, cierra, modera y lee resultados de encuestas de cualquier evento (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Quien ataca: un asistente con token Sanctum normal de cualquier evento activo, que pasa check.ban y check.approval. El registro publico de la landing entrega ese token al instante en los eventos que no exigen aprobacion. Las rutas /api/v1/admin/polls* llegan a PollController sin ningun chequeo de rol ni de evento. (a) store acepta cualquier event_id y session_id, sin comparar la sesion con el evento.
- **Fuente:** auditoria de seguridad M0.8.1, pista #30 (`eventos-backend:admin-api:poll-admin-actions-without-role`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/StaffYAdminApiTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/admin.php`, `backend: routes/api.php`, `backend: app/Http/Controllers/Api/V1/PollController.php`, `socket: src/index.ts`, `backend: tests/Feature/Polls/PollTest.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.6):** Acciones admin de encuestas con staff en vivo del evento de la encuesta. Prueba `AdminApiPorEventoTest.php`. Backend `4cf9f7d`.

### BUG-419: GameController::authorizeAdmin acepta al admin* de cualquier evento para operar juegos, exportar participantes y sortear premios de otro evento (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Quien ataca: un usuario con un rol admin* (admin, admin_content, admin_notifs o admin_analytics) en el evento A, en la fila de attendees que devuelve Attendee::where('user_id')->first(). La consulta no tiene ORDER BY. Con el indice unico (user_id, event_id) de MySQL suele salir la fila de menor event_id, no la de menor id. Ejemplos: quien fue admin_analytics de un evento anterior y hoy es asistente de B, o el admin de otra organizacion.
- **Fuente:** auditoria de seguridad M0.8.1, pista #29 (`eventos-backend:admin-api:game-admin-check-not-bound-to-game-event`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/StaffYAdminApiTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/admin.php`, `backend: routes/api.php`, `backend: app/Http/Controllers/Api/V1/Admin/GameController.php`, `backend: app/Models/Attendee.php`, `backend: database/migrations/2026_03_28_200004_create_attendees_table.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.6):** `App\Support\AdminDelEvento`: cada accion de juegos con el evento del juego; crear exige sesion y patrocinador del mismo evento. Prueba `AdminApiPorEventoTest.php`. Backend `4cf9f7d`.

### BUG-418: El manifiesto del totem entrega el QR estatico (credencial reutilizable) de todos los asistentes con check-in del evento (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** GET /api/v1/rooms/manifest, con solo el X-Totem-Token de un salon, devuelve todos los asistentes con check-in del evento y, para cada uno, nombre, foto y el valor crudo de su QR estatico (qr_tokens.token). El alcance de evento tiene sentido funcional (cualquier asistente puede llegar a la puerta de cualquier salon), pero el kiosko solo usa ese valor como llave del mapa para mostrar el nombre al escanear; con un hash bastaria.
- **Fuente:** auditoria de seguridad M0.8.1, pista #28 (`eventos-backend:totems:manifest-exposes-static-qr`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/PaginasYOperadorTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/web.php`, `backend: app/Http/Controllers/Api/V1/RoomCheckinController.php`, `backend: app/Services/RoomCheckinService.php`, `eventos-kiosko/src/hooks/useAttendeeCache.ts`, `backend: app/Http/Controllers/Api/V1/LeadController.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.7):** El manifiesto del totem trae `token_hash` (sha256); el kiosko compara hashes (eventos-kiosko `68096db`). Prueba `DatosExpuestosTest.php`. Backend `0df110a`.

### BUG-417: El token de Mission Control y del display se sale de su evento y su sesion: solo se ata a los parametros de la ruta, y en el socket actua como el staff dueño (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Cada visita a /monitor/{id} o /display/session/{id} con la firma valida emite un token Sanctum de 12 h a nombre del primer usuario activo con attendee admin, organizer o moderator del evento E, con permisos operador:{tipo}, evento:{E}, sesion:{S}. La unica barrera es TokenDeOperador::permite en el callback de Sanctum, que compara {eventId} y {sessionId} de la ruta y event_id de la query.
- **Fuente:** auditoria de seguridad M0.8.1, pista #27 (`eventos-backend:operator-token:scope-bound-only-to-route-params`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/PaginasYOperadorTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/web.php`, `backend: app/Http/Controllers/Web/OperadorController.php`, `backend: app/Support/TokenDeOperador.php`, `backend: app/Providers/AppServiceProvider.php`, `backend: app/Http/Controllers/Api/V1/Admin/BanController.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.6):** `TokenDeOperador::permite` ata al evento del token lo que nombra la peticion (asistente, encuesta, voto, juego, control, salon, sesion, event_id del cuerpo). Prueba `CruceEntreEventosTest.php`. Backend `1a48d93`.

### BUG-416: Los enlaces firmados de Mission Control y del display no vencen ni se pueden revocar, y el de una sesion entrega los de todas las sesiones del evento (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** La firma de /monitor/{id} es HMAC-SHA256(app.qr_secret, 'sessionId|eventId') y la de /display/session/{id} es igual con el prefijo 'display|'. No lleva vencimiento, nonce, version ni usuario, y el controlador solo exige ademas que el evento siga activo.
- **Fuente:** auditoria de seguridad M0.8.1, pista #25 (`eventos-backend:operator-pages:hmac-links-no-expiry-no-revocation`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/PaginasYOperadorTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/web.php`, `backend: app/Http/Controllers/Web/OperadorController.php`, `backend: config/app.php`, `backend: .env.production.example`, `backend: app/Console/Commands/SecurityCheckCommand.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.7):** Decision Kamilo. `App\Support\EnlaceDeOperador` + migracion `operator_link_version`: vencen un dia despues del evento, boton "Regenerar enlaces" y regeneracion del evento cuando un operador pierde el rol. Prueba `EnlacesDeOperadorTest.php`. Backend `112717c`.

### BUG-415: Un admin de un evento puede lanzar un control de asistencia sobre un salon de otro evento: push a sus asistentes y bloqueo del control legitimo (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** POST /api/v1/events/attendance-checks/trigger autoriza contra el event_id del cuerpo (attendee con rol de EVENT_ADMINS en E) y comprueba que las sesiones sean de E, pero room_id solo se valida con exists:event_rooms,id.
- **Fuente:** auditoria de seguridad M0.8.1, pista #24 (`eventos-backend:attendance-checks:trigger-room-not-scoped-to-event`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/PaginasYOperadorTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/events.php`, `backend: app/Http/Controllers/Api/V1/RoomCheckinController.php`, `backend: app/Http/Controllers/Api/V1/StaffCheckinController.php`, `backend: app/Support/Roles.php`, `backend: app/Support/TokenDeOperador.php`
- **Resuelto 2026-10-23 (M0.8.6):** El salon del control de asistencia tiene que ser del evento. Prueba `CruceEntreEventosTest.php`. Backend `1a48d93`.

### BUG-414: El embed de stream que guarda un rol admin* del asistente se ejecuta en Mission Control con el origen del backend (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Pueden guardar stream_iframe con PATCH /api/v1/admin/sessions/{id}/live-config dos tipos de usuario: (1) un asistente con attendees.role admin, admin_content, admin_notifs o admin_analytics que no tiene rol Spatie de panel, y por eso conserva su bearer, porque la regla D.1 solo borra tokens de quien entra al panel; (2) quien tenga el token de Mission Control de la sesion. El servidor solo aplica strip_tags(..., '<iframe>').
- **Fuente:** auditoria de seguridad M0.8.1, pista #23 (`eventos-backend:static-pages:stream-iframe-unsandboxed-srcdoc`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/PaginasYOperadorTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/admin.php`, `backend: app/Http/Controllers/Api/V1/Admin/SessionConfigController.php`, `backend: app/Http/Controllers/Web/OperadorController.php`, `backend: public/mission-control/app.js`, `backend: app/Support/Roles.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.6):** `App\Support\EmbedDeStream` + mutadores de EventSession: el embed queda en un iframe https limpio por cualquier puerta; Mission Control (inicio y al refrescar) arma el iframe desde el src, sin srcdoc. Pruebas `CodigoEnPaginasDelAdminTest.php` y `xss-paginas.mjs`. Backend `0a3381d`.

### BUG-413: XSS almacenado en Event Pulse: el nombre del asistente se pinta con innerHTML sin codificar, en el mismo origen que /admin (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Un asistente de un evento con el Pulse activo, incluido quien se registra por la landing publica, puede guardar en users.name un valor con marcado HTML. Lo hace con PUT /api/v1/me/profile (regla sometimes|string|max:255) o al registrarse (string|max:255). Ni User ni ningun middleware global lo sanean. El Pulse arma su HTML concatenando cadenas sin ninguna funcion de escape y lo asigna con innerHTML en tres lugares.
- **Fuente:** auditoria de seguridad M0.8.1, pista #22 (`eventos-backend:event-pulse:unescaped-attendee-name-innerhtml`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/PaginasYOperadorTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api.php`, `backend: app/Http/Controllers/Api/V1/ProfileController.php`, `backend: app/Http/Controllers/PulseController.php`, `backend: public/event-pulse/js/moments.js`, `backend: app/Http/Requests/Api/V1/Auth/RegisterRequest.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.6):** `EP.esc` en los 42 textos de Event Pulse que van a innerHTML y URLs seguras en style. Prueba `CodigoEnPaginasDelAdminTest.php`. Backend `0a3381d`.

### BUG-412: chat-monitor.html toma stream_iframe y stream_url de la URL, sin token, y los pinta como HTML en el origen del backend (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Ninguna ruta ni controlador sirve chat-monitor.html con __MC_CONFIG__: no hay referencias en app/, routes/, resources/ ni public/. Se entrega como archivo estatico (public/.htaccess sirve directo los archivos que existen), asi que _mc queda vacio y la config sale siempre de la query string. El render del stream corre aunque falte el token: la linea 356 solo muestra un aviso y no detiene el script.
- **Fuente:** auditoria de seguridad M0.8.1, pista #20 (`eventos-backend:chat-monitor:url-query-config-into-html-sinks`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/navegador/xss412-chat-monitor.mjs`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: public/chat-monitor.html`, `backend: public/.htaccess`, `backend: app/Http/Middleware/SecurityHeaders.php`
- **Resuelto 2026-10-23 (M0.8.6):** chat-monitor ya no toma el stream de la URL y arma el iframe desde el src https (sin HTML). Navegador `xss-paginas.mjs`. Backend `0a3381d`.

### BUG-411: Peticiones salientes a URLs configuradas por staff del evento sin politica de destino (ZIP de documentos, webhooks, SMTP) (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Un admin de evento (event_admin, permiso manage-content, RoleSeeder.php:40) puede pegar a mano cualquier URL en el campo file_url de un documento: ImageUploadField::fileField solo aplica ->url() y maxLength 500. Al guardar, DocumentObserver encola RegenerateDocumentsZipJob. El job hace Http::timeout(30)->get($doc->file_url) desde el worker, sin limitar host ni esquema, y siguiendo redirecciones (Guzzle sigue hasta 5 por defecto).
- **Fuente:** auditoria de seguridad M0.8.1, pista #19 (`eventos-backend:outbound-requests:admin-configured-url-fetch-without-host-policy`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/ExportesTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: app/Filament/Components/ImageUploadField.php`, `backend: app/Filament/Resources/DocumentResource.php`, `backend: app/Services/DocumentEffects.php`, `backend: app/Jobs/RegenerateDocumentsZipJob.php`, `backend: database/seeders/RoleSeeder.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.7):** `App\Support\DestinoSaliente`: ZIP y webhooks solo a destinos publicos, sin redirecciones; lo propio se lee del disco. SMTP queda fuera (servidores de correo internos legitimos). Prueba `VariosDelEventoTest.php`. Backend `e71162d`.

### BUG-410: Las variables de las plantillas de correo se insertan como HTML sin escape, incluidas las que controla otra persona (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** EmailTemplate::render sustituye cada {{variable}} con str_replace del valor crudo dentro del HTML ya envuelto por EmailLayout, y BaseEventosMail envia el resultado como htmlString. Solo el encabezado del layout usa e(). Varias variables las controla alguien distinto del destinatario.
- **Fuente:** auditoria de seguridad M0.8.1, pista #18 (`eventos-backend:mail:email-template-raw-html-substitution`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/ExportesTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/auth.php`, `backend: app/Http/Controllers/Api/V1/AuthController.php`, `backend: app/Services/MagicLinkService.php`, `backend: app/Mail/MagicLinkMail.php`, `backend: app/Models/EmailTemplate.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.7):** Variables escapadas en el HTML del correo; el color solo si es hex. Prueba `VariosDelEventoTest.php`. Backend `e71162d`.

### BUG-409: Los exports CSV/XLSX guardan como formula los textos de asistentes que empiezan con '=' (RESUELTO 2026-10-24)
- **Severidad propuesta:** BAJA (prioridad C de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** BaseExportJob escribe las filas sin neutralizar. En XLSX, Maatwebsite usa su DefaultValueBinder, que viene en la config del paquete porque el proyecto no tiene config/excel.php. Ese binder delega en el de PhpSpreadsheet 1.30.7, que deja como TYPE_FORMULA todo string de mas de un caracter que empieza con '='. El archivo guarda entonces una formula real y no texto. pre_calculate_formulas=false evita que se evalue en el servidor.
- **Fuente:** auditoria de seguridad M0.8.1, pista #17 (`eventos-backend:exports:spreadsheet-formula-injection`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/ExportesTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.8 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: app/Http/Controllers/Api/V1/ProfileController.php`, `backend: app/Jobs/Exports/ExportAttendeesMasterJob.php`, `backend: app/Jobs/Exports/BaseExportJob.php`, `backend: vendor/maatwebsite/excel/config/excel.php`, `backend: vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Cell/DefaultValueBinder.php` (y mas)
- **Resuelto 2026-10-24 (M0.8.8):** `Exportes::celda/fila/csv`: toda celda de texto que empieza con = + - @ tab o retorno lleva un apostrofo (los numeros quedan igual); todos los CSV y XLSX del staff pasan por ahi. Prueba `tests/Feature/Security/DatosYContenidoTest.php`. Backend `6c4abd8`.

### BUG-408: Los exports del Data Center quedan en almacenamiento publico con nombres que se pueden adivinar (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Los exports del Data Center son 44 tipos. Entre ellos estan asistentes_master (nombre, correo, telefono, empresa, cargo y respuestas del registro), leads, chat y consent_log. Se escriben como exports/{tipo}_{eventId}_{Ymd_His}.csv|xlsx (BaseExportJob.php:53, por ejemplo asistentes_master_1_20261022_163717.csv) en storage/app/public. Si hay un bucket R2 configurado, el archivo se sube al disco r2 con la misma clave y se borra la copia local.
- **Fuente:** auditoria de seguridad M0.8.1, pista #16 (`eventos-backend:exports:public-storage-predictable-filenames`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/ExportesTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/data-center.php`, `backend: app/Http/Controllers/Api/V1/DataCenterController.php`, `backend: app/Jobs/Exports/BaseExportJob.php`, `backend: config/filesystems.php`, `backend: app/Services/StorageService.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.6):** `App\Support\Exportes`: todos los exports al disco privado; descarga por enlace firmado (24 h) con sesion del panel y acceso al evento (`/admin/exportes/{evento}/{archivo}`). Prueba `ExportesPrivadosTest.php`. Backend `9980228`.

### BUG-407: El Data Center y /dc-embed pintan nombres de asistentes y de eventos sin escape en innerHTML (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** La SPA del Data Center (/data-center) la sirve Laravel en el mismo host que /admin, solo a super_admin, org_admin y event_admin con sesion y 2FA. Arma el HTML con template literals y lo asigna a innerHTML sin ninguna funcion de escape. Interpolan users.name crudo: el podio y la lista del leaderboard (app.js:809-813, en tabGamificacion), el top de preguntas (1031, tabEngagement) y el top del muro (1106, tabSocial).
- **Fuente:** auditoria de seguridad M0.8.1, pista #15 (`eventos-backend:data-center:unescaped-server-data-innerhtml`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/ExportesTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: app/Http/Controllers/Api/V1/ProfileController.php`, `backend: app/Services/ExportService.php`, `backend: app/Http/Controllers/Api/V1/DataCenterController.php`, `backend: public/data-center/assets/app.js`, `backend: public/data-center/embed.html` (y mas)
- **Resuelto 2026-10-23 (M0.8.6):** `esc()` en el Data Center (113 textos) y en `/dc-embed`. Prueba `CodigoEnPaginasDelAdminTest.php` y navegador `tests/Auditoria/navegador/xss-paginas.mjs` (rojo con lo viejo, verde con lo nuevo). Backend `0a3381d`.

### BUG-406: Staff y permisos: con manage-users (event_admin, org_admin) se puede subir el propio rol hasta super_admin con un estado Livewire forjado y tomar, editar o borrar cuentas super_admin (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** UserResource (Sistema > Staff y permisos) solo exige manage-users, que RoleSeeder da a org_admin (linea 39) y a event_admin (linea 40). No hay Policies ni Gate::before que limiten registros: el helper authorize de Filament devuelve allow cuando no hay policy (vendor/filament/filament/src/helpers.php:42), asi que canEdit y canDelete son verdaderos sobre cualquier registro listado, incluidos los super_admin (STAFF_LISTING).
- **Fuente:** auditoria de seguridad M0.8.1, pista #12 (`eventos-backend:filament:staff-roles-no-hierarchy`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/StaffYAdminApiTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: app/Filament/Resources/UserResource.php`, `backend: database/seeders/RoleSeeder.php`, `backend: app/Filament/Traits/HasResourcePermission.php`, `backend: vendor/filament/filament/src/Resources/Pages/EditRecord.php`, `backend: app/Filament/Resources/UserResource/Pages/EditUser.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.6):** `App\Support\JerarquiaDelStaff` (super_admin > org_admin > event_admin > moderador y check-in), aplicada al guardar y en canEdit/canDelete de UserResource. Prueba `JerarquiaDelStaffTest.php`. Backend `06b2393`.

### BUG-405: El embed publico /dc-embed entrega todas las estadisticas internas, incluidas IP y nombre de los admins y la ultima auditoria (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** DataCenterPageController::embed no pide sesion: con el token UUID (vigente) inyecta en el HTML (window.__DC_EMBED__) el resultado completo de ExportService::getStats. embed.html solo usa registered, checked_in, rating_avg, total_ratings, leads, sessions_total, sponsors y leaderboard (los nombres del leaderboard se muestran a proposito), pero el JSON trae audit.recent_actions con la IP, el nombre y el avatar de cada admin y la accion hecha con su objetivo, audit.top_admins,...
- **Fuente:** auditoria de seguridad M0.8.1, pista #10 (`eventos-backend:data-center:embed-full-stats`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/StaffYAdminApiTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/web.php`, `backend: app/Http/Controllers/Web/DataCenterPageController.php`, `backend: app/Services/ExportService.php`, `backend: public/data-center/embed.html`
- **Resuelto 2026-10-23 (M0.8.7):** /dc-embed lleva solo lo que pinta. Prueba `DatosExpuestosTest.php`. Backend `0df110a`.

### BUG-404: El registro entrega sesion para un correo nunca probado y las reglas que confian en el correo la aceptan (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** POST /api/v1/public/events/{slug}/register (y /auth/register) crea la cuenta para cualquier correo nuevo y devuelve un bearer al instante, sin prueba de que el correo sea de quien se registra. Ninguna ruta exige email_verified_at. Dar sesion a un registro nuevo es una decision de producto: el comentario del controlador solo la niega a 'existing'. El problema son las decisiones de acceso que se toman sobre ese correo sin probar.
- **Fuente:** auditoria de seguridad M0.8.1, pista #9 (`eventos-backend:public-registration:session-for-unproven-email`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AccesoTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api.php`, `backend: app/Http/Controllers/Api/V1/PublicRegistrationController.php`, `backend: app/Services/AuthService.php`, `backend: app/Http/Controllers/Api/V1/StandController.php`, `backend: app/Http/Controllers/Api/V1/AuthController.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.6):** Decision Kamilo "entra, sin confiar" (decisiones.md). `App\Support\CorreoComprobado`: invitaciones de stand por correo solo con correo comprobado; la primera prueba por enlace de entrada cierra sesiones y contraseña de antes; eventos con restriccion sin sesion hasta comprobar (landing, app y login). App `bde3eff`. Prueba `CorreoSinComprobarTest.php`. Backend `b15ebf2`.

### BUG-403: El limitador del handoff cuenta por un correo que la peticion no trae: 5 canjes por hora para toda la plataforma (RESUELTO 2026-10-24)
- **Severidad propuesta:** BAJA (prioridad C de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** POST /api/v1/public/handoff usa throttle:public_register, cuyo limite principal es 5 por hora por sha1 del campo email. El handoff solo manda code (la webapp envia {code}), asi que todas las redenciones de todas las personas caen en el mismo cubo, sha1(''). Fuera de local, despues de 5 canjes en una hora el resto recibe 429: la confirmacion del widget vuelve al registro y la persona no recibe la sesion.
- **Fuente:** auditoria de seguridad M0.8.1, pista #8 (`eventos-backend:public-registration:handoff-throttle-keyed-by-absent-email`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AccesoTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.8 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api.php`, `eventos-web/src/app/api/public/handoff/route.ts`, `backend: app/Providers/AppServiceProvider.php`, `backend: app/Http/Controllers/Api/V1/PublicHandoffController.php`, `eventos-web/src/lib/publicRegistration.ts` (y mas)
- **Resuelto 2026-10-24 (M0.8.8):** El handoff tiene su limite propio `public_handoff` por red (600/h), ya no el del registro que contaba por un correo vacio. Prueba `tests/Feature/Security/LimitesYCodigosTest.php`. Backend `6721cda`.

### BUG-402: Un codigo de acceso con un espacio intercalado se valida pero no gasta usos (RESUELTO 2026-10-24)
- **Severidad propuesta:** BAJA (prioridad C de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** En eventos con requires_access_code, el validador de RegisterRequest (que hereda PublicRegisterRequest) quita los espacios y pasa a mayusculas para buscar el codigo y comprobar isValid(). El merge del valor limpio cambia el input de la peticion, pero no los datos del validador, que se copiaron al crearlo. Por eso validated() entrega el valor crudo.
- **Fuente:** auditoria de seguridad M0.8.1, pista #7 (`eventos-backend:public-registration:access-code-normalization-skips-use-count`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AccesoTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.8 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api.php`, `backend: app/Http/Requests/Api/V1/Auth/RegisterRequest.php`, `backend: app/Http/Controllers/Api/V1/PublicRegistrationController.php`, `backend: app/Services/AuthService.php`, `backend: vendor/laravel/framework/src/Illuminate/Foundation/Http/FormRequest.php` (y mas)
- **Resuelto 2026-10-24 (M0.8.8):** `AccessCode::normalizar` en un solo lugar (prepareForValidation del registro y AuthService): se valida y se gasta igual escrito. Prueba `tests/Feature/Security/LimitesYCodigosTest.php`. Backend `6721cda`.

### BUG-401: verify-identity entrega el codigo de activacion a quien sepa el correo y el telefono de un importado (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Caso: un usuario importado por CSV que todavia no activa su cuenta (invitation_token presente, activated_at nulo), cuyo evento activo mas reciente tiene activation_verify_field = 'phone', que es la unica opcion que ofrece el admin. POST /api/v1/auth/verify-identity no pide autenticacion. Compara el valor enviado con users.phone, sin espacios y en minusculas, y si coincide rota invitation_token y lo devuelve en el cuerpo de la respuesta.
- **Fuente:** auditoria de seguridad M0.8.1, pista #6 (`eventos-backend:auth:verify-identity-token-on-field-match`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AccesoTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/auth.php`, `backend: app/Http/Controllers/Api/V1/AuthController.php`, `backend: app/Services/AuthService.php`, `APP EVENTOS/docs/decisiones.md`, `APP EVENTOS/docs/living/BUG-LOG.md` (y mas)
- **Resuelto 2026-10-23 (M0.8.6):** verify-identity ya no devuelve el codigo: manda el enlace de activacion al correo (misma regla que check-email, BUG-363); la app avisa que llego al correo (eventos-app `836e532`). Prueba `EnlacesYActivacionTest.php`. Backend `06b2393`.

### BUG-400: El enlace de restablecer contraseña se arma con el Host de la peticion (sin TrustHosts ni raiz forzada) (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (prioridad A de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** POST /api/v1/auth/forgot-password crea el token del broker y arma el enlace con route('password.reset', ...). Es una URL absoluta cuya raiz sale del Host de la peticion, o de X-Forwarded-Host si la peticion viene de un proxy confiable. No hay TrustHosts (Laravel lo deja apagado), ni URL::forceRootUrl, ni config('app.url') en esta ruta, a diferencia del magic link (app.webapp_url) y de la invitacion (app.url).
- **Fuente:** auditoria de seguridad M0.8.1, pista #5 (`eventos-backend:auth:password-reset-link-from-request-host`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AccesoTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.6 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/auth.php`, `backend: app/Http/Controllers/Api/V1/AuthController.php`, `backend: app/Mail/PasswordResetMail.php`, `backend: bootstrap/app.php`, `backend: vendor/laravel/framework/src/Illuminate/Http/Middleware/TrustProxies.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.6):** `App\Support\EnlaceDeCorreo`: restablecer contraseña y verificar correo se arman con la raiz y el esquema de APP_URL. No se activa TrustHosts global: el socket llama al backend por 127.0.0.1. Prueba `tests/Feature/Security/EnlacesYActivacionTest.php`. Backend `06b2393`.

### BUG-399: Desactivar una cuenta (is_active = false) solo frena el login con contraseña: el magic link y los tokens existentes siguen dando acceso (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** El login con contraseña responde 403 'Cuenta desactivada' si is_active es falso, y 'Staff y permisos' ofrece el interruptor 'Cuenta activa'. Ese listado incluye a staff_checkin (rol Spatie sin acceso al panel) y a quien tenga attendees.role admin* en algun evento. Esas cuentas no caen en el veto D.1.
- **Fuente:** auditoria de seguridad M0.8.1, pista #4 (`eventos-backend:auth:is-active-only-enforced-on-password-login`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AccesoTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/auth.php`, `backend: app/Services/MagicLinkService.php`, `backend: app/Http/Controllers/Api/V1/AuthController.php`, `backend: app/Filament/Resources/UserResource.php`, `backend: app/Support/Roles.php` (y mas)
- **Resuelto 2026-10-23 (M0.8.7):** Cuenta desactivada: tokens y fichas cerrados, token viejo no autentica, magic link no entra. Prueba `CuentasYTokensTest.php`. Backend `5636d38`.

### BUG-398: Los limites por correo normalizan con strtolower y trim, pero MySQL compara sin acentos ni mayusculas: cada variante abre un cubo nuevo (RESUELTO 2026-10-24)
- **Severidad propuesta:** BAJA (prioridad C de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** entry_check (check-email, verify-identity) y cuenta_correo (forgot-password, register) cuentan por sha1(strtolower(trim(email))). En PHP 8.2 y despues, strtolower solo cambia ASCII. En cambio, el controlador busca User::where('email', ...) con la colacion por defecto utf8mb4_unicode_ci, que iguala acentos, formas anchas y mayusculas.
- **Fuente:** auditoria de seguridad M0.8.1, pista #3 (`eventos-backend:auth:email-throttle-key-differs-from-db-collation`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AccesoTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.8 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/auth.php`, `backend: app/Providers/AppServiceProvider.php`, `backend: app/Http/Controllers/Api/V1/AuthController.php`, `backend: config/database.php`, `backend: database/migrations/0001_01_01_000000_create_users_table.php` (y mas)
- **Resuelto 2026-10-24 (M0.8.8):** `App\Support\ClaveDeCorreo` (NFKC + sin acentos + minusculas, como utf8mb4_unicode_ci) en todos los limites por correo. Prueba `tests/Feature/Security/LimitesYCodigosTest.php`. Backend `6721cda`.

### BUG-397: check-email anonimo rota el codigo de activacion y reenvia la invitacion en cada llamada (RESUELTO 2026-10-24)
- **Severidad propuesta:** BAJA (prioridad C de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** POST /api/v1/auth/check-email no pide autenticacion. Para un usuario con invitation_token y sin activated_at, cada llamada rota invitation_token (lineas 51-52, antes incluso de mirar si tiene evento activo). Si tiene asistente en un evento activo, ademas encola un InvitationMail con el enlace nuevo al correo guardado. El unico limite es entry_check: 10 por minuto por texto del correo (600 por hora, 14.400 por dia a un mismo buzon) y 1500 por minuto por red.
- **Fuente:** auditoria de seguridad M0.8.1, pista #2 (`eventos-backend:auth:check-email-anonymous-invitation-rotation-resend`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AccesoTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.8 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: routes/api/auth.php`, `backend: app/Http/Controllers/Api/V1/AuthController.php`, `backend: app/Providers/AppServiceProvider.php`, `backend: config/auth.php`, `backend: app/Services/AuthService.php` (y mas)
- **Resuelto 2026-10-24 (M0.8.8):** check-email ya no rota el codigo en cada consulta: lo cambia solo cuando de verdad reenvia el correo, como mucho una vez cada 10 minutos (respeta BUG-363: el viejo deja de servir). Prueba `tests/Feature/Security/LimitesYCodigosTest.php`. Backend `6721cda`.

### BUG-396: El reto 2FA del panel no cuenta para el bloqueo un codigo TOTP incorrecto repetido en intervalos distintos (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA (prioridad B de la auditoria; la severidad real sale de la prueba)
- **Que pasa:** Alguien que ya tiene la contraseña de una cuenta del staff llega al reto /admin/dos-pasos/verificar. Para no contar dos veces el doble envio (envio automatico al completar 6 digitos y luego Enter), el reto guarda en la sesion una huella del ultimo codigo fallido y no cuenta un fallo igual al anterior. La huella es sha256(id|codigo): no incluye el intervalo TOTP de 30 s ni un tiempo maximo, y solo se borra con un acierto.
- **Fuente:** auditoria de seguridad M0.8.1, pista #1 (`eventos-backend/app/Filament/Auth/TwoFactorChallenge.php:verify:huella-de-codigo-fallido-sin-intervalo-totp`). Dos revisiones de codigo independientes; **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/AccesoTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo). Detalle, rastro y plan en `NEEDS-VALIDATION.md` de la corrida.
- **Cuando se arregla:** M0.8.7 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `backend: app/Filament/Auth/TwoFactorChallenge.php`, `backend: app/Services/TwoFactorService.php`, `backend: app/Support/StaffLockout.php`, `backend: app/Support/TwoFactorLogin.php`, `backend: tests/Feature/Security/StaffLockoutTest.php`
- **Resuelto 2026-10-23 (M0.8.7):** La huella del codigo repetido lleva la ventana TOTP. Prueba `VariosDelEventoTest.php`. Backend `e71162d`.

## 2026-10-22 — App movil: errores de TypeScript en `main` (3 abiertos)

> Salieron al archivar el trabajo de julio (recap, haptics, reintentos) en la rama local
> `archivo/2026-07-recap-haptics-reintentos` de eventos-app (`8176acd`): con `main` limpio `tsc` da 4 errores en 3 archivos.
> **Ya estaban anotados por Kamilo** en `PENDIENTES.md` §7 desde el 2026-07-04 (lista de 5 errores del WIP del recap);
> aqui solo reciben numero.

### BUG-394: `AttendanceCheckModal` no compila en TypeScript (`useRef` sin valor inicial) (RESUELTO 2026-10-24)
- **Severidad:** BAJA (solo tipos; en la app no cambia nada) — `useRef<ReturnType<typeof setInterval>>()` sin argumento,
  que los tipos de React 19 ya no aceptan. Ensucia `tsc` y tapa errores nuevos.
- **Arreglo:** `useRef<...>(undefined)`. **Cuando:** M0.6.1.
- **Archivos:** `eventos-app/components/ui/AttendanceCheckModal.tsx:24`
- **Resuelto 2026-10-24 (M0.6.1):** Ya estaba arreglado en eventos-app `3b845dc` (useRef con valor inicial); se cierra al verificar `tsc` limpio en M0.6.1.

### BUG-393: El banner del concurso de fotos sale sin borde en la app (RESUELTO 2026-10-24)
- **Severidad:** BAJA (visual) — `ContestBanner` lee `border` de `useTheme()`, que no existe en `ThemeTokens`: cae a
  `transparent`. Es diseño: se decide con lab que borde lleva.
- **Cuando:** M0.6.1 con decision de diseño de Kamilo.
- **Archivos:** `eventos-app/components/social/ContestBanner.tsx:25`
- **Resuelto 2026-10-24 (M0.6.1):** Lab M0.6.1 opcion A: Noir con el borde sutil de tarjeta (`surface.border`); Lux tarjeta blanca con `shadow.sm` y sin borde (decision Kamilo: Lux usa elevacion). App `d863e2f`.

### BUG-392: La insignia "reclamado" de la lista de Golden Tickets sale con el estilo del modal (RESUELTO 2026-10-24)
- **Severidad:** BAJA (visual) — en `leaderboard.tsx` los estilos `goldenClaimedBadge` y `goldenClaimedText` estan
  definidos dos veces (lista y modal); el segundo pisa al primero, asi que la lista usa el del modal (padding, borde,
  radio). Arreglarlo cambia como se ve: es diseño, con lab.
- **Cuando:** M0.6.1 con decision de diseño de Kamilo.
- **Archivos:** `eventos-app/app/(app)/leaderboard.tsx:1373-1374, 1387-1388`
- **Resuelto 2026-10-24 (M0.6.1):** Lab M0.6.1 opcion A: la lista usa su propio estilo (`goldenListClaimed*`, solo texto teal); ya no la pisa la pastilla del modal. La pantalla es isla Noir. App `d863e2f`.

## 2026-10-22 — Hallazgos del inventario del admin y de la fase 0 (17 abiertos)

> Orden de arreglo (decision Kamilo 2026-10-22, `decisiones.md`): seguridad en el bloque 0.8 al cierre de la fase 0;
> lo solo-Filament que no es de seguridad va a la lista no repetir del admin nuevo (M2.8.5). La logica que vive en
> hooks de guardar o crear de 20 paginas (no en acciones) queda descrita en `contratos/acciones-admin.md` (hallazgo 15).

### BUG-391: Secretos con valor por defecto inseguro y variables que faltan en `.env.example` (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA si pasa a produccion
- **Que pasa:** `APP_QR_SECRET` (firma QR y enlaces del operador) y los secretos internos caen a `change_this_secret_in_production` si no estan en el `.env`; 12 variables propias no estan en `.env.example`, asi que una instalacion copiada del ejemplo arranca sin ellas. En local `SOCKET_INTERNAL_SECRET` tiene ese valor.
- **Fuente:** catalogo de variables (`docs/contratos/variables.md`, M0.5.4), verificado a mano.
- **Cuando se arregla:** M0.8.5 (bloque de seguridad).
- **Archivos:** `config/app.php:166`, `config/services.php`, `.env.example`
- **Auditoria M0.8.1 (2026-10-23):** el camino se sostiene en dos revisiones independientes, pista #21 (`eventos-backend:config:qr-secret-insecure-default`); prioridad sugerida A. **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/PaginasYOperadorTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo).
- **Resuelto 2026-10-23 (M0.8.5):** `config/app.php` sin valor por defecto; `App\Support\Secretos::qr()` lanza error si el secreto falta o es de ejemplo, asi que nada se firma con una clave conocida. `security:check` suma `WEBAPP_INTERNAL_SECRET`. `.env.example` trae los secretos vacios y las variables que faltaban. En local `APP_QR_SECRET` y `SOCKET_INTERNAL_SECRET` estaban con el valor de ejemplo: rotados en backend y socket (los QR estaticos ya guardados siguen valiendo; los enlaces de Mission Control viejos no). Prueba `tests/Feature/Security/SecretosSinValorPorDefectoTest.php`. Backend `2155f56`.

### BUG-390: `/dc-embed` manda `X-Frame-Options: ALLOWALL`, que no es un valor valido (RESUELTO 2026-10-23)
- **Severidad propuesta:** BAJA
- **Que pasa:** El navegador ignora el valor y aplica su comportamiento por defecto; la intencion (dejar embeber) no queda expresada. Va con `frame-ancestors` de CSP.
- **Fuente:** lectura de `routes/web.php` al mover las rutas (M0.5.2), verificado a mano.
- **Cuando se arregla:** M0.8.4 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `app/Http/Controllers/Web/DataCenterPageController.php`

- **Resuelto 2026-10-23 (M0.8.4):** el embed declara `frame-ancestors *` por CSP y SecurityHeaders ya no lo pisa con DENY (antes no se podia incrustar). Prueba `tests/Feature/Security/PanelAdminSeguridadTest.php`. Backend `8b99911`.
### BUG-389: Los resultados de cualquier encuesta se ven sin token en `/display/polls/{id}` (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA
- **Que pasa:** La pagina es publica y sin firma: cambiando el numero se ven los resultados de cualquier encuesta de cualquier evento, incluidas las respuestas abiertas (texto libre de los asistentes).
- **Fuente:** lectura de `routes/web.php` al mover las rutas (M0.5.2), verificado a mano.
- **Cuando se arregla:** M0.8.4 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `app/Http/Controllers/Web/OperadorController.php` (`displayEncuesta`)
- **Auditoria M0.8.1 (2026-10-23):** el camino se sostiene en dos revisiones independientes, pista #26 (`eventos-backend:operator-pages:public-poll-display-without-token`); prioridad sugerida B. **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/PaginasYOperadorTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo).

- **Resuelto 2026-10-23 (M0.8.4):** se borro `/display/polls/{id}`. Prueba `tests/Feature/Security/PanelAdminSeguridadTest.php`. Backend `8b99911`.
### BUG-388: El correo de prueba de las plantillas sale por el mailer por defecto, no por el SMTP de la organizacion (ABIERTO 2026-10-22)
- **Severidad propuesta:** BAJA
- **Que pasa:** La prueba no usa el remitente real (`EmailService::mailerFor`): puede salir bien y el correo real fallar.
- **Fuente:** inventario de acciones del admin (`docs/contratos/acciones-admin.md`, M0.5.3), leido por un agente; **por verificar a mano**.
- **Cuando se arregla:** lista no repetir del admin nuevo, M2.8.5 (antes, si aparece un cliente antes de la semana 12).
- **Archivos:** `EditEmail.php:258`

- **CONFIRMADO con prueba 2026-10-23 (M0.8.4):** `tests/Auditoria/InventarioAdminTest.php`, en rojo. Sigue en la lista no repetir (M2.8.5).
### BUG-387: Paginas del admin sin permiso propio y sin permisos por accion (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA
- **Que pasa:** No hay Policies ni `->authorize` en ninguna accion: el acceso es por recurso completo. `LiveGameResults` y `PrizeRedemptions` no definen permiso, entra cualquier usuario del panel.
- **Fuente:** inventario de acciones del admin (`docs/contratos/acciones-admin.md`, M0.5.3), leido por un agente; **por verificar a mano**.
- **Cuando se arregla:** M0.8.4 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `app/Filament/Traits/HasResourcePermission.php`, paginas `LiveGameResults` y `PrizeRedemptions`
- **Auditoria M0.8.1 (2026-10-23):** el camino se sostiene en dos revisiones independientes, pista #11 (`eventos-backend:filament:pages-without-permission`); prioridad sugerida A. **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/StaffYAdminApiTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo).

- **Resuelto 2026-10-23 (M0.8.4):** Resultados de juegos pide `view-analytics`, Canjes `manage-checkin`; CSV solo del evento activo. Prueba `tests/Feature/Security/PanelAdminSeguridadTest.php`. Backend `8b99911`.
### BUG-386: Importar asistentes: contraseña igual al telefono, invitaciones con enlace roto y archivos que no se borran (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (la contraseña)
- **Que pasa:** En modo telefono la contraseña es el telefono y la cuenta queda activada. Si el correo ya tiene cuenta activada, se manda una invitacion con un token que no se guarda (enlace roto). Corre sincrono dentro de la peticion. El archivo subido y los temporales de fotos oficiales no se borran.
- **Fuente:** inventario de acciones del admin (`docs/contratos/acciones-admin.md`, M0.5.3), leido por un agente; **por verificar a mano**.
- **Cuando se arregla:** M0.8.4 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `AttendeesImport.php:72-74, 76, 91-92, 131-138`, `ListAttendeeAdmins.php:95`, `EventPhotoResource.php:411-425`
- **Auditoria M0.8.1 (2026-10-23):** el camino se sostiene en dos revisiones independientes, pista #13 (`eventos-backend:import:phone-as-password`); prioridad sugerida A. **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/StaffYAdminApiTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo).

- **Resuelto 2026-10-23 (M0.8.4):** se quito el modo de importar con telefono como contraseña. Prueba `tests/Feature/Security/PanelAdminSeguridadTest.php`. Backend `8b99911`.
### BUG-385: Secretos de webhooks a la vista en notificaciones y en un .txt (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA
- **Que pasa:** `copy_secret` muestra el secreto HMAC en una notificacion; `regenerate` muestra la llave nueva; `download_spec` escribe el secreto y la llave de entrada en un .txt descargable.
- **Fuente:** inventario de acciones del admin (`docs/contratos/acciones-admin.md`, M0.5.3), leido por un agente; **por verificar a mano**.
- **Cuando se arregla:** M0.8.4 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `WebhookEndpointResource.php:186, 236, 241, 269`, `WebhookApiKeyResource.php:116`
- **Auditoria M0.8.1 (2026-10-23):** el camino se sostiene en dos revisiones independientes, pista #51 (`eventos-backend:webhooks:spec-embeds-unbound-inbound-key`); prioridad sugerida A. **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/StandsYNetworkingTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo).

- **Resuelto 2026-10-23 (M0.8.4):** cada llave de entrada se liga a su partner (`webhook_endpoint_id`); la especificacion trae solo la suya. Prueba `tests/Feature/Security/PanelAdminSeguridadTest.php`. Backend `8b99911`.
### BUG-384: Webhooks: "Simular evento" manda asistentes inventados como reales; reenviar puede triplicar (RESUELTO 2026-10-23)
- **Severidad propuesta:** ALTA (datos a terceros)
- **Que pasa:** `simulate_event` marca `test` segun el modo del endpoint: a uno en modo real le llegan asistentes inventados como registros reales. `resend_failed` reenvia cada intento fallido y cada reintento crea su log (hasta 3 envios del mismo payload); no toma los fallos de red (`response_status = 0`). En `send_test`, si la conexion falla el admin ve un error en vez de "Prueba fallo".
- **Fuente:** inventario de acciones del admin (`docs/contratos/acciones-admin.md`, M0.5.3), leido por un agente; **por verificar a mano**.
- **Cuando se arregla:** M0.8.4 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `WebhookDispatchService.php:24`, `WebhookEndpointResource.php:195-204, 315-336`, `DispatchWebhookJob`

- **Resuelto 2026-10-23 (M0.8.4):** Simular evento sale como prueba; Reenviar fallidos una vez por envio e incluye caidas de red. Prueba `tests/Feature/Security/PanelAdminSeguridadTest.php`. Backend `8b99911`.
### BUG-383: Abrir dos paginas del admin ya cambia datos (ABIERTO 2026-10-22)
- **Severidad propuesta:** BAJA
- **Que pasa:** La lista de encuestas crea la encuesta post-evento con 3 preguntas al montarse; abrir una solicitud de soporte la pasa de `open` a `read` aunque nadie la lea.
- **Fuente:** inventario de acciones del admin (`docs/contratos/acciones-admin.md`, M0.5.3), leido por un agente; **por verificar a mano**.
- **Cuando se arregla:** lista no repetir del admin nuevo, M2.8.5 (antes, si aparece un cliente antes de la semana 12).
- **Archivos:** `ListEventSurveys.php:32-53`, `EditSupportRequest.php:33-40`

- **CONFIRMADO con prueba 2026-10-23 (M0.8.4):** `tests/Auditoria/InventarioAdminTest.php`, en rojo. Sigue en la lista no repetir (M2.8.5).
### BUG-382: Activar o cerrar encuestas desde el admin no avisa por socket (ABIERTO 2026-10-22)
- **Severidad propuesta:** MEDIA
- **Que pasa:** La API emite `poll:new` y `poll:closed`; Filament no: los asistentes no ven la encuesta hasta refrescar.
- **Fuente:** inventario de acciones del admin (`docs/contratos/acciones-admin.md`, M0.5.3), leido por un agente; **por verificar a mano**.
- **Cuando se arregla:** lista no repetir del admin nuevo, M2.8.5 (antes, si aparece un cliente antes de la semana 12).
- **Archivos:** `EventSurveyResource.php:125-139`, `EditEventSurvey.php:40-56`, `Api/V1/PollController.php:104, 123`

- **CONFIRMADO con prueba 2026-10-23 (M0.8.4):** `tests/Auditoria/InventarioAdminTest.php`, en rojo. Sigue en la lista no repetir (M2.8.5).
### BUG-381: `QuestionsRelationManager` de sesiones es codigo muerto (ABIERTO 2026-10-22)
- **Severidad propuesta:** BAJA
- **Que pasa:** 5 acciones con aviso al socket que nadie usa: no esta registrado (`getRelations()` devuelve vacio). La moderacion de preguntas vive en Mission Control.
- **Fuente:** inventario de acciones del admin (`docs/contratos/acciones-admin.md`, M0.5.3), leido por un agente; **por verificar a mano**.
- **Cuando se arregla:** lista no repetir del admin nuevo, M2.8.5 (antes, si aparece un cliente antes de la semana 12).
- **Archivos:** `EventSessionResource/RelationManagers/QuestionsRelationManager.php`, `EventSessionResource.php:393-399`

- **CONFIRMADO con prueba 2026-10-23 (M0.8.4):** `tests/Auditoria/InventarioAdminTest.php`, en rojo. Sigue en la lista no repetir (M2.8.5).
### BUG-380: Asignar speakers a una sesion no avisa a la app ni a la webapp (ABIERTO 2026-10-22)
- **Severidad propuesta:** MEDIA
- **Que pasa:** `speakers()->sync` cambia la tabla intermedia sin pasar por `EventSessionObserver`: nadie se entera hasta que otra cosa invalide la agenda.
- **Fuente:** inventario de acciones del admin (`docs/contratos/acciones-admin.md`, M0.5.3), leido por un agente; **por verificar a mano**.
- **Cuando se arregla:** lista no repetir del admin nuevo, M2.8.5 (antes, si aparece un cliente antes de la semana 12).
- **Archivos:** `EventSessionResource.php:130`

- **CONFIRMADO con prueba 2026-10-23 (M0.8.4):** `tests/Auditoria/InventarioAdminTest.php`, en rojo. Sigue en la lista no repetir (M2.8.5).
### BUG-379: Muro: seis caminos para publicar u ocultar, cada uno con efectos distintos (ABIERTO 2026-10-22)
- **Severidad propuesta:** MEDIA
- **Que pasa:** Fila: puntos y socket, sin webapp. Masivo: puntos, sin socket ni webapp. Detalle: solo webapp. Ocultar (fila o masivo) no avisa a nadie. `WallPost` no tiene observer propio.
- **Fuente:** inventario de acciones del admin (`docs/contratos/acciones-admin.md`, M0.5.3), leido por un agente; **por verificar a mano**.
- **Cuando se arregla:** lista no repetir del admin nuevo, M2.8.5 (antes, si aparece un cliente antes de la semana 12).
- **Archivos:** `WallPostResource.php:136-163, 174-175, 184-195, 222-224`, `ViewWallPost.php:23-38`

- **CONFIRMADO con prueba 2026-10-23 (M0.8.4):** `tests/Auditoria/InventarioAdminTest.php`, en rojo. Sigue en la lista no repetir (M2.8.5).
### BUG-378: Fotos: aprobar da resultados distintos y "aprobar seleccionadas" puede romper (ABIERTO 2026-10-22)
- **Severidad propuesta:** MEDIA
- **Que pasa:** Aprobar en la fila da puntos; en el detalle no. `bulk_approve` no filtra por estado y vuelve a aprobar rechazadas. Si la seleccion incluye una foto oficial (`attendee_id` nulo) llama `tryAward` con null en un `int`: TypeError.
- **Fuente:** inventario de acciones del admin (`docs/contratos/acciones-admin.md`, M0.5.3), leido por un agente; **por verificar a mano**.
- **Cuando se arregla:** lista no repetir del admin nuevo, M2.8.5 (antes, si aparece un cliente antes de la semana 12).
- **Archivos:** `EventPhotoResource.php:176-179, 255-260`, `ViewEventPhoto.php:24`, `PointsService.php:120-122`

- **CONFIRMADO con prueba 2026-10-23 (M0.8.4):** `tests/Auditoria/InventarioAdminTest.php`, en rojo. Sigue en la lista no repetir (M2.8.5).
### BUG-377: "Marcar reclamado" del Golden Ticket no registra quien, no bloquea y no mira vencimiento (ABIERTO 2026-10-22)
- **Severidad propuesta:** MEDIA (premios)
- **Que pasa:** GoldenTicket actualiza directo; PrizeRedemptions usa `RewardService::confirm`, que guarda `confirmed_by`, bloquea la fila y revisa el vencimiento. Dos clics a la vez o un premio vencido pasan igual.
- **Fuente:** inventario de acciones del admin (`docs/contratos/acciones-admin.md`, M0.5.3), leido por un agente; **por verificar a mano**.
- **Cuando se arregla:** lista no repetir del admin nuevo, M2.8.5 (antes, si aparece un cliente antes de la semana 12).
- **Archivos:** `GoldenTicketResource.php:127-130`, `PrizeRedemptions.php:108`, `RewardService.php:102-121`

- **CONFIRMADO con prueba 2026-10-23 (M0.8.4):** `tests/Auditoria/InventarioAdminTest.php`, en rojo. Sigue en la lista no repetir (M2.8.5).
### BUG-376: Cambiar el rol de un asistente tiene reglas distintas en el admin y en la API (RESUELTO 2026-10-23)
- **Severidad propuesta:** MEDIA
- **Que pasa:** Filament ofrece `attendee` y `staff_checkin`; la API acepta `Roles::ATTENDEE_ASSIGNABLE`, que no tiene `staff_checkin` y si tiene `vendedor` y los `admin*`. Hay que confirmar quien puede subir a alguien a admin por la API.
- **Fuente:** inventario de acciones del admin (`docs/contratos/acciones-admin.md`, M0.5.3), leido por un agente; **por verificar a mano**.
- **Cuando se arregla:** M0.8.4 (bloque de seguridad, cierre de la fase 0).
- **Archivos:** `AttendeeAdminResource.php:321-324`, `Support/Roles.php:60`, `Api/V1/Admin/AttendeeController.php:65`
- **Auditoria M0.8.1 (2026-10-23):** el camino se sostiene en dos revisiones independientes, pista #31 (`eventos-backend:admin-api:update-role-sub-admin-self-escalation`); prioridad sugerida A. **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/StaffYAdminApiTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo).

- **Resuelto 2026-10-23 (M0.8.4):** solo el admin completo cambia roles por la API y nadie el suyo. Prueba `tests/Feature/Security/PanelAdminSeguridadTest.php`. Backend `8b99911`.
### BUG-375: Cuatro formas de mandar push "ya", cada una con otro publico (ABIERTO 2026-10-22)
- **Severidad propuesta:** MEDIA
- **Que pasa:** Filament (`sendPush`, `send_now`, `resend`) solo usa Expo (deja fuera a quien solo tiene web push) y no excluye baneados. La API excluye baneados pero tambien solo usa Expo. El job programado si incluye web push y ademas crea un anuncio; `send_now` no.
- **Fuente:** inventario de acciones del admin (`docs/contratos/acciones-admin.md`, M0.5.3), leido por un agente; **por verificar a mano**.
- **Cuando se arregla:** lista no repetir del admin nuevo, M2.8.5 (antes, si aparece un cliente antes de la semana 12).
- **Archivos:** `AnnouncementResource.php:275-276`, `ScheduledNotificationResource.php:111-112, 151-152`, `Admin/NotificationController.php:78-80`, `DispatchScheduledNotificationsJob.php:24-54`

- **CONFIRMADO con prueba 2026-10-23 (M0.8.4):** `tests/Auditoria/InventarioAdminTest.php`, en rojo. Sigue en la lista no repetir (M2.8.5).
## 2026-10-22 — Fase 0, bloque 0.4: tiempo real (3 bugs: 3 resueltos)

### BUG-374: El admin muestra los eventos de todas las organizaciones (RESUELTO 2026-10-23)
- **Severidad:** ALTA cuando haya un segundo cliente (hoy hay una sola organizacion) — `EventResource` no tiene
  `getEloquentQuery`: cualquier usuario con acceso al panel ve todos los eventos y "Trabajar en este" lo deja operar en
  cualquiera, de cualquier organizacion. Los demas recursos filtran por el evento activo, asi que el cruce entra por aqui.
- **Visto:** inventario de acciones del admin (M0.5.3), verificado a mano.
- **Arreglo:** filtrar por `organization_id` del usuario (super_admin ve todo). Agendado: M0.8.3 (bloque de seguridad).
- **Archivos:** `app/Filament/Resources/EventResource.php`
- **Auditoria M0.8.1 (2026-10-23):** el camino se sostiene en dos revisiones independientes, pista #14 (`eventos-backend:staff:global-scope-no-org-binding`); prioridad sugerida A. **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/StaffYAdminApiTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo).
- **Resuelto 2026-10-23 (M0.8.3):** `users.organization_id` + tabla `event_staff`; `App\Support\AccesoDelStaff` (super_admin todo, org_admin su organizacion, event_admin y moderador los asignados) en el selector de evento, el contexto por defecto, `EventResource`, el Data Center y el CSV de sesion; Staff y permisos filtra por organizacion. Prueba `tests/Feature/Security/StaffPorOrganizacionTest.php`. Migracion aplicada en la base local. Backend `4a3153f`.

### BUG-373: Banear desde el admin no saca a la persona del socket en el momento (RESUELTO 2026-10-23)
- **Severidad:** MEDIA — `AttendeeAdminResource` crea el `AttendeeBan` pero no llama `/internal/ban/enforce`, que la
  API si llama (`Admin/BanController.php:180`). El `AttendeeBan::created` del AppServiceProvider solo borra la ficha de
  auth del socket: la conexion abierta sigue (chat incluido) hasta que se reconecte.
  Ademas Filament no guarda la IP en la auditoria del ban y la push de ban sale distinta que la de la API
  (`BanController.php:68-79`).
- **Visto:** inventario M0.5.3, verificado a mano.
- **Arreglo:** que las dos puertas usen el mismo servicio de ban. Agendado: M0.8.2 (bloque de seguridad).
- **Archivos:** `app/Filament/Resources/AttendeeAdminResource.php`, `Admin/BanController.php`
- **Auditoria M0.8.1 (2026-10-23):** el camino se sostiene en dos revisiones independientes, pista #56 (`eventos-socket:ban:live-revocation-not-propagated`); prioridad sugerida B. **CONFIRMADO con prueba 2026-10-23** (`tests/Auditoria/SocketLadoLaravelTest.php`, rama `auditoria/validacion` de eventos-backend, en rojo).
- **Resuelto 2026-10-23 (M0.8.2):** `App\Services\BanService` es la unica puerta (API y panel: aviso al socket, IP en la auditoria, misma push). En el socket, `/internal/ban/enforce` publica en `socket:config` y cada proceso desconecta a los suyos. Prueba: `tests/Feature/Security/BanUnicaPuertaTest.php` y el caso de dos procesos del arnes (rojo sin el arreglo, verde con el). Backend `fe92d72`, socket `4a5bb20`.

### BUG-372: "Enviar push" de un anuncio privado se lo manda a todo el evento, con el codigo del premio (RESUELTO 2026-10-22)
- **Severidad:** CRITICA (fuga de codigos canjeables) — `AnnouncementResource::sendPush` arma la audiencia por evento y
  roles e ignora `target_attendee_id`. Los anuncios privados (premio del Golden Ticket "Tienes un premio sin reclamar.
  Codigo: XXXXXX", respuestas de soporte) salen en la misma tabla ya publicados, con el boton "Enviar push" visible.
  Un clic del staff y el codigo llega a todos los telefonos del evento; cualquiera puede reclamar el premio.
- **Visto:** inventario M0.5.3, verificado a mano (`AnnouncementResource.php:273-292`, `GoldenTicketResource.php:143`).
- **Fix (backend a23884a, decision Kamilo):** `sendPush` manda solo al destinatario cuando el anuncio es privado; cubre
  los tres botones. `PushAnuncioPrivadoTest` (falla con el codigo anterior). Suite 1.141.
- **Archivos:** `app/Filament/Resources/AnnouncementResource.php`

### BUG-395: React avisa en desarrollo por el script del tema dentro del layout (ABIERTO 2026-10-22)
- **Severidad:** BAJA (aviso de consola en `next dev`, no se ve) — `Encountered a script tag while rendering React
  component`: es el script que pone `data-theme` antes de pintar para que no parpadee el tema (`src/app/[locale]/layout.tsx:69`).
  En la primera carga si corre (va en el HTML). Moverlo mal trae de vuelta el parpadeo.
- **Visto:** QA de hidratacion del BUG-358 (2026-10-22).
- **Cuando se arregla:** cuando se toque el layout; opcion `next/script` con `beforeInteractive`, probando que no
  parpadee en las dos pieles.
- **Archivos:** `eventos-web/src/app/[locale]/layout.tsx`

### BUG-371: Con la suite corriendo, un pedido real del servidor local leyo la config de pruebas (ABIERTO 2026-10-22)
- **Severidad:** BAJA (solo local) — durante `./vendor/bin/pest` completo, un pedido de Mission Control por Apache dio
  500: `no such table: personal_access_tokens (Connection: sqlite, database.sqlite)` en entorno production. Alguna
  prueba deja visible a Apache una config con sqlite por un instante. No se reprodujo sin la suite.
- **Regla mientras tanto:** no correr la suite completa mientras se hace QA en vivo contra Laragon.
- **Por revisar:** que prueba escribe algo compartido (`bootstrap/cache`, `.env`, `database.sqlite`).

### BUG-370: Mission Control abre pero la API le rechaza todo desde el 2FA del staff (RESUELTO 2026-10-22)
- **Severidad:** ALTA (herramienta del operador en vivo sin datos) — `/monitor/{id}?token=` crea con `createToken` un
  token de API para un admin/organizador/moderador del evento (`routes/web.php:491`). Desde D.1 del 2FA (backend
  `83e327c`, 2026-09-27) `Sanctum::authenticateAccessTokensUsing` (`AppServiceProvider.php:104`) borra y rechaza todo
  token de quien tiene `hasPanelAccess()`. Resultado: 401 en agenda, live-config, sponsors y juegos; el token desaparece
  de la tabla. El socket si conecta y entra a las salas (chat y Q&A en vivo llegan), pero lo que va por la API no.
  `ROADMAP-SEGURIDAD-STAFF.md:165` daba a Mission Control por "fuera del candado por diseño": no se vio el token interno.
  Chat monitor y control de asistencia usan el mismo token. El display crea `display-session-{id}` igual (por revisar).
- **Visto:** QA en vivo de las 5 paginas sueltas (M0.4.3) con Playwright, 2026-10-22.
- **Fix (backend 13aa3ac, decision Kamilo opcion A):** token acotado `App\Support\TokenDeOperador`: permisos
  `operador:{mission-control|display}`, `evento:{id}`, `sesion:{id}` en vez de `*`; la regla D.1 lo deja pasar solo en
  su lista de rutas (41 de Mission Control, 2 del display) y con su evento y su sesion; afuera 401 sin borrarlo. El
  display quedo de verdad en solo lectura (antes `*` pese al comentario "read-only"). `auth/me` en la lista: el socket
  valida ahi al vencer su ficha de Redis (15 min). 5 pruebas en `TokenDeOperadorTest`; en vivo Mission Control 11/11
  respuestas 200. Suite 1.135.
- **Archivos:** `routes/web.php` (/monitor, /display/session), `app/Providers/AppServiceProvider.php`

### BUG-369: El escaneo del totem de sala esperaba un aviso al socket que nadie recibia (RESUELTO 2026-10-22)
- **Severidad:** MEDIA (rendimiento en puerta) — `RoomCheckinService` mandaba `room:occupancy` a `event:{id}:admin` dentro
  del escaneo, de forma sincrona pese al comentario "async": una consulta de ocupacion + un POST de hasta 2 s antes de
  responderle al totem. Nadie se une a esa sala; ningun totem ni el Event Pulse lo usan (el Pulse pide el aforo a la API).
- **Fix (backend f8c3cde, decision Kamilo M0.4.6):** fuera el envio y el metodo. Junto salio `agenda:updated` (alargar
  sesion), que tampoco escuchaba nadie.
- **Archivos:** `app/Services/RoomCheckinService.php`, `Admin/SessionConfigController.php`

### BUG-368: El cartel de anuncio sobre el video no salia desde julio (RESUELTO 2026-10-22)
- **Severidad:** ALTA (funcion perdida sin aviso) — `useAnnouncementOverlay` escuchaba `announcement:new`, que el socket
  dejo de emitir el 2026-07-04 (los anuncios pasaron a `data:invalidate`). El anuncio llegaba a la campana pero el
  cartel de 5 s encima de la transmision no volvio a salir. Visto al revisar los sobrantes del socket (M0.4.6).
- **Fix (eventos-web dcf4079, decision Kamilo):** el cartel sale de la misma lista que la campana (marco, sin pedidos
  extra): la pagina del stream la recibe, `router.refresh` trae el anuncio nuevo y se muestra el que no estaba al abrir.
  3 pruebas vitest. **Ojo:** el cartel solo existe en escritorio; en celular nunca estuvo.
- **Archivos:** `src/hooks/streaming/useAnnouncementOverlay.ts`, `StreamShell.tsx`, `session-stream/[id]/page.tsx`

## 2026-10-22 — Fase 0, bloque 0.3: congelar las 247 rutas (3 bugs: 3 resueltos; 1 hallazgo por verificar)

### BUG-367: El Data Center no invalida sus estadisticas al cambiar los datos (RESUELTO 2026-10-22)
- **Severidad:** BAJA — `DataCenterCacheObserver` busca `{cache.prefix}:dc:stats:{id}:*` (`eventos_:dc:stats...`), pero la
  clave real es `{REDIS_PREFIX}{cache.prefix}dc:stats...` (`eventos_database_eventos_dc:stats...`, comprobado contra el
  Redis local). El patron nunca coincide y no lanza error, asi que tampoco corre el respaldo. Las cifras quedan viejas
  hasta que vence la cache: 60 s (300 s la del mes anterior). Visto al armar el catalogo de claves (M0.4.4).
- **Fix (backend 3add5d4, decision Kamilo):** version por evento como la de modulos: `ExportService::statsKey` arma
  `dc:stats:{id}:v{ver}` y `invalidateStats` hace INCR de `dc:stats:{id}:ver`. Todas las variantes (filtros, curr30,
  prev30) quedan viejas de una, sin `KEYS`. Las metas (`saveGoal`/`deleteGoal`) usan lo mismo. Probado contra el Redis
  local (v0 -> v1) y con 3 pruebas nuevas (filtradas, metas). Las pruebas no lo veian porque corren con cache en memoria.
- **Archivos:** `app/Observers/DataCenterCacheObserver.php`, `app/Services/ExportService.php`, `DataCenterController.php`

### BUG-366: Mission Control no se entera de que termino una trivia (RESUELTO 2026-10-22)
- **Severidad:** BAJA — `public/mission-control/app.js` escucha `game:ended`, pero Laravel emite `game:finished`
  (`GameController::nextQuestion`, ultima ronda). El panel de juegos del operador no cambia al cerrar la trivia; la
  webapp y la app si (escuchan `game:finished`). Visto al armar el catalogo del socket (M0.4.1).
- **Fix (backend b060f2b, decision Kamilo):** Mission Control escucha `game:finished` (ya une `event:{id}`, la sala
  donde llega). Catalogo del socket al dia.
- **Archivos:** `eventos-backend/public/mission-control/app.js`

### BUG-365: Las pruebas de contrato usaban el Redis de desarrollo y lo que escribian pasaba a la corrida siguiente (RESUELTO 2026-10-22)
- **Severidad:** MEDIA (solo pruebas) — `join` del sorteo guarda el pool en `game:{id}:pool` (TTL 1 h) en la base 0 del
  Redis local. La segunda corrida respondia 409 "Already joined": snapshot distinto segun cuando se corriera, y llaves de
  prueba en el Redis de desarrollo.
- **Fix (backend 749d312):** `ModoContratos::redisAparte`: base 15 solo para contratos, vaciada al empezar cada prueba y en
  `/__contratos/reiniciar`. C# hace lo mismo con su Redis en el reinicio.
- **Archivos:** `app/Support/ModoContratos.php`, `tests/Pest.php`

### BUG-364: En las pruebas de contrato los pasos de un flujo corrian todos como la primera persona (RESUELTO 2026-10-22)
- **Severidad:** ALTA (solo pruebas) — en modo interno el guard de Sanctum guarda al primer usuario autenticado de la
  prueba y no vuelve a leer el bearer: en un flujo dario → ana → fabio, ana y fabio eran dario. Contra un servidor (PHP o
  C#) no pasa. Revisado: ningun snapshot congelado cambio al corregirlo; lo destapo el sorteo con gente (M0.3.11).
- **Fix (backend 749d312):** `app('auth')->forgetGuards()` antes de cada peticion interna en `Apoyo::pedir`.
- **Archivos:** `tests/Contratos/Apoyo.php`

### BUG-363: `check-email` le entregaba el codigo de activacion de una cuenta importada a quien escribiera el correo (RESUELTO 2026-10-22)
- **Severidad:** CRITICA — persona importada por CSV y sin activar: cualquiera que supiera su correo recibia el
  `invitation_token` en la respuesta y podia ponerle su propia contraseña. Solo lo frenaba el dato de verificacion
  (cedula) si el evento lo tenia configurado (fix parcial de abril, 1198cae).
- **Fix (backend 80aef0b, decision Kamilo):** la respuesta ya no trae el codigo: `{status: pending_activation, email_sent}`;
  el codigo viaja solo en el correo (enlace `/join/{token}` que abre la app). La app ya manejaba el caso sin codigo.
- **Archivos:** `app/Http/Controllers/Api/V1/AuthController.php`, `tests/Feature/Auth/ActivacionImportadoTest.php`

### BUG-362: El correo de acceso del registro publico no llegaba nunca (RESUELTO 2026-10-22)
- **Severidad:** CRITICA — `WelcomeAccessMail` hereda de `WelcomeMail`, que guardaba la persona como `private`. Al
  serializar para la cola, `SerializesModels` solo ve las propiedades que la clase hija alcanza, asi que la persona se
  perdia y el job fallaba al salir de Redis. En produccion: registro OK, correo nunca enviado. Desde 7d46d5c (2026-10-12).
  Se vio porque con la cola sincrona de las pruebas de contrato el registro respondia 500.
- **Fix (backend 90e61f8):** `protected readonly` en `WelcomeMail`; prueba de regresion que serializa y vuelve.
- **Archivos:** `app/Mail/WelcomeMail.php`, `tests/Feature/Emails/WelcomeAccessMailTest.php`

### Sin numero: `GET events/{id}/documents/zip` devolvia `generated_at` siempre null (RESUELTO 2026-10-22)
- Faltaba el cast `datetime` de `documents_zip_generated_at`; `optional()` sobre un string devolvia null en silencio.
  Fix en backend 90e61f8 (`app/Models/Event.php`).

### Hallazgo por verificar: 22 columnas con `useCurrent()` toman la hora de la base, no la de la app
- Si el MySQL de produccion no esta en hora de Bogota, esas fechas (votos, historias, ediciones de leads, QR...) quedan
  corridas 5 horas. Se verifica en M1.2.5 (roadmap de la migracion).

## 2026-10-21 — Fase 0 de la migracion a .NET, bloques 0.1 y 0.2 (5 bugs: 5 resueltos)

### BUG-360: Laravel 12 tira el token CSRF al hacer login y la activacion del 2FA volvia a dar 419 (RESUELTO 2026-10-21)
- **Severidad:** ALTA — Al subir a Laravel 12 (backend dac3429), `SessionGuard::login()` paso de `migrate(true)` a
  `regenerate(true)`, que ademas regenera el token CSRF. La pagina de activacion del 2FA sigue abierta con el token viejo
  y el primer clic daba 419 "This page has expired": la misma regresion del QA del 2026-09-26, que el test
  `TwoFactorLoginTest > conserva el token CSRF` cazo al correr la suite completa.
- **Fix (backend dac3429):** `TwoFactorLogin::complete()` guarda el token antes de `login()` y lo vuelve a poner. El id
  de sesion si cambia (la fijacion de sesion sigue cubierta).
- **Archivos:** `app/Support/TwoFactorLogin.php`

### BUG-361: La ruta de "lo mio" se tragaba `GET /events/support/mine` y respondia 500 (RESUELTO 2026-10-21)
- **Severidad:** ALTA — `Route::get('/{eventId}/mine')` (85fd4c0, B3.9 del 2026-10-21) se registraba antes que
  `/support/mine`, asi que `{eventId}` tomaba el valor "support" y `LoMioController::index(int $eventId)` reventaba con
  TypeError. Seis tests de `SupportRequestTest` lo mostraron; en produccion la pantalla de soporte habria quedado sin
  "mis consultas".
- **Fix (backend dac3429):** `->whereNumber('eventId')` en la ruta de lo mio.
- **Archivos:** `routes/api/events.php`

### BUG-344, BUG-347 y BUG-348 (RESUELTOS 2026-10-21, ver sus entradas del 2026-09-13)
- BUG-344: `tests/TestCase::refreshApplication()` aborta con mensaje claro si la configuracion esta cacheada o la
  conexion no es sqlite, antes de que `RefreshDatabase` toque nada (backend dac3429). Probado con `config:cache`.
- BUG-347: `Http::preventStrayRequests()` + fake que contesta 200 solo a los POST hacia el socket, la webapp y exp.host;
  las lecturas las fakea cada test (backend 4cedb3e). Suite completa sin variables de entorno: 1.122 pasan en 153 s
  (antes 30 min con el socket apagado).
- BUG-348: el test prueba que la cache de stats se sirve mientras nada cambia y que un asistente nuevo la invalida de
  inmediato (backend 4cedb3e).

## 2026-10-18 — Navegacion inmediata y agenda/social livianas (4 bugs: 2 resueltos, 2 abiertos)

### BUG-357: La transicion de cambio de modulo mueve toda la tarjeta y encima los titulos (RESUELTO 2026-10-21)
- **Fix (eventos-web `f9e6bfc`, letra A "Relevo" de `design/features/navigation/lab-transicion-contenido.html`):**
  `CanvasCard` envuelve lo de adentro en `.canvas-card-contenido` (`view-transition-name: contenido`); la foto del
  wrapper `modulo` (marco incluido) no se anima; lo viejo se apaga en 120 ms y recien ahi entra lo nuevo (zoom 2%,
  395 ms). En celular, sin tarjeta, la misma secuencia sobre la caja `modulo`. Verificado con Playwright en produccion
  local leyendo `getAnimations()` en `startViewTransition().ready`: escritorio solo anima `contenido`, celular solo
  `modulo`, siempre en secuencia.
- **Texto original (2026-10-18):**
- **Severidad:** MEDIA (visual, lo vio Kamilo) — `TransicionModulo` (View Transitions) fotografia el wrapper completo:
  marco y fondo de la tarjeta hacen zoom, y lo viejo (150 ms) se apaga mientras lo nuevo (300 ms) ya aparece, asi que los
  titulos se cruzan. **Fix revisado sin escribir:** `view-transition-name` solo en un contenedor interno del contenido
  (`CanvasCard` envuelve sus hijos en un div 100%x100%; en celular las pantallas son `.m-screen` con `display: contents`,
  hay que darles caja), grupo `modulo` sin animacion para que el marco quede quieto, y fade through en secuencia (salida
  ~100 ms, entrada despues). Alternativa si no convence: quitar la transicion (corte).
- **Archivos:** `eventos-web/src/components/shell/TransicionModulo.tsx`, `src/app/globals.css`, `src/components/shell/CanvasCard.tsx`

### BUG-358: Error de hidratacion de React #418 en la webapp de produccion (RESUELTO 2026-10-22)
- **Severidad:** BAJA por ahora — dos `Minified React error #418` (texto distinto entre servidor y navegador) en la consola
  de `localhost:3000` (build de produccion) mientras se median pantallas. Sin causa ubicada; sospecha: horas o fechas
  calculadas distinto en servidor y navegador. Reproducir con `next dev` para ver el componente.
- **Causa (2026-10-22, reproducido con `next dev` + Playwright):** la sospecha era correcta. En home y agenda,
  `HappeningNowM` daba "Coming Up 08:00 a. m." con un espacio fino (U+202F) en un lado y normal en el otro, y sin locale
  fijo. 11 componentes formateaban la hora por su cuenta; el formateador comun `lib/format/time.ts` (que ya normalizaba)
  solo lo usaban 3.
- **Fix (eventos-web `2ff14a6`):** todas las horas pasan por `formatLocalTime` (mismas opciones, el texto visible no
  cambia); contadores de asistentes con `es-CO` fijo. `tests/lib/formatoHoras.test.ts` falla si alguien vuelve a usar
  `toLocaleTimeString` fuera de `lib/format/time.ts`. 9 pantallas sin error en escritorio y 5 en telefono. Vitest 688.

### BUG-359: Falta `mobileNav` en `messages/pt.json` (RESUELTO 2026-10-22)
- **Severidad:** BAJA — `pnpm build` imprime `MISSING_MESSAGE: mobileNav (pt)`: la barra movil en portugues sale sin textos.
  Ya venia de antes. **Fix:** copiar la seccion de `es.json` y traducir.
- **Fix (eventos-web `9dab432`):** tambien faltaba `mobileShell` (3 textos de "muy pronto"). 9 textos traducidos con el
  estilo de `pt.json`. `tests/lib/mensajes.test.ts` falla si a `en` o `pt` les falta o sobra un texto frente a `es`.

### BUG-356: La huella del token era un hash de 32 bits y ahora guarda la sesion (RESUELTO)
- **Severidad:** ALTA en cuanto la sesion (`/auth/me`) paso a memoria del servidor — con 32 bits y 5.000 asistentes la
  probabilidad de que dos tokens compartan huella era ~0,3% por evento, y una persona habria visto la sesion de otra.
- **Fix (webapp `368fe81`):** `huellaDeSesion` con SHA-256 (128 bits); la sesion se suelta en cualquier escritura con el
  token (`apiFetch`). Test del marco ajustado.
- **Archivos:** `eventos-web/src/lib/serverCache.ts`, `src/lib/auth.ts`, `src/lib/api.ts`

## 2026-10-16 — Bugs de riesgo para producción (3 resueltos)

### BUG-354: Horas de la webapp corridas 5 h en un servidor en UTC (RESUELTO)
- **Severidad:** ALTA — ~25 partes formatean fechas con la zona del proceso (`toLocaleTimeString` sin `timeZone`, `getHours`, agrupar la agenda por dia). En un servidor en UTC el HTML sale 5 h corrido frente al celular en Bogota y React reporta error de hidratacion.
- **Fix (eventos-web 95f52ca):** `instrumentation.ts` fija `TZ=America/Bogota` al arrancar si el despliegue no define otra. Pendiente para eventos en otra zona: formatear con la zona del evento.
- **Archivos:** `instrumentation.ts`, `tests/lib/zonaHoraria.test.ts`

### BUG-355: Rutas de cuenta sin limite de intentos (RESUELTO)
- **Severidad:** MEDIA — `/register`, `/forgot-password`, `/activate` y `/reset-password` no tenian throttle propio: se podian usar miles de veces para mandar correos o gastar el cupo de Resend.
- **Fix (backend):** limitadores `cuenta_correo` (5/h por correo, 600/h por red) y `cuenta_enlace` (10/h por enlace o correo, 300/min por red), umbrales en `config/auth.php` puerta. `LimitesDeCuentaTest` 5/5.
- **Archivos:** `app/Providers/AppServiceProvider.php`, `routes/api/auth.php`, `config/auth.php`

## 2026-09-29 — Cierre del 2FA del staff, S.4 a S.8 (5 bugs, todos resueltos)

> Contexto: `ROADMAP-SEGURIDAD-STAFF.md` 25/26. Todos cazados en QA vivo con
> Chrome o al implementar, y cada uno con test o medicion. Backend `217bc95`.

### BUG-353: "Copiar" no copiaba nada en el entorno local (RESUELTO)
- **Severidad:** MEDIA — En `http://*.test` no existe `navigator.clipboard` (contexto no seguro): el boton "Copiar" de los codigos de recuperacion y el de la clave del 2FA fallaban en silencio. **Fix:** respaldo con `textarea` + `execCommand("copy")` en la activacion y en "Seguridad de tu cuenta".
- **Archivos:** `resources/views/filament/auth/two-factor-setup.blade.php`, `resources/views/filament/pages/account-security.blade.php`

### BUG-352: La activacion del 2FA no era responsive y obligaba a hacer scroll (RESUELTO)
- **Severidad:** MEDIA (QA de Kamilo) — La tarjeta media ~850 px: 245 px de scroll en un portatil 1366x768 y 286 px en celular. Ademas la fuente monoespaciada caia en la del sistema en Windows. **Fix:** activacion v2 (dos columnas en portatil, clave primero en celular, `--lum-fm` JetBrains Mono, una sola escala de espacios). Lab: `design/features/admin-2fa/lab-2fa-activacion-v2.html`. Medido en la pagina real: 0 px de scroll en los tres tamaños.
- **Archivos:** `resources/css/filament/admin/theme.css`, `resources/views/filament/auth/two-factor-setup.blade.php`, `app/Filament/Auth/TwoFactorSetup.php`

### BUG-351: Restos de otra cuenta en la sesion expulsaban a quien acababa de entrar (RESUELTO)
- **Severidad:** ALTA — Si una cuenta se eliminaba con el admin abierto, su huella de contraseña quedaba en la sesion del navegador. La siguiente persona que entraba en ese navegador pasaba el 2FA y `AuthenticateSession` la expulsaba en su primer clic (huella vieja contra contraseña nueva). El segundo intento si funcionaba. **Fix:** `TwoFactorLogin::complete()` escribe la huella de quien entra. Test de regresion que falla sin el fix.
- **Archivos:** `app/Support/TwoFactorLogin.php`, `tests/Feature/Security/TwoFactorLoginTest.php`

### BUG-350: Una cuenta desactivada seguia entrando al admin (RESUELTO)
- **Severidad:** CRITICA (seguridad, previo a esta sesion) — `User::canAccessPanel()` solo miraba el rol y el login de Filament no revisa `is_active`: apagar "Cuenta activa" no sacaba a nadie. **Fix:** `canAccessPanel` exige `is_active !== false` (Filament lo revisa en el login y en cada peticion) y desactivar cierra sus sesiones y quita la confianza de sus equipos. Verificado en vivo con una sesion real en Redis.
- **Archivos:** `app/Models/User.php`, `tests/Feature/Security/StaffSessionTest.php`

### BUG-349: El doble envio del codigo tapaba los codigos de recuperacion recien generados (RESUELTO)
- **Severidad:** ALTA — El campo del codigo se envia solo al completar 6 digitos; si la persona ademas presionaba Enter llegaba un segundo envio con el mismo codigo, que salia "ya se uso", y el error tapaba los codigos nuevos: los viejos ya muertos y los nuevos sin ver. **Fix:** con codigos en pantalla el segundo envio no hace nada; en el reto, el mismo codigo incorrecto repetido cuenta una sola vez para el bloqueo. Tests de regresion.
- **Archivos:** `app/Filament/Pages/AccountSecurity.php`, `app/Filament/Auth/TwoFactorChallenge.php`

## 2026-09-13 — Auditoria de infraestructura + plan noviembre B1.1/B1.2 (10 bugs: 8 resueltos, 2 abiertos: BUG-345 va en B3.1, BUG-346 resuelto)

> Contexto: posible cliente en noviembre, evento de 5.000 personas. Plan en
> `docs/roadmaps/ROADMAP-INFRAESTRUCTURA.md` "PLAN DE TRABAJO — EVENTO DE NOVIEMBRE".
> Sin commit al registrar (esperando confirmacion de Kamilo).

### BUG-348: El test "stats are cached for 60s" del Data Center espera un comportamiento que se cambio a proposito en abril (RESUELTO 2026-10-21, backend 4cedb3e)
- **Severidad:** BAJA (test desactualizado, el codigo esta bien) — Es el unico fallo de la suite completa del 2026-09-13 (887 pasan, 1 falla). El test pide las estadisticas, crea un asistente y espera que la segunda consulta devuelva el valor viejo en cache (`registered = 0`); recibe 1. Falla siempre, con cache `array` y con `file`. **Causa:** `DataCenterCacheObserver` borra `dc:stats:{eventId}:*` al crear, actualizar o borrar los modelos del Data Center (registrado en `AppServiceProvider.php:95`). Lo agrego el commit `f6111e6` del 2026-04-25, posterior al commit del test (`07796dc`, mismo dia). La cache de 60 s ahora solo aplica mientras los datos no cambian, que es lo correcto para el organizador.
- **Ajeno a B1.1/B1.2:** ninguno de los archivos de ese cambio toca Data Center, observers, asistentes ni cache.
- **Fix pendiente:** reescribir el test para que pruebe lo que el codigo promete: dos consultas seguidas sin cambios devuelven el valor en cache, y un asistente nuevo invalida la cache y se ve de inmediato.
- **Archivos:** `tests/Feature/DataCenter/DataCenterApiTest.php:76`
### BUG-347: Los tests llaman al servidor de sockets real y la suite completa tarda horas (RESUELTO 2026-10-21, backend 4cedb3e)
- **Severidad:** ALTA (confiabilidad de la suite) — Controladores, servicios y observers avisan al socket con `Http::post` a `SOCKET_SERVER_URL` (`InvalidationService`, `CheckinService`, `GameService`, `WallController`, `PollController` y otros). Ningun test usa `Http::fake()` ni `Http::preventStrayRequests()`. En Windows un puerto cerrado de localhost no rechaza la conexion: espera el tiempo completo, medido en 2,00 s. Con el socket apagado, la suite completa (unos 950 tests en 87 archivos) corrio 29 minutos con 37 s de CPU sin terminar y se detuvo a mano. El resultado de la suite depende de si el socket esta prendido en la maquina.
- **Tambien la webapp:** `InvalidationService` avisa a la webapp local (`WEBAPP_INTERNAL_URLS`, por defecto `http://127.0.0.1:3000`) para soltar su cache del marco, con 1 s de espera por llamada. Con la webapp apagada, los tests unitarios de rate limit tardaban 1-2 s cada uno.
- **Mitigacion usada el 2026-09-13 para validar B1.1/B1.2:** correr Pest con `SOCKET_SERVER_URL=http://0.0.0.0:1` y `WEBAPP_INTERNAL_URLS=http://0.0.0.0:1`: en Windows esa direccion falla en 1 ms en vez de esperar.
- **Fix pendiente:** `Http::preventStrayRequests()` + fake de las rutas `/internal/*` del socket y de la webapp en `tests/TestCase.php`, y revisar los tests que hoy asumen la llamada real.
- **Archivos:** `tests/TestCase.php` (pendiente)

### BUG-346: Avisar al socket la configuracion de una sesion no tiene tiempo de espera propio (RESUELTO 2026-10-16)
- **Severidad:** ALTA — `SessionConfigController::emitConfigUpdate` usa `Http::post(...)` sin `timeout()`. Todas las demas llamadas internas al socket usan `Http::timeout(2)`. Sin tiempo propio aplica el de Laravel (`connect_timeout` 10 s en `PendingRequest`). **En produccion, si el nodo de sockets se cae, cada cambio de configuracion de sesion bloquea un proceso PHP-FPM hasta 10 s**, y con varios moderadores cambiando la configuracion en vivo se come los procesos de la API. En tests, las 17 peticiones que cambian configuracion en `SessionConfigTest` suman minutos.
- **Fix aplicado (backend a20df92):** `Http::timeout(2)` como el resto; los 3 jobs de push a Expo con `timeout(10)`. `tests/Unit/HttpTimeoutTest.php` falla si cualquier `Http::` de `app/` no define timeout.
- **Archivos:** `app/Http/Controllers/Api/V1/Admin/SessionConfigController.php:495`
### BUG-345: El correo no controla su ritmo frente al limite de Resend (ABIERTO → plan B3.1)
- **Severidad:** ALTA — Resend permite 10 peticiones por segundo por equipo (docs de Resend, ampliable por solicitud). `SendEmailJob` no limita el ritmo: solo `tries = 3` y `backoff = 60`. Con 5.000 invitaciones, o 5.000 personas pidiendo enlace de acceso al abrir puertas, los envios que choquen con el limite se reintentan tres veces y se pierden.
- **Fix previsto:** cola limitada por debajo del techo, enlaces de acceso con prioridad sobre invitaciones, reintentos que no pierdan correos y pedir ampliacion a Resend. Item B3.1 del plan de noviembre.
- **Archivos:** `app/Jobs/SendEmailJob.php`

### BUG-344: Pest con la configuracion cacheada corre contra la base MySQL de desarrollo (RESUELTO 2026-10-21, backend dac3429)
- **Severidad:** ALTA (riesgo de perdida de datos) — Con `bootstrap/cache/config.php` presente (lo deja `DEMO-BUILD.bat` via `artisan optimize`), Laravel ignora los `<env>` de `phpunit.xml` y los tests usan `eventos_db` en MySQL. El 2026-09-13 fallaron 53 tests con "connection refused" porque MySQL estaba apagado. **Con MySQL encendido, `RefreshDatabase` habria migrado de cero la base de desarrollo.**
- **Mitigacion aplicada:** `php artisan config:clear` antes de testear + memoria `feedback_tests_config_cache`.
- **Fix pendiente:** guarda en `tests/TestCase.php` que aborte si la conexion no es `sqlite` o si existe la cache de configuracion.
- **Archivos:** `tests/TestCase.php` (pendiente)

### BUG-343: El chequeo nuevo de staff lanzaba excepcion sin roles y se saltaba la revision del evento demo (RESUELTO antes de commit)
- **Severidad:** ALTA — El chequeo de staff con clave "password" usaba `User::role(Roles::PANEL_ACCESS)`, que lanza excepcion si los roles no existen. El `catch` la convertia en advertencia y el chequeo del evento demo, que viene despues, nunca corria: una base sin roles con el evento demo pasaba en verde. Cazado por el test "security:check fails when the demo event exists".
- **Fix:** `User::whereHas('roles', fn ($q) => $q->whereIn('name', Roles::PANEL_ACCESS))`, que no lanza.
- **Archivos:** `app/Console/Commands/SecurityCheckCommand.php`

### BUG-342: La base de tests rechazaba las plantillas de correo de acceso y de recap (RESUELTO)
- **Severidad:** MEDIA (solo entorno de tests) — Los tipos `magic_link` y `recap_ready` se agregan con `ALTER TABLE ... MODIFY ENUM` en las migraciones `2026_04_26_120003` y `2026_05_02_031313`, que solo corren en MySQL. En SQLite la columna quedaba con la lista original y el CHECK rechazaba esas plantillas (`CHECK constraint failed: type`). Ningun test sembraba esas plantillas, por eso nunca aparecio.
- **Fix:** los dos tipos declarados tambien en la migracion original, en `email_templates.type` y `email_logs.type`. En MySQL los ALTER posteriores reescriben el mismo ENUM.
- **Archivos:** `database/migrations/2026_04_05_000001_create_email_templates_table.php`

### BUG-341: En una base sin eventos el admin abria un Escritorio vacio en vez del wizard (RESUELTO)
- **Severidad:** MEDIA — `SetFilamentEventContext` deja el contexto vacio si no hay eventos, y el Escritorio salia sin evento ni tareas. En un servidor nuevo, lo primero que hay que hacer es crear el evento.
- **Fix:** `Dashboard::mount()` redirige al wizard de crear evento cuando no existe ningun evento. Tests `PrimerIngresoWizardTest` (2).
- **Archivos:** `app/Filament/Pages/Dashboard.php`, `tests/Feature/Admin/PrimerIngresoWizardTest.php`

### BUG-340: `security:check` dejaba pasar el demo y la cuenta de prueba (RESUELTO)
- **Severidad:** ALTA — El chequeo revisaba APP_DEBUG y claves de BD, Redis y secretos, pero no la base: un servidor con `superadmin@eventos.test` / `password` y el evento demo salia en verde.
- **Fix:** tres chequeos criticos nuevos: usuarios `@eventos.test`, staff con acceso al panel con clave "password" (solo staff, para no revisar miles de claves con bcrypt) y el evento demo `summit-empresarial-2026` (advertencia en vez de error si `EVENTOS_DEMO=true`). 4 tests nuevos.
- **Archivos:** `app/Console/Commands/SecurityCheckCommand.php`, `tests/Feature/Security/SecurityCheckCommandTest.php`

### BUG-339: Un servidor nuevo quedaba con el demo entero y un superadmin con clave "password" (RESUELTO)
- **Severidad:** CRITICA (seguridad) — `deploy.sh` corria `php artisan db:seed --force`, y `DatabaseSeeder` siembra el evento demo, asistentes, fotos y `UserSeeder` crea `superadmin@eventos.test` con clave `password`. Cualquier cliente montado con el script habria recibido eso.
- **Fix:** `SistemaSeeder` con solo lo global (roles, plantillas de modulos y de correo); comando `php artisan eventos:instalar` (organizacion + sistema + superadmin con clave generada mostrada una vez, se niega si la base tiene datos); `DatabaseSeeder` se niega en produccion salvo `EVENTOS_DEMO=true`; `deploy.sh` y `COMO-VOLVER.md` usan la instalacion. Verificado sobre una base real: 1 organizacion, 1 usuario, 8 roles, 24 plantillas de modulos, 30 de correo, 0 eventos. 6 tests nuevos.
- **Archivos:** `app/Console/Commands/InstalarCommand.php`, `database/seeders/SistemaSeeder.php`, `database/seeders/DatabaseSeeder.php`, `config/app.php`, `.env.production.example`, `tests/Feature/Security/InstalarCommandTest.php`, `docs/infra/deploy.sh`, `docs/infra/COMO-VOLVER.md`

## 2026-06-21 — Sesion DaVinci W.7 Sponsors completo (4 bugs)

### BUG-338: Polish visuales W.7 — 4 issues agrupados (RESUELTO)
- **Severidad:** MEDIA — Detectados en testing live del Wall + DetailPanel:
  1. **Halo accent en hover Platinum** rompia con eventos cuya `primary_color` es rojo/coral (cliente real lo verifico — "outline rojo del accent sobra"). Bug visual coherencia tema.
  2. **Elevacion Noir desaparecia durante living shuffle** porque solo estaba definida en `:hover`. Cuando las cards se reordenan cada 7s, el mouse deja de hovear y volvian a estado plano momentaneamente.
  3. **Heart pop animation CSS vanilla** se veia cortada/forzada (keyframes `sp-heart-pop` 360ms con 3 keyframes scale 1→1.3→1). Usuario reporto "se ve toda forzada y cortada no se ve natural".
  4. **Toast `+puntos por visitar` inline en el hero del detail** violaba el patron lumina del proyecto (todos los toasts viven top-center, 1 a la vez). Tambien aparecia siempre aunque el usuario ya hubiera visitado (ver BUG-337).
- **Fix:**
  1. Removido halo accent. Reemplazado por `box-shadow: 0 8px 20px -8px rgba(0,0,0,0.55)` neutral en Noir (sin tinte de color del cliente).
  2. Shadow base aplicada en estado reposo (no solo hover) — ahora la elevacion sobrevive al shuffle aunque el mouse se pierda del target.
  3. CSS keyframes removidas. Reemplazadas por framer-motion `AnimatePresence` con `key={isFavorite-tick}` que fuerza remount + spring damping 10 stiffness 380 mass 0.6 fresh en cada toggle. Tambien `whileTap` scale 0.88 spring.
  4. Toast movido a `lumina.success({message: "+N puntos por visitar"})` top-center. Hero quedo limpio sin floating toast inline.
- **Archivos:** `sponsors.css` (shadows + remove `.sp-heart-pop` keyframes), `SponsorHero.tsx` (motion.button + AnimatePresence), `SponsorDetailPanel.tsx` (lumina call), removidos halo accent + transition transform CSS para no chocar con framer-motion `layout`
- **Aprendizaje:** **Cuando un elemento usa framer-motion `layout` (shuffle smooth), NO aplicar tambien CSS `transition: transform` — chocan**. Ademas, **animaciones de UX micro (heart pop, badge pulse) deben ir en framer-motion, no en CSS keyframes vanilla** — el spring physics da naturalidad que los keyframes con 3 puntos no logran. CSS keyframes solo para animaciones de carga decorativas (skeleton shimmer).

### BUG-337: visitStand devolvia visit_points sin distinguir si tryAward otorgo (UI mostraba toast falso) (RESUELTO)
- **Severidad:** ALTA — `GamificationController::visitStand` siempre devolvia `visit_points: $sponsor->visit_points`, sin importar si `PointsService::tryAward` efectivamente otorgo puntos. tryAward es idempotente: retorna `null` si el usuario ya visito ese sponsor antes, si gamification esta deshabilitada para la accion `visit_stand`, o si el role del attendee no permite recibir esa accion. **El cliente webapp W.7 mostraba toast `+50 puntos por visitar` cada vez que el usuario abria el detail del mismo sponsor**, aunque ya hubiera recibido los puntos en una visita anterior. UX enganosa — el cliente crei que ganaba puntos cuando no.
- **Fix:** `visitStand` ahora captura el return de `tryAward` (PointsLog o null) y devuelve `points_awarded: int` (0 si null). Cliente solo muestra toast si `points_awarded > 0`. `VisitStandResponse` type frontend tambien agrega el campo.
- **Archivos:** `GamificationController.php` (capture log + return points_awarded), `lib/types/sponsor.ts` (type), `SponsorDetailPanel.tsx` (condicional toast)
- **Aprendizaje:** **Endpoints gamificados que llaman a `tryAward` deben SIEMPRE exponer si efectivamente otorgaron puntos**, no solo el valor configurado. Aplicable a futuros endpoints: rate_session, ask_question, wall_post, vote_poll, etc. tryAward es idempotente por diseno — la respuesta del controller tiene que reflejarlo.

### BUG-336: SponsorResource no exponia trivia_enabled / passport_enabled / visit_points (RESUELTO)
- **Severidad:** MEDIA — Gap del SponsorResource backend: los 3 campos del Sponsor model (`trivia_enabled`, `passport_enabled`, `visit_points`) NO estaban en el toArray. El cliente webapp W.7 los necesita para:
  1. Mostrar badges pasaporte/trivia en las cards del wall ANTES de pegar visit-stand
  2. Decidir si renderizar el panel trivia condicional
  3. Mostrar el valor de visit_points en el toast/UI
  Si el cliente solo se entera de la trivia AL pegar visit-stand, hay un delay UX evidente.
- **Fix:** Agregados los 3 campos al `SponsorResource::toArray` (boolean casts + int cast para visit_points).
- **Archivos:** `app/Http/Resources/V1/SponsorResource.php` (+3 lineas)
- **Aprendizaje:** Antes de implementar el cliente de cualquier feature gamificado, verificar que el Resource expone TODOS los flags de la entidad. Pequeño gap = trabajo de re-fetch innecesario en cliente.

### BUG-335: next-themes 0.4.6 incompatible Next 16 + React 19 ("Encountered a script tag") (RESUELTO)
- **Severidad:** ALTA — `next-themes 0.4.6` inyecta su script anti-FOUC adentro del cliente component (ThemeProvider) con `<script dangerouslySetInnerHTML>`. React 19 + Next 16 Turbopack lo flagea como error de runtime: *"Encountered a script tag while rendering React component. Scripts inside React components are never executed when rendering on the client."* Bug abierto upstream sin fix: issues #385 (Mar 19, 2026) y #387 (Mar 24, 2026), ambos `open + bug + triage`. Sin ETA del mantainer.
- **Fix:** Reemplazo total de next-themes con provider propio en `src/components/providers/ThemeProvider.tsx` (~60 lineas). API minima compatible: `useTheme()` retorna `{theme, resolvedTheme, setTheme}`. Script anti-FOUC inline en `<head>` del LocaleLayout server component (donde si es safe). Actualizado 4 consumers (ThemeToggle, ThemeTogglePill, EventThemeProvider, sonner) cambiando solo el import.
- **Archivos:** `src/app/[locale]/layout.tsx` (script inline en head), `src/components/providers/ThemeProvider.tsx` (rewrite), 4 consumers (cambio import)
- **Aprendizaje:** **Para dependencias que injectan scripts blocking (anti-FOUC, theme detection, analytics critico), preferir provider propio cuando el package tiene bugs upstream sin fix activo**. 60 lineas con context propio es trivial; debugging un bug de tercero puede costar dias. next-themes queda en package.json sin desinstalar por si hay uso indirecto en otra parte de la build.

---

## 2026-06-20 — Sesion DaVinci pulido social + responsive (6 bugs)

### BUG-334: React key warning en BlockedRow (RESUELTO)
- **Severidad:** MEDIA — `SolicitudesView.tsx` linea 136: `<BlockedRow key={item.id} ...>` con `item.id` undefined porque `fetchBlockedAttendees` se tipo como `AttendeeListItem[]` (que tiene `id`), pero el backend `/me/blocked` devuelve shape `BlockedItem` (con `attendee_id` + `blocked_at`, NO `id`). Todos los rows tenian key=undefined → warning React "Each child should have a unique key prop"
- **Fix:** `lib/social.ts` cambio tipo `AttendeeListItem[]` → `BlockedItem[]`. Propagado a `SocialView.tsx` (state `blocked` tipado correcto, handler `handleUnblock` usa `attendee_id`, `handleBlocked` construye BlockedItem con shape correcto + timestamp local optimistic). `SolicitudesView.tsx` props + `BlockedRow` con `key={item.attendee_id}`
- **Archivos:** `eventos-web/src/lib/social.ts`, `src/components/app/social/SocialView.tsx`, `src/components/app/social/SolicitudesView.tsx`

### BUG-333: Skeleton AttendeeProfilePanel v1 sobreprometia (RESUELTO)
- **Severidad:** BAJA — v1 del skeleton renderizaba 3 secciones estructuradas (titulo "Sobre" + 3 lineas + titulo "Intereses" + 5 chips + titulo "Asistira a" + 2 cards). Las secciones reales son CONDICIONALES (`profile.bio?`, `profile.interests.length>0?`, `profile.sessions.length>0?`). Para attendees sin bio/intereses/sesiones, el usuario veia el skeleton elaborado → cargaba → todo desaparecia. Mismatch visual confuso
- **Fix:** v2 neutro — 1 titulo chico + 3 lineas tipo bio sin titulos especificos. Honesto: "viene mas info" sin promesa de estructura. Borrado CSS auxiliar `.sn-pp-sk-*` (chips, sessions, section)
- **Archivos:** `eventos-web/src/components/app/social/AttendeeProfilePanel.tsx`, `src/components/app/social/social.css`

### BUG-332: Boton outline en Lux se veia lavado (RESUELTO)
- **Severidad:** MEDIA — Despues del fix de prominencia (BUG-330 bajamos a outline ghost), el `.sn-pp-btn.primary` usaba `color: var(--slate-light)` y borde con color-mix de slate. `--slate-light` es el MISMO hex en Noir y Lux (token independiente del tema). En Lux (fondo claro #f6f8fa) el slate-light quedaba poco contrastado, casi invisible. Usuario reporto "en lux se ve pesimo"
- **Fix:** Cambio tokens hardcoded a tokens theme-aware — `color: var(--text-primary)` + `border-color: var(--border-strong)` + hover `background: var(--surface-low)`. Mismo patron que `sn-rqx-btn.rej` (Ignorar) que ya estaba bien resuelto. Tokens se invierten automatico Noir↔Lux
- **Archivos:** `eventos-web/src/components/app/social/social.css` linea ~1078

### BUG-331: Boton "Conectar" sobredimensionado / dominante visualmente (RESUELTO)
- **Severidad:** MEDIA — Despues del fix responsive inicial (BUG-329), el boton seguia viendose "gigante como si tuviera ceguera" (palabras del usuario). El boton `.sn-pp-btn.primary` era solido `var(--slate-light)` + texto negro contrastante + bold 700 → pop visual dominante. En una card de directorio el HERO debe ser avatar+nombre, el CTA es terciario
- **Fix:** Pattern espejo de LinkedIn/Twitter "Connect" en directorios. Outline ghost (bg transparent + border + text color), font weight 700→600 (semibold), padding compacto 5/11, max-width 220→150, font-size 11px. Bajada de jerarquia visual completa
- **Archivos:** `eventos-web/src/components/app/social/social.css` linea ~1043

### BUG-330: Grid Personas con tiers fijos truncaba boton "Conectar" en 1366x768 (RESUELTO)
- **Severidad:** ALTA — `.sn-pp-grid` con `grid-template-columns: repeat(2, 1fr)` rigido. En 1366x768 con perfil abierto: 1366 - sidebar W.0 (80) - sn-col-left (260) - sn-col-right (360 perfil abierto) = ~666 px centro / 2 cols = 323 cada card → boton "+ Conectar" se cortaba mostrando "+ Conec". Container query existia (@1280) pero el canvas raiz quedaba >1280 en 1366, asi que NO disparaba
- **Fix (3 iteraciones):**
  1. v1: tiers progresivos 3 niveles container query (1440/1100) + sidebars mas chicos. Parche, no responsive real.
  2. v2: responsive PROPORCIONAL real — `clamp(200px, 17%, 260px)` para sidebars, `repeat(auto-fill, minmax(min(260px, 100%), 1fr))` para grid (espejo de live.css), `min-width: max-content` + `flex-wrap: wrap` en boton para nunca truncar.
  3. v3: compactacion + outline ghost (ver BUG-330).
- **Archivos:** `eventos-web/src/components/app/social/social.css` linea ~7-30 (canvas) y ~951-1085 (grid+btn)

### BUG-329: Suite vitest 194/194 fallando (Node 25 experimental localStorage) (RESUELTO)
- **Severidad:** CRITICA — Bloqueante de cierre formal de cualquier modulo (no se puede declarar W.5 "cerrado" con tests rojos). Re-auditoria 2026-06-20 detecto que TODOS los 194 tests vitest fallaban con `TypeError: localStorage.clear is not a function` en `tests/setup.ts:17`. Causa raiz: Node 25.8.1 expone un `globalThis.localStorage` experimental (warning `--localstorage-file was provided without a valid path`) que shadowea el de jsdom 29 con un stub sin metodos
- **Fix:** `tests/setup.ts` con `beforeAll` que stubea `globalThis.localStorage` Y `window.localStorage` con un Storage propio Map-based via `Object.defineProperty`. Una sesion, un archivo, un fix
- **Archivos:** `eventos-web/tests/setup.ts`

---

## 2026-05-06 — W.0 Shell audit (3 bugs)

### BUG-328: Bell button del sidebar sin onClick (UX confusa) (RESUELTO)
- **Severidad:** BAJA — `SidebarPill.tsx` renderizaba el bell como `<button type="button">` sin `onClick`. Visualmente clickeable, sin accion. Combinado con BUG-326 (badge "3" hardcoded) sugeria al usuario que hay notifs por leer pero al click no pasa nada
- **Fix:** Cambiado a `<span>` con `cursor: default` + tooltip "notifications · proximamente". Cuando W.10 wiree el panel notif, restaurar como `<button>` con handler. Color cambiado a `--text-label` para reforzar visualmente el estado coming-soon
- **Archivo:** `eventos-web/src/components/shell/SidebarPill.tsx`

### BUG-327: Sidebar items navegando a 404 silencioso (RESUELTO)
- **Severidad:** ALTA — `SidebarPill.tsx` listaba 6 items pero solo `/home` y `/agenda` tienen ruta implementada. Click en `/live`, `/networking`, `/social`, `/sponsors` → Next 404 page (sale del shell spatial, rompe la sensacion de app). Estos modulos viven en W.4/W.6/W.7/W.8 (no implementados)
- **Fix:** Refactor a componente interno `<NavSlot>` con flag `available: boolean`. Items NO disponibles renderizan `<span aria-disabled>` con opacity 0.55, cursor default + tooltip "{label} · proximamente". El icono `live` mantiene el dot pulse pero atenuado. Cuando los modulos existan, cambiar `available: false` → `true`
- **Archivo:** `eventos-web/src/components/shell/SidebarPill.tsx`

### BUG-326: unreadNotifications hardcoded a 3 en SpatialShell (RESUELTO)
- **Severidad:** MEDIA — `SpatialShell.tsx` pasaba `unreadNotifications={3}` literal al SidebarPill. Cualquier usuario en cualquier evento veia el badge "3" en el bell sin importar su estado real de notificaciones. Backend NO tiene endpoint de notif count y la app movil tampoco lo consulta (solo registra push token via `/auth/expo-token`)
- **Fix:** Eliminado el hardcode. Prop `unreadNotifications` ahora opcional sin default — si no se pasa, NO se muestra badge. Cuando W.10 implemente panel notif con endpoint real (ej. `GET /me/notifications/unread-count`), pasarlo desde el server component
- **Archivos:** `eventos-web/src/components/shell/SpatialShell.tsx`, `eventos-web/src/components/shell/SidebarPill.tsx`

---

## 2026-05-06 — W.2 Home audit + wired backend recap (3 bugs)

### BUG-325: :focus-visible global pintaba halo accent gigante en TODOS los modulos (RESUELTO)
- **Severidad:** MEDIA — `globals.css` linea 455 definia `:focus-visible` con `outline: 2px solid var(--accent)` + `box-shadow: 0 0 0 4px color-mix(--accent 20%)`. Con `primary_color` rojo del cliente (Bancolombia), TODOS los botones de TODOS los modulos al click mostraban un halo rojo de 4px que el usuario interpretaba como animacion errante. El fix de W.3 (BUG-319) solo cubrio `.agenda-root`; W.2 home y demas modulos seguian afectados
- **Fix:** Override global del `:focus-visible` con outline sutil `color-mix(in srgb, var(--accent) 65%, var(--text-muted))` + outline-offset 2px + SIN box-shadow ring. Mantiene a11y WCAG con outline visible pero sin halo accent. Para CTAs primarios que necesiten ring accent destacado, usar utility `.focus-accent-ring` explicito (TODO crear cuando aplique). El override de `.agenda-root` se mantiene como doble-defensa
- **Archivo:** `eventos-web/src/app/globals.css` linea 455

### BUG-324: TODO referenciaba endpoint inexistente `/me/recap-stats` (RESUELTO)
- **Severidad:** BAJA — `EndedState.tsx` tenia comentario `// TODO: stats reales del usuario via /api/v1/event/{id}/me/recap-stats`. Ese endpoint NO existe en el backend. El real es `GET /events/{id}/my-recap` (controlado por `RecapController@myRecap`) y devuelve un shape muy distinto al asumido (discriminated union de 3 casos: not_enabled, threshold_not_met, available)
- **Fix:** Eliminado TODO obsoleto. Wireado al endpoint real en `lib/recap.ts` con tipo `RecapState` que mapea exactamente los 3 casos. `BACKEND-API-MAP.md` corregido — la entrada anterior decia `{data: {sessions_attended, points_earned, connections_made}}` (inventado por el agent en el barrido inicial), reemplazada con shape real verificado via curl
- **Archivos:** `eventos-web/src/components/app/home/EndedState.tsx`, `eventos-web/src/lib/recap.ts`, `docs/webapp/BACKEND-API-MAP.md`

### BUG-323: EndedState mostraba stats hardcoded a TODOS los usuarios (CRITICA — produccion)
- **Severidad:** CRITICA — `EndedState.tsx` linea 24 declaraba `const stats = { hoursLive: 14, sessionsAttended: 8, connections: 32, followUps: 12, tier: "Gold", tierTopPercent: 18 }`. Cualquier usuario que entrara a un evento ENDED veia exactamente "14h, 8 sesiones, 32 conexiones, Gold Top 18%" sin importar su asistencia real. Marcado en roadmap W.2 como "DONE (mock data)" pero quedo como deuda activa olvidada — bug de produccion latente
- **Fix:** Wireado al endpoint real `/events/{id}/my-recap` + `/me/contacts` para connections count. EndedState ahora maneja 3 casos: (1) recap deshabilitado por organizador → "Gracias por participar", (2) usuario no cumple threshold → mensaje explicativo sin stats, (3) recap disponible → 4 stats reales (hours/sessions/connections/days) + tier real (insider/activo/headliner segun config del organizador) + CTA "Ver mi recap" + "Descargar certificado" condicional al config. Campos inventados eliminados: `followUps` (no existe en backend), `tierTopPercent` (no existe — el ranking percentil no se calcula). `tier` y label vienen de `recap.config.tiers.labels` (customizable per evento)
- **Archivos:** `eventos-web/src/components/app/home/EndedState.tsx`, `eventos-web/src/components/app/home/HomeView.tsx`, `eventos-web/src/lib/recap.ts` (nuevo), `eventos-web/src/app/[locale]/(app)/home/page.tsx`

---

## 2026-05-06 — W.3 Agenda webapp implementacion + barrido backend (5 bugs)

### BUG-322: Re-rate sesion sin manejo del 409 backend (RESUELTO)
- **Severidad:** ALTA — Backend `RatingController@store` valida UNIQUE constraint per user/session y retorna 409 con `{message: "Ya evaluaste esta sesion"}`. La UI permitia reabrir el RatingModal con `initialRating` precargado y al submit el usuario veia toast de error generico
- **Fix:** Estrellas readonly en `SessionCard` ahora son `<span>` (no `<button>`), no clickeables. En `DetailPanel` se oculta el boton "Evaluar" y se muestra `dp-rated` con las estrellas. El modal precarga `initialRating` solo para casos donde aun se permita editar (pendiente decision producto)
- **Archivos:** `eventos-web/src/components/app/agenda/{SessionCard,DetailPanel,RatingModal,AgendaView}.tsx`

### BUG-321: Asistencia mostraba "0 confirmados" con avatares de speakers (RESUELTO)
- **Severidad:** MEDIA — DetailPanel renderizaba seccion "Asistencia" con avatares (mock = primeros 6 speakers) pero el count "X confirmados" usaba `favorites_count` real (a menudo 0). Visualmente: caras visibles + texto "0" → confunde
- **Fix:** Seccion "Asistencia" oculta hasta que exista endpoint `/sessions/{id}/attendees` (W.8). El count de "interesados" (= `favorites_count`) se muestra como meta-row en el card de info, sin avatares. Markup completo del bloque preservado en codigo con TODO documentado
- **Archivos:** `eventos-web/src/components/app/agenda/{AgendaView,DetailPanel}.tsx`

### BUG-320: Heart de SessionCard animaba al favoritar desde DetailPanel (RESUELTO)
- **Severidad:** MEDIA — Patron inicial: `useEffect(() => { if (was=false && now=true) playHeartAnim() }, [is_favorite])`. En spatial UI la lista queda visible al lado del detail; favoritar desde el boton del detail cambia state, dispara effect, anima la card en la lista → doble feedback visual confuso
- **Fix:** Animacion solo en el `onClick` directo del propio botón (heart card o boton detail). Sin useEffect cross-state. Cuando favoriteas desde detail, la card actualiza su fill pero no anima
- **Archivos:** `eventos-web/src/components/app/agenda/{SessionCard,DetailPanel}.tsx`

### BUG-319: focus-visible global pintaba ring rojo en botones del agenda (RESUELTO)
- **Severidad:** MEDIA — `globals.css` define `:focus-visible { outline: 2px solid var(--accent); box-shadow: 0 0 0 4px color-mix(...accent 20%) }` para a11y. En eventos con `primary_color` rojo (Bancolombia), al click en el boton "Favorita" del DetailPanel aparece un halo rojo de 4px que el usuario interpretaba como anim del heart
- **Fix:** Override en `agenda.css` dentro de `.agenda-root` para botones: outline 2px del `--ag-border-strong` (sutil), `box-shadow: none`. Mantiene a11y WCAG sin el halo accent gigante
- **Archivos:** `eventos-web/src/components/app/agenda/agenda.css`

### BUG-318: lumina toast dentro del setState updater (CRITICA — React 19 strict)
- **Severidad:** CRITICA — `toggleFavorite` llamaba `lumina.favorite/info()` dentro de la funcion updater de `setDays((prev) => {...})`. React 19 Strict Mode corre el updater 2x para detectar side effects → toast se disparaba duplicado + setState en `LuminaToastViewport` durante render de `AgendaView` → warning "Cannot update a component (`LuminaToastViewport`) while rendering a different component (`AgendaView`)"
- **Fix:** Lookup ANTES del setDays (`findSession(days, id)`) → setDays con la mutacion pura → toast despues del setDays. El updater queda 100% puro
- **Patron a evitar:** NUNCA llamar funciones con side effects (toast, navigation, analytics) dentro de updater functions de useState. Solo cambios al state.
- **Archivos:** `eventos-web/src/components/app/agenda/AgendaView.tsx`

---

## 2026-05-02 — Auditoria seguridad webapp post-F10 (4 bugs)

### BUG-317: Webapp sin headers de seguridad basicos (RESUELTO)
- **Severidad:** MEDIA — `next.config.ts` no configuraba ningun header de seguridad. Sin `X-Frame-Options` la webapp es embebible en iframes maliciosos (clickjacking). Sin `X-Content-Type-Options: nosniff` browsers pueden inferir MIME types incorrectos (XSS via assets). Sin HSTS, downgrade attacks HTTPS→HTTP posibles
- **Fix:** `next.config.ts` agrega `headers()` con: `X-Frame-Options: SAMEORIGIN` (override per-evento si Bancolombia pide embed via `events.embed_allowed_origins`), `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Strict-Transport-Security: max-age=31536000; includeSubDomains`, `X-DNS-Prefetch-Control: on`. CSP completa diferida a W.4 streaming (necesita whitelisting Vimeo + socket.eventos.app + Sentry tunnel `/monitoring`)
- **Verificacion:** `curl -I http://localhost:3000/es/login` confirma headers en response
- **Archivo:** `eventos-web/next.config.ts`

### BUG-316: PII (nombre usuario) en localStorage + cookie (RESUELTO)
- **Severidad:** MEDIA — `useLastEmail` guardaba `eventos:lastUserName` en localStorage. `login/page.tsx` leia cookie `eventos_last_name`. Email en localStorage es aceptable (low sensitivity, public-ish), pero el nombre + email = PII identificable bajo GDPR. En maquinas compartidas el nombre es visible a otros usuarios via DevTools
- **Fix:** `useLastEmail` ahora solo expone `{ email }`. `forgetEmail()` limpia la key legacy `eventos:lastUserName`. `LoginForm` props `cached: { email }` (sin name). `login/page.tsx` y `verify/page.tsx` no leen cookie name. Si se necesita saludo personalizado, leer del backend tras login y mantener en memoria
- **Archivos:** `eventos-web/src/hooks/useLastEmail.ts`, `components/auth/LoginForm.tsx`, `app/[locale]/(auth)/login/page.tsx`, `app/[locale]/(auth)/verify/page.tsx`

### BUG-315: dangerouslySetInnerHTML con email del usuario en step sent (RESUELTO)
- **Severidad:** ALTA — En `LoginForm` step `sent` el mensaje "Te enviamos un link a {email}" se renderizaba via `dangerouslySetInnerHTML` con email interpolado. Aunque pasaba por `escapeHtml()` (4 caracteres: `&<>"`), la funcion era incompleta (faltaba `'`) y el patron es de alto riesgo XSS por defecto
- **Causa:** Necesidad de inyectar HTML `<strong>` alrededor del email + interpolar `{fromAddress}` (otra cadena con HTML). El refactor anterior no aprovecho el patron `t.rich()` de next-intl
- **Fix:** Reemplazado `dangerouslySetInnerHTML` por `t.rich("magicLinkSent.description", { email, emailTag: chunks => <strong>...</strong>, fromTag: ... })`. El email se interpola como ReactNode (escape automatico). Mensajes i18n actualizados (es/en/pt) con tags `<emailTag>{email}</emailTag>` en lugar de placeholders + HTML inline. Funcion `escapeHtml` eliminada del LoginForm
- **Archivos:** `eventos-web/src/components/auth/LoginForm.tsx`, `messages/{es,en,pt}.json`

### BUG-314: Password schema sin .max() — payload bomb potencial (RESUELTO)
- **Severidad:** CRITICA — `passwordLoginSchema` validaba password con `z.string().min(1)` sin limite superior. Cliente malicioso puede enviar password de varios MB sin que el frontend lo rechace, aumentando carga al backend (parsing JSON + bcrypt comparison sobre string gigante = DoS por amplificacion)
- **Fix:** `passwordLoginSchema.password` ahora `z.string().min(1).max(128, { message: "Contrasena demasiado larga" })`. 128 chars cubre passwords razonables de gestores enterprise (KeePass, 1Password) sin permitir abuso
- **Archivo:** `eventos-web/src/lib/authValidators.ts`

---

## 2026-05-02 — W.1 webapp F10.B + F10.C + auditoria login (8 bugs)

### BUG-313: AnimatedNumber rebote en countdown timer (RESUELTO)
- **Severidad:** MEDIA — En PreEventHome el countdown mostraba "dos numeros" parpadeando cada segundo. Visualmente parecia que habia 2 timers
- **Causa:** AnimatedNumber interpola valores intermedios con framer-motion `animate()`. Pasando de 47→46 atraviesa 46.5, donde `Math.round(46.5)` devuelve 47 mientras el target final es 46 → rebote visual entre los dos numeros
- **Fix:** Quitado AnimatedNumber del CountdownUnit. Vuelve a `String(value).padStart(2, "0")` directo. AnimatedNumber queda disponible solo para deltas grandes (live pulse 247→251, stats finales) donde la animacion suma valor
- **Archivo:** `eventos-web/src/components/app/home/PreEventHome.tsx`

### BUG-312: Next 16 scroll-behavior warning (RESUELTO)
- **Severidad:** BAJA — Console warning "Detected scroll-behavior: smooth on `<html>`. To disable smooth scrolling during route transitions, add data-scroll-behavior=smooth"
- **Fix:** Agregado `data-scroll-behavior="smooth"` al `<html>` en los 3 layouts (`[locale]/layout.tsx`, `not-found.tsx`, `error.tsx`)
- **Archivo:** `eventos-web/src/app/[locale]/layout.tsx`, `app/not-found.tsx`, `app/error.tsx`

### BUG-311: 422 backend errors.email[0] no llegaba al toast (RESUELTO)
- **Severidad:** ALTA — Login con credenciales incorrectas mostraba toast generico "Algo salio mal" en lugar del mensaje real "Credenciales incorrectas. X intentos restantes"
- **Causa:** Laravel `ValidationException::withMessages(['email' => [...]]` devuelve 422 con `{ message: "The given data was invalid", errors: { email: [...] } }`. El frontend leia solo `payload.message` (ingles generico) y la heuristica `looksLocalized` no lo detectaba como localizado → fallback a generic
- **Fix:** `getApiErrorMessage` ahora extrae `errors.{field}[0]` antes de fallback. Proxies (`login`, `magic-link`, `forgot-password`) propagan `errors` desde el ApiError.payload al cliente
- **Archivos:** `eventos-web/src/lib/apiErrors.ts`, `app/api/auth/{login,magic-link,forgot-password}/route.ts`, `components/auth/LoginForm.tsx`, `components/auth/ForgotPasswordSheet.tsx`

### BUG-310: Root layout no importa globals.css (RESUELTO)
- **Severidad:** ALTA — `app/not-found.tsx` y `app/error.tsx` (root pages) no recibian tokens CSS Lumina porque solo `[locale]/layout.tsx` importaba globals.css
- **Fix:** Importado `./globals.css` en `app/layout.tsx` (root pass-through). Los pages root-level ahora tienen tokens disponibles
- **Archivo:** `eventos-web/src/app/layout.tsx`

### BUG-309: fontFamily inline en componentes auth (RESUELTO)
- **Severidad:** MEDIA — `TabletRotateOverlay.tsx` y `LoginSlideshow.tsx` SlideOverlay h2 usaban `style={{ fontFamily: "var(--font-jakarta)" }}` inline en lugar de la utility `font-display`. Inconsistencia con el resto de componentes ya migrados
- **Fix:** Reemplazado por `className="font-display"` (que mapea a `--font-jakarta` via `@theme inline` de globals.css)
- **Archivos:** `eventos-web/src/components/auth/TabletRotateOverlay.tsx`, `components/auth/LoginSlideshow.tsx`

### BUG-308: Button onPointerDown rompe SSR para asChild (RESUELTO)
- **Severidad:** CRITICA — `not-found.tsx` (server component) renderizaba EmptyState con action.href → Button asChild → `<a>`. Yo habia agregado `onPointerDown={handlePointerDown}` automatico al Button base para haptic mobile. Server components NO pueden pasar event handlers (funciones) como props → Next 16 throw "Event handlers cannot be passed to Client Component props" → 500 en cualquier ruta no encontrada
- **Causa:** F10.B1 implemente haptic en el Button shadcn base, pero shadcn Button NO tiene "use client" (intencional para usar en server components). La funcion handler que agregue rompio el contrato server-friendly
- **Fix:** Revertido el haptic del Button base. Solo se conserva el feedback visual via CSS (`active:scale-[0.98]` + `translateY 1px`) que funciona en server o client sin diferencia. Haptic mobile (navigator.vibrate) queda como pendiente para wrapper opt-in `<HapticButton>` cliente puro
- **Archivo:** `eventos-web/src/components/ui/button.tsx`

### BUG-307: Auth gate middleware bloqueaba la pantalla 404 (RESUELTO)
- **Severidad:** ALTA — Cualquier ruta inexistente (`/es/asdfasdf`, etc) era interceptada por el middleware proxy.ts y redirigida a `/login` en lugar de mostrar la pantalla 404. El usuario nunca veia el `not-found.tsx` Lumina
- **Causa:** El middleware tenia auth gate que redirigia TODAS las rutas no publicas a `/login` si no habia cookie auth. Como middleware no sabe si una ruta existe, bloqueaba antes del rendering
- **Fix:** Movido auth gate al server layout `[locale]/(app)/layout.tsx` (que ya hacia `getCurrentUser()` + `redirect('/login')`). Middleware ahora solo hace routing i18n. Trade-off: requests no autenticadas a rutas protegidas ejecutan el server layout (1 fetch backend) antes de redirect — costo aceptable para la 404 correcta
- **Archivo:** `eventos-web/src/proxy.ts`

### BUG-306: not-found.tsx y error.tsx faltan html/body tags (RESUELTO)
- **Severidad:** ALTA — Next 16 reportaba "Missing `<html>` and `<body>` tags in the root layout" cuando se renderizaba la 404 o el error global. Pages root-level (`app/not-found.tsx`, `app/error.tsx`) NO heredan del `[locale]/layout.tsx` que tiene los tags
- **Fix:** Agregado `<html lang="es">` + `<body className="font-sans antialiased bg-[var(--bg-sunken)]">` propios a ambas pages. Sin i18n (texto fijo) porque puede llegar antes del routing de locale
- **Archivos:** `eventos-web/src/app/not-found.tsx`, `app/error.tsx`

---

## 2026-04-26 — Mission Control + Data Center cierre (19 bugs)

### BUG-305: Ruleta MC muestra resultado antes de fin de animacion (RESUELTO)
- **Severidad:** ALTA — En Mission Control, despues de POST /spin el panel mostraba el sector ganador y los puntos inmediatamente, mientras la animacion de la ruleta en el display seguia girando 5s mas
- **Causa:** showGameResult() llamado en el .then() del fetch sin esperar spin_duration_ms
- **Fix:** Disparar projectGameSpin() primero (broadcast socket inicia animacion), luego setManagedTimeout('spin-reveal-{id}', spinDuration) revela el resultado en MC sincronizado con el display
- **Archivo:** public/mission-control/app.js linea 2034

### BUG-304: Socket.IO status bar casi invisible (RESUELTO)
- **Severidad:** BAJA — Indicador "Socket.IO conectado" font-size 10px color t5 sin badge, no se notaba el estado
- **Fix:** Pill con borde+fondo card, dot 7px verde con glow + animacion pulse 2s, "Socket.IO" en var(--t) negrita
- **Archivo:** public/mission-control/styles.css linea 400

### BUG-303: Render sin error boundary (RESUELTO)
- **Severidad:** BAJA — innerHTML = renderX(data) sin null-check podia romper toda la SPA si data venia mal
- **Fix:** Helper safeRender(el, fn, fallback) captura excepciones y muestra mensaje fallback
- **Archivo:** public/mission-control/app.js cleanup section

### BUG-302: Z-index conflicts sin escala centralizada (RESUELTO)
- **Severidad:** BAJA — Modal podia quedar detras de stream cuando fullscreen
- **Fix:** Variables CSS --z-base/stream/drawer/overlay/modal/toast en :root
- **Archivo:** public/mission-control/styles.css linea 14

### BUG-301: ESC no cierra modales (RESUELTO)
- **Severidad:** BAJA — Modales (poll, game, confirm, ban) no respondian a tecla Escape
- **Fix:** Listener global keydown ESC que cierra confirmOverlay, banOverlay, .mc-poll-overlay.on, .mc-game-overlay.on
- **Archivo:** public/mission-control/app.js

### BUG-300: Substring tras HTML escape rompe entities (RESUELTO)
- **Severidad:** BAJA — Linea 550 LED Q&A: esc(q.body).substring(0, 60) cortaba HTML entities a la mitad (&lt; → &lt sin cerrar)
- **Fix:** Truncar primero, escapar despues: esc((q.body || '').substring(0, 60))
- **Archivo:** public/mission-control/app.js linea 550

### BUG-299: Progress bar attendance imprecisa (RESUELTO)
- **Severidad:** MEDIA — Bar usaba contador setInterval cada 1s (drift +/-2s en checks de 30s)
- **Fix:** attendanceEndAt = Date.now() + ttl*1000, calcular msLeft = endAt - now en cada tick
- **Archivo:** public/mission-control/app.js linea 1576

### BUG-298: Doble-click en game spin/draw/launch/trivia (RESUELTO)
- **Severidad:** MEDIA — Click rapido podia disparar 2 spins/draws/jackpot. Doble jackpot = doble premio
- **Fix:** Helper lockButton(key, ms) que rechaza segundas llamadas en ventana de tiempo. Aplicado a: spin (8s), draw (12s), launch (5s), trivia next-question (5s), trivia close-round (5s), trivia launch (5s)
- **Archivo:** public/mission-control/app.js varios callsites

### BUG-297: localStorage parse sin validar tipo (RESUELTO)
- **Severidad:** MEDIA — JSON.parse en try-catch vacio. Si user corrompe localStorage, codigo asume array y .map() falla con TypeError
- **Fix:** Array.isArray() check post-parse para timelineLog y mcTasks; fallback a []
- **Archivo:** public/mission-control/app.js lineas 822, 1144

### BUG-296: Socket no rejoinea al reconnect (RESUELTO)
- **Severidad:** ALTA — Tras desconexion+reconexion, admin "estaba en vivo" pero no recibia eventos nuevos (chat, Q&A, polls). Solo el primer connect emit join:event/session
- **Fix:** socketOn('reconnect', onSocketConnect) que re-emite joins. Tambien onSocketConnect ahora emite SIEMPRE en cualquier connect (no solo el primero)
- **Archivo:** public/mission-control/app.js linea 196

### BUG-295: Race conditions en modales sin lock (RESUELTO)
- **Severidad:** ALTA — Click rapido en "Crear encuesta" + cerrar 3x rapido podia generar overlays apilados o estado de botones inconsistente
- **Fix:** ESC global cierra overlays activos. lockButton(key) rechaza clicks dentro de ventana
- **Archivo:** public/mission-control/app.js cleanup section

### BUG-294: 50+ catches silenciosos en apiFetch (RESUELTO)
- **Severidad:** ALTA — Patron sistemico: .catch(function() {}) sin loguear ni notificar. Backend cae y admin no se entera
- **Fix:** Helper apiCatch(label) que console.error + toast. sed reemplazo masivo de .catch(function(){}) → .catch(apiCatch())
- **Archivo:** public/mission-control/app.js multiple callsites

### BUG-293: Memory leaks de timers en sesiones largas (RESUELTO)
- **Severidad:** ALTA — setInterval/setTimeout sin clearInterval global. CPU creep + memory bloat en sesiones >1h. attInterval, _jackpotTimer, _triviaTimer, liveTimerInterval, _tt acumulaban
- **Fix:** Helpers setManagedInterval/Timeout(key, fn, ms) con registro global. cleanupAll() en beforeunload limpia todo + disconnect socket
- **Archivo:** public/mission-control/app.js cleanup section

### BUG-292: Listeners socket duplicados al reconectar (CRITICO, RESUELTO)
- **Severidad:** CRITICA — Cada socket.on() se acumulaba sin limpieza. Al reconectar o reintentar, mensajes procesados 2x: Q&A aprobadas dobles, metricas infladas
- **Fix:** Wrapper socketOn(event, handler) que registra _socketHandlers[event] y hace socket.off(event, prevHandler) antes de socket.on. Reemplazo masivo en 21 callsites
- **Archivo:** public/mission-control/app.js linea 169

### BUG-291: Token MC expira a 12h sin refresh (CRITICO, RESUELTO)
- **Severidad:** CRITICA — En eventos de 8+h, despues de 12h todos los apiFetch fallaban con 401 silencioso. Admin con UI funcional pero ningun POST/GET surte efecto
- **Fix:** apiFetch detecta 401/419 y dispara handleTokenExpired() con debounce 10s que toast + reload de URL (Laravel /monitor/{id}?token=... emite nuevo token)
- **Archivo:** public/mission-control/app.js apiFetch

### BUG-290: Hero card delta % absurdo en Data Center (RESUELTO)
- **Severidad:** MEDIA — Chip mostraba "+5300%" cuando comparaba total historico del evento vs solo periodo previo (60-30d)
- **Causa:** Logica conceptual incorrecta. cmp.registered eran los nuevos del periodo previo; s.registered eran TODOS historicos
- **Fix:** Backend devuelve compare = { current: ventana 30d, previous: ventana 30-60d }. Frontend con MIN_BASE=5 (si previo<5 muestra "+N 30d" no %), PCT_CAP=150 (>150% muestra absoluto). Tooltip con valores crudos
- **Archivo:** app/Services/ExportService.php + public/data-center/assets/app.js

### BUG-289: Schedule::call con withoutOverlapping requiere name() antes (RESUELTO)
- **Severidad:** ALTA — Cron processor scheduled exports lanzaba LogicException al cargar console.php, rompiendo TODA la suite de tests. Detectado al correr php artisan test
- **Fix:** Reordenar: ->name('dc:scheduled-exports')->everyFiveMinutes()->withoutOverlapping()
- **Archivo:** routes/console.php linea 95

### BUG-288: ExportService HOUR() no portable a SQLite (RESUELTO)
- **Severidad:** ALTA — selectRaw('HOUR(created_at) as h, ...') falla en SQLite ("no such function: HOUR"). Test suite no podia validar Data Center stats
- **Causa:** SQLite usa strftime, no funciones MySQL
- **Fix:** Helper hourSql($col) que detecta DB::connection()->getDriverName() y emite "CAST(strftime('%H', col) AS INTEGER)" en SQLite o "HOUR(col)" en MySQL. Aplicado a 3 lugares: chat_by_hour, checkins_by_hour, net_by_hour
- **Archivo:** app/Services/ExportService.php lineas 122-129

### BUG-287: ExportService HAVING sin GROUP BY (RESUELTO)
- **Severidad:** ALTA — Query session_ratings con ->having('ratings_count', '>', 0) falla en SQLite ("HAVING clause on a non-aggregate query"). Detectado por test
- **Fix:** Cambiado ->having() por ->whereHas('ratings') que filtra sesiones con al menos 1 rating sin requerir GROUP BY
- **Archivo:** app/Services/ExportService.php

## 2026-04-25 — Data Center (8 bugs)

### BUG-286: CSV renombrado a .xlsx genera error en Excel (RESUELTO)
- **Severidad:** ALTA — BaseExportJob generaba CSV con fputcsv pero ponia extension .xlsx cuando el usuario elegia formato Excel
- **Sintoma:** Excel muestra error de formato corrupto al abrir el archivo
- **Fix:** Forzar siempre extension .csv. XLSX real requiere maatwebsite/excel (pendiente P2)

### BUG-285: R2 bucket vacio causa 500 en cada export (RESUELTO)
- **Severidad:** CRITICA — `shouldUploadToR2()` verificaba `config('filesystems.disks.r2.key')` pero en dev la key existe y el bucket esta vacio
- **Sintoma:** `The GetObject operation requires non-empty parameter: Bucket` en cada export
- **Fix:** Verificar `bucket` en vez de `key`, skip completo en environment testing

### BUG-284: readonly + SerializesModels causa 500 al dispatch (RESUELTO)
- **Severidad:** CRITICA — BaseExportJob usaba `public readonly int $eventId` en constructor. `SerializesModels` trait intenta reinicializar propiedades readonly al deserializar el job
- **Sintoma:** `Cannot initialize readonly property from scope ExportAttendeesMasterJob`
- **Fix:** Quitar `readonly` de las 4 propiedades del constructor en BaseExportJob y ExportMasterZipJob

### BUG-283: ExportService stats query — ChatMessage sin relacion session() (RESUELTO)
- **Severidad:** ALTA — `ExportService::computeStats()` usaba `ChatMessage::whereHas('session', ...)` pero ChatMessage no tiene relacion `session()`, tiene campo `room` (string) y relacion `sender()`
- **Sintoma:** 500 en endpoint `/api/v1/data-center/{event}/stats`
- **Fix:** Cambiado a `ChatMessage::where('event_id', $eventId)->count()`. Mismo fix para LiveGame

### BUG-282: N+1 queries en ExportGamesSummaryJob (RESUELTO)
- **Severidad:** MEDIA — Dentro del `cursor()->map()`, ejecutaba `LiveGameParticipant::where()->count()` y `->with()->get()` por cada juego
- **Sintoma:** Performance degradada, potencial timeout con muchos juegos
- **Fix:** Preload `participantCounts` y `triviaWinners` antes del cursor con queries agrupadas

### BUG-281: ExportAttendeesMasterJob — cursor() con HasMany no eager load (RESUELTO)
- **Severidad:** MEDIA — `cursor()` con `with('registrationFieldValues')` (HasMany) no hace eager loading. Cada attendee ejecuta query individual para sus field values
- **Sintoma:** N+1 queries silencioso, lento con muchos asistentes
- **Fix:** Cambiado de `cursor()` a `get()` para que el eager loading funcione correctamente

### BUG-280: ExportSpinResultsJob — NPE en sector null (RESUELTO)
- **Severidad:** MEDIA — `$sector['label']` cuando `$sector` es null (sector index invalido o config corrupta)
- **Sintoma:** Potential TypeError en PHP 7.x (PHP 8 permite null['key'] → null, pero no es defensivo)
- **Fix:** Default `$sector` a `[]` en vez de `null` cuando el index no se encuentra

### BUG-279: Selector de evento faltante en SPA (RESUELTO)
- **Severidad:** ALTA — SPA solo funcionaba si el URL tenia el event ID (`/data-center/1`). No habia forma de seleccionar evento desde la interfaz
- **Sintoma:** Mensaje "Selecciona un evento desde Filament" sin poder hacer nada
- **Fix:** Agregado endpoint `GET /api/v1/data-center/events`, dropdown en header de la SPA, preseleccion por URL

## 2026-04-24 — Concurso de Fotos + Golden Ticket (10 bugs)

### BUG-278: Archivos prize support no commiteados (RESUELTO)
- **Severidad:** ALTA — Announcement.php, Reward.php, bootstrap/app.php sin pushear de sesiones anteriores
- **Fix:** Commit separado con archivos pendientes

### BUG-277: action_url sin scheme eventos:// (RESUELTO)
- **Severidad:** ALTA — Announcements creados con `action_url: 'gamification/rewards'` sin scheme
- **Sintoma:** Android crashea: "No Activity found to handle Intent"
- **Fix:** Cambiar a `eventos://gamification/rewards` en todos los Announcement::create

### BUG-276: Announcement sin published_at (RESUELTO)
- **Severidad:** ALTA — Golden Tickets creaban announcements con `published_at: null`
- **Sintoma:** Announcements no aparecian en la app (endpoint filtra `whereNotNull('published_at')`)
- **Fix:** Agregar `'published_at' => now()` en los 4 Announcement::create de Filament

### BUG-275: LikesMilestoneService crash en fotos oficiales (RESUELTO)
- **Severidad:** CRITICA — `totalLikesForAttendee()` recibia null cuando `attendee_id` es null en fotos oficiales
- **Fix:** Guard `if ($photo->attendee_id !== null)` antes de llamar milestone service

### BUG-274: Self-like check crasheaba en fotos oficiales (RESUELTO)
- **Severidad:** MEDIA — Comparaba `null === $attendee->id` en fotos con attendee_id=null
- **Fix:** Guard `$photo->attendee_id !== null &&` antes de la comparacion

### BUG-273: Route /photos/contest despues de /{photoId} (RESUELTO)
- **Severidad:** MEDIA — Ruta literal despues de parametrizada, fragil ante cambios
- **Fix:** Mover GET /photos/contest antes de las rutas con {photoId}

### BUG-272: PhotoGrid viewer mostraba foto invertida (RESUELTO)
- **Severidad:** MEDIA — Sort por likes en MemoriasContent pero PhotoViewer usaba array original
- **Fix:** Mover sort al parent con useMemo, pasar mismo array sorted a grid y viewer

### BUG-271: Contest entry multiple por attendee (RESUELTO)
- **Severidad:** ALTA — Cada foto subida durante concurso se marcaba como contest entry
- **Fix:** Check `exists()` + `lockForUpdate()` — solo 1 entry por attendee

### BUG-270: ContestBanner crash "cannot read subtle" (RESUELTO)
- **Severidad:** CRITICA — `border.subtle` no existe en theme tokens, `border` es string plano
- **Fix:** Fallback `typeof border === 'string' ? border : border?.subtle`

### BUG-269: console.error en upload foto muestra red screen Expo (RESUELTO)
- **Severidad:** BAJA — `console.error` en catch mostraba error rojo en Expo debug ademas del toast
- **Fix:** Cambiar a `console.warn` solo en `__DEV__`

---

### BUG-268: Filament searchable selects no cargan opciones (RESUELTO)
- **Severidad**: ALTA
- **Fecha reportado**: 2026-04-24
- **Fecha resuelto**: 2026-04-26
- **Donde**: Filament v3.3.49 — TODOS los Select con `->searchable()` en forms y actions
- **Sintoma**: Escribes texto en el campo de busqueda y no retorna resultados. Afecta: Totems (salon), Golden Tickets (asistente), Patrocinadores, y cualquier Select searchable
- **Causa raiz**: `vendor/filament/forms/src/Components/Select.php:622-624` — cuando `Select::searchable()` se invoca sin `->preload()` y sin `getSearchResultsUsing` callback, el metodo `getSearchResults()` retorna `[]` vacio. El AJAX call al escribir vuelve sin resultados aunque las opciones existan
- **Fix aplicado**: Barrido a 26 archivos en `app/Filament/Resources/` y `Pages/` agregando `->preload()` a todos los `Forms\Components\Select::make()` y `Tables\Filters\SelectFilter::make()` que tenian `->searchable()` sin `->preload()`. 27 fixes totales (algunos archivos con multiples Selects). 769 tests backend pasan, 0 regresiones
- **Archivos afectados**: AnnouncementResource, AttendeeAdminResource (form + 2 filters), AttendeeAdminResource\Pages\ListAttendeeAdmins (3), AttendeeBanResource (filter), BannerResource, CustomPageResource, DocumentResource (2), EmailTemplateResource, EventFaqResource, EventOnboardingResource, EventResource\Pages\CreateEvent, EventRoomResource, EventSessionResource, GoldenTicketResource, HighlightResource, LivePollResource (2), ModuleResource, OnboardingSurveyOptionResource, OrganizationEmailSettingsResource, PostEventSurveyResource, RegistrationFieldResource (form + filter), RoomTotemResource, ScheduledNotificationResource, SessionTrackResource, SessionTypeResource, SpeakerResource, SponsorResource (event_id + owner_attendee_id)

---

## 2026-04-24 — Event Pulse Auditoria completa (30 bugs)

### BUG-267: Race condition sessionViewers undefined (RESUELTO)
- **Severidad:** CRITICA — sections.js accedia EP.sessionViewers antes de que socket.js lo inicializara
- **Fix:** Inicializar EP.sessionViewers={} y EP.sessionViewersList={} en data.js (carga primero)

### BUG-266: Memory leak leadsScrollTimer nunca limpiado (RESUELTO)
- **Severidad:** CRITICA — cada vez que se entraba a Leads se creaba un nuevo setInterval sin limpiar el anterior
- **Fix:** clearInterval(leadsScrollTimer) en showSection() al cambiar de seccion

### BUG-265: Null check updateDetailPanel sin guard (RESUELTO)
- **Severidad:** CRITICA — si rooms era null/undefined, el for loop crasheaba
- **Fix:** Guard `if (!rooms || !rooms.length) return` al inicio

### BUG-264: Null check speakers en selectSession (RESUELTO)
- **Severidad:** CRITICA — si speaker era null, acceder a spk.photo_url crasheaba
- **Fix:** Filter `speakers.filter(sp => sp && sp.name)` antes de iterar

### BUG-263: insertSocialPost sin validacion de data (RESUELTO)
- **Severidad:** CRITICA — si socket enviaba data sin body, el DOM insert fallaba
- **Fix:** Guard `if (!data || !data.body) return` al inicio de la funcion

### BUG-262: initials() crashea con nombre vacio o single char (RESUELTO)
- **Severidad:** ALTA — `name.split(' ')` con string vacio o solo espacios producida undefined[0]
- **Fix:** trim() + filter(p => p.length > 0) + guard !parts.length

### BUG-261: N+1 query en PulseController leads() (RESUELTO)
- **Severidad:** ALTA — query de Lead por cada sponsor dentro del map (N+1)
- **Fix:** 1 query con eager load + groupBy en PHP collection

### BUG-260: N+1 query en PulseController leaderboard() (RESUELTO)
- **Severidad:** ALTA — 2 queries por attendee (find + lastAction) dentro del map
- **Fix:** 3 queries batch: topRows, attendees whereIn, lastActions unique

### BUG-259: refreshBootstrap sin retry ni null check (RESUELTO)
- **Severidad:** ALTA — si el API fallaba post-reconnect, counters quedaban stale sin reintentar
- **Fix:** Guard `!data || !data.stats`, retry hasta 3 veces con backoff 5s

### BUG-258: Bootstrap response sin validacion (RESUELTO)
- **Severidad:** ALTA — si API devuelve respuesta incompleta, app.js crasheaba accediendo data.event
- **Fix:** `if (!data || !data.event || !data.stats) throw new Error()`

### BUG-257: API fetch sin timeout (RESUELTO)
- **Severidad:** ALTA — fetch() esperaba indefinidamente si el server no respondia
- **Fix:** AbortController con timeout de 8 segundos

### BUG-256: Charlas auto-select race condition (RESUELTO)
- **Severidad:** MEDIA — auto-select podia seleccionar sesion que ya no estaba live entre fetch y render
- **Fix:** Verificar `.live` en charlasData antes de llamar selectSession

### BUG-255: Session cleanup sin logging en socket server (RESUELTO)
- **Severidad:** MEDIA — errores de limpieza de viewers se silenciaban completamente
- **Fix:** console.warn en catch block del cleanup

### BUG-254: Token pulse sin validacion de longitud (RESUELTO)
- **Severidad:** MEDIA — tokens malformados `ep_x` pasaban al validatePulseToken
- **Fix:** Check `token.length >= 10` ademas de startsWith('ep_')

### BUG-253: Timeline hour edge case null (RESUELTO)
- **Severidad:** MEDIA — si checked_in_at producia hour null en SQLite, groupBy fallaba
- **Fix:** COALESCE en query + filter null keys

### BUG-252: Inline color detail-virt hardcoded (RESUELTO)
- **Severidad:** MEDIA — style="color:var(--blue)" inline en JS, no respetaba cascada CSS
- **Fix:** Clase CSS `.detail-aud-virt` con color: var(--blue)

### BUG-251: CSS orphan .agenda-time .min (RESUELTO)
- **Severidad:** BAJA — selector CSS sin elemento HTML correspondiente
- **Fix:** Eliminado

### BUG-250: CSS orphan m-lead y m-match sin builders JS (RESUELTO)
- **Severidad:** BAJA — estilos CSS completos para moment types sin implementar en JS
- **Fix:** Marcados con comentario "(CSS ready, JS builder pendiente)"

### BUG-249: Toggle CSS especificidad fragil (RESUELTO)
- **Severidad:** BAJA — .toggle-sun/.toggle-moon podian ser sobreescritos por selectores mas amplios
- **Fix:** Scope `.theme-toggle .toggle-*` para mayor especificidad

### BUG-248: Moments interval nunca limpiado (RESUELTO)
- **Severidad:** MEDIA — setInterval de 15s seguia corriendo aunque moment data se recargara
- **Fix:** Guardar en momentPlayInterval, clearInterval antes de crear nuevo

### BUG-247: Magic numbers en moments.js (RESUELTO)
- **Severidad:** BAJA — 5000, 15000, 3000, 20 hardcoded sin contexto
- **Fix:** Constantes MOMENT_DURATION, MOMENT_INTERVAL, MOMENT_FIRST_DELAY, MOMENT_POOL_MAX

### BUG-246: prefers-reduced-motion no respetado (RESUELTO)
- **Severidad:** BAJA — usuarios con accesibilidad reducida veian todas las animaciones
- **Fix:** Media query global que reduce duration a 0.01ms

### BUG-245: Charlas no diferencia sin sesiones vs todas finalizadas (RESUELTO)
- **Severidad:** BAJA — mismo mensaje generico para ambos casos
- **Fix:** "No hay sesiones programadas" vs "Todas las sesiones han finalizado"

### BUG-244: Bootstrap fail sin forma de reintentar (RESUELTO)
- **Severidad:** BAJA — usuario veia "Error cargando datos" sin opcion de retry
- **Fix:** Link "Reintentar" con location.reload()

### BUG-243: Typo "Ubicacion" sin tilde (RESUELTO)
- **Severidad:** BAJA — falta acento en HTML
- **Fix:** `Ubicaci&oacute;n`

### BUG-242: Dead HTML ep-bot-info nunca poblado (RESUELTO)
- **Severidad:** BAJA — div vacio en frame bottom sin JS que lo llene
- **Fix:** Eliminado HTML + CSS bot-left/bot-right

### BUG-241: last_action muestra slug interno (game_spin) en vez de label (RESUELTO)
- **Severidad:** MEDIA — leaderboard mostraba "game_spin" en vez de "Ruleta en vivo"
- **Fix:** PulseController usa PointsService::getConfig() para traducir action key a label

### BUG-240: Nombre evento duplicado en idle (RESUELTO)
- **Severidad:** BAJA — nombre aparecia arriba izquierda (brand) Y abajo izquierda (bot-left)
- **Fix:** Eliminado bot-left, nombre solo en brand-name + ambient center

### BUG-239: Dot verde doble en agenda charlas (RESUELTO)
- **Severidad:** BAJA — sesion live tenia agenda-dot verde + agenda-live-tag con otro dot
- **Fix:** Eliminado agenda-live-tag, solo queda agenda-dot

### BUG-238: Teal usado fuera de gamificacion (RESUELTO)
- **Severidad:** MEDIA — teal (#0D9488) se usaba en eyebrows, badges, timelines, networking. Solo deberia ser gamificacion
- **Fix:** Reemplazo sistematico: live→green, leads→platinum, networking→platinum, eyebrows→ink-50, bars→ink-50

### BUG-237: Pip status conexion siempre verde (RESUELTO)
- **Severidad:** MEDIA — pip del nav arrancaba verde por CSS default aunque socket no estuviera conectado
- **Fix:** Default rojo (offline), JS agrega .online (verde) o .reconnecting (amarillo). Fix especificidad nav.css

---

## 2026-04-24 — Event Pulse RT + Performance 10K (4 bugs)

### BUG-236: session:audience fan-out a 10K sockets (RESUELTO)
- **Severidad:** CRITICA — con 10K usuarios y 10 salones, broadcastAudience generaba ~200K msgs/sec al event room que nadie usaba
- **Causa:** broadcastAudience emitia session:audience a Rooms.event() (todos los usuarios) en vez de un room aislado
- **Fix:** Rooms.pulse(eventId) exclusivo para dashboards Pulse (1-2 sockets). MC sigue via Rooms.session(). App no recibe session:audience

### BUG-235: Sesiones fantasma "live" en Event Pulse (RESUELTO)
- **Severidad:** ALTA — sesiones que terminaron ayer seguian mostrando como "En vivo" en Pulse
- **Causa:** PulseController rooms() solo verificaba actual_end_at === null, sin considerar que publicEnd() ya habia pasado. Si el moderador nunca presiono "Terminar", la sesion quedaba live para siempre
- **Fix:** Agregar check `publicEnd()->isAfter(now())` en la condicion de live. Eliminar fallback a "most recent session"

### BUG-234: viewers[] sin limite en payload session:audience (RESUELTO)
- **Severidad:** MEDIA — con 1000 personas en un salon, el payload tenia 1000 objetos {id, name} (~40KB) enviados a todos
- **Causa:** broadcastAudience recolectaba TODOS los viewers sin limite
- **Fix:** Limitar a max 20 viewers. Pulse muestra max 8 burbujas + badge "+N"

### BUG-233: Counter m-on (En linea) solo se actualizaba cada 5 min (RESUELTO)
- **Severidad:** MEDIA — el counter "En linea" en Pulse solo cambiaba con el bootstrap refresh (5 min)
- **Causa:** socket.js no sumaba EP.sessionViewers al recibir session:audience. Solo el bootstrap cada 5min actualizaba m-on
- **Fix:** Calcular totalOnline sumando todos los EP.sessionViewers al recibir session:audience

---

## 2026-04-23 — Sesion Performance + Async Jobs (5 bugs)

### BUG-232: Redis del wildcard no limpia keys en tests (RESUELTO)
- **Severidad:** MEDIA — tests con cache stale daban falsos positivos
- **Causa:** Redis del con wildcard no limpia keys en tests
- **Fix:** Redis::flushdb()

### BUG-231: points_log sin unique constraint — duplicados posibles en retry (RESUELTO)
- **Severidad:** ALTA — retries podian crear entradas duplicadas de puntos
- **Causa:** points_log sin unique constraint
- **Fix:** Migration unique index + insertOrIgnore

### BUG-230: Trivia answer race condition — check + create sin proteccion (RESUELTO)
- **Severidad:** CRITICA — dos respuestas simultaneas podian ambas pasar el check
- **Causa:** check + create sin proteccion atomica
- **Fix:** firstOrCreate + wasRecentlyCreated

### BUG-229: Trivia closeRound 10K timeout — foreach 10K con awardPoints individual (RESUELTO)
- **Severidad:** CRITICA — closeRound con 10K participantes causaba timeout por foreach con awardPoints individual
- **Causa:** foreach 10K con awardPoints individual
- **Fix:** ProcessTriviaRewardsJob bulk async

### BUG-228: Spin 10K timeout — foreach 10K con 2 queries por participante (RESUELTO)
- **Severidad:** CRITICA — spin con 10K participantes causaba timeout por foreach con 2 queries por participante
- **Causa:** foreach 10K con 2 queries por participante
- **Fix:** ProcessSpinRewardsJob bulk async

---

## 2026-04-23 — Sesion Trivia Kahoot-style (10 bugs)

### BUG-227: loadTrivia llamada antes de definirse — ReferenceError (RESUELTO)
- **Severidad:** MEDIA — setActiveFeature llamaba loadTrivia() que se definia en IIFE posterior
- **Causa:** Orden de ejecucion: setActiveFeature se define antes que initTrivia IIFE
- **Fix:** Guard typeof loadTrivia === 'function'
- **Archivo:** mission-control/app.js

### BUG-226: renderGameTrivia usaba standbyTimer y displayContent inexistentes (RESUELTO)
- **Severidad:** CRITICA — display crasheaba al recibir proyeccion de trivia
- **Causa:** Variables copiadas mal: standbyTimer→_standbyTimer, displayContent→content
- **Fix:** Corregir nombres de variables
- **Archivo:** public/display/session.html

### BUG-225: initGames tenia handlers de trivia huerfanos — crash null addEventListener (RESUELTO)
- **Severidad:** CRITICA — gameTriviaNextBtn/gameTriviaCloseBtn/gameAddQuestion ya no existian en HTML pero los listeners seguian en JS, crash al cargar MC
- **Causa:** Migracion incompleta de trivia de Games a su propio tab
- **Fix:** Eliminar todos los handlers y elementos de trivia del IIFE de initGames
- **Archivo:** mission-control/app.js, mission-control/index.html

### BUG-224: Boton trivia en Games estaba disabled con titulo "Proximamente" (RESUELTO)
- **Severidad:** BAJA — boton de trivia en panel Games tenia disabled y title="Proximamente"
- **Causa:** Placeholder viejo nunca removido
- **Fix:** Removido — trivia ahora vive en su propio tab MC
- **Archivo:** mission-control/index.html

### BUG-223: Toast game:launched decia "Ruleta en curso" para trivia (RESUELTO)
- **Severidad:** BAJA — el label solo chequeaba jackpot, todo lo demas era "Ruleta"
- **Causa:** Ternario simple sin caso trivia
- **Fix:** Record con labels por tipo: spin/jackpot/trivia
- **Archivo:** hooks/useDataInvalidation.ts

### BUG-222: Countdown trivia no se detenia al responder (RESUELTO)
- **Severidad:** MEDIA — despues de responder una pregunta, el timer seguia contando en la app
- **Causa:** useEffect del countdown no observaba myAnswer
- **Fix:** Agregar useEffect separado que limpia el interval cuando myAnswer cambia
- **Archivo:** components/screens/TriviaPanel.tsx

### BUG-221: GameController errores usaban 'error' en vez de 'message' — MC no mostraba mensajes (RESUELTO)
- **Severidad:** MEDIA — apiFetch busca d.message pero backend enviaba d.error, toasts de error vacios
- **Causa:** Inconsistencia en formato de respuesta de error
- **Fix:** Reemplazar todos response()->json(['error' =>]) por ['message' =>]
- **Archivo:** GameController.php (26 ocurrencias)

### BUG-220: configPayload no devolvia feature flags individuales (RESUELTO)
- **Severidad:** ALTA — API devolvia interactive_mode pero no chat_enabled/qna_enabled/polls_enabled/trivia_enabled, frontend no podia derivar activePanel
- **Causa:** configPayload solo retornaba interactive_mode raw
- **Fix:** Derivar flags de interactive_mode en configPayload + derivar activePanel de interactive_mode directamente
- **Archivo:** SessionConfigController.php, useSessionConfig.ts

### BUG-219: effectiveMode mapping no incluia trivia — panel siempre 'none' (RESUELTO)
- **Severidad:** CRITICA — session-stream linea 302 tenia mapping manual hardcodeado que no incluia 'trivia', siempre caia en 'none' mostrando "Interaccion desactivada"
- **Causa:** Se usaba effectiveMode con ternarios en vez de activePanel del config
- **Fix:** Reemplazar mapping manual por activePanel directo de useSessionConfig
- **Archivo:** app/(app)/session-stream/[id].tsx

### BUG-218: publicShow recibia eventId como sessionId — config de sesion incorrecta (RESUELTO)
- **Severidad:** CRITICA — GET /events/{eventId}/sessions/{sessionId}/live-config siempre devolvia config de sesion 1 (eventId) en vez de la sesion real
- **Causa:** publicShow(int $sessionId) solo recibia 1 parametro, Laravel inyectaba el primer parametro de ruta (eventId)
- **Fix:** publicShow(int $eventId, int $sessionId) — recibir ambos parametros
- **Archivo:** SessionConfigController.php

---

## 2026-04-22 — Golden Ticket + Prize Fixes (5 bugs)

### BUG-217: Announcements privados visibles a todos los attendees del mismo rol (RESUELTO)
- **Severidad:** ALTA — announcements con target_attendee_id (ej: "Ganaste el sorteo") se mostraban a todos los attendees del mismo rol, no solo al target
- **Causa:** AnnouncementController filtraba por roles pero no por target_attendee_id
- **Fix:** Separar query: publicos (cacheados por rol, whereNull target_attendee_id) + privados (sin cache, where target_attendee_id = attendee actual)
- **Archivo:** app/Http/Controllers/Api/V1/AnnouncementController.php

### BUG-216: RewardController::myRedemptions() response crash expires_at null (RESUELTO)
- **Severidad:** CRITICA — listar redemptions con prizes (expires_at=null) crasheaba al mapear la respuesta
- **Causa:** `$r->expires_at->toIso8601String()` sin nullsafe
- **Fix:** `$r->expires_at?->toIso8601String()`
- **Archivo:** app/Http/Controllers/Api/V1/RewardController.php

### BUG-215: RewardController::redeem() response crash expires_at null (RESUELTO)
- **Severidad:** MEDIA — aunque redeem() solo crea redemptions con expires_at, la respuesta no usaba nullsafe
- **Causa:** `->toIso8601String()` en campo que podria ser null en futuras extensiones
- **Fix:** `$redemption->expires_at?->toIso8601String()`
- **Archivo:** app/Http/Controllers/Api/V1/RewardController.php

### BUG-214: RewardService::confirm() crash con expires_at null (RESUELTO)
- **Severidad:** CRITICA — confirmar un premio de jackpot (expires_at=null) crasheaba con "Call to a member function isPast() on null"
- **Causa:** abort_if no verificaba null antes de ->isPast()
- **Fix:** `abort_if($redemption->expires_at && $redemption->expires_at->isPast(), 410, ...)`
- **Archivo:** app/Services/RewardService.php

### BUG-213: RewardRedemption::isExpired() crash con expires_at null (RESUELTO)
- **Severidad:** CRITICA — premios tipo prize (jackpot) tienen expires_at=null, isExpired() llamaba ->isPast() en null
- **Causa:** Metodo no contemplaba que prize redemptions no expiran
- **Fix:** Nullsafe operator `$this->expires_at?->isPast()`
- **Archivo:** app/Models/RewardRedemption.php

---

## 2026-04-22 — Agenda end_datetime no se actualizaba en RT

### BUG-212: Hora de fin no se reflejaba en agenda despues de editar en Filament (RESUELTO)
- **Severidad:** ALTA — admin cambiaba hora de fin y la app seguia mostrando la vieja
- **Causa:** `publicEnd()` retorna `adjusted_end_at ?? end_datetime`. Si en algun momento se hizo un delay desde MC, `adjusted_end_at` se seteaba y tenia prioridad permanente sobre `end_datetime`. Al editar `end_datetime` en Filament, la API seguia devolviendo el `adjusted_end_at` viejo.
- **Fix:** `EventSessionObserver::updated()` ahora limpia `adjusted_end_at` via `updateQuietly()` cuando `end_datetime` cambia. Asi `publicEnd()` refleja el nuevo horario.

---

## 2026-04-22 — Auditoria sistema invalidacion RT (10 bugs)

### BUG-211: HTTP response del socket nunca se validaba (RESUELTO)
- **Severidad:** MEDIA — respuestas 4xx/5xx del socket se trataban como exito
- **Fix:** Validar `$response->successful()`, log warning si no es 2xx

### BUG-210: Throttle key se setea ANTES del broadcast — fallo silencioso sin retry (RESUELTO)
- **Severidad:** MEDIA — si HTTP falla, throttle bloquea reintento por 1 segundo
- **Causa:** `Cache::put(throttleKey)` se ejecutaba antes del HTTP POST. Si el POST fallaba, el throttle ya estaba activo.
- **Fix:** Throttle key solo se setea DESPUES de response exitoso. Si falla, retry posible inmediatamente.

### BUG-209: EventObserver cache key modules sin sufijo de rol — no limpia nada (RESUELTO)
- **Severidad:** MEDIA — `Cache::forget("event:{id}:modules")` no matcheaba keys reales `event:{id}:modules:attendee`
- **Fix:** Iterar roles (attendee/vendedor/guest) + vendedor_extra, igual que ModuleObserver

### BUG-208: Docblock InvalidationService dice 200ms, implementacion es 1s (RESUELTO)
- **Severidad:** MEDIA — documentacion inconsistente con implementacion
- **Fix:** Docblock corregido a "1 second"

### BUG-207: EventRoom sin observer — cambios de sala invisibles (RESUELTO)
- **Severidad:** ALTA — renombrar sala no se reflejaba en agenda de la app
- **Causa:** No existia observer para `EventRoom`
- **Fix:** `EventRoomObserver` creado: cache clear agenda + broadcast

### BUG-206: SessionTrack sin observer — cambios de track invisibles (RESUELTO)
- **Severidad:** ALTA — renombrar track o cambiar color no se reflejaba en la app
- **Causa:** No existia observer para `SessionTrack`
- **Fix:** `SessionTrackObserver` creado: cache clear agenda + broadcast

### BUG-205: Speaker y Announcement observers incompletos — uno limpia cache, otro broadcast (RESUELTO)
- **Severidad:** ALTA — doble observer con responsabilidades partidas
- **Causa:** `SpeakerObserver` solo broadcasteaba, `ContentObserver` solo limpiaba cache. Idem `AnnouncementObserver`
- **Fix:** Cada observer dedicado ahora hace cache clear + broadcast. ContentObserver como respaldo

### BUG-204: ModuleObserver limpia cache pero nunca broadcast — app no se entera (RESUELTO)
- **Severidad:** ALTA — cambios de modulos (habilitar/deshabilitar) no llegaban a la app en RT
- **Causa:** Observer solo llamaba `Cache::forget()` + push silencioso, nunca `InvalidationService::broadcast()`
- **Fix:** Agregar `InvalidationService::broadcast($eventId, 'modules')` en created/updated/deleted

### BUG-203: EventObserver isDirty() siempre false en saved() — polls post-evento nunca se activan (RESUELTO)
- **Severidad:** CRITICA — encuestas post-evento nunca se activaban automaticamente
- **Causa:** `isDirty()` en hook `saved()` siempre retorna false porque el modelo ya sincronizo atributos con la DB
- **Fix:** Cambiar a `wasChanged('status')` que funciona correctamente en `saved()`

### BUG-202: broadcastToAttendee llama endpoint inexistente en socket server (RESUELTO)
- **Severidad:** CRITICA — toda invalidacion per-attendee fallaba silenciosamente (404)
- **Causa:** `InvalidationService::broadcastToAttendee()` POSTeaba a `/internal/emit-to-user` que no existia en el socket server
- **Fix:** Endpoint agregado en socket server, usa `attendeeConnections` map para emitir a sockets especificos

---

## 2026-04-22 — Sorteo Ceremony: Rewrite + Bug Fixes (10 bugs)

### BUG-201: Photo src vacio no dispara onerror — imagen rota invisible (RESUELTO)
- **Severidad:** MEDIA — si `photo_url` es null/empty, `src=""` no dispara onerror en todos los browsers
- **Causa:** Celdas del reel usaban `src="${photo_url || ''}"` que es un src vacio
- **Fix:** Si no hay `photo_url`, usar beam directamente como src: `p.photo_url || beamFallback(p.name)`

### BUG-200: Winner beam avatar pixelado — usaba tamaño del reel (RESUELTO)
- **Severidad:** BAJA — beam en reveal se veia borroso
- **Causa:** `beamFallback` usaba `imgSize` del reel (~386px) pero la foto del winner es 320px max. En realidad el beam se pedia a tamaño reel cuando debia ser 320.
- **Fix:** `beamFallback` ahora acepta parametro `size`, winner usa `beamFallback(name, 320)`

### BUG-199: Participants vacio causa crash en ceremony (RESUELTO)
- **Severidad:** ALTA — si backend manda `participants:[]`, `others[random]` da undefined
- **Causa:** No habia fallback cuando la lista de participantes venia vacia
- **Fix:** `if (!others.length) others = participants.length ? participants : [winner]`

### BUG-198: Glow ring desalineado del reel — fotos fuera del borde dorado (RESUELTO)
- **Severidad:** MEDIA — borde dorado no contenia las fotos
- **Causa:** Glow era un `div` separado con tamaño fijo en CSS que no matcheaba el reel responsivo (`min(45vh,420px)`)
- **Fix:** Eliminar div glow separado, usar `outline` directo en el reel con `outline-offset:8px`. Siempre rodea el reel exacto.

### BUG-197: Premio y titulo montados encima del reel (RESUELTO)
- **Severidad:** MEDIA — texto se superponia con las fotos del reel
- **Causa:** `.d-ceremony-info` estaba en el flow normal con `margin-bottom` insuficiente. Al crecer el reel con `min(45vh,420px)`, el espacio no alcanzaba.
- **Fix:** Mover info a `position:absolute; top:0; right:0` — queda al nivel del header, nunca se monta

### BUG-196: Fase participacion mostraba "0 PARTICIPANTES" sin sentido (RESUELTO)
- **Severidad:** MEDIA — confuso para el publico
- **Causa:** El counter de participacion y countdown se mostraban en el display publico. El pool se calcula automaticamente, no hay nada que "unirse".
- **Fix:** Reemplazar counter/countdown por "Preparate..." pulsante. El MC ya tiene el eligible count para el moderador.

### BUG-195: Nombre duplicado en reel — overlay + label debajo (RESUELTO)
- **Severidad:** BAJA — nombre aparecia dentro de la foto Y debajo del reel
- **Causa:** Se renderizaba `.cell-name` overlay dentro de cada celda + `#reelName` debajo del reel simultaneamente
- **Fix:** Quitar overlay de las celdas, dejar solo el label debajo

### BUG-194: Winner name no aparecia en reveal — letters stagger vacias (RESUELTO)
- **Severidad:** ALTA — nombre del ganador no se mostraba
- **Causa:** `gsap.to('.winner-letter', ...)` se agendaba al construir el timeline, pero las letras se creaban dinamicamente en un `call()` posterior. GSAP no encontraba elementos.
- **Fix:** Mover stagger dentro de un `call()` para que ejecute cuando las letras ya existan en DOM

### BUG-193: Confetti no aparecia en winner reveal (RESUELTO)
- **Severidad:** MEDIA — confetti invisible despues del reveal
- **Causa:** CSS `.d-confetti-piece` tenia `animation:confettiFall linear forwards` que competia con GSAP. La CSS animation forzaba opacity a 0, pisando los valores de GSAP.
- **Fix:** Quitar CSS keyframes de confetti, dejar solo GSAP para animar las piezas

### BUG-192: Slot machine 3 reels — feo, demasiado espacio negro, casino feel (RESUELTO)
- **Severidad:** MEDIA — UX/diseño inaceptable para pantalla de evento premium
- **Causa:** Implementacion original usaba 3 columnas de fotos girando (slot machine literal). Se veian 3 puntos focales compitiendo, mucho negro entre reels, aspecto de casino.
- **Fix:** Rewrite completo → "Photo Cascade Ceremony": strip vertical unico con GSAP `power4.out`, 20 celdas max, shockwave, winner reveal con nombre grande (80px) + confetti amber

---

## 2026-04-22 — Live Moments Phase 2: Pool RT + Spin/Jackpot Fixes (15 bugs)

### BUG-191: Standby timer no se limpia en disconnect (RESUELTO)
- **Severidad:** MEDIA — timer seguia corriendo tras desconexion del socket
- **Causa:** clearTimeout + clearInterval no se llamaban al desconectar
- **Fix:** clearTimeout + clearInterval en disconnect handler

### BUG-190: Idle message stuck on error (RESUELTO)
- **Severidad:** MEDIA — mensaje idle quedaba visible despues de un error
- **Causa:** Estado no se restauraba a idle tras un error
- **Fix:** Restore idle state en error handler

### BUG-189: < 2 sectores = pantalla blanca (RESUELTO)
- **Severidad:** ALTA — crear spin con menos de 2 sectores dejaba pantalla en blanco
- **Causa:** spin-wheel lib requiere minimo 2 sectores
- **Fix:** Validation message antes de lanzar

### BUG-188: Spin-wheel module race condition (RESUELTO)
- **Severidad:** MEDIA — modulo spin-wheel fallaba intermitentemente al montar
- **Causa:** Timing issue en inicializacion del canvas
- **Fix:** retry con setTimeout

### BUG-187: CSV formula injection (RESUELTO)
- **Severidad:** ALTA (seguridad) — campos con = + - @ podian ejecutar formulas en Excel
- **Causa:** Export CSV sin sanitizacion de prefijos de formula
- **Fix:** Sanitize prefixes (= + - @) en export

### BUG-186: Jackpot srandmember null → attendee_id=0 (RESUELTO)
- **Severidad:** ALTA — si Redis SRANDMEMBER retornaba null, se asignaba attendee_id=0
- **Causa:** No se verificaba null del resultado de srandmember
- **Fix:** Null check antes de asignar winner

### BUG-185: mt_rand crash con sectors vacio (RESUELTO)
- **Severidad:** CRITICA — mt_rand(0, -1) crasheaba PHP
- **Causa:** sectors array vacio pasaba count()-1 como max a mt_rand
- **Fix:** Early return si sectors vacio

### BUG-184: Race condition double draw — atomic WHERE status=active (RESUELTO)
- **Severidad:** ALTA — dos draws simultaneos podian ejecutarse
- **Causa:** Sin proteccion atomica en cambio de estado
- **Fix:** atomic WHERE status=active en UPDATE

### BUG-183: Race condition double spin — atomic WHERE status=active (RESUELTO)
- **Severidad:** ALTA — dos spins simultaneos podian ejecutarse
- **Causa:** Sin proteccion atomica en cambio de estado
- **Fix:** atomic WHERE status=active en UPDATE

### BUG-182: Race condition double launch — atomic WHERE status=draft (RESUELTO)
- **Severidad:** ALTA — dos launches simultaneos podian crear juego duplicado
- **Causa:** Sin proteccion atomica en cambio de estado
- **Fix:** atomic WHERE status=draft en UPDATE

### BUG-181: Gamificacion invisible — game_spin already in config (RESUELTO)
- **Severidad:** MEDIA — accion de gamificacion no aparecia
- **Causa:** game_spin ya estaba registrado en config
- **Fix:** Verificar duplicados antes de agregar

### BUG-180: Rueda cuadrada (RESUELTO)
- **Severidad:** MEDIA — la rueda se renderizaba como cuadrado
- **Causa:** overflow hidden sin border-radius
- **Fix:** border-radius 50% + overflow hidden

### BUG-179: Pool virtual stale — Redis zombies (RESUELTO)
- **Severidad:** ALTA — participantes desconectados seguian en el pool
- **Causa:** Socket RT no limpiaba pool al desconectar
- **Fix:** Limpiar pool en disconnect handler del socket

### BUG-178: Sectores sin color/weight → rueda gris (RESUELTO)
- **Severidad:** MEDIA — sectores sin configurar se renderizaban grises
- **Causa:** Backend no asignaba defaults de color y weight
- **Fix:** Backend defaults para color y weight en sectores

### BUG-177: Angulo visual no corresponde al sector (RESUELTO)
- **Severidad:** ALTA — la rueda apuntaba a un sector diferente al ganador
- **Causa:** Bug en libreria spin-wheel con calculo de angulos
- **Fix:** Fix en spin-wheel lib para angulos correctos

---

## 2026-04-21 — Kiosk + Staff Check-in (13 bugs)

### BUG-176: HMAC incompatible en StaffCheckinController.resolveAttendeeFromQr (RESUELTO)
- **Severidad:** CRITICA — assign staff siempre fallaba con "QR no valido" para tokens dinamicos
- **Causa:** Implementacion propia usaba formato `{id}.{window}` con sig 12 chars y ventana 30s, pero CheckinService genera `{id}|{event_id}|{window}` con sig 32 chars y ventana 60s
- **Fix:** Delegar a `CheckinService::validateDynamicToken()` como fuente unica de verdad
- **Archivo:** StaffCheckinController.php (resolveAttendeeFromQr)

### BUG-175: assign-staff doble submit al seleccionar room (RESUELTO)
- **Severidad:** MEDIA — tap rapido en room picker podia enviar dos assigns del mismo QR al mismo room
- **Causa:** `handleRoomSelect` no verificaba si ya estaba en estado `assigning`
- **Fix:** Guard `if (scanState.status === 'assigning') return` al inicio
- **Archivo:** app/(app)/assign-staff.tsx

### BUG-174: auto-flush useEffect con stale closure de flush (RESUELTO)
- **Severidad:** MEDIA — el effect usaba `flush` directo como dependencia, que cambiaba en cada render y causaba re-runs innecesarios
- **Causa:** `flush` es un `useCallback` pero sus dependencias cambian, creando nueva referencia cada vez
- **Fix:** `flushRef.current` pattern para evitar dependencia directa en el effect
- **Archivo:** app/(app)/staff-checkin.tsx

### BUG-173: Cola offline descartaba scans fallidos en flush (RESUELTO)
- **Severidad:** ALTA — si scanBatch retornaba errores parciales (ej. NOT_ASSIGNED), flush borraba toda la cola incluyendo scans que podrian reintentarse
- **Causa:** `saveQueue([])` incondicional despues de scanBatch, sin distinguir entre errores permanentes (DEBOUNCE, QR_INVALID) y transitorios
- **Fix:** Solo descartar scans con errores permanentes, re-encolar el resto
- **Archivo:** hooks/useOfflineScanQueue.ts

### BUG-172: acceptAssignment race condition — dos accepts concurrentes (RESUELTO)
- **Severidad:** ALTA — dos requests simultaneos podian ambos leer accepted_at=NULL y ejecutar el update
- **Causa:** Query sin lock, permitia lecturas concurrentes del mismo row
- **Fix:** `lockForUpdate()` en la query de assignment
- **Archivo:** StaffCheckinController.php (acceptAssignment)

### BUG-171: reassign no validaba from_room del mismo evento (RESUELTO)
- **Severidad:** ALTA — admin de evento A podria desactivar assignment de evento B
- **Causa:** `reassign` solo validaba `to_room_id` con `event_id`, pero `from_room_id` se usaba sin verificar pertenencia al evento
- **Fix:** Agregar `firstOrFail()` con `event_id` para from_room antes de desactivar
- **Archivo:** StaffCheckinController.php (reassign)

### BUG-170: RoomAssignmentModal doble tap en aceptar/rechazar (RESUELTO)
- **Severidad:** ALTA — dos taps rapidos enviaban dos API calls, podian activar role dos veces
- **Causa:** `handleAccept` no verificaba `loading` state antes de ejecutar. Entre el tap y el setState async, el segundo tap pasaba el guard.
- **Fix:** Agregar `if (!pending || loading) return` en accept y reject
- **Archivo:** components/ui/RoomAssignmentModal.tsx

### BUG-169: StaffHappeningNow y staff-checkin no respetan tema Lux (RESUELTO)
- **Severidad:** MEDIA — colores hardcoded Noir, BottomSheet invisible en tema Lux
- **Causa:** Componentes creados con tokens Noir estaticos en vez de useTheme()
- **Fix:** Migrado a useTheme() tokens duales, BottomSheet textos/botones theme-aware, boton "Escanear otro" ink #1A1A1A en Lux
- **Archivo:** StaffHappeningNow.tsx, staff-checkin.tsx

### BUG-168: Staff check-in layout aislado en vez de modulo (RESUELTO)
- **Severidad:** MEDIA — staff perdia acceso a agenda, networking, etc.
- **Causa:** Implementacion inicial redirigia a Stack separado sin tabs. El usuario esperaba la misma experiencia que el vendedor (app completa + modulo extra).
- **Fix:** Revertir redirect, agregar como modulo en ModuleMenu y StaffHappeningNow card en home
- **Archivo:** _layout.tsx, ModuleMenu.tsx, index.tsx

### BUG-167: roomCheckinApi usaba api como funcion callable (RESUELTO)
- **Severidad:** ALTA — staff-checkin mostraba "Sin salones asignados" siempre
- **Causa:** `api` es un objeto con `.get()/.post()`, no una funcion. `api<T>(path)` fallaba silenciosamente.
- **Fix:** Cambiar a `api.get<T>(path)` y `api.post<T>(path, body)`
- **Archivo:** lib/roomCheckinApi.ts

### BUG-166: Kiosko socket WebSocket error en consola (RESUELTO)
- **Severidad:** BAJA — error en consola, no afecta funcionalidad
- **Causa:** Kiosko enviaba totem_token al socket server que espera Bearer Sanctum token. Socket rechazaba con 401.
- **Fix:** Eliminar conexion socket del kiosko. Ping HTTP cada 10s es suficiente para actualizaciones.
- **Archivo:** useRoomTotem.ts (socket removed)

### BUG-165: Manifest endpoint crash — undefined variable $now (RESUELTO)
- **Severidad:** ALTA — manifest seguia con 500 despues del fix de BUG-164
- **Causa:** Variable `$now` fue eliminada en el refactor pero seguia usandose en `generated_at`
- **Fix:** Cambiar `$now->toISOString()` a `now()->toISOString()`
- **Archivo:** RoomCheckinController.php (manifest method)

### BUG-164: Manifest endpoint crash — is_banned column not found (RESUELTO)
- **Severidad:** ALTA — manifest retornaba 500, kiosko no cargaba cache de nombres
- **Causa:** Query usaba `get(['id','user_id','checked_in_at','is_banned'])` pero attendees no tiene columna `is_banned`, el ban esta en tabla separada `attendee_bans`
- **Fix:** Remover `is_banned` del select, query separada a `AttendeeBan` con flip() para lookup rapido
- **Archivo:** RoomCheckinController.php (manifest method)

---

## 2026-04-21 — Session Lifecycle + Mission Control + Agenda RT (9 bugs)

### BUG-163: Kiosk ping ignora adjusted_end_at — muestra hora original tras delay (RESUELTO)
- **Severidad:** ALTA — kiosk mostraba hora de fin sin reflejar el retraso
- **Consecuencia:** Moderador retrasa sesion 15 min desde MC, pero el totem en la puerta del salon sigue mostrando la hora original. Asistentes que miran la pantalla creen que la sesion termina antes. Progress bar tambien incorrecta.
- **Causa:** `RoomCheckinController::ping()` usaba `$s->actual_end_at ?? $s->end_datetime` ignorando `adjusted_end_at` (el campo que guarda el delay)
- **Fix:** Cambiar a `$s->actual_end_at ?? $s->publicEnd()` — `publicEnd()` ya prioriza adjusted sobre original
- **Archivo:** RoomCheckinController.php:95

### BUG-162: Push de delay incluia asistentes de sesiones pasadas (RESUELTO)
- **Severidad:** BAJA — notificacion innecesaria a gente que favorito sesiones de hace meses
- **Consecuencia:** Si un salon tuvo un evento hace 6 meses y se reusa, los asistentes del evento viejo que favoritaron sesiones en ese salon recibian push "Agenda retrasada X min" sin contexto.
- **Causa:** Query de favoritos no filtraba por fecha, incluia todas las sesiones del salon sin importar cuando fueron
- **Fix:** Filtro `where('start_datetime', '>=', now()->subHours(2))` para solo incluir sesiones recientes
- **Archivo:** SessionConfigController.php:382

### BUG-161: MC segundo moderador no recibia timer ni stop al cambiar estado (RESUELTO)
- **Severidad:** MEDIA — si dos moderadores abrian MC, el segundo no veia el cronometro correcto
- **Consecuencia:** Moderador 1 inicia sesion. Moderador 2 recibe evento socket `session:started` pero no iniciaba el timer — mostraba "En vivo" sin cronometro. Al finalizar, timer del moderador 2 seguia corriendo.
- **Causa:** Socket listeners para session:started/ended/cancelled solo cambiaban `sessionState` y llamaban `updateControlUI()`, sin llamar `startLiveTimer()`/`stopLiveTimer()` ni usar `actual_start_at` del payload
- **Fix:** Iniciar timer con `actual_start_at` del server al recibir started, detener en ended/cancelled
- **Archivo:** public/mission-control/app.js:1607-1609

### BUG-160: Agenda app no actualizaba horarios en tiempo real (RESUELTO)
- **Severidad:** ALTA — moderador cambiaba horario, asistentes veian hora vieja hasta reabrir app
- **Consecuencia:** Titulo, descripcion y otros campos SI se actualizaban RT. Pero start/end NO. El asistente veia "10:00 AM" cuando la sesion ya estaba movida a "10:30 AM". Llegaba tarde o se confundia.
- **Causa:** 3 problemas simultaneos:
  1. MMKV disk cache (`initialData`) restauraba datos stale al re-montar componentes
  2. FlashList `extraData` no incluia la data de sesiones, no forzaba re-render al cambiar horarios
  3. `ENTITY_KEYS['agenda']` solo invalidaba `['agenda']`, no `['mi-agenda']` — favoritos nunca se refrescaban
- **Fix:** (1) `deleteCached()` del MMKV al recibir invalidation socket, (2) agregar `sessionsForDay` a extraData, (3) agregar `'mi-agenda'` a ENTITY_KEYS
- **Archivos:** useDataInvalidation.ts, AgendaScreen.tsx

### BUG-159: Cancelar sesion retrasada no revertia el delay en la siguiente (RESUELTO)
- **Severidad:** ALTA — la siguiente sesion quedaba desplazada sin razon
- **Consecuencia:** Si sesion A tenia +15 min de delay (empujando sesion B), y luego A se cancelaba, sesion B seguia desplazada +15 min innecesariamente. En un dia con 8 sesiones, esto podia acumular 30+ min de gap fantasma.
- **Causa:** `cancel()` solo seteaba `cancelled_at` sin verificar si habia un delay activo que revertir
- **Fix:** Si la sesion cancelada tenia `adjusted_end_at`, calcular el delay y revertirlo en la siguiente sesion
- **Archivo:** SessionConfigController.php:242-249
- **Test nuevo:** `test_cancel_reverts_delay_on_next_session`

### BUG-158: start() permitia iniciar sesion finalizada o cancelada (RESUELTO)
- **Severidad:** ALTA — moderador podia "reiniciar" una sesion terminada, corrompiendo datos de asistencia
- **Consecuencia:** Si moderador clickeaba "Iniciar" en una sesion ya finalizada (por error de UI o segundo MC), se sobreescribia `actual_start_at`. Todas las metricas de asistencia (duracion, check-in/out) quedaban invalidas.
- **Causa:** Solo se validaba `actual_start_at` (ya iniciada), sin verificar `actual_end_at` ni `cancelled_at`
- **Fix:** Guards para ALREADY_ENDED y CANCELLED antes del check de ALREADY_STARTED
- **Archivo:** SessionConfigController.php:148
- **Tests nuevos:** `test_start_rejects_already_ended`, `test_start_rejects_cancelled`

### BUG-157: Delay podia mover sesiones ya iniciadas (RESUELTO)
- **Severidad:** ALTA — sesion en vivo se movia en la agenda, confundiendo asistentes
- **Consecuencia:** Si la sesion B ya estaba en vivo y el moderador retrasaba sesion A, sesion B se movia en la agenda publica. Asistentes en B veian "Sesion en vivo" con horario futuro, inconsistencia critica.
- **Causa:** `adjustNextSession()` buscaba la siguiente sesion por `start_datetime` sin excluir las que ya tienen `actual_start_at`
- **Fix:** Agregar `->whereNull('actual_start_at')` al query
- **Archivo:** SessionConfigController.php:334
- **Test nuevo:** `test_delay_does_not_move_already_started_next`

### BUG-156: Archivo .ics de calendario ignora delays — hora original (RESUELTO)
- **Severidad:** ALTA — usuario descarga sesion al calendario y la hora no refleja el retraso
- **Consecuencia:** Si una sesion fue retrasada 30 min, el .ics seguia mostrando la hora original. El asistente llegaba 30 min antes al salon vacio, o perdia el inicio real.
- **Causa:** Endpoint calendar usaba `$session->end_datetime` (hora original) en vez de `$session->publicEnd()` (hora ajustada con delay)
- **Fix:** Cambiar a `publicEnd()` que prioriza `adjusted_end_at` sobre `end_datetime`
- **Archivo:** AgendaController.php:63

### BUG-155: Carbon mutation corrompe duracion de siguiente sesion al retrasar (RESUELTO)
- **Severidad:** CRITICA — la siguiente sesion en el salon quedaba con duracion 0 minutos
- **Consecuencia:** Si Salon A tiene sesion de 60 min y se retrasa 10 min, la siguiente sesion pasaba de 60 min a 0 min de duracion (start y end iguales). En la agenda publica la sesion aparecia como instantanea, imposible de entrar al stream.
- **Causa:** `addMinutes()` de Carbon MUTA el objeto en lugar de crear uno nuevo. PHP evalua los valores del array secuencialmente: el primer valor muta el Carbon, el segundo usa el ya-mutado. Ambos campos (`start_datetime`, `end_datetime`) terminan apuntando al mismo valor.
- **Fix:** `->copy()->addMinutes()` para crear instancias independientes
- **Archivo:** SessionConfigController.php:350-354 (adjustNextSession)
- **Test nuevo:** `test_delay_preserves_next_session_duration`

---

## 2026-04-20 — Mission Control: Config Streaming + About Fix (7 bugs)

### BUG-154: Session stats crash — null component en infolist schema (RESUELTO)
- **Severidad:** ALTA — pagina /admin/event-sessions/{id}/stats crasheaba completamente
- **Causa:** Secciones condicionales (top question, polls) retornaban `null` en el array del schema cuando no habia datos. Filament no acepta null como componente.
- **Fix:** Spread operator `...($condition ? [Section] : [])` en vez de ternario con null
- **Archivo:** ViewSessionStats.php
- **Commit:** e9eee0a

### BUG-153: MC tareas texto poco legible — color gris medio (RESUELTO)
- **Severidad:** BAJA — texto de tareas usaba var(--t2) gris, poca legibilidad
- **Fix:** Cambiado a var(--t) blanco principal
- **Archivo:** styles.css

### BUG-152: MC about section margenes vacios sin speakers/descripcion (RESUELTO)
- **Severidad:** BAJA — divs vacios dejaban margin-bottom:10px sin contenido visible
- **Causa:** aboutSpeaker y aboutDesc se renderizaban como elementos vacios
- **Fix:** display:none cuando no hay contenido
- **Archivo:** app.js

### BUG-151: MC stream_iframe acepta HTML arbitrario — vector XSS (RESUELTO)
- **Severidad:** ALTA — admin podia inyectar scripts via campo stream_iframe
- **Causa:** Validacion solo era nullable|string|max:2000, sin sanitizacion
- **Fix:** strip_tags() solo permite tag iframe
- **Archivo:** SessionConfigController.php

### BUG-150: MC apiFetch crasheaba con respuestas error no-JSON (RESUELTO)
- **Severidad:** MEDIA — si el server devuelve HTML en error 500, r.json() lanzaba uncaught exception
- **Causa:** apiFetch asumia que todas las respuestas error eran JSON
- **Fix:** Verificar content-type antes de parsear JSON en errores
- **Archivo:** app.js

### BUG-149: MC custom_url en modal config causaba error 422 (RESUELTO)
- **Severidad:** ALTA — al guardar config streaming, custom_url pasaba por validacion de dominio whitelist y fallaba
- **Causa:** El modal enviaba custom_url que se valida con validateEmbedDomain. Campo duplicado con tab Custom
- **Fix:** Eliminado custom_url del modal config (ya se gestiona en tab Custom)
- **Archivo:** app.js, index.html

### BUG-148: MC boton config streaming invisible en desktop (RESUELTO)
- **Severidad:** MEDIA — boton usaba clase mc-drawer-btn que tiene display:none en desktop
- **Causa:** Clase compartida con el boton hamburguesa del sidebar, oculto hasta tablet
- **Fix:** Nueva clase mc-header-btn con display:flex siempre visible
- **Archivo:** index.html, styles.css

---

## 2026-04-20 — Sesion Quick Wins + Stand Stats + QA (7 bugs)

### BUG-147: Tests BanTest fallaban por activated_at null (RESUELTO)
- **Severidad:** BAJA — solo tests, no produccion
- **Causa:** BanController ahora requiere `activated_at` para enviar push, pero el test creaba users sin ese campo
- **Fix:** Agregar `['activated_at' => now()]` al factory del target user
- **Archivo:** `tests/Feature/Admin/BanTest.php:30`
- **Commit:** 53a535c

### BUG-146: handleRefresh sin error handling — UI congelada si refetch falla (RESUELTO)
- **Severidad:** MEDIA — pull-to-refresh se queda girando infinitamente si hay error de red
- **Causa:** `await refetch()` sin try/catch, `setRefreshing(false)` nunca se ejecutaba en caso de error
- **Fix:** Wrap en `try { await refetch(); } finally { setRefreshing(false); }`
- **Archivos:** `stand-stats.tsx:137`, `stand-contacts.tsx:146`
- **Commit:** ed1f83a

### BUG-145: resolveAvatarUrl llamada con firma incorrecta en stand-contacts (RESUELTO)
- **Severidad:** MEDIA — fotos de solicitudes de contacto se veian en blanco
- **Causa:** `resolveAvatarUrl(att.name)` pasaba nombre como primer argumento (photoUrl), funcion interpretaba el nombre como URL y `fixStorageUrl("Laura Martinez")` retornaba URL invalida
- **Fix:** Cambiar a `resolveAvatarUrl(att.photo_url, att.name)`
- **Archivo:** `app/(app)/stand-contacts.tsx:52`
- **Commit:** ed1f83a

### BUG-144: Polling innecesario en encuestas, gamification y passport (RESUELTO)
- **Severidad:** MEDIA — 6,000 req/min con 500 usuarios sin necesidad
- **Causa:** `refetchInterval` de 15-30s en 3 hooks aunque encuestas ya tenia socket y gamification/passport no necesitaban polling
- **Fix:** Eliminado refetchInterval, agregado socket invalidation targeted via `broadcastToAttendee()`. Leaderboard usa `staleTime: 60s` + `refetchOnWindowFocus`
- **Archivos:** `encuestas.tsx:37`, `useGamification.ts:58,71`, `usePassport.ts:32`, `useDataInvalidation.ts:14`, `PointsService.php:104`, `LeadController.php:159`
- **Commit:** 53a535c + ed1f83a

### BUG-143: Session detail UI inconsistente — colores hardcodeados (RESUELTO)
- **Severidad:** MEDIA — pantalla se veia diferente al resto en dark/light mode
- **Causa:** Card de hora/ubicacion usaba `rgba(255,255,255,0.03)`, botones usaban `#FFF0F3`, `#F0F2F5`, fotos con `#151515` hardcoded en vez de tokens del theme
- **Fix:** Reemplazado por GlassCard, GlassButton, `surface.backgroundElevated`, `surface.low`
- **Archivo:** `app/(app)/session/[id].tsx`
- **Commit:** ed1f83a

### BUG-142: Push ban llega aunque usuario no inicio sesion / esta en onboarding (RESUELTO)
- **Severidad:** ALTA — notificacion de ban en onboarding, UX confuso
- **Causa:** 3 problemas: (1) push token se registraba durante onboarding antes de completar, (2) BanController enviaba push sin verificar `activated_at`, (3) NotificationController no filtraba usuarios baneados en notificaciones masivas
- **Fix:** (1) Guard `onboarding_seen` en useNotifications, (2) check `activated_at` en BanController, (3) `whereDoesntHave('activeBan')` en NotificationController
- **Archivos:** `hooks/useNotifications.ts:102`, `BanController.php:69`, `NotificationController.php:80`
- **Commit:** 53a535c + ed1f83a

### BUG-141: Reload Expo manda al onboarding — race condition hydrate (RESUELTO)
- **Severidad:** ALTA — usuario autenticado pierde sesion al recargar
- **Causa:** `(app)/_layout.tsx` evaluaba `if (!token)` antes de que `hydrate()` terminara de leer de SecureStore. Token era `null` por defecto, no porque el user no estuviera autenticado.
- **Fix:** Agregar `if (!isHydrated) return null` antes del check de token
- **Archivo:** `app/(app)/_layout.tsx:55`
- **Commit:** ed1f83a

---

## 2026-04-20 — QA Hallazgos (no bugs, mejoras pendientes)

- **QA-01:** Colores hardcodeados trending — RESUELTO: constante STATUS_COLORS en stand-stats.tsx
- **QA-02:** require('expo-router') dinamico — RESUELTO: import estatico `import { router } from 'expo-router'`
- **QA-03:** debounceTimers leak — RESUELTO: cleanup en return del useEffect (clearTimeout + batchTimer)

---

## 2026-04-19 — Sesion Mission Control Display + Metricas + Moderacion (13 bugs)

### BUG-140: results endpoint crash por first_name en attendees (RESUELTO)
- **Severidad:** ALTA — pending_answers query fallaba con "Unknown column first_name"
- **Causa:** Query usaba `with('attendee:id,first_name,last_name')` pero attendees no tiene esos campos, el nombre viene de User
- **Fix:** Cambiar a `with('attendee.user:id,name')`
- **Archivo:** PollController.php (results method)

### BUG-139: open_text answers sin moderacion en display publico (RESUELTO)
- **Severidad:** CRITICA — cualquier texto escrito por asistentes salia directamente en pantalla LED sin filtro
- **Causa:** No existia sistema de moderacion para respuestas de texto abierto
- **Fix:** Campo `is_approved` en live_poll_votes (default false para open_text), modal de moderacion en MC, endpoints approve/reject/batch, display solo muestra aprobadas
- **Archivo:** Migracion, LivePollVote model, PollController, app.js, styles.css, index.html

### BUG-138: YouTube iframe overlay bloqueaba controles (RESUELTO)
- **Severidad:** MEDIA — no se podia subir volumen ni interactuar con player YouTube
- **Causa:** `.mc-stream-top` con z-index:2 cubria toda la parte superior del iframe
- **Fix:** `pointer-events:none` en el overlay, `pointer-events:auto` solo en los hijos (chip, fullscreen btn)
- **Archivo:** public/mission-control/styles.css

### BUG-137: navigator.clipboard undefined en HTTP (RESUELTO)
- **Severidad:** MEDIA — boton copiar enlace LED no funcionaba en desarrollo (HTTP)
- **Causa:** `navigator.clipboard` solo existe en contextos seguros (HTTPS/localhost)
- **Fix:** Fallback con `textarea + execCommand('copy')` para HTTP
- **Archivo:** public/mission-control/app.js (copyText function)

### BUG-136: Keyboard shortcuts activos en TEXTAREA (RESUELTO)
- **Severidad:** BAJA — escribir "1" en pin modal cambiaba al tab Chat
- **Causa:** Filter solo excluia INPUT y SELECT, no TEXTAREA
- **Fix:** Agregar TEXTAREA al filtro de keydown
- **Archivo:** public/mission-control/app.js

### BUG-135: apiFetch crash en DELETE sin JSON body (RESUELTO)
- **Severidad:** MEDIA — rechazar respuesta podia crashear si backend devolvia 204 o body vacio
- **Causa:** `r.json()` siempre se llamaba sin verificar content-type
- **Fix:** Verificar content-type contiene `application/json` y status !== 204 antes de parsear
- **Archivo:** public/mission-control/app.js (apiFetch)

### BUG-134: chat:history sobreescribia msgCount de Redis (RESUELTO)
- **Severidad:** MEDIA — counter de mensajes bajaba de 500 a 20 al reconectar
- **Causa:** `msgCount = msgs.length` (historial max 20) reemplazaba el count real de Redis
- **Fix:** Solo actualizar msgCount desde historial si Redis no ha enviado un count mayor
- **Archivo:** public/mission-control/app.js

### BUG-133: Duplicate socket.on('poll:updated') listener en MC (RESUELTO)
- **Severidad:** BAJA — doble fetch de resultados en cada update, race condition potencial
- **Causa:** Un listener para handlePollUpdate (linea 474) y otro para refreshModList (linea 877)
- **Fix:** Integrar refresh del modal dentro de handlePollUpdate, eliminar listener duplicado
- **Archivo:** public/mission-control/app.js

### BUG-132: Respuestas aprobadas en batch salen de golpe en display (RESUELTO)
- **Severidad:** MEDIA — 10 respuestas aprobadas simultaneamente aparecian todas de golpe, no se leian
- **Causa:** Display renderizaba todas las nuevas inmediatamente sin delay
- **Fix:** Cola de presentacion client-side (textQueue): despacha 1 respuesta cada 1.8s, el moderador aprueba cuando quiera
- **Archivo:** public/display/session.html (textQueue, drainTextQueue, showNextText)

### BUG-131: Aprobar todas dispara rate limit 429 (RESUELTO)
- **Severidad:** ALTA — boton "Aprobar todas" en MC lanzaba N requests simultaneos, Laravel rate limiter los bloqueaba
- **Causa:** `Promise.all(ids.map(id => apiFetch(...)))` — un POST por voto
- **Fix:** Nuevo endpoint `POST /admin/polls/votes/approve-batch` que acepta array vote_ids, 1 sola query
- **Archivo:** PollController.php (approveVoteBatch), routes/api/admin.php, app.js

### BUG-130: Open text empuja titulo fuera de pantalla (RESUELTO)
- **Severidad:** ALTA — respuestas crecian infinito, titulo desaparecia del viewport
- **Causa:** Container sin altura fija ni overflow hidden, items se acumulaban sin limite
- **Fix:** `.d-poll` con flex column height:100%, `.d-text-scroll` con flex:1 overflow:hidden, mask-image gradient top, max 20 items DOM
- **Archivo:** public/display/session.html (CSS + JS)

### BUG-129: Animaciones display se resetean en cada voto (RESUELTO)
- **Severidad:** MEDIA — barras parpadean y se reinician con cada poll:updated
- **Causa:** `renderPollResults` usaba `innerHTML` para reconstruir todo el DOM en cada update, reiniciando CSS animations
- **Fix:** Separar `renderPollFull` (primera carga con animaciones) de `updatePollInPlace` (solo cambia widths y counters via CSS transition)
- **Archivo:** public/display/session.html

### BUG-128: Display LED pierde proyeccion al refrescar (RESUELTO)
- **Severidad:** ALTA — operador de pantalla pierde contenido al recargar browser
- **Causa:** Estado de proyeccion (que poll se muestra) solo existia como evento socket transitorio, sin persistencia
- **Fix:** Guardar en Redis `display:active:session:{id}` (TTL 4h) al proyectar. En join:session, socket envia estado guardado al display
- **Archivo:** eventos-socket/src/chat.ts (display:project handler + join:session)

---

## 2026-04-18 — Sesion Mission Control QA Live (28 bugs)

### BUG-127: Mission Control metricas se pierden al refrescar (RESUELTO)
- **Severidad:** MEDIA — msgCount vuelve a 0 al recargar pagina
- **Causa:** Counter es variable JS client-side. Redis guarda ultimos 20 mensajes pero no el count total.
- **Fix:** exactamente el previsto — Redis INCR `chat:count:session:{id}` por mensaje.
- **Verificado 2026-08-01** (auditoria de vigencia, la cadena ya estaba completa y sin
  marcar): `eventos-socket/src/chat.ts:394` incrementa · `:289-291` lo emite al socket
  que entra · `index.ts:415` lo sirve · `mission-control/app.js:926-928` adopta
  `d.chatCount` cuando supera al contador local.

### BUG-126: Toast emoji engana — dice "activado" sin guardar (RESUELTO)
- **Severidad:** MEDIA — moderador cree que emoji only esta activo pero no se guardo
- **Causa:** Toggle mostraba toast inmediato pero el cambio solo se aplicaba al dar "Guardar" manualmente
- **Fix:** Auto-save al togglear emoji only y slow mode (llama `saveConfig()` directamente)
- **Archivo:** `public/mission-control/app.js`

### BUG-125: Mission Control poll form muestra opciones en star_rating/open_text (RESUELTO)
- **Severidad:** MEDIA — UX confusa, inputs de opciones visibles cuando no aplican
- **Causa:** Al cambiar tipo de encuesta en el selector, los inputs de "Opcion 1/2" no se ocultaban
- **Fix:** `updatePollFormType()` oculta `#pollOptsContainer` y `#pollAddOpt` cuando tipo no es multiple_choice
- **Archivo:** `public/mission-control/app.js`

### BUG-124: App crash RangeError status 0 cuando backend/socket cae (RESUELTO)
- **Severidad:** CRITICA — app crashea con `RangeError: Failed to construct 'Response': status 0`
- **Causa:** `fetch()` lanza error crudo cuando servidor no responde (network error, timeout). React Native intenta construir `Response` con status 0 (fuera del rango 200-599). React Query reintentaba el error crudo.
- **Fix:** api.ts: catch en fetch → `ApiError(0, null, msg, 'NETWORK_ERROR')` tanto en `request()` como `upload()`. React Query retry inteligente: no reintenta 401/403/404/422, si reintenta network errors y 5xx hasta 2 veces.
- **Archivos:** `lib/api.ts`, `app/_layout.tsx`

### BUG-123: PinnedBanner invisible en ambos temas (RESUELTO)
- **Severidad:** ALTA — mensaje anclado no se ve, feature roto visualmente
- **Causa:** `surface.medium` es `rgba(255,255,255,0.04)` en Noir y `rgba(0,0,0,0.04)` en Lux — ambos invisibles. Ademas PinnedBanner creaba su PROPIO socket (conexion duplicada innecesaria).
- **Fix:** Reescrito: recibe message/author como props (sin socket propio). Usa `surface.backgroundElevated` (visible). Integrado dentro de ChatPanel.tsx y session-chat/[id].tsx. Eliminado de session-stream/[id].tsx.
- **Archivos:** `components/ui/PinnedBanner.tsx`, `components/screens/ChatPanel.tsx`, `app/(app)/session-chat/[id].tsx`, `app/(app)/session-stream/[id].tsx`

### BUG-122: PollSlides estrellas rojas en Lux — accent del evento como color estrella (RESUELTO)
- **Severidad:** MEDIA — estrellas se ven rojas/feas cuando accent del evento es rojo
- **Causa:** StarRating usaba `accent` del evento como color de estrellas. Si accent=#ff0000, estrellas rojas. Ademas sombras `theme.shadow.sm` en Lux creaban cuadrados blancos feos.
- **Fix:** Color gold fijo `#F5B740` para estrellas (no depende de accent). Sombras eliminadas de options y container. Blur Lux reducido de 60 a 10, fondo 95% opaco.
- **Archivo:** `components/screens/PollSlides.tsx`

### BUG-121: cooldown no se limpia cuando slowModeSeconds cambia a 0 (RESUELTO)
- **Severidad:** MEDIA — usuario queda con countdown activo aunque admin desactivo slow mode
- **Fix:** useEffect con dependencia slowModeSeconds, clearInterval si <= 0
- **Archivo:** ChatPanel.tsx

### BUG-120: response unwrapping incorrecto en useSessionConfig (RESUELTO)
- **Severidad:** ALTA — fallback resp podria mergear shape incorrecto al config
- **Fix:** Usar resp?.data con validacion typeof object
- **Archivo:** useSessionConfig.ts

### BUG-119: token flood en personal_access_tokens (RESUELTO)
- **Severidad:** MEDIA — cada visita al monitor creaba token sin limpiar
- **Fix:** Eliminar tokens MC anteriores de la misma sesion antes de crear nuevo
- **Archivo:** routes/web.php

### BUG-118: sesiones soft-deleted no excluidas en ruta monitor (RESUELTO)
- **Severidad:** ALTA — podria servir monitor de sesion eliminada
- **Fix:** Usar withoutTrashed() explicito en findOrFail
- **Archivo:** routes/web.php

### BUG-117: evento inactivo permite abrir monitor (RESUELTO)
- **Severidad:** MEDIA — monitor funciona en eventos pausados/cancelados
- **Fix:** Verificar event.is_active antes de generar token
- **Archivo:** routes/web.php

### BUG-116: custom_enabled + interactive_mode coexisten sin validacion (RESUELTO)
- **Severidad:** BAJA — estado inconsistente en BD
- **Fix:** Si custom_enabled=true, forzar interactive_mode=none en controller
- **Archivo:** SessionConfigController.php

### BUG-115: Poll results sin validacion de shape (RESUELTO)
- **Severidad:** MEDIA — si r.questions no existe, rendering crashea
- **Fix:** Verificar r && r.questions antes de cachear
- **Archivo:** mission-control/app.js

### BUG-114: API response sin validacion de shape en config load (RESUELTO)
- **Severidad:** MEDIA — si API retorna shape inesperado, falla silenciosamente
- **Fix:** Validar r.data existe y es object antes de usar
- **Archivo:** mission-control/app.js, useSessionConfig.ts

### BUG-113: onclick inline en botones Q&A moderacion (RESUELTO)
- **Severidad:** MEDIA — onclick con q.id y status en strings HTML
- **Fix:** Event delegation con data-qid y data-status en qnaList
- **Archivo:** mission-control/app.js

### BUG-112: onclick inline en lista baneados del monitor (RESUELTO)
- **Severidad:** MEDIA — anti-pattern XSS, onclick con datos dinamicos
- **Fix:** Event delegation con data-aid en bannedList
- **Archivo:** mission-control/app.js

### BUG-111: PollSlides colores hardcoded no coinciden con tema (RESUELTO)
- **Severidad:** MEDIA — encuesta se ve con accent rojo, fondo blanco inconsistente
- **Causa:** PollSlides usaba rgba dark hardcoded. Migrado a useTheme() pero el accent del evento estaba en #ff0000.
- **Fix:** migrado a useTheme(). Accent cambiado a #1A1A1A.
- **Verificado 2026-08-01** (auditoria de vigencia): `PollSlides.tsx` deriva del tema
  (`theme.isDark`, `theme.blur.tint`). Los colores literales que quedan NO son deuda,
  son decisiones posteriores de Kamilo: `#B5A68B` es la dorada unificada
  ([[project_platinum_gold]]) y el verde/rojo de estado sigue el criterio del
  SurveyDeck (cierre en verde, NO accent — el accent puede ser rojo y leerse como
  alarma). El "falta pulido visual" era apreciacion, no defecto.
- **Archivo:** PollSlides.tsx, PollPanel.tsx, QnAPanel.tsx

### BUG-110: poll sync — PollSlides mantenia state del poll anterior (RESUELTO)
- **Severidad:** ALTA — al lanzar nueva encuesta, app mostraba "Gracias por responder" del poll cerrado
- **Causa:** React reutilizaba el componente PollSlides sin reiniciar `useState(currentIdx)`. El currentIdx quedaba en totalQ del poll anterior.
- **Fix:** Agregar `key={activePoll.id}` en PollPanel para forzar remount
- **Archivo:** PollPanel.tsx, useChat.ts
- **Commit:** ca590f9

### BUG-109: emitConfigUpdate usaba secret incorrecto (RESUELTO)
- **Severidad:** ALTA — socket server rechazaba config updates del backend (401)
- **Causa:** Controller usaba `config('services.socket.secret')` que era vacio. El correcto es `config('services.socket.internal_secret')`
- **Fix:** Cambiar a `internal_secret`
- **Archivo:** SessionConfigController.php
- **Commit:** bfeccf1

### BUG-108: abort(422) retornaba HTML en API JSON (RESUELTO)
- **Severidad:** MEDIA — clientes recibían HTML en vez de JSON al enviar URL no permitida
- **Causa:** `abort(422, 'mensaje')` genera respuesta HTML por defecto en Laravel
- **Fix:** Usar `ValidationException::withMessages()` que retorna JSON
- **Archivo:** SessionConfigController.php
- **Commit:** 4e8caaf

### BUG-107: '' literal not terminated — script tag roto por HTML en string JS (RESUELTO)
- **Severidad:** CRITICA — monitor no cargaba, todo el JS fallaba
- **Causa:** HTML embebido en strings JS (srcdoc con `</style></head>`) rompia el parser HTML del browser
- **Fix:** Separar en archivos: mission-control/index.html + styles.css + app.js
- **Archivo:** public/mission-control/*
- **Commit:** 201aeb1

### BUG-106: cookies same-origin causaban 403 en fetch (RESUELTO)
- **Severidad:** CRITICA — PATCH/GET desde monitor daba 403 siempre en browser
- **Causa:** Browser envia cookies automaticamente en same-origin fetch (default). Laravel statefulApi priorizaba cookie sesion web (usuario anonimo) sobre Bearer token.
- **Fix:** `credentials: 'omit'` explicito en fetch — Bearer token only
- **Archivo:** mission-control/app.js
- **Commit:** 87427a6

### BUG-105: XSS en onclick inline del chat monitor (RESUELTO)
- **Severidad:** CRITICA — inyeccion de codigo via mensajes de chat con comillas
- **Causa:** `escAttr()` con comillas simples en onclick inline no era seguro. HTML entities se decodifican dentro de atributos.
- **Fix:** Event delegation con data attributes + msgDataMap (no mas onclick inline)
- **Archivo:** chat-monitor.html
- **Commit:** 4e8caaf

### BUG-104: panel switching no funcionaba en monitor HTML (RESUELTO)
- **Severidad:** ALTA — click en tabs Q&A/Polls/Custom no cambiaba el panel visible
- **Causa:** CSS clase `.panel` era demasiado generica (conflicto con panel-header del sidebar). Luego display:none/flex peleaba con clase .visible.
- **Fix:** Usar IDs directos con style.display + setTimeout para clase .active
- **Archivo:** chat-monitor.html (luego mission-control/app.js)
- **Commit:** varios

### BUG-103: CSRF 419 en PATCH desde Mission Control monitor (RESUELTO)
- **Severidad:** ALTA — no se podia guardar cambios desde el monitor
- **Causa:** HTML servido por ruta web Laravel seteaba cookies de sesion. fetch enviaba cookies (same-origin). Laravel statefulApi aplicaba CSRF verification al detectar cookie.
- **Fix:** `credentials: 'omit'` en fetch + `validateCsrfTokens(except: ['api/*'])`
- **Archivo:** mission-control/app.js, bootstrap/app.php
- **Commit:** 87427a6

### BUG-102: conflicto interactive_mode vs chat_enabled/qna_enabled (RESUELTO)
- **Severidad:** CRITICA — dos sistemas paralelos controlaban lo mismo, app no cambiaba panel
- **Causa:** Mission Control usaba campos boolean (chat_enabled, qna_enabled, polls_enabled) mientras Filament usaba interactive_mode. Ambos emitian eventos socket diferentes. App confundida.
- **Fix:** Eliminar booleans del config. Monitor usa interactive_mode (mismo que Filament). Observer emite session:mode_changed automaticamente.
- **Archivo:** SessionConfigController.php, session-stream/[id].tsx, useSessionMode.ts
- **Commit:** 97d968e

### BUG-101: HMAC route usaba whereHas('roles') inexistente (RESUELTO)
- **Severidad:** ALTA — ruta /monitor/{id} siempre daba 500 "No hay administrador"
- **Causa:** La query usaba `whereHas('roles', ...)` pero Attendee no tiene relacion `roles`, usa campo `role` directo
- **Fix:** Cambiar a `whereIn('role', ['admin', 'organizer', 'moderator'])`
- **Archivo:** `routes/web.php` ruta /monitor
- **Commit:** c18fcc5

### BUG-100: role 'admin' no reconocido en requireModerator Q&A (RESUELTO)
- **Severidad:** ALTA — admin no podia moderar preguntas Q&A
- **Causa:** `requireModerator` en QuestionController solo aceptaba `organizer` y `moderator`, no `admin`
- **Fix:** Agregar `admin` al array de roles permitidos
- **Archivo:** `QuestionController.php:194`, `RatingController.php:117`
- **Commit:** c18fcc5

---

## 2026-04-16 — Sesion Light Mode + Migracion Tokens

### BUG-099: Networking sendRequest crashea "Cannot read property map of undefined" (RESUELTO)
- **Severidad:** ALTA — feature completamente roto, no se podia enviar solicitud de contacto
- **Causa:** `useSendContactRequest` onMutate optimistic update asumia estructura plana (`old.data.map()`) pero `useDirectory` usa `useInfiniteQuery` con estructura `{ pages: [{ data: [...] }] }`. Al acceder `old.data` en infinite query, era `undefined` → `.map()` crasheaba
- **Origen:** Bug pre-existente desde el revert del swipe networking (2026-04-15). Al implementar infinite query en directorio, el onMutate no se actualizo
- **Fix:** Cambiar `old.data.map(...)` por `old.pages.map(page => ({ ...page, data: page.data.map(...) }))`
- **Archivo:** `hooks/useNetworking.ts` linea 73-84
- **Commit:** 6124f0f

---

## 2026-04-14 — Sesion Onboarding Replay + Session Detail + Bancolombia (21 bugs)

### BUG-098: activate endpoint 500 con token ya usado (RESUELTO)
- **Severidad:** MEDIA — excepcion 500 en vez de error limpio 422
- **Causa:** `firstOrFail()` lanza ModelNotFoundException cuando token ya consumido
- **Fix:** Usar `first()` + ValidationException con mensaje claro
- **Archivo:** `AuthService.php:activateAccount()`

### BUG-097: invitation_token expuesto sin verificacion (RESUELTO)
- **Severidad:** MEDIA — token entregado a cualquiera que conozca el email
- **Causa:** check-email devolvia token directo sin verificar identidad
- **Fix:** Si evento tiene `activation_verify_field`, check-email no devuelve token. Nuevo endpoint `POST /auth/verify-identity` valida dato personal antes de entregar token.
- **Archivos:** `AuthController.php`, `AuthStep.tsx`, `authApi.ts`, `RegistrationSettingsResource.php`

### BUG-096: Mensajes API en ingles (RESUELTO)
- **Severidad:** BAJA — "Profile updated" y "Photo removed" aparecian en ingles
- **Fix:** Traducir a "Perfil actualizado." y "Foto eliminada."
- **Archivo:** `ProfileController.php`

### BUG-095: Access code no sanitiza espacios al pegar (RESUELTO)
- **Severidad:** MEDIA — usuario pega codigo con espacio, falla validacion
- **Causa:** Ni el backend ni la app eliminaban whitespace del codigo pegado
- **Fix:** Backend: `preg_replace('/\s+/', '', $value)` + merge limpio. App: `.replace(/\s/g, '')` antes de enviar.
- **Archivos:** `RegisterRequest.php`, `AuthStep.tsx`

### BUG-094: AccessCode race condition en registerUse (RESUELTO)
- **Severidad:** MEDIA — dos registros simultaneos podian exceder max_uses
- **Causa:** `increment('uses_count')` no verificaba max_uses atomicamente
- **Fix:** WHERE atomico con `uses_count < max_uses` en el UPDATE, retorna bool
- **Archivo:** `AccessCode.php:registerUse()`

### BUG-093: invitation_token reutilizable tras intercepcion (RESUELTO)
- **Severidad:** MEDIA — token interceptado podia usarse despues
- **Causa:** check-email devolvia el mismo token siempre, tokens viejos seguian siendo validos
- **Fix:** Regenerar token en cada check-email request. Token anterior se invalida automaticamente.
- **Archivo:** `AuthController.php`

### BUG-092: Session Detail favorite sin error handling (RESUELTO)
- **Severidad:** BAJA — toggle falla sin informar al usuario
- **Causa:** `toggleFavorite.mutate()` sin onError callback
- **Fix:** Agregar `onError` con toast de error
- **Archivo:** `session/[id].tsx`

### BUG-091: FormStep pre-fill falla silenciosamente (RESUELTO)
- **Severidad:** BAJA — usuario no sabe por que campos estan vacios en replay
- **Causa:** `.catch(() => {})` sin feedback al usuario
- **Fix:** Toast de error "No se pudieron cargar tus datos previos"
- **Archivo:** `FormStep.tsx`

### BUG-090: InterestsStep replay — IDs huerfanos no validados (RESUELTO)
- **Severidad:** MEDIA — IDs de opciones eliminadas quedaban seleccionados invisiblemente
- **Causa:** `selected_ids` del API se cargaban sin verificar que existieran en opciones actuales
- **Fix:** Filtrar selected_ids contra IDs validos de options. Esperar a que options carguen antes de fetch.
- **Archivo:** `InterestsStep.tsx`

### BUG-089: PhotoStep replay — foto aparece sin animacion (RESUELTO)
- **Severidad:** BAJA — cosmetic, foto aparece de golpe sin fade-in
- **Causa:** `photoOpacity` se inicializaba a 1 si photoUri existia, pero en replay se seteaba despues del init
- **Fix:** Inicializar opacity a 0, animar a 1 cuando se pre-carga la foto en replay
- **Archivo:** `PhotoStep.tsx`

### BUG-088: registrationApprovedAt fallback sobreescribe dato fresco (RESUELTO)
- **Severidad:** ALTA — usuario con aprobacion revocada podia entrar con cache viejo
- **Causa:** `att?.registration_approved_at ?? user?.registrationApprovedAt` — si servidor devuelve null (pendiente), `??` salta al cache que podia tener 'auto'
- **Fix:** Priorizar dato del attendee fresco; fallback al cache solo cuando no hay attendee
- **Archivo:** `app/index.tsx`

### BUG-087: dynamicOptions stale al cambiar pais en FormStep (RESUELTO)
- **Severidad:** MEDIA — ciudades del pais anterior persisten si API falla
- **Causa:** Al cambiar parent field, se reseteaba el valor del child pero no se limpiaban las opciones dinamicas en `dynamicOptions` state
- **Fix:** Clear `dynamicOptions[field.key]` junto con el reset del child value
- **Archivo:** `FormStep.tsx`

### BUG-086: isReplay/postActivation se pierden en re-renders (RESUELTO)
- **Severidad:** ALTA — replay no funciona (confetti, campos vacios)
- **Causa:** Flags leidos con `getCached()` en cuerpo del componente y borrados inmediatamente. En re-renders, valor era false.
- **Fix:** Usar `useState(() => ...)` con lazy initializer para persistir valor inicial
- **Archivo:** `OnboardingContext.tsx`

### BUG-085: Onboarding custom fields (country/city) no se guardan (RESUELTO)
- **Severidad:** MEDIA — datos de ubicacion se perdian
- **Causa:** FormStep enviaba string keys ("country") a `PUT /me/registration-fields`, pero backend esperaba numeric field IDs
- **Fix:** Nueva columna `onboarding_data` JSON en attendees + endpoints `GET/PUT /me/onboarding-data`
- **Archivos:** Migration, `Attendee.php`, `ProfileController.php`, `api.php`, `FormStep.tsx`

### BUG-084: Onboarding replay — campos vacios, confetti, puntos dobles (RESUELTO)
- **Severidad:** ALTA — 8 sub-bugs en flujo "Ver introduccion de nuevo"
- **Causa:** No habia distincion entre primera vez y replay. Mismo flag `post_activation_onboarding` para ambos flujos
- **Sub-bugs:**
  1. FormStep campos siempre vacios (no pre-fill de profile)
  2. InterestsStep selecciones vacias (no carga my-interests)
  3. PhotoStep no muestra foto actual del usuario
  4. Confetti aparece siempre (sin sentido en replay)
  5. Puntos se otorgan de nuevo (gamificacion inflada)
  6. Titulo dice "Bienvenido" en vez de "Datos actualizados"
  7. Hints repiten info obvia
  8. "Saltar" enganoso (campos vacios sugieren datos perdidos)
- **Fix:** Flag separado `replay_onboarding`, `isReplay` en contexto, pre-fill desde APIs existentes
- **Archivos:** `ProfileScreen.tsx`, `OnboardingContext.tsx`, `PhotoStep.tsx`, `FormStep.tsx`, `InterestsStep.tsx`, `DoneStep.tsx`

### BUG-083: Skip button cuenta campos ocultos (depends_on) (RESUELTO)
- **Severidad:** MEDIA — UX confusa, boton skip desaparece incorrectamente
- **Causa:** `config.fields.some(f => f.required)` incluia campos hidden por depends_on
- **Fix:** Usar `visibleFields` en vez de `config.fields`
- **Archivo:** `FormStep.tsx`

### BUG-082: Fetch cities en cada keystroke de FormStep (RESUELTO)
- **Severidad:** MEDIA — llamadas API innecesarias, rendimiento
- **Causa:** useEffect para cities dependia de `[values]` completo, se ejecutaba en cada cambio de cualquier campo
- **Fix:** Unificar effects, solo ejecutar cuando parent value realmente cambia via prevParentValues ref
- **Archivo:** `FormStep.tsx`

### BUG-081: index.tsx registrationApprovedAt null sin fallback (RESUELTO)
- **Severidad:** ALTA — redirige a pending-approval incorrectamente
- **Causa:** Si `/auth/me` retorna attendee null, `registrationApprovedAt` queda null y redirige a pending-approval
- **Fix:** Fallback a `user?.registrationApprovedAt` del cache cuando attendee es null
- **Archivo:** `app/index.tsx:80`

### BUG-080: index.tsx fetch sin timeout (RESUELTO)
- **Severidad:** ALTA — pantalla negra indefinida si backend no responde
- **Causa:** `fetch('/auth/me')` sin AbortController, emulador con red lenta cuelga forever
- **Fix:** Agregar AbortController con timeout 6s + clearTimeout en finally
- **Archivo:** `app/index.tsx:34`

### BUG-079: Login no pasa event_slug al backend (RESUELTO)
- **Severidad:** ALTA — eventId queda null, home carga vacia
- **Causa:** `authApi.login()` no incluia `event_slug`, backend adivinaba el evento por `is_active` fallback
- **Fix:** Agregar `event_slug: DEFAULT_EVENT_SLUG` al payload de login en AuthStep
- **Archivo:** `AuthStep.tsx:213`

### BUG-078: API onboarding crashea con array_flip en preset cities (RESUELTO)
- **Severidad:** CRITICA — API devuelve 500, onboarding no carga
- **Causa:** `array_flip()` en `resolveStepsConfigUrls()` falla con preset `cities` que es array anidado (country→array de ciudades)
- **Fix:** Skip campos con `depends_on` en la resolucion + verificar `is_string` antes de `array_flip`
- **Archivo:** `OnboardingController.php:170`

---

## 2026-04-12 — Sesion Moderacion + Auth + Error Handling (16 bugs)

### BUG-077: Imagenes Filament no se muestran en app (RESUELTO)
- **Severidad:** ALTA (feature roto)
- **Causa:** Filament guarda path relativo (`onboarding/archivo.png`), app necesita URL completa. Ademas hostname `eventos-backend.test` no resuelve desde dispositivo.
- **Fix:** `resolveStepsConfigUrls()` en API convierte a URL con `asset()`. App aplica `fixStorageUrl()` para reemplazar hostname por IP.
- **Nota:** En produccion con Cloudflare R2, las URLs seran absolutas y este fix no sera necesario. Documentar en DISPONIBILIDAD-HA.md al migrar a R2.

### BUG-076: Titulo/subtitulo visible cuando hero image cubre todo (RESUELTO)
- **Severidad:** MEDIA (UX/diseño)
- **Causa:** No habia forma de ocultar textos cuando el key visual hero ya comunicaba todo.
- **Fix:** Toggle `show_text` en Filament. Si false, solo muestra botones.

### BUG-075: Textos de botones welcome no editables (RESUELTO)
- **Severidad:** MEDIA (config)
- **Causa:** "Crear cuenta" y "Ya tengo cuenta" hardcoded en WelcomeStep.
- **Fix:** Campos `register_button_text` y `login_button_text` en Filament.

### BUG-074: Titulo welcome "Bienvenido a" no editable (RESUELTO)
- **Severidad:** MEDIA (config)
- **Causa:** `title_prefix` hardcoded en WelcomeStep. No habia campo en Filament.
- **Fix:** Campo `title_prefix` en Filament + lectura desde config con fallback.

### BUG-073: "No tienes cuenta? Registrate" visible sin registro habilitado (RESUELTO)
- **Severidad:** MEDIA (UX)
- **Causa:** AuthStep mostraba link toggle login/register sin verificar `show_register_button`.
- **Fix:** Condicional `(isRegister || showRegisterOption)` oculta el link si registro desactivado.

### BUG-072: ConnectionError boton se estira verticalmente (RESUELTO)
- **Severidad:** MEDIA (visual)
- **Causa:** Componente ConnectionError no tenia layout split (content center + boton bottom).
- **Fix:** Reestructurado identico a banned.tsx: content centrado + boton full-width abajo con SafeArea.

### BUG-071: Post-activation onboarding detecta token viejo (RESUELTO)
- **Severidad:** ALTA (flujo roto)
- **Causa:** OnboardingContext usaba `hasToken` para detectar post-activacion. Cualquier token viejo activaba el salto a photo.
- **Fix:** Flag especifico `post_activation_onboarding` que solo activate-account setea, se consume una vez.

### BUG-070: Activate-account sin token deja pantalla sin salida (RESUELTO)
- **Severidad:** MEDIA (edge case)
- **Causa:** Deep link sin parametro `token` mostraba pantalla con campos pero sin poder enviar.
- **Fix:** Redirige a `/onboarding` inmediatamente si no hay token.

### BUG-069: Attendee null causa loop pending-approval (RESUELTO)
- **Severidad:** ALTA (edge case)
- **Causa:** Si backend devuelve `attendee: null`, `registrationApprovedAt` queda `null` y usuario queda atrapado.
- **Fix:** Si no hay attendee, `registrationApprovedAt = 'no_attendee'` (no bloquea).

### BUG-068: onboarding_seen no se limpia al logout (RESUELTO)
- **Severidad:** CRITICA (multi-usuario)
- **Causa:** `clearAuth()` no borraba el flag `onboarding_seen`. Segundo usuario en mismo dispositivo saltaba welcome.
- **Fix:** `clearAuth()` ahora limpia `onboarding_seen` y `post_activation_onboarding`.

### BUG-067: Onboarding fetch sin timeout (RESUELTO)
- **Severidad:** ALTA (UX)
- **Causa:** `onboardingApi.get()` usaba `fetch()` sin AbortController. Si Laravel esta caido, pantalla negra 30-120s.
- **Fix:** AbortController con timeout 6s + ConnectionError screen con boton reintentar.

### BUG-066: registrationApprovedAt null bloquea usuarios sin approval (RESUELTO)
- **Severidad:** CRITICA (flujo roto)
- **Causa:** `AttendeeResource` devuelve `registration_approved_at: null` cuando el evento NO requiere approval. App interpreta null como "no aprobado" y manda a pending-approval.
- **Fix:** Backend devuelve `'auto'` cuando `registration_requires_approval = false`.

### BUG-065: Login no verifica approval antes de mostrar QR (RESUELTO)
- **Severidad:** ALTA (flujo roto)
- **Causa:** AuthStep login iba directo a `goTo('done')` sin verificar `registrationApprovedAt`. Usuario no aprobado veia QR.
- **Fix:** Agregado check `registrationApprovedAt === null` en login flow + index.tsx.

### BUG-064: Toast vacio en pending-approval (RESUELTO)
- **Severidad:** MEDIA (UX)
- **Causa:** `toast.show('texto', 'variant')` — firma incorrecta, espera objeto `{ message, variant }`.
- **Fix:** Corregido en pending-approval.tsx y activate-account.tsx.

### BUG-063: Ban no se valida server-side (RESUELTO)
- **Severidad:** ALTA (seguridad)
- **Causa:** No habia middleware que chequeara ban en cada API call.
- **Fix:** Middleware `CheckBan` aplicado a todas las rutas autenticadas (excepto auth/me, auth/logout).

### BUG-062: Token registro 30d hardcoded (RESUELTO)
- **Severidad:** MEDIA (seguridad)
- **Causa:** `AuthService.php` register y activate usaban `addDays(30)` hardcoded.
- **Fix:** Reemplazado por `config('sanctum.expiration')` en ambos metodos.

### Bugs no criticos detectados (no corregidos — code smells)
- **CS-001:** Race condition en token refresh deduplication — multiples 401 pueden llamar clearAuth() dos veces. Sin impacto real.
- **CS-002:** Flag `post_activation_onboarding` se consume al montar provider — fragil si re-monta, pero no re-monta en flujo normal.
- **CS-003:** Email verified state se resetea al cambiar login/register — UX minor, email queda escrito.

---

## 2026-04-12 — Auditoria de Flujos Auth (39 escenarios + 10 edge cases)

### Escenarios verificados

**Flujo normal (10):** Primera vez UP/DOWN/retry, usuario regresa, token guardado, token expirado refresh/fail, logout.
**Login inteligente (7):** checkEmail not_found/pending_activation/active, password correcto/incorrecto, cuenta bloqueada, error red.
**Registro (5):** Exitoso sin/con approval, email existente, error red, logout+re-registro.
**Activate account (4):** Token valido, sin token, token usado, activacion completa.
**Ban (6):** Socket RT, HTTP 403, reabrir baneado, ban temporal expira, desbanear, ban desde chat.
**Pending approval (4):** Pantalla, verificar pendiente, verificar aprobado, reabrir pendiente.
**Edge cases (3):** Evento sin approval, attendee null, servidor cae mid-onboarding.

**Resultado:** 39/39 escenarios cubiertos. 9 bugs encontrados y corregidos (BUG-064 a BUG-072). 3 code smells documentados.

---

## 2026-04-11 — Sesion Onboarding Steps + Auth + Seguridad (5 bugs)

### BUG-061: Back arrow inconsistente (chevron-left vs arrow-left) (RESUELTO)
- **Severidad:** BAJA
- **Causa:** Onboarding usaba `chevron-left`, resto del app usa `arrow-left`.
- **Fix:** `77a85a4` — unificado a `arrow-left`.

### BUG-060: Logout redirige a login viejo NativeWind (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** `index.tsx` y `_layout.tsx` redirigian a `/(auth)/login` que fue eliminado.
- **Fix:** `9e47000` + `1bfc6c6` — redirige a `/onboarding`. Si `onboarding_seen=true`, empieza en auth step directo.

### BUG-059: Cambiar foto perfil no actualizaba MiQR (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** `useQrToken` tiene `staleTime: Infinity`. Al cambiar foto en perfil, MiQR seguia con la vieja.
- **Fix:** `9965312` — `invalidateQueries(['qr-token'])` al subir/eliminar foto.

### BUG-058: Foto upload no actualizaba authStore (RESUELTO)
- **Severidad:** ALTA (feature roto)
- **Causa:** `api.upload('/me/photo')` en PhotoStep no capturaba la respuesta. El store quedaba con `photoUrl: null`. Mi QR y Perfil mostraban beam avatar.
- **Fix:** `9e47000` — captura `photo_url` de respuesta y actualiza authStore.

### BUG-057: Password input autoCapitalize mayuscula en Android (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Campos password sin `autoCapitalize="none"` ni `autoCorrect={false}`. Android pone primera letra mayuscula.
- **Fix:** `585c8c4` — agregado en 7 inputs (AuthStep, login, register, activate-account).

---

## 2026-04-10 — Sesion Vendedor + Mi Stand + Liquid Glass (3 bugs)

### BUG-056: Teal color en Mi Stand card (solo gamification usa teal) (RESUELTO)
- **Severidad:** BAJA
- **Causa:** Mi Stand card usaba colores teal que estan reservados para gamification.
- **Fix:** `e04b62e` — colores unificados blancos, teal solo en GamificationHud.

### BUG-055: VendorHappeningNow ratio roto en flex (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** flex porcentual no daba anchos correctos para carousel + Mi Stand card.
- **Fix:** `e04b62e` — anchos fijos en pixeles (65%/32%/3% gap) en vez de flex.

### BUG-054: Rules of hooks en VendorHappeningNow (RESUELTO)
- **Severidad:** ALTA (crash)
- **Causa:** Hooks despues de early return violaba reglas de React.
- **Fix:** `e04b62e` — mover todos los hooks antes del early return.

---

## 2026-04-10 — Sesion UI + Rewards (8 bugs)

### BUG-053: Select sponsor vacio en Filament (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** options() con session variable null.
- **Fix:** `1ac843c` — relationship() con preload().

### BUG-052: FlashList v2 estimatedItemSize deprecated (RESUELTO)
- **Severidad:** BAJA
- **Causa:** FlashList v2 calcula tamaño automaticamente.
- **Fix:** `f3ed54a` — prop eliminada de social.tsx.

### BUG-051: Mutation de canje perdia contexto (RESUELTO)
- **Severidad:** ALTA
- **Causa:** setRedeemConfirm(null) cerraba sheet ANTES de mutation.
- **Fix:** `38cd0f5` — mutation primero, cerrar despues, setTimeout 400ms para QR.

### BUG-050: QR Modal de canje detras de BottomSheets (RESUELTO)
- **Severidad:** ALTA
- **Causa:** Overlay con absoluteFillObject dentro del View principal.
- **Fix:** `38cd0f5` — Modal real de RN con transparent + animationType fade.

### BUG-049: QR de canje salia redondo (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Usaba RgbRing (circular) en vez de componente rectangular.
- **Fix:** `6cb955f` — creado RgbRect con borderRadius: 16.

### BUG-048: RewardService usaba campo 'metadata' inexistente (RESUELTO)
- **Severidad:** ALTA
- **Causa:** points_log no tiene columna metadata, usa reference_type/reference_id.
- **Fix:** `4787934` — cambiado a reference_type: 'reward' + reference_id.

### BUG-047: points_log.points era unsigned — no aceptaba descuentos (RESUELTO)
- **Severidad:** ALTA
- **Causa:** `unsignedSmallInteger('points')` rechazaba -50 para canje.
- **Fix:** `4787934` — migration cambia a `smallInteger` (signed). award() permite negativos.

### BUG-046: SQL query expuesto al usuario en error de canje (RESUELTO)
- **Severidad:** CRITICA
- **Causa:** Endpoint `POST /rewards/{id}/redeem` sin try-catch. MySQL error propagaba query raw al cliente.
- **Fix:** `a4f5afc` — try-catch en RewardController. Throwable genera mensaje generico. report() loguea.

---

## 2026-04-10 — Responsive Audit (5 bugs)

### BUG-045: ModuleMenu/HappeningNow/HomeHero tamanos fijos (RESUELTO)
- **Severidad:** BAJA
- **Causa:** Heights y fontSizes hardcoded no se adaptan.
- **Fix:** `5cb8c4e` — proporcionales al screenWidth.

### BUG-044: Leads header 3 botones overflow (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** 3 botones en 1 fila no caben en 360dp.
- **Fix:** `5cb8c4e` — 2 filas.

### BUG-043: Login sin ScrollView (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Formulario se cortaba en pantallas pequenas.
- **Fix:** `5cb8c4e` — ScrollView agregado.

### BUG-042: 12 pantallas SafeArea inconsistente (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** pt-14/pt-6 hardcoded en vez de insets.
- **Fix:** `5cb8c4e` — useSafeAreaInsets().top + 16 en todas.

### BUG-041: Skeletons overflow en pantallas 360dp (x3) (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Width fijo en vez de flex:1.
- **Fix:** `5cb8c4e` — flex:1 en skeletons.

---

## 2026-04-09e — Profile + Branding (3 bugs)

### BUG-040: Flecha en Mi Agenda (es tab, no stack) (RESUELTO)
- **Severidad:** BAJA
- **Causa:** Mi Agenda tenia header de stack con flecha.
- **Fix:** Quitar flecha cuando favoritesOnly=true.

### BUG-039: SVG avatar no renderizaba en social (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** react-native Image no soporta SVG.
- **Fix:** Cambiar a expo-image.

### BUG-038: Beam avatar fallback no funcionaba (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Falta de fallback en Photo component.
- **Fix:** avatar_url accessor con beam fallback en todos los endpoints.

---

## 2026-04-09b — Sponsors (3 bugs)

### BUG-037: Pixel fraccionario en grid 4col (RESUELTO)
- **Severidad:** BAJA
- **Causa:** Calculo sin Math.floor().
- **Fix:** Math.floor() en dimensiones.

### BUG-036: Loop infinito con sponsors = [] (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Comparacion de referencia [] crea nuevo objeto cada render.
- **Fix:** Constante EMPTY_SPONSORS.

### BUG-035: Trivia nunca se mostraba (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** visitStand() no se ejecutaba automaticamente al montar.
- **Fix:** Ejecutar implicitamente en mount.

---

## 2026-04-09 — Social Unificado (7 bugs)

### BUG-034: ~20 bugs menores social (gesture, layout, optimistic updates) (RESUELTO)
- **Severidad:** MEDIA/BAJA
- **Fix:** Sesion dedicada, 30 bugs resueltos en total.

### BUG-033: PhotoViewer initialIndex out of bounds (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** initialIndex > array length.
- **Fix:** Validar indice.

### BUG-032: Upload foto network error con archivos grandes (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** upload_max_filesize 1M en PHP.
- **Fix:** Quality 0.5 + upload_max_filesize 2M.

### BUG-031: MediaTypeOptions deprecated en expo-image-picker (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** API cambio en nueva version.
- **Fix:** Usar ['images'] array directamente.

### BUG-030: Comentarios no scrolleaban en BottomSheet (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** GestureDetector + FlatList conflicto.
- **Fix:** Gesture solo en handle, nestedScrollEnabled=true.

### BUG-029: Stale closures en timer de stories (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Closure captura valores iniciales del state.
- **Fix:** useRef para referencia mutable.

### BUG-028: GestureDetector sin GestureHandlerRootView (x2) (RESUELTO)
- **Severidad:** ALTA (crash)
- **Causa:** GestureDetector requiere GestureHandlerRootView como ancestor.
- **Fix:** Agregar wrapping en layout.

---

## 2026-04-08 — UI Masiva (Agenda, Speakers, Streaming) (9 bugs)

### BUG-027: debounceTimer con useState (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** useState se reinicializa cada render.
- **Fix:** useRef.

### BUG-026: Highlight re-trigger al favoritar (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** State no diferenciaba user tap vs programmatic.
- **Fix:** didHighlight ref.

### BUG-025: BreathingCarousel spring brusco (RESUELTO)
- **Severidad:** BAJA
- **Causa:** Spring behavior en Reanimated.
- **Fix:** withTiming suave + Easing.out(cubic).

### BUG-024: Hero titulo cortado (RESUELTO)
- **Severidad:** BAJA
- **Causa:** numberOfLines 2 + lineHeight incorrecto.
- **Fix:** numberOfLines 3, lineHeight 46.

### BUG-023: Emoji SVG crash en Filament (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Filament no soporta emojis en icon fields.
- **Fix:** Heroicons.

### BUG-022: LayoutAnimation no funciona con FlashList (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** FlashList virtualiza items.
- **Fix:** Key remount para animaciones (DaySlide).

### BUG-021: Animated.View absolute dentro de Pressable rompe Android (RESUELTO)
- **Severidad:** ALTA
- **Causa:** Android stacking context issue con overflow:hidden.
- **Fix:** Remover overlay, animar solo contenedor.

### BUG-020: interpolate template literal crash en worklet (RESUELTO)
- **Severidad:** ALTA (crash)
- **Causa:** Template literals no soportados en Reanimated worklets.
- **Fix:** Remover template literal.

### BUG-019: Flash blanco en Android al cambiar pantalla (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Activity background default blanco.
- **Fix:** backgroundColor #0e0e0e en app.json + ScreenWrapper.

---

## 2026-04-07 — Home + API (3 bugs)

### BUG-018: favoritedBy field name incorrecto (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** API retorna favoritedBy, no favorites.
- **Fix:** Corregir en controller y tipos TS.

### BUG-017: Pressable function style no aplica backgroundColor en Android (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Limitacion de React Native en Android.
- **Fix:** Separar View (bg) + Pressable (gesture).

### BUG-016: start_datetime/end_datetime API mismatch (RESUELTO)
- **Severidad:** ALTA
- **Causa:** Codigo esperaba start_datetime, API retorna start/end.
- **Fix:** Cambiar toda la app a start/end.

---

## 2026-04-07 — Security Audit (SEC-1/2/3)

### BUG-005 a BUG-015: 11 vulnerabilidades de seguridad (RESUELTO)
- **Severidad:** CRITICA
- Socket room auth, HTMLPurifier 8 modelos, token expiration, security headers, CORS, HTTPS, account lockout, Redis rate limiting, FormRequests, .env.production, security:check
- **Fix:** 42 tests, 11 fixes. Documentado en docs/FASE-SEGURIDAD.md.

---

## 2026-04-07 — Photobooth/Social/Gamification (2 bugs)

### BUG-004: Error de limite fotos silenciado (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Mensaje del backend se perdia en error handling.
- **Fix:** Mostrar mensaje real.

### BUG-003: useSessionRating no persistia en Expo Go (RESUELTO)
- **Severidad:** ALTA
- **Causa:** MMKV no sincroniza entre pantallas en Expo Go.
- **Fix:** Reescribir con react-query.

---

## 2026-04-06 — Agenda/Favorites (2 bugs)

### BUG-002: Filament SelectFilter dot notation crash (RESUELTO)
- **Severidad:** ALTA
- **Causa:** Filament no soporta relaciones anidadas en SelectFilter.
- **Fix:** Query manual.

### BUG-001: Favoritos Mi Agenda no sincronizaban (RESUELTO)
- **Severidad:** ALTA
- **Causa:** onMutate del toggleFavorite fallaba sin cache previa de mi-agenda.
- **Fix:** extractFavorites() — mi-agenda siempre derivada de agenda.

---

## Resumen acumulado

| Severidad | Count | Resueltos | Pendientes |
|-----------|-------|-----------|------------|
| CRITICA | 32 | 32 | 0 |
| ALTA | 83 | 83 | 0 |
| MEDIA | 104 | 104 | 0 |
| BAJA | 24 | 24 | 0 |
| **Total** | **243+** | **243+** | **0** |

> Nota: BUG-005 a BUG-015 cuentan como 11 bugs individuales en una sola entrada.

> **Corregido 2026-10-16:** la tabla de arriba quedo en agosto. Hoy hay **4 abiertos** (BUG-344, 345, 347 y 348, sesion
> 2026-09-13; BUG-345 con codigo en B3.1). Las tareas viven en `PENDIENTES.md` §3.

Historico 2026-08-01: Bugs pendientes: **NINGUNO**. Los dos ultimos (BUG-127 metricas MC persist, BUG-111
PollSlides accent) se cerraron el 2026-08-01 por auditoria de vigencia: ambos ya
estaban resueltos en codigo y nadie los habia marcado. Detalle en cada entrada.

---

## Patrones recurrentes (para prevenir)

1. **Android vs iOS** — Pressable, GestureDetector, flash blanco, stacking contexts
2. **Reanimated worklets** — no template literals, no shared values en render
3. **FlashList** — no LayoutAnimation, no absolute positioning, v2 rompe estimatedItemSize
4. **Filament** — no dot notation en SelectFilter, no emojis en icons
5. **Expo SDK** — APIs deprecated entre versiones, verificar changelogs
6. **Integracion** — NUNCA integrar 3+ componentes de golpe, uno a la vez
7. **DB schema** — si un campo puede ser negativo en el futuro, usar signed desde el inicio
8. **Error handling** — NUNCA exponer errores tecnicos al usuario, siempre try-catch en endpoints
9. **Carbon mutation** — `addMinutes()`/`addHours()` MUTAN el objeto. SIEMPRE usar `->copy()->addMinutes()` cuando se necesita el valor original intacto
10. **Race conditions** — SIEMPRE usar atomic WHERE o lockForUpdate() para cambios de estado concurrentes
11. **Bulk operations** — NUNCA foreach con query individual para 1000+ registros. Jobs async con bulk insert/update
