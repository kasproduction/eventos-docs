# W.10 — Notificaciones + Perfil + Settings + Mi QR + Mis... + Cambiar evento

> Hub personal del usuario. Notificaciones, perfil editable, settings, Mi QR, Mis Stands (si vendor), Mis Redenciones, Mis Prizes, Mi Recap (link Fase 2), Soporte access, Cambiar evento.
>
> **Estimacion:** ~8h (expandida de 5h por sub-pages adicionales).
> **Dependencias:** W.0, W.1, W.9 (rewards/prizes), W.15 (Mis Stands), W.17 (Soporte).
> **Estado:** Pendiente.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `AUTH-SPEC.md`
- App movil: `screens/notifications/` y `screens/profile/`

---

## Alcance

1. Web Notifications API (push browser-side cuando webapp esta abierta o en background con permiso)
2. Listado notificaciones in-app
3. Perfil editable: avatar, nombre, bio, intereses, redes
4. Settings: idioma (es/en/pt), tema (Noir/Lux), privacy, blocked list (link a W.8)
5. Sesiones activas + logout multi-device
6. Cerrar cuenta (Fase 2 — solo placeholder)
7. **Mi QR**: codigo QR personal del asistente para escaneo presencial (display readonly, util si user esta en evento hibrido)
8. **Mis Stands** (si vendor): link a W.15 Vendor Dashboard
9. **Mis Redenciones**: lista canjes de rewards (link a W.9)
10. **Mis Prizes**: golden tickets + sorteos + premios (link a W.9 tab Prizes)
11. **Mi Recap** (Fase 2 webapp; Fase 1 deeplink a app movil)
12. **Soporte**: link a W.17 + boton "Crear ticket"
13. **Cambiar evento** (si user esta inscrito en multiples eventos): selector de evento activo

---

## Endpoints (verificar)

- `GET /api/v1/me/notifications?cursor`
- `POST /api/v1/me/notifications/{id}/read`
- `POST /api/v1/me/notifications/read-all`
- `GET /api/v1/me/profile`
- `PATCH /api/v1/me/profile`
- `GET /api/v1/auth/sessions` (existente)
- `POST /api/v1/auth/logout` y `logout-all`
- `POST /api/v1/me/push-subscriptions` — subscribe Web Push
- `GET /api/v1/me/qr-token` — token QR del asistente (rotativo HMAC)
- `GET /api/v1/me/events?registered=true` — eventos donde estoy inscrito (para selector)
- `POST /api/v1/me/events/{id}/set-active` — cambiar evento activo

Socket events:
- `notification.new` (RT badge update)

---

## Fase 0 — Hooks (~30min) — 0/3

- [ ] `useNotifications()` — infinite query
- [ ] `useProfile()` — read + mutation
- [ ] `useSessions()` — listado activas

---

## Fase 1 — Web Notifications API (~1h) — 0/4

### 1.1 Permission — 0/2
- [ ] Modal opt-in primera vez ("Quieres recibir avisos?")
- [ ] Si aceptado: subscribe Service Worker + POST a backend

### 1.2 Render notification — 0/2
- [ ] Service Worker recibe push → muestra Notification con title + body
- [ ] Click → abre webapp en URL relevante

---

## Fase 2 — Listado in-app (~1h) — 0/4

### 2.1 UI — 0/2
- [ ] Badge en pill bar con count unread
- [ ] Click → opens panel `<NotificationsPanel />` con lista

### 2.2 Acciones — 0/2
- [ ] Click notif → marca como leida + redirect a target
- [ ] Boton "Marcar todas como leidas"

---

## Fase 3 — Perfil editable (~1h) — 0/3

### 3.1 Form — 0/2
- [ ] Avatar upload (max 2MB, crop circular)
- [ ] Inputs: nombre, bio, intereses (multi-select), redes sociales

### 3.2 Submit — 0/1
- [ ] Mutation con optimistic + toast confirmacion

---

## Fase 4 — Settings (~1h) — 0/4

### 4.1 Idioma — 0/1
- [ ] Selector es-CO / en / pt-BR → cambio inmediato (next-intl)

### 4.2 Tema — 0/1
- [ ] Switch Noir / Lux → cambio inmediato

### 4.3 Privacy — 0/1
- [ ] Toggles: visible en networking, mostrar puntos publicamente

### 4.4 Sesiones — 0/1
- [ ] Lista sesiones activas con boton "Cerrar" individual + "Cerrar todas las otras"

---

## Fase 4.5 — Mi QR (~30min) — 0/3

### 4.5.1 Display — 0/3
- [ ] `<MiQR />` con QR generado del token HMAC + nombre + foto + nombre evento
- [ ] Boton "Refresh" para regenerar QR (rotacion HMAC)
- [ ] Brightness boost en mobile (opcional, util escaneo presencial)

---

## Fase 4.6 — Sub-pages Mis... (~1.5h) — 0/5

### 4.6.1 Menu user — 0/2
- [ ] User menu dropdown con: Perfil, Mi QR, Mi Stand (si vendor), Mis Redenciones, Mis Prizes, Mi Recap, Soporte, Configuracion, Cerrar sesion
- [ ] Cada item link al modulo correspondiente

### 4.6.2 Detection vendor — 0/1
- [ ] Si `user.vendor_access || user.tags.includes('stand')` → mostrar "Mi Stand" link a W.15
- [ ] Si no → ocultar

### 4.6.3 Mi Recap — 0/2
- [ ] Si `user.recap_image_url !== null` → link a vista del recap (Fase 2 web; Fase 1 deeplink app movil)
- [ ] Si no → ocultar

---

## Fase 4.7 — Cambiar evento (~30min) — 0/3

### 4.7.1 Selector — 0/3
- [ ] Si user esta inscrito en >1 evento → dropdown evento activo en header
- [ ] Click otro evento → mutation set-active + reload webapp con context nuevo
- [ ] Lista incluye eventos pasados (con badge "Finalizado")

---

## Fase 5 — Tests (~30min) — 0/3

### 5.1 Vitest — 0/1
- [ ] Optimistic update perfil

### 5.2 Playwright — 0/2
- [ ] Happy path: editar perfil + cambiar idioma + cerrar sesion remota
- [ ] Edge case: notification permission denied → no mostrar prompt de nuevo

---

## Edge cases

- [ ] Permission denied 2 veces → no mostrar prompt mas (recordar en localStorage)
- [ ] Notif click pero webapp cerrada → abrir nueva tab con URL target
- [ ] Avatar upload >2MB → error pre-submit
- [ ] Cambio tema con custom branding → respeta primary color, solo cambia surface
- [ ] Logout-all desde otra sesion → sesion actual recibe 401 al next request → redirect login
- [ ] Notif duplicada (RT + polling) → dedup por id
- [ ] User edita perfil concurrente (otra pestana) → ultimo gana, refresh recomendado
- [ ] QR escaneado offline (kiosk sin internet) → kiosk valida con cache local, no falla
- [ ] User cambia evento durante sesion live de otro evento → confirmar antes "Saldras de la sesion live"
- [ ] User en 1 solo evento → no mostrar selector cambiar evento
- [ ] Mi Recap aun en proceso → mostrar "Tu recap se esta generando"
- [ ] Mi Stand pero el user perdio vendor_access → ocultar link inmediato
- [ ] Token QR expirado (>15min sin refresh) → auto-refresh al abrir Mi QR

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports
- [ ] Web Push validado en Chrome + Edge + Firefox (Safari Web Push requiere PWA install)
- [ ] Commit DaVinci + memoria + PENDIENTES.md
