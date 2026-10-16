# Pendientes — EventOS

> SOLO lo abierto, por area tecnica. La ventana del dia a dia es `PENDIENTES-WEBAPP.md` (estado general en %).
> Lo cerrado se mueve a `COMPLETADO.md` al cerrar (limpieza 2026-10-16).
> Actualizado: 2026-10-16.
> Bugs: BUG-001 a BUG-353. Abiertos: los 5 de infraestructura del 2026-09-13 (BUG-344 a 348; BUG-345 con
> codigo en B3.1, falta medirlo). Detalle: `BUG-LOG.md`.

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

### Recap compartible — post-evento — IMPLEMENTADO

> Existe en backend (`RecapService.php`, `RecapImageRenderer.php`, `RecapController.php`, `RecapVerifyController.php`)
> y en la app. Plan: `docs/ROADMAP-RECAP.md`.

- [ ] E2E con Chromium en el servidor + validacion visual en dev build
- [ ] Commitear el trabajo del recap que sigue en el working tree del Expo (ver arriba, rompe typecheck)

### Pendientes menores app (Kiosk / Silent disco)

- [ ] Kiosko: verificar scan endpoint < 100ms en VPS real (en local Windows ~150ms, Linux produccion estimado ~50ms)
- [ ] Staff app: cola offline MMKV + batch sync (nice-to-have, solo si zonas sin WiFi son problema real)
- [ ] Silent disco push notification — verificar con dev build real (no Expo Go)

### Lux V2 — futuro (bloqueante externo)

- [ ] Tab Bar polish con `@callstack/liquid-glass` cuando la libreria soporte Expo (actualmente solo iOS 26 nativo)

---

## 2. Web App — pendientes (ventana: `PENDIENTES-WEBAPP.md`)

- [ ] QA presencial en device fisico (M.2-M.8 + W.12 Fase C, ~2h con Kamilo)

---

## 2.5 KNOWLEDGE BASE / Manual del organizador — **EN PAUSA (decision Kamilo 2026-07-19)**

> Se retoma al final, pre-produccion (primero bugs/funcionalidad/deploy).
> Ventana operativa: `docs/roadmaps/ROADMAP-MANUAL.md` (5/35 — quedan M1-M7:
> 22 paginas + capturas + deploy del sitio + links contextuales en admin).

- [ ] Al retomar: re-escribir el piloto de Encuestas con el tono aprobado
      (natural, humanizado, sin tecnicismos — feedback Kamilo) y producir
      M1-M7 contra el inventario blindado

## 3. Landing Web (registro publico) — 26/32

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
- [ ] Credencial con etiqueta visible (VIP, Prensa): en el admin marcar que tags salen en la escarapela (idea Kamilo 2026-10-11, despues)
- [ ] "Ya tenias tu lugar" con varios eventos: hoy sale tambien si el correo tiene cuenta en OTRO evento y no en este
- [ ] Tarjeta al compartir cuando el evento NO tiene "Card del Home": hoy sale sin imagen. Generarla (nombre, fecha, lugar,
  color del evento) necesita lab antes
- [ ] En produccion, probar la tarjeta en WhatsApp, LinkedIn y X con la URL real (en local la imagen no es publica)
- [ ] Revisar la landing en iPad con Kamilo (empezada 2026-10-12)

---

## 4. Admin — residuales (backlog, no urgente)


- [ ] Configuracion canales WhatsApp/SMS desde Filament (email/SMTP ya existe;
      WhatsApp/SMS son nice-to-have seccion 9)
- [ ] Branded QR codes con logo

---

## 5. Seguridad del staff — 25/26

> 2FA, equipos de confianza, sesiones abiertas, registro de accesos y bloqueo HECHOS (2026-09-29).
> Ventana: `docs/roadmaps/ROADMAP-SEGURIDAD-STAFF.md`. Raya de Kamilo: la URL del admin no sale sin esto.

- [ ] S.7.2 Correr `security:check` contra el `.env` real de produccion (va con el montaje)
- [ ] Passkeys (bloque S.9, ampliacion 2026-08-01) — sin fecha

---


## 6. Deploy + Infra

> Bloquea testing real pero no bloquea desarrollo de features.
> **2026-10-16:** el plan vigente es B1-B6 de `ROADMAP-INFRAESTRUCTURA.md`; esta lista es la de abril.
> Stack DO sao1 consolidado. Ref: `docs/DISPONIBILIDAD-HA.md` y `docs/PLAN-STRESS-TESTDO.md` v2.1.

- [ ] SEC-4: Docker Compose, 2 Droplets DO sao1, Cloudflare WAF+LB, DO Managed MySQL+Redis, VPC privada
- [ ] SEC-5: Sentry, SecurityLogger, uptime monitoring (BetterStack)
- [ ] GitHub Actions CI/CD (blue-green deploy)
- [ ] EAS Build production (Android + iOS)
- [ ] Data Center: deploy VPS-3 worker headless (plan en `ROADMAP-DATA-CENTER.md` DC-DEPLOY-1 a DC-DEPLOY-6)
- [ ] Read replica MySQL + R2 storage para exports

---

## 7. Stress test — EJECUTADO 2026-08-01 (`docs/infra/INFORME-STRESS-2026-08-01.md`)


### Quedo sin correr

- [ ] D.4.4 prueba mixta: HTTP + sockets + `stress-admin.js` a la vez (el
      admin trabajando mientras el evento corre — el escenario mas realista)
- [ ] Prueba de red degradada 4G Colombia (TEST 5 del plan v2.1): todo se
      midio dentro del mismo centro de datos a proposito, para saturar el
      servidor y no medir el cable

## 7b. Stress test 10K — sigue DIFERIDO

> **Kamilo revierte el diferido del 2026-07-08**: "necesito ya medir esto, lo
> postergamos mucho tiempo". Y define el alcance: **NO a medias — se monta
> como si fuera produccion** (dominio, HTTPS, Cloudflare, R2, SMTP, webapp
> Next incluida), se mide, y se destruye. Razon: dejar piezas fuera solo
> genera pendientes futuros ("mas adelante: ay, falto esto").
>
> **Ventana de ejecucion: `docs/roadmaps/ROADMAP-DEPLOY-STRESS.md`.**
> Ref de arquitectura: `docs/infra/PLAN-STRESS-TESTDO.md` v2.1 (el HA de 2
> droplets sigue siendo el objetivo del test de 10K real, no el de hoy).
>
> **Kit de carga YA EXISTE** en `eventos-backend/tests/load/`: k6
> (`stress-full.js` 5000 personas / 9 escenarios, `stress-admin.js`,
> `stress-local.js`) + artillery Socket.IO (`stress-sockets.yml`) +
> `setup-tokens.php`. No se reescribe.

### Bloqueantes cazados 2026-08-01 (arreglar ANTES de medir, o el numero miente)

- [ ] **ANOTADO 2026-09-13: hoy tiene 4 tokens; el recorrido vigente es `entrar-por-la-puerta.js` por la webapp, revisar si este kit sigue en uso.** **`tests/load/tokens.json` tiene ~10 usuarios** y el script reparte
      tokens en rueda: 5000 VUs golpearian las mismas 10 cuentas
      (rate limits + caches por persona = resultado inventado). Sembrar
      miles de asistentes antes de correr.

### Optimistic UI restantes (~3-4h) — pre-stress polish

> Plan completo en `docs/OPTIMISTIC-UI-PLAN.md`. Audit en `docs/OPTIMISTIC-UI-AUDIT.md`.
> Implementar DESPUES de cerrar features de negocio (app + webapp), ANTES del stress test.

- [ ] Chat tempId + ack + estados progresivos (2-3h, cambio mobile + socket server)
- [ ] Emoji skip-self (30min, cambio socket server)
- [ ] Dedup wall:comment con socket broadcast (20min)
- [ ] Q&A upvote anti-parpadeo (15min)

### Fixes pre-stress (Live Moments)

- [ ] Throttle game broadcasts (ver DISPONIBILIDAD-HA.md seccion 11)
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
- [ ] **Reglas del hero** — la decision "cada-interaccion-hero si/no" quedo
      RESUELTA 2026-08-01 y reencuadrada por Kamilo: el hero se resuelve por
      REGLAS que AGREGAN ("los ultimos 10 ingresos" en una sola tarjeta), no una
      tarjeta por interaccion — y **el lead entra** como tarjeta individual.
      Ya no es un si/no: es pieza propia (diseño de tarjetas → aprobacion →
      codigo). Detalle en [[project_event_pulse_complete]].

### Backlog Expo (sesion Expo futura)
- [ ] Borrar `banners.tsx` + `BannerCarousel` + `bannersApi` del Expo (feature legacy muerta)
- [ ] `ModuleMenuCompact.tsx` huerfano: importado en `(tabs)/index.tsx:25` pero nunca
      renderizado (hallazgo 2026-08-01). Borrar componente + import, o darle uso.
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
