# Web App — Decisiones (ADR-style)

> Architectural Decision Records con fecha. Cada entrada explica que se decidio, por que, alternativas consideradas y consecuencias.
>
> Formato: `ADR-NNN — Titulo — fecha — estado`. Estados: `propuesto`, `aceptado`, `rechazado`, `superseded by ADR-XXX`.

---

## ADR-001 — Repo separado `eventos-web` — 2026-05-01 — aceptado

**Decision**: La webapp vive en un repo nuevo `eventos-web`, no monorepo con backend o app movil.

**Razon**: Deploy independiente, CI/CD propio, no contamina app/backend. Stack distinto (Next.js vs Laravel vs RN). Cada repo tiene su ciclo de release.

**Alternativas consideradas**:
- Monorepo con Turborepo/Nx — descartado por friccion inicial alta y curva de aprendizaje
- Subcarpeta dentro de `eventos-backend` — descartado, mezcla concerns

**Consecuencias**: Tokens Lumina Noir se duplican (copy-paste Fase 1). Drift es manejable. Si crece consideramos package npm privado.

---

## ADR-002 — Auth Bearer token Sanctum — 2026-05-01 — aceptado

**Decision**: La webapp usa **Bearer token Sanctum**, mismo flujo que la app movil. Token guardado en httpOnly cookie via API route Next.

**Razon**: Simetria con app movil (mismo backend, mismo middleware), token revocable, no se mezcla con sesion de Filament admin.

**Alternativas consideradas**:
- Sanctum SPA stateful (cookie session) — descartado, requiere CSRF, mismo dominio raiz
- JWT custom — descartado, agrega complejidad sin beneficio

**Consecuencias**: Refresh token rotation manual (Sanctum no rota por default). TTL definido en `AUTH-SPEC.md`. Logout multi-device disponible.

---

## ADR-003 — Magic link como flujo principal — 2026-05-01 — aceptado

**Decision**: El login principal es **magic link** (token un solo uso 15 min enviado por email). Email/password queda como fallback.

**Razon**: Bancolombia compliance enterprise lo va a pedir. Reduce password fatigue, alinea con tendencia Notion/Vercel/Linear. Mas seguro.

**Alternativas consideradas**:
- Solo email/password — descartado, Bancolombia lo va a pedir
- SSO Google/Microsoft Fase 1 — descartado, complejidad alta para Fase 1

**Consecuencias**: Email deliverability es critico. DKIM/SPF/DMARC obligatorio. Plan B: SMS OTP si Bancolombia lo pide.

---

## ADR-004 — Subdominios `app.eventos.app` y `admin.eventos.app` — 2026-05-01 — aceptado

**Decision**: Webapp asistente en `app.eventos.app`, Filament admin en `admin.eventos.app`, API en `api.eventos.app`.

**Razon**: Separa contextos de cookie, simplifica CSP, permite deploy independiente.

**Alternativas consideradas**:
- Path-based (`eventos.app/app`, `/admin`) — descartado, complica routing y CSP

**Consecuencias**: CORS configurado en backend para los 3 subdominios + Vercel preview URLs.

---

## ADR-005 — Streaming Vimeo — 2026-05-01 — aceptado

**Decision**: Streaming video usa **Vimeo embed** (mismo provider que app movil).

**Razon**: Vendible a Bancolombia, ya validado en app movil, compatible con DRM corporativo, soporta paywalls.

**Alternativas consideradas**:
- YouTube embed — descartado, Bancolombia no usa YouTube enterprise
- HLS custom con Mux/CloudFlare Stream — descartado, costo + complejidad
- Bancolombia provider corporativo — fallback futuro si lo requieren

**Consecuencias**: CSP debe incluir `*.vimeo.com`. Sin transcoding propio, dependencia externa.

---

## ADR-006 — PWA installable con prompt condicional — 2026-05-01 — aceptado

**Decision**: La webapp **es PWA installable**. El install prompt se muestra **solo en desktop/tablet**, no en mobile.

**Razon**: Bancolombia ejecutivos van a "instalar" la webapp en escritorio (estandar enterprise). En mobile NO mostramos prompt para no canibalizar la app nativa.

**Alternativas consideradas**:
- PWA solo desktop (sin manifest mobile) — descartado, complica detection
- No PWA — descartado, perdemos UX enterprise desktop
- PWA con prompt en mobile — descartado, canibaliza app

**Consecuencias**: Manifest detecta plataforma + dimensiones. Service worker minimo (cache fonts, assets, no data dinamica). En mobile, si detecta deeplink a app nativa, redirige.

---

## ADR-007 — Deploy DO sao1 mismo VPC que backend — 2026-05-01 — aceptado

**Decision**: Webapp se deploya en **DO sao1**, mismo VPC privado que backend.

**Razon**: Latencia minima a backend (mismo datacenter), simetria con stack actual, control total. Bancolombia hosting requirements (geografia LATAM) cubierto.

**Alternativas consideradas**:
- Vercel — descartado por costo enterprise + dependencia externa
- Cloudflare Pages — descartado, mismo motivo
- AWS LATAM — descartado, sobrecomplica infra

**Consecuencias**: Build con `next build && next start` o standalone. Nginx reverse proxy. Sin edge functions globales (LATAM-only por ahora).

---

## ADR-008 — i18n con `next-intl` desde W.1 — 2026-05-01 — aceptado

**Decision**: i18n con `next-intl`. Idiomas: **es-CO (default) + en + pt-BR**. Infraestructura activa desde W.1.

**Razon**: Bancolombia opera Colombia (es) + Panama (es) + filiales internacionales (en) + posible Brasil (pt). Multilenguaje viene seguro. Mejor montar la infra desde el dia 1 que rehacer despues.

**Alternativas consideradas**:
- `next-i18next` — descartado, deprecado en Next.js App Router
- Solo es-CO Fase 1 — descartado, refactor caro despues
- 5+ idiomas — descartado, scope creep

**Consecuencias**: Rutas con prefix `/es/`, `/en/`, `/pt/`. Detection por header + override usuario. Catalogos JSON en `messages/{locale}.json`.

---

## ADR-009 — Responsive con disenio dedicado por viewport — 2026-05-01 — aceptado

**Decision**: La webapp **NO usa responsive automatico de Tailwind**. Tiene 3 disenios dedicados:
- Mobile (< 640px): stack tradicional sin spatial
- Tablet portrait (640-1024px): pill bar + 1 panel full-width
- Desktop (> 1024px): pill bar + max 3 paneles spatial

**Razon**: La critica explicita del usuario fue Event Pulse — no era 100% responsive. Cada viewport debe ser una experiencia coherente, no una version comprimida.

**Alternativas consideradas**:
- Responsive automatico Tailwind — descartado, queda mediocre
- Solo desktop Fase 1 — descartado, perdemos asistentes en tablet/mobile

**Consecuencias**: Cada modulo se valida en los 3 viewports en device real (no DevTools). Triple esfuerzo en algunos modulos. Ver `RESPONSIVE-SPEC.md`.

---

## ADR-010 — Stack: Next.js 15 + Tailwind + shadcn/ui + Framer Motion — 2026-05-01 — aceptado

**Decision**: Stack frontend completo:
- **Next.js 15** (App Router)
- **TypeScript** strict mode
- **Tailwind CSS** + **shadcn/ui** (componentes base)
- **Framer Motion** (animaciones spring, layout animations, AnimatePresence)
- **`@dnd-kit/core`** (drag de paneles)
- **TanStack Query** (data fetching, mismo patron que app movil)
- **Socket.IO client** (RT)
- **Zustand** (panel state, layout preferences)
- **Sonner** (toasts)
- **next-intl** (i18n)
- **Sentry** (`@sentry/nextjs`)

**Razon**: Stack maduro, batteries-included, compatible con tooling existente. shadcn/ui da componentes con tokens override-ables (clave para Lumina Noir).

**Alternativas consideradas**:
- Remix — descartado, menos ecosystem PWA + i18n
- SvelteKit — descartado, ecosystem mas chico
- React Router 7 framework mode — descartado, prematuro

**Consecuencias**: Stack confirmado. Ningun cambio sin justificacion fuerte.

---

## ADR-011 — Tokens Lumina Noir desde app movil — 2026-05-01 — aceptado

**Decision**: Tokens visuales se portan **literalmente desde `eventos-app/lib/theme-noir.ts` y `theme-lux.ts`**. NO se inventa paleta nueva. NO se usa la paleta del demo `showcase-onboarding-v6.html`.

**Razon**: La app movil ya tiene Lumina Noir + Lux validados con cliente. Webapp debe sentirse parte de la misma plataforma. Demo HTML era prueba funcional, estetica descartable.

**Alternativas consideradas**:
- Paleta nueva webapp-only — descartado, inconsistencia plataforma
- Portar paleta del demo (#6C63FF violeta etc.) — descartado, demo no estaba validado visualmente

**Consecuencias**: Detalle en `DESIGN-SYSTEM.md`. Drift se controla con copy-paste validado por sesion DaVinci.

---

## ADR-012 — Showcase v6 es referencia FUNCIONAL no visual — 2026-05-01 — aceptado

**Decision**: El demo `design/showcase-onboarding-v6.html` se usa **solo como referencia conceptual** del mini tour de bienvenida (cursor guiado recorriendo features). La estetica del demo se **descarta completamente**.

**Razon**: Usuario explicito que el demo era prueba funcional, no aspecto. Visual real debe seguir Lumina Noir aprobado en app movil.

**Consecuencias**: W.1 implementa el tour funcional con tokens Lumina Noir reales, no copia colores ni layouts del HTML.

---

## ADR-013 — Onboarding webapp NO es formulario — 2026-05-01 — aceptado

**Decision**: El "onboarding" en webapp es un **mini tour cinematico** que muestra las features (4-6 escenas, ~30-60s, skippable), no un formulario de registro multi-paso.

**Razon**: El registro completo vive en landing publica + form embebido. El asistente que llega a `app.eventos.app` ya esta registrado o entra via magic link. Lo que necesita es orientarse.

**Alternativas consideradas**:
- Formulario de bienvenida (preferencias) — descartado, se hace en app movil onboarding
- Sin onboarding — descartado, asistente virtual nuevo se pierde sin guia

**Consecuencias**: Implementacion en W.1. Flag `onboarding_completed` en localStorage. Tour solo aparece la primera vez.

---

## ADR-014 — Testing: Playwright E2E + Vitest unit — 2026-05-01 — aceptado

**Decision**: Cada modulo cierra con:
- **Vitest** unit tests para `lib/`, `hooks/`, `utils/`
- **Playwright** E2E con happy path + 1 edge case minimo

**Razon**: Cobertura razonable sin friccion alta. Playwright maneja Socket.IO + multi-tab + responsive testing.

**Alternativas consideradas**:
- Cypress — descartado, weaker Socket.IO support
- Solo unit — descartado, no captura regresiones de flujo
- Solo E2E — descartado, lento + flaky

**Consecuencias**: CI corre los 2 en cada push. Tiempo total CI **<= 8 min** target.

---

## ADR-015 — Sentry frontend — 2026-05-01 — aceptado

**Decision**: `@sentry/nextjs` con DSN propio + source maps en build.

**Razon**: Vision de errores en produccion, breadcrumbs de navegacion, performance monitoring. Backend ya tiene Sentry; frontend completa el panorama.

**Consecuencias**: Source maps subidos en build (no en cliente). Ignorar errores `chrome-extension://`. Sample rate: 100% errors, 10% perf.

---

## ADR-016 — Compatibilidad navegador: Chrome/Edge ultimas 2 + Safari 16+ + Firefox 115+ — 2026-05-01 — aceptado

**Decision**: Soporte minimo:
- Chrome ultimas 2 versiones
- Edge ultimas 2 versiones
- Safari 16+
- Firefox 115+
- IE11 NO se soporta

**Razon**: Bancolombia corporativo usa Edge moderno. Safari 16+ cubre iPad nuevos. Firefox 115+ cubre enterprise stragglers.

**Consecuencias**: Polyfills minimos. CSS moderno permitido (`:has()`, `container queries`, `gap`).

---

## ADR-017 — CSP estricto + X-Frame-Options SAMEORIGIN por default — 2026-05-01 — aceptado

**Decision**: CSP estricto. `X-Frame-Options: SAMEORIGIN` por default. Override por evento si cliente solicita embed.

**Razon**: Seguridad enterprise + Bancolombia podria querer embeber webapp en intranet.

**Consecuencias**: Config `events.embed_allowed_origins` en backend. CSP whitelist: `script-src 'self'`, `connect-src 'self' wss://socket.eventos.app https://*.vimeo.com`, `img-src 'self' https://* data:`.

---

## ADR-018 — Performance budget — 2026-05-01 — aceptado

**Decision**: Budget operativo:
- Bundle inicial **< 200KB** gzipped (sin fonts)
- Time to Interactive **< 3s** en 4G Bogota
- Lighthouse Performance **>= 85** desktop, **>= 75** mobile
- Lighthouse Accessibility **>= 95** todos los modulos

**Razon**: Bancolombia 10K concurrent. Webapp debe sentirse instantanea.

**Consecuencias**: Code splitting agresivo, lazy load por modulo, monitoreo continuo en CI con `@next/bundle-analyzer`.

---

## ADR-019 — Onboarding webapp como "tour" en W.1 (no W.2) — 2026-05-01 — aceptado

**Decision**: El mini tour de bienvenida vive en **W.1 (Setup + Auth)**, no en W.2 (Home). Razon: el tour aparece despues del primer login y antes de que el usuario interactue con Home.

**Consecuencias**: W.1 incluye componente `WelcomeTour.tsx` + flag localStorage. Detalle en `W.1-setup-auth.md`.

---

## ADR-020 — Coordinacion endpoints con app movil — 2026-05-01 — aceptado

**Decision**: La webapp consume los **mismos 150+ endpoints** validados con la app movil. Antes de cada modulo se verifica que el endpoint da lo que la web necesita.

**Razon**: Backend 100% listo, 309+ tests validan. Cero trabajo backend nuevo en plan.

**Consecuencias**: Si un modulo requiere ajuste backend (ej. agenda paginada vs completa), **es bloqueante del modulo** y se planea aparte. NO se hace en paralelo sin auditoria previa.

---

## ADR-021 — Login slideshow es feature NUEVO (NO reutiliza event_highlights) — 2026-05-02 — aceptado

**Decision**: El slideshow del login en webapp es un **feature 100% nuevo**. Tabla nueva `event_login_slides`, modelo nuevo, Filament resource dedicado, endpoint API publico nuevo. **Cero overlap con `event_highlights` existente** (esa tabla era para banners de la app movil, esta sin uso real).

**Razon**: El slideshow del login tiene proposito narrativo distinto a los highlights del home (carousel rotativo en agenda movil). Mezclarlos confunde organizador (no sabe donde aparece cada imagen) y limita evolucion futura. Tabla dedicada permite agregar campos especificos (subtitle, label, cta_text) sin afectar otros usos.

**Schema propuesto `event_login_slides`**:
- `id`, `event_id` (FK), `image_url`, `label` (texto chico arriba), `title` (display 800), `subtitle` (texto chico abajo)
- `sort_order`, `enabled`, `starts_at`/`expires_at` (opcional, puede mostrar slide solo en ventana)
- `cta_text`, `cta_url` (opcional, click-through al registro publico o info)

**Endpoint publico**: `GET /api/v1/events/{slug}/login-slides` (sin auth, devuelve slides activos ordenados, soporta CDN cache 5min)

**Alternativas consideradas**:
- Reutilizar `event_highlights` con flag `show_in_login` — descartado, mezcla concerns y limita evolucion
- Storage JSON inline en `events.branding.login_slides` — descartado, no soporta sort, dificulta i18n futuro

**Consecuencias**: Backend nuevo (~1.5h: migration + model + resource Filament + endpoint + tests). Frontend webapp consume y renderiza con Ken Burns + Live Pulse + welcome_message overlay.

---

## ADR-022 — Login innovaciones DaVinci aprobadas — 2026-05-02 — aceptado

**Decision**: La pantalla login NO es un form generico. Implementa 5 elementos diferenciadores:

1. **Split-screen 55/45** desktop (slideshow izquierda + form derecha). Tablet portrait usa el mismo layout colapsado a 1 col con slideshow como header. Mobile usa imagen del slide activo como background full + form encima con scrim glass
2. **Slideshow Ken Burns** (zoom 1.0→1.1 cada 5s, crossfade Framer Motion) — imagenes vienen de `event_login_slides` ordenadas
3. **Live Pulse pill** "200 conectados ahora mismo" en zona slideshow — socket RT, actualiza cada 30s
4. **welcome_message overlay** opcional — si organizador setea `events.branding.welcome_message`, aparece en top-right del slideshow como tarjeta glass "Mensaje del organizador"
5. **Magic link como protagonista visual** — input email enorme con tipografia Plus Jakarta, boton primary, "Tengo contrasena" expand-collapse abajo (NO tabs paritarias)

**Razon**: Login es la primera impresion del evento para 10K asistentes. Cada elemento tiene proposito:
- Slideshow + welcome_message = identidad del evento + branding customizable
- Live Pulse = urgencia + sensacion de comunidad
- Magic link protagonista = alinea con compliance Bancolombia + tendencia industria

**Validado visualmente**: demo HTML standalone en `design/features/webapp/Login/iteraciones/login-v1-davinci.html` — toggle Noir/Lux + 3 viewports + annotations.

**Pendiente Fase 2** (NO incluido en F4):
- Background video opcional (slot 1 puede ser MP4 looped)
- Preview cascade avatars al tipear email (preview de comunidad)
- Branding accent dinamico aplicado a todos los componentes (parcial en F4, completo en W.12)

---

## ADR-023 — Bloquear F4-F9 webapp hasta backend magic-link listo — 2026-05-02 — aceptado

**Decision**: Pausar W.1 webapp en F3 (cerrado). Sesion siguiente dedicada a **backend magic-link** en `eventos-backend`. Despues sesion concentrada F4-F9 webapp con backend real, **sin mocks**.

**Razon**: F4 con mock backend = ~2-3h de codigo de descarte cuando llegue backend real. Email deliverability + Bancolombia compliance se valida tarde. F5/F6/F7 dependientes de F4 logged-in. Mejor pausar y volver con flujo end-to-end real (Mailpit local desde dia 1).

**Alternativas consideradas**:
- Frontend con mock + swap despues — descartado (deuda tecnica)
- F4 limited (solo UI, sin envio) + F5/F6 con cookie hardcoded dev — descartado (no testea integracion)

**Consecuencias**:
- W.1 Backend roadmap nuevo: `docs/webapp/W.1-backend-magic-link.md` (~3-4h)
- W.1 Webapp F4-F9 se hace despues en sesion concentrada (~5.5h)
- Total ~9h vs ~10h del plan original — ahorro real por cero mocks

---

## Decisiones pendientes / abiertas

Ninguna actualmente.

Decisiones que se tomaran durante implementacion:
- **W.0**: posicion final del pill bar (arriba vs abajo) — confirmar con refs visuales nuevas
- **W.4**: Vimeo player customizado vs default — segun look feel
- **W.11**: Long-polling fallback config — segun comportamiento Socket.IO en proxy corporativo
