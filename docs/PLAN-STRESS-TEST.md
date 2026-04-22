# Plan de Stress Test — EventOS

> Simulacion completa de un dia de evento con 10,000 asistentes.
> Todos los flujos naturales: attendee, vendedor, admin, organizador.
> Objetivo: validar que la plataforma aguanta sin degradacion.
> Prerequisito: deploy en VPS (Docker + CI/CD)
> Actualizado: 2026-04-20

---

## Estado actual

### Lo que ya existe

| Componente | Estado | Ubicacion |
|-----------|--------|-----------|
| Scripts k6 (HTTP) | Listos | `tests/load/stress-full.js` (1,400 VUs, 6 escenarios) |
| Scripts k6 (admin) | Listos | `tests/load/stress-admin.js` (exports concurrentes) |
| Scripts k6 (socket) | Listos | `tests/load/stress-sockets.js` |
| Artillery (socket) | Listo | `tests/load/stress-sockets.yml` (1,500 conexiones) |
| Tokens pre-generados | Listos | `tests/load/tokens.json` |
| README interpretacion | Listo | `tests/load/README.md` |
| Arquitectura HA | Documentada | `docs/DISPONIBILIDAD-HA.md` |
| Anti-colapso | Implementado | Jitter, throttle, dedup, cache warm |

### Lo que falta

| Componente | Que falta |
|-----------|-----------|
| Escenarios flujo natural | Scripts que simulen persona real completa (no endpoints sueltos) |
| Endpoints nuevos en tests | session attendance, stand stats, stand contacts, session export |
| Escala 10K | Subir de 1,400 VUs a 10,000 VUs con ramp up realista |
| Deploy VPS | Docker Compose + CI/CD (prerequisito) |
| VPS generador de carga | Hetzner temporal para lanzar tests |
| Monitoreo en vivo | Sentry + Grafana durante el test |

---

## Infraestructura necesaria

### VPS 1 — Servidor EventOS (produccion)

```
Hetzner CPX31 (4 vCPU, 8 GB RAM) — prueba inicial 1,000 usuarios
Hetzner CPX41 (8 vCPU, 16 GB RAM) — prueba 5,000 usuarios
Hetzner CCX33 (16 vCPU, 32 GB RAM) — prueba 10,000 usuarios

Docker Compose: 6 servicios
  app (Laravel PHP-FPM + Nginx)
  queue (php artisan queue:work)
  scheduler (php artisan schedule:run)
  socket (Node.js Socket.IO)
  mysql (MySQL 8)
  redis (Redis 7)
```

### VPS 2 — Generador de carga (temporal, se destruye al terminar)

```
Hetzner CPX31 (4 vCPU, 8 GB RAM) — suficiente para generar 10K VUs
Costo: ~$0.02/hora = ~$0.50 por dia de test
Herramientas: k6 + Artillery + node.js scripts custom
```

### Costo total estimado

| Concepto | Costo |
|---------|-------|
| VPS servidor (16 vCPU, test) | ~$60/mes (se puede bajar despues) |
| VPS generador (temporal, 1 dia) | ~$1 |
| Dominio + DNS | Ya existe |
| Cloudflare | Free tier |
| **Total stress test** | **~$61 una vez** |

---

## Escenarios de flujo natural

### Escenario 1: Attendee (10,000 usuarios)

Cada usuario virtual ejecuta este flujo completo con tiempos de espera reales:

```
FASE 1 — Llegada (minuto 0-30)
  ├── POST /auth/login (email + password + event_slug)
  ├── GET /events/{id}/modules
  ├── GET /events/{id}/branding
  ├── GET /events/{id}/agenda
  ├── GET /events/{id}/speakers
  ├── GET /events/{id}/highlights
  ├── GET /events/{id}/announcements
  ├── sleep 5-15s (usuario mira el home)
  ├── POST /events/{id}/checkin (QR scan → +50 puntos)
  ├── WebSocket connect → join:event
  └── sleep 10-30s

FASE 2 — Sesion en vivo (minuto 30-90)
  ├── WebSocket emit join:session (keynote)
  ├── GET /sessions/{id}/chat/messages (historial)
  ├── sleep 2-5min
  ├── WebSocket emit chat:send ("Excelente presentacion!")
  ├── sleep 1-3min
  ├── WebSocket emit chat:send (otro mensaje)
  ├── sleep 2-5min
  ├── GET /events/{id}/sessions/{id}/questions (Q&A)
  ├── POST /events/{id}/sessions/{id}/questions (hacer pregunta) — 30% de usuarios
  ├── POST /events/{id}/sessions/{id}/questions/{id}/upvote — 50% de usuarios
  ├── sleep 3-5min
  ├── GET /sessions/{id}/poll/active
  ├── POST /polls/{id}/vote (votar encuesta) — 80% de usuarios
  ├── sleep 5-10min
  ├── POST /events/{id}/agenda/{id}/favorite (favoritar sesion)
  ├── sleep 10-20min
  ├── WebSocket emit leave:session
  └── POST /events/{id}/sessions/{id}/rate (4-5 estrellas) — 70% de usuarios

FASE 3 — Interaccion social (minuto 90-120)
  ├── GET /events/{id}/wall (social feed)
  ├── POST /events/{id}/wall (publicar) — 20% de usuarios
  ├── POST /events/{id}/wall/{id}/like — 40% de usuarios
  ├── POST /events/{id}/wall/{id}/comments — 15% de usuarios
  ├── sleep 2-5min
  ├── GET /events/{id}/networking/directory
  ├── GET /events/{id}/networking/suggested
  ├── POST /events/{id}/networking/request — 30% de usuarios
  ├── sleep 1-3min
  ├── GET /events/{id}/sponsors
  ├── POST /events/{id}/sponsors/{id}/view (vista implicita)
  ├── POST /events/{id}/sponsors/{id}/favorite — 25% de usuarios
  └── sleep 5-10min

FASE 4 — Segunda sesion / Silent disco (minuto 120-180)
  ├── WebSocket emit join:session (workshop)
  ├── chat:send (1-3 mensajes)
  ├── sleep 20-30min
  ├── WebSocket emit leave:session
  ├── Si silent_disco: join:session otra sesion → 5min → leave → join otra
  └── POST rate session

FASE 5 — Gamificacion + cierre (minuto 180-210)
  ├── GET /events/{id}/leaderboard
  ├── GET /me/points?event_id={id}
  ├── GET /events/{id}/my-passport
  ├── POST /events/{id}/rewards/{id}/redeem — 10% de usuarios
  ├── sleep 5min
  ├── GET /events/{id}/post-event-survey
  ├── POST /polls/{id}/vote (encuesta post-evento)
  └── Disconnect socket → logout
```

### Escenario 2: Vendedor (50 usuarios)

```
FASE 1 — Setup (minuto 0-15)
  ├── POST /auth/login
  ├── GET /me/stand
  ├── GET /me/stand/stats (debe ser 0)
  └── WebSocket connect

FASE 2 — Escaneo activo (minuto 15-180, loop)
  ├── POST /leads (scan QR attendee) — cada 2-5min
  ├── PUT /leads/{id} (clasificar tier: hot/warm/cold)
  ├── sleep 2-5min
  ├── GET /me/stand/stats (verificar que crece)
  ├── GET /me/stand/contacts (verificar solicitudes)
  ├── sleep 10-15min
  └── repeat

FASE 3 — Export (minuto 120 y minuto 180)
  ├── GET /me/leads/export (CSV con resumen stats)
  └── Verificar que descarga OK

FASE 4 — Cierre (minuto 180-210)
  ├── GET /me/stand/stats (stats finales)
  └── Disconnect
```

### Escenario 3: Admin / Organizador (5 usuarios)

```
FASE 1 — Pre-evento (minuto 0-15)
  ├── Login Filament (session auth)
  ├── Dashboard check
  └── GET /api/v1/health (verificar todo verde)

FASE 2 — Evento en vivo (minuto 15-180, loop)
  ├── Abrir Mission Control (monitor/{sessionId})
  ├── GET /internal/session-metrics/{id} (audience + chat count)
  ├── PATCH /admin/sessions/{id}/live-config (toggle chat/Q&A/polls)
  ├── sleep 5-10min
  ├── POST /internal/poll/broadcast (activar encuesta)
  ├── sleep 5min
  ├── POST /internal/poll/broadcast (cerrar encuesta)
  ├── GET /events/{id}/sessions/{id}/questions/pending (moderar Q&A)
  ├── PATCH /questions/{id}/moderate (aprobar/responder) — 3-5 por ciclo
  ├── sleep 10min
  ├── POST /admin/attendees/{id}/ban (banear spam) — 1-2 por hora
  ├── GET /sessions/{id}/stats (verificar metricas)
  ├── Click "Exportar CSV" (ExportSessionStatsJob queue)
  ├── Verificar notificacion campana + descargar
  └── repeat

FASE 3 — Post-evento (minuto 180-210)
  ├── GET /sessions/{id}/stats (todas las sesiones)
  ├── Export CSV de cada sesion
  ├── GET /me/stand/stats (para cada sponsor)
  └── Verificar que todo se genero correctamente
```

---

## Metricas a capturar

### Thresholds (pass/fail)

| Metrica | Aceptable | Warning | Fallo |
|---------|-----------|---------|-------|
| API p50 | <200ms | <500ms | >500ms |
| API p95 | <500ms | <2s | >2s |
| API p99 | <1s | <5s | >5s |
| Socket connect time | <500ms | <2s | >2s |
| Socket message latency | <100ms | <500ms | >500ms |
| HTTP errors (5xx) | 0% | <0.1% | >0.1% |
| HTTP errors (429 rate limit) | <1% | <5% | >5% |
| Socket reconnections | <0.5% | <2% | >2% |
| Queue backlog | <50 jobs | <200 jobs | >200 jobs |
| CSV export time | <30s | <2min | >2min |

### Metricas de servidor (monitorear durante test)

| Metrica | Donde |
|---------|-------|
| CPU % | `htop` o Grafana |
| RAM % | `free -m` |
| Redis memory | `redis-cli info memory` |
| MySQL connections | `SHOW PROCESSLIST` |
| MySQL slow queries | slow query log |
| Disk I/O | `iostat` |
| Network | `iftop` |
| Socket connections | Socket.IO admin UI o `fetchSockets()` |
| Queue workers active | `php artisan queue:monitor` |
| Laravel response time | Telescope o Sentry |

---

## Plan de ejecucion

### Pre-requisitos

- [ ] Deploy Docker Compose en VPS (SEC-4)
- [ ] Sentry configurado (SEC-5)
- [ ] DNS + SSL configurado
- [ ] Seeders de datos: 10,000 attendees + 50 vendedores + 5 admins + 30 sesiones
- [ ] Tokens pre-generados para los 10,050 usuarios
- [ ] VPS generador de carga aprovisionado

### Dia de test

```
Hora    Actividad
────────────────────────────────────────────────
08:00   Provisionar VPS servidor (16 vCPU, 32 GB)
08:30   Deploy Docker Compose + seed datos
09:00   Health check: API, socket, Redis, MySQL todo verde
09:15   Configurar monitoreo: Sentry, htop, redis-cli monitor
09:30   VPS generador: instalar k6 + artillery + scripts

10:00   TEST 1 — Warmup (100 usuarios, 15 min)
10:20   Revisar metricas, fix si hay errores basicos

10:30   TEST 2 — Carga media (1,000 usuarios, 30 min)
11:00   Revisar metricas, ajustar configs si p95 > 1s

11:15   TEST 3 — Carga alta (5,000 usuarios, 60 min)
12:15   Pausa, revisar logs, fix issues criticos

13:00   TEST 4 — Carga completa (10,000 usuarios, 2 horas)
        Flujo natural completo: login → checkin → sesiones → chat →
        polls → Q&A → social → networking → gamification → export →
        rating → logout. Admin exportando CSV durante carga maxima.
        Silent disco: 3 sesiones simultaneas con cambio de canal.
15:00   Fin del test. Capturar reporte final.

15:30   Analisis de resultados
        - Endpoints mas lentos
        - Errores por tipo
        - Bottlenecks (CPU? RAM? MySQL? Redis? Socket?)
        - Comparar con thresholds

16:00   Documentar hallazgos en STRESS-TEST-RESULTS.md
16:30   Lista de fixes priorizados
17:00   Destruir VPS generador de carga

Dias siguientes: implementar fixes, repetir test hasta que pase limpio
```

### Test de regresion (post-fixes)

Despues de cada fix, repetir TEST 4 (10K, 2h) para verificar que:
1. El fix resolvio el problema
2. No introdujo regresiones
3. Todas las metricas estan dentro de thresholds

---

## Scripts a actualizar antes del test

### Agregar a stress-full.js

```
1. attendeeFlow — flujo completo natural (5 fases, 210 min)
2. vendorFlow — scan, stats, contacts, export (loop 180 min)
3. adminFlow — MC, moderar, export, ban (loop 180 min)
```

### Endpoints nuevos a incluir

| Endpoint | Escenario | Frecuencia |
|---------|-----------|-----------|
| GET /me/stand/stats | vendorFlow | Cada 10-15 min |
| GET /me/stand/contacts | vendorFlow | Cada 15-20 min |
| GET /sessions/{id}/stats | adminFlow | Cada sesion |
| GET /sessions/{id}/viewers | adminFlow | Cada 5 min durante sesion |
| POST /sessions/{id}/export (queue) | adminFlow | 1 por sesion |
| GET /me/leads/export (CSV mejorado) | vendorFlow | 2 veces |
| WebSocket join:session + Redis tracking | attendeeFlow | 2-3 sesiones por user |
| WebSocket leave:session + Redis tracking | attendeeFlow | Al salir de cada sesion |

### Verificaciones especificas

| Verificacion | Como |
|-------------|------|
| Attendance crece en tiempo real | GET /sessions/{id}/viewers durante sesion activa |
| Export no bloquea API | Descargar CSV mientras 10K usuarios chatean |
| Gamification puntos correctos | GET /me/points al final = suma de todas las acciones |
| Silent disco cambio de canal | join → leave → join otra sesion, verificar audience count |
| Notificacion campana aparece | Admin recibe notificacion DB despues de export queue |
| Rate limits funcionan | 429 responses para usuarios que excedan limites |
| Ban desconecta socket | Admin banea → usuario pierde conexion en <2s |
| Push notifications llegan | Verificar Expo receipts post-test |

---

## QA de integridad funcional (pre-stress test)

> Antes de lanzar carga, validar que la logica de negocio no tiene bugs de integridad.
> Estos bugs no se detectan con stress test — solo con tests que verifican **consecuencias**, no solo acciones.
> Leccion aprendida: sesion 2026-04-21 encontro 8 bugs criticos/altos revisando Session Lifecycle + MC.

### Principio: no revisar modulo por modulo

Revisar CRUD simples (speakers, FAQ, documents) es desperdicio — ahi los bugs son cosmeticos.
Los bugs de integridad viven en modulos con **estado derivado, cascadas, cache multicapa o concurrencia**.

### Modulos que requieren revision profunda

| Modulo | Por que | Que verificar |
|--------|---------|---------------|
| **Session Lifecycle** | Cascadas (delay→next, cancel→revert), Carbon mutation, estado compartido | Duracion preservada, sesiones ya iniciadas intocables, revert correcto, .ics con publicEnd |
| **Agenda RT** | Cache 3 capas (MMKV + react-query + socket) | Cambio desde Filament/MC refleja en app <3s, mi-agenda tambien, reconexion socket resync |
| **Gamification** | Puntos derivados de 13+ acciones, canje, leaderboard | Puntos = suma exacta de acciones, canje descuenta, no doble award, leaderboard ordenado |
| **Networking requests** | Optimistic UI + infinite query + socket RT | Request → accept → aparece en contactos, no duplicados, badge correcto, cancel revierte |
| **Registration flow** | 6 modos combinables (approval + access_code + invite_only + domain + depends_on + verify) | Cada combinacion funciona, token no reutilizable, race condition max_uses, replay pre-fill |
| **Check-in / Room** | Kiosk cache vs API source of truth, staff assignment, offline queue | Scan correcto, ban rechazado, offline queue flush parcial, concurrent accept lock |
| **Moderation** | Ban RT, palabras bloqueadas, chat delete, Q&A approve | Ban desconecta socket <2s, palabras filtradas, delete RT, aprobacion RT en display |

### Smoke tests E2E (1 test, flujo completo)

Estos tests simulan un flujo de usuario real de punta a punta. Si pasan, la integridad funcional esta validada.

#### Test 1: Dia de evento completo (backend)
```
Crear evento → 3 salas × 3 sesiones/sala → 5 attendees, 2 vendedores, 1 admin
1. Admin: delay sesion A1 +15min → verificar:
   - A1.end_datetime intacto, A1.adjusted_end_at = original+15
   - A2.start movido +15, A2.duracion preservada (60 min)
   - A3 NO movido
   - .ics de A1 usa adjusted_end_at
2. Admin: start sesion A1 → verificar:
   - actual_start_at = now
   - A2 (ya movida) NO se mueve otra vez
3. Admin: end sesion A1 antes del original → verificar:
   - adjusted_end_at cleared, A2 revertida
4. Admin: cancel sesion B1 (que tenia delay) → verificar:
   - B2 revertida, B1.adjusted_end_at cleared
5. Attendee: favoritar A2 → verificar mi-agenda incluye A2
6. Vendedor: scan attendee → lead creado → puntos gamification
7. Admin: ban attendee → verificar 403 en siguiente request
```

#### Test 2: Cache RT de agenda (app, manual o Detox)
```
1. Abrir app → ver agenda → anotar hora de sesion X
2. Desde Filament: cambiar titulo sesion X → app debe mostrar nuevo titulo <3s
3. Desde MC: delay sesion X +10min → app debe mostrar nueva hora <3s
4. Cambiar a tab Mi Agenda → verificar que tambien tiene hora nueva
5. Cerrar app, reabrir → verificar que muestra hora nueva (no la vieja del MMKV)
6. Matar socket server → Filament cambia titulo → levantar socket → app debe sincronizar al reconectar
```

#### Test 3: Gamification integridad de puntos
```
1. Attendee con 0 puntos
2. Check-in (+50) → verify total = 50
3. Favoritar sesion (+10) → verify total = 60
4. Scan por vendedor (+20 lead) → verify total = 80
5. Rating sesion (+15) → verify total = 95
6. Canjear reward (-50) → verify total = 45
7. Intentar canjear reward de 50 → verify rejected (insuficiente)
8. Leaderboard: attendee en posicion correcta
```

#### Test 4: Networking consistency
```
1. A envia request a B → B tiene 1 pending
2. B acepta → A y B en contactos mutuos, request desaparece
3. A envia request a C → C rechaza → request desaparece, no en contactos
4. Directory: B ya no aparece como "sugerido" para A
5. Badge networking: B acepto → badge +1 para A
```

### Chaos testing (post-stress, pre-produccion)

| Escenario | Que hacer | Que debe pasar |
|-----------|-----------|----------------|
| Socket muere 30s | `kill socket`, esperar, levantar | App reconecta, refetch agenda/announcements, no pierde estado |
| Redis muere 10s | `redis-cli shutdown`, levantar | API degrada (no cache), no crash. Socket reconecta pub/sub |
| MySQL slow (5s queries) | Simular con `SLEEP(5)` en trigger | API devuelve 504 timeout, app muestra error, no crash |
| 2 MCs misma sesion | Abrir 2 tabs MC, operar ambas | Estado sincronizado via socket, no conflicto |
| App pierde red 60s | Modo avion, volver | Socket reconecta, agenda fresca, chat historial intacto |
| Double-tap rapido | Tap start 2 veces en MC | Solo 1 request pasa (409 en segunda) |

### Calendario pre-produccion

```
Semana -8 (junio):  Smoke tests E2E (Test 1-4) — automatizar con PHPUnit/Pest
Semana -6:          Stress test carga (10K plan existente)
Semana -4:          Chaos testing + fixes
Semana -3:          Dry run con cliente (Eventos Efectivos opera MC, nosotros attendee)
Semana -2:          Fix bugs del dry run + re-test
Semana -1:          Freeze — solo hotfixes criticos
Dia D (sept):       Evento real
```

---

## Documentos relacionados

| Doc | Contenido |
|-----|-----------|
| `docs/DISPONIBILIDAD-HA.md` | Arquitectura 99.99%, anti-colapso, failover |
| `tests/load/README.md` | Guia de uso de scripts k6/Artillery |
| `tests/load/stress-full.js` | Script actual 1,400 VUs (base para 10K) |
| `docs/PENDIENTES.md` | Deploy + SEC-4/5 como prerequisitos |
| `EventOS_Roadmap.md` | Timeline deploy + Bancolombia |

---

_Plan de Stress Test v1.0 — EventOS_
_20 abril 2026_
