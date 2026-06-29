# W.9 — Desafio (Engagement Hub)

> Leaderboard + Logros (acciones completadas) + Passport stamps + Rewards/Redemption + Golden Tickets. Engagement core.
>
> **Counter:** **30/35 (86%)** — Sprint 2.B sesion 2 entregada (2026-06-29, commit `4238c69` local)
> **Estimacion total:** ~12h (gastadas ~9h, faltan ~2-3h)
> **Dependencias:** W.0, W.1.

**Arquitectura final (2026-06-29):**
- Hub split layout literal espejo W.7. Wall izq apila 6 cards (Hero/Tickets/Premios/Tip/Retos/Pasaporte) — click en card abre panel der con detalle.
- **Encuestas viven en W.4 Streaming** (in-stream context con `poll:new`/`poll:vote`/`poll:closed` sockets). NO se replican en W.9 hub.
- **Golden Tickets**: wall card lista TODOS los tickets, cada pending es boton individual, click → panel der con SU reveal (sin lista repetida ni modal).
- **Redeem optimistic**: modal abre inmediato con QR skeleton, backend genera token + descuenta puntos en paralelo, modal rellena cuando llega.
- **Sin toast "+X pts via diff"**: descartado por espejo Expo (Expo NO muestra toast al ganar puntos, sube silencioso al refrescar).
- **Colores TEAL/GOLD/CYAN fijos** del sistema gamification, NO `var(--accent)` del cliente.
- **Haptic** wireado en todo el modulo (5 intensidades en `lib/haptic.ts`).

**Cards del wall (todas implementadas):**
- HeroCard — avatar + puntos + posicion + barra segmentada vs lider + podio escalado top 3
- GoldenTicketsCard — TODOS los tickets, pending son botones, claimed son info
- RewardsPreviewCard — preview primeros 2 (oculta si `rewards_enabled=false`)
- TipCard — proximo objetivo motivacional
- RetosCard — barra completed/total + proximo reto
- PasaporteCard — barra + grid 6 stamps + boton "+N" (oculta si `passport_enabled=false`)

**Panels del detail (todos implementados):**
- GoldenTicketPanel — reveal de UN ticket especifico
- RankingPanel — podio + lista top 50
- RewardsPanel — catalogo + redeem optimistic con RedeemModal
- RetosPanel — lista de logros con estado
- PasaportePanel — grid completo + metadata
- RulesPanel — reglas educativas + tabla puntos

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

## Fase 0 — Hooks / fetchers — 3/4

- [x] **`fetchDesafioOverview(eventId)`** SSR — agrega 5 endpoints (`/leaderboard` + `/me/points` + `/me/prizes` + `/rewards` + `/my-passport`) con degradacion suave. Reemplaza `useMyPoints` + `useLeaderboard` separados con una llamada SSR. `lib/desafio.ts`.
- [x] **Lazy fetchers client** (`lib/desafio-client.ts`): `fetchRankingClient`, `fetchRewardsClient`, `fetchPassportClient`, `redeemRewardClient`. Llamados al abrir cada panel via proxies Next `/api/desafio/[eventId]/{leaderboard|rewards|passport|redeem/[rewardId]}`.
- [x] **`fetchDesafioRanking` server** (`lib/desafio.ts`) — equivalente server-side de `useLeaderboard`.
- [~N/A] `useGamificationConfig` — `actions[]` ya viene embebido en `/me/points` response, no requiere hook separado.

---

## Fase 1 — Encuestas / Surveys — N/A descartado

- [~N/A] **Encuestas viven en W.4 Streaming** (in-stream context con `poll:new`/`poll:vote`/`poll:closed` sockets). NO duplicar en W.9 hub. Decision arquitectural 2026-06-28.

---

## Fase 2 — Leaderboard — 3/4

- [x] `RankingPanel` muestra top 50 (`leaderboard()` PointsService backend ya limita a 50).
- [x] Cada fila: posicion + avatar (boring-avatars beam fallback) + nombre + total_points.
- [x] Sticky bar `my_position + my_points` — HeroCard del wall siempre visible con la posicion del usuario + barra segmentada vs lider.
- [x] `my_position > 50` cubierto — backend devuelve `my_position` separado del top 50, panel muestra siempre "Tu posicion #N".
- [ ] Share rank social — fuera scope sesion 2, va a backlog futuro.

---

## Fase 3 — Mis Logros / Retos — 3/3

- [x] `RetosCard` (wall preview) + `RetosPanel` (lista completa) itera sobre `actions[]` del overview.
- [x] Cada item: icon + label + earned/points + estado completed/pending (visual diferenciado `.dx-reto-row.done` vs `.pending`).
- [x] Detalle inline en la lista (no modal separado — info ya esta visible).

---

## Fase 4 — Passport stamps — 3/4

- [x] `PasaporteCard` wall (preview 6 stamps + boton "+N") + `PasaportePanel` grid completo.
- [x] Cada stamp: logo sponsor + nombre + tier + stamped_at.
- [x] Solo VIEW (earning via QR fisico mobile — correcto, no aplica a webapp).
- [ ] Socket `data:invalidate{entity:passport}` → animacion + toast — depende W.11 sockets RT, no es bloqueante para cierre W.9.

---

## Fase 5 — Rewards/Redemption — 4/5

- [x] `RewardsPreviewCard` (wall preview 2 items) + `RewardsPanel` (catalogo grid completo via lazy fetch).
- [x] Cada reward: icon + nombre + costo + `remaining_stock` + sponsor. `can_redeem` calculado cliente-side (`myPoints >= points_cost && remaining_stock > 0`).
- [x] **Redeem optimistic** — boton "Canjear" abre `RedeemModal` INMEDIATO con estado loading (QR skeleton shimmer + "Estamos preparando tu codigo…"). En paralelo `POST /events/{id}/rewards/{rewardId}/redeem`. Al exito: modal transiciona a estado ready (QR real + countdown 5min). Al error: modal cierra + `lumina.error` con codigo especifico (`INSUFFICIENT_POINTS` / `OUT_OF_STOCK` / `ALREADY_PENDING`).
- [x] Display token + hint "Muestra al vendedor" + countdown.
- [ ] **Tab "Mis canjes"** wireado con `GET /me/redemptions` — placeholder hoy (tab existe en panel pero vacio). Scope sesion 3.

---

## Fase 6 — Golden Ticket reveal — 2/2

- [x] `GoldenTicketPanel` panel der: trophy XL + line gold + overline "Ganador" + nombre + sponsor + claim_code XXL gold + QR grande con RGB rect animado (`@property --dx-rgb-angle`) + hint "Presenta este codigo en el stand de…" + countdown si `expires_at`.
- [x] **Flow individual** (decision 2026-06-29): wall card lista TODOS los tickets, pending son `<button>` individuales con haptic, claimed son `<div>` info estatica. Click pending → selecciona ese ticket + abre panel der mostrando SU reveal solo (sin lista repetida ni modal). Click otra vez → toggle, cierra panel. Click otro pending → cambia ticket revelado.

---

## Fase 7 — Toast +X puntos via diff — N/A descartado

- [~N/A] **Descartado 2026-06-29** por espejo Expo. El Expo NO muestra toast al ganar puntos: `visit-stand` + `trivia answer` + acciones del muro/agenda suben puntos silenciosamente, el usuario descubre el incremento al volver al HUD del home o al leaderboard. Memoria `feedback_no_points_diff_toast.md`.

---

## Fase 8 — Tests — 1/3

- [x] **Vitest helpers puros** — `tests/components/desafio/desafioDerive.test.ts` (11 tests: `initials`, `pointsRatio`, `segmentsFilled`, `pointsToTop`, `sortActions`, `pickFeaturedTicket`, `retosProgress`) + `tests/lib/desafioNormalize.test.ts` (14 tests cubriendo los 3 shape gaps backend: ticket nested reward/sponsor, reward `can_redeem` calculado, passport `stands`→`stamps`).
- [~N/A] Vitest diff calc — N/A (no implementamos points diff).
- [ ] **Playwright `desafio.spec.ts`** (5 escenarios: auth gate, SSR hub renderiza, click ticket pending abre panel der con SU reveal, redeem optimistic abre modal con loading→ready, Esc cierra) — sesion 3.

---

## Fase 9 — Cierre formal — 0/4

- [ ] Validar manual 3 viewports (desktop 1600 / tablet H 1130 / mobile webapp).
- [ ] Lighthouse autenticado — batch QA final cross-modulos (no bloqueante).
- [ ] Memoria — actualizar `project_w9_engagement_webapp.md` con arquitectura final + crear `feedback_no_repetir_info_en_panel.md` + `feedback_no_modal_desktop.md` + `feedback_no_points_diff_toast.md`.
- [ ] Counter PARITY-MATRIX sincronizar W.9.

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
