# EventOS — Alta Disponibilidad e Infraestructura

> Arquitectura para 99.9% uptime (~8.7 horas downtime/año).
> Principio: ningún componente es punto único de falla.
> Versión: 2.0 | Fecha: 2026-04-25
>
> **NOTA v2.0:** Stack migrado de Hetzner+PlanetScale+Upstash a DigitalOcean sao1 consolidado.
> Razon: latencia inter-servicio < 1ms (VPC) vs 80-150ms (servicios remotos), RTT Bogota ~80ms vs ~150ms.
> 99.99% corregido a 99.9% (realista para single-region sin multi-AZ).
> Las secciones 2-3 de este documento aun referencian PlanetScale/Upstash/Hetzner — son la v1.0 historica.
> Para la arquitectura definitiva de produccion ver `docs/PLAN-STRESS-TESTDO.md` v2.1 seccion 3.
> Para el plan de optimistic UI (latencia percibida) ver `docs/OPTIMISTIC-UI-PLAN.md`.

---

## 1. Principio Fundamental

**Nada corre en un solo lugar.** Cada componente tiene réplica o failover automático.

```
ANTES (1 VPS):                      DESPUÉS (HA):
┌──────────┐                        ┌──────────────┐
│  TODO    │  ← si muere,           │  Cloudflare  │ ← absorbe DDoS, balancea
│  en 1    │     todo muere         └──────┬───────┘
│  VPS     │                        ┌──────┴───────┐
└──────────┘                        │   2+ VPS     │ ← si 1 muere, el otro sigue
                                    └──────┬───────┘
                                    ┌──────┴───────┐
                                    │  Managed DB  │ ← réplicas automáticas
                                    │  Managed Redis│ ← failover automático
                                    │  R2 Storage  │ ← distribuido globalmente
                                    └──────────────┘
```

---

## 2. Arquitectura de Producción (99.9%) — DigitalOcean sao1 consolidado

```
                         ┌─────────────────────────┐
                         │       INTERNET           │
                         └────────────┬────────────┘
                                      │
                         ┌────────────┴────────────┐
                         │      CLOUDFLARE          │
                         │  ────────────────────    │
                         │  WAF + DDoS (L3-L7)     │
                         │  TLS 1.2+ termination   │
                         │  Load Balancer (LB)     │
                         │  Health checks cada 10s │
                         │  Failover automático    │
                         │  Cache estáticos (CDN)  │
                         │  Rate limiting global   │
                         │  SLA: 100% (Enterprise) │
                         └──────┬──────────┬───────┘
                                │          │
                    ┌───────────┴──┐  ┌────┴───────────┐
                    │  Droplet-1   │  │  Droplet-2      │
                    │  DO sao1     │  │  DO sao1        │
                    │  4vCPU/8GB   │  │  4vCPU/8GB      │
                    │              │  │                  │
                    │  Nginx       │  │  Nginx          │
                    │  Laravel     │  │  Laravel         │
                    │  PHP-FPM     │  │  PHP-FPM         │
                    │  Socket.IO   │  │  Socket.IO       │
                    │              │  │                  │
                    │  UFW + F2B   │  │  UFW + F2B      │
                    └──────┬───────┘  └──────┬──────────┘
                           │                 │
                    ┌──────┴─────────────────┴──────┐
                    │     VPC privada DO sao1       │
                    │     (< 1ms RTT entre todos)   │
                    └──────┬─────────────────┬──────┘
                           │                 │
              ┌────────────┴───────┐  ┌──────┴────────────┐
              │  DO Managed MySQL  │  │  DO Managed Redis  │
              │  sao1 (1GB RAM)    │  │  sao1 (1GB, HA)    │
              │  ──────────────    │  │  ──────────────    │
              │  Read replica      │  │  Failover auto     │
              │  Backups diarios   │  │  Persistence       │
              │  Failover auto     │  │  Sin TLS overhead  │
              │  VPC < 1ms RTT     │  │  VPC < 1ms RTT     │
              └────────────────────┘  └────────────────────┘
                           │
              ┌────────────┴───────┐
              │  Cloudflare R2     │
              │  (Object Storage)  │
              │  ──────────────    │
              │  CDN global        │
              │  S3-compatible     │
              │  Egress gratis     │
              │  SLA: 99.99%       │
              └────────────────────┘

              ┌────────────────────┐
              │  Droplet-3 (opt.)  │
              │  DO sao1 worker    │
              │  ──────────────    │
              │  queue:work exports│
              │  Lee read replica  │
              │  No sirve HTTP     │
              └────────────────────┘
```

### Por qué DO sao1 consolidado (no Hetzner + PlanetScale + Upstash)

| Decision | Razon |
|----------|-------|
| **DO en lugar de Hetzner** | Audiencia 100% Latam. Hetzner no tiene region Latam. RTT Bogota: ~80ms vs ~150ms |
| **DO sao1 (Sao Paulo)** | Region mas cercana a Colombia con Managed MySQL + Redis + VPC |
| **DO Managed MySQL** | VPC privada < 1ms vs PlanetScale remoto 80-150ms. Pricing flat, no se dispara en bursts |
| **DO Managed Redis** | VPC privada, sin TLS overhead por cada comando, pricing flat |
| **R2 se mantiene** | Egress gratis. DO Spaces no tiene region sao1 |
| **Cloudflare adelante** | WAF + DDoS + CDN que DO LB no tiene |

### ¿Qué pasa cuando algo falla?

| Componente | Qué pasa | Tiempo de recuperación | Impacto para el usuario |
|------------|----------|----------------------|------------------------|
| Droplet-1 se cae | Cloudflare LB redirige todo a Droplet-2 | **<30 segundos** | Ninguno (con retry automatico en app, invisible) |
| Droplet-2 se cae | Cloudflare LB redirige todo a Droplet-1 | **<30 segundos** | Ninguno |
| Droplet-3 se cae | Export jobs se acumulan en Redis, se procesan al volver | **auto-recovery** | Exports tardan mas, evento intacto |
| DO MySQL se cae | DO failover a read replica promovida | **<60 segundos** | Breve degradacion, app retry rescata |
| DO Redis se cae | DO failover a replica HA | **<30 segundos** | Reconexion de socket (automatica) |
| R2 se cae | Cloudflare distribuido, 99.99% SLA | **~0 segundos** | Ninguno (CDN cache) |
| DDoS ataque | Cloudflare absorbe (Tbps de capacidad) | **0 segundos** | Ninguno |
| Deploy sale mal | Blue-green: Droplet-2 sigue con version anterior | **0 segundos** | Ninguno |
| Cert SSL expira | Cloudflare renueva automaticamente | **0 segundos** | Ninguno |
| Disco Droplet lleno | Solo logs/temp — DB y storage no estan en Droplet | **N/A** | Ninguno |
| Red degradada (4G venue) | App tiene retry automatico (2x, backoff+jitter) | **N/A** | Retry rescata ~90% de errores transitorios |

---

## 3. Servicios Managed — Selección y Justificación

### 3.1 Base de Datos: DigitalOcean Managed MySQL (sao1)

| Aspecto | Detalle |
|---------|---------|
| **Qué es** | MySQL managed en la misma VPC que los Droplets |
| **Por qué** | RTT < 1ms via VPC privada (vs 80-150ms PlanetScale remoto). Pricing flat, no se dispara en bursts de evento |
| **Plan** | 1GB RAM ($15/mes) + read replica ($15/mes) = $30/mes |
| **SLA** | 99.99% uptime |
| **Backups** | Automaticos diarios, retencion 7 dias |
| **Failover** | Automatico a read replica promovida (<60s) |
| **Region** | sao1 (Sao Paulo) — misma VPC que Droplets |

**Cambio en EventOS:**
```env
# .env.production
DB_CONNECTION=mysql
DB_HOST=private-db-eventos-do-sao1.ondigitalocean.com   # VPC privada
DB_PORT=25060
DB_DATABASE=eventos_prod
DB_USERNAME=doadmin
DB_PASSWORD=xxxxx
```

**Por que NO PlanetScale:** PlanetScale no tiene region Latam. RTT desde sao1 a us-east = 80-150ms. Un endpoint con 5 queries suma 400-750ms de piso solo en latencia de red. Con DO Managed MySQL en VPC, el mismo endpoint tarda < 5ms en latencia de red.

### 3.2 Redis: DigitalOcean Managed Redis (sao1)

| Aspecto | Detalle |
|---------|---------|
| **Qué es** | Redis managed en la misma VPC |
| **Por qué** | VPC privada < 1ms RTT. Sin TLS overhead por cada comando (vs Upstash). Pricing flat |
| **Plan** | 1GB RAM con HA ($15/mes) |
| **SLA** | 99.99% uptime |
| **Persistence** | Si — AOF enabled |
| **Failover** | Automatico con HA |
| **Region** | sao1 — misma VPC |

**Cambio en EventOS:**
```env
# .env.production — Backend
REDIS_HOST=private-redis-eventos-do-sao1.ondigitalocean.com  # VPC privada
REDIS_PORT=25061
REDIS_PASSWORD=xxxxx

# .env.production — Socket server
REDIS_HOST=private-redis-eventos-do-sao1.ondigitalocean.com
REDIS_PORT=25061
REDIS_PASSWORD=xxxxx
REDIS_DB=2
```

**Por que NO Upstash:** Upstash cobra por comando y agrega TLS overhead en cada operacion. Con 10K usuarios y Socket.IO Redis adapter haciendo pub/sub constante, el costo es impredecible y la latencia se suma. DO Managed Redis en VPC: 0 overhead, pricing flat $15/mes.

### 3.3 Storage: Cloudflare R2 (ya implementado)

| Aspecto | Detalle |
|---------|---------|
| **Qué es** | Object storage S3-compatible con CDN global |
| **Por qué** | 0 egress fees, distribuido globalmente, ya integrado |
| **Plan** | $0.015/GB almacenado, 0 costo por descargas |
| **SLA** | 99.99% |

### 3.4 Load Balancer: Cloudflare

| Aspecto | Detalle |
|---------|---------|
| **Qué es** | Cloudflare Load Balancing |
| **Cómo funciona** | Health checks a cada Droplet cada 10s. Si uno no responde (2 failures), redirige al otro |
| **Plan** | $5/mes por LB + $0.50/mes por origin server |
| **Health check** | `GET /api/v1/health` — si no retorna 200 en 5s, se marca como down |
| **Failover** | Automatico. Si Droplet-1 falla, 100% trafico va a Droplet-2 |
| **Session affinity** | No necesaria — APIs stateless, sessions en DO Managed Redis |

**Configuracion Cloudflare LB:**
```
Pool: eventos-backend
├── Origin 1: droplet1.eventos.com (primary, weight: 1)
├── Origin 2: droplet2.eventos.com (secondary, weight: 1)
└── Health check: GET /api/v1/health, interval: 10s, threshold: 2 failures

Steering: Round robin
Fallback pool: droplet2 only
```

### 3.5 Socket.IO con multiples nodos

Ya implementado: **Redis adapter** (`@socket.io/redis-adapter`). Socket.IO en Droplet-1 y Droplet-2 comparten estado via DO Managed Redis en VPC.

```
Cliente A (conectado a Droplet-1) envia chat:send
    → Droplet-1 publica en Redis (VPC < 1ms)
    → Droplet-2 recibe via Redis pub/sub (VPC < 1ms)
    → Cliente B (conectado a Droplet-2) recibe el mensaje
```

**Sticky sessions para WebSocket:** Cloudflare maneja esto automaticamente con `websockets: true` en la zona.

---

## 4. Deploy Strategy: Blue-Green

Con 2 Droplets, el deploy es zero-downtime:

```
Estado normal:
    Cloudflare LB → Droplet-1 (v1.0) + Droplet-2 (v1.0)

Paso 1: Sacar Droplet-2 del LB
    Cloudflare LB → Droplet-1 (v1.0)     Droplet-2 (offline)

Paso 2: Deploy v1.1 en Droplet-2
    Cloudflare LB → Droplet-1 (v1.0)     Droplet-2 (v1.1, testing)

Paso 3: Verificar Droplet-2 funciona (smoke tests)
    curl https://droplet2.eventos.com/api/v1/health → 200 OK

Paso 4: Meter Droplet-2 al LB, sacar Droplet-1
    Cloudflare LB → Droplet-2 (v1.1)     Droplet-1 (v1.0, actualizando)

Paso 5: Deploy v1.1 en Droplet-1
    Cloudflare LB → Droplet-1 (v1.1) + Droplet-2 (v1.1)

Resultado: 0 downtime. Si v1.1 tiene bug → rollback = meter Droplet-1 (v1.0) de vuelta.
```

---

## 5. Monitoreo y Alertas

### 5.1 Health Checks

| Endpoint | Qué verifica | Frecuencia |
|----------|-------------|-----------|
| `GET /api/v1/health` | Laravel + DB + Redis + Queue | Cloudflare: 10s |
| `GET /health` (socket) | Socket.IO responde + Redis conectado | BetterStack: 60s |
| DO MySQL panel | DB healthy, replicas sincronizadas, slow queries | DO: automatico |
| DO Redis panel | Redis healthy, ops/sec, memoria | DO: automatico |

### 5.2 Alertas

| Evento | Canal | Tiempo respuesta |
|--------|-------|-----------------|
| Droplet down (health check fail) | Email + Push + Slack | Cloudflare failover en <30s |
| DB latencia > 500ms | Email | DO Insights alerta automatica |
| Redis memory > 80% | Email | DO alerta automatica |
| Error rate > 5% (Sentry) | Email + Push | Investigar en <15 min |
| SSL cert proximo a expirar | Email | Cloudflare renueva auto, alerta 30d antes |

### 5.3 Herramientas de monitoreo

| Herramienta | Proposito | Costo |
|-------------|----------|-------|
| **Cloudflare Analytics** | Trafico, cache hit rate, threats blocked | Incluido |
| **BetterStack** (o UptimeRobot) | Uptime monitoring + incident pages | Free tier o $10/mes |
| **Sentry** | Error tracking backend + app | Free tier (5K events/mes) o $26/mes |
| **DO MySQL Insights** | Query performance, slow queries, connections | Incluido |
| **DO Redis Metrics** | Ops/sec, memoria, latencia | Incluido |

---

## 6. Backups y Recuperación

### 6.1 Estrategia de backups

| Dato | Servicio | Frecuencia | Retencion | Automatico |
|------|---------|-----------|-----------|-----------|
| Base de datos | DO Managed MySQL | Diarios | 7 dias | Si |
| Redis state | DO Managed Redis | Persistencia continua (AOF) | Automatico | Si |
| Storage (imagenes) | Cloudflare R2 | Distribuido (inherente) | Ilimitado | Si |
| Codigo fuente | GitHub | Cada push | Ilimitado | Si |
| Config/secrets | Backup manual encrypted | Semanal | Indefinido | Manual |

### 6.2 RPO y RTO

| Metrica | Valor | Significado |
|---------|-------|-------------|
| **RPO** (Recovery Point Objective) | **< 24 horas** | Maximo pierdes 1 dia de datos (backup interval DO MySQL) |
| **RTO** (Recovery Time Objective) | **< 5 minutos** | Todo se recupera solo (managed services + failover) |

### 6.3 Disaster Recovery

| Escenario | Recuperacion |
|-----------|-------------|
| Droplet destruido | Levantar nuevo Droplet en sao1, `docker compose up`. DB y Redis intactos (managed en VPC). **Tiempo: 15-30 min** |
| DB corrupta | DO restore desde backup diario. **Tiempo: <10 min** |
| Cuenta cloud comprometida | Rotar secrets, revocar tokens, restore desde backup. **Tiempo: 1-2 horas** |
| Region sao1 cae | Plan de contingencia seccion 6.4. **Tiempo: 30-60 min** |

### 6.4 Plan de contingencia — Caida regional DO sao1

> Probabilidad: ~0.01% durante un evento de 4-8 horas.
> Este plan es cold standby: cero costo mensual, preparacion previa unica, recovery manual.
> NO es multi-region activo (complejidad y costo injustificados para la escala actual).

#### Estrategia: Cold Standby + Backup externo a DO

```
ESTADO NORMAL (99.99% del tiempo):
  Cloudflare LB → DO sao1 (Droplets + MySQL + Redis en VPC)

EMERGENCIA (DO sao1 caido):
  1. Cloudflare DNS → apuntar a Droplets de emergencia en DO nyc3 (o Vultr/Hetzner)
  2. Restore MySQL desde backup en R2 (fuera de DO)
  3. Redis se reconstruye solo (cache + pub/sub, no datos criticos)
  4. App funciona en 30-60 min con ~150ms RTT (vs ~80ms normal)
```

#### Preparacion previa (hacer UNA VEZ antes del primer evento)

**1. Snapshots semanales de Droplets**

Los snapshots de DO capturan la imagen completa (Docker, config, codigo).
Se pueden restaurar en cualquier region DO.

```bash
# Automatizar con DO API (cron semanal o pre-evento)
doctl compute droplet-action snapshot <droplet-1-id> --snapshot-name "eventos-d1-$(date +%Y%m%d)"
doctl compute droplet-action snapshot <droplet-2-id> --snapshot-name "eventos-d2-$(date +%Y%m%d)"
```

**2. Backup MySQL externo a DO (en Cloudflare R2)**

DO Managed MySQL hace backups diarios, pero si toda la region cae, esos backups
podrian ser inaccesibles. Un dump en R2 (fuera de DO) garantiza acceso independiente.

```bash
# Cron semanal en Droplet-1 (o manual la noche antes del evento)
mysqldump -h private-db-eventos.ondigitalocean.com \
  -u doadmin -p eventos_prod \
  --single-transaction --routines --triggers \
  | gzip > /tmp/backup-$(date +%Y%m%d).sql.gz

# Subir a R2 (fuera de DO, accesible siempre)
aws s3 cp /tmp/backup-$(date +%Y%m%d).sql.gz \
  s3://eventos-backups/ \
  --endpoint-url https://ACCOUNT_ID.r2.cloudflarestorage.com

# Limpiar local
rm /tmp/backup-*.sql.gz
```

**3. Documentar .env de emergencia**

Tener preparado un `.env.emergency` con las variables para region alternativa
(hosts DB/Redis cambian, el resto es igual). Guardarlo encrypted en R2 o GitHub Secrets.

#### Runbook de emergencia — DO sao1 caido durante evento

```
PASO 0 — VERIFICAR (2 minutos)
  [ ] Confirmar que es DO, no Cloudflare ni red del venue
  [ ] dash.cloudflare.com → funciona? Si no, es Cloudflare (diferente problema)
  [ ] status.digitalocean.com → sao1 reportado down?
  [ ] Si solo es 1 Droplet → CF LB ya hizo failover, no hacer nada
  [ ] Si es toda la region sao1 → continuar con paso 1

PASO 1 — LEVANTAR INFRA ALTERNATIVA (15-20 minutos)
  [ ] DO panel → crear 2 Droplets en nyc3 desde snapshots mas recientes
      (o Vultr/Hetzner si DO completo esta caido)
  [ ] Instalar MySQL en el Droplet (o DO Managed MySQL en nyc3 si disponible)
  [ ] Descargar backup de R2:
      aws s3 cp s3://eventos-backups/backup-YYYYMMDD.sql.gz /tmp/ \
        --endpoint-url https://ACCOUNT_ID.r2.cloudflarestorage.com
  [ ] Restore:
      gunzip -c /tmp/backup-*.sql.gz | mysql -u root eventos_prod
  [ ] Instalar Redis (apt install redis-server o DO Managed en nyc3)

PASO 2 — CONFIGURAR Y ARRANCAR (10 minutos)
  [ ] Copiar .env.emergency a ambos Droplets
  [ ] Actualizar DB_HOST, REDIS_HOST con nuevas IPs
  [ ] docker compose up -d en ambos
  [ ] Verificar: curl http://NUEVA_IP/api/v1/health → 200

PASO 3 — REDIRIGIR TRAFICO (5 minutos)
  [ ] Cloudflare → Load Balancer → cambiar origins a nuevas IPs
  [ ] Cloudflare → purge cache
  [ ] Verificar: curl https://api.eventos.com/api/v1/health → 200
  [ ] Verificar: WebSocket conecta al nuevo socket server

PASO 4 — COMUNICAR (inmediato)
  [ ] Notificar al cliente: "hubo una interrupcion de 30 min, ya estamos operativos"
  [ ] Los usuarios solo vieron ~30 min de downtime
  [ ] Con retry automatico en la app, muchos ni lo notaron
```

#### Que se pierde en la evacuacion

| Dato | Estado |
|------|--------|
| Registros hasta ultimo backup R2 | Intactos |
| Datos entre backup y la caida (hasta 24h) | **Perdidos** |
| Check-ins del dia | Perdidos si no estaban en el backup |
| Chat history (Redis) | Perdido (efimero, se reconstruye) |
| Leads escaneados post-backup | Perdidos |
| Imagenes/fotos | Intactas (R2 es independiente de DO) |
| Gamification points post-backup | Perdidos |

**Mitigacion para evento critico:** La noche antes del evento, correr el backup manual a R2. RPO baja de 7 dias a < 12 horas.

#### Que NO hacer durante la emergencia

- **No esperar a que DO se recupere** — puede tardar horas. Mejor evacuar en 30 min
- **No intentar acceder a DO Managed MySQL** — si la region cayo, los managed services tambien
- **No rutear a un servidor casero** — internet residencial no soporta la carga
- **No cambiar DNS directamente** — usar Cloudflare LB (cambiar origins), el DNS no cambia

#### Protocolo dia del evento

```
DIA D - 2 HORAS ANTES:
  [ ] Verificar DO status page: todo verde en sao1
  [ ] Verificar CF health checks: ambos Droplets UP
  [ ] Verificar snapshot reciente (< 7 dias)
  [ ] Verificar backup MySQL en R2 (< 24h, hacer manual si > 24h)
  [ ] Tener runbook impreso o en laptop (no depender de acceso a GitHub)
  [ ] Tener hotspot 4G propio (independiente del WiFi del venue)

DURANTE:
  [ ] Monitorear alertas BetterStack
  [ ] Si alerta → paso 0 del runbook
```

#### Costo del cold standby

| Item | Costo mensual |
|------|---------------|
| Snapshots DO | Gratis (incluido en plan) |
| Backup MySQL en R2 | ~$0.01 (< 1GB comprimido) |
| Droplets de emergencia | $0 (solo se crean si se necesitan, se cobran por hora) |
| **Total** | **~$0/mes** |

Si se activa la emergencia, los Droplets temporales cuestan ~$0.07/hora cada uno.
Un evento de 8 horas con 2 Droplets de emergencia = ~$1.12 total.

---

## 7. Costos Estimados (99.9% uptime) — DO sao1 consolidado

### Escenario produccion: 10,000 usuarios concurrentes

| Servicio | Plan | Costo/mes |
|----------|------|-----------|
| Droplet-1 DO sao1 | 4 vCPU, 8GB RAM | $48 |
| Droplet-2 DO sao1 | 4 vCPU, 8GB RAM | $48 |
| DO Managed MySQL sao1 | 1GB RAM + read replica | $30 |
| DO Managed Redis sao1 | 1GB RAM, HA | $15 |
| Cloudflare Pro | WAF + analytics | $20 |
| Cloudflare LB | Load balancing 2 origins | $6 |
| Cloudflare R2 | ~10GB storage | ~$1 |
| BetterStack | Monitoring basico | Free |
| Sentry | Error tracking | Free (5K events) |
| **TOTAL** | | **~$168/mes** |

### Escalar a 10+ eventos o mas capacidad

| Cambio | Costo adicional | Proposito |
|--------|----------------|-----------|
| Droplet-3 DO sao1 (4vCPU/8GB) | +$48/mes | Worker headless para export jobs (Data Center) |
| Sentry Team | +$26/mes | Mas events |
| Droplets a 8vCPU/16GB | +$36/mes c/u | Si bcrypt stampede satura CPU |
| **TOTAL escalado** | **~$278/mes** |

**Droplet-3 es un worker headless:** Solo corre `php artisan queue:work --queue=exports`. No tiene Nginx, no sirve HTTP. Lee de la read replica MySQL, sube archivos a R2. Si se cae, el evento sigue perfecto — los export jobs se acumulan en Redis hasta que Droplet-3 vuelva.

### Comparacion con stack anterior (Hetzner + PlanetScale + Upstash)

| | Stack anterior | DO sao1 consolidado |
|---|---|---|
| Costo | ~$75/mes | ~$168/mes |
| Latencia inter-servicio | 80-150ms (remoto) | < 1ms (VPC) |
| RTT Bogota | ~150ms (us-east) | ~80ms (sao1) |
| Pricing | Variable (PlanetScale bursts) | Flat/predecible |
| Consola | 4 dashboards separados | 1 panel DO + Cloudflare |
| Recuperacion | Si | Si |
| Escalar | Resize Droplet en DO panel | Agregar Droplet al LB |
| Backups DB | Manual | Automatico (DO Managed) |
| Si te hackean el Droplet | Pierdes todo | DB y storage intactos (managed en VPC) |

**$168/mes por 99.9% uptime.** Se lo cobras al cliente como parte del servicio.

---

## 7.5 Real-Time Data Invalidation Architecture

> Implementado 2026-04-09. Elimina polling, sincroniza Admin→App en 1-3 segundos, optimizado para 10,000+ usuarios.

### Principio

**Cero requests cuando nadie cambia nada.** Solo se refetchea cuando hay un cambio real, notificado via socket.

### Flujo completo

```
Admin edita sesion en Filament
  │
  ├─ Model::save() → Eloquent Observer
  │    ├─ Cache::forget('event:1:agenda')         ← Warm cache (dato fresco listo)
  │    └─ InvalidationService::broadcast(1, 'agenda')
  │         ├─ Redis throttle check (1s TTL)      ← Anti-storm: max 1 evento/seg/entidad
  │         └─ HTTP POST → Socket Server /internal/data/invalidate
  │              └─ io.to('event:1').emit('data:invalidate', { entity: 'agenda' })
  │                   └─ 10,000 clientes reciben
  │                        └─ setTimeout(Math.random() * 2000)  ← Jitter anti-thundering herd
  │                             └─ queryClient.invalidateQueries(['agenda', eventId])
  │                                  └─ React Query refetch (deduplicado)
  │                                       └─ API responde desde DB (cache ya limpio) ~5ms
  │                                            └─ UI actualizada
```

### Capas de defensa (redundancia sin acoplamiento)

| Capa | Mecanismo | Que cubre | Si falla |
|------|-----------|-----------|----------|
| 1 | **Socket invalidation** | Admin cambia dato → app se entera en 1-3s | Capa 2 cubre |
| 2 | **focusManager (AppState)** | App vuelve de background → refetch stale | Independiente del socket |
| 3 | **Reconnect sync** | Socket reconecta tras micro-corte → invalida criticos | Complementa capa 1 |
| 4 | **staleTime** | Queries expiran naturalmente (30s-5min) | Red de seguridad final |

Cada capa opera independientemente. Si todas fallan, pull-to-refresh manual sigue funcionando.

> **Nota critica sobre staleTime:** El focusManager solo refetchea queries que React Query considera *stale*. Si `staleTime: Infinity`, nunca refetchea. Los valores actuales (30s anuncios, 5min agenda/sponsors) son el balance ideal: no generan polling pero aseguran que al volver de background (>30s) los datos se consideren expirados y el focusManager los refresque. Nunca usar `staleTime: Infinity` en queries que necesiten actualizarse.

### Entidades con invalidacion real-time

| Entidad | Observer | Query Key | Cache Key Redis |
|---------|----------|-----------|-----------------|
| Agenda (sesiones) | `EventSessionObserver` | `['agenda', eventId]` | `event:{id}:agenda` |
| Anuncios | `AnnouncementObserver` | `['announcements', eventId]` | — |
| Sponsors | `SponsorObserver` | `['sponsors', eventId]` | — |
| Speakers | `SpeakerObserver` | `['speakers', eventId]` | — |
| Highlights | `HighlightObserver` | `['highlights', eventId]` | — |

### Proteccion anti-colapso (10k+ usuarios)

| Mecanismo | Donde | Que previene |
|-----------|-------|-------------|
| **Jitter 0-2s** | App (cliente) | 10k requests en 2s, no en 1ms |
| **Throttle 1s** | Backend (Redis TTL) | Admin edita 5 cosas → 1 evento socket |
| **React Query dedup** | App (cliente) | 3 eventos seguidos → 1 solo refetch |
| **Redis cache API** | Backend | Cada request servido en ~5ms |
| **Warm cache** | Backend (Observer) | Cache::forget ANTES de broadcast → refetch trae datos frescos |
| **hasConnectedOnce flag** | App (cliente) | Primera conexion no invalida innecesariamente |

### Rendimiento: Antes vs Ahora

| Metrica | Antes (staleTime) | Ahora (socket + focusManager) |
|---------|-------------------|-------------------------------|
| Requests con datos sin cambios | 1 cada 30s-5min/query/usuario | **0** |
| 10k usuarios, 5 queries, 1 hora | ~600,000 requests desperdiciados | **0** |
| Latencia de actualizacion | 30s a 5 min | **1-3 segundos** |
| Volver de background | Datos viejos | **Refetch inmediato** |
| Carga servidor en reposo | Alta (requests constantes) | **Cero** |
| Conexiones socket extra | 0 | **0** (reutiliza la del chat) |

### Archivos clave

| Archivo | Repo | Proposito |
|---------|------|-----------|
| `app/Services/InvalidationService.php` | Backend | Throttle + HTTP POST al socket |
| `app/Observers/*Observer.php` | Backend | Cache::forget + broadcast por entidad |
| `src/index.ts` (endpoint `/internal/data/invalidate`) | Socket | Relay evento a room |
| `hooks/useDataInvalidation.ts` | App | focusManager + socket listener + jitter |
| `app/_layout.tsx` (DataInvalidationProvider) | App | Monta el hook global |

### Notas de produccion

- El endpoint `/internal/data/invalidate` requiere header `X-Internal-Secret` (mismo patron que los demas endpoints internos)
- El socket server usa Redis adapter — funciona con multiples instancias (VPS-1 y VPS-2)
- El Observer usa `saved()` (cubre create + update). `::where()->update()` (query builder) NO dispara observers — usar `$model->save()` siempre
- El throttle usa `Cache::store('redis')->put($key, true, 1)` — TTL 1 segundo. TTL < 1s se redondea a 0 en Redis

---

## 8. Configuracion Droplet (cada uno identico)

### 8.1 Docker Compose (producción)

```yaml
# docker-compose.prod.yml — CADA Droplet tiene esto
version: '3.8'

services:
  nginx:
    image: nginx:1.27-alpine
    ports: ["80:80", "443:443"]
    volumes:
      - ./nginx/conf.d:/etc/nginx/conf.d:ro
      - ./nginx/ssl:/etc/nginx/ssl:ro
    depends_on:
      app:
        condition: service_healthy
    deploy:
      resources:
        limits: { cpus: '0.5', memory: 256M }
    restart: unless-stopped

  app:
    build:
      context: ./eventos-backend
      dockerfile: Dockerfile
    user: "1000:1000"
    environment:
      - APP_ENV=production
    env_file: .env.production
    healthcheck:
      test: ["CMD", "php", "artisan", "health:check"]
      interval: 30s
      timeout: 5s
      retries: 3
    deploy:
      resources:
        limits: { cpus: '1.5', memory: 1536M }
    restart: unless-stopped

  socket:
    build:
      context: ./eventos-socket
      dockerfile: Dockerfile
    user: "1000:1000"
    env_file: .env.socket.production
    ports: ["3001:3001"]
    healthcheck:
      test: ["CMD", "wget", "-qO-", "http://localhost:3001/health"]
      interval: 30s
      timeout: 5s
      retries: 3
    deploy:
      resources:
        limits: { cpus: '0.5', memory: 512M }
    restart: unless-stopped

# NO hay MySQL ni Redis — son DO Managed services en VPC privada
```

### 8.2 Lo que NO corre en el Droplet

| Servicio | Donde corre | Por que |
|----------|-------------|---------|
| MySQL | DO Managed MySQL sao1 (VPC privada) | Replicas, backups, failover automatico, < 1ms RTT |
| Redis | DO Managed Redis sao1 (VPC privada) | Persistencia AOF, failover HA, < 1ms RTT |
| Storage | Cloudflare R2 (CDN global) | Distribuido globalmente, 0 egress |
| DNS + LB | Cloudflare | DDoS protection, WAF, health checks |

### 8.3 Hardening de cada Droplet

```bash
# 1. Firewall
ufw default deny incoming
ufw allow 22/tcp    # SSH
ufw allow 80/tcp    # HTTP (redirect to HTTPS)
ufw allow 443/tcp   # HTTPS
ufw allow 3001/tcp  # Socket.IO (si Cloudflare proxea directo)
ufw enable

# 2. SSH
sed -i 's/PasswordAuthentication yes/PasswordAuthentication no/' /etc/ssh/sshd_config
systemctl restart sshd

# 3. Fail2ban
apt install fail2ban -y
# Config: 5 intentos SSH → ban 1 hora

# 4. Auto-updates
apt install unattended-upgrades -y
dpkg-reconfigure -plow unattended-upgrades

# 5. Non-root user para Docker
adduser deploy
usermod -aG docker deploy
```

---

## 9. Checklist de Disponibilidad

```
INFRAESTRUCTURA DO sao1
✅ Arquitectura documentada (este documento + PLAN-STRESS-TESTDO.md)
🔲 Droplet-1 provisionado (DO sao1, 4vCPU/8GB)
🔲 Droplet-2 provisionado (DO sao1, 4vCPU/8GB)
🔲 VPC privada DO sao1 configurada
🔲 Docker Compose prod en ambos Droplets
🔲 UFW + SSH key + Fail2ban en ambos
🔲 Non-root user en ambos

MANAGED SERVICES DO
🔲 DO Managed MySQL sao1: database + credentials + read replica
🔲 DO Managed Redis sao1: instance HA + credentials
🔲 VPC conectando Droplets + MySQL + Redis (< 1ms RTT verificado)
🔲 Cloudflare R2: bucket configurado (ya existe en dev)

CLOUDFLARE
🔲 Dominio configurado
🔲 SSL Full (Strict)
🔲 Load Balancer configurado (2 origins Droplets)
🔲 Health checks activos (GET /api/v1/health cada 10s, threshold 2 failures)
🔲 Failover pool configurado
🔲 WAF OWASP rules habilitadas
🔲 Bot Fight Mode ON
🔲 Rate limiting rules (login, register)
🔲 WebSocket support habilitado

MONITORING
🔲 BetterStack/UptimeRobot configurado
🔲 Sentry backend + app
🔲 Alertas de downtime (email + push)

DEPLOY
🔲 GitHub Actions CI/CD configurado
🔲 Blue-green deploy script
🔲 Smoke test post-deploy automatizado
🔲 Rollback procedure documentado

BACKUPS
🔲 DO MySQL backups verificados
🔲 Secrets backup encrypted (offline)
🔲 Test de restore ejecutado

OPTIMISTIC UI (pre-stress test)
✅ Haptic feedback (7 hooks, 9 puntos)
✅ Retry automatico API (network + 502/503/504)
✅ Bug fix Q&A blocked words (201 fake → 422)
🔲 Chat tempId + ack + estados progresivos
🔲 Emoji skip-self
🔲 Dedup wall:comment
🔲 Q&A upvote anti-parpadeo
```

---

## 10. SLA que podemos ofrecer al cliente

Con esta arquitectura (DO sao1 consolidado):

| Metrica | Garantia | Como se logra |
|---------|----------|--------------|
| **Uptime** | 99.9% (~8.7h downtime/ano) | 2 Droplets DO sao1 + DO Managed MySQL/Redis + Cloudflare LB |
| **RPO** | < 24 horas | DO MySQL backups diarios automaticos |
| **RTO** | < 5 minutos | Failover automatico en todos los niveles |
| **Latencia API** | < 200ms p95 | VPC < 1ms + Redis cache + CDN cache |
| **Latencia percibida** | < 100ms | Optimistic UI en 10/30 acciones + haptic feedback |
| **Latencia WebSocket** | < 100ms | Redis adapter VPC + sao1 ~80ms Bogota |
| **DDoS protection** | Incluida | Cloudflare (absorbe ataques de Tbps) |
| **Cifrado** | En transito y reposo | TLS 1.2+ (Cloudflare) + AES-256 (Laravel) |
| **Backups** | Automaticos, 7 dias | DO MySQL + R2 |
| **Retry automatico** | 2x con backoff | Network errors + 502/503/504 invisibles al usuario |

---

## 11. Live Moments — Fixes Pre-Produccion (10K usuarios)

> Auditoria realizada 2026-04-23. La infraestructura HA cubre Live Moments sin cambios.
> Estos 4 fixes son optimizaciones **app-level** requeridas antes del stress test.

### Estado actual sin fixes

| Escenario | Problema | Impacto |
|-----------|----------|---------|
| 10K responden trivia en 10s | 10,000 HTTP POSTs de broadcast `game:answer-count` | Cola HTTP saturada, contador 200-500ms atrasado |
| closeRound() con 10K participantes | `SUM(score) GROUP BY` sin indice, full scan 50K rows | Query 500ms-2s, riesgo timeout |
| MC con 30 sesiones visibles | `getEligiblePool()` hace HTTP al socket server en cada poll (cada 5s) | 30+ HTTP calls/5s, MC laggy |
| Broadcasts concurrentes | Pool HTTP default 6 conexiones | Backlog, timeouts despues de 2-5s |

### FIX-1: Throttle game:answer-count (CRITICO)

**Problema:** Cada `POST /games/{id}/answer` dispara un broadcast con el conteo actual. Con 10K usuarios respondiendo en 10-30 segundos, son 10K HTTP POSTs redundantes al socket server (muchos con el mismo numero).

**Solucion:** Throttle con Redis — maximo 1 broadcast por segundo.

```php
// GameController::answer() — despues de crear LiveGameParticipant
$throttleKey = "game:answer-count:{$game->id}:{$round}";
if (!Cache::has($throttleKey)) {
    $count = LiveGameParticipant::where('game_id', $game->id)
        ->where('round', $round)->count();
    GameService::broadcast($game, 'game:answer-count', [
        'round' => $round, 'count' => $count,
    ]);
    Cache::put($throttleKey, true, 1); // 1 segundo
}
```

**Resultado:** 10K answers → max ~10 broadcasts. **99% reduccion.**

**Esfuerzo:** 20 lineas, 30 min.

### FIX-2: Indexes en live_game_participants (CRITICO)

**Problema:** `closeRound()` hace `SUM(score) GROUP BY attendee_id` sobre toda la tabla sin indices. Con 10K participantes x 5 rondas = 50K rows → full table scan 500ms-2s.

**Solucion:** Migracion con indices compuestos.

```php
Schema::table('live_game_participants', function (Blueprint $table) {
    $table->index(['game_id', 'round']);           // para distribution count
    $table->index(['game_id', 'attendee_id']);     // para leaderboard SUM
});
```

**Resultado:** closeRound query: 500ms → 20ms. **10x mas rapido.**

**Esfuerzo:** 1 migracion, 5 min.

### FIX-3: Cache getEligiblePool() (ALTO)

**Problema:** `getEligiblePool()` hace 2 DB queries + 1 HTTP al socket server cada vez. MC la llama en `bySession()` que se ejecuta cada 5s. Con 30 sesiones visibles = 30 HTTP calls al socket server cada 5s.

**Solucion:** Cache en Redis 30 segundos, invalidar al cambiar estado del juego.

```php
public static function getEligiblePool(LiveGame $game): Collection
{
    return Cache::remember(
        "game:eligible:{$game->id}",
        30,
        fn() => self::buildPool($game)
    );
}
// En launch(), spin(), draw(): Cache::forget("game:eligible:{$game->id}");
```

**Resultado:** HTTP calls al socket: 30/5s → 1/30s. **30x reduccion.**

**Esfuerzo:** 15 lineas, 1 hora.

### FIX-4: HTTP connection pool para broadcasts (ALTO)

**Problema:** Laravel HTTP client usa pool default de 6 conexiones. Con broadcasts rapidos (100/s en pico de trivia), las conexiones se saturan y forman cola.

**Solucion:** Aumentar pool y reducir timeouts.

```php
// GameService::broadcast()
Http::connectTimeout(1)->timeout(2)
    ->withHeaders(['X-Internal-Secret' => $secret])
    ->post("{$socketUrl}/internal/broadcast", $payload);
```

**Resultado:** Acepta 10x mas broadcasts concurrentes sin timeout.

**Esfuerzo:** 10 lineas, 30 min.

### Cronograma de implementacion

| Fix | Cuando | Prerequisito de |
|-----|--------|-----------------|
| FIX-2 (indices) | Inmediato, no rompe nada | Stress test |
| FIX-1 (throttle) | Pre stress test | Stress test trivia |
| FIX-3 (cache pool) | Pre stress test | Stress test MC |
| FIX-4 (HTTP pool) | Pre stress test | Stress test carga |

**Total:** ~3 horas de trabajo. Todos van en la fase P6 (Deploy + Stress test).

### Lo que NO necesita cambios

- Socket.IO Redis adapter: diseñado para 10K+ (broadcast 1→todos)
- Real-time invalidation: jitter + debounce 800ms ya implementados
- Rate limiting: por usuario y por sesion
- Sanctum tokens: 12h de vida, suficiente
- HA infra: 2 VPS + PlanetScale + Upstash + Cloudflare = sin cambios

### Patron validado

El patron de broadcast (1 mensaje → todos los clientes) esta validado en produccion con el sistema de bingo que manejo 4,000 usuarios reales simultaneos. Trivia usa el mismo patron:
- Backend decide resultado (server-side)
- 1 broadcast al room del evento
- Socket.IO + Redis adapter distribuye a N clientes
- No hay broadcast individual por usuario

---

## 12. Rooms aislados: Event Pulse (Rooms.pulse)

### Problema detectado (2026-04-24)

`broadcastAudience()` emitia `session:audience` al `Rooms.event()` (todos los usuarios del evento). Con 10K usuarios y 10 salones activos, esto generaba hasta **200,000 mensajes/segundo** que los 10K usuarios recibian e ignoraban — solo Event Pulse (1-2 dashboards) necesitaba esa data.

### Solucion: Rooms.pulse()

Se creo un room aislado `pulse:{eventId}` exclusivo para dashboards Event Pulse. `broadcastAudience()` ahora emite a:

1. `Rooms.session(sessionId)` — Mission Control (sin cambios, como siempre funciono)
2. `Rooms.pulse(eventId)` — Event Pulse (1-2 sockets, aislado del event room)

**Ya NO se emite `session:audience` al `Rooms.event()`** (10K sockets). Los 10K usuarios nunca usaban ese evento.

### Impacto en performance (10K, 10 salones)

| Metrica | ANTES | AHORA |
|---------|-------|-------|
| Destino session:audience | event room (10,000 sockets) | pulse room (1-2 sockets) |
| Payload viewers[] | sin limite (hasta 1,000 objetos) | max 20 objetos |
| Msgs/sec pico | ~200,000 | ~25 |
| Bandwidth desperdiciado | ~8 MB/s | ~20 KB/s |

### Que NO cambio

- `checkin:update` → sigue en `Rooms.event()` (la app lo necesita, rate bajo ~10/min)
- `data:invalidate` → sigue en `Rooms.event()` (la app lo necesita para refrescar agenda, branding)
- `wall:post` → sigue en `Rooms.event()` (rate bajo ~5/min)
- `session:audience` a `Rooms.session()` → sigue igual (MC lo usa)
- Disconnect handler → sin cambios, MC sigue funcionando identico

### Si Pulse se cae

Cero impacto. Pulse es read-only (no escribe, no controla). Si se desconecta:
- La app sigue funcionando
- Mission Control sigue funcionando
- El socket server sigue funcionando
- Los 1-2 sockets de Pulse se desconectan y nadie lo nota

### Auth: tokens ep_*

Pulse se autentica con `pulse_token` (prefijo `ep_`), validado via `GET /api/v1/pulse/validate`. El socket server asigna `role: 'pulse'` y auto-join a `Rooms.pulse(eventId)` en la conexion. No necesita `join:event` para recibir `session:audience`.

Pulse SI hace `join:event` para recibir `checkin:update`, `data:invalidate` y `wall:post` que van al event room.

### Archivos modificados

| Archivo | Repo | Cambio |
|---------|------|--------|
| `src/rooms.ts` | Socket | `Rooms.pulse(eventId)` agregado |
| `src/auth.ts` | Socket | `validatePulseToken()` para tokens ep_* |
| `src/chat.ts` | Socket | `broadcastAudience` emite a `Rooms.pulse()`, viewers max 20, `fallbackEventId` |
| `src/index.ts` | Socket | Middleware ep_*, auto-join pulse room, connKey sintetico |

---

*EventOS Alta Disponibilidad v2.0*
*Actualizado: 2026-04-25 — stack migrado a DO sao1, 99.9% realista, optimistic UI audit*
*Historico: v1.0 (04-07 Hetzner), v1.1 (04-20 session stats), v1.2 (04-24 Rooms.pulse)*
