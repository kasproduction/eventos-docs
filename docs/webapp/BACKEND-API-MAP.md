# Backend API Map — EventOS

> Referencia EXHAUSTIVA de TODOS los endpoints del backend Laravel para la webapp Next.js.
>
> **Fecha:** 2026-05-06 | **Total endpoints:** ~197 documentados | **Cobertura:** ~92%
> **Base URL:** `http://eventos-backend.test/api/v1` (dev) — production: `https://api.eventos.app/v1`
> **Auth:** Bearer Sanctum via cookie httpOnly `eventos_auth` (proxy en Next routes `/api/...`)

## Indice

### Inventario detallado
- [Auth & Profile](#auth--profile)
- [Eventos & Branding](#eventos--branding)
- [Agenda & Sesiones](#agenda--sesiones)
- [Sponsors](#sponsors)
- [Leads](#leads)
- [Check-in & QR](#check-in--qr)
- [Networking](#networking)
- [Chat por sesion](#chat-por-sesion)
- [Social Wall](#social-wall)
- [Photobooth](#photobooth)
- [Gamification](#gamification)
- [Passport & Stories](#passport--stories)
- [Documentos & Custom Pages](#documentos--custom-pages)
- [Announcements & FAQ](#announcements--faq)
- [Encuestas / Polls](#encuestas--polls)
- [Stand Team (Vendor)](#stand-team-vendor)
- [Rewards](#rewards)
- [Admin Panel](#admin-panel)
- [Data Center](#data-center)
- [Pulse (Live Dashboard)](#pulse-live-dashboard)
- [Webhooks Inbound](#webhooks-inbound)
- [Session Stats & Exports](#session-stats--exports)
- [Room Check-in & Staff](#room-check-in--staff)
- [Attendance Checks (Silent Disco)](#attendance-checks-silent-disco)
- [Recap Post-evento](#recap-post-evento)
- [Misc](#misc)

### Estado vs webapp
- [Endpoints faltantes (gaps)](#endpoints-faltantes)
- [Cobertura por modulo W.x](#cobertura-por-modulo)
- [Recomendaciones](#top-5-recomendaciones)
- [Deuda tecnica de W.3](#deuda-tecnica-de-w3)
- [Como usar este documento](#como-usar-este-documento)

---

## Auth & Profile

**Routes:** `routes/api/auth.php` + `routes/api.php` (profile)
**Middleware:** mayoria publicos, algunos `auth:sanctum`

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| POST | `/auth/check-email` | publico | `{email}` | `{status: "not_found"\|"pending_activation"\|"active", invitation_token?, requires_verification?}` | Step 1 smart login |
| POST | `/auth/register` | publico | `{name, email, password, password_confirmation, phone?, company?, job_title?, consent_accepted, event_slug, fields?, access_code?}` | `{token, user: UserResource}` | Valida access_code + capacity + ventana |
| POST | `/auth/activate` | publico | `{token, password, password_confirmation, event_slug?}` | `{token, user: UserResource}` | Activa user importado |
| POST | `/auth/verify-identity` | publico | `{email, verification_value}` | `{status, invitation_token?}` | Pre-activacion verification |
| POST | `/auth/login` | publico | `{email, password, event_slug?}` | `{token, user: UserResource}` | throttle 5/15min |
| POST | `/auth/magic-link` | publico | `{email, event_slug?}` | `{message}` | Envia email Mailable |
| POST | `/auth/verify-magic-link` | publico | `{token}` | `{token, user: UserResource}` | Token 15 min |
| POST | `/auth/forgot-password` | publico | `{email}` | `{message}` | Reset flow |
| POST | `/auth/reset-password` | publico | `{email, token, password, password_confirmation}` | `{message}` | Cierra reset |
| GET | `/auth/verify-email/{token}` | publico | — | redirect HTML | Legacy |
| GET | `/auth/me` | sanctum | — | `{data: UserResource}` | Perfil actual |
| POST | `/auth/refresh` | sanctum | — | `{token, user: UserResource}` | Refresca Sanctum |
| POST | `/auth/verify-email` | sanctum | — | `{message}` | Reenvia |
| POST | `/auth/logout` | sanctum | — | `{message}` | Revoca token actual |
| POST | `/auth/expo-token` | sanctum | `{token}` | `{message}` | Push Expo (mobile) |
| GET | `/me/profile` | sanctum | — | `{data: {...}}` | Perfil completo |
| PUT | `/me/profile` | sanctum | `{name?, phone?, company?, job_title?, linkedin_url?}` | `{data}` | Update datos |
| POST | `/me/photo` | sanctum | FormData `{photo: File}` | `{data: {photo_url}}` | Sube foto perfil |
| DELETE | `/me/photo` | sanctum | — | `{message}` | Borra foto |
| GET | `/me/onboarding-data` | sanctum | — | `{data: {interests, fields}}` | Onboarding guardado |
| PUT | `/me/onboarding-data` | sanctum | `{interests?, fields?}` | `{data}` | Update onboarding |
| GET | `/me/registration-fields` | sanctum | — | `{data: [Field[]]}` | Campos dinamicos |
| PUT | `/me/registration-fields` | sanctum | `{fields: {field_id: value}}` | `{data}` | Update campos |

### UserResource
```ts
{
  id: number, name: string, email: string,
  phone: string | null, company: string | null, job_title: string | null,
  photo_url: string | null, linkedin_url: string | null,
  email_verified: boolean, is_active: boolean,
  created_at: string | null  // ISO8601
}
```

---

## Eventos & Branding

**Routes:** `routes/api/events.php` | **Middleware:** mayoria publicos

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/events/{eventId}/branding` | publico | — | `{data: {id, name, logo_url, primary_color, accent_color, ...}}` | Branding + UI config |
| GET | `/events/{eventId}/modules` | publico | — | `{data: [ModuleResource[]]}` | Modulos habilitados |
| GET | `/events/{eventId}/banners` | publico | — | `{data: [Banner[]]}` | Carrusel home |
| GET | `/events/{eventId}/highlights` | publico | — | `{data: [Highlight[]]}` | Featured items |
| GET | `/events/by-slug/{slug}` | publico | — | `{data: PublicEvent}` | Pre-login lookup |
| GET | `/events/{slug}/login-slides` | publico | — | `{data: [Slide[]]}` | Slides login |
| GET | `/events/{eventId}/happening-now` | publico | — | `{data: [EventSessionResource[]]}` | Live ahora (LiveState webapp) |
| GET | `/events/{eventId}/onboarding` | publico | — | `{data: {steps, config}}` | Onboarding flow |
| POST | `/events/{eventId}/onboarding/survey` | sanctum | `{interests: number[]}` | `{data: {message}}` | Guarda intereses |

### ModuleResource
```ts
{
  id: number, slug: string, name: string, icon: string, enabled: boolean,
  roles: string[], visibility_presence: string | null, visibility_tags: string[] | null,
  config: object | null, sort_order: number
}
```

---

## Agenda & Sesiones

**Routes:** `routes/api/events.php` | **Middleware:** mayoria `auth:sanctum + check.approval`

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/events/{eventId}/agenda` | sanctum (publico segun check.approval) | `?favorites=true` | `{data: {YYYY-MM-DD: [EventSessionResource[]]}, server_time}` | Agrupa por dia |
| POST | `/events/{eventId}/agenda/{sessionId}/favorite` | sanctum | — | `{data: {is_favorite: boolean}}` | Toggle |
| GET | `/events/{eventId}/sessions/{sessionId}/calendar.ics` | publico | — | `text/calendar` | .ics download |
| GET | `/events/{eventId}/speakers` | publico | — | `{data: [SpeakerResource[]]}` | Lista |
| GET | `/events/{eventId}/speakers/{speakerId}` | publico | — | `{data: SpeakerResource}` | Detalle + sessions |
| GET | `/events/{eventId}/tracks` | publico | — | `{data: [Track[]]}` | Categorias |
| POST | `/events/{eventId}/sessions/{sessionId}/rate` | sanctum | `{rating: 1-5, comment?: string}` | `{data: {id, rating, comment}}` | UNIQUE — re-rate retorna 409 |
| GET | `/events/{eventId}/my-ratings` | sanctum | — | `{data: {sessionId: rating}}` | Map ratings del user |
| POST | `/events/{eventId}/speakers/{speakerId}/rate` | sanctum | `{rating: 1-5, comment?}` | `{data}` | Rating speaker |
| GET | `/events/{eventId}/my-speaker-ratings` | sanctum | — | `{data}` | Mis speaker ratings |
| GET | `/events/{eventId}/my-interests` | sanctum | — | `{data: {interests}}` | Mis intereses |
| PUT | `/events/{eventId}/my-interests` | sanctum | `{interests: number[]}` | `{data}` | Update intereses |
| GET | `/events/{eventId}/sessions/{sessionId}/questions` | publico | `?approved=true` | `{data: [SessionQuestionResource[]]}` | Q&A |
| POST | `/events/{eventId}/sessions/{sessionId}/questions` | sanctum | `{body, is_anonymous?}` | `{data: SessionQuestionResource}` | Crea pregunta |
| POST | `/events/{eventId}/sessions/{sessionId}/questions/{qId}/upvote` | sanctum | — | `{data: {upvotes}}` | Upvote |
| GET | `/events/{eventId}/sessions/{sessionId}/questions/pending` | admin | — | `{data}` | Mod queue |
| PATCH | `/events/{eventId}/sessions/{sessionId}/questions/{qId}/moderate` | admin | `{status: "approved"\|"rejected"}` | `{data}` | Modera |
| GET | `/events/{eventId}/attendance` | publico | — | `{data: {current_attendees, capacity}}` | Aforo |

### EventSessionResource
```ts
{
  id: number, title: string, description: string | null,
  start: string, end: string,  // ISO8601
  location: string | null,
  session_type: { name: string, color: string } | null,
  status: "scheduled" | "live" | "delayed" | "cancelled" | "ended",
  capacity: number | null,
  stream_url: string | null, stream_iframe: string | null, recording_url: string | null,
  interactive_mode: "chat" | "qna" | "poll" | "none" | null,
  room_id: number | null, silent_disco_group_id: number | null,
  track: { id: number, name: string, color: string } | null,
  speakers: SpeakerResource[],
  is_favorite: boolean,        // solo cuando isset
  favorites_count: number      // solo cuando isset
}
```

### SpeakerResource
```ts
{
  id: number, name: string, bio: string | null,
  company: string | null, job_title: string | null,
  photo_url: string | null, linkedin_url: string | null,
  avg_rating: number | null,   // float redondeado 1 decimal
  rating_count: number,
  sessions: EventSessionResource[]   // whenLoaded
}
```

### SessionQuestionResource
```ts
{
  id: number, body: string,
  status: "pending" | "approved" | "rejected",
  upvotes: number, is_anonymous: boolean, my_upvote: boolean,
  author: string | null,   // null si anon y no es admin
  created_at: string
}
```

---

## Sponsors

**Routes:** `routes/api/sponsors.php` | **Middleware:** `auth:sanctum + check.approval`

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/events/{eventId}/sponsors` | publico | — | `{data: [SponsorResource[]]}` | Directorio |
| POST | `/events/{eventId}/sponsors/{sponsorId}/favorite` | sanctum | — | `{data: {is_favorite: true}}` | Marca |
| DELETE | `/events/{eventId}/sponsors/{sponsorId}/favorite` | sanctum | — | `{data: {is_favorite: false}}` | Quita |
| POST | `/events/{eventId}/sponsors/{sponsorId}/contact` | sanctum | `{message?}` | `{data: {message}}` | Contacta |
| POST | `/events/{eventId}/sponsors/{sponsorId}/view` | sanctum | — | `{message}` | Analytics view |

### SponsorResource
```ts
{
  id: number, name: string, tier: string,
  logo_url: string | null, banner_url: string | null,
  description: string | null, website_url: string | null,
  contact_email: string | null,   // solo si show_contact_button=true
  show_contact_button: boolean,
  is_favorite: boolean,           // whenLoaded
  services: SponsorServiceResource[],
  sessions: [{ id, title, start_datetime, end_datetime, location, session_type }]  // whenLoaded
}
```

---

## Leads

**Routes:** `routes/api/leads.php` | **Middleware:** `auth:sanctum + check.approval`

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/leads` | sanctum | `?per_page=15&page=1&sort=-created_at` | `{data: [LeadResource[]], meta}` | Mis leads |
| POST | `/leads` | sanctum | `{attendee_id, tier: "hot"\|"warm"\|"cold", note?}` | `{data: LeadResource}` | Crea (scan QR) |
| PUT | `/leads/{lead}` | sanctum | `{tier?, note?}` | `{data}` | Update |
| GET | `/leads/{lead}/edits` | sanctum | — | `{data: [LeadEditResource[]]}` | Historial edits |
| GET | `/me/leads/export` | sanctum | — | `text/csv` | Export CSV |

### LeadResource
```ts
{
  id: number, tier: "hot" | "warm" | "cold",
  note: string | null, scanned_at: string,
  attendee: {
    id: number, name: string, email: string,
    company: string | null, job_title: string | null,
    phone: string | null, photo_url: string | null
  }
}
```

---

## Check-in & QR

**Routes:** `routes/api/checkin.php` | **Middleware:** `auth:sanctum + check.approval`

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/me/qr` | sanctum | — | `{data: {token, url, expires_at}}` | QR personal |
| POST | `/checkin` | sanctum | `{qr_token}` | `{data: {message, attendee: AttendeeResource}}` | Check-in scan |

### AttendeeResource
```ts
{
  id: number, event_id: number, event_slug: string,
  role: "attendee" | "organizer" | "speaker" | "sponsor",
  tags: string[], stand_name: string | null, qr_token: string | null,
  networking_visible: boolean, checked_in_at: string | null,
  has_vendor_access: boolean, sponsor_id: number | null,
  registration_approved_at: string | "auto" | null,
  ban: { reason: string, expires_at: string | null } | null
}
```

---

## Networking

**Routes:** `routes/api/networking.php` | **Middleware:** `auth:sanctum + check.approval`

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/events/{eventId}/attendees` | sanctum | `?search=text&role=attendee` | `{data: [AttendeeResource[]]}` | Directorio |
| GET | `/events/{eventId}/suggested-contacts` | sanctum | `?limit=5` | `{data: [AttendeeResource[]]}` | Matchmaking (basado en intereses) |
| GET | `/attendees/{attendeeId}/profile` | sanctum | — | `{data: AttendeeResource}` | Perfil publico |
| POST | `/contacts/request` | sanctum | `{target_attendee_id, message?}` | `{data: {id, status: "pending"}}` | Envia solicitud |
| PUT | `/contacts/request/{id}` | sanctum | `{action: "accept"\|"reject"}` | `{data}` | Responde |
| GET | `/me/contacts` | sanctum | `?page=1` | `{data: [AttendeeResource[]], meta}` | Aceptados |
| GET | `/me/contact-requests` | sanctum | `?page=1` | `{data: [RequestResource[]]}` | Recibidas |
| GET | `/me/contact-requests/sent` | sanctum | `?page=1` | `{data}` | Enviadas |
| GET | `/me/blocked` | sanctum | — | `{data: [AttendeeResource[]]}` | Bloqueados |
| POST | `/contacts/block/{attendeeId}` | sanctum | — | `{data: {message}}` | Bloquea |
| DELETE | `/contacts/block/{attendeeId}` | sanctum | — | `{data: {message}}` | Desbloquea |

---

## Chat por sesion

**Routes:** `routes/api/chat.php` | **Middleware:** `auth:sanctum + check.approval`

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/sessions/{sessionId}/chat/messages` | sanctum | `?page=1&per_page=50` | `{data: [ChatMessage[]], meta}` | Historial |
| DELETE | `/admin/chat/messages/{id}` | admin | — | `{message}` | Mod (admin) |

> **Gap:** chat 1:1 entre asistentes NO existe. Solo chat por sesion. Ver [gaps](#endpoints-faltantes).

---

## Social Wall

**Routes:** `routes/api/events.php` | **Middleware:** mayoria `auth:sanctum + check.approval`

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/events/{eventId}/wall` | publico | `?page=1` | `{data: [WallPost[]], meta}` | Feed |
| POST | `/events/{eventId}/wall` | sanctum | `{content, photo_url?}` | `{data: WallPostResource}` | Crea post |
| POST | `/events/{eventId}/wall/{postId}/like` | sanctum | — | `{data: {is_liked: true}}` | Like |
| DELETE | `/events/{eventId}/wall/{postId}/like` | sanctum | — | `{data: {is_liked: false}}` | Unlike |
| GET | `/events/{eventId}/wall/{postId}/comments` | publico | — | `{data: [WallComment[]]}` | Comentarios |
| POST | `/events/{eventId}/wall/{postId}/comments` | sanctum | `{content}` | `{data: WallCommentResource}` | Crea comentario |

---

## Photobooth

**Routes:** `routes/api/events.php` | **Middleware:** mayoria `auth:sanctum + check.approval`

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/events/{eventId}/photos` | publico | `?page=1&contest=true\|false` | `{data: [Photo[]], meta}` | Galeria |
| GET | `/events/{eventId}/photos/mine` | sanctum | — | `{data: [Photo[]]}` | Mis fotos |
| GET | `/events/{eventId}/photos/contest` | publico | — | `{data}` | Photo contest (top likes) |
| POST | `/events/{eventId}/photos` | sanctum | FormData `{photo: File}` | `{data: PhotoResource}` | Sube |
| POST | `/events/{eventId}/photos/{photoId}/like` | sanctum | — | `{data: {is_liked: true}}` | Like |
| DELETE | `/events/{eventId}/photos/{photoId}/like` | sanctum | — | `{data: {is_liked: false}}` | Unlike |

---

## Gamification

**Routes:** `routes/api/events.php` + `api.php` | **Middleware:** `auth:sanctum + check.approval` (algunos sin check)

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/me/points` | sanctum | — | `{data: {total_points, balance}}` | Mis puntos (sin check.approval) |
| GET | `/events/{eventId}/leaderboard` | publico | `?limit=100` | `{data: [LeaderboardEntry[]]}` | Top |
| GET | `/events/{eventId}/gamification-config` | publico | — | `{data: {points_per_action}}` | Config |
| GET | `/events/{eventId}/gamification/rules` | publico | — | `{data: [Rule[]]}` | Reglas |
| POST | `/events/{eventId}/visit-stand/{sponsorId}` | sanctum | — | `{data: {points_awarded}}` | Visit stand |
| POST | `/events/{eventId}/trivia/{triviaId}/answer` | sanctum | `{answer}` | `{data: {correct, points}}` | Trivia |

---

## Passport & Stories

**Routes:** `routes/api/events.php` | **Middleware:** `auth:sanctum + check.approval`

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/events/{eventId}/my-passport` | sanctum | — | `{data: {stamps, progress}}` | Mi pasaporte |
| GET | `/events/{eventId}/stories` | publico | `?page=1` | `{data: [AttendeeStory[]], meta}` | Historias |
| POST | `/events/{eventId}/stories` | sanctum | FormData `{photo: File, caption?}` | `{data: StoryResource}` | Crea |
| DELETE | `/events/{eventId}/stories/{storyId}` | sanctum | — | `{message}` | Borra |

---

## Documentos & Custom Pages

**Routes:** `routes/api/events.php` | **Middleware:** publicos

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/events/{eventId}/documents` | publico | — | `{data: [DocumentResource[]]}` | Descargables |
| GET | `/events/{eventId}/pages` | publico | — | `{data: [CustomPageResource[]]}` | Custom |
| GET | `/events/{eventId}/pages/{pageId}` | publico | — | `{data: CustomPageResource}` | Detalle |

### DocumentResource
```ts
{
  id: number, title: string, file_url: string,
  file_size: number, mime_type: string,
  session_id: number | null, sort_order: number
}
```

### CustomPageResource
```ts
{
  id: number, title: string, icon: string | null,
  content_type: "text" | "html" | "iframe",
  body: string | null, iframe_url: string | null,
  iframe_height: number | null, allow_fullscreen: boolean,
  sort_order: number
}
```

---

## Announcements & FAQ

**Routes:** `routes/api/events.php` | **Middleware:** publicos

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/events/{eventId}/announcements` | publico | — | `{data: [AnnouncementResource[]]}` | Anuncios |
| GET | `/events/{eventId}/faqs` | publico | — | `{data: [Faq[]]}` | FAQs |

### AnnouncementResource
```ts
{
  id: number, title: string, body: string,
  action_url: string | null, image_url: string | null,
  roles: string[], published_at: string | null
}
```

---

## Encuestas / Polls

**Routes:** `routes/api/events.php` | **Middleware:** mayoria `auth:sanctum + check.approval`

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/events/{eventId}/surveys` | publico | — | `{data: [Poll[]]}` | Surveys post-event |
| GET | `/events/{eventId}/post-event-survey` | publico | — | `{data: Poll}` | Survey principal |
| POST | `/polls/{poll}/vote` | sanctum | `{option_id}` | `{data: {voted: true}}` | Vota |
| GET | `/sessions/{sessionId}/poll/active` | publico | — | `{data: Poll \| null}` | Poll activo en sesion |

---

## Stand Team (Vendor)

**Routes:** `routes/api/stand.php` | **Middleware:** `auth:sanctum + check.approval`

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/me/stand` | sanctum | — | `{data: {id, name, members}}` | Mi stand |
| GET | `/me/stand/stats` | sanctum | — | `{data: {leads_count}}` | Stats |
| GET | `/me/stand/contacts` | sanctum | — | `{data: [LeadResource[]]}` | Visitantes |
| POST | `/me/stand/members` | sanctum | `{attendee_id?, email?}` | `{data: StandMemberResource}` | Add miembro |
| DELETE | `/me/stand/members/{attendeeId}` | sanctum | — | `{message}` | Remove miembro |
| POST | `/me/stand/transfer` | sanctum | `{new_owner_id}` | `{data}` | Transferir propiedad |
| GET | `/me/stand/search-attendees` | sanctum | `?q=text` | `{data: [AttendeeResource[]]}` | Buscar |
| POST | `/me/stand/resolve-qr` | sanctum | `{qr_token}` | `{data: AttendeeResource}` | QR → attendee |
| POST | `/me/stand/share-link` | sanctum | — | `{data: {share_url}}` | Genera link |
| GET | `/me/pending-invitations` | sanctum | — | `{data: [Invitation[]]}` | Mis invites |
| GET | `/staff-invitations/{token}/info` | publico | — | `{data: {event, stand}}` | Invitation info |
| POST | `/staff-invitations/{token}/accept` | publico | `{password?, password_confirmation?}` | `{data: {token, user}}` | Acepta invite |
| POST | `/staff-invitations/{token}/reject` | publico | — | `{message}` | Rechaza |

### StandMemberResource
```ts
{
  id: number, attendee_id: number | null, invited_email: string | null,
  status: "pending" | "accepted" | "expired",
  name: string | null, photo_url: string | null,
  added_at: string, accepted_at: string | null, expires_at: string | null
}
```

---

## Rewards

**Routes:** `routes/api/rewards.php` | **Middleware:** `auth:sanctum + check.approval`

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/events/{eventId}/rewards` | sanctum | — | `{data: [RewardResource[]]}` | Premios |
| POST | `/events/{eventId}/rewards/{rewardId}/redeem` | sanctum | — | `{data: {redeemed_at}}` | Canjea |
| POST | `/rewards/confirm` | sanctum | `{redemption_id}` | `{data: {message}}` | Confirma (admin) |
| GET | `/me/prizes` | sanctum | — | `{data: [Prize[]]}` | Mis premios canjeados |
| GET | `/me/redemptions` | sanctum | — | `{data: [Redemption[]]}` | Historial |

---

## Admin Panel

**Routes:** `routes/api/admin.php` | **Middleware:** `auth:sanctum + check.approval` (admin only)

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/admin/events/{eventId}/attendees` | admin | `?page=1&sort=name&role=` | `{data: [AttendeeResource[]], meta}` | Gestion |
| PATCH | `/admin/attendees/{attendeeId}/role` | admin | `{role}` | `{data: AttendeeResource}` | Cambia rol |
| POST | `/admin/attendees/{attendeeId}/ban` | admin | `{reason, expires_at?}` | `{data: {banned_at}}` | Banea |
| DELETE | `/admin/attendees/{attendeeId}/ban` | admin | — | `{message}` | Desbanea |
| GET | `/admin/attendees/{attendeeId}/ban-history` | admin | — | `{data: [BanRecord[]]}` | Historial |
| GET | `/admin/events/{event}/notifications` | admin | — | `{data: [Notification[]]}` | Notifs sent |
| POST | `/admin/events/{event}/notifications/send` | admin | `{title, body, roles?}` | `{data: {sent_at}}` | Send notif |
| POST | `/admin/events/{event}/notifications/schedule` | admin | `{title, body, scheduled_at}` | `{data}` | Schedule |
| DELETE | `/admin/events/{event}/notifications/{notification}` | admin | — | `{message}` | Cancela |
| POST | `/admin/uploads` | admin | FormData `{file: File}` | `{data: {url, size}}` | Sube archivo |
| DELETE | `/admin/uploads` | admin | `{url}` | `{message}` | Borra |
| GET | `/admin/sessions/{sessionId}/live-config` | admin | — | `{data: {room_name, stream_url}}` | Config sesion |
| PATCH | `/admin/sessions/{sessionId}/live-config` | admin | `{room_name?, stream_url?}` | `{data}` | Update |
| POST | `/admin/sessions/{sessionId}/start` | admin | — | `{data: {status: "live"}}` | Inicia |
| POST | `/admin/sessions/{sessionId}/end` | admin | — | `{data: {status: "finished"}}` | Termina |
| POST | `/admin/sessions/{sessionId}/cancel` | admin | — | `{data: {status: "cancelled"}}` | Cancela |
| POST | `/admin/sessions/{sessionId}/delay` | admin | `{minutes}` | `{data: {new_start}}` | Retrasa |
| POST | `/admin/polls` | admin | `{title, options}` | `{data: PollResource}` | Crea poll |
| POST | `/admin/polls/{poll}/start` | admin | — | `{data: {status: "active"}}` | Inicia |
| POST | `/admin/polls/{poll}/close` | admin | — | `{data: {status: "closed"}}` | Cierra |
| GET | `/admin/polls/{poll}/results` | admin | — | `{data: {options, total_votes}}` | Resultados |
| POST | `/admin/polls/votes/{vote}/approve` | admin | — | `{message}` | Aprueba voto |
| POST | `/admin/polls/votes/approve-batch` | admin | `{vote_ids: number[]}` | `{data: {approved_count}}` | Aprueba batch |
| DELETE | `/admin/polls/votes/{vote}` | admin | — | `{message}` | Rechaza |
| POST | `/admin/games` | admin | `{session_id, type, ...}` | `{data: GameResource}` | Crea game |
| PATCH | `/admin/games/{id}` | admin | `{title?, settings?}` | `{data}` | Edita |
| DELETE | `/admin/games/{id}` | admin | — | `{message}` | Elimina |
| POST | `/admin/games/{id}/launch` | admin | — | `{data: {status: "active"}}` | Lanza |
| POST | `/admin/games/{id}/spin` | admin | — | `{data: {winner_id}}` | Spin |
| POST | `/admin/games/{id}/draw` | admin | `{winners}` | `{data: [Winner[]]}` | Sorteo |
| POST | `/admin/games/{id}/next-question` | admin | — | `{data: {question}}` | Siguiente |
| POST | `/admin/games/{id}/close-round` | admin | — | `{data: {correct_answer}}` | Cierra ronda |
| GET | `/admin/games/{id}/results` | admin | — | `{data: [Result[]]}` | Resultados |
| GET | `/admin/games/{id}/export` | admin | — | `text/csv` | Export |
| GET | `/admin/sessions/{sessionId}/games` | admin | — | `{data: [GameResource[]]}` | Games sesion |

---

## Data Center

**Routes:** `routes/api/data-center.php` | **Middleware:** `auth:sanctum` (Filament)

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/data-center/events` | sanctum | — | `{data: [Event[]]}` | Mis eventos |
| GET | `/data-center/multi` | sanctum | — | `{data: {totals}}` | Stats multi |
| GET | `/data-center/{event}/stats` | sanctum | — | `{data: {attendees_count, checkins}}` | Stats |
| POST | `/data-center/{event}/export` | sanctum | `{format, filters?}` | `{data: {export_id, url}}` | Export |
| POST | `/data-center/{event}/export-all` | sanctum | — | `{data: {export_id}}` | Todo |
| GET | `/data-center/{event}/exports` | sanctum | — | `{data: [Export[]]}` | Lista |
| DELETE | `/data-center/{event}/export/{id}` | sanctum | — | `{message}` | Cancela |
| GET | `/data-center/{event}/notifications` | sanctum | — | `{data: [Notification[]]}` | Notifs DC |
| POST | `/data-center/{event}/notifications/read-all` | sanctum | — | `{message}` | Marca leidas |
| DELETE | `/data-center/{event}/notifications/clear` | sanctum | — | `{message}` | Limpia |
| GET | `/data-center/{event}/goals` | sanctum | — | `{data: [Goal[]]}` | Goals |
| POST | `/data-center/{event}/goals` | sanctum | `{metric, target}` | `{data: GoalResource}` | Crea |
| DELETE | `/data-center/{event}/goals/{metric}` | sanctum | — | `{message}` | Borra |
| GET | `/data-center/{event}/scheduled` | sanctum | — | `{data: [ScheduledExport[]]}` | Programados |
| POST | `/data-center/{event}/scheduled` | sanctum | `{frequency}` | `{data}` | Crea schedule |
| DELETE | `/data-center/{event}/scheduled/{id}` | sanctum | — | `{message}` | Borra |
| GET | `/data-center/{event}/embeds` | sanctum | — | `{data: [Embed[]]}` | Embed tokens |
| POST | `/data-center/{event}/embeds` | sanctum | `{dashboard_type}` | `{data: {token, url}}` | Crea token |
| DELETE | `/data-center/{event}/embeds/{token}` | sanctum | — | `{message}` | Borra |

---

## Pulse (Live Dashboard)

**Routes:** `routes/api.php` | **Middleware:** `check.pulse` (token query param)

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/pulse/{slug}/bootstrap` | pulse_token | — | `{data: {event, stats}}` | Init |
| GET | `/pulse/{slug}/rooms` | pulse_token | — | `{data: [Room[]]}` | Salas |
| GET | `/pulse/{slug}/checkins` | pulse_token | — | `{data: {total, by_hour}}` | Timeline |
| GET | `/pulse/{slug}/leads` | pulse_token | — | `{data: {total, by_tier}}` | Leads |
| GET | `/pulse/{slug}/connections` | pulse_token | — | `{data: {total, accepted}}` | Networking |
| GET | `/pulse/{slug}/social` | pulse_token | — | `{data: {posts, likes}}` | Wall |
| GET | `/pulse/{slug}/leaderboard` | pulse_token | — | `{data: [LeaderboardEntry[]]}` | Top |
| GET | `/pulse/{slug}/ratings` | pulse_token | — | `{data: {avg_rating, by_session}}` | Ratings |

---

## Webhooks Inbound

**Routes:** `routes/api.php` | **Middleware:** `check.webhook` (X-Webhook-Key)

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| POST | `/webhooks/checkin` | webhook_key | `{attendee_id, event_id, timestamp}` | `{success: true}` | Single |
| POST | `/webhooks/checkin/batch` | webhook_key | `{checkins: [...]}` | `{processed}` | Batch |

---

## Session Stats & Exports

**Routes:** `routes/api/events.php`

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/sessions/{sessionId}/stats` | publico | — | `{data: {views, avg_rating}}` | Stats |
| GET | `/sessions/{sessionId}/viewers` | admin | — | `{data: [Viewer[]]}` | Viewers |
| GET | `/sessions/{sessionId}/export` | admin | — | `text/csv` | Export asistencia |

---

## Room Check-in & Staff

**Routes:** `routes/api/events.php`

### Rooms
| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/rooms/{eventId}/occupancy` | admin | — | `{data: {rooms: [{capacity, current_count}]}}` | Aforo salas |
| GET | `/rooms/{roomId}/attendees` | admin | — | `{data: [AttendeeResource[]]}` | Asistentes en sala |

### Staff Check-in (mobile)
| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| POST | `/staff-checkin/assign` | admin | `{staff_attendee_id, room_id}` | `{data: {assignment_id}}` | Asigna |
| POST | `/staff-checkin/unassign` | admin | `{assignment_id}` | `{message}` | Desasigna |
| POST | `/staff-checkin/scan` | staff | `{qr_token}` | `{data: {checked_in: true}}` | Scan |
| POST | `/staff-checkin/scan-batch` | staff | `{qr_tokens: string[]}` | `{data: {processed}}` | Batch |
| GET | `/staff-checkin/my-rooms` | staff | — | `{data: [Room[]]}` | Mis salas |
| GET | `/staff-checkin/rooms` | admin | — | `{data: [Room[]]}` | Todas |
| POST | `/staff-checkin/accept-assignment` | staff | — | `{data: {message}}` | Acepta |
| POST | `/staff-checkin/reject-assignment` | staff | — | `{data: {message}}` | Rechaza |
| GET | `/staff-checkin/pending-assignment` | staff | — | `{data: Assignment \| null}` | Pending |
| POST | `/staff-checkin/reassign` | admin | `{assignment_id, new_room_id}` | `{data: {message}}` | Reassign |

---

## Attendance Checks (Silent Disco)

**Routes:** `routes/api/events.php`

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| POST | `/attendance-checks/trigger` | admin | `{session_id}` | `{data: {check_id, timeout_seconds}}` | Inicia check |
| POST | `/attendance-checks/{checkId}/confirm` | sanctum | — | `{data: {confirmed: true}}` | Confirma |
| GET | `/attendance-checks/pending` | sanctum | — | `{data: Check \| null}` | Pending mio |
| GET | `/attendance-checks/active` | publico | — | `{data: Check \| null}` | Activo |
| GET | `/attendance-checks/history` | admin | `?page=1` | `{data: [Check[]], meta}` | Historial |
| GET | `/attendance-checks/{checkId}/results` | admin | — | `{data: {confirmed, total}}` | Resultados |
| GET | `/attendance-checks/report` | admin | — | `text/csv` | Reporte |

---

## Recap Post-evento

**Routes:** `routes/api/events.php` | **Controller:** `RecapController@myRecap`

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/events/{eventId}/my-recap` | sanctum | — | Discriminated union (3 casos) — ver shape abajo | Verifica threshold antes de generar; cache 24h |

### Shape `/my-recap` (3 casos)

```ts
type RecapResponse =
  // Caso 1: organizador desactivo recap
  | { enabled: false; reason: "recap_disabled" }

  // Caso 2: enabled pero usuario no cumple threshold (min sesiones + min %
  //         duracion configurado en `events.certificate_min_sessions` y
  //         `events.certificate_min_duration_pct`)
  | { enabled: true; available: false; reason: "threshold_not_met" }

  // Caso 3: enabled + usuario califica
  | {
      enabled: true;
      available: true;
      recap: {
        config: {
          tiers?: {
            show: boolean;
            thresholds: { insider_max_h: number; activo_max_h: number };
            labels: { insider: string; activo: string; headliner: string };
          };
          blocks?: {
            certificate?: { show: boolean; label: string; stats: string[] };
            // ... otros bloques (header_ig, protagonist, footer, back_side)
          };
          cover?: { cta_text: string };
          share?: { ig_button_text: string };
          // ... mas config customizable per evento via `events.recap_config` JSON
        };
        attendee: {
          name: string | null;
          handle: string | null;       // email handle (antes del @)
          role: string | null;         // user.job_title
          company: string | null;
          photo_url: string | null;
        };
        stats: {
          hours: number;
          minutes: number;
          sessions_count: number;
          days_count: number;
        };
        tier: "insider" | "activo" | "headliner";
        serial: string;                // "SUMMITNAME·2026·#1234"
        verify_code: string;           // HMAC para validar autenticidad
        sessions_list: Array<{
          session_id: number;
          title: string | null;
          joined_at: string | null;    // ISO8601
          duration_seconds: number;
        }>;
        image_url: string | null;      // URL R2 de la imagen recap (si Browsershot ya genero)
      };
    };
```

> **NO incluye `connections_count`** — para "X conexiones" del recap, llamar
> en paralelo a `GET /me/contacts?event_id={id}` y usar `data.length`.

> **NO incluye `points_earned`** — usar `GET /me/points?event_id={id}`.

---

## Misc

| Metodo | Path | Auth | Body | Response | Notas |
|---|---|---|---|---|---|
| GET | `/health` | publico | — | `{status: "ok"}` | Health |
| GET | `/version` | publico | — | `{version}` | Version |
| GET | `/presets/{type}` | publico | — | `{data: []}` | Listas estaticas (countries, industries) |
| GET | `/presets/cities/{countryCode}` | publico | — | `{data: []}` | Ciudades por pais |
| GET | `/events/{eventId}/registration-fields` | publico | — | `{data: [Field[]]}` | Campos registro |
| GET | `/events/{eventId}/onboarding-fields` | publico | — | `{data: [Field[]]}` | Campos onboarding |
| POST | `/track` | sanctum | `{event_id, action, metadata?}` | `{data: {tracked: true}}` | Analytics |
| POST | `/support` | sanctum | `{subject, message, email?}` | `{data: {ticket_id}}` | Soporte |
| GET | `/support/mine` | sanctum | — | `{data: [SupportTicket[]]}` | Mis tickets |
| GET | `/sessions/{sessionId}/game/active` | publico | — | `{data: Game \| null}` | Game activo |
| POST | `/games/{id}/join` | publico | `{attendee_id?}` | `{data: {joined: true}}` | Join |
| POST | `/games/{id}/answer` | publico | `{answer}` | `{data: {correct}}` | Responde |

---

# ESTADO REAL VS WEBAPP

> **Realidad verificada (2026-05-06):** el backend cubre TODO lo que la app movil
> consume. NO hay gaps bloqueantes. El roadmap webapp puede arrancar cualquier
> modulo sin esperar trabajo backend.

## Sin gaps bloqueantes

Todos los modulos planeados de la webapp (W.0 → W.17) tienen sus endpoints
disponibles en el backend, verificado contra los hooks reales que ya consume
la app movil (`eventos-app/hooks/`). El backend tiene cobertura ~100% para
features ya validadas en mobile.

## Eventos socket en tiempo real (RT)

El backend YA emite estos eventos via `socket-server` interno (room
`event:{eventId}`). La webapp solo tiene que suscribirse en W.11.

| Evento | Trigger backend | Payload | Notas |
|---|---|---|---|
| `session:started` | `Admin/SessionConfigController@start` | `{session_id, title, actual_start_at}` | Admin inicia sesion |
| `session:ended` | `Admin/SessionConfigController@end` | `{session_id, actual_end_at}` | Admin termina |
| `session:cancelled` | `Admin/SessionConfigController@cancel` | `{session_id, cancelled_at}` | Admin cancela |
| `agenda:updated` | `Admin/SessionConfigController@updateLiveConfig` | `{session_id, ...}` | Cambios live config |
| `agenda:delayed` | `Admin/SessionConfigController@delay` | `{room_name, minutes, affected_sessions}` | Retraso propagado |
| `chat:message:new` | `ChatController@store` | `{message: ChatMsg}` | Chat de sesion (no DM) |
| `points:awarded` | `AwardsPoints` trait | `{action, points, total}` | Gamification realtime |

Mas detalles en `eventos-socket/` (servidor) y `eventos-app/hooks/useChat.ts` +
`useDataInvalidation.ts` (suscripcion movil).

## Nice to have (NO bloquean ningun modulo)

Mejoras opcionales detectadas durante el barrido. Ninguna es necesaria para
shippear los modulos de la webapp — son optimizaciones de DX/UX.

| Mejora | Modulo | Workaround actual | Esfuerzo backend |
|---|---|---|---|
| Search server-side en agenda (`?q=`) | W.3 | Filtrado en cliente sobre el JSON completo (funciona bien hasta ~500 sesiones) | ~30min |
| Conflict detection de horarios favoritos | W.3 | Calculo en cliente comparando rangos `start/end` de favoritos | 0 (frontend) |
| Standardizar query params (`?q` vs `?search`) | Transversal | Cada hook normaliza | ~1h |
| `AttendeeResource` unificado entre contextos | W.8 | Cada endpoint ya retorna lo que necesita | ~2h refactor |

> **Decisiones cerradas (2026-05-06):**
> - **Chat 1:1 / DMs**: NO se implementa. Para mensajes directos entre asistentes
>   se usa el contacto registrado (email/LinkedIn/teléfono) o WhatsApp. El chat
>   actual es por sesion (room compartida) que ya cubre la conversacion live.
> - **Historial de notifs in-app**: NO se implementa. El push del SO + el feed
>   de anuncios (`/announcements`) cubren el caso. Si se necesita historial,
>   abrir tarea en su momento.
> - **Asistentes por sesion (lista de quien va)**: NO se implementa. La app
>   movil tampoco lo expone. La webapp oculta esa seccion del DetailPanel.

---

# COBERTURA POR MODULO

Todos los modulos tienen cobertura completa. Las cifras "necesarios" reflejan
solo endpoints que efectivamente consume el modulo (no inflado con futuras
features hipoteticas).

| Modulo W.x | Endpoints usados | Cobertura | Notas |
|---|---|---|---|
| W.1 Auth | 12 | 12/12 ✅ | Magic link + smart login + Sanctum |
| W.2 Home | 8 | 8/8 ✅ | Branding, modules, banners, highlights, happening-now |
| W.3 Agenda | 6 | 6/6 ✅ | Implementado y wireado al backend |
| W.4 Streaming | 5 | 5/5 ✅ | live-config + chat sesion + Q&A + polls + ratings |
| W.5 Speakers | 2 | 2/2 ✅ | Lista + detalle |
| W.6 Social Wall | 9 | 9/9 ✅ | Wall + stories + photos |
| W.7 Sponsors | 5 | 5/5 ✅ | Directorio + favorito + contact + view |
| W.8 Networking | 11 | 11/11 ✅ | Directory + suggested + contacts + blocked (sin DM) |
| W.9 Gamification | 6 | 6/6 ✅ | Points + leaderboard + passport + trivia + visit-stand + rewards |
| W.18 Hub Personal | 8 | 8/8 ✅ | Profile + photo + qr + prizes + redemptions + recap + support + ratings (renombrado desde W.10 el 2026-06-20) |
| W.11 Sockets RT | 7 events | 7/7 ✅ | Eventos session lifecycle + chat + points ya emitidos |
| W.13 FAQ/Docs/Pages | 3 | 3/3 ✅ | FAQs + documents + pages |
| W.14 Anuncios | 2 | 2/2 ✅ | announcements + highlights |
| W.15 Vendor Stand | 13 | 13/13 ✅ | Mi stand + leads + members + scan QR |
| W.16 Live Moments | 5 | 5/5 ✅ | Game active + join + answer + spin display |
| W.17 Soporte | 2 | 2/2 ✅ | POST /support + GET /support/mine |

---

# TOP 4 RECOMENDACIONES

## 1. Verificar shape de cada respuesta antes de codear
- **Por que:** W.3 encontro mismatches entre asumido vs Resource real
- **Que:** Para cada endpoint que vas a usar, abre `app/Http/Resources/V1/{Name}Resource.php` y lee el `toArray()`
- **Checklist:** Validar campos, tipos, condicionales (`whenLoaded`, `when`)

## 2. Curl al endpoint con token de prueba antes de escribir tipos TS
- **Como obtener token:** `php artisan tinker --execute="echo App\Models\User::first()->createToken('debug')->plainTextToken"`
- **Por que:** Confirma shape REAL > shape documentado. Catches breaking changes silenciosos.
- **Cuando:** Antes de definir interface en `lib/types/`.

## 3. Reusar hooks/lib del app movil como referencia
- **Por que:** Los hooks de `eventos-app/hooks/` ya validaron shapes y patterns optimistas
- **Que:** Antes de escribir un hook nuevo en webapp, mirar el equivalente movil
- **Ejemplo:** `useAgenda.ts` movil → ya tiene `extractFavorites`, `patchFavorite`, optimistic + revert. Solo adaptar a TanStack web.

## 4. Wirear sockets desde W.11 (no antes)
- **Por que:** Los eventos RT ya existen en backend, pero distribuirlos por modulo dispersa codigo
- **Que:** En W.11, montar un `useSocketEvent(eventName, callback)` que invalide queries TanStack al recibir el evento
- **Critico:** `session:started/ended/cancelled/delayed` → invalidate `['agenda', eventId]`

---

# DEUDA TECNICA DE W.3

**Problemas encontrados durante la implementacion del modulo W.3 Agenda:**

1. Asumio que `EventSessionResource` incluia `capacity`, `room_id` desde el principio sin verificar.
2. No verifico el shape de `track` (es objeto con `{id, name, color}` o solo `{id, name}`?).
3. Duplico logica de "agrupar por dia" que ya hacia el backend.
4. No documento que campos son condicionales (`whenLoaded` vs `when` vs directos).
5. Asumio que `?favorites=true` filtraba en backend — confirmado, pero no se uso al inicio.
6. Mock de "attendees por sesion" usando speakers como placeholder — confundio al usuario.
7. Toast placeholders de "Calendario", "Evaluar", "Unirme" sin POST real al backend hasta el barrido.

**Para W.8+ EVITAR:**

1. Lee SIEMPRE el Resource file ANTES de codear el componente.
2. Valida que campos son `whenLoaded()` vs siempre presentes.
3. Confirma tipos exactos (`string`, `int`, `bool`, `object[]`, `null`).
4. Pregunta: "Esto deberia estar en backend?" antes de hardcodear logica.
5. Documenta supuestos en ticket: "Espero que endpoint X retorne `{...}`".
6. Antes de escribir un componente que muestra datos, hace un `curl` con token de prueba para ver shape real.

---

# COMO USAR ESTE DOCUMENTO

Para **cada modulo W.X que inicies:**

1. Consulta tabla "Cobertura por Modulo" → anota gaps.
2. Para cada gap: crea issue backend con tag `missing-endpoint` + link a esta tabla.
3. Antes de codear el componente: lee la seccion correspondiente (ej: W.3 → "Agenda & Sesiones").
4. Abre archivos Resource: `app/Http/Resources/V1/{Name}Resource.php`.
5. Valida shapes esperadas: campos, tipos, null-ability.
6. Si shape distinto a lo esperado: DETENTE → abre issue "shape-mismatch" antes de codear workaround.

Para **debugging:**

- Cuando data inesperada: busca aqui → "Es shape correcto?" → "Es endpoint disponible?"
- Cuando comportamiento RT raro: consulta "Socket Events" → "Deberia ser RT?"

Para **onboarding nuevos devs:**

- Aqui esta TODO lo que el backend expone vs TODO lo que necesita la webapp.
- Muestra gaps + explica por que existen + workarounds actuales.

---

**Ultima actualizacion:** 2026-05-06 — barrido completo W.3 Agenda
**Mantenedor:** Cuando se agregue un endpoint backend, actualiza la seccion correspondiente + cobertura.
