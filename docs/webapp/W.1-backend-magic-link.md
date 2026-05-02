# W.1 Backend — Magic Link + Login Slideshow ✅ CERRADO

> Sesion backend dedicada en `eventos-backend`. Bloqueante de W.1 webapp F4-F9 (ADR-023).
>
> **Estimacion:** ~4h. Solo backend Laravel + Pest. Cero frontend.
> **Dependencias:** Backend Sanctum ya instalado. Mailpit local para test emails.
> **Estado:** ✅ **CERRADO 2026-05-02** — commit `ef24003` en `eventos-backend` branch `feature/magic-link-auth`. 10/10 tests Pest passing.

---

## Lectura obligatoria

- `docs/webapp/AUTH-SPEC.md` (flujo magic link completo)
- `docs/webapp/DECISIONS.md` ADR-021 (login slideshow feature nuevo) y ADR-023 (bloqueo F4-F9)

---

## Alcance

1. Migration + model `magic_link_tokens`
2. Endpoints magic link (`POST /auth/magic-link`, `POST /auth/verify-magic-link`)
3. Endpoint refresh token (verificar si existe, agregar si no)
4. Mailable `MagicLinkMail` con template branded por evento
5. **Feature nuevo Login Slideshow** (ADR-021):
   - Migration + model `event_login_slides`
   - Endpoint publico `GET /api/v1/events/{slug}/login-slides`
   - Filament resource `LoginSlideResource`
   - Campo nuevo opcional `events.branding.welcome_message` (JSON column)
6. Pest tests cubriendo happy path + 8 edge cases AUTH-SPEC

---

## Fase 0 — Setup + Mailpit (~15min) — 0/2

### 0.1 Mailpit local — 0/1
- [ ] Verificar Mailpit corriendo en :8025 (UI) + :1025 (SMTP). Si no, instalar via `winget install MailPit` o Docker
- [ ] `.env` con `MAIL_MAILER=smtp` `MAIL_HOST=127.0.0.1` `MAIL_PORT=1025`

### 0.2 Branch + ramas — 0/1
- [ ] `git checkout -b feature/magic-link-auth` en `eventos-backend`

---

## Fase 1 — Magic link tokens (~1h) — 0/4

### 1.1 Migration `magic_link_tokens` — 0/1
- [ ] `php artisan make:migration create_magic_link_tokens_table`
- Schema:
  - `id` bigint
  - `user_id` foreign constrained
  - `token_hash` string 64 (SHA-256 del token raw, NUNCA guardar raw)
  - `expires_at` timestamp
  - `used_at` timestamp nullable
  - `ip_address` string 45 nullable (IPv6 cabe)
  - `user_agent` string 500 nullable
  - `device_name` string 100 nullable (para identificar sesion luego)
  - `timestamps`
- Index: `(token_hash, expires_at, used_at)` para verify rapido
- Index: `(user_id, created_at)` para rate limit

### 1.2 Model `MagicLinkToken` — 0/1
- [ ] `app/Models/MagicLinkToken.php` con scope `valid()` (not used + not expired)
- [ ] Relacion `user()`

### 1.3 Endpoint `POST /api/v1/auth/magic-link` — 0/1
- [ ] Controller `Api/V1/AuthController@requestMagicLink`
- [ ] Request validation: email required, valid email
- [ ] Rate limit 3 por email por hora (existing `RateLimiter` middleware)
- [ ] **Anti-enumeration**: si email no existe, devolver 200 generico igual ("si tu email esta registrado, recibiras un link"). NO 404
- [ ] Si existe: genera token random 32 chars hex (`Str::random(32)`), hashea con SHA-256, guarda en BD, dispatch `SendMagicLinkEmail` job (queue)
- [ ] TTL 15 min via `expires_at = now()->addMinutes(15)`

### 1.4 Endpoint `POST /api/v1/auth/verify-magic-link` — 0/1
- [ ] Controller `Api/V1/AuthController@verifyMagicLink`
- [ ] Request validation: token required string
- [ ] Lookup por `token_hash` (hash inputte primero)
- [ ] Validar: not used, not expired, user not banned
- [ ] Marcar `used_at = now()`, registrar `ip_address` + `user_agent`
- [ ] Generar Sanctum token con `device_name = web-{userAgent_hash}`
- [ ] Devolver `{ token: 'sanctum-XXX', user: {...basic profile} }`
- [ ] Errores: 410 expirado, 410 ya usado, 401 invalido (todos genericos)

---

## Fase 2 — Refresh + logout (~30min) — 0/3

### 2.1 Verificar `POST /auth/refresh` — 0/1
- [ ] Si NO existe: agregar. Validar Bearer current valido + ip + user-agent matchean. Emitir nuevo Bearer reemplazando anterior. Revoke el viejo
- [ ] Si EXISTE: verificar que cumpla AUTH-SPEC (renovacion < 1h restante)

### 2.2 `POST /auth/logout` — 0/1
- [ ] Verificar que existe y revoca solo el token actual

### 2.3 `POST /auth/logout-all` — 0/1
- [ ] Verificar que existe y revoca todos los tokens del usuario

---

## Fase 3 — Mailable + email template (~45min) — 0/4

### 3.1 Mailable `MagicLinkMail` — 0/1
- [ ] `php artisan make:mail MagicLinkMail`
- [ ] Constructor recibe `User $user`, `Event $event` (evento contextual), `string $url` (link completo con token raw)
- [ ] Subject: "Tu link de acceso a {event_name}"
- [ ] Markdown template branded por evento (logo + primary_color)

### 3.2 Markdown template — 0/1
- [ ] `resources/views/emails/magic-link.blade.php`
- [ ] Logo evento + primary_color custom + boton CTA
- [ ] Plain-text fallback obligatorio (Bancolombia firewalls)
- [ ] Footer "Si no solicitaste este email, ignoralo"
- [ ] Responsive mobile

### 3.3 Job `SendMagicLinkEmail` — 0/1
- [ ] Queue job para no bloquear request
- [ ] Retry 3x con backoff exponencial
- [ ] Log a Sentry si falla 3 veces

### 3.4 DKIM/SPF/DMARC test — 0/1
- [ ] Probar contra Mailpit local (display correcto)
- [ ] Validar via Mail-Tester >9/10 antes de produccion (NO bloquea sesion, blocking de deploy)

---

## Fase 4 — Login Slideshow feature (~1h) — 0/5

### 4.1 Migration `event_login_slides` — 0/1
- [ ] `php artisan make:migration create_event_login_slides_table`
- Schema:
  - `id`, `event_id` foreign constrained cascade delete
  - `image_url` string 500 (R2 storage)
  - `video_url` string 500 nullable (Tier 2 #9 — MP4 looped mute si esta seteado, fallback imagen)
  - `label` string 100 nullable (texto chico arriba)
  - `title` string 255 nullable (display headline)
  - `subtitle` string 500 nullable (texto chico abajo)
  - `has_overlay_text` boolean default true (ADR-021 — toggle si imagen ya trae texto)
  - `cta_text` string 100 nullable, `cta_url` string 500 nullable
  - `sort_order` smallInteger default 0
  - `enabled` boolean default true
  - `starts_at` timestamp nullable, `expires_at` timestamp nullable
  - `timestamps`
- Index: `(event_id, enabled, sort_order)`

### 4.2 Model `EventLoginSlide` + Observer — 0/1
- [ ] `app/Models/EventLoginSlide.php` con scope `active()` (enabled + within window)
- [ ] Relacion `event()` belongsTo
- [ ] Observer si necesita cache invalidation por evento

### 4.3 Endpoint publico `GET /events/{slug}/login-slides` — 0/1
- [ ] Controller `Api/V1/PublicEventController@loginSlides`
- [ ] Sin auth (es endpoint publico para landing/login)
- [ ] Cache 5min con tag `login-slides:{event_id}`
- [ ] Devuelve array de slides activos ordenados con: `id, image_url, label, title, subtitle, cta_text, cta_url`

### 4.4 Filament resource `LoginSlideResource` — 0/1
- [ ] `php artisan make:filament-resource EventLoginSlide`
- [ ] Form: ImageUploadField para `image_url`, TextInput label/title/subtitle, TextInput cta_text/cta_url, DateTimePicker starts_at/expires_at, Toggle enabled, sort_order
- [ ] Table: image preview thumbnail, title, sort, enabled toggle, ordering por sort_order
- [ ] Reorderable via drag (Filament soporta nativo)
- [ ] NavigationGroup "Webapp" o "Login" (nuevo group)

### 4.5 Campo `organizer_logo_url` en events (Tier 2 #8) — 0/1
- [ ] Migration: agregar columna `organizer_logo_url` string 500 nullable en `events` (organizador distinto del evento, ej Bancolombia presenta Summit)
- [ ] Filament: ImageUploadField en EventResource seccion Branding, helper "Logo del organizador (opcional, si distinto del evento)"
- [ ] API: incluir en response de evento existente
- [ ] **Reemplaza el campo `welcome_message`** que estaba en plan original (descartado en ADR-024 por estado contextual del evento)

### 4.6 Resolver event status contextual — 0/1
- [ ] Backend computa `event.live_status` automatico en serializer:
  - `upcoming` si `start_date > now`
  - `live_today` si `start_date <= now < (start_date + 12h)` (dia del evento, antes que arranque)
  - `live_now` si hay sesiones activas en `event_sessions` con `started_at AND NOT ended_at`
  - `ended` si `end_date < now`
- [ ] Endpoint publico `GET /api/v1/events/{slug}` incluye `live_status` + `countdown_seconds` (segundos hasta start)
- [ ] Test: fixtures con cada estado, assert serializer correcto

---

## Fase 5 — Tests Pest (~1h) — 0/8

### 5.1 Magic link request tests — 0/3
- [ ] `tests/Feature/Auth/MagicLinkRequestTest.php`
- [ ] Test: email registrado dispara email + crea token (con `Mail::fake()`)
- [ ] Test: email NO registrado devuelve 200 generico SIN crear token (anti-enumeration)
- [ ] Test: rate limit 3/hour bloquea cuarta peticion

### 5.2 Magic link verify tests — 0/3
- [ ] `tests/Feature/Auth/MagicLinkVerifyTest.php`
- [ ] Test: token valido devuelve Bearer + marca used_at + registra ip/user-agent
- [ ] Test: token expirado devuelve 410
- [ ] Test: token usado devuelve 410 en segundo intento

### 5.3 Login slides tests — 0/2
- [ ] `tests/Feature/LoginSlidesTest.php`
- [ ] Test: endpoint publico devuelve solo slides enabled + dentro de window + ordenados
- [ ] Test: cache funciona (segundo request hits cache)

---

## Cierre del modulo backend

- [ ] Todos los tests pasan
- [ ] Mailpit recibe email con DKIM/SPF/DMARC simulados
- [ ] Filament: organizador puede subir slides + reorder + toggle enabled, ver preview
- [ ] Branch listo para merge
- [ ] Memoria de sesion en `~/.claude/projects/.../memory/project_session_YYYYMMDD.md`
- [ ] Roadmap `W.1-setup-auth.md` actualizado con backend cerrado, F4-F9 webapp **desbloqueadas**

---

## Endpoints nuevos resumen

| Endpoint | Metodo | Auth? |
|---|---|---|
| `/api/v1/auth/magic-link` | POST | No |
| `/api/v1/auth/verify-magic-link` | POST | No |
| `/api/v1/auth/refresh` | POST | Bearer |
| `/api/v1/events/{slug}/login-slides` | GET | No (publico) |

---

## Tablas nuevas resumen

| Tabla | Descripcion |
|---|---|
| `magic_link_tokens` | Tokens magic link con TTL 15min, anti-replay |
| `event_login_slides` | Feature nuevo: slideshow customizable por evento (ADR-021) |

---

## Riesgos backend

1. **DKIM/SPF/DMARC en `eventos.app`** — bloqueante de produccion, no de dev. Test temprano con Mail-Tester
2. **Rate limit cache driver** — debe ser Redis (existente), no array
3. **Cache login-slides invalidation** — Observer debe limpiar cache al editar/eliminar slide
4. **Bancolombia firewalls bloquean emails externos** — mitigacion en F4 webapp UX (mensaje claro + fallback contrasena)

---

## NO entra en este modulo

- 2FA (Fase 2)
- SSO Google/Microsoft (Fase 2)
- Anomaly detection geo (Fase 2)
- SMS OTP fallback (Fase 2 si Bancolombia lo pide)
- Webhooks de email bounces (Fase 2)
