# W.10 — Hub Personal (Perfil + Mi QR + Settings)

> Hub personal del usuario. Perfil editable, foto, datos de onboarding, settings (idioma + tema, client-side), Mi QR, log-out, y entry-points hacia los otros modulos (Mi Stand W.15, Soporte W.17, Mis Premios W.9, Mis Contactos W.8).
>
> **Estimacion:** ~4h (reducida de 8h tras audit — sin features inventadas).
> **Dependencias:** W.0, W.1, W.9, W.15, W.17.
> **Estado:** Pendiente — backend audit completado 2026-05-07.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `AUTH-SPEC.md`
- App movil: `app/(app)/(tabs)/perfil.tsx` (o equivalente), `attendee/`, perfil settings
- Backend: `routes/api.php` seccion Profile, `app/Http/Controllers/Api/V1/ProfileController.php`

---

## Drift corregido (2026-05-07)

Version previa documentaba 7 features que **fueron inventadas por agentes** y NO son requerimiento del producto:

- ~~Web Notifications API + push subscriptions~~ — no se necesita en webapp Fase 1; los avisos llegan via bell popover (W.14) + chat banner pinned (W.4)
- ~~Bandeja de notificaciones in-app `/me/notifications`~~ — el bell de W.14 con localStorage cubre el caso
- ~~Sesiones activas multi-device + logout-all~~ — backend solo expone `POST /logout` (cierra la sesion actual)
- ~~Selector de evento multi-event `/me/events`~~ — flujo actual: 1 user esta en 1 evento por sesion. Si el cliente esta inscrito en otro, se loguea aparte
- ~~Mi QR rotativo con `/me/qr-token`~~ — el endpoint real es `GET /me/qr` (en `routes/api/checkin.php`), no rotativo HMAC
- ~~Cerrar cuenta~~ — fuera de scope Fase 1
- ~~Privacy toggles "visible en networking, mostrar puntos publicamente"~~ — no existen los flags en backend

Eliminadas tambien todas las menciones a `notification.new` socket (no existe).

---

## Alcance real

1. **Mi perfil editable**: foto, nombre, datos de onboarding (bio/intereses/redes — viven en `me/onboarding-data`)
2. **Mi QR**: badge personal para escaneo presencial (display readonly)
3. **Settings client-side**: idioma (es-CO/en/pt-BR via next-intl) + tema (Noir/Lux via next-themes)
4. **Cerrar sesion**
5. **User menu**: dropdown con entry-points a otros modulos relevantes segun permisos

---

## Endpoints reales (verificados 2026-05-07)

```
// Auth
GET  /api/v1/auth/me                                    // datos del user actual
POST /api/v1/auth/logout                                // cerrar sesion (solo la actual)
POST /api/v1/auth/refresh                               // refresh token

// Profile
GET    /api/v1/me/profile                               // datos del perfil
PUT    /api/v1/me/profile                               // actualizar (nombre, redes, etc.)
POST   /api/v1/me/photo                                 // subir foto (multipart)
DELETE /api/v1/me/photo                                 // quitar foto
GET    /api/v1/me/onboarding-data                       // bio + intereses + campos extra
PUT    /api/v1/me/onboarding-data                       // actualizar onboarding-data

// Registration fields (campos dinamicos del evento)
GET /api/v1/me/registration-fields
PUT /api/v1/me/registration-fields

// Mi QR
GET /api/v1/me/qr
  → {data: {token, url, expires_at}}

// Entry points hacia otros W.x
GET /api/v1/me/contacts            // W.8 Mis Contactos
GET /api/v1/me/blocked             // W.8 Bloqueados
GET /api/v1/me/points?event_id=    // W.9 Mis puntos
GET /api/v1/me/prizes              // W.9 Mis premios
GET /api/v1/me/redemptions         // W.9 Mis canjes
GET /api/v1/me/stand               // W.15 Mi stand (si vendor)
GET /api/v1/me/pending-invitations // W.15 Invites recibidos
GET /api/v1/support/mine           // W.17 Mis tickets soporte
```

**No hay sockets dedicados a este modulo.**

---

## Refs visuales

- App movil profile/perfil — pantalla con avatar grande + bio + entry points a sub-pages
- Memoria: `project_session_20260417c.md` (Lux v2 dark islands para perfil)

---

## Fase 0 — Hooks (~30min) — 0/3

- [ ] `useMe()` — `GET /auth/me` (cache larga, ya cargado en root layout)
- [ ] `useProfile()` — `GET/PUT /me/profile` con optimistic
- [ ] `useOnboardingData()` — `GET/PUT /me/onboarding-data`

---

## Fase 1 — Perfil editable (~1.5h) — 0/5

### 1.1 Layout — 0/2
- [ ] Hero con foto grande + nombre + role + tags (desde `AttendeeResource`)
- [ ] Tabs/secciones: Datos, Onboarding, Mi QR

### 1.2 Datos — 0/2
- [ ] Form editable nombre + redes sociales
- [ ] `POST /me/photo` con preview + crop circular + max size validate cliente
- [ ] `DELETE /me/photo` boton "Quitar foto"

### 1.3 Onboarding data — 0/1
- [ ] Form: bio + intereses (multi-select) + cualquier `me/registration-fields` que el evento haya configurado
- [ ] Submit → `PUT /me/onboarding-data` (o `/me/registration-fields` segun corresponda)

---

## Fase 2 — Mi QR (~30min) — 0/3

### 2.1 Display — 0/3
- [ ] `<MiQR />` con QR generado del `token` que retorna `GET /me/qr`
- [ ] Muestra nombre + foto + nombre evento + QR
- [ ] **SOLO mobile webapp** (regla `feedback_qr_only_mobile.md`): en desktop/tablet H, ocultar la opcion o mostrar mensaje "Abre este QR desde tu celular"
- [ ] Brightness boost en mobile via `screen.brightness` API si soportado

---

## Fase 3 — Settings client-side (~30min) — 0/3

### 3.1 Idioma — 0/1
- [ ] Selector es-CO / en / pt-BR → cambio inmediato (next-intl + cookie persistente)

### 3.2 Tema — 0/1
- [ ] Switch Noir / Lux → cambio inmediato (next-themes)

### 3.3 Cerrar sesion — 0/1
- [ ] Boton "Cerrar sesion" → `POST /auth/logout` → limpia cookie + redirect a `/login`

---

## Fase 4 — User menu dropdown (~1h) — 0/3

### 4.1 Trigger — 0/1
- [ ] Avatar arriba derecha del shell (W.0 SidebarPill / header) → dropdown

### 4.2 Items — 0/2
- [ ] Items siempre visibles: Perfil, Mi QR (mobile only), Configuracion, Cerrar sesion
- [ ] Items condicionales:
  - "Mi Stand" → si `attendee.has_vendor_access` (link W.15)
  - "Invitaciones pendientes" → si `useMyPendingInvitations().data.length > 0` (link W.15)
  - "Mis premios" → si `useMyPrizes().data.length > 0` (link W.9 tab)
  - "Mis canjes" → si `useMyRedemptions().data.length > 0` (link W.9 tab)
  - "Soporte" → siempre (link W.17)

---

## Fase 5 — Tests (~30min) — 0/3

### 5.1 Vitest — 0/1
- [ ] Optimistic update perfil con revert si falla

### 5.2 Playwright — 0/2
- [ ] Happy path: editar nombre + cambiar idioma + cerrar sesion
- [ ] Edge case: foto >5MB (o el limite real backend) → error pre-submit

---

## Edge cases

- [ ] Avatar upload muy pesado → error pre-submit con mensaje claro
- [ ] Cambio tema con custom branding → respeta `branding.primary_color`, solo cambia surface
- [ ] User intenta editar perfil con campo requerido vacio → validacion zod inline
- [ ] Logout en una pestana → otras pestanas reciben 401 al next request → redirect login
- [ ] User edita perfil concurrente (otra pestana) → ultimo gana (sin conflict resolution)
- [ ] Mi QR escaneado offline (kiosk sin internet) → kiosk valida con cache local (es problema del kiosk, no de la webapp)
- [ ] Token QR expira → al refresh la pantalla, `GET /me/qr` retorna nuevo token
- [ ] Mi Stand desaparece del menu si user pierde `vendor_access` → sigue siguiente request 403 redirect

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports (Mi QR solo en mobile)
- [ ] Lighthouse OK
- [ ] Commit DaVinci + memoria + PENDIENTES.md
