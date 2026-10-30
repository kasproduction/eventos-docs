# Buenas practicas de la migracion a .NET

> Reglas para escribir C# en la migracion (`docs/roadmaps/ROADMAP-MIGRACION-DOTNET.md`). Se fijan antes de la primera
> linea de C#. Version 2026-10-22: reglas de Kamilo, revisadas contra el roadmap y contra lo que salio al congelar las
> 247 rutas (backend `90e61f8`), con sus correcciones aprobadas. Una regla que no se pueda vigilar en CI dice como se
> vigila. La version operativa para Claude es `docs/plantillas/CLAUDE-dotnet.md`. Decisiones: `docs/decisiones.md`.


## PHP congelado (regla 6 del roadmap, reforzada por Kamilo 2026-10-27)

**No se escribe ni una linea mas de PHP, ni puentes, ni jobs, ni excepciones.** Lo que C# necesite de PHP se adelanta
a C#, aunque sea de la fase 4 (un job, un envio de push, un correo). Las dos excepciones ya hechas, el outbox de correos
(M2.2.5) y el rol organizador (M2.2.6), son las ultimas; el outbox se reemplaza en cuanto C# mande correo, no en la 4c.
PHP solo sigue sirviendo lo que todavia no se ha portado. Cuando un trabajo pasa a C#, sale de PHP en el mismo
despliegue (regla 4).

Las pruebas si se siguen escribiendo en PHP (pruebas cruzadas, escenarios de `ContratoSeeder`): son la forma de
comprobar que PHP lee lo que C# escribe. El producto nunca. Un `ORDER BY` que falte en PHP ya no se agrega alla: lo
define C# y se anota como diferencia en `decisiones.md` (Kamilo 2026-10-27).

## Portar comportamiento, no forma

1. **El snapshot define que hace el endpoint; el codigo PHP solo explica por que.** PHP se lee para entender reglas, no
   para traducirlo linea a linea. Si el snapshot y el PHP no coinciden, manda el snapshot y se anota el hallazgo.
2. **Organizacion por modulo** (`Agenda/`, `Social/`, `CheckIn/`...), los 16 del inventario de `migracion-dotnet.md`
   §1.1. Cada modulo trae sus endpoints, servicios, DTOs y tests. Nada de carpetas `Controllers/`, `Services/` o
   `Repositories/` globales. Los contratos de PHP hoy van por tema (`tests/Contratos/*Test.php`); al portar un modulo se
   llevan sus pruebas a la carpeta del modulo.
3. **Cero abstraccion preventiva.** EF Core directo, sin repositorios; Minimal APIs o controllers finos; servicios con
   nombre. No Clean Architecture ni proyectos por capa. Los proyectos de la solucion (D.6: `Api`, `Worker`, `Hub`,
   `Contracts`, `Tests`) son uno por proceso que se despliega, no por capa.
4. **Efectos secundarios en un solo `XxxEffects` por entidad**, un metodo por efecto, disparado desde el servicio. Es la
   traduccion de los 14 observers, los 6 hooks `booted` y las closures del provider (M0.5.1 los deja asi en PHP primero).

## Red de seguridad

5. **Los snapshots corren en CI contra PHP y contra C# en cada push**, no solo al cruzar (M1.1.4, M1.1.5):
   - **Endpoint de reinicio solo para pruebas:** vuelve a sembrar `ContratoSeeder` entre pruebas que escriben. Se activa
     por variable de entorno y no existe en produccion (la ruta ni se registra).
   - **Las pruebas que no pueden correr contra C# llevan etiqueta y comparan forma**, no valor: hoy el 429 (repite pedidos
     por dentro), el ZIP "listo" (cambia la base en la prueba) y el Data Center (sesion simulada con `Sanctum::actingAs`).
   - **Ruleta y sorteo:** PHP usa semilla fija (`mt_srand(2026)`); C# acepta una semilla en modo pruebas para comparar el
     valor, no solo la forma.
6. **Cada cruce se revierte en un despliegue:** `cruzar.ps1` y `descruzar.ps1`, probados antes del primer cruce real.
7. **Prueba cruzada de escrituras para todo lo que escribe:** si C# guarda, PHP lee lo mismo que si hubiera guardado
   PHP o Filament (escribir por C#, leer el snapshot por PHP).
   - **Criterio de hecho (Kamilo 2026-10-25):** una tarea que escribe datos que PHP lee no esta hecha hasta que su
     prueba cruzada existe y pasa. No se marca `[x]` en el roadmap sin ella. Vale para el admin nuevo aunque no espeje
     una ruta de PHP: lo que C# escribe y PHP lee (columnas, cifrados, JSON, bitacoras, filas que PHP lista o cierra)
     se prueba leyendolo con el codigo de PHP.
   - **"Lo que no probe" no es una nota de reporte:** solo puede quedar sin probar lo que una decision de Kamilo
     registrada en `decisiones.md` permite. Sin esa decision es trabajo pendiente de la misma tarea, que sigue abierta.
8. **`mysqldump` automatico dentro del script de cruce** para las rutas que escriben.
9. **Toda lista con orden explicito y el mismo que PHP.** Donde PHP no pone `ORDER BY`, MySQL decide y C# puede
   devolver otro orden; C# define el orden y se anota como diferencia en `decisiones.md` (PHP ya no se toca: regla 6
   reforzada, Kamilo 2026-10-27).

## .NET

10. **Async de punta a punta:** prohibido `.Result` y `.Wait()`. Lo vigila `Microsoft.VisualStudio.Threading.Analyzers`
    con los avisos como error en CI.
11. **Un solo `ConnectionMultiplexer`** de Redis como singleton; **`HttpClientFactory`** para todo cliente HTTP.
12. **`AsNoTracking()` en lecturas y proyeccion a DTO con `Select`:** nunca cargar la entidad entera para devolver tres
    campos.
13. **Fechas:** `DateTimeOffset` con el **offset de la zona de la app** (`APP_TIMEZONE`, hoy Bogota, -05:00), sin
    fracciones (M0.3.2). Nunca `DateTime.Now`. La zona por evento queda abierta en `decisiones.md`, para despues de la
    fase 5.
    - 22 migraciones tienen columnas con `useCurrent()`: la base pone su propia hora. **C# las escribe siempre
      explicitas.** La zona horaria del MySQL de produccion se verifica en la fase 1 (M1.2.5): si es UTC, esas fechas ya
      estan corridas 5 horas.
14. **Politica JSON global en un solo lugar:** snake_case, nulos incluidos, campos condicionales omitidos. Nunca se
    sobreescribe por endpoint. Las unicas excepciones son por campo, en **una lista cerrada en un solo archivo**
    (`Contracts/ExcepcionesJson.cs`, M1.4.1). **Nada entra a la lista sin una decision en `decisiones.md`.** Hoy son 15
    nombres que llegan a un cliente (Anexo A): 2 en la API HTTP (`eventId`,
    `sessionId`, encuestas), 1 en datos de push y el resto en el socket (confirmado en M0.4.1).
15. **Jobs de Hangfire idempotentes.** `ShouldBeUnique` de Laravel NO equivale a `DisableConcurrentExecution` (ese solo
    evita correr dos a la vez; los duplicados igual se encolan). La unicidad es **un filtro propio de Hangfire (o una
    verificacion antes de encolar), escrito una vez en `Worker` y reutilizado** por todo job que en PHP es
    `ShouldBeUnique`, con la misma clave.
16. **`dotnet list package --vulnerable` en CI;** versiones fijas en los `.csproj`.

## Observabilidad

17. **`X-Request-Id`** que atraviesa proxy, PHP, C# y Node, visible en Sentry de los dos lados (M1.1.3).
18. **Panel por ruta y por `X-Backend`** (p95, errores, req/s). Despues de cada cruce se compara la ruta contra su
    propio numero en PHP.
19. **Cada cruce se mide con los k6 de agosto en el MSI** (PHP contra C#, misma maquina) y el numero queda anotado en
    el roadmap junto a la tarea. En el servidor real se mide en los hitos, no en cada cruce.
20. **Criterio de cruce de una ruta:** snapshot igual contra los dos, orden explicito (regla 9), prueba cruzada de
    escrituras si escribe (regla 7), medicion en el MSI anotada (regla 19), `descruzar.sh` probado y la ruta
    probada detras del Nginx real del MSI (regla 29, decision D.9 del 2026-10-25).

## Proceso

21. **Contexto por sesion, un commit por tarea** (ajustada por Kamilo 2026-10-29; antes era "un bloque por sesion").
    Se sigue en la misma sesion mientras el contexto aguante y el trabajo sea del mismo tipo; bloques cortos encadenados
    estan bien. `/guardar` + `/clear` se piden solo cuando el contexto se esta llenando o cuando cambia el tipo de
    trabajo (por ejemplo, de construir a revisar, o de C# a infraestructura). Nada de "aprovecho y tambien": un bug que
    aparece se registra en BUG-LOG y se arregla solo si bloquea o si Kamilo lo decide.
22. **Segunda pasada de revision por modulo**, en sesion aparte, con una pregunta: que hace PHP que esto no hace.
23. **Solo la fase activa visible en el roadmap;** las demas plegadas.
24. **Un hallazgo corrige un numero, no abre un documento.** Las decisiones van solo en `docs/decisiones.md`, una
    linea cada una (fecha, que, por que); el roadmap y `PENDIENTES.md` solo enlazan.
25. **Retro al cerrar cada fase:** que se estimo y que costo, que regla falto, que se corrige del roadmap. Va al final de
    la fase en el roadmap, en cinco lineas. **La retro actualiza `docs/ARQUITECTURA.md`** (existe desde M1.5.9 y en la
    fase 5 queda como la version final, M5.3.2).

## Seguridad

26. **Todo lo que cuelga de un evento se filtra por evento de raiz, no endpoint por endpoint.** En PHP el filtro estaba
    repartido y la auditoria M0.8.1 encontro cruces entre eventos (BUG-415, 417, 426, 436). En C#: el evento sale de la
    ruta, nunca del "ultimo evento activo" del usuario (BUG-425); filtro global de EF Core por `EventId` en las
    entidades del evento; la membresia es requisito de la ruta, como `miembro.evento` en PHP (403 `NOT_IN_EVENT`, `/mine`
    queda afuera, decision 2026-10-23). Un id de otro evento responde 404 y no escribe nada. Cada modulo lleva su prueba
    de cruce: alguien de otro evento no ve ni toca nada.
    - **Como esta hecho (M1.3.6, decision 2026-10-25):** cerrado por defecto. Toda ruta del evento termina en
      `.DelEvento()`, que fija el evento en el contexto; sin eso las 86 tablas del evento no devuelven nada. Las hijas
      se filtran por su padre y `events` no se filtra. Guardar algo de otro evento responde el 404 de modelo de Laravel.
      Cruzar eventos a proposito solo con `CruzandoEventos(CruceDeEventos.X)`, lista cerrada en
      `Contracts/Datos/CruceDeEventos.cs`; `IgnoreQueryFilters` en otro lado rompe una prueba. Detalle y pruebas en
      `eventos-api-dotnet` (`EventosDb.Evento.cs`, `FiltroPorEventoTest`).
27. **Un hallazgo se confirma con una prueba roja antes de arreglarlo, y el arreglo trae su control** (el caso bueno que
    tiene que seguir funcionando). Si el arreglo rompe una prueba vieja que fija una regla de producto, no se reescribe
    la prueba: se adapta el arreglo o se pregunta a Kamilo (BUG-397 contra BUG-363, retro de la fase 0).

30. **Cookies del admin nuevo y lo que el navegador guarda** (Kamilo 2026-10-26, M2.2.7). Toda cookie que pone el
    admin (token del staff, equipo de confianza) va `HttpOnly`, `Secure` y `SameSite=Lax`, con su vencimiento escrito.
    El token nunca llega al JavaScript del navegador: las llamadas a C# salen del servidor de Next. Lo que se conserva
    al vencer la sesion (lo que se estaba escribiendo) va en memoria o `sessionStorage`, nunca en `localStorage`.

## Documentacion

28. **La documentacion tecnica se genera desde el codigo cuando se puede; lo escrito a mano es solo decisiones y el
    porque.** Generado: el OpenAPI de PHP (`eventos-backend/docs/api.json`, Scramble, M0.3.6), los snapshots de los
    contratos, el OpenAPI de C# con su interfaz navegable (M1.5.7, comparado en CI contra `api.json` en las rutas
    cruzadas) y la tabla de dueños de ruta (`contratos/rutas-duenos.md`, la escriben `cruzar.ps1` y `descruzar.ps1`,
    M1.5.8). A mano: `decisiones.md` (que se decidio y por que), `ARQUITECTURA.md` (como encaja todo y por que, regla 25)
    y los catalogos de `contratos/` que el codigo no puede dar (acciones del admin, claves de Redis, variables). Un
    documento a mano que repite lo que dice el codigo se borra. En la API admin nueva, ningun endpoint sin descripcion
    en su OpenAPI (M2.1.4).
29. **Probar con la pieza real, no solo con el servidor de pruebas** (D.9, retro de la fase 1). Lo que toca HTTP,
    proxy, servidor o despliegue pasa por Nginx, Kestrel y PHP-FPM de verdad antes de darse por bueno
    (`scripts/probar-proxy-local.sh` en local, el entorno de `scripts/preparar-medicion-msi.sh` en el MSI). Cada
    ruta cruzada se prueba detras del Nginx real del MSI (criterio de la regla 20). `TestServer` escondio BUG-465.

---

## Anexo A. Excepciones JSON (lista cerrada)

Van en `Contracts/ExcepcionesJson.cs` (M1.4.1). Nada entra sin decision en `decisiones.md`. **Aprobada por Kamilo el 2026-10-22 (24 nombres).** Lista al 2026-10-22, sacada
de los 330 snapshots y del codigo PHP:

| # | Campo | Donde viaja | Origen en PHP |
|---|---|---|---|
| 1 | `eventId` | API: encuestas (junto a `event_id`); socket | `PollController.php:400`; `InvalidationService`, `WallController`, `SessionConfigController` |
| 2 | `sessionId` | API: encuestas (junto a `session_id`); push; socket | `PollController.php:398`; `SendAgendaRemindersJob:125`, `SendSessionChangedNotificationJob:57`; `QuestionController:214`, `EventSessionObserver:108` |
| 3 | `attendeeId` | socket | `BanController:187`, `InvalidationService:181`, `StaffNotificationService`; Node en `chat:message`, `chat:emoji` |
| 4 | `fromName` | socket | `NetworkingController:710` |
| 5 | `fromAttendeeId` | socket | `NetworkingController:711` |
| 6 | `streamUrl` | socket | `EventSessionObserver:110` |
| 7 a 14 | `invitationId`, `sponsorId`, `sponsorName`, `sponsorLogo`, `sponsorTier`, `inviterName`, `attendeeName`, `expiresAt` | socket | `StaffNotificationService` |
| 15 | `checkedIn` | socket (`checkin:update`) | Node convierte `checked_in` de `CheckinService:216` |
| 16 a 19 | `dbId`, `userName`, `sentAt`, `pinnedAt` | socket (`chat:*`) | Node (`chat.ts`); en C# los arma el hub |
| 20 | `chatCount` | socket (`session:metrics`) | Node (`chat.ts`) |
| 21 a 24 | `pollId`, `questionData`, `gameData`, `messageId` | socket, del cliente al servidor (`display:project`, `chat:delete`) | Mission Control, chat-monitor |

- En la API HTTP solo existen 1 y 2. **Confirmado en M0.4.1 (2026-10-22)** contra `docs/contratos/eventos-socket.json`, que
  dice evento por evento que campo viaja: entran 15 a 24, que arma Node y antes no estaban. `targetAttendeeId` sale de la
  lista: va de PHP a Node (`/internal/*`) y nunca llega a un cliente; en la fase 3 lo usa solo la fachada `/internal/*`.
  Los eventos de juegos, asistencia, agenda y staff de salas viajan en snake_case y se copian igual.
- No se copian: los campos de botones de Filament que hoy salen en `GET data-center/{id}/exports` (`dispatchDirection`,
  `isOutlined`, `shouldClose`...), que el admin nuevo redefine; y `storeUrlIos`, `expiresIn`, `logoUrl`, que son datos
  de vistas Blade, no JSON.

## Anexo B. Formas de Laravel que se copian tal cual

- 401 `{"message": "Unauthenticated."}`; 422 `{message, errors}` con los textos de `lang/es`; 429 `Too Many Attempts.`
  con `Retry-After`; 404 de modelo `No query results for model [App\Models\X] N` (sale asi en produccion).
- Paginacion donde PHP la arma a mano: `{data, meta: {current_page, last_page, total}}`; revisar el snapshot de cada ruta.
- Codigos de negocio en `code` (`FORBIDDEN`, `QR_INVALID`, `DUPLICATE_LEAD`, `staff_uses_panel`...).
- **Diferencias con PHP decididas (no se copian; cada una con su decision en `decisiones.md`):**
  - D.7: token de staff rechazado con Redis caido. PHP responde 500; C# borra el token en MySQL (obligatorio), deja un
    aviso en el log por la ficha del socket y responde el 401 de arriba.
  - D.8: C# no acepta la sesion por cookie en `/api` (solo bearer). Las rutas del Data Center siguen en PHP hasta M2.7.2.
  - 2026-10-27 (M2.3.2c, push de cambio de horario): el texto va limpio. PHP manda "🔄 Cambio de horario" y separa el
    lugar con "·"; C# manda "Cambio de horario" y el lugar separado con coma (reglas de interfaz de Kamilo).
  - 2026-10-27 (M2.3.2c, retrasar un salon desde Mission Control): un solo aviso. En PHP quien tiene en favoritos la
    sesion siguiente recibe "Agenda actualizada" y ademas "Cambio de horario" (el retraso mueve su hora y dispara el
    observer); C# manda solo "Agenda actualizada".
  - 2026-10-27 (M2.3.2c1, sesiones; Kamilo): el fin no puede ser antes del inicio (422 en `end_datetime`, "El fin no
    puede ser antes del inicio."; igual al inicio si vale). Filament no lo frenaba. La base de demo no tiene ninguna
    sesion asi (consulta del 2026-10-27, 186 sesiones), no hay nada que corregir antes de c3.
  - 2026-10-27 (M2.3.2c1, sesiones; Kamilo): aforo y grupo de Silent Disco enteros de 0 en adelante. Filament solo pedia
    `numeric` y el grupo negativo daba 500 en la columna sin signo (como BUG-478).
  - 2026-10-27 (M2.3.2c1): las URLs del stream y de la grabacion tienen que ser http(s); si no, 422. La regla `url` de
    Filament dejaba pasar otros esquemas (ftp:) y el modelo los guardaba vacios sin decir nada.
  - 2026-10-27 (M2.3.2c1): los mensajes de validacion de sesiones son textos claros propios ("Escribe el titulo.",
    "Elige el estado de la lista."), no los de Laravel con el nombre tecnico del campo.
  - 2026-10-27 (M2.3.2c1, BUG-380): asignar speakers avisa (pieza de la sesion, cache de agenda y de speakers).
  - 2026-10-27 (M2.3.2c2, push de cambio de horario; Kamilo): la hora va como en Colombia, "4:00 p. m." (sin cero
    adelante; "12:30 a. m." a media noche). PHP mandaba "04:00 PM" (`h:i A` de Carbon).
  - 2026-10-27 (M2.3.2c3, "Agenda actualizada" al retrasar un salon; Kamilo): respeta `push_session_reminders` (sin fila
    manda), como "Cambio de horario"; PHP no miraba ninguna preferencia. Y el salon sin nombre es "el salón" (PHP: "el salon").
  - 2026-10-27 (M2.3.2c3, forma): al finalizar una sesion retrasada PHP la guardaba dos veces y mandaba la pieza
    `session.updated` cada vez; C# la guarda y la manda una vez, con el estado final.
  - 2026-10-29 (M2.3.5, subidas; Kamilo): el SVG no se acepta (Filament si): se serviria desde el mismo origen que la Api y
    puede traer scripts. El tipo sale del contenido (JPG, PNG, WebP, GIF; PDF en documentos) y el destino es una lista
    cerrada. La miniatura de sesion va siempre al disco publico como ruta relativa (`thumbnail_path`), no a R2, mientras PHP
    siga leyendo la agenda; pasa a R2 cuando la ruta publica de sesiones sea de C# (4a).
  - 2026-10-29 (M2.3.3b, sponsors; Kamilo): el lider del stand se elige con un buscador del servidor (tope de 20) y C#
    valida que sea un asistente del evento que no sea del staff (Filament solo lo filtraba en el formulario). Una trivia
    de stand con respuestas no se borra (se apaga): Filament la borraba y se llevaba las respuestas en cascada.
  - 2026-10-29 (M2.3.3b, forma): topes de la columna con mensaje (logo, banner y sitio web hasta 255; puntos hasta 65535;
    colaboradores de 1 a 20) en vez del 500 de Filament (BUG-486 y 487); reordenar sponsors avisa `sponsors` (BUG-488);
    el lider del stand no se quita del equipo desde la lista de miembros (Filament podia dejar al lider sin acceso).
  - 2026-10-29 (M2.3.3c1, documentos; Kamilo): Word y PowerPoint viejos (`.doc`, `.ppt`) no se pueden subir. Filament los
    aceptaba; C# solo reconoce por el contenido los modernos (`.docx`, `.pptx`), el PDF, JPG y PNG, y a un archivo viejo le
    dice "guárdalo como .docx o .pptx". Un documento viejo que ya existe se puede editar sin volver a subirlo.
  - 2026-10-29 (M2.3.3c1; Kamilo): un documento nuevo queda de ultimo (`sort_order` mayor + 1); Filament lo dejaba en 0, o
    sea primero. Reordenar, crear, editar y borrar corren todos los efectos de `DocumentObserver` y `ContentObserver`
    (olvidar `event:{id}:documents`, el ZIP y el aviso `documents`); Filament no los corria al reordenar (BUG-489).
  - 2026-10-29 (M2.3.3c1; Kamilo): el ZIP de documentos se arma 10 segundos despues del primer cambio y los cambios que
    llegan en ese tiempo no programan otro (PHP: un trabajo unico mientras hubiera uno esperando, que corria al instante).
    Al empezar, el trabajo suelta la marca: un cambio durante la corrida programa la siguiente y ninguno se pierde.
  - 2026-10-30 (M2.3.3c2, paginas; Kamilo): el cuerpo se limpia con HtmlSanitizer y la misma lista de etiquetas que PHP, copiando las
    costumbres de HTMLPurifier (parrafos automaticos, `<br />`, `target="_blank" rel="noreferrer noopener"` en enlaces externos,
    `style` solo en span con `propiedad:valor;`, alt de la imagen con el nombre del archivo). Medido contra 58 casos reales de
    HTMLPurifier: 57 iguales y una pagina ya guardada no cambia al pasar por C#. Diferencias aceptadas: HTMLPurifier arregla el HTML
    roto con reglas propias que no se copian (por ejemplo un `<hr>` en medio de un texto); los saltos de linea son `\n` (el servidor es
    Linux; PHP usa PHP_EOL, que en Windows da `\r\n`); el cuerpo solo se limpia si cambio (como `isDirty` de PHP).
  - 2026-10-30 (M2.3.3c2): una pagina externa (`iframe`) pide su URL (Filament dejaba una pagina externa sin pagina); una pagina nueva
    queda de ultima; "quien la ve" sin marcar se guarda null (la ven todos); borrar es suave como el `SoftDeletes` del modelo.
  - 2026-10-30 (M2.3.3c2): el admin nuevo avisa `pages` a los clientes al guardar, olvidar la lista y reordenar; PHP solo olvidaba
    `event:{id}:pages:{rol}` porque nadie leia las paginas. Ahora la webapp las lee y su boton del menu se actualiza al instante.
  - 2026-10-30 (M2.3.4a, modulos; Kamilo): al prender o apagar un modulo C# avisa `modules` una vez, despues de subir la version de la cache de
    `event:{id}:modules:ver`; PHP lo avisaba dos veces (el observer y `invalidate()` del panel) y el primer aviso llegaba antes de subir la
    version. Reordenar y cambiar la audiencia solo suben la version y avisan (sin push silencioso ni olvidar claves), como el panel; el push
    silencioso (`module_update`) va solo al prender o apagar y a los asistentes con token de Expo (no respeta `push_session_reminders`: no muestra nada).
    Un modulo de un catalogo viejo (que `ModuleCatalog` ya no conoce) no se lista ni se edita (404).
  - 2026-10-30 (M2.3.4b, branding; Kamilo): al pasar el evento a `ended` C# activa la encuesta post-evento que estaba en borrador (estado `active` y `activated_at`),
    como `EventObserver`; los recaps no, siguen en PHP hasta M2.6.4. `organizer_name` topa en 120 (la columna) en vez de 255 (BUG-490). Lo escondido en
    Filament no se guarda: About solo en borrador o registro con About prendido, imagen y oscurecer del hero solo con image o video, el video solo con video.
  - 2026-10-30 (M2.3.4c, onboarding de la app; Kamilo): las opciones de la encuesta de Intereses ya no llevan emoji (decision de diseno): el admin nuevo no tiene el campo y C# guarda
    `emoji` vacio en cada opcion que escribe, asi las viejas se limpian al editarlas. La app y la webapp dejan de pintarlo en la proxima publicacion (PENDIENTES §8). Vaciar un texto del
    onboarding lo quita del JSON y la app vuelve a su texto de siempre; en Filament el merge ignora los null y el texto queda como estaba (BUG-491).
    Diferencia decidida por Kamilo (2026-10-30): C# no copia ese merge; la unica otra diferencia con Filament en esta pestana es el emoji vacio. Lo escondido se comporta igual (cruzada con control).

## Anexo C. Comandos de los contratos (desde `eventos-backend`)

- Contra PHP: `./vendor/bin/pest tests/Contratos` (~90 s).
- Contra C#, solo las rutas ya portadas (Windows o MSI): `scripts/contratos-contra-csharp.sh <ruta-a-eventos-backend>` desde
  `eventos-api-dotnet` (levanta C# en modo contratos y corre lo de `scripts/contratos-csharp.txt`).
- Contra C#: `CONTRATO_BASE_URL=http://localhost:<puerto> ./vendor/bin/pest tests/Contratos` (el servidor en modo
  contratos; `solo-php` se salta).
- PHP como otro backend: `.\scripts\servidor-contratos.ps1` en una terminal y `CONTRATO_BASE_URL=http://127.0.0.1:8123`
  en otra (~5 min). Es la prueba de que el modo externo funciona antes de que exista C#.
- Orden de las listas: `CONTRATO_ORDEN_INVERSO=1 ./vendor/bin/pest tests/Contratos` (sqlite invierte las consultas sin
  `ORDER BY`; si un snapshot cambia, a esa lista le falta orden).
- Regenerar: `--update-snapshots` SOLO sin `--filter` (con filtro borra todos y regenera solo los filtrados); para uno
  solo, borrar su `.snap` y correr.
