# Pendientes — Webapp (Bancolombia)

> Vista operativa unica. Todo lo faltante para vender webapp standalone — desde tu cama, transporte o cualquier device sin abrir otros docs.
>
> **Generado:** 2026-05-21 (auditoria de parity)
> **Fuente de detalle:** `docs/webapp/PARITY-MATRIX.md` (cruce Expo↔Webapp↔Backend, 117/117 endpoints backend OK)
> **Detalle por modulo:** `docs/webapp/W.X-*.md`
>
> Items con [x] estan hechos. [ ] = pendiente.
> Marcar items completados directo aqui al cerrar cada sesion.

---

## RESUMEN GLOBAL

| Modulo | Counter | Estado |
|---|---|---|
| W.0 Spatial UI base | 20/24 | 83% — en proceso |
| W.1 Setup + Auth + UI Foundation | 102/107 | 95% — en proceso |
| W.1B Backend magic link | 10/10 | **CERRADO** |
| W.2 Home | 10/20 | 50% — en proceso |
| W.3 Agenda | 24/30 | 80% — en proceso |
| W.4 Streaming | 83/111 | 75% — en proceso |
| W.5 Speakers | 0/35 | 0% — backlog |
| W.6 Social Wall | 0/40 | 0% — backlog (Networking confundido aqui — ver W.8) |
| W.7 Sponsors | 0/23 | 0% — backlog |
| W.8 Networking | ~15/25 | 60% — en proceso (lo "social" implementado realmente es W.8) |
| W.9 Engagement | 0/35 | 0% — backlog |
| W.10 Hub Personal | 2/19 | 10% — backlog |
| W.11 Sockets RT | 8/42 | 20% — usado parcial en W.4 |
| W.12 Polish + E2E + PWA | 0/43 | 0% — cierre Fase 1 |
| W.13 FAQ + Docs + Pages | 0/17 | 0% — backlog |
| W.14 Anuncios + Bell | 0/20 | 0% — backlog |
| W.15 Vendor Dashboard | 0/35 | **OPCIONAL** Fase 1 |
| W.16 Live Moments | 0/23 | 0% — backlog |
| W.17 Soporte | 0/15 | 0% — backlog |
| W.X Welcome Showcase | 0/7 | **BLOQUEADO** |
| **TOTAL** | **274/691** | **40%** |

---

## QUE SIGUE (1 sola tarea concreta)

- [ ] **Sprint 1 / Item 1 — W.8 AlertDialog DaVinci en boton Bloquear** (~30 min)
  - Archivo: `eventos-web/src/components/app/social/AttendeeProfilePanel.tsx`
  - Reemplazar `window.confirm` por componente `<AlertDialog>` de shadcn
  - Estilo DaVinci (tokens del proyecto, sin emojis)

---

## SPRINTS PROPUESTOS (orden recomendado)

### Sprint 1 — Correcciones Tier 1 (~5-6h, 1 sesion) — 0/6
> Items que NO requieren diseño nuevo. Programar sobre lo existente.

- [ ] W.8 — Reemplazar `window.confirm` Bloquear por AlertDialog DaVinci
- [ ] W.8 — Skeleton mas amigable en AttendeeProfilePanel (bio + intereses + sesiones placeholder, no 3 lineas grises)
- [ ] W.8 — Playwright E2E happy path (abrir perfil → conectar → solicitud enviada)
- [ ] W.6 — Filtros tabs Recientes / Mas likes / Mis posts (UI ya existe, falta logica orden client-side)
- [ ] W.3 — Bulk .ics download (todas las favoritas en un solo archivo)
- [ ] W.0 — Wire modulos top-level a sidebar (hoy solo W.2/W.3 navegan, faltan W.5-W.17)

### Sprint 2 — Correcciones Tier 2 (~8-10h, 1-2 sesiones) — 0/7
> Refs internas (patrones ya establecidos en el proyecto), poco diseño nuevo.

- [ ] W.8 — Bloqueados list (reusa AttendeeCard + boton Desbloquear)
- [ ] W.8 — Mi perfil editable (form avatar + bio + intereses + redes)
- [ ] W.8 — Filtro role dropdown (attendee/speaker/sponsor)
- [ ] W.3 — Lifecycle badges ORIGINAL / AJUSTADA / CANCELADA
- [ ] W.4 — Replay detection + auto-rating modal post-stream
- [ ] W.2 — Sponsors logo band en home (strip discreto, mientras W.7 no este)
- [ ] W.2 — Anuncios mini badge en home (punto rojo + count)

### Sprint 3 — W.5 Speakers (~5h, 1 sesion DaVinci) — 0/35
### Sprint 4 — W.7 Sponsors (~7h, 1-2 sesiones DaVinci) — 0/23
### Sprint 5 — W.9 Engagement (~10h, 2 sesiones) — 0/35
### Sprint 6 — W.10 Hub + W.14 Anuncios+Bell + W.17 Soporte (~12h, 2-3 sesiones) — 0/54
### Sprint 7 — W.16 Live Moments (~6h, 1-2 sesiones) — 0/23
### Sprint 8 — W.11 Sockets RT consolidacion + W.12 Polish/PWA/E2E (~14h, 2-3 sesiones) — 0/85

**Total estimado para webapp standalone vendible:** ~60-70h sin W.15 vendor (8-10 sesiones DaVinci)

---

## BACKLOG GRANULAR — TODO desglosado

### W.0 — Spatial UI base (20/24, 83%)

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
- [ ] **Modulos navegables top-level desde sidebar** (W.5-W.17 sin handlers; hoy solo W.2/W.3)
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

### W.3 — Agenda (24/30, 80%)

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
- [ ] **Bulk .ics download** (todas mis favoritas un solo archivo)
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

### W.5 — Speakers (0/35, BACKLOG)

> **Demo HTML aprobado 2026-05-08.** Implementacion lista para arrancar.
> Espejo Expo: sin tracks/featured/keynote flags DB (todo derivado), click sesion → /agenda?highlight=X

**Fase 0 — Hooks (0/3)**
- [ ] `useSpeakers(eventId)` lista
- [ ] `useSpeaker(speakerId)` detalle
- [ ] `useSpeakerRating(speakerId)` rate + my-ratings

**Fase 1 — Featured derivation (0/3)**
- [ ] Algoritmo featured (keynotes o top sessions, no flag DB)
- [ ] Fallback top 5 by session_count si no hay keynote
- [ ] Carousel breathing auto-rotate

**Fase 2 — Lista (0/4)**
- [ ] Lista alfabetica 1-col mobile / 2-col desktop
- [ ] SpeakerListItem (photo 56x56 + nombre + rol + session count badge)
- [ ] Search debounce 400ms
- [ ] Empty state

**Fase 3 — DetailPanel (0/7)**
- [ ] DetailPanel slide-in 320-520px 480ms
- [ ] Hero foto 4:5 XL
- [ ] Nombre + job + company
- [ ] Bio card
- [ ] Sesiones grid (time + type badge + title + location)
- [ ] Click sesion → /agenda?highlight={id}
- [ ] LinkedIn button condicional

**Fase 4 — Rating (0/4)**
- [ ] Rating UI stars interactivo (1-5)
- [ ] Comment 280 chars textarea
- [ ] UNIQUE constraint check pre-submit
- [ ] Estado "Calificar" vs "Evaluado" post-submit

**Fase 5 — Deep link + viewport (0/4)**
- [ ] Deep link `?id=X` auto-open detail
- [ ] Agenda `?highlight=sId` scroll + pulse gold
- [ ] SSR fetcher
- [ ] 3 viewports (desktop / tablet / mobile)

**Fase 6 — Tests (0/3)**
- [ ] Skeleton
- [ ] Vitest hooks + featured derivation
- [ ] Playwright happy path

**Fase 7 — Cierre (0/7)**
- [ ] Lighthouse Performance >=85
- [ ] Lighthouse Accessibility >=95
- [ ] Tests verdes
- [ ] Memoria actualizada
- [ ] PARITY-MATRIX seccion W.5 a 100%
- [ ] Detalle commit DaVinci
- [ ] Validar device real

### W.6 — Social Wall (0/40, BACKLOG — feed editorial parcial vive en W.8)

> NOTA: Lo "social" implementado (feed + composer + posts + comentarios) tecnicamente es W.6 Wall, pero esta integrado en lo que llamamos "Social Networking" junto con W.8. Listar como pendientes los items Wall NO implementados.

**Fase 0 — Hooks (0/3)**
- [ ] useSocialFeed paginated
- [ ] usePostComments lazy
- [ ] useCreatePost mutation optimistic

**Fase 1 — Feed (0/4)**
- [x] PostCard render
- [x] InlineComments expandible
- [ ] Paginacion page-based (backend usa `?page=`)
- [ ] Empty "Aun no hay publicaciones"

**Fase 2 — Like + Comments (0/5)**
- [x] Heart optimistic setQueryData
- [x] POST revert on fail
- [x] Animacion heart Framer Motion
- [x] Click "X comentarios" expande sub-thread
- [x] Input crear comentario inline

**Fase 3 — Crear post (0/4)**
- [x] PostComposer textarea max 500
- [ ] **Imagen upload** preview antes enviar
- [x] Optimistic post aparece estado "enviando"
- [ ] Listener `wall:post` deduplica propio via socket

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

**Fase 7 — Filtros (0/2)**
- [ ] **Tabs Recientes / Mas likes / Mis posts**
- [ ] URL state shareable

**Fase 8 — Tests (0/3)**
- [ ] Vitest optimistic like + dedup
- [ ] Playwright crear + like + comentar cross-tab
- [ ] Playwright post fallido + retry

### W.7 — Sponsors (0/23, BACKLOG)

**Fase 0 — Hooks (0/3)**
- [ ] useSponsors lista
- [ ] useSponsorFavorite toggle
- [ ] useSponsorContact submit

**Fase 1 — Brand Wall (0/5)**
- [ ] Grid agrupado por tier (platinum 2c, gold 3c, silver/bronze/media 4c)
- [ ] Shuffle animation 7s (si no scrolling + sin search)
- [ ] Stagger reveal on mount
- [ ] Search debounce 350ms por nombre + descripcion
- [ ] CardPressable scale animation

**Fase 2 — Brand Profile (0/5)**
- [ ] Hero logo XL + nombre + descripcion + tier badge
- [ ] Tab Acerca (descripcion completa)
- [ ] Tab Servicios (chips multiselect)
- [ ] Tab Sesiones (cards time + type + title + location)
- [ ] Tab Contactar (form)

**Fase 3 — Favorite (0/3)**
- [ ] Heart toggle optimistic
- [ ] Animation spring
- [ ] Lista favoritos en Mi Hub

**Fase 4 — Contact + Tracking (0/2)**
- [ ] Contact form servicios + textarea mensaje
- [ ] Tracking view fire-and-forget on profile open

**Fase 5 — Trivia integration (0/3)**
- [ ] Trivia modal auto-trigger on visitStand
- [ ] Pregunta + 4 opciones + countdown + result
- [ ] Auto-close 2.5s + +points feedback

**Fase 6 — Tests (0/2)**
- [ ] Vitest hooks
- [ ] Playwright happy path

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
- [ ] **Reemplazar window.confirm Bloquear por AlertDialog DaVinci**
- [ ] **Skeleton mejor AttendeeProfilePanel** (bio + intereses + sesiones placeholder)
- [ ] **Bloqueados list** (vista + boton desbloquear)
- [ ] **Mi perfil editable** (form avatar + bio + intereses multi-select + redes)
- [ ] **Filtro role dropdown** (attendee/speaker/sponsor/etc) en directorio
- [ ] **RT listeners** `networking:notify` (request_received/accepted toast + invalidate)
- [ ] **Gap C Sugeridos cards grandes** mover panel der al centro Personas
- [ ] **Playwright E2E** abrir perfil → conectar → confirmar solicitud
- [ ] **Tracking analytics** social.profile_opened + connection_sent + contact_method_clicked
- [ ] **Cierre commit + memoria + counter PARITY-MATRIX**

### W.9 — Engagement (encuestas + leaderboard + logros + passport + rewards) (0/35, BACKLOG)

**Fase 0 — Hooks (0/4)**
- [ ] useSurveys
- [ ] useLeaderboard
- [ ] useMyPoints
- [ ] useGamificationConfig

**Fase 1 — Encuestas / Surveys (0/4)**
- [ ] SurveysList activas/cerradas
- [ ] Click opcion → POST /polls/{id}/vote
- [ ] Resultados barras % por opcion
- [ ] Socket poll:new / poll:closed listeners

**Fase 2 — Leaderboard (0/4)**
- [ ] LeaderboardTable top 50
- [ ] Sticky bar my_position + my_points
- [ ] Si my_position > 50 → "Estas en posicion #234"
- [ ] Share rank social

**Fase 3 — Mis Logros (0/3)**
- [ ] Grid actions completed (NO badges)
- [ ] Grayscale si completed=false
- [ ] Modal detalle action

**Fase 4 — Passport stamps (0/4)**
- [ ] Passport libreta visual grid stamps
- [ ] Cada stamp: icono sponsor + nombre + fecha
- [ ] Solo VIEW (earning requiere QR fisico mobile)
- [ ] Socket data:invalidate{entity:passport} → animacion + toast

**Fase 5 — Rewards (0/5)**
- [ ] RewardsCatalog grid
- [ ] Reward card imagen + nombre + costo
- [ ] Modal confirm redeem
- [ ] Display redeemed_at + codigo + instrucciones
- [ ] Tab Mis Premios (golden_ticket + sorteo + canje mezclados)

**Fase 6 — Golden Ticket reveal (0/2)**
- [ ] Modal Golden Ticket (overline "Ganador" / "Reclamado", nombre premio, sponsor, claim_code XL, QR, descripcion)
- [ ] Estado pending vs confirmed

**Fase 7 — Toast +X puntos diff (0/2)**
- [ ] Hook useTrackPointsDiff guarda previousTotal
- [ ] Delta > 0 → toast "+X puntos" + confetti si >=10

**Fase 8 — Tests (0/3)**
- [ ] Vitest diff calc multiples invalidates
- [ ] Playwright votar survey + leaderboard + logros
- [ ] Playwright poll cerrada solo resultados

**Fase 9 — Cierre (0/4)**
- [ ] 3 viewports
- [ ] Lighthouse
- [ ] Memoria
- [ ] Counter PARITY-MATRIX

### W.10 — Hub Personal (2/19, 10%)

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
