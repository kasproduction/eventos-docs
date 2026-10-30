# Completado — EventOS

> Historial de todo lo implementado, organizado por area.
> Consultar para contexto historico. El dia a dia es `PENDIENTES.md`.
> **NOTA:** Los numeros BUG-XXX en este archivo son historicos. La numeracion fue reorganizada el 2026-04-23.
> Fuente de verdad para bugs: `docs/BUG-LOG.md` (numeracion secuencial BUG-001 a BUG-232).
> Actualizado: 2026-10-28

---

## 2026-10-29: documentos en C# y en el admin nuevo (M2.3.3c1), y M2.3.7 y M2.3.8 cerradas

- **M2.3.3c1** documentos: lista, crear, editar, borrar y reordenar en C# con los efectos de `DocumentObserver` y `ContentObserver`, subida de PDF, Word y PowerPoint modernos, JPG y PNG (un `.doc` o `.ppt` viejo se rechaza diciendo "guárdalo como .docx o .pptx"), pantalla en el admin nuevo y Filament de solo lectura. El ZIP lo arma `RegenerarZipDeDocumentos` en Hangfire (M4e.6 adelantada) y `GET /events/{eventId}/documents/zip` se cruzo a C# con `cruzar.sh`; el job de PHP se apago. BUG-489 (Filament, reordenar documentos).
- **M2.3.7 y M2.3.8** cerradas como verificacion: `CoberturaDelBloqueTest` recorre las escrituras del admin y exige efectos, prueba de C# y cruzada por recurso. Los contadores del roadmap se recalculan con `scripts/contadores-del-roadmap.sh` (quedaron en 95/211).

---

## 2026-10-29: sponsors en C# y en el admin nuevo (M2.3.3b)

- **M2.3.3b** sponsors con servicios, trivias del stand, lider y equipo, y el buscador de asistentes comun (tope de 20) para cualquier selector del admin. Una trivia con respuestas no se borra, se apaga. Pruebas: C# 248, cruzadas 31/31, vitest 709, e2e admin 27/27. Roadmap 91/210.

---

## 2026-10-29: subidas de archivos (M2.3.5)

- **M2.3.5** boton "Subir archivo" en el admin nuevo (foto del speaker, slides del login, destacados, preguntas y miniatura de sesion) sobre `POST /api/admin/v1/events/{eventId}/uploads` de C#: R2 con AWSSDK.S3 y disco publico de respaldo, mismo esquema de nombres y URL que `StorageService`; tipo leido del contenido, SVG fuera. La miniatura de sesion queda en el disco publico (decision de Kamilo). Pruebas: C# 241, cruzadas 29/29, vitest 708, e2e admin 25/25. Roadmap 90/210.

---

## 2026-10-28: speakers e Identidad en C# y en el admin nuevo (M2.3.3a)

- **M2.3.3a** speakers (con sus sesiones), slides del login, destacados y preguntas frecuentes en la API del admin de C#
  con los efectos exactos de PHP, y sus pantallas en el admin nuevo (Contenido, Speakers; Experiencia, Identidad con
  pestañas por permiso). C# `d729f4c`, web `975437d`, backend `a7c9697`. Cruzadas 27/27 con control, e2e admin 24/24.
- **BUG-484 y BUG-485** anotados y confirmados (PHP; C# no los tiene).

## 2026-10-27: sesiones y push en C# (M2.3.2c1 y c2)

- **M2.3.2c1** sesiones en el admin nuevo con los efectos de `SessionEffects` (C# `b87cd76`, web `1dd6128`, backend `c9ce3ce`).
- **M2.3.2c2** Hangfire en el Worker y aviso de cambio de horario por Expo y Web Push; borrado de PHP (C# `b38f965`,
  backend `e3ab3c3`). Probado en telefono y en Chrome real.
- **M2.3.2c3** el ciclo de Mission Control (start, end, cancel, delay) en C#, las cuatro rutas cruzadas con ida y vuelta
  probada en el Nginx local, y Sesiones de Filament en solo lectura. M2.3.2c cerrada; BUG-380 cerrado.
- **BUG-480, BUG-481, BUG-482** corregidos (conteos con la papelera, carrera de filtros, claves argon2id en el ingreso).

## 2026-10-25: fase 1 en C# (31/33; solo faltan las dos tareas del servidor)

- **Bloque 1.1** repo `eventos-api-dotnet`, Docker solo Linux, health/version, modo contratos, analizadores (C# `8f5baee`).
- **Bloque 1.2** datos: conector Microting, scaffold, fechas en hora de Bogota, bool y JSON, `ReportesDb`, suite PHP en
  MySQL (BUG-458 a 463). Falta M1.2.5 (servidor).
- **Bloque 1.3** autenticacion compatible: bearer de Sanctum, staff rechazado, token de operador, `/auth/me` identico
  (primer contrato contra C#), filtros de acceso, cifrado de Laravel, filtro por evento cerrado por defecto en 86 tablas
  (C# `c11cb3d` a `c4c4ba8`). D.7 y D.8 decididas.
- **Bloque 1.4** politica JSON global, errores de Laravel (422 con lang/es, 404, 405, 429, 500), limitador en Redis con
  proxies de confianza, cache `dotnet:` con `eventos:invalidate` (C# `ca47dfe` a `f5c71bc`).
- **Bloque 1.5** Nginx reparte por ruta (decision: Nginx), `cruzar.sh`/`descruzar.sh` con respaldo, `rutas-duenos.md`
  generado, HEAD como Laravel, OpenAPI de C# contra el de PHP, panel por ruta, `ARQUITECTURA.md` v1, k6 en el MSI y
  primera medicion: `/auth/me` con 7 veces menos CPU por pedido en C# (`infra/MEDICION-PHP-VS-CSHARP.md`). Falta M1.5.6.
- **Retro M1.6.1** en el roadmap. **Decisiones de Kamilo:** D.2, D.3, D.4, D.9 (regla 29) y M2.1.3 (admin en Next dentro
  de eventos-web). **WHITE-LABEL** corregido (app propia solo desde la cuenta del cliente).
- **Bugs:** BUG-456 a 468 (468 abierto, BAJA). C# 126 pruebas; 6 contratos de PHP pasan contra C#.

## Movidos de PENDIENTES el 2026-10-25 (cerrados entre el 21 y el 25 de octubre)

### De "3. BUGS ABIERTOS (detalle en `BUG-LOG.md`)"

- [x] BUG-358 Error de hidratacion React #418: RESUELTO 2026-10-22 (horas con espacio fino; eventos-web `2ff14a6`)
- [x] BUG-359 Falta `mobileNav` en `messages/pt.json`: RESUELTO 2026-10-22 (eventos-web `9dab432`)
- [x] BUG-464 PHP elegia el registro o ban mas nuevo solo por fecha: RESUELTO 2026-10-25 (backend `28f9daa`)
- [x] BUG-465 Las rutas GET de C# no respondian HEAD: RESUELTO 2026-10-25 (C# `90d30bd`)
- [x] BUG-466 C# confiaba en cualquier proxy con la lista vacia: RESUELTO 2026-10-25 (C# `54178fa`)
- [x] BUG-467 C# escuchaba en todas las interfaces: RESUELTO 2026-10-25 (C# `90d30bd`)
- [x] BUG-396 El reto 2FA del panel no cuenta para el bloqueo un codigo TOTP incorrecto repetido en intervalos distintos (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-397 check-email anonimo rota el codigo de activacion y reenvia la invitacion en cada llamada (M0.8.8): RESUELTO 2026-10-24 (M0.8.8)
- [x] BUG-398 Los limites por correo normalizan con strtolower y trim, pero MySQL compara sin acentos ni mayusculas: cada... (M0.8.8): RESUELTO 2026-10-24 (M0.8.8)
- [x] BUG-399 Desactivar una cuenta (is_active = false) solo frena el login con contraseña: el magic link y los tokens... (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-400 El enlace de restablecer contraseña se arma con el Host de la peticion (sin TrustHosts ni raiz forzada) (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-401 verify-identity entrega el codigo de activacion a quien sepa el correo y el telefono de un importado (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-402 Un codigo de acceso con un espacio intercalado se valida pero no gasta usos (M0.8.8): RESUELTO 2026-10-24 (M0.8.8)
- [x] BUG-403 El limitador del handoff cuenta por un correo que la peticion no trae: 5 canjes por hora para toda la... (M0.8.8): RESUELTO 2026-10-24 (M0.8.8)
- [x] BUG-404 El registro entrega sesion para un correo nunca probado y las reglas que confian en el correo la aceptan (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-405 El embed publico /dc-embed entrega todas las estadisticas internas, incluidas IP y nombre de los admins y la... (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-406 Staff y permisos: con manage-users (event_admin, org_admin) se puede subir el propio rol hasta super_admin... (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-407 El Data Center y /dc-embed pintan nombres de asistentes y de eventos sin escape en innerHTML (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-408 Los exports del Data Center quedan en almacenamiento publico con nombres que se pueden adivinar (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-409 Los exports CSV/XLSX guardan como formula los textos de asistentes que empiezan con '=' (M0.8.8): RESUELTO 2026-10-24 (M0.8.8)
- [x] BUG-410 Las variables de las plantillas de correo se insertan como HTML sin escape, incluidas las que controla otra... (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-411 Peticiones salientes a URLs configuradas por staff del evento sin politica de destino (ZIP de documentos,... (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-412 chat-monitor.html toma stream_iframe y stream_url de la URL, sin token, y los pinta como HTML en el origen... (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-413 XSS almacenado en Event Pulse: el nombre del asistente se pinta con innerHTML sin codificar, en el mismo... (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-414 El embed de stream que guarda un rol admin* del asistente se ejecuta en Mission Control con el origen del... (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-415 Un admin de un evento puede lanzar un control de asistencia sobre un salon de otro evento: push a sus... (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-416 Los enlaces firmados de Mission Control y del display no vencen ni se pueden revocar, y el de una sesion... (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-417 El token de Mission Control y del display se sale de su evento y su sesion: solo se ata a los parametros de... (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-418 El manifiesto del totem entrega el QR estatico (credencial reutilizable) de todos los asistentes con check-in... (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-419 GameController::authorizeAdmin acepta al admin* de cualquier evento para operar juegos, exportar... (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-420 Las acciones admin de encuestas no revisan rol ni evento: cualquier asistente crea, activa, cierra, modera y... (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-421 POST y DELETE /api/v1/admin/uploads no exigen ningun rol: cualquier asistente sube archivos de cualquier tipo... (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-422 StorageService::upload conserva la extension del cliente: en el disco public un .php subido por... (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-423 Un anuncio 'Programado' llega a los asistentes antes de su hora: la API y el socket solo miran que... (M0.8.8): RESUELTO 2026-10-24 (M0.8.8)
- [x] BUG-424 Las restricciones de audiencia se aplican solo al listar: /pages/{id} ignora 'Quien la ve' y los datos de los... (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-425 El ban y la aprobacion se revisan sobre el ultimo evento activo del usuario, no sobre el evento de la ruta (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-426 Rutas de contenido del evento solo piden sesion: un asistente de cualquier otro evento lee enlaces de... (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-427 PUT /me/registration-fields deja cambiar campos que el organizador bloqueo ('Editable despues del registro'... (M0.8.8): RESUELTO 2026-10-24 (M0.8.8)
- [x] BUG-428 Quitar el rol de admin a un asistente no invalida su ficha del socket: conserva poderes de admin en el chat y... (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-429 Banear no borra la ficha de autenticacion del socket si el indice del usuario ya vencio: el baneado reconecta... (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-430 El check-in de puerta acepta registros pendientes de aprobacion con el QR estatico que reciben al registrarse... (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-431 staff-checkin/unassign deja a un staff de check-in bajar a 'attendee' a cualquier asistente del evento,... (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-432 La restriccion 'Quienes ganan estos puntos' del admin no se aplica al otorgar puntos (M0.8.8): RESUELTO 2026-10-24 (M0.8.8)
- [x] BUG-433 Acciones de una sola vez (completar intereses y check-in) se premian sin referencia y no se deduplican (M0.8.8): RESUELTO 2026-10-24 (M0.8.8)
- [x] BUG-434 Canje de premios: el saldo se calcula con SUM sin bloqueo y canjes simultaneos de premios distintos gastan... (M0.8.8): RESUELTO 2026-10-24 (M0.8.8)
- [x] BUG-435 Trivia en vivo: el puntaje usa el tiempo que manda el cliente y la ronda sigue abierta despues de revelar la... (M0.8.8): RESUELTO 2026-10-24 (M0.8.8)
- [x] BUG-436 Registrar la vista de un patrocinador no valida que sea del evento ni deduplica, e infla las estadisticas de... (M0.8.8): RESUELTO 2026-10-24 (M0.8.8)
- [x] BUG-437 Cambiar o quitar el lider del stand desde el admin no le quita el acceso al pool de leads: el lider anterior,... (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-438 El cupo de colaboradores del stand no se revalida al aceptar y ni el lider ni el staff pueden retirar... (M0.8.8): RESUELTO 2026-10-24 (M0.8.8)
- [x] BUG-439 El lider de un stand obtiene correo, empresa y rol de cualquier asistente del evento sin que este haya dado... (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-440 Un registro pendiente de aprobacion entra al socket con acceso completo al evento (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-441 Una cuenta sin asistente en un evento activo recibe el rol 'admin' en el socket y puede moderar y proyectar... (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-442 El token de solo lectura del display, y el acotado de Mission Control, dan poderes de admin en el socket (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-443 Los tokens revocados en bloque siguen abriendo el socket hasta 24 horas (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-444 Las acciones de moderacion y proyeccion del socket no se atan al evento de la sesion (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-445 Un mensaje de un remitente sin asistente (Pulse o cuenta sin evento activo) hace fallar el guardado en lote... (M0.8.8): RESUELTO 2026-10-24 (M0.8.8)
- [x] BUG-446 Un evento de socket sin payload tumba el proceso del servidor (M0.8.6): RESUELTO 2026-10-23 (M0.8.6)
- [x] BUG-447 /internal/session-config acepta un secreto vacio si INTERNAL_SECRET no esta definido (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-448 Borrar un mensaje del chat por la API (DELETE /admin/chat/messages/{id}) no lo saca del historial que el... (M0.8.8): RESUELTO 2026-10-24 (M0.8.8)
- [x] BUG-449 Las preguntas de Q&A pendientes de moderacion (texto y autor) se reparten a toda la sala de la sesion (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-450 La asistencia virtual que decide el certificado y el nivel sale de la sala del socket sin atarse al horario,... (M0.8.8): RESUELTO 2026-10-24 (M0.8.8)
- [x] BUG-451 checkin:update reparte a todo asistente conectado el nombre, rol, id y stand de cada persona que hace... (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-452 Bloquear a un contacto aceptado no le quita el acceso: sigue en Mis contactos y sigue viendo las historias de... (M0.8.7): RESUELTO 2026-10-23 (M0.8.7)
- [x] BUG-373 Banear desde el admin no saca del socket en el momento: RESUELTO 2026-10-23 (M0.8.2, BanService)
- [x] BUG-374 El admin muestra eventos de todas las organizaciones: RESUELTO 2026-10-23 (M0.8.3, staff por organizacion y eventos asignados)
- [x] BUG-376 Cambiar rol: reglas distintas en admin y API; confirmar quien sube a admin (M0.8.4): RESUELTO 2026-10-23 (M0.8.4)
- [x] BUG-384 Webhooks: "Simular evento" manda asistentes inventados como reales; reenviar triplica (M0.8.4): RESUELTO 2026-10-23 (M0.8.4)
- [x] BUG-385 Secretos de webhooks a la vista en notificaciones y en un .txt (M0.8.4): RESUELTO 2026-10-23 (M0.8.4)
- [x] BUG-386 Importar asistentes: contraseña igual al telefono, enlaces rotos, archivos sin borrar (M0.8.4): RESUELTO 2026-10-23 (M0.8.4)
- [x] BUG-387 Paginas del admin sin permiso propio y sin permisos por accion (M0.8.4): RESUELTO 2026-10-23 (M0.8.4)
- [x] BUG-389 Resultados de cualquier encuesta sin token en `/display/polls/{id}` (M0.8.4): RESUELTO 2026-10-23 (M0.8.4)
- [x] BUG-390 `/dc-embed` con `X-Frame-Options: ALLOWALL` (M0.8.4): RESUELTO 2026-10-23 (M0.8.4)
- [x] BUG-391 Secretos con valor por defecto y faltantes en `.env.example` (M0.8.5): RESUELTO 2026-10-23 (M0.8.5)
- [x] BUG-392 Insignia "reclamado" de Golden Tickets con el estilo del modal (diseño): RESUELTO 2026-10-24 (M0.6.1)
- [x] BUG-393 Banner del concurso de fotos sin borde (diseño): RESUELTO 2026-10-24 (M0.6.1)
- [x] BUG-394 `AttendanceCheckModal` no compila en TypeScript (solo tipos): RESUELTO 2026-10-24 (M0.6.1)

### De "Navegacion inmediata (Kamilo 2026-10-18) — 8/9"

- [x] Sin skeleton al cambiar de modulo; el menu marca el destino en el toque; sesion 30 s en memoria; precarga al
  pasar el mouse, al apoyar el dedo y en reposo (agenda, social, speakers). eventos-web `368fe81`. Medido local:
  precargados 49-106 ms, el resto 324-454 ms sin skeleton (antes 275-654 ms con skeleton)
- [x] Labs de transicion (`design/features/navigation/`): v1, v2, C2 camara (descartada: marea), v3 sutiles, **ios**
  (aprobado). Cambio de modulo con View Transitions en `TransicionModulo` (eventos-web `bb2cf2a`)
- [x] BUG-357 corregido 2026-10-21: solo cambia el contenido de adentro, marco quieto, salida (120 ms) y entrada (zoom 2%,
  395 ms) en secuencia. Lab `lab-transicion-contenido.html` (A Relevo escogida); eventos-web `f9e6bfc`
- [x] Abrir con zoom desde la tarjeta: **decision Kamilo 2026-10-21, speakers en escritorio se queda como esta** (el
  panel ya entra deslizando con fundido). Lab `lab-abrir-desde-tarjeta.html` (A crece hasta su sitio, B nace y se
  asienta, celular como el lab iOS) queda de referencia por si se retoma para celular, sesiones o patrocinadores
- [x] Choque en celular entre el push del detalle y la transicion de modulo: corregido 2026-10-21 (eventos-web
  `lib/transicionModulo.ts`): dentro de un detalle se conserva la clave del modulo de origen, entrar y volver no
  disparan la transicion. Verificado con Playwright (0 transiciones al abrir y volver, push y pop intactos)
- [x] Tokens de prueba del asistente sembrado (277, 278, 279) borrados 2026-10-21 con permiso de Kamilo

### De "Sueltos de la webapp (inventario 2026-10-16)"

- [x] BUG-455 La webapp usa un evento fijo (`DEFAULT_SLUG`) y no el de la persona como Expo; sin inscripcion, pantalla propia como baneado o pendiente (lab antes): RESUELTO 2026-10-24 (M0.6.1)

### De "Recap de la app: archivado, pendiente de rediseño y reparacion (antes "WIP sin commitear", 2026-07-04)"

- [x] BUG-392 y BUG-393 con lab de diseño (M0.6.1).: RESUELTO 2026-10-24 (M0.6.1)

## 2026-10-22 (tercera parte): bloque 0.4 cerrado, tiempo real por una sola puerta

- **BUG-366** Mission Control se entera del fin de la trivia (`b060f2b`). **BUG-367** cifras del Data Center al dia
  apenas cambia algo (version por evento, `3add5d4`).
- **M0.4.2 / M0.4.3** webapp, app, kiosko y las 5 paginas sueltas hablan con el tiempo real por un solo archivo cada uno
  (eventos-web `e708bcf`, eventos-app `c461d0c`, eventos-kiosko `e6412c2`, backend `79194af`).
- **M0.4.5** canal Redis `eventos:invalidate` para la cache de C# (`a8bf77e`).
- **M0.4.6** fuera dos avisos que nadie escuchaba (`f8c3cde`, BUG-369 escaneo de sala mas rapido); cartel de anuncio sobre
  el video de vuelta (eventos-web `dcf4079`, BUG-368). Anexo A aprobado (24 nombres).

## 2026-10-22 (segunda parte): bloque 0.3 cerrado, catalogos del socket y de Redis

- **M0.3.11:** escenario `en-sala`; sorteo con ganador y ruleta con participantes congelados (302 pruebas). Arnes:
  BUG-364 (todos los pasos corrian como el primero que entraba) y BUG-365 (Redis de desarrollo compartido; ahora base 15
  vaciada por prueba). Backend `749d312`, `c65ae71`.
- **M0.4.1:** `docs/contratos/eventos-socket.json` (61 eventos: 51 + 10) y `npm run catalogo` en eventos-socket
  (`c090f80`), que corrigio los tipos de inicio y fin de sesion. Anexo A pasa a 24 nombres (pendiente de aprobar).
- **M0.4.4:** `docs/contratos/claves-redis.md` (bases, prefijos, dueños, formato y vida). Docs `d1136f8`, `a67633d`, `0686cff`.
- Registrados sin tocar: BUG-366 (Mission Control no se entera del fin de la trivia) y BUG-367 (cache del Data Center no se invalida).

## 2026-10-22: contratos de las 247 rutas, reglas de la migracion, dos bugs criticos cerrados

- **Contratos completos** (M0.3.3 a M0.3.10): 300 pruebas, 330 respuestas, 247/247 rutas; corren tambien por HTTP contra
  otro backend (probado contra el propio PHP: 289 + 11 solo-php) y con orden inverso para cazar listas sin orden.
  Backend `90e61f8`, `c65edf1`, `66c9892`, `a3bd325`. M0.3.7 sacado por decision de Kamilo.
- **BUG-362** (correo de acceso del registro publico perdido en la cola) y **BUG-363** (codigo de activacion expuesto en
  `check-email`) cerrados con pruebas de regresion (`90e61f8`, `80aef0b`). Cast del ZIP de documentos.
- **Docs:** `BUENAS-PRACTICAS-MIGRACION.md`, `decisiones.md` (unico lugar), `plantillas/CLAUDE-dotnet.md`, roadmap 17/187.
- Texto original de lo cerrado: M0.3.3 a M0.3.10 del roadmap de migracion (ver el roadmap para el detalle).

## 2026-10-21 (segunda parte): migracion a .NET decidida en evaluacion, fase 0 arrancada

- **Laravel 12.70.1** y `composer audit` en cero (eran 63 avisos, 3 criticos): backend `dac3429`. Guardia BUG-344 en
  `tests/TestCase.php`. Regresiones cazadas y cerradas: BUG-360 (CSRF del 2FA con Laravel 12) y BUG-361 (ruta de lo mio
  tapaba `support/mine`).
- **Suite sin dependencias externas** (BUG-347, BUG-348): 1.122 pruebas en 153 s sin socket ni webapp (backend `4cedb3e`).
- **Contratos de la API:** `ContratoSeeder` + `tests/Contratos/` con 103 snapshots + `docs/api.json` OpenAPI (backend
  `6c4087c`). **Un solo formato de fecha** (offset del evento) en toda la API (backend `ebaef93`).
- **Docs:** `docs/migracion-dotnet.md`, `docs/pendientes-2026-10.md`, `docs/roadmaps/ROADMAP-MIGRACION-DOTNET.md`.
- Texto original de las tareas cerradas:

- [ ] BUG-347 Los tests llaman al socket y a la webapp reales: falta `preventStrayRequests` y simular `/internal`
      (la suite tarda ~30 min)
- [ ] BUG-344 Pest con config cacheada corre contra MySQL de dev: TestCase debe abortar si no es sqlite (mitigado)
- [ ] BUG-348 Test de cache del Data Center falla siempre: reescribirlo para la invalidacion por observer
      (`DataCenterApiTest.php:76`). Prioridad baja
- [ ] `composer audit`: 57 avisos en 19 paquetes sin revisar

## 2026-10-16: tres bugs de riesgo y decisiones de Kamilo

- **BUG-346 el admin ya no se traba** si el tiempo real está caído: `emitConfigUpdate` con `timeout(2)`; los 3 jobs de push a Expo con `timeout(10)`; `tests/Unit/HttpTimeoutTest.php` revisa todo `app/` (backend a20df92). Session + live-config 57/57.
- **Horas corridas:** la webapp fija `TZ=America/Bogota` al arrancar (`instrumentation.ts`, respeta TZ del despliegue); arregla de una vez las ~25 partes que formatean con la zona del proceso (eventos-web 95f52ca, vitest 3/3).
- **Límite en rutas de cuenta:** `throttle:cuenta_correo` (registro, olvidé mi contraseña: 5/h por correo, 600/h por red) y `throttle:cuenta_enlace` (activar, restablecer: 10/h por enlace o correo, 300/min por red); umbrales en `config/auth.php` puerta (LimitesDeCuentaTest 5/5).
- **Decisiones:** no hay cliente aún; servidor sigue en pausa; bajar el costo de pruebas al retomar.
- Texto original de las tareas cerradas:

- [ ] ¿El cliente del 6 de diciembre ya esta confirmado?
- [ ] Costo de pruebas vigente (~$120/mes bases, ~$6,5/dia droplets): confirmar en P.1
- [ ] BUG-346 El aviso de config de sesion al socket no tiene timeout: si el socket cae, PHP queda bloqueado 10 s
      (`SessionConfigController.php:495`). Prioridad alta
- [ ] Horas en vistas mobile de la webapp sin `timeZone`: en un servidor UTC el render da 5 h de diferencia y error
      de hidratacion (`AgendaMobile.tsx:60`, `HappeningNowM.tsx:183`, `SessionDetailM.tsx:50`, `SpeakerDetailM.tsx:28`,
      `SponsorDetailM.tsx:43`, `AttendeeDetailM.tsx:33`, `VendorHappeningNowM.tsx:185`). **Antes de produccion**
- [ ] Sin rate limit propio: `/forgot-password`, `/register`, `/activate`, `/reset-password`
      (`eventos-backend/routes/api/auth.php:8-15`). Va con B.5

---

## 2026-10-21 — BUG-357, grupo 1 de B.1 y detalles sin doble transicion

- [x] BUG-357: cambio de modulo mueve solo el contenido, marco quieto, salida y entrada en secuencia (eventos-web
      `f9e6bfc`; lab `lab-transicion-contenido.html`, letra A Relevo escogida por Kamilo).
- [x] B.1 grupo 1: speakers, destacados, branding y en vivo una vez por evento en memoria del servidor; Live arma "lo que
      sigue" desde la agenda partida (eventos-web `8759e9e`, backend `6f148ad`). Medido: home 15 → 12, speakers 7 → 6,
      live 8 → 6, agenda 6 → 5, detalle de speaker 8 → 6 pedidos por pantalla.
- [x] Entrar o volver de un detalle no dispara la transicion de modulo encima del push del celular (eventos-web `43a4fd8`).
- [x] Abrir con zoom desde la tarjeta: decision Kamilo, speakers en escritorio se queda como esta; lab
      `lab-abrir-desde-tarjeta.html` de referencia.
- [x] B.1 grupo 2: patrocinadores, ranking y muro partidos en lo de todos (memoria del servidor) y lo mio; segundo pedido
      de calificaciones en Speakers quitado (eventos-web 69828f4, f1ef98e, 1b2e2cc, 43f49b9; backend 136849e, e7ed96d,
      14dfb59). Pest 21 tests nuevos, vitest 679.
- [x] B3.9 lo mio en un solo pedido: `GET /events/{id}/mine?piezas=` con estado por pieza (backend 85fd4c0, 535161a) y
      `lib/loMio.ts` que agrupa por evento y token (eventos-web 4d3d92a, 39c7d39). Medido: home 15 → 10, speakers 7 → 5,
      desafio 11 → 9. LoMioTest 9, vitest 684.

## Agosto a octubre 2026 + limpieza de pendientes — 2026-10-16

> Desde el 1 de agosto lo cerrado se quedo en los pendientes. El 2026-10-16 se movio todo aca.
> Primero el resumen de lo hecho en esos meses; despues el texto original de cada bloque, tal cual estaba.

### Resumen de lo cerrado entre agosto y octubre

- **2026-08-01/02 — Demo en produccion real + stress test.** 8 escenarios en verde, 25 bugs, luego destruido
  con snapshot. Medido: ~70 req/s y ~1.000 sockets por droplet. I.0 (errores que rompen un evento) 3/3.
- **2026-08-17/18 — Nivel 1 montado y medido.** 300 personas + 3 apagones = 0 errores; 5/5 de lo que destapo.
  Socket en cluster, ficha de sesion compartida en Redis, indices de juegos.
- **2026-09-07 — Demo local sin desplegar** (`demo/`, scripts DEMO-BUILD / START / STOP) y demo de 5 dias.
- **2026-09-13 — Auditoria contra codigo** + B1.1 instalacion limpia sin demo (`eventos:instalar`) + B1.2 primer
  ingreso al wizard. Contadores viejos saneados.
- **2026-09-26/29 — 2FA del staff 25/26:** TOTP obligatorio, recuperacion, equipos de confianza 30 dias, pagina
  "Seguridad de tu cuenta", sesiones abiertas, registro de accesos y bloqueo, `security:check` endurecido, manual.
- **2026-09-30 a 10-04 — Landing:** labs de hero y fondos, linea vitrina, confirmacion con escarapela.
- **2026-10-11 — Registro publico sin contraseña** (backend 87e519e), landing completa en la webapp contra el
  backend real, confirmacion con 3 estados, widget embebible con pase de un solo uso, admin Landing y widget.
- **2026-10-12 — Turnstile (llaves de prueba), SEO/OG, hero A2 vertical.** Backend: cupo y codigo con lock
  (6959bd0), B3.1 correo con ritmo (d5c3690), correo unico del registro (7d46d5c), B3.2 la puerta (de80348).
  B3.1 y B3.2 sin medir en servidor.
- **2026-10-14 — Referencia Bintec 2026** (`docs/analysis/REFERENCIA-BINTEC-2026.md`) → pendientes B.1-B.6.

### Texto original movido de PENDIENTES-WEBAPP.md


## ~~ALERTA — bloqueante critico detectado 2026-06-20~~ — RESUELTO 2026-06-20

~~194/194 tests vitest fallando.~~ → **194/194 verdes.** Root cause: Node 25.8.1 expone `globalThis.localStorage` experimental (warning `--localstorage-file was provided without a valid path`) que shadowea el de jsdom 29 con un stub sin metodos. Fix en `eventos-web/tests/setup.ts`: stub propio Map-based + `Object.defineProperty` sobre globalThis + window. Una sesion, un archivo, un fix.

---

## RESUMEN GLOBAL (post-recount)

| Modulo | Counter | Estado | Cambio vs auditoria 2026-05-21 |
|---|---|---|---|
| **W.0 Spatial UI base** | **24/24** | **CERRADO 100% 2026-07-04** (Command palette + Pre-load + device real reclasificados a Fase 2/W.12) | **+3** |
| **W.1 Setup + Auth + UI Foundation** | **107/107** | **CERRADO 100% 2026-07-04** (5 items diferidos reclasificados formalmente: 2 a Fase 2, 2 a W.12, 1 agrupado con W.4) | **+5** |
| W.1B Backend magic link | 10/10 | **CERRADO** | — |
| **W.2 Home** | **16/16** | **CERRADO 100% 2026-07-08** — GamificationHud LIVE (slide del carrusel espejo Expo, borde RGB + deeplink /desafio) + EventArchive ENDED (espejo Expo puro: banner + stats evento + prompt encuesta + 4 links archivo) + prompt encuesta → **/encuestas** (SurveyDeck por slides: cascada estrellas, spring, cierre verde). Recap/certificado → pantalla aparte (Fase 2) | **+3** |
| **W.3 Agenda** | **25/25** | **CERRADO 2026-07-04 via auditoria procedencia** — badges/conflict/check-in eran inventos (Expo no los tiene; el espejo real del lifecycle es el toast agenda:delayed YA hecho en W.11). URL state → Fase 2, recordatorio → W.12 Push | **+3** |
| **W.4 Streaming** | **92/92** | **CERRADO 2026-07-04 noche** (recount contra codigo: replay + rating auto + anuncios in-stream + custom panel + slow mode + floating emojis + mobile/tablet layouts YA estaban implementados y sin marcar. Fix race auto-rate e1b0c9a. Reclasificados: trivia→W.16, E2E cross-tab→W.12, 17 menores→QA W.12) | **+9** |
| **W.5 Speakers** | **35/35** | **CERRADO 100% 2026-07-04** (reclasificado Lighthouse + device fisico a W.12 Polish cross-modulos) | **+2** |
| **W.6 Social Wall** | **28/28** | **CERRADO 2026-07-05 (BLOQUE 1)** — Momentos (barra + viewer 9:16 + upload center-crop) + Memorias (5ta vista: grid oficial 2x2 + PhotoViewer marco fijo contain + ContestBanner podio/countdown + orden por likes) espejo Expo completo (`50a0f79`). Denominador re-baseado 41→28 (los 13 extra eran items eliminados en auditoria que seguian contando). +29 vitest +12 E2E, verificado vivo | **+28** |
| **W.7 Sponsors** | **23/23** | **CERRADO 2026-06-21** | **+23** (Sprint 2.A entero — skeleton + tooltip + E2E 12/12 + Lighthouse acc 98) |
| **W.8 Networking** | **21/21** | **CERRADO 100% 2026-07-04** (link sidebar identity → /perfil + 5 E2E nuevos + 4 items reclasificados formalmente: filtro role skip por privacy, RT listeners → W.11, sugeridos cards → Fase 2, tracking → Fase 2) | **+6** |
| **W.9 Engagement** | **35/35** | **CERRADO 2026-06-29 (Sprint 2.B)** | **+5** (2026-06-29: redemptions inline + E2E 11/11 verde con viewports desktop/tablet H/mobile + 8 tests vitest. counter PARITY sync) |
| **W.10 Live Hub** | **16/16** | **CERRADO por consenso 2026-06-20** | **+16** (creado en commit `0e185e6`, validado visual con seeder) |
| **W.11 Sockets RT** | **22/22** | **CERRADO 2026-07-04 noche** (GlobalSocketProvider 6 listeners + prop-sync 3 vistas + 11 vitest + 2 E2E + verificacion viva pipeline Laravel→socket→cliente. Scope 42→22: game/staff/cross-tab RT/stress reclasificados a W.12/W.15/W.16) | **+14** |
| W.12 Polish + E2E + PWA | **25/48** | **52%** — **2026-07-05 tarde BLOQUE 5 Fases A+B (`b9aa4df` backend + `2dc43a3` + `471cf94` web)**: Web Push COMPLETO (VAPID + push_subscriptions + transporte multi-canal en 13 call-sites + SW + soft prompt + scheduled→announcement, verificado vivo Chrome+FCM) + PWA 5/5 (manifest, SW, install desktop/tablet, offline) + CSP completo 13 directivas + robots noindex + titles 13 rutas (SEO OG/sitemap → Fase 2) + print agenda + code splitting 7 componentes. Falta real (Fase C): QA device, Lighthouse batch, WCAG audit, E2E cross-tab, DSN prod | **+17** |
| **W.13 FAQ + Docs** | **15/15** | **CERRADO 100% 2026-07-04** (Fase A FAQ Asistente orb + Fase B Documents split layout + backend ZIP escalable. Pages reclasificado formalmente a Fase 2) | **+2** |
| **W.14 Anuncios + Cartel + Bell** | **19/19** | **RE-CERRADO 2026-07-05** — cartel solo-highlights (espejo Expo) + feature banners muerta de raiz (BLOQUE 3: ruta+controller+Filament+modelo+migration drop). BD dedup + vigencias frescas | **+5** |
| **W.15 Vendor** | **35/35** | **HECHO via Mobile parity M.8 (2026-07-11, eventos-web `3f7e4dc`, pusheado)** — el vendor se hizo como feature del webapp MOBILE (celular espejo Expo): Mi Stand + Leads + Export CSV + scanner QR camara browser (@zxing) + Stats + Team + join-team + sockets staff. QA vivo device pendiente. Ver seccion Mobile parity M.8 (267-282) | **+35** |
| **W.16 Live Moments** | **5/5** | **CERRADO 100% 2026-07-09 (BLOQUE 4)** — TriviaPanel espejo Expo en la columna interactiva del streaming (4 fases idle/question/result/finished): countdown drenante rojo <=5s, opciones A-F color Kahoot, reveal de distribucion animada, mini-leaderboard + podio top 5. **Noir puro via --st-* (adapta Lux), UNICO color = letras A-F, cero iconos (decision Kamilo)**. Hook local `useTrivia` (patron useQnA) + reducer puro. Proxy `/events/games/{id}/answer`. Toasts ruleta/jackpot en GlobalSocketProvider. 10 vitest. **Validado en vivo** (launch->question->answer correcto score 135->result) via Mission Control + QaTriviaSeeder | **+5** |
| **W.17 Soporte** | **13/13** | **CERRADO 100% 2026-07-04** (split layout espejo W.14 + form nueva consulta + subflow FAQ + backend announcement on ticket-resolve. RT respuesta → W.11 via `data:invalidate{announcements}` (OJO: `support:new_response` NO existe como evento — auditoria 2026-07-04) + Web Push → W.12) | **+2** |
| **W.18 Hub Personal** | **19/19** | **100% — CERRADO 2026-07-04** (split 35/65 espejo W.13/W.14/W.17. Wall: hero+stats+rows+footer. Panel der: 3 sub-views Datos/Intereses/Apariencia. Data form con 3 cards visuales agrupando + 1 solo Guardar. Intereses chips min 1 con empty state. Apariencia Lux/Noir cards con preview aplicando via useTheme. Logout modal confirm. **Foto upload + shuffle beam avatar** (PerfilAvatarMenu popover: subir/cambiar variante/eliminar, seed en localStorage scopeado por email, beam URL espejo Expo). Deep link `eventos://profile[/sub]`. Sidebar refactor + ProfilePopover eliminado. 391/391 vitest + 13/13 E2E) | **+17** |
| **W.X Welcome Showcase** | **1/1** | **CERRADO 2026-07-14** (eventos-web `9d02140`, pusheado) — pelicula de bienvenida: opening keyvisual → invitacion crossfade → FLIP al canvas + 6 beats que HABITAN el canvas espejando su modulo real (Agenda: popups tip+sesion SINCRONIZADOS a la accion + payoff Mi Agenda / En vivo: player 16:9 poster+controles + About / Social: hub 3col / Desafio: hub+ranking / Sponsors: por tier REAL + detalle / Speakers) + finale nombre completo. Saltar se retira en finale. Copy es-CO tuteo. `/api/showcase` sponsors por tier real. typecheck/lint 0, vitest 7/7. **Falta solo QA final end-to-end.** Ver [[project_wx_showcase_design]] | **+1** |
| **W.19 Spatial Player** | **0/9** | **ABIERTO 2026-09-07** (BLOQUE 8) — el stream como sistema operativo: player persistente en el shell, `split` con Agenda, `mini` flotante con el resto, /live con preview real en vez de redirigir. Decision Kamilo viendo la demo de 5 dias | **+0 (nuevo)** |
| **TOTAL** | **550/585** | **94.0%** — Fase 1 desktop (W.15 → Mobile parity 2026-07-05). **2026-09-07: se abre W.19 (0/9), denominador 576→585** | **2026-07-14: W.X Welcome Showcase CERRADO 0→1** (pelicula bienvenida, todas las escenas habitan el canvas). **Ya no queda ningun modulo de features abierto.** Lo que resta es QA presencial (Mobile parity M.2-M.8 + B5 Fase C + QA final W.X) + DEPLOY DEMO. **19 modulos cerrados:** W.0, W.1, W.1B, W.2, W.3, W.4, W.5, W.6, W.7, W.8, W.9, W.10, W.11, W.13, W.14, W.16, W.17, W.18, W.X |

> Conflicto W.10 resuelto 2026-06-20: el codigo creo "W.10 Live Hub" reusando el numero. Doc viejo "W.10 Hub Personal" se renombra a W.18 Hub Personal. Sin refactor de codigo, solo doc.

---

## ESTADO GENERAL POR CATEGORIA — auditoria 2026-09-13

> Conteo de casillas de TODOS los documentos de pendientes + verificacion contra
> codigo de los casos que no cuadraban. Contadores viejos corregidos el mismo dia
> (ver "Contadores corregidos" abajo). Global sobre frentes con contador:
> **599 de 719 = 83%** (actualizado tras B1.1 y B1.2).

| Categoria | Avance | % | Estado | Donde vive el detalle |
|---|---|---|---|---|
| Webapp | 550/585 | 94% | Activa. Falta W.19 y QA presencial | Este archivo (BLOQUE 5, BLOQUE 8, MOBILE PARITY) |
| App movil Expo | Fase 1 cerrada | 100% implementado | Faltan pruebas en telefono; no publicada en tiendas | `PENDIENTES.md` §1 y §6 |
| Admin Lumina | 57/57 | 100% | Cerrado. Solo falta decidir las paginas personalizadas | `ROADMAP-LUMINA-ADMIN.md` F10.6 |
| Modulos del organizador | Cerrados | 100% implementado | Pulse, Data Center, kiosko, Mission Control, juegos en vivo, webhooks, recap | Roadmaps de cada modulo |
| Infraestructura | 33/73 | 45% | Activa: plan 2/31. **Evento el 6 de diciembre de 2026.** Sigue: token de DO → banco de pruebas en servidor real → B3 medido ahi (ver "ESTADO Y PENDIENTES AL 2026-09-29") | `ROADMAP-INFRAESTRUCTURA.md` |
| Seguridad del staff | 25/26 | 96% | Casi cerrado 2026-09-29: 2FA, equipos de confianza, sesiones, registro de accesos y bloqueo, `security:check`. Falta S.7.2 (correr el check en el servidor real) | `ROADMAP-SEGURIDAD-STAFF.md` |
| Manual del organizador | 5/35 | 14% | En pausa | `ROADMAP-MANUAL.md` |
| Registro publico, landing y widget | Sin contador | 0% | Sin empezar. La webapp no tiene pantalla de registro | `PENDIENTES.md` §3 + RUTA etapa 3 |

### Detalle de lo abierto por categoria

**Webapp (35 abiertos)**
| Bloque | Avance |
|---|---|
| W.19 Spatial Player | 0/9 |
| W.12 pulido: telefono, Edge, Firefox, accesibilidad, Lighthouse | 25/48 |
| QA presencial mobile M.1 a M.8 | Construido, falta probarlo en telefono |
| QA final de la pelicula de bienvenida W.X | 0/1 |

**Infraestructura por fase**
| Fase | Avance |
|---|---|
| I.0 Errores que rompen un evento | 3/3 |
| Nivel 1 montado y medido | 7/7 + 4 seguimientos abiertos (repetir caida API con 4 vCPU, ROL=admin, replica de lectura, modo registro/evento) |
| I.1 Rendimiento | 9/16 |
| I.1b Puerta del evento | 2/4 |
| I.2 Medir el ritmo real | 0/3 |
| I.3 Catalogo vendible | 5/7 |
| I.4 Independencia de DigitalOcean | 0/3 |
| I.5 Monitoreo y torre de control | 0/11 |
| I.6 Antes de exponer la URL | 2/3 (falta 2FA del staff) |
| I.7 Orquestador pipeline con Terraform (NUEVO 2026-09-13) | 2/10 |

**App movil y modulos — sueltos reales**
- App movil: probar el socket en telefono; push de silent disco con build real;
  barra liquid glass espera soporte de la libreria; EAS build de produccion y
  publicacion en tiendas (`eas.json` tiene perfiles, no hay rastro de publicacion).
- Event Pulse: decision "cada interaccion es momento hero"; verificar motor de
  momentos v2 (`ROADMAP-EVENT-PULSE.md`).
- Kiosko: medir escaneo < 100 ms en servidor real.
- Mission Control: pestaña de juegos (Fase 2).
- Recap: E2E con Chromium en VPS + validacion visual en dev build.

**Backlog sin fecha (no cuenta en el porcentaje):** stress 10K, UI optimista del
chat, unificar estilos de las SPAs standalone, tests de Mission Control, nice to
have Fase 2 y 3 (`PENDIENTES.md` §7b, §8, §9).

### Hallazgos de la auditoria 2026-09-13 (verificados contra codigo)

1. **El montaje siembra el demo y una cuenta de prueba.** `deploy.sh:369` corre
   `db:seed --force`; `UserSeeder.php:16-17` crea `superadmin@eventos.test` con
   clave `password`. Detalle y clasificacion del sembrado: I.7.1.
2. **`security:check` no detecta esa cuenta ni el evento demo.** → I.7.1.
3. **Sin eventos, el admin no lleva al wizard** (`SetFilamentEventContext` deja el
   contexto vacio). El wizard F11 existe y esta probado; necesita que exista una
   organizacion. → I.7.2.
4. **Registro abierto imposible hoy.** Un correo no registrado no recibe enlace
   ni ve error (`AuthController.php:470-474`); la webapp no tiene registro (solo
   el Expo usa `POST /auth/register`). Hoy se entra solo por importacion del admin
   con invitacion. → RUTA etapa 3.
5. **No hay despliegue automatico.** El montaje es manual (doctl + rsync + `.env`
   por rol); las BD administradas del Nivel 1 se borraron; `deploy.sh` no instala
   el codigo. → I.7 (Terraform + GitHub Actions aprobados).
6. **No hay 2FA** en ninguna parte del backend. → `ROADMAP-SEGURIDAD-STAFF.md`.
7. **Cloudflare LB sigue cobrando $5/mes** sin infraestructura detras. Kamilo
   cancela o usa.


### Contadores corregidos el 2026-09-13

- `PENDIENTES.md` Recap 0/75 → IMPLEMENTADO (backend + app verificados).
- `PENDIENTES.md` y `EventOS_Roadmap.md` DEPLOY DEMO 0/6 → HECHO 2026-08-01/02.
- `ROADMAP-INFRAESTRUCTURA.md` 28 → 31: `deploy.sh` por rol, Cloudflare naranja y
  rotacion de R2 marcados como hechos.
- **Anotados, sin marcar (no verificado item por item):** casillas de trivia en
  `ROADMAP-LIVE-MOMENTS.md` (la trivia existe: `LiveGame`, `GameController`, W.16)
  y criterios de exito de `ROADMAP-WEBHOOKS.md` (roadmap cerrado con 24 tests).

---

## RUTA A NOVIEMBRE — orden de trabajo con dependencias (decision Kamilo 2026-09-13, re-priorizada el mismo dia)

> **Contexto:** posible cliente en noviembre (evento con influencers, **5.000 personas** — ver "EL EVENTO DE NOVIEMBRE ES DE 5.000 PERSONAS" arriba).
> **Actualizado 2026-09-29: el evento es el DOMINGO 6 DE DICIEMBRE DE 2026.** Estado, cambio de orden
> (servidor real primero, local no sirve para capacidad), pendientes y calendario por semana:
> `ROADMAP-INFRAESTRUCTURA.md` → "ESTADO Y PENDIENTES AL 2026-09-29".
> **Criterio que manda (Kamilo, textual):** *"si en este momento me dijeran
> tenemos evento mañana, ¿que podria montar sin sufrir ni improvisar?"*.
> W.19 es mejora (lo actual funciona) → pasa a segundo plano. Tiendas de ultimo.
>
> **2026-09-13 — LA INFRAESTRUCTURA SE EJECUTA POR SU PLAN:** `docs/roadmaps/ROADMAP-INFRAESTRUCTURA.md`
> "PLAN DE TRABAJO — EVENTO DE NOVIEMBRE, 5.000 PERSONAS" (0/29, bloques B1-B6 con compuertas).
> Las etapas 1, 2 y 5 de esta ruta son ese plan; los items de infraestructura de abajo quedan como resumen.
>
> **2026-10-14 — MENOS PETICIONES (por decidir):** referencia real de Bintec 2026 en
> `docs/analysis/REFERENCIA-BINTEC-2026.md`. Idea central: una peticion por pantalla ya armada
> para pintar (empezar por la agenda: 1 por dia), cache CDN para lo que casi no cambia, lo vivo por
> socket, video afuera, chat con respaldo externo si 5.000 no aguanta. Va antes de medir 5.000.


### Diagnostico "evento mañana" (verificado contra codigo 2026-09-13)

- **Funciona sin sufrir:** todos los modulos del asistente (agenda, streaming con
  URL de YouTube/Vimeo, social, desafio, sponsors, speakers), Pulse, kiosko,
  Mission Control, juegos en vivo. Crear el evento con el wizard F11. Importar la
  lista de asistentes desde el admin con invitacion por correo
  (`ListAttendeeAdmins.php:60`, `AttendeesImport`).
- **Se puede, pero improvisando:** el montaje (manual por consola con `doctl`,
  tokens nuevos, las BD administradas se borraron al cerrar el Nivel 1, codigo por
  rsync, `.env` por rol a mano); limpiar el demo y cambiar la clave del
  superadmin a mano (`deploy.sh:369` + `UserSeeder.php:16-17`); el correo necesita
  clave nueva de Resend y dominio verificado; vigilar por ssh sin alertas; nunca
  se recorrio en telefono con gente real.
- **No se puede:** registro abierto desde un enlace. Un correo que no existe no
  recibe nada y no ve error (`AuthController.php:470-474`, anti-enumeracion), y la
  webapp no tiene pantalla de registro. 2FA del staff. App en tiendas.

**Etapa 0 — Guardar lo suelto** (espera confirmacion de Kamilo para commit)
- [ ] APP EVENTOS: `demo/`, este archivo, ROADMAP-INFRAESTRUCTURA (I.7)
- [ ] eventos-backend: `Demo5DiasSeeder.php` + cambio en `DemoCompletoSeeder.php`
- [x] **HECHO 2026-09-13 (ver "Contadores corregidos" arriba).** Sanear contadores viejos: Recap figura 0/75 en PENDIENTES.md y ya existe
      (`RecapService.php`, `eventos-app/app/(app)/recap`); trivia de LIVE-MOMENTS y
      criterios de WEBHOOKS sin marcar con el roadmap cerrado; DEPLOY DEMO 0/6 en
      EventOS_Roadmap y PENDIENTES.md (se hizo en agosto); 3 items de
      ROADMAP-INFRAESTRUCTURA hechos sin marcar (deploy.sh por rol, rotacion de
      claves, Cloudflare naranja)

**Etapa 1 — Un admin que se le entrega a un cliente**
- [x] **HECHO 2026-09-13.** I.7.1 Instalacion limpia sin demo y sin `superadmin@eventos.test`/`password`
      (ROADMAP-INFRAESTRUCTURA I.7) → **base de todo lo demas**
- [x] **HECHO 2026-09-13.** I.7.2 Primer ingreso lleva al wizard F11 → depende de I.7.1
- [ ] 2FA del staff, S.0-S.6 + S.8 (ROADMAP-SEGURIDAD-STAFF, 0/26). S.7 va con el
      montaje. Regla Kamilo: la URL del admin no sale sin esto

**Etapa 2 — Montar sin improvisar** → depende de I.7.1
- [ ] I.7.3 Paquete por version (reemplaza el rsync desde una semilla)
- [ ] I.7.4 Terraform por nivel + I.7.5 pipeline etapas 1-6 con compuertas
- [ ] I.7.6 Humo funcional automatico (incluye correo de acceso recibido)
- [ ] Correo transaccional listo por cliente: clave Resend, dominio verificado
- [ ] Alerta externa (capa 1 de I.5) + respaldo fuera de DigitalOcean (I.4)
- [ ] I.7.8 Entrega + runbook del dia del evento ("si pasa X, haz Y")

**Etapa 3 — La puerta del publico** (influencers: la gente llega por un enlace)
- [ ] **Registro publico en la webapp** (NUEVO). Terreno: el backend tiene
      `POST /auth/register` (`routes/api/auth.php:8`), solo lo usa el Expo
      (`eventos-app/lib/authApi.ts:105`); `eventos-web` `(auth)` no tiene registro.
      Espejo del flujo Expo, diseño DaVinci antes de codear
- [ ] I.1b limitador por IP cuenta FALLOS, no intentos + rutas publicas por recinto
      (ROADMAP-INFRAESTRUCTURA I.1b, 2/4)

**Etapa 4 — QA presencial con Kamilo** → depende de etapas 1-3
- [ ] Mobile parity M.1-M.8 en telefono real
- [ ] W.12 Fase C: device, Edge/Firefox, Lighthouse, WCAG, E2E cross-tab
- [ ] W.X QA final de la pelicula de corrido

**Etapa 5 — Ensayo general** → depende de etapas 2 y 4
- [ ] Montar con el pipeline el nivel del evento, I.7.7 carga del tamaño real +
      caida de un nodo por rol, y desmontar. Con eso "evento mañana" = si

**Etapa 6 — W.19 Spatial Player** (BLOQUE 8 abajo, 0/9) — mejora, segundo plano
- Propuesta de arquitectura hecha 2026-09-13 (capa fija de reproduccion en el
  layout + hueco por pantalla; el iframe nunca se mueve porque Safari no soporta
  `moveBefore()`; modo derivado de la ruta; chat/Q&A/config suben al provider;
  8.1 y 8.3 juntos; mini con snap a esquina). **Sin aprobar todavia.**
- 8.6 DECIDIDO A desktop / B mobile · 8.7 decision pendiente

**Etapa 7 — Tiendas (ultimo, decision Kamilo)**
- [ ] EAS build de produccion Android + iOS, cuentas, envio y revision
      (`eas.json` ya tiene perfil `production` y `submit`; no hay rastro de
      publicacion). **Riesgo anotado:** la revision toma dias o semanas; si el
      cliente pide app en tiendas, dejar margen antes del evento
- [ ] Pipeline restante: I.7.9 hermanos (actualizar, modo registro/evento,
      destruir) + I.7.10 prueba de punta a punta


## QUE SIGUE — features CERRADAS, queda QA presencial (webapp only)

**Ya no hay modulos de features abiertos** (2026-07-14: W.X fue el ultimo). Lo que resta
de webapp es QA CON Kamilo presente (device + tiempo), NO codigo nuevo:

> **EXCEPCION 2026-09-07 — se abre W.19 Spatial Player** (BLOQUE 8 abajo, 0/9): en la demo
> de 5 dias Kamilo vio que el streaming NO se comporta como un sistema operativo
> (visionOS): al navegar a otro modulo el player desaparece entero, y "En vivo" es un
> directorio que redirige, sin preview. Es un cambio de arquitectura del shell, no un bug.

- [ ] **Mobile parity M.2-M.8 QA vivo en device** (implementacion 60/60 cerrada 2026-07-11):
  heart particulas, momentos/uploads, shuffle, trivia sponsor, desafio, anuncios detail,
  soporte form, orb FAQ, encuestas deck, **vendor** (scanner camara via HTTPS, export
  share sheet, invitacion socket en vivo, gates ban/pending con datos reales).
- [ ] **W.12 Fase C** (~2h presenciales): QA device iPad/iPhone/Edge/Firefox + Lighthouse
  batch + WCAG audit + E2E cross-tab. (Fase A/B validadas 2026-07-05: push+PWA OK.)
- [ ] **W.X Showcase**: QA final de la pelicula de corrido (el QA fue por escena).

> Deuda menor webapp:
> · ~~panel `custom` streaming = placeholder~~ **CORREGIDO 2026-07-14** (`62ebfbd`): monta
>   el iframe del `custom_url` (Slido/Mentimeter/Google Slides/Miro/Figma…) en tablet+mobile
>   (desktop ya lo hacia); la CSP ya permite `frame-src https:` y el backend valida el dominio.
> · ~~bug proxy poll-vote sin /events~~ **CORREGIDO 2026-07-14** (`c4f6293`).
> · ~~2 tests ProfileSecurityTest rojos~~ **CORREGIDO 2026-07-14** (`2eaf11c`: validator
>   blindado vs schemes XSS + tests, 11/11).
> · **E2E Playwright /encuestas y /trivia — NO SE HARA (decision Kamilo 2026-07-14).**
>   Razon: es cobertura de regresion, NO un bug — ambos flujos funcionan (verificados EN
>   VIVO) y ya tienen vitest del reducer/helpers. El E2E de trivia exige simular eventos de
>   socket (`game:question`…) que quedan **flaky** (falsos negativos + ruido de CI y
>   mantenimiento). Post-pivote la prioridad es completitud + deploy, no cobertura de 2
>   flujos ya validados. Si a futuro un cliente lo exige, se reevalua.
> · Expo documentos legacy por alinear.
>
> **FUERA de "webapp only"** (superficies/infra aparte — NO son pendiente de webapp):
> ~~DEPLOY DEMO 0/6~~ **HECHO 2026-08-01/02** (produccion real, 8 escenarios verde, 25 bugs) ·
> **RENDIMIENTO Y CAPACIDAD 0/5** (frente nuevo — empezar por los 45 ms) ·
> **SEGURIDAD DEL STAFF 0/2** (raya: sin 2FA no sale la URL) ·
> **Paridad config admin↔superficies 0/3** (toca backend/Filament/Expo/webapp a la vez) ·
> **Event Pulse** = display propio del backend, **COMPLETO** — solo queda diagnosticar el
> cache del `moments.js` v2 + 1 decision de diseño (cada-interaccion-hero vs actual);
> ver [[project_event_pulse_complete]].

> Deuda menor W.2 (no bloqueante): E2E Playwright de /encuestas (tengo vitest 9 tests) · verificar Lux con ojo · recap/certificado como pantalla aparte (Fase 2, hoy el codigo recap sigue en lib/recap sin consumir desde Home) · ~~streaming poll-vote proxy sin prefijo /events~~ **CORREGIDO 2026-07-14 (`c4f6293`)**: ahora pega a /events/polls/{id}/vote.

> ~~Deuda ProfileSecurityTest 2 tests rojos~~ **CORREGIDO 2026-07-14 (`2eaf11c`)**: el
> validator ahora acepta string plano (conducta relajada W.18) PERO rechaza schemes XSS
> (`javascript:`/`data:`/`vbscript:`); los 2 tests actualizados a "rechaza scheme peligroso"
> + 2 tests nuevos de la conducta relajada. 11/11 verde.

---

## PLAN DE CIERRE TOTAL — BLOQUES (definido 2026-07-04, inventario 4 agentes contra codigo)

> **Meta:** cierre 95-100% del scope COMPLETO (656 items), incluyendo W.15 + W.16 + W.X.
> Decision Kamilo 2026-07-04: revierte skips de W.16 y W.15. Total ~30-38h en 6-8 sesiones DaVinci.
> W.X va de ULTIMO por diseño (reusa componentes reales en miniatura de todos los modulos cerrados).
> Los sprints historicos cerrados (Sprint 0-2, 2026-06-20 → 2026-07-04) viven en git de este doc.

### BLOQUE 0 — Contabilidad + fixes rapidos — 3/3 **CERRADO 2026-07-04**
- [x] Marcar las ~11 victorias gratis en counters (W.3 +2, W.6 +3, W.12 +6) — validado 402/402 vitest + typecheck
- [x] Fix alias `sessions` en `KNOWN_ENTITIES` de `useGlobalSocket.tsx:124` (backend puede emitir `agenda` o `sessions`)
- [x] Boton "Ver introduccion de nuevo" OCULTO en PerfilView (navegaba a /onboarding → 404). E2E perfil 13/13 ajustado (asserts toHaveCount(0)). Re-habilitar en BLOQUE 7

> **AUDITORIA DE PROCEDENCIA 2026-07-04 (3 agentes contra Expo real + backend):** todo
> item del plan quedo verificado contra codigo. INVENTOS DE PLANEACION eliminados
> (nunca existieron en Expo ni backend): W.2 sponsors band + multi-sede + proximos
> eventos org + dedupe · W.3 badges AJUSTADA/CANCELADA (API no expone los 3 tiempos,
> Expo NO pinta badges — el espejo real es el toast agenda:delayed que YA tenemos) +
> conflict detector + boton self check-in (el asistente presenta QR, staff escanea) +
> URL state (→Fase 2) · W.16 sorteo ceremony + golden reveal full-screen (Expo solo
> hace toast + anuncio + modal por tap en hub — eso SI se espeja) · W.15 invite sin
> login (backend exige sesion) · W.X showcase 6 beats (el onboarding Expo es un wizard
> de REGISTRO, no un carrusel de features) · Banners (feature legacy — highlights la
> reemplazo; el home Expo solo renderiza highlights).
> Regla nueva: **todo item de backlog debe citar procedencia** (pantalla Expo
> archivo:linea, endpoint backend, o decision explicita de Kamilo).

### BLOQUE 1 — W.6 Social → 100% — 7/7 **CERRADO 2026-07-05** (`50a0f79`)
- [x] ~~Load-more/paginacion~~ — **HECHO 2026-07-04** (`7afb5d0`): infinite scroll cursor-based + dedup + sentinel. Verificado vivo 99 posts
- [x] ~~Hashtags click-to-filter~~ → **ELIMINADO** (decision Kamilo; parser tambien borrado del codigo `f0bedaa`. Idea menciones → Event Pulse, en memoria)
- [x] **Stories = "Momentos"** — barra sobre el feed (visible en Feed y Memorias), anillo accent no-visto / tenue visto, "visto" en localStorage `eventos:social:story-seen:{eventId}` reactivo (useSyncExternalStore)
- [x] **MomentosViewer** — card 9:16 centrada DENTRO del CanvasCard (patron Instagram desktop), auto-advance 5s, dots, click zones 40/60, teclado+Esc+chevrons+click afuera, marca visto al abrir, sin salto entre autores
- [x] **Upload momento** — preview 9:16 + center-crop canvas + POST multipart + toast espejo "Momento subido! Visible por 24h."
- [x] **Vista Memorias** — 5ta vista sidebar: grid 3 col oficial 2x2, PhotoViewer marco FIJO 16:9/9:16 con contain (nunca recorta), like optimistic+haptic+pop, foto propia → toast informativo (anti-gaming), upload header crop 1:1
- [x] **ContestBanner** — solo active/ended<24h, countdown vivo useNow, podio oro/plata/bronce 72/56, orden por likes client-side (grid+viewer indices compartidos)

### BLOQUE 2 — W.2 Home → 100% — 4/4 **CERRADO 2026-07-08** (`c30b55d` web + `127693a` backend)
- [x] ~~Foto real speaker en feed salas~~ — **HECHO 2026-07-04**: `RoomAvatar` usa `speaker_photo_url` con gradiente fallback (espejo Expo session-stream:272)
- [x] **GamificationHud LIVE** — slide extra del carrusel de highlights (`CartelDigital`), espejo Expo `GamificationHud`: borde RGB girando 6s + barra segmentada de 10 + rank/puntos/retos/stamps, toda la card deeplink a /desafio. Tamano fluido cqw+em (no desborda el slot 16:9). Datos SSR via `fetchDesafioOverview` → `deriveHudData`. Paleta teal fija (no accent). Dwell 10s vs 6s highlights
- [x] **Post-event survey prompt ENDED** — tarjeta en el EventArchive (pendiente "Responder"→/encuestas / completada). Estado via `fetchPostEventSurvey` (GET /events/{id}/post-event-survey)
- [x] **EventArchive ENDED** (espejo Expo puro, decision Kamilo: recap/certificado fuera del Home) — banner "Evento finalizado" + fecha, 3 stats del evento (asistentes/sesiones/fotos, `photo_count` nuevo en by-slug), 4 links archivo (agenda/social/desafio/speakers). Reemplaza el recap-col
- [x] **DESTINO /encuestas** (no estaba en el plan; el prompt sin destino era boton muerto) — `SurveyDeck` por slides espejo Expo `PollSlides` elevado a EventOS: 1 pregunta/slide, transicion spring, opciones en cards con pop + check dibujado, **estrellas que se llenan en cascada** al seleccionar, cierre verde de exito (NO accent que puede ser rojo=alarma), voto **optimista** (feedback instantaneo). Reutiliza el sistema de polls + proxies /api/surveys. Deeplink eventos://encuestas. Cero dots, tokens Lux+Noir

### BLOQUE 3 — Cartel espejo + BD limpia — 3/3 **CERRADO 2026-07-05**
- [x] CartelDigital → **solo highlights** (`highlightsToCartelItems`, merger round-robin y sponsor pill eliminados; backend ya filtra vigencia con scope active). `lib/banners.ts` borrado
- [x] **Feature banners MUERTA de raiz**: ruta + BannerController + BannerResource Filament + modelo + BannerTest + seeders (Content/ModuleTemplate) + catalogo modulos (CreateEvent/ModuleResource) + migration `drop_banners_feature` (tabla + module_templates + modules slug=banners). Endpoint verificado 404. stress-full.js → highlights
- [x] BD limpia: 4 duplicados de `event_highlights` borrados + vigencias refrescadas (4 highlights activos 3 dias). Verificacion: 401/401 vitest + 12/12 E2E cartel+home + suite backend Content (2 fallos PRE-existentes de ProfileSecurityTest, ajenos — validator W.18 relajado sin actualizar tests, anotado abajo)

### BLOQUE 4 — W.16 Live Moments espejo real — 5/5 CERRADO 2026-07-09
> Espejo estricto de lo que Expo HACE: trivia completa + toasts de ruleta/jackpot. Validado en vivo (MC + QaTriviaSeeder). Commit eventos-web `main`.
- [x] TriviaPanel espejo (`TriviaPanel.tsx`): fases idle/question (A-F + countdown rojo ≤5s) / result (+X pts, distribucion animada, top 3) / finished (podio top 5). **Noir --st-*, unico color letras A-F, cero iconos (Kamilo)**
- [x] `useTrivia` (hook local patron useQnA, NO store global — la webapp no tiene zustand) + reducer puro `lib/trivia.ts`, alimentado por `game:question`/`game:round-result`/`game:finished`
- [x] `POST /events/games/{gameId}/answer` proxy (prefijo /events explicito) — NO optimista (backend devuelve correct/score, espejo Expo)
- [x] Listeners `game:launched`/`game:result` → toasts en GlobalSocketProvider + router.refresh (puntos/premios)
- [x] 10 vitest (reducer + helpers). Deuda menor: E2E Playwright (mismo criterio /encuestas)


### MOBILE PARITY — workstream 60/60 **CERRADO 2026-07-11 (implementacion 100%; queda QA vivo Kamilo M.2-M.8)**

> **Enfoque acordado (2026-07-05):** NO portar componentes RN (react-native-web
> descartado). Capa de PRESENTACION mobile nueva sobre la capa de datos existente
> (fetchers SSR + proxies + clients: 100% agnostica del layout — verificado en
> baseline). Transcripcion pantalla-por-pantalla del Expo real.
>
> **Baseline 2026-07-09 (5 agentes: 4 sobre Expo real + 1 sobre eventos-web):**
> - **Molde arquitectonico ya existe**: `StreamShell.tsx:187-193` bifurca con
>   `useIsMobile()` (client matchMedia, SSR-safe) en 3 variantes; logica en el
>   padre, variantes = puro layout. Breakpoint canonico `mobile <640px`
>   (`globals.css:8`). NO hay UA sniffing en el repo — la bifurcacion es client.
> - **Hoy a 390px la webapp esta ROTA sin guard**: canvas ~240px
>   (`globals.css:449` asume sidebar desktop) + SidebarPill flotando encima.
>   TabletRotateOverlay NO cubre <640px.
> - **Shell Expo a espejar**: `FloatingTabBar.tsx` (354) — pill flotante blur,
>   burbuja deslizante spring, badge rojo networking, 5 tabs:
>   Inicio / Mi Agenda / Mi QR / Networking / Perfil. Sin headers nativos
>   (cada pantalla dibuja el suyo; bell solo en Home). Scrolls reservan
>   `useTabBarHeight` (68 + safe area) como padding inferior.
> - **MAPA DE NAVEGACION ESPEJO (correccion Kamilo 2026-07-09 — la webapp
>   mobile NO inventa entradas de menu que Expo no tiene):** ModuleMenu del
>   Home = SOLO 4 modulos hardcoded Agenda/Speakers/Social/Sponsors
>   (`ModuleMenu.tsx:26-31`) + Room Check-in (staff) / Asignar Staff (admin)
>   por rol. El resto se alcanza asi: Anuncios = campana del Home; Desafio =
>   slide JUGAR del HUD (`GamificationHud.tsx:163`) + deeplink + EventArchive;
>   FAQ = boton Ayuda del Perfil (`ProfileScreen.tsx:275`); Soporte = via FAQ;
>   Encuestas = EventArchive ENDED (`EventArchive.tsx:49`) + PollSlides
>   overlay en chat/stream; About = card del Home SOLO registration/draft
>   (`index.tsx:170-185`); **Documentos/Pages = SIN navegacion propia** — solo
>   via highlights `link_url` / anuncios `action_url` (deeplinks internos,
>   `HappeningNow.tsx:347-356`). El modulo existe pero es opcional/invisible.
>
> **NO se espeja (procedencia verificada):** `passport.tsx` standalone
> (huerfana — cero navegaciones entrantes; el pasaporte real es sheet del hub
> Desafio) · `banners.tsx` (muerta) · onboarding wizard (decision W.1/W.X) ·
> `recap/[eventId]` → Fase 2 (decision W.2) · `pages/[id]` → Fase 2 (decision
> W.13) · **staff-checkin + assign-staff → FUERA (decision Kamilo 2026-07-09:
> el staff no va en el workstream mobile; kiosko cubre check-in)** ·
> activate-account/pending-approval (auth webapp = magic link W.1; revisar en
> M.0 si el gate de aprobacion aplica).
>
> **Decisiones cerradas 2026-07-09 (no re-preguntar):**
> - ~~session-chat como modo chat full-height de StreamShellMobile~~ —
>   **REVOCADA 2026-07-10 (Kamilo)**: sesion SIN stream entra igual a
>   /session-stream y el player muestra el aviso "Transmision no
>   disponible aun" (StreamPlayer EmptyState, ya implementado). NO hay
>   modo chat pantalla completa ni ruta `/session-chat`.
> - **about.tsx entra** como pantalla chica en M.7 (webapp no tiene /about).
> - **Orden de bloques M.0→M.8; M.0 primero y bloqueante.** Mockup DaVinci del
>   tab bar aprobado ANTES de codear.

#### M.0 — Shell mobile (fundacion, bloquea todo) — 7/7 **CERRADO 2026-07-11**
- [x] **Bifurcacion shell CSS-first** (2026-07-09): chrome desktop (`AmbientBackground`/`SidebarPill`/`ThemeTogglePill`) en wrapper `contents mobile:hidden`; `Stage` con variantes `mobile:block p-0`; `MobileGate` oculta la vista desktop <640px y muestra placeholder digno (CTA volver al inicio) hasta que cada ruta entregue su vista mobile (`MOBILE_READY`, /session-stream ya bifurca solo). Cero flash de hidratacion (CSS decide, no JS)
- [x] **MobileTabBar espejo `FloatingTabBar.tsx`** (2026-07-09, mockup DaVinci aprobado + 3 decisiones: blur espejo en Noir / accent en activa / con labels): pill flotante blur 380 max, burbuja lente framer spring damping22/stiffness340 + highlight especular 45%, lift -3px, press scale 0.88, labels 9px, badge rojo solicitudes (SSR + auto-refresh via router.refresh de networking:notify), vibrate soft. **Solo visible en las 5 rutas tab** (espejo: stack screens sin tab bar). Rutas nuevas /mi-qr y /networking con `DesktopRedirect` (>=640 → /home y /social)
- [x] Patron header mobile por pantalla — **HECHO en M.1 2026-07-09** (`MobileHeader` back pill + canon titulos; bell con badge/shake solo en Home via `HomeHeaderM`)
- [x] Reserva altura tab bar: `--m-tabbar-reserve` (68 + safe area + 15, espejo `useTabBarHeight.ts`) en mobile-shell.css — usada por placeholder y PushPrompt
- [x] Guard CSS mobile: canvas/Stage neutralizados <640px (vista desktop ademas oculta por MobileGate)
- [x] PushPrompt: bottom sube sobre el tab bar en mobile (`calc(var(--m-tabbar-reserve) + 12px)`)
- [x] **Deep links + gates de acceso — CERRADO 2026-07-11** (eventos-web `ef1c757`): `parseActionUrl` con `eventos://join-team/{token}` y `eventos://stand` como rutas internas reales (M.8; solo recap queda internal-future) + **gates espejo `(app)/_layout.tsx:63-73`**: `getAccessGate` (ban + registration_approved_at de la MISMA llamada /auth/me cacheada) en el layout (app) → `/banned` (motivo + expiracion/Permanente + Entendido=logout, espejo banned.tsx 152) y `/pending-approval` (Verificar estado via proxy /api/auth/status + Cerrar sesion, espejo pending-approval.tsx 191 — divergencia documentada: aprobado → /home, la webapp no tiene onboarding wizard). Ambas en (auth), fuera del shell (sin loop), universales de viewport. Verificacion: 556/556 vitest · E2E 34/34 serial (+3 gates: redirect ban, redirect pending, usuario normal rebota a /home) · screenshots 390px revisados
> Verificacion 2026-07-09: typecheck + lint 0 errores · 497/497 vitest (+9: MobileTabBar 6 + MobileGate 3) · **E2E nuevo `mobile-shell.spec.ts` 7/7** (tab bar 5 tabs + activo, navegacion, placeholder + desktop oculto, stack sin tab bar, redirects desktop) · screenshots 390px OK (badge, burbuja accent, placeholder)

#### M.1 — Home + Mi QR (tabs 1 y 3) — 8/8 **IMPLEMENTADO 2026-07-09 (QA vivo Kamilo pendiente)**
- [x] **Home mobile por status** (`HomeMobile.tsx` espejo `(tabs)/index.tsx:143-271`): registration/draft = hero+countdown+info card+about · published = countdown compact+HappeningNow+modulos compact · live = HappeningNow+modulos · ended = EventArchive (reuso componente espejo W.2). Datos: `fetchEventBranding` NUEVO (GET /events/{id}/branding — la MISMA fuente que Expo useBranding) + agenda flatten + announcements. Dual render CSS en home/page.tsx
- [x] HomeHeader mobile: logo/header_title + bell → /anuncios con badge unread (localStorage lastSeenAt reuso W.14) + shake CSS cada 5s + badge pop spring
- [x] HappeningNow mobile (`HappeningNowM.tsx` espejo 573): dark island NOIR fijo, carrusel crossfade 400ms con rotacion 6s/10s HUD, dots por tipo (accent/teal/rosa), PulseDot, SpeakerRow stack + favoritos "+N asistiran", HighlightCard con link_url interno/externo, UNIRTE → /session-stream (chat-mode M.7). Status visual: `session-visual-status.ts` transcrito de `sessionStatus.ts` Expo, derivado con useNow (sin setState-in-effect)
- [x] ModuleMenu mobile: SOLO 4 cards espejo (`ModuleMenu.tsx:26-31`), ratio 0.58, stagger 60ms, compact published
- [x] GamificationHud slide: reuso del componente espejo W.2 dentro del carrusel (verificar tamano en QA vivo)
- [x] Proxy `/api/mi-qr` + fetcher `fetchQrToken` (GET /me/qr?event_id)
- [x] Mi QR espejo `MiQrScreen.tsx` (503): badge card negra siempre + DashedLine + **RgbWaveBorder pastel girando 6s (CSS)** + QR rotativo countdown 60s auto-refetch + tap fullscreen bg 0.88 + identidad beam avatar + formatEventDates transcrito
- [x] Wallet pill "Pronto" disabled (espejo)
> BONUS M.1: **/about implementado** (adelantado de M.7 — la card About del Home registration lo necesitaba; `AboutView` espejo `about.tsx` 175: imagen 16:9 + texto + links, back pill, desktop redirige) + **MobileHeader** patron header (cierra item 3 de M.0).
> Verificacion 2026-07-09: typecheck+lint 0 errores · 509/509 vitest (+12: MiQrView 6 + HomeMobile/status visual 6) · E2E mobile-shell 9/9 (home mobile hero+4 modulos+bell, mi-qr badge+fullscreen+tab activa) · screenshots 390 revisados (home live + mi-qr fieles al Expo). QA vivo pendiente: HUD slide con datos reales, estados published/ended/registration con backend real, hero imagen, Lux

#### M.2 — Agenda mobile (tab 2 + stack) — 5/5 **IMPLEMENTADO 2026-07-09 (QA vivo Kamilo pendiente)**
- [x] **DayStrip Fever + TrackFilter** (`AgendaMobile.tsx` espejo `AgendaScreen.tsx:60-209`): pills 52x76 con dia activo accent + dot dia-con-eventos + relleno ±1 deshabilitado + auto-scroll centrado (scrollTo, NO scrollIntoView — leccion err 113); chips de track con dot color + "Todos"
- [x] **SessionCard timeline** (espejo `:400-624`): hora 12h es-CO + TimelineDot pulso live + conector + badges EN VIVO/check/track + **corazon --heart con pop + ring + 6 particulas** (canon webapp, divergencia aprobada del accent Expo) + finished tachada/atenuada + speakers stack + Calendario (.ics W.3 reuso) / Evaluar / UNIRTE / Ver grabacion + DaySlide 280ms + highlight deeplink glow 2.5s
- [x] **Rutas espejo estructura Expo**: tab = `/mi-agenda` NUEVA (favoritesOnly, con tab bar, boton "Todas" = downloadAgendaIcs bulk W.3, quitar favorito saca de la lista) · `/agenda` completa = stack (back pill, SIN tab bar) con dual render CSS · desktop /mi-agenda → /agenda
- [x] **Detalle de sesion `/session/[id]` NUEVA** (`SessionDetailM` espejo `session/[id].tsx` 458): badges live/tipo/track + titulo 26 + card fecha/hora/lugar/capacidad + Favorita/Calendario/Evaluar + stream full-width + "Acerca de" + speakers clickeables (→ /speakers hasta M.5) — **decision: ruta (espejo), en desktop redirige a /agenda?highlight={id}** (el panel ES el detalle desktop)
- [x] **RatingModalM**: reuso del RatingModal W.3 con wrapper fixed `.agenda-root` (los estilos del pop estan scopeados — bug QA cazado en screenshot: renderizaba inline; +fix bg transparent)
> Verificacion 2026-07-09: typecheck + lint 0 errores (2 violaciones react-hooks refactorizadas: dia activo derivado + slide dir en state) · 517/517 vitest (+8: AgendaMobile 5 + tabbar/gate updates) · E2E mobile-shell 11/11 (mi-agenda tab activa + agenda stack sin tab bar + tap card → /session/{id}) · screenshots 390 revisados (timeline/pills/detalle fieles). QA vivo pendiente: heart particulas, DaySlide, rating real, "Todas" ICS, Lux

#### M.3 — Networking + Perfil (tabs 4 y 5) — 6/6 **IMPLEMENTADO 2026-07-09 (QA vivo pendiente) — LAS 5 TABS DEL SHELL COMPLETAS**
- [x] **Networking 3 tabs** (`NetworkingM.tsx` espejo `NetworkingScreen.tsx:139-176`): pills segmented accent + badge rojo count solicitudes (badge verde contactos-nuevos requiere store socket → nota deuda)
- [x] **Directorio**: search server-side debounce 400ms + **infinite scroll IntersectionObserver** via proxy NUEVO `/api/social/attendees` + contador "N asistentes" + footers espejo + sugeridos carousel horizontal (cards 160x260: comun + tags + CTA por relation) + quick-connect optimista con relation override
- [x] **Contactos**: rows + "Guardar contacto" = .vcf single + "Exportar todos (.vcf)" multi-card + bloqueados colapsable con desbloquear optimista
- [x] **Solicitudes**: cards con mensaje quote + timeAgo + Aceptar/Ignorar optimista ("Conectado con X")
- [x] **Perfil de asistente `/attendee/[id]` NUEVA** (`AttendeeDetailM` espejo `attendee/[id].tsx` 613): hero horizontal 80 + CTA por relation + sheets Connect (mensaje 500) / Block (confirm rojo) + bio + intereses comunes resaltados + sus sesiones → /session/{id} + redes (SVGs inline, licencias lucide) + Contactar mutuos (wa.me/mailto/.vcf) — desktop redirige a /social. Nota espejo: los botones Aceptar/Ignorar de Expo ahi NO tienen handler; web enlaza al tab Solicitudes
- [x] **Perfil propio `PerfilM` IMPLEMENTADO 2026-07-09** (espejo `ProfileScreen.tsx` 927): hero centrado avatar 96 + badge camara → SheetM foto (input capture)/galeria/shuffle beam x3 (seed W.18 por email)/volver-al-avatar · redes brand SVG → abren edit · stats Puntos/Retos/Logros · Mis datos + modal Editar full-sheet (basicos + redes, merge patch preservando email — leccion W.18) · Mis intereses inline espejo MyInterests (violeta semantico FIJO, min 1) · toggle tema next-themes · logout reusa PerfilLogoutModal (cross-tab + disposeSocket). "Ver introduccion" OMITIDO (decision Bloque 0). SheetM extraido a shell/mobile (compartido con attendee)
> QA fixes 2026-07-09 (feedback Kamilo img-less): halo avatar attendee centrado (grid 88) · sheets con slide-up+backdrop fade+sombra · vibrate en enviar solicitud/bloquear/desbloquear · active:scale en filas/cards · fade-up sutil en filas de directorio (divergencia minima, Expo no anima — vetable). Intereses en comun: shape backend verificado OK, pendiente cross-check Kamilo vs panel desktop
> Verificacion 2026-07-09: typecheck+lint 0 errores · 517/517 vitest · E2E mobile-shell 12/12 (+networking: 3 tabs + tap directorio → /attendee/{id}) · mockBackend +/attendees/{id}/profile · screenshots 390 revisados (networking + attendee fieles)

#### M.4 — Social mobile — 7/7 **IMPLEMENTADO 2026-07-09 (QA vivo pendiente)**
- [x] **Header sticky blur** (espejo `social.tsx:183-214`): back + Social + MomentosRow + SegmentedControl con indicador framer layoutId — contenido scrollea POR DETRAS del blur
- [x] **Momentos**: row espejo (upload circle + "Tu" + anillos accent/tenue reactivos via STORY_SEEN_EVENT external store) + **MomentosViewerM full-screen** (espejo Expo: negro, dots barra, auto-advance 5s, tap zones 40/60, marca visto). Upload = input file + centerCropImage 9:16 W.6
- [x] **Feed**: PostCardM espejo (autor+timeAgo, body, foto 240, corazon --heart pop spring, comentarios) + infinite scroll cursor reuso W.6 (`/api/social/wall`) + like optimista con rollback (pull-refresh = gesto nativo del browser)
- [x] **SocialFAB contextual** espejo: crear post (Feed) / subir foto (Memorias), press scale + vibrate medium
- [x] **CommentsSheetM**: SheetM + burbujas + auto-scroll fondo + input redondo con send accent
- [x] **CreatePostSheetM** espejo pageSheet: header Cancelar/Publicar pill, textarea autofocus, foto opcional con preview+quitar (object URL en handler — sin setState-in-effect), toasts published/en-revision
- [x] **Memorias**: ContestBannerM (countdown useNow 1s, podio medallas 72/56, corona winner) + PhotoGridM (triples + OFICIAL 2-col + corona rank) + **PhotoViewerM full-screen** (fade, swipe scroll-snap, counter, footer autor/caption/heart). Regla W.6: like a foto propia = toast informativo
> Verificacion 2026-07-09: typecheck+lint 0 errores (3 violaciones react-hooks corregidas) · 517/517 vitest · E2E mobile-shell 14/14 (+social: momentos+segmented+FAB contextual) · screenshots 390 revisados (feed + memorias fieles). QA vivo: momentos con stories reales, viewers, uploads con crop, comentarios

#### M.5 — Speakers + Sponsors mobile — 5/5 **IMPLEMENTADO 2026-07-09 (QA vivo pendiente)**
- [x] **SpeakersM** (espejo `speakers.tsx` 308): search client 400ms + Destacados (keynotes>=2 o top-5, cards 200 foto 4:5 + badge Keynote, ocultas al buscar, "Ver todos" con window.scrollTo) + Todos con contador y rows foto 56 + badge sesiones
- [x] **SpeakerDetailM `/speaker/[id]` NUEVA** (espejo 295): hero cuadrado + nombre 28 + "en" empresa + estrellas avg·votos + LinkedIn (blanco en noir espejo) + Calificar dorado #B5A68B + bio + sesiones → /session/{id}. Rating reusa SpeakersRatingModal W.5 via wrapper `.speakers-root` (patron RatingModalM). Desktop redirige a /speakers
- [x] **SponsorsM** (espejo `sponsors.tsx` 391): tiers platinum(2col glass)/gold(3col grad)/circulos(4col), **living shuffle 7s** con framer layout spring pausado en scroll/busqueda, search 350ms, gradientes TIER_ACCENT literales
- [x] **SponsorDetailM `/sponsor/[id]` NUEVA** (espejo 690): nav flotante (back + corazon --heart canon), hero logo 100, sesiones, servicios glass + solicitud enviada verde, **trivia auto-abre** al llegar de visit-stand (modal espejo :527-689: A-D estados verde/rojo, progress violeta, +pts, resumen 48 auto-close 2.5s, vibrate success/error), web/email. visit-stand + recordView al montar (una vez, sin toast +pts — regla)
- [x] **Contacto sponsor como sheet** (espejo `sponsor-contact.tsx` 201 — misma pantalla SIN ruta aparte, divergencia estructural minima): handshake + chips con check + mensaje condicional + Enviar (N) + ALREADY_CONTACTED → sent
> Verificacion 2026-07-09: typecheck+lint 0 errores · 517/517 vitest · E2E mobile-shell 16/16 (+speakers/sponsors: listas + tap → detalles) · screenshots 390 revisados (sponsor detail fiel). QA vivo: shuffle 7s, trivia con preguntas reales, contacto, rating speaker

> **Fix QA Kamilo 2026-07-09 — skeletons mobile (2 rondas)**: (a) recortados a la izquierda: los `loading.tsx` desktop renderizan CanvasCard (`width: calc(100vw - 150px)`) que a 390px quedaba pegado a la izquierda → dual render + safety net CSS `<640px` `.canvas-card-root { width:100% }`. (b) Rechazo del generico ("ni siquiera son los propios de los componentes") → **skeletons espejo por modulo**, transcripcion de los pre-built de Expo `Skeleton.tsx`: `HomeSkeletonM` (:286 hero+carrusel 172+dots+grid 2x2), `AgendaSkeletonM` (:53 day pills 56x64+track pills+timeline hora, usado por /mi-agenda y /agenda), `SpeakersSkeletonM`+`SpeakerDetailSkeletonM` (:91/:133 — el detail lo reusa /session/[id] igual que Expo session:176), `SponsorsSkeletonM`+`SponsorDetailSkeletonM` (:184/:219 brand wall tiers), `ContentListSkeletonM` (:166, attendee/[id] rows=4 espejo attendee:93). Social/Perfil/MiQr sin skeleton Expo → calcan su vista M (segmented+momentos+posts / hero+stats+datos / badge card+QR). Base comun `BoneM` (pulse 0.06→0.14 900ms = SkeletonGroup). +6 loading.tsx nuevos (mi-agenda, mi-qr, session, speaker, sponsor, attendee) con shell desktop minimo; `MobileSkeletonM` generico queda SOLO como fallback del root (/about, placeholders). Hook `MOCK_SLOW_PATH` (lista por comas) + `MOCK_SLOW_MS` en mockBackend para QA visual. Ronda 3 (QA "cargan los genericos y despues los propios"): el root `(app)/loading.tsx` mostraba el generico en la fase 1 de la navegacion antes del loading del segmento → root mobile ahora SIN shapes (stage oscuro + tab bar, misma filosofia que el shell minimo desktop) y `MobileSkeletonM` generico ELIMINADO. De paso: 2 errores lint colados en M.5 (SpeakersM memoization → derivado por compiler; SponsorsM setState-in-effect → shuffle guarda orden de ids, display derivado). Verificado: typecheck+lint 0 err · 517/517 vitest · E2E 16/16 · 7 screenshots loading 390px revisados (todos calcan su modulo, sin recorte).

#### M.6 — Desafio mobile — 3/3 (implementado 2026-07-10, Fable)
- [x] **Hub espejo `leaderboard.tsx`** (1421): `DesafioM` pantalla stack **Noir forzado "dark island"** — `.dxm-root` redefine en scope los tokens de tema globales (MobileHeader/SheetM/BoneM van noir gratis) + los `--dx-*` (reusa RgbRing/RgbRect/RedeemModal/Avatar del W.9 desktop sin cambios). Hero HUD (borde RGB conic si top-3, rank cyan 40, SegmentedBar 10 con glow, mini top-3 con RgbRing en #1, Ver ranking) + golden tickets rows + Premios preview (afford/agotado) + MotivationalTip 7 estados + Retos (Proximo: X) + Pasaporte (stamps 5 + "+N"). Entrada stagger FadeIn espejo (delays 0-450). Misma capa de datos W.9 (`desafio-client` + lazy fetch por sheet). Skeleton `DesafioSkeletonM` calca la vista (island oscura tambien en loading)
- [x] **6 bottom sheets espejo** (SheetM gana prop `snap` = snapPercent Expo): Retos 75 (completados ARRIBA espejo :594, orden inverso al panel desktop) / Ranking 60 (podio 72/56 + confeti 14 dots determinista + filas 4-5 + MyPositionPop spring si >5) / Pasaporte 80 / Premios 80 (5 estados CTA espejo: Canjear/Ver ticket/Canjeado/Agotado/Faltan X) / Redeem-confirm 32 (confirm-first espejo Expo, "Canjeando…" en el sheet — divergencia deliberada del optimista desktop) / Rules 70 (reglas + tabla puntos por accion)
- [x] **Modales QR espejo**: canje reusa `RedeemModal` W.9 (QR RgbRect + countdown m:ss en vivo, dynamic import = qrcode.react fuera del bundle) + `GoldenTicketModalM` NUEVO (:900-993: linea accent + overline Ganador/Reclamado, claim_code XL espaciado, QR marco blanco borde platino solo pending, fecha reclamo; accent dual GOLD/TEAL)
> **+ Slide de navegacion stack (M.0, gap cazado por Kamilo 2026-07-10)**: espejo `(app)/_layout.tsx:81` Expo — MobileGate envuelve rutas stack en wrapper keyed por pathname con slide CSS 300ms desde la derecha (push), desde la izquierda -30% (popstate = back), session-stream desde abajo (:108 fullScreenModal); tabs instantaneas, primer mount sin anim, reduced-motion off, `overflow-x: clip` en html/body <640. Derived-state R19 con `popped` en useState (leer refs en render lo veta el lint).
> Verificacion 2026-07-10: typecheck+lint 0 errores · 518/518 vitest (+1 slide push/pop) · E2E mobile-shell 19/19 serial (+3 M.6: hub+ranking, premios 5 estados, golden modal) · desafio.spec desktop 11/11 (test 390px re-apuntado a DesafioM) · 8 screenshots 390px revisados contra Expo. Gotcha nuevo: strict violation de Playwright NO se reintenta — esperar `toHaveCount` del catalogo full antes de assertar CTAs (el fallback preview duplica "Canjear").

#### M.7 — Comunicacion + streaming QA — 8/8 (CERRADO 2026-07-10, Fable — eventos-web `51857d8`)
- [x] Anuncios mobile: espejo `anuncios.tsx:46-47` (body COMPLETO inline; SOLO action_url tocable, dot violeta/dorado) + **detail full-screen slide desde la derecha** con CTA via parseActionUrl (decision Kamilo: ver el anuncio antes de saltar; en Expo el tap navega directo). Mark-read total al montar. SIN pull-to-refresh (divergencia aprobada — data:invalidate cubre)
- [x] Encuestas mobile: lista activas (tocables, progreso X/Y + Respondida) / cerradas (atenuadas) espejo + **SurveyDeck W.2 en overlay slide** full-screen. Voto optimista identico desktop. **RT nuevo: poll:new/poll:closed → refresh en GlobalSocketProvider** (espejo Expo :37-51, beneficia desktop). Mock E2E gano surveys+vote (deuda desktop saldada)
- [x] Soporte mobile: my-support + support-contact en /soporte — lista (badges Pendiente/Leido/Resuelto, respuesta admin barra verde, "Esperando respuesta…") + form slide `?nueva=1` (asunto 200 + mensaje 2000, errores tipados 429/403/422). **Gotcha staleTimes**: al volver de una URL visitada el payload RSC cacheado re-seedeaba la lista pre-creacion → `router.refresh()` tras crear
- [x] FAQ/Asistente mobile: espejo `faq.tsx` — OrbBlob desktop reusado (idle/active/settled), state machine 800ms sin red, response card mordida + barra cian #5DE4D4, chips solo browsing, contacto → /soporte?nueva=1 + Mis consultas (N). **Cadena Perfil → Ayuda → FAQ → Soporte completa**
- [x] Documentos mobile: **adaptacion del W.13 desktop (canon — decision Kamilo: la pantalla Expo es legacy huerfana y queda pendiente de adoptar este diseno, deuda backlog Expo)**. Lista compacta + ZIP bulk pill + preview en SheetM 92dvh reusando DocumentPreview + URL state ?id. `lib/documents-client.ts` compartido. Solo deeplink (sin entrada de menu, espejo alcance)
- [x] About — ruta NUEVA /about **HECHO en M.1 2026-07-09** (espejo `about.tsx` 175: imagen 16:9 + texto + links branding; entrada = card del Home SOLO registration/draft, espejo `index.tsx:170-185`)
- [x] ~~Chat full-height sin stream~~ **DESCARTADO (decision Kamilo 2026-07-10)**: sesion sin stream entra igual a /session-stream y el player muestra "Transmision no disponible aun" (ya implementado, StreamPlayer EmptyState). El modo chat pantalla completa NO va
- [x] QA StreamShellMobile vs Expo `session-stream/[id].tsx`: player (iframe custom → YouTube → generica → placeholder, + Vimeo dedicado que Expo no tiene) / RatingModal 1.5s finished+no-calificada (con gate anti-carrera superior a Expo) / tracking session_stream_view ≥3s una vez (+ pagehide/keepalive) / speaker row (fix separador " · ") / paneles OK. **GAP anotado: panel `custom` es placeholder** (Expo monta WebView a custom_url; en web implica decidir CSP frame-src) — deuda W.4, decision pendiente

#### M.8 — Vendor W.15 (cierre del workstream) — 11/11 **IMPLEMENTADO 2026-07-11 (Fable, QA vivo Kamilo pendiente)** (re-baseado 0/9→0/11 tras lectura completa Expo)
> Procedencia verificada: ~3.000 lineas Expo (8 pantallas) + 18 endpoints backend mapeados (auditoria 2026-07-04). Gating: `attendee.has_vendor_access` del `GET /auth/me` (`isVendor = role==='vendedor' || hasVendorAccess`, espejo `(tabs)/index.tsx:64`).
> **Decisiones Kamilo 2026-07-11**: TODO NATIVO espejo Expo — `navigator.share` (share sheet nativo browser) para export CSV y link equipo, `tel:`/`mailto:` links nativos. **WhatsApp dedicado APLAZADO** (sin botones wa.me por ahora — mas adelante). Scanner = **@zxing/browser** (prior art REAL de eventos-kiosko `useQrScanner.ts` — BarcodeDetector era error del backlog: no existe en Safari/Firefox). Scanners siempre oscuros #0e0e0e (espejo); resto adapta Lux/Noir. Join-team sin redirect desktop (card centrada universal). Modal invitacion staff GLOBAL (espejo layout Expo).
> QA nota: getUserMedia exige contexto seguro — camara NO funciona via IP de red; QA device del scanner requiere `next dev --experimental-https` o flag Chrome.
- [x] Capa de datos + gating: proxies `/api/stand/*` + `/api/leads/*` + `lib/stand-client.ts`/`lib/leads-client.ts` + exponer `attendee.has_vendor_access` en getCurrentUser (hoy se descarta) (espejo `standApi`/`leadsApi`/`useStand`/`useLeads`)
- [x] **Vendor Home** (NUEVO — espejo `(tabs)/index.tsx:249` + `VendorHappeningNow.tsx` 467): HomeMobile isVendor reemplaza HappeningNowM por split 65/32 — carrusel sesion compacta+HUD / MiStandCard noir (QR breathing, pill N leads, Abrir → /mi-stand)
- [x] Mi Stand dashboard (espejo `mi-stand.tsx` 288: hero logo/tier/rol + 3 stat cards navegables + FAB scanner breathing con scanline + empty state)
- [x] Mis Leads (espejo `leads.tsx` 195 + `lead-detail.tsx` 317: grouped Hoy/Ayer/fecha + tier hot/warm/cold + notas + historial ediciones + Guardar solo dirty)
- [x] Export CSV (`GET /me/leads/export`) — navigator.share con File si disponible, fallback descarga directa + toast
- [x] **Scanner stand QR camara browser** (espejo `scanner-stand.tsx` 542: frame 4 esquinas + scanline + sheet resultado 55/error 38 + tier chips post-captura + DUPLICATE_LEAD warning — @zxing/browser)
- [x] Scanner invite equipo (espejo `scanner-invite.tsx` 282: resolve-qr → sheet invitar, gating owner)
- [x] Solicitudes stand (espejo `stand-contacts.tsx` 211: chips servicios interes + mensaje quote + acciones tel/mailto — wa.me aplazado)
- [x] Stats (espejo `stand-stats.tsx` 360: overview rows + trend vs ayer up/down + TierBar + MemberBar + top services + sheet export)
- [x] Team management (espejo `mi-equipo.tsx` 567: 3 vias invitar QR/busqueda/email + share link navigator.share/clipboard + transfer + remove, owner-only, sheets confirmar/acciones/compartir)
- [x] Join-team CON SESION (espejo `join-team/[token].tsx` 276: card centrada 5 estados, auth requerido) + **sockets `staff:invited`/`staff:removed` en GlobalSocketProvider + modal invitacion global + fetch pending-invitations al montar** (NUEVO — espejo `useDataInvalidation.ts:329,403` + `useStaffInvitations` + `StaffInvitationModal`) + tests
> Verificacion 2026-07-11: typecheck + lint 0 errores · 555/555 vitest (+8 groupLeadsByDay/leadTimeAgo) · E2E mobile-shell 31/31 serial (+7 M.8: home vendor, mi-stand, leads+detail, mi-equipo, stats→contacts, gating no-vendor, redirect desktop) · 9 screenshots 390px revisados (home split 65/32, stand, leads Hoy/Ayer, detail tier/historial, equipo owner, stats barras, contacts, join-team, scanner permiso). Gotchas nuevos: **eventos-web usa pnpm** (npm install revienta contra node_modules .pnpm) · **toLocaleString es-CO en Node != Chrome → hydration mismatch** (formateo manual de fechas, patron formatShortDate) · fixtures E2E con offsets de HORAS cruzan medianoche y flakean (lead "Hoy" = now-5min). QA vivo Kamilo pendiente: scanner con camara real (getUserMedia exige localhost/HTTPS — `next dev --experimental-https` para device), export CSV share sheet en celular real, flujo invitacion socket en vivo, Lux.

### ~~BLOQUE 7 — W.X Welcome Showcase~~ — **ELIMINADO 2026-07-04**
> El "showcase 6 beats" era invento: el onboarding real de Expo (~4.500 lineas) es un
> wizard de REGISTRO (welcome hero + auth + foto + forms dinamicos + survey). La webapp
> ya cubre su parte con W.1 magic link. Boton "Ver introduccion" queda oculto (Bloque 0).
> Si Fase 2 pide espejo del wizard completo, se disena entonces.

### DEPLOY DEMO — 6/6 **HECHO 2026-08-01/02** (produccion real, luego destruida con snapshot)

> Se monto el stack COMPLETO en DigitalOcean nyc1 sobre `killjoy.pro` y se
> corrieron los 8 escenarios de experiencia. **Guia probada: `docs/infra/deploy.sh`
> (~15 min; el primer montaje a mano tomo 3 horas).** Diagnostico completo:
> `docs/infra/DIAGNOSTICO-2026-08-02.md`.
- [x] Hosting + dominio — 1 droplet 4 vCPU/8 GB **$48/mes** + Cloudflare + R2 (`cdn.killjoy.pro`)
- [x] Backend Laravel prod — env + Horizon + cron + R2 + **Resend** (magic links verificados)
- [x] eventos-web prod — Next standalone + HTTPS + los `X-Forwarded-*` que nadie documentaba
- [x] eventos-socket prod — **PM2 cluster 4 procesos** + Redis DB 2 + WebSocket puro
- [x] Evento demo curado — 5.050 asistentes, 120 archivos, slides, FAQ, premios, Q&A (`DemoMediaSeeder` + `DemoCompletoSeeder`)
- [x] DSN Sentry prod
- [x] **BONUS: 25 bugs corregidos**, 8 de ellos visibles SOLO abriendo el navegador

**Droplets DESTRUIDOS el 2026-08-02** (con snapshot, ~$0,40/mes). Ya no facturan.
Volver: `docs/infra/COMO-VOLVER.md` — **apagar NO detiene el cobro, hay que destruir.**

---

### RENDIMIENTO Y CAPACIDAD → **MOVIDO 2026-08-02** a `docs/roadmaps/ROADMAP-INFRAESTRUCTURA.md`

> Este bloque nacio de una premisa que se midio y resulto **falsa**: los "45 ms
> de peaje fijo" que iban a duplicar la capacidad eran 33, y el propio documento
> decia que 15-25 seria sano. **Ese 2x nunca estuvo disponible.**
>
> Lo que si aparecio, midiendo de verdad sobre el droplet:
>
> | | antes | despues |
> |---|---|---|
> | 1.500 personas navegando | **82% de CPU** | **46%** |
> | 2.500 personas navegando | sin medir | **68%**, 87,4 pantallas/s, 0 errores |
> | `branding` | 37,8 ms | **27,6 ms** |
>
> Dos causas, ninguna sospechada: Sanctum dejaba **una escritura en cada
> lectura**, y el panel de administracion **arrancaba entero en cada peticion de
> la API** porque `filament:optimize` nunca se habia corrido.
>
> **Y navegando —no midiendo— salio algo peor: los limites por IP contaban para
> TODA la plataforma** (5 ingresos por minuto entre todos), porque la webapp
> llama al backend desde el servidor. Un evento real se caia en la puerta.
>
> **Todo el frente, con el catalogo de niveles vendibles, vive ahora en
> `docs/roadmaps/ROADMAP-INFRAESTRUCTURA.md` (2/26).**

---

### SEGURIDAD DEL STAFF — 0/2 (movido a post-deploy 2026-08-01)

> **Raya escrita por Kamilo: la URL del admin no sale a NINGUN prospecto ni recibe
> datos reales sin el 2FA puesto.** Ver [[project_security_audit]].
- [ ] 2FA / passkeys en el panel admin (el HTTPS que exigen ya lo regala el deploy)
- [x] **HECHO 2026-08-18.** **Rotar credenciales de R2** — quedaron escritas en el chat del 2026-08-02.
      R2 NO se destruyo con los droplets.
- [x] **HECHO en el Nivel 1 (2026-08-17).** Cloudflare a **naranja** antes de exponer nada (en gris se registraron 125
      intentos de bots buscando `/.env` desde 7 IPs)

> **Paralelos movidos fuera de este doc (2026-07-14):** Paridad config admin↔superficies,
> Event Pulse cliente, y Backlog Expo NO son features de webapp — viven ahora en
> `docs/living/PENDIENTES.md` seccion "Webapp — paralelos / backlog".

---


## BACKLOG GRANULAR — TODO desglosado

### W.0 — Spatial UI base (24/24, **CERRADO 100% 2026-07-04**)

> Cerrado al reclasificar 3 items nice-to-have: Command palette ⌘K → Fase 2 (feature power-user, no critico para venta), Pre-load vecinos → W.12 Polish (optimizacion perf), Validar device real → W.12 Fase 0 (QA cross-modulos).

- [x] Setup canvas universal (1130×664 tablet H, 1600×920 desktop cap)
- [x] Sidebar pill flotante izq 52px con iconos (⌂ 📅 ●live 👥 💬 🏢 📰 👤)
- [x] Bell redondeado top-right
- [x] Canvas raiz formulas clamp
- [x] 3 estados shell (Browse/Live/Conectar)
- [x] Animaciones spring 350ms
- [x] Reduced motion respeto
- [x] Home cinematic mute 3 estados
- [x] Companion demo W.2
- [x] Shell W.0 implementado React validado 2026-05-04
- [x] Tokens Lumina Noir CSS vars portados
- [x] Ambient background sunken
- [x] EventThemeProvider primary_color dinamico
- [x] ProfilePopover con logout/idioma/tema
- [x] Stage centrado padding simetrico
- [x] CanvasCard width 1600 cap + height min(vh-56, 920)
- [x] Sin aspect-ratio rigido (fix tablet 2026-05-05)
- [x] Theme toggle pill bottom-left
- [x] Live pulse indicator
- [x] Connection status pill
- [x] ~~**Modulos navegables top-level desde sidebar**~~ 2026-06-21 — 5/5 items navegan (home, agenda, live, speakers, social). Sponsors disabled con tooltip "proximamente"
- [x] ~~**Command palette ⌘K**~~ → **reclasificado Fase 2** (power-user, no critico Fase 1)
- [x] ~~**Pre-load vecinos**~~ → **reclasificado W.12 Polish** (optimizacion perf cross-modulos)
- [x] ~~**Validar device real** iPad/Pixel/iPhone~~ → **reclasificado W.12 Fase 0** (QA cross-modulos)

### W.1 — Setup + Auth + UI Foundation (107/107, **CERRADO 100% 2026-07-04**)

> Cerrado al reclasificar 5 items diferidos: B4 StaggerList + B11 Swipe haptics → Fase 2 (polish device-only), Smoke device real + Lighthouse final → W.12 Polish, CSP whitelist Vimeo/Sentry → agrupado con W.4 cierre.

- [x] Next.js 15 scaffold TypeScript Tailwind shadcn
- [x] Tokens Lumina Noir/Lux CSS vars
- [x] i18n next-intl es-CO/en/pt-BR prefix
- [x] Magic link auth 4 steps (email/sent/password/verifying)
- [x] StateChart auth flow
- [x] Mailcheck typo detection
- [x] Network status banner
- [x] LoginSlideshow Ken Burns video
- [x] Password fallback completo
- [x] Layout shell + middleware auth gate
- [x] LuminaToast component
- [x] FormField component
- [x] EmptyState component
- [x] Skeletons base
- [x] Animaciones Framer Motion microinteracciones
- [x] Error boundaries
- [x] Sentry frontend integrado
- [x] Tests Vitest 22 + Playwright 12 (34/34)
- [x] CSP base configurado
- [x] DOMPurify base
- [x] Cross-tab sync logout
- [x] useExitGuard form dirty
- [x] (...75 items mas implementados, F0-F10)
- [x] ~~**B4 StaggerList animation**~~ → **Fase 2** (polish nice-to-have)
- [x] ~~**B11 Swipe haptics**~~ → **Fase 2** (requiere device real touch)
- [x] ~~**Smoke test 3 viewports device real**~~ → **W.12 Fase 0** (QA cross-modulos)
- [x] ~~**Lighthouse final pass**~~ → **W.12 Fase 3** (batch cross-modulos)
- [x] ~~**CSP whitelist Vimeo + Sentry final**~~ → agrupado con **W.4 Streaming cierre** (depende integracion completa)

### W.1B — Backend magic link (10/10, CERRADO)

- [x] Setup Mailpit
- [x] Migration `magic_link_tokens` SHA-256
- [x] Migration `event_login_slides` con video/overlay toggle
- [x] POST /auth/magic-link rate limit 3/email/hora anti-enum
- [x] POST /auth/verify-magic-link (410 expired/used)
- [x] POST /auth/refresh token rotation
- [x] GET /events/{slug}/login-slides publico
- [x] Filament LoginSlideResource drag-reorder
- [x] Mailable branded por evento
- [x] Pest tests happy path + 8 edge cases

### W.2 — Home (16/16, CERRADO 100% 2026-07-08)

- [x] Hooks useEvent base
- [x] Hero cinematic + wordmark
- [x] Estado PRE (countdown firma)
- [x] Estado LIVE (split 7fr/3fr poster + happening-now feed)
- [x] Estado ENDED (split 6fr/4fr poster + recap stats + conexiones)
- [x] HappeningNow feed scrolleable salas activas
- [x] Featured con tinte accent
- [x] Recap stats 2x2 + tier Gold + CTA "Ver mi recap" (2026-05-06)
- [x] EventPoster compartido 3 estados
- [x] Canvas raiz universal aplicado
- [x] ~~Sponsors logo band~~ → **INVENTO eliminado** (auditoria 2026-07-04: home Expo no tiene strip de sponsors)
- [x] **GamificationHud preview LIVE** — HECHO 2026-07-08 (slide del carrusel Home LIVE, borde RGB girando, card entera deeplink /desafio, tamano fluido cqw, datos SSR)
- [x] ~~Anuncios mini con count badge~~ → **CUBIERTO por Bell sidebar W.14** (divergencia intencional ya decidida: Expo badge en campana del header = webapp badge en BellPopover)
- [x] **Post-event survey prompt** ENDED — HECHO 2026-07-08 (prompt dentro de EventArchive → ruta /encuestas SurveyDeck por slides)
- [x] **EventArchive** ENDED — HECHO 2026-07-08 (espejo Expo puro: banner "Evento finalizado" + 3 stats + prompt encuesta + 4 links archivo)
- [x] ~~Multi-sede pill~~ → **INVENTO eliminado** (backend solo tiene UN venue string)
- [x] ~~Foto real speaker~~ — **HECHO 2026-07-04** (`RoomAvatar` con `speaker_photo_url` + gradiente fallback, espejo Expo)
- [x] ~~Proximos eventos org en ENDED~~ → **INVENTO eliminado** (Expo no lo tiene)
- [x] ~~Atmosfera dinamica por estado~~ → **INVENTO eliminado** (nice-to-have sin espejo; la cinematic ya existe)
- [x] ~~useHappeningNow dedupe~~ → **INVENTO eliminado** (Expo no dedupea; filtra por estado y corta a 2)

### W.3 — Agenda (25/25, **CERRADO 100% 2026-07-04** — badges/conflict/check-in eran inventos; URL state → Fase 2)

- [x] Tipos + fetcher SSR
- [x] Lista sesiones agrupada por dia
- [x] Timeline visual
- [x] Heart favorito optimistic
- [x] Tabs Agenda / Mi Agenda
- [x] Chips tracks filtrables
- [x] Search debounce
- [x] Favoritos toggle wired
- [x] DetailPanel 320-520px slide-in 480ms
- [x] Header detail con badges (EN VIVO, tipo session, track)
- [x] Time + location + capacity card
- [x] Action buttons (Favorita, Calendario, Evaluar)
- [x] Description + speakers grid
- [x] Speakers clickables → speaker detail
- [x] Rating modal 5-star + comment 280 chars
- [x] my-ratings backend wired
- [x] .ics download individual
- [x] Add to calendar variants (native/browser)
- [x] Highlight pueden expandirse desde home (param highlightSessionId)
- [x] Colores por tipo session
- [x] AttendeesPop modal (centrado blur lista)
- [x] Stream/Recording CTA
- [x] Session detail page individual
- [x] Tests Vitest 55
- [x] ~~Lifecycle badges AJUSTADA/CANCELADA~~ → **INVENTO eliminado** (auditoria 2026-07-04: el API de agenda NO expone los 3 tiempos como campos y la agenda Expo NO pinta badges. El espejo real es el toast `agenda:delayed` — YA implementado en W.11)
- [x] ~~Conflict detector visual~~ → **INVENTO eliminado** (cero rastro en Expo y backend)
- [x] ~~Room check-in boton~~ → **INVENTO eliminado** (el asistente nunca se auto-checkea: presenta su QR y el staff escanea via `/staff-checkin/*`. El "Mi QR" mobile vive en Mobile parity)
- [x] ~~**Bulk .ics download**~~ (todas mis favoritas un solo archivo) — hecho 2026-06-21 (Sprint 1 item 8): `lib/ics.ts` generador puro RFC 5545 (VCALENDAR + N VEVENT con UID determinista, DTSTAMP UTC, escape comas/semicolons/backslash). Boton "Todas" del AgendaHeader visible cuando `countMine > 0` en CUALQUIER dia. Filename `mi-agenda-{event.slug}.ics`. +16 tests vitest
- [x] ~~Recordatorio push 10min antes~~ → **movido a W.12 Web Push** (vive con la infra push, no es item de agenda)
- [x] ~~**RT socket invalidation**~~ — CUBIERTO por W.11 (verificado 2026-07-04: `data:invalidate{agenda}` → router.refresh + prop-sync `AgendaView:62`. Alias `sessions` agregado en Bloque 0 `3e73b29`)
- [x] ~~URL state shareable~~ → **reclasificado Fase 2** (concepto webapp-only sin espejo, nice-to-have)
- [x] ~~**Playwright E2E happy path**~~ — YA EXISTIA (verificado 2026-07-04: `agenda.spec.ts` 300 lineas, 16 tests: days strip, tabs, filtros, search, DetailPanel, favoritos 200/500, rating, live, highlight x3, Esc)

### W.4 — Streaming (92/92 — CERRADO 2026-07-04 noche, recount contra codigo)

> Recount en QA vivo: la mayoria de items abiertos YA estaban implementados en
> StreamShell.tsx + hooks y el doc no se habia actualizado. Replay verificado
> en vivo. Items hechos hoy confirmados:
> anuncios in-stream (useAnnouncementOverlay + chat:pinned banner) · custom
> panel iframe (StreamShell:316) · replay (boton agenda → player, paneles off) ·
> rating auto con gate ratingsLoaded (fix race e1b0c9a) · mobile + tablet
> layouts (StreamShellMobile/Tablet) · floating emojis 5 paths · slow mode
> threaded a 3 variantes · CSP Vimeo via frame-src https (W.13).
> Reclasificados fuera: Trivia panel → W.16 (skip webapp) · Playwright
> cross-tab con socket real → W.12 · 17 items menores sin detalle → QA W.12.
> El detalle historico de items [x] previos quedo en git (commit anterior).

### W.5 — Speakers (35/35, **CERRADO 100% 2026-07-04**)

> Implementado en commit `134bf6e` (2026-05-09). Cerrado 100% real el 2026-07-04 al reclasificar los 3 items de QA operacional (Lighthouse Perf + Lighthouse Acc + device fisico) a W.12 Polish + PWA, que es donde vive el QA cross-modulos pre-deploy.
> Espejo Expo: sin tracks/featured/keynote flags DB (todo derivado), click sesion → /agenda?highlight=X

**Fase 0 — Hooks (3/3)**
- [x] `fetchSpeakers(eventId)` lista (lib/speakers.ts)
- [x] `fetchMySpeakerRatings(eventId)` (lib/speakers.ts)
- [x] `rateSpeakerRequest` + `fetchMySpeakerRatingsClient` (lib/speakersClient.ts)

**Fase 1 — Featured derivation (3/3)**
- [x] Algoritmo `getFeatured()` (speakersDerive.ts) — keynotes o top sessions, no flag DB
- [x] Fallback top by session_count si no hay keynote
- [x] BreathingCarousel auto-rotate + flechas al hover

**Fase 2 — Lista (4/4)**
- [x] Lista alfabetica via `sortAlphabetical()`
- [x] SpeakerListItem (photo + nombre + rol + session count badge)
- [x] Search debounce 400ms
- [x] Empty state (2 variantes: no-speakers + no-results)

**Fase 3 — DetailPanel (7/7)**
- [x] SpeakerDetailPanel slide-in con race protection
- [x] Hero foto + nombre + job + company + bio
- [x] Sesiones grid clickable
- [x] Click sesion → `/agenda?highlight={id}` con router.push (sin race vs URL sync)
- [x] LinkedIn button condicional (test E2E lo verifica)

**Fase 4 — Rating (4/4)**
- [x] SpeakersRatingModal con stars + comment 280 chars
- [x] UNIQUE 409 → silencioso + re-hidrata my-ratings (sin error toast)
- [x] Estado "Calificar" vs "Evaluado" + boton disabled si ya calificado
- [x] Optimistic update + revert en fallo real

**Fase 5 — Deep link + viewport (4/4)**
- [x] Deep link `?id=X` auto-open via useState initializer (R19 set-state-in-render)
- [x] URL sync sin recargar (router.replace scroll:false)
- [x] SSR fetcher (page.tsx hace `Promise.all([speakers, myRatings])`)
- [x] ~~Validar 3 viewports en device real~~ → **reclasificado a W.12 Fase 0** (QA cross-modulos pre-deploy)

**Fase 6 — Tests (3/3)**
- [x] Vitest `tests/components/speakers/speakersDerive.test.ts`
- [x] Vitest `tests/lib/speakersClient.test.ts`
- [x] Playwright `e2e/speakers.spec.ts` (13 escenarios: auth gate, search, panel, stars, LinkedIn condicional, ya calificado, modal focus, optimistic, 409 silencioso, 500 revert, click sesion, deep link, Esc layer order)

**Fase 7 — Cierre (7/7)**
- [x] ~~Lighthouse Performance >=85~~ → **reclasificado a W.12 Fase 3** (batch cross-modulos)
- [x] ~~Lighthouse Accessibility >=95~~ → **reclasificado a W.12 Fase 2** (WCAG AA global)
- [x] Tests verdes (391/391 vitest verde 2026-07-04)
- [x] Detalle commit DaVinci (commit `134bf6e` describe el modulo)
- [x] Memoria actualizada (`project_w5_speakers_v2.md` cierre formal agregado 2026-06-20)
- [x] PARITY-MATRIX seccion W.5 actualizada
- [x] ~~Validar device real~~ → **reclasificado a W.12 Fase 0** (QA responsive cross-modulos)

### W.6 — Social Wall (28/28, **CERRADO 100% 2026-07-05** — Momentos + Memorias + Contest hechos (BLOQUE 1); Hashtags eliminado del scope; denominador re-baseado 40→28)

> Recount 2026-06-20: el feed editorial implementado en `/social` (compartido con W.8 Networking) es W.6 Wall. Doc anterior listaba 0% por error de auditoria. Lo IMPLEMENTADO marcado [x] aqui.

**Fase 0 — Hooks (3/3)**
- [x] `fetchWallFeed` SSR (lib/social.ts) — backend usa `?page=` (paginacion pendiente UI)
- [x] ~~`usePostComments` lazy~~ — ES LAZY REAL (verificado 2026-07-04: `InlineComments` solo monta al expandir + fetch en useEffect `InlineComments.tsx:50-69`; feed inicial solo trae `comments_count`)
- [x] `createWallPost` mutation con foto opcional + manejo `pending` (post en moderacion)

**Fase 1 — Feed (4/4)**
- [x] PostCard render
- [x] InlineComments expandible
- [x] ~~Paginacion~~ — **HECHO 2026-07-04** (`7afb5d0`): infinite scroll cursor-based (fetchWallFeed corregido a `?cursor=`, proxy `/api/social/wall`, `appendFeedPage` dedup, sentinel IntersectionObserver + shimmer). Verificado vivo con 99 posts (5 paginas). +5 vitest + E2E `wall-paged`
- [x] Empty hint en SidebarRight ("Conecta con asistentes desde Personas")

**Fase 2 — Like + Comments (5/5)**
- [x] Heart optimistic (`toggleLikeOptimistic` + `toggleWallLike`)
- [x] POST revert on fail (SocialClientError catch)
- [x] Sync likes_count con server (race condition manejada)
- [x] Click "X comentarios" expande sub-thread (estado `expandedComments`)
- [x] Input crear comentario inline (Composer + handleCommentAdded)

**Fase 3 — Crear post (4/4)**
- [x] Composer textarea max 500
- [x] **Imagen upload** preview antes enviar (File API en createWallPost)
- [x] Post optimistic aparece + lumina toast
- [x] ~~Listener `wall:post` deduplica propio via socket~~ — HECHO por W.11 (verificado 2026-07-04: dedup por server ID `SocialView.tsx:131-136` + `wall:comment` skip propio `:141-150`)

**Fase 4 — Stories "Momentos" (3/3) — CERRADA 2026-07-05 (`50a0f79`)**
> Espejo `MomentosRow.tsx` + `MomentosViewer.tsx` + `useStories` Expo, verificado linea a linea.
- [x] Barra Momentos (anillo accent=no visto / tenue=visto + boton "Tu momento"; visible en Feed y Memorias como el sticky header Expo)
- [x] Viewer card 9:16 centrada en el CanvasCard (auto-advance 5000ms, click zones 40/60, dots, timeAgo, visto al abrir en localStorage `eventos:social:story-seen:{eventId}`, sin salto entre autores; desktop: teclado+Esc+chevrons+click afuera)
- [x] Upload 9:16 (preview + center-crop canvas `image-crop.ts`) → `POST /events/{id}/stories` + toast espejo literal "Momento subido! Visible por 24h."

**Fase 5 — Memorias + Photo Contest (4/4) — CERRADA 2026-07-05 (`50a0f79`)**
- [x] Vista Memorias 5ta del sidebar: grid 3 col con oficial 2x2 badge OFICIAL + upload boton header (crop 1:1, toast "Foto enviada! En revision.")
- [x] `PhotoViewer` marco FIJO 16:9/9:16 por orientacion, foto contain (NUNCA recorta — decision QA), meta en flujo debajo, likes optimistic + haptic + pop; foto propia → toast informativo sin red (anti-gaming backend)
- [x] `ContestBanner` espejo: solo active/ended<24h (scheduled nunca se monta, espejo social.tsx:364), countdown vivo useNow "2h 05m"/"4:07", podio medallas 72/56, corona ganador
- [x] Orden por likes cuando contest activo — client-side, grid y viewer comparten indices

**Fase 6 — Hashtags (ELIMINADA del scope, decision Kamilo 2026-07-04)**
> Click-to-filter borrado del scope: no aporta en este contexto. El parser
> `renderHashtags()` tambien ELIMINADO del codigo (`f0bedaa`) — feature muerto =
> codigo muerto, y de paso se fue el `dangerouslySetInnerHTML` (superficie XSS
> innecesaria); el body volvio a texto plano React. La idea buena derivada —
> analitica de menciones/hashtags trending — pertenece a Event Pulse, anotada
> en memoria `project_webapp_ideas`.

**Fase 7 — Filtros (2/2)**
- [x] View switch Feed/Personas/Solicitudes/Mis posts (sidebar izq) — funcional pero NO son tabs sticky en feed
- [x] ~~**Tabs Recientes / Mas likes / Mis posts** explicitas en vista Feed~~ (hecho 2026-06-20: `FeedTab` type + `sortAndFilterFeed` helper en `socialDerive.ts`, FeedView con state local de tab, 3 empty states distintos por tab, reusa `.sn-rqx-tabs` CSS existente, 6 tests vitest agregados). URL state shareable queda pendiente (nice-to-have, no critico)

**Fase 8 — Tests (3/3)**
- [x] Vitest `tests/components/social/socialDerive.test.ts` (toggleLikeOptimistic + filterMyPosts)
- [x] Vitest `tests/components/social/AttendeeProfilePanel.test.tsx`
- [x] Playwright `e2e/social.spec.ts` (5 escenarios: SSR shell+feed, switch Personas, conectar optimistic, aceptar solicitud, Mis posts vacio)

### W.7 — Sponsors (23/23, **CERRADO 2026-06-21**)

> Sprint 2.A entero en una sesion DaVinci larga. Espejo LITERAL Expo a la izquierda (wall) + DetailPanel der vacio hasta click. Animaciones via framer-motion `layout` spring damping 28 stiffness 120. Lumina toasts top-center con colores neutrales rgba(80,200,120)/rgba(255,100,100) (no `var(--accent)`).

**Fase 0 — Hooks (3/3)**
- [x] useSponsors lista
- [x] useSponsorFavorite toggle
- [x] useSponsorContact submit

**Fase 1 — Brand Wall (5/5)**
- [x] Grid agrupado por tier (platinum 2c, gold 3c, silver/bronze/media 4c)
- [x] Shuffle animation con framer-motion `layout` spring (damping 28, stiffness 120) — equivalente Reanimated
- [x] Stagger reveal on mount
- [x] Search debounce 350ms por nombre + descripcion
- [x] CardPressable scale animation

**Fase 2 — Brand Profile (5/5)**
- [x] Hero logo XL + nombre + descripcion + tier badge (tier label SOLO en wall por jerarquia, no en detail panel — decision espejo Expo)
- [x] Tab Acerca (descripcion completa)
- [x] Tab Servicios (chips multiselect)
- [x] Tab Sesiones (cards time + type + title + location)
- [x] Tab Contactar (form)

**Fase 3 — Favorite (3/3)**
- [x] Heart toggle optimistic (framer-motion, no CSS keyframes)
- [x] Animation spring
- [x] Lista favoritos en Mi Hub

**Fase 4 — Contact + Tracking (2/2)**
- [x] Contact form servicios + textarea mensaje (chips + 409 ALREADY_CONTACTED handled)
- [x] Tracking view fire-and-forget on profile open

**Fase 5 — Trivia integration (3/3)**
- [x] Trivia modal auto-trigger on visitStand (espejo TriviaModal Expo)
- [x] Pregunta + 4 opciones (letras A/B/C/D) + countdown + result + boton "Responder/Siguiente/Ver resultado"
- [x] Auto-close 2.5s + pantalla resumen "+N puntos ganados" feedback

**Fase 6 — Tests (2/2)**
- [x] Vitest 14 (hooks + shuffle + contact form 409)
- [x] Playwright happy path — **12 E2E verde** + Lighthouse acc 98 + CLS 0

**Backend gaps cerrados durante W.7 (BUG-336, BUG-337)**
- [x] SponsorResource expone trivia/passport/visit_points (BUG-336)
- [x] GamificationController visitStand devuelve `points_awarded` distinguiendo idempotente (BUG-337) — patron a auditar en W.3/W.4/W.6/W.9

### W.8 — Networking (21/21, **CERRADO 100% 2026-07-04**)

> Lo "social" implementado en `/social` que NO es feed Wall (W.6) realmente vive aqui.
> Cerrado 100% al reclasificar 4 items: Filtro role (skip — backend publico no expone role), RT listeners (bloqueado W.11), Sugeridos cards grandes (skip — mini-rows funcionan), Tracking analytics (Fase 2). El resto se completo: Mi perfil editable → cubierto por W.18 con link desde sidebar (2026-07-04), E2E happy path ampliado con 5 tests nuevos, cierre commit + memoria.

- [x] Directorio paginado (`PersonasView`)
- [x] AttendeeCard horizontal
- [x] Suggested contacts mini-rows panel der
- [x] Perfil attendee in-slot (panel der MUTA, visionOS)
- [x] 4 estados relacion (none/sent/received/contact)
- [x] Compose inline mensaje opcional
- [x] vCard download + WhatsApp + Email
- [x] Solicitudes Recibidas/Enviadas tabs
- [x] Aceptar / Ignorar
- [x] Bloquear con confirm (hoy `window.confirm`)
- [x] Mi red (contactos aceptados)
- [x] socialClient.ts (fetchAttendeeProfileClient + sendContactRequest + respondContactRequest + blockAttendee)
- [x] API proxies social/*
- [x] Tests vitest 49 (vcard + socialClient + AttendeeProfilePanel)
- [x] Bug handleRespond requestId invertido corregido
- [x] ~~**Reemplazar window.confirm Bloquear por AlertDialog DaVinci**~~ (hecho 2026-06-20: rechazada v1 shadcn AlertDialog generica; v2 final con `ui/confirm-pop.tsx` reusable + CSS global, patron espejo `rating-pop`, Plus Jakarta 700 20px + Urbanist 14px, copy honesto sin promesa de "tu perfil", tests 27/27 verde)
- [x] ~~**Skeleton mejor AttendeeProfilePanel**~~ (hecho 2026-06-20 v1: 3 secciones estructuradas. v2 simplificado mismo dia por feedback usuario "no coincide con lo que hay" — secciones reales Sobre/Intereses/Asistira a son condicionales y pueden no aparecer para muchos attendees → over-promesa visual. Skeleton final es honesto: 1 titulo chico + 3 lineas tipo bio, sin chips ni cards.)
- [x] ~~**Bloqueados list** (vista + boton desbloquear)~~ (hecho 2026-06-20: tercera tab "Bloqueados" en SolicitudesView, `BlockedRow` no clickeable + boton ghost Desbloquear, `fetchBlockedAttendees` SSR, optimistic + revert, 2 tests vitest agregados. Migrar a W.18 Settings cuando exista)
- [x] ~~**Mi perfil editable**~~ → **cubierto por W.18 Hub Personal** (`/perfil` con foto upload + shuffle beam + edicion completa). SidebarLeft de `/social` ahora tiene link clickeable al perfil (loop cerrado 2026-07-04)
- [x] ~~**Filtro role dropdown**~~ → **skip formal** (2026-07-04): backend publico `/events/{id}/attendees` NO expone `role` (privacy/compliance). Agregar seria inventar data
- [x] ~~**RT listeners** `networking:notify`~~ → **reclasificado a W.11 Sockets RT** (bloqueado hasta que W.11 tenga el layer socket completo)
- [x] ~~**Gap C Sugeridos cards grandes**~~ → **reclasificado Fase 2**: mini-rows en sidebar der ya funcionan; refactor de layout es cosmetic no urgente
- [x] ~~**Playwright E2E abrir perfil → conectar → confirmar solicitud**~~ (hecho 2026-07-04: `e2e/social.spec.ts` ampliado con 5 tests nuevos — click sidebar identity → /perfil, filtro Sin contactar, abrir perfil → panel + CTA Conectar, rechazar solicitud, tab Bloqueados + Desbloquear. 11/11 verde con serial mode)
- [x] ~~**Tracking analytics**~~ → **reclasificado Fase 2**: `social.profile_opened + connection_sent + contact_method_clicked` viven con la infra de analytics global (no existe aun en webapp)
- [x] ~~**Cierre commit + memoria + counter PARITY-MATRIX**~~ (hecho 2026-07-04)

### W.9 — Engagement (leaderboard + logros + passport + rewards + golden tickets) — **CERRADO 35/35 (100%) 2026-06-29**

> Arquitectura final 2026-06-29: hub split layout literal espejo W.7. Wall izq apila 6 cards (Hero/Tickets/Premios/Tip/Retos/Pasaporte), panel der detalle del seleccionado. Shapes adaptados al backend real (Expo intacto). Encuestas viven en W.4 Streaming (in-stream context), no se replican aqui. Toast "+X puntos via diff" DESCARTADO (espejo Expo no lo hace).

**Fase 0 — Hooks / fetchers (3/4)**
- [x] `useMyPoints` equivalente — via `fetchDesafioOverview` SSR (overview agrega `/me/points`)
- [x] `useLeaderboard` equivalente — via `fetchRankingClient` (lazy al abrir panel) + top3 en overview
- [x] Lazy fetchers full data — `fetchRankingClient` / `fetchRewardsClient` / `fetchPassportClient` / `redeemRewardClient` via 4 proxies Next `/api/desafio/[eventId]/{leaderboard|rewards|passport|redeem/[rewardId]}`
- [~N/A] `useGamificationConfig` — `actions[]` ya viene embebido en `/me/points` response, no requiere hook separado

**Fase 1 — Encuestas / Surveys (N/A — viven en W.4 Streaming)**
- [~N/A] Encuestas in-stream son del modulo W.4 (`poll:new`/`poll:vote`/`poll:closed` sockets). NO duplicar en W.9 hub.

**Fase 2 — Leaderboard (3/4)**
- [x] LeaderboardTable top 50 — `RankingPanel` con podio escalado #2 #1 #3 + lista top 50
- [x] Sticky bar `my_position + my_points` — HeroCard del wall (siempre visible)
- [x] my_position > 50 — backend devuelve `my_position` separado del top 50, panel muestra siempre "Tu posicion #N"
- [ ] Share rank social — fuera de scope sesion 2, va a backlog futuro

**Fase 3 — Mis Logros / Retos (3/3)**
- [x] Grid actions completed (`RetosCard` wall + `RetosPanel` lista completa)
- [x] Visual estados completed vs pending (`.dx-reto-row.done` / `.pending`)
- [x] Detalle inline (label + puntos + iconos por accion) — no requiere modal separado

**Fase 4 — Passport stamps (3/4)**
- [x] `PasaporteCard` grid 6 + `PasaportePanel` grid completo
- [x] Cada stamp: logo sponsor + nombre + tier + stamped_at
- [x] Solo VIEW (earning via QR fisico mobile, correcto)
- [x] Socket `data:invalidate{entity:passport}` → refresh silencioso — HECHO via W.11 (GlobalSocketProvider mapea `passport` en KNOWN_ENTITIES → router.refresh, sin animacion ni toast, espejo Expo)

**Fase 5 — Rewards (6/6)**
- [x] `RewardsPreviewCard` wall + `RewardsPanel` catalogo completo grid
- [x] Reward card icon + nombre + costo + stock + sponsor
- [x] Redeem optimistic — `RedeemModal` con 2 estados (loading skeleton shimmer → ready con QR real + countdown 5min)
- [x] Display token + countdown + hint "Muestra al vendedor"
- [x] Redemptions INLINE en cada card (espejo Expo) — 5 estados: Mostrar QR TEAL / Ya canjeado disabled+check / Agotado / Canjeando… / Canjear o Faltan X. Reusa token existente sin pegar otra vez al POST `/redeem`. Decision arquitectural: tab "Mis canjes" descartado (Expo NO lo tiene, las redemptions viven en el catalogo)
- [x] Bloque "Canjes activos sin catalogo" — si reward fue retirado pero hay redemption pending vigente, el usuario sigue viendo su QR

**Fase 6 — Golden Ticket reveal (2/2)**
- [x] `GoldenTicketPanel` (panel der): trophy XL + overline "Ganador" + nombre + sponsor + claim_code XL gold + QR grande con RGB rect + hint + countdown si expira
- [x] Estado pending vs claimed — wall card muestra TODOS los tickets (pending boton individual, claimed info estatica), click pending → reveal en panel der mostrando UN solo ticket (sin lista repetida ni modal — espejo desktop informativo)

**Fase 7 — Toast +X puntos diff (N/A descartado)**
- [~N/A] `useTrackPointsDiff` + toast "+X pts" — Expo NO lo hace (visit_stand + trivia answer + acciones suben puntos silenciosamente, usuario descubre al volver al HUD). Memoria `feedback_no_points_diff_toast.md` documenta decision. Webapp = espejo Expo en comportamiento.

**Fase 8 — Tests (2/3) ✓**
- [x] Vitest helpers puros — `desafioDerive.test.ts` (11 tests) + `desafioNormalize.test.ts` (22 tests: 14 shape gaps + 4 redemption normalizer + 4 active/confirmed/orphan helpers). 270/270 vitest verde
- [~N/A] Vitest diff calc — N/A (no implementamos points diff)
- [x] Playwright `desafio.spec.ts` 8/8 verde — auth gate / SSR hub / click ticket pending → reveal / 5 estados CTA inline / Mostrar QR sin POST / Canjear con POST / Esc cierra / bloque orphans. Fixture + 7 handlers mockBackend. Serial mode (evita saturar dev con 8 workers paralelos, 13s vs 30s timeout)

**Fase 9 — Cierre formal (4/4) ✓**
- [x] Validar 3 viewports — E2E automatizado verifica desktop 1600x900 + tablet H 1130x800 + mobile 390x844 (sin overflow horizontal, hub renderiza, panel abre, modal sin overflow). Validacion visual UX se queda al batch QA final cross-modulos (W.5/W.7/W.10/W.9)
- [ ] Lighthouse autenticado — **batch QA final cross-modulos** (no bloqueante para cierre formal, idem W.5/W.7/W.10)
- [x] Memoria — `project_w9_engagement_webapp.md` actualizada con arquitectura final + nuevas memorias (`feedback_no_repetir_info_en_panel.md`, `feedback_no_modal_desktop.md`, `feedback_no_points_diff_toast.md`)
- [x] Counter PARITY-MATRIX sincronizado W.9 0/35→35/35 + W.7 0/23→23/23 + totales (modulos cerrados 2→5, vitest 194 fail→270 verde, E2E 9→11 specs)

**Decisiones arquitecturales W.9 (no preguntar de nuevo):**
- Colores TEAL/GOLD/CYAN fijos, NO `var(--accent)` del cliente (gamification = sistema, no marca cliente). Ver `feedback_no_accent_in_gamification.md`.
- Webapp = espejo Expo en comportamiento. Toast "+X pts via diff" descartado, `claimTicket` attendee-side descartado (vendedor confirma).
- En desktop, panel der NUNCA repite info ya visible en wall. Cada wall card lista sus items, click en item especifico → detalle del item en panel der (no lista repetida, no modal).
- Modal solo cuando NO hay espacio en panel (caso unico: `RedeemModal` post-canje, porque el QR temporal vive fuera del panel — flujo separado del catalogo).
- **Tab "Mis canjes" DESCARTADO** — Expo NO tiene tab separada. Las redemptions viven INLINE en el catalogo: cada card chequea su redemption activa via `findActiveRedemption(rewardId, redemptions)` y cambia el CTA (Mostrar QR / Ya canjeado / Agotado / etc). Si reward fue retirado del catalogo pero hay redemption pending vigente, el bloque "Canjes activos sin catalogo" arriba del grid garantiza acceso al QR.

### W.10 — Live Hub (16/16, **CERRADO por consenso 2026-06-20**)

> Creado en commit `0e185e6` (2026-05-10). Reusa el numero "W.10" que originalmente era Hub Personal. Conflicto resuelto 2026-06-20: Live Hub se queda con W.10 (mas reciente, en commits + tests), Hub Personal renombrado a W.18.

- [x] SSR `fetchHappeningNow` + `fetchUpNext` (lib/live.ts + lib/happeningNow.ts)
- [x] Sidebar pill `/live` activo (ya no disabled)
- [x] LiveHubView root + LiveHero + LiveSideCard + UpcomingCard
- [x] 4 estados visuales: default 2+N, 1 live solo, 0 lives + N upcoming, 0+0 empty
- [x] Click hero/side con has_stream → /session-stream/{id}; sin stream → /agenda?highlight
- [x] Click upcoming → /agenda?highlight={id}
- [x] Header pill "X EN VIVO" indicador
- [x] Tokens Slate Mono globales (--slate, --slate-light, --slate-dark, --slate-deep) en globals.css
- [x] Single radial-gradient elliptical disuelto (sin spots concentrados)
- [x] Lux overrides completos (cards crema + slate-dark sobre claros)
- [x] Vitest `tests/lib/live.test.ts`
- [x] Playwright `e2e/live.spec.ts` (8 escenarios: auth gate, SSR default, upcoming countdown+room+speaker, badge Tu agenda, solo, por arrancar, empty state, navegacion 3 tipos)
- [x] Validacion visual con `LiveHubDemoSeeder` (3 lives + 6 upcoming + 4 past) — funciona OK 2026-06-20
- [x] Counter PARITY-MATRIX + PENDIENTES-WEBAPP actualizado
- [x] **Skip vitest componente LiveHubView** — E2E + JSDoc + visual cubren (anti-overengineering)
- [x] **Skip doc maestro `W.10-live-hub.md`** — anti-regadero, info vive en commit `0e185e6` + JSDoc + esta seccion

### W.11 — Sockets RT (22/22 — CERRADO 2026-07-04 noche)

> Implementado segun `docs/W.11-SOCKETS-PLAN.md` (investigacion + auditorias Fable 5).
> Verificacion viva: pipeline Laravel→socket→cliente confirmado (tinker broadcast →
> cliente recibio `data:invalidate entity=announcements`; contrato roto reprodujo
> `EVENT_ACCESS_DENIED`). 402 vitest + suite E2E.

- [x] socket.io-client instalado
- [x] Auth bearer via socket-token endpoint
- [x] Singleton usado en W.4 Streaming (chat + Q&A + polls)
- [x] Dedup tempId chat propio
- [x] Listeners session:* live indicator
- [x] Pinned message socket-driven
- [x] Optimistic + revert chat
- [x] Connection status pill
- [x] **GlobalSocketProvider** en `(app)/layout.tsx` — singleton global (antes solo streaming)
- [x] **Connection management** — reconnection Infinity, backoff 1s→5s cap (espejo Expo)
- [x] **Reconnect on visibilitychange** (tab dormida re-conecta al volver)
- [x] **Listener wall:post** — bus cliente → SocialView prepend dedup por server ID
- [x] **Listener wall:comment** — +1 count (skip propio, sin double-count Expo) + refetch InlineComments
- [x] **Listener networking:notify** — batch 1500ms espejo Expo + toasts lumina + refetch requests via proxy nuevo `/api/social/requests`
- [x] **Anuncios RT** via `data:invalidate{announcements}` — bell + lista refrescan
- [x] **Listener data:invalidate** generico — 10 entities, debounce 800ms → router.refresh + prop-sync AgendaView/SponsorsView/SoporteView (vistas sordas arregladas)
- [x] **Listener ban:enforced** — logout + toast + redirect /login
- [x] **Listener agenda:delayed** — toast espejo Expo literal
- [x] **Long-polling fallback** — transports websocket+polling en singleton
- [x] **Skip-self** — dedup server-id (wall:post) + author check (wall:comment)
- [x] **Tests Vitest** — 11 nuevos (402/402 total verde)
- [x] **E2E degradacion** — 2 specs (provider monta + 401 no rompe) + regresion suite completa
- Reclasificados fuera del modulo: game events → W.16 (skip webapp) · staff listeners → W.15 · token refresh durante conexion → W.12 (deuda D.5 del plan) · Playwright RT cross-tab con socket real + stress 10K → W.12 · counter PARITY → N/A (doc historico)


### W.13 — FAQ + Documentos (15/15, **CERRADO 100% 2026-07-04**)

> Fase A FAQ entregada 2026-06-29 nocturna (Asistente orb Siri-style + split layout). Fase B Documents entregada 2026-06-30 tarde (/documentos con arquitectura ZIP escalable). Fase C Pages reclasificada formalmente a Fase 2 (decision usuario 2026-06-29).

**Fase 0 — Hooks (2/2)**
- [x] useFaqs (`lib/faq.ts` SSR fetcher)
- [x] useDocuments (`lib/documents.ts` SSR fetcher)
- [x] ~~usePages~~ → **reclasificado Fase 2 (Pages skip)**

**Fase 1 — FAQ (4/4)**
- [x] Accordion + orb Siri-style (browsing/thinking/answering) espejo Expo
- [x] Search debounce 300ms + chips categoria
- [x] Wired a `/soporte?new=true` (CTAs siempre visibles en panel der)
- [x] OrbBlob.tsx CSS puro 4 radial gradients + Lux support

**Fase 2 — Documentos (7/7)**
- [x] Split layout wall + preview panel der
- [x] Icono lucide MIME (FileText/FileImage/FileVideo/FileAudio/File — no emojis)
- [x] Preview embed segun kind (PDF iframe / imagen / video / audio / fallback metadata)
- [x] Skeleton shimmer + fade-in 220ms + timeout fallback 6s
- [x] Descarga individual `<a download>` con `suggestedFilename`
- [x] **Bulk ZIP pre-generado backend escalable a 10K users** (observer + job + endpoint + composer maennchen/zipstream-php)
- [x] URL state `?id=X` + Esc + CSP frame-src

**Fase 3 — Pages (reclasificado)**
- [x] ~~Pages dinamicas iframe/HTML~~ → **reclasificado Fase 2** (usuario 2026-06-29). Modulo webapp usa `/documentos` para archivos y `/faq` para preguntas — Pages dinamicas de organizador es low priority

**Fase 4 — Tests + Cierre (2/2)**
- [x] Playwright — `e2e/faq.spec.ts` + `e2e/documentos.spec.ts` con happy paths completos
- [x] Counter PARITY-MATRIX + memoria (`project_w13_documents.md`)

### W.14 — Anuncios + Cartel Digital + Bell (17/17, **CERRADO 100% 2026-07-04**)

> Fase A Anuncios + BellPopover entregada 2026-06-29 (sprint 2.C). Fase B Cartel Digital entregada 2026-06-30 (ambient signage col der LIVE). Cerrado 100% al reclasificar 3 items bloqueados por otros modulos: RT anuncios (`data:invalidate{announcements}` — `announcement:new` no se emite) → W.11 Sockets, Web Push real → W.12 Polish, dots/counter → decision de diseno (cartelera NO slideshow, sin dots).

**Fase 0 — Hooks (3/3)**
- [x] `fetchAnnouncements` SSR (lib/announcements.ts)
- [x] `fetchBanners` + `fetchHighlights` SSR (Fase B)
- [x] `parseActionUrl` helper 13 mappings eventos:// → rutas webapp (23 vitest)

**Fase 1 — AnnouncementsList (4/4)**
- [x] Lista cards titulo + body + timeAgo (`/anuncios` route)
- [x] Image thumbnail si existe
- [x] Deep link handler `parseActionUrl` — reusado por bell, cartel, cards
- [x] ~~Socket anuncios RT~~ → **reclasificado a W.11 Sockets** (mecanismo real: `data:invalidate{announcements}` — `announcement:new` es dead type, auditoria 2026-07-04)

**Fase 2 — Cartel Digital (6/6) — Fase B 2026-06-30**
- [x] Ambient signage 16:9 col der LIVE state (cross-fade 700ms cada 6s)
- [x] Sin dots, sin flechas — cartelera ≠ slideshow
- [x] Sponsor pill top-left si `sponsor_name`, titulo overlay bottom-left
- [x] Hover/focus pausa el ciclo + `prefers-reduced-motion` support
- [x] Empty → zona colapsa, feed salas ocupa 100%
- [x] Merger round-robin banners+highlights + backend cero cambios

**Fase 3 — BellPopover (3/3)**
- [x] BellPopover radix con badge count unread (per-item read tracking)
- [x] localStorage `lastSeenAt:{eventId}` scoped tracking + cross-tab sync
- [x] Click card → mark seen + nav `/anuncios?id=X`

**Fase 4 — Web Push (reclasificado)**
- [x] ~~Web Push real (8 tipos backend documented)~~ → **reclasificado a W.12 Polish** (infra push cross-modulos)

**Fase 5 — Tests + Cierre (1/1)**
- [x] Vitest 23 (parseActionUrl) + 16 (announcementsUnread) + 11 (cartelItems merger) + 12 (CartelDigital) + 10 E2E anuncios + 6 E2E cartel

### W.15 — Vendor Dashboard — **HECHO via Mobile parity M.8 (35/35, 2026-07-11)**

> El vendor NO se hizo como desktop: se movio a **Mobile parity M.8** (decision Kamilo
> 2026-07-05: el staff del stand abre el webapp en el CELULAR y trabaja). Implementado
> 11/11 en eventos-web `3f7e4dc`, pusheado. Detalle en la seccion **Mobile parity M.8**
> arriba (267-282). Todas las fases de este spec quedaron cubiertas:

- [x] **Fase 0 Hooks**: `lib/stand-client` + `lib/leads-client` + `getVendorAccess`
  (`has_vendor_access` expuesto en /auth/me) + sockets staff + fetch pending-invitations
- [x] **Fase 1 Mi Stand**: hero logo/tier/rol + 3 stat cards navegables + FAB scanner + empty
- [x] **Fase 2 Leads**: grouped Hoy/Ayer/fecha + tier + nota + historial ediciones + Export CSV (navigator.share)
- [x] **Fase 3 Visitantes**: cards lead + acciones tel/mailto (**WhatsApp APLAZADO**, decision Kamilo)
- [x] **Fase 4 Stats**: StatRow + trend vs ayer + TierBar + MemberBar
- [x] **Fase 5 Team**: slots + invitar search/email + share link nativo + transfer/remove (owner-only)
- [x] **Fase 6 Invitaciones**: `/join-team/{token}` auth + accept/reject + modal socket `staff:invited` global
- [x] **Fase 7 Tests**: +8 vitest + E2E mobile-shell (7 vendor). QA vivo device pendiente (scanner HTTPS, share sheet)

### W.16 — Live Moments espejo real (5/5, CERRADO 2026-07-09)

> **CERRADO.** Trivia en vivo en la columna interactiva del streaming. Archivos eventos-web:
> `hooks/streaming/useTrivia.ts`, `lib/trivia.ts` (reducer puro + tipos + helpers),
> `components/app/streaming/TriviaPanel.tsx`, `streaming.css` (seccion `.tv-*`),
> `app/api/streaming/game-answer/[gameId]/route.ts`, wire en `StreamShell.tsx`, toasts en
> `useGlobalSocket.tsx`. Backend seeder `QaTriviaSeeder`. Validado en vivo via Mission
> Control (launch/next-question/close-round) + respuesta desde webapp (score 135 correcto).
> Decisiones Kamilo: noir puro (sin teal), unico color = letras A-F, cero iconos, estado por
> relleno no borde (no "neon"). Fix cascada: `.tv-opt > .tv-opt-dist` especificidad para que
> la barra no empuje la letra. `setState`-en-effect del countdown → derived-state R19 (lint).

> **Auditoria de procedencia:** el scope viejo (23 items) tenia inventos. Lo REAL en Expo:
> TriviaPanel completo (340 lineas, la referencia) + toasts de ruleta/jackpot
> (`useDataInvalidation:436-454`). NO existen en Expo: ceremonia GSAP de sorteo (solo
> toast), golden reveal announcement-driven (solo modal por tap en hub — webapp ya lo
> tiene en /desafio), `display:project` (evento inexistente). Photo contest se movio a
> W.6 Fase 5 (vive en el social, no en live moments). Ceremonias cinematograficas: si
> algun dia se quieren, se disenan para AMBAS plataformas como feature nuevo.

- [x] TriviaPanel espejo (fases idle/question/result/finished: opciones A-F + countdown rojo ≤5s + "+X pts" + distribucion animada + podio top 5 + sponsor badge)
- [x] `useTrivia` hook local + reducer puro (NO zustand — la webapp usa useReducer + sockets como useQnA)
- [x] `POST /events/games/{gameId}/answer` proxy — NO optimista (backend devuelve correct/score)
- [x] Listeners `game:launched`/`game:result` → toasts + router.refresh (puntos/premios)
- [x] 10 vitest (reducer + helpers). E2E trivia → deuda menor

### W.17 — Soporte (13/13, **CERRADO 100% 2026-07-04**)

> Entregado 2026-06-29 nocturna (sprint 2.D). Split layout espejo W.14 + form nueva consulta + subflow del Asistente FAQ. Cerrado 100% al reclasificar 2 items bloqueados por otros modulos.

**Fase 0 — Hooks (2/2)**
- [x] `fetchSupportTickets` SSR (lib/support.ts server-only)
- [x] `createSupportTicketClient` mutation (lib/support-client.ts separado)

**Fase 1 — CreateTicketForm (3/3)**
- [x] Subject input max 200 + counter + validation
- [x] Message textarea max 2000 + counter
- [x] Submit + framer-motion + toast success + haptics enterprise

**Fase 2 — TicketsList (4/4)**
- [x] Cards ordenadas por fecha en wall izq
- [x] Status badge (open/responded/resolved) + AnimatePresence stagger
- [x] Admin response bar sin verde (feedback usuario 2026-06-29)
- [x] "Esperando respuesta" state

**Fase 3 — Real-time (reclasificado)**
- [x] ~~Socket respuesta admin RT~~ → **reclasificado a W.11 Sockets** (mecanismo real: announcement privado → `data:invalidate{announcements}` — `support:new_response` no existe como evento, auditoria 2026-07-04)
- [x] ~~Web Push notif~~ → **reclasificado a W.12 Polish**

**Fase 4 — Backend integration + Tests + Cierre (4/4)**
- [x] Backend: `EditSupportRequest` crea announcement privado `eventos://my-support` cuando admin responde (para que webapp se entere sin push Expo)
- [x] Vitest support-client (403/422/429 mapping)
- [x] Memoria + subflow Asistente (Soporte NO tiene nav item propio — entry via FAQ CTAs)

### W.18 — Hub Personal (19/19, **CERRADO 100% 2026-07-04**)

> Espejo directo `ProfileScreen.tsx` mobile (927 lineas, ~85% Expo). Split 35/65 espejo W.13/W.14/W.17. Ver `memory/project_w18_hub_personal_blueprint.md` para detalle arquitectural completo.

**Fase 0 — Backend integration (3/3)**
- [x] `lib/profile.ts` SSR fetchers (`fetchMyProfile`, `fetchMyPoints`, `fetchMyInterests`)
- [x] `lib/profile-client.ts` mutations con `ProfileClientError` (403/422/429) + normalize shape (`linkedin_url` → `linkedin`)
- [x] API proxies: `/api/profile` PUT, `/api/profile/photo` POST+DELETE (multipart), `/api/interests/[eventId]` PUT

**Fase 1 — Wall + Rows (5/5)**
- [x] Hero: avatar 92px + nombre + `cargo · empresa` + socials (LinkedIn/Twitter/Instagram/Web SVG inline)
- [x] Stats gamification 3 cards SIN iconos (Puntos / Retos x/y / Logros) — condicional a modulos
- [x] Rows clickeables con chevron (Mis datos / Mis intereses / Apariencia)
- [x] Footer: "Ver introduccion de nuevo" + "Cerrar sesion" (rojo)
- [x] Estilo espejo W.13/W.14/W.17

**Fase 2 — Sub-views (5/5)**
- [x] `PerfilDataForm`: 3 cards visuales agrupando (Sobre ti / Contacto / Redes) + 1 solo Guardar
- [x] `PerfilInterestsForm`: chips con contador min 1 + empty state honesto si organizador no configuro opciones
- [x] `PerfilAppearanceForm`: 2 cards Lux/Noir con preview aplicando via `useTheme()` al instante
- [x] `PerfilLogoutModal`: confirm con cross-tab broadcast + redirect
- [x] `PerfilAvatarMenu`: Radix Popover con Subir foto (max 5MB) + Shuffle beam + Eliminar (rojo)

**Fase 3 — Sidebar + deep link + i18n (3/3)**
- [x] Sidebar bottom (Asistente + Perfil + Bell) — refactor coherente. `ProfilePopover` + `UserMenu` eliminados
- [x] Deep link `eventos://profile[/datos|intereses|apariencia]` en parseActionUrl
- [x] i18n 63 keys en es/en/pt

**Fase 4 — Tests + Cierre (3/3)**
- [x] Vitest 14 nuevos (8 profileNormalize + 6 deep link perfil). Total 391/391
- [x] E2E `perfil.spec.ts` 13/13 verde con serial mode
- [x] Memoria `project_w18_hub_personal_blueprint.md` + `project_sidebar_bottom_zone.md`

### W.X — Welcome Showcase — **CERRADO 2026-07-14 (eventos-web `9d02140`, pusheado) — 1/1**

> Feature propia de la webapp (NO espejo Expo): pelicula de bienvenida que explica que es
> cada modulo. Historia: eliminado 2026-07-04 (no espejaba Expo) → re-abierto 2026-07-11
> (decision Kamilo) → cerrado 2026-07-14.
- [x] **Welcome Showcase implementado** — opening keyvisual → invitacion crossfade → FLIP
  al canvas + 6 beats que HABITAN el canvas espejando su modulo real (Agenda: popups
  tip+sesion SINCRONIZADOS a la accion + payoff Mi Agenda / En vivo: player 16:9
  poster+controles + About / Social: hub 3col / Desafio: hub+ranking / Sponsors: por tier
  REAL + detalle / Speakers) + finale nombre completo. Saltar se retira en finale. Copy
  es-CO tuteo. `/api/showcase` sponsors por tier real. Callouts clampean al canvas.
  Favorito bloom suave. Boton "Ver introduccion" del perfil **re-habilitado**. reduced-motion
  fallback estatico. typecheck/lint 0, vitest 7/7. **Falta solo QA final end-to-end.**
  Ver [[project_wx_showcase_design]]

---

## PENDIENTES PARALELOS → movidos a PENDIENTES.md (2026-07-14)

> Documentales, Backend nice-to-have, Analytics tracking y el backlog de Mobile-parity→Expo
> NO son features de webapp Fase 1. Se movieron a `docs/living/PENDIENTES.md` seccion
> "Webapp — paralelos / backlog". Lo que M.8/M.1/M.3/M.4 hicieron (vendor, Mi QR, split
> Social/Networking) queda registrado en la seccion Mobile parity de arriba.

---

## CERRADO RECIENTE (ultimas 5 sesiones)

- **2026-06-27 (Sprint 2.B sesion 1)** — **W.9 Desafio hub inicial 18/35 (51%)**: split layout literal espejo W.7 + 6 cards apiladas espejo DESAFIO Expo (Hero + Golden Tickets + Premios preview + Tip + Retos + Pasaporte) + 6 detail panels (GoldenTicket reveal con QR real qrcode.react + Ranking podio escalado + Premios catalogo + Todos los retos + Pasaporte + Como funciona con 5 reglas educativas + tabla puntos). Avatar component reusable con photo_url + boring-avatars beam fallback (espejo Expo `lib/avatars.ts`). RgbRing + RgbRect con WAVE_COLORS pasteles 6s linear (espejo Expo). Colores **TEAL fijos** (`#39d2c0` + `#B5A68B` GOLD + `#C0C0C0` silver + `#CD7F32` bronze) — **NO `var(--accent)` del cliente** porque Expo no customiza gamification. SSR agrega 5 endpoints con degradacion suave. i18n + sidebar wired. Commit `32018f1` eventos-web pusheado. Pendiente sesion 2: lazy fetch + mutations + tests vitest/E2E + validacion contra backend real.
- **2026-06-21 (Sprint 2.A entero + Sprint 1 cierre)** — **W.7 Sponsors CERRADO 23/23** en una sesion DaVinci larga: wall espejo Expo + framer-motion shuffle (damping 28, stiffness 120) + DetailPanel Hero/Sessions/Trivia (espejo TriviaModal Expo) + ContactForm (chips + 409 ALREADY_CONTACTED) + skeleton SSR + tooltip radix + 14 vitest + **12 E2E Playwright verde** + Lighthouse acc 98 + CLS 0. Cierra **Sprint 2.A entero** y **cruza 50% global** (369/707 = 52.2%).
- **2026-06-21 (Sprint 1 item 8 — W.3)** — **Bulk .ics download**: `lib/ics.ts` generador puro RFC 5545 (VCALENDAR + N VEVENT con UID determinista, DTSTAMP UTC, escape comas/semicolons/backslash). Boton "Todas" del AgendaHeader visible cuando `countMine > 0` en CUALQUIER dia (antes era `visibleSessions.length > 0` del dia activo). Filename `mi-agenda-{event.slug}.ics`. +16 tests vitest.
- **2026-06-21 (Sprint 1 item 9 — W.0)** — **Wire sidebar verificado + cleanup**: smoke 5/5 items (home/agenda/live/speakers/social) navegan sin error. Patron `available: boolean` ya implementado (sponsors disabled con tooltip "proximamente"). Bonus: quitado brand letter `event.name?.charAt(0)` del sidebar (generaba ruido visual tipo debug en eventos sin logo elaborado). **Sprint 1 CERRADO 9/9**.
- **2026-06-21 (BUG-335)** — **Fix theme provider**: next-themes 0.4.6 incompatible Next 16 + React 19 (issues upstream #385/#387 sin fix). Reemplazado con provider propio 60 lineas + script anti-FOUC inline en `<head>` del LocaleLayout server component.
- **2026-06-21 (Backend gaps BUG-336/BUG-337)** — SponsorResource expone trivia/passport/visit_points + GamificationController visitStand devuelve `points_awarded` distinguiendo idempotente (patron a auditar en W.3/W.4/W.6/W.9).
- **2026-06-21 (Polish W.7 — BUG-338)** — 4 fixes agrupados: halo accent rojo eliminado de cards selected (outline accent redundante + se veia mal con primary_color rojo del cliente), elevacion preservada durante shuffle (era el `transition: transform` CSS chocando con framer-motion `layout` — eliminado), heart pop reemplazado por framer-motion (CSS keyframes forzado removido), toast inline → top-center con colores neutrales (no `var(--accent)`).
- **2026-06-20 (Sprint 1, item 7)** — W.6 Tabs filtros feed: `FeedTab` type ("recent" | "top" | "mine") + helper `sortAndFilterFeed` en socialDerive (pure, testeable). FeedView con state local de tab + 3 empty states distintos por contexto. Reusa `.sn-rqx-tabs` global. 6 tests vitest agregados (recent preserva ref, top desc + tie-break created_at, mine filtra is_mine). 202/202 verde.
- **2026-06-20 (Sprint 1, item 6)** — W.8 Skeleton estructurado en AttendeeProfilePanel: 3 secciones placeholder (Sobre con titulo+3 lineas bio, Intereses con 5 chips varying width, Asistira a con 2 session cards). Reusa `.sn-sk-shape` shimmer existente, 5 reglas CSS nuevas. Reemplaza las 3 lineas genericas previas. Sin tests nuevos (visual-only, sin logica). 196/196 verde.
- **2026-06-20 (Sprint 1, item 5)** — W.8 Bloqueados list: tercera tab dentro de SolicitudesView, `fetchBlockedAttendees` SSR en `lib/social.ts`, `handleUnblock` optimistic en SocialView (con revert), `BlockedRow` no clickeable + boton Desbloquear ghost (sin confirm — alineado con Twitter/Instagram). Cierra el gap UX del ConfirmPop ("El bloqueo es reversible" ahora tiene donde verse y deshacerse). 2 tests vitest agregados (196/196 total). Tercer item cerrado del Sprint 1 con codigo (vs 3 admin/cierre formales).
- **2026-06-20 (Sprint 1, item 4)** — W.8 ConfirmPop DaVinci reemplaza `window.confirm` del boton Bloquear. **v1 rechazada:** shadcn AlertDialog generica, visual generico, fuentes default (font-medium con Plus Jakarta cae a sistema). **v2 final:** nuevo `ui/confirm-pop.tsx` + `confirm-pop.css` global espejo del patron `rating-pop`/`attendees-pop` — Plus Jakarta 700 20px titulo + Urbanist 14px desc + drag handle iOS + 440px + shadow doble + boton Bloquear rojo solido. Copy honesto "El bloqueo es reversible" (NO promete vista de bloqueados que no existe). Memoria `feedback_analyze_before_code.md` actualizada con anti-pattern shadcn vs patron del proyecto. 194/194 verde.
- **2026-06-20 (Sprint 1, item 3)** — Renombrado doc W.10 viejo → W.18 Hub Personal via `git mv`. Actualizadas referencias en `_index.md`, `PARITY-MATRIX.md`, `BACKEND-API-MAP.md`, `PLAN.md` (tablas modulos + estimacion). Agregada row nueva para W.10 Live Hub en PLAN.md. Total bloqueante webapp: 139h → 143h (incluye W.10 Live Hub nuevo).
- **2026-06-20 (Sprint 1, item 2)** — W.10 Live Hub cerrado por consenso (16/16, 100%): validacion visual con `LiveHubDemoSeeder` (3 lives + 6 upcoming + 4 past) confirmada por usuario. Skip vitest componente + doc maestro (anti-regadero — info ya vive en commit + JSDoc + E2E). Segundo modulo cerrado en una sesion.
- **2026-06-20 (Sprint 1, item 1)** — W.5 Speakers cierre formal: tests 27/27 verde, memoria actualizada, counters PARITY+PENDIENTES sincronizados. Primer modulo cerrado al maximo posible (94%) desde W.1B. Faltan solo Lighthouse + device real (requieren usuario fisico).
- **2026-06-20 (Sprint 0)** — Re-auditoria + Sprint 0 hygiene: tests vitest 194/194 verde (fix localStorage), backend health OK, 6 rutas smoke clean, screenshot pendiente borrado. Corregido desfase docs: W.5 (0%→94%), W.6 (0%→42%), W.10 Live Hub (75% modulo nuevo), W.10 viejo→W.18 renombrado. Cifra global subio de 40% a 48%.
- **2026-05-21** — Auditoria parity + creacion `docs/webapp/PARITY-MATRIX.md` (4 fases agentes paralelo, 117/117 endpoints OK)
- **2026-05-17/18** — W.8 perfil attendee in-slot visionOS + feed editorial + 49 tests vitest (194 total)
- **2026-05-15/16** — W.8 avatar beam fallback espejo Expo (commit 332b2ef)
- **2026-05-13/14** — W.8 Social Networking modulo base (feed + directorio + solicitudes + mis posts)
- **2026-05-08** — W.5 Speakers demo HTML aprobado (mirror Expo, sin tracks/featured flags)
- **2026-05-07** — Backend audit completo 197 endpoints, BACKEND-API-MAP.md
- **2026-05-06** — W.3 Agenda implementado React + W.2 Home recap base
- **2026-05-05** — W.0 Spatial UI fix tablet canvas
- **2026-05-04** — W.0 Spatial Shell implementado React validado
- **2026-05-02** — W.1 + W.1B cerrados (auth magic link + slideshow + UI foundation + tests)

---


### Partes cerradas de bloques que siguen abiertos (BLOQUE 5 y W.12)

- [x] **Web Push real CERRADO** (`b9aa4df` backend + `2dc43a3` web): VAPID + tabla push_subscriptions + endpoints + `SendWebPushJob` + transporte multi-canal `toAttendee()` en 13 call-sites + filtros web-only + SW con PUSH_ROUTES espejo Expo + soft prompt pill (divergencia web aprobada) + track push_open + recordatorios de sesion incluidos. **Fix aprobado: scheduled → Announcement** (la push es el golpe, el announcement es la carta). Verificado VIVO: Chrome real + FCM + click routing + Bell live. Bug pre-existente cazado: ban Filament limpiaba token antes de la push
- [x] **PWA CERRADO** (`2dc43a3`): manifest + iconos (anillo dorado noir) + install prompt via useInstallPrompt en /perfil SOLO >=1024px + offline.html estatico + SW
- [x] **CSP completo CERRADO** (`471cf94`): 13 directivas, connect-src backend+socket desde env, dev relajado por NODE_ENV. Verificado vivo + suite E2E bajo la politica
- [x] ~~`loading.tsx` en 7 rutas sin cubrir~~ — **HECHO 2026-07-05** (`29fce3d`): soporte, faq (shape del orb), anuncios, desafio (cards heterogeneas), documentos, session-stream (tokens --st-*), perfil. 13/13 rutas con skeleton que calca el layout real
- [x] **EXTRA 2026-07-05** (`29fce3d`): token global `--heart: #ff5d6c` + barrido haptics 13 modulos. **No-optimistas (agenda rating / soporte ticket / forms perfil): SKIP FORMAL 2026-07-05** — optimista es para toggles; forms con validacion 422 o que necesitan id del server DEBEN esperar (decision Kamilo)
- [x] **Code splitting CERRADO** (`471cf94`): dynamic() ssr:false en 7 componentes post-interaccion (AttendeeProfilePanel, PhotoViewer, MomentosViewer, CropUploadModal, RedeemModal + GoldenTicketPanel con qrcode.react, DocumentPreview + FileKindIcon extraido). Lazy framer-motion global NO (33 archivos, riesgo > ganancia)
- [x] **Print agenda CERRADO** (`471cf94`): documento imprimible (papel blanco, sin chrome, break-inside avoid), verificado visual 2 iteraciones
- [x] **SEO recortado con higiene** (decision Kamilo 2026-07-05): robots.ts noindex total + title por ruta 13 paginas. OG images + sitemap → **Fase 2 formal** (app auth-gated)
- [x] Skeleton consistente todos los modulos — **HECHO** (13/13 rutas desktop + skeletons mobile por modulo que calcan el layout real)
- [x] ~~Focus visible :focus-visible outline accent~~ — YA EXISTE (verificado 2026-07-04: regla global `globals.css:493` con color-mix + offset 2px)
- [x] Code splitting por modulo — **HECHO 2026-07-05** (`471cf94`): dynamic() ssr:false en 7 componentes post-interaccion; FileKindIcon extraido para split real de documentos
- [x] ~~Lazy @dnd-kit + framer-motion~~ — @dnd-kit NO se usa (verificado); lazy framer global descartado (33 archivos estaticos, riesgo > ganancia; qrcode.react SI salio del bundle /desafio via splitting)
- [x] Meta tags por pagina — **HECHO** (`471cf94`): title template `%s — EventOS` + generateMetadata en 13 rutas via lib/pageMetadata (nav.* i18n)
- [x] ~~OG images~~ → **Fase 2 formal** (app auth-gated, nadie comparte rutas privadas)
- [x] ~~sitemap.xml~~ → **Fase 2 formal**; en su lugar robots.ts noindex TOTAL (antes las rutas privadas de eventos eran indexables)
- [x] Manifest — src/app/manifest.ts + iconos anillo dorado #B5A68B sobre noir (sin tipografia, generados con Playwright)
- [x] Service Worker — public/sw.js (push + click routing + offline; cache eventos-sw-v1)
- [x] Install prompt condicional desktop/tablet — useInstallPrompt (beforeinstallprompt) + entry "Instalar aplicacion" en /perfil footer
- [x] Install prompt NO en mobile — gate matchMedia >=1024px (no canibalizar app nativa)
- [x] Offline fallback page — public/offline.html estatico Lumina Noir (sin red, sin middleware, cero superficie XSS)
- [x] Stylesheet print friendly — globals (canvas liberado, pills/ambient print:hidden) + agenda.css @media print
- [x] Imprimir agenda — documento real: papel blanco/tinta negra, sin chrome, sesiones break-inside avoid, verificado visual 2 iteraciones (ratings viven en el detail panel = fuera del papel a proposito)
- [x] ~~Smoke test critical paths~~ — YA EXISTE (verificado 2026-07-04: **20 specs** en e2e/ cubriendo todos los modulos: auth-gate, login, verify, home, agenda 16 tests, streaming, speakers, social, sponsors, desafio, live, global-socket, faq, documentos, cartel, anuncios, soporte, perfil)
- [x] ~~Login + home + agenda~~ — YA EXISTE (auth-gate.spec + login-form.spec + home.spec + agenda.spec)
- [x] ~~Source maps subidos en build (no en cliente)~~ — YA CONFIGURADO (verificado 2026-07-04: `withSentryConfig` con `deleteSourcemapsAfterUpload: true` + tunnelRoute /monitoring + release via GIT_SHA. Sentry client/server/edge completo con PII scrub)
- [x] CSP estricto — **HECHO 2026-07-05** (`471cf94`): 13 directivas (script/style/img/font/connect/media/frame/worker/object/base-uri/form-action/frame-ancestors), connect-src backend+socket desde env, dev relajado por NODE_ENV, verificado vivo + suite E2E bajo la politica
- [x] ~~X-Frame-Options~~ — YA EXISTE (verificado 2026-07-04: SAMEORIGIN + nosniff + Referrer-Policy + HSTS en `next.config.ts:76-84`)
- [x] ~~Reduced motion verificado~~ — YA EXISTE (verificado 2026-07-04: media query global `globals.css:470` + hook `useReducedMotionPref` + ~18 componentes/CSS)
- [x] reduced-motion serie estatica W.X — **HECHO 2026-07-14** (Showcase: variante estatica sin pelicula via useReducedMotionPref)
- [x] Backend: minishlink/web-push + VAPID keys + tabla push_subscriptions (endpoint hash unico, multi-device) + WebPushSubscriptionController (GET key + POST/DELETE) espejo expo-token
- [x] Transporte multi-canal: `SendPushToAttendeeJob::toAttendee()` choke point (Expo + web), 13 call-sites migrados, filtros de jobs masivos incluyen web-only, subscription por valor (simetria token Expo, prune 410). Recordatorios de sesion (SendAgendaRemindersJob) y TODOS los triggers incluidos. Fix bug pre-existente: ban Filament limpiaba token ANTES de la push
- [x] **Scheduled → Announcement persistente** (decision Kamilo: "la push es el golpe en la puerta, el announcement es la carta") — notificaciones programadas ya no se pierden si nadie las vio; Bell enciende live via AnnouncementObserver
- [x] SW cliente: push handler con supresion si pestana enfocada (Bell+socket cubren in-app), notificationclick con PUSH_ROUTES espejo LITERAL Expo useNotifications.ts, track push_open, pushsubscriptionchange re-subscribe
- [x] Soft prompt pill una-vez-por-evento (divergencia web aprobada vs auto-prompt Expo: Chrome penaliza el prompt automatico y un bloqueo es permanente) + resync silencioso con permiso granted. 15 Pest + 14 vitest + 3 E2E

### Texto original movido de PENDIENTES.md

### Recap — plan original (casillas que nunca reflejaron el estado real)
> Plan completo: `docs/ROADMAP-RECAP.md` (DaVinci mode, con tests + refs).
> Solo app movil en Fase 1. Webapp consume mismo backend en Fase 2 (post Web App Bancolombia).

- [ ] Fase 0: Investigacion + refs visuales (~2h) — 0/8
- [ ] Fase 1: Backend datos (RecapService + API + tests) (~4h) — 0/12
- [ ] Fase 2: Backend imagen Browsershot + R2 (~4h) — 0/10
- [ ] Fase 3: Backend email + URL firmada + trigger (~4h) — 0/13
- [ ] Fase 4: App pantalla recap nativa (~7h) — 0/15
- [ ] Fase 5: App distribucion (push, deeplink, banner, lista) (~2.5h) — 0/8
- [ ] Fase 6: QA + edge cases + tests E2E (~3h) — 0/9

- [x] DEPLOY DEMO 6/6 — HECHO 2026-08-01/02 y luego destruido (ver ROADMAP-INFRAESTRUCTURA). Texto original: (hosting, backend prod, Next prod, socket PM2, evento
      demo curado, Sentry DSN) — **prioridad estrategica post-pivote**

## 3. Landing Web (registro publico)

> **ACTIVO desde 2026-09-30 (decision Kamilo): es la feature que sigue, antes del servidor.**
> Landing personalizada por cliente + summit demo de EventOS como vitrina + widget embebible
> (enlace, integrado, boton con ventana, boton flotante). Target: CEOs y ejecutivos, elegante sin
> cliche. Registro en forma de frase, sin contraseña (enlace magico). Base visual: propuesta A
> (boleto) + color de marca en patrocinadores. El widget NO espeja el onboarding de Expo.
> **Linea visual aprobada 2026-10-01: `design/system/LINEA-VITRINA.md`** (hero oscuro = Paisaje de
> datos, claro = Fachada cinetica; lab `design/features/landing/lab-hero-elegidos.html`).
> Labs: `design/features/landing/` (v1, v2, direcciones de hero, fondos three.js). Estado del dia y
> descartes: `docs/NEXT-SESSION.md` sesion 2026-09-30.
> **Hallazgos backend (antes de abrir al publico):** `POST /auth/register` sin throttle; sin CAPTCHA
> (propuesto Turnstile); `max_attendees` sin lock (carrera en el ultimo cupo; **RESUELTO 2026-10-12** backend 6959bd0, tambien el ultimo uso de un codigo); `SecurityHeaders`
> pone `X-Frame-Options: DENY` global (bloquea el iframe); `allowed_embed_domains` hoy valida
> streams salientes, hace falta un campo propio para dominios que embeben; registro exige
> contraseña (hace falta registro sin contraseña + enlace magico para cuentas nuevas).
> Campos nuevos que pide el diseño: frase del speaker, "que trae" el patrocinador.

> Ultimo en orden porque el registro puede hacerse por CSV/import hasta tener landing.
> Ref: `docs/ROADMAP-UIUX-LANDING.md`

### Secciones

- [ ] Hero, Sobre el evento, Speakers, Agenda, Sponsors, Venue, Testimonios, Galeria, FAQ, Footer

### Registro embebido

- [ ] Form integrado, progressive profiling, social proof, CAPTCHA, rate limiting

### Widget de registro embebible (decision Kamilo 2026-07-18)

> Registro insertable en CUALQUIER sitio de terceros (pagina del organizador,
> intranet corporativa), no solo en nuestra landing.
> **Prior art interno**: patron `/dc-embed/{token}` del Data Center (embed con
> token + tabla dc_embed_tokens) y campo `allowed_embed_domains` que YA existe
> en el modelo Event — la arquitectura esta semi-prevista.

- [x] Script/iframe embebible: `widget.js` (data-evento, data-tema) -> iframe `/[locale]/e/[slug]/widget`; al registrarse va a nuestra confirmacion con pase de un solo uso (2026-10-11)
- [x] Validacion de dominio contra allowed_embed_domains (frame-ancestors en proxy.ts) + rate limit
- [x] CAPTCHA (Turnstile) conectado en registro publico + widget (2026-10-12): invisible, solo aparece si Cloudflare duda;
  si Cloudflare no responde el registro sigue (queda en el log `turnstile.unreachable`). Probado de punta a punta.
- [ ] **ANTES DE ABRIR EL REGISTRO: cambiar las llaves de PRUEBA de Turnstile por las reales** (Cloudflare > Turnstile).
  Hoy van las de prueba, que aprueban a cualquiera. Backend `.env`: `TURNSTILE_SECRET_KEY`; eventos-web `.env.local`
  (y el del servidor): `NEXT_PUBLIC_TURNSTILE_SITE_KEY`. Agregar el dominio real de la landing en el panel de Turnstile.
- [x] Tema del widget: color del evento, Noir/Lux del evento o forzado con data-tema
- [x] Config en admin: Entrada > Landing y widget (dominios, tema, codigo, vista previa) — falta que Kamilo la revise
- [ ] **Toggle de tema para el demo** (idea Kamilo 2026-10-16): pildora Noir/Lux flotante que cambia `?tema=` en la misma URL,
  visible SOLO con `?demo` (una landing real no deja al asistente cambiar la plantilla del admin); el parametro viaja a la
  confirmacion y al widget. Minilab A (pildora abajo a la derecha) / B (barra fina arriba, con color del cliente como el lab v3)
  antes de codear. Sin backend. Hoy el tema se fuerza con `?tema=lux|noir` (`src/lib/landingEvent.ts:10`).

### Post-registro (pagina: `design/features/landing/confirmacion.html`; labs `lab-confirmacion-estados.html`, `lab-cinta.html`)

> Decisiones Kamilo 2026-10-04: la confirmacion es una **pagina propia** (no el panel del registro), con poco
> texto y **celebracion** (ola del color del cliente en la escena del hero); **escarapela colgante** con animacion
> Sutil: un lado nombre grande + cargo + empresa, el otro el **QR real** igual al de Expo/webapp. Noir = escarapela
> oscura, Lux = crema, cinta oscura en las dos, cinta que pasa por la ranura (sin metal). Apple Wallet = nice to have.

- [x] Frase del registro con cargo y empresa (opcionales): "Hola, me llamo ___ trabajo como ___ en ___ y mi correo es ___" (v3)
- [x] Lab de la pagina de confirmacion (Noir/Lux, celular, movimiento reducido) y paso desde el registro de la v3
- [x] Revision de Kamilo en iPad (bien) y celular: escarapela gigante -> se achica sola para caber en una pantalla (2026-10-11)
- [x] Version fija `confirmacion.html` (Noir oscura / Lux crema, cinta por la ranura, Sutil); el registro de la v3 lleva ahi
- [x] Sin datos inventados: fuera "Pase general" y "0348" (el backend no tiene tipo de entrada ni numero); el nombre no se repite atras del QR
- [x] Cinta blanda: puntas quietas, holgura y retraso en el balanceo; la lengueta gira entera con la tarjeta (torcida en punta descartada: "se ve rara")
- [x] Backend 87e519e: `POST /api/v1/public/events/{slug}/register` sin contraseña -> registered (sesion: la confirmacion pide `/me/qr`) / pending / existing (reenvia el acceso). Cargo y empresa ya se guardaban en el usuario.
- [x] Confirmacion con los 3 estados (registered / existing "ya tenias tu lugar" / pending "en revision"); sin sesion no hay QR ni giro
- [x] Correo completo (sin censura) + "¿No es tu correo? Corrígelo": con sesion `PATCH /api/v1/me/email` (cuenta < 24 h, sin entrar, sin verificar); sin sesion vuelve al registro con lo escrito
- [x] Registro: "¿Quisiste decir gmail.com?" (dominios comunes, distancia <= 2)
- [x] Rayas del fondo en la confirmacion (mismo error del hero): mask del oscurecido solo sobre los cubos
- [ ] Credencial con etiqueta visible (VIP, Prensa): en el admin marcar que tags salen en la escarapela (idea Kamilo 2026-10-11, despues)
- [ ] "Ya tenias tu lugar" con varios eventos: hoy sale tambien si el correo tiene cuenta en OTRO evento y no en este
- [x] Pasar la confirmacion y el registro al stack real: webapp `/[locale]/e/[slug]` y `/confirmacion` contra el backend (2026-10-11)
- [x] Registro por pasos de tamaño fijo (lab-registro-pasos A) y secciones del v3 con datos reales en la webapp
- [x] Speakers 6 columnas + aparicion fila por fila con tono parejo (lab-speakers-tamano C)

### Endpoints publicos

- [x] GET /api/v1/public/events/{slug}/landing (speakers, agenda, tracks, sponsors, faqs) 5711d44
- [x] POST /api/v1/public/events/{slug}/register (limite por correo y red) 87e519e
- [x] CAPTCHA en el registro publico (con el widget) — Turnstile 2026-10-12, llaves de prueba (ver arriba)

### Stack

- [x] Stack: dentro de la webapp Next (decision 2026-10-11), Noir/Lux por evento
- [x] SEO/OG de la landing publica (2026-10-12): titulo y descripcion del evento (sin "EventOS"), canonical, tarjeta al
  compartir con la imagen 16:9 "Card del Home", datos estructurados schema.org/Event; confirmacion y widget fuera de Google
- [ ] Tarjeta al compartir cuando el evento NO tiene "Card del Home": hoy sale sin imagen. Generarla (nombre, fecha, lugar,
  color del evento) necesita lab antes
- [ ] En produccion, probar la tarjeta en WhatsApp, LinkedIn y X con la URL real (en local la imagen no es publica)


- [ ] Preview landing en tiempo real (depende de que exista la LANDING, secc. 3)  → HECHO: vista previa en vivo en Entrada > Landing y widget (2026-10-11)

## 5. Seguridad del staff — **EN COLA, DESPUES DEL DEPLOY** (decision Kamilo 2026-08-01)

> "Prefiero tener todo lo de seguridad en regla y no esperar a tener cliente
> encima con presion" (2026-07-20) — sigue vigente, cambia el ORDEN.
>
> **Movido a post-deploy el 2026-08-01.** Razones: hoy no hay puerta que proteger
> (el admin corre local y el staff es Kamilo solo) y las passkeys exigen HTTPS,
> que el deploy regala — hacerlo antes obligaba a montar SSL en Laragon y cambiar
> `APP_URL`, rompiendo las imagenes de correos, todo trabajo desechable.
>
> **RAYA ESCRITA (el riesgo de mover esto es que "despues" se vuelva "nunca"):
> el admin no se le muestra a ningun prospecto ni recibe datos reales de nadie
> hasta que el 2FA este puesto.** Deploy → verificar → 2FA → recien ahi sale la URL.
>
> **Ampliacion de alcance 2026-08-01**: ademas de TOTP entran **passkeys**
> (huella/Face ID). Verificado: `laravel/passkeys` funciona standalone, NO
> requiere Fortify. La base sigue siendo `pragmarx/google2fa` a mano — es la
> misma libreria que usa Laravel Fortify oficialmente (0 advisories, 104M
> instalaciones). Passkeys entra como bloque S.9 (roadmap 0/26 → ~0/31).
>
> **Ventana operativa: `docs/roadmaps/ROADMAP-SEGURIDAD-STAFF.md` (0/26)**
> — 2FA con app autenticadora (TOTP) obligatorio para todo el staff del admin
> + recuperacion + dispositivos de confianza + sesiones activas + registro de
> accesos. ~2-3 sesiones.
>
> **Desbloqueo**: SEC-3.1 llevaba aplazado desde abril por depender de
> WhatsApp Business API; TOTP elimina esa dependencia. El diseño SEC-3.1/3.2
> de `infra/FASE-SEGURIDAD.md` queda SUPERSEDED por el roadmap nuevo.
> Ya hecho y NO se rehace: lockout, socket rate limiting, FormRequests (~90%).

## 7. Stress test — **EJECUTADO 2026-08-01** → `docs/infra/INFORME-STRESS-2026-08-01.md`

> **RESULTADO: ~70 req/s · ~1.000 sockets simultaneos · cuello de botella CPU**
> (0% libre con 6,1 GB de RAM sin tocar). Traduccion comercial: **~1.000
> personas con la app abierta a la vez; un evento de 1.000-2.000 inscritos
> entra comodo en un droplet de $48/mes**. Para crecer se suman NUCLEOS, no
> memoria.
>
> Los dos bloqueantes D.0 quedaron resueltos y commiteados. Salieron 3 bugs de
> producto (leaderboard/MySQL 8, backend sin instalar con `--no-dev`,
> HTMLPurifier) y 4 defectos del arnes de medicion. +25% de capacidad gratis
> con OPcache/JIT y cachés de Laravel.
>
> **PENDIENTE INMEDIATO**: los droplets siguen ENCENDIDOS y facturando
> (`eventos-target` 134.209.116.227 · `eventos-load` 104.248.53.253, ~$0.14/h
> entre los dos). Destruirlos es decision de Kamilo tras leer el informe.
> Tambien: **rotar las credenciales de R2** (quedaron escritas en el chat de
> la sesion; alcance acotado a objetos de un bucket, pero R2 no se destruye).

- [x] **RESUELTO (verificado 2026-09-13: ficha compartida en Redis, `eventos-socket/src/auth.ts`; `/auth/me` solo si no hay ficha).** Mitigar el hallazgo de arquitectura: **cada conexion de socket cuesta
      una peticion HTTP** a `/auth/me` (verificado: 1.468 conexiones = 1.468
      peticiones, pico de 76/s = justo el techo de la maquina). Una
      reconexion masiva se come sola toda la capacidad HTTP. Mitigacion:
      que el socket valide el token sin pegarle al backend en cada handshake.

- [x] **RESUELTO (verificado 2026-09-13: `instances: 'max'`, `cluster`, `max_memory_restart: '2G'`).** **`eventos-socket/ecosystem.config.js`**: `instances: 1` +
      `exec_mode: 'fork'` + `max_memory_restart: '256M'`. Bajo carga el
      servidor se reinicia a mitad del test → se mide una caida, no una
      capacidad. Subir el techo + cluster mode (el adaptador Redis ya esta
      implementado, es seguro).

- [x] **HECHO (verificado 2026-09-13: unique game_id+attendee_id+round e index game_id+round+score).** Indices `live_game_participants`

- [x] ~~**Enforcement de modulos en el Expo**~~ **HECHO 2026-08-01**: `ModuleMenu.tsx`
      filtra contra `useModules()` (el endpoint ya devuelve solo los encendidos),
      fail-open sin datos, grid entero oculto si no queda ninguno (decision Kamilo:
      el Home conserva HappeningNow + HUD + campana). NO se agregaron entradas
      nuevas al grid — documentos/paginas/recap se alcanzan por sus caminos reales
      (deeplinks, HUD, Ayuda del perfil), coherente con "no inventar entradas de
      menu que Expo no tiene". Ademas `ENTITY_KEYS` gana `modules`, que era lo que
      dejaba al Expo sordo al broadcast del panel. Falta QA vivo en device.

---


## Sueltos del Expo + BUG-LOG a cero — 2026-08-01

Sesion de barrido antes del deploy. Expo `main` (commit selectivo — el WIP del recap
sigue intacto en el working tree) · backend `feature/magic-link-auth` · socket `main`.

- **Enforcement de modulos en el Expo (cierra la paridad F10 en la 3a superficie)**.
  `ModuleMenu.tsx` tenia los 4 modulos clavados a mano e ignoraba `useModules()`.
  Ahora filtra contra el endpoint (que ya devuelve **solo los encendidos**), espejo
  literal del rail de la webapp (`SidebarPill.tsx:103-104`). **Fail-open** sin datos:
  un backend caido no puede dejar al asistente sin navegacion. Si no queda ninguno el
  grid no se dibuja (decision Kamilo: el Home conserva HappeningNow + HUD + campana;
  un cartel de "no hay modulos" seria ruido sobre una decision deliberada). Los de rol
  (`staff_checkin`, `admin`) no son del catalogo y no se filtran. **No se agregaron
  entradas nuevas** — documentos/paginas/recap siguen llegando por deeplinks, HUD y
  Ayuda del perfil, coherente con "no inventar entradas de menu que Expo no tiene".
- **`ENTITY_KEYS` gana `modules`** (`useDataInvalidation.ts`). El backend emitia
  `modules` (`ModuleObserver` + `ModulosPanel.php:162`) y el Expo lo descartaba en el
  `return` del listener — era el unico cliente sordo. Una linea reconecta una cadena
  ya probada en la webapp.
- **Double-count del comentario propio (`useWall`)**. `addComment` sumaba +1 optimista
  y el listener `wall:comment` volvia a sumar al rebotar el broadcast. **No se podia
  arreglar solo en el Expo**: el payload no decia quien comento, y deduplicar por id
  llegaba tarde porque el backend emite el socket ANTES de responder el POST
  (`WallController.php:208` vs `:210`). Fix en 3 repos, 4 lineas: el broadcast manda
  `attendee_id`, el tipo del socket lo refleja, y el Expo hace **skip-self** — el
  mismo patron que ya usaba `useDataInvalidation.ts:276`.
- **BUG-LOG a 0 pendientes de 305.** BUG-127 (metricas MC) y BUG-111 (PollSlides)
  estaban resueltos en codigo desde hacia tiempo y nadie los habia marcado; se
  cerraron con la evidencia archivo:linea en cada entrada.
- Hallazgo anotado en PENDIENTES: `ModuleMenuCompact.tsx` esta importado pero nunca
  renderizado (componente huerfano).

---

## Saneamiento facil→dificil 2026-07-20 (post-pivote, foco features/bugs)

Barrido de pendientes chicos + una feature nueva. Backend `feature/magic-link-auth`
(`bd01c66` `746d4be` `7849212` `894c3a0`) · webapp main `cb162cb` · Expo main
`b285202` (selectivo) — TODO PUSHEADO.

- **Fix `SessionLifecycleTest` (2 tests hermanos)** — no eran flaky, fallaban 5/5.
  Montaban el delay con `end_datetime`+`adjusted_end_at` en el mismo `update()` y
  `EventSessionObserver` borra el override → la reversion nunca se ejercitaba.
  Fix: escenario en dos updates + `travelTo(now())` + asercion exacta. Produccion
  intacta (el "usar assertLessThanOrEqual" del doc habria enmascarado 2 tests
  muertos). 23/23 verde. Gotcha en [[project_backend_notes]].
- **`/scanner-stand` webapp** — verificado: NO era hueco. Camino real Home vendedor
  → `/mi-stand` → FAB scanner → `/scanner-stand`.
- **LeadResource huerfano (Filament) DEMOLIDO** — nav oculto, sin accion de borrar.
  Leads NO se pueden borrar en ningun lado (ni API ni admin — lo mas seguro para
  data comercial), se ven en el vendor y se exportan en el DC (`leads_master` +
  `lead_edits`). El LeadResource de la API se conserva.
- **Encuestas por anuncio + canon 'aviso'** — el compositor de anuncios gana destino
  "Encuestas" (`eventos://encuestas`) con **targeting por encuesta especifica**
  (selector "¿Cual encuesta?" → `eventos://encuestas?id={id}`; webapp y Expo
  preseleccionan, degradan a la lista si el id no existe). Canon ModuleCatalog:
  encuestas pasa de rail/grid a `aviso` (se surfacea por aviso/push/Home finalizado,
  no nav fija). Verificado end-to-end en Chrome. Detalle + gotcha `?id=` vs `/id`
  en [[project_anuncios_deeplink_encuestas]].
- **`moments.js` v2 (Event Pulse) VERIFICADO en vivo** — sin bug; cache-bust `?v=22`
  funciona, cascada renderiza (los screenshots vacios eran los huecos de 15s).
  Cero codigo. Event Pulse 0/2 → 0/1.
- **Paginas F10.6** — DEFERIDO post-deploy (decision Kamilo: no aporta ni detiene,
  no es dependencia). Sigue EN PAUSA en el admin.

---

## Frentes grandes cerrados mayo-julio 2026 (movidos de PENDIENTES 2026-07-19)

> Resumen ejecutivo; el detalle vive en los roadmaps historicos y en
> `PENDIENTES-WEBAPP.md` (backlog granular con todos los checkboxes).

| Frente | Cierre | Detalle |
|--------|--------|---------|
| **Webapp Fase 1 COMPLETA** (la seccion "~132h 0/18" de PENDIENTES) | 2026-07-14 | 19 modulos cerrados: W.0 shell, W.1 auth magic link, W.2 home, W.3 agenda, W.4 streaming, W.5 speakers, W.6 social+momentos, W.7 sponsors, W.8 networking, W.9 desafio, W.10 live hub, W.11 sockets RT, W.13 documentos, W.14 anuncios/cartel, W.16 trivia, W.17 soporte, W.18 hub personal, W.X welcome showcase. 550/576 items (95.5%); lo restante es QA presencial device + deploy. Ventana: `PENDIENTES-WEBAPP.md` |
| **Mobile parity webapp 60/60** | 2026-07-11 | M.0-M.8 completos incl. Vendor 11/11 + gates banned/pending. Falta solo QA vivo en device fisico |
| **W.12 Web Push + PWA (Fases A+B)** | 2026-07-05 | Push multi-canal (toAttendee choke point) + PWA + CSP 13 directivas + splitting. Falta Fase C QA |
| **Magic link auth end-to-end** (item de seccion Seguridad) | mayo-julio | Backend branch `feature/magic-link-auth` + webapp W.1: token un solo uso, verify, refresh. Auth principal de la webapp en produccion de features |
| **LUMINA ADMIN F0-F12 COMPLETO** (reemplaza "Admin Filament polish" secc. 4) | 2026-07-19 | EventContext + scoping 44 resources (mata Event Switcher pendiente) · tema Noir/Lux White Chrome · arbol features nav 45→18 · interiores INT.1-13 (Agenda, Branding por superficie, Entrada, Asistentes, Sponsors, Social, Gamificacion, Anuncios, Emails 16 tipos + fork, En vivo, Encuestas lista+builder, Salones+kioskos QR, standalone) · Escritorio · Panel Modulos con enforcement webapp RT verificado · Wizard v2 4 pasos · validacion es · Roles canon + Staff digno · decision UN DROPLET POR EVENTO. Roadmap historico: `ROADMAP-LUMINA-ADMIN.md` (FILAMENT-PULIDO superseded) |
| **Socket singleton Expo** | 2026-07-04 | `0d9a754`: 6 conexiones paralelas → 1 singleton `lib/socket.ts`, 6 consumidores migrados + fixes `0ca10b6`/`460dca8` |
| **Knowledge base ARRANCADO (M0 fundacion)** | 2026-07-19 | Inventario 27 paginas blindado con barrido 4 superficies + sitio Starlight compilando (`manual/`) + piloto Encuestas. Ventana: `ROADMAP-MANUAL.md` |

---

## Webapp (Next.js) — fase activa

| Sesion | Feature | Fecha | Notas |
|--------|---------|-------|-------|
| W.0 | Spatial Shell + CanvasCard + SidebarPill + ThemeToggle | 2026-05-04 | Fix tablet H 2026-05-05 (canvas sin aspect-ratio rigido) |
| W.2 | Home cinematic con 3 estados (PreState/LiveState/EndedState) | 2026-05-04 | Wordmark accent, mute por estado |
| W.3 | **Agenda completa + wired backend** | **2026-05-06** | DayStrip + ChipFilters + SessionCard + DetailPanel + RatingModal + AttendeesPop. POST favoritos optimistic + revert, .ics download, rating + my-ratings, Asistencia oculta hasta W.8. Doc maestro `BACKEND-API-MAP.md` (805 lineas, todos los endpoints + decisiones cerradas). |
| W.4 | Streaming player + Q&A + chat + polls + layout spatial | 2026-05-09 | StreamPlayer multiformat, hooks useChat/useQA/useSessionDetail, socket dedup tempId, pinned message socket-driven. Trivia delegada a W.16. |
| W.5 | **Speakers modulo + agenda highlight extension** | 2026-05-09 → cerrado 2026-06-20 | 8 componentes (SpeakersView + BreathingCarousel + FeaturedCard + SpeakerListItem + SpeakerDetailPanel + SpeakersRatingModal + SpeakersHeader + Stars). Race protection panel swap. Deep link `?id=X`. Click sesion → /agenda?highlight. UNIQUE 409 silencioso. 27 vitest + 13 Playwright. **CERRADO 94%** 2026-06-20 (faltan Lighthouse + device fisico). |
| W.6 | **Social Wall — feed + composer + likes + comments + tabs filtros** | 2026-05-13 → 2026-06-20 | Feed paginated SSR + Composer max 500 + foto upload + heart optimistic + comments inline + MisPostsView filter is_mine. **Tabs filtros (Recientes/Mas likes/Mis posts)** agregadas 2026-06-20 con helper pure `sortAndFilterFeed` + tie-break created_at. 3 empty states por contexto. 6 tests nuevos. **45%** (faltan Stories + Photo Contest + Hashtags). |
| W.8 | **Social Networking — perfil in-slot + ConfirmPop + Bloqueados + skeleton** | 2026-05-13 → 2026-06-20 | Directorio paginado + AttendeeCard + perfil in-slot visionOS (panel der MUTA, 4 estados relacion) + compose inline + vCard/WhatsApp/Email + Solicitudes Recibidas/Enviadas + Aceptar/Ignorar/Bloquear. **2026-06-20:** ConfirmPop DaVinci reemplaza `window.confirm` (componente reusable patron `rating-pop`, NO shadcn AlertDialog), Bloqueados list (tercera tab en Solicitudes con BlockedRow + handler optimistic), skeleton v2 honesto (no sobrepromete secciones condicionales). 49+2 tests vitest. **60%** (faltan mi-perfil editable + filtro role). |
| W.10 | **Live Hub — browsing transmisiones (modulo NUEVO)** | 2026-05-10 → cerrado 2026-06-20 | LiveHubView + LiveHero + LiveSideCard + UpcomingCard. SSR `fetchHappeningNow` + `fetchUpNext`. 4 estados (default 2+N, 1 solo, 0 lives + N upcoming, empty). Slate Mono tokens globales + Lux overrides. 8 escenarios Playwright. Validacion visual con `LiveHubDemoSeeder` (3 lives + 6 upcoming + 4 past). **CERRADO 100%** 2026-06-20 (consenso, anti-regadero — sin doc maestro). |
| Docs | **W.10 viejo Hub Personal → W.18 (rename)** | 2026-06-20 | `git mv docs/webapp/W.10-notificaciones-perfil.md → W.18-hub-personal.md`. Refs actualizadas en `_index.md`, `PARITY-MATRIX.md`, `BACKEND-API-MAP.md`, `PLAN.md` (agregado row W.10 Live Hub nuevo + W.18 Hub Personal en tabla modulos y estimacion). Conflicto numeracion resuelto. |
| Hygiene | **Fix vitest suite 194→202/202 verde** | 2026-06-20 | Node 25.8.1 expone `globalThis.localStorage` experimental sin metodos. Fix `tests/setup.ts`: stub propio Map-based + `Object.defineProperty` sobre globalThis + window. Una sesion, un archivo, un fix. Desbloquea cierre formal de modulos. |
| Re-auditoria | **Counters reales doc vs codigo** | 2026-06-20 | Sesion completa de re-recount despues de 1 mes pausa. W.5: 0%→94%, W.6: 0%→45%, W.10 Live Hub nuevo 100%, W.18 renombrado. Total global 40%→48.7% (344/707). PARITY-MATRIX + PENDIENTES-WEBAPP sincronizados con realidad. |
| W.3 | **Bulk .ics download (Mi Agenda completa)** | 2026-06-21 | Generador puro `lib/ics.ts` RFC 5545 client-side (VCALENDAR + N VEVENT, UID determinista, DTSTAMP UTC, escape comas/semicolons/backslash). Reemplaza handler fake `handleTodas` de AgendaView. Boton "Todas" del header ahora visible cuando `countMine > 0` (favoritas en CUALQUIER dia, no solo el visible). Filename `mi-agenda-{event.slug}.ics`. Sin endpoint backend nuevo: el snapshot AgendaData ya tiene todo. Pulido UX: toast pre-download "Preparando tu agenda..." + boton disabled ~700ms (anti doble-click + feedback inmediato en conexion lenta). +16 tests vitest (218/218 verde). |
| W.0 | **Sidebar wire + smoke + brand cleanup — Sprint 1 CERRADO 9/9** | 2026-06-21 | Smoke 5/5 items navegan (home, agenda, live, speakers, social). Patron `available: boolean` ya estaba bien: `/sponsors` disabled con tooltip "proximamente" + opacidad 55%. Logica: conforme aparece cada modulo, `available: false` → `true` en `SidebarPill.tsx` (no items fantasma). Bonus: quitado el brand letter inicial (`event.name?.charAt(0)`) que generaba ruido visual tipo debug en eventos sin logo elaborado — si el cliente quiere identidad en sidebar, lo correcto es generar keyvisual real. Sidebar arranca ahora directo en Home. **Sprint 1 CERRADO** (9/9): item 1 hygiene + items 2-7 cierres formales W.5/W.10/W.6/W.8 + items 8-9 bulk .ics + sidebar wire. |
| Theme | **Reemplazo next-themes con provider propio (fix Next 16 + React 19)** | 2026-06-21 | Bug abierto upstream `next-themes 0.4.6` issues #385/#387: "Encountered a script tag while rendering React component". Solucion: provider propio 60 lineas en `ThemeProvider.tsx` + script blocking anti-FOUC inline en `<head>` del LocaleLayout server component. Cambiados 4 consumers (`ThemeToggle`, `ThemeTogglePill`, `EventThemeProvider`, `sonner`). API minima `useTheme()` retorna `{theme, resolvedTheme, setTheme}`. next-themes queda en package.json sin desinstalar (puede haber uso indirecto). |
| W.7 | **Sponsors — modulo CERRADO 23/23 Sprint 2.A** | 2026-06-21 | Wall espejo Expo (Platinum cards + Gold cards + Compact logos round) con living shuffle 7s via **framer-motion `layout` + spring damping 28 stiffness 120** (equivalente exacto al Reanimated Expo). DetailPanel: Hero + Sessions + Trivia (multi-pregunta + bonus pts + pantalla resumen `+X puntos ganados` + autoclose 2.5s — espejo Expo `TriviaModal`) + ContactForm (chips servicios + textarea + 409 ALREADY_CONTACTED) + Actions (Sitio web + Email). Toast `+puntos por visitar` via lumina (top-center) condicional a `points_awarded > 0` (no toast falso si ya visitaste). **Gap backend cerrado**: `SponsorResource` ahora expone `trivia_enabled`/`passport_enabled`/`visit_points` + `GamificationController::visitStand` devuelve `points_awarded` (distingue si tryAward otorgo de verdad). Esc + click fuera cierran detail. Stagger reveal del detail (hero→sesiones→trivia→contact→actions, 60ms delay). Elevaciones: Lux multi-layer shadows + Noir shadow base oscura (no halo accent — rompia con primary_color rojo/coral). Colores success/error neutrales `rgba(80,200,120)`/`rgba(255,100,100)` en vez de tokens del cliente. **CIERRE FORMAL**: Skeleton loading SSR con shimmer + 3-tier phantom cards (`loading.tsx` segmento). Tooltip radix custom en compact logos (reemplaza title HTML nativo). Heart pulido: sin circle background + animacion framer-motion (`AnimatePresence` + spring damping 10 stiffness 380 mass 0.6, fresh cada toggle) — reemplaza keyframes CSS vanilla que se veian cortados. **E2E Playwright 12/12 verde** (auth gate, SSR wall 3 tiers, click Platinum, Esc, contact 200/409, sin trivia/services, Notion minimo, search filter, sin resultados, click fuera). Lighthouse prod build: **Accessibility 98 ✅** (target ≥95), CLS 0 ✅ (sin layout shift del shuffle). Performance autenticado → batch QA final cross-modulos (Lighthouse standalone redirige a login, comun a W.3/W.5/W.10/W.6/W.8). 14 tests vitest + 12 E2E = 244 tests verde total. |
| Backend | **SponsorResource + GamificationController visit-stand** | 2026-06-21 | SponsorResource expone 3 campos nuevos: `trivia_enabled`, `passport_enabled`, `visit_points` (necesarios para que el cliente sepa badges + condiciones sin ir a `visit-stand`). `GamificationController::visitStand` ahora distingue si `PointsService::tryAward` efectivamente otorgo (return `points_awarded: int`, 0 si idempotente/gamification off/role no permitido). Sin estos cambios la UI mostraba toast "+X puntos" cada click aunque el usuario ya tuviera los puntos. |
| W.14 | **Anuncios Fase A — lista + BellPopover + deep links (10/20 Sprint 2.C)** | 2026-06-29 | Ruta `/anuncios` SSR + `AnnouncementsView` lista vertical espejo Expo `anuncios.tsx` (cards con image/title/body/timeAgo/dot/chevron, expandibles inline si tienen `action_url`). **`BellPopover`** reemplaza el `<span>` placeholder del bell en `SidebarPill` (W.0): preview de 5 mas recientes + footer "Ver todos" → `/anuncios`. Badge unread via `localStorage:eventos:announcements:lastSeenAt:{eventId}` con sync cross-tab via `storage` event. Lazy init sin setState-in-effect. **Helper puro `parseActionUrl()`** con 13 mappings: `eventos://gamification/*` → `/desafio` (golden ticket case), `agenda/{id}` → `/agenda?highlight={id}`, `speakers/{id}` → `/speakers?id={id}`, `sponsors/social/wall/anuncios` directos; backlog W.13/W.15/W.17 (`my-support/faq/recap/join-team/stand`) caen a `internal-future` con toast amable; `http(s)` → `window.open('_blank')`; esquemas desconocidos → `console.warn` sin romper UI. **End-to-end golden ticket verificado**: `GameController:680` ya crea announcement privado con `action_url: eventos://gamification/rewards` cuando alguien gana un live game; webapp lo lista + click navega a `/desafio` sin coordinacion nueva backend. **309/309 vitest verde** (+39 nuevos: 23 announcementDeeplink + 16 announcementsUnread). **E2E `anuncios.spec.ts` 10/10 verde** (15s, serial mode): auth gate / SSR sorted / 4 tipos action_url (interna/externa/null/future) / empty state / Bell badge unread cae a 0 / footer Ver todos / card popover navega + cierra / sidebar item. Fixture + 1 handler nuevo en mockBackend. **Decisiones:** sin tabs "Todos/No leidos" (cosmeticas, backend no persiste read_at); sin modal/panel detail (anuncios entran completos en card); BellPopover divergencia intencional vs Expo (mobile bottom tabs vs sidebar desktop). **Pendiente Fase B (~2h):** banners + highlights + RT socket W.11. **Pendiente Web Push:** W.12 Polish con manifest+SW+VAPID — backend ya tiene 8 tipos de push enum documentados en memoria para no inventar. |
| W.9 | **Desafio Engagement Hub — CERRADO 35/35 Sprint 2.B** | 2026-06-27 → 2026-06-29 | Hub split layout literal espejo W.7 (wall izq 6 cards apiladas + DetailPanel der). 6 cards: Hero (posicion + puntos + mini ranking top 3), Golden Tickets (lista con cada pending boton individual), Premios preview (2 inline), Tip (puntos para entrar top 10), Retos (4/5 progreso), Pasaporte (2/3 stands). 6 paneles: GoldenTicket reveal (claim_code XL + QR real qrcode.react + RgbRect WAVE_COLORS animado via `@property --dx-rgb-angle`), Ranking (podio escalado #2 #1 #3 + top 50 + my_position siempre visible), Rewards (catalogo con redemptions INLINE — 5 estados CTA: Canjear / Mostrar QR TEAL borderless / Ya canjeado disabled+check / Agotado / Faltan X), Retos (lista completa actions con earned), Pasaporte (grid stamps + completed banner), Rules (5 reglas educativas + tabla puntos). **Sesion 1 (2026-06-27, `32018f1`):** estructura inicial 18/35 + SSR 5 endpoints + sidebar + i18n + colores TEAL fijos del sistema (no `var(--accent)`). **Sesion 2 (2026-06-29, `4238c69`):** shapes backend reales (`/me/prizes` nested, `/my-passport` stands rename, `/rewards.can_redeem` calculado cliente) + 4 proxies Next + lazy fetch + redeem optimistic (modal loading→ready) + haptic helper 5 intensidades + golden tickets flow individual (3 iteraciones: lista repetida rechazada, modal full-screen rechazado, reveal in-panel aceptado) + QR ring fix + focus ring fix + 27 vitest. **Sesion 3 (2026-06-29, `8aab0b4`):** redemptions INLINE en catalogo (espejo Expo `leaderboard.tsx:316-345` — eliminado tab "Mis canjes" por feedback_mirror_feature_completo + feedback_no_repetir_info_en_panel). Helper puros `findActiveRedemption`/`hasConfirmedRedemption`/`orphanActiveRedemptions` con `now` inyectable. `handleShowExistingQR` reabre modal con token EXISTENTE sin pegar POST (cero riesgo cobrar puntos doble). Bloque "Canjes activos sin catalogo" garantiza acceso al QR si reward fue retirado. CSS nuevo: `.dx-btn-show-qr` (TEAL borderless) + `.dx-reward-badge-confirmed` + `.dx-rewards-orphans`. **E2E desafio.spec.ts 11/11 verde** (auth gate, SSR hub, ticket pending reveal, 5 estados CTA inline visibles, Mostrar QR sin POST verificado con spy, Canjear con POST 1 vez, Esc cierra modal, bloque orphans, 3 viewports automatizados desktop 1600/tablet H 1130/mobile 390 sin overflow horizontal). Fixture `desafioFixture` + 7 handlers en mockBackend. Serial mode (8 workers saturan SSR 5-fetch, 13s vs 30s timeout). **270/270 vitest verde** (+11 helpers redemption). Decisiones: encuestas viven en W.4 streaming (in-stream sockets), toast "+X pts via diff" descartado (espejo Expo no lo hace), `claimTicket` attendee-side descartado (vendedor confirma). Validacion visual Lighthouse + device fisico → batch QA final cross-modulos. |

---

## Fase 0 — Setup (2026-03-28)

- [x] Entorno: PHP 8.3, Redis, MySQL 8.4, Node 22, Git, Composer, EAS CLI
- [x] Laravel 11 base: Sanctum, Spatie, Filament, Horizon, Telescope, Sentry. 16 migraciones.
- [x] Expo SDK 55: Router, NativeWind, Reanimated, SecureStore, MMKV, TanStack Query, Zustand, i18n
- [x] Socket.IO: Redis adapter DB 2, auth Sanctum, rooms event/session/chat, health endpoint

## Fase 1 — MVP funcional (2026-03-29 → 2026-04-07)

| Sesion | Feature | Fecha | Tests |
|--------|---------|-------|-------|
| 1.1 | Auth + Roles + QR HMAC + Tracking | 2026-03-29 | 12 |
| 1.2 | Motor modulos dinamicos + cache Redis | 2026-03-28 | 23 |
| 1.3a | Contenido backend: sessions, speakers, pages, announcements, Filament CRUD | 2026-03-28 | — |
| 1.3b | Contenido app: agenda, speakers, pages, anuncios, FlashList + expo-image | 2026-03-28 | — |
| 1.4 | QR check-in + kiosco standalone + Socket.IO real-time | 2026-03-29 | — |
| 1.5 | Leads vendedor: scanner QR, notas, tier, historial, export | 2026-03-30 | — |
| 1.6 | Patrocinadores + stand teams: sponsors=stands, stand_members, lead_edits | 2026-03-30 | — |
| 1.7 | Networking: solicitudes, aceptar/rechazar, contactos, directorio | 2026-03-31 | — |
| 1.8 | Gestion usuarios + bans (motivo, expiracion, Filament) | 2026-03-31 | — |
| 1.9 | Chat real-time por sesion (Socket.IO, Redis) | 2026-04-01 | — |
| 1.10 | Encuestas en vivo (live_poll_questions, 4 tipos, slides) | 2026-04-04 | — |
| 1.11 | Push notifications (Expo Push API, FCM v1) | 2026-04-04 | — |
| 1.12 | Tracks + session types | 2026-04-04 | — |
| 1.13a | Emails automaticos: 11 mailables, BaseEventosMail, EmailService | 2026-04-05 | — |
| 1.13b | SMTP propio por organizacion | 2026-04-05 | — |
| 1.x | Upload imagenes / Cloudflare R2 | 2026-04-05 | — |
| 1.x | Banners: carrusel sponsors en Home | 2026-04-05 | — |
| 1.x-A | Onboarding configurable: backend + app base | 2026-04-05 | — |
| 1.x-B | Onboarding animaciones premium | 2026-04-06 | — |
| 1.14 | Streaming nativo + Mi Agenda | 2026-04-06 | — |
| 1.15 | Q&A en vivo + moderacion Filament | 2026-04-06 | — |
| 1.16 | Evaluacion sesiones (ratings, crystals) | 2026-04-06 | — |
| fix | YouTube iframe + logica separada por tipo URL | 2026-04-07 | — |
| 1.17 | Photobooth / Memorias (galeria moderada, likes) | 2026-04-07 | — |
| 1.18 | Matchmaking por intereses (overlap, sugerencias) | 2026-04-07 | — |
| 1.19 | Social wall (feed + posts + comments + likes) | 2026-04-07 | — |
| 1.20 | Gamification 13 acciones + leaderboard | 2026-04-07 | — |
| 1.21 | Passport stamps por lead scan | 2026-04-07 | — |
| 1.22 | Registro personalizable + import/export + approval + deep link + consent | 2026-04-07 | — |

## Pulido funcional + Stress testing (2026-04-07)

- [x] Tab vendedor unificado con presencial
- [x] Modulos por rol
- [x] k6 + artillery scripts (ejecutado local, pendiente VPS)

## Seguridad (2026-04-07 → 2026-04-12)

| ID | Fix | Tests |
|----|-----|-------|
| SEC-1.1 | Socket room authorization (join valida eventId, session via internal+Redis) | 6 |
| SEC-1.2 | HTMLPurifier trait en 8 modelos | 13 |
| SEC-1.3 | Token expiration 7d + POST /auth/refresh + auto-refresh interceptor | 7 |
| SEC-2.1 | SecurityHeaders middleware (X-Frame, CSP, HSTS, Referrer, Permissions) | 6 |
| SEC-2.2 | CORS hardening (methods/headers restringidos) | — |
| SEC-2.3 | HTTPS enforcement en app (throw si no HTTPS en prod) | — |
| SEC-2.4 | php artisan security:check | 4 |
| SEC-2.5 | .env.production.example + SESSION_SECURE_COOKIE | — |
| SEC-3.3 | Account lockout (5 intentos → 30 min, HTTP 423) | 6 |
| SEC-3.4 | Socket rate limiting Redis (max 5 conexiones/user) | — |
| SEC-3.5 | 5 FormRequests user-facing | — |
| SEC-3b.1 | Token register 30d → config (sanctum.expiration) | 2026-04-12 |
| SEC-3b.3 | Middleware CheckBan server-side en todas rutas API | 2026-04-12 |
| SEC-3b.5 | Ban real-time via socket (/internal/ban/enforce → app) | 2026-04-12 |

Total: 42 security tests, 309 tests backend.

## Real-time + Invalidation (2026-04-09)

- [x] RT sync admin→app: 5 entidades (agenda, speakers, announcements, branding, modules)
- [x] 4 capas: socket events + focusManager refetch + reconnect refetch + staleTime
- [x] Jitter aleatorio (200-1000ms) para evitar thundering herd
- [x] EventObserver + socket branding:updated para cambios de config

## UI/UX Lumina Noir (~98% completado)

### Fundamentos (2026-04-07)
- [x] Design tokens: #0e0e0e fondo, primary_color dinamico, Urbanist + PlusJakartaSans
- [x] ThemeProvider + useBranding + ScreenWrapper anti-flash Android
- [x] FloatingTabBar: liquid glass, sliding bubble spring, bounce on tap, 5 tabs reales
- [x] StatusBar light, SafeArea en todas las pantallas

### Pantallas completadas
- [x] Home: header configurable, hero text/image, HappeningNow crossfade 6s, GamificationHud, ModuleMenu cascade — 2026-04-07
- [x] Agenda: day strip, glass cards, tracks, heart particles, calendar, DaySlide direccional — 2026-04-08
- [x] Speakers: carousel breathing, detail hero, rating crystals diamond, LinkedIn — 2026-04-08
- [x] Streaming: split-screen, YouTube embed, session states, buttons contextuales — 2026-04-08
- [x] Social: Feed+Memorias+Momentos unificado, stories 24h, comments BottomSheet, header blur sticky — 2026-04-09 + 2026-04-11
- [x] Sponsors: Brand Wall grid tiers, living shuffle, Brand Profile, trivia A/B/C/D, contact form — 2026-04-09
- [x] Profile: beam Ocean, edit modal, social links, stats, pull-to-refresh — 2026-04-09
- [x] Encuestas PollSlides: rediseno completo por pregunta (Multiple/Star/OpenText) — 2026-04-10
- [x] Chat sesion: emojis animados, cooldown, Enter=enviar, error handling — 2026-04-10
- [x] Mi QR: tab real, RGB wave pastel, badge digital, fullscreen modal, wallet ready — 2026-04-10
- [x] Gamificacion: HUD teal/cyan, barra segmentada, pantalla unificada, portal cards, premios — 2026-04-10
- [x] Vendedor: tabs presencial, VendorHappeningNow, Mi Stand premium, leads Lumina Noir — 2026-04-10
- [x] Networking: contactos vCard, guardar en telefono, export masivo .vcf — 2026-04-10
- [x] Pending-approval Lumina Noir — 2026-04-12
- [x] Activate-account Lumina Noir — 2026-04-12
- [x] ConnectionError screen reutilizable — 2026-04-12
- [x] Banned screen Lumina Noir — 2026-04-11

### Responsive (2026-04-10)
- [x] Audit completo 360dp (ZTE) + 411dp (Medium Phone): 31 archivos, 12 pantallas SafeArea
- [x] Layout proporcional: skeletons, leads header, login scroll, ModuleMenu, HappeningNow, HomeHero

### Micro-interacciones (2026-04-11)
- [x] ScalePress: tap feedback 0.96 + haptic (ModuleMenu, Speakers, Agenda, Networking)
- [x] Image reveal: transition={300} en 17 archivos expo-image
- [x] ContentFade: opacity 0→1 (400ms) en sponsors, anuncios, speaker detail
- [x] FadeInItem: stagger wave top→bottom
- [x] AnimatedBadge: scale pop en HomeHeader + Networking badges
- [x] Haptics: leaderboard, profile, social, bell
- [x] Screen transitions: slide_from_right (stream mantiene slide_from_bottom)
- [x] Home wave entrance: HomeSkeleton → FadeInItem coordinado

### Transversales
- [x] Skeleton loading en 5+ pantallas
- [x] EmptyState en social feed, memorias, speaker detail
- [x] Iconografia: MaterialCommunityIcons (no emojis) en ModuleMenu
- [x] LuminaToast reemplaza Alert.alert en toda la app
- [x] returnKeyType en 14 archivos (search/next/go/send/done)
- [x] Background #1a1919 → #141414, console.log cleanup

## Onboarding DaVinci (2026-04-11 → 2026-04-12)

- [x] Welcome: 5 floating pills formas unicas, 5 backgrounds configurables (particles, constellation, mesh, bubbles, minimal), primaryColor aplicado
- [x] Auth: login/register animado, ForgotSheet BottomSheet, keyboard handling, stats con FadeInDown
- [x] Login inteligente 2 pasos: POST /auth/check-email → not_found/pending_activation/active → password animado
- [x] Photo: avatar 180px, camara/galeria, upload + authStore update, foto en context
- [x] About: preview card live, cargo/empresa, campos con iconos, scroll natural
- [x] Interests: chips wrap con emoji, haptic, min 3, pending_survey MMKV
- [x] Done: badge IDENTICO a MiQR, QR real funcional, tap fullscreen, confetti (solo registro)
- [x] Gamificacion: AnimatedPts (scale+particulas+color flash), SkipModal BottomSheet, 80pts max
- [x] Auth legacy eliminado (login.tsx, register.tsx, forgot-password.tsx borrados)
- [x] Activate-account redirige a onboarding photo step (flag post_activation_onboarding)

## Onboarding configurable Filament (2026-04-12)

- [x] JSON onboarding_steps_config en events (migration + model + cast array)
- [x] Filament UI: 7 secciones colapsables (Welcome, Auth, Photo, Forms, Survey, Done, Orden)
- [x] Welcome: pills dinamicas, hero image, show_text, title_prefix, textos botones
- [x] Auth: show_title/subtitle/stats, stats dinamicas, hide register link
- [x] FormStep generico: campos dinamicos (text/tel/email/number/url/select/checkbox/textarea)
- [x] Colores master/slave (ColorPicker en Filament, useStepColors helper)
- [x] Steps dinamicos desde step_order + enabled flags
- [x] Real-time: polling 30s pre-login + socket data:invalidate post-login
- [x] Retrocompatibilidad: si config null, fallback a hardcoded

## Campos dinamicos 1.x-E-A (2026-04-12b)

- [x] SelectSheet: BottomSheet radio reutilizable con accent color y haptic
- [x] Checkbox: Switch toggle inline, glass row
- [x] Textarea: TextInput multiline 4 lineas
- [x] Validacion required: borde rojo + toast + skip oculto si required
- [x] PreviewCard live cuando form tiene job_title/company

## Campos dinamicos 1.x-E-B (2026-04-13)

- [x] SearchableSheet: BottomSheet 65% con TextInput busqueda, filtro local, radio select
- [x] CheckboxGroupSheet: BottomSheet multi-select con checkboxes, boton "Confirmar (N)"
- [x] DateTimePicker: @react-native-community/datetimepicker, picker nativo, guarda ISO, muestra formateado es-CO
- [x] FormStep: 3 render cases nuevos (searchable_select, checkbox_group, date)
- [x] onboardingApi.ts: tipos actualizados (3 tipos + preset + preset_options)
- [x] PresetController: GET /presets/{type}, GET /presets/cities/{code}
- [x] config/presets.php: 53 paises, ciudades (9 paises), 20 industrias
- [x] Filament: 11 tipos en selector + campo preset para searchable_select
- [x] OnboardingController: resuelve preset → preset_options al servir config
- [x] OnboardingSeeder: ejemplos de los 3 tipos nuevos
- [x] QA: 7 tests presets + 11 tipos verificados, 309 backend tests passing

## Seguridad SEC-3b completado (2026-04-13)

- [x] SEC-3b.2: index.tsx valida GET /me al startup. 401→clearAuth, 403→banned/pending. Fallback red graceful.
- [x] SEC-3b.4: CheckApproval.php middleware server-side. 403 si no aprobado. Excluido de auth/profile/onboarding.
- [x] Fix: index.tsx maneja 403 (antes solo 401, app podia quedar colgada)
- [x] Fix: lockout counter se resetea cuando lock expira (antes acumulaba intentos para siempre)

## QR dinamico rotativo 1.C3 (2026-04-13)

- [x] Formato d.{attendee_id}.{window}.{signature_32hex} — O(1) validacion, no O(n)
- [x] Ventana 60s, tolerancia 5 ventanas (~180s), clock skew +-1
- [x] GET /me/qr devuelve token dinamico + expires_in (TTL real)
- [x] Checkin valida: estatico primero (backward compat), luego dinamico
- [x] LeadController tambien valida dinamico (fix posterior al QA)
- [x] App: useQrToken refetch 50s, MiQrScreen countdown "QR dinamico · 45s"
- [x] DoneStep: preview actualizado al formato nuevo
- [x] Validacion qr_token relajada min:20 max:100 (formato variable)

## Scanner Lumina Noir (2026-04-13)

- [x] Reescritura completa scanner-stand.tsx (NativeWind → StyleSheet)
- [x] Resultados en BottomSheet (55% success/duplicate, 38% error)
- [x] Lead card con foto, nombre, cargo, empresa, email
- [x] Tier selector inline (hot/warm/cold) en resultado success
- [x] Scan line animada Reanimated, haptic feedback en todos los estados
- [x] ScalePress en todos los botones, SafeArea top+bottom

## Docs reestructurados (2026-04-13)

- [x] EventOS_Roadmap.md: 3217 → 419 lineas (v4.0 slim)
- [x] Original archivado: docs/ROADMAP-HISTORICO-v3.1.md (3144 lineas intactas)
- [x] docs/PENDIENTES.md: reescrito auto-contenido, organizado por area de trabajo
- [x] docs/COMPLETADO.md: nuevo, historial por area
- [x] 3 apendices extraidos: ANALISIS-COMPETITIVO.md, WEB-APP-PLAN.md, WHITE-LABEL.md
- [x] PLAN-TAGS-MODULOS.md: plan arquitectura tags + visibilidad modulos (aprobado, pendiente implementar)

## Lux v2 "The Gallery" — Light Mode completo (2026-04-17)

### Tokens y sistema
- [x] theme-lux.ts: tokens completos (surface, text, icon, shadow, categories, states, glass, skeleton, statusBar, overrides, semantic)
- [x] useTheme() dinamico en ~85 archivos
- [x] Session Types configurables desde Filament (nombre, color, sin hardcoded TYPE_COLORS)

### Pantallas migradas Lux (12 fases)
- [x] Tokens, Tab Bar, StatusBar, Onboarding, Home, Agenda, Session Detail, Speakers, Mi QR, Social, Sponsors, Networking, Leaderboard, About

### Perfil Lux
- [x] StatCard, DataCard, avatar badge, social icons, edit fields, bottom sheet icons — card pattern (#FFFFFF + shadow.sm)

### Back buttons Lux (6 pantallas)
- [x] FAQ, Support, Anuncios, Mi Stand, Leads, Lead Detail — shadow.md flotante

### Cards Lux (5 pantallas)
- [x] Anuncios, Mi Stand (7 cards), Lead Detail (5 cards), My Support — card pattern

### NativeWind → StyleSheet (12 archivos, 173 className)
- [x] ChatPanel, session-chat, QnAPanel, PollPanel, DynamicField, SplashLoader, banners, documentos, encuestas, passport, pages, BannerCarousel
- [x] Pantallas siempre claras (opcion B), solo migracion sintaxis
- [x] session-stream: no tocado (siempre dark)

### Formularios sin accent
- [x] FormStep: accent removido de bordes/labels/checkboxes, solo rojo oscuro (#B91C1C) para errores
- [x] DynamicField: reescrito completo, labels neutros, checkboxes verdes, sin accent
- [x] Switch trackColor: verde semantico (#22C55E) en vez de primaryColor

### Componentes criticos
- [x] LuminaToast: texto textTokens.white (era #FFFFFF invisible en Lux)
- [x] Skeleton: contenedores backgroundSunken en Lux (era surface.low invisible)
- [x] FAQ: chips ink activos, cards/contact/response con shadow.sm
- [x] MyInterests: chips no seleccionados con shadow.sm + borderStrong
- [x] DaySlide agenda: opacity removida (evita flash shadow Android)

### Dark Islands (siempre Noir)
- [x] Gamificacion/Desafio: pantalla completa forzada Noir (noirSurface/noirText importados)
- [x] RGB_BORDER: 4px → 6px
- [x] HappeningNow SessionCard: sin shadow.lg (evita flash en crossfade)
- [x] GamificationHud: sin shadow.lg (idem)
- [x] Componentes dark island documentados: HappeningNow, VendorHappeningNow, GamificationHud, VendorGamificationHud, Mi QR badge, MomentosViewer, PhotoViewer, QR modal, Scanner, Streaming

## Moderacion chat completa (2026-04-12)

- [x] Ban real-time via socket (/internal/ban/enforce → ban:enforced → app /banned)
- [x] Middleware CheckBan en todas rutas API (excepto auth/me, auth/logout)
- [x] Interceptor 403 ban en api.ts
- [x] Palabras bloqueadas chat + Q&A (config Filament, silent drop, cache 5min)
- [x] Chat delete + ban desde app (admin long press) y desde chat monitor
- [x] Chat monitor HTML standalone por sesion (acceso desde Filament)
- [x] Velocidad monitor (cola mensajes directo/0.5s/1s/2s), slow mode, pause/resume
- [x] Rendimiento: cache auth tokens 15min, connection pooling, message batching 200ms

## Error handling (2026-04-12)

- [x] ConnectionError screen reutilizable (wifi-off, reintentar)
- [x] Onboarding: 6s timeout + spinner + ConnectionError
- [x] Home presencial + virtual error states

## Auditoria auth (2026-04-12)

- [x] 39 escenarios + 10 edge cases verificados en codigo
- [x] 14 bugs encontrados y corregidos (BUG-065 a BUG-078)
- [x] 3 code smells documentados (CS-001/002/003, no criticos)

## QA Master (2026-04-12b)

- [x] Barrido completo: 60+ endpoints probados con curl real
- [x] 20 modulos cubiertos, 3 roles verificados (presencial 14 modulos, virtual 11, vendedor stand+leads)
- [x] Escritura: social post/like/comment, Q&A, poll vote, profile, registration fields, favorites, photo, expo token
- [x] Middleware: auth 401, ban 403, throttle login 429, security headers
- [x] Docs: QA-AUTH-ONBOARDING.md + QA-MASTER.md

## Rewards / Premios canjeables (2026-04-10)

- [x] Catalogo premios: Filament CRUD, addon toggle, RewardSeeder
- [x] QR temporal HMAC 5min para canje
- [x] Staff confirma escaneo, descuenta puntos
- [x] Expire + refund automatico si no se canjea
- [x] 9 tests, seguridad SQL fix, points_log signed
- [x] Flujo canje: toast, estados boton (canjear/ver ticket/canjeado), QR rect RGB, recovery

## Gamification config (2026-04-10)

- [x] 13 acciones con puntos, daily_max, labels editables
- [x] Roles por accion, rules endpoint publico
- [x] Filament unificado bajo grupo Gamificacion
- [x] passport_completion_points configurable

## Analisis competitivo (2026-04-09)

- [x] Cotizaciones reales: Cisco $88K USD, ICE360 $49M COP
- [x] 8 features donde EventOS ya supera a ambos
- [x] 6 gaps identificados → sesiones 1.C1-C6
- [x] Pricing: $3K-8K/evento, $800-1.5K/mes SaaS
- [x] Escalabilidad horizontal validada hasta 100K+

## Showcase web demo inversor (2026-04-13)

- [x] 6 versiones iteradas (v1→v6): opening cinematico, speakers, agenda, streaming, networking, social, gamification, brand wall
- [x] Cursor fantasma, minimize-to-pill, TNT finale, progress dots, play gate
- [x] GSAP skills repo clonado (design/gsap-skills/)
- [x] Spatial UI documentado en roadmap (Apendice F → docs/WEB-APP-PLAN.md)

## Tags + Visibilidad modulos — Backend (2026-04-14)

- [x] Migration: campo `tags` JSON en attendees (default [])
- [x] Migration: `visibility_presence` enum + `visibility_tags` JSON en modules
- [x] Roles simplificados: presencial/virtual eliminados → `attendee` unico
- [x] Attendee model: cast tags array, helper `hasAnyTag()`
- [x] Module model: cast visibility_tags array
- [x] ModuleController: filtrado triple (role + checked_in_at + tags)
- [x] QR disponible para todos los attendees (identidad, no solo ticket)
- [x] CheckinService: attendance count usa attendee (no presencial)
- [x] API resources: AttendeeResource expone tags, ModuleResource expone visibility
- [x] Filament: 9 resources actualizados (roles attendee/vendedor, TagsInput, visibility selectors)
- [x] Validation rules: NotificationController, AttendeeController actualizados
- [x] Observers: ModuleObserver + ContentObserver con nuevas cache keys
- [x] AuthService + AttendeesImport: default role attendee
- [x] AttendeeFactory: +withTags() state, default attendee
- [x] 48 archivos modificados, 314 tests passing (797 assertions), 0 fallos
- [x] Commit: 30ce854

## Layout unificado — App (2026-04-14)

- [x] Merge (presencial)/(tabs) + (virtual)/(tabs) → (tabs)/ unico
- [x] Home unificado con logica vendor condicional (isVendor)
- [x] UserRole simplificado: 'attendee' | 'vendedor' | 'admin'
- [x] Ruta unica homeRoute() → /(app)/(tabs) para todos
- [x] authStore, authApi, DoneStep, MiQrScreen, ProfileScreen, activate-account, pending-approval actualizados
- [x] 14 archivos eliminados (layouts duplicados), 6 creados, 9 actualizados
- [x] TypeScript 0 errores nuevos, Expo bundle compila OK (4.6MB)
- [x] Commit: 810cc89

## Event Lifecycle — Backend + App (2026-04-14)

- [x] Migration: status enum (draft/registration/published/live/ended) + modality enum en events
- [x] Migration: about_enabled, about_image_url, about_text, about_links en events
- [x] Migration: roles en modules JSON (presencial/virtual → attendee)
- [x] Admins ven todos los modulos (skip role filter en ModuleController)
- [x] Branding API: status, modality, venue, max_attendees, registered_count, session_count, photo_count, about_*
- [x] Filament: estado+modalidad+fechas+venue+capacidad+about condicional con toggle
- [x] EventObserver → InvalidationService → socket data:invalidate → app refetch
- [x] App: 4 estados de Home (registration, published, live, ended)
- [x] CountdownTimer: normal (registration) + compact (published) + "comienza hoy" (expired)
- [x] EventInfoCard: modalidad badge, venue, registrados/capacidad, cierre registro
- [x] EventArchive: banner finalizado + stats + links a agenda/social/gamification/speakers
- [x] About pre-evento: pantalla /(app)/about con imagen+texto+links, card en Home registration
- [x] ModuleMenu compact en published (cards 56px, iconos 16px)
- [x] Socket debounce 800ms por entidad (7 invalidaciones = 1 refetch)
- [x] useBranding staleTime 30min → 5min + refetchQueries
- [x] Role pill eliminado de Mi QR, Perfil, DoneStep
- [x] Fix: branding API crasheaba por columna 'approved' inexistente
- [x] Fix: Stack.Screen mi-qr fantasma causaba socket reconnect loop
- [x] Seeders actualizados (7 archivos presencial→attendee)
- [x] 314 tests backend, TS 0 errores app
- [x] Commits backend: d970983, e4a3981, 7a61a87, 878a0b3, d375e26, d48b559, 7bda761, b25518b
- [x] Commits app: 810cc89, 59f49f7, 267ec45, 90edd7f, 7f6f6cd, c26eb06, 0e5ff8b, f30c9fd, 167c72c, 95b8aa5, b81e22a, bcc7649, 142d039, daf8411, 877a851, 85ee6f3, 5834769

## Filament cleanup (2026-04-14)

- [x] Reorganizar navegacion: 11 grupos inconsistentes → 7 limpios
- [x] Grupos: Evento(6), Contenido(7), Interaccion(7), Comunicacion(5), Registro(4), Gamificacion(3), Sistema(1)
- [x] Tildes unificadas, sort secuencial sin duplicados
- [x] 26 archivos actualizados
- [x] Commit: d2a9e86

## Optimizacion onboarding cache (2026-04-14)

- [x] useOnboarding: cache-first desde MMKV (initialData + initialDataUpdatedAt=0)
- [x] Primera vez: loading → fetch → cache. Segunda vez en adelante: instantaneo
- [x] Eliminado refetchInterval 30s (innecesario con socket invalidation)
- [x] Commit app: 5834769

---

## Sesion 2026-04-14 (tarde) — Session Detail + Onboarding + CSV + Codigos + Seguridad

### Session Detail Screen
- [x] Pantalla detalle de sesion: badges, titulo, rating, time/location, speakers tappables
- [x] Botones: Favorita, Calendario, Evaluar, UNIRTE / Ver grabacion
- [x] Navegacion circular: Agenda → Session → Speaker → Session
- [x] Agenda card tap → session detail (antes iba directo a stream)

### Onboarding — depends_on + replay
- [x] Campos condicionales: depends_on en config JSON (pais → ciudades dinamicas)
- [x] preset_value_map para resolver codigos pais
- [x] Onboarding replay diferenciado: pre-fill foto/profile/custom/intereses
- [x] Sin confetti, sin puntos dobles, titulo "Datos actualizados"
- [x] onboarding_data JSON en attendees para campos custom
- [x] GET/PUT /me/onboarding-data endpoints
- [x] isReplay flag persistente (useState lazy initializer)

### Pre-registro CSV
- [x] Flujo completo: CSV → User+Attendee+QR → InvitationMail → deep link → activacion
- [x] Fallback sin deep link: check-email → token directo → activate-account
- [x] Deep link redirect page Lumina Noir
- [x] Verificacion identidad: campo configurable (telefono), POST /auth/verify-identity
- [x] Token rotado en cada check-email (seguridad)
- [x] Tracking deep link: invitation_clicked_at + badges Enviado/Click/Activado
- [x] Login pasa event_slug (fix bug recurrente)

### Registro por Codigo de Acceso
- [x] Modelo AccessCode con isValid(), registerUse() atomico
- [x] Filament AccessCodeResource: CRUD + generar lote + ver usos
- [x] Toggle requires_access_code en RegistrationSettings
- [x] Campo condicional en AuthStep (uppercase, sanitiza espacios)
- [x] Validacion en RegisterRequest + AuthService
- [x] Tracking: access_code_used en attendee, filtro en asistentes
- [x] registerUse() con WHERE atomico (race condition safe)

### Bugs resueltos: 21 (BUG-079 a BUG-099)
- [x] BUG-079: API crash array_flip cities (CRITICA)
- [x] BUG-080: Login sin event_slug (ALTA)
- [x] BUG-081: Fetch sin timeout en index (ALTA)
- [x] BUG-082: registrationApprovedAt fallback (ALTA)
- [x] BUG-083-099: 17 bugs adicionales (onboarding, seguridad, UX)

### Bancolombia — documentacion
- [x] Requerimientos: silent disco, multi-location, webhooks badges, mission control
- [x] Timeline y estrategia competitiva actualizados en roadmap

Commits: ~20 commits en 3 repos (app, backend, docs)

---

## Sesion 2026-04-15 — Campos unificados + Registro avanzado + FAQ + Soporte

### Campos unificados registration_fields (2026-04-15 manana)
- [x] registration_fields como fuente unica (elimina campos inline en onboarding_steps_config)
- [x] Nuevas columnas: `depends_on` (varchar 60), `show_in` (registration/onboarding/both)
- [x] Onboarding config solo tiene `field_ids` (resuelve a campos reales via API)
- [x] Endpoint nuevo: GET /events/{id}/onboarding-fields
- [x] PUT /me/registration-fields acepta campos onboarding/both
- [x] Export CSV incluye todo automaticamente (una sola tabla)
- [x] depends_on avanzado: `"campo"` (cualquier valor), `"campo:val1,val2"` (especificos), `"campo:!val"` (negacion)
- [x] Deep merge Filament: mutateFormDataBeforeSave evita que secciones colapsadas null sobreescriban config
- [x] Legacy backward compat: configs con fields inline siguen funcionando

### Validacion DaVinci — 4 bugs (2026-04-15 manana)
- [x] BUG-100: Nombre acepta @# → regex solo letras+tildes+espacios
- [x] BUG-101: InterestsStep sin toasts → toast error + exito
- [x] BUG-102: Botones disabled sin feedback → eliminado disabled, validacion con toast
- [x] BUG-103: Inputs sin borde rojo → hasError en FocusInput (AuthStep)
- [x] FormStep: toast especifico por campo ("El campo X es obligatorio")

### Staff invite 1.x-H — COMPLETADO (2026-04-15 tarde)
- [x] Backend: StaffInvitationController, StaffNotificationService, join-team.blade.php, 2 migrations, 23 tests
- [x] App: mi-equipo.tsx, scanner-invite.tsx, join-team/[token].tsx, StaffInvitationModal.tsx, useStaffInvitations.ts
- [x] Socket: StaffInvitePayload, StaffResponsePayload, StaffRemovedPayload (4 event types)
- [x] Flujo: QR scan + busqueda nombre + email + link compartible + deep link aceptacion
- [x] Landing web Lumina Noir join-team.blade.php
- [x] Config: multi-stand + expiracion tokens

### Registro cerrado 1.x-F — COMPLETADO (2026-04-15 tarde)
- [x] 3 modos: email_list, domain, both (OR logic)
- [x] Toggle master, compatible con approval + access_code + invite_only
- [x] Sanitizacion: lowercase, trim, strip @ de dominios
- [x] Mensaje rechazo custom o default
- [x] 21 tests, 38 assertions. QA: 21 escenarios verificados

### Login intentos + lockout (2026-04-15 tarde)
- [x] Backend: "Credenciales incorrectas. X intentos restantes." en 422
- [x] Backend: 5to intento devuelve 423 directo con lockout
- [x] App: Object.values(errors).flat()[0] para cualquier campo
- [x] QA audit: 25+ casos de error auth verificados

### Encuesta post-evento (2026-04-15 tarde)
- [x] Backend: scope post_event en live_polls, auto-activacion EventObserver al ended
- [x] Filament: PostEventSurveyResource dedicado (CRUD, activar/cerrar manual)
- [x] App: card EventArchive, usePostEventSurvey hook
- [x] Reutiliza 100% del sistema de encuestas existente (zero duplicacion)
- [x] Seeder: 5 preguntas, 35 encuestados, 201 votos. Export CSV funcional.
- [x] 9 tests, 27 assertions

### FAQ asistente — COMPLETADO (2026-04-15 noche)
- [x] Backend: tabla event_faqs (event_id, section, question, answer_text, answer_action_url, answer_image_url, sort_order, is_active)
- [x] Backend: FaqController API GET /events/{id}/faqs (publica, agrupada por seccion) + 6 tests
- [x] Backend: CRUD Filament FaqResource
- [x] Backend: FaqSeeder (4 categorias, 12 preguntas)
- [x] App: Pantalla FAQ con orbe animado (OrbBlob 3 estados), categorias stagger, accordion con Reanimated
- [x] App: Icono Ayuda en perfil → ruta /(app)/faq
- [x] App: Filtro por categoria (chips horizontales), busqueda visual

### Soporte asistente — COMPLETADO (2026-04-15 noche)
- [x] Backend: migration admin_response + responded_at + resolved_at en support_requests
- [x] Backend: SupportController (store + mine), ordena por id DESC
- [x] Backend: Filament SupportRequestResource con badge rojo pendientes + textarea respuesta + auto-resolve
- [x] Backend: 10 tests, 34 assertions (SupportRequestTest.php)
- [x] App: support-contact.tsx — formulario asunto+mensaje, toast + router.back() (no pantalla sent vacia)
- [x] App: my-support.tsx — lista consultas con status badge (pendiente amber / resuelto green)
- [x] App: faq.tsx — boton "Mis consultas (N)" solo si tiene consultas
- [x] Push: SendPushToAttendeeJob al responder soporte ("Tu consulta fue resuelta: Re: {subject}")
- [x] Push tap navigation: PUSH_ROUTES map (support_resolved→my-support, announcement→anuncios, agenda_reminder→agenda)
- [x] INVALIDATION_MAP: support_resolved invalida ['my-support']

### Dev build Android (2026-04-15 noche)
- [x] Wireless debugging: adb pair + connect (puerto pairing != puerto conexion)
- [x] npx expo run:android — dev build nativo en dispositivo fisico
- [x] Push notifications verificadas en dispositivo real
- [x] Push tap navigation verificada

### SEC-6.2 Rate limit endpoints escritura (2026-04-15 noche)
- [x] Trait `ChecksRateLimit` reutilizable con defaults + config custom por evento
- [x] JSON `rate_limits` en events (migration + cast + fillable)
- [x] 7 endpoints protegidos: wall posts (10/dia), comments (30/dia), Q&A (10/dia/sesion), support (5/dia), photos (20/dia), stories (10/dia), leads (200/dia)
- [x] Filament: pagina "Limites de uso" con toggles + inputs por categoria (grupo Evento)
- [x] Toggle OFF = ilimitado, toggle ON = limite editable con default pre-llenado
- [x] 10 unit tests (trait aislado) + 13 feature tests (endpoints) + 8 spam simulation tests
- [x] App: fix toasts para 429 en CommentsSheet, support-contact, scanner-stand (DAILY_LIMIT)
- [x] 0 errores TypeScript nuevos en app

### Push reminders configurables + Agregar todas al calendario (2026-04-15 noche)
- [x] `SendAgendaRemindersJob` refactorizado: lee `reminder_config` JSON por evento, windows dinamicos
- [x] Defaults: enabled=true, windows=[15,5], notify_on_change=true
- [x] Filament: pagina "Recordatorios" (grupo Comunicacion) — toggle enabled, TagsInput minutos, toggle notify_on_change
- [x] `SendSessionChangedNotificationJob` — push a favoritos cuando admin cambia hora de sesion
- [x] `EventSessionObserver` — detecta cambio start_datetime/end_datetime → dispatch push (si notify_on_change=true)
- [x] App: boton "Todas" en header Mi Agenda — agrega todas las favoritas al calendario nativo de una vez
- [x] App: `session_changed` en INVALIDATION_MAP + PUSH_ROUTES (invalida agenda + mi-agenda, navega a agenda)
- [x] 5 unit tests (config defaults/merge/disabled) + 14 feature tests (push, dedup, spam 5x10=50, multi-evento)
- [x] 0 errores TypeScript nuevos

### Mensaje anclado chat — tipo Twitch (2026-04-16)
- [x] Socket server: chat:pin/chat:unpin handlers, Redis storage (TTL 24h), broadcast a room
- [x] Socket types: ChatPinnedPayload, ClientToServerEvents pin/unpin, ServerToClientEvents pinned/unpinned
- [x] join:session envia pinned actual (si existe) junto con history
- [x] Chat monitor: campo texto libre + icono pin en hover mensajes + banner azul activo + desanclar
- [x] App: PinnedBanner component (pin icon + message + author + X close)
- [x] PinnedBanner dentro del contenedor interactivo (no reduce player)
- [x] Visible en todos los modes (chat, Q&A, poll)
- [x] useChat: pinnedMessage state + listeners chat:pinned/chat:unpinned
- [x] Solo admin puede pin/unpin (role check server-side)
- [x] TypeScript 0 errores nuevos en socket + app

### Calendar .ics en email de bienvenida (2026-04-16)
- [x] WelcomeMail adjunta .ics con fechas del evento (METHOD:REQUEST, UID unico, SEQUENCE:0)
- [x] Datos del .ics: nombre, fechas, venue, descripcion — todo de event model
- [x] Sin fechas → sin adjunto (graceful)
- [x] Verificado en Mailpit: adjunto correcto, 562 bytes, text/calendar, contenido ICS valido

### Componentes base + Theme tokens (2026-04-16)
- [x] `lib/theme.ts`: tokens surface (low/medium/high/border), text (primary/secondary/muted/label/placeholder), radius, spacing, fonts
- [x] `GlassCard`: variantes low/medium/high, radius md/lg/xl, padding, bordered
- [x] `GlassButton`: variantes primary/glass/outline/icon, ScalePress+haptic integrado, accent dinamico
- [x] `GlassInput`: label, focus state accent, error state rojo, leftIcon
- [x] `SectionLabel`: uppercase, letterSpacing, variantes sm/md
- [x] Session Detail migrada: Calendar/UNIRTE/Ver grabacion → GlassButton, Description/Speakers → GlassCard+SectionLabel
- [x] Fix TS error pre-existente: AuthStep RefObject<TextInput | null>
- [x] 0 errores TypeScript en toda la app
- [x] Decision: migrar resto de archivos junto con Light Mode (tokens dinamicos, no doble pasada)

---

## Lumina Lux v2 "The Gallery" — Sesion 2026-04-17

### Tokens y fundamentos (Fases 0-2)
- [x] theme-lux.ts reescrito: Cool gray #F6F8FA, ink #1A1A1A, sombras 4 niveles, WCAG AA
- [x] theme-noir.ts paridad completa de interfaces
- [x] theme.ts expandido: IconTokens, ShadowTokens, CategoryTokens, StateTokens, GlassTokens, SkeletonTokens, StatusBarTokens, SemanticTokens, OverrideTokens, isDark
- [x] themeStore: accent dinamico por tema (Noir=#FFFFFF, Lux=#1A1A1A)
- [x] FloatingTabBar: Liquid Glass emulation (blur 35, tint, specular highlights)
- [x] Onboarding: pills binarias ink, primaryColor por tema, AuthStep cards visibles
- [x] Gold adaptativo: semantic.gold Noir=#FFD700, Lux=#B8860B

### Home (Fase 3)
- [x] HappeningNow: dark island fondo #0a0a0a solido (NO transparente)
- [x] VendorHappeningNow: dark island identico, MiStandCard negro
- [x] GamificationHud + VendorGamificationHud: dark islands con shadow.lg en Lux
- [x] ModuleMenu: cards blancas + shadow.md
- [x] HomeHeader: bell blanca + shadow
- [x] HomeHero: kicker text.muted (no accent)

### Agenda (Fase 4)
- [x] Day pills: #FFFFFF + shadow.md (misma elevacion que modulos)
- [x] Session cards: #FFFFFF + shadow.sm, borde live neutro en Lux
- [x] Track filter: bordes #D1D3D8, "Todos" activo = ink solido
- [x] Timeline: connector #C8C9CE opacity 0.6, dots #4A4B50 upcoming
- [x] Action buttons: fondo #F0F2F5
- [x] Perf: useTheme() extraido de SessionCard/AnimatedHeart/TimelineDot (props)

### Session Detail (Fase 5)
- [x] Titulo, metadata, speakers: textTokens.primary
- [x] Metadata card: #FFFFFF + shadow.sm, iconos icon.tertiary
- [x] GlassCard: #FFFFFF + shadow.sm en Lux (aplica globalmente)
- [x] GlassButton: fondo #F0F2F5 + borde #E5E7EB en Lux
- [x] Favorita button: #FFF0F3 rosa cuando guardada

### Speakers (Fase 6)
- [x] Featured cards: glass (BlurView + sombra exterior + borde luminoso)
- [x] Speaker list items: #FFFFFF + shadow.sm
- [x] Speaker detail: back button, LinkedIn azul brand, Calificar gold visible, cards blancas

### Mi QR (Fase 7)
- [x] Badge card: dark island #0a0a0a solido + shadow.lg en Lux
- [x] DashedLine: colores Noir hardcodeados (independiente del tema)
- [x] Hint/wallet cards: #FFFFFF + shadow.sm

### Social (Fase 8)
- [x] PostCard: #FFFFFF + shadow.sm, autor textTokens.primary
- [x] CommentsSheet: titulo + input adaptados

### Sponsors (Fase 9)
- [x] Brand wall: cards blancas + shadow.sm (sin BlurView/gradient en Lux)
- [x] Sponsor detail: back button solido, textos ink, links visibles
- [x] Session cards unificadas: hora + type badge + titulo + location + chevron

### Networking (Fase 10)
- [x] Suggestion cards: glass effect (igual que speakers featured)
- [x] Directory/contact/request cards: #FFFFFF + shadow.sm
- [x] Attendee detail: back button, sesiones unificadas, cards blancas
- [x] "Ignorar" en solicitudes: textTokens.secondary (visible)

### Session Types configurables
- [x] Backend: tabla session_types (event_id, name, slug, color, order)
- [x] Backend: FK session_type_id en event_sessions, migracion datos existentes
- [x] Filament: SessionTypeResource con ColorPicker
- [x] API: session_type como {name, color} en agenda, speakers, sponsors, attendee
- [x] App: badges leen color de API, zero TYPE_COLORS hardcodeado
- [x] 5 tipos default creados, 32 sesiones migradas

### Commits sesion 2026-04-17
- Backend: ~8 commits (tokens, session_types, API updates)
- App: ~15 commits (Fases 0-10, perf, session_types)
- Docs: roadmap Lux v2, completado, pendientes

## Tareas completadas previas (movidas de PENDIENTES 2026-04-17)

- [x] Calendar sync boton visible — boton .ics en session detail (2026-04-16)
- [x] SEC-6.1: Rate limit networking — 100/evento, 30/dia. 3 tests (2026-04-15)
- [x] SEC-6.2: Rate limit endpoints escritura — Trait ChecksRateLimit, 23 tests (2026-04-15)
- [x] Fix Reanimated warning — StatCard entering separado (2026-04-16)
- [x] Mensaje anclado chat Twitch — socket pin/unpin, Redis TTL, chat monitor (2026-04-16)
- [x] Componentes base + Theme tokens — GlassCard, GlassButton, etc (2026-04-16)
- [x] Setup wizard evento Filament — 5 pasos, modulos, auto-crea (2026-04-15)
- [x] Dev build + Push — probadas en dev build fisico (2026-04-15)
- [x] Push reminders configurables — windows dinamicos, toggle, 19 tests (2026-04-15)
- [x] Calendar invite .ics en email — WelcomeMail adjunta .ics (2026-04-16)
- [x] ModuleMenu: elevation removida en Lux, borde sutil (evita frame negro en transiciones) (2026-04-17)
- [x] TabScreenWrapper/ScreenWrapper: opacity removida (evita flash elevation Android) (2026-04-17)
- [x] BottomSheet: prop forceNoir para gamificacion (2026-04-17)
- [x] Backend Filament Light Mode: migration default_theme + primary_color_light, Select tema + ColorPicker, API branding + onboarding (2026-04-17)
- [x] Tema pre-auth: default_theme aplicado en queryFn de useOnboarding antes del render (2026-04-17)
- [x] primary_color_light: accent adaptativo por tema en themeStore (2026-04-17)

## Mission Control v4 — Display + Metricas + Moderacion (2026-04-19)

### Display LED session-level
- [x] Ruta `/display/session/{id}?token=HMAC` — pagina publica Lumina Noir
- [x] Socket RT: `display:project` / `display:stop` con persistencia Redis (TTL 4h, sobrevive refresh)
- [x] Render polls: multiple choice (barras + ranking + counter animado), star rating (track + counter), open text (cola 1.8s)
- [x] Render Q&A: pregunta alineada izquierda, autor bottom-right, slide-in animation
- [x] Fade suave entre proyecciones, standby con breathing animation
- [x] Boton copiar enlace HMAC en sidebar MC (fallback execCommand para HTTP)

### Metricas en vivo
- [x] Redis `INCR chat:count:session:{id}` por mensaje de chat
- [x] `session:audience` broadcast debounce 500ms en join/leave (fetchSockets count)
- [x] `session:metrics` event al join con chat count persistido
- [x] Engagement: (mensajes + preguntas) / audiencia * 100, client-side cada 5s
- [x] MPM (messages per minute) con ventana deslizante 60s

### Moderacion open text
- [x] Migracion `is_approved` en live_poll_votes (default false para open_text)
- [x] Modal de moderacion en MC con aprobar/rechazar individual
- [x] Endpoint batch `POST /admin/polls/votes/approve-batch` (1 request, sin rate limit)
- [x] Display solo muestra respuestas aprobadas
- [x] Cola de presentacion: respuestas salen 1 cada 1.8s aunque se aprueben de golpe

### Q&A proyectable
- [x] Boton Proyectar en preguntas aprobadas/respondidas del tab Q&A
- [x] Display: pregunta grande alineada izquierda, autor small bottom-right

### Mejoras MC
- [x] Timeline persistente en localStorage por sesion
- [x] YouTube iframe: pointer-events passthrough, controles accesibles
- [x] Poll cards: update individual sin re-render de toda la lista
- [x] Animaciones: bars con deferred rAF (0→target), cards sin reset en updates

### Herramientas moderador (2026-04-19b)
- [x] Reloj real en header (hora actual, reemplaza elapsed timer)
- [x] Countdown sesion: verde/amber(<5min)/rojo(pasado)
- [x] Mini agenda sidebar: sesiones del dia, actual resaltada, pasadas gris
- [x] Tareas: checkbox list con localStorage, add/toggle/remove, word-break completo
- [x] Responsive: sidebar drawer <1200px, stack <1024px, compacto <768px
- [x] Audiencia: cuenta usuarios unicos, excluye admin/moderator/organizer

### Testing
- [x] DisplayTestSeeder: 3 tipos de poll con 10 votos cada uno
- [x] SimulateVotes command: `php artisan app:simulate-votes {pollId} --count=20 --delay=2`
- [x] simulate-audience.cjs: 50 conexiones socket simultaneas
- [x] 13 bugs encontrados y corregidos (BUG-135 a BUG-147)

### Totales acumulados 2026-04-19
- Backend: 488+ tests, 1168+ assertions, 0 fallos
- Features completados: campos unificados, staff invite, registro cerrado, login lockout, encuesta post-evento, FAQ, soporte completo, push navigation, SEC-6.2 rate limits, push reminders configurables, agregar todas al calendario, .ics en email bienvenida, mensaje anclado chat, componentes base, Light Mode Lux v2, Mission Control v4 display+metricas+moderacion+herramientas moderador
- Bugs: BUG-001 a BUG-147 registrados, 145+ resueltos

---

## 2026-04-20 — Quick Wins + Stand Stats + Rendimiento

### Quick wins
- [x] Health check endpoint: DB + Redis + Queue en /api/v1/health
- [x] Permisos granulares Filament: HasResourcePermission trait, 41 recursos, canAccessPanel gate (super_admin/org_admin/event_admin/moderator), 10 permisos mapeados

### Stand Stats + Contacts
- [x] GET /me/stand/stats: leads, views, favorites, contacts, stamps, trivia, by_tier, by_member, top_services
- [x] GET /me/stand/contacts: solicitudes de contacto con attendee info completa
- [x] App stand-stats.tsx: engagement unificado, tier bars, ranking equipo, servicios, export, pull-to-refresh
- [x] App stand-contacts.tsx: inbox solicitudes con Llamar/Email/WhatsApp
- [x] Mi Stand simplificado: 3 stats + hero + FAB (eliminados duplicados)
- [x] CSV export con resumen stats
- [x] StandStatsSeeder + 13 tests (49 assertions)

### Rendimiento
- [x] Polling eliminado: encuestas (30s), gamification (30s+15s), passport (15s)
- [x] Invalidacion targeted: broadcastToAttendee() para gamification/passport
- [x] Leaderboard: staleTime 60s + refetchOnWindowFocus

### Bug fixes (BUG-148 a BUG-154)
- [x] BUG-148: reload Expo → onboarding (isHydrated guard)
- [x] BUG-149: push ban sin sesion (onboarding_seen + activated_at + activeBan)
- [x] BUG-150: session detail UI hardcodeada (GlassCard/GlassButton/tokens)
- [x] BUG-151: polling innecesario (3 refetchInterval eliminados)
- [x] BUG-152: resolveAvatarUrl firma incorrecta
- [x] BUG-153: handleRefresh sin try/finally
- [x] BUG-154: BanTest sin activated_at

### QA hallazgos resueltos
- [x] QA-01: colores hardcodeados trending → constante STATUS_COLORS
- [x] QA-02: require('expo-router') dinamico → import estatico
- [x] QA-03: debounceTimers leak → cleanup clearTimeout en disconnect

### Session Stats + Attendance Tracking
- [x] Migracion: tabla `session_attendances` (session_id, attendee_id, source, joined_at, left_at, duration_seconds)
- [x] Migracion: 6 campos config en events (silent_disco_enabled, attendance_min_minutes, attendance_points, attendance_bonus_full, certificate_min_sessions, certificate_min_duration_pct)
- [x] SessionStatsService: attendance, chat, Q&A, polls, ratings, engagement score 0-100, activity timeline, attendance detail
- [x] Socket: Redis SADD/HSET en join:session, SREM/HSET en leave:session (tracking asistencia RT)
- [x] FlushSessionAttendanceJob: cron cada 60s Redis → DB (batch upsert)
- [x] AwardSessionAttendancePointsJob: puntos al finalizar sesion (min minutos + bonus >90%)
- [x] API: GET /sessions/{id}/stats, GET /sessions/{id}/viewers, GET /sessions/{id}/export
- [x] ExportSessionStatsJob: CSV en background con notificacion Filament (campana + boton descargar)
- [x] Filament ViewSessionStats: pagina resumen por sesion con boton "Exportar CSV"
- [x] Filament GamificationSettings: toggle silent disco + config asistencia/certificados
- [x] SessionStatsSeeder: 50 usuarios simulados con asistencia, chat, Q&A, polls, ratings
- [x] 11 tests session stats (59 assertions)

### Totales acumulados 2026-04-20b
- Backend: 537+ tests, 1377+ assertions, 0 fallos
- Bugs: BUG-001 a BUG-154 registrados, 154+ resueltos (+ 3 QA)

---

## Room Check-in System — Salones con Totems (2026-04-20c)

> Adaptacion completa del sistema Checki (PHP vanilla) a EventOS Laravel.
> 7 commits backend, 3 commits kiosko. 17 tests, 216+ assertions.

### Migraciones
- [x] `event_rooms`: salones del evento (name, capacity, checkin_enabled, is_active)
- [x] `room_totems`: tablets/iPads en puertas (name, type entrada/salida/bidireccional, auth_token 64 chars, last_heartbeat_at)
- [x] `room_movements`: movimientos inmutables (type checkin/checkout, scanned_at servidor, device_timestamp referencia, method qr_scan/manual/auto_room_change/auto_end_day/auto_end_session, flags JSON)
- [x] `room_attendee_states`: cache estado actual attendee+room (inside/outside, reconstruible desde movements)
- [x] `attendance_checks`: triggers moderador silent disco (session_ids JSON, ttl_seconds, expires_at)
- [x] `attendance_check_responses`: confirmaciones individuales (UNIQUE check_id+attendee_id)
- [x] `event_sessions` +: room_id FK, silent_disco_group_id, actual_start_at, actual_end_at, cancelled_at
- [x] `events` +: room_checkin_enabled, room_debounce_seconds, room_auto_checkout_buffer_minutes, attendance_check_ttl, attendance_check_min_confirms

### Modelos
- [x] EventRoom, RoomTotem (auto-genera auth_token), RoomMovement, RoomAttendeeState
- [x] AttendanceCheck, AttendanceCheckResponse
- [x] EventSession: effectiveStart(), effectiveEnd(), isCancelled()

### Services
- [x] RoomCheckinService: processScan() 10 pasos (resolve QR, verify ban/checkin, toggle state, auto-checkout otro salon, debounce, lock, create movement, update state, broadcast socket)
- [x] Cache layer: getStateFromCache, getOtherRoomInside, setCacheState, resolveTotem, cacheTotem (Cache facade con fallback DB)
- [x] RoomAttendanceCalculator: calculate() por evento/room, effectiveStart/End, skip cancelled, overlap rule (primera sesion gana), buffer 15min pre-inicio, buildIntervals, getAttendeesInsideAt
- [x] Batch offline: processScan con device_timestamp + flag cola_offline

### Controller — RoomCheckinController
- [x] POST /api/v1/rooms/scan — escaneo QR totem (auth X-Totem-Token)
- [x] POST /api/v1/rooms/scan/batch — sync cola offline (array scans con device_timestamp)
- [x] GET /api/v1/rooms/ping — heartbeat + schedule completo salon (sesiones con status live/ended/upcoming)
- [x] GET /events/rooms/{eventId}/occupancy — aforo RT por salon (admin)
- [x] GET /events/rooms/{roomId}/attendees — lista asistentes dentro (admin)
- [x] POST /events/attendance-checks/trigger — admin dispara check silent disco (push + socket a quienes estan DENTRO del salon)
- [x] POST /events/attendance-checks/{id}/confirm — attendee confirma sesion (valida TTL, valida inside room, idempotente, award points)
- [x] GET /events/attendance-checks/pending — check activo sin responder (para app al abrir desde push)
- [x] GET /events/attendance-checks/{id}/results — resultados por sesion (admin)
- [x] GET /events/attendance-checks/report — reporte final mayoria (admin, entregable sponsor)

### Controller — SessionConfigController (lifecycle)
- [x] POST /admin/sessions/{id}/start — marca actual_start_at, broadcast session:started
- [x] POST /admin/sessions/{id}/end — marca actual_end_at, broadcast session:ended
- [x] POST /admin/sessions/{id}/cancel — marca cancelled_at, broadcast session:cancelled
- [x] POST /admin/sessions/{id}/delay — retrasa agenda salon X minutos (todas las futuras)

### Jobs / Cron
- [x] AutoCheckoutEndOfDayJob: diario 23:30, checkout todos los "inside" con flag inferido
- [x] SmartAutoCheckoutJob: cada 15min, si salon sin sesion activa ni proxima (buffer configurable) → auto-checkout
- [x] SmartAutoCheckoutJob: auto-cierra sesiones sin "End" (actual_start_at set pero actual_end_at null, 30min despues de end_datetime)
- [x] ExportRoomAttendanceJob: CSV con nombre, email, empresa, telefono, minutos, % asistencia, calidad dato

### Filament Admin
- [x] EventRoomResource: CRUD salones (event, name, capacity, toggle checkin, activo)
- [x] RoomTotemResource: CRUD totems (evento, salon, nombre, tipo, IP, token copiable, last heartbeat)
- [x] EventSessionResource: Select room_id + TextInput silent_disco_group_id

### Mission Control
- [x] Tab "Control" (5ta tab, tecla 5): botones Start/End/Cancel/Delay con modal confirmacion
- [x] Estado visual sesion (Programada/En vivo/Finalizada/Cancelada)
- [x] Boton attendance check (solo visible si sesion tiene silent_disco_group_id)
- [x] Countdown + resultados RT por sesion via socket attendance:check:update
- [x] MC_CONFIG inyecta actual_start_at, actual_end_at, cancelled_at
- [x] attendance-check.html: pagina standalone para trigger + resultados (alternativa al boton en MC)

### Kiosko Web (eventos-kiosko, modo room)
- [x] Modo activado por URL: ?mode=room&totem_token=XXX
- [x] Camara oculta siempre activa (sin viewfinder, detecta QR en cualquier parte del frame)
- [x] Cooldown 5s: mismo QR no se reprocesa, previene loop infinito
- [x] Offline queue: IndexedDB almacena scans cuando servidor no responde
- [x] Batch sync: al reconectar, envia cola en orden cronologico con device_timestamp
- [x] Heartbeat ping cada 10s: devuelve schedule completo del salon (status live/ended/upcoming)
- [x] Socket listeners: session:started, session:ended, session:cancelled, agenda:updated → re-ping
- [x] Resultado 4s: checkin (verde), checkout (azul), error (rojo), offline (amber) → auto-regresa
- [x] room-api.ts, offline-queue.ts, useRoomTotem.ts, RoomApp.tsx

### CORS / Routing
- [x] X-Totem-Token agregado a allowed_headers
- [x] Puertos 5173/5174/5175 en allowed_origins
- [x] Rutas totem en web.php (bypass Sanctum statefulApi que bloqueaba desde browser)

### Tests
- [x] RoomCheckinTest: 16 tests, 48 assertions (scan, checkout, auto-checkout, debounce, banned, unauthorized, ping, trigger, confirm, expired, idempotent, not-in-room, pending)
- [x] RoomStressTest: 1 test, 168 assertions (50 personas, 17 escenarios: normal, late start, late arrival, break, no checkout, room change, cancelled, extended, lunch auto-checkout, silent disco, debounce, banned, no event checkin, no end press, delay, offline batch, saturation)

### Totales acumulados 2026-04-20c
- Backend: 553+ tests, 1593+ assertions
- Kiosko: build exitoso, funcional en localhost

---

## 2026-04-21 — Kiosk Fases 3-4 + Staff App + Session Lifecycle + Bug Audit

### Kiosk Roadmap — Fases completadas
- [x] **Fase 0** — Demo HTML hibrido (standalone) — COMPLETADA
- [x] **Fase 1** — Implementar en React (Kiosko) — COMPLETADA (28 items, solo falta verificar scan <100ms en VPS)
- [x] **Fase 2** — Mission Control navegacion — COMPLETADA (sidebar agenda, HMAC nav, tracks)
- [x] **Fase 3** — Silent disco UI — COMPLETADA (MC tab asistencia, app modal, TTL, CSV, socket RT; push pendiente dev build)
- [x] **Fase 4** — App movil staff_checkin — COMPLETADA (backend 10 tests, app hub+scanner+assignment, socket RT; cola offline nice-to-have)

### Staff App (sesion 2026-04-21b)
- [x] StaffHappeningNow card en home
- [x] ModuleMenu: modulo staff_checkin visible solo para rol staff_checkin
- [x] Scanner staff con room picker + resultado BottomSheet
- [x] Filament: assign staff desde admin panel
- [x] Fix: roomCheckinApi usaba `api` como callable (BUG-165)

### Session Lifecycle — 8 bugs criticos corregidos (sesion 2026-04-21c)
- [x] **BUG-175 CRITICO**: Carbon mutation en adjustNextSession() — siguiente sesion quedaba con duracion 0 min (start=end). Fix: `->copy()->addMinutes()`
- [x] **BUG-176 ALTO**: .ics calendario usaba end_datetime en vez de publicEnd() — usuario descargaba hora sin delay. Fix: `publicEnd()`
- [x] **BUG-177 ALTO**: Delay movia sesiones ya iniciadas. Fix: `whereNull('actual_start_at')`
- [x] **BUG-178 ALTO**: start() permitia iniciar sesion finalizada/cancelada. Fix: guards ended + cancelled
- [x] **BUG-179 ALTO**: cancel() no revertia delay en siguiente sesion. Fix: revert logic + clear adjusted_end_at
- [x] **BUG-180 ALTO**: Agenda RT no actualizaba horarios (titulo si, start/end no). Fix: MMKV cache clear + FlashList extraData + mi-agenda invalidation
- [x] **BUG-181 MEDIO**: MC segundo moderador no recibia timer correcto. Fix: startLiveTimer/stopLiveTimer en socket listeners
- [x] **BUG-182 BAJO**: Push delay incluia sesiones pasadas. Fix: filtro `>= now()-2h`
- [x] 5 tests nuevos: preserva duracion, no mueve started, rechaza ended/cancelled, cancel revierte delay
- [x] **BUG-183**: Kiosk ping ignoraba adjusted_end_at — totem mostraba hora sin delay. Fix: `publicEnd()` en RoomCheckinController:95
- [x] **MC cronometro verificado**: elapsed desde actual_start_at server, remaining contra SESSION_END (actualizado por delay), warning amber <5min, sync socket entre MCs

### MC Session Lifecycle — Control tab completado
- [x] Botones Start/End/Cancel con modal confirmacion
- [x] Delay +5/+10/+15/+30 con cascade a siguiente sesion
- [x] Cronometro "En vivo — X min" con warning <5 min restantes
- [x] Timeline persistente en localStorage
- [x] Socket sync entre multiples MCs (started/ended/cancelled)
- [x] Estado inicial desde MC_CONFIG (actual_start_at, actual_end_at, cancelled_at)

### QA Pre-produccion documentado
- [x] PLAN-STRESS-TEST.md ampliado: QA de integridad funcional + 4 smoke tests E2E + chaos testing (6 escenarios) + calendario pre-prod semana -8 a dia D

---

## Webhooks Integracion Partners — COMPLETADO (2026-04-21)

> P0 non-negotiable. Bancolombia usa empresas externas para badges.
> Roadmap: docs/ROADMAP-WEBHOOKS.md

### Fase 1 — Modelo de datos
- [x] 3 migraciones: webhook_endpoints, webhook_api_keys, webhook_logs
- [x] 3 modelos: auto-genera secret (64 chars), key con prefijo wh_live_ (48 chars), log inmutable
- [x] Helpers: listensTo(), signPayload() HMAC-SHA256, hasPermission(), isSuccess()

### Fase 2 — Outbound (EventOS → Partner)
- [x] WebhookDispatchService: busca endpoints activos, filtra por evento suscrito, filtra campos seleccionados
- [x] DispatchWebhookJob: HTTP POST con HMAC signature, retry 3x (1min/5min/30min), respeta 429 Retry-After
- [x] AttendeeWebhookObserver: dispara en created (registered), updated (approved/checked_in/profile), deleted (cancelled)
- [x] Idempotency key ULID en cada payload
- [x] Test mode: payloads con `"test": true`
- [x] needs_attention: marca endpoint tras 3 fallos consecutivos

### Fase 3 — Inbound (Partner → EventOS)
- [x] WebhookInboundController: check-in individual y batch (max 100)
- [x] Auth por header X-Webhook-Key (sin Sanctum)
- [x] Rate limit atomico por key (configurable, default 100/hora), Retry-After header en 429
- [x] Check-in por email o por attendee_id
- [x] Test mode: no marca checked_in_at, responde would_check_in
- [x] Idempotente: re-checkin devuelve already_checked_in
- [x] Validaciones: banned (403), not found (404), inactive key (401), sin permiso (403)
- [x] Batch transaccional (DB::transaction)

### Fase 4 — Filament Admin
- [x] WebhookEndpointResource: CRUD + acciones (Enviar prueba, Copiar secret, Reenviar fallidos, Descargar spec)
- [x] WebhookApiKeyResource: CRUD + accion Regenerar key
- [x] WebhookLogResource: read-only, filtros direction/status/test, detalle payload+response
- [x] WebhookStatsWidget: enviados hoy, check-ins recibidos, tasa de exito
- [x] Boton "Simular evento": 5 registros + 3 check-ins fake al partner
- [x] Boton "Descargar spec": TXT con URLs, keys, payload ejemplo, codigos respuesta, verificacion HMAC

### Fase 5 — Operaciones
- [x] PruneWebhookLogsCommand: `webhook:prune-logs --days=90` en cron diario 2am
- [x] TestWebhooksCommand: `app:test-webhooks` — 19 checks automaticos (setup + outbound + inbound + logs)

### Bugs post-audit corregidos
- [x] Rate limit race condition: Cache::get+increment → increment atomico
- [x] Retry-After header faltante en 429
- [x] Batch sin transaccion → DB::transaction
- [x] Sin idempotency key → ULID por dispatch
- [x] Logs sin cleanup → prune command cron

### Totales
- 24 tests unitarios, 60 assertions
- 19 checks en test command
- 3 Filament resources, 1 widget, 6 acciones
- 0 bugs pendientes

### Totales acumulados 2026-04-21
- Backend: 582+ tests, 1664+ assertions
- Kiosk roadmap: 4 de 4 fases completadas
- Webhooks: COMPLETADO (P0 cerrado)

---

## 2026-04-22/23 — Live Moments (Juegos en Vivo) COMPLETADO

> Roadmap completo: docs/ROADMAP-LIVE-MOMENTS.md v2.5
> Bugs: ver BUG-LOG.md (numeracion reorganizada, BUG-177 a BUG-232)

### Fase 1 — Backend + MC base (2026-04-21/22)
- [x] LiveGame + LiveGameParticipant models, migrations, relations
- [x] GameService: pool RT (presencial room + virtual socket), weightedRandom, broadcast, cooldown
- [x] CRUD + launch + spin + draw + next-question + close-round + answer endpoints
- [x] MC tab Games: drafts, launch, historial, eligible RT
- [x] Display spin: spin-wheel lib, idle, control btn, auto-standby
- [x] Display jackpot: photo cascade ceremony GSAP, shockwave, confetti
- [x] MasterSeeder: 50 attendees, 25 check-in, 12 games

### Fase 2 — Sorteo Ceremony + Golden Ticket (2026-04-22)
- [x] Rewrite slot machine → Photo Cascade Ceremony (strip vertical GSAP power4.out)
- [x] Golden Ticket: claim_code 6 chars, /me/prizes, Filament PrizeRedemptions page
- [x] AnnouncementController: publicos cacheados + privados solo al target
- [x] App: useMyPrizes hook, AnnouncementCard tappable deep link
- [x] 15 bugs resueltos (pool RT, race conditions, ceremony visual)

### Fase 3 — App integration (2026-04-22)
- [x] Socket listeners: game:launched, game:result, game:question, game:round-result, game:finished
- [x] Toast info/success, invalidacion my-points/my-prizes/announcements
- [x] Push al ganador jackpot con claim_code + deep link

### Fase 4 — Trivia Kahoot-style (2026-04-23)
- [x] Backend: next-question (broadcast sin respuesta), close-round (distribucion, leaderboard, auto-finish)
- [x] Answer: scoring correcta + speed bonus, previene doble (unique constraint)
- [x] MC tab Trivia: 4 estados (draft, lobby, question activa, resultado, podio)
- [x] Display LED: pregunta+opciones, barras distribucion RT, leaderboard, podio
- [x] App TriviaPanel: countdown, opciones, feedback correcto/incorrecto, mini leaderboard, podio
- [x] useTriviaStore zustand: 4 fases (idle, question, result, finished)
- [x] 15 bugs resueltos (activePanel critico, countdown, toast labels, MC handlers)

### Fase 5 — Performance + Export + Branded + Platinum Gold (2026-04-23)
- [x] ProcessSpinRewardsJob: bulk upsert + insertOrIgnore (20K queries → ~10)
- [x] ProcessTriviaRewardsJob: bulk points award async
- [x] points_log unique constraint migration
- [x] Trivia CSV export (ronda, pregunta, respuesta, tiempo, score)
- [x] Filament page LiveGameResults con export por juego
- [x] Branded moments: sponsor en MC projections, display, app toast + TriviaPanel
- [x] Platinum Gold #B5A68B unificado (3 repos, ~15 archivos)
- [x] MC metrics elevation (box-shadow, sin barras color generico)
- [x] Golden Ticket rediseno "Award Certificate" (linea accent, trophy, QR limpio)
- [x] AgendaScreen Evaluar + PollSlides star_rating colores corregidos
- [x] 6 bugs resueltos (10K spin, 10K trivia, answer race condition, points_log unique, Redis flushdb)

### BUG-LOG reorganizado (2026-04-23)
- [x] Renumerado BUG-001 a BUG-232, cero duplicados (antes habia ~15 duplicados)
- [x] 210+ bugs total, 208+ resueltos, 2 pendientes

### Totales acumulados 2026-04-23
- Backend: 582+ tests, 1664+ assertions (41 Game tests, 172 assertions)
- Live Moments: COMPLETADO (5 fases, 35+ bugs resueltos)
- Bugs: BUG-001 a BUG-232, 208+ resueltos, 2 pendientes (BUG-111, BUG-127)

## Data Center modulo (2026-04-25 al 2026-04-26) — 6 iteraciones, ~25h

### SPA standalone con 9 tabs analytics
- [x] F0-F7 backend: 44 export jobs en app/Jobs/Exports/, BaseExportJob, ExportService
- [x] Queue separado 'exports' para aislamiento de evento en vivo (10K activos)
- [x] DataCenterController + Sanctum stateful auth (session cookie Filament)
- [x] SPA en public/data-center/ — index.html + assets/app.js + assets/app.css
- [x] 9 tabs enriquecidas: Asistentes, Sesiones, Engagement, Sponsors, Gamificacion, Social, Networking, Comunicaciones, Auditoria

### Bloques A/B/C/D enterprise
- [x] A1 Sub-filtros extendidos (chat_by_hour por session_id)
- [x] A2 Cache invalidation con DataCenterCacheObserver
- [x] A3 Polling notifs adaptativo (pausa tras 3 polls vacios)
- [x] B1 Tooltips en graficas (title SVG en donuts)
- [x] B2 Chip "Actualizado hace Xs" en header
- [x] B3 Sparkline en card Check-ins con checkins_by_hour
- [x] B4 Empty states con microcopy especifica
- [x] B5 Loading granular fade
- [x] C1 Comparativa entre periodos (30d vs 30d, capped 150%, +N fallback)
- [x] C2 Goals/targets con UI modal CRUD + barras progreso bajo hero cards
- [x] C3 Scheduled exports con cron processor + modal UI + lista
- [x] C4 Embed publico backend listo (UI descartada por redundante con Event Pulse)
- [x] C5 Multi-evento dashboard (UI en empty state + tabla comparativa)
- [x] D1 Comparador A/B sesiones (2 selects + tabla winner highlight)
- [x] D2 Export "vista actual" hereda filtros del tab activo

### Tema Lux v2 + filtros + XLSX
- [x] Tema Lux con toggle, localStorage persiste sin flash
- [x] Filtros reactivos: date range global + sub-entidad por tab
- [x] XLSX real con maatwebsite/excel — generateXlsx() en BaseExportJob
- [x] Cards con shadow en lugar de border (regla Lux)

### Infra produccion lista
- [x] docker/Dockerfile.worker + docker/docker-compose.worker.yml
- [x] .env.worker.example completo (replica + R2 + VPC Redis)
- [x] config/database.php read/write split condicional
- [x] SANCTUM_STATEFUL_DOMAINS en .env.example
- [x] routes/web.php fallback /data-center/{any?} con auth + role check

### 5 migraciones nuevas
- [x] dc_event_goals, dc_scheduled_exports, dc_embed_tokens
- [x] /dc-embed/{token} ruta publica con embed.html standalone

### QA: 29 tests nuevos (100% passing)
- [x] DataCenterFiltersTest — 7 tests
- [x] DataCenterFeaturesTest — 14 tests
- [x] DataCenterCacheObserverTest — 4 tests
- [x] BaseExportJobXlsxTest — 3 tests
- [x] phpunit.xml: memory_limit 2048M
- [x] Suite global: 769/772 passed (3 fallos pre-existentes flaky no relacionados)

### Seeder demo
- [x] DataCenterDemoSeeder con pravatar avatars, clearbit logos, 54 attendees,
      280 session_attendances, 360 push_logs, 80 webhook_logs, 58 questions,
      90 audit_log, 8 bans, 12 role changes

## Mission Control bugs cierre (2026-04-26) — 15 bugs + ruleta + brand

### Bugs criticos (2)
- [x] BUG-291: Token 12h expira sin refresh
- [x] BUG-292: Listeners socket duplicados

### Bugs altos (5)
- [x] BUG-293: Memory leaks timers
- [x] BUG-294: 50+ catches silenciosos
- [x] BUG-295: Race conditions modales
- [x] BUG-296: Socket no rejoinea reconnect
- [x] BUG-287: ExportService HAVING sin GROUP BY

### Bugs medios (5)
- [x] BUG-288: HOUR() no portable SQLite
- [x] BUG-297: localStorage sin validar tipo
- [x] BUG-298: Doble-click games (lockButton 6 callsites)
- [x] BUG-299: Progress bar attendance imprecisa
- [x] BUG-290: Hero card delta % absurdo

### Bugs bajos (5)
- [x] BUG-289: Schedule::call name() antes withoutOverlapping
- [x] BUG-300: Substring tras escape rompe HTML entities
- [x] BUG-301: ESC no cierra modales
- [x] BUG-302: Z-index conflicts
- [x] BUG-303: Render error boundary

### Polish UI
- [x] BUG-304: Status bar Socket.IO casi invisible
- [x] BUG-305: Ruleta resultado antes de fin animacion
- [x] Brand "MISSION CONTROL" estilo unificado con Data Center (mc-brand-mark eliminado)

### Nuevos endpoints + cron
- [x] /data-center/{event}/goals (GET, POST, DELETE/{metric})
- [x] /data-center/{event}/scheduled (GET, POST, DELETE/{id})
- [x] /data-center/{event}/embeds (GET, POST, DELETE/{token})
- [x] /data-center/multi?event_ids= (multi-evento comparativa)
- [x] /data-center/{event}/notifications/read-all (POST), /clear (DELETE)
- [x] Cron Schedule::call cada 5min procesa dc_scheduled_exports

### Totales acumulados 2026-04-26
- Backend: 611+ tests (582 + 29 nuevos), 1735+ assertions
- Data Center: COMPLETADO (6 iteraciones, ~25h)
- Mission Control: 15/15 bugs + ruleta + brand unificado
- Bugs: BUG-001 a BUG-305, 226+ resueltos, 2 pendientes (BUG-111, BUG-127)
