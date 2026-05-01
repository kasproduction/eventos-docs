# W.15 — Vendor Dashboard

> Pantalla para vendedores/sponsors operando un stand. Mi Stand management, leads capturados, stand stats, team members. Solo accesible si user tiene `vendor_access` o `tags=stand` en el evento.
>
> **Estimacion:** ~6h.
> **Dependencias:** W.0, W.1, W.7 (Sponsors comparte data model).
> **Estado:** Pendiente. **Marcado como OPCIONAL** — solo se ejecuta si Bancolombia o cliente lo pide explicitamente. Para Fase 1 puede quedar en backlog.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- App movil: `app/(app)/mi-stand.tsx`, `stand-contacts.tsx`, `stand-stats.tsx`, `scanner-stand.tsx`
- Hooks: `useStand`, `useLeads`
- Memorias: `project_s16_notes.md` (sponsors=stands), `project_sponsors_uiux_notes.md`

---

## Alcance

1. **Mi Stand**: dashboard del vendor con info del stand (logo, descripcion, productos, redes)
2. **Editar Mi Stand**: si tiene permiso, edita campos limitados (descripcion, redes, productos — NO logo/branding que controla organizador)
3. **Leads capturados**: lista de contactos escaneados por el vendor (datos de contacto + notas + tags)
4. **Lead detalle**: ver lead individual + agregar notas + estado (cold/warm/hot/closed)
5. **Stand stats**: analytics del stand (visitantes unicos, leads capturados, tasa conversion, top sesiones, comparativa vs otros stands)
6. **Team members**: agregar/quitar miembros del equipo del stand (otros vendors)
7. **Export leads CSV**

**NO entra en webapp**:
- Scanner camara (escanear QR badges) — solo movil
- Scanner-invite networking — solo movil

La webapp es para **lectura, dashboard, follow-up**. La captura activa de leads sigue siendo movil.

---

## Refs visuales

- App movil mi-stand premium (`features/Screenshot 2026-...`)
- App movil stand-stats con graficas
- Memoria: `project_session_20260410f.md` (Mi Stand premium)

---

## Endpoints (verificar)

- `GET /api/v1/me/stand` — mi stand (si tengo vendor_access)
- `PATCH /api/v1/stand/{id}` — editar stand
- `GET /api/v1/stand/{id}/leads?cursor`
- `GET /api/v1/stand/{id}/leads/{leadId}`
- `PATCH /api/v1/leads/{leadId}` — actualizar status/notas
- `GET /api/v1/stand/{id}/stats` — analytics
- `GET /api/v1/stand/{id}/team` — team members
- `POST /api/v1/stand/{id}/team` — agregar miembro
- `DELETE /api/v1/stand/{id}/team/{userId}`
- `GET /api/v1/stand/{id}/leads/export` — CSV download

---

## Permisos

- Verificar `user.vendor_access` o `tags includes 'stand'` antes de mostrar W.15
- Si user no tiene permiso → redirect home + toast "Acceso no autorizado"
- Owner del stand vs miembros: owner edita stand + team, miembros solo ven leads + stats

---

## Fase 0 — Hooks + permisos (~45min) — 0/4

- [ ] `useMyStand()` — devuelve stand si tengo permiso, null si no
- [ ] `useStandLeads(standId, filters)` — infinite query
- [ ] `useStandStats(standId)`
- [ ] `useStandTeam(standId)`

---

## Fase 1 — Mi Stand dashboard (~1h) — 0/4

### 1.1 Layout — 0/2
- [ ] Hero del stand (logo + nombre + tier + categoria + descripcion)
- [ ] Tabs: Resumen / Leads / Stats / Equipo / Editar

### 1.2 Resumen — 0/2
- [ ] Cards rapidas: Total leads, Leads hoy, Visitantes, Conversion rate
- [ ] Recent activity: ultimos 5 leads capturados

---

## Fase 2 — Leads capturados (~1.5h) — 0/5

### 2.1 Lista — 0/3
- [ ] `<LeadsList />` con filtros (status, fecha, search por nombre/email)
- [ ] Cada lead: avatar/inicial + nombre + email + status pill + fecha
- [ ] Click → detalle

### 2.2 Detalle — 0/2
- [ ] `<LeadDetail />` panel secundario
- [ ] Notas editables + cambio de status + tags + accion "Marcar como cerrado"

---

## Fase 3 — Stand stats (~1.5h) — 0/4

### 3.1 KPIs — 0/2
- [ ] Cards: Total leads, Leads hoy, Conversion rate, Avg lead score
- [ ] Sparkline 7 dias

### 3.2 Graficas — 0/2
- [ ] Barra: leads por dia
- [ ] Donut: distribucion por status (cold/warm/hot/closed)
- [ ] Tabla: top sesiones con mas conversion

---

## Fase 4 — Editar stand (~45min) — 0/3

### 4.1 Form — 0/2
- [ ] Editables: descripcion, redes sociales, productos (lista CRUD)
- [ ] NO editable: logo, nombre, tier (solo organizador)

### 4.2 Submit — 0/1
- [ ] Mutation con optimistic + toast

---

## Fase 5 — Team members (~30min) — 0/3

### 5.1 Lista — 0/2
- [ ] Avatar + nombre + role + boton "Quitar" (solo owner)

### 5.2 Agregar — 0/1
- [ ] Modal "Agregar miembro" con search por email + selector role

---

## Fase 6 — Export CSV (~15min) — 0/1

- [ ] Boton "Exportar leads CSV" → endpoint backend genera CSV + descarga

---

## Fase 7 — Tests (~45min) — 0/4

### 7.1 Vitest — 0/2
- [ ] Permisos: user sin vendor_access no ve W.15
- [ ] Optimistic update lead status

### 7.2 Playwright — 0/2
- [ ] Happy path: ver leads + editar status + ver stats + invitar miembro
- [ ] Edge case: user sin permiso → redirect

---

## Edge cases

- [ ] Stand sin leads → empty state "Aun no has capturado leads"
- [ ] Lead duplicado → backend dedupe por email + warning UI
- [ ] User pierde vendor_access durante sesion → siguiente request 403 → redirect
- [ ] Stats sin data (evento nuevo) → mostrar "Aun no hay datos suficientes"
- [ ] Team owner intenta quitarse a si mismo → bloqueado, mensaje "No puedes quitarte a ti mismo"
- [ ] Editar stand con conflict (otro miembro edita simultaneo) → ultimo gana, toast "Datos actualizados, refrescando"
- [ ] CSV export con >10K leads → backend procesa async + envia link via email

---

## Acceso desde la app

- Si user tiene vendor_access → entry "Mi Stand" en user menu (W.10) + acceso directo desde Sponsors panel
- Si NO tiene permiso → no se muestra entry

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports
- [ ] Lighthouse OK
- [ ] Commit DaVinci + memoria + PENDIENTES.md
