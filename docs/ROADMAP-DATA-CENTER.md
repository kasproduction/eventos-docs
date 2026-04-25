# ROADMAP: Data Center (Centro de Datos)

> **Prioridad:** P2
> **Estado:** Backend COMPLETO (F0-F7). SPA scaffold funcional. Pendiente: UI DaVinci + XLSX.
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
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...options.headers,
        },
    });
    if (res.status === 401) window.location.href = '/admin/login';
    if (res.status === 403) showError('Sin permisos para este evento');
    return res;
}
```

**Por que session cookie y no Bearer token:**
- MC usa HMAC→Bearer porque necesita funcionar SIN login Filament (TV del venue, staff tecnico)
- Pulse usa `ep_*` query token por la misma razon (pantalla gigante en el evento)
- Data Center SIEMPRE lo usa un admin ya logueado en Filament → session cookie es lo natural
- Sanctum stateful API esta disenado exactamente para este caso (SPA same-origin + session)

| Rol | Acceso Data Center | Scope |
|-----|-------------------|-------|
| super_admin | Si | Todos los eventos |
| org_admin | Si | Eventos de su organizacion |
| event_admin | Si | Solo su evento |
| moderator | No | Solo tiene MC |

### Coexistencia Filament + SPAs

| Herramienta | Tipo | Auth | CSRF | Vive en |
|-------------|------|------|------|---------|
| Filament | Admin CRUD | Session Laravel | Blade (automatico) | /admin |
| Mission Control | Operacion sesion | HMAC → Sanctum Bearer | No (Bearer token) | public/mission-control/ |
| Event Pulse | Dashboard RT | `ep_*` query token | No (solo GET) | public/event-pulse/ |
| Data Center | Analytics post-evento | Session cookie + Sanctum stateful | No (`api/*` excluido) | public/data-center/ |

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

| VPS | Que corre | Quien lo usa | Lee de | Escribe en |
|-----|-----------|-------------|--------|------------|
| VPS-1 | API + Socket + Filament + Queue:default | 10K asistentes + admins | Primary | Primary |
| VPS-2 | API + Socket + Filament + Queue:default | 10K asistentes + admins | Primary | Primary |
| VPS-3 | Queue:exports (worker headless) | Nadie directamente | **REPLICA** | Solo R2 (archivos) |

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
        limits: { cpus: '2', memory: 2048M }
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
- `->select(['id','name','email'])` — no SELECT *
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

### P1: SPA UI DaVinci (prioridad alta — la cara del producto)

El SPA actual es un scaffold funcional: tabs + botones + toast. Necesita el nivel de MC y Event Pulse.

```
DC-UI-1: Graficas por tab (Chart.js)
  - Tab Asistentes: pie chart check-in vs no-checkin, timeline de registros
  - Tab Sesiones: bar chart asistencia por sesion, rating distribution
  - Tab Sponsors: bar chart leads por sponsor, pie chart tier distribution
  - Tab Gamificacion: leaderboard top 10 visual, puntos distribution
  - Tab Social: timeline de posts, foto grid preview

DC-UI-2: Diseno DaVinci Lumina Noir
  - Header premium con nombre evento + fecha + logo
  - Stat cards con iconos SVG y micro-animaciones (countUp.js)
  - Tabs con iconos + badge de conteo de items
  - Export rows con descripcion corta + preview de columnas
  - Empty states ilustrados por tab
  - Loading skeletons mientras carga stats

DC-UI-3: Panel de notificaciones mejorado
  - Slide-in con animacion
  - Estado del export: pending (spinner), completed (verde + link), error (rojo)
  - Progress indicator para export maestro ("Generando 28/44...")
  - Mark as read al abrir

DC-UI-4: Responsive + polish
  - Mobile: tabs horizontales con scroll, stat cards 2 columnas
  - Tablet: 3 columnas stats
  - Desktop: layout actual refinado
  - Transiciones entre tabs (fade)
  - Hover states en export rows
```

### P2: Formato XLSX real (maatwebsite/excel)

```
DC-XLSX-1: Implementar generateXlsx() en BaseExportJob
  - Usar maatwebsite/excel (ya instalado)
  - Worksheet por tab o archivo individual
  - Auto-width columns, header bold, freeze first row
  - Re-habilitar opcion XLSX en el selector de formato

DC-XLSX-2: Export maestro XLSX (opcional)
  - Opcion: ZIP con 44 CSVs o 1 XLSX con 9 worksheets (1 por tab)
```

### P3: Filtros avanzados en SPA

```
DC-FILT-1: Date range picker global
  - Filtrar exports por rango de fechas (created_at)
  - Integrar con cada job via $this->filters['date_from'] / ['date_to']

DC-FILT-2: Filtros por sub-entidad
  - Chat/Q&A/Polls: dropdown de sesiones
  - Leads/Visitas/Trivia: dropdown de sponsors
  - Juegos: dropdown de juegos
  - Puntos: dropdown de action types
```

### P4: Infra produccion

```
DC-INFRA-1: docker-compose.worker.yml para VPS-3
DC-INFRA-2: Read replica MySQL config (database.php read/write split)
DC-INFRA-3: Nginx try_files para SPA fallback
DC-INFRA-4: SANCTUM_STATEFUL_DOMAINS en .env produccion
DC-INFRA-5: Refactorizar 3 export jobs legacy para extender BaseExportJob
```

### P5: Nice-to-have futuro

```
DC-NICE-1: Exports programados (cron: "enviar leads cada lunes")
DC-NICE-2: API publica de analytics (para integraciones externas)
DC-NICE-3: Preview de datos antes de exportar (tabla paginada en SPA)
DC-NICE-4: Banner/sponsor impression tracking (requiere nueva tabla)
DC-NICE-5: Comparativa entre eventos (multi-evento en mismo Data Center)
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
