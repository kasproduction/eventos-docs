# Pendientes de EventOS clasificados para decidir — octubre 2026

Documento de solo lectura hecho el 2026-10-21. No cambia nada del codigo ni de `docs/living/PENDIENTES.md`, que sigue siendo la fuente unica de tareas. Este archivo es la misma lista vista con otra pregunta: **que se hace ya en Laravel, que se hace ya por seguridad, que se congela hasta el admin nuevo en .NET, y que se hace en Laravel pero con DTO y prueba de contrato para que luego se porte sin dolor.**

## Fuentes revisadas

| Fuente | Que habia |
|---|---|
| `docs/living/PENDIENTES.md` (actualizado 2026-10-16 + avances del 2026-10-18 y 2026-10-21) | 210 casillas abiertas en 14 secciones. Es la base de todo lo que sigue |
| `docs/living/BUG-LOG.md` | 6 bugs abiertos: BUG-344, 345, 347, 348, 358, 359 |
| `docs/roadmaps/*` (16 archivos) | 11 llevan aviso HISTORICO y no cuentan. Los 5 activos (Infraestructura, Seguridad del staff, Manual, Data Center, Event Pulse) ya estan volcados en PENDIENTES; Infraestructura tiene 75 casillas abiertas que son el detalle de B1-B6 |
| TODO y FIXME en los 5 repos | 5 reales: 2 en `HappeningNowController` (usar favorita o keynote), 2 en `ProfileScreen.tsx` del Expo (color fijo en vez del accent del tema), 1 en `useTracker.ts` (sin proveedor de analytics). El resto son la palabra "todo" en español |
| Issues de GitHub (6 repos) | 0 abiertos |
| `features/` | 38 capturas de pantalla de la competencia (2026-03-31). No hay features a medio terminar ahi |
| Tablero de prioridades (artifact, coleccion `prioridades`) | Vacio: Kamilo no ha marcado nada |
| `composer audit` corrido hoy | 63 avisos en 20 paquetes: 3 criticos, 20 altos, 30 medios, 7 bajos. Incluye `laravel/framework` (afectado todo lo menor a 12.69) |
| Working tree del Expo, verificado hoy | El recap sigue sin commitear y `tsc` da 5 errores, igual que en julio |

## Como leer las columnas

- **Modulo:** admin (Filament), API (Laravel publica), socket (Node), app (Expo), web (Next), infra.
- **Tamaño:** horas (menos de un dia), dias (1 a 5), semanas (mas de una). Estimado de una persona con IA.
- **Evento:** SI si el evento de 5.000 personas del 6 de diciembre no sale sin esto; NO si es demo, pulido o fase 2. Recordar que al 2026-10-16 no hay cliente para esa fecha y el servidor esta en pausa: la columna responde "si el evento ocurre".
- **Tipo:** Seguridad, Soporte (operar y ver lo que pasa), o vacio si es funcion.
- **Recomendacion:** una de cuatro: **Ya en Laravel**, **Ya por seguridad**, **Congelar hasta admin .NET**, **Laravel con DTO + contrato** (se hace ya, pero la respuesta se define como Resource o DTO explicito y se le escribe una prueba de contrato con snapshot, para que .NET la porte igual).

Regla que pidio Kamilo: no se toca Filament salvo que un pendiente del admin sea necesario para el evento. Esos van en la seccion 3 marcados aparte.

---

## Resumen

| Bloque | Items | Tamaño junto |
|---|---|---|
| 1. Seguridad, hacer ya | 9 | 1 a 2 semanas, la mitad necesita servidor |
| 2. Lo que pide el evento del 6 de diciembre | 38 | 8 a 12 semanas, 13 solo con servidor |
| 3. Admin necesario para el evento (unico caso donde vale tocar Filament) | 2 | dias |
| 4. Demo vendible y webapp (no bloquea el evento) | 25 | 4 a 6 semanas |
| 5. App Expo | 11 | 1 semana + tiendas (semanas, solo si el cliente quiere app) |
| 6. Deuda tecnica | 14 | 1 a 2 semanas |
| 7. Congelado hasta el admin nuevo en .NET | 17 | no se hace ahora |
| 8. Fase 2 y nice to have | 48 agrupados | no se hace ahora |
| 9. Decisiones abiertas de Kamilo | 12 | decidir, no codear |

Lectura rapida: de las 210 casillas, 47 se hacen ya (seguridad + evento), 17 se congelan con justificacion, 48 ya estaban diferidas, y el resto es demo, Expo y deuda que se hace cuando haya hueco. Nada de lo que pide el evento requiere tocar Filament, salvo dos casos chicos de la seccion 3.

---

## 1. Seguridad: hacer ya

| # | Pendiente | Modulo | Tamaño | Evento | Tipo | Recomendacion | Fuente |
|---|---|---|---|---|---|---|---|
| S1 | **Subir Laravel 11.51 a 12.** Laravel 11 no recibe parches de seguridad desde marzo de 2026 y `composer audit` marca el framework (1 alto, 1 medio, 1 bajo, 1 sin severidad; corregidos solo en 12.69+). Todo lo instalado ya soporta 12: Filament 3.3.49, Sanctum 4.3, Horizon 5.45, Pest 3.8, Telescope 5.19, spatie/permission 6.25. La guia oficial de 12 tiene cambios minimos (validacion de imagen con SVG, `Str::is`, inspeccion multi esquema). El costo es correr las 1.100 pruebas y limpiar los caches de Filament | API + admin | 2 a 3 dias | SI | Seguridad | **Ya por seguridad** | `composer.lock`, audit de hoy |
| S2 | **Resolver los 63 avisos de `composer audit`** en el mismo paso que S1. Criticos: `mtdowling/jmespath.php` (viene con el SDK de S3 para R2), `phpoffice/phpspreadsheet` x2 (viene con maatwebsite/excel, que debe subir a 3.1.70+). Altos: `league/commonmark` (9), `filament/forms`, `guzzle`, `symfony/http-kernel`, `symfony/mime`, `maatwebsite/excel`. Casi todo es transitivo y sale con `composer update`; Filament debe quedar en 3.3.52+ y Livewire en 3.8.3+ | API + admin | incluido en S1 | SI | Seguridad | **Ya por seguridad** | PENDIENTES §3 ("57 avisos"), hoy son 63 |
| S3 | **Llaves reales de Turnstile** antes de abrir cualquier registro. Hoy van las de prueba, que aprueban a cualquiera (`TURNSTILE_SECRET_KEY` en backend, `NEXT_PUBLIC_TURNSTILE_SITE_KEY` en web, dominio en el panel de Cloudflare) | infra | horas | SI | Seguridad | **Ya por seguridad** (es configuracion, no codigo) | PENDIENTES §4 |
| S4 | **B.5 Cabeceras de seguridad** en eventos-web y la API (CSP, X-Frame-Options, nosniff, Referrer-Policy), y confirmar que ningun registro use la cedula como contraseña. El backend ya tiene `SecurityHeaders`; falta auditar la webapp | web + API | horas | SI | Seguridad | **Ya por seguridad** | PENDIENTES §5 B.5 |
| S5 | **BUG-344** guarda en `tests/TestCase.php` que aborte si la conexion no es sqlite o existe `bootstrap/cache/config.php`. Riesgo real: `RefreshDatabase` contra la base de desarrollo | API | horas | NO | Seguridad (datos) | **Ya por seguridad** | BUG-LOG |
| S6 | **S.7.2 `security:check` contra el `.env` real** de produccion (debug apagado, llaves, HTTPS). Cierra el frente 2FA 26/26 | infra | horas | SI | Seguridad | **Ya por seguridad** cuando se prenda el servidor | PENDIENTES §1 B1.3, §10 |
| S7 | **B4.2 Respaldo cifrado a R2 por cron con restauracion probada** | infra | dias | SI | Seguridad + Soporte | **Ya en Laravel** (es un cron con `mysqldump`, independiente del lenguaje) | PENDIENTES §1 B4.2 |
| S8 | **Sentry: DSN de produccion y validacion** en backend, web y app | infra | horas | SI | Soporte | **Ya en Laravel** | PENDIENTES §1 sueltos, §5 Fase 8 |
| S9 | **Revisar la autenticacion del admin y cuando expiran los tokens** (nota de Kamilo). Hoy: tokens Sanctum 7 dias con refresh; staff sin token por API; sesion web con 2FA y `StaffSession`. Es documentar y confirmar, no construir | admin + API | horas | NO | Seguridad | **Ya en Laravel** (escribirlo en `docs/webapp/AUTH-SPEC.md`) | PENDIENTES §11 |

Fuera de esta lista por decision de Kamilo: el parche de PHP. Passkeys (S.9) va a la seccion 7.

---

## 2. Lo que pide el evento del 6 de diciembre

Orden del plan B1-B6 de PENDIENTES §1. Etiqueta entre corchetes como en el original: [local] se hace sin servidor, [local+medir] se codea y se cierra midiendo, [servidor] solo con servidor.

### 2.1 Arranque del servidor (en pausa por decision de Kamilo hasta cerrar features)

| # | Pendiente | Modulo | Tamaño | Evento | Tipo | Recomendacion |
|---|---|---|---|---|---|---|
| P.0 | Token nuevo de DigitalOcean (`doctl auth init`, lo corre Kamilo) | infra | horas | SI | Soporte | Ya (lo hace Kamilo) |
| P.1 | Inventario de snapshots y bases administradas con costo por dia; no prender nada sin confirmar | infra | horas | SI | Soporte | Ya en Laravel |
| P.2 | Banco de pruebas Nivel 1 desde snapshots de agosto + codigo de hoy; revisar `TRUSTED_PROXIES` siempre | infra | dias | SI | Soporte | Ya en Laravel |
| P.3 | Punto de partida medido con `entrar-por-la-puerta.js` antes de arreglar nada | infra | horas | SI | Soporte | Ya en Laravel |
| P.4 | Medir B3.1 y B3.2 en servidor (codigo hecho 2026-10-12) | infra | dias | SI | Soporte | Ya en Laravel |

### 2.2 B2 Montaje reproducible (0/5)

| # | Pendiente | Modulo | Tamaño | Evento | Tipo | Recomendacion |
|---|---|---|---|---|---|---|
| B2.1 | Paquete por version con GitHub Actions [local] | infra | dias | SI | Soporte | Ya en Laravel. Es independiente del lenguaje; si llega .NET, se agrega un job al mismo pipeline |
| B2.2 | Terraform por nivel 0-4 [local escribir, servidor aplicar] | infra | semanas | SI | Soporte | Ya en Laravel. Lo mismo: la infra no cambia con el backend |
| B2.3 | Pipeline con compuertas, etapas 1-6 [servidor] | infra | semanas | SI | Soporte | Ya en Laravel |
| B2.4 | Humo funcional automatico, incluye correo de acceso recibido [servidor] | infra | dias | SI | Soporte | Ya en Laravel. **Escribirlo contra la API publica con los mismos snapshots de las pruebas de contrato**: sirve igual para .NET |
| B2.5 | Remontar de cero y destruir [servidor] | infra | dias | SI | Soporte | Ya en Laravel |

### 2.3 B3 Arreglos de codigo que pide 5.000 (0/8, 4 con codigo listo)

| # | Pendiente | Modulo | Tamaño | Evento | Tipo | Recomendacion |
|---|---|---|---|---|---|---|
| B3.1 | Correo con ritmo controlado: codigo `d5c3690`, falta medir y pedir ampliacion a Resend (BUG-345) | API + infra | dias (medir) | SI | Soporte | Ya en Laravel |
| B3.2 | La puerta: login cuenta fallos por red, publicas por recinto. Codigo `de80348`, falta medir | API + infra | dias (medir) | SI | Seguridad | Ya en Laravel |
| B3.3 | Juegos en vivo: limitar difusiones + cachear `GameService::getEligiblePool` | API + socket | dias | SI | | **Laravel con DTO + contrato**: los juegos son de lo mas complejo (821 lineas) y lo que se escriba aqui se porta despues; dejar `GameService` con metodos con nombre y respuestas tipadas |
| B3.4 | Replica de lectura: conexion `reports` para Data Center, Pulse y exports | API + infra | dias | SI | Soporte | Ya en Laravel (configuracion de conexiones, EF Core lo replica con un segundo `DbContext`) |
| B3.5 | ROL=admin en `deploy.sh`: admin en `admin.<dominio>` sin trafico de asistentes | infra | dias | SI | Soporte | Ya en Laravel |
| B3.6 | El 503 de la webapp con una persona: verificar si sigue y corregir | web | horas a dias | SI | | Ya en Laravel |
| B3.7 | Agenda partida (compartida cacheada + personal): codigo hecho, falta medir con 300 | API + web | dias (medir) | SI | | Ya en Laravel. **Ya nacio como DTO**: la respuesta partida se cubre con prueba de contrato al medir |
| B3.8 | Social por pestañas: codigo hecho, falta medir con 300 | web | dias (medir) | SI | | Ya en Laravel |
| B3.9 | Lo mio en un pedido (`GET /events/{id}/mine?piezas=`): hecho 2026-10-21, falta medir | API + web | dias (medir) | SI | | Ya en Laravel. Mismo caso: `LoMioService` ya devuelve piezas tipadas; agregar el snapshot |

### 2.4 B4 Ver lo que pasa (0/6)

| # | Pendiente | Modulo | Tamaño | Evento | Tipo | Recomendacion |
|---|---|---|---|---|---|---|
| B4.1 | Alerta externa al telefono (BetterStack o UptimeRobot a health, app y socket) | infra | horas | SI | Soporte | Ya en Laravel |
| B4.2 | Respaldo cifrado (ver S7) | infra | dias | SI | Seguridad | Ya por seguridad |
| B4.3 | Logs centralizados con id de peticion | infra + API | dias | SI | Soporte | Ya en Laravel. El id de peticion como cabecera `X-Request-Id` lo entiende cualquier backend |
| B4.4 | Alertas por umbral salidas de lo medido; cualquier 429 a un usuario legitimo suena | infra | dias | SI | Soporte | Ya en Laravel |
| B4.5 | **Torre de control** en el admin (solo super_admin, en vivo por socket) | admin | semanas | ver seccion 3 | Soporte | **Ver seccion 3**: es el unico pendiente grande del admin que el evento podria pedir |
| B4.6 | Escalar con un comando (`escalar.sh --api +1`) | infra | dias | SI | Soporte | Ya en Laravel |

### 2.5 B5 Medir para 5.000 (0/5)

| # | Pendiente | Modulo | Tamaño | Evento | Tipo | Recomendacion |
|---|---|---|---|---|---|---|
| B5.1 | Ritmo real: registro de pantallas por persona por minuto (decide Nivel 3 o 4). Si no da tiempo, cotizar Nivel 4 | web + API | dias | SI | Soporte | Ya en Laravel. Es tambien el numero que falta para fijar el limitador `api` (hoy 300/min a ojo) |
| B5.2 | Generador de carga para 5.000: varias maquinas + miles de asistentes sembrados | infra | dias | SI | Soporte | Ya en Laravel. Los scripts k6 sirven igual contra .NET |
| B5.3 | Montar Nivel 3 y medir hasta 5.000 (ola de login, 4G, admin con carga, export, foto viral, canario) | infra | semanas | SI | Soporte | Ya en Laravel |
| B5.4 | Caidas con carga: un nodo por rol + caida de API con 4 vCPU | infra | dias | SI | Soporte | Ya en Laravel |
| B5.5 | Decidir Nivel 3 o 4 y escribir la promesa del nivel (aguante, costo, RTO/RPO) | infra | horas | SI | Soporte | Ya (documento) |

### 2.6 B6 Entrega y dia del evento (0/4)

| # | Pendiente | Modulo | Tamaño | Evento | Tipo | Recomendacion |
|---|---|---|---|---|---|---|
| B6.1 | Pipeline completo con carga, caida y entrega con informe | infra | dias | SI | Soporte | Ya en Laravel |
| B6.2 | Modo registro / modo evento (`escalar.sh --modo`) | infra | dias | SI | Soporte | Ya en Laravel |
| B6.3 | Runbook del dia del evento + modo "evento en curso" (congela despliegues, snapshot al empezar) | infra | dias | SI | Soporte | Ya (documento) |
| B6.4 | Ensayo general una semana antes con el nivel elegido (28-29 de noviembre) | infra | dias | SI | Soporte | Ya en Laravel |

### 2.7 Sueltos de infraestructura y bugs que el evento necesita

| # | Pendiente | Modulo | Tamaño | Evento | Tipo | Recomendacion |
|---|---|---|---|---|---|---|
| I.a | Correo transaccional por cliente: clave Resend, dominio verificado, Mail-Tester > 9/10 | infra | horas | SI | Soporte | Ya en Laravel |
| I.b | Cloudflare LB cobra 5 USD/mes sin nada detras: cancelar o usar | infra | horas | NO | Soporte | Ya (decision) |
| I.c | Cache de auth fria: precalentar la ficha del socket si los 5.000 llegan tras 15 min sin uso (832 de 1.500 perdidos en la prueba) | API + socket | dias | SI | Soporte | **Laravel con DTO + contrato**: la ficha `socketauth:t:*` la leera tambien SignalR; fijar su JSON como contrato |
| I.d | HTTP connection pool (viejo "Fixes pre-stress") | API | horas | NO | | Ya en Laravel si sobra tiempo; .NET lo trae de serie con `HttpClientFactory` |
| BUG-358 | Error de hidratacion React #418 en produccion (sospecha: fechas calculadas distinto en servidor y navegador) | web | horas a dias | SI (sale en cada carga) | | Ya en Laravel. **Cruza con la decision de un solo formato de fecha** de `migracion-dotnet.md` seccion 6 |
| BUG-359 | Falta `mobileNav` en `messages/pt.json` | web | horas | NO | | Ya |
| BUG-345 | Correo sin ritmo (= B3.1) | API | ver B3.1 | SI | Soporte | Ya en Laravel |
| Pulse | Pulse cuenta 84 mensajes y la BD tiene 2.156: revisar `stats.messages` | API | horas | NO | | **Laravel con DTO + contrato** (Pulse se porta tal cual; que el numero quede definido) |
| QA.1 | QA presencial §6: mobile parity M.2-M.8 en telefono real, 3 viewports, Safari, 44 px, Lighthouse, WCAG | web + app | dias | SI | | Ya (QA, no codigo) |
| QA.2 | Red corporativa con WebSocket bloqueado: hoy el socket es solo WebSocket, sin polling. Verificar que la app no se rompa y decidir si se acepta | socket + web + app | horas | SI | | Ya (QA). **Nota:** SignalR tendria el mismo comportamiento con `skipNegotiation` |
| QA.3 | Login sin red durante el magic link, agenda que se actualiza sola por socket, kiosko y attendance-check en vivo, Pulse en < 2 s, Expo socket singleton en device, 4G Bogota con Sentry | todos | dias | SI | | Ya (QA) |
| Kiosk | Verificar scan del totem < 100 ms en VPS real | API | horas | SI (si hay salas) | | Ya en Laravel |

---

## 3. Admin necesario para el evento (unico caso donde vale tocar Filament)

Revise las 17 casillas de admin (PENDIENTES §2, §8, §11, §12) contra la pregunta "sin esto el evento no sale". Solo dos pasan, y una es una decision:

| # | Pendiente | Tamaño | Por que es del evento | Recomendacion |
|---|---|---|---|---|
| A1 | **B4.5 Torre de control** (super_admin, en vivo: conectados por nodo, peticiones/s, p95, errores, CPU por rol, colas, nodos en el LB). Pedida por Kamilo el 2026-04-19 y el 2026-08-17; con 11 o mas maquinas entrar por ssh no es viable | semanas en Filament | Operar 5.000 personas sin ver nada es improvisar | **Marcado aparte, decision de Kamilo.** Dos caminos: (a) construirla en Filament ahora, sabiendo que se tira con el admin nuevo; (b) cubrir el evento con B4.1 + B4.4 (alertas) + Laravel Pulse como panel (horas de instalacion) y construir la torre de verdad en el admin .NET, donde ademas el hub de SignalR tiene los conectados en casa. Mi recomendacion es (b): lo que el evento necesita es que suene una alarma y poder escalar con un comando, no una pantalla bonita |
| A2 | **Mission Control: pulir iPad horizontal a 1024 px** | horas | El moderador opera el evento desde Mission Control | Ya. **No es Filament**: Mission Control es una pagina estatica en `public/mission-control`, asi que no rompe la regla |

Lo que NO pasa el filtro y va a la seccion 7: renombrar Highlights, otorgar puntos manuales, toggle de Silent Disco, borrar `EventOnboardingResource`, credencial con etiqueta, paginas custom, keyvisual por superficie, hero modo texto, WhatsApp/SMS desde Filament, branded QR, landing builder, email builder, manual del organizador.

---

## 4. Demo vendible y webapp (no bloquea el evento)

Prioridad declarada por Kamilo el 2026-10-16: demo vendible primero. Son items de web casi todos; cuando tocan backend se marca.

| # | Pendiente | Modulo | Tamaño | Evento | Recomendacion |
|---|---|---|---|---|---|
| B.1 | Menos peticiones por pantalla: grupos 1 y 2 y B3.9 hechos; queda medir en servidor con 300 y las piezas que falten de "lo mio" (premios, pasaporte, recompensas, invitaciones, historias) si pesan | web + API | dias | NO (mejora) | **Laravel con DTO + contrato** para cada pieza nueva de `/mine` |
| Nav.1 | Skeleton solo si la espera pasa de 300 ms | web | horas | NO | Ya |
| B.2 | Juegos a demanda por QR en el stand (trivia, memoria, ahorcado, reflejos; un intento al ranking; recuperar partida; vista previa staff). Lab antes de codigo | API + web + app | semanas | NO | **Laravel con DTO + contrato**: endpoints nuevos, sin socket, 3 a 4 peticiones por juego. Reusar golden tickets, pasaporte y trivia. Es el candidato ideal para nacer ya con Resource + snapshot |
| B.3 | Cero friccion de entrada: el QR del stand suma con la sesion que ya existe, nunca otro login | web + API | dias | NO | **Laravel con DTO + contrato** (toca el handoff) |
| B.4 | Ficha de sesion: triple estado (me interesa, favorito, asistire) y Outlook ademas de Google. Verificar que hay hoy | API + web | dias | NO | **Laravel con DTO + contrato** (campo nuevo en `session_favorites` y en `EventSessionResource`) |
| B.6 | Respaldo de chat (Ably o similar) solo si la medicion de 5.000 muestra que el socket no aguanta | socket | decision | NO | **Congelar**: si el socket se reescribe en SignalR, la decision se toma con el socket nuevo medido |
| W.19 | Spatial Player 8.1 a 8.9: player persistente en el shell, split con agenda, mini flotante, /live con preview, mobile, lifecycle, QA | web | semanas | NO | Ya en Laravel (es solo web; el unico contrato que usa, `session:ended`, ya existe) |
| W.12.0 | Audit responsive: 3 viewports reales, tablet portrait, Edge corporativo, Firefox 115+ | web | dias | NO (es QA.1) | Ya |
| W.12.1 | Empty states consistentes, loading transitions | web | dias | NO | Ya |
| W.12.2 | Accesibilidad: contraste AA, teclado, tab order, ARIA | web | dias | NO | Ya |
| W.12.3 | Performance: bundle < 200 KB, `next/image`, Lighthouse 85/75, TTI < 3 s en 4G | web | dias | NO | Ya |
| W.12.7 | E2E cross-tab de streaming y social (requiere socket real en CI) | web + socket | dias | NO | **Congelar hasta decidir el socket**: un E2E contra Socket.IO se reescribe contra SignalR |
| W.12.9 | Cierre: memoria, counters historicos | docs | horas | NO | Ya |
| Web.a | Precarga de modulos vecinos (`RouteWarmer`) | web | horas | NO | Ya |
| Web.b | Sugerencia de typo del correo tambien al editar en el paso de contraseña | web | horas | NO | Ya |
| Web.c | Verificar que un modulo apagado no haga peticiones ("paquete minimo") | web | horas | NO | Ya (entra en B.1) |
| Web.d | UI optimista en rating de agenda, ticket de soporte y forms de perfil | web | horas | NO | Ya |
| Web.e | W.9 share rank social; Lighthouse autenticado; 3 vitest opcionales | web | horas | NO | Ya si sobra |
| L.1 | Toggle de tema Noir/Lux para el demo, visible solo con `?demo`; minilab antes | web | horas | NO | Ya (sin backend) |
| L.2 | "Ya tenias tu lugar" con varios eventos: sale aunque la cuenta sea de otro evento | API + web | horas | NO | **Laravel con DTO + contrato** (es la respuesta de `check-email`) |
| L.3 | Tarjeta al compartir cuando el evento no tiene card del home: generarla; lab antes | web | dias | NO | Ya |
| L.4 | Probar la tarjeta OG en WhatsApp, LinkedIn y X con URL real | web | horas | NO | Ya cuando haya servidor |
| L.5 | Credencial con etiqueta visible (VIP, Prensa) | admin + web | dias | NO | **Congelar hasta admin .NET** (la UI de marcar tags vive en el admin) |
| TODO.1 | `HappeningNowController`: usar la favorita del usuario o `is_keynote` en vez del primer keynote | API | horas | NO | **Laravel con DTO + contrato** si se toca; si no, dejarlo |
| TODO.2 | Agenda en grilla por auditorio para eventos de varias salas (pregunta Bintec) | web | dias | NO | Decision de Kamilo (seccion 9) |

---

## 5. App Expo

| # | Pendiente | Tamaño | Evento | Recomendacion |
|---|---|---|---|---|
| E1 | **Commitear el recap que sigue en el working tree** (verificado hoy: 5 errores de `tsc`, los mismos de julio: `leaderboard.tsx` propiedades duplicadas, `RecapCard.tsx` `image_url`, `ContestBanner.tsx` `border`, `AttendanceCheckModal.tsx` `useRef`). Kamilo lo puso de ultimo en julio; pero cualquier sesion de Expo lo necesita primero | horas | NO | Ya. Es deuda pura y bloquea el resto de la app |
| E2 | Recap: E2E con Chromium en el servidor + validacion visual en dev build | dias | NO | Ya cuando haya servidor |
| E3 | EAS build de produccion Android + iOS, cuentas, envio y revision (dias o semanas de revision) | semanas | **SI solo si el cliente pide app en tiendas**; la webapp cubre el resto | Ya en Laravel. **Nota para la migracion:** cada build en tiendas congela el cliente de Socket.IO en los telefonos durante semanas; si se va a SignalR, conviene que la primera build publicada ya lleve la capa `realtime` desacoplada |
| E4 | SHA-1 del keystore en Firebase + dominio/DNS | horas | con E3 | Ya |
| E5 | Silent disco push: verificar con dev build real | horas | NO | Ya |
| E6 | Staff app: cola offline MMKV + batch sync (solo si zonas sin WiFi son problema real) | dias | NO | Congelar hasta QA presencial |
| E7 | Alinear `documentos.tsx` legacy | horas | NO | Ya si sobra |
| E8 | Borrar `banners.tsx`, `BannerCarousel`, `bannersApi` (feature muerta) y `ModuleMenuCompact.tsx` huerfano | horas | NO | Ya si sobra |
| E9 | Portar "click sesion → agenda highlight" del W.5 al Expo | horas | NO | Ya si sobra |
| E10 | Decidir `recap/[eventId]` del Expo (fase 2) | decision | NO | Fase 2 |
| TODO.3 | `ProfileScreen.tsx` color fijo en vez del accent del tema (x2); `useTracker.ts` sin proveedor de analytics | horas | NO | Ya si sobra; analytics es fase 2 |
| Lux | Tab bar con `@callstack/liquid-glass` cuando soporte Expo | bloqueante externo | NO | Esperar |

---

## 6. Deuda tecnica

| # | Pendiente | Modulo | Tamaño | Evento | Tipo | Recomendacion |
|---|---|---|---|---|---|---|
| D1 | **BUG-347** `Http::preventStrayRequests()` + fake de `/internal/*` del socket y de la webapp en `TestCase.php`. La suite completa tarda 30 min y su resultado depende de si el socket esta prendido | API | horas a 1 dia | NO | Soporte | **Ya en Laravel.** Ademas es prerrequisito de las pruebas de contrato: no se pueden congelar snapshots con una suite que depende de servicios externos |
| D2 | BUG-348 reescribir el test de cache del Data Center para la invalidacion por observer | API | horas | NO | | Ya |
| D3 | Optimistic UI: chat tempId + ack + estados (2 a 3 h, cambia socket server y app) | socket + app | horas | NO | | **Congelar hasta decidir el socket**: cambia el protocolo del chat en Node, que se reescribiria en SignalR |
| D4 | Optimistic UI: emoji skip-self (cambia socket server) | socket | horas | NO | | **Congelar** (misma razon) |
| D5 | Optimistic UI: dedup `wall:comment`, Q&A upvote anti-parpadeo | web + app | horas | NO | | Ya (son solo cliente) |
| D6 | Tests funcionales de Mission Control (mock de token HMAC de `/monitor/{id}`) | API | horas | NO | | Ya |
| D7 | E2E de flujos criticos: aprobar Q&A, lanzar game, cancelar sesion, export programado | API + web | dias | NO | | **Laravel con DTO + contrato**: estos cuatro flujos son los que mas side effects tienen (observers, jobs, socket); un E2E que los fije es oro para la migracion |
| D8 | Unificacion de las 5 SPAs estaticas (`public/shared/tokens.css`, `components.css`, `lib.js`; migrar chat-monitor, attendance-check, display, event-pulse) | admin (estaticas) | 10 a 12 h | NO | | **Congelar hasta admin .NET**: esas cinco paginas son las que mas sentido tiene absorber en el admin nuevo en Next; unificarlas hoy es trabajo que se tira |
| D9 | Socket: emits que nadie escucha (`session:started/ended/cancelled`, `agenda:updated`, `room:occupancy`): consumir o quitar | socket | horas | NO | | **Congelar**: se resuelve solo al escribir el catalogo de eventos de la fase 0 de la migracion |
| D10 | Documentar arquitectura del socket | docs | horas | NO | Soporte | Ya en Laravel. **Es la fase 0 de la migracion**: catalogo de eventos, payloads, salas y claves de Redis |
| D11 | Paginas custom (iframes del organizador, 60% construido, huerfano): construir el acceso o demoler | admin + API + app + web | dias | NO | | **Congelar hasta admin .NET** y decidir ahi si se demuele (recomendado: demoler, nadie lo pidio) |
| D12 | White-label: `app.json` → `app.config.js` + estructura `clients/` | app | dias | NO | | Fase 2 |
| D13 | Backend nice-to-have verificados como no construidos: search server-side, `AttendeeResource` unificado, cancelar solicitud, score de match, sort del wall, leaderboard paginado, `points:awarded` | API | dias | NO | | **Congelar**: si alguno se necesita, nace en .NET. La unificacion de `AttendeeResource` si entra en la fase 0 (DTOs explicitos) |
| D14 | Kiosko: TODO de `totem_token` en la URL (`config.ts`, `RoomApp.tsx`) es documentacion de uso, no deuda | app | - | NO | | Nada |

---

## 7. Congelado hasta el admin nuevo en .NET, con justificacion

Regla: todo lo que vive en Filament o solo tiene sentido si el admin sigue siendo Filament. La justificacion comun es que el admin se va a reescribir (decision en evaluacion, `migracion-dotnet.md`), y cada hora puesta en Filament hoy se tira; la excepcion unica es la seccion 3.

| # | Pendiente | Tamaño si se hiciera en Filament | Justificacion para congelar |
|---|---|---|---|
| C1 | Renombrar "Highlights" a "Destacados" (`HighlightResource.php:23`) | horas | Cosmetico del admin viejo. En el admin nuevo nace con el nombre correcto. **Ojo:** si el cambio es en la API (`entity: 'highlights'`), tampoco: es contrato con 8 clientes del socket |
| C2 | Donde vive "Otorgar puntos manuales" (el motor lo soporta, no hay UI) | dias | Es una pantalla nueva de admin. Nace en .NET como endpoint + pantalla |
| C3 | Toggle de Silent Disco en el form de sesion | horas | Form de Filament |
| C4 | Borrar `EventOnboardingResource` (muerto) | horas | Se va con todo Filament |
| C5 | Credencial con etiqueta visible (VIP, Prensa): marcar tags en el admin | dias | UI de admin + campo; la parte web de la escarapela espera a esto |
| C6 | Keyvisual por superficie (`keyvisual_desktop` + `keyvisual_mobile` + 2 uploads con preview) | dias | 2 uploads en Filament. **Partir:** la columna y el campo en `EventBrandingController` pueden entrar ya con DTO + contrato; la UI de subirlos, en .NET |
| C7 | Hero modo texto: contrato unico de branding (type image o text) en 3 superficies | dias | Mismo caso: el contrato de branding va ya con DTO + contrato; el form de Filament no |
| C8 | Paginas custom del organizador (ver D11) | dias | Huerfano; decidir demoler en el admin nuevo |
| C9 | Configuracion de canales WhatsApp/SMS desde Filament | dias | Nice to have y UI de admin |
| C10 | Branded QR con logo | dias | No lo pidio nadie; si se hace, es un endpoint que nace en .NET |
| C11 | Landing builder en Filament; email builder visual; A/B testing de emails | semanas | Fase 2 y encima en Filament |
| C12 | **Manual del organizador** (M1 a M7, 30 paginas con capturas del admin) | semanas | Documenta pantallas de Filament que se van a reemplazar. Retomar cuando exista el admin nuevo; mientras, un manual de 2 paginas para el evento con lo que el operador toca (Mission Control, kiosko, Pulse) |
| C13 | Unificacion de las 5 SPAs estaticas (D8) | 10 a 12 h | Se absorben en el admin nuevo |
| C14 | Torre de control en Filament (A1) si Kamilo elige el camino (b) | semanas | Nace en .NET con los conectados del hub |
| C15 | Passkeys para el staff (S.9) | semanas | Toca el login de Filament; en .NET es `Fido2NetLib` sobre Identity. Sin fecha de todas formas |
| C16 | Cache de `auth/me` en Next; `data:sync` en vez de `data:invalidate` para refrescar solo lo que cambio (PENDIENTES §13) | dias | Cambia el protocolo del socket; se diseña con SignalR |
| C17 | Laravel Pulse como panel y canario automatico si no entran con la torre (§13) | horas | Pulse es PHP; si se usa para el evento (camino b de A1), es temporal y se sabe |

---

## 8. Fase 2 y nice to have (ya estaban diferidos, no cambian)

Agrupados como estan en PENDIENTES §12 y §13. Ninguno es del evento ni de seguridad. Recomendacion unica: **no hacer ahora; si un cliente lo pide, nace en .NET con DTO desde el dia uno.**

| Grupo | Items | Tamaño junto |
|---|---|---|
| Webapp diferidos al cerrar modulos | barra ⌘K, StaggerList y swipe con haptica, aviso a usuario sin contraseña, CapsLock y cuenta regresiva, filtros en URL, recap y certificado aparte, tarjetas grandes de sugeridos, paginas dinamicas, OG y sitemap autenticado, SSR a TanStack infinite, choque de favoritos, home terminado por estado, juegos (subasta, "donde esta el sponsor", speed-dating) | semanas |
| Analytics tracking | `social.profile_opened`, `connection_sent`, vistas por sponsor, speaker y sesion; no hay infra de analytics | semanas |
| App movil cosmetico | racha de visitas, orbe FAQ en Skia, venue y mapa, crop circular | dias |
| Registro y acceso avanzado | waitlist, referral, social login | semanas |
| Comunicacion avanzada | WhatsApp Business API, SMS fallback, email builder | semanas |
| Post-evento | networking follow-up, highlight reel, event replay | semanas |
| Seguridad avanzada | anomaly detection, backup y restore de evento en JSON | semanas |
| Features opcionales | wallet digital, digital signage, badge printing, juegos Unity | semanas |
| Showcase e inversor | paneles clickeables 1920x1080, audio, hints | dias |
| Fase 3 SaaS | multi-tenant, Stripe, export GDPR, Unity bridge, multi-location | meses |
| Infra despues del 6 de diciembre (§13) | consultas duplicadas de auth, COUNT de branding, re-medir techo por endpoint, pagina de estado publica, niveles de 10.000, otro proveedor con RTO/RPO, stress 10K, I.7.9 e I.7.10, revisar el viejo §6 de abril al cerrar B2 | semanas |

---

## 9. Decisiones abiertas de Kamilo (decidir, no codear)

| # | Pregunta | Que destraba |
|---|---|---|
| Q1 | ¿Cuando abre el registro de asistentes? Esa fecha fija cuando tiene que existir produccion | Todo el bloque 2 |
| Q2 | ¿Carga y caida de nodo (B6.1) obligatorias antes de evento real y opcionales en demos? | B2.3, B6.1 |
| Q3 | Umbrales verde/amarillo/rojo del canario | B4.4 |
| Q4 | W.19 Spatial Player: aprobar arquitectura y decidir 8.7 (tarjetas con miniatura) | W.19 |
| Q5 | Bintec: agenda en grilla por auditorio para varias salas | TODO.2 |
| Q6 | Torre de control: camino (a) Filament ahora o (b) alertas + Pulse para el evento y torre en .NET | A1, C14, C17 |
| Q7 | ¿Se puede volver a calificar una sesion ya calificada? (BUG-322) | contrato de `RatingController` |
| Q8 | Event Pulse: reglas del hero (ultimos 10 ingresos + lead individual): lab, aprobacion, codigo | Pulse |
| Q9 | Paginas custom: construir el acceso o demoler | D11, C8 |
| Q10 | App en tiendas para el 6 de diciembre, o solo webapp | E3, E4 |
| Q11 | Socket: emits que nadie escucha, consumir o quitar | D9 (va con el catalogo de eventos) |
| Q12 | **La grande:** migrar a .NET si o no, y si el admin se reescribe. Define que tan estrictos son "congelar" y "DTO + contrato" en este documento | Todo |

---

## 10. Lo que este documento NO cambia

- `docs/living/PENDIENTES.md` sigue siendo la fuente unica. Si Kamilo aprueba esta clasificacion, la recomendacion de cada item se copia alla como etiqueta y lo congelado se marca con "congelado hasta admin .NET" sin borrarlo.
- Las tareas de la migracion (fase 0: OpenAPI, pruebas de contrato, formato de fecha, catalogo de eventos, capa `realtime`) no estan aqui porque todavia no estan decididas. Estan en `docs/migracion-dotnet.md` seccion 6. Si se aprueban, entran a PENDIENTES como bloque propio y D1, D7 y D10 pasan a ser parte de el.
