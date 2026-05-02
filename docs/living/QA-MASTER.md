# QA Master — Barrido Completo de Plataforma

> Auditoria endpoint por endpoint de todos los modulos.
> Actualizado: 2026-05-02 | Metodo: curl real + tests automatizados
> Tests app movil/backend: 71 archivos, 753 test methods (Pest), 1947+ assertions
> Tests webapp: 22 Vitest unit + 12 Playwright E2E = 34 passing (commit 4e8e588)
> IMPORTANTE: Socket server debe estar corriendo para real-time. Iniciar con: cd eventos-socket && npx ts-node src/index.ts

---

## Resumen ejecutivo

| Modulos probados | Endpoints testados | Tests automatizados | Archivos test | Bugs historicos |
|-----------------|-------------------|---------------------|---------------|-----------------|
| 38+ | 197 | 787 | 77 | 290 registrados, todos corregidos |

### Cambios desde QA 04-17 (490 tests) a QA 04-25 (712 tests)

| Area nueva | Tests | Fecha |
|------------|-------|-------|
| Session Lifecycle (delay, cancel, revert, cascada) | 23 | 04-21 |
| Session Stats (attendance, engagement, export) | 11 | 04-20 |
| Session Config (start/end/cancel/delay, live-config) | 23 | 04-18 |
| Room Check-in (occupancy, attendees, attendance checks) | 23 | 04-21 |
| Staff Check-in (assign, scan, batch, reassign) | 18 | 04-21 |
| Room Stress (concurrent scans, edge cases) | 5+ | 04-20 |
| Webhooks Outbound (5 eventos, retry, payload) | 8 | 04-21 |
| Webhooks Inbound (check-in externo, batch) | 11 | 04-21 |
| Webhook Model (config, filtering) | 5+ | 04-21 |
| Trivia Kahoot-style (rondas, speed bonus, leaderboard) | 10 | 04-23 |
| Spin/Ruleta (sectores, pesos, rewards) | 10 | 04-22 |
| Jackpot/Sorteo (draw, golden ticket, claim) | 10 | 04-22 |
| Game Export (CSV resultados) | 5+ | 04-23 |
| Event Pulse (bootstrap, 7 secciones, auth) | 20 | 04-24 |
| Photo Contest (lifecycle, anti-gaming, voting) | 25 | 04-24 |
| Stand Stats (leads, views, favorites, contacts, tier) | 13 | 04-20 |
| Data Center (API auth, exports, jobs, contract) | 31 | 04-25 |
| W.1B Backend Magic Link (Pest 8) + Login Slides (Pest 2) | 10 | 05-02 |
| W.1 Webapp Vitest unit (mailcheck/validators/api) | 22 | 05-02 |
| W.1 Webapp Playwright E2E (auth-gate/login-form/verify-page) | 12 | 05-02 |
| **Total nuevos** | **~295** | 04-18 a 05-02 |

---

## 1. Auth — Login / Register / Tokens

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/auth/check-email` | POST | No | OK | 200 | Devuelve status: not_found/pending_activation/active |
| `/auth/register` | POST | No | OK | 201 | Devuelve token + user + attendee. registration_approved_at=null si approval ON |
| `/auth/login` | POST | No | OK | 200 | Devuelve token + user + attendee + ban info |
| `/auth/login` (wrong pw) | POST | No | OK | 422 | "Credenciales incorrectas. X intentos restantes." |
| `/auth/login` (5to intento) | POST | No | OK | 423 | "Cuenta bloqueada temporalmente" + locked_until + remaining_attempts=0 |
| `/auth/login` (bloqueado) | POST | No | OK | 423 | "Intenta en X minutos" (incluso con password correcta) |
| `/auth/login` (desactivado) | POST | No | OK | 403 | "Cuenta desactivada." |
| `/auth/me` | GET | Si | OK | 200 | User + attendee + role + event_slug. Ban check activo (403 si baneado) |
| `/auth/me` (baneado) | GET | Si | OK | 403 | "Acceso suspendido" + ban reason + expires_at |
| `/auth/me` (token invalido) | GET | No | OK | 401 | "Unauthenticated" |
| `/auth/logout` | POST | Si | OK | 200 | Funciona incluso si baneado |
| `/auth/expo-token` | POST | Si | OK | 204 | Requiere event_id en body |

### 1b. Auth Toast Messages — 24 tests automatizados (AuthToastMessagesTest.php)

Verifica el mensaje EXACTO que el usuario ve en el toast de la app para cada error:

**Login (8 tests):**

| Caso | HTTP | Mensaje que ve el usuario |
|------|------|--------------------------|
| 1er intento fallido | 422 | "Credenciales incorrectas. 4 intentos restantes." |
| 2do intento | 422 | "Credenciales incorrectas. 3 intentos restantes." |
| 4to intento | 422 | "Credenciales incorrectas. 1 intento restante." (singular) |
| 5to intento | 423 | "Cuenta bloqueada temporalmente. Intenta en 30 minutos." |
| Cuenta bloqueada | 423 | "Cuenta bloqueada temporalmente. Intenta en X minutos." |
| Cuenta desactivada | 403 | "Cuenta desactivada." |
| Email no existente | 422 | "Credenciales incorrectas." (no revela si email existe — seguridad) |
| Login exitoso | 200 | token + user + attendee (no toast) |

**Registro (16 tests):**

| Caso | HTTP | Mensaje que ve el usuario |
|------|------|--------------------------|
| Registro exitoso | 201 | token + user + attendee (no toast, navega) |
| Email duplicado | 422 | "The email has already been taken." |
| Evento no existe | 422 | "El evento no existe o no esta disponible." |
| Registro deshabilitado | 422 | "El registro para este evento esta cerrado." |
| Solo invitados (sin restriction) | 422 | "Este evento solo acepta invitaciones." |
| Capacidad maxima | 422 | "Este evento ha alcanzado su capacidad maxima." |
| Ventana no abre | 422 | "El registro aun no esta abierto para este evento." |
| Ventana ya cerro | 422 | "El registro para este evento ya cerro." |
| Consent no aceptado | 422 | "Debes aceptar los terminos y condiciones." |
| Access code requerido | 422 | errors.access_code |
| Access code invalido | 422 | "El codigo de acceso no es valido." |
| Restriction dominio (custom) | 422 | Mensaje configurable del admin |
| Restriction + approval | 201 | Registro OK pero approved_at=null |
| Nombre vacio | 422 | errors.name |
| Password corta | 422 | errors.password |
| Password no coincide | 422 | errors.password |

### 1c. Registration Restriction — 21 tests (RegistrationRestrictionTest.php)

| Caso | HTTP | Resultado |
|------|------|-----------|
| Restriction OFF | 201 | Cualquiera entra |
| email_list — email en lista | 201 | Pasa |
| email_list — email NO en lista | 422 | Rechazado |
| email_list — case insensitive | 201 | PEDRO@EMPRESA.COM matchea pedro@empresa.com |
| domain — dominio correcto | 201 | Pasa |
| domain — dominio incorrecto | 422 | Rechazado |
| domain — multiples dominios | 201 | Cualquier dominio de la lista |
| domain — dominio con @ (error admin) | 201 | Sanitiza automatico |
| both — email en lista, dominio no | 201 | OR logic |
| both — dominio OK, email no | 201 | OR logic |
| both — ni email ni dominio | 422 | Rechazado |
| restriction + approval | 201 | Pasa pero approved_at=null |
| mensaje custom | 422 | Devuelve texto configurado por admin |
| mensaje default | 422 | "No estas autorizado para registrarte en este evento." |
| lista con espacios/lineas vacias | 201 | Trim funciona |
| listas vacias bloquea todo | 422 | Nadie entra |
| registration_disabled prioridad | 422 | "registro cerrado" (gana sobre restriction) |
| access_code + restriction | 422 | Ambos validan |
| invite_only + restriction | 201 | Restriction habilita registro filtrado |
| invite_only sin restriction | 422 | Bloqueo total (comportamiento original) |

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
| `/events/{id}/surveys` | GET | Si | OK | 200 | Incluye scope event + post_event |
| `/polls/{id}/vote` | POST | Si | OK | 200 | Star rating, multiple choice, open text |
| `/events/{id}/post-event-survey` | GET | Si | OK | 200 | null si no hay, o encuesta activa con preguntas + my_answers |

### 9b. Encuesta Post-Evento — 9 tests (PostEventSurveyTest.php) + simulacion curl

**Tests automatizados:**

| Caso | Resultado |
|------|-----------|
| No hay encuesta → null | OK |
| Encuesta draft → null | OK |
| Encuesta activa → 3 preguntas, 3 tipos | OK |
| Auto-activacion al cambiar evento a ended | OK (draft → active) |
| Encuesta ya activa no se duplica al ended | OK |
| Encuesta scope=event no se activa al ended | OK |
| Votar star rating + multiple choice | OK |
| Aparece en /surveys endpoint | OK |
| Draft no aparece en /surveys | OK |

**Simulacion curl (7 tests manuales):**

| # | Test | Resultado |
|---|------|-----------|
| 1 | GET post-event-survey activa | 5 preguntas, 0 respuestas |
| 2 | Votar star rating (5 estrellas) | voted: true |
| 3 | Votar multiple choice (Networking) | voted: true |
| 4 | Votar texto libre | voted: true |
| 5 | Respuestas en BD | 3 votos correctos con contenido |
| 6 | Aparece en /surveys endpoint | 1 encuesta post-evento |
| 7 | my_answers = 3/3 despues de votar | OK |

**Seeder:** PostEventSurveySeeder — 5 preguntas (star, mc single, mc multi, mc single, text), 35 encuestados, 201 votos. Export CSV funcional desde Filament.

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

## 21. Staff Invite — Gestion de equipos (1.x-H)

| Endpoint | Metodo | Auth | Resultado | HTTP | Notas |
|----------|--------|------|-----------|------|-------|
| `/me/stand` | GET | Si | OK | 200 | Info stand + miembros + cupos |
| `/me/stand/members` | POST | Si | OK | 201 | Invitar por email o attendee_id. Devuelve share_link + token |
| `/me/stand/members/{id}` | DELETE | Si | OK | 204 | Remover miembro. Socket staff:removed al target |
| `/me/stand/transfer` | POST | Si | OK | 200 | Transferir liderazgo |
| `/me/stand/search-attendees` | GET | Si | OK | 200 | Buscar por nombre, excluye miembros actuales |
| `/me/stand/resolve-qr` | POST | Si | OK | 200 | QR token → attendee info para invitar |
| `/me/stand/share-link` | POST | Si | OK | 201 | Genera link generico sin email |
| `/staff-invitations/{token}/info` | GET | Si | OK | 200 | Preview invitacion (para deep link) |
| `/staff-invitations/{token}/accept` | POST | Si | OK | 200 | Acepta, activa vendor access |
| `/staff-invitations/{token}/reject` | POST | Si | OK | 204 | Rechaza |
| `/me/pending-invitations` | GET | Si | OK | 200 | Lista invitaciones pendientes del usuario |
| `/join-team/{token}` (web) | GET | No | OK | 200 | Landing Lumina Noir con store badges |

### 21b. Staff Invite — 23 tests (StandTeamTest.php)

| Caso | Resultado |
|------|-----------|
| Owner ve su stand | OK |
| Non-owner no puede ver | 403 |
| Invitar por email queda pendiente (no auto-activa) | Pendiente |
| Invitar usuario registrado queda pendiente | Pendiente |
| Invitar por attendee_id (QR) | Pendiente |
| Limite de cupos | STAND_FULL |
| Email duplicado | ALREADY_IN_STAND |
| No invitarse a si mismo | CANNOT_ADD_SELF |
| Accept — activa vendor access | has_vendor_access=true |
| Reject | status=removed |
| Invitacion expirada → 410 | INVITATION_EXPIRED |
| Usuario no target no puede aceptar | 403 |
| Multi-stand OFF bloquea | ALREADY_IN_OTHER_STAND |
| Multi-stand ON permite | OK |
| Target ve pendientes | 1 invitacion |
| Expiradas no aparecen en pendientes | 0 |
| Buscar asistentes | Resultados correctos |
| Busqueda excluye miembros | 0 resultados |
| Owner remueve miembro | vendor_access revocado |
| Transferir ownership | owner_attendee_id cambia |
| Landing valida | Muestra stand + "Abrir en la app" |
| Landing expirada | Muestra "expirada" |
| Landing token invalido | Muestra "no encontrada" |

### Socket events staff:

| Evento | Direccion | Payload |
|--------|-----------|---------|
| `staff:invited` | server → target | invitationId, token, sponsorName, sponsorLogo, inviterName |
| `staff:accepted` | server → inviter | attendeeName, attendeeId, sponsorId |
| `staff:rejected` | server → inviter | attendeeName, attendeeId, sponsorId |
| `staff:removed` | server → target | sponsorName, sponsorId |

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

## 22. Rate Limits — SEC-6.2 (2026-04-15)

### Unit tests — ChecksRateLimit trait (10 tests)

| Caso | Resultado |
|------|-----------|
| Bajo el limite → null | OK |
| En el limite → 429 DAILY_LIMIT | OK |
| No cuenta registros de ayer | OK |
| Scope: otro evento no cuenta | OK |
| Config custom del evento (limit=3) | OK |
| Config disabled → sin limite | OK |
| Defaults cuando rate_limits=null | OK |
| Key inexistente → no aplica | OK |
| getDefault retorna valores correctos | OK |
| allDefaults retorna 7 keys | OK |

### Feature tests — Endpoints reales (13 tests)

| Caso | Endpoint | HTTP | Resultado |
|------|----------|------|-----------|
| Wall post limite 10 | POST /wall | 429 | DAILY_LIMIT |
| Wall post bajo limite | POST /wall | 201 | OK |
| Wall post ayer no cuenta | POST /wall | 201 | Reset diario |
| Wall post otro evento | POST /wall | 201 | Scope correcto |
| Comment limite 30 | POST /wall/{id}/comments | 429 | DAILY_LIMIT |
| Q&A limite 10/sesion | POST /sessions/{id}/questions | 429 | DAILY_LIMIT |
| Q&A otra sesion libre | POST /sessions/{id2}/questions | 201 | Scope por sesion |
| Support limite 5 | POST /support | 429 | DAILY_LIMIT |
| Photos limite 20 | POST /photos | 429 | DAILY_LIMIT |
| Stories limite 10 | POST /stories | 429 | DAILY_LIMIT |
| Leads limite 200 | POST /leads | 429 | DAILY_LIMIT |
| Config custom limit=2 | POST /wall | 429 | Respeta config |
| Config disabled | POST /wall | 201 | Ilimitado |

### Spam simulation (8 tests)

| Caso | Resultado |
|------|-----------|
| 15 posts rapidos: 10 OK, 11-15 bloqueados | Todos 429 con code+message |
| Formato respuesta 429 para toast | code + message presentes |
| Q&A por sesion: 10 en A + 10 en B independientes | Scope correcto |
| Respuesta 429 matchea regex limite | "limite.*\d+.*dia" |
| Evento grande limit=50 | Bloquea en 51 |
| Evento sin limites (disabled) | 101 posts OK |
| Dos usuarios independientes | Cada uno su contador |
| Reset diario: bloqueado hoy, libre mañana | Reset OK |

### Filament config verificada

| Campo | Tipo | Default | Funciona |
|-------|------|---------|----------|
| wall_posts enabled/limit | Toggle + Number | true / 10 | OK |
| wall_comments enabled/limit | Toggle + Number | true / 30 | OK |
| qna_questions enabled/limit | Toggle + Number | true / 10 | OK |
| support enabled/limit | Toggle + Number | true / 5 | OK |
| photos enabled/limit | Toggle + Number | true / 20 | OK |
| stories enabled/limit | Toggle + Number | true / 10 | OK |
| leads enabled/limit | Toggle + Number | true / 200 | OK |

### App toasts 429

| Pantalla | Antes | Despues |
|----------|-------|---------|
| Social (posts/photos/stories) | Ya mostraba err.message | OK |
| CommentsSheet | Sin toast | Agregado catch + toast |
| Q&A (QnAPanel) | Ya mostraba err.message | OK |
| Support | Mensaje generico fijo | Ahora usa err.message |
| Scanner leads | err.message (no code) | Ahora usa err.code + DAILY_LIMIT en mapa |

---

## 23. Push Reminders Configurables (2026-04-15)

### Unit tests — ReminderConfig (5 tests)

| Caso | Resultado |
|------|-----------|
| Defaults sin config | enabled=true, windows=[15,5], notify_on_change=true |
| Config custom respetada | windows=[30,10,5] |
| Desactivado | enabled=false |
| Merge parcial conserva defaults | Solo windows custom, resto default |
| DEFAULTS tiene 3 keys | enabled, windows, notify_on_change |

### Feature tests — SendAgendaRemindersJob (14 tests)

| Caso | Resultado |
|------|-----------|
| Push 15 min antes de favorita | Dispatched |
| Sin favorita → no push | Not dispatched |
| Reminder desactivado → no push | Not dispatched |
| Sin expo_push_token → no push | Not dispatched |
| Windows custom [30] → solo 30 min | 1 push (no 15 min) |
| Deduplicacion cache (2 runs) | Solo 1 push |
| 4 attendees con favorita → 4 push | Correcto |
| Sesion cancelada → no push | Not dispatched |
| Cambio hora → push session_changed | Dispatched |
| Cambio titulo → no push | Not dispatched |
| notify_on_change=false → no push | Not dispatched |
| Spam 5x10=50 push | 50 jobs despachados |
| 10 runs seguidos deduped | Solo 1 ronda |
| 2 eventos configs diferentes | Solo evento enabled envia |

### Filament config verificada

| Campo | Tipo | Default | Funciona |
|-------|------|---------|----------|
| enabled | Toggle | true | OK |
| windows | TagsInput | [15, 5] | OK — acepta cualquier minuto 1-60 |
| notify_on_change | Toggle | true | OK |

### App

| Feature | Estado |
|---------|--------|
| Boton "Todas" en Mi Agenda header | OK — agrega favoritas del dia al calendario nativo |
| Push session_changed → invalida agenda+mi-agenda | OK |
| Push session_changed → navega a /(app)/agenda | OK |

---

## 24. Mensaje Anclado Chat — tipo Twitch (2026-04-16)

### Arquitectura

Todo via Socket.IO — zero endpoints backend, zero DB. Redis almacena 1 pinned por sesion (TTL 24h).

| Evento socket | Direccion | Quien | Payload |
|---------------|-----------|-------|---------|
| `chat:pin` | client → server | Admin/moderador | `{ sessionId, message, author? }` |
| `chat:unpin` | client → server | Admin/moderador | `{ sessionId }` |
| `chat:pinned` | server → room | Broadcast | `{ sessionId, message, author, pinnedAt }` |
| `chat:unpinned` | server → room | Broadcast | `{ sessionId }` |

### Flujo

1. Moderador abre chat monitor → ve mensajes en vivo
2. Opcion A: hover mensaje → icono pin → ancla ESE mensaje con su autor
3. Opcion B: escribe texto libre en campo superior → click "Anclar"
4. Socket `chat:pin` → server valida admin → guarda en Redis → broadcast `chat:pinned`
5. App: `PinnedBanner` aparece arriba del panel interactivo (no reduce player)
6. Visible en todos los modes (chat, Q&A, poll) — vive en session-stream, no dentro del panel
7. Al unirse a sesion: server envia pinned actual (si existe) junto con history
8. Usuario puede ocultar con X (solo local, no para todos)
9. Moderador puede desanclar → socket `chat:unpin` → broadcast `chat:unpinned`

### Chat Monitor UI

| Elemento | Ubicacion | Funcion |
|----------|-----------|---------|
| Campo texto + boton "Anclar" | Debajo de controles | Texto libre custom |
| Icono pin en cada mensaje | Hover acciones (junto a delete/ban) | Ancla mensaje existente |
| Banner azul activo | Arriba del feed | Muestra pinned actual + boton desanclar |

### App UI

| Componente | Archivo | Ubicacion |
|-----------|---------|-----------|
| `PinnedBanner` | `components/ui/PinnedBanner.tsx` | Dentro de flex:1 del panel interactivo |
| Pin icon + message + author + X close | Inline | Arriba del ChatPanel/QnAPanel/PollPanel |

### Verificaciones

| Test | Resultado |
|------|-----------|
| Socket server compila (tsc --noEmit) | 0 errores |
| App compila (tsc --noEmit) | 0 errores nuevos |
| Types: ChatPinnedPayload en types.ts | OK |
| ClientToServerEvents: chat:pin, chat:unpin | OK |
| ServerToClientEvents: chat:pinned, chat:unpinned | OK |
| Redis key: chat:pinned:session:{id} TTL 24h | OK |
| Solo admin puede pin/unpin | OK (role check) |
| join:session envia pinned actual | OK |
| PinnedBanner no reduce player (dentro del flex:1) | OK |
| X dismiss es local (no broadcast) | OK |
| Nuevo pin resetea dismiss | OK |
| **Prueba manual usuario (2026-04-16)** | **OK — agenda RT funciona con socket, pinned visible, Lumina Noir** |

---

## 25. Calendar .ics en Email de Bienvenida (2026-04-16)

### Implementacion

WelcomeMail ahora adjunta archivo `.ics` con las fechas del evento al email de confirmacion de registro.

| Campo ICS | Valor | Fuente |
|-----------|-------|--------|
| SUMMARY | EventOS Summit 2026 | event.name |
| DTSTART | 20260915T130000Z | event.start_date (UTC) |
| DTEND | 20270917T000000Z | event.end_date (UTC) |
| LOCATION | Centro de Convenciones Agora, Bogota | event.venue |
| DESCRIPTION | Descripcion del evento | event.description |
| UID | event-1@eventos.app | event.id (unico, permite updates futuros) |
| METHOD | REQUEST | Auto-agrega al calendario del usuario |
| SEQUENCE | 0 | Version original |
| Filename | eventos-summit-2026.ics | Slug del nombre |

### Prueba Mailpit

| Test | Resultado |
|------|-----------|
| Email llega a Mailpit | OK — Asunto: "Bienvenido a EventOS Summit 2026" |
| Adjunto presente | OK — 1 attachment, 562 bytes |
| Tipo MIME | text/calendar |
| Contenido ICS valido | BEGIN:VCALENDAR, VEVENT, UID, DTSTART, DTEND, SUMMARY, LOCATION |
| METHOD:REQUEST | OK — calendario auto-agrega la cita |
| Caracteres especiales escapados | OK — comas, punto y coma |
| Sin fechas → sin adjunto | OK — attachments() retorna [] |

### Decision arquitectura

- .ics solo en email de bienvenida (1 email, 1 vez por usuario)
- NO .ics por cada favorita ni por cambio de hora (10K x 5 = 50K emails absurdo)
- Cambios de hora → push notification (ya implementado en seccion 23)
- Sesiones individuales → boton "Calendario" en app (expo-calendar nativo)

---

## 26. Push Token Cleanup al Banear (2026-04-17)

**Bug original:** usuario baneado seguia recibiendo push notifications porque `expo_push_token` no se limpiaba.

**Fix:** al crear ban (API + Filament), se envia push de notificacion "Acceso suspendido" y luego se limpia el token.

### Flujo

1. Admin banea usuario (API `POST /admin/attendees/{id}/ban` o Filament)
2. Se crea `AttendeeBan` en BD
3. Se envia push "Acceso suspendido" con el token actual
4. Se limpia `expo_push_token = null` en attendee
5. Se envia email `BannedMail` + socket `ban:enforced`
6. Usuario no recibe mas push despues del ban

### Tests automatizados (3 tests, 9 assertions)

| Test | Que verifica |
|------|-------------|
| `ban limpia expo_push_token` | Token es null despues del ban |
| `ban envia push ANTES de limpiar token` | Push se despacha con token original, luego se limpia |
| `ban sin push token no falla` | Si usuario no tenia token, no crashea ni despacha push |

### Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `BanController.php` | `$attendee->update(['expo_push_token' => null])` despues del push |
| `AttendeeAdminResource.php` | Mismo cleanup en accion ban de Filament |
| `BanTest.php` | 3 tests nuevos (15 totales, 53 assertions) |

---

## 27. Auditoria Seguridad Codigo (2026-04-17)

**Metodo:** Revision automatizada + manual de controllers, rutas, modelos.

### Issues encontrados y corregidos

| # | Severidad | Issue | Fix |
|---|---|---|---|
| SEC-7.1 | Media | LinkedIn/website aceptaban `javascript:` URLs | Validacion `url:http,https` en ProfileController |
| SEC-7.2 | Baja | Photo delete parseaba URL sin verificar formato | Guard `str_contains('/storage/')` antes de delete |
| SEC-7.3 | Baja | ChatController.destroy podia tener eventId null | Fallback a `$message->event_id` + null guard 400 |

### Tests (9 tests, 18 assertions)

| Test | Que verifica |
|------|-------------|
| linkedin rechaza URL sin http/https | `javascript:alert(1)` → 422 |
| linkedin acepta URL con https | URL valida → 200 |
| website rechaza URL sin http/https | `ftp://` → 422 |
| website acepta URL con https | URL valida → 200 |
| twitter acepta handle sin URL | `@usuario` → 200 (no es URL) |
| instagram acepta handle sin URL | `@mi_cuenta` → 200 |
| linkedin y website aceptan null | Limpiar campos → 200 |
| update perfil cambia nombre y empresa | Datos se guardan correctamente |
| profile requiere auth | Sin token → 401 |

### Fortalezas confirmadas (sin issues)

- Zero SQL injection (sin raw queries en toda la app)
- Zero mass assignment (todos usan validated arrays)
- Authorization en todos los controllers (cross-check user + event)
- Rate limiting en rutas publicas (throttle:api)
- File uploads con validacion de tipo y tamano
- IDOR prevenido (attendee.event_id siempre validado)
- TypeScript app: 0 errores de compilacion

---

## 28. Session Lifecycle (2026-04-21) — 23 tests

| Endpoint | Metodo | Auth | Resultado | Notas |
|----------|--------|------|-----------|-------|
| `/admin/sessions/{id}/start` | POST | Admin | OK | actual_start_at = now, sesiones ya iniciadas intocables |
| `/admin/sessions/{id}/end` | POST | Admin | OK | actual_end_at = now, adjusted_end_at cleared, siguiente revertida |
| `/admin/sessions/{id}/cancel` | POST | Admin | OK | status=cancelled, siguiente revertida, delay cleared |
| `/admin/sessions/{id}/delay` | POST | Admin | OK | Cascada: siguiente se mueve, duracion preservada |
| `/admin/sessions/{id}/live-config` | PATCH | Admin | OK | Toggle chat/Q&A/polls/stream, socket broadcast |
| `/sessions/{id}/live-config` | GET | Public | OK | Config publica (sin auth, para display) |

Tests cubren: cascada delay->next, cancel->revert, duracion preservada, sesiones ya iniciadas intocables, .ics con adjusted_end_at, Carbon mutation safety.

---

## 29. Session Stats & Attendance (2026-04-20) — 11 tests

| Endpoint | Metodo | Auth | Resultado | Notas |
|----------|--------|------|-----------|-------|
| `/sessions/{id}/stats` | GET | Admin | OK | Attendance, chat, Q&A, polls, ratings, engagement 0-100 |
| `/sessions/{id}/viewers` | GET | Admin | OK | Attendees activos en sesion (Redis) |
| `/sessions/{id}/export` | GET | Admin | OK | CSV en queue, notificacion campana |

Tests cubren: SessionStatsService, attendance tracking Redis→DB, engagement score, export queue.

---

## 30. Room Check-in & Staff (2026-04-20 a 04-21) — 41 tests

| Endpoint | Metodo | Auth | Resultado | Notas |
|----------|--------|------|-----------|-------|
| `/rooms/{id}/occupancy` | GET | Auth | OK | Personas en cada sala RT |
| `/rooms/{id}/attendees` | GET | Auth | OK | Lista attendees en sala |
| `/attendance-checks/trigger` | POST | Admin | OK | Dispara check silent disco |
| `/attendance-checks/{id}/confirm` | POST | Auth | OK | Attendee confirma presencia |
| `/attendance-checks/pending` | GET | Auth | OK | Checks pendientes del usuario |
| `/attendance-checks/active` | GET | Auth | OK | Check activo actual |
| `/attendance-checks/history` | GET | Auth | OK | Historial de checks |
| `/attendance-checks/{id}/results` | GET | Admin | OK | Resultados de un check |
| `/attendance-checks/report` | GET | Admin | OK | Reporte general |
| `/staff-checkin/assign` | POST | Admin | OK | Asignar staff a sala |
| `/staff-checkin/unassign` | POST | Admin | OK | Desasignar |
| `/staff-checkin/scan` | POST | Staff | OK | Scan QR individual |
| `/staff-checkin/scan-batch` | POST | Staff | OK | Scan batch (kiosk) |
| `/staff-checkin/my-rooms` | GET | Staff | OK | Salas asignadas |
| `/staff-checkin/rooms` | GET | Auth | OK | Todas las salas |
| `/staff-checkin/accept-assignment` | POST | Staff | OK | Aceptar asignacion |
| `/staff-checkin/reject-assignment` | POST | Staff | OK | Rechazar |
| `/staff-checkin/pending-assignment` | GET | Staff | OK | Asignaciones pendientes |
| `/staff-checkin/reassign` | POST | Admin | OK | Reasignar staff |

Tests: RoomCheckinTest (23), StaffCheckinTest (18), RoomStressTest (concurrent scans, edge cases).

---

## 31. Webhooks (2026-04-21) — 24 tests

| Endpoint | Metodo | Auth | Resultado | Notas |
|----------|--------|------|-----------|-------|
| `/webhooks/checkin` | POST | X-Webhook-Key | OK | Check-in externo, actualiza checked_in_at |
| `/webhooks/checkin/batch` | POST | X-Webhook-Key | OK | Batch check-in (multiples attendees) |

**Outbound (Observer-driven):**

| Evento | Trigger | Payload |
|--------|---------|---------|
| `attendee.registered` | Attendee created | name, email, event, fields |
| `attendee.approved` | registration_approved_at set | attendee data |
| `attendee.checked_in` | checked_in_at set | attendee + timestamp |
| `attendee.updated` | name/phone/company/job_title/tags change | changed fields |
| `attendee.cancelled` | Attendee deleted | attendee_id |

Tests: WebhookInboundTest (11), WebhookOutboundTest (8), WebhookModelTest (5+). Retry con backoff, payload signing, filtering por evento.

---

## 32. Live Moments — Games (2026-04-21 a 04-23) — 41 tests

### Spin / Ruleta — 10 tests

| Endpoint | Metodo | Auth | Resultado | Notas |
|----------|--------|------|-----------|-------|
| `/admin/games` | POST | Admin | OK | Crear juego tipo spin |
| `/admin/games/{id}/launch` | POST | Admin | OK | Broadcast game:launched |
| `/admin/games/{id}/spin` | POST | Admin | OK | Ejecuta giro, selecciona ganador |
| `/games/{id}/join` | POST | Auth | OK | Attendee se une al pool |

### Jackpot / Sorteo — 10 tests

| Endpoint | Metodo | Auth | Resultado | Notas |
|----------|--------|------|-----------|-------|
| `/admin/games/{id}/draw` | POST | Admin | OK | Sortea ganador del pool, genera claim_code 6 chars |
| `/rewards/confirm` | POST | Staff | OK | Confirma claim_code, entrega premio |

### Trivia Kahoot-style — 10 tests

| Endpoint | Metodo | Auth | Resultado | Notas |
|----------|--------|------|-----------|-------|
| `/admin/games/{id}/next-question` | POST | Admin | OK | Broadcast game:question a attendees |
| `/admin/games/{id}/close-round` | POST | Admin | OK | Cierra ronda, calcula scores, broadcast game:round-result |
| `/admin/games/{id}/results` | GET | Admin | OK | Leaderboard final |
| `/games/{id}/answer` | POST | Auth | OK | Respuesta con speed bonus |

### Game Export — 5+ tests

| Endpoint | Metodo | Auth | Resultado | Notas |
|----------|--------|------|-----------|-------|
| `/admin/games/{id}/export` | GET | Admin | OK | CSV resultados |
| `/admin/sessions/{id}/games` | GET | Admin | OK | Games por sesion |

Tests cubren: pool eligibility, weighted random, speed bonus, anti-duplicate, leaderboard ordering, claim_code validation, golden ticket lifecycle.

---

## 33. Event Pulse (2026-04-24) — 20 tests

| Endpoint | Metodo | Auth | Resultado | Notas |
|----------|--------|------|-----------|-------|
| `/pulse/{slug}/bootstrap` | GET | pulse_token | OK | Stats agregados: checkins, online, leads, connections, ratings, messages, points |
| `/pulse/{slug}/rooms` | GET | pulse_token | OK | Occupancy por sala |
| `/pulse/{slug}/checkins` | GET | pulse_token | OK | Timeline check-ins |
| `/pulse/{slug}/leads` | GET | pulse_token | OK | Leads por sponsor |
| `/pulse/{slug}/connections` | GET | pulse_token | OK | Conexiones networking |
| `/pulse/{slug}/social` | GET | pulse_token | OK | Actividad social wall |
| `/pulse/{slug}/leaderboard` | GET | pulse_token | OK | Top attendees |
| `/pulse/{slug}/ratings` | GET | pulse_token | OK | Ratings por sesion |

Auth via middleware `check.pulse` con `pulse_token` query param. Socket: auto-join `pulse:{eventId}` room, recibe `pulse:active_users` cada 10s.

---

## 34. Photo Contest + Golden Ticket (2026-04-24) — 25 tests

| Endpoint | Metodo | Auth | Resultado | Notas |
|----------|--------|------|-----------|-------|
| `/events/{id}/photos/contest` | GET | Auth | OK | Fotos en concurso con votos |
| `/events/{id}/photos/{id}/like` | POST | Auth | OK | Like con anti-gaming (no self-like, rate limit concurso) |
| `/events/{id}/photos/{id}/like` | DELETE | Auth | OK | Unlike |

Tests cubren: contest toggle, horario apertura/cierre, 1 entry por attendee, anti-gaming (no self-like, max likes en concurso), Golden Ticket generico desacoplado de sorteo, claim_code validation.

---

## 35. Stand Stats & Contacts (2026-04-20) — 13 tests

| Endpoint | Metodo | Auth | Resultado | Notas |
|----------|--------|------|-----------|-------|
| `/me/stand/stats` | GET | Vendedor | OK | leads, views, favorites, contacts, stamps, trivia, by_tier, by_member, top_services |
| `/me/stand/contacts` | GET | Vendedor | OK | Solicitudes contacto con attendee info completa |

---

## 36. Admin endpoints (2026-04-18 a 04-24)

| Endpoint | Metodo | Auth | Resultado | Notas |
|----------|--------|------|-----------|-------|
| `/admin/events/{id}/attendees` | GET | Admin | OK | Lista paginada |
| `/admin/attendees/{id}/role` | PATCH | Admin | OK | Cambiar rol |
| `/admin/attendees/{id}/ban` | POST | Admin | OK | Ban con motivo + expiracion |
| `/admin/attendees/{id}/ban` | DELETE | Admin | OK | Unban |
| `/admin/attendees/{id}/ban-history` | GET | Admin | OK | Historial bans |
| `/admin/events/{id}/notifications/send` | POST | Admin | OK | Push masivo |
| `/admin/events/{id}/notifications/schedule` | POST | Admin | OK | Push programado |
| `/admin/polls` | POST | Admin | OK | Crear poll |
| `/admin/polls/{id}/start` | POST | Admin | OK | Activar, broadcast poll:new |
| `/admin/polls/{id}/close` | POST | Admin | OK | Cerrar, broadcast poll:closed |
| `/admin/polls/{id}/results` | GET | Admin | OK | Resultados |
| `/admin/polls/votes/{id}/approve` | POST | Admin | OK | Aprobar voto open-text |
| `/admin/polls/votes/approve-batch` | POST | Admin | OK | Batch approve |
| `/admin/uploads` | POST | Admin | OK | Upload archivo |
| `/admin/chat/messages/{id}` | DELETE | Admin | OK | Borrar mensaje chat |

---

## 37. Endpoints adicionales verificados (2026-04-25)

| Endpoint | Metodo | Auth | Resultado | Notas |
|----------|--------|------|-----------|-------|
| `/events/{id}/photos/mine` | GET | Auth | OK | Fotos del usuario |
| `/events/{id}/my-ratings` | GET | Auth | OK | Ratings del usuario |
| `/events/{id}/my-speaker-ratings` | GET | Auth | OK | Ratings a speakers |
| `/events/{id}/speakers/{id}/rate` | POST | Auth | OK | Rating speaker |
| `/events/{id}/gamification-config` | GET | Auth | OK | Config puntos |
| `/events/{id}/gamification/rules` | GET | Auth | OK | Reglas visibles |
| `/events/{id}/visit-stand/{id}` | POST | Auth | OK | Tracking visita, +20pts |
| `/me/contact-requests/sent` | GET | Auth | OK | Solicitudes enviadas |
| `/me/blocked` | GET | Auth | OK | Lista bloqueados |
| `/me/onboarding-data` | GET | Auth | OK | Data onboarding |
| `/me/onboarding-data` | PUT | Auth | OK | Actualizar data |
| `/me/prizes` | GET | Auth | OK | Premios ganados |
| `/me/redemptions` | GET | Auth | OK | Canjes realizados |
| `/events/{id}/rewards/{id}/redeem` | POST | Auth | OK | Canje con lockForUpdate, 5min token |
| `/rewards/confirm` | POST | Staff | OK | Confirmar canje con claim code |
| `/events/{id}/faqs` | GET | Auth | OK | FAQs del evento |
| `/health` | GET | No | OK | DB + Redis + Queue health |
| `/version` | GET | No | OK | Version app |
| `/track` | POST | Auth | OK | Tracking analitico |

---

## 38. Data Center — Exports & API (2026-04-25) — 31 tests

| Endpoint / Feature | Metodo | Auth | Resultado | HTTP | Test |
|---------------------|--------|------|-----------|------|------|
| `/data-center/events` | GET | sanctum | OK | 200 | DataCenterApiTest |
| `/data-center/{event}/stats` | GET | sanctum | OK | 200 | DataCenterApiTest |
| `/data-center/{event}/stats` (moderator) | GET | sanctum | FORBIDDEN | 403 | DataCenterApiTest |
| `/data-center/{event}/stats` (no auth) | GET | - | UNAUTH | 401 | DataCenterApiTest |
| `/data-center/{event}/export` | POST | sanctum | OK | 202 | DataCenterApiTest |
| `/data-center/{event}/export` (invalid type) | POST | sanctum | REJECT | 422 | DataCenterApiTest |
| `/data-center/{event}/export` (throttle 3ro) | POST | sanctum | THROTTLE | 429 | DataCenterApiTest |
| `/data-center/{event}/export-all` | POST | sanctum | OK | 202 | DataCenterApiTest |
| `/data-center/{event}/export-all` (cooldown) | POST | sanctum | THROTTLE | 429 | DataCenterApiTest |
| `/data-center/{event}/exports` | GET | sanctum | OK | 200 | DataCenterApiTest |
| `/data-center/{event}/notifications` | GET | sanctum | OK | 200 | DataCenterApiTest |

**Tests de contract (ExportJobsTest):**

| Test | Que valida |
|------|-----------|
| all export jobs extend BaseExportJob | 44 clases verificadas |
| all export jobs use queue exports | Queue name = 'exports' en las 44 |
| all export jobs have public headers and query | Metodos publicos para MasterZipJob |
| 44 export types are valid | EXPORT_MAP tiene 44 entries, todas las clases existen |

**Tests de datos por export (ExportJobsTest):**

| Export testeado | Que valida |
|-----------------|-----------|
| AttendeesMaster | Headers correctos, 2 rows, campos dinamicos registration_fields |
| Checkins | Solo incluye checked_in (1 de 2) |
| ConsentLog | accepted_at mapeado, IP correcta |
| LeadsMaster | Sponsor name, vendor, scanned, tier hot |
| LeadsMaster (filtro) | sponsor_id filter respetado (1 de 2 sponsors) |
| SessionRatings | strip_tags en comentarios HTML |
| WallPosts | likes_count y comments_count correctos |
| Connections | sender y receiver mapeados, status accepted |
| AuditLog | action ban_attendee registrada |
| Job handle() | CSV creado + Filament notification enviada |
| Job failed() | Error notification enviada al admin |

**Bugs encontrados y corregidos:** 8 (BUG-279 a BUG-286)

---

## 22. Webapp W.1 (eventos-web) — Sesion 2026-05-02

> Repo nuevo `C:\laragon\www\eventos-web` (Next.js 16 + Tailwind 4 + TS strict + i18n + tokens Lumina Noir/Lux portados).
> 8 commits W.1: ba2fc24, 811b7dd, e425570, ffd8589, 6ce5aec, 96fff15, d615bcf, 4e8e588.
> Backend complementario: `feature/magic-link-auth` en eventos-backend con commits 5d5e25d + ef24003 + d44ff42.

### 22.1 W.1B Backend Magic Link (commit eventos-backend `ef24003`)

| Test | Resultado | HTTP | Notas |
|---|---|---|---|
| `POST /auth/magic-link` email registrado | OK | 200 | Crea token + dispatch SendEmailJob, response generico anti-enumeration |
| `POST /auth/magic-link` email NO registrado | OK | 200 | Mismo response generico, sin token creado, sin email queued |
| Anti-enumeration: registrado vs fake responses identicos | OK | 200 | `expect(r1.status).toBe(r2.status)` + same message |
| Rate limit: 4ta peticion mismo email/hora NO crea token | OK | 200 | DB hard limit `where created_at >= now()->subHour()->count() >= 3` |
| `POST /auth/verify-magic-link` token valido | OK | 200 | Devuelve `{token: 'sanctum-XXX', user: UserResource}`, marca `used_at` |
| Token expirado (>15min) | OK | 410 | `code: token_expired` |
| Token usado 2x (segundo intento) | OK | 410 | `code: token_used` |
| Token invalido (random) | OK | 401 | `code: token_invalid` |
| `GET /events/{slug}/login-slides` filtrado correcto | OK | 200 | Solo enabled + dentro window + ordenados por sort_order |
| Cache login-slides + Observer invalidation | OK | — | Cache::has post-fetch=true, Observer borra al save() |

**Tests Pest**: `tests/Feature/Auth/MagicLinkTest.php` (8) + `tests/Feature/PublicEvent/LoginSlidesTest.php` (2) = **10/10 passing**.

**Bug captado SQLite**: ALTER ENUM no soportado, type 'magic_link' rechazado. Fix: tests usan `Queue::fake()` (no Mail::fake()) para evitar SendEmailJob → email_logs insert.

**E2E manual con curl + Mailpit**:
| Caso | Resultado |
|---|---|
| Curl POST /auth/magic-link real | 200 + token DB + email Mailpit |
| Curl POST /auth/verify-magic-link con raw token | 200 + bearer Sanctum |
| Curl POST /auth/verify-magic-link mismo token 2x | 410 token_used |

### 22.2 W.1 F0 — Scaffold + CI

| Check | Resultado | Notas |
|---|---|---|
| pnpm create next-app@latest | OK | Next 16.2.4 + React 19.2 + Tailwind 4 + TS strict |
| typecheck | OK | `noUncheckedIndexedAccess` + `noImplicitOverride` |
| lint | OK | eslint 9 + eslint-config-next |
| Build production | OK | 977ms compile, 4 paginas estaticas |
| Dev server (Turbopack) | OK | 407ms ready |
| HTTP GET / | 200 | placeholder page funciona |
| GitHub Actions CI workflow | OK | typecheck + lint + build + tests |

### 22.3 W.1 F1 — Tokens Lumina Noir + Lux + theme switcher

| Check | Resultado | Notas |
|---|---|---|
| Tokens portados de eventos-app/lib/theme-noir.ts y theme-lux.ts | OK | 8 surfaces, 6 textos, 5 glass, 5 categorias, dark island, gold |
| @theme inline expone como Tailwind utilities | OK | Tailwind 4 sin tailwind.config.ts |
| next-themes wrapper (Noir default, Lux opcional) | OK | data-theme attribute, localStorage persist |
| useMediaQuery con useSyncExternalStore (SSR-safe) | OK | mobile/tablet/desktop breakpoints |
| useReducedMotionPref | OK | CSS @media + JS hook |
| `:focus-visible` con accent dynamic | OK | outline 2px solid var(--accent) |
| GET / 200 con tokens aplicados | OK | 255ms first hit, 29ms cached, sin warnings hydration |

**Bug captado**: Next 16 ESLint rule `react-hooks/set-state-in-effect` rechaza setState sincronico. Fix: useSyncExternalStore en useMediaQuery + useIsClient.

### 22.4 W.1 F2 — shadcn/ui + Sonner + tokens merge

| Check | Resultado | Notas |
|---|---|---|
| shadcn 2.x init con preset radix-nova | OK | 13 componentes pedidos + 2 deps (textarea, input-group) |
| Merge tokens shadcn → Lumina | OK | Aliases `--background`, `--foreground`, `--card`, `--primary`, etc. apuntan a tokens Noir/Lux |
| TooltipProvider en root layout | OK | delayDuration 200 |
| Sonner Toaster con theme mapping noir→dark, lux→light | OK | position bottom-right |
| Build production | OK | 4 paginas, 431ms generate |
| GET / 200 | 200 | 357ms first, 40905 bytes demo |

### 22.5 W.1 F3 — i18n con next-intl

| Check | Resultado | Notas |
|---|---|---|
| next-intl 4.5 routing prefix `/es/`, `/en/`, `/pt/` | OK | localePrefix: always |
| getRequestConfig + hasLocale validation | OK | timeZone America/Bogota |
| Catalogos messages/{es,en,pt}.json | OK | 5 namespaces, 50+ keys, interpolation |
| Type-safe via global.d.ts AppConfig.Messages | OK | Cualquier key invalida = error TS |
| App router reorganizado a `app/[locale]/` | OK | NextIntlClientProvider envuelve providers |
| LanguageSwitcher dropdown locale-aware | OK | Persistencia cookie NEXT_LOCALE |
| Build production | OK | 6 paginas (3 locales SSG + middleware) |
| GET / | 307 | Redirect a /es (default locale) |
| GET /es, /en, /pt | 200 | Strings correctos por idioma verificados |

**Bug captado**: Next 16 deprecó `middleware.ts` → `proxy.ts`. Renombrado, warning eliminado. Cache `.next/` quedó stale tras mover page → `rm -rf .next` resolvió typecheck.

### 22.6 W.1 F4 + F5 — Magic link + Login Slideshow + Tier 1+2 (commit `6ce5aec`)

| Check | Resultado | Notas |
|---|---|---|
| LoginCard layout split 60/40 desktop, 55/45 tablet H, sheet adaptativo mobile | OK | max-height 78%, no height fijo |
| LoginSlideshow Ken Burns 1.0→1.08 + crossfade | OK | Framer Motion AnimatePresence |
| Soporta video_url MP4 en primer slot (Tier 2 #9) | OK | `<video autoplay loop muted playsinline>` con fallback imagen |
| LivePulse mock RT con jitter 3s | OK | Solo en live_today/live_now |
| EventStatusPill contextual upcoming/live_today/ended | OK | Oculto en live_now (solo Live Pulse) |
| EventLogo single o doble (organizer + event) | OK | Cuando organizer_logo_url distinto |
| TabletRotateOverlay portrait detection | OK | matchMedia listener |
| NetworkStatusBanner offline | OK | navigator.onLine listener via useSyncExternalStore |
| State machine 4 steps email→sent→password→verifying | OK | useState + setStep |
| LoginForm con react-hook-form ❌ NO | — | Decidido useState simple, evita 50KB bundle |
| Mailcheck typo detection (gmail.con → gmail.com) | OK | Wrapper LATAM domains (bancolombia, etc.) |
| useLastEmail localStorage SSR-safe | OK | useSyncExternalStore + storage event listener |
| Welcome back conditional | OK | Si lastUserName cached + email coincide |
| ARIA live regions por step | OK | aria-live="polite" |
| Auto-focus inteligente por step | OK | useRef email/password input |
| inputmode="email" + autocomplete="email webauthn" | OK | Apple Passkey + Google Smart Lock |
| Microcopy humano i18n (es/en/pt) | OK | 20+ keys auth.login con interpolation HTML |

**API routes Next.js (proxy a backend Laravel)**:
| Endpoint | Test | Resultado |
|---|---|---|
| `POST /api/auth/magic-link` | curl con email real | 200 + email Mailpit |
| `POST /api/auth/magic-link` | email fake (anti-enum) | 200 mismo response, 0 emails |
| `POST /api/auth/verify` con token raw | curl real | Cookie httpOnly seteada |
| `POST /api/auth/login` (password) | curl con creds | 200 + cookie |
| `POST /api/auth/logout` | curl con cookie | Cookie limpiada |

**E2E real verificado**: GET /es/login → submit email → POST /api/auth/magic-link → backend Laravel → email entregado a Mailpit (subject correcto, branded).

### 22.7 W.1 F6 — Layout protegido + Status gating (commit `96fff15`)

| Check | Resultado | Notas |
|---|---|---|
| proxy.ts auth gate sin cookie | OK | Redirect /login?next={path} |
| getCurrentUser server-side validation | OK | Cookie zombie cleanup en 401 |
| AppHeader sticky con logo + LanguageSwitcher + ThemeToggle + UserMenu | OK | Slot center placeholder PillBar |
| UserMenu DropdownMenu (Perfil/Settings/Logout) | OK | Logout funcional + forgetEmail() |
| 4 home variants segun event.status | OK | PreEventHome/PublishedHome/LiveHome/EndedHome |
| Backend extension PublicEventController.show() | OK | Expone status + 8 campos nuevos (commit eventos-backend d44ff42) |
| Build production | OK | 19 paginas + 4 API routes + middleware (529ms generate) |

**Auth gate E2E**:
| Caso | Resultado |
|---|---|
| GET /es/home sin cookie | 307 → /es/login?next=%2Fes%2Fhome |
| GET / sin cookie | 307 → /es (luego cadena hasta /es/login) |
| GET /es sin cookie | 307 → /es/login |
| GET /es/login publica | 200 |
| GET /es/home con cookie Sanctum real | 200 + render LiveHome correcto |

**Bug captado**: Backend `/auth/me` devuelve `{ data: { user, attendee, role } }` no `{ data: ...user }`. Fix `getCurrentUser` extrae `result.data.user`.

### 22.8 W.1 F8 — Sentry frontend (commit `d615bcf`)

| Check | Resultado | Notas |
|---|---|---|
| @sentry/nextjs instalado | OK | Setup manual (wizard requiere TTY) |
| 3 configs: client + server + edge | OK | Cada uno con beforeSend scrub PII |
| instrumentation.ts (Next 15+ pattern) | OK | onRequestError + register server/edge |
| withSentryConfig wrapper en next.config.ts | OK | Tunnel /monitoring + sourcemaps deleteAfterUpload |
| Scrub PII: email/password/token/cookie/Authorization | OK | beforeSend client + server |
| ignoreErrors browser extensions + ResizeObserver | OK | denyUrls chrome-extension/moz-extension |
| Build production sin DSN | OK | Sentry inerte (zero overhead), bundle sin cambios |
| typecheck + lint | OK | sourcemaps.deleteSourcemapsAfterUpload (no hideSourceMaps) |

### 22.9 W.1 F9 — Vitest + Playwright (commit `4e8e588`)

**22 Vitest unit tests passing**:
| Archivo | Tests | Cubre |
|---|---|---|
| `tests/lib/mailcheck.test.ts` | 6 | Typo detection, dominios LATAM (bancolombia.com.co reconocido) |
| `tests/lib/authValidators.test.ts` | 10 | zod schemas: emailSchema, magicLinkRequestSchema, verifyMagicLinkSchema, passwordLoginSchema |
| `tests/lib/api.test.ts` | 6 | apiFetch headers, bearer, body JSON, ApiError class con status + code |

**12 Playwright E2E tests passing**:
| Archivo | Tests | Cubre |
|---|---|---|
| `e2e/auth-gate.spec.ts` | 4 | / sin auth → /es/login, /es sin auth → /es/login, /es/home → /login?next=, /es/login publica 200 |
| `e2e/login-form.spec.ts` | 4 | Render step email, mailcheck typo gmail.con→gmail.com, mock /api/auth/magic-link → step sent, click password → step password con email pill |
| `e2e/verify-page.spec.ts` | 4 | token length!=64 redirect, sin token redirect, mock 401 token_invalid muestra UI, mock 410 token_expired muestra UI |

**Bugs captados**:
1. Vitest ApiError test: Response stream consumido al primer .json(). Fix: mockImplementation crea Response nueva.
2. Playwright "/" expected /es directo, hay cadena 3 redirects. Fix: assert termina en /es/login.
3. Chrome Playwright detecta en-US Accept-Language → next-intl /en. Fix: locale es-CO + extraHTTPHeaders en playwright.config.

**CI workflow update**: 2 jobs (check con typecheck+lint+vitest+build, e2e con Playwright + upload report on fail).

### 22.10 Visual QA pendiente W.1 (no automatizable — Sprint A futuro)

- [ ] Smoke test login `/es/login` browser real (Chrome/Edge/Safari/Firefox)
- [ ] Comparar lado-a-lado con `design/features/webapp/Login/iteraciones/login-v7-davinci-FINAL.html`
- [ ] Validar 4 steps state machine en uso real (transiciones spring fluidas)
- [ ] Validar 5 estados evento (draft/registration/published/live/ended) en home variants
- [ ] Mobile real: Pixel + iPhone 14 (bottom sheet adaptativo, no rompe en 360px)
- [ ] Tablet portrait: overlay rotacion aparece correcto
- [ ] Tablet landscape: layout escalado correcto
- [ ] Lighthouse Performance >= 85 desktop / >= 75 mobile
- [ ] Lighthouse Accessibility >= 95
- [ ] Seedear 2-3 event_login_slides en backend para ver slideshow con imagenes reales

---

## Estado final QA (2026-05-02)

| Categoria | Tests | OK | Bugs | Notas |
|-----------|-------|-----|------|-------|
| Auth/Ban/Approval/Toast | 50+ | 50 | 1 hist. | check.ban, lockout, toast messages, restriction |
| Agenda/Favorites/Calendar | 15+ | 15 | 0 | Toggle, .ics, reminders |
| Session Lifecycle | 23 | 23 | 0 | Delay, cancel, revert, cascada |
| Session Config | 23 | 23 | 0 | Start/end/cancel, live-config, socket broadcast |
| Session Stats | 11 | 11 | 0 | Attendance, engagement, export |
| Room Check-in | 23 | 23 | 0 | Occupancy, attendance checks, silent disco |
| Staff Check-in | 18 | 18 | 0 | Assign, scan, batch, reassign |
| Room Stress | 5+ | 5 | 0 | Concurrent scans |
| Chat/Pinned | 15+ | 15 | 0 | Socket flow, pin/unpin, rate limit |
| Q&A | 10+ | 10 | 0 | Submit, upvote, moderate, blocked words (422 fix) |
| Polls/Surveys | 20+ | 20 | 0 | Vote, post-event, auto-activate |
| Social Wall | 10+ | 10 | 0 | Posts, likes, comments |
| Photos/Contest | 35+ | 35 | 0 | Upload, like, contest lifecycle, anti-gaming |
| Networking | 15+ | 15 | 0 | Request, respond, block, matchmaking, swipe |
| Gamification/Rewards | 15+ | 15 | 0 | Points, redeem, confirm, lockForUpdate |
| Sponsors/Stand | 30+ | 30 | 0 | Favorite, stats, contacts, team, invite |
| Live Moments (Games) | 41 | 41 | 0 | Spin, jackpot, trivia, export, golden ticket |
| Event Pulse | 20 | 20 | 0 | Bootstrap, 7 secciones, auth pulse_token |
| Webhooks | 24 | 24 | 0 | Inbound, outbound, model, retry |
| Rate Limits | 31 | 31 | 0 | 7 endpoints, spam simulation |
| Push/Reminders | 19 | 19 | 0 | Dedup, windows, multi-evento |
| Security (SEC-1 a SEC-7) | 50+ | 50 | 3 hist. | Socket auth, headers, ban, URL injection |
| Presets/Fields/Theme | 30+ | 30 | 0 | 11 tipos, onboarding, branding |
| Admin endpoints | 30+ | 30 | 0 | Bans, polls, notifications, uploads, games |
| Data Center | 31 | 31 | 8 corr. | 44 exports, 7 endpoints, SPA standalone |
| **W.1B Backend Magic Link** | 10 | 10 | 1 SQLite | Pest tests + curl + Mailpit. SQLite ENUM bug → fix con Queue::fake |
| **W.1 Webapp F0-F9 (eventos-web)** | 34 | 34 | 3 corr. | 22 Vitest unit + 12 Playwright E2E. Bugs: Response stream, redirect chain, Chrome locale |
| **TOTAL** | **787** | **787** | **0 activos** | 71 archivos backend test + 6 webapp test = 77 archivos, ~1990+ assertions |
