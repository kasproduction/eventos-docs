# W.7 — Sponsors

> Brand Wall con grid por tier + Brand Profile (cliente reusa data del index, NO endpoint detail) + Contact (no "leads" scoped) + Favorite + View tracking + Trivia integration con gamification.
>
> **Estimacion:** ~5h (reducida de 7h tras audit — sin endpoints inventados).
> **Dependencias:** W.0, W.1.
> **Estado:** Pendiente — backend audit completado 2026-05-07.

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

## Fase 0 — Hooks (~30min) — 0/3

- [ ] `useSponsors(eventId)` — fetch index, agrupar por tier en cliente
- [ ] `useToggleFavoriteSponsor()` — POST/DELETE optimistic
- [ ] `useContactSponsor()` — POST contact

---

## Fase 1 — Brand Wall (~2h) — 0/5

### 1.1 Grid por tier — 0/3
- [ ] `<BrandWall />` agrupa cliente: Platinum > Gold > Silver > Bronze (orden + tamano por tier)
- [ ] Cada sponsor card tiene su propio color (no todos dorados — leer feedback memoria)
- [ ] Click → abre `<SponsorProfile sponsor={item} />` (NO fetch extra)

### 1.2 Layout responsive — 0/2
- [ ] Mobile: lista vertical agrupada por tier
- [ ] Tablet: 2-3 cols Platinum, 4 cols Gold, 6 cols Silver/Bronze
- [ ] Desktop: 4 cols Platinum, 6 cols Gold, 8 cols Silver/Bronze

---

## Fase 2 — Brand Profile (~1.5h) — 0/5

### 2.1 Layout — 0/2
- [ ] Hero con logo + nombre + tier + categoria
- [ ] Tabs: Acerca de / Servicios / Sesiones / Contactar
- [ ] Mount → `POST /sponsors/{id}/view` fire-and-forget

### 2.2 Acerca de — 0/1
- [ ] Bio (`description`) + redes/website (`website_url`) + boton Favorite

### 2.3 Servicios — 0/1
- [ ] Grid de `services[]` con imagen + nombre + descripcion

### 2.4 Sesiones — 0/1
- [ ] Lista de `sessions[]` con click → abre `/agenda` o detail W.3

### 2.5 Contactar — 0/2
- [ ] Solo si `show_contact_button=true`
- [ ] Form: mensaje (max 500 chars)
- [ ] Submit → `POST /sponsors/{id}/contact {message}` → toast "Tu mensaje fue enviado a {sponsor}"

---

## Fase 3 — Trivia integration (~1h) — 0/3

NOTA: La trivia de sponsor es parte de la gamification del evento (W.9). Esta fase solo monta el componente que apunta al endpoint correcto.

### 3.1 Trivia panel — 0/2
- [ ] Si gamification config indica que el sponsor tiene trivia → tab "Trivia" en profile
- [ ] Lista preguntas + opciones + responder
- [ ] `POST /events/{eventId}/trivia/{triviaId}/answer {answer_index}` (verificar shape exacto)

### 3.2 Estado — 0/1
- [ ] Ya jugado: mostrar resultado + "Ya completaste la trivia"

---

## Fase 4 — Tests (~30min) — 0/3

### 4.1 Vitest — 0/1
- [ ] `useSponsors` agrupa por tier correctamente

### 4.2 Playwright — 0/2
- [ ] Happy path: ver wall + abrir profile + enviar contact
- [ ] Edge case: sponsor sin `show_contact_button` → tab Contactar oculto

---

## Edge cases

- [ ] Sponsor sin servicios → tab Servicios oculto
- [ ] Sponsor sin sesiones → tab Sesiones oculto
- [ ] Sponsor sin trivia → tab Trivia oculto
- [ ] `show_contact_button=false` → tab Contactar oculto
- [ ] Contact form con mensaje vacio → permitido (backend acepta `message?`)
- [ ] Contact muy largo → bloquear pre-submit
- [ ] Sponsor activo sin tier asignado → fallback "Otros"
- [ ] Logo broken → placeholder con primera letra del nombre
- [ ] Lead duplicado: backend deduplica? Si retorna 409, mostrar "Ya enviaste un mensaje" (verificar comportamiento)
- [ ] Trivia ya completa → no permitir reenviar (idempotencia backend)

---

## Pendiente backend (nice to have)

- Endpoint detail individual `GET /sponsors/{id}` (hoy se reusa item del index, OK pero menos eficiente con 100+ sponsors)
- Search server-side en `/sponsors`
- Filtro por tier server-side

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports
- [ ] Lighthouse OK
- [ ] Commit DaVinci + memoria + PENDIENTES.md
