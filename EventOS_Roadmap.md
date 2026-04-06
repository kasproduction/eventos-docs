# EventOS — Plan de trabajo definitivo

_Fases, sesiones, dependencias progresivas y definición de completado_

---

## Principio: dependencias progresivas

**No instalar todo al inicio.** Cada sesión instala solo lo que necesita.
Esto evita romper el proyecto con librerías que no se usan aún y hace
cada sesión más fácil de debuggear.

---

## Resumen de fases

| Fase       | Nombre                  | Sesiones               |
| ---------- | ----------------------- | ---------------------- |
| **Fase 0** | Setup e infraestructura | 0.1 → 0.4              |
| **Fase 1** | MVP funcional           | 1.1 → 1.13             |
| **Fase 2** | Experiencia completa    | 2.1 → 2.17             |
| **Fase 3** | SaaS + Monetización     | 3.1 → 3.4 _(aplazada)_ |

**Milestone MVP lanzable:** Completar Fase 0 + Fase 1 completa.

## Estado Fase 1 (al 2026-04-04)

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
| 1.10 | Encuestas en vivo | ✅ Completa — mergeado a main (2026-04-04) |
| 1.11 | Push notifications | ✅ Completa — mergeado a main (2026-04-04) |
| 1.12 | Tracks + Session types | ✅ |
| 1.13a | Emails automáticos + editor de plantillas | ✅ Completa — mergeado a main (2026-04-05) |
| 1.13b | SMTP propio por organización | ✅ Completa — mergeado a main (2026-04-05) |

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

## SESIÓN 1.x-B — Onboarding: Animaciones premium

## ─────────────────────────────────────────

**Depende de:** Sesión 1.x-A completada y funcionando.

**Objetivo:** Transformar la pantalla funcional en una experiencia visualmente memorable.

### Técnicas de animación a implementar

| Efecto | Librería | Detalle |
|---|---|---|
| Entrada por elementos | Reanimated `withSpring` + `withDelay` | Título sube desde +40px, subtítulo aparece 150ms después, imagen hace fade+scale |
| Parallax imagen | Reanimated `useAnimatedScrollHandler` | La imagen se mueve al 60% de la velocidad del gesto — profundidad |
| Transición entre slides | Crossfade del gradiente de fondo | El nuevo fondo aparece con `withTiming(1, {duration: 400})` mientras el contenido entra desde el lado |
| Dots tipo pill animados | Reanimated `useAnimatedStyle` | Dot activo se expande horizontalmente con spring (igual que Instagram Stories) |
| Haptic al cambiar slide | `expo-haptics` | `impactAsync(ImpactFeedbackStyle.Light)` |
| Pulse en botón CTA | Reanimated `withRepeat` + `withSequence` | Scale 1 → 1.04 → 1, infinito, suave |
| Skip fade-out | Reanimated | El botón skip se desvanece al llegar al último slide |

### App — checklist Sesión B

- [ ] Reanimated: animación de entrada por slide (spring title + delay subtitle + fade-scale image)
- [ ] Parallax: imagen se mueve a 0.6x la velocidad del scroll
- [ ] Crossfade de gradiente entre slides
- [ ] Dots pill animados con Reanimated
- [ ] Haptic feedback en cambio de slide
- [ ] Pulse animado en botón CTA (último slide)
- [ ] Fade-out del botón Skip en último slide
- [ ] Prueba visual en pantallas pequeñas (SE 375px) y grandes (Pro Max 430px)

---

## ─────────────────────────────────────────

## SESIÓN UI — Diseño visual completo _(post Fase 1, antes de Fase 2)_

## ─────────────────────────────────────────

**Cuándo:** Cuando Fase 1 esté funcional completa y antes de lanzar con cliente real.
**Referencias:** `/design/` — estilo dark mode (Fever), cards vibrantes por sesión, tipografía bold.

**Dirección visual definida:**

- Fondo: dark mode (#0D0D0D o similar) como base
- Accent: `primary_color` del evento (configurable por admin en Filament)
- Cards redondeadas, sombra sutil, tipografía grande y bold en headers
- Bottom tab bar minimalista
- Estilo corporativo-moderno (no festival) — mezcla Fever dark + cards tipo agenda de la segunda ref

**Tareas:**

- [ ] Design tokens: colores, tipografía, espaciado en `tailwind.config.js`
- [ ] Tema dinámico por evento (primary_color desde API → NativeWind CSS vars)
- [ ] Rediseño Home screen (ModuleMenu con cards visuales)
- [ ] Rediseño Agenda (cards por sesión con color de track)
- [ ] Rediseño Speakers, Documentos, Anuncios
- [ ] Rediseño Auth screens (login / registro)
- [ ] Rediseño Profile screen
- [ ] Dark mode completo (ya planeado desde Fase 1)
- [ ] Iconografía consistente (reemplazar emojis del ModuleMenu)
- [ ] Splash screen / onboarding visual

---

## ─────────────────────────────────────────

## MEJORAS PRE-DEPLOY — Pulido antes de lanzar con cliente real

## ─────────────────────────────────────────

> Pequeñas mejoras detectadas durante el desarrollo. No bloquean el MVP pero mejoran la experiencia. Agregar aquí cualquier detalle que surja durante las sesiones.

### Emails — mejoras post S1.13

- [ ] **Tracking de aperturas (pixel 1×1)** — agregar columna `opened_at` en `email_logs`, endpoint `GET /track/email/{token}` que devuelve imagen transparente y registra la apertura, inyectar pixel en el layout base de emails. Mostrar tasa de apertura en `EmailLogResource` (total enviados, abiertos, fallidos por tipo/evento). Nota: Gmail bloquea imágenes por defecto; Apple Mail iOS 15+ pre-carga imágenes (falsos positivos). Útil como referencia, no como métrica exacta.

### Emails — pendiente Sesión UI / Web (S2.1)

- [ ] **Pantallas web para password reset y verificación de email** — actualmente las rutas del backend (`/password/reset/{token}` y `/auth/verify-email/{id}/{hash}`) muestran una página de redirect al deep link de la app. Cuando se implemente Next.js (S2.1), estas pantallas deben tener su versión web completa:
  - `/password/reset?token=XXX&email=XXX` → formulario de nueva contraseña en el browser → llama `POST /api/v1/auth/reset-password`
  - `/auth/verify-email/{id}/{hash}` → ya verifica y redirige, pero mostrar una página más cuidada post-verificación
  - La página de redirect del backend (`deep-link-redirect.blade.php`) puede detectar user-agent para redirigir a web vs deep link según el dispositivo.

### Agenda

- [ ] **Pantalla "Mi Agenda"** — vista filtrada con solo las sesiones marcadas como favoritas. El campo `is_favorite` ya existe, solo falta la screen y la ruta. Referencia: Webex hace esto bien. Podría ser un tab extra en la agenda o una sección en el home.
- [ ] **Botón "Ver transmisión"** — en la card/detalle de sesión, mostrar botón solo cuando `stream_url != null`. Abrir con `expo-web-browser`. El campo ya existe en `event_sessions`.
- [ ] **Speaker ↔ Sesión bidireccional en Filament** — al crear/editar un speaker, poder asignarle sus sesiones directamente (hoy solo funciona desde la sesión hacia el speaker). La pivot `session_speaker` ya existe.

### Vendedor

- [ ] **Tab Inicio del vendedor** — actualmente muestra ModuleMenu genérico. Considerar agregar card de resumen del stand (leads capturados hoy, cupos usados) encima del ModuleMenu.

### Módulos visibles por rol (prioridad alta pre-deploy)

- [ ] **Definir qué módulos ve cada rol en el home.** Hoy el ModuleMenu muestra los módulos que devuelve la API sin filtrar por rol en el frontend, lo que causa que roles vean rutas que no les corresponden (ej: vendedor ve Networking que no tiene sentido en su flujo, presencial ve Leads, etc.). Opciones:
  - **A (recomendada):** El backend ya filtra por rol en `GET /events/{id}/modules` — verificar que el `ModuleSeeder` asigne los módulos correctos a cada rol. Así el filtro es centralizado.
  - **B:** Filtrar en el frontend por `user.role` antes de renderizar el ModuleMenu.
  - Módulos por rol esperados:
    - `presencial`: agenda, speakers, documentos, anuncios, networking, mi-qr, patrocinadores, banners, paginas
    - `virtual`: agenda, speakers, documentos, anuncios, networking, patrocinadores, banners, paginas
    - `vendedor`: leads, escaner, mi-stand, patrocinadores, anuncios _(sin networking, sin mi-qr)_

### Admin — Módulos

- [ ] **Rediseño del form de creación de módulos** — actualmente el admin escribe el slug a mano y hay un textarea de JSON libre que no sirve para módulos estándar. El flujo correcto:
  1. Select "Tipo de módulo" → lista de templates disponibles (agenda, speakers, documentos, anuncios, networking, chat, encuestas, mi-qr, leads, banners, + custom). Al elegir → auto-rellena nombre, ícono, roles por defecto, sort_order sugerido.
  2. Si es módulo estándar → no hay config adicional (la app mapea por slug hardcoded, el JSON es irrelevante en Fase 1).
  3. Si es `custom` (iframe) → aparecen campos controlados: URL del iframe, permisos sandbox (allow-scripts, allow-forms, etc.), altura fija o scroll. Sin JSON crudo.
  - **Por qué no se hizo antes:** el JSON config no tiene efecto real en módulos estándar (la app ignora el campo). Los módulos del evento se crean por seeder — el admin normalmente solo activa/desactiva, raramente crea uno nuevo.

- [ ] **Dos instancias del mismo módulo (ej: dos agendas)** — requiere cambio en la app: el router de Expo debe aceptar el módulo como parámetro y pasar config al screen (ej: `{track_id: 2}` para filtrar). En Fase 1 no aplica — ya existen tracks para separar sesiones dentro de una sola agenda. Revisar en Fase 2.

### General

_(agregar aquí más mejoras a medida que surjan)_

---

## ─────────────────────────────────────────

## FASE 2 — Experiencia completa _(aplazada)_

## ─────────────────────────────────────────

Módulos aplazados para después del MVP:

| Sesión | Feature                                                                                                                                                                                                                                                                                                           | Nuevas deps cuando llegue                         |
| ------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------- |
| 2.1    | **Web Next.js + Streaming** — portal web para asistentes virtuales (login, agenda, speakers, docs, anuncios, networking) + iframe/embed de stream. El organizador provee la URL del stream (YouTube Live, Vimeo, Streamyard, etc.) — nosotros la embebemos. Sin SDK de video propio.                              | `next`, `socket.io-client` (web)                  |
| 2.2    | **App — Streaming nativo (Expo)** — WebView con la `stream_url` de la sesión. El organizador pone su URL de YouTube/Vimeo/cualquier servicio. No se decodifica video — es un embed. Tiempo de visualización trackeado via timer en nuestra tabla `tracking` (evento `session_stream_view`, duración en segundos). | `react-native-webview`                            |
| 2.3    | Preguntas al speaker                                                                                                                                                                                                                                                                                              | ninguna nueva                                     |
| 2.4    | Evaluación de sesiones                                                                                                                                                                                                                                                                                            | ninguna nueva                                     |
| 2.5    | Photobooth / Memorias                                                                                                                                                                                                                                                                                             | `expo-image-picker`                               |
| 2.6    | Certificados PDF                                                                                                                                                                                                                                                                                                  | `spatie/browsershot` (queue pdf)                  |
| 2.7    | Reporte post-evento PDF                                                                                                                                                                                                                                                                                           | `spatie/browsershot` (ya instalado)               |
| 2.8    | Analytics avanzado                                                                                                                                                                                                                                                                                                | ninguna nueva                                     |
| 2.9    | Matchmaking por intereses                                                                                                                                                                                                                                                                                         | ninguna nueva (tablas de networking ya en Fase 1) |
| 2.10   | **Social wall** — feed de posts con fotos/texto del evento. Asistentes publican, comentan, dan likes. Moderación por admin.                                                                                                                                                                                       | ninguna nueva (Socket.IO ya existe)               |
| 2.11   | **Gamification + Leaderboard** — sistema de puntos por actividad: check-in, preguntas al speaker, visitar stands, publicar en social wall, completar perfil. Ranking visible en home y por evento.                                                                                                               | ninguna nueva                                     |
| 2.12   | **Passport Contest** — escanear QR de stands para acumular stamps. Admin define cuántos stands hay que visitar para ganar premio. Se construye sobre S1.4 (QR) + S1.6 (stands).                                                                                                                                 | ninguna nueva                                     |
| 2.13   | **Photo/Caption Contest** — galería de fotos subidas por asistentes con votación por likes. Caption contest: admin sube foto, asistentes proponen leyendas. Se construye sobre Social wall (2.10).                                                                                                               | `expo-image-picker` (ya en 1.x uploads)           |
| 2.14   | **Video calls 1:1** — llamada de video dentro del networking. Asistente A solicita videollamada → B acepta → sala efímera. Solo en app + web.                                                                                                                                                                    | `livekit-client`, `@livekit/react-native-webrtc`  |
| 2.15   | **Floor plan del venue** — admin sube imagen del plano del recinto y posiciona logos/números de stands sobre ella. Asistentes ven el mapa interactivo (zoom/pan) con stands clickeables.                                                                                                                         | ninguna nueva (imagen estática + coordenadas)     |
| 2.16   | **Reports exportables detallados** — extiende 2.8. Nuevos reportes: actividad Q&A, actividad chat por sesión, lounge report (tiempo en networking), bookmarks de sesiones. Exportables en CSV y PDF.                                                                                                             | `spatie/browsershot` (ya en 2.6)                  |
| 2.17   | **Proximity chat web (spatial audio)** — solo en Next.js. Espacio virtual tipo Gather: avatares que se mueven con click/teclas, audio via Livekit, volumen se atenúa por distancia. Sala por evento. Se construye sobre 2.1 (web Next.js) + necesita Livekit server.                                            | `livekit-server-sdk`, `@livekit/components-react` |

> **Nota (2026-03-30):** El asistente virtual en Fase 1 accede por app móvil — el layout `app/(app)/(virtual)/(tabs)/` ya está implementado. La web (Next.js) se construye en Fase 2 junto con streaming porque su diferencial principal es "ver transmisión desde el navegador sin descargar la app". Agregar la web sin streaming en Fase 1 no aportaría valor diferencial.

> **Decisión de arquitectura streaming (2026-03-30):** El streaming NO es propio — es un WebView/iframe que embebe la URL que el organizador ya tiene (YouTube Live, Vimeo, Streamyard, etc.). Ventajas: cero costo adicional, cero dependencia de SDK de video, el cliente usa el servicio que ya tiene. El tiempo de visualización se mide con un timer propio (entrada/salida de la pantalla del stream) guardado en `tracking` — suficiente para el reporte post-evento por usuario y sesión.

---

## ─────────────────────────────────────────

## FASE 3 — SaaS + Monetización _(aplazada)_

## ─────────────────────────────────────────

| Sesión | Feature                         | Nuevas deps cuando llegue                     |
| ------ | ------------------------------- | --------------------------------------------- |
| 3.1    | Multi-tenant + gestión de orgs  | ninguna nueva                                 |
| 3.2    | Stripe + facturación            | `laravel/cashier`                             |
| 3.3    | Data export (Ley 1581/GDPR)     | ninguna nueva                                 |
| 3.4    | Juegos Unity + Socket.IO bridge | `socket.io-client` Unity, Reanimated gestures |

---

## Resumen de dependencias por sesión

### Backend (Laravel)

| Sesión        | Paquete                                                          |
| ------------- | ---------------------------------------------------------------- |
| 0.2           | sanctum, spatie/permission, filament, horizon, telescope, sentry |
| 1.3           | ezyang/htmlpurifier                                              |
| 1.11          | kreait/laravel-firebase                                          |
| 1.x (uploads) | league/flysystem-aws-s3-v3 (R2)                                  |
| 2.5+          | spatie/browsershot                                               |
| 2.17          | livekit-server-sdk                                               |
| 3.2           | laravel/cashier                                                  |

### App (Expo)

| Sesión        | Paquete                                                                                |
| ------------- | -------------------------------------------------------------------------------------- |
| 0.3           | expo-router, nativewind, reanimated, secure-store, mmkv, tanstack-query, zustand, i18n |
| 1.3           | expo-image, expo-file-system, @shopify/flash-list                                      |
| 1.4           | expo-camera, expo-keep-awake                                                           |
| 1.9           | socket.io-client                                                                       |
| 1.11          | expo-notifications                                                                     |
| 2.1           | expo-av                                                                                |
| 1.x (uploads) | expo-image-picker                                                                      |
| 2.14          | @livekit/react-native-webrtc (dev build requerido)                                     |

### Socket.IO (Node.js)

| Sesión | Paquete                                                     |
| ------ | ----------------------------------------------------------- |
| 0.4    | socket.io, @socket.io/redis-adapter, ioredis, axios, dotenv |
| 2.17   | livekit-client, @livekit/components-react (web Next.js)      |

---

## Orden de implementación

```
FASE 0:   0.1 → 0.2 → 0.3 → 0.4
FASE 1:   1.1 → 1.2 → 1.3 → 1.4 → 1.5 → 1.6 → 1.7 → 1.8 → 1.9 → 1.10 → 1.11 → 1.12 → 1.13
              ↑ MVP lanzable mínimo viable: hasta sesión 1.4
FASE 2:   2.1 → 2.2 → 2.3 → 2.4 → 2.5 → 2.6 → 2.7 → 2.8 → 2.9
          → 2.10 → 2.11 → 2.12 → 2.13 → 2.14 → 2.15 → 2.16 → 2.17
              ↑ 2.17 (proximity chat) es el más complejo — hacerlo al final de Fase 2
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

_EventOS Plan v2.1 — Kasproduction_
_Documento maestro: EventOS_ClaudeCode_Prompt_v2.md (v2.5)_
_Dev setup: EventOS_DevSetup.md_
