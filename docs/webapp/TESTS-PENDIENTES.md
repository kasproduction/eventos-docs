# Tests pendientes — webapp

> Inventario de cobertura actual + plan exhaustivo de tests faltantes (vitest + Playwright E2E).
> Documento generado 2026-05-08 al cerrar W.5 Speakers.
> **Proximo arranque:** ejecutar este plan modulo por modulo en orden de criticidad.

---

## Estado actual

> **Update 2026-05-09:** Suite completa de retroactivos cerrada en una sesion.
> 57 E2E + 118 vitest = **175 tests verde**. Mock backend en
> `e2e/_helpers/mockBackend.mjs` cubre los modulos del shell post-login + el
> hack de bearer-tag para variar event.status sin levantar mocks alternativos.

### Vitest unit tests — 9 archivos, **118 tests**

| Archivo | Cubre |
|---|---|
| `tests/components/agenda/agendaDerive.test.ts` | buildDayStrip, firstDayWithSessions, deriveUiState, trackSlug, formatTime, collectTracks, totalSessions |
| `tests/components/speakers/speakersDerive.test.ts` | filterSpeakers, getFeatured, hasKeynoteSession, sortAlphabetical |
| `tests/lib/api.test.ts` | apiFetch helper (errores, retries, headers) |
| `tests/lib/authValidators.test.ts` | emailSchema, magicLinkRequestSchema, verifyMagicLinkSchema, passwordLoginSchema |
| `tests/lib/mailcheck.test.ts` | suggestEmail (typo detection gmail.con → gmail.com) |
| `tests/lib/streaming/detectSource.test.ts` | detectStreamSource (iframe vs HLS) |
| `tests/hooks/streaming/useChat.dedup.test.ts` | useChat dedup + useQnA upvote logic |
| `tests/lib/speakersClient.test.ts` | rateSpeakerRequest 200/409/422/fallback + fetchMySpeakerRatingsClient (7 tests, 2026-05-09) |
| `tests/lib/agendaClient.test.ts` | toggleFavoriteRequest, rateSessionRequest, fetchMyRatings (8 tests, 2026-05-09) |

### Playwright E2E — 8 archivos, **57 tests**

| Archivo | Tests |
|---|---|
| `e2e/auth-gate.spec.ts` | redirects sin cookie + login publica (4 tests) |
| `e2e/login-form.spec.ts` | step email/sent/password + typo detection (4 tests) |
| `e2e/verify-page.spec.ts` | token validacion + errores 401/410 (4 tests) |
| `e2e/home.spec.ts` | 3 estados (pre/live/ended) + 3 variantes de recap (6 tests, 2026-05-09) |
| `e2e/agenda.spec.ts` | days strip, tabs, chips, search, favoritos optimistic, rating, highlight pulse (16 tests, 2026-05-09) |
| `e2e/streaming.spec.ts` | live/replay/empty player, about, rating, mobile shell (9 tests, 2026-05-09) |
| `e2e/speakers.spec.ts` | SSR auth, search, panel, rating 200/409/500, deep link, Esc orden (14 tests, 2026-05-09) |

**Patron clave Playwright:** SSR pega contra mockBackend Node (`e2e/_helpers/mockBackend.mjs`, puerto 8101) que sirve fixtures de `e2e/_fixtures/data.mjs`. Mutaciones browser-side se mockean con `page.route('**/api/...')` para variar status. Sin Laragon ni socket.io server.

---

## Cobertura por modulo

| Modulo | Estado | Unit | E2E |
|---|---|---|---|
| **W.1 Auth** | ✅ Implementado y testeado | authValidators, mailcheck | 12 tests (gate, login-form, verify) |
| **W.2 Home** | ✅ Implementado y testeado | — | 6 tests (pre/live/ended + recap variants) |
| **W.3 Agenda** | ✅ Implementado y testeado | agendaDerive, agendaClient | 16 tests (days, chips, fav, rate, highlight) |
| **W.4 Streaming** | ✅ Implementado y testeado | detectSource, useChat, useQnA | 9 tests (player states, about, rate) |
| **W.5 Speakers** | ✅ Implementado y testeado | speakersDerive, speakersClient | 14 tests (search, panel, rate, deep link) |

**Limitaciones conocidas (no bloqueantes):**
- W.4 paneles interactivos (chat send, Q&A upvote, poll vote) requieren socket.io
  server real → cubiertos por vitest unit (useChat dedup) + se tocara con mock
  socket en sesion futura.
- W.4 hidden bug: hydration mismatch en `SpeakerDetailPanel.tsx:170` y en
  `streaming` por `toLocaleTimeString("es-CO")` Node vs browser. Atacar separado.

---

## Plan exhaustivo de tests faltantes

### Orden recomendado

1. **W.5 Speakers** (mas reciente, mejor en cabeza) — ~45 min
2. **W.3 Agenda** (mas complejo, mas critico) — ~60 min
3. **W.4 Streaming** (real-time, mas casos edge) — ~45 min
4. **W.2 Home** (mas simple, varios estados) — ~30 min

**Total estimado:** ~3-4h E2E + ~1-2h vitest extras = **~5-6h trabajo concentrado**.

---

### 1. W.5 Speakers E2E — `e2e/speakers.spec.ts` ✅ COMPLETADO 2026-05-09

> Las secciones 2-4 abajo (W.3 / W.4 / W.2) tambien quedaron cerradas en la
> misma sesion del 2026-05-09. El plan original se mantiene como historial
> del scope que se cubrio.

**Setup:** mock auth cookie + mock fetches `**/api/speakers/...`, `**/events/{id}/speakers`, `**/events/{id}/my-speaker-ratings`.

**Tests (~12):**

- [ ] Render SSR sin auth → redirige `/login`
- [ ] Render SSR con auth → lista carga con `Destacados` + `Todos`
- [ ] Search ⌘K abre overlay con animacion pop-in
- [ ] Search escribir "Garcia" debounce 400ms → filtra + oculta destacados + muestra "Resultados"
- [ ] Search clear (X) → restaura lista completa
- [ ] Click sp-row → panel side desde derecha (verificar `.detail.open`)
- [ ] Foto fallback ui-avatars cuando `photo_url === null`
- [ ] Stars + avg + n votos visible solo si `rating_count > 0`
- [ ] LinkedIn condicional: con `linkedin_url` → 2 botones flex:1; sin → solo Calificar max-220 alineado derecha
- [ ] LinkedIn click → `target="_blank"` y `rel="noopener noreferrer"` validados
- [ ] Click "Calificar" → modal abre, focus en close button (50ms)
- [ ] Modal Tab loop: focus close → estrellas → comment → submit → skip → cierra
- [ ] Modal hover star 4 → label "Bueno" visible (aria-live)
- [ ] Submit rating mock 200 → optimistic UI: boton "Evaluado" inmediato, toast "Gracias por evaluar al speaker"
- [ ] Submit rating mock 409 → re-fetch myRatings, toast NO error (silencioso)
- [ ] Submit rating mock 500 → revert optimistic, toast error
- [ ] Click sesion en panel → navega `/agenda?highlight=X` (URL change verificado)
- [ ] Deep link `/speakers?id=5` carga con panel speaker 5 abierto + URL mantiene query
- [ ] `Esc` cierra capas en orden: modal → search → panel
- [ ] ArrowDown/ArrowUp con panel abierto navega filtered list
- [ ] Mobile webapp viewport ≤640px → panel detail full-screen overlay

### 2. W.3 Agenda E2E — `e2e/agenda.spec.ts`

**Setup:** mock auth + mock `**/api/agenda/...` con 2 dias y 5 sesiones.

**Tests (~15):**

- [ ] Render SSR carga days strip + tabs `Agenda (N)` y `Mi Agenda (M)`
- [ ] Click day pill → slide-out -30px / 180ms + nuevo dia slide-in +40px / 420ms
- [ ] Click track chip → filtra sesiones, multi-select acumulativo
- [ ] Click chip activo → desactiva
- [ ] ChipFilters "Todos" → limpia selectedTracks
- [ ] Tab "Mi Agenda" muestra solo `is_favorite: true`
- [ ] Search ⌘K abre overlay con animacion pop-in (compartido con speakers)
- [ ] Search "panel" → filtra por title/description/track/speaker
- [ ] Click sess-card → DetailPanel abre desde derecha
- [ ] DetailPanel swap: click otra session → swap-out -60px + swap-in
- [ ] Toggle favorite optimistic → heart fill + count++ inmediato
- [ ] Toggle favorite mock POST 500 → revert + toast error
- [ ] Click "Calificar" en card → RatingModal abre
- [ ] Submit rating session → toast "Gracias por tu evaluacion"
- [ ] Click "Todas" (tab Mi Agenda) → toast lumina con count
- [ ] Estado live → badge pulse animation (`pulse-live` keyframe)
- [ ] Estado past → muted styling (`.sess-card.past`)
- [ ] Estado scheduled futuro → no badge live
- [ ] Highlight via `?highlight=X` (deep link desde W.5):
  - Salta al dia correcto
  - Espera 800ms
  - scrollIntoView card
  - Aplica `.highlighted` class
  - Despues de 1.7s limpia clase + URL
  - Verifica que el ripple (`::after`) y pulse glow ocurren
- [ ] Highlight con id no existente → ignora silenciosamente, no error
- [ ] Highlight warm nav (AgendaView ya montado) → `lastHighlightId` permite re-fire
- [ ] Empty state "Sin favoritas este dia" cuando tab=mine y count=0
- [ ] Empty state "Sin sesiones" cuando day vacio

### 3. W.4 Streaming E2E — `e2e/streaming.spec.ts`

**Setup:** mock auth + mock `**/api/streaming/...` + socket events stub.

**Tests (~10):**

- [ ] Estado pre-stream (no `start_at` proximo) → muestra countdown
- [ ] Estado live → iframe/HLS player + sidebar interactivo
- [ ] Estado ended sin `recording_url` → muestra "Sesion terminada"
- [ ] Estado ended con `recording_url` → reproduce replay
- [ ] Stream iframe (Twitch/YouTube embed) detection via `detectStreamSource`
- [ ] Stream HLS m3u8 detection
- [ ] Sidebar tabs Chat / Q&A / Poll
- [ ] Chat: send mensaje optimistic → aparece en lista inmediato
- [ ] Chat: dedup logic — mensaje socket con mismo client_id NO duplica
- [ ] Q&A: submit pregunta → aparece en lista
- [ ] Q&A: upvote toggle optimistic
- [ ] Poll: live_poll_questions render + vote
- [ ] Mobile webapp ≤640px → sidebar bottom-sheet en lugar de side
- [ ] Click "Volver a agenda" → `router.push('/agenda')`
- [ ] Estado interactive_mode='none' → sidebar oculto

### 4. W.2 Home E2E — `e2e/home.spec.ts`

**Setup:** mock auth + mock `**/api/home/...` con cliente diferentes estados de evento.

**Tests (~7):**

- [ ] Estado `pre_event` → countdown timer + CTA "Mira la agenda"
- [ ] Estado `published` → info evento + CTA "Confirmar asistencia"
- [ ] Estado `live` → CTA "Entrar al stream" + dot pulse
- [ ] Estado `ended` → CTA "Ver tu recap" + agradecimiento
- [ ] Mute estado-aware: pre/ended muted, live unmuted by default
- [ ] Wordmark renderiza con accent del cliente (var(--accent))
- [ ] Click CTA navega a la ruta correcta segun estado
- [ ] Hero keyvisual del cliente, NO tipografia CSS gigante (regla feedback)

---

## Tests vitest adicionales

### `tests/lib/speakers.test.ts` — fetchers SSR (~5 tests)

- [ ] `fetchSpeakers(eventId)` retorna `[]` sin auth (no throw)
- [ ] `fetchSpeakers` retorna `data` cuando backend OK
- [ ] `fetchSpeakers` retorna `[]` en `ApiError` (graceful degradation)
- [ ] `fetchMySpeakerRatings` retorna `{}` sin auth
- [ ] `fetchMySpeakerRatings` parsea map `{ "5": 4 }` → `{ 5: 4 }` (string key → number)

### `tests/lib/speakersClient.test.ts` — client mutations (~5 tests)

- [ ] `rateSpeakerRequest` POST con body correcto + headers credentials
- [ ] `rateSpeakerRequest` lanza `SpeakersClientError` con `status === 409` en UNIQUE conflict
- [ ] `rateSpeakerRequest` lanza error en 422
- [ ] `fetchMySpeakerRatingsClient` GET con credentials include
- [ ] Helpers privados `postJson/getJson` manejan body parse error

### `tests/lib/agendaClient.test.ts` — client mutations (~6 tests)

- [ ] `toggleFavoriteRequest` POST con body + retorna `{ is_favorite }`
- [ ] `rateSessionRequest` POST con `{ rating, comment }`
- [ ] `fetchMyRatings` GET retorna map vacio si no califico
- [ ] `downloadSessionIcs` dispara descarga blob
- [ ] Errores 401/422/500 lanzan `AgendaClientError` con `status` correcto
- [ ] Mensajes de error parsean `{ message }` del payload

### `tests/components/speakers/SpeakersView.test.tsx` — happy DOM render (~4 tests)

- [ ] Renderiza header + sidebar + canvas con shape correcta
- [ ] Initializer `useState` con `?id=X` → panel preopen
- [ ] Featured solo aparece sin search
- [ ] Empty list cuando speakers=[]

### `tests/components/agenda/AgendaView.highlight.test.tsx` — happy DOM (~4 tests)

- [ ] Initializer `selectedDay` prioriza dia con highlight session
- [ ] `lastHighlightId` ref evita re-fire mismo id
- [ ] setTimeout chain switch dia + scroll + clear pulse + clean URL
- [ ] reduced-motion desactiva animacion pulse

---

## Convenciones a seguir

### Setup auth en E2E

Las specs de auth ya existentes mockean `**/api/auth/**`. Para modulos post-login necesitamos **simular cookie httpOnly `eventos_auth`**. Opcion mas limpia:

```ts
test.beforeEach(async ({ context }) => {
  await context.addCookies([
    {
      name: "eventos_auth",
      value: "fake-bearer-token",
      domain: "localhost",
      path: "/",
      httpOnly: true,
    },
  ]);
});
```

Y mock del endpoint que `getCurrentUser()` usa para validar el bearer (probablemente `/api/auth/me` — verificar):

```ts
await page.route("**/api/auth/me", async (route) => {
  await route.fulfill({
    status: 200,
    contentType: "application/json",
    body: JSON.stringify({
      id: 1,
      name: "Test User",
      email: "test@kasproduction.com",
      eventId: 1,
    }),
  });
});
```

### Setup mocks por modulo

Idealmente extraer un helper compartido `e2e/_helpers/mockAgenda.ts`, `mockSpeakers.ts` con fixtures realistas que ambos vitest y E2E reusen.

### Fixtures realistas

Cada modulo necesita fixture estable:
- `e2e/_fixtures/event.ts` — PublicEvent base
- `e2e/_fixtures/speakers.ts` — 6 speakers (3 con keynote, 2 sin sesiones, 1 con ratings)
- `e2e/_fixtures/agenda.ts` — 2 dias, 5 sesiones por dia, varios tracks/tipos/estados
- `e2e/_fixtures/streaming.ts` — 1 sesion live + 1 ended con recording

### Matchers preferidos

```ts
// Bueno: estable a cambios de markup
await expect(page.getByRole("heading", { name: "Speakers" })).toBeVisible();
await expect(page.getByRole("button", { name: /Calificar/i })).toBeEnabled();

// Evitar: depende de classNames internos
await expect(page.locator(".sp-row").first()).toBeVisible();
```

Excepcion: cuando se necesita verificar animaciones / estados visuales (`.detail.open`, `.highlighted`) el class selector es legitimo.

### Naming convention

- E2E: `e2e/{modulo}.spec.ts` (ya establecido)
- Component vitest: `tests/components/{modulo}/{Componente}.test.tsx`
- Lib vitest: `tests/lib/{archivo}.test.ts`

### Ejecutar

```bash
pnpm test                    # vitest run (rapido)
pnpm test:watch              # vitest dev
pnpm test:e2e                # playwright headless
pnpm test:e2e:ui             # playwright UI mode (debug)
```

---

## Definition of done — modulo completo

Un modulo se considera "test-cubierto" cuando tiene:

1. **Unit tests** de toda funcion pura/derivacion (>80% line coverage de los `*Derive.ts`/`*Client.ts`)
2. **E2E happy path** (1 test que recorre el flujo principal end-to-end)
3. **E2E edge cases** (5+ tests de casos: empty, error, race, deep link, keyboard a11y)
4. **Mocks reusables** en `e2e/_helpers/`
5. **Run en CI verde** (forbidOnly:true, no flaky tests)

---

## Decision pendiente

¿Hacer todo en **una sola sesion larga** (~6h, riesgo fatiga) o **una sesion por modulo** (W.5 hoy, W.3 manana, etc.)?

**Recomendacion DaVinci:** una por sesion. Permite revisar cada modulo con calma, mas energia, mejor calidad. Si falla algun test, hay tiempo para diagnosticar.

**Orden propuesto cuando se retome:**

1. ✅ **W.5 Speakers E2E** (14 tests, 2026-05-09) — completado
2. ✅ **W.3 Agenda E2E** (16 tests, 2026-05-09) — completado
3. ✅ **W.4 Streaming E2E** (9 tests, 2026-05-09) — completado (sin paneles socket)
4. ✅ **W.2 Home E2E** (6 tests, 2026-05-09) — completado
5. ✅ **vitest gaps speakersClient + agendaClient** (15 tests, 2026-05-09) — completado

---

## Infraestructura E2E (instalada 2026-05-09)

**`e2e/_helpers/mockBackend.mjs`** — servidor Node minimal (puerto 8101) que
simula Laravel para SSR. El dev de E2E (puerto 3100) recibe `API_INTERNAL_URL`
apuntando ahi en lugar de `eventos-backend.test`. Esto permite tests
deterministicos sin tocar Laragon ni mutar datos reales.

**`e2e/_fixtures/data.mjs`** — fixtures compartidas (event, user, 6 speakers,
myRatings). Importadas tanto por mockBackend como por specs.

**`e2e/_helpers/mockAuth.ts`** — helper `setAuthCookie(context, token?)` para
inyectar la cookie httpOnly `eventos_auth` en tests post-login.

**Patron para tests futuros (W.3, W.4, W.2):**

```ts
import { setAuthCookie } from "./_helpers/mockAuth";

test.beforeEach(async ({ context }) => {
  await setAuthCookie(context);
});

test("...", async ({ page }) => {
  // SSR usa fixtures del mockBackend (auto)
  // Mutations browser-side: page.route("**/api/.../...")
});
```

**Endpoints del mockBackend ya cubiertos:**
- `GET /api/v1/auth/me` (401 sin Authorization)
- `GET /api/v1/events/by-slug/{slug}`
- `GET /api/v1/events/{id}/speakers`
- `GET /api/v1/events/{id}/my-speaker-ratings`
- `POST /api/v1/events/{id}/speakers/{spkid}/rate`
- `GET /api/v1/events/{id}/agenda` (stub vacio para no error 404 cuando navegas)

**Para W.3 Agenda extender mockBackend:**
- `GET /api/v1/events/{id}/agenda` con dias + sesiones reales
- `GET /api/v1/events/{id}/my-ratings`
- `POST /api/v1/events/{id}/sessions/{sid}/favorite`
- `POST /api/v1/events/{id}/sessions/{sid}/rate`

**Bug detectado, NO bloqueante:** hydration mismatch en
`SpeakerDetailPanel.tsx:170` por `formatTime()` con diferencia entre Node y
browser en `toLocaleTimeString("es-CO")` (probable narrow non-breaking space
vs space). Documentar y atacar separado.
