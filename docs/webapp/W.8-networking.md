# W.8 — Networking

> Perfiles de asistentes + solicitudes de contacto + chat 1:1.
>
> **Estimacion:** ~7h.
> **Dependencias:** W.0, W.1, W.6 (puede compartir UI patterns con Social Wall — pantalla "Connect" agrupa ambos visualmente).
> **Estado:** Pendiente.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- `W.0-spatial-ui.md`
- App movil: `screens/networking/` — RT, glass cards, infinite scroll
- Memoria: `project_networking_notes.md`, `project_s118_notes.md` (matchmaking)

---

## Alcance

1. Directorio asistentes con filtros (intereses, empresa, rol)
2. Perfil completo otro asistente
3. Matchmaking con score (overlap intereses + sesiones comunes)
4. **Suggested contacts**: top 5 sugerencias rankeadas con razones explicitas
5. Solicitudes de contacto (enviar/aceptar/rechazar)
6. **Sent requests tracking**: ver mis solicitudes enviadas + estado pendiente/aceptada/rechazada
7. Chat 1:1 con conexiones aceptadas
8. **Bookmarks**: marcar attendee como bookmark (sin enviar solicitud) — para revisar mas tarde
9. **Blocked list**: bloquear attendee → no aparece en directorio + no puede enviar solicitud
10. Mi perfil editable (foto, bio, intereses)

---

## Refs visuales

- App movil networking (`features/Screenshot 2026-...`)
- `design/showcase-onboarding-v6.html` `conn-frame` — concepto match cards

---

## Endpoints (verificar)

- `GET /api/v1/event/{id}/attendees?intereses&q&cursor`
- `GET /api/v1/attendees/{id}` — perfil
- `GET /api/v1/event/{id}/matchmaking?limit=5` — top sugerencias rankeadas con razones
- `POST /api/v1/attendees/{id}/connect` — solicitud
- `POST /api/v1/connections/{id}/accept`
- `POST /api/v1/connections/{id}/reject`
- `GET /api/v1/me/connections/sent?status=pending` — solicitudes enviadas
- `GET /api/v1/connections/{id}/messages?cursor`
- `POST /api/v1/connections/{id}/messages`
- `POST /api/v1/attendees/{id}/bookmark` — toggle bookmark
- `GET /api/v1/me/bookmarks` — mis bookmarks
- `POST /api/v1/attendees/{id}/block` — bloquear
- `GET /api/v1/me/blocked` — mi blocked list
- `POST /api/v1/attendees/{id}/unblock`

Socket events:
- `connection.request.new`, `connection.accepted`
- `chat.dm.message.new`

---

## Fase 0 — Hooks (~30min) — 0/3

- [ ] `useAttendees(eventId, filters)` — infinite query
- [ ] `useMatchmaking(eventId)` — sugerencias rankeadas
- [ ] `useConnection(connectionId)` con mensajes

---

## Fase 1 — Directorio (~2h) — 0/4

### 1.1 Grid — 0/2
- [ ] `<AttendeesGrid />` con cards (avatar + nombre + role + intereses)
- [ ] Click → perfil

### 1.2 Filtros — 0/2
- [ ] Pills intereses (multi-select)
- [ ] Search + dropdown empresa/rol

---

## Fase 2 — Matchmaking (~1.5h) — 0/3

### 2.1 Sugerencias — 0/2
- [ ] `<MatchmakingSection />` cards top 5 con badge "Match {score}%"
- [ ] Hover/tap muestra razones (X intereses comunes, Y sesiones comunes)

### 2.2 CTA — 0/1
- [ ] Boton "Conectar" directo

---

## Fase 3 — Perfil (~1h) — 0/3

### 3.1 Layout — 0/2
- [ ] Desktop: panel secondary 50%
- [ ] Mobile: full screen

### 3.2 Contenido — 0/1
- [ ] Avatar grande + nombre + role + bio + intereses + sesiones favoritas + redes + CTA Conectar

---

## Fase 3.5 — Bookmarks + Blocked list (~1h) — 0/4

### 3.5.1 Bookmarks — 0/2
- [ ] Boton "Marcar para despues" en perfil → toggle bookmark
- [ ] Tab "Mis Bookmarks" con lista de attendees marcados (sin enviar solicitud)

### 3.5.2 Blocked — 0/2
- [ ] Menu "..." en perfil → opcion "Bloquear" con confirm
- [ ] Tab "Bloqueados" en settings W.10 con boton "Desbloquear"

---

## Fase 4 — Solicitudes + chat (~2h) — 0/5

### 4.1 Inbox solicitudes — 0/2
- [ ] `<ConnectionsInbox />` con tabs: Recibidas / Enviadas / Aceptadas / Bookmarks
- [ ] Botones aceptar/rechazar inline en Recibidas; Cancelar en Enviadas

### 4.2 Chat 1:1 — 0/3
- [ ] `<ChatDM />` similar a chat sesion pero 1:1
- [ ] Optimistic + ack
- [ ] Indicador "escribiendo..." (W.11 lo agrega)

---

## Fase 5 — Mi perfil (~30min) — 0/2

- [ ] Form editable: avatar upload + bio + intereses (multi-select)
- [ ] Submit → mutation actualiza

---

## Fase 6 — Tests (~30min) — 0/3

### 6.1 Vitest — 0/1
- [ ] `useMatchmaking` ranking

### 6.2 Playwright — 0/2
- [ ] Happy path: ver match + conectar + chat
- [ ] Edge case: solicitud rechazada queda removida

---

## Edge cases

- [ ] Asistente sin intereses → no aparece en matchmaking
- [ ] User intenta conectar dos veces → backend dedupe + toast
- [ ] User rechazo connection antes → no permitir reenviar
- [ ] Chat con user banneado → mostrar "Este usuario ya no esta disponible"
- [ ] Mensaje >1000 chars → error pre-submit
- [ ] User cambia intereses → recalcular matchmaking en proxima query
- [ ] Solicitud expirada (>24h sin respuesta) → auto-mover a "Expiradas"
- [ ] User bloqueado intenta enviar solicitud → backend rechaza (ambos lados)
- [ ] Cancelar solicitud enviada → backend permite si pending, no si ya aceptada
- [ ] Bookmark a user que se borra de evento → cleanup automatico
- [ ] Suggested contacts con scores empatados → ordenar alfabetico

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports
- [ ] Commit DaVinci + memoria + PENDIENTES.md
