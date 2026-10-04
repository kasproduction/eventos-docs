# Linea Vitrina — lo que vende y recibe antes del evento

> Aprobada por Kamilo el 2026-10-01 al ver `design/features/landing/lab-hero-elegidos.html`.
> Aplica a todo lo que esta **fuera de la app del evento**: landing por cliente, widget de registro,
> summit demo de EventOS, paginas comerciales. La app (webapp + Expo) sigue con Lumina y su espejo.
> Publico: CEOs, ejecutivos mayores, gente fancy. Elegante, con impacto, cero cliche.

## La esencia (Kamilo 2026-10-01: "exactamente")

> Despues de una ronda de labs llenos de efectos que Kamilo llamo "horrible, enredado".
> Filosofia de Kamilo: **menos elementos, mas significado.**

1. **La pagina es una invitacion, no una vitrina.** Un momento fuerte al abrir (el hero) y despues
   calma: tipografia impecable, espacio, fotos reales, contenido claro.
2. **Lo que un CEO necesita en 30 segundos, en este orden:** que es y por que ir, quien habla
   (prestigio), cuando y donde, registrarse en un paso. Lo abre en el celular, desde la invitacion
   que le reenvio su asistente.
3. **Lo esencial visible sin interaccion** (nombre, cargo, empresa). El detalle extra (la frase, que
   trae el patrocinador) puede llegar con un gesto sutil, como el giro de Noir; en celular, con un toque.
4. **Una sola firma por plantilla, usada con medida.** Noir y Lux se diferencian por material, luz y
   ritmo de composicion, no por una coleccion de efectos.
5. **Primero el celular**, despues el escritorio.

Descartado por enredado (2026-10-01): color que entra desde el cursor (circulo), galeria con
flechas, retrato que sigue al cursor, celular que cambia solo, un truco distinto por seccion.
Lo que funciono: el hero, el boleto con la frase y **Noir completo de `lab-landing-v3.html`**.

**Correccion el mismo dia:** aplicar la esencia de mas tambien falla. Una v4 quieta y plana fue
"generica". El **giro sutil de las lamas de Noir (speakers y patrocinadores) es la referencia
aprobada**: un gesto con alma suma, lo que sobra es amontonar efectos distintos. Cuando se rechaza
una pieza, se cambia solo esa pieza.

## Los dos heros que la definen

| Version | Escena | Que hace |
|---|---|---|
| Oscura (tech) | **Paisaje de datos** | Campo de columnas en 3D que crece al abrir, como un grafico que se vuelve ciudad. Las cimas toman el color del cliente; bajo el cursor el terreno sube. |
| Clara (humana) | **Fachada cinetica** | Pared de lamas color yeso con luz de tarde y sombras suaves. Al abrir giran en ola desde el centro; con el cursor y un barrido cada 6 s muestran el color del cliente por detras. |

## Reglas

1. **El fondo es un sistema, no una decoracion.** Una superficie hecha de muchas piezas iguales
   (columnas, lamas) con luz real. Nunca figuras literales ni dibujos de "tecnologia".
2. **El color del cliente es la recompensa.** Aparece en los picos o al interactuar; no pinta toda
   la pantalla. Lo demas es grafito y niebla (oscuro) o yeso calido y luz de tarde (claro).
3. **Oscuro = estructura y datos. Claro = arquitectura y material.** El mismo lenguaje de piezas en
   las dos versiones; solo cambia el material y la luz.
4. **Un momento al abrir, despues calma.** Entrada de 1,5 a 3 s (crecer, girar en ola). Luego quieto
   con un evento lento y periodico. El cursor se responde con resortes, nunca brusco.
5. **El fondo nunca compite con el contenido.** Texto sobre degradado lateral; formulario en tarjeta
   solida (el boleto).
6. **Tipografia:** Plus Jakarta Sans en titulos (apretado negativo), Urbanist en todo lo demas.
   **Cero mono, cero dots, cero mayusculas espaciadas.** Etiquetas en caja normal y gris suave.
   Numeros con `tabular-nums`; cuentas en palabras ("33 dias 10 h").
7. **Copy en tuteo de Colombia.**
8. **Personalizable por cliente sin redisenar:** cambian acento, logo y textos; la escena se queda.
9. **Tecnica:** three.js (geometria instanciada, sombras suaves, DPR max 1,5), GSAP para la interfaz,
   `prefers-reduced-motion` = estado final quieto, imagen fija si no hay WebGL.

## Prohibido (ya probado y descartado)

Particulas (con o sin figuras), ondas y lineas de fondo, seda, haz de luz, metal liquido, curvas de
nivel, vidrio y refraccion, texto que cambia de caracteres (es firma de KasProduction), tablero de
salidas, papel, editorial plana, campaña con foto, globo con arcos, tipografia mono.
**Persiana de lamas** (retrato o tarjeta que se abre en franjas que giran): Kamilo 2026-10-01 "no me
gusta y no lo voy a usar en nada". Prohibida en todo.
**Regla de descubrimiento:** si una pieza esconde informacion detras de un gesto, tiene que AVISAR que
hay algo (la gente no sabe que al darle clic pasa algo).
Tambien probados sin eleccion (no reusar sin pedirlo): plano de la sala, instrumento, cinta de
estadio, cartel suizo, objetos de arcilla, galeria de arcos, la firma, campo de color
(`lab-hero-tech.html`, `lab-hero-claro.html`).
