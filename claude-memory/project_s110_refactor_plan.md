---
name: S1.10 — Plan refactor encuestas (COMPLETADO 2026-04-04)
description: 7 pasos implementados y mergeados a main — referencia histórica
type: project
---

## Estado final: ✅ TODO COMPLETADO Y MERGEADO

Branch `feature/s110-encuestas-v2` → mergeado a main en backend + app el 2026-04-04.

## Pasos completados

- [x] PASO 1 — Backend: migraciones y modelos
- [x] PASO 2 — Backend: PollController refactorizado
- [x] PASO 3 — Backend: LivePollResource Filament (+ export, import, display_url column)
- [x] PASO 4 — Socket server (poll:closed, room event:{id})
- [x] PASO 5 — App: useChat.ts refactorizado
- [x] PASO 6 — App: PollSlides component
- [x] PASO 7 — App: módulo Encuestas scope=event
- [x] EXTRA — ExportPollResponsesJob con CSV por asistente + notificación campana
- [x] EXTRA — Import CSV con plantilla descargable
- [x] EXTRA — Display proyectable /display/polls/{id}
- [x] EXTRA — notifications table + databaseNotifications Filament
- [x] 18 tests Pest pasando

## Nota técnica importante

`finfo` PHP extension no disponible en Laragon Windows → `Storage::disk('public')->put()` falla.
Fix: usar `file_put_contents(storage_path('app/public/...'), $content)` directo.

## Why

PC volátil — guardado como referencia histórica de decisiones de la sesión.
