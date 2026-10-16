# Pendientes — Webapp y salida a produccion

> Vista operativa unica: SOLO lo abierto. Lo cerrado se mueve a `COMPLETADO.md` el mismo dia que se cierra
> (regla retomada 2026-10-16: desde el 1 de agosto no se movia nada y este archivo llego a 1.448 lineas).
> [ ] = pendiente. Al cerrar un item: marcarlo, y en el `/guardar` moverlo a COMPLETADO con fecha y commit.

---

## ESTADO GENERAL AL 2026-10-16

> Evento: **domingo 6 de diciembre de 2026, 5.000 personas.** Faltan 51 dias. Ensayo general 28-29 de noviembre.

| Area | Avance | Que falta |
|---|---|---|
| Modulos del asistente (webapp W.0-W.18 + W.X) | 550/585 = **94%** | W.19 Spatial Player (mejora) y QA presencial |
| Registro publico, landing y widget | 26/32 = **81%** | Llaves reales de Turnstile, toggle de tema para el demo, 4 menores (`PENDIENTES.md` §3) |
| Admin y modulos del organizador | **100%** | Nada bloqueante |
| Seguridad del staff (2FA) | 25/26 = **96%** | S.7.2: correr `security:check` en el servidor real |
| App Expo | Implementada | Recap sin commit (rompe typecheck), QA en telefono, tiendas sin empezar |
| QA presencial con Kamilo | **0%** | Mobile parity, W.12 Fase C, pelicula W.X |
| Bintec 2026 (B.1-B.6) | 0/6 = **0%** | Menos peticiones primero |
| **Salida a produccion** (plan B1-B6, `ROADMAP-INFRAESTRUCTURA.md`) | ~4/31 = **13%** | Servidor, pipeline, alertas, medir 5.000, runbook |

**Lectura:** el producto esta casi completo; lo que no esta es correrlo en un servidor. Lo medido llega a
300 personas por maquina (Nivel 1, agosto). B3.1 correo y B3.2 la puerta tienen codigo pero no medicion.
El plan de servidor esta EN PAUSA por decision de Kamilo (2026-09-30) hasta cerrar features.

---

## RUTA A DICIEMBRE — lo abierto, en orden

> Criterio que manda (Kamilo): *"si en este momento me dijeran tenemos evento mañana, ¿que podria montar sin
> sufrir ni improvisar?"*. Detalle y calendario por semana: `ROADMAP-INFRAESTRUCTURA.md` "ESTADO Y PENDIENTES".
> Historial de la ruta de septiembre (etapas 0-7 originales): `COMPLETADO.md` seccion 2026-10-16.

**1. Admin que se entrega a un cliente**
- [ ] S.7.2 `security:check` contra el `.env` real del servidor (lo unico que le falta al 2FA)

**2. Montar sin improvisar** (B2, 0/5)
- [ ] I.7.3 Paquete por version (reemplaza el rsync desde una semilla)
- [ ] I.7.4 Terraform por nivel + I.7.5 pipeline etapas 1-6 con compuertas
- [ ] I.7.6 Humo funcional automatico (incluye correo de acceso recibido)
- [ ] Correo transaccional listo por cliente: clave Resend, dominio verificado
- [ ] Alerta externa (capa 1 de I.5) + respaldo fuera de DigitalOcean (I.4)
- [ ] I.7.8 Entrega + runbook del dia del evento ("si pasa X, haz Y")
- [ ] Cloudflare LB sigue cobrando $5/mes sin nada detras: cancelar o usar (hallazgo 2026-09-13)

**3. La puerta del publico**
- [ ] Llaves reales de Turnstile antes de abrir el registro (`PENDIENTES.md` §3)
- [ ] Medir en el servidor B3.1 correo y B3.2 la puerta (`tests/load/entrar-por-la-puerta.js`)

**4. QA presencial con Kamilo**
- [ ] Mobile parity M.2-M.8 en telefono real (heart, momentos, shuffle, trivia sponsor, desafio, anuncios,
      soporte, orb FAQ, encuestas, vendor con camara por HTTPS)
- [ ] W.12 Fase C: device, Edge/Firefox, Lighthouse, WCAG, E2E cross-tab (bloque W.12 abajo)
- [ ] W.X QA final de la pelicula de corrido

**5. Ensayo general**
- [ ] Montar con el pipeline el nivel del evento, I.7.7 carga del tamaño real + caida de un nodo por rol,
      y desmontar. Con eso "evento mañana" = si

**6. W.19 Spatial Player** (BLOQUE 8 abajo, 0/9) — mejora, segundo plano. Arquitectura propuesta sin aprobar.

**7. Tiendas (ultimo, decision Kamilo)**
- [ ] EAS build de produccion Android + iOS, cuentas, envio y revision. **Riesgo:** la revision toma dias
      o semanas; si el cliente pide app en tiendas, dejar margen
- [ ] Pipeline restante: I.7.9 hermanos (actualizar, modo registro/evento, destruir) + I.7.10 punta a punta


### BINTEC 2026 — lo que sale de la referencia (Kamilo 2026-10-14) — 0/6

> Fuente de todo: `docs/analysis/REFERENCIA-BINTEC-2026.md`. Decision de enfoque (Kamilo):
> menos features nuevas, mas que lo existente cargue liviano y entre sin friccion.
> Orden propuesto: B.1 primero (lo que mas pesa para el 6 de diciembre); B.2 siguiente frente,
> con /davinci (Kamilo dio "dale" a arrancar el analisis, sin codigo).

- [ ] **B.1 Menos peticiones por pantalla.** Medir cuantas hace hoy cada pantalla (~9 medidas en
  agosto). Empezar por la agenda: 1 peticion por dia ya armada para pintar (sede → sala → sesiones,
  horas y textos calculados), cache CDN 30-60 s, "en vivo" por socket. Luego el resto de pantallas.
- [ ] **B.2 Juegos a demanda por QR.** Hueco: nuestra gamificacion es EN VIVO (trivia del streaming,
  momentos, MC); falta la capa que la persona juega cuando quiere en el stand. Un QR = un juego corto
  (trivia, memoria, ahorcado, reflejos), un intento que suma al mismo ranking, repeticiones por
  diversion, recuperar partida si se cae la red, modo vista previa para staff, silenciar sonido.
  Sin socket, 3-4 peticiones por juego. Base a revisar: golden tickets, pasaporte, trivia. Lab antes de codigo.
- [ ] **B.3 Cero friccion de entrada.** El QR del stand suma con la sesion que ya existe; nunca otro
  login (Bintec Play no dejo entrar a Kamilo).
- [ ] **B.4 Ficha de sesion:** triple estado (me interesa / favorito / asistire presencialmente) y
  Outlook Calendar ademas de Google. Verificar que tenemos hoy antes de agregar.
- [ ] **B.5 Cabeceras de seguridad:** revisar CSP, X-Frame-Options, nosniff y Referrer-Policy en
  eventos-web y la API. Y que ningun registro use un dato conocido (cedula) como contraseña.
- [ ] **B.6 Respaldo de chat para eventos grandes:** evaluar Ably (o similar) solo si la medicion
  de 5.000 muestra que nuestro socket no aguanta. Decision por evento, no reescritura.


### EL EVENTO DE NOVIEMBRE ES DE 5.000 PERSONAS (dato Kamilo 2026-09-13)

> **Lo medido llega a 300 activas** (Nivel 1, agosto). 5.000 personas cae en el
> **Nivel 3 (hasta 2.500 activas, ~$700-1.050/mes) o Nivel 4 (5.000-10.000
> activas, ~$1.300-2.200/mes)** del catalogo, y los dos son DERIVADOS, no
> medidos. Cual de los dos depende de cuantas estan activas a la vez, que es
> justo I.2 (sin medir). **Montar para 5.000 sin medir = improvisar.**

**Infraestructura abierta, reclasificada para 5.000 personas:**

| Para que sirve | Items |
|---|---|
| **Imprescindible para noviembre** | I.7.1 instalacion limpia · I.7.2 wizard · 2FA · I.4 respaldo fuera de DO · I.5 capa 1 alerta externa · I.1b limitador por fallos + rutas publicas por recinto · I.2 medir el ritmo real (decide Nivel 3 o 4 y el costo) · montar Nivel 3/4 y medirlo con carga del tamaño real (login en ola, 5K, red 4G Colombia, caida de nodo con carga, export aislado, admin trabajando con carga D.4.4) · replica de lectura + ROL=admin (Nivel 3 la incluye) · correo masivo con ritmo controlado (hallazgo 8) · juegos en vivo: limitar difusiones + cache del grupo elegible · I.7.3-I.7.8 pipeline con humo, carga y entrega · torre de control + alertas por umbral + logs centralizados + escalar con un comando (con 11+ maquinas, entrar por ssh no es viable) · verificar el 503 de la webapp con una persona |
| **Mejora, no bloquea** | Panel en vivo del servidor (Pulse) · canario automatico · que el tiempo real refresque solo lo que cambio · precarga completa · cache de la sesion en Next · modo registro/evento · runbook y game day periodico · consultas duplicadas y conteos del branding |
| **Despues de noviembre** | Niveles de 10.000 · la promesa escrita por nivel · montar en otro proveedor · pagina de estado publica · modo "evento en curso" · re-medir el techo publicado por endpoint · I.7.9-I.7.10 |

**Hallazgo 8 — el correo tiene techo (2026-09-13).** Resend permite **10
peticiones por segundo por equipo** (docs de Resend; ampliable por solicitud) y
`SendEmailJob` no limita su ritmo (solo `tries=3`, `backoff=60`). Con 5.000
invitaciones o una ola de enlaces de acceso al abrir puertas: 600 correos por
minuto como maximo, y los que choquen con el limite se reintentan 3 veces y se
pierden. Hace falta limitar el ritmo en la cola y pedir ampliacion a Resend
antes del evento.

**Pendientes de agosto que ya estaban resueltos (verificado 2026-09-13):** socket
en cluster (`ecosystem.config.js`: `instances: 'max'`, `cluster`, 2G); el socket
ya no le pide `/auth/me` al backend en cada conexion (ficha compartida en Redis,
`eventos-socket/src/auth.ts`); indices de `live_game_participants` creados.


---

### BLOQUE 5 — W.12 Polish/cierre — 8/11 (Fases A+B CERRADAS 2026-07-05 tarde; falta Fase C QA)
- [ ] **Fase C** — QA device real 3 viewports + Edge/Firefox + Lighthouse batch + WCAG audit (CON Kamilo)
- [ ] **Fase C** — E2E cross-tab (streaming Q&A, social conectar)
- [ ] **Fase C** — DSN prod Sentry + validacion (item de deploy; config completa ya en codigo)

### BLOQUE 8 — W.19 Spatial Player (visionOS): el stream convive con el resto — 0/9 (abierto 2026-09-07)

**Procedencia:** decision Kamilo 2026-09-07, viendo la demo local de 5 dias con stream en vivo.
**Diagnostico contra codigo (2026-09-07):** el player vive DENTRO de la ruta
`session-stream/[id]/page.tsx` → `StreamShell.tsx` (grid desktop/tablet/mobile por pagina);
al cambiar de ruta se desmonta y no queda ni miniatura. No existe ningun mini player ni
PiP en `eventos-web/src` (grep 2026-09-07). En `/live`, `LiveHubView.tsx:53-58` hace
`router.push('/session-stream/{id}')` directo y `LiveHero.tsx:39` pinta un poster estatico
(`livePoster.ts`), no el video. Resultado: "En vivo" es un directorio, no un en vivo.

**Regla de convivencia (la idea de Kamilo, en 3 frases):**
1. Si estoy viendo un stream y abro **Agenda** → la agenda se abre EN LA MISMA pagina,
   al lado del player (split literal izq/der, patron W.13/W.14/W.18), sin salir del stream.
2. Si abro **cualquier otro modulo** (social, sponsors, desafio, speakers, perfil...) →
   el player pasa a **flotante** (mini, esquina, arrastrable, con volver/cerrar; chat y
   paneles se ocultan, el audio sigue).
3. **/live** deja de redirigir a ciegas: muestra **preview** de lo que se esta reproduciendo
   y ahi decido si entro.

- [ ] **8.1 Player persistente a nivel shell.** Sacar `StreamPlayer` de la ruta y montarlo
  en un `StreamProvider` del shell W.0 (junto a `GlobalSocketProvider`) con estado
  `{sessionId, mode: 'full' | 'split' | 'mini' | 'closed'}`. La ruta `session-stream/[id]`
  pasa a ser solo el layout `full`; navegar NO desmonta el iframe (YouTube/Vimeo/HLS).
  Ref: `StreamShell.tsx` (grid por pagina), `detectSource.ts`.
- [ ] **8.2 Modo `split` con Agenda.** Click en Agenda desde el stream → `mode: 'split'`:
  player izq (16:9 con controles) + agenda der en el mismo canvas. Clic en otra sesion
  con stream → cambia la fuente sin salir. Sin stream → DetailPanel normal.
  Ref: `agenda/DetailPanel.tsx`, patron split [[feedback_split_layout_pattern]].
- [ ] **8.3 Modo `mini` (flotante) para el resto.** Cualquier otra ruta → `mode: 'mini'`:
  card 320px esquina inferior derecha (fuera del SidebarPill), arrastrable (framer drag),
  spring al entrar/salir, boton volver (→ `full` con FLIP como W.X) y cerrar.
  Chat/Q&A/polls se ocultan pero su hook sigue vivo (no re-join de rooms,
  [[feedback_no_extra_sockets]]). Toasts de trivia/ruleta siguen llegando por
  `GlobalSocketProvider`.
- [ ] **8.4 Volver al stream.** Desde `mini` o `split`, clic en el player o en "En vivo" del
  rail → `full` restaurando paneles y scroll del chat donde iba (no recargar historial).
- [ ] **8.5 Mobile (espejo YouTube app).** Mismo estado, pero `mini` es una barra sobre la
  `MobileTabBar` (thumbnail + titulo + play/pause + cerrar); `split` no existe en mobile
  (la agenda reemplaza y el player queda en barra). Ref: `shell/mobile/MobileTabBar.tsx`.
- [ ] **8.6 /live con preview real.** `LiveHero` reproduce el stream destacado muted
  (iframe autoplay+mute, YouTube/Vimeo lo permiten) en vez del poster; clic → `full`.
  **DECIDIDO Kamilo 2026-09-13:** A en desktop (preview permanente muted en el hero)
  y B en mobile (tap dispara preview de 4 s y luego entra; el browser bloquea autoplay).
  Ref: `live/LiveHero.tsx:39`, `LiveHubView.tsx:55`.
- [ ] **8.7 Tarjetas laterales del hub con vida.** `LiveSideCard` de las otras salas en
  vivo: miniatura del stream muted o, si son varias, rotacion cada 8 s (1 solo iframe
  extra maximo; 3 iframes de YouTube a la vez pesan). Decision Kamilo pendiente.
- [ ] **8.8 Lifecycle.** Si la sesion del player termina (`session:ended` / hora + buffer),
  el mini muestra "Termino — ver siguiente" con la proxima del mismo salon (ya lo sabe el
  backend, es lo que devuelve `rooms/ping` al kiosko). Cerrar el evento (`ended`) apaga
  el player.
- [ ] **8.9 QA vivo con Kamilo.** Recorrido completo: stream → agenda (split) → social (mini)
  → volver (full) → /live (preview) → otra sala; desktop + tablet + mobile; y con socket
  apagado (degradacion sin errores). Medir que el iframe NO se recargue en ningun paso.

**Fuera de scope (por ahora):** PiP nativo del browser (`requestPictureInPicture`) solo
aplica a `<video>`, no a iframes de YouTube/Vimeo — el flotante tiene que ser DOM propio.
Multi-stream simultaneo (dos salas a la vez) → Fase 2.

---

### W.12 — Polish + E2E + PWA (25/48 — Fases A+B Bloque 5 cerradas 2026-07-05, falta Fase C QA)

**Fase 0 — Audit responsive (0/4)**
- [ ] 3 viewports en device real (laptop / iPad / iPhone)
- [ ] Validar tablet portrait warning overlay
- [ ] Validar Edge corporativo
- [ ] Validar Firefox 115+

**Fase 1 — Skeletons + empty (0/3)**
- [ ] Empty states consistentes
- [ ] Loading transitions

**Fase 2 — Accesibilidad (1/5)**
- [ ] WCAG AA contraste 4.5:1
- [ ] Keyboard nav completa
- [ ] Tab order logico
- [ ] ARIA labels iconos sin texto

**Fase 3 — Performance (2/8)**
- [ ] Bundle <200KB gzipped
- [ ] next/image sizes correcto
- [ ] Lighthouse Performance >=85 desktop (Fase C)
- [ ] Lighthouse Performance >=75 mobile (Fase C)
- [ ] TTI <3s 4G Bogota (Fase C)
- [ ] Migrar SSR → TanStack Query infinite cache (post-W.11)

**Fase 4 — SEO (3/3 — recortado con higiene, decision Kamilo 2026-07-05)**

**Fase 5 — PWA (5/5 — CERRADA 2026-07-05, `2dc43a3`)**

**Fase 6 — Print (2/2 — CERRADA 2026-07-05, `471cf94`)**

**Fase 7 — E2E (2/4)**
- [ ] Streaming + Q&A cross-tab (requiere socket server real en CI)
- [ ] Social conectar cross-tab

**Fase 8 — Sentry validation (1/2)**
- [ ] DSN prod

**Fase 9 — Cierre (3/7)**
- [ ] ~~Bancolombia embed test~~ → N/A (cliente perdido — validar si algun embed test aplica al proximo cliente)
- [ ] Memoria
- [ ] Counter PARITY-MATRIX → N/A (doc historico desde 2026-07-04)

**Fase 10 — Web Push (5/5 — CERRADA 2026-07-05, `b9aa4df` backend + `2dc43a3` web, verificado VIVO)**
> Validado por Kamilo mismo dia: push desde Filament post-reinicio Laragon (Apache + OPENSSL_CONF OK) + install PWA real OK. Todo pusheado.


**Sueltos de modulos cerrados (no bloquean)**
- [ ] W.9: Share rank social (backlog futuro)
- [ ] W.9: Lighthouse autenticado (va con el batch de W.12 Fase C)

---

## REFERENCIA RAPIDA (donde buscar detalle, NO leer por default)

| Necesito... | Voy a... |
|---|---|
| Saber detalle items de un modulo | `docs/webapp/W.X-*.md` |
| Ver parity vs Expo / endpoints backend | `docs/webapp/PARITY-MATRIX.md` |
| Decisiones tecnicas (ADRs) | `docs/webapp/DECISIONS.md` |
| Endpoints backend con shapes | `docs/webapp/BACKEND-API-MAP.md` |
| Contexto sesion pasada (continuidad) | `docs/NEXT-SESSION.md` |
| Pendientes generales proyecto (incluye Expo, landing) | `docs/living/PENDIENTES.md` |
| Roadmap modulo especifico (LiveMoments, Recap, etc) | `docs/roadmaps/ROADMAP-*.md` |

---

## CONVENCIONES

- **Marcar items hechos:** cambiar `[ ]` por `[x]`
- **Actualizar counters arriba** del modulo al cerrar items
- **Mover "QUE SIGUE"** al item nuevo apenas se cierre el actual
- **Pendientes paralelos** no entran en los sprints, solo en su seccion
- **Lo cerrado sale de aqui:** al `/guardar`, mover a `COMPLETADO.md` con fecha y commit
