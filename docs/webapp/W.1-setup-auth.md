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

## Fase 8 — Sentry + observabilidad (~30min) — 0/3

### 8.1 Sentry — 0/2
- [ ] `pnpm add @sentry/nextjs`
- [ ] `sentry.client.config.ts`, `sentry.server.config.ts`, `sentry.edge.config.ts` con DSN

### 8.2 Source maps — 0/1
- [ ] CI uploadea source maps en build (no en cliente)

---

## Fase 9 — Tests (~1h) — 0/5 — DESBLOQUEADA (backend listo)
> **Despues de F9 viene F10 UI/UX foundation (~6.5h, ADR-026)** — sistema completo de feedback, microinteracciones, error handling. Cierra W.1 con base solida para todos los modulos siguientes.

### 9.1 Vitest — 0/2
- [ ] Test `useAuth` hook: login, logout, refresh
- [ ] Test API proxy routes: adjuntan Bearer correctamente

### 9.2 Playwright — 0/3
- [ ] Happy path magic link: enviar email → recibir en Mailpit → click link → llegar a /home
- [ ] Edge case: token invalido muestra error correcto
- [ ] Edge case: rate limit muestra mensaje + countdown

---

## Fase 10 — UI/UX foundation DaVinci (~6.5h) — 0/26 (ADR-026)

> Cimiento de UI/UX para todos los modulos siguientes. Cero modulos siguientes (W.0/W.2+) hasta tener este sistema. Razon: evitar refactor masivo en W.12.

### F10.A — Foundation (~3h) — 0/6

#### A1. LuminaToast wrapper — 0/1
- [ ] `src/components/ui/lumina-toast.tsx` wrapper sobre Sonner: 5 variantes (success/error/info/calendar/favorite) con iconos lucide custom + colores Lumina + haptics web (`navigator.vibrate(50)` Android Chrome, falla silente iOS web)
- API: `lumina.success({ message, description?, haptic? })`
- Reemplaza usos de `toast.*` en LoginForm

#### A2. FormField reutilizable — 0/1
- [ ] `src/components/ui/form-field.tsx` wrapper sobre shadcn Input
- Props: `label`, `error`, `hint`, `register` (react-hook-form pattern)
- AnimatePresence error con icono `AlertCircle` slide-in
- Border rojo + ring automatic cuando error
- Refactor LoginForm email + password fields

#### A3. EmptyState — 0/1
- [ ] `src/components/ui/empty-state.tsx` con 4 variantes:
  - `not_found` (SearchX) "Sin resultados"
  - `not_yet` (Clock) "Aun no hay {X}"
  - `error` (AlertTriangle) "Hubo un error"
  - `success` (CheckCheck) "Listo"
- Props: `variant`, `title`, `description?`, `action?` (button)

#### A4. Skeleton patterns — 0/1
- [ ] 5 componentes pre-armados en `src/components/ui/skeletons.tsx`:
  - `<SkeletonCard />`, `<SkeletonList count={n} />`, `<SkeletonAvatar />`, `<SkeletonText lines={n} />`, `<SkeletonGrid cols={n} count={n} />`
- Match exact al loaded state (no genericos)

#### A5. Refactor F4 + F6 — 0/1
- [ ] LoginForm usa FormField en email + password
- [ ] LoginForm `toast.*` → `lumina.*`
- [ ] PreEventHome stats → SkeletonGrid durante loading
- [ ] LiveHome / EndedHome usan Skeleton durante hydration

#### A6. useOptimistic helper — 0/1
- [ ] `src/hooks/useOptimisticMutation.ts` wrapper sobre TanStack Query mutate
- Toggle local instant + revalidate background
- Rollback automatic en error + lumina.error toast
- Prepara W.3 favorites, W.6 likes, W.9 passport stamps

### F10.B — Polish (~2.5h) — 0/11

#### B1. Button micro-feedback — 0/1
- [ ] `<MotionButton />` wrapper sobre shadcn Button con Framer Motion: `whileTap`, `whileHover`, haptic mobile

#### B2. Focus rings dynamic — 0/1
- [ ] `:focus-visible` con outline 2px solid `--accent` + offset 3px + shadow ring `0 0 0 4px color-mix(--accent, 20%)`
- Transicion 150ms

#### B3. Page transitions — 0/1
- [ ] `<PageTransition />` AnimatePresence en root layout
- Login → home: fade + slide up subtle (300ms spring)
- Login step → step: horizontal slide spring
- Home variants switch: fade

#### B4. Stagger entrance — 0/1
- [ ] `<StaggerList />` componente — items entran con delay 100ms
- Aplica a EventInfoCard items, listas futuras

#### B5. AnimatedNumber — 0/1
- [ ] `<AnimatedNumber value={n} />` anima del valor anterior al nuevo (no salta)
- Aplica a countdown timer, live pulse counter, stats

#### B6. Smooth scroll — 0/1
- [ ] CSS `scroll-behavior: smooth` global (con `prefers-reduced-motion: reduce` respeto)
- Helper `scrollIntoView({ behavior: 'smooth', block: 'start' })`

#### B7. Theme transition cross-fade — 0/1
- [ ] `* { transition: background-color 250ms, color 250ms, border-color 250ms }` global limited
- Sin flash al togglear Noir ↔ Lux

#### B8. Reduced motion tier — 0/1
- [ ] B1-B7 respetan `useReducedMotionPref()` — animaciones se vuelven instantaneas

#### B9. Gradient breathing pre-event — 0/1
- [ ] Hero PreEventHome con animacion gradient sutil pulse 8s (opacity 0.95 → 1.0)

#### B10. Scroll trigger entrance — 0/1
- [ ] `useInView` IntersectionObserver — list items entran cuando entran al viewport
- Performance: solo primera vista, no infinite loop

#### B11. Swipe haptics mobile — 0/1
- [ ] Swipe gestures en sheets / drag handles disparan `navigator.vibrate(30)` Android Chrome

### F10.C — Premium (~1.5h) — 0/9

#### C1. Error boundaries — 0/1
- [ ] `<ErrorBoundary />` wrapper en `src/components/error-boundary.tsx`
- Captura errores render + reporta Sentry + UI bonita "Algo salio mal" + retry button
- Per-route + global

#### C2. 404/500/offline pages custom — 0/1
- [ ] `app/not-found.tsx` Lumina Noir con icono SearchX + CTA "Volver"
- [ ] `app/error.tsx` con retry
- [ ] `app/offline.tsx` con instrucciones reconnect

#### C3. Keyboard shortcuts globales — 0/1
- [ ] `useKeyboardShortcut` hook
- Esc cierra modales/overlays
- `?` muestra help (placeholder)
- Cmd+K / Ctrl+K reservado para command palette W.0

#### C4. CopyButton — 0/1
- [ ] `<CopyButton text="..." />` con `navigator.clipboard` + animacion check verde 1s + lumina.success

#### C5. Connection status pill — 0/1
- [ ] Mejora del NetworkStatusBanner: pill bottom-right con 3 estados (online/slow/offline)
- Latency check `/api/health` cada 30s
- Tooltip "Conexion lenta" cuando >500ms

#### C6. Save indicators — 0/1
- [ ] Pattern reusable "Guardando..." ↔ "Guardado" con check
- Para futuros forms con autosave

#### C7. SubmittingButton — 0/1
- [ ] `<SubmittingButton submitting={bool} idle="Enviar" submitting="Enviando..." />` con spinner inline

#### C8. Cross-tab sync — 0/1
- [ ] Logout en una tab → otras detectan via `storage` event de `localStorage`
- Aplica a `eventos:lastEmail` cleanup en logout

#### C9. Page exit guard — 0/1
- [ ] `useExitGuard(isDirty: boolean)` hook
- `beforeunload` listener con confirm dialog si form esta dirty

**Cierre F10**: typecheck + lint + build verde, smoke test visual del login + home variants en browser real, demos en `design/features/webapp/UI/` (opcional). Sistema listo para que W.0/W.2+ lo consuman desde dia 1.

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

## Cierre de modulo

- [x] Magic link funciona end-to-end con Mailpit local (F4 verificado)
- [x] Status gating cubre 5 estados Filament (draft/registration/published/live/ended) (F6)
- [x] Backend Sanctum + httpOnly cookie funcionando (F4+F6)
- [x] Commits DaVinci + memorias sesion + roadmap maestro v5.7
- [ ] F8 Sentry frontend (~30min)
- [ ] F9 Vitest hooks + Playwright happy path (~1h)
- [ ] Validado en device real: Pixel + iPhone + iPad + desktop Chrome/Edge (F9 + smoke test final)
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

**Pendientes F8 + F9** (no creados aun):
- `sentry.client.config.ts` + `sentry.server.config.ts` + `sentry.edge.config.ts` (F8)
- `vitest.config.ts` + tests unit hooks (F9)
- `playwright.config.ts` + happy path E2E (F9)
