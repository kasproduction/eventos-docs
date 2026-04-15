# EventOS — Roadmap

_Plan de trabajo: fases, sesiones, dependencias, timeline_
_Actualizado: 2026-04-14 | v4.2_

---

## Estado rapido

| Fase | Estado | Detalle |
|------|--------|---------|
| **Fase 0** — Setup | ✅ Completa | 4 sesiones, 2026-03-28 |
| **Fase 1** — MVP funcional | ✅ ~98% | 22+ sesiones, 2026-03-29 → 2026-04-07. Tags+visibility backend 04-14 |
| **UI/UX Lumina Noir** | ✅ ~98% | Barrido visual completo, 2026-04-07 → 2026-04-12 |
| **Seguridad** | ✅ 80% | SEC-1/2/3 + 3b parcial. 42 tests. Pendiente: SEC-3b.2, 3b.4, 4, 5 |
| **Onboarding DaVinci** | ✅ | 6 steps, configurable Filament, gamificacion, 5 backgrounds |
| **Moderacion** | ✅ | Ban RT, palabras bloqueadas, chat monitor, slow mode |
| **QA** | ✅ | 60+ endpoints, 20 modulos, 314 tests backend |
| **Deploy** | ⏳ | Docker + VPS + CI/CD |
| **Fase 2** — Web app | ⏳ | Next.js, W.0–W.12 |
| **Fase 3** — SaaS | ⏳ | Multi-tenant, monetizacion |

**Que falta:** ver `docs/PENDIENTES.md`
**Que se hizo:** ver `docs/COMPLETADO.md`

---

## Repositorios

| Repo | Ruta local | Stack |
|------|-----------|-------|
| kasproduction/eventos-backend | `C:\laragon\www\eventos-backend` | Laravel 11 + Filament |
| kasproduction/eventos-app | `C:\Users\Kasproduction\Projects\eventos-app` | Expo SDK 55 + React Native |
| kasproduction/eventos-socket | `C:\laragon\www\eventos-socket` | Node.js + Socket.IO |
| kasproduction/eventos-kiosko | `C:\laragon\www\eventos-kiosko` | Kiosco QR check-in |

## Documentos

| Documento | Ruta | Contenido |
|-----------|------|-----------|
| **Pendientes** | `docs/PENDIENTES.md` | TODO lo que falta — la unica fuente de verdad |
| **Completado** | `docs/COMPLETADO.md` | Historial de todo lo hecho por area |
| **UI/UX + Landing** | `docs/ROADMAP-UIUX-LANDING.md` | Spec diseno: landing, estados evento, design system, pasos |
| **Web App** | `docs/WEB-APP-PLAN.md` | Spatial UI, W.0-W.12, stack Next.js |
| **Competitivo** | `docs/ANALISIS-COMPETITIVO.md` | Cisco $88K vs ICE360 $49M vs EventOS, pricing, escala |
| **White-Label** | `docs/WHITE-LABEL.md` | app.config.js, clients/, EAS build |
| **Seguridad** | `docs/FASE-SEGURIDAD.md` | Auditoria OWASP, SEC-1 a SEC-5, 42 tests |
| **Compliance** | `docs/COMPLIANCE-SEGURIDAD.md` | ISO 27001, Ley 1581, GDPR, cabeceras HTTP |
| **Alta Disponibilidad** | `docs/DISPONIBILIDAD-HA.md` | 2 VPS, PlanetScale, Upstash, Cloudflare, 99.99% |
| **QA Master** | `docs/QA-MASTER.md` | Barrido 60+ endpoints, 20 modulos, 3 roles |
| **QA Auth** | `docs/QA-AUTH-ONBOARDING.md` | 30+ escenarios auth/onboarding |
| **Bug Log** | `docs/BUG-LOG.md` | Bugs historicos BUG-001 a BUG-078 |
| **Documento maestro** | `EventOS_ClaudeCode_Prompt_v2.md` | Stack, modelos, API contracts, reglas de negocio |
| **Dev setup** | `EventOS_DevSetup.md` | Instrucciones de desarrollo local |
| **Roadmap historico** | `docs/ROADMAP-HISTORICO-v3.1.md` | Roadmap v3.1 completo (3,144 lineas) — checklists detallados de cada sesion, notas tecnicas, apendices A-J |

---

## Motor de modulos — como funciona (v4.1)

El motor de modulos es el corazon de EventOS. Determina que ve cada asistente en la app
segun **3 criterios que se evaluan en cascada**:

### Los 3 filtros

```
GET /api/v1/events/{id}/modules

Para cada modulo habilitado:
  1. ROLE        → ¿el rol del attendee esta en module.roles[]?
  2. PRESENCIA   → ¿cumple module.visibility_presence?
  3. TAGS        → ¿el attendee tiene algun tag de module.visibility_tags[]?

Si pasa los 3 → el modulo aparece en la app.
Si falla cualquiera → oculto.
```

### Modelo de datos

**Attendee** (campos relevantes):
- `role` — enum: `attendee`, `vendedor`, `admin`, `admin_*`
- `tags` — JSON array: `["vip", "prensa"]` o `[]`
- `checked_in_at` — null (remoto) o datetime (llego al venue)

**Module** (campos relevantes):
- `roles` — JSON array: `["attendee"]`, `["vendedor"]`, `["attendee", "vendedor"]`
- `visibility_presence` — enum: `all` | `checked_in` | `not_checked_in`
- `visibility_tags` — JSON array o null: `["vip"]`, `null` (todos)

### Casos de uso reales

| Modulo | roles | visibility_presence | visibility_tags | Quien lo ve |
|--------|-------|--------------------|-----------------| ------------|
| Agenda | attendee, vendedor | all | null | Todos |
| Streaming | attendee | all | null | Todos los asistentes |
| Mapa Venue | attendee | checked_in | null | Solo los que llegaron al venue |
| Zona VIP | attendee | all | ["vip"] | VIP siempre (incluso remoto) |
| Dress Code | attendee | checked_in | ["vip"] | VIP que llegaron al venue |
| Sala Prensa | attendee | all | ["prensa"] | Solo prensa |
| Info Stands | attendee | checked_in | null | Presentes en el venue |
| Mis Leads | vendedor | all | null | Solo vendedores |
| Mi QR | attendee | all | null | Todos (QR = identidad) |

### Flujo automatico: virtual → presencial

```
1. Maria se registra → role: attendee, tags: [], checked_in_at: null
2. Ve: Agenda, Streaming, Mi QR, Social, Networking (modules con presence=all)
3. NO ve: Mapa Venue (requiere checked_in)

4. Maria llega al evento → staff escanea su QR
5. Backend: checked_in_at = now()
6. Socket broadcast: attendee:checkin { attendee_id: 42 }
7. App de Maria: escucha, invalida query 'modules'
8. Mapa Venue aparece en 1-2 segundos — zero intervencion admin
```

### Flujo VIP pre-registrado

```
1. Admin sube CSV: nombre, email, tags: ["vip"]
2. Backend crea User + Attendee con tags ["vip"]
3. VIP abre app → ve Zona VIP (tag match), NO ve Dress Code (no checked_in)
4. VIP llega al venue → check-in → Dress Code aparece automaticamente
```

### Decision de diseno

> **No hay tipos de evento. No hay roles presencial/virtual.**
> Solo existe `attendee` con `tags` + `checked_in_at`.
> Los modulos son inteligentes: el admin los configura una vez
> y el sistema adapta la experiencia de cada persona automaticamente.

### Donde se configura

- **Filament > Modulos**: cada modulo tiene campos `Visibilidad por presencia` (Select) y `Visibilidad por tags` (TagsInput)
- **Filament > Asistentes**: campo `Tags` editable por asistente
- **CSV Import**: columna tags en import masivo
- **API**: `GET /events/{id}/modules` ya devuelve solo los modulos visibles para ese usuario

### Estado de implementacion — TODO COMPLETADO (2026-04-14)

- [x] Backend: migrations, modelos, API, Filament, 314 tests
- [x] App: layout unificado (merge presencial/virtual → tabs unico)
- [x] App: socket checkin:update → invalidar modules
- [x] Backend: CSV import con columna tags
- [x] Event lifecycle: 4 estados (registration/published/live/ended)
- [x] Countdown, InfoCard, Archive, About pre-evento
- [x] Modalidad badge (presencial/virtual/hibrido)

---

## Principio: dependencias progresivas

**No instalar todo al inicio.** Cada sesion instala solo lo que necesita.
Esto evita romper el proyecto con librerias que no se usan aun y hace
cada sesion mas facil de debuggear.

---

## Flujo git por sesion

```bash
# Inicio
git checkout develop && git pull origin develop
git checkout -b feature/sesion-XX-nombre

# Durante
# → implementar → probar → "confirmo, funciona" → Claude hace commit

# Al terminar la sesion
git checkout develop
git merge feature/sesion-XX-nombre
git push origin develop
```

**Formato de commits:** Conventional Commits
`feat:` / `fix:` / `chore:` / `test:` / `docs:` / `refactor:`

---

## Fase 0 — Setup ✅ (2026-03-28)

| Sesion | Feature | Deps instaladas |
|--------|---------|-----------------|
| 0.1 | Entorno de desarrollo (PHP 8.3, Redis, MySQL 8.4, Node 22) | — |
| 0.2 | Laravel 11 base (16 migraciones, seeders, Filament, Horizon, Telescope) | sanctum, spatie/permission, filament, horizon, telescope, sentry |
| 0.3 | Expo SDK 55 base (Router, MMKV, Zustand, TanStack Query, i18n) | expo-router, nativewind, reanimated, secure-store, mmkv, tanstack-query, zustand |
| 0.4 | Socket.IO base (Redis adapter DB 2, auth Sanctum, rooms, health) | socket.io, @socket.io/redis-adapter, ioredis, axios |

---

## Fase 1 — MVP funcional ✅ ~98% (2026-03-29 → 2026-04-07)

| Sesion | Feature | Fecha |
|--------|---------|-------|
| 1.1 | Auth + Roles + QR HMAC + Tracking (12 tests Pest) | 03-29 |
| 1.2 | Motor modulos dinamicos + cache Redis (23 tests) | 03-28 |
| 1.3a | Contenido backend: sessions, speakers, pages, announcements + HTMLPurifier | 03-28 |
| 1.3b | Contenido app: agenda, speakers, pages, anuncios + FlashList + expo-image | 03-28 |
| 1.4 | QR check-in + kiosco standalone + Socket.IO real-time | 03-29 |
| 1.5 | Leads vendedor: scanner QR, notas, tier, historial, export | 03-30 |
| 1.6 | Patrocinadores + stand teams + stand_members + lead_edits | 03-30 |
| 1.7 | Networking: solicitudes, aceptar/rechazar, contactos, directorio | 03-31 |
| 1.8 | Gestion usuarios + bans (motivo, expiracion, Filament) | 03-31 |
| 1.9 | Chat real-time por sesion (Socket.IO + Redis) + socket.io-client app | 04-01 |
| 1.10 | Encuestas en vivo (live_poll_questions, 4 tipos, slides) | 04-04 |
| 1.11 | Push notifications (Expo Push API, FCM v1) + expo-notifications | 04-04 |
| 1.12 | Tracks + session types | 04-04 |
| 1.13a | Emails automaticos: 11 mailables, BaseEventosMail, EmailService | 04-05 |
| 1.13b | SMTP propio por organizacion | 04-05 |
| 1.x | Upload imagenes / Cloudflare R2 + league/flysystem-aws-s3-v3 | 04-05 |
| 1.x | Banners: carrusel sponsors en Home | 04-05 |
| 1.x-A | Onboarding configurable: backend + app base | 04-05 |
| 1.x-B | Onboarding animaciones premium | 04-06 |
| 1.14 | Streaming nativo + Mi Agenda + react-native-webview | 04-06 |
| 1.15 | Q&A en vivo + moderacion Filament | 04-06 |
| 1.16 | Evaluacion sesiones (ratings, crystals) | 04-06 |
| fix | YouTube iframe + logica separada por tipo URL | 04-07 |
| 1.17 | Photobooth / Memorias (galeria moderada, likes) + expo-image-picker | 04-07 |
| 1.18 | Matchmaking por intereses (overlap, sugerencias) | 04-07 |
| 1.19 | Social wall (feed + posts + comments + likes) | 04-07 |
| 1.20 | Gamification 13 acciones + leaderboard | 04-07 |
| 1.21 | Passport stamps por lead scan | 04-07 |
| 1.22 | Registro personalizable + import/export + approval + deep link + consent | 04-07 |
| pulido | Tab vendedor unificado, modulos por rol, admin rediseno | 04-07 |
| stress | k6 + artillery scripts (ejecutado local, pendiente VPS Ubuntu) | 04-07 |

---

## Seguridad ✅ 80% (2026-04-07 → 2026-04-12)

| Bloque | Estado | Tests | Fecha |
|--------|--------|-------|-------|
| SEC-1: 3 criticos (socket room auth, XSS/HTMLPurifier, token expiration) | ✅ | 26 | 04-07 |
| SEC-2: 5 altos (security headers, CORS, HTTPS, security:check, .env.prod) | ✅ | 10 | 04-07 |
| SEC-3: Medios (lockout, rate limiting Redis, FormRequests) | ✅ parcial | 6 | 04-07 |
| SEC-3b.1: Token register → configurable | ✅ | — | 04-12 |
| SEC-3b.3: Middleware CheckBan server-side | ✅ | — | 04-12 |
| SEC-3b.5: Ban real-time via socket | ✅ | — | 04-12 |
| SEC-3b.2: Validar token al startup (GET /me) | ⏳ | — | — |
| SEC-3b.4: Middleware approval server-side | ⏳ | — | — |
| SEC-3.1: 2FA OTP | ⏳ | — | — |
| SEC-3.2: Device fingerprinting | ⏳ | — | — |
| SEC-4: Docker, server hardening, Cloudflare, backups | ⏳ | — | sesion deploy |
| SEC-5: SecurityLogger, Sentry, uptime | ⏳ | — | sesion deploy |

Total: 42 security tests, 314 tests backend, 0 TS errors.

---

## UI/UX Lumina Noir ✅ ~98% (2026-04-07 → 2026-04-12)

Detalle completo en `docs/ROADMAP-UIUX-LANDING.md` (Paso 5) y `docs/COMPLETADO.md`.

**Completado:**
Home, Agenda, Speakers, Streaming, Social, Sponsors, Profile, Encuestas, Chat, Mi QR, Gamificacion, Vendedor+Mi Stand+Leads, Networking, Pending-approval, Activate-account, Banned, ConnectionError.

**Transversales:** FloatingTabBar liquid glass, micro-interacciones (ScalePress, ContentFade, FadeInItem, AnimatedBadge, haptics), screen transitions, responsive 360dp, skeleton/empty states, returnKeyType 14 archivos, LuminaToast, Urbanist+PlusJakartaSans.

**Onboarding DaVinci:** Welcome (5 pills, 5 backgrounds), Auth (login inteligente 2 pasos), Photo, About (preview live), Interests (chips), Done (badge MiQR), gamificacion (AnimatedPts 80pts), configurable Filament (7 secciones, FormStep generico, colores master/slave, steps dinamicos).

**Moderacion chat:** Ban RT socket, CheckBan middleware, palabras bloqueadas, chat monitor HTML, slow mode, batching.

---

## Onboarding & Auth — pendientes detallados

| ID | Feature | Detalle |
|----|---------|---------|
| 1.x-E-B | FormStep tipos avanzados | searchable_select (paises), checkbox_group, date picker |
| ~~1.x-C~~ | ~~Roles asistente~~ | **ELIMINADO** — ya no existen roles presencial/virtual, reemplazado por tags+visibility |
| 1.x-D | Estados evento lifecycle | registration_only/published/live/ended + countdown DaVinci |
| ~~1.x-F~~ | ~~Registro cerrado~~ | **COMPLETADO** — whitelist emails, dominios corporativos, ambos. Toggle master + approval + access_code + invite_only compatible. 21 tests, 38 assertions. |
| 1.x-G | Registro por codigo | Admin genera codigos en Filament, campo validacion |
| ~~1.x-H~~ | ~~Staff invite~~ | **COMPLETADO** — QR + busqueda + email + link compartible, aceptacion, socket RT, landing web, mi-equipo pantalla, deep link |

---

## Features competitivos (del analisis 2026-04-09)

| ID | Feature | Prioridad | Detalle |
|----|---------|-----------|---------|
| 1.C1 | Analytics dashboard | MAXIMA | ROI, engagement, asistencia. Justifica precio. Ambos competidores lo tienen. |
| 1.C3 | ~~QR dinamico rotativo~~ | ✅ | HMAC-SHA256 60s, O(1). Completado 04-13. |
| 1.C5 | Calendar sync (.ics) | Media | Archivo .ics universal por sesion. QA-MASTER confirma endpoint funcional. |
| 1.23 | Permisos granulares Filament | Media | Spatie ya instalado, falta wiring. |
| 1.C2 | Wallet digital | Baja | Apple Wallet + Google Wallet. Post-venta. |
| 1.C4 | Digital signage | Baja | Pantallas venue. Base checki. Post-venta. |
| 1.C6 | Badge printing | Baja | Impresora termica. Add-on. Post-venta. |

---

## Fase 2 — Web app + Features complejos ⏳

Spec completa: `docs/WEB-APP-PLAN.md`

| Sesion | Feature |
|--------|---------|
| W.0–W.1 | Spatial UI System + Setup Next.js + Auth |
| W.2–W.4 | Home + Agenda + Streaming+Q&A+Chat (core virtual) |
| W.5–W.8 | Speakers + Social + Sponsors + Networking |
| W.9–W.12 | Encuestas+Gamification + Notificaciones + Sockets + Polish |

Estimacion: ~22-28 dias (~7 semanas).

### Otros features Fase 2

| Feature | Deps | Por que aplazado |
|---------|------|-----------------|
| Photo/Caption Contest | expo-image-picker | Depende de social wall |
| Video calls 1:1 (LiveKit) | livekit-client, @livekit/react-native-webrtc | Infra de media server, costo mensual |
| Proximity chat (spatial audio) | livekit-server-sdk | El mas complejo. Depende de web + LiveKit |
| Ruleta en vivo | socket.io (ya existe) | Depende de gamificacion completa |
| Sorteo en vivo (jackpot) | socket.io + /display/ | Requiere pantalla display dedicada |
| Juegos Unity en stands | Unity WebGL, socket.io | Desarrollo Unity aparte |
| Trivia live tipo Kahoot | Base encuestas (1.10) | Refactor a modo competitivo |
| Networking speed-dating | socket.io | Depende de networking |
| Subasta de puntos | socket.io | Depende de rewards |
| Donde esta el patrocinador | socket.io + display | Juego visual |
| Game Bridge (Unity ↔ App) | socket.io, Unity WebGL | Solo el bridge, juegos existen |
| Momentos en Vivo branded | socket.io, Filament | Reemplaza momentos hardcodeados |

---

## Fase 3 — SaaS + Monetizacion ⏳

| Sesion | Feature | Deps |
|--------|---------|------|
| 3.1 | Multi-tenant + aislamiento de recursos | — |
| 3.2 | Stripe + facturacion | laravel/cashier |
| 3.3 | Data export (Ley 1581/GDPR) | — |
| 3.4 | Juegos Unity + Socket.IO bridge | Unity WebGL |

Niveles de aislamiento incremental:
1. Queue isolation por org (Horizon supervisors)
2. Rate limiting por event_id
3. Redis databases separadas
4. Database read replicas
5. Container isolation Docker
6. Database por organizacion (maximo aislamiento)

---

## Deploy a produccion ⏳

### Arquitectura

```
Internet
   |
   +-- app.eventos.com (HTTPS)
   |     +-- Nginx → Laravel (PHP-FPM) + Queue Worker + Scheduler
   |
   +-- socket.eventos.com (WSS)
   |     +-- Socket.IO Node.js
   |
   +-- CDN / R2 — assets estaticos
```

### Docker Compose — 6 servicios

| Servicio | Imagen | Proposito |
|---|---|---|
| app | php:8.3-fpm + nginx | Laravel API + Filament |
| queue | mismo Dockerfile | php artisan queue:work |
| scheduler | mismo Dockerfile | php artisan schedule:run |
| socket | node:20-alpine | Socket.IO server |
| mysql | mysql:8 | Base de datos |
| redis | redis:7-alpine | Cache + queues |

**VPS:** Hetzner CX22 (2 vCPU, 4 GB RAM, ~$5/mes). Suficiente para 3-5 eventos simultaneos, ~500 asistentes c/u.

### Checklist pre-deploy

- [ ] docker-compose.yml con 6 servicios
- [ ] Dockerfile Laravel (PHP 8.3 + extensiones: pdo_mysql, redis, gd, zip)
- [ ] Dockerfile Socket.IO (Node 20 Alpine)
- [ ] Nginx config con SSL (Let's Encrypt via Certbot o Caddy)
- [ ] .env.production con secrets reales (nunca en git)
- [ ] php artisan migrate --force en deploy
- [ ] php artisan config:cache && route:cache en deploy
- [ ] Supervisor config para queue:work (auto-restart)
- [ ] GitHub Actions CI/CD: push a main → build Docker → deploy VPS
- [ ] EAS Build production profile (Android + iOS)
- [ ] FCM V1 key subida a Expo (ya hecho en S1.11)
- [ ] SHA-1 keystore produccion en Firebase Console
- [ ] Dominio + DNS apuntando al VPS
- [ ] Cloudflare R2 configurado

### Variables de entorno produccion

```env
# Backend .env
APP_ENV=production
APP_KEY=                    # php artisan key:generate
DB_HOST=mysql               # nombre del servicio Docker
REDIS_HOST=redis
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis
EXPO_API_URL=https://exp.host/--/api/v2

# App
EXPO_PUBLIC_API_URL=https://api.eventos.com
EXPO_PUBLIC_SOCKET_URL=wss://socket.eventos.com
```

---

## Dependencias por sesion

### Backend (Laravel)

| Sesion | Paquete |
|--------|---------|
| 0.2 | sanctum, spatie/permission, filament, horizon, telescope, sentry |
| 1.3 | ezyang/htmlpurifier |
| 1.11 | expo-notifications (FCM via Expo Push API) |
| 1.x (uploads) | league/flysystem-aws-s3-v3 (R2) |
| F2.3 | livekit-server-sdk _(Fase 2)_ |
| 3.2 | laravel/cashier |

### App (Expo)

| Sesion | Paquete |
|--------|---------|
| 0.3 | expo-router, nativewind, reanimated, secure-store, mmkv, tanstack-query, zustand, i18n |
| 1.3 | expo-image, expo-file-system, @shopify/flash-list |
| 1.4 | expo-camera, expo-keep-awake |
| 1.9 | socket.io-client |
| 1.11 | expo-notifications |
| 1.14 | react-native-webview |
| 1.17 | expo-image-picker |
| F2.3 | @livekit/react-native-webrtc _(Fase 2)_ |

### Socket.IO (Node.js)

| Sesion | Paquete |
|--------|---------|
| 0.4 | socket.io, @socket.io/redis-adapter, ioredis, axios, dotenv |

### Load Testing

| Sesion | Herramienta |
|--------|-------------|
| 1.x (Stress) | k6 (open source, scripts JS) |

---

## Orden de implementacion

```
FASE 0:   0.1 → 0.2 → 0.3 → 0.4                           ✅

FASE 1:   1.1 → 1.2 → ... → 1.22 + 1.x extras              ✅
          + Pulido funcional + Stress testing                  ✅

SEGURIDAD: SEC-1/2/3 + 3b parcial                             ✅ 80%

UI/UX:    Barrido visual Lumina Noir + onboarding DaVinci      ✅ ~98%
          + Moderacion + QA Master                             ✅

PENDIENTE:
  → Features competitivos (1.C1 analytics, 1.C3 QR, 1.C5 .ics, 1.23 permisos)
  → Web app (W.0–W.12)
  → Deploy (Docker + VPS + CI/CD + EAS Build)
  → Fase 2 (cuando haya cliente con requerimiento web/video)
  → Fase 3 (cuando haya segundo cliente o monetizacion)
```

---

## Nota operativa — Procesos en desarrollo (Windows)

En Linux (produccion) cron maneja el scheduler automaticamente. En Windows local hay que correr dos procesos manualmente:

```bash
# Terminal 1 — procesa jobs en cola (push, exports, etc.)
php artisan queue:work --timeout=60

# Terminal 2 — dispara jobs programados cada minuto
php artisan schedule:work
```

**Sin queue:work:** notificaciones push se encolan pero nunca se envian.
**Sin schedule:work:** CheckExpoReceiptsJob no corre → "Entregadas/Abiertas" queda en 0.

En produccion: Supervisor (queue:work) + crontab (schedule:run).

---

## Clientes en pipeline

### Bancolombia (enterprise, alta prioridad)
- **Contexto:** Reunión 2026-04-14. Competencia presentó webapp (UI pobre, promete 60 días).
- **Requerimiento:** Flujo completo webapp + landing + experiencia asistente.
- **Formato especial:** Silent Disco (un salón, audífonos, charlas simultáneas).
- **Escala:** Multi-país (Colombia + Panamá), multi-ciudad (Bogotá + Medellín).
- **Ventaja nuestra:** App nativa + real-time + gamificación + Lumina Noir. Backend 100% listo.
- **Riesgo:** Web app no existe aún. Es el blocker principal.
- **Acción:** Web app es ahora prioridad máxima. Silent Disco como diferenciador.

### Eventos Efectivos (cliente ancla original)
- **Evento:** Septiembre 2026.
- **Estado:** Producto listo, falta pitch.

---

## Timeline — ajustada con Bancolombia

| Mes | Objetivo | Entregable |
|-----|----------|------------|
| **Abril** | Silent Disco spec + web app setup + features competitivos | Backend silent disco + Next.js base |
| **Mayo** | Web app core (landing + registro + agenda + streaming) | Demo web funcional |
| **Junio** | Web app completa + deploy + demo Bancolombia | Producto desplegado, web + app |
| **Julio** | Pitch Eventos Efectivos + onboarding Bancolombia | White-label, datos reales |
| **Agosto** | Pruebas + ajustes ambos clientes | Stress test, feedback |
| **Septiembre** | Eventos en vivo | Casos de estudio reales |

### Estrategia competitiva

> La competencia tiene track record (ya hicieron eventos). Nosotros no.
> Pero su producto es feo y genérico. El nuestro es 10x mejor — aún nadie lo vio.
> Si el cliente vive una experiencia mediocre con ellos, la próxima vez busca algo mejor.
> Mejor llegar después con algo impecable que llegar a medias y dar mala primera impresión.
> El Mission Control + Silent Disco + Lumina Noir + real-time es el knockout.
> Construir para ganar cualquier deal, no solo este.

### Filtro de decisiones (actualizado)

> "¿Este feature me acerca a cerrar Bancolombia o al pitch de Eventos Efectivos?"
>
> Si → hacerlo. No → posponerlo.

---

_EventOS Roadmap v4.2 — Kasproduction_
_14 abril 2026_
