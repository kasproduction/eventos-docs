---
name: EventOS — notas técnicas S1.6 Sponsors + Stand Teams
description: Arquitectura completa de S1.6 — directorio de sponsors y sistema de equipos de stand
type: project
---

## El sponsor ES el stand

Un `sponsor` tiene dos caras:
- **Pública**: directorio visible para todos los asistentes (logo, descripción, favoritos, contacto)
- **Interna**: equipo de vendedores que captura leads durante el evento

## Tablas nuevas en S1.6

**`sponsors`** (tabla principal — amplía lo planeado originalmente):
- Campos públicos: name, tier, logo_url, banner_url, description, website_url, contact_email, show_contact_button, sort_order, enabled
- Campos de equipo nuevos: `owner_attendee_id`, `max_collaborators`

**`stand_members`** (miembros del equipo):
- sponsor_id, attendee_id (nullable si no registrado aún), invited_email, invited_by_attendee_id
- status: ENUM(pending, active, removed)
- Activación auto al registrarse (hook en AuthService::register())

**`sponsor_services`**, **`sponsor_favorites`** (directorio público)

**`lead_edits`** (log de ediciones):
- lead_id, attendee_id, field_changed, old_value, new_value, created_at

## Cambios a tablas existentes (alter migrations)

**`attendees`**:
- `has_vendor_access BOOLEAN DEFAULT false` — flag para colaboradores de stand
- `sponsor_id BIGINT NULL FK sponsors` — a qué stand pertenece

**`leads`**:
- `sponsor_id BIGINT NULL FK sponsors` — pool del stand
- UNIQUE: `(sponsor_id, scanned_attendee_id)` cuando hay stand; índices simples en FKs individuales

## Flujo de invitación a stand

1. Owner agrega email desde la app → `POST /api/v1/me/stand/members`
2. ¿Ya registrado en el evento?
   - SÍ → `has_vendor_access=true`, `stand_members.status=active` inmediato
   - NO → `stand_members.status=pending`
3. Al registrarse → `AuthService::register()` detecta pending invite → activa automáticamente
4. Colaborador ve Leads/Scanner en ModuleMenu sin cambiar su rol

## Transferencia de ownership

- `POST /api/v1/me/stand/transfer` — transfiere a miembro activo del stand
- **Bug fix implementado:** al transferir, el owner saliente se registra automáticamente como `stand_member` activo → el nuevo owner puede devolverle el control en el futuro
- El owner debe ser un miembro activo para recibir ownership de vuelta

## Pool de leads compartido

- `leads.sponsor_id` define el pool
- `GET /api/v1/leads` — si `sponsor_id` → query por todos los miembros del stand
- Duplicado en pool → 409 `ALREADY_IN_STAND_POOL` con nombre de quien lo capturó
- Cualquier miembro puede editar → registra en `lead_edits`
- Leads de S1.5 (sponsor_id=NULL) quedan como personales

## Módulos para colaboradores

`GET /api/v1/events/{id}/modules` detecta `has_vendor_access=true` y agrega módulos leads/scanner dinámicamente sin cambiar el rol del asistente.

## Endpoints S1.6

```
GET    /api/v1/events/{id}/sponsors                    — directorio público + is_favorite
POST   /api/v1/events/{id}/sponsors/{id}/favorite      — marcar favorito
DELETE /api/v1/events/{id}/sponsors/{id}/favorite      — quitar favorito
GET    /api/v1/me/stand                                — mi stand + miembros + cupos
POST   /api/v1/me/stand/members                        — agregar por email
DELETE /api/v1/me/stand/members/{attendeeId}           — remover (solo owner)
POST   /api/v1/me/stand/transfer                       — transferir ownership
GET    /api/v1/leads/{id}/edits                        — log de ediciones
```

## Estado al 2026-03-30

- Backend: ✅ completo, 68/68 tests, pusheado a main
- App: 🔲 pendiente (sesión futura post-S1.7)
- Filament: ✅ SponsorResource con Repeater de servicios
- Comando demo: `php artisan demo:s16` — resetea estado y genera tokens frescos
