si# Plan de Stress Test — EventOS

> Validacion empirica de que 10,000 asistentes simultaneos tienen experiencia indistinguible de una plataforma enterprise.
> No se trata de probar que Laravel no se cae. Se trata de probar que el dia del evento, con 12,000 personas en Movistar Arena, el director ejecutivo de Bancolombia abre la app y siente instantaneidad.
> Version: 2.1 | Fecha: 2026-04-24
> Stack: DigitalOcean consolidado en sao1 (Sao Paulo) + Cloudflare adelante + R2 storage
> Prerequisito: deploy HA real (no Docker Compose local monolitico)

---

## 0. Cambios vs v1.0

| Area | v1.0 | v2.1 |
|------|------|------|
| Stack de prueba | VPS unico + MySQL/Redis local en Docker | Stack identico a produccion: 2 Droplets DO + DO Managed MySQL + DO Managed Redis + CF LB, todo en sao1 |
| Region | No especificada (default us) | DO sao1 (Sao Paulo) — RTT Bogota ~80ms vs us-east ~150ms |
| Generador de carga | Hetzner local (2ms RTT) | k6 Cloud regional o Droplet en otra region con `tc` para simular 4G Colombia |
| Threshold p95 | < 2s warning | < 800ms hard limit |
| Threshold 5xx | < 0.1% aceptable | < 0.01% hard limit |
| Metricas cliente | No estaban | TTI, click-to-response, RUM desde device real |
| Login stampede | Dentro del flujo general | Test aislado (bcrypt es CPU-bound, asesino silencioso) |
| Failover durante carga | No estaba | Test critico: matar Droplet-1 con 10K activos |
| Break point | No estaba | Escalar hasta romper para conocer margen real |
| Red degradada | No estaba | `tc` simulando 150ms RTT + 2% packet loss + 1 Mbps |
| Export aislado | Mencionado | Test formal: API p95 con/sin exports debe ser igual |

---

## 1. Filosofia del test

### Lo que NO es este test

No es "ver si el servidor aguanta". Ese es un test de infraestructura de 2005. Hoy cualquier VPS decente aguanta 10K HTTP/s con contenido estatico. La pregunta no es esa.

### Lo que SI es este test

Validar empiricamente tres cosas que el cliente va a percibir:

1. **Que la app se sienta instantanea** — TTI < 3s en 4G, click-to-response < 500ms percibido.
2. **Que no haya clicks perdidos** — 5xx rate < 0.01%, timeouts practicamente nulos, retry automatico invisible.
3. **Que las fallas reales sean invisibles** — si VPS-1 muere a las 14:37 durante el keynote, nadie debe enterarse.

Si el test pasa en estos tres ejes, la plataforma es enterprise. Si pasa solo en "el p95 de backend es 400ms", no necesariamente.

### El principio que rige todo

**Probar contra la realidad, no contra un universo paralelo.** Tu arquitectura de produccion es DO consolidado en sao1 (2 Droplets + Managed MySQL + Managed Redis en VPC privada) detras de Cloudflare. Tu test tiene que correr contra eso mismo, o el test miente.

---

## 2. Estado actual

### Lo que ya existe

| Componente | Estado | Ubicacion |
|-----------|--------|-----------|
| Scripts k6 (HTTP) | Base | `tests/load/stress-full.js` (1,400 VUs, 6 escenarios) |
| Scripts k6 (admin) | Base | `tests/load/stress-admin.js` (exports concurrentes) |
| Scripts k6 (socket) | Base | `tests/load/stress-sockets.js` |
| Artillery (socket) | Base | `tests/load/stress-sockets.yml` (1,500 conexiones) |
| Tokens pre-generados | Listos | `tests/load/tokens.json` |
| README interpretacion | Listo | `tests/load/README.md` |
| Arquitectura HA | Documentada | `docs/DISPONIBILIDAD-HA.md` |
| Anti-colapso | Implementado | Jitter, throttle, dedup, cache warm |

### Lo que falta (critico)

| Componente | Que falta | Prioridad |
|-----------|-----------|-----------|
| Stack HA desplegado | 2 Droplets DO sao1 + DO Managed MySQL + DO Managed Redis + CF (DNS+WAF+LB) en staging | P0 |
| Generador de carga realista | k6 Cloud o VPS con `tc` para red Colombia | P0 |
| Escenarios flujo natural | Scripts que simulen persona real completa | P0 |
| Escalado 10K | Subir de 1,400 a 10,000 VUs con ramp up realista | P0 |
| Test login stampede | Script aislado bcrypt-aware | P1 |
| Test failover | Chaos durante 10K activos | P1 |
| Test break point | Escalada hasta romper | P1 |
| Test red degradada | `tc` o k6 network profiles | P1 |
| RUM device real | Sentry Performance en app + device fisico midiendo | P1 |
| Monitoreo live | Sentry + Grafana + synthetic checks desde Bogota | P2 |

---

## 3. Infraestructura de test (NO negociable)

### 3.1 Stack del servidor = identico a produccion

```
Cloudflare (DNS + WAF + DDoS + CDN estaticos)
   |
   +-- api-staging.eventos.com (proxy a origins DO)
           |
           +-- Droplet-1 DO sao1 (4 vCPU, 8 GB RAM)
           +-- Droplet-2 DO sao1 (4 vCPU, 8 GB RAM)
                 |
                 +-- Nginx + Laravel PHP-FPM + Socket.IO (Redis adapter)
                 |
                 +-- Cada uno conecta via VPC privada DO (< 1ms RTT) a:
                      - DO Managed MySQL sao1 (1GB + read replica)
                      - DO Managed Redis sao1 (1GB con HA)
                 +-- Y a Cloudflare R2 para storage (mantenido por egress gratis)
   |
   +-- No hay MySQL local. No hay Redis local. No hay "todo en un Droplet".
```

**Por que DO sao1 consolidado y no Hetzner US + servicios dispersos:**

| Decision | Razon |
|----------|-------|
| **DO en lugar de Hetzner** | Audiencia 100% Latam. Hetzner no tiene region Latam. DO sao1 baja RTT Bogota de ~150ms (us-east) a ~80ms |
| **Sao1 (Sao Paulo)** | Region mas cercana a Colombia. Tiene Managed MySQL, Managed Redis, VPC, todo lo que necesitas |
| **DO Managed MySQL en lugar de PlanetScale** | Vive en la misma VPC que los Droplets — RTT < 1ms vs 80-150ms a PlanetScale. Pricing flat (no se dispara en bursts de evento) |
| **DO Managed Redis en lugar de Upstash** | VPC privada, no TLS overhead por cada comando, pricing flat |
| **R2 se mantiene** | Egress gratis es imposible de batir. Spaces de DO no esta en sao1 (sgp1, ams3, fra1, nyc3, sfo3) — irrelevante para ti |
| **Cloudflare adelante** | DO LB no tiene WAF ni DDoS protection comparable. CF se queda como primera linea |

**Por que no el CCX33 monolitico de v1.0:**
MySQL local en Docker tiene 0.1ms de latencia. DO Managed MySQL en la misma VPC tiene < 1ms. No es la misma config pero el delta es marginal (vs 150ms si la DB estuviera fuera de la region). Lo que NO podemos hacer es testear con MySQL local — eso fue el error de v1.0, porque produccion va a tener Managed DB y la diferencia de comportamiento con MySQL local + reservas de memoria + caches calientes es gigante.

**Costo stack test (1 mes de uso, se baja despues a produccion):**

| Concepto | Costo |
|---------|-------|
| Droplet-1 sao1 (4 vCPU, 8 GB) | $48 |
| Droplet-2 sao1 (4 vCPU, 8 GB) | $48 |
| DO Managed MySQL 1GB sao1 + read replica | $30 |
| DO Managed Redis 1GB sao1 (HA) | $15 |
| Cloudflare Pro + LB | $26 |
| R2 bucket test | ~$1 |
| **Total stack staging/test** | **~$168/mes** |

Comparado contra los $76/mes del stack actual (Hetzner + PlanetScale + Upstash + CF), son ~$92/mes mas. La ganancia: latencia inter-servicio < 1ms (vs 80-150ms), latencia usuario ~80ms (vs ~150ms), una sola consola, una sola factura, pricing predictible.

### 3.2 Generador de carga = latencia real del usuario

**Problema con v1.0:** generador en mismo data center que servidor = 2-5ms RTT. El usuario real en Movistar Arena tiene 80-300ms RTT + packet loss 1-3%.

**Importante:** si el generador esta en sao1 (mismo region que los Droplets de produccion), tampoco vamos a medir latencia real Bogota-Sao Paulo. Hay que pensar bien la ubicacion del generador.

**Opciones (elegir una):**

| Opcion | Costo | Fidelidad | Esfuerzo |
|--------|-------|-----------|----------|
| **k6 Cloud con nodos Sao Paulo** | ~$99 por test run | Alta (latencia Latam real medida desde regiones DO/AWS reales) | Bajo |
| **Droplet temporal en sao1 + `tc`** | ~$2 (1 dia, $0.08/hora 4vCPU/8GB) | Media (latencia simulada via traffic control) | Medio |
| **Droplet temporal en nyc3 (sin tc)** | ~$2 | Media-baja (RTT 150ms real, pero no simula 4G Colombia) | Trivial |
| **Artillery desde laptop con throttling Chrome** | $0 | Baja (solo valida UX en tu maquina) | Trivial, solo dev |

**Recomendacion: k6 Cloud para el test final de 10K.** Para iteraciones intermedias, Droplet temporal en sao1 con `tc` basta:

```bash
# En el Droplet generador (mismo sao1), antes de correr k6:
# Simular red 4G Colombia congestionada
sudo tc qdisc add dev eth0 root netem delay 150ms 30ms distribution normal loss 2% rate 1mbit
# 150ms RTT +- 30ms jitter, 2% packet loss, 1 Mbps bandwidth
# Esto simula el RTT Bogota-saopaulo + congestion 4G Movistar
```

### 3.3 Device real para metricas cliente

Esto NO es opcional. Pon un iPhone de gama media (ej. iPhone 12) y un Android medio (ej. Samsung A54) conectados a 4G real Movistar/Claro en Bogota, con Sentry Performance en la app. Durante el test de 10K, esos dos devices se conectan y navegan los mismos flujos. Se captura:

- TTI (Time To Interactive) — desde cold start hasta que la home es tappable
- FCP (First Contentful Paint) — cuando aparece el primer pixel util
- Click-to-response — tiempo desde tap hasta feedback visual
- Chat latency end-to-end — desde envio hasta aparicion en otra sesion

**Sin device real, no sabes lo que el usuario siente. El resto del test es backend theatre.**

---

## 4. Thresholds enterprise-grade

### 4.1 Pass/fail del test (backend)

| Metrica | Hard limit | Warning | Fallo |
|---------|-----------|---------|-------|
| API p50 | < 150ms | < 400ms | >= 400ms |
| API p95 | **< 800ms** | < 1.5s | >= 1.5s |
| API p99 | **< 2s** | < 3.5s | >= 3.5s |
| Socket connect time | < 400ms | < 1s | >= 1s |
| Socket message latency (chat end-to-end) | < 150ms | < 400ms | >= 400ms |
| HTTP 5xx rate | **< 0.01%** | < 0.05% | >= 0.05% |
| HTTP 429 (rate limit) | < 0.5% | < 2% | >= 2% |
| Socket reconnects | **< 0.1%** | < 0.5% | >= 0.5% |
| Queue backlog (default) | < 50 jobs | < 200 | >= 200 |
| Queue backlog (exports) | < 20 jobs | < 100 | >= 100 |
| CSV export time | < 30s | < 90s | >= 90s |

### 4.2 Pass/fail del test (cliente, desde device real)

| Metrica | Hard limit | Warning | Fallo |
|---------|-----------|---------|-------|
| TTI (cold start en 4G) | **< 3s** | < 5s | >= 5s |
| FCP | < 1.5s | < 3s | >= 3s |
| Click-to-response (con optimistic UI) | < 100ms | < 300ms | >= 300ms |
| Click-to-response (sin optimistic UI) | < 500ms | < 1.2s | >= 1.2s |
| Tiempo abrir agenda (tab change) | < 200ms | < 600ms | >= 600ms |
| Chat message ida-vuelta | < 300ms | < 800ms | >= 800ms |

### 4.3 Metricas de servidor durante el test

| Metrica | Donde | Alarma |
|---------|-------|--------|
| CPU Droplet-1, Droplet-2 | htop / Grafana | > 75% sostenido |
| RAM Droplet | free -m | > 80% |
| PHP-FPM workers activos | `pm status` | > 90% del pool |
| DO Managed Redis ops/sec | DO panel | > 80% del plan |
| DO Managed MySQL connections | DO panel | > 80% pool (default 25 por droplet) |
| DO Managed MySQL slow queries | DO Insights | cualquiera > 500ms |
| Disk I/O Droplet | iostat | > 80% util |
| Socket connections | `fetchSockets()` | > 6K por Droplet |
| Queue workers activos | `queue:monitor` | backlog creciente |
| Sentry error rate | Sentry dashboard | > 0.05% |

### 4.4 Por que estos thresholds y no los de v1.0

- **p95 < 800ms:** por debajo de 1s, el cerebro lo percibe como "fluido". Arriba de 1s, "lento". 2s es inaceptable.
- **p99 < 2s:** en 10K usuarios x 100 requests = 1M requests. p99 = 1% = 10,000 requests lentos por evento. Con p99 = 2s, son 10K clicks con 2s. Con el threshold anterior (p99 = 5s), son 10K clicks con 5s. Inaceptable.
- **5xx < 0.01%:** 100 errores en 1M requests. Con threshold anterior (0.1%), son 1000 errores visibles por evento. La diferencia entre enterprise y banal.
- **Socket reconnect < 0.1%:** 10 usuarios de 10K con reconexiones. Threshold anterior (0.5%) = 50 usuarios con perdida de chat, Q&A, polls.

---

## 5. Escenarios de flujo natural

### 5.1 Escenario Attendee (10,000 VUs)

Flujo completo con tiempos de espera reales:

```
FASE 1 — Llegada (minuto 0-30)
  POST /auth/login (email + password + event_slug)
  GET /events/{id}/modules
  GET /events/{id}/branding
  GET /events/{id}/agenda
  GET /events/{id}/speakers
  GET /events/{id}/highlights
  GET /events/{id}/announcements
  sleep 5-15s (usuario mira el home)
  POST /events/{id}/checkin (QR scan, +50 puntos)
  WebSocket connect -> join:event
  sleep 10-30s

FASE 2 — Sesion en vivo (minuto 30-90)
  WebSocket emit join:session (keynote)
  GET /sessions/{id}/chat/messages (historial)
  sleep 2-5min
  WebSocket emit chat:send ("Excelente presentacion!")
  sleep 1-3min
  WebSocket emit chat:send (otro mensaje)
  sleep 2-5min
  GET /events/{id}/sessions/{id}/questions (Q&A)
  POST /events/{id}/sessions/{id}/questions (preguntar) — 30% de users
  POST /events/{id}/sessions/{id}/questions/{id}/upvote — 50% de users
  sleep 3-5min
  GET /sessions/{id}/poll/active
  POST /polls/{id}/vote — 80% de users
  sleep 5-10min
  POST /events/{id}/agenda/{id}/favorite
  sleep 10-20min
  WebSocket emit leave:session
  POST /events/{id}/sessions/{id}/rate — 70% de users

FASE 3 — Interaccion social (minuto 90-120)
  GET /events/{id}/wall
  POST /events/{id}/wall (publicar) — 20% de users
  POST /events/{id}/wall/{id}/like — 40% de users
  POST /events/{id}/wall/{id}/comments — 15% de users
  sleep 2-5min
  GET /events/{id}/networking/directory
  GET /events/{id}/networking/suggested
  POST /events/{id}/networking/request — 30% de users
  sleep 1-3min
  GET /events/{id}/sponsors
  POST /events/{id}/sponsors/{id}/view
  POST /events/{id}/sponsors/{id}/favorite — 25% de users
  sleep 5-10min

FASE 4 — Segunda sesion / Silent disco (minuto 120-180)
  WebSocket emit join:session (workshop)
  chat:send (1-3 mensajes)
  sleep 20-30min
  WebSocket emit leave:session
  Si silent_disco: join otra -> 5min -> leave -> join otra
  POST rate session

FASE 5 — Gamificacion + cierre (minuto 180-210)
  GET /events/{id}/leaderboard
  GET /me/points?event_id={id}
  GET /events/{id}/my-passport
  POST /events/{id}/rewards/{id}/redeem — 10% de users
  sleep 5min
  GET /events/{id}/post-event-survey
  POST /polls/{id}/vote (encuesta post)
  Disconnect socket -> logout
```

### 5.2 Escenario Vendedor (50 VUs)

```
FASE 1 — Setup (minuto 0-15)
  POST /auth/login
  GET /me/stand
  GET /me/stand/stats (debe ser 0)
  WebSocket connect

FASE 2 — Escaneo activo (minuto 15-180, loop)
  POST /leads (scan QR attendee) — cada 2-5min
  PUT /leads/{id} (clasificar hot/warm/cold)
  sleep 2-5min
  GET /me/stand/stats (verificar crece)
  GET /me/stand/contacts (verificar solicitudes)
  sleep 10-15min
  repeat

FASE 3 — Export (minuto 120 y minuto 180)
  GET /me/leads/export (CSV)
  Verificar descarga OK

FASE 4 — Cierre (minuto 180-210)
  GET /me/stand/stats finales
  Disconnect
```

### 5.3 Escenario Admin / Organizador (5 VUs)

```
FASE 1 — Pre-evento (minuto 0-15)
  Login Filament
  Dashboard check
  GET /api/v1/health

FASE 2 — Evento en vivo (minuto 15-180, loop)
  Abrir Mission Control (monitor/{sessionId})
  GET /internal/session-metrics/{id}
  PATCH /admin/sessions/{id}/live-config (toggle chat/Q&A/polls)
  sleep 5-10min
  POST /internal/poll/broadcast (activar encuesta)
  sleep 5min
  POST /internal/poll/broadcast (cerrar encuesta)
  GET /events/{id}/sessions/{id}/questions/pending
  PATCH /questions/{id}/moderate — 3-5 por ciclo
  sleep 10min
  POST /admin/attendees/{id}/ban — 1-2 por hora
  GET /sessions/{id}/stats
  Click "Exportar CSV" (ExportSessionStatsJob queue)
  Verificar notificacion campana + descarga
  repeat

FASE 3 — Post-evento (minuto 180-210)
  GET /sessions/{id}/stats (todas)
  Export CSV de cada sesion
  GET /me/stand/stats por sponsor
  Verificar integridad
```

---

## 6. Tests criticos faltantes en v1.0

### 6.1 Test de Login Stampede (aislado)

**Por que existe:** bcrypt es CPU-bound (~50-100ms por hash segun cost factor). 10K logins en 10 minutos = pico de 50-100 logins/seg. Si los 2 Droplets tienen 4 vCPU cada uno, saturas CPU en bcrypt y **todo cae** — no solo login, sino la API entera porque PHP-FPM queda sin workers libres.

**Escenario:**
```
Ramp up: 0 -> 10,000 logins en 10 minutos (16 logins/seg sostenido)
Pico sinteticos: burst de 100 logins/seg durante 30 segundos

Endpoint: POST /auth/login (email + password real, no mockeado)
```

**Metricas criticas:**
- CPU de los Droplets durante el burst (threshold: < 80%)
- API p95 de **otros endpoints** durante el burst (threshold: el mismo que el base; si sube, hay contention)
- Login p95 propio (threshold: < 1.5s porque bcrypt es lento por diseño)

**Mitigaciones si falla:**
- Subir Droplets a 8 vCPU/16GB ($84/mes c/u en lugar de $48)
- Bajar cost factor de bcrypt de 12 a 10 (ojo: menos seguro)
- Encolar logins via Redis rate limiting + queue
- Usar Argon2 con menos iteraciones

### 6.2 Test de Failover durante carga (critico para 99.9%)

**Por que existe:** tu arquitectura HA promete "si Droplet-1 cae, CF redirige a Droplet-2 en <30s sin impacto". Esa promesa NO esta validada. Hay que probarla.

**Escenario:**
```
T+0:00   Lanzar test 10K (flujos naturales, fases 1-2)
T+0:30   Minuto 30 del test, con 10K activos en sesiones:
         - `docker stop app` en Droplet-1 (mata Nginx + Laravel + Socket)
         - O en DO panel: "Power off" del Droplet-1
         - Cloudflare deberia detectar en < 30s (health check cada 10s, 2 failures)
         - Todo el trafico pasa a Droplet-2
T+0:32   Monitorear:
         - Cuantos requests fallan durante la transicion? (contador desde el generador)
         - Cuantos sockets se reconectan? (monitorear reconnect events)
         - p95 de API durante la transicion (deberia subir momentaneamente)
         - Usuarios notan algo? (device real + Sentry)
T+0:35   Esperar 3 minutos con Droplet-2 solo
T+0:35   `docker start app` en Droplet-1 (o "Power on")
T+0:36   Verificar que CF vuelve a usar ambos (balance vuelve)
T+0:40   Mismo test con Droplet-2 muerto en vez de Droplet-1
```

**Pass criteria:**
- Durante la transicion, < 0.1% de requests fallan (con retry automatico del mobile, el usuario no lo nota)
- Reconexion de sockets < 5s
- p95 de API durante transicion < 1.5s (vs 800ms base: 2x es aceptable por 30s, mas de 2x no)
- Device real: usuario no percibe el corte

**Si no pasa:** el 99.9% es teoria. Hay que ajustar:
- Bajar health check interval a 5s en lugar de 10s
- Reducir threshold a 1 failure en lugar de 2
- Implementar retry automatico en el app (si 5xx/timeout, reintentar 1 vez con backoff 500ms)
- Verificar que el LB tiene `session affinity: none` para que websockets migren

### 6.3 Test de Break Point (encontrar el limite real)

**Por que existe:** pasar a 10K no es suficiente. Necesitas saber cuanto margen tienes. Si rompe a 11K, dormiras mal. Si rompe a 25K, tienes 2.5x de headroom.

**Escenario:**
```
Ramp up progresivo:
  10K durante 30 min   (baseline pass)
  12K durante 30 min
  15K durante 30 min
  20K durante 30 min
  25K durante 30 min
  30K durante 30 min
  ...hasta que rompa (p95 > 2s sostenido o 5xx > 1%)
```

**Que capturar:**
- Numero de VUs donde p95 rompe 2s
- Numero de VUs donde 5xx supera 1%
- Cual componente cae primero: CPU Droplet? DO Managed MySQL connections? DO Managed Redis ops? Socket adapter?

**Accion:** documentar el break point. Si es < 15K (solo 1.5x de margen), considerar escalar infra antes de produccion.

### 6.4 Test de Red Degradada (latencia real del evento)

**Por que existe:** en el Movistar Arena con 12K personas, WiFi del venue + 4G Movistar/Claro van saturados. El test que no simula esto miente.

**Escenario (usando `tc` en el generador k6 o un profile de k6 Cloud):**
```
Test A: Red buena (baseline)
  150ms RTT, 0% loss, 10 Mbps

Test B: 4G Colombia congestionado
  200ms RTT +- 50ms jitter, 2% loss, 2 Mbps

Test C: WiFi venue saturado
  300ms RTT +- 100ms jitter, 5% loss, 500 Kbps

Correr cada uno con 2,000 VUs durante 30 min (no 10K, para separar el efecto red vs carga)
```

**Pass criteria:**
- Test B: TTI en device real < 5s (vs 3s en red buena). p95 API < 1.5s.
- Test C: TTI < 8s. p95 API < 2.5s. Retry del cliente debe rescatar la mayoria de requests.

**Si no pasa:** agresivo uso de:
- Service worker con cache offline en la app
- Prefetch al login (no esperar a que el usuario navegue)
- Payload optimization (comprimir respuestas, quitar campos innecesarios)
- Skeleton loaders en vez de spinners

### 6.5 Test de Export Aislado (valida VPS-3 worker headless)

**Por que existe:** tu arquitectura promete "exports corren en VPS-3 aislado, no tocan la API principal". Hay que probarlo.

**Escenario:**
```
Parte A — Baseline sin exports
  10K VUs flujo normal durante 30 min
  Capturar: API p95, CPU VPS-1, CPU VPS-2

Parte B — Con exports simultaneos
  10K VUs flujo normal
  + 5 admins disparando exports simultaneos cada 2 minutos (15 exports totales)
  Cada export: CSV de 10K filas con joins pesados
  Capturar: API p95, CPU VPS-1, CPU VPS-2, CPU VPS-3
```

**Pass criteria:**
- API p95 Parte B <= API p95 Parte A * 1.1 (maximo 10% de degradacion)
- CPU Droplet-1 y Droplet-2: sin cambio
- CPU Droplet-3 (worker): alto durante exports (como debe ser)
- Exports completan en < 90s cada uno

**Si no pasa:** el aislamiento no funciona. Revisar:
- Que los jobs de export realmente van a la cola `exports` y no `default`
- Que Droplet-3 realmente lee de la **read replica de DO Managed MySQL** y no del primary
- Que Droplet-1/Droplet-2 no tienen workers de la cola `exports`

---

## 7. Metricas que el usuario SIENTE

Esta seccion es la que v1.0 no tenia. Son las metricas que definen si la app se siente enterprise o banal. Se miden desde **device fisico real**, no desde el generador de carga.

### 7.1 Time To Interactive (TTI)

Desde cold start (app cerrada) hasta que la home es tappable.

Depende de: TLS handshake + DNS + token refresh + 6 endpoints paralelos del home (modules, branding, agenda, speakers, highlights, announcements).

**Target: < 3s en 4G Colombia.**

**Como mejorarlo si no pasa:**
- Agrupar los 6 endpoints en 1 endpoint `GET /events/{id}/bootstrap` que devuelve todo en una respuesta (1 RTT en vez de 6)
- Cache agresivo en Cloudflare Workers/KV de agenda/sponsors/speakers (stale-while-revalidate 60s)
- Prefetch al login: mientras el backend procesa login, el app ya pide el bootstrap

### 7.2 Click-to-response percibido

Tiempo desde que el usuario toca un boton hasta que ve cambio visual.

**Sin optimistic UI** (ej. favoritar sesion, upvote Q&A):
- Target: < 500ms
- Realidad sin optimistic: latencia red + servidor + render = 300-800ms
- Se siente lento si > 300ms

**Con optimistic UI** (que deberia ser todo):
- Target: < 100ms
- Realidad: solo render local, servidor responde despues
- Si falla, rollback con toast

**Criterio dur del test:** toda accion del usuario que modifica estado (favoritar, votar, mandar chat, responder encuesta, dar like) DEBE tener optimistic UI. Si no la tiene, es bug.

### 7.3 Chat latency end-to-end

Desde que usuario A manda `chat:send` hasta que aparece en la pantalla de usuario B.

**Target: < 300ms.**

Componentes:
- A -> Socket server (network): 50-150ms
- Socket server -> Redis pub/sub: 5-20ms
- Redis -> otro Socket server: 5-20ms
- Socket server -> B (network): 50-150ms
- Render en B: 10-50ms

**Como medirlo:** dos devices fisicos en la misma sesion, uno manda mensaje con timestamp, el otro lo recibe. Diferencia = latencia real.

### 7.4 Checkin stampede recovery

En el evento real, 10K personas llegan entre las 7:30 y 9:00 AM. La mayoria hace check-in en los primeros 30 minutos. Pico de 50-100 checkins/seg durante 10 minutos.

**Target:** durante el pico de checkin, el resto de la app (usuarios ya dentro) mantiene p95 < 1s.

**Escenario modificado del attendeeFlow:** comprimir FASE 1 de 30 min a 10 min para 5,000 de los 10K VUs. Los otros 5K ya estan en FASE 2 (sesion en vivo). Simula la ola real.

### 7.5 Metricas de Sentry Performance durante el test

Configurar Sentry en la app mobile para capturar:
- Transaction: app.startup
- Transaction: screen.home
- Transaction: screen.agenda
- Transaction: screen.session
- Transaction: action.checkin
- Transaction: action.vote

Con 2-3 devices reales generando transacciones durante el test de 10K, tienes datos de experiencia real.

---

## 8. Plan de ejecucion

### 8.1 Pre-requisitos

- [ ] Deploy HA completo en staging: 2 Droplets DO sao1 + DO Managed MySQL + DO Managed Redis + CF (DNS+WAF+LB) + R2
- [ ] VPC privada DO configurada para que los Droplets hablen con MySQL/Redis sin pasar por internet publica
- [ ] Sentry configurado (backend + app mobile)
- [ ] DNS + SSL via Cloudflare apuntando a IPs publicas de los Droplets
- [ ] Seeders de datos:
  - 10,000 attendees con tokens pre-generados
  - 50 vendedores con stands asignados
  - 5 admins con permisos
  - 30 sesiones distribuidas en 3 salas
  - 15 sponsors con contenido
  - 200 preguntas pre-seed, 10 polls, 5 rewards
- [ ] Tokens pre-generados en `tests/load/tokens.json` (10,055 tokens)
- [ ] Droplet generador temporal (sao1 con `tc` o region distinta) con k6 + Artillery instalados
- [ ] Profiles `tc` configurados en el generador para red 4G Colombia
- [ ] 2 devices fisicos (iOS + Android) con la app + Sentry
- [ ] Alertas Slack/email configuradas para hard limits

### 8.2 Dia de test (cronograma)

```
HORA    ACTIVIDAD

07:00   Provisionar stack HA en staging DO sao1 (si no estaba ya)
        - 2 Droplets via Terraform o panel
        - DO Managed MySQL + read replica
        - DO Managed Redis HA
        - VPC privada conectando los 5
        - CF DNS + LB apuntando a Droplets
07:30   Ejecutar seeders: 10K users + 30 sessions + data
08:00   Verificar health: GET /api/v1/health en CF, Droplet-1, Droplet-2
08:15   Configurar monitoreo: Sentry, Grafana, tail de logs, DO panel (MySQL/Redis insights)
08:30   Conectar devices reales, instalar build de staging de la app
08:45   Smoke run: 50 VUs durante 5 min, verificar nada roto

09:00   TEST 1 — Warmup
        100 VUs, 15 min, red buena
        Verificar flujo basico funciona contra stack DO

09:20   TEST 2 — Carga media
        1,000 VUs, 30 min
        Verificar thresholds se cumplen a escala chica

09:55   TEST 3 — Login Stampede (aislado)
        10K logins en 10 min + pico 100/s durante 30s
        Verificar CPU Droplets no satura y otros endpoints no se degradan

10:15   TEST 4 — Carga alta
        5,000 VUs, 45 min, flujo natural
        Ajustar si hay warnings

11:05   Pausa de 30 min para analisis intermedio

11:35   TEST 5 — Red degradada
        2,000 VUs con profile 4G Colombia (tc en generador: 200ms + 2% loss + 2 Mbps)
        Verificar thresholds cliente se mantienen

12:10   Almuerzo

13:00   TEST 6 — Carga completa 10K (EL TEST PRINCIPAL)
        10,000 VUs, 2 horas, flujo natural completo
        + 5 admins exportando CSV durante carga maxima
        + Silent disco en 3 sesiones simultaneas
        + Devices reales capturando TTI/click-response
        + Sentry corriendo

15:00   (Dentro de TEST 6) T+2h, justo al terminar:
        TEST 7 — Failover durante carga
        Mantener 10K activos, "Power off" Droplet-1 desde DO panel
        Medir transicion, reconexion, degradacion via CF LB
        3 min con Droplet-2 solo, luego Power on Droplet-1
        Repetir matando Droplet-2

15:30   TEST 8 — Export aislado
        Mantener 2K VUs baseline
        Disparar 15 exports simultaneos
        Verificar API p95 no se degrada > 10%
        Verificar Droplet-3 (worker) lee de read replica, no primary

16:00   TEST 9 — Break Point (si todo lo anterior paso)
        Escalar 10K -> 15K -> 20K -> 25K hasta romper
        Documentar punto de quiebre y componente que cayo primero

17:00   Fin de tests. Capturar reportes de todas las herramientas.

17:30   Analisis
        - Endpoints mas lentos
        - Errores por tipo
        - Bottleneck identificado (CPU Droplet, DO MySQL connections, DO Redis ops, Socket, network)
        - Compare contra thresholds
        - Metricas device real vs backend
        - Break point numerico

18:30   Documentar en STRESS-TEST-RESULTS-v1.md
19:00   Lista priorizada de fixes
19:30   Destruir Droplet generador (los de staging quedan para regression tests)

Dias siguientes: implementar fixes, repetir TEST 6 hasta limpio.
```

### 8.3 Test de regresion post-fixes

Despues de cada ronda de fixes, repetir:
- TEST 6 (10K, 2h, flujo natural)
- TEST 7 (failover)
- TEST 8 (export aislado)

Solo liberar a dry run con cliente cuando todos los hard limits pasen sin warnings.

---

## 9. Scripts a actualizar

### 9.1 Modificaciones en `stress-full.js`

```
1. attendeeFlow — flujo completo natural (5 fases, 210 min)
2. vendorFlow — scan, stats, contacts, export (loop 180 min)
3. adminFlow — MC, moderar, export, ban (loop 180 min)
4. Configurar thresholds nuevos (seccion 4.1)
5. Usar `options.cloud` si se corre desde k6 Cloud
```

### 9.2 Scripts nuevos a crear

| Archivo | Proposito |
|---------|-----------|
| `tests/load/stress-login-stampede.js` | Test aislado 10K logins en 10 min |
| `tests/load/stress-breakpoint.js` | Escalada progresiva hasta romper |
| `tests/load/stress-export-isolated.js` | 10K baseline + 15 exports, compara p95 |
| `scripts/chaos-failover.sh` | Matar VPS-1 o VPS-2 desde linea de comandos |
| `scripts/tc-4g-colombia.sh` | Aplica profile red 4G al generador |
| `scripts/tc-wifi-venue.sh` | Aplica profile red WiFi saturado |
| `scripts/cleanup-tc.sh` | Remueve reglas `tc` |

### 9.3 Endpoints nuevos a incluir

| Endpoint | Escenario | Frecuencia |
|---------|-----------|-----------|
| GET /me/stand/stats | vendorFlow | Cada 10-15 min |
| GET /me/stand/contacts | vendorFlow | Cada 15-20 min |
| GET /sessions/{id}/stats | adminFlow | Cada sesion |
| GET /sessions/{id}/viewers | adminFlow | Cada 5 min durante sesion |
| POST /sessions/{id}/export (queue) | adminFlow | 1 por sesion |
| GET /me/leads/export (CSV) | vendorFlow | 2 veces |
| WebSocket join:session + Redis tracking | attendeeFlow | 2-3 sesiones/user |
| WebSocket leave:session + Redis tracking | attendeeFlow | Al salir |
| GET /events/{id}/bootstrap (si se implementa agrupacion) | attendeeFlow FASE 1 | 1 vez |

### 9.4 Verificaciones especificas durante el test

| Verificacion | Como |
|-------------|------|
| Attendance crece en tiempo real | GET /sessions/{id}/viewers durante sesion activa |
| Export no bloquea API | TEST 8 (seccion 6.5) |
| Gamification puntos correctos | GET /me/points al final = suma de todas las acciones |
| Silent disco cambio de canal | join -> leave -> join otra, verificar audience |
| Notificacion campana aparece | Admin recibe notif DB despues de export queue |
| Rate limits funcionan | 429 responses para users que excedan limites |
| Ban desconecta socket | Admin banea -> usuario pierde conexion < 2s |
| Push notifications llegan | Verificar Expo receipts post-test |
| Retry automatico del mobile | Durante TEST 7, device real NO muestra error |
| Optimistic UI universal | Device real: todas las acciones reflejan < 100ms |

---

## 10. QA de integridad funcional (pre-stress test)

> Antes de lanzar carga, validar que la logica de negocio no tiene bugs de integridad.
> Estos bugs no se detectan con stress test — solo con tests que verifican **consecuencias**, no solo acciones.
> Leccion aprendida: sesion 2026-04-21 encontro 8 bugs criticos/altos revisando Session Lifecycle + MC.

### 10.1 Principio: no revisar modulo por modulo

Revisar CRUD simples (speakers, FAQ, documents) es desperdicio — ahi los bugs son cosmeticos.
Los bugs de integridad viven en modulos con **estado derivado, cascadas, cache multicapa o concurrencia**.

### 10.2 Modulos que requieren revision profunda

| Modulo | Por que | Que verificar |
|--------|---------|---------------|
| **Session Lifecycle** | Cascadas (delay->next, cancel->revert), Carbon mutation, estado compartido | Duracion preservada, sesiones ya iniciadas intocables, revert correcto, .ics con publicEnd |
| **Agenda RT** | Cache 3 capas (MMKV + react-query + socket) | Cambio desde Filament/MC refleja en app < 3s, mi-agenda tambien, reconexion socket resync |
| **Gamification** | Puntos derivados de 13+ acciones, canje, leaderboard | Puntos = suma exacta de acciones, canje descuenta, no doble award, leaderboard ordenado |
| **Networking requests** | Optimistic UI + infinite query + socket RT | Request -> accept -> aparece en contactos, no duplicados, badge correcto, cancel revierte |
| **Registration flow** | 6 modos combinables (approval + access_code + invite_only + domain + depends_on + verify) | Cada combinacion funciona, token no reutilizable, race condition max_uses, replay pre-fill |
| **Check-in / Room** | Kiosk cache vs API source of truth, staff assignment, offline queue | Scan correcto, ban rechazado, offline queue flush parcial, concurrent accept lock |
| **Moderation** | Ban RT, palabras bloqueadas, chat delete, Q&A approve | Ban desconecta socket < 2s, palabras filtradas, delete RT, aprobacion RT en display |

### 10.3 Smoke tests E2E

#### Test 1: Dia de evento completo (backend, PHPUnit/Pest)
```
Crear evento -> 3 salas x 3 sesiones/sala -> 5 attendees, 2 vendedores, 1 admin
1. Admin: delay sesion A1 +15min -> verificar:
   - A1.end_datetime intacto, A1.adjusted_end_at = original+15
   - A2.start movido +15, A2.duracion preservada (60 min)
   - A3 NO movido
   - .ics de A1 usa adjusted_end_at
2. Admin: start sesion A1 -> verificar:
   - actual_start_at = now
   - A2 (ya movida) NO se mueve otra vez
3. Admin: end sesion A1 antes del original -> verificar:
   - adjusted_end_at cleared, A2 revertida
4. Admin: cancel sesion B1 (que tenia delay) -> verificar:
   - B2 revertida, B1.adjusted_end_at cleared
5. Attendee: favoritar A2 -> verificar mi-agenda incluye A2
6. Vendedor: scan attendee -> lead creado -> puntos gamification
7. Admin: ban attendee -> verificar 403 en siguiente request
```

#### Test 2: Cache RT de agenda (app, manual o Detox)
```
1. Abrir app -> ver agenda -> anotar hora de sesion X
2. Desde Filament: cambiar titulo sesion X -> app debe mostrar nuevo titulo < 3s
3. Desde MC: delay sesion X +10min -> app debe mostrar nueva hora < 3s
4. Cambiar a tab Mi Agenda -> verificar que tambien tiene hora nueva
5. Cerrar app, reabrir -> verificar que muestra hora nueva (no la vieja del MMKV)
6. Matar socket server -> Filament cambia titulo -> levantar socket -> app debe sincronizar al reconectar
```

#### Test 3: Gamification integridad de puntos
```
1. Attendee con 0 puntos
2. Check-in (+50) -> verify total = 50
3. Favoritar sesion (+10) -> verify total = 60
4. Scan por vendedor (+20 lead) -> verify total = 80
5. Rating sesion (+15) -> verify total = 95
6. Canjear reward (-50) -> verify total = 45
7. Intentar canjear reward de 50 -> verify rejected (insuficiente)
8. Leaderboard: attendee en posicion correcta
```

#### Test 4: Networking consistency
```
1. A envia request a B -> B tiene 1 pending
2. B acepta -> A y B en contactos mutuos, request desaparece
3. A envia request a C -> C rechaza -> request desaparece, no en contactos
4. Directory: B ya no aparece como "sugerido" para A
5. Badge networking: B acepto -> badge +1 para A
```

---

## 11. Chaos testing

> Post-stress, pre-produccion. Valida que fallas reales no impactan al usuario.

| Escenario | Que hacer | Que debe pasar |
|-----------|-----------|----------------|
| Socket muere 30s | `docker stop socket` en ambos Droplets | App reconecta, refetch agenda/announcements, no pierde estado |
| DO Managed Redis falla 10s | Bloquear con firewall temporal el endpoint Redis | API degrada (no cache), no crash. Socket reconecta pub/sub al volver |
| DO Managed MySQL slow (5s queries) | Simular con `SLEEP(5)` en trigger o `SET GLOBAL max_execution_time` | API devuelve 504 timeout, app muestra error, circuit breaker rompe |
| Cloudflare LB falla a failover | Bajar healthcheck del Droplet-1 manualmente | CF redirige a Droplet-2 en < 30s, usuarios no notan |
| 2 MCs misma sesion | Abrir 2 tabs MC, operar ambas | Estado sincronizado via socket, no conflicto |
| App pierde red 60s | Modo avion, volver | Socket reconecta, agenda fresca, chat historial intacto |
| Double-tap rapido | Tap start 2 veces en MC | Solo 1 request pasa (409 en segunda) |
| DDoS sintetico | 100K req/s desde el generador | Cloudflare absorbe, app no se entera |
| DB connection pool exhausted | Reducir pool a 5 conexiones, cargar 10K | CF LB retorna 503, requests caen, mobile retry rescata |
| DO Managed MySQL primary cae, failover a replica | DO panel: trigger failover manual | DO promueve replica en < 30s, app degrada brevemente |

---

## 12. Calendario pre-produccion

```
Semana -18 (abr):  Deploy HA en staging + smoke tests E2E (Test 1-4)
Semana -16:        Stress test carga (este plan, dia completo)
Semana -14:        Fix ronda 1 + re-test TEST 6
Semana -12:        Fix ronda 2 + chaos testing
Semana -10:        Optimizaciones de latencia percibida (bootstrap endpoint, CDN cache, optimistic UI completo)
Semana -8:         Re-test completo post-optimizaciones
Semana -6:         Dry run 1 con cliente (Eventos Efectivos opera MC, nosotros attendee)
Semana -4:         Fix bugs del dry run + re-test
Semana -3:         Dry run 2 (final)
Semana -2:         Congelar. Solo hotfixes criticos
Semana -1:         Freeze absoluto. Nadie toca main
Dia D (sept 2026): Evento real
```

---

## 13. Criterios de cierre

El plan esta completo cuando:

1. [ ] TEST 6 (10K, 2h) pasa todos los hard limits sin warnings
2. [ ] TEST 7 (failover) el usuario no percibe el corte
3. [ ] TEST 8 (export aislado) confirma que VPS-3 no toca la API
4. [ ] TEST 9 (break point) documenta limite >= 15K (1.5x margen)
5. [ ] Device real reporta TTI < 3s en 4G Colombia
6. [ ] Chaos testing pasa todos los escenarios de seccion 11
7. [ ] Sentry reporta < 0.05% error rate durante 2h de carga
8. [ ] Todos los Smoke tests E2E pasan verdes en CI

**Si uno solo de estos falla, no vamos a dry run con cliente.**

---

## 14. Documentos relacionados

| Doc | Contenido |
|-----|-----------|
| `docs/DISPONIBILIDAD-HA.md` | Arquitectura 99.9% real, failover, multi-region |
| `tests/load/README.md` | Guia de uso de scripts k6/Artillery |
| `tests/load/stress-full.js` | Script 10K VUs (base actual) |
| `tests/load/stress-login-stampede.js` | Test bcrypt stampede |
| `tests/load/stress-breakpoint.js` | Escalada hasta romper |
| `tests/load/stress-export-isolated.js` | Validacion aislamiento VPS-3 |
| `scripts/chaos-failover.sh` | Matar VPS-1/VPS-2 a comando |
| `docs/PENDIENTES.md` | Deploy + SEC-4/5 como prerequisitos |
| `EventOS_Roadmap.md` | Timeline deploy + Bancolombia |

---

_Plan de Stress Test v2.1 — EventOS_
_24 abril 2026_
_Cambios v1.0 -> v2.0: test contra stack HA real, thresholds enterprise, metricas cliente (TTI, click-to-response), 5 tests criticos nuevos (stampede, failover, breakpoint, red degradada, export aislado)._
_Cambios v2.0 -> v2.1: stack consolidado en DigitalOcean sao1 (Sao Paulo) en lugar de Hetzner + servicios dispersos. Latencia inter-servicio < 1ms via VPC privada DO. Latencia usuario Bogota ~80ms vs ~150ms anterior. Costos predictibles flat._
