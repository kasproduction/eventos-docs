# Cómo crecemos — arquitectura de escalado

> Escrito el 2026-08-02 **desde lo medido**, no desde lo supuesto.
> El plan viejo (`DISPONIBILIDAD-HA.md` §2) decía "2 droplets iguales detrás de
> un balanceador". Las mediciones de esta sesión dicen que ese no es el primer
> paso, y explican por qué.

---

# 1. Lo que cambia el plan viejo

Cuatro hechos medidos que el plan original no tenía:

**a) El cuello es CPU, no memoria.** 0% de CPU libre con **6,1 GB de RAM sin
usar**. Crecer = sumar núcleos. Comprar RAM no hace nada.

**b) HTTP y sockets tienen costos OPUESTOS.**

| | Consumo real |
|---|---|
| **5.000 sockets conectados** | ~400 MB RAM · **0-4% de CPU** |
| **70 peticiones HTTP/segundo** | **100% de CPU** |

Los sockets son casi gratis. **Lo caro es HTTP.** Eso rompe la idea de "dos
droplets idénticos": no hay que clonar la máquina, hay que separar los roles.

**c) MySQL y Redis nunca fueron el problema.** MySQL osciló entre 2 y 8 hilos
activos con 5.000 personas encima. Redis, ni se despeinó. **Sacarlos del
droplet no mejora nada de rendimiento** — se hace solo para que dos máquinas
puedan compartir estado.

**d) Ya no hace falta afinidad de sesión.** Al pasar todo a WebSocket puro
(hoy), desapareció la necesidad de pegar cada persona a una máquina fija.
**Cualquier balanceo funciona.** Eso era el obstáculo técnico principal para
crecer, y ya está resuelto.

Y un quinto hecho que cambia toda la aritmética: **una persona quieta cuesta
cero.** La carga no depende de cuánta gente hay, sino de cuántas acciones
ocurren.

---

# 2. Hoy — un droplet, todo adentro

```
                    Internet
                       │
                 ┌─────┴─────┐
                 │ Cloudflare │  ← hoy en GRIS
                 └─────┬─────┘
      ┌────────────────┴────────────────┐
      │      Droplet · 4 vCPU / $48      │
      │  ─────────────────────────────   │
      │  nginx                            │
      │  PHP-FPM ······· el cuello        │
      │  Node × 4 ······ casi gratis      │
      │  MySQL ········· ocioso           │
      │  Redis ········· ocioso           │
      │  Horizon                          │
      └──────────────┬───────────────────┘
                     │
              Cloudflare R2
```

**Aguanta 1.000-2.000 personas navegando activamente.** Aislamiento total: si
cae el VPS de un cliente, no arrastra a los demás — la decisión de un droplet
por evento sigue siendo correcta.

---

# 3. Etapa 1 — optimizar antes de comprar (gratis, duplica)

**Una ruta que NO EXISTE cuesta ~45 ms. Una que consulta la base, ~52 ms.**
El trabajo de tu aplicación son 7 ms; los otros 45 son peaje del framework.

Bajar ese costo a la mitad **duplica la capacidad de todo** —llegada,
navegación, todo— **sin comprar un solo núcleo**.

Dónde mirar (sin diagnosticar aún, esto es la lista de sospechosos):

- la pila de middleware que corre en cada petición
- el arranque de proveedores — la app registra ~20 observadores
- la sesión, que se levanta incluso en rutas de API que no la necesitan

**Es la palanca más rentable que queda y no cuesta nada.** Cualquier peso
gastado en hardware antes de esto es pagar por no optimizar.

---

# 4. Etapa 2 — más núcleos en la misma máquina

Lo más simple que existe: DigitalOcean redimensiona CPU sin rehacer nada.

| | Capacidad estimada |
|---|---|
| 4 vCPU (hoy) | ~1.000-2.000 navegando |
| 8 vCPU (+$48/mes) | ~2.000-4.000 |
| 16 vCPU (+$144/mes) | ~4.000-8.000 |

**Sin cambiar una línea de código ni de arquitectura.** Y si además se hizo la
etapa 1, cada número se duplica.

**Hasta ~5.000 personas navegando, este es el camino.** No hay razón para
complicar la arquitectura antes.

---

# 5. Etapa 3 — separar los roles (el paso real de arquitectura)

Recién cuando un solo droplet no alcanza. **Y no es clonar la máquina: es
separar por costo.**

```
                         Internet
                            │
              ┌─────────────┴─────────────┐
              │  Cloudflare Load Balancer  │
              │  chequeo cada 10s          │
              │  round-robin + failover    │
              └──────┬──────────────┬──────┘
                     │              │
          ┌──────────┴───┐   ┌──────┴───────┐
          │  API-1       │   │  API-2       │   ← lo CARO: se replica
          │  nginx+PHP   │   │  nginx+PHP   │      (sin estado, escala lineal)
          └──────┬───────┘   └──────┬───────┘
                 │                  │
                 └────────┬─────────┘
                          │
        ┌─────────────────┼─────────────────┐
        │                 │                 │
 ┌──────┴──────┐  ┌───────┴──────┐  ┌───────┴───────┐
 │ SOCKETS     │  │ Redis        │  │ MySQL         │
 │ 1 droplet   │  │ administrado │  │ administrado  │
 │ chico basta │  │ + TLS        │  │ + réplica     │
 └─────────────┘  └──────────────┘  └───────────────┘
                          │
                   Cloudflare R2
```

### Por qué así y no dos droplets iguales

**Un solo nodo de sockets sirve a todos los nodos de API.** Medimos 5.000
conexiones con 0-4% de CPU: un droplet chico de $12-24 sostiene decenas de
miles. Clonarlo sería pagar por capacidad que sobra.

**Los nodos de API se replican porque son los caros y no guardan estado.** Cada
uno que sumás es capacidad lineal.

### Qué hay que cambiar de verdad (no es solo comprar)

| Cambio | Por qué |
|---|---|
| **Redis administrado, con TLS** | Hoy escucha en localhost. Al cruzar la red lleva sesiones y las fichas de autenticación — sin TLS sería exponerlas |
| **MySQL administrado** | Con dos nodos, cada uno tendría su propia base. Obligatorio |
| **Réplica de lectura** | Para que los exports pesados del Data Center no le roben CPU al evento |
| Balanceador de Cloudflare | Reparte y saca de rotación al que falle |

### Lo que YA está resuelto y no hay que tocar

- **El adaptador de Redis** — un mensaje emitido en un nodo llega a los
  suscriptores del otro. Probado hoy con 4 procesos.
- **WebSocket puro** — no hace falta afinidad de sesión, cualquier reparto sirve.
- **La caché de autenticación vive en Redis** — compartida entre nodos por diseño.
- **Los archivos ya están fuera** (R2). No hay estado en disco.

**Ese trabajo ya se hizo hoy, sin querer.** Era el obstáculo real para crecer.

---

# 6. Round-robin, failover y qué pasa si uno cae

El balanceador de Cloudflare hace las dos cosas: reparte por turnos entre
orígenes sanos y **saca de rotación al que falle dos chequeos seguidos** (~30
segundos).

**Y esto no es teoría — E7 lo midió.** Matamos los 4 procesos de socket con
1.321 personas conectadas:

| | |
|---|---|
| Recuperados | **1.321 — el 100%** |
| Colgados sin volver | **0** |
| Tiempo | **~11 segundos**, solos |

Con dos nodos es aún mejor: los usuarios del que cae se reparten al otro, y
**la mitad del evento ni se entera**.

Un detalle que solo se sabe por haberlo medido: **la reconexión masiva no
tumbó nada** porque la caché de autenticación hace que reconectar no le cueste
peticiones al backend. Sin ese arreglo, la avalancha habría sido peor que la
caída.

---

# 7. Costos reales

| Etapa | Capacidad | Costo/mes |
|---|---|---|
| **Hoy** — 1 droplet | 1.000-2.000 navegando | **$48** |
| **+ Etapa 1** (optimizar) | 2.000-4.000 | **$48** — gratis |
| **Etapa 2** — 8 vCPU | 4.000-8.000 con etapa 1 | **~$96** |
| **Etapa 3** — roles separados | 10.000+ | **~$200-250** |

Desglose de la etapa 3: 2× API ($96) + sockets ($24) + MySQL administrado con
réplica ($30) + Redis administrado HA ($15) + balanceador ($6) + Cloudflare Pro
($20) ≈ **$191**.

---

# 8. Cuándo NO crecer

**Para 5.000 personas no hace falta arquitectura nueva.** Un droplet más grande
lo hace. Sumar máquinas antes de atacar los 45 ms es pagar por no optimizar.

Y sobre disponibilidad: **99,999% son 26 segundos de caída al año**, exigen
multi-región y protegen las horas en que no hay nadie usando la app. Para una
plataforma de eventos la métrica correcta es **cero degradación durante la
ventana del evento** — alcanzable, verificable, y lo que un cliente compra.

---

# 9. El orden, en una línea

1. **Optimizar los 45 ms** — gratis, duplica todo
2. **Cloudflare en naranja** — gratis, protege
3. **Más núcleos** cuando haga falta — simple, sin arquitectura
4. **Separar roles** solo pasadas las ~8.000 personas navegando

Cada paso se justifica con una medición, no con una intuición. Y cada uno se
puede verificar con los mismos 8 escenarios que ya existen.
