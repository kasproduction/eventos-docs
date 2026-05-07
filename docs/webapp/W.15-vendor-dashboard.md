# W.15 — Vendor Dashboard

> Pantalla para vendedores/sponsors operando un stand. Mi Stand info + team, leads capturados (mis scans personales), stats del stand. Solo accesible si user tiene `has_vendor_access=true` en su `AttendeeResource`.
>
> **Estimacion:** ~6h.
> **Dependencias:** W.0, W.1, W.7 (Sponsors comparte data).
> **Estado:** Pendiente — backend audit completado 2026-05-07. **OPCIONAL** — solo si Bancolombia o cliente lo pide. Para Fase 1 puede quedar en backlog.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- App movil: `app/(app)/mi-stand.tsx`, `stand-contacts.tsx`, `stand-stats.tsx`, `mi-equipo.tsx`, `assign-staff.tsx`, `join-team`
- Memorias: `project_s16_notes.md` (sponsors=stands), `project_s1xh_staff_invite.md` (staff invite), `project_session_20260410f.md` (Mi Stand premium)
- Backend routes: `routes/api/stand.php`, `routes/api/leads.php`

---

## Drift corregido (2026-05-07)

Version previa documentaba endpoints `/stand/{id}/...` (no existen — los reales son `/me/stand/...`), `PATCH /stand/{id}` para editar stand (NO existe — el stand lo administra organizador via Filament), y omitia 6+ endpoints criticos del flujo de invitaciones (`search-attendees`, `resolve-qr`, `share-link`, `pending-invitations`, `staff-invitations/{token}/accept|reject|info`). Tambien decia `PATCH /leads/{lead}` siendo el real `PUT /leads/{lead}`.

---

## Alcance real

1. **Mi Stand**: dashboard read-only del stand (logo, descripcion, miembros). El stand NO se edita desde web — eso es Filament del organizador.
2. **Mis leads**: lista de scans personales (los leads son por usuario, no por stand). El doc anterior asumio scope por stand.
3. **Visitantes del stand**: lista de quienes escaneamos en el stand (`/me/stand/contacts`).
4. **Stats del stand**: leads totales, por miembro, etc.
5. **Team management**: agregar/remover miembros, transferir propiedad, generar link de invitacion + QR resolve.
6. **Pending invitations**: si me invitaron a otro stand, ver y aceptar/rechazar.
7. **Export leads CSV** personal.

**NO entra en webapp** (mobile only):
- Scanner camara (escanear QR badges) — solo movil
- Crear leads via scan — solo movil

La webapp es para **lectura, dashboard, follow-up, gestion de team**. La captura activa de leads sigue siendo movil.

**NO existe en backend** (no documentar como feature):
- Editar el stand desde el vendor (descripcion, redes, productos) — eso es Filament
- Lead detalle individual con `GET /stand/{id}/leads/{leadId}` — usar `GET /leads` con paginacion + filtrar en cliente

---

## Endpoints reales (verificados 2026-05-07)

### Stand info + team
```
GET /me/stand
  → {data: {id, name, members: [StandMemberResource]}}
  StandMemberResource: {id, attendee_id, invited_email, status:'pending'|'accepted'|'expired',
                       name, photo_url, added_at, accepted_at, expires_at}

GET /me/stand/stats
  → {data: {leads_count, ...}}

GET /me/stand/contacts
  → {data: [LeadResource[]]}  // visitantes del stand (otros del team scanearon)

POST /me/stand/members
  body: {attendee_id?: number, email?: string}
  → {data: StandMemberResource}

DELETE /me/stand/members/{attendeeId}
  → {message: string}

POST /me/stand/transfer
  body: {new_owner_id: number}
  → {data}

GET /me/stand/search-attendees?q={text}
  → {data: [AttendeeResource[]]}  // para autocompletar en agregar miembro

POST /me/stand/resolve-qr
  body: {qr_token: string}
  → {data: AttendeeResource}  // QR → attendee, antes de invitar

POST /me/stand/share-link
  → {data: {share_url: string}}  // genera link generico de invitacion
```

### Staff invitations (recibir invites)
```
GET /me/pending-invitations
  → {data: [Invitation[]]}

GET /staff-invitations/{token}/info  (publico)
  → {data: {event, stand}}  // ver datos del invite antes de aceptar (sin auth)

POST /staff-invitations/{token}/accept  (publico)
  body: {password?, password_confirmation?}  // si user nuevo, crea cuenta
  → {data: {token, user}}

POST /staff-invitations/{token}/reject  (publico)
  → {message}
```

### Leads (scope: mis scans, NO scope stand)
```
GET /leads?per_page=15&page=1&sort=-created_at
  → {data: [LeadResource[]], meta}

PUT /leads/{lead}     // NOTA: PUT, no PATCH
  body: {tier?: 'hot'|'warm'|'cold', note?: string}
  → {data}

GET /leads/{lead}/edits
  → {data: [LeadEditResource[]]}  // historial de cambios

GET /me/leads/export?event_id={id}
  → text/csv
```

### Permisos
- `AttendeeResource.has_vendor_access: boolean` — gate web
- Si false → redirect home + toast "Acceso no autorizado"
- Backend NO valida `tags=stand`. Solo `vendor_access` o role como `vendedor`/`sponsor`

---

## Eventos socket (W.11)
| Evento | Uso |
|---|---|
| `staff:invited` | Notif cuando me invitan a un stand → invalidate `me/pending-invitations` |
| `staff:accepted` | Notif al owner cuando alguien acepta → invalidate `me/stand` |
| `staff:rejected` | Notif al owner cuando alguien rechaza |
| `staff:removed` | El owner me removio → toast + invalidate `me/stand` |

---

## Refs visuales

- App movil mi-stand premium (`features/Screenshot 2026-...`)
- Memoria: `project_session_20260410f.md` (Mi Stand premium)

---

## Fase 0 — Hooks + permisos (~45min) — 0/4

- [ ] `useMyStand()` — devuelve `{data}` o 403/404 si no tengo vendor_access
- [ ] `useMyLeads(filters)` — paginated query
- [ ] `useStandStats()` — stats sumarizadas
- [ ] `usePendingInvitations()` — invites recibidos

---

## Fase 1 — Mi Stand dashboard (~1h) — 0/4

### 1.1 Layout — 0/2
- [ ] Hero del stand (datos venidos de SponsorResource via lookup): logo + nombre + tier + descripcion
- [ ] Tabs: Resumen / Leads / Visitantes / Stats / Equipo / Invitaciones

### 1.2 Resumen — 0/2
- [ ] Cards rapidas: Total leads (yo), Leads hoy, Visitantes (team), Miembros activos
- [ ] Recent activity: ultimos 5 leads mios

---

## Fase 2 — Mis leads (~1.5h) — 0/5

### 2.1 Lista — 0/3
- [ ] `<LeadsList />` con filtros (tier hot/warm/cold, fecha, search por nombre/email)
- [ ] Cada lead: avatar + nombre + email + tier pill + fecha
- [ ] Click → drawer/panel detalle (NO navegacion full — porque no hay endpoint single)

### 2.2 Detalle (cliente reusa data de la lista) — 0/2
- [ ] `<LeadDetail />` muestra LeadResource completo desde el item ya cargado
- [ ] Notas editables + cambio de tier → `PUT /leads/{lead}` (optimistic + revert)

---

## Fase 3 — Visitantes del stand (~30min) — 0/2

### 3.1 Lista — 0/2
- [ ] `<StandContactsList />` — leads de quien sea del team (no solo mios)
- [ ] Avatar + nombre + member que lo escaneo + tiempo

---

## Fase 4 — Stats (~1h) — 0/3

### 4.1 KPIs — 0/2
- [ ] Cards: Total leads team, Leads hoy, Avg leads por miembro
- [ ] Sparkline 7 dias si backend lo da, o computado desde lista de visitantes

### 4.2 Distribucion — 0/1
- [ ] Donut por tier (hot/warm/cold)
- [ ] Si stats endpoint no trae el detalle, computar en cliente sobre `/me/stand/contacts`

---

## Fase 5 — Team management (~1h) — 0/5

### 5.1 Lista miembros — 0/2
- [ ] Lista renderizada desde `useMyStand().data.members`
- [ ] Por miembro: avatar + nombre + status pill (`pending`/`accepted`/`expired`) + boton "Remover" (owner only)

### 5.2 Agregar miembro (3 vias) — 0/2
- [ ] **A. Por busqueda directa**: input con autocomplete usando `GET /me/stand/search-attendees?q=` → seleccionar → `POST /me/stand/members {attendee_id}`
- [ ] **B. Por email**: input email → `POST /me/stand/members {email}` (genera invite token)
- [ ] **C. Por share link**: boton "Generar link" → `POST /me/stand/share-link` → copia URL al clipboard

### 5.3 Transfer ownership — 0/1
- [ ] Solo owner — modal de confirmacion "¿Transferir a X?" → `POST /me/stand/transfer`

---

## Fase 6 — Recibir invitaciones (~45min) — 0/3

### 6.1 Lista pendientes — 0/2
- [ ] `<PendingInvitations />` — `GET /me/pending-invitations`
- [ ] Por invite: stand + sponsor + tier + acciones Aceptar/Rechazar

### 6.2 Acciones — 0/1
- [ ] `POST /staff-invitations/{token}/accept` o `/reject` → invalidate + toast

### 6.3 Pagina publica de invite (sin login) — bonus si scope lo pide
- [ ] Ruta `/[locale]/(public)/invite/[token]/page.tsx`
- [ ] `GET /staff-invitations/{token}/info` (publico) → muestra evento + stand
- [ ] Si user no logueado, mostrar form crear cuenta + accept

---

## Fase 7 — Export CSV (~15min) — 0/1

- [ ] Boton "Exportar leads CSV" → `GET /me/leads/export?event_id=` → trigger download via anchor invisible

---

## Fase 8 — Tests (~45min) — 0/4

### 8.1 Vitest — 0/2
- [ ] Permisos: user sin `has_vendor_access` no ve W.15 (redirect)
- [ ] Optimistic update lead tier con revert si falla

### 8.2 Playwright — 0/2
- [ ] Happy path: ver leads + editar tier + invitar miembro por email
- [ ] Edge case: user sin permiso → redirect home

---

## Edge cases

- [ ] Stand sin leads → empty state "Aun no has capturado leads — usa la app movil para escanear"
- [ ] User pierde vendor_access durante sesion → siguiente request 403 → redirect
- [ ] Stats sin data → "Aun no hay datos suficientes"
- [ ] Owner intenta transferir a miembro que no es del team → backend 422
- [ ] Owner intenta removerse a si mismo → bloqueado, mensaje claro
- [ ] CSV con >10K leads → backend procesa async + envia link via email (verificar comportamiento real)
- [ ] Invite expirado (status `expired`) → mostrar disabled con badge
- [ ] Aceptar invite a stand cuando ya soy miembro → backend 409, toast "Ya eres miembro"
- [ ] Share link generado y compartido a desconocido → backend valida cuando llega al accept (token + email match)

---

## Acceso desde la app

- Si `attendee.has_vendor_access` → entry "Mi Stand" en user menu (W.10)
- Si NO → no se muestra entry
- Pending invitations: si `me/pending-invitations` no esta vacio → badge en bell o entry destacado

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports
- [ ] Lighthouse OK
- [ ] Commit DaVinci + memoria + PENDIENTES.md
