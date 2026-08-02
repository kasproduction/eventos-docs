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

## El dato que falta y bloquea todo lo demas

**Nunca se midio cuantas peticiones genera una persona real por minuto.**

Toda la aritmetica de capacidad (incluida la de `DISPONIBILIDAD-HA.md`) se
apoya en ese numero, y no existe. Es lo PRIMERO que hay que responder: sin el,
"¿cuantos servidores para X personas?" no tiene respuesta honesta.

Sale de E1 y E6 midiendo peticiones/usuario/minuto en navegacion realista.

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
| E1-E8 | **0/8** — sin correr |
| Canario | sin implementar |
| Peticiones por usuario/minuto | **sin medir** (bloquea la aritmetica de capacidad) |

Herramientas ya construidas y reutilizables: `tests/load/runner.js` (un
escenario a la vez) · `socket-load.js` (cliente real con metricas) ·
`auth-security-check.js` · `ban-security-check.js` · k6 con 5.050 tokens.
