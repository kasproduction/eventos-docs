# W.8 — Networking

> Directorio + sugerencias + perfiles + solicitudes de contacto + lista de bloqueados. **Sin chat 1:1 y sin bookmarks** (no existen en backend).
>
> **Estimacion:** ~5h (reducida de 7h tras audit — sin chat DM ni bookmarks).
> **Dependencias:** W.0, W.1, W.6 (puede compartir UI patterns con Social Wall — pantalla "Connect" agrupa ambos visualmente).
> **Estado:** Pendiente — backend audit completado 2026-05-07.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- `W.0-spatial-ui.md`
- App movil: `screens/networking/` — RT, glass cards, infinite scroll
- Backend: `routes/api/networking.php`, `app/Http/Controllers/Api/V1/NetworkingController.php`
- Memoria: `project_networking_notes.md`, `project_s118_notes.md` (matchmaking)

---

## Drift corregido (2026-05-07)

Version previa documentaba **chat 1:1 (DM)** y **bookmarks**, ninguno existe en backend. Tambien usaba paths incorrectos y eventos socket inventados:

- ~~`POST /attendees/{id}/connect`~~ → real `POST /contacts/request {target_attendee_id, message?}`
- ~~`POST /connections/{id}/accept`~~ → real `PUT /contacts/request/{id} {status: 'accepted'}`
- ~~`POST /connections/{id}/reject`~~ → real `PUT /contacts/request/{id} {status: 'ignored'}` (no 'rejected')
- ~~`GET /event/{id}/matchmaking?limit=5`~~ → real `GET /events/{id}/suggested-contacts`
- ~~`GET /attendees/{id}`~~ → real `GET /attendees/{id}/profile`
- ~~`GET /connections/{id}/messages`, `POST /connections/{id}/messages`~~ → **NO existe** chat DM
- ~~`POST /attendees/{id}/bookmark`, `GET /me/bookmarks`~~ → **NO existe** bookmarks
- ~~`POST /attendees/{id}/block`, `POST /attendees/{id}/unblock`~~ → reales son `POST /contacts/block/{attendeeId}` y `DELETE /contacts/block/{attendeeId}`
- ~~Sockets `connection.request.new`, `connection.accepted`, `chat.dm.message.new`~~ → reales son `networking:notify {type:'request_received'|'request_accepted', fromName, fromAttendeeId}` (directed event a un attendee, NO room broadcast)

---

## Alcance real

1. **Directorio** asistentes (paginado, con search server-side: `?search=text&role=attendee`)
2. **Suggested contacts**: top sugerencias (sin "match score %" — el endpoint solo retorna `common_count` y `common_tags`. Si cliente quiere score visible, calcularlo client-side)
3. **Perfil completo** del otro asistente
4. **Enviar solicitud** (con mensaje opcional)
5. **Inbox solicitudes**: recibidas + enviadas (sin cancelar — backend no expone DELETE)
6. **Aceptar / Ignorar** solicitudes recibidas
7. **Mis contactos** (aceptados)
8. **Lista de bloqueados** + bloquear/desbloquear
9. **Mi perfil editable** (foto, bio, intereses) — endpoint `PUT /me/profile`

NO entra:
- Chat 1:1 / DM (no existe en backend; chat es solo por sesion en W.4)
- Bookmarks (no existe)
- Cancelar solicitud enviada (no hay endpoint)
- "Match score %" como numero — solo se ven razones (common interests/tags)

---

## Endpoints reales (verificados 2026-05-07)

```
// Directorio
GET /api/v1/events/{eventId}/attendees?search={text}&role={role}
  → {data: [AttendeeResource[]]}

// Sugerencias
GET /api/v1/events/{eventId}/suggested-contacts
  → {data: [{...AttendeeResource, common_count: number, common_tags: string[]}]}

// Perfil de otro
GET /api/v1/attendees/{attendeeId}/profile
  → {data: AttendeeResource}

// Solicitudes
POST /api/v1/contacts/request
  body: {target_attendee_id: number, message?: string}
  → {data: {id, status: 'pending'}}
  errores: 409 CONTACT_ALREADY_SENT (ya envio antes)

PUT /api/v1/contacts/request/{id}
  body: {status: 'accepted' | 'ignored'}
  → {data: {id, status}}
  errores: 409 ALREADY_RESPONDED

// Mis listas
GET /api/v1/me/contacts                           // aceptados
GET /api/v1/me/contact-requests                   // recibidas pending
GET /api/v1/me/contact-requests/sent              // enviadas pending

// Bloqueos
GET    /api/v1/me/blocked
POST   /api/v1/contacts/block/{attendeeId}
DELETE /api/v1/contacts/block/{attendeeId}

// Mi perfil
GET /api/v1/me              // datos mios
PUT /api/v1/me/profile      // editar
```

---

## Eventos socket (W.11)

| Evento | Payload | Uso |
|---|---|---|
| `networking:notify` | `{type:'request_received'\|'request_accepted', fromName, fromAttendeeId}` | Toast + invalidate `me/contact-requests` o `me/contacts` |

NO existen `connection.*` ni `chat.dm.*`.

---

## Refs visuales

- App movil networking (`features/Screenshot 2026-...`)
- `design/showcase-onboarding-v6.html` `conn-frame` — concepto match cards
- Memoria: `project_networking_notes.md`

---

## Fase 0 — Hooks (~30min) — 0/4

- [ ] `useAttendees(eventId, filters)` — paginated query con search/role
- [ ] `useSuggestedContacts(eventId)` — top sugerencias con `common_count`/`common_tags`
- [ ] `useAttendeeProfile(attendeeId)`
- [ ] `useMyContacts() / useReceivedRequests() / useSentRequests() / useBlocked()`

---

## Fase 1 — Directorio (~1.5h) — 0/4

### 1.1 Grid — 0/2
- [ ] `<AttendeesGrid />` con cards (avatar + nombre + role + tags)
- [ ] Click → perfil

### 1.2 Filtros — 0/2
- [ ] Search server-side (`?search=`) con debounce 300ms
- [ ] Filtro por role (dropdown attendee/speaker/sponsor/etc)

---

## Fase 2 — Suggested contacts (~1h) — 0/3

### 2.1 Sugerencias — 0/2
- [ ] `<SuggestedSection />` cards top sugerencias
- [ ] Hover/tap muestra "X intereses comunes" usando `common_count` y lista de `common_tags`

### 2.2 CTA — 0/1
- [ ] Boton "Conectar" → modal "Enviar solicitud" con mensaje opcional → `POST /contacts/request`

---

## Fase 3 — Perfil otro asistente (~45min) — 0/3

### 3.1 Layout — 0/2
- [ ] Desktop: panel secondary 50%
- [ ] Mobile: full screen

### 3.2 Contenido — 0/1
- [ ] Avatar + nombre + role + tags + bio + intereses + sesiones (si endpoint lo provee) + redes + CTA Conectar (o "Solicitud pendiente" / "Ya conectados")
- [ ] Menu "..." → opcion "Bloquear" con confirm

---

## Fase 4 — Lista bloqueados (~30min) — 0/2

- [ ] Tab "Bloqueados" en settings (W.10) usando `useBlocked()`
- [ ] Boton "Desbloquear" por item → `DELETE /contacts/block/{attendeeId}`

---

## Fase 5 — Inbox solicitudes (~1.5h) — 0/4

### 5.1 Tabs — 0/2
- [ ] `<ConnectionsInbox />` tabs: Recibidas / Enviadas / Mis Contactos
- [ ] Recibidas: aceptar/ignorar inline (`PUT /contacts/request/{id} {status}`)
- [ ] Enviadas: read-only, badge "Esperando" / "Aceptada" / "Ignorada"
- [ ] Mis Contactos: lista de aceptados con info basica + link al perfil

### 5.2 RT — 0/2
- [ ] Listener `networking:notify` `type=request_received` → toast "{fromName} quiere conectar" + invalidate Recibidas
- [ ] Listener `networking:notify` `type=request_accepted` → toast "{fromName} acepto tu solicitud" + invalidate Mis Contactos

---

## Fase 6 — Mi perfil editable (~30min) — 0/2

- [ ] Form: avatar upload + bio + intereses (multi-select usando endpoint `/me/interests` si existe en W.9, sino input tags)
- [ ] Submit → `PUT /me/profile` → optimistic + revert

---

## Fase 7 — Tests (~30min) — 0/3

### 7.1 Vitest — 0/1
- [ ] `useSuggestedContacts` con `common_count` ordering

### 7.2 Playwright — 0/2
- [ ] Happy path: ver suggested + enviar solicitud + tab 2 acepta + tab 1 recibe `networking:notify` + lista Mis Contactos actualiza
- [ ] Edge case: 409 CONTACT_ALREADY_SENT → toast claro

---

## Edge cases

- [ ] Asistente sin intereses → no aparece en suggested-contacts (backend lo filtra)
- [ ] User intenta conectar dos veces → 409 CONTACT_ALREADY_SENT, mostrar estado "ya enviada"
- [ ] User ignoro solicitud antes → puede reenviar (verificar comportamiento backend, default si no hay record reactivable)
- [ ] User bloqueado intenta enviar solicitud → backend rechaza (ambos lados — verificar)
- [ ] Mensaje de solicitud >500 chars → bloquear pre-submit (verificar limite backend)
- [ ] Suggested contacts con `common_count` empatados → ordenar alfabetico secundario
- [ ] Network change durante mutation → retry button + dont duplicate request

---

## Pendiente backend (nice to have)

- **Endpoint cancelar solicitud enviada** (`DELETE /contacts/request/{id}`) si user se arrepiente. Hoy no existe.
- **Score numerico de match** en `/suggested-contacts` (si producto lo pide visible). Hoy hay `common_count` pero no porcentaje.
- **Chat DM** real-time entre conexiones aceptadas. Decision en `BACKEND-API-MAP.md` Decisiones cerradas: NO se implementa, usar contacto registrado o WhatsApp.
- **Bookmarks** — feature opcional si producto lo pide.

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports
- [ ] Lighthouse OK
- [ ] Commit DaVinci + memoria + PENDIENTES.md
