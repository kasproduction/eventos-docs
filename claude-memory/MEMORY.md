# Memory Index — EventOS (Kasproduction)

## Proyecto
- [project_eventos.md](project_eventos.md) — Stack completo, bundle ID, idiomas, versiones mínimas, documento maestro v2.5
- [project_scope_decisions.md](project_scope_decisions.md) — Qué está en Fase 1 (MVP activo), qué aplazado a Fase 2/3 y por qué
- [project_architecture_decisions.md](project_architecture_decisions.md) — Decisiones técnicas clave: auth, storage, real-time, PDFs, API, infra. No cambiar sin revisar esto.
- [project_dependencies.md](project_dependencies.md) — Qué librería se instala en qué sesión (dependencias progresivas, no todo al inicio)
- [project_documents.md](project_documents.md) — Rutas locales, repos GitHub, estado de sesiones (S1.5 completa, S1.6 pendiente al 2026-03-29)
- [project_s16_notes.md](project_s16_notes.md) — Arquitectura S1.6: sponsors=stands, stand_members, lead_edits, has_vendor_access, flujo invitación, pool leads
- [project_s110_notes.md](project_s110_notes.md) — S1.10 encuestas: modelo correcto (live_poll_questions), 4 tipos de pregunta, UX slides, estado V1 parcial pendiente refactorizar
- [project_s110_refactor_plan.md](project_s110_refactor_plan.md) — Plan paso a paso del refactor S1.10 (7 pasos), branch feature/s110-encuestas-v2, estado al 2026-04-04
- [project_backend_notes.md](project_backend_notes.md) — Notas técnicas del backend: BelongsToMany FK explícitos, ContentObserver, htmlpurifier en Windows
- [project_expo_notes.md](project_expo_notes.md) — Notas técnicas Expo SDK 55: MMKV v4, FlashList v2, expo-file-system/legacy, expo-camera CameraView, rutas tipadas as any
- [project_socket_notes.md](project_socket_notes.md) — Notas técnicas del socket server: ioredis v5 API, Redis DB 2, auth depende de Sesión 1.1, puerto 3001
- [project_auth_notes.md](project_auth_notes.md) — HASH_DRIVER tests, UserFactory plaintext, Pest v3, QR HMAC-SHA256, EXPO_PUBLIC_API_URL
- [project_modules_notes.md](project_modules_notes.md) — phpunit SQLite habilitado, OrganizationFactory plan='starter', AuthUser.eventId, cache key módulos
- [project_ui_notes.md](project_ui_notes.md) — Referencias design/ (dark mode Fever + cards), sesión UI post-Fase 1, primary_color dinámico por evento

## Feedback (cómo trabajar con el usuario)
- [feedback_git_workflow.md](feedback_git_workflow.md) — Git con commits confirmados por el usuario tras cada feature verificado
- [feedback_approach.md](feedback_approach.md) — Memorias como log (no sobreescribir), cubrir escenarios antes de codear, deps progresivas
- [feedback_run_commands.md](feedback_run_commands.md) — Ejecutar comandos bash/composer/artisan directamente, no pedirle al usuario que los corra
