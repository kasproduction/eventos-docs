# EventOS — Plan de trabajo definitivo

_Fases, sesiones, dependencias progresivas y definicion de completado_

---

## Indice rapido

| Seccion | Linea aprox | Contenido |
|---------|-------------|-----------|
| **Estado del proyecto** | ~30 | Tabla resumen de TODAS las sesiones con estado (✅/⏳) |
| **Fase 0 — Setup** | ~130 | 0.1–0.4: repos, backend, app, socket |
| **Fase 1 — Funcional** | ~260 | 1.1–1.13: auth, modulos, contenido, QR, leads, sponsors, networking, chat, encuestas, push, tracks, emails |
| **Onboarding** | ~1100 | 1.x-A/B: slides, animaciones, survey post-login |
| **Sesiones 1.14–1.22** | ~1250 | Streaming, Q&A, ratings, photobooth, matchmaking, social wall, gamification, passport, registro |
| **Pulido funcional** | ~1935 | Items pre-deploy: tab vendedor, modulos rol, admin rediseno |
| **Stress testing** | ~1965 | k6 scripts, escenarios, metricas |
| **Sesion UI (Lumina Noir)** | ~2005 | Barrido visual: ~80% completado, pantallas hechas vs pendientes |
| **Fase 2** | ~2040 | Web Next.js, video calls, proximity chat, sorteos, juegos |
| **Fase 3** | ~2060 | SaaS multi-tenant, monetizacion |

### Repositorios y rutas locales

| Repo | Ruta local | Stack |
|------|-----------|-------|
| kasproduction/eventos-backend | `C:\laragon\www\eventos-backend` | Laravel 11 + Filament |
| kasproduction/eventos-app | `C:\Users\Kasproduction\Projects\eventos-app` | Expo SDK 55 + React Native |
| kasproduction/eventos-socket | `C:\laragon\www\eventos-socket` | Node.js + Socket.IO |
| kasproduction/eventos-kiosko | `C:\laragon\www\eventos-kiosko` | Kiosco QR check-in |

### Documentos relacionados

| Documento | Ruta | Contenido |
|-----------|------|-----------|
| **UI/UX + Landing** | `docs/ROADMAP-UIUX-LANDING.md` | Plan completo: landing web, estados evento, registro, seguridad, design system, PASO 1-6 con checklist |
| **Seguridad** | `docs/FASE-SEGURIDAD.md` | Auditoria OWASP, 42 tests, 11 fixes aplicados |
| **Compliance** | `docs/COMPLIANCE-SEGURIDAD.md` | ISO 27001, Ley 1581, GDPR, cabeceras HTTP |
| **Alta Disponibilidad** | `docs/DISPONIBILIDAD-HA.md` | 2 VPS, PlanetScale, Upstash, Cloudflare, 99.99% uptime |
| **Documento maestro** | `EventOS_ClaudeCode_Prompt_v2.md` | Stack, modelos, API contracts, reglas de negocio |
| **Dev setup** | `EventOS_DevSetup.md` | Instrucciones de desarrollo local |

### Apendices (al final de este archivo)

| Apendice | Contenido |
|----------|-----------|
| **C** | UI/UX Premium Dark Theme — fundamentos implementados (2026-04-07) |
| **D (HA)** | Alta Disponibilidad — arquitectura, costos, deploy strategy |
| **D (Sponsors)** | Sponsors UI/UX Premium — Brand Wall + Brand Profile (2026-04-09) |
| **E** | Analisis Competitivo — Cisco $88K vs ICE360 $49M vs EventOS |
| **F** | Web App — experiencia virtual completa Next.js (W.1–W.12) |
| **G** | White-Label + Rebranding — app.config.js, clients/, EAS build |
| **H** | Timeline Cliente Ancla — Eventos Efectivos, septiembre 2026 |

---

## Principio: dependencias progresivas

**No instalar todo al inicio.** Cada sesión instala solo lo que necesita.
Esto evita romper el proyecto con librerías que no se usan aún y hace
cada sesión más fácil de debuggear.

---

## Resumen de fases

| Fase       | Nombre                        | Sesiones                                      |
| ---------- | ----------------------------- | --------------------------------------------- |
| **Fase 0** | Setup e infraestructura       | 0.1 → 0.4                                     |
| **Fase 1** | Todo lo funcional             | 1.1 → 1.26 + 1.x (Storage, Banners, Onboarding, Pulido, Stress Test) |
| **Sesión UI** | Barrido visual completo    | Una sola sesión al final de Fase 1            |
| **Fase 2** | Features complejas aplazadas  | Web Next.js, Video calls LiveKit, Proximity chat |
| **Fase 3** | SaaS + Monetización           | 3.1 → 3.4 _(aplazada)_                        |

**Estrategia:** Primero TODO funcional completo (Fase 1 + sesiones ex-Fase2), luego stress testing para validar arquitectura, y solo entonces el barrido de UI. Así el diseño final se aplica sobre features estables y una arquitectura validada. No se rediseña dos veces.

**Milestone MVP lanzable:** Fase 0 + Fase 1 completa + Stress Test + UI sweep + deploy.

## Estado del proyecto (al 2026-04-10, Roadmap v3.0)

| Sesión | Feature | Estado |
|--------|---------|--------|
| 0.1–0.4 | Setup completo | ✅ |
| 1.1 | Auth + Roles + Tracking | ✅ |
| 1.2 | Motor de módulos dinámicos | ✅ |
| 1.3a/b | Contenido (backend + app) | ✅ |
| 1.4 | QR + Check-in + Kiosco | ✅ |
| 1.5 | Leads (vendedor scanner) | ✅ |
| 1.6 | Patrocinadores + Stand Teams | ✅ |
| 1.7 | Networking con solicitudes | ✅ |
| 1.8 | Gestión usuarios + Bans | ✅ |
| 1.9 | Chat en tiempo real por sesión | ✅ |
| 1.10 | Encuestas en vivo | ✅ (2026-04-04) |
| 1.11 | Push notifications | ✅ (2026-04-04) |
| 1.12 | Tracks + Session types | ✅ |
| 1.13a | Emails automáticos + editor de plantillas | ✅ (2026-04-05) |
| 1.13b | SMTP propio por organización | ✅ (2026-04-05) |
| 1.x Storage | Upload de imágenes / Cloudflare R2 | ✅ (2026-04-05) |
| 1.x Banners | Carrusel de sponsors en pantalla Inicio | ✅ (2026-04-05) |
| 1.x-A | Onboarding configurable — Backend + App base | ✅ (2026-04-05) |
| 1.x-B | Onboarding — Animaciones premium | ✅ (2026-04-06) |
| **1.14** | **Streaming nativo + Mi Agenda** | ✅ (2026-04-06) — push reminders pendiente probar en dev build |
| **1.15** | **Preguntas al speaker (Q&A)** | ✅ (2026-04-06) |
| **bugs** | **Fix favoritos Mi Agenda + Q&A moderación Filament** | ✅ (2026-04-06) |
| **1.16** | **Evaluación de sesiones** | ✅ (2026-04-06) |
| **fix streaming** | **YouTube fix — `react-native-youtube-iframe` + lógica separada por tipo de URL** | ✅ (2026-04-07) |
| **1.17** | **Photobooth / Memorias** | ✅ (2026-04-07) |
| **1.18** | **Matchmaking por intereses** | ✅ (2026-04-07) |
| **1.19** | **Social wall** | ✅ (2026-04-07) |
| **1.20** | **Gamification + Leaderboard** | ✅ (2026-04-07) |
| **1.21** | **Passport Contest (stamps por lead scan)** | ✅ (2026-04-07) |
| **1.22** | **Registro personalizable + Import/Export** | ✅ (2026-04-07) |
| **RT Invalidation** | **Real-time sync Admin→App (socket + focusManager + jitter)** | ✅ (2026-04-09) — 5 entidades, 4 capas, probado en vivo |
| **Push Invalidation** | **Push notification auto-invalida caches al llegar** | ⏳ Codigo listo, pendiente dev build para probar |
| **Competitive** | **Analisis competitivo (Cisco $88K USD vs ICE360 $49M COP vs EventOS)** | ✅ (2026-04-09) — gaps identificados, 6 nuevas sesiones, pricing, escalabilidad validada |
| **1.23** | **Permisos granulares Filament (roles admin)** | ⏳ Pendiente |
| **1.x Rewards** | **Redencion de puntos (kiosko de premios)** | ⏳ Catalogo, QR temporal, staff escanea, descuenta puntos. Cierra el loop de gamificacion. |
| **Nice to have** | **Light mode (tema claro)** | ⏳ Requiere refactor de colores hardcoded a theme provider. Sesion dedicada si cliente lo pide. |
| ~~1.22 old~~ | ~~Floor plan del venue~~ | Movido a Fase 3 |
| **— Nuevos features (identificados en analisis competitivo 2026-04-09)** | | |
| **1.C1** | **Analytics dashboard (Filament + API)** | ⏳ PRIORIDAD MAXIMA — ROI, engagement, asistencia, sponsors, leads. Justifica el precio ante el cliente. Ambos competidores lo tienen. |
| **1.C2** | **Wallet digital (Apple Wallet + Google Wallet)** | ⏳ Badge digital con QR dinamico. .pkpass (Apple) + JWT (Google Wallet API). Reemplaza badge printing fisico como default. Diferenciador vs competencia. |
| **1.C3** | **QR dinamico rotativo** | ⏳ QR en app rota cada 30-60s con HMAC-SHA256 + timestamp + user_id. Imposible de clonar. Reemplaza reconocimiento facial ($5M COP en competencia). Base HMAC ya existe. |
| **1.C4** | **Digital signage (pantallas venue)** | ⏳ Integrar concepto de proyecto checki: pantallas con agenda en vivo, sesion actual/siguiente, override imagen/video, leaderboard gamificacion, social wall. Checki tenia 106 archivos de referencia (commit 5e2f867). |
| **1.C5** | **Calendar sync (.ics)** | ⏳ Boton "Agregar al calendario" por sesion. Genera archivo .ics con titulo, hora, salon, recordatorio. Compatible Google Calendar, Outlook, Apple Calendar. Sin OAuth. |
| **1.C6** | **Badge printing fisico (add-on)** | ⏳ Impresora termica (Zebra/Brother) + credencial personalizada. Add-on para clientes que lo pidan. Logica de check-in ya existe. Complementa wallet digital. |
| **— PDFs & Analytics (aplazados al final, después de todos los features)** | | |
| **1.P1** | **Certificados PDF** | ⏳ Aplazado |
| **1.P2** | **Reporte post-evento PDF** | ⏳ Aplazado |
| **1.P3** | **Analytics avanzado** | ⏳ Absorbido por 1.C1 — analytics dashboard es ahora prioridad maxima |
| **1.P4** | **Reports exportables detallados** | ⏳ Aplazado |
| **1.x Pulido** | **Pulido funcional (mejoras pre-deploy)** | ✅ (2026-04-07) |
| **1.x Stress** | **Stress & Load Testing (k6 + artillery)** | ✅ Scripts listos (2026-04-07) — test local ejecutado, pendiente VPS Ubuntu |
| **— Fase Seguridad (auditoría + hardening pre-deploy)** | | |
| **SEC-1** | **3 críticos: room auth socket, XSS/HTMLPurifier, token expiration** | ✅ (2026-04-07) — 26 tests |
| **SEC-2** | **5 altos: security headers, CORS, HTTPS, security:check, .env.prod** | ✅ (2026-04-07) — 10 tests |
| **SEC-3** | **Medios: account lockout, Redis rate limiting, FormRequests** | ✅ parcial (2026-04-07) — 6 tests. 2FA y device fingerprint pendientes |
| **SEC-4** | **Infraestructura: Docker, server hardening, Cloudflare, backups** | ⏳ Pendiente (sesión deploy) |
| **SEC-5** | **Monitoreo: SecurityLogger, Sentry, uptime** | ⏳ Pendiente (sesión deploy) |
| **— Landing + UI/UX** | | |
| **Landing** | **Landing web premium (hero video, speakers, agenda, sponsors, registro)** | ⏳ Planificado — docs/ROADMAP-UIUX-LANDING.md |
| **UI Sponsors** | **Brand Wall + Brand Profile premium** | ✅ (2026-04-09) — grid por tier, living shuffle, contact form, trivia, 15 tests |
| **UI Profile** | **Profile screen Lumina Noir + editar datos + avatar beam** | ✅ (2026-04-09) — beam Ocean, foto editable, redes sociales, stats, modal editar, pull to refresh |
| **Profile API** | **GET/PUT /me/profile + POST/DELETE /me/photo + social fields** | ✅ (2026-04-09) — ProfileController, migration twitter/instagram/website, beam fallback todos los endpoints |
| **RT Branding** | **Real-time branding invalidation (EventObserver + socket)** | ✅ (2026-04-09) — cambios de config/branding llegan via socket, refetch on reconnect |
| **UI Fixes** | **Mi Agenda sin flecha, Home pull to refresh, expo-image SVG social** | ✅ (2026-04-09) |
| **Responsive** | **Audit completo 360dp — 31 archivos, layout proporcional, SafeArea fix** | ✅ (2026-04-10) — skeletons, leads header, login scroll, ModuleMenu/HappeningNow/HomeHero proporcionales, 12 pantallas SafeArea, day pills centrados |
| **UX Polish** | **PollSlides Lumina Noir, chat emojis animados+cooldown, Enter=enviar, logout BottomSheet** | ✅ (2026-04-10) — rediseno encuesta, Q&A error handling, console.log cleanup, #1a1919→#141414, tab bar ajustado |
| **Testing** | **Emuladores ZTE 360dp + Medium Phone 411dp configurados, TestStreamingSeeder** | ✅ (2026-04-10) |
| **UI Mi QR** | **Badge digital: tab real, RGB wave pastel, evento+QR+identidad, fullscreen tap, wallet ready** | ✅ (2026-04-10) — proporcional 360-411dp, dashed lines, role pill accent |
| **UI Gamification** | **GamificationHud rediseno: teal/cyan sci-fi, progress segmentada, RGB wave 2px, prioridad carousel** | ✅ (2026-04-10) — rank 36px, puntos 28px, slide 2do posicion, 10s duracion |
| **UI Desafio** | **Pantalla unificada: hero HUD + ranking + retos + pasaporte + premios, portal cards, BottomSheets** | ✅ (2026-04-10) — confetti, FadeInSection, motivational tip, returnKeyType 14 archivos |
| **Rewards** | **Sistema de premios canjeables: catalogo, QR temporal HMAC 5min, staff confirma, expire+refund** | ✅ (2026-04-10) — 9 tests, Filament CRUD, addon toggle, RewardSeeder |
| **— Web App (asistente virtual — Next.js)** | | |
| **W.1** | **Setup Next.js + Tailwind + shadcn/ui + auth + layout** | ⏳ Pendiente |
| **W.2** | **Home (hero, happening now, highlights, carousel sponsors)** | ⏳ Pendiente |
| **W.3** | **Agenda (lista, filtros, tracks, favoritos, detalle sesion)** | ⏳ Pendiente |
| **W.4** | **Streaming + Q&A + Chat (por sesion, experiencia virtual core)** | ⏳ Pendiente |
| **W.5** | **Speakers (lista, perfil, ratings)** | ⏳ Pendiente |
| **W.6** | **Social wall (feed, posts, comentarios, likes, fotos)** | ⏳ Pendiente |
| **W.7** | **Sponsors (brand wall, perfil, lead capture, trivia)** | ⏳ Pendiente |
| **W.8** | **Networking (perfiles, solicitudes, chat)** | ⏳ Pendiente |
| **W.9** | **Encuestas + Gamification + Leaderboard** | ⏳ Pendiente |
| **W.10** | **Notificaciones web + perfil + settings** | ⏳ Pendiente |
| **W.11** | **Socket.IO integration (real-time en toda la web app)** | ⏳ Pendiente |
| **W.12** | **Responsive polish + Lumina Noir theme + dark mode** | ⏳ Pendiente |
| **— White-label + Deploy** | | |
| **WL.1** | **Migrar app.json → app.config.js dinamico + estructura clients/** | ⏳ Pendiente |
| **Sesión UI** | **Barrido visual Lumina Noir (10 pantallas + responsive + transversales)** | 🔶 ~80% (2026-04-10) — quedan Networking/Gamification/Matchmaking/Auth/Onboarding + skeleton/empty states |
| **Deploy** | **Docker + VPS + CI/CD (backend + web + app)** | ⏳ Pendiente |

---

## Flujo git por sesión

```bash
# Inicio
git checkout develop && git pull origin develop
git checkout -b feature/sesion-XX-nombre

# Durante
# → implementar → probar → "confirmo, funciona" → Claude hace commit

# Al terminar la sesión
git checkout develop
git merge feature/sesion-XX-nombre
git push origin develop
```

**Formato de commits:** Conventional Commits
`feat:` / `fix:` / `chore:` / `test:` / `docs:` / `refactor:`

---

## ─────────────────────────────────────────

## FASE 0 — Setup e infraestructura

## ─────────────────────────────────────────

### Sesión 0.1 — Entorno de desarrollo ✅ COMPLETADA (2026-03-28)

**Branch:** ninguno (tareas locales)
**Repos:** —

**Objetivo:** Entorno 100% funcional antes de tocar código.

**Checklist:**

- [x] PHP 8.3.26 activo en Laragon + extensiones (redis, gd, zip, fileinfo) ← nota: PHP 8.3 en vez de 8.2, funciona igual
- [x] Redis corriendo → `redis-cli ping` → PONG
- [x] MySQL 8.4.3 corriendo → bases `eventos_db` y `eventos_testing` creadas (utf8mb4_unicode_ci)
- [x] Node.js v22.12.0 → `node -v` v22.x.x
- [x] Git 2.47.1 configurado
- [x] Composer 2.8.4 disponible
- [x] EAS CLI 18.4.0 instalado globalmente
- [x] Repo creado en GitHub: kasproduction/eventos-backend (eventos-app y eventos-socket pendientes para sus sesiones)
- [ ] VS Code + extensiones instaladas ← pendiente verificar
- [x] .gitignore global configurado (C:\Users\Kasproduction\.gitignore_global)

**Definición de completado:** EventOS_DevSetup.md PASO 13 pasa completo sin errores.

---

### Sesión 0.2 — Laravel base ✅ COMPLETADA (2026-03-28)

**Branch:** `feature/s02-laravel-base`
**Repo:** `kasproduction/eventos-backend`

**Objetivo:** Laravel 11 con dependencias core, migraciones y seeders. Sin nada extra todavía.

**Dependencias instaladas:**

- laravel/sanctum ^4.3
- spatie/laravel-permission ^6.25
- filament/filament ^3.2
- laravel/horizon ^5.45 (con --ignore-platform-req=ext-pcntl — normal en Windows, corre en Linux en prod)
- laravel/telescope ^5.19 (dev only)
- sentry/sentry-laravel ^4.24

**Checklist:**

- [x] `composer create-project laravel/laravel:^11.0 eventos-backend` → Laravel 11.31
- [x] `.env`: DB=eventos_db, Redis, APP_URL=http://eventos-backend.test, APP_ENV=local, timezone=America/Bogota
- [x] `config/hashing.php` → driver: argon2id
- [x] Paquetes instalados (ver arriba)
- [x] Comandos artisan ejecutados (key:generate, storage:link, vendor:publish, filament:install, horizon:install, telescope:install)
- [x] 16 migraciones creadas y ejecutadas sin errores (schema completo del documento maestro)
- [x] Seeders: 1 org, 3 eventos (real/demo/borrador), 5 usuarios por rol, attendees, module_templates, módulos
- [x] CORS configurado (permite Expo dev localhost:8081, localhost:19006)
- [x] Rate limiting: 5/min login, 60/min API, 10/min upload
- [x] Estructura: `Controllers/Api/V1/`, `Resources/V1/`, `Requests/`, `Services/`
- [x] `GET /api/v1/health` → `{"status":"ok","checks":{"database":"ok","redis":"ok"}}` ✅
- [x] `GET /api/v1/version` → 200 ✅
- [x] Horizon en `/horizon` funcionando ✅
- [x] Telescope en `/telescope` funcionando (local only) ✅
- [x] `php artisan test` → 2 passed ✅

**Notas técnicas:**

- Tabla de sessions renombrada `event_sessions` para no colisionar con la tabla `sessions` de Laravel (sesiones HTTP)
- Telescope solo se registra cuando APP_ENV=local (via AppServiceProvider)
- `platform-check: false` en composer.json para ignorar ext-pcntl/posix en Windows

**Definición de completado:** `/api/v1/health` → 200 ✅. Todas las migraciones ejecutadas ✅. Seeders sin errores ✅.

---

### Sesión 0.3 — Expo base ✅ COMPLETADA (2026-03-28)

**Branch:** `main` (commit directo — repo nuevo)
**Repo:** `kasproduction/eventos-app`

**Objetivo:** Proyecto Expo SDK 53 con routing, storage, i18n y TypeScript listos. Sin pantallas de features todavía.

**Versiones reales instaladas:**

- expo@53.0.27, expo-router@5.1.11
- nativewind@4.2.3, tailwindcss, react-native-reanimated@3.17.4
- expo-secure-store@14.2.4, react-native-mmkv@4.3.0
- @tanstack/react-query@5.95.2, zustand@5.0.12
- expo-localization@16.1.6 (SDK 53 compatible — no existe dist-tag sdk-53, expo install lo resuelve auto)
- i18n-js@4.5.3, expo-build-properties@0.14.8

> ← Nota: SDK 53 (no 52) porque es el latest stable al momento de la sesión.
> FlashList, expo-image, expo-camera, expo-notifications, expo-file-system,
> socket.io-client → se instalan en la sesión que los necesita.

**Checklist:**

- [x] `app.json` correcto (Bundle ID com.kasproduction.eventos, iOS 15+, Android API 29+, New Architecture enabled)
- [x] `eas.json` (development / preview / production)
- [x] Archivos de config: `babel.config.js`, `metro.config.js`, `tailwind.config.js`, `global.css`, `nativewind-env.d.ts`
- [x] Estructura Expo Router:
  - `app/(auth)/` — login (placeholder)
  - `app/(app)/(presencial)/(tabs)/` — inicio, agenda, networking, perfil
  - `app/(app)/(virtual)/(tabs)/` — inicio, agenda, perfil
  - `app/(app)/(vendedor)/(tabs)/` — leads, escáner, perfil
- [x] Auth store con Zustand (token en expo-secure-store, nunca AsyncStorage) con hidratación al arranque
- [x] MMKV store para cache (react-native-mmkv v4 API: `createMMKV()` en vez de `new MMKV()`)
- [x] i18n: `locales/es.json` y `locales/en.json` con keys base (common, auth, tabs, event, agenda, networking, profile)
- [x] Hook `useTracker` implementado (stub con console.log en **DEV**)
- [x] SplashLoader con loading bar animada (Reanimated, se muestra mientras se hidrata el auth store)
- [x] TypeScript strict mode sin errores (`npx tsc --noEmit` pasa limpio)
- [ ] ESLint + Prettier ← pendiente (no bloqueante para siguiente sesión)
- [ ] Development build con EAS en dispositivo físico ← pendiente (requiere cuenta EAS configurada)

**Notas técnicas:**

- `expo-localization` no tiene dist-tag sdk-52 ni sdk-53 — usar `npx expo install` para que resuelva la versión compatible automáticamente
- `react-native-mmkv` v4 cambió API: ya no existe `new MMKV()`, ahora es `createMMKV({ id })` y `.remove(key)` en vez de `.delete(key)`
- `index.ts` debe ser `import 'expo-router/entry'` para que Expo Router funcione como entry point
- App.tsx eliminado — Expo Router maneja el entry point

**Definición de completado:** TypeScript compila sin errores ✅. Estructura de rutas lista ✅. Pusheado a GitHub ✅.

---

### Sesión 0.4 — Socket.IO base ✅ COMPLETADA (2026-03-28)

**Branch:** `main` (repo nuevo)
**Repo:** `kasproduction/eventos-socket`

**Objetivo:** Servidor Node.js con Socket.IO + Redis adapter autenticando con Sanctum.

**Versiones reales instaladas:**

- socket.io@4.8.3, @socket.io/redis-adapter@8.3.0
- ioredis@5.10.1, axios@1.14.0, dotenv@17.3.1
- typescript@6.0.2, ts-node@10.9.2, nodemon@3.1.14

**Checklist:**

- [x] `.env` / `.env.example`: PORT, REDIS_HOST/PORT/DB, LARAVEL_API_URL, ALLOWED_ORIGINS
- [x] `src/index.ts` — servidor con Redis adapter (ioredis DB 2)
- [x] `src/config.ts` — config centralizada desde env vars
- [x] `src/types.ts` — ServerToClientEvents, ClientToServerEvents, SocketData, payloads
- [x] `src/auth.ts` — valida token Sanctum via GET /api/v1/auth/me
- [x] `src/rooms.ts` — helpers Rooms.event(), Rooms.session(), Rooms.chat()
- [x] Auth middleware en socket handshake (Bearer token en socket.handshake.auth.token)
- [x] Rooms base: `event:{id}`, `session:{id}`, `chat:{eventId}`
- [x] Eventos del cliente: join:event, join:session, leave:session, chat:send, poll:vote
- [x] Eventos del servidor: announcement:new, session:started, session:ended, poll:new, poll:updated, chat:message, attendee:checkin
- [x] `GET /health` → `{"status":"ok","uptime":...}` ✅
- [x] `pm2 ecosystem.config.js` configurado
- [x] `npm run dev` levanta, Redis adapter listo, servidor escuchando puerto 3001 ✅
- [x] TypeScript strict sin errores ✅
- [x] Pusheado a GitHub ✅

**Notas técnicas:**

- ioredis v5 usa `new Redis(options)` (no `createClient` de ioredis v4/redis v4)
- La autenticación de socket retornará 401 hasta que /api/v1/auth/me se implemente en Sesión 1.1 — es correcto
- Redis DB 2 reservada para pub/sub de Socket.IO (DB 0 = cache, DB 1 = queues)

**Definición de completado:** Servidor levanta sin errores. Health endpoint responde. TypeScript pasa. ✅

---

## ─────────────────────────────────────────

## FASE 1 — MVP funcional

## ─────────────────────────────────────────

### Sesión 1.1 — Auth + Roles + Tracking ✅ COMPLETADA (2026-03-29)

**Branch:** `feature/s11-auth-roles` → mergeado a main
**Repos:** `kasproduction/eventos-backend` + `kasproduction/eventos-app`
**Nuevas dependencias:** pestphp/pest@^3.0 + pestphp/pest-plugin-laravel@^3.0

**Backend:**

- [x] `POST /api/v1/auth/register` — crea user, attendee, qr_token, consent_log + campos dinámicos de registration_fields
- [x] `POST /api/v1/auth/login` — token Sanctum 30 días + attendee por event_slug
- [x] `GET /api/v1/auth/me` — user + attendee del evento activo más reciente
- [x] `POST /api/v1/auth/logout` — revoca token actual
- [x] `POST /api/v1/auth/forgot-password` — sin enumeración de emails
- [x] `POST /api/v1/auth/reset-password` — revoca todos los tokens al resetear
- [x] `POST /api/v1/auth/verify-email` — reenvía verificación
- [x] Roles Spatie: super_admin, org_admin, event_admin, moderator, attendee, speaker, vendor + permisos
- [x] QR token HMAC-SHA256(attendee_id|event_id|timestamp, APP_QR_SECRET)
- [x] `consent_logs` al registrar (Ley 1581)
- [x] `POST /api/v1/track` — activity_log append-only, 204 siempre
- [x] Filament UserResource con gestión de roles Spatie
- [x] `HASH_DRIVER` leeenv var (argon2id en prod, bcrypt en tests vía phpunit.xml)

**App:**

- [x] Screen Login — conectado a API, redirect por rol (presencial/virtual/vendedor)
- [x] Screen Registro — todos los campos, consentimiento Ley 1581
- [x] Screen Forgot password — anti-enumeración, confirm screen
- [x] `lib/api.ts` — cliente HTTP genérico con Bearer token y ApiError tipado
- [x] `lib/authApi.ts` — login, register, logout, forgotPassword, resetPassword, me
- [x] Auth store clearAuth llama /logout en background

**Tests Pest (12/12 ✅):**

- [x] Login exitoso devuelve token
- [x] Login fallido → 422
- [x] Cuenta desactivada → 403
- [x] Logout revoca token (verificado en DB)
- [x] GET /me devuelve usuario autenticado
- [x] Registro exitoso: user + attendee + qr_token + consent_log + rol Spatie
- [x] Registro duplicado → 422
- [x] Registro sin consentimiento → 422
- [x] QR token es 64 chars hexadecimal
- [x] super_admin tiene todos los permisos
- [x] vendor solo puede scan-qr, no manage-events
- [x] attendee puede view-events, no manage-events

**Notas técnicas:**

- `HASH_DRIVER=bcrypt` en phpunit.xml para tests rápidos. Producción sigue usando argon2id
- `UserFactory` usa plaintext `'password' => 'password'` — el cast `'hashed'` maneja el hasheo
- Pest v3 (no v4 — requiere PHPUnit 12 que rompe Laravel 11)
- `EXPO_PUBLIC_API_URL` env var para la URL de la API en la app

**Definición de completado:** `POST /login` → token → `GET /me` → 200 con user + attendee ✅

---

### Sesión 1.2 — Motor de módulos ✅ (2026-03-28)

**Branch:** `feature/s12-modulos` — mergeado a main
**Repos:** `eventos-backend` + `eventos-app`
**Nuevas dependencias:** ninguna

**Backend:**

- [x] `GET /api/v1/events/{id}/modules` — filtra por rol del usuario, cache Redis 30s
- [x] Seeder module_templates (Congreso / Feria / Lanzamiento) — ya existía de S0.2
- [x] Filament resource módulos (activar/desactivar, editar config JSON)
- [x] ModuleObserver + SendSilentPushJob — push silenciosa al cambiar módulo
- [x] OrganizationFactory + EventFactory para tests

**App:**

- [x] Home screen: menú dinámico desde API (nunca hardcodeado) — presencial/virtual/vendedor
- [x] useModules hook con cache MMKV stale-while-revalidate
- [x] ModuleMenu componente reutilizable
- [x] AuthUser.eventId agregado al store

**Tests:**

- [x] Módulos filtrados correctamente por rol (presencial/virtual/vendedor)
- [x] Módulos deshabilitados no aparecen
- [x] Cache llena correctamente en primera request
- [x] Template congreso/feria aplica módulos correctos
- [x] 23/23 tests passing

**Notas técnicas:**

- phpunit.xml: SQLite en memoria habilitado (antes estaba comentado)
- OrganizationFactory: plan='starter' (no 'basic') — enum: starter/pro/enterprise

**Definición de completado:** ✅ Admin activa/desactiva módulo en Filament → menú del home cambia en la app sin reiniciar.

---

### Sesión 1.3a — Contenido del evento (Backend) ✅ COMPLETADA (2026-03-28)

**Branch:** `feature/s13a-contenido-backend` → mergeado a main
**Repos:** `eventos-backend`

**Nuevas dependencias backend:**

```bash
composer require ezyang/htmlpurifier   # sanitización HTML en custom_pages
```

**Backend:**

- [x] CRUD event_sessions (días, capacity, status, soft delete) + Filament resource
- [x] CRUD speakers + session_speaker + Filament resource
- [x] CRUD documents + Filament resource
- [x] CRUD announcements + Filament resource (acción "Publicar ahora")
- [x] CRUD custom_pages (HTMLPurifier, soporte html/iframe/mixed) + Filament resource
- [x] `GET /api/v1/events/{id}/agenda` — sesiones agrupadas por día + toggle favoritos, cache Redis 60s
- [x] `GET /api/v1/events/{id}/speakers` + detail — cache Redis 5min
- [x] `GET /api/v1/events/{id}/documents` — cache Redis 5min
- [x] `GET /api/v1/events/{id}/announcements` — filtro por rol + cache Redis 30s
- [x] `GET /api/v1/events/{id}/pages` — filtro por rol + cache Redis 5min
- [x] ContentObserver — invalida caché Redis para los 5 modelos de contenido
- [x] ContentSeeder — 3 speakers, 5 sesiones (2 días), 2 docs, 2 anuncios, 1 custom page

**Tests (33/33 ✅ — 108 assertions):**

- [x] Agenda agrupa sesiones por día
- [x] Soft delete no aparece en agenda
- [x] Toggle favorito agrega y quita la sesión
- [x] Speakers devuelve lista del evento
- [x] Speaker detail incluye sus sesiones
- [x] Documentos devuelve lista del evento
- [x] Anuncios solo devuelve los publicados
- [x] Anuncios filtra por rol del attendee
- [x] Custom pages solo devuelve las habilitadas
- [x] Todos los endpoints requieren autenticación

**Notas técnicas:**

- BelongsToMany con foreign keys explícitos: Laravel infiere `event_session_id` del modelo `EventSession`, pero la pivot usa `session_id`
- AnnouncementController: `whereNull('roles')->orWhereJsonContains('roles', $role)` — null = visible para todos
- ContentObserver genérico usa `class_basename($model)` para despachar invalidación correcta

**Definición de completado:** ✅ 33/33 tests. Admin crea sesiones, speakers y anuncios en Filament. Todos los endpoints retornan datos correctos.

---

### Sesión 1.3b — Contenido del evento (App) ✅ COMPLETADA (2026-03-28)

**Branch:** `feature/s13b-contenido-app` → mergeado a main
**Repos:** `eventos-app`
**Depende de:** 1.3a completa

**Nuevas dependencias instaladas:**

```bash
npx expo install expo-image expo-file-system expo-web-browser react-native-webview
npm install @shopify/flash-list        # v2 — eliminado estimatedItemSize (no existe en v2)
```

**App:**

- [x] Hooks: useAgenda (+ toggleFavorite mutation), useSpeakers/useSpeaker, useDocuments, useAnnouncements, usePages — todos MMKV stale-while-revalidate
- [x] `app/(app)/_layout.tsx` — Stack compartido para pantallas de contenido (speakers, docs, etc.)
- [x] AgendaScreen (components/screens/): FlashList con secciones por día, toggle favoritos optimista, badges por tipo de sesión — compartido con presencial + virtual
- [x] Screen Speakers (lista) + Speaker/[id] (detalle con bio, sesiones, LinkedIn)
- [x] Screen Documentos: abrir PDF/archivo via expo-web-browser
- [x] Screen Anuncios: FlashList con timestamps relativos
- [x] Screen Pages/[id]: WebView para HTML + iframe + mixed
- [x] ModuleMenu: router.navigate() (correcto para tabs + stack), nueva ruta 'paginas'
- [x] app.json: ios.deploymentTarget 15.0 → 15.1 (requerido por react-native-webview)

**Notas técnicas:**

- FlashList v2 eliminó el prop `estimatedItemSize` — no pasarlo
- `app/(app)/_layout.tsx` como Stack es el patrón correcto para pantallas compartidas entre roles
- Virtual y presencial agenda comparten `AgendaScreen` vía re-export desde `components/screens/`
- `router.navigate()` en lugar de `push()` para tabs (evita duplicar el tab en el historial)

**Definición de completado:** ✅ TypeScript pasa sin errores. Agenda, speakers, docs, anuncios y custom pages implementados con cache MMKV.

---

### Sesión 1.4 — QR personal + Check-in + Kiosco ✅ COMPLETADA (2026-03-29)

**Branches mergeados:** `feature/s14-checkin-backend`, `feature/s14-mi-qr`, `feature/s14-checkin-socket`
**Repos:** `eventos-backend` + `eventos-app` + `eventos-socket` + `eventos-kiosko` (repo nuevo)

**Backend:**

- [x] `GET /api/v1/me/qr` — devuelve QR token HMAC-SHA256
- [x] `POST /api/v1/checkin` — valida QR, errores: QR_INVALID, QR_ALREADY_USED, ATTENDEE_BANNED, EVENT_NOT_PUBLISHED
- [x] Redis INCR atómico al hacer check-in (aforo)
- [x] Broadcast Socket.IO via HTTP interno `/internal/checkin`
- [x] `GET /api/v1/events/{id}/attendance` — aforo live
- [x] Idempotency-Key en POST /checkin (Redis 24h)
- [x] AuthController login auto-resuelve attendee sin event_slug

**App Expo:**

- [x] Screen Mi QR — QR con react-native-qrcode-svg + foto + nombre
- [x] cacheStore fallback in-memory para Expo Go (MMKV/NitroModules)
- [x] index.tsx useEffect routing fix para dispositivo físico
- [x] Profile screen con botón logout

**Kiosco web (nuevo repo eventos-kiosko):**

- [x] React + Vite + Tailwind + ZXing + Socket.IO client
- [x] IdleScreen: aforo en vivo via Socket.IO
- [x] ScanScreen: cámara ZXing, frame animado
- [x] ResultScreen: verde (foto+nombre) / rojo (error), 4s countdown
- [x] Screen Wake Lock API para tablet
- [x] Configuración via URL params: ?event_id=X&token=TOKEN

**Tests:** 9 Pest tests — todos pasando (42 total)

**Notas técnicas:**

- Kiosco usa token de usuario con rol admin/vendedor (no presencial)
- Socket.IO errors en dev son normales si el server no está corriendo
- react-native-qrcode-svg requiere react-native-svg (instalado como peer)
- AuthController login: si no hay event_slug, busca primer evento activo del usuario

**Definición de completado:** ✅ Kiosco escanea QR y registra check-in. Aforo live se actualiza en tiempo real.

---

### Sesión 1.5 — Leads (Vendedor Scanner) ✅ COMPLETADA (2026-03-29)

**Branch:** `feature/s15-leads` → mergeado a main
**Repos:** `eventos-backend` + `eventos-app`
**Nuevas dependencias:** `expo-camera` + `expo-sharing` (instaladas 2026-03-29 — el roadmap decía "ninguna" pero eran necesarias)

**Backend:**

- [x] `POST /api/v1/leads` — captura lead escaneando QR, con tier y nota
- [x] `GET /api/v1/leads` — lista del vendedor autenticado (nunca de otros vendedores)
- [x] `PUT /api/v1/leads/{id}` — actualizar notas/tier (hot/warm/cold)
- [x] `GET /api/v1/me/leads/export` — CSV: nombre, empresa, cargo, email, teléfono, tier, nota, fecha_scan
- [x] Filament resource Leads (vista admin: leads por stand, filtros por tier)
- [x] Idempotency-Key en `POST /leads` (Redis 24h)

**App:**

- [x] Screen Leads: FlatList + botón escanear QR + botón CSV
- [x] Screen Lead detail: editar nota + cambiar tier
- [x] Botón exportar CSV desde la app (expo-file-system/legacy + expo-sharing)
- [x] Scanner de QR con expo-camera (CameraView SDK 55)

**Tests:** 9 Pest tests — todos pasando (51 total)

**Notas técnicas:**

- `expo-camera` y `expo-sharing` no estaban instalados — se instalaron en esta sesión
- `expo-file-system` SDK 55: `cacheDirectory`/`downloadAsync` están en `expo-file-system/legacy`, no en el namespace principal
- La pantalla de leads reemplazó el VendedorHome (ModuleMenu) — el tab de inicio ahora es la lista de leads
- Rutas tipadas de Expo Router: `'/(app)/lead-detail' as any` hasta que `npx expo start` regenere los tipos

**Definición de completado:** ✅ Vendedor escanea QR de asistente, guarda lead con notas, exporta CSV.

---

### Sesión 1.6 — Directorio de Patrocinadores + Stand Teams ✅ COMPLETADA (2026-03-30)

**Branch:** `feature/s16-sponsors` → mergeado a main
**Repos:** `eventos-backend`
**Tests:** 68/68 pasando (16 nuevos de S1.6)

> **Alcance implementado:** Backend + App completos. Verificado en dispositivo Android.

#### Implementado (Backend)

**Migraciones (7):**

- [x] `sponsors` — perfil público + `owner_attendee_id`, `max_collaborators`
- [x] `sponsor_services` — servicios/productos por sponsor
- [x] `sponsor_favorites` — favoritos del asistente
- [x] `stand_members` — equipo del stand con invitación por email
- [x] `lead_edits` — log de ediciones de leads
- [x] alter `attendees` — `has_vendor_access`, `sponsor_id`
- [x] alter `leads` — `sponsor_id`, nuevo UNIQUE de pool de stand

**Endpoints nuevos (10):**

- [x] `GET /api/v1/events/{id}/sponsors` — directorio con `is_favorite`
- [x] `POST /DELETE /api/v1/events/{id}/sponsors/{id}/favorite`
- [x] `GET /api/v1/me/stand` — mi stand + miembros + cupos
- [x] `POST /api/v1/me/stand/members` — invitar por email (activa si ya registrado)
- [x] `DELETE /api/v1/me/stand/members/{id}` — remover colaborador
- [x] `POST /api/v1/me/stand/transfer` — transferir ownership (owner saliente queda como miembro activo)
- [x] `GET /api/v1/leads/{id}/edits` — log de ediciones

**Modificados:**

- [x] `LeadController` — pool compartido por stand, CSV con "Capturado por", `lead_edits`
- [x] `ModuleController` — agrega leads/scanner a colaboradores sin cambiar su rol
- [x] `AuthService::register()` — hook activa pending invites automáticamente

**Filament:**

- [x] `SponsorResource` — CRUD + servicios Repeater + owner + cupos

**Bug fix incluido:** Al transferir ownership el owner saliente queda registrado como stand_member activo, permitiendo recuperar el control en el futuro.

**App implementada:**

- [x] `sponsors.tsx` — FlashList agrupada por tier, favorito inline
- [x] `sponsor/[id].tsx` — hero banner, servicios, botones web/email, favorito
- [x] `mi-stand.tsx` — owner vs colaborador (is_owner), controles condicionales
- [x] `leads.tsx` — standalone para colaboradores con `has_vendor_access`
- [x] `scanner-stand.tsx` — scanner standalone para colaboradores
- [x] `lead-detail.tsx` — banner "escaneado por", historial de cambios con lead_edits
- [x] `AuthUser` + `hasVendorAccess`, `sponsorId`, `attendeeId`
- [x] `ModuleMenu` — rutas banners/patrocinadores, leads, escaner, mi-stand
- [x] Perfil con cerrar sesión en todos los roles

**Fixes post-implementación:**

- [x] `AuthController::login()` activa pending invites (usuarios con cuenta existente)
- [x] `StandController::show()` accesible para miembros activos con flag `is_owner`
- [x] `LeadController::store()` duplicate check cubre leads pre-pool
- [x] Patch `react-native-css-interop` stringify — bug Navigation context crash
- [x] `ModuleController` slug `key` → `slug` para módulos vendedor extra
- [x] Módulos `banners`, `mi-stand`, `escaner` insertados en DB para evento demo
- [x] `api.ts` — timeout 15s con `AbortController` en todos los requests (fix: fetch sin respuesta quedaba colgado indefinidamente)
- [x] `lead-detail.tsx` — spinner historial de cambios eliminado (no es UI crítica; si llegan edits se muestran, si no, nada)
- [x] `lead-detail.tsx` — `useEffect` sincroniza tier/nota cuando lead carga después del primer render (`useState` solo inicializa una vez: si `leads` estaba fetching al montar, tier/nota quedaban null/'')
- [x] `lead-detail.tsx` — `retry: false` en query de edits

---

### Sesión 1.7 — Networking con Solicitudes de Contacto ✅ COMPLETADA (2026-03-30)

**Branch:** `feature/s17-networking`
**Repos:** `eventos-backend` + `eventos-app`
**Nuevas dependencias:** `expo-contacts` (guardar en agenda del teléfono)

**Backend:** ✅ COMPLETO

- [x] Migraciones: `contact_requests`, `contact_blocks`
- [x] `GET /api/v1/events/{id}/attendees` — directorio opt-in (excluye bloqueados, paginado)
- [x] `GET /api/v1/attendees/{id}/profile` — perfil público del asistente
- [x] `POST /api/v1/contacts/request` — enviar solicitud (body: receiver_attendee_id, message?)
- [x] `PUT /api/v1/contacts/request/{id}` — responder: `{ status: 'accepted'|'ignored' }`
- [x] `GET /api/v1/me/contacts` — contactos aceptados mutuamente
- [x] `GET /api/v1/me/contact-requests` — solicitudes recibidas pendientes
- [x] `GET /api/v1/me/contact-requests/sent` — solicitudes enviadas pendientes
- [x] `GET /api/v1/me/blocked` — lista de bloqueados por el usuario
- [x] `POST /api/v1/contacts/block/{attendeeId}` — bloquear (silencioso, cancela pendientes)
- [x] `DELETE /api/v1/contacts/block/{attendeeId}` — desbloquear
- [x] Push al receptor al recibir solicitud + push al emisor al aceptar
- [x] `SendPushToAttendeeJob` — job de push individual reutilizable
- [x] phone/email solo expuestos en perfil cuando `relation === 'contact'`
- [ ] Toggle `networking_visible` en `PUT /api/v1/me/profile` ← pendiente (S1.8 perfil)

**App:** ✅ COMPLETO

- [x] NetworkingScreen compartida (presencial + virtual): tabs Directorio / Contactos / Solicitudes con badge
- [x] Directorio: FlashList paginada, buscable (debounce 400ms), badge de relación (none/enviada/recibida/contacto)
- [x] app/(app)/attendee/[id].tsx: perfil público, botón Conectar, modal con mensaje opcional, bloquear
- [x] Sección Contactar en perfil (WhatsApp/email/LinkedIn) — solo visible cuando son contactos mutuos
- [x] Guardar contacto en agenda del teléfono (expo-contacts, con permiso)
- [x] Mis Contactos: lista con exportar todos como .vcf (vCard 3.0 vía Share nativo)
- [x] Sección Bloqueados colapsable en tab Contactos (con Desbloquear por usuario)
- [x] Solicitudes recibidas: aceptar / ignorar (respuesta optimista — desaparece al instante)
- [x] Enviar solicitud: optimistic update en perfil → sin flash Enviando→Conectar→Enviada
- [x] fix: retry en guardar contacto (race condition Android primer permiso)
- [x] fix: módulo networking habilitado para rol virtual
- [x] seeder: datos de networking para usuario virtual (María): 2 contactos, 2 solicitudes recibidas, 1 enviada
- [ ] Toggle "Aparecer en el directorio" en perfil propio ← pendiente (S1.8 perfil)

**Tests:** ✅ COMPLETO — 13 tests, 81/81 suite verde

- [x] networking_visible=false → asistente no aparece en directorio
- [x] Asistente bloqueado no aparece en directorio del que bloqueó
- [x] Solicitud duplicada → 409 CONTACT_ALREADY_SENT
- [x] Bloqueo previene nuevas solicitudes (CONTACT_BLOCKED)
- [x] Aceptar solicitud → ambos se ven como contactos mutuos
- [x] Status 'ignored' NO genera notificación al emisor

**Definición de completado:** ✅ Asistente busca otro en directorio, envía solicitud, receptor recibe push y acepta → ambos son contactos mutuos. Funciona en presencial y virtual.

**Notas técnicas:**

- `orderByRaw('(SELECT name FROM users WHERE users.id = attendees.user_id)')` — sin JOIN para evitar ambigüedad SQLite en tests
- `useSendContactRequest`: onMutate actualiza cache de perfil Y directorio al instante (elimina flash)
- `useRespondRequest`: onMutate elimina solicitud de la lista al instante (revierte si error)
- `expo-contacts addContactAsync`: retry con 400ms delay por race condition de Android en primer otorgamiento de permiso
- Módulo networking: `roles` actualizado a `["presencial","virtual"]` en DB (y cache:clear)

---

### Sesión 1.8 — Gestión de usuarios + Bans (Admin)

**Branch:** `feature/s18-admin-bans`
**Repos:** `eventos-backend` + `eventos-app`
**Nuevas dependencias:** ninguna

**Diseño del ban (2026-03-30):**
El ban es a nivel de **asistente por evento** (no de cuenta global). `users.is_active=false` sigue siendo el bloqueo de cuenta permanente.

Flujo de ban:

- Login **siempre funciona** — no se rompe el flujo de registro/QR/descarga del app
- La respuesta del login incluye `attendee.ban` (null si no hay ban activo)
- La app detecta `ban != null` y muestra `BannedScreen` con motivo y fecha de expiración (si aplica)
- Admin puede **desbanear** en cualquier momento → la próxima vez que el usuario abre la app ve el home normal
- Ban temporal: expira automáticamente (Scheduler) → mismo efecto que desbanear
- Check-in también verifica el ban como segunda línea de defensa

```
Login → attendee.ban = { reason, expires_at } → app muestra BannedScreen
      → attendee.ban = null                   → app muestra home normal
```

**Backend:** ✅ COMPLETO

- [x] Migración: `create_moderation_tables` actualizada — attendee_bans (event_id, unbanned_at/by), attendee_role_changes, admin_audit_log
- [x] `AttendeeResource` incluye `ban` (objeto con reason + expires_at, o null) en login/me response
- [x] `POST /api/v1/admin/attendees/{id}/ban` — banear con motivo y duración opcional
- [x] `DELETE /api/v1/admin/attendees/{id}/ban` — desbanear (registra unbanned_at + unbanned_by)
- [x] `GET /api/v1/admin/attendees/{id}/ban-history` — historial de bans/desbans
- [x] `GET /api/v1/admin/events/{id}/attendees` + `PATCH /admin/attendees/{id}/role`
- [x] Filament resource Attendees (lista, cambiar rol, Banear/Desbanear inline)
- [x] Filament resource AttendeeBans (historial read-only)
- [x] attendee_role_changes log automático al cambiar rol
- [x] admin_audit_log en ban, unban, change_role
- [x] Job: `ExpireAttendeeBansJob` — expira bans vencidos (Scheduler, cada hora)
- [x] Push al usuario al ser baneado (con motivo) y al ser desbaneado

**App:** ✅ COMPLETO

- [x] `BannedScreen` — pantalla de acceso suspendido con motivo + fecha de expiración (si aplica) + botón "Entendido" que cierra sesión
- [x] Auth flow: al hidratar o hacer login, si `attendee.ban != null` → redirigir a `BannedScreen`
- [x] Al desbanear o expirar, el usuario inicia sesión normalmente

**Tests:** ✅ COMPLETO — 8 tests, 89/89 suite verde

- [x] Login retorna `ban` en attendee cuando hay ban activo
- [x] Login retorna `ban: null` cuando no hay ban o fue desbaneado
- [x] Check-in con ban activo → 403 ATTENDEE_BANNED (segunda línea de defensa)
- [x] Ban con duración expira automáticamente (job)
- [x] Desbanear → ban.unbanned_at registrado, próximo login muestra home
- [x] Audit log registra quién hizo el ban y quién desbaneó
- [x] attendee_role_changes registra cambio de rol

**Definición de completado:** ✅ Admin banea asistente en Filament → asistente abre la app y ve BannedScreen con motivo. Admin desbanea → asistente ve home normal.

**Notas técnicas:**

- `create_moderation_tables.php` es la migración canónica para ban/role_changes/audit_log — no crear migraciones separadas para estas tablas
- `AdminAuditLog` requiere `protected $table = 'admin_audit_log'` (Laravel pluralizaría a `admin_audit_logs`)
- `AttendeeAdminResource` usa `getEloquentQuery()` con `with(['user','event','activeBan'])` para evitar N+1 en la tabla Filament
- Ban check en `migrate:fresh`: el esquema antiguo en `create_moderation_tables` tenía `lifted_at`/`lifted_by`; ya reemplazado por `unbanned_at`/`unbanned_by`

---

### Sesión 1.9a — Chat en tiempo real por sesión (Backend + Socket)

**Branch:** `feature/s19-chat`
**Repos:** `eventos-backend` + `eventos-socket`
**Estado:** ✅ COMPLETO

**Aclaración de diseño (2026-03-29):**
El chat es **por sesión de agenda**, NO un chat general del evento.
Cada sesión en streaming tiene su propio canal independiente.
N transmisiones simultáneas = N chats separados (sin límite fijo).
Room Socket.IO: `chat:session:{sessionId}` (no `chat:event:{eventId}`).

**Socket.IO:** ✅ COMPLETO

- [x] Room `chat:session:{sessionId}` — una por sesión de agenda
- [x] Evento `chat:send` (cliente→servidor) / `chat:message` (servidor→cliente)
- [x] Historial de contexto: últimos 20 mensajes al hacer join (`chat:history`)
      → Redis LPUSH/LTRIM por sala: `chat:history:session:{id}` (sobrevive reinicios)
      → Al conectar: LRANGE 0 19 → emit `chat:history` antes de recibir nuevos
- [x] Rate limiting: 1 mensaje/2s por usuario (Redis)
- [x] Throttle global: 50ms entre broadcasts por sala
- [x] Validación: autenticado + no baneado antes de enviar
- [x] Persistencia fire-and-forget: socket → POST /internal/chat/message → Laravel

**Backend:** ✅ COMPLETO

- [x] `GET /api/v1/sessions/{id}/chat/messages` — últimos N mensajes (cursor pagination, `before_id`)
- [x] `DELETE /api/v1/admin/chat/messages/{id}` — moderar (soft delete, admin only)
- [x] `POST /internal/chat/message` — endpoint interno para socket (X-Internal-Secret)
- [x] `chat_messages` table: room, body, type, SoftDeletes — `ChatMessage::sessionRoom()`
- [x] `EventSessionFactory` creada para tests

**Tests:** ✅ COMPLETO — 5 tests, 94/94 suite verde

- [x] Socket puede persistir mensaje vía endpoint interno
- [x] Endpoint interno rechaza secret inválido
- [x] GET mensajes retorna solo mensajes de la sesión correcta
- [x] Mensaje eliminado no aparece en historial
- [x] Admin puede eliminar mensaje de chat

**Notas técnicas:**

- `type` enum en `chat_messages` es `['text', 'image', 'system']` — usar `'text'` para mensajes de chat (no `'chat'`)
- `EventSession` no tenía `HasFactory` — agregado + `EventSessionFactory` creada
- `sync_moderation_tables_schema.php` usa `Schema::hasColumn()` guards para ser condicional (MySQL vs SQLite)
- Socket module en `eventos-socket/src/chat.ts` — `registerChatHandlers(io, socket, redis)`

---

### Sesión 1.9b — Chat en tiempo real por sesión (App)

**Branch:** `feature/s19-chat` → mergeado a main
**Repos:** `eventos-app`
**Estado:** ✅ COMPLETO (2026-03-30)

**App:** ✅ COMPLETO

- [x] `app/(app)/session-chat/[id].tsx` — screen chat por sesión (FlatList invertida)
- [x] `hooks/useChat.ts` — Socket.IO: conexión, join:session, chat:history, chat:message, chat:deleted, chat:emoji, loadMore
- [x] `lib/chatApi.ts` — historial API con cursor pagination (`before_id`)
- [x] `_layout.tsx` registra ruta `session-chat/[id]`
- [x] `AgendaScreen.tsx` — botón 💬 Chat en cada SessionCard → navega a chat
- [x] Dedup API vs socket: IDs distintos por formato, `socketMsgIds` Set + `lastApiSentAt` ref
- [x] Memoización `displayData` con `useMemo` para evitar reverse() en cada render
- [x] `EXPO_PUBLIC_SOCKET_URL` agregado a `.env`

**Emojis animados:** ✅ COMPLETO

- [x] Socket event `chat:emoji` — barra de 8 emojis toggleable
- [x] Emojis: ❤️ 👏 🔥 😂 😮 🎉 ⭐ 💯
- [x] Animación float-up + fade con `Animated` nativo (Reanimated en sesión UI)
- [x] Throttle manejado en servidor (1 emoji/s por usuario)

**Backend + Performance:** ✅

- [x] `demo:s19` — siembra 20 mensajes en sesión 1, 5 en sesión 2, genera tokens frescos
- [x] Migración `optimize_chat_messages_index` — reemplaza `(room, created_at)` por `(room, id)` para cursor pagination eficiente

**Web (versión browser):** aplazado a Fase 2 (S2.1 — junto con streaming web Next.js)

**Notas técnicas:**
- `FlashList` v2 no tiene `inverted` → se usa `FlatList` de react-native (suficiente para chat 50-200 msgs)
- `socket.io-client@4` instalado (compatible con servidor socket.io@4.8.3)
- IDs API = string numérico ("1","2"), IDs socket = tempId ("sessionId-attendeeId-ts") — no colisionan
- `chat:history` socket solo agrega mensajes con `sentAt > lastApiSentAt` para evitar duplicados visuales

**Definición de completado:** ✅ Chat + emojis funcionan en N sesiones simultáneas sin interferencia. Mensajes persisten tras reconexión. Probado en dispositivo físico.

---

### Sesión 1.10 — Encuestas en vivo ✅ COMPLETA (refactor v2)

**Branch:** `feature/s110-encuestas-v2` → mergeado a main ✅
**Repos:** `eventos-backend` + `eventos-app` + `eventos-socket`
**Completado:** 2026-04-04

#### Decisiones de diseño aplicadas

- `word_cloud` eliminado del scope (3 tipos finales: multiple_choice, open_text, star_rating)
- `allow_multiple boolean` en `live_poll_questions` — soporta single y multi-select sin recodear
- UNIQUE: `(poll_id, question_id, attendee_id, option_id)` — permite multi-select
- Poll cierra mientras usuario responde: si ≥1 respuesta guardada → terminar; si 0 → cerrar inmediato
- Navegación hacia atrás permitida (UPDATE voto anterior)
- Badge verde en ícono del módulo Encuestas cuando hay poll activo

#### Modelo de datos implementado

```
live_polls:          id, event_id, session_id (nullable), scope (session/event), title, status, activated_at, closed_at
live_poll_questions: id, poll_id, question_text, question_type (multiple_choice|open_text|star_rating), allow_multiple, sort_order
live_poll_options:   id, question_id (FK), option_text, sort_order
live_poll_votes:     id, poll_id, question_id, option_id (nullable), attendee_id, answer_text (nullable)
                     UNIQUE: (poll_id, question_id, attendee_id, option_id)
```

#### Backend ✅

- [x] 4 migraciones: refactor live_polls, create live_poll_questions, rebuild live_poll_options y live_poll_votes
- [x] Modelos: LivePoll, LivePollQuestion (nuevo), LivePollOption, LivePollVote
- [x] PollController: store/start/close/vote (single+multi-select+open_text+star_rating)/activePoll/surveys/results
- [x] `GET /api/v1/events/{id}/surveys` + `has_active` para badge
- [x] `GET /api/v1/admin/polls/{id}/results` — resultados por pregunta
- [x] LivePollResource Filament: Repeater anidado, página LivePollResults (barras/estrellas/open_text)
- [x] Display proyectable: `GET /display/polls/{id}` — Blade full-screen, auto-refresh 3s
- [x] Socket: poll:closed evento correcto, room session:{id} o event:{id} según scope
- [x] 18 tests Pest — 18/18 pasando
- [x] ExportPollResponsesJob: CSV por asistente (nombre/email/rol/respuestas), BOM UTF-8, notificación campana con link descarga
- [x] Import preguntas desde CSV (FileUpload en Filament + plantilla descargable con instrucciones)
- [x] display_url column en tabla Filament — enlace directo al proyector desde la lista
- [x] Toast único post-export (QUEUE_CONNECTION=sync en dev, job síncrono)

#### App ✅

- [x] useChat.ts: myAnswers por questionId, poll:closed logic, join event:{id} room
- [x] PollSlides: slides por pregunta (1/N), MultipleChoice/StarRating/OpenText, botón Atrás, pantalla Gracias
- [x] encuestas.tsx: módulo scope=event con lista activas/cerradas
- [x] ModuleMenu: prop `badges` con punto verde en módulos activos
- [x] index.tsx (presencial+virtual): badge encuestas si has_active=true

**Definición de completado:** ✅ Admin crea encuesta con preguntas mixtas → inicia → app muestra slides por tipo → resultados en Filament y display proyectable.

---

### Sesión 1.11 — Push notifications ✅ COMPLETADA (2026-04-04)

**Branch:** `feature/s111-push` → mergeado a main (ambos repos)
**Repos:** `eventos-backend` + `eventos-app`

**Implementado:**

- [x] `expo-notifications ~55.0.16` — FCM V1 via Expo Push API (no kreait/laravel-firebase — descartado)
- [x] `push_notification_logs` table — tracking entregadas / abiertas por dispositivo
- [x] `scheduled_notifications` table — notificaciones programadas con cron dispatch
- [x] `SendPushToAttendeeJob` — job individual con logging de ticket Expo + open tracking
- [x] `CheckExpoReceiptsJob` — verifica receipts cada 5 min, actualiza status/delivered_at
- [x] `DispatchScheduledNotificationsJob` — ejecuta cada minuto, despacha notificaciones pendientes
- [x] `POST /api/v1/auth/expo-token` — registro del push token al autenticar
- [x] `POST /api/v1/admin/events/{event}/notifications/send` — envío inmediato
- [x] `POST /api/v1/admin/events/{event}/notifications/schedule` — programar futuro
- [x] `AnnouncementResource` Filament — "Publicar ahora" envía push real a todos los asistentes
- [x] `ScheduledNotificationResource` Filament — CRUD + "Enviar ahora" + stats entregadas/abiertas
- [x] `TrackController` — maneja `push_open` para registrar opened_at
- [x] `useNotifications` hook — registro token, listener foreground, tracking apertura
- [x] Grupo "Comunicación" en Filament con Anuncios + Notificaciones Push juntos
- [x] 13 Pest tests — todos pasando

**Probado en dispositivo:** OnePlus 10 Pro — notificación llega en < 3s desde Filament.

**Definición de completado:** ✅ Admin envía push desde Filament → llega al dispositivo de prueba en < 5s.

---

### Sesión 1.12 — Tracks + Session types ✅ COMPLETADA (2026-04-04)

**Branch:** `feature/s112-tracks-session-types` → mergeado a main (ambos repos)
**Repos:** `eventos-backend` + `eventos-app`

**Objetivo:** Agregar tracks para agrupar sesiones temáticamente (session_type y capacity ya existían en DB).

**Backend:**

- [x] Tabla `session_tracks`: `id`, `event_id`, `name`, `color` (hex, default #6366f1), `description`, `sort_order`, timestamps
- [x] `event_sessions.track_id` FK nullable → `session_tracks` (nullOnDelete)
- [x] `SessionTrack` model + relaciones
- [x] Filament: `SessionTrackResource` — ColorPicker, sessions_count, deferLoading, bulk assign desde EventSessionResource
- [x] Filament: filtro por track en EventSessionResource, select reactivo filtrado por event_id
- [x] API: `GET /api/v1/events/{id}/tracks` — lista tracks con color
- [x] API: sesiones incluyen `track: {id, name, color}` via `whenLoaded`
- [x] Fix API 401 JSON: `AuthenticateApi` middleware + exception render handler en `bootstrap/app.php`
- [x] Optimización Filament: OPcache 256MB + SPA mode + `deferLoading()` en todos los resources

**App:**

- [x] Agenda: `TrackBadge` — badge de color con fondo hexcolor+22 por track en cada card
- [x] Filtro por track en agenda (chips horizontales, aparece solo si hay tracks)

**Definición de completado:** Admin crea tracks con colores → asigna sesiones → app muestra agenda con badges de color y filtro funcional. ✅

---

### Sesión 1.13a — Emails automáticos + editor de plantillas ✅ 2026-04-05

**Branch:** `feature/s113-emails` → mergeado a main
**Repos:** `eventos-backend`

**11 tipos de email implementados:**

| Tipo | Trigger |
|---|---|
| `invitation` | CSV import / envío manual desde Filament |
| `welcome` | Registro exitoso en evento |
| `reminder_24h` | Cron diario 8am — eventos que empiezan mañana |
| `session_reminder` | Cron cada 5min — sesiones favoritas en 55-65min |
| `banned` | Ban desde API o Filament |
| `cancelled` | Cancelación del evento |
| `password_reset` | POST /api/v1/auth/forgot-password |
| `email_verification` | Registro + reenvío manual |
| `csv_import_completed` | Al terminar job de importación CSV |
| `registration_pending` | Registro con requires_approval=true |
| `registration_approval` | Admin aprueba registro en Filament |

**Backend:**

- [x] `.env` MAIL_MAILER=smtp, MAIL_PORT=1025 (Mailpit), APP_DEEP_LINK_SCHEME=eventos
- [x] `BaseEventosMail` + 11 Mailables concretos + `SponsorLeadMail` + `ContactRequestMail`
- [x] Layout HTML responsive con branding del evento (logo, primary_color)
- [x] `SendEmailJob` — genérico, 3 reintentos, logging automático en `email_logs`
- [x] `SendEventRemindersJob` (cron diario 8am) + `SendSessionRemindersJob` (cron cada 5min)
- [x] `EmailTemplate` model con `resolve(eventId, type, locale)` — fallback evento → sistema
- [x] 26 plantillas sembradas ES + EN en `EmailTemplateSeeder`
- [x] `email_logs` table: type, user_id, event_id, recipient_email, status, sent_at, error_message
- [x] Password reset: token en `password_reset_tokens`, link → ruta web → deep link `eventos://`
- [x] Email verification: signed URL 60min, ruta web verifica + redirige a deep link `eventos://`
- [x] Vista Blade `auth/deep-link-redirect` con auto-redirect JS + botón fallback
- [x] Filament `EmailTemplateResource` — editor Tiptap, variables hint, botón "Enviar prueba"
- [x] Filament `EmailLogResource` — solo lectura, filtrable por tipo/estado/evento

**Tests:** 9 pasando (resolución fallback, registro→welcome, registro→pending, ban, schedulers, log)

---

### Sesión 1.13b — SMTP propio por organización ✅ 2026-04-05

**Branch:** `feature/s113b-smtp-org` → mergeado a main
**Repos:** `eventos-backend`

**Backend:**

- [x] Tabla `organization_email_settings` — password encriptada con cast `'encrypted'`
- [x] `OrganizationEmailSettings::isReady()` — use_custom_smtp + is_verified + host + from_email
- [x] `EmailService::mailerFor(?orgId)` — resuelve mailer custom o fallback al sistema
- [x] `EmailService::testConnection(settings, toEmail)` — envía email de prueba real
- [x] `SendEmailJob` actualizado — resuelve organization_id desde event_id automáticamente
- [x] Filament `OrganizationEmailSettingsResource` — form completo + botón "Probar conexión"
- [x] Botón verifica conexión, setea is_verified + verified_at, notificación success/danger

**Tests:** 7 pasando (mailerFor fallback, isReady, password encriptada, SendEmailJob con org)

**Definición de completado:** ✅ 141 tests pasando. Emails llegan a Mailpit en dev. SMTP propio verificable desde Filament.

---

## ─────────────────────────────────────────

## SESIÓN 1.x — Upload de imágenes / archivos (Cloudflare R2) ✅ COMPLETADA (2026-04-05)

## ─────────────────────────────────────────

**Arquitectura implementada: R2 como primario + URL manual como fallback**

No hay vendor lock-in: el campo en BD siempre almacena una URL pública. Si Cloudflare falla, el admin pega una URL de otro host. Si R2 no está configurado, el sistema sube automáticamente al disco `public` local.

**Flujo real implementado:**
1. Admin ve un `FileUpload` en Filament → sube archivo → `StorageService::upload()` lo sube a R2 (o disco local si R2 no configurado) → URL pública se guarda en el campo `_url`
2. Alternativamente: admin pega cualquier URL en el campo "URL de imagen (alternativa)" → se guarda directamente → sin subir archivo

**`StorageService`:**
- `upload(UploadedFile, directory): string` → intenta R2, fallback a `public` disk, siempre retorna URL
- `delete(url): void` → borra de R2 o public disk; ignora URLs externas silenciosamente
- `r2IsConfigured(): bool` → verifica que key, secret, bucket y endpoint estén en config
- `activeDriver(): string` → retorna `'r2'` o `'public'`

**`POST /api/v1/admin/uploads`** — sube archivo, retorna `{ url, driver, original_name }`
**`DELETE /api/v1/admin/uploads`** — borra archivo por URL

**`ImageUploadField::make(field, label, directory)`** — componente Filament reutilizable:
- FileUpload (primary): usa `saveUploadedFileUsing` → R2/public → guarda URL en DB
- TextInput (fallback): pegar URL de cualquier host, live(onBlur), escribe al mismo campo

**Filament resources actualizados (FileUpload + URL fallback):**
- [x] `SponsorResource` — `logo_url`, `banner_url`
- [x] `SpeakerResource` — `photo_url`
- [x] `DocumentResource` — `file_url` (PDFs, PPT, DOCX, imágenes)

**Pendiente para Sesión UI o App:**
- `users.photo_url` — foto de perfil (desde app: expo-image-picker → POST /admin/uploads)
- `events.logo_url`, `events.banner_url` — requiere EventResource en Filament
- `event_sessions.thumbnail_url` — requiere campo en EventSessionResource

**Variables de entorno (dejar vacíos en dev para usar disco local):**
```
CLOUDFLARE_R2_KEY=
CLOUDFLARE_R2_SECRET=
CLOUDFLARE_R2_BUCKET=
CLOUDFLARE_R2_ENDPOINT=     # https://<account-id>.r2.cloudflarestorage.com
CLOUDFLARE_R2_PUBLIC_URL=   # https://pub-<hash>.r2.dev
```

**Cloudflare R2 pricing (2026):**
- Storage: 10 GB gratis/mes, luego $0.015/GB
- Egress (descargas): **$0 siempre** ← diferencial vs S3
- Para un evento de 500-1000 asistentes: el tier gratuito cubre todo

**Dependencia instalada:** `league/flysystem-aws-s3-v3:^3.32` — compatible con R2 (S3 API)
**Extensiones PHP habilitadas en CLI:** `ext-fileinfo`, `ext-gd` (necesarias para tests de upload)

---

## ─────────────────────────────────────────

## SESIÓN 1.x — Banners / Carrusel de sponsors en pantalla Inicio ✅ COMPLETADA (2026-04-05)

## ─────────────────────────────────────────

**⚠️ IMPORTANTE — NO CONFUNDIR:**
- Módulo `banners` (slug: `banners`) = este carrusel/slideshow del home
- Módulo `patrocinadores` (slug: `patrocinadores`) = directorio de sponsors con detalle → ya implementado en S1.5/S1.6
- Son features separadas. El error de confundirlas ocurrió dos veces. Ver `feedback_banners_vs_patrocinadores.md`.

**Backend:**
- [x] Model `Banner` con fillable + casts + `UPDATED_AT = null` + scope `enabled()`
- [x] `BannerResource` en Filament (ImageUploadField para `image_url`, ToggleColumn, reorder por `sort_order`)
- [x] `GET /api/v1/events/{eventId}/banners` — lista banners habilitados ordenados
- [x] 3 tests Feature pasando (habilitado aparece, deshabilitado no, sin banners → 200 vacío)
- [x] Seeds: 4 banners en ContentSeeder (3 enabled, 1 disabled)

**App:**
- [x] `lib/bannersApi.ts` — interface `Banner` + `bannersApi.list(eventId)`
- [x] `hooks/useBanners.ts` — React Query hook, staleTime 5min
- [x] `BannerCarousel` component — FlatList + pagingEnabled + autoplay timer + dots indicadores
- [x] Toca banner → `Linking.openURL(link_url)`
- [x] Home presencial y virtual: carrusel embebido bajo el saludo, condicional a módulo `banners` habilitado
- [x] Pantalla standalone `/banners` registrada en `_layout.tsx`

**Comportamiento módulo `banners`:**
- Módulo `banners` desactivado en Filament → carousel oculto en todo el home
- Módulo activo pero banner individual `enabled=false` → ese banner no aparece (filtrado en API)
- El slug `banners` NO aparece en el ModuleMenu grid (`HIDDEN_FROM_MENU`) — es solo carousel de layout
- Control granular: activar/desactivar módulo entero O banners individuales

---

## ─────────────────────────────────────────

## SESIÓN 1.x-A — Onboarding configurable: Backend + estructura base app ✅ COMPLETADA (2026-04-05)

## ─────────────────────────────────────────

**Filosofía:** El onboarding es la puerta del producto. Si es plano, el cliente siente que compró algo barato. Si es fluido, animado y con su marca, ya vendiste la siguiente renovación.

**Flujo implementado:** Abrir app → onboarding (pre-login, fetch público sin token) → login → home. Una sola vez, flag global `onboarding_seen` en MMKV. Si no hay slides → salta directo al login.

### Tabla `onboarding_slides`

| Campo | Tipo | Propósito |
|---|---|---|
| `event_id` | FK | Slides por evento |
| `order` | integer | Orden drag & drop en Filament |
| `media_url` | string | URL de imagen o Lottie JSON |
| `media_type` | enum | `image` \| `lottie` |
| `title` | string nullable | Título del slide |
| `subtitle` | string nullable | Subtítulo / descripción |
| `bg_color_from` | string | Color hex inicio del gradiente de fondo |
| `bg_color_to` | string | Color hex fin del gradiente de fondo |
| `title_color` | string | Color hex del texto (claro u oscuro según fondo) |
| `auto_advance_seconds` | integer nullable | null = manual, número = auto-avanza en X segundos |
| `active` | boolean | Toggle para desactivar sin borrar |

### Campos añadidos a `events`

| Campo | Propósito |
|---|---|
| `onboarding_cta_text` | Texto del botón final ("Empezar la experiencia", "Entrar al evento", etc.) |
| `onboarding_skip_enabled` | Si el usuario puede saltar el onboarding |

### Filament — `OnboardingSlideResource`

- Grupo: `Configuración del evento`
- Reordenamiento drag & drop por `order`
- Preview de imagen inline + color swatch del gradiente en tabla
- Toggle activo/inactivo por fila
- ImageUploadField para `media_url`
- ColorPicker para `bg_color_from`, `bg_color_to`, `title_color`

### API

- `GET /api/v1/events/{event}/onboarding` — slides activos ordenados + `cta_text` + `skip_enabled`

### Nuevas dependencias app (instalar en esta sesión)

| Paquete | Por qué |
|---|---|
| `expo-linear-gradient` | Gradientes de fondo configurables por slide |
| `lottie-react-native` | Animaciones Lottie JSON como media de slide |
| `expo-haptics` | Feedback táctil al cambiar slide |

### App — Sesión A ✅

- [x] `lib/onboardingApi.ts` — fetch público sin token, usa `EXPO_PUBLIC_EVENT_ID`
- [x] `hooks/useOnboarding.ts` — React Query, staleTime 10min, eventId opcional
- [x] `app/onboarding.tsx` — slides fullscreen, LinearGradient, Image/Lottie, dots pill, haptics, autoplay, skip, CTA
- [x] Responsive: `Dimensions.height` fullscreen, `useSafeAreaInsets` para bottom UI
- [x] Flujo pre-login: `index.tsx` chequea `onboarding_seen` síncrono → onboarding → login
- [x] Al terminar/skip: `setCached('onboarding_seen', true)` → login (o home si ya tiene sesión)
- [x] Autoplay configurable por slide (`auto_advance_seconds`)
- [x] Dots tipo pill (activo = ancho w-20, inactivo = círculo w-6)
- [x] `OnboardingSeeder` independiente: `php artisan db:seed --class=OnboardingSeeder`
- [x] `EXPO_PUBLIC_EVENT_ID=1` en `.env`

### Backend — checklist ✅

- [x] Migration `create_onboarding_slides_table`
- [x] Migration `add_onboarding_fields_to_events_table`
- [x] Model `OnboardingSlide` + fillable + casts + scope `active()`
- [x] Relación `Event::onboardingSlides()` + campos `onboarding_cta_text`, `onboarding_skip_enabled`
- [x] `OnboardingSlideResource` Filament — ColorPicker, ImageUploadField, reorder, gradient preview blade
- [x] `GET /api/v1/events/{eventId}/onboarding` — **ruta pública**, sin sanctum
- [x] Seeds: 3 slides en `ContentSeeder` + `OnboardingSeeder` standalone
- [x] 4 tests pasando (154 total en suite)

---

## ─────────────────────────────────────────

## SESIÓN 1.x-B — Onboarding: Animaciones premium ✅ COMPLETADA (2026-04-06)

## ─────────────────────────────────────────

**Depende de:** Sesión 1.x-A completada y funcionando.

**Objetivo:** Transformar la pantalla funcional en una experiencia visualmente memorable.

### Técnicas de animación implementadas

| Efecto | Librería | Detalle |
|---|---|---|
| Entrada por elementos | Reanimated `withSpring` + `withDelay` | Título sube desde +40px, subtítulo aparece 150ms después, imagen hace fade+scale |
| Parallax imagen | Reanimated `useAnimatedScrollHandler` | La imagen se mueve al 60% de la velocidad del gesto — profundidad |
| Transición entre slides | Crossfade del gradiente de fondo | El nuevo fondo aparece con `withTiming(1, {duration: 400})` mientras el contenido entra desde el lado |
| Dots tipo pill animados | Reanimated `useAnimatedStyle` | Dot activo se expande horizontalmente con spring (igual que Instagram Stories) |
| Haptic al cambiar slide | `expo-haptics` | `impactAsync(ImpactFeedbackStyle.Light)` |
| Pulse en botón CTA | Reanimated `withRepeat` + `withSequence` | Scale 1 → 1.04 → 1, infinito, suave |
| Skip fade-out | Reanimated | El botón skip se desvanece al llegar al último slide |

### App — checklist Sesión B ✅

- [x] Reanimated: animación de entrada por slide (spring title + delay subtitle + fade-scale image)
- [x] Parallax: imagen se mueve a 0.6x la velocidad del scroll
- [x] Crossfade de gradiente entre slides
- [x] Dots pill animados con Reanimated
- [x] Haptic feedback en cambio de slide
- [x] Pulse animado en botón CTA (último slide)
- [x] Fade-out del botón Skip en último slide
- [x] Flujo survey post-login: pills multi-select intereses, `pending_survey_option_ids` en MMKV, `onboardingApi.storeSurvey` fire & forget
- [x] Fase login integrada en onboarding (formulario dark sin salir del flujo)
- [x] Filament editor en vivo: `OnboardingPreview` — split view editor + mockup teléfono, tabs Slides/Survey/Login
- [x] Seeder: 3 slides estilo Fever dark mode + 12 opciones survey con emoji

### Bugs pendientes (dejar para sesión de pulido UI)

1. **Botones invisibles en Android** — `AnimatedFlatList` (Reanimated createAnimatedComponent) puede quedar encima de controles absolutamente posicionados. Fix probable: `zIndex: 10` al View de controles.
2. **Animaciones no visibles en Expo Go** — Reanimated 4.x requiere development build. Usar `npx expo run:android` para probar.
3. **Reset onboarding** — no hay botón "Ver introducción de nuevo" en perfil. Fix: `deleteCached('onboarding_seen')` + `router.replace('/onboarding')`.

---

## ─────────────────────────────────────────

## SESIONES 1.14 → 1.26 — Features funcionales (ex-Fase 2)

## ─────────────────────────────────────────

> Estas sesiones fueron movidas de Fase 2 a Fase 1 (2026-04-06). Decisión: terminar TODO lo funcional antes del barrido de UI, así el diseño se aplica una sola vez sobre código estable.

---

### Sesión 1.14 — Streaming nativo + Mi Agenda ✅ (2026-04-06)

**Branch:** `feature/s114-streaming` → mergeado a `main`
**Repos:** `eventos-app` + `eventos-backend` + `eventos-socket`

**Scope completado:**
- [x] Split-screen: WebView (stream, top) + panel interactivo (bottom) — chat/poll/qna/none
- [x] `interactive_mode` enum por sesión, toggle en Filament — cambia en tiempo real via socket (`session:mode_changed`)
- [x] `stream_url` también reactiva: admin pega/borra URL → pantalla se actualiza sin salir (`useSessionMode` hook)
- [x] Placeholder `📡 Transmisión no disponible aún` cuando no hay URL
- [x] Tab "Mi Agenda" → abre directamente favoritos; Agenda general accesible como ruta stack
- [x] Botón ▶️ "Ver transmisión" siempre visible en todas las cards
- [x] Botón 📅 "Calendario" → descarga `.ics` y comparte (Google Cal, iOS, Outlook)
- [x] `ChatPanel` y `PollPanel` como componentes embebibles (reutilizables fuera de split-screen)
- [x] Optimistic update en toggle ★ favorito — ambos caches (`agenda` + `mi-agenda`) sincronizados
- [x] Tracking fire-and-forget `session_stream_view` con `duration_seconds` al salir/background
- [x] Push reminders: 15 min y 5 min antes para sesiones favoritas (dedupe Redis) — `SendAgendaRemindersJob`
- [x] Speaker ↔ Sesión bidireccional en Filament (`SessionsRelationManager`)

**Tests Pest:**
- [x] Sesión con `stream_url=null` → campo en API
- [x] Sesión con `stream_url` válida → campo presente
- [x] `POST /track` con `session_stream_view` + `duration_seconds` → registrado
- [x] `?favorites=true` devuelve solo sesiones favoritas del usuario
- [x] Agenda favoritos vacía → 200 array vacío
- [x] Agenda favoritos requiere autenticación → 401
- [x] `calendar.ics` devuelve respuesta con Content-Type `text/calendar`
- [x] Push 15 min antes de sesión favorita → job despachado
- [x] Push 5 min antes de sesión favorita → job despachado
- [x] Dedupe: no reenvía si ya se envió en esa ventana
- [x] No envía si sesión cancelada
- [x] No envía si attendee sin token
- [x] No envía si sesión no está en favoritos

**Pendiente verificar en dispositivo:**
- [ ] Push reminders — ⚠️ requieren dev build (expo-notifications no funciona en Expo Go SDK 53+) — pendiente para sesión de deploy

---

### Sesión 1.15 — Preguntas al speaker (Q&A en vivo) ✅ (2026-04-06)

**Branch:** `feature/s115-qna` → mergeado a `main`
**Repos:** `eventos-backend` + `eventos-app` + `eventos-socket`

**Scope completado:**
- [x] Tablas: `session_questions` + `question_upvotes` (PK compuesta para dedupe de upvotes)
- [x] Endpoints públicos: POST pregunta, GET aprobadas (ordenadas por upvotes), POST upvote idempotente
- [x] Endpoints moderador: GET pendientes, PATCH moderate (approved/answered/dismissed)
- [x] Moderación desde Filament admin — `QuestionsRelationManager` con polling 3s para revisión en vivo
- [x] Acciones en Filament: Aprobar / Respondida / Descartar (con confirmación) + bulk approve/dismiss
- [x] Socket: `question:submitted` → broadcast al room, `question:approved/answered/upvoted` → todos en el room
- [x] `QnAPanel`: asistente ve lista de aprobadas + input + toggle anónimo (moderación NO en la app)
- [x] Preguntas ordenadas por upvotes (más votadas arriba)
- [x] Pregunta anónima → autor oculto en GET público, visible solo para moderadores

**Tests Pest:**
- [x] Asistente envía pregunta → status pending en DB
- [x] Pregunta anónima guarda is_anonymous=true + autor no expuesto
- [x] Endpoint requiere autenticación → 401
- [x] GET público solo devuelve approved y answered
- [x] Moderador aprueba → aparece en GET público
- [x] Moderador descarta → no aparece en GET público
- [x] Asistente no puede moderar → 403
- [x] Upvote agrega 1 voto, segundo upvote idempotente
- [x] GET pending devuelve todas las pendientes (solo moderador)

---

### Sesión 1.16 — Evaluación de sesiones ✅ COMPLETADA (2026-04-06)

**Branch:** `feature/s116-evaluaciones` → mergeado a `main`
**Repos:** `eventos-backend` + `eventos-app`
**Nuevas dependencias:** ninguna

**Objetivo:** Al terminar una sesión, el asistente puede evaluarla (stars + comentario). Admin ve resultados agregados.

**Lo implementado:**
- Migración `session_ratings`: `session_id`, `attendee_id`, `rating` (unsignedTinyInteger 1-5), `comment` nullable
- UNIQUE `(session_id, attendee_id)` — una evaluación por sesión por asistente (backend previene doble voto)
- `POST /api/v1/events/{eventId}/sessions/{sessionId}/rate` — disponible en cualquier estado de sesión, evita duplicados (409)
- `GET /api/v1/events/{eventId}/sessions/{sessionId}/ratings` — promedio, distribución 1-5, comentarios (solo organizer/moderador)
- Filament `SessionRatingResource`: lista con evento/sesión/asistente/rating-estrellas, export CSV, badge, infolist en vista detalle
- App `useSessionRating`: `submitRating` + persistencia MMKV `{sessionId: rating}` por evento
- App `RatingModal`: bottom sheet, 5 estrellas interactivas, reset automático al abrir, etiqueta por valor, comentario opcional
- `AgendaScreen`: botón "Evaluar" en todas las sesiones; tras calificar muestra `★★★☆☆ Mi nota` bloqueado (re-render con `localRatings` state)
- `session-stream/[id].tsx`: modal automático si `status=finished` y no evaluada (1.5s delay)
- Fix: `SelectFilter` con relación anidada removido (Filament no soporta dot notation en filtros)
- Fix: 409 del backend también marca la sesión como evaluada localmente

**Tests Pest (8/8 ✅):**
- [x] Asistente envía evaluación → registrada en DB
- [x] Evaluación sin comentario válida (campo nullable)
- [x] Evaluar sesión en cualquier estado es permitido
- [x] Segundo rating del mismo asistente → 409
- [x] Endpoint requiere autenticación → 401
- [x] Admin obtiene promedio correcto (4,5,3 → avg 4.0)
- [x] GET ratings filtra por sesión correcta (no mezcla otras sesiones)
- [x] Asistente no puede ver ratings de admin → 403

**Definición de completado:** ✅ Asistente evalúa → botón cambia a estrellas read-only → admin ve lista y detalle en Filament → backend previene doble voto con UNIQUE.

---

### Sesión 1.17 — Photobooth / Memorias ✅ COMPLETADA (2026-04-07)

**Branch:** `feature/s117-photobooth` → mergeado a `main`
**Repos:** `eventos-backend` + `eventos-app`
**Nuevas dependencias:** `expo-image-picker`

**Objetivo:** Asistentes suben fotos del evento. Galería compartida moderada por admin.

**Lo implementado:**

Backend:
- Migración `event_photos`: `event_id`, `attendee_id`, `photo_url`, `caption`, `status` (pending/approved/rejected), `likes_count`
- Migración `event_photo_likes`: UNIQUE(`event_photo_id`, `attendee_id`) — like idempotente
- Migración `events` +2 columnas: `photos_auto_approve` (bool), `max_photos_per_attendee` (int, 0=sin límite)
- `POST /api/v1/events/{id}/photos` — multipart → StorageService → R2/disco. Respeta auto-aprobación y límite por asistente
- `GET /api/v1/events/{id}/photos` — galería pública (solo approved, paginada con likes y `liked` boolean)
- `GET /api/v1/events/{id}/photos/mine` — mis fotos (pending + approved, sin rechazadas)
- `POST /api/v1/events/{id}/photos/{id}/like` — like idempotente (firstOrCreate + increment)
- `DELETE /api/v1/events/{id}/photos/{id}/like` — unlike (decrement)
- Filament `EventPhotoResource` (grupo Interacción): lista con thumbnail, estado badge, moderar individual y bulk (aprobar/rechazar), badge fotos pendientes en nav
- Filament `EventPhotoSettingsResource` (grupo Interacción): toggle auto-aprobar + límite por asistente por evento
- `EventPhoto` model + `EventPhotoLike` model + `EventPhotoFactory`
- `PhotoSeeder`: 8 fotos de prueba (6 approved, 1 pending, 1 rejected) con likes entre asistentes
- Módulo `fotos` agregado al `ModuleTemplateSeeder` (template congreso, sort_order 12)

App:
- `expo-image-picker` instalado + permiso iOS en `app.json`
- `api.upload()` — método multipart/form-data en `lib/api.ts` (timeout 30s)
- `fixStorageUrl()` — reemplaza hostname local (`eventos-backend.test`) por IP del `.env` para desarrollo
- `hooks/usePhotos.ts`: `usePhotoGallery` (approved paginadas), `useMyPhotos` (pending+approved propias), `usePhotoActions` (upload + toggleLike optimistic)
- `app/(app)/fotos.tsx`: pantalla completa con 2 tabs (Galería / Mis fotos)
  - Galería: grid 3 columnas (FlashList), pull-to-refresh, likes optimísticos
  - Mis fotos: lista con estado (✓ Publicada / ⏳ En revisión)
  - Modal subir: `ImagePicker`, preview, caption (max 200), feedback post-subida
  - Modal visor fullscreen: **swipe horizontal** (FlatList paginado), like, caption+autor, indicador "3/6"
- `ModuleMenu.tsx`: slugs `fotos`, `photobooth`, `memorias` → `/fotos` + emoji `camera`/`photo`/`image`
- Fotos rechazadas: simplemente no se muestran (ni al dueño). El dueño puede volver a subir sin penalización.

**Tests Pest (8/8 ✅):**
- [x] Foto subida queda en `status=pending` (moderación manual activa)
- [x] Con auto-aprobación activada → `status=approved`
- [x] Foto `pending` no aparece en GET galería pública
- [x] Admin aprueba → aparece en galería pública
- [x] Foto rechazada no aparece en galería pública
- [x] `POST /photos/{id}/like` agrega like, incrementa `likes_count`
- [x] Doble like del mismo usuario → idempotente (no duplica)
- [x] `DELETE /photos/{id}/like` quita like correctamente

**Notas técnicas:**
- Likes sin Socket.IO — optimistic update en frontend + fetch al refrescar. No vale la complejidad de socket para contadores de likes.
- URLs de storage en desarrollo: `eventos-backend.test` no es resoluble desde el celular. `fixStorageUrl()` reemplaza por la IP del `EXPO_PUBLIC_API_URL`. En producción con R2 las URLs son absolutas y no necesitan reemplazo.
- FlashList v2 no tiene `estimatedItemSize` — removido.
- Límite de fotos: solo cuenta `pending` + `approved` (rechazadas no penalizan al asistente).

**Definición de completado:** ✅ Asistente sube foto → admin aprueba en Filament → aparece en galería de todos → swipe entre fotos → likes optimísticos.

---

### Sesión 1.18 — Certificados PDF

**Branch:** `feature/s118-certificados`
**Repos:** `eventos-backend`
**Nuevas dependencias:** `spatie/browsershot` (headless Chrome — verificar Laragon)

**Objetivo:** Admin genera certificados de asistencia/participación en PDF para los asistentes.

**Scope:**
- Tabla `certificates`: `event_id`, `attendee_id`, `type` (attendance/speaker/sponsor), `issued_at`, `file_url`, `download_count`
- Template Blade del certificado: nombre del evento, nombre del asistente, fecha, logo, firma digital (imagen)
- `GET /api/v1/me/certificates` — mis certificados descargables
- `POST /api/v1/admin/events/{id}/certificates/generate` — genera para todos los asistentes con check-in (job en queue)
- `POST /api/v1/admin/certificates/{id}/revoke` — revocar certificado individual
- Filament: `CertificateResource` — lista, acción "Generar todos", acción "Enviar por email"
- Email automático con PDF adjunto al generar (usa `SendEmailJob` existente)
- App: pantalla "Mis certificados" con botón descargar/compartir

**Tests Pest (objetivo: ~6 tests):**
- [ ] `GET /me/certificates` devuelve solo los certificados del usuario autenticado
- [ ] Certificado revocado no aparece en `GET /me/certificates`
- [ ] `GenerateCertificatesJob` crea un `certificate` por cada asistente con `check_in` registrado
- [ ] Asistente sin check-in no recibe certificado al ejecutar el job
- [ ] Certificado revocado registra `revoked_at` en DB
- [ ] Endpoint requiere autenticación → 401

**Definición de completado:** Admin genera certificados → asistentes reciben email + pueden descargar desde la app.

---

### Sesión 1.19 — Reporte post-evento PDF

**Branch:** `feature/s119-reporte-post-evento`
**Repos:** `eventos-backend`
**Nuevas dependencias:** `spatie/browsershot` (ya instalado en S1.18)

**Objetivo:** Al terminar el evento, el admin descarga un reporte ejecutivo en PDF con métricas clave.

**Scope:**
- Template Blade del reporte: asistencia total vs registrados, check-ins por hora, módulos más usados, encuestas (resultados), evaluaciones (promedios), leads capturados, emails enviados/abiertos, top speakers por rating
- `GET /api/v1/admin/events/{id}/report` — genera PDF (browsershot, job en queue) + devuelve URL
- Filament: botón "Generar reporte" en EventResource → notificación campana con link al PDF
- Datos agregados desde: `check_ins`, `activity_log`, `session_ratings`, `leads`, `live_poll_votes`, `email_logs`

**Tests Pest (objetivo: ~5 tests):**
- [ ] Endpoint solo accesible con rol `event_admin` o `org_admin` → asistente recibe 403
- [ ] Reporte incluye `total_checkins` correcto (coincide con registros en `check_ins`)
- [ ] Reporte incluye `avg_session_rating` correcto si hay evaluaciones
- [ ] Reporte con evento sin datos → 200 con métricas en 0 (no error)
- [ ] Job encolado correctamente al llamar el endpoint (Horizon queue visible)

**Definición de completado:** Admin toca "Generar reporte" → recibe PDF con métricas del evento.

---

### Sesión 1.20 — Analytics avanzado

**Branch:** `feature/s120-analytics`
**Repos:** `eventos-backend`
**Nuevas dependencias:** ninguna (`activity_log` ya existe)

**Objetivo:** Dashboard de analítica en Filament con métricas de uso del evento en tiempo real y tendencias.

**Scope:**
- Filament page `EventAnalytics` — accesible desde EventResource
- Métricas en tiempo real (polling 30s desde Filament):
  - Asistentes online ahora (Redis SCARD `event:{id}:online`)
  - Check-ins del día (curva por hora)
  - Módulos más visitados (top 5 desde `activity_log`)
  - Mensajes de chat enviados en la última hora
  - Votos en encuestas activas
- Métricas históricas:
  - Retención: asistentes que abrieron la app N días consecutivos
  - Funnel: registrado → check-in → usó networking → evaluó sesión
  - Heatmap de actividad por hora del día
- Export CSV de `activity_log` filtrado por evento/fecha/tipo

**Tests Pest (objetivo: ~5 tests):**
- [ ] Endpoint analytics solo accesible por admins del evento → 403 para asistente
- [ ] `GET /admin/events/{id}/analytics` devuelve `total_checkins`, `online_now`, `top_modules`
- [ ] Export CSV de `activity_log` devuelve `Content-Type: text/csv`
- [ ] Filtro por fecha en export devuelve solo filas dentro del rango
- [ ] Evento de otro organizador no accesible → 403

**Definición de completado:** Admin abre Analytics → ve métricas en tiempo real actualizadas cada 30s. Puede exportar actividad cruda.

---

### Sesión 1.18 — Matchmaking por intereses ✅ COMPLETADA (2026-04-07)

**Branch:** mergeado a `main`
**Repos:** `eventos-backend` + `eventos-app`

**Lo implementado:**
- `GET /events/{id}/suggested-contacts` — overlap de `attendee_interests`, ordena DESC por cantidad en común, excluye contactos/bloqueados/ocultos, max 20
- `GET/PUT /events/{id}/my-interests` — ver y editar intereses desde perfil
- App: sección "Sugeridos para ti" en tab Networking (cards horizontales con tags), componente `MyInterests` en perfil (3 roles)
- Onboarding survey obligatorio (botón deshabilitado sin selección)
- 7 tests Pest + 1 test common_tags

**Tests Pest (7/7 ✅):**
- [x] Asistente sin intereses registrados → `suggested_contacts` vacío, no error
- [x] Asistente con 3 intereses comunes aparece antes que uno con 1 interés en común (ordenado DESC)
- [x] Ya-contactos mutuos excluidos de sugerencias
- [x] Asistentes bloqueados excluidos de sugerencias
- [x] `networking_visible=false` excluye al asistente de sugerencias de otros
- [x] Endpoint requiere autenticación → 401
- [x] Incluye common_tags con labels de los intereses compartidos

**Definición de completado:** ✅ Intereses obligatorios en onboarding, editables desde perfil. Sugerencias por compatibilidad en Networking.

---

### Sesión 1.19 — Social Wall ✅ COMPLETADA (2026-04-07)

**Branch:** mergeado a `main`
**Repos:** `eventos-backend` + `eventos-app` + `eventos-socket`

**Objetivo:** Feed social del evento con posts, comentarios, likes y moderación.

**Lo implementado:**
- Tablas: `wall_posts` (body, photo_url, status pending/published/hidden, likes_count, comments_count), `wall_comments`, `wall_post_likes`
- Campos en `events`: `wall_auto_approve`, `max_wall_posts_per_attendee` (toggleable + límite)
- `POST/GET /events/{id}/wall` — crear post multipart + feed cursor paginado (solo published)
- `POST/DELETE /events/{id}/wall/{id}/like` — like idempotente
- `POST/GET /events/{id}/wall/{id}/comments` — comentar + listar
- Socket.IO: `/internal/wall/broadcast` → emite `wall:post` y `wall:comment` al event room en tiempo real
- Filament: `WallPostResource` (moderar individual/bulk, publicar/ocultar, badge pendientes) + `WallSettingsResource`
- App: pantalla `/social` con feed (PostCards + avatar + likes + comentarios), modal crear post con foto, modal comentarios, socket real-time
- Mensaje dinámico: auto-approve ON → "¡Publicado!" / OFF → "En revisión"
- `WallSeeder`: 10 posts, 10 comentarios con emojis, 21 likes

**Tests Pest (10/10 ✅)**

---

### Sesión 1.20 — Gamification + Leaderboard ✅ COMPLETADA (2026-04-07)

**Branch:** mergeado a `main`
**Repos:** `eventos-backend` + `eventos-app`

**Objetivo:** Sistema de puntos configurable por acción, leaderboard, trivia en stands.

**Lo implementado:**

Backend:
- `PointsService`: config toggleable por acción + puntos personalizables por evento (JSON `gamification_config`), daily_max, prevención duplicados por reference
- `points_log`: event_id, attendee_id, action, points, reference_type, reference_id
- `stand_trivias` + `stand_trivia_answers`: preguntas por stand con respuesta correcta y bonus_points
- Campos en sponsors: `visit_points` (personalizable por stand), `trivia_enabled`
- `GamificationController`: leaderboard top 50 + my_position, mis puntos con status de acciones (completada/pendiente), visit-stand con puntos por sponsor, trivia con respuesta correcta/incorrecta
- `AwardsPoints` trait enganchado en 7 controladores: CheckinController, RatingController, QuestionController, PollController, NetworkingController (ambos ganan), WallController, EventPhotoController
- `LikesMilestoneService`: milestones a 5/10/20/50/100 likes totales (posts+fotos), enganchado en like de wall y photos
- Chat: puntos por sesión (1 vez) enganchado en `/internal/chat/message`
- Puntos al aprobar posts/fotos desde Filament (no solo en auto-approve)
- Filament: `GamificationSettingsResource` (toggles por acción + puntos), trivia como repeater en `SponsorResource`

13 acciones configurables:
- checkin, visit_stand, stand_trivia, rate_session, ask_question, wall_post, upload_photo, likes_milestone, wall_comment (daily_max), connect, vote_poll, complete_interests, chat_session

App:
- Pantalla `/leaderboard` con 2 tabs: "Mis puntos" (total + checklist acciones con ✓/pendiente) y "Ranking" (leaderboard top 50 con medallas)
- Botón "Registrar visita al stand" en sponsor detail → puntos + TriviaModal
- TriviaModal: preguntas con opciones, feedback correcto/incorrecto, bonus points, navegación multi-pregunta
- `useGamification.ts`: hooks para leaderboard, my-points, visitStand, answerTrivia

Fixes incluidos:
- `useSessionRating` reescrito con react-query en vez de MMKV — persiste correctamente en Expo Go
- Upload foto/post: muestra mensaje real del backend (ej. "Límite alcanzado")
- Social wall: invalida `my-points` al comentar/publicar para actualización inmediata

**Tests Pest (9/9 ✅)** — 218 total suite verde

**Definición de completado:** ✅ Admin configura acciones/puntos/trivias → asistente participa → gana puntos → ve checklist + ranking.

---

### Sesión 1.21 — Passport Contest ✅ COMPLETADA (2026-04-07)

**Branch:** mergeado a `main`
**Repos:** `eventos-backend` + `eventos-app`

**Objetivo:** Ruta de stands — al escanear el QR del asistente como lead, se registra la visita al stand + stamp en el pasaporte.

**Lo implementado:**
- [x] Tabla `passport_stamps`: UNIQUE(`event_id`, `attendee_id`, `sponsor_id`) con `stamped_at`
- [x] Campos en `events`: `passport_enabled`, `passport_required_stamps`, `passport_prize`
- [x] Campo en `sponsors`: `passport_enabled` (admin elige qué stands participan)
- [x] `GET /events/{id}/my-passport` — stands con estado stamped/pending, progreso, completed, prize
- [x] Hook en `LeadController`: al capturar lead → stamp automático + puntos `visit_stand` (puntos por sponsor)
- [x] Filament `PassportSettingsResource`: toggle passport, stands requeridos, premio toggle opcional (limpia campo al desactivar)
- [x] Filament `SponsorResource`: toggle `passport_enabled` por stand
- [x] App pantalla `/passport`: grid de stands con logo/tier/check verde, barra progreso, mensaje completado, premio
- [x] Hint: "Visita los stands y pide al vendedor que escanee tu QR para registrar tu visita"
- [x] Módulo `passport`/`pasaporte` en ModuleMenu + template seeder

**Tests Pest (6/6 ✅):**
- [x] Passport muestra stands con estado stamped/pending
- [x] Stamp duplicado no se crea (UNIQUE firstOrCreate)
- [x] Al completar required_stamps → completed=true
- [x] Sponsor sin passport_enabled no aparece en la lista
- [x] Passport deshabilitado → data null
- [x] Endpoint requiere autenticación → 401

**Definición de completado:** ✅ Vendedor escanea QR del asistente → lead + stamp + puntos → asistente ve su pasaporte actualizado.

---

### Sesión 1.22 — Registro personalizable + Import/Export ✅ COMPLETADA (2026-04-07)

**Branch:** `feature/s122-registro`
**Repos:** `eventos-backend` + `eventos-app`
**Nuevas dependencias:** `maatwebsite/excel` (backend, para import/export CSV/Excel)

**Objetivo:** Registro público con campos 100% personalizables por evento, importación masiva de asistentes con link de activación, y exportación con columnas dinámicas.

---

#### Contexto

El spec maestro (v2.5) incluye `registration_fields` y `registration_field_values` en la Sesión 2 (Auth), pero solo se implementó login por QR/email en S1.1. Esta sesión completa lo que faltó: registro público, campos dinámicos, import/export, y edición desde perfil.

---

#### A. Modelo de datos

**Migration: `registration_fields`**
```
id, event_id, label, field_type, placeholder, options (JSON), 
preset (nullable), required, editable_after (bool), sort_order,
created_at, updated_at
```

**field_type ENUM:**
| Tipo | Uso | Componente app |
|------|-----|---------------|
| `text` | Empresa, Cargo | TextInput |
| `email` | Email alternativo | TextInput keyboardType=email |
| `phone` | Teléfono | TextInput + prefijo país |
| `number` | Años experiencia | TextInput keyboardType=numeric |
| `date` | Fecha nacimiento | DatePicker nativo |
| `url` | LinkedIn, website | TextInput keyboardType=url |
| `textarea` | Bio, comentarios | TextInput multiline |
| `select` | Industria (lista corta) | BottomSheet picker |
| `searchable_select` | País, Ciudad (lista larga) | BottomSheet + buscador |
| `checkbox` | ¿Requiere certificado? | Switch sí/no |
| `checkbox_group` | Intereses múltiples | Lista de checkboxes, valor JSON array |

**preset** (para listas largas predefinidas):
- `null` → opciones manuales (JSON en `options`)
- `countries` → carga lista de países
- `cities_co` → ciudades de Colombia
- Extensible a futuros presets

**Migration: `registration_field_values`**
```
id, attendee_id, field_id, value (TEXT), 
UNIQUE(attendee_id, field_id)
```

**Campos adicionales en `events`:**
```
registration_mode ENUM('invite_only','open','hybrid') DEFAULT 'open'
```
(ya existen: `registration_opens_at`, `registration_closes_at`, `registration_requires_approval`)

**Campos adicionales en `users`:**
```
invitation_token VARCHAR(64) NULL UNIQUE
activated_at TIMESTAMP NULL
```

---

#### B. Tres flujos de ingreso

**Flujo A — Registro público (open/hybrid)**
1. Asistente abre link del evento o la app
2. Tap "Crear cuenta" → formulario dinámico (campos fijos + campos del evento)
3. Submit → `POST /api/v1/auth/register`
4. Si `requires_approval=false` → auto-aprobado, token Sanctum, va al Home
5. Si `requires_approval=true` → pantalla "Registro pendiente", email `registration_pending`

**Flujo B — Importación (invite_only/hybrid)**
1. Admin sube CSV/Excel en Filament con columnas mapeadas a registration_fields
2. Se crean User (sin contraseña) + Attendee + registration_field_values
3. Se envía email `InvitationMail` con link: `/join/{invitation_token}`
4. Asistente abre link → pantalla de activación (solo contraseña + confirmar)
5. `POST /api/v1/auth/activate` → asigna contraseña, `activated_at=now()`, token Sanctum

**Opción contraseña por teléfono:** al importar, el admin puede elegir "Usar teléfono como contraseña temporal". El email dice "Tu contraseña es tu número de teléfono". Al primer login se sugiere cambiarla.

**Flujo C — Híbrido**
- `registration_mode='hybrid'`: permite import + registro público simultáneamente

---

#### C. Admin — Filament

**RegistrationFieldResource (CRUD + reorder):**
- Lista reordenable (drag & drop via `sort_order`)
- Form: label, field_type (select), placeholder, options (Repeater para select/checkbox_group), preset (select condicional), required (toggle), editable_after (toggle)
- Preview visual del formulario (opcional, nice-to-have)

**ImportAction en AttendeeResource:**
- Botón "Importar asistentes" → modal upload CSV/Excel
- Paso 1: sube archivo
- Paso 2: mapeo de columnas CSV → campos fijos (nombre, email, teléfono) + registration_fields del evento
- Paso 3: elige modo contraseña (link de activación / teléfono como contraseña)
- Paso 4: resumen + confirmar → Job en queue que crea users + attendees + field_values + envía emails

**ExportAction en AttendeeResource:**
- Botón "Exportar" → modal con opciones:
  - Formato: CSV / Excel
  - Columnas fijas: nombre, email, rol, estado, check-in
  - Columnas dinámicas: cada registration_field del evento como columna
  - Filtros: solo checked-in, solo aprobados, rango de fechas
- Descarga directa o job en queue si > 1000 registros

**Config del evento (EventResource):**
- `registration_mode`: solo invitados / abierto / híbrido
- `registration_requires_approval`: toggle
- `registration_opens_at` / `registration_closes_at`: date pickers

**Aprobación manual:**
- En AttendeeResource: filtro "Pendientes de aprobación"
- Bulk action "Aprobar seleccionados" → `registration_approved_at = now()` + email `registration_approval`

---

#### D. App — Expo

**RegisterScreen (formulario dinámico):**
- `GET /api/v1/events/{id}/registration-fields` → lista de campos con tipo, opciones, required, order
- Campos fijos arriba: nombre, email, contraseña, confirmar contraseña
- Campos dinámicos abajo: renderizados según field_type
- Checkbox de consentimiento (privacy policy + terms) siempre al final
- Validación en tiempo real: campos requeridos, formato email, contraseña min 8 chars
- Botón deshabilitado hasta que todo lo requerido esté completo

**ActivateAccountScreen (solo para importados):**
- Llega por deep link `/join/{token}`
- Muestra "¡Hola {nombre}!" + solo campos de contraseña + confirmar
- `POST /api/v1/auth/activate`

**PendingApprovalScreen:**
- Se muestra cuando `registration_approved_at IS NULL`
- "Tu registro está pendiente de aprobación. Te notificaremos cuando sea aprobado."
- Pull-to-refresh o check periódico

**Perfil — edición de campos de registro:**
- `GET /api/v1/me/registration-fields` → campos con valor actual + `editable_after`
- Campos con `editable_after=true` → editables (ícono lápiz)
- Campos con `editable_after=false` → solo lectura (ícono candado)
- Campos opcionales vacíos → placeholder invitando a completar
- `PUT /api/v1/me/registration-fields` → actualiza solo campos editables

---

#### E. Endpoints API

| Método | Ruta | Descripción | Auth |
|--------|------|-------------|------|
| `GET` | `/api/v1/events/{id}/registration-fields` | Campos del formulario (público) | No |
| `POST` | `/api/v1/auth/register` | Registro público | No |
| `POST` | `/api/v1/auth/activate` | Activar cuenta importada | No |
| `GET` | `/api/v1/me/registration-fields` | Mis valores + editabilidad | Sí |
| `PUT` | `/api/v1/me/registration-fields` | Editar mis campos | Sí |

---

#### F. Emails

| Email | Trigger |
|-------|---------|
| `InvitationMail` | Import masivo → link de activación por asistente |
| `RegistrationPendingMail` | Registro con requires_approval=true |
| `RegistrationApprovalMail` | Admin aprueba registro manualmente |

---

#### G. Tests Pest (objetivo: ~12 tests)

- [ ] `POST /register` crea user + attendee + field_values + consent_log + qr_token
- [ ] `POST /register` con requires_approval → `registration_approved_at IS NULL`
- [ ] `POST /register` auto-aprobado → retorna token Sanctum
- [ ] `POST /register` valida campos required dinámicamente (falla si falta campo requerido)
- [ ] `POST /register` rechaza si registration_mode='invite_only'
- [ ] `POST /register` rechaza fuera de ventana (opens_at / closes_at)
- [ ] `POST /activate` con token válido → asigna contraseña + activated_at
- [ ] `POST /activate` con token inválido → 404
- [ ] `GET /events/{id}/registration-fields` devuelve campos ordenados por sort_order
- [ ] `PUT /me/registration-fields` actualiza solo campos con editable_after=true
- [ ] `PUT /me/registration-fields` ignora campos con editable_after=false
- [ ] Import CSV crea users + attendees + field_values (Feature test)

---

**Lo implementado (adicional al plan original):**
- [x] Toggle `registration_enabled` manual en Filament (independiente de fechas)
- [x] Plantilla de importación descargable (CSV/Excel con headers dinámicos)
- [x] Pantalla PendingApproval con botón "Verificar estado" + bloqueo en app layout
- [x] Deep link `/join/{token}` → web route → `eventos://activate-account?token=`
- [x] Checkbox de consentimiento visible (Ley 1581) — no hardcoded
- [x] Validación `max_attendees` antes de permitir registro
- [x] `AttendeeResource` expone `registration_approved_at` al frontend
- [x] Link "Regístrate" en el login del onboarding (donde realmente se ve)
- [x] Staff/Admins separados de Asistentes en Filament (UserResource filtrado)
- [x] Seeder con 10 campos de ejemplo (todos los tipos)

**Definición de completado:** ✅
1. Admin configura campos de registro por evento (todos los tipos) → asistente ve formulario dinámico y se registra
2. Admin importa CSV → asistentes reciben email → activan cuenta con contraseña
3. Admin exporta asistentes con columnas dinámicas en CSV/Excel
4. Asistente edita sus campos opcionales desde el perfil
5. Flujo de aprobación manual funciona end-to-end
6. Registro se bloquea si disabled, invite_only, fuera de ventana, o evento lleno
7. Checkbox consentimiento obligatorio (Ley 1581)

---

### Sesión 1.23 — Permisos granulares Filament (roles admin) ⏳ PENDIENTE

**Branch:** `feature/s123-filament-permissions`
**Repos:** `eventos-backend`
**Nuevas dependencias:** ninguna (Spatie ya instalado)

**Objetivo:** Cada rol de admin en Filament ve y puede hacer solo lo que le corresponde. Staff separado de asistentes.

---

#### Roles y acceso

| Rol | Acceso en Filament |
|-----|-------------------|
| `super_admin` | Todo — eventos, orgs, usuarios, config global |
| `admin` | Todo de su(s) evento(s) — no ve config global ni otros eventos |
| `admin_content` | Agenda, speakers, docs, anuncios, custom pages, banners, encuestas |
| `admin_notifs` | Notificaciones push, emails, plantillas email |
| `admin_analytics` | Reportes, leaderboard, ratings, logs (solo lectura) |
| `monitor` (nuevo) | Lectura de todo el evento — no crea, no edita, no borra |

#### Implementación

1. **Spatie Permissions**: crear permissions granulares (`view_attendees`, `manage_sessions`, `send_notifications`, etc.) y asignarlos a cada rol
2. **Filament Policies**: cada Resource tiene una Policy que chequea `$user->can('view_attendees')` etc.
3. **Scope por evento**: admin/admin_* solo ven datos de eventos donde son attendee con ese rol
4. **Selector de evento activo**: dropdown en header de Filament → filtra TODOS los Resources al evento seleccionado (session-based)
5. **Staff vs Asistentes**: UserResource muestra solo staff (ya implementado), AttendeeAdminResource muestra asistentes del evento
6. **Navigation groups**: recursos agrupados y ocultos según permisos del usuario logueado
7. **Monitor**: rol de solo lectura — `can()` retorna true para `view*`, false para `create/update/delete`

#### Tests Pest (objetivo: ~8 tests)

- [ ] super_admin ve todos los recursos
- [ ] admin_content solo ve sesiones, speakers, docs, anuncios
- [ ] admin_notifs solo ve notificaciones y emails
- [ ] admin_analytics solo ve reportes (read-only)
- [ ] monitor puede ver pero no crear/editar/borrar
- [ ] admin no ve eventos de otra organización
- [ ] attendee normal no puede acceder al panel Filament
- [ ] Permisos se asignan correctamente al crear admin desde Filament

**Definición de completado:** Cada rol ve exactamente lo que debe ver, no puede acceder a lo que no le corresponde, y los tests lo verifican.

---

### Sesión 1.25 — Floor plan del venue

**Branch:** `feature/s125-floor-plan`
**Repos:** `eventos-backend` + `eventos-app`
**Nuevas dependencias:** ninguna (imagen estática + coordenadas)

**Objetivo:** Admin sube el plano del recinto e indica la posición de cada stand. Asistentes ven mapa interactivo con stands clickeables.

**Scope:**
- Tabla `venue_maps`: `event_id`, `image_url`, `width`, `height` (dimensiones en px para calcular %)
- Tabla `map_pins`: `map_id`, `sponsor_id` nullable, `label`, `x_percent`, `y_percent`, `icon_type` (stand/entrance/stage/bathroom/food)
- `GET /api/v1/events/{id}/venue-map` — imagen + pins con info del stand asociado
- Filament: subir imagen del plano (StorageService R2), colocar pins con coordenadas (campos numéricos X/Y %)
- App: pantalla "Mapa" — imagen con zoom/pan (`react-native-gesture-handler` ya instalado), pins superpuestos, tap en pin → popup con nombre/descripción del stand → navegar a su perfil

**Tests Pest (objetivo: ~4 tests):**
- [ ] `GET /events/{id}/venue-map` devuelve `image_url` + array de `pins` con coordenadas
- [ ] Pin con `sponsor_id` incluye datos del sponsor (`name`, `logo_url`, `tier`)
- [ ] Evento sin mapa configurado → 200 con `data: null` (no 404)
- [ ] Pins de otro evento no aparecen en la respuesta

**Definición de completado:** Admin sube plano + posiciona stands → asistente ve mapa interactivo, toca un stand y ve su info.

---

### Sesión 1.26 — Reports exportables detallados

**Branch:** `feature/s126-reports`
**Repos:** `eventos-backend`
**Nuevas dependencias:** `spatie/browsershot` (ya instalado en S1.18)

**Objetivo:** Reportes adicionales para el organizador: Q&A, chat, networking lounge, sesiones favoritas. CSV + PDF.

**Scope:**
- Reporte actividad Q&A: preguntas por sesión, upvotes, % respondidas
- Reporte chat: mensajes por sesión, asistentes más activos, pico de actividad por hora
- Reporte networking lounge: asistentes con más conexiones, solicitudes enviadas/recibidas
- Reporte bookmarks: sesiones más guardadas como favorito
- Todos exportables en CSV (inmediato) y PDF (browsershot, job en queue)
- Filament: `ReportsResource` con tabs por tipo de reporte, filtros por evento y rango de fechas

**Tests Pest (objetivo: ~6 tests):**
- [ ] Reporte Q&A devuelve preguntas del evento correcto (no mezcla con otros eventos)
- [ ] Reporte chat devuelve mensajes agrupados por sesión
- [ ] Reporte bookmarks devuelve sesiones ordenadas por `favorites_count` DESC
- [ ] Export CSV devuelve `Content-Type: text/csv` con cabeceras correctas
- [ ] Filtro por rango de fechas excluye registros fuera del rango
- [ ] Endpoint solo accesible por admins → 403 para asistente

**Definición de completado:** Admin selecciona tipo de reporte + rango de fechas → descarga CSV o PDF con datos correctos.

---

## ─────────────────────────────────────────

## SESIÓN 1.x — Pulido funcional (mejoras pre-deploy)

## ─────────────────────────────────────────

**Cuándo:** Después de 1.26, antes de Stress Testing.
**Objetivo:** Cerrar todos los pendientes funcionales detectados durante el desarrollo que no encajaron en una sesión específica.

**Items a resolver:**

### App
- [x] **Tab Inicio del vendedor** — card resumen: leads hoy, total leads, equipo activo
- [x] **Módulos visibles por rol** — vendedor ahora tiene anuncios, patrocinadores, encuestas + template seeder actualizado
- [x] **Reset onboarding** — botón "Ver introducción de nuevo" en perfil (3 roles)
- [ ] **Fix z-index controles onboarding** — requiere dev build para probar → mover a sesión de deploy
- [x] **Refetch automático** — `refetchInterval` en wall (30s), leaderboard (30s), mis puntos (15s), passport (15s), fotos (30s)

### Backend / Filament
- ~~Tracking de aperturas email (pixel 1×1)~~ → movido a Fase 3
- [x] **Admin módulos — rediseño form de creación** — Select "Tipo de módulo" con presets → auto-rellena slug/nombre/ícono/roles. Custom disponible.
- [x] **Speaker ↔ Sesión bidireccional en Filament** — SpeakersRelationManager en EventSessionResource (attach/detach)

### Pendiente para Web (Fase 2)
- [ ] **Pantallas web para password reset y verificación de email** — cuando se implemente Next.js, reemplazar el `deep-link-redirect.blade.php` con formulario web completo. La página actual redirige al deep link `eventos://` — funciona pero no es ideal sin la app instalada.

---

## ─────────────────────────────────────────

## SESIÓN 1.x — Stress & Load Testing (k6)

## ─────────────────────────────────────────

**Cuándo:** Después del Pulido funcional, antes del barrido UI.
**Por qué antes de UI:** Si el stress test revela que hay que cambiar arquitectura, los componentes UI ya rediseñados no se ven afectados. Arquitectura primero, estética después.

**Herramienta:** [k6](https://k6.io/) — scripting en JS, open source, métricas en tiempo real.

**Escenarios a simular:**

| Escenario | Usuarios concurrentes | Objetivo |
|---|---|---|
| Login masivo | 500 simultáneos | p95 < 2s, 0% errores |
| Check-in storm | 500 en 60s | Redis INCR atómico, 0 duplicados |
| GET módulos / agenda | 1,000 simultáneos | p95 < 200ms (debe venir de Redis) |
| Votación en encuesta | 300 votes/10s | Socket.IO aguanta, 0 votos perdidos |
| Chat en sesión | 200 mensajes/min | Latencia < 500ms p95 |
| Descarga CSV leads | 50 simultáneos | Queue procesa sin timeouts |

**Métricas a capturar:**

- Tiempo de respuesta: p50, p95, p99
- Tasa de error por endpoint
- Uso de CPU/RAM del VPS bajo carga
- Hit rate de Redis cache (debe ser > 95% en endpoints GET de lista)
- Cola de jobs Horizon: profundidad máxima, tiempo de procesamiento

**Entregables de la sesión:**

- [ ] Scripts k6 para cada escenario (`tests/load/*.js`)
- [ ] Baseline report: qué soporta el VPS mínimo (2vCPU/4GB)
- [ ] Tabla: N asistentes → VPS recomendado (< 500 / 500-2,000 / 2,000-10,000)
- [ ] Lista de bottlenecks encontrados + fixes aplicados
- [ ] Documentación de configuración Nginx + PHP-FPM óptima para cada tier

**Definición de completado:** 500 usuarios concurrentes hacen check-in en 60 segundos sin errores. Reporte de capacidad documenta el límite real del hardware.

---

## ─────────────────────────────────────────

## SESIÓN UI — Barrido Visual Lumina Noir _(~80% completado al 2026-04-10)_

## ─────────────────────────────────────────

**Estado:** En progreso. Pantallas principales completadas, quedan ~5 pantallas secundarias + items transversales.
**Documento detallado:** `docs/ROADMAP-UIUX-LANDING.md` (PASO 5 tiene el desglose completo)

**Design system implementado:**

- Fondo: #0e0e0e (Lumina Noir), accent: primary_color dinamico por evento
- Fonts: Urbanist (body) + Plus Jakarta Sans (headlines)
- FloatingTabBar glassmorphism, LuminaToast, ScreenWrapper anti-flash
- ThemeProvider + useBranding hook + real-time branding via socket

**Pantallas completadas (Lumina Noir):**

- [x] Design tokens + ThemeProvider + fonts — 2026-04-07
- [x] Home (Hero, HappeningNow, GamificationHud, ModuleMenu, Mi QR) — 2026-04-07
- [x] Agenda (day strip, glass cards, tracks, heart particles, calendar) — 2026-04-08
- [x] Speakers (carousel breathing, detail hero, rating crystals, LinkedIn) — 2026-04-08
- [x] Streaming (split-screen, YouTube embed, session states) — 2026-04-08
- [x] Social (Feed + Memorias + Momentos unificado, stories, comments) — 2026-04-09
- [x] Sponsors (Brand Wall grid tiers, Brand Profile, trivia, contact form) — 2026-04-09
- [x] Profile (beam Ocean, edit modal, social links, stats) — 2026-04-09
- [x] Encuestas/PollSlides rediseno Lumina Noir — 2026-04-10
- [x] Chat sesion (emojis animados, cooldown, Enter=enviar) — 2026-04-10
- [x] Responsive audit 360dp (31 archivos, 12 pantallas SafeArea) — 2026-04-10

**Pendiente:**

- [ ] Networking (directorio + contactos + solicitudes) — UI funcional, falta Lumina Noir polish
- [ ] Gamificacion / Leaderboard — UI funcional, falta polish visual
- [ ] Matchmaking — UI funcional, falta polish visual
- [ ] Auth screens (login / registro) — rediseno visual
- [ ] Onboarding visual (bugs z-index Android, requiere dev build)
- [ ] Iconografia consistente (reemplazar emojis ModuleMenu por icon set + custom upload)
- [ ] Skeleton/EmptyState/Pull-to-refresh (componentes creados, integracion pendiente)
- [ ] Splash screen

---

## ─────────────────────────────────────────

## FASE 2 — Features complejas aplazadas

## ─────────────────────────────────────────

> **Decisión 2026-04-06:** Las sesiones 2.2–2.12 y 2.15–2.16 se movieron a Fase 1 (sesiones 1.14–1.26). Quedan en Fase 2 solo los features que requieren infraestructura nueva significativa o son proyectos propios.

| Sesión | Feature | Nuevas deps | Por qué aplazado |
| ------ | ------- | ----------- | ---------------- |
| 2.1 | **Web Next.js + Streaming** — portal web completo para asistentes virtuales: login, agenda, speakers, docs, anuncios, networking + embed de stream (YouTube Live, Vimeo, etc.) | `next`, `socket.io-client` (web) | Es un proyecto aparte (nuevo repo), no una sesión. Requiere diseño propio. |
| 2.2 | **Photo/Caption Contest** — depende de Social wall (S1.22). Galería de fotos con votos, caption contest. | `expo-image-picker` (ya en S1.x) | Depende de S1.22 completada. |
| 2.3 | **Video calls 1:1** — videollamada dentro del networking. Sala efímera con LiveKit. Solo app + web. | `livekit-client`, `@livekit/react-native-webrtc` | LiveKit = infraestructura de media server propia, costo mensual, complejidad alta. |
| 2.4 | **Proximity chat web (spatial audio)** — espacio virtual tipo Gather con audio espacial. Solo Next.js. | `livekit-server-sdk`, `@livekit/components-react` | El más complejo del roadmap. Depende de 2.1 (web) + 2.3 (LiveKit). |
| 2.5 | **Ruleta en vivo (reward virtuales)** — Presentador gira ruleta, backend detecta conectados en room, asigna puntos solo a presentes. Incentiva quedarse conectado. Animacion en streaming + app. | `socket.io` (ya existe) | Depende de gamificacion completa. |
| 2.6 | **Sorteo en vivo (jackpot)** — Boton "Participar" con countdown 30s, slot machine con fotos de participantes en pantalla display + streaming, ganador con foto grande + confetti. Presencial + virtual. | `socket.io` + `/display/` (ya existe) | Requiere pantalla display dedicada. |
| 2.7 | **Juegos interactivos en stands** — Vendedor escanea QR, app del asistente se convierte en control (joystick/botones), conecta via socket a juego Unity WebGL en pantalla del stand. Lead automatico + puntos. | `Unity WebGL`, `socket.io` | Requiere desarrollo Unity aparte. Diferencial de producto. |
| 2.8 | **Trivia live tipo Kahoot** — Preguntas en tiempo real durante sesion, ranking por velocidad, puntos gamificacion. | Base de encuestas (S1.10) | Requiere refactor de encuestas a modo competitivo. |
| 2.9 | **Networking speed-dating virtual** — Match aleatorio, timer 3 min, opcion conectar/pasar, puntos por interaccion. | `socket.io` | Depende de networking (S1.7). |
| 2.10 | **Subasta de puntos** — Premios se subastan en tiempo real (no precio fijo). Timer 60s, bids via socket. Premia a quien mas jugo. | `socket.io` | Depende de rewards (1.x). |
| 2.11 | **Donde esta el patrocinador** — Juego visual: logo se esconde, todos adivinan (presencial+virtual). Primeros 10 ganan puntos. Sponsor como entretenimiento. | `socket.io` + display | Juego simple pero muy visual. |
| 2.12 | **Game Bridge (Unity ↔ App)** — Celular como control de juegos Unity en stands. Vendedor escanea QR → app habilita joystick/botones → socket relay → Unity WebGL en TV del stand. DaVinci tiene juegos ya hechos. | `socket.io`, `Unity WebGL` | Solo el bridge, juegos ya existen. |
| 2.13 | **Momentos en Vivo** — Sistema flexible de momentos branded: admin configura en Filament (sponsor, tipo, titulo, ganador, logo). Publicar → push + socket + social wall + display. Un componente, infinitos usos (sorteos, hackatones, reconocimientos). | `socket.io`, Filament | Reemplaza momentos hardcodeados. |

> **Nota:** El asistente virtual en Fase 1 accede por app móvil (`app/(app)/(virtual)/` ya implementado). La web (2.1) se construye en Fase 2 porque su diferencial es "acceder sin descargar la app" — sin ese diferencial no aporta valor en Fase 1.

> **Nota streaming:** El streaming NO es propio — es un embed de la URL que el organizador ya tiene (YouTube Live, Vimeo, Streamyard). Cero costo adicional. Implementado en S1.14.


---

## ─────────────────────────────────────────

## FASE 3 — SaaS + Monetización _(aplazada)_

## ─────────────────────────────────────────

| Sesión | Feature                         | Nuevas deps cuando llegue                     |
| ------ | ------------------------------- | --------------------------------------------- |
| 3.1    | Multi-tenant + aislamiento de recursos | ninguna nueva                          |
| 3.2    | Stripe + facturación            | `laravel/cashier`                             |
| 3.3    | Data export (Ley 1581/GDPR)     | ninguna nueva                                 |
| 3.4    | Juegos Unity + Socket.IO bridge | `socket.io-client` Unity, Reanimated gestures |

**S3.1 — Multi-tenant + aislamiento de recursos (detalle):**

El problema: actualmente todos los eventos comparten servidor, BD, Redis, queues. Un evento de 5000 personas que sature la CPU tumba al de 100 personas.

Niveles de aislamiento a implementar:
1. **Queue isolation**: queues separadas por organización/evento (Horizon supervisors dedicados)
2. **Rate limiting por evento**: Nginx/Laravel throttle scoped a event_id, no global
3. **Redis databases**: cache (DB 0), queues (DB 1), sockets (DB 2) — ya parcial, falta por evento
4. **Database read replicas**: queries pesadas (analytics, exports) van a réplica de lectura
5. **Container isolation**: Docker Compose con recursos limitados por org (CPU/memory limits)
6. **Database por organización** (opcional, máximo aislamiento): cada org tiene su propia BD, solo la BD maestra tiene orgs+billing

Estrategia incremental:
- Deploy inicial: 1 servidor, mitigado con caching + queues + rate limiting (suficiente para < 5 eventos simultáneos)
- Primer cliente grande: horizontal scaling (2+ workers + load balancer + read replica)
- SaaS público: container isolation + DB por org

---

## Resumen de dependencias por sesión

### Backend (Laravel)

| Sesión        | Paquete                                                          |
| ------------- | ---------------------------------------------------------------- |
| 0.2           | sanctum, spatie/permission, filament, horizon, telescope, sentry |
| 1.3           | ezyang/htmlpurifier                                              |
| 1.11          | expo-notifications (FCM via Expo Push API)                       |
| 1.x (uploads) | league/flysystem-aws-s3-v3 (R2)                                  |
| 1.18+         | spatie/browsershot (certificados, reportes PDF)                  |
| F2.3          | livekit-server-sdk _(Fase 2 aplazada)_                           |
| 3.2           | laravel/cashier                                                  |

### App (Expo)

| Sesión        | Paquete                                                                                |
| ------------- | -------------------------------------------------------------------------------------- |
| 0.3           | expo-router, nativewind, reanimated, secure-store, mmkv, tanstack-query, zustand, i18n |
| 1.3           | expo-image, expo-file-system, @shopify/flash-list                                      |
| 1.4           | expo-camera, expo-keep-awake                                                           |
| 1.9           | socket.io-client                                                                       |
| 1.11          | expo-notifications                                                                     |
| 1.14          | react-native-webview (ya instalado en S1.3b — verificar)                               |
| 1.17          | expo-image-picker                                                                      |
| F2.3          | @livekit/react-native-webrtc _(Fase 2 aplazada — requiere dev build)_                  |

### Load Testing

| Sesión       | Herramienta |
| ------------ | ----------- |
| 1.x (Stress) | k6 (open source, scripts en JS, sin deps de proyecto) |

### Socket.IO (Node.js)

| Sesión | Paquete                                                     |
| ------ | ----------------------------------------------------------- |
| 0.4    | socket.io, @socket.io/redis-adapter, ioredis, axios, dotenv |
| F2.4   | livekit-client, @livekit/components-react _(Fase 2 aplazada)_ |

---

## Orden de implementación

```
FASE 0:   0.1 → 0.2 → 0.3 → 0.4

FASE 1 (funcional completa):
  1.1 → 1.2 → 1.3 → 1.4 → 1.5 → 1.6 → 1.7 → 1.8 → 1.9 → 1.10 → 1.11 → 1.12 → 1.13
  → 1.x(Storage) → 1.x(Banners) → 1.x-A → 1.x-B  ← ✅ completado
  → 1.14 → 1.15 → 1.16 → 1.17 → 1.18 → 1.19 → 1.20 → 1.21
  → 1.22 → 1.23 → 1.24 → 1.25 → 1.26
  → 1.x(Pulido funcional)
  → 1.x(Stress & Load Testing)  ← arquitectura validada antes de UI

SESIÓN UI:  Barrido visual completo (diseño una sola vez sobre código estable)

DEPLOY:     Docker + VPS + CI/CD + EAS Build

FASE 2:   cuando haya cliente con requerimiento de web o video calls
          2.1(Web Next.js) → 2.2(Photo Contest) → 2.3(Video calls) → 2.4(Proximity chat)

FASE 3:   cuando haya segundo cliente o plan de monetización
```

---

## ─────────────────────────────────────────

## NOTA OPERATIVA — Procesos requeridos en desarrollo (Windows)

## ─────────────────────────────────────────

En Linux (producción) `cron` maneja el scheduler automáticamente. En Windows local hay que correr dos procesos manualmente en terminales separadas:

```bash
# Terminal 1 — procesa los jobs en cola (push, exports, etc.)
php artisan queue:work --timeout=60

# Terminal 2 — dispara jobs programados cada minuto (receipts, scheduled notifications)
php artisan schedule:work
```

**Sin queue:work:** las notificaciones push se encolan pero nunca se envían.
**Sin schedule:work:** `CheckExpoReceiptsJob` no corre → "Entregadas/Abiertas" queda en 0.

En producción (Linux) esto se reemplaza por:
- `queue:work` supervisado con Supervisor o Laravel Horizon
- `schedule:run` en crontab: `* * * * * php /var/www/artisan schedule:run`

---

## ─────────────────────────────────────────

## SESIÓN 1.x — Deploy a producción + Docker

## ─────────────────────────────────────────

**Cuándo:** Antes del primer cliente real. Post S1.13 (emails) idealmente.
**Complejidad:** Media — son 3 servicios (Laravel + Socket + MySQL/Redis), Docker lo simplifica mucho.

### Arquitectura de producción

```
Internet
   │
   ├── app.eventos.com (HTTPS)
   │     └── Nginx → Laravel (PHP-FPM) + Queue Worker + Scheduler
   │
   ├── socket.eventos.com (WSS)
   │     └── Socket.IO Node.js
   │
   └── CDN / R2 — assets estáticos
```

### Opción recomendada: Docker Compose en VPS

Un solo `docker-compose.yml` con 6 servicios:

| Servicio | Imagen | Propósito |
|---|---|---|
| `app` | php:8.3-fpm + nginx | Laravel API + Filament |
| `queue` | mismo Dockerfile que app | `php artisan queue:work` |
| `scheduler` | mismo Dockerfile que app | `php artisan schedule:run` cada minuto |
| `socket` | node:20-alpine | Socket.IO server |
| `mysql` | mysql:8 | Base de datos |
| `redis` | redis:7-alpine | Cache + queues |

**Por qué Docker:**
- Un solo `docker compose up -d` levanta todo el stack
- Mismo entorno en dev y producción — elimina "en mi máquina funciona"
- Fácil de migrar entre proveedores de VPS
- Actualizar versiones de PHP/Node sin afectar el SO del servidor

**VPS recomendado:** Hetzner CX22 (2 vCPU, 4 GB RAM, €4/mes) o DigitalOcean Droplet equivalente. Suficiente para 3–5 eventos simultáneos con ~500 asistentes cada uno.

### App móvil (Expo)

No vive en el servidor — se distribuye vía:
- **Android:** EAS Build → APK/AAB → Google Play Store (o link directo para el cliente)
- **iOS:** EAS Build → IPA → TestFlight / App Store

```bash
# Build de producción Android
eas build --platform android --profile production

# Build de producción iOS
eas build --platform ios --profile production
```

### Variables de entorno críticas para producción

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

# App .env / app.json
EXPO_PUBLIC_API_URL=https://api.eventos.com
EXPO_PUBLIC_SOCKET_URL=wss://socket.eventos.com
```

### Checklist pre-deploy

- [ ] `docker-compose.yml` con los 6 servicios
- [ ] Dockerfile para Laravel (PHP 8.3 + extensiones: pdo_mysql, redis, gd, zip)
- [ ] Dockerfile para Socket.IO (Node 20 Alpine)
- [ ] Nginx config con SSL (Let's Encrypt via Certbot o Caddy)
- [ ] `.env.production` con secrets reales (nunca en git)
- [ ] `php artisan migrate --force` en deploy
- [ ] `php artisan config:cache && php artisan route:cache` en deploy
- [ ] Supervisor config para `queue:work` (auto-restart si cae)
- [ ] GitHub Actions CI/CD: push a main → build Docker → deploy en VPS
- [ ] EAS Build configurado para production profile
- [ ] FCM V1 key subida a Expo (ya hecho en S1.11)
- [ ] SHA-1 del keystore de producción en Firebase Console
- [ ] Dominio + DNS apuntando al VPS
- [ ] Cloudflare R2 configurado (cuando esté S1.x uploads)

---

---

## Apéndice A: Fase Seguridad (2026-04-07)

> Documento detallado: `docs/FASE-SEGURIDAD.md`

### Auditoría realizada

Se auditaron los 3 componentes (backend, socket, app). Resultados:
- 3 críticos, 6 altos, 8 medios, 4 bajos

### Implementado (SEC-1 + SEC-2 + SEC-3 parcial)

| ID | Fix | Componente | Tests |
|----|-----|-----------|-------|
| SEC-1.1 | Socket room authorization (join:event valida eventId, join:session via internal endpoint + Redis cache) | socket + backend | 6 |
| SEC-1.2 | HTMLPurifier trait en 8 modelos (strip_tags user input, whitelist HTML admin) | backend | 13 |
| SEC-1.3 | Token expiration 7 días + POST /auth/refresh + app interceptor auto-refresh | backend + app | 7 |
| SEC-2.1 | SecurityHeaders middleware (X-Frame, CSP, HSTS, Referrer-Policy, Permissions-Policy) | backend | 6 |
| SEC-2.2 | CORS hardening (methods/headers restringidos, socket fallback *→false) | backend + socket | — |
| SEC-2.3 | HTTPS enforcement en app (throw si no HTTPS en prod) | app | — |
| SEC-2.4 | `php artisan security:check` (valida debug, placeholders, DB pass, tokens) | backend | 4 |
| SEC-2.5 | `.env.production.example` + SESSION_SECURE_COOKIE=true | backend | — |
| SEC-3.3 | Account lockout (5 intentos → 30 min lock, HTTP 423, reset on success) | backend | 6 |
| SEC-3.4 | Socket rate limiting Redis (INCR/EXPIRE, max 5 conexiones por user) | socket | — |
| SEC-3.5 | 5 FormRequests user-facing (WallPost, Comment, Question, Photo, Rating) | backend | — |

**Total: 42 security tests, 278 tests backend (719 assertions), 0 TS errors**

### Pendiente

| ID | Fix | Razón aplazamiento |
|----|-----|--------------------|
| SEC-3.1 | 2FA OTP (email/WhatsApp) | Requiere WhatsApp Business API + pantalla app |
| SEC-3.2 | Device fingerprinting | Depende de 2FA |
| SEC-4 | Docker, server hardening, Cloudflare, backups | Se implementa en sesión de deploy |
| SEC-5 | SecurityLogger, Sentry, uptime monitoring | Se implementa en sesión de deploy |

### Archivos clave creados

```
eventos-backend/
├── app/Traits/HasSanitizedHtml.php
├── app/Http/Middleware/SecurityHeaders.php
├── app/Console/Commands/SecurityCheckCommand.php
├── app/Http/Requests/Api/V1/Store*.php (5 archivos)
├── .env.production.example
├── database/migrations/..._add_lockout_fields_to_users_table.php
├── routes/web.php (+internal/validate-session-access)
├── routes/api/auth.php (+/auth/refresh)
└── tests/Feature/Security/ (5 test files)

eventos-socket/
├── src/rateLimit.ts (NUEVO)
├── src/index.ts (room auth + max connections)
└── src/chat.ts (Redis rate limiting + session validation)

eventos-app/
└── lib/api.ts (HTTPS enforcement + auto-refresh interceptor)
```

---

## Apéndice B: Landing Web + UI/UX Premium (2026-04-07)

> Documento detallado: `docs/ROADMAP-UIUX-LANDING.md`

### Landing Web

Página pública premium del evento con registro embebido. Login solo en app móvil.

**Secciones planificadas:**
1. Hero con video ambient + countdown + CTA registro
2. Speakers con hover/expand
3. Agenda resumida por tracks
4. Sponsors/Partners logos
5. Venue + mapa interactivo
6. Testimonios ediciones pasadas
7. FAQ colapsable
8. Footer con redes sociales
9. Open Graph meta tags para social sharing

**Stack:** Next.js/Astro + Tailwind CSS + Framer Motion

### Estados del Evento (3 lifecycle states)

| Estado | Registro | App muestra |
|--------|----------|-------------|
| `pre_event` | Abierto (landing) | Countdown + teaser + speakers confirmados |
| `active` | Abierto o cerrado | Acceso completo a todas las features |
| `post_event` | Cerrado | Modo archivo: grabaciones, fotos, certificados, NPS |

### Flujo Post-Registro

1. Pantalla confirmación web (QR descarga app + botones stores)
2. Email confirmación (.ics calendar invite + QR check-in)
3. WhatsApp template (configurable desde admin)
4. SMS fallback (configurable)

### Seguridad Registro

- CAPTCHA/reCAPTCHA v3 invisible en form web
- Rate limiting: max 5 registros por IP en 15 min
- Progressive profiling: datos mínimos en web, completo en app
- 2FA OTP opcional (configurable por evento)
- Device fingerprinting (nuevo device → forzar OTP)
- Account lockout (5 intentos → 30 min)

### Design System

- **Lumina Noir** (dark principal): #0e0e0e + #D1FC00 + Plus Jakarta Sans
- **Lumina Lux** (light alternativo): #FFFFFF + #D4FF00
- Definidos en `design/stitch/stitch/lumina_noir/DESIGN.md` y `lumina_lux/DESIGN.md`
- Prototipos HTML en `design/stitch/` y `design/onboarding/app/`

### Orden de implementación UI/UX

1. Design system tokens en React Native
2. Landing web (Next.js + secciones + form registro)
3. Flujos registro + auth (confirmación, 2FA, countdown)
4. Estados del evento (pre/active/post)
5. Barrido visual app (Lumina Noir en todas las pantallas)
6. Admin premium (analytics dashboard, canales comunicación)

---

## Apéndice C: Compliance de Seguridad (2026-04-07)

> Documento detallado: `docs/COMPLIANCE-SEGURIDAD.md`

Documento formal de compliance que cubre:
- Alineación OWASP Top 10 (2021) — los 10 riesgos cubiertos con controles y evidencia
- Protocolos y certificados digitales (TLS 1.2+, SHA-256, key management)
- 7 cabeceras de seguridad HTTP implementadas y testeadas
- Validación de entrada en capas (Cloudflare WAF → middleware → FormRequest → model sanitization)
- Controles ISO 27001 — Confidencialidad, Integridad, Disponibilidad
- Cumplimiento Ley 1581 (Habeas Data Colombia) + GDPR
- 43 tests de seguridad (82 assertions) + 278 tests totales
- Diagrama de infraestructura completo
- Plan de mejora continua (audits, pentesting, backups, monitoring)
- Inventario de gestión de vulnerabilidades con proceso de remediación

**Uso:** Presentar a clientes corporativos, auditorías de seguridad, licitaciones, o como evidencia de compliance.

---

## Apéndice D: Alta Disponibilidad — 99.99% Uptime (2026-04-07)

> Documento detallado: `docs/DISPONIBILIDAD-HA.md`

### Arquitectura target

- **2 VPS** idénticos (Hetzner CX22, ~$5 c/u) — stateless, intercambiables
- **PlanetScale** — MySQL managed con réplicas y failover automático (99.99% SLA)
- **Upstash** — Redis managed con persistencia y TLS (99.99% SLA)
- **Cloudflare** — Load balancer con health checks cada 30s + failover automático + WAF + DDoS
- **Cloudflare R2** — Storage distribuido globalmente (ya implementado)

### Garantías

| Métrica | Valor |
|---------|-------|
| Uptime | 99.99% (~52 min downtime/año) |
| RPO (pérdida máxima de datos) | < 12 horas |
| RTO (tiempo de recuperación) | < 5 minutos |
| Failover automático | < 30 segundos |
| DDoS protection | Incluida (Cloudflare Tbps) |

### Por qué funciona

Ningún componente es punto único de falla:
- VPS muere → Cloudflare redirige al otro en <30s
- MySQL muere → PlanetScale failover automático en <5s
- Redis muere → Upstash failover automático en <3s
- DDoS → Cloudflare absorbe sin tocar los VPS

### Costo

| Escenario | Costo/mes |
|-----------|-----------|
| Hasta 3,000 usuarios concurrentes | ~$75/mes |
| Hasta 10,000 usuarios concurrentes | ~$160/mes |

### Deploy strategy

Blue-green con zero downtime: deploy en VPS-2 mientras VPS-1 sirve, luego swap. Si falla → rollback instantáneo.

---

---

## Apendice C: UI/UX Premium (Dark Theme) — Implementado 2026-04-07

### Fundamentos
- Plus Jakarta Sans + Inter (expo-google-fonts)
- Dark palette: #0e0e0e background, tonal surfaces, dynamic accent color
- ThemeStore (Zustand) + useBranding hook con auto-sync
- SafeArea (useSafeAreaInsets) en todas las pantallas
- StatusBar light mode

### Floating Tab Bar
- Pill flotante con expo-blur, spring animations, haptic feedback
- MaterialCommunityIcons filled/outline per active state
- Mi QR como boton central (navega a stack screen)
- TabScreenWrapper: fade + slide-up entre tabs

### Home Screen Redesign
- HomeHeader: logo/texto configurable + campana notificaciones (anuncios)
- HomeHero: modo texto (label + titulo + highlight accent) o imagen
- HappeningNow: carousel crossfade (6s) de sesiones live/proximas
- GamificationHud: RGB border animado, rank HUD, barras progreso, breathe text
- ModuleMenu: 4 modulos fijos (Agenda, Speakers, Social, Sponsors) con cascade animation

### Backend: Branding API
- GET /events/{id}/branding: primary_color, primary_text_color, header_title, hero_type/label/title/highlight/image/video/overlay
- Filament EventBrandingResource con color picker + campos condicionales
- favorites_count en EventSessionResource

### Bugs corregidos
- start_datetime/end_datetime -> start/end (coincidia con API real)
- Pressable function style no aplica backgroundColor en Android
- favoritedBy (no favorites) en withCount

---

## Apendice D: Sponsors UI/UX Premium — Implementado 2026-04-09

### Brand Wall (sponsors.tsx)
- Grid adaptativo por tier: Platinum 2col cards, Gold 3col cards, Silver/Bronze/Media 4col circulos
- Sin labels de tier — jerarquia visual por tamano y forma
- Living shuffle: cada 7s, spring damping 28 (suave, sin bounce)
- Compact tiers: logo circular flotante sin card wrapper
- Premium tiers: glass cards con LinearGradient diagonal por tier
- Buscador con debounce 350ms
- Haptic feedback + press scale 0.96
- StaggerReveal entrada escalonada

### Brand Profile (sponsor/[id].tsx)
- Logo hero 100px centrado, sin banner (eliminado definitivamente)
- Floating nav con BlurView tint dark
- Vista implicita: recordView() + visitStand() al montar
- Seccion "Charlas": sesiones del sponsor con fecha/hora/ubicacion
- Servicios seleccionables: chips toggle + formulario contacto (SponsorLead)
- Trivia: opciones con A/B/C/D, shake/bounce, progress bar, haptic, pts acumulados, summary inline con auto-close 2.5s
- LuminaToast en favorito (heart rosa)

### Backend
- Tabla sponsor_leads + SponsorLead model (interested_services JSON, message, unique)
- POST /sponsors/{id}/contact — leads cualificados con email al sponsor
- POST /sponsors/{id}/view — ActivityLog para analytics
- sponsor_id nullable en event_sessions — charlas del sponsor
- SponsorResource incluye sessions en API
- SponsorSeeder: 30 sponsors (GitHub avatar logos), 5 tiers, sesiones asignadas
- 15 tests sponsor (300 total), 0 failures

### Decisiones de diseno
- Sponsors no entregan banners (90%+ solo logo) — eliminado banner como hero
- Tier diferenciado por tamano de card, no por labels explicitos
- Compact tiers = solo logo circular (sin rectangulo = no parece boton)
- Living shuffle = equidad dentro del mismo tier (nadie es "primero" permanente)
- Contact form: leads virtual > presencial (10,000 vs 100) — servicios seleccionables
- Trivia: backend filtra preguntas ya respondidas (no puntos infinitos)

---

---

## Apendice E: Analisis Competitivo — 2026-04-09

### Cotizaciones reales analizadas

Se obtuvieron dos cotizaciones dirigidas a **Eventos Efectivos y Producciones S.A.S.** para el evento **Bintec Bancolombia** (~14,000 asistentes):

| Proveedor | Plataforma | Total | Moneda | En USD |
|---|---|---|---|---|
| **Axity / Cisco** | Webex Events (ex-Socio) | $88,697 | USD | $88,697 |
| **IPServices** | ICE360 Premium | $49,340,364 | COP | ~$11,748 |
| **EventOS** (proyectado) | EventOS SaaS | $200-400/mes infra | USD | $200-400 |

### Por que Cisco cobra $88,697 USD
- 95%+ es margen, no infraestructura
- Marca ("nadie pierde su empleo por comprar Cisco")
- SLA contractual 99.9% con respaldo legal
- Compliance (SOC2, ISO 27001)
- Cadena de intermediarios (Bancolombia → Eventos Efectivos → Axity → Cisco)
- Enterprise sales (abogados, procurement, licitaciones)
- Soporte dedicado ("War Room" con ingenieros)
- Webex Events fue una startup (Socio) comprada por Cisco en 2022. Misma arquitectura: servers + DB + WebSockets + CDN + app movil.

### Features donde EventOS YA supera a ambos
1. Gamificacion (13 acciones + passport stamps + leaderboard) — ni Cisco ni ICE360
2. Social Wall (feed + memorias + momentos) — Cisco basico, ICE360 nada
3. Photobooth moderado — ninguno
4. Ratings de sesiones — ninguno
5. Matchmaking por intereses con overlap — superior al "Shake & Connect" de Cisco
6. Push nativo (no WhatsApp $850K extra, no web-only)
7. Real-time invalidation 4 capas (socket + focusManager + reconnect + staleTime)
8. Brand Wall/Profile con lead capture cualificado

### Gaps cerrados con nuevas sesiones (1.C1-1.C6)
- **1.C1 Analytics** — ambos competidores lo tienen, es tablestakes
- **1.C2 Wallet digital** — reemplaza badge printing como default moderno
- **1.C3 QR dinamico** — reemplaza reconocimiento facial (mas seguro, $0 hardware)
- **1.C4 Digital signage** — proyecto checki ya existia, integrar a EventOS
- **1.C5 Calendar .ics** — ICE360 tenia sync, nosotros archivo .ics universal
- **1.C6 Badge printing** — add-on para clientes nostalgicos

### Mercado objetivo y pricing

| Segmento | Precio | Por que nos eligen |
|---|---|---|
| Eventos corporativos medianos (500-5k) | $3,000-8,000 USD/evento | Mas features que ICE360, 10x mas barato que Cisco |
| Agencias de eventos (white-label) | $1,500-3,000 USD/mes | Plataforma que venden como propia |
| Empresas con 3-4 eventos/año | $800-1,500 USD/mes SaaS | Mas barato que contratar por evento |

### Validacion de escalabilidad horizontal

Arquitectura actual (Laravel stateless + Socket.IO/Redis adapter + Cloudflare CDN) escala sin reescribir codigo:

| Escala | Infra | Costo/mes | Equipo |
|---|---|---|---|
| 1k-5k | 1 VPS gordo | ~$80-120 | Solo |
| 5k-10k | 2-3 VPS + LB | ~$200-400 | Solo |
| 10k-50k | 5-8 VPS + Redis cluster + MySQL replica | ~$800-1,500 | 1 DevOps |
| 50k-100k | 10-15 VPS + DB cluster | ~$3,000-5,000 | DevOps + backend + soporte |
| 100k+ | Kubernetes, auto-scaling, multi-region | ~$8,000-15,000 | Equipo completo |

A 100k+ el negocio factura $500k-1M USD/año y el equipo se paga solo.

---

---

## Apendice F: Web App — Experiencia Virtual Completa (2026-04-09)

### Por que web app (no solo landing)

El publico virtual es el target principal del producto. Un asistente remoto esta sentado frente a su computadora — el celular lo usa para otras cosas. La experiencia del evento en el browser es la experiencia primaria para asistentes virtuales. Ambos competidores (Cisco Webex Events, ICE360) son web-first.

**Decision:** La web app NO es un complemento de la app movil. Es la experiencia completa para el publico virtual. La app movil agrega las interacciones presenciales (QR check-in, escaneo de leads, passport stamps).

### Stack tecnico

- **Framework:** Next.js 15 (App Router)
- **Styling:** Tailwind CSS + shadcn/ui (componentes base)
- **Theme:** Lumina Noir (dark mode, mismo design system que la app movil)
- **Fonts:** Urbanist (body) + Plus Jakarta Sans (headlines)
- **Real-time:** Socket.IO client
- **State:** React Query (TanStack Query) — misma estrategia que la app movil
- **Auth:** Token-based, misma API de Laravel
- **Repo:** Nuevo repo separado (eventos-web)

### Features web (experiencia virtual completa)

| Sesion | Feature | Detalle |
|--------|---------|---------|
| W.1 | Setup + Auth + Layout | Next.js + Tailwind + shadcn/ui + login/register + layout shell |
| W.2 | Home | Hero configurable, happening now, highlights carousel, sponsors |
| W.3 | Agenda | Lista completa, filtros por dia/track/tipo, favoritos, detalle sesion |
| W.4 | Streaming + Q&A + Chat | Experiencia virtual core — video embed + preguntas + chat en vivo |
| W.5 | Speakers | Directorio, perfil detallado, ratings post-sesion |
| W.6 | Social Wall | Feed, posts con fotos, comentarios, likes, memorias |
| W.7 | Sponsors | Brand Wall (grid por tier), Brand Profile, lead capture, trivia |
| W.8 | Networking | Perfiles, solicitudes de contacto, chat 1:1 |
| W.9 | Encuestas + Gamification | Encuestas en vivo, leaderboard, badges, puntos |
| W.10 | Notificaciones + Perfil | Web Notifications API, editar perfil, settings |
| W.11 | Socket.IO | Real-time sync en toda la web (agenda, Q&A, chat, social, encuestas) |
| W.12 | Polish | Responsive, Lumina Noir completo, transiciones, loading states |

### Features SOLO movil (no aplica en web)

- Kiosco de check-in (hardware dedicado)
- QR badge fisico (entrada presencial)
- Escaneo de leads con camara (presencial)
- Passport stamps con QR (presencial)
- Push notifications nativas (web usa Web Notifications API)

### Soporte tablets e iPads

No se requiere trabajo adicional. Todos los dispositivos estan cubiertos con las dos plataformas existentes:

| Dispositivo | Experiencia recomendada | Por que |
|-------------|-------------------------|---------|
| iPhone / Android phone | App nativa | Interacciones presenciales (QR, check-in, leads, passport) |
| iPad / tablet Android | Web app en Safari/Chrome o app nativa | Web app responsive aprovecha pantalla completa; app nativa funciona sin cambios |
| Laptop / desktop | Web app | Experiencia virtual principal |

**App nativa en tablets:** React Native usa Flexbox — los layouts se adaptan automaticamente a pantallas mas grandes. La app ya corre en iPads y tablets Android sin modificaciones. Opcionalmente se pueden optimizar layouts (ej: agenda en dos columnas) pero no es bloqueante.

**Web app en tablets:** Tailwind CSS tiene breakpoints para tablet (`md: 768px`) y desktop (`lg: 1024px`). El diseño responsive cubre tablets nativamente. Un asistente en iPad abre el browser → experiencia perfecta.

**Impacto en timeline:** Cero. No agrega dias de trabajo.

### Ventaja tecnica

El backend no se toca. La API esta 100% lista. Los 300+ tests existentes validan todo. La web app es puramente frontend consumiendo los mismos endpoints. React (Next.js) comparte patrones con React Native — no se parte de cero.

### Estimacion

~22-28 dias de desarrollo. Ver timeline completo en Apendice H.

---

## Apendice G: White-Label + Rebranding (2026-04-09)

### Como funciona

Cada cliente recibe una app con su propia marca: nombre, icono, splash, colores, bundle ID. El codigo es el mismo — solo cambia la configuracion de build.

### Implementacion tecnica

**Paso 1:** Migrar `app.json` estatico → `app.config.js` dinamico

```js
// app.config.js
const CLIENT = process.env.CLIENT || 'eventos';

const clients = {
  eventos: {
    name: 'EventOS',
    slug: 'eventos',
    bundleId: 'com.eventos.app',
    icon: './assets/clients/eventos/icon.png',
    splash: './assets/clients/eventos/splash.png',
  },
  bintec: {
    name: 'Bintec 2026',
    slug: 'bintec-2026',
    bundleId: 'com.bintec2026.app',
    icon: './assets/clients/bintec/icon.png',
    splash: './assets/clients/bintec/splash.png',
  },
};

const config = clients[CLIENT];
module.exports = { expo: { name: config.name, slug: config.slug, ... } };
```

**Paso 2:** Estructura de assets por cliente

```
assets/clients/
  eventos/       # Default (EventOS generico)
    icon.png
    splash.png
  bintec/        # Bancolombia / Bintec
    icon.png
    splash.png
  enel/          # Ejemplo futuro
    icon.png
    splash.png
```

**Paso 3:** Build con un comando

```bash
CLIENT=bintec eas build --profile production --platform all --auto-submit
```

### Tiempos de aprobacion en tiendas

| Tienda | Primera vez | Actualizaciones |
|--------|-------------|-----------------|
| Apple App Store | 24-48 horas | 12-24 horas |
| Google Play | 2-7 dias (primera vez) | Horas (a veces minutos) |

### Que se puede cambiar sin rebuild (desde Filament admin)

- Activar/desactivar modulos (networking, photobooth, gamificacion, etc.)
- Colores del evento (primary_color dinamico)
- Logo y banner del evento
- Configurar agenda, speakers, sponsors
- Contenido completo del evento

### Que requiere rebuild (1 comando + aprobacion tiendas)

- Nombre de la app en las tiendas
- Icono de la app
- Splash screen
- Bundle ID (solo se define una vez, no se cambia despues)

### Flujo para un cliente nuevo

```
Dia 1: Cliente entrega logo + colores + nombre del evento
Dia 1: Crear carpeta assets/clients/nombre/ + config (2-3 horas)
Dia 1: CLIENT=nombre eas build --auto-submit (20 min build en nube)
Dia 2-3: Apple aprueba (~24-48h)
Dia 2: Google aprueba (~horas)
Dia 3: App en las tiendas con la marca del cliente
```

**Tiempo total por cliente nuevo: 3 dias (la mayoria es espera de aprobacion).**

### Cuentas de developer

| Escenario | Cuenta | Costo |
|-----------|--------|-------|
| Clientes pequenos/medianos | Tu cuenta (Kasproduction) | $99/ano Apple + $25 una vez Google |
| Corporativos grandes | Cuenta del cliente | El cliente paga su cuenta, te invita como developer |

Apple permite hasta 30 apps por cuenta. Google no tiene limite.

### Modelo de negocio white-label

| Opcion | Descripcion | Para quien |
|--------|-------------|------------|
| **A. App propia por cliente** | "Bintec 2026" en las tiendas, marca 100% del cliente | Agencias, corporativos ($5K+ USD) |
| **B. App generica EventOS** | Usuario abre EventOS → selecciona su evento | Clientes pequenos ($800-1.5K/mes SaaS) |
| **C. Ambas** | White-label premium + app generica para el resto | Escala con multiples segmentos |

### Web app white-label

La web app tambien se rebrandea facilmente:
- Dominio del cliente: `eventos.bintec.com.co` → apunta al mismo backend
- CSS variables para colores/logo → cambia por evento automaticamente
- Mismo concepto: un codebase, multiples marcas

### Setup necesario (una sola vez)

| Tarea | Tiempo |
|-------|--------|
| Migrar app.json → app.config.js | ~1 hora |
| Estructura assets/clients/ | ~30 min |
| EAS build profiles por cliente | ~30 min |
| Documentar proceso | ~30 min |
| **Total** | **~2-3 horas** |

---

## Apendice H: Timeline Cliente Ancla — Septiembre 2026

### Contexto

Cliente real identificado: **Eventos Efectivos y Producciones S.A.S.** (agencia que cotizo Cisco $88K USD e ICE360 $49M COP). Necesitan plataforma para evento en **septiembre 2026**.

### Timeline comprometido

| Mes | Objetivo | Entregable |
|-----|----------|------------|
| **Abril** | Desarrollo: analytics + features competitivos (C1-C5) | Backend + app movil completos |
| **Mayo** | Desarrollo: web app completa + deploy + UI sweep | Producto desplegado en VPS, web app funcional |
| **Junio** | Pitch + demo en vivo | Video demo 3 min, presentacion a Eventos Efectivos |
| **Julio** | Onboarding cliente | White-label, personalizacion, datos del evento |
| **Agosto** | Pruebas + ajustes | Stress test con datos reales, feedback del cliente |
| **Septiembre** | Evento en vivo | Primer caso de estudio real |

### Estimacion de trabajo (abril-mayo)

| Bloque | Duracion | Acumulado |
|--------|----------|-----------|
| Analytics dashboard (1.C1) | 2-3 dias | Semana 1 |
| Permisos admin (1.23) + Calendar .ics (1.C5) + QR dinamico (1.C3) | 2 dias | Semana 1-2 |
| Web app W.1-W.4 (setup + home + agenda + streaming) | 6-8 dias | Semana 2-3 |
| Web app W.5-W.8 (speakers + social + sponsors + networking) | 6-8 dias | Semana 3-5 |
| Web app W.9-W.12 (encuestas + gamification + sockets + polish) | 5-6 dias | Semana 5-6 |
| Deploy (backend + web + app) + SEC-4/5 | 2-3 dias | Semana 6-7 |
| UI sweep app movil + white-label setup | 2-3 dias | Semana 7 |
| Video demo + materiales de pitch | 1 dia | Semana 7 |
| **Total** | **~32-38 dias** | **~7 semanas** |

### Prioridad de features para este deal

**Critico (sin esto no hay venta):**
- Analytics dashboard — justifica el ROI ante el cliente
- Web app completa — publico virtual es el core
- Streaming + Q&A + Chat — experiencia virtual minima
- Deploy funcional — demo en vivo, no screenshots

**Importante (fortalece la venta):**
- Calendar sync (.ics) — esperado por todos
- QR dinamico — diferenciador vs competencia
- Permisos admin — necesario para operacion real

**Puede esperar (agregar post-venta si lo piden):**
- Wallet digital (C2)
- Digital signage (C4)
- Badge printing (C6)
- Certificados PDF
- Rewards / redencion de puntos
- Light mode

### Filtro de decisiones

> "Este feature me acerca al pitch de junio con Eventos Efectivos?"
>
> Si → hacerlo. No → posponerlo.

---

_EventOS Plan v3.0 — Kasproduction_
_Documento maestro: EventOS_ClaudeCode_Prompt_v2.md (v2.5)_
_Dev setup: EventOS_DevSetup.md_
_Seguridad: docs/FASE-SEGURIDAD.md_
_Compliance: docs/COMPLIANCE-SEGURIDAD.md_
_Disponibilidad: docs/DISPONIBILIDAD-HA.md_
_UI/UX + Landing: docs/ROADMAP-UIUX-LANDING.md_
_Analisis competitivo: memoria project_competitive_analysis.md_
