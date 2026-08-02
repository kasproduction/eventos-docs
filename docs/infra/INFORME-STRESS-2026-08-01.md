# Informe de carga — EventOS sobre un droplet — 2026-08-01

> Primera medicion real del producto sobre infraestructura de produccion.
> Responde la pregunta que llevaba abierta desde el pivote: **cuanta gente
> aguanta la maquina que efectivamente se le vende a un cliente.**

## Que se monto

| | |
|---|---|
| **Servidor medido** | DigitalOcean sao1 · 4 vCPU / 8 GB · Ubuntu 24.04 LTS |
| **Generador de carga** | Identico, maquina aparte (nunca en la misma) |
| **Dominio** | killjoy.pro — HTTPS real con Let's Encrypt, **sin proxy de Cloudflare** |
| **Stack** | nginx · PHP 8.3-FPM · MySQL 8 · Redis 7 · Node 20 · PM2 · Horizon |
| **Aplicacion** | Backend Laravel + admin Filament + 4 SPAs + webapp Next.js + servidor de sockets — **todo en la misma maquina**, como se vende |
| **Datos** | Evento demo real + **5.050 asistentes** con token propio (uno por persona, como en un evento de verdad) |

Se monto completo a proposito. Recortar piezas habria dado un numero
optimista y habria dejado el trabajo pendiente para despues.

---

## El resultado

### HTTP

| Usuarios simultaneos | Mediana | p95 | Caudal | Errores |
|---|---|---|---|---|
| 20 | **53 ms** | 187 ms | 40 req/s | 0% |
| 50–100 | 363 ms | 1,35 s | 59,6 req/s | 0% |
| 250 | 3,22 s | 3,99 s | 67,6 req/s | 0% |
| 500 | 6,56 s | 7,87 s | **69,9 req/s** | 0% |

**El techo son ~70 peticiones por segundo.** A partir de ahi el caudal no
sube ni un punto: solo crece la cola. Duplicar los usuarios duplica la
espera y no entrega una peticion mas.

Nunca hubo errores. El servidor no se cae — se pone lento, que es
preferible: la gente espera, no ve una pantalla rota.

### Sockets (donde vive un evento en directo)

| Conexiones pedidas | Logradas | Tiempo de conexion (p50) | Ida y vuelta del chat (p50) |
|---|---|---|---|
| 200 | **200 (100%)** | 892 ms | **342 ms** |
| 1.000 | **977 (98%)** | 2,9 s | 346 ms |
| 2.500 | 1.468 (59%) | 751 ms | 732 ms |

**~1.000 conexiones simultaneas se sostienen sin perder ninguna.** Hacia
2.500 la mitad no logra entrar. Ninguna conexion ya establecida se cayo en
ningun escenario.

---

## Donde esta el limite, exactamente

Con 500 usuarios encima, el servidor mostraba:

```
CPU libre ......... 0%        ← clavado
RAM libre ......... 6,1 GB    ← ni se despeina
MySQL activos ..... 2 a 8 hilos
PHP-FPM ........... 61 procesos (tope 60)
```

**El cuello de botella es CPU, y es PHP.** Ni la base de datos, ni la
memoria, ni la red.

La cuenta cierra sola: la mediana sin saturar es 53 ms, y 4 nucleos entre
53 ms dan ~75 peticiones por segundo teoricas. Medimos 70. **La aplicacion
es eficiente; lo que se acabo fueron los nucleos.**

De ahi salen dos conclusiones practicas:

1. **Subir `pm.max_children` no serviria de nada.** Serian mas procesos
   peleando por el mismo CPU saturado, con peor latencia.
2. **Para crecer hay que sumar nucleos, no memoria.** Con 6 GB libres, este
   perfil podria correr en una maquina de menos RAM y mas CPU por el mismo
   precio.

### El hallazgo no obvio: cada conexion de socket cuesta una peticion HTTP

El servidor de sockets valida el token de cada conexion nueva llamando a
`/auth/me` del backend. Verificado en el registro de nginx: **1.468
conexiones logradas = 1.468 peticiones a `/auth/me`**, con un pico de **76
por segundo — justo el techo HTTP de la maquina.**

Consecuencia real: si mil personas pierden el wifi y reconectan a la vez,
esa reconexion masiva genera mil peticiones al backend y se come sola toda
la capacidad HTTP. El chat y la navegacion compiten por el mismo CPU.

No es un bug, es una caracteristica del diseño que hasta hoy nadie habia
visto. Mitigacion natural para el futuro: que el socket valide el token sin
pegarle al backend en cada handshake.

---

## Cuanta gente es esto, en cristiano

**~1.000 personas con la app abierta al mismo tiempo**, que es el limite del
lado de los sockets y coincide con lo que da el lado HTTP.

Para dimensionar de verdad hay que separar dos cosas:

- **Inscritos al evento**: pueden ser varios miles. No todos abren la app a
  la vez.
- **Simultaneos activos**: los que estan usando la app en el mismo minuto.
  Este es el numero que importa, y es ~1.000.

**Un evento de 1.000 a 2.000 inscritos entra comodo en un droplet de $48 al
mes.** De 5.000 para arriba hay que sumar nucleos o repartir en dos
maquinas (la arquitectura ya esta documentada en `DISPONIBILIDAD-HA.md`).

El punto delicado no es el promedio sino el pico: cuando arranca la
conferencia magistral y todo el mundo abre la app en el mismo minuto. Ahi
la cola se siente. Ese es el escenario a vigilar, no el uso sostenido.

---

## Lo que se gano en el camino

**OPcache con JIT + cachés de configuracion y rutas de Laravel** — pasos de
produccion que faltaban:

| | Antes | Despues |
|---|---|---|
| Mediana | 564 ms | **363 ms** (−36%) |
| p95 | 1,86 s | **1,35 s** (−27%) |
| Caudal | 47,5 req/s | **59,6 req/s** (+25%) |

Un 25% de capacidad gratis, sin tocar una linea de codigo de la aplicacion.

---

## Bugs reales encontrados

Tres del producto (los tres habrian llegado a un cliente):

1. **El leaderboard devolvia 500 en el 86% de las peticiones.**
   `only_full_group_by` esta ACTIVADO por defecto en MySQL 8 y desactivado
   en el Laragon local. Lo hermoso del caso: la rama que falla **solo corre
   cuando el asistente no esta en el top 50**. Con los 50 asistentes del
   seed de desarrollo, todos estaban dentro y el codigo nunca se ejecutaba.
   Aparece recien con miles de personas — o sea, exactamente en un evento
   real. Arreglado (`fc4cbbb`).
2. **El backend no se podia instalar en produccion.** `composer install
   --no-dev` reventaba: Telescope es dependencia de desarrollo pero su
   provider estaba registrado siempre. Arreglado (`0e31e32`).
3. **Guardar cualquier contenido con HTML fallaba en un servidor nuevo.**
   HTMLPurifier apuntaba su cache a un directorio que nadie creaba.
   Arreglado (`5f2f575`).

Y cuatro del arnes de medicion, que estaban dando numeros falsos:

4. **Los tokens se repartian mal**: cada usuario virtual usaba siempre la
   misma cuenta. Como el limite de tasa de la API es **por usuario** (60/min)
   y no por IP, el test medi­a su propia agresividad en forma de 429.
   Arreglado — ahora cada iteracion toma una persona distinta.
5. `stress-local.js` seguia pegandole a `/banners`, feature eliminada de
   raiz en julio. 404 en el 100% de las peticiones, inflando el error ~10
   puntos.
6. **`stress-full.js` no corre en un generador de 8 GB**: k6 reserva ~3,3 MB
   por usuario virtual y sus 2.400 usuarios piden ~8 GB solo de arranque. El
   kernel lo mataba antes de la primera peticion. Se resolvio con un
   lanzador por escenario (`runner.js`); correrlo entero exige 16 GB.
7. **El motor de sockets de artillery no manda el token** en el handshake:
   reportaba miles de usuarios creados mientras el servidor rechazaba todas
   las conexiones. Se reemplazo por un cliente propio (`socket-load.js`).

---

## Para la guia de deploy (esto no estaba escrito en ningun lado)

- **`X-Forwarded-Host` y `X-Forwarded-Port` son obligatorios** en el proxy de
  la webapp. Sin ellos Next.js filtra el puerto interno y redirige a
  `app.dominio.com:3000`, que desde afuera no existe.
- **`LARAVEL_API_URL` del socket lleva el `/api/v1`.** Sin el, ninguna
  conexion autentica y el error que se ve es "token invalido", que despista.
- `APP_URL` tiene que ser el dominio real desde el primer arranque: las
  imagenes de los correos se guardan con URL absoluta.
- Los repos son privados y se subieron desde la maquina local con
  `git archive`. **Para el deploy definitivo hay que poner llave de
  despliegue o token de GitHub** en el servidor.
- El log que sirve es el diario (`laravel-AAAA-MM-DD.log`), no `laravel.log`.
- `php artisan security:check` paso en verde antes de exponer nada.

---

## Que NO responde esta prueba

- **Nada sobre 10.000 personas.** Eso exige la arquitectura de
  `DISPONIBILIDAD-HA.md`: dos droplets, MySQL y Redis manejados, balanceador.
- **Nada sobre la red real de un asistente.** Todo se midio dentro del mismo
  centro de datos, a proposito, para saturar el servidor y no medir el cable.
  La latencia de 4G en Colombia es otra prueba.
- **Nada sobre el admin bajo carga.** `stress-admin.js` quedo sin correr.
- La webapp Next.js quedo montada y sirviendo, pero la carga se dirigio a la
  API y a los sockets, que es donde estaba la pregunta.
