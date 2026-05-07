# W.5 — Speakers

> Directorio de speakers + perfil detallado (con sesiones del speaker) + ratings de speaker (con UNIQUE — no editable). **Sin favorite speakers** (no existe en backend).
>
> **Estimacion:** ~4h (reducida de 5h tras audit — sin favorite, sin filtros server-side).
> **Dependencias:** W.0, W.1, W.3 (sesiones del speaker desde agenda).
> **Estado:** Pendiente — backend audit completado 2026-05-07.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- `W.0-spatial-ui.md`
- App movil: `app/(app)/speakers.tsx`, `speaker/[id].tsx`
- Backend: `app/Http/Controllers/Api/V1/SpeakerController.php`, `SpeakerRatingController.php`, `SpeakerResource.php`

---

## Drift corregido (2026-05-07)

Version previa documentaba endpoints + features que NO existen:

- ~~`POST /speakers/{id}/favorite`~~ → **NO existe** favorite de speakers en backend (solo sesiones se favoritean en W.3 y sponsors en W.7)
- ~~`?featured`, `?track`, `?q`, `?favorited` filtros server-side~~ → SpeakerController NO valida estos params. Todo cacheado 5min, filtrado en cliente.
- ~~`GET /speakers/{id}` standalone~~ → real `GET /events/{eventId}/speakers/{speakerId}` (require eventId en path)
- ~~`POST /sessions/{id}/rating`~~ (rate de sesion) → real `POST /events/{eventId}/sessions/{sessionId}/rate` (W.3 ya lo wireo)

Endpoints reales verificados:

```
GET /api/v1/events/{eventId}/speakers                            (publico, cache 5min)
  → {data: [SpeakerResource[]]}

GET /api/v1/events/{eventId}/speakers/{speakerId}                (publico)
  → {data: SpeakerResource}

POST /api/v1/events/{eventId}/speakers/{speakerId}/rate          (sanctum)
  body: {rating: 1-5, comment?: string}
  → {data: {id, rating, comment}}
  errores: 409 si re-rate (UNIQUE constraint)

GET /api/v1/events/{eventId}/my-speaker-ratings                  (sanctum)
  → {data: {speakerId: rating, ...}}
```

---

## SpeakerResource shape (verificado)

```ts
{
  id: number,
  name: string,
  bio: string | null,
  company: string | null,
  job_title: string | null,
  photo_url: string | null,
  linkedin_url: string | null,
  avg_rating: number | null,    // float redondeado a 1 decimal (whenLoaded)
  rating_count: number,
  sessions: EventSessionResource[]   // whenLoaded — viene cargado siempre desde index/show
}
```

---

## Alcance real

1. **Directorio**: lista paginated client-side (no server-side filter)
2. **Filtros cliente**: search por nombre/company, filtro track (si las sesiones tienen track), featured (si backend marca alguno — verificar)
3. **Perfil detallado** con sesiones + redes
4. **Rating del speaker** con UNIQUE (re-rate retorna 409, NO editable)
5. **Mis ratings**: si user ya rateo, mostrar el rating fijo en perfil

NO entra:
- Favorite speakers (no existe)
- Tab "Mis Speakers" (depende de favorites)

---

## Eventos socket

Ninguno dedicado para speakers. Si los datos cambian → refetch on focus o invalidacion.

---

## Refs visuales

- App movil speakers (`features/Screenshot 2026-...`)
- `design/showcase-onboarding-v6.html` `spk-row`

---

## Fase 0 — Hooks (~30min) — 0/3

- [ ] `useSpeakers(eventId)` — fetch index, sin filtros server-side
- [ ] `useSpeaker(eventId, speakerId)` — detalle (opcional, ya viene en index)
- [ ] `useMySpeakerRatings(eventId)` — map `{speakerId: rating}`

---

## Fase 1 — Directorio (~1.5h) — 0/5

### 1.1 Grid — 0/3
- [ ] `<SpeakersGrid />` cards verticales (foto 4/5 + nombre + role + count sesiones)
- [ ] **Estrella + `avg_rating` + `rating_count`** visible si `rating_count >= 3` (umbral evita sesgo con 1-2 ratings)
- [ ] Click → abre perfil

### 1.2 Filtros cliente — 0/2
- [ ] Search input filtra cliente por `name`, `company`, `job_title`
- [ ] Filtro por track derivado de `speaker.sessions[].track.id` (multi-select)
- [ ] (Opcional) Featured: si backend agrega flag, mostrar pill "Keynote"

---

## Fase 2 — Perfil detallado (~1.5h) — 0/4

### 2.1 Layout — 0/2
- [ ] Desktop: panel secondary 50% width
- [ ] Mobile: full screen overlay

### 2.2 Contenido — 0/2
- [ ] Foto grande + nombre + role + company + bio
- [ ] Sesiones del speaker (`speaker.sessions[]`) con CTA "Ver detalle" → abre W.3 detail
- [ ] Redes sociales (`linkedin_url` + otras si backend amplia)

---

## Fase 3 — Ratings (~1h) — 0/3

### 3.1 Trigger — 0/2
- [ ] CTA "Calificar speaker" en perfil (siempre visible si user logueado y ha pasado >=1 sesion del speaker — heuristica cliente)
- [ ] Modal con estrella 1-5 + comentario opcional 280 chars
- [ ] Submit → `POST /events/{id}/speakers/{speakerId}/rate`

### 3.2 Estado — 0/1
- [ ] Si user ya rateo (lookup en `useMySpeakerRatings`) → mostrar rating fijo + "Ya calificaste" (no permitir editar — UNIQUE)
- [ ] Manejo 409 si user intenta re-rate por carrera

---

## Fase 4 — Tests (~30min) — 0/3

### 4.1 Vitest — 0/1
- [ ] Filtro cliente por search + track

### 4.2 Playwright — 0/2
- [ ] Happy path: filtrar + ver perfil + calificar
- [ ] Edge case: ya calificado muestra rating fijo

---

## Edge cases

- [ ] Speaker sin foto → placeholder con iniciales
- [ ] Speaker sin sesiones → empty state "Sin sesiones programadas"
- [ ] Rating con 409 → toast "Ya calificaste a este speaker"
- [ ] Speaker con `rating_count < 3` → no mostrar estrella en card (evita sesgo)
- [ ] Search vacio → mostrar todos
- [ ] Track filter sin matches → "No hay speakers en esa track"
- [ ] Multiples filtros simultaneos (search + track) → AND
- [ ] Favorite speakers solicitado por producto → escalar como pendiente backend

---

## Pendiente backend (nice to have)

- **Favorite speakers**: tabla `attendee_favorite_speakers` + endpoints toggle + filtro `?favorited`. Hoy no existe; si producto lo necesita, abrir issue.
- **Filtros server-side** (`?track`, `?search`) si lista crece >100 speakers (perdida de cache 5min vs filtrar cliente).
- **Featured flag** en SpeakerResource para destacar keynotes.

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports
- [ ] Lighthouse OK
- [ ] Commit DaVinci + memoria + PENDIENTES.md
