# Web App — Master Plan

> Plan maestro de la web app para asistentes virtuales. Es la **experiencia primaria** del publico remoto, no un complemento de la app movil.
>
> **Estado:** En planeacion — sesion 2026-05-01.
> **Stack:** Next.js 15 (App Router) + TypeScript + Tailwind + shadcn/ui + Framer Motion + TanStack Query + Socket.IO client + Zustand.
> **Repo:** `eventos-web` (separado).
> **Deploy:** DO sao1 (mismo VPC que backend).
> **Dominio:** `app.eventos.app` (asistente) — admin Filament en `admin.eventos.app`.

---

## Por que webapp y no solo app movil

El asistente virtual esta sentado frente a su computadora — el celular lo usa para otras cosas. La experiencia del evento en el browser es la experiencia primaria para asistentes remotos. Cisco Webex Events e ICE360 (la competencia directa) son web-first.

Bancolombia pidio webapp. La competencia ya presento una. **Sin esto perdemos el deal.**

La web app NO es un complemento de la app movil. Es la experiencia completa para el publico virtual. La app movil agrega las interacciones presenciales (QR check-in, escaneo de leads, passport stamps).

---

## Decisiones cerradas (2026-05-01)

Detalle completo en `DECISIONS.md`. Resumen:

| Tema | Decision |
|---|---|
| Repo | `eventos-web` separado (no monorepo) |
| Stack | Next.js 15 + Tailwind + shadcn/ui + Framer Motion + TanStack Query + Zustand + Socket.IO client |
| Auth | Bearer token Sanctum (Opcion A) — mismo backend que app movil. Tokens en httpOnly cookie via API route Next |
| Login principal | **Magic link** (token un solo uso 15 min) + email/password fallback |
| Subdominio | `app.eventos.app` (asistente) / `admin.eventos.app` (Filament) |
| Streaming | **Vimeo** (mismo provider que app movil — vendible a Bancolombia) |
| PWA | **Si**, install prompt condicional: desktop/tablet muestra prompt, mobile NO (no canibaliza app nativa) |
| Deploy | DO sao1 mismo VPC que backend |
| i18n | `next-intl` desde W.1 — es-CO (default) + en + pt-BR |
| Responsive | **No automatico Tailwind** — 3 disenios dedicados por viewport (ver `RESPONSIVE-SPEC.md`) |
| Tokens | Lumina Noir portado desde `eventos-app/lib/theme-noir.ts` (no del demo HTML) |
| Tema | Noir + Lux ambos — primary color dinamico desde `branding` del evento |
| Tracking session | localStorage para layout preferido del usuario |
| Testing | Playwright E2E + Vitest unit — cada modulo cierra con happy path + 1 edge case minimo |
| Sentry | Si — frontend + backend |
| Compatibilidad | Chrome/Edge ultimas 2 + Safari 16+ + Firefox 115+. NO IE11 |

---

## Spatial UI System (W.0)

La web app NO usa sidebar corporativa. Usa un sistema de paneles spatial inspirado en visionOS pero adaptado a Lumina Noir.

### Concepto core

- **Pill bar flotante** minimalista (posicion superior — confirmada en W.0 con refs visuales nuevas, no del demo)
- **Sistema de paneles con jerarquia**: max 3 paneles simultaneos (1 primario + 2 secundarios)
- Abrir un panel nuevo desplaza al de menor jerarquia con animacion spring
- Layout se redistribuye automaticamente segun contexto
- Ejemplo: click "unirse a sesion" → agenda se contrae, aparece player + chat

### Reglas de diseno

| Regla | Detalle |
|---|---|
| Max 3 ventanas | Si abres una cuarta, la de menor prioridad desaparece con transicion |
| Jerarquia clara | El panel primario domina el espacio, secundarios se adaptan |
| Transiciones spring | Animaciones spring/damping, nunca lineales |
| No sidebar | Nunca sidebar lateral tradicional |
| No PiP | No picture-in-picture (se siente como parche) |

### Features del sistema spatial

| Feature | Detalle |
|---|---|
| Paneles arrastrables | Usuario reacomoda paneles a su gusto (`@dnd-kit/core`) |
| Presets de layout | "Conferencia" (player+chat), "Networking" (chat+perfiles+matches), "Explorar" (agenda+speakers+mapa) |
| Memoria de layout | Recuerda combo preferido (localStorage) |
| Happening Now persistente | Siempre visible, pulsa sutil cuando sesion por empezar |
| Command palette | Cmd+K / Ctrl+K — navegar a cualquier seccion |

### Estilo visual

Lumina Noir solido (no glass de entorno real). Paneles con bordes sutiles + opacidad controlada. Glass solo en piezas premium (max 3-4 por pantalla). Tokens portados de `eventos-app/lib/theme-noir.ts`.

---

## Modulos (W.0 — W.17)

| Modulo | Roadmap | Detalle | Horas |
|---|---|---|---|
| W.0 | `W.0-spatial-ui.md` | PanelManager, PillBar, presets, command palette, drag | ~12h |
| W.1 | `W.1-setup-auth.md` | Next.js setup + magic link + email/password + i18n + login slideshow (event_login_slides). **Tour movido a W.X (ADR-025)** | ~9h |
| W.1B | `W.1-backend-magic-link.md` | **Sesion backend separada bloqueante** — endpoints magic link + tabla event_login_slides + Filament resource + Mailable | ~4h |
| W.2 | `W.2-home.md` | Hero, countdown, happening now, GamificationHud, recap banner, anuncios mini, sponsors preview, module menu, post-event survey, EventArchive | ~9h |
| W.3 | `W.3-agenda.md` | Lista, filtros, favoritos, detalle, lifecycle states, conflictos, room-checkin, .ics, ratings post-sesion, recordatorios, session chat | ~11h |
| W.4 | `W.4-streaming.md` | Vimeo + Q&A + chat + polls + Trivia Kahoot + anuncios in-stream + replay | ~14h |
| W.5 | `W.5-speakers.md` | Directorio, ratings en lista, perfil, favoritos | ~5h |
| W.6 | `W.6-social-wall.md` | Feed, posts, comentarios, likes, Stories, Photo Contest banner, Hashtags, Memorias | ~10h |
| W.7 | `W.7-sponsors.md` | Brand Wall, Brand Profile, lead capture, trivia | ~7h |
| W.8 | `W.8-networking.md` | Directorio, matchmaking, suggested, perfiles, requests sent/received, chat 1:1, bookmarks, blocked list | ~7h |
| W.9 | `W.9-encuestas-gamification.md` | Encuestas, leaderboard, badges, passport stamps, rewards, prizes, streak | ~10h |
| W.10 | `W.10-notificaciones-perfil.md` | Notif, perfil, settings, Mi QR, Mis Stands, Mis Redenciones, Mis Prizes, Mi Recap, Soporte access, Cambiar evento | ~8h |
| W.11 | `W.11-sockets-rt.md` | Socket.IO RT en toda la web | ~6h |
| W.12 | `W.12-polish.md` | Responsive final, transiciones, loading states, PWA, E2E | ~8h |
| W.13 | `W.13-faq-documentos-pages.md` | FAQ, Documentos descargables, Pages dinamicas | ~3h |
| W.14 | `W.14-anuncios-boletines.md` | Anuncios urgentes, banners rotativos, boletines/highlights | ~3h |
| W.15 | `W.15-vendor-dashboard.md` | Mi Stand, Leads capturados, Stats, Team (OPCIONAL Fase 1) | ~6h |
| W.16 | `W.16-live-moments.md` | Trivia Kahoot engine, Sorteo Ceremony display, Concurso Fotos display, Golden Ticket reveal, Spin Wheel readonly | ~6h |
| W.17 | `W.17-soporte.md` | Tickets de soporte, chat staff, RT updates | ~3h |
| **W.X** | `W.X-welcome-showcase.md` (TBD) | **WelcomeShowcase cinematic post-login** — port de `showcase-onboarding-v6.html` con tokens Lumina Noir + accent dinamico + componentes reales en miniatura. Bloqueado por W.3+W.4+W.5+W.7+W.8+W.9 (ADR-025) | ~3.5h |

---

## Specs maestros (lectura obligatoria antes de codear)

| Doc | Contenido |
|---|---|
| `DECISIONS.md` | ADRs con fecha — todo lo que se decidio y por que |
| `AUTH-SPEC.md` | Magic link + email/password + Bearer Sanctum + refresh + logout multi-device + session timeout |
| `RESPONSIVE-SPEC.md` | 3 breakpoints, regla "diseno dedicado por viewport", checklist device real |
| `DESIGN-SYSTEM.md` | Tokens Lumina Noir, fonts, glass rules, componentes shadcn, animaciones |

---

## Features SOLO movil (NO aplica en web)

- Kiosko de check-in (hardware dedicado)
- QR badge fisico (entrada presencial)
- Escaneo de leads con camara (presencial)
- Passport stamps con QR (presencial)
- Push notifications nativas (web usa Web Notifications API)

---

## Soporte tablets / iPads

| Dispositivo | Experiencia | Por que |
|---|---|---|
| iPhone / Android phone | App nativa (preferente) o webapp | Interacciones presenciales (QR, check-in, leads, passport) requieren app |
| iPad / tablet Android | Webapp responsive (PWA installable) | Experiencia spatial encaja perfecto |
| Laptop / desktop | Webapp (PWA installable) | Experiencia virtual principal |

---

## Performance budget

- Bundle inicial **< 200KB** gzipped (sin contar fonts)
- Code splitting por modulo (lazy import de paneles no visibles)
- Lazy load de `@dnd-kit/core` y `framer-motion` en panels
- Imagen avatar/photo: `next/image` con `sizes` correcto
- Streaming: lazy mount del player (solo cuando se abre el panel)
- Time to Interactive **< 3s** en 4G Bogota (medicion en VPS real)
- Lighthouse Performance **>= 85** en desktop, **>= 75** en mobile

---

## Accesibilidad

- WCAG AA minimo en TODOS los modulos (Bancolombia compliance)
- Contraste 4.5:1 en texto (Lumina Noir cumple — tokens validados)
- Focus visible en todos los interactivos (`:focus-visible` con outline accent)
- Soporte teclado completo (Cmd+K, Tab navigation, Esc cierra modales)
- ARIA labels en todos los iconos sin texto
- `prefers-reduced-motion` respetado (transiciones spring se vuelven instantaneas)
- Lighthouse Accessibility **>= 95** en todos los modulos

---

## Testing strategy

- **Vitest** unit — lib/, hooks/, utils/
- **Playwright** E2E — happy path + 1 edge case minimo por modulo
- **Visual regression** opcional con Playwright screenshots — en W.12
- Cada PR de modulo cierra con su suite de tests verde
- CI ejecuta `pnpm test` + `pnpm test:e2e` en cada push a `main`

---

## Sentry

- Frontend: `@sentry/nextjs` con DSN propio
- Source maps subidos en build (no en cliente)
- Breadcrumbs de navegacion + RT events
- Ignorar errores de extensiones browser (`chrome-extension://`)

---

## Compatibilidad navegador

| Browser | Version minima | Notas |
|---|---|---|
| Chrome | Ultimas 2 | Target principal |
| Edge | Ultimas 2 | Bancolombia corporativo usa Edge |
| Safari | 16+ | iPad |
| Firefox | 115+ | Enterprise stragglers |
| IE11 | NO | Cero soporte |

---

## CSP / X-Frame-Options

Bancolombia podria querer embeber la webapp en su intranet via iframe. Decision en W.1:
- `X-Frame-Options: SAMEORIGIN` por default
- Override por evento si cliente solicita embed (config en `events.embed_allowed_origins`)
- CSP estricto: `script-src 'self'`, `connect-src 'self' wss://socket.eventos.app https://*.vimeo.com`, `img-src 'self' https://* data:`

---

## Coordinacion con app movil

- **Tokens Lumina Noir compartidos**: copy-paste Fase 1 (drift es manejable). Si crece consideramos package npm privado
- **Hooks adaptados**: `useAgenda`, `useSpeakers`, etc. de la app movil se RE-implementan con TanStack Query web (mismo endpoint, distinta libreria de fetch)
- **Endpoints existentes**: 150+ endpoints validados con app movil. Antes de cada modulo verificar que el endpoint da lo que la web necesita (ej. agenda paginada vs completa). Si requiere ajuste backend, **es bloqueante del modulo** y se planea aparte
- **i18n keys compartidas**: nombres de claves alineados con la app movil cuando aplique (ej. `agenda.no_sessions` igual en ambos repos)

---

## Login con identidad visual del evento (feature nuevo)

La pantalla `/login` NO es un form generico. Implementa identidad visual del evento via **slideshow customizable desde Filament**. Tabla nueva `event_login_slides` (NO reutiliza `event_highlights` que era para banners de la app movil sin uso real).

**Innovaciones aprobadas (ADR-022)**:
1. Split-screen 55/45 desktop, layout dedicado por viewport (RESPONSIVE-SPEC)
2. Slideshow Ken Burns con crossfade entre slides (~5s c/u)
3. Live Pulse RT "200 conectados ahora" via socket
4. welcome_message overlay opcional (`events.branding.welcome_message`)
5. Magic link como protagonista visual (no tabs paritarias)

**Backend bloqueante** (ADR-023): Login slideshow + endpoints magic link se hacen en sesion separada `W.1-backend-magic-link.md` antes de F4. Cero mocks.

**Demo aprobado**: `design/features/webapp/Login/iteraciones/login-v1-davinci.html` (Lumina Noir + Lux + 3 viewports).

---

## Onboarding webapp (concepto funcional, no visual)

Primer login del asistente virtual incluye un **mini tour cinematico** estilo showcase:
- Cursor simulado recorre 4-6 features principales
- Highlight de pill bar + paneles + happening now
- Skippable en cualquier momento
- Solo aparece la primera vez (`localStorage` flag `onboarding_completed`)

NO es formulario de registro — el registro completo vive en la landing publica + form embebido en webapp si el evento permite registro abierto.

Detalle en `W.1-setup-auth.md`. Nota: el demo `design/showcase-onboarding-v6.html` es **referencia funcional** (concepto del tour), NO referencia visual (estetica del demo se descarta).

---

## Estimacion total (post-auditoria 2026-05-01)

| Modulo | Horas | Bloqueante | Notas |
|---|---|---|---|
| W.0 Spatial UI base | ~12h | Si | Cimiento |
| W.1 Setup + Auth + Tour | ~10h | Si | Cimiento tecnico, va antes que W.0 |
| W.2 Home | ~9h | Si | Expandido por GamificationHud + Recap banner + anuncios mini |
| W.3 Agenda | ~11h | Si | Expandido por lifecycle + room-checkin + .ics + ratings |
| W.4 Streaming | ~14h | Si | Expandido por Trivia + anuncios in-stream + replay |
| W.5 Speakers | ~5h | Si | Expandido por ratings en lista + favoritos |
| W.6 Social Wall | ~10h | Si | Expandido por Stories + Hashtags + Photo Contest + Memorias |
| W.7 Sponsors | ~7h | Si | Vendor side delegado a W.15 |
| W.8 Networking | ~7h | Si | Expandido por bookmarks + blocked + sent requests |
| W.9 Engagement | ~10h | Si | Expandido por Passport + Rewards + Prizes + Streak |
| W.10 Hub Personal | ~8h | Si | Expandido por Mi QR + Mis... + Cambiar evento |
| W.11 Sockets RT | ~6h | Si | RT en toda la web |
| W.12 Polish + E2E + PWA | ~8h | Si | Cierre webapp |
| W.13 FAQ + Documentos + Pages | ~3h | Si | Modulo nuevo |
| W.14 Anuncios + Boletines | ~3h | Si | Modulo nuevo |
| W.15 Vendor Dashboard | ~6h | **Opcional** | Solo si cliente solicita |
| W.16 Live Moments (subset) | ~6h | Si | Trivia + Sorteo + Concurso fotos display |
| W.17 Soporte | ~3h | Si | Modulo nuevo |
| W.X WelcomeShowcase | ~3.5h | Si | Cinematic onboarding — bloqueado por W.3+W.4+W.5+W.7+W.8+W.9 (ADR-025). Reusa componentes reales en miniatura |
| **Total bloqueante** | **~135.5h** | | **~31-35 dias work** |
| **Total con W.15 opcional** | **~141.5h** | | **~32-37 dias work** |

> **Nota**: estimacion asume backend 100% listo. Cero trabajo backend nuevo previsto excepto endpoints magic link (~3-4h backend, sesion separada). Si algun modulo requiere ajuste backend, **es bloqueante del modulo** y se planea aparte.

---

## Riesgos

1. **Vimeo embed CSP** — Vimeo iframe requiere whitelisting en CSP. Test temprano en W.1
2. **Magic link deliverability** — emails pueden caer en spam corporativo. Bancolombia + DKIM/SPF + DMARC configurado en `eventos.app`
3. **Socket.IO en web bajo proxy corporativo** — Bancolombia firewall puede bloquear WebSockets. Plan B: long-polling fallback
4. **PWA install prompt en mobile** — debe NO mostrarse para no canibalizar app nativa. Detect via `userAgent` + dimensiones viewport
5. **Performance con 3 paneles + RT** — 10K concurrent + Socket.IO + paneles abiertos puede saturar memoria. Stress test en W.11
6. **Tablets en portrait con spatial** — diseno spatial puede pelear con 768px portrait. Validar en device real desde W.0

---

## Que NO entra en Fase 1

- Editor visual de paneles (drag-to-create)
- Multi-evento simultaneo (un usuario en 2 eventos a la vez)
- Webcam-to-webcam networking (solo chat 1:1)
- Grabaciones post-evento (replay) — Fase 2
- Variantes idioma adicionales mas alla de es-CO/en/pt-BR
- Theme custom por evento mas alla de primary color (paleta completa configurable queda Fase 2)

---

## Referencias

- `WEB-APP-PLAN.md` (legacy — reemplazado por este doc)
- `DECISIONS.md` — ADRs
- `AUTH-SPEC.md` — auth detallado
- `RESPONSIVE-SPEC.md` — breakpoints
- `DESIGN-SYSTEM.md` — tokens
- `eventos-app/lib/theme-noir.ts` — fuente de tokens
- `design/LANDING/` — refs visuales spatial UI (visionOS, futbol AR, meeting panels)
- `design/showcase-onboarding-v6.html` — referencia FUNCIONAL del tour de bienvenida (no visual)
