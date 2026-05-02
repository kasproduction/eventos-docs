# EventOS — Roadmap

_Plan de trabajo: fases, sesiones, dependencias, timeline_
_Actualizado: 2026-05-02 | v5.4 — W.1B Backend cerrado (magic link + login slides + 10 Pest), F4-F9 webapp DESBLOQUEADAS_

---

## Estado rapido

| Fase | Estado | Detalle |
|------|--------|---------|
| **Fase 0** — Setup | ✅ Completa | 4 sesiones, 2026-03-28 |
| **Fase 1** — MVP funcional | ✅ 100% | 22+ sesiones + extras. Tags, lifecycle, FAQ, soporte, registro avanzado |
| **UI/UX Lumina Noir** | ✅ 100% | Barrido completo, todas las pantallas |
| **UI/UX Lumina Lux v2** | ✅ 100% | Light mode "The Gallery", ~85 archivos, dark islands, session types |
| **Mission Control v4** | ✅ 100% | Display LED, metricas RT, moderacion, Q&A proyectable, herramientas moderador, responsive |
| **Live Moments** | ✅ 100% | Ruleta + Sorteo + Trivia Kahoot-style, branded, performance 10K, 41 tests |
| **Event Pulse** | ✅ 100% | Dashboard RT standalone, Noir/Lux, 7 secciones, responsive, 20 tests |
| **Concurso Fotos** | ✅ 100% | Contest lifecycle, Golden Ticket generico, anti-gaming, 36 tests |
| **Data Center** | ✅ 100% | SPA standalone analytics, 9 tabs, 44 exports CSV/XLSX, comparativa periodos, goals, scheduled, comparador A/B, multi-evento, tema Lux toggle, infra prod lista |
| **Room Check-in** | ✅ 100% | Kiosk + Staff + Silent disco + attendance tracking |
| **Webhooks** | ✅ 100% | 5 fases, 24 tests, integracion partners |
| **Seguridad** | ✅ 90% | SEC-1/2/3/3b/6 completo. Pendiente: 2FA, device, infra (SEC-4/5) |
| **Onboarding DaVinci** | ✅ | 6 steps, configurable, campos unificados, depends_on avanzado |
| **Moderacion** | ✅ | Ban RT, palabras bloqueadas, chat monitor, slow mode, pin Twitch |
| **Optimistic UI** | ✅ Audit | 30 acciones auditadas, 10 con optimistic, haptics, retry API. Plan listo |
| **QA** | ✅ | 150+ endpoints, 20 modulos, 582+ tests backend, 1664+ assertions |
| **Deploy** | ⏳ | Docker + DO sao1 + CI/CD. Arquitectura HA definida |
| **Fase 2** — Web app | 🚧 W.1 F0-F3 + W.1B backend cerrados | F4-F9 webapp **DESBLOQUEADAS**. Backend `eventos-backend` branch `feature/magic-link-auth` con magic link endpoints + tabla event_login_slides + Filament + 10 Pest passing (commit ef24003). Webapp Next 16 + Tailwind 4 + i18n + tokens en repo `eventos-web` |
| **Fase 3** — SaaS | ⏳ | Multi-tenant, monetizacion |

**Que falta:** ver `docs/living/PENDIENTES.md`
**Que se hizo:** ver `docs/living/COMPLETADO.md`
**Arquitectura:** ver `docs/infra/DISPONIBILIDAD-HA.md` (DO sao1 consolidado)
**Stress test:** ver `docs/infra/PLAN-STRESS-TESTDO.md` (v2.1, DO sao1, 9 tests)
**Optimistic UI:** ver `docs/analysis/OPTIMISTIC-UI-PLAN.md` (30 acciones auditadas, plan 3 semanas)
**Webapp:** ver `docs/webapp/PLAN.md` (master) + `DECISIONS.md` + `AUTH-SPEC.md` + 18 roadmaps W.X

---

## Repositorios

| Repo | Ruta local | Stack |
|------|-----------|-------|
| kasproduction/eventos-backend | `C:\laragon\www\eventos-backend` | Laravel 11 + Filament |
| kasproduction/eventos-app | `C:\Users\Kasproduction\Projects\eventos-app` | Expo SDK 55 + React Native |
| kasproduction/eventos-socket | `C:\laragon\www\eventos-socket` | Node.js + Socket.IO |
| kasproduction/eventos-kiosko | `C:\laragon\www\eventos-kiosko` | Kiosco QR check-in |

## Documentos

> **Reorganizacion 2026-05-01:** docs/ ahora estructurado en categorias (living/roadmaps/analysis/infra/briefs/archive/webapp). Indice maestro en `docs/_index.md`.

### Indices maestros (entry points)

| Documento | Ruta | Contenido |
|-----------|------|-----------|
| **Indice docs** | `docs/_index.md` | Links a toda la doc por categoria |
| **Indice design** | `design/_index.md` | Estructura visual + estado por feature |
| **Documento maestro** | `EventOS_ClaudeCode_Prompt_v2.md` | Stack, modelos, API contracts, reglas de negocio |
| **Dev setup** | `EventOS_DevSetup.md` | Instrucciones de desarrollo local |

### `docs/living/` — Vivos

| Documento | Ruta | Contenido |
|-----------|------|-----------|
| **Pendientes** | `docs/living/PENDIENTES.md` | TODO lo que falta — la unica fuente de verdad |
| **Completado** | `docs/living/COMPLETADO.md` | Historial de todo lo hecho por area |
| **Bug Log** | `docs/living/BUG-LOG.md` | Bugs historicos BUG-001 a BUG-305 |
| **QA Master** | `docs/living/QA-MASTER.md` | Barrido endpoints, 20 modulos, 3 roles |
| **Modulos** | `docs/living/MODULOS.md` | 15 modulos + 6 sistemas + admin |

### `docs/webapp/` — Plan webapp completo (NUEVO)

| Documento | Ruta | Contenido |
|-----------|------|-----------|
| **Master plan** | `docs/webapp/PLAN.md` | Vision, stack, scope, 16 gaps, ~132h estimado |
| **Decisiones** | `docs/webapp/DECISIONS.md` | 20 ADRs (auth, deploy, PWA, i18n, streaming, responsive) |
| **Auth spec** | `docs/webapp/AUTH-SPEC.md` | Magic link + Bearer Sanctum + refresh + multi-device |
| **Responsive** | `docs/webapp/RESPONSIVE-SPEC.md` | 3 disenios dedicados por viewport |
| **Design system** | `docs/webapp/DESIGN-SYSTEM.md` | Tokens Lumina Noir + Lux portados |
| **W.0-W.17** | `docs/webapp/W.X-*.md` | 18 roadmaps modulares DaVinci |

### `docs/roadmaps/` — Plans por feature

| Documento | Estado |
|-----------|--------|
| `docs/roadmaps/ROADMAP-DATA-CENTER.md` | Cerrado |
| `docs/roadmaps/ROADMAP-EVENT-PULSE.md` | Cerrado |
| `docs/roadmaps/ROADMAP-FILAMENT-PULIDO.md` | Pendiente |
| `docs/roadmaps/ROADMAP-KIOSK.md` | En progreso |
| `docs/roadmaps/ROADMAP-LIGHTMODE.md` | Cerrado |
| `docs/roadmaps/ROADMAP-LIVE-MOMENTS.md` | Cerrado |
| `docs/roadmaps/ROADMAP-LUX-V2.md` | Cerrado |
| `docs/roadmaps/ROADMAP-MISSION-CONTROL.md` | Cerrado |
| `docs/roadmaps/ROADMAP-RECAP.md` | Implementado (validacion visual pendiente) |
| `docs/roadmaps/ROADMAP-UIUX-LANDING.md` | Pendiente |
| `docs/roadmaps/ROADMAP-WEBHOOKS.md` | Cerrado |

### `docs/analysis/` — Auditorias

| Documento | Contenido |
|-----------|-----------|
| `docs/analysis/ANALISIS-COMPETITIVO.md` | Cisco $88K vs ICE360 $49M vs EventOS |
| `docs/analysis/ANALISIS-LIGHTMODE.md` | Audit pre-implementacion light mode |
| `docs/analysis/CODEBASE-MAP.md` | 150+ endpoints, socket events, observers |
| `docs/analysis/GAPS-ANALYSIS.md` | Dedup socket, coordinacion REST+socket |
| `docs/analysis/OPTIMISTIC-UI-AUDIT.md` | 30 acciones auditadas |
| `docs/analysis/OPTIMISTIC-UI-PLAN.md` | Plan 3 semanas, 9 PRs |

### `docs/infra/` — Deploy + seguridad + stress

| Documento | Contenido |
|-----------|-----------|
| `docs/infra/COMPLIANCE-SEGURIDAD.md` | ISO 27001, Ley 1581, GDPR |
| `docs/infra/FASE-SEGURIDAD.md` | OWASP, SEC-1 a SEC-6 |
| `docs/infra/DISPONIBILIDAD-HA.md` | 2 Droplets DO sao1, 99.9% uptime |
| `docs/infra/PLAN-STRESS-TESTDO.md` | Stress test v2.1, 9 tests |
| `docs/infra/WHITE-LABEL.md` | App config dinamico multi-cliente |

### `docs/briefs/` — One-shots

| Documento | Contenido |
|-----------|-----------|
| `docs/briefs/BRIEF-CLAUDE-CODE-OPTIMISTIC-UI.md` | Brief original audit |
| `docs/briefs/PLAN-TAGS-MODULOS.md` | Plan tags + visibilidad |
| `docs/briefs/QA-AUTH-ONBOARDING.md` | 30+ escenarios |

### `docs/archive/` — Legacy preservado

| Documento | Razon |
|-----------|-------|
| `docs/archive/ROADMAP-HISTORICO-v3.1.md` | Reemplazado por v5.3 (este doc) |
| `docs/archive/WEB-APP-PLAN.md` | Stub legacy → redirige a `docs/webapp/PLAN.md` |

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

## Principio: pausas de revision periodica

Cada 5-7 dias de desarrollo intenso, hacer una sesion de revision que cubra:

1. **QA-MASTER** — actualizar con tests y endpoints nuevos. En 8 dias se acumularon 220 tests sin documentar.
2. **Roadmap + PENDIENTES** — reflejar lo completado. Sin esto el roadmap miente sobre el estado real.
3. **Docs de arquitectura** — verificar que reflejan decisiones actuales (ej: HA doc decia PlanetScale cuando ya habiamos decidido DO).
4. **Audit tecnico rapido** — buscar bugs silenciosos, deuda tecnica, patrones inconsistentes. La sesion 04-25 encontro: bug Q&A (201 fake), zero retry en API, zero haptics en 30 acciones.
5. **Memorias de sesion** — guardar contexto para futuras conversaciones.

Esta practica se demostro valiosa el 04-25: en una sesion de revision se corrigieron 3 bugs, se actualizaron 6 documentos, se auditaron 3 repos completos, y se alinearon decisiones de arquitectura que estaban dispersas en 3 documentos diferentes.

**Frecuencia recomendada:** cada viernes o al cerrar un bloque de features (P0, P1, etc).

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
| 1.x-D | Event lifecycle: 4 estados, countdown, archivo, about, modalidad | 04-14 |
| 1.x-E | Campos unificados registration_fields, depends_on avanzado | 04-15 |
| 1.x-F | Registro cerrado: email whitelist, dominios, both. 21 tests | 04-15 |
| 1.x-G | Registro por codigo de acceso + verificacion identidad CSV | 04-14 |
| 1.x-H | Staff invite: QR+email+link, aceptacion, socket RT, landing | 04-15 |
| 1.x-I | FAQ asistente: orbe, categorias, accordion, soporte, push | 04-15 |
| post | Encuesta post-evento: scope post_event, auto-activacion, CSV | 04-15 |
| 1.x-J | Componentes base GlassCard/GlassButton + Theme tokens | 04-16 |
| 1.x-K | Mensaje anclado chat tipo Twitch (pin/unpin RT, Redis TTL) | 04-16 |
| 1.x-L | Calendar .ics en email bienvenida | 04-16 |
| LUX | Light mode Lux v2 "The Gallery" completo (12 fases, ~85 archivos) | 04-17 |
| types | Session types configurables desde Filament (colores API) | 04-17 |
| MC | Mission Control v4: display LED + metricas RT + moderacion + Q&A + herramientas | 04-17→04-19 |
| 1.23 | Permisos granulares Filament (41 recursos, HasResourcePermission, canAccessPanel) | 04-19 |
| perf | Auditoria polling: 3 refetchInterval eliminados, invalidacion targeted por socket | 04-19 |
| fix | Health check +Queue, reload→onboarding, push ban lifecycle, session detail UI | 04-19 |
| stats | Stand stats/contacts + Mi Stand simplificado + CSV con resumen | 04-20 |
| perf | Polling eliminado (3 refetchInterval), invalidacion targeted socket | 04-20 |
| sess | Session attendance tracking (socket→Redis→cron DB), SessionStatsService, CSV queue export | 04-20 |
| 1.23 | Permisos granulares Filament (41 recursos, canAccessPanel) | 04-20 |
| conf | Silent disco toggle + attendance gamification config + certificate criteria | 04-20 |

---

## Seguridad ✅ 90% (2026-04-07 → 2026-04-15)

| Bloque | Estado | Tests | Fecha |
|--------|--------|-------|-------|
| SEC-1: 3 criticos (socket room auth, XSS/HTMLPurifier, token expiration) | ✅ | 26 | 04-07 |
| SEC-2: 5 altos (security headers, CORS, HTTPS, security:check, .env.prod) | ✅ | 10 | 04-07 |
| SEC-3: Medios (lockout, rate limiting Redis, FormRequests) | ✅ parcial | 6 | 04-07 |
| SEC-3b.1: Token register → configurable | ✅ | — | 04-12 |
| SEC-3b.3: Middleware CheckBan server-side | ✅ | — | 04-12 |
| SEC-3b.5: Ban real-time via socket | ✅ | — | 04-12 |
| SEC-3b.2: Validar token al startup (GET /me) | ✅ | — | 04-13 |
| SEC-3b.4: Middleware approval server-side (CheckApproval) | ✅ | — | 04-13 |
| SEC-6.1: Rate limit networking (100/evento, 30/dia) | ✅ | 3 | 04-15 |
| SEC-6.2: Rate limit escritura (posts, comments, Q&A, support, photos, stories, leads) | ✅ | 31 | 04-15 |
| SEC-3.1: 2FA OTP | ⏳ | — | — |
| SEC-3.2: Device fingerprinting | ⏳ | — | — |
| SEC-4: Docker, server hardening, Cloudflare, backups | ⏳ | — | sesion deploy |
| SEC-5: SecurityLogger, Sentry, uptime | ⏳ | — | sesion deploy |

Total: 75+ security tests, 488+ tests backend, 1168+ assertions, 0 TS errors.

---

## UI/UX Lumina Noir ✅ 100% (2026-04-07 → 2026-04-12)

Detalle completo en `docs/ROADMAP-UIUX-LANDING.md` (Paso 5) y `docs/COMPLETADO.md`.

**Completado:**
Home, Agenda, Speakers, Streaming, Social, Sponsors, Profile, Encuestas, Chat, Mi QR, Gamificacion, Vendedor+Mi Stand+Leads, Networking, Pending-approval, Activate-account, Banned, ConnectionError.

**Transversales:** FloatingTabBar liquid glass, micro-interacciones (ScalePress, ContentFade, FadeInItem, AnimatedBadge, haptics), screen transitions, responsive 360dp, skeleton/empty states, returnKeyType 14 archivos, LuminaToast, Urbanist+PlusJakartaSans.

**Onboarding DaVinci:** Welcome (5 pills, 5 backgrounds), Auth (login inteligente 2 pasos), Photo, About (preview live), Interests (chips), Done (badge MiQR), gamificacion (AnimatedPts 80pts), configurable Filament (7 secciones, FormStep generico, colores master/slave, steps dinamicos).

**Moderacion chat:** Ban RT socket, CheckBan middleware, palabras bloqueadas, chat monitor HTML, slow mode, batching.

---

## UI/UX Lumina Lux v2 "The Gallery" ✅ 100% (2026-04-16 → 2026-04-17)

Detalle completo en `docs/ROADMAP-LUX-V2.md` y `docs/COMPLETADO.md`.

**Sistema:** theme-lux.ts tokens completos, useTheme() en ~85 archivos, accent dinamico (Noir=#FFFFFF, Lux=#1A1A1A), session types configurables desde Filament.

**12 fases completadas:** Tokens, Tab Bar, Onboarding, Home, Agenda, Session Detail, Speakers, Mi QR, Social, Sponsors, Networking, Leaderboard, About, Perfil.

**Dark islands:** HappeningNow, GamificationHud, Mi QR badge, Scanner, Streaming — siempre Noir (#0a0a0a solido).

**NativeWind migrado:** 12 archivos (173 className → StyleSheet). FormStep/DynamicField sin accent. Backend theme toggle en Filament.

---

## Mission Control v4 ✅ 100% (2026-04-17 → 2026-04-19)

Detalle completo en `docs/ROADMAP-MISSION-CONTROL.md` y `docs/COMPLETADO.md`.

**Core (04-17):** Control bar (Chat/Q&A/Polls/Custom), toggles RT, HMAC access, app reacciona a toggles, Filament integration.

**Display LED (04-19):** Pagina publica Lumina Noir `/display/session/{id}?token=HMAC`, socket RT project/stop, render polls (barras+ranking+counter), Q&A (slide-in), fade entre proyecciones, standby breathing.

**Metricas RT (04-19):** Audiencia (fetchSockets, excluye admin), MPM ventana 60s, engagement %, Redis INCR chat count.

**Moderacion (04-19):** Open text batch approve, cola presentacion 1.8s, Q&A proyectable.

**Herramientas moderador (04-19):** Reloj real, countdown sesion (verde/amber/rojo), mini agenda sidebar, tareas localStorage, responsive drawer <1200px.

**Testing:** DisplayTestSeeder, SimulateVotes command, simulate-audience.cjs 50 conexiones, 13 bugs (BUG-135 a BUG-147).

---

## Onboarding & Auth — pendientes detallados

| ID | Feature | Detalle |
|----|---------|---------|
| 1.x-E-B | FormStep tipos avanzados | searchable_select (paises), checkbox_group, date picker |
| ~~1.x-C~~ | ~~Roles asistente~~ | **ELIMINADO** — ya no existen roles presencial/virtual, reemplazado por tags+visibility |
| 1.x-D | Estados evento lifecycle | registration_only/published/live/ended + countdown DaVinci |
| ~~1.x-F~~ | ~~Registro cerrado~~ | **COMPLETADO 04-15** — whitelist emails, dominios corporativos, ambos. Toggle master + approval + access_code + invite_only compatible. 21 tests, 38 assertions. |
| ~~1.x-G~~ | ~~Registro por codigo~~ | **COMPLETADO 04-14** — AccessCode model, Filament CRUD + lote, toggle, campo AuthStep, tracking usos, verificacion identidad CSV. |
| ~~1.x-H~~ | ~~Staff invite~~ | **COMPLETADO 04-15** — QR + busqueda + email + link compartible, aceptacion, socket RT, landing web, mi-equipo pantalla, deep link. 23 tests. |

---

## Features competitivos (del analisis 2026-04-09)

| ID | Feature | Prioridad | Detalle |
|----|---------|-----------|---------|
| 1.C1 | Analytics dashboard | MAXIMA | ROI, engagement, asistencia. Justifica precio. Ambos competidores lo tienen. |
| 1.C3 | ~~QR dinamico rotativo~~ | ✅ | HMAC-SHA256 60s, O(1). Completado 04-13. |
| 1.C5 | Calendar sync (.ics) | Media | Archivo .ics universal por sesion. QA-MASTER confirma endpoint funcional. |
| 1.23 | ~~Permisos granulares Filament~~ | ✅ | HasResourcePermission trait, 41 recursos, canAccessPanel. Completado 04-19. |
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

> Arquitectura migrada a DigitalOcean sao1 consolidado (2026-04-25).
> Ver `docs/DISPONIBILIDAD-HA.md` para detalle completo y `docs/PLAN-STRESS-TESTDO.md` para validacion.

### Arquitectura HA (DO sao1 consolidado)

```
Cloudflare (DNS + WAF + DDoS + CDN + LB)
   |
   +-- api.eventos.com (proxy a origins DO)
   |     +-- Droplet-1 DO sao1 (4 vCPU, 8 GB) — Nginx + Laravel + Socket.IO
   |     +-- Droplet-2 DO sao1 (4 vCPU, 8 GB) — Nginx + Laravel + Socket.IO
   |           |
   |           +-- VPC privada DO (< 1ms RTT):
   |                 +-- DO Managed MySQL sao1 (1GB + read replica)
   |                 +-- DO Managed Redis sao1 (1GB, HA)
   |
   +-- Cloudflare R2 — storage (egress gratis)
   +-- Droplet-3 (opcional) — Worker headless exports, lee de read replica
```

### Por que DO sao1 y no Hetzner+PlanetScale+Upstash

| Decision | Razon |
|----------|-------|
| DO en lugar de Hetzner | Audiencia 100% Latam. Hetzner no tiene region Latam. RTT Bogota: ~80ms vs ~150ms |
| DO Managed MySQL | VPC privada < 1ms vs PlanetScale remoto 80-150ms. Pricing flat |
| DO Managed Redis | VPC privada, no TLS overhead, pricing flat |
| R2 se mantiene | Egress gratis. DO Spaces no tiene region sao1 |
| Cloudflare adelante | WAF + DDoS que DO LB no tiene |

### Docker Compose por Droplet — 3 servicios

| Servicio | Imagen | Proposito |
|---|---|---|
| app | php:8.3-fpm + nginx | Laravel API + Filament + Queue Worker + Scheduler |
| socket | node:20-alpine | Socket.IO server |

**NO hay MySQL ni Redis local** — son DO Managed services en VPC privada.

### Costo mensual

| Concepto | Costo |
|---------|-------|
| Droplet-1 sao1 (4 vCPU, 8 GB) | $48 |
| Droplet-2 sao1 (4 vCPU, 8 GB) | $48 |
| DO Managed MySQL 1GB + read replica | $30 |
| DO Managed Redis 1GB (HA) | $15 |
| Cloudflare Pro + LB | $26 |
| R2 storage | ~$1 |
| **Total** | **~$168/mes** |

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

COMPLETADO 04-15:
  → Campos unificados, FAQ+soporte, staff invite, registro cerrado
  → Login lockout, encuesta post-evento, push navigation, dev build
  → SEC-6.2 rate limits, push reminders configurables

COMPLETADO 04-16 a 04-17:
  → Light mode Lux v2 "The Gallery" (12 fases, ~85 archivos)
  → Session types configurables, .ics en email, mensaje anclado chat
  → NativeWind → StyleSheet (12 archivos), componentes base

COMPLETADO 04-18 a 04-19:
  → Mission Control v4 (display LED, metricas RT, moderacion, Q&A proyectable)
  → Herramientas moderador (reloj, countdown, tareas, mini agenda, responsive)
  → 13 bugs MC resueltos (BUG-135 a BUG-147)
  → 488+ tests, 1168+ assertions

COMPLETADO 04-19 (quick wins + bugs + rendimiento):
  → Health check: endpoint /api/v1/health ahora verifica DB + Redis + Queue
  → Permisos Filament: HasResourcePermission trait en 41 recursos, canAccessPanel
    gate (solo super_admin/org_admin/event_admin/moderator entran al panel),
    10 permisos mapeados por recurso, super_admin bypasea todo
  → Bug fix: reload Expo → onboarding. Guard isHydrated en (app)/_layout.tsx
    antes del check de token (race condition SecureStore async)
  → Bug fix: push ban sin sesion. App: guard onboarding_seen en useNotifications
    antes de registrar push token. Backend: BanController verifica activated_at
    antes de enviar push. NotificationController filtra whereDoesntHave('activeBan')
  → Bug fix: session detail UI hardcodeada. GlassCard, GlassButton, surface tokens
    reemplazan rgba/hex hardcodeados
  → Auditoria polling: eliminado refetchInterval de encuestas (30s, redundante con
    socket poll:new/poll:closed), gamification (30s+15s), passport (15s)
  → Invalidacion targeted: gamification y passport usan broadcastToAttendee()
    (socket solo al usuario afectado, no broadcast a 10K). Leaderboard usa
    staleTime 60s + refetchOnWindowFocus (max 1 req/min por usuario que mire
    la pantalla). InvalidationService::broadcastToAttendee() via /internal/emit-to-user.
    Cero thundering herd a escala.
  → ENTITY_KEYS ampliado: gamification → ['my-points'], passport → ['my-passport']
  → Eliminados de pendientes: Branded QR, Crop circular (nice-to-have innecesarios)

COMPLETADO 04-20 (stand stats + contacts + QA):
  → Stand stats: GET /me/stand/stats — leads, views, favorites, contacts, stamps,
    trivia, by_tier, by_member, top_services. Todo con tablas existentes, cero migraciones
  → Stand contacts: GET /me/stand/contacts — solicitudes de contacto con attendee
    info completa (foto, email, phone, company, job_title, servicios, mensaje)
  → App stand-stats.tsx: pantalla engagement unificada, tier bars, ranking equipo,
    servicios solicitados, export con BottomSheet, pull-to-refresh
  → App stand-contacts.tsx: inbox solicitudes con acciones (Llamar/Email/WhatsApp)
  → Mi Stand simplificado: 3 stats (Estadisticas/Hoy/Equipo) + hero + FAB.
    Eliminados: leads recientes, boton ver todos, boton exportar (redundantes)
  → CSV export mejorado: header con resumen stats antes de tabla de leads
  → StandStatsSeeder: 5 visitors con fotos, phones, leads, views, favs, contacts
  → 13 tests stand stats, 526 total, 1318 assertions

COMPLETADO 04-20b (session stats + attendance):
  → Tabla session_attendances: source (app/web/kiosko/manual), joined_at, left_at,
    duration_seconds. Config evento: silent_disco, attendance points, certificate criteria
  → SessionStatsService centralizado: attendance, chat, Q&A, polls, ratings,
    engagement score 0-100, activity timeline, attendance detail por fuente
  → Socket tracking: Redis SADD/HSET en join:session, SREM en leave:session.
    Cron FlushSessionAttendanceJob cada 60s Redis→DB (batch upsert)
  → AwardSessionAttendancePointsJob: puntos por duracion al cerrar sesion
  → ExportSessionStatsJob: CSV en queue con notificacion Filament (campana+descargar)
  → API: GET /sessions/{id}/stats + /viewers + /export (Event Pulse ready)
  → Filament: ViewSessionStats pagina resumen + boton export en tabla sesiones
  → GamificationSettings: toggle silent disco + config asistencia/certificados
  → SessionStatsSeeder: 50 users simulados, 537+ tests, 1377+ assertions
  → Pendiente: stress test 10K en VPS (requiere deploy)

COMPLETADO 04-20 a 04-21 (room check-in + kiosk + staff):
  → Room check-in completo: Fases 0-4, occupancy RT, silent disco
  → Kiosk Lumina Noir: USB scanner, cache, flujo optimista, offline queue
  → Staff app: asignacion, scan batch, rooms, reassign, socket RT
  → MC Control: toggles live-config, silent disco, attendance checks
  → Session lifecycle bugs: 8 criticos resueltos (cascadas, Carbon, revert)
  → Webhooks: 5 fases, 24 tests, attendee.registered/approved/checked_in/updated/cancelled

COMPLETADO 04-21 a 04-23 (Live Moments completo):
  → Live Moments F0-F5: Ruleta + Sorteo + Trivia Kahoot-style
  → Sorteo Ceremony: GSAP, Golden Ticket claim_code, pantalla display
  → Trivia: 4 estados, speed bonus, leaderboard RT, 10 rondas
  → Performance 10K: throttle game broadcasts, indices, cache pool
  → Platinum Gold: paleta #B5A68B unificada
  → 41 tests, 172 assertions

COMPLETADO 04-23 a 04-24 (Event Pulse completo):
  → Event Pulse RT: 7 secciones (checkins, rooms, leads, networking, social, leaderboard, ratings)
  → Noir/Lux responsive, active users RT, moments timeline
  → Rooms.pulse aislamiento: 200K msgs/sec → 25 msgs/sec
  → Bootstrap endpoint agregado
  → 20 tests, 79 assertions, 30 bugs corregidos

COMPLETADO 04-24 (Concurso fotos + Golden Ticket):
  → Contest lifecycle: toggle/horario, 1 entry por attendee, anti-gaming
  → Golden Ticket generico desacoplado de sorteo
  → 36 tests, 108 assertions, 10 bugs

COMPLETADO 04-25 (Optimistic UI audit + fixes):
  → Audit completo: 30 acciones del usuario mapeadas (3 repos, 150+ endpoints, socket events)
  → 4 documentos: CODEBASE-MAP, OPTIMISTIC-UI-AUDIT, GAPS-ANALYSIS, OPTIMISTIC-UI-PLAN
  → Bug fix: Q&A blocked words retornaba 201 fake → ahora 422 con error code
  → Retry automatico API: network errors + 502/503/504, 2 reintentos con backoff+jitter
  → Haptic feedback: 7 hooks, 9 puntos de insercion (favoritos, likes, upvotes, votes, networking)
  → Arquitectura HA migrada de Hetzner+PlanetScale+Upstash a DO sao1 consolidado
  → Stress test plan v2.1 con DO sao1, 9 tests formales, thresholds enterprise
  → 582+ tests, 1664+ assertions

PENDIENTE:
  → Features competitivos (1.C1 analytics dashboard)
  → Web app (W.0-W.12) — PRIORIDAD por Bancolombia
  → Deploy (Docker + DO sao1 + CI/CD + EAS Build)
  → Seguridad restante (2FA, device fingerprinting, SEC-4/5 infra)
  → Optimistic UI implementacion (chat tempId, post-pendientes)
  → Stress test 10K (post-deploy)
  → Fase 2 features (video calls, spatial audio)
  → Fase 3 (multi-tenant, monetizacion)
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

## Timeline — ajustada post-planeacion webapp (2026-05-01)

| Mes | Objetivo | Entregable |
|-----|----------|------------|
| **Abril** (hecho) | Diferenciadores + audit + Recap + Data Center + Filament BUG-268 + planeacion webapp | Live Moments, Event Pulse, Concurso Fotos, Golden Ticket, Optimistic UI audit, Kiosk, Room Check-in, Webhooks, Mission Control v4, Recap v6 disenio aprobado, Data Center 9 tabs/44 exports, BUG-268 cerrado, planeacion webapp 18 modulos. 582+ tests |
| **Mayo** | Webapp cimientos + core | **W.1 F0-F3 cerradas** (scaffold + tokens + shadcn + i18n, 4.5h, repo `eventos-web`). **W.1B backend** (~4h: magic-link endpoints + `event_login_slides` tabla + Mailable + Pest). **W.1 F4-F9** webapp (~5.5h: login slideshow v7 aprobado + Tier 1+2 mejoras + bottom sheet adaptativo). Despues W.0 Spatial + W.2 Home + W.3 Agenda + W.4 Streaming. Deploy staging DO sao1 |
| **Junio** | Webapp completa + stress test | W.5-W.17 (resto de modulos) + W.12 Polish + PWA. Stress test 10K (9 tests). Optimistic UI chat. ~86h restantes |
| **Julio** | Demo Bancolombia + pitch Eventos Efectivos | Producto desplegado web + app. Dry run 1 con cliente |
| **Agosto** | Onboarding clientes + fixes | Dry run 2. Fix rondas. Freeze semana -2 |
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

_EventOS Roadmap v5.4 — Kasproduction_
_2 mayo 2026_
_Cambios v5.3→v5.4 (2026-05-02b): W.1B Backend cerrado en branch `feature/magic-link-auth`. Migration magic_link_tokens (TTL 15min, SHA-256, single-use). Endpoints POST /auth/magic-link (anti-enumeration, rate limit 3/hora) + POST /auth/verify-magic-link (codes token_invalid/used/expired). MagicLinkMail extends BaseEventosMail customizable Filament. Login slideshow feature nuevo (ADR-021): tabla event_login_slides + LoginSlideResource (drag reorder, has_overlay_text toggle) + endpoint publico GET /events/{slug}/login-slides cache 5min + Observer invalidation. Endpoint publico GET /events/by-slug/{slug} con live_status computado (upcoming/live_today/live_now/ended). organizer_logo_url + organizer_name campos nuevos en events (Tier 2 #8). 10/10 Pest tests passing. Commits: ef24003 backend + 5d5e25d cleanup Recap pendiente_
_Cambios v5.2→v5.3 (2026-05-02): Webapp W.1 F0-F3 cerradas en repo `eventos-web` (Next 16 + Tailwind 4 + TS strict + tokens Lumina Noir/Lux portados + shadcn 2.x + i18n next-intl 3 locales + bottom sheet adaptativo). Login design phase completo (7 iteraciones HTML demo, v7 final aprobado en `design/features/webapp/Login/iteraciones/`). ADR-021 login slideshow feature nuevo (NO event_highlights), ADR-022 5 innovaciones DaVinci, ADR-023 bloqueo F4-F9 hasta backend, ADR-024 mobile bottom sheet + Tier 1+2 (12 mejoras: cached email, mailcheck, ARIA live, doble logo organizador, video slot, accent dinamico extendido, network banner, preload). Roadmap nuevo `W.1-backend-magic-link.md` (~4h sesion proxima). 4 commits eventos-web (ba2fc24, 811b7dd, e425570, ffd8589) + 7 commits APP EVENTOS docs/design_
_Cambios v5.1→v5.2: Webapp planeacion completa (18 modulos W.0-W.17, ~132h, 23 docs en `docs/webapp/`), repo reorganizado por categoria (docs/ y design/), Recap v6 disenio aprobado e implementado, BUG-268 Filament searchable cerrado_
_Cambios v5.0→v5.1 (2026-04-26): Data Center cerrado (9 tabs, 44 exports, SPA standalone), 19 bugs nuevos (BUG-287 a BUG-305), 29 tests Data Center_
_Cambios v4.5→v5.0 (2026-04-25): Live Moments/Event Pulse/Concurso completados, audit optimistic UI (4 docs), stack DO sao1, stress test v2.1, haptics+retry+bug fix Q&A, 582+ tests_
