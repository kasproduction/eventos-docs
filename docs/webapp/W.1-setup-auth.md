# W.1 — Setup + Auth + Tour de bienvenida

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

## Fase 3 — i18n base (~1h) — 0/5

### 3.1 next-intl — 0/2
- [ ] `pnpm add next-intl`
- [ ] Configurar `i18n.ts` + `middleware.ts` para detectar locale + redirect

### 3.2 Catalogos — 0/3
- [ ] `messages/es-CO.json`, `messages/en.json`, `messages/pt-BR.json` con keys base (auth, nav, errors)
- [ ] Estructura: `auth.login.title`, `auth.login.email`, `auth.errors.invalid`, etc.
- [ ] Helper `useTranslations()` documentado

---

## Fase 4 — Auth — Magic link (~2.5h) — 0/8

### 4.1 Endpoint backend nuevos — 0/3
- [ ] `POST /api/v1/auth/magic-link` (backend, sesion separada o paralela): genera token, envia email
- [ ] `POST /api/v1/auth/verify-magic-link` (backend): valida token, devuelve Bearer
- [ ] Tabla `magic_link_tokens` migration + tests Pest

### 4.2 Frontend pages — 0/3
- [ ] `src/app/(auth)/login/page.tsx` con form email + boton "Enviar link"
- [ ] `src/app/(auth)/login/MagicLinkSent.tsx` (estado post-envio: "Revisa tu email")
- [ ] `src/app/(auth)/verify/page.tsx` recibe `?token=XXX` → POST verify → guarda cookie → redirect

### 4.3 Next API routes — 0/2
- [ ] `src/app/api/auth/magic-link/route.ts` proxy POST al backend
- [ ] `src/app/api/auth/verify/route.ts` proxy POST al backend + setea httpOnly cookie

---

## Fase 5 — Auth — Email + password fallback (~1h) — 0/4

### 5.1 Frontend — 0/2
- [ ] `LoginForm.tsx` con tabs: "Magic link" (default) | "Contrasena"
- [ ] Tab contrasena: form email + password + boton "Iniciar sesion"

### 5.2 API routes — 0/2
- [ ] `src/app/api/auth/login/route.ts` proxy POST al backend
- [ ] Test happy path con Mailpit local

---

## Fase 6 — Layout shell + middleware (~1.5h) — 0/6

### 6.1 Middleware — 0/2
- [ ] `src/middleware.ts` valida cookie `auth` en rutas protegidas
- [ ] Si no hay cookie → redirect `/login?next=...`

### 6.2 Layout protegido — 0/2
- [ ] `src/app/(app)/layout.tsx` con header (logo + theme switcher + user menu) + slot principal
- [ ] Placeholder de PillBar (W.0 lo construye)

### 6.3 User menu — 0/2
- [ ] `<UserMenu />` con avatar + nombre + dropdown (Perfil, Configuracion, Cerrar sesion)
- [ ] Logout: POST `/api/auth/logout` → borra cookie → redirect `/login`

---

## Fase 7 — Tour de bienvenida (~1.5h) — 0/6

### 7.1 Componente — 0/3
- [ ] `<WelcomeTour />` overlay full screen con 4-6 escenas
- [ ] Cada escena: highlight de un area de la UI + texto explicativo + boton "Siguiente"
- [ ] Cursor simulado animado (Framer Motion) recorriendo features

### 7.2 Escenas — 0/2
- [ ] Escena 1: Pill bar / nav (mostrar como navegar)
- [ ] Escena 2: Happening Now (sesion en vivo)
- [ ] Escena 3: Agenda + favoritos
- [ ] Escena 4: Connect (networking + social)
- [ ] Escena 5: Profile + notificaciones
- [ ] Escena 6: Cierre con CTA "Empezar"

### 7.3 Logica skip + persist — 0/1
- [ ] Boton "Saltar" en cualquier momento
- [ ] Al completar o skip: `localStorage.setItem('onboarding_completed', '1')`
- [ ] Hook `useFirstTimeUser()` decide si mostrar o no

---

## Fase 8 — Sentry + observabilidad (~30min) — 0/3

### 8.1 Sentry — 0/2
- [ ] `pnpm add @sentry/nextjs`
- [ ] `sentry.client.config.ts`, `sentry.server.config.ts`, `sentry.edge.config.ts` con DSN

### 8.2 Source maps — 0/1
- [ ] CI uploadea source maps en build (no en cliente)

---

## Fase 9 — Tests (~1h) — 0/5

### 9.1 Vitest — 0/2
- [ ] Test `useAuth` hook: login, logout, refresh
- [ ] Test API proxy routes: adjuntan Bearer correctamente

### 9.2 Playwright — 0/3
- [ ] Happy path magic link: enviar email → recibir en Mailpit → click link → llegar a /home
- [ ] Edge case: token invalido muestra error correcto
- [ ] Edge case: rate limit muestra mensaje + countdown

---

## Edge cases

- [ ] Magic link expirado (>15min) → mensaje claro + CTA "Reenviar"
- [ ] Magic link usado dos veces → error en segundo intento
- [ ] Email caido en spam → instrucciones en pantalla post-envio
- [ ] User sin password (solo magic link) → al intentar login con password muestra "Configura tu contrasena primero"
- [ ] Cookie corrupta → middleware redirect login + flush cookie
- [ ] User cambia de pestana durante tour → tour pausa y resume
- [ ] User refresh durante tour → tour sigue abierto (estado en sessionStorage)
- [ ] User sin email registrado → mensaje generico anti-enumeration

---

## Cierre de modulo

- [ ] Vitest + Playwright verde
- [ ] Validado en device real: Pixel + iPhone + iPad + desktop Chrome/Edge
- [ ] Lighthouse Performance >= 85 desktop, >= 75 mobile
- [ ] Magic link funciona end-to-end con Mailpit local
- [ ] Tour cinematico fluido en los 3 viewports
- [ ] Commit DaVinci + memoria sesion + PENDIENTES.md actualizado

---

## Archivos creados

```
src/
  app/
    (auth)/
      login/page.tsx
      login/LoginForm.tsx
      login/MagicLinkSent.tsx
      verify/page.tsx
      set-password/page.tsx
    (app)/
      layout.tsx
      home/page.tsx                  // placeholder, W.2 lo termina
    api/
      auth/
        login/route.ts
        magic-link/route.ts
        verify/route.ts
        refresh/route.ts
        logout/route.ts
        sessions/route.ts
      proxy/[...path]/route.ts
    layout.tsx                        // root layout con fonts, theme, sentry
    globals.css
  components/
    auth/
      LoginForm.tsx
      MagicLinkSent.tsx
      UserMenu.tsx
      WelcomeTour.tsx
      WelcomeTourScene.tsx
  hooks/
    useAuth.ts
    useFirstTimeUser.ts
    useTheme.ts
    useIdleTimer.ts
  lib/
    api.ts                            // wrapper fetch con Bearer
    cookies.ts                        // helpers httpOnly cookies
  middleware.ts
  i18n.ts
  messages/
    es-CO.json
    en.json
    pt-BR.json
sentry.client.config.ts
sentry.server.config.ts
sentry.edge.config.ts
playwright.config.ts
vitest.config.ts
```
