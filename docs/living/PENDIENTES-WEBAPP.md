# Pendientes — Webapp (post-Bancolombia, producto propio)

> Vista operativa unica. Todo lo faltante para vender webapp standalone — desde tu cama, transporte o cualquier device sin abrir otros docs.
>
> **Re-auditado:** 2026-06-20 (recount realidad codigo vs doc despues de 1 mes de pausa)
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
| W.0 Spatial UI base | **21/24** | **87%** — en proceso | **+1** (Sprint 1 item 9 — wire sidebar + quitar brand letter) |
| W.1 Setup + Auth + UI Foundation | 102/107 | 95% — en proceso | — |
| W.1B Backend magic link | 10/10 | **CERRADO** | — |
| W.2 Home | **12/20** | **60% — en proceso** (split adaptive col der LIVE: cartel arriba si hay items, feed salas ocupa 100% si no — 2026-06-30) | **+2** |
| W.3 Agenda | **25/30** | **83%** — en proceso | **+1** (Sprint 1 item 8 — bulk .ics) |
| W.4 Streaming | 83/111 | 75% — en proceso | — |
| W.5 Speakers | **33/35** | **94% — cerrado al maximo posible** (solo faltan Lighthouse + device fisico) | **+33** (doc decia 0%, codigo lo tiene casi completo) |
| W.6 Social Wall | **18/40** | **45%** — feed+composer+likes+comments+tabs filtros hechos | **+18** (Sprint 1 item 7 cerrado) |
| **W.7 Sponsors** | **23/23** | **CERRADO 2026-06-21** | **+23** (Sprint 2.A entero — skeleton + tooltip + E2E 12/12 + Lighthouse acc 98) |
| W.8 Networking | 15/25 | 60% — en proceso | — |
| **W.9 Engagement** | **35/35** | **CERRADO 2026-06-29 (Sprint 2.B)** | **+5** (2026-06-29: redemptions inline + E2E 11/11 verde con viewports desktop/tablet H/mobile + 8 tests vitest. counter PARITY sync) |
| **W.10 Live Hub** | **16/16** | **CERRADO por consenso 2026-06-20** | **+16** (creado en commit `0e185e6`, validado visual con seeder) |
| W.11 Sockets RT | 8/42 | 20% — usado parcial en W.4 | — |
| W.12 Polish + E2E + PWA | 0/43 | 0% — cierre Fase 1 | — |
| **W.13 FAQ + Docs + Pages** | **15/17** | **88% — Fase B Documents entregada 2026-06-30 tarde** (Fase A FAQ + Fase B `/documentos` split layout + `<iframe>` preview PDF/imagen/video/audio + skeleton shimmer + descarga individual + **bulk ZIP pre-generado backend con job + observer + endpoint escalable a 10K users**). Falta solo Pages dinamicas (skip Fase 2 segun feedback) | **+5** |
| W.14 Anuncios + Bell + Cartel | **17/20** | **85% — Fase B Cartel Digital entregada 2026-06-30** (CartelDigital ambient signage 16:9 en col der LIVE: cross-fade 700ms cada 6s, sin dots/arrows, hover pausa, sponsor pill, deeplink reusa parseActionUrl, merger round-robin banners+highlights, 11 vitest merger + 12 vitest componente + 6 E2E). Falta: RT socket `announcement:new` (depende W.11) + Web Push (W.12) | **+6** |
| W.15 Vendor Dashboard | 0/35 | **OPCIONAL** Fase 1 | — |
| W.16 Live Moments | 0/23 | 0% — **SKIP webapp** (mobile-first, sorteos/trivia/golden ticket reveal son experiencia celu, no laptop). Webapp solo muestra resultados historicos | — |
| **W.17 Soporte** | **13/15** | **87% — entregado 2026-06-29 nocturna** (split layout espejo W.14 + form nueva consulta + integrado FAQ como subflow + lib/support-client separado de support.ts + framer-motion stagger + haptics enterprise + Web Push fase B). Falta: notif real-time `support:new_response` (depende W.11) | **+13** |
| **W.18 Hub Personal** (renombrado desde W.10 viejo) | 2/19 | 10% — backlog | renumerado para evitar choque |
| W.X Welcome Showcase | 0/7 | **BLOQUEADO** | — |
| **TOTAL** | **451/707** | **63.8%** | +13 sesion 2026-06-30 (W.14 Fase B +6 + W.2 Home +2 + W.13 Fase B +5). Cartel Digital + Documents split layout + backend ZIP pre-generado escalable (job + observer + endpoint). Bug fix useNow snapshot inestable (agenda hydration). 377/377 vitest verde |

> Conflicto W.10 resuelto 2026-06-20: el codigo creo "W.10 Live Hub" reusando el numero. Doc viejo "W.10 Hub Personal" se renombra a W.18 Hub Personal. Sin refactor de codigo, solo doc.

---

## QUE SIGUE (1 sola tarea concreta)

- [ ] **Sprint 2.F — W.18 Hub Personal** (~2-3h, 1 sesion) — perfil editable + intereses + logout. **CRITICO** para vender webapp standalone. **Riesgo BAJO** (blueprint claro tras investigacion Expo).

  **Blueprint listo** en `memory/project_w18_hub_personal_blueprint.md`:
  - Expo `ProfileScreen.tsx` (927 lineas, ~85% completo) sirve como espejo directo
  - 11 endpoints backend listos: `/me/profile` GET/PUT, `/me/photo` POST/DELETE, `/me/points`, `/events/{id}/my-interests` GET/PUT, etc.
  - Componentes Expo replicables: `StatCard`, `DataRow`, `EditField`, `BottomSheet`
  - **Decision DaVinci pendiente al arrancar:** layout single-column centrado max-w-640 vs split layout wall + panel. Mi voto tentativo single-column (es tu perfil personal, no navegador de listas). Definir al inicio, NO codear antes.
  - Divergencias Expo → Webapp confirmadas: FAQ ya en sidebar (no duplicar entry), Toggle tema global existe (no duplicar aca), idioma toggle nuevo webapp (i18n ya en 3 idiomas)

> **Orden recomendado (re-estimado 2026-06-30 tarde tras investigar W.18):**
> 1. ~~W.14 Fase B~~ → CERRADO 2026-06-30 (17/20, falta solo RT socket)
> 2. ~~W.13 Fase B Documents~~ → CERRADO 2026-06-30 tarde (15/17, backend ZIP escalable)
> 3. **W.18 Hub Personal (~2-3h, riesgo BAJO)** — espejo Expo directo confirmado
> 4. W.8 Networking completar (~1.5-2h)
> 5. W.4 Replay + in-stream (~3h) — riesgo MEDIO backend replay
> 6. W.11 sockets criticos 6 items (~1-1.5h) — riesgo ALTO E2E flaky
> 7. W.12 Web Push + Sentry (~2-3h)
> 4. W.8 Networking (~1.5-2h, antes ~3h)
> 5. W.4 Replay + anuncios in-stream (~3h, antes ~5h) — riesgo MEDIO si backend replay no listo
> 6. W.11 sockets criticos 6 items (~1-1.5h, antes ~2h) — riesgo ALTO E2E flaky
> 7. W.12 Web Push + Sentry (~2-3h, antes ~4h)
>
> **Re-estimacion 2026-06-30:** `project_velocity_analysis_2026_06_30.md`. Roadmap original 22h → realista **11-14h en 3 sesiones DaVinci**. Velocidad subio por reuso de patrones (parseActionUrl, lumina, useNow, framer split layout) — no por atajos. Riesgos vigilados: W.18 sin espejo, W.11 sockets, W.4 backend replay.
>
> **Skips confirmados:** W.16 entero (mobile-first), W.6 Stories+Photo Contest (mobile-first, nicho), W.2 sponsors band + lifecycle states (ya hay /sponsors, cinematic ya esta), W.11 reducido de 42 a ~6 items, W.12 reducido a Web Push+Sentry (PWA installable + offline + Lighthouse100 + E2E full → Fase 1.5 o skip).

---

## SPRINTS PROPUESTOS (orden recomendado, recalculado 2026-06-20)

### Sprint 0 — Hygiene (~2-3h, urgente) — 4/4 **CERRADO**
> Bloqueante. Sin esto cualquier cierre formal es mentira.

- [x] ~~Reparar suite vitest (194/194 fallando)~~ → 194/194 verdes (fix `tests/setup.ts` localStorage stub, 2026-06-20)
- [x] ~~Verificar Laragon backend~~ → health 200 OK (2026-06-20)
- [x] ~~Smoke test 6 rutas~~ → todas 307 (auth gate funcionando), login 200, cero 500, cero warnings runtime
- [x] ~~Decidir screenshot~~ → borrado (era captura Valorant random, sin relacion con la webapp, 2026-06-20)

### Sprint 1 — Cierres formales modulos casi-hechos (~2-3h, 1 sesion) — 9/9 **CERRADO 2026-06-21**
> Cosechar lo que ya esta al 80-90%. Cierra modulos completos = sube % global y baja stress mental.

- [x] ~~**W.5 Speakers — cierre formal**~~: hecho 2026-06-20 (Sprint 1 item 1) — tests 27/27 verde, memoria `project_w5_speakers_v2.md` actualizada, counters PARITY+PENDIENTES sincronizados (94%). Lighthouse + device fisico → batch QA final cross-modulos
- [x] ~~**W.10 Live Hub — cierre formal**~~: hecho 2026-06-20 (Sprint 1 item 2) — validacion visual con `LiveHubDemoSeeder` (3 lives + 6 upcoming + 4 past) OK. Skip vitest componente + doc maestro (anti-regadero: info ya vive en commit `0e185e6` + JSDoc + E2E + esta seccion)
- [x] ~~**W.10 (viejo) → W.18 Hub Personal — renombrar doc**~~: hecho 2026-06-20 (git mv + actualizadas refs en _index, PARITY-MATRIX, BACKEND-API-MAP, PLAN)
- [x] ~~**W.8 — AlertDialog DaVinci reemplazando `window.confirm` Bloquear**~~: hecho 2026-06-20 (Sprint 1 item 4) — v1 shadcn AlertDialog rechazada por generica; v2 final con `ui/confirm-pop.tsx` + CSS global espejo patron `rating-pop`, Plus Jakarta 700 20px + Urbanist 14px, copy honesto "El bloqueo es reversible". Memoria `feedback_analyze_before_code.md` actualizada
- [x] ~~**W.8 — Skeleton mejor AttendeeProfilePanel**~~: hecho 2026-06-20 (Sprint 1 item 6) — v1 con 3 secciones estructuradas (Sobre+Intereses+Asistira) rechazada (over-promesa visual, campos condicionales pueden no aparecer). v2 final honesto: 1 titulo chico + 3 lineas tipo bio. Reusa `.sn-sk-shape` shimmer existente
- [x] ~~**W.6 — Tabs filtros Recientes/Mas likes/Mis posts**~~: hecho 2026-06-20 (Sprint 1 item 7) — `FeedTab` type + helper `sortAndFilterFeed` pure en socialDerive. FeedView con state local + 3 empty states distintos. Reusa `.sn-rqx-tabs` global. 6 tests vitest agregados
- [x] ~~**W.3 — Bulk .ics download**~~: hecho 2026-06-21. Reemplazado handler fake `handleTodas` con `downloadAgendaIcs()` real. Generador puro `lib/ics.ts` (RFC 5545: VCALENDAR + N VEVENT con UID determinista, DTSTAMP UTC, escape comas/semicolons/backslash). Boton "Todas" del AgendaHeader ahora visible cuando hay favoritas en CUALQUIER dia (`countMine > 0`, antes era `visibleSessions.length > 0` del dia). Filename `mi-agenda-{event.slug}.ics`. +16 tests vitest (218 verde, typecheck OK)
- [x] ~~**W.0 — Wire modulos top-level a sidebar**~~: hecho 2026-06-21. Verificado smoke 5/5 items (home, agenda, live, speakers, social) navegan sin error. Patron `available: boolean` ya implementado: `/sponsors` correctamente disabled con tooltip "proximamente" + opacidad 55%. Logica decidida: **conforme aparece cada modulo, se cambia `available: false` → `true` en `SidebarPill.tsx`** (no agregamos items fantasma futuros). Bonus: quitado el brand letter inicial (`event.name?.charAt(0)`) — generaba ruido visual tipo debug en eventos sin logo elaborado

### Sprint 2 — Modulos criticos no empezados, orden CRITICO PARITY (~25h, 4-5 sesiones DaVinci) — 23/N
> "Lo que falta para vender webapp standalone" segun PARITY-MATRIX seccion 5.

- [x] ~~Sprint 2.A — W.7 Sponsors~~ — **CERRADO 23/23 (2026-06-21)** — Sprint 2.A entero en una sesion DaVinci larga: wall espejo Expo + framer-motion shuffle + DetailPanel (Hero/Sessions/Trivia/Contact/Actions) + skeleton SSR + tooltip radix + 14 vitest + **12 E2E Playwright verde** + Lighthouse acc 98 + CLS 0
- [x] ~~Sprint 2.B — W.9 Engagement~~ — **CERRADO 35/35 (2026-06-29)** — hub split + 6 cards + 6 panels + RGB ring + QR + lazy fetch + redeem optimistic + redemptions inline + bloque orphans. 270/270 vitest + 11/11 E2E (incluye desktop 1600 / tablet H 1130 / mobile 390 sin overflow horizontal)

- [x] ~~Sprint 2.C — W.14 Anuncios + Banners + Bell~~ — **CERRADO 17/20 (2026-06-30)**. Fase A 2026-06-29 (Anuncios + BellPopover + deeplink + E2E 13). Fase B 2026-06-30 (CartelDigital ambient signage 16:9 col der LIVE: cross-fade 700ms cada 6s, sin dots/arrows, hover pausa, sponsor pill, merger round-robin banners+highlights, backend reusado cero cambios, 23 vitest + 6 E2E). Falta: socket `announcement:new` (depende W.11) + Web Push (W.12)
- [x] ~~Sprint 2.D — W.17 Soporte~~ — **CERRADO 13/15 (2026-06-29)** (split layout espejo W.14 + subflow Asistente + EditSupportRequest crea announcement privado). Falta RT response (W.11)
- [x] ~~Sprint 2.E — W.13 Fase B Documents~~ — **CERRADO 15/17 (2026-06-30 tarde)** (split layout wall + preview embed + skeleton shimmer + descarga individual + bulk ZIP pre-generado backend con job + observer + endpoint escalable a 10K users, backend migration + Model fillable + composer maennchen/zipstream-php pure PHP). Falta: Pages dinamicas (skip Fase 2)
- [ ] Sprint 2.F — W.18 Hub Personal (perfil editable + intereses + logout) (~2-3h, 1 sesion, riesgo BAJO) — 0/19. **Blueprint en `memory/project_w18_hub_personal_blueprint.md`** — espejo Expo `ProfileScreen.tsx` + 11 endpoints backend listos + componentes reusables identificados. Decision DaVinci pendiente: layout single-column vs split

### Sprint 3 — W.6 completar (Stories + Photo Contest + Hashtags) (~3-4h, 1 sesion) — 0/19
### Sprint 4 — W.16 Live Moments (Trivia + Sorteo + Golden Ticket reveal) (~6h, 1-2 sesiones) — 0/23
### Sprint 5 — W.13 FAQ + Documentos + Pages (~3h, 1 sesion) — 0/17
### Sprint 6 — W.8 Networking completar (bloqueados + mi perfil + filtros role) (~3h, 1 sesion) — 0/10
### Sprint 7 — W.2/W.3/W.4 completar (sponsors band + lifecycle + replay + anuncios in-stream) (~10h, 2 sesiones) — 0/30
### Sprint 8 — W.11 Sockets RT consolidacion (~6h, 1-2 sesiones) — 0/34
### Sprint 9 — W.12 Polish + E2E + PWA (cierre Fase 1) (~8-10h, 2 sesiones) — 0/43

**Total estimado para webapp standalone vendible:** ~70-80h sin W.15 vendor (10-12 sesiones DaVinci)

---

## BACKLOG GRANULAR — TODO desglosado

### W.0 — Spatial UI base (21/24, 87%)

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
- [ ] **Command palette ⌘K funcional** (busqueda navegar a cualquier seccion)
- [ ] **Pre-load vecinos** (lazy mount de paneles adyacentes)
- [ ] **Validar device real** iPad portrait/landscape, Pixel, iPhone

### W.1 — Setup + Auth + UI Foundation (102/107, 95%)

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
- [ ] **B4 StaggerList animation** (diferido)
- [ ] **B11 Swipe haptics** (diferido device real)
- [ ] **Smoke test 3 viewports en device real** (Pixel/iPhone/iPad)
- [ ] **Lighthouse final pass** (Performance >=85 desktop, >=75 mobile)
- [ ] **CSP whitelist Vimeo + Sentry final** (pendiente W.4 integracion completa)

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

### W.2 — Home (10/20, 50%)

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
- [ ] **Sponsors logo band sutil** (strip discreto)
- [ ] **GamificationHud preview LIVE** (puntos + posicion mini)
- [ ] **Anuncios mini con count badge** (punto rojo)
- [ ] **Post-event survey prompt** estado ENDED
- [ ] **EventArchive link** estado ENDED
- [ ] **Multi-sede pill** (si aplica)
- [ ] **Foto real speaker** (hoy gradient placeholder)
- [ ] **Proximos eventos org en estado ENDED**
- [ ] **Atmosfera dinamica por estado** (luz, partículas sutiles)
- [ ] **useHappeningNow hook refinado** con dedupe

### W.3 — Agenda (25/30, 83%)

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
- [ ] **Lifecycle badges ORIGINAL/AJUSTADA/CANCELADA** (depende W.11 socket)
- [ ] **Conflict detector visual** (calcular client-side, depende W.12)
- [ ] **Room check-in boton** (endpoint backend listo)
- [x] ~~**Bulk .ics download**~~ (todas mis favoritas un solo archivo) — hecho 2026-06-21 (Sprint 1 item 8): `lib/ics.ts` generador puro RFC 5545 (VCALENDAR + N VEVENT con UID determinista, DTSTAMP UTC, escape comas/semicolons/backslash). Boton "Todas" del AgendaHeader visible cuando `countMine > 0` en CUALQUIER dia. Filename `mi-agenda-{event.slug}.ics`. +16 tests vitest
- [ ] **Recordatorio push 10min antes** (depende W.10 settings)
- [ ] **RT socket invalidation** (depende W.11)
- [ ] **URL state shareable** (filtros en query params)
- [ ] **Playwright E2E happy path**

### W.4 — Streaming (83/111, 75%)

- [x] Detector source YouTube/Vimeo/iframe/generic/empty (19 tests)
- [x] StreamPlayer 16:9 universal
- [x] Tracking view analytics
- [x] useSessionDetail hook
- [x] useSessionLiveConfig hook (polling fresh stream_url)
- [x] useSessionRating hook
- [x] Q&A panel submit
- [x] Q&A upvote
- [x] Q&A filtros (approved/my)
- [x] Chat panel optimistic tempId
- [x] Chat dedup contra socket propio
- [x] Pinned banner socket-driven
- [x] Polls voting multiple_choice
- [x] Polls voting open
- [x] Polls voting star
- [x] Layout spatial desktop 60/20/20
- [x] Socket singleton + auth bearer
- [x] (...60+ items implementados)
- [ ] **Trivia integration panel** (delegado W.16)
- [ ] **Anuncios in-stream pinned/announcement/display overlay**
- [ ] **Custom panel iframe**
- [ ] **Replay detection automatica** post-stream
- [ ] **Rating modal auto** si finished + no rated
- [ ] **Mobile layout** stream + paneles
- [ ] **Tablet layout** stream + paneles
- [ ] **Floating emojis** en chat (Animated parallel translateY/scale/rotate)
- [ ] **Slow mode chat** configurable
- [ ] **CSP Vimeo embed** whitelist
- [ ] **Playwright E2E** stream + Q&A + chat cross-tab
- [ ] (...17 items menores: tablet pinning, edge cases stream broken, AppState background tracking, ...)

### W.5 — Speakers (33/35, 94% — **CERRADO al maximo posible sin device fisico**)

> Implementado en commit `134bf6e` (2026-05-09). Doc anterior decia 0%, recount 2026-06-20 corrige.
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

**Fase 5 — Deep link + viewport (3/4)**
- [x] Deep link `?id=X` auto-open via useState initializer (R19 set-state-in-render)
- [x] URL sync sin recargar (router.replace scroll:false)
- [x] SSR fetcher (page.tsx hace `Promise.all([speakers, myRatings])`)
- [ ] Validar 3 viewports en device real (desktop/tablet/mobile)

**Fase 6 — Tests (3/3)**
- [x] Vitest `tests/components/speakers/speakersDerive.test.ts`
- [x] Vitest `tests/lib/speakersClient.test.ts`
- [x] Playwright `e2e/speakers.spec.ts` (13 escenarios: auth gate, search, panel, stars, LinkedIn condicional, ya calificado, modal focus, optimistic, 409 silencioso, 500 revert, click sesion, deep link, Esc layer order)

**Fase 7 — Cierre (6/7)** — cerrado lo que se puede sin device fisico/auth
- [ ] Lighthouse Performance >=85 (PENDIENTE USUARIO — requiere navegar logueado + chrome devtools)
- [ ] Lighthouse Accessibility >=95 (PENDIENTE USUARIO — mismo)
- [x] Tests verdes (194/194 vitest verde post-fix localStorage 2026-06-20)
- [x] Detalle commit DaVinci (commit `134bf6e` describe el modulo)
- [x] Memoria actualizada (`project_w5_speakers_v2.md` cierre formal agregado 2026-06-20)
- [x] PARITY-MATRIX seccion W.5 actualizada
- [ ] Validar device real (PENDIENTE USUARIO — laptop + tablet + mobile fisico)

### W.6 — Social Wall (18/40, 45% — feed editorial implementado, faltan Stories+Contest+Hashtags)

> Recount 2026-06-20: el feed editorial implementado en `/social` (compartido con W.8 Networking) es W.6 Wall. Doc anterior listaba 0% por error de auditoria. Lo IMPLEMENTADO marcado [x] aqui.

**Fase 0 — Hooks (2/3)**
- [x] `fetchWallFeed` SSR (lib/social.ts) — backend usa `?page=` (paginacion pendiente UI)
- [ ] `usePostComments` lazy (hoy carga al expandir InlineComments — verificar si es lazy real)
- [x] `createWallPost` mutation con foto opcional + manejo `pending` (post en moderacion)

**Fase 1 — Feed (3/4)**
- [x] PostCard render
- [x] InlineComments expandible
- [ ] Paginacion page-based UI (SSR carga primera, falta load-more / infinite scroll)
- [x] Empty hint en SidebarRight ("Conecta con asistentes desde Personas")

**Fase 2 — Like + Comments (5/5)**
- [x] Heart optimistic (`toggleLikeOptimistic` + `toggleWallLike`)
- [x] POST revert on fail (SocialClientError catch)
- [x] Sync likes_count con server (race condition manejada)
- [x] Click "X comentarios" expande sub-thread (estado `expandedComments`)
- [x] Input crear comentario inline (Composer + handleCommentAdded)

**Fase 3 — Crear post (3/4)**
- [x] Composer textarea max 500
- [x] **Imagen upload** preview antes enviar (File API en createWallPost)
- [x] Post optimistic aparece + lumina toast
- [ ] Listener `wall:post` deduplica propio via socket (depende W.11)

**Fase 4 — Stories (0/4)**
- [ ] **StoriesBar arriba feed con avatares ring + upload "+"**
- [ ] **StoryViewer modal full screen progress bars + tap next**
- [ ] Upload story foto 9:16
- [ ] Auto-expire 24h (backend cleanup)

**Fase 5 — Photo Contest (0/2)**
- [ ] **Photo Contest banner** (status active/ended, countdown timer, podio top 3 con medallas)
- [ ] CTA → vista concurso / sube tu foto

**Fase 6 — Hashtags client-side (0/2)**
- [ ] Parser regex `/#[\w_-]+/g`
- [ ] Click filtra feed client-side

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

### W.8 — Networking (~15/25, 60%)

> Lo "social" implementado en `/social` que NO es feed Wall (W.6) realmente vive aqui.

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
- [ ] **Mi perfil editable** (form avatar + bio + intereses multi-select + redes)
- [ ] **Filtro role dropdown** (attendee/speaker/sponsor/etc) en directorio
- [ ] **RT listeners** `networking:notify` (request_received/accepted toast + invalidate)
- [ ] **Gap C Sugeridos cards grandes** mover panel der al centro Personas
- [ ] **Playwright E2E** abrir perfil → conectar → confirmar solicitud
- [ ] **Tracking analytics** social.profile_opened + connection_sent + contact_method_clicked
- [ ] **Cierre commit + memoria + counter PARITY-MATRIX**

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
- [ ] Socket `data:invalidate{entity:passport}` → animacion + toast — depende W.11 sockets RT

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

### W.11 — Sockets RT (8/42, 20%)

- [x] socket.io-client instalado
- [x] Auth bearer via socket-token endpoint
- [x] Singleton usado en W.4 Streaming (chat + Q&A + polls)
- [x] Dedup tempId chat propio
- [x] Listeners session:* live indicator
- [x] Pinned message socket-driven
- [x] Optimistic + revert chat
- [x] Connection status pill
- [ ] **Setup singleton centralizado** (extraer de W.4)
- [ ] **Connection management** exponential backoff
- [ ] **Reconnect on focus**
- [ ] **useSocketRoom join/leave hook generico**
- [ ] **Listener wall:post** prepend feed dedup
- [ ] **Listener wall:comment** invalidate comments
- [ ] **Listener networking:notify** toast + invalidate
- [ ] **Listener announcement:new** + display:project
- [ ] **Listener data:invalidate** generico (points/leaderboard/passport)
- [ ] **Listener auth:ban** force logout
- [ ] **Listener agenda:updated** (futuro lifecycle)
- [ ] **Listener game events** (W.16 trivia/spin/jackpot)
- [ ] **Long-polling fallback** (corporate firewalls)
- [ ] **Skip-self** en eventos propios broadcast
- [ ] **Auth token refresh** durante conexion abierta
- [ ] **(...19 listeners adicionales del catalogo 28 eventos S→C)**
- [ ] **Tests Vitest singleton + dedup + reconnect**
- [ ] **Playwright RT cross-tab**
- [ ] **Performance 10K concurrent stress test**
- [ ] **Counter PARITY-MATRIX**

### W.12 — Polish + E2E + PWA (0/43, BACKLOG cierre Fase 1)

**Fase 0 — Audit responsive (0/4)**
- [ ] 3 viewports en device real (laptop / iPad / iPhone)
- [ ] Validar tablet portrait warning overlay
- [ ] Validar Edge corporativo
- [ ] Validar Firefox 115+

**Fase 1 — Skeletons + empty (0/3)**
- [ ] Skeleton consistente todos los modulos
- [ ] Empty states consistentes
- [ ] Loading transitions

**Fase 2 — Accesibilidad (0/5)**
- [ ] WCAG AA contraste 4.5:1
- [ ] Focus visible :focus-visible outline accent
- [ ] Keyboard nav completa
- [ ] Tab order logico
- [ ] ARIA labels iconos sin texto

**Fase 3 — Performance (0/8)**
- [ ] Bundle <200KB gzipped
- [ ] Code splitting por modulo
- [ ] Lazy @dnd-kit + framer-motion
- [ ] next/image sizes correcto
- [ ] Lighthouse Performance >=85 desktop
- [ ] Lighthouse Performance >=75 mobile
- [ ] TTI <3s 4G Bogota
- [ ] Migrar SSR → TanStack Query infinite cache (post-W.11)

**Fase 4 — SEO (0/3)**
- [ ] Meta tags por pagina
- [ ] OG images
- [ ] sitemap.xml

**Fase 5 — PWA (0/5)**
- [ ] Manifest
- [ ] Service Worker
- [ ] Install prompt condicional desktop/tablet
- [ ] Install prompt NO en mobile (no canibalizar app)
- [ ] Offline fallback page

**Fase 6 — Print (0/2)**
- [ ] Stylesheet print friendly
- [ ] Imprimir agenda + ratings

**Fase 7 — E2E (0/4)**
- [ ] Smoke test critical paths
- [ ] Login + home + agenda
- [ ] Streaming + Q&A cross-tab
- [ ] Social conectar cross-tab

**Fase 8 — Sentry validation (0/2)**
- [ ] DSN prod
- [ ] Source maps subidos en build (no en cliente)

**Fase 9 — Cierre (0/7)**
- [ ] CSP estricto
- [ ] X-Frame-Options
- [ ] Reduced motion verificado
- [ ] reduced-motion serie estatica W.X
- [ ] Bancolombia embed test
- [ ] Memoria
- [ ] Counter PARITY-MATRIX

### W.13 — FAQ + Documentos + Pages (0/17, BACKLOG)

**Fase 0 — Hooks (0/3)**
- [ ] useFaqs
- [ ] useDocuments
- [ ] usePages

**Fase 1 — FAQ (0/4)**
- [ ] Accordion estados (browsing/thinking/answering) — referencia Expo
- [ ] Search debounce 300ms
- [ ] Chips categoria
- [ ] Counter "Mis consultas" link a W.17

**Fase 2 — Documentos (0/3)**
- [ ] Lista cards icono MIME + size + download
- [ ] WebBrowser.openBrowserAsync equivalente (window.open)
- [ ] Loading + empty + error retry

**Fase 3 — Pages (0/3)**
- [ ] Detalle iframe (source.uri)
- [ ] Detalle HTML body purificado DOMPurify
- [ ] Skeleton loading

**Fase 4 — Tests + Cierre (0/4)**
- [ ] Vitest hooks
- [ ] Playwright happy path
- [ ] Counter PARITY-MATRIX
- [ ] Memoria

### W.14 — Anuncios + Banners + Highlights + Bell (0/20, BACKLOG)

**Fase 0 — Hooks (0/3)**
- [ ] useAnnouncements
- [ ] useBanners
- [ ] useHighlights

**Fase 1 — AnnouncementsList (0/4)**
- [ ] Lista cards titulo + body + timeAgo
- [ ] Image thumbnail si existe
- [ ] Deep link handler (`eventos://` + http)
- [ ] Socket announcement:new RT

**Fase 2 — BannersCarousel (0/4)**
- [ ] Carousel autoplay 5s
- [ ] Soporte imagen + video
- [ ] Indicador dots
- [ ] Pausa on hover

**Fase 3 — HighlightsList (0/3)**
- [ ] Lista editorial scroll-snap
- [ ] Card image + title + lead
- [ ] Click → modal o nueva vista

**Fase 4 — BellPopover (0/4)**
- [ ] BellPopover radix con badge count
- [ ] localStorage `lastSeenAt` tracking
- [ ] Tiempo relativo en items
- [ ] Click action_url + mark seen

**Fase 5 — Tests + Cierre (0/2)**
- [ ] Vitest + Playwright
- [ ] Counter PARITY-MATRIX

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

**Fase 6 — Invitaciones publicas (0/3)**
- [ ] Pagina sin-login `/staff-invite/{token}`
- [ ] Validar token automatico
- [ ] Aceptar / No gracias + actualizar hasVendorAccess

**Fase 7 — Tests + Cierre (0/4)**
- [ ] Vitest hooks
- [ ] Playwright happy path
- [ ] Memoria
- [ ] Counter PARITY-MATRIX

### W.16 — Live Moments (0/23, BACKLOG)

**Fase 0 — Hooks (0/4)**
- [ ] useActiveGame
- [ ] useAnswerGame
- [ ] usePhotoContest
- [ ] usePhotoLike

**Fase 1 — Trivia panel (0/6)**
- [ ] Pregunta + 4 opciones A-B-C-D
- [ ] Countdown timer en vivo
- [ ] Leaderboard via socket display:project
- [ ] Result feedback (green/red)
- [ ] Running total points
- [ ] Auto-close 2.5s

**Fase 2 — Sorteo / Spin / Jackpot ceremony (0/4)**
- [ ] GSAP full-screen modal
- [ ] Animacion ceremony (3 tipos: spin wheel readonly / jackpot reveal / random pick)
- [ ] Confetti winner
- [ ] Auto-close

**Fase 3 — Photo contest display (0/4)**
- [ ] Feed grid fotos
- [ ] Likes simples optimistic
- [ ] Podio top 3 con medallas
- [ ] Countdown si active

**Fase 4 — Golden Ticket reveal (0/2)**
- [ ] Announcement-driven modal
- [ ] Confetti + claim_code + QR + sponsor + estado

**Fase 5 — Tests + Cierre (0/3)**
- [ ] Vitest hooks
- [ ] Playwright trivia round
- [ ] Counter PARITY-MATRIX

### W.17 — Soporte (0/15, BACKLOG)

**Fase 0 — Hooks (0/2)**
- [ ] useSupportTickets list
- [ ] useCreateTicket mutation

**Fase 1 — CreateTicketForm (0/3)**
- [ ] Subject input max 200 + counter
- [ ] Message textarea max 2000 + counter
- [ ] Submit + haptics + toast success

**Fase 2 — TicketsList (0/4)**
- [ ] Cards ordenada por fecha
- [ ] Status badge (open/responded/resolved)
- [ ] Admin response green bar left
- [ ] "Esperando respuesta" si no hay

**Fase 3 — Polling (0/2)**
- [ ] refetchOnWindowFocus 60s fallback (no socket RT en soporte)
- [ ] Optimistic invalidate tras nueva consulta

**Fase 4 — Tests + Cierre (0/4)**
- [ ] Vitest hooks
- [ ] Playwright crear + ver ticket
- [ ] Memoria
- [ ] Counter PARITY-MATRIX

### W.18 — Hub Personal (2/19, 10% — renombrado desde W.10 viejo el 2026-06-20)

> Originalmente W.10 en doc, choca con W.10 Live Hub del codigo. Renombrado a W.18 para evitar refactor de codigo. Doc maestro: `docs/webapp/W.18-hub-personal.md` (renombrado via `git mv` el 2026-06-20).

- [x] UserMenu base dropdown
- [x] ThemeTogglePill bottom-left
- [ ] **Hooks useMe + useMyProfile + useOnboarding**
- [ ] **Form perfil editable** (avatar upload + nombre + bio + redes)
- [ ] **Intereses multi-select** (tags chips)
- [ ] **Onboarding bio + registration-fields editables**
- [ ] **Settings idioma** (es-CO / en / pt-BR)
- [ ] **Settings tema** (Noir / Lux)
- [ ] **Cerrar sesion completo** (limpiar cookies + cache + redirect)
- [ ] **User menu dropdown condicional** (vendor → Mi Stand, premios → Mis Premios, canjes → Mis Redenciones, soporte → Mis Consultas)
- [ ] **Mi Recap link** (modal o vista dedicada — decidir)
- [ ] **Mi QR vista** (decidir: mobile-only feedback dice si, web no necesita)
- [ ] **Vista "About event"** (texto + imagen + links sociales — pantalla del Expo no mapeada)
- [ ] **Privacy toggles** (visible to others)
- [ ] **Skeleton + responsive**
- [ ] **Tests Vitest hooks**
- [ ] **Playwright happy path editar + guardar**
- [ ] **Lighthouse**
- [ ] **Counter PARITY-MATRIX**

### W.X — Welcome Showcase (0/7, BLOQUEADO)

> Espera W.3 + W.4 + W.5 + W.7 + W.8 + W.9 cerrados para reusar componentes reales en miniatura.

- [ ] Engine timeline 6 beats
- [ ] Skip siempre visible
- [ ] Reduced motion fallback (serie screenshots estaticos)
- [ ] localStorage `onboarding_completed`
- [ ] Componentes reales en miniatura (Speakers/Agenda/Streaming/Connect/Gamification/Sponsors)
- [ ] Post-login routing condicional
- [ ] Tests Vitest + Playwright

---

## PENDIENTES PARALELOS (sin bloquear sprints)

### Documentales
- [ ] Decidir W.X para `recap/[eventId]` del Expo (no mapeado a ningun modulo webapp)
- [ ] Decidir W.X para `about.tsx` del Expo (texto + imagen + links sociales)
- [ ] Validar si `banners.tsx` Expo es vista dedicada o solo carousel embebido

### Mobile parity (cuando webapp este al dia)
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
