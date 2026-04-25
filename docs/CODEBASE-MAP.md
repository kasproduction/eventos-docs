# Codebase Map — EventOS

> Mapeo completo de los 3 repositorios para audit de Optimistic UI.
> Generado: 2026-04-25 | Solo lectura, no analisis.

---

## 1. Estructura de repositorios

### 1.1 Mobile — `C:\Users\Kasproduction\Projects\eventos-app\`

```
eventos-app/
├── app/                    # Expo Router (file-based routing)
│   ├── (app)/(tabs)/       # 5 tabs principales (home, mi-agenda, mi-qr, networking, profile)
│   ├── (app)/              # 40+ rutas autenticadas (agenda, session, sponsors, social, etc.)
│   ├── (auth)/             # Login, activacion, pending approval
│   ├── _layout.tsx         # Root layout (providers, theme)
│   ├── index.tsx           # Entry/splash
│   ├── onboarding.tsx      # Onboarding multi-step
│   └── banned.tsx          # Pantalla de ban
├── components/             # 76 componentes reutilizables
│   ├── screens/            # Componentes de pantalla completa
│   ├── ui/                 # Primitivos (buttons, modals, cards)
│   ├── social/             # Posts, comments, photos
│   └── onboarding/         # Steps del onboarding
├── hooks/                  # 36 custom hooks (mutations, queries, socket)
├── stores/                 # Zustand stores (auth, cache, theme)
├── lib/                    # API client, utilidades (23 archivos)
├── locales/                # i18n (espanol)
└── assets/                 # Iconos, splash
```

### 1.2 Laravel API — `C:\laragon\www\eventos-backend\`

```
eventos-backend/
├── app/
│   ├── Http/Controllers/Api/V1/   # 46 controllers REST
│   ├── Http/Middleware/            # AuthenticateApi, CheckBan, CheckApproval, CheckPulse
│   ├── Models/                     # Eloquent models
│   ├── Observers/                  # 11 observers (cache, socket, webhooks)
│   ├── Services/                   # InvalidationService, PointsService, RewardService, etc.
│   ├── Jobs/                       # 21 queue jobs
│   └── Traits/                     # AwardsPoints, ChecksRateLimit
├── routes/
│   ├── api.php                     # Incluye sub-archivos
│   └── api/                        # auth, events, leads, networking, sponsors, etc.
└── config/                         # services.php (socket URL + secret)
```

### 1.3 Socket Server — `C:\laragon\www\eventos-socket\`

```
eventos-socket/
└── src/
    ├── index.ts     # 487 lineas — HTTP server, internal endpoints, auth middleware, connection handler
    ├── chat.ts      # 563 lineas — chat handlers, rate limiting, persistence, audience broadcast
    ├── auth.ts      # 90 lineas  — token validation (Sanctum + Pulse), cache
    ├── rooms.ts     # 11 lineas  — room naming (event, session, chat, display, pulse)
    ├── types.ts     # 251 lineas — TypeScript interfaces
    ├── rateLimit.ts # 36 lineas  — Redis-backed rate limiting
    └── config.ts    # 16 lineas  — env config
```

---

## 2. Stack confirmado por archivo

### 2.1 Mobile (package.json)

| Dependencia | Version | Archivo fuente |
|-------------|---------|----------------|
| expo | ^55.0.9 | package.json |
| react-native | 0.83.4 | package.json |
| react | 19.2.0 | package.json |
| @tanstack/react-query | ^5.95.2 | package.json |
| zustand | ^5.0.12 | package.json |
| socket.io-client | ^4.8.3 | package.json |
| expo-router | ~55.0.8 | package.json |
| nativewind | ^4.2.3 | package.json |

**SDK:** Expo SDK 55 (app.json)
**State:** Zustand para client state (auth, theme, cache), React Query para server state
**No Redux, no Jotai**

### 2.2 Laravel (composer.json)

| Dependencia | Version |
|-------------|---------|
| laravel/framework | 11.31 |
| laravel/sanctum | ^4.3 |
| laravel/horizon | ^5.45 |
| laravel/telescope | ^5.19 |
| laravel/pail | ^1.1 |
| filament/filament | ^3.2 |
| spatie/laravel-permission | ^6.25 |
| maatwebsite/excel | ^3.1 |
| sentry/sentry-laravel | ^4.24 |
| league/flysystem-aws-s3-v3 | ^3.32 |

**NO tiene:** Reverb, Pulse, Echo, Broadcasting nativo. La comunicacion con sockets es via HTTP custom a Node.js.

### 2.3 Socket Server (package.json)

| Dependencia | Version |
|-------------|---------|
| socket.io | 4.8.3 |
| @socket.io/redis-adapter | 8.3.0 |
| ioredis | 5.10.1 |
| axios | (HTTP client para Laravel) |
| typescript | 6.0.2 |

**Redis DB:** 2 (config.ts:10)
**Puerto:** 3001

---

## 3. Endpoints REST agrupados por resource

> Prefijo: `/v1/`. Auth via Sanctum salvo donde se indica [public].

### Auth (routes/api/auth.php)

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| POST | /auth/check-email | AuthController@checkEmail [public, throttled] |
| POST | /auth/register | AuthController@register [public] |
| POST | /auth/activate | AuthController@activate [public] |
| POST | /auth/verify-identity | AuthController@verifyIdentity [public, throttled] |
| POST | /auth/login | AuthController@login [public, throttle:login 5/min] |
| POST | /auth/forgot-password | AuthController@forgotPassword [public] |
| POST | /auth/reset-password | AuthController@resetPassword [public] |
| GET | /auth/verify-email/{token} | AuthController@verifyEmail [public] |
| GET | /auth/me | AuthController@me |
| POST | /auth/refresh | AuthController@refresh |
| POST | /auth/verify-email | AuthController@resendVerification |
| POST | /auth/expo-token | ExpoTokenController@update |
| POST | /auth/logout | AuthController@logout |

### Onboarding & Presets (routes/api/events.php)

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /events/{id}/onboarding | OnboardingController@index [public] |
| GET | /events/{id}/registration-fields | RegistrationFieldController@index [public] |
| GET | /events/{id}/onboarding-fields | RegistrationFieldController@onboardingFields [public] |
| POST | /events/{id}/onboarding/survey | OnboardingController@storeSurvey |
| GET | /presets/{type} | PresetController@preset [public] |
| GET | /presets/cities/{countryCode} | PresetController@cities [public] |

### Check-in & QR (routes/api/checkin.php)

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /me/qr | CheckinController@qr |
| POST | /checkin | CheckinController@checkin |

### Events — Branding & Content (routes/api/events.php)

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /events/{id}/branding | EventBrandingController@show |
| GET | /events/{id}/modules | ModuleController@index |
| GET | /events/{id}/banners | BannerController@index |
| GET | /events/{id}/highlights | HighlightController@index |
| GET | /events/{id}/announcements | AnnouncementController@index |
| GET | /events/{id}/tracks | SessionTrackController@index |

### Agenda & Sessions

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /events/{id}/agenda | AgendaController@index |
| POST | /events/{id}/agenda/{sessionId}/favorite | AgendaController@toggleFavorite |
| GET | /events/{id}/sessions/{id}/calendar.ics | AgendaController@calendar |
| GET | /events/{id}/speakers | SpeakerController@index |
| GET | /events/{id}/speakers/{id} | SpeakerController@show |
| GET | /events/{id}/documents | DocumentController@index |
| GET | /events/{id}/pages | CustomPageController@index |
| GET | /events/{id}/pages/{id} | CustomPageController@show |
| GET | /events/{id}/attendance | CheckinController@attendance |

### Q&A

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /events/{id}/sessions/{id}/questions | QuestionController@index |
| POST | /events/{id}/sessions/{id}/questions | QuestionController@store |
| POST | /events/{id}/sessions/{id}/questions/{id}/upvote | QuestionController@upvote |
| GET | /events/{id}/sessions/{id}/questions/pending | QuestionController@pending |
| PATCH | /events/{id}/sessions/{id}/questions/{id}/moderate | QuestionController@moderate |

### Photobooth

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /events/{id}/photos | EventPhotoController@index |
| GET | /events/{id}/photos/mine | EventPhotoController@mine |
| GET | /events/{id}/photos/contest | EventPhotoController@contest |
| POST | /events/{id}/photos | EventPhotoController@store |
| POST | /events/{id}/photos/{id}/like | EventPhotoController@like |
| DELETE | /events/{id}/photos/{id}/like | EventPhotoController@unlike |

### Gamification & Passport

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /events/{id}/my-passport | PassportController@show |
| GET | /events/{id}/leaderboard | GamificationController@leaderboard |
| GET | /events/{id}/gamification-config | GamificationController@config |
| GET | /events/{id}/gamification/rules | GamificationController@rules |
| POST | /events/{id}/visit-stand/{sponsorId} | GamificationController@visitStand |
| POST | /events/{id}/trivia/{triviaId}/answer | GamificationController@answerTrivia |
| GET | /me/points | GamificationController@myPoints |

### Social Wall

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /events/{id}/wall | WallController@index |
| POST | /events/{id}/wall | WallController@store |
| POST | /events/{id}/wall/{id}/like | WallController@like |
| DELETE | /events/{id}/wall/{id}/like | WallController@unlike |
| GET | /events/{id}/wall/{id}/comments | WallController@comments |
| POST | /events/{id}/wall/{id}/comments | WallController@storeComment |

### Stories

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /events/{id}/stories | AttendeeStoryController@index |
| POST | /events/{id}/stories | AttendeeStoryController@store |
| DELETE | /events/{id}/stories/{id} | AttendeeStoryController@destroy |

### Ratings

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /events/{id}/my-ratings | RatingController@myRatings |
| POST | /events/{id}/sessions/{id}/rate | RatingController@store |
| GET | /events/{id}/sessions/{id}/ratings | RatingController@adminIndex |
| GET | /events/{id}/my-speaker-ratings | SpeakerRatingController@myRatings |
| POST | /events/{id}/speakers/{id}/rate | SpeakerRatingController@store |

### Polls & Surveys

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| POST | /polls/{id}/vote | PollController@vote |
| GET | /sessions/{id}/poll/active | PollController@activePoll |
| GET | /events/{id}/surveys | PollController@surveys |
| GET | /events/{id}/post-event-survey | PollController@postEventSurvey |

### Games

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /sessions/{id}/game/active | GameController@active [public] |
| POST | /games/{id}/join | GameController@join |
| POST | /games/{id}/answer | GameController@answer |

### Networking (routes/api/networking.php)

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /events/{id}/attendees | NetworkingController@directory |
| GET | /events/{id}/suggested-contacts | NetworkingController@suggestedContacts |
| GET | /attendees/{id}/profile | NetworkingController@profile |
| POST | /contacts/request | NetworkingController@sendRequest |
| PUT | /contacts/request/{id} | NetworkingController@respondRequest |
| GET | /me/contacts | NetworkingController@myContacts |
| GET | /me/contact-requests | NetworkingController@receivedRequests |
| GET | /me/contact-requests/sent | NetworkingController@sentRequests |
| GET | /me/blocked | NetworkingController@blockedList |
| POST | /contacts/block/{id} | NetworkingController@block |
| DELETE | /contacts/block/{id} | NetworkingController@unblock |

### Sponsors (routes/api/sponsors.php)

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /events/{id}/sponsors | SponsorController@index |
| POST | /events/{id}/sponsors/{id}/favorite | SponsorController@favorite |
| DELETE | /events/{id}/sponsors/{id}/favorite | SponsorController@unfavorite |
| POST | /events/{id}/sponsors/{id}/contact | SponsorController@contact |
| POST | /events/{id}/sponsors/{id}/view | SponsorController@recordView |

### Leads (routes/api/leads.php)

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /leads | LeadController@index |
| POST | /leads | LeadController@store |
| PUT | /leads/{id} | LeadController@update |
| GET | /leads/{id}/edits | LeadController@edits |
| GET | /me/leads/export | LeadController@export |

### Stand Team (routes/api/stand.php)

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /me/stand | StandController@show |
| GET | /me/stand/stats | StandController@stats |
| GET | /me/stand/contacts | StandController@contacts |
| POST | /me/stand/members | StandController@addMember |
| DELETE | /me/stand/members/{id} | StandController@removeMember |
| POST | /me/stand/transfer | StandController@transferOwnership |
| GET | /me/stand/search-attendees | StandController@searchAttendees |
| POST | /me/stand/resolve-qr | StandController@resolveQr |
| POST | /me/stand/share-link | StandController@generateShareLink |
| GET | /me/pending-invitations | StandController@pendingInvitations |
| GET | /staff-invitations/{token}/info | StandController@invitationInfo [public] |
| POST | /staff-invitations/{token}/accept | StandController@acceptInvitation [public] |
| POST | /staff-invitations/{token}/reject | StandController@rejectInvitation [public] |

### Rewards (routes/api/rewards.php)

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /events/{id}/rewards | RewardController@index |
| POST | /events/{id}/rewards/{id}/redeem | RewardController@redeem |
| POST | /rewards/confirm | RewardController@confirm |
| GET | /me/prizes | RewardController@myPrizes |
| GET | /me/redemptions | RewardController@myRedemptions |

### Profile

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /me/profile | ProfileController@show |
| PUT | /me/profile | ProfileController@update |
| POST | /me/photo | ProfileController@uploadPhoto |
| DELETE | /me/photo | ProfileController@deletePhoto |
| GET | /me/onboarding-data | ProfileController@onboardingData |
| PUT | /me/onboarding-data | ProfileController@updateOnboardingData |
| GET | /me/registration-fields | RegistrationFieldController@myFields |
| PUT | /me/registration-fields | RegistrationFieldController@updateMyFields |

### Chat (routes/api/chat.php)

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /sessions/{id}/chat/messages | ChatController@index |
| DELETE | /admin/chat/messages/{id} | ChatController@destroy [admin] |

### Rooms & Staff Check-in

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /rooms/{id}/occupancy | RoomCheckinController@occupancy |
| GET | /rooms/{id}/attendees | RoomCheckinController@attendeesInRoom |
| POST | /attendance-checks/trigger | RoomCheckinController@triggerCheck |
| POST | /attendance-checks/{id}/confirm | RoomCheckinController@confirmCheck |
| GET | /attendance-checks/pending | RoomCheckinController@pendingCheck |
| GET | /attendance-checks/active | RoomCheckinController@activeCheck |
| GET | /attendance-checks/history | RoomCheckinController@checkHistory |
| GET | /attendance-checks/{id}/results | RoomCheckinController@checkResults |
| GET | /attendance-checks/report | RoomCheckinController@report |
| POST | /staff-checkin/assign | StaffCheckinController@assign |
| POST | /staff-checkin/unassign | StaffCheckinController@unassign |
| POST | /staff-checkin/scan | StaffCheckinController@scan |
| POST | /staff-checkin/scan-batch | StaffCheckinController@scanBatch |
| GET | /staff-checkin/my-rooms | StaffCheckinController@myRooms |
| GET | /staff-checkin/rooms | StaffCheckinController@listRooms |
| POST | /staff-checkin/accept-assignment | StaffCheckinController@acceptAssignment |
| POST | /staff-checkin/reject-assignment | StaffCheckinController@rejectAssignment |
| GET | /staff-checkin/pending-assignment | StaffCheckinController@pendingAssignment |
| POST | /staff-checkin/reassign | StaffCheckinController@reassign |

### Admin (routes/api/admin.php)

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /admin/events/{id}/attendees | AttendeeController@index |
| PATCH | /admin/attendees/{id}/role | AttendeeController@updateRole |
| POST | /admin/attendees/{id}/ban | BanController@ban |
| DELETE | /admin/attendees/{id}/ban | BanController@unban |
| GET | /admin/attendees/{id}/ban-history | BanController@history |
| GET | /admin/events/{id}/notifications | NotificationController@index |
| POST | /admin/events/{id}/notifications/send | NotificationController@send |
| POST | /admin/events/{id}/notifications/schedule | NotificationController@schedule |
| DELETE | /admin/events/{id}/notifications/{id} | NotificationController@destroy |
| POST | /admin/uploads | UploadController@store |
| DELETE | /admin/uploads | UploadController@destroy |
| GET | /admin/sessions/{id}/live-config | SessionConfigController@show |
| PATCH | /admin/sessions/{id}/live-config | SessionConfigController@update |
| POST | /admin/sessions/{id}/start | SessionConfigController@start |
| POST | /admin/sessions/{id}/end | SessionConfigController@end |
| POST | /admin/sessions/{id}/cancel | SessionConfigController@cancel |
| POST | /admin/sessions/{id}/delay | SessionConfigController@delayRoom |
| POST | /admin/games | GameController@store |
| PATCH | /admin/games/{id} | GameController@update |
| DELETE | /admin/games/{id} | GameController@destroy |
| POST | /admin/games/{id}/launch | GameController@launch |
| POST | /admin/games/{id}/spin | GameController@spin |
| POST | /admin/games/{id}/draw | GameController@draw |
| POST | /admin/games/{id}/next-question | GameController@nextQuestion |
| POST | /admin/games/{id}/close-round | GameController@closeRound |
| GET | /admin/games/{id}/results | GameController@results |
| GET | /admin/games/{id}/export | GameController@export |
| GET | /admin/sessions/{id}/games | GameController@bySession |
| GET | /admin/polls | PollController@index |
| POST | /admin/polls | PollController@store |
| POST | /admin/polls/{id}/start | PollController@start |
| POST | /admin/polls/{id}/close | PollController@close |
| GET | /admin/polls/{id}/results | PollController@results |
| POST | /admin/polls/votes/{id}/approve | PollController@approveVote |
| POST | /admin/polls/votes/approve-batch | PollController@approveVoteBatch |
| DELETE | /admin/polls/votes/{id} | PollController@rejectVote |

### Event Pulse (routes/api/events.php)

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /pulse/{slug}/bootstrap | PulseController@bootstrap [check.pulse] |
| GET | /pulse/{slug}/rooms | PulseController@rooms |
| GET | /pulse/{slug}/checkins | PulseController@checkins |
| GET | /pulse/{slug}/leads | PulseController@leads |
| GET | /pulse/{slug}/connections | PulseController@connections |
| GET | /pulse/{slug}/social | PulseController@social |
| GET | /pulse/{slug}/leaderboard | PulseController@leaderboard |
| GET | /pulse/{slug}/ratings | PulseController@ratings |

### Health & System

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| GET | /health | HealthController@health |
| GET | /version | HealthController@version |
| POST | /track | TrackController@store |
| POST | /support | SupportController@store |
| GET | /support/mine | SupportController@mine |

### Webhooks Inbound

| Method | Ruta | Controller@method |
|--------|------|-------------------|
| POST | /webhooks/checkin | WebhookInboundController@checkin [X-Webhook-Key] |
| POST | /webhooks/checkin/batch | WebhookInboundController@checkinBatch [X-Webhook-Key] |

---

## 4. Socket events del server Node.js

### 4.1 Client -> Server (socket.on)

Todos registrados en `src/chat.ts` via `registerChatHandlers()`.

| Evento | Archivo:Linea | Ack? | Broadcast |
|--------|---------------|------|-----------|
| `join:event` | index.ts:583-593 | No | No broadcast. Valida pertenencia al evento |
| `join:session` | chat.ts:232-306 | No | `broadcastAudience()` a session + pulse rooms |
| `leave:session` | chat.ts:308-322 | No | `broadcastAudience()` a session + pulse rooms |
| `chat:send` | chat.ts:325-395 | No | `io.to(Rooms.chat(sessionId)).emit('chat:message')` (linea 381). **No skip-self** |
| `chat:delete` | chat.ts:398-430 | No | `io.to(chatRoom).emit('chat:deleted')` (linea 409). Admin only |
| `chat:emoji` | chat.ts:433-445 | No | `io.to(Rooms.chat(sessionId)).emit('chat:emoji')` (linea 444). Rate: 3/2s |
| `chat:pin` | chat.ts:448-472 | No | `io.to(Rooms.chat(sessionId)).emit('chat:pinned')` (linea 470). Admin only |
| `chat:unpin` | chat.ts:475-489 | No | `io.to(Rooms.chat(sessionId)).emit('chat:unpinned')` (linea 487). Admin only |
| `display:project` | chat.ts:492-505 | No | `io.to(Rooms.display(sessionId)).emit('display:project')` (linea 501) |
| `display:stop` | chat.ts:508-517 | No | `io.to(Rooms.display(sessionId)).emit('display:stop')` (linea 513) |

**Hallazgo critico:** Ningun handler tiene ack callback. `chat:send` usa `io.to()` (no `socket.to()`), por lo que el sender recibe su propio mensaje de vuelta.

### 4.2 Server -> Client (broadcasts via HTTP /internal/)

Todos en `src/index.ts`, protegidos con `X-Internal-Secret`.

| Endpoint interno | Evento emitido | Room | Linea |
|------------------|----------------|------|-------|
| `/internal/checkin` | `checkin:update` | event:{eventId} | 55 |
| `/internal/poll/broadcast` | `poll:new` / `poll:updated` / `poll:closed` | session:{id} o event:{id} | 96-100 |
| `/internal/question/broadcast` | `question:submitted/approved/answered/upvoted` | session:{id} | 132 |
| `/internal/session/mode_changed` | `session:mode_changed` | session:{id} | 159 |
| `/internal/wall/broadcast` | `wall:post` / `wall:comment` | event:{id} | 193-196 |
| `/internal/data/invalidate` | `data:invalidate` | event:{id} | 221 |
| `/internal/emit-to-user` | Dinamico | Socket ID directo | 249 |
| `/internal/ban/enforce` | `ban:enforced` | Socket ID directo | 304 |
| `/internal/networking/notify` | `networking:notify` | Socket ID directo | 338 |
| `/internal/staff/notify` | Dinamico | Socket ID directo | 366 |
| `/internal/broadcast` | Dinamico | Room especificado | 391 |
| `/internal/session-config` | `session:config_updated` | event:{id} | 467 |

**Periodico:** `pulse:active_users` cada 10s a `pulse:{eventId}` (index.ts:650-669)

### 4.3 Rooms (src/rooms.ts:4-10)

```typescript
event:       (eventId)   => `event:${eventId}`
pulse:       (eventId)   => `pulse:${eventId}`
session:     (sessionId) => `session:${sessionId}`
chat:        (sessionId) => `chat:session:${sessionId}`
display:     (sessionId) => `display:session:${sessionId}`
```

### 4.4 tempId en chat

El server genera tempId en chat.ts:363: `${sessionId}-${attendeeId}-${Date.now()}`

El payload de ChatMessagePayload (types.ts:117-126) incluye:
- `id`: string (el tempId generado por server)
- `dbId?`: number (null hasta persistir en MySQL)

**Nota:** El tempId lo genera el SERVER, no el cliente. El cliente no envia tempId.

---

## 5. Socket.on() listeners en mobile

### 5.1 useDataInvalidation.ts (hub central)

| Evento | Linea | Accion |
|--------|-------|--------|
| `data:invalidate` | 236-259 | Debounce 800ms, limpia MMKV, invalida React Query keys |
| `checkin:update` | 262-268 | Refresca modules si attendee hizo check-in |
| `ban:enforced` | 307-314 | Redirige a /banned |
| `staff:invited` | 316-320 | Actualiza store de invitaciones |
| `staff:accepted` | 322-326 | Toast + invalida stand queries |
| `staff:rejected` | 326-327 | Toast + invalida stand queries |
| `staff:assignment_request` | 335-340 | Modal de asignacion |
| `staff:assignment_accepted` | 340-345 | Invalida room queries |
| `staff:room_unassigned` | 345-350 | Revierte rol, redirige home |
| `staff:room_changed` | 350-355 | Invalida room queries |
| `agenda:delayed` | 368-374 | Toast de notificacion |
| `attendance:check` | 376-389 | Modal de verificacion (silent disco) |
| `staff:removed` | 391-408 | Revierte acceso sponsor, redirige |
| `networking:notify` | 411-421 | Batch 1500ms, toast con conteo |
| `game:launched` | 424-430 | Toast con titulo del juego |
| `game:result` | 430-442 | Award puntos/premios, invalida queries |
| `game:question` | 445-450 | Actualiza TriviaStore |
| `game:round-result` | 450-455 | Actualiza TriviaStore |
| `game:finished` | 455-459 | Marca juego terminado, invalida my-points |

### 5.2 useChat.ts

| Evento | Linea | Dedup? |
|--------|-------|--------|
| `chat:history` | 145-158 | Filtra por lastApiSentAt |
| `chat:message` | 160-164 | Set socketMsgIds (linea 70) |
| `chat:deleted` | 165-170 | N/A |
| `chat:pinned` | 171-175 | N/A |
| `chat:emoji` | 176-180 | N/A |
| `poll:new` | 181-185 | N/A |
| `poll:updated` | 186-190 | N/A |
| `poll:closed` | 191-195 | N/A |

### 5.3 useSessionMode.ts

| Evento | Linea |
|--------|-------|
| `session:mode_changed` | Actualiza modo interactivo |
| `session:config_updated` | Actualiza config de sesion |

### 5.4 useWall.ts

| Evento | Linea | Dedup? |
|--------|-------|--------|
| `wall:post` | 68-74 | Merge en feed |
| `wall:comment` | 75-80 | Merge en comentarios |

### 5.5 useQnA.ts

| Evento | Linea | Dedup? |
|--------|-------|--------|
| `question:approved` | 62-73 | Si: `if (prev.find(x => x.id === q.id))` linea 66 |
| `question:answered` | 75-85 | Reemplaza por ID |
| `question:upvoted` | 87-97 | Reemplaza por ID, no dedup explicito |

---

## 6. Observers de Laravel

| Observer | Hooks | Efectos |
|----------|-------|---------|
| EventObserver | saved() | Cache branding+modules, broadcast `branding` invalidation |
| EventSessionObserver | saved(), deleted(), updated() | Cache agenda, broadcast `agenda`, dispatch SendSessionChangedNotificationJob si cambia hora, HTTP a socket si cambia interactive_mode/stream_url |
| AnnouncementObserver | saved(), deleted() | Cache announcements (por rol), broadcast `announcements` si published |
| SponsorObserver | saved(), deleted() | Broadcast `sponsors` invalidation |
| ModuleObserver | updated(), created(), deleted() | Cache modules, broadcast `modules`, dispatch SendSilentPushJob |
| EventRoomObserver | saved(), deleted() | Cache agenda, broadcast `agenda` |
| HighlightObserver | saved(), deleted() | Cache + broadcast `highlights` |
| SpeakerObserver | saved(), deleted() | Cache + broadcast `speakers` |
| SessionTrackObserver | saved(), deleted() | Cache + broadcast |
| ContentObserver | Generic | Cache invalidation para varios modelos |
| AttendeeWebhookObserver | created(), updated(), deleted() | Webhooks: attendee.registered/approved/checked_in/updated/cancelled |

---

## 7. Queue Jobs

| Job | Proposito |
|-----|-----------|
| SendPushToAttendeeJob | Push a un attendee |
| SendSilentPushJob | Push silencioso (module_update) |
| SendEmailJob | Email generico |
| SendEventRemindersJob | Recordatorio pre-evento |
| SendSessionRemindersJob | Recordatorio pre-sesion |
| SendAgendaRemindersJob | Recordatorios agenda |
| SendSessionChangedNotificationJob | Notif cambio horario sesion |
| CheckExpoReceiptsJob | Verificar delivery push Expo |
| DispatchScheduledNotificationsJob | Notificaciones programadas |
| ExpireAttendeeBansJob | Auto-unban expirado |
| ExpireRedemptionsJob | Expirar rewards canjeados |
| AwardSessionAttendancePointsJob | Puntos por asistir sesion |
| FlushSessionAttendanceJob | Limpiar estado attendance |
| AutoCheckoutEndOfDayJob | Auto checkout fin de dia |
| SmartAutoCheckoutJob | Checkout inteligente |
| ExportSessionStatsJob | Export stats sesion |
| ExportRoomAttendanceJob | Export attendance sala |
| ExportPollResponsesJob | Export respuestas poll |
| ProcessTriviaRewardsJob | Awards trivia |
| ProcessSpinRewardsJob | Awards spin wheel |
| DispatchWebhookJob | Webhook a partner |

---

## 8. Rate Limiting

### Laravel (AppServiceProvider.php:66-86)

| Key | Limite |
|-----|--------|
| login | 5/min por IP |
| api | 60/min por user ID o IP |
| upload | 10/min por user ID o IP |

### Socket Server (rateLimit.ts + chat.ts)

| Key | Limite |
|-----|--------|
| rl:chat:{attendeeId}:{sessionId} | 1 msg / slowSec (default 2s) |
| rl:emoji:{attendeeId}:{sessionId} | 3 / 2s |
| rl:join:session:{attendeeId} | 10 / 60s |

---

_CODEBASE-MAP.md — EventOS | 2026-04-25_
_Fase 1 completada. Solo mapeo, no analisis._
