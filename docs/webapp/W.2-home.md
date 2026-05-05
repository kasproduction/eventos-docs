# W.2 — Home

> Pantalla principal post-login. Hero del evento + countdown + happening now + GamificationHud + Recap banner + Highlights carousel + Anuncios mini + Sponsors brand wall preview + Module menu compacto + Post-event survey prompt + EventArchive (eventos pasados) + Atajos a modulos.
>
> **Estimacion:** ~9h (expandida de 6h por submodulos faltantes).
> **Dependencias:** W.0 (spatial + canvas raiz validado), W.1 (auth + layout shell), W.9 (gamification para Hud), W.14 (anuncios mini).
> **Estado:** **IMPLEMENTADO base 2026-05-04** en `eventos-web/src/components/app/home/` (cinematic + mute por estado, 3 estados PRE/LIVE/ENDED, feed scrolleable de salas conectado al backend via `/api/v1/events/{id}/happening-now`). Pendientes UI/UX: simultaneas multi-sede, recap stats endpoint, panel notif overlay del bell.

---

## Diseno final (validado 2026-05-03)

**Direccion:** cinematic + **mute por estado** dentro del canvas raiz universal del shell W.0 (ver `W.0-spatial-ui.md` seccion "Patron del card raiz universal").

**Demo activo:** `design/features/webapp/W0-spatial/home-v2-C-cinematic-MUTE.html`

### Composicion por estado

Las 3 vistas miden **identico footprint** (mismo card raiz 16/9), pero su layout interno cambia segun el contenido pide:

- **PRE**: poster full + wordmark del evento (`SUMMIT 2026` accent en el ano) bottom-left + countdown firma bottom-right. SIN CTAs. La unica accion es esperar.
- **LIVE**: split `7fr 3fr`. Poster izq con wordmark + meta del dia. Happening-col 30% derecha = **feed scrolleable de salas activas (patron app movil)**: header dinamico `EN VIVO · N SALAS`, lista vertical de `.room-card` con scroll interno (avatar speaker 38px + sala + timer + titulo line-clamp 2 + speaker), 1 card featured con tinte accent. Click en card = abre stream. Sin CTA "Unirme" separado — cada card es el tap target. Footer `.next` con proximo cambio de slot.
- **ENDED**: split `6fr 4fr`. Poster apagado (`filter: brightness(0.72)`) izq con wordmark del evento. Recap-col derecha con stats 2x2 (horas/sesiones/conexiones/seguimientos), tier-row Gold con `Top 18%`, **unico CTA "Ver mi recap"**, certificado como link sutil.

### Decisiones cerradas

1. **Card raiz universal** — formulas en `W.0-spatial-ui.md`. Aplica a TODOS los modulos.
2. **Mute por estado** elegido sobre 3 alternativas (Split, Overlay, PiP). Razon: cada estado tiene contenido distinto y requiere composicion distinta.
3. **Wordmark del evento** display 44px (`SUMMIT 2026` con accent en `2026`). NO tipografia CSS gigante (regla `feedback_keyvisual_not_typography`). El keyvisual real del cliente (en prod `<img>`) sigue siendo protagonista.
4. **Sin strip footer suelto** debajo del card — regla VisionOS: si necesitas info adicional, va dentro del card o en una pill propia con surface.
5. **CTAs reducidos al minimo** (PRE: 0, LIVE: 1, ENDED: 1). Cualquier secundario va como link sutil, no boton paralelo.
6. **Sin "Mi entrada · QR" en desktop** — regla `feedback_qr_only_mobile`. En PRE el countdown habla solo.
7. **Bell-pill rounded-rect top-right** (no circulo) como "app solita". Perfil en sidebar abajo. NO duplicar perfil en toolbar.
8. **Sin "Itinerario sugerido" en PRE** — el countdown + fecha + sede ya dicen todo.
9. **Sesiones simultaneas LIVE = feed scrolleable como app movil**. Lista vertical de `.room-card` con scroll interno. Sala **featured** (tinte accent): regla `(a) favorita del usuario activa, fallback (b) keynote/track principal del organizador`. NO usar `(c) sala con mas asistentes` (metrica tipo Cisco, no aporta al asistente). Sin CTA "Unirme al stream" separado — cada card es el tap target.

### Pendientes de diseno (abiertos)

1. **Multi-sede (Bancolombia)**: pill selector de sede en eyebrow del poster + chip "Tambien en MED·CAL".
3. **Foto real del speaker**: hoy es placeholder gradient. En prod va `<img>` con `object-fit: cover` y halo accent.
4. **Proximos eventos del organizador en ENDED**: hoy no aparecen. Si los queremos, van como mini-card adentro del recap-col.
5. **Atmosfera dinamica por estado**: hoy igual en los 3. Idea: LIVE mas oscuro (apagaron luces), PRE mas luminoso.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- `W.0-spatial-ui.md` (PanelManager API)
- App movil: `screens/home/` — tomar decisiones de jerarquia ya validadas

---

## Estado de implementacion (2026-05-04)

El rediseno **cinematic + mute por estado** (validado 2026-05-03) reemplazo la descomposicion original en 9 fases por una composicion por estado de evento. El alcance que SI se entrego en `eventos-web/src/components/app/home/` es:

| Item | Estado |
|---|---|
| Hero cinematic + wordmark del evento + 3 estados (PRE/LIVE/ENDED) | DONE |
| Happening Now como feed scrolleable de salas (patron app movil) | DONE |
| Conexion backend `/api/v1/events/{id}/happening-now` | DONE |
| Mute por estado dentro del canvas raiz universal (W.0) | DONE |
| ENDED: stats 2x2 + tier-row Gold + CTA "Ver mi recap" | DONE (mock data — endpoint pendiente) |
| Sponsors logo band sutil abajo | NOT YET (originalmente Fase 4) |
| GamificationHud preview en LIVE | NOT YET (originalmente Fase 5) |
| Anuncios mini banner | NOT YET (originalmente Fase 6) |
| Highlights carousel | DESCARTADO en cinematic — vive en W.14 |
| Module menu + atajos modulares | DESCARTADO — sidebar W.0 cubre nav |
| EventArchive link | NOT YET (originalmente Fase 8) |
| Post-event survey prompt | NOT YET (originalmente Fase 7.2) |
| Multi-sede pill selector (Bancolombia) | NOT YET (pendiente diseno) |
| Recap stats endpoint backend | NOT YET (bloqueante para datos reales en ENDED) |
| Panel notif overlay del bell | NOT YET (W.0 lo lista tambien) |

Fases originales 0-9 abajo quedan como **referencia historica**. Los items "NOT YET" arriba son los que sobreviven y deben planearse en una iteracion proxima de W.2.

---

## Alcance

1. Hero configurable (nombre evento, fecha, branding)
2. Countdown timer (DD:HH:MM:SS) si pre-evento
3. Happening Now panel (sesion en vivo destacada)
4. **GamificationHud** (puntos visibles + posicion + badge ultimos desbloqueados — readonly preview)
5. **Recap banner** post-evento (link a recap del evento Fase 2)
6. Highlights carousel (boletines — usa `useHighlights` compartido con W.14)
7. **Anuncios mini** (top 3 anuncios no leidos — link a W.14 para ver todos)
8. Sponsors mini brand wall (top sponsors)
9. **Module menu compacto** (atajos a 6 modulos principales)
10. **Post-event survey prompt** (banner sutil si evento termino y user no respondio survey)
11. **EventArchive** (si user tiene eventos pasados, link "Ver eventos anteriores")
12. Atajos a modulos principales (cards clickeables)
13. Empty states completos por widget

---

## Refs visuales

- App movil home (`features/Screenshot 2026-...`) — jerarquia validada
- `design/LANDING/landing.webp` — editorial premium con espacio negativo
- `design/event-pulse/` — solo para color tokens accent del evento

---

## Endpoints (verificar antes de codear)

- `GET /api/v1/event/{id}` — datos del evento (nombre, fecha, branding, countdown)
- `GET /api/v1/event/{id}/happening-now` — sesion actualmente live
- `GET /api/v1/event/{id}/highlights` — boletines
- `GET /api/v1/event/{id}/sponsors?tier=top` — sponsors top tier para preview
- `GET /api/v1/event/{id}/me/gamification?summary=true` — puntos + posicion + ultimo badge
- `GET /api/v1/event/{id}/announcements?unread=true&limit=3` — top 3 unread
- `GET /api/v1/event/{id}/post-event-survey/status` — pendiente o no
- `GET /api/v1/me/events?past=true` — eventos pasados (EventArchive)
- `GET /api/v1/event/{id}/recap-config` — saber si recap esta configurado

---

## Fase 0 — Setup (~30min) — 2/3

### 0.1 Hooks — 2/3
- [x] `useEvent(eventId)` — TanStack Query
- [x] `useHappeningNow(eventId)` — refetchInterval 60s
- [ ] `useHighlights(eventId)` — DESCARTADO en cinematic (vive en W.14)

---

## Fase 1 — Hero (~1h) — 4/4 — DONE 2026-05-04

Implementado como **poster cinematic + wordmark + countdown firma** (no como hero clasico con backdrop).

### 1.1 Componente — 2/2
- [x] Poster full + wordmark (`SUMMIT 2026` accent en el ano)
- [x] PRE: countdown firma bottom-right, sin CTAs
- [x] LIVE: split 7/3 con feed de salas activas
- [x] ENDED: poster apagado + recap-col con tier Gold + CTA "Ver mi recap"

### 1.2 Responsive — 2/2
- [x] Desktop + tablet H validados (canvas 16/9 universal)
- [ ] Mobile pendiente (replica patron app movil)

---

## Fase 2 — Happening Now (~1.5h) — 4/4 — DONE 2026-05-04

Implementado como **feed scrolleable de salas activas** (patron app movil, NO panel destacado unico).

### 2.1 Componente — 3/3
- [x] Feed de `.room-card` con scroll interno (avatar 38px + sala + timer + titulo + speaker)
- [x] Header dinamico `EN VIVO · N SALAS`
- [x] Sala featured con tinte accent (regla: favorita usuario activa, fallback keynote/track principal)
- [x] Tap target = card completa (sin CTA "Unirme" separado)

### 2.2 Empty state — 1/1
- [x] Footer `.next` con proximo cambio de slot cuando aplica

---

## Fase 3 — Highlights carousel (~1h) — DESCARTADO

Carousel removido del Home en el rediseno cinematic. Highlights/boletines viven ahora en W.14 (`W.14-anuncios-boletines.md`).

---

## Fase 4 — Sponsors brand wall preview (~1h) — 0/3 — PENDIENTE iter 2

Logo band sutil abajo del card. Mantener — Bancolombia querra ver sponsors en home. No bloqueante de cierre W.2 base.

### 4.1 Componente — 0/2
- [ ] `<SponsorsPreview />` con logo band horizontal sutil (NO grid grande, NO canibaliza W.7)
- [ ] CTA "Ver todos" → abre Sponsors panel (W.7)

### 4.2 Responsive — 0/1
- [ ] Logo band escala con clamp (igual que tipografia del card)

---

## Fase 5 — GamificationHud preview (~1h) — 0/4 — PENDIENTE iter 2

Va dentro del card en LIVE (no como widget separado). Decidir slot exacto al integrar — candidato: chip pequeno en happening-col arriba o eyebrow del poster.

### 5.1 Componente — 0/3
- [ ] `<GamificationHudPreview />` con mis puntos + posicion top X + ultimo badge desbloqueado
- [ ] Dark island treatment (mantiene dark en Lux para drama)
- [ ] CTA "Ver todo" → abre W.9 panel completo

### 5.2 RT — 0/1
- [ ] Socket `points.awarded` → animacion +X puntos sobre el HUD

---

## Fase 6 — Anuncios mini (~30min) — 0/2 — PENDIENTE iter 2

Banner sutil intra-card cuando haya unread urgente. NO strip footer suelto (regla VisionOS).

### 6.1 Componente — 0/2
- [ ] `<AnnouncementsMini />` muestra top 1-3 unread urgente (usa `useAnnouncements` con limit=3)
- [ ] Boton "Ver todos" → abre panel W.14

---

## Fase 7 — Recap banner + Post-event survey (~30min) — 1/3

### 7.1 Recap banner — 1/2 — DONE base
- [x] CTA "Ver mi recap" en estado ENDED del cinematic (recap-col derecha) — 2026-05-04
- [ ] Wiring real: condicional segun `user.recap_image_url !== null`, click abre recap viewer (hoy mock data)

### 7.2 Post-event survey prompt — 0/1 — PENDIENTE iter 2
- [ ] Si evento termino y user no respondio survey → banner sutil "Cuentanos como te fue" + CTA W.9

---

## Fase 8 — EventArchive (~30min) — 0/2 — PENDIENTE iter 2

NO va como strip footer (regla VisionOS). Candidato: opcion en sidebar pill "Más" o link sutil dentro del card en estado ENDED.

### 8.1 Link — 0/2
- [ ] Si user tiene >0 eventos pasados → link sutil "Eventos anteriores" (ubicacion por decidir)
- [ ] Click → abre lista eventos pasados con cover + nombre + fecha + CTA "Ver recap" si existe

---

## Fase 9 — Module menu + Atajos modulares (~1h) — DESCARTADO

Sidebar pill del shell W.0 cubre toda la navegacion entre modulos top-level. Cards atajos en Home eran redundantes y canibalizaban el patron spatial.

---

## Fase 10 — QA + tests (~1h) — 0/4 — PENDIENTE

### 10.1 Vitest — 0/2
- [ ] `useEvent`, `useHappeningNow` con mock TanStack
- [ ] Estados PRE/LIVE/ENDED renderizan composicion correcta + feed de salas con/sin sesiones

### 10.2 Playwright — 0/2
- [ ] Happy path: home carga con datos + click en `.room-card` abre stream + estado cambia segun horario evento
- [ ] Edge case: sin sesiones live muestra footer `.next` correcto + ENDED muestra recap CTA

---

## Edge cases

- [ ] Branding sin `primary_color` → fallback a Lumina Noir default white
- [ ] Highlights con 1 solo item → no carousel, solo card centrada
- [ ] Sponsors sin Platinum/Gold → muestra Silver
- [ ] Pre-evento +30 dias → countdown muestra "DD : HH : MM"
- [ ] Post-evento sin recap configurado → recap banner oculto
- [ ] Refresh durante happening now en transicion → no parpadea (skeleton)
- [ ] User sin gamification activada (evento sin puntos) → ocultar GamificationHud
- [ ] Anuncios mini sin unread → ocultar widget completo
- [ ] EventArchive vacio → ocultar link
- [ ] Post-event survey ya respondido → ocultar prompt
- [ ] Recap aun no generado (job pending) → banner muestra "Tu recap esta en proceso"

---

## Cierre

### Cierre base cinematic (2026-05-04)
- [x] Hero cinematic + 3 estados PRE/LIVE/ENDED
- [x] Feed scrolleable de salas LIVE conectado a backend
- [x] Recap CTA en ENDED (mock data)
- [x] Validado desktop + tablet H
- [x] Commit DaVinci + memoria

### Cierre completo (pendiente iter 2)
- [ ] Tests Vitest + Playwright verde
- [ ] Validado en mobile (replica patron app movil)
- [ ] Lighthouse Performance >= 85 desktop / >= 75 mobile
- [ ] Sponsors logo band + Gamification chip + Anuncios mini integrados
- [ ] Recap banner wiring real (no mock)
- [ ] Post-event survey + EventArchive link (ubicacion por decidir)
- [ ] Multi-sede pill selector (Bancolombia)
- [ ] Atmosfera dinamica por estado
- [ ] PENDIENTES.md actualizado
