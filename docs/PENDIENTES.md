# Pendientes — EventOS

> La UNICA fuente de verdad de lo que falta por hacer.
> Organizado por PRIORIDAD DE NEGOCIO, no por area tecnica.
> Filtro: "esto me acerca a cerrar el deal de septiembre con Eventos Efectivos?"
> Actualizado: 2026-04-26
> Backend: 611+ tests, 1735+ assertions
> Bugs: BUG-001 a BUG-305, 226+ resueltos, 2 pendientes (BUG-111, BUG-127)

---

## Modulos cerrados

> Detalle completo en `docs/COMPLETADO.md`. Aqui solo lista breve para no duplicar.

- Webhooks integracion partners (2026-04-21) — 24 tests, 5 bugs
- Live Moments Ruleta + Sorteo + Trivia (2026-04-23) — 41 tests, Platinum Gold, 35 bugs
- Event Pulse dashboard live (2026-04-24) — 20 tests, 30 bugs
- Concurso de Fotos + Golden Ticket (2026-04-24) — 36 tests, 10 bugs
- Data Center analytics (2026-04-26) — 9 tabs, 44 exports, 29 tests, 5 migraciones, 6 iteraciones
- Mission Control 15 bugs auditoria + ruleta + brand (2026-04-26) — BUG-291 a BUG-305

---

## P1 — Deuda tecnica de modulos recien cerrados

### QA Mission Control + Data Center

- [ ] Tests funcionales para Mission Control (~1.5h) — depende de mock de token HMAC `/monitor/{id}?token=...`
- [ ] Tests E2E flujos criticos: aprobar Q&A, lanzar game, cancelar sesion, scheduled export trigger
- [ ] Fix flaky test pre-existente `SessionLifecycleTest > cancel reverts delay on next session`
      (assertLessThan con timestamps iguales por ejecucion mismo segundo — usar assertLessThanOrEqual)

### Unificacion SPAs (deuda tecnica) — ~10-12h

> Cada SPA usa toasts/modales/colores/fetch wrappers diferentes. Crear biblioteca compartida.

- [ ] Crear `public/shared/` con `tokens.css` (variables Noir + Lux unificadas)
- [ ] Crear `public/shared/components.css` con .btn, .modal, .toast, .empty, .card consistentes
- [ ] Crear `public/shared/lib.js` con apiFetch(), toast(type), openModal(), helpers DOM
- [ ] Migrar Chat Monitor (`public/chat-monitor.html`) a Material Symbols + tokens unificados
- [ ] Migrar Attendance Check (`public/attendance-check.html`) — actualmente usa colores propios (#6366f1)
- [ ] Migrar Display Session (`public/display/session.html`) — alinear empty states
- [ ] Migrar Event Pulse (`public/event-pulse/`) — `--ink` → `--t` consistente con Noir/Lux

---

## P2 — Post-features (hacer DESPUES de terminar juegos/diferenciadores)

> Estos dependen de que features existan. Cada feature nuevo cambia que datos hay para analytics y que mostrar en el recap.
> (Filament dashboard ROI/engagement quedo cubierto por Data Center, ver `docs/COMPLETADO.md`.)

### Recap compartible (reemplaza certificado PDF tradicional) — 6-8h

- [ ] Card/story visual con stats personales del attendee: sesiones, conexiones, puntos, ranking, fotos
- [ ] Diseño para compartir en redes (Instagram story format, LinkedIn post format)
- [ ] Branding del evento integrado (logo, colores, nombre)
- [ ] Boton "Compartir mi experiencia" en pantalla post-evento
- [ ] Opcionalmente incluye certificado de asistencia (horas, sesiones) como dato dentro del recap
- [ ] Mas viral y moderno que un PDF aburrido que nadie comparte

---

## P3 — Web App (Bancolombia virtual)

> Bancolombia pidio webapp. La competencia ya presento una (fea). Sin esto perdemos ese deal.
> Ref: docs/WEB-APP-PLAN.md

- [ ] W.0-W.1: Setup Next.js 15 + Spatial UI (pill nav, paneles max 3, presets)
- [ ] W.2: Home + branding + countdown
- [ ] W.3: Agenda + favoritos
- [ ] W.4: Streaming + chat + Q&A + polls
- [ ] W.5: Speakers
- [ ] W.6: Social wall
- [ ] W.7: Sponsors
- [ ] W.8: Networking
- [ ] W.9: Encuestas
- [ ] W.10: Notificaciones
- [ ] W.11: Sockets RT
- [ ] W.12: Polish
- [ ] Command palette, paneles arrastrables, presets

---

## P4 — Admin Filament polish (el cliente lo va a usar)

> El organizador de Eventos Efectivos opera desde Filament. Si esta en ingles o desordenado, se ve amateur.

### BUG-268: Searchable selects rotos — URGENTE

- [ ] Todos los `Select::searchable()` en Filament no retornan resultados al buscar
- [ ] Afecta: Totems, Golden Tickets, Patrocinadores, y cualquier form con Select searchable
- [ ] Fix: auditar todos los Select en `app/Filament/Resources/`, agregar `->preload()` donde falte
- [ ] Si persiste, evaluar upgrade Filament (actual: v3.3.49)
- [ ] Ver BUG-LOG.md BUG-268

### Filament UI Enterprise

- [ ] Nivel 1: columns, labels espanol, secciones con icon/description, custom theme
- [ ] Nivel 2: Tabs por recurso (EventBranding, Gamification, Registration)
- [ ] Nivel 3: Wizards features complejos
- [ ] Nivel 4: Dashboard evento con stats (conecta con Analytics Dashboard de P0)

---

## P5 — Seguridad pre-produccion

> No bloquea demo pero si bloquea Bancolombia (enterprise = compliance).

- [ ] SEC-3.1: 2FA OTP — codigo 6 digitos por email
- [ ] SEC-3.2: Device fingerprinting — login nuevo fuerza 2FA
- [ ] Magic link login — token un solo uso 15 min
- [ ] Session management — ver/cerrar dispositivos

---

## P6 — Deploy + Infra + Stress

> Bloquea testing real pero no bloquea desarrollo de features.
> Stack migrado a DO sao1 consolidado (2026-04-25). Ver docs/PLAN-STRESS-TESTDO.md v2.1.

- [ ] SEC-4: Docker Compose, 2 Droplets DO sao1, Cloudflare WAF+LB, DO Managed MySQL+Redis, VPC privada
- [ ] SEC-5: Sentry, SecurityLogger, uptime monitoring (BetterStack)
- [ ] GitHub Actions CI/CD (blue-green deploy)
- [ ] EAS Build production (Android + iOS)
- [ ] 4 FIX Live Moments pre-stress: throttle game broadcasts, indices live_game_participants, cache getEligiblePool, HTTP connection pool (ver DISPONIBILIDAD-HA.md seccion 11)
- [ ] Stress test 10K: 9 tests formales (ver `docs/PLAN-STRESS-TESTDO.md` v2.1)
  - TEST 1-4: Warmup → 1K → Login stampede → 5K
  - TEST 5: Red degradada 4G Colombia
  - TEST 6: 10K 2h flujo natural (EL TEST PRINCIPAL)
  - TEST 7: Failover durante carga (matar Droplet-1)
  - TEST 8: Export aislado (VPS-3 no toca API)
  - TEST 9: Break point (escalar hasta romper)
- [ ] QA integridad funcional: smoke tests E2E + chaos testing
- [ ] Device real iOS + Android con Sentry Performance en 4G Bogota

### Optimistic UI (post-pendientes, pre-stress)

> Implementar DESPUES de cerrar P1-P5, ANTES del stress test.
> Plan completo en docs/OPTIMISTIC-UI-PLAN.md. Audit en docs/OPTIMISTIC-UI-AUDIT.md.
> (Haptic feedback, retry API, bug Q&A blocked words ya completados — ver `docs/COMPLETADO.md`.)

- [ ] Chat tempId + ack + estados progresivos (2-3h, cambio mobile + socket server)
- [ ] Emoji skip-self (30min, cambio socket server)
- [ ] Dedup wall:comment con socket broadcast (20min)
- [ ] Q&A upvote anti-parpadeo (15min)

---

## P7 — Landing Web (registro publico)

> Ultimo porque el registro puede hacerse por CSV/import hasta tener landing.

### Secciones

- [ ] Hero, Sobre el evento, Speakers, Agenda, Sponsors, Venue, Testimonios, Galeria, FAQ, Footer

### Registro embebido

- [ ] Form integrado, progressive profiling, social proof, CAPTCHA, rate limiting

### Post-registro

- [ ] Confirmacion web + QR descarga app

### Endpoints publicos

- [ ] GET /api/public/event/{slug} (datos, speakers, agenda, sponsors, faqs, registration-count)
- [ ] POST /api/public/event/{slug}/register (rate limited + CAPTCHA)

### Stack

- [ ] Next.js SSG/ISR o Astro, Tailwind, Framer Motion/GSAP, SEO/OG, Responsive, Dark/Light

---

## Pendientes menores de modulos cerrados (Room Check-in / Kiosk / Silent Disco)

- [ ] Kiosko: verificar scan endpoint < 100ms en VPS real (Linux)
- [ ] Staff app: cola offline MMKV + batch sync (nice-to-have)
- [ ] Silent disco push notification — verificar con dev build real

---

## Nice to have (NO hacer antes de cerrar deal septiembre)

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

## Pendientes consolidados por roadmap (auditoria 2026-04-26)

> Mapa unico de todos los `[ ]` reales que quedan en cada `docs/ROADMAP-*.md`.
> NO se borran de los roadmaps individuales — esta seccion es solo cross-reference.
> Roadmaps cerrados (sin pendientes reales): ROADMAP-DATA-CENTER (salvo deploy), ROADMAP-EVENT-PULSE, ROADMAP-LIVE-MOMENTS, ROADMAP-WEBHOOKS.

### ROADMAP-LIGHTMODE — Fase 7 + 8 (~4.5h)

- [ ] **Fase 7 backend** (~1.5h): migration `events.primary_color_light` (default #1A1B1E), Filament EventBranding doble color picker (Noir/Lux), API response incluye `primary_color_light`, App `useColorScheme()` lee el correcto, Profile toggle Auto/Light/Dark con persist
- [ ] **Fase 8 QA visual** (~3h): auditar 360dp/411dp en Noir/Lux, BlurView fallback Android, colores semanticos legibles en Lux (red/amber/green con sufficient contrast), fondos onboarding con tema correcto

### ROADMAP-KIOSK — verificaciones de produccion

- [ ] Scan endpoint < 100ms — verificar con VPS real (en local Windows da ~150ms, en Linux produccion estimado ~50ms)
- [ ] Push notification — verificar con dev build real (no Expo Go)
- [ ] Cola offline para staff scan (MMKV + batch sync) — solo si zonas sin WiFi son problema real

### ROADMAP-LUX-V2 — futuro (depende de Expo)

- [ ] Tab Bar polish con `@callstack/liquid-glass` cuando la libreria soporte Expo (actualmente solo iOS 26 nativo). Bloqueante: dependencia externa.

### ROADMAP-DATA-CENTER — solo deploy

- [ ] Deploy a VPS-3 (plan completo en `ROADMAP-DATA-CENTER.md` seccion "PLAN DE DEPLOY A VPS-3", DC-DEPLOY-1 a DC-DEPLOY-6)

### ROADMAP-UIUX-LANDING — Paso 6 parcial

> Paso 2 Landing Web ya esta en P7 (registro publico). Dashboard analytics quedo cubierto por Data Center.

- [ ] Paso 6 Admin Premium: configuracion canales (email/WhatsApp/SMS), preview landing en tiempo real, branded QR codes con logo
- [ ] Showcase demo (de Nice to have): panels clickeables, responsive 1920x1080, audio, hints, social wall

---

## Documentos de referencia

| Doc                                       | Contenido                                                        |
| ----------------------------------------- | ---------------------------------------------------------------- |
| `EventOS_Roadmap.md`                      | Fases, sesiones, timeline (v5.0)                                 |
| `docs/COMPLETADO.md`                      | Historial completo                                               |
| `docs/PLAN-TAGS-MODULOS.md`               | Plan tags + visibilidad modulos                                  |
| `docs/ROADMAP-UIUX-LANDING.md`            | Spec diseno landing + UI                                         |
| `docs/WEB-APP-PLAN.md`                    | Spec web app spatial UI                                          |
| `docs/ANALISIS-COMPETITIVO.md`            | Cotizaciones, gaps, pricing                                      |
| `docs/WHITE-LABEL.md`                     | App config dinamico                                              |
| `docs/FASE-SEGURIDAD.md`                  | Auditoria OWASP                                                  |
| `docs/DISPONIBILIDAD-HA.md`               | Arquitectura HA DO sao1, deploy, RT invalidation                 |
| `docs/BUG-LOG.md`                         | Bugs historicos                                                  |
| `docs/QA-MASTER.md`                       | Barrido endpoints                                                |
| `docs/PLAN-STRESS-TEST.md`                | Stress test v2.0 (Hetzner, referencia historica)                 |
| `docs/PLAN-STRESS-TESTDO.md`              | Stress test v2.1 (DO sao1 consolidado, plan definitivo)          |
| `docs/ROADMAP-LUX-V2.md`                  | Light mode completo                                              |
| `docs/CODEBASE-MAP.md`                    | Mapeo completo 3 repos: 150+ endpoints, socket events, observers |
| `docs/OPTIMISTIC-UI-AUDIT.md`             | 30 acciones auditadas, estado optimistic, gaps                   |
| `docs/GAPS-ANALYSIS.md`                   | Gaps detallados, dedup socket, coordinacion REST+socket          |
| `docs/OPTIMISTIC-UI-PLAN.md`              | Plan 3 semanas: 9 PRs, cross-cutting, metricas                   |
| `docs/BRIEF-CLAUDE-CODE-OPTIMISTIC-UI.md` | Brief original del audit optimistic UI                           |
| `docs/MODULOS.md`                         | 15 modulos + 6 sistemas + admin (v1.0)                           |
