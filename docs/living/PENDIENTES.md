# Pendientes — EventOS

> La UNICA fuente de verdad de lo que falta por hacer.
> Organizado por AREA tecnica, con prioridad de impacto dentro de cada una.
> Filtro: "esto me acerca a cerrar el deal de septiembre con Eventos Efectivos?"
> Actualizado: 2026-04-26
> Backend: 611+ tests, 1735+ assertions
> Bugs: BUG-001 a BUG-305, 226+ resueltos, 2 pendientes (BUG-111, BUG-127)

---

## Modulos cerrados (referencia)

> Detalle completo en `docs/COMPLETADO.md`. Aqui solo lista breve.

- Webhooks integracion partners (2026-04-21) — 24 tests, 5 bugs
- Live Moments Ruleta + Sorteo + Trivia (2026-04-23) — 41 tests, Platinum Gold, 35 bugs
- Event Pulse dashboard live (2026-04-24) — 20 tests, 30 bugs
- Concurso de Fotos + Golden Ticket (2026-04-24) — 36 tests, 10 bugs
- Data Center analytics (2026-04-26) — 9 tabs, 44 exports, 29 tests, 5 migraciones, 6 iteraciones
- Mission Control 15 bugs auditoria + ruleta + brand (2026-04-26) — BUG-291 a BUG-305

---

## 1. App movil (Expo) — features pendientes

### ⚠ WIP SIN COMMITEAR en el working tree de eventos-app (estado 2026-07-04)

> Trabajo del recap a medio hacer, quedo en el working tree (NO commiteado
> porque rompe typecheck). Retomar ANTES de cualquier otra sesion Expo:
>
> - **Untracked:** `app/(app)/recap/`, `components/recap/`, `hooks/useRecap.ts`, `lib/recapColors.ts`
> - **Modificados:** `hooks/useAgenda.ts`, `useNetworking.ts`, `usePhotos.ts`, `useSponsors.ts`, `lib/api.ts`
> - **5 errores typecheck a resolver antes de commit:**
>   1. `app/(app)/leaderboard.tsx:1387-1388` — object literal con propiedades duplicadas (x2)
>   2. `components/recap/RecapCard.tsx:59` — `image_url` no existe en type `RecapData`
>   3. `components/social/ContestBanner.tsx:25` — `border` no existe en `ThemeTokens`
>   4. `components/ui/AttendanceCheckModal.tsx:24` — `useRef` espera 1 argumento
>
> El commit `0d9a754` (singleton socket, 2026-07-04) NO incluyo nada de esto —
> verificado con git stash que los errores son previos.

### Deudas Expo detectadas en auditoria sockets 2026-07-04

> Detalle: `docs/AUDITORIA-SOCKETS-SUPERFICIES-2026-07-04.md` + plan W.11 Seccion D.

- [x] ~~6 conexiones socket paralelas vs MAX_CONNECTIONS=5~~ — **RESUELTO** `0d9a754`: `lib/socket.ts` singleton + 6 consumidores migrados. **Pendiente verificacion viva en device**: regresion streaming (chat/Q&A/polls/emojis/pinned) + wall + encuestas + log server `conns=1`
- [ ] `ENTITY_KEYS` no mapea entity `modules` (backend la emite, Expo la pierde) — agregar `modules: ['modules']` en `useDataInvalidation.ts`
- [ ] Double-count de comment propio (optimistic +1 + socket +1 en `useWall`) — la webapp ya no lo hereda; fix Expo pendiente

### Event Pulse — bugs del CLIENTE destapados en QA vivo 2026-07-04 (sesion dedicada ~1-2h) — 0/4

> Los emits backend YA funcionan (GAP-C verificado: ratings/connections/leads/leaderboard
> llegan al Pulse en <2s — server log con 9 ratings reales). Lo roto es el refresh
> client-side de `public/event-pulse/` (construido en abril, validado solo con simulador).

- [ ] **Formula inconsistente del counter ratings**: `socket.js refreshStat` suma counts
      del top-6 sesiones del endpoint `/ratings`, pero el bootstrap (F5) usa
      `stats.ratings` (total real). Live y F5 dan numeros distintos — unificar a total
      (endpoint deberia exponer total, o refreshStat usar bootstrap parcial)
- [ ] **Charlas vacia**: `PulseController:102` exige `whereNotNull('room_id')` — sesiones
      sin sala no aparecen ("no hay sesiones programadas" con el LiveHubDemoSeeder).
      Decidir: relajar filtro o exigir salas en eventos reales (y que el seeder las asigne)
- [ ] Verificar mismo patron de formula en counters leads/connections (refreshStat
      calcula desde endpoints parciales)
- [ ] Menor: `poll:closed` emite a `room=session:null` cuando el poll scope=session
      no tiene session_id (visto en server log — payload del PollController)

### Recap compartible — post-evento (~26h, 3-4 dias) — 0/75

> Plan completo: `docs/ROADMAP-RECAP.md` (DaVinci mode, con tests + refs).
> Solo app movil en Fase 1. Webapp consume mismo backend en Fase 2 (post Web App Bancolombia).

- [ ] Fase 0: Investigacion + refs visuales (~2h) — 0/8
- [ ] Fase 1: Backend datos (RecapService + API + tests) (~4h) — 0/12
- [ ] Fase 2: Backend imagen Browsershot + R2 (~4h) — 0/10
- [ ] Fase 3: Backend email + URL firmada + trigger (~4h) — 0/13
- [ ] Fase 4: App pantalla recap nativa (~7h) — 0/15
- [ ] Fase 5: App distribucion (push, deeplink, banner, lista) (~2.5h) — 0/8
- [ ] Fase 6: QA + edge cases + tests E2E (~3h) — 0/9

### Pendientes menores app (Kiosk / Silent disco)

- [ ] Kiosko: verificar scan endpoint < 100ms en VPS real (en local Windows ~150ms, Linux produccion estimado ~50ms)
- [ ] Staff app: cola offline MMKV + batch sync (nice-to-have, solo si zonas sin WiFi son problema real)
- [ ] Silent disco push notification — verificar con dev build real (no Expo Go)

### Lux V2 — futuro (bloqueante externo)

- [ ] Tab Bar polish con `@callstack/liquid-glass` cuando la libreria soporte Expo (actualmente solo iOS 26 nativo)

---

## 2. Web App (Bancolombia virtual) — ~132h, ~30-34 dias — 0/18

> Bancolombia pidio webapp. La competencia ya presento una. Sin esto perdemos ese deal.
> Ref: `docs/webapp/PLAN.md` (master) + 4 specs + 18 roadmaps modulares.
> Stack: Next.js 15 + TypeScript + Tailwind + shadcn/ui + Framer Motion + TanStack Query + Socket.IO + Zustand.
> Repo: `eventos-web` (separado). Deploy: DO sao1. Auth: Bearer Sanctum + magic link principal.
> **Auditoria 2026-05-01:** Modulos W.13-W.17 agregados tras comparar con app movil. Submodulos faltantes en W.2/W.3/W.4/W.5/W.6/W.8/W.9/W.10 expandidos.

### Cimientos (2 sesiones)
- [ ] W.1: Setup Next.js + Auth (magic link + email/password) + i18n + tour bienvenida (~10h)
- [ ] W.0: Spatial UI System (PanelManager, PillBar, presets, command palette, drag) (~12h)

### Modulos core asistente virtual (10 sesiones)
- [ ] W.2: Home (hero, countdown, happening now, GamificationHud, recap banner, anuncios mini, post-event survey, EventArchive) (~9h)
- [x] W.3: Agenda (lista, filtros, favoritos, .ics download, ratings + my-ratings backend wired) (~11h) — **DONE 2026-05-06**
- [ ] W.4: Streaming (Vimeo + Q&A + chat + polls + Trivia + anuncios in-stream + replay) (~14h)
- [ ] W.5: Speakers (directorio, ratings en lista, perfil, favoritos) (~5h)
- [ ] W.6: Social Wall (feed, Stories, Hashtags, Photo Contest, Memorias) (~10h)
- [ ] W.7: Sponsors (Brand Wall, Profile, lead capture, trivia) (~7h)
- [ ] W.8: Networking (matchmaking, sent/received, bookmarks, blocked) (~7h) — chat 1:1 descartado 2026-05-06 (WhatsApp/email/LinkedIn cubren)
- [ ] W.9: Engagement (encuestas, leaderboard, badges, passport, rewards, prizes, streak) (~10h)
- [ ] W.10: Hub Personal (notif, perfil, settings, Mi QR, Mis Stands, Mis Redenciones, Mis Prizes, Mi Recap, Soporte access, Cambiar evento) (~8h)

### Modulos transversales (1 sesion)
- [ ] W.11: Sockets RT (Socket.IO, dedup, long-polling fallback, 4 capas RT invalidation) (~6h)

### Modulos nuevos (auditoria)
- [ ] W.13: FAQ + Documentos + Pages dinamicas (~3h)
- [ ] W.14: Anuncios + Boletines (anuncios urgentes, banners rotativos, highlights) (~3h)
- [ ] W.15: Vendor Dashboard (Mi Stand, Leads, Stats, Team) **— OPCIONAL Fase 1** (~6h)
- [ ] W.16: Live Moments subset web (Trivia engine, Sorteo Ceremony display, Concurso Fotos, Golden Ticket reveal) (~6h)
- [ ] W.17: Soporte (tickets, chat staff, RT) (~3h)

### Cierre (1 sesion)
- [ ] W.12: Polish + E2E + PWA (responsive final, transiciones, loading states, install prompt condicional) (~8h)

**Decisiones cerradas (2026-05-01):** Vimeo streaming, DO sao1 deploy, magic link principal, i18n es-CO/en/pt-BR, PWA install prompt condicional (desktop/tablet only), responsive con 3 disenios dedicados por viewport. Detalle en `docs/webapp/DECISIONS.md`.

**Backend bloqueante (~3-4h)**: Endpoints `POST /api/v1/auth/magic-link` + `POST /api/v1/auth/verify-magic-link` + `POST /api/v1/auth/refresh`. Se hace en sesion separada o paralela a W.1.

**Auditoria submodulos vs app movil:** Reporte completo en `docs/webapp/PLAN.md`. 39 pantallas movil + 37 hooks revisados. Items presenciales (scanner camara, QR badge, staff tools) confirmados como solo movil.

---

## 2.5 KNOWLEDGE BASE / Manual del organizador (decision Kamilo 2026-07-19)

> "Falta un gran instructivo de todo — como crear un asistente, importar,
> crear evento, como funcionan los modulos, el webhook, lo que se puede y
> no hacer." NO se arranca ya; pegado al DEPLOY DEMO (el manual es parte
> del demo comercial y escribirlo caza huecos — hoy destapo Paginas).

- [ ] **Fuente unica Markdown** (repo, versionada): un doc por modulo con
      esqueleto fijo — que es · como se configura (admin) · que ve el
      asistente (app/webapp) · lo que se puede y NO · gotchas. El 80% se
      escribe DESDE EL CODIGO con procedencia (triggers de emails,
      catalogo acciones gamificacion, eventos+payloads de webhooks del
      WebhookDispatchService, rate limits, enforcement de modulos).
- [ ] Sitio estatico barato (Starlight/VitePress) para servirlo.
- [ ] Link contextual "¿Como funciona?" en cada modulo del admin → su
      pagina del manual.
- [ ] Estimacion honesta: 2-3 sesiones para v1 (~20 modulos + admin +
      wizard + roles/despliegue), con capturas del admin real.

## 3. Landing Web (registro publico)

> Ultimo en orden porque el registro puede hacerse por CSV/import hasta tener landing.
> Ref: `docs/ROADMAP-UIUX-LANDING.md`

### Secciones

- [ ] Hero, Sobre el evento, Speakers, Agenda, Sponsors, Venue, Testimonios, Galeria, FAQ, Footer

### Registro embebido

- [ ] Form integrado, progressive profiling, social proof, CAPTCHA, rate limiting

### Widget de registro embebible (decision Kamilo 2026-07-18)

> Registro insertable en CUALQUIER sitio de terceros (pagina del organizador,
> intranet corporativa), no solo en nuestra landing.
> **Prior art interno**: patron `/dc-embed/{token}` del Data Center (embed con
> token + tabla dc_embed_tokens) y campo `allowed_embed_domains` que YA existe
> en el modelo Event — la arquitectura esta semi-prevista.

- [ ] Script/iframe embebible `<script src=".../widget.js" data-event="slug">` con token
- [ ] Validacion de dominio contra allowed_embed_domains + rate limit + CAPTCHA
- [ ] Tema del widget: hereda branding del evento (accent/logo), Noir/Lux auto
- [ ] Config en admin: cluster Entrada → tab "Widget" (generar snippet + dominios)

### Post-registro

- [ ] Confirmacion web + QR descarga app

### Endpoints publicos

- [ ] GET /api/public/event/{slug} (datos, speakers, agenda, sponsors, faqs, registration-count)
- [ ] POST /api/public/event/{slug}/register (rate limited + CAPTCHA)

### Stack

- [ ] Next.js SSG/ISR o Astro, Tailwind, Framer Motion/GSAP, SEO/OG, Responsive, Dark/Light

---

## 4. Admin Filament — polish (el cliente lo va a usar)

> El organizador de Eventos Efectivos opera desde Filament. Si esta en ingles o desordenado, se ve amateur.

### Filament UI Enterprise

> Plan completo en `docs/ROADMAP-FILAMENT-PULIDO.md`.

- [ ] **Event Switcher global** (~3-4h) — topbar widget con Select de evento activo, default automatico al primer evento activo, todos los Resources/Pages respetan `session('filament_event_id')`, quitar Selects "Evento" duplicados de forms. URGENTE para UX profesional
- [ ] Nivel 1: columns, labels espanol, secciones con icon/description, custom theme
- [ ] Nivel 2: Tabs por recurso (EventBranding, Gamification, Registration)
- [ ] Nivel 3: Wizards features complejos
- [ ] Nivel 4: Dashboard evento con stats — SUPERADO por Data Center

### Admin Premium (UIUX-LANDING Paso 6)

- [ ] Configuracion canales (email/WhatsApp/SMS) desde Filament
- [ ] Preview landing en tiempo real
- [ ] Branded QR codes con logo

---

## 5. Seguridad pre-produccion

> No bloquea demo pero si bloquea Bancolombia (enterprise = compliance).
> Ref: `docs/FASE-SEGURIDAD.md`

- [ ] SEC-3.1: 2FA OTP — codigo 6 digitos por email
- [ ] SEC-3.2: Device fingerprinting — login nuevo fuerza 2FA
- [ ] Magic link login — token un solo uso 15 min
- [ ] Session management — ver/cerrar dispositivos

---

## 6. Deploy + Infra

> Bloquea testing real pero no bloquea desarrollo de features.
> Stack DO sao1 consolidado. Ref: `docs/DISPONIBILIDAD-HA.md` y `docs/PLAN-STRESS-TESTDO.md` v2.1.

- [ ] SEC-4: Docker Compose, 2 Droplets DO sao1, Cloudflare WAF+LB, DO Managed MySQL+Redis, VPC privada
- [ ] SEC-5: Sentry, SecurityLogger, uptime monitoring (BetterStack)
- [ ] GitHub Actions CI/CD (blue-green deploy)
- [ ] EAS Build production (Android + iOS)
- [ ] Data Center: deploy VPS-3 worker headless (plan en `ROADMAP-DATA-CENTER.md` DC-DEPLOY-1 a DC-DEPLOY-6)
- [ ] Read replica MySQL + R2 storage para exports

---

## 7. Stress test 10K (Bancolombia validation)

> Despues de Deploy + Infra. Ref: `docs/PLAN-STRESS-TESTDO.md` v2.1.

### Optimistic UI restantes (~3-4h) — pre-stress polish

> Plan completo en `docs/OPTIMISTIC-UI-PLAN.md`. Audit en `docs/OPTIMISTIC-UI-AUDIT.md`.
> Implementar DESPUES de cerrar features de negocio (app + webapp), ANTES del stress test.

- [ ] Chat tempId + ack + estados progresivos (2-3h, cambio mobile + socket server)
- [ ] Emoji skip-self (30min, cambio socket server)
- [ ] Dedup wall:comment con socket broadcast (20min)
- [ ] Q&A upvote anti-parpadeo (15min)

### Fixes pre-stress (Live Moments)

- [ ] Throttle game broadcasts (ver DISPONIBILIDAD-HA.md seccion 11)
- [ ] Indices `live_game_participants`
- [ ] Cache `getEligiblePool`
- [ ] HTTP connection pool

### 9 tests formales

- [ ] TEST 1-4: Warmup → 1K → Login stampede → 5K
- [ ] TEST 5: Red degradada 4G Colombia
- [ ] TEST 6: 10K 2h flujo natural (EL TEST PRINCIPAL)
- [ ] TEST 7: Failover durante carga (matar Droplet-1)
- [ ] TEST 8: Export aislado (VPS-3 no toca API)
- [ ] TEST 9: Break point (escalar hasta romper)

### QA en device real

- [ ] Smoke tests E2E + chaos testing
- [ ] iOS + Android con Sentry Performance en 4G Bogota

---

## 8. Deuda tecnica

### QA Mission Control + Data Center

- [ ] Tests funcionales para Mission Control (~1.5h) — depende de mock de token HMAC `/monitor/{id}?token=...`
- [ ] Tests E2E flujos criticos: aprobar Q&A, lanzar game, cancelar sesion, scheduled export trigger
- [ ] Fix flaky test pre-existente `SessionLifecycleTest > cancel reverts delay on next session` (assertLessThan con timestamps iguales — usar assertLessThanOrEqual)
toca revisar la autenticacion aca como funciona y tener claro los token cuando expiran etc 

### Unificacion SPAs (~10-12h)

> Cada SPA usa toasts/modales/colores/fetch wrappers diferentes. Crear biblioteca compartida.

- [ ] Crear `public/shared/tokens.css` (variables Noir + Lux unificadas)
- [ ] Crear `public/shared/components.css` con .btn, .modal, .toast, .empty, .card consistentes
- [ ] Crear `public/shared/lib.js` con apiFetch(), toast(type), openModal(), helpers DOM
- [ ] Migrar Chat Monitor (`public/chat-monitor.html`) a Material Symbols + tokens unificados
- [ ] Migrar Attendance Check (`public/attendance-check.html`) — actualmente usa colores propios (#6366f1)
- [ ] Migrar Display Session (`public/display/session.html`) — alinear empty states
- [ ] Migrar Event Pulse (`public/event-pulse/`) — `--ink` → `--t` consistente con Noir/Lux

---

## 9. Nice to have (NO hacer antes de cerrar deal septiembre)

> Mover a activo solo si un cliente lo pide explicitamente.

### App movil — cosmetico/incremental

- [ ] Racha de visitas a la app (streak gamification — dia consecutivo = bonus puntos)
- [ ] Orbe FAQ a Skia shader (reemplazar Reanimated+BlurView, solo cosmetic)
- [ ] Venue + Mapa (depende de si el evento tiene plano)
- [ ] react-native-image-crop-picker: crop circular dark

### Registro & Acceso avanzado

- [ ] Waitlist (cuando max_attendees se llena)
- [ ] Referral tracking
- [ ] Social login (Google)

### Comunicacion avanzada

- [ ] WhatsApp Business API (ICE360 lo tiene por $850K COP — evaluar si vale la pena)
- [ ] SMS fallback
- [ ] Email builder visual (Fase 2+)

### Post-evento

- [ ] Networking follow-up ("Conectaste con X personas")
- [ ] Highlight reel (collage automatico fotos)
- [ ] Event replay (grabaciones post-evento)

### Seguridad avanzada

- [ ] Anomaly detection — alertar admin (Fase 2+)
- [ ] Backup/Restore de evento (snapshot JSON)

### Platform Health — Dashboard interno

- [ ] Dashboard interno: salud plataforma RT
- [ ] Health por modulo: API, Socket, Redis, MySQL, Queue
- [ ] Metricas: requests/sec, latencia, memoria
- [ ] Stack: Laravel Pulse + Sentry

### Documentacion

- [ ] Documentar arquitectura socket
- [ ] White-label: migrar app.json → app.config.js + estructura clients/

### Features opcionales

- [ ] Wallet digital (.pkpass + Google Wallet)
- [ ] Digital signage (pantallas venue)
- [ ] Badge printing fisico
- [ ] Landing builder Filament (Fase 2+)
- [ ] A/B testing emails (Fase 2+)
- [ ] Juegos Unity en stands (requiere dev Unity separado)

### Showcase / Demo inversor

- [ ] Panels clickeables, responsive 1920x1080, audio, hints, social wall

### Fase 3 — SaaS + Monetizacion

- [ ] Multi-tenant, Stripe, Data export GDPR, Juegos Unity bridge
- [ ] Multi-location con tracks (Bancolombia: Colombia + Panama)

---

## Webapp — paralelos / backlog (movido de PENDIENTES-WEBAPP.md 2026-07-14)

> Estas secciones vivian en `docs/living/PENDIENTES-WEBAPP.md` pero NO son features de
> webapp Fase 1 (que ya esta cerrada: W.0-W.18 + W.X). Se movieron aca para que el doc de
> webapp quede solo con lo de webapp (features + QA + deploy). Ninguno bloquea el cierre.

### Paridad config admin ↔ 3 superficies — 1/3 (diseño con Kamilo; toca backend+Filament+Expo+webapp)
- [x] **Modulos fuente unica** (PARCIAL 2026-07-19, F10 Lumina Admin): panel
      /admin/modulos con catalogo canon + invalidacion RT; el RAIL de la
      webapp ya obedece (oculta apagados, eventos-web 7f22593). Falta el
      enforcement del grid del Expo y del resto de superficies (Expo aun
      hardcodea; el HUD desafio si obedece).
- [ ] **Paginas custom: acceso real en las apps o demolicion** (decision
      aplazada de F10.6, 2026-07-19): el feature (iframes/HTML embebido:
      YouTube, Slido, mapa) tiene backend+API+detalle Expo pero NINGUNA
      superficie lo lista — quedo EN PAUSA visible+deshabilitado en el admin
      (backend `4cd43d4`+`52f4522`). Construir el listado (~1 sesion: modulo
      webapp + lista Expo + entrada al catalogo canon) o demoler entero
      (incluye release Expo).
- [ ] **Keyvisual por superficie**: `keyvisual_desktop` + `keyvisual_mobile` en branding + 2 uploads Filament con preview.
- [ ] **Hero modo texto**: contrato unico de branding (type image|text) a escala en las 3 superficies.

### Event Pulse cliente — 0/4 (display propio del backend, NO webapp)
> EP esta COMPLETO (ver `project_event_pulse_complete` en memoria); solo queda: formula counter
> ratings live≠F5 · Charlas vacia (room_id PulseController:102) · verificar leads/connections ·
> poll:closed room null · + diagnosticar cache del `moments.js` v2 + decision cada-interaccion-hero.

### Backlog Expo (sesion Expo futura)
- [ ] Borrar `banners.tsx` + `BannerCarousel` + `bannersApi` del Expo (feature legacy muerta)
- [ ] `ENTITY_KEYS` sin `modules` (backend la emite, Expo la pierde)
- [ ] Double-count comment propio en `useWall` (Expo)
- [ ] Portar "click sesion → agenda highlight" del webapp W.5 al Expo
- [ ] Validar si `banners.tsx` Expo es vista dedicada o solo carousel embebido
- [ ] Decidir recap/[eventId] del Expo (→ Fase 2, no mapeado a webapp)

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

---

## Documentos de referencia

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
