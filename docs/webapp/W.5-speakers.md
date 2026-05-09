# W.5 — Speakers

> Directorio de speakers + perfil detallado + ratings 1-5 (UNIQUE, no editable). **Espejo del Expo mobile** — sin tracks, sin chips, sin favorite speakers, sin tabs "Mi Speakers".
>
> **Estimacion:** ~5.5h (incluye agenda highlight extension + deep linking).
> **Dependencias:** W.0, W.1, W.3 (extension de AgendaScreen para `?highlight=X`).
> **Estado:** Demo v2 aprobado 2026-05-08, listo para implementar.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- `W.0-spatial-ui.md`
- App movil:
  - `app/(app)/speakers.tsx` — pantalla lista
  - `app/(app)/speaker/[id].tsx` — pantalla detalle
  - `hooks/useSpeakers.ts`, `hooks/useSpeakerRating.ts`
- Webapp agenda (referencia de wireframe desktop):
  - `src/components/app/agenda/AgendaView.tsx`
  - `src/components/app/agenda/AgendaHeader.tsx`
  - `src/components/app/agenda/DetailPanel.tsx`
  - `src/components/app/agenda/RatingModal.tsx`
- Backend: `app/Http/Controllers/Api/V1/SpeakerController.php`, `SpeakerRatingController.php`, `SpeakerResource.php`
- Demo HTML aprobado: `design/features/webapp/SPEAKERS/speakers-v1-davinci.html`

---

## Doctrina (Memoria viva)

Webapp = espejo Expo en datos+comportamiento. Agenda webapp aporta SOLO wireframe desktop:

| Aspecto | Fuente |
|---|---|
| Datos, shape, edge cases, copys, comportamiento on-click | **Expo mobile** |
| Canvas, side panel, search ⌘K, animaciones, sin back button, keyboard nav | **Agenda webapp** |

NO alucinar features que no existen en Expo (tracks/chips/featured flags/favorite speakers).

---

## Endpoints reales (verificados 2026-05-07)

```
GET /api/v1/events/{eventId}/speakers                    (publico, cache 5min)
  → {data: [SpeakerResource[]]} con sessions[] preloaded

GET /api/v1/events/{eventId}/speakers/{speakerId}        (publico)
  → {data: SpeakerResource}

POST /api/v1/events/{eventId}/speakers/{speakerId}/rate  (sanctum)
  body: {rating: 1-5, comment?: string}
  → {data: {id, rating, comment}}
  errores: 409 si re-rate (UNIQUE constraint)

GET /api/v1/events/{eventId}/my-speaker-ratings          (sanctum)
  → {data: {speakerId: rating, ...}}
```

---

## SpeakerResource shape

```ts
{
  id: number,
  name: string,
  bio: string | null,
  company: string | null,
  job_title: string | null,
  photo_url: string | null,
  linkedin_url: string | null,
  avg_rating: number | null,    // float redondeado a 1 decimal
  rating_count: number,
  sessions: EventSessionResource[]   // viene cargado siempre (whenLoaded)
}
```

**NO existen** `is_featured`, `is_keynote`, `track`, ni tabla `attendee_favorite_speakers`. Featured y keynote pill se DERIVAN de `sessions[].session_type.name`.

---

## Alcance real

1. **Lista** ordenada alfabeticamente por `name`, sin paginacion
2. **Featured carousel** (Destacados) derivado:
   - `keynotes = speakers.filter(hasKeynote)` donde `hasKeynote = sessions.some(s.session_type.name.toLowerCase().includes('keynote'))`
   - `if (keynotes.length >= 2) return keynotes.slice(0, 8)`
   - `else return [...speakers].sort((a,b) => b.sessions.length - a.sessions.length).slice(0, 5)`
3. **Search cliente** (debounce 400ms) por `name`, `company`, `job_title`. Se oculta destacados cuando hay search.
4. **Detail side panel** desktop / full-screen mobile (CSS responsive, no ruta separada)
5. **Rating** 1-5 con comment opcional 280 chars. UNIQUE → 409 si re-rate.
6. **Click sesion** en speaker detail → `router.push('/agenda?highlight=X')` → agenda hace scrollIntoView + pulse 1.7s.
7. **Deep link** `/speakers?id=X` auto-abre detail.
8. **LinkedIn** condicional (si `linkedin_url`), abre en nueva pestaña (`target="_blank"`, `rel="noopener noreferrer"`).

NO entra:
- Favorite speakers (no existe backend)
- Tabs "Mi Speakers" / filtro favorited
- Tracks o chips de filtro
- Featured/keynote como flags de DB (todo derivado)
- Mostrar `avg_rating` o estrella en cards de lista (replica mobile, solo en detail)

---

## Eventos socket

Ninguno dedicado para speakers. `refresh_all` (notifications) puede invalidar cache si backend lo emite. Refetch on focus en `useSpeakers`.

---

## Wireframe demo

`design/features/webapp/SPEAKERS/speakers-v1-davinci.html` (v2 aprobado 2026-05-08):
- Header: solo titulo "Speakers" + tools (search button colapsable ⌘K) — sin back, sin subtitle, sin chip-row
- speakers-card SIN background wrapper (solo contenido)
- Destacados: BreathingCarousel con FeaturedCards (200px width, foto 4:5, glass Noir/Lux)
- Todos: 1 col mobile / 2 col desktop (container queries), SpeakerListItem glass
- Side panel detail right: foto cap max-width 260 centrada, name + role bold, stars+rating row si rating_count>0, 2 botones flex:1 (LinkedIn condicional + Calificar gold), bio card "ACERCA DE", sessions con session_type pill colors

---

## Fase 0 — Pre-vuelo (~30min) — 0/5

- [ ] Verificar `BreathingCarousel` en webapp (no aparecio en grep) — crear si falta
- [ ] Crear `lib/types/speaker.ts` con `Speaker`, `SpeakerSession`, `SessionType`
- [ ] Crear `lib/speakersClient.ts` (proxies `/api/speakers/[eventId]/...` apuntando a Laravel)
- [ ] Crear `app/api/speakers/[eventId]/route.ts`, `app/api/speakers/[eventId]/[speakerId]/route.ts`, `app/api/speakers/[eventId]/[speakerId]/rate/route.ts`, `app/api/speakers/[eventId]/my-ratings/route.ts`
- [ ] Verificar accent gold ya existe en `<Button>` shadcn (variant) — agregar si falta

---

## Fase 1 — Hooks + tipos (~45min) — 0/4

- [ ] `useSpeakers(eventId, initialData?)` — TanStack Query, staleTime 5min, refetchOnWindowFocus
- [ ] `useMySpeakerRatings(eventId)` — map `{speakerId: rating}`
- [ ] `useSubmitSpeakerRating()` — useMutation con optimistic update + 409 handling + revert
- [ ] (Opcional) `useSpeaker(eventId, id)` — solo si necesitamos refetch granular

---

## Fase 2 — SpeakersView (lista) (~1.5h) — 0/8

### 2.1 Page + SSR (~20min) — 0/2
- [ ] `app/[locale]/(app)/speakers/page.tsx` — server component, fetch initial data
- [ ] `<SpeakersView event={...} initialSpeakers={...} />` client component

### 2.2 Header + Search (~30min) — 0/2
- [ ] `<SpeakersHeader />` solo title + tools (search button) — sin tabs, sin subtitle (replica AgendaHeader sin tabs)
- [ ] Search overlay colapsable ⌘K + debounce 400ms

### 2.3 Listado (~40min) — 0/4
- [ ] Featured derivation logica + render condicional (oculto si hay search)
- [ ] `<BreathingCarousel>` + `<FeaturedCard speaker>` (foto 4:5, Keynote pill condicional, 200px width, glass Noir/Lux)
- [ ] `<SpeakerListItem speaker>` (avatar 56px, name, role+company, count sesiones pill)
- [ ] Empty states: "No hay speakers registrados" / "No se encontraron speakers"

---

## Fase 3 — Detail panel (~1.5h) — 0/7

### 3.1 Panel shell (~20min) — 0/2
- [ ] `<SpeakerDetailPanel />` con `open`, `swapping`, animacion translateX 80→0 480ms (constantes PANEL_SWAP_MS=260, PANEL_CLOSE_MS=240)
- [ ] EmptyHint cuando panel cerrado (replica agenda EmptyHint)

### 3.2 Contenido (~40min) — 0/3
- [ ] Hero foto cap max-width 260 centrada (NO full-width)
- [ ] Name 28px + Role: `{job_title} en <b>{company}</b>` (con literal "en")
- [ ] Stars + avg_rating + n votos (solo si `rating_count > 0`)

### 3.3 Acciones + bio + sesiones (~30min) — 0/2
- [ ] Action row: LinkedIn (condicional, `<a target="_blank" rel="noopener noreferrer">`) + Calificar gold. Cuando LinkedIn ausente: max-220 alineado derecha
- [ ] Bio card "ACERCA DE" + Sessions list con session_type pill (`session_type.color`)

---

## Fase 4 — RatingModal + mutation (~45min) — 0/4

- [ ] Extender `agenda/RatingModal.tsx` aceptando prop `title` opcional (default "Como estuvo la sesion?") — alternativa: copiar a `speakers/RatingModal.tsx` si la signature diverge demasiado
- [ ] Wire `useSubmitSpeakerRating` con `lumina.success('Gracias por evaluar al speaker')`
- [ ] Manejo 409 → `lumina.error('Ya calificaste a este speaker')`
- [ ] Estado `.rated` en boton Calificar → "Evaluado" goldSoft, no clickable

---

## Fase 5 — Deep linking + agenda highlight (~1h) — 0/4

### 5.1 Deep link speakers (~20min) — 0/2
- [ ] `useSearchParams` lee `?id=X` → auto-abre panel
- [ ] `router.push('/speakers?id=X')` al openDetail; `router.push('/speakers')` al close

### 5.2 Click sesion → agenda highlight (~40min) — 0/2
- [ ] Speaker detail session card onClick: `closePanel(); router.push('/agenda?highlight={sessionId}')`
- [ ] Extender `AgendaView.tsx`:
  - `useSearchParams` lee `?highlight=X`
  - Si presente: encuentra sesion, set `selectedDay`, scrollIntoView del card 800ms despues, set `highlightedId=X` por 1700ms
  - Pulse animation en `.sess-card[data-id="X"]`: outline gold 2px + boxShadow gold pulse 2s alternate
  - Limpia `?highlight` del URL al terminar

---

## Fase 6 — Responsive + skeleton + tests (~1h) — 0/5

- [ ] Skeleton inicial SpeakersView (header + 5 sp-row placeholders)
- [ ] Skeleton SpeakerDetailPanel (foto+name+actions+bio+2 sessions)
- [ ] Mobile webapp: panel detail full-screen overlay (CSS @media)
- [ ] Tablet vertical bloqueada con overlay W.0
- [ ] Vitest: filterSpeakers, getFeatured derivation, hasKeynote
- [ ] Playwright: happy path (lista → click → rating → toast → deep link reload mantiene panel)

---

## Edge cases

- [ ] Speaker sin foto → placeholder ui-avatars (replica mobile)
- [ ] Speaker sin sesiones → empty state inline en detail "Sin sesiones publicas asignadas"
- [ ] Rating 409 → toast "Ya calificaste a este speaker", boton queda en estado .rated
- [ ] `rating_count === 0` → no mostrar Stars row en detail
- [ ] LinkedIn `null` → boton ausente, Calificar acotado max-220 alineado derecha
- [ ] Deep link `?id=X` con speaker inexistente → ignorar, render lista normal sin error
- [ ] Click sesion → agenda highlight pero sesion ya pasada → highlight pulse igual (no hay diferencia visual)
- [ ] Search vacio → mostrar todos en orden alfabetico
- [ ] Search activo → ocultar Destacados (replica mobile)
- [ ] Solo 1 speaker en evento → no Destacados, lista de 1 en "Todos"

---

## Pendiente backend (nice to have, NO bloquea W.5)

- **Featured/keynote flags** en DB si producto pide control manual (hoy 100% derivado de session_type)
- **Filtros server-side** si lista crece >100 speakers (hoy filtra cliente, cache 5min hace OK)
- **Favorite speakers** si producto lo pide — abrir issue, requiere tabla `attendee_favorite_speakers`

## Pendiente Expo mobile (parity post W.5)

- **Click sesion en speaker detail → agenda highlight** (hoy abre `/session/[id]` standalone). Webapp lo hace mejor; portar a mobile despues para parity.

---

## Cierre

- [ ] Tests verde (vitest + playwright)
- [ ] Validado 3 viewports (desktop, tablet H, mobile webapp)
- [ ] Lighthouse OK
- [ ] Deep link `?id=X` funciona en hard reload
- [ ] Click sesion → agenda redirige + highlight
- [ ] Commit DaVinci + memoria + PENDIENTES.md + ROADMAP-RECAP.md
