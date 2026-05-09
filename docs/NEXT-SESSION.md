# Siguiente sesion — punto de entrada unico

> **Como usar este archivo:** al arrancar nueva sesion, simplemente decime
> **"siguiente"** o **"next"** y leo este archivo + retomo donde quedamos.
> Yo lo actualizo al cierre de cada sesion (paso del workflow DaVinci).

---

## Ultima sesion

**Fecha:** 2026-05-09 (jornada larga — 3 pases)
**Que se hizo:** Tests retroactivos + perf tuning navegacion + exploracion UX modulo `/live`.

**Infra E2E nueva:**
- `mockBackend.mjs` Node http minimal (puerto 8101, separado de Laragon)
- `_fixtures/data.mjs` con event/user/speakers/myRatings + agenda 10
  sesiones (2 dias) + happening-now + 3 estados de recap
- `mockAuth.ts` setAuthCookie helper (cookie tagueada permite variar
  scenarios via bearer match en el mock)
- `playwright.config.ts` con 2 webServers (mock 8101 + dev 3100)

**E2E retroactivos (51 tests nuevos):**
- W.5 Speakers (14): SSR auth, search ⌘K, panel detail, stars, LinkedIn
  condicional, rating 200/409/500, click sesion → highlight, deep link, Esc
- W.3 Agenda (16): days strip, tabs, chips, search por speaker, click
  card → DetailPanel, favorite optimistic 200/500, rate session past,
  highlight pulse `?highlight=X` con switch dia + clear class + clear URL
- W.4 Streaming (9): live/replay/empty player, badge En vivo/Grabacion,
  about session, rating + auto-prompt en replay, mobile shell
- W.2 Home (6): 3 estados pre/live/ended + 3 variantes de recap
  (available/not-eligible/disabled) via bearer-tag

**Vitest nuevos (15 tests):**
- `tests/lib/speakersClient.test.ts` — rateSpeakerRequest 200/409/422/fallback
  + fetchMySpeakerRatingsClient con map conversion
- `tests/lib/agendaClient.test.ts` — toggleFavoriteRequest, rateSessionRequest,
  fetchMyRatings con shape + status checks

**Bugs reales detectados y corregidos durante los tests:**
- `AgendaView.tsx`: `usePathname` venia de `next/navigation` (no locale-aware)
  → al limpiar `?highlight` la URL terminaba en `/es/es/agenda`. Fix: usar
  `usePathname` desde `@/i18n/navigation`.
- `lib/publicEvent.ts`: `fetchPublicEvent` no enviaba bearer (endpoint publico).
  Cambio minimo a forwardear bearer si existe — hace personalizacion posible
  + nos da el canal de tests para variar event.status sin levantar mocks.

**Suite total:** 57 E2E + 118 vitest = **175 tests verde**.

**Bugs descubiertos por los tests + atacados despues (2026-05-09 sesion B):**
1. **Hydration mismatch** en `SpeakerDetailPanel`, `StreamShell`, `DetailPanel`
   por `toLocaleTimeString("es-CO")` con `hour12=true` → V8 (Chromium/Node 22+)
   inserta U+202F (narrow no-break space) entre la hora y el AM/PM, mientras
   que Node anterior usa U+0020. React detecta la diferencia y warning.
   **Fix:** nuevo helper `lib/format/time.ts` con `formatLocalTime` /
   `formatLocalRange` / `formatLocalDate` que normaliza el output reemplazando
   U+202F y U+00A0 con espacio regular. Reemplazadas todas las callsites.
2. **Warning "unique key prop" en `OuterLayoutRouter`** que se disparaba en
   TODAS las paginas. Causa: `NextIntlClientProvider` como provider mas
   externo en `[locale]/layout.tsx` interactuaba mal con la reconciliacion
   de Next 16 + React 19. **Fix:** reorden de providers, `NextIntlClientProvider`
   ahora es el mas interno (envuelve a `{children}` + `LuminaToastViewport`).
3. **Sentry "ACTION REQUIRED" warning:** agregado el hook
   `onRouterTransitionStart` en `instrumentation-client.ts` (export requerido
   por Sentry SDK desde Next 15 para instrumentar navegaciones).
4. **AgendaView locale duplicado** (visto en sesion A): `usePathname` venia
   de `next/navigation`. Cambiado a `@/i18n/navigation`. (ya estaba aplicado)
5. **publicEvent no enviaba bearer** (visto en sesion A): cambio minimo a
   forwardear si existe. Da contexto opcional al backend + canal de tests.

Suite final: **57 E2E + 118 vitest = 175 tests verde, sin hydration warnings,
sin warnings de React.**

**Sin commits aun.** Decision pendiente del usuario.

---

### Pase C: Perf tuning navegacion + UX live module (2026-05-09 final)

**Perf tuning aplicado (commit `316e099` en eventos-web):**
- `React.cache()` en `getCurrentUser` + `fetchPublicEvent` para deduplicar
  llamadas SSR duplicadas (layout + page hacian /auth/me y /events/by-slug
  cada uno por separado en cada nav). 50% menos roundtrips a Laragon.
- `experimental.staleTimes`: dynamic 0 → 300s (5min), static → 30min.
  Volver a un modulo dentro de 5min es instant. **Parche temporal** hasta
  que W.11 sockets desbloquee invalidacion push (paridad mobile).
- `loading.tsx` generico (app)/ + por modulo: agenda, home, speakers.
  Skeletons que mimickean el shape real (day strip, chip filters,
  feat-cards, sp-grid, split poster + happening col) para evitar
  layout shift entre skeleton y contenido final.

**Bug UX fixed (mismo commit):**
- Boton "Ver" / "Unirme" en RoomCard del Home LiveState no tenia
  `onClick`. Ahora `has_stream=true` → `/session-stream/{id}`, sin stream
  → `/agenda?highlight={id}`. Toda la card es clickeable + el boton
  stopPropaga. UX consistente con W.5/W.3.

**Diseno UX exploratorio modulo `/live` (commit `ebf5da4` en APP EVENTOS):**
5 demos HTML iterativos en `design/live-pill-demo/` hasta llegar a v5
(aprobada). Concepto cerrado:

- El pill "En vivo" del sidebar **navega a `/es/live`** (modulo nuevo)
- `/es/live` = **Live Hub estilo Twitch / YouTube Live**: full canvas con
  hero featured + side cards (live) + grid upcoming, todo con scroll
  horizontal por seccion para fitear sin scroll vertical
- Home LiveState se mantiene tal cual (preserva branding del cliente
  durante el evento). NO hay redundancia conceptual: Home = identidad,
  /live = consumo focused
- Card pattern: poster con gradient placeholder + LIVE/countdown badge +
  audience count + featured tag para `is_featured`. Click → stream module
- Upcoming cards: countdown amber (rojo si <10min) + watermark
  "PROXIMAMENTE" + fav badge si esta en agenda del usuario
- 0 live + 0 upcoming → empty state amigable
- 0 live + N upcoming → titulo cambia a "Por arrancar", sin badge rojo

**Plan tuning push-based agendado a W.12 polish Fase 3.3:**
- Migracion de fetchers SSR a TanStack Query con `staleTime: Infinity`
- Socket emite eventos por modulo (`agenda:updated`, `speakers:updated`,
  etc.) → cliente invalida queries puntuales
- Nav esperada: <100ms siempre. Skeleton solo en very-first-load
- Bloqueado por W.11 sockets RT

**Commits del pase C (no pusheados):**
- `eventos-web 316e099` — perf+fix: tuning navegacion webapp
- `APP EVENTOS ebf5da4` — design+docs: live pill propuesta UX + plan tuning

---

## Proxima sesion

### Tarea principal sugerida: implementar `/es/live` (Live Hub)

Demo aprobado en `design/live-pill-demo/v5-hub-compact.html`. Implementacion
estimada ~2-3h:

1. Crear modulo `app/[locale]/(app)/live/page.tsx` con SSR fetch de
   `fetchHappeningNow(eventId)` + un nuevo `fetchUpNext(eventId)` o
   derivar de `fetchAgenda` filtrando por `start > serverTime`
2. Componente `LiveHubView.tsx` con grid `auto / 1fr / 1fr` (header +
   live row + upcoming row), scroll horizontal por seccion
3. `LiveCard.tsx` (con variantes `featured` / `bg-1/2/3`) +
   `UpcomingCard.tsx` (con countdown badge + fav badge)
4. Wirear pill "En vivo" del sidebar a `/es/live`. State del pill:
   0 live + 0 upcoming → disabled; 1 live → nav directa al stream
   con tooltip; N live → nav a /live; 0 live + N upcoming → pill amber
5. Endpoint `fetchUpNext` o doc en BACKEND-API-MAP que `/happening-now`
   incluya tambien `next_sessions: [...]` (cross-team con backend)
6. Skeleton `loading.tsx` matcheando el shape del hub

### Opciones alternativas para retomar

1. **Commit + push** del trabajo de hoy (3 commits sin pushear)
2. **Bug fix:** hydration mismatch en `formatTime` — probablemente
   normalizar U+202F via wrapper o forzar formato explicito
3. **Tests E2E avanzados:** W.4 paneles socket (requiere socket.io
   stub) + component happy-DOM tests
4. **Nuevo modulo W.x:** networking / social / sponsors segun roadmap
5. **Atacar pendientes paralelos:** featured/keynote como flags reales,
   mobile parity highlight a Expo, errores 82-91, analytics tracking

**Para arrancar diga:** "siguiente" o el modulo concreto a atacar.

### Decisiones cerradas hoy (pase C, no preguntar de nuevo)

- **`/live` se mantiene como modulo separado** (no se fusiona con Home).
  Razon: Home preserva branding del cliente durante live; /live es
  consumo focused. No hay conflicto conceptual.
- **Live Hub layout:** full canvas con scroll horizontal por seccion
  (live row + upcoming row), no popover. Demo v5 aprobado.
- **Pill "En vivo" comportamiento:** 0 live + 0 next → disabled;
  1 live + 0 next → nav directa al stream con tooltip; N live → nav
  a /live; 0 live + N next → pill amber con badge contador.
- **TanStack Query con socket invalidation es el modelo objetivo**
  pero requiere W.11. Mientras tanto, `staleTimes` 5min/30min como parche.
- **Featured con `is_featured`** del backend para hero del Live Hub.
  Backend ya expone el flag en HappeningSession.

### Decisiones cerradas previas (no preguntar de nuevo)

- E2E corre en puerto 3100 (separado del dev del usuario en 3000)
- mockBackend en 8101 reemplaza Laragon durante tests
- Bearer-tag pattern: cookie con valor "fake-bearer-event-pre", "...-event-ended",
  "...-recap-not-eligible", "...-recap-disabled" para variar status sin mocks separados
- Visibility checks en elementos con CSS opacity transitions: usar
  `toHaveClass(/\bopen\b/)` en lugar de `toBeVisible` (Playwright ignora opacity)
- `getByRole("alert")` ambiguo en dev mode (overlay hydration tambien usa alert)
  → filtrar por `hasText` cuando se asserta toasts especificos
- Counter pattern para `page.route` cuando una llamada cliente-side debe diferir
  entre 1ra (initial state) y 2da+ (post-action state)
- `useSessionLiveConfig` puede correr sin socket — los tests E2E lo aprovechan
  para cubrir el shell sin levantar socket.io server

---

## Pendientes paralelos

**Tests pendientes (out of scope hoy):**
- W.4 paneles interactivos (chat/Q&A/polls) — requieren socket.io server stub
- Component happy-DOM tests para AgendaView highlight initializer + SpeakersView preopen

**Bugs detectados (todos cerrados 2026-05-09):**
- ~~Hydration mismatch formatTime/formatRange~~ → fixed via `lib/format/time.ts`
- ~~Warning key prop en OuterLayoutRouter~~ → fixed reordenando providers
- ~~Sentry onRouterTransitionStart missing~~ → fixed via instrumentation-client

**Backend (cross-team):**
- Featured/keynote como flags reales en DB
- Avg rating threshold ≥3 en lista
- Endpoint `/speakers/{id}/sessions/preview`

**Mobile parity:**
- Portar "click sesion → agenda highlight" al Expo

**Tracking analytics:**
- Eventos `speakers.list_viewed`, `speakers.detail_opened`, `speakers.rated`,
  `speakers.session_clicked`, `speakers.linkedin_clicked`

**Design backlog:**
- Errores 82-91 en `design/ERRORES/` aun sin revisar

---

## Convenciones / contexto operativo

- **Working dir principal:** `C:\laragon\www\APP EVENTOS` (este repo, docs+design)
- **Webapp Next.js:** `C:\laragon\www\eventos-web`
- **Mobile Expo:** `C:\Users\Kasproduction\Projects\eventos-app`
- **Backend Laravel:** `C:\laragon\www\eventos-backend` (vive en Laragon)
- **Modo de trabajo:** DaVinci — calidad sobre cantidad, cero emojis
- **E2E:** `pnpm test:e2e` levanta auto mockBackend (8101) + dev (3100). Reusa
  servers entre runs en local. Para reload de fixtures, killear puerto 8101
  con `Stop-Process`.
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
