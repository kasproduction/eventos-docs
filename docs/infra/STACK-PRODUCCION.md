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
| **Quien balancea** | **Cloudflare Load Balancer** (round-robin + health check cada 10 s + failover) | Sin punto unico de falla (no vive en nuestras maquinas) · portable (balancea IPs de CUALQUIER proveedor) · soporta WebSocket · WAF/DDoS/CDN incluidos · **~$25-40/mes con 6 origenes** ($5 base + $5 por origen extra + $10-15 por chequeos de 30/15 s; corregido 2026-08-18, ver 3.3) | **nginx propio round-robin**: una maquina mas y el nuevo punto unico. **DO Load Balancer**: ata a DO sin aportar nada que Cloudflare no de |
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

## 3. NIVEL 1 — BASICO — hasta ~300 personas activas — MONTADO Y MEDIDO 2026-08-17/18

El combo minimo que **no sufre**: cada rol por duplicado, datos fuera.

| Pieza | Que | Tamaño | Cuantas | ~USD/mes |
|---|---|---|---|---|
| **API** | nginx + PHP-FPM (Laravel) | **4 vCPU / 8 GB** (medido: 2 vCPU NO absorbe la caida de su pareja) | **2** | ~$96-128 |
| **Webapp** | Next (pm2) | 2 vCPU / 4 GB | **2** | ~$48-64 |
| **Sockets + colas** | 4 procesos socket + Horizon + scheduler | 2 vCPU / 4 GB | **2** | ~$48-64 |
| **MySQL** | DO Managed MySQL 8.4 **con standby** (2 nodos) | 1 vCPU / 2 GB | 1 cluster | ~$60 |
| **Redis** | DO Managed Valkey 8 HA TLS (2 nodos) | 1 vCPU / 2 GB | 1 | ~$30 |
| **Balanceador** | Cloudflare LB: `api.` → 2 API · `app.` → 2 web · `socket.` → 2 sockets | — | 1 | **~$25-40** (ver 3.3, corregido) |
| Cloudflare | DNS + proxy naranja + WAF | Free/Pro | — | $0-20 |
| R2 · Resend · Sentry · respaldos · monitor | | | | ~$10-30 |
| **Total: 6 droplets + 2 administrados** | | | | **~$320-440** |

### 3.1 Lo que se midio (2026-08-17/18: 300 sinteticos por la webapp, 10 min, apagones a proposito)

Montado tal cual con `deploy.sh ROL=api|web|sockets`, MySQL y Valkey
administrados en la VPC nyc1, certificado de origen de Cloudflare en los 6
nodos, 443 abierto solo a Cloudflare + VPC. **Reparto con nginx round-robin
en un droplet aparte** (el complemento de Cloudflare LB no se compro: ver 3.3).

| Minuto | Que pasaba | p50 pantalla | p95 | Fallos |
|---|---|---|---|---|
| 1,0-2,0 | todo arriba, caches calentando | **300-360 ms** | 0,7-1,3 s | 0 |
| 2,5-4,5 | **api-1 apagada de golpe** (power-off) | 2,5 → **8-10 s** | 6-12 s | **0** |
| 5,5-7,5 | api-1 volvio sola · **web-1 apagada de golpe** | **290-420 ms** | 0,5-1,5 s | **0** |
| 8,0 | web-1 volviendo fria · sock-1 apagada | 620 ms | 5 s | 0 |
| 8,5-10 | todo arriba | **340-420 ms** | 0,7-2,2 s | 0 |

**5.639 pantallas, 0 fallidas, 300/300 ingresos, con tres apagones.** Los nodos
apagados **volvieron solos al reparto** al encenderlos (nginx + FPM/pm2 arrancan
con el sistema; el proxy los reintenta a los 10 s).

- **Web: caida INVISIBLE.** web-2 sola (carga 1,0) sostuvo los 300 igual.
- **API: caida SIN ERRORES pero LENTA.** api-2 sola subio a carga 18-25 sobre
  2 vCPU y la pantalla a 8-10 s. **Por eso el nodo API pasa a 4 vCPU:** con
  300 cada API de 2 vCPU iba a carga ~4-6 (no al 25% que suponia la
  aritmetica del droplet unico) y no tiene margen para absorber a la pareja.
- **Sockets: caida INVISIBLE, medida con la persona 301 (Kamilo) dentro de una
  sesion en vivo** con 200 personas en la sala y 40 chateando (E4). Se apago
  de golpe el nodo donde estaba su conexion (21:59:18): el proxy dejo de
  mandarle a los 0 s, su navegador se reengancho solo al otro nodo en < 10 s,
  **el chat siguio moviendose y no vio nada.** (Los sinteticos de E4 no
  reconectan a proposito — `reconnection: false` — asi que la mitad de la sala
  se fue con el nodo; eso es el script, no la plataforma.) Antes, con 300
  navegando y sin sesion, la misma prueba: 0 fallos y reenganche en < 10 s.
- **BD administrada / Redis administrado: failover no medido** (no hay "tirar
  el primario" desde la API de DO). Es garantia del proveedor, no medida nuestra.

### 3.2 Los tres cuellos que NO existian en el droplet unico (y su correccion)

Aparecen al sacar los datos a servicios administrados **por TLS**. Los tres
estan corregidos en codigo y son la razon de que la corrida 1 (p50 660 ms,
APIs a carga 8-10) se volviera la corrida 2 (p50 300 ms, carga 4-6):

1. **phpredis + TLS se cuelga.** ~0,5% de las conexiones nuevas se quedan
   esperando la respuesta al `AUTH` para siempre: el timeout de PHP no aplica
   al handshake TLS. Con 300 personas eso eran logins de 120 s y 504.
   Verificado que el servidor no era (500/500 conexiones crudas responden).
   **Correccion: `REDIS_CLIENT=predis`** (0 colgados en 400 logins) +
   `persistent`/`persistent_id` por conexion en `config/database.php`.
2. **Un handshake TLS a Redis por peticion.** El Valkey de 1 vCPU termina ~25
   handshakes/s (1 solo = 78 ms; 60 a la vez = 2,5 s cada uno) y PHP abria
   2-3 conexiones por peticion. **Correccion: conexiones persistentes**
   (`REDIS_PERSISTENT=true`): un handshake por worker de FPM, no por peticion.
3. **Una conexion MySQL TLS por peticion:** conectar cuesta 40-70 ms
   (handshake + `caching_sha2`) contra 1-3 ms la consulta. **Correccion:
   `DB_PERSISTENT=true`** (`PDO::ATTR_PERSISTENT`). Cuenta de conexiones:
   28 workers x 2 APIs + Horizon ≈ 70 < 151 (`max_connections` del plan 2 GB).

Y dos mas de operacion: **`onOneServer()` en las 13 tareas del scheduler**
(corre en los 2 nodos de sockets sin duplicar) y **el socket acepta
`REDIS_TLS=true` + `REDIS_USERNAME`**.

### 3.3 El balanceador: nginx propio vs Cloudflare LB — decidido con numeros

Lo que se uso para MEDIR fue **nginx round-robin en un droplet aparte**
(`docs/infra/lb-nginx.conf`: tres `upstream` por rol sobre la VPC, TLS con el
certificado de origen, `max_fails=2 fail_timeout=10s`, `proxy_next_upstream`
para reintentar la misma peticion en el otro nodo, WebSocket con
`proxy_read_timeout 3600`). Funciono perfecto: saco al nodo caido a los 0 s
(la peticion en curso se reintento en la pareja) y lo volvio a meter solo.

| | nginx propio (1 droplet 2 vCPU) | Cloudflare Load Balancer |
|---|---|---|
| Costo | ~$24/mes | $5 base + $5/origen extra (6 → $25) + $10/15 por chequeo 30/15 s → **~$25-40/mes** |
| Reparto | round-robin real, por peticion | por peticion, con pesos y geo |
| Deteccion de caida | **inmediata** (pasiva: la peticion falla → siguiente nodo, y `fail_timeout` lo saca 10 s) | activa: chequeo cada 60 s gratis (2-3 min para sacar un nodo) / 15 s pagando |
| Punto unico | **SI: si muere el droplet del proxy, muere todo** | no (vive en la red de Cloudflare) |
| WebSocket | si | si |
| WAF / DDoS / TLS de borde | no (se pone Cloudflare naranja delante igual) | incluido |
| Operacion | un config file mas, un droplet mas que mantener | clic |

**Decision:** para **medir y para demos**, nginx (gratis, inmediato, ya
escrito). Para **vender**, el principio manda — "nada corre en un solo lugar" —
y un nginx solo es exactamente el punto unico que el diseño evita. Opciones
que respetan el principio, de menor a mayor costo:

1. **Cloudflare LB con chequeo de 60 s** (~$25/mes): sin punto unico, pero un
   nodo caido tarda 2-3 min en salir — la 301 lo nota. Aceptable si ademas el
   nodo web/API reintenta (Next ya lo hace en parte).
2. **Cloudflare LB con chequeo de 15 s** (~$40/mes): < 45 s. Es lo que
   corresponde al Nivel 1 vendible. Se compra con el primer cliente.
3. **Dos nginx con IP flotante de DO** (~$48/mes + complejidad de keepalived):
   inmediato y sin punto unico, pero es una pieza mas que operar. Solo si el
   costo del LB de Cloudflare crece con los niveles altos (12+ origenes).

Y una regla que aplica a las tres: **el proxy/LB debe reintentar la peticion
en el otro nodo** (`proxy_next_upstream` en nginx; en Cloudflare, "retry on
origin error" del pool), porque es lo que hace que la caida sea INVISIBLE y no
solo "corta".

### 3.3b Cloudflare: dos correcciones a lo que decia este documento

- **Dos registros A naranja NO reparten.** La primera corrida de 300 se fue
  entera a api-2 y web-2 (api-1: 0 peticiones). Cloudflare reutiliza la
  conexion al mismo origen. Sin balanceador de verdad no hay Nivel 1.
- **El Load Balancer de Cloudflare cuesta mas de lo escrito aqui.** $5/mes
  base incluye **2 origenes en total** (no 2 por pool); cada origen extra
  **$5/mes**; chequeo cada 60 s gratis, **cada 30 s +$10, cada 15 s +$15**;
  con 6 origenes y chequeo de 15 s son **~$40/mes**. Con 60 s y 2 reintentos
  un nodo caido tarda 2-3 min en salir del reparto (la 301 lo notaria). Se
  decide al vender el primer Nivel 1; para medir se uso nginx round-robin
  (`docs/infra/lb-nginx.conf`) en un droplet aparte — que ES el punto unico
  que el diseño evita, y por eso no vale para produccion.

### 3.4 Como quedo la infraestructura al cierre del 2026-08-18

Todo con etiqueta `eventos-n1` en DigitalOcean nyc1, VPC `default-nyc1`
(10.116.0.0/20). **Kamilo decide si se destruye o se deja** (ver NEXT-SESSION):

| Nodo | Rol | Publica | Privada | Que corre |
|---|---|---|---|---|
| api-1 | API | 157.245.130.36 | 10.116.0.14 | nginx 443 (origin cert) + PHP-FPM 28 hijos, predis+PDO persistentes |
| api-2 | API | 68.183.110.206 | 10.116.0.15 | idem |
| web-1 | web | 142.93.57.247 | 10.116.0.16 | nginx 443 → Next pm2 :3000 (escucha en 0.0.0.0 para la invalidacion) |
| web-2 | web | 142.93.203.86 | 10.116.0.17 | idem |
| sock-1 | sockets | 159.223.143.80 | 10.116.0.19 | nginx 443 → socket pm2 (2 procesos) + Horizon (default + heavy) + cron scheduler |
| sock-2 | sockets | 165.22.2.226 | 10.116.0.18 | idem |
| eventos-semilla | proxy + carga | 157.245.81.59 | 10.116.0.5 | nginx round-robin (`lb-nginx.conf`) + `tests/load` (240 GB, del snapshot) |
| eventos-n1-mysql | BD | — | `private-eventos-n1-mysql-…` :25060 | MySQL 8.4, 2 nodos, `eventos_db`, usuario `eventos_user`, TLS |
| eventos-n1-redis | Redis | — | `private-eventos-n1-redis-…` :25061 | Valkey 8, 2 nodos, TLS, BD 0/1/2 |

DNS (Cloudflare, naranja): `api/app/socket.killjoy.pro` → 157.245.81.59
(proxy). `api-2/app-2/socket-2.killjoy.pro` → nodo 2 directo (naranja).
Cloudflare: complemento Load Balancing base activo ($5/mes, sin usar) con 3
monitores y 1 pool `eventos-api` de prueba — cancelar o usar. Costo del stack
encendido: **~$0,40/h ≈ $10/dia**.

Secretos que quedaron en el chat de esta sesion y hay que **rotar**: token de
API de DigitalOcean, token `eventos-nivel1` de Cloudflare, token R2 y clave de
Resend que Kamilo pego por error.

**Que se garantiza (medido salvo lo marcado):** muere cualquier droplet de web
o sockets → su pareja atiende y nadie lo nota (web y sockets MEDIDOS). Muere una API → sin errores, y sin lentitud si son de 4 vCPU
(MEDIDO con 2 vCPU: lento). Muere la BD primaria → standby (garantia DO).
Muere Redis → failover (garantia DO). Deploy sin corte (blue-green, por hacer).

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

## 8. Como se monto el nivel 1 (hecho 2026-08-17/18)

1. `deploy.sh ROL=api|web|sockets` (ROL=todo sigue siendo el droplet demo).
   Antes de correrlo, en `/root/`: `origin.pem`+`origin.key` (certificado de
   origen de Cloudflare, `*.dominio`, 15 años) y `do-ca.crt` (CA de la BD).
2. MySQL 8.4 (2 nodos) y Valkey 8 (2 nodos) administrados en la VPC de la
   region, cortafuegos de ambos = etiqueta `eventos-n1` de los droplets.
3. `.env` por rol (ver el bloque NIVEL 1 al final de `deploy.sh`):
   `REDIS_CLIENT=predis`, `REDIS_HOST=tls://…`, `REDIS_PERSISTENT=true`,
   `DB_PERSISTENT=true`, `MYSQL_ATTR_SSL_CA`, `TRUSTED_PROXIES` = rangos de
   Cloudflare + IPs publicas de los web + `10.0.0.0/8`,
   `WEBAPP_INTERNAL_URLS` = las 2 IPs privadas de web.
4. Codigo: `rsync` desde un nodo ya construido por la VPC (Next tarda 5+ min
   en compilar por nodo; copiarlo es segundos) y `config:cache` en cada uno.
5. DNS `api/app/socket` naranja → balanceador. **Cloudflare LB (comprar
   origenes) o, para medir, nginx round-robin** (`lb-nginx.conf`).
6. Medido: ver 3.1 (web, sockets y API). **Pendiente:** repetir la caida de
   API con nodos de 4 vCPU para confirmar que ademas de no fallar no se siente.

## 9. La respuesta corta, para cotizar

- **Demo**: 1 droplet. No se vende.
- **Hasta 300 a la vez**: 6 droplets (2 API de 4 vCPU + 2 web + 2 sockets) +
  MySQL y Redis administrados + Cloudflare LB → **~$320-440/mes**. MEDIDO
  2026-08-18: 300 personas, tres apagones, 0 errores (3.1).
- **Hasta 1.000**: 7 droplets → **~$440-600/mes**.
- **Hasta 2.500**: 9-11 droplets + replica → **~$700-1.050/mes**.
- **5.000-10.000**: 15-19 droplets → **~$1.300-2.200/mes**. Medir antes.

"A la vez" = navegando activamente al ritmo del script (una pantalla cada
20-40 s). En un evento real la gente mira menos seguido, asi que caben mas
inscritos por nivel — cuantos mas, se sabra al instrumentar el ritmo real (I.2).
