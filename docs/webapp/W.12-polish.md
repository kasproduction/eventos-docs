# W.12 — Polish + E2E final

> Pase final de pulido: responsive validado en device real, transiciones fluidas, loading states completos, empty states diseñados, accesibilidad, SEO, PWA install prompt, E2E suite verde.
>
> **Estimacion:** ~8h.
> **Dependencias:** W.0-W.11 cerrados.
> **Estado:** Pendiente.

---

## Lectura obligatoria

- `PLAN.md`, `RESPONSIVE-SPEC.md`, `DESIGN-SYSTEM.md`, `AUTH-SPEC.md`
- Todos los `W.X-*.md` para validar checklists

---

## Alcance

1. Responsive validado en 3 viewports + device real
2. Transiciones spring fluidas en toda la webapp
3. Loading states (skeletons) en cada modulo
4. Empty states diseñados por viewport
5. Accesibilidad: WCAG AA en todos los modulos
6. SEO basico (meta tags, OG, sitemap)
7. PWA: manifest, install prompt condicional, service worker minimo
8. Print stylesheet (al menos no se rompe)
9. Performance: Lighthouse >= 85 desktop / >= 75 mobile
10. E2E suite completa Playwright
11. Sentry: validar reportes en produccion staging

---

## Fase 0 — Audit responsive (~1.5h) — 0/4

### 0.1 Validacion device real — 0/4
- [ ] Pixel 6 / Android: cada modulo W.0-W.10
- [ ] iPhone 14 / iOS 16+: cada modulo
- [ ] iPad 10": cada modulo
- [ ] Desktop Chrome + Edge + Safari + Firefox

Cualquier inconsistencia visual / interactiva → fix in-place.

---

## Fase 1 — Loading + Empty states (~1.5h) — 0/3

### 1.1 Skeletons — 0/2
- [ ] Cada modulo tiene skeleton de su lista/grid principal
- [ ] Tokens consistentes (`noirSkeleton` / `luxSkeleton`)

### 1.2 Empty states — 0/1
- [ ] Cada modulo tiene empty state con ilustracion sutil + texto + CTA

---

## Fase 2 — Accesibilidad (~1.5h) — 0/5

### 2.1 ARIA — 0/2
- [ ] Iconos sin texto: `aria-label`
- [ ] Modales: `role="dialog"` + `aria-labelledby`

### 2.2 Keyboard — 0/2
- [ ] Tab navigation completa
- [ ] Esc cierra modales
- [ ] Enter envia forms

### 2.3 Lighthouse — 0/1
- [ ] Lighthouse Accessibility >= 95 todos los modulos

---

## Fase 3 — Performance (~3h) — 0/8

### 3.1 Bundle analysis — 0/2
- [ ] `pnpm add -D @next/bundle-analyzer`
- [ ] Bundle inicial < 200KB gzipped

### 3.2 Code splitting — 0/2
- [ ] Cada modulo lazy-imported via dynamic import
- [ ] Lighthouse Performance >= 85 desktop, >= 75 mobile

### 3.2b Ambient prefetch — 0/2 (idea Kamilo 2026-06-21)
> Patron Linear/Notion: pre-cachear las rutas top-level del sidebar EN
> BACKGROUND mientras el usuario interactua con cualquier ruta
> autenticada (o con el onboarding). Cuando navega, todo esta warm y
> NO ve skeletons en cada click.
>
> Implementacion: `<RouteWarmer />` en `(app)/layout.tsx`. Al mount,
> `requestIdleCallback` → `router.prefetch()` paralelo de las 5 rutas
> restantes del sidebar (skip la activa). Auto-skip rutas disabled.
> Skipear si `navigator.connection.saveData === true`.
>
> Bonus: pagina del onboarding tambien dispara prefetch al boton
> "Comenzar" — para cuando el usuario aterriza en /home, las 5 rutas
> del sidebar ya estan en cache.
>
> Limites: solo prod (dev igual compila on-demand). Funciona con el
> RSC payload Next 16, complementa la migracion 3.3 (TanStack + push
> invalidation) pero es ortogonal — esto cubre el RSC del shell, esa
> cubre los datos del modulo.

- [ ] `<RouteWarmer />` en `(app)/layout.tsx` con `requestIdleCallback`
- [ ] Bonus: prefetch tambien en ultima pantalla del onboarding

### 3.3 Migracion fetchers SSR → TanStack Query con invalidacion push (DEPENDE DE W.11) — 0/4

> **Arquitectura objetivo (paridad mobile):** la app Expo cachea con
> `staleTime: Infinity` y SOLO invalida cuando el socket avisa que algo
> cambio en backend. La webapp deberia hacer lo mismo. Hoy (2026-05-09)
> usa `staleTimes` del router Next como parche — funciona pero hace
> refetch cada N min sin razon. Reemplazar por modelo push.
>
> **Mejora esperada:** nav promedio 300-500ms → <100ms (5-10x). Skeleton
> solo en very-first-load del modulo. Cero refetch innecesario cuando el
> organizador no toco nada.
>
> **Bloqueado por:** W.11 sockets RT debe estar wireado y el backend debe
> emitir eventos por modulo afectado (`agenda:updated`, `speakers:updated`,
> `event:branding_updated`, etc.) desde Filament observers.

- [ ] Mover fetchers (`fetchAgenda`, `fetchSpeakers`, `fetchHappeningNow`,
      `fetchPublicEvent`, `getCurrentUser`) a TanStack Query hooks
      client-side con `staleTime: Infinity`, `gcTime: 30min`
- [ ] Wrapper `useEventInvalidation()` que escucha eventos socket y hace
      `qc.invalidateQueries({ queryKey: [...] })` por evento
- [ ] Quitar `experimental.staleTimes` de `next.config.ts` (ya no aplica)
- [ ] Quitar `loading.tsx` por modulo donde el render sea client-side
      (TanStack Query maneja loading state directo) — solo dejar el
      generico (app)/loading.tsx para el very-first-load del shell

---

## Fase 4 — SEO + meta (~30min) — 0/3

### 4.1 Meta tags — 0/2
- [ ] OG: og:title, og:description, og:image (logo eventos)
- [ ] Twitter Card

### 4.2 Sitemap — 0/1
- [ ] `app/sitemap.ts` con rutas publicas (login, verify, set-password)
- [ ] Rutas autenticadas no se indexan (robots.txt)

---

## Fase 5 — PWA (~1.5h) — 0/5

### 5.1 Manifest — 0/2
- [ ] `app/manifest.ts` con name, short_name, icons (192, 512), theme_color, background_color
- [ ] start_url, display: standalone

### 5.2 Service worker — 0/2
- [ ] `next-pwa` o custom SW con cache fonts, manifest, assets estaticos
- [ ] No cachear data dinamica (RT siempre fresco)

### 5.3 Install prompt condicional — 0/1
- [ ] Detectar plataforma: desktop/tablet → mostrar prompt. Mobile → no mostrar
- [ ] Boton "Instalar app" en menu user

---

## Fase 6 — Print stylesheet (~30min) — 0/2

- [ ] `@media print` minimo: hide nav, expand panels
- [ ] Probar print de agenda + perfil

---

## Fase 7 — E2E suite final (~1.5h) — 0/4

### 7.1 Smoke test — 0/2
- [ ] Login magic link → home → cada modulo abre sin errores
- [ ] Logout → vuelve a login

### 7.2 Critical paths — 0/2
- [ ] Asistir sesion completa (joinear streaming + chat + Q&A + poll)
- [ ] Networking (matchmaking + connect + chat 1:1)

---

## Fase 8 — Sentry validation (~30min) — 0/2

- [ ] Generar error intencional en staging → verificar Sentry recibe
- [ ] Source maps en Sentry (clickear stack trace muestra TypeScript)

---

## Cierre del modulo (y de la webapp Fase 1)

- [ ] Todos los modulos W.0-W.11 cerrados
- [ ] E2E suite verde
- [ ] Lighthouse en todos targets
- [ ] Validacion en 4 navegadores
- [ ] Validacion en device real (Pixel + iPhone + iPad + desktop)
- [ ] PWA install funcional desktop/tablet
- [ ] Sentry recibe errores
- [ ] Deploy staging DO sao1 OK
- [ ] Deploy produccion DO sao1 con dominio `app.eventos.app`
- [ ] Commit DaVinci final
- [ ] Memoria sesion final con resumen webapp completa
- [ ] PENDIENTES.md seccion 2 cerrada (W.0-W.12 todos en N/N)
- [ ] EventOS_Roadmap.md actualizado con webapp Fase 1 cerrada

---

## Despues de Fase 1 (no entra aqui)

- Stress test 10K en webapp (PENDIENTES seccion 7)
- W.X.1 ajustes feedback Bancolombia post-demo
- Multilenguaje completo si pidieron mas idiomas
- Webapp + recap integrado (recap solo movil Fase 1, integracion web Fase 2)
- White-label config dinamica (clientes/{slug})
