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

## Idéntico o arreglado (Kamilo 2026-10-30)

La regla de "C# responde exactamente lo que respondía PHP" vale solo para lo que lee un cliente que ya existe. Antes de copiar un
comportamiento que, mirado solo, parece un defecto (un campo que sale siempre vacío, algo que no se reintenta, un texto que no se
puede borrar, una validación que falta, un efecto que PHP no dispara), se pregunta quién lo consume:

1. **Idéntico, defectos incluidos:** los contratos que consumen la webapp (`eventos-web` fuera de `src/app/admin`), la app Expo
   (`eventos-app`) y el kiosko (`eventos-kiosko`): las rutas de `/api/v1`, sus formas de error y los efectos que esos clientes leen
   (cache de PHP, avisos al socket). Se confirma con un grep en esos tres repos; "creo que lo usan" no vale.
2. **Se arregla en C# y se anota:** los contratos externos sin consumidor (webhooks salientes, exportaciones para clientes, API para
   terceros) y los del admin nuevo (`/api/admin/v1`, que escribimos nosotros: tiene que ser igual de seguro, no igual de torpe).
   Cada arreglo lleva un BUG-NNN "resuelto en C#" en `BUG-LOG.md` y una línea en el Anexo B con el motivo: "sin consumidores externos
   al momento del cambio".
3. **Siempre idéntico, aunque sea externo, lo que un tercero verifica o parsea:** la firma, las cabeceras, el orden y el escape del
   JSON, el formato del ULID y la forma del log de un webhook. Eso solo cambia con aviso a quien lo consuma.
4. **Arreglar lo interno nunca toca el contrato:** reintentar, anotar un log que faltaba, limpiar archivos huérfanos o sumar un aviso
   al socket no cambian lo que lee el cliente; se hace sin consultar, y se anota.
5. **En duda, se anota como "por decidir" en el Anexo B y se pregunta a Kamilo;** no se copia por inercia.
6. **Pruebas:** la cruzada compara contra PHP normalizando los campos arreglados, y otra aserción fija el valor arreglado. Si el primer
   cliente externo llega a consumir un contrato arreglado, ese contrato pasa a la regla 3 (cambiarlo exige aviso).

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
    Un control destructivo (quitar una proteccion que impide borrar o dañar) corre contra la base desechable
    (`scripts/base-desechable.sh`, copia con llaves foraneas de la referencia), nunca contra la base compartida de las pruebas
    (Kamilo 2026-10-31: dos controles de M2.4.1b borraron asistentes sembrados con su cascada). Las bases de trabajo de
    contratos y cruzadas tienen las mismas llaves foraneas que la referencia (BUG-494, `ReinicioDeContratosTest`).

30. **Cookies del admin nuevo y lo que el navegador guarda** (Kamilo 2026-10-26, M2.2.7). Toda cookie que pone el
    admin (token del staff, equipo de confianza) va `HttpOnly`, `Secure` y `SameSite=Lax`, con su vencimiento escrito.
    El token nunca llega al JavaScript del navegador: las llamadas a C# salen del servidor de Next. Lo que se conserva
    al vencer la sesion (lo que se estaba escribiendo) va en memoria o `sessionStorage`, nunca en `localStorage`.

31. **Toda pantalla que junta los datos personales de una persona deja rastro de quien la abrio** (Kamilo 2026-11-09,
    M2.9.4, Ley 1581 de Colombia). Al abrirla se escribe en la bitacora del panel (`admin_audit_log`) quien, a quien,
    cuando y desde que IP, con su prueba (abrir la pantalla deja la fila; sin permiso no queda ni se ve nada). Lo que se
    muestra de un envio fallido es el error del servidor, nunca el contenido del mensaje.

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
  - 2026-10-30 (M2.3.6a, correo base; Kamilo): los dos avisos de seguridad del staff salen directo de C# y ya no por el outbox de PHP. Diferencias con PHP: el aviso se pide DESPUES de guardar (el outbox lo escribia en la
    misma transaccion, asi que si el proceso se cae entre guardar y encolar el aviso se pierde; para la persona que se bloquea o activa el 2FA es menor que mandar un aviso de algo que no se guardo); el reintento es el de
    Hangfire (10, 30, 60, 120 y 300 s) en vez del `retryUntil` de 12 horas de PHP; el limitador por segundo se hace esperando dentro del trabajo, no devolviendolo a la cola. El correo del sistema va por el SMTP de Resend
    (un solo camino con el SMTP de cada organizacion, MailKit) y no por la API de Resend; los rebotes y entregas no se leen (mejora posible por webhook, PENDIENTES §12).
  - 2026-10-30 (M2.3.6b, webhooks; Kamilo): sin consumidores externos de webhooks al momento del cambio, C# NO copia dos defectos de PHP.
    BUG-492: PHP mandaba `phone`, `company`, `job_title` y `photo_url` siempre null (los lee del asistente y viven en `users`) y
    `attendee.updated` solo saltaba por las etiquetas; C# manda los valores reales de la persona y `attendee.updated` salta tambien por su
    nombre, telefono, empresa o cargo. BUG-493: un 5xx del sistema del cliente no se reintentaba (PHP solo reintentaba errores de red y
    429); C# lo reintenta a los 60 y 300 s, hasta 3 intentos. IDENTICO a PHP, byte a byte (la cruzada recalcula el HMAC como el cliente y
    compara contra el DispatchWebhookJob real): la firma HMAC-SHA256 en hex, las cabeceras (Host, Content-Type sin charset,
    X-EventOS-Signature, X-EventOS-Event, User-Agent y Content-Length), el orden y el escape del JSON (`\/`, `\uXXXX` en minusculas,
    `[]` para un data vacio), el ULID en mayusculas, el log y el estado del endpoint. NO puede ser identico: el texto del error de una
    conexion caida (cURL en PHP, .NET en C#). Un tercer intento fallido queda solo en el log (PHP lo dejaba en failed_jobs). Los
    reintentos son trabajos nuevos con el numero de intento, no los reintentos de Hangfire.
  - 2026-10-30 (M2.3.10, cierre de la revision de lo copiado; Kamilo): sin consumidores externos al momento del cambio, C# ARREGLA estos
    comportamientos de PHP (webapp, app y kiosko no los leen): push con 3 intentos por lote y borrado de tokens muertos; ZIP de documentos
    con reintentos y URL en null si no entra ninguno; borrar un sponsor retira el acceso de vendedor de su equipo; correo con plantilla
    apagada deja fila `skipped` y un reintento no duplica; marcadores de plantilla en una sola pasada; documento propio con mime y tamano del
    archivo y sin `msword`/`ms-powerpoint` nuevos; `stream_iframe` invalido da 422 en vez de guardarse vacio; aforo 0 = sin limite tambien al
    editar; restaurar una sesion borrada; textos por caracteres y no por UTF-16; cualquier zona IANA; avisos de FAQ y de la encuesta
    post-evento; modulo de catalogo viejo visible solo para apagarlo; desempate por id al correr la siguiente sesion.
    Sesion cancelada: C# deja `status=cancelled` (PHP solo ponia `cancelled_at` y la webapp y la app, que leen `status`, la veian
    programada o en vivo). No hay accion de revertir; el estado y `cancelled_at` van juntos al editar (salir de Cancelada la limpia).
  - 2026-10-30 (M2.3.10, aceptados): 3 (el socket deja pasar un aviso por segundo y entidad), 4 (si Redis falla, el guardado sigue y la cache
    vieja dura hasta que venza), 5 (sin outbox: si el proceso cae entre guardar y pedir el efecto, se pierde), 13 (borrar un documento,
    speaker o sponsor no borra su archivo), 19 (lo escondido en el formulario no se guarda), 20 (cada guardado del evento avisa `branding`),
    23 (fechas del admin truncadas al minuto) y 26 (limitadores propios de cada backend mientras convivan: el cupo real se duplica).
  - 2026-10-31 (M2.3.10b, segunda revision; Kamilo): C# ARREGLA. Ingreso del staff: un fallo de contraseña tarda al menos 200 ms (el
    Timebox de `SessionGuard::validate`, que C# no tenia: sin persona la respuesta salia en pocos ms y decia si el correo existe);
    activar y confirmar el segundo factor miran el bloqueo (PHP tampoco lo miraba en `TwoFactorSetup`) y descartan el pendiente; el
    limite de 5 codigos por minuto suma y pone su vencimiento en un solo paso de Redis (Lua) y pasado el cupo no sigue sumando.
    Onboarding: la imagen hero relativa que guardo el FileUpload de Filament se acepta si vuelve sin cambios (antes 422); dos
    formularios no pueden tener el mismo `id` (la app busca cada paso por id y el segundo nunca se veia); una opcion de Intereses que
    ya eligieron asistentes no se quita: 422 que pide desactivarla (Filament la borraba y la cascada se llevaba las respuestas; mismo
    criterio que la trivia del stand). Tracks y tipos: reordenar pide la lista completa y se guarda en una transaccion; un track o un
    tipo nuevo va al final (Filament lo dejaba en 0). Sesion cancelada: `status` y `cancelled_at` nunca se contradicen; entrar a
    Cancelada escribe la fecha (la que habia se conserva), salir la limpia, y la que cancelo Mission Control de PHP (solo
    `cancelled_at`) el admin la lee como Cancelada, asi guardarla sin tocar el estado no la revierte.
  - 2026-10-31 (M2.3.10b, aceptados, PHP hace lo mismo): dos personas que guardan el onboarding a la vez, gana la ultima; la carrera al
    crear el slug de un tipo (el indice unico `event_id, slug` frena el segundo con un error); el color obligatorio de tracks y tipos; las opciones de Intereses
    sin tope; una lista `null` en el onboarding la vacia; los numeros vacios de un formulario quedan null en vez de los valores por
    defecto; en el HTML de las paginas, el `!important` dentro de `span` que HTMLPurifier quita y un `>` dentro de un atributo.
    La limpieza de `staff_sessions` y `trusted_devices` vencidos va a M2.4.4.
  - 2026-10-31 (M2.3.6c, importacion de asistentes; Kamilo): IDENTICO a `AttendeesImport`, porque lo leen la app, la webapp, el
    correo y los webhooks (la cruzada compara contra el import real de PHP): las columnas de la persona (nombre, correo en
    minusculas, telefono, sin activar, activa), el token de invitacion de 64 letras y numeros, el rol de asistente, el asistente
    (role attendee, etiquetas, invited_via csv, aprobado desde ya), el QR (HMAC de "id|evento|segundos" con APP_QR_SECRET), las
    respuestas por campo, el enlace `/join/{token}` con APP_URL, el webhook attendee.registered por asistente, la version de las
    estadisticas del Data Center y las columnas de la plantilla. C# ARREGLA (admin nuevo, sin consumidor): corre en segundo plano
    (la Api lee y valida al subir; el Worker escribe) y el archivo no queda en disco (PHP lo dejaba en storage/app/imports); lee
    CSV con punto y coma o tabulador y en la codificacion de Windows (PHP solo coma y UTF-8: un CSV de Excel en espanol quedaba
    en una columna); cada fila se valida como el registro (`RegisterRequest`: nombre 255, correo valido 255, telefono 30 y cada
    respuesta por el tipo de su campo) y ademas las listas desplegables y de casillas solo aceptan sus opciones o las de su
    lista precargada (countries, cities_co; el registro no lo cotejaba): se comparan sin mayusculas ni tildes y se guarda la
    opcion como esta escrita en el campo; la que no vale sale en el CSV de errores con su motivo; las casillas se guardan como el registro (1/0 y las multiples como JSON;
    PHP guardaba el texto de la celda); las fechas se aceptan AAAA-MM-DD o DD/MM/AAAA y se guardan AAAA-MM-DD; Si/No/X para una
    casilla; transaccion por fila; quien ya estaba inscrito sale en la lista (PHP lo omitia sin decir); a quien ya activo su
    cuenta la invitacion lo lleva a entrar a la webapp (`App:WebappUrl`/login; PHP le mandaba un token que nunca guardaba); el
    alias `e-mail` coincide (en PHP el encabezado pasa a `e_mail` y nunca coincidia); los alias fijos no se toman como campos;
    la fila de ejemplo de la plantilla no se importa; etiquetas sin vacias ni repetidas; un "0" es un valor (PHP lo descartaba
    con empty()); las filas vacias se saltan; sin columna de nombre o de correo, 422 al subir; una importacion a la vez por
    evento; hasta 10.000 filas y 10 MB; el correo "importacion terminada" se manda a quien importo (PHP nunca lo mandaba); la
    plantilla y el CSV de errores salen con punto y coma y BOM. Una sola contraseña de descarte (argon2id, como Hash::make) por
    importacion en vez de una por fila: nadie la conoce, se reemplaza al activar, y un argon2id por fila costaria mas de 30
    minutos en un archivo grande. Igual que PHP: no se mira el cupo del evento (`max_attendees`) ni la ventana de registro.
  - 2026-10-31 (M2.4.1a, lista de asistentes; Kamilo): IDENTICO a AttendeeAdminResource y AttendeesExport (las cruzadas lo comparan):
    la lista sin el staff del evento, aprobar (la fecha, la bitacora, el correo `registration_approval` con el HTML de
    RegistrationApprovalMail, el webhook attendee.approved, las fichas del socket de BUG-428 y las estadisticas del Data Center) y
    las columnas de la exportacion. C# ARREGLA (admin nuevo, sin consumidor): el filtro de codigos
    de acceso trae solo los del evento (Filament listaba los de todos los eventos: cruce entre eventos, regla 26); la exportacion deja
    fuera al staff del evento, como la lista (AttendeesExport lo incluia), y el rol sale con su nombre, como en la lista
    (Asistente, Vendedor, Staff Check-in; AttendeesExport lo dejaba como esta guardado); aprobar en lote acepta hasta 500 por vez; la bitacora anota
    la IP de quien aprueba (Filament la dejaba vacia); la exportacion CSV sale con punto y coma y BOM; en el Excel exportado una celda
    que parece formula queda como texto con el prefijo de texto de Excel (quotePrefix) en vez del apostrofo visible de PHP (en el CSV
    el apostrofo queda literal, como PHP). Identico aunque no sirve, sin consumidor: `staff_room_assignments.assigned_by` es
    obligatorio y apunta a un asistente; quien asigna desde el panel casi nunca lo es y PHP pone ahi al mismo staff de check-in (C#
    hara lo mismo en M2.4.1b). Cambiarlo pide una migracion y nadie lee la columna.
  - 2026-10-31 (M2.4.1b, perfil del asistente; Kamilo): IDENTICO a AttendeeAdminResource (la cruzada lo lee con los modelos de PHP y
    abre el formulario de Filament): etiquetas al instante con attendee.updated, cambiar rol (Asistente o Staff Check-in) con
    attendee_role_changes y la bitacora `change_role` y las fichas del socket, asignar salon activo al momento (sin aceptacion),
    eliminar con attendee.cancelled, y las estadisticas del Data Center. C# ARREGLA (admin nuevo, sin consumidor): eliminar no se
    puede si la persona tiene leads de sponsors o lidera un stand (la base se llevaba en cascada los datos de los sponsors y el
    stand quedaba sin lider); la confirmacion muestra lo que se perderia (puntos, publicaciones, mensajes, fotos) y sugiere banear;
    al eliminar se borran tambien sus fichas del socket (PHP la dejaba conectada hasta 24 h); dejar de ser Staff Check-in desactiva
    sus salones y borra `staff_attendee:` y `staff_assigned:` (como el retiro de la API de PHP; Filament no lo hacia y podia seguir
    escaneando); asignar salon borra la cache de la asignacion. Privacidad: el avatar sin foto es la inicial; PHP lo pedia a
    `hostedboringavatars.vercel.app` mandandole el nombre de la persona. `assigned_by` sigue identico, con una precision: C# busca
    al asistente de quien asigna en este evento (PHP lo buscaba en cualquier evento, cruce sin motivo); si no tiene, el mismo staff.
  - 2026-11-01 (M2.4.2, baneos; Kamilo): IDENTICO a `BanService` (bitacora `ban_attendee` y `unban_attendee` con la IP,
    `BannedMail`, push "Acceso suspendido" solo si la cuenta esta activada, `/internal/ban/enforce` al socket sin esperar
    respuesta, fichas del socket al banear, estadisticas del Data Center). C# ARREGLA (admin nuevo, sin consumidor): el
    vencimiento corre cada 5 minutos (PHP cada hora: el acceso volvia a tiempo pero el push "tu suspension expiro" llegaba
    hasta una hora tarde); al desbanear tambien se borran las fichas del socket (PHP no: lo seguia viendo baneado hasta que
    vencia su copia de 60 s); los envios de push de C# saltan a los baneados. POR ETAPAS: al banear C# sigue borrando el token
    de Expo y las suscripciones de Web Push como PHP, porque los envios de push que siguen en PHP no filtran a los baneados;
    mientras tanto el "Acceso restaurado" no llega (igual que hoy). Deja de borrarlos en el ultimo punto de M4c.6.
  - 2026-11-01 (M2.4.3a, webhooks del admin; Kamilo): IDENTICO a `WebhookEndpointResource` en lo que recibe el partner (el ping
    `test.ping` con su cuerpo y su firma, el reenvio con el mismo cuerpo del log, la simulacion como prueba, la especificacion con
    la llave de ese partner) y en la fila que lee PHP. C# ARREGLA (admin nuevo, sin consumidor): el secreto no viaja en la fila y
    se copia al portapapeles sin mostrarse (decision de Kamilo); probar un webhook inactivo no manda nada y lo dice, y una caida
    de red responde "no respondio" (BUG-502); eventos y campos se validan contra la lista y quedan en su orden (el CheckboxList
    los guardaba en el orden en que se marcaban); el resumen cuenta solo el evento (BUG-501); la especificacion dice los
    reintentos reales (3 intentos: al minuto y a los 5 minutos; PHP decia 1, 5 y 30); en la lista la URL va debajo del partner y
    copiar el secreto y la especificacion estan en la pagina del webhook (la fila de Filament no cabia y escondia el partner).
  - 2026-11-01 (M2.4.3b, llaves de entrada; Kamilo): IDENTICO a `WebhookApiKeyResource` en la fila que lee PHP y en la llave
    (`wh_live_` y 40 letras o numeros; el check-in de PHP la acepta). C# ARREGLA (admin nuevo, sin consumidor): la llave completa
    se muestra una sola vez al crearla o regenerarla, con boton Copiar (Filament mostraba los primeros 12 y la dejaba copiar
    entera siempre); regenerar pide confirmacion y avisa que la anterior deja de servir al instante; la lista solo muestra los
    ultimos 4 caracteres y la fecha; la API nunca la vuelve a devolver. POR ETAPAS: en la base sigue en texto plano hasta M4d.9
    (BUG-503): el check-in de partners sigue en PHP y la compara asi; ahi entran el hash, la columna de los ultimos 4 y el texto
    nuevo de la especificacion.
  - 2026-11-01 (M2.4.3c, historial de webhooks; Kamilo): IDENTICO a `WebhookLogResource` en columnas, filtros y orden. C# ARREGLA
    (admin nuevo, sin consumidor): solo los envios del evento (BUG-504, cruce entre eventos); el detalle en su propia pagina con
    URL (Filament: modal); lo que se mando legible, sin monospace (Filament: JSON en <pre>); "Copiar JSON exacto" con las
    cabeceras y el cuerpo que salieron, firma incluida, gracias a `request_body` y `request_headers` (migracion nueva; los llena
    C#; lo que manda PHP queda en null y se dice); "Copiar respuesta" (lo guardado, recortado a 2000 como en PHP; sin
    cabeceras de la respuesta, decision de Kamilo). Filtros extra: partner y llave, para llegar desde Webhooks y Llaves.
  - 2026-11-01 (M2.4.4a, Staff y permisos; Kamilo): IDENTICO a `UserResource` en la jerarquia (BUG-406), el ultimo super_admin
    (StaffGuard), la organizacion (BUG-374), la clave en argon2id y lo que hace `User::booted` al cambiar la clave o desactivar
    (cerrar sesiones y quitar equipos de confianza). C# ARREGLA (admin nuevo): no lista a los admins antiguos de `attendees`
    (no entran al panel; revisar en M2.4.4b si esos roles aun significan algo para la app); borrar a quien tiene bitacora responde
    422 y ofrece desactivar (BUG-506; Filament rompia); un evento fuera de lo que ve quien edita es un error y las asignaciones
    que no ve se conservan (Filament descartaba lo primero y borraba lo segundo). POR DECISION: la contrasena la sigue fijando
    quien crea la cuenta (BUG-505, al cerrar el hito 1).
  - 2026-11-01 (M2.4.4b, seguridad de cada persona; Kamilo): IDENTICO a EditUser en quien puede desbloquear (super_admin, solo
    bloqueada) y restablecer el segundo factor (super_admin, nunca a si mismo, solo con segundo factor), a TwoFactorService::resetFor
    (borra el 2FA, `2fa_reset`, quita equipos, cierra sesiones, correo `staff_2fa_reset`) y a SecurityActivity (mismas frases y
    fechas, ultimas 20 de 90 dias). C# ARREGLA (admin nuevo): pedir una de las dos acciones cuando ya no aplica responde 422 con el
    motivo (Filament solo escondia el boton); la actividad la ve quien puede abrir la ficha (403 si no).
  - 2026-11-01 (M2.4.4c, limpieza del staff; Kamilo): NUEVO en C# (PHP no lo tiene): cada dia se borran las sesiones del staff sin
    uso hace mas de 30 dias (con su token si son del admin nuevo) y los equipos de confianza vencidos hace mas de 30 dias. PHP solo
    borraba las sesiones vencidas de quien volvia a entrar y nunca los equipos. No cambia lo que se ve: PHP ya no las listaba.
  - 2026-11-01 (M2.4.5a, codigos de acceso; Kamilo): IDENTICO a AccessCodeResource en campos, filtros y en que el registro gasta
    el uso. C# ARREGLA (admin nuevo): el codigo se guarda normalizado (sin espacios y en mayusculas, `AccessCode::normalizar`; en
    Filament uno con un espacio adentro nunca servia), un codigo repetido de cualquier evento responde 422 "Ya existe ese codigo"
    (Filament rompia con el indice unico) y `max_uses` minimo 1.
  - 2026-11-01 (M2.4.5b y c, Golden Tickets y Premios Ganados; Kamilo): IDENTICO a GoldenTicketResource y RewardService en el
    premio, el canje, el token HMAC, el codigo de 6 letras y el anuncio privado. Decidido por Kamilo: una sola accion de confirmar
    reclamo para las dos pantallas (M2.8.5), que guarda quien confirmo tambien en Golden Tickets. C# ARREGLA: no confirma dos veces
    ni uno vencido (actualizacion condicionada), reenviar o confirmar cuando ya no aplica responde 422 con el motivo, y otorgar
    valida que la persona sea asistente del evento y no del equipo.
  - 2026-11-01 (BUG-508, fechas; Kamilo): C# corta las fracciones de segundo al escribir, como Laravel (MySQL las redondeaba y la
    hora quedaba en el segundo siguiente); las columnas con milesimas (`timestamp(3)`) conservan las suyas.
  - 2026-11-02 (M2.4.6, Soporte; Kamilo): IDENTICO a SupportRequestResource y EditSupportRequest en estados, textos del push y del
    anuncio privado, `action_url` y lo que lee la app en `/support/mine`. C# ARREGLA (admin nuevo): ver la solicitud no la marca
    leida (BUG-383: la pantalla manda `POST /read`), responder es una sola vez (422 la segunda; un doble clic no manda dos push ni
    dos anuncios) y pide texto (Filament guardaba vacio sin decir nada), y Soporte entra con `view-content` o `manage-content`
    (BUG-509: org_admin y event_admin no lo veian). Decidido por Kamilo: el contador de abiertas del menu sale de la misma consulta
    que la lista (`open_count`, `count_only=1`).
  - 2026-11-02 (M2.5.1a, Anuncios; Kamilo): IDENTICO a AnnouncementResource y AnnouncementObserver en campos, `action_url` (compose y
    parseActionUrl), estados, el contenido con `trim(strip_tags)` de PHP 8 y los efectos (cache por rol, data:sync del nuevo para todos,
    avisar lo demas). C# ARREGLA (admin nuevo): una audiencia vacia se guarda null (BUG-510), "Lleva a" una sesion o URL sin elegirla es un
    error (Filament lo guardaba sin destino), la sesion y la encuesta tienen que ser del evento y un contenido que queda vacio al quitarle
    las etiquetas es un error. La imagen se guarda como URL (la de la subida, R2 o disco publico) y no como ruta relativa: la app la lee
    igual (V1\AnnouncementResource deja pasar una URL). Decidido por Kamilo: los privados en la lista solo para leer, "Solo generales" por
    defecto; partida en a (sin push) y b (push con sus logs).
  - 2026-11-02 (M2.5.1b, el push de anuncios; Kamilo): IDENTICO a sendPush y dispatchToAttendees en los logs (`pending` con el ticket de
    Expo, `failed` con el motivo, `delivered` en el navegador de quien no tiene app), el `log_id` en los datos, el titulo y el texto. C#
    ARREGLA: la audiencia incluye a quien solo tiene el navegador suscrito (BUG-511), salta a los baneados (como los demas push de C#) y
    "Enviar push" solo con el anuncio publicado (Filament lo mandaba de un programado). Decidido por Kamilo: un solo "Publicar ahora" con la
    casilla "Enviar push a N personas" marcada por defecto (Filament publicaba siempre con push) y "Enviar push" aparte. `CheckExpoReceiptsJob`
    sigue en PHP y procesa tambien los tickets de C# hasta M4c.6.
  - 2026-11-02 (M2.5.2, las push programadas; Kamilo): IDENTICO a DispatchScheduledNotificationsJob en los logs (`scheduled`, con el `log_id`),
    los datos (`event_id`, `type` scheduled, `notification_id`), la carta (anuncio publicado, texto sin etiquetas, sus roles, push_sent) y el
    aviso de los anuncios programados que llegaron (BUG-423); Reenviar es una copia nueva como en Filament. C# ARREGLA (decision de Kamilo):
    Enviar ahora y Reenviar llegan tambien a quien solo tiene navegador, saltan a los baneados y dejan la carta (BUG-375: en Filament solo Expo,
    con baneados y sin carta); crear con la hora ya llegada la manda en el mismo paso (Filament esperaba al minuto siguiente aunque decia
    "deja la hora actual para enviar inmediatamente"); la toma condicionada evita mandarla dos veces (PHP: withoutOverlapping, que no frena el
    boton); los roles son Asistente y Vendedor, los de los anuncios (Filament ofrecia tambien "Admin", un rol viejo que ninguna pantalla asigna).
  - 2026-11-02 (M2.5.3 y M2.5.4, Emails; Kamilo): IDENTICO a EmailCatalog (19 tipos, grupos, variables, esenciales), EditEmail (copia del evento
    por idioma, idioma vacio no se guarda, Sistema solo super_admin y global, volver a la del sistema, prueba con "[PRUEBA]" por el SMTP del
    sistema), EmailLayout y render (la cruzada compara el HTML) y OrganizationEmailSettings (cast `encrypted`, vacia no cambia, isReady). C# ARREGLA:
    cambiar servidor, puerto, cifrado, usuario, clave o remitente deja la configuracion sin verificar (PHP seguia verificada con datos sin probar y
    los correos salian por ahi); el historial no trae los correos sin evento de toda la plataforma; Emails pide send-notifications o
    view-analytics en el menu (salia a todo el staff); los datos de muestra dicen "Chrome en Windows" (no el punto medio) y el correo de prueba de
    la conexion va sin emojis. Decidido por Kamilo: el contenido en HTML con la vista previa, mientras llega el editor con formato (M2.8.6).
  - 2026-11-03 (M2.6.3a, Gamificacion, Ajustes; Kamilo): IDENTICO a GamificationSettingsResource en columnas, topes de Filament, campos del
    concurso solo con el concurso activo, puntos por posicion como textos (KeyValue), la ventana de cada accion (se mezcla con lo que ya tenia,
    `daily_max` solo en las repetibles, sin roles = nadie) y PointsService::getConfig para leer; cada guardado corre EventObserver::saved. C#
    ARREGLA: la asistencia a sesiones se guarda (BUG-513: Filament la descartaba), apagar "Tiene premio al completar" borra el premio (BUG-514) y
    los numeros vacios dan 422 (Filament mandaba null a columnas que no lo aceptan). Decidido por Kamilo: el resumen dice que los cambios de puntos
    aplican desde ahora y no recalculan lo otorgado, y cada accion se edita en una ventana encima como en Filament (`Ventana`, la primera del
    admin nuevo; las confirmaciones de otras pantallas siguen siendo una caja en la pagina).
  - 2026-11-03 (M2.6.3b, Chat, Recordatorios y Limites de uso; Kamilo): IDENTICO a ChatSettingsResource (columnas, modo lento de 0 a 300, el
    POST a Node `/internal/chat/invalidate-config` con `eventId` al guardar), ReminderSettingsResource (`reminder_config` con enabled, windows
    y notify_on_change, que lee SendAgendaRemindersJob::resolveConfig) y RateLimitSettingsResource (`rate_limits` por limite con enabled y limit,
    los topes del formulario, ChecksRateLimit para leer); cada guardado corre EventObserver::saved. C# ARREGLA (decidido por Kamilo): los
    limites abren con lo que de verdad aplica y guardar no apaga los que estaban por defecto (BUG-515); un minuto fuera de 1 a 60 da 422, sin
    repetidos, al menos uno si estan activos y apagarlos conserva los minutos (BUG-516); el modo lento vacio da 422 (BUG-517, Filament 500).
  - 2026-11-03 (M2.6.1a, Encuestas: lista, crear, titulo, activar y cerrar; Kamilo): IDENTICO a EventSurveyResource y ListEventSurveys en
    alcance (generales y post-evento, las de sesion no), orden, columnas, "Nueva encuesta" (general en borrador), las 3 preguntas base de la
    post-evento, activar solo en borrador y cerrar solo activa, sin borrar encuestas; view-analytics. C# ARREGLA (decidido por Kamilo): activar
    y cerrar avisan `poll:new` y `poll:closed` como PollController (BUG-382), y la post-evento no se crea al abrir la lista (un GET no escribe):
    la lista avisa y un boton la crea. Ademas el organizador no entra (BUG-519). Diferencia menor: "Respuestas" de cada pregunta cuenta
    personas, no votos (Filament contaba votos: una de varias opciones sumaba de mas).
  - 2026-11-03 (M2.6.1b, Encuestas, las preguntas; Kamilo): IDENTICO a QuestionsRelationManager en campos y topes (pregunta hasta 500, tres
    tipos, al menos 2 opciones de hasta 200), la nueva de ultima, ordenar y eliminar con sus respuestas. C# ARREGLA (decidido por Kamilo): una
    pregunta con respuestas no cambia de tipo y sin respuestas, al cambiarlo, se quitan sus opciones (Filament las dejaba pegadas); una opcion
    con votos no se puede quitar, con el motivo y el conteo (Filament la quitaba y su voto quedaba sin opcion); cada cambio a una encuesta
    activa avisa `poll:updated`. "Desactivar opcion" va en M4b.4: los lectores de PHP no la respetarian.
  - 2026-11-03 (M2.6.4a, Recap, la configuracion; Kamilo): IDENTICO a EventRecapResource en campos, topes y forma de `recap_config` (la de
    RecapConfigResolver), lo escondido no se guarda (imagen del titulo solo en imagen, texto y stats del certificado solo con el certificado) y
    al guardar se borra `recap:{evento}:{asistente}` de quien ya tiene recap; manage-events. C# ARREGLA (decidido por Kamilo): lee lo que
    aplica y guarda la configuracion entera, asi guardar no apaga el recap ni sus bloques (BUG-520); Insider menor que Activo; vaciar un texto
    vuelve al de fabrica. La imagen sube por `POST /uploads` con destino event-recap (sin SVG, como el resto). Generar y regenerar quedan en
    PHP hasta M4e.5 (huecos aceptados en decisiones.md).
  - 2026-11-03 (M2.6.5a, Social, el Muro y los puntos; Kamilo): IDENTICO a WallPostResource y WallSettingsResource en columnas, filtro de
    estado, busqueda por autor, campos y "El social hoy" (sin el punto medio); manage-content. Los puntos en C# (`Puntos.IntentarAsync`, M4b.3
    adelantado) con las reglas de PointsService::tryAward: puntos por accion y rol, tope diario en el dia de Bogota, sin repetir por referencia,
    fila en points_log y avisos `gamification` a la persona y `leaderboard` al evento; cruzada contra PHP. C# ARREGLA (decidido por Kamilo):
    un solo camino para publicar y ocultar desde la fila, el detalle y el masivo (BUG-379); la persona queda bloqueada en una transaccion y el
    indice unico points_log_unique_award frena el doble, asi dos aprobaciones a la vez dan los puntos una vez; el cambio de estado es un UPDATE
    condicionado, asi `wall:post` sale una vez; el maximo vacio es 422 (Filament daba 500); la webapp suelta `sponsors` y `wall` (BUG-521).
    Ocultar sigue sin aviso al socket, como PHP (`wall:hidden` en M3.4.1).
  - 2026-11-03 (M2.6.5b, Social, Fotos; Kamilo): IDENTICO a EventPhotoResource en columnas, filtros (estado, tipo, concurso), busqueda por
    asistente, detalle y "Subir foto oficial" (aprobada, oficial, sin asistente, en events/{evento}/photos como StorageService); manage-content.
    Ninguna accion avisa a nadie, como PHP (la galeria se lee de la base). C# ARREGLA (decidido por Kamilo): un solo camino para aprobar y
    rechazar desde la fila, el detalle y el masivo (BUG-378): aprobar solo una pendiente y con puntos solo si tiene autor; rechazar tambien una
    aprobada, con confirmacion, y los puntos se quedan (quitarlos va despues de la fase 5). Las fotos de la galeria pesan hasta 10 MB, como el
    FileUpload de Filament; el resto de imagenes sigue en 5. Una foto por pedido. El contador de pendientes vuelve al menu (count_only=1).
  - 2026-11-03 (M2.6.5c, Social, Premiar; Kamilo): IDENTICO a "Premiar" y "Premiar top fotos" de EventPhotoResource en quien se premia
    (aprobada y con autor; top: aprobadas, del concurso, no oficiales y sin premio, por likes y la mas vieja primero), el Golden Ticket de
    RewardService::createGoldenTicket (el mismo de Golden Tickets, M2.4.5b), sus titulos y motivos, el anuncio privado y manage-content.
    C# ARREGLA (decidido por Kamilo): una foto se premia una sola vez y lo frena la base (migracion de Laravel: columna generada
    event_photo_prize_id con indice unico, solo para los premios de fotos), no solo la pantalla; los puestos siguen desde el ultimo dado y
    "Premiar top" se frena al llegar al maximo de ganadores (Filament numeraba desde 1 cada vez); los puntos por posicion se dan (BUG-522).
    La prueba de PHP que afirmaba "se pueden crear 2" se actualizo. El reinicio de los contratos copia solo las columnas normales.
  - 2026-11-04 (M2.2.8, Seguridad de tu cuenta; Kamilo): IDENTICO a AccountSecurity de Filament en lo que se ve y en lo que queda en la
    bitacora (sessions_closed y trusted_devices_revoked con reason self/others/self_all, count y label; recovery_codes_regenerated), en que
    solo se toca lo propio, en que la sesion actual no se cierra desde ahi, en el cupo de 5 codigos por minuto compartido con el login y en
    las frases de cada fila. C# ARREGLA (decidido por Kamilo, BUG-523): un codigo malo al pedir codigos nuevos cuenta para el bloqueo del
    login (5 seguidos = 30 minutos y correo staff_account_locked) y al bloquear se cierra la sesion desde la que se intento (Filament solo
    frenaba 5 por minuto); con la cuenta ya bloqueada, 423 y esa sesion tambien se cierra; el mismo codigo repetido en la misma ventana de
    30 s cuenta una vez; un codigo bueno deja los fallidos en cero. "Quitar la confianza" pasa de enlace subrayado a boton de texto.
    Sin consumidores externos al momento del cambio (pantalla del admin nuevo).

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
