# W.4 — Streaming + Q&A + Chat + Polls + Trivia + Anuncios in-stream + Replay

> Experiencia virtual core. Vimeo embed + Q&A + chat + polls + Trivia Kahoot-style + anuncios in-stream + replay grabaciones post-evento. Modulo mas complejo de la webapp.
>
> **Estimacion:** ~14h (expandida de 12h por Trivia + anuncios + replay).
> **Dependencias:** W.0, W.1, W.3 (agenda da contexto sesion), W.16 (Trivia comparte engine).
> **Estado:** Pendiente.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`, `AUTH-SPEC.md`
- `W.0-spatial-ui.md` (panels)
- App movil: `screens/streaming/` + Q&A + chat + polls — patrones validados
- `eventos-socket/` — eventos socket existentes (no inventar)

---

## Alcance

1. Vimeo player embebido (Vimeo Player SDK) — live + replay si grabacion disponible
2. Q&A live: ver preguntas + upvote + enviar pregunta + moderacion
3. Chat live: mensajes con tempId (optimistic) + ack
4. Polls live: votar encuestas activas
5. **Trivia Kahoot-style**: preguntas interactivas con tiempo + bonus points (delegado a W.16, aqui solo integracion del panel)
6. **Anuncios in-stream**: si organizador envia anuncio durante sesion, overlay sutil sobre player + en chat
7. **Replay**: si sesion termino y tiene grabacion, mostrar player con grabacion + transcript opcional
8. Layout spatial: player primario + chat secondary + Q&A secondary (3 paneles desktop)
9. Responsive:
   - Mobile: stack (player full / tabs Q&A/Chat/Polls/Trivia debajo)
   - Tablet: player + 1 panel a la vez con tabs internos
   - Desktop: spatial 3 panels

---

## Refs visuales

- App movil streaming layout (`features/Screenshot 2026-...`)
- `design/showcase-onboarding-v6.html` `str-wrap` — concepto

---

## Endpoints (verificar)

- `GET /api/v1/event/{id}/sessions/{sessionId}/stream` — config Vimeo + permisos + replay_url si aplica
- `GET /api/v1/sessions/{id}/questions` — Q&A list
- `POST /api/v1/sessions/{id}/questions` — enviar pregunta
- `POST /api/v1/sessions/{id}/questions/{qid}/upvote` — upvote
- `GET /api/v1/sessions/{id}/messages?after=cursor` — chat paginado
- `POST /api/v1/sessions/{id}/messages` — enviar mensaje
- `GET /api/v1/sessions/{id}/polls/active` — encuestas activas
- `POST /api/v1/polls/{id}/vote` — votar
- `GET /api/v1/sessions/{id}/trivia/active` — trivia activa (W.16 detalle)
- `POST /api/v1/trivia/{id}/answer` — responder
- `GET /api/v1/sessions/{id}/announcements?stream=true` — anuncios in-stream
- `GET /api/v1/sessions/{id}/replay` — grabacion si disponible (URL Vimeo + transcript opcional)

Socket events:
- `chat.message.new`, `chat.message.banned`
- `qa.question.new`, `qa.question.upvoted`, `qa.question.approved`
- `poll.activated`, `poll.results.updated`, `poll.closed`
- `trivia.question.activated`, `trivia.question.closed`
- `stream.announcement.broadcast` (anuncio in-stream)

---

## Fase 0 — Setup (~30min) — 0/2

### 0.1 Vimeo SDK — 0/2
- [ ] `pnpm add @vimeo/player`
- [ ] Wrapper component `<VimeoPlayer videoId={...} />` con manejo de eventos (play/pause/ended)

---

## Fase 1 — Player layout (~1.5h) — 0/4

### 1.1 Componente — 0/2
- [ ] `<StreamingPanel />` con `<VimeoPlayer />` + indicador "EN VIVO" + viewer count
- [ ] CSP whitelist `*.vimeo.com` validado

### 1.2 Responsive — 0/2
- [ ] Mobile: aspect-ratio 16/9 full width arriba
- [ ] Desktop: panel principal con player aspect 16/9

---

## Fase 2 — Q&A panel (~3h) — 0/7

### 2.1 Lista — 0/3
- [ ] `<QAList />` con preguntas ordenadas por upvotes desc
- [ ] Cada pregunta: avatar + nombre + texto + count upvotes + boton heart
- [ ] Filtros: "Todas", "Mas votadas", "Mias"

### 2.2 Enviar pregunta — 0/2
- [ ] Input + boton "Enviar" (max 280 chars)
- [ ] Estado pending (esperando moderacion) si evento tiene moderacion activa

### 2.3 Upvote — 0/2
- [ ] Optimistic update count
- [ ] Anti-parpadeo si llega socket update en transicion

---

## Fase 3 — Chat panel (~3h) — 0/7

### 3.1 Lista — 0/3
- [ ] `<ChatPanel />` con mensajes scrollable
- [ ] Cada mensaje: avatar + nombre + texto + timestamp
- [ ] Auto-scroll al final cuando llega nuevo mensaje

### 3.2 Enviar — 0/3
- [ ] Input + boton enviar
- [ ] Optimistic con tempId + estado "enviando" → "enviado" → "fallido"
- [ ] Si fallo: tap para reintentar

### 3.3 Moderacion — 0/1
- [ ] Si banned, mostrar toast "Estas banneado del chat" + deshabilitar input

---

## Fase 4 — Polls panel (~2h) — 0/5

### 4.1 Lista activa — 0/2
- [ ] `<PollsPanel />` muestra poll activo (siempre 1 a la vez)
- [ ] Header con titulo + tiempo restante

### 4.2 Voting — 0/2
- [ ] Click opcion → mutation vote → muestra resultados con barra de progreso
- [ ] Si ya voto: mostrar resultados directamente

### 4.3 Empty — 0/1
- [ ] Si no hay poll activo: empty state "Sin encuestas activas"

---

## Fase 4.5 — Trivia Kahoot integration (~1.5h) — 0/4

### 4.5.1 Panel Trivia — 0/2
- [ ] `<TriviaPanel />` (componente delegado a W.16) montado como tab interno cuando trivia activa
- [ ] Socket `trivia.question.activated` → switch tab automatico a Trivia + sound alert (con mute toggle)

### 4.5.2 UX — 0/2
- [ ] Pregunta + 4 opciones + countdown bar
- [ ] Detalle completo en `W.16-live-moments.md` Fase 1

---

## Fase 4.6 — Anuncios in-stream (~30min) — 0/3

### 4.6.1 Overlay sobre player — 0/2
- [ ] `<StreamAnnouncementOverlay />` slide-down sutil sobre player con texto + 3s auto-dismiss
- [ ] Socket `stream.announcement.broadcast` → trigger overlay

### 4.6.2 En chat — 0/1
- [ ] Mismo anuncio aparece como mensaje pinned en chat (estilo system message)

---

## Fase 4.7 — Replay grabacion (~1h) — 0/4

### 4.7.1 Detection — 0/2
- [ ] Si `session.status === 'ended'` y `replay_url !== null` → mostrar player con grabacion
- [ ] Indicador "GRABACION" en lugar de "EN VIVO"

### 4.7.2 Controles — 0/2
- [ ] Player tiene controles full (play, pause, seek, speed) — en live solo play/pause
- [ ] Transcript opcional si backend lo provee (sidebar derecha o toggle)

---

## Fase 5 — Layout spatial (~1.5h) — 0/4

### 5.1 Desktop — 0/2
- [ ] Player primary (60% width) + Chat secondary (20%) + Q&A secondary (20%)
- [ ] Tab interna en panel secondary para alternar Q&A / Polls

### 5.2 Mobile/tablet — 0/2
- [ ] Mobile: player full width + bottom tabs (Chat / Q&A / Polls)
- [ ] Tablet: player full width + 1 panel debajo con tabs

---

## Fase 6 — Tests (~1.5h) — 0/5

### 6.1 Vitest — 0/2
- [ ] Optimistic chat con tempId + ack
- [ ] Optimistic upvote anti-parpadeo

### 6.2 Playwright — 0/3
- [ ] Happy path: ver stream + enviar Q&A + chat + votar poll
- [ ] Edge case: chat banned, input deshabilitado
- [ ] Edge case: stream offline, mensaje correcto

---

## Edge cases

- [ ] Vimeo videoId invalido → fallback "Stream no disponible"
- [ ] User sin permiso (sesion premium no comprada) → CTA "Acceder con codigo"
- [ ] Conexion lenta → buffering visible, no parpadeo
- [ ] Poll cierra mientras user esta votando → mensaje "Encuesta cerrada"
- [ ] Q&A moderada: pregunta enviada con estado "Pendiente aprobacion"
- [ ] Chat con flood control activo → mensaje "Espera X segundos para enviar"
- [ ] Multiple polls en rapida sucesion → solo se muestra el activo
- [ ] User abandona panel y vuelve → auto-reconnect socket sin duplicar mensajes
- [ ] CSP bloquea Vimeo → log Sentry + mensaje "Habilita JavaScript de Vimeo"

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports + Edge corporativo simulado (firewall WebSockets)
- [ ] Lighthouse OK
- [ ] Commit DaVinci + memoria + PENDIENTES.md
