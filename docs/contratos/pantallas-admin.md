# Mapa de pantallas del admin nuevo (M2.1.1)

Fuente: `eventos-backend/app/Filament` leido el 2026-10-25: 42 resources, 6 paginas sueltas y 10 clusters. Las
acciones de cada pantalla estan en `acciones-admin.md` (M0.5.3). El alcance sale de D.3 (cabecera de la fase 2 del
roadmap). Como se ve cada pantalla lo fija M2.1.2: se copia Lumina como esta hoy en Filament.

Destinos:
- **H1**: entra al hito 1. Se indica el bloque del roadmap que la construye.
- **M2b**: despues del hito 1. Mientras tanto sigue en Filament.
- **Absorbida**: no tiene pantalla propia. Vive dentro de otra.
- **Enlace**: sigue siendo su propia app (Data Center, Mission Control, Event Pulse). El admin nuevo solo la abre.
- **Se demuele**: no se construye y se borra de Filament en M2.8.3.

## Menu

Los grupos y el orden son los de hoy (AdminPanelProvider.php:134-140 y los clusters). El admin nuevo los copia.

| Grupo | Entrada del menu (cluster) | Pantallas adentro (pestanas de arriba) |
|---|---|---|
| (arriba) | Escritorio | Dashboard |
| Contenido | Agenda | Sesiones, Tracks, Tipos de sesion, Evaluaciones (oculta) |
| Contenido | Speakers | Speakers |
| Contenido | Sponsors | Sponsors |
| Contenido | Documentos | Documentos |
| Contenido | Paginas | Paginas |
| Personas | Asistentes | Asistentes, Bloqueos |
| Personas | Entrada | Registro, Campos del formulario, Codigos de acceso, Landing y widget |
| Comunicacion | Anuncios | Anuncios, Push programadas, Recordatorios |
| Comunicacion | Emails | Plantillas, Envio (SMTP), Historial (oculta) |
| Experiencia | Identidad | Branding (con la pestana Onboarding app), Slides del login, Highlights, FAQ, Recap |
| Experiencia | Modulos | Panel de modulos |
| Experiencia | Social | Publicaciones, Fotos, Ajustes del muro |
| Experiencia | Gamificacion | Gamificacion, Golden Tickets, Premios, Premios ganados |
| En vivo | (sueltas) | Chat, Encuestas, Salones, Soporte, Event Pulse, Data Center (enlace al SPA), Live Moments (oculta) |
| Sistema | (sueltas) | Eventos, Staff y permisos, Limites de uso |
| Sistema | Webhooks | Webhooks, API Keys, Logs (oculta) |
| (menu de cuenta) | Seguridad de tu cuenta | AccountSecurity |

Capturas del admin actual (M2.1.2, 1440 px, pagina completa, 87 por tema): `design/features/admin/filament-actual/` (Noir) y `lux/` (Lux), con
`_indice.txt` (archivo, estado HTTP, URL final, titulo) y `capturar.mjs` para repetirlas (Kamilo entra a mano; el script
no toca credenciales; se corre desde la raiz de `eventos-web`).

## Pantalla por pantalla

| Pantalla (Filament) | Grupo | Destino | Bloque | Nota |
|---|---|---|---|---|
| Dashboard | Escritorio | H1 | 2.1 | Pagina de entrada del admin. Las cifras del evento son del Data Center (enlace) |
| AccountSecurity | Cuenta | H1 | 2.2.1 | 2FA, codigos de recuperacion, dispositivos y sesiones del staff |
| EventResource | Sistema | H1 | 2.3.1 | Con filtro por organizacion (BUG-374) y el asistente de primer ingreso (`CreateEvent`) |
| UserResource | Sistema | H1 | 2.4.4 (staff en 2.4.4a: hecho; seguridad 2.4.4b; limpieza 2.4.4c) | Staff, roles y reset de 2FA (las invitaciones de stand van en M4e.7). No deja borrar al ultimo super_admin |
| EventSessionResource (+ Speakers por sesion) | Contenido / Agenda | H1 | 2.3.2 | Asignar speakers tiene que avisar (BUG-380) |
| SessionTrackResource | Contenido / Agenda | H1 | 2.3.2 | |
| SessionTypeResource | Contenido / Agenda | H1 | 2.3.2 | |
| EventRoomResource (+ Totems) | En vivo | H1 | 2.3.2 | Salones. Crear uno llama al socket |
| SpeakerResource (+ Sesiones) | Contenido | H1 | 2.3.3 | |
| SponsorResource | Contenido / Sponsors | H1 | 2.3.3 | Tiers, servicios, stand, equipo |
| HighlightResource | Experiencia / Identidad | H1 | 2.3.3 | En la interfaz se llama "Destacados" (M2.8.3) |
| LoginSlideResource | Experiencia / Identidad | H1 | 2.3.3 | |
| EventFaqResource | Experiencia / Identidad | H1 | 2.3.3 | |
| DocumentResource | Contenido | H1 si cabe | 2.3.3 | D.3: "si caben" |
| EventBrandingResource | Experiencia / Identidad | H1 | 2.3.4b y 2.3.4c (hechas 2026-10-30) | Lo esencial, Identidad comun, App movil, Webapp y Onboarding app (siete pantallas) ya en el admin nuevo; Filament lo sigue mostrando hasta M2.7 (INT.2) |
| ModulosPanel | Experiencia | H1 | 2.3.4a (hecha 2026-10-30) | Ya en el admin nuevo; Filament lo sigue mostrando hasta M2.7 |
| AttendeeAdminResource | Personas / Asistentes | H1 | 2.4.1, 2.3.6 | Lista, perfil, tags, aprobacion, check-in manual, importar y exportar |
| RegistrationFieldResource | Personas / Entrada | H1 | 2.4.1 | Los campos de registro estan en 2.4.1 |
| AttendeeBanResource | Personas / Asistentes | H1 | 2.4.2 | Con expulsion del socket (BUG-373) |
| WebhookEndpointResource, WebhookApiKeyResource, WebhookLogResource | Sistema / Webhooks | H1 | 2.4.3 (endpoints 2.4.3a, llaves 2.4.3b e historial 2.4.3c: hechos) | Los secretos no se muestran (BUG-385); el del endpoint se copia sin mostrarse |
| GoldenTicketResource, RewardResource, PrizeRedemptions | Experiencia / Gamificacion | H1 | 2.4.5 | Marcar como reclamado con `RewardService::confirm` (BUG-377) |
| AccessCodeResource | Personas / Entrada | H1 | 2.4.5 | |
| SupportRequestResource | En vivo | H1 | 2.4.6 (hecha 2026-11-02) | Abrir una solicitud no la marca como leida (BUG-383); entra con view-content o manage-content (BUG-509); contador de abiertas en el menu |
| AnnouncementResource | Comunicacion / Anuncios | H1 | 2.5.1a y 2.5.1b (hechas 2026-11-02) | La push de un anuncio privado no sale a todos (BUG-372); audiencia vacia = todos (BUG-510); privados solo para leer; push tambien al navegador (BUG-511) |
| ScheduledNotificationResource | Comunicacion / Anuncios | H1 | 2.5.2 (hecha 2026-11-02) | Un solo publico para la push (BUG-375, resuelto) |
| EmailTemplateResource, OrganizationEmailSettingsResource | Comunicacion / Emails | H1 | 2.5.3 (hecha 2026-11-02) | Editor con formato en M2.8.6 |
| EmailLogResource | Comunicacion / Emails | H1 | 2.5.4 (hecha 2026-11-02) | Solo lectura; tambien el push por notificacion |
| EventSurveyResource | En vivo | M2b | 2.6.1a y 2.6.1b (hechas 2026-11-03) | Activar y cerrar avisan al socket (BUG-382); la post-evento sale de un boton, la lista no la crea; el organizador no entra (BUG-519); una opcion con votos no se quita y una pregunta con respuestas no cambia de tipo |
| LiveGameResults | En vivo | M2b | 2.6.2 (absorbida 2026-11-03, sin codigo) | Oculta desde 2026-07-18; los resultados los exporta el Data Center (M2.7.2). Configurar y lanzar juegos es de Mission Control con la API de PHP (M4d.6) |
| GamificationSettingsResource | Experiencia / Gamificacion | M2b | 2.6.3a (hecha 2026-11-03) | La asistencia a sesiones se guarda (BUG-513, pendiente de M2.7.1: Filament se oculta o queda de solo lectura); apagar el premio del pasaporte lo borra (BUG-514); cada accion se edita en una ventana encima, como Filament |
| ReminderSettingsResource | Comunicacion / Anuncios | M2b | 2.6.3b (hecha 2026-11-03) | Un minuto que no vale da error, sin repetidos, apagar conserva los minutos (BUG-516) |
| ChatSettingsResource | En vivo | M2b | 2.6.3b (hecha 2026-11-03) | Modo lento vacio da error (BUG-517); al guardar, Node recarga la configuracion del chat |
| RateLimitSettingsResource | Sistema | M2b | 2.6.3b (hecha 2026-11-03) | Abre con los limites que de verdad aplican; guardar no los apaga (BUG-515) |
| RegistrationSettingsResource | Personas / Entrada | H1 | 2.3.1 | Subida desde M2b (Kamilo, 2026-10-25): sin ella no se monta un evento nuevo |
| EventRecapResource | Experiencia / Identidad | M2b | 2.6.4a (hecha 2026-11-03, la configuracion); generar y regenerar en M4e.5 | Abre con los valores que aplican y guardar no apaga nada (BUG-520, resuelto en el admin nuevo). Hasta M4e.5: finalizar desde el admin nuevo no genera recaps y regenerar sigue en Filament |
| WallPostResource, WallSettingsResource | Experiencia / Social | M2b | 2.6.5 | Bloque nuevo (Kamilo, 2026-10-25). Un solo camino para publicar y ocultar (BUG-379) |
| EventPhotoResource | Experiencia / Social | M2b | 2.6.5 | Aprobar da los mismos puntos desde cualquier lado (BUG-378) |
| LandingWidget | Personas / Entrada | H1 | 2.3.1 | No estaba en ningun bloque. Sube al hito 1 (Kamilo, 2026-10-25) |
| SessionRatingResource | Contenido / Agenda | Absorbida | | Ya esta oculta. Las evaluaciones se leen en el Data Center |
| EventPulseResource | En vivo | Enlace | 2.7.3 | Enciende Event Pulse y genera su token. La pantalla de Event Pulse es otra app |
| EventOnboardingResource | (oculta) | Se demuele | 2.8.3 | Sus secciones ya viven en Branding (INT.2, EventBrandingResource.php:309). La app de Expo si usa el onboarding (`lib/onboardingApi.ts`); lo que se quita es solo el resource duplicado |
| CustomPageResource | Contenido | H1 | 2.3.3 | Se queda (Kamilo, 2026-10-25): carga contenido del cliente en un iframe (`iframe_url`) o un cuerpo propio, con roles. Por verificar en M2.3.3 por donde llega el asistente: la app de Expo registra `app/(app)/pages/[id].tsx` y la webapp no llama `/pages` |
| Banners | | Ya demolido | | Se borro el 2026-07-04 (`2026_07_04_230000_drop_banners_feature.php`). Hoy son Highlights |

## Avisos genericos del navegador en el admin actual (no copiar)

Regla de M2.8.5 (Kamilo, 2026-10-25): en el admin nuevo no hay `alert()`, `confirm()`, `prompt()` ni avisos genericos
del navegador. Revisado el 2026-10-25 en `eventos-backend` (Filament, vistas, `public/`):

| Donde | Que sale hoy | En el admin nuevo |
|---|---|---|
| Todo el panel de Filament, al vencer la sesion o el token CSRF (419) | `confirm("This page has expired. Would you like to refresh the page?")` de Livewire, en ingles (`vendor/livewire/livewire/dist/livewire.js:4486`, `handlePageExpiry`; no esta sobreescrito) | Pantalla propia de sesion vencida |
| Todo el panel, cuando una peticion de Livewire falla (403 de `RequireTwoFactor` sin segundo factor, 500) | El HTML crudo de la respuesta en un modal de Livewire (`showFailureModal`, misma linea 4486 y siguientes) | 403 sin segundo factor: la pantalla de sesion vencida. Otros errores: toast de Lumina con el mensaje, sin HTML crudo |
| Formularios con cambios sin guardar, al cerrar la pestaña o recargar | Dialogo nativo de `beforeunload` (`app/Providers/Filament/AdminPanelProvider.php:308`). Al navegar dentro del panel ya sale el modal propio `lum-leave` | Borrador guardado mientras se escribe; nada de `beforeunload` |
| Data Center, borrar un embed | `confirm('Borrar este embed?')` (`public/data-center/assets/app.js:1738`) | Modal de confirmacion (cuando el Data Center pase al admin nuevo, M2.7.2) |
| Data Center, borrar una exportacion programada | `confirm('Borrar este programado?')` (`public/data-center/assets/app.js:1824`) | Modal de confirmacion |
| Monitor de chat, banear | `confirm('Banear a ...?')` (`public/chat-monitor.html:515`) | Modal de confirmacion (M2.7.3) |
| Campos obligatorios de Filament | Probable: la burbuja nativa "Completa este campo" (los campos llevan `required` y los formularios no tienen `novalidate`). Por verificar en el navegador al construir M2.3 | Error en linea bajo el campo, con el texto de la validacion de la API |

Lo que Filament ya hace bien y se copia: las acciones con `requiresConfirmation()` (37 en `app/Filament`) abren un modal
propio, y los avisos salen como notificaciones del panel (el toast de Lumina).

## Cuentas

- Total: 48 (42 resources y 6 paginas).
- H1: 35 pantallas (31 seguras y 4 "si caben": documentos, plantillas, SMTP e historial de correos).
- M2b: 10. Absorbida: 1. Enlace: 1. Se demuele: 1 (`EventOnboardingResource`; banners ya no existe).

## Decisiones (Kamilo, 2026-10-25)

1. Las paginas personalizadas se quedan: el cliente las usa para cargar su contenido en un iframe.
2. Muro y fotos van a M2b, en el bloque nuevo M2.6.5.
3. Ajustes de registro y landing y widget suben al hito 1, en M2.3.1.
