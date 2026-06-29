# Completado — EventOS

> Historial de todo lo implementado, organizado por area.
> Consultar para contexto historico. El dia a dia es `PENDIENTES.md`.
> **NOTA:** Los numeros BUG-XXX en este archivo son historicos. La numeracion fue reorganizada el 2026-04-23.
> Fuente de verdad para bugs: `docs/BUG-LOG.md` (numeracion secuencial BUG-001 a BUG-232).
> Actualizado: 2026-04-23

---

## Webapp (Next.js) — fase activa

| Sesion | Feature | Fecha | Notas |
|--------|---------|-------|-------|
| W.0 | Spatial Shell + CanvasCard + SidebarPill + ThemeToggle | 2026-05-04 | Fix tablet H 2026-05-05 (canvas sin aspect-ratio rigido) |
| W.2 | Home cinematic con 3 estados (PreState/LiveState/EndedState) | 2026-05-04 | Wordmark accent, mute por estado |
| W.3 | **Agenda completa + wired backend** | **2026-05-06** | DayStrip + ChipFilters + SessionCard + DetailPanel + RatingModal + AttendeesPop. POST favoritos optimistic + revert, .ics download, rating + my-ratings, Asistencia oculta hasta W.8. Doc maestro `BACKEND-API-MAP.md` (805 lineas, todos los endpoints + decisiones cerradas). |
| W.4 | Streaming player + Q&A + chat + polls + layout spatial | 2026-05-09 | StreamPlayer multiformat, hooks useChat/useQA/useSessionDetail, socket dedup tempId, pinned message socket-driven. Trivia delegada a W.16. |
| W.5 | **Speakers modulo + agenda highlight extension** | 2026-05-09 → cerrado 2026-06-20 | 8 componentes (SpeakersView + BreathingCarousel + FeaturedCard + SpeakerListItem + SpeakerDetailPanel + SpeakersRatingModal + SpeakersHeader + Stars). Race protection panel swap. Deep link `?id=X`. Click sesion → /agenda?highlight. UNIQUE 409 silencioso. 27 vitest + 13 Playwright. **CERRADO 94%** 2026-06-20 (faltan Lighthouse + device fisico). |
| W.6 | **Social Wall — feed + composer + likes + comments + tabs filtros** | 2026-05-13 → 2026-06-20 | Feed paginated SSR + Composer max 500 + foto upload + heart optimistic + comments inline + MisPostsView filter is_mine. **Tabs filtros (Recientes/Mas likes/Mis posts)** agregadas 2026-06-20 con helper pure `sortAndFilterFeed` + tie-break created_at. 3 empty states por contexto. 6 tests nuevos. **45%** (faltan Stories + Photo Contest + Hashtags). |
| W.8 | **Social Networking — perfil in-slot + ConfirmPop + Bloqueados + skeleton** | 2026-05-13 → 2026-06-20 | Directorio paginado + AttendeeCard + perfil in-slot visionOS (panel der MUTA, 4 estados relacion) + compose inline + vCard/WhatsApp/Email + Solicitudes Recibidas/Enviadas + Aceptar/Ignorar/Bloquear. **2026-06-20:** ConfirmPop DaVinci reemplaza `window.confirm` (componente reusable patron `rating-pop`, NO shadcn AlertDialog), Bloqueados list (tercera tab en Solicitudes con BlockedRow + handler optimistic), skeleton v2 honesto (no sobrepromete secciones condicionales). 49+2 tests vitest. **60%** (faltan mi-perfil editable + filtro role). |
| W.10 | **Live Hub — browsing transmisiones (modulo NUEVO)** | 2026-05-10 → cerrado 2026-06-20 | LiveHubView + LiveHero + LiveSideCard + UpcomingCard. SSR `fetchHappeningNow` + `fetchUpNext`. 4 estados (default 2+N, 1 solo, 0 lives + N upcoming, empty). Slate Mono tokens globales + Lux overrides. 8 escenarios Playwright. Validacion visual con `LiveHubDemoSeeder` (3 lives + 6 upcoming + 4 past). **CERRADO 100%** 2026-06-20 (consenso, anti-regadero — sin doc maestro). |
| Docs | **W.10 viejo Hub Personal → W.18 (rename)** | 2026-06-20 | `git mv docs/webapp/W.10-notificaciones-perfil.md → W.18-hub-personal.md`. Refs actualizadas en `_index.md`, `PARITY-MATRIX.md`, `BACKEND-API-MAP.md`, `PLAN.md` (agregado row W.10 Live Hub nuevo + W.18 Hub Personal en tabla modulos y estimacion). Conflicto numeracion resuelto. |
| Hygiene | **Fix vitest suite 194→202/202 verde** | 2026-06-20 | Node 25.8.1 expone `globalThis.localStorage` experimental sin metodos. Fix `tests/setup.ts`: stub propio Map-based + `Object.defineProperty` sobre globalThis + window. Una sesion, un archivo, un fix. Desbloquea cierre formal de modulos. |
| Re-auditoria | **Counters reales doc vs codigo** | 2026-06-20 | Sesion completa de re-recount despues de 1 mes pausa. W.5: 0%→94%, W.6: 0%→45%, W.10 Live Hub nuevo 100%, W.18 renombrado. Total global 40%→48.7% (344/707). PARITY-MATRIX + PENDIENTES-WEBAPP sincronizados con realidad. |
| W.3 | **Bulk .ics download (Mi Agenda completa)** | 2026-06-21 | Generador puro `lib/ics.ts` RFC 5545 client-side (VCALENDAR + N VEVENT, UID determinista, DTSTAMP UTC, escape comas/semicolons/backslash). Reemplaza handler fake `handleTodas` de AgendaView. Boton "Todas" del header ahora visible cuando `countMine > 0` (favoritas en CUALQUIER dia, no solo el visible). Filename `mi-agenda-{event.slug}.ics`. Sin endpoint backend nuevo: el snapshot AgendaData ya tiene todo. Pulido UX: toast pre-download "Preparando tu agenda..." + boton disabled ~700ms (anti doble-click + feedback inmediato en conexion lenta). +16 tests vitest (218/218 verde). |
| W.0 | **Sidebar wire + smoke + brand cleanup — Sprint 1 CERRADO 9/9** | 2026-06-21 | Smoke 5/5 items navegan (home, agenda, live, speakers, social). Patron `available: boolean` ya estaba bien: `/sponsors` disabled con tooltip "proximamente" + opacidad 55%. Logica: conforme aparece cada modulo, `available: false` → `true` en `SidebarPill.tsx` (no items fantasma). Bonus: quitado el brand letter inicial (`event.name?.charAt(0)`) que generaba ruido visual tipo debug en eventos sin logo elaborado — si el cliente quiere identidad en sidebar, lo correcto es generar keyvisual real. Sidebar arranca ahora directo en Home. **Sprint 1 CERRADO** (9/9): item 1 hygiene + items 2-7 cierres formales W.5/W.10/W.6/W.8 + items 8-9 bulk .ics + sidebar wire. |
| Theme | **Reemplazo next-themes con provider propio (fix Next 16 + React 19)** | 2026-06-21 | Bug abierto upstream `next-themes 0.4.6` issues #385/#387: "Encountered a script tag while rendering React component". Solucion: provider propio 60 lineas en `ThemeProvider.tsx` + script blocking anti-FOUC inline en `<head>` del LocaleLayout server component. Cambiados 4 consumers (`ThemeToggle`, `ThemeTogglePill`, `EventThemeProvider`, `sonner`). API minima `useTheme()` retorna `{theme, resolvedTheme, setTheme}`. next-themes queda en package.json sin desinstalar (puede haber uso indirecto). |
| W.7 | **Sponsors — modulo CERRADO 23/23 Sprint 2.A** | 2026-06-21 | Wall espejo Expo (Platinum cards + Gold cards + Compact logos round) con living shuffle 7s via **framer-motion `layout` + spring damping 28 stiffness 120** (equivalente exacto al Reanimated Expo). DetailPanel: Hero + Sessions + Trivia (multi-pregunta + bonus pts + pantalla resumen `+X puntos ganados` + autoclose 2.5s — espejo Expo `TriviaModal`) + ContactForm (chips servicios + textarea + 409 ALREADY_CONTACTED) + Actions (Sitio web + Email). Toast `+puntos por visitar` via lumina (top-center) condicional a `points_awarded > 0` (no toast falso si ya visitaste). **Gap backend cerrado**: `SponsorResource` ahora expone `trivia_enabled`/`passport_enabled`/`visit_points` + `GamificationController::visitStand` devuelve `points_awarded` (distingue si tryAward otorgo de verdad). Esc + click fuera cierran detail. Stagger reveal del detail (hero→sesiones→trivia→contact→actions, 60ms delay). Elevaciones: Lux multi-layer shadows + Noir shadow base oscura (no halo accent — rompia con primary_color rojo/coral). Colores success/error neutrales `rgba(80,200,120)`/`rgba(255,100,100)` en vez de tokens del cliente. **CIERRE FORMAL**: Skeleton loading SSR con shimmer + 3-tier phantom cards (`loading.tsx` segmento). Tooltip radix custom en compact logos (reemplaza title HTML nativo). Heart pulido: sin circle background + animacion framer-motion (`AnimatePresence` + spring damping 10 stiffness 380 mass 0.6, fresh cada toggle) — reemplaza keyframes CSS vanilla que se veian cortados. **E2E Playwright 12/12 verde** (auth gate, SSR wall 3 tiers, click Platinum, Esc, contact 200/409, sin trivia/services, Notion minimo, search filter, sin resultados, click fuera). Lighthouse prod build: **Accessibility 98 ✅** (target ≥95), CLS 0 ✅ (sin layout shift del shuffle). Performance autenticado → batch QA final cross-modulos (Lighthouse standalone redirige a login, comun a W.3/W.5/W.10/W.6/W.8). 14 tests vitest + 12 E2E = 244 tests verde total. |
| Backend | **SponsorResource + GamificationController visit-stand** | 2026-06-21 | SponsorResource expone 3 campos nuevos: `trivia_enabled`, `passport_enabled`, `visit_points` (necesarios para que el cliente sepa badges + condiciones sin ir a `visit-stand`). `GamificationController::visitStand` ahora distingue si `PointsService::tryAward` efectivamente otorgo (return `points_awarded: int`, 0 si idempotente/gamification off/role no permitido). Sin estos cambios la UI mostraba toast "+X puntos" cada click aunque el usuario ya tuviera los puntos. |
| W.14 | **Anuncios Fase A — lista + BellPopover + deep links (10/20 Sprint 2.C)** | 2026-06-29 | Ruta `/anuncios` SSR + `AnnouncementsView` lista vertical espejo Expo `anuncios.tsx` (cards con image/title/body/timeAgo/dot/chevron, expandibles inline si tienen `action_url`). **`BellPopover`** reemplaza el `<span>` placeholder del bell en `SidebarPill` (W.0): preview de 5 mas recientes + footer "Ver todos" → `/anuncios`. Badge unread via `localStorage:eventos:announcements:lastSeenAt:{eventId}` con sync cross-tab via `storage` event. Lazy init sin setState-in-effect. **Helper puro `parseActionUrl()`** con 13 mappings: `eventos://gamification/*` → `/desafio` (golden ticket case), `agenda/{id}` → `/agenda?highlight={id}`, `speakers/{id}` → `/speakers?id={id}`, `sponsors/social/wall/anuncios` directos; backlog W.13/W.15/W.17 (`my-support/faq/recap/join-team/stand`) caen a `internal-future` con toast amable; `http(s)` → `window.open('_blank')`; esquemas desconocidos → `console.warn` sin romper UI. **End-to-end golden ticket verificado**: `GameController:680` ya crea announcement privado con `action_url: eventos://gamification/rewards` cuando alguien gana un live game; webapp lo lista + click navega a `/desafio` sin coordinacion nueva backend. **309/309 vitest verde** (+39 nuevos: 23 announcementDeeplink + 16 announcementsUnread). **E2E `anuncios.spec.ts` 10/10 verde** (15s, serial mode): auth gate / SSR sorted / 4 tipos action_url (interna/externa/null/future) / empty state / Bell badge unread cae a 0 / footer Ver todos / card popover navega + cierra / sidebar item. Fixture + 1 handler nuevo en mockBackend. **Decisiones:** sin tabs "Todos/No leidos" (cosmeticas, backend no persiste read_at); sin modal/panel detail (anuncios entran completos en card); BellPopover divergencia intencional vs Expo (mobile bottom tabs vs sidebar desktop). **Pendiente Fase B (~2h):** banners + highlights + RT socket W.11. **Pendiente Web Push:** W.12 Polish con manifest+SW+VAPID — backend ya tiene 8 tipos de push enum documentados en memoria para no inventar. |
| W.9 | **Desafio Engagement Hub — CERRADO 35/35 Sprint 2.B** | 2026-06-27 → 2026-06-29 | Hub split layout literal espejo W.7 (wall izq 6 cards apiladas + DetailPanel der). 6 cards: Hero (posicion + puntos + mini ranking top 3), Golden Tickets (lista con cada pending boton individual), Premios preview (2 inline), Tip (puntos para entrar top 10), Retos (4/5 progreso), Pasaporte (2/3 stands). 6 paneles: GoldenTicket reveal (claim_code XL + QR real qrcode.react + RgbRect WAVE_COLORS animado via `@property --dx-rgb-angle`), Ranking (podio escalado #2 #1 #3 + top 50 + my_position siempre visible), Rewards (catalogo con redemptions INLINE — 5 estados CTA: Canjear / Mostrar QR TEAL borderless / Ya canjeado disabled+check / Agotado / Faltan X), Retos (lista completa actions con earned), Pasaporte (grid stamps + completed banner), Rules (5 reglas educativas + tabla puntos). **Sesion 1 (2026-06-27, `32018f1`):** estructura inicial 18/35 + SSR 5 endpoints + sidebar + i18n + colores TEAL fijos del sistema (no `var(--accent)`). **Sesion 2 (2026-06-29, `4238c69`):** shapes backend reales (`/me/prizes` nested, `/my-passport` stands rename, `/rewards.can_redeem` calculado cliente) + 4 proxies Next + lazy fetch + redeem optimistic (modal loading→ready) + haptic helper 5 intensidades + golden tickets flow individual (3 iteraciones: lista repetida rechazada, modal full-screen rechazado, reveal in-panel aceptado) + QR ring fix + focus ring fix + 27 vitest. **Sesion 3 (2026-06-29, `8aab0b4`):** redemptions INLINE en catalogo (espejo Expo `leaderboard.tsx:316-345` — eliminado tab "Mis canjes" por feedback_mirror_feature_completo + feedback_no_repetir_info_en_panel). Helper puros `findActiveRedemption`/`hasConfirmedRedemption`/`orphanActiveRedemptions` con `now` inyectable. `handleShowExistingQR` reabre modal con token EXISTENTE sin pegar POST (cero riesgo cobrar puntos doble). Bloque "Canjes activos sin catalogo" garantiza acceso al QR si reward fue retirado. CSS nuevo: `.dx-btn-show-qr` (TEAL borderless) + `.dx-reward-badge-confirmed` + `.dx-rewards-orphans`. **E2E desafio.spec.ts 11/11 verde** (auth gate, SSR hub, ticket pending reveal, 5 estados CTA inline visibles, Mostrar QR sin POST verificado con spy, Canjear con POST 1 vez, Esc cierra modal, bloque orphans, 3 viewports automatizados desktop 1600/tablet H 1130/mobile 390 sin overflow horizontal). Fixture `desafioFixture` + 7 handlers en mockBackend. Serial mode (8 workers saturan SSR 5-fetch, 13s vs 30s timeout). **270/270 vitest verde** (+11 helpers redemption). Decisiones: encuestas viven en W.4 streaming (in-stream sockets), toast "+X pts via diff" descartado (espejo Expo no lo hace), `claimTicket` attendee-side descartado (vendedor confirma). Validacion visual Lighthouse + device fisico → batch QA final cross-modulos. |

---

## Fase 0 — Setup (2026-03-28)

- [x] Entorno: PHP 8.3, Redis, MySQL 8.4, Node 22, Git, Composer, EAS CLI
- [x] Laravel 11 base: Sanctum, Spatie, Filament, Horizon, Telescope, Sentry. 16 migraciones.
- [x] Expo SDK 55: Router, NativeWind, Reanimated, SecureStore, MMKV, TanStack Query, Zustand, i18n
- [x] Socket.IO: Redis adapter DB 2, auth Sanctum, rooms event/session/chat, health endpoint

## Fase 1 — MVP funcional (2026-03-29 → 2026-04-07)

| Sesion | Feature | Fecha | Tests |
|--------|---------|-------|-------|
| 1.1 | Auth + Roles + QR HMAC + Tracking | 2026-03-29 | 12 |
| 1.2 | Motor modulos dinamicos + cache Redis | 2026-03-28 | 23 |
| 1.3a | Contenido backend: sessions, speakers, pages, announcements, Filament CRUD | 2026-03-28 | — |
| 1.3b | Contenido app: agenda, speakers, pages, anuncios, FlashList + expo-image | 2026-03-28 | — |
| 1.4 | QR check-in + kiosco standalone + Socket.IO real-time | 2026-03-29 | — |
| 1.5 | Leads vendedor: scanner QR, notas, tier, historial, export | 2026-03-30 | — |
| 1.6 | Patrocinadores + stand teams: sponsors=stands, stand_members, lead_edits | 2026-03-30 | — |
| 1.7 | Networking: solicitudes, aceptar/rechazar, contactos, directorio | 2026-03-31 | — |
| 1.8 | Gestion usuarios + bans (motivo, expiracion, Filament) | 2026-03-31 | — |
| 1.9 | Chat real-time por sesion (Socket.IO, Redis) | 2026-04-01 | — |
| 1.10 | Encuestas en vivo (live_poll_questions, 4 tipos, slides) | 2026-04-04 | — |
| 1.11 | Push notifications (Expo Push API, FCM v1) | 2026-04-04 | — |
| 1.12 | Tracks + session types | 2026-04-04 | — |
| 1.13a | Emails automaticos: 11 mailables, BaseEventosMail, EmailService | 2026-04-05 | — |
| 1.13b | SMTP propio por organizacion | 2026-04-05 | — |
| 1.x | Upload imagenes / Cloudflare R2 | 2026-04-05 | — |
| 1.x | Banners: carrusel sponsors en Home | 2026-04-05 | — |
| 1.x-A | Onboarding configurable: backend + app base | 2026-04-05 | — |
| 1.x-B | Onboarding animaciones premium | 2026-04-06 | — |
| 1.14 | Streaming nativo + Mi Agenda | 2026-04-06 | — |
| 1.15 | Q&A en vivo + moderacion Filament | 2026-04-06 | — |
| 1.16 | Evaluacion sesiones (ratings, crystals) | 2026-04-06 | — |
| fix | YouTube iframe + logica separada por tipo URL | 2026-04-07 | — |
| 1.17 | Photobooth / Memorias (galeria moderada, likes) | 2026-04-07 | — |
| 1.18 | Matchmaking por intereses (overlap, sugerencias) | 2026-04-07 | — |
| 1.19 | Social wall (feed + posts + comments + likes) | 2026-04-07 | — |
| 1.20 | Gamification 13 acciones + leaderboard | 2026-04-07 | — |
| 1.21 | Passport stamps por lead scan | 2026-04-07 | — |
| 1.22 | Registro personalizable + import/export + approval + deep link + consent | 2026-04-07 | — |

## Pulido funcional + Stress testing (2026-04-07)

- [x] Tab vendedor unificado con presencial
- [x] Modulos por rol
- [x] k6 + artillery scripts (ejecutado local, pendiente VPS)

## Seguridad (2026-04-07 → 2026-04-12)

| ID | Fix | Tests |
|----|-----|-------|
| SEC-1.1 | Socket room authorization (join valida eventId, session via internal+Redis) | 6 |
| SEC-1.2 | HTMLPurifier trait en 8 modelos | 13 |
| SEC-1.3 | Token expiration 7d + POST /auth/refresh + auto-refresh interceptor | 7 |
| SEC-2.1 | SecurityHeaders middleware (X-Frame, CSP, HSTS, Referrer, Permissions) | 6 |
| SEC-2.2 | CORS hardening (methods/headers restringidos) | — |
| SEC-2.3 | HTTPS enforcement en app (throw si no HTTPS en prod) | — |
| SEC-2.4 | php artisan security:check | 4 |
| SEC-2.5 | .env.production.example + SESSION_SECURE_COOKIE | — |
| SEC-3.3 | Account lockout (5 intentos → 30 min, HTTP 423) | 6 |
| SEC-3.4 | Socket rate limiting Redis (max 5 conexiones/user) | — |
| SEC-3.5 | 5 FormRequests user-facing | — |
| SEC-3b.1 | Token register 30d → config (sanctum.expiration) | 2026-04-12 |
| SEC-3b.3 | Middleware CheckBan server-side en todas rutas API | 2026-04-12 |
| SEC-3b.5 | Ban real-time via socket (/internal/ban/enforce → app) | 2026-04-12 |

Total: 42 security tests, 309 tests backend.

## Real-time + Invalidation (2026-04-09)

- [x] RT sync admin→app: 5 entidades (agenda, speakers, announcements, branding, modules)
- [x] 4 capas: socket events + focusManager refetch + reconnect refetch + staleTime
- [x] Jitter aleatorio (200-1000ms) para evitar thundering herd
- [x] EventObserver + socket branding:updated para cambios de config

## UI/UX Lumina Noir (~98% completado)

### Fundamentos (2026-04-07)
- [x] Design tokens: #0e0e0e fondo, primary_color dinamico, Urbanist + PlusJakartaSans
- [x] ThemeProvider + useBranding + ScreenWrapper anti-flash Android
- [x] FloatingTabBar: liquid glass, sliding bubble spring, bounce on tap, 5 tabs reales
- [x] StatusBar light, SafeArea en todas las pantallas

### Pantallas completadas
- [x] Home: header configurable, hero text/image, HappeningNow crossfade 6s, GamificationHud, ModuleMenu cascade — 2026-04-07
- [x] Agenda: day strip, glass cards, tracks, heart particles, calendar, DaySlide direccional — 2026-04-08
- [x] Speakers: carousel breathing, detail hero, rating crystals diamond, LinkedIn — 2026-04-08
- [x] Streaming: split-screen, YouTube embed, session states, buttons contextuales — 2026-04-08
- [x] Social: Feed+Memorias+Momentos unificado, stories 24h, comments BottomSheet, header blur sticky — 2026-04-09 + 2026-04-11
- [x] Sponsors: Brand Wall grid tiers, living shuffle, Brand Profile, trivia A/B/C/D, contact form — 2026-04-09
- [x] Profile: beam Ocean, edit modal, social links, stats, pull-to-refresh — 2026-04-09
- [x] Encuestas PollSlides: rediseno completo por pregunta (Multiple/Star/OpenText) — 2026-04-10
- [x] Chat sesion: emojis animados, cooldown, Enter=enviar, error handling — 2026-04-10
- [x] Mi QR: tab real, RGB wave pastel, badge digital, fullscreen modal, wallet ready — 2026-04-10
- [x] Gamificacion: HUD teal/cyan, barra segmentada, pantalla unificada, portal cards, premios — 2026-04-10
- [x] Vendedor: tabs presencial, VendorHappeningNow, Mi Stand premium, leads Lumina Noir — 2026-04-10
- [x] Networking: contactos vCard, guardar en telefono, export masivo .vcf — 2026-04-10
- [x] Pending-approval Lumina Noir — 2026-04-12
- [x] Activate-account Lumina Noir — 2026-04-12
- [x] ConnectionError screen reutilizable — 2026-04-12
- [x] Banned screen Lumina Noir — 2026-04-11

### Responsive (2026-04-10)
- [x] Audit completo 360dp (ZTE) + 411dp (Medium Phone): 31 archivos, 12 pantallas SafeArea
- [x] Layout proporcional: skeletons, leads header, login scroll, ModuleMenu, HappeningNow, HomeHero

### Micro-interacciones (2026-04-11)
- [x] ScalePress: tap feedback 0.96 + haptic (ModuleMenu, Speakers, Agenda, Networking)
- [x] Image reveal: transition={300} en 17 archivos expo-image
- [x] ContentFade: opacity 0→1 (400ms) en sponsors, anuncios, speaker detail
- [x] FadeInItem: stagger wave top→bottom
- [x] AnimatedBadge: scale pop en HomeHeader + Networking badges
- [x] Haptics: leaderboard, profile, social, bell
- [x] Screen transitions: slide_from_right (stream mantiene slide_from_bottom)
- [x] Home wave entrance: HomeSkeleton → FadeInItem coordinado

### Transversales
- [x] Skeleton loading en 5+ pantallas
- [x] EmptyState en social feed, memorias, speaker detail
- [x] Iconografia: MaterialCommunityIcons (no emojis) en ModuleMenu
- [x] LuminaToast reemplaza Alert.alert en toda la app
- [x] returnKeyType en 14 archivos (search/next/go/send/done)
- [x] Background #1a1919 → #141414, console.log cleanup

## Onboarding DaVinci (2026-04-11 → 2026-04-12)

- [x] Welcome: 5 floating pills formas unicas, 5 backgrounds configurables (particles, constellation, mesh, bubbles, minimal), primaryColor aplicado
- [x] Auth: login/register animado, ForgotSheet BottomSheet, keyboard handling, stats con FadeInDown
- [x] Login inteligente 2 pasos: POST /auth/check-email → not_found/pending_activation/active → password animado
- [x] Photo: avatar 180px, camara/galeria, upload + authStore update, foto en context
- [x] About: preview card live, cargo/empresa, campos con iconos, scroll natural
- [x] Interests: chips wrap con emoji, haptic, min 3, pending_survey MMKV
- [x] Done: badge IDENTICO a MiQR, QR real funcional, tap fullscreen, confetti (solo registro)
- [x] Gamificacion: AnimatedPts (scale+particulas+color flash), SkipModal BottomSheet, 80pts max
- [x] Auth legacy eliminado (login.tsx, register.tsx, forgot-password.tsx borrados)
- [x] Activate-account redirige a onboarding photo step (flag post_activation_onboarding)

## Onboarding configurable Filament (2026-04-12)

- [x] JSON onboarding_steps_config en events (migration + model + cast array)
- [x] Filament UI: 7 secciones colapsables (Welcome, Auth, Photo, Forms, Survey, Done, Orden)
- [x] Welcome: pills dinamicas, hero image, show_text, title_prefix, textos botones
- [x] Auth: show_title/subtitle/stats, stats dinamicas, hide register link
- [x] FormStep generico: campos dinamicos (text/tel/email/number/url/select/checkbox/textarea)
- [x] Colores master/slave (ColorPicker en Filament, useStepColors helper)
- [x] Steps dinamicos desde step_order + enabled flags
- [x] Real-time: polling 30s pre-login + socket data:invalidate post-login
- [x] Retrocompatibilidad: si config null, fallback a hardcoded

## Campos dinamicos 1.x-E-A (2026-04-12b)

- [x] SelectSheet: BottomSheet radio reutilizable con accent color y haptic
- [x] Checkbox: Switch toggle inline, glass row
- [x] Textarea: TextInput multiline 4 lineas
- [x] Validacion required: borde rojo + toast + skip oculto si required
- [x] PreviewCard live cuando form tiene job_title/company

## Campos dinamicos 1.x-E-B (2026-04-13)

- [x] SearchableSheet: BottomSheet 65% con TextInput busqueda, filtro local, radio select
- [x] CheckboxGroupSheet: BottomSheet multi-select con checkboxes, boton "Confirmar (N)"
- [x] DateTimePicker: @react-native-community/datetimepicker, picker nativo, guarda ISO, muestra formateado es-CO
- [x] FormStep: 3 render cases nuevos (searchable_select, checkbox_group, date)
- [x] onboardingApi.ts: tipos actualizados (3 tipos + preset + preset_options)
- [x] PresetController: GET /presets/{type}, GET /presets/cities/{code}
- [x] config/presets.php: 53 paises, ciudades (9 paises), 20 industrias
- [x] Filament: 11 tipos en selector + campo preset para searchable_select
- [x] OnboardingController: resuelve preset → preset_options al servir config
- [x] OnboardingSeeder: ejemplos de los 3 tipos nuevos
- [x] QA: 7 tests presets + 11 tipos verificados, 309 backend tests passing

## Seguridad SEC-3b completado (2026-04-13)

- [x] SEC-3b.2: index.tsx valida GET /me al startup. 401→clearAuth, 403→banned/pending. Fallback red graceful.
- [x] SEC-3b.4: CheckApproval.php middleware server-side. 403 si no aprobado. Excluido de auth/profile/onboarding.
- [x] Fix: index.tsx maneja 403 (antes solo 401, app podia quedar colgada)
- [x] Fix: lockout counter se resetea cuando lock expira (antes acumulaba intentos para siempre)

## QR dinamico rotativo 1.C3 (2026-04-13)

- [x] Formato d.{attendee_id}.{window}.{signature_32hex} — O(1) validacion, no O(n)
- [x] Ventana 60s, tolerancia 5 ventanas (~180s), clock skew +-1
- [x] GET /me/qr devuelve token dinamico + expires_in (TTL real)
- [x] Checkin valida: estatico primero (backward compat), luego dinamico
- [x] LeadController tambien valida dinamico (fix posterior al QA)
- [x] App: useQrToken refetch 50s, MiQrScreen countdown "QR dinamico · 45s"
- [x] DoneStep: preview actualizado al formato nuevo
- [x] Validacion qr_token relajada min:20 max:100 (formato variable)

## Scanner Lumina Noir (2026-04-13)

- [x] Reescritura completa scanner-stand.tsx (NativeWind → StyleSheet)
- [x] Resultados en BottomSheet (55% success/duplicate, 38% error)
- [x] Lead card con foto, nombre, cargo, empresa, email
- [x] Tier selector inline (hot/warm/cold) en resultado success
- [x] Scan line animada Reanimated, haptic feedback en todos los estados
- [x] ScalePress en todos los botones, SafeArea top+bottom

## Docs reestructurados (2026-04-13)

- [x] EventOS_Roadmap.md: 3217 → 419 lineas (v4.0 slim)
- [x] Original archivado: docs/ROADMAP-HISTORICO-v3.1.md (3144 lineas intactas)
- [x] docs/PENDIENTES.md: reescrito auto-contenido, organizado por area de trabajo
- [x] docs/COMPLETADO.md: nuevo, historial por area
- [x] 3 apendices extraidos: ANALISIS-COMPETITIVO.md, WEB-APP-PLAN.md, WHITE-LABEL.md
- [x] PLAN-TAGS-MODULOS.md: plan arquitectura tags + visibilidad modulos (aprobado, pendiente implementar)

## Lux v2 "The Gallery" — Light Mode completo (2026-04-17)

### Tokens y sistema
- [x] theme-lux.ts: tokens completos (surface, text, icon, shadow, categories, states, glass, skeleton, statusBar, overrides, semantic)
- [x] useTheme() dinamico en ~85 archivos
- [x] Session Types configurables desde Filament (nombre, color, sin hardcoded TYPE_COLORS)

### Pantallas migradas Lux (12 fases)
- [x] Tokens, Tab Bar, StatusBar, Onboarding, Home, Agenda, Session Detail, Speakers, Mi QR, Social, Sponsors, Networking, Leaderboard, About

### Perfil Lux
- [x] StatCard, DataCard, avatar badge, social icons, edit fields, bottom sheet icons — card pattern (#FFFFFF + shadow.sm)

### Back buttons Lux (6 pantallas)
- [x] FAQ, Support, Anuncios, Mi Stand, Leads, Lead Detail — shadow.md flotante

### Cards Lux (5 pantallas)
- [x] Anuncios, Mi Stand (7 cards), Lead Detail (5 cards), My Support — card pattern

### NativeWind → StyleSheet (12 archivos, 173 className)
- [x] ChatPanel, session-chat, QnAPanel, PollPanel, DynamicField, SplashLoader, banners, documentos, encuestas, passport, pages, BannerCarousel
- [x] Pantallas siempre claras (opcion B), solo migracion sintaxis
- [x] session-stream: no tocado (siempre dark)

### Formularios sin accent
- [x] FormStep: accent removido de bordes/labels/checkboxes, solo rojo oscuro (#B91C1C) para errores
- [x] DynamicField: reescrito completo, labels neutros, checkboxes verdes, sin accent
- [x] Switch trackColor: verde semantico (#22C55E) en vez de primaryColor

### Componentes criticos
- [x] LuminaToast: texto textTokens.white (era #FFFFFF invisible en Lux)
- [x] Skeleton: contenedores backgroundSunken en Lux (era surface.low invisible)
- [x] FAQ: chips ink activos, cards/contact/response con shadow.sm
- [x] MyInterests: chips no seleccionados con shadow.sm + borderStrong
- [x] DaySlide agenda: opacity removida (evita flash shadow Android)

### Dark Islands (siempre Noir)
- [x] Gamificacion/Desafio: pantalla completa forzada Noir (noirSurface/noirText importados)
- [x] RGB_BORDER: 4px → 6px
- [x] HappeningNow SessionCard: sin shadow.lg (evita flash en crossfade)
- [x] GamificationHud: sin shadow.lg (idem)
- [x] Componentes dark island documentados: HappeningNow, VendorHappeningNow, GamificationHud, VendorGamificationHud, Mi QR badge, MomentosViewer, PhotoViewer, QR modal, Scanner, Streaming

## Moderacion chat completa (2026-04-12)

- [x] Ban real-time via socket (/internal/ban/enforce → ban:enforced → app /banned)
- [x] Middleware CheckBan en todas rutas API (excepto auth/me, auth/logout)
- [x] Interceptor 403 ban en api.ts
- [x] Palabras bloqueadas chat + Q&A (config Filament, silent drop, cache 5min)
- [x] Chat delete + ban desde app (admin long press) y desde chat monitor
- [x] Chat monitor HTML standalone por sesion (acceso desde Filament)
- [x] Velocidad monitor (cola mensajes directo/0.5s/1s/2s), slow mode, pause/resume
- [x] Rendimiento: cache auth tokens 15min, connection pooling, message batching 200ms

## Error handling (2026-04-12)

- [x] ConnectionError screen reutilizable (wifi-off, reintentar)
- [x] Onboarding: 6s timeout + spinner + ConnectionError
- [x] Home presencial + virtual error states

## Auditoria auth (2026-04-12)

- [x] 39 escenarios + 10 edge cases verificados en codigo
- [x] 14 bugs encontrados y corregidos (BUG-065 a BUG-078)
- [x] 3 code smells documentados (CS-001/002/003, no criticos)

## QA Master (2026-04-12b)

- [x] Barrido completo: 60+ endpoints probados con curl real
- [x] 20 modulos cubiertos, 3 roles verificados (presencial 14 modulos, virtual 11, vendedor stand+leads)
- [x] Escritura: social post/like/comment, Q&A, poll vote, profile, registration fields, favorites, photo, expo token
- [x] Middleware: auth 401, ban 403, throttle login 429, security headers
- [x] Docs: QA-AUTH-ONBOARDING.md + QA-MASTER.md

## Rewards / Premios canjeables (2026-04-10)

- [x] Catalogo premios: Filament CRUD, addon toggle, RewardSeeder
- [x] QR temporal HMAC 5min para canje
- [x] Staff confirma escaneo, descuenta puntos
- [x] Expire + refund automatico si no se canjea
- [x] 9 tests, seguridad SQL fix, points_log signed
- [x] Flujo canje: toast, estados boton (canjear/ver ticket/canjeado), QR rect RGB, recovery

## Gamification config (2026-04-10)

- [x] 13 acciones con puntos, daily_max, labels editables
- [x] Roles por accion, rules endpoint publico
- [x] Filament unificado bajo grupo Gamificacion
- [x] passport_completion_points configurable

## Analisis competitivo (2026-04-09)

- [x] Cotizaciones reales: Cisco $88K USD, ICE360 $49M COP
- [x] 8 features donde EventOS ya supera a ambos
- [x] 6 gaps identificados → sesiones 1.C1-C6
- [x] Pricing: $3K-8K/evento, $800-1.5K/mes SaaS
- [x] Escalabilidad horizontal validada hasta 100K+

## Showcase web demo inversor (2026-04-13)

- [x] 6 versiones iteradas (v1→v6): opening cinematico, speakers, agenda, streaming, networking, social, gamification, brand wall
- [x] Cursor fantasma, minimize-to-pill, TNT finale, progress dots, play gate
- [x] GSAP skills repo clonado (design/gsap-skills/)
- [x] Spatial UI documentado en roadmap (Apendice F → docs/WEB-APP-PLAN.md)

## Tags + Visibilidad modulos — Backend (2026-04-14)

- [x] Migration: campo `tags` JSON en attendees (default [])
- [x] Migration: `visibility_presence` enum + `visibility_tags` JSON en modules
- [x] Roles simplificados: presencial/virtual eliminados → `attendee` unico
- [x] Attendee model: cast tags array, helper `hasAnyTag()`
- [x] Module model: cast visibility_tags array
- [x] ModuleController: filtrado triple (role + checked_in_at + tags)
- [x] QR disponible para todos los attendees (identidad, no solo ticket)
- [x] CheckinService: attendance count usa attendee (no presencial)
- [x] API resources: AttendeeResource expone tags, ModuleResource expone visibility
- [x] Filament: 9 resources actualizados (roles attendee/vendedor, TagsInput, visibility selectors)
- [x] Validation rules: NotificationController, AttendeeController actualizados
- [x] Observers: ModuleObserver + ContentObserver con nuevas cache keys
- [x] AuthService + AttendeesImport: default role attendee
- [x] AttendeeFactory: +withTags() state, default attendee
- [x] 48 archivos modificados, 314 tests passing (797 assertions), 0 fallos
- [x] Commit: 30ce854

## Layout unificado — App (2026-04-14)

- [x] Merge (presencial)/(tabs) + (virtual)/(tabs) → (tabs)/ unico
- [x] Home unificado con logica vendor condicional (isVendor)
- [x] UserRole simplificado: 'attendee' | 'vendedor' | 'admin'
- [x] Ruta unica homeRoute() → /(app)/(tabs) para todos
- [x] authStore, authApi, DoneStep, MiQrScreen, ProfileScreen, activate-account, pending-approval actualizados
- [x] 14 archivos eliminados (layouts duplicados), 6 creados, 9 actualizados
- [x] TypeScript 0 errores nuevos, Expo bundle compila OK (4.6MB)
- [x] Commit: 810cc89

## Event Lifecycle — Backend + App (2026-04-14)

- [x] Migration: status enum (draft/registration/published/live/ended) + modality enum en events
- [x] Migration: about_enabled, about_image_url, about_text, about_links en events
- [x] Migration: roles en modules JSON (presencial/virtual → attendee)
- [x] Admins ven todos los modulos (skip role filter en ModuleController)
- [x] Branding API: status, modality, venue, max_attendees, registered_count, session_count, photo_count, about_*
- [x] Filament: estado+modalidad+fechas+venue+capacidad+about condicional con toggle
- [x] EventObserver → InvalidationService → socket data:invalidate → app refetch
- [x] App: 4 estados de Home (registration, published, live, ended)
- [x] CountdownTimer: normal (registration) + compact (published) + "comienza hoy" (expired)
- [x] EventInfoCard: modalidad badge, venue, registrados/capacidad, cierre registro
- [x] EventArchive: banner finalizado + stats + links a agenda/social/gamification/speakers
- [x] About pre-evento: pantalla /(app)/about con imagen+texto+links, card en Home registration
- [x] ModuleMenu compact en published (cards 56px, iconos 16px)
- [x] Socket debounce 800ms por entidad (7 invalidaciones = 1 refetch)
- [x] useBranding staleTime 30min → 5min + refetchQueries
- [x] Role pill eliminado de Mi QR, Perfil, DoneStep
- [x] Fix: branding API crasheaba por columna 'approved' inexistente
- [x] Fix: Stack.Screen mi-qr fantasma causaba socket reconnect loop
- [x] Seeders actualizados (7 archivos presencial→attendee)
- [x] 314 tests backend, TS 0 errores app
- [x] Commits backend: d970983, e4a3981, 7a61a87, 878a0b3, d375e26, d48b559, 7bda761, b25518b
- [x] Commits app: 810cc89, 59f49f7, 267ec45, 90edd7f, 7f6f6cd, c26eb06, 0e5ff8b, f30c9fd, 167c72c, 95b8aa5, b81e22a, bcc7649, 142d039, daf8411, 877a851, 85ee6f3, 5834769

## Filament cleanup (2026-04-14)

- [x] Reorganizar navegacion: 11 grupos inconsistentes → 7 limpios
- [x] Grupos: Evento(6), Contenido(7), Interaccion(7), Comunicacion(5), Registro(4), Gamificacion(3), Sistema(1)
- [x] Tildes unificadas, sort secuencial sin duplicados
- [x] 26 archivos actualizados
- [x] Commit: d2a9e86

## Optimizacion onboarding cache (2026-04-14)

- [x] useOnboarding: cache-first desde MMKV (initialData + initialDataUpdatedAt=0)
- [x] Primera vez: loading → fetch → cache. Segunda vez en adelante: instantaneo
- [x] Eliminado refetchInterval 30s (innecesario con socket invalidation)
- [x] Commit app: 5834769

---

## Sesion 2026-04-14 (tarde) — Session Detail + Onboarding + CSV + Codigos + Seguridad

### Session Detail Screen
- [x] Pantalla detalle de sesion: badges, titulo, rating, time/location, speakers tappables
- [x] Botones: Favorita, Calendario, Evaluar, UNIRTE / Ver grabacion
- [x] Navegacion circular: Agenda → Session → Speaker → Session
- [x] Agenda card tap → session detail (antes iba directo a stream)

### Onboarding — depends_on + replay
- [x] Campos condicionales: depends_on en config JSON (pais → ciudades dinamicas)
- [x] preset_value_map para resolver codigos pais
- [x] Onboarding replay diferenciado: pre-fill foto/profile/custom/intereses
- [x] Sin confetti, sin puntos dobles, titulo "Datos actualizados"
- [x] onboarding_data JSON en attendees para campos custom
- [x] GET/PUT /me/onboarding-data endpoints
- [x] isReplay flag persistente (useState lazy initializer)

### Pre-registro CSV
- [x] Flujo completo: CSV → User+Attendee+QR → InvitationMail → deep link → activacion
- [x] Fallback sin deep link: check-email → token directo → activate-account
- [x] Deep link redirect page Lumina Noir
- [x] Verificacion identidad: campo configurable (telefono), POST /auth/verify-identity
- [x] Token rotado en cada check-email (seguridad)
- [x] Tracking deep link: invitation_clicked_at + badges Enviado/Click/Activado
- [x] Login pasa event_slug (fix bug recurrente)

### Registro por Codigo de Acceso
- [x] Modelo AccessCode con isValid(), registerUse() atomico
- [x] Filament AccessCodeResource: CRUD + generar lote + ver usos
- [x] Toggle requires_access_code en RegistrationSettings
- [x] Campo condicional en AuthStep (uppercase, sanitiza espacios)
- [x] Validacion en RegisterRequest + AuthService
- [x] Tracking: access_code_used en attendee, filtro en asistentes
- [x] registerUse() con WHERE atomico (race condition safe)

### Bugs resueltos: 21 (BUG-079 a BUG-099)
- [x] BUG-079: API crash array_flip cities (CRITICA)
- [x] BUG-080: Login sin event_slug (ALTA)
- [x] BUG-081: Fetch sin timeout en index (ALTA)
- [x] BUG-082: registrationApprovedAt fallback (ALTA)
- [x] BUG-083-099: 17 bugs adicionales (onboarding, seguridad, UX)

### Bancolombia — documentacion
- [x] Requerimientos: silent disco, multi-location, webhooks badges, mission control
- [x] Timeline y estrategia competitiva actualizados en roadmap

Commits: ~20 commits en 3 repos (app, backend, docs)

---

## Sesion 2026-04-15 — Campos unificados + Registro avanzado + FAQ + Soporte

### Campos unificados registration_fields (2026-04-15 manana)
- [x] registration_fields como fuente unica (elimina campos inline en onboarding_steps_config)
- [x] Nuevas columnas: `depends_on` (varchar 60), `show_in` (registration/onboarding/both)
- [x] Onboarding config solo tiene `field_ids` (resuelve a campos reales via API)
- [x] Endpoint nuevo: GET /events/{id}/onboarding-fields
- [x] PUT /me/registration-fields acepta campos onboarding/both
- [x] Export CSV incluye todo automaticamente (una sola tabla)
- [x] depends_on avanzado: `"campo"` (cualquier valor), `"campo:val1,val2"` (especificos), `"campo:!val"` (negacion)
- [x] Deep merge Filament: mutateFormDataBeforeSave evita que secciones colapsadas null sobreescriban config
- [x] Legacy backward compat: configs con fields inline siguen funcionando

### Validacion DaVinci — 4 bugs (2026-04-15 manana)
- [x] BUG-100: Nombre acepta @# → regex solo letras+tildes+espacios
- [x] BUG-101: InterestsStep sin toasts → toast error + exito
- [x] BUG-102: Botones disabled sin feedback → eliminado disabled, validacion con toast
- [x] BUG-103: Inputs sin borde rojo → hasError en FocusInput (AuthStep)
- [x] FormStep: toast especifico por campo ("El campo X es obligatorio")

### Staff invite 1.x-H — COMPLETADO (2026-04-15 tarde)
- [x] Backend: StaffInvitationController, StaffNotificationService, join-team.blade.php, 2 migrations, 23 tests
- [x] App: mi-equipo.tsx, scanner-invite.tsx, join-team/[token].tsx, StaffInvitationModal.tsx, useStaffInvitations.ts
- [x] Socket: StaffInvitePayload, StaffResponsePayload, StaffRemovedPayload (4 event types)
- [x] Flujo: QR scan + busqueda nombre + email + link compartible + deep link aceptacion
- [x] Landing web Lumina Noir join-team.blade.php
- [x] Config: multi-stand + expiracion tokens

### Registro cerrado 1.x-F — COMPLETADO (2026-04-15 tarde)
- [x] 3 modos: email_list, domain, both (OR logic)
- [x] Toggle master, compatible con approval + access_code + invite_only
- [x] Sanitizacion: lowercase, trim, strip @ de dominios
- [x] Mensaje rechazo custom o default
- [x] 21 tests, 38 assertions. QA: 21 escenarios verificados

### Login intentos + lockout (2026-04-15 tarde)
- [x] Backend: "Credenciales incorrectas. X intentos restantes." en 422
- [x] Backend: 5to intento devuelve 423 directo con lockout
- [x] App: Object.values(errors).flat()[0] para cualquier campo
- [x] QA audit: 25+ casos de error auth verificados

### Encuesta post-evento (2026-04-15 tarde)
- [x] Backend: scope post_event en live_polls, auto-activacion EventObserver al ended
- [x] Filament: PostEventSurveyResource dedicado (CRUD, activar/cerrar manual)
- [x] App: card EventArchive, usePostEventSurvey hook
- [x] Reutiliza 100% del sistema de encuestas existente (zero duplicacion)
- [x] Seeder: 5 preguntas, 35 encuestados, 201 votos. Export CSV funcional.
- [x] 9 tests, 27 assertions

### FAQ asistente — COMPLETADO (2026-04-15 noche)
- [x] Backend: tabla event_faqs (event_id, section, question, answer_text, answer_action_url, answer_image_url, sort_order, is_active)
- [x] Backend: FaqController API GET /events/{id}/faqs (publica, agrupada por seccion) + 6 tests
- [x] Backend: CRUD Filament FaqResource
- [x] Backend: FaqSeeder (4 categorias, 12 preguntas)
- [x] App: Pantalla FAQ con orbe animado (OrbBlob 3 estados), categorias stagger, accordion con Reanimated
- [x] App: Icono Ayuda en perfil → ruta /(app)/faq
- [x] App: Filtro por categoria (chips horizontales), busqueda visual

### Soporte asistente — COMPLETADO (2026-04-15 noche)
- [x] Backend: migration admin_response + responded_at + resolved_at en support_requests
- [x] Backend: SupportController (store + mine), ordena por id DESC
- [x] Backend: Filament SupportRequestResource con badge rojo pendientes + textarea respuesta + auto-resolve
- [x] Backend: 10 tests, 34 assertions (SupportRequestTest.php)
- [x] App: support-contact.tsx — formulario asunto+mensaje, toast + router.back() (no pantalla sent vacia)
- [x] App: my-support.tsx — lista consultas con status badge (pendiente amber / resuelto green)
- [x] App: faq.tsx — boton "Mis consultas (N)" solo si tiene consultas
- [x] Push: SendPushToAttendeeJob al responder soporte ("Tu consulta fue resuelta: Re: {subject}")
- [x] Push tap navigation: PUSH_ROUTES map (support_resolved→my-support, announcement→anuncios, agenda_reminder→agenda)
- [x] INVALIDATION_MAP: support_resolved invalida ['my-support']

### Dev build Android (2026-04-15 noche)
- [x] Wireless debugging: adb pair + connect (puerto pairing != puerto conexion)
- [x] npx expo run:android — dev build nativo en dispositivo fisico
- [x] Push notifications verificadas en dispositivo real
- [x] Push tap navigation verificada

### SEC-6.2 Rate limit endpoints escritura (2026-04-15 noche)
- [x] Trait `ChecksRateLimit` reutilizable con defaults + config custom por evento
- [x] JSON `rate_limits` en events (migration + cast + fillable)
- [x] 7 endpoints protegidos: wall posts (10/dia), comments (30/dia), Q&A (10/dia/sesion), support (5/dia), photos (20/dia), stories (10/dia), leads (200/dia)
- [x] Filament: pagina "Limites de uso" con toggles + inputs por categoria (grupo Evento)
- [x] Toggle OFF = ilimitado, toggle ON = limite editable con default pre-llenado
- [x] 10 unit tests (trait aislado) + 13 feature tests (endpoints) + 8 spam simulation tests
- [x] App: fix toasts para 429 en CommentsSheet, support-contact, scanner-stand (DAILY_LIMIT)
- [x] 0 errores TypeScript nuevos en app

### Push reminders configurables + Agregar todas al calendario (2026-04-15 noche)
- [x] `SendAgendaRemindersJob` refactorizado: lee `reminder_config` JSON por evento, windows dinamicos
- [x] Defaults: enabled=true, windows=[15,5], notify_on_change=true
- [x] Filament: pagina "Recordatorios" (grupo Comunicacion) — toggle enabled, TagsInput minutos, toggle notify_on_change
- [x] `SendSessionChangedNotificationJob` — push a favoritos cuando admin cambia hora de sesion
- [x] `EventSessionObserver` — detecta cambio start_datetime/end_datetime → dispatch push (si notify_on_change=true)
- [x] App: boton "Todas" en header Mi Agenda — agrega todas las favoritas al calendario nativo de una vez
- [x] App: `session_changed` en INVALIDATION_MAP + PUSH_ROUTES (invalida agenda + mi-agenda, navega a agenda)
- [x] 5 unit tests (config defaults/merge/disabled) + 14 feature tests (push, dedup, spam 5x10=50, multi-evento)
- [x] 0 errores TypeScript nuevos

### Mensaje anclado chat — tipo Twitch (2026-04-16)
- [x] Socket server: chat:pin/chat:unpin handlers, Redis storage (TTL 24h), broadcast a room
- [x] Socket types: ChatPinnedPayload, ClientToServerEvents pin/unpin, ServerToClientEvents pinned/unpinned
- [x] join:session envia pinned actual (si existe) junto con history
- [x] Chat monitor: campo texto libre + icono pin en hover mensajes + banner azul activo + desanclar
- [x] App: PinnedBanner component (pin icon + message + author + X close)
- [x] PinnedBanner dentro del contenedor interactivo (no reduce player)
- [x] Visible en todos los modes (chat, Q&A, poll)
- [x] useChat: pinnedMessage state + listeners chat:pinned/chat:unpinned
- [x] Solo admin puede pin/unpin (role check server-side)
- [x] TypeScript 0 errores nuevos en socket + app

### Calendar .ics en email de bienvenida (2026-04-16)
- [x] WelcomeMail adjunta .ics con fechas del evento (METHOD:REQUEST, UID unico, SEQUENCE:0)
- [x] Datos del .ics: nombre, fechas, venue, descripcion — todo de event model
- [x] Sin fechas → sin adjunto (graceful)
- [x] Verificado en Mailpit: adjunto correcto, 562 bytes, text/calendar, contenido ICS valido

### Componentes base + Theme tokens (2026-04-16)
- [x] `lib/theme.ts`: tokens surface (low/medium/high/border), text (primary/secondary/muted/label/placeholder), radius, spacing, fonts
- [x] `GlassCard`: variantes low/medium/high, radius md/lg/xl, padding, bordered
- [x] `GlassButton`: variantes primary/glass/outline/icon, ScalePress+haptic integrado, accent dinamico
- [x] `GlassInput`: label, focus state accent, error state rojo, leftIcon
- [x] `SectionLabel`: uppercase, letterSpacing, variantes sm/md
- [x] Session Detail migrada: Calendar/UNIRTE/Ver grabacion → GlassButton, Description/Speakers → GlassCard+SectionLabel
- [x] Fix TS error pre-existente: AuthStep RefObject<TextInput | null>
- [x] 0 errores TypeScript en toda la app
- [x] Decision: migrar resto de archivos junto con Light Mode (tokens dinamicos, no doble pasada)

---

## Lumina Lux v2 "The Gallery" — Sesion 2026-04-17

### Tokens y fundamentos (Fases 0-2)
- [x] theme-lux.ts reescrito: Cool gray #F6F8FA, ink #1A1A1A, sombras 4 niveles, WCAG AA
- [x] theme-noir.ts paridad completa de interfaces
- [x] theme.ts expandido: IconTokens, ShadowTokens, CategoryTokens, StateTokens, GlassTokens, SkeletonTokens, StatusBarTokens, SemanticTokens, OverrideTokens, isDark
- [x] themeStore: accent dinamico por tema (Noir=#FFFFFF, Lux=#1A1A1A)
- [x] FloatingTabBar: Liquid Glass emulation (blur 35, tint, specular highlights)
- [x] Onboarding: pills binarias ink, primaryColor por tema, AuthStep cards visibles
- [x] Gold adaptativo: semantic.gold Noir=#FFD700, Lux=#B8860B

### Home (Fase 3)
- [x] HappeningNow: dark island fondo #0a0a0a solido (NO transparente)
- [x] VendorHappeningNow: dark island identico, MiStandCard negro
- [x] GamificationHud + VendorGamificationHud: dark islands con shadow.lg en Lux
- [x] ModuleMenu: cards blancas + shadow.md
- [x] HomeHeader: bell blanca + shadow
- [x] HomeHero: kicker text.muted (no accent)

### Agenda (Fase 4)
- [x] Day pills: #FFFFFF + shadow.md (misma elevacion que modulos)
- [x] Session cards: #FFFFFF + shadow.sm, borde live neutro en Lux
- [x] Track filter: bordes #D1D3D8, "Todos" activo = ink solido
- [x] Timeline: connector #C8C9CE opacity 0.6, dots #4A4B50 upcoming
- [x] Action buttons: fondo #F0F2F5
- [x] Perf: useTheme() extraido de SessionCard/AnimatedHeart/TimelineDot (props)

### Session Detail (Fase 5)
- [x] Titulo, metadata, speakers: textTokens.primary
- [x] Metadata card: #FFFFFF + shadow.sm, iconos icon.tertiary
- [x] GlassCard: #FFFFFF + shadow.sm en Lux (aplica globalmente)
- [x] GlassButton: fondo #F0F2F5 + borde #E5E7EB en Lux
- [x] Favorita button: #FFF0F3 rosa cuando guardada

### Speakers (Fase 6)
- [x] Featured cards: glass (BlurView + sombra exterior + borde luminoso)
- [x] Speaker list items: #FFFFFF + shadow.sm
- [x] Speaker detail: back button, LinkedIn azul brand, Calificar gold visible, cards blancas

### Mi QR (Fase 7)
- [x] Badge card: dark island #0a0a0a solido + shadow.lg en Lux
- [x] DashedLine: colores Noir hardcodeados (independiente del tema)
- [x] Hint/wallet cards: #FFFFFF + shadow.sm

### Social (Fase 8)
- [x] PostCard: #FFFFFF + shadow.sm, autor textTokens.primary
- [x] CommentsSheet: titulo + input adaptados

### Sponsors (Fase 9)
- [x] Brand wall: cards blancas + shadow.sm (sin BlurView/gradient en Lux)
- [x] Sponsor detail: back button solido, textos ink, links visibles
- [x] Session cards unificadas: hora + type badge + titulo + location + chevron

### Networking (Fase 10)
- [x] Suggestion cards: glass effect (igual que speakers featured)
- [x] Directory/contact/request cards: #FFFFFF + shadow.sm
- [x] Attendee detail: back button, sesiones unificadas, cards blancas
- [x] "Ignorar" en solicitudes: textTokens.secondary (visible)

### Session Types configurables
- [x] Backend: tabla session_types (event_id, name, slug, color, order)
- [x] Backend: FK session_type_id en event_sessions, migracion datos existentes
- [x] Filament: SessionTypeResource con ColorPicker
- [x] API: session_type como {name, color} en agenda, speakers, sponsors, attendee
- [x] App: badges leen color de API, zero TYPE_COLORS hardcodeado
- [x] 5 tipos default creados, 32 sesiones migradas

### Commits sesion 2026-04-17
- Backend: ~8 commits (tokens, session_types, API updates)
- App: ~15 commits (Fases 0-10, perf, session_types)
- Docs: roadmap Lux v2, completado, pendientes

## Tareas completadas previas (movidas de PENDIENTES 2026-04-17)

- [x] Calendar sync boton visible — boton .ics en session detail (2026-04-16)
- [x] SEC-6.1: Rate limit networking — 100/evento, 30/dia. 3 tests (2026-04-15)
- [x] SEC-6.2: Rate limit endpoints escritura — Trait ChecksRateLimit, 23 tests (2026-04-15)
- [x] Fix Reanimated warning — StatCard entering separado (2026-04-16)
- [x] Mensaje anclado chat Twitch — socket pin/unpin, Redis TTL, chat monitor (2026-04-16)
- [x] Componentes base + Theme tokens — GlassCard, GlassButton, etc (2026-04-16)
- [x] Setup wizard evento Filament — 5 pasos, modulos, auto-crea (2026-04-15)
- [x] Dev build + Push — probadas en dev build fisico (2026-04-15)
- [x] Push reminders configurables — windows dinamicos, toggle, 19 tests (2026-04-15)
- [x] Calendar invite .ics en email — WelcomeMail adjunta .ics (2026-04-16)
- [x] ModuleMenu: elevation removida en Lux, borde sutil (evita frame negro en transiciones) (2026-04-17)
- [x] TabScreenWrapper/ScreenWrapper: opacity removida (evita flash elevation Android) (2026-04-17)
- [x] BottomSheet: prop forceNoir para gamificacion (2026-04-17)
- [x] Backend Filament Light Mode: migration default_theme + primary_color_light, Select tema + ColorPicker, API branding + onboarding (2026-04-17)
- [x] Tema pre-auth: default_theme aplicado en queryFn de useOnboarding antes del render (2026-04-17)
- [x] primary_color_light: accent adaptativo por tema en themeStore (2026-04-17)

## Mission Control v4 — Display + Metricas + Moderacion (2026-04-19)

### Display LED session-level
- [x] Ruta `/display/session/{id}?token=HMAC` — pagina publica Lumina Noir
- [x] Socket RT: `display:project` / `display:stop` con persistencia Redis (TTL 4h, sobrevive refresh)
- [x] Render polls: multiple choice (barras + ranking + counter animado), star rating (track + counter), open text (cola 1.8s)
- [x] Render Q&A: pregunta alineada izquierda, autor bottom-right, slide-in animation
- [x] Fade suave entre proyecciones, standby con breathing animation
- [x] Boton copiar enlace HMAC en sidebar MC (fallback execCommand para HTTP)

### Metricas en vivo
- [x] Redis `INCR chat:count:session:{id}` por mensaje de chat
- [x] `session:audience` broadcast debounce 500ms en join/leave (fetchSockets count)
- [x] `session:metrics` event al join con chat count persistido
- [x] Engagement: (mensajes + preguntas) / audiencia * 100, client-side cada 5s
- [x] MPM (messages per minute) con ventana deslizante 60s

### Moderacion open text
- [x] Migracion `is_approved` en live_poll_votes (default false para open_text)
- [x] Modal de moderacion en MC con aprobar/rechazar individual
- [x] Endpoint batch `POST /admin/polls/votes/approve-batch` (1 request, sin rate limit)
- [x] Display solo muestra respuestas aprobadas
- [x] Cola de presentacion: respuestas salen 1 cada 1.8s aunque se aprueben de golpe

### Q&A proyectable
- [x] Boton Proyectar en preguntas aprobadas/respondidas del tab Q&A
- [x] Display: pregunta grande alineada izquierda, autor small bottom-right

### Mejoras MC
- [x] Timeline persistente en localStorage por sesion
- [x] YouTube iframe: pointer-events passthrough, controles accesibles
- [x] Poll cards: update individual sin re-render de toda la lista
- [x] Animaciones: bars con deferred rAF (0→target), cards sin reset en updates

### Herramientas moderador (2026-04-19b)
- [x] Reloj real en header (hora actual, reemplaza elapsed timer)
- [x] Countdown sesion: verde/amber(<5min)/rojo(pasado)
- [x] Mini agenda sidebar: sesiones del dia, actual resaltada, pasadas gris
- [x] Tareas: checkbox list con localStorage, add/toggle/remove, word-break completo
- [x] Responsive: sidebar drawer <1200px, stack <1024px, compacto <768px
- [x] Audiencia: cuenta usuarios unicos, excluye admin/moderator/organizer

### Testing
- [x] DisplayTestSeeder: 3 tipos de poll con 10 votos cada uno
- [x] SimulateVotes command: `php artisan app:simulate-votes {pollId} --count=20 --delay=2`
- [x] simulate-audience.cjs: 50 conexiones socket simultaneas
- [x] 13 bugs encontrados y corregidos (BUG-135 a BUG-147)

### Totales acumulados 2026-04-19
- Backend: 488+ tests, 1168+ assertions, 0 fallos
- Features completados: campos unificados, staff invite, registro cerrado, login lockout, encuesta post-evento, FAQ, soporte completo, push navigation, SEC-6.2 rate limits, push reminders configurables, agregar todas al calendario, .ics en email bienvenida, mensaje anclado chat, componentes base, Light Mode Lux v2, Mission Control v4 display+metricas+moderacion+herramientas moderador
- Bugs: BUG-001 a BUG-147 registrados, 145+ resueltos

---

## 2026-04-20 — Quick Wins + Stand Stats + Rendimiento

### Quick wins
- [x] Health check endpoint: DB + Redis + Queue en /api/v1/health
- [x] Permisos granulares Filament: HasResourcePermission trait, 41 recursos, canAccessPanel gate (super_admin/org_admin/event_admin/moderator), 10 permisos mapeados

### Stand Stats + Contacts
- [x] GET /me/stand/stats: leads, views, favorites, contacts, stamps, trivia, by_tier, by_member, top_services
- [x] GET /me/stand/contacts: solicitudes de contacto con attendee info completa
- [x] App stand-stats.tsx: engagement unificado, tier bars, ranking equipo, servicios, export, pull-to-refresh
- [x] App stand-contacts.tsx: inbox solicitudes con Llamar/Email/WhatsApp
- [x] Mi Stand simplificado: 3 stats + hero + FAB (eliminados duplicados)
- [x] CSV export con resumen stats
- [x] StandStatsSeeder + 13 tests (49 assertions)

### Rendimiento
- [x] Polling eliminado: encuestas (30s), gamification (30s+15s), passport (15s)
- [x] Invalidacion targeted: broadcastToAttendee() para gamification/passport
- [x] Leaderboard: staleTime 60s + refetchOnWindowFocus

### Bug fixes (BUG-148 a BUG-154)
- [x] BUG-148: reload Expo → onboarding (isHydrated guard)
- [x] BUG-149: push ban sin sesion (onboarding_seen + activated_at + activeBan)
- [x] BUG-150: session detail UI hardcodeada (GlassCard/GlassButton/tokens)
- [x] BUG-151: polling innecesario (3 refetchInterval eliminados)
- [x] BUG-152: resolveAvatarUrl firma incorrecta
- [x] BUG-153: handleRefresh sin try/finally
- [x] BUG-154: BanTest sin activated_at

### QA hallazgos resueltos
- [x] QA-01: colores hardcodeados trending → constante STATUS_COLORS
- [x] QA-02: require('expo-router') dinamico → import estatico
- [x] QA-03: debounceTimers leak → cleanup clearTimeout en disconnect

### Session Stats + Attendance Tracking
- [x] Migracion: tabla `session_attendances` (session_id, attendee_id, source, joined_at, left_at, duration_seconds)
- [x] Migracion: 6 campos config en events (silent_disco_enabled, attendance_min_minutes, attendance_points, attendance_bonus_full, certificate_min_sessions, certificate_min_duration_pct)
- [x] SessionStatsService: attendance, chat, Q&A, polls, ratings, engagement score 0-100, activity timeline, attendance detail
- [x] Socket: Redis SADD/HSET en join:session, SREM/HSET en leave:session (tracking asistencia RT)
- [x] FlushSessionAttendanceJob: cron cada 60s Redis → DB (batch upsert)
- [x] AwardSessionAttendancePointsJob: puntos al finalizar sesion (min minutos + bonus >90%)
- [x] API: GET /sessions/{id}/stats, GET /sessions/{id}/viewers, GET /sessions/{id}/export
- [x] ExportSessionStatsJob: CSV en background con notificacion Filament (campana + boton descargar)
- [x] Filament ViewSessionStats: pagina resumen por sesion con boton "Exportar CSV"
- [x] Filament GamificationSettings: toggle silent disco + config asistencia/certificados
- [x] SessionStatsSeeder: 50 usuarios simulados con asistencia, chat, Q&A, polls, ratings
- [x] 11 tests session stats (59 assertions)

### Totales acumulados 2026-04-20b
- Backend: 537+ tests, 1377+ assertions, 0 fallos
- Bugs: BUG-001 a BUG-154 registrados, 154+ resueltos (+ 3 QA)

---

## Room Check-in System — Salones con Totems (2026-04-20c)

> Adaptacion completa del sistema Checki (PHP vanilla) a EventOS Laravel.
> 7 commits backend, 3 commits kiosko. 17 tests, 216+ assertions.

### Migraciones
- [x] `event_rooms`: salones del evento (name, capacity, checkin_enabled, is_active)
- [x] `room_totems`: tablets/iPads en puertas (name, type entrada/salida/bidireccional, auth_token 64 chars, last_heartbeat_at)
- [x] `room_movements`: movimientos inmutables (type checkin/checkout, scanned_at servidor, device_timestamp referencia, method qr_scan/manual/auto_room_change/auto_end_day/auto_end_session, flags JSON)
- [x] `room_attendee_states`: cache estado actual attendee+room (inside/outside, reconstruible desde movements)
- [x] `attendance_checks`: triggers moderador silent disco (session_ids JSON, ttl_seconds, expires_at)
- [x] `attendance_check_responses`: confirmaciones individuales (UNIQUE check_id+attendee_id)
- [x] `event_sessions` +: room_id FK, silent_disco_group_id, actual_start_at, actual_end_at, cancelled_at
- [x] `events` +: room_checkin_enabled, room_debounce_seconds, room_auto_checkout_buffer_minutes, attendance_check_ttl, attendance_check_min_confirms

### Modelos
- [x] EventRoom, RoomTotem (auto-genera auth_token), RoomMovement, RoomAttendeeState
- [x] AttendanceCheck, AttendanceCheckResponse
- [x] EventSession: effectiveStart(), effectiveEnd(), isCancelled()

### Services
- [x] RoomCheckinService: processScan() 10 pasos (resolve QR, verify ban/checkin, toggle state, auto-checkout otro salon, debounce, lock, create movement, update state, broadcast socket)
- [x] Cache layer: getStateFromCache, getOtherRoomInside, setCacheState, resolveTotem, cacheTotem (Cache facade con fallback DB)
- [x] RoomAttendanceCalculator: calculate() por evento/room, effectiveStart/End, skip cancelled, overlap rule (primera sesion gana), buffer 15min pre-inicio, buildIntervals, getAttendeesInsideAt
- [x] Batch offline: processScan con device_timestamp + flag cola_offline

### Controller — RoomCheckinController
- [x] POST /api/v1/rooms/scan — escaneo QR totem (auth X-Totem-Token)
- [x] POST /api/v1/rooms/scan/batch — sync cola offline (array scans con device_timestamp)
- [x] GET /api/v1/rooms/ping — heartbeat + schedule completo salon (sesiones con status live/ended/upcoming)
- [x] GET /events/rooms/{eventId}/occupancy — aforo RT por salon (admin)
- [x] GET /events/rooms/{roomId}/attendees — lista asistentes dentro (admin)
- [x] POST /events/attendance-checks/trigger — admin dispara check silent disco (push + socket a quienes estan DENTRO del salon)
- [x] POST /events/attendance-checks/{id}/confirm — attendee confirma sesion (valida TTL, valida inside room, idempotente, award points)
- [x] GET /events/attendance-checks/pending — check activo sin responder (para app al abrir desde push)
- [x] GET /events/attendance-checks/{id}/results — resultados por sesion (admin)
- [x] GET /events/attendance-checks/report — reporte final mayoria (admin, entregable sponsor)

### Controller — SessionConfigController (lifecycle)
- [x] POST /admin/sessions/{id}/start — marca actual_start_at, broadcast session:started
- [x] POST /admin/sessions/{id}/end — marca actual_end_at, broadcast session:ended
- [x] POST /admin/sessions/{id}/cancel — marca cancelled_at, broadcast session:cancelled
- [x] POST /admin/sessions/{id}/delay — retrasa agenda salon X minutos (todas las futuras)

### Jobs / Cron
- [x] AutoCheckoutEndOfDayJob: diario 23:30, checkout todos los "inside" con flag inferido
- [x] SmartAutoCheckoutJob: cada 15min, si salon sin sesion activa ni proxima (buffer configurable) → auto-checkout
- [x] SmartAutoCheckoutJob: auto-cierra sesiones sin "End" (actual_start_at set pero actual_end_at null, 30min despues de end_datetime)
- [x] ExportRoomAttendanceJob: CSV con nombre, email, empresa, telefono, minutos, % asistencia, calidad dato

### Filament Admin
- [x] EventRoomResource: CRUD salones (event, name, capacity, toggle checkin, activo)
- [x] RoomTotemResource: CRUD totems (evento, salon, nombre, tipo, IP, token copiable, last heartbeat)
- [x] EventSessionResource: Select room_id + TextInput silent_disco_group_id

### Mission Control
- [x] Tab "Control" (5ta tab, tecla 5): botones Start/End/Cancel/Delay con modal confirmacion
- [x] Estado visual sesion (Programada/En vivo/Finalizada/Cancelada)
- [x] Boton attendance check (solo visible si sesion tiene silent_disco_group_id)
- [x] Countdown + resultados RT por sesion via socket attendance:check:update
- [x] MC_CONFIG inyecta actual_start_at, actual_end_at, cancelled_at
- [x] attendance-check.html: pagina standalone para trigger + resultados (alternativa al boton en MC)

### Kiosko Web (eventos-kiosko, modo room)
- [x] Modo activado por URL: ?mode=room&totem_token=XXX
- [x] Camara oculta siempre activa (sin viewfinder, detecta QR en cualquier parte del frame)
- [x] Cooldown 5s: mismo QR no se reprocesa, previene loop infinito
- [x] Offline queue: IndexedDB almacena scans cuando servidor no responde
- [x] Batch sync: al reconectar, envia cola en orden cronologico con device_timestamp
- [x] Heartbeat ping cada 10s: devuelve schedule completo del salon (status live/ended/upcoming)
- [x] Socket listeners: session:started, session:ended, session:cancelled, agenda:updated → re-ping
- [x] Resultado 4s: checkin (verde), checkout (azul), error (rojo), offline (amber) → auto-regresa
- [x] room-api.ts, offline-queue.ts, useRoomTotem.ts, RoomApp.tsx

### CORS / Routing
- [x] X-Totem-Token agregado a allowed_headers
- [x] Puertos 5173/5174/5175 en allowed_origins
- [x] Rutas totem en web.php (bypass Sanctum statefulApi que bloqueaba desde browser)

### Tests
- [x] RoomCheckinTest: 16 tests, 48 assertions (scan, checkout, auto-checkout, debounce, banned, unauthorized, ping, trigger, confirm, expired, idempotent, not-in-room, pending)
- [x] RoomStressTest: 1 test, 168 assertions (50 personas, 17 escenarios: normal, late start, late arrival, break, no checkout, room change, cancelled, extended, lunch auto-checkout, silent disco, debounce, banned, no event checkin, no end press, delay, offline batch, saturation)

### Totales acumulados 2026-04-20c
- Backend: 553+ tests, 1593+ assertions
- Kiosko: build exitoso, funcional en localhost

---

## 2026-04-21 — Kiosk Fases 3-4 + Staff App + Session Lifecycle + Bug Audit

### Kiosk Roadmap — Fases completadas
- [x] **Fase 0** — Demo HTML hibrido (standalone) — COMPLETADA
- [x] **Fase 1** — Implementar en React (Kiosko) — COMPLETADA (28 items, solo falta verificar scan <100ms en VPS)
- [x] **Fase 2** — Mission Control navegacion — COMPLETADA (sidebar agenda, HMAC nav, tracks)
- [x] **Fase 3** — Silent disco UI — COMPLETADA (MC tab asistencia, app modal, TTL, CSV, socket RT; push pendiente dev build)
- [x] **Fase 4** — App movil staff_checkin — COMPLETADA (backend 10 tests, app hub+scanner+assignment, socket RT; cola offline nice-to-have)

### Staff App (sesion 2026-04-21b)
- [x] StaffHappeningNow card en home
- [x] ModuleMenu: modulo staff_checkin visible solo para rol staff_checkin
- [x] Scanner staff con room picker + resultado BottomSheet
- [x] Filament: assign staff desde admin panel
- [x] Fix: roomCheckinApi usaba `api` como callable (BUG-165)

### Session Lifecycle — 8 bugs criticos corregidos (sesion 2026-04-21c)
- [x] **BUG-175 CRITICO**: Carbon mutation en adjustNextSession() — siguiente sesion quedaba con duracion 0 min (start=end). Fix: `->copy()->addMinutes()`
- [x] **BUG-176 ALTO**: .ics calendario usaba end_datetime en vez de publicEnd() — usuario descargaba hora sin delay. Fix: `publicEnd()`
- [x] **BUG-177 ALTO**: Delay movia sesiones ya iniciadas. Fix: `whereNull('actual_start_at')`
- [x] **BUG-178 ALTO**: start() permitia iniciar sesion finalizada/cancelada. Fix: guards ended + cancelled
- [x] **BUG-179 ALTO**: cancel() no revertia delay en siguiente sesion. Fix: revert logic + clear adjusted_end_at
- [x] **BUG-180 ALTO**: Agenda RT no actualizaba horarios (titulo si, start/end no). Fix: MMKV cache clear + FlashList extraData + mi-agenda invalidation
- [x] **BUG-181 MEDIO**: MC segundo moderador no recibia timer correcto. Fix: startLiveTimer/stopLiveTimer en socket listeners
- [x] **BUG-182 BAJO**: Push delay incluia sesiones pasadas. Fix: filtro `>= now()-2h`
- [x] 5 tests nuevos: preserva duracion, no mueve started, rechaza ended/cancelled, cancel revierte delay
- [x] **BUG-183**: Kiosk ping ignoraba adjusted_end_at — totem mostraba hora sin delay. Fix: `publicEnd()` en RoomCheckinController:95
- [x] **MC cronometro verificado**: elapsed desde actual_start_at server, remaining contra SESSION_END (actualizado por delay), warning amber <5min, sync socket entre MCs

### MC Session Lifecycle — Control tab completado
- [x] Botones Start/End/Cancel con modal confirmacion
- [x] Delay +5/+10/+15/+30 con cascade a siguiente sesion
- [x] Cronometro "En vivo — X min" con warning <5 min restantes
- [x] Timeline persistente en localStorage
- [x] Socket sync entre multiples MCs (started/ended/cancelled)
- [x] Estado inicial desde MC_CONFIG (actual_start_at, actual_end_at, cancelled_at)

### QA Pre-produccion documentado
- [x] PLAN-STRESS-TEST.md ampliado: QA de integridad funcional + 4 smoke tests E2E + chaos testing (6 escenarios) + calendario pre-prod semana -8 a dia D

---

## Webhooks Integracion Partners — COMPLETADO (2026-04-21)

> P0 non-negotiable. Bancolombia usa empresas externas para badges.
> Roadmap: docs/ROADMAP-WEBHOOKS.md

### Fase 1 — Modelo de datos
- [x] 3 migraciones: webhook_endpoints, webhook_api_keys, webhook_logs
- [x] 3 modelos: auto-genera secret (64 chars), key con prefijo wh_live_ (48 chars), log inmutable
- [x] Helpers: listensTo(), signPayload() HMAC-SHA256, hasPermission(), isSuccess()

### Fase 2 — Outbound (EventOS → Partner)
- [x] WebhookDispatchService: busca endpoints activos, filtra por evento suscrito, filtra campos seleccionados
- [x] DispatchWebhookJob: HTTP POST con HMAC signature, retry 3x (1min/5min/30min), respeta 429 Retry-After
- [x] AttendeeWebhookObserver: dispara en created (registered), updated (approved/checked_in/profile), deleted (cancelled)
- [x] Idempotency key ULID en cada payload
- [x] Test mode: payloads con `"test": true`
- [x] needs_attention: marca endpoint tras 3 fallos consecutivos

### Fase 3 — Inbound (Partner → EventOS)
- [x] WebhookInboundController: check-in individual y batch (max 100)
- [x] Auth por header X-Webhook-Key (sin Sanctum)
- [x] Rate limit atomico por key (configurable, default 100/hora), Retry-After header en 429
- [x] Check-in por email o por attendee_id
- [x] Test mode: no marca checked_in_at, responde would_check_in
- [x] Idempotente: re-checkin devuelve already_checked_in
- [x] Validaciones: banned (403), not found (404), inactive key (401), sin permiso (403)
- [x] Batch transaccional (DB::transaction)

### Fase 4 — Filament Admin
- [x] WebhookEndpointResource: CRUD + acciones (Enviar prueba, Copiar secret, Reenviar fallidos, Descargar spec)
- [x] WebhookApiKeyResource: CRUD + accion Regenerar key
- [x] WebhookLogResource: read-only, filtros direction/status/test, detalle payload+response
- [x] WebhookStatsWidget: enviados hoy, check-ins recibidos, tasa de exito
- [x] Boton "Simular evento": 5 registros + 3 check-ins fake al partner
- [x] Boton "Descargar spec": TXT con URLs, keys, payload ejemplo, codigos respuesta, verificacion HMAC

### Fase 5 — Operaciones
- [x] PruneWebhookLogsCommand: `webhook:prune-logs --days=90` en cron diario 2am
- [x] TestWebhooksCommand: `app:test-webhooks` — 19 checks automaticos (setup + outbound + inbound + logs)

### Bugs post-audit corregidos
- [x] Rate limit race condition: Cache::get+increment → increment atomico
- [x] Retry-After header faltante en 429
- [x] Batch sin transaccion → DB::transaction
- [x] Sin idempotency key → ULID por dispatch
- [x] Logs sin cleanup → prune command cron

### Totales
- 24 tests unitarios, 60 assertions
- 19 checks en test command
- 3 Filament resources, 1 widget, 6 acciones
- 0 bugs pendientes

### Totales acumulados 2026-04-21
- Backend: 582+ tests, 1664+ assertions
- Kiosk roadmap: 4 de 4 fases completadas
- Webhooks: COMPLETADO (P0 cerrado)

---

## 2026-04-22/23 — Live Moments (Juegos en Vivo) COMPLETADO

> Roadmap completo: docs/ROADMAP-LIVE-MOMENTS.md v2.5
> Bugs: ver BUG-LOG.md (numeracion reorganizada, BUG-177 a BUG-232)

### Fase 1 — Backend + MC base (2026-04-21/22)
- [x] LiveGame + LiveGameParticipant models, migrations, relations
- [x] GameService: pool RT (presencial room + virtual socket), weightedRandom, broadcast, cooldown
- [x] CRUD + launch + spin + draw + next-question + close-round + answer endpoints
- [x] MC tab Games: drafts, launch, historial, eligible RT
- [x] Display spin: spin-wheel lib, idle, control btn, auto-standby
- [x] Display jackpot: photo cascade ceremony GSAP, shockwave, confetti
- [x] MasterSeeder: 50 attendees, 25 check-in, 12 games

### Fase 2 — Sorteo Ceremony + Golden Ticket (2026-04-22)
- [x] Rewrite slot machine → Photo Cascade Ceremony (strip vertical GSAP power4.out)
- [x] Golden Ticket: claim_code 6 chars, /me/prizes, Filament PrizeRedemptions page
- [x] AnnouncementController: publicos cacheados + privados solo al target
- [x] App: useMyPrizes hook, AnnouncementCard tappable deep link
- [x] 15 bugs resueltos (pool RT, race conditions, ceremony visual)

### Fase 3 — App integration (2026-04-22)
- [x] Socket listeners: game:launched, game:result, game:question, game:round-result, game:finished
- [x] Toast info/success, invalidacion my-points/my-prizes/announcements
- [x] Push al ganador jackpot con claim_code + deep link

### Fase 4 — Trivia Kahoot-style (2026-04-23)
- [x] Backend: next-question (broadcast sin respuesta), close-round (distribucion, leaderboard, auto-finish)
- [x] Answer: scoring correcta + speed bonus, previene doble (unique constraint)
- [x] MC tab Trivia: 4 estados (draft, lobby, question activa, resultado, podio)
- [x] Display LED: pregunta+opciones, barras distribucion RT, leaderboard, podio
- [x] App TriviaPanel: countdown, opciones, feedback correcto/incorrecto, mini leaderboard, podio
- [x] useTriviaStore zustand: 4 fases (idle, question, result, finished)
- [x] 15 bugs resueltos (activePanel critico, countdown, toast labels, MC handlers)

### Fase 5 — Performance + Export + Branded + Platinum Gold (2026-04-23)
- [x] ProcessSpinRewardsJob: bulk upsert + insertOrIgnore (20K queries → ~10)
- [x] ProcessTriviaRewardsJob: bulk points award async
- [x] points_log unique constraint migration
- [x] Trivia CSV export (ronda, pregunta, respuesta, tiempo, score)
- [x] Filament page LiveGameResults con export por juego
- [x] Branded moments: sponsor en MC projections, display, app toast + TriviaPanel
- [x] Platinum Gold #B5A68B unificado (3 repos, ~15 archivos)
- [x] MC metrics elevation (box-shadow, sin barras color generico)
- [x] Golden Ticket rediseno "Award Certificate" (linea accent, trophy, QR limpio)
- [x] AgendaScreen Evaluar + PollSlides star_rating colores corregidos
- [x] 6 bugs resueltos (10K spin, 10K trivia, answer race condition, points_log unique, Redis flushdb)

### BUG-LOG reorganizado (2026-04-23)
- [x] Renumerado BUG-001 a BUG-232, cero duplicados (antes habia ~15 duplicados)
- [x] 210+ bugs total, 208+ resueltos, 2 pendientes

### Totales acumulados 2026-04-23
- Backend: 582+ tests, 1664+ assertions (41 Game tests, 172 assertions)
- Live Moments: COMPLETADO (5 fases, 35+ bugs resueltos)
- Bugs: BUG-001 a BUG-232, 208+ resueltos, 2 pendientes (BUG-111, BUG-127)

## Data Center modulo (2026-04-25 al 2026-04-26) — 6 iteraciones, ~25h

### SPA standalone con 9 tabs analytics
- [x] F0-F7 backend: 44 export jobs en app/Jobs/Exports/, BaseExportJob, ExportService
- [x] Queue separado 'exports' para aislamiento de evento en vivo (10K activos)
- [x] DataCenterController + Sanctum stateful auth (session cookie Filament)
- [x] SPA en public/data-center/ — index.html + assets/app.js + assets/app.css
- [x] 9 tabs enriquecidas: Asistentes, Sesiones, Engagement, Sponsors, Gamificacion, Social, Networking, Comunicaciones, Auditoria

### Bloques A/B/C/D enterprise
- [x] A1 Sub-filtros extendidos (chat_by_hour por session_id)
- [x] A2 Cache invalidation con DataCenterCacheObserver
- [x] A3 Polling notifs adaptativo (pausa tras 3 polls vacios)
- [x] B1 Tooltips en graficas (title SVG en donuts)
- [x] B2 Chip "Actualizado hace Xs" en header
- [x] B3 Sparkline en card Check-ins con checkins_by_hour
- [x] B4 Empty states con microcopy especifica
- [x] B5 Loading granular fade
- [x] C1 Comparativa entre periodos (30d vs 30d, capped 150%, +N fallback)
- [x] C2 Goals/targets con UI modal CRUD + barras progreso bajo hero cards
- [x] C3 Scheduled exports con cron processor + modal UI + lista
- [x] C4 Embed publico backend listo (UI descartada por redundante con Event Pulse)
- [x] C5 Multi-evento dashboard (UI en empty state + tabla comparativa)
- [x] D1 Comparador A/B sesiones (2 selects + tabla winner highlight)
- [x] D2 Export "vista actual" hereda filtros del tab activo

### Tema Lux v2 + filtros + XLSX
- [x] Tema Lux con toggle, localStorage persiste sin flash
- [x] Filtros reactivos: date range global + sub-entidad por tab
- [x] XLSX real con maatwebsite/excel — generateXlsx() en BaseExportJob
- [x] Cards con shadow en lugar de border (regla Lux)

### Infra produccion lista
- [x] docker/Dockerfile.worker + docker/docker-compose.worker.yml
- [x] .env.worker.example completo (replica + R2 + VPC Redis)
- [x] config/database.php read/write split condicional
- [x] SANCTUM_STATEFUL_DOMAINS en .env.example
- [x] routes/web.php fallback /data-center/{any?} con auth + role check

### 5 migraciones nuevas
- [x] dc_event_goals, dc_scheduled_exports, dc_embed_tokens
- [x] /dc-embed/{token} ruta publica con embed.html standalone

### QA: 29 tests nuevos (100% passing)
- [x] DataCenterFiltersTest — 7 tests
- [x] DataCenterFeaturesTest — 14 tests
- [x] DataCenterCacheObserverTest — 4 tests
- [x] BaseExportJobXlsxTest — 3 tests
- [x] phpunit.xml: memory_limit 2048M
- [x] Suite global: 769/772 passed (3 fallos pre-existentes flaky no relacionados)

### Seeder demo
- [x] DataCenterDemoSeeder con pravatar avatars, clearbit logos, 54 attendees,
      280 session_attendances, 360 push_logs, 80 webhook_logs, 58 questions,
      90 audit_log, 8 bans, 12 role changes

## Mission Control bugs cierre (2026-04-26) — 15 bugs + ruleta + brand

### Bugs criticos (2)
- [x] BUG-291: Token 12h expira sin refresh
- [x] BUG-292: Listeners socket duplicados

### Bugs altos (5)
- [x] BUG-293: Memory leaks timers
- [x] BUG-294: 50+ catches silenciosos
- [x] BUG-295: Race conditions modales
- [x] BUG-296: Socket no rejoinea reconnect
- [x] BUG-287: ExportService HAVING sin GROUP BY

### Bugs medios (5)
- [x] BUG-288: HOUR() no portable SQLite
- [x] BUG-297: localStorage sin validar tipo
- [x] BUG-298: Doble-click games (lockButton 6 callsites)
- [x] BUG-299: Progress bar attendance imprecisa
- [x] BUG-290: Hero card delta % absurdo

### Bugs bajos (5)
- [x] BUG-289: Schedule::call name() antes withoutOverlapping
- [x] BUG-300: Substring tras escape rompe HTML entities
- [x] BUG-301: ESC no cierra modales
- [x] BUG-302: Z-index conflicts
- [x] BUG-303: Render error boundary

### Polish UI
- [x] BUG-304: Status bar Socket.IO casi invisible
- [x] BUG-305: Ruleta resultado antes de fin animacion
- [x] Brand "MISSION CONTROL" estilo unificado con Data Center (mc-brand-mark eliminado)

### Nuevos endpoints + cron
- [x] /data-center/{event}/goals (GET, POST, DELETE/{metric})
- [x] /data-center/{event}/scheduled (GET, POST, DELETE/{id})
- [x] /data-center/{event}/embeds (GET, POST, DELETE/{token})
- [x] /data-center/multi?event_ids= (multi-evento comparativa)
- [x] /data-center/{event}/notifications/read-all (POST), /clear (DELETE)
- [x] Cron Schedule::call cada 5min procesa dc_scheduled_exports

### Totales acumulados 2026-04-26
- Backend: 611+ tests (582 + 29 nuevos), 1735+ assertions
- Data Center: COMPLETADO (6 iteraciones, ~25h)
- Mission Control: 15/15 bugs + ruleta + brand unificado
- Bugs: BUG-001 a BUG-305, 226+ resueltos, 2 pendientes (BUG-111, BUG-127)
