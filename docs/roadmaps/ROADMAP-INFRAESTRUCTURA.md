# ROADMAP — INFRAESTRUCTURA Y CATALOGO VENDIBLE — 28/59

> **Abierto el 2026-08-02.** Reemplaza la seccion "RENDIMIENTO Y CAPACIDAD 0/5"
> de `PENDIENTES-WEBAPP.md`, que nacio de una premisa que hoy se demostro falsa.

---

# EL CATALOGO — escrito el 2026-08-17 (leer esto primero)

> **Principio (`DISPONIBILIDAD-HA.md` §1, desde el primer dia): "nada corre en un
> solo lugar". Todo lo que se vende NO tiene punto unico de falla. Un droplet
> solo es DEMO/QA y no se cotiza.** La arquitectura es una sola — Cloudflare LB
> + N droplets por rol (API / web / sockets+colas) + MySQL administrado con
> standby + Redis administrado HA + R2 — y lo que cambia con la gente es cuantos
> nodos y de que tamaño. **Lista de compra exacta, decisiones y por que:
> `docs/infra/STACK-PRODUCCION.md`.** Nada DERIVADO se promete sin medirlo.

| Nivel | Personas activas* | Combo | ~USD/mes | Que se garantiza | Estado |
|---|---|---|---|---|---|
| **0 · Demo/QA** | ~300 | 1 droplet 4 vCPU con todo adentro | ~$60 | Nada: si muere, el evento muere. **NO SE VENDE.** | **MEDIDO** (curva abajo) |
| **1 · Basico** | hasta **300** | **2 API (4 vCPU) + 2 web + 2 sockets/colas** (2 vCPU) · MySQL admin. con standby · Redis admin. HA · balanceador · R2 | **~$320-440** | Muere cualquier droplet → su pareja atiende y **nadie lo nota** (web y sockets MEDIDO con la persona 301 en una sesion en vivo; API MEDIDO sin errores, lento con 2 vCPU → por eso 4). BD/Redis → failover del proveedor. Cero datos perdidos, nadie hace nada. | **MONTADO Y MEDIDO 2026-08-17/18** — 300 personas, 3 apagones, 0 errores (`STACK-PRODUCCION.md` §3.1) |
| **2** | hasta **1.000** | 3 API (4 vCPU) + 2 web (4 vCPU) + 2 sockets · MySQL standby · Redis HA · LB | ~$440-600 | Idem | DERIVADO |
| **3** | hasta **2.500** | 5-6 API + 2-3 web + 2 sockets · MySQL standby + **replica** · Redis HA · LB | ~$700-1.050 | Idem | DERIVADO |
| **4** | 5.000-10.000 | 9-12 API + 3-4 web + 3 sockets · MySQL + 2 replicas · Redis HA · LB | ~$1.300-2.200 | Idem; medir antes | DERIVADO |

*"Activas" = navegando al ritmo del script (una pantalla cada 20-40 s: gente
MUY activa). Un evento real es mas liviano y admite mas inscritos por cada
activa — ese multiplicador (I.2) sigue sin medirse. **Estas cifras son el
piso, no el techo.**

### La curva de UNA maquina (4 vCPU / 8 GB, = nivel 0 demo) — medida por la persona 301

Metodo del 2026-08-17: N personas sinteticas por la webapp
(`entrar-por-la-puerta.js`) y **una persona real en Chrome desde Bogota**
navegando encima (TTFB por pantalla, `performance` del navegador). Es lo que
Kamilo pidio desde el principio: *"el usuario 1 y el 301 deben navegar igual"*.

| Activas | Persona 301 (TTFB) | Multitud (p50 / p95 del script) | CPU | Veredicto |
|---|---|---|---|---|
| 0 | 260-310 ms | — | 1% | linea base |
| **300** | **170-335 ms** (igual o mejor que sola) | 105-215 ms / 0,2-1,3 s | 41% | **PLANO** |
| 400 | mediana ~620 ms, picos 2,1 s | 230-680 ms / 3-3,6 s | 58% | se siente |
| 480-500 | mediana ~330-620 ms, picos 0,9-2 s | 0,4-3,9 s / 2,4-5 s | 69-73% | **al limite** |
| **700** | **9 s / 19 s / 9 s** (Kamilo lo vivio como persona 701) | 1,4-20 s | ~85% | **ROTO** |

**El codo esta entre 300 y 400, y llega ANTES de que el CPU toque el 60%:**
lo que rompe no es el promedio, son las olas (todos entrando, todos cambiando
de pantalla). Punto de operacion sano: **50% de CPU en regimen.**

**Reparto del CPU medido con 300 activas** (base de los niveles derivados):
Laravel 1,0 nucleo · Next 0,4 · MySQL 0,3 · sockets+Redis+nginx ~0,1. Por
cada 100 activas, operando al 50%: ~0,66 nucleos de Laravel, ~0,28 de Next,
~0,18 de MySQL. RAM nunca es cuello (1,8 de 8 GB). Sockets: 5.000 = 0-4% CPU.
**Es lineal, y eso es exactamente lo que hay que verificar:** separar roles
mete latencia de red entre nodos y el balanceador tiene su costo.

**OJO al comparar con el script:** las personas sinteticas no ejecutan
JavaScript, asi que NO se benefician del arreglo del cache del navegador
(`430ef94`). Una persona real de hoy pesa MENOS que una sintetica. Con 500
sinteticos encima (73% CPU), Kamilo navegando en Chrome sintio ~3 s por
pantalla el primer minuto (tormenta de entrada) y despues rapido, incluso al
refrescar.

### Lo que ya esta listo para multi-nodo (hecho en agosto sin querer)

WebSocket puro sin afinidad de sesion · adaptador Redis entre procesos de
socket · cache de auth compartida · archivos en R2 · `deploy.sh` portable · y
desde hoy la webapp invalida su cache por aviso del backend con **lista de
URLs** (`WEBAPP_INTERNAL_URLS`), preparada para N nodos web.

### El NIVEL 1 se monto y se midio (2026-08-17/18) — 7/7

- [x] `deploy.sh ROL=api|web|sockets` (todo = demo). Certificado de origen de
      Cloudflare en los nodos, 443 solo a Cloudflare + VPC.
- [x] MySQL 8.4 (2 nodos) y Valkey 8 (2 nodos) administrados en la VPC nyc1,
      cortafuegos por etiqueta. Datos importados del snapshot del demo.
- [x] 6 droplets 2 vCPU/4 GB por rol, codigo por `rsync` desde un nodo semilla.
- [x] Reparto: **nginx round-robin** en un droplet aparte (`lb-nginx.conf`) —
      el Cloudflare LB pide comprar origenes ($5/origen/mes) y se decidio no
      pagarlo para medir. Ver "nginx vs Cloudflare" en STACK-PRODUCCION §3.3.
- [x] **300 personas, 10 min, apagones a proposito: 5.639 pantallas, 0 fallos.**
      Web: invisible. Sockets: invisible (Kamilo dentro de una sesion en vivo con
      200 en la sala y 40 chateando; reenganche < 10 s). API: sin errores pero
      lenta con 2 vCPU sola → **API pasa a 4 vCPU en el catalogo.**
- [x] Tres cuellos que NO existian en el droplet unico, corregidos en codigo:
      phpredis+TLS se cuelga (→ predis), un handshake TLS a Redis por peticion
      (→ persistentes), una conexion MySQL TLS por peticion (→ PDO persistente).
      Corrida 1 → 2: p50 660 → 300 ms, carga API 9 → 4.
- [x] Cinco bugs VIEJOS que el stack real destapo (Pulse x2, asistencia por
      sesion nunca volcada, colas `exports/pdf/documents` que Horizon no
      escuchaba) — corregidos. Ver "LO QUE DESTAPO EL NIVEL 1" al final.
- [ ] Repetir la caida de API con nodos de 4 vCPU (confirmar que ademas de no
      fallar no se siente) — y con eso el Nivel 1 queda MEDIDO sin asteriscos.
- [ ] **Modo registro / modo evento** (STACK-PRODUCCION §11): administrados
      siempre encendidos + 1 droplet `ROL=todo` mientras la gente se inscribe
      (~$180-200/mes); el combo de 6 se levanta desde snapshots la vispera
      (~$6,5/dia). Falta: `escalar.sh --modo evento|registro` y probarlo una vez.

---

## EL OBJETIVO, ESCRITO

> **"Su evento es para N personas. Este es el stack, esto cuesta, esto aguanta,
> y esto le prometo por escrito."**

Cuatro niveles: **1.000 · 2.500 · 5.000 · 10.000+**. Cada uno con lo que
soporta, lo que cuesta, y **la promesa** — no solo el numero.

### Los criterios contra los que se juzga cualquier opcion

1. **Cero degradacion durante la ventana del evento.** El usuario 1 y el 1.501
   abren la misma pantalla en el mismo tiempo. **El numero que se vende no es
   donde se rompe: es donde la linea del canario deja de ser plana.**
2. **Sin punto unico de falla** a partir del nivel donde se vende tranquilidad.
3. **Portable.** Montable en cualquier proveedor que de un VPS. `deploy.sh` ya
   lo es (Ubuntu + apt, cero comandos propios de DigitalOcean).
4. **Aislamiento por cliente.** Un combo por evento/cliente; **adentro de ese
   combo la infraestructura crece segun haga falta** — una maquina para un
   evento chico, roles separados y balanceados para uno grande.

### Correccion de una decision mal transcrita (2026-08-02)

`NEXT-SESSION.md` registro el 2026-07-19 **"DECISION FIRME: UN DROPLET POR
EVENTO"** con un "no re-preguntar" que hizo que nadie la revisara. Se copio a
`COMPLETADO.md`, `EventOS_Roadmap.md`, `DIAGNOSTICO` y la memoria.

**Estaba mal transcrita.** Kamilo hablaba de **un combo aislado por cliente**,
que adentro puede tener N maquinas. Al escribirlo como "un droplet" se convirtio
un *estado actual* en un *techo permanente*.

**Consecuencia:** el plan de crecimiento de `COMO-CRECEMOS.md` §5 (API replicada
+ nodo de sockets + Redis y MySQL administrados + balanceador) **nunca fue
descartado — fue diferido por economia.** Y su dibujo **no vale como diseño**:
es una suposicion heredada del plan de abril, no un resultado de nada. Entra en
revision como el resto.

---

## LO MEDIDO EL 2026-08-02 — no volver a deducirlo

### Rendimiento por peticion (droplet 4 vCPU / 8 GB, en reposo)

| Tramo | Al empezar | Al cerrar |
|---|---|---|
| Arrancar Laravel (piso) | 19,8 ms | **11,4 ms** |
| Autenticar (Sanctum) | 9,1 ms | **3,2 ms** |
| Guardias + limitador | 3,9 ms | ~9 ms |
| `branding` completo | 37,8 ms | **27,6 ms** |
| `agenda` completo | 50,6 ms | **39,1 ms** |
| nginx sirviendo un estatico | 0,1 ms | 0,1 ms |

### Capacidad (mismo script, mismas personas)

| Personas navegando | CPU antes | CPU despues | Pantallas/s | Errores |
|---|---|---|---|---|
| 1.500 | **82%** (sin margen) | **46%** | 52,0 | 0 de 15.597 |
| 2.500 | nunca se midio | **~68%** | **87,4** | 0 de 26.206 |

**El costo de CPU por peticion bajo ~46%** — mas que el 27% de latencia, porque
lo que se quito era CPU pura. El techo de la sesion anterior eran ~70 req/s **y
a ese ritmo estaba roto**; hoy hace 87,4 al 68% sin un solo error.

### Premisas que se cayeron

- **"45 ms de peaje fijo, bajarlo a la mitad duplica la capacidad"** — FALSO. El
  peaje real eran 33 ms (19,8 de framework + 13 de auth y guardias), y el propio
  documento decia que 15-25 ms seria sano. **Ese 2x nunca estuvo disponible.**
  Lo probable es que el 45 se midiera bajo carga: no era costo fijo, era cola.
- **"El trabajo de la app son 7 ms"** — para `branding` son ~4; para `agenda`,
  ~16. **Depende del endpoint, y eso cambia la cuenta de capacidad**: agenda
  cuesta 2,5 veces el piso. El techo depende de QUE pantallas abre la gente.
- **RAM nunca fue el cuello** — 1,8 GB usados de 8 con 2.500 personas encima.
  Confirmado por segunda vez.

### Dos trampas de despliegue (costaron horas hoy)

- **`opcache.validate_timestamps=0`**: PHP-FPM no vuelve a mirar los archivos.
  Subir codigo o correr `config:cache` **no tiene efecto** sin
  `systemctl reload php8.3-fpm`. Falla en silencio: responde 200 con la version
  vieja. Documentado en `deploy.sh` (`a25b2ab`).
- **`env()` no funciona en `bootstrap/app.php`**: con la configuracion cacheada
  devuelve el valor por defecto y la configuracion real **se ignora sin un solo
  error**. Por eso `trustProxies` vive en `AppServiceProvider`.

---

# FASES

## I.0 — Errores que rompen un evento — 3/3 **CERRADO 2026-08-02**

> Los tres salieron navegando en produccion, con los 8 escenarios de carga ya en
> verde. **Ningun test los habria encontrado.**

- [x] **El magic link se quedaba girando para siempre** (`317ce9e`). El `fetch`
      no tenia tiempo limite, y el `catch` solo mostraba un aviso flotante
      dejando el spinner puesto: cualquier fallo pasajero quedaba convertido en
      pantalla muerta, sin explicacion y sin boton. Ahora corte a 20 s y caida a
      la pantalla de error, con **"Reintentar" y no "pedir otro link"** — el
      link solo se gasta cuando el backend lo valida, asi que sigue vivo.
      **Correccion honesta:** primero dictamine que "la peticion nunca se hace",
      basandome en un `grep | tail` que cortaba resultados. Falso: el POST si
      sale, y no pude reproducir el colgado. La causa exacta queda sin
      identificar — pero la ausencia de salida era un defecto real por si sola.
- [x] **500 en las historias del muro** (`493461f`). `map()` sobre una
      Eloquent\Collection conserva el tipo aunque adentro queden enteros, y su
      `unique()` llama a `getKey()` sobre cada uno. Arreglado con `->toBase()`.
- [x] **`_next/image` en 400** con las imagenes de `cdn.killjoy.pro`
      (`317ce9e`). R2 con dominio propio no lo cubren los patrones genericos.
      Sale de `CDN_HOSTNAME`, porque cambia con cada cliente.

> **Hueco encontrado de paso, NO introducido:** `messages/pt.json` no tiene la
> seccion `mobileNav` — el build lo reporta como `MISSING_MESSAGE`. Portugues
> esta incompleto.

## I.1 — Rendimiento — 9/16

### Lo que se cerro el 2026-08-17 — la persona 301 en Chrome

- [x] **El marco se pedia 7 veces por pantalla** (eventos-web `253107a` +
      backend `cce1a84`). Una pantalla eran 7 llamadas al backend: `auth/me`,
      `by-slug`, `contact-requests`, `announcements`, `documents`, `modules` +
      **la unica que la persona vino a ver**. 6 de 7 eran el marco repetido.
      Ahora vive en memoria del servidor de Next (`lib/marco.ts`), clave
      `entidad:evento:attendeeId`, y **el backend lo invalida a proposito**
      (`InvalidationService::soltarMarcoWebapp` → `POST /api/internal/invalidate`,
      secreto compartido, ANTES del socket para que el `router.refresh()` de
      los clientes ya encuentre el cache vacio). El TTL de 60 s es red de
      seguridad, no el mecanismo. Segunda pantalla: **de 7 llamadas a 2**.
      **Medido con 300 personas: CPU 61% → 41%, p50 de pantalla 250-600 →
      105-215 ms.** No el 3x que se esperaba: las 2 llamadas que quedan son
      las caras (agenda cuesta 2,5x el piso).
      Gotchas cazados: `globalThis` para el almacen (Next empaqueta ruta y
      layout por separado: dos Maps, el aviso respondia `removed: 0`) · el
      catch AFUERA del productor (si no, un fallo se cachea 60 s) · por
      persona clave EXACTA ("…:13" como prefijo borraria "…:1320") ·
      `documents` no emitia invalidacion nunca (DocumentObserver ahora si) ·
      solicitudes de contacto avisan POR PERSONA (NetworkingController).
- [x] **`by-slug` NO se personaliza** (`2397cdb`): el `registered_for_user` que
      justificaba una clave por sesion no existe en el backend. Clave = solo
      slug: **una llamada por minuto para todo el servidor**, y el limitador
      por IP de by-slug deja de importar para la webapp.
- [x] **Un fallo pasajero cacheaba "evento vacio" un minuto** (`2397cdb`).
      Cazado con la persona 301 en Chrome: un 429 en by-slug devolvia el
      placeholder (id 0), el cache lo guardaba, y esa persona veia el evento
      sin anuncios ni desafio (`/events/0/...`). Catch afuera del cache.
- [x] **CADA CLIC EN EL RAIL BORRABA EL CACHE DE NAVEGACION ENTERO** (`430ef94`)
      — **por eso los esqueletos al volver a una pantalla ya visitada.**
      `useRouter` de next-intl devuelve un objeto NUEVO por ruta (su useMemo
      depende de `usePathname`), y `GlobalSocketProvider` lo tenia en las deps
      del efecto: se rehacia en cada navegacion, veia el socket ya conectado y
      programaba el `router.refresh()` "de reconexion" con jitter 0-2 s.
      Contado en produccion con Chrome: **1 clic = 1 refresh a los ~1,8 s.**
      Local no lo sufria (sin socket server, nunca conecta) — por eso nadie lo
      vio. Ahora: volver a Agenda = **0 peticiones, instantaneo**; primera
      visita 1 peticion (antes 3-4). El refresh de reconexion sale SOLO del
      evento `connect` cuando ya hubo conexion. **Este arreglo no lo mide el
      script de carga (no ejecuta JS): la ganancia real con navegadores es
      mayor que la que muestran las corridas.**
- [ ] **`auth/me` en cada pantalla** — ahora es 1 de las 2 llamadas que quedan
      (y sostiene ademas la conexion de socket). Cachearla del lado de Next
      unos segundos es la mayor pieza restante, pero es la SESION: si el
      backend revoca, tiene que verse rapido (TTL ~10 s + invalidacion en
      logout/ban). Merece analisis propio.
- [ ] **Precarga completa al entrar** (diseño propio, pedido de Kamilo:
      *"como un HTML estatico: se descarga todo en la introduccion"*). Durante
      el showcase, traer los modulos del rail ENTEROS (`router.prefetch` full),
      escalonado (uno cada 1-2 s), guardados 30 min → cada clic instantaneo
      sin esqueleto incluso la primera vez. Hoy la precarga por hover es
      PARCIAL (hasta `loading.tsx`) y el clic pide el resto igual. Costo: ~10
      renders por persona una vez al entrar, en el minuto que ya es el peor →
      escalonar y/o solo los 4-5 modulos principales.
- [ ] **El socket refresca solo lo que cambio.** Cualquier `data:invalidate`
      sigue vaciando el cache del navegador ENTERO; con evento vivo eso pasa
      seguido y la precarga se pierde a cada anuncio. `data:sync` con el dato
      adentro para anuncios/modulos como ya se hizo con agenda (E3).

- [x] **La escritura de Sanctum en cada lectura** (`7fd3a76`). `last_used_at`
      hacia un UPDATE en cada peticion autenticada; con MySQL en durabilidad
      maxima son dos sincronizaciones a disco. **Y bloqueaba la replica de
      lectura**, que es la base de los niveles de 2.500 en adelante.
- [x] **`filament:optimize` nunca se habia ejecutado** (`a25b2ab`). El panel de
      administracion arrancaba entero en cada peticion de la API resolviendo sus
      iconos: **10,47 ms → 0,51 ms**. Estaba en la guia descrito como paso
      cosmetico del admin, por eso se salto.
- [ ] **Consultas duplicadas en la cadena de autenticacion.** El asistente se
      resuelve dos veces y el evento se carga dos veces. **11 de las 15
      consultas de `branding` ocurren antes de que el controlador haga nada.**
- [ ] **Los tres `COUNT` de `branding`** (asistentes, sesiones, fotos) se
      recalculan en cada llamada. Cacheables.
- [ ] **Re-medir el techo** con todo aplicado y publicar UN numero por endpoint.

### La app preguntaba de mas — cazado por Kamilo navegando (2026-08-02)

> Su observacion, textual: *"aunque ya hubiese abierto todos los modulos siguen
> cargando esqueletos... el esqueleto pasa de ser algo bonito a un indicador de
> que algo esta mal"*. Tenia razon, y no era solo estetica: **cada esqueleto es
> una peticion al servidor.**
>
> Medido: una persona, dos clics = **51 llamadas al backend**.

- [x] **El rail precargaba los 10 modulos en cada navegacion** (`ab6e6ff`).
      Ahora precarga UNO al pasar el mouse. Carga completa: de 19 peticiones de
      precarga a **0**.
- [x] **Los datos del evento se pedian 7 veces por navegacion** (`ab6e6ff`).
      Cache en memoria del servidor, 30 s, **con la sesion en la clave** — se
      personalizan, y cachear por evento a secas le entregaria a una persona los
      datos de otra. Dos clics: de 51 llamadas a **36**.
- [x] **El esqueleto al VOLVER** — CERRADO 2026-08-17 (`430ef94`, arriba). La
      hipotesis era parcialmente cierta: si lo borraba `router.refresh()`, pero
      no el del tiempo real — el de "reconexion" que el socket disparaba EN
      CADA CLIC. El "refrescar solo lo que cambio" sigue abierto arriba como
      pieza propia.
- [ ] **La webapp toca su propio techo con UNA persona.** Aparecieron `503` en
      desafio, speakers, live, social y documentos **con el servidor vacio**,
      por la rafaga de renders simultaneos. Visto de nuevo el 2026-08-17 en
      LOCAL con build de produccion: un `503` en una de las 3 peticiones RSC
      del primer clic a agenda. Con el refresh-por-clic muerto la rafaga es
      menor; **verificar si sigue apareciendo.**

> **Y lo que esto le hace al catalogo:** el script de carga cuenta **1 peticion
> por pantalla**; un usuario real de navegador costaba **10 o mas**. Las "2.500
> personas" de I.3 se midieron con clientes que piden mucho menos que la app
> real. **Hace falta un escenario que entre por la webapp** — el mismo agujero
> del arnes que ya escondio el bug de los limites por IP.

## I.1b — La puerta del evento — 2/4

> **El bug mas grave del dia, y solo aparecio al entrar por la webapp.**

- [x] **`TRUSTED_PROXIES` con la IP del droplet anterior** (2026-08-17, corregido
      en el servidor + `deploy.sh`). Al restaurar el snapshot en un droplet
      nuevo, el `.env` traia la IP publica del droplet del 2 de agosto. La
      webapp le habla al backend por `https://api.…` (IP publica propia), asi
      que Laravel NO confiaba en Next como proxy, ignoraba el X-Forwarded-For y
      veia a TODOS los asistentes como una sola IP: **es el bug de `41b8040`
      reaparecido por configuracion**. Sintoma: `by-slug` (300/min por IP) le
      dio 429 a la persona 301 con 300 navegando → evento vacio. **Regla:
      al restaurar un snapshot, revisar TRUSTED_PROXIES SIEMPRE.**
- [ ] **500 entrando en 60 s → 20 bloqueados en la puerta (429).** El techo por
      red de 300/min por IP se agota con 500 personas entrando a 8-10 por
      segundo desde una IP (= un recinto). Es el mismo item de abajo (contar
      fallos, no intentos), medido de nuevo hoy. **Y `by-slug`/rutas publicas
      siguen a 300/min por IP tambien para el Expo** (300 telefonos en el wifi
      del salon abriendo la app en el mismo minuto): pendiente decidir el
      limitador de rutas publicas por recinto.

- [x] **El limite de ingreso protegia la red, no la cuenta** (`69dd89c`). En un
      recinto mil personas comparten el wifi, o sea UNA IP publica. Con 5
      ingresos por minuto por IP, **solo 5 personas por minuto podian entrar al
      evento**. Medido: de 50, entraron 5. Ahora se cuenta por cuenta (5/min a
      ESA cuenta, mas estricto donde importa) con un techo por red. De 300,
      entraron 300. Mismo error corregido en `magic_link` (10 enlaces/hora para
      todo un recinto) y `magic_verify` (ahora por token, que ya es de un solo
      uso).
- [ ] **El techo por IP hay que contarlo por FALLOS, no por intentos.** El techo
      nuevo (300/min por IP) bloqueo 450 de 1.000 cuando llegaron a 25 por
      segundo. Se cambio un absurdo por otro mas suave, pero sigue castigando al
      recinto lleno. **Un ataque de fuerza bruta son fallos; un evento
      empezando son aciertos** — el limitador cuenta las dos cosas igual. Hay
      que limpiar el contador de IP en cada ingreso exitoso.

## I.2 — Instrumentacion del ritmo real — 0/3

> **Una sola pieza, cuatro pagos.** Hoy no sabemos que es normal, y por eso cada
> umbral del sistema es una adivinanza — incluido el que expulso a Kamilo.

- [ ] Registrar cuantas pantallas abre cada persona por minuto, y cuales.
- [ ] **Pago 1 — comercial:** el multiplicador que convierte peticiones/segundo
      en personas. Hoy es un supuesto mio (una pantalla cada 20-40 s) y **es el
      parametro mas influyente de toda la cotizacion**.
      **Pago 2 — seguridad:** la linea base contra la que se detecta abuso.
      **Pago 3 — operacion:** los datos que alimentan el Platform Health (I.5).
      **Pago 4 — el limite `api`**, hoy en 300/minuto puesto a ojo.
- [ ] **Correccion tecnica a la idea original:** no se puede medir "RAM y CPU del
      usuario 1320" — en un pool compartido de PHP-FPM eso no se atribuye por
      persona. Lo que si: peticiones por minuto, que rutas, tamaño de subidas,
      eventos de socket. Con eso, *"el 1320 va a 40 peticiones/minuto cuando la
      mediana es 2"* es trivial de detectar, y sirve mas.

## I.3 — El catalogo vendible — 4/7

> **2026-08-17: la tabla del catalogo esta ARRIBA del todo** ("EL CATALOGO").
> El nivel 1 (una maquina) esta MEDIDO por la persona 301 en Chrome; los
> niveles 2-4 son DERIVADOS del reparto de CPU medido y hay que montarlos y
> medirlos antes de venderlos. Lo de abajo es el historico del 2 de agosto.

- [x] **Nivel 0 (demo) — una maquina** — MEDIDO 2026-08-17: hasta ~300 activas
      plano, 500 al limite, 700 roto. **No se vende**: si muere, el evento
      muere. Es la base de calculo de los niveles con redundancia.

### LA CURVA DEL CANARIO — MEDIDA 2026-08-02

> **El canario es UNA persona real desde Bogota**, con su propio token, medida
> aparte del promedio mientras la multitud navega. Es el usuario N+1.
>
> **Los tiempos INCLUYEN el viaje de red (~93 ms Bogota-Nueva York)**: son lo
> que siente la persona, no tiempo de servidor puro. El servidor solo son ~28 ms
> de esos 121. Droplet 4 vCPU / 8 GB con los arreglos de hoy aplicados.
>
> **Y la multitud de esta tabla entra por la API, no por la webapp** — o sea que
> son mucho mas baratos que personas reales. Para cotizar, la tabla que vale es
> la de abajo.

| Personas navegando | CPU | canario `branding` p50 | p95 | Veredicto |
|---|---|---|---|---|
| **reposo** | 1% | **121,0 ms** | 124,5 | linea base |
| **1.000** | 27% | **120,4 ms** | 124,5 | **plano — no lo nota** |
| **2.000** | 55% | **121,6 ms** | 130,0 | **plano** |
| **2.500** | 68% | **124,9 ms** | 143,6 | +3% / +15% — se empieza a sentir |
| **3.000** | 77% | **147,6 ms** | **253,2** | **DOBLADO: +22% y el p95 al doble** |

**El codo esta entre 2.500 y 3.000.** Y la señal que primero se rompe **no es
la mediana, es el p95**: a 3.000 la mediana sube 22% pero la cola se DOBLA. Es
decir, la mayoria sigue bien y una minoria la pasa mal — exactamente lo que un
promedio esconde y el canario no.

**Traduccion honesta a niveles, para UNA maquina:**

- **hasta 2.000 — comodo.** Canario indistinguible del reposo, CPU 55%.
- **2.500 — el borde vendible.** Todavia bien (+3%), pero sin margen.
- **3.000 — ya no.** Una maquina deja de alcanzar aqui.

> **OJO, el multiplicador:** esto es al ritmo del script — **una pantalla cada
> 20-40 segundos, gente MUY activa**. En un evento real la gente mira el
> telefono cada 2-3 minutos, y las mismas peticiones/segundo son 4-6 veces mas
> personas. **Ese factor sigue sin medirse (I.2) y es el que convierte esta
> tabla en una cotizacion.** Sin el, estos numeros son el piso pesimista.

---

### LA CURVA QUE VALE — PERSONAS REALES POR LA WEBAPP (2026-08-02, noche)

> **ESTA TABLA REEMPLAZA A LA DE ARRIBA PARA COTIZAR.**
>
> La de arriba se midio con un cliente que hace **1 peticion por pantalla**. Una
> persona real con navegador hace **~10**: la pagina se renderiza en el
> servidor y eso dispara todas las llamadas al backend. **La diferencia es de
> unas ocho veces.**
>
> Medido con `tests/load/entrar-por-la-puerta.js`, el primer escenario que entra
> por donde entra la gente.

#### MEDIDO — se puede citar

| Personas reales por la webapp | CPU | Tiempo de pantalla | Errores |
|---|---|---|---|
| **300** | **74%** | p50 bajando hasta **541 ms** | 0 |
| **550** | **87%** | p50 entre **8.084 y 16.326 ms** | 0 |

- **El canario con 550 encima** (una persona en Bogota, aparte del promedio):
  `branding` **4.899 ms** contra **121 ms** con el servidor vacio.
- **Las 3.184 pantallas devolvieron 200.** Ni un error. **El servidor informa
  que todo esta bien mientras la gente espera diez segundos** — por eso el
  criterio es el canario y no el porcentaje de errores.
- **Abrir una pantalla = 9 llamadas al backend** (contadas en nginx).

**Precisiones para no citar de mas:**
- Los tiempos del canario **incluyen ~93 ms de viaje Bogota-Nueva York**. Son la
  experiencia real, no tiempo de servidor puro.
- El **541 ms de las 300** venia de un percentil **acumulado** que aun bajaba, o
  sea es un **techo**: el real es algo menor. La corrida con percentil por
  ventana quedo sin completar.
- El **rango de las 550** son los percentiles por ventana de 30 s. No hay un
  numero unico.
- El escenario **no pide archivos estaticos ni imita las precargas del
  navegador**, asi que el costo real es **algo MAYOR**. Es un piso.

#### DERIVADO — aritmetica sobre lo medido

- **Una persona real cuesta ~9 veces un cliente del script viejo**, comparando
  CPU por usuario (68%/2.500 contra 74%/300). No es una medicion directa.
- **De 300 a 5.000 son 16x.**

#### EXTRAPOLADO — NO citar a un cliente sin medirlo antes

- *"1.200-1.800 personas a ritmo de evento real"* — depende de un multiplicador
  (una pantalla cada 2-3 min en vez de cada 20-40 s) **que sigue sin medirse**.
- *"Bajar de 10 a 3 peticiones por pantalla triplicaria la capacidad"* — es la
  expectativa, no un resultado. **Exactamente el tipo de afirmacion que produjo
  el "45 ms que duplican la capacidad" y resulto falsa.**

> **Conclusion que si se sostiene: una maquina de 4 nucleos sirve del orden de
> 300 personas reales navegando activamente. A 550 la experiencia esta rota.**
> El catalogo decia 2.500.

**Y el limite de lo que queda por optimizar:** hoy se saco ~2x. Llegar a 5.000
pide 16x. **Ningun ajuste de codigo conocido da 16x** — ese salto es la
arquitectura de varios nodos. Antes de tocar hardware queda una sola pieza con
tamaño (las ~10 peticiones por pantalla, ver I.1), y su ganancia esta sin medir.

#### La leccion de metodo, que es la mas cara del dia

**Kamilo pidio desde el principio medir la experiencia real de un usuario.** Se
midio al final. Toda la optimizacion de la jornada se hizo contra un numero
inflado ocho veces — las correcciones siguen siendo validas, pero la foto de
capacidad que se dio a mitad de dia estaba mal.

**Regla que sale de aqui: el primer escenario de cualquier medicion futura entra
por la webapp. Los tests contra la API con token ya emitido son un
complemento, no la medida.**

- [x] **Correr la curva del canario** buscando **el codo** — donde la linea deja
      de ser plana— y no el punto de rotura. **HECHO: el codo esta entre 2.500 y
      3.000 personas muy activas en una maquina de 4 nucleos.**
- [x] ~~**Nivel 1.000** — una maquina.~~ **SUPERSEDED 2026-08-17: una maquina
      NO da para 1.000 activas — da para ~300.** Ese es el nivel 1 del
      catalogo de arriba; 1.000 es el nivel 2 y pide roles separados.
- [ ] **Niveles 2.500 / 5.000 / 10.000+** — minimo dos nodos, sin punto unico de
      falla. **El salto de nivel no es de capacidad, es de promesa.**
- [x] **Topologia y decisiones CERRADAS 2026-08-17 → `docs/infra/STACK-PRODUCCION.md`.**
      Balancea **Cloudflare Load Balancer** (sin punto unico, portable entre
      proveedores, ~$5-10; nginx propio descartado por ser el nuevo punto
      unico; DO LB descartado por atar a DO sin aportar). BD **DO Managed MySQL**
      en VPC (< 1 ms) con standby y replica; **PlanetScale descartado**
      (remoto 80-150 ms, sin FKs, precio por uso). Redis administrado con TLS.
      UN nodo de sockets (5.000 conexiones = 0-4% CPU). **Todo lo vendible sin punto unico desde el nivel 1** (2 API + 2
      web + 2 sockets chicos + BD/Redis administrados). "Mas nucleos en la
      misma maquina" NO es nivel vendible: sigue siendo una sola maquina.
- [ ] **Escribir `deploy.sh --rol api|web|sockets|todo`** — hoy monta "todo" en
      una. Es lo que falta para poder MONTAR el nivel 1 y medirlo.
- [ ] **Escribir la promesa de cada nivel**: RTO/RPO, punto de operacion en
      **50-60% de CPU y no 82%**, y que costo total **y por persona**. Ojo: el
      costo de un combo redundante (~$191/mes) es **por cliente**, no repartido.

## I.4 — Independencia de proveedor — 0/3

> Es la pregunta original: *"se cae DigitalOcean, ¿que puedo hacer?"*. **Hoy la
> respuesta es: nada.** El unico respaldo es el snapshot, y vive dentro de
> DigitalOcean. Va DESPUES de I.3 porque la estrategia depende de donde terminen
> viviendo los datos.

- [ ] **Respaldo fuera del proveedor** — volcado cifrado a R2 por cron. Horas de
      trabajo, centavos al mes, y es el unico punto donde hoy no hay respuesta.
- [ ] **Verificar `deploy.sh` remontando de cero.** Se escribio desde lo que se
      hizo a mano y **nunca se corrio limpio**.
- [ ] **RTO/RPO escrito y probado** en otro proveedor. La promesa honesta no es
      "no te enteras" —eso es activo-activo, caro y dificil— sino *"remontamos
      en X minutos perdiendo maximo Y"*. Con `deploy.sh` a ~15 min y respaldo
      horario, sale algo vendible y verificable.

## I.5 — Platform Health — 0/11

> **Kamilo lo pidio el 2026-04-19** (memoria `project_event_pulse_idea`), con su
> propia nota: *"sin esto, un error en produccion lo descubrimos porque un
> asistente se queja"* y *"hacerlo antes del primer evento en produccion"*.
> **Quedo enterrado bajo `PENDIENTES.md` §9 "Nice to have (NO hacer antes de
> cerrar deal septiembre)"** — una seccion atada a un deal que se cayo en julio.
> El deploy del 1-2 de agosto fue ese primer evento, y corrio sin esto.

> **2026-08-17 — Kamilo, al cierre:** *"¿vamos a tener un panel que nos muestre
> en tiempo real el rendimiento, no pm2, algo que permita seguimiento real ANTES
> de que el usuario se de cuenta que fallo?"* Hoy seguimos operando a ciegas:
> el 1-2 de agosto y hoy, "produccion" se miro con `top` y el log de nginx por
> ssh. Eso no es operar, es espiar. Este es el BRIEF DE DISEÑO acordado (se
> diseña bien cuando toque, no se codea de pasada), en el orden en que le sirve
> al dia del evento.

### Las tres capas — lo minimo antes de exponer la URL

- [ ] **Capa 1 — Alerta externa** ("¿esta arriba y responde a tiempo?"). Un
      vigilante FUERA de nuestra infra (BetterStack / UptimeRobot, planificado
      desde SEC-5.3) pega a `/api/v1/health`, `app.` y el socket cada 30 s desde
      varias regiones; falla o tarda mas de X → **aviso al telefono de Kamilo**.
      30 min de configurar. **Antes de exponer la URL a nadie.**
- [ ] **Capa 2 — Panel en vivo del servidor** ("¿por que esta lento?"):
      **Laravel Pulse** (oficial, gratis, una tarde) + Sentry que ya esta.
      Peticiones/s, tiempo por endpoint, consultas lentas, colas, excepciones,
      usuarios que mas piden. Ya existe y no se usa: `HealthController`
      (`/health`, `/version`), Sentry, Horizon.
- [ ] **Capa 3 — El canario automatico** ("¿la persona 301 lo esta
      sintiendo?"): `entrar-por-la-puerta.js` con USERS=1 corriendo cada minuto
      desde un droplet chico o desde el monitor externo, midiendo tiempo de
      pantalla por la webapp; sobre el umbral → alerta. Es lo que hoy se hizo a
      mano en Chrome, automatizado.
- **Capa 4 (gratis con el stack):** el Cloudflare LB ve el health check de cada
  nodo cada 10 s y **saca al que falle solo** — actua mientras las otras avisan.

### Lo que Kamilo pidio ademas ("todo muy dinamico, que sepa en tiempo real todo")

- [ ] **La torre de control** — UNA pantalla en el admin (solo super_admin) para
      el dia del evento: personas conectadas ahora (sockets por nodo),
      peticiones/s, p95 por endpoint, errores del ultimo minuto, CPU por rol,
      colas, y **estado de cada nodo en el balanceador** (verde/rojo). En vivo
      por socket, no refrescando. Pulse da la mitad generica; la otra mitad sale
      de lo que ya emite el socket server + la API de DO. Distinto del Event
      Pulse (que es del organizador): esto es de quien opera la maquina.
- [ ] **Alertas con umbrales que salen de lo medido, no a ojo**, por nivel del
      stack: CPU por rol > 60% sostenido 2 min · p95 > 800 ms · 5xx > 1% ·
      colas creciendo · reconexiones masivas de socket · **y CUALQUIER 429 a un
      usuario legitimo** (hoy aprendimos que es la firma del bug de IP
      compartida: debe sonar el telefono, no descubrirse navegando).
- [ ] **Modo "evento en curso"**: interruptor que sube la sensibilidad (canario
      cada 30 s, alertas mas agresivas), **congela despliegues** y toma snapshot
      al empezar. El dia del evento no se toca nada.
- [ ] **Registro por persona** (= I.2, doble pago): pantallas/minuto, rutas,
      subidas por asistente → multiplicador comercial real + linea base de
      abuso ("el 1320 va a 40/min cuando la mediana es 2").
- [ ] **Ensayo de fallas periodico ("game day")**: tirar un nodo con carga y que
      nadie lo note — antes de cada evento importante, no una vez (E7 y la
      medicion del nivel 1 son el patron). Con runbook por falla: "si pasa X,
      haz Y" (`COMO-VOLVER.md` tiene el espiritu).
- [ ] **Escalar en 5 minutos con un comando**: `escalar.sh --api +1` crea el
      droplet desde snapshot, corre `deploy.sh --rol api`, lo mete al LB. NO
      autoescalado (traicionero en olas): escalado humano asistido, desde la
      torre de control, antes de que duela.
- [ ] **Logs centralizados con id de peticion** (Grafana Loki o el mismo
      BetterStack): con 6-11 maquinas, entrar por ssh a cada una (lo de hoy)
      no es viable. "Que le paso a la persona 1320 a las 10:42" en un solo
      lugar, nginx + Laravel + socket correlacionados.
- [ ] **Pagina de estado publica** (`status.<dominio>`, BetterStack la da
      gratis): a un cliente enterprise, "asi estuvo tu evento minuto a minuto"
      vale mas que la promesa del contrato.

**Orden honesto:** capas 1-3 + torre de control + alertas por umbral son lo que
cambia el juego; el resto se agrega cuando un evento real lo pida. **Nada de
esto sustituye la arquitectura: el monitor avisa, el balanceador y la pareja de
nodos son los que salvan.**

## I.6 — Antes de exponerselo a nadie — 0/3

- [ ] **Cloudflare a naranja.** Hoy en gris a proposito (para medir el servidor y
      no a Cloudflare), y el servidor esta desnudo: 125 intentos de bots
      buscando `/.env` desde 7 IPs.
- [ ] **Rotar las credenciales de R2** — quedaron escritas en la conversacion del
      2026-08-02. R2 no se destruye con los droplets.
- [ ] **2FA del staff.** Raya escrita por Kamilo: **la URL del admin no sale a
      ningun prospecto sin esto.** Ver `ROADMAP-SEGURIDAD-STAFF.md`.

---

## LO QUE DESTAPO EL NIVEL 1 (2026-08-18) — 5/5 corregidos

> Ninguno lo causo el multi-nodo. Todos existian; el stack real (Pulse, Data
> Center y exports usados de verdad con `QUEUE=redis`) los hizo visibles. **La
> pregunta de Kamilo — "¿son bugs que salen por esta implementacion?" — tiene
> respuesta medida: no. Y uno de escala si se encontro y se resolvio (TLS por
> peticion, arriba).**

- [x] **Event Pulse "no conectado" (1/2)**: armaba la URL del socket como
      `hostname:3001` (`public/event-pulse/js/socket.js:35`), nunca abierto por
      el cortafuegos ni en el droplet unico. Ahora `PulseController::bootstrap`
      manda `socket_url` (`SOCKET_SERVER_URL`), como display y mission-control.
- [x] **Event Pulse "no conectado" (2/2)**: carrera — el callback del bootstrap
      corria antes de que cargara `counters.js` (la API detras de Cloudflare +
      proxy responde antes de que el navegador baje `sections.js`) →
      `animateCounter is not defined` → nunca `EP.ready` → nunca socket. Ahora
      arranca tras `DOMContentLoaded` (`app.js`), `?v=23`.
- [x] **Asistencia por sesion nunca volcada**: `FlushSessionAttendanceJob` leia
      `Redis::connection('default')` (BD 0, con prefijo) y el socket escribe
      `session:{id}:joined|left` en la BD 2 sin prefijo (conexion `socket`).
      `session_attendances` estaba vacia desde el primer evento. Corregido: 304
      registros de la sesion 5 al primer volcado; Data Center muestra 406/track.
- [x] **Exports del Data Center nunca se procesaron en produccion**: van a la
      cola `exports` (y recaps a `pdf`, ZIP de documentos a `documents`) y
      Horizon solo escuchaba `default`. En local `QUEUE=sync` los ejecutaba en
      linea. Nuevo `supervisor-heavy` (`config/horizon.php`): 3 colas, timeout
      900 s, 3 procesos en produccion. Los 4 exports atascados salieron en 20 s.
- [x] **Sesion demo en vivo**: las fechas del seeder eran del 1-2 de agosto y
      la BD guarda hora Bogota (`APP_TIMEZONE`) — para probar se movio la
      sesion 5 a hoy y se le puso el video de YouTube (url + iframe embebido).

**Leccion:** los tableros del organizador (Pulse, Data Center, exports) NO
tienen escenario de carga ni QA en produccion. Cada uno guardaba un bug de
meses. Entra a I.5 como criterio: el game day incluye abrir los tres.

## LO QUE SE ARREGLO EL 2026-08-02 Y NO ES DE RENDIMIENTO

> Salio navegando, no midiendo. **Era mas grave que todo lo de rendimiento: un
> evento real se caia en la puerta, no adentro.**

- [x] **Los limites por IP contaban para TODA la plataforma** (`41b8040` +
      `901aa12`). La webapp llama al backend desde el servidor, asi que Laravel
      veia a todos los asistentes con una sola direccion: **5 ingresos por
      minuto y 10 magic links por hora ENTRE TODOS.** En un evento de 1.000
      personas, la numero 11 en abrir su enlace quedaba bloqueada.
      **Por que sobrevivio a los 8 escenarios en verde: todos los tests usan
      tokens ya emitidos y pegan directo a la API. Nadie inicio sesion nunca a
      traves de la webapp bajo carga.**
- [x] **Un 429 expulsaba al login** a gente con sesion valida (`901aa12`).
      `/perfil` era la unica pantalla que redirigia por una consulta de DATOS en
      vez de por la SESION. **429 significa "vas muy rapido", no "no eres tu".**
- [x] **El limite `api` de 60/minuto** expulsaba usuarios legitimos: abrir una
      pantalla dispara entre 8 y 15 peticiones. Subido a 300 **como parche
      explicito**, pendiente del numero real (I.2).

**Leccion para el arnes de medicion:** los escenarios verdes no sustituyen usar
el producto. De los 25 bugs del 1 de agosto, 8 aparecieron solo abriendo el
navegador; los 3 de hoy, tambien. **Hace falta un escenario que entre por la
puerta de verdad — login y magic link a traves de la webapp, bajo carga.**
