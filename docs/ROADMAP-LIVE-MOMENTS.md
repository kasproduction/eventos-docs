# Roadmap — Live Moments (Juegos en Vivo)

> Sistema de activaciones en vivo: Ruleta (puntos) y Sorteo (premios fisicos).
> Pool = presenciales en salon (room check-in) + virtuales conectados (socket RT via /internal/session/viewers).
> Display usa libreria spin-wheel (angulos, pesos, animacion nativos).
> Controles en el DISPLAY (el moderador esta frente a la pantalla, no en el laptop).
> Prioridad: P1 (diferenciador — competencia NO lo tiene)
> Estado: Fase 2.6 completada — pendiente 2.7 (2026-04-22)

---

## Principios fundamentales

1. **Sesion-scoped**: el juego pertenece a una sesion. El pool se calcula automaticamente.
2. **Pool = salon + online**: presenciales (`room_attendee_states` inside) + virtuales (socket server real-time `GET /internal/session/viewers`).
3. **Ruleta = siempre all_win**: todos los elegibles reciben los puntos del sector ganador. No hay "un ganador".
4. **Sorteo = un ganador**: ceremony con foto cascade (strip GSAP), se detiene en el ganador. Premio fisico.
5. **Controles en display**: boton Girar/Sortear en la pantalla del display (usa token MC, POST al backend).
6. **Resultado server-side**: el backend decide sector/ganador, no el cliente.
7. **Persistencia de premios**: el ganador del jackpot recibe anuncio permanente + entrada en "Mis premios".
8. **Gamificacion visible**: accion `game_spin` en desafios con contador (x1, x2, x3...).

---

## Modelo de datos

### Migration: `live_games`
```
id, event_id FK, session_id FK, sponsor_id FK nullable
type: enum (spin, jackpot)
title: varchar 150
status: enum (draft, active, finished, cancelled)
audience: enum (all, presencial, virtual) default 'all'
config: JSON (sectores para spin, premio para jackpot)
winner_attendee_id FK nullable (solo jackpot)
winner_data: JSON nullable
auto_post_wall: boolean default true
started_at, finished_at timestamps nullable
```

### Migration: `live_game_participants`
```
id, game_id FK, attendee_id FK
round: int default 1
answer: JSON nullable
score: int default 0
is_winner: boolean default false
prize_claimed_at: timestamp nullable (jackpot: cuando se reclamo)
prize_claim_code: varchar 8 nullable (codigo verificacion)
responded_at timestamp nullable
UNIQUE(game_id, attendee_id, round)
```

### Config JSON

**Spin (Ruleta)** — siempre all_win:
```json
{
  "sectors": [
    { "label": "50 pts", "points": 50, "weight": 40, "color": "#10B981" },
    { "label": "100 pts", "points": 100, "weight": 25, "color": "#3B82F6" },
    { "label": "200 pts", "points": 200, "weight": 10, "color": "#8B5CF6" },
    { "label": "500 pts", "points": 500, "weight": 1, "color": "#F59E0B" }
  ],
  "mode": "all_win",
  "spin_duration_ms": 5000
}
```

**Jackpot (Sorteo)** — un ganador:
```json
{
  "prize_title": "iPad Pro",
  "prize_description": "Cortesia de AWS",
  "slot_duration_ms": 8000
}
```

---

## Bugs resueltos (sesion 2026-04-22)

### BUG-1: Angulo visual no corresponde al sector — RESUELTO
- Migrado a libreria `spin-wheel` v5 que maneja angulos, pesos y animacion nativamente
- `spinToItem(sectorIndex)` garantiza que el pointer siempre apunta al sector correcto
- Pesos de sectores respetados automaticamente por la libreria

### BUG-2: Sectores sin color/weight → rueda gris — CORREGIDO
- Backend asigna defaults (fallback colors + weight=1) en `store()`
- Display tiene fallback colors array

### BUG-3: Pool virtual stale (Redis zombies) — CORREGIDO v2
- Problema: Redis SETs `session:*:viewers` no tenian TTL, quedaban zombies al desconectar
- Fix: eliminar Redis SETs para pool, usar socket server RT via `GET /internal/session/viewers`
- Socket endpoint usa `fetchSockets()` + `socket.data.user.attendeeId` — siempre real-time
- GameService::getVirtualPool ahora hace HTTP al socket server, no lee Redis
- MC: eligible_count se actualiza en RT via `session:audience` event
- MC: refresh carga eligible_count del API (antes hardcoded 0)

### BUG-4: Rueda cuadrada — CORREGIDO
- `.d-wheel-wrap` ahora tiene `overflow:hidden; border-radius:50%`

### BUG-5: Gamificacion invisible — YA ESTABA CORREGIDO
- `game_spin` ya existia en PointsService DEFAULT_CONFIG

### BUG-6: Race condition double launch — CORREGIDO
- `WHERE status='draft'` atomic update previene doble lanzamiento

### BUG-7: Race condition double spin — CORREGIDO
- `WHERE status='active'` atomic update previene doble giro

### BUG-8: Race condition double draw — CORREGIDO
- `WHERE status='active'` atomic update previene doble sorteo

### BUG-9: mt_rand crash con sectors vacio — CORREGIDO
- Early return para empty/single sector en weightedRandom()

### BUG-10: Jackpot srandmember null → attendee_id=0 — CORREGIDO
- Check `$winnerId` antes de buscar attendee

### BUG-11: CSV formula injection — CORREGIDO
- Sanitize campos con prefijos peligrosos (=, +, -, @)

### BUG-12: Spin-wheel module race condition — CORREGIDO
- Retry con setTimeout si modulo no cargo aun

### BUG-13: < 2 sectores = pantalla blanca — CORREGIDO
- Muestra mensaje "Configuracion de ruleta invalida"

### BUG-14: Idle message stuck on error — CORREGIDO
- Restore idle state (mensaje + rotacion) en catch del control button

### BUG-15: Standby timer no se limpia en disconnect — CORREGIDO
- clearTimeout + clearInterval en disconnect handler

---

## Fase 2 — Completada

### 2.1 Angulo rueda (BUG-1) — COMPLETADO
- [x] Libreria spin-wheel maneja angulos nativamente

### 2.2 Display visual (BUG-4) — COMPLETADO
- [x] overflow:hidden + border-radius:50%

### 2.3 Gamificacion visible (BUG-5) — COMPLETADO
- [x] game_spin ya en DEFAULT_CONFIG

### 2.4 Boton Girar/Sortear en display — COMPLETADO
- [x] Display: si tiene token MC, muestra boton de control
- [x] Spin: boton "Girar" → POST /admin/games/{id}/spin
- [x] Jackpot: boton "Sortear" → POST /admin/games/{id}/draw
- [x] Boton discreto, semitransparente, desaparece tras accion

### 2.5 Elegibles con spin-wheel — COMPLETADO
- [x] Libreria JS maneja visualizacion de sectores con pesos

### 2.6 Sorteo Ceremony (reemplaza slot machine) — COMPLETADO 2026-04-22
- [x] Rewrite completo: slot machine 3 reels → Photo Cascade Ceremony
- [x] Strip vertical unico con 20 celdas (random picks del pool + winner al final)
- [x] GSAP `power4.out` — desaceleracion natural, 60fps con cualquier cantidad de participantes
- [x] Responsive: reel `min(45vh, 420px)`, celdas proporcionales
- [x] Outline amber en reel, intensifica al frenar, fade en lock
- [x] Shockwave doble (amber + white) al parar
- [x] Winner reveal: foto 320px + "GANADOR" + nombre 80px + premio 30px uppercase + session name
- [x] Confetti amber geometric con GSAP (no CSS keyframes)
- [x] Fase participacion simplificada: titulo + premio + "Preparate..." (sin counter inutil)
- [x] Info (titulo + premio) posicionado absolute top-right (no se monta con reel)
- [x] Fallbacks: beam si no hay foto, `[winner]` si participants vacio
- [x] 10 bugs resueltos (BUG-162 a BUG-171)

### 2.7 Persistencia de premios (jackpot) — PENDIENTE ~1h
- [ ] Al ganar: generar claim_code (6 chars alfanumerico)
- [ ] Al ganar: crear Announcement privado para el ganador
- [ ] Nuevo endpoint: `GET /me/prizes` → lista de premios ganados
- [ ] Filament: boton "Marcar reclamado"
- [ ] App: seccion "Mis premios" en pantalla Gamificacion

---

## Fase 3 — App integration — PENDIENTE ~3h

### Socket listeners
- [ ] `game:launched` → banner contextual en Home + session: "Ruleta en vivo — Mira la pantalla!"
- [ ] `game:result` type=spin → toast "Ganaste [N] pts en [titulo]!" + AnimatedPts
- [ ] `game:result` type=jackpot → si ganaste: pantalla especial confetti + "GANASTE [premio]!"
- [ ] `game:result` type=jackpot → si no ganaste: "El ganador es [nombre]"
- [ ] `game:ended` → dismiss banner

### Push notifications (requiere dev build)
- [ ] Spin: no push (toast via socket es suficiente)
- [ ] Jackpot ganador: push "Ganaste [premio]! Toca para ver detalles" → deep link a Mis Premios
- [ ] Jackpot todos: push "Se sorteo [premio] — el ganador es [nombre]!"

---

## Fase 4 — Trivia (Quiz) — PENDIENTE ~6h

### Backend
- [ ] `POST /admin/games/{id}/launch` → status=active
- [ ] `POST /admin/games/{id}/next-question` → broadcast pregunta + timer
- [ ] `POST /games/{id}/answer` → valida timer, calcula score (correcta + speed bonus)
- [ ] `POST /admin/games/{id}/close-round` → broadcast respuesta correcta + top 3
- [ ] `POST /admin/games/{id}/finish` → ranking final, top 3 puntos, social wall
- [ ] `GET /games/{id}/leaderboard` → ranking acumulado
- [ ] Tests: 8

### Display LED
- [ ] Pregunta: texto grande + 4 opciones coloreadas + countdown circular
- [ ] Barras de respuestas RT
- [ ] Resultado: correcta resaltada + distribucion + top 3
- [ ] Final: podio top 3 + confetti

### MC
- [ ] Modal crear trivia: titulo, preguntas repeater, sponsor, audience
- [ ] Panel activo: pregunta actual, countdown, respuestas RT, boton Siguiente/Cerrar
- [ ] Leaderboard sidebar RT

### App
- [ ] Socket game:update → pantalla pregunta + 4 botones + countdown
- [ ] Tap respuesta → POST answer → disabled + "Esperando..."
- [ ] Socket game:result → correcta verde/rojo + puntos animados + posicion
- [ ] Socket game:ended → resultado final con podio

---

## Fase 5 — Pulido — PENDIENTE ~2h

### Branded moments
- [ ] Display: logo sponsor en esquina durante todo el juego
- [ ] App: "Patrocinado por [sponsor]" con logo
- [ ] Push: "[sponsor] te invita a participar"
- [ ] Social wall: post con logo sponsor

### Sonido display
- [ ] Countdown tick ultimos 5s
- [ ] Fanfare al resultado
- [ ] Confetti pop
- [ ] User gesture unlock al primer click

### Historial exportable
- [x] CSV export spin + jackpot (COMPLETADO)
- [x] CSV sanitizado contra formula injection (COMPLETADO)
- [ ] Boton export en MC historial

---

## Estado actual

| Componente | Estado |
|------------|--------|
| Backend modelos + API | Funcional, 11 bugs corregidos, race conditions resueltos |
| MC tab Games | Funcional (drafts, launch, spin, draw, historial, eligible RT) |
| Display spin | Funcional (spin-wheel lib, idle, control btn, auto-standby) |
| Display jackpot | Basico (contador + ganador, sin slot machine) |
| Pool RT (socket+presencial) | Funcional (socket /internal/session/viewers + MC RT refresh) |
| Gamificacion visible | Funcional (game_spin en config) |
| Boton en display | Funcional (Girar/Sortear con token MC) |
| Slot machine fotos | PENDIENTE (Fase 2.6) |
| Premios persistentes | PENDIENTE (Fase 2.7) |
| App socket listeners | PENDIENTE (Fase 3) |
| Push notifications | PENDIENTE (Fase 3, requiere dev build) |
| Trivia | PENDIENTE (Fase 4) |
| 24 tests | PASANDO |

---

_Roadmap Live Moments v2.1 — EventOS_
_22 abril 2026_
