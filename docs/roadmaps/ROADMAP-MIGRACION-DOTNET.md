# ROADMAP — MIGRACION A .NET (dos sistemas conviviendo) — 56/203

> **Ventana operativa de la migracion.** Analisis base: `docs/migracion-dotnet.md` (inventario, riesgos, equivalentes).
> **Reglas:** `docs/BUENAS-PRACTICAS-MIGRACION.md` (version para Claude: `docs/plantillas/CLAUDE-dotnet.md`).
> **Decisiones (tomadas y abiertas, D.1 a D.6 incluidas):** `docs/decisiones.md`, unico lugar.
> **Solo la fase activa queda abierta;** las demas van plegadas (clic en el titulo para verlas).
> Clasificacion de pendientes: `docs/pendientes-2026-10.md`. Fuente unica de tareas: `docs/living/PENDIENTES.md`
> (cuando Kamilo de el dale, este roadmap se registra alla como bloque con sus contadores).
>
> **Decision en evaluacion (Kamilo 2026-10-21):** el evento del 6 de diciembre se cayo, no entran mas pendientes,
> y se quiere un solo sistema entendible de punta a punta para la linea enterprise. Este roadmap es el camino
> desde hoy (PHP + Node hacen todo) hasta "C# hace todo lo que PHP hace hoy", con los dos sistemas vivos en el medio
> sobre la misma base de datos y el mismo dominio.
>
> **Por que se migra (lo medido, no lo supuesto).** Agosto de 2026, droplet de 4 vCPU, 300 personas activas: Laravel
> gastaba 1,0 nucleo, Next 0,4, MySQL 0,3. PHP era el 60% de la carga. Antes de cachear el marco la maquina iba al 61%
> solo con 300; al limite (480 a 500) iba al 70% y a 700 se rompia (`project_stack_catalogo_2026_08_17`,
> `docs/infra/STACK-PRODUCCION.md`). Eso es el lenguaje: PHP-FPM arranca el framework completo en cada pedido. Kamilo lo
> vio en vivo y es el argumento principal. Los otros dos: un solo runtime entendible de punta a punta (hoy PHP-FPM + Node
> + PM2 + Horizon + cron, con 17 endpoints internos entre ellos) y un admin que se pueda mostrar a un cliente enterprise.
> La cifra de cuanto menos consume C# se mide en la semana 6 (M1.5.2) y con ella se recalcula el catalogo de niveles.
>
> **El argumento de fondo, medido el 2026-08-01 (`docs/infra/INFORME-STRESS-2026-08-01.md`, `DIAGNOSTICO-2026-08-02.md`,
> `COMO-CRECEMOS.md`):** en el droplet de 4 vCPU el techo fueron **70 peticiones por segundo** con la CPU al 0% libre y
> **6,1 GB de RAM sin usar**; PHP-FPM con 61 procesos y MySQL con 2 a 8 hilos. Textual del informe: "El cuello de botella
> es CPU, y es PHP. Ni la base de datos, ni la memoria, ni la red." La cuenta: 53 ms por peticion sin saturar, 4 nucleos,
> 75 por segundo teoricas, 70 medidas. De esos 53 ms, el peaje fijo de arrancar Laravel en cada peticion era 33 ms (con
> Sanctum escribiendo en cada lectura y Filament arrancando en cada peticion de la API); tras arreglarlo quedo en 11,4 ms,
> que es el piso de PHP-FPM. Como una persona real hace ~9 peticiones por pantalla, ese piso se paga 9 veces por pantalla.
> En el mismo informe, los sockets de Node con 5.000 conexiones gastaron 0 a 4% de CPU: lo caro es HTTP, y HTTP es PHP.
> En .NET el proceso arranca una vez y cada peticion cuesta del orden de 1 ms de framework, no 11: ese es el margen que
> se va a medir en M1.5.2.
>
> **Correccion de un dato del brief:** DigitalOcean no tiene region en Sao Paulo (`DIAGNOSTICO-2026-08-02.md` §1); el
> servidor real estuvo en nyc1 y desde Bogota son ~100 ms de ida y vuelta que se suman a todo. La region se decide en la
> fase 1 con el proveedor que haya.
>
> **Orden elegido: lo que duele primero, lo que funciona de ultimo.** Admin nuevo (fase 2) antes que el tiempo real
> (fase 3) y antes que la API publica (fase 4), porque el admin es lo que se vende y lo que mas parches tiene, y la API
> publica esta medida y funciona.
>
> Etiquetas como en los otros roadmaps: **[local]** se hace sin servidor · **[local+medir]** se codea local y cierra
> midiendo · **[servidor]** solo con servidor. Semanas = una persona con IA, tiempo completo.

---

## REGLAS DE CONVIVENCIA (aplican en todas las fases)

1. **Una base de datos.** El esquema lo manda Laravel hasta la fase 5. C# la lee con EF Core en modo scaffold y no crea
   migraciones. Una columna nueva = migracion Laravel + re-scaffold.
2. **Un Redis, prefijos distintos.** C# puede borrar claves de cache de PHP por nombre (es un `DEL`), nunca leerlas
   (van serializadas en PHP). Las claves que los dos leen (`socketauth:t:*`, `chat:history:*`, `session:{id}:viewers`)
   son JSON o strings crudos y quedan en el catalogo de claves (M0.4.4).
3. **Cada ruta tiene un dueño.** El proxy manda por ruta exacta, no por prefijo. Cabecera `X-Backend: php|dotnet` en
   toda respuesta.
4. **Cada tarea programada y cada cola tiene un dueño.** Al pasar a Hangfire se quita de `routes/console.php` o de
   Horizon en el mismo despliegue.
5. **Una ruta cruza a C# solo cuando su prueba de contrato pasa igual contra los dos.** Sin snapshot no hay cruce.
6. **Nada nuevo en PHP despues de la fase 0.** Si entra un pedido, la pregunta es "en cual carro", no "si se hace".
7. **Regla del cliente (escrita hoy para no discutirla con el cliente encima):** si aparece un cliente que paga antes
   del hito 1 (semana 12), se le entrega con PHP y la migracion se pausa donde este. Lo construido en C# no se bota.

## BANCO DE PRUEBAS: el MSI de Kamilo (revisado por Tailscale 2026-10-21)

`servidor@100.94.231.75`: MSI PRO X300, Ryzen 3 5300G (4 nucleos / 8 hilos, comparable a un droplet de 4 vCPU), 8 GB
fisicos de los que Linux ve 5,7 (la BIOS reserva 2 GB a la grafica: bajar UMA Frame Buffer a 512 MB), swap 4 GB, 82 GB
libres, Ubuntu 24.04.5. Ya tiene PHP 8.4.26 con pcntl, posix, intl, gd, zip, bcmath, pdo_mysql y opcache; Node 24;
MySQL 8.0 (con las bases `energia_en_ruta*` de otro proyecto: no se tocan); nginx con el sitio por defecto; composer; git.
**Le falta:** Docker + compose, Redis, .NET SDK, PHP-FPM como servicio, k6 (k6 corre desde el Windows).

**Para que se usa:** Linux de verdad para los dos backends (fase 0: suite y contratos; fase 1: M1.1.2 Docker del servicio
C#), la medicion comparativa PHP contra C# (M1.5.2, misma maquina, k6 desde el Windows por la red local) y demo/QA por
Tailscale. **Para que no:** capacidad que se cotiza (eso se mide en el servidor real) ni carga de miles (k6 gasta 1 a 2 MB
por usuario virtual). Instalacion pendiente para el arranque de la fase 1 (decision Kamilo 2026-10-21: primero 0.2).

## HITOS VENDIBLES

| Hito | Semana | Que se le muestra a un cliente | Que corre por dentro |
|---|---|---|---|
| 0 | 3 | Lo de hoy, con seguridad al dia | PHP + Node |
| 1 | 12 a 15 | **Admin enterprise nuevo** + webapp + app | PHP (API publica, correos, jobs) + C# (admin) + Next |
| 2 | 21 | Lo mismo, con el tiempo real sin Node | PHP (API publica) + C# (admin + hub) |
| 3 | 48 | Lo mismo | C# casi todo, PHP solo Data Center y exports |
| 4 | 54 | Lo mismo | Solo C# + Next. Filament, Node, PM2, Horizon y PHP-FPM apagados |

Total realista: **54 semanas**. Optimista: 30 a 34. Lo que separa los dos esta en `migracion-dotnet.md` seccion 5.

## DECISIONES DE KAMILO

Viven en **`docs/decisiones.md`** (D.1 dada 2026-10-21; D.2 a D.6 abiertas).

---

<details>
<summary><b>Fase 0: Preparar en PHP (3 semanas; real 4 dias) — 41/41 HECHA 2026-10-24</b></summary>

## Fase 0: Preparar en PHP (3 semanas; real 4 dias) — 41/41 HECHA 2026-10-24

> Nada de C#. Todo esto es deuda y seguridad que vale aunque la migracion se pause. Al terminar, PHP queda al dia y con
> una red de pruebas de contrato que permite mover pedazos sin romper la webapp ni la app.

### 0.1 Seguridad al dia (2 a 3 dias) — 5/5 (HECHO 2026-10-21, backend dac3429)
- [x] M0.1.1 Subir Laravel 11.51 a 12 (quedo 12.70.1) (`composer.json`, guia oficial de 12: validacion de imagen con SVG, `Str::is`, inspeccion multi esquema). Filament 3.3.49, Sanctum 4.3, Horizon 5.45, Pest 3.8 ya lo soportan **[local]**
- [x] M0.1.2 `composer update` con los 63 avisos (audit en 0; en Windows va con `--ignore-platform-req=ext-pcntl,ext-posix` por Horizon) del audit del 2026-10-21: maatwebsite/excel 3.1.70+ (phpspreadsheet critico), Filament 3.3.52+, Livewire 3.8.3+, guzzle 7.15.2+, commonmark 2.10.2+, jmespath 2.9.1+. Audit en 0 criticos y 0 altos **[local]**
- [x] M0.1.3 Suite Pest completa despues del upgrade: 1.120 pasan, 1 falla conocida (BUG-348, va en M0.2.2). Dos regresiones corregidas en el camino: Laravel 12 tira el token CSRF en `login()` (`TwoFactorLogin::complete` lo conserva) y `{eventId}/mine` se tragaba `GET /events/support/mine` (whereNumber; venia de 85fd4c0) **[local]**
- [x] M0.1.4 BUG-344 (probado: con `config:cache` la suite aborta con mensaje claro): guarda en `tests/TestCase.php` que aborte si la conexion no es sqlite o existe `bootstrap/cache/config.php` **[local]**
- [x] M0.1.5 Llaves reales de Turnstile como paso del runbook (ROADMAP-INFRAESTRUCTURA, "Al abrir el registro publico") y cabeceras de eventos-web revisadas: tiene nosniff, Referrer-Policy, HSTS, X-Frame-Options y CSP; le falta `Permissions-Policy` (debe ser `camera=(self)` por el escaner QR, pendiente de QA en telefono, anotado en el runbook). Cedula como contraseña no aplica: registro sin contraseña **[local]**

### 0.2 Suite confiable (1 dia) — 3/3 (HECHO 2026-10-21, backend 4cedb3e)
- [x] M0.2.1 BUG-347: `Http::preventStrayRequests()` + fake de `/internal/*` del socket y de `/api/internal/invalidate` de la webapp en `tests/TestCase.php`; revisar los tests que asumian la llamada real **[local]**
- [x] M0.2.2 BUG-348: reescribir el test de cache del Data Center para la invalidacion por observer (`DataCenterApiTest.php:76`) **[local]**
- [x] M0.2.3 Suite completa sin socket ni webapp: 1.122 pasan en 153 s (antes 30 min) **[local]**

### 0.3 Contratos de la API (5 a 6 dias) — 10/10 (backend 90e61f8 a c65ae71)
- [x] M0.3.1 Seeder determinista `ContratoSeeder`: reloj congelado 2026-12-06 10:00 Bogota, evento `contrato-2026`, 10 personas con bearer conocido, staff, 5 sesiones, stand, social, gamificacion, encuesta, juego, totem, webhook. Ampliado 2026-10-22: evento con aprobacion (kiko), invitacion/enlace magico/reset con valor fijo, QR fijo por persona, staff de check-in **[local]**
- [x] M0.3.2 **Un solo formato de fecha: ISO 8601 con el offset de la zona de la app, sin fracciones** (`2026-12-06T13:30:00-05:00`). Trait `FechasConOffset` en los 84 modelos + 73 `toISOString` reemplazados (2026-10-21) + `Carbon::serializeUsing` para las fechas armadas a mano en arreglos (2026-10-22). Motivo: la app (`hooks/useAgenda.ts:70`) y la webapp (`AgendaView.tsx:107`) agrupan la agenda con `split('T')[0]`; con UTC las sesiones de la noche cambian de dia. Queda fuera: CSV humanos y filas crudas de `DB::table` del Data Center (el admin nuevo las redefine). Zona por evento: abierta en `decisiones.md` **[local]**
- [x] M0.3.3 **Las 247 rutas congeladas:** 300 pruebas, 330 respuestas en 10 archivos de `tests/Contratos/` (por tema; se reparten por modulo al portar), `./vendor/bin/pest tests/Contratos` ~90 s, dos pasadas identicas. Flujos encadenados (juegos, encuestas, verificacion de asistencia, Data Center). Bugs encontrados: BUG-362 (correo de acceso perdido en la cola), BUG-363 (codigo de activacion expuesto), `generated_at` del ZIP siempre null **[local]**
- [x] M0.3.4 Formas de error: 401 (x2), 403 baneo, 403 aprobacion pendiente, 403 pulse, 404, 405, 422, 429 con `Retry-After` (texto en ingles: `Too Many Attempts.`), paginacion **[local]**
- [x] M0.3.5 Lo que no es JSON: `.ics`, CSV de leads y de sesion (con `Content-Disposition`), ZIP de documentos (la API da 202 "en preparacion" o la URL; el ZIP vive en R2) **[local]**
- [x] M0.3.6 OpenAPI con `dedoc/scramble` 0.13: `docs/api.json`, 247 operaciones, 16 avisos; regenerado 2026-10-22 (Scramble no infiere `null` en `?->toIso8601String()`; mandan los snapshots) **[local]**
- M0.3.7 ~~Resources en los 5 controladores grandes~~: **sacado** (`decisiones.md` 2026-10-22: el contrato ya lo fijan los snapshots)
- [x] M0.3.8 Una sola carpeta de Resources (`V1`). No habia dos `AttendeeResource`: la duplicada era la carpeta **[local]**
- [x] M0.3.9 Pruebas listas para correr contra C#, **probadas contra el propio PHP servido como otro backend** (backend 66c9892): modo contratos (`CONTRATOS_SERVIDOR`, reloj congelado, llamadas afuera simuladas, `POST /__contratos/reiniciar {escenario?}` en 0,4 s), `scripts/servidor-contratos.ps1`, escenarios con nombre (`zip-listo`), azar determinista `Azar` (min + semilla mod rango, cabecera `X-Contrato-Semilla`). Externo: 289 pasan + 11 `solo-php` (Data Center por sesion, hasta M4e) **[local]**
- [x] M0.3.10 **Orden explicito en toda lista** (backend a3bd325): deteccion con `CONTRATO_ORDEN_INVERSO=1` (sqlite invierte toda consulta sin ORDER BY); 15 rutas, 4 causas (speakers por sesion, salas y manifiesto, favoritos, sesiones de la verificacion), orden = el que ya daba MySQL, ningun snapshot cambio. Limite: solo ve listas con 2+ elementos en el seeder **[local]**
- [x] M0.3.11 Cubrir el sorteo con ganador: hoy el flujo jackpot de `JuegosTest` responde 403 "Not eligible" porque en el seeder nadie esta dentro de una sala (`GameService::getEligiblePool` pide estar en la sala de la sesion o viendola en vivo). No es bug de la app: falta un escenario `en-sala` (ana y fabio dentro del auditorio) para congelar `draw` y `spin` con participantes **[local]**
  **Hecho 2026-10-22 (backend c65ae71):** escenario `en-sala` (ana y fabio en el auditorio); flujos jackpot y ruleta con gente, interno 302 (normal e inverso), externo 291 + 11 solo-php. Destapo BUG-364 y BUG-365 del arnes (749d312) y un orden faltante en `draw`. **Hallazgo para M4 (portar juegos):** el sorteo elige entre quienes estan en la sala o viendo en vivo, no entre quienes tocaron "unirse" (el set de Redis `game:{id}:pool` solo cuenta), y solo guarda al ganador como participante; C# copia eso tal cual salvo decision.

### 0.4 Tiempo real listo para cambiar de motor (3 a 4 dias) — 6/6 (HECHO 2026-10-22)
- [x] M0.4.1 Catalogo de eventos del socket: `docs/contratos/eventos-socket.json` con todos los nombres, payload, sala y quien escucha (fuente: `eventos-socket/src/types.ts` + apendice B de `migracion-dotnet.md`); `types.ts` se genera o se valida contra el. Confirma los nombres camelCase del socket en la lista de excepciones JSON (BUENAS-PRACTICAS, Anexo A) **[local]**
  **Hecho 2026-10-22:** eran 61, no 44: 51 del servidor al cliente (17 sin tipar en `types.ts`: juegos, asistencia, agenda, staff de salas, `session:cancelled`, `room:occupancy`) y 10 del cliente. `npm run catalogo` en eventos-socket valida nombres y campos; destapo que `types.ts` tipaba `session:started/ended` con una forma que Laravel no manda (corregido, solo tipos). Anexo A confirmado: 24 nombres (entran los que arma Node; `targetAttendeeId` sale, es interno). El apendice B de `migracion-dotnet.md` tenia errores (Mission Control no escucha `display:*`; si escucha `session:*`). Hallazgos: BUG-366 (Mission Control escucha `game:ended`, que nadie emite) y dos oyentes sin emisor en la webapp (`announcement:new`, `networking:refresh`), anotados para M0.4.6.
- [x] M0.4.2 Capa `realtime` en la webapp: `src/lib/realtime.ts` envuelve `getSocket`, `on`, `off`, `emit`, `joinSession`, `leaveSession`; los 7 hooks de `hooks/streaming/*` y `useGlobalSocket.tsx` dejan de importar `socket.io-client` **[local]**
  **Hecho 2026-10-22 (eventos-web e708bcf):** interfaz `Realtime` (on, off, once, emit, connected, connect; `connect` es el unico evento de ciclo de vida que promete), `getRealtime`/`disposeRealtime`, `joinEvent`/`joinSession`/`leaveSession`. Eran 5 hooks de streaming con socket (no 7: `useStreamViewTracking` no lo usa) + `useGlobalSocket` + el logout. `lib/streaming/socket.ts` borrado. Prueba `tests/lib/realtime.test.ts` falla si otro archivo importa `socket.io-client`. tsc y lint limpios, vitest 681 (679 + 2).
- [x] M0.4.3 Lo mismo en Expo (`lib/socket.ts` ya es singleton; 8 hooks), kiosko (`useAttendance.ts`) y las 5 paginas estaticas de `public/` (mission-control, event-pulse, display, chat-monitor, attendance-check) con un `realtime.js` compartido **[local]**
  **Hecho 2026-10-22:** Expo (eventos-app c461d0c): `lib/socket.ts` pasa a `lib/realtime.ts` con la interfaz `Realtime` de la webapp; eran 6 consumidores (useChat, useDataInvalidation, useQnA, useSessionMode, useWall, encuestas), no 8. Kiosko (eventos-kiosko e6412c2): `src/lib/realtime.ts` con `abrirRealtime`/`joinEvent`. Paginas sueltas (backend 79194af): `public/js/realtime/motor.js` (Socket.IO 4.7.5 local, antes 4 del CDN y 1 copia en event-pulse) + `realtime.js` con `Realtime.abrir/joinEvent/joinSession`. Verificado sin servidor (sintaxis + motor y capa cargados juntos: mismas opciones que antes). **Miradas en vivo 2026-10-22** (Playwright): las 5 cargan el motor local, conectan, entran a sus salas con los nombres del catalogo y reciben del servidor. Destapo BUG-370 (Mission Control sin datos de la API desde el 2FA), ajeno a este cambio. Expo: los 5 errores de `tsc` que salen ya estaban (leaderboard, recap y otros, sin commit desde julio).
- [x] M0.4.4 Catalogo de claves de Redis: clave, dueño (PHP, Node, los dos), formato (serializado PHP, JSON, string, set), TTL. Fuente: 9 archivos con `Redis::` en backend + `src/*.ts` del socket (`docs/contratos/claves-redis.md`) **[local]**
  **Hecho 2026-10-22:** 4 bases (0 PHP con prefijo, 1 cache con doble prefijo, 2 compartida sin prefijo, 15 contratos), 15 claves y un canal en la 2, 5 grupos en la 0, 30 en la 1. Hallazgos: BUG-367 (el Data Center no invalida sus estadisticas: el patron no coincide con el prefijo real); `event:{id}:agenda` y `:branding` solo se borran, nadie las escribe.
- [x] M0.4.5 `InvalidationService::broadcast` publica ademas en el canal Redis `eventos:invalidate` (`{eventId, entity, op}`) sin quitar el POST al socket. Test con `Redis::fake` o cliente real en sqlite **[local]**
  **Hecho 2026-10-22 (backend):** `InvalidationService::publicarEnRedis` por la conexion `socket` (sin prefijo: el canal es exactamente `eventos:invalidate`), ANTES del throttle (el throttle frena a los clientes, no a las caches). `broadcast` publica `op: invalidate`; `sync` publica su operacion (tambien cambia datos; si cae al plan B llega dos veces, borrar dos veces da igual). Prueba con los eventos de comando de Redis (PUBLISH real) + suscriptor real de ioredis. Suite 1.130 verdes.
- [x] M0.4.6 Decision registrada sobre lo que sobra (`decisiones.md`): emitidos sin consumidor (`agenda:updated`, `room:occupancy`, que va a una sala que nadie une) y oyentes sin emisor (`announcement:new` en la webapp; `networking:refresh` NO va aqui: es del bus interno de la webapp, `useGlobalSocket` lo emite con `emitBus` despues de `networking:notify` y `SocialView` lo escucha; error del catalogo corregido 2026-10-22; `game:ended` en Mission Control era BUG-366, resuelto 2026-10-22): quedan en el catalogo como estan o se quitan. Corregido en M0.4.1: `session:started/ended/cancelled` SI tienen consumidor (Mission Control, `app.js:1703`) **[local]**
  **Hecho 2026-10-22 (decision Kamilo):** fuera `agenda:updated` y `room:occupancy` (backend f8c3cde; BUG-369: el escaneo de sala esperaba ese POST). `announcement:new` era un cartel perdido desde julio: reconectado a la lista de la campana (eventos-web dcf4079, BUG-368). `networking:refresh` no sobraba (bus interno de la webapp). Catalogo: 49 + 10, `oyentes_sin_emisor` vacio.

### 0.5 Logica escondida a la vista (3 dias) — 5/5 (HECHO 2026-10-22)
- [x] M0.5.1 `EventSessionObserver::updated` (4 efectos) y `saved` a `App\Services\SessionEffects` con un metodo por efecto; el observer queda en llamadas de una linea. Igual `DocumentObserver`, `EventObserver`, `ModuleObserver`, `AttendeeWebhookObserver` **[local]**
  **Hecho 2026-10-22 (backend 56ef5d6):** `SessionEffects`, `DocumentEffects`, `EventEffects`, `ModuleEffects`, `AttendeeWebhookEffects` en `app/Services`; cada efecto con nombre y el observer como indice. Suite 1.135 igual. Fuera el borrado por patron de modulos, que nunca hizo nada.
- [x] M0.5.2 Las 16 closures de `routes/web.php` (561 lineas) a controladores con nombre: `InternalChatController` (5 rutas), `DisplayController`, `JoinController`, `DataCenterPageController` **[local]**
  **Hecho 2026-10-22 (backend ae22313):** 5 controladores en `app/Http/Controllers/Web` (`OperadorController`, `InternalChatController`, `DeepLinkController`, `AdminDescargasController`, `DataCenterPageController`); `/` pasa a `Route::view`. Mismas rutas, nombres y middlewares (route:list) y mismas respuestas en vivo. Secreto interno con `hash_equals`.
- [x] M0.5.3 Inventario de las 111 acciones con logica de Filament: archivo, que hace, que jobs o correos dispara, que claves de cache toca (`docs/contratos/acciones-admin.md`). Es la lista de lo que el admin nuevo tiene que reproducir **[local]**
  **Hecho 2026-10-22:** `docs/contratos/acciones-admin.md`: 69 acciones con logica propia (no 111) + 108 estandar; 17 despachan jobs, 8 mandan correo, 23 llaman HTTP, 33 tocan cache. 16 hallazgos; 3 verificados a mano y registrados (BUG-372 push privado a todos, BUG-373 ban sin expulsar, BUG-374 eventos de otras organizaciones), el resto por verificar en la auditoria. Ojo: parte de la logica vive en hooks de guardar/crear de 20 archivos, fuera de la tabla.
- [x] M0.5.4 Catalogo de variables de entorno: las 60 de `.env` con dueño y si C# la necesita (`docs/contratos/variables.md`) **[local]**
  **Hecho 2026-10-22:** `docs/contratos/variables.md`: eran 71, no 60. Hallazgos: `APP_KEY` compartida (3 columnas cifradas: 2FA, SMTP por organizacion, sesiones del staff -> M1.3.5 nueva); `APP_QR_SECRET` firma QR y enlaces; 12 variables propias faltan en `.env.example` (secretos incluidos, para la auditoria); 4 sin uso.
- [x] M0.5.5 Cabecera `X-Backend: php` en toda respuesta de Laravel desde ya **[local]**
  **Hecho 2026-10-22 (backend):** middleware global `MarcaBackend`; API, errores, admin y paginas del operador la llevan (comprobado en vivo). Los estaticos de Apache no (no pasan por Laravel). 4 pruebas.

### 0.6 Cerrar lo que quedo a medias en PHP (1 a 2 dias + medir) — 3/3 (M0.6.3 movida a la fase 1)
- [x] M0.6.1 Expo: `tsc` limpio en `main` (BUG-392, BUG-393 con decision de diseño; BUG-394). El recap NO se commitea: archivado en la rama local `archivo/2026-07-recap-haptics-reintentos` (`8176acd`) por decision de Kamilo 2026-10-22 (no funcionaba bien y se veia mal); queda como funcion pendiente de rediseño y reparacion en PENDIENTES §7 **[local]**
  **Hecho 2026-10-24:** `tsc` limpio en la app (BUG-394 ya estaba en `3b845dc`); lab `design/features/app/lab-m061.html`, Kamilo eligio A en los tres con Lux por elevacion: BUG-392 y 393 en app `d863e2f`; BUG-455 (webapp con el evento de la persona y pantalla `/sin-inscripcion`) en webapp `cee7ec6` y `b02d206`, con prueba y QA en Noir y Lux. Vitest 691. Regla 26 (filtro por evento) en BUENAS-PRACTICAS `2d6c9e1`; main del backend avanzado a `6c4abd8`.
- [x] M0.6.2 BUG-359 `mobileNav` en `messages/pt.json`; BUG-358 hidratacion #418 (se espera que caiga con M0.3.2) **[local]**
  **Hecho 2026-10-22 (eventos-web `9dab432`, `2ff14a6`):** BUG-359 (tambien faltaba `mobileShell`) y BUG-358 reproducido con `next dev`: no cayo con M0.3.2, era el formato de la hora (espacio fino U+202F) en 11 componentes. Dos pruebas de guardia (idiomas y horas). Vitest 688. Salio BUG-395 (aviso del script del tema, solo desarrollo).
- [→] M0.6.3 Medir en servidor B3.7, B3.8 y B3.9 con 300 personas (lo hecho el 2026-10-18 y 2026-10-21). Si el servidor sigue en pausa, queda como primera medicion de la fase 1 **[servidor]**
  **Movida a la fase 1 el 2026-10-22 (decision Kamilo): ahora es M1.5.6.** El servidor sigue en pausa. No cuenta en la fase 0.
- [x] M0.6.4 Registrar este roadmap en `docs/living/PENDIENTES.md` como bloque con contadores y marcar lo congelado del admin con "congelado hasta admin .NET" (lista en `pendientes-2026-10.md` §7) **[local]**
  **Hecho 2026-10-22:** la fila con contadores ya estaba en PENDIENTES §0. De los 17 congelados de `pendientes-2026-10.md` §7, 6 no tenian la marca (credencial en §4, landing builder, A/B de correos, torre de control, passkeys, cache de `auth/me` con `data:sync`, Pulse y canario): marcados con su motivo. C1 a C3 (Destacados, puntos manuales, Silent Disco) viven en `decisiones.md` y en M2.8.1 y M2.8.3; nota en PENDIENTES §8.

### 0.8 Seguridad (al cierre de la fase 0, antes de la retro; decision Kamilo 2026-10-22) — 8/8

> Va antes de M0.7.1: C# arranca sobre un contrato limpio y no copia fallas. Lo que es solo de Filament y no es de
> seguridad va a la lista "no repetir" de la fase 2 (M2.8.5), salvo que aparezca un cliente antes de la semana 12.

- [x] M0.8.1 Auditoria de seguridad del backend y del socket (estandar) **[local]**
  **Hecho 2026-10-23:** 54 agentes (presupuesto 50 subido a 55 por Kamilo para cubrir networking), cobertura limpia (3 criticos finales), 65 pistas, todas `needs_validation` (sin sandbox no hubo prueba; dos revisiones independientes cada una). 57 nuevas en BUG-LOG (BUG-396 a BUG-452) y 8 que ya existian con nota. Informe fuera del repo: `~/security-audit-skill/eventos-backend/run-1/REPORT.md`.
- [x] M0.8.2 BUG-373: banear desde el admin expulsa del socket en el momento (mismo servicio que la API) **[local]**
  **Hecho 2026-10-23:** `BanService` para la API y el panel; el socket reparte el ban a todos sus procesos por `socket:config`. Backend `fe92d72`, socket `4a5bb20`. Suite 1.144, contratos 302.
- [x] M0.8.3 BUG-374: el admin solo muestra los eventos de la organizacion del usuario (super_admin ve todo) **[local]**
  **Hecho 2026-10-23:** decision Kamilo: una organizacion por persona; org_admin ve todos los de su organizacion; event_admin y moderador solo los asignados. `AccesoDelStaff` + migracion (`users.organization_id`, `event_staff`). Backend `4a3153f`. Suite 1.150, contratos 302 (1 snapshot cambiado a proposito).
- [x] M0.8.4 Verificar los 13 hallazgos del inventario del admin (`contratos/acciones-admin.md`) y los de M0.5.2 (`/display/polls` publica, `X-Frame-Options: ALLOWALL`); arreglar los de seguridad confirmados **[local]**
  **Hecho 2026-10-23:** arreglados BUG-376, 384, 385, 386, 387, 389, 390 (decisiones de Kamilo en `decisiones.md`; backend `8b99911`); los 9 del inventario que no son de seguridad (375, 377-383, 388) confirmados con prueba y siguen en M2.8.5. Suite 1.157, contratos 302.
- [x] M0.8.5 `.env.example` completo: las 12 variables propias que faltan, secretos sin valor por defecto inseguro, y `security:check` que falle si un secreto quedo en `change_this_secret_in_production` (`contratos/variables.md`) **[local]**
  **Hecho 2026-10-23:** BUG-391 resuelto. `APP_QR_SECRET` sin valor por defecto; `Secretos::qr()` se niega a firmar o verificar (QR, premios, enlaces del operador) si falta o es de ejemplo; `security:check` revisa tambien `WEBAPP_INTERNAL_SECRET`; `.env.example` con los secretos vacios y las variables que faltaban. En local los dos secretos estaban con el valor de ejemplo: rotados (backend y socket). Backend `2155f56`. Suite 1.166, contratos 302.
- [x] M0.8.6 Arreglar las pistas de prioridad A de M0.8.1 (20 nuevas: cruce entre eventos u organizaciones, toma de cuenta o de rol, codigo en el origen del admin), cada una con su prueba que falle primero (BUG-LOG 2026-10-23) **[local]**
  **Hecho 2026-10-23:** los 20 arreglados con su prueba y control: socket (441, 442, 444, 446), API admin por evento (419-422), cruce entre eventos (415, 417, 426: middleware `miembro.evento`), codigo en el admin (407, 412-414, con prueba en navegador), exports privados (408), cuentas y roles (400, 401, 404 con decision de Kamilo, 406). Dos nuevos encontrados y arreglados (BUG-453, 454). Backend `5e68371` a `b15ebf2`, socket `1f148dd`, app `836e532` y `bde3eff`. Suite 1.201, contratos 302 (3 snapshots cambiados a proposito).
  **Antes de arreglar (decision Kamilo 2026-10-23):** confirmar o descartar las 65 con prueba en `tests/Auditoria` (rama `auditoria/validacion`). **Hecho: 65/65 confirmadas, 0 descartadas.** Al arreglar cada una, su prueba pasa a verde y se mueve a `Feature/Security`.
- [x] M0.8.7 Arreglar las pistas de prioridad B de M0.8.1 (21: datos personales y controles dentro del evento), con su prueba **[local]**
  **Hecho 2026-10-23:** los 21 con prueba y control: bloquear y banear (452, 425, 429), cuentas y tokens (399, 443, 428), datos de mas (405, 418, 439, 449, 451), pendientes de aprobacion (430, 440), stands y check-in (437, 431), y 396, 410, 411, 416, 424, 447. Decisiones de Kamilo en 416 y 424. Backend `1e044de` a `112717c`, socket `2f99985`, `dbe2713`, `eab5820`, kiosko `68096db`, `0f46ee3`. Suite 1.234, contratos 302.
- [x] M0.8.8 Arreglar las pistas de prioridad C de M0.8.1 (16: puntos, conteos y endurecimiento), con su prueba; las que sean solo de Filament pueden ir a la lista no repetir (M2.8.5) si Kamilo decide **[local]**
  **Hecho 2026-10-24:** los 16 con prueba y control: puntos y juegos (432-435, 445, 450), limites y codigos (397, 398, 402, 403), datos y contenido (409, 423, 427, 436, 438, 448). Ninguno era solo de Filament. BUG-397 respeta la regla de BUG-363 (el codigo cambia al reenviar). De 438 queda retirar invitaciones pendientes para el admin nuevo. Backend `a8b6172` a `6c4abd8`, socket `431ff50`, `57f3e1e`, `ead3c31`. Suite 1.257, contratos 302, arnes del socket 11/11 en verde. En `tests/Auditoria` solo quedan los 11 del inventario del admin (M2.8.5).

### 0.7 Cierre de la fase — 1/1
- [x] M0.7.1 Retro de la fase 0 (regla 25 de BUENAS-PRACTICAS): estimado contra real, regla que falto, que se corrige del roadmap **[local]**
  **Hecho 2026-10-24. Retro:**
  1. **Estimado contra real:** 3 semanas estimadas (0.1 a 0.6); costo 4 dias (21 al 24 de octubre) y ademas entro el bloque 0.8 completo (auditoria con 65 pistas). En la fase se registraron 99 bugs (BUG-357 a 455): 88 resueltos con prueba; quedan 11 con dueño (9 del admin en M2.8.5, BUG-371 y 395 solo de local).
  2. **Por que no se recalcula la fase 1 con este ritmo:** fue PHP conocido, con la suite y los contratos ya puestos. La fase 1 es C# nuevo y el MSI sin instalar. Se mide la primera semana de C# y con eso se ajustan las fechas.
  3. **Reglas que faltaron:** filtrar por evento de raiz (7 cruces entre eventos en la auditoria): regla 26. Confirmar cada hallazgo con una prueba roja y su control antes de arreglarlo, sin reescribir pruebas viejas que fijan producto (BUG-397 contra BUG-363): regla 27.
  4. **Que se corrige del roadmap:** M1.3.3 porta tambien las piezas nuevas de PHP (evento de la ruta para ban y aprobacion, `miembro.evento`, `audiencia.modulo`); nueva M1.3.6 con el filtro por evento de C# y su prueba de cruce; M1.1.4 deja fuera `tests/Auditoria` (11 rojos a proposito).
  5. **Antes de arrancar la fase 1:** prender el MSI e instalar Docker, Redis y el .NET SDK (con permiso de Kamilo). Todos los repos quedaron en `main` con push. Abierto sin bloquear: sombra de dos capas en Lux (PENDIENTES).

</details>

---

## Fase 1: Cimientos en C# (3 semanas) — 15/33

> C# arranca con lo invisible: lee la misma base, acepta los mismos tokens, responde con el mismo JSON y los mismos
> errores. Sin trafico real. Al terminar, `GET /auth/me` pasa su snapshot contra los dos backends.

### 1.1 Repositorio y esqueleto (2 dias) — 7/7 (HECHO 2026-10-25)
- [x] M1.1.1 Repo `eventos-api-dotnet`, .NET 10 LTS, solucion con `Api`, `Worker`, `Contracts`, `Tests`; `Hub` se agrega en la fase 3 **[local]** — **Hecho 2026-10-25** (`e7a95e0`, GitHub privado): versiones centralizadas, avisos como error, `global.json`, `CLAUDE.md` de la plantilla; compila y prueba en Windows y en el MSI
- [x] M1.1.2 Dockerfile multi-stage y `docker-compose.yml` (solo Linux: MSI y servidor; en Windows `dotnet run` contra Laragon) que levanta C# junto a MySQL y Redis de la maquina **[local]** — **Hecho 2026-10-25** (`f4b4df6`, `be5e875`): una receta para Api y Worker, imagen sin root (~340 MB), red del host para no abrir MySQL ni Redis; en reposo Api 18 MB y Worker 10 MB
- [x] M1.1.3 Sentry, logs estructurados con id de peticion (`X-Request-Id`, cruza con B4.3), `/health` y `/version` con la misma forma que `HealthController` **[local]** — **Hecho 2026-10-25** (`30e93eb`): cuerpo identico a PHP lado a lado; logs JSON con `RequestId`; Sentry apagado sin DSN (falta crear el proyecto aparte de C#); 7 pruebas con control. Sin `InvariantGlobalization` (Windows no reconocia `America/Bogota`)
- [x] M1.1.4 CI: build, tests, y las pruebas de contrato de M0.3 corridas contra PHP (normal y con `CONTRATO_ORDEN_INVERSO=1`) y contra C# con `CONTRATO_BASE_URL` en cada push; las `solo-php` se saltan contra C# (M0.3.9) `tests/Auditoria` queda fuera (11 rojos a proposito, inventario del admin) **[local]** — **Parte A hecha 2026-10-25** (`6745e96`, `6adfe77`): `scripts/verificar.sh` en runner propio en el MSI (sin costo), C# + contratos de PHP en los dos ordenes, verde en 2m19s; BUG-456 resuelto para lograrlo. **Parte B hecha 2026-10-25** (`7d314b1`): contratos de PHP contra MySQL (la verdad, BUG-458 a 461) y orden inverso en SQLite; los de las rutas de C# (`scripts/contratos-csharp.txt`: health y version) corren contra C# en modo contratos. ~7 min en el MSI
- [x ] M1.1.5 Modo contratos en C#, igual al de PHP (`app/Support/ModoContratos.php`): reloj congelado, llamadas afuera simuladas, `POST /__contratos/reiniciar {escenario?}` con los nombres de `ContratoEscenarios`, azar `min + semilla mod rango` con `X-Contrato-Semilla`; activado por variable de entorno y sin registrarse en produccion **[local]** — **Hecho 2026-10-25** (`d5debca`): reinicio copiando la base de referencia que siembra PHP (0,55 s), escenarios identicos a PHP comprobados fila por fila, azar con la formula de PHP, llamadas afuera simuladas; control: con `App__Env=production` el contrato de version falla
- [x ] M1.1.6 Analizador de async en CI (`Microsoft.VisualStudio.Threading.Analyzers`, avisos como error): falla el build con `.Result` o `.Wait()` **[local]** — **Hecho 2026-10-25**: `Microsoft.VisualStudio.Threading.Analyzers` 18.12.1 en `Directory.Build.props`; Redis pasa a un solo `IConnectionMultiplexer` creado al arrancar (VSTHRD011); control: un `.Result` rompe el build (VSTHRD103)
- [x ] M1.1.7 `dotnet list package --vulnerable` en CI (falla si hay altos o criticos) y versiones fijas en los `.csproj` **[local]** — **Hecho 2026-10-25**: `NuGetAudit` (todos los paquetes, nivel alto) rompe la restauracion y `verificar.sh` corre `dotnet list package --vulnerable --include-transitive`; versiones fijas en `Directory.Packages.props`; control: System.Text.Json 8.0.4 da NU1903

### 1.2 Datos (3 dias) — 5/6
- [x] M1.2.1 Scaffold de las 105 tablas con Pomelo; script repetible `scaffold.ps1` que se corre despues de cada migracion Laravel **[local]** — **Hecho 2026-10-25** (`348620e`): con `Microting.EntityFrameworkCore.MySql` 10.0.13, no Pomelo (no tiene version para EF Core 10; prueba de B y C y decision en `decisiones.md`). 114 tablas (no 105): 112 entidades y 2 tablas puente como muchos a muchos, en `src/Contracts/Datos`. `scaffold.ps1` migra con PHP una base `eventos_esquema_pruebas` y genera desde ahi; prueba en MySQL: cada entidad se lee de la base sembrada (control: un mapeo roto falla)
- [x] M1.2.2 Conversor de fechas: columnas `DATETIME` guardadas en hora de Bogota (`APP_TIMEZONE`), leidas como `Unspecified` y convertidas a UTC; serializadas con el formato decidido en M0.3.2 **[local]** — **Hecho 2026-10-25** (`0f2f638`): `ZonaDeLaApp` en `Contracts` (sale `App:Timezone`), conversion en todas las `DateTime` fuera del archivo generado; escribir una fecha que no es UTC es error. En MySQL: Apertura sale `2026-12-06T08:30:00-05:00`, igual al snapshot; escritura en transaccion; control sin conversion: `Unspecified`
- [x] M1.2.3 Los 38 casts `array` como `JsonDocument` o tipos propios; los 69 `boolean` desde `tinyint`; los 3 `encrypted` (2FA) con el descifrado AES-256-CBC + MAC de Laravel portado y probado contra valores reales **[local]** — **Hecho 2026-10-25** (`9e6f43f`, `43c949f`): 38 json a `JsonDocument` (UseMicrosoftJson); 77 `tinyint(1)` a `bool` sin default en el modelo y con `= true` en C# los 41 que en la base valen 1 (control: sin el ajuste un `false` insertado se guardaba `true`). `casts-de-laravel.json` generado: marca 8 `tinyint(1)` que PHP devuelve 0/1 sin cast, 2 json sin cast y 3 `array` sobre texto (lo que sale lo decide el snapshot de cada ruta). **Los 3 `encrypted` van en M1.3.5** (el mismo lector del cifrado). Pendiente al portar escrituras: columnas numericas con default (ej. `rate_limit` 100) siguen con el default de EF: un 0 explicito se guardaria como el default; la prueba cruzada de escrituras (regla 7) lo atrapa
- [x] M1.2.4 Segundo `DbContext` de solo lectura para la replica (`reports`, cruza con B3.4) **[local]** — **Hecho 2026-10-25** (`3aa3075`): `ReportesDb` (mismo modelo, `ConnectionStrings:MySqlReportes`, sin replica lee la principal), guardar es error y sin tracking
- [ ] M1.2.5 Verificar la zona horaria del MySQL de produccion (`SELECT @@global.time_zone, @@session.time_zone`): las 22 columnas con `useCurrent()` toman la hora de la base. Si no es Bogota, medir cuantas filas estan corridas y decidir (`decisiones.md`) **[servidor]**
- [x ] M1.2.6 Suite completa de PHP (1.257 pruebas) contra MySQL, como ya corren los contratos (BUG-458): mismas bases `*_pruebas` y `TruncaSoloLoUsado`; lo que falle es un hallazgo con su BUG **[local]** — **Hecho 2026-10-25**: medido 23 fallos de 1257 en MySQL; resueltos BUG-462 (roles de asistente inexistentes), 463 (datos de prueba invalidos) y 460 (llaves de `meta`). Ahora 1256 en MySQL (1 omitida sin el repo del socket) y 1257 en SQLite; `verificar.sh` corre la suite en MySQL (base `eventos_suite_pruebas`). Backend `d91ed89` a `955cd6f`

### 1.3 Autenticacion compatible (3 dias) — 3/6
- [x] M1.3.1 `AuthenticationHandler` Sanctum: parte `id|secreto`, compara `sha256` con `personal_access_tokens.token`, respeta `expires_at` (7 dias) y `abilities` **[local]** — **Hecho 2026-10-25** (`c11cb3d`, `src/Api/Autenticacion/TokenDeSanctum.cs`): espejo del Guard de Sanctum 4.3 (bearer como `Request::bearerToken`, formato como `isValidBearerToken`, sin `|` busca por el hash del texto entero, vence a los 7 dias de `created_at` y en `expires_at`). **Corregido:** `last_used_at` NO se escribe, PHP lo tiene apagado (`config/sanctum.php`). Sin esquema por defecto: solo autentica en las rutas `ConToken()`, como `auth:sanctum`. 401 `{"message":"Unauthenticated."}` como `bootstrap/app.php`. Pruebas con control en MySQL
- [x] M1.3.2 Rechazo de tokens de usuarios con acceso al panel (`hasPanelAccess`, roles de `Roles::PANEL_ACCESS` desde `model_has_roles`) y borrado del token + ficha `socketauth:t:*` al rechazar **[local]** Excepcion: el token de operador (`App\Support\TokenDeOperador`, BUG-370): `operador:{tipo}`, `evento:{id}`, `sesion:{id}`, solo su lista de rutas, sin borrarlo. — **Hecho 2026-10-25** (`c11cb3d`, `TokenDeOperador.cs`): cuenta desactivada no entra (BUG-399); staff: 401, se borra el token y su ficha `socketauth:t:*` (base 2 de Redis); operador: rutas por plantilla de Laravel (los parametros de C# llevan el mismo nombre que en PHP), evento y sesion de ruta, query y cuerpo, y los ids que nombra (BUG-417). Seis controles: cada regla rota hace fallar su prueba. Dos diferencias con PHP en `decisiones.md` (abiertas)
- [ ] M1.3.3 Claims de rol de plataforma (spatie) y rol de asistente (`attendees.role`) en el principal; `CheckBan` y `CheckApproval` como filtros con las mismas respuestas 403, sobre el evento de la ruta (`EventoDeLaPeticion`, BUG-425), mas `miembro.evento` (403 `NOT_IN_EVENT`, `/mine` afuera) y `audiencia.modulo` **[local]** — Parte hecha en M1.3.4: `CheckBan` como filtro `ConBan()` sobre `EventoDeLaPeticion`. Falta: claims de rol, `CheckApproval`, `miembro.evento`, `audiencia.modulo`
- [x] M1.3.4 `GET /auth/me` identico: snapshot de M0.3 pasa contra PHP y contra C# **[local]** — **Hecho 2026-10-25** (C# `a006cd2`, backend `28f9daa`): los contratos `GET auth/me` (ana y carla) pasan contra C# y estan en `contratos-csharp.txt`; control: un `sponsor_id` roto falla. Trae de M1.3.3 `check.ban` (`ConBan()`) con `EventoDeLaPeticion` (BUG-425); token de operador con rol `display` y `operador` (BUG-442). En PHP se agrego desempate por `id` a `asistenteDe`, `EventoDeLaPeticion::asistente` y `activeBan` (regla 9)
- [ ] M1.3.5 Lector del cifrado de Laravel (`APP_KEY`, AES-256-CBC + HMAC, carga JSON base64) en `Contracts`: `users.two_factor_secret`, `organization_email_settings.password`, `staff_sessions.session_id`. Prueba contra valores cifrados por PHP; escribir en el mismo formato mientras conviven (`contratos/variables.md`, M0.5.4) **[local]**
- [ ] M1.3.6 Filtro por evento de raiz (regla 26): filtro global de EF Core por `EventId` en las entidades del evento y membresia exigida en la ruta; un id de otro evento es 404 y no escribe. Prueba de cruce por modulo: alguien de otro evento no ve ni toca nada (retro M0.7.1) **[local]**

### 1.4 Forma de las respuestas y limites (3 dias) — 0/4
- [ ] M1.4.1 `JsonNamingPolicy.SnakeCaseLower` global, nulos incluidos, campos condicionales omitidos (no `null`) donde el Resource usa `when()`; excepciones en lista cerrada `Contracts/ExcepcionesJson.cs` (los 24 del Anexo A de BUENAS-PRACTICAS, confirmados en M0.4.1; nada entra sin decision) **[local]**
- [ ] M1.4.2 Formas de error de Laravel en vez de `ProblemDetails`: 401 `Unauthenticated.`, 422 `{message, errors}` con los textos de `lang/es`, 429 con `Retry-After`; snapshots de M0.3.4 pasan **[local]**
- [ ] M1.4.3 Limitador de tasa en Redis (INCR + EXPIRE o ventana deslizante en Lua) con las 12 claves de `AppServiceProvider::configureRateLimiting` (por correo, token, usuario, IP) y `TrustProxies` equivalente (`ForwardedHeaders` con la misma lista) **[local]**
- [ ] M1.4.4 Cache con prefijo `dotnet:` sobre Redis y suscriptor del canal `eventos:invalidate` (M0.4.5) que borra lo suyo **[local]**

### 1.5 Proxy y primera medicion (2 dias) — 0/9
- [ ] M1.5.1 Nginx (o YARP) con tabla de rutas por ruta exacta: todo a PHP salvo `/api/v1/health`, `/version` y `/auth/me` a C#. Cabecera `X-Backend`. Script `cruzar.ps1 <ruta>` que cambia el dueño de una ruta **[local]**
- [ ] M1.5.2 **La medicion que faltaba:** `e3-agenda-storm.js` contra `/auth/me` y `/events/by-slug` servidos por PHP y por C# en la misma maquina. Es el numero real de "cuanto menos consume" **[local+medir]**
- [ ] M1.5.3 `descruzar.ps1 <ruta>` (devuelve la ruta a PHP en un despliegue) y `mysqldump` automatico dentro de `cruzar.ps1` para rutas que escriben; los dos probados antes del primer cruce real **[local]**
- [ ] M1.5.4 Panel por ruta y por `X-Backend` (p95, errores, req/s) desde los logs de M1.1.3; despues de cada cruce, la ruta contra su propio numero en PHP **[local]**
- [ ] M1.5.5 `medir-cruce.ps1 <ruta>`: k6 de agosto contra PHP y contra C# en el MSI, deja el numero para anotar en el roadmap. Criterio de cruce completo (regla 20): snapshot igual, orden explicito, prueba cruzada si escribe, medicion anotada, `descruzar.ps1` probado. En el servidor real se mide por hito **[local+medir]**
- [ ] M1.5.6 Medir en el servidor real, con 300 personas, lo hecho en octubre para bajar pedidos por pantalla: agenda partida (B3.7), Social por pestañas (B3.8) y "lo mio" en un pedido (B3.9). Venia de M0.6.3 (servidor en pausa). Es la primera medicion de la fase 1 en servidor; pedirle a Kamilo que lo prenda **[servidor]**
- [ ] M1.5.7 OpenAPI de C# generado desde el codigo (`Microsoft.AspNetCore.OpenApi`) con interfaz navegable (Scalar, solo fuera de produccion); en CI, cada ruta cruzada (segun `contratos/rutas-duenos.md`) se compara contra su operacion en `eventos-backend/docs/api.json` y falla si cambian parametros, estados o forma (regla 28) **[local]**
- [ ] M1.5.8 Tabla de dueños de ruta `docs/contratos/rutas-duenos.md` (ruta exacta, metodo, PHP o C#, fecha del cruce, numero medido), generada por `cruzar.ps1` y `descruzar.ps1` desde la tabla del proxy; nunca editada a mano (regla 28) **[local]**
- [ ] M1.5.9 `docs/ARQUITECTURA.md` v1: estado actual de la convivencia (proxy, quien atiende que, base y Redis compartidos, colas, tiempo real en Node), enlazando a `rutas-duenos.md` en vez de copiarla. Se actualiza en la retro de cada fase (regla 25) **[local]**

### 1.6 Cierre de la fase — 0/1
- [ ] M1.6.1 Retro de la fase 1 (incluye actualizar `ARQUITECTURA.md`, regla 25) **[local]**

---

<details>
<summary><b>Fase 2: Admin nuevo (hito 1, 10 a 12 semanas) — 0/46</b></summary>

## Fase 2: Admin nuevo (hito 1, 10 a 12 semanas) — 0/46

> Next sirve la interfaz; C# sirve la API admin y reproduce los efectos secundarios que hoy hacen los observers de
> Laravel. Filament se apaga modulo por modulo. La API publica, Node y los correos siguen en PHP y no se enteran.
> Alcance del hito 1 segun D.3: 2.1 a 2.5, 2.7, 2.8 y 2.10. El resto (2.6, 2.9) es M2b y puede ir en paralelo con las fases 3 y 4.

### 2.1 Diseño del admin (1 semana) — 0/4
- [ ] M2.1.1 Mapa de pantallas desde el inventario de acciones (M0.5.3): que modulos entran al hito 1 (D.3), que se demuele (paginas custom, `EventOnboardingResource`, banners) **[local]**
- [ ] M2.1.2 Labs por letra del admin en Next (2 a 3 direcciones, Lumina Noir/Lux, linea enterprise, cero Filament). Base: `design/` y el SPA del Data Center que ya existe en Vite **[local]**
- [ ] M2.1.3 Decision de donde vive: `eventos-web` bajo `/admin` con su propio layout, o repo aparte. Recomendado: mismo repo, layout propio, para compartir tokens y auth **[local]**
- [ ] M2.1.4 Contrato de la API admin: OpenAPI nuevo (no hereda rutas de Filament, que no tiene API) con DTOs explicitos desde el dia uno **[local]**

### 2.2 Entrar al admin (1 semana) — 0/6
- [ ] M2.2.1 Login del staff en C# con contraseña bcrypt `$2y$` + TOTP (Otp.NET) + codigos de recuperacion; bloqueo por fallos (`StaffLockout`), dispositivos de confianza (`trusted_devices`), sesiones abiertas (`staff_sessions`), bitacora (`staff_security_events`) **[local]**
- [ ] M2.2.2 Token de staff para la API admin: Sanctum con ability `admin` en la misma tabla (Laravel no lo emite; C# si) o cookie propia. Recomendado: bearer con ability, para que el Data Center tambien lo use **[local]**
- [ ] M2.2.3 Contexto de evento (hoy en sesion de Filament, `SetFilamentEventContext`) pasa a cabecera `X-Event-Id` validada por rol y organizacion **[local]**
- [ ] M2.2.4 Roles y permisos de plataforma (`Roles.php`: super_admin, org_admin, event_admin, moderator, staff_checkin) como policies de C# **[local]**
- [ ] M2.2.6 Rol **Organizador** (el cliente): ve Data Center, estadisticas y datos del evento, sin editar nada; el staff edita. Rol de plataforma nuevo en las policies de M2.2.4, con lab de pantallas antes de codear (decision Kamilo 2026-10-25; hoy no existe: BUG-462) **[local]**
- [ ] M2.2.5 Correos del staff (2FA activado, cuenta bloqueada, reset) los sigue mandando PHP: C# inserta en una tabla `outbox` que `SendEmailJob` drena. Es el puente de correo hasta la fase 4c **[local]**

### 2.3 Contenido del evento (2 semanas) — 0/8
- [ ] M2.3.1 Eventos y organizaciones: CRUD, wizard de primer ingreso (`PrimerIngresoWizardTest` como referencia), estado `is_active`, `pulse_token`, `registration_*` **[local]**
- [ ] M2.3.2 Agenda: sesiones (3 tiempos original/ajustado/real), tipos, tracks, salas, speakers por sesion, delay y cancelacion con los mismos efectos que `SessionConfigController` y `EventSessionObserver` **[local]**
- [ ] M2.3.3 Speakers, sponsors (tiers, servicios, stand, equipo), highlights, slides de login, FAQ, documentos (con `RegenerateDocumentsZipJob` en Hangfire), paginas custom (o demolidas) **[local]**
- [ ] M2.3.4 Branding (keyvisual por superficie y hero modo texto nacen aqui, PENDIENTES §8) y panel de modulos (`ModuleCatalog`, templates, por rol) **[local]**
- [ ] M2.3.5 Uploads a R2 con AWSSDK.S3 y el mismo esquema de URLs que `StorageService` (fallback a disco publico) **[local]**
- [ ] M2.3.6 Importacion CSV de asistentes (`AttendeesImport`) con ClosedXML/CsvHelper y correo de "importacion terminada" por el outbox **[local]**
- [ ] M2.3.7 Efectos secundarios de contenido: al guardar, borrar las claves de cache de PHP (`event:{id}:agenda`, `:speakers`, `:documents`, `:branding`, `:modules:{rol}`, `login-slides:event:{id}`, `dc:stats:*`), publicar en `eventos:invalidate` y hacer POST a Node `/internal/data/invalidate` y `/internal/data/sync` con el mismo secreto. Fuente: observers de `migracion-dotnet.md` §1.4 **[local]**
- [ ] M2.3.8 Snapshot de cada escritura: tras guardar desde C#, los endpoints publicos de PHP devuelven lo mismo que si se hubiera guardado desde Filament (prueba cruzada automatica) **[local]**

### 2.4 Personas (1,5 semanas) — 0/6
- [ ] M2.4.1 Asistentes: lista, perfil, tags, rol de asistente, aprobacion (`registration_approved_at`), check-in manual, campos de registro dinamicos y sus valores **[local]**
- [ ] M2.4.2 Baneos con `ban:enforced` al socket, `SocketAuthCache::forgetUser` equivalente (borrar `socketauth:u:*` y `:t:*`) y `ExpireAttendeeBansJob` en Hangfire **[local]**
- [ ] M2.4.3 Webhooks de asistente (`attendee.registered/approved/checked_in/updated/cancelled`) como eventos de dominio en C# con la misma firma HMAC y reintentos; endpoints y llaves de webhook (CRUD + log) **[local]**
- [ ] M2.4.4 Staff del panel: usuarios, roles, invitaciones (`StaffInvitationController`), reset de 2FA por super_admin **[local]**
- [ ] M2.4.5 Golden tickets, premios, canjes (`PrizeRedemptions`) y codigos de acceso (`AccessCode`) **[local]**
- [ ] M2.4.6 Soporte: tickets de `support_requests` con respuesta **[local]**

### 2.5 Comunicacion (1 semana) — 0/4
- [ ] M2.5.1 Anuncios con targeting y "lleva a" (deeplink a encuestas), con `data:sync` al socket y cache por rol borrada **[local]**
- [ ] M2.5.2 Notificaciones programadas y envio inmediato (push Expo + Web Push): C# escribe `scheduled_notifications` y `push_notification_logs`; el despacho lo sigue haciendo PHP hasta la fase 4c **[local]**
- [ ] M2.5.3 Plantillas de correo por evento (16 tipos de `EmailCatalog`, fork por evento, vista previa con `EmailLayout` portado) y SMTP por organizacion **[local]**
- [ ] M2.5.4 Log de correos (`email_logs`) y de push para leer **[local]**

### 2.6 Operacion en vivo desde el admin (M2b, 1,5 semanas) — 0/4
- [ ] M2.6.1 Encuestas: CRUD con preguntas y opciones, importar plantilla, moderacion de votos. Lanzar y cerrar siguen siendo de la API admin publica (PHP) hasta la fase 4d **[local]**
- [ ] M2.6.2 Juegos en vivo: configurar trivia, ruleta y jackpot (`LiveGame`); resultados (`LiveGameResults`) **[local]**
- [ ] M2.6.3 Ajustes por evento: gamificacion (puntos por accion, topes diarios), recordatorios, chat (palabras bloqueadas, modo lento, con POST a Node `/internal/chat/invalidate-config`), limites de tasa, registro **[local]**
- [ ] M2.6.4 Recap: configuracion y regeneracion (`GenerateRecapsForEventJob` queda en PHP hasta la fase 4e; C# solo lo encola via outbox) **[local]**

### 2.7 Apagar Filament modulo por modulo (continuo) — 0/4
- [ ] M2.7.1 Por cada modulo que el admin nuevo cubre, el resource de Filament se oculta del panel (no se borra) y se anota en el inventario de M0.5.3 **[local]**
- [ ] M2.7.2 El SPA del Data Center (`public/data-center`) pasa a bearer del staff (M2.2.2) y se enlaza desde el admin nuevo; deja de depender de la sesion web de Laravel **[local]** (D.8: hasta aqui sus 19 rutas siguen en PHP con cookie; despues quedan listas para M4e)
- [ ] M2.7.3 Mission Control, Event Pulse, display, chat-monitor y attendance-check se enlazan desde el admin nuevo con el mismo token; su unificacion (PENDIENTES §11) se hace aqui o se absorben como paginas del admin **[local]**
- [ ] M2.7.4 Cuando el ultimo modulo del hito 1 este cubierto: Filament solo queda para lo de M2b, con un aviso en el panel **[local]**

### 2.8 Lo congelado que nace aqui (dentro de los bloques de arriba) — 0/5
- [ ] M2.8.1 "Otorgar puntos manuales" (motor existe en `PointsService`, sin UI) **[local]**
- [ ] M2.8.2 Credencial con etiqueta visible (VIP, Prensa): marcar tags que salen en la escarapela **[local]**
- [ ] M2.8.3 Renombres y limpiezas: Highlights → Destacados solo en la interfaz (la entidad `highlights` es contrato con 8 clientes del socket), borrar `EventOnboardingResource`, toggle de Silent Disco segun `decisiones.md` **[local]**
- [ ] M2.8.4 Manual del organizador se reescribe sobre el admin nuevo (ROADMAP-MANUAL M1 a M7 cambian de pantallas) **[local]**
- [ ] M2.8.5 Lista "no repetir" del inventario del admin (`contratos/acciones-admin.md`, seccion Hallazgos): un solo camino por accion con los mismos efectos que la API (push con el mismo publico, aprobar fotos y muro con puntos y avisos iguales, marcar premio reclamado con `RewardService::confirm`, encuestas que avisan por socket, asignar speakers que avisa); nada de logica en hooks de guardar; permisos por accion **[local]**

### 2.9 Torre de control minima (M2b, 1 semana, segun D.4) — 0/3
- [ ] M2.9.1 Pagina solo super_admin en el admin nuevo: peticiones por segundo y p95 por ruta (desde los logs de M1.1.3), errores del ultimo minuto, colas de Hangfire y Horizon (leyendo Redis), estado de cada nodo del LB **[local]**
- [ ] M2.9.2 Conectados por nodo: hasta la fase 3 los da Node por `/internal/session-metrics`; despues los da el hub **[local]**
- [ ] M2.9.3 Alertas por umbral (B4.4) salen de aqui a BetterStack/UptimeRobot **[servidor]**

### 2.10 Cierre de la fase (hito 1) — 0/2
- [ ] M2.10.1 Medicion del hito 1 en el servidor real (admin nuevo + API PHP) con los k6 de agosto; numero anotado aqui **[servidor]**
- [ ] M2.10.2 Retro de la fase 2 **[local]**

</details>

---

<details>
<summary><b>Fase 3: Tiempo real en SignalR (6 semanas) — 0/23</b></summary>

## Fase 3: Tiempo real en SignalR (6 semanas) — 0/23

> El hub vive en el mismo proceso que la API admin (o en `Hub`, mismo repo). Mantiene una fachada HTTP identica a los
> 17 endpoints internos de Node para que PHP siga avisando sin enterarse. Los 8 clientes cambian un archivo cada uno.
> Doble emision solo las semanas de transicion y sin app en tiendas (D.5).

### 3.1 Hub y entrada (1,5 semanas) — 0/6
- [ ] M3.1.1 Hub SignalR con `Microsoft.AspNetCore.SignalR.StackExchangeRedis`, solo WebSockets, `skipNegotiation` exigido a los clientes (sin afinidad, como hoy) **[local]**
- [ ] M3.1.2 Autenticacion del handshake igual que `auth.ts`: token en `access_token` o cabecera; `ep_` valida contra `pulse_token`; Sanctum lee la ficha `socketauth:t:{sha256}` en Redis (TTL deslizante 15 min, tope 24 h), si no esta valida contra la tabla y escribe la ficha con el mismo JSON **[local]**
- [ ] M3.1.3 Estado de conexiones en Redis, no en memoria: maximo 5 por usuario, mapa asistente → conexiones (para `emit-to-user` y `networking:notify`), salas de cada conexion (para exigir `join:session` antes de `chat:send`) **[local]**
- [ ] M3.1.4 Salas con los mismos nombres (`event:{id}`, `pulse:{id}`, `session:{id}`, `chat:session:{id}`, `display:session:{id}`) como grupos; `join:event` valida el evento del token; `join:session` valida acceso con cache 5 min fail-closed **[local]**
- [ ] M3.1.5 Rate limits `rl:chat`, `rl:emoji`, `rl:join:session` con las mismas claves y ventanas (`rateLimit.ts`) **[local]**
- [ ] M3.1.6 CORS fail-closed con la lista de `ALLOWED_ORIGINS` **[local]**

### 3.2 Chat, display y audiencia (1,5 semanas) — 0/6
- [ ] M3.2.1 Chat: historial en `chat:history:session:{id}` (20), anclado con TTL 24 h, modo lento por sesion y por evento, palabras bloqueadas (cache 5 min invalidada por `socket:config` o por el canal propio), pausa, throttle 50 ms por sala, emojis permitidos, borrado y anclado solo admin (`chat.ts`) **[local]**
- [ ] M3.2.2 Persistencia del chat directa a MySQL desde C# por lotes de 200 ms (muere `/internal/chat/message` y `messages-batch` de PHP); contador `chat:count:session:{id}` **[local]**
- [ ] M3.2.3 Asistencia por sesion: `session:{id}:viewers/joined/left` igual, y `FlushSessionAttendanceJob` pasa a Hangfire en el mismo despliegue (regla 4) **[local]**
- [ ] M3.2.4 Audiencia con debounce 500 ms a `session:{id}` y `pulse:{id}` (excluye staff), `session:metrics`, `pulse:active_users` cada 10 s calculado desde Redis **[local]**
- [ ] M3.2.5 Display: `display:project` y `display:stop` con estado en `display:active:session:{id}` (TTL 4 h) **[local]**
- [ ] M3.2.6 Propagacion de configuracion entre instancias por el backplane (reemplaza el canal `socket:config` de Node) **[local]**

### 3.3 Fachada para PHP (1 semana) — 0/3
- [ ] M3.3.1 Los 17 endpoints `/internal/*` de Node reimplementados como minimal API con el mismo `X-Internal-Secret` y los mismos payloads, llamando a `IHubContext` (lista en `migracion-dotnet.md` §1.10) **[local]**
- [ ] M3.3.2 `SOCKET_SERVER_URL` de PHP apunta al hub; `InvalidationService`, `GameService::broadcast`, `PollController`, `CheckinService`, observers y jobs no cambian **[local]**
- [ ] M3.3.3 Prueba cruzada: cada accion de PHP que emite (los snapshots de M0.3 mas un cliente SignalR de prueba) llega con el payload del catalogo **[local]**

### 3.4 Clientes (1 semana) — 0/4
- [ ] M3.4.1 `realtime.ts` de la webapp con `@microsoft/signalr` detras de la misma interfaz de M0.4.2; `/api/auth/socket-token` igual **[local]**
- [ ] M3.4.2 Expo con `@microsoft/signalr` (WebSockets, `skipNegotiation`), reconexion con re-join de evento y sesiones (ya existe el ref-count), probado en segundo plano en Android e iOS **[local]**
- [ ] M3.4.3 Kiosko y las 5 paginas estaticas con el `realtime.js` compartido en version SignalR **[local]**
- [ ] M3.4.4 Bandera `REALTIME=socketio|signalr` por cliente para volver atras en un despliegue **[local]**

### 3.5 Transicion y apagado (1 semana) — 0/3
- [ ] M3.5.1 Doble emision: PHP avisa a Node y al hub a la vez (dos URLs en `SOCKET_SERVER_URL`) mientras los clientes cambian **[local+medir]**
- [ ] M3.5.2 Medir con `e4-chat-magistral.js` adaptado a SignalR: 1.000 en una sala, 40 mensajes por segundo, mismo criterio que agosto **[servidor]**
- [ ] M3.5.3 Apagar Node y PM2; borrar `eventos-socket` del despliegue; `tests/load` actualizados **[servidor]**

### 3.6 Cierre de la fase (hito 2) — 0/1
- [ ] M3.6.1 Retro de la fase 3 (la medicion del hito es M3.5.2) **[local]**

</details>

---

<details>
<summary><b>Fase 4: API publica, modulo por modulo (27 semanas) — 0/48</b></summary>

## Fase 4: API publica, modulo por modulo (27 semanas) — 0/48

> Cada modulo sigue el mismo ciclo: portar servicios con nombre, pasar snapshots, mover sus jobs y tareas a Hangfire,
> cruzar las rutas con `cruzar.ps1` cumpliendo el criterio de cruce (M1.5.5), medir en el MSI, quitar el codigo PHP una
> semana despues si no hubo vuelta atras.
> Orden: lo que no escribe primero; auth despues de tener contratos de todo lo que emite tokens; operacion del evento
> al final porque es lo mas complejo; Data Center de ultimo porque es lo mas aislado.

### 4a. Lectura publica y contenido (3 semanas) — 0/6
- [ ] M4a.1 Publicos pre-login (12): by-slug, landing, login-slides, presets, campos de registro, onboarding GET; cache propia con prefijo `dotnet:` e invalidacion por el canal **[local]**
- [ ] M4a.2 Contenido (31): agenda (compartida + personal, `.ics`), speakers, sponsors (shared + mine), documentos y ZIP, FAQ, paginas, highlights, anuncios por rol, tracks, branding, modulos, happening-now **[local]**
- [ ] M4a.3 `GET /events/{id}/mine?piezas=` (`LoMioService`, 6 piezas con estado por pieza) **[local]**
- [ ] M4a.4 Aviso a la webapp (`POST /api/internal/invalidate` con `WEBAPP_INTERNAL_SECRET`) para las entidades del marco **[local]**
- [ ] M4a.5 Cruce de rutas tras snapshots verdes; medir `e3-agenda-storm.js` contra C# **[local+medir]**
- [ ] M4a.6 Quitar los controladores PHP del modulo una semana despues **[local]**

### 4b. Perfil, social y gamificacion (5 semanas) — 0/9
- [ ] M4b.1 `/me/*` (20): perfil, foto a R2, campos de registro, onboarding data, cambio de correo (por el outbox), intereses **[local]**
- [ ] M4b.2 Muro, fotos, historias, calificaciones de sesion y speaker, preguntas con moderacion y upvote; cada escritura emite por `IHubContext` lo que hoy va por `/internal/wall/broadcast` y `/question/broadcast` **[local]**
- [ ] M4b.3 `PointsService` completo: config por evento y rol, dedupe por referencia, tope diario, `broadcastToAttendee('gamification')` y `leaderboard`; `LikesMilestoneService` **[local]**
- [ ] M4b.4 Encuestas: votar con transaccion, `activePoll`, `surveys`, encuesta post evento; `poll:*` por el hub **[local]**
- [ ] M4b.5 Networking (11): directorio con bloqueos, solicitudes con `networking:notify` dirigido y correo por outbox, sugeridos por intereses **[local]**
- [ ] M4b.6 Premios y pasaporte: canje con transaccion y lock (RedLock), token HMAC de canje, `ExpireRedemptionsJob` en Hangfire, `my-prizes`, `my-redemptions`, sellos **[local]**
- [ ] M4b.7 Juegos a demanda por QR (B.2) y triple estado de la ficha de sesion (B.4) nacen aqui con DTO desde el dia uno (PENDIENTES §5) **[local]**
- [ ] M4b.8 Cruce de rutas tras snapshots; medir `e5-e6-fotos-y-control.js` (subidas masivas a R2) **[local+medir]**
- [ ] M4b.9 Quitar PHP del modulo **[local]**

### 4c. Autenticacion, registro y correo (5 semanas) — 0/10
- [ ] M4c.1 Login con contraseña: bcrypt, conteo de fallos por cuenta (`auth.puerta`), limitadores `login` y `entry_check`, `check-email` y `verify-identity` con las mismas respuestas (`AuthController` 587 lineas, `AuthService` 284) **[local]**
- [ ] M4c.2 Emision de tokens: misma tabla, mismo formato `id|secreto`, `expires_at` 7 dias, ficha `socketauth:t:*` con el mismo JSON, invitaciones de stand enlazadas al activar **[local]**
- [ ] M4c.3 Magic link (SHA-256, 15 min, un solo uso), `refresh` rotando, `logout` revocando ficha, reset y verificacion de correo, registro con invitacion (`activate`) **[local]**
- [ ] M4c.4 Registro publico de la landing y el widget: Turnstile, cupo y codigo con lock, limitador `public_register`, handoff de un solo uso (`reg-handoff:*`), un solo correo `welcome_access` **[local]**
- [ ] M4c.5 Correo en C#: MailKit, 20 mailables con plantillas por evento y `{{placeholders}}`, `EmailLayout`, SMTP por organizacion, dos colas (`mail-priority`, `mail`) en Hangfire con limitador por segundo compartido, `retryUntil`, `email_logs`. El outbox de M2.2.5 se drena desde aqui y PHP deja de mandar correo **[local]**
- [ ] M4c.6 Push: Expo (`exp.host`) con recibos cada 5 min, Web Push con VAPID, preferencias por tipo, push silencioso al cambiar modulos **[local]**
- [ ] M4c.7 Recordatorios y notificaciones programadas: `DispatchScheduledNotifications` (1 min), `SendAgendaReminders` (1 min), `SendSessionReminders` (5 min), `SendEventReminders` (08:00), `SendSessionChangedNotification`; cada una se quita de `routes/console.php` al pasar **[local]**
- [ ] M4c.8 Web Push subscription y Expo token (3 + 1 rutas) **[local]**
- [ ] M4c.9 Cruce de rutas; medir `entrar-por-la-puerta.js` y `e1-puertas-abiertas.js` (ola de login, 1.000 detras de una IP) **[local+medir]**
- [ ] M4c.10 Quitar PHP del modulo **[local]**

### 4d. Operacion del evento (8 semanas) — 0/12
- [ ] M4d.1 Check-in de evento: QR estatico HMAC (`APP_QR_SECRET`, `attendee|event|timestamp`) y dinamico por ventana recortado a 32; `processCheckin` en transaccion; `checkin:update` al kiosko; `GET /me/qr`, `attendance` **[local]**
- [ ] M4d.2 Salas y totems: `RoomCheckinService::processScan` (estado en Redis con fallback a DB, toggle, debounce por evento, lock no bloqueante, auto-salida de otra sala, transaccion), totem autenticado por token cacheado, `ping`, `manifest`, `scan/batch` (4 rutas que hoy viven en `web.php` sin CSRF) **[local]**
- [ ] M4d.3 Staff de puerta (10): asignaciones con aceptacion, scan y scan-batch con cache de asignacion, salas; verificaciones de asistencia (7) con `attendance:check:update` **[local]**
- [ ] M4d.4 Auto-checkouts: `AutoCheckoutEndOfDay` (23:30), `SmartAutoCheckout` (15 min), `RoomAttendanceCalculator`, ocupacion **[local]**
- [ ] M4d.5 Stand y leads (23): equipo con invitaciones por token, transferencia, resolver QR, estadisticas, leads con historial de ediciones y export CSV, `staff:*` dirigidos, correos por el outbox **[local]**
- [ ] M4d.6 Juegos en vivo (14): pool de elegibles en Redis, cooldown por sesion, `weightedRandom`, trivia por rondas, ruleta, jackpot, premios por job, auto post en el muro, anuncio al ganador, export; B3.3 (limitar difusiones + cachear pool) se hace aqui **[local]**
- [ ] M4d.7 Admin API en vivo (Mission Control): `SessionConfigController` (start, end, cancel, delay con `adjusted_end_at`, live-config con `session:config_updated`), baneos, notificaciones, uploads, borrar chat **[local]**
- [ ] M4d.8 Event Pulse (9): `pulse_token`, 8 agregaciones, `pulse/validate` para el hub **[local]**
- [ ] M4d.9 Webhooks entrantes (2) con `X-Webhook-Key` y lote en transaccion; salientes ya en C# desde M2.4.3 **[local]**
- [ ] M4d.10 Soporte, tracking, `stories:cleanup`, `ExpireAttendeeBans`, `webhook:prune-logs`, poda de `staff_security_events` **[local]**
- [ ] M4d.11 Cruce de rutas; medir `e7-se-cae-algo.js` (caidas con carga) y el kiosko < 100 ms **[servidor]**
- [ ] M4d.12 Quitar PHP del modulo **[local]**

### 4e. Data Center, exportaciones y recap (6 semanas) — 0/9
- [ ] M4e.1 Los 19 endpoints del Data Center sobre el `DbContext` de replica: stats con cache `dc:stats`, objetivos, embeds por token UUID, notificaciones **[local]**
- [ ] M4e.2 `ExportService` (722 lineas) y los 50 jobs de exportacion: CSV con las reglas de comillas de `fputcsv` (diff automatico contra archivos de PHP), XLSX con ClosedXML, ZIP maestro en streaming, correo al terminar, subida a R2 **[local]**
- [ ] M4e.3 Worker Hangfire separado para `exports`, `documents` y `pdf` (reemplaza `docker/Dockerfile.worker`) **[local]**
- [ ] M4e.4 Exportaciones programadas (`dc_scheduled_exports`, cada 5 min, recalculo de `next_run_at`) y `exports:cleanup` **[local]**
- [ ] M4e.5 Recap: `RecapService`, imagen con Playwright (aislado en el worker, 300 MB de imagen Docker), verificacion `/r/{code}` con HMAC de `APP_KEY`, correo "recap listo", `GenerateRecapsForEvent` al terminar el evento **[local]**
- [ ] M4e.6 Documentos ZIP por evento (`RegenerateDocumentsZipJob`, unico) **[local]**
- [ ] M4e.7 Paginas servidas por PHP que quedan: `/display/*`, `/monitor/{id}`, `/join/{token}`, `/join-team/{token}`, `/password/reset/{token}`, `/auth/verify-email/*`, `/dc-embed/{token}` pasan a Next o a C# (son 10) **[local]**
- [ ] M4e.8 Cruce de rutas; medir export aislado con carga (D.4.4 del plan viejo) **[servidor]**
- [ ] M4e.9 Quitar PHP del modulo **[local]**

### 4f. Cierre de la fase (hito 3) — 0/2
- [ ] M4f.1 Medicion del hito 3 en el servidor real (C# casi todo) con los k6 de agosto; numero anotado aqui **[servidor]**
- [ ] M4f.2 Retro de la fase 4 **[local]**

</details>

---

<details>
<summary><b>Fase 5: Apagar PHP (3 semanas) — 0/12</b></summary>

## Fase 5: Apagar PHP (3 semanas) — 0/12

### 5.1 Esquema y datos (1 semana) — 0/4
- [ ] M5.1.1 EF Core toma el esquema: migracion inicial "baseline" que no cambia nada; la tabla `migrations` de Laravel queda como historia **[local]**
- [ ] M5.1.2 Tablas que solo usaba Laravel y se van: `jobs`, `job_batches`, `failed_jobs`, `cache`, `cache_locks`, `sessions`, `telescope_*`, `pulse_*` (si se instalo). Respaldo antes **[servidor]**
- [ ] M5.1.3 `personal_access_tokens` sigue igual (es el contrato de los clientes). Decidir si algun dia se pasa a JWT: fuera de este roadmap **[local]**
- [ ] M5.1.4 `encrypted` casts: re-cifrar `two_factor_secret` con DataProtection de .NET y retirar el descifrador de Laravel **[local]**

### 5.2 Infraestructura (1 semana) — 0/5
- [ ] M5.2.1 Quitar el proxy por ruta: todo a C# salvo Next; `X-Backend` se queda **[servidor]**
- [ ] M5.2.2 Apagar PHP-FPM, Horizon, el cron del scheduler, Telescope; borrar el worker Docker de PHP **[servidor]**
- [ ] M5.2.3 Terraform y pipeline (B2) sin el rol PHP; `deploy.sh --rol api` despliega C# **[servidor]**
- [ ] M5.2.4 Respaldo (B4.2), alertas (B4.1, B4.4) y torre apuntando solo a C# **[servidor]**
- [ ] M5.2.5 Bateria completa de `tests/load` contra el sistema final y actualizar el catalogo de niveles (`STACK-PRODUCCION.md`) con las cifras medidas **[servidor]**

### 5.3 Cierre (1 semana) — 0/3
- [ ] M5.3.1 `eventos-backend` archivado en GitHub (no borrado); `docs/` apunta al repo nuevo; `reference_project_paths` actualizado **[local]**
- [ ] M5.3.2 Version final del mismo `docs/ARQUITECTURA.md` de M1.5.9 (no uno nuevo): sin PHP ni Node, solo API, hub, worker, Next, Redis, MySQL y R2; `rutas-duenos.md` queda toda en C# y se archiva. Y el runbook del dia del evento (B6.3) reescrito **[local]**
- [ ] M5.3.3 Retrospectiva con numeros: semanas reales por fase contra las estimadas, para la proxima vez (la medicion del hito 4 es M5.2.5) **[local]**

</details>

---

## LO QUE NO ENTRA (para no discutirlo cada semana)

- Features nuevas de asistente que no esten en PENDIENTES hoy. Las que estan (B.2, B.3, B.4) nacen en la fase 4b.
- JWT, Identity, OAuth: los tokens Sanctum son el contrato con los clientes y se quedan.
- Reescribir la webapp o la app: no se tocan salvo la capa `realtime` y el admin nuevo en Next.
- Octane, Reverb, Filament 4: parches al carro viejo. Si la decision es migrar, no se invierte ahi.
- Fase 2 y nice to have de PENDIENTES §12: siguen diferidos; si un cliente los pide, nacen en C#.
