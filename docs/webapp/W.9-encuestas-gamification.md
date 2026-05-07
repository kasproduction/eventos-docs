# W.9 — Encuestas + Gamification + Passport + Rewards

> Encuestas (live polls + surveys post-evento) + Leaderboard + Logros (acciones completadas — no badges separados) + Passport stamps + Rewards/Redemption. Engagement core.
>
> **Estimacion:** ~8h (reducida de 10h tras audit — sin badges/streak inventados).
> **Dependencias:** W.0, W.1.
> **Estado:** Pendiente — backend audit completado 2026-05-07.

**Sub-areas (tabs internos en pantalla "Engagement"):**
- **Tab 1: Encuestas** (polls fuera de sesion + surveys post-evento)
- **Tab 2: Leaderboard** + mi posicion + breakdown
- **Tab 3: Mis Logros** (`attendeeStatus.actions` — NO badges como entidad)
- **Tab 4: Passport** (stamps por visitar stands)
- **Tab 5: Rewards** (canjear puntos por premios)

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- App movil: `screens/encuestas/`, `screens/gamification/`
- Backend: `app/Services/PointsService.php` (modelo de datos real), `app/Http/Controllers/Api/V1/GamificationController.php`, `RewardController.php`
- Memorias: `project_s110_notes.md` (encuestas), `project_s119_s120_s121_notes.md` (gamification + passport)

---

## Drift corregido (2026-05-07)

Version previa documentaba endpoints + features que NO existen en backend:

- ~~`GET /event/{id}/me/gamification`~~ → real `GET /me/points?event_id={id}`
- ~~`GET /event/{id}/me/gamification/breakdown`~~ → ya viene en `attendeeStatus` (`total + actions[]`)
- ~~`GET /event/{id}/badges`~~ → **NO existe Badges como entidad**. El concepto real es "actions completadas" (cada accion del `gamification-config` con `earned > 0`). Renombrar tab "Badges" → "Mis Logros".
- ~~`GET /event/{id}/passport`~~ → real `GET /events/{id}/my-passport`
- ~~`GET /event/{id}/polls`~~ → real `GET /events/{id}/surveys` (polls de sesion son `/sessions/{id}/poll/active`)
- ~~Streak feature~~ → **NO existe** en backend. Eliminar Tab/Fase. Si cliente lo pide, abrir issue backend.
- ~~Sockets `points.awarded`, `leaderboard.updated`, `badge.unlocked`, `passport.stamp.new`, `streak.bonus.awarded`, `poll.activated`, `poll.results.updated`~~ → reales son `data:invalidate {entity:'points'|'leaderboard'|'passport'}` y `poll:new`/`poll:updated`/`poll:closed`. Toast "+X puntos" funciona via tracking local (computar diff antes/despues de invalidate) o pedir backend agregue evento dedicado.

---

## Endpoints reales (verificados 2026-05-07)

### Polls / Encuestas
```
GET  /api/v1/events/{eventId}/surveys                    // surveys del evento
GET  /api/v1/events/{eventId}/post-event-survey          // survey principal post-evento
POST /api/v1/polls/{poll}/vote                           // body: {option_id}
GET  /api/v1/sessions/{sessionId}/poll/active            // poll activo en sesion (W.4)
```

### Gamification
```
GET  /api/v1/events/{eventId}/leaderboard
  → {data: [{attendee_id, name, photo_url, total_points}], my_position, my_points}

GET  /api/v1/me/points?event_id={id}
  → {data: {total, actions: [{action, label, icon, points, earned, completed, daily_max}]}}

GET  /api/v1/events/{eventId}/gamification-config
  → {data: {actionKey: {enabled, points, label, icon, daily_max, roles?}}}

GET  /api/v1/events/{eventId}/gamification/rules
  → {data: ...}  // descripcion de reglas para mostrar al user

POST /api/v1/events/{eventId}/visit-stand/{sponsorId}
  → asigna puntos + passport stamp

POST /api/v1/events/{eventId}/trivia/{triviaId}/answer
  body: {answer_index: number}
  → quiz simple en stand (no Kahoot live)
```

### Passport
```
GET /api/v1/events/{eventId}/my-passport
  → {data: ...}  // stamps coleccionados (verificar shape exacto en PassportController)
```

### Rewards
```
GET  /api/v1/events/{eventId}/rewards
  → {data: [RewardResource[]]}

POST /api/v1/events/{eventId}/rewards/{rewardId}/redeem
  → {data: {redeemed_at}}

POST /api/v1/rewards/confirm
  body: {redemption_id}
  → {data: {message}}  // admin confirma entrega

GET /api/v1/me/redemptions
  → {data: [Redemption[]]}

GET /api/v1/me/prizes
  → {data: [Prize[]]}  // golden tickets + sorteos + redenciones
```

---

## Eventos socket reales (W.11)

| Evento | Uso |
|---|---|
| `poll:new` | Poll activado → invalidate lista |
| `poll:updated` | Resultados cambian → optimistic merge |
| `poll:closed` | Cerrado → switch a vista resultados |
| `data:invalidate {entity:'points'}` | Mis puntos cambiaron → invalidate `me/points` + computar diff para toast |
| `data:invalidate {entity:'leaderboard'}` | Top10 cambio → invalidate `leaderboard` |
| `data:invalidate {entity:'passport'}` | Nuevo stamp → invalidate `my-passport` |

NO existen como eventos dedicados: `points.awarded`, `leaderboard.updated`, `badge.unlocked`, `passport.stamp.new`, `streak.bonus.awarded`. Para toast "+X puntos por {action}":
- Cliente guarda `previousTotal` antes de invalidate
- Tras refetch, `newTotal - previousTotal = puntos ganados`
- Si `>0`, toast con animacion (no se sabe el `action` exacto sin backend, mostrar genérico "+X puntos")

---

## Refs visuales

- App movil gamification (`features/Screenshot 2026-...`)
- `design/showcase-onboarding-v6.html` `gam-wrap` — concepto leaderboard + stamps
- Memoria: `project_s119_s120_s121_notes.md`

---

## Fase 0 — Hooks (~30min) — 0/4

- [ ] `useSurveys(eventId)` — surveys del evento
- [ ] `useLeaderboard(eventId)` — top 50 + my_position + my_points
- [ ] `useMyPoints(eventId)` — total + actions[]
- [ ] `useGamificationConfig(eventId)` — para mostrar reglas

---

## Fase 1 — Encuestas / Surveys (~1.5h) — 0/4

### 1.1 Lista — 0/2
- [ ] `<SurveysList />` con filtros activas/cerradas
- [ ] Cada survey: titulo + tiempo restante o "Cerrada"

### 1.2 Votar — 0/2
- [ ] Click opcion → `POST /polls/{id}/vote {option_id}` → mostrar resultados
- [ ] Si ya voto: muestra resultado directo (`my_answers` no vacio)

---

## Fase 2 — Leaderboard (~1.5h) — 0/4

### 2.1 Tabla — 0/2
- [ ] `<LeaderboardTable />` top 50 (backend default), opcion "ver todo" si pedimos endpoint paginado
- [ ] Cada fila: posicion + avatar + nombre + total_points

### 2.2 Mi posicion — 0/2
- [ ] Sticky bar con `my_position + my_points`
- [ ] Si `my_position > 50` → mostrar "Estas en posicion #234" debajo del top 50

---

## Fase 3 — Mis Logros (NO Badges) (~1h) — 0/3

### 3.1 Grid — 0/2
- [ ] `<MisLogros />` itera sobre `attendeeStatus.actions[]`
- [ ] Cada item: icon + label + earned/possible + completed badge si `completed=true`
- [ ] Items con `completed=false` en grayscale

### 3.2 Detalle (modal) — 0/1
- [ ] Click → modal con descripcion del action + cuantos puntos da + `daily_max` si aplica + cuantas veces lo completaste

---

## Fase 4 — Passport stamps (~1.5h) — 0/4

### 4.1 Passport visual — 0/2
- [ ] `<Passport />` libreta visual con grid de stamps obtenidos via `GET /events/{id}/my-passport`
- [ ] Cada stamp: icono sponsor/stand + nombre + fecha desbloqueo

### 4.2 Trigger desbloqueo — 0/2
- [ ] `POST /events/{id}/visit-stand/{sponsorId}` se dispara desde:
  - mobile: scanner QR del stand (no aplica web)
  - web: visitar `<SponsorProfile>` (W.7) por X tiempo o click en CTA
- [ ] Socket `data:invalidate {entity:'passport'}` → invalidate `my-passport` → animacion stamp aparece + toast

---

## Fase 5 — Rewards/Redemption (~2h) — 0/5

### 5.1 Catalogo — 0/2
- [ ] `<RewardsCatalog />` grid usando `GET /events/{id}/rewards`
- [ ] Cada reward: imagen + nombre + costo en puntos + boton "Canjear"
- [ ] Si user no tiene puntos suficientes → boton deshabilitado con tooltip

### 5.2 Redeem — 0/2
- [ ] Click "Canjear" → modal confirm
- [ ] `POST /events/{id}/rewards/{rewardId}/redeem` → muestra `redeemed_at` + codigo + instrucciones

### 5.3 Mis redenciones + premios — 0/1
- [ ] Tab "Mis Premios" mezcla `GET /me/prizes` + `GET /me/redemptions`
- [ ] Cada item: tipo (golden_ticket / sorteo / canje) + estado + codigo

---

## Fase 6 — Toast +X puntos via diff (~30min) — 0/2

Como NO hay socket dedicado `points.awarded`:

- [ ] Hook `useTrackPointsDiff()` guarda `previousTotal` antes de cada `data:invalidate` con entity `'points'`
- [ ] Tras refetch, computa `delta = newTotal - previousTotal`
- [ ] Si `delta > 0` → toast Sonner "+{delta} puntos"
- [ ] Si delta>=10 (umbral) → animacion confetti sutil

---

## Fase 7 — Tests (~30min) — 0/3

### 7.1 Vitest — 0/1
- [ ] Diff calc en `useTrackPointsDiff` con multiples invalidates seguidos

### 7.2 Playwright — 0/2
- [ ] Happy path: votar survey + ver leaderboard + ver mis logros
- [ ] Edge case: poll cerrada solo muestra resultados

---

## Edge cases

- [ ] Sin surveys activas → empty "Sin encuestas activas"
- [ ] Leaderboard sin participantes → "Aun no hay puntos asignados"
- [ ] Mi posicion >100 → muestra "Estas en posicion #234"
- [ ] User vota en survey que cierra durante mutation → mensaje "Encuesta cerrada antes de procesar"
- [ ] Empate en puntos → backend ya ordena por timestamp del ultimo punto
- [ ] Passport sin stamps → empty state con CTA "Visita stands para coleccionar"
- [ ] Reward sold out (stock limitado) → boton "Agotado" sin posibilidad de canjear
- [ ] Reward canjeado pero error al generar codigo → backend reintenta + toast warning
- [ ] User pierde puntos despues de canjear → no es refund, business rule lo decide
- [ ] Multiples invalidates seguidos en <1s → debounce diff calculation (no spamear toasts)
- [ ] Golden ticket ya canjeado → no aparece como activo en MyPrizes

---

## Pendiente backend (nice to have)

- Evento socket dedicado `points:awarded {amount, action, total}` para hacer toast informativo (con accion concreta). Hoy se infiere via diff.
- Endpoint paginado de leaderboard (>50 entradas)
- Endpoint para ranking de equipos/stands (no existe)

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports
- [ ] Lighthouse OK
- [ ] Commit DaVinci + memoria + PENDIENTES.md
