---
name: S1.10 — Encuestas en vivo (V2 completa)
description: Arquitectura implementada: multi-pregunta, 3 tipos, allow_multiple, export CSV, display proyectable — mergeado a main 2026-04-04
type: project
---

## Estado: ✅ COMPLETA — mergeada a main en backend + app (2026-04-04)

Branch `feature/s110-encuestas-v2` → main en ambos repos.

---

## Modelo de datos (implementado)

```
live_polls:          id, event_id, session_id (nullable), scope (session|event), title, status (draft|active|closed), activated_at, closed_at
live_poll_questions: id, poll_id, question_text, question_type (multiple_choice|open_text|star_rating), allow_multiple boolean, sort_order
live_poll_options:   id, question_id (FK cascadeDelete), option_text, sort_order
live_poll_votes:     id, poll_id, question_id, option_id (nullable), attendee_id, answer_text (nullable)
                     UNIQUE: (poll_id, question_id, attendee_id, option_id)
```

## Decisiones UX (aprobadas y aplicadas)

- `word_cloud` eliminado — 3 tipos finales: `multiple_choice`, `open_text`, `star_rating`
- `allow_multiple` soporta single/multi-select sin recodear en el futuro (gamification)
- Poll cierra mientras usuario responde: ≥1 respuesta → terminar slides; 0 respuestas → cerrar inmediato
- Navegación hacia atrás permitida — UPDATE voto anterior
- Badge verde en módulo Encuestas cuando `has_active=true`

## Backend (completo)

- 4 migraciones + tabla notifications
- Modelos: LivePoll, LivePollQuestion, LivePollOption, LivePollVote
- PollController: vote (single+multi+open_text+star_rating), activePoll con my_answers, surveys con has_active, results por pregunta
- LivePollResource Filament: Repeater anidado preguntas+opciones, página LivePollResults, acciones start/close/export/import
- ExportPollResponsesJob: CSV por asistente con BOM UTF-8, `file_put_contents` directo (evita finfo extension issue de Windows), notificación campana, failed() handler
- Import CSV: FileUpload en headerActions del Repeater, plantilla descargable en `/admin/polls/import-template`
- Display proyectable: `/display/polls/{id}` — Blade público, auto-refresh 3s
- QUEUE_CONNECTION=sync en `.env` local (dev sin worker)
- 18 tests Pest — 18/18 pasando

## App (completo)

- `useChat.ts`: myAnswers Record<questionId, MyAnswer>, poll:closed con closedPending, join event:{id} room
- `PollSlides.tsx`: slides 1/N, MultipleChoice (single/multi), StarRating, OpenText, botón Atrás, pantalla Gracias
- `encuestas.tsx`: módulo scope=event, lista activas/cerradas, invalidate por socket
- `ModuleMenu`: prop `badges: Record<string, boolean>` con punto verde
- `index.tsx` (presencial+virtual): badge encuestas si has_active=true

## Tareas UI pendientes (acumuladas — sesión UI post-Fase 1)

- Display `/display/polls/{id}`: cambiar de scroll a slides (una pregunta full-screen, animación, control admin)
  - Archivo: `resources/views/display/poll.blade.php`
