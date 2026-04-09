# EventOS — Alta Disponibilidad e Infraestructura

> Arquitectura para 99.99% uptime (~52 min downtime/año).
> Principio: ningún componente es punto único de falla.
> Versión: 1.0 | Fecha: 2026-04-07

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

## 2. Arquitectura de Producción (99.99%)

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
                         │  Health checks cada 30s │
                         │  Failover automático    │
                         │  Cache estáticos (CDN)  │
                         │  Rate limiting global   │
                         │  SLA: 100% (Enterprise) │
                         └──────┬──────────┬───────┘
                                │          │
                    ┌───────────┴──┐  ┌────┴───────────┐
                    │   VPS-1      │  │   VPS-2         │
                    │  (Primary)   │  │  (Secondary)    │
                    │              │  │                  │
                    │  Nginx       │  │  Nginx          │
                    │  Laravel     │  │  Laravel         │
                    │  PHP-FPM     │  │  PHP-FPM         │
                    │  Socket.IO   │  │  Socket.IO       │
                    │              │  │                  │
                    │  UFW + F2B   │  │  UFW + F2B      │
                    │  SSH key     │  │  SSH key         │
                    └──────┬───────┘  └──────┬──────────┘
                           │                 │
              ┌────────────┴─────────────────┴──────────────┐
              │                                              │
    ┌─────────┴──────────┐              ┌───────────────────┴─┐
    │  PlanetScale       │              │  Upstash Redis      │
    │  (Managed MySQL)   │              │  (Managed Redis)    │
    │  ──────────────    │              │  ──────────────     │
    │  Réplicas auto     │              │  Global replication │
    │  Failover auto     │              │  Failover auto      │
    │  Backups diarios   │              │  Persistence        │
    │  Branching (CI/CD) │              │  TLS encryption     │
    │  Connection pooling│              │  99.99% SLA         │
    │  SLA: 99.99%       │              │                     │
    └────────────────────┘              └─────────────────────┘
              │
    ┌─────────┴──────────┐
    │  Cloudflare R2     │
    │  (Object Storage)  │
    │  ──────────────    │
    │  CDN global        │
    │  S3-compatible     │
    │  Réplicas auto     │
    │  SLA: 99.99%       │
    └────────────────────┘
```

### ¿Qué pasa cuando algo falla?

| Componente | Qué pasa | Tiempo de recuperación | Impacto para el usuario |
|------------|----------|----------------------|------------------------|
| VPS-1 se cae | Cloudflare LB redirige todo a VPS-2 | **<30 segundos** | Ninguno (tal vez 1 request falla) |
| VPS-2 se cae | Cloudflare LB redirige todo a VPS-1 | **<30 segundos** | Ninguno |
| MySQL se cae | PlanetScale failover a réplica | **<5 segundos** | Ninguno (automático) |
| Redis se cae | Upstash failover a réplica | **<3 segundos** | Reconexión de socket (automática) |
| R2 se cae | Cloudflare distribuido, 99.99% SLA | **~0 segundos** | Ninguno (CDN cache) |
| DDoS ataque | Cloudflare absorbe (Tbps de capacidad) | **0 segundos** | Ninguno |
| Deploy sale mal | Blue-green: VPS-2 sigue con versión anterior | **0 segundos** | Ninguno |
| Cert SSL expira | Cloudflare renueva automáticamente | **0 segundos** | Ninguno |
| Disco VPS lleno | Solo logs/temp — DB y storage no están en VPS | **N/A** | Ninguno |

---

## 3. Servicios Managed — Selección y Justificación

### 3.1 Base de Datos: PlanetScale

| Aspecto | Detalle |
|---------|---------|
| **Qué es** | MySQL managed serverless (vitess bajo el capó, la misma tech de YouTube) |
| **Por qué** | Réplicas automáticas, 0 downtime migrations, branching para CI/CD, connection pooling incluido |
| **Plan** | Scaler ($29/mes): 10GB storage, 1 billion row reads/mes, 10M row writes/mes |
| **Free tier** | 5GB, 1B reads, 10M writes — suficiente para empezar |
| **SLA** | 99.99% uptime |
| **Backups** | Automáticos cada 12 horas, retención 2 días (free) o 30 días (paid) |
| **Failover** | Automático, transparente para la aplicación |
| **Regiones** | US, EU, Asia — elegir la más cercana a tu audiencia |

**Cambio en EventOS:**
```env
# .env.production
DB_CONNECTION=mysql
DB_HOST=aws.connect.psdb.cloud        # PlanetScale endpoint
DB_PORT=3306
DB_DATABASE=eventos_prod
DB_USERNAME=xxxxx                      # PlanetScale credentials
DB_PASSWORD=pscale_pw_xxxxx
MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt  # TLS obligatorio
```

**Alternativas:** DigitalOcean Managed MySQL ($15/mes, 1GB RAM), AWS RDS, Supabase.

### 3.2 Redis: Upstash

| Aspecto | Detalle |
|---------|---------|
| **Qué es** | Redis serverless con billing por comando |
| **Por qué** | 0 mantenimiento, réplicas globales, TLS por defecto, pay-per-use |
| **Plan** | Pay as you go: $0.2 por 100K commands. Pro ($10/mes): 10K commands/seg |
| **Free tier** | 10K commands/día, 256MB — suficiente para desarrollo |
| **SLA** | 99.99% uptime |
| **Persistence** | Sí — no pierdes datos en restart |
| **Multi-region** | Global replication disponible (lecturas locales) |
| **TLS** | Obligatorio por defecto (cifrado en tránsito) |

**Cambio en EventOS:**
```env
# .env.production — Backend
REDIS_CLIENT=predis
REDIS_HOST=global-xxxxx.upstash.io
REDIS_PASSWORD=AxxxxxxxxxxxxxxxxxxxxxxxxxxxQ=
REDIS_PORT=6379
REDIS_SCHEME=tls                       # TLS obligatorio en Upstash

# .env.production — Socket server
REDIS_HOST=global-xxxxx.upstash.io
REDIS_PORT=6379
REDIS_PASSWORD=AxxxxxxxxxxxxxxxxxxxxxxxxxxxQ=
```

**Alternativas:** Redis Cloud ($5/mes, 30MB), AWS ElastiCache, DigitalOcean Managed Redis ($10/mes).

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
| **Cómo funciona** | Health checks a cada VPS cada 30s. Si uno no responde, redirige al otro |
| **Plan** | $5/mes por LB + $0.50/mes por origin server |
| **Health check** | `GET /api/v1/health` — si no retorna 200 en 5s, se marca como down |
| **Failover** | Automático. Si VPS-1 falla, 100% tráfico va a VPS-2 |
| **Session affinity** | No necesaria — nuestras APIs son stateless (sessions en Redis) |

**Configuración Cloudflare LB:**
```
Pool: eventos-backend
├── Origin 1: vps1.tudominio.com (primary, weight: 1)
├── Origin 2: vps2.tudominio.com (secondary, weight: 1)
└── Health check: GET /api/v1/health, interval: 30s, threshold: 2 failures

Steering: Round robin (o proximity si VPS en diferentes regiones)
Fallback pool: vps2 only
```

### 3.5 Socket.IO con múltiples nodos

Ya implementado: **Redis adapter** (`@socket.io/redis-adapter`). Esto significa que Socket.IO en VPS-1 y VPS-2 comparten estado via Redis.

```
Cliente A (conectado a VPS-1) envía chat:send
    → VPS-1 publica en Redis
    → VPS-2 recibe via Redis pub/sub
    → Cliente B (conectado a VPS-2) recibe el mensaje
```

**Sticky sessions para WebSocket:** Cloudflare maneja esto automáticamente con `websockets: true` en la zona.

---

## 4. Deploy Strategy: Blue-Green

Con 2 VPS, el deploy es zero-downtime:

```
Estado normal:
    Cloudflare LB → VPS-1 (v1.0) + VPS-2 (v1.0)

Paso 1: Sacar VPS-2 del LB
    Cloudflare LB → VPS-1 (v1.0)     VPS-2 (offline)

Paso 2: Deploy v1.1 en VPS-2
    Cloudflare LB → VPS-1 (v1.0)     VPS-2 (v1.1, testing)

Paso 3: Verificar VPS-2 funciona (smoke tests)
    curl https://vps2.tudominio.com/api/v1/health → 200 OK

Paso 4: Meter VPS-2 al LB, sacar VPS-1
    Cloudflare LB → VPS-2 (v1.1)     VPS-1 (v1.0, actualizando)

Paso 5: Deploy v1.1 en VPS-1
    Cloudflare LB → VPS-1 (v1.1) + VPS-2 (v1.1)

Resultado: 0 downtime. Si v1.1 tiene bug → rollback = meter VPS-1 (v1.0) de vuelta.
```

---

## 5. Monitoreo y Alertas

### 5.1 Health Checks

| Endpoint | Qué verifica | Frecuencia |
|----------|-------------|-----------|
| `GET /api/v1/health` | Laravel responde (200 OK) | Cloudflare: 30s |
| `GET /health` (socket) | Socket.IO responde + Redis conectado | BetterStack: 60s |
| PlanetScale dashboard | DB healthy, réplicas sincronizadas | PlanetScale: automático |
| Upstash dashboard | Redis healthy, latencia | Upstash: automático |

### 5.2 Alertas

| Evento | Canal | Tiempo respuesta |
|--------|-------|-----------------|
| VPS down (health check fail) | Email + Push + Slack | Cloudflare failover en <30s |
| DB latencia > 500ms | Email | PlanetScale alerta automática |
| Redis memory > 80% | Email | Upstash alerta automática |
| Error rate > 5% (Sentry) | Email + Push | Investigar en <15 min |
| SSL cert próximo a expirar | Email | Cloudflare renueva auto, alerta 30d antes |

### 5.3 Herramientas de monitoreo

| Herramienta | Propósito | Costo |
|-------------|----------|-------|
| **Cloudflare Analytics** | Tráfico, cache hit rate, threats blocked | Incluido |
| **BetterStack** (o UptimeRobot) | Uptime monitoring + incident pages | Free tier o $10/mes |
| **Sentry** | Error tracking backend + app | Free tier (5K events/mes) o $26/mes |
| **PlanetScale Insights** | Query performance, slow queries | Incluido |
| **Upstash Console** | Redis metrics, commands/sec | Incluido |

---

## 6. Backups y Recuperación

### 6.1 Estrategia de backups

| Dato | Servicio | Frecuencia | Retención | Automático |
|------|---------|-----------|-----------|-----------|
| Base de datos | PlanetScale | Cada 12h | 30 días (paid) | ✅ Sí |
| Redis state | Upstash | Persistencia continua | Automático | ✅ Sí |
| Storage (imágenes) | Cloudflare R2 | Distribuido (inherente) | Ilimitado | ✅ Sí |
| Código fuente | GitHub | Cada push | Ilimitado | ✅ Sí |
| Config/secrets | Backup manual encrypted | Semanal | Indefinido | Manual |

### 6.2 RPO y RTO

| Métrica | Valor | Significado |
|---------|-------|-------------|
| **RPO** (Recovery Point Objective) | **< 12 horas** | Máximo pierdes 12h de datos (backup interval DB) |
| **RTO** (Recovery Time Objective) | **< 5 minutos** | Todo se recupera solo (managed services + failover) |

### 6.3 Disaster Recovery

| Escenario | Recuperación |
|-----------|-------------|
| VPS destruido | Levantar nuevo VPS, `docker compose up`. DB y Redis no se afectan (managed). **Tiempo: 15-30 min** |
| DB corrupta | PlanetScale restore desde backup. **Tiempo: <5 min** |
| Cuenta cloud comprometida | Rotar secrets, revocar tokens, restore desde backup. **Tiempo: 1-2 horas** |
| Región entera cae (AWS us-east-1) | PlanetScale: failover a otra región. VPS: levantar en otra región. **Tiempo: 30-60 min** |

---

## 7. Costos Estimados (99.99% uptime)

### Escenario: 1-5 eventos simultáneos, hasta 3,000 usuarios concurrentes

| Servicio | Plan | Costo/mes |
|----------|------|-----------|
| VPS-1 (Hetzner CX22) | 2 vCPU, 4GB RAM, 40GB | €4.50 (~$5) |
| VPS-2 (Hetzner CX22) | 2 vCPU, 4GB RAM, 40GB | €4.50 (~$5) |
| PlanetScale | Scaler (10GB, 1B reads) | $29 |
| Upstash Redis | Pro (10K cmd/seg) | $10 |
| Cloudflare Pro | WAF + analytics | $20 |
| Cloudflare LB | Load balancing 2 origins | $6 |
| Cloudflare R2 | ~10GB storage | ~$0.15 |
| BetterStack | Monitoring básico | Free |
| Sentry | Error tracking | Free (5K events) |
| **TOTAL** | | **~$75/mes** |

### Escenario: Escalar a 10+ eventos, 10,000+ usuarios

| Cambio | Costo adicional |
|--------|----------------|
| +1 VPS (VPS-3) | +$5/mes |
| PlanetScale Team | +$10/mes |
| Upstash Business | +$40/mes |
| Sentry Team | +$26/mes |
| **TOTAL escalado** | **~$160/mes** |

### Comparación con 1 VPS

| | 1 VPS | HA (2 VPS + managed) |
|---|---|---|
| Costo | $8-15/mes | $75/mes |
| Downtime/año | ~4-8 horas | ~52 minutos |
| Recuperación automática | No | Sí |
| Escalar | Apagar, resize, encender | Agregar VPS al LB |
| Backups DB | Manual (tú lo haces) | Automático (PlanetScale) |
| Si te hackean el VPS | Pierdes todo | DB y storage intactos (están afuera) |

**$75/mes por 99.99% uptime.** Se lo cobras al cliente como parte del servicio.

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

## 8. Configuración VPS (cada uno idéntico)

### 8.1 Docker Compose (producción)

```yaml
# docker-compose.prod.yml — CADA VPS tiene esto
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

# NO hay MySQL ni Redis — son managed services externos
```

### 8.2 Lo que NO corre en el VPS

| Servicio | Dónde corre | Por qué |
|----------|-------------|---------|
| MySQL | PlanetScale (cloud) | Réplicas, backups, failover automático |
| Redis | Upstash (cloud) | Persistencia, TLS, failover automático |
| Storage | Cloudflare R2 (CDN) | Distribuido globalmente, 0 egress |
| DNS + LB | Cloudflare | DDoS protection, health checks |

### 8.3 Hardening de cada VPS

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
INFRAESTRUCTURA
✅ Arquitectura documentada (este documento)
🔲 VPS-1 provisionado (Hetzner/DO)
🔲 VPS-2 provisionado (Hetzner/DO)
🔲 Docker Compose prod en ambos VPS
🔲 UFW + SSH key + Fail2ban en ambos
🔲 Non-root user en ambos

MANAGED SERVICES
🔲 PlanetScale: cuenta + database + credentials
🔲 Upstash: cuenta + Redis instance + credentials
🔲 Cloudflare R2: bucket configurado (ya existe en dev)

CLOUDFLARE
🔲 Dominio configurado
🔲 SSL Full (Strict)
🔲 Load Balancer configurado (2 origins)
🔲 Health checks activos (GET /api/v1/health cada 30s)
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
🔲 PlanetScale backups verificados
🔲 Secrets backup encrypted (offline)
🔲 Test de restore ejecutado
```

---

## 10. SLA que podemos ofrecer al cliente

Con esta arquitectura:

| Métrica | Garantía | Cómo se logra |
|---------|----------|--------------|
| **Uptime** | 99.99% (~52 min downtime/año) | 2 VPS + managed DB/Redis + Cloudflare LB |
| **RPO** | < 12 horas | PlanetScale backups automáticos |
| **RTO** | < 5 minutos | Failover automático en todos los niveles |
| **Latencia API** | < 200ms p95 | CDN cache + connection pooling + Redis cache |
| **Latencia WebSocket** | < 100ms | Redis adapter + servidor cercano a audiencia |
| **DDoS protection** | Incluida | Cloudflare (absorbe ataques de Tbps) |
| **Cifrado** | En tránsito y reposo | TLS 1.2+ (Cloudflare) + AES-256 (Laravel) + TLS Redis (Upstash) |
| **Backups** | Automáticos, 30 días | PlanetScale + R2 |

---

*EventOS Alta Disponibilidad v1.0*
*Próxima revisión: pre-deploy a producción*
