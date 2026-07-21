# Pendientes — EventOS

> La UNICA fuente de verdad de lo que falta por hacer.
> Organizado por AREA tecnica, con prioridad de impacto dentro de cada una.
> Filtro post-pivote 2026-07-08: "¿esto acerca el demo desplegado y vendible
> para prospectos oct-nov?" (el deal Eventos Efectivos/Bancolombia se cayo).
> Actualizado: 2026-07-19 (limpieza: lo cerrado se movio a COMPLETADO.md)
> Bugs: BUG-001 a BUG-305, 226+ resueltos, 2 pendientes (BUG-111, BUG-127)

---

> **Lo hecho NO vive aca**: historial en `COMPLETADO.md`, estado por fase en
> `EventOS_Roadmap.md` (Estado rapido).

---

## 1. App movil (Expo) — features pendientes

### ⚠ WIP SIN COMMITEAR en el working tree de eventos-app (estado 2026-07-04)

> **Decision Kamilo 2026-07-20: DE ULTIMO en la cola** (es un estres; no
> bloquear el resto del saneamiento con esto). Sigue siendo obligatorio
> resolverlo ANTES de cualquier sesion que toque el Expo a fondo.
> Trabajo del recap a medio hacer, quedo en el working tree (NO commiteado
> porque rompe typecheck):
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

### Deudas Expo (auditoria sockets 2026-07-04)

- [ ] Verificacion viva en device del socket singleton: regresion streaming
      (chat/Q&A/polls/emojis/pinned) + wall + encuestas + log server `conns=1`
- Los fixes de codigo (ENTITY_KEYS `modules`, double-count comment) estan en
  **"Backlog Expo"** al final de este doc — sin duplicar aca.

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

## 2. Web App — pendientes (ventana: `PENDIENTES-WEBAPP.md`)

- [ ] QA presencial en device fisico (M.2-M.8 + B5 Fase C, ~2h con Kamilo)
- [ ] DEPLOY DEMO 0/6 (hosting, backend prod, Next prod, socket PM2, evento
      demo curado, Sentry DSN) — **prioridad estrategica post-pivote**

---

## 2.5 KNOWLEDGE BASE / Manual del organizador — **EN PAUSA (decision Kamilo 2026-07-19)**

> Se retoma al final, pre-produccion (primero bugs/funcionalidad/deploy).
> Ventana operativa: `docs/roadmaps/ROADMAP-MANUAL.md` (5/35 — quedan M1-M7:
> 22 paginas + capturas + deploy del sitio + links contextuales en admin).

- [ ] Al retomar: re-escribir el piloto de Encuestas con el tono aprobado
      (natural, humanizado, sin tecnicismos — feedback Kamilo) y producir
      M1-M7 contra el inventario blindado

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

## 4. Admin — residuales (backlog, no urgente)

- [ ] Configuracion canales WhatsApp/SMS desde Filament (email/SMTP ya existe;
      WhatsApp/SMS son nice-to-have seccion 9)
- [ ] Preview landing en tiempo real (depende de que exista la LANDING, secc. 3)
- [ ] Branded QR codes con logo

---

## 5. Seguridad del staff — **FRENTE ACTIVO** (decision Kamilo 2026-07-20)

> "Prefiero tener todo lo de seguridad en regla y no esperar a tener cliente
> encima con presion." Se hace AHORA, sin deal de por medio.
>
> **Ventana operativa: `docs/roadmaps/ROADMAP-SEGURIDAD-STAFF.md` (0/26)**
> — 2FA con app autenticadora (TOTP) obligatorio para todo el staff del admin
> + recuperacion + dispositivos de confianza + sesiones activas + registro de
> accesos. ~2-3 sesiones.
>
> **Desbloqueo**: SEC-3.1 llevaba aplazado desde abril por depender de
> WhatsApp Business API; TOTP elimina esa dependencia. El diseño SEC-3.1/3.2
> de `infra/FASE-SEGURIDAD.md` queda SUPERSEDED por el roadmap nuevo.
> Ya hecho y NO se rehace: lockout, socket rate limiting, FormRequests (~90%).

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

## 7. Stress test 10K — **DIFERIDO post-pivote 2026-07-08**

> El pivote saco el stress 10K del alcance (era validacion Bancolombia; hoy
> no hay cliente enterprise). Se reactiva SOLO si aparece un deal que lo
> exija. Ref: `docs/PLAN-STRESS-TESTDO.md` v2.1. Los "Optimistic UI
> restantes" y "Fixes pre-stress" de abajo siguen siendo mejoras validas
> pero NO bloquean el demo.

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
- [ ] Revisar la autenticacion del admin: como funciona + cuando expiran los tokens (nota Kamilo; encaja con el frente Seguridad del Staff)

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

### Paridad config admin ↔ 3 superficies (diseño con Kamilo; toca backend+Filament+Expo+webapp)
- [ ] **Enforcement de modulos en el Expo**: el panel /admin/modulos + rail
      webapp YA obedecen (F10, hecho); el grid del Expo sigue hardcodeado a 4
      modulos (agenda/speakers/social/sponsors) y documentos/banners/passport/
      pages/recap quedan huerfanos sin entrada. El HUD desafio si obedece.
- [ ] **Paginas custom: DEFERIDO POST-DEPLOY** (decision Kamilo 2026-07-20:
      "es para el final, no aporta ni detiene, no es dependencia"). El feature
      (iframes/HTML embebido: YouTube, Slido, mapa) tiene backend+API+detalle
      Expo pero NINGUNA superficie lo lista (huerfano ~60% construido); quedo
      EN PAUSA visible+deshabilitado en el admin (`4cd43d4`+`52f4522`). Al
      retomar: construir el acceso (Filament reactivar + lista Expo + modulo
      webapp ~1 sesion) o demoler entero. Costo mapeado, git preserva todo.
- [ ] **Keyvisual por superficie**: `keyvisual_desktop` + `keyvisual_mobile` en branding + 2 uploads Filament con preview.
- [ ] **Hero modo texto**: contrato unico de branding (type image|text) a escala en las 3 superficies.

> Huecos del barrido del manual 2026-07-19 CERRADOS 2026-07-20 (ver COMPLETADO):
> `/encuestas` fuera del rail (resuelto: canon 'aviso' + anuncio con targeting) ·
> LeadResource huerfano (demolido) · `/scanner-stand` (verificado, no era hueco).

### Event Pulse cliente — 0/1 (display propio del backend, NO webapp)
> Los 5 bugs del cliente saneados 2026-07-20 (`f53d8c8` + `c9439a8`). El motor
> `moments.js` v2 quedo VERIFICADO en vivo 2026-07-20 (sin bug, cache-bust OK).
> Solo queda:
- [ ] Decision cada-interaccion-hero (idea en `project_event_pulse_idea`)

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
