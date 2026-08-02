# ROADMAP — INFRAESTRUCTURA Y CATALOGO VENDIBLE — 8/30

> **Abierto el 2026-08-02.** Reemplaza la seccion "RENDIMIENTO Y CAPACIDAD 0/5"
> de `PENDIENTES-WEBAPP.md`, que nacio de una premisa que hoy se demostro falsa.

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

## I.1 — Rendimiento — 4/9

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
- [ ] **El esqueleto al VOLVER sigue ahi.** Hipotesis a verificar: el cache de
      navegacion (5 min, ya configurado) lo borra `router.refresh()`, que la app
      llama ante cada aviso de tiempo real — y en Next eso **invalida todos los
      modulos, no solo el que cambio**. Con un evento vivo eso pasa seguido.
      **El arreglo es refrescar solo lo que cambio, y eso toca el tiempo real
      entero: merece diseño, no parche.**
- [ ] **La webapp toca su propio techo con UNA persona.** Aparecieron `503` en
      desafio, speakers, live, social y documentos **con el servidor vacio**,
      por la rafaga de renders simultaneos. Deberia irse con la rafaga — hay que
      verificarlo. **Ese techo nunca se midio por separado del backend.**

> **Y lo que esto le hace al catalogo:** el script de carga cuenta **1 peticion
> por pantalla**; un usuario real de navegador costaba **10 o mas**. Las "2.500
> personas" de I.3 se midieron con clientes que piden mucho menos que la app
> real. **Hace falta un escenario que entre por la webapp** — el mismo agujero
> del arnes que ya escondio el bug de los limites por IP.

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

## I.3 — El catalogo vendible — 1/5

### LA CURVA DEL CANARIO — MEDIDA 2026-08-02

> **El canario es UNA persona real desde Bogota**, con su propio token, medida
> aparte del promedio mientras la multitud navega. Es el usuario N+1.
> Tiempo de servidor, sin TLS ni establecimiento de conexion. Un droplet
> 4 vCPU / 8 GB, con todos los arreglos de hoy aplicados.

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

- [x] **Correr la curva del canario** buscando **el codo** — donde la linea deja
      de ser plana— y no el punto de rotura. **HECHO: el codo esta entre 2.500 y
      3.000 personas muy activas en una maquina de 4 nucleos.**
- [ ] **Nivel 1.000** — una maquina. Barato, honesto, **y se dice claro: si esa
      maquina cae, el evento se cae.**
- [ ] **Niveles 2.500 / 5.000 / 10.000+** — minimo dos nodos, sin punto unico de
      falla. **El salto de nivel no es de capacidad, es de promesa.**
- [ ] **Revisar la topologia desde cero** (no dar por bueno el dibujo de
      `COMO-CRECEMOS` §5). Decisiones abiertas: quien balancea —nginx propio
      (portable, pero se vuelve el nuevo punto unico) contra balanceador de
      Cloudflare (sin punto unico, ~$6/mes, ata a Cloudflare)—; que se separa
      primero; **y que "mas nucleos en la misma maquina" probablemente no
      sobrevive como nivel vendible, porque una maquina mas grande sigue siendo
      una sola maquina.**
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

## I.5 — Platform Health — 0/4

> **Kamilo lo pidio el 2026-04-19** (memoria `project_event_pulse_idea`), con su
> propia nota: *"sin esto, un error en produccion lo descubrimos porque un
> asistente se queja"* y *"hacerlo antes del primer evento en produccion"*.
> **Quedo enterrado bajo `PENDIENTES.md` §9 "Nice to have (NO hacer antes de
> cerrar deal septiembre)"** — una seccion atada a un deal que se cayo en julio.
> El deploy del 1-2 de agosto fue ese primer evento, y corrio sin esto.

- [ ] **Ya existe y no se esta usando:** `HealthController` con `/health` y
      `/version`, Sentry, Horizon. **Falta Laravel Pulse** (no instalado).
- [ ] Salud por modulo en vivo: API, socket, Redis, MySQL, colas.
- [ ] Peticiones con error (500, 429, timeouts) en tiempo real, por evento.
- [ ] Alertas: enterarse antes de que alguien se queje.

## I.6 — Antes de exponerselo a nadie — 0/3

- [ ] **Cloudflare a naranja.** Hoy en gris a proposito (para medir el servidor y
      no a Cloudflare), y el servidor esta desnudo: 125 intentos de bots
      buscando `/.env` desde 7 IPs.
- [ ] **Rotar las credenciales de R2** — quedaron escritas en la conversacion del
      2026-08-02. R2 no se destruye con los droplets.
- [ ] **2FA del staff.** Raya escrita por Kamilo: **la URL del admin no sale a
      ningun prospecto sin esto.** Ver `ROADMAP-SEGURIDAD-STAFF.md`.

---

## LO QUE SE ARREGLO HOY Y NO ES DE RENDIMIENTO

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
