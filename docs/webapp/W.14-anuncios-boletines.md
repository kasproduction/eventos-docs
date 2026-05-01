# W.14 — Anuncios + Boletines

> Comunicaciones del organizador: anuncios urgentes (push-style) + boletines/highlights (carousel editorial).
>
> **Estimacion:** ~3h.
> **Dependencias:** W.0, W.1, W.11 (RT push).
> **Estado:** Pendiente.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- App movil: `app/(app)/anuncios.tsx` + `app/(app)/banners.tsx`
- Hooks: `useAnnouncements`, `useBanners`, `useHighlights`
- Memoria: `feedback_banners_vs_patrocinadores.md` (banners != sponsors, son comunicaciones)

---

## Alcance

1. **Anuncios urgentes**: notificaciones in-app push-style enviados por organizador (lista + leidos/no leidos + tap → detalle)
2. **Banners**: imagenes/videos rotativos en zonas estrategicas (home hero, agenda header, sponsors header)
3. **Boletines/Highlights**: items editoriales con cover + titulo + body (mostrados en home carousel y aqui en detalle)

Importante: **banners NO son patrocinadores** (memoria del usuario lo aclara). Los banners son comunicaciones del organizador (informativos, recordatorios, hype). Sponsors es modulo aparte (W.7).

---

## Refs visuales

- App movil anuncios: lista cards con badge unread
- Banners: carousel rotativo con auto-advance
- Boletines: cover + titulo + fecha en cards

---

## Endpoints (verificar)

- `GET /api/v1/event/{id}/announcements?cursor` — anuncios paginados
- `POST /api/v1/announcements/{id}/read` — marcar leido
- `GET /api/v1/event/{id}/banners` — banners activos
- `GET /api/v1/event/{id}/highlights` — boletines

Socket events:
- `announcement.new` — RT push para anuncios urgentes

---

## Fase 0 — Hooks (~30min) — 0/3

- [ ] `useAnnouncements(eventId)` con read mutation
- [ ] `useBanners(eventId)` con auto-rotate logic
- [ ] `useHighlights(eventId)` (compartido con W.2 Home)

---

## Fase 1 — Anuncios (~1h) — 0/4

### 1.1 Lista — 0/2
- [ ] `<AnnouncementsList />` con cards (icono tipo + titulo + extracto + tiempo + dot unread)
- [ ] Tabs: "Todos" / "No leidos"

### 1.2 Detalle + RT — 0/2
- [ ] Click anuncio → `<AnnouncementDetail />` modal o panel secundario
- [ ] Socket `announcement.new` → toast + invalidate lista + badge unread aumenta

---

## Fase 2 — Banners carousel (~1h) — 0/4

### 2.1 Componente — 0/2
- [ ] `<BannersCarousel />` con auto-advance 5s + indicators
- [ ] Soporta imagen y video (mute autoplay loop)

### 2.2 Embedding — 0/2
- [ ] Banner en home hero (rotativo 1 a la vez)
- [ ] Banner sticky en otras pantallas si organizador lo configura

---

## Fase 3 — Boletines/Highlights (~30min) — 0/3

### 3.1 Lista — 0/2
- [ ] `<HighlightsList />` con cards editoriales (cover grande + titulo + fecha)
- [ ] Click → `<HighlightDetail />` con body completo (HTML safe)

### 3.2 Cross-link — 0/1
- [ ] Carousel mini en home (W.2) reusa este modulo

---

## Fase 4 — Tests (~30min) — 0/3

### 4.1 Vitest — 0/1
- [ ] Mark as read optimistic

### 4.2 Playwright — 0/2
- [ ] Happy path: ver lista + leer anuncio + ver banner rotando
- [ ] Edge case: socket announcement.new → toast aparece + badge actualiza

---

## Edge cases

- [ ] Banner sin imagen valida → skip + log warning
- [ ] Anuncio urgente con scheduled_at futuro → no mostrar hasta tiempo
- [ ] Anuncio expirado → no mostrar en lista
- [ ] Multiples anuncios urgentes simultaneos → stack toasts (no overlap)
- [ ] Carousel banners pausa al hover (desktop)
- [ ] Carousel se reduce a 1 sin auto-advance si hay solo 1 banner
- [ ] Anuncio con HTML malicioso → DOMPurify
- [ ] Mark as read offline → optimistic + sync al reconectar

---

## Acceso desde la app

- Bell badge en header pill bar muestra count anuncios no leidos
- Click bell → abre `<AnnouncementsList />`
- Banners aparecen contextualmente (home, agenda, sponsors)

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports
- [ ] Lighthouse OK
- [ ] Commit DaVinci + memoria + PENDIENTES.md
