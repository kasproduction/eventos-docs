# Roadmap — Live Moments (Juegos en Vivo)

> Sistema unificado de activaciones en vivo: Ruleta, Trivia, Sorteo.
> Cada momento vive dentro de una sesion — participantes = presenciales en salon + virtuales conectados.
> Aprobado: 2026-04-21 (DaVinci paso 6)
> Estado: Pendiente implementacion
> Prioridad: P1 (diferenciador — competencia NO lo tiene)
> Ref: memory/project_gamification_ideas.md, memory/reference_bingo_performance.md

---

## Principios fundamentales

1. **Sesion-scoped**: el juego pertenece a una sesion. MC ya sabe cual. El pool de participantes se calcula automaticamente.
2. **Audience configurable**: moderador elige "todos", "solo presenciales" o "solo virtuales" antes de lanzar.
3. **Presenciales** = `room_attendee_states` WHERE status=inside AND room_id=session.room_id
4. **Virtuales** = Redis SMEMBERS `session:{id}:viewers` (ya tracked por join:session)
5. **1 broadcast → todos**: patron bingo 4000 personas. Respuestas individuales NO se broadcastean. Solo resultado final.
6. **Resultado server-side**: el backend decide el ganador, no el cliente. Anti-trampa.
7. **Branded moments**: cada juego puede tener sponsor_id — logo visible en display, app, push, social wall.
8. **Reutilizar infraestructura**: display:project pipeline, PointsService, AnimatedPts app, polls display renderer.

---

## Modelo de datos

### Migration: `live_games`
```
id
event_id FK
session_id FK
sponsor_id FK nullable (branded moment)
type: enum (spin, trivia, jackpot)
title: varchar 150
status: enum (draft, active, finished, cancelled)
audience: enum (all, presencial, virtual) default 'all'
config: JSON (sectores/preguntas/timer segun tipo)
current_round: int default 0 (trivia: pregunta actual)
winner_attendee_id FK nullable
winner_data: JSON nullable (nombre, foto, premio, score)
auto_post_wall: boolean default true
started_at timestamp nullable
finished_at timestamp nullable
created_at / updated_at
```

### Migration: `live_game_participants`
```
id
game_id FK
attendee_id FK
round: int default 1 (trivia: en que pregunta respondio)
answer: JSON nullable (sector index, option index, etc)
score: int default 0
is_winner: boolean default false
responded_at timestamp nullable
UNIQUE(game_id, attendee_id, round)
```

---

## Config JSON por tipo

### Spin (Ruleta)
```json
{
  "sectors": [
    { "label": "50 pts", "points": 50, "weight": 40, "color": "#10B981" },
    { "label": "100 pts", "points": 100, "weight": 25, "color": "#3B82F6" },
    { "label": "200 pts", "points": 200, "weight": 10, "color": "#8B5CF6" },
    { "label": "iPhone!", "points": 500, "weight": 1, "color": "#F59E0B", "is_prize": true }
  ],
  "mode": "all_win | random_winner",
  "spin_duration_ms": 5000
}
```
- `all_win`: todos los elegibles reciben los puntos del sector donde cae
- `random_winner`: un elegible random gana el premio fisico/puntos grandes

### Trivia (Quiz)
```json
{
  "questions": [
    {
      "text": "Cual es el lenguaje mas usado en 2026?",
      "options": ["Python", "JavaScript", "Rust", "Go"],
      "correct": 1,
      "timer_seconds": 15,
      "points_correct": 100,
      "points_speed_bonus": 50
    }
  ],
  "show_leaderboard_every": 3
}
```
- Speed bonus: `points_speed_bonus * (time_remaining / timer_seconds)` — mas rapido = mas puntos
- Leaderboard cada N preguntas en display

### Jackpot (Sorteo)
```json
{
  "participation_seconds": 30,
  "prize_title": "iPad Pro",
  "prize_description": "Cortesia de AWS",
  "prize_image_url": "https://...",
  "slot_duration_ms": 8000,
  "push_to_all": true
}
```

---

## Fase 0 — Infraestructura base — ~3h

### Backend
- [ ] Migration: `live_games` + `live_game_participants`
- [ ] Modelo `LiveGame`: relaciones event, session, sponsor, participants, winner
- [ ] Modelo `LiveGameParticipant`: relacion game, attendee
- [ ] `GameService::getEligiblePool(game)` — resuelve audience (all/presencial/virtual) contra room_attendee_states + Redis viewers
- [ ] `GameService::awardPoints(game, attendeeId, points, action)` — reutiliza PointsService existente
- [ ] `GameService::autoPostWall(game)` — crea post en social wall con ganador + sponsor logo
- [ ] Tests: 4 (modelos, pool presencial, pool virtual, pool all)

### Socket server
- [ ] Endpoint `/internal/game/broadcast` — recibe { eventId, sessionId, event, payload }
- [ ] Emite a `session:{sessionId}` (participantes) + `display:session:{sessionId}` (pantalla)
- [ ] Tipos: `game:launched`, `game:update`, `game:result`, `game:ended`

### MC — Tab Games (Tab 6, tecla 6)
- [ ] Tab visible solo si evento tiene `games_enabled` (toggle Filament)
- [ ] 3 botones crear: [Ruleta] [Trivia] [Sorteo]
- [ ] Panel juego activo: titulo + sponsor + tipo + stats RT + controles
- [ ] Historial: ultimos juegos con resultado
- [ ] Radio select audience: Todos / Solo presenciales / Solo virtuales
- [ ] Preview: boton "Vista previa" muestra como se vera en display sin lanzar

### Display LED
- [ ] Agregar `case 'game_spin'`, `case 'game_trivia'`, `case 'game_jackpot'` en renderProjection()
- [ ] Audio queue: countdown tick, fanfare resultado (user gesture unlock al primer click)

---

## Fase 1 — Ruleta (Spin) — ~4h

### Backend
- [ ] `POST /api/v1/admin/games` — crear juego (type, title, config, session_id, sponsor_id, audience)
- [ ] `POST /api/v1/admin/games/{id}/launch` — lanzar (status→active, broadcast game:launched)
- [ ] `POST /api/v1/admin/games/{id}/spin` — moderador gira rueda
  - Calcula sector ganador (weighted random)
  - Si mode=all_win: asigna puntos a TODOS los elegibles via PointsService
  - Si mode=random_winner: SRANDMEMBER del pool → ganador + puntos
  - Broadcast `game:result` con sector, angulo final, ganador
  - Status→finished
- [ ] `GET /api/v1/games/{id}/active` — juego activo para la sesion (app consulta)
- [ ] Tests: 6 (crear, lanzar, spin all_win, spin random_winner, pool filtrado, puntos asignados)

### Display LED
- [ ] Rueda CSS: N sectores coloreados con labels
- [ ] Animacion: `transform: rotate(Xdeg)` con cubic-bezier deceleration (~5s)
- [ ] Angulo final viene del backend (deterministic)
- [ ] Resultado: sector resaltado + nombre ganador (si random_winner) + foto + sponsor logo
- [ ] Standby post-resultado: vuelve a standby tras 10s

### MC
- [ ] Modal crear ruleta: titulo, sectores (repeater: label+puntos+peso+color), modo (todos/random), sponsor select, audience select
- [ ] Boton "Girar" (solo cuando activo)
- [ ] Stats: elegibles N, resultado sector, ganador (si aplica)
- [ ] Boton "Proyectar" envia display:project type=game_spin

### App
- [ ] Socket listener `game:launched` → banner en Home + session actual: "Ruleta en vivo — Mira la pantalla!"
- [ ] Socket listener `game:result` → toast con puntos ganados (si all_win) o nombre ganador
- [ ] Banner desaparece en `game:ended`
- [ ] Ganador: push notification "Ganaste [premio]! Acercate a [stand/mesa]"

---

## Fase 2 — Sorteo (Jackpot) — ~3h

### Backend
- [ ] `POST /api/v1/admin/games/{id}/launch` — abre participacion (countdown)
- [ ] `POST /api/v1/games/{id}/join` — attendee se une (valida elegible, agrega a Redis SET `game:{id}:pool`)
  - Rate limit: 1 join por attendee por juego
  - Broadcast `game:update` con count actualizado (debounce 500ms)
- [ ] `POST /api/v1/admin/games/{id}/draw` — moderador cierra + sortea
  - SRANDMEMBER de Redis SET → ganador
  - Guarda winner_attendee_id + winner_data
  - Broadcast `game:result` con ganador (nombre, foto, premio)
  - Push al ganador + push a todos (si config.push_to_all)
  - Auto-post social wall con foto ganador + sponsor
  - Status→finished
- [ ] Tests: 6 (join, join duplicado, draw, pool audience filtrado, push ganador, auto-post wall)

### Display LED
- [ ] Fase participacion: contador grande animado "2,847 participantes" (animateCounter existente)
- [ ] Fase sorteo: slot machine con fotos de participantes (3 carriles, velocidad decrece, frena en ganador)
- [ ] Resultado: foto grande ganador + nombre + premio + confetti CSS + sponsor logo

### MC
- [ ] Modal crear sorteo: titulo, premio (texto+imagen), duracion participacion, sponsor, audience
- [ ] Panel activo: contador participantes RT, countdown, boton "Sortear" (habilitado al cerrar participacion)
- [ ] Resultado: foto ganador + nombre + boton "Re-sortear" (si el primero no esta presente)

### App
- [ ] Socket `game:launched` type=jackpot → BottomSheet con boton grande "PARTICIPAR" + countdown
- [ ] Tap participar → POST /games/{id}/join → boton cambia a "Participando" (check)
- [ ] Socket `game:update` → contador sube en BottomSheet
- [ ] Socket `game:result` → si ganaste: pantalla especial confetti + "GANASTE [premio]!". Si no: "El ganador es [nombre]"
- [ ] AnimatedPts si el sorteo da puntos de consolacion

---

## Fase 3 — Trivia (Quiz) — ~6h

### Backend
- [ ] `POST /api/v1/admin/games/{id}/launch` — lanza trivia (status→active)
- [ ] `POST /api/v1/admin/games/{id}/next-question` — moderador avanza a siguiente pregunta
  - Broadcast `game:update` con pregunta actual + opciones + timer
  - Inicia countdown server-side (Redis key con TTL)
- [ ] `POST /api/v1/games/{id}/answer` — attendee responde
  - Valida: elegible, no respondio esta ronda, timer no expirado
  - Calcula score: correcta = points_correct + (speed_bonus * time_remaining/timer)
  - Guarda en live_game_participants (round, answer, score)
  - NO broadcast individual (patron bingo)
  - Redis INCR `game:{id}:round:{n}:option:{idx}` para stats RT
- [ ] `POST /api/v1/admin/games/{id}/close-round` — cierra pregunta actual (auto o manual)
  - Broadcast `game:result` con respuesta correcta + distribucion + top 3
  - Si show_leaderboard_every: broadcast leaderboard completo
- [ ] `POST /api/v1/admin/games/{id}/finish` — termina trivia
  - Calcula ranking final (SUM scores por attendee)
  - Top 3 ganadores + puntos gamificacion
  - Broadcast `game:ended` con podio
  - Auto-post social wall
- [ ] `GET /api/v1/games/{id}/leaderboard` — ranking acumulado (app consulta entre preguntas)
- [ ] Tests: 8 (lanzar, responder correcta, responder incorrecta, speed bonus, timer expirado, leaderboard, finish top 3, audience filtrado)

### Display LED
- [ ] Pregunta: texto grande + 4 opciones coloreadas (A/B/C/D) + countdown circular
- [ ] Durante timer: barras de respuestas crecen RT (patron polls)
- [ ] Resultado: respuesta correcta resaltada + distribucion % + top 3 con fotos
- [ ] Leaderboard: top 10 con foto, nombre, score, posicion (cada N preguntas)
- [ ] Final: podio top 3 con fotos grandes + confetti + sponsor

### MC
- [ ] Modal crear trivia: titulo, preguntas (repeater: texto+4 opciones+correcta+timer+puntos), sponsor, audience
- [ ] Panel activo: pregunta actual, countdown, respuestas RT (barras), boton "Siguiente" / "Cerrar ronda"
- [ ] Leaderboard en sidebar: top 10 actualizado en RT
- [ ] Boton "Finalizar trivia" → muestra podio

### App
- [ ] Socket `game:launched` type=trivia → BottomSheet: "Trivia patrocinada por [sponsor]" + "Listo!"
- [ ] Socket `game:update` (nueva pregunta) → pantalla pregunta: texto + 4 botones coloreados + countdown
- [ ] Tap opcion → POST /games/{id}/answer → boton deshabilitado + "Esperando..."
- [ ] Socket `game:result` → respuesta correcta resaltada (verde/rojo) + "+150 pts" animado + posicion
- [ ] Socket `game:ended` → pantalla resultado final: tu posicion, puntos totales, podio top 3
- [ ] Transicion entre preguntas: slide horizontal (patron DaySlide agenda)

---

## Fase 4 — Pulido + QA — ~2h

### Branded moments
- [ ] Display: logo sponsor en esquina durante todo el juego
- [ ] App banner: "Patrocinado por [sponsor]" con logo
- [ ] Push: "[sponsor] te invita a jugar" / "[sponsor] sortea [premio]"
- [ ] Social wall auto-post: "Ganador del sorteo de [sponsor]: [nombre]" con logo
- [ ] MC: sponsor select con logo preview

### Cooldown
- [ ] Minimo 2 minutos entre juegos en la misma sesion
- [ ] MC: boton crear deshabilitado durante cooldown con countdown

### Historial attendee
- [ ] Seccion "Mis juegos" en pantalla Gamification/Desafio
- [ ] Lista: tipo + titulo + resultado (gane/perdi) + puntos + fecha

### Resultados exportables
- [ ] `GET /api/v1/admin/games/{id}/export` — CSV con participantes, respuestas, scores, ganador
- [ ] Boton en MC historial: "Exportar resultados"
- [ ] Util para reporte al sponsor

### Sonido display
- [ ] Audio queue: countdown tick (ultimos 5s), fanfare resultado, confetti pop
- [ ] User gesture unlock: primer click en display page desbloquea audio
- [ ] Volumen configurable (pill en display)

### Tests QA
- [ ] Test E2E: crear spin → lanzar → girar → verificar puntos asignados
- [ ] Test E2E: crear jackpot → 10 joins → draw → verificar ganador + push + wall post
- [ ] Test E2E: crear trivia 3 preguntas → responder → verificar scores + leaderboard
- [ ] Test: audience presencial solo incluye room_attendee_states inside
- [ ] Test: audience virtual solo incluye Redis viewers
- [ ] Test: cooldown previene lanzar dos juegos en 2 minutos
- [ ] Test: attendee no elegible no puede participar

---

## Resumen de esfuerzo

| Fase | Que | Tiempo | Tests |
|------|-----|--------|-------|
| 0 | Infraestructura (modelos, socket, MC tab, display base) | ~3h | 4 |
| 1 | Ruleta (spin) | ~4h | 6 |
| 2 | Sorteo (jackpot) | ~3h | 6 |
| 3 | Trivia (quiz) | ~6h | 8 |
| 4 | Pulido (branded, cooldown, historial, export, sonido, QA) | ~2h | 7 |
| **Total** | | **~18h** | **31 tests** |

---

## Criterio de DONE

- [ ] 3 tipos de juego funcionando E2E (MC → backend → socket → display + app)
- [ ] Pool de participantes respeta audience (all/presencial/virtual)
- [ ] Presenciales = room check-in, virtuales = socket viewers
- [ ] Branded moments: sponsor visible en display, app, push, social wall
- [ ] Puntos gamificacion asignados automaticamente
- [ ] Display LED con animaciones por tipo (rueda, slot machine, trivia barras)
- [ ] App: banner contextual + participacion via BottomSheet
- [ ] MC: crear, lanzar, controlar, ver stats RT, exportar
- [ ] Cooldown entre juegos
- [ ] 31 tests pasando
- [ ] 0 bugs pendientes

---

## Documentos relacionados

| Doc | Contenido |
|-----|-----------|
| `docs/PENDIENTES.md` | P1 — Diferenciadores |
| `memory/project_gamification_ideas.md` | Ideas originales: ruleta, trivia, sorteo, momentos branded |
| `memory/reference_bingo_performance.md` | Patron bingo 4000 personas: 1 broadcast, validacion server-side |
| `memory/project_unity_gamebridge.md` | Unity Game Bridge (Fase futura, mismo sistema base) |
| `memory/project_mission_control_notes.md` | MC v4: display LED, metricas RT, projection pipeline |
| `docs/ROADMAP-WEBHOOKS.md` | Patron implementacion: fases, tests por fase, bugs post-audit |

---

_Roadmap Live Moments v1.0 — EventOS_
_21 abril 2026_
