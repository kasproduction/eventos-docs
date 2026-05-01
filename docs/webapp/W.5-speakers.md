# W.5 — Speakers

> Directorio de speakers + perfil detallado + ratings post-sesion.
>
> **Estimacion:** ~5h.
> **Dependencias:** W.0, W.1, W.3 (sesiones de speaker).
> **Estado:** Pendiente.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- `W.0-spatial-ui.md`
- App movil: `screens/speakers/` — patrones validados

---

## Alcance

1. Grid/lista directorio de speakers
2. Filtro por track + search
3. Perfil detallado (bio, sesiones, redes sociales)
4. Ratings post-sesion (estrella + comentario opcional)
5. Featured speakers (keynotes destacados)
6. **Ratings agregados visibles en lista** (estrella promedio + count) sin abrir perfil
7. **My Favorites de speakers**: marcar speaker como favorito → tab "Mis Speakers" + filtrar agenda solo del speaker

---

## Refs visuales

- App movil speakers (`features/Screenshot 2026-...`)
- `design/showcase-onboarding-v6.html` `spk-row` — 3 cards horizontal con foto + role + sesiones

---

## Endpoints (verificar)

- `GET /api/v1/event/{id}/speakers?featured&track&q&favorited` — incluye `avg_rating` y `ratings_count`
- `GET /api/v1/speakers/{id}` — incluye sesiones del speaker + redes sociales
- `POST /api/v1/sessions/{id}/rating` — rating post-sesion
- `POST /api/v1/speakers/{id}/favorite` — toggle favorito speaker

---

## Fase 0 — Hooks (~30min) — 0/2

- [ ] `useSpeakers(eventId, filters)`
- [ ] `useSpeaker(speakerId)`

---

## Fase 1 — Directorio (~2h) — 0/6

### 1.1 Grid — 0/3
- [ ] `<SpeakersGrid />` con cards verticales (foto 4/5 + nombre + role + sesiones count)
- [ ] **Estrella + rating promedio** visible en card (si tiene >=3 ratings)
- [ ] Hover/tap → abre perfil

### 1.2 Filtros — 0/3
- [ ] Pills filtro por track
- [ ] Search input
- [ ] Tab "Mis Speakers" (favoritos) — filtra solo speakers favoriteados por user

---

## Fase 2 — Perfil detallado (~2h) — 0/5

### 2.1 Layout — 0/2
- [ ] Desktop: panel secondary 50% width
- [ ] Mobile: full screen overlay

### 2.2 Contenido — 0/4
- [ ] Foto grande + nombre + role + company + bio
- [ ] Sesiones del speaker con CTA "Ver detalle"
- [ ] Redes sociales (LinkedIn, Twitter, etc.)
- [ ] **Boton "Favoritar speaker"** → mutation toggle, guarda en `useSpeakerFavorites`

---

## Fase 3 — Ratings (~1h) — 0/3

### 3.1 Trigger — 0/2
- [ ] Si user asistio a sesion del speaker (post-evento o post-sesion en vivo) → CTA "Calificar"
- [ ] Modal con estrella 1-5 + comentario opcional 280 chars

### 3.2 Estado — 0/1
- [ ] Si ya califico: mostrar rating actual sin posibilidad de cambiar (alineado con app movil — UNIQUE constraint)

---

## Fase 4 — Tests (~30min) — 0/3

### 4.1 Vitest — 0/1
- [ ] `useSpeakers` con filters

### 4.2 Playwright — 0/2
- [ ] Happy path: filtrar + ver perfil + calificar
- [ ] Edge case: ya calificado muestra rating fijo

---

## Edge cases

- [ ] Speaker sin foto → placeholder con iniciales
- [ ] Speaker sin sesiones → empty state "Sin sesiones programadas"
- [ ] Rating cuando sesion no termino aun → CTA deshabilitado
- [ ] User no asistio → no muestra CTA rating
- [ ] Speaker featured pero filtro track activo lo excluye → mostrar igual al inicio (override featured)
- [ ] Speaker sin ratings (<3) → no mostrar estrella en card (evita crear sesgo)
- [ ] Tab "Mis Speakers" sin favoritos → empty state con CTA "Favoritea desde el directorio"
- [ ] Favoritar mientras se desplaza scroll → optimistic + dedup mutations rapidas

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports
- [ ] Commit DaVinci + memoria + PENDIENTES.md
