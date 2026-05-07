# W.17 — Soporte

> Tickets de soporte simples: el asistente abre una consulta con asunto + mensaje, el staff responde una vez via Filament. Sin chat threading, sin RT, sin attachments — el modelo backend es un solo ticket + una sola respuesta del admin.
>
> **Estimacion:** ~2h (reducida de 3h tras audit backend).
> **Dependencias:** W.0, W.1.
> **Estado:** Pendiente — backend audit completado 2026-05-07.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`
- App movil: `app/(app)/my-support.tsx`, `app/(app)/support-contact.tsx`
- Memoria: `project_session_20260415c.md` (Soporte + push + dev build)
- Backend: `app/Http/Controllers/Api/V1/SupportController.php` (modelo ticket simple)

---

## Drift corregido (2026-05-07)

Version previa documentaba sistema chat-style con threading, multiples mensajes, attachments, status changes RT y sockets `support.ticket.*`. **Nada de eso existe en backend.** El modelo real es:

- 1 ticket = 1 `subject` + 1 `message` del usuario + 1 `admin_response` del staff
- Sin attachments, sin threading
- Sin sockets (admin responde via Filament, el user re-fetcha al volver)
- Solo 2 endpoints: `POST /support` y `GET /support/mine`

Por eso la estimacion baja de 3h a 2h y se simplifica el alcance.

---

## Alcance real

1. **Crear ticket**: form con asunto (max 200 chars) + mensaje (max 2000 chars). Sin categoria, sin attachments.
2. **Mis tickets**: lista cronologica de mis consultas con su estado y admin_response (si existe).
3. **Detalle de ticket**: read-only — muestra mi mensaje original + respuesta del admin si ya respondio.

NO entra en scope (no soportado por backend):
- Chat threading / multi-mensaje en un mismo ticket
- Reabrir ticket / cerrar manualmente desde web
- Attachments (imagenes, archivos)
- Notificaciones RT cuando staff responde
- Categorias predefinidas

---

## Endpoints reales (verificados 2026-05-07)

```
POST /api/v1/support
  body: {event_id: number, subject: string (max 200), message: string (max 2000)}
  response: {data: {id: number}} (201)
  rate-limit: configurado por evento (ChecksRateLimit trait, action='support')
  errors: 403 si user no es attendee del evento, 429 si rate-limited

GET /api/v1/support/mine?event_id={id}
  response: {data: [{id, subject, message, status, admin_response, responded_at, created_at}]}
  status values: 'open' | 'responded' (definir con backend si hay otros)
```

**NO existen** y por tanto NO usar:
- `GET /support-tickets/{id}` (detalle individual)
- `POST /support-tickets/{id}/messages` (chat reply)
- `POST /support-tickets/{id}/close`
- Endpoint para reabrir ticket
- Sockets `support.*`

---

## Refs visuales

- App movil my-support — lista cards estilo email
- Memoria: `project_session_20260415c.md`

---

## Fase 0 — Hooks (~20min) — 0/2

- [ ] `useSupportTickets(eventId)` — TanStack Query, sin paginacion (lista completa)
- [ ] `useCreateSupportTicket(eventId)` — mutation con manejo de 429 (rate-limit) y 403

---

## Fase 1 — Crear ticket (~40min) — 0/3

### 1.1 Form — 0/2
- [ ] `<CreateTicketForm />` con asunto + textarea mensaje (sin categoria, sin attachments)
- [ ] Counters de chars en vivo (200 / 2000)

### 1.2 Submit — 0/1
- [ ] Mutation crear → toast "Tu consulta fue enviada" + invalidar lista + redirect/cerrar modal

---

## Fase 2 — Lista mis tickets (~40min) — 0/4

### 2.1 Lista — 0/2
- [ ] `<TicketsList />` ordenada desc por created_at
- [ ] Cada ticket: subject + tiempo relativo + status pill + indicador respuesta ("Respondido" / "En espera")

### 2.2 Detalle — 0/1
- [ ] Click → `<TicketDetail />` panel/modal:
  - Mi mensaje original (subject + body + tiempo)
  - Respuesta admin si `admin_response !== null` (con tiempo `responded_at`)
  - Mensaje "El equipo aun no ha respondido" si pendiente

### 2.3 Empty state — 0/1
- [ ] "No tienes consultas todavia" + CTA "Abrir nueva consulta"

---

## Fase 3 — Polling fallback (~20min) — 0/2

Como NO hay sockets, para refresco al recibir respuesta:

- [ ] `refetchOnWindowFocus: true` en `useSupportTickets` → al volver a la pestana, lista se actualiza
- [ ] Opcional: si user esta en detalle de ticket pendiente, refetch cada 60s mientras esta abierto. Sin polling agresivo.

---

## Fase 4 — Tests (~20min) — 0/3

### 4.1 Vitest — 0/1
- [ ] Submit ticket con validacion zod (subject<=200, message<=2000)

### 4.2 Playwright — 0/2
- [ ] Happy path: crear ticket + ver en lista pendiente
- [ ] Edge case: hit rate-limit → toast claro

---

## Edge cases

- [ ] Rate-limited (429) → toast "Has alcanzado el limite diario de consultas, intenta manana"
- [ ] User no es attendee del evento (403) → mensaje "Necesitas estar registrado al evento"
- [ ] Mensaje supera 2000 chars → bloquear submit, contador en rojo
- [ ] Backend no responde → retry con manual button + Sentry log
- [ ] Volver a la pantalla mientras admin responde → focus refetch trae la respuesta

---

## Acceso desde la app

- En user menu (W.10): entry "Soporte" → `<TicketsList />`
- Desde error boundaries de red persistentes: link "¿Sigue sin funcionar? Abrir consulta de soporte"

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports
- [ ] Lighthouse OK
- [ ] Si en algun momento backend agrega threading/sockets, este doc se debe revisar (escribir issue tracker en backend)
- [ ] Commit DaVinci + memoria + PENDIENTES.md
