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

## Endpoints (verificados 2026-05-06)

**Wireados al backend (proxy Next con cookie httpOnly):**
- ✅ `GET /api/v1/events/{id}/agenda` — lista agrupada por dia + `is_favorite` + `favorites_count`
- ✅ `POST /api/v1/events/{id}/agenda/{sId}/favorite` — toggle favorito (proxy: `/api/agenda/{id}/favorite/{sId}`)
- ✅ `GET /api/v1/events/{id}/sessions/{sId}/calendar.ics` — download ICS (proxy: `/api/agenda/{id}/sessions/{sId}/calendar`)
- ✅ `POST /api/v1/events/{id}/sessions/{sId}/rate` — rating (proxy: `/api/agenda/{id}/sessions/{sId}/rate`). Backend retorna 409 si ya calificado
- ✅ `GET /api/v1/events/{id}/my-ratings` — map sessionId → rating (proxy: `/api/agenda/{id}/my-ratings`)

**No wireados (diferidos a otros modulos):**
- 🔜 `POST /sessions/{id}/room-checkin` / `room-checkout` — diferido (UX no validada en webapp)
- 🔜 `POST /sessions/{id}/reminder` — W.10 push notif setup
- 🔜 `GET /sessions/{id}/chat/messages` — W.4 streaming (vive con el player)
- ❌ `/my-agenda/ics` (bulk) — endpoint no existe en backend
- ❌ `/sessions/{id}/attendees` — endpoint no existe; seccion "Asistencia" oculta hasta W.8

Doc maestro de TODOS los endpoints: `BACKEND-API-MAP.md`.

---

## Fase 0 — Setup — 1/1 ✅

### 0.1 Tipos + fetcher SSR
- [x] `lib/types/agenda.ts` (espejo `EventSessionResource`)
- [x] `lib/agenda.ts` (`fetchAgenda` server-side con bearer cookie)
- [x] `lib/agendaClient.ts` (mutations client-side: favorite, rate, my-ratings, .ics)

> Decision: NO usamos TanStack `useAgenda` hook por ahora. Datos vienen via SSR + state local. Cuando entre W.11 socket invalidation, migrar a TanStack para reactividad RT.

---

## Fase 1 — Lista de sesiones — 5/5 ✅

### 1.1 Componente — 3/3
- [x] `<SessionList />` agrupa por dia + day-slide animation al cambiar dia
- [x] `<SessionCard />` 1:1 patron app movil: time-col 52px + card + heart top-right + action row con border-top
- [x] Click card → abre `<DetailPanel />` floating right (no replace, coexisten)

### 1.2 Estados — 2/2
- [x] Live: badge `EN VIVO` con pulse + CTA `UNIRTE` accent + border-color tinted
- [x] Past: title `text-decoration: line-through` + opacity 0.45 + sin description
- [x] Upcoming: layout neutral, sin countdown (descartado por ruido visual)

---

## Fase 2 — Filtros — 3/4 (URL state diferido)

### 2.1 Filtros UI — 3/3
- [x] Tabs Agenda / Mi Agenda (no por dia — los dias son DayStrip aparte)
- [x] Pills filtro por track (multi-select via Set), `<ChipFilters />`
- [x] Search input expanded inline (sin debounce — filtra en cliente, OK con poca data)

### 2.2 URL state — 0/1 (diferido)
- [ ] Filtros persistidos en URL → diferido a polish W.12 (no es bloqueante)

---

## Fase 3 — Favoritos — 3/3 ✅

### 3.1 Toggle — 2/2
- [x] Click heart → optimistic update local + POST al backend
- [x] Si error backend → revert state + `lumina.error` toast

### 3.2 Tab "Mi Agenda" — 1/1
- [x] Filtra `s.is_favorite === true` localmente (datos ya vienen con flag del backend)

---

## Fase 4 — Detalle de sesion — 4/4 ✅

### 4.1 Layout — 2/2
- [x] Desktop: panel floating right 320-520px width, slide-in 480ms spring + swap-out 260ms
- [x] Mobile: heredado del shell W.0 (mobile redirige a app movil — no aplica detail panel)

### 4.2 Contenido — 2/2
- [x] Badges (LIVE/Track) + title + meta (date/time/location/capacity/interesados) + actions + about + speakers + asistencia (oculta)
- [x] CTAs: `UNIRTE` accent si live (placeholder W.4), `Calendario` descarga .ics real, `Ver grabacion` glass si recording_url, `Favorita` con cambio de fill

---

## Fase 4.5 — Lifecycle + conflictos — 0/4 (diferido a W.11)

### 4.5.1 Lifecycle badges — 0/2 (diferido)
- [ ] `<SessionLifecycleBadge />` ORIGINAL/AJUSTADA/CANCELADA → diferido. Backend YA emite eventos `session:cancelled`, `agenda:delayed` via socket. Pendiente wirear cuando entre W.11.

### 4.5.2 Detector conflictos — 0/2 (diferido)
- [ ] Conflict detection 2 favoritas overlap → diferido. Calculo puramente cliente (sin backend), mover a polish W.12.

---

## Fase 4.6 — Room check-in + .ics + ratings + recordatorio — 4/8

### 4.6.1 Room check-in — 0/3 (diferido)
- [ ] Boton "Check-in al room" → diferido. UX no validada en webapp; quizas no tiene sentido (asistente virtual sin presencia fisica).
- [ ] Mutation room-checkin/checkout → diferido a sesion dedicada
- [ ] Queue si capacity llena → diferido

### 4.6.2 .ics download — 1/2
- [x] Boton "Calendario" en card upcoming + DetailPanel → `downloadSessionIcs()` via anchor, respeta Content-Disposition del backend
- [ ] "Descargar Mi Agenda" completa (.ics bulk) → endpoint `/my-agenda/ics` NO existe en backend; diferido o agregar al backend

### 4.6.3 Rating post-sesion — 2/2 ✅
- [x] Cards `past` sin rating → boton "Evaluar" gold abre `<RatingModal />`
- [x] Modal con 5 estrellas (gold pop) + comment opcional 1000 chars + submit POST. Si ya calificado: estrellas readonly en card, boton oculto en detail (backend rechaza re-rate con 409)

### 4.6.4 Recordatorio — 0/1 (diferido a W.10)
- [ ] Toggle "Recordarme 10min antes" → diferido a W.10 (necesita push notif setup)

---

## Fase 4.7 — Session-specific chat — 0/2 (diferido a W.4)

### 4.7.1 Distincion Q&A vs Chat — 0/2 (diferido)
- [ ] Tabs Q&A + Chat en streaming → vive en W.4 (no tiene sentido tenerlo en agenda)
- [ ] Link "Ver chat" desde detalle → diferido a W.4

---

## Fase 5 — Real-time — 0/2 (diferido a W.11)

### 5.1 Socket events — 0/2 (diferido)
- [ ] `session:started/ended/cancelled/delayed` + `agenda:updated` → invalidate query → diferido a W.11. Backend YA emite todos estos eventos a la room `event:{eventId}` via `broadcastSessionLifecycle()`.

---

## Fase 6 — QA + tests — 2/4

### 6.1 Vitest — 1/2 ✅
- [x] `tests/components/agenda/agendaDerive.test.ts` — 33 tests cubriendo buildDayStrip, deriveUiState, trackSlug, formatTime, collectTracks, totalSessions, firstDayWithSessions
- [ ] `useAgenda` hook con filters → no aplica (no creamos hook TanStack en F0)

### 6.2 Playwright — 0/2 (pendiente)
- [ ] Happy path: filtrar dia + track + favoritar + ver detalle → pendiente sesion E2E dedicada
- [ ] Edge case: search sin resultados → pendiente

---

## Edge cases

- [x] Sesion sin imagen → no aplica (cards no tienen imagen, solo speakers stack)
- [x] Sesion cancelada → `deriveUiState` retorna `past` + line-through del title
- [ ] Conflicto agenda (2 favoritas overlap) → diferido a polish W.12
- [x] Track sin sesiones → `collectTracks` solo devuelve tracks que tienen sesiones
- [x] Search con 0 caracteres → no filtra por search, mantiene dia/track
- [x] User no logged → middleware `proxy.ts` redirige a /login antes de llegar a /agenda
- [x] Sesion live > duration esperada → `deriveUiState` respeta `status: "live"` del backend hasta que organizador la cierre
- [ ] Sesion AJUSTADA con recordatorio activo → diferido (recordatorios no implementados)
- [ ] Sesion CANCELADA con favoritos → diferido a W.11 (socket invalidation)
- [x] Room checkin sin horario → no aplica (room check-in diferido)
- [x] Room queue → no aplica
- [x] .ics descarga sin fav → permitido siempre (cualquier sesion puede descargar su .ics)
- [x] Rating con sesion no asistida → permitido (backend valida via attendee, sin chequeo de asistencia)
- [x] Rating ya enviado → estrellas readonly + boton oculto (backend retorna 409 — manejado correctamente)
- [ ] Recordatorio sin permission push → diferido a W.10

---

## Cierre — 3/4

- [x] **Tests verde:** Vitest 55/55 + typecheck + lint OK
- [ ] **Validado 3 viewports:** pendiente sesion QA browser real con `W.3-QA-CHECKLIST.md`
- [ ] **Lighthouse OK:** pendiente medicion en build de produccion
- [x] **Commit DaVinci + memoria + PENDIENTES.md:** webapp `00ac800`, docs `1eefe0f`, memoria `project_w3_agenda_react.md` + `project_backend_api_map.md`, PENDIENTES marca W.3 done, COMPLETADO con entrada nueva
