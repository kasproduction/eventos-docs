# W.17 — Soporte

> Sistema de tickets de soporte: el asistente puede abrir un ticket con el equipo del evento (problema tecnico, duda, queja). Chat-style con mensajes + estado del ticket.
>
> **Estimacion:** ~3h.
> **Dependencias:** W.0, W.1, W.11 (chat RT).
> **Estado:** Pendiente.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`
- App movil: `app/(app)/my-support.tsx`, `app/(app)/support-contact.tsx`
- Memoria: `project_session_20260415c.md` (Soporte + push + dev build)

---

## Alcance

1. **Crear ticket**: form con categoria + asunto + descripcion + adjuntos opcionales
2. **Mis tickets**: lista con filtros (Abiertos / En proceso / Resueltos / Cerrados)
3. **Chat ticket**: mensajes con staff + estado (open/in_progress/resolved/closed) + resolucion
4. **Notificaciones RT**: cuando staff responde, RT update + push notification

---

## Refs visuales

- App movil my-support — chat-style con mensajes
- Memoria: `project_session_20260415c.md`

---

## Endpoints (verificar)

- `GET /api/v1/me/support-tickets?status&cursor`
- `POST /api/v1/me/support-tickets` — crear ticket
- `GET /api/v1/support-tickets/{id}` — detalle con mensajes
- `POST /api/v1/support-tickets/{id}/messages` — enviar mensaje
- `POST /api/v1/support-tickets/{id}/close` — cerrar ticket (asistente)

Socket events:
- `support.ticket.message.new` — RT cuando staff responde
- `support.ticket.status.changed` — open → in_progress → resolved → closed

---

## Fase 0 — Hooks (~30min) — 0/2

- [ ] `useSupportTickets(filters)` — infinite query
- [ ] `useSupportTicket(ticketId)` — detalle + send message mutation

---

## Fase 1 — Crear ticket (~1h) — 0/4

### 1.1 Form — 0/3
- [ ] `<CreateTicketForm />` con select categoria (Tecnico, Acceso, Pago, Sugerencia, Otro)
- [ ] Asunto (max 100 chars) + descripcion (max 1000 chars)
- [ ] Upload adjuntos opcional (max 3 archivos, max 5MB c/u)

### 1.2 Submit — 0/1
- [ ] Mutation crear → redirect al ticket creado + toast "Tu ticket fue enviado"

---

## Fase 2 — Lista mis tickets (~30min) — 0/3

### 2.1 Lista — 0/2
- [ ] `<TicketsList />` con filtro tabs por status
- [ ] Cada ticket: # + asunto + categoria + status pill + ultima actividad

### 2.2 Empty state — 0/1
- [ ] "No tienes tickets aun" + CTA "Abrir nuevo ticket"

---

## Fase 3 — Chat ticket (~1h) — 0/4

### 3.1 Layout — 0/2
- [ ] Header: # + asunto + status pill + boton cerrar (si abierto)
- [ ] Mensajes scrollable con avatar + nombre + texto + tiempo

### 3.2 Input + RT — 0/2
- [ ] Input para enviar mensaje (deshabilitado si ticket cerrado)
- [ ] Socket subscribe `support.ticket.message.new` → append mensaje + scroll bottom

---

## Fase 4 — Tests (~30min) — 0/3

### 4.1 Vitest — 0/1
- [ ] Send message optimistic con tempId

### 4.2 Playwright — 0/2
- [ ] Happy path: crear ticket + enviar mensaje + recibir respuesta RT
- [ ] Edge case: cerrar ticket → input deshabilitado

---

## Edge cases

- [ ] Adjunto >5MB → error pre-submit
- [ ] Tipo archivo no permitido (exe, etc.) → error
- [ ] Ticket cerrado por staff → user recibe socket event + status pill cambia
- [ ] User intenta enviar mensaje en ticket cerrado → error "Este ticket esta cerrado"
- [ ] User reabre ticket cerrado (si feature lo permite) → backend valida
- [ ] Staff responde con HTML → DOMPurify
- [ ] Mensaje muy largo (>2000 chars) → truncar + "Ver mas"
- [ ] User sin tickets pero con notif → notif sigue visible aunque lista este vacia

---

## Acceso desde la app

- En user menu (W.10): entry "Soporte" → `<TicketsList />`
- Boton flotante "Necesitas ayuda?" en errores de red persistentes

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports
- [ ] Lighthouse OK
- [ ] Commit DaVinci + memoria + PENDIENTES.md
