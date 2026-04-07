# Fase de Seguridad — EventOS

> Auditoría completa + plan de remediación para producción.
> Fecha auditoría: 2026-04-07 | Estado: Planificación

---

## Resumen Ejecutivo

Se auditaron los 3 componentes del sistema:
- **eventos-backend** (Laravel + Sanctum + Filament)
- **eventos-socket** (Socket.IO + Redis + TypeScript)
- **eventos-app** (Expo/React Native)

### Hallazgos por severidad

| Severidad | Cantidad | Componente(s) |
|-----------|----------|---------------|
| CRITICO | 3 | Socket, Backend |
| ALTO | 6 | Backend, Socket, App, Infra |
| MEDIO | 8 | Todos |
| BAJO | 4 | App, Backend |

---

## 1. Hallazgos CRITICOS (Fix antes de producción)

### 1.1 Socket — Room Authorization INEXISTENTE

**Archivo:** `eventos-socket/src/index.ts` (líneas 262-274) y `src/chat.ts` (líneas 86-104)

**Problema:** Cualquier usuario autenticado puede unirse a CUALQUIER room de CUALQUIER evento simplemente enviando un `eventId` o `sessionId`. No hay validación server-side.

```typescript
// ACTUAL: sin validación
socket.on('join:event', ({ eventId }) => {
  void socket.join(Rooms.event(eventId)); // Se une sin verificar acceso
});
```

**Escenarios de ataque:**
- Espionaje: unirse a eventos ajenos y ver anuncios, polls, Q&A
- Poll stuffing: votar en encuestas de sesiones a las que no asiste
- Chat interception: leer conversaciones de sesiones privadas

**Fix requerido:**
```typescript
socket.on('join:event', async ({ eventId }) => {
  const hasAccess = await validateEventAccess(user.attendeeId, eventId);
  if (!hasAccess) {
    socket.emit('error', 'EVENT_ACCESS_DENIED');
    return;
  }
  await socket.join(Rooms.event(eventId));
});
```

### 1.2 Backend — HTMLPurifier instalado pero NO usado

**Archivo:** `composer.json` tiene `ezyang/htmlpurifier ^4.19` pero NUNCA se aplica.

**Problema:** Campos de contenido HTML (CustomPage body, wall posts, etc.) se almacenan y devuelven sin sanitizar. Riesgo de XSS almacenado.

**Archivos afectados:**
- `app/Filament/Resources/CustomPageResource.php` — dice "Se sanitiza automáticamente" pero NO
- Wall posts, chat messages, cualquier campo de texto rico

**Fix requerido:** Implementar sanitización en model mutators o observers:
```php
protected function body(): Attribute
{
    return Attribute::make(
        set: fn ($value) => clean($value) // HTMLPurifier
    );
}
```

### 1.3 Backend — Tokens Sanctum NUNCA expiran

**Archivo:** `config/sanctum.php` línea 50: `'expiration' => null`

**Problema:** Un token robado funciona para siempre. No hay mecanismo de rotación ni expiración.

**Fix requerido:**
```php
'expiration' => 60 * 24 * 7, // 7 días
```
Más: implementar refresh token flow en la app.

---

## 2. Hallazgos ALTOS (Fix antes de release mayor)

### 2.1 Sin Security Headers en NINGÚN componente

**Problema:** Ni backend ni socket envían headers de seguridad estándar.

**Headers faltantes:**
| Header | Protege contra |
|--------|---------------|
| `X-Frame-Options: DENY` | Clickjacking |
| `X-Content-Type-Options: nosniff` | MIME sniffing |
| `Content-Security-Policy` | XSS, injection |
| `Strict-Transport-Security` | Downgrade HTTPS→HTTP |
| `Referrer-Policy: strict-origin-when-cross-origin` | Fuga de información |
| `Permissions-Policy` | Acceso a APIs del navegador |

**Fix:** Crear middleware Laravel `SecurityHeaders` y aplicar globalmente.

### 2.2 Socket — CORS fallback a wildcard `*`

**Archivo:** `eventos-socket/src/index.ts` línea 213

```typescript
origin: config.allowedOrigins.length > 0 ? config.allowedOrigins : '*',
```

**Problema:** Si `ALLOWED_ORIGINS` está vacío, CUALQUIER origen puede conectarse. Combinado con `credentials: true` = CSRF risk.

**Fix:** Cambiar fallback a `false` (rechazar si no configurado).

### 2.3 App — Sin Certificate Pinning

**Problema:** La app usa `fetch()` estándar sin pinning. Vulnerable a MITM en redes comprometidas (WiFi público en el venue del evento, por ejemplo).

**Fix:** Implementar certificate pinning con `react-native-ssl-pinning` o configuración nativa.

### 2.4 App — HTTP en desarrollo (obvio) pero SIN enforcing HTTPS en producción

**Archivo:** `eventos-app/.env`: `EXPO_PUBLIC_API_URL=http://192.168.50.142/api/v1`

**Problema:** No hay nada que fuerce HTTPS en producción. La URL se cambia manualmente.

**Fix:** Agregar validación en `api.ts`:
```typescript
if (__DEV__ === false && !BASE_URL.startsWith('https://')) {
  throw new Error('Production API must use HTTPS');
}
```

### 2.5 Backend — CORS demasiado permisivo

**Archivo:** `config/cors.php`
- `allowed_methods => ['*']` — debería ser solo `['GET', 'POST', 'PUT', 'PATCH', 'DELETE']`
- `allowed_headers => ['*']` — debería listar solo los necesarios

### 2.6 Backend — Secretos placeholder en .env

**Archivo:** `.env`
```
APP_QR_SECRET=change_this_secret_in_production
SOCKET_INTERNAL_SECRET=change_this_secret_in_production
```

**Fix:** Proceso de deployment DEBE validar que no existan placeholders.

---

## 3. Hallazgos MEDIOS

### 3.1 Sin 2FA en ningún nivel

**Estado actual:** Login solo con email+password. Sin segundo factor.

**Plan:** Implementar OTP de 6 dígitos:
- Nueva tabla `otp_codes` (user_id, code, channel, expires_at, verified_at)
- Canales: email (siempre), WhatsApp (configurable)
- Toggle por evento desde admin Filament
- Expiración: 5 minutos
- Max 3 intentos por OTP generado

### 3.2 Socket — Rate limiting solo en memoria

**Archivo:** `eventos-socket/src/chat.ts` líneas 35-39

**Problema:** Rate limits se almacenan en Maps de JavaScript. No persisten entre reinicios ni escalan a múltiples instancias.

**Fix:** Migrar a Redis-backed rate limiting:
```typescript
async function checkRateLimit(redis: Redis, key: string, limit: number, windowMs: number): Promise<boolean> {
  const count = await redis.incr(key);
  if (count === 1) await redis.pexpire(key, windowMs);
  return count <= limit;
}
```

### 3.3 Socket — Sin rate limiting en eventos no-chat

**Problema:** Solo `chat:send` y `chat:emoji` tienen rate limit. Faltan:
- `join:event` / `join:session` — sin límite
- `poll:vote` — sin límite
- `question:submit` — sin límite
- `wall:post` — sin límite

### 3.4 Socket — Sin límite de conexiones por usuario

**Problema:** Un usuario puede abrir 1000+ conexiones paralelas. Cada una tiene rate limiters independientes.

**Fix:** Max 3-5 conexiones simultáneas por usuario.

### 3.5 App — Sin detección de jailbreak/root

**Problema:** App corre sin restricciones en dispositivos comprometidos.

**Fix:** Agregar detección con `expo-device` o similar. No bloquear, pero alertar al backend.

### 3.6 Backend — FormRequests solo en auth

**Problema:** Solo 5 FormRequests existen (todos de auth). El resto de controladores usa `$request->validate()` inline.

**Implicación:** Validación dispersa, difícil de auditar, fácil de olvidar.

**Fix:** Crear FormRequests para todos los endpoints que reciben input del usuario.

### 3.7 Backend — Session cookie sin flag `secure`

**Archivo:** `config/session.php` — `SESSION_SECURE_COOKIE` no está seteado.

**Fix:** En producción: `SESSION_SECURE_COOKIE=true`

### 3.8 App — Sin obfuscación de código Android

**Archivo:** `android/app/build.gradle` línea 119: `minifyEnabled` defaults to false.

**Fix:** Habilitar R8/ProGuard en release builds.

---

## 4. Hallazgos BAJOS

### 4.1 App — Sin autenticación biométrica

No implementada. Nice-to-have para futuro.

### 4.2 Backend — Debug mode ON en local

`APP_DEBUG=true` — correcto para dev, pero necesita proceso de deploy que lo valide.

### 4.3 App — Deep link validation parcial

El token de `eventos://activate-account?token=X` se envía directo al backend. El backend valida, pero la app podría validar formato primero.

### 4.4 Socket — Sin cleanup de rate limit maps

Las Maps de rate limiting crecen indefinidamente. Necesitan limpieza periódica.

---

## 5. Lo que Cloudflare SÍ cubre

Con dominio en Cloudflare (plan gratuito o Pro) se obtiene:

| Protección | Cloudflare | Todavía necesitas en código |
|-----------|-----------|---------------------------|
| DDoS L3/L4 | ✅ Automático | Nada extra |
| DDoS L7 (HTTP) | ✅ Con reglas WAF | Rate limiting en app |
| HTTPS/TLS | ✅ SSL flexible o full | Cert en origin server |
| HSTS headers | ✅ Configurable | Backup en middleware |
| Bot protection | ✅ Básico gratis, avanzado Pro | CAPTCHA en forms |
| IP blocking | ✅ Firewall rules | — |
| Rate limiting | ✅ Básico (10K req/mes gratis) | Rate limiting granular en código |
| WAF rules | ✅ OWASP core rules (Pro) | Input validation en código |
| Page rules | ✅ Cache, redirects | — |

**IMPORTANTE:** Cloudflare NO protege contra:
- Vulnerabilidades de lógica de negocio (room authorization, token expiration)
- XSS almacenado (si ya pasó y está en DB)
- SQL injection (WAF ayuda pero no es infalible)
- Secrets expuestos en código
- Problemas de autenticación/autorización

**Cloudflare es la primera línea de defensa, pero NO reemplaza seguridad en código.**

---

## 6. Lo que necesitas en infraestructura (VPS/Docker)

### 6.1 Hardening del servidor

| Tarea | Descripción | Prioridad |
|-------|-------------|-----------|
| **Firewall (UFW/iptables)** | Solo puertos 80, 443, 22 abiertos. Redis y MySQL solo localhost | CRITICO |
| **SSH key-only** | Deshabilitar password auth en SSH | CRITICO |
| **Fail2ban** | Ban automático de IPs con intentos fallidos de SSH/HTTP | ALTO |
| **Non-root user** | App corre con usuario no-root | CRITICO |
| **Automatic updates** | Unattended upgrades para parches de seguridad OS | ALTO |
| **Disk encryption** | LUKS en volumen de datos | MEDIO |
| **Backup encriptado** | Backups diarios de DB encriptados con GPG | ALTO |

### 6.2 Docker security

| Tarea | Descripción | Prioridad |
|-------|-------------|-----------|
| **Non-root containers** | Todos los containers corren con usuario non-root | ALTO |
| **Read-only filesystem** | Containers con `read_only: true` donde posible | MEDIO |
| **No latest tags** | Pinear versiones específicas de imágenes | ALTO |
| **Secrets management** | Docker secrets o .env encriptado, NO en docker-compose | CRITICO |
| **Network isolation** | Red interna para Redis/MySQL, solo nginx expuesto | CRITICO |
| **Resource limits** | CPU/memory limits en cada container | MEDIO |
| **Health checks** | Healthchecks para restart automático | ALTO |

### 6.3 Redis en producción

| Tarea | Descripción | Prioridad |
|-------|-------------|-----------|
| **Password auth** | `requirepass` obligatorio | CRITICO |
| **Bind localhost** | Solo acepta conexiones locales | CRITICO |
| **TLS** | Si Redis está en otro servidor | ALTO si aplica |
| **Rename commands** | Deshabilitar `FLUSHDB`, `FLUSHALL`, `CONFIG` | MEDIO |
| **Maxmemory policy** | `allkeys-lru` para evitar OOM | ALTO |

### 6.4 MySQL en producción

| Tarea | Descripción | Prioridad |
|-------|-------------|-----------|
| **Password fuerte** | No más `root` sin password | CRITICO |
| **Usuario dedicado** | User con permisos mínimos (solo SELECT/INSERT/UPDATE/DELETE en su DB) | CRITICO |
| **Bind localhost** | Solo conexiones locales | CRITICO |
| **SSL connections** | Si DB en otro servidor | ALTO si aplica |
| **Audit plugin** | Log de queries privilegiadas | MEDIO |
| **Backups automáticos** | mysqldump encriptado diario + binary log | ALTO |

---

## 7. Plan de Implementación — Fase Seguridad

### Paso 1: CRITICOS (día 1-2)

```
1.1 Socket room authorization
    ├── Crear endpoint interno Laravel: POST /internal/validate-access
    ├── Validar attendee pertenece al event en join:event
    ├── Validar attendee está registrado en session para join:session
    ├── Cache resultado en Redis (5 min TTL)
    └── Tests

1.2 HTMLPurifier activado
    ├── Crear trait HasSanitizedContent o usar model observer
    ├── Aplicar a: CustomPage, WallPost, ChatMessage
    ├── Sanitizar en write (no en read)
    └── Tests

1.3 Token expiration
    ├── Configurar sanctum.expiration = 10080 (7 días)
    ├── Implementar refresh token endpoint
    ├── App: interceptor que refresh automáticamente
    └── Tests
```

### Paso 2: ALTOS (día 3-4)

```
2.1 Security headers middleware
    ├── Crear app/Http/Middleware/SecurityHeaders.php
    ├── X-Frame-Options, X-Content-Type-Options, CSP, HSTS, Referrer-Policy
    ├── Registrar en bootstrap/app.php globalmente
    └── Verificar con securityheaders.com

2.2 CORS hardening
    ├── Backend: restringir allowed_methods y allowed_headers
    ├── Socket: cambiar fallback * → false
    ├── Agregar dominios de producción
    └── Verificar con curl

2.3 App HTTPS enforcement
    ├── Validación en api.ts para producción
    ├── Certificate pinning (react-native-ssl-pinning)
    └── Tests

2.4 Secrets validation
    ├── Crear artisan command: php artisan security:check
    ├── Valida: no placeholders, debug=false, secure cookie, etc.
    ├── Ejecutar en CI/CD antes de deploy
    └── Falla si encuentra problemas
```

### Paso 3: MEDIOS (día 5-7)

```
3.1 2FA (OTP)
    ├── Migration: create_otp_codes_table
    ├── OtpService: generate, verify, canResend
    ├── Canales: email (Mail), WhatsApp (API)
    ├── Endpoints: POST /auth/request-otp, POST /auth/verify-otp
    ├── Toggle por evento en Filament
    ├── App: pantalla "Enter Code" (ya hay diseño en onboarding/)
    └── Tests

3.2 Socket rate limiting → Redis
    ├── Migrar Maps a Redis INCR/EXPIRE
    ├── Agregar rate limits a: join:event, join:session, poll:vote, question:submit
    ├── Max 5 conexiones por usuario
    ├── Cleanup automático con TTL
    └── Tests

3.3 Device fingerprinting
    ├── Migration: create_user_devices_table (user_id, fingerprint, name, last_used, trusted)
    ├── Middleware que registra device en login
    ├── Device nuevo + 2FA habilitado = forzar OTP
    ├── Endpoint: GET /auth/devices, DELETE /auth/devices/{id}
    ├── App: pantalla "Dispositivos activos"
    └── Tests

3.4 FormRequests
    ├── Crear FormRequest para cada endpoint que recibe input
    ├── Mínimo: WallPost, EventPhoto, Profile, Networking
    └── Auditar controllers por validate() inline

3.5 Account lockout
    ├── Campo locked_until en users
    ├── 5 intentos fallidos → lock 30 min
    ├── Notificar por email al usuario
    ├── Admin puede desbloquear desde Filament
    └── Tests
```

### Paso 4: Infraestructura (día 8-9)

```
4.1 Docker security
    ├── docker-compose.prod.yml con todas las restricciones
    ├── Non-root containers
    ├── Network isolation (internal network para Redis/MySQL)
    ├── Resource limits
    ├── Health checks
    └── .env.production template

4.2 Server hardening
    ├── UFW firewall rules
    ├── SSH key-only + Fail2ban
    ├── Non-root user para app
    ├── Unattended upgrades
    └── Documentar en runbook

4.3 Redis + MySQL production
    ├── Passwords fuertes
    ├── Bind localhost
    ├── Usuario MySQL dedicado con permisos mínimos
    ├── Backup script (daily, encrypted)
    └── Monitoreo con alertas

4.4 Cloudflare config
    ├── SSL mode: Full (Strict)
    ├── HSTS habilitado
    ├── Bot fight mode ON
    ├── Rate limiting rules (login, register)
    ├── Page rules (cache estáticos)
    ├── Firewall rules (block known bad actors)
    └── WAF OWASP rules (Pro plan recomendado)
```

### Paso 5: Monitoreo y respuesta (día 10)

```
5.1 Logging
    ├── Log structured (JSON) para todos los eventos de seguridad
    ├── Failed logins con IP, user-agent, timestamp
    ├── Admin actions (audit trail)
    ├── File upload attempts
    ├── Rate limit hits
    ├── Alertas para: 10+ failed logins/min, nuevo device en admin, lockouts
    └── Retención: 90 días mínimo

5.2 Monitoring
    ├── Health endpoint: GET /api/v1/health (ya existe)
    ├── Uptime monitoring (UptimeRobot, BetterStack, o Cloudflare)
    ├── Error tracking (Sentry recomendado)
    ├── Alertas push al admin para incidentes
    └── Runbook de respuesta a incidentes
```

---

## 8. Lo que YA está bien

No todo es negativo. El backend tiene buenas bases:

| Aspecto | Estado | Detalle |
|---------|--------|---------|
| **Password hashing** | Argon2id | Parámetros fuertes (64MB, 4 iter) |
| **SQL injection** | Protegido | Eloquent + prepared statements en raw queries |
| **Rate limiting básico** | Implementado | 5/min login, 60/min API, 10/min uploads |
| **File uploads** | Validado | MIME check, 10MB max, UUID filenames |
| **Token storage (app)** | SecureStore | No AsyncStorage ni MMKV para tokens |
| **API versioning** | v1 prefix | Estructura limpia |
| **Internal endpoints** | Secret-based auth | X-Internal-Secret header |
| **Signed URLs** | hash_equals() | Email verification seguro |
| **CSRF** | Habilitado | Excepto internal endpoints (correcto) |
| **Redis DB separation** | DB 2 para socket | No colisiona con cache/queues |
| **Git security** | .env ignorado | .gitignore correcto |
| **Console logging (app)** | Limpio | No hay console.log en producción |
| **Dependencies (socket)** | 0 vulnerabilidades | npm audit clean |
| **Job payloads** | Safe | SerializesModels, sin secrets |

---

## 9. Checklist pre-deploy

```
CRITICO — Sin esto NO se va a producción:
[ ] Socket room authorization implementado
[ ] HTMLPurifier activado en todos los modelos con HTML
[ ] Token expiration configurado (7 días)
[ ] APP_DEBUG=false
[ ] Secrets NO son placeholders
[ ] MySQL con password + user dedicado
[ ] Redis con password
[ ] Firewall configurado (solo 80/443/22)
[ ] SSH key-only
[ ] SSL mode Full (Strict) en Cloudflare
[ ] HTTPS enforced (redirect HTTP→HTTPS)
[ ] Non-root containers en Docker
[ ] Backups funcionando

ALTO — Fix antes de usuarios reales:
[ ] Security headers middleware activo
[ ] CORS restringido a dominios de producción
[ ] Certificate pinning en app
[ ] CAPTCHA en registro web
[ ] Rate limiting en socket → Redis
[ ] Fail2ban configurado
[ ] Error tracking (Sentry) activo
[ ] Monitoring/uptime configurado

MEDIO — Fix en primer sprint post-launch:
[ ] 2FA implementado
[ ] Device fingerprinting
[ ] Account lockout
[ ] FormRequests en todos los endpoints
[ ] Session secure cookie
[ ] Android code obfuscation
[ ] Audit logging completo
```

---

## 10. Dependencias nuevas estimadas

| Paquete | Componente | Para qué |
|---------|-----------|----------|
| `laravel/fortify` o custom | Backend | 2FA, password rules, device tracking |
| `react-native-ssl-pinning` | App | Certificate pinning |
| `sentry/sentry-laravel` | Backend | Error tracking |
| `@sentry/react-native` | App | Error tracking app |
| `zod` | Socket | Schema validation endpoints internos |

---

*Documento vivo — se actualiza conforme avanza la implementación.*
*Próxima revisión de seguridad recomendada: pre-deploy y cada 3 meses.*
