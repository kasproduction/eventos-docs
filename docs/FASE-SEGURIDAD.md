# Fase de Seguridad — EventOS

> Auditoría completa + plan de remediación para producción.
> Fecha auditoría: 2026-04-07 | Estado: Planificación
> Documento tipo: Sesión técnica (mismo formato que S1.x)

---

## Resumen Ejecutivo

Se auditaron los 3 componentes:
- **eventos-backend** (Laravel 11 + Sanctum + Filament) — `C:\laragon\www\eventos-backend`
- **eventos-socket** (Socket.IO 4.8 + ioredis 5 + TypeScript) — `C:\laragon\www\eventos-socket`
- **eventos-app** (Expo SDK 55 + React Native) — `C:\Users\Kasproduction\Projects\eventos-app`

| Severidad | Cantidad | Resumen |
|-----------|----------|---------|
| CRITICO | 3 | Room auth socket, HTMLPurifier sin usar, tokens permanentes |
| ALTO | 6 | Sin headers seguridad, CORS wildcard, sin cert pinning |
| MEDIO | 8 | Sin 2FA, rate limit en memoria, sin device fingerprint |
| BAJO | 4 | Sin biométricos, debug mode local, deep links parcial |

---

## Lo que YA está bien (no tocar)

| Aspecto | Estado | Detalle | Archivo |
|---------|--------|---------|---------|
| Password hashing | ✅ Argon2id | 64MB memory, 4 iter, fuerte | `config/hashing.php` |
| SQL injection | ✅ Protegido | Eloquent + parameterized. Raw queries usan aggregates sin user input | Todos los controllers |
| Rate limiting básico | ✅ Implementado | login:5/min, api:60/min, upload:10/min | `AppServiceProvider.php:42-62` |
| File uploads | ✅ Validado | MIME check, 10MB max, UUID filenames, R2/local | `StorageService.php`, controllers |
| Token storage (app) | ✅ SecureStore | expo-secure-store, NO MMKV ni AsyncStorage | `authStore.ts:2,44,65` |
| API versioning | ✅ v1 prefix | Rutas organizadas `/api/v1/` | `routes/api.php:12` |
| Internal endpoints | ✅ Secret-based | Header `X-Internal-Secret` validado | `routes/web.php:150-185` |
| Signed URLs | ✅ hash_equals() | Email verification seguro contra timing attacks | `routes/web.php:46` |
| CSRF | ✅ Habilitado | Excepto internal endpoints (correcto) | Laravel default |
| Redis DB separation | ✅ DB 2 para socket | No colisiona con cache (0) ni queues (1) | `eventos-socket/.env` |
| .env en .gitignore | ✅ Correcto | También ignora .env.backup, .env.production | `.gitignore` |
| Console logging (app) | ✅ Limpio | 0 console.log en producción | Verificado en búsqueda completa |
| Dependencies (socket) | ✅ 0 CVEs | npm audit clean, todas las versiones current | `package.json` |
| Job payloads | ✅ Safe | SerializesModels, sin secrets en queue | 10 jobs auditados |
| Permisos app | ✅ Razonables | Camera, Internet, Contacts, Vibrate — todos justificados | `AndroidManifest.xml` |
| .env app | ✅ Solo públicas | EXPO_PUBLIC_*, sin secrets, .gitignore incluye *.key, *.p8, *.jks | `.env`, `.gitignore` |

---

## SESION SEC-1: Críticos (día 1-2)

### SEC-1.1 Socket — Room Authorization

**Estado:** 🔲 Pendiente
**Severidad:** CRITICO
**Componente:** eventos-socket

#### Problema

```typescript
// eventos-socket/src/index.ts:262-274 — ACTUAL (INSEGURO)
socket.on('join:event', ({ eventId }) => {
  void socket.join(Rooms.event(eventId)); // Cualquier user puede join
});

// eventos-socket/src/chat.ts:86-104 — ACTUAL (INSEGURO)
socket.on('join:session', async ({ sessionId }) => {
  await socket.join(Rooms.session(sessionId)); // Sin validar acceso
  await socket.join(Rooms.chat(sessionId));
});
```

**Escenarios de ataque:**
- Espionaje: unirse a eventos ajenos → ver anuncios, polls, Q&A
- Poll stuffing: votar en encuestas de sesiones que no asiste
- Chat interception: leer conversaciones de sesiones privadas

#### Archivos a modificar

| Archivo | Cambio |
|---------|--------|
| `eventos-backend/routes/web.php` | Agregar `POST /internal/validate-access` |
| `eventos-socket/src/index.ts` | Validar acceso antes de `socket.join()` en `join:event` |
| `eventos-socket/src/chat.ts` | Validar acceso antes de `socket.join()` en `join:session` |
| `eventos-socket/src/auth.ts` | Cachear event_id del user al autenticar (evitar query por cada join) |

#### Implementación Backend

```php
// routes/web.php — nuevo endpoint interno
Route::post('/internal/validate-access', function (Request $request) {
    $secret = $request->header('X-Internal-Secret');
    if (!$secret || $secret !== config('services.socket.internal_secret')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $attendeeId = $request->input('attendee_id');
    $eventId    = $request->input('event_id');
    $sessionId  = $request->input('session_id'); // nullable

    // Validar que el attendee pertenece al evento
    $attendee = Attendee::where('id', $attendeeId)
        ->where('event_id', $eventId)
        ->where('status', 'approved')
        ->first();

    if (!$attendee) {
        return response()->json(['valid' => false]);
    }

    // Si se pidió validar sesión específica
    if ($sessionId) {
        $sessionExists = Session::where('id', $sessionId)
            ->where('event_id', $eventId)
            ->exists();
        if (!$sessionExists) {
            return response()->json(['valid' => false]);
        }
    }

    return response()->json(['valid' => true]);
});
```

#### Implementación Socket

```typescript
// eventos-socket/src/index.ts — FIX
import axios from 'axios';

async function validateAccess(
  attendeeId: number,
  eventId: number,
  sessionId?: number
): Promise<boolean> {
  const cacheKey = sessionId
    ? `access:${attendeeId}:${eventId}:${sessionId}`
    : `access:${attendeeId}:${eventId}`;

  // Check Redis cache first (5 min TTL)
  const cached = await pubClient.get(cacheKey);
  if (cached === '1') return true;
  if (cached === '0') return false;

  try {
    const res = await axios.post(
      `${config.laravelUrl}/internal/validate-access`,
      { attendee_id: attendeeId, event_id: eventId, session_id: sessionId },
      {
        headers: { 'X-Internal-Secret': config.internalSecret },
        timeout: 2000,
      }
    );
    const valid = res.data.valid === true;
    await pubClient.setex(cacheKey, 300, valid ? '1' : '0');
    return valid;
  } catch {
    return false; // fail-closed
  }
}

socket.on('join:event', async ({ eventId }) => {
  const hasAccess = await validateAccess(user.attendeeId, eventId);
  if (!hasAccess) {
    socket.emit('error', { code: 'EVENT_ACCESS_DENIED' });
    return;
  }
  await socket.join(Rooms.event(eventId));
});
```

#### Tests

| Test | Tipo | Archivo |
|------|------|---------|
| User puede join su propio event | Feature | `tests/Feature/Socket/RoomAuthTest.php` |
| User NO puede join event ajeno | Feature | `tests/Feature/Socket/RoomAuthTest.php` |
| User NO puede join session de event ajeno | Feature | `tests/Feature/Socket/RoomAuthTest.php` |
| Attendee con status != approved NO puede join | Feature | `tests/Feature/Socket/RoomAuthTest.php` |
| Internal endpoint rechaza sin X-Internal-Secret | Feature | `tests/Feature/Socket/RoomAuthTest.php` |
| Cache Redis funciona (segunda llamada no hace HTTP) | Unit | Socket test manual |
| Fail-closed: si backend no responde, acceso denegado | Unit | Socket test manual |

#### Notas técnicas

- **Fail-closed**: Si el backend no responde, el acceso se DENIEGA (no se permite)
- **Cache**: Redis key `access:{attendeeId}:{eventId}` con TTL 5 min. Invalidar en: ban, kick, cambio de status attendee
- **Performance**: Primera conexión = 1 HTTP call al backend. Después todo desde cache.
- El user.attendeeId ya viene del auth middleware (viene de Laravel en la validación del token)

---

### SEC-1.2 Backend — HTMLPurifier activado

**Estado:** 🔲 Pendiente
**Severidad:** CRITICO
**Componente:** eventos-backend

#### Problema

`composer.json` incluye `ezyang/htmlpurifier ^4.19` pero NUNCA se aplica. Campos HTML se guardan y devuelven sin sanitizar → XSS almacenado posible.

#### Modelos afectados

| Modelo | Campo(s) | Riesgo |
|--------|----------|--------|
| `CustomPage` | `body` (RichEditor Filament) | ALTO — HTML directo del admin |
| `WallPost` | `body` | ALTO — input directo del usuario |
| `ChatMessage` (via socket) | `message` | MEDIO — texto plano pero se muestra en UI |
| `Question` (Q&A) | `body` | MEDIO — pregunta del usuario |
| `Session` | `description` | BAJO — solo admin edita |
| `Speaker` | `bio` | BAJO — solo admin edita |
| `Sponsor` | `description` | BAJO — solo admin edita |

#### Archivos a crear/modificar

| Archivo | Cambio |
|---------|--------|
| `app/Traits/HasSanitizedHtml.php` | **NUEVO** — Trait con mutators de sanitización |
| `app/Models/CustomPage.php` | Usar trait, sanitizar `body` |
| `app/Models/WallPost.php` | Usar trait, sanitizar `body` |
| `app/Models/Question.php` | Usar trait, sanitizar `body` |
| `app/Http/Controllers/Api/V1/WallController.php` | Strip tags en input (doble capa) |
| `app/Http/Controllers/Api/V1/ChatController.php` | Strip tags en messages |

#### Implementación

```php
// app/Traits/HasSanitizedHtml.php — NUEVO
namespace App\Traits;

use HTMLPurifier;
use HTMLPurifier_Config;

trait HasSanitizedHtml
{
    protected static function bootHasSanitizedHtml(): void
    {
        static::saving(function ($model) {
            foreach ($model->getSanitizedFields() as $field) {
                if ($model->isDirty($field) && $model->{$field} !== null) {
                    $model->{$field} = static::purifyHtml($model->{$field});
                }
            }
        });
    }

    abstract public function getSanitizedFields(): array;

    protected static function purifyHtml(string $html): string
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,b,i,strong,em,a[href],ul,ol,li,h2,h3,h4,blockquote,img[src|alt]');
        $config->set('AutoFormat.AutoParagraph', true);
        $config->set('HTML.TargetBlank', true);
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($html);
    }

    protected static function stripAllHtml(string $text): string
    {
        return strip_tags(trim($text));
    }
}
```

```php
// app/Models/CustomPage.php — agregar
use App\Traits\HasSanitizedHtml;

class CustomPage extends Model
{
    use HasSanitizedHtml;

    public function getSanitizedFields(): array
    {
        return ['body']; // HTML permitido (RichEditor)
    }
}
```

```php
// app/Models/WallPost.php — agregar
use App\Traits\HasSanitizedHtml;

class WallPost extends Model
{
    use HasSanitizedHtml;

    public function getSanitizedFields(): array
    {
        return ['body']; // Solo texto plano (strip all tags)
    }

    // Override para wall posts: NO permitir HTML
    protected static function purifyHtml(string $html): string
    {
        return static::stripAllHtml($html);
    }
}
```

#### Tests

| Test | Tipo | Descripción |
|------|------|-------------|
| CustomPage body sanitiza script tags | Unit | `<script>alert(1)</script>` → eliminado |
| CustomPage body permite tags seguros | Unit | `<p><strong>Hola</strong></p>` → intacto |
| WallPost body elimina TODO el HTML | Unit | `<b>texto</b>` → `texto` |
| Question body elimina HTML | Unit | Strip tags |
| XSS clásico eliminado | Unit | `"><img src=x onerror=alert(1)>` → eliminado |
| SVG XSS eliminado | Unit | `<svg onload=alert(1)>` → eliminado |
| Atributo event handler eliminado | Unit | `<div onclick="alert(1)">` → `<div>` |
| Link javascript: eliminado | Unit | `<a href="javascript:alert(1)">` → href eliminado |

#### Notas técnicas

- **Doble capa**: Sanitizar en el modelo (saving event) + sanitizar en el controller (input)
- **HTMLPurifier config por contexto**: CustomPage permite HTML rico (p, strong, img). WallPost/Question/Chat solo texto plano.
- **No sanitizar en read**: Sanitizar al escribir, no al leer. Evita overhead en cada request.
- **Filament RichEditor**: Ya escapa en frontend pero el backend DEBE sanitizar también (defensa en profundidad)

---

### SEC-1.3 Backend — Token Expiration + Refresh

**Estado:** 🔲 Pendiente
**Severidad:** CRITICO
**Componente:** eventos-backend + eventos-app

#### Problema

`config/sanctum.php` línea 50: `'expiration' => null` — tokens permanentes. Un token robado funciona para siempre.

#### Archivos a modificar

| Archivo | Cambio |
|---------|--------|
| `eventos-backend/config/sanctum.php` | `expiration => 10080` (7 días) |
| `eventos-backend/.env` | `SANCTUM_TOKEN_EXPIRATION=10080` |
| `eventos-backend/routes/api/auth.php` | Agregar `POST /auth/refresh` endpoint |
| `eventos-backend/app/Http/Controllers/Api/V1/AuthController.php` | Método `refresh()` |
| `eventos-app/lib/api.ts` | Interceptor 401 → auto-refresh → retry |
| `eventos-app/stores/authStore.ts` | Almacenar refresh_token en SecureStore |

#### Implementación Backend

```php
// config/sanctum.php
'expiration' => env('SANCTUM_TOKEN_EXPIRATION', 10080), // 7 días

// AuthController.php — nuevo método
public function refresh(Request $request): JsonResponse
{
    $user = $request->user();

    // Revocar token actual
    $request->user()->currentAccessToken()->delete();

    // Crear nuevo token
    $newToken = $user->createToken('app', ['*']);

    return response()->json([
        'token' => $newToken->plainTextToken,
        'expires_at' => now()->addMinutes(config('sanctum.expiration'))->toISOString(),
    ]);
}

// routes/api/auth.php
Route::post('/auth/refresh', [AuthController::class, 'refresh'])
    ->middleware('auth:sanctum');
```

#### Implementación App

```typescript
// eventos-app/lib/api.ts — interceptor de refresh
let isRefreshing = false;
let failedQueue: Array<{ resolve: Function; reject: Function }> = [];

async function refreshToken(): Promise<string | null> {
  const token = await SecureStore.getItemAsync('token');
  if (!token) return null;

  const res = await fetch(`${BASE_URL}/auth/refresh`, {
    method: 'POST',
    headers: {
      Authorization: `Bearer ${token}`,
      'Content-Type': 'application/json',
    },
  });

  if (!res.ok) return null;

  const data = await res.json();
  await SecureStore.setItemAsync('token', data.token);
  return data.token;
}

// En el fetch wrapper existente: si 401 → refresh → retry
// Si refresh falla → logout
```

#### Tests

| Test | Tipo | Descripción |
|------|------|-------------|
| Token expira después de 7 días | Feature | `travel(8)->days()` → 401 |
| Refresh genera token nuevo y revoca viejo | Feature | POST /auth/refresh → 200 + nuevo token |
| Token viejo no funciona post-refresh | Feature | Usar token revocado → 401 |
| Refresh con token expirado falla | Feature | 401 |
| Double refresh no genera errores | Feature | Race condition safe |

#### Notas técnicas

- **7 días** es un buen balance: no molesta al usuario frecuente, pero limita ventana de un token robado
- **Refresh = revoke + create**: No extender el mismo token, crear uno nuevo y revocar el anterior
- **App interceptor**: Si la API devuelve 401 → intentar refresh una vez → si falla → logout
- **Race condition**: Si 2 requests fallan simultáneamente, solo 1 debe hacer refresh. Los demás esperan en la queue.

---

## SESION SEC-2: Altos (día 3-4)

### SEC-2.1 Security Headers Middleware

**Estado:** 🔲 Pendiente
**Severidad:** ALTO
**Componente:** eventos-backend

#### Archivos a crear/modificar

| Archivo | Cambio |
|---------|--------|
| `app/Http/Middleware/SecurityHeaders.php` | **NUEVO** |
| `bootstrap/app.php` | Registrar middleware globalmente |

#### Implementación

```php
// app/Http/Middleware/SecurityHeaders.php — NUEVO
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '0'); // Deshabilitado en browsers modernos, CSP lo reemplaza
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // HSTS solo si no estamos en local
        if (!app()->isLocal()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // CSP básico — ajustar según necesidades de Filament
        if (!$request->is('admin/*')) {
            $response->headers->set(
                'Content-Security-Policy',
                "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https://fonts.gstatic.com"
            );
        }

        return $response;
    }
}
```

```php
// bootstrap/app.php — registrar
->withMiddleware(function (Middleware $middleware) {
    $middleware->statefulApi();
    $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
})
```

#### Tests

| Test | Tipo | Descripción |
|------|------|-------------|
| Response incluye X-Frame-Options: DENY | Feature | GET /api/v1/health → header presente |
| Response incluye X-Content-Type-Options | Feature | Cualquier endpoint → header presente |
| HSTS NO presente en local | Feature | APP_ENV=local → sin HSTS |
| HSTS SÍ presente en producción | Feature | APP_ENV=production → HSTS present |
| CSP NO se aplica a /admin/* | Feature | Filament necesita inline scripts |

#### Notas técnicas

- **X-XSS-Protection = 0**: Los browsers modernos lo deprecaron. CSP es la protección real.
- **CSP excluye admin/**: Filament usa inline scripts/styles que romperían con CSP estricto
- **Permissions-Policy**: Deshabilitamos camera/mic/geo a nivel de header. La app nativa maneja sus propios permisos.
- **Verificar post-deploy**: Usar https://securityheaders.com para validar

---

### SEC-2.2 CORS Hardening

**Estado:** 🔲 Pendiente
**Severidad:** ALTO
**Componente:** eventos-backend + eventos-socket

#### Backend — `config/cors.php`

```php
// ACTUAL (demasiado permisivo):
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],

// FIX:
'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
'allowed_headers' => ['Content-Type', 'Authorization', 'Accept', 'X-Requested-With', 'X-Internal-Secret'],

// PRODUCCIÓN: agregar dominios reales
'allowed_origins' => [
    env('FRONTEND_URL', 'http://localhost:3000'),
    // Agregar: https://app.tudominio.com, https://landing.tudominio.com
],
```

#### Socket — `src/index.ts:213`

```typescript
// ACTUAL (peligroso):
origin: config.allowedOrigins.length > 0 ? config.allowedOrigins : '*',

// FIX (fail-closed):
origin: config.allowedOrigins.length > 0 ? config.allowedOrigins : false,
```

#### Tests

| Test | Tipo | Descripción |
|------|------|-------------|
| CORS permite origen configurado | Feature | Origin header → Access-Control headers correctos |
| CORS rechaza origen no configurado | Feature | Origin desconocido → sin Access-Control headers |
| Socket rechaza si ALLOWED_ORIGINS vacío | Manual | Fallback = false, no * |
| Preflight OPTIONS funciona | Feature | OPTIONS request → 200 con headers |

---

### SEC-2.3 App — HTTPS Enforcement + Certificate Pinning

**Estado:** 🔲 Pendiente
**Severidad:** ALTO
**Componente:** eventos-app

#### Archivos a modificar

| Archivo | Cambio |
|---------|--------|
| `lib/api.ts` | Validación HTTPS en producción |
| `package.json` | Agregar `react-native-ssl-pinning` (requiere dev build) |
| `lib/pinnedFetch.ts` | **NUEVO** — fetch wrapper con pinning |

#### Implementación HTTPS enforcement

```typescript
// lib/api.ts — agregar al inicio
export const BASE_URL = process.env.EXPO_PUBLIC_API_URL ?? 'http://eventos-backend.test/api/v1';

if (__DEV__ === false && !BASE_URL.startsWith('https://')) {
  throw new Error('[SECURITY] Production API must use HTTPS. Check EXPO_PUBLIC_API_URL');
}
```

#### Certificate Pinning

```typescript
// lib/pinnedFetch.ts — NUEVO (solo funciona en dev build, no Expo Go)
import { fetch as pinnedFetch } from 'react-native-ssl-pinning';

// En producción: usar pinnedFetch con SHA-256 hash del cert
// En desarrollo: usar fetch estándar
export const secureFetch = __DEV__ ? fetch : (url: string, options: any) =>
  pinnedFetch(url, {
    ...options,
    sslPinning: {
      certs: ['cert_sha256_hash'], // SHA-256 del certificado del servidor
    },
  });
```

#### Notas técnicas

- **Certificate pinning requiere dev build** — NO funciona en Expo Go (misma limitación que S1.14 push notifications)
- **Pinning hash se actualiza** al renovar el certificado SSL → proceso de release debe incluir actualizar el hash
- **Fallback**: Si el pinning falla (cert renovado), el usuario no puede conectarse. Considerar: incluir 2 hashes (actual + backup)

---

### SEC-2.4 Secrets Validation Command

**Estado:** 🔲 Pendiente
**Severidad:** ALTO
**Componente:** eventos-backend

#### Archivo a crear

```
app/Console/Commands/SecurityCheckCommand.php — NUEVO
```

#### Implementación

```php
// php artisan security:check
class SecurityCheckCommand extends Command
{
    protected $signature = 'security:check';
    protected $description = 'Validate security configuration for production';

    public function handle(): int
    {
        $errors = [];

        // Debug mode
        if (config('app.debug')) {
            $errors[] = 'APP_DEBUG is true — must be false in production';
        }

        // Placeholder secrets
        $placeholders = ['change_this', 'your_secret', 'placeholder', 'default'];
        foreach (['app.qr_secret', 'services.socket.internal_secret'] as $key) {
            $value = config($key, '');
            foreach ($placeholders as $p) {
                if (str_contains(strtolower($value), $p)) {
                    $errors[] = "{$key} contains placeholder value";
                }
            }
        }

        // Session secure cookie
        if (config('session.secure') !== true) {
            $errors[] = 'SESSION_SECURE_COOKIE should be true for HTTPS';
        }

        // Token expiration
        if (config('sanctum.expiration') === null) {
            $errors[] = 'Sanctum token expiration is null — tokens never expire';
        }

        // APP_KEY
        if (empty(config('app.key'))) {
            $errors[] = 'APP_KEY is not set';
        }

        // Database password
        if (empty(config('database.connections.mysql.password'))) {
            $errors[] = 'Database password is empty';
        }

        // Redis password (si no es localhost)
        if (config('database.redis.default.host') !== '127.0.0.1'
            && empty(config('database.redis.default.password'))) {
            $errors[] = 'Redis password is empty for non-localhost host';
        }

        if (empty($errors)) {
            $this->info('All security checks passed.');
            return 0;
        }

        foreach ($errors as $e) {
            $this->error("FAIL: {$e}");
        }

        $this->error(count($errors) . ' security issue(s) found.');
        return 1; // Non-zero exit = CI/CD fails
    }
}
```

#### Tests

| Test | Tipo | Descripción |
|------|------|-------------|
| Command falla si APP_DEBUG=true | Feature | Exit code 1 |
| Command falla si secrets placeholder | Feature | Exit code 1 |
| Command pasa con config correcta | Feature | Exit code 0 |

#### Notas técnicas

- **Exit code 1** = CI/CD pipeline falla y no se despliega
- Ejecutar en GitHub Actions: `php artisan security:check` antes del deploy
- Agregar nuevas validaciones conforme se agreguen features de seguridad

---

### SEC-2.5 Backend — Session Secure Cookie

**Estado:** 🔲 Pendiente
**Severidad:** ALTO
**Componente:** eventos-backend

#### Cambio

```env
# .env.production
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
```

**Archivo:** `config/session.php` — ya lee de env, solo falta setear en producción.

---

### SEC-2.6 Backend — Secrets Placeholder

**Estado:** 🔲 Pendiente
**Severidad:** ALTO

#### Cambio

Crear `.env.production.example` con:

```env
APP_DEBUG=false
APP_QR_SECRET=       # GENERAR: openssl rand -hex 32
SOCKET_INTERNAL_SECRET=  # GENERAR: openssl rand -hex 32
DB_PASSWORD=          # GENERAR: password fuerte
REDIS_PASSWORD=       # GENERAR: password fuerte
SESSION_SECURE_COOKIE=true
SANCTUM_TOKEN_EXPIRATION=10080
```

---

## SESION SEC-3: Medios — 2FA + Device Fingerprint + Lockout (día 5-7)

### SEC-3.1 2FA (OTP por email/WhatsApp)

**Estado:** 🔲 Pendiente
**Severidad:** MEDIO
**Componente:** eventos-backend + eventos-app

#### Migration

```php
// create_otp_codes_table
Schema::create('otp_codes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('code', 6);           // 6 dígitos
    $table->string('channel', 20);       // 'email' | 'whatsapp' | 'sms'
    $table->unsignedTinyInteger('attempts')->default(0); // Max 3
    $table->timestamp('expires_at');
    $table->timestamp('verified_at')->nullable();
    $table->timestamps();

    $table->index(['user_id', 'code', 'expires_at']);
});
```

#### Columnas nuevas en `events`

```php
// alter_events_add_2fa_settings
$table->boolean('two_factor_enabled')->default(false);
$table->string('two_factor_channel', 20)->default('email'); // 'email' | 'whatsapp' | 'both'
```

#### Archivos a crear

| Archivo | Descripción |
|---------|-------------|
| `app/Services/OtpService.php` | generate(), verify(), canResend(), cleanup() |
| `app/Mail/OtpCodeMail.php` | Mailable para enviar OTP por email |
| `app/Http/Controllers/Api/V1/OtpController.php` | request-otp, verify-otp |
| `app/Http/Requests/Api/V1/Auth/RequestOtpRequest.php` | Validación |
| `app/Http/Requests/Api/V1/Auth/VerifyOtpRequest.php` | Validación |

#### Archivos a modificar

| Archivo | Cambio |
|---------|--------|
| `AuthController.php` → `login()` | Si 2FA habilitado: devolver `{ requires_2fa: true }` en vez de token |
| `routes/api/auth.php` | Agregar rutas OTP |
| Filament `EventResource` | Toggle 2FA + selector canal |

#### Flujo

```
Login request (email+password)
    ├── Credenciales válidas + 2FA OFF → devolver token (como ahora)
    └── Credenciales válidas + 2FA ON
        ├── Generar OTP 6 dígitos (random_int)
        ├── Guardar en otp_codes (expires_at = now+5min)
        ├── Enviar por canal configurado (email/WhatsApp)
        └── Response: { requires_2fa: true, channel: 'email', user_id: X }

Verify OTP request (user_id + code)
    ├── Code válido + no expirado + attempts < 3 → devolver token
    ├── Code inválido → attempts++ → error
    └── Code expirado o attempts >= 3 → error "solicita nuevo código"
```

#### Endpoints nuevos

| Método | Ruta | Auth | Descripción |
|--------|------|------|-------------|
| `POST` | `/api/v1/auth/request-otp` | No | Solicitar OTP (requiere user_id del login previo) |
| `POST` | `/api/v1/auth/verify-otp` | No | Verificar código → recibir token |

#### App — Pantalla OTP

Ya hay diseño en `design/onboarding/` (pantalla "Enter code" con 4 campos numéricos).

| Archivo app | Cambio |
|-------------|--------|
| `app/(auth)/verify-otp.tsx` | **NUEVO** — pantalla de ingreso de código |
| `app/(auth)/login.tsx` | Si response.requires_2fa → navegar a verify-otp |
| `lib/authApi.ts` | Agregar requestOtp(), verifyOtp() |

#### Tests

| Test | Tipo | Descripción |
|------|------|-------------|
| Login con 2FA OFF devuelve token directo | Feature | Comportamiento actual no cambia |
| Login con 2FA ON devuelve requires_2fa | Feature | No devuelve token |
| OTP se genera con 6 dígitos | Unit | random_int(100000, 999999) |
| OTP expira después de 5 min | Feature | travel(6)->minutes() → expired |
| OTP falla después de 3 intentos | Feature | attempts >= 3 → error |
| OTP válido devuelve token | Feature | POST verify-otp → token |
| OTP no se puede reutilizar | Feature | verified_at not null → error |
| Resend OTP invalida el anterior | Feature | Solo 1 OTP activo por user |
| Rate limit en request-otp | Feature | Max 3 requests en 5 min |

#### Notas técnicas

- **OTP = 6 dígitos** (100000-999999) generado con `random_int()` (cryptographically secure)
- **Expiración: 5 minutos** — suficiente para revisar email/WhatsApp
- **Max 3 intentos** por código generado — previene brute force (10^6 combinaciones / 3 intentos = inútil)
- **WhatsApp**: Usar API de WhatsApp Business con templates pre-aprobados. Fallback a email si WhatsApp falla.
- **Resend**: Invalidar OTP anterior, generar nuevo. Rate limit: max 3 resends en 5 min.
- **No revelar si el user existe**: Si el login falla, decir "credenciales inválidas" (no "usuario no encontrado" vs "contraseña incorrecta")

---

### SEC-3.2 Device Fingerprinting

**Estado:** 🔲 Pendiente
**Severidad:** MEDIO
**Componente:** eventos-backend + eventos-app

#### Migration

```php
// create_user_devices_table
Schema::create('user_devices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('fingerprint', 64)->index(); // SHA-256 del device info
    $table->string('device_name')->nullable();    // "iPhone 15 Pro" / "Samsung Galaxy S24"
    $table->string('platform', 20)->nullable();   // 'ios' | 'android' | 'web'
    $table->string('ip_address', 45)->nullable(); // IPv4/IPv6
    $table->timestamp('last_used_at');
    $table->boolean('trusted')->default(false);   // User puede marcar como trusted
    $table->timestamps();

    $table->unique(['user_id', 'fingerprint']);
});
```

#### Flujo

```
Login exitoso (post-OTP si aplica)
    ├── Calcular fingerprint del device (hash de: platform + device model + app version)
    ├── Buscar en user_devices
    ├── Device conocido → update last_used_at → emitir token
    └── Device NUEVO
        ├── Si 2FA habilitado → forzar OTP (incluso si ya verificó en este login)
        ├── Guardar device en user_devices
        ├── Enviar email: "Nuevo inicio de sesión desde [device_name]"
        └── Emitir token
```

#### Endpoints

| Método | Ruta | Auth | Descripción |
|--------|------|------|-------------|
| `GET` | `/api/v1/auth/devices` | Sí | Listar dispositivos del usuario |
| `DELETE` | `/api/v1/auth/devices/{id}` | Sí | Eliminar dispositivo (cerrar sesión remota) |
| `PATCH` | `/api/v1/auth/devices/{id}/trust` | Sí | Marcar como trusted |

#### App — Info del device

```typescript
// lib/deviceInfo.ts — NUEVO
import * as Device from 'expo-device';
import * as Crypto from 'expo-crypto';

export async function getDeviceFingerprint(): Promise<string> {
  const raw = [
    Device.brand,
    Device.modelName,
    Device.osName,
    Device.osVersion,
    // NO incluir device ID (cambia en reinstall)
  ].join('|');

  return await Crypto.digestStringAsync(
    Crypto.CryptoDigestAlgorithm.SHA256,
    raw
  );
}

export function getDeviceName(): string {
  return `${Device.brand} ${Device.modelName}` || 'Unknown Device';
}
```

#### Tests

| Test | Tipo | Descripción |
|------|------|-------------|
| Login registra device | Feature | user_devices row created |
| Device conocido no requiere 2FA extra | Feature | fingerprint match → skip |
| Device nuevo fuerza 2FA si habilitado | Feature | fingerprint new → requires_2fa |
| Email de nuevo device se envía | Feature | Mail::assertSent |
| DELETE device revoca tokens de ese device | Feature | Token invalidado |
| Listar devices funciona | Feature | GET /auth/devices → array |

---

### SEC-3.3 Account Lockout

**Estado:** 🔲 Pendiente
**Severidad:** MEDIO
**Componente:** eventos-backend

#### Migration

```php
// alter_users_add_lockout
$table->timestamp('locked_until')->nullable()->after('remember_token');
$table->unsignedTinyInteger('failed_login_attempts')->default(0)->after('locked_until');
```

#### Lógica en AuthController::login()

```php
// Antes de verificar password:
if ($user->locked_until && $user->locked_until->isFuture()) {
    $minutes = $user->locked_until->diffInMinutes(now());
    return response()->json([
        'message' => "Cuenta bloqueada. Intenta en {$minutes} minutos.",
        'locked_until' => $user->locked_until->toISOString(),
    ], 423); // 423 Locked
}

// Si password incorrecto:
$user->increment('failed_login_attempts');
if ($user->failed_login_attempts >= 5) {
    $user->update(['locked_until' => now()->addMinutes(30)]);
    // Enviar email: "Tu cuenta fue bloqueada por intentos fallidos"
    Mail::to($user)->send(new AccountLockedMail($user));
}

// Si login exitoso:
$user->update(['failed_login_attempts' => 0, 'locked_until' => null]);
```

#### Filament — Admin puede desbloquear

Agregar acción "Desbloquear" en AttendeeResource con:
```php
$user->update(['failed_login_attempts' => 0, 'locked_until' => null]);
```

#### Tests

| Test | Tipo | Descripción |
|------|------|-------------|
| 4 intentos fallidos: no lockout | Feature | still 200/401 |
| 5 intentos fallidos: lockout 30 min | Feature | 423 response |
| Login exitoso resetea contador | Feature | failed_attempts = 0 |
| Después de 30 min: puede intentar de nuevo | Feature | travel(31)->minutes() → allowed |
| Email de lockout se envía | Feature | Mail::assertSent |
| Admin puede desbloquear | Feature | Filament action → locked_until = null |

---

### SEC-3.4 Socket Rate Limiting → Redis

**Estado:** 🔲 Pendiente
**Severidad:** MEDIO
**Componente:** eventos-socket

#### Archivos a crear/modificar

| Archivo | Cambio |
|---------|--------|
| `src/rateLimit.ts` | **NUEVO** — Rate limiter Redis-backed |
| `src/chat.ts` | Reemplazar Maps por Redis rate limiter |
| `src/index.ts` | Rate limit en join:event, join:session |
| `src/index.ts` | Max conexiones por usuario |

#### Implementación

```typescript
// src/rateLimit.ts — NUEVO
import { Redis } from 'ioredis';

export async function checkRateLimit(
  redis: Redis,
  key: string,
  maxAttempts: number,
  windowSeconds: number
): Promise<{ allowed: boolean; remaining: number; retryAfter: number }> {
  const current = await redis.incr(key);
  if (current === 1) {
    await redis.expire(key, windowSeconds);
  }
  const ttl = await redis.ttl(key);
  return {
    allowed: current <= maxAttempts,
    remaining: Math.max(0, maxAttempts - current),
    retryAfter: current > maxAttempts ? ttl : 0,
  };
}

// Rate limits por evento:
// chat:send     → 1 msg / 2 sec  (key: rl:chat:{attendeeId}:{sessionId})
// chat:emoji    → 1 / 1 sec      (key: rl:emoji:{attendeeId}:{sessionId})
// join:event    → 5 / 60 sec     (key: rl:join:event:{attendeeId})
// join:session  → 10 / 60 sec    (key: rl:join:session:{attendeeId})
// poll:vote     → 3 / 10 sec     (key: rl:poll:{attendeeId})
// question      → 2 / 30 sec     (key: rl:question:{attendeeId})
```

#### Max conexiones por usuario

```typescript
// src/index.ts — agregar en io.on('connection')
const MAX_CONNECTIONS_PER_USER = 5;
const userConnections = new Map<number, Set<string>>();

io.on('connection', (socket) => {
  const userId = socket.data.user.id;
  if (!userConnections.has(userId)) {
    userConnections.set(userId, new Set());
  }
  const conns = userConnections.get(userId)!;
  if (conns.size >= MAX_CONNECTIONS_PER_USER) {
    socket.emit('error', { code: 'MAX_CONNECTIONS' });
    socket.disconnect();
    return;
  }
  conns.add(socket.id);
  socket.on('disconnect', () => {
    conns.delete(socket.id);
    if (conns.size === 0) userConnections.delete(userId);
  });
});
```

---

### SEC-3.5 FormRequests para todos los endpoints

**Estado:** 🔲 Pendiente
**Severidad:** MEDIO
**Componente:** eventos-backend

#### FormRequests a crear

| FormRequest | Controller | Validaciones |
|-------------|-----------|-------------|
| `StoreWallPostRequest` | WallController@store | body:string,max:1000, image:nullable,image,max:10240 |
| `StoreEventPhotoRequest` | EventPhotoController@store | photo:required,image,max:10240 |
| `UpdateProfileRequest` | ProfileController@update | name:string,max:255, phone:nullable,string, etc. |
| `StoreQuestionRequest` | QuestionController@store | body:required,string,max:500, session_id:required,exists |
| `VotePollRequest` | PollController@vote | option_id:required,exists |
| `StoreSessionRatingRequest` | SessionRatingController@store | score:required,integer,1-5, comment:nullable,max:500 |
| `SendMatchRequestRequest` | NetworkingController@sendRequest | target_attendee_id:required,exists |
| `ReportContentRequest` | ReportController@store | reportable_type, reportable_id, reason |

#### Notas técnicas

- **Solo mover** validación existente de controllers a FormRequests. NO cambiar reglas.
- **authorize()** en cada FormRequest: retornar true si auth middleware ya cubre
- Cada FormRequest tiene su test de validación

---

## SESION SEC-4: Infraestructura (día 8-9)

### SEC-4.1 Docker Security — `docker-compose.prod.yml`

**Estado:** 🔲 Pendiente

```yaml
# Principios:
# 1. Non-root containers
# 2. Read-only donde posible
# 3. Network isolation
# 4. Resource limits
# 5. Health checks
# 6. Pinned image versions

services:
  nginx:
    image: nginx:1.27-alpine
    ports: ["80:80", "443:443"]
    networks: [frontend, backend]
    read_only: true
    tmpfs: [/tmp, /var/cache/nginx, /var/run]
    deploy:
      resources:
        limits: { cpus: '0.5', memory: 256M }
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/health"]
      interval: 30s
      retries: 3

  app:
    build: ./eventos-backend
    user: "1000:1000"  # non-root
    networks: [backend]
    read_only: true
    tmpfs: [/tmp, /var/www/html/storage/framework/cache, /var/www/html/storage/framework/sessions, /var/www/html/storage/framework/views]
    deploy:
      resources:
        limits: { cpus: '1.0', memory: 512M }
    healthcheck:
      test: ["CMD", "php", "artisan", "security:check"]
      interval: 60s

  socket:
    build: ./eventos-socket
    user: "1000:1000"
    networks: [backend]
    deploy:
      resources:
        limits: { cpus: '0.5', memory: 256M }

  redis:
    image: redis:7.4-alpine
    command: redis-server --requirepass ${REDIS_PASSWORD} --maxmemory 128mb --maxmemory-policy allkeys-lru
    networks: [backend]  # NO frontend
    volumes: [redis-data:/data]
    deploy:
      resources:
        limits: { cpus: '0.25', memory: 192M }

  mysql:
    image: mysql:8.4
    networks: [backend]  # NO frontend
    volumes: [mysql-data:/var/lib/mysql]
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}        # User dedicado, no root
      MYSQL_PASSWORD: ${DB_PASSWORD}
    deploy:
      resources:
        limits: { cpus: '1.0', memory: 1G }

networks:
  frontend:    # Solo nginx expuesto
  backend:
    internal: true  # No accesible desde fuera
```

### SEC-4.2 Server Hardening Checklist

| Item | Comando/Archivo | Estado |
|------|----------------|--------|
| UFW firewall | `ufw allow 22/tcp && ufw allow 80/tcp && ufw allow 443/tcp && ufw enable` | 🔲 |
| SSH key-only | `/etc/ssh/sshd_config`: `PasswordAuthentication no` | 🔲 |
| Fail2ban | `apt install fail2ban` + config para sshd + nginx | 🔲 |
| Non-root user | `adduser deploy && usermod -aG docker deploy` | 🔲 |
| Unattended upgrades | `apt install unattended-upgrades && dpkg-reconfigure -plow unattended-upgrades` | 🔲 |
| Swap limitado | `sysctl vm.swappiness=10` | 🔲 |
| Audit logging | `apt install auditd` + rules para /etc, /var/log | 🔲 |

### SEC-4.3 Cloudflare Config Checklist

| Item | Ubicación en Cloudflare | Valor | Estado |
|------|------------------------|-------|--------|
| SSL mode | SSL/TLS → Overview | Full (Strict) | 🔲 |
| Always Use HTTPS | SSL/TLS → Edge Certificates | ON | 🔲 |
| HSTS | SSL/TLS → Edge Certificates | Max-Age 6 months, includeSubDomains | 🔲 |
| Min TLS version | SSL/TLS → Edge Certificates | TLS 1.2 | 🔲 |
| Bot Fight Mode | Security → Bots | ON | 🔲 |
| Challenge Passage | Security → Settings | 30 minutes | 🔲 |
| Browser Integrity Check | Security → Settings | ON | 🔲 |
| Rate limiting login | Security → WAF → Rate Limiting | `/api/v1/auth/login` → 10 req/min | 🔲 |
| Rate limiting register | Security → WAF → Rate Limiting | `/api/v1/auth/register` → 5 req/min | 🔲 |
| Block known threats | Security → WAF → Managed Rules | OWASP Core Ruleset (Pro) | 🔲 |
| Cache static assets | Caching → Cache Rules | `*.jpg,*.png,*.css,*.js` → 1 month | 🔲 |
| Disable server header | Network | ON | 🔲 |

### SEC-4.4 Backup Strategy

```
Diario (cron 02:00 UTC):
├── mysqldump → compress → encrypt (GPG) → upload R2/S3
├── Redis RDB snapshot → encrypt → upload
├── Retención: 30 días daily, 12 meses monthly
└── Test de restore: mensual

Script: /opt/eventos/backup.sh (propiedad de root, 700)
Monitoreo: alertar si backup no se ejecutó en 26 horas
```

---

## SESION SEC-5: Monitoreo y Logging (día 10)

### SEC-5.1 Security Event Logging

**Estado:** 🔲 Pendiente
**Componente:** eventos-backend

#### Archivo a crear

```
app/Services/SecurityLogger.php — NUEVO
```

#### Eventos a loggear

| Evento | Nivel | Datos |
|--------|-------|-------|
| Login exitoso | info | user_id, ip, device, timestamp |
| Login fallido | warning | email_attempted, ip, user_agent, timestamp |
| Account locked | warning | user_id, ip, failed_attempts |
| OTP generated | info | user_id, channel, timestamp |
| OTP verified | info | user_id, timestamp |
| OTP failed | warning | user_id, attempts, timestamp |
| New device detected | notice | user_id, device_name, ip |
| Token refreshed | info | user_id, old_token_id, new_token_id |
| Admin action | info | admin_id, action, resource, resource_id |
| File upload | info | user_id, filename, size, mime |
| Rate limit hit | warning | ip, endpoint, count |
| Socket room denied | warning | attendee_id, event_id, reason |

#### Implementación

```php
// Logging channel dedicado en config/logging.php
'security' => [
    'driver' => 'daily',
    'path' => storage_path('logs/security.log'),
    'level' => 'info',
    'days' => 90,
],

// SecurityLogger::log('login_failed', ['email' => $email, 'ip' => $ip]);
// Escribe en canal 'security' con formato JSON structured
```

### SEC-5.2 Error Tracking (Sentry)

**Estado:** 🔲 Pendiente

#### Dependencias

| Paquete | Componente |
|---------|-----------|
| `sentry/sentry-laravel` | Backend |
| `@sentry/react-native` | App |

#### Config

```env
# Backend .env
SENTRY_LARAVEL_DSN=https://xxx@sentry.io/yyy
SENTRY_TRACES_SAMPLE_RATE=0.1  # 10% de requests

# App .env
EXPO_PUBLIC_SENTRY_DSN=https://xxx@sentry.io/zzz
```

### SEC-5.3 Uptime Monitoring

| Endpoint | Herramienta | Intervalo |
|----------|------------|-----------|
| `GET /api/v1/health` | BetterStack o UptimeRobot | 1 min |
| `wss://socket.dominio.com` | BetterStack | 1 min |
| Landing web | Cloudflare Analytics | Automático |

Alerta → email + push al admin si downtime > 2 minutos.

---

## Checklist Global de Progreso

```
SESION SEC-1: CRITICOS
🔲 SEC-1.1  Socket room authorization (endpoint + validación + cache + tests)
🔲 SEC-1.2  HTMLPurifier trait + aplicar a modelos + tests XSS
🔲 SEC-1.3  Token expiration + refresh endpoint + app interceptor + tests

SESION SEC-2: ALTOS
🔲 SEC-2.1  SecurityHeaders middleware + registrar + tests
🔲 SEC-2.2  CORS hardening backend + socket fallback + tests
🔲 SEC-2.3  HTTPS enforcement app + certificate pinning
🔲 SEC-2.4  security:check artisan command + tests
🔲 SEC-2.5  Session secure cookie en producción
🔲 SEC-2.6  .env.production.example sin placeholders

SESION SEC-3: MEDIOS
🔲 SEC-3.1  2FA OTP (migration + service + endpoints + mail + app screen + tests)
🔲 SEC-3.2  Device fingerprinting (migration + middleware + endpoints + app + tests)
🔲 SEC-3.3  Account lockout (migration + lógica + mail + Filament action + tests)
🔲 SEC-3.4  Socket rate limiting → Redis + max conexiones por user
🔲 SEC-3.5  FormRequests para todos los endpoints con input

SESION SEC-4: INFRAESTRUCTURA
🔲 SEC-4.1  docker-compose.prod.yml (non-root, network isolation, limits, health)
🔲 SEC-4.2  Server hardening (UFW, SSH, Fail2ban, non-root, updates)
🔲 SEC-4.3  Cloudflare config (SSL Strict, HSTS, WAF, rate limits, bot fight)
🔲 SEC-4.4  Backup strategy (daily encrypted, retention 30d, test restore)

SESION SEC-5: MONITOREO
🔲 SEC-5.1  Security event logging (SecurityLogger + canal dedicado + 90 días)
🔲 SEC-5.2  Sentry (backend + app)
🔲 SEC-5.3  Uptime monitoring (BetterStack/UptimeRobot)
```

**Total: 21 items | Estimado: ~10 días de implementación**

---

## Dependencias nuevas por sesión

| Sesión | Paquete | Componente | Instalar cuándo |
|--------|---------|-----------|----------------|
| SEC-1.3 | — | Backend | Solo config change |
| SEC-2.3 | `react-native-ssl-pinning` | App | Requiere dev build |
| SEC-3.1 | — | Backend | Solo código nuevo |
| SEC-3.2 | `expo-device`, `expo-crypto` | App | Ya incluidos en Expo |
| SEC-5.2 | `sentry/sentry-laravel` | Backend | Cuando se configure Sentry |
| SEC-5.2 | `@sentry/react-native` | App | Requiere dev build |
| SEC-3.4 | `zod` (opcional) | Socket | Para schema validation |

---

*Documento vivo — se actualiza con ✅ conforme se implementa cada item.*
*Próxima revisión de seguridad: pre-deploy y cada 3 meses.*
