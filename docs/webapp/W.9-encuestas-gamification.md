# W.9 — Encuestas + Gamification + Passport + Rewards

> Encuestas en vivo (live polls fuera de sesion) + Leaderboard + Badges + Puntos + Passport stamps + Rewards/Redemption + Streak. Engagement core de la plataforma.
>
> **Estimacion:** ~10h (expandida de 6h por Passport + Rewards + Streak).
> **Dependencias:** W.0, W.1.
> **Estado:** Pendiente.

**Nota:** este modulo tiene 4 sub-areas. Visualmente unificadas en una pantalla "Engagement" con tabs internos:
- **Tab 1: Encuestas** (polls fuera de sesion)
- **Tab 2: Leaderboard** + mi posicion
- **Tab 3: Badges + Passport** (stamps por visitar stands)
- **Tab 4: Rewards** (redimir puntos por premios)

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- App movil: `screens/encuestas/` y `screens/gamification/`
- Memorias: `project_s110_notes.md` (encuestas), `project_s119_s120_s121_notes.md` (gamification + passport)

---

## Alcance

1. **Encuestas**: lista de polls activas/cerradas + votar + ver resultados
2. **Leaderboard** top 10/50/100 con avatar + nombre + puntos
3. Mi posicion + puntos detalle (breakdown por accion)
4. **Badges**: desbloqueados + bloqueados con condicion para desbloquear
5. **Passport stamps**: visitar stands para coleccionar sellos (visual passport con stamps)
6. **Rewards/Redemption**: catalogo de premios canjeables con puntos + historial canjes
7. **My Prizes**: premios ganados (golden ticket, sorteo, redenciones) con codigo de canjeo
8. **Streak**: dias consecutivos visitando la app (bonus puntos)
9. Animacion al ganar puntos (+X notification toast)
10. Confetti al desbloquear badge nuevo

---

## Refs visuales

- App movil gamification (`features/Screenshot 2026-...`)
- `design/showcase-onboarding-v6.html` `gam-wrap` — concepto leaderboard + stamps
- Memoria: `project_s119_s120_s121_notes.md`

---

## Endpoints (verificar)

- `GET /api/v1/event/{id}/polls` — encuestas (no de sesion)
- `POST /api/v1/polls/{id}/vote`
- `GET /api/v1/event/{id}/leaderboard?limit=10`
- `GET /api/v1/event/{id}/me/gamification` — mis puntos + badges + posicion + streak
- `GET /api/v1/event/{id}/me/gamification/breakdown` — puntos por accion
- `GET /api/v1/event/{id}/badges` — todos los badges del evento
- `GET /api/v1/event/{id}/passport` — stamps coleccionados
- `GET /api/v1/event/{id}/rewards` — catalogo
- `POST /api/v1/rewards/{id}/redeem` — canjear premio
- `GET /api/v1/me/redemptions` — historial canjes
- `GET /api/v1/me/prizes` — premios ganados (golden tickets + sorteos + redenciones)

Socket events:
- `points.awarded` (RT toast)
- `leaderboard.updated` (RT refresh)
- `badge.unlocked` (confetti + toast)
- `passport.stamp.new` (stamp animation)
- `streak.bonus.awarded`
- `poll.activated`, `poll.results.updated`

---

## Fase 0 — Hooks (~30min) — 0/3

- [ ] `usePolls(eventId)`
- [ ] `useLeaderboard(eventId, limit)`
- [ ] `useMyGamification(eventId)`

---

## Fase 1 — Encuestas (~1.5h) — 0/4

### 1.1 Lista — 0/2
- [ ] `<PollsList />` con tabs Activas / Cerradas
- [ ] Cada poll: pregunta + tiempo restante o "Cerrada"

### 1.2 Votar — 0/2
- [ ] Click opcion → mutation → mostrar resultados
- [ ] Si ya voto: muestra resultado directo

---

## Fase 2 — Leaderboard (~1.5h) — 0/4

### 2.1 Tabla — 0/2
- [ ] `<LeaderboardTable />` top 10 default, opcion 50/100
- [ ] Cada fila: posicion + avatar + nombre + puntos

### 2.2 Mi posicion — 0/2
- [ ] Sticky bar con mi posicion + puntos arriba o abajo
- [ ] Animacion al cambiar de posicion (slide + fade)

---

## Fase 3 — Badges (~1.5h) — 0/4

### 3.1 Grid — 0/2
- [ ] `<BadgesGrid />` con todos los badges del evento
- [ ] Desbloqueados: full color. Bloqueados: grayscale + opacidad reducida

### 3.2 Detalle — 0/2
- [ ] Click badge → modal con descripcion + condicion para desbloquear
- [ ] Si desbloqueado: muestra fecha desbloqueo + animacion

---

## Fase 3.5 — Passport stamps (~1.5h) — 0/4

### 3.5.1 Passport visual — 0/2
- [ ] `<Passport />` libreta visual con grid de stamps (desbloqueados con sello visible, bloqueados grayscale)
- [ ] Cada stamp: icono sponsor/stand + nombre + fecha desbloqueo

### 3.5.2 Trigger desbloqueo — 0/2
- [ ] Cuando user visita stand (presencial via app movil scanner) o ve sponsor profile en webapp → backend incrementa stamps
- [ ] Socket `passport.stamp.new` → animacion stamp aparece en passport + toast

---

## Fase 3.6 — Rewards/Redemption (~2h) — 0/5

### 3.6.1 Catalogo — 0/2
- [ ] `<RewardsCatalog />` grid de premios con imagen + nombre + costo en puntos + boton "Canjear"
- [ ] Si user no tiene puntos suficientes → boton deshabilitado con tooltip "Te faltan X puntos"

### 3.6.2 Redeem — 0/2
- [ ] Click "Canjear" → modal confirm con premio + puntos a descontar
- [ ] Submit → mutation redeem → muestra codigo de canjeo + instrucciones

### 3.6.3 Mis canjes — 0/1
- [ ] Tab "Mis Premios" con redenciones + estado canjeado/pendiente

---

## Fase 3.7 — My Prizes consolidado (~30min) — 0/2

### 3.7.1 Lista — 0/2
- [ ] `<MyPrizesList />` agrega: golden tickets + sorteos ganados + redenciones canjeadas
- [ ] Cada item: tipo + premio + codigo + estado canjeo

---

## Fase 3.8 — Streak (~30min) — 0/2

### 3.8.1 Display — 0/2
- [ ] Indicador "Estas en racha de {N} dias" en header gamification
- [ ] Tooltip "Vuelve manana para conservar tu racha y ganar bonus"

---

## Fase 4 — Toast +X puntos (~30min) — 0/3

### 4.1 Trigger — 0/2
- [ ] Socket event `points.awarded` → muestra toast "+{n} puntos por {action}"
- [ ] Animacion: slide right + fade out 3s

### 4.2 Badge unlocked — 0/1
- [ ] Socket `badge.unlocked` → modal confetti full screen 3s + toast persistente

---

## Fase 5 — Tests (~30min) — 0/3

### 5.1 Vitest — 0/1
- [ ] `useLeaderboard` con limit

### 5.2 Playwright — 0/2
- [ ] Happy path: votar poll + ver leaderboard + ver badges
- [ ] Edge case: poll cerrada solo muestra resultados

---

## Edge cases

- [ ] Sin polls activas → empty "Sin encuestas activas"
- [ ] Leaderboard sin participantes → "Aun no hay puntos asignados"
- [ ] Mi posicion >100 → muestra "Estas en posicion #234"
- [ ] Badge ya desbloqueado pero socket llega tarde → no duplicar animation
- [ ] User vota en poll que cierra durante mutation → mensaje "Encuesta cerrada antes de procesar"
- [ ] Empate en puntos → ordenar por timestamp del ultimo punto ganado
- [ ] Passport sin stamps → empty state con CTA "Visita stands para coleccionar"
- [ ] Reward sold out (stock limitado) → boton "Agotado" sin posibilidad de canjear
- [ ] Reward canjeado pero error al generar codigo → backend reintenta + toast warning
- [ ] User pierde puntos despues de canjear (refund?) → dependera de business rule, default no
- [ ] Streak rota un dia → mensaje "Tu racha se reinicio, vuelve manana"
- [ ] Multiples badges desbloqueados al mismo tiempo → encolar animaciones, no overlap
- [ ] Golden ticket ya canjeado → no aparece como activo en MyPrizes

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports
- [ ] Commit DaVinci + memoria + PENDIENTES.md
