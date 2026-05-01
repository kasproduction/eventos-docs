# ROADMAP: Data Center (Centro de Datos) — CERRADO

> **Prioridad:** P2
> **Estado:** ✓ MODULO 100% COMPLETO al 26-04-2026. Solo pendiente deploy a VPS-3 cuando sea momento.
> **Cerrado:** 26 abril 2026 — 5 iteraciones, ~25h trabajo total
> **Lo unico no implementado:** Email tracking real (depende de provider SES/Mailgun en deploy) y embed publico (descartado, redundante con Event Pulse)
> **Principio:** El evento NUNCA se detiene por una metrica. Todo via queue.
> **Implementado:** 25 abril 2026 — 44 export jobs, 7 endpoints API, SPA standalone, 31 tests
> **Auditado contra:** Codigo real de eventos-backend (25 abril 2026)

---

## Filosofia

No es un "dashboard bonito". Es un **centro de datos operativo** donde el organizador (y el patrocinador) descarga absolutamente todo lo que el sistema midio. Cada interaccion, cada click, cada lead, cada voto, cada segundo de permanencia. Si paso en EventOS, se puede descargar.

La competencia (Cisco Webex Events $88K, ICE360 $49M COP) entrega reportes detallados. Este no puede ser un punto flaco.

---

## Arquitectura

> **Decision 2026-04-25:** Data Center es SPA standalone, NO Filament Page.
> Filament = administrador de recursos (CRUD). Data Center = generador de recursos (graficas, insights, exports).
> Mismo patron que Mission Control y Event Pulse.

```
[Filament Sidebar]
    |
    +-- Link "Data Center" → abre SPA en nueva tab
    |
    +-- Auth: session cookie Laravel (misma que Filament)
    +-- Rol: super_admin, org_admin, event_admin (moderator NO)

[SPA standalone: /data-center/{eventSlug}]
    |
    +-- Secciones por categoria (9 secciones, scroll o nav lateral)
    |     |
    |     +-- Graficas + stat cards (Chart.js o Recharts)
    |     +-- Boton "Descargar CSV" por cada dataset
    |     +-- Cada boton llama endpoint API que despacha Job al queue
    |     +-- Notificacion en la SPA cuando el export esta listo
    |     +-- Descarga: signed URL a R2 (24h expiry)
    |
    +-- Filtros: Rango de fechas, sesion especifica, sponsor
    +-- Formato: CSV (default) o Excel
    +-- Diseno: Lumina Noir/Lux, consistente con Pulse y MC
    +-- Stack: HTML/JS/CSS standalone (o React si la complejidad lo amerita)
    +-- Vive en: public/data-center/ o repo separado
```

### Auth y permisos

La SPA reutiliza la session cookie de Laravel/Filament via **Sanctum stateful API**.
No tokens separados. CSRF no necesario (`api/*` esta excluido en `validateCsrfTokens`).

```
Admin login en /admin (Filament)
  → Cookie de sesion Laravel creada
  → Click "Data Center" en sidebar → nueva tab
  → SPA carga (assets estaticos desde public/data-center/)
  → Cada fetch incluye credentials: 'include' (envia session cookie)
  → Sanctum detecta request same-origin → usa session guard
  → Middleware valida: sesion activa + rol con permiso
  → Si no hay sesion → SPA recibe 401 → redirect /admin/login
```

**Por que funciona sin CSRF:**

- `bootstrap/app.php` tiene `.validateCsrfTokens(except: ['api/*'])`
- Las rutas Data Center viven en `api/v1/data-center/*` → excluidas de CSRF
- `.statefulApi()` + `auth:sanctum` reconoce same-origin y usa session cookie
- La SPA SOLO necesita `credentials: 'include'` en cada fetch

**Init de la SPA:**

```javascript
// Fetch wrapper — sin CSRF, sin tokens, solo session cookie
async function dcFetch(url, options = {}) {
  const res = await fetch(url, {
    ...options,
    credentials: "include",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
      ...options.headers,
    },
  });
  if (res.status === 401) window.location.href = "/admin/login";
  if (res.status === 403) showError("Sin permisos para este evento");
  return res;
}
```

**Por que session cookie y no Bearer token:**

- MC usa HMAC→Bearer porque necesita funcionar SIN login Filament (TV del venue, staff tecnico)
- Pulse usa `ep_*` query token por la misma razon (pantalla gigante en el evento)
- Data Center SIEMPRE lo usa un admin ya logueado en Filament → session cookie es lo natural
- Sanctum stateful API esta disenado exactamente para este caso (SPA same-origin + session)

| Rol         | Acceso Data Center | Scope                      |
| ----------- | ------------------ | -------------------------- |
| super_admin | Si                 | Todos los eventos          |
| org_admin   | Si                 | Eventos de su organizacion |
| event_admin | Si                 | Solo su evento             |
| moderator   | No                 | Solo tiene MC              |

### Coexistencia Filament + SPAs

| Herramienta     | Tipo                  | Auth                              | CSRF                  | Vive en                 |
| --------------- | --------------------- | --------------------------------- | --------------------- | ----------------------- |
| Filament        | Admin CRUD            | Session Laravel                   | Blade (automatico)    | /admin                  |
| Mission Control | Operacion sesion      | HMAC → Sanctum Bearer             | No (Bearer token)     | public/mission-control/ |
| Event Pulse     | Dashboard RT          | `ep_*` query token                | No (solo GET)         | public/event-pulse/     |
| Data Center     | Analytics post-evento | Session cookie + Sanctum stateful | No (`api/*` excluido) | public/data-center/     |

Filament es el hub central. Las SPAs son las herramientas especializadas que se abren desde Filament.
Cada SPA usa el mecanismo de auth apropiado a su contexto de uso.

### API Endpoints (Laravel — VPS-1/VPS-2)

La SPA consume estos endpoints. Todos protegidos por session cookie + role middleware.

```php
// routes/data-center.php (incluido desde routes/api.php)
Route::middleware(['auth:sanctum', 'role:super_admin,org_admin,event_admin'])
    ->prefix('v1/data-center/{event}')
    ->group(function () {
        Route::get('/stats', [DataCenterController::class, 'stats']);           // 16 stat cards (Redis cache)
        Route::post('/export', [DataCenterController::class, 'export']);        // Dispatch export job
        Route::get('/exports', [DataCenterController::class, 'listExports']);   // Exports pendientes/completados
        Route::get('/notifications', [DataCenterController::class, 'notifications']); // Polling notificaciones
        Route::delete('/export/{id}', [DataCenterController::class, 'cancelExport']); // Cancelar export en cola
    });
```

**Cada endpoint es liviano:**

- `stats` → Redis GET, 0 queries MySQL (~1ms)
- `export` → dispatch job a Redis queue (~1ms)
- `exports` → query a `notifications` table (~5ms)
- `notifications` → polling cada 5s, query simple (~2ms)

**Ningun endpoint ejecuta queries pesados.** El trabajo real ocurre en VPS-3.

### Integracion con Filament (link, no page)

```php
// app/Providers/Filament/AdminPanelProvider.php
->navigationItems([
    NavigationItem::make('Data Center')
        ->url(fn () => '/data-center/' . (Filament::getTenant()?->slug ?? ''))
        ->icon('heroicon-o-chart-bar-square')
        ->openUrlInNewTab()
        ->group('Herramientas')
        ->visible(fn () => auth()->user()->hasRole(['super_admin', 'org_admin', 'event_admin'])),
])
```

Filament solo tiene un link en el sidebar. La SPA corre independiente.

### Nginx config (SPA fallback)

Sin esto, recargar el browser en `/data-center/evento-demo` devuelve 404.
Mismo patron que MC y Pulse.

```nginx
# Nginx (VPS-1/VPS-2) — agregar al server block
location /data-center/ {
    try_files $uri $uri/ /data-center/index.html;
}

# Las rutas API siguen pasando por PHP-FPM normalmente
# porque /api/v1/data-center/* matchea el location de Laravel primero
```

**En dev (Laragon):** No necesita config especial — Laragon ya maneja `public/` con el vhost.

### Performance Strategy (CRITICO — 10K usuarios activos)

El evento NO puede parar porque alguien pidio un export. Cinco capas de proteccion.

#### Capa 1: VPS-3 Worker de Exports (AISLAMIENTO TOTAL)

**Problema:** Si exports comparten PHP-FPM con la API, un query de 300K filas satura workers y tumba el check-in.
**Problema inverso:** Si movemos Filament entero a VPS-3 y se cae, el organizador no puede banear, moderar ni operar Mission Control. Inaceptable.
**Solucion:** VPS-1/VPS-2 mantienen Filament completo. VPS-3 es SOLO un worker headless que procesa exports.

```
INTERNET
   |
CLOUDFLARE (WAF + LB + CDN)
   |
   +--- api.eventos.com ---> VPS-1 + VPS-2 (round robin, failover)
   |                          |
   |                    [API + Socket.IO + Filament]
   |                    [Queue: default (push, email, games)]
   |                    [Lee/Escribe: PRIMARY MySQL]
   |                    [10K asistentes + 1-5 admins]
   |
   +--- (sin HTTP) ----------> VPS-3 (worker headless)
                                |
                          [Queue: exports SOLAMENTE]
                          [NO Nginx, NO HTTP, NO Filament]
                          [Lee: REPLICA MySQL (read-only)]
                          [Escribe: solo archivos a R2]
   |
   +--- Managed Services DO sao1 (VPC privada, < 1ms RTT)
         |
         +-- DO Managed MySQL PRIMARY ($15/mes) <--- VPS-1, VPS-2
         |      |
         |      +-- DO Managed MySQL REPLICA ($15/mes) <--- VPS-3
         |
         +-- DO Managed Redis HA ($15/mes) <--- todos (cache + queue broker)
         |
         +-- Cloudflare R2 (storage) <--- todos (exports, fotos, docs)
```

**Principio: El admin opera desde VPS-1/VPS-2 (mismo Filament de siempre). Los queries pesados de export corren en VPS-3 contra la replica.**

**Flujo del Data Center:**

1. Organizador abre Data Center en Filament (VPS-1 o VPS-2)
2. Ve 16 stats cacheados en Redis (0 queries al MySQL)
3. Click "Descargar Leads" --> despacha job al queue "exports" (Redis)
4. VPS-3 toma el job del queue (Upstash compartido)
5. VPS-3 ejecuta query pesado contra REPLICA MySQL
6. VPS-3 genera CSV --> sube a Cloudflare R2
7. VPS-3 envia Filament Notification al admin con link
8. Admin descarga CSV (link firmado a R2)

| VPS   | Que corre                               | Quien lo usa            | Lee de      | Escribe en         |
| ----- | --------------------------------------- | ----------------------- | ----------- | ------------------ |
| VPS-1 | API + Socket + Filament + Queue:default | 10K asistentes + admins | Primary     | Primary            |
| VPS-2 | API + Socket + Filament + Queue:default | 10K asistentes + admins | Primary     | Primary            |
| VPS-3 | Queue:exports (worker headless)         | Nadie directamente      | **REPLICA** | Solo R2 (archivos) |

**Si VPS-3 se cae:**

- El evento sigue perfecto (VPS-1/VPS-2 no se enteran)
- Filament sigue en VPS-1/VPS-2 (bans, MC, polls, moderacion — todo)
- Export jobs se acumulan en Redis (Upstash tiene persistencia)
- Cuando VPS-3 vuelve, procesa cola pendiente automaticamente
- Unico impacto: exports tardan mas

**Si VPS-1 se cae:**

- Cloudflare redirige a VPS-2 en <30s
- VPS-3 sigue procesando exports
- Cero impacto

**Costo:** +$48/mes Droplet-3 DO sao1 (4vCPU/8GB) + $15/mes read replica MySQL = $63/mes extra

**Droplet-3 docker-compose.worker.yml:**

```yaml
services:
  worker:
    build:
      context: ./eventos-backend
      dockerfile: Dockerfile
    command: php artisan queue:work --queue=exports --tries=1 --timeout=300 --sleep=3
    env_file: .env.worker
    deploy:
      resources:
        limits: { cpus: "2", memory: 2048M }
    restart: unless-stopped
```

```env
# .env.worker (Droplet-3 DO sao1)
DB_CONNECTION=mysql
DB_HOST=private-replica-db-eventos.ondigitalocean.com   # SOLO replica via VPC
DB_PORT=25060
DB_DATABASE=eventos_prod
QUEUE_CONNECTION=redis
REDIS_HOST=private-redis-eventos.ondigitalocean.com     # Mismo Redis compartido via VPC
REDIS_PORT=25061
FILESYSTEM_DISK=r2                                       # Exports van a R2
```

#### Capa 2: Read Replica (MySQL)

DO Managed MySQL soporta read replicas:

- Droplet-1/Droplet-2 --> `private-db-eventos.ondigitalocean.com` (read + write, VPC)
- Droplet-3 --> `private-replica-db-eventos.ondigitalocean.com` (read only, VPC)

VPS-3 NUNCA escribe en MySQL. Solo lee datos y sube archivos a R2.

**Delay de replica:** ~1-2 segundos. Datos de hace 2 seg para exports = perfectamente valido.

#### Capa 3: Queue separado

```
VPS-1/VPS-2:  php artisan queue:work --queue=default --tries=3
              --> Push, emails, gamificacion, webhooks, attendance

VPS-3:        php artisan queue:work --queue=exports --tries=1 --timeout=300
              --> SOLO export jobs, max 2 simultaneos
```

Ambos usan DO Managed Redis como broker (VPC compartida). Workers en Droplets diferentes. Si Droplet-3 muere, jobs esperan en Redis.

#### Capa 4: Query optimization

Cada export job sigue estas reglas:

- `->cursor()` para CSV streaming (write row by row, ~2MB RAM)
- `->select(['id','name','email'])` — no SELECT \*
- `->with(['user:id,name,email,phone,company,job_title'])` — eager load con select
- Todos los joins resueltos en 1-2 queries max (no N+1)
- Indices en `(event_id, created_at)` minimo por tabla

**Pico de viewers (sweep line en PHP, no query por minuto):**

```php
$records = SessionAttendance::where('session_id', $id)
    ->select('joined_at', 'left_at')->cursor();
$events = [];
foreach ($records as $r) {
    $events[] = [$r->joined_at, +1];
    $events[] = [$r->left_at ?? $sessionEnd, -1];
}
sort($events);
$peak = $current = 0;
foreach ($events as [$time, $delta]) {
    $current += $delta;
    if ($current > $peak) { $peak = $current; $peakMinute = $time; }
}
// 1 query + O(n log n) PHP. 50K records = ~50ms.
```

#### Capa 5: Redis cache para stat cards

Las 16 cards del Data Center NO hacen query en vivo:

```php
Cache::remember("dc:stats:{$eventId}", 60, function () {
    return [
        'registered' => Attendee::where('event_id', $id)->count(),
        'checked_in' => Attendee::...whereNotNull('checked_in_at')->count(),
        // ...12 cards mas
    ];
});
```

Max 60 segundos de delay. Cero impacto en primary.

#### Resumen

```
Capa 1: VPS-3 worker     --> Queries pesados en servidor aparte (+$5/mes)
Capa 2: Read Replica      --> VPS-3 lee de replica MySQL (+$10/mes)
Capa 3: Queue separado    --> Export workers en VPS-3, evento en VPS-1/VPS-2
Capa 4: Query optimize    --> Cursor streaming, eager load, indices
Capa 5: Redis cache       --> Stat cards cacheadas 60s
```

**Resultado:** 10K activos + organizador descargando 44 exports = 0 impacto.
**Costo total infra:** ~$231/mes (vs $168 base). $63 extra por aislamiento total (Droplet-3 + read replica).

---

## Inventario COMPLETO de Metricas (42 exports)

Auditado contra modelos, migraciones y controladores reales.

### TAB 1: Asistentes y Acceso (6 exports)

| #   | Export                                           | Columnas del CSV                                                                                                                                                                                                                          | Tabla(s) fuente                                                             |
| --- | ------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------- |
| 1   | **Asistentes Master**                            | Nombre, Email, Telefono, Empresa, Cargo, Rol, Tags[], Check-in, Checkout, Badge impreso, Fuente invitacion (csv/link/qr/manual), Networking visible, Lead tier, Fecha registro, + TODOS los campos dinamicos de registration_field_values | `attendees` + `users` + `registration_field_values` + `registration_fields` |
| 2   | **Check-ins Evento**                             | Nombre, Email, Rol, Hora check-in, Hora checkout, Duracion total en venue (min), Metodo checkout (manual/auto_end_day/smart)                                                                                                              | `attendees` (checked_in_at, checked_out_at)                                 |
| 3   | **Movimientos por Sala**                         | Nombre, Email, Sala, Totem (nombre), Tipo (checkin/checkout), Metodo (qr_scan/manual/auto_room_change/auto_end_day/auto_end_session), Hora scan, Flags (fuera_horario/cambio_salon/inferido/cola_offline), Notas                          | `room_movements` + `room_totems` + `event_rooms`                            |
| 4   | **Asistencia por Sesion (Presencial + Virtual)** | Sesion, Nombre, Email, Fuente (app/web/kiosko/manual), Hora entrada, Hora salida, Duracion (min), Minuto de abandono (left_at - actual_start_at), % sesion visto, Aun conectado (si/no)                                                   | `session_attendances` + `event_sessions` (actual_start_at)                  |
| 5   | **Silent Disco (Pulse Checks)**                  | Check ID, Sala, Sesiones incluidas, Fecha disparo, TTL (seg), Total en sala al momento, Total respondieron, % respuesta, Lista de quienes NO respondieron (nombre, email)                                                                 | `attendance_checks` + `attendance_check_responses` + `room_attendee_states` |
| 6   | **Consent Log (GDPR/Ley 1581)**                  | Nombre, Email, IP, User Agent, Fecha aceptacion                                                                                                                                                                                           | `consent_logs`                                                              |

**Nota sobre #4:** Este export es CLAVE para virtuales. `session_attendances` tiene `joined_at`, `left_at`, `duration_seconds` y `source`. Si `left_at IS NULL` el asistente sigue conectado. Esto responde "cuanto tiempo duro viendo un virtual".

### TAB 2: Sesiones y Contenido (5 exports)

| #   | Export                    | Columnas del CSV                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        | Tabla(s) fuente                                                                    |
| --- | ------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------- |
| 7   | **Resumen de Sesiones**   | Sesion, Tipo (keynote/panel/workshop/break/networking), Track, Sala, Speakers, Fecha, Hora inicio programada, Hora inicio real (actual_start_at), Hora fin real (actual_end_at), Hora ajustada (adjusted_end_at), Duracion real (min), Asistentes unicos, Pico de viewers concurrentes, Minuto del pico, Viewers actuales (left_at IS NULL), Duracion promedio asistente (min), Rating promedio, Total ratings, Mensajes chat, Participantes chat unicos, Preguntas Q&A (total/approved/answered), Votos en polls, Favoritos, Engagement score promedio | `event_sessions` + `SessionStatsService` + pico calculado de `session_attendances` |
| 8   | **Favoritos de Sesiones** | Sesion, Tipo, Fecha sesion, Nombre asistente, Email, Fecha favorito                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     | `session_favorites`                                                                |
| 9   | **Ratings de Sesiones**   | Sesion, Nombre asistente, Email, Rating (1-5), Comentario, Fecha                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        | `session_ratings`                                                                  |
| 10  | **Ratings de Speakers**   | Speaker, Empresa, Sesiones donde participo, Nombre asistente, Email, Rating (1-5), Fecha                                                                                                                                                                                                                                                                                                                                                                                                                                                                | `speaker_ratings` + `session_speaker`                                              |
| 11  | **Asistencia por Fuente** | Sesion, Fuente (app/web/kiosko/manual), Conteo, % del total                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             | `session_attendances` agregado por source                                          |

**Nota sobre #7:** Los 3 tiempos de sesion son criticos: `start_datetime` (programado), `actual_start_at` (cuando realmente inicio), `adjusted_end_at` (si se demoro). El organizador necesita saber si las sesiones cumplieron horario.

### TAB 3: Engagement en Vivo (5 exports)

| #   | Export                                        | Columnas del CSV                                                                                                                                                | Tabla(s) fuente                                                                |
| --- | --------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------ |
| 12  | **Chat por Sesion**                           | Sesion, Autor nombre, Autor email, Mensaje, Tipo (text/image/system), Room, Fecha/hora                                                                          | `chat_messages`                                                                |
| 13  | **Preguntas Q&A**                             | Sesion, Autor nombre, Autor email, Pregunta, Estado (pending/approved/answered/dismissed), Upvotes, Es anonima, Fecha envio, Fecha respuesta (answered_at)      | `session_questions`                                                            |
| 14  | **Polls y Votaciones**                        | Poll titulo, Sesion, Pregunta, Tipo pregunta (multiple_choice/open_text/rating/word_cloud), Opcion elegida / Texto respuesta, Nombre votante, Email, Fecha voto | `live_poll_votes` + `live_poll_options` + `live_poll_questions` + `live_polls` |
| 15  | **Encuestas Post-Evento**                     | Encuesta titulo, Pregunta, Opcion/Respuesta, Nombre, Email, Fecha                                                                                               | `survey_answers` + `survey_questions` + `survey_options`                       |
| 16  | **Engagement Score por Asistente por Sesion** | Sesion, Nombre, Email, Score total (0-100), Asistencia (0-40), Chat (0-15), Poll (0-15), Q&A (0-15), Rating (0-15)                                              | Calculado por `SessionStatsService`                                            |

**Nota:** Se elimino "Reacciones Emoji" como export independiente porque los emojis van por socket sin persistencia en DB. El chat ya incluye type=emoji si se persiste.

### TAB 4: Patrocinadores y Leads (7 exports)

| #   | Export                                      | Columnas del CSV                                                                                                                                                                                            | Tabla(s) fuente                              |
| --- | ------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------- |
| 17  | **Leads Master**                            | Patrocinador, Tier sponsor (platinum/gold/silver/bronze), Vendedor nombre, Vendedor email, Lead nombre, Lead email, Lead telefono, Lead empresa, Lead cargo, Tier lead (hot/warm/cold), Nota, Fecha captura | `leads` + `attendees` + `users` + `sponsors` |
| 18  | **Historial de Ediciones de Leads**         | Lead (nombre+email escaneado), Patrocinador, Campo editado (tier/note), Valor anterior, Valor nuevo, Editado por (nombre), Fecha                                                                            | `lead_edits`                                 |
| 19  | **Formularios de Contacto (Sponsor Leads)** | Patrocinador, Asistente nombre, Email, Servicios interesados (nombres), Mensaje, Fecha envio, Fecha notificacion al sponsor                                                                                 | `sponsor_leads` + `sponsor_services`         |
| 20  | **Visitas a Stands**                        | Patrocinador, Tier, Asistente nombre, Email, Empresa, Cargo, Puntos otorgados (visit_points del sponsor), Fecha visita                                                                                      | `passport_stamps` + `sponsors`               |
| 21  | **Trivia de Stands**                        | Patrocinador, Pregunta, Opciones, Respuesta correcta, Asistente nombre, Email, Respuesta seleccionada, Correcta? (si/no), Puntos bonus, Fecha                                                               | `stand_trivia_answers` + `stand_trivias`     |
| 22  | **Favoritos de Sponsors**                   | Patrocinador, Tier, Asistente nombre, Email, Fecha                                                                                                                                                          | `sponsor_favorites`                          |
| 23  | **Equipo de Stand (Staff)**                 | Patrocinador, Miembro nombre, Email, Invitado por, Estado (pending/active/removed), Fecha invitacion, Fecha ingreso                                                                                         | `stand_members`                              |

**Nota:** Este tab es CRITICO. Los patrocinadores pagan. Si no pueden demostrar ROI, no renuevan. Cada export incluye datos de contacto completos. El export #17 es el mas importante de todo el Data Center.

### TAB 5: Gamificacion y Juegos (10 exports)

| #   | Export                                     | Columnas del CSV                                                                                                                                                                                                                                                               | Tabla(s) fuente                                                                                          |
| --- | ------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | -------------------------------------------------------------------------------------------------------- |
| 24  | **Leaderboard Final**                      | Posicion, Nombre, Email, Empresa, Puntos totales, Desglose: checkin, visit_stand, stand_trivia, rate_session, ask_question, wall_post, upload_photo, likes_milestone, wall_comment, connect, vote_poll, complete_interests, chat_session, upload_story, game_spin, game_trivia | `points_log` agregado por attendee + action                                                              |
| 25  | **Log Completo de Puntos**                 | Nombre, Email, Accion, Puntos (+/-), Referencia tipo, Referencia nombre (sesion/sponsor/post/game), Fecha                                                                                                                                                                      | `points_log` con joins a referencia                                                                      |
| 26  | **Resultados Trivia (Live Game)**          | Juego titulo, Sesion, Sponsor (si aplica), Ronda, Pregunta texto, Asistente nombre, Email, Respuesta dada, Respuesta correcta, Correcta? (si/no), Tiempo respuesta (seg), Puntos ronda, Score acumulado, Es ganador?, Fecha                                                    | `live_game_participants` WHERE game.type='trivia' + `live_games`                                         |
| 27  | **Resultados Ruleta (Spin)**               | Juego titulo, Sesion, Sponsor, Sector ganado (label), Puntos del sector, Total participantes, Nombre, Email, Fecha                                                                                                                                                             | `live_game_participants` WHERE game.type='spin' + `live_games` config.sectors                            |
| 28  | **Resultados Jackpot (Sorteo)**            | Juego titulo, Sesion, Sponsor, Premio titulo, Premio descripcion, Total participantes elegibles, Audiencia (all/presencial/virtual), Ganador nombre, Ganador email, Ganador foto, Claim code, Estado reclamo, Fecha inicio, Fecha fin                                          | `live_games` WHERE type='jackpot' + `live_game_participants` WHERE is_winner=true + `reward_redemptions` |
| 29  | **Premios Canjeados (Catalogo de Puntos)** | Asistente nombre, Email, Premio nombre, Sponsor del premio, Puntos gastados, Estado (pending/confirmed/expired/cancelled), Token QR, Fecha solicitud, Fecha expiracion, Confirmado por (staff nombre), Fecha confirmacion                                                      | `reward_redemptions` WHERE source_type IS NULL + `rewards` WHERE type='catalog'                          |
| 30  | **Premios de Juegos (Golden Tickets)**     | Asistente nombre, Email, Premio nombre, Fuente (Jackpot/Foto Mas Votada/Premio Manual), Juego origen, Claim code (6 chars), Estado (pending/confirmed), Confirmado por (staff), Fecha otorgado, Fecha confirmacion                                                             | `reward_redemptions` WHERE claim_code IS NOT NULL + `rewards` WHERE type='prize'                         |
| 31  | **Passport Stamps**                        | Asistente nombre, Email, Stand visitado, Tier sponsor, Fecha stamp, Passport completo? (si/no), Total stamps vs requeridos                                                                                                                                                     | `passport_stamps` + evento config (passport_required_stamps)                                             |
| 32  | **Catalogo de Rewards**                    | Premio nombre, Descripcion, Sponsor, Tipo (catalog/prize), Costo en puntos, Stock inicial, Canjeados, Stock restante, Roles permitidos, Habilitado, Game asociado                                                                                                              | `rewards`                                                                                                |
| 33  | **Resumen de Juegos**                      | Juego titulo, Tipo (spin/trivia/jackpot), Sesion, Sponsor, Estado, Audiencia target, Total participantes, Ganador(es), Fecha inicio, Fecha fin, Auto-post wall                                                                                                                 | `live_games` + conteos de `live_game_participants`                                                       |

**Nota sobre #26-28:** Cada tipo de juego tiene estructura diferente en `live_game_participants.answer` (JSON):

- Spin: `{sector: int}` — el index del sector donde cayo
- Trivia: `{option: int, correct: bool, time_elapsed: float}` — por ronda
- Jackpot: null (solo `is_winner` importa)

**Nota sobre #29 vs #30:** Son exports SEPARADOS porque responden preguntas diferentes:

- #29: "Quien gasto sus puntos y en que" (canje voluntario, tiene expiracion 5min)
- #30: "Quien gano premios en juegos" (otorgado por sistema, tiene claim_code, sin expiracion)

### TAB 6: Social y Fotos (4 exports)

| #   | Export                   | Columnas del CSV                                                                                                                                                             | Tabla(s) fuente                                                      |
| --- | ------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------- |
| 34  | **Muro Social**          | Autor nombre, Email, Texto, Tiene foto (si/no), URL foto, Estado (pending/published/hidden), Likes, Comentarios, Fecha                                                       | `wall_posts`                                                         |
| 35  | **Comentarios del Muro** | Post (autor + extracto), Comentarista nombre, Email, Comentario, Estado (published/hidden), Fecha                                                                            | `wall_comments` + `wall_posts`                                       |
| 36  | **Fotos del Evento**     | Autor nombre, Email, URL foto, Caption, Estado moderacion (pending/approved/rejected), Es oficial, Likes, Es entrada concurso, Es ganador concurso, Posicion concurso, Fecha | `event_photos`                                                       |
| 37  | **Concurso de Fotos**    | Posicion, Participante nombre, Email, URL foto, Votos (likes_count), Es ganador, Puntos otorgados (contest_winner_points), Fecha subida                                      | `event_photos` WHERE is_contest_entry=true ORDER BY likes_count DESC |

### TAB 7: Networking (2 exports)

| #   | Export                       | Columnas del CSV                                                                                                                                                                            | Tabla(s) fuente                                    |
| --- | ---------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------- |
| 38  | **Conexiones y Solicitudes** | Solicitante nombre, Solicitante email, Solicitante empresa, Receptor nombre, Receptor email, Receptor empresa, Estado (pending/accepted/ignored), Mensaje, Fecha solicitud, Fecha respuesta | `contact_requests`                                 |
| 39  | **Intereses de Matchmaking** | Asistente nombre, Email, Intereses seleccionados (lista), Total intereses, Fecha seleccion                                                                                                  | `attendee_interests` + `onboarding_survey_options` |

**Nota:** No existe tabla `matchmaking_matches`. El matching se calcula on-the-fly por overlap de `attendee_interests`. El export #39 permite al organizador analizar que intereses son mas populares.

### TAB 8: Comunicaciones (3 exports)

| #   | Export                 | Columnas del CSV                                                                                                                                                                  | Tabla(s) fuente                                                        |
| --- | ---------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------- |
| 40  | **Push Notifications** | Tipo (announcement/scheduled/module_update), Titulo, Body, Roles target, Fecha envio, Total enviados, Entregados, Abiertos, Fallidos, % delivery, % open rate, Errores frecuentes | `push_notification_logs` + `scheduled_notifications` + `announcements` |
| 41  | **Emails Enviados**    | Destinatario, Email, Template/Mailable, Asunto, Evento, Enviado (si/no), Abierto, Click, Fecha envio, Fecha apertura                                                              | `email_logs` (si existe)                                               |
| 42  | **Webhooks Enviados**  | Endpoint URL, Evento trigger, Payload (resumido), Status code respuesta, Intentos, Ultimo intento, Fecha                                                                          | `webhook_logs` (si existe via DispatchWebhookJob)                      |

### TAB 9: Auditoria Admin (2 exports)

| #   | Export                    | Columnas del CSV                                                                                                 | Tabla(s) fuente                           |
| --- | ------------------------- | ---------------------------------------------------------------------------------------------------------------- | ----------------------------------------- |
| 43  | **Audit Log Admin**       | Admin nombre, Email, Accion, Target tipo, Target ID, Valores anteriores (JSON), Valores nuevos (JSON), IP, Fecha | `admin_audit_logs`                        |
| 44  | **Cambios de Rol y Bans** | Asistente nombre, Email, Tipo (role_change/ban/unban), De rol / Razon ban, A rol / Expira, Ejecutado por, Fecha  | `attendee_role_changes` + `attendee_bans` |

---

## Resumen Ejecutivo por Tab

| Tab                       | Exports | Responde a                                                      |
| ------------------------- | ------- | --------------------------------------------------------------- |
| 1. Asistentes y Acceso    | 6       | Quien vino, cuando, donde estuvo, cuanto duro, consent          |
| 2. Sesiones y Contenido   | 5       | Que sesiones funcionaron, ratings, tiempos reales, fuentes      |
| 3. Engagement en Vivo     | 5       | Chat, Q&A, polls, encuestas, engagement score per-attendee      |
| 4. Patrocinadores y Leads | 7       | ROI completo: leads, contactos, visitas, trivia, equipo         |
| 5. Gamificacion y Juegos  | 10      | Leaderboard, puntos, trivia, ruleta, jackpot, premios, passport |
| 6. Social y Fotos         | 4       | Muro, comentarios, fotos, concurso con ranking                  |
| 7. Networking             | 2       | Conexiones, intereses de matchmaking                            |
| 8. Comunicaciones         | 3       | Push, emails, webhooks                                          |
| 9. Auditoria              | 2       | Admin actions, bans, role changes                               |

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

### FASE 0: Infraestructura de Export + VPS-3 + Read Replica (3h)

**Objetivo:** Base reutilizable para los 44 exports + aislamiento total de performance

```
app/
  Jobs/Exports/              <-- Namespace dedicado
    BaseExportJob.php        <-- Job abstracto: queue exports, timeout, notification, cleanup
  Services/
    ExportService.php        <-- Registra exports, genera filename, signed URL, throttle
  Http/Controllers/Api/
    DataCenterController.php <-- Endpoints API para stats, exports, notifications
  Console/Commands/
    CleanupExportsCommand.php
routes/
  data-center.php            <-- Rutas API agrupadas con middleware session + role
config/
  database.php               <-- read/write split (VPS-3 lee de replica)
public/data-center/          <-- SPA standalone (HTML/JS/CSS) servido por CDN
docker/
  docker-compose.worker.yml  <-- Docker compose para VPS-3 (SOLO queue worker, sin HTTP)
```

**Incluye:**

- docker-compose.worker.yml para VPS-3 (SOLO worker headless, sin Nginx, sin PHP-FPM)
- SPA en `public/data-center/` — Cloudflare cachea los assets estaticos
- API endpoints en Laravel (VPS-1/VPS-2) con Sanctum stateful auth (session cookie, sin CSRF)
- Configurar read/write split en database.php (DB_HOST=replica, DB_WRITE_HOST=primary)
- Verificar `SANCTUM_STATEFUL_DOMAINS` en .env incluye dominio de produccion
- BaseExportJob con `$queue = 'exports'` (worker en VPS-3)
- DataCenterController con endpoints: stats, trigger export, list exports, notifications
- Rutas API con middleware `['auth:sanctum', 'role:...']` (Sanctum stateful, sin CSRF)
- SPA scaffold: index.html + JS con `dcFetch()` wrapper (`credentials: 'include'`)
- Filament sidebar link (NavigationItem) que abre `/data-center/{eventSlug}` en nueva tab
- Nginx config: `try_files $uri $uri/ /data-center/index.html` (SPA fallback)
- Redis cache helper para stat cards (TTL 60s)
- En dev sin replica ni VPS-3, todo local (transparente, misma DB)

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
    // notifyUser() -> Laravel DatabaseNotification con link de descarga (SPA las consulta via API)
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

### FASE 1: SPA UI + Tab Asistentes (2.5h)

**Objetivo:** SPA standalone funcional con primer tab completo

- SPA Data Center (Lumina Noir/Lux, consistente con MC y Event Pulse):
  - Header con 16 stat cards (API → Redis cache)
  - Nav lateral o tabs con 9 secciones
  - Filtros globales: rango de fechas, formato (CSV/Excel)
  - Boton export por dataset → `POST /api/v1/data-center/{eventId}/export`
  - Panel de notificaciones: polling cada 5s a `/api/v1/data-center/{eventId}/notifications`
  - Descarga: signed URL directo a R2 (no pasa por VPS)
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
  Http/Controllers/Api/
    DataCenterController.php
  Console/Commands/
    CleanupExportsCommand.php
routes/
  data-center.php
public/data-center/
  index.html
  assets/
    app.js
    app.css
```

**Total: 44 Job classes + 1 BaseExportJob + 1 Service + 1 Controller + 1 Command + SPA assets**

---

## Dependencias

Ya instalado:

- `maatwebsite/excel` v3.1
- `Filament Notifications` (DatabaseNotification)
- `Queue database` con tabla `jobs` (produccion: Redis)
- `Job Batching` con tabla `job_batches`
- `ExportSessionStatsJob` como patron de referencia
- `ExportPollResponsesJob` como patron de referencia
- `ExportRoomAttendanceJob` como patron de referencia
- `AttendeesExport` como patron maatwebsite
- Redis 3 databases (default, cache, socket)

Nuevo:

- **VPS-3 dedicado** worker headless para queue exports (~$48/mes DO sao1 4vCPU/8GB)
- **Read Replica MySQL** DO Managed (~$15/mes). VPS-3 lee de replica.
- docker-compose.worker.yml para VPS-3 (sin HTTP, solo queue worker)
- SPA standalone en `public/data-center/` (Cloudflare cachea assets)
- DataCenterController con endpoints API (stats, exports, notifications)
- Rutas API con middleware `auth:web` + role check
- Link en Filament sidebar que abre SPA en nueva tab
- Queue "exports" con worker en VPS-3
- Comando `exports:cleanup` en Kernel scheduler
- **Redis cache** para stat cards del header (TTL 60s)
- Los 3 export jobs existentes se refactorizan para extender BaseExportJob

---

## Estado de Implementacion (25 abril 2026)

```
F0 [DONE] Base: BaseExportJob, ExportService, DataCenterController, CleanupExportsCommand, SPA scaffold
F1 [DONE] Asistentes (6 exports)
F2 [DONE] Sesiones + Engagement (10 exports)
F3 [DONE] Patrocinadores (7 exports)
F4 [DONE] Gamificacion (10 exports)
F5 [DONE] Social + Networking (6 exports)
F6 [DONE] Comunicaciones + Auditoria (5 exports)
F7 [DONE] Export maestro ZIP + rate limit 30min
QA [DONE] 31 tests, 283 assertions, bugs R2/readonly/N+1 corregidos
```

**Archivos creados:** 45 jobs (app/Jobs/Exports/), 1 service, 1 controller, 1 command, SPA (3 archivos), 2 test files
**Archivos modificados:** api.php, console.php, AdminPanelProvider.php

---

## PENDIENTES para proxima sesion

### Estado actual (sesion 26 abril 2026)

**Completado en sesiones anteriores:**

- Diseno aprobado: White Chrome + violet/blue charts sobre Noir (demo en design/data-center-demo.html)
- CSS con paleta aprobada (app.css)
- HTML shell (index.html)
- Backend ExportService::computeStats() expandido con: top_sessions, sponsors+leads, leaderboard, points_by_action, session_ratings, rating_distribution, lead_tiers, wall_by_status, connections_by_status, games, Q&A stats
- Notification panel rediseñado (iconos en cuadrados soft-color)

**Iteracion 6 (26 abril, cierre final con UIs faltantes y tema Lux):**

```
DC-UI-GOALS: Goals UI completa [DONE]
  - Boton "Metas" en action bar
  - Modal con 5 metricas (Registrados/Check-ins/Leads/Fotos/Conexiones)
  - Input target + Guardar/Borrar por metrica
  - Barra de progreso bajo cada hero card cuando hay meta configurada
  - Color violeta <70%, ambar 70-99%, verde >=100%

DC-CRON-SCHEDULED: Cron processor scheduled exports [DONE]
  - Schedule::call cada 5 minutos en routes/console.php
  - Lee dc_scheduled_exports con next_run_at <= now() AND is_active = true
  - Dispatcha el job correspondiente con filtros guardados
  - Recalcula next_run_at segun frequency (daily/weekly/monthly)

DC-UI-SCHEDULED: Modal programar exports [DONE]
  - Boton schedule (icono reloj) en cada export row
  - Modal con: frequency, format, email recipient, chips de filtros aplicados
  - Lista "Mis programados" con next/last run
  - Borrar individuales

DC-UI-LUX: Tema Lux (light mode) [DONE]
  - Variables CSS condicionales :root[data-theme="lux"]
  - Tokens completos: bg cool linen, ink #1A1B1E, sombras multicapa
  - Toggle en header (icono dark_mode/light_mode)
  - Persiste en localStorage
  - Cards usan shadow en lugar de border (regla Lux)
  - Boton primario: blanco en Noir, negro en Lux
  - Sin ambient glow en Lux (fondo limpio)
  - Modal scrim mas claro en Lux

DC-EMBED-DESCARTADO: Panel Embed [WONTFIX]
  - Funcionalidad backend queda lista (tabla + endpoints + ruta /dc-embed/{token})
  - UI no se construye: redundante con Event Pulse (que ya hace shareable views)
  - Decision: si en el futuro se necesita compartir DC, se reactivara
```

**Iteracion 5 (26 abril, sprint cierre Bloques A/B/C/D):**

```
DC-A1: Sub-filtros extendidos [DONE]
  - chat_by_hour ahora filtra por session_id (room='chat:session:{id}')
  - Date range tambien aplica a chat_messages, audit, regs, checkins, attendance_by_source

DC-A2: Cache invalidation con Observer [DONE]
  - app/Observers/DataCenterCacheObserver.php
  - Registrado en AppServiceProvider para Attendee, EventSession, Sponsor, Lead,
    AttendeeBan, AttendeeRoleChange, WallPost, EventPhoto, LiveGame
  - Borra dc:stats:{eventId}:* via Redis pattern (fallback Cache::forget)

DC-A3: Polling notifs adaptativo [DONE]
  - emptyPollCount: pausa polling tras 3 polls vacios consecutivos (15s)
  - Reanuda al disparar export o abrir panel
  - resumePollingIfPaused() en triggerExport

DC-B1: Tooltips en gráficas [DONE]
  - Donuts con <title> SVG (hover muestra label + valor + %)
  - Funciona nativo, sin JS extra

DC-B2: "Ultima actualizacion" en header [DONE]
  - Chip dc-fresh con dot animado pulse-dot
  - Auto-tick cada 15s con relativeTime
  - Reset al recargar stats (lastStatsAt)

DC-B3: Sparkline en card "Check-ins" [DONE]
  - Usa checkins_by_hour (24 buckets), trim leading/trailing zeros
  - Mismo helper sparklineSvg() del card "Registrados"

DC-B4: Empty states con microcopy especifica [DONE]
  - "Aun no hay ratings - apareceran cuando los asistentes califiquen sesiones"
  - "Aun no hay preguntas Q&A - apareceran cuando los asistentes envien preguntas"
  - "El muro esta vacio - los posts publicados apareceran rankeados por engagement"

DC-B5: Loading granular fade [DONE]
  - Si cachedStats existe: opacity .35 en tabContent + statsGrid mientras carga
  - Si no hay datos previos: skeleton total
  - Quita la clase tras loadStats()

DC-C1: Comparativa entre periodos [DONE]
  - Backend computePrevPeriodSummary (ultimos 30d vs prev 30d)
  - Cache separado dc:stats:{eventId}:prev30 (TTL 5min)
  - Frontend deltaChip con flecha up/down + verde/rojo
  - Aplicado a hero cards Registrados y Leads

DC-C2: Goals/targets configurables [DONE]
  - Tabla dc_event_goals (event_id, metric, target, label)
  - Endpoints GET/POST/DELETE /goals
  - computeStats incluye 'goals' con current vs target + pct
  - Metricas: registered, checked_in, leads, photos, connections

DC-C3: Email scheduled reports [DONE]
  - Tabla dc_scheduled_exports (frequency, next_run_at, recipient, filters)
  - Endpoints GET/POST/DELETE /scheduled
  - daily/weekly/monthly con next_run_at calculado al crear
  - (Cron processor sera tarea de deploy con Schedule::call)

DC-C4: Embed link publico [DONE]
  - Tabla dc_embed_tokens (token UUID, expires_at, filters, label, access_count)
  - Endpoint GET /dc-embed/{token} sirve embed.html con datos inyectados
  - public/data-center/embed.html — vista solo lectura, header + 4 hero cards + sponsors + leaderboard
  - Sin auth, validacion por token + fecha expiracion

DC-C5: Multi-evento dashboard [DONE]
  - Endpoint GET /data-center/multi?event_ids=1,2,3
  - UI en empty state: card "Comparativa multi-evento" con checkboxes
  - 2-5 eventos seleccionables, tabla comparativa con max highlight verde
  - 8 metricas comparadas

DC-C6: Email logs (tab Comunicaciones) [DEFERRED]
  - Tabla email_logs ya existe (de Iteracion previa) con sent/failed
  - Falta: hook en Mailable para insertar registros + extender stats
  - Schema actual no tiene opened/clicked (requiere webhook SES/Mailgun)
  - Diferido: tab Comunicaciones funciona con push + webhook + email link a CSV

DC-D1: Comparador A/B sesiones [DONE]
  - Bar superior en tab Sesiones: 2 selects (A vs B) + boton Comparar
  - runSessionCompare hace 2 fetches paralelos con session_id
  - Card comparativa con metricas: Rating, Reviews, Asistencia, Q&A, Chat msgs
  - Highlight verde al ganador por metrica

DC-D2: Export "vista actual" [DONE]
  - Boton dc-btn-export-view en action bar
  - TAB_PRIMARY_EXPORT mapea cada tab a su dataset principal
  - Hereda automaticamente filtros activos
  - Asistentes->attendees_master, Sponsors->leads_master, Gamificacion->leaderboard, etc

DC-MIGRATIONS: 3 migraciones nuevas [DONE]
  - dc_event_goals
  - dc_scheduled_exports
  - dc_embed_tokens
```

**Iteracion 4 (26 abril, sprint completo):**

```
DC-DATA-2: Backend datos enriquecidos para 4 tabs [DONE]
  - top_speakers (avg rating + count + avatar)
  - attendance_by_track (group by session_tracks)
  - chat_by_hour (HOUR(created_at) ultimos 7d)
  - top_askers (Q&A count + upvotes con avatar)
  - top_posters (wall_posts count + likes con avatar)
  - photos_preview (top 6 by likes con flag is_winner)
  - top_interests (matchmaking top 8)
  - net_by_hour (contact_requests por hora con accept rate)

DC-UI-13: Tab Sesiones enriquecida [DONE]
  - Top Speakers card con avatar + rating + role
  - Asistencia por Track con barras coloridas
  - Mantiene top ratings + distribucion estrellas

DC-UI-14: Tab Engagement enriquecida [DONE]
  - Heatmap de actividad de chat por hora (24 buckets) con peak hour
  - Top Askers con avatar + count + upvotes
  - Mantiene mini stats + donut Q&A

DC-UI-15: Tab Social enriquecida [DONE]
  - Top Posters con avatar + posts + likes
  - Grid 3x3 de fotos preview (top 6 por likes) con overlay y flag winner
  - Mantiene mini stats + donut moderacion

DC-UI-16: Tab Networking enriquecida [DONE]
  - Top Intereses (matchmaking) con barras
  - Heatmap solicitudes por hora con accept rate stacked
  - Mantiene donut + tasa aceptacion

DC-XLSX-1: XLSX real implementado [DONE]
  - generateXlsx() en BaseExportJob con maatwebsite/excel
  - Anonymous class con FromArray + WithHeadings + WithStyles + ShouldAutoSize
  - Header bold con bg #0F172A y texto blanco, freeze pane A2, auto-width
  - Notification con label "Descargar XLSX" cuando aplica
  - Re-habilitado en selector UI: <option value="xlsx">XLSX</option>

DC-FILT-1: Date range picker global [DONE]
  - Chip "Sin filtro / Ultimos Nd / fecha custom"
  - Popover con presets (7/30/90/all) + inputs custom desde/hasta
  - Filtros pasados a jobs via filters[] en POST /export
  - Estilo activo chip con violet outline cuando hay filtro

DC-FILT-2: Filtros sub-entidad por tab [DONE]
  - Sesiones/Engagement: dropdown sesion
  - Sponsors: dropdown patrocinador
  - Gamificacion: dropdown juego
  - Auto-refresh al cambiar tab via refreshEntityFilter()
  - Cleared automaticamente cuando cambias a tab sin filtros

DC-UI-17: Tablet breakpoint refinado (881-1200px) [DONE]
  - Hero queda en 4 columnas, hc padding reducido
  - dc-grid c64 mantiene 1.4fr 1fr
  - Photo grid 3 cols
  - Filters menos margin

DC-INFRA-1: docker-compose.worker.yml VPS-3 [DONE]
  - docker/Dockerfile.worker (PHP 8.3 alpine, sin Nginx)
  - docker/docker-compose.worker.yml con worker + scheduler
  - 2 vCPU / 2GB limits, healthcheck, restart unless-stopped
  - max-jobs=200 + max-time=3600 para reciclar workers

DC-INFRA-2: Read/write split MySQL [DONE]
  - config/database.php con array_merge condicional segun DB_WRITE_HOST
  - VPS-3 lee de replica via DB_HOST, escribe a primary via DB_WRITE_HOST
  - Si DB_WRITE_HOST=disabled, no aplica split (defensa worker no escriba)
  - sticky=true para consistencia post-write

DC-INFRA-4: SANCTUM_STATEFUL_DOMAINS en .env.example [DONE]
  - Default dev: localhost,127.0.0.1,eventos-backend.test
  - Doc inline para produccion: api.eventos.com sin protocolo

DC-INFRA-3: Nginx try_files [DEFERRED]
  - En dev resuelto con route fallback Laravel (/data-center/{any?})
  - En prod aplicara cuando se haga deploy

DC-INFRA-5: 3 jobs legacy refactor [DEFERRED]
  - app/Jobs/ExportSessionStatsJob, ExportPollResponsesJob, ExportRoomAttendanceJob
  - Llamados desde Filament tradicional (no Data Center) — refactor rompe callers
  - Los 44 jobs DC ya viven en app/Jobs/Exports/ con BaseExportJob
  - Deuda tecnica documentada, no bloqueante

DC-ENV-WORKER: .env.worker.example [DONE]
  - Plantilla completa para Droplet-3
  - REPLICA host, R2 storage, Redis VPC, queue:exports
```

**Iteracion 3 (26 abril, feedback DaVinci):**

```
DC-UI-8: Empty state V2 enterprise [DONE]
  - Quitado icono central cliche (analytics) + frase imperativa "Selecciona un evento"
  - Reemplazado por mapa de 9 categorias del producto (3x3 grid)
  - Header chip con "9 categorias · 44 datasets · CSV/XLSX/ZIP"
  - Header eventName cuando no hay evento: "Vista general / Sin evento activo"
  - Tabs ocultos cuando no hay evento (no se muestran sin contexto)

DC-UI-9: Tabs presencia mejorada [DONE]
  - font-size 12 -> 13, padding 9x12 -> 12x16, weight 500 -> 600
  - color off: t3 (40%) -> t2 (60%) — menos apagado
  - icon size 15 -> 17, opacity off .5 -> .7

DC-UI-10: Logos sponsors + avatares reales [DONE]
  - Backend: sponsors[].logo, leaderboard[].avatar, games[].winner_avatar/reward_image/reward_name
  - Frontend: helper sponsorAvatar() con <img> + onerror fallback violet (no Google G)
  - Tabla ROI: td-logo con img o fallback violet
  - Podium: dc-pod-av-img / dc-pod-av-fallback con violet gradient
  - Rank list: dc-rank-av con violet fallback
  - Game cards: dc-game-thumb con reward image o icon

DC-UI-11: Tab Comunicaciones con datos reales [DONE]
  - Backend computeStats: comms = { push: {total/delivered/opened/failed/open_rate/delivery_rate}, announcements, scheduled, webhook: {total/success/failed} }
  - Frontend: donut push (verde/azul/rojo) + mini stats + donut webhooks + email card
  - Reemplaza la lista repetida de canales por graficas reales

DC-FIX-2: Notificaciones limpiar + persist [DONE]
  - Nuevo endpoint POST /notifications/read-all (markAllAsRead)
  - Nuevo endpoint DELETE /notifications/clear (deletes all DC notifs)
  - Boton "Limpiar todo" (delete_sweep) en panel header
  - Auto mark-as-read al abrir el panel (persiste en server, no solo cliente)
  - Bug "siguen apareciendo despues de cerrar panel" -> resuelto

DC-UI-12: Responsive refinado [DONE]
  - 3 breakpoints: 1200px (2 cols hero/grid), 880px (header stacked, tabs compactos), 640px (1 col, podium reducido, donut vertical, mini-grid 2 cols)

DC-DEMO-1: Seeder demo [DONE]
  - Nuevo DataCenterDemoSeeder
  - Pobla: pravatar avatars, clearbit logos, 54 attendees con timestamps distribuidos (30d + checkins por hora ponderada),
    280 session_attendances con sources, 360 push_logs (delivered/opened/failed), 80 webhook_logs (88% success), 58 session_questions
  - Limpia cache dc:stats al final
  - Comando: php artisan db:seed --class=DataCenterDemoSeeder
```

**Iteracion 2 (26 abril, mismo dia):**

```
DC-FIX-1: 404 al recargar SPA [DONE]
  - Agregada ruta web /data-center/{any?} que sirve index.html
  - Protegida con auth + role check (super_admin, org_admin, event_admin)
  - Permite recargar /data-center/1, /data-center/cualquier-cosa sin 404

DC-DATA-1: 3 datasets nuevos en computeStats() [DONE]
  - registrations_by_day: ultimos 30 dias group by DATE(created_at)
  - checkins_by_hour: distribucion por hora group by HOUR(checked_in_at)
  - attendance_by_source: session_attendances group by source

DC-UI-5: Tab Asistentes premium con datos reales [DONE]
  - Helper areaSvg() reutilizable (curvas con grid + gradient fill)
  - Helper sparklineSvg() para hero cards
  - Layout reescrito: donut + area chart real / top sessions + mini stats / source bars + hour buckets
  - Reemplaza el area chart y heatmap ficticios del demo con datos 100% reales

DC-UI-6: Empty state premium [DONE]
  - Hero card cuando no hay evento seleccionado
  - Badge animado (breathe), title, sub, 6 pills preview con iconos
  - Bounce arrow indicando selector arriba a la derecha

DC-UI-7: Loading skeletons [DONE]
  - renderHeroSkeleton() — 4 cards shimmer mientras carga stats
  - renderTabSkeleton() — placeholder de cards mientras llega data
  - Animacion shimmer + ::after overlay sutil
```

**Iteracion 1 (26 abril):**

```
DC-P0: Reescribir funciones de tab en app.js [DONE]
  - Helper donutSvg() reutilizable (segments con cumulativo rotation, filtro value>0)
  - Helper exportsList() para lista de exports por tab
  - tabAsistentes refactor: removido area chart y heatmap ficticios; ahora donut real + mini stats reales + top_sessions reales + exports list
  - tabSponsors: usa s.sponsors y s.lead_tiers reales; tabla ROI simplificada (Leads, Favoritos, Score) sin columnas inventadas
  - tabGamificacion: leaderboard real (con empty state), points_by_action real con labels mapeadas, games con type→icon/color mapping y status badge
  - 6 tabs nuevas creadas: tabSesiones (top ratings + distribucion estrellas), tabEngagement (mini stats + donut Q&A), tabSocial (mini stats + donut moderacion + photos approved), tabNetworking (donut connections + accept rate), tabComunicaciones (3 channels), tabAuditoria (2 tracks)
  - Todas las tabs incluyen exportsList() al final con los CSV downloads

DC-P0b: Eliminar JetBrains Mono [DONE]
  - index.html: removido JetBrains Mono del Google Fonts link
  - app.css: --fm cambiado de 'JetBrains Mono' a 'Urbanist'
  - app.js: SVG text font-family "JetBrains Mono" → "Urbanist" weight 500
  - Verificado: 0 referencias a JetBrains en los 3 archivos
```

### Lista completa de tareas — TODO COMPLETADO

```
SPA UI DaVinci [DONE]
  + 9 tabs con datos reales conectadas al API
  + Diseno Lumina Noir + Lux v2 (toggle theme)
  + Panel notificaciones con limpiar todo
  + Responsive 4 breakpoints (1200/881/640/<640) + scroll-x tabs

Formato XLSX [DONE]
  + generateXlsx() en BaseExportJob con maatwebsite/excel
  + Auto-width, header bold, freeze pane A2
  + Selector UI re-habilitado

Filtros avanzados [DONE]
  + Date range picker global con presets + custom (reactivo backend)
  + Filtros sub-entidad por tab (sesion/sponsor/juego)

Infra produccion [DONE]
  + docker/Dockerfile.worker
  + docker/docker-compose.worker.yml (worker + scheduler)
  + .env.worker.example completo
  + database.php read/write split condicional
  + SANCTUM_STATEFUL_DOMAINS en .env.example
  - DC-INFRA-3 (Nginx try_files): resuelto en local con route fallback Laravel — produccion opcional
  - DC-INFRA-5 (refactor 3 jobs legacy): viven fuera de app/Jobs/Exports, llamados por Filament directo —
    refactor rompe callers. DEUDA TECNICA documentada, no bloqueante.

Bloques A/B/C/D enterprise [DONE]
  A1 Sub-filtros extendidos (chat_by_hour por session_id) [DONE]
  A2 Cache invalidation con Observer (DataCenterCacheObserver) [DONE]
  A3 Polling notifs adaptativo (pausa tras 3 polls vacios) [DONE]
  B1 Tooltips en graficas (title SVG) [DONE]
  B2 Chip "Actualizado hace Xs" en header [DONE]
  B3 Sparkline en card "Check-ins" con checkins_by_hour [DONE]
  B4 Empty states con microcopy especifica [DONE]
  B5 Loading granular fade (.dc-loading-fade) [DONE]
  C1 Comparativa entre periodos (30d vs 30d previos, capped) [DONE]
  C2 Goals/targets con UI modal + barras progreso bajo hero cards [DONE]
  C3 Scheduled exports con cron processor + modal UI + lista [DONE]
  C4 Embed publico — backend listo, UI descartada (redundante con Event Pulse) [WONTFIX]
  C5 Multi-evento dashboard (UI en empty state + tabla comparativa) [DONE]
  C6 Email logs hook + webhook tracking [DEFERRED a deploy]
      Razon: depende de provider SES/Mailgun/Postmark
      Tabla email_logs ya existe con sent/failed
  D1 Comparador A/B sesiones (2 selects + tabla winner highlight) [DONE]
  D2 Export "vista actual" (botón en action bar, hereda filtros) [DONE]

Tema Lux v2 [DONE]
  + Variables CSS condicionales :root[data-theme="lux"]
  + Toggle en header con icono dark_mode/light_mode
  + Persiste en localStorage (no flash al recargar)
  + Cards con shadow en lugar de border (regla Lux)
  + Boton primario invertido (blanco→negro)
  + Sin ambient glow en Lux

Seeder demo [DONE]
  + DataCenterDemoSeeder con: avatares pravatar, logos clearbit, 54 attendees,
    280 session_attendances, 360 push_logs, 80 webhook_logs, 58 questions,
    90 audit_log entries, 8 bans, 12 role changes
```

### Unico pendiente: Deploy a VPS-3 (cuando sea momento)

Plan completo en seccion "PLAN DE DEPLOY A VPS-3" abajo. Pasos DC-DEPLOY-1 a DC-DEPLOY-6.

### Deferred / WontFix con razon documentada

```
DC-INFRA-3 (Nginx try_files): no necesario en local — Laravel fallback funciona
DC-INFRA-5 (refactor 3 jobs legacy): rompe callers Filament, deuda tecnica
C4 Embed UI: redundante con Event Pulse que ya tiene shareable views
C6 Email tracking opens/clicks: depende de provider que se elija en deploy
```

---

## PLAN DE MEJORAS POST-SPRINT (orden por impacto/esfuerzo)

### Bloque A — Bugs sutiles antes de produccion (~3h, alta prioridad)

```
DC-A1: Sub-filtros aplican parcialmente
  - session_id filtra solo top_sessions, session_ratings, top_askers
  - chat_by_hour, push, audit, regs siguen mostrando todo el evento
  - Decision: extender filtro a chat_by_hour (filter por session_id en chat_messages)
    y a leads_master views cuando hay sponsor_id
  - Esfuerzo: ~1h

DC-A2: Cache invalidation con Observer
  - Crear ObserverDataCenterStats que dispara Cache::forget("dc:stats:{eventId}:*")
    cuando cambia: AttendeeBan, AttendeeRoleChange, Sponsor, EventSession, Lead
  - Hoy: cache 60s sin invalidacion -> admin ve datos viejos hasta 1 minuto
  - Esfuerzo: ~1h

DC-A3: Polling notifs adaptativo
  - Si pollNotifications devuelve 0 unread tres veces seguidas, pausar polling
  - Re-iniciar al disparar export o abrir panel
  - Hoy: 5s perpetuo aunque no haya nada en cola
  - Esfuerzo: ~30min
```

### Bloque B — Polish enterprise (~2.5h)

```
DC-B1: Tooltip en gráficas (donuts y barras)
  - Hover sobre segmento muestra label + valor + %
  - Implementacion: data-tooltip + delegated mousemove en .dc-section
  - Esfuerzo: ~1h

DC-B2: "Ultima actualizacion" en header
  - Chip "Actualizado hace 12s" junto al brand
  - Auto-tick cada 10s con relativeTime()
  - Reset al recargar stats
  - Esfuerzo: ~30min

DC-B3: Sparkline en card "Check-ins"
  - Usar checkins_by_hour (ya viene en stats)
  - Mismo helper sparklineSvg() del card "Registrados"
  - Esfuerzo: ~15min

DC-B4: Empty states con microcopy especifica
  - "Aun nadie ha enviado preguntas - los Q&A apareceran aqui cuando los asistentes interactuen"
  - Por card, no genericos
  - Esfuerzo: ~30min

DC-B5: Loading granular (fade en lugar de skeleton total)
  - Al filtrar, las cards entran en opacity:.4 mientras llega data
  - Mejor UX que skeleton total
  - Esfuerzo: ~15min
```

### Bloque C — Features con valor enterprise (~10h, P5 evolutivo)

```
DC-C1: Comparativa entre periodos (vs ultimos 30 dias)
  - Cada hero card muestra delta: "+12% leads" / "-5% chat"
  - Backend: computeStats acepta `compare_with` y devuelve previous_period
  - UI: chip pequeno con icono trending_up/down
  - Esfuerzo: ~3h

DC-C2: Goals/targets configurables
  - Tabla event_goals: metric, target, deadline
  - UI: barra de progreso bajo cada hero card "1247/2000 registrados (62%)"
  - Esfuerzo: ~2h

DC-C3: Email scheduled reports
  - "Enviarme leads cada lunes 9AM" como cron jobs
  - UI: modal "Programar reporte" desde cualquier export
  - Tabla: scheduled_exports (frequency, next_run, recipient_email)
  - Esfuerzo: ~2.5h

DC-C4: Embed link publico con vista filtrada
  - Generar token UUID asociado a evento + filtros
  - URL: dc.eventos.com/embed/{token} - solo lectura, expira en N dias
  - Caso uso: patrocinador recibe su mini-DC con sus datos
  - Esfuerzo: ~2h

DC-C5: Multi-evento dashboard
  - Vista agregada de varios eventos del mismo organization
  - KPIs comparados: total registrados, leads, NPS, etc.
  - Esfuerzo: ~3h

DC-C6: Email logs tracking (tab Comunicaciones completo)
  - Crear tabla email_logs (sent_at, opened_at, clicked_at, mailable, recipient)
  - Hook en Mailable: track al send + webhook SES/Mailgun para opens/clicks
  - Tab Comunicaciones tendria card real de Email
  - Esfuerzo: ~3h
```

### Bloque D — UX advanced (~1h)

```
DC-D1: Comparador A/B de sesiones
  - Lado a lado dos sesiones (rating, attendance, chat msgs, Q&A)
  - Boton "Comparar con..." en tab Sesiones
  - Esfuerzo: ~1h

DC-D2: Export "tal cual lo veo"
  - Boton flotante "Exportar vista actual" usa filtros activos
  - Atajo a triggerExport con filtros aplicados sin elegir tipo
  - Esfuerzo: ~30min
```

---

## PLAN DE DEPLOY A VPS-3 (cuando sea el momento)

### Topologia final en DO sao1

```
                    Cloudflare WAF + LB
                           |
       +-------------------+-------------------+
       |                   |                   |
       v                   v                   v
   api.eventos.com    api.eventos.com    exports.eventos.com
   (Droplet-1)        (Droplet-2)        (Cloudflare R2)
       |                   |                       ^
       |  round-robin      |                       |
       |  failover <30s    |                       | signed URLs 24h
       v                   v                       |
   +-------------------------------------------+   |
   | VPC privada DO sao1                       |   |
   |                                           |   |
   |  Managed MySQL Primary  <----- writes ----+   |
   |   |                                       |   |
   |   +-> Read Replica  <------------+        |   |
   |                                  |        |   |
   |  Managed Redis (queue + cache)   |        |   |
   |   ^                              |        |   |
   |   |  via VPC privada             |        |   |
   |   |                              |        |   |
   |  Droplet-3 (Worker headless) ----+--------+---+ writes ONLY R2
   |   - php artisan queue:work --queue=exports
   |   - NO HTTP, NO Nginx, NO IP publica
   |                                           |
   +-------------------------------------------+
```

### DC-DEPLOY-1: Provisionar infra en DigitalOcean

```
1. Droplet-3
   - DO sao1, 4vCPU / 8GB RAM, Ubuntu 24.04
   - Misma VPC que Droplet-1 y Droplet-2
   - Sin IP publica (solo SSH desde bastion o via Tailscale)
   - Costo: ~$48/mes

2. Read Replica MySQL
   - Console DO -> cluster MySQL -> "Add read-only node"
   - Anotar hostname privado: private-replica-db-xxx.b.db.ondigitalocean.com
   - Costo: ~$15/mes

3. Usuario MySQL read-only
   CREATE USER 'worker_readonly'@'%' IDENTIFIED BY 'XXXX';
   GRANT SELECT ON eventos_prod.* TO 'worker_readonly'@'%';
   FLUSH PRIVILEGES;

4. R2 bucket (si no existe)
   - Cloudflare dashboard -> R2 -> Create bucket "eventos-exports"
   - Custom domain: exports.eventos.com
   - API token con permisos write+read
```

### DC-DEPLOY-2: Configurar Droplet-3

```bash
# SSH al droplet
ssh root@<droplet-3-private-ip>

# Instalar Docker
apt update && apt install docker.io docker-compose-plugin -y
systemctl enable docker

# Clonar repo
cd /opt
git clone https://github.com/<org>/eventos-backend.git
cd eventos-backend

# Configurar variables
cp .env.worker.example .env.worker
nano .env.worker
```

Variables criticas en .env.worker:

```env
APP_KEY=base64:<MISMA_KEY_QUE_VPS-1>
DB_HOST=private-replica-db-xxx.b.db.ondigitalocean.com   # REPLICA
DB_USERNAME=worker_readonly
DB_PASSWORD=<password creado en paso 3>
DB_WRITE_HOST=disabled                                    # worker NO escribe DB
REDIS_HOST=private-redis-xxx.db.ondigitalocean.com        # mismo Redis VPS-1/2
REDIS_PASSWORD=<existing>
FILESYSTEM_DISK=r2
AWS_ACCESS_KEY_ID=<R2 token>
AWS_SECRET_ACCESS_KEY=<R2 secret>
AWS_BUCKET=eventos-exports
AWS_ENDPOINT=https://<account>.r2.cloudflarestorage.com
AWS_URL=https://exports.eventos.com
```

### DC-DEPLOY-3: Levantar el worker

```bash
docker compose -f docker/docker-compose.worker.yml up -d
docker compose -f docker/docker-compose.worker.yml logs -f worker

# Verificar que toma jobs:
# Desde Droplet-1 admin dispara un export -> en logs aparece:
# [date] Processing: App\Jobs\Exports\ExportLeadsMasterJob
# [date] Processed: ExportLeadsMasterJob (250ms, 1247 rows)
```

### DC-DEPLOY-4: Configurar Droplets API (Droplet-1, Droplet-2)

```env
# .env de cada droplet API:
SANCTUM_STATEFUL_DOMAINS=api.eventos.com
```

Nginx (si NO se quiere usar route fallback Laravel):

```nginx
location /data-center/ {
    try_files $uri $uri/ /data-center/index.html;
}
```

(El route fallback Laravel ya implementado funciona igual, este paso es opcional.)

### DC-DEPLOY-5: DNS Cloudflare

```
api.eventos.com         A    <Droplet-1 IP>
api.eventos.com         A    <Droplet-2 IP>   (round robin)
exports.eventos.com     CNAME <bucket>.r2.cloudflarestorage.com
                              (custom domain en R2 dashboard)
```

Droplet-3 NO va en DNS publico — solo accesible via VPC privada.

### DC-DEPLOY-6: Smoke test

```
1. Login en https://api.eventos.com/admin
2. Click sidebar "Data Center" -> abre /data-center/{slug}
3. Seleccionar evento -> stats cargan, hero cards animadas
4. Aplicar filtro fecha 7 dias -> vista recarga
5. Disparar un export pequeno (ej: Consent Log)
6. Esperar notification -> link de descarga R2
7. Descargar -> archivo CSV/XLSX llega correctamente
8. Probar XLSX -> abrir en Excel, freeze pane y header bold OK
9. Probar export maestro -> ZIP con 44 archivos
```

### Costo total infra produccion

```
Droplet-1 (4vCPU/8GB) ........ $48/mes
Droplet-2 (4vCPU/8GB) ........ $48/mes
Droplet-3 (4vCPU/8GB) ........ $48/mes  [NUEVO]
MySQL Primary ................ $15/mes
MySQL Read Replica ........... $15/mes  [NUEVO]
Managed Redis ................ $15/mes
Cloudflare R2 ................ $5-10/mes
Cloudflare LB/WAF ............ $0-20/mes
                              ─────────
Total                          ~$200-230/mes (10K activos)

Comparativa:
- Cisco Webex Events: $88,000 USD/evento
- ICE360: $49,000,000 COP/evento (~$11K USD)
- EventOS infra: ~$2,500 USD/ANO total
```

### Failover y resiliencia

```
Droplet-3 cae        -> Jobs se acumulan en Redis (persistencia).
                         VPS-1/2 siguen 100%. Al volver, drena cola.

Droplet-1 cae        -> Cloudflare redirige a Droplet-2 en <30s.
                         Worker sigue funcionando.

MySQL Primary cae    -> Failover automatico DO Managed (~30-60s).
                         Worker pausa hasta que vuelva.

Replica desfasada    -> Delay tipico 1-2s, aceptable para exports.

Red R2 cae           -> Exports en cola fallan, se reintentan automaticamente.
                         Job tries=1, pero podria subirse a tries=3 con backoff.
```

### Monitoreo recomendado

```
- DO monitoring: alertas CPU>80%, RAM>90%, disk>85%
- docker compose logs -f para debugging puntual
- Sentry DSN en .env.worker (opcional pero recomendado)
- Cloudflare Analytics: requests, errores, latencia p95
- Cron en Droplet-3 que confirma que queue:work esta corriendo
   (pongamos un healthcheck script que pingee si hay un container)
```

---

## Que NO incluye este roadmap

- Dashboard en tiempo real (eso es Event Pulse, ya COMPLETO)
- CRUD de datos (eso es Filament)
- Moderacion (eso es Mission Control)

---

## Criterio de Exito (validado 25 abril 2026)

1. [OK] Organizador abre Data Center, ve 12 conteos del evento
2. [OK] Hace click en "Descargar Leads", recibe notificacion con link
3. [OK] Descarga CSV con nombre, email, empresa, cargo de cada lead
4. [OK] El evento en curso NO se ve afectado (queue "exports" separado)
5. [OK] Descarga "Resultados Trivia" y ve quien respondio que, tiempo, ganador
6. [OK] Descarga "Premios Canjeados" y ve quien canjeo, staff confirmo, expiro
7. [OK] Descarga "Asistencia Sesion" y ve duracion de cada virtual
8. [OK] "Descargar Todo" genera ZIP con 44 archivos
9. [OK] Silent disco export muestra quienes NO respondieron
10. [OK] Jackpot export muestra ganador, claim code, estado reclamo
11. [PENDIENTE] Graficas visuales por tab
12. [PENDIENTE] Formato XLSX nativo
13. [PENDIENTE] Filtros por fecha/sesion/sponsor
