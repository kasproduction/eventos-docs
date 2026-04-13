# QA Master — Barrido Completo de Plataforma

> Auditoria endpoint por endpoint de todos los modulos.
> Actualizado: 2026-04-12 | Metodo: curl real contra backend corriendo

---

## Resumen ejecutivo

| Modulos probados | Endpoints testados | OK | Bugs | Notas |
|-----------------|-------------------|-----|------|-------|
| 20 | 50+ | 48 | 1 corregido | Escritura + 3 roles verificados |

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

## Proximos pasos QA

- [ ] Probar rate limiting en endpoints criticos (registro, login ya verificados)
- [ ] Verificar que modulos desactivados devuelven 403/404
- [ ] Probar upload de fotos real (profile, social wall, photobooth)
- [ ] Probar calendar .ics download
- [ ] Q&A: aprobar pregunta desde admin y probar upvote
- [ ] Crear poll desde Filament y probar vote
