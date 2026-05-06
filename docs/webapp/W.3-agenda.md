# W.3 — Agenda

> Lista completa de sesiones + filtros (dia/track) + favoritos + detalle + .ics download + ratings post-sesion + chat session-specific.
>
> **Estimacion:** ~11h (real ejecutado).
> **Dependencias:** W.0, W.1.
> **Estado:** **IMPLEMENTADO React 2026-05-06** — wireado al backend (favoritos POST, .ics download, ratings POST, my-ratings GET). Demo HTML base: `design/features/webapp/W3-agenda/agenda-v3-davinci.html`.

---

## Lo entregado (2026-05-06)

**Componentes** (`eventos-web/src/components/app/agenda/`):
- `AgendaView.tsx` — root del modulo, ensambla todo + maneja estado + atajos teclado
- `AgendaHeader.tsx` — title + tabs Agenda|Mi Agenda + buscar (search expanded inline)
- `DayStrip.tsx` — pills horizontales scrolleables, auto-center selected, min 7 dias
- `ChipFilters.tsx` — chips por track con multi-select
- `SessionList.tsx` — timeline + day-slide animation
- `SessionCard.tsx` — patron movil 1:1, heart con cambio de color, action row con border-top
- `DetailPanel.tsx` — floating right, slide-in 480ms + swap-out 260ms entre sesiones
- `AttendeesPop.tsx` — sub-panel modal (oculto hasta endpoint W.8)
- `RatingModal.tsx` — bottom-sheet con 5 estrellas + comment opcional, precarga rating si ya califico
- `agendaDerive.ts` — helpers puros (buildDayStrip, deriveUiState, formatTime, trackSlug)
- `agenda.css` — tokens del demo (--ag-*) + theme overrides Noir/Lux

**Wiring backend** (`eventos-web/src/lib/agendaClient.ts` + `app/api/agenda/...`):
- Favoritos: POST optimistic + revert si falla + reconciliacion con response real
- Rating: POST con manejo de 409 (ya calificado), abre modal readonly si UNIQUE
- My-ratings: GET al mount, alimenta estrellas readonly en cards `past`
- Calendar.ics: download via anchor invisible que respeta Content-Disposition del backend

**Microinteracciones (todas validadas en app movil):**
- Day-slide al cambiar dia (CSS keyframes)
- Slide-in/swap-out del detail panel
- Heart pop simple en card (sin particulas, sin aro — replica scale del app movil)
- Star pop al setear rating
- Toast notifications via lumina (favorite/calendar/success/info/error)

**Decisiones cerradas vs roadmap original:**
- ❌ Chat 1:1 / DM → descartado (WhatsApp/email/LinkedIn cubren). Solo chat por sesion (W.4).
- ❌ Conflict detection con modal → calculado en cliente, sin endpoint backend
- ❌ Asistencia (lista quien va) → endpoint no existe en backend; seccion oculta hasta W.8 networking. Count "interesados" usa `favorites_count`.
- ❌ Room check-in / .ics de Mi Agenda completa / recordatorios push → fuera de scope W.3, mover a W.10/W.4.

---

---

## Demo aprobado (2026-05-05)

`design/features/webapp/W3-agenda/agenda-v3-davinci.html` — demo DaVinci con TODO funcional:

**Layout final:**
- Canvas raiz universal (sin aspect-ratio rigido — fix tablet 2026-05-05)
- Header global ancho del card (60%) alineado izquierda: title "Agenda/Mi Agenda" + subtitle dia + tabs + "Todas" (mi agenda) + buscar
- Sin back button (eliminado por usuario)
- DayStrip pills ovaladas (52×74 radius 999px) — 7 dias con simetria (2 vacios + 3 evento + 2 vacios)
- Track chips horizontal (Tecnologia, Negocios, Innovacion, Liderazgo, Cultura)
- Agenda card 60% width (cap 920px) alineada izquierda
- Detail panel floating absolute right cuando session selected
- Empty hint cuando no hay session selected

**SessionCard 1:1 patron app movil:**
- Layout `[time-col 52px][card-col]` con timeline vertical line (`<View>`, no `::before`)
- Top row: badges (LIVE/check/Track) + heart top-right
- Info dimmed si past, title line-through si past
- Description SOLO live (todas las cards mismo tamaño en upcoming)
- Location row + speakers stack (border accent solo si live)
- Action row con border-top: Calendario / Evaluar (gold) / UNIRTE / Ver grabacion
- Heart particle anim (6 particles + ring + scale-pop)

**Detail panel completo (session/[id].tsx fidel):**
- Badges row LIVE + Track
- Title 26pt PJS 800
- Stars rated si finished + rated + "Tu evaluacion"
- Meta card (date + time + location + capacity) 4 rows
- Action grid 2-3 cols (Favorita/Calendario/Evaluar)
- Stream button full width (UNIRTE accent live / Ver grabacion glass past)
- Section "Acerca de"
- Section "Asistencia" (avatar stack + count + "Ver quien va y conectar")
- Sub-panel attendees (3er nivel) con backdrop blur + lista CTA "Conectar"
- Section Speakers + cards con foto 42 + name + role · org + chevron + helper "Toca un speaker para ver su perfil"

**Lux mode** (replica `theme-lux.ts` exacto):
- Cool gray base #F6F8FA (NO beige)
- Cards bg #FFFFFF puras con multi-layer shadows (sm/md/lg/xl con opacity 0.06-0.14)
- Sin hover bg crema — solo `transform: translateY(-1px)` + shadow-md
- Borders rgba(0,0,0,0.08 / 0.14)
- Text tones #1A1B1E / #4A4B50 / #7C7D82 / #A8A9AE
- Track tags pastel app movil: tech #EFF6FF/#1D4ED8, business #F0FDF4/#166534, innovation #FFF7ED/#C2410C, leadership #FDF2F8/#BE185D, culture #FDF4FF/#7E22CE
- Gold rating #8E8170 (luxSemantic.gold)

**Microinteracciones (haptics visuales):**
- :active scale 0.985 (cards), 0.94 (daypills/action-btns), 0.96 (chips), 0.97 (tabs), 0.92 (tools), 0.88 (heart)
- Heart particle animation: 6 particles + ring + heart-pop
- Toast notifications: favorite/calendar/success/info con icono color-coded, anim spring-soft fade+translate auto-dismiss 2.4s

**Animaciones panel detail (W0-spatial system):**
- Entrada (cerrado→abierto): translateX(80px→0) en 480ms spring-snap
- Cambio entre sesiones: slide-out izq 260ms + slide-in der 480ms (sin reanimar lista)
- Sub-panel attendees: scale .94→1 + translateY 380ms spring-snap

**Tweaks panel:** Noir/Lux toggle + 5 swatches accent (lime/amber/rose/violet/cyan). Re-aplica accent al cambiar tema (corrige opacity de accent-soft/glow segun modo).

**Selected sin glow:** sin outline ni barra accent ni shadow tinted. El detail panel a la derecha indica seleccion. Replica app movil donde no hay selected state visual.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- `W.0-spatial-ui.md`
- App movil: `hooks/useAgenda.ts` + `screens/agenda/` — patron validado

---

## Alcance

1. Lista completa de sesiones del evento
2. Filtros: dia, track (categoria), tipo (keynote/workshop/panel/etc.), search
3. Favoritos (heart) — tabs "Todas" / "Mi Agenda"
4. **Detector conflictos**: warning si tiene 2 favoritas en mismo horario
5. Detalle de sesion (panel secundario o modal)
6. Si live: CTA "Unirse" abre Streaming W.4
7. **Lifecycle states**: original / adjusted (organizador cambio hora) / cancelled — visualizacion clara con badges
8. **Room check-in**: para sesiones presenciales (silent disco, salas con capacity), boton check-in cuando user esta cerca/en horario
9. **.ics download**: agregar sesion individual o "Mi Agenda" completa al calendario externo
10. **Ratings post-sesion**: cuando sesion termina y user asistio, prompt "Califica esta sesion" (estrella + comentario opcional)
11. **Recordatorio**: opcion "Recordarme 10min antes" → push notification (si W.10 push activo)
12. **Session-specific chat** vs Q&A global: chat de sesion es distinto al Q&A (chat = mensajes asistentes, Q&A = preguntas a moderador/speaker)
13. Sincronizacion RT cuando organizador edita sesion (W.11)

---

## Refs visuales

- App movil agenda (`features/Screenshot 2026-...`)
- `design/showcase-onboarding-v6.html` agenda window — concepto tabs Dia 1 / Dia 2 / Mi Agenda

---

## Endpoints (verificar)

- `GET /api/v1/event/{id}/sessions` — lista completa, filtros via query
- `GET /api/v1/event/{id}/sessions/{sessionId}` — detalle
- `POST /api/v1/event/{id}/sessions/{sessionId}/favorite` — toggle favorito
- `GET /api/v1/event/{id}/tracks` — lista tracks/categorias
- `POST /api/v1/sessions/{id}/room-checkin` — entrar al room (silent disco / sala fisica)
- `POST /api/v1/sessions/{id}/room-checkout` — salir del room
- `GET /api/v1/event/{id}/sessions/{sessionId}/ics` — download .ics individual
- `GET /api/v1/event/{id}/my-agenda/ics` — download .ics de Mi Agenda completa
- `POST /api/v1/sessions/{id}/rating` — rating post-sesion (estrella + comentario)
- `POST /api/v1/sessions/{id}/reminder` — programar recordatorio
- `GET /api/v1/sessions/{id}/messages` — chat session-specific (distinto a Q&A)

---

## Fase 0 — Setup (~30min) — 0/3

### 0.1 Hooks — 0/3
- [ ] `useAgenda(eventId, filters)` — TanStack Query con filters como key
- [ ] `useSession(sessionId)` — detalle
- [ ] `useFavorite(sessionId)` — mutation toggle

---

## Fase 1 — Lista de sesiones (~2h) — 0/5

### 1.1 Componente — 0/3
- [ ] `<AgendaList />` con grupos por dia (sticky headers)
- [ ] `<SessionRow />` con time + bar color (track) + nombre + speaker preview + heart
- [ ] Click row → abre `<SessionDetail />` panel secundario o modal

### 1.2 Estados — 0/2
- [ ] Live: indicador rojo pulsante + CTA "Unirse"
- [ ] Past: opacity reducida + "Ver grabacion" si hay
- [ ] Future: countdown si <1h

---

## Fase 2 — Filtros (~1.5h) — 0/4

### 2.1 Filtros UI — 0/3
- [ ] Tabs por dia (Dia 1, Dia 2, Mi Agenda)
- [ ] Pills filtro por track (multi-select)
- [ ] Search input con debounce 300ms

### 2.2 URL state — 0/1
- [ ] Filtros persistidos en URL (`?day=1&track=tech&q=ai`) para shareable links

---

## Fase 3 — Favoritos (~1h) — 0/3

### 3.1 Toggle — 0/2
- [ ] Click heart → optimistic update + mutation
- [ ] Si error → revert + toast error

### 3.2 Tab "Mi Agenda" — 0/1
- [ ] Filtra solo favoritos del usuario actual

---

## Fase 4 — Detalle de sesion (~1.5h) — 0/4

### 4.1 Layout — 0/2
- [ ] Desktop: panel secundario (40% width) cuando se abre desde lista
- [ ] Mobile: full screen overlay con back button

### 4.2 Contenido — 0/2
- [ ] Imagen sesion + titulo + speakers + tags + descripcion
- [ ] CTAs: "Unirse" si live, "Agregar a calendario" (.ics), "Compartir"

---

## Fase 4.5 — Lifecycle states + conflictos (~1h) — 0/4

### 4.5.1 Lifecycle badges — 0/2
- [ ] `<SessionLifecycleBadge />` con 3 estados: ORIGINAL (sin badge) / **AJUSTADA** (badge amarillo + tooltip "Hora cambio") / **CANCELADA** (badge rojo + tachada)
- [ ] Si AJUSTADA: mostrar `original_start_time` y `adjusted_start_time` en detalle

### 4.5.2 Detector conflictos — 0/2
- [ ] Si user tiene 2 favoritas con overlap de tiempo → warning amarillo en card + en Mi Agenda tab
- [ ] Modal "Tienes conflicto entre estas sesiones, cual prefieres?"

---

## Fase 4.6 — Room check-in + .ics + ratings + recordatorio (~2h) — 0/8

### 4.6.1 Room check-in — 0/3
- [ ] Si sesion tiene `room_id` y user en horario activo → boton "Check-in al room" en detalle
- [ ] Click → mutation room-checkin → mostrar "Estas en {room_name}" + boton checkout
- [ ] Si capacity llena → mostrar "Room lleno, espera tu turno" (queue)

### 4.6.2 .ics download — 0/2
- [ ] Boton "Agregar a calendario" en detalle sesion → download .ics individual
- [ ] Boton "Descargar Mi Agenda" en tab Mi Agenda → .ics completo

### 4.6.3 Rating post-sesion — 0/2
- [ ] Cuando sesion termina y user asistio → prompt persistente en tab Mi Agenda
- [ ] Modal: estrella 1-5 + comentario opcional 280 chars + submit

### 4.6.4 Recordatorio — 0/1
- [ ] Toggle "Recordarme 10min antes" en detalle → mutation programar push (W.10)

---

## Fase 4.7 — Session-specific chat (~30min) — 0/2

### 4.7.1 Distincion Q&A vs Chat — 0/2
- [ ] En streaming W.4 hay tabs: Q&A (preguntas estructuradas) + Chat (mensajes libres)
- [ ] En agenda detalle, link "Ver chat de la sesion" → abre W.4 chat tab directamente

---

## Fase 5 — Real-time (~30min) — 0/2

### 5.1 Socket events — 0/2
- [ ] `session.updated` → invalidate query de sesion + lista
- [ ] `session.cancelled` → mostrar toast + remover de lista
- [ ] (Implementacion completa en W.11, aqui solo subscribe basico)

---

## Fase 6 — QA + tests (~1.5h) — 0/4

### 6.1 Vitest — 0/2
- [ ] `useAgenda` con filters
- [ ] Optimistic update favoritos

### 6.2 Playwright — 0/2
- [ ] Happy path: filtrar por dia + track + favoritar + ver detalle
- [ ] Edge case: search sin resultados muestra empty correcto

---

## Edge cases

- [ ] Sesion sin imagen → placeholder generico (avatar speaker o color track)
- [ ] Sesion cancelada → mostrar tachada con razon
- [ ] Conflicto agenda (2 favoritas en mismo horario) → indicador warning + modal
- [ ] Track sin sesiones → no mostrar pill filtro
- [ ] Search con 0 caracteres → reset filtros texto pero mantiene dia/track
- [ ] User no logged → favoritar muestra modal "Inicia sesion para favoritar"
- [ ] Sesion live > duration esperada → mantiene "EN VIVO" hasta que organizador la cierre
- [ ] Sesion AJUSTADA pero user ya tenia recordatorio → reagendar push automaticamente
- [ ] Sesion CANCELADA con asistentes → notif RT + remover de Mi Agenda
- [ ] Room checkin sin estar en horario → error "Solo puedes ingresar dentro del horario"
- [ ] Room queue: user en cola, otro sale → notif "Tu turno disponible"
- [ ] .ics descarga sin fav → permitir solo si esta en Mi Agenda o la propia sesion
- [ ] Rating con sesion no asistida → no mostrar prompt
- [ ] Rating ya enviado → muestra rating actual readonly (UNIQUE constraint)
- [ ] Recordatorio sin permission push → mostrar warning "Activa notificaciones primero"

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports
- [ ] Lighthouse OK
- [ ] Commit DaVinci + memoria + PENDIENTES.md
