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

## Endpoints (verificados 2026-05-07)

- `GET /api/v1/events/{id}/announcements` — lista completa (no paginado, sin `?cursor`). Shape: `{data: AnnouncementResource[]}` con `{id, title, body, action_url, image_url, roles, published_at}`
- `GET /api/v1/events/{id}/banners` — banners activos
- `GET /api/v1/events/{id}/highlights` — boletines

**NO existe** `POST /announcements/{id}/read` — el "leido" es client-side via `localStorage` (ver seccion BellPopover abajo).

Socket events:
- `announcement:new` — RT push (NO `announcement.new`). Payload: `AnnouncementPayload {id, title, body, eventId, createdAt}` (ver `eventos-socket/src/types.ts`)

---

## Fase 0 — Hooks (~30min) — 1/3
- [x] **SSR fetcher** `lib/announcements.ts` con `fetchAnnouncements(eventId)` (apiFetch + bearer cookie). Pattern: server-side en layout + page, sin client hook duplicado.
- [~N/A] `useBanners(eventId)` con auto-rotate logic — Fase B
- [~N/A] `useHighlights(eventId)` — Fase B (compartido con W.2 Home)

---

## Fase 1 — Anuncios (~1h) — 3/4 ✓

### 1.1 Lista — 2/2 ✓
- [x] `<AnnouncementsView />` lista vertical de cards (image opcional + dot color + timeAgo + title + body + chevron). Espejo Expo `anuncios.tsx`. Empty state cuando 0 items.
- [~rechazado] Tabs "Todos / No leidos" — eliminado: backend NO persiste read_at, todos los items son "vistos" cuando abris la pagina. Tabs serian solo cosmeticas y confusas. Decision DaVinci.

### 1.2 Detalle + RT — 1/2
- [x] Click anuncio → handler segun `parseActionUrl()`: internal (router.push) / external (window.open '_blank') / internal-future (toast amable) / unknown (console.warn). Sin modal/panel — los anuncios entran completos en card, no merecen detail page.
- [ ] Socket `announcement:new` → invalidate + badge unread aumenta — depende W.11 sockets RT (~20%). Fase B.

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

## Fase 4 — Tests (~30min) — 2/3 ✓

### 4.1 Vitest — 1/1 ✓
- [x] **Helpers puros 39 tests verde:** `announcementDeeplink.test.ts` (23 — 13 mappings + none/external/edge + backlog future) + `announcementsUnread.test.ts` (16 — countUnread + lastSeenKey + timeAgo). 309/309 suite total.

### 4.2 Playwright — 1/2
- [x] **E2E `anuncios.spec.ts` 10/10 verde (15s estable, serial mode):** auth gate / SSR lista sorted / click action interna (golden ticket → /desafio, espejo Expo) / click sin action_url (no nav, info estatica) / click internal-future (toast amable, no nav) / empty state / BellPopover badge unread baja a 0 al abrir / Ver todos navega / click card popover golden ticket → /desafio / sidebar item Anuncios navega.
- [ ] Edge case socket `announcement:new` — Fase B (depende W.11).

---

## Edge cases

- [ ] Banner sin imagen valida → skip + log warning
- [ ] Anuncio urgente con scheduled_at futuro → no mostrar hasta tiempo
- [ ] Anuncio expirado → no mostrar en lista
- [ ] Multiples anuncios urgentes simultaneos → stack toasts (no overlap)
- [ ] Carousel banners pausa al hover (desktop)
- [ ] Carousel se reduce a 1 sin auto-advance si hay solo 1 banner
- [ ] Anuncio con HTML malicioso → DOMPurify
- [ ] Mark as read offline → solo localStorage (sin sync server, no hay endpoint)

---

## Acceso desde la app

- Bell badge en header pill bar muestra count anuncios no leidos
- Click bell → abre `<AnnouncementsList />`
- Banners aparecen contextualmente (home, agenda, sponsors)

---

## Bell + Announcements popover (sidebar W.0) — pendiente diseñado 2026-05-06

> Documentado para implementar como parte de esta sesion W.14. Hoy el bell
> en `SidebarPill` esta como `<span>` deshabilitado con tooltip
> "proximamente" (BUG-328 fix). El comportamiento real es:

### Plan de implementacion

1. **Server-side fetcher** (`lib/announcements.ts`):
   - `fetchAnnouncements(eventId)` → llama `GET /api/v1/events/{id}/announcements`
   - Retorna `Announcement[]` con shape: `{id, title, body, action_url, image_url, roles, published_at}`
   - Filtrar por `roles.includes(user.role)` o vacio (= todos los roles)

2. **Layout integration** (`app/[locale]/(app)/layout.tsx`):
   - Fetch announcements en paralelo con event + user
   - Pasar al `SpatialShell` → `SidebarPill` como prop

3. **Componente cliente `BellPopover.tsx`**:
   - Reemplaza el `<span>` deshabilitado actual
   - Usa Radix Popover (ya existe en `components/ui/popover.tsx`)
   - Trigger = bell button con badge cuenta
   - Content = lista de cards (title + body + tiempo relativo + image opcional + action_url linkeable)

4. **Estado "no leido" sin endpoint backend**:
   - Backend NO expone `read_at` per usuario en announcements
   - Workaround: `localStorage.eventos:announcements:lastSeenAt:{eventId}` con timestamp ISO
   - Badge cuenta items con `published_at > lastSeenAt`
   - Al abrir popover → `lastSeenAt = now()` → badge → 0

5. **Tiempo relativo:**
   - `Intl.RelativeTimeFormat("es-CO")` para "hace 2h", "hace 3d"
   - Built-in, sin lib externa

6. **Click en card:**
   - Si `action_url` → `window.open(action_url, "_blank")`
   - Sino → solo cerrar popover

### Endpoints verificados (curl 2026-05-06)

```
GET /api/v1/events/{id}/announcements
→ {data: [{id, title, body, action_url, image_url, roles, published_at}]}
```

Roles default: `["attendee"]`. published_at = ISO8601.

### Razon por la que NO se hizo en W.0 fix

W.0 audit cerro 3 bugs (BUG-326/327/328) con el bell como `<span>`
"proximamente" para evitar el 404 silencioso. El popover real es
funcionalidad de W.14 (anuncios) — NO contaminar el shell con feature
logic. Cuando se ejecute esta seccion W.14, tocar:

- `components/shell/SidebarPill.tsx` → reemplazar `<span>` bell por
  `<BellPopover />`
- `components/shell/SpatialShell.tsx` → aceptar y pasar prop
  `announcements`
- `app/[locale]/(app)/layout.tsx` → fetch announcements server-side

### Estimacion

~30-40 min (fetcher + popover + localStorage tracking + estilos).

### Decisiones cerradas

- **Sin Web Push** — backend solo tiene Expo (mobile). Para webapp el bell
  solo lista anuncios via fetch + RT socket en W.11. NO hay notificacion
  del SO.
- **Sin endpoint `/me/notifications`** — la app movil tampoco lo tiene;
  los anuncios son public per-evento, el "no leido" es client-side
  via localStorage.

---

## Cierre Fase A (2026-06-29) — 10/20

- [x] Tests verde — 309/309 vitest + 10/10 E2E anuncios.spec.ts
- [x] Commit DaVinci + memoria + PENDIENTES + PARITY actualizados
- [ ] Validado 3 viewports — Fase B con banners (los anuncios solos son lista vertical estandar)
- [ ] Lighthouse OK — batch QA final cross-modulos

## Pendiente Fase B (~2h)

- [ ] BannersCarousel auto 5s + indicators + video/image (tocar W.2 home tambien)
- [ ] HighlightsList + cross-link con W.2 home
- [ ] Socket `announcement:new` (depende W.11)
- [ ] **Web Push real (manifest + SW + VAPID + endpoint backend `/me/push-subscriptions`)** → W.12 Polish, no aqui. Backend ya tiene 8 tipos de push (`announcement`, `agenda_reminder`, `agenda_update`, `session_changed`, `staff_invitation`, `staff_accepted`, `support_resolved`, `refresh_all`) que el cliente Expo procesa via `useNotifications.ts:67-77`. Cuando se haga Web Push, respetar este enum 1:1.
