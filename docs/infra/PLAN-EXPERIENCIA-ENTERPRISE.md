# Plan de Experiencia Enterprise — escenarios de evento real

> **Decision Kamilo 2026-08-01**: los tests de carga existentes (TEST 1-9 de
> `PLAN-STRESS-TESTDO.md`) miden el eje equivocado. Son todos *"muchos
> usuarios jalando"*: warmup, 1K, 5K, 10K, punto de rotura.
>
> **Ninguno prueba el REPARTO** — una accion que se propaga a miles. Y ahi es
> donde muere una plataforma de eventos, porque es lo unico que no escala solo.
>
> Este plan reemplaza la pregunta. Deja de ser **"¿aguanta 5.000?"** y pasa a
> ser **"¿el 5.001 nota que hay 5.000?"**

---

## El principio: el canario

En **cada** escenario corre en paralelo un **usuario canario**: una persona
sola, real, cuya experiencia se mide aparte del promedio.

El canario abre la app, entra a la agenda, manda un mensaje — lo que haria
cualquiera. **Ese canario es el usuario 5.001.**

- Si en medio de la tormenta el canario navega bien → hay garantia enterprise.
- Si el canario sufre → el numero de conectados no vale nada.

**Los promedios mienten.** Con 5.000 usuarios, un p95 bonito puede esconder que
250 personas la estan pasando pesimo. El canario no se promedia con nadie.

## El objetivo, escrito

> **Cero degradacion durante la ventana del evento.**

No 99,999% anual — eso son 26 segundos al año, exige multi-region, y protege
las horas en que no hay nadie. A un organizador le importa que sus 8 horas
salgan perfectas.

### Umbrales del canario (propuesta, ajustar con Kamilo)

| Metrica | Verde | Amarillo | Rojo |
|---|---|---|---|
| Carga de pantalla (p95) | < 300 ms | < 800 ms | ≥ 800 ms |
| Conexion de socket | < 1 s | < 2,5 s | ≥ 2,5 s |
| Mensaje de chat ida y vuelta | < 500 ms | < 1,5 s | ≥ 1,5 s |
| Ver un cambio del admin | < 3 s | < 10 s | ≥ 10 s |
| Errores visibles | 0 | 0 | ≥ 1 |

**Rojo en el canario invalida el escenario completo**, sin importar lo que
digan los promedios.

---

## El dato que faltaba — MEDIDO 2026-08-02 (E1)

| | |
|---|---|
| Abrir la app | **8 peticiones** (las 8 consultas de la pantalla de inicio) |
| **1.000 personas quietas, 45 s** | **0 peticiones** |
| Por persona por minuto en reposo | **0,000** |

**Cero literal.** Queda VALIDADO lo que `DISPONIBILIDAD-HA.md` afirmaba y
estaba sin verificar: **la carga no es proporcional a cuanta gente hay sino a
cuantas ACCIONES ocurren.** Alguien con la app abierta sin tocarla no le
cuesta nada al servidor — la invalidacion por socket cumple.

### La formula, con datos medidos

```
techo del servidor    ~70 peticiones/segundo
abrir la app           8 peticiones
estar quieto           0 peticiones
```

**→ ~8,7 personas por segundo entrando sin degradar**
**→ ~525 personas por minuto**
**→ 5.000 personas entran comodas en ~10 minutos**

Respuesta vendible: *"si su evento abre puertas 10 minutos antes, 5.000
personas entran sin sentir nada"*. Si abren todos de golpe en 3 minutos, hay
cola.

**La palanca:** como el techo lo pone el costo fijo de ~45 ms por peticion,
bajarlo a la mitad DUPLICA la capacidad de llegada sin tocar hardware.

### E1 — resultado (1.000 personas)

1.000/1.000 sockets · 8.000 peticiones · **0 fallidas** · primera pantalla
p50 1,7 s (inflada porque el ritmo real de llegada dio 46 req/s, cerca del
techo; sin cola serian ~400 ms).

---

# Los ocho escenarios

## E1 — Se abren las puertas

**Qué**: 5.000 personas abren la app en 2 minutos. El pico real de cualquier
evento: la fila de entrada avanza y todos sacan el telefono.

**Mide**: tiempo hasta la primera pantalla · conexiones logradas · errores ·
**peticiones por usuario** (el dato que falta).

**Sospecha**: es el peor momento del dia. Cada persona hace login + carga de
home + conexion de socket, todo junto.

---

## E2 — El anuncio

**Qué**: con 5.000 conectados, el admin publica un anuncio.

**Mide**: **cuando lo ve el ULTIMO**, no el primero. Peticiones que dispara la
invalidacion. Si la API sobrevive al reparto.

**Sospecha**: un anuncio invalida cache y despierta a 5.000 clientes a la vez.
El jitter de 0-2s del cliente deberia repartirlo — **nunca se verifico con
5.000**.

---

## E3 — La agenda cambia · **el mas peligroso**

**Qué**: el admin mueve una sesion de sala u hora a mitad del evento.

**Mide**: la tormenta de refrescos. **5.000 clientes refrescando agenda casi
simultaneamente.**

**Sospecha**: 5.000 peticiones de golpe contra un servidor que sostiene ~70/s
son **70 segundos de cola**. Este escenario, solo, puede tumbar la experiencia
de todo el evento — y es una accion que un organizador hace sin pensarlo dos
veces.

**Es el que hay que correr primero.** Si algo esta roto, esta aca.

---

## E4 — El chat de la magistral

**Qué**: 1.000 personas en la misma sesion, escribiendo.

**Mide**: ida y vuelta del mensaje bajo carga · si el modo lento y los limites
por persona funcionan · CPU del proceso de sockets · si el reparto a 1.000
suscriptores del mismo room degrada.

**Sospecha**: cada mensaje se reparte a 1.000 conexiones. 10 mensajes/segundo
son 10.000 entregas/segundo. Es el unico escenario donde el socket, no PHP,
puede ser el cuello.

---

## E5 — La foto viral

**Qué**: subidas masivas y simultaneas al muro.

**Mide**: subida hasta R2 · si el limite de 10/min por persona protege sin
estorbar · si el bucket en Norteamerica y el servidor en Sao Paulo se sienten.

---

## E6 — El canario solo

**Qué**: control. El canario navegando **sin** carga encima.

**Mide**: la linea base contra la que se comparan todos los demas. Sin esto no
se sabe si 400 ms es malo o es simplemente lo que cuesta esa pantalla.

---

## E7 — Se cae algo a mitad del evento

**Qué**: matar el proceso de sockets · matar PHP-FPM · llenar la cola de
trabajos. Con gente conectada.

**Mide**: **cuanto tarda en recuperarse solo** · si los clientes reconectan ·
si se pierden mensajes · que ve el usuario mientras tanto.

**Sospecha**: PM2 revive el socket, pero **5.000 clientes reconectando a la vez
es E1 otra vez, sin aviso**. La reconexion masiva puede ser peor que la caida.

---

## E8 — El organizador trabajando

**Qué**: el admin usa Filament de verdad (listados, filtros, exports, edicion)
mientras el evento corre con 5.000 encima.

**Mide**: si el organizador puede operar · si sus consultas pesadas degradan la
experiencia de los asistentes.

**Sospecha**: el admin y la app comparten los mismos procesos PHP. Un export
grande puede robarle CPU a 5.000 personas. `stress-admin.js` ya existe y
**nunca se corrio**.

---

# Orden de ejecucion

Del que mas probable rompe al que menos:

1. **E3** la agenda cambia ← empezar aca
2. **E1** se abren las puertas (y sale el dato de peticiones/usuario)
3. **E4** el chat
4. **E7** se cae algo
5. **E2** el anuncio
6. **E8** el organizador
7. **E5** las fotos
8. **E6** control — correr al inicio para tener la linea base

## Metodo

El de hoy, que ya se probo: **una capa a la vez.** Se arreglo la estampida de
autenticacion y aparecio `worker_connections` detras. Cada capa que se quita
destapa la siguiente — no se puede planear todo de antemano.

Cada escenario: correr → medir el canario → si esta rojo, arreglar → repetir
hasta verde → pasar al siguiente. Y anotar en el informe **que se arreglo**,
no solo el numero final.

## Estado

| Escenario | Estado |
|---|---|
| **E3** la agenda cambia | **VERDE** — corrido, roto, arreglado y re-medido |
| **E2** el anuncio | **VERDE** — mismo patron, mecanismo verificado |
| **E4** el chat | **VERDE** — perfilado, diagnosticado y arreglado (ver abajo) |
| **E1** se abren las puertas | **VERDE** — y trae el dato que faltaba |
| **E7** se cae algo | **VERDE** — 100% recupera solo en ~11 s |
| **E8** el organizador | **VERDE** — no afecta a los asistentes |
| E5, E6 | sin correr |
| Canario | **implementado** (`e3-agenda-storm.js`) |
| Peticiones por usuario/minuto | **MEDIDO**: 8 por arranque · **0 en reposo** |

### E3 — resultado (2026-08-01, 3.000 conectados)

| | Antes | Despues |
|---|---|---|
| Peticiones que genera el cambio | **3.000** | **0** |
| Ultimo en tener la agenda | **52,5 s** | **0,20 s** |
| Latencia del refresco | p50 27,6 s | *no hay refresco* |
| **Canario p95** | **29.415 ms** | **74 ms** |
| Veredicto | **ROJO** | **VERDE** |

El arreglo: el aviso viaja CON el dato (`InvalidationService::sync` +
`data:sync`). Se reparte **1.272 bytes** por la conexion que ya existe en vez
de 3.000 peticiones HTTP. **260 veces mas rapido gastando menos.**

Dos decisiones lo hicieron viable: mandar **la sesion** (1,2 KB) y no la
agenda (34 KB); y descubrir que **la agenda no es igual para todos**
(`is_favorite` es por persona) — mandar la agenda completa le habria pisado
los favoritos a 3.000 personas con los del admin.

### E2 — mecanismo verificado

Anuncio para todos → `[sync] announcements bytes=238`.
Anuncio segmentado a un rol → `[invalidate]`, camino viejo.

**Segunda vez que aparece la misma leccion**: el dato no siempre es igual
para todos. Repartir un anuncio segmentado a toda la sala no seria lentitud,
seria una fuga. **Regla para los proximos escenarios: verificar SIEMPRE si el
dato es identico para todos ANTES de repartirlo.**

### E4 — resultado (2026-08-01, 1.000 en la sala, 100 escribiendo)

**El hallazgo NO fue de rendimiento: fue perdida de datos silenciosa.**

`postToLaravel` (eventos-socket/chat.ts) hablaba HTTP plano contra el puerto
80. En produccion la API es HTTPS en el 443 y el 80 responde un redirect.
Como el envio es fire-and-forget con `req.on('error', () => {})` **no habia
una sola señal**: ni error, ni log, ni trabajo fallido.

**Se enviaron 4.241 mensajes y la tabla quedo en 0 filas.**

En un evento real: cero historial de chat, export `chat_messages` del Data
Center vacio, moderacion sin nada que mostrar. En desarrollo funcionaba
porque ahi la API si es HTTP — por eso nunca se vio.

Arreglado y verificado: **1.817 mensajes guardados**. Ademas se le puso
registro al fallo; el silencio absoluto fue lo que dejo pasar 4.241 mensajes.

| Metrica | Valor |
|---|---|
| Ida y vuelta (todos) | p50 558 ms · **p95 1.877 ms** |
| **Canario** | p50 247 ms · **p95 1.779 ms** |
| Limite de tasa | 128 rebotes (sano) |
| `SESSION_NOT_JOINED` | 48 — sin investigar |
| Veredicto inicial | **ROJO** |

#### El perfilado dio la respuesta

    total=485ms  config=0ms  limite=4ms  espera_throttle=456ms  emit_a_1000=25ms

El tiempo NO se iba en configuracion (cacheada), ni en el limite de tasa
(4 ms de Redis), ni en el reparto en si (25 ms). Se iba **esperando turno**,
y esa espera crecia sola: 98 → 124 → 150 → ... → 475 ms.

**Mecanismo:** Node es de un solo hilo. Cada reparto a 1.000 sockets lo
bloquea ~25 ms. Con 40 mensajes/s entrando, el servidor necesita 40 × 25 =
**1.000 ms de trabajo por cada segundo** que pasa. No queda margen. Por eso
el CPU marcaba 0-4%: no estaba calculando, estaba **haciendo fila**.

(Correccion: antes se habia escrito que el throttle agregaba "como maximo
50 ms". Falso — era el 95% del problema.)

#### El arreglo: un proceso por nucleo + WebSocket puro

| Canario con 1.000 en la sala | Antes | Despues |
|---|---|---|
| Ida y vuelta p50 | **631 ms** | **30 ms** |
| p95 | **1.181 ms** | **43 ms** |
| Errores reales | — | **0** |

Los 4 nucleos estaban ociosos. Cada proceso entrega a ~250 en vez de 1.000.

**Por que NO se agrupo mensajes** (la idea "elegante"): tocaba la UI
optimista del chat en Expo y webapp. Al revisar salio que Expo, kiosko,
Mission Control, Event Pulse, chat-monitor, attendance-check y display **ya
usaban websocket puro** — solo la webapp tenia sondeo. Una linea, no una
reescritura.

**Por que NO `ip_hash`** (la solucion de manual de Socket.IO): en un evento
todos estan detras del wifi del recinto, o sea UNA IP publica. Los 1.000
caerian en el mismo proceso. La receta estandar falla justo en este caso.

#### Regresion que introdujo el cluster (cazada por Kamilo)

Pregunta suya: *"desde Mission Control o settings podemos limitar el chat,
no?"*. El modo lento por sesion y el cache de palabras bloqueadas viven en
la MEMORIA de cada proceso; la orden llega por HTTP a UNO solo:

· subir el modo lento → solo el 25% de la gente lo recibia
· bloquear una palabra ofensiva → el 75% la seguia viendo hasta 5 minutos

Lo segundo es una falla de **moderacion**. Arreglado con un canal Redis
(`socket:config`) que todos los procesos escuchan. Verificado: 4 de 4.

Y al verificarlo salio otro: con `pubClient.duplicate()` solo 2 de 4 quedaban
suscritos de verdad — los cuatro lo decian en el log pero
`PUBSUB NUMSUB` reportaba 2. **El log mentia.** Causa: era el unico cliente
Redis sin manejador de errores, se suscribia antes de registrar el manejador
de mensajes, y no se re-suscribia al reconectar.

### E7 — resultado (2026-08-02, 1.321 conectados)

Se mataron los CUATRO procesos del socket de golpe, con gente adentro.

| | |
|---|---|
| Se cayeron | 1.321 (todos) |
| **Recuperados** | **1.321 — 100%** |
| Colgados sin volver | **0** |
| Tiempo de recuperacion | **~10,7 s** para todos |

La sospecha era que la reconexion masiva fuera peor que la caida. **No paso**,
y la razon es directa: la cache de auth arreglada hoy hace que reconectar no
le cueste peticiones al backend. Ese arreglo se pago solo aca.

### E8 — resultado (2026-08-02, 800 dentro + organizador trabajando)

**El trabajo del admin NO toca la experiencia de los asistentes.** El canario
se mantuvo en **3-4 ms con 0 errores** en las tres corridas. La sospecha —que
un export pesado le robara CPU a miles— queda descartada. La latencia del
propio admin tambien esta bien: mediana 40 ms.

**Pregunta de producto que destapo, sin responder:** el limite de 60
peticiones/minuto **por persona** aplica tambien al organizador. Las tablas de
Filament paginan, filtran y refrescan; un organizador activo puede pasarse
solo y recibir 429. Medirlo con una cuenta real antes del demo.

Dos defectos del arnes corregidos en el camino (ambos reportaban como falla
del servidor lo que era del test): apuntaba a `/admin/events/{id}/attendees`,
ruta inexistente; y buscaba la cuenta de organizador muestreando 20 tokens —
lo que dejo de funcionar con mi propio arreglo de E4, porque con UN solo admin
entre 5.050 la probabilidad de hallarlo en 20 intentos es del 0,4%.

### Lo que el patron NO cubre (deliberado)

Crear o borrar sesiones · sesion que cambia de dia · anuncios segmentados.
Todo eso sigue por invalidar y refrescar. El cliente no puede parchar lo que
no tiene, y son casos raros a mitad de evento.

Falta: la webapp ya lo implementa (`AgendaView`), pero **sin QA vivo**.

Herramientas ya construidas y reutilizables: `tests/load/runner.js` (un
escenario a la vez) · `socket-load.js` (cliente real con metricas) ·
`auth-security-check.js` · `ban-security-check.js` · k6 con 5.050 tokens.
