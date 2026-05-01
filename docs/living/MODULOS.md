# EventOS — Modulos del Sistema

> **Version:** 1.0 | **Fecha:** 2026-04-24
> **Auditado contra:** codigo real de eventos-backend, eventos-app, eventos-socket, eventos-kiosko
> **Total:** 15 modulos app + 6 sistemas complementarios + admin Filament

---

## Indice

1. [Modulos de la App (visibles al usuario)](#modulos-app)
2. [Sistemas complementarios (no son modulos visibles)](#sistemas)
3. [Admin Filament (panel del organizador)](#admin)
4. [Socket Server (tiempo real)](#socket)
5. [Kiosk / Totem (hardware)](#kiosk)
6. [Event Pulse (dashboard live)](#pulse)
7. [Mission Control (moderador)](#mc)
8. [Data Center (exports)](#datacenter)

---

## Sistema de Modulos

Cada evento tiene modulos habilitables. Se configuran en la tabla `modules` con:

```
slug          — identificador unico
name          — nombre visible
icon          — icono heroicon
enabled       — habilitado si/no
roles         — JSON: ['attendee', 'vendedor', 'admin']
visibility_presence — 'all' | 'checked_in' | 'not_checked_in'
visibility_tags     — JSON: filtro por tags del attendee
config        — JSON: config custom por modulo
sort_order    — orden en el menu
```

**3 templates predefinidos:**
- **Congreso:** 15 modulos (todos)
- **Feria:** 7 modulos (agenda, mi-qr, leads, networking, anuncios, banners, patrocinadores)
- **Lanzamiento:** 7 modulos (agenda, speakers, anuncios, chat, encuestas, banners, patrocinadores)

---

<a name="modulos-app"></a>
## 1. Modulos de la App

### 1.1 Agenda (`agenda`)

**Que hace:** Muestra las sesiones del evento organizadas por dia/hora. El usuario puede favoritar sesiones y exportar a su calendario.

**Tablas:** `event_sessions`, `session_tracks`, `session_favorites`, `session_attendances`

**Funcionalidades:**
- Lista de sesiones por dia con hora, sala, tipo, track, speakers
- Favoritar/desfavoritar sesiones
- Export .ics al calendario personal
- 3 tiempos: programado (start_datetime), real (actual_start_at), ajustado (adjusted_end_at)
- Tipos de sesion: keynote, panel, workshop, break, networking
- Status: scheduled, live, finished, cancelled
- Stream URL (YouTube/Vimeo embebido) para virtuales
- Recording URL post-sesion

**Endpoints:** `/events/{id}/agenda`, `/{id}/agenda/{sessionId}/favorite`, `/sessions/{id}/calendar.ics`

**Puntos:** Favoritar sesion = configurable. Asistencia completa = attendance_points + bonus.

**Templates:** Congreso, Feria, Lanzamiento

---

### 1.2 Speakers (`speakers`)

**Que hace:** Directorio de ponentes con perfil, bio, empresa, redes sociales, y sesiones donde participan.

**Tablas:** `speakers`, `speaker_ratings`, `session_speaker`

**Funcionalidades:**
- Directorio de speakers con foto, nombre, empresa, cargo
- Perfil detallado con bio, LinkedIn
- Lista de sesiones donde participa (many-to-many)
- Rating de speakers (1-5 estrellas, separado del rating de sesion)

**Endpoints:** `/events/{id}/speakers`, `/speakers/{id}`, `/speakers/{id}/rate`

**Puntos:** Rating de speaker = configurable (rate_session action)

**Templates:** Congreso, Lanzamiento

---

### 1.3 Documentos (`documentos`)

**Que hace:** Biblioteca de materiales descargables del evento (PDFs, presentaciones, guias).

**Tablas:** `documents`

**Funcionalidades:**
- Lista de documentos generales del evento
- Documentos por sesion (session_id nullable)
- Descarga directa desde Cloudflare R2
- Tipos: PDF, PPT, DOC, imagenes

**Endpoints:** `/events/{id}/documents`

**Templates:** Congreso

---

### 1.4 Anuncios (`anuncios`)

**Que hace:** Canal de comunicacion del organizador hacia los asistentes. Mensajes importantes en tiempo real.

**Tablas:** `announcements`

**Funcionalidades:**
- Lista de anuncios con titulo y cuerpo
- Filtrado por roles (solo attendees, solo vendors, todos)
- Push notification automatica al publicar
- Broadcast por socket en tiempo real (`announcement:new`)
- Timestamp de publicacion

**Endpoints:** `/events/{id}/announcements`

**Templates:** Congreso, Feria, Lanzamiento

---

### 1.5 Networking (`networking`)

**Que hace:** Permite a los asistentes descubrirse, conectarse y hacer contactos profesionales.

**Tablas:** `contact_requests`, `contact_blocks`, `attendee_interests`, `onboarding_survey_options`

**Funcionalidades:**
- Directorio de asistentes (filtrable por nombre, empresa, cargo)
- Perfil publico con intereses en comun
- Sugerencias de contacto por overlap de intereses (matchmaking)
- Enviar solicitud de contacto con mensaje opcional
- Aceptar/ignorar solicitudes
- Bloquear usuarios (silencioso)
- Push + email al receptor de solicitud
- Socket notification en tiempo real (`networking:notify`)
- Rate limit: 100 solicitudes por evento, 30 por dia

**Endpoints:** `/events/{id}/attendees`, `/contacts/request`, `/me/contacts`, `/me/contact-requests`, `/contacts/block/{id}`, `/events/{id}/suggested-contacts`

**Puntos:** Conexion aceptada = 15 pts a AMBOS usuarios

**Templates:** Congreso, Feria

---

### 1.6 Mi QR (`mi-qr`)

**Que hace:** Genera el codigo QR personal del asistente para check-in y escaneo por vendors.

**Tablas:** `qr_tokens`

**Funcionalidades:**
- QR dinamico con HMAC-SHA256 (rota cada 60s)
- Formato: `d.{attendee_id}.{window}.{signature}`
- Usado para: check-in evento, check-in sala, escaneo de leads por vendors
- Pantalla con nombre, foto, rol badge, QR animado

**Endpoints:** `/me/qr`

**Templates:** Congreso, Feria

---

### 1.7 Leads (`leads`)

**Que hace:** Permite a los vendedores capturar leads escaneando QR de asistentes. Visible solo para rol vendedor.

**Tablas:** `leads`, `lead_edits`, `sponsor_leads`

**Funcionalidades:**
- Escanear QR del asistente para crear lead
- Clasificar tier: hot/warm/cold
- Agregar notas al lead
- Historial de ediciones (campo, valor anterior, nuevo, quien edito)
- Pool de leads personal o por sponsor (stand)
- Export CSV de leads

**Endpoints:** `/leads`, `/leads/{id}`, `/leads/{id}/edits`, `/me/leads/export`

**Roles:** Solo `vendedor`

**Templates:** Congreso, Feria

---

### 1.8 Chat (`chat`)

**Que hace:** Chat en tiempo real dentro de sesiones. Mensajes, emojis, moderacion.

**Tablas:** `chat_messages`

**Funcionalidades:**
- Mensajes de texto por sesion (room: `chat:session:{id}`)
- Emojis en tiempo real (8 permitidos: heart, clap, fire, laugh, wow, party, star, 100)
- Historial: ultimos 20 mensajes en Redis
- Pin de mensaje (admin, 1 por sesion, 24h TTL)
- Soft delete por admin (moderacion)
- Rate limit: 1 msg/2s (configurable via slow mode 0-120s)
- Palabras bloqueadas (silent drop)
- Pausa global de chat por admin
- Modo emoji-only (oculta input de texto)
- Puntos: 5 pts por primera participacion en chat de sesion

**Config evento:** `chat_blocked_words`, `chat_slow_mode_seconds`, `chat_paused`

**Socket events:** `chat:send`, `chat:message`, `chat:emoji`, `chat:pin`, `chat:delete`

**Templates:** Congreso, Lanzamiento

---

### 1.9 Encuestas / Polls (`encuestas`)

**Que hace:** Polls en vivo durante sesiones y encuestas post-evento.

**Tablas:** `live_polls`, `live_poll_questions`, `live_poll_options`, `live_poll_votes`, `surveys`, `survey_questions`, `survey_options`, `survey_answers`

**Funcionalidades:**
- **Live Polls:** Activados por admin en tiempo real durante sesion
  - 4 tipos: multiple_choice, open_text, star_rating, word_cloud
  - Status: draft -> active -> closed
  - Resultados en tiempo real via socket (`poll:new`, `poll:updated`, `poll:closed`)
  - Moderacion de respuestas open_text (approve/reject)
- **Post-event surveys:** Encuesta activada al terminar evento
  - Preguntas + opciones configurables
  - Respuesta unica por asistente

**Endpoints:** `/polls/{id}/vote`, `/sessions/{id}/poll/active`, `/events/{id}/surveys`, `/events/{id}/post-event-survey`

**Puntos:** 10 pts por votar en poll

**Templates:** Congreso, Lanzamiento

---

### 1.10 Banners (`banners`)

**Que hace:** Carousel de imagenes en la pantalla principal del app. Patrocinadores o contenido destacado.

**Tablas:** `banners`

**Funcionalidades:**
- Carousel de imagenes con link externo opcional
- Ordenables por sort_order
- Habilitables/deshabilitables
- Sponsor name asociado (no es lo mismo que patrocinadores)

**Endpoints:** `/events/{id}/banners`, `/events/{id}/highlights`

**Templates:** Congreso, Feria, Lanzamiento

---

### 1.11 Patrocinadores (`patrocinadores`)

**Que hace:** Directorio de sponsors con perfil, servicios, equipo, y formulario de contacto.

**Tablas:** `sponsors`, `sponsor_services`, `sponsor_favorites`, `sponsor_leads`, `stand_members`, `stand_trivias`, `stand_trivia_answers`

**Funcionalidades:**
- Directorio por tier: Platinum, Gold, Silver, Bronze, Community
- Perfil con logo, banner, descripcion, website, redes
- Lista de servicios ofrecidos
- Boton de contacto -> formulario con servicios interesados + mensaje
- Favoritar sponsors
- Equipo de stand (invitar colaboradores por email)
- Trivia por stand (preguntas con respuesta correcta, bonus points)
- Visit points configurables por sponsor

**Endpoints:** `/events/{id}/sponsors`, `/sponsors/{id}/favorite`, `/sponsors/{id}/contact`, `/sponsors/{id}/view`

**Templates:** Congreso, Feria, Lanzamiento

---

### 1.12 Fotos / Photobooth (`fotos`)

**Que hace:** Galeria de fotos del evento con moderacion, likes, y concurso de fotos.

**Tablas:** `event_photos`, `event_photo_likes`

**Funcionalidades:**
- Subir fotos con caption
- Moderacion: pending -> approved/rejected (o auto_approve)
- Likes con conteo denormalizado
- Fotos oficiales (staff)
- **Concurso de fotos:**
  - Ventana de tiempo (contest_starts_at / contest_ends_at)
  - 1 entrada por asistente (auto-marcada)
  - Ranking por likes
  - Max winners configurable
  - Puntos por posicion (JSON: {"1": 500, "2": 300, "3": 100})
  - Anti-gaming: no self-like, limite de likes por persona
  - Tiebreak: foto mas antigua gana

**Config evento:** `photos_auto_approve`, `max_photos_per_attendee`, `photo_contest_enabled`, `contest_name`, `contest_prize`, `contest_starts_at`, `contest_ends_at`, `contest_max_winners`, `contest_winner_points`, `contest_max_likes_per_attendee`

**Endpoints:** `/events/{id}/photos`, `/photos/mine`, `/photos/contest`, `/photos/{id}/like`

**Puntos:** 10 pts subir foto (si aprobada), 15 pts likes milestone (5,10,20,50,100)

**Templates:** Congreso

---

### 1.13 Social Wall (`social`)

**Que hace:** Muro social tipo feed donde los asistentes publican texto y fotos, comentan y dan like.

**Tablas:** `wall_posts`, `wall_comments`, `wall_post_likes`

**Funcionalidades:**
- Publicar texto + foto opcional
- Moderacion: pending -> published/hidden (o auto_approve)
- Comentarios en posts (max 500 chars)
- Likes con conteo denormalizado
- Broadcast en tiempo real (`wall:post`, `wall:comment`)
- Rate limit: max posts por dia, max 10 comentarios/dia

**Config evento:** `wall_auto_approve`, `max_wall_posts_per_attendee`

**Endpoints:** `/events/{id}/wall`, `/wall/{id}/like`, `/wall/{id}/comments`

**Puntos:** Post = 10 pts, Comentario = 5 pts (max 10/dia), Like milestone = 15 pts

**Templates:** Congreso

---

### 1.14 Leaderboard / Gamificacion (`leaderboard`)

**Que hace:** Tabla de posiciones en tiempo real. Muestra ranking de asistentes por puntos acumulados.

**Tablas:** `points_log`, `rewards`, `reward_redemptions`

**Funcionalidades:**
- Leaderboard top 50 + posicion del usuario actual
- Desglose de puntos por accion (16 tipos)
- Catalogo de premios canjeables por puntos
- Canje: token QR con 5 min de expiracion, confirmado por staff
- Si expira: puntos devueltos automaticamente (PointsLog negativo)
- Golden tickets: premios de juegos con claim_code (6 chars), sin expiracion

**16 acciones que otorgan puntos:**

| Accion | Puntos default | Limite |
|--------|---------------|--------|
| `checkin` | 50 | 1x evento |
| `visit_stand` | 20 (por sponsor) | 1x por stand |
| `stand_trivia` | 30 (por trivia) | 1x por trivia |
| `rate_session` | 15 | 1x por sesion |
| `ask_question` | 10 | por pregunta |
| `wall_post` | 10 | por post |
| `upload_photo` | 10 | por foto aprobada |
| `likes_milestone` | 15 | en 5,10,20,50,100 |
| `wall_comment` | 5 | max 10/dia |
| `connect` | 15 | por conexion (ambos) |
| `vote_poll` | 10 | por poll |
| `complete_interests` | 10 | 1x evento |
| `chat_session` | 5 | 1x por sesion |
| `upload_story` | 5 | max 5/dia |
| `game_spin` | variable | por juego |
| `game_trivia` | variable | score acumulado |

**Endpoints:** `/events/{id}/leaderboard`, `/me/points`, `/events/{id}/rewards`, `/rewards/{id}/redeem`, `/rewards/confirm`, `/me/prizes`, `/me/redemptions`

**Templates:** Congreso

---

### 1.15 Passport (`passport`)

**Que hace:** Sistema de sellos coleccionables. El asistente visita stands y acumula sellos para ganar un premio.

**Tablas:** `passport_stamps`

**Funcionalidades:**
- Cada sponsor con `passport_enabled=true` otorga un sello al ser visitado
- Progreso visible: X/N stamps requeridos
- Al completar: premio configurable + puntos bonus (passport_completion_points)
- Vinculado al modulo de patrocinadores (visit-stand)

**Config evento:** `passport_enabled`, `passport_required_stamps`, `passport_prize`, `passport_completion_points`

**Endpoints:** `/events/{id}/my-passport`

**Templates:** Congreso

---

<a name="sistemas"></a>
## 2. Sistemas Complementarios

Estos no son modulos visibles en el menu del app, pero son funcionalidades core del sistema.

### 2.1 Autenticacion y Registro

**Tablas:** `users`, `attendees`, `consent_logs`, `qr_tokens`, `registration_fields`, `registration_field_values`, `access_codes`, `event_invitation_links`

**Funcionalidades:**
- Registro por link (self-service) o CSV import
- Campos dinamicos configurables por evento (11 tipos: text, email, phone, select, searchable_select, checkbox, checkbox_group, textarea, number, url, date)
- Campos condicionales (depends_on)
- Campos por fase (show_in: registration, onboarding, both)
- Access codes con max_uses
- Aprobacion manual de registros (registration_requires_approval)
- Restriccion por email/dominio (registration_restriction)
- Verificacion de email
- Password reset
- Account lockout (5 intentos = 30 min lock)
- Consent log GDPR/Ley 1581 (IP, user_agent)
- Sanctum tokens con expiracion

**Endpoints:** `/auth/register`, `/auth/login`, `/auth/activate`, `/auth/verify-identity`, `/auth/forgot-password`

### 2.2 Onboarding

**Tablas:** `onboarding_slides`, `onboarding_survey_options`, `attendee_interests`

**Funcionalidades:**
- Slides de bienvenida pre-login (configurables)
- Formulario de datos adicionales (registration_fields show_in=onboarding)
- Encuesta de intereses (seleccion multiple, usada para matchmaking)
- Upload de foto de perfil
- Steps configurables via JSON: Welcome, Auth, Photo, About, Interests, FormStep, Done

**Endpoints:** `/events/{id}/onboarding`, `/events/{id}/onboarding/survey`, `/me/profile`, `/me/photo`

### 2.3 Q&A (Preguntas al Speaker)

**Tablas:** `session_questions`, `session_question_upvotes`

**Funcionalidades:**
- Enviar preguntas durante sesion (con opcion anonima)
- Moderacion: pending -> approved -> answered (o dismissed)
- Upvotes por otros asistentes
- Top question por upvotes
- Broadcast en tiempo real (`question:submitted`, `question:approved`, `question:answered`)
- Palabras bloqueadas (silent rejection)

**Endpoints:** `/sessions/{id}/questions`, `/questions/{id}/upvote`

**Puntos:** Preguntar = 10 pts

### 2.4 Session Ratings

**Tablas:** `session_ratings`, `speaker_ratings`

**Funcionalidades:**
- Rating 1-5 estrellas por sesion (1 por asistente)
- Comentario opcional
- Rating separado por speaker
- Distribucion de ratings (cuantos dieron 1, 2, 3, 4, 5)
- Rating promedio por sesion

**Endpoints:** `/sessions/{id}/rate`, `/speakers/{id}/rate`, `/me/my-ratings`, `/me/my-speaker-ratings`

**Puntos:** 15 pts por rating

### 2.5 Stories / Momentos

**Tablas:** `attendee_stories`

**Funcionalidades:**
- Subir foto efimera (expira en 24h)
- Visible solo para contactos aceptados
- Max 5/dia

**Endpoints:** `/events/{id}/stories`

**Puntos:** 5 pts por story

### 2.6 Notificaciones Push

**Tablas:** `push_notification_logs`, `scheduled_notifications`, `notification_preferences`

**Funcionalidades:**
- Push inmediatas (announcements) o programadas
- Segmentacion por roles
- Tracking: pending -> delivered -> opened -> failed
- Preferencias del usuario (opt-in/out por categoria)
- Expo push tokens (FCM)
- Session reminders (15 min antes)
- Agenda reminders (dia del evento)

**Endpoints:** `/auth/expo-token`, admin: `/notifications/send`, `/notifications/schedule`

---

<a name="admin"></a>
## 3. Admin Filament

Panel de administracion web para el organizador. 48 resources, 3 custom pages, 9 grupos de navegacion.

### Grupos y Resources

**Evento (9 resources):**
- EventBrandingResource — Branding: colores, logo, hero, venue, fechas, modalidad
- AttendeeAdminResource — Asistentes: lista, import CSV, export Excel, roles
- AttendeeBanResource — Bans: historial (read-only)
- LeadResource — Leads: lista por vendor/sponsor (read-only)
- RateLimitSettingsResource — Rate limits del evento
- EventRoomResource — Salas fisicas del venue
- ModuleResource — Modulos habilitados/configurados
- RoomTotemResource — Totems/kioscos por sala
- SponsorResource — Patrocinadores: CRUD completo + equipo + trivia

**Contenido (8 resources):**
- EventSessionResource — Sesiones: CRUD + stats page + speakers relation + MC link
- SpeakerResource — Speakers: CRUD + sesiones relation
- SessionTypeResource — Tipos de sesion
- SessionTrackResource — Tracks/categorias
- DocumentResource — Documentos descargables
- BannerResource — Banners carousel
- HighlightResource — Destacados
- CustomPageResource — Paginas HTML custom

**Registro (5 resources):**
- AccessCodeResource — Codigos de acceso
- EventOnboardingResource — Config de onboarding
- RegistrationSettingsResource — Config de registro
- RegistrationFieldResource — Campos dinamicos
- OnboardingSurveyOptionResource — Opciones de encuesta onboarding

**Comunicacion (7 resources):**
- AnnouncementResource — Anuncios + push
- EmailTemplateResource — Templates de email
- EmailLogResource — Log de emails (read-only)
- OrganizationEmailSettingsResource — SMTP por organizacion
- ReminderSettingsResource — Reminders automaticos
- ScheduledNotificationResource — Push programadas
- ChatSettingsResource — Config chat: blocked words, slow mode, pausa

**Interaccion (9 resources):**
- LivePollResource — Polls: CRUD + CSV import + start/close + resultados
- EventFaqResource — FAQs
- EventPhotoResource — Fotos (moderacion)
- EventPhotoSettingsResource — Config fotos + concurso
- WallPostResource — Muro social (moderacion)
- WallSettingsResource — Config muro
- SessionRatingResource — Ratings (read-only)
- SupportRequestResource — Tickets de soporte
- PostEventSurveyResource — Encuesta post-evento

**Gamificacion (4 resources):**
- GamificationSettingsResource — Config: 16 acciones, puntos, limites, roles
- GoldenTicketResource — Golden tickets (read-only)
- PassportSettingsResource — Config passport
- RewardResource — Catalogo de premios: CRUD

**Herramientas (1 resource):**
- EventPulseResource — Config Event Pulse dashboard

**Integraciones (3 resources):**
- WebhookEndpointResource — Endpoints webhook
- WebhookApiKeyResource — API keys
- WebhookLogResource — Log webhooks (read-only)

**Sistema (1 resource):**
- UserResource — Usuarios staff/admin

### Custom Pages

- **LiveGameResults** — Resultados de juegos (spin, trivia, jackpot) con export CSV
- **ModerarQnA** — Moderacion Q&A en tiempo real (approve/answer/dismiss)
- **PrizeRedemptions** — Confirmacion de premios con claim codes

---

<a name="socket"></a>
## 4. Socket Server (eventos-socket)

Servidor Node.js con Socket.IO + Redis adapter para tiempo real.

**Rooms:**
- `event:{eventId}` — Todos los usuarios del evento
- `pulse:{eventId}` — Solo dashboards Event Pulse (aislado)
- `session:{sessionId}` — Usuarios en una sesion
- `chat:session:{sessionId}` — Chat de sesion
- `display:session:{sessionId}` — Pantalla de proyeccion

**Eventos server -> client:** announcement:new, session:started/ended, poll:new/updated/closed, chat:message/history/deleted/emoji/pinned, question:submitted/approved/answered/upvoted, wall:post/comment, checkin:update, session:mode_changed, session:config_updated, session:audience, data:invalidate, networking:notify, ban:enforced, staff:invited/accepted/rejected/removed, display:project/stop, game:launched/question/answer-count/round-result/finished/result, pulse:active_users

**Eventos client -> server:** join:event, join:session, leave:session, chat:send, chat:emoji, chat:delete, chat:pin/unpin, display:project/stop

**Endpoints internos (/internal/*):** 16 endpoints HTTP llamados por Laravel backend con X-Internal-Secret

**Redis keys:** chat:history, chat:pinned, chat:count, session:viewers, session:joined/left, display:active, rate limits, access cache, blocked_words_cache

**Seguridad:** Sanctum tokens, Pulse tokens (ep_*), max 5 conexiones por usuario, rate limiting por accion, CORS fail-closed

---

<a name="kiosk"></a>
## 5. Kiosk / Totem (eventos-kiosko)

App React para pantallas fisicas en salas del evento.

### Modo Check-in Evento
- **IdleScreen** — Pantalla de bienvenida con aforo en vivo
- **ScanScreen** — Camara para escanear QR (jsQR)
- **ResultScreen** — Confirmacion: nombre, foto, rol, stand. Auto-return 4s

### Modo Room Totem
- **RoomIdleScreen** — Sesion actual/siguiente, timeline del dia, aforo sala, reloj
- **RoomResultScreen** — Check-in (verde), checkout (azul), error (rojo), offline (ambar)

**Features:**
- USB scanner support (HID keyboard emulation)
- Offline queue: escaneos sin conexion se guardan y sincronizan via batch
- Heartbeat cada 10s (ping al backend)
- Cache local de nombres para respuesta instantanea
- Auth via X-Totem-Token (64 chars, generado por totem)

**Tablas:** `room_totems`, `room_movements`, `room_attendee_states`, `event_rooms`

---

<a name="pulse"></a>
## 6. Event Pulse (Dashboard Live)

Dashboard en tiempo real para el organizador. Webapp HTML con Socket.IO.

**Auth:** Token `ep_` + 32 chars aleatorios. Socket room aislado `pulse:{eventId}`.

**Secciones:**
- **Check-ins:** Total registrados vs checked-in, timeline por hora, ultimos 5
- **Rooms:** Sesiones agrupadas por sala, ocupacion en vivo, top 20 asistentes con foto
- **Leads:** Leads por sponsor (nombre, logo, conteo), ultimos 5 leads
- **Connections:** Total conexiones aceptadas, % acceptance rate, ultimas 6
- **Social:** Top 15 posts por likes
- **Leaderboard:** Top 10 por puntos
- **Ratings:** Top 6 sesiones por rating, promedio evento

**Endpoints:** `/pulse/{slug}/bootstrap`, `/rooms`, `/checkins`, `/leads`, `/connections`, `/social`, `/leaderboard`, `/ratings`

**Performance:** Room aislado (pulse room). Solo 1-2 sockets reciben datos de audiencia. 99.75% ahorro de bandwidth vs broadcast a 10K.

---

<a name="mc"></a>
## 7. Mission Control (Moderador)

Panel unificado para controlar sesiones en vivo. Acceso via HMAC link desde Filament o directo.

**Controles:**
- Toggle modo interactivo: Chat / Q&A / Poll / Trivia / Custom / None
- Emoji-only mode
- Slow mode (0-120s entre mensajes)
- Custom embed URL (HTTPS only, whitelist de dominios)
- Stream URL / iframe

**Paneles:**
- **Chat:** Mensajes en vivo, delete, ban, pin/unpin
- **Q&A:** Pending/Approved/Answered, approve/reject/answer, upvotes
- **Polls:** Lanzar/cerrar, resultados en tiempo real
- **Games:** Lanzar spin/trivia/jackpot, ver resultados

**Lifecycle de sesion:**
- Start (actual_start_at)
- End (actual_end_at) -> trigger: puntos asistencia, encuesta post-evento
- Cancel (cancelled_at)
- Delay (adjusted_end_at) -> push a asistentes afectados

**Config en vivo:** `interactive_mode`, `emoji_only`, `slow_mode_seconds`, `custom_enabled`, `custom_url`, `stream_url`, `stream_iframe`, `recording_url`

---

## 8. Live Moments (Juegos en Vivo)

Lanzados desde Mission Control durante sesiones.

### Spin (Ruleta)
- Admin configura sectores con label, puntos, peso, color
- Todos participan, todos reciben puntos del sector que les toco
- Weighted random para resultado
- ProcessSpinRewardsJob: bulk insert participantes + puntos

### Trivia (Kahoot-style)
- Preguntas con opciones, respuesta correcta, time_limit
- Por rondas: pregunta -> respuestas -> cierre -> leaderboard
- Puntos por correcta + speed bonus
- ProcessTriviaRewardsJob: puntos acumulados al finalizar
- Auto-post ganador en wall

### Jackpot (Sorteo)
- Asistentes se unen al pool (Redis SET)
- Audiencia filtrable: presencial, virtual, all
- Admin sortea -> 1 ganador aleatorio
- Ganador recibe: golden ticket (claim_code 6 chars), reward_redemption, anuncio, push, wall post
- Slot machine animation en el cliente

**Tablas:** `live_games`, `live_game_participants`, `rewards`, `reward_redemptions`

**Cooldown:** 2 min entre juegos por sesion (dev: 10s)

---

<a name="datacenter"></a>
## 9. Data Center (Exports)

Centro de datos con 44 exports CSV/Excel descargables desde Filament. Ver `ROADMAP-DATA-CENTER.md`.

**Estado:** Roadmap completo, pendiente implementacion.

**Arquitectura:** VPS-3 worker headless contra replica MySQL. Filament queda en VPS-1/VPS-2. 5 capas de performance.

---

## Resumen Cuantitativo

| Categoria | Cantidad |
|-----------|----------|
| Modulos app visibles | 15 |
| Sistemas complementarios | 6 (auth, onboarding, Q&A, ratings, stories, push) |
| Filament Resources | 48 |
| Filament Custom Pages | 3 |
| Filament Nav Groups | 9 |
| API Controllers | 45+ |
| Models | 79 |
| Socket events (server->client) | 30+ |
| Socket events (client->server) | 10 |
| Internal HTTP endpoints (socket) | 16 |
| Tablas de base de datos | 50+ |
| Acciones con puntos | 16 |
| Tipos de juego live | 3 (spin, trivia, jackpot) |
| Templates de evento | 3 (congreso, feria, lanzamiento) |
| Tipos de campo dinamico | 11 |
| Emails automaticos | 12 tipos |
| Exports planificados (Data Center) | 44 |
