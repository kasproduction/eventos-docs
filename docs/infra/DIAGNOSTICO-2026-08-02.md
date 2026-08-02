# Diagnóstico completo — EventOS en producción real

> **2026-08-01 / 02.** Primera vez que este producto sale de desarrollo.
> Se montó el stack completo, se ejecutaron los 8 escenarios de experiencia y
> se corrigió todo lo que apareció. Este documento es el resultado.
>
> Guía de despliegue ejecutable: `docs/infra/deploy.sh`
> Plan de escenarios: `docs/infra/PLAN-EXPERIENCIA-ENTERPRISE.md`

---

# 1. Qué se montó

| | |
|---|---|
| **Servidor** | DigitalOcean **nyc1 (Nueva York)** · 4 vCPU / 8 GB · Ubuntu 24.04 LTS |
| **Generador de carga** | Idéntico, máquina aparte |
| **Dominio** | killjoy.pro · HTTPS con Let's Encrypt · **Cloudflare en gris** (sin proxy) |
| **Stack** | nginx 1.24 · PHP 8.3-FPM · MySQL 8 · Redis 7 · Node 20 · PM2 (4 procesos) · Horizon |
| **Almacenamiento** | Cloudflare R2, dominio propio `cdn.killjoy.pro`, región ENAM |
| **Correo** | Resend por SMTP, dominio verificado |
| **Datos** | Evento demo + 5.050 asistentes reales + 120 archivos generados |

### Corrección importante de documentación

`DISPONIBILIDAD-HA.md` y `PLAN-STRESS-TESTDO.md` dicen **"DO sao1 consolidado"**
y justifican la elección con *"RTT Bogotá ~80 ms vs ~150 ms"*.

**DigitalOcean no tiene región en São Paulo.** Toda esa justificación se apoya
en una región que no existe. El servidor está en **nyc1**.

Consecuencia útil: el bucket R2 quedó en ENAM (Norteamérica Este), o sea **en
la misma región que el servidor**. Sin querer, quedó óptimo.

---

# 2. El dato que cambia todo: la latencia real

**Todas las mediciones de carga se hicieron dentro del mismo centro de datos.**
Eso es correcto para saturar el servidor, pero NO es lo que siente un asistente.

Medido desde Colombia contra Nueva York:

| | |
|---|---|
| Ida y vuelta de red (TCP) | **~100 ms** |
| Con negociación TLS | ~200 ms |
| Petición completa en frío | ~345 ms |

**La experiencia real de alguien en Bogotá es: nuestros números + ~100 ms.**

| Pantalla | Medido (mismo datacenter) | Real en Bogotá |
|---|---|---|
| Abrir la app | 118 ms | **~220 ms** |
| Agenda | 61 ms | ~160 ms |
| Sponsors / ranking / muro | 27-30 ms | ~130 ms |

Sigue siendo bueno — bajo el umbral de 300 ms — pero hay que decirlo así y no
vender los 27 ms.

---

# 3. Capacidad medida

## Los números

| | |
|---|---|
| **Sockets simultáneos** | **5.000** con 0 fallos |
| **HTTP sostenido** | **~70 peticiones/segundo** |
| **Cuello de botella** | **CPU** (0% libre con 6,1 GB de RAM sin usar) |
| Costo fijo por petición | **~45 ms** de framework (una ruta vacía cuesta casi lo mismo que una con consulta) |
| Abrir la app | **8 peticiones** |
| Estar quieto con la app abierta | **0 peticiones** |

## La fórmula, con datos reales

```
techo             ~70 peticiones/segundo
abrir la app       8 peticiones
estar quieto       0 peticiones
```

**→ ~8,7 personas por segundo entrando sin degradar**
**→ ~525 personas por minuto**
**→ 5.000 personas entran cómodas en ~10 minutos**

Frase vendible y honesta: *"si su evento abre puertas 10 minutos antes, 5.000
personas entran sin sentir nada"*. Si abren todos de golpe en 3 minutos, hay
cola.

## Qué aguanta hoy, en una línea

**Un droplet de $48/mes: eventos de 1.000 a 5.000 asistentes, con margen.**

---

# 4. Los 8 escenarios — resultado final

| | Escenario | Resultado |
|---|---|---|
| **E1** | Se abren las puertas | **VERDE** · 1.000/1.000 sockets, 8.000 peticiones, 0 fallos |
| **E2** | El anuncio | **VERDE** · 238 bytes en vez de 3.000 peticiones |
| **E3** | La agenda cambia | **VERDE** · **52,5 s → 0,20 s** |
| **E4** | El chat de la magistral | **VERDE** · **631 ms → 30 ms** |
| **E5** | La foto viral | **VERDE** · 75 fotos, 742 ms hasta R2, 0 fallos |
| **E6** | Control (línea base) | **VERDE** · 27-118 ms por pantalla |
| **E7** | Se cae algo | **VERDE** · **100% recupera solo en ~11 s** |
| **E8** | El organizador trabajando | **VERDE** · el asistente ni se entera (3-4 ms) |

**Todos empezaron en rojo o destaparon algo. Ninguno pidió rediseño.**

---

# 5. Todo lo que se encontró y se corrigió

## Bugs de producto — habrían llegado a un cliente

**1. El chat NO guardaba nada** · `eventos-socket/chat.ts`
Se enviaron 4.241 mensajes y la tabla quedó en **0 filas**. `postToLaravel`
hablaba HTTP plano contra el puerto 80 cuando la API es HTTPS en el 443, y el
envío era fire-and-forget con el error silenciado. **Ni una señal.** En
desarrollo funcionaba porque ahí la API sí es HTTP.
*Impacto real: cero historial, export del Data Center vacío, moderación sin
nada que mostrar.*

**2. El magic link quedaba mudo** · 3 fallas encadenadas
El seeder de la plantilla no estaba en `DatabaseSeeder`; sin plantilla, el
trabajo de correo no envía **y no registra nada**; y faltaba `WEBAPP_URL`, así
que el enlace apuntaba a la API en vez de a la app.
*Impacto: la única puerta de entrada del asistente, muerta en una instalación
nueva. Endpoint 200, token creado, correo inexistente.*

**3. El ranking rompía en MySQL 8** · `GamificationController`
500 en el **86%** de las peticiones. `only_full_group_by` viene activado en
MySQL 8 y apagado en Laragon. Y la rama que falla **solo corre si el asistente
NO está en el top 50** — con 50 asistentes de seed, nadie la ejecutaba nunca.
*Lección transferible: un seed chico esconde ramas enteras de código.*

**4. El backend no se podía instalar** · `bootstrap/providers.php`
`composer install --no-dev` reventaba: Telescope es dependencia de desarrollo
pero su provider estaba registrado siempre.

**5. HTMLPurifier sin su directorio** · guardar cualquier contenido con HTML
fallaba en un servidor recién montado.

**6. La moderación llegaba al 25%** · regresión del cluster
Al pasar a 4 procesos, el modo lento y las palabras bloqueadas siguieron
viviendo en la memoria de cada proceso. La orden del admin llega por HTTP a
**uno solo**. Bloquear una palabra ofensiva dejaba que el 75% la siguiera
viendo hasta 5 minutos.

**7. Un log que mentía** · al verificar lo anterior
Los 4 procesos escribían "suscrito" pero Redis reportaba **2 suscriptores
reales**. Tres causas: el único cliente Redis sin manejador de errores, se
suscribía antes de registrar el manejador, y no se re-suscribía al reconectar.

## Cuellos de botella de infraestructura

**8. La estampida de autenticación**
Cada conexión de socket disparaba **una petición HTTP completa** a `/auth/me`
(~50 ms de CPU). Verificado: 1.468 conexiones = 1.468 peticiones, pico de 76/s
— justo el techo de la máquina. 5.000 llegando juntos = ~250 s de CPU solo
para abrir la puerta.
→ Laravel deja la ficha en Redis al emitir el token. **0 peticiones.**

**9. `worker_connections 768`**
El default de Ubuntu. Cada WebSocket con proxy consume **dos** conexiones de
nginx: 4 workers × 768 ÷ 2 = **1.536**. Medimos 1.468 y 1.493.
*El techo no era el hardware ni el código: era una línea que nadie tocó.*
→ Solo eso llevó de 1.468 a 2.500.

**10. El TTL fijo de la caché de auth**
Duraba 15 minutos desde la emisión: a los 15 min había 0 fichas y **la
estampida volvía**. 832 de 1.500 conexiones perdidas.
→ Ventana deslizante con tope absoluto de 24 h.

**11. El reparto saturando un solo hilo**
Con 1.000 en una sala, cada mensaje bloqueaba el hilo 25 ms. A 40 mensajes/s
el servidor necesitaba **1.000 ms de trabajo por cada segundo**. La cola crecía
sola: la espera iba de 98 ms a 475 ms.
→ Un proceso por núcleo + WebSocket puro. **631 ms → 30 ms.**

**12. El comentario propio contaba doble** · el broadcast no decía quién
comentó, y el socket sale ANTES de responder el POST, así que deduplicar por
id llegaba tarde.

## Bugs que SOLO aparecieron abriendo el navegador

> Ocho defectos que ninguna prueba de carga ni revision de codigo encontro.
> Los 8 escenarios pasaron en verde mientras estos estaban vivos.

**13. El magic link llevaba a un 404.** El backend armaba `/auth/verify`; la
ruta real es `/verify` — en la webapp la carpeta se llama `(auth)` y los
parentesis son un GRUPO de Next: organizan archivos, no aparecen en la URL.

**14. El login con contraseña devolvia 500.** `env()` entrega TEXTO y nadie
convertia `SANCTUM_TOKEN_EXPIRATION` a numero: `addMinutes("10080")` hacia
fallar a Carbon. En desarrollo la variable no esta definida y se usaba el
default (entero), por eso nunca se vio. Y el magic link emite su token SIN
expiracion — un camino tapaba al otro.

**15. Un servidor lento EXPULSABA a la gente.** `getMe()` devolvia null ante
cualquier fallo que no fuera 401, y todas las paginas hacen
`if (!user) redirect('/login')`. O sea que "no pude preguntarle al backend" se
trataba igual que "no tenes sesion". Con 5.000 navegando, abrir el perfil
cerraba la sesion. Y se retroalimenta: los expulsados vuelven todos juntos y
agravan el pico que los echo.

**16. Los tableros daban 403.** nginx con `index index.php` a secas intenta
listar el directorio: Event Pulse y Mission Control son carpetas con
index.html. Data Center no lo sufria porque tiene ruta propia en Laravel —
por eso el fallo era desparejo y confundia.

**17. TODO el admin estaba caido.** `Vite manifest not found`. El backend
tiene su propio package.json para el tema de Filament y el despliegue nunca
corria `pnpm run build`. El admin completo y el Data Center —que vive detras
de su login— inaccesibles. La API respondia perfecto mientras tanto.

**18. Las acciones masivas no avisaban nada.** Aprobar 82 publicaciones
funcionaba, pero sin notificacion y con los contadores viejos hasta recargar.
Lo unico que se movia era la seleccion limpiandose — y eso se ve IGUAL cuando
funciona que cuando falla. **Una accion que funciona en silencio es
indistinguible de una que falla.**

**19. Los premios no se veian.** Sembrados y habilitados, pero el evento tenia
`rewards_enabled` apagado: el endpoint devolvia lista vacia sin explicar nada.

**20. La caché de auth vence y la estampida vuelve.** La ventana deslizante
solo se renueva al leerse. Con 5.000 llegando de golpe contra caché frio
—"todos se registraron anoche"— reaparecio: 832 de 1.500 conexiones perdidas
por timeout. **Pendiente de resolver antes de un evento real.**

## Optimizaciones aplicadas

**OPcache con JIT + cachés de Laravel** — pasos de producción que faltaban:
mediana 564 → **363 ms**, caudal 47,5 → **59,6 req/s**. **+25% gratis.**

## Defectos del arnés de medición (daban números falsos)

- Los tokens se repartían mal: cada usuario virtual usaba **siempre la misma
  cuenta**. Como el límite es **por usuario** (60/min), el test medía su propia
  agresividad en forma de 429.
- `stress-local` le pegaba a `/banners`, feature eliminada en julio: 404 en el
  100%, inflando el error ~10 puntos.
- `stress-full` **no cabe en 8 GB**: k6 reserva ~3,3 MB por usuario virtual y
  sus 2.400 piden ~8 GB solo de arranque. El kernel lo mataba antes de la
  primera petición.
- El motor de sockets de artillery **no manda el token**: reportaba miles de
  usuarios creados mientras el servidor rechazaba todo.
- `stress-admin` apuntaba a `/admin/events/{id}/attendees`, ruta inexistente.
- Y buscaba la cuenta de organizador **muestreando 20 tokens** — lo que dejó
  de funcionar con nuestro propio arreglo del reparto: con UN admin entre
  5.050, la probabilidad de hallarlo en 20 intentos es del 0,4%.
- El canario **se auto-limitaba**: navegaba cada 500 ms = 240 peticiones/minuto
  contra un límite de 60.

---

# 6. Buenas prácticas — lo que este ejercicio enseñó

## Lo que SÍ hay que hacer

**Medir con un canario.** En cada escenario, un usuario solo cuya experiencia
se mide **aparte del promedio**. Los promedios mienten: con 5.000 usuarios un
p95 bonito puede esconder 250 personas pasándola pésimo. En E3 la mediana del
canario no se movió (80 → 72 ms) mientras su p95 saltaba a **29 segundos**.

**Perfilar antes de optimizar.** En E4 diagnostiqué mal dos veces seguidas.
Las marcas de tiempo por paso dieron la respuesta en una línea:
`config=0ms limite=4ms espera_throttle=456ms emit=25ms`.

**Verificar si el dato es igual para todos ANTES de repartirlo.** Apareció dos
veces: `is_favorite` en la agenda (mandar la agenda completa le habría pisado
los favoritos a 3.000 personas con los del admin) y `roles` en los anuncios
(repartir uno segmentado sería una fuga, no lentitud).

**Mandar el dato con el aviso.** El patrón que resolvió E2 y E3: en vez de
avisar "algo cambió" y que 3.000 personas lo pidan, mandar lo que cambió.
**1.272 bytes reemplazaron 3.000 peticiones HTTP.**

**Mandar la pieza, no el conjunto.** La sesión pesa 1,2 KB, la agenda 34 KB.

**Usar los núcleos que ya se pagan.** El servidor tenía 3 de 4 ociosos.

**Que las fallas griten.** Casi todos los bugs graves eran **silenciosos**.
Ahora los correos esenciales sin plantilla y la persistencia de chat fallida
se registran.

## Lo que NO hay que hacer

**No confiar en el log.** Cuatro procesos escribían "suscrito" y solo dos lo
estaban. Verificar contra la fuente (`PUBSUB NUMSUB`), no contra lo que el
código dice de sí mismo.

**No usar `ip_hash` para repartir sockets en un evento.** Es la receta estándar
de Socket.IO **y falla justo acá**: todos están detrás del wifi del recinto, o
sea **una IP pública**. Los 1.000 caerían en el mismo proceso.

**No dejar `fire-and-forget` sin registro.** Costó 4.241 mensajes perdidos sin
una sola señal.

**No asumir que desarrollo y producción se parecen.** HTTP vs HTTPS ocultó la
pérdida del chat. `only_full_group_by` ocultó el ranking roto. El seed de 50
personas ocultó una rama entera.

**No medir con el generador en la misma máquina.** Y no confundir la
saturación del generador con la del servidor: en E4 el canario vivía en el
proceso que procesaba 40.000 eventos/segundo.

**No dejar que el arnés de pruebas envejezca.** Seis defectos distintos, todos
reportando como falla del servidor lo que era del test.

**No poner TTL largo por ahorrar.** La tentación era 7 días; habría hecho que
una invalidación fallida durara una semana en vez de 15 minutos.

---

# 7. Infraestructura: cómo crece

> **Desarrollado en detalle en `docs/infra/COMO-CRECEMOS.md`** — con las cuatro
> etapas, qué cambia en cada una, costos reales y por qué el plan viejo de
> "dos droplets iguales" NO es el primer paso.

## Hoy — un droplet por evento (decisión firme)

```
                   Internet
                      │
              ┌───────┴───────┐
              │   Cloudflare   │   ← hoy en GRIS (sin proxy)
              └───────┬───────┘
                      │
         ┌────────────┴────────────┐
         │   Droplet · nyc1        │
         │   4 vCPU / 8 GB · $48   │
         │  ─────────────────────  │
         │  nginx                  │
         │  PHP-FPM (60 hijos)     │
         │  Node × 4 (cluster)     │
         │  MySQL · Redis          │
         │  Horizon                │
         └────────────┬────────────┘
                      │
              ┌───────┴───────┐
              │ Cloudflare R2 │  cdn.killjoy.pro
              └───────────────┘
```

**Aislamiento total: si cae el VPS de un cliente, no arrastra a los demás.**
Es la decisión de arquitectura ya tomada y los números la respaldan — un
droplet cubre hasta 5.000 personas.

## Paso 1 — antes de exponerlo (barato, hacer ya)

**Cloudflare en naranja.** Hoy está en gris y el servidor está desnudo: ya se
vieron 125 intentos de bots buscando `/.env` desde 7 IPs distintas. El proxy da
protección contra ataques, filtro de aplicación y caché de estáticos. **Gratis.**

*Se dejó en gris a propósito para medir el servidor y no a Cloudflare.*

## Paso 2 — más capacidad en la misma máquina (gratis)

**Atacar los 45 ms de costo fijo por petición.** Es el techo real: una ruta
vacía cuesta casi lo mismo que una con consulta, o sea que el trabajo de la app
no es el problema. Lo esperable serían 15-25 ms.

**Bajarlo a la mitad duplica todo** — capacidad de llegada y de navegación —
**sin comprar hardware**. Es la palanca más rentable que queda.

## Paso 3 — dos droplets con failover (~$150-200/mes)

Solo si un cliente lo exige. Requiere separar el estado de la aplicación:

```
                   Internet
                      │
         ┌────────────┴────────────┐
         │  Cloudflare Load Balancer│
         │  health checks cada 10s  │
         │  failover automático     │
         └──────┬────────────┬──────┘
                │            │
        ┌───────┴──┐    ┌────┴─────┐
        │ Droplet-1│    │ Droplet-2│   ← si uno muere, el otro sigue
        │ app only │    │ app only │
        └───────┬──┘    └────┬─────┘
                │            │
         ┌──────┴────────────┴──────┐
         │  MySQL administrado (+réplica)│
         │  Redis administrado (HA)      │
         └───────────────────────────────┘
```

**Qué hace falta cambiar** (no es solo comprar máquinas):

1. **MySQL y Redis salen del droplet** a servicios administrados. Hoy están
   dentro; con dos máquinas cada una tendría su propia base y no funcionaría.
2. **Redis administrado con TLS.** Hoy escucha en localhost; al cruzar la red
   lleva datos de sesión y las fichas de autenticación.
3. **El adaptador de Redis ya está puesto** — un mensaje emitido en un droplet
   llega a los suscriptores del otro. Esa parte ya funciona.
4. **WebSocket puro ya resuelve la afinidad** — cualquier balanceo sirve, no
   hace falta pegar cada persona a una máquina.
5. **Los archivos ya están fuera** (R2), así que no hay estado en disco.

**Round-robin vs failover:** el balanceador de Cloudflare hace las dos cosas.
Reparte por turnos entre orígenes sanos y saca de rotación al que falle dos
chequeos seguidos (~30 segundos).

**El dato que E7 aporta:** cuando un droplet caiga, sus usuarios reconectan al
otro. Medimos que **1.321 personas reconectaron solas en ~11 segundos sin
tumbar nada** — y eso fue con un solo servidor recibiendo la avalancha entera.
Con dos, la mitad ni se entera.

## Cuándo NO crecer

**Para 5.000 personas no hace falta.** Un droplet lo hace. Sumar máquinas antes
de atacar los 45 ms es pagar por no optimizar.

Y sobre los cinco nueves: **99,999% son 26 segundos de caída al año**, exigen
multi-región y protegen las horas en que no hay nadie usando la app. Para una
plataforma de eventos la métrica correcta es **cero degradación durante la
ventana del evento** — alcanzable, verificable, y lo que un cliente compra.

---

# 8. Qué falta — sin adornos

## Bloqueante antes de un cliente pagando

**QA de cliente: cero.** Se verificó que el servidor manda lo correcto. Que el
Expo y la webapp lo **pinten** bien no lo miró nadie. Incluye todo lo de hoy:
el parche de agenda, el de anuncios, el chat.

**El recorrido completo de una persona, nunca probado.** Entrar, moverse,
participar, salir. Probamos piezas, no la experiencia.

**Seguridad del staff (2FA) sin empezar.** Decisión ya tomada: va después del
deploy, con la raya escrita — *la URL no sale a ningún prospecto sin eso*.

## Importante, no bloqueante

- **Red real degradada** (4G colombiano). Todo se midió en fibra de datacenter.
- **El límite de 60 peticiones/minuto por persona también aplica al
  organizador.** Las tablas de Filament paginan y refrescan; puede pasarse
  solo. Medirlo con una cuenta real.
- **Los 45 ms de costo fijo** — sin diagnosticar dónde se van.
- **Llave de despliegue** en el servidor: hoy el código se sube desde la
  máquina local con `git archive`.
- **Rotar las credenciales de R2** (quedaron escritas en la conversación).

---

# 9. Lectura final

**Un producto técnicamente sano que nunca había salido de desarrollo, y salió
por primera vez.**

Se corrieron 4 de 8 escenarios y aparecieron problemas en los 4; después los 8,
y todos terminaron en verde. **Quince correcciones, ninguna pidió rediseño.**
Eso es la señal más importante: los cimientos aguantan, lo que fallaba eran
conexiones mal atadas — y varias eran fallas silenciosas que en un evento real
se habrían descubierto tarde.

**No está listo para un cliente pagando.** Falta QA de cliente, el recorrido
completo y la seguridad del staff.

**Pero ya se sabe exactamente qué falta, cuánto aguanta y cuánto cuesta.** Que
es más de lo que se sabía ayer.
