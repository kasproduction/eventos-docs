# Pendientes — EventOS (FUENTE UNICA)

> **Este es el UNICO lugar donde viven las tareas abiertas** (decision Kamilo 2026-10-16: "prefiero que aca salgan mil
> tareas a tener todo suelto y estar acordandome manualmente"). Los roadmaps y docs de modulo guardan diseño, contexto
> e historia; si una tarea nace en otro doc, se escribe AQUI con su fuente. Lo cerrado se mueve a `COMPLETADO.md` en
> cada `/guardar`.
> Inventario completo de todos los docs: 2026-10-16 (45 archivos, ~1.700 casillas; las de planes viejos de modulos
> cerrados NO se copiaron: esos docs llevan el aviso "HISTORICO" arriba).
> Actualizado: 2026-10-21 (noche).
> **Vista para priorizar (privada, celular):** https://claude.ai/artifact/UwSGdGXbJCHTeXEb7UpBDL. Kamilo marca
> Ya / Antes del evento / Despues / Descartar y notas; Claude las lee con ArtifactData (coleccion `prioridades`, id = hash
> de la tarea) y pasa el orden a este archivo. Si este archivo cambia, se regenera la pagina.

---

## 0. ESTADO GENERAL AL 2026-10-22

> **2026-10-21: el evento del 6 de diciembre SE CAYO (Kamilo). No entran mas pendientes.** Se evalua migrar el backend a
> .NET; la fase 0 (preparar en PHP) ya arranco con dale. Ventana operativa: `docs/roadmaps/ROADMAP-MIGRACION-DOTNET.md`
> (31/195). Reglas: `docs/BUENAS-PRACTICAS-MIGRACION.md`. Decisiones: `docs/decisiones.md`. Clasificacion de todo lo de aqui (que se hace ya, que se congela hasta el admin nuevo, que va con DTO y
> contrato): `docs/pendientes-2026-10.md`. Regla: nada nuevo en PHP despues de la fase 0; si entra un pedido, la pregunta
> es "en cual carro". Lo marcado **[congelado hasta admin .NET]** abajo sale de esa clasificacion (seccion 7).

| Area | Avance | Que falta |
|---|---|---|
| **Migracion a .NET, fase 0 (preparar en PHP)** | 31/39 | 0.6 cierres, 0.8 seguridad (auditoria, BUG-373, BUG-374, `.env.example`), 0.7 retro logica a la vista, 0.6 cierres, 0.7 retro |

| Area | Avance | Que falta |
|---|---|---|
| Modulos del asistente (webapp W.0-W.18 + W.X) | 550/585 = **94%** | W.19 Spatial Player (mejora) y QA presencial |
| Registro publico, landing y widget | 26/32 = **81%** | Llaves reales de Turnstile, toggle de tema para el demo, 4 menores (§4) |
| Admin y modulos del organizador | **100%** | Nada bloqueante (decisiones menores en §2) |
| Seguridad del staff (2FA) | 25/26 = **96%** | S.7.2: correr `security:check` en el servidor real |
| App Expo | Implementada | Recap archivado para rediseño (rama local), BUG-392/393 visuales, QA en telefono, tiendas sin empezar |
| QA presencial con Kamilo | **0%** | §6 |
| Bintec 2026 (B.1-B.6) | 0/6 = **0%** (B.1 casi) | Todo lo compartido ya vive en memoria del servidor (2026-10-21); queda juntar "lo mio" por pantalla y medir en servidor |
| **Salida a produccion** (plan B1-B6) | ~4/31 = **13%** | §1 |

**Lectura:** el producto esta casi completo; lo que no esta es correrlo en un servidor. Lo medido llega a 300
personas por maquina (Nivel 1, agosto). El plan de servidor esta EN PAUSA por decision de Kamilo (2026-09-30)
hasta cerrar features. De las 29 tareas abiertas de B1-B6: 8 se hacen enteras sin servidor, 8 se codean en local
y se cierran midiendo en servidor, 13 solo con servidor.

---

## 1. SALIDA A PRODUCCION — plan B1-B6 (31 tareas) + arranque P.0-P.4

> Diseño, mediciones y calendario: `docs/roadmaps/ROADMAP-INFRAESTRUCTURA.md` (las secciones I.0-I.7 son el detalle
> de cada B; ninguna es independiente). Regla Kamilo 2026-09-29: B3 se cierra con medicion en servidor, no en local.
> Etiqueta: **[local]** se hace entera sin servidor · **[local+medir]** codigo local, cierra en servidor · **[servidor]**.

**Arranque del servidor (en pausa hasta cerrar features)**
- [ ] P.0 Token nuevo de DigitalOcean: Kamilo corre `! doctl auth init` (el anterior esta revocado)
- [ ] P.1 Inventario de snapshots y bases administradas + costo por dia. No prender nada sin confirmacion
- [ ] P.2 Banco de pruebas Nivel 1 desde los snapshots de agosto + codigo de hoy. Revisar `TRUSTED_PROXIES` SIEMPRE
      (vino quemado en el snapshot)
- [ ] P.3 Punto de partida medido con `tests/load/entrar-por-la-puerta.js`, antes de arreglar nada
- [ ] P.4 Medir B3.1 y B3.2 en el servidor (codigo hecho 2026-10-12)

**B1 Base limpia y segura — 2/3**
- [ ] B1.3 2FA del staff 25/26: falta S.7.2 `security:check` contra el `.env` real **[servidor]**

**B2 Montaje reproducible — 0/5**
- [ ] B2.1 Paquete por version con GitHub Actions (= I.7.3) **[local]**
- [ ] B2.2 Terraform por nivel 0-4 (= I.7.4) **[local]** escribirlo; aplicarlo es servidor
- [ ] B2.3 Pipeline con compuertas, etapas 1-6 (= I.7.5) **[servidor]**
- [ ] B2.4 Humo funcional automatico, incluye correo de acceso recibido (= I.7.6) **[servidor]**
- [ ] B2.5 Remontar de cero y destruir (= I.4 + I.7.10) **[servidor]**

**B3 Arreglos de codigo que pide 5.000 — 0/8 (2 con codigo)**
- [~] B3.1 Correo con ritmo controlado. Codigo d5c3690, falta medir **[local+medir]**
- [~] B3.2 La puerta (login cuenta fallos por red, rutas publicas por recinto). Codigo de80348, falta medir **[local+medir]**
- [ ] B3.3 Juegos en vivo: limitar difusiones + cachear `GameService::getEligiblePool` **[local+medir]**
- [ ] B3.4 Replica de lectura: conexion `reports` para Data Center, Pulse y exports **[local+medir]**
- [ ] B3.5 ROL=admin en `deploy.sh`: admin en `admin.<dominio>` sin trafico de asistentes **[local+medir]**
- [ ] B3.6 El 503 de la webapp con una persona: verificar si sigue y corregir **[local+medir]**
- [~] B3.7 Agenda partida en compartida cacheada + personal. Codigo backend 5b923a7 + webapp 2928657 (2026-10-18): la agenda pasa de 3 pedidos (uno pesado) a 2 livianos. Falta medir con 300 en servidor; luego sponsors, muro y ranking (cruza con B.1) **[local+medir]**
- [~] B3.8 Social pide 10 llamadas por apertura. Codigo webapp 3e23f65 (2026-10-18): cada pestaña pide lo suyo al abrirse; celular de 10 a 3, escritorio 4 + uno por pestaña. Visto en Chrome escritorio y 390 px con la sesion de Kamilo. Falta medir con 300 en servidor (cruza con B.1) **[local+medir]**

**B4 Ver lo que pasa — 0/6**
- [ ] B4.1 Alerta externa al telefono (BetterStack/UptimeRobot a health, app y socket) (= I.5 capa 1) **[servidor]**
- [ ] B4.2 Respaldo cifrado a R2 por cron CON restauracion probada (= I.4) **[servidor]**
- [ ] B4.3 Logs centralizados con id de peticion: "que le paso a la persona 1320 a las 10:42" **[local]**
- [ ] B4.4 Alertas por umbral salidas de lo medido; cualquier 429 a un usuario legitimo suena **[servidor]**
- [ ] B4.5 **Torre de control** (pedida 2026-04-19 y 2026-08-17): UNA pantalla en el admin solo super_admin con **[congelado hasta admin .NET si Kamilo elige D.4 (nace en el admin nuevo con los conectados del hub)]**
      conectados por nodo, peticiones/s, p95 por endpoint, errores del ultimo minuto, CPU por rol, colas y estado de
      cada nodo, en vivo por socket. Lab antes de codear. Brief: ROADMAP-INFRAESTRUCTURA I.5 **[local]**
- [ ] B4.6 Escalar con un comando: `escalar.sh --api +1` desde el pipeline **[servidor]**

**B5 Medir para 5.000 — 0/5**
- [ ] B5.1 Ritmo real: registro por persona (pantallas/minuto, rutas). Si no da tiempo, cotizar Nivel 4 (= I.2) **[local]**
- [ ] B5.2 Generador de carga para 5.000: varias maquinas + miles de asistentes sembrados (hoy `tokens.json` tiene 4) **[local]**
- [ ] B5.3 Montar Nivel 3 y medir hasta 5.000: ola de login, red 4G Colombia, admin con carga (D.4.4), export, juegos,
      foto viral E5 (subidas masivas a R2 contra el limite 10/min), canario sin carga E6 **[servidor]**
- [ ] B5.4 Caidas con carga: un nodo por rol + repetir la caida de API con 4 vCPU **[servidor]**
- [ ] B5.5 Decidir Nivel 3 o 4 y escribir la promesa del nivel: aguante, costo, RTO/RPO **[servidor]**

**B6 Entrega y dia del evento — 0/4**
- [ ] B6.1 Pipeline completo con carga, caida y entrega con informe (= I.7.7 + I.7.8) **[servidor]**
- [ ] B6.2 Modo registro / modo evento (`escalar.sh --modo`): barato mientras la gente se registra **[local]**
- [ ] B6.3 Runbook del dia del evento ("si pasa X, haz Y", incluye protocolo de `DISPONIBILIDAD-HA.md:536-546`)
      + modo "evento en curso" (congela despliegues, snapshot al empezar) **[local]** borrador
- [ ] B6.4 Ensayo general una semana antes con el nivel elegido **[servidor]**

**Sueltos de infraestructura**
- [ ] Correo transaccional por cliente: clave Resend, dominio verificado, Mail-Tester > 9/10 (`W.1-backend-magic-link.md:121`)
- [ ] Cloudflare LB cobra $5/mes sin nada detras: cancelar o usar (hallazgo 2026-09-13)
- [ ] Cache de auth fria: si los 5.000 llegan tras 15 min sin uso vuelve la estampida (832 de 1.500 perdidos en la
      prueba); precalentar (`SocketAuthCache.php:45`, `infra/DIAGNOSTICO-2026-08-02.md:267`)
- [ ] Sentry: DSN de produccion y validacion (W.12 Fase C)
- [ ] HTTP connection pool (viejo "Fixes pre-stress", `DISPONIBILIDAD-HA.md` seccion 11)

---

## 2. DECISIONES Y PREGUNTAS ABIERTAS DE KAMILO

> Viven en **`docs/decisiones.md`** (unico lugar desde 2026-10-22): tomadas con fecha y por que, y abiertas.

---

## 3. BUGS ABIERTOS (detalle en `BUG-LOG.md`)

> Cerrados 2026-10-21: BUG-344, BUG-347, BUG-348, y los nuevos BUG-360 y BUG-361 (ver COMPLETADO).
> Cerrados 2026-10-22: BUG-362 a BUG-370 y BUG-372.

- [x] BUG-358 Error de hidratacion React #418: RESUELTO 2026-10-22 (horas con espacio fino; eventos-web `2ff14a6`)
- [x] BUG-359 Falta `mobileNav` en `messages/pt.json`: RESUELTO 2026-10-22 (eventos-web `9dab432`)
- [ ] BUG-395 Aviso de React en desarrollo por el script del tema en el layout (no se ve; cuando se toque el layout)

- [ ] BUG-345 Correo sin ritmo: codigo hecho en B3.1, se cierra al medirlo

**Abiertos el 2026-10-22** (orden de arreglo en `decisiones.md`):
- Bloque 0.8 de seguridad (cierre de la fase 0):
  - [ ] BUG-373 Banear desde el admin no saca del socket en el momento (M0.8.2)
  - [ ] BUG-374 El admin muestra eventos de todas las organizaciones (M0.8.3)
  - [ ] BUG-376 Cambiar rol: reglas distintas en admin y API; confirmar quien sube a admin (M0.8.4)
  - [ ] BUG-384 Webhooks: "Simular evento" manda asistentes inventados como reales; reenviar triplica (M0.8.4)
  - [ ] BUG-385 Secretos de webhooks a la vista en notificaciones y en un .txt (M0.8.4)
  - [ ] BUG-386 Importar asistentes: contraseña igual al telefono, enlaces rotos, archivos sin borrar (M0.8.4)
  - [ ] BUG-387 Paginas del admin sin permiso propio y sin permisos por accion (M0.8.4)
  - [ ] BUG-389 Resultados de cualquier encuesta sin token en `/display/polls/{id}` (M0.8.4)
  - [ ] BUG-390 `/dc-embed` con `X-Frame-Options: ALLOWALL` (M0.8.4)
  - [ ] BUG-391 Secretos con valor por defecto y faltantes en `.env.example` (M0.8.5)
- Lista no repetir del admin nuevo (M2.8.5; antes si llega un cliente antes de la semana 12):
  - [ ] BUG-375 Cuatro formas de mandar push "ya", cada una con otro publico
  - [ ] BUG-377 "Marcar reclamado" del Golden Ticket sin quien, sin bloqueo, sin vencimiento
  - [ ] BUG-378 Fotos: aprobar da resultados distintos; aprobar seleccionadas puede romper
  - [ ] BUG-379 Muro: seis caminos con efectos distintos
  - [ ] BUG-380 Asignar speakers no avisa a la app ni a la webapp
  - [ ] BUG-381 `QuestionsRelationManager` de sesiones es codigo muerto
  - [ ] BUG-382 Activar o cerrar encuestas desde el admin no avisa por socket
  - [ ] BUG-383 Abrir dos paginas del admin ya cambia datos
  - [ ] BUG-388 El correo de prueba de plantillas no usa el SMTP de la organizacion
- App movil (M0.6.1):
  - [ ] BUG-392 Insignia "reclamado" de Golden Tickets con el estilo del modal (diseño)
  - [ ] BUG-393 Banner del concurso de fotos sin borde (diseño)
  - [ ] BUG-394 `AttendanceCheckModal` no compila en TypeScript (solo tipos)
- Solo local:
  - [ ] BUG-371 Con la suite corriendo, un pedido real leyo la config de pruebas (no correr la suite durante QA en vivo)
- [ ] Pulse cuenta 84 mensajes y la BD tiene 2.156: revisar el criterio de `stats.messages` (sin verificar)

---

## 4. REGISTRO PUBLICO, LANDING Y WIDGET — 26/32

> Hecho y decidido: `COMPLETADO.md` seccion 2026-10-16 (landing en la webapp `/[locale]/e/[slug]`, registro por
> pasos sin contraseña, confirmacion con escarapela, widget embebible, Turnstile, SEO/OG, admin Landing y widget).
> Linea visual: `design/system/LINEA-VITRINA.md`. Labs: `design/features/landing/`.

- [ ] **ANTES DE ABRIR EL REGISTRO: cambiar las llaves de PRUEBA de Turnstile por las reales** (Cloudflare > Turnstile).
  Hoy van las de prueba, que aprueban a cualquiera. Backend `.env`: `TURNSTILE_SECRET_KEY`; eventos-web `.env.local`
  (y el del servidor): `NEXT_PUBLIC_TURNSTILE_SITE_KEY`. Agregar el dominio real de la landing en el panel de Turnstile.

- [ ] **Toggle de tema para el demo** (idea Kamilo 2026-10-16): pildora Noir/Lux flotante que cambia `?tema=` en la misma URL,
  visible SOLO con `?demo` (una landing real no deja al asistente cambiar la plantilla del admin); el parametro viaja a la
  confirmacion y al widget. Minilab A (pildora abajo a la derecha) / B (barra fina arriba, con color del cliente como el lab v3)
  antes de codear. Sin backend. Hoy el tema se fuerza con `?tema=lux|noir` (`src/lib/landingEvent.ts:10`).
- [ ] Credencial con etiqueta visible (VIP, Prensa): en el admin marcar que tags salen en la escarapela (idea Kamilo 2026-10-11, despues) **[congelado hasta admin .NET: es la misma de la seccion 8]**
- [ ] "Ya tenias tu lugar" con varios eventos: hoy sale tambien si el correo tiene cuenta en OTRO evento y no en este
- [ ] Tarjeta al compartir cuando el evento NO tiene "Card del Home": hoy sale sin imagen. Generarla (nombre, fecha, lugar,
  color del evento) necesita lab antes
- [ ] En produccion, probar la tarjeta en WhatsApp, LinkedIn y X con la URL real (en local la imagen no es publica)

---

## 5. WEBAPP

### BINTEC 2026 — lo que sale de la referencia (Kamilo 2026-10-14) — 0/6

> Fuente de todo: `docs/analysis/REFERENCIA-BINTEC-2026.md`. Decision de enfoque (Kamilo):
> menos features nuevas, mas que lo existente cargue liviano y entre sin friccion.
> Orden propuesto: B.1 primero (lo que mas pesa para el 6 de diciembre); B.2 siguiente frente,
> con /davinci (Kamilo dio "dale" a arrancar el analisis, sin codigo).

- [~] **B.1 Menos peticiones por pantalla.** Medir cuantas hace hoy cada pantalla (~9 medidas en
  agosto). Empezar por la agenda: 1 peticion por dia ya armada para pintar (sede → sala → sesiones,
  horas y textos calculados), cache CDN 30-60 s, "en vivo" por socket. Luego el resto de pantallas.
  **2026-10-18:** conteo por codigo (promedio 4,2 pedidos por pantalla); agenda partida (B3.7) y Social por pestañas
  (B3.8) hechas: estimado 2,3. Sigue el "grupo 1", lo que es igual para todos y solo hay que guardar en memoria:
  speakers, en vivo/lo que sigue (home y live), destacados, branding. Despues patrocinadores, muro y ranking partidos.
  **2026-10-21 grupo 1 HECHO** (eventos-web `8759e9e`, backend `6f148ad`): speakers, destacados, branding y en vivo una
  vez por evento en memoria del servidor (frescos por aviso; en vivo hasta el proximo cambio de sala o 15 s, con los
  segundos restantes corregidos); Live arma "lo que sigue" desde la agenda partida. Medido local cargando cada pantalla
  completa con la precarga en reposo incluida (pedidos al backend, tibio): home 15 → 12, speakers 7 → 6, live 8 → 6,
  agenda 6 → 5, detalle de speaker 8 → 6. Lo que queda es por persona (lo mio, calificaciones, muro, historias,
  invitaciones, puntos, premios, pasaporte, ranking, recompensas) = grupo 2: patrocinadores, muro y ranking partidos,
  y `my-speaker-ratings` que se pide dos veces en Speakers.
  **2026-10-21 grupo 2 HECHO** (eventos-web 69828f4, f1ef98e, 1b2e2cc, 43f49b9; backend 136849e, e7ed96d, 14dfb59):
  patrocinadores partidos (`/sponsors/shared` + `/mine`, aviso `sponsors`), ranking partido (`/leaderboard/shared` en
  memoria 15 s por reloj, el aviso por cada punto NO va a la webapp; `/mine` fresco), muro partido (`/wall/shared` primera
  pagina 15 s + aviso `wall` al publicar, aprobar u ocultar; `/mine?ids=` con mis likes), y el segundo pedido de
  calificaciones en Speakers quitado. Medido local tibio: speakers 7 → 5; en home, social y desafio los pedidos pesados
  (muro, ranking) pasan a ser livianos (solo lo mio). Lo que queda por pantalla es todo por persona: lo mio de agenda,
  muro, patrocinadores, ranking, calificaciones, historias, invitaciones, puntos, premios, pasaporte, recompensas.
  **2026-10-21 B3.9 HECHO** (backend 85fd4c0 + 535161a; eventos-web 4d3d92a + 39c7d39): `GET /events/{id}/mine?piezas=`
  junta lo mio de una pantalla (agenda, speakers, patrocinadores, muro, ranking, puntos) en UN pedido; cada pieza con su
  estado (una rota no tumba las demas); pieza desconocida 422; `lib/loMio.ts` agrupa por evento y token en una vuelta del
  reloj sin cambiar las paginas. Medido local tibio (cada pantalla + su precarga en reposo): home 15 → 10, speakers 7 → 5,
  live 8 → 6, detalle de speaker 8 → 5, social 7 → 6, desafio 11 → 9. Por render queda UN pedido de lo mio; lo que se ve
  repetido (`/mine` x3) son las precargas de agenda, social y speakers, una cada una.
  **Siguiente en B.1:** medir en servidor con 300 (B3.7/B3.8/B3.9; desde 2026-10-22 es la tarea M1.5.6 del roadmap de la migracion), y las piezas que faltan del desafio (premios, pasaporte,
  recompensas) e invitaciones/historias de social si pesan en la medida real.

### Navegacion inmediata (Kamilo 2026-10-18) — 8/9
- [x] Sin skeleton al cambiar de modulo; el menu marca el destino en el toque; sesion 30 s en memoria; precarga al
      pasar el mouse, al apoyar el dedo y en reposo (agenda, social, speakers). eventos-web `368fe81`. Medido local:
      precargados 49-106 ms, el resto 324-454 ms sin skeleton (antes 275-654 ms con skeleton)
- [x] Labs de transicion (`design/features/navigation/`): v1, v2, C2 camara (descartada: marea), v3 sutiles, **ios**
      (aprobado). Cambio de modulo con View Transitions en `TransicionModulo` (eventos-web `bb2cf2a`)
- [x] BUG-357 corregido 2026-10-21: solo cambia el contenido de adentro, marco quieto, salida (120 ms) y entrada (zoom 2%,
      395 ms) en secuencia. Lab `lab-transicion-contenido.html` (A Relevo escogida); eventos-web `f9e6bfc`
- [x] Abrir con zoom desde la tarjeta: **decision Kamilo 2026-10-21, speakers en escritorio se queda como esta** (el
      panel ya entra deslizando con fundido). Lab `lab-abrir-desde-tarjeta.html` (A crece hasta su sitio, B nace y se
      asienta, celular como el lab iOS) queda de referencia por si se retoma para celular, sesiones o patrocinadores
- [ ] Skeleton solo si la espera pasa de ~300 ms (hoy no hay ninguno en modulos; con servidor lento el clic parece
      no hacer nada mas alla del menu)
- [x] Choque en celular entre el push del detalle y la transicion de modulo: corregido 2026-10-21 (eventos-web
      `lib/transicionModulo.ts`): dentro de un detalle se conserva la clave del modulo de origen, entrar y volver no
      disparan la transicion. Verificado con Playwright (0 transiciones al abrir y volver, push y pop intactos)
- [x] Tokens de prueba del asistente sembrado (277, 278, 279) borrados 2026-10-21 con permiso de Kamilo
- [ ] **B.2 Juegos a demanda por QR.** Hueco: nuestra gamificacion es EN VIVO (trivia del streaming,
  momentos, MC); falta la capa que la persona juega cuando quiere en el stand. Un QR = un juego corto
  (trivia, memoria, ahorcado, reflejos), un intento que suma al mismo ranking, repeticiones por
  diversion, recuperar partida si se cae la red, modo vista previa para staff, silenciar sonido.
  Sin socket, 3-4 peticiones por juego. Base a revisar: golden tickets, pasaporte, trivia. Lab antes de codigo.
- [ ] **B.3 Cero friccion de entrada.** El QR del stand suma con la sesion que ya existe; nunca otro
  login (Bintec Play no dejo entrar a Kamilo).
- [ ] **B.4 Ficha de sesion:** triple estado (me interesa / favorito / asistire presencialmente) y
  Outlook Calendar ademas de Google. Verificar que tenemos hoy antes de agregar.
- [~] **B.5 Cabeceras de seguridad:** revisadas 2026-10-21 (M0.1.5): la webapp tiene CSP, X-Frame-Options, nosniff,
  Referrer-Policy y HSTS; la API ademas `Permissions-Policy`. Falta `Permissions-Policy` en la webapp (`camera=(self)` por el
  escaner QR, QA en telefono). Cedula como contraseña no aplica: el registro es sin contraseña.
- [ ] **B.6 Respaldo de chat para eventos grandes:** evaluar Ably (o similar) solo si la medicion
  de 5.000 muestra que nuestro socket no aguanta. Decision por evento, no reescritura.

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

### Sueltos de la webapp (inventario 2026-10-16)

- [ ] Precarga de modulos vecinos (`RouteWarmer`): se paso a W.12 y nunca quedo listada; no existe (`W.12-polish.md:103`)
- [ ] Sugerencia de typo del correo tambien al editar el email en el paso de contraseña (`W.1-setup-auth.md:437`)
- [ ] Verificar que un modulo apagado no haga peticiones ("paquete minimo"); entra en B.1 (`REFERENCIA-BINTEC-2026.md:104`)
- [ ] UI optimista en rating de agenda, ticket de soporte y forms de perfil (`NEXT-SESSION.md:2808`; ojo: el skip formal
      2026-07-05 decia que los forms con validacion 422 deben esperar, revisar antes)
- [ ] W.9 Share rank social (backlog)
- [ ] Opcional: 3 vitest de `TESTS-PENDIENTES.md` que no existen (fetchers SSR de speakers, SpeakersView, highlight de agenda);
      el E2E ya cubre el flujo

---

## 6. QA PRESENCIAL CON KAMILO (device + tiempo)

- [ ] Mobile parity M.2-M.8 en telefono real: heart, momentos, shuffle, trivia sponsor, desafio, anuncios, soporte,
      orb FAQ, encuestas, vendor (scanner con camara por HTTPS, export, invitacion en vivo, gates ban/pending)
- [ ] W.12 Fase C: 3 viewports en device, Edge, Firefox, **Safari de escritorio**, toques de 44 px, Lighthouse, WCAG
      (`RESPONSIVE-SPEC.md:134`, :276)
- [ ] W.X pelicula de bienvenida de corrido
- [ ] Transiciones de los 4 pasos del login y los 5 estados del home (`QA-MASTER.md:1229`)
- [ ] Red corporativa con WebSocket bloqueado: cae a polling sin errores (`W.11-sockets-rt.md:295`)
- [ ] Login sin red durante el magic link: sale "tu link sigue valido" (`W.1-setup-auth.md:416`)
- [ ] Agenda de la webapp se actualiza sola por socket (`infra/PLAN-EXPERIENCIA-ENTERPRISE.md:380`)
- [ ] Kiosko y attendance-check muestran `joined event:X` y el Pulse se mueve en < 2 s (`AUDITORIA-SOCKETS-SUPERFICIES:221`)
- [ ] Expo: socket singleton en device (streaming chat/Q&A/polls/emojis/pinned + wall + encuestas, log `conns=1`)
- [ ] Landing en iPad con Kamilo (empezada 2026-10-12)
- [ ] iOS + Android con Sentry Performance en 4G Bogota

---

## 7. APP MOVIL (EXPO)

### Recap de la app: archivado, pendiente de rediseño y reparacion (antes "WIP sin commitear", 2026-07-04)

> **2026-10-22, decision Kamilo:** el recap de la app no se commitea como estaba: no funcionaba bien y se veia mal (es
> sobre todo diseño). El working tree se archivo, no se borro: rama LOCAL `archivo/2026-07-recap-haptics-reintentos`
> de eventos-app (`8176acd`, sin push), y `main` de eventos-app quedo limpio.
> En esa rama: `app/(app)/recap/`, `components/recap/`, `hooks/useRecap.ts`, `lib/recapColors.ts` (el recap), mas
> los cambios de julio en `hooks/useAgenda.ts`, `useNetworking.ts`, `usePhotos.ts`, `useSponsors.ts` (vibracion al
> tocar) y `lib/api.ts` (2 reintentos con espera ante caida de red o 502/503/504).
>
> Historia: el 2026-07-20 Kamilo lo puso DE ULTIMO en la cola ("es un estres"). Los 5 errores de typecheck anotados
> aqui en julio quedaron asi el 2026-10-22: `RecapCard.tsx:59` se fue con el recap a la rama de archivo;
> `AttendanceCheckModal.tsx:24` arreglado (BUG-394, eventos-app `3b845dc`); `leaderboard.tsx:1387-1388` (BUG-392) y
> `ContestBanner.tsx:25` (BUG-393) siguen abiertos, son visuales y van con lab de diseño (M0.6.1).

- [ ] Rediseño y reparacion del recap de la app: lab de diseño aprobado y funcionando contra el backend real (que ya
  existe). Partir de la rama de archivo y de `docs/ROADMAP-RECAP.md`.
- [ ] Revisar aparte, de la misma rama: la vibracion al tocar (4 hooks) y los reintentos de red de `lib/api.ts`.
- [ ] BUG-392 y BUG-393 con lab de diseño (M0.6.1).

### Recap compartible — post-evento — IMPLEMENTADO

> Existe en backend (`RecapService.php`, `RecapImageRenderer.php`, `RecapController.php`, `RecapVerifyController.php`)
> y en la app. Plan: `docs/ROADMAP-RECAP.md`.

- [ ] E2E con Chromium en el servidor + validacion visual en dev build
- [ ] ~~Commitear el trabajo del recap del working tree~~ -> archivado 2026-10-22 (ver arriba): rediseño y reparacion

### Pendientes menores app (Kiosk / Silent disco)

- [ ] Kiosko: verificar scan endpoint < 100ms en VPS real (en local Windows ~150ms, Linux produccion estimado ~50ms)
- [ ] Staff app: cola offline MMKV + batch sync (nice-to-have, solo si zonas sin WiFi son problema real)
- [ ] Silent disco push notification — verificar con dev build real (no Expo Go)

### Lux V2 — futuro (bloqueante externo)

- [ ] Tab Bar polish con `@callstack/liquid-glass` cuando la libreria soporte Expo (actualmente solo iOS 26 nativo)

### Tiendas y build

- [ ] EAS build de produccion Android + iOS, cuentas, envio y revision. **Riesgo:** la revision toma dias o
      semanas; si el cliente pide app en tiendas, dejar margen
- [ ] SHA-1 del keystore de produccion en Firebase + dominio/DNS (`EventOS_Roadmap.md:568`)
- [ ] Alinear `documentos.tsx` (legacy huerfano previo al tema) (`NEXT-SESSION.md:2344`)

### Backlog Expo (sesion Expo futura)
- [ ] Borrar `banners.tsx` + `BannerCarousel` + `bannersApi` del Expo (feature legacy muerta)
- [ ] `ModuleMenuCompact.tsx` huerfano: importado en `(tabs)/index.tsx:25` pero nunca
      renderizado (hallazgo 2026-08-01). Borrar componente + import, o darle uso.
- [ ] Portar "click sesion → agenda highlight" del webapp W.5 al Expo
- [ ] Validar si `banners.tsx` Expo es vista dedicada o solo carousel embebido
- [ ] Decidir recap/[eventId] del Expo (→ Fase 2, no mapeado a webapp)

---

## 8. ADMIN Y MODULOS DEL ORGANIZADOR **[congelado hasta admin .NET, salvo Mission Control iPad]**

> Tambien congelados (lista `pendientes-2026-10.md` §7, C1 a C3): renombrar "Highlights" a "Destacados", donde vive "Otorgar puntos manuales" y el toggle de Silent Disco. Las preguntas estan en `decisiones.md` (Abiertas, Producto) y el trabajo en el roadmap de la migracion (M2.8.1 y M2.8.3).

> Justificacion por item en `docs/pendientes-2026-10.md` seccion 7. Nacen en el admin nuevo (fase 2 del roadmap de migracion).

- [ ] Configuracion canales WhatsApp/SMS desde Filament (email/SMTP ya existe;
      WhatsApp/SMS son nice-to-have seccion 9)
- [ ] Branded QR codes con logo
- [ ] Borrar `EventOnboardingResource` (muerto, sigue existiendo) (`NEXT-SESSION.md:1898`)
- [ ] Mission Control: pulir iPad horizontal a 1024 px (`project_mission_control_notes`)
- [ ] Credencial con etiqueta visible (VIP, Prensa): marcar en el admin que tags salen en la escarapela (§4)

### Paridad config admin ↔ 3 superficies (diseño con Kamilo; toca backend+Filament+Expo+webapp)
- [ ] **Paginas custom: DEFERIDO POST-DEPLOY** (decision Kamilo 2026-07-20:
      "es para el final, no aporta ni detiene, no es dependencia"). El feature
      (iframes/HTML embebido: YouTube, Slido, mapa) tiene backend+API+detalle
      Expo pero NINGUNA superficie lo lista (huerfano ~60% construido); quedo
      EN PAUSA visible+deshabilitado en el admin (`4cd43d4`+`52f4522`). Al
      retomar: construir el acceso (Filament reactivar + lista Expo + modulo
      webapp ~1 sesion) o demoler entero. Costo mapeado, git preserva todo.
- [ ] **Keyvisual por superficie**: `keyvisual_desktop` + `keyvisual_mobile` en branding + 2 uploads Filament con preview.
- [ ] **Hero modo texto**: contrato unico de branding (type image|text) a escala en las 3 superficies.

> `/encuestas` fuera del rail (resuelto: canon 'aviso' + anuncio con targeting) ·

---

## 9. MANUAL DEL ORGANIZADOR — EN PAUSA (decision Kamilo 2026-07-19) **[congelado hasta admin .NET: documenta pantallas que se van]**

> Se retoma al final, pre-produccion (primero bugs/funcionalidad/deploy).
> Ventana operativa: `docs/roadmaps/ROADMAP-MANUAL.md` (5/35 — quedan M1-M7:
> 22 paginas + capturas + deploy del sitio + links contextuales en admin).

- [ ] Al retomar: re-escribir el piloto de Encuestas con el tono aprobado
      (natural, humanizado, sin tecnicismos — feedback Kamilo) y producir
      M1-M7 contra el inventario blindado

---

## 10. SEGURIDAD DEL STAFF — 25/26

> 2FA, equipos de confianza, sesiones abiertas, registro de accesos y bloqueo HECHOS (2026-09-29).
> Ventana: `docs/roadmaps/ROADMAP-SEGURIDAD-STAFF.md`. Raya de Kamilo: la URL del admin no sale sin esto.

- [ ] S.7.2 Correr `security:check` contra el `.env` real de produccion (va con el montaje)
- [ ] Passkeys (bloque S.9, ampliacion 2026-08-01) — sin fecha **[congelado hasta admin .NET: toca el login de Filament; en .NET va sobre Identity]**
- [ ] **Auditoria de seguridad del backend** (dale de Kamilo 2026-10-22, `decisiones.md`): que es = revisar a proposito quien
  puede ver o tocar que en cada ruta, inyecciones, limites y datos de mas en las respuestas; por que = las pruebas de
  contrato congelan lo que hay aunque este mal (BUG-363 aparecio de rebote); lista cuando = informe con hallazgos
  verificados, cada uno en BUG-LOG con severidad, y los graves arreglados o con decision de Kamilo.
  Ya vistos de rebote (2026-10-22, M0.5.2): `/display/polls/{id}` publica sin token (resultados y respuestas abiertas de
  cualquier encuesta cambiando el numero); `/dc-embed` manda `X-Frame-Options: ALLOWALL` (valor invalido); confirmar que el
  `SOCKET_INTERNAL_SECRET` de produccion no sea el de ejemplo; `/internal/*` comparaba con `!==` (ya `hash_equals`); 12 variables propias faltan en `.env.example`, entre ellas
  `APP_QR_SECRET` y los secretos internos, que caen al valor por defecto `change_this_secret_in_production`
  (`docs/contratos/variables.md`). Y los 13 hallazgos por verificar del inventario del admin
  (`docs/contratos/acciones-admin.md`, seccion Hallazgos).

---

## 11. DEUDA TECNICA

### TODO escritos en el codigo que no estaban en ninguna lista (revisados 2026-10-22)

- [ ] `eventos-backend` `HappeningNowController.php:45` y `:97`: "lo que pasa ahora" elige la sesion destacada sin
  mirar la favorita del asistente; espera un `FavoriteRoom` o `session.is_keynote` que no existen
- [ ] `eventos-app` `ProfileScreen.tsx:769` y `:894`: color morado fijo donde va el accent del evento (deuda de la Fase 3)
- [ ] `eventos-app` `hooks/useTracker.ts:18`: el tracker no manda a ningun proveedor de analitica
- Ramas del backend: se trabaja en `feature/magic-link-auth`, 166 commits delante de `main` (quieto desde 2026-05-02).
  Desplegar desde `main` perderia cinco meses, seguridad incluida. Pendiente: decidir cuando se une a `main`.
  Las ramas viejas de abril (backend, app, socket) no tienen nada que no este en la rama principal.

### Optimistic UI restantes (~3-4h) — pre-stress polish **[los que tocan el socket, congelados hasta decidir SignalR]**

> Plan completo en `docs/OPTIMISTIC-UI-PLAN.md`. Audit en `docs/OPTIMISTIC-UI-AUDIT.md`.
> Implementar DESPUES de cerrar features de negocio (app + webapp), ANTES del stress test.

- [ ] Chat tempId + ack + estados progresivos (2-3h, cambio mobile + socket server)
- [ ] Emoji skip-self (30min, cambio socket server)
- [ ] Dedup wall:comment con socket broadcast (20min)
- [ ] Q&A upvote anti-parpadeo (15min)

### QA Mission Control + Data Center

- [ ] Tests funcionales para Mission Control (~1.5h) — depende de mock de token HMAC `/monitor/{id}?token=...`
- [ ] Tests E2E flujos criticos: aprobar Q&A, lanzar game, cancelar sesion, scheduled export trigger
- [ ] Revisar la autenticacion del admin: como funciona + cuando expiran los tokens (nota Kamilo; encaja con el frente Seguridad del Staff)

### Unificacion SPAs (~10-12h) **[congelado hasta admin .NET: se absorben en el admin nuevo]**

> Cada SPA usa toasts/modales/colores/fetch wrappers diferentes. Crear biblioteca compartida.

- [ ] Crear `public/shared/tokens.css` (variables Noir + Lux unificadas)
- [ ] Crear `public/shared/components.css` con .btn, .modal, .toast, .empty, .card consistentes
- [ ] Crear `public/shared/lib.js` con apiFetch(), toast(type), openModal(), helpers DOM
- [ ] Migrar Chat Monitor (`public/chat-monitor.html`) a Material Symbols + tokens unificados
- [ ] Migrar Attendance Check (`public/attendance-check.html`) — actualmente usa colores propios (#6366f1)
- [ ] Migrar Display Session (`public/display/session.html`) — alinear empty states
- [ ] Migrar Event Pulse (`public/event-pulse/`) — `--ink` → `--t` consistente con Noir/Lux

---

## 12. DIFERIDO A FASE 2 Y NICE TO HAVE (no antes del 6 de diciembre salvo que un cliente lo pida)

- [ ] **Aforo por sala en vivo** (idea Kamilo 2026-10-22, para el admin nuevo): personas dentro de cada sala en el totem de sala y en el admin. Hoy solo el Event Pulse lo muestra (pide a la API). `room:occupancy` se quito porque nadie lo escuchaba (BUG-369).
- [ ] Cartel de anuncio sobre el video en celular: solo existe en escritorio (BUG-368).

### Webapp — diferidos por decision al cerrar cada modulo

- [ ] Barra de comandos ⌘K (`W.0-spatial-ui.md:447`)
- [ ] StaggerList y gestos de swipe con haptica (`W.1-setup-auth.md:343`, :364)
- [ ] Usuario sin contraseña que intenta entrar con contraseña: avisarle (`W.1-setup-auth.md:417`; pesa mas desde el registro sin contraseña)
- [ ] Indicador de CapsLock y cuenta regresiva para reenviar el link (`W.1-setup-auth.md:431`, :434)
- [ ] Filtros en la URL para compartir, agenda y social (`W.3-agenda.md:195`, `W.6-social-wall.md:179`)
- [ ] Recap y certificado en pantalla aparte de la webapp (`W.2-home.md:207`)
- [ ] Tarjetas grandes de sugeridos en networking (`W.8-networking.md:142`)
- [ ] Paginas dinamicas del organizador en la webapp (`W.13-faq-documentos-pages.md:80-83`)
- [ ] OG y sitemap de la webapp autenticada (`W.12-polish.md:137-141`)
- [ ] Migrar SSR a TanStack Query infinite cache
- [ ] Aviso de choque cuando dos favoritos caen a la misma hora (`project_w3_agenda_react`)
- [ ] Home terminado con los proximos eventos del organizador; ambiente distinto por estado (`project_webapp_home_v2`)
- [ ] Juegos: subasta de puntos, "¿donde esta el sponsor?", speed-dating (`project_gamification_ideas`; agrupar con B.2)

### Backend nice-to-have (NO bloqueante — verificados 2026-07-14 como NO construidos)
- [ ] Search server-side params standardizados
- [ ] AttendeeResource unificado
- [ ] Endpoint cancelar solicitud (`DELETE /contacts/request/{id}`)
- [ ] Score numerico match en suggested-contacts
- [ ] Sort server-side wall `?sort=likes_count`
- [ ] Endpoint paginado leaderboard >50
- [ ] Evento socket `points:awarded {amount, action, total}` informativo

### Analytics tracking — Fase 2 (= decision W.8; no existe infra de analytics aun)
- [ ] `social.profile_opened` · `connection_sent` · `connection_message_added` · `contact_method_clicked` · `profile_closed` · views por sponsor/speaker/sesion

### Nice to have general

> Mover a activo solo si un cliente lo pide explicitamente.

#### App movil — cosmetico/incremental

- [ ] Racha de visitas a la app (streak gamification — dia consecutivo = bonus puntos)
- [ ] Orbe FAQ a Skia shader (reemplazar Reanimated+BlurView, solo cosmetic)
- [ ] Venue + Mapa (depende de si el evento tiene plano)
- [ ] react-native-image-crop-picker: crop circular dark

#### Registro & Acceso avanzado

- [ ] Waitlist (cuando max_attendees se llena)
- [ ] Referral tracking
- [ ] Social login (Google)

#### Comunicacion avanzada

- [ ] WhatsApp Business API (ICE360 lo tiene por $850K COP — evaluar si vale la pena)
- [ ] SMS fallback
- [ ] Email builder visual (Fase 2+)

#### Post-evento

- [ ] Networking follow-up ("Conectaste con X personas")
- [ ] Highlight reel (collage automatico fotos)
- [ ] Event replay (grabaciones post-evento)

#### Seguridad avanzada

- [ ] Anomaly detection — alertar admin (Fase 2+)
- [ ] Backup/Restore de evento (snapshot JSON)

#### Platform Health — Dashboard interno

> Absorbido por B4.5 torre de control y §13 (Pulse, canario). Sin casillas aqui para no duplicar.

#### Documentacion

- [ ] Documentar arquitectura socket
- [ ] White-label: migrar app.json → app.config.js + estructura clients/

#### Features opcionales

- [ ] Wallet digital (.pkpass + Google Wallet)
- [ ] Digital signage (pantallas venue)
- [ ] Badge printing fisico
- [ ] Landing builder Filament (Fase 2+) **[congelado hasta admin .NET]**
- [ ] A/B testing emails (Fase 2+) **[congelado hasta admin .NET]**
- [ ] Juegos Unity en stands (requiere dev Unity separado)

#### Showcase / Demo inversor

- [ ] Panels clickeables, responsive 1920x1080, audio, hints, social wall

#### Fase 3 — SaaS + Monetizacion

- [ ] Multi-tenant, Stripe, Data export GDPR, Juegos Unity bridge
- [ ] Multi-location con tracks (Bancolombia: Colombia + Panama)

---

## 13. DESPUES DEL 6 DE DICIEMBRE — infraestructura (`ROADMAP-INFRAESTRUCTURA.md:260`)

- [ ] Cache de `auth/me` en Next · precarga completa · `data:sync` en vez de `data:invalidate` (refrescar solo lo que cambio) **[congelado hasta SignalR: cambia el protocolo del socket]**
- [ ] Consultas duplicadas de auth · COUNT de branding · re-medir el techo publicado por endpoint
- [ ] Laravel Pulse como panel (capa 2) y canario automatico (capa 3) si no entran con la torre **[congelado hasta admin .NET salvo que se use para el evento (camino b de D.4)]**
- [ ] Pagina de estado publica · niveles de 10.000 · montar en otro proveedor con RTO/RPO
- [ ] Stress 10K (TEST 6-9 del plan viejo: 10K 2 h, failover, export aislado, break point)
- [ ] I.7.9 hermanos del pipeline (actualizar, destruir) + I.7.10 prueba de punta a punta
- [ ] Viejo §6 de abril: Docker Compose, CI/CD blue-green, Data Center en VPS-3 worker headless (reemplazado por B1-B6;
      revisar si algo sigue aplicando al cerrar B2)

---

## 14. CONTEXTO (no son tareas)

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

### Documentos de referencia

| Doc                                       | Contenido                                                        |
| ----------------------------------------- | ---------------------------------------------------------------- |
| `EventOS_Roadmap.md`                      | Fases, sesiones, timeline (v5.1)                                 |
| `docs/COMPLETADO.md`                      | Historial completo                                               |
| `docs/BUG-LOG.md`                         | Bugs historicos                                                  |
| `docs/QA-MASTER.md`                       | Barrido endpoints                                                |
| `docs/PLAN-TAGS-MODULOS.md`               | Plan tags + visibilidad modulos                                  |
| `docs/ROADMAP-UIUX-LANDING.md`            | Spec diseno landing + UI                                         |
| `docs/webapp/PLAN.md`                     | Master plan webapp (reemplaza WEB-APP-PLAN.md legacy)            |
| `docs/webapp/DECISIONS.md`                | ADRs webapp (auth, deploy, PWA, i18n, streaming, responsive)     |
| `docs/webapp/AUTH-SPEC.md`                | Auth detallado: magic link + Bearer Sanctum + refresh            |
| `docs/webapp/RESPONSIVE-SPEC.md`          | 3 disenios dedicados por viewport (no responsive automatico)     |
| `docs/webapp/DESIGN-SYSTEM.md`            | Tokens Lumina Noir + Lux portados desde app movil                |
| `docs/webapp/W.0-W.12-*.md`               | 13 roadmaps modulares DaVinci con counter 0/N                    |
| `docs/ANALISIS-COMPETITIVO.md`            | Cotizaciones, gaps, pricing                                      |
| `docs/WHITE-LABEL.md`                     | App config dinamico                                              |
| `docs/FASE-SEGURIDAD.md`                  | Auditoria OWASP                                                  |
| `docs/DISPONIBILIDAD-HA.md`               | Arquitectura HA DO sao1, deploy, RT invalidation                 |
| `docs/PLAN-STRESS-TEST.md`                | Stress test v2.0 (Hetzner, referencia historica)                 |
| `docs/PLAN-STRESS-TESTDO.md`              | Stress test v2.1 (DO sao1 consolidado, plan definitivo)          |
| `docs/ROADMAP-DATA-CENTER.md`             | Data Center analytics modulo (cerrado)                           |
| `docs/ROADMAP-FILAMENT-PULIDO.md`         | Event Switcher global + UI Enterprise Niveles 1-3                |
| `docs/ROADMAP-RECAP.md`                   | Recap post-evento estilo Wrapped — app movil Fase 1              |
| `docs/ROADMAP-EVENT-PULSE.md`             | Dashboard live standalone (cerrado)                              |
| `docs/ROADMAP-KIOSK.md`                   | Kiosko + Staff check-in                                          |
| `docs/ROADMAP-LIGHTMODE.md`               | Light mode Fases 1-8                                             |
| `docs/ROADMAP-LIVE-MOMENTS.md`            | Ruleta + Sorteo + Trivia (cerrado)                               |
| `docs/ROADMAP-LUX-V2.md`                  | Light mode Lux completo                                          |
| `docs/ROADMAP-MISSION-CONTROL.md`         | Mission Control v4 (cerrado)                                     |
| `docs/ROADMAP-WEBHOOKS.md`                | Webhooks partners (cerrado)                                      |
| `docs/CODEBASE-MAP.md`                    | Mapeo completo 3 repos: 150+ endpoints, socket events, observers |
| `docs/OPTIMISTIC-UI-AUDIT.md`             | 30 acciones auditadas, estado optimistic, gaps                   |
| `docs/GAPS-ANALYSIS.md`                   | Gaps detallados, dedup socket, coordinacion REST+socket          |
| `docs/OPTIMISTIC-UI-PLAN.md`              | Plan 3 semanas: 9 PRs, cross-cutting, metricas                   |
| `docs/BRIEF-CLAUDE-CODE-OPTIMISTIC-UI.md` | Brief original del audit optimistic UI                           |
| `docs/MODULOS.md`                         | 15 modulos + 6 sistemas + admin (v1.0)                           |
