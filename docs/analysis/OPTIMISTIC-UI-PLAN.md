# Plan de Implementacion — Optimistic UI EventOS

> Plan priorizado por semanas. Solo audit y plan, no implementacion.
> Referencia: BRIEF-CLAUDE-CODE-OPTIMISTIC-UI.md, OPTIMISTIC-UI-AUDIT.md, GAPS-ANALYSIS.md
> Generado: 2026-04-25

---

## Seccion A — Inventario rapido

| # | Accion | Optimistic hoy? | Nivel | Gap? |
|---|--------|-----------------|-------|------|
| A1 | Favoritar sesion | Si | 1 | Solo haptic |
| A2 | Favoritar sponsor | Si | 1 | Solo haptic |
| A3 | Like wall post | Si | 1 | Solo haptic |
| A4 | Comentar wall | Si | 2 | Dedup socket |
| A5 | Publicar wall post | No | 2 | Optimistic + dedup |
| A6 | Like foto | Si (callback manual) | 1 | Solo haptic |
| A7 | Chat send | No | 2 | GAP CRITICO |
| A8 | Emoji chat | No | 1 | Skip-self |
| A9 | Pregunta Q&A | Parcial | 2 | onMutate falta |
| A10 | Upvote Q&A | Si | 1 | Parpadeo socket |
| A11 | Votar poll | Si | 2 | Completo |
| A12 | Rating sesion | No (callback, correcto) | 3 | N/A |
| A13 | Rating speaker | No (callback, correcto) | 3 | N/A |
| A14 | Networking request | Si | 2 | Solo haptic |
| A15 | Responder networking | Si | 2 | Completo |
| A16 | Bloquear attendee | No | 3 | N/A (correcto) |
| A17 | Desbloquear | Si | 1 | Completo |
| A18 | Canjear reward | No | 3 | N/A (correcto) |
| A19 | Check-in QR | N/A | 3 | N/A |
| A20 | Publicar foto | No (multipart, correcto) | 3 | N/A |
| A21 | Visitar stand | N/A (auto tracking) | N/A | N/A |
| A22 | Update lead | No | 1 | Gap |
| A23 | Scan lead | NECESITO CONFIRMACION | 3 | N/A |
| A24 | Soporte/contacto | No | 3 | N/A |
| A25 | Update perfil | No (callback) | 2 | Gap menor |
| A26 | Publicar story | No (multipart, correcto) | 3 | N/A |
| A27 | Aceptar staff invite | No | 3 | N/A (correcto) |
| A28 | Attendance check | No | 3 | N/A (correcto) |
| A29 | Trivia answer | Parcial (server-auth, correcto) | 3 | N/A |
| A30 | Update intereses | No (callback) | 1 | Gap menor |

**Resumen:** 30 acciones, 10 con optimistic completo, 4 con gaps reales (chat, emoji, Q&A submit, wall post), 3 con gaps menores (lead update, perfil, intereses), 10 correctamente sin optimistic (nivel 3), 2 server-authoritative correctos, 1 tracking automatico. 0 con haptic feedback. 0 retry automatico.

---

## Seccion B — Quick Wins (Semana 1)

Acciones REST de alto impacto, bajo riesgo, donde el patron es trivial.
Todas siguen patron 4.1 del brief (useMutation con onMutate).

### PR-1: Haptic feedback universal

**Titulo:** feat(mobile): haptic feedback en todas las acciones existentes

**Archivos a modificar:**
- `eventos-app/hooks/useAgenda.ts` — agregar `Haptics.impactAsync(ImpactFeedbackStyle.Light)` en onMutate (linea ~148)
- `eventos-app/hooks/useNetworking.ts` — en onMutate de sendRequest (linea ~65), respondRequest (linea ~110)
- `eventos-app/hooks/useSponsors.ts` — en onMutate (linea ~24)
- `eventos-app/hooks/useWall.ts` — en toggleLike onMutate (linea ~146), addComment onMutate (linea ~176)
- `eventos-app/hooks/useQnA.ts` — en upvote (linea ~128)
- `eventos-app/hooks/useChat.ts` — en votePoll (linea ~296)
- `eventos-app/hooks/usePhotos.ts` — en like/unlike callback (linea ~98)
- `eventos-app/hooks/useSessionRating.ts` — en submit callback (linea ~34), usar Medium
- `eventos-app/hooks/useSpeakerRating.ts` — en submit callback (linea ~34), usar Medium

**Approach:**
- Import `* as Haptics from 'expo-haptics'`
- Una linea por mutacion: `Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light)`
- Para acciones destructivas (bloquear, ban): `Haptics.notificationAsync(NotificationFeedbackType.Warning)`

**Tests:** Manual en device fisico. Los haptics no se sienten en simulador.

**Validacion:** Comparar grabacion de pantalla antes/despues. La percepcion de "instantaneidad" mejora neurologicamente con haptics aunque la latencia real no cambie.

**Nota:** scanner-stand.tsx (A23) y AttendanceCheckModal.tsx (A28) YA tienen haptic feedback. No tocar.

**Estimado:** ~30 minutos. 9 archivos, 1 linea por archivo.

---

### PR-2: Update lead optimistic

**Titulo:** feat(mobile): optimistic update en edicion de leads

**Archivos a modificar:**
- `eventos-app/hooks/useLeads.ts` — agregar onMutate con patch optimista (actualmente solo tiene onSuccess en linea 19-23)

**Approach:**
- Seguir patron identico a useSponsors.ts:21-28
- onMutate: cancelar query 'leads', obtener previous, patchear lead en cache, retornar previous
- onError: revertir a previous
- onSettled: invalidar 'leads'

**Tests:**
- Cambiar tier de lead a "hot" → debe verse inmediato
- Simular error de red → debe revertir a tier anterior
- Verificar que cache de leads se actualiza sin refetch

**Validacion:** Medir tiempo entre tap y cambio visual. Antes: 400-800ms. Despues: <50ms.

**Estimado:** ~20 minutos.

---

### PR-3: Retry automatico en API client

**Titulo:** feat(mobile): retry automatico para errores transitorios

**Archivos a modificar:**
- `eventos-app/lib/api.ts` — agregar logica de retry en la funcion principal de request (alrededor de linea 103-109 donde maneja network errors)

**Approach:**
- Envolver la llamada fetch en un retry loop (max 2 reintentos)
- Solo reintentar en: network error (timeout/offline), 502, 503, 504
- NO reintentar en: 400, 401, 403, 404, 409, 422, 429
- Backoff: 500ms primer retry, 1500ms segundo retry
- Flag `_retryCount` para evitar loops infinitos
- Jitter aleatorio de 0-200ms para evitar thundering herd

**Tests:**
- Simular timeout → debe reintentar 1 vez despues de 500ms
- Simular 502 → debe reintentar
- Simular 422 → NO debe reintentar
- Verificar que retry count se loggea en Sentry

**Validacion:** Durante stress test de failover (TEST 7), medir cuantos errores llegan al usuario vs cuantos se rescatan via retry.

**Estimado:** ~45 minutos.

---

### PR-4: Pregunta Q&A optimistic (A9)

**Titulo:** feat(mobile): optimistic submit de preguntas Q&A

**Archivos a modificar:**
- `eventos-app/hooks/useQnA.ts` — convertir submitQuestion de onSuccess a onMutate pattern (lineas 109-123)

**Approach:**
- onMutate: insertar pregunta con ID temporal (`temp_${Date.now()}`), status "pending_approval"
- onSuccess: reemplazar pregunta temporal con respuesta del server (con ID real)
- onError: remover pregunta temporal, toast "No se pudo enviar"
- La pregunta aparece en "mis preguntas" con badge "pendiente" inmediatamente

**Tests:**
- Enviar pregunta → debe aparecer inmediato con status pending
- Simular error → debe desaparecer con toast
- Verificar que no se duplica cuando llega `question:approved` del socket (dedup por ID ya existe en linea 66)

**Riesgos:**
- Blocked words: server retorna 201 con `{ data: { id: 0, status: 'pending' } }` sin crear la pregunta (QuestionController.php linea 79). El cliente no puede distinguir entre "guardada pendiente de aprobacion" y "silenciosamente bloqueada"
- **Pre-requisito backend:** Cambiar QuestionController@store para retornar 422 con `{ error: 'blocked_content' }` cuando detecta palabra bloqueada, en vez de 201 fake. Esto permite rollback limpio del optimista en onError

**Estimado:** ~30 minutos.

---

### PR-5: Dedup de wall:comment con optimistic existente (A4)

**Titulo:** fix(mobile): dedup de comentarios wall con broadcast socket

**Archivos a modificar:**
- `eventos-app/hooks/useWall.ts` — en el listener de `wall:comment` (linea 75-80), agregar check de senderId

**Approach:**
- El comentario optimista ya existe (useWall.ts:174-191) con tempId = -Date.now()
- El broadcast `wall:comment` llega via socket con el comment real (ID positivo)
- Solucion: en el listener, si el comment.attendee_id === myAttendeeId Y ya existe un comment con tempId negativo en los ultimos 5s, reemplazar el optimista con el real en vez de agregar

**Tests:**
- Publicar comentario → debe aparecer 1 vez (no 2)
- Verificar que comentarios de otros usuarios SI se agregan

**Estimado:** ~20 minutos.

---

## Seccion C — Socket Simple (Semana 2)

### PR-6: Emoji chat con skip-self (A8)

**Titulo:** feat(socket+mobile): emoji reactions con skip-self y optimistic local

**Archivos a modificar:**
- `eventos-socket/src/chat.ts` — cambiar `io.to()` a `socket.to()` en linea 444
- `eventos-app/hooks/useChat.ts` — insertar emoji en estado local antes de socket.emit

**Approach:**
- Server: `socket.to(Rooms.chat(sessionId)).emit('chat:emoji', payload)` (skip-self)
- Cliente: mostrar emoji inmediato en local, server solo envia a otros
- El sender no necesita confirmacion para emojis (fire-and-forget)
- Haptic feedback: `Haptics.impactAsync(Light)`

**Tests:**
- Enviar emoji → aparece local inmediato
- Otro device en misma sesion → recibe emoji
- Sender NO recibe su propio emoji de vuelta del server

**Estimado:** ~30 minutos.

---

### PR-7: Q&A upvote anti-parpadeo (A10)

**Titulo:** fix(mobile): preservar estado local de upvote cuando llega broadcast

**Archivos a modificar:**
- `eventos-app/hooks/useQnA.ts` — en listener de `question:upvoted` (linea 87-97)

**Approach:**
- Cuando llega `question:upvoted`, actualizar `upvotes` count del server PERO preservar `my_upvote` local
- Actualmente el listener pisa todo el objeto, incluyendo my_upvote
- Fix: merge selectivo `{ ...localQuestion, upvotes: serverQuestion.upvotes }`

**Tests:**
- Upvotear pregunta → ver incremento inmediato
- Cuando llega broadcast → no debe parpadear ni perder my_upvote

**Estimado:** ~15 minutos.

---

### PR-8: Wall post dedup para futuro optimistic (A5)

**Titulo:** feat(mobile): dedup de wall:post para soportar optimistic futuro

**Archivos a modificar:**
- `eventos-app/hooks/useWall.ts` — en listener de `wall:post` (linea 68-74)

**Approach:**
- Agregar senderId check: si el post.attendee_id === myAttendeeId, ignorar (el optimista o el onSuccess ya lo tiene)
- Esto prepara el terreno para agregar onMutate al store() sin causar duplicados

**Tests:**
- Publicar post → no aparece duplicado
- Post de otro usuario → aparece via socket normalmente

**Estimado:** ~15 minutos.

---

## Seccion D — Socket Complejo (Semana 3)

### PR-9: Chat con TempId y estados progresivos (A7) — EL CAMBIO PRINCIPAL

**Titulo:** feat(socket+mobile): chat optimistic con tempId y estados progresivos

**Fase 1 — Server acepta tempId del cliente:**

Archivos:
- `eventos-socket/src/types.ts` — agregar `tempId?: string` a ClientToServerEvents['chat:send']
- `eventos-socket/src/chat.ts` — usar tempId del cliente si viene, sino generar server-side (linea 363). Preservar en ChatMessagePayload

Approach:
- `chat:send` payload cambia de `{ sessionId, message }` a `{ sessionId, message, tempId? }`
- Si tempId viene del cliente, usarlo como `payload.id`
- Si no viene (cliente viejo), generar como hoy — retrocompatible
- El broadcast `chat:message` ya incluye el tempId en el campo `id`

**Fase 2 — Ack callback:**

Archivos:
- `eventos-socket/src/chat.ts` — agregar ack callback al handler de `chat:send` (linea 325)
- `eventos-socket/src/types.ts` — agregar callback type

Approach:
- `chat:send` pasa a tener ack: `(data, callback) => { ... callback({ ok: true, dbId }) }`
- Callback confirma persistencia y devuelve dbId
- Si blocked words: `callback({ ok: false, reason: 'blocked' })`
- Si rate limited: `callback({ ok: false, reason: 'rate_limited', retryAfter: seconds })`
- Timeout de 5s en el cliente

**Fase 3 — Cliente optimistic:**

Archivos:
- `eventos-app/hooks/useChat.ts` — reescribir sendMessage

Approach (patron 4.2 del brief):
```
1. const tempId = `temp_${Date.now()}_${nanoid(6)}`
2. Insertar mensaje local con status='sending'
3. Haptic feedback
4. socket.timeout(5000).emit('chat:send', { sessionId, message, tempId }, ack)
5. ack ok: actualizar status='sent', guardar dbId
6. ack error blocked: remover mensaje, toast
7. ack error rate_limited: remover mensaje, toast con countdown
8. timeout: status='failed', boton retry
```

**Fase 4 — Dedup en listener:**

Archivos:
- `eventos-app/hooks/useChat.ts` — modificar listener de `chat:message` (linea 160-164)

Approach:
- Al recibir `chat:message`, chequear si `messages.some(m => m.tempId === msg.tempId || m.tempId === msg.id)`
- Si existe: ignorar (ya lo tenemos local)
- Si no existe: agregar (mensaje de otro usuario)

**Tests criticos:**
- Enviar mensaje → aparece inmediato con status "sending"
- Server confirma → status cambia a "sent"
- Otro device → recibe el mensaje
- Blocked word → mensaje desaparece con toast
- Rate limited → toast con countdown
- Red caida → status "failed" despues de 5s, boton retry
- Dos mensajes rapidos → rate limit local previene envio (conocer slow_mode_seconds)

**Riesgos:**
- Mas complejo que los otros PRs. Requiere coordinacion mobile + socket server
- El ack callback es un cambio de protocolo. Clientes viejos sin ack deben seguir funcionando
- El slow_mode_seconds debe propagarse al cliente (ya viene en `session:config_updated`)

**Estimado:** 2-3 horas total (las 4 fases).

---

## Seccion E — Cross-cutting Concerns

### E1. Wrapper estandarizado para useMutation optimistic

**Archivo nuevo:** `eventos-app/hooks/useOptimisticMutation.ts`

**Proposito:** Wrapper sobre useMutation de React Query que incluye:
- Haptic feedback automatico (configurable: Light, Medium, Warning)
- Patron onMutate/onError/onSettled estandarizado
- Toast automatico en onError con mensaje configurable
- Integracion con Sentry para tracking de rollback rate

**Uso:**
```typescript
const mutation = useOptimisticMutation({
  mutationFn: (id) => api.favorite(id),
  queryKey: ['favorites'],
  optimisticUpdate: (old, id) => [...old, id],
  haptic: 'light',
  errorMessage: 'No se pudo guardar',
})
```

**Justificacion:** Evitar duplicar el patron onMutate + haptic + toast en 10+ hooks.

### E2. Hook para socket emit con ack + timeout

**Archivo nuevo:** `eventos-app/hooks/useSocketEmit.ts`

**Proposito:** Wrapper sobre socket.emit que incluye:
- Timeout configurable (default 5s)
- Retry automatico 1 vez si timeout
- Callback de error tipado
- Tracking de latencia en Sentry

**Uso para chat:**
```typescript
const { emit } = useSocketEmit()
emit('chat:send', { sessionId, message, tempId }, {
  timeout: 5000,
  onAck: (response) => { ... },
  onTimeout: () => { ... },
})
```

### E3. Sistema de toast unificado para rollbacks

**Requisito:** Los toasts de rollback NO deben ser intrusivos. El usuario toco un boton, vio el cambio, y 500ms despues el toast dice "No se pudo guardar". Debe ser sutil:
- Duracion: 2s
- Posicion: bottom, no modal
- Sin boton de accion (excepto retry en chat failed)
- Color: surface neutral, no rojo alarmante

### E4. Helper de Haptics

**Archivo nuevo:** `eventos-app/lib/haptics.ts`

**Funciones:**
- `hapticLight()` — acciones reversibles (like, favorite, upvote)
- `hapticMedium()` — acciones de confirmacion (send message, submit)
- `hapticWarning()` — acciones destructivas (block, delete)
- `hapticSuccess()` — confirmaciones exitosas (reward redeemed)

**Justificacion:** Centralizar para poder ajustar intensidad globalmente.

---

## Seccion F — Metricas a instrumentar

### F1. Sentry Performance Transactions

| Transaction | Que mide | Donde |
|-------------|----------|-------|
| `action.favorite` | Tap → visual change | useAgenda, useSponsors |
| `action.like` | Tap → visual change | useWall |
| `action.chat_send` | Tap → message appears | useChat |
| `action.upvote` | Tap → count change | useQnA |
| `action.network_request` | Tap → status change | useNetworking |

### F2. Custom metrics

| Metrica | Tipo | Que indica |
|---------|------|------------|
| `optimistic.rollback_rate` | Counter | % de acciones optimistas que fallan y revierten |
| `optimistic.retry_success_rate` | Counter | % de retries automaticos que rescatan un error |
| `chat.perceived_latency` | Histogram | Tiempo entre tap y mensaje visible en pantalla |
| `chat.server_latency` | Histogram | Tiempo entre emit y ack |
| `api.retry_count` | Counter | Cuantos requests necesitaron retry |

### F3. Thresholds de alerta

| Metrica | Warning | Critico |
|---------|---------|---------|
| Rollback rate | > 1% | > 5% |
| Chat perceived latency p95 | > 200ms | > 500ms |
| API retry rate | > 2% | > 10% |

---

## Seccion G — Riesgos y mitigaciones

### G1. Riesgo: Parpadeo por socket invalidation pisando optimistic

**Probabilidad:** Media
**Impacto:** Bajo (visual, no funcional)
**Mitigacion:** Para acciones que disparan broadcast (wall post/comment, Q&A), implementar senderId check o tempId reconciliacion ANTES de activar optimistic UI
**Deteccion:** Visual en testing manual. Automatizable con snapshot tests de estado de React Query

### G2. Riesgo: Chat duplicado durante migracion a tempId

**Probabilidad:** Media-alta durante el deploy
**Impacto:** Medio (mensajes duplicados visibles)
**Mitigacion:** Deploy escalonado — primero server (acepta tempId opcional), luego mobile. Ambos deben ser retrocompatibles
**Deteccion:** QA manual con 2 devices. Uno con version vieja, otro con nueva. Verificar chat funciona en ambos

### G3. Riesgo: Blocked words con optimistic muestra mensaje que desaparece

**Probabilidad:** Baja (pocos mensajes bloqueados)
**Impacto:** Medio (usuario ve su mensaje y luego desaparece, confuso)
**Mitigacion:** Con ack callback, el server notifica "blocked" y el cliente marca como "no enviado" en vez de desaparecer silenciosamente
**Deteccion:** Test manual con palabra bloqueada

### G4. Riesgo: Race condition en reward redeem con optimistic accidental

**Probabilidad:** Baja (ya esta marcado como Nivel 3)
**Impacto:** Alto (doble deduccion de puntos)
**Mitigacion:** Mantener Nivel 3 (loading explicito). NO agregar optimistic a rewards nunca. El lockForUpdate del server protege
**Deteccion:** Test de stress con 2 taps rapidos

### G5. Riesgo: MMKV cache + optimistic update + app restart

**Probabilidad:** Baja
**Impacto:** Medio (estado stale despues de restart)
**Mitigacion:** Los optimistic updates son in-memory en React Query, no se persisten a MMKV. Al reiniciar, React Query refetcha del server. No hay conflicto
**Deteccion:** Test: hacer accion optimista, force-close app, reabrir. Verificar estado correcto

---

## Orden de implementacion recomendado

```
Semana 1 (5 PRs, ~2.5 horas total):
  PR-1: Haptic feedback universal (30 min)
  PR-2: Update lead optimistic (20 min)
  PR-3: Retry automatico API client (45 min)
  PR-4: Pregunta Q&A optimistic (30 min)
  PR-5: Dedup wall:comment (20 min)

Semana 2 (3 PRs, ~1 hora total):
  PR-6: Emoji skip-self (30 min)
  PR-7: Q&A upvote anti-parpadeo (15 min)
  PR-8: Wall post dedup (15 min)

Semana 3 (1 PR grande + cross-cutting, ~4 horas total):
  PR-9: Chat tempId + ack + estados progresivos (2-3 horas)
  E1-E4: Wrappers, helpers, metricas (1 hora)

Post-semana 3:
  Instrumentar Sentry Performance (F1-F3)
  Validar con device real en 4G
  Stress test con optimistic UI activo
```

---

## Dependencias entre PRs

```
PR-1 (haptics) ← independiente, puede ir primero
PR-2 (leads) ← independiente
PR-3 (retry) ← independiente, beneficia a todos
PR-4 (Q&A) ← independiente pero requiere confirmacion de backend (blocked words)
PR-5 (wall comment dedup) ← independiente

PR-6 (emoji) ← requiere deploy de socket server
PR-7 (upvote) ← independiente
PR-8 (wall post dedup) ← independiente, prepara para PR futuro de wall post optimistic

PR-9 (chat tempId) ← requiere PR-6 desplegado (familiaridad con cambios socket)
                    ← requiere PR-3 desplegado (retry para fallback)

E1-E4 ← pueden ir en paralelo con semana 2, se usan en semana 3
```

---

_OPTIMISTIC-UI-PLAN.md — EventOS | 2026-04-25_
_Fase 4 completada._
