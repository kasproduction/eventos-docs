# QA Master — Barrido Completo de Plataforma

> Auditoria endpoint por endpoint de todos los modulos.
> Actualizado: 2026-04-13 | Metodo: curl real contra backend corriendo

---

## Resumen ejecutivo

| Modulos probados | Endpoints testados | OK | Bugs | Notas |
|-----------------|-------------------|-----|------|-------|
| 21 | 70+ | 70 | 1 corregido | Escritura + 3 roles + presets + 11 field types |

**Bug corregido en esta sesion:**
- `/me`, `/refresh`, `/verify-email`, `/expo-token` no tenian `check.ban` middleware — usuario baneado podia llamar `/me` y recibir 200. Fix: `eeb6ebc`

---

## 1. Auth — Login / Register / Tokens

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/auth/check-email` | POST | No | OK | 200 | Devuelve status: not_found/pending_activation/active |
| `/auth/register` | POST | No | OK | 201 | Devuelve token + user + attendee. registration_approved_at=null si approval ON |
| `/auth/login` | POST | No | OK | 200 | Devuelve token + user + attendee + ban info |
| `/auth/login` (wrong pw) | POST | No | OK | 422 | "Las credenciales son incorrectas" |
| `/auth/login` (5 intentos) | POST | No | OK | 429 | Rate limiter activo (throttle:login) |
| `/auth/me` | GET | Si | OK | 200 | User + attendee + role + event_slug. Ban check activo (403 si baneado) |
| `/auth/me` (baneado) | GET | Si | OK | 403 | "Acceso suspendido" + ban reason + expires_at |
| `/auth/me` (token invalido) | GET | No | OK | 401 | "Unauthenticated" |
| `/auth/logout` | POST | Si | OK | 200 | Funciona incluso si baneado |
| `/auth/logout` (baneado) | POST | Si | OK | 200 | Correcto — usuario baneado puede cerrar sesion |
| `/auth/expo-token` | POST | Si | OK | 204 | Requiere event_id en body |

**QA detallado auth/onboarding:** ver `QA-AUTH-ONBOARDING.md`

---

## 2. Agenda

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/events/{id}/agenda` | GET | Si | OK | 200 | 4 dias, 32 sesiones. Estructura: `{ "2026-04-08": [...] }` |
| `/events/{id}/tracks` | GET | Si | OK | 200 | 5 tracks |
| `/events/{id}/agenda/{sessionId}/favorite` | POST | Si | OK | 200 | Toggle: `{ is_favorite: true/false }` |
| `/events/{id}/sessions/{sessionId}/calendar.ics` | GET | Si | No probado | — | Pendiente: verificar generacion .ics |

---

## 3. Speakers

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/events/{id}/speakers` | GET | Si | OK | 200 | 18 speakers con foto, bio, empresa |

---

## 4. Check-in / Mi QR

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/me/qr?event_id={id}` | GET | Si | OK | 200 | token HMAC, user_name, user_photo |
| `/me/qr` (sin event_id) | GET | Si | Validation | 422 | "The event id field is required" |

---

## 5. Sponsors

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/events/{id}/sponsors` | GET | Si | OK | 200 | 30 sponsors con tier, logo, descripcion |

---

## 6. Social Wall

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/events/{id}/wall` | GET | Si | OK | 200 | 17 posts |

---

## 7. Q&A

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/events/{id}/sessions/{sid}/questions` | GET | Si | OK | 200 | 0 preguntas (sin data de prueba) |

---

## 8. Chat

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/sessions/{sid}/chat/messages` | GET | Si | OK | 200 | 0 mensajes (sin data de prueba) |

---

## 9. Encuestas (Polls)

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/events/sessions/{sid}/poll/active` | GET | Si | OK | 200 | `{}` cuando no hay poll activa |
| `/polls/{id}/vote` | POST | Si | No probado | — | Requiere poll activa |

---

## 10. Ratings

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/events/{id}/sessions/{sid}/rate` | POST | Si | OK | 409 | "Ya evaluaste esta sesion" (usuario ya rateó) |

---

## 11. Networking

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/events/{id}/attendees` | GET | Si | OK | 200 | 30 attendees |
| `/me/contacts` | GET | Si | OK | 200 | 0 contactos |
| `/me/contact-requests` | GET | Si | OK | 200 | 0 solicitudes |
| `/events/{id}/suggested-contacts` | GET | Si | OK | 200 | 0 sugerencias (matchmaking) |

---

## 12. Gamification

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/me/points?event_id={id}` | GET | Si | OK | 200 | 65 pts, 14 acciones con earned/completed |
| `/events/{id}/leaderboard` | GET | Si | OK | 200 | 41 en ranking |
| `/events/{id}/passport` | GET | Si | OK | 200 | 0 stamps |
| `/events/{id}/rewards` | GET | Si | OK | 200 | 5 rewards disponibles |

---

## 13. Photobooth / Memorias

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/events/{id}/photos` | GET | Si | OK | 200 | 15 fotos |

---

## 14. Stories / Momentos

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/events/{id}/stories` | GET | Si | OK | 200 | 0 stories |

---

## 15. Profile

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/me/profile` | GET | Si | OK | 200 | name, email, phone, company, job_title, photo_url, social links |

---

## 16. Branding

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/events/{id}/branding` | GET | Si | OK | 200 | primary_color, logo, hero config, event_name, dates |

---

## 17. Modules

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/events/{id}/modules` | GET | Si | OK | 200 | 14 modulos activos |

---

## 18. Registration Fields

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/me/registration-fields` | GET | Si | OK | 200 | 11 campos configurados |

---

## 19. Onboarding

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/events/{id}/onboarding` | GET | No | OK | 200 | steps_config con 7 keys, bg_type, primary_color |

---

## 20. Otros

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/events/{id}/highlights` | GET | Si | OK | 200 | 1 highlight |
| `/events/{id}/announcements` | GET | Si | OK | 200 | 13 anuncios |
| `/events/{id}/banners` | GET | Si | OK | 200 | 3 banners |
| `/events/{id}/documents` | GET | Si | OK | 200 | 3 documentos |
| `/events/{id}/pages` | GET | Si | OK | 200 | 1 pagina custom |
| `/events/{id}/trivia/{id}/answer` | POST | Si | No probado | — | Requiere trivia activa |
| `/me/leads?event_id={id}` | GET | Si | OK | 200 | 0 leads (usuario no es vendedor) |

---

## Endpoints que requieren data de prueba para verificar completamente

| Endpoint | Razon |
|----------|-------|
| Q&A preguntas | No hay preguntas en la sesion de prueba |
| Chat mensajes | No hay mensajes en la sesion de prueba |
| Poll vote | No hay encuesta activa |
| Trivia answer | No hay trivia en sponsor de prueba |
| Stories | No hay stories activas |
| Leads | Usuario no es vendedor |
| Calendar .ics | Requiere verificar generacion del archivo |

---

## Middleware check

| Middleware | Donde aplica | Estado |
|-----------|-------------|--------|
| `AuthenticateApi:sanctum` | Todas las rutas protegidas | OK — 401 si token invalido |
| `check.ban` | Todas las rutas protegidas + auth/me,refresh | OK — 403 si baneado |
| `check.ban` excluido de | `/auth/logout` | OK — baneado puede cerrar sesion |
| `throttle:login` | `/auth/login` | OK — 429 despues de 5 intentos |
| `throttle:api` | Todas las rutas protegidas | OK |
| `SecurityHeaders` | Global | OK — X-Frame-Options, CSP, etc |

---

---

## Endpoints de escritura (POST/PUT/DELETE)

| Endpoint | Metodo | Resultado | HTTP | Notas |
|----------|--------|-----------|------|-------|
| `/events/{id}/wall` | POST | OK | 200 | Crea post, devuelve id + body |
| `/events/{id}/wall/{pid}/like` | POST | OK | 200 | Devuelve likes_count |
| `/events/{id}/wall/{pid}/comments` | POST | OK | 200 | Crea comentario |
| `/events/{id}/sessions/{sid}/questions` | POST | OK | 200 | Crea pregunta con status: pending |
| `/events/{id}/agenda/{sid}/favorite` | POST | OK | 200 | Toggle: is_favorite true/false |
| `/contacts/request` | POST | OK | 200 | Requiere receiver_attendee_id + event_id |
| `/contacts/block/{id}` | POST | OK | 204 | Requiere event_id en body |
| `/contacts/block/{id}` | DELETE | OK | 204 | Desbloquear |
| `/me/profile` | PUT | OK | 200 | Actualiza company, job_title, etc |
| `/me/registration-fields` | PUT | OK | 200 | Guarda campos custom |
| `/events/{id}/onboarding/survey` | POST | OK | 200 | Guarda intereses |
| `/auth/expo-token` | POST | OK | 204 | Requiere token + event_id |
| `/events/{id}/photos` | POST | Validacion | 422 | "photo field required" (correcto sin archivo) |
| `/events/{id}/sessions/{sid}/rate` | POST | OK | 409 | "Ya evaluaste" (correcto, duplicado) |

---

## Pruebas por rol

### Presencial (14 modulos)
- Agenda, speakers, sponsors, social, networking, gamification, QR, chat, Q&A: OK
- Modulos: agenda, speakers, documentos, anuncios, chat, encuestas, banners, patrocinadores, fotos, social, leaderboard, networking, checkin, leads

### Virtual (11 modulos)
- Ve: agenda, speakers, documentos, anuncios, chat, encuestas, banners, patrocinadores, fotos, social, leaderboard
- NO ve: checkin, leads, networking presencial
- QR token: null (correcto — badge sin QR en la app)

### Vendedor
- Mi Stand: OK (responde, name puede estar vacio si no tiene sponsor asignado)
- Leads: OK (0 leads, correcto para prueba)
- Export leads: OK (200, genera CSV)

---

---

## Endpoints pendientes completados (sesion 2)

| Test | Resultado | HTTP | Notas |
|------|-----------|------|-------|
| Upload foto profile (multipart) | OK | 200 | Devuelve photo_url |
| Calendar .ics download | OK | 200 | Genera VCALENDAR valido (BEGIN:VCALENDAR, VEVENT) |
| Q&A upvote (pregunta aprobada) | OK | 200 | `{ upvotes: 1, my_upvote: true }` |
| Q&A upvote (pregunta pending) | Correcto | 404 | Preguntas pendientes no son votables |
| Poll active | OK | 200 | Devuelve poll con questions y options |
| Poll vote | OK | 200 | `{ voted: true, question_id: 4 }` |
| Modulos visibilidad | OK | 200 | 14 modulos con is_visible, filtrado por rol |
| Rate limit registro | Sin throttle estricto | 201x5 | 5 registros seguidos pasaron. Mitigacion: CAPTCHA en landing (planificado) |

## Notas de seguridad

- **Registro sin rate limit estricto**: El endpoint `/auth/register` no tiene throttle dedicado. En produccion agregar CAPTCHA (reCAPTCHA v3) en la landing web. En app movil el riesgo es menor.
- **Poll questions vacias**: Si se crea un LivePoll sin preguntas, la API devuelve `questions: []`. La app maneja esto correctamente (no muestra nada).

---

## 21. Presets API (2026-04-13 — tarea 1.x-E-B)

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/presets/countries` | GET | No | OK | 200 | 53 paises, formato [{value, label}] |
| `/presets/industries` | GET | No | OK | 200 | 20 industrias |
| `/presets/cities/CO` | GET | No | OK | 200 | 20 ciudades colombianas |
| `/presets/cities/ZZ` | GET | No | OK | 200 | [] (pais inexistente, array vacio) |
| `/presets/invalid` | GET | No | OK | 404 | "Preset not found" |
| `/events/1/onboarding` | GET | No | OK | 200 | preset_options inyectadas: industry=20, country=53 |
| `/me/registration-fields` | PUT | Si | OK | 200 | Guarda searchable_select/checkbox_group/date correctamente |

### Tipos de campo onboarding verificados

| Tipo | Config | Render app | Almacenamiento | Estado |
|------|--------|------------|----------------|--------|
| `text` | OK | TextInput | profile/fields | OK |
| `tel` | OK | phone-pad | fields | OK |
| `email` | OK | email-address | fields | OK |
| `number` | OK | numeric | fields | OK |
| `url` | OK | url keyboard | fields | OK |
| `select` | OK | SelectSheet (radio) | fields | OK |
| `searchable_select` | OK + preset | SearchableSheet (busqueda+radio) | fields | OK |
| `checkbox` | OK | Switch toggle | fields | OK |
| `checkbox_group` | OK | CheckboxGroupSheet (multi-select) | fields como CSV | OK |
| `textarea` | OK | multiline 4 lineas | fields | OK |
| `date` | OK | DateTimePicker nativo | fields como ISO | OK |

### Filament admin verificado

- 11 tipos disponibles en selector (3 nuevos: searchable_select, checkbox_group, date)
- Campo `preset` visible solo para searchable_select
- Campo `options` visible para select, searchable_select, checkbox_group
- Seeder actualizado con ejemplos de los 3 tipos

---

## Estado final QA

| Categoria | Total | OK | Bugs | Notas |
|-----------|-------|-----|------|-------|
| GET endpoints | 40+ | 40 | 0 | Todos responden |
| POST/PUT/DELETE | 15+ | 15 | 0 | Escritura funciona |
| Auth/Ban/Approval | 11 | 11 | 1 corregido | check.ban en auth routes |
| Presets API | 7 | 7 | 0 | countries, industries, cities, onboarding |
| Field types | 11 | 11 | 0 | 8 existentes + 3 nuevos |
| Roles (3) | 3 | 3 | 0 | presencial/virtual/vendedor |
| Middleware | 5 | 5 | 0 | auth, ban, throttle, security headers |
| **TOTAL** | **70+** | **70** | **1 corregido** | Plataforma solida |
