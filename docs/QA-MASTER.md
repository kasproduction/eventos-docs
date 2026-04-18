# QA Master — Barrido Completo de Plataforma

> Auditoria endpoint por endpoint de todos los modulos.
> Actualizado: 2026-04-17 | Metodo: curl real + tests automatizados (468 tests, 1177 assertions)
> IMPORTANTE: Socket server debe estar corriendo para real-time (agenda, chat, pinned, branding). Iniciar con: cd eventos-socket && npx ts-node src/index.ts

---

## Resumen ejecutivo

| Modulos probados | Endpoints testados | OK | Bugs | Notas |
|-----------------|-------------------|-----|------|-------|
| 26 | 100+ | 100 | 1 corregido | + rate limits + reminders + calendar bulk + session changed |

**Bug corregido en esta sesion:**
- `/me`, `/refresh`, `/verify-email`, `/expo-token` no tenian `check.ban` middleware — usuario baneado podia llamar `/me` y recibir 200. Fix: `eeb6ebc`

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
| Rate Limits SEC-6.2 | 31 | 31 | 0 | 10 unit + 13 feature + 8 spam |
| Push Reminders | 19 | 19 | 0 | 5 unit + 14 feature (dedup, spam, multi-evento) |
| Mensaje anclado chat | 11 | 11 | 0 | Socket flow verificado, TypeScript 0 errores |
| Calendar .ics email | 7 | 7 | 0 | Mailpit verificado, adjunto valido |
| Push token ban cleanup | 3 | 3 | 0 | Token limpiado, orden push→cleanup, sin token no falla |
| **TOTAL** | **121+** | **121** | **1 corregido** | Plataforma solida — 468 tests automatizados |
