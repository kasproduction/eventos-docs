da# W.1 — Setup + Auth + Tour de bienvenida

> Cimiento tecnico: scaffold Next.js 15, auth con magic link como flujo principal, layout shell, i18n base, mini tour cinematico al primer login.
>
> **Estimacion:** ~10h (~1.5 dias). Bloqueante de TODO el resto. Se hace ANTES de W.0 spatial.
> **Dependencias:** Backend con endpoints magic link nuevos (~3-4h backend en paralelo o sesion separada).
> **Estado:** Pendiente.

---

## Lectura obligatoria

- `PLAN.md` (vision + scope)
- `DECISIONS.md` (ADR-001 al ADR-020)
- `AUTH-SPEC.md` (flujo completo magic link + email/password)
- `RESPONSIVE-SPEC.md` (3 disenios)
- `DESIGN-SYSTEM.md` (tokens, fonts)
- `design/showcase-onboarding-v6.html` — referencia FUNCIONAL del tour (no visual)

---

## Alcance

1. Scaffold Next.js 15 + TypeScript + Tailwind + shadcn/ui
2. Setup repo `eventos-web` con CI/CD
3. Tokens Lumina Noir + Lux portados (CSS variables)
4. Fonts: Plus Jakarta Sans + Urbanist via `next/font/google`
5. i18n base con `next-intl` (es-CO, en, pt-BR)
6. Auth: magic link (principal) + email/password (fallback)
7. Bearer token Sanctum en httpOnly cookie via API route
8. Layout shell con header y placeholder de pill bar
9. Middleware de proteccion de rutas
10. Mini tour cinematico al primer login (~30-60s, skippable)
11. Sentry frontend
12. Tests basicos (Vitest + Playwright)

---

## Fase 0 — Setup repo (~1h) — 5/5

### 0.1 Crear repo — 2/2
- [x] `git init` en `C:\laragon\www\eventos-web` (auto via `pnpm create next-app`)
- [x] `pnpm create next-app@latest` con: TypeScript, Tailwind, App Router, src dir, alias `@/*`. Latest stable instalado: **Next 16.2.4 + React 19.2 + Tailwind 4** (no Next 15 — downgrade injustificable)

### 0.2 Configuracion — 3/3
- [x] `tsconfig.json` strict mode + `noUncheckedIndexedAccess` + `noImplicitOverride` + `forceConsistentCasingInFileNames` + paths
- [x] **Tailwind 4** breakpoints custom via `@custom-variant` en `globals.css` (Tailwind 4 ya no usa `tailwind.config.ts` para tokens — todo en CSS)
- [x] `.env.local` + `.env.example` con: `NEXT_PUBLIC_API_URL`, `NEXT_PUBLIC_SOCKET_URL`, `SENTRY_DSN`, `MAGIC_LINK_SECRET`, `NEXT_PUBLIC_APP_URL`

### 0.3 Extras DaVinci (sumados sin abrir decisiones)
- [x] `.github/workflows/ci.yml` desde dia 0 (typecheck + lint + build per ADR-014)
- [x] Scripts utiles en `package.json`: `typecheck`, `check` (typecheck + lint)
- [x] `.gitignore` extendido (`.env*` + `!.env.example`, `.turbo`, `.vscode/`, Playwright outputs, Sentry artifacts)
- [x] Layout root: `lang="es-CO"` (ADR-008) + `data-theme="noir"` (DESIGN-SYSTEM default)

**Cierre F0**: Build production 977ms, dev server 407ms con Turbopack, HTTP 200 en /. Commit `ba2fc24`.

---

## Fase 1 — Tokens + fonts + globals (~1.5h) — 6/6

### 1.1 Fonts — 2/2
- [x] `app/layout.tsx` con `next/font/google` para Urbanist (300-800) + Plus Jakarta Sans (600-900)
- [x] CSS variables `--font-urbanist`, `--font-jakarta` expuestas via @theme y aplicadas a body

### 1.2 Tokens CSS — 2/2
- [x] `app/globals.css` con tokens Noir + Lux completos como CSS variables (portados literal de `eventos-app/lib/theme-noir.ts` y `theme-lux.ts`). Genericos sin prefijo (`--surface-low`, `--text-primary`, `--gold`, etc.) — alinea con shadcn/ui standard
- [x] Theme switching via `data-theme="lux"` override de `[data-theme="lux"]` selector. Default Noir en `:root`. **next-themes 0.4** maneja persistencia localStorage + hydration

### 1.3 Tailwind 4 extension — 2/2
- [x] `@theme inline` en globals.css expone tokens como utilities (Tailwind 4 reemplaza `tailwind.config.ts` para tokens)
- [x] **NO se usa `dark:` prefix** — el switch es Noir/Lux explicito, no light/dark del sistema. `enableSystem={false}` en ThemeProvider

### 1.4 Extras DaVinci sumados
- [x] `useMediaQuery` con `useSyncExternalStore` (pasa `react-hooks/set-state-in-effect` de Next 16)
- [x] `useIsMobile/useIsTablet/useIsDesktop` con breakpoints RESPONSIVE-SPEC
- [x] `useReducedMotionPref` (DESIGN-SYSTEM 376) + CSS `@media (prefers-reduced-motion)` cubren JS y CSS
- [x] `useIsClient` hook compartido para mounted-only components (hydration-safe pattern)
- [x] `ThemeToggle` reutilizable
- [x] `ThemeProvider` wrapper con default Noir, attribute `data-theme`
- [x] `:focus-visible` con outline accent (DESIGN-SYSTEM accessibility)
- [x] `color-scheme` declarado por theme
- [x] Demo page `/` que valida tokens visualmente (sera reemplazada en F4 por LoginForm)

**Cierre F1**: Typecheck + lint + build 1049ms + dev 396ms + GET / 200 (255ms first hit, 29ms cached). Sin warnings hydration. Commit `811b7dd`.

---

## Fase 2 — shadcn/ui + componentes base (~1h) — 4/4

### 2.1 Setup — 2/2
- [x] `pnpm dlx shadcn@latest init` con `--template next --base radix --preset nova --css-variables`. **shadcn 2.x** (no shadcn-ui — el package se renombró)
- [x] **15 componentes** instalados (13 pedidos + 2 deps): Button, Input, Dialog, Sheet, DropdownMenu, Tooltip, Avatar, Badge, Separator, Skeleton, Tabs, Command, Popover, Sonner + textarea, input-group (deps de input)

### 2.2 Sonner — 1/1
- [x] `<Toaster />` en root layout. Wrapper mapea `theme: noir → dark`, `lux → light`. Position `bottom-right`. Tokens via `--normal-bg/--normal-text/--normal-border/--border-radius`

### 2.3 Lucide — 1/1
- [x] `lucide-react@1.14` instalado automaticamente por preset radix-nova

### 2.4 Merge tokens shadcn ↔ Lumina (clave)
shadcn init agrego `:root` + `.dark` con `oklch(...)` grises que duplicaban mis tokens. Solucion:
- Removidos los `:root` y `.dark` shadcn
- Aliases shadcn (`--background`, `--foreground`, `--card`, `--primary`, `--secondary`, `--muted`, `--accent-foreground`, `--destructive`, `--input`, `--ring`) ahora apuntan a tokens Lumina Noir/Lux via `@theme inline`
- `--primary` = mi `--accent` (brand). `--secondary` = `--surface-high`. `--muted` = `--surface-medium`
- Mi `--card` original renombrado a `--card-subtle`, mi `--input` a `--input-bg` para liberar nombres a shadcn semantics
- Nuevo: `--destructive` + `--destructive-foreground` por theme

### 2.5 Layout updates
- [x] `TooltipProvider delayDuration={200}` wrappea children en root layout
- [x] `<Toaster />` montado en root, dentro de TooltipProvider

**Cierre F2**: typecheck + lint clean, build 4 paginas estaticas, dev 410ms, GET / 200 en 357ms. 40905 bytes HTML demo. Commit `e425570`.

---

## Fase 3 — i18n base (~1h) — 5/5

### 3.1 next-intl — 2/2
- [x] `pnpm add next-intl@4.5`
- [x] Routing prefix-based: `src/i18n/routing.ts` con `[es, en, pt]`, `defaultLocale: es`, `localePrefix: always`. **URL prefix corto** (ADR-008) — sub-locale `es-CO`/`pt-BR` se aplica en formatters Intl
- [x] `src/i18n/request.ts` con `getRequestConfig` + `hasLocale` validation + timeZone `America/Bogota`
- [x] `src/i18n/navigation.ts` con `Link`, `redirect`, `usePathname`, `useRouter`, `getPathname` locale-aware
- [x] `src/proxy.ts` (Next 16 renombro `middleware.ts` → `proxy.ts`) con `createMiddleware(routing)`. matcher excluye api/_next/_vercel/archivos
- [x] `next.config.ts` con `withNextIntl` plugin

### 3.2 Catalogos — 3/3
- [x] `messages/{es,en,pt}.json` con 5 namespaces: `common` (9 keys), `nav` (8 keys), `auth.login`/`auth.magicLinkSent`/`auth.verify`/`auth.errors` (24 keys auth), `auth.logoutReason` (2 keys), `tour` (3 keys + 6 escenas)
- [x] Interpolation: `auth.magicLinkSent.description` con `{email}`, `auth.errors.rateLimit` con `{minutes}`
- [x] **Type-safe** via `global.d.ts`: `AppConfig.Messages = typeof messages` + `AppConfig.Locale = (typeof routing.locales)[number]`. Cualquier key invalida da error TS

### 3.3 Reorganizacion app router (necesaria para next-intl con i18n routing)
- [x] `app/layout.tsx` — pass-through (`return children`) per next-intl docs
- [x] `app/[locale]/layout.tsx` — html/body/fonts/providers/intl con `setRequestLocale`, `generateStaticParams`, `hasLocale` validation + `notFound()`
- [x] `app/[locale]/page.tsx` — demo movido aca
- [x] `NextIntlClientProvider` envuelve `<TooltipProvider><ThemeProvider>`

### 3.4 Extras DaVinci
- [x] `LanguageSwitcher` reutilizable: dropdown shadcn + lucide Languages icon + `useTransition` no bloquea UI durante navegacion. Persistencia via cookie `NEXT_LOCALE`
- [x] Demo page usa `useTranslations()` extensivamente: login card preview, buttons, tooltip con interpolation, toast con i18n

**Cierre F3**: typecheck + lint clean (despues de borrar cache `.next/` que apuntaba a layout viejo), build con SSG prerender de los 3 locales + middleware/proxy activo. Dev 392ms ready, root `/` → 307 redirect a `/es`, `/es`/`/en`/`/pt` → 200. Strings correctos verificados via curl. Commit `ffd8589`.

**Bug captado**: Next 16 deprecó `src/middleware.ts` → `src/proxy.ts`. Detectado al ver warning en dev server, renombrado y reescrito comentario. Sin warnings tras rename.

---

## ✅ BACKEND + F4 WEBAPP CERRADOS (2026-05-02b)

> **Backend**: W.1B en `eventos-backend` branch `feature/magic-link-auth` commit `ef24003`. Magic link endpoints + login_slides + Mailable + 10 Pest passing.
>
> **Webapp F4**: `eventos-web` commit `6ce5aec`. Magic link UI + slideshow + Tier 1+2 mejoras + state machine. E2E real verificado: form → API route → backend → email a Mailpit.

---

## Fase 4 — Auth — Magic link + Login Slideshow (~3h) — ✅ 12/12 CERRADA

### 4.1 Backend bloqueante — 3/3 (W.1B `ef24003`)
- [x] `POST /api/v1/auth/magic-link` (anti-enumeration + rate limit 3/email/hora)
- [x] `POST /api/v1/auth/verify-magic-link` (codes token_invalid/used/expired)
- [x] Tabla `magic_link_tokens` SHA-256 + 8 Pest tests passing

### 4.2 Backend feature Login Slideshow — 2/2
- [x] Tabla `event_login_slides` con video_url + has_overlay_text (ADR-021)
- [x] `GET /api/v1/events/{slug}/login-slides` endpoint publico cache 5min + Filament `LoginSlideResource` con drag reorder + Observer invalidation

### 4.3 Frontend pages — 3/3
- [x] `src/app/[locale]/(auth)/login/page.tsx` server component fetch event + slides paralelo
- [x] State `sent` integrado en `LoginForm` (no archivo separado — state machine)
- [x] `src/app/[locale]/(auth)/verify/page.tsx` recibe `?token` 64 chars + valida + redirect

### 4.4 Componentes login slideshow — 3/3
- [x] `LoginSlideshow.tsx` Ken Burns 1.0→1.08 cada 5s + crossfade Framer Motion + `video_url` MP4 con fallback
- [x] `LivePulse.tsx` solo en live_today/live_now, mock RT con jitter (sera socket en W.11)
- [x] `EventStatusPill.tsx` contextual upcoming/live_today/ended con countdown live

### 4.5 Next API routes — 4/2
- [x] `app/api/auth/magic-link/route.ts` proxy POST + X-Forwarded-For passthrough
- [x] `app/api/auth/verify/route.ts` proxy POST + setea httpOnly cookie con bearer Sanctum
- [x] `app/api/auth/login/route.ts` (password fallback) + `app/api/auth/logout/route.ts` (revoke + clear cookie)

### 4.6 Tier 1 mejoras (auto-aplicadas) — 7/7
- [x] `useLastEmail` con `useSyncExternalStore` (SSR-safe localStorage pattern)
- [x] `inputmode="email"` + `autocomplete="email webauthn"` (Apple Passkey + Smart Lock)
- [x] mailcheck.js typo detection — wrapper con dominios LATAM (bancolombia, etc.)
- [x] Microcopy humano i18n (es/en/pt) actualizado: 20+ keys auth.login + interpolation HTML
- [x] `aria-live="polite"` div ARIA con announcements por step
- [x] Auto-focus inteligente con `useRef` por step (email/password input)
- [x] Welcome back conditional cuando `localStorage.lastUserName` cached

### 4.7 Tier 2 mejoras — 5/5
- [x] **Doble logo** `EventLogo.tsx`: `organizer_logo_url` distinto del `logo_url` → render compacto "Logo + Presenta + Logo + Nombre"
- [x] **Video slot**: `LoginSlideshow` consume `video_url` con `<video autoplay loop muted playsinline>` + fallback imagen
- [x] **Accent dinamico extendido**: `--accent` aplica a boton primary + spinner verify + focus border input + btn-link underline
- [x] **Network status banner**: `useNetworkStatus` con `useSyncExternalStore` + `NetworkStatusBanner` warning amber top
- [x] **Preload step siguiente**: state machine instant transition (no router.prefetch necesario porque todo en mismo bundle)

### 4.8 Mobile bottom sheet adaptativo (ADR-024 corregido v6→v7) — 4/4
- [x] LoginCard CSS con `max-height: 78%` (NO height fijo) — sheet respira con contenido
- [x] `display: flex; flex-direction: column; justify-content: flex-end` — slideshow toma sobrante
- [x] Drag handle visible siempre (`::before` pseudo)
- [x] Sin estados collapsed/expanded — sheet adaptativo unico (patron app movil real)

**Cierre F4**: typecheck + lint clean, build 16 paginas + 4 API routes + middleware (514ms generate), dev 397ms ready, GET `/es/login` 200 (34261 bytes), POST `/api/auth/magic-link` proxy → backend Laravel → email entregado a Mailpit ✓. Commit eventos-web `6ce5aec`.

---

## Fase 5 — Auth — Email + password fallback (~1h) — ✅ 4/4 CERRADA (incluida en F4)

### 5.1 Frontend — 2/2
- [x] State machine integrado en `LoginForm`: step `password` aparece via "Mejor con mi contrasena" desde step `sent` (NO tabs paritarias — flow narrativo)
- [x] Form email pill (read-only) + input password + submit + acciones inline ("Volver al link · Olvide mi contrasena")

### 5.2 API routes — 2/2
- [x] `app/api/auth/login/route.ts` proxy POST a backend `/auth/login` con device_name custom
- [x] Test E2E manual ok via curl + dev server

---

## Fase 6 — Layout shell + middleware + status gating (~2.5h) — ✅ 10/10 CERRADA

### 6.1 Middleware auth — 1/1
- [x] `proxy.ts` extendido con auth gate. PUBLIC_PATH_SEGMENTS = [login, verify, set-password]. Sin cookie → redirect `/login?next={originalPath}`. Validacion ligera (no llama backend).

### 6.2 Layout protegido — 2/2
- [x] `app/[locale]/(app)/layout.tsx` con `getCurrentUser()` server-side. Si bearer revocado → clearAuthCookie + redirect login
- [x] `AppHeader` sticky con logo evento + LanguageSwitcher + ThemeToggle + UserMenu. Slot center placeholder PillBar (W.0)

### 6.3 UserMenu — 1/1
- [x] `UserMenu.tsx` con Avatar + DropdownMenu (Perfil, Configuracion, Cerrar sesion). Logout funcional + forgetEmail() + redirect login

### 6.4 Backend extension — 1/1
- [x] `PublicEventController.show()` expone status + modality + about_* + registered_count + session_count + max_attendees + registration_closes_at (commit eventos-backend `d44ff42`)

### 6.5 Status gating + 4 home variants — 4/4
- [x] `home/page.tsx` switch por `event.status` (mismo patron app movil `eventos-app/app/(app)/(tabs)/index.tsx`)
- [x] `PreEventHome` para draft/registration: Hero + Countdown + EventInfoCard + AboutCard
- [x] `PublishedHome` placeholder W.2 con greeting + slot Module Menu
- [x] `LiveHome` placeholder W.0/W.2/W.3 con badge "En vivo" + lista pendientes
- [x] `EndedHome` placeholder W.10 con stats finales + slot Mi Recap

### 6.6 Extras
- [x] Type `EventStatus` + `EventModality` + `AuthUser`
- [x] `lib/auth.ts` `getCurrentUser()` con cookie zombie cleanup

**Cierre F6**: typecheck + lint clean, build 19 paginas + 4 API + middleware (529ms). E2E sin auth `/es/home` → 307 `/es/login?next=%2Fes%2Fhome` ✓. E2E con cookie Sanctum real → 200 + render LiveHome correcto ✓. Commits: eventos-web `96fff15` + eventos-backend `d44ff42`.

---

## Fase 7 — Tour de bienvenida — ⏭️ MOVIDA A W.X (ADR-025)

> **Decision 2026-05-02d**: El WelcomeShowcase se mueve a fase tardia despues de que esten W.3 (Agenda), W.4 (Streaming), W.5 (Speakers), W.7 (Sponsors), W.8 (Networking) y W.9 (Gamification). El showcase reusa componentes reales en miniatura — implementarlo aca con placeholders genera codigo de descarte.
>
> Mientras tanto, post-login va directo a `/home` sin showcase. Flag `onboarding_completed` localStorage queda preparado para cuando W.X llegue. El showcase v6 original (`design/features/onboarding/iteraciones/showcase-onboarding-v6.html`) sirve como referencia FUNCIONAL del concepto cuando se construya W.X — los tokens visuales seran Lumina Noir + accent dinamico (ADR-012, no la paleta del demo).

---

## Fase 8 — Sentry + observabilidad (~30min) — ✅ 3/3 CERRADA

### 8.1 Sentry — 2/2
- [x] `pnpm add @sentry/nextjs` (setup manual, wizard requiere TTY)
- [x] `sentry.client.config.ts` (tracesSampleRate 0.1, replaysOnError 1.0, ignoreErrors browser ext + ResizeObserver + AbortError, beforeSend scrub email/password/token), `sentry.server.config.ts` (scrub authorization/cookie + body PII), `sentry.edge.config.ts` (minimal middleware/proxy). Pattern Next 15+ via `instrumentation.ts` (register segun NEXT_RUNTIME) + `instrumentation-client.ts`

### 8.2 Source maps — 1/1
- [x] `next.config.ts` wrapped con `withSentryConfig`: org/project/authToken via env vars (CI only, sin DSN local = Sentry off auto), `sourcemaps.deleteSourcemapsAfterUpload` (no exponer cliente), `tunnelRoute /monitoring` (evita ad-blockers)

**Cierre F8**: Privacy compliance Bancolombia: cookie auth + Authorization header + email/password/token de body scrubbed antes de send. Commit eventos-web `d615bcf`.

---

## Fase 9 — Tests (~1h) — ✅ 5/5 CERRADA (22 unit + 12 E2E passing)

### 9.1 Vitest — 2/2 (22 tests passing en 1.5s)
- [x] `vitest.config.ts` con jsdom + alias `@/*` + coverage v8 + `tests/setup.ts` con jest-dom + cleanup
- [x] Tests unit: `mailcheck.test.ts` (6, typo + dominios LATAM), `authValidators.test.ts` (10, zod schemas email/magic-link/verify/password), `api.test.ts` (5, apiFetch wrapper + ApiError + headers)
- **Nota**: tests de `useAuth` hook + API proxy routes diferidos a W.10 (cuando exista hook real). El cubrimiento real (validators + api wrapper + mailcheck) protege la superficie crítica de F4-F6.

### 9.2 Playwright — 3/3 (12 tests passing en 9.3s)
- [x] `playwright.config.ts`: Chromium project, locale es-CO forzado + Accept-Language headers (sino test detecta en-US y redirect /en), reuseExistingServer en dev, retain video on fail
- [x] `e2e/auth-gate.spec.ts` (4): `/` sin auth → `/es/login` (3 redirects), `/es` sin auth → `/es/login`, `/es/home` sin cookie → `/es/login?next=/es/home`, `/es/login` publica 200
- [x] `e2e/login-form.spec.ts` (4): step email render, mailcheck typo `gmail.con` → `gmail.com`, submit con mock → step sent, click "Mejor con mi contrasena" → step password
- [x] `e2e/verify-page.spec.ts` (4): token length != 64 redirect, sin token redirect, mock 401 `token_invalid` → "Link invalido" + retry, mock 410 `token_expired` → "Este link expiro"

### 9.3 CI workflow update
- [x] `.github/workflows/ci.yml` ahora con 2 jobs: `check` (typecheck + lint + Vitest unit + build) + `e2e` (playwright con browsers install + upload report on fail). `e2e` depends on `check`.

### 9.4 Scripts package.json
- [x] `pnpm test` (vitest run), `pnpm test:watch`, `pnpm test:ui`, `pnpm test:e2e`, `pnpm test:e2e:ui`

**Cierre F9**: 34/34 tests verde local. Commit eventos-web `4e8e588`.

---

## Fase 10 — UI/UX foundation DaVinci (~6.5h) — 8/26 (ADR-026)

> Cimiento de UI/UX para todos los modulos siguientes. Cero modulos siguientes (W.0/W.2+) hasta tener este sistema. Razon: evitar refactor masivo en W.12.

### F10.A — Foundation (~3h) — ✅ 6/6 + 2 extras (LuminaToast reescrito + apiErrors mapper)

#### A1. LuminaToast wrapper — 1/1 ✅
- [x] **Reescrito SIN Sonner** (port 1:1 del `LuminaToast` de la app movil): store propio via `useSyncExternalStore`, `<LuminaToastViewport>` montado en `[locale]/layout.tsx`. Position fixed top centrado, max-width `min(90vw, 420px)`, **forma pill `rounded-full`** auto-width al contenido. Animacion entrada `translateY -120 → 0 + scale 0.92 → 1` spring (damping 18, stiffness 200, mass 0.8). Auto-dismiss 3s + click dismiss. 5 variantes (success/error/info/calendar/favorite) con colores `#4ADE80/#FF7351/#60A5FA/#A78BFA/#F472B6` (idénticos al móvil). Background `bg-elevated/95` + `backdrop-blur-xl`. Border `border-strong` visible. Texto `truncate` 1 línea. Haptics web via `navigator.vibrate` silencioso en iOS.
- API: `lumina.success({ message, haptic?, duration? })` (sin `description` — pill no permite 2 líneas, igual que app móvil)

#### A2. FormField reutilizable — 1/1 ✅
- [x] **Refactorizado al patron app movil** (`GlassInput.tsx`): `error` es **boolean** (no string). Solo cambia border + label color + leading icon a rojo, **sincronicamente sin animacion**. Mensaje del error vive en toast (lumina.error), NO en el campo. `aria-invalid` + `aria-describedby` para a11y. forwardRef + label uppercase 10px tracking + hint + leading/trailing slots.

#### A3. EmptyState — 1/1 ✅
- [x] `src/components/ui/empty-state.tsx` con 4 variantes (not_found/not_yet/error/success), iconos Lucide tinted, `font-display` titulo, action button asChild para href. NO renderea aun en ningun modulo (preparado para W.2/W.3).

#### A4. Skeleton patterns — 1/1 ✅
- [x] 5 patrones pre-armados en `src/components/ui/skeletons.tsx`: `SkeletonCard`, `SkeletonList`, `SkeletonAvatar`, `SkeletonText`, `SkeletonGrid`. Match exact al loaded state (no genericos). NO renderea aun (F6 es SSR sync — aplicara cuando W.2 conecte client-side fetch).

#### A5. Refactor F4 + F6 — 1/1 ✅
- [x] LoginForm: FormField en email + password, validacion local previa con `lumina.error` (emailRequired/emailInvalid/passwordRequired), API errors con `getApiErrorMessage()`, network error `t("errors.network")`, success → `router.push("/home")` SIN toast (la transicion es la confirmacion, patron movil). `noValidate` para suprimir tooltip browser nativo. Border vuelve a normal al primer keystroke.
- [x] **Dots eliminados** en FooterLinks (stack vertical mobile + tablet flex con gap-5)
- [x] **`font-display`** reemplazo 12 `style={{ fontFamily: "var(--font-jakarta)" }}` inline en LoginForm + 4 home variants
- [x] **EventStatusPill ELIMINADO** (cliché Cisco/Hopin/ICE360 — `feedback_no_status_widgets.md`)
- [x] **UserMenu logout** migrado a `lumina.success/error`
- [x] LiveHome rediseñado: lista de pendientes con label `W.0` semibold + divider en lugar de bullets `· W.0 spatial...`. Footer "Evento" con stat grid 2 cols (sin dots). PreEventHome/PublishedHome/EndedHome con `font-display`.

#### A6. useOptimistic helper — 1/1 ✅
- [x] `src/hooks/useOptimisticMutation.ts` wrapper sobre TanStack Query (instalado `@tanstack/react-query@5.100`). `QueryProvider` montado en layout con defaults DaVinci (refetchOnWindowFocus, staleTime 60s, retry 1). Toggle local instant via cache snapshot + updater pure function. Rollback automatic en error + `lumina.error` toast con `errorMessage`. Prepara W.3 favorites, W.6 likes, W.9 passport stamps.

#### Extras DaVinci (no estaban en el plan original) — 2/2 ✅
- [x] **`lib/apiErrors.ts`** — mapper `(status, code, retry_after, t) → string i18n`. Resuelve el problema de "Too many attempts" en ingles del backend Laravel: 429 con `Retry-After` → "Demasiados intentos. Intenta en X min." (o "Espera X segundos" si <60s). Codigos backend (`token_expired`, `account_inactive`, `invalid_credentials`) → claves `auth.errors.*`. Heuristica `looksLocalized()` deja pasar mensajes ya en ES/PT del backend (validation FormRequest).
- [x] **`apiFetch` extendido** para capturar header `Retry-After` en `ApiError.retryAfter`. Proxies `magic-link/verify/login` propagan `code` + `retry_after` al cliente. Catalogos i18n actualizados es/en/pt con `rateLimitSeconds`, `network`, `accountInactive`.

**Cierre F10.A**: typecheck + lint clean, build production verde, smoke test `/es/login` HTTP 200. Toast/Form/EventStatusPill validados visualmente por usuario (DaVinci).

### F10.B — Polish (~2.5h) — ✅ 10/11 (B11 diferido)

#### B1. Button micro-feedback — 1/1 ✅
- [x] Button shadcn base con `active:translate-y-px active:scale-[0.98]` (CSS solo, sin Framer wrapper). Aplica a TODOS los buttons automaticamente. Haptic web (`navigator.vibrate`) revertido por bug crítico SSR (BUG-308) — queda como pendiente para wrapper opt-in `<HapticButton>` cliente puro

#### B2. Focus rings dynamic — 1/1 ✅
- [x] `:focus-visible` con `outline: 2px solid var(--accent)` + `outline-offset: 3px` + `box-shadow: 0 0 0 4px color-mix(in srgb, var(--accent) 20%, transparent)` + transition 150ms. NO aplica a inputs/textareas/select (esos tienen su propio focus state via border-bottom variant minimal)

#### B3. Page transitions — 1/1 ✅
- [x] `[locale]/template.tsx` con motion.div fade + slide-up 8px (300ms ease) en cada navegacion. Respeta `useReducedMotionPref()`. Login step → step usa AnimatePresence con slide horizontal direccional separado (state machine vive en LoginForm)
- [x] **Slide horizontal direccional entre steps** del LoginForm: `STEP_ORDER` mapea email/sent/password/verifying. Forward (mas alto) → entra de derecha sale a izquierda. Backward → invertido. Spring damping 25 stiffness 220. Aplicado tambien al ForgotPasswordSheet (form → confirmation)

#### B4. Stagger entrance — 0/1 ⏳ DIFERIDO
- [ ] `<StaggerList />` — sin uso aun en login. Aplica cuando W.2 traiga listas reales (sponsors, agenda items)

#### B5. AnimatedNumber — 1/1 ✅ (con caveat)
- [x] `src/components/ui/animated-number.tsx` con framer `animate()` + `useMotionValue`. Reduced motion → render directo. **NO aplicar a countdown timer** (BUG-313: rebote por interpolacion). Usar solo para deltas grandes (live pulse, stats)

#### B6. Smooth scroll — 1/1 ✅
- [x] `html { scroll-behavior: smooth }` en globals.css + `data-scroll-behavior="smooth"` para evitar warning Next 16 (BUG-312). Reduced motion media query lo apaga

#### B7. Theme transition cross-fade — 1/1 ✅
- [x] `body { transition: background-color 250ms ease, color 250ms ease }` en globals.css. Sin flash al togglear Noir ↔ Lux

#### B8. Reduced motion tier — 1/1 ✅
- [x] B1 (CSS), B3 (template + step transitions), B5 (animated number), B6 (scroll-behavior), B7 (transitions) — todos respetan `prefers-reduced-motion` via globals.css media query + hook `useReducedMotionPref()` para JS

#### B9. Gradient breathing pre-event — 1/1 ✅
- [x] Hero PreEventHome con `motion-safe:animate-[hero-breath_8s_ease-in-out_infinite]` keyframes (opacity 0.95→1.0 + scale 1→1.03). Radial gradient con accent + gold tinte sutil

#### B10. Scroll trigger entrance — 1/1 ✅
- [x] `src/hooks/useInView.ts` con IntersectionObserver. `once: true` por default (perfecto para listas largas sin re-trigger). Devuelve `[ref, inView]`. Sin uso aun (preparado para W.2/W.3)

#### B11. Swipe haptics mobile — 0/1 ⏳ DIFERIDO
- [ ] El Dialog de Radix no expone gesture lifecycle hooks facilmente. Mejor en W.X cuando tengamos sheets custom con drag (reorder, swipe-to-dismiss)

**Cierre F10.B**: typecheck + lint clean. Build production verde. Slide direccional entre steps validado por usuario en `/es/login` (email → sent → password) y modal forgot password (form → confirmation).

### F10.C — Premium (~1.5h) — ✅ 8/9 (C7 absorbido en B1)

#### C1. Error boundaries — 1/1 ✅
- [x] `src/components/error-boundary.tsx` clase ErrorBoundary global con `getDerivedStateFromError` + `componentDidCatch` + Sentry capture (tags: boundary, route + componentStack). Fallback EmptyState con retry button. Disponible para envolver subtrees especificos (no montado en route por default — el `app/error.tsx` cubre el caso global)

#### C2. 404/500/offline pages custom — 1/1 ✅
- [x] `app/not-found.tsx` Lumina con EmptyState variant `not_found` + CTA "Volver al inicio"
- [x] `app/error.tsx` con captureException Sentry + EmptyState variant `error` + retry button
- [x] Ambos con `<html lang="es">` + `<body>` propios (BUG-306) y `data-scroll-behavior="smooth"` (BUG-312)
- [ ] `app/offline.tsx` no creado por ahora — el ConnectionStatusPill (C5) cubre el feedback offline ambient

#### C3. Keyboard shortcuts globales — 1/1 ✅
- [x] `src/hooks/useKeyboardShortcut.ts` soporta string simple ("Escape", "?") o combinacion ("Mod+K" donde Mod=Cmd Mac/Ctrl Win), array de shortcuts, opcion `allowInInputs` (default false — no interfiere con escritura). NO aplicado a ningun componente aun (ready para W.0 command palette + sheets futuros)

#### C4. CopyButton — 1/1 ✅
- [x] `src/components/ui/copy-button.tsx` con clipboard.writeText + animacion check verde 1s + lumina.success. Sin uso aun (ready para W.10 Mi QR + W.7 sponsor profile + cualquier "copiar enlace")

#### C5. Connection status pill — 1/1 ✅
- [x] `src/components/support/ConnectionStatusPill.tsx` reemplaza al `NetworkStatusBanner` legacy (eliminado). Pill top-center con slide-down spring + WifiOff color `#FF7351`. Solo cubre online/offline (slow detection diferida — `feedback_no_polling.md` prohibe ping `/api/health`. Pendiente: usar TanStack Query observer para latencia real de fetches existentes)

#### C6. Save indicators — 1/1 ✅
- [x] `src/components/ui/save-indicator.tsx` componente con state machine `idle | saving | saved | error`. Iconos Loader2 (saving), Check verde (saved), AlertCircle rojo (error). AnimatePresence fade slide. Sin uso aun (ready para W.X profile edit, settings, drafts autosave)

#### C7. SubmittingButton — 1/1 ✅ (absorbido en F10.A submit refactor)
- [x] No es componente separado — el patron quedo integrado en LoginForm + ForgotPasswordSheet con conditional render `{submitting ? <Loader2 spin /> + t("submitting") : <text + ArrowRight>}`. Usa Button base + Loader2 inline. Replica naturalmente cuando se necesite

#### C8. Cross-tab sync — 1/1 ✅
- [x] `src/hooks/useCrossTabSync.ts` + `broadcastCrossTab()` helper. Listener `storage` event en localStorage. UserMenu logout aplica: cierra sesion en una tab → broadcast `eventos:logout` → otras tabs detectan + redirect /login + clear local state

#### C9. Page exit guard — 1/1 ✅
- [x] `src/hooks/useExitGuard.ts` con `beforeunload` listener (texto custom NO se respeta desde 2017 por Chrome anti-phishing — solo dialog default browser). Sin uso aun (ready para forms futuros con autosave / draft state)

**Cierre F10.C**: typecheck + lint clean. Build production verde. 404 visible en `/es/asdfasdf` (post-fix BUG-307 + BUG-308 + BUG-310). ConnectionStatusPill validado offline/online en DevTools Network throttle.

---

**Cierre F10 GLOBAL**: 26/28 items entregados (24/26 plan original + 2 extras = `lib/apiErrors.ts` + `apiFetch` Retry-After). Diferidos: B4 StaggerList + B11 swipe haptics + C2 offline page (cubierto por C5). 8 bugs corregidos en sesion auditoria visual con usuario (BUG-306 a BUG-313).

---

## Edge cases

- [x] Magic link expirado (>15min) → 410 `token_expired` + UI con CTA "Solicitar nuevo link" (F4)
- [x] Magic link usado dos veces → 410 `token_used` en segundo intento (F4 + Pest test)
- [x] Email caido en spam → instrucciones en MagicLinkSent + tip whitelist (F4)
- [x] User sin email registrado → mensaje generico anti-enumeration (F4 + Pest test)
- [x] Cookie corrupta / bearer revocado → `getCurrentUser()` 401 → clearAuthCookie + redirect login (F6)
- [x] Sin auth en ruta protegida → middleware redirect `/login?next={path}` (F6)
- [ ] Network offline durante verify → banner amber "Tu link sigue valido — reintenta cuando vuelvas" (F4 cubre via `useNetworkStatus`, falta validar)
- [ ] User sin password (solo magic link) → al intentar login con password muestra "Configura tu contrasena primero" (Fase 2)

---

## Pendientes login DaVinci (revision UI/UX 2026-05-02 con usuario)

Auditoria visual completa del login con feedback iterativo. Lo siguiente queda
pendiente — NO bloqueante para cerrar W.1, se atiende en F10.B/F10.C o sesion
de polish dedicada antes de Bancolombia demo:

- [ ] **Tab navigation**: validar orden Tab fluido entre fields (email → submit →
      step sent → password → submit). Probar con teclado real, no solo click.
- [ ] **CapsLock indicator** en password field: hint "CAPS LOCK ON" cuando el
      usuario tiene caps activo escribiendo password (UX nice-to-have, app movil
      no lo tiene tampoco)
- [ ] **Welcome-back state visual**: cuando hay `cached.email + cached.name` el
      form dice "Bienvenido de nuevo" pero sin avatar/inicial. Patron Linear/Slack
      = circulo con inicial del nombre cached + saludo. Ver F10.B.
- [ ] **Tablet portrait** (768-1023px): NO se usa segun decision usuario
      2026-05-02 — la webapp en tablet siempre va horizontal. No QA portrait.
- [ ] **CountdownBar visual progress**: el "Reenviar en 2:30" del step `sent`
      podria tener barra de progreso animada (decreciente) — feedback ambient
      del tiempo restante. Opcional.
- [ ] **Welcome to home transition**: al hacer router.push("/home") tras login
      exitoso, deberia haber transicion fade+slide en lugar de cut. Lo cubre
      F10.B B3 Page transitions.
- [ ] **Mailcheck suggestion en step password**: si edito el email en el step
      password (ya editable), el typo suggestion no aparece. Verificar.
- [ ] **CSP Vimeo + Sentry**: validar CSP estricto con Vimeo embed (W.4) +
      Sentry tunnel route `/monitoring`. F4 dejo CSP TODO.
- [ ] **Forgot password backend rate limit**: `POST /auth/forgot-password` no
      tiene rate limit dedicado en `AppServiceProvider.php` — usa el default
      `throttle:api` (60/min). Verificar antes de prod si Bancolombia exige
      anti-spam mas estricto.
- [ ] **Smoke test visual final**: validar los 4 viewports (desktop, tablet H,
      mobile portrait + landscape) con device real al cerrar F10.

---

## Cierre de modulo

- [x] Magic link funciona end-to-end con Mailpit local (F4 verificado)
- [x] Status gating cubre 5 estados Filament (draft/registration/published/live/ended) (F6)
- [x] Backend Sanctum + httpOnly cookie funcionando (F4+F6)
- [x] Commits DaVinci + memorias sesion + roadmap maestro v5.7
- [x] F8 Sentry frontend (3 configs + scrub PII + sourcemaps CI) — commit `d615bcf`
- [x] F9 Vitest (22) + Playwright (12) — commit `4e8e588`
- [ ] Validado en device real: Pixel + iPhone + iPad + desktop Chrome/Edge (smoke test final post-F10)
- [ ] Lighthouse Performance >= 85 desktop, >= 75 mobile (W.12 polish)
- [ ] PENDIENTES.md actualizado (al cerrar W.1 completo)

---

## Archivos creados (real)

**eventos-web**:
```
src/
  app/
    [locale]/
      layout.tsx                              // F1+F3 — fonts + intl + providers
      page.tsx                                // F6 — redirect /home
      (auth)/
        login/page.tsx                        // F4
        verify/page.tsx                       // F4
      (app)/
        layout.tsx                            // F6 — protected, AppHeader + slot
        home/page.tsx                         // F6 — switch por event.status
    api/auth/
      magic-link/route.ts                     // F4
      verify/route.ts                         // F4
      login/route.ts                          // F4 (password fallback)
      logout/route.ts                         // F4
    layout.tsx                                // F0 — pass-through root
    globals.css                               // F1 — tokens Lumina Noir/Lux
  components/
    auth/
      LoginCard.tsx                           // F4 — split layout
      LoginForm.tsx                           // F4 — state machine 4 steps
      LoginSlideshow.tsx                      // F4 — Ken Burns + video
      LivePulse.tsx                           // F4
      EventStatusPill.tsx                     // F4
      EventLogo.tsx                           // F4 — single/doble
      TabletRotateOverlay.tsx                 // F4
      NetworkStatusBanner.tsx                 // F4
      UserMenu.tsx                            // F6
    app/
      AppHeader.tsx                           // F6
      home/
        PreEventHome.tsx                      // F6 — draft/registration
        PublishedHome.tsx                     // F6 — published
        LiveHome.tsx                          // F6 — live
        EndedHome.tsx                         // F6 — ended
    providers/
      ThemeProvider.tsx                       // F1 — next-themes Noir/Lux
    ThemeToggle.tsx                           // F1
    LanguageSwitcher.tsx                      // F3
    ui/                                       // F2 — 15 shadcn components
  hooks/
    useMediaQuery.ts                          // F1
    useReducedMotionPref.ts                   // F1
    useIsClient.ts                            // F1
    useLastEmail.ts                           // F4 — Tier 1 cached email
    useNetworkStatus.ts                       // F4 — Tier 2 offline banner
  i18n/
    routing.ts                                // F3
    request.ts                                // F3
    navigation.ts                             // F3
  lib/
    api.ts                                    // F4 — apiFetch wrapper
    cookies.ts                                // F4 — httpOnly helpers
    authValidators.ts                         // F4 — zod schemas
    mailcheck.ts                              // F4 — Tier 1 typo
    publicEvent.ts                            // F4 — fetch event + slides
    auth.ts                                   // F6 — getCurrentUser server-side
    types/event.ts                            // F4+F6 — types
    utils.ts                                  // F2 — cn helper
  proxy.ts                                    // F3+F6 — i18n + auth gate
  messages/{es,en,pt}.json                    // F3
global.d.ts                                   // F3 — type-safe i18n
.github/workflows/ci.yml                      // F0
```

**eventos-backend** (W.1B + F6.A):
- `app/Models/MagicLinkToken.php` (W.1B)
- `app/Models/EventLoginSlide.php` (W.1B)
- `app/Mail/MagicLinkMail.php` (W.1B)
- `app/Http/Controllers/Api/V1/PublicEventController.php` (W.1B + F6.A status)
- `app/Filament/Resources/LoginSlideResource.php` + 3 Pages (W.1B)
- `app/Observers/EventLoginSlideObserver.php` (W.1B)
- `app/Http/Requests/Api/V1/Auth/MagicLinkRequest.php` + `VerifyMagicLinkRequest.php` (W.1B)
- 4 migrations (`magic_link_tokens`, `email_template_enums`, `event_login_slides`, `events.organizer_logo_url`)
- `database/seeders/MagicLinkEmailTemplateSeeder.php`
- `tests/Feature/Auth/MagicLinkTest.php` + `tests/Feature/PublicEvent/LoginSlidesTest.php` (10/10 passing)

**F8 — Sentry (creado):**
- `sentry.client.config.ts` + `sentry.server.config.ts` + `sentry.edge.config.ts`
- `instrumentation.ts` + `instrumentation-client.ts` (Next 15+ pattern)
- `next.config.ts` wrapped con `withSentryConfig`

**F9 — Tests (creado):**
- `vitest.config.ts` + `tests/setup.ts`
- `tests/lib/mailcheck.test.ts` (6) + `authValidators.test.ts` (10) + `api.test.ts` (5) → 22 unit
- `playwright.config.ts` + `e2e/auth-gate.spec.ts` (4) + `login-form.spec.ts` (4) + `verify-page.spec.ts` (4) → 12 E2E
- `.github/workflows/ci.yml` actualizado (jobs `check` + `e2e`)
