# Siguiente sesion — punto de entrada unico

> **Como usar este archivo:** al arrancar nueva sesion, simplemente decime
> **"siguiente"** o **"next"** y leo este archivo + retomo donde quedamos.
> Yo lo actualizo al cierre de cada sesion (paso del workflow DaVinci).
>
> Esto reemplaza el "tengo que recordar 3 contextos" — todo lo necesario para
> arrancar vive aca.

---

## Ultima sesion

**Fecha:** 2026-05-08
**Que se hizo:**
- W.5 Speakers webapp implementado completo (24 archivos en eventos-web)
- Demo HTML v2 alineado al Expo (espejo mobile + wireframe agenda webapp)
- Refactor: RatingPop shared en components/ui (agenda y speakers son wrappers)
- Agenda highlight extension: ?highlight=X con scroll + ripple + pulse glow
- Sidebar speakers item available + aria-current
- Polish: stagger fade-in, empty states ilustrados, race protection panel,
  optimistic rating, focus trap modal, touch targets 36px, aria-live
- 20 vitest speakersDerive verde (total webapp 103/103)

**Commits:**
- `eventos-web` → `134bf6e feat(W.5): speakers module + agenda highlight`
- `APP EVENTOS` → `c7c5de4 docs(W.5): scope real + tests backlog + demo v2`

**No pusheado.** Decision pendiente del usuario.

---

## Proxima sesion

### Tarea principal: **Tests E2E retroactivos**

Detalle completo en `docs/webapp/TESTS-PENDIENTES.md`.

**Orden recomendado:**

1. ☐ **W.5 Speakers E2E** — `e2e/speakers.spec.ts` (~12 tests, 45 min)
2. ☐ **W.3 Agenda E2E** — `e2e/agenda.spec.ts` (~15 tests, incluye highlight pulse, 60 min)
3. ☐ **W.4 Streaming E2E** — `e2e/streaming.spec.ts` (~10 tests, 45 min)
4. ☐ **W.2 Home E2E** — `e2e/home.spec.ts` (~7 tests, 30 min)
5. ☐ **Vitest gaps** — speakersClient, agendaClient, view component tests (~20 tests, 60 min)

**Total estimado:** ~5-6h distribuido en 4-5 sesiones.

**Para arrancar diga:** "siguiente" o "tests speakers" o "arranquemos w.5 e2e".

### Prep necesario antes de codear (yo lo hago al inicio de la sesion)

1. Verificar como simular auth cookie en Playwright (mock `/api/auth/me`)
2. Crear `e2e/_helpers/mockAuth.ts` reusable
3. Crear `e2e/_fixtures/speakers.ts` con 6 speakers realistas
4. Si dev server no corre, levantar `pnpm dev`

### Decisiones ya cerradas (no preguntar de nuevo)

- E2E mockea fetches con `page.route('**/api/...')` — NO requiere backend Laravel
- Sin tracks, sin chips, sin favorite speakers (espejo Expo)
- Click sesion en speaker detail → `/agenda?highlight=X` (no `/session/[id]`)
- Featured derivado de keynote sessions (fallback top 5 by sessions count)
- Foto detail cap max-width 260 centrada (no full-width)
- LinkedIn condicional con `target="_blank"` cuando linkedin_url presente
- Modal compartido en `components/ui/rating-pop.tsx` con prop `labels`
- Orden alfabetico en "Todos"
- SSR en page.tsx con `fetchSpeakers` + `fetchMySpeakerRatings`

---

## Pendientes paralelos (cuando termine tests)

Lista priorizada del backlog DaVinci aprobado 2026-05-08 (no atacados aun
porque tests es prioridad 1):

**UX visible:**
- Empty state ilustrado (✅ aplicado)
- Stagger fade-in cards (✅ aplicado)
- Highlight pulse ripple (✅ aplicado)
- Carousel fade gradient (✅ aplicado)

**Robustez:**
- Optimistic UI rating (✅ aplicado)
- Race protection panel (✅ aplicado)
- Stale-while-revalidate myRatings — pendiente

**A11y:**
- Touch targets 36px (✅ aplicado)
- aria-live counter (✅ aplicado)
- aria-current sidebar (✅ aplicado)
- Focus trap modal (✅ aplicado via RatingPop shared)

**Backend (cross-team):**
- Featured/keynote como flags reales en DB
- Avg rating threshold ≥3 en lista (decision producto)
- Endpoint `/speakers/{id}/sessions/preview` (optimizar payload)

**Mobile parity:**
- Portar "click sesion → agenda highlight" al Expo

**Tracking analytics:**
- Eventos `speakers.list_viewed`, `speakers.detail_opened`,
  `speakers.rated`, `speakers.session_clicked`, `speakers.linkedin_clicked`
  (requiere infraestructura de telemetria — no hay helper estandar)

**Validacion manual (no tengo browser):**
- 3 viewports (tablet H, tablet vertical lock, mobile webapp)
- Lighthouse audit (sospecha < 90 perf por imagenes pravatar — usar `next/image`)

---

## Convenciones / contexto operativo

- **Working dir principal:** `C:\laragon\www\APP EVENTOS` (este repo, docs+design)
- **Webapp Next.js:** `C:\laragon\www\eventos-web`
- **Mobile Expo:** `C:\Users\Kasproduction\Projects\eventos-app`
- **Backend Laravel:** `C:\laragon\www\eventos-backend`
- **Modo de trabajo:** DaVinci — calidad sobre cantidad, cero emojis
- **Workflow git:** commits cuando usuario diga "commit" / "guardar". Push solo
  con palabra explicita "push". Nunca skip hooks.
- **Usuario:** Kamilo Arias (solo founder), idioma espanol coloquial

---

## Como cierro cada sesion (yo, automaticamente)

Al final de cada sesion productiva, actualizo este archivo con:
1. Que se hizo (resumen 3-5 bullets)
2. Commits hechos (hashes)
3. Que sigue (proxima tarea concreta + prompt para arrancar)
4. Decisiones cerradas que no se deben preguntar de nuevo
5. Pendientes paralelos sin bloquear

Asi no tienes que recordar nada — solo abrir esto.
