# Decisiones de Kamilo

> **Unico lugar donde viven las decisiones** (decision 2026-10-22). El roadmap de la migracion y `living/PENDIENTES.md`
> solo enlazan aqui. Una linea por decision: fecha, que, por que. Lo que se decide sale de "Abiertas" y baja a
> "Tomadas" con su fecha. Decisiones mas viejas, por modulo, siguen en `living/COMPLETADO.md` y en `webapp/DECISIONS.md`.

## Tomadas

| Fecha | Que | Por que |
|---|---|---|
| 2026-09-30 | No se sigue con DigitalOcean hasta tener todas las features; servidor en pausa | El costo de pruebas de agosto no se justifica sin producto cerrado |
| 2026-10-12 | Resend se queda como proveedor de correo; el plan pagado entra en el costo de cada evento | No sumar proveedores |
| 2026-10-12 | Registro publico = un solo correo (bienvenida con enlace de acceso, `welcome_access`) | Un registro, un correo; menos ruido y menos costo |
| 2026-10-16 | No hay cliente para el 6 de diciembre: prioridad demo vendible; medir 5.000 espera a que haya cliente | Sin cliente no se paga la medicion grande |
| 2026-10-16 | `living/PENDIENTES.md` es el unico lugar de tareas abiertas | Una sola lista; los demas docs no cuentan |
| 2026-10-21 | El evento del 6 de diciembre se cayo; no entran mas pendientes | Se evalua migrar a .NET |
| 2026-10-21 | D.1: dale a la fase 0 de la migracion | Tres semanas que mejoran PHP aunque la migracion no siga |
| 2026-10-21 | Regla del cliente: si un cliente paga antes de la semana 12, se le entrega con PHP y la migracion se pausa | No discutirlo con el cliente encima |
| 2026-10-21 | Un solo formato de fecha en la API: ISO 8601 con el offset de la zona de la app, sin fracciones (M0.3.2) | La app y la webapp agrupan la agenda con `split('T')[0]`; con UTC las sesiones de la noche cambian de dia |
| 2026-10-21 | En el MSI primero va 0.2 (suite) y la instalacion de Docker/Redis/.NET queda para el arranque de la fase 1 | Orden: red de pruebas antes que infraestructura |
| 2026-10-21 | Speakers en escritorio conserva su animacion de abrir | Se ve bien como esta |
| 2026-10-22 | M0.3.7 (pasar respuestas a Resources en PHP) sale del roadmap | El contrato ya lo fijan los 330 snapshots; ordenar codigo que se apaga en la fase 5 no aporta |
| 2026-10-22 | Cuenta importada sin activar: el codigo de activacion viaja solo por correo, nunca en la respuesta de `check-email` (BUG-363) | Quien sabia el correo podia ponerle su contraseña |
| 2026-10-22 | Reglas de la migracion fijadas en `BUENAS-PRACTICAS-MIGRACION.md` | Fijarlas antes de la primera linea de C# |
| 2026-10-22 | Unicidad de jobs en Hangfire: filtro propio (o verificacion antes de encolar), escrito una vez en `Worker` y reutilizado | `DisableConcurrentExecution` no equivale a `ShouldBeUnique` |
| 2026-10-22 | Fechas en C#: offset de la zona de la app; las 22 columnas con hora de la base se escriben explicitas | Igual que PHP hoy; la hora de la base puede no ser la de Bogota |
| 2026-10-22 | Politica JSON global + lista cerrada de excepciones por campo en un solo archivo; nada entra a la lista sin decision aqui | Que el contrato no se deforme endpoint por endpoint |
| 2026-10-22 | Endpoint de reinicio de la base solo para pruebas, por variable de entorno y ausente en produccion; pruebas que no corren contra C# llevan etiqueta y comparan forma; ruleta y sorteo aceptan semilla en modo pruebas | Que los snapshots corran contra C# en cada push |
| 2026-10-22 | Un bloque del roadmap por sesion, un commit por tarea; un bug que aparece se registra y se arregla solo si bloquea o si Kamilo lo decide | Avance visible sin desviarse |
| 2026-10-22 | `decisiones.md` es el unico lugar de las decisiones | Estaban en tres lugares |
| 2026-10-22 | Anexo A aprobado con 24 nombres camelCase (entran los que arma el socket hoy; sale `targetAttendeeId`) | Son los que ya reciben los clientes; C# los respeta tal cual |
| 2026-10-22 | BUG-366 y BUG-367 se arreglan ya en PHP (backend b060f2b y 3add5d4) | Son de una linea y PHP sigue en uso hasta la fase 5 |
| 2026-10-22 | M0.4.6: fuera `agenda:updated` y `room:occupancy`; el cartel de anuncios sobre el video se reconecta a la lista de la campana | Nadie los escuchaba; el cartel era una funcion perdida desde julio |
| 2026-10-22 | BUG-370: Mission Control y el display usan un token acotado (sus rutas, su evento, su sesion), unica excepcion a D.1 (opcion A) | El operador sigue entrando por enlace firmado sin cuenta; el enlace ya no da admin completo |
| 2026-10-22 | El recap de la app (trabajo de julio sin commit) no se commitea: queda archivado en una rama local y pasa a funcion pendiente de rediseño y reparacion. Haptics y reintentos de red del mismo archivo, aparte | No funcionaba bien y se veia mal; es sobre todo diseño |
| 2026-10-22 | Cuando se arregla cada bug: lo critico ya (BUG-372); seguridad en el bloque 0.8 al cierre de la fase 0 (auditoria, BUG-373, BUG-374, lo que se confirme); lo solo-Filament que no es de seguridad va a la lista "no repetir" del admin nuevo (M2.8.5), salvo que aparezca un cliente antes de la semana 12 | Que C# arranque limpio y no se gaste trabajo en codigo que se apaga |
| 2026-10-22 | Auditoria: los 56 candidatos se verifican por grupos de subsistema (un verificador independiente por grupo y una segunda revision por grupo) dentro de los 50 agentes, en vez de uno por uno (habrian sido ~115) | Mismo doble control para todos sin multiplicar el gasto; el informe lo declara |
| 2026-10-22 | Auditoria de seguridad (M0.8.1): tamaño estandar con presupuesto de ~50 agentes (completa: cada hallazgo con sus dos verificaciones), backend + socket, solo lectura de codigo (sin entorno aislado: Docker apagado y sin imagen PHP local); la confirmacion con prueba que falla va en M0.8.6 | El estimado inicial de 25 no alcanzaba para verificar todo |
| 2026-10-22 | Auditoria de seguridad del backend a proposito, aparte del roadmap (permisos ruta por ruta, inyeccion, limites, datos de mas) | Las pruebas de contrato congelan lo que hay, aunque este mal; BUG-363 aparecio de rebote. Va al cierre de la fase 0, antes de pasar rutas a C# |

## Abiertas

### Migracion a .NET
- **D.2** Orden admin → tiempo real → API publica (roadmap) o el clasico API primero (`migracion-dotnet.md` §5). Recomendado: el del roadmap.
- **D.3** Alcance del admin del hito 1: modulos de demo (eventos, agenda, speakers, sponsors, asistentes, branding, modulos, anuncios, login con 2FA) y el resto despues (M2b), o los 45 resources antes de mostrar nada. Recomendado: demo primero.
- **D.4** Torre de control: version minima en C# dentro del admin nuevo (M2.9) y nada en Filament. Recomendado: si.
- **D.5** App en tiendas: no publicar build antes de la fase 3 (cada build congela el cliente de Socket.IO semanas). Si un cliente la pide antes, la fase 3 se adelanta.
- **D.6** Nombre y repo del servicio C# (propuesta: `eventos-api-dotnet`, .NET 10 LTS, un repo con `Api`, `Hub`, `Worker`, `Contracts`, `Tests`).
- Zona horaria por evento (hoy toda la API usa la de la app): se decide despues de la fase 5.

### Producto (venian de `PENDIENTES.md` §2)
- ¿Cuando abre el registro de asistentes? Esa fecha fija cuando tiene que existir produccion.
- ¿Carga y caida de nodo (I.7.7) obligatorias antes de evento real y opcionales en demos? (`project_orquestador_pipeline`)
- Umbrales verde/amarillo/rojo del canario: hoy propuesta (`infra/PLAN-EXPERIENCIA-ENTERPRISE.md:37`).
- W.19 Spatial Player: aprobar la arquitectura propuesta 2026-09-13 y decidir 8.7.
- Bintec: ¿agenda en grilla por auditorio para eventos de varias salas? (`REFERENCIA-BINTEC-2026.md:23`, :106)
- Admin: renombrar "Highlights" a "Destacados" (`HighlightResource.php:23`).
- Admin: donde vive "Otorgar puntos manuales" (el motor lo soporta, no hay UI).
- Admin: ¿vuelve el toggle de Silent Disco al form de sesion?
- Agenda: ¿se puede volver a calificar una sesion ya calificada? Hoy queda de solo lectura (BUG-322).
- Event Pulse: reglas del hero (tarjetas que agregan "los ultimos 10 ingresos" + lead individual).
- Paginas custom (iframes del organizador): construir el acceso o demoler.
