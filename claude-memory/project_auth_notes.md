---
name: EventOS — notas técnicas de auth (Sesión 1.1)
description: Gotchas de autenticación Sanctum, Spatie roles, Pest y HMAC-QR
type: project
---

Notas de la Sesión 1.1 (2026-03-29).

## HASH_DRIVER en tests

`config/hashing.php` usa `env('HASH_DRIVER', 'argon2id')`.
`phpunit.xml` tiene `HASH_DRIVER=bcrypt` y `BCRYPT_ROUNDS=4` para tests rápidos.
Producción sigue usando argon2id con memory=65536, time=4.

**How to apply:** Si se agregan más tests con User::factory(), pasar password como texto plano — el cast `'hashed'` maneja el hasheo. No pasar valores pre-hasheados al factory.

## UserFactory — plaintext password

```php
'password' => 'password', // cast 'hashed' handles hashing
```

**Why:** Pasar `Hash::make('password')` al factory con cast `hashed` llama a `verifyConfiguration()` que falla si el hash fue creado con parámetros distintos al driver configurado.

## Credenciales de prueba (DB local)

- Password por defecto de todos los usuarios sembrados: `password`
- Admin Filament: `admin@eventos.test` / `Admin123!` (reseteada manualmente en S1.11)
- Usuarios del evento 19: presencial@eventos.test, virtual@eventos.test, vendedor@eventos.test, sofia@eventos.test, etc. — todos con `password`

## Pest v3 (no v4)

Pest v4 requiere PHPUnit ^12 que rompe Laravel 11 (usa PHPUnit ^11).
Instalar con `composer require pestphp/pest:^3.0 pestphp/pest-plugin-laravel:^3.0 --dev --ignore-platform-req=ext-pcntl --ignore-platform-req=ext-posix`.

`tests/Pest.php` global:
```php
uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class)->in('Feature');
```

## QR token — HMAC-SHA256

```php
$payload = implode('|', [$attendee->id, $attendee->event_id, now()->timestamp]);
$token = hash_hmac('sha256', $payload, config('app.qr_secret'));
```

Guardado en tabla `qr_tokens` para lookup. Produce 64 chars hexadecimales.
`APP_QR_SECRET` en .env → `config('app.qr_secret')`.

## Attendee en /me y /login

- `GET /api/v1/auth/me` → busca el attendee del evento activo más reciente del usuario
- `POST /api/v1/auth/login` → si no se pasa `event_slug`, auto-resuelve el attendee del primer evento activo (fix S1.4)
- Si no hay attendee, retorna `null` en el campo (no error)
- Sin attendee → `eventId: null` en authStore → `useModules(null)` no fetcha → menú vacío

## EXPO_PUBLIC_API_URL

La app usa `process.env.EXPO_PUBLIC_API_URL` (prefijo EXPO_PUBLIC para que Expo lo exponga al bundle).
Default: `http://eventos-backend.test/api/v1` para desarrollo local.

## Registro y roles Spatie

Al registrarse vía API, el usuario recibe automáticamente el rol `attendee` (Spatie).
Los seeders asignan roles manualmente: super_admin, event_admin, attendee, vendor.
