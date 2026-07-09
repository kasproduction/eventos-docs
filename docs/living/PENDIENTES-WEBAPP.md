# Pendientes — Webapp (post-Bancolombia, producto propio)

> Vista operativa unica. Todo lo faltante para vender webapp standalone — desde tu cama, transporte o cualquier device sin abrir otros docs.
>
> **Re-auditado:** 2026-07-04 (inventario TOTAL contra codigo con 4 agentes — meta: cierre 100% webapp, plan en BLOQUES abajo)
> **Pivote comercial:** Bancolombia se perdio — el producto sigue, ahora generico para el proximo cliente
> **Fuente de detalle:** `docs/webapp/PARITY-MATRIX.md` (cruce Expo↔Webapp↔Backend, 117/117 endpoints backend OK)
> **Detalle por modulo:** `docs/webapp/W.X-*.md`
>
> Items con [x] estan hechos. [ ] = pendiente.
> Marcar items completados directo aqui al cerrar cada sesion.

---

## ~~ALERTA — bloqueante critico detectado 2026-06-20~~ — RESUELTO 2026-06-20

~~194/194 tests vitest fallando.~~ → **194/194 verdes.** Root cause: Node 25.8.1 expone `globalThis.localStorage` experimental (warning `--localstorage-file was provided without a valid path`) que shadowea el de jsdom 29 con un stub sin metodos. Fix en `eventos-web/tests/setup.ts`: stub propio Map-based + `Object.defineProperty` sobre globalThis + window. Una sesion, un archivo, un fix.

---

## RESUMEN GLOBAL (post-recount)

| Modulo | Counter | Estado | Cambio vs auditoria 2026-05-21 |
|---|---|---|---|
| **W.0 Spatial UI base** | **24/24** | **CERRADO 100% 2026-07-04** (Command palette + Pre-load + device real reclasificados a Fase 2/W.12) | **+3** |
| **W.1 Setup + Auth + UI Foundation** | **107/107** | **CERRADO 100% 2026-07-04** (5 items diferidos reclasificados formalmente: 2 a Fase 2, 2 a W.12, 1 agrupado con W.4) | **+5** |
| W.1B Backend magic link | 10/10 | **CERRADO** | — |
| **W.2 Home** | **16/16** | **CERRADO 100% 2026-07-08** — GamificationHud LIVE (slide del carrusel espejo Expo, borde RGB + deeplink /desafio) + EventArchive ENDED (espejo Expo puro: banner + stats evento + prompt encuesta + 4 links archivo) + prompt encuesta → **/encuestas** (SurveyDeck por slides: cascada estrellas, spring, cierre verde). Recap/certificado → pantalla aparte (Fase 2) | **+3** |
| **W.3 Agenda** | **25/25** | **CERRADO 2026-07-04 via auditoria procedencia** — badges/conflict/check-in eran inventos (Expo no los tiene; el espejo real del lifecycle es el toast agenda:delayed YA hecho en W.11). URL state → Fase 2, recordatorio → W.12 Push | **+3** |
| **W.4 Streaming** | **92/92** | **CERRADO 2026-07-04 noche** (recount contra codigo: replay + rating auto + anuncios in-stream + custom panel + slow mode + floating emojis + mobile/tablet layouts YA estaban implementados y sin marcar. Fix race auto-rate e1b0c9a. Reclasificados: trivia→W.16, E2E cross-tab→W.12, 17 menores→QA W.12) | **+9** |
| **W.5 Speakers** | **35/35** | **CERRADO 100% 2026-07-04** (reclasificado Lighthouse + device fisico a W.12 Polish cross-modulos) | **+2** |
| **W.6 Social Wall** | **28/28** | **CERRADO 2026-07-05 (BLOQUE 1)** — Momentos (barra + viewer 9:16 + upload center-crop) + Memorias (5ta vista: grid oficial 2x2 + PhotoViewer marco fijo contain + ContestBanner podio/countdown + orden por likes) espejo Expo completo (`50a0f79`). Denominador re-baseado 41→28 (los 13 extra eran items eliminados en auditoria que seguian contando). +29 vitest +12 E2E, verificado vivo | **+28** |
| **W.7 Sponsors** | **23/23** | **CERRADO 2026-06-21** | **+23** (Sprint 2.A entero — skeleton + tooltip + E2E 12/12 + Lighthouse acc 98) |
| **W.8 Networking** | **21/21** | **CERRADO 100% 2026-07-04** (link sidebar identity → /perfil + 5 E2E nuevos + 4 items reclasificados formalmente: filtro role skip por privacy, RT listeners → W.11, sugeridos cards → Fase 2, tracking → Fase 2) | **+6** |
| **W.9 Engagement** | **35/35** | **CERRADO 2026-06-29 (Sprint 2.B)** | **+5** (2026-06-29: redemptions inline + E2E 11/11 verde con viewports desktop/tablet H/mobile + 8 tests vitest. counter PARITY sync) |
| **W.10 Live Hub** | **16/16** | **CERRADO por consenso 2026-06-20** | **+16** (creado en commit `0e185e6`, validado visual con seeder) |
| **W.11 Sockets RT** | **22/22** | **CERRADO 2026-07-04 noche** (GlobalSocketProvider 6 listeners + prop-sync 3 vistas + 11 vitest + 2 E2E + verificacion viva pipeline Laravel→socket→cliente. Scope 42→22: game/staff/cross-tab RT/stress reclasificados a W.12/W.15/W.16) | **+14** |
| W.12 Polish + E2E + PWA | **25/48** | **52%** — **2026-07-05 tarde BLOQUE 5 Fases A+B (`b9aa4df` backend + `2dc43a3` + `471cf94` web)**: Web Push COMPLETO (VAPID + push_subscriptions + transporte multi-canal en 13 call-sites + SW + soft prompt + scheduled→announcement, verificado vivo Chrome+FCM) + PWA 5/5 (manifest, SW, install desktop/tablet, offline) + CSP completo 13 directivas + robots noindex + titles 13 rutas (SEO OG/sitemap → Fase 2) + print agenda + code splitting 7 componentes. Falta real (Fase C): QA device, Lighthouse batch, WCAG audit, E2E cross-tab, DSN prod | **+17** |
| **W.13 FAQ + Docs** | **15/15** | **CERRADO 100% 2026-07-04** (Fase A FAQ Asistente orb + Fase B Documents split layout + backend ZIP escalable. Pages reclasificado formalmente a Fase 2) | **+2** |
| **W.14 Anuncios + Cartel + Bell** | **19/19** | **RE-CERRADO 2026-07-05** — cartel solo-highlights (espejo Expo) + feature banners muerta de raiz (BLOQUE 3: ruta+controller+Filament+modelo+migration drop). BD dedup + vigencias frescas | **+5** |
| W.15 Vendor Dashboard | 0/35 | **MOVIDO a Mobile parity (decision Kamilo 2026-07-05)**: el staff del stand NO va a instalar app para un evento — vendor se hace como feature del webapp MOBILE (viewport celular espejo Expo, patron Mi QR), incluyendo scanner QR con camara en browser (prior art: eventos-kiosko). Fuera del denominador Fase 1 desktop. Procedencia verificada: ~3.000 lineas Expo + 18 endpoints | — |
| **W.16 Live Moments** | **5/5** | **CERRADO 100% 2026-07-09 (BLOQUE 4)** — TriviaPanel espejo Expo en la columna interactiva del streaming (4 fases idle/question/result/finished): countdown drenante rojo <=5s, opciones A-F color Kahoot, reveal de distribucion animada, mini-leaderboard + podio top 5. **Noir puro via --st-* (adapta Lux), UNICO color = letras A-F, cero iconos (decision Kamilo)**. Hook local `useTrivia` (patron useQnA) + reducer puro. Proxy `/events/games/{id}/answer`. Toasts ruleta/jackpot en GlobalSocketProvider. 10 vitest. **Validado en vivo** (launch->question->answer correcto score 135->result) via Mission Control + QaTriviaSeeder | **+5** |
| **W.17 Soporte** | **13/13** | **CERRADO 100% 2026-07-04** (split layout espejo W.14 + form nueva consulta + subflow FAQ + backend announcement on ticket-resolve. RT respuesta → W.11 via `data:invalidate{announcements}` (OJO: `support:new_response` NO existe como evento — auditoria 2026-07-04) + Web Push → W.12) | **+2** |
| **W.18 Hub Personal** | **19/19** | **100% — CERRADO 2026-07-04** (split 35/65 espejo W.13/W.14/W.17. Wall: hero+stats+rows+footer. Panel der: 3 sub-views Datos/Intereses/Apariencia. Data form con 3 cards visuales agrupando + 1 solo Guardar. Intereses chips min 1 con empty state. Apariencia Lux/Noir cards con preview aplicando via useTheme. Logout modal confirm. **Foto upload + shuffle beam avatar** (PerfilAvatarMenu popover: subir/cambiar variante/eliminar, seed en localStorage scopeado por email, beam URL espejo Expo). Deep link `eventos://profile[/sub]`. Sidebar refactor + ProfilePopover eliminado. 391/391 vitest + 13/13 E2E) | **+17** |
| ~~W.X Welcome Showcase~~ | **ELIMINADO** | Invento de planeacion (auditoria 2026-07-04): el onboarding Expo es un wizard de REGISTRO, no un carrusel de features. Boton perfil oculto (`4325f05`). Fase 2 si se quiere espejo del wizard | — |
| **TOTAL** | **549/576** | **95.3%** — Fase 1 desktop (W.15 → Mobile parity 2026-07-05) | **2026-07-09 BLOQUE 4 CERRADO**: W.16 Trivia 0→5 (TriviaPanel espejo + useTrivia + proxy + toasts, validado en vivo). Quedan: B5 Fase C QA (~2h CON Kamilo presente) + Mobile parity. **18 modulos cerrados:** W.0, W.1, W.1B, W.2, W.3, W.4, W.5, W.6, W.7, W.8, W.9, W.10, W.11, W.13, W.14, W.16, W.17, W.18 |

> Conflicto W.10 resuelto 2026-06-20: el codigo creo "W.10 Live Hub" reusando el numero. Doc viejo "W.10 Hub Personal" se renombra a W.18 Hub Personal. Sin refactor de codigo, solo doc.

---

## QUE SIGUE (1 sola tarea concreta)

- [ ] **MOBILE PARITY — QA vivo M.2 con Kamilo, luego M.3 Networking + Perfil.** 20/58: M.0 shell 6/7 + M.1 Home/MiQR 8/8 (commiteado `ab416fd`, QA vivo OK con fix HUD img 118) + **M.2 Agenda 5/5 IMPLEMENTADO 2026-07-09** (pendiente commit + QA vivo: /mi-agenda favoritos + /agenda stack + /session/{id} detalle + corazon particulas + ICS). Despues M.3: NetworkingScreen 3 tabs + attendee/[id] + ProfileScreen. Item M.0 restante (gates banned/aprobacion + deeplinks) se verifica en QA.

> BLOQUE 4 (W.16 Trivia) YA cerrado 2026-07-09. BLOQUE 2 (Home) cerrado 2026-07-08. Quedan solo Mobile parity + B5 Fase C.
> Cuando Kamilo tenga 2h presenciales: **B5 Fase C** (QA device iPad/iPhone/Edge/Firefox + Lighthouse + WCAG + E2E cross-tab + DSN prod). Fase A 100% validada 2026-07-05 (push desde Filament OK + install PWA OK).
> Deuda menor W.16 (no bloqueante): E2E Playwright de trivia (tengo 10 vitest del reducer/helpers) — mismo criterio que /encuestas.

> Deuda menor W.2 (no bloqueante): E2E Playwright de /encuestas (tengo vitest 9 tests) · verificar Lux con ojo · recap/certificado como pantalla aparte (Fase 2, hoy el codigo recap sigue en lib/recap sin consumir desde Home) · streaming poll-vote proxy pega a /polls/{id}/vote sin prefijo /events (posible bug latente pre-existente, la ruta real unica es /events/polls/{id}/vote).

> Deuda menor anotada 2026-07-05: `ProfileSecurityTest` 2 tests rojos PRE-existentes
> (esperan 422 para linkedin/website sin http; el validator W.18 se relajo a proposito
> espejo Expo y los tests no se actualizaron). Decidir: aceptar string plano en tests
> + agregar rechazo de schemes peligrosos (`javascript:`/`data:`) al validator.

---

## PLAN DE CIERRE TOTAL — BLOQUES (definido 2026-07-04, inventario 4 agentes contra codigo)

> **Meta:** cierre 95-100% del scope COMPLETO (656 items), incluyendo W.15 + W.16 + W.X.
> Decision Kamilo 2026-07-04: revierte skips de W.16 y W.15. Total ~30-38h en 6-8 sesiones DaVinci.
> W.X va de ULTIMO por diseño (reusa componentes reales en miniatura de todos los modulos cerrados).
> Los sprints historicos cerrados (Sprint 0-2, 2026-06-20 → 2026-07-04) viven en git de este doc.

### BLOQUE 0 — Contabilidad + fixes rapidos — 3/3 **CERRADO 2026-07-04**
- [x] Marcar las ~11 victorias gratis en counters (W.3 +2, W.6 +3, W.12 +6) — validado 402/402 vitest + typecheck
- [x] Fix alias `sessions` en `KNOWN_ENTITIES` de `useGlobalSocket.tsx:124` (backend puede emitir `agenda` o `sessions`)
- [x] Boton "Ver introduccion de nuevo" OCULTO en PerfilView (navegaba a /onboarding → 404). E2E perfil 13/13 ajustado (asserts toHaveCount(0)). Re-habilitar en BLOQUE 7

> **AUDITORIA DE PROCEDENCIA 2026-07-04 (3 agentes contra Expo real + backend):** todo
> item del plan quedo verificado contra codigo. INVENTOS DE PLANEACION eliminados
> (nunca existieron en Expo ni backend): W.2 sponsors band + multi-sede + proximos
> eventos org + dedupe · W.3 badges AJUSTADA/CANCELADA (API no expone los 3 tiempos,
> Expo NO pinta badges — el espejo real es el toast agenda:delayed que YA tenemos) +
> conflict detector + boton self check-in (el asistente presenta QR, staff escanea) +
> URL state (→Fase 2) · W.16 sorteo ceremony + golden reveal full-screen (Expo solo
> hace toast + anuncio + modal por tap en hub — eso SI se espeja) · W.15 invite sin
> login (backend exige sesion) · W.X showcase 6 beats (el onboarding Expo es un wizard
> de REGISTRO, no un carrusel de features) · Banners (feature legacy — highlights la
> reemplazo; el home Expo solo renderiza highlights).
> Regla nueva: **todo item de backlog debe citar procedencia** (pantalla Expo
> archivo:linea, endpoint backend, o decision explicita de Kamilo).

### BLOQUE 1 — W.6 Social → 100% — 7/7 **CERRADO 2026-07-05** (`50a0f79`)
- [x] ~~Load-more/paginacion~~ — **HECHO 2026-07-04** (`7afb5d0`): infinite scroll cursor-based + dedup + sentinel. Verificado vivo 99 posts
- [x] ~~Hashtags click-to-filter~~ → **ELIMINADO** (decision Kamilo; parser tambien borrado del codigo `f0bedaa`. Idea menciones → Event Pulse, en memoria)
- [x] **Stories = "Momentos"** — barra sobre el feed (visible en Feed y Memorias), anillo accent no-visto / tenue visto, "visto" en localStorage `eventos:social:story-seen:{eventId}` reactivo (useSyncExternalStore)
- [x] **MomentosViewer** — card 9:16 centrada DENTRO del CanvasCard (patron Instagram desktop), auto-advance 5s, dots, click zones 40/60, teclado+Esc+chevrons+click afuera, marca visto al abrir, sin salto entre autores
- [x] **Upload momento** — preview 9:16 + center-crop canvas + POST multipart + toast espejo "Momento subido! Visible por 24h."
- [x] **Vista Memorias** — 5ta vista sidebar: grid 3 col oficial 2x2, PhotoViewer marco FIJO 16:9/9:16 con contain (nunca recorta), like optimistic+haptic+pop, foto propia → toast informativo (anti-gaming), upload header crop 1:1
- [x] **ContestBanner** — solo active/ended<24h, countdown vivo useNow, podio oro/plata/bronce 72/56, orden por likes client-side (grid+viewer indices compartidos)

### BLOQUE 2 — W.2 Home → 100% — 4/4 **CERRADO 2026-07-08** (`c30b55d` web + `127693a` backend)
- [x] ~~Foto real speaker en feed salas~~ — **HECHO 2026-07-04**: `RoomAvatar` usa `speaker_photo_url` con gradiente fallback (espejo Expo session-stream:272)
- [x] **GamificationHud LIVE** — slide extra del carrusel de highlights (`CartelDigital`), espejo Expo `GamificationHud`: borde RGB girando 6s + barra segmentada de 10 + rank/puntos/retos/stamps, toda la card deeplink a /desafio. Tamano fluido cqw+em (no desborda el slot 16:9). Datos SSR via `fetchDesafioOverview` → `deriveHudData`. Paleta teal fija (no accent). Dwell 10s vs 6s highlights
- [x] **Post-event survey prompt ENDED** — tarjeta en el EventArchive (pendiente "Responder"→/encuestas / completada). Estado via `fetchPostEventSurvey` (GET /events/{id}/post-event-survey)
- [x] **EventArchive ENDED** (espejo Expo puro, decision Kamilo: recap/certificado fuera del Home) — banner "Evento finalizado" + fecha, 3 stats del evento (asistentes/sesiones/fotos, `photo_count` nuevo en by-slug), 4 links archivo (agenda/social/desafio/speakers). Reemplaza el recap-col
- [x] **DESTINO /encuestas** (no estaba en el plan; el prompt sin destino era boton muerto) — `SurveyDeck` por slides espejo Expo `PollSlides` elevado a EventOS: 1 pregunta/slide, transicion spring, opciones en cards con pop + check dibujado, **estrellas que se llenan en cascada** al seleccionar, cierre verde de exito (NO accent que puede ser rojo=alarma), voto **optimista** (feedback instantaneo). Reutiliza el sistema de polls + proxies /api/surveys. Deeplink eventos://encuestas. Cero dots, tokens Lux+Noir

### BLOQUE 3 — Cartel espejo + BD limpia — 3/3 **CERRADO 2026-07-05**
- [x] CartelDigital → **solo highlights** (`highlightsToCartelItems`, merger round-robin y sponsor pill eliminados; backend ya filtra vigencia con scope active). `lib/banners.ts` borrado
- [x] **Feature banners MUERTA de raiz**: ruta + BannerController + BannerResource Filament + modelo + BannerTest + seeders (Content/ModuleTemplate) + catalogo modulos (CreateEvent/ModuleResource) + migration `drop_banners_feature` (tabla + module_templates + modules slug=banners). Endpoint verificado 404. stress-full.js → highlights
- [x] BD limpia: 4 duplicados de `event_highlights` borrados + vigencias refrescadas (4 highlights activos 3 dias). Verificacion: 401/401 vitest + 12/12 E2E cartel+home + suite backend Content (2 fallos PRE-existentes de ProfileSecurityTest, ajenos — validator W.18 relajado sin actualizar tests, anotado abajo)

### BLOQUE 4 — W.16 Live Moments espejo real — 5/5 CERRADO 2026-07-09
> Espejo estricto de lo que Expo HACE: trivia completa + toasts de ruleta/jackpot. Validado en vivo (MC + QaTriviaSeeder). Commit eventos-web `main`.
- [x] TriviaPanel espejo (`TriviaPanel.tsx`): fases idle/question (A-F + countdown rojo ≤5s) / result (+X pts, distribucion animada, top 3) / finished (podio top 5). **Noir --st-*, unico color letras A-F, cero iconos (Kamilo)**
- [x] `useTrivia` (hook local patron useQnA, NO store global — la webapp no tiene zustand) + reducer puro `lib/trivia.ts`, alimentado por `game:question`/`game:round-result`/`game:finished`
- [x] `POST /events/games/{gameId}/answer` proxy (prefijo /events explicito) — NO optimista (backend devuelve correct/score, espejo Expo)
- [x] Listeners `game:launched`/`game:result` → toasts en GlobalSocketProvider + router.refresh (puntos/premios)
- [x] 10 vitest (reducer + helpers). Deuda menor: E2E Playwright (mismo criterio /encuestas)

### BLOQUE 5 — W.12 Polish/cierre — 8/11 (Fases A+B CERRADAS 2026-07-05 tarde; falta Fase C QA)
- [x] **Web Push real CERRADO** (`b9aa4df` backend + `2dc43a3` web): VAPID + tabla push_subscriptions + endpoints + `SendWebPushJob` + transporte multi-canal `toAttendee()` en 13 call-sites + filtros web-only + SW con PUSH_ROUTES espejo Expo + soft prompt pill (divergencia web aprobada) + track push_open + recordatorios de sesion incluidos. **Fix aprobado: scheduled → Announcement** (la push es el golpe, el announcement es la carta). Verificado VIVO: Chrome real + FCM + click routing + Bell live. Bug pre-existente cazado: ban Filament limpiaba token antes de la push
- [x] **PWA CERRADO** (`2dc43a3`): manifest + iconos (anillo dorado noir) + install prompt via useInstallPrompt en /perfil SOLO >=1024px + offline.html estatico + SW
- [x] **CSP completo CERRADO** (`471cf94`): 13 directivas, connect-src backend+socket desde env, dev relajado por NODE_ENV. Verificado vivo + suite E2E bajo la politica
- [x] ~~`loading.tsx` en 7 rutas sin cubrir~~ — **HECHO 2026-07-05** (`29fce3d`): soporte, faq (shape del orb), anuncios, desafio (cards heterogeneas), documentos, session-stream (tokens --st-*), perfil. 13/13 rutas con skeleton que calca el layout real
- [x] **EXTRA 2026-07-05** (`29fce3d`): token global `--heart: #ff5d6c` + barrido haptics 13 modulos. **No-optimistas (agenda rating / soporte ticket / forms perfil): SKIP FORMAL 2026-07-05** — optimista es para toggles; forms con validacion 422 o que necesitan id del server DEBEN esperar (decision Kamilo)
- [x] **Code splitting CERRADO** (`471cf94`): dynamic() ssr:false en 7 componentes post-interaccion (AttendeeProfilePanel, PhotoViewer, MomentosViewer, CropUploadModal, RedeemModal + GoldenTicketPanel con qrcode.react, DocumentPreview + FileKindIcon extraido). Lazy framer-motion global NO (33 archivos, riesgo > ganancia)
- [x] **Print agenda CERRADO** (`471cf94`): documento imprimible (papel blanco, sin chrome, break-inside avoid), verificado visual 2 iteraciones
- [x] **SEO recortado con higiene** (decision Kamilo 2026-07-05): robots.ts noindex total + title por ruta 13 paginas. OG images + sitemap → **Fase 2 formal** (app auth-gated)
- [ ] **Fase C** — QA device real 3 viewports + Edge/Firefox + Lighthouse batch + WCAG audit (CON Kamilo)
- [ ] **Fase C** — E2E cross-tab (streaming Q&A, social conectar)
- [ ] **Fase C** — DSN prod Sentry + validacion (item de deploy; config completa ya en codigo)

### MOBILE PARITY — workstream 26/58 (baseline CERRADO 2026-07-09, 100% Fable)

> **Enfoque acordado (2026-07-05):** NO portar componentes RN (react-native-web
> descartado). Capa de PRESENTACION mobile nueva sobre la capa de datos existente
> (fetchers SSR + proxies + clients: 100% agnostica del layout — verificado en
> baseline). Transcripcion pantalla-por-pantalla del Expo real.
>
> **Baseline 2026-07-09 (5 agentes: 4 sobre Expo real + 1 sobre eventos-web):**
> - **Molde arquitectonico ya existe**: `StreamShell.tsx:187-193` bifurca con
>   `useIsMobile()` (client matchMedia, SSR-safe) en 3 variantes; logica en el
>   padre, variantes = puro layout. Breakpoint canonico `mobile <640px`
>   (`globals.css:8`). NO hay UA sniffing en el repo — la bifurcacion es client.
> - **Hoy a 390px la webapp esta ROTA sin guard**: canvas ~240px
>   (`globals.css:449` asume sidebar desktop) + SidebarPill flotando encima.
>   TabletRotateOverlay NO cubre <640px.
> - **Shell Expo a espejar**: `FloatingTabBar.tsx` (354) — pill flotante blur,
>   burbuja deslizante spring, badge rojo networking, 5 tabs:
>   Inicio / Mi Agenda / Mi QR / Networking / Perfil. Sin headers nativos
>   (cada pantalla dibuja el suyo; bell solo en Home). Scrolls reservan
>   `useTabBarHeight` (68 + safe area) como padding inferior.
> - **MAPA DE NAVEGACION ESPEJO (correccion Kamilo 2026-07-09 — la webapp
>   mobile NO inventa entradas de menu que Expo no tiene):** ModuleMenu del
>   Home = SOLO 4 modulos hardcoded Agenda/Speakers/Social/Sponsors
>   (`ModuleMenu.tsx:26-31`) + Room Check-in (staff) / Asignar Staff (admin)
>   por rol. El resto se alcanza asi: Anuncios = campana del Home; Desafio =
>   slide JUGAR del HUD (`GamificationHud.tsx:163`) + deeplink + EventArchive;
>   FAQ = boton Ayuda del Perfil (`ProfileScreen.tsx:275`); Soporte = via FAQ;
>   Encuestas = EventArchive ENDED (`EventArchive.tsx:49`) + PollSlides
>   overlay en chat/stream; About = card del Home SOLO registration/draft
>   (`index.tsx:170-185`); **Documentos/Pages = SIN navegacion propia** — solo
>   via highlights `link_url` / anuncios `action_url` (deeplinks internos,
>   `HappeningNow.tsx:347-356`). El modulo existe pero es opcional/invisible.
>
> **NO se espeja (procedencia verificada):** `passport.tsx` standalone
> (huerfana — cero navegaciones entrantes; el pasaporte real es sheet del hub
> Desafio) · `banners.tsx` (muerta) · onboarding wizard (decision W.1/W.X) ·
> `recap/[eventId]` → Fase 2 (decision W.2) · `pages/[id]` → Fase 2 (decision
> W.13) · **staff-checkin + assign-staff → FUERA (decision Kamilo 2026-07-09:
> el staff no va en el workstream mobile; kiosko cubre check-in)** ·
> activate-account/pending-approval (auth webapp = magic link W.1; revisar en
> M.0 si el gate de aprobacion aplica).
>
> **Decisiones cerradas 2026-07-09 (no re-preguntar):**
> - **session-chat = comportamiento espejo OBLIGATORIO como modo de
>   StreamShellMobile**: el routing de UNIRTE es por stream configurado
>   (`HappeningNow.tsx:182-186`) — sesion CON `stream_url`/`stream_iframe` →
>   player (+ placeholder si retrasada); sesion SIN stream → chat full-height.
>   NO se crea ruta `/session-chat` aparte (mismos sockets/paneles).
> - **about.tsx entra** como pantalla chica en M.7 (webapp no tiene /about).
> - **Orden de bloques M.0→M.8; M.0 primero y bloqueante.** Mockup DaVinci del
>   tab bar aprobado ANTES de codear.

#### M.0 — Shell mobile (fundacion, bloquea todo) — 5/7
- [x] **Bifurcacion shell CSS-first** (2026-07-09): chrome desktop (`AmbientBackground`/`SidebarPill`/`ThemeTogglePill`) en wrapper `contents mobile:hidden`; `Stage` con variantes `mobile:block p-0`; `MobileGate` oculta la vista desktop <640px y muestra placeholder digno (CTA volver al inicio) hasta que cada ruta entregue su vista mobile (`MOBILE_READY`, /session-stream ya bifurca solo). Cero flash de hidratacion (CSS decide, no JS)
- [x] **MobileTabBar espejo `FloatingTabBar.tsx`** (2026-07-09, mockup DaVinci aprobado + 3 decisiones: blur espejo en Noir / accent en activa / con labels): pill flotante blur 380 max, burbuja lente framer spring damping22/stiffness340 + highlight especular 45%, lift -3px, press scale 0.88, labels 9px, badge rojo solicitudes (SSR + auto-refresh via router.refresh de networking:notify), vibrate soft. **Solo visible en las 5 rutas tab** (espejo: stack screens sin tab bar). Rutas nuevas /mi-qr y /networking con `DesktopRedirect` (>=640 → /home y /social)
- [ ] Patron header mobile por pantalla (espejo: header propio + back pill; bell con badge/shake SOLO en Home — `HomeHeader.tsx`) — llega con las primeras pantallas M.1
- [x] Reserva altura tab bar: `--m-tabbar-reserve` (68 + safe area + 15, espejo `useTabBarHeight.ts`) en mobile-shell.css — usada por placeholder y PushPrompt
- [x] Guard CSS mobile: canvas/Stage neutralizados <640px (vista desktop ademas oculta por MobileGate)
- [x] PushPrompt: bottom sube sobre el tab bar en mobile (`calc(var(--m-tabbar-reserve) + 12px)`)
- [ ] Deep links `eventos://` operativos en shell mobile (parseActionUrl existente) + gates banned/aprobacion espejo `(app)/_layout.tsx:63-73` — verificar al cerrar M.1
> Verificacion 2026-07-09: typecheck + lint 0 errores · 497/497 vitest (+9: MobileTabBar 6 + MobileGate 3) · **E2E nuevo `mobile-shell.spec.ts` 7/7** (tab bar 5 tabs + activo, navegacion, placeholder + desktop oculto, stack sin tab bar, redirects desktop) · screenshots 390px OK (badge, burbuja accent, placeholder)

#### M.1 — Home + Mi QR (tabs 1 y 3) — 8/8 **IMPLEMENTADO 2026-07-09 (QA vivo Kamilo pendiente)**
- [x] **Home mobile por status** (`HomeMobile.tsx` espejo `(tabs)/index.tsx:143-271`): registration/draft = hero+countdown+info card+about · published = countdown compact+HappeningNow+modulos compact · live = HappeningNow+modulos · ended = EventArchive (reuso componente espejo W.2). Datos: `fetchEventBranding` NUEVO (GET /events/{id}/branding — la MISMA fuente que Expo useBranding) + agenda flatten + announcements. Dual render CSS en home/page.tsx
- [x] HomeHeader mobile: logo/header_title + bell → /anuncios con badge unread (localStorage lastSeenAt reuso W.14) + shake CSS cada 5s + badge pop spring
- [x] HappeningNow mobile (`HappeningNowM.tsx` espejo 573): dark island NOIR fijo, carrusel crossfade 400ms con rotacion 6s/10s HUD, dots por tipo (accent/teal/rosa), PulseDot, SpeakerRow stack + favoritos "+N asistiran", HighlightCard con link_url interno/externo, UNIRTE → /session-stream (chat-mode M.7). Status visual: `session-visual-status.ts` transcrito de `sessionStatus.ts` Expo, derivado con useNow (sin setState-in-effect)
- [x] ModuleMenu mobile: SOLO 4 cards espejo (`ModuleMenu.tsx:26-31`), ratio 0.58, stagger 60ms, compact published
- [x] GamificationHud slide: reuso del componente espejo W.2 dentro del carrusel (verificar tamano en QA vivo)
- [x] Proxy `/api/mi-qr` + fetcher `fetchQrToken` (GET /me/qr?event_id)
- [x] Mi QR espejo `MiQrScreen.tsx` (503): badge card negra siempre + DashedLine + **RgbWaveBorder pastel girando 6s (CSS)** + QR rotativo countdown 60s auto-refetch + tap fullscreen bg 0.88 + identidad beam avatar + formatEventDates transcrito
- [x] Wallet pill "Pronto" disabled (espejo)
> BONUS M.1: **/about implementado** (adelantado de M.7 — la card About del Home registration lo necesitaba; `AboutView` espejo `about.tsx` 175: imagen 16:9 + texto + links, back pill, desktop redirige) + **MobileHeader** patron header (cierra item 3 de M.0).
> Verificacion 2026-07-09: typecheck+lint 0 errores · 509/509 vitest (+12: MiQrView 6 + HomeMobile/status visual 6) · E2E mobile-shell 9/9 (home mobile hero+4 modulos+bell, mi-qr badge+fullscreen+tab activa) · screenshots 390 revisados (home live + mi-qr fieles al Expo). QA vivo pendiente: HUD slide con datos reales, estados published/ended/registration con backend real, hero imagen, Lux

#### M.2 — Agenda mobile (tab 2 + stack) — 5/5 **IMPLEMENTADO 2026-07-09 (QA vivo Kamilo pendiente)**
- [x] **DayStrip Fever + TrackFilter** (`AgendaMobile.tsx` espejo `AgendaScreen.tsx:60-209`): pills 52x76 con dia activo accent + dot dia-con-eventos + relleno ±1 deshabilitado + auto-scroll centrado (scrollTo, NO scrollIntoView — leccion err 113); chips de track con dot color + "Todos"
- [x] **SessionCard timeline** (espejo `:400-624`): hora 12h es-CO + TimelineDot pulso live + conector + badges EN VIVO/check/track + **corazon --heart con pop + ring + 6 particulas** (canon webapp, divergencia aprobada del accent Expo) + finished tachada/atenuada + speakers stack + Calendario (.ics W.3 reuso) / Evaluar / UNIRTE / Ver grabacion + DaySlide 280ms + highlight deeplink glow 2.5s
- [x] **Rutas espejo estructura Expo**: tab = `/mi-agenda` NUEVA (favoritesOnly, con tab bar, boton "Todas" = downloadAgendaIcs bulk W.3, quitar favorito saca de la lista) · `/agenda` completa = stack (back pill, SIN tab bar) con dual render CSS · desktop /mi-agenda → /agenda
- [x] **Detalle de sesion `/session/[id]` NUEVA** (`SessionDetailM` espejo `session/[id].tsx` 458): badges live/tipo/track + titulo 26 + card fecha/hora/lugar/capacidad + Favorita/Calendario/Evaluar + stream full-width + "Acerca de" + speakers clickeables (→ /speakers hasta M.5) — **decision: ruta (espejo), en desktop redirige a /agenda?highlight={id}** (el panel ES el detalle desktop)
- [x] **RatingModalM**: reuso del RatingModal W.3 con wrapper fixed `.agenda-root` (los estilos del pop estan scopeados — bug QA cazado en screenshot: renderizaba inline; +fix bg transparent)
> Verificacion 2026-07-09: typecheck + lint 0 errores (2 violaciones react-hooks refactorizadas: dia activo derivado + slide dir en state) · 517/517 vitest (+8: AgendaMobile 5 + tabbar/gate updates) · E2E mobile-shell 11/11 (mi-agenda tab activa + agenda stack sin tab bar + tap card → /session/{id}) · screenshots 390 revisados (timeline/pills/detalle fieles). QA vivo pendiente: heart particulas, DaySlide, rating real, "Todas" ICS, Lux

#### M.3 — Networking + Perfil (tabs 4 y 5) — 6/6 **IMPLEMENTADO 2026-07-09 (QA vivo pendiente) — LAS 5 TABS DEL SHELL COMPLETAS**
- [x] **Networking 3 tabs** (`NetworkingM.tsx` espejo `NetworkingScreen.tsx:139-176`): pills segmented accent + badge rojo count solicitudes (badge verde contactos-nuevos requiere store socket → nota deuda)
- [x] **Directorio**: search server-side debounce 400ms + **infinite scroll IntersectionObserver** via proxy NUEVO `/api/social/attendees` + contador "N asistentes" + footers espejo + sugeridos carousel horizontal (cards 160x260: comun + tags + CTA por relation) + quick-connect optimista con relation override
- [x] **Contactos**: rows + "Guardar contacto" = .vcf single + "Exportar todos (.vcf)" multi-card + bloqueados colapsable con desbloquear optimista
- [x] **Solicitudes**: cards con mensaje quote + timeAgo + Aceptar/Ignorar optimista ("Conectado con X")
- [x] **Perfil de asistente `/attendee/[id]` NUEVA** (`AttendeeDetailM` espejo `attendee/[id].tsx` 613): hero horizontal 80 + CTA por relation + sheets Connect (mensaje 500) / Block (confirm rojo) + bio + intereses comunes resaltados + sus sesiones → /session/{id} + redes (SVGs inline, licencias lucide) + Contactar mutuos (wa.me/mailto/.vcf) — desktop redirige a /social. Nota espejo: los botones Aceptar/Ignorar de Expo ahi NO tienen handler; web enlaza al tab Solicitudes
- [x] **Perfil propio `PerfilM` IMPLEMENTADO 2026-07-09** (espejo `ProfileScreen.tsx` 927): hero centrado avatar 96 + badge camara → SheetM foto (input capture)/galeria/shuffle beam x3 (seed W.18 por email)/volver-al-avatar · redes brand SVG → abren edit · stats Puntos/Retos/Logros · Mis datos + modal Editar full-sheet (basicos + redes, merge patch preservando email — leccion W.18) · Mis intereses inline espejo MyInterests (violeta semantico FIJO, min 1) · toggle tema next-themes · logout reusa PerfilLogoutModal (cross-tab + disposeSocket). "Ver introduccion" OMITIDO (decision Bloque 0). SheetM extraido a shell/mobile (compartido con attendee)
> QA fixes 2026-07-09 (feedback Kamilo img-less): halo avatar attendee centrado (grid 88) · sheets con slide-up+backdrop fade+sombra · vibrate en enviar solicitud/bloquear/desbloquear · active:scale en filas/cards · fade-up sutil en filas de directorio (divergencia minima, Expo no anima — vetable). Intereses en comun: shape backend verificado OK, pendiente cross-check Kamilo vs panel desktop
> Verificacion 2026-07-09: typecheck+lint 0 errores · 517/517 vitest · E2E mobile-shell 12/12 (+networking: 3 tabs + tap directorio → /attendee/{id}) · mockBackend +/attendees/{id}/profile · screenshots 390 revisados (networking + attendee fieles)

#### M.4 — Social mobile — 0/7
- [ ] Header sticky blur + SegmentedControl Feed/Memorias (espejo `social.tsx:183-240`)
- [ ] MomentosRow + viewer stories mobile (viewer 9:16 W.6 ya existe — adaptar a viewport completo)
- [ ] Feed mobile: lista virtual + pull-to-refresh (espejo FlashList `:305-328`; infinite scroll cursor W.6 ya existe)
- [ ] SocialFAB contextual (espejo `SocialFAB`: crear post o subir foto segun segmento — webapp desktop NO usa FAB, mobile SI es espejo)
- [ ] CommentsSheet bottom sheet mobile (espejo `CommentsSheet`)
- [ ] CreatePostModal + upload foto 1:1 mobile (crop W.6 reusable)
- [ ] Memorias mobile: PhotoGrid + PhotoViewer marco fijo + ContestBanner (W.6 ya espejo en desktop — layout mobile)

#### M.5 — Speakers + Sponsors mobile — 0/5
- [ ] Speakers mobile (espejo `speakers.tsx` 308: search debounce + Destacados carousel + lista con badge sesiones)
- [ ] Detalle speaker mobile (espejo `speaker/[id].tsx` 295: hero foto cuadrada + rating + LinkedIn + bio + sus sesiones)
- [ ] Sponsors mobile (espejo `sponsors.tsx` 427: tiers platinum→media, living shuffle 7s pausado en scroll/search, pull-to-refresh, search 350ms)
- [ ] Detalle sponsor mobile (espejo `sponsor/[id].tsx` 690: hero logo + sesiones + servicios/contacto chips + banner trivia + modal trivia + website/email)
- [ ] Contacto sponsor mobile (espejo `sponsor-contact.tsx` 215: chips servicios + mensaje + enviar — ruta o sheet, decidir en diseno)

#### M.6 — Desafio mobile — 0/3
- [ ] Hub espejo `leaderboard.tsx` (1421): hero HUD (posicion + puntos + SegmentedBar + mini-ranking top-3 con RGB ring) + cards Premios/Golden/Retos/Pasaporte + MotivationalTip — **Noir forzado ("dark island"), DesafioView 368 tiene la logica**
- [ ] 6 bottom sheets espejo: Retos / Ranking (podio + confeti) / Pasaporte / Rewards / Redeem-confirm / Rules (snap points `:590-813`)
- [ ] Modales QR espejo: RedeemQrModal (QR + countdown expiracion en vivo `:843-898`) + GoldenTicketModal (QR + claim_code `:900-993`)

#### M.7 — Comunicacion + streaming QA — 1/8
- [ ] Anuncios mobile (espejo `anuncios.tsx` 149: cards imagen+timeAgo + deep links + pull-to-refresh)
- [ ] Encuestas mobile (espejo `encuestas.tsx` 187: lista activas/cerradas + PollSlides — SurveyDeck W.2 ya es slides, adaptar viewport)
- [ ] Soporte mobile (espejo `my-support.tsx` 131 + `support-contact.tsx` 149: consultas con status + respuesta admin + form)
- [ ] FAQ/Asistente mobile (espejo `faq.tsx` 335: orb + state machine browsing/thinking/answering + chips categoria)
- [ ] Documentos mobile (espejo `documentos.tsx` 111: lista + abrir) — SIN entrada de menu, solo deeplink (espejo)
- [x] About — ruta NUEVA /about **HECHO en M.1 2026-07-09** (espejo `about.tsx` 175: imagen 16:9 + texto + links branding; entrada = card del Home SOLO registration/draft, espejo `index.tsx:170-185`)
- [ ] Chat full-height sin stream: StreamShellMobile modo sin video = chat pantalla completa (espejo `session-chat/[id].tsx` 204 + routing `HappeningNow.tsx:182-186` — comportamiento OBLIGATORIO, sin ruta nueva)
- [ ] QA StreamShellMobile existente contra Expo `session-stream/[id].tsx` (356): speaker row header, 3 estrategias de player, RatingModal delay, tracking

#### M.8 — Vendor W.15 (cierre del workstream) — 0/9
> Procedencia verificada: ~3.000 lineas Expo (8 pantallas) + 18 endpoints backend mapeados (auditoria 2026-07-04). Gating: `attendee.has_vendor_access` del `GET /auth/me` (`isVendor = role==='vendedor' || hasVendorAccess`).
- [ ] Hooks + gating hasVendorAccess (espejo `useStand`/`useLeads`/`standApi`/`leadsApi`)
- [ ] Mi Stand dashboard (espejo `mi-stand.tsx` 288: hero logo/tier/rol + 3 stat cards navegables — NO tabs)
- [ ] Mis Leads (espejo `leads.tsx` 195 + `lead-detail.tsx` 317: grouped Hoy/Ayer/fecha + tier hot/warm/cold + notas + historial ediciones) + export CSV (`GET /me/leads/export`)
- [ ] **Scanner stand QR con camara browser** (espejo `scanner-stand.tsx` 542: CameraView frame + scanline + sheet resultado — web: getUserMedia + BarcodeDetector, prior art eventos-kiosko)
- [ ] Scanner invite equipo (espejo `scanner-invite.tsx` 282: resolver QR asistente → invitar al stand, gating owner)
- [ ] Solicitudes stand (espejo `stand-contacts.tsx` 211: servicios interes + mensaje + acciones tel/mailto/wa.me)
- [ ] Stats (espejo `stand-stats.tsx` 360: overview + trend vs ayer + TierBar + MemberBar + top services)
- [ ] Team management (espejo `mi-equipo.tsx` 567: slots + invitar busqueda/email + share link + transfer + remove, owner-only)
- [ ] Join-team CON SESION (espejo `join-team/[token].tsx` 276 — auth requerido, NO publico) + tests

### ~~BLOQUE 7 — W.X Welcome Showcase~~ — **ELIMINADO 2026-07-04**
> El "showcase 6 beats" era invento: el onboarding real de Expo (~4.500 lineas) es un
> wizard de REGISTRO (welcome hero + auth + foto + forms dinamicos + survey). La webapp
> ya cubre su parte con W.1 magic link. Boton "Ver introduccion" queda oculto (Bloque 0).
> Si Fase 2 pide espejo del wizard completo, se disena entonces.

### PARALELO — PARIDAD DE CONFIG admin ↔ 3 superficies (detectado Kamilo 2026-07-09) — 0/3
> Gap real verificado en baseline: el admin configura modulos/branding pero las superficies
> obedecen a medias. Requiere diseno con Kamilo ANTES de codear (toca backend + Filament +
> Expo + webapp a la vez). NO bloquea Mobile parity (el espejo replica el comportamiento
> actual; al unificar se corrige en ambos lados en un movimiento).
- [ ] **Modulos fuente unica**: visibilidad de modulos por superficie desde config Filament. Hoy: Expo hardcodea 4 (`ModuleMenu.tsx:26-31`, solo gamification consulta `modules.enabled`), sidebar desktop hardcodea items (documentos gated por count). Cada superficie renderiza su layout, la visibilidad sale del admin
- [ ] **Keyvisual por superficie**: branding con `keyvisual_desktop` + `keyvisual_mobile` (fallback: desktop con crop focal si falta mobile) + Filament 2 uploads con preview por superficie. Expo consume el mobile. **Idealmente ANTES de M.1 Home mobile** (es cuando se necesita)
- [ ] **Hero modo texto**: contrato unico de branding (type image|text) renderizado a escala en las 3 superficies. Modo texto = fallback digno sin arte, keyvisual = camino premium (decision desktop vigente). Flag: revisar que hace hoy la webapp desktop sin keyvisual

### PARALELO — Event Pulse cliente (sesion dedicada ~1-2h) — 0/4
> Detalle en `docs/living/PENDIENTES.md` seccion Event Pulse. Formula counter ratings live≠F5 · Charlas vacia (room_id PulseController:102) · verificar leads/connections · poll:closed room null

### PARALELO — Backlog Expo (sesion Expo futura)
- [ ] Borrar `banners.tsx` + `BannerCarousel` + `bannersApi` (feature legacy muerta)
- [ ] `ENTITY_KEYS` sin `modules` (backend la emite, Expo la pierde)
- [ ] Double-count comment propio en `useWall`

---

## BACKLOG GRANULAR — TODO desglosado

### W.0 — Spatial UI base (24/24, **CERRADO 100% 2026-07-04**)

> Cerrado al reclasificar 3 items nice-to-have: Command palette ⌘K → Fase 2 (feature power-user, no critico para venta), Pre-load vecinos → W.12 Polish (optimizacion perf), Validar device real → W.12 Fase 0 (QA cross-modulos).

- [x] Setup canvas universal (1130×664 tablet H, 1600×920 desktop cap)
- [x] Sidebar pill flotante izq 52px con iconos (⌂ 📅 ●live 👥 💬 🏢 📰 👤)
- [x] Bell redondeado top-right
- [x] Canvas raiz formulas clamp
- [x] 3 estados shell (Browse/Live/Conectar)
- [x] Animaciones spring 350ms
- [x] Reduced motion respeto
- [x] Home cinematic mute 3 estados
- [x] Companion demo W.2
- [x] Shell W.0 implementado React validado 2026-05-04
- [x] Tokens Lumina Noir CSS vars portados
- [x] Ambient background sunken
- [x] EventThemeProvider primary_color dinamico
- [x] ProfilePopover con logout/idioma/tema
- [x] Stage centrado padding simetrico
- [x] CanvasCard width 1600 cap + height min(vh-56, 920)
- [x] Sin aspect-ratio rigido (fix tablet 2026-05-05)
- [x] Theme toggle pill bottom-left
- [x] Live pulse indicator
- [x] Connection status pill
- [x] ~~**Modulos navegables top-level desde sidebar**~~ 2026-06-21 — 5/5 items navegan (home, agenda, live, speakers, social). Sponsors disabled con tooltip "proximamente"
- [x] ~~**Command palette ⌘K**~~ → **reclasificado Fase 2** (power-user, no critico Fase 1)
- [x] ~~**Pre-load vecinos**~~ → **reclasificado W.12 Polish** (optimizacion perf cross-modulos)
- [x] ~~**Validar device real** iPad/Pixel/iPhone~~ → **reclasificado W.12 Fase 0** (QA cross-modulos)

### W.1 — Setup + Auth + UI Foundation (107/107, **CERRADO 100% 2026-07-04**)

> Cerrado al reclasificar 5 items diferidos: B4 StaggerList + B11 Swipe haptics → Fase 2 (polish device-only), Smoke device real + Lighthouse final → W.12 Polish, CSP whitelist Vimeo/Sentry → agrupado con W.4 cierre.

- [x] Next.js 15 scaffold TypeScript Tailwind shadcn
- [x] Tokens Lumina Noir/Lux CSS vars
- [x] i18n next-intl es-CO/en/pt-BR prefix
- [x] Magic link auth 4 steps (email/sent/password/verifying)
- [x] StateChart auth flow
- [x] Mailcheck typo detection
- [x] Network status banner
- [x] LoginSlideshow Ken Burns video
- [x] Password fallback completo
- [x] Layout shell + middleware auth gate
- [x] LuminaToast component
- [x] FormField component
- [x] EmptyState component
- [x] Skeletons base
- [x] Animaciones Framer Motion microinteracciones
- [x] Error boundaries
- [x] Sentry frontend integrado
- [x] Tests Vitest 22 + Playwright 12 (34/34)
- [x] CSP base configurado
- [x] DOMPurify base
- [x] Cross-tab sync logout
- [x] useExitGuard form dirty
- [x] (...75 items mas implementados, F0-F10)
- [x] ~~**B4 StaggerList animation**~~ → **Fase 2** (polish nice-to-have)
- [x] ~~**B11 Swipe haptics**~~ → **Fase 2** (requiere device real touch)
- [x] ~~**Smoke test 3 viewports device real**~~ → **W.12 Fase 0** (QA cross-modulos)
- [x] ~~**Lighthouse final pass**~~ → **W.12 Fase 3** (batch cross-modulos)
- [x] ~~**CSP whitelist Vimeo + Sentry final**~~ → agrupado con **W.4 Streaming cierre** (depende integracion completa)

### W.1B — Backend magic link (10/10, CERRADO)

- [x] Setup Mailpit
- [x] Migration `magic_link_tokens` SHA-256
- [x] Migration `event_login_slides` con video/overlay toggle
- [x] POST /auth/magic-link rate limit 3/email/hora anti-enum
- [x] POST /auth/verify-magic-link (410 expired/used)
- [x] POST /auth/refresh token rotation
- [x] GET /events/{slug}/login-slides publico
- [x] Filament LoginSlideResource drag-reorder
- [x] Mailable branded por evento
- [x] Pest tests happy path + 8 edge cases

### W.2 — Home (16/16, CERRADO 100% 2026-07-08)

- [x] Hooks useEvent base
- [x] Hero cinematic + wordmark
- [x] Estado PRE (countdown firma)
- [x] Estado LIVE (split 7fr/3fr poster + happening-now feed)
- [x] Estado ENDED (split 6fr/4fr poster + recap stats + conexiones)
- [x] HappeningNow feed scrolleable salas activas
- [x] Featured con tinte accent
- [x] Recap stats 2x2 + tier Gold + CTA "Ver mi recap" (2026-05-06)
- [x] EventPoster compartido 3 estados
- [x] Canvas raiz universal aplicado
- [x] ~~Sponsors logo band~~ → **INVENTO eliminado** (auditoria 2026-07-04: home Expo no tiene strip de sponsors)
- [x] **GamificationHud preview LIVE** — HECHO 2026-07-08 (slide del carrusel Home LIVE, borde RGB girando, card entera deeplink /desafio, tamano fluido cqw, datos SSR)
- [x] ~~Anuncios mini con count badge~~ → **CUBIERTO por Bell sidebar W.14** (divergencia intencional ya decidida: Expo badge en campana del header = webapp badge en BellPopover)
- [x] **Post-event survey prompt** ENDED — HECHO 2026-07-08 (prompt dentro de EventArchive → ruta /encuestas SurveyDeck por slides)
- [x] **EventArchive** ENDED — HECHO 2026-07-08 (espejo Expo puro: banner "Evento finalizado" + 3 stats + prompt encuesta + 4 links archivo)
- [x] ~~Multi-sede pill~~ → **INVENTO eliminado** (backend solo tiene UN venue string)
- [x] ~~Foto real speaker~~ — **HECHO 2026-07-04** (`RoomAvatar` con `speaker_photo_url` + gradiente fallback, espejo Expo)
- [x] ~~Proximos eventos org en ENDED~~ → **INVENTO eliminado** (Expo no lo tiene)
- [x] ~~Atmosfera dinamica por estado~~ → **INVENTO eliminado** (nice-to-have sin espejo; la cinematic ya existe)
- [x] ~~useHappeningNow dedupe~~ → **INVENTO eliminado** (Expo no dedupea; filtra por estado y corta a 2)

### W.3 — Agenda (25/25, **CERRADO 100% 2026-07-04** — badges/conflict/check-in eran inventos; URL state → Fase 2)

- [x] Tipos + fetcher SSR
- [x] Lista sesiones agrupada por dia
- [x] Timeline visual
- [x] Heart favorito optimistic
- [x] Tabs Agenda / Mi Agenda
- [x] Chips tracks filtrables
- [x] Search debounce
- [x] Favoritos toggle wired
- [x] DetailPanel 320-520px slide-in 480ms
- [x] Header detail con badges (EN VIVO, tipo session, track)
- [x] Time + location + capacity card
- [x] Action buttons (Favorita, Calendario, Evaluar)
- [x] Description + speakers grid
- [x] Speakers clickables → speaker detail
- [x] Rating modal 5-star + comment 280 chars
- [x] my-ratings backend wired
- [x] .ics download individual
- [x] Add to calendar variants (native/browser)
- [x] Highlight pueden expandirse desde home (param highlightSessionId)
- [x] Colores por tipo session
- [x] AttendeesPop modal (centrado blur lista)
- [x] Stream/Recording CTA
- [x] Session detail page individual
- [x] Tests Vitest 55
- [x] ~~Lifecycle badges AJUSTADA/CANCELADA~~ → **INVENTO eliminado** (auditoria 2026-07-04: el API de agenda NO expone los 3 tiempos como campos y la agenda Expo NO pinta badges. El espejo real es el toast `agenda:delayed` — YA implementado en W.11)
- [x] ~~Conflict detector visual~~ → **INVENTO eliminado** (cero rastro en Expo y backend)
- [x] ~~Room check-in boton~~ → **INVENTO eliminado** (el asistente nunca se auto-checkea: presenta su QR y el staff escanea via `/staff-checkin/*`. El "Mi QR" mobile vive en Mobile parity)
- [x] ~~**Bulk .ics download**~~ (todas mis favoritas un solo archivo) — hecho 2026-06-21 (Sprint 1 item 8): `lib/ics.ts` generador puro RFC 5545 (VCALENDAR + N VEVENT con UID determinista, DTSTAMP UTC, escape comas/semicolons/backslash). Boton "Todas" del AgendaHeader visible cuando `countMine > 0` en CUALQUIER dia. Filename `mi-agenda-{event.slug}.ics`. +16 tests vitest
- [x] ~~Recordatorio push 10min antes~~ → **movido a W.12 Web Push** (vive con la infra push, no es item de agenda)
- [x] ~~**RT socket invalidation**~~ — CUBIERTO por W.11 (verificado 2026-07-04: `data:invalidate{agenda}` → router.refresh + prop-sync `AgendaView:62`. Alias `sessions` agregado en Bloque 0 `3e73b29`)
- [x] ~~URL state shareable~~ → **reclasificado Fase 2** (concepto webapp-only sin espejo, nice-to-have)
- [x] ~~**Playwright E2E happy path**~~ — YA EXISTIA (verificado 2026-07-04: `agenda.spec.ts` 300 lineas, 16 tests: days strip, tabs, filtros, search, DetailPanel, favoritos 200/500, rating, live, highlight x3, Esc)

### W.4 — Streaming (92/92 — CERRADO 2026-07-04 noche, recount contra codigo)

> Recount en QA vivo: la mayoria de items abiertos YA estaban implementados en
> StreamShell.tsx + hooks y el doc no se habia actualizado. Replay verificado
> en vivo. Items hechos hoy confirmados:
> anuncios in-stream (useAnnouncementOverlay + chat:pinned banner) · custom
> panel iframe (StreamShell:316) · replay (boton agenda → player, paneles off) ·
> rating auto con gate ratingsLoaded (fix race e1b0c9a) · mobile + tablet
> layouts (StreamShellMobile/Tablet) · floating emojis 5 paths · slow mode
> threaded a 3 variantes · CSP Vimeo via frame-src https (W.13).
> Reclasificados fuera: Trivia panel → W.16 (skip webapp) · Playwright
> cross-tab con socket real → W.12 · 17 items menores sin detalle → QA W.12.
> El detalle historico de items [x] previos quedo en git (commit anterior).

### W.5 — Speakers (35/35, **CERRADO 100% 2026-07-04**)

> Implementado en commit `134bf6e` (2026-05-09). Cerrado 100% real el 2026-07-04 al reclasificar los 3 items de QA operacional (Lighthouse Perf + Lighthouse Acc + device fisico) a W.12 Polish + PWA, que es donde vive el QA cross-modulos pre-deploy.
> Espejo Expo: sin tracks/featured/keynote flags DB (todo derivado), click sesion → /agenda?highlight=X

**Fase 0 — Hooks (3/3)**
- [x] `fetchSpeakers(eventId)` lista (lib/speakers.ts)
- [x] `fetchMySpeakerRatings(eventId)` (lib/speakers.ts)
- [x] `rateSpeakerRequest` + `fetchMySpeakerRatingsClient` (lib/speakersClient.ts)

**Fase 1 — Featured derivation (3/3)**
- [x] Algoritmo `getFeatured()` (speakersDerive.ts) — keynotes o top sessions, no flag DB
- [x] Fallback top by session_count si no hay keynote
- [x] BreathingCarousel auto-rotate + flechas al hover

**Fase 2 — Lista (4/4)**
- [x] Lista alfabetica via `sortAlphabetical()`
- [x] SpeakerListItem (photo + nombre + rol + session count badge)
- [x] Search debounce 400ms
- [x] Empty state (2 variantes: no-speakers + no-results)

**Fase 3 — DetailPanel (7/7)**
- [x] SpeakerDetailPanel slide-in con race protection
- [x] Hero foto + nombre + job + company + bio
- [x] Sesiones grid clickable
- [x] Click sesion → `/agenda?highlight={id}` con router.push (sin race vs URL sync)
- [x] LinkedIn button condicional (test E2E lo verifica)

**Fase 4 — Rating (4/4)**
- [x] SpeakersRatingModal con stars + comment 280 chars
- [x] UNIQUE 409 → silencioso + re-hidrata my-ratings (sin error toast)
- [x] Estado "Calificar" vs "Evaluado" + boton disabled si ya calificado
- [x] Optimistic update + revert en fallo real

**Fase 5 — Deep link + viewport (4/4)**
- [x] Deep link `?id=X` auto-open via useState initializer (R19 set-state-in-render)
- [x] URL sync sin recargar (router.replace scroll:false)
- [x] SSR fetcher (page.tsx hace `Promise.all([speakers, myRatings])`)
- [x] ~~Validar 3 viewports en device real~~ → **reclasificado a W.12 Fase 0** (QA cross-modulos pre-deploy)

**Fase 6 — Tests (3/3)**
- [x] Vitest `tests/components/speakers/speakersDerive.test.ts`
- [x] Vitest `tests/lib/speakersClient.test.ts`
- [x] Playwright `e2e/speakers.spec.ts` (13 escenarios: auth gate, search, panel, stars, LinkedIn condicional, ya calificado, modal focus, optimistic, 409 silencioso, 500 revert, click sesion, deep link, Esc layer order)

**Fase 7 — Cierre (7/7)**
- [x] ~~Lighthouse Performance >=85~~ → **reclasificado a W.12 Fase 3** (batch cross-modulos)
- [x] ~~Lighthouse Accessibility >=95~~ → **reclasificado a W.12 Fase 2** (WCAG AA global)
- [x] Tests verdes (391/391 vitest verde 2026-07-04)
- [x] Detalle commit DaVinci (commit `134bf6e` describe el modulo)
- [x] Memoria actualizada (`project_w5_speakers_v2.md` cierre formal agregado 2026-06-20)
- [x] PARITY-MATRIX seccion W.5 actualizada
- [x] ~~Validar device real~~ → **reclasificado a W.12 Fase 0** (QA responsive cross-modulos)

### W.6 — Social Wall (28/28, **CERRADO 100% 2026-07-05** — Momentos + Memorias + Contest hechos (BLOQUE 1); Hashtags eliminado del scope; denominador re-baseado 40→28)

> Recount 2026-06-20: el feed editorial implementado en `/social` (compartido con W.8 Networking) es W.6 Wall. Doc anterior listaba 0% por error de auditoria. Lo IMPLEMENTADO marcado [x] aqui.

**Fase 0 — Hooks (3/3)**
- [x] `fetchWallFeed` SSR (lib/social.ts) — backend usa `?page=` (paginacion pendiente UI)
- [x] ~~`usePostComments` lazy~~ — ES LAZY REAL (verificado 2026-07-04: `InlineComments` solo monta al expandir + fetch en useEffect `InlineComments.tsx:50-69`; feed inicial solo trae `comments_count`)
- [x] `createWallPost` mutation con foto opcional + manejo `pending` (post en moderacion)

**Fase 1 — Feed (4/4)**
- [x] PostCard render
- [x] InlineComments expandible
- [x] ~~Paginacion~~ — **HECHO 2026-07-04** (`7afb5d0`): infinite scroll cursor-based (fetchWallFeed corregido a `?cursor=`, proxy `/api/social/wall`, `appendFeedPage` dedup, sentinel IntersectionObserver + shimmer). Verificado vivo con 99 posts (5 paginas). +5 vitest + E2E `wall-paged`
- [x] Empty hint en SidebarRight ("Conecta con asistentes desde Personas")

**Fase 2 — Like + Comments (5/5)**
- [x] Heart optimistic (`toggleLikeOptimistic` + `toggleWallLike`)
- [x] POST revert on fail (SocialClientError catch)
- [x] Sync likes_count con server (race condition manejada)
- [x] Click "X comentarios" expande sub-thread (estado `expandedComments`)
- [x] Input crear comentario inline (Composer + handleCommentAdded)

**Fase 3 — Crear post (4/4)**
- [x] Composer textarea max 500
- [x] **Imagen upload** preview antes enviar (File API en createWallPost)
- [x] Post optimistic aparece + lumina toast
- [x] ~~Listener `wall:post` deduplica propio via socket~~ — HECHO por W.11 (verificado 2026-07-04: dedup por server ID `SocialView.tsx:131-136` + `wall:comment` skip propio `:141-150`)

**Fase 4 — Stories "Momentos" (3/3) — CERRADA 2026-07-05 (`50a0f79`)**
> Espejo `MomentosRow.tsx` + `MomentosViewer.tsx` + `useStories` Expo, verificado linea a linea.
- [x] Barra Momentos (anillo accent=no visto / tenue=visto + boton "Tu momento"; visible en Feed y Memorias como el sticky header Expo)
- [x] Viewer card 9:16 centrada en el CanvasCard (auto-advance 5000ms, click zones 40/60, dots, timeAgo, visto al abrir en localStorage `eventos:social:story-seen:{eventId}`, sin salto entre autores; desktop: teclado+Esc+chevrons+click afuera)
- [x] Upload 9:16 (preview + center-crop canvas `image-crop.ts`) → `POST /events/{id}/stories` + toast espejo literal "Momento subido! Visible por 24h."

**Fase 5 — Memorias + Photo Contest (4/4) — CERRADA 2026-07-05 (`50a0f79`)**
- [x] Vista Memorias 5ta del sidebar: grid 3 col con oficial 2x2 badge OFICIAL + upload boton header (crop 1:1, toast "Foto enviada! En revision.")
- [x] `PhotoViewer` marco FIJO 16:9/9:16 por orientacion, foto contain (NUNCA recorta — decision QA), meta en flujo debajo, likes optimistic + haptic + pop; foto propia → toast informativo sin red (anti-gaming backend)
- [x] `ContestBanner` espejo: solo active/ended<24h (scheduled nunca se monta, espejo social.tsx:364), countdown vivo useNow "2h 05m"/"4:07", podio medallas 72/56, corona ganador
- [x] Orden por likes cuando contest activo — client-side, grid y viewer comparten indices

**Fase 6 — Hashtags (ELIMINADA del scope, decision Kamilo 2026-07-04)**
> Click-to-filter borrado del scope: no aporta en este contexto. El parser
> `renderHashtags()` tambien ELIMINADO del codigo (`f0bedaa`) — feature muerto =
> codigo muerto, y de paso se fue el `dangerouslySetInnerHTML` (superficie XSS
> innecesaria); el body volvio a texto plano React. La idea buena derivada —
> analitica de menciones/hashtags trending — pertenece a Event Pulse, anotada
> en memoria `project_webapp_ideas`.

**Fase 7 — Filtros (2/2)**
- [x] View switch Feed/Personas/Solicitudes/Mis posts (sidebar izq) — funcional pero NO son tabs sticky en feed
- [x] ~~**Tabs Recientes / Mas likes / Mis posts** explicitas en vista Feed~~ (hecho 2026-06-20: `FeedTab` type + `sortAndFilterFeed` helper en `socialDerive.ts`, FeedView con state local de tab, 3 empty states distintos por tab, reusa `.sn-rqx-tabs` CSS existente, 6 tests vitest agregados). URL state shareable queda pendiente (nice-to-have, no critico)

**Fase 8 — Tests (3/3)**
- [x] Vitest `tests/components/social/socialDerive.test.ts` (toggleLikeOptimistic + filterMyPosts)
- [x] Vitest `tests/components/social/AttendeeProfilePanel.test.tsx`
- [x] Playwright `e2e/social.spec.ts` (5 escenarios: SSR shell+feed, switch Personas, conectar optimistic, aceptar solicitud, Mis posts vacio)

### W.7 — Sponsors (23/23, **CERRADO 2026-06-21**)

> Sprint 2.A entero en una sesion DaVinci larga. Espejo LITERAL Expo a la izquierda (wall) + DetailPanel der vacio hasta click. Animaciones via framer-motion `layout` spring damping 28 stiffness 120. Lumina toasts top-center con colores neutrales rgba(80,200,120)/rgba(255,100,100) (no `var(--accent)`).

**Fase 0 — Hooks (3/3)**
- [x] useSponsors lista
- [x] useSponsorFavorite toggle
- [x] useSponsorContact submit

**Fase 1 — Brand Wall (5/5)**
- [x] Grid agrupado por tier (platinum 2c, gold 3c, silver/bronze/media 4c)
- [x] Shuffle animation con framer-motion `layout` spring (damping 28, stiffness 120) — equivalente Reanimated
- [x] Stagger reveal on mount
- [x] Search debounce 350ms por nombre + descripcion
- [x] CardPressable scale animation

**Fase 2 — Brand Profile (5/5)**
- [x] Hero logo XL + nombre + descripcion + tier badge (tier label SOLO en wall por jerarquia, no en detail panel — decision espejo Expo)
- [x] Tab Acerca (descripcion completa)
- [x] Tab Servicios (chips multiselect)
- [x] Tab Sesiones (cards time + type + title + location)
- [x] Tab Contactar (form)

**Fase 3 — Favorite (3/3)**
- [x] Heart toggle optimistic (framer-motion, no CSS keyframes)
- [x] Animation spring
- [x] Lista favoritos en Mi Hub

**Fase 4 — Contact + Tracking (2/2)**
- [x] Contact form servicios + textarea mensaje (chips + 409 ALREADY_CONTACTED handled)
- [x] Tracking view fire-and-forget on profile open

**Fase 5 — Trivia integration (3/3)**
- [x] Trivia modal auto-trigger on visitStand (espejo TriviaModal Expo)
- [x] Pregunta + 4 opciones (letras A/B/C/D) + countdown + result + boton "Responder/Siguiente/Ver resultado"
- [x] Auto-close 2.5s + pantalla resumen "+N puntos ganados" feedback

**Fase 6 — Tests (2/2)**
- [x] Vitest 14 (hooks + shuffle + contact form 409)
- [x] Playwright happy path — **12 E2E verde** + Lighthouse acc 98 + CLS 0

**Backend gaps cerrados durante W.7 (BUG-336, BUG-337)**
- [x] SponsorResource expone trivia/passport/visit_points (BUG-336)
- [x] GamificationController visitStand devuelve `points_awarded` distinguiendo idempotente (BUG-337) — patron a auditar en W.3/W.4/W.6/W.9

### W.8 — Networking (21/21, **CERRADO 100% 2026-07-04**)

> Lo "social" implementado en `/social` que NO es feed Wall (W.6) realmente vive aqui.
> Cerrado 100% al reclasificar 4 items: Filtro role (skip — backend publico no expone role), RT listeners (bloqueado W.11), Sugeridos cards grandes (skip — mini-rows funcionan), Tracking analytics (Fase 2). El resto se completo: Mi perfil editable → cubierto por W.18 con link desde sidebar (2026-07-04), E2E happy path ampliado con 5 tests nuevos, cierre commit + memoria.

- [x] Directorio paginado (`PersonasView`)
- [x] AttendeeCard horizontal
- [x] Suggested contacts mini-rows panel der
- [x] Perfil attendee in-slot (panel der MUTA, visionOS)
- [x] 4 estados relacion (none/sent/received/contact)
- [x] Compose inline mensaje opcional
- [x] vCard download + WhatsApp + Email
- [x] Solicitudes Recibidas/Enviadas tabs
- [x] Aceptar / Ignorar
- [x] Bloquear con confirm (hoy `window.confirm`)
- [x] Mi red (contactos aceptados)
- [x] socialClient.ts (fetchAttendeeProfileClient + sendContactRequest + respondContactRequest + blockAttendee)
- [x] API proxies social/*
- [x] Tests vitest 49 (vcard + socialClient + AttendeeProfilePanel)
- [x] Bug handleRespond requestId invertido corregido
- [x] ~~**Reemplazar window.confirm Bloquear por AlertDialog DaVinci**~~ (hecho 2026-06-20: rechazada v1 shadcn AlertDialog generica; v2 final con `ui/confirm-pop.tsx` reusable + CSS global, patron espejo `rating-pop`, Plus Jakarta 700 20px + Urbanist 14px, copy honesto sin promesa de "tu perfil", tests 27/27 verde)
- [x] ~~**Skeleton mejor AttendeeProfilePanel**~~ (hecho 2026-06-20 v1: 3 secciones estructuradas. v2 simplificado mismo dia por feedback usuario "no coincide con lo que hay" — secciones reales Sobre/Intereses/Asistira a son condicionales y pueden no aparecer para muchos attendees → over-promesa visual. Skeleton final es honesto: 1 titulo chico + 3 lineas tipo bio, sin chips ni cards.)
- [x] ~~**Bloqueados list** (vista + boton desbloquear)~~ (hecho 2026-06-20: tercera tab "Bloqueados" en SolicitudesView, `BlockedRow` no clickeable + boton ghost Desbloquear, `fetchBlockedAttendees` SSR, optimistic + revert, 2 tests vitest agregados. Migrar a W.18 Settings cuando exista)
- [x] ~~**Mi perfil editable**~~ → **cubierto por W.18 Hub Personal** (`/perfil` con foto upload + shuffle beam + edicion completa). SidebarLeft de `/social` ahora tiene link clickeable al perfil (loop cerrado 2026-07-04)
- [x] ~~**Filtro role dropdown**~~ → **skip formal** (2026-07-04): backend publico `/events/{id}/attendees` NO expone `role` (privacy/compliance). Agregar seria inventar data
- [x] ~~**RT listeners** `networking:notify`~~ → **reclasificado a W.11 Sockets RT** (bloqueado hasta que W.11 tenga el layer socket completo)
- [x] ~~**Gap C Sugeridos cards grandes**~~ → **reclasificado Fase 2**: mini-rows en sidebar der ya funcionan; refactor de layout es cosmetic no urgente
- [x] ~~**Playwright E2E abrir perfil → conectar → confirmar solicitud**~~ (hecho 2026-07-04: `e2e/social.spec.ts` ampliado con 5 tests nuevos — click sidebar identity → /perfil, filtro Sin contactar, abrir perfil → panel + CTA Conectar, rechazar solicitud, tab Bloqueados + Desbloquear. 11/11 verde con serial mode)
- [x] ~~**Tracking analytics**~~ → **reclasificado Fase 2**: `social.profile_opened + connection_sent + contact_method_clicked` viven con la infra de analytics global (no existe aun en webapp)
- [x] ~~**Cierre commit + memoria + counter PARITY-MATRIX**~~ (hecho 2026-07-04)

### W.9 — Engagement (leaderboard + logros + passport + rewards + golden tickets) — **CERRADO 35/35 (100%) 2026-06-29**

> Arquitectura final 2026-06-29: hub split layout literal espejo W.7. Wall izq apila 6 cards (Hero/Tickets/Premios/Tip/Retos/Pasaporte), panel der detalle del seleccionado. Shapes adaptados al backend real (Expo intacto). Encuestas viven en W.4 Streaming (in-stream context), no se replican aqui. Toast "+X puntos via diff" DESCARTADO (espejo Expo no lo hace).

**Fase 0 — Hooks / fetchers (3/4)**
- [x] `useMyPoints` equivalente — via `fetchDesafioOverview` SSR (overview agrega `/me/points`)
- [x] `useLeaderboard` equivalente — via `fetchRankingClient` (lazy al abrir panel) + top3 en overview
- [x] Lazy fetchers full data — `fetchRankingClient` / `fetchRewardsClient` / `fetchPassportClient` / `redeemRewardClient` via 4 proxies Next `/api/desafio/[eventId]/{leaderboard|rewards|passport|redeem/[rewardId]}`
- [~N/A] `useGamificationConfig` — `actions[]` ya viene embebido en `/me/points` response, no requiere hook separado

**Fase 1 — Encuestas / Surveys (N/A — viven en W.4 Streaming)**
- [~N/A] Encuestas in-stream son del modulo W.4 (`poll:new`/`poll:vote`/`poll:closed` sockets). NO duplicar en W.9 hub.

**Fase 2 — Leaderboard (3/4)**
- [x] LeaderboardTable top 50 — `RankingPanel` con podio escalado #2 #1 #3 + lista top 50
- [x] Sticky bar `my_position + my_points` — HeroCard del wall (siempre visible)
- [x] my_position > 50 — backend devuelve `my_position` separado del top 50, panel muestra siempre "Tu posicion #N"
- [ ] Share rank social — fuera de scope sesion 2, va a backlog futuro

**Fase 3 — Mis Logros / Retos (3/3)**
- [x] Grid actions completed (`RetosCard` wall + `RetosPanel` lista completa)
- [x] Visual estados completed vs pending (`.dx-reto-row.done` / `.pending`)
- [x] Detalle inline (label + puntos + iconos por accion) — no requiere modal separado

**Fase 4 — Passport stamps (3/4)**
- [x] `PasaporteCard` grid 6 + `PasaportePanel` grid completo
- [x] Cada stamp: logo sponsor + nombre + tier + stamped_at
- [x] Solo VIEW (earning via QR fisico mobile, correcto)
- [x] Socket `data:invalidate{entity:passport}` → refresh silencioso — HECHO via W.11 (GlobalSocketProvider mapea `passport` en KNOWN_ENTITIES → router.refresh, sin animacion ni toast, espejo Expo)

**Fase 5 — Rewards (6/6)**
- [x] `RewardsPreviewCard` wall + `RewardsPanel` catalogo completo grid
- [x] Reward card icon + nombre + costo + stock + sponsor
- [x] Redeem optimistic — `RedeemModal` con 2 estados (loading skeleton shimmer → ready con QR real + countdown 5min)
- [x] Display token + countdown + hint "Muestra al vendedor"
- [x] Redemptions INLINE en cada card (espejo Expo) — 5 estados: Mostrar QR TEAL / Ya canjeado disabled+check / Agotado / Canjeando… / Canjear o Faltan X. Reusa token existente sin pegar otra vez al POST `/redeem`. Decision arquitectural: tab "Mis canjes" descartado (Expo NO lo tiene, las redemptions viven en el catalogo)
- [x] Bloque "Canjes activos sin catalogo" — si reward fue retirado pero hay redemption pending vigente, el usuario sigue viendo su QR

**Fase 6 — Golden Ticket reveal (2/2)**
- [x] `GoldenTicketPanel` (panel der): trophy XL + overline "Ganador" + nombre + sponsor + claim_code XL gold + QR grande con RGB rect + hint + countdown si expira
- [x] Estado pending vs claimed — wall card muestra TODOS los tickets (pending boton individual, claimed info estatica), click pending → reveal en panel der mostrando UN solo ticket (sin lista repetida ni modal — espejo desktop informativo)

**Fase 7 — Toast +X puntos diff (N/A descartado)**
- [~N/A] `useTrackPointsDiff` + toast "+X pts" — Expo NO lo hace (visit_stand + trivia answer + acciones suben puntos silenciosamente, usuario descubre al volver al HUD). Memoria `feedback_no_points_diff_toast.md` documenta decision. Webapp = espejo Expo en comportamiento.

**Fase 8 — Tests (2/3) ✓**
- [x] Vitest helpers puros — `desafioDerive.test.ts` (11 tests) + `desafioNormalize.test.ts` (22 tests: 14 shape gaps + 4 redemption normalizer + 4 active/confirmed/orphan helpers). 270/270 vitest verde
- [~N/A] Vitest diff calc — N/A (no implementamos points diff)
- [x] Playwright `desafio.spec.ts` 8/8 verde — auth gate / SSR hub / click ticket pending → reveal / 5 estados CTA inline / Mostrar QR sin POST / Canjear con POST / Esc cierra / bloque orphans. Fixture + 7 handlers mockBackend. Serial mode (evita saturar dev con 8 workers paralelos, 13s vs 30s timeout)

**Fase 9 — Cierre formal (4/4) ✓**
- [x] Validar 3 viewports — E2E automatizado verifica desktop 1600x900 + tablet H 1130x800 + mobile 390x844 (sin overflow horizontal, hub renderiza, panel abre, modal sin overflow). Validacion visual UX se queda al batch QA final cross-modulos (W.5/W.7/W.10/W.9)
- [ ] Lighthouse autenticado — **batch QA final cross-modulos** (no bloqueante para cierre formal, idem W.5/W.7/W.10)
- [x] Memoria — `project_w9_engagement_webapp.md` actualizada con arquitectura final + nuevas memorias (`feedback_no_repetir_info_en_panel.md`, `feedback_no_modal_desktop.md`, `feedback_no_points_diff_toast.md`)
- [x] Counter PARITY-MATRIX sincronizado W.9 0/35→35/35 + W.7 0/23→23/23 + totales (modulos cerrados 2→5, vitest 194 fail→270 verde, E2E 9→11 specs)

**Decisiones arquitecturales W.9 (no preguntar de nuevo):**
- Colores TEAL/GOLD/CYAN fijos, NO `var(--accent)` del cliente (gamification = sistema, no marca cliente). Ver `feedback_no_accent_in_gamification.md`.
- Webapp = espejo Expo en comportamiento. Toast "+X pts via diff" descartado, `claimTicket` attendee-side descartado (vendedor confirma).
- En desktop, panel der NUNCA repite info ya visible en wall. Cada wall card lista sus items, click en item especifico → detalle del item en panel der (no lista repetida, no modal).
- Modal solo cuando NO hay espacio en panel (caso unico: `RedeemModal` post-canje, porque el QR temporal vive fuera del panel — flujo separado del catalogo).
- **Tab "Mis canjes" DESCARTADO** — Expo NO tiene tab separada. Las redemptions viven INLINE en el catalogo: cada card chequea su redemption activa via `findActiveRedemption(rewardId, redemptions)` y cambia el CTA (Mostrar QR / Ya canjeado / Agotado / etc). Si reward fue retirado del catalogo pero hay redemption pending vigente, el bloque "Canjes activos sin catalogo" arriba del grid garantiza acceso al QR.

### W.10 — Live Hub (16/16, **CERRADO por consenso 2026-06-20**)

> Creado en commit `0e185e6` (2026-05-10). Reusa el numero "W.10" que originalmente era Hub Personal. Conflicto resuelto 2026-06-20: Live Hub se queda con W.10 (mas reciente, en commits + tests), Hub Personal renombrado a W.18.

- [x] SSR `fetchHappeningNow` + `fetchUpNext` (lib/live.ts + lib/happeningNow.ts)
- [x] Sidebar pill `/live` activo (ya no disabled)
- [x] LiveHubView root + LiveHero + LiveSideCard + UpcomingCard
- [x] 4 estados visuales: default 2+N, 1 live solo, 0 lives + N upcoming, 0+0 empty
- [x] Click hero/side con has_stream → /session-stream/{id}; sin stream → /agenda?highlight
- [x] Click upcoming → /agenda?highlight={id}
- [x] Header pill "X EN VIVO" indicador
- [x] Tokens Slate Mono globales (--slate, --slate-light, --slate-dark, --slate-deep) en globals.css
- [x] Single radial-gradient elliptical disuelto (sin spots concentrados)
- [x] Lux overrides completos (cards crema + slate-dark sobre claros)
- [x] Vitest `tests/lib/live.test.ts`
- [x] Playwright `e2e/live.spec.ts` (8 escenarios: auth gate, SSR default, upcoming countdown+room+speaker, badge Tu agenda, solo, por arrancar, empty state, navegacion 3 tipos)
- [x] Validacion visual con `LiveHubDemoSeeder` (3 lives + 6 upcoming + 4 past) — funciona OK 2026-06-20
- [x] Counter PARITY-MATRIX + PENDIENTES-WEBAPP actualizado
- [x] **Skip vitest componente LiveHubView** — E2E + JSDoc + visual cubren (anti-overengineering)
- [x] **Skip doc maestro `W.10-live-hub.md`** — anti-regadero, info vive en commit `0e185e6` + JSDoc + esta seccion

### W.11 — Sockets RT (22/22 — CERRADO 2026-07-04 noche)

> Implementado segun `docs/W.11-SOCKETS-PLAN.md` (investigacion + auditorias Fable 5).
> Verificacion viva: pipeline Laravel→socket→cliente confirmado (tinker broadcast →
> cliente recibio `data:invalidate entity=announcements`; contrato roto reprodujo
> `EVENT_ACCESS_DENIED`). 402 vitest + suite E2E.

- [x] socket.io-client instalado
- [x] Auth bearer via socket-token endpoint
- [x] Singleton usado en W.4 Streaming (chat + Q&A + polls)
- [x] Dedup tempId chat propio
- [x] Listeners session:* live indicator
- [x] Pinned message socket-driven
- [x] Optimistic + revert chat
- [x] Connection status pill
- [x] **GlobalSocketProvider** en `(app)/layout.tsx` — singleton global (antes solo streaming)
- [x] **Connection management** — reconnection Infinity, backoff 1s→5s cap (espejo Expo)
- [x] **Reconnect on visibilitychange** (tab dormida re-conecta al volver)
- [x] **Listener wall:post** — bus cliente → SocialView prepend dedup por server ID
- [x] **Listener wall:comment** — +1 count (skip propio, sin double-count Expo) + refetch InlineComments
- [x] **Listener networking:notify** — batch 1500ms espejo Expo + toasts lumina + refetch requests via proxy nuevo `/api/social/requests`
- [x] **Anuncios RT** via `data:invalidate{announcements}` — bell + lista refrescan
- [x] **Listener data:invalidate** generico — 10 entities, debounce 800ms → router.refresh + prop-sync AgendaView/SponsorsView/SoporteView (vistas sordas arregladas)
- [x] **Listener ban:enforced** — logout + toast + redirect /login
- [x] **Listener agenda:delayed** — toast espejo Expo literal
- [x] **Long-polling fallback** — transports websocket+polling en singleton
- [x] **Skip-self** — dedup server-id (wall:post) + author check (wall:comment)
- [x] **Tests Vitest** — 11 nuevos (402/402 total verde)
- [x] **E2E degradacion** — 2 specs (provider monta + 401 no rompe) + regresion suite completa
- Reclasificados fuera del modulo: game events → W.16 (skip webapp) · staff listeners → W.15 · token refresh durante conexion → W.12 (deuda D.5 del plan) · Playwright RT cross-tab con socket real + stress 10K → W.12 · counter PARITY → N/A (doc historico)

### W.12 — Polish + E2E + PWA (25/48 — Fases A+B Bloque 5 cerradas 2026-07-05, falta Fase C QA)

**Fase 0 — Audit responsive (0/4)**
- [ ] 3 viewports en device real (laptop / iPad / iPhone)
- [ ] Validar tablet portrait warning overlay
- [ ] Validar Edge corporativo
- [ ] Validar Firefox 115+

**Fase 1 — Skeletons + empty (0/3)**
- [ ] Skeleton consistente todos los modulos
- [ ] Empty states consistentes
- [ ] Loading transitions

**Fase 2 — Accesibilidad (1/5)**
- [ ] WCAG AA contraste 4.5:1
- [x] ~~Focus visible :focus-visible outline accent~~ — YA EXISTE (verificado 2026-07-04: regla global `globals.css:493` con color-mix + offset 2px)
- [ ] Keyboard nav completa
- [ ] Tab order logico
- [ ] ARIA labels iconos sin texto

**Fase 3 — Performance (2/8)**
- [ ] Bundle <200KB gzipped
- [x] Code splitting por modulo — **HECHO 2026-07-05** (`471cf94`): dynamic() ssr:false en 7 componentes post-interaccion; FileKindIcon extraido para split real de documentos
- [x] ~~Lazy @dnd-kit + framer-motion~~ — @dnd-kit NO se usa (verificado); lazy framer global descartado (33 archivos estaticos, riesgo > ganancia; qrcode.react SI salio del bundle /desafio via splitting)
- [ ] next/image sizes correcto
- [ ] Lighthouse Performance >=85 desktop (Fase C)
- [ ] Lighthouse Performance >=75 mobile (Fase C)
- [ ] TTI <3s 4G Bogota (Fase C)
- [ ] Migrar SSR → TanStack Query infinite cache (post-W.11)

**Fase 4 — SEO (3/3 — recortado con higiene, decision Kamilo 2026-07-05)**
- [x] Meta tags por pagina — **HECHO** (`471cf94`): title template `%s — EventOS` + generateMetadata en 13 rutas via lib/pageMetadata (nav.* i18n)
- [x] ~~OG images~~ → **Fase 2 formal** (app auth-gated, nadie comparte rutas privadas)
- [x] ~~sitemap.xml~~ → **Fase 2 formal**; en su lugar robots.ts noindex TOTAL (antes las rutas privadas de eventos eran indexables)

**Fase 5 — PWA (5/5 — CERRADA 2026-07-05, `2dc43a3`)**
- [x] Manifest — src/app/manifest.ts + iconos anillo dorado #B5A68B sobre noir (sin tipografia, generados con Playwright)
- [x] Service Worker — public/sw.js (push + click routing + offline; cache eventos-sw-v1)
- [x] Install prompt condicional desktop/tablet — useInstallPrompt (beforeinstallprompt) + entry "Instalar aplicacion" en /perfil footer
- [x] Install prompt NO en mobile — gate matchMedia >=1024px (no canibalizar app nativa)
- [x] Offline fallback page — public/offline.html estatico Lumina Noir (sin red, sin middleware, cero superficie XSS)

**Fase 6 — Print (2/2 — CERRADA 2026-07-05, `471cf94`)**
- [x] Stylesheet print friendly — globals (canvas liberado, pills/ambient print:hidden) + agenda.css @media print
- [x] Imprimir agenda — documento real: papel blanco/tinta negra, sin chrome, sesiones break-inside avoid, verificado visual 2 iteraciones (ratings viven en el detail panel = fuera del papel a proposito)

**Fase 7 — E2E (2/4)**
- [x] ~~Smoke test critical paths~~ — YA EXISTE (verificado 2026-07-04: **20 specs** en e2e/ cubriendo todos los modulos: auth-gate, login, verify, home, agenda 16 tests, streaming, speakers, social, sponsors, desafio, live, global-socket, faq, documentos, cartel, anuncios, soporte, perfil)
- [x] ~~Login + home + agenda~~ — YA EXISTE (auth-gate.spec + login-form.spec + home.spec + agenda.spec)
- [ ] Streaming + Q&A cross-tab (requiere socket server real en CI)
- [ ] Social conectar cross-tab

**Fase 8 — Sentry validation (1/2)**
- [ ] DSN prod
- [x] ~~Source maps subidos en build (no en cliente)~~ — YA CONFIGURADO (verificado 2026-07-04: `withSentryConfig` con `deleteSourcemapsAfterUpload: true` + tunnelRoute /monitoring + release via GIT_SHA. Sentry client/server/edge completo con PII scrub)

**Fase 9 — Cierre (3/7)**
- [x] CSP estricto — **HECHO 2026-07-05** (`471cf94`): 13 directivas (script/style/img/font/connect/media/frame/worker/object/base-uri/form-action/frame-ancestors), connect-src backend+socket desde env, dev relajado por NODE_ENV, verificado vivo + suite E2E bajo la politica
- [x] ~~X-Frame-Options~~ — YA EXISTE (verificado 2026-07-04: SAMEORIGIN + nosniff + Referrer-Policy + HSTS en `next.config.ts:76-84`)
- [x] ~~Reduced motion verificado~~ — YA EXISTE (verificado 2026-07-04: media query global `globals.css:470` + hook `useReducedMotionPref` + ~18 componentes/CSS)
- [ ] reduced-motion serie estatica W.X
- [ ] ~~Bancolombia embed test~~ → N/A (cliente perdido — validar si algun embed test aplica al proximo cliente)
- [ ] Memoria
- [ ] Counter PARITY-MATRIX → N/A (doc historico desde 2026-07-04)

**Fase 10 — Web Push (5/5 — CERRADA 2026-07-05, `b9aa4df` backend + `2dc43a3` web, verificado VIVO)**
- [x] Backend: minishlink/web-push + VAPID keys + tabla push_subscriptions (endpoint hash unico, multi-device) + WebPushSubscriptionController (GET key + POST/DELETE) espejo expo-token
- [x] Transporte multi-canal: `SendPushToAttendeeJob::toAttendee()` choke point (Expo + web), 13 call-sites migrados, filtros de jobs masivos incluyen web-only, subscription por valor (simetria token Expo, prune 410). Recordatorios de sesion (SendAgendaRemindersJob) y TODOS los triggers incluidos. Fix bug pre-existente: ban Filament limpiaba token ANTES de la push
- [x] **Scheduled → Announcement persistente** (decision Kamilo: "la push es el golpe en la puerta, el announcement es la carta") — notificaciones programadas ya no se pierden si nadie las vio; Bell enciende live via AnnouncementObserver
- [x] SW cliente: push handler con supresion si pestana enfocada (Bell+socket cubren in-app), notificationclick con PUSH_ROUTES espejo LITERAL Expo useNotifications.ts, track push_open, pushsubscriptionchange re-subscribe
- [x] Soft prompt pill una-vez-por-evento (divergencia web aprobada vs auto-prompt Expo: Chrome penaliza el prompt automatico y un bloqueo es permanente) + resync silencioso con permiso granted. 15 Pest + 14 vitest + 3 E2E
> Validado por Kamilo mismo dia: push desde Filament post-reinicio Laragon (Apache + OPENSSL_CONF OK) + install PWA real OK. Todo pusheado.

### W.13 — FAQ + Documentos (15/15, **CERRADO 100% 2026-07-04**)

> Fase A FAQ entregada 2026-06-29 nocturna (Asistente orb Siri-style + split layout). Fase B Documents entregada 2026-06-30 tarde (/documentos con arquitectura ZIP escalable). Fase C Pages reclasificada formalmente a Fase 2 (decision usuario 2026-06-29).

**Fase 0 — Hooks (2/2)**
- [x] useFaqs (`lib/faq.ts` SSR fetcher)
- [x] useDocuments (`lib/documents.ts` SSR fetcher)
- [x] ~~usePages~~ → **reclasificado Fase 2 (Pages skip)**

**Fase 1 — FAQ (4/4)**
- [x] Accordion + orb Siri-style (browsing/thinking/answering) espejo Expo
- [x] Search debounce 300ms + chips categoria
- [x] Wired a `/soporte?new=true` (CTAs siempre visibles en panel der)
- [x] OrbBlob.tsx CSS puro 4 radial gradients + Lux support

**Fase 2 — Documentos (7/7)**
- [x] Split layout wall + preview panel der
- [x] Icono lucide MIME (FileText/FileImage/FileVideo/FileAudio/File — no emojis)
- [x] Preview embed segun kind (PDF iframe / imagen / video / audio / fallback metadata)
- [x] Skeleton shimmer + fade-in 220ms + timeout fallback 6s
- [x] Descarga individual `<a download>` con `suggestedFilename`
- [x] **Bulk ZIP pre-generado backend escalable a 10K users** (observer + job + endpoint + composer maennchen/zipstream-php)
- [x] URL state `?id=X` + Esc + CSP frame-src

**Fase 3 — Pages (reclasificado)**
- [x] ~~Pages dinamicas iframe/HTML~~ → **reclasificado Fase 2** (usuario 2026-06-29). Modulo webapp usa `/documentos` para archivos y `/faq` para preguntas — Pages dinamicas de organizador es low priority

**Fase 4 — Tests + Cierre (2/2)**
- [x] Playwright — `e2e/faq.spec.ts` + `e2e/documentos.spec.ts` con happy paths completos
- [x] Counter PARITY-MATRIX + memoria (`project_w13_documents.md`)

### W.14 — Anuncios + Cartel Digital + Bell (17/17, **CERRADO 100% 2026-07-04**)

> Fase A Anuncios + BellPopover entregada 2026-06-29 (sprint 2.C). Fase B Cartel Digital entregada 2026-06-30 (ambient signage col der LIVE). Cerrado 100% al reclasificar 3 items bloqueados por otros modulos: RT anuncios (`data:invalidate{announcements}` — `announcement:new` no se emite) → W.11 Sockets, Web Push real → W.12 Polish, dots/counter → decision de diseno (cartelera NO slideshow, sin dots).

**Fase 0 — Hooks (3/3)**
- [x] `fetchAnnouncements` SSR (lib/announcements.ts)
- [x] `fetchBanners` + `fetchHighlights` SSR (Fase B)
- [x] `parseActionUrl` helper 13 mappings eventos:// → rutas webapp (23 vitest)

**Fase 1 — AnnouncementsList (4/4)**
- [x] Lista cards titulo + body + timeAgo (`/anuncios` route)
- [x] Image thumbnail si existe
- [x] Deep link handler `parseActionUrl` — reusado por bell, cartel, cards
- [x] ~~Socket anuncios RT~~ → **reclasificado a W.11 Sockets** (mecanismo real: `data:invalidate{announcements}` — `announcement:new` es dead type, auditoria 2026-07-04)

**Fase 2 — Cartel Digital (6/6) — Fase B 2026-06-30**
- [x] Ambient signage 16:9 col der LIVE state (cross-fade 700ms cada 6s)
- [x] Sin dots, sin flechas — cartelera ≠ slideshow
- [x] Sponsor pill top-left si `sponsor_name`, titulo overlay bottom-left
- [x] Hover/focus pausa el ciclo + `prefers-reduced-motion` support
- [x] Empty → zona colapsa, feed salas ocupa 100%
- [x] Merger round-robin banners+highlights + backend cero cambios

**Fase 3 — BellPopover (3/3)**
- [x] BellPopover radix con badge count unread (per-item read tracking)
- [x] localStorage `lastSeenAt:{eventId}` scoped tracking + cross-tab sync
- [x] Click card → mark seen + nav `/anuncios?id=X`

**Fase 4 — Web Push (reclasificado)**
- [x] ~~Web Push real (8 tipos backend documented)~~ → **reclasificado a W.12 Polish** (infra push cross-modulos)

**Fase 5 — Tests + Cierre (1/1)**
- [x] Vitest 23 (parseActionUrl) + 16 (announcementsUnread) + 11 (cartelItems merger) + 12 (CartelDigital) + 10 E2E anuncios + 6 E2E cartel

### W.15 — Vendor Dashboard (0/35, OPCIONAL Fase 1)

> Solo si cliente lo pide. Backlog por default.

**Fase 0 — Hooks (0/4)**
- [ ] useMyStand
- [ ] useMyLeads
- [ ] useStandStats
- [ ] usePendingInvitations

**Fase 1 — Mi Stand dashboard (0/4)**
- [ ] Hero sponsor + logo + descripcion + tier + role badge
- [ ] Stats row clickables (leads / hoy / equipo)
- [ ] Empty state guideline
- [ ] Tabs Acerca / Leads / Equipo

**Fase 2 — Mis Leads (0/5)**
- [ ] Lista grouped por fecha (Hoy/Ayer/dd mmm)
- [ ] Tier badge + nota italic + timeAgo
- [ ] Detail drawer notas / tier editable
- [ ] Historial ediciones (field_label + old→new + edited_by + fecha)
- [ ] Export CSV

**Fase 3 — Visitantes stand (0/2)**
- [ ] Cards lead avatar + nombre + job + timestamp
- [ ] Acciones whatsapp / email / llamar

**Fase 4 — Stats (0/3)**
- [ ] StatRow pairs (totals + diff vs ayer trend)
- [ ] TierBar (hot/warm/cold/unclassified % stacked)
- [ ] MemberBar (each team member lead count)

**Fase 5 — Team management (0/5)**
- [ ] Slots indicator (usado/max)
- [ ] Invitar by attendee search
- [ ] Invitar by email
- [ ] Share link modal (whatsapp/copy)
- [ ] Transfer ownership / Remove member

**Fase 6 — Invitaciones staff (0/3) — CORREGIDO por auditoria: CON sesion, NO publicas**
> El backend exige sanctum para aceptar (`/staff-invitations/{token}/*` dentro del grupo auth).
> Espejo `join-team/[token].tsx` Expo (276 lineas). "Sin login" era invento.
- [ ] Pagina `/join-team/{token}` autenticada (info de la invitacion via `GET .../info`)
- [ ] Aceptar / No gracias (`POST .../accept|reject`) + actualizar hasVendorAccess + invalidar modules
- [ ] Modal invitacion socket `staff:invited` (espejo `StaffInvitationModal` 216 lineas)

**Fase 7 — Tests + Cierre (0/4)**
- [ ] Vitest hooks
- [ ] Playwright happy path
- [ ] Memoria
- [ ] Counter PARITY-MATRIX

### W.16 — Live Moments espejo real (5/5, CERRADO 2026-07-09)

> **CERRADO.** Trivia en vivo en la columna interactiva del streaming. Archivos eventos-web:
> `hooks/streaming/useTrivia.ts`, `lib/trivia.ts` (reducer puro + tipos + helpers),
> `components/app/streaming/TriviaPanel.tsx`, `streaming.css` (seccion `.tv-*`),
> `app/api/streaming/game-answer/[gameId]/route.ts`, wire en `StreamShell.tsx`, toasts en
> `useGlobalSocket.tsx`. Backend seeder `QaTriviaSeeder`. Validado en vivo via Mission
> Control (launch/next-question/close-round) + respuesta desde webapp (score 135 correcto).
> Decisiones Kamilo: noir puro (sin teal), unico color = letras A-F, cero iconos, estado por
> relleno no borde (no "neon"). Fix cascada: `.tv-opt > .tv-opt-dist` especificidad para que
> la barra no empuje la letra. `setState`-en-effect del countdown → derived-state R19 (lint).

> **Auditoria de procedencia:** el scope viejo (23 items) tenia inventos. Lo REAL en Expo:
> TriviaPanel completo (340 lineas, la referencia) + toasts de ruleta/jackpot
> (`useDataInvalidation:436-454`). NO existen en Expo: ceremonia GSAP de sorteo (solo
> toast), golden reveal announcement-driven (solo modal por tap en hub — webapp ya lo
> tiene en /desafio), `display:project` (evento inexistente). Photo contest se movio a
> W.6 Fase 5 (vive en el social, no en live moments). Ceremonias cinematograficas: si
> algun dia se quieren, se disenan para AMBAS plataformas como feature nuevo.

- [x] TriviaPanel espejo (fases idle/question/result/finished: opciones A-F + countdown rojo ≤5s + "+X pts" + distribucion animada + podio top 5 + sponsor badge)
- [x] `useTrivia` hook local + reducer puro (NO zustand — la webapp usa useReducer + sockets como useQnA)
- [x] `POST /events/games/{gameId}/answer` proxy — NO optimista (backend devuelve correct/score)
- [x] Listeners `game:launched`/`game:result` → toasts + router.refresh (puntos/premios)
- [x] 10 vitest (reducer + helpers). E2E trivia → deuda menor

### W.17 — Soporte (13/13, **CERRADO 100% 2026-07-04**)

> Entregado 2026-06-29 nocturna (sprint 2.D). Split layout espejo W.14 + form nueva consulta + subflow del Asistente FAQ. Cerrado 100% al reclasificar 2 items bloqueados por otros modulos.

**Fase 0 — Hooks (2/2)**
- [x] `fetchSupportTickets` SSR (lib/support.ts server-only)
- [x] `createSupportTicketClient` mutation (lib/support-client.ts separado)

**Fase 1 — CreateTicketForm (3/3)**
- [x] Subject input max 200 + counter + validation
- [x] Message textarea max 2000 + counter
- [x] Submit + framer-motion + toast success + haptics enterprise

**Fase 2 — TicketsList (4/4)**
- [x] Cards ordenadas por fecha en wall izq
- [x] Status badge (open/responded/resolved) + AnimatePresence stagger
- [x] Admin response bar sin verde (feedback usuario 2026-06-29)
- [x] "Esperando respuesta" state

**Fase 3 — Real-time (reclasificado)**
- [x] ~~Socket respuesta admin RT~~ → **reclasificado a W.11 Sockets** (mecanismo real: announcement privado → `data:invalidate{announcements}` — `support:new_response` no existe como evento, auditoria 2026-07-04)
- [x] ~~Web Push notif~~ → **reclasificado a W.12 Polish**

**Fase 4 — Backend integration + Tests + Cierre (4/4)**
- [x] Backend: `EditSupportRequest` crea announcement privado `eventos://my-support` cuando admin responde (para que webapp se entere sin push Expo)
- [x] Vitest support-client (403/422/429 mapping)
- [x] Memoria + subflow Asistente (Soporte NO tiene nav item propio — entry via FAQ CTAs)

### W.18 — Hub Personal (19/19, **CERRADO 100% 2026-07-04**)

> Espejo directo `ProfileScreen.tsx` mobile (927 lineas, ~85% Expo). Split 35/65 espejo W.13/W.14/W.17. Ver `memory/project_w18_hub_personal_blueprint.md` para detalle arquitectural completo.

**Fase 0 — Backend integration (3/3)**
- [x] `lib/profile.ts` SSR fetchers (`fetchMyProfile`, `fetchMyPoints`, `fetchMyInterests`)
- [x] `lib/profile-client.ts` mutations con `ProfileClientError` (403/422/429) + normalize shape (`linkedin_url` → `linkedin`)
- [x] API proxies: `/api/profile` PUT, `/api/profile/photo` POST+DELETE (multipart), `/api/interests/[eventId]` PUT

**Fase 1 — Wall + Rows (5/5)**
- [x] Hero: avatar 92px + nombre + `cargo · empresa` + socials (LinkedIn/Twitter/Instagram/Web SVG inline)
- [x] Stats gamification 3 cards SIN iconos (Puntos / Retos x/y / Logros) — condicional a modulos
- [x] Rows clickeables con chevron (Mis datos / Mis intereses / Apariencia)
- [x] Footer: "Ver introduccion de nuevo" + "Cerrar sesion" (rojo)
- [x] Estilo espejo W.13/W.14/W.17

**Fase 2 — Sub-views (5/5)**
- [x] `PerfilDataForm`: 3 cards visuales agrupando (Sobre ti / Contacto / Redes) + 1 solo Guardar
- [x] `PerfilInterestsForm`: chips con contador min 1 + empty state honesto si organizador no configuro opciones
- [x] `PerfilAppearanceForm`: 2 cards Lux/Noir con preview aplicando via `useTheme()` al instante
- [x] `PerfilLogoutModal`: confirm con cross-tab broadcast + redirect
- [x] `PerfilAvatarMenu`: Radix Popover con Subir foto (max 5MB) + Shuffle beam + Eliminar (rojo)

**Fase 3 — Sidebar + deep link + i18n (3/3)**
- [x] Sidebar bottom (Asistente + Perfil + Bell) — refactor coherente. `ProfilePopover` + `UserMenu` eliminados
- [x] Deep link `eventos://profile[/datos|intereses|apariencia]` en parseActionUrl
- [x] i18n 63 keys en es/en/pt

**Fase 4 — Tests + Cierre (3/3)**
- [x] Vitest 14 nuevos (8 profileNormalize + 6 deep link perfil). Total 391/391
- [x] E2E `perfil.spec.ts` 13/13 verde con serial mode
- [x] Memoria `project_w18_hub_personal_blueprint.md` + `project_sidebar_bottom_zone.md`

### ~~W.X — Welcome Showcase~~ — **ELIMINADO 2026-07-04 (invento de planeacion)**

> Auditoria de procedencia: el onboarding real de Expo (~4.500 lineas) es un **wizard de
> REGISTRO** (welcome hero + auth + foto + forms dinamicos por admin + survey + done),
> NO un carrusel de features con miniaturas. El "showcase 6 beats" no espejaba nada.
> La webapp ya cubre su lado con W.1 magic link. Boton "Ver introduccion" del perfil
> queda OCULTO (Bloque 0, `4325f05`). Si Fase 2 pide espejo del wizard completo
> (registro con foto/forms/survey en webapp), se disena entonces como feature real.

---

## PENDIENTES PARALELOS (sin bloquear sprints)

### Documentales
- [ ] Decidir W.X para `recap/[eventId]` del Expo (no mapeado a ningun modulo webapp)
- [ ] Decidir W.X para `about.tsx` del Expo (texto + imagen + links sociales)
- [ ] Validar si `banners.tsx` Expo es vista dedicada o solo carousel embebido

### Mobile parity (cuando webapp este al dia)

> **Workstream confirmado por Kamilo 2026-07-04:** la webapp/PWA abierta en celular debe
> verse y COMPORTARSE exactamente igual a Expo (misma estetica) — es el fallback para
> quien no descarga la app en el evento presencial, donde mas se usan estos features.
> Diferido para despues del cierre Fase 1. Nada construido aun.

- [ ] **W.15 Vendor COMPLETO como feature mobile-web** (decision 2026-07-05: staff de stand no instala app — abre el webapp en el celular y trabaja): las 7 fases del bloque W.15 + **scanner QR con camara en browser** (prior art eventos-kiosko). Espejo estetico Expo
- [ ] **Mi QR del asistente en perfil — SOLO viewport mobile (<640px)**: espejo `MiQrScreen.tsx` Expo (502 lineas) — QR grande pieza principal + nombre + caducidad, token rota 60s via `GET /me/qr` (hook `useQrToken` refetch 50s). Desktop NO lo muestra (decision `feedback_qr_only_mobile`: el staff escanea el celular). qrcode.react ya esta en el bundle (desafio)
- [ ] Portar "click sesion → agenda highlight" del webapp W.5 al Expo
- [ ] Otros gaps mobile que aparezcan en sesiones futuras
- [ ] **Mobile webapp split Social vs Networking (2026-06-20):** webapp desktop unifica `/social` con sidebar (Feed + Personas + Solicitudes + Mis posts). Mobile webapp debe SEPARAR como en Expo: `/social` (Wall + Memorias + Momentos) + `/networking` (directorio + solicitudes + contactos + bloqueados). Ver `project_social_unified_notes.md` en memoria para arquitectura completa. Trabajo futuro, NO en scope actual

### Backend nice-to-have (NO bloqueante)
- [ ] Search server-side params standardizados
- [ ] AttendeeResource unificado
- [ ] Endpoint cancelar solicitud (`DELETE /contacts/request/{id}`)
- [ ] Score numerico match en suggested-contacts
- [ ] Sort server-side wall `?sort=likes_count`
- [ ] Endpoint paginado leaderboard >50
- [ ] Evento socket `points:awarded {amount, action, total}` informativo

### Analytics tracking
- [ ] `social.profile_opened`
- [ ] `social.connection_sent`
- [ ] `social.connection_message_added`
- [ ] `social.contact_method_clicked` (whatsapp/email/vcard)
- [ ] `social.profile_closed`
- [ ] Tracking views por sponsor / speaker / sesion

---

## CERRADO RECIENTE (ultimas 5 sesiones)

- **2026-06-27 (Sprint 2.B sesion 1)** — **W.9 Desafio hub inicial 18/35 (51%)**: split layout literal espejo W.7 + 6 cards apiladas espejo DESAFIO Expo (Hero + Golden Tickets + Premios preview + Tip + Retos + Pasaporte) + 6 detail panels (GoldenTicket reveal con QR real qrcode.react + Ranking podio escalado + Premios catalogo + Todos los retos + Pasaporte + Como funciona con 5 reglas educativas + tabla puntos). Avatar component reusable con photo_url + boring-avatars beam fallback (espejo Expo `lib/avatars.ts`). RgbRing + RgbRect con WAVE_COLORS pasteles 6s linear (espejo Expo). Colores **TEAL fijos** (`#39d2c0` + `#B5A68B` GOLD + `#C0C0C0` silver + `#CD7F32` bronze) — **NO `var(--accent)` del cliente** porque Expo no customiza gamification. SSR agrega 5 endpoints con degradacion suave. i18n + sidebar wired. Commit `32018f1` eventos-web pusheado. Pendiente sesion 2: lazy fetch + mutations + tests vitest/E2E + validacion contra backend real.
- **2026-06-21 (Sprint 2.A entero + Sprint 1 cierre)** — **W.7 Sponsors CERRADO 23/23** en una sesion DaVinci larga: wall espejo Expo + framer-motion shuffle (damping 28, stiffness 120) + DetailPanel Hero/Sessions/Trivia (espejo TriviaModal Expo) + ContactForm (chips + 409 ALREADY_CONTACTED) + skeleton SSR + tooltip radix + 14 vitest + **12 E2E Playwright verde** + Lighthouse acc 98 + CLS 0. Cierra **Sprint 2.A entero** y **cruza 50% global** (369/707 = 52.2%).
- **2026-06-21 (Sprint 1 item 8 — W.3)** — **Bulk .ics download**: `lib/ics.ts` generador puro RFC 5545 (VCALENDAR + N VEVENT con UID determinista, DTSTAMP UTC, escape comas/semicolons/backslash). Boton "Todas" del AgendaHeader visible cuando `countMine > 0` en CUALQUIER dia (antes era `visibleSessions.length > 0` del dia activo). Filename `mi-agenda-{event.slug}.ics`. +16 tests vitest.
- **2026-06-21 (Sprint 1 item 9 — W.0)** — **Wire sidebar verificado + cleanup**: smoke 5/5 items (home/agenda/live/speakers/social) navegan sin error. Patron `available: boolean` ya implementado (sponsors disabled con tooltip "proximamente"). Bonus: quitado brand letter `event.name?.charAt(0)` del sidebar (generaba ruido visual tipo debug en eventos sin logo elaborado). **Sprint 1 CERRADO 9/9**.
- **2026-06-21 (BUG-335)** — **Fix theme provider**: next-themes 0.4.6 incompatible Next 16 + React 19 (issues upstream #385/#387 sin fix). Reemplazado con provider propio 60 lineas + script anti-FOUC inline en `<head>` del LocaleLayout server component.
- **2026-06-21 (Backend gaps BUG-336/BUG-337)** — SponsorResource expone trivia/passport/visit_points + GamificationController visitStand devuelve `points_awarded` distinguiendo idempotente (patron a auditar en W.3/W.4/W.6/W.9).
- **2026-06-21 (Polish W.7 — BUG-338)** — 4 fixes agrupados: halo accent rojo eliminado de cards selected (outline accent redundante + se veia mal con primary_color rojo del cliente), elevacion preservada durante shuffle (era el `transition: transform` CSS chocando con framer-motion `layout` — eliminado), heart pop reemplazado por framer-motion (CSS keyframes forzado removido), toast inline → top-center con colores neutrales (no `var(--accent)`).
- **2026-06-20 (Sprint 1, item 7)** — W.6 Tabs filtros feed: `FeedTab` type ("recent" | "top" | "mine") + helper `sortAndFilterFeed` en socialDerive (pure, testeable). FeedView con state local de tab + 3 empty states distintos por contexto. Reusa `.sn-rqx-tabs` global. 6 tests vitest agregados (recent preserva ref, top desc + tie-break created_at, mine filtra is_mine). 202/202 verde.
- **2026-06-20 (Sprint 1, item 6)** — W.8 Skeleton estructurado en AttendeeProfilePanel: 3 secciones placeholder (Sobre con titulo+3 lineas bio, Intereses con 5 chips varying width, Asistira a con 2 session cards). Reusa `.sn-sk-shape` shimmer existente, 5 reglas CSS nuevas. Reemplaza las 3 lineas genericas previas. Sin tests nuevos (visual-only, sin logica). 196/196 verde.
- **2026-06-20 (Sprint 1, item 5)** — W.8 Bloqueados list: tercera tab dentro de SolicitudesView, `fetchBlockedAttendees` SSR en `lib/social.ts`, `handleUnblock` optimistic en SocialView (con revert), `BlockedRow` no clickeable + boton Desbloquear ghost (sin confirm — alineado con Twitter/Instagram). Cierra el gap UX del ConfirmPop ("El bloqueo es reversible" ahora tiene donde verse y deshacerse). 2 tests vitest agregados (196/196 total). Tercer item cerrado del Sprint 1 con codigo (vs 3 admin/cierre formales).
- **2026-06-20 (Sprint 1, item 4)** — W.8 ConfirmPop DaVinci reemplaza `window.confirm` del boton Bloquear. **v1 rechazada:** shadcn AlertDialog generica, visual generico, fuentes default (font-medium con Plus Jakarta cae a sistema). **v2 final:** nuevo `ui/confirm-pop.tsx` + `confirm-pop.css` global espejo del patron `rating-pop`/`attendees-pop` — Plus Jakarta 700 20px titulo + Urbanist 14px desc + drag handle iOS + 440px + shadow doble + boton Bloquear rojo solido. Copy honesto "El bloqueo es reversible" (NO promete vista de bloqueados que no existe). Memoria `feedback_analyze_before_code.md` actualizada con anti-pattern shadcn vs patron del proyecto. 194/194 verde.
- **2026-06-20 (Sprint 1, item 3)** — Renombrado doc W.10 viejo → W.18 Hub Personal via `git mv`. Actualizadas referencias en `_index.md`, `PARITY-MATRIX.md`, `BACKEND-API-MAP.md`, `PLAN.md` (tablas modulos + estimacion). Agregada row nueva para W.10 Live Hub en PLAN.md. Total bloqueante webapp: 139h → 143h (incluye W.10 Live Hub nuevo).
- **2026-06-20 (Sprint 1, item 2)** — W.10 Live Hub cerrado por consenso (16/16, 100%): validacion visual con `LiveHubDemoSeeder` (3 lives + 6 upcoming + 4 past) confirmada por usuario. Skip vitest componente + doc maestro (anti-regadero — info ya vive en commit + JSDoc + E2E). Segundo modulo cerrado en una sesion.
- **2026-06-20 (Sprint 1, item 1)** — W.5 Speakers cierre formal: tests 27/27 verde, memoria actualizada, counters PARITY+PENDIENTES sincronizados. Primer modulo cerrado al maximo posible (94%) desde W.1B. Faltan solo Lighthouse + device real (requieren usuario fisico).
- **2026-06-20 (Sprint 0)** — Re-auditoria + Sprint 0 hygiene: tests vitest 194/194 verde (fix localStorage), backend health OK, 6 rutas smoke clean, screenshot pendiente borrado. Corregido desfase docs: W.5 (0%→94%), W.6 (0%→42%), W.10 Live Hub (75% modulo nuevo), W.10 viejo→W.18 renombrado. Cifra global subio de 40% a 48%.
- **2026-05-21** — Auditoria parity + creacion `docs/webapp/PARITY-MATRIX.md` (4 fases agentes paralelo, 117/117 endpoints OK)
- **2026-05-17/18** — W.8 perfil attendee in-slot visionOS + feed editorial + 49 tests vitest (194 total)
- **2026-05-15/16** — W.8 avatar beam fallback espejo Expo (commit 332b2ef)
- **2026-05-13/14** — W.8 Social Networking modulo base (feed + directorio + solicitudes + mis posts)
- **2026-05-08** — W.5 Speakers demo HTML aprobado (mirror Expo, sin tracks/featured flags)
- **2026-05-07** — Backend audit completo 197 endpoints, BACKEND-API-MAP.md
- **2026-05-06** — W.3 Agenda implementado React + W.2 Home recap base
- **2026-05-05** — W.0 Spatial UI fix tablet canvas
- **2026-05-04** — W.0 Spatial Shell implementado React validado
- **2026-05-02** — W.1 + W.1B cerrados (auth magic link + slideshow + UI foundation + tests)

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
- **Cerrado reciente** mantener ultimas 10 sesiones max (rotar)
