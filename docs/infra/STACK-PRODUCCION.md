# STACK DE PRODUCCION — el combo necesario para no sufrir, segun cuanta gente

> **Escrito el 2026-08-17.** Este es EL documento del stack: cuantas maquinas,
> de que tamaño, quien balancea, que base de datos, donde vive Redis, donde
> viven los sockets, y por que. Lo que aqui dice MEDIDO se midio con la persona
> 301 en Chrome; lo que dice DERIVADO es aritmetica sobre lo medido y **no se le
> promete a un cliente sin montarlo y medirlo primero**.

---

## 0. EL PRINCIPIO — es el de `DISPONIBILIDAD-HA.md` §1 desde el primer dia

> **"Nada corre en un solo lugar."** Todo lo que se vende NO tiene punto unico
> de falla. **Un droplet solo es demo/QA y no se cotiza.**

Y el objetivo (criterio corregido el 2026-08-02): **cero degradacion durante la
ventana del evento** — no 99,999% anual, que protege un martes a las 3 am. El
dia del evento nadie ve error, nadie ve pantalla lenta, y si una maquina muere
nadie tiene que hacer nada.

**Por que no basta un droplet con pm2 y reinicio automatico:** si el droplet
muere y no arranca, el evento se cae 20-40 minutos mientras alguien se entera,
restaura el snapshot y cambia el DNS — y se pierde todo lo que paso desde el
snapshot: check-ins, votos, fotos, leads. Eso NO es enterprise. Eso es la demo.

**La arquitectura es UNA sola.** Lo que cambia con la cantidad de gente es
cuantos nodos de cada rol y de que tamaño:

```
                Internet
                    │
        ┌───────────┴───────────┐
        │  Cloudflare           │  DNS · WAF/DDoS · TLS · CDN
        │  Load Balancer        │  round-robin · health check 10 s · failover
        └──┬────────┬────────┬──┘
           │        │        │
      ┌────┴───┐ ┌──┴─────┐ ┌┴────────┐
      │ API ×N │ │ WEB ×N │ │ SOCK ×N │   droplets, sin estado, desechables
      │ nginx  │ │ Next   │ │ socket  │
      │ Laravel│ │        │ │ Horizon │
      └────┬───┘ └──┬─────┘ └┬────────┘
           └────────┼────────┘
                    │  VPC privada (< 1 ms)
        ┌───────────┼──────────────┐
   ┌────┴─────┐ ┌───┴──────┐ ┌─────┴─────┐
   │ MySQL    │ │ Redis    │ │ R2        │
   │ admin.   │ │ admin.   │ │ archivos  │
   │ standby  │ │ HA + TLS │ │           │
   │ +replica │ │          │ │           │
   └──────────┘ └──────────┘ └───────────┘
```

**Cada rol en su sitio, como se hablo desde el principio:** Redis en UN lugar
(administrado, HA), los sockets en su nodo, la BD fuera de los droplets, los
archivos en R2. Los droplets no guardan NADA: cualquiera se puede tirar y
volver a crear con `deploy.sh`.

---

## 1. Las decisiones cerradas (no re-preguntar)

| Decision | Que se elige | Por que | Que se descarta |
|---|---|---|---|
| **Quien balancea** | **Cloudflare Load Balancer** (round-robin + health check cada 10 s + failover) | Sin punto unico de falla (no vive en nuestras maquinas) · portable (balancea IPs de CUALQUIER proveedor) · soporta WebSocket · WAF/DDoS/CDN incluidos · ~$5-10/mes | **nginx propio round-robin**: una maquina mas y el nuevo punto unico. **DO Load Balancer**: ata a DO sin aportar nada que Cloudflare no de |
| **Base de datos** | **DigitalOcean Managed MySQL**, misma region, VPC privada, **con nodo standby** desde el primer nivel vendible y **replica de lectura** desde el nivel 3 | RTT < 1 ms por VPC vs 80-150 ms remoto (cada pantalla son 10-15 consultas) · precio plano, no se dispara en las olas · respaldos diarios incluidos · failover automatico al standby en < 60 s | **PlanetScale** (se hablo en abril): remoto (80-150 ms por consulta), sin claves foraneas (Vitess) — el esquema las usa por todos lados —, precio por uso que se dispara justo cuando mas gente hay. Decidido en `DISPONIBILIDAD-HA.md` v2.0 y confirmado hoy |
| **Redis** | **DigitalOcean Managed Redis/Valkey, HA, TLS**, desde el primer nivel vendible | Con mas de una maquina, la cache de auth, las sesiones, el limitador y el adaptador de sockets TIENEN que ser compartidos · al cruzar la red lleva fichas de autenticacion → TLS · failover automatico | Redis dentro de un droplet: se cae con el |
| **Sockets** | Nodo(s) propio(s), **2 desde el primer nivel vendible** (redundancia, no capacidad) | Medido: 5.000 conexiones = 0-4% de CPU. Un nodo chico sostiene decenas de miles; el segundo existe para que si uno cae, el otro tenga a todos en < 30 s (E7 midio: 100% reconecta solo en 11 s) | — |
| **Colas (Horizon)** | En los nodos de sockets | Casi no consumen (push, correos, exports) | — |
| **Webapp (Next)** | Nodo(s) propio(s), **2 desde el primer nivel vendible** | Next es ~25% del CPU navegando; en su maquina no compite con Laravel. Sin estado (su cache del marco se invalida por aviso del backend a la LISTA de nodos: `WEBAPP_INTERNAL_URLS`) | — |
| **API (Laravel)** | Nodo(s) propio(s), **2 desde el primer nivel vendible**, es lo que se multiplica al crecer | Es el 60% del CPU. Sin estado (sesion y cache en Redis, archivos en R2): cada nodo que se suma es capacidad lineal | — |
| **Archivos** | **Cloudflare R2** con dominio propio | Ya hecho; sin egress; fuera de las maquinas | — |
| **Correo** | Resend | Ya hecho | — |
| **Errores / salud / alertas** | Sentry (ya) + Laravel Pulse + monitor externo con alerta (I.5) | Enterarse antes de que alguien se queje | — |
| **Respaldo fuera del proveedor** | Volcado cifrado a R2 por cron (I.4) + respaldos de la BD administrada + snapshots automaticos de DO | El unico respaldo hoy vive dentro de DO | — |
| **Deploy** | Blue-green: se saca un nodo del LB, se actualiza, se prueba, se mete; luego el otro | Cero corte en despliegues (`DISPONIBILIDAD-HA.md` §4) | — |
| **Region** | La mas cercana al publico. DO no tiene Sao Paulo: para Colombia, **nyc1** (~93 ms) | — | — |
| **Punto de operacion** | **50% de CPU en regimen por rol** | El codo llega antes del 60%: rompen las olas, no el promedio. Y el margen es lo que absorbe al nodo que se cae | — |

**Regla que sale de hoy y aplica a TODOS los niveles:** `TRUSTED_PROXIES` debe
incluir **los rangos de Cloudflare** y las IPs privadas de los nodos web; si no,
Laravel ve a todo el mundo como una sola IP y los limitadores por IP se agotan
entre todos (bug de `41b8040`, reaparecido el 2026-08-17 por un snapshot con
la IP vieja quemada).

---

## 2. NIVEL 0 — DEMO / QA — NO SE VENDE

1 droplet 4 vCPU / 8 GB con todo adentro (~$48-63). Es lo que existe hoy en
`killjoy.pro` y lo que `deploy.sh` monta. Sirve para demos, QA y para medir la
curva de una maquina. **MEDIDO 2026-08-17:** hasta ~300 activas plano (persona
301: TTFB 170-335 ms, CPU 41%) · 400 se siente · 480-500 al limite (CPU
69-73%) · 700 roto. Si muere, el evento muere. Por eso no se vende.

---

## 3. NIVEL 1 — BASICO — hasta ~300 personas activas — DERIVADO de lo medido

El combo minimo que **no sufre**: cada rol por duplicado, datos fuera.

| Pieza | Que | Tamaño | Cuantas | ~USD/mes |
|---|---|---|---|---|
| **API** | nginx + PHP-FPM (Laravel) | 2 vCPU / 4 GB | **2** | ~$48-64 |
| **Webapp** | Next (pm2) | 2 vCPU / 4 GB | **2** | ~$48-64 |
| **Sockets + colas** | 4 procesos socket + Horizon | 2 vCPU / 4 GB | **2** | ~$48-64 |
| **MySQL** | DO Managed MySQL **con standby** | 1-2 vCPU / 2-4 GB | 1 cluster | ~$60-120 |
| **Redis** | DO Managed Redis/Valkey HA, TLS | 1 GB | 1 | ~$15-30 |
| **Balanceador** | Cloudflare LB: `api.` → 2 API · `app.` → 2 web · `socket.` → 2 sockets | — | 1 | ~$5-10 |
| Cloudflare | DNS + proxy naranja + WAF | Free/Pro | — | $0-20 |
| R2 · Resend · Sentry · respaldos · monitor | | | | ~$10-30 |
| **Total: 6 droplets + 2 administrados** | | | | **~$240-400** |

**Por que 2 vCPU por nodo alcanza:** con 300 activas en UNA maquina de 4
nucleos, Laravel gastaba 1,0 nucleo, Next 0,4, MySQL 0,3. Repartido en dos
nodos por rol, cada API va a ~0,5 nucleo de 2 (25%): **si uno muere, el otro
queda al 50% — exactamente el punto de operacion.** Eso es "no sufrir": el
margen no es lujo, es el que absorbe la caida.

**Que se garantiza:** muere cualquier droplet → el LB lo saca en < 30 s y su
pareja atiende; nadie lo nota. Muere la BD primaria → standby en < 60 s. Muere
Redis → failover automatico, los sockets reconectan solos. Deploy sin corte.
**Cero datos perdidos, nadie tiene que hacer nada durante el evento.**

**Que hay que hacer para que sea MEDIDO (proxima sesion):** montarlo con
`deploy.sh --rol`, correr 300 por la webapp con Kamilo y Claude en Chrome como
las 301, y **tirar un droplet de cada rol a proposito** en plena carga: la
persona 301 no debe notarlo. Ese es el criterio de aceptacion.

---

## 4. NIVEL 2 — hasta ~1.000 activas — DERIVADO

Misma arquitectura; se engorda la API (es lo que escala) y la BD.

| Pieza | Tamaño | Cuantas | ~USD/mes |
|---|---|---|---|
| API | 4 vCPU / 8 GB | **3** (2 bastan al 50%; el tercero es el margen para perder uno) | ~$144-190 |
| Webapp | 4 vCPU / 8 GB | **2** | ~$96-126 |
| Sockets + colas | 2 vCPU / 4 GB | **2** | ~$48-64 |
| MySQL administrado con standby | 2 vCPU / 4 GB | 1 cluster | ~$100-120 |
| Redis administrado HA | 1 GB | 1 | ~$15-30 |
| Cloudflare LB + Pro | | | ~$25-30 |
| R2 · Resend · Sentry · respaldos · monitor | | | ~$10-40 |
| **Total: 7 droplets + 2 administrados** | | | **~$440-600** |

Derivacion: 1.000 al 50% → Laravel ~6,6 nucleos (3×4 = 12: al 55% con los
tres, al 83% si cae uno — aceptable por minutos), Next ~2,8 (2×4 ✓), MySQL
~1,8 (2 vCPU ✓).

---

## 5. NIVEL 3 — hasta ~2.500 activas — DERIVADO

| Pieza | Tamaño | Cuantas | ~USD/mes |
|---|---|---|---|
| API | 4 vCPU / 8 GB | **5-6** | ~$240-380 |
| Webapp | 4 vCPU / 8 GB | **2-3** | ~$96-190 |
| Sockets + colas | 2 vCPU / 4 GB | **2** | ~$48-64 |
| MySQL administrado, standby + **replica de lectura** (exports y Data Center leen de la replica) | 4 vCPU / 8 GB | 1 cluster | ~$240-300 |
| Redis administrado HA | 1-2 GB | 1 | ~$30-60 |
| Cloudflare LB + Pro | | | ~$25-30 |
| R2 · Resend · Sentry · respaldos · monitor | | | ~$10-40 |
| **Total: 9-11 droplets + 2 administrados** | | | **~$700-1.050** |

Derivacion: 2.500 al 50% → Laravel ~16,5 nucleos (5×4 = 20 ✓; 6 para perder
uno sin pasar del 65%), Next ~7 (2×4 = 8; 3 para margen), MySQL ~4,5.

---

## 6. NIVEL 4 — 5.000-10.000 activas — DERIVADO

| Pieza | Tamaño | Cuantas | ~USD/mes |
|---|---|---|---|
| API | 4 vCPU / 8 GB (o 8 vCPU / 16 GB, la mitad) | **9-12** | ~$430-760 |
| Webapp | 4 vCPU / 8 GB | **3-4** | ~$150-250 |
| Sockets + colas | 2-4 vCPU | **3** | ~$72-150 |
| MySQL administrado, standby + **2 replicas** | 8 vCPU / 16 GB | 1 cluster | ~$500-700 |
| Redis administrado HA | 2-4 GB | 1 | ~$60-100 |
| Cloudflare LB + Pro/Business | | | ~$50-250 |
| **Total: 15-19 droplets + 2 administrados** | | | **~$1.300-2.200** |

Preguntas que solo se responden midiendo: si conviene menos maquinas mas
grandes; si MySQL aguanta las ESCRITURAS de 10.000 personas (las lecturas van a
las replicas, las escrituras no); el techo de un balanceador de Cloudflare.

---

## 7. Lo que ya esta listo para esto (no hay que inventarlo)

- **WebSocket puro, sin afinidad de sesion** → cualquier reparto sirve.
- **Adaptador Redis entre procesos/nodos de socket** → probado con 4 procesos.
- **Cache de auth en Redis** → compartida entre nodos por diseño.
- **Archivos en R2** → cero estado en disco.
- **Webapp invalida su cache del marco por aviso del backend a una LISTA de
  URLs** (2026-08-17).
- **`deploy.sh` portable** (Ubuntu + apt, nada propio de DO).
- **E7 midio la caida de sockets:** 1.321 conectados, 100% reconecta solo en 11 s.

## 8. Lo que falta para MONTAR el nivel 1 (proxima sesion)

1. `deploy.sh --rol api|web|sockets|todo` (hoy monta "todo" en una).
2. Provisionar MySQL y Redis administrados en DO (VPC de la misma region).
3. `.env` por rol: `DB_HOST`/`REDIS_HOST` a los administrados (TLS),
   `SESSION_DRIVER=redis`, `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`,
   `WEBAPP_INTERNAL_URLS` con las 2 IPs privadas de web, `SOCKET_SERVER_URL`
   al LB de sockets, `TRUSTED_PROXIES` con rangos de Cloudflare + IPs privadas.
4. Cloudflare a naranja + Load Balancer con `api.`, `app.` y `socket.`.
5. Volcado horario cifrado a R2 (I.4) + monitor externo con alerta (I.5).
6. **Medir**: 300 por la webapp + Kamilo y Claude en Chrome + tirar un nodo de
   cada rol. Si la persona 301 no lo nota, el nivel 1 pasa a MEDIDO.

## 9. La respuesta corta, para cotizar

- **Demo**: 1 droplet. No se vende.
- **Hasta 300 a la vez**: 6 droplets chicos (2 API + 2 web + 2 sockets) + MySQL
  y Redis administrados + Cloudflare LB → **~$240-400/mes**. Nada se cae.
- **Hasta 1.000**: 7 droplets → **~$440-600/mes**.
- **Hasta 2.500**: 9-11 droplets + replica → **~$700-1.050/mes**.
- **5.000-10.000**: 15-19 droplets → **~$1.300-2.200/mes**. Medir antes.

"A la vez" = navegando activamente al ritmo del script (una pantalla cada
20-40 s). En un evento real la gente mira menos seguido, asi que caben mas
inscritos por nivel — cuantos mas, se sabra al instrumentar el ritmo real (I.2).
