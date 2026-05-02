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

## ADR-019 — Onboarding webapp como "tour" en W.1 (no W.2) — 2026-05-01 — **superseded por ADR-025**

**Decision original**: El mini tour de bienvenida vive en **W.1 (Setup + Auth)**, no en W.2 (Home).

**Consecuencias**: W.1 incluye componente `WelcomeTour.tsx` + flag localStorage. Detalle en `W.1-setup-auth.md`.

**Superseded 2026-05-02d**: ver ADR-025. El showcase muestra modulos que aun no existen (W.3 agenda, W.4 streaming, W.5 speakers, W.7 sponsors, W.8 networking, W.9 gamification). Implementarlo en W.1 con placeholders genera codigo de descarte cuando los modulos reales esten.

---

## ADR-025 — WelcomeShowcase movido a fase tardia (W.X post W.9) — 2026-05-02d — aceptado

**Decision**: El **WelcomeShowcase** (cinematic onboarding tipo `showcase-onboarding-v6.html`) se mueve de W.1 F7 a una **fase nueva W.X** despues de que esten construidos W.3 (Agenda), W.4 (Streaming), W.5 (Speakers), W.7 (Sponsors), W.8 (Networking) y W.9 (Gamification).

**Razon**: El showcase es un **trailer cinematico que muestra los 6 features principales** del evento. Cada beat usa un modulo real:
- Beat 1 SPEAKERS → componentes de W.5
- Beat 2 AGENDA → componentes de W.3
- Beat 3 STREAMING → player de W.4
- Beat 4 CONNECT → networking + social de W.8/W.6
- Beat 5 GAMIFICATION → leaderboard de W.9
- Beat 6 SPONSORS → brand wall de W.7

Si se construye el showcase en W.1 con datos inventados y componentes placeholder, cuando los modulos reales lleguen el showcase queda desfasado visual y conceptualmente. Reusar los componentes reales en miniatura (con datos demo si aplica) garantiza coherencia + cero codigo de descarte.

**Razon decision DaVinci**: feedback usuario explicito 2026-05-02d — "yo dejaria esto para el final porque depende de mucho de como se ven el resto de modulos".

**Alternativas consideradas**:
- W.1 F7 con placeholders (plan original) — descartado, deuda tecnica
- Showcase opcional en W.12 polish — descartado, merece su propia W
- Skip showcase completo — descartado, era diferenciador clave del producto

**Consecuencias**:
- W.1 F7 **removido**. W.1 cierra con F0-F6 + F8 (Sentry) + F9 (tests)
- Nueva W.X en `PLAN.md` "WelcomeShowcase" entre W.9 y W.10 (o como sub-modulo de W.12)
- Estimado revisado W.1 total: ~9h (no 10h)
- Estimado WelcomeShowcase: ~3.5h (cuando llegue)
- localStorage flag `onboarding_completed` sigue, pero NO se setea desde W.1 (queda no-op hasta W.X)
- Mientras tanto, post-login → directo a `/home` sin showcase

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

## ADR-024 — Mobile bottom sheet expandible + 12 mejoras Tier 1+2 — 2026-05-02 — aceptado

**Decision**: Login mobile usa **bottom sheet con snap collapsed/expanded**, mismo patron que Apple Maps, Google Maps, Notion mobile, Linear mobile. Plus 12 mejoras curadas distribuidas en Tier 1 (auto-aplicadas) y Tier 2 (aprobadas).

### Mobile bottom sheet pattern
- Container fijo iPhone 14 (390×844) base de diseno. Cubre 75% market mobile. Funciona desde iPhone SE 375×667 hasta Pro Max 430×932
- Sheet **collapsed** 50% del altura (default step email)
- Sheet **expanded** 78% del altura (auto-snap al focus input o submit)
- Step verifying: 50% (centrado, no expanded)
- Animacion spring 350ms cubic-bezier(0.16, 1, 0.3, 1)
- Drag handle visible siempre arriba del sheet
- Sheet hint "Toca para empezar" cuando collapsed
- Slide overlay text se oculta cuando sheet expanded (no compite)
- Slideshow scrim mas oscuro cuando sheet expanded

### Tier 1 — auto-aplicado en F4 (zero scope creep)
1. **Email recordado** — `localStorage.eventos:lastEmail` pre-fill al volver
2. **Mobile keyboard optimizado** — `inputmode="email"` + `autocomplete="email webauthn"` activa Apple Passkey + Google Smart Lock
3. **Typo email detection** — mailcheck.js style, sugiere `gmail.com` si user tipea `gmail.con`
4. **Microcopy humano**:
   - "Email" → "Tu email de trabajo"
   - "Te enviamos un link" → "Te mandamos el link en menos de 1 minuto. Sin contrasenas."
   - "Tengo contrasena" → "Mejor con mi contrasena"
   - Email firma explicita: "Te enviamos un link a {email} desde **eventos@eventos.app**"
5. **ARIA live regions** — `aria-live="polite"` anuncia transiciones de step (Bancolombia WCAG)
6. **Auto-focus inteligente** — focus gestionado por step (email input / password input / boton reenviar)
7. **Welcome back** — si hay `localStorage.userName`, header "Bienvenido de nuevo" en lugar de "Iniciar sesion"

### Tier 2 — aprobado en F4 (sumar a backend roadmap)
8. **Doble logo organizador + evento** — campo nuevo `events.organizer_logo_url` (organizador distinto del evento, ej Bancolombia presenta Summit). Render compacto "Logo + Presenta + Logo + Nombre"
9. **Video como primer slot** — campo nuevo `event_login_slides.video_url` nullable. Si esta seteado, renderiza `<video autoplay loop muted playsinline>`. Fallback a imagen si falla. Broadcast feel premium
10. **Accent dinamico extendido** — `branding.primary_color` aplica a:
    - Boton primary (default)
    - Glow del live pulse dot (`color-mix` con red)
    - Border focus del input
    - Spinner verify
    - Underline btn-link 30% opacity
11. **Network status banner** — `navigator.onLine` listener. Si offline durante verify, banner top warning "Sin conexion. Tu link sigue valido — reintenta cuando vuelvas."
12. **Preload step siguiente** — `router.prefetch('/login?sent=1')` cuando user en step email. Transicion 0ms al submit

### Tier 3 — Fase 2 (cuando Bancolombia pida)
- Codigo numerico 6 digitos como fallback (mas resistente a filtros corp)
- Passkey WebAuthn (login sin password ni link via biometric)
- WebOTP API (auto-fill SMS code)

**Validado en demo**: `design/features/webapp/Login/iteraciones/login-v5-davinci.html` con toolbar exhaustivo (10 grupos de controles).

### Backend bloqueante actualizado (W.1-backend-magic-link.md)
- `events.organizer_logo_url` string 500 nullable (Tier 2 #8)
- `event_login_slides.video_url` string 500 nullable (Tier 2 #9)
- API endpoint event response: agregar campos nuevos en serializer

---

## ADR-026 — F10 UI/UX foundation con 3 tiers (~6.5h) — 2026-05-02 — aceptado

**Decision**: Despues de F8 Sentry y F9 Tests, agregar **Fase 10 UI/UX foundation** dentro de W.1 con 3 tiers (Foundation + Polish + Premium). Total ~6.5h. W.1 pasa de ~9h a ~15.5h pero queda con cimiento UI completo antes de cualquier modulo de feature.

**Razon**: Cada modulo siguiente (W.0 Spatial, W.2 Home, W.3 Agenda, etc.) necesita el mismo sistema de feedback (toasts, validation, skeletons, empty states, error boundaries). Sin F10, cada modulo improvisa su propio patron → inconsistencia + refactor masivo en W.12. Hacerlo en F10 desde el inicio garantiza coherencia y ahorra ~3-4h netas.

Mismo razonamiento que F6 status gating (ADR-024) — base bien antes de modulos.

**Tier A Foundation (~3h) — base critica para todos los modulos**:
- A1. `LuminaToast` wrapper sobre Sonner: 5 variantes (success/error/info/calendar/favorite) con iconos custom + haptics web (`navigator.vibrate` en Android Chrome)
- A2. `<FormField />` reutilizable: shadcn Input wrapper + AnimatePresence error + soporta react-hook-form
- A3. `<EmptyState />`: 4 variantes (not_found/not_yet/error/success) con icon + title + description + CTA
- A4. Skeleton patterns: `<SkeletonCard/List/Avatar/Text/Grid />` pre-armados con match exact al loaded
- A5. Refactor F4 + F6 para usar el sistema (LoginForm + PreEventHome)
- A6. `useOptimistic` helper hook (prepara W.3+ — agenda favorites, social likes, etc.)

**Tier B Polish (~2.5h) — microinteracciones premium**:
- B1. `<Button />` Framer Motion wrapper con whileTap + whileHover + haptic mobile
- B2. Focus rings custom con `--accent` dynamic + shadow ring
- B3. Page transitions con AnimatePresence (login→home, login step→step, home variants switch)
- B4. Stagger entrance animations (lista items con delay 100ms)
- B5. `<AnimatedNumber />` ticker (countdown, live count, stats)
- B6. Smooth scroll global + scrollIntoView programatico
- B7. Theme transition cross-fade 250ms (no flash)
- B8. Reduced motion tier — todos los anteriores respetan `useReducedMotionPref`
- B9. Gradient breathing en hero pre-event (sutil pulse 8s)
- B10. Scroll trigger con IntersectionObserver — list items entran cuando entran al viewport
- B11. Haptics en swipe gestures mobile (drag handles, sheet drag)

**Tier C Premium (~1.5h) — diferenciadores enterprise**:
- C1. `<ErrorBoundary />` con retry button + Sentry report automatic
- C2. Custom `not-found.tsx` + `error.tsx` + `offline.tsx` Lumina Noir
- C3. Keyboard shortcuts globales (`useKeyboardShortcut` hook + Esc + `?` help + Cmd+K prep)
- C4. `<CopyButton />` con clipboard API + animacion check + toast confirmation
- C5. Connection status pill (online/slow/offline) con latency check `/api/health` cada 30s
- C6. Save indicators pattern para autosave (futuros forms)
- C7. `<SubmittingButton />` con disabled + spinner + texto auto-cambia
- C8. Cross-tab sync (logout en una tab → otras detectan via storage event)
- C9. Page exit guard (form unsaved → confirm dialog)

**Mejoras DaVinci propuestas + descartadas**:
- ✅ B9 gradient breathing, B10 scroll trigger, B11 swipe haptics, A6 useOptimistic, C8 cross-tab, C9 exit guard
- ❌ Confetti / sound effects / magnetic hover / custom cursors — descartado por ser gimmicky para Bancolombia enterprise
- ⏭️ Idle detection + session warning — Fase 2 (AUTH-SPEC linea 116)

**Alternativas consideradas**:
- F10 dispersa en cada modulo (W.0+) — descartado, inconsistencia + refactor
- F10 en W.12 polish al final — descartado, refactor de N modulos al introducir LuminaToast tarde
- F10 como modulo separado W.1.5 — descartado, mas claro como fase final de W.1

**Consecuencias**:
- W.1 estimado revisado: ~9h → ~15.5h
- Total bloqueante webapp: ~135.5h → ~142h (mas robusto)
- Cero refactor visual en modulos siguientes
- Login + home variants existentes refactorizan a usar el sistema en F10.A.5
- Prepara terreno para optimistic UI plan (`docs/analysis/OPTIMISTIC-UI-PLAN.md`)

---

## Decisiones pendientes / abiertas

Ninguna actualmente.

Decisiones que se tomaran durante implementacion:
- **W.0**: posicion final del pill bar (arriba vs abajo) — confirmar con refs visuales nuevas
- **W.4**: Vimeo player customizado vs default — segun look feel
- **W.11**: Long-polling fallback config — segun comportamiento Socket.IO en proxy corporativo
