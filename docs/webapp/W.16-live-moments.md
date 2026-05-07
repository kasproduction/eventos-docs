# W.16 — Live Moments (subset web)

> Experiencias en vivo durante el evento. **Subset web** — solo features que tienen sentido en pantalla grande/desktop. Mobile-first features (slot machine, spin tap-tap) se quedan solo en app movil.
>
> **Estimacion:** ~5h (reducida de 6h tras audit backend — eliminacion de mocks).
> **Dependencias:** W.0, W.1, W.4 (Streaming context), W.6 (Photos viven en Photobooth), W.11 (RT).
> **Estado:** Pendiente — backend audit completado 2026-05-07.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- App movil: `components/screens/TriviaPanel.tsx`, `screens/live-moments/`
- `docs/roadmaps/ROADMAP-LIVE-MOMENTS.md` — fuente de verdad backend
- Backend: `app/Http/Controllers/Api/V1/Admin/GameController.php`, `EventPhotoController.php`
- `eventos-socket/src/types.ts` — listar `DisplayProjectPayload`
- Memorias: `project_live_moments_notes.md`, `project_session_20260423.md` (Trivia Kahoot), `project_session_20260422c.md` (Sorteo Ceremony GSAP)

---

## Drift corregido (2026-05-07) — arquitectura mal entendida

Version previa describia un sistema REST tradicional con endpoints `/sessions/{id}/trivia/active`, `/event/{id}/sorteo/active`, `/photo-contest/{id}/vote`, `/me/golden-tickets` y eventos socket `trivia.question.activated`, `sorteo.winner.revealed`, `photo.contest.vote.updated`, `golden.ticket.awarded`. **Nada de eso existe**.

La arquitectura real es:

1. **Modelo unificado `LiveGame`**: trivia, spin wheel, jackpot, sorteo son todos del mismo modelo, distinguidos por `type` (`'trivia'`, `'spin'`, `'jackpot'`).
2. **Control desde Mission Control admin**: el organizador maneja el juego desde Filament/MC con endpoints `/admin/games/{id}/launch|spin|draw|next-question|close-round`.
3. **Broadcast unificado via `display:project`**: la webapp y app movil reciben el evento socket `display:project` con `type:'game_trivia'|'game_spin'|'game_jackpot'` para mostrar overlay/panel adecuado.
4. **Concurso de fotos != live moment**: las fotos viven en `EventPhotoController` (likes simples, sin "vote"). El "concurso" es la vista filtrada `/photos/contest`, sin endpoints de winner/contest socket.
5. **Golden Ticket**: NO existe modelo dedicado en backend. La forma actual es enviar un `Announcement` privado via socket `announcement:new` con role/target especifico, y opcionalmente un Reward asociado.

---

## Alcance Fase 1 web

1. **Trivia Kahoot integrada en streaming W.4**: panel que monta cuando `display:project { type:'game_trivia' }` llega y `session.activePanel === 'trivia'`. Render pregunta + 4 opciones + countdown + leaderboard.
2. **Sorteo / Spin display ceremony**: cuando MC dispara `display:project { type:'game_spin' }`, modal full-screen ceremony con GSAP. Read-only.
3. **Jackpot display**: idem, `display:project { type:'game_jackpot' }`.
4. **Concurso de Fotos (vista)**: feed `/photos/contest` con likes simples (mismo endpoint que photobooth).
5. **Golden Ticket reveal**: si llega `announcement:new` con target privado (role o attendee_id), mostrar modal full-screen con animacion premiada. La detencion del "ticket" es announcement-driven, no modelo dedicado.

---

## NO entra en webapp Fase 1 (mobile only o no existe)

- **Slot Machine** interactivo — experiencia tap-tap mobile-first
- **Spin Wheel** interactivo — el spin debe sentirse fisico
- **Vote concurso fotos**: NO existe endpoint de vote — solo `like/unlike` (mismo que wall photos). Si cliente lo pide, escalar a backend
- **`/me/golden-tickets` endpoint**: NO existe. Si se necesita historial, abrir issue backend

---

## Endpoints reales (verificados 2026-05-07)

### Games (Trivia / Spin / Jackpot — modelo unificado)
```
GET /api/v1/sessions/{sessionId}/game/active   (publico)
  → {data: GameResource | null}  // game activo en la sesion

POST /api/v1/games/{id}/join                    (sanctum)
  → {data: ...}  // unirse al pool (jackpot mainly)

POST /api/v1/games/{id}/answer                  (sanctum)
  body: {answer: string|number, time_remaining_ms?: number}
  → {data: {correct: bool, points_earned: number}}
```

### Admin (Mission Control — fuera de scope web cliente, pero util saberlos)
```
POST /admin/games                          // crear
POST /admin/games/{id}/launch              // iniciar
POST /admin/games/{id}/spin                // spin wheel
POST /admin/games/{id}/draw                // sortear ganador
POST /admin/games/{id}/next-question       // siguiente trivia
POST /admin/games/{id}/close-round         // cerrar ronda
GET  /admin/games/{id}/results             // resultados
GET  /admin/games/{id}/export              // CSV
GET  /admin/sessions/{sessionId}/games     // games de la sesion
```

### Concurso fotos (compartido con W.6 Social Wall / Photobooth)
```
GET /api/v1/events/{eventId}/photos                        // todas (publico)
GET /api/v1/events/{eventId}/photos/mine                   // mis fotos
GET /api/v1/events/{eventId}/photos/contest                // concurso (filtro)
POST /api/v1/events/{eventId}/photos                       // subir
POST /api/v1/events/{eventId}/photos/{photoId}/like        // like
DELETE /api/v1/events/{eventId}/photos/{photoId}/like      // unlike
```

NO existe `POST /photo-contest/{photoId}/vote`. El "voto" es el like.

### Trivia gamification de stand (no es Kahoot live — es quiz pasivo)
```
POST /api/v1/events/{eventId}/trivia/{triviaId}/answer
  → asigna puntos pero no es Kahoot. Vive en GamificationController.
```
Solo mencionado para no confundir con `POST /games/{id}/answer` (que es el de Kahoot live).

---

## Eventos socket reales

`DisplayProjectPayload.type` valido: `'poll_results' | 'question' | 'game_spin' | 'game_trivia' | 'game_jackpot'`.

| Evento | Payload | Uso en W.16 |
|---|---|---|
| `display:project` | `{sessionId, type, gameData?, data?}` | Mission Control inicia/proyecta game o spin. La webapp escucha y monta overlay |
| `display:stop` | `{sessionId}` | MC detiene proyeccion |
| `announcement:new` | `AnnouncementPayload` | Si llega un announcement marcado como golden ticket (rol/target privado), trigger modal celebracion |
| `data:invalidate` | `{entity:'leaderboard'\|'points'}` | Refrescar leaderboard del juego cuando se acumulan puntos |

NO existen (NO usar):
- ~~`trivia.question.activated/closed/leaderboard.updated`~~
- ~~`sorteo.winner.revealed/ceremony.started`~~
- ~~`photo.contest.new_photo/vote.updated/winner`~~
- ~~`golden.ticket.awarded`~~ — usa `announcement:new`
- ~~`spin.wheel.result`~~ — usa `display:project { type:'game_spin' }`

---

## Refs visuales

- App movil sorteo ceremony GSAP — animaciones premiadas
- Memoria: `project_session_20260422c.md` (Sorteo Ceremony)

---

## Fase 0 — Hooks (~30min) — 0/4

- [ ] `useActiveGame(sessionId)` — `GET /sessions/{id}/game/active` (cache 30s)
- [ ] `useAnswerGame()` — `POST /games/{id}/answer` con bonus segun tiempo
- [ ] `usePhotoContest(eventId)` — `GET /events/{id}/photos/contest`
- [ ] `usePhotoLike()` — `POST/DELETE /events/{id}/photos/{photoId}/like`

---

## Fase 1 — Trivia Kahoot panel (~2h) — 0/6

### 1.1 Layout panel — 0/2
- [ ] `<TriviaPanel sessionId />` integrado en streaming W.4 cuando `activePanel === 'trivia'`
- [ ] Renderiza desde `useActiveGame(sessionId)` + `display:project { type:'game_trivia' }`

### 1.2 UX — 0/3
- [ ] Pregunta + 4 opciones + countdown bar
- [ ] Click opcion → mutation answer → mostrar correcto/incorrecto inline
- [ ] Bonus points: enviar `time_remaining_ms` para que backend calcule rapidez
- [ ] Lock answer 1 sec antes de fin para evitar mutaciones tardias

### 1.3 Leaderboard trivia — 0/1
- [ ] Top 10 con avatar + nombre + puntos al cerrar pregunta. Llega via `data:invalidate {entity:'leaderboard'}` o el siguiente `display:project` lo trae en `gameData`

---

## Fase 2 — Sorteo / Spin / Jackpot ceremony display (~1.5h) — 0/4

### 2.1 Modo ceremony — 0/2
- [ ] Listener `display:project` filtrar `type:'game_spin'` o `type:'game_jackpot'` → modal full screen modo "ceremonia"
- [ ] Animacion GSAP: nombres rotando + reveal + confetti (con `prefers-reduced-motion` fallback)

### 2.2 Display ganadores — 0/2
- [ ] El `gameData` trae winner(s) cuando MC los anuncia
- [ ] Modal queda abierto hasta `display:stop` (organizador cierra desde MC)

---

## Fase 3 — Concurso Fotos (~1h) — 0/4

### 3.1 Feed — 0/2
- [ ] `<PhotoContestFeed />` grid usando `usePhotoContest(eventId)`
- [ ] Cada foto: imagen + autor + count likes + boton heart

### 3.2 Like — 0/1
- [ ] Click heart → `POST .../like` (optimistic + revert si falla)
- [ ] Si ya like → DELETE
- Sin RT — no hay socket de likes

### 3.3 Ranking — 0/1
- [ ] Tab "Top votadas" ordena por count likes
- [ ] Si concurso cerrado: badge "Cerrado" + indicar ganadores anunciados via announcement (si organizador uso esa via)

---

## Fase 4 — Golden Ticket reveal (~30min) — 0/2

### 4.1 Detection — 0/2
- [ ] Listener `announcement:new` → si el announcement tiene flag/marker indicando golden ticket (acordar con backend, ej. roles array contiene `'golden_ticket'` o key `is_golden`), trigger modal full-screen
- [ ] Modal: confetti + sonido (con mute toggle) + mensaje del announcement + image_url + action_url

NOTA: Si backend no provee marker para golden tickets, hay 2 opciones:
1. Crear convencion: announcements con `roles: ['golden_ticket']` → tratamiento especial
2. Pedir backend que agregue campo `kind: 'standard' | 'golden_ticket'` en AnnouncementResource

Documentar la decision aqui antes de implementar.

---

## Fase 5 — Tests (~30min) — 0/3

### 5.1 Vitest — 0/1
- [ ] Trivia: lock answer 1s antes de fin

### 5.2 Playwright — 0/2
- [ ] Happy path: trivia activa → responder + ver ranking
- [ ] Edge case: announcement con marker golden ticket → modal aparece + confetti

---

## Edge cases

- [ ] Trivia sin tiempo restante → no permitir responder (lock automatico cliente)
- [ ] User responde 2 veces rapido → backend idempotente, primer answer wins
- [ ] Sorteo ceremony cerrada por organizador antes de tiempo → modal cierra suave en `display:stop`
- [ ] Concurso fotos sin fotos → empty state "Sube tu foto en Social Wall"
- [ ] Golden ticket multi-tab → solo 1 tab muestra modal (BroadcastChannel API)
- [ ] Spin wheel sin internet → modal no se monta (socket disconnect)
- [ ] User da like a foto propia → backend permite (verificar) o rechaza
- [ ] `prefers-reduced-motion` → confetti version sutil sin particulas
- [ ] `display:project` llega antes de cargar la sesion → buffer hasta que monte

---

## Acceso desde la app

- Trivia: aparece automaticamente cuando MC la activa en sesion live (integrado en streaming W.4)
- Sorteo Ceremony / Spin / Jackpot: modal full screen RT
- Concurso Fotos: pill bar dropdown "Mas..." → "Concurso Fotos"
- Golden Ticket: modal RT cuando llega `announcement:new` con marker

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports (ceremony y golden ticket sobre todo en desktop)
- [ ] Lighthouse OK
- [ ] Decision golden ticket marker tomada con backend (Fase 4)
- [ ] Commit DaVinci + memoria + PENDIENTES.md
