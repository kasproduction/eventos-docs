# STACK DE PRODUCCION — la lista de compra por nivel

> **Escrito el 2026-08-17.** Este es EL documento del stack: cuantas maquinas,
> de que tamaño, quien balancea, que base de datos, que cache, donde viven los
> archivos, y por que cada decision. Lo que aqui dice MEDIDO se midio con la
> persona 301 en Chrome; lo que dice DERIVADO es aritmetica sobre lo medido y
> **no se le promete a un cliente sin montarlo y medirlo primero**.
>
> Los numeros de "personas" de `COMO-CRECEMOS.md` §7 y de `DISPONIBILIDAD-HA.md`
> estan INFLADOS (script viejo, 1 peticion por pantalla). La curva que vale es
> la de `docs/roadmaps/ROADMAP-INFRAESTRUCTURA.md` → "EL CATALOGO".

---

## 0. Las decisiones cerradas (no re-preguntar)

| Decision | Que se elige | Por que | Que se descarta |
|---|---|---|---|
| **Quien balancea** | **Cloudflare Load Balancer** (round-robin + health check cada 10 s + failover) | Sin punto unico de falla (no vive en ninguna de nuestras maquinas) · portable (balancea IPs de CUALQUIER proveedor: cumple el criterio 3 del roadmap) · ya soporta WebSocket · ~$5-10/mes · Cloudflare ya es el DNS | **nginx propio round-robin**: seria una maquina mas y el nuevo punto unico de falla. **DO Load Balancer**: funciona, pero ata a DO y no aporta nada que Cloudflare no de |
| **Base de datos** | **DigitalOcean Managed MySQL** (misma region, VPC privada), con **nodo standby** desde el nivel 2 y **replica de lectura** desde el nivel 3 | RTT < 1 ms por VPC vs 80-150 ms de un servicio remoto (cada pantalla son 10-15 consultas: 100 ms × 15 = 1,5 s por pantalla) · precio plano, no se dispara en las olas del evento · respaldos diarios incluidos · failover automatico al standby | **PlanetScale** (se hablo en abril): remoto (80-150 ms por consulta), sin claves foraneas (Vitess) — el esquema las usa por todos lados —, precio por uso que se dispara justo cuando mas gente hay. Decidido en `DISPONIBILIDAD-HA.md` v2.0 y confirmado hoy |
| **Redis** | **DigitalOcean Managed Redis/Valkey** con TLS, desde el nivel 2 | Con mas de una maquina de API la cache de auth, las sesiones, el limitador y el adaptador de sockets TIENEN que ser compartidos · al cruzar la red lleva fichas de autenticacion → TLS obligatorio · $15/mes | Redis en el droplet de sockets: funciona, pero se cae con el |
| **Sockets** | **UN nodo** de sockets (2 vCPU) para todos los nodos de API; 2 nodos desde el nivel 3 | Medido: 5.000 conexiones = 0-4% de CPU. Clonarlo por capacidad seria pagar por nada; se duplica solo por REDUNDANCIA en el nivel 3 | — |
| **Colas (Horizon)** | En el nodo de sockets | Casi no consumen (push, correos, exports); no merecen maquina | — |
| **Webapp (Next)** | Nodo propio desde el nivel 2; 2 nodos desde el nivel 3 | Next es el 25% del CPU con la gente navegando; en su maquina no compite con Laravel. Es sin estado (su cache del marco se invalida por aviso del backend a la LISTA de nodos: `WEBAPP_INTERNAL_URLS`) | — |
| **Archivos** | **Cloudflare R2** con dominio propio (`cdn.<dominio>`) | Ya hecho y probado; sin egress; fuera de las maquinas (nada en disco = cualquier nodo es desechable) | — |
| **Correo** | Resend | Ya hecho | — |
| **Errores / salud** | Sentry (ya) + Laravel Pulse (I.5, pendiente) | — | — |
| **Respaldo fuera del proveedor** | Volcado cifrado a R2 por cron (I.4) + snapshots de DO | El unico respaldo hoy vive dentro de DO | — |
| **Region** | La mas cercana al publico. DO no tiene Sao Paulo: para Colombia, **nyc1** (~93 ms) | — | — |
| **Punto de operacion** | **50% de CPU en regimen** | El codo llega antes del 60%: rompen las olas, no el promedio | — |

**Regla que sale de hoy y aplica a TODOS los niveles:** `TRUSTED_PROXIES` debe
incluir la IP publica de cada maquina de la webapp **y los rangos de Cloudflare**
cuando el trafico pase por el balanceador; si no, Laravel ve a todo el mundo
como una sola IP y los limitadores por IP se agotan entre todos (bug de
`41b8040`, reaparecido el 2026-08-17 por un snapshot con la IP vieja).

---

## 1. NIVEL 1 — BASICO — hasta ~300 personas activas — **MEDIDO 2026-08-17**

Para un evento de tamaño medio donde se acepta que **si la maquina cae, el
evento se cae** (y se remonta en ~15 min con `deploy.sh`).

| Pieza | Que | Tamaño | ~USD/mes |
|---|---|---|---|
| Droplet unico | nginx + PHP-FPM (Laravel) + Next + 4 procesos de socket + Horizon + MySQL + Redis | **4 vCPU / 8 GB** Premium Intel | ~$48-63 |
| Cloudflare | DNS + proxy naranja (I.6) | Free | $0 |
| R2 | archivos | pago por uso | ~$1-5 |
| Resend | correo | free/pro segun volumen | $0-20 |
| Snapshot diario + volcado a R2 | respaldo | — | ~$1-3 |
| **Total** | | | **~$60-90** |

**Medido:** 300 activas plano (persona 301: TTFB 170-335 ms, CPU 41%) · 400 se
siente · 480-500 al limite (CPU 69-73%) · 700 roto.
**Promesa:** RTO ~15 min, RPO 1 hora (volcado horario). Punto unico de falla, se
dice claro. **Cuando NO alcanza:** evento con mas de ~300 personas navegando a la
vez, o cliente que exige que "no se caiga" — eso es nivel 2.

---

## 2. NIVEL 2 — SIN PUNTO UNICO EN API Y DATOS — hasta ~1.000 activas — DERIVADO

| Pieza | Que | Tamaño | Cuantas | ~USD/mes |
|---|---|---|---|---|
| **API** | nginx + PHP-FPM (Laravel) | 4 vCPU / 8 GB | **2** | ~$96-126 |
| **Webapp** | Next (pm2) | 4 vCPU / 8 GB | **1** | ~$48-63 |
| **Sockets + colas** | 4 procesos socket + Horizon | 2 vCPU / 4 GB | **1** | ~$24-32 |
| **MySQL** | DO Managed MySQL **con standby** (HA) | 2 vCPU / 4 GB | 1 cluster (primario + standby) | ~$100-120 |
| **Redis** | DO Managed Redis/Valkey, TLS | 1 GB | 1 | ~$15 |
| **Balanceador** | Cloudflare Load Balancer: `api.` → 2 API; `app.` → 1 web (2 en nivel 3) | — | 1 | ~$5-10 |
| Cloudflare | DNS + proxy naranja + WAF basico | Free/Pro | — | $0-20 |
| R2 · Resend · Sentry · respaldos | | | | ~$5-30 |
| **Total: 4 droplets + 2 administrados** | | | | **~$300-420** |

**Como se deriva:** con 300 activas Laravel gasta 1,0 nucleo, Next 0,4, MySQL
0,3. Para 1.000 al 50%: Laravel ~6,6 nucleos (2×4 vCPU = 8 ✓), Next ~2,8 (1×4
✓), MySQL ~1,8 (2 vCPU administrado ✓). **Es lineal, y eso es lo que hay que
verificar:** Laravel→MySQL deja de ser localhost (VPC < 1 ms, pero no 0), y el
balanceador reparte pero no multiplica.

**Promesa:** se cae un nodo API → el balanceador lo saca en ~30 s y nadie lo
nota. Se cae la BD primaria → el standby entra solo en ~1 min. Se cae la webapp
o el nodo de sockets → se remonta en minutos (NO es transparente: por eso el
nivel 3). RPO minutos (BD administrada) + volcado propio a R2.

**Que cambia en el codigo/config respecto al nivel 1** (nada nuevo por
inventar, todo existe):
- `.env` de cada API: `DB_HOST`/`REDIS_HOST` a los administrados (TLS en Redis),
  `SESSION_DRIVER=redis`, `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`.
- Cada API: `WEBAPP_INTERNAL_URLS=http://<ip-privada-web>:3000` (nivel 3: las
  dos, separadas por coma). `SOCKET_SERVER_URL=http://<ip-privada-sockets>:3001`.
- Webapp: `API_INTERNAL_URL` apuntando al balanceador de la API (o a la IP
  privada de un API con fallback — a decidir al montar).
- Sockets: `LARAVEL_API_URL` al balanceador; adaptador Redis al administrado.
- `deploy.sh --rol api|web|sockets|todo` (por escribir: hoy solo monta "todo").
- `TRUSTED_PROXIES`: IPs privadas de la webapp + rangos de Cloudflare.

---

## 3. NIVEL 3 — TRANQUILIDAD — hasta ~2.500 activas — DERIVADO

Nada es unico. Cualquier maquina puede caer y el evento sigue.

| Pieza | Tamaño | Cuantas | ~USD/mes |
|---|---|---|---|
| API | 4 vCPU / 8 GB | **4-5** | ~$200-315 |
| Webapp | 4 vCPU / 8 GB | **2** | ~$96-126 |
| Sockets + colas | 2 vCPU / 4 GB | **2** | ~$48-64 |
| MySQL administrado | 4 vCPU / 8 GB, **standby + replica de lectura** (exports y Data Center leen de la replica) | 1 cluster | ~$240-300 |
| Redis administrado HA | 1-2 GB | 1 | ~$30-60 |
| Cloudflare LB + Pro | | | ~$25-30 |
| R2 · Resend · Sentry · respaldos | | | ~$10-40 |
| **Total: 8-9 droplets + 2 administrados** | | | **~$650-950** |

Derivacion: 2.500 al 50% → Laravel ~16,5 nucleos (4-5×4), Next ~7 (2×4), MySQL
~4,5 (4 vCPU + replica para lecturas pesadas).

---

## 4. NIVEL 4 — MASIVO — 5.000-10.000 activas — DERIVADO

| Pieza | Tamaño | Cuantas | ~USD/mes |
|---|---|---|---|
| API | 4 vCPU / 8 GB (o 8 vCPU / 16 GB, la mitad) | **8-10** | ~$400-630 |
| Webapp | 4 vCPU / 8 GB | **3-4** | ~$150-250 |
| Sockets + colas | 2-4 vCPU | **3** | ~$72-150 |
| MySQL administrado | 8 vCPU / 16 GB, standby + **2 replicas** | 1 cluster | ~$500-700 |
| Redis administrado HA | 2-4 GB | 1 | ~$60-100 |
| Cloudflare LB + Pro/Business | | | ~$50-250 |
| **Total: 14-17 droplets + 2 administrados** | | | **~$1.300-2.100** |

A este nivel hay preguntas que solo se responden midiendo: si conviene menos
maquinas mas grandes (menos nodos que cuidar) o mas chicas (mas granular la
falla); si MySQL aguanta las escrituras de 10.000 personas (los reads van a las
replicas, las escrituras no); y el techo de un solo balanceador de Cloudflare.

---

## 5. Lo que ya esta listo para multi-nodo (no hay que inventarlo)

- **WebSocket puro, sin afinidad de sesion** → cualquier reparto sirve.
- **Adaptador Redis entre procesos de socket** → un mensaje emitido en un nodo
  llega a los del otro (probado con 4 procesos).
- **Cache de auth en Redis** → compartida entre nodos por diseño.
- **Archivos en R2** → cero estado en disco.
- **Webapp invalida su cache del marco por aviso del backend a una LISTA de
  URLs** (2026-08-17).
- **`deploy.sh` portable** (Ubuntu + apt, nada propio de DO).

## 6. Lo que falta para poder MONTAR el nivel 2 (proxima sesion)

1. `deploy.sh --rol api|web|sockets|todo` (hoy monta "todo" en una).
2. Provisionar MySQL y Redis administrados en DO (VPC de la misma region).
3. Cloudflare a naranja + Load Balancer con `api.` (2 origins) y `app.`.
4. `TRUSTED_PROXIES` con rangos de Cloudflare + IPs privadas.
5. Correr `entrar-por-la-puerta.js` con 1.000 y **Kamilo + Claude en Chrome como
   las 1.001**. Con eso la fila 2 pasa a MEDIDA.

## 7. La respuesta corta, para cotizar

- **Hasta 300 personas navegando a la vez** → 1 droplet 4 vCPU (~$70/mes). Se
  puede caer.
- **Hasta 1.000** → 4 droplets + MySQL y Redis administrados + balanceador de
  Cloudflare (~$350-420/mes). La API y los datos no se caen; webapp y sockets
  se remontan en minutos.
- **Hasta 2.500** → 8-9 droplets + administrados con replica (~$650-950/mes).
  Nada se cae.
- **5.000-10.000** → 14-17 droplets (~$1.300-2.100/mes). Medir antes.

"A la vez" = navegando activamente al ritmo del script (una pantalla cada
20-40 s). En un evento real la gente mira menos seguido, asi que caben mas
inscritos por cada nivel — cuantos mas, se sabra cuando se instrumente el ritmo
real (I.2).
