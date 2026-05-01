# W.16 — Live Moments (subset web)

> Experiencias en vivo durante el evento. **Subset web** — solo features que tienen sentido en pantalla grande/desktop. Mobile-first features (slot machine, spin wheel) se quedan solo en app movil.
>
> **Estimacion:** ~6h.
> **Dependencias:** W.0, W.1, W.4 (Streaming context), W.11 (RT).
> **Estado:** Pendiente.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- App movil: `screens/live-moments/` (Live Moments completo)
- `docs/ROADMAP-LIVE-MOMENTS.md` — fuente de verdad backend
- Memorias: `project_live_moments_notes.md`, `project_session_20260423.md` (Trivia Kahoot), `project_session_20260423b.md` (Live Moments F5), `project_session_20260422c.md` (Sorteo Ceremony GSAP), `project_session_20260424c.md` (Concurso fotos + Golden Ticket), `project_session_20260422e.md` (Golden Ticket F2.7)

---

## Alcance Fase 1 web (subset)

1. **Trivia Kahoot-style en sesion**: integrado en streaming W.4, juego de preguntas con tiempo + ranking + bonus points
2. **Sorteo Ceremony display**: pantalla full screen ceremonia de premios con animaciones GSAP, ganadores anunciados con drama
3. **Concurso de Fotos display**: feed publico del concurso con ranking + ganadores + leaderboard de votaciones
4. **Golden Ticket reveal**: cuando user gana golden ticket, modal full-screen con animacion premiada + premio
5. **Spin Wheel**: solo display readonly (ver wheel girando con resultados RT) — NO interactivo en web
6. **Slot Machine**: NO entra en web (es experiencia tap-tap mobile-first)

---

## NO entra en webapp Fase 1 (queda movil-only)

- **Slot Machine** interactivo — experiencia tap-tap mobile-first
- **Spin Wheel** interactivo — el spin debe sentirse fisico, mejor en mobile (touch)

Web puede mostrar **resultado** de estos (ej. ganador del spin wheel via socket event) pero no permite jugarlos.

---

## Refs visuales

- App movil sorteo ceremony GSAP — animaciones premiadas
- Memoria: `project_session_20260422c.md` (Sorteo Ceremony)

---

## Endpoints (verificar)

- `GET /api/v1/sessions/{id}/trivia/active` — trivia activa
- `POST /api/v1/trivia/{id}/answer` — responder pregunta trivia
- `GET /api/v1/sessions/{id}/trivia/leaderboard` — ranking
- `GET /api/v1/event/{id}/sorteo/active` — sorteo activo
- `GET /api/v1/event/{id}/photo-contest` — concurso fotos
- `POST /api/v1/photo-contest/{photoId}/vote` — votar foto
- `GET /api/v1/me/golden-tickets` — mis golden tickets

Socket events:
- `trivia.question.activated`, `trivia.question.closed`, `trivia.leaderboard.updated`
- `sorteo.winner.revealed`, `sorteo.ceremony.started`
- `photo.contest.new_photo`, `photo.contest.vote.updated`, `photo.contest.winner`
- `golden.ticket.awarded` (RT toast spectacular)
- `spin.wheel.result` — resultado de spin (si queremos mostrar en web)

---

## Fase 0 — Hooks (~30min) — 0/4

- [ ] `useTrivia(sessionId)` — pregunta activa + responder + leaderboard
- [ ] `useSorteo(eventId)` — sorteo activo + ganadores
- [ ] `usePhotoContest(eventId)` — feed + votar
- [ ] `useGoldenTickets()` — mis tickets

---

## Fase 1 — Trivia Kahoot (~2h) — 0/6

### 1.1 Layout panel — 0/2
- [ ] `<TriviaPanel />` integrado en streaming W.4 cuando trivia activa
- [ ] Pregunta + 4 opciones + countdown timer + animacion al responder

### 1.2 Logica — 0/3
- [ ] Click opcion → mutation answer → mostrar correcto/incorrecto
- [ ] Bonus points si rapido (mas tiempo restante = mas puntos)
- [ ] Lock answer 1 sec antes de fin para evitar mutaciones tardias

### 1.3 Leaderboard trivia — 0/1
- [ ] Top 10 con avatar + nombre + puntos al final de cada pregunta

---

## Fase 2 — Sorteo Ceremony display (~1.5h) — 0/4

### 2.1 Modo ceremony — 0/2
- [ ] Cuando organizador inicia sorteo → modal full screen modo "ceremonia"
- [ ] Animacion GSAP: nombres rotando + reveal + confetti

### 2.2 Display ganadores — 0/2
- [ ] Lista ganadores con avatar + premio
- [ ] Mantiene ceremony abierta hasta que organizador la cierra

---

## Fase 3 — Concurso Fotos (~1.5h) — 0/5

### 3.1 Feed — 0/2
- [ ] `<PhotoContestFeed />` grid de fotos del concurso
- [ ] Cada foto: imagen + autor + count votos + boton heart

### 3.2 Votar — 0/2
- [ ] Click heart → mutation vote (1 voto por user por foto)
- [ ] Optimistic + dedup socket

### 3.3 Ranking + ganadores — 0/1
- [ ] Tab "Top votadas" con podium top 3 + lista resto
- [ ] Si concurso cerrado: mostrar ganadores oficiales

---

## Fase 4 — Golden Ticket reveal (~30min) — 0/3

### 4.1 Trigger — 0/2
- [ ] Socket event `golden.ticket.awarded` → modal full screen con animacion
- [ ] Confetti + sonido (con mute toggle) + mensaje "Has ganado: {premio}"

### 4.2 Mis tickets — 0/1
- [ ] Pantalla en perfil (W.10) "Mis golden tickets" con lista de tickets ganados + estado canjeo

---

## Fase 5 — Spin Wheel display readonly (~30min) — 0/2

### 5.1 Display — 0/2
- [ ] Si organizador inicia spin desde admin → toast en webapp "Spin wheel girando..."
- [ ] Cuando termina: socket `spin.wheel.result` → toast con ganador

---

## Fase 6 — Tests (~30min) — 0/3

### 6.1 Vitest — 0/1
- [ ] Trivia: lock answer 1s antes de fin

### 6.2 Playwright — 0/2
- [ ] Happy path: trivia activa → responder + ver ranking
- [ ] Edge case: golden ticket awarded → modal aparece + confetti

---

## Edge cases

- [ ] Trivia sin tiempo restante → no permitir responder (lock automatico)
- [ ] User responde 2 veces rapido → solo cuenta primera (idempotencia backend)
- [ ] Sorteo ceremony cerrada por organizador antes de tiempo → modal cierra suave
- [ ] Concurso fotos sin fotos → empty state "Sube tu foto al social wall (W.6)"
- [ ] Golden ticket multi-tab → solo 1 tab muestra modal (broadcast channel)
- [ ] Spin wheel sin internet → no se muestra (socket disconnect)
- [ ] User vota foto propia → backend rechaza + toast
- [ ] Confetti animacion con `prefers-reduced-motion` → version sutil sin particulas

---

## Acceso desde la app

- Trivia: aparece automaticamente cuando organizador la activa en sesion live (integrado en streaming W.4)
- Sorteo Ceremony: modal full screen RT
- Concurso Fotos: pill bar dropdown "Mas..." → "Concurso Fotos"
- Golden Ticket: notification + modal RT cuando se gana

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports (ceremony y golden ticket sobre todo en desktop)
- [ ] Lighthouse OK
- [ ] Commit DaVinci + memoria + PENDIENTES.md
