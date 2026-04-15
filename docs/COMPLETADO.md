# Completado — EventOS

> Historial de todo lo implementado, organizado por area.
> Consultar para contexto historico. El dia a dia es `PENDIENTES.md`.
> Actualizado: 2026-04-15

---

## Fase 0 — Setup (2026-03-28)

- [x] Entorno: PHP 8.3, Redis, MySQL 8.4, Node 22, Git, Composer, EAS CLI
- [x] Laravel 11 base: Sanctum, Spatie, Filament, Horizon, Telescope, Sentry. 16 migraciones.
- [x] Expo SDK 55: Router, NativeWind, Reanimated, SecureStore, MMKV, TanStack Query, Zustand, i18n
- [x] Socket.IO: Redis adapter DB 2, auth Sanctum, rooms event/session/chat, health endpoint

## Fase 1 — MVP funcional (2026-03-29 → 2026-04-07)

| Sesion | Feature | Fecha | Tests |
|--------|---------|-------|-------|
| 1.1 | Auth + Roles + QR HMAC + Tracking | 2026-03-29 | 12 |
| 1.2 | Motor modulos dinamicos + cache Redis | 2026-03-28 | 23 |
| 1.3a | Contenido backend: sessions, speakers, pages, announcements, Filament CRUD | 2026-03-28 | — |
| 1.3b | Contenido app: agenda, speakers, pages, anuncios, FlashList + expo-image | 2026-03-28 | — |
| 1.4 | QR check-in + kiosco standalone + Socket.IO real-time | 2026-03-29 | — |
| 1.5 | Leads vendedor: scanner QR, notas, tier, historial, export | 2026-03-30 | — |
| 1.6 | Patrocinadores + stand teams: sponsors=stands, stand_members, lead_edits | 2026-03-30 | — |
| 1.7 | Networking: solicitudes, aceptar/rechazar, contactos, directorio | 2026-03-31 | — |
| 1.8 | Gestion usuarios + bans (motivo, expiracion, Filament) | 2026-03-31 | — |
| 1.9 | Chat real-time por sesion (Socket.IO, Redis) | 2026-04-01 | — |
| 1.10 | Encuestas en vivo (live_poll_questions, 4 tipos, slides) | 2026-04-04 | — |
| 1.11 | Push notifications (Expo Push API, FCM v1) | 2026-04-04 | — |
| 1.12 | Tracks + session types | 2026-04-04 | — |
| 1.13a | Emails automaticos: 11 mailables, BaseEventosMail, EmailService | 2026-04-05 | — |
| 1.13b | SMTP propio por organizacion | 2026-04-05 | — |
| 1.x | Upload imagenes / Cloudflare R2 | 2026-04-05 | — |
| 1.x | Banners: carrusel sponsors en Home | 2026-04-05 | — |
| 1.x-A | Onboarding configurable: backend + app base | 2026-04-05 | — |
| 1.x-B | Onboarding animaciones premium | 2026-04-06 | — |
| 1.14 | Streaming nativo + Mi Agenda | 2026-04-06 | — |
| 1.15 | Q&A en vivo + moderacion Filament | 2026-04-06 | — |
| 1.16 | Evaluacion sesiones (ratings, crystals) | 2026-04-06 | — |
| fix | YouTube iframe + logica separada por tipo URL | 2026-04-07 | — |
| 1.17 | Photobooth / Memorias (galeria moderada, likes) | 2026-04-07 | — |
| 1.18 | Matchmaking por intereses (overlap, sugerencias) | 2026-04-07 | — |
| 1.19 | Social wall (feed + posts + comments + likes) | 2026-04-07 | — |
| 1.20 | Gamification 13 acciones + leaderboard | 2026-04-07 | — |
| 1.21 | Passport stamps por lead scan | 2026-04-07 | — |
| 1.22 | Registro personalizable + import/export + approval + deep link + consent | 2026-04-07 | — |

## Pulido funcional + Stress testing (2026-04-07)

- [x] Tab vendedor unificado con presencial
- [x] Modulos por rol
- [x] k6 + artillery scripts (ejecutado local, pendiente VPS)

## Seguridad (2026-04-07 → 2026-04-12)

| ID | Fix | Tests |
|----|-----|-------|
| SEC-1.1 | Socket room authorization (join valida eventId, session via internal+Redis) | 6 |
| SEC-1.2 | HTMLPurifier trait en 8 modelos | 13 |
| SEC-1.3 | Token expiration 7d + POST /auth/refresh + auto-refresh interceptor | 7 |
| SEC-2.1 | SecurityHeaders middleware (X-Frame, CSP, HSTS, Referrer, Permissions) | 6 |
| SEC-2.2 | CORS hardening (methods/headers restringidos) | — |
| SEC-2.3 | HTTPS enforcement en app (throw si no HTTPS en prod) | — |
| SEC-2.4 | php artisan security:check | 4 |
| SEC-2.5 | .env.production.example + SESSION_SECURE_COOKIE | — |
| SEC-3.3 | Account lockout (5 intentos → 30 min, HTTP 423) | 6 |
| SEC-3.4 | Socket rate limiting Redis (max 5 conexiones/user) | — |
| SEC-3.5 | 5 FormRequests user-facing | — |
| SEC-3b.1 | Token register 30d → config (sanctum.expiration) | 2026-04-12 |
| SEC-3b.3 | Middleware CheckBan server-side en todas rutas API | 2026-04-12 |
| SEC-3b.5 | Ban real-time via socket (/internal/ban/enforce → app) | 2026-04-12 |

Total: 42 security tests, 309 tests backend.

## Real-time + Invalidation (2026-04-09)

- [x] RT sync admin→app: 5 entidades (agenda, speakers, announcements, branding, modules)
- [x] 4 capas: socket events + focusManager refetch + reconnect refetch + staleTime
- [x] Jitter aleatorio (200-1000ms) para evitar thundering herd
- [x] EventObserver + socket branding:updated para cambios de config

## UI/UX Lumina Noir (~98% completado)

### Fundamentos (2026-04-07)
- [x] Design tokens: #0e0e0e fondo, primary_color dinamico, Urbanist + PlusJakartaSans
- [x] ThemeProvider + useBranding + ScreenWrapper anti-flash Android
- [x] FloatingTabBar: liquid glass, sliding bubble spring, bounce on tap, 5 tabs reales
- [x] StatusBar light, SafeArea en todas las pantallas

### Pantallas completadas
- [x] Home: header configurable, hero text/image, HappeningNow crossfade 6s, GamificationHud, ModuleMenu cascade — 2026-04-07
- [x] Agenda: day strip, glass cards, tracks, heart particles, calendar, DaySlide direccional — 2026-04-08
- [x] Speakers: carousel breathing, detail hero, rating crystals diamond, LinkedIn — 2026-04-08
- [x] Streaming: split-screen, YouTube embed, session states, buttons contextuales — 2026-04-08
- [x] Social: Feed+Memorias+Momentos unificado, stories 24h, comments BottomSheet, header blur sticky — 2026-04-09 + 2026-04-11
- [x] Sponsors: Brand Wall grid tiers, living shuffle, Brand Profile, trivia A/B/C/D, contact form — 2026-04-09
- [x] Profile: beam Ocean, edit modal, social links, stats, pull-to-refresh — 2026-04-09
- [x] Encuestas PollSlides: rediseno completo por pregunta (Multiple/Star/OpenText) — 2026-04-10
- [x] Chat sesion: emojis animados, cooldown, Enter=enviar, error handling — 2026-04-10
- [x] Mi QR: tab real, RGB wave pastel, badge digital, fullscreen modal, wallet ready — 2026-04-10
- [x] Gamificacion: HUD teal/cyan, barra segmentada, pantalla unificada, portal cards, premios — 2026-04-10
- [x] Vendedor: tabs presencial, VendorHappeningNow, Mi Stand premium, leads Lumina Noir — 2026-04-10
- [x] Networking: contactos vCard, guardar en telefono, export masivo .vcf — 2026-04-10
- [x] Pending-approval Lumina Noir — 2026-04-12
- [x] Activate-account Lumina Noir — 2026-04-12
- [x] ConnectionError screen reutilizable — 2026-04-12
- [x] Banned screen Lumina Noir — 2026-04-11

### Responsive (2026-04-10)
- [x] Audit completo 360dp (ZTE) + 411dp (Medium Phone): 31 archivos, 12 pantallas SafeArea
- [x] Layout proporcional: skeletons, leads header, login scroll, ModuleMenu, HappeningNow, HomeHero

### Micro-interacciones (2026-04-11)
- [x] ScalePress: tap feedback 0.96 + haptic (ModuleMenu, Speakers, Agenda, Networking)
- [x] Image reveal: transition={300} en 17 archivos expo-image
- [x] ContentFade: opacity 0→1 (400ms) en sponsors, anuncios, speaker detail
- [x] FadeInItem: stagger wave top→bottom
- [x] AnimatedBadge: scale pop en HomeHeader + Networking badges
- [x] Haptics: leaderboard, profile, social, bell
- [x] Screen transitions: slide_from_right (stream mantiene slide_from_bottom)
- [x] Home wave entrance: HomeSkeleton → FadeInItem coordinado

### Transversales
- [x] Skeleton loading en 5+ pantallas
- [x] EmptyState en social feed, memorias, speaker detail
- [x] Iconografia: MaterialCommunityIcons (no emojis) en ModuleMenu
- [x] LuminaToast reemplaza Alert.alert en toda la app
- [x] returnKeyType en 14 archivos (search/next/go/send/done)
- [x] Background #1a1919 → #141414, console.log cleanup

## Onboarding DaVinci (2026-04-11 → 2026-04-12)

- [x] Welcome: 5 floating pills formas unicas, 5 backgrounds configurables (particles, constellation, mesh, bubbles, minimal), primaryColor aplicado
- [x] Auth: login/register animado, ForgotSheet BottomSheet, keyboard handling, stats con FadeInDown
- [x] Login inteligente 2 pasos: POST /auth/check-email → not_found/pending_activation/active → password animado
- [x] Photo: avatar 180px, camara/galeria, upload + authStore update, foto en context
- [x] About: preview card live, cargo/empresa, campos con iconos, scroll natural
- [x] Interests: chips wrap con emoji, haptic, min 3, pending_survey MMKV
- [x] Done: badge IDENTICO a MiQR, QR real funcional, tap fullscreen, confetti (solo registro)
- [x] Gamificacion: AnimatedPts (scale+particulas+color flash), SkipModal BottomSheet, 80pts max
- [x] Auth legacy eliminado (login.tsx, register.tsx, forgot-password.tsx borrados)
- [x] Activate-account redirige a onboarding photo step (flag post_activation_onboarding)

## Onboarding configurable Filament (2026-04-12)

- [x] JSON onboarding_steps_config en events (migration + model + cast array)
- [x] Filament UI: 7 secciones colapsables (Welcome, Auth, Photo, Forms, Survey, Done, Orden)
- [x] Welcome: pills dinamicas, hero image, show_text, title_prefix, textos botones
- [x] Auth: show_title/subtitle/stats, stats dinamicas, hide register link
- [x] FormStep generico: campos dinamicos (text/tel/email/number/url/select/checkbox/textarea)
- [x] Colores master/slave (ColorPicker en Filament, useStepColors helper)
- [x] Steps dinamicos desde step_order + enabled flags
- [x] Real-time: polling 30s pre-login + socket data:invalidate post-login
- [x] Retrocompatibilidad: si config null, fallback a hardcoded

## Campos dinamicos 1.x-E-A (2026-04-12b)

- [x] SelectSheet: BottomSheet radio reutilizable con accent color y haptic
- [x] Checkbox: Switch toggle inline, glass row
- [x] Textarea: TextInput multiline 4 lineas
- [x] Validacion required: borde rojo + toast + skip oculto si required
- [x] PreviewCard live cuando form tiene job_title/company

## Campos dinamicos 1.x-E-B (2026-04-13)

- [x] SearchableSheet: BottomSheet 65% con TextInput busqueda, filtro local, radio select
- [x] CheckboxGroupSheet: BottomSheet multi-select con checkboxes, boton "Confirmar (N)"
- [x] DateTimePicker: @react-native-community/datetimepicker, picker nativo, guarda ISO, muestra formateado es-CO
- [x] FormStep: 3 render cases nuevos (searchable_select, checkbox_group, date)
- [x] onboardingApi.ts: tipos actualizados (3 tipos + preset + preset_options)
- [x] PresetController: GET /presets/{type}, GET /presets/cities/{code}
- [x] config/presets.php: 53 paises, ciudades (9 paises), 20 industrias
- [x] Filament: 11 tipos en selector + campo preset para searchable_select
- [x] OnboardingController: resuelve preset → preset_options al servir config
- [x] OnboardingSeeder: ejemplos de los 3 tipos nuevos
- [x] QA: 7 tests presets + 11 tipos verificados, 309 backend tests passing

## Seguridad SEC-3b completado (2026-04-13)

- [x] SEC-3b.2: index.tsx valida GET /me al startup. 401→clearAuth, 403→banned/pending. Fallback red graceful.
- [x] SEC-3b.4: CheckApproval.php middleware server-side. 403 si no aprobado. Excluido de auth/profile/onboarding.
- [x] Fix: index.tsx maneja 403 (antes solo 401, app podia quedar colgada)
- [x] Fix: lockout counter se resetea cuando lock expira (antes acumulaba intentos para siempre)

## QR dinamico rotativo 1.C3 (2026-04-13)

- [x] Formato d.{attendee_id}.{window}.{signature_32hex} — O(1) validacion, no O(n)
- [x] Ventana 60s, tolerancia 5 ventanas (~180s), clock skew +-1
- [x] GET /me/qr devuelve token dinamico + expires_in (TTL real)
- [x] Checkin valida: estatico primero (backward compat), luego dinamico
- [x] LeadController tambien valida dinamico (fix posterior al QA)
- [x] App: useQrToken refetch 50s, MiQrScreen countdown "QR dinamico · 45s"
- [x] DoneStep: preview actualizado al formato nuevo
- [x] Validacion qr_token relajada min:20 max:100 (formato variable)

## Scanner Lumina Noir (2026-04-13)

- [x] Reescritura completa scanner-stand.tsx (NativeWind → StyleSheet)
- [x] Resultados en BottomSheet (55% success/duplicate, 38% error)
- [x] Lead card con foto, nombre, cargo, empresa, email
- [x] Tier selector inline (hot/warm/cold) en resultado success
- [x] Scan line animada Reanimated, haptic feedback en todos los estados
- [x] ScalePress en todos los botones, SafeArea top+bottom

## Docs reestructurados (2026-04-13)

- [x] EventOS_Roadmap.md: 3217 → 419 lineas (v4.0 slim)
- [x] Original archivado: docs/ROADMAP-HISTORICO-v3.1.md (3144 lineas intactas)
- [x] docs/PENDIENTES.md: reescrito auto-contenido, organizado por area de trabajo
- [x] docs/COMPLETADO.md: nuevo, historial por area
- [x] 3 apendices extraidos: ANALISIS-COMPETITIVO.md, WEB-APP-PLAN.md, WHITE-LABEL.md
- [x] PLAN-TAGS-MODULOS.md: plan arquitectura tags + visibilidad modulos (aprobado, pendiente implementar)

## Moderacion chat completa (2026-04-12)

- [x] Ban real-time via socket (/internal/ban/enforce → ban:enforced → app /banned)
- [x] Middleware CheckBan en todas rutas API (excepto auth/me, auth/logout)
- [x] Interceptor 403 ban en api.ts
- [x] Palabras bloqueadas chat + Q&A (config Filament, silent drop, cache 5min)
- [x] Chat delete + ban desde app (admin long press) y desde chat monitor
- [x] Chat monitor HTML standalone por sesion (acceso desde Filament)
- [x] Velocidad monitor (cola mensajes directo/0.5s/1s/2s), slow mode, pause/resume
- [x] Rendimiento: cache auth tokens 15min, connection pooling, message batching 200ms

## Error handling (2026-04-12)

- [x] ConnectionError screen reutilizable (wifi-off, reintentar)
- [x] Onboarding: 6s timeout + spinner + ConnectionError
- [x] Home presencial + virtual error states

## Auditoria auth (2026-04-12)

- [x] 39 escenarios + 10 edge cases verificados en codigo
- [x] 14 bugs encontrados y corregidos (BUG-065 a BUG-078)
- [x] 3 code smells documentados (CS-001/002/003, no criticos)

## QA Master (2026-04-12b)

- [x] Barrido completo: 60+ endpoints probados con curl real
- [x] 20 modulos cubiertos, 3 roles verificados (presencial 14 modulos, virtual 11, vendedor stand+leads)
- [x] Escritura: social post/like/comment, Q&A, poll vote, profile, registration fields, favorites, photo, expo token
- [x] Middleware: auth 401, ban 403, throttle login 429, security headers
- [x] Docs: QA-AUTH-ONBOARDING.md + QA-MASTER.md

## Rewards / Premios canjeables (2026-04-10)

- [x] Catalogo premios: Filament CRUD, addon toggle, RewardSeeder
- [x] QR temporal HMAC 5min para canje
- [x] Staff confirma escaneo, descuenta puntos
- [x] Expire + refund automatico si no se canjea
- [x] 9 tests, seguridad SQL fix, points_log signed
- [x] Flujo canje: toast, estados boton (canjear/ver ticket/canjeado), QR rect RGB, recovery

## Gamification config (2026-04-10)

- [x] 13 acciones con puntos, daily_max, labels editables
- [x] Roles por accion, rules endpoint publico
- [x] Filament unificado bajo grupo Gamificacion
- [x] passport_completion_points configurable

## Analisis competitivo (2026-04-09)

- [x] Cotizaciones reales: Cisco $88K USD, ICE360 $49M COP
- [x] 8 features donde EventOS ya supera a ambos
- [x] 6 gaps identificados → sesiones 1.C1-C6
- [x] Pricing: $3K-8K/evento, $800-1.5K/mes SaaS
- [x] Escalabilidad horizontal validada hasta 100K+

## Showcase web demo inversor (2026-04-13)

- [x] 6 versiones iteradas (v1→v6): opening cinematico, speakers, agenda, streaming, networking, social, gamification, brand wall
- [x] Cursor fantasma, minimize-to-pill, TNT finale, progress dots, play gate
- [x] GSAP skills repo clonado (design/gsap-skills/)
- [x] Spatial UI documentado en roadmap (Apendice F → docs/WEB-APP-PLAN.md)

## Tags + Visibilidad modulos — Backend (2026-04-14)

- [x] Migration: campo `tags` JSON en attendees (default [])
- [x] Migration: `visibility_presence` enum + `visibility_tags` JSON en modules
- [x] Roles simplificados: presencial/virtual eliminados → `attendee` unico
- [x] Attendee model: cast tags array, helper `hasAnyTag()`
- [x] Module model: cast visibility_tags array
- [x] ModuleController: filtrado triple (role + checked_in_at + tags)
- [x] QR disponible para todos los attendees (identidad, no solo ticket)
- [x] CheckinService: attendance count usa attendee (no presencial)
- [x] API resources: AttendeeResource expone tags, ModuleResource expone visibility
- [x] Filament: 9 resources actualizados (roles attendee/vendedor, TagsInput, visibility selectors)
- [x] Validation rules: NotificationController, AttendeeController actualizados
- [x] Observers: ModuleObserver + ContentObserver con nuevas cache keys
- [x] AuthService + AttendeesImport: default role attendee
- [x] AttendeeFactory: +withTags() state, default attendee
- [x] 48 archivos modificados, 314 tests passing (797 assertions), 0 fallos
- [x] Commit: 30ce854

## Layout unificado — App (2026-04-14)

- [x] Merge (presencial)/(tabs) + (virtual)/(tabs) → (tabs)/ unico
- [x] Home unificado con logica vendor condicional (isVendor)
- [x] UserRole simplificado: 'attendee' | 'vendedor' | 'admin'
- [x] Ruta unica homeRoute() → /(app)/(tabs) para todos
- [x] authStore, authApi, DoneStep, MiQrScreen, ProfileScreen, activate-account, pending-approval actualizados
- [x] 14 archivos eliminados (layouts duplicados), 6 creados, 9 actualizados
- [x] TypeScript 0 errores nuevos, Expo bundle compila OK (4.6MB)
- [x] Commit: 810cc89

## Event Lifecycle — Backend + App (2026-04-14)

- [x] Migration: status enum (draft/registration/published/live/ended) + modality enum en events
- [x] Migration: about_enabled, about_image_url, about_text, about_links en events
- [x] Migration: roles en modules JSON (presencial/virtual → attendee)
- [x] Admins ven todos los modulos (skip role filter en ModuleController)
- [x] Branding API: status, modality, venue, max_attendees, registered_count, session_count, photo_count, about_*
- [x] Filament: estado+modalidad+fechas+venue+capacidad+about condicional con toggle
- [x] EventObserver → InvalidationService → socket data:invalidate → app refetch
- [x] App: 4 estados de Home (registration, published, live, ended)
- [x] CountdownTimer: normal (registration) + compact (published) + "comienza hoy" (expired)
- [x] EventInfoCard: modalidad badge, venue, registrados/capacidad, cierre registro
- [x] EventArchive: banner finalizado + stats + links a agenda/social/gamification/speakers
- [x] About pre-evento: pantalla /(app)/about con imagen+texto+links, card en Home registration
- [x] ModuleMenu compact en published (cards 56px, iconos 16px)
- [x] Socket debounce 800ms por entidad (7 invalidaciones = 1 refetch)
- [x] useBranding staleTime 30min → 5min + refetchQueries
- [x] Role pill eliminado de Mi QR, Perfil, DoneStep
- [x] Fix: branding API crasheaba por columna 'approved' inexistente
- [x] Fix: Stack.Screen mi-qr fantasma causaba socket reconnect loop
- [x] Seeders actualizados (7 archivos presencial→attendee)
- [x] 314 tests backend, TS 0 errores app
- [x] Commits backend: d970983, e4a3981, 7a61a87, 878a0b3, d375e26, d48b559, 7bda761, b25518b
- [x] Commits app: 810cc89, 59f49f7, 267ec45, 90edd7f, 7f6f6cd, c26eb06, 0e5ff8b, f30c9fd, 167c72c, 95b8aa5, b81e22a, bcc7649, 142d039, daf8411, 877a851, 85ee6f3, 5834769

## Filament cleanup (2026-04-14)

- [x] Reorganizar navegacion: 11 grupos inconsistentes → 7 limpios
- [x] Grupos: Evento(6), Contenido(7), Interaccion(7), Comunicacion(5), Registro(4), Gamificacion(3), Sistema(1)
- [x] Tildes unificadas, sort secuencial sin duplicados
- [x] 26 archivos actualizados
- [x] Commit: d2a9e86

## Optimizacion onboarding cache (2026-04-14)

- [x] useOnboarding: cache-first desde MMKV (initialData + initialDataUpdatedAt=0)
- [x] Primera vez: loading → fetch → cache. Segunda vez en adelante: instantaneo
- [x] Eliminado refetchInterval 30s (innecesario con socket invalidation)
- [x] Commit app: 5834769

---

## Sesion 2026-04-14 (tarde) — Session Detail + Onboarding + CSV + Codigos + Seguridad

### Session Detail Screen
- [x] Pantalla detalle de sesion: badges, titulo, rating, time/location, speakers tappables
- [x] Botones: Favorita, Calendario, Evaluar, UNIRTE / Ver grabacion
- [x] Navegacion circular: Agenda → Session → Speaker → Session
- [x] Agenda card tap → session detail (antes iba directo a stream)

### Onboarding — depends_on + replay
- [x] Campos condicionales: depends_on en config JSON (pais → ciudades dinamicas)
- [x] preset_value_map para resolver codigos pais
- [x] Onboarding replay diferenciado: pre-fill foto/profile/custom/intereses
- [x] Sin confetti, sin puntos dobles, titulo "Datos actualizados"
- [x] onboarding_data JSON en attendees para campos custom
- [x] GET/PUT /me/onboarding-data endpoints
- [x] isReplay flag persistente (useState lazy initializer)

### Pre-registro CSV
- [x] Flujo completo: CSV → User+Attendee+QR → InvitationMail → deep link → activacion
- [x] Fallback sin deep link: check-email → token directo → activate-account
- [x] Deep link redirect page Lumina Noir
- [x] Verificacion identidad: campo configurable (telefono), POST /auth/verify-identity
- [x] Token rotado en cada check-email (seguridad)
- [x] Tracking deep link: invitation_clicked_at + badges Enviado/Click/Activado
- [x] Login pasa event_slug (fix bug recurrente)

### Registro por Codigo de Acceso
- [x] Modelo AccessCode con isValid(), registerUse() atomico
- [x] Filament AccessCodeResource: CRUD + generar lote + ver usos
- [x] Toggle requires_access_code en RegistrationSettings
- [x] Campo condicional en AuthStep (uppercase, sanitiza espacios)
- [x] Validacion en RegisterRequest + AuthService
- [x] Tracking: access_code_used en attendee, filtro en asistentes
- [x] registerUse() con WHERE atomico (race condition safe)

### Bugs resueltos: 21 (BUG-079 a BUG-099)
- [x] BUG-079: API crash array_flip cities (CRITICA)
- [x] BUG-080: Login sin event_slug (ALTA)
- [x] BUG-081: Fetch sin timeout en index (ALTA)
- [x] BUG-082: registrationApprovedAt fallback (ALTA)
- [x] BUG-083-099: 17 bugs adicionales (onboarding, seguridad, UX)

### Bancolombia — documentacion
- [x] Requerimientos: silent disco, multi-location, webhooks badges, mission control
- [x] Timeline y estrategia competitiva actualizados en roadmap

Commits: ~20 commits en 3 repos (app, backend, docs)

---

## Sesion 2026-04-15 — Campos unificados + Registro avanzado + FAQ + Soporte

### Campos unificados registration_fields (2026-04-15 manana)
- [x] registration_fields como fuente unica (elimina campos inline en onboarding_steps_config)
- [x] Nuevas columnas: `depends_on` (varchar 60), `show_in` (registration/onboarding/both)
- [x] Onboarding config solo tiene `field_ids` (resuelve a campos reales via API)
- [x] Endpoint nuevo: GET /events/{id}/onboarding-fields
- [x] PUT /me/registration-fields acepta campos onboarding/both
- [x] Export CSV incluye todo automaticamente (una sola tabla)
- [x] depends_on avanzado: `"campo"` (cualquier valor), `"campo:val1,val2"` (especificos), `"campo:!val"` (negacion)
- [x] Deep merge Filament: mutateFormDataBeforeSave evita que secciones colapsadas null sobreescriban config
- [x] Legacy backward compat: configs con fields inline siguen funcionando

### Validacion DaVinci — 4 bugs (2026-04-15 manana)
- [x] BUG-100: Nombre acepta @# → regex solo letras+tildes+espacios
- [x] BUG-101: InterestsStep sin toasts → toast error + exito
- [x] BUG-102: Botones disabled sin feedback → eliminado disabled, validacion con toast
- [x] BUG-103: Inputs sin borde rojo → hasError en FocusInput (AuthStep)
- [x] FormStep: toast especifico por campo ("El campo X es obligatorio")

### Staff invite 1.x-H — COMPLETADO (2026-04-15 tarde)
- [x] Backend: StaffInvitationController, StaffNotificationService, join-team.blade.php, 2 migrations, 23 tests
- [x] App: mi-equipo.tsx, scanner-invite.tsx, join-team/[token].tsx, StaffInvitationModal.tsx, useStaffInvitations.ts
- [x] Socket: StaffInvitePayload, StaffResponsePayload, StaffRemovedPayload (4 event types)
- [x] Flujo: QR scan + busqueda nombre + email + link compartible + deep link aceptacion
- [x] Landing web Lumina Noir join-team.blade.php
- [x] Config: multi-stand + expiracion tokens

### Registro cerrado 1.x-F — COMPLETADO (2026-04-15 tarde)
- [x] 3 modos: email_list, domain, both (OR logic)
- [x] Toggle master, compatible con approval + access_code + invite_only
- [x] Sanitizacion: lowercase, trim, strip @ de dominios
- [x] Mensaje rechazo custom o default
- [x] 21 tests, 38 assertions. QA: 21 escenarios verificados

### Login intentos + lockout (2026-04-15 tarde)
- [x] Backend: "Credenciales incorrectas. X intentos restantes." en 422
- [x] Backend: 5to intento devuelve 423 directo con lockout
- [x] App: Object.values(errors).flat()[0] para cualquier campo
- [x] QA audit: 25+ casos de error auth verificados

### Encuesta post-evento (2026-04-15 tarde)
- [x] Backend: scope post_event en live_polls, auto-activacion EventObserver al ended
- [x] Filament: PostEventSurveyResource dedicado (CRUD, activar/cerrar manual)
- [x] App: card EventArchive, usePostEventSurvey hook
- [x] Reutiliza 100% del sistema de encuestas existente (zero duplicacion)
- [x] Seeder: 5 preguntas, 35 encuestados, 201 votos. Export CSV funcional.
- [x] 9 tests, 27 assertions

### FAQ asistente — COMPLETADO (2026-04-15 noche)
- [x] Backend: tabla event_faqs (event_id, section, question, answer_text, answer_action_url, answer_image_url, sort_order, is_active)
- [x] Backend: FaqController API GET /events/{id}/faqs (publica, agrupada por seccion) + 6 tests
- [x] Backend: CRUD Filament FaqResource
- [x] Backend: FaqSeeder (4 categorias, 12 preguntas)
- [x] App: Pantalla FAQ con orbe animado (OrbBlob 3 estados), categorias stagger, accordion con Reanimated
- [x] App: Icono Ayuda en perfil → ruta /(app)/faq
- [x] App: Filtro por categoria (chips horizontales), busqueda visual

### Soporte asistente — COMPLETADO (2026-04-15 noche)
- [x] Backend: migration admin_response + responded_at + resolved_at en support_requests
- [x] Backend: SupportController (store + mine), ordena por id DESC
- [x] Backend: Filament SupportRequestResource con badge rojo pendientes + textarea respuesta + auto-resolve
- [x] Backend: 10 tests, 34 assertions (SupportRequestTest.php)
- [x] App: support-contact.tsx — formulario asunto+mensaje, toast + router.back() (no pantalla sent vacia)
- [x] App: my-support.tsx — lista consultas con status badge (pendiente amber / resuelto green)
- [x] App: faq.tsx — boton "Mis consultas (N)" solo si tiene consultas
- [x] Push: SendPushToAttendeeJob al responder soporte ("Tu consulta fue resuelta: Re: {subject}")
- [x] Push tap navigation: PUSH_ROUTES map (support_resolved→my-support, announcement→anuncios, agenda_reminder→agenda)
- [x] INVALIDATION_MAP: support_resolved invalida ['my-support']

### Dev build Android (2026-04-15 noche)
- [x] Wireless debugging: adb pair + connect (puerto pairing != puerto conexion)
- [x] npx expo run:android — dev build nativo en dispositivo fisico
- [x] Push notifications verificadas en dispositivo real
- [x] Push tap navigation verificada

### Totales 2026-04-15
- Backend: 397 tests, 1009 assertions, 0 fallos
- ~20 commits backend, ~20 commits app, 1 commit socket, 3 commits docs
- Features completados: campos unificados, staff invite, registro cerrado, login lockout, encuesta post-evento, FAQ, soporte completo, push navigation
- Bugs: BUG-100 a BUG-103 resueltos
