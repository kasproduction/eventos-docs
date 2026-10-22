# Inventario de acciones del admin (Filament)

Fuente: lectura del codigo de `eventos-backend` (app/Filament, servicios, jobs y observers que tocan), 69 apariciones de `->action(` / `->using(` en 29 archivos. Fecha 2026-10-22.
Tarea M0.5.3 de ROADMAP-MIGRACION-DOTNET. Rutas relativas a la raiz del repo backend.

> **Verificados a mano (2026-10-22) y en BUG-LOG:** hallazgo del push de anuncio privado (BUG-372), ban desde Filament
> sin expulsar del socket (BUG-373), eventos de todas las organizaciones visibles (BUG-374). El resto de los hallazgos
> de abajo los encontro un agente al leer el codigo: **por verificar** en la auditoria de seguridad.

Convenciones:
- "Permiso del recurso" = `HasResourcePermission::canAccess()` (app/Filament/Traits/HasResourcePermission.php): super_admin entra siempre; el resto necesita el permiso Spatie indicado. No hay Policies (no existe app/Policies) ni `->authorize()` en ninguna accion.
- "Evento activo" = `EventContext::id()`, que sale de la sesion (`session('filament_event_id')`).
- Efectos de observers: `AnnouncementObserver` (borra `event:{id}:announcements:{attendee|vendedor}` y llama `InvalidationService::sync` o `broadcast` 'announcements'), `AttendeeWebhookObserver` (webhooks `attendee.*` via `WebhookDispatchService` -> `DispatchWebhookJob`), `DataCenterCacheObserver` (`Cache::increment("dc:stats:{eventId}:ver")`, para Attendee, EventSession, Sponsor, Lead, AttendeeBan, AttendeeRoleChange, WallPost, EventPhoto, LiveGame), `EventObserver` -> app/Services/EventEffects.php, `EventSessionObserver` -> app/Services/SessionEffects.php, `EventRoomObserver`, `ContentObserver`. Registro en app/Providers/AppServiceProvider.php:127-162.
- `InvalidationService::broadcast(evento, entidad)` (app/Services/InvalidationService.php) hace: POST a la webapp `{webapp}/api/internal/invalidate` (solo entidades del marco webapp), PUBLISH en Redis canal `eventos:invalidate`, y POST al socket `{socket}/internal/data/invalidate` con freno de 1 s en la clave Redis `invalidate:{evento}:{entidad}`. `sync` hace lo mismo pero manda la pieza a `{socket}/internal/data/sync` (y si falla cae a `broadcast`).
- `PointsService::tryAward` (app/Services/PointsService.php:120) crea `PointsLog` si la accion esta activa y no repetida, y llama `InvalidationService::broadcastToAttendee(..., 'gamification')` (POST al socket) y `broadcast(evento, 'leaderboard')`.
- `SendPushToAttendeeJob::toAttendee` despacha `SendPushToAttendeeJob` (Expo, POST a exp.host) y `SendWebPushJob` por cada suscripcion web. `dispatchToAttendees` ademas crea un `PushNotificationLog` por asistente.
- `SendEmailJob` solo envia si la plantilla del correo esta activa; usa el SMTP de la organizacion si esta listo (`EmailService::mailerFor`).
- `AdminAuditLog::record` crea una fila en `admin_audit_logs`. Desde Filament nunca se le pasa la IP (la API si).

## Paginas sueltas (LiveGameResults, PrizeRedemptions)

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Pages/LiveGameResults.php:127 `export` | Fila de tabla | Descarga un CSV con los resultados de un juego terminado (ruleta, sorteo o trivia). | Solo lectura. CSV en streaming con BOM UTF-8 y celdas saneadas contra formulas (`=`, `+`, `-`, `@`). Columnas distintas por tipo de juego. | Visible si `status = finished`. Pagina sin permiso propio: cualquier usuario del panel. Sin confirmacion. |
| app/Filament/Pages/PrizeRedemptions.php:106 `confirm` | Fila de tabla | Marca un premio como reclamado. | `RewardService::confirm` (app/Services/RewardService.php:102): transaccion con bloqueo de fila, exige `status = pending` y no vencido (404/410 si no), pone `status = confirmed`, `confirmed_at`, `confirmed_by`. | Visible si `status = pending`. Con confirmacion. Pagina sin permiso propio. |

## AccessCodeResource

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/AccessCodeResource/Pages/ListAccessCodes.php:50 `generate_batch` | Header de pagina | Genera de 1 a 100 codigos de acceso de una vez. | Crea N `AccessCode` (evento activo, `max_uses`, `expires_at`, `is_active = true`); reintenta si el codigo ya existe. Sin observers. | Permiso `manage-events`. Sin confirmacion. Form: cantidad 1-100. |

## AnnouncementResource

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/AnnouncementResource.php:180 `publish_now` | Boton dentro del formulario de edicion | Publica el anuncio ya y manda la push a la audiencia. | `Announcement.published_at = now` y luego `push_sent = true` (dos guardados: dispara `AnnouncementObserver` dos veces -> cache + `InvalidationService::broadcast` 'announcements'). `sendPush` (linea 273): asistentes del evento con `expo_push_token` (filtra por `roles` si hay) -> `SendPushToAttendeeJob::dispatchToAttendees` (crea `PushNotificationLog`, jobs `SendPushToAttendeeJob` y `SendWebPushJob`). | Visible si el anuncio existe y no esta publicado. Con confirmacion. Permiso `manage-content`. No mira `push_sent` (a diferencia de `publish`). |
| app/Filament/Resources/AnnouncementResource.php:323 `publish` | Fila de tabla | Publica ya; si no se habia mandado push, la manda. | Igual que arriba, pero solo llama `sendPush` si `push_sent` es falso. | Visible si `published_at` es nulo. Con confirmacion. |
| app/Filament/Resources/AnnouncementResource.php:342 `send_push` | Fila de tabla | Vuelve a mandar la push del anuncio. | `sendPush` (mismos jobs y logs) y `push_sent = true` (observer). | Visible si esta publicado. Con confirmacion. Ver hallazgo 1: aplica tambien a anuncios privados. |

## AttendeeAdminResource

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/AttendeeAdminResource.php:124 `chooseTags` | Formulario de edicion (modal del campo Tags), guarda al instante | Cambia los tags del asistente (elegir existentes o crear nuevos). | `Attendee.tags` = union limpia sin repetidos. `AttendeeWebhookObserver` -> webhook `attendee.updated` (`DispatchWebhookJob`); `DataCenterCacheObserver`. | Permiso `manage-users`. Sin confirmacion. |
| app/Filament/Resources/AttendeeAdminResource.php:248 `aprobar_registro` | Formulario de edicion (seccion Estado) | Aprueba el registro del asistente. | `approveRegistration` (linea 425): `registration_approved_at = now`, `AdminAuditLog` `approve_registration`, `SendEmailJob` con `RegistrationApprovalMail`. Observers: webhook `attendee.approved`, DataCenter. | Visible si no esta aprobado. Con confirmacion. |
| app/Filament/Resources/AttendeeAdminResource.php:289 `banear` | Formulario de edicion (seccion Acceso) | Suspende el acceso del asistente, con motivo y vencimiento opcional. | `banAttendee` (linea 449): crea `AttendeeBan`; `AdminAuditLog` `ban_attendee`; push "Acceso suspendido" (`SendPushToAttendeeJob`/`SendWebPushJob`); borra `expo_push_token` y todas las `pushSubscriptions`; `SendEmailJob` con `BannedMail`. Hook `AttendeeBan::created` (AppServiceProvider.php:129) -> `SocketAuthCache::forgetUser` (borra claves Redis `socketauth:u:{user}` y `socketauth:t:*`). DataCenter. NO llama al socket `/internal/ban/enforce` (la API si). | Visible si no hay ban activo. Con confirmacion. Vencimiento debe ser futuro. |
| app/Filament/Resources/AttendeeAdminResource.php:301 `desbanear` | Formulario de edicion (seccion Acceso) | Levanta el ban activo. | `unbanAttendee` (linea 488): `AttendeeBan.unbanned_at/unbanned_by`; `AdminAuditLog` `unban_attendee`; push "Acceso restaurado". DataCenter (AttendeeBan). | Visible si hay ban activo. Con confirmacion. |
| app/Filament/Resources/AttendeeAdminResource.php:329 `cambiar_rol` | Formulario de edicion (seccion Acciones) | Cambia el rol entre Asistente y Staff Check-in. | `Attendee.role`; crea `AttendeeRoleChange`; `AdminAuditLog` `change_role`. DataCenter (Attendee y AttendeeRoleChange). No dispara webhook (`role` no esta en los campos relevantes). | Sin confirmacion. Solo ofrece `attendee` y `staff_checkin`. Si el rol no cambia, avisa y no hace nada. |
| app/Filament/Resources/AttendeeAdminResource.php:378 `asignar_salon` | Formulario de edicion (seccion Acciones) | Asigna un salon a un staff de check-in. | `StaffRoomAssignment::updateOrCreate` (asistente + salon, `is_active = true`, `assigned_by` = asistente del admin o el mismo asistente). Sin observers. | Visible si `role = staff_checkin`. Sin confirmacion. Solo salones activos del evento. |
| app/Filament/Resources/AttendeeAdminResource.php:399 `regenerar_recap` | Formulario de edicion (seccion Acciones) | Pide regenerar la imagen de resumen del asistente. | Despacha `GenerateRecapImageJob(force: true)` (genera PNG, lo sube con `StorageService::uploadBytes` y actualiza el asistente); `AdminAuditLog` `regenerate_recap`. | Con confirmacion. |
| app/Filament/Resources/AttendeeAdminResource.php:606 `aprobar_registro` | Fila de tabla | Aprueba el registro desde la lista. | Igual que `approveRegistration` (correo, auditoria, webhook, DataCenter). | Visible si no esta aprobado. Con confirmacion. |
| app/Filament/Resources/AttendeeAdminResource.php:616 `aprobar_registros` | Accion masiva | Aprueba varios registros a la vez. | `approveRegistration` por cada uno no aprobado (un correo, una auditoria y un webhook por persona). | Con confirmacion. Salta los ya aprobados. |
| app/Filament/Resources/AttendeeAdminResource/Pages/ListAttendeeAdmins.php:48 `plantilla` | Header de pagina | Descarga la plantilla de importacion (xlsx o csv). | Solo lectura (`AttendeeImportTemplate`, columnas fijas + campos de registro). | Permiso `manage-users`. Sin confirmacion. |
| app/Filament/Resources/AttendeeAdminResource/Pages/ListAttendeeAdmins.php:86 `importar` | Header de pagina | Importa asistentes desde CSV o Excel y crea sus cuentas. | `AttendeesImport` (app/Imports/AttendeesImport.php), sincrono en la peticion: por fila crea `User` (si no existe, rol Spatie `attendee`), `Attendee` (`invited_via = csv`, ya aprobado), `QrToken` (HMAC), `RegistrationFieldValue`; modo invitacion: `invitation_token` + `SendEmailJob` con `InvitationMail`; modo telefono: contrasena = telefono y cuenta activada. Observers: webhook `attendee.registered`, DataCenter. Archivo queda en disco `local/imports`. | Sin confirmacion. Tipos csv, xls, xlsx. |
| app/Filament/Resources/AttendeeAdminResource/Pages/ListAttendeeAdmins.php:137 `exportar` | Header de pagina | Descarga la lista de asistentes, con filtro por rol y check-in. | Solo lectura (`AttendeesExport`). | Sin confirmacion. |

## EmailTemplateResource (EditEmail)

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/EmailTemplateResource/Pages/EditEmail.php:163 `restore` | Header de pagina | Borra la version personalizada del correo para este evento y vuelve a la del sistema. | `restoreSystem` (linea 231): borra `EmailTemplate` del evento y tipo (es y en). | Visible si hay version del evento. Con confirmacion. No hace nada si el correo es de sistema o no hay evento. Permiso `send-notifications`; la pagina exige super_admin al montar (linea 46). |
| app/Filament/Resources/EmailTemplateResource/Pages/EditEmail.php:178 `sendTest` | Header de pagina | Manda un correo de prueba con datos de muestra, incluidos cambios sin guardar. | `sendTest` (linea 243): reemplaza variables con `EmailCatalog::sampleVars`, envuelve con `EmailLayout::wrap`, `Mail::html` sincrono con asunto "[PRUEBA]". Usa el mailer por defecto, no el SMTP de la organizacion. | Sin confirmacion. Destino libre (validado como email). |

## EventPhotoResource

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/EventPhotoResource.php:175 `approve` | Fila de tabla | Aprueba una foto pendiente y le da puntos al autor. | `EventPhoto.status = approved`; `PointsService::tryAward('upload_photo')` (PointsLog + POST socket gamification y leaderboard); DataCenter. | Visible si `pending`. Sin confirmacion. Permiso `manage-content`. |
| app/Filament/Resources/EventPhotoResource.php:189 `reject` | Fila de tabla | Rechaza una foto pendiente. | `status = rejected`; DataCenter. | Visible si `pending`. Con confirmacion. |
| app/Filament/Resources/EventPhotoResource.php:210 `award_prize` | Fila de tabla | Premia la foto con un Golden Ticket y avisa al autor. | Si no estaba premiada: `RewardService::createGoldenTicket` (transaccion: crea `Reward` tipo prize oculto + `RewardRedemption` pendiente con token HMAC y codigo de 6 letras); `is_contest_winner = true`; crea `Announcement` privado (`target_attendee_id`, deeplink `eventos://gamification/rewards`) -> `AnnouncementObserver` (cache + broadcast 'announcements'). Sin push. | Visible si aprobada, con autor y no ganadora. Con confirmacion. |
| app/Filament/Resources/EventPhotoResource.php:255 `bulk_approve` | Accion masiva | Aprueba varias fotos y da puntos. | Por cada una: `status = approved` + `tryAward('upload_photo')`. No filtra por estado. | Sin confirmacion. |
| app/Filament/Resources/EventPhotoResource.php:269 `bulk_reject` | Accion masiva | Rechaza varias fotos. | `status = rejected` en cada una; DataCenter. | Con confirmacion. |
| app/Filament/Resources/EventPhotoResource.php:295 `award_top_contest` | Header de tabla | Premia las N fotos con mas likes del concurso. | Si el concurso esta activo: toma hasta `contest_max_winners` (3 por defecto) fotos aprobadas de concurso, no oficiales ni premiadas; por cada una: Golden Ticket (`createGoldenTicket`), `is_contest_winner` + `contest_rank`, puntos extra `top_photo_bonus` segun `contest_winner_points`, `Announcement` privado. | Con confirmacion. Avisa y sale si el concurso esta apagado. |
| app/Filament/Resources/EventPhotoResource.php:408 `upload_official` | Header de tabla | Sube fotos oficiales del evento, ya aprobadas. | Por archivo: `StorageService::upload` (R2 si esta configurado, si no disco public) a `events/{id}/photos`; crea `EventPhoto` (`is_official`, `approved`, sin autor). DataCenter. Los temporales en `temp-official` no se borran. | Sin confirmacion. Imagenes de hasta 10 MB. |
| app/Filament/Resources/EventPhotoResource/Pages/ViewEventPhoto.php:23 `approve` | Header de pagina (detalle) | Aprueba la foto desde el detalle. | Solo `status = approved` (sin puntos); DataCenter. | Visible si `pending`. Sin confirmacion. |
| app/Filament/Resources/EventPhotoResource/Pages/ViewEventPhoto.php:35 `reject` | Header de pagina (detalle) | Rechaza la foto desde el detalle. | `status = rejected`; DataCenter. | Visible si `pending`. Con confirmacion. |

## EventPulseResource

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/EventPulseResource.php:76 `copiar` | Formulario (boton junto al enlace) | Copia el enlace del tablero en vivo al portapapeles. | Ninguno en servidor: ejecuta JS en el navegador. | Permiso `manage-events`. Sin confirmacion. |
| app/Filament/Resources/EventPulseResource.php:98 `regenerar_token` | Formulario | Cambia el token del tablero; el enlace viejo deja de servir. | `Event.pulse_token = 'ep_' + 32 aleatorios` -> `EventObserver` (borra `event:{id}:branding` y `event:{id}:modules:*`, `InvalidationService::broadcast` 'branding'). | Visible si hay token. Con confirmacion. |

## EventResource

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/EventResource.php:70 `activar` | Fila de tabla | Cambia el evento con el que trabaja el admin. | `EventContext::set` (escribe `filament_event_id` en la sesion) y redirige a /admin. | Visible si no es el evento activo. Sin confirmacion. Permiso `manage-events`. Lista todos los eventos (sin filtro por organizacion). |

## EventRoomResource

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/EventRoomResource/Pages/ListEventRooms.php:48 `create` | Header de pagina | Crea un salon y abre su edicion. | Crea `EventRoom` (activo) -> `EventRoomObserver` (`Cache::forget event:{id}:agenda`, `InvalidationService::broadcast` 'agenda'). | Permiso `manage-content`. Sin confirmacion. Reemplaza el CreateAction estandar. |

## EventSessionResource

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/EventSessionResource.php:129 `assignSpeakers` | Formulario de edicion (modal Speakers), guarda al instante | Define que speakers dan la sesion. | `speakers()->sync(ids)` sobre la tabla pivote. No pasa por `EventSessionObserver`: no hay invalidacion de cache ni aviso a socket o webapp. | Oculto en crear. Sin confirmacion. Permiso `manage-content`. |
| app/Filament/Resources/EventSessionResource.php:381 `assign_track` | Accion masiva | Pone (o quita) el track de varias sesiones. | `track_id` en cada sesion -> `EventSessionObserver` por sesion: `SessionEffects::olvidarAgenda`, `avisarCambio` (`InvalidationService::sync` 'agenda' `session.updated` con la pieza). | Sin confirmacion. |
| app/Filament/Resources/EventSessionResource/RelationManagers/QuestionsRelationManager.php:97 `approve` | Relation manager (fila) NO REGISTRADO | Aprueba una pregunta del Q&A. | `SessionQuestion.status = approved`; POST socket `/internal/question/broadcast` evento `question:approved`. | Visible si `pending`. Sin confirmacion. Codigo muerto (ver hallazgos). |
| app/Filament/Resources/EventSessionResource/RelationManagers/QuestionsRelationManager.php:104 `answer` | Relation manager (fila) NO REGISTRADO | Marca la pregunta como respondida. | `status = answered`; POST socket `question:answered`. | Visible si `approved`. |
| app/Filament/Resources/EventSessionResource/RelationManagers/QuestionsRelationManager.php:112 `dismiss` | Relation manager (fila) NO REGISTRADO | Descarta la pregunta. | `status = dismissed`. Sin aviso al socket. | Visible si `pending`. Con confirmacion. |
| app/Filament/Resources/EventSessionResource/RelationManagers/QuestionsRelationManager.php:118 `bulk_approve` | Relation manager (masiva) NO REGISTRADO | Aprueba varias preguntas. | Igual que `approve` por cada una. | Sin confirmacion. |
| app/Filament/Resources/EventSessionResource/RelationManagers/QuestionsRelationManager.php:124 `bulk_dismiss` | Relation manager (masiva) NO REGISTRADO | Descarta varias preguntas. | Igual que `dismiss` por cada una. | Con confirmacion. |

## EventSurveyResource

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/EventSurveyResource.php:125 `activate` | Fila de tabla | Abre la encuesta para que la respondan. | `LivePoll.status = active`, `activated_at`. Sin observers ni aviso al socket. | Visible si `draft`. Con confirmacion. Permiso `view-analytics`. |
| app/Filament/Resources/EventSurveyResource.php:138 `close` | Fila de tabla | Cierra la encuesta. | `status = closed`, `closed_at`. Sin aviso. | Visible si `active`. Con confirmacion. |
| app/Filament/Resources/EventSurveyResource/Pages/EditEventSurvey.php:40 `activate` | Header de pagina | Igual que la de fila, desde la edicion. | Igual. | Visible si `draft`. Con confirmacion. |
| app/Filament/Resources/EventSurveyResource/Pages/EditEventSurvey.php:54 `close` | Header de pagina | Igual que la de fila, desde la edicion. | Igual. | Visible si `active`. Con confirmacion. |
| app/Filament/Resources/EventSurveyResource/Pages/ListEventSurveys.php:72 `create` | Header de pagina | Crea una encuesta general en borrador y abre su edicion. | Crea `LivePoll` (`scope = event`, `draft`). Aparte, al abrir la lista (`mount`, linea 32) se crea sola la encuesta post-evento con 3 preguntas si no existe. | Sin confirmacion. |

## GamificationSettingsResource

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/GamificationSettingsResource.php:252 `editAction` | Formulario (tarjeta por accion de puntos), guarda al instante | Edita una regla de puntos: activa, nombre, puntos, maximo diario y roles. | Mezcla la clave en `Event.gamification_config` (solo claves del catalogo) -> `EventObserver` (cache branding/modulos + broadcast 'branding'). | Permiso `manage-events`. Sin confirmacion. Ignora claves desconocidas. |

## GoldenTicketResource

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/GoldenTicketResource.php:126 `mark_claimed` | Fila de tabla | Marca un Golden Ticket como reclamado. | Update directo `RewardRedemption.status = confirmed`, `confirmed_at`. No usa `RewardService::confirm`: no guarda `confirmed_by` ni bloquea la fila. | Visible si `pending`. Con confirmacion. Permiso `manage-events`. |
| app/Filament/Resources/GoldenTicketResource.php:142 `resend` | Fila de tabla | Le recuerda al ganador que tiene un premio sin reclamar. | Crea `Announcement` privado con el codigo -> `AnnouncementObserver`. Sin push. | Visible si `pending`. Con confirmacion. |
| app/Filament/Resources/GoldenTicketResource.php:216 `create_ticket` | Header de tabla | Otorga un premio manual a un asistente. | `RewardService::createGoldenTicket` (Reward + RewardRedemption, motivo libre o preset, patrocinador opcional) + `Announcement` privado. Sin push. | Sin confirmacion. Lista asistentes del evento sin roles admin. |

## OrganizationEmailSettingsResource

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/OrganizationEmailSettingsResource/Pages/EditOrganizationEmailSettings.php:40 `test_connection` | Header de pagina | Prueba el SMTP propio mandando un correo y marca si quedo verificado. | `EmailService::testConnection` (envio sincrono por el SMTP guardado); `OrganizationEmailSettings.is_verified` y `verified_at` segun resultado. | Visible si `use_custom_smtp` y hay host. Sin confirmacion. Permiso `send-notifications`. Usa lo ultimo guardado. |

## ScheduledNotificationResource

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/ScheduledNotificationResource.php:110 `send_now` | Fila de tabla | Manda ya una notificacion programada. | Asistentes con `expo_push_token` (filtro por roles) -> `dispatchToAttendees` (logs + `SendPushToAttendeeJob`/`SendWebPushJob`); `sent_at`, `sent_count`. No crea `Announcement` (el job programado si). | Visible si no enviada. Con confirmacion. Permiso `send-notifications`. |
| app/Filament/Resources/ScheduledNotificationResource.php:142 `resend` | Fila de tabla | Crea una copia de la notificacion y la manda ya. | Crea `ScheduledNotification` nueva y hace lo mismo que `send_now` sobre la copia. | Visible si ya enviada. Con confirmacion. |

## SponsorResource

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/SponsorResource/Pages/ListSponsors.php:51 `ajustes_equipos` | Header de pagina | Guarda los ajustes de invitaciones de equipos de stand. | `Event.staff_allow_multi_stand`, `staff_invitation_expires`, `staff_invitation_hours` -> `EventObserver` (cache + broadcast 'branding'). | Permiso `manage-content`. Sin confirmacion. Horas 1-720. |

## SupportRequestResource

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/SupportRequestResource/Pages/EditSupportRequest.php:55 `resolve` | Header de pagina | Cierra la solicitud sin notificar al asistente. | `SupportRequest.status = resolved`. Sin push ni anuncio. | Visible si no esta resuelta. Con confirmacion. Permiso `view-content`. |

## UserResource (EditUser)

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/UserResource/Pages/EditUser.php:35 `unlock` | Header de pagina | Desbloquea una cuenta del staff bloqueada por intentos fallidos. | `StaffLockout::unlock` (app/Support/StaffLockout.php:115): `locked_until = null`, contadores a 0; `StaffSecurityEvent` `ACCOUNT_UNLOCKED`. | Visible solo a super_admin y si la cuenta esta bloqueada. Sin confirmacion. Permiso `manage-users`. |
| app/Filament/Resources/UserResource/Pages/EditUser.php:54 `resetTwoFactor` | Header de pagina | Borra el segundo factor de otra persona del staff para que lo configure de nuevo. | `UserResource::resetTwoFactor` -> `TwoFactorService::resetFor` (app/Services/TwoFactorService.php:246): desactiva 2FA, `StaffSecurityEvent` `TWO_FACTOR_RESET`, `TrustedDevice::revokeAllFor`, `StaffSession::closeAllFor`, `SendEmailJob` con `StaffTwoFactorResetMail`. | Visible si super_admin, no es uno mismo y la persona tiene 2FA; se repite el chequeo con `abort_unless` (403). Con confirmacion. |

## WallPostResource

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/WallPostResource.php:136 `publish` | Fila de tabla | Publica un post del muro. | `WallPost.status = published`. Si estaba pendiente: `tryAward('wall_post')` y POST socket `/internal/wall/broadcast` (`wall:post` con autor y foto). DataCenter. No avisa a la webapp. | Visible si no esta publicado. Sin confirmacion. Permiso `manage-content`. |
| app/Filament/Resources/WallPostResource.php:174 `hide` | Fila de tabla | Oculta un post. | `status = hidden`; DataCenter. Sin aviso a socket ni webapp. | Visible si no esta oculto. Con confirmacion. |
| app/Filament/Resources/WallPostResource.php:184 `bulk_publish` | Accion masiva | Publica varios posts. | `status = published` en cada uno; puntos a los que estaban pendientes. Sin socket ni webapp. Recarga la lista. | Sin confirmacion. |
| app/Filament/Resources/WallPostResource.php:222 `bulk_hide` | Accion masiva | Oculta varios posts. | `status = hidden` en cada uno. Sin avisos. | Con confirmacion. |
| app/Filament/Resources/WallPostResource/Pages/ViewWallPost.php:23 `publish` | Header de pagina (detalle) | Publica desde el detalle. | `status = published`; `InvalidationService::soltarMarcoWebapp(evento, 'wall')` (POST webapp). Sin puntos ni socket. | Visible si no esta publicado. Sin confirmacion. |
| app/Filament/Resources/WallPostResource/Pages/ViewWallPost.php:36 `hide` | Header de pagina (detalle) | Oculta desde el detalle. | `status = hidden`; `soltarMarcoWebapp(evento, 'wall')`. | Visible si no esta oculto. Con confirmacion. |

## WebhookApiKeyResource

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/WebhookApiKeyResource.php:114 `regenerate` | Fila de tabla | Cambia la llave de entrada del partner; la vieja deja de servir. | `WebhookApiKey.key = 'wh_live_' + 40 aleatorios`; muestra la llave nueva en la notificacion. | Con confirmacion. Permiso `manage-events`. |

## WebhookEndpointResource

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Resources/WebhookEndpointResource.php:155 `send_test` | Fila de tabla | Manda un ping de prueba al partner y dice si respondio bien. | `DispatchWebhookJob::dispatchSync` (POST firmado HMAC al URL del partner, crea `WebhookLog`, actualiza `last_triggered_at`/`last_status`); lee el ultimo log. | Con confirmacion. Permiso `manage-events`. |
| app/Filament/Resources/WebhookEndpointResource.php:185 `copy_secret` | Fila de tabla | Muestra el secreto HMAC del endpoint. | Ninguno; el secreto sale en la notificacion (no copia al portapapeles). | Sin confirmacion. |
| app/Filament/Resources/WebhookEndpointResource.php:194 `resend_failed` | Fila de tabla | Reenvia los webhooks que fallaron en las ultimas 24 h. | Por cada `WebhookLog` saliente, no de prueba, con status >= 400 de las ultimas 24 h: `DispatchWebhookJob::dispatch` con el mismo payload. | Visible si `last_status` es `needs_attention` o `failed`. Con confirmacion. |
| app/Filament/Resources/WebhookEndpointResource.php:212 `download_spec` | Fila de tabla | Descarga un .txt con la especificacion de la integracion para el partner. | Solo lectura. Incluye URL, eventos, campos, secreto HMAC y, si existe, la llave de entrada activa y su limite. | Sin confirmacion. |
| app/Filament/Resources/WebhookEndpointResource.php:315 `simulate_event` | Header de tabla | Manda 5 registros y 3 check-ins inventados a los endpoints activos. | `WebhookDispatchService::dispatch` 8 veces (`attendee.registered` x5, `attendee.checked_in` x3) -> `DispatchWebhookJob` por endpoint suscrito. | Con confirmacion. |

## Compartida (trait HasDirtySaveAction)

| Accion | Donde | Que hace | Efectos | Condiciones |
|---|---|---|---|---|
| app/Filament/Traits/HasDirtySaveAction.php:20 `save` | Header de pagina en 28 paginas de edicion | Boton "Guardar cambios", encendido solo si hay cambios. | Llama al `save()` de la pagina: el guardado estandar de Filament con sus hooks (`afterSave`, `beforeSave`, `mutateFormDataBeforeSave`) y observers; en EditEmail es un `save()` propio (EditEmail.php:198, `EmailTemplate::updateOrCreate` por idioma). Ver hallazgo 15. | Deshabilitado en el navegador si no hay cambios (Alpine `luminaDirtySave`). |

## Resumen

- Acciones con logica propia: 69 filas (coincide con las 69 apariciones). 5 de ellas estan en un relation manager que no se registra en ningun recurso.
- Cuentan los efectos directos y los que pasan por observers; no se cuenta la fila `save` del trait, porque su efecto depende de cada pagina.
- Despachan jobs: 17 (publish_now, publish, send_push de anuncios; chooseTags, aprobar_registro x2, aprobar_registros, banear, desbanear, regenerar_recap, importar; send_now y resend de notificaciones; resetTwoFactor; send_test, resend_failed, simulate_event de webhooks).
- Mandan correo: 8 (aprobar_registro x2, aprobar_registros, banear, importar, sendTest de plantilla, test_connection SMTP, resetTwoFactor).
- Llaman HTTP en la misma peticion (socket, webapp, partner o R2): 23 (las 3 de anuncios via observer; approve, award_prize, bulk_approve, award_top_contest y upload_official de fotos; regenerar_token; create de salon; assign_track; approve, answer y bulk_approve de preguntas; editAction de gamificacion; resend y create_ticket de Golden Ticket; ajustes_equipos; publish de muro en tabla, publish y hide de muro en detalle; send_test de webhook).
- Tocan cache o Redis: 33 (claves `event:{id}:announcements:*`, `event:{id}:agenda`, `event:{id}:branding`, `event:{id}:modules:*`, `dc:stats:{id}:ver`, `invalidate:{id}:{entidad}`, `socketauth:u:*` y `socketauth:t:*`, canal Redis `eventos:invalidate`).
- Jobs distintos: `SendPushToAttendeeJob`, `SendWebPushJob`, `SendEmailJob`, `DispatchWebhookJob`, `GenerateRecapImageJob`.
- Acciones estandar sin logica propia: 108 (33 EditAction, 36 DeleteAction, 22 CreateAction, 8 DeleteBulkAction, 4 ViewAction, 2 AttachAction, 2 DetachAction, 1 ExportAction). Excepcion: el DeleteAction de EditUser.php:67 tiene un `->before` que impide borrar al ultimo super_admin.

## Hallazgos

1. **[BUG-372, resuelto]** La push de un anuncio privado sale a todo el evento. `send_push` se ve en cualquier anuncio publicado (AnnouncementResource.php:338) y la lista incluye los privados porque solo filtra por evento (AnnouncementResource.php:39). `sendPush` (AnnouncementResource.php:275-280) no mira `target_attendee_id`. Si alguien toca "Enviar push" en un anuncio de Golden Ticket o de soporte, el titulo y el codigo de premio ("Ganaste: ... Codigo: XXXXXX") llegan a toda la audiencia.
2. **[BUG-375]** Hay cuatro formas de mandar push "ya" y cada una elige el publico distinto. Filament `sendPush` y `send_now`/`resend` solo toman asistentes con `expo_push_token`, asi que quien solo tiene web push queda fuera, y no excluyen baneados (AnnouncementResource.php:275-276, ScheduledNotificationResource.php:111-112 y 151-152). La API excluye baneados pero tambien solo usa Expo (Api/V1/Admin/NotificationController.php:78-80). El job programado si incluye web push y ademas crea un `Announcement` (Jobs/DispatchScheduledNotificationsJob.php:24-26 y 47-54). `send_now` desde Filament no crea ese anuncio.
3. **[BUG-373]** El ban desde Filament no saca al usuario del socket en el momento. La API llama `/internal/ban/enforce` (Api/V1/Admin/BanController.php:85 y 178-194). Filament solo borra la cache de autenticacion del socket con el hook `AttendeeBan::created` (AppServiceProvider.php:129-134). Otras diferencias: la API manda la push de ban solo si el usuario ya esta activado e incluye la fecha de vencimiento en el texto (BanController.php:68-79), y Filament no guarda la IP en la auditoria.
4. **[BUG-376]** Cambiar rol tiene reglas distintas en Filament y en la API. Filament ofrece `attendee` y `staff_checkin` (AttendeeAdminResource.php:321-324). La API acepta `Roles::ATTENDEE_ASSIGNABLE` (Support/Roles.php:60, Api/V1/Admin/AttendeeController.php:65), que no incluye `staff_checkin` y si incluye `vendedor` y los `admin*`.
5. **[BUG-377]** "Marcar reclamado" esta hecho dos veces y de forma distinta. GoldenTicketResource.php:127-130 actualiza directo, sin `confirmed_by`, sin bloqueo de fila y sin chequear vencimiento. PrizeRedemptions.php:108 usa `RewardService::confirm` (RewardService.php:102-121), que hace las tres cosas.
6. **[BUG-378]** La moderacion de fotos da resultados distintos segun desde donde se haga. Aprobar en la fila da puntos (EventPhotoResource.php:176-179) y aprobar en el detalle no (ViewEventPhoto.php:24). `bulk_approve` no filtra por estado (EventPhotoResource.php:255-260): vuelve a aprobar fotos rechazadas. Ademas, si la seleccion incluye una foto oficial (`attendee_id` nulo), llama `tryAward` con null en un parametro `int` (PointsService.php:120-122), lo que en PHP 8 da TypeError.
7. **[BUG-379]** Publicar y ocultar en el muro tiene 6 caminos con efectos distintos. En la fila: puntos y socket, sin webapp (WallPostResource.php:136-163). En masivo: puntos, sin socket ni webapp (WallPostResource.php:184-195). En el detalle: solo webapp, sin puntos ni socket (ViewWallPost.php:23-25 y 36-38). Ocultar en fila y en masivo no avisa a nadie (WallPostResource.php:174-175 y 222-224). `WallPost` no tiene observer propio, solo DataCenter.
8. **[BUG-380]** Asignar speakers no avisa a nadie. `speakers()->sync` (EventSessionResource.php:130) cambia la tabla pivote sin pasar por `EventSessionObserver`: la app y la webapp no se enteran hasta que algo mas invalide la agenda.
9. **[BUG-381]** Hay codigo muerto. `EventSessionResource\RelationManagers\QuestionsRelationManager` (5 acciones con broadcast al socket) no se registra: `getRelations()` devuelve [] (EventSessionResource.php:393-399) y ninguna otra clase lo referencia. La moderacion de preguntas vive en Mission Control.
10. **[BUG-382]** Activar o cerrar encuestas en Filament no avisa por socket (EventSurveyResource.php:125-139, EditEventSurvey.php:40-56). `PollController::start` y `close` si emiten `poll:new` y `poll:closed` (Api/V1/PollController.php:104 y 123).
11. **[BUG-383]** Abrir dos paginas ya cambia datos. La lista de encuestas crea la encuesta post-evento con 3 preguntas en `mount` (ListEventSurveys.php:32-53). Abrir una solicitud de soporte la pasa de `open` a `read` (EditSupportRequest.php:33-40).
12. **[BUG-384]** Webhooks:
    - `simulate_event` manda asistentes inventados con `test = test_mode` del endpoint (WebhookDispatchService.php:24). A un endpoint en modo real le llegan como registros reales (WebhookEndpointResource.php:315-336).
    - `resend_failed` reenvia cada intento fallido. Cada reintento del job crea su propio log (DispatchWebhookJob), asi que un mismo payload puede salir hasta 3 veces. Tampoco toma los fallos de red, que quedan con `response_status = 0` (WebhookEndpointResource.php:195-204).
    - En `send_test`, si la conexion falla el job relanza la excepcion dentro de `dispatchSync`. El admin ve un error en vez del aviso "Prueba fallo".
13. **[BUG-385]** Secretos a la vista. `copy_secret` muestra el secreto HMAC en una notificacion (WebhookEndpointResource.php:186). `regenerate` muestra la llave nueva (WebhookApiKeyResource.php:116). `download_spec` escribe el secreto y la llave de entrada en un .txt (WebhookEndpointResource.php:236, 241 y 269).
14. **[BUG-386]** Importar asistentes:
    - Corre sincrono dentro de la peticion (ListAttendeeAdmins.php:95; `AttendeesImport` no esta encolado).
    - Si el correo ya existe con una cuenta activada y el modo es invitacion, se genera un token nuevo que no se guarda pero igual se manda el correo con ese enlace, que queda roto (AttendeesImport.php:76, 91-92 y 131-138).
    - En modo telefono la contrasena es el telefono y la cuenta queda activada (AttendeesImport.php:72-74).
    - El archivo subido no se borra, y tampoco los temporales de fotos oficiales (EventPhotoResource.php:411-425).
15. **[no es bug: cobertura de esta tabla]** Parte de la logica no esta en `->action`, asi que esta tabla no la cubre. Hay 20 paginas con hooks de guardar o crear (`afterSave`, `afterCreate`, `beforeSave`, `mutateFormDataBefore*`). Por ejemplo:
    - EditSupportRequest.php:74-113: al guardar una respuesta resuelve la solicitud, manda push y crea un anuncio privado.
    - EditUser.php:83-95: no deja quitar el ultimo super_admin.
    - EventPulseResource.php:47-58: el toggle crea o borra `pulse_token` al instante, en `afterStateUpdated`.
    - CreateEvent.php:282 y 312, y los hooks de EventBranding, EventFaq, EventOnboarding, EventRecap, ChatSettings, RegistrationField y ReminderSettings.
16. **[BUG-387, BUG-374 y BUG-388]** Permisos:
    - No hay Policies y ninguna accion usa `->authorize`. El acceso es por recurso completo (`HasResourcePermission`).
    - `LiveGameResults` y `PrizeRedemptions` no definen permiso: entra cualquier usuario del panel.
    - `EventResource` no filtra por organizacion y `activar` deja trabajar en cualquier evento (EventResource.php:67-74; `EventContext::set` solo escribe la sesion, EventContext.php:38-41).
    - El correo de prueba de plantillas usa el mailer por defecto y no el SMTP de la organizacion (EditEmail.php:258 frente a `EmailService::mailerFor`), asi que no prueba el remitente real.
