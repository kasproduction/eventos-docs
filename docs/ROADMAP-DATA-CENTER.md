# ROADMAP: Data Center (Centro de Datos)

> **Prioridad:** P2
> **Estimacion:** 14-18h (7 fases)
> **Principio:** El evento NUNCA se detiene por una metrica. Todo via queue.
> **Patron base:** ExportSessionStatsJob + maatwebsite/excel + Filament Notifications
> **Auditado contra:** Codigo real de eventos-backend (24 abril 2026)

---

## Filosofia

No es un "dashboard bonito". Es un **centro de datos operativo** donde el organizador (y el patrocinador) descarga absolutamente todo lo que el sistema midio. Cada interaccion, cada click, cada lead, cada voto, cada segundo de permanencia. Si paso en EventOS, se puede descargar.

La competencia (Cisco Webex Events $88K, ICE360 $49M COP) entrega reportes detallados. Este no puede ser un punto flaco.

---

## Arquitectura

```
[Filament Page: DataCenter]
    |
    +-- Tabs por categoria (9 tabs)
    |     |
    |     +-- Cards con conteo + boton "Descargar"
    |     +-- Cada boton despacha un Job al queue
    |     +-- Job genera CSV/Excel en storage/exports/
    |     +-- Filament Notification con link de descarga
    |
    +-- Filtros globales: Evento, Rango de fechas
    +-- Formato: CSV (default) o Excel
    +-- Descarga: link temporal (signed URL, 24h expiry)
```

### Queue Strategy (CRITICO)

```
Queue: "exports" (dedicado, separado de "default")
Concurrencia: max 2 exports simultaneos
Timeout: 300s (5 min)
Retry: 1 intento
Cleanup: Cron borra exports > 48h
```

El worker de exports corre en un queue separado para que NUNCA compita con:
- Push notifications (queue: default)
- Emails (queue: default)
- Gamificacion: ProcessSpinRewardsJob, ProcessTriviaRewardsJob (queue: default)
- Webhooks: DispatchWebhookJob (queue: default)
- Attendance: AwardSessionAttendancePointsJob, FlushSessionAttendanceJob (queue: default)

```bash
# Worker produccion
php artisan queue:work --queue=default --tries=3
php artisan queue:work --queue=exports --tries=1 --timeout=300
```

---

## Inventario COMPLETO de Metricas (42 exports)

Auditado contra modelos, migraciones y controladores reales.

### TAB 1: Asistentes y Acceso (6 exports)

| # | Export | Columnas del CSV | Tabla(s) fuente |
|---|--------|-----------------|-----------------|
| 1 | **Asistentes Master** | Nombre, Email, Telefono, Empresa, Cargo, Rol, Tags[], Check-in, Checkout, Badge impreso, Fuente invitacion (csv/link/qr/manual), Networking visible, Lead tier, Fecha registro, + TODOS los campos dinamicos de registration_field_values | `attendees` + `users` + `registration_field_values` + `registration_fields` |
| 2 | **Check-ins Evento** | Nombre, Email, Rol, Hora check-in, Hora checkout, Duracion total en venue (min), Metodo checkout (manual/auto_end_day/smart) | `attendees` (checked_in_at, checked_out_at) |
| 3 | **Movimientos por Sala** | Nombre, Email, Sala, Totem (nombre), Tipo (checkin/checkout), Metodo (qr_scan/manual/auto_room_change/auto_end_day/auto_end_session), Hora scan, Flags (fuera_horario/cambio_salon/inferido/cola_offline), Notas | `room_movements` + `room_totems` + `event_rooms` |
| 4 | **Asistencia por Sesion (Presencial + Virtual)** | Sesion, Nombre, Email, Fuente (app/web/kiosko/manual), Hora entrada, Hora salida, Duracion (min), Minuto de abandono (left_at - actual_start_at), % sesion visto, Aun conectado (si/no) | `session_attendances` + `event_sessions` (actual_start_at) |
| 5 | **Silent Disco (Pulse Checks)** | Check ID, Sala, Sesiones incluidas, Fecha disparo, TTL (seg), Total en sala al momento, Total respondieron, % respuesta, Lista de quienes NO respondieron (nombre, email) | `attendance_checks` + `attendance_check_responses` + `room_attendee_states` |
| 6 | **Consent Log (GDPR/Ley 1581)** | Nombre, Email, IP, User Agent, Fecha aceptacion | `consent_logs` |

**Nota sobre #4:** Este export es CLAVE para virtuales. `session_attendances` tiene `joined_at`, `left_at`, `duration_seconds` y `source`. Si `left_at IS NULL` el asistente sigue conectado. Esto responde "cuanto tiempo duro viendo un virtual".

### TAB 2: Sesiones y Contenido (5 exports)

| # | Export | Columnas del CSV | Tabla(s) fuente |
|---|--------|-----------------|-----------------|
| 7 | **Resumen de Sesiones** | Sesion, Tipo (keynote/panel/workshop/break/networking), Track, Sala, Speakers, Fecha, Hora inicio programada, Hora inicio real (actual_start_at), Hora fin real (actual_end_at), Hora ajustada (adjusted_end_at), Duracion real (min), Asistentes unicos, Pico de viewers concurrentes, Minuto del pico, Viewers actuales (left_at IS NULL), Duracion promedio asistente (min), Rating promedio, Total ratings, Mensajes chat, Participantes chat unicos, Preguntas Q&A (total/approved/answered), Votos en polls, Favoritos, Engagement score promedio | `event_sessions` + `SessionStatsService` + pico calculado de `session_attendances` |
| 8 | **Favoritos de Sesiones** | Sesion, Tipo, Fecha sesion, Nombre asistente, Email, Fecha favorito | `session_favorites` |
| 9 | **Ratings de Sesiones** | Sesion, Nombre asistente, Email, Rating (1-5), Comentario, Fecha | `session_ratings` |
| 10 | **Ratings de Speakers** | Speaker, Empresa, Sesiones donde participo, Nombre asistente, Email, Rating (1-5), Fecha | `speaker_ratings` + `session_speaker` |
| 11 | **Asistencia por Fuente** | Sesion, Fuente (app/web/kiosko/manual), Conteo, % del total | `session_attendances` agregado por source |

**Nota sobre #7:** Los 3 tiempos de sesion son criticos: `start_datetime` (programado), `actual_start_at` (cuando realmente inicio), `adjusted_end_at` (si se demoro). El organizador necesita saber si las sesiones cumplieron horario.

### TAB 3: Engagement en Vivo (5 exports)

| # | Export | Columnas del CSV | Tabla(s) fuente |
|---|--------|-----------------|-----------------|
| 12 | **Chat por Sesion** | Sesion, Autor nombre, Autor email, Mensaje, Tipo (text/image/system), Room, Fecha/hora | `chat_messages` |
| 13 | **Preguntas Q&A** | Sesion, Autor nombre, Autor email, Pregunta, Estado (pending/approved/answered/dismissed), Upvotes, Es anonima, Fecha envio, Fecha respuesta (answered_at) | `session_questions` |
| 14 | **Polls y Votaciones** | Poll titulo, Sesion, Pregunta, Tipo pregunta (multiple_choice/open_text/rating/word_cloud), Opcion elegida / Texto respuesta, Nombre votante, Email, Fecha voto | `live_poll_votes` + `live_poll_options` + `live_poll_questions` + `live_polls` |
| 15 | **Encuestas Post-Evento** | Encuesta titulo, Pregunta, Opcion/Respuesta, Nombre, Email, Fecha | `survey_answers` + `survey_questions` + `survey_options` |
| 16 | **Engagement Score por Asistente por Sesion** | Sesion, Nombre, Email, Score total (0-100), Asistencia (0-40), Chat (0-15), Poll (0-15), Q&A (0-15), Rating (0-15) | Calculado por `SessionStatsService` |

**Nota:** Se elimino "Reacciones Emoji" como export independiente porque los emojis van por socket sin persistencia en DB. El chat ya incluye type=emoji si se persiste.

### TAB 4: Patrocinadores y Leads (7 exports)

| # | Export | Columnas del CSV | Tabla(s) fuente |
|---|--------|-----------------|-----------------|
| 17 | **Leads Master** | Patrocinador, Tier sponsor (platinum/gold/silver/bronze), Vendedor nombre, Vendedor email, Lead nombre, Lead email, Lead telefono, Lead empresa, Lead cargo, Tier lead (hot/warm/cold), Nota, Fecha captura | `leads` + `attendees` + `users` + `sponsors` |
| 18 | **Historial de Ediciones de Leads** | Lead (nombre+email escaneado), Patrocinador, Campo editado (tier/note), Valor anterior, Valor nuevo, Editado por (nombre), Fecha | `lead_edits` |
| 19 | **Formularios de Contacto (Sponsor Leads)** | Patrocinador, Asistente nombre, Email, Servicios interesados (nombres), Mensaje, Fecha envio, Fecha notificacion al sponsor | `sponsor_leads` + `sponsor_services` |
| 20 | **Visitas a Stands** | Patrocinador, Tier, Asistente nombre, Email, Empresa, Cargo, Puntos otorgados (visit_points del sponsor), Fecha visita | `passport_stamps` + `sponsors` |
| 21 | **Trivia de Stands** | Patrocinador, Pregunta, Opciones, Respuesta correcta, Asistente nombre, Email, Respuesta seleccionada, Correcta? (si/no), Puntos bonus, Fecha | `stand_trivia_answers` + `stand_trivias` |
| 22 | **Favoritos de Sponsors** | Patrocinador, Tier, Asistente nombre, Email, Fecha | `sponsor_favorites` |
| 23 | **Equipo de Stand (Staff)** | Patrocinador, Miembro nombre, Email, Invitado por, Estado (pending/active/removed), Fecha invitacion, Fecha ingreso | `stand_members` |

**Nota:** Este tab es CRITICO. Los patrocinadores pagan. Si no pueden demostrar ROI, no renuevan. Cada export incluye datos de contacto completos. El export #17 es el mas importante de todo el Data Center.

### TAB 5: Gamificacion y Juegos (10 exports)

| # | Export | Columnas del CSV | Tabla(s) fuente |
|---|--------|-----------------|-----------------|
| 24 | **Leaderboard Final** | Posicion, Nombre, Email, Empresa, Puntos totales, Desglose: checkin, visit_stand, stand_trivia, rate_session, ask_question, wall_post, upload_photo, likes_milestone, wall_comment, connect, vote_poll, complete_interests, chat_session, upload_story, game_spin, game_trivia | `points_log` agregado por attendee + action |
| 25 | **Log Completo de Puntos** | Nombre, Email, Accion, Puntos (+/-), Referencia tipo, Referencia nombre (sesion/sponsor/post/game), Fecha | `points_log` con joins a referencia |
| 26 | **Resultados Trivia (Live Game)** | Juego titulo, Sesion, Sponsor (si aplica), Ronda, Pregunta texto, Asistente nombre, Email, Respuesta dada, Respuesta correcta, Correcta? (si/no), Tiempo respuesta (seg), Puntos ronda, Score acumulado, Es ganador?, Fecha | `live_game_participants` WHERE game.type='trivia' + `live_games` |
| 27 | **Resultados Ruleta (Spin)** | Juego titulo, Sesion, Sponsor, Sector ganado (label), Puntos del sector, Total participantes, Nombre, Email, Fecha | `live_game_participants` WHERE game.type='spin' + `live_games` config.sectors |
| 28 | **Resultados Jackpot (Sorteo)** | Juego titulo, Sesion, Sponsor, Premio titulo, Premio descripcion, Total participantes elegibles, Audiencia (all/presencial/virtual), Ganador nombre, Ganador email, Ganador foto, Claim code, Estado reclamo, Fecha inicio, Fecha fin | `live_games` WHERE type='jackpot' + `live_game_participants` WHERE is_winner=true + `reward_redemptions` |
| 29 | **Premios Canjeados (Catalogo de Puntos)** | Asistente nombre, Email, Premio nombre, Sponsor del premio, Puntos gastados, Estado (pending/confirmed/expired/cancelled), Token QR, Fecha solicitud, Fecha expiracion, Confirmado por (staff nombre), Fecha confirmacion | `reward_redemptions` WHERE source_type IS NULL + `rewards` WHERE type='catalog' |
| 30 | **Premios de Juegos (Golden Tickets)** | Asistente nombre, Email, Premio nombre, Fuente (Jackpot/Foto Mas Votada/Premio Manual), Juego origen, Claim code (6 chars), Estado (pending/confirmed), Confirmado por (staff), Fecha otorgado, Fecha confirmacion | `reward_redemptions` WHERE claim_code IS NOT NULL + `rewards` WHERE type='prize' |
| 31 | **Passport Stamps** | Asistente nombre, Email, Stand visitado, Tier sponsor, Fecha stamp, Passport completo? (si/no), Total stamps vs requeridos | `passport_stamps` + evento config (passport_required_stamps) |
| 32 | **Catalogo de Rewards** | Premio nombre, Descripcion, Sponsor, Tipo (catalog/prize), Costo en puntos, Stock inicial, Canjeados, Stock restante, Roles permitidos, Habilitado, Game asociado | `rewards` |
| 33 | **Resumen de Juegos** | Juego titulo, Tipo (spin/trivia/jackpot), Sesion, Sponsor, Estado, Audiencia target, Total participantes, Ganador(es), Fecha inicio, Fecha fin, Auto-post wall | `live_games` + conteos de `live_game_participants` |

**Nota sobre #26-28:** Cada tipo de juego tiene estructura diferente en `live_game_participants.answer` (JSON):
- Spin: `{sector: int}` — el index del sector donde cayo
- Trivia: `{option: int, correct: bool, time_elapsed: float}` — por ronda
- Jackpot: null (solo `is_winner` importa)

**Nota sobre #29 vs #30:** Son exports SEPARADOS porque responden preguntas diferentes:
- #29: "Quien gasto sus puntos y en que" (canje voluntario, tiene expiracion 5min)
- #30: "Quien gano premios en juegos" (otorgado por sistema, tiene claim_code, sin expiracion)

### TAB 6: Social y Fotos (4 exports)

| # | Export | Columnas del CSV | Tabla(s) fuente |
|---|--------|-----------------|-----------------|
| 34 | **Muro Social** | Autor nombre, Email, Texto, Tiene foto (si/no), URL foto, Estado (pending/published/hidden), Likes, Comentarios, Fecha | `wall_posts` |
| 35 | **Comentarios del Muro** | Post (autor + extracto), Comentarista nombre, Email, Comentario, Estado (published/hidden), Fecha | `wall_comments` + `wall_posts` |
| 36 | **Fotos del Evento** | Autor nombre, Email, URL foto, Caption, Estado moderacion (pending/approved/rejected), Es oficial, Likes, Es entrada concurso, Es ganador concurso, Posicion concurso, Fecha | `event_photos` |
| 37 | **Concurso de Fotos** | Posicion, Participante nombre, Email, URL foto, Votos (likes_count), Es ganador, Puntos otorgados (contest_winner_points), Fecha subida | `event_photos` WHERE is_contest_entry=true ORDER BY likes_count DESC |

### TAB 7: Networking (2 exports)

| # | Export | Columnas del CSV | Tabla(s) fuente |
|---|--------|-----------------|-----------------|
| 38 | **Conexiones y Solicitudes** | Solicitante nombre, Solicitante email, Solicitante empresa, Receptor nombre, Receptor email, Receptor empresa, Estado (pending/accepted/ignored), Mensaje, Fecha solicitud, Fecha respuesta | `contact_requests` |
| 39 | **Intereses de Matchmaking** | Asistente nombre, Email, Intereses seleccionados (lista), Total intereses, Fecha seleccion | `attendee_interests` + `onboarding_survey_options` |

**Nota:** No existe tabla `matchmaking_matches`. El matching se calcula on-the-fly por overlap de `attendee_interests`. El export #39 permite al organizador analizar que intereses son mas populares.

### TAB 8: Comunicaciones (3 exports)

| # | Export | Columnas del CSV | Tabla(s) fuente |
|---|--------|-----------------|-----------------|
| 40 | **Push Notifications** | Tipo (announcement/scheduled/module_update), Titulo, Body, Roles target, Fecha envio, Total enviados, Entregados, Abiertos, Fallidos, % delivery, % open rate, Errores frecuentes | `push_notification_logs` + `scheduled_notifications` + `announcements` |
| 41 | **Emails Enviados** | Destinatario, Email, Template/Mailable, Asunto, Evento, Enviado (si/no), Abierto, Click, Fecha envio, Fecha apertura | `email_logs` (si existe) |
| 42 | **Webhooks Enviados** | Endpoint URL, Evento trigger, Payload (resumido), Status code respuesta, Intentos, Ultimo intento, Fecha | `webhook_logs` (si existe via DispatchWebhookJob) |

### TAB 9: Auditoria Admin (2 exports)

| # | Export | Columnas del CSV | Tabla(s) fuente |
|---|--------|-----------------|-----------------|
| 43 | **Audit Log Admin** | Admin nombre, Email, Accion, Target tipo, Target ID, Valores anteriores (JSON), Valores nuevos (JSON), IP, Fecha | `admin_audit_logs` |
| 44 | **Cambios de Rol y Bans** | Asistente nombre, Email, Tipo (role_change/ban/unban), De rol / Razon ban, A rol / Expira, Ejecutado por, Fecha | `attendee_role_changes` + `attendee_bans` |

---

## Resumen Ejecutivo por Tab

| Tab | Exports | Responde a |
|-----|---------|-----------|
| 1. Asistentes y Acceso | 6 | Quien vino, cuando, donde estuvo, cuanto duro, consent |
| 2. Sesiones y Contenido | 5 | Que sesiones funcionaron, ratings, tiempos reales, fuentes |
| 3. Engagement en Vivo | 5 | Chat, Q&A, polls, encuestas, engagement score per-attendee |
| 4. Patrocinadores y Leads | 7 | ROI completo: leads, contactos, visitas, trivia, equipo |
| 5. Gamificacion y Juegos | 10 | Leaderboard, puntos, trivia, ruleta, jackpot, premios, passport |
| 6. Social y Fotos | 4 | Muro, comentarios, fotos, concurso con ranking |
| 7. Networking | 2 | Conexiones, intereses de matchmaking |
| 8. Comunicaciones | 3 | Push, emails, webhooks |
| 9. Auditoria | 2 | Admin actions, bans, role changes |

**Total: 44 exports descargables**

---

## Metricas de Resumen (Header del Data Center)

Cards en la parte superior de la pagina, siempre visibles:

```
Fila 1: [Registrados]  [Check-ins]  [% Asistencia]  [Sesiones totales]
Fila 2: [Leads]        [Conexiones]  [Mensajes chat] [Fotos subidas]
Fila 3: [Puntos Tot.]  [Premios canjeados] [Rating Avg]  [Push Open%]
Fila 4: [Virtuales activos] [Presenciales en sala] [Juegos jugados] [Posts muro]
```

16 stat cards. Query directo, sin queue. Son contexto, no exportables.

---

## Fases de Implementacion

### FASE 0: Infraestructura de Export (2h)
**Objetivo:** Base reutilizable para los 44 exports

```
app/
  Jobs/Exports/              <-- Namespace dedicado
    BaseExportJob.php        <-- Job abstracto: queue, timeout, notification, cleanup
  Services/
    ExportService.php        <-- Registra exports, genera filename, signed URL, throttle
  Filament/Pages/
    DataCenter.php           <-- Page principal con tabs
  Console/Commands/
    CleanupExportsCommand.php
```

**BaseExportJob** (patron):
```php
abstract class BaseExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue = 'exports';
    public $timeout = 300;
    public $tries = 1;

    public function __construct(
        public int $eventId,
        public int $userId,       // quien solicito
        public string $format,    // csv | xlsx
        public ?array $filters,   // fecha_desde, fecha_hasta, session_id, sponsor_id, etc.
    ) {}

    abstract protected function getData(): Collection;
    abstract protected function getHeaders(): array;
    abstract protected function getFilename(): string;

    public function handle(): void
    {
        $data = $this->getData();
        $path = $this->generateFile($data);
        $this->notifyUser($path);
    }

    // generateFile() -> maatwebsite/excel o fputcsv segun format
    // notifyUser() -> Filament DatabaseNotification con link de descarga
    // failed() -> Notification de error con mensaje
}
```

**ExportService:**
```php
class ExportService
{
    // Validar max 2 exports en queue para este evento
    // Despachar job
    // Generar signed URL (24h expiry)
    // Cleanup: borrar exports > 48h
}
```

**Comando cleanup:**
```php
// app/Console/Kernel.php
$schedule->command('exports:cleanup')->dailyAt('03:00');
```

### FASE 1: Data Center Page + Tab Asistentes (2.5h)
**Objetivo:** Filament page funcional con primer tab completo

- `DataCenter.php` — Filament Page con:
  - Select de evento (filtro global)
  - Date range picker (opcional)
  - Format toggle (CSV/Excel)
  - 16 stat cards en header
  - 9 tabs con Livewire
- 6 export jobs del Tab 1:
  - `ExportAttendeesMasterJob` (refactor de AttendeesExport existente, agrega campos dinamicos)
  - `ExportCheckinsJob`
  - `ExportRoomMovementsJob` (refactor de ExportRoomAttendanceJob existente)
  - `ExportSessionAttendanceJob` (INCLUYE duracion virtuales)
  - `ExportSilentDiscoJob`
  - `ExportConsentLogJob`

### FASE 2: Tabs Sesiones + Engagement (2.5h)
**Objetivo:** 10 exports de contenido y engagement

- 5 exports Tab 2 (Sesiones):
  - `ExportSessionSummaryJob` (refactor de ExportSessionStatsJob — agrega 3 tiempos, engagement score)
  - `ExportSessionFavoritesJob`
  - `ExportSessionRatingsJob`
  - `ExportSpeakerRatingsJob`
  - `ExportAttendanceBySourceJob`
- 5 exports Tab 3 (Engagement):
  - `ExportChatMessagesJob`
  - `ExportQuestionsJob`
  - `ExportPollVotesJob` (refactor de ExportPollResponsesJob — agrega 4 tipos: multiple_choice, open_text, rating, word_cloud)
  - `ExportSurveyResponsesJob`
  - `ExportEngagementScoreJob` (score 0-100 per attendee per session)

### FASE 3: Tab Patrocinadores (2.5h)
**Objetivo:** 7 exports de ROI para sponsors

- `ExportLeadsMasterJob`
- `ExportLeadEditsJob`
- `ExportSponsorContactFormsJob`
- `ExportStandVisitsJob`
- `ExportStandTriviaJob`
- `ExportSponsorFavoritesJob`
- `ExportStandMembersJob`

**CRITICO:** El organizador le entrega estos CSVs al patrocinador como prueba de ROI. Cada export incluye nombre, email, empresa, cargo del lead/visitante.

### FASE 4: Tab Gamificacion y Juegos (3h)
**Objetivo:** 10 exports — el tab mas grande y detallado

- `ExportLeaderboardJob` (desglose por las 16 acciones)
- `ExportPointsLogJob` (incluye puntos negativos por refund de premios expirados)
- `ExportTriviaResultsJob` (por ronda, por pregunta, tiempo respuesta, score acumulado)
- `ExportSpinResultsJob` (sector label, puntos, todos los participantes)
- `ExportJackpotResultsJob` (ganador, claim code, estado reclamo, total elegibles, audiencia type)
- `ExportRewardRedemptionsJob` (canjes de puntos: quien, que, confirmado por, estado)
- `ExportGamePrizesJob` (golden tickets: fuente, claim code, estado)
- `ExportPassportStampsJob` (stamps + progreso vs requeridos)
- `ExportRewardsCatalogJob` (catalogo: stock, canjeados, disponibles)
- `ExportGamesSummaryJob` (resumen de todos los juegos del evento)

**Detalle clave por tipo de juego:**

**Trivia (#26):** Cada fila = 1 respuesta de 1 asistente en 1 ronda. Columnas: juego, ronda, pregunta, respuesta dada, respuesta correcta, correcta?, tiempo (seg desde `answer.time_elapsed`), puntos ronda, score acumulado (SUM por attendee), is_winner. El organizador puede ver exactamente quien respondio que, en cuanto tiempo, y quien gano.

**Ruleta (#27):** Todos participan, todos ganan algo (o nada si el sector tiene 0 puntos). Cada fila = 1 participante. Columnas: juego, sector label (del `config.sectors[answer.sector]`), puntos del sector, nombre, email. El organizador ve la distribucion de premios.

**Jackpot (#28):** Solo 1 ganador. El export muestra: total de participantes que se unieron (Redis pool), audiencia target (presencial/virtual/all), el ganador con claim_code, estado del reclamo (pending/confirmed). Si el ganador no reclamo, el organizador lo sabe.

**Premios canjeados (#29) vs Golden tickets (#30):** Separados porque:
- #29 = canje voluntario con puntos. Tiene `expires_at` (5 min). Si expiro, los puntos se devolvieron (PointsLog negativo).
- #30 = premio otorgado por juego/concurso. Tiene `claim_code` (6 chars). Sin expiracion. `source_type` indica origen (live_game, event_photo, manual).

### FASE 5: Tabs Social + Networking (2h)
**Objetivo:** 6 exports

- Social (4):
  - `ExportWallPostsJob`
  - `ExportWallCommentsJob`
  - `ExportEventPhotosJob`
  - `ExportPhotoContestJob` (ranking por likes, ganadores marcados)
- Networking (2):
  - `ExportConnectionsJob`
  - `ExportMatchmakingInterestsJob`

### FASE 6: Tabs Comunicaciones + Auditoria (1.5h)
**Objetivo:** 5 exports

- Comunicaciones (3):
  - `ExportPushNotificationsJob`
  - `ExportEmailsJob`
  - `ExportWebhooksJob`
- Auditoria (2):
  - `ExportAuditLogJob`
  - `ExportRoleChangesAndBansJob`

### FASE 7: Export Maestro + Polish (2h)
**Objetivo:** El "descargar todo" y pulido final

- **Export Maestro (ZIP):** Un boton "Descargar Todo" que:
  1. Despacha los 44 jobs al queue via `Bus::batch()`
  2. Cuando todos terminan (`then` callback), genera ZIP con 44 archivos
  3. Notifica con link de descarga del ZIP
  4. Nombrado: `{evento_slug}_data_center_{fecha}.zip`
  5. Progress: "Generando 28/44..."

- **Filtros por sub-entidad:** Algunos exports soportan filtro adicional:
  - Chat/Q&A/Polls: filtrar por session_id
  - Leads/Visitas/Trivia: filtrar por sponsor_id
  - Juegos: filtrar por game_id
  - Puntos: filtrar por action type

- **Permisos:** Solo roles `admin` y `admin_analytics` ven el Data Center
- **Rate limiting:** Max 1 export maestro por evento cada 30 min
- **Indicador:** Badge en notificacion "Generando 28/44..."

---

## Estructura de Archivos Final

```
app/
  Jobs/Exports/
    BaseExportJob.php
    // Tab 1: Asistentes (6)
    ExportAttendeesMasterJob.php
    ExportCheckinsJob.php
    ExportRoomMovementsJob.php
    ExportSessionAttendanceJob.php
    ExportSilentDiscoJob.php
    ExportConsentLogJob.php
    // Tab 2: Sesiones (5)
    ExportSessionSummaryJob.php
    ExportSessionFavoritesJob.php
    ExportSessionRatingsJob.php
    ExportSpeakerRatingsJob.php
    ExportAttendanceBySourceJob.php
    // Tab 3: Engagement (5)
    ExportChatMessagesJob.php
    ExportQuestionsJob.php
    ExportPollVotesJob.php
    ExportSurveyResponsesJob.php
    ExportEngagementScoreJob.php
    // Tab 4: Sponsors (7)
    ExportLeadsMasterJob.php
    ExportLeadEditsJob.php
    ExportSponsorContactFormsJob.php
    ExportStandVisitsJob.php
    ExportStandTriviaJob.php
    ExportSponsorFavoritesJob.php
    ExportStandMembersJob.php
    // Tab 5: Gamificacion (10)
    ExportLeaderboardJob.php
    ExportPointsLogJob.php
    ExportTriviaResultsJob.php
    ExportSpinResultsJob.php
    ExportJackpotResultsJob.php
    ExportRewardRedemptionsJob.php
    ExportGamePrizesJob.php
    ExportPassportStampsJob.php
    ExportRewardsCatalogJob.php
    ExportGamesSummaryJob.php
    // Tab 6: Social (4)
    ExportWallPostsJob.php
    ExportWallCommentsJob.php
    ExportEventPhotosJob.php
    ExportPhotoContestJob.php
    // Tab 7: Networking (2)
    ExportConnectionsJob.php
    ExportMatchmakingInterestsJob.php
    // Tab 8: Comunicaciones (3)
    ExportPushNotificationsJob.php
    ExportEmailsJob.php
    ExportWebhooksJob.php
    // Tab 9: Auditoria (2)
    ExportAuditLogJob.php
    ExportRoleChangesAndBansJob.php
  Services/
    ExportService.php
  Filament/Pages/
    DataCenter.php
  Console/Commands/
    CleanupExportsCommand.php
```

**Total: 44 Job classes + 1 BaseExportJob + 1 Service + 1 Page + 1 Command = 48 archivos**

---

## Dependencias

Ya instalado:
- `maatwebsite/excel` v3.1
- `Filament Notifications` (DatabaseNotification)
- `Queue database` con tabla `jobs`
- `Job Batching` con tabla `job_batches`
- `ExportSessionStatsJob` como patron de referencia
- `ExportPollResponsesJob` como patron de referencia
- `ExportRoomAttendanceJob` como patron de referencia
- `AttendeesExport` como patron maatwebsite

Nuevo:
- Agregar queue "exports" al worker de produccion
- Comando `exports:cleanup` en Kernel scheduler
- Los 3 export jobs existentes se refactorizan para extender BaseExportJob

---

## Que NO incluye este roadmap

- Graficas/charts en Filament (eso es P4 Filament Polish)
- Dashboard en tiempo real (eso es Event Pulse, ya COMPLETO)
- API publica de analytics (no necesario ahora)
- Exports automaticos programados (nice-to-have futuro)
- Banner click tracking (no existe tabla, seria nueva feature)
- Sponsor view/impression tracking (no existe tabla)

---

## Criterio de Exito

1. Organizador abre Data Center, ve 16 conteos del evento
2. Hace click en "Descargar Leads", recibe notificacion en 5-15s con link
3. Descarga CSV con nombre, email, empresa, cargo de cada lead
4. El evento en curso NO se ve afectado (queue "exports" separado)
5. Descarga "Resultados Trivia" y ve quien respondio que, en cuanto tiempo, quien gano
6. Descarga "Premios Canjeados" y ve quien canjeo que, si staff confirmo, o si expiro
7. Descarga "Asistencia Sesion" y ve cuanto tiempo duro cada virtual conectado
8. "Descargar Todo" genera ZIP con 44 archivos en <3 min
9. Silent disco export muestra quienes NO respondieron al pulse check
10. Jackpot export muestra ganador, claim code, y si ya reclamo el premio

---

## Orden de ataque recomendado

```
F0 (2h)    -> Base: BaseExportJob, ExportService, DataCenter page, cleanup
F1 (2.5h)  -> Asistentes (6 exports) — el tab mas pedido
F3 (2.5h)  -> Patrocinadores (7 exports) — ROI, lo que vende
F4 (3h)    -> Gamificacion (10 exports) — premios, quien gano que
F2 (2.5h)  -> Sesiones + Engagement (10 exports) — contenido y duracion virtuales
F5 (2h)    -> Social + Networking (6 exports)
F6 (1.5h)  -> Comunicaciones + Auditoria (5 exports)
F7 (2h)    -> Export maestro ZIP + filtros + polish
```

Sponsors (F3) y Gamificacion (F4) antes de Sesiones (F2) porque:
- El ROI del sponsor genera dinero
- "Quien gano que" es la pregunta #1 post-evento
- La duracion de virtuales es importante pero secundaria
