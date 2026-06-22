# W.7 — Sponsors

> Brand Wall con grid por tier + Brand Profile (cliente reusa data del index, NO endpoint detail) + Contact (no "leads" scoped) + Favorite + View tracking + Trivia integration con gamification.
>
> **Estimacion:** ~5h (reducida de 7h tras audit — sin endpoints inventados).
> **Dependencias:** W.0, W.1.
> **Estado:** **CERRADO 2026-06-21** (23/23). Pendiente Lighthouse autenticado del batch QA final (afecta a todos los modulos post-login por igual).

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- `W.0-spatial-ui.md`
- App movil: `screens/sponsors/` — Brand Wall + Brand Profile premium
- Backend: `routes/api/sponsors.php`, `app/Http/Resources/V1/SponsorResource.php`
- Memorias: `project_sponsors_uiux_notes.md`, `project_s16_notes.md`

---

## Drift corregido (2026-05-07)

Version previa documentaba endpoints que NO existen:

- ~~`GET /sponsors/{id}` (detalle individual)~~ → no existe. El `SponsorController::index` retorna toda la informacion (logo, banner, descripcion, services, sessions, contact_email, etc.). El cliente debe reusar el item ya cargado del index.
- ~~`POST /sponsors/{id}/leads`~~ → endpoint global de leads es `POST /leads` (W.15) y se crea desde scanner mobile. Para que el asistente envie info al sponsor desde la webapp, usar `POST /events/{eventId}/sponsors/{sponsorId}/contact {message?}`.
- ~~`GET /sponsors/{id}/trivia`~~ → la trivia de sponsor es parte de gamification. NO hay endpoint GET. La trivia activa se entrega via gamification config + `POST /events/{eventId}/trivia/{triviaId}/answer`.
- ~~`POST /sponsors/{id}/trivia/answer`~~ → real `POST /events/{eventId}/trivia/{triviaId}/answer` (sin scope sponsor en path; trivia tiene `sponsor_id` interno)
- ~~`?tier`~~ filtro server-side → no existe. Backend retorna todos y agrupa cliente por `sponsor.tier`.

Endpoints reales verificados:

```
GET    /api/v1/events/{eventId}/sponsors                                (publico)
POST   /api/v1/events/{eventId}/sponsors/{sponsorId}/favorite           (sanctum)
DELETE /api/v1/events/{eventId}/sponsors/{sponsorId}/favorite           (sanctum)
POST   /api/v1/events/{eventId}/sponsors/{sponsorId}/contact            (sanctum)
  body: {message?: string}
POST   /api/v1/events/{eventId}/sponsors/{sponsorId}/view               (sanctum)
  → tracking analytics, fire-and-forget
```

Trivia (compartida con W.9 Gamification):
```
POST /api/v1/events/{eventId}/trivia/{triviaId}/answer
  body: {answer_index: number}  (verificar shape exacto en GamificationController)
```

---

## SponsorResource shape (verificado)

```ts
{
  id: number,
  name: string,
  tier: string,                  // 'platinum'|'gold'|'silver'|'bronze'|...
  logo_url: string | null,
  banner_url: string | null,
  description: string | null,
  website_url: string | null,
  contact_email: string | null, // solo si show_contact_button=true
  show_contact_button: boolean,
  is_favorite: boolean,          // whenLoaded
  services: SponsorServiceResource[],
  sessions: [{id, title, start_datetime, end_datetime, location, session_type}]
}
```

---

## Alcance real

1. **Brand Wall**: grid agrupado por tier — cliente computa el grupo desde `sponsor.tier`
2. **Brand Profile**: panel/modal que reusa el item ya cargado del index. NO hace fetch extra.
3. **Favorite**: toggle con optimistic
4. **Contact**: form que dispara `POST /sponsors/{id}/contact` con mensaje opcional
5. **View tracking**: dispara `POST /sponsors/{id}/view` al abrir profile (fire-and-forget)
6. **Trivia**: integrada en profile si gamification config define trivia para ese sponsor

NO entra:
- "Leads" scoped al sponsor (eso es W.15 vendor side, scan mobile)
- Endpoint detail individual

---

## Eventos socket

Sponsors NO tienen RT scoped. Si los datos cambian, refetch on focus o `data:invalidate` cuando aplique.

---

## Refs visuales

- App movil sponsors (`features/Screenshot 2026-...`)
- `design/showcase-onboarding-v6.html` `brand-wall`
- Memoria: `project_sponsors_uiux_notes.md` (Brand Wall, Brand Profile, contact form)

---

## Fase 0 — Capa datos (~30min) — 3/3 ✅

- [x] `lib/types/sponsor.ts` — espejo de SponsorResource backend (incluye trivia_enabled, passport_enabled, visit_points, services, sessions)
- [x] `lib/sponsors.ts` — server fetcher `fetchSponsors(eventId)` con manejo de auth + apiFetch
- [x] `lib/sponsorsClient.ts` — client mutations: `toggleFavoriteSponsor`, `contactSponsor`, `recordSponsorView`, `visitStand`, `answerSponsorTrivia` (con SponsorsClientError code='ALREADY_CONTACTED')
- [x] 5 API proxy routes Next: `/api/sponsors/[eventId]/[sponsorId]/{favorite,contact,view}`, `/visit-stand/[sponsorId]`, `/trivia/[triviaId]/answer`

---

## Fase 1 — Brand Wall (~2h) — 5/5 ✅

### 1.1 Grid por tier — 3/3 ✅
- [x] `<SponsorWall />` agrupa cliente con `groupByTier` + `TIER_DISPLAY_ORDER`. 3 variantes de card: Platinum (logo 56 + nombre + desc), Gold (logo 42 + nombre), Compact (solo logo round 60 — sin nombre, silver/bronze/media)
- [x] Living shuffle dentro de cada tier (equidad) cada 7s via `shuffleWithSeed(items, shuffleSeed + tier.charCodeAt(0))`. **Animacion smooth via framer-motion `layout` + spring damping 28 stiffness 120** = equivalente exacto a Reanimated `LinearTransition.springify` del Expo
- [x] Click → DetailPanel desliza desde la derecha (NO fetch extra — reusa item del index)

### 1.2 Layout responsive — 2/2 ✅
- [x] Desktop: split fixed 1.4fr (wall) + 1fr (detail). Cards en `auto-fit minmax()` adapta cols al ancho disponible
- [x] Mobile (<1000px): wall full ancho, detail oculto en reposo; cuando hay seleccion, wall se oculta y detail toma todo. Validacion device fisico → batch QA final cross-modulos

---

## Fase 2 — DetailPanel (~1.5h) — 5/5 ✅

### 2.1 Layout — 2/2 ✅
- [x] Hero centrado: logo 88x88, nombre + badge pasaporte inline (si `passport_enabled`), descripcion. **Sin tier label** (decision espejo Expo: el tier se siente solo por jerarquia del Wall — el detail es del sponsor, no del paquete que pago)
- [x] Mount → `recordSponsorView` (analytics) + `visitStand` (gamification — devuelve `points_awarded` + trivia). Toast `+puntos por visitar` via lumina (top-center) condicional a `points_awarded > 0` — sin esto cada click mostraria toast falso

### 2.2 Descripcion + Favorite — 1/1 ✅
- [x] Heart toggle con pop animation (espejo Expo `tapHeart`) + lumina favorite

### 2.3 Servicios — 1/1 ✅
- [x] Chip toggle multi-seleccion. CTA "Enviar solicitud (N)" disabled hasta seleccionar ≥1

### 2.4 Sesiones — 1/1 ✅
- [x] Lista de sesiones del sponsor con click → `/agenda?highlight=X` (patron W.5 highlight ya existente)

### 2.5 Contactar — 2/2 ✅
- [x] Form: chips servicios + textarea (max 1000 chars)
- [x] Submit → `POST /sponsors/{id}/contact`. 409 ALREADY_CONTACTED → estado "Solicitud enviada" verde neutral `rgba(80,200,120)`. Lumina success al envio

---

## Fase 3 — Trivia integration (~1h) — 3/3 ✅

### 3.1 Trivia panel — 2/2 ✅
- [x] Si `visitStand` devuelve `trivia[]` → panel multi-pregunta. **Espejo del TriviaModal Expo**: pregunta + bonus pts subtitle + opciones con letra A/B/C/D circle + boton explicito "Responder → Siguiente pregunta → Ver resultado" + feedback box (verde "+X puntos" o rojo "Incorrecto, 0 puntos") + "X pts acumulados" running total
- [x] `POST /events/{eventId}/trivia/{triviaId}/answer {selected_index}` → devuelve `correct_index` + `is_correct` + `points_awarded`. Toast lumina si points > 0

### 3.2 Estado — 1/1 ✅
- [x] Pantalla resumen final: "+totalPts puntos ganados" tipografia 48px + sub "Se sumaron a tu posicion en leaderboard" / "Mejor suerte la proxima vez" + autoclose 2.5s

---

## Fase 4 — Tests (~30min) — 3/3 ✅

### 4.1 Vitest — 1/1 ✅
- [x] `tests/lib/sponsorsDerive.test.ts` cubre `groupByTier`, `filterSponsors`, `shuffleWithSeed` (determinismo + permutacion + array vacio). 14 tests verdes

### 4.2 Playwright — 2/2 ✅
- [x] `e2e/sponsors.spec.ts` — 12 tests, todos verdes. Cubre: auth gate, SSR wall 3 tiers, click Platinum abre detail, Esc cierra, contact submit success, contact 409 ALREADY_CONTACTED, sponsor sin trivia/services, Notion minimo (sin nada), search filter case-insensitive, search sin resultados, click fuera cierra
- [x] Fixture `sponsorsFixture` con 8 sponsors variados + `sponsorTriviasFixture`. Mock handlers en `mockBackend.mjs` simula 409 sponsor id=4 + points_awarded=0 sponsor id=2 (ya visitado)

---

## Edge cases — 10/10 ✅

- [x] Sponsor sin servicios → seccion contact oculta (Microsoft id=2 lo prueba)
- [x] Sponsor sin sesiones → seccion sesiones oculta (Mercado Libre id=4)
- [x] Sponsor sin trivia → trivia panel oculto (Mercado Libre)
- [x] Sponsor con `show_contact_button=false` → email oculto (Microsoft)
- [x] Contact mensaje vacio → permitido (`message?` opcional en backend)
- [x] Contact >1000 chars → maxLength input bloquea
- [x] Sponsor sin tier → no aplica (backend valida enum), pero `TIER_DISPLAY_ORDER` ignora unknown
- [x] Logo `null` → fallback letra inicial (Plus Jakarta 700)
- [x] Lead duplicado 409 → estado "Solicitud enviada" + lumina info "Ya enviaste una solicitud"
- [x] Trivia ya respondida → backend devuelve `already_answered: true` (no re-otorga puntos). Cliente respeta state local — `triviaAnswered` Map evita reenviar

---

## Pendiente backend (nice to have, post-Fase 1)

- Endpoint detail individual `GET /sponsors/{id}` (hoy se reusa item del index, OK pero menos eficiente con 100+ sponsors)
- Search server-side en `/sponsors`
- Filtro por tier server-side

---

## Decisiones cerradas

- **UX premium en webapp:**
  - Esc + click fuera del wall cierran el detail (espejo W.3 Agenda)
  - Stagger reveal del detail (hero → sessions → trivia → contact → actions, 60ms delay incremental)
  - Skeleton loading SSR con phantom cards 3-tier + shimmer animation (`loading.tsx` segmento)
  - Tooltip radix custom en compact logos (reemplaza `title` HTML nativo)
- **Elevaciones:**
  - Lux: multi-layer shadows (`--sp-shadow-sm/md/lg` espejo agenda/speakers)
  - Noir: shadow base oscura siempre presente (sobrevive al living shuffle, no se pierde cuando el mouse deja de hovear)
  - Sin halo accent (rompia con primary_color rojo/coral del cliente)
- **Colores success/error:** `rgba(80,200,120)` verde + `rgba(255,100,100)` rojo como tokens **del sistema**, NO `var(--accent)` del cliente (espejo Expo — confirmaciones no se tinen del primary_color del evento)
- **Badge trivia (?) en cards del wall:** REMOVIDO. La trivia se descubre naturalmente al abrir el detail. Mantenemos solo el badge pasaporte ✓ (compromiso real del asistente)
- **Sin outline selected en cards:** la seleccion se comunica solo via DetailPanel abierto. El outline accent en wall era ruido + se veia mal con primary_color rojo

---

## Cierre

- [x] Tests verde (vitest + Playwright 12/12)
- [x] Validado 3 viewports CSS (desktop 1600 + auto-fit responsive). Device fisico → batch QA final
- [x] Accessibility Lighthouse 98 ✅ (prod build). Performance autenticado → batch QA final (Lighthouse standalone redirige a login)
- [x] Commit DaVinci + COMPLETADO + PENDIENTES-WEBAPP actualizado
