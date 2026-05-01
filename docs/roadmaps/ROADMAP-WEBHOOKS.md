# Roadmap — Webhooks Integracion Partners

> Integracion bidireccional con partners externos (badge printers, CRMs, etc.)
> Aprobado: 2026-04-21 (DaVinci paso 6)
> Estado: COMPLETADO — 24 tests, 60 assertions, 5 bugs post-audit corregidos, doc auto-generada
> Prioridad: P0 (non-negotiable para Bancolombia)

---

## Contexto

Bancolombia y otros clientes enterprise usan empresas externas para imprimir escarapelas/badges.
Necesitan: (1) recibir datos de registrados automaticamente, (2) avisarnos cuando alguien recoge su badge (= check-in).
Sin webhooks no hay integracion con el ecosistema del cliente.

---

## Fase 1 — Modelo de datos + Migraciones — COMPLETADA

- [x] Migration: `webhook_endpoints` (event_id, name, url, secret 64chars, events JSON, is_active, test_mode, selected_fields JSON, last_triggered_at, last_status)
- [x] Migration: `webhook_api_keys` (event_id, name, key wh_live_ 48chars, permissions JSON, rate_limit int, is_active, last_used_at)
- [x] Migration: `webhook_logs` (webhook_endpoint_id nullable, api_key_id nullable, direction outbound/inbound, event_name, payload JSON, response_status, response_body text, attempt, duration_ms, is_test boolean)
- [x] Modelo WebhookEndpoint: relacion event, cast events/selected_fields array, auto-genera secret
- [x] Modelo WebhookApiKey: relacion event, auto-genera key con prefijo `wh_live_`, cast permissions array
- [x] Modelo WebhookLog: relacion endpoint/apiKey, scope outbound/inbound/test
- [x] **Tests**: 5 tests modelo (auto-generacion secret, auto-generacion key, casts, helpers, isSuccess)

---

## Fase 2 — Outbound: EventOS envia a partners — COMPLETADA

- [x] `WebhookDispatchService::dispatch()` — busca endpoints activos, filtra por evento, filtra campos, idempotency_key ULID
- [x] `DispatchWebhookJob` — HTTP POST con HMAC-SHA256, retry 3x (1min/5min/30min), respeta 429 Retry-After, needs_attention tras 3 fallos
- [x] `AttendeeWebhookObserver` — created→registered, updated→approved/checked_in/profile, deleted→cancelled
- [x] Payload con idempotency_key, test flag, campos filtrados
- [x] 8 tests: active/inactive endpoints, event filter, selected_fields, test_mode, HMAC signature, retry+logs, observer created, observer checkin

---

## Fase 3 — Inbound: Partner envia a EventOS — COMPLETADA

- [x] `WebhookInboundController::checkin()` — auth por X-Webhook-Key, rate limit atomico, check-in por email o ID, test mode, idempotente
- [x] `WebhookInboundController::checkinBatch()` — max 100, DB::transaction, resultados parciales (ok/not_found/banned)
- [x] Rutas: POST /api/v1/webhooks/checkin + /checkin/batch (sin Sanctum, auth propia)
- [x] Retry-After header en 429 con limit/window en body
- [x] 11 tests: key valida, by ID, key invalida (401), key inactiva (401), sin permiso (403), not found (404), idempotente, banned (403), test mode, batch mixed, rate limit (429)

---

## Fase 4 — Filament Admin — COMPLETADA

### WebhookEndpointResource
- [x] Tabla: nombre, url (truncada+tooltip), eventos (badges), status (pill verde/roja/amber), test mode, activo, ultimo envio (since)
- [x] Form: nombre, url (https), eventos (CheckboxList 5 tipos), campos a enviar (CheckboxList 10 campos), toggle test_mode, toggle activo
- [x] Secret: auto-generado, accion "Copiar secret" muestra en notification
- [x] Accion "Enviar prueba" → dispara ping sync, muestra resultado ok/fail
- [x] Accion "Reenviar fallidos" → re-despacha los que fallaron en ultimas 24h (visible solo en failed/needs_attention)
- [x] Accion "Descargar spec" → TXT con URLs, keys, payload ejemplo, codigos respuesta, verificacion HMAC PHP

### WebhookApiKeyResource
- [x] Tabla: nombre, key (truncada+copyable), permisos (badges), rate limit /h, activo, ultimo uso
- [x] Form: nombre, permisos (CheckboxList: checkin/checkout), rate limit input, toggle activo
- [x] Accion "Regenerar key" → nueva key, notification con key completa

### WebhookLogResource (read-only)
- [x] Tabla: fecha, direccion (badge info/success), evento, endpoint/key, status (badge color), intento, duracion ms, test
- [x] Filtros: direccion (outbound/inbound), status group (2xx/4xx/5xx/timeout), is_test
- [x] Accion "Ver" → modal con payload JSON pretty + response body

### WebhookStatsWidget (header de lista endpoints)
- [x] Stat: enviados hoy (exitosos + %)
- [x] Stat: check-ins recibidos hoy
- [x] Stat: tasa de exito (color verde/amber/rojo)

### Simulador
- [x] Header action "Simular evento" → 5 registros fake + 3 check-ins fake al partner, logs TEST

---

## Fase 5 — Alertas + Operaciones — COMPLETADA

- [x] needs_attention: DispatchWebhookJob marca endpoint tras 3 fallos consecutivos (automatico)
- [x] `PruneWebhookLogsCommand` (`webhook:prune-logs --days=90`) — cron diario 2am
- [x] `TestWebhooksCommand` (`app:test-webhooks`) — 19 checks automaticos E2E (setup + outbound + inbound + logs)

## Bugs post-audit corregidos

| Bug | Que era | Fix |
|-----|---------|-----|
| Rate limit race condition | Cache::get + increment separados | increment atomico, TTL en primer uso |
| Sin Retry-After en 429 | Partner no sabia cuando reintentar | Header Retry-After: 3600 + body limit/window |
| Batch sin transaccion | Crash parcial = estado inconsistente | DB::transaction envuelve loop |
| Sin idempotency key | Retries duplicaban sin forma de deduplicar | ULID unico por dispatch |
| Logs sin cleanup | Tabla crece sin limite | Prune command cron diario |

---

## Resumen final

| Fase | Que | Tests |
|------|-----|-------|
| 1 | Modelos + migraciones | 5 |
| 2 | Outbound (dispatch + jobs + observers) | 8 |
| 3 | Inbound (controller + auth + rate limit) | 11 |
| 4 | Filament (3 resources + widget + spec + simulador) | — |
| 5 | Operaciones (prune + test command) | — |
| **Total** | **COMPLETADO** | **24 tests, 60 assertions** |

---

## Criterio de DONE

- [ ] Partner puede recibir datos de registrados automaticamente
- [ ] Partner puede enviar check-in y EventOS lo procesa (modulos, push, gamification)
- [ ] Organizador configura todo desde Filament sin tocar codigo
- [ ] Modo test funcional (partner prueba sin afectar datos reales)
- [ ] Logs completos de cada interaccion
- [ ] Documentacion auto-generada para entregar al partner
- [ ] 28 tests pasando
- [ ] 0 bugs pendientes

---

## Documentos relacionados

| Doc | Contenido |
|-----|-----------|
| `docs/PENDIENTES.md` | P0 — Webhooks como primera prioridad |
| `docs/ANALISIS-COMPETITIVO.md` | Bancolombia necesita integracion badges |
| `memory/project_bancolombia.md` | Requerimientos Bancolombia: webhook in/out badges |

---

_Roadmap Webhooks v1.0 — EventOS_
_21 abril 2026_
