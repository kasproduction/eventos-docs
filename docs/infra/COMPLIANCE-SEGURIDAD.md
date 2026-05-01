# EventOS — Documento de Compliance de Seguridad

> Controles técnicos implementados, alineación con OWASP Top 10,
> estándares de desarrollo seguro y cumplimiento normativo.
> Versión: 1.0 | Fecha: 2026-04-07

---

## 1. Resumen Ejecutivo

EventOS es una plataforma de gestión de eventos compuesta por tres componentes:
- **API Backend** — Laravel 11 + PHP 8.3 + MySQL 8 + Redis 7
- **App Móvil** — React Native (Expo SDK 55) para iOS y Android
- **Servidor Real-time** — Node.js + Socket.IO 4.8 + Redis adapter

La plataforma implementa controles de seguridad alineados con **OWASP Top 10 (2021)**, **CWE Top 25**, y buenas prácticas de **ISO/IEC 27001** en los pilares de confidencialidad, integridad y disponibilidad.

Se cuenta con **300 tests automatizados** (762 assertions), de los cuales **42 son específicos de seguridad** que validan controles de acceso, sanitización, tokens, lockout, headers y configuración. Se incluye un sistema de **sincronización reactiva en tiempo real** con 5 capas de redundancia y protección anti-colapso para 10,000+ usuarios concurrentes.

---

## 2. Arquitectura de Seguridad

```
                    ┌──────────────┐
                    │  Cloudflare  │
                    │  WAF + DDoS  │
                    │  TLS 1.2+   │
                    └──────┬───────┘
                           │ HTTPS only
                    ┌──────┴───────┐
                    │    Nginx     │
                    │  Reverse     │
                    │  Proxy       │
                    └──┬───────┬───┘
                       │       │
              ┌────────┴──┐ ┌──┴────────┐
              │  Laravel   │ │ Socket.IO │
              │  API       │ │ Server    │
              │  (PHP-FPM) │ │ (Node.js) │
              └────┬───────┘ └────┬──────┘
                   │              │
              ┌────┴──────────────┴────┐
              │         Redis          │
              │  Sessions | Cache |    │
              │  Queues | Pub/Sub      │
              └────────────┬───────────┘
                           │
              ┌────────────┴───────────┐
              │        MySQL 8         │
              │   Datos persistentes   │
              └────────────────────────┘
```

### Separación de redes (producción)

| Red | Componentes | Acceso externo |
|-----|-------------|---------------|
| Frontend | Nginx (puertos 80/443) | Sí — único punto de entrada |
| Backend (internal) | Laravel, Socket.IO, Redis, MySQL | No — solo vía Nginx |

---

## 3. Alineación OWASP Top 10 (2021)

### A01 — Broken Access Control

| Control | Implementación | Evidencia |
|---------|---------------|-----------|
| Autenticación por token | Laravel Sanctum (Bearer tokens SHA-256) | `config/sanctum.php` |
| Expiración de tokens | 7 días, revocación en refresh | SEC-1.3: `TokenExpirationTest.php` (7 tests) |
| Autorización por roles | 4 roles: presencial, virtual, vendedor, admin | `Attendee.role`, middleware `AuthenticateApi` |
| Room authorization (WebSocket) | Validación server-side antes de join a rooms | SEC-1.1: `RoomAuthTest.php` (6 tests) |
| CORS restringido | Origins explícitos, methods/headers limitados | `config/cors.php`, socket CORS fail-closed |
| CSRF protection | Habilitado por defecto en Laravel | Excepto endpoints internos (justificado) |

### A02 — Cryptographic Failures

| Control | Implementación | Detalle |
|---------|---------------|---------|
| Hashing de contraseñas | Argon2id | 64MB memory, 4 iteraciones, verificación habilitada |
| Cifrado en tránsito | HTTPS obligatorio (TLS 1.2+) | Cloudflare Full Strict + app enforcement (SEC-2.3) |
| Cifrado en reposo (app) | AES-256-CBC (Laravel) | `config/app.php` cipher, APP_KEY rotación soportada |
| Almacenamiento seguro (móvil) | expo-secure-store | Tokens NUNCA en AsyncStorage ni MMKV |
| Integridad QR codes | HMAC-SHA256 | `APP_QR_SECRET` con validación server-side |
| Signed URLs | hash_equals() | Verificación de email, timing-attack safe |

### A03 — Injection

| Control | Implementación | Detalle |
|---------|---------------|---------|
| SQL Injection | Eloquent ORM + prepared statements | Auditoría: 0 inyecciones encontradas en raw queries |
| XSS (Cross-Site Scripting) | HTMLPurifier trait | SEC-1.2: 8 modelos protegidos, 13 tests XSS |
| XSS — Plain text models | strip_tags() automático | WallPost, ChatMessage, SessionQuestion, etc. |
| XSS — Rich HTML models | HTMLPurifier whitelist | CustomPage: solo tags seguros (p, strong, a, img...) |
| Content Security Policy | CSP header en API | `default-src 'none'; frame-ancestors 'none'` |

### A04 — Insecure Design

| Control | Implementación | Detalle |
|---------|---------------|---------|
| Rate limiting | 5/min login, 60/min API, 10/min uploads | `AppServiceProvider.php` throttle config |
| Account lockout | 5 intentos → 30 min bloqueo | SEC-3.3: `AccountLockoutTest.php` (6 tests) |
| Socket rate limiting | Redis-backed (INCR/EXPIRE) | chat 1/2s, emoji 3/2s, join 10/60s |
| Max connections | 5 conexiones simultáneas por usuario | Socket server enforcement |
| Token refresh | Revoke + new (no extender) | SEC-1.3: eliminación de token viejo garantizada |
| Fail-closed | Acceso denegado si backend no responde | Socket room validation, CORS fallback=false |

### A05 — Security Misconfiguration

| Control | Implementación | Detalle |
|---------|---------------|---------|
| Security headers | Middleware global | 7 headers: X-Frame, X-Content-Type, CSP, HSTS, Referrer-Policy, Permissions-Policy, X-Permitted-Cross-Domain-Policies |
| Pre-deploy validation | `php artisan security:check` | Bloquea CI/CD si: debug=true, placeholders, DB sin password, tokens sin expiración |
| Production template | `.env.production.example` | Todos los valores con instrucciones GENERAR/CAMBIAR |
| Debug deshabilitado | APP_DEBUG=false enforced | `security:check` falla si debug=true |

### A06 — Vulnerable and Outdated Components

| Control | Implementación | Detalle |
|---------|---------------|---------|
| Backend dependencies | `composer audit` | Ejecutar en CI/CD antes de deploy |
| Socket dependencies | `npm audit` | 0 vulnerabilidades al 2026-04-07 |
| App dependencies | Expo SDK 55 (current) | Todas las dependencias actualizadas |
| Image versions (Docker) | Pinned versions | nginx:1.27, php:8.3, node:20, redis:7.4, mysql:8.4 |

### A07 — Identification and Authentication Failures

| Control | Implementación | Detalle |
|---------|---------------|---------|
| Password hashing | Argon2id (no bcrypt) | Resistente a GPU/ASIC |
| Minimum password | Validación en RegisterRequest | min:8 + confirmation |
| Account lockout | 5 intentos → 30 min | HTTP 423 + reset on success |
| Token expiration | 7 días | Sanctum `expiration` config |
| Auto-refresh | App interceptor | 401 → refresh → retry (deduplicado) |
| No info leak | Misma respuesta para user no existe vs password incorrecto | "Las credenciales son incorrectas" |
| 2FA (planificado) | OTP 6 dígitos email/WhatsApp | SEC-3.1 — diseño listo, implementación pendiente |

### A08 — Software and Data Integrity Failures

| Control | Implementación | Detalle |
|---------|---------------|---------|
| CSRF protection | Laravel default | Token validation en forms stateful |
| QR integrity | HMAC-SHA256 | Firmado con secret server-side |
| Email verification | Signed URLs con hash_equals | Timing-attack safe |
| Internal endpoints | X-Internal-Secret header | Socket↔Backend communication |
| Data invalidation endpoint | X-Internal-Secret header | POST /internal/data/invalidate — mismo patron de seguridad |
| Input sanitization | FormRequests + HTMLPurifier | Validación antes de persistir |

### A09 — Security Logging and Monitoring Failures

| Control | Implementación | Estado |
|---------|---------------|--------|
| Failed login logging | Log en Laravel | Implementado (channel stack) |
| Security event logging | SecurityLogger dedicado | Planificado SEC-5.1 (canal daily, 90 días retención) |
| Error tracking | Sentry | Planificado SEC-5.2 (backend + app) |
| Uptime monitoring | BetterStack/UptimeRobot | Planificado SEC-5.3 |
| Audit trail admin | Filament activity log | Parcial — bans, role changes registrados |

### A10 — Server-Side Request Forgery (SSRF)

| Control | Implementación | Detalle |
|---------|---------------|---------|
| No user-controlled URLs | Internal endpoints usan secret, no URLs del user | Socket→Laravel siempre a URL fija configurada |
| File upload validation | MIME type + extension + size limit | No se permiten URLs como source de upload |
| R2/Storage | URLs generadas server-side | Usuarios no controlan paths de storage |

---

## 4. Protocolos y Certificados Digitales

### Cifrado en tránsito

| Aspecto | Configuración |
|---------|--------------|
| Protocolo | TLS 1.2 mínimo (configurado en Cloudflare) |
| SSL Mode | Full (Strict) — valida certificado del origin |
| HSTS | `max-age=31536000; includeSubDomains` (middleware + Cloudflare) |
| Certificate Authority | Cloudflare (DigiCert/Google Trust Services) |
| Key length | 2048 bits mínimo (estándar Cloudflare) |
| Algoritmo de integridad | SHA-256 |
| Renovación | Automática (Cloudflare managed) |

### Cifrado en reposo

| Dato | Método |
|------|--------|
| Contraseñas | Argon2id hash (irreversible, 64MB memory cost) |
| Sesiones | AES-256-CBC (Laravel encryption) |
| Cache | Redis (en memoria, protegido por password en prod) |
| Base de datos | MySQL TDE o disk encryption (depende del hosting) |
| Tokens en móvil | expo-secure-store (Keychain iOS / Keystore Android) |

### Almacenamiento de certificados

| Certificado | Ubicación | Protección |
|-------------|-----------|-----------|
| SSL/TLS (edge) | Cloudflare (managed) | Cloudflare infraestructura |
| Origin cert | Server `/etc/ssl/` | Permisos 600, owned by root |
| Android keystore | EAS Build (managed) | Passphrase en EAS Secrets |
| iOS signing | Apple Developer Portal | 2FA en Apple account |

---

## 5. Cabeceras de Seguridad HTTP

Implementadas en `app/Http/Middleware/SecurityHeaders.php`, registrado globalmente.

| Header | Valor | Propósito |
|--------|-------|-----------|
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` | Forzar HTTPS, prevenir downgrade (solo prod/staging) |
| `X-Frame-Options` | `DENY` | Prevenir clickjacking |
| `X-Content-Type-Options` | `nosniff` | Prevenir MIME type sniffing |
| `Content-Security-Policy` | `default-src 'none'; frame-ancestors 'none'` | Prevenir XSS e inclusión de recursos (solo API routes) |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Limitar información en referrer |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=()` | Restringir APIs del browser |
| `X-Permitted-Cross-Domain-Policies` | `none` | Prevenir políticas cross-domain de Flash/PDF |

### Métodos HTTP permitidos

Solo los necesarios para la operación:
- `GET` — lectura de recursos
- `POST` — creación de recursos, login, acciones
- `PUT` / `PATCH` — actualización de recursos
- `DELETE` — eliminación de recursos
- `OPTIONS` — preflight CORS

---

## 6. Validación de Entrada

### Estrategia de validación en capas

```
[Cliente]  →  [Cloudflare WAF]  →  [Laravel Middleware]  →  [FormRequest]  →  [Model Sanitization]  →  [DB]
                                          │                       │                    │
                                     Rate Limit              Reglas de          HTMLPurifier /
                                     Auth Token              validación         strip_tags
```

### FormRequests implementados

| FormRequest | Campos validados | Controller |
|-------------|-----------------|------------|
| LoginRequest | email, password | AuthController |
| RegisterRequest | nombre, email, password, campos dinámicos del evento | AuthController |
| ActivateRequest | token, password | AuthController |
| ForgotPasswordRequest | email | AuthController |
| ResetPasswordRequest | token, email, password | AuthController |
| StoreWallPostRequest | body (max:1000), photo (image, max:10MB) | WallController |
| StoreWallCommentRequest | body (max:500) | WallController |
| StoreQuestionRequest | body (min:5, max:500), is_anonymous | QuestionController |
| StoreEventPhotoRequest | photo (image, max:10MB), caption (max:200) | EventPhotoController |
| StoreSessionRatingRequest | rating (1-5), comment (max:1000) | RatingController |

### Sanitización automática (Model-level)

| Modelo | Campo | Método |
|--------|-------|--------|
| WallPost | body | strip_tags (plain text) |
| WallComment | body | strip_tags |
| SessionQuestion | body | strip_tags |
| ChatMessage | body | strip_tags |
| SessionRating | comment | strip_tags |
| Lead | note | strip_tags |
| Announcement | body | strip_tags |
| CustomPage | body | HTMLPurifier (whitelist: p, strong, em, a, ul, ol, li, h2-h4, img, table) |

---

## 7. Control de Acceso y Autenticación

### Flujo de autenticación

```
[Login] → Email + Password
    │
    ├── Credenciales inválidas → "Credenciales incorrectas" (sin info leak)
    │     └── Incrementar failed_attempts
    │           └── >= 5 intentos → Lock 30 min (HTTP 423)
    │
    ├── Cuenta bloqueada → HTTP 423 + locked_until
    │
    └── Credenciales válidas
          ├── Reset failed_attempts = 0
          ├── Crear token Sanctum (7 días expiración)
          └── Responder con token + datos usuario/attendee
```

### Roles y permisos

| Rol | Acceso |
|-----|--------|
| presencial | App completa: agenda, chat, Q&A, networking, social wall, gamification |
| virtual | Igual que presencial + streaming |
| vendedor | Lead capture, stand management, directorio sponsors |
| admin | Filament panel: gestión evento, moderación, analytics |
| superadmin | Multi-organización, todas las configuraciones |

### Protección de sesiones WebSocket

| Evento | Validación |
|--------|-----------|
| Conexión | Token Sanctum validado via Laravel /auth/me |
| join:event | eventId === user.eventId (sin HTTP call) |
| join:session | Validación interna + cache Redis 5min |
| chat:send | Verificar membresía en room + rate limit |
| chat:emoji | Verificar membresía en room + rate limit |

---

## 8. Logs de Seguridad y Trazabilidad

### Eventos registrados actualmente

| Evento | Datos capturados | Canal |
|--------|-----------------|-------|
| Login exitoso | user_id, IP, user-agent | Laravel log (stack) |
| Login fallido | email intentado, IP, user-agent | Laravel log (stack) |
| Account lockout | user_id, failed_attempts, locked_until | Laravel log (stack) |
| Socket room denied | user_id, event_id, reason | Console (socket server) |
| File upload | user_id, filename, size, mime | Laravel log |

### Planificado (SEC-5.1)

Canal de logging dedicado `security` con formato JSON structured:
- Retención: 90 días mínimo
- Formato: `{timestamp, event, user_id, ip, user_agent, details}`
- Alertas: 10+ failed logins/min, nuevo dispositivo admin, lockouts masivos

---

## 9. Controles ISO 27001 — CIA Triad

### 9.1 Confidencialidad

| Control | Implementación |
|---------|---------------|
| Autenticación fuerte | Argon2id + tokens Sanctum con expiración |
| Control de acceso basado en roles | 5 roles con permisos diferenciados |
| Cifrado en tránsito | TLS 1.2+ obligatorio (Cloudflare Full Strict) |
| Cifrado en reposo | AES-256-CBC (sessions/cache), Argon2id (passwords) |
| Almacenamiento seguro en dispositivos | expo-secure-store (Keychain/Keystore nativo) |
| Sanitización de datos | HTMLPurifier + strip_tags automático |
| Separación de redes | Docker networks: frontend (público) + backend (internal) |
| Secrets management | .env excluido de git, .env.production.example como template, security:check bloquea deploy |

### 9.2 Integridad

| Control | Implementación |
|---------|---------------|
| Validación de entrada | 10 FormRequests + inline validation + sanitización en modelo |
| Protección CSRF | Laravel default (token validation) |
| Integridad de QR | HMAC-SHA256 firmado server-side |
| URLs firmadas | hash_equals() para verificación de email |
| Versionamiento de API | Prefijo /api/v1/ para control de cambios |
| Tests automatizados | 300 tests (762 assertions) ejecutados en cada build |
| Pre-deploy checks | `php artisan security:check` bloquea deploy con config insegura |

### 9.3 Disponibilidad

| Control | Implementación |
|---------|---------------|
| Health endpoint | GET /api/v1/health (monitoreo externo) |
| Rate limiting | Protección contra abuso: login 5/min, API 60/min, upload 10/min |
| DDoS protection | Cloudflare L3/L4/L7 (automático) |
| Socket max connections | 5 por usuario (previene resource exhaustion) |
| Docker health checks | Restart automático de containers caídos |
| Redis failover | Retry strategy con backoff exponencial |
| Backups | Planificado: daily encrypted, retención 30 días, test restore mensual |
| Uptime monitoring | Planificado: BetterStack/UptimeRobot con alertas |
| Real-time sync | 5 capas: socket + focusManager + reconnect + push + staleTime |
| Anti-thundering herd | Jitter 0-2s en cliente + throttle 1s en backend |
| Warm cache | Cache::forget antes de broadcast (dato fresco en refetch) |

### 9.4 Integridad de datos en actividades en vivo

Para features interactivos (sorteos, ruletas, juegos) que involucran premios o puntos:

| Principio | Implementación |
|-----------|---------------|
| **MySQL primero, socket después** | Resultado critico (ganador, puntos) se persiste en DB antes de emitir por socket |
| **Fallback multi-canal** | Si socket falla: push notification + social wall auto-post cubren |
| **Participación via HTTP** | Boton "Participar" es HTTP POST, no depende del socket |
| **Redis para datos volatiles** | Conectados en room, inputs de juego — datos que se pueden reconstruir |
| **Idempotencia** | Operaciones criticas (redencion, sorteo) son idempotentes — no se duplican premios |

Regla: si el socket muere en el peor momento, ningun dato critico se pierde y el usuario recibe la informacion por otro canal en <5 segundos.

---

## 10. Gestión de Vulnerabilidades

### Proceso de escaneo

| Tipo | Herramienta | Frecuencia | Responsable |
|------|------------|-----------|-------------|
| Dependencias PHP | `composer audit` | Cada deploy (CI/CD) | Automatizado |
| Dependencias Node.js | `npm audit` | Cada deploy (CI/CD) | Automatizado |
| Config de seguridad | `php artisan security:check` | Cada deploy (CI/CD) | Automatizado |
| Headers de seguridad | securityheaders.com | Post-deploy + mensual | Manual |
| Escaneo de vulnerabilidades | OWASP ZAP (automatizado) | Pre-release | Automatizado/Manual |
| Pentesting | Tercero certificado | Anual | Externo |

### Proceso de remediación

```
Hallazgo → Clasificación (Crítico/Alto/Medio/Bajo)
    │
    ├── Crítico/Alto → Fix inmediato, re-test, hotfix release
    │
    ├── Medio → Fix en siguiente sprint, re-test
    │
    └── Bajo → Backlog, fix en próximo release cycle
```

**Política:** No se autoriza salida a producción con hallazgos críticos o altos pendientes. Se ejecuta re-test post-remediación para evidenciar cierre.

---

## 11. Cumplimiento Normativo

### Ley 1581 de 2012 (Habeas Data — Colombia)

| Requisito | Implementación |
|-----------|---------------|
| Consentimiento informado | Checkbox obligatorio en registro con texto legal configurable |
| consent_accepted_at | Timestamp almacenado por usuario |
| Derecho de acceso | API GET /auth/me retorna todos los datos del usuario |
| Derecho de rectificación | API para editar perfil + campos de registro editables |
| Registro del consentimiento | consent_logs table con IP, user-agent, timestamp |

### GDPR (si aplica a usuarios europeos)

| Requisito | Implementación |
|-----------|---------------|
| Consentimiento explícito | Mismo mecanismo Ley 1581 |
| Data export | Export de attendees en CSV/Excel desde admin |
| Right to erasure | Planificado: endpoint DELETE /auth/account |
| Data minimization | Progressive profiling — solo datos mínimos en registro |
| Breach notification | Planificado: SecurityLogger con alertas de anomalías |

---

## 12. Inventario de Tests de Seguridad

| Test File | Tests | Assertions | Qué valida |
|-----------|-------|-----------|------------|
| `RoomAuthTest.php` | 6 | 9 | Socket room authorization, internal endpoint, access denied |
| `SanitizationTest.php` | 13 | 19 | XSS prevention en 8 modelos, script tags, event handlers, javascript: |
| `TokenExpirationTest.php` | 7 | 17 | Token expiration, refresh, revocation, 401 on expired |
| `SecurityHeadersTest.php` | 7 | 13 | 7 security headers presentes, HSTS solo en prod, CSP en API |
| `SecurityCheckCommandTest.php` | 4 | 4 | Pre-deploy validation: debug, placeholders, DB password |
| `AccountLockoutTest.php` | 6 | 20 | Lockout en 5 intentos, 423, reset on success, expiration |
| **Total** | **43** | **82** | |

Además, los 300 tests totales del backend (762 assertions) validan funcionalidad que incluye controles de acceso, validación de datos, flujos de autenticación, sponsor contact forms y view tracking.

---

## 13. Diagrama de Infraestructura

```
┌─────────────────────────────────────────────────────────────────┐
│                         INTERNET                                 │
└────────────────────────────┬────────────────────────────────────┘
                             │
                    ┌────────┴────────┐
                    │   CLOUDFLARE    │
                    │  ─────────────  │
                    │  WAF (OWASP)   │
                    │  DDoS L3-L7    │
                    │  TLS 1.2+      │
                    │  Rate Limiting  │
                    │  Bot Protection │
                    │  HSTS + Preload │
                    └────────┬────────┘
                             │ HTTPS (Full Strict)
                    ┌────────┴────────┐
                    │      VPS        │
                    │   (Hetzner /    │
                    │   DigitalOcean) │
                    │                 │
                    │  UFW Firewall   │
                    │  22/80/443 only │
                    │  Fail2ban       │
                    │  SSH key-only   │
                    └────────┬────────┘
                             │
                    ┌────────┴────────┐
                    │     Docker      │
                    │   Compose       │
                    ├─────────────────┤
                    │                 │
      ┌─────────┐  │  ┌───────────┐  │  ┌───────────┐
      │  Nginx  │──│──│  Laravel   │──│──│ Socket.IO │
      │  :80/443│  │  │  PHP-FPM  │  │  │  :3001    │
      │ (public)│  │  │ (internal)│  │  │ (internal)│
      └─────────┘  │  └─────┬─────┘  │  └─────┬─────┘
                   │        │        │        │
                   │  ┌─────┴────────┴────────┴──┐
                   │  │         Redis 7           │
                   │  │  Sessions | Cache | Queue │
                   │  │  Pub/Sub (Socket adapter) │
                   │  │     (internal network)    │
                   │  │   Password protected      │
                   │  └───────────────────────────┘
                   │        │
                   │  ┌─────┴─────────────────────┐
                   │  │        MySQL 8.4           │
                   │  │   Dedicated user (no root) │
                   │  │     (internal network)     │
                   │  │   Password protected       │
                   │  │   Daily encrypted backups  │
                   │  └───────────────────────────┘
                   │
                   │  ┌───────────────────────────┐
                   │  │    Cloudflare R2           │
                   │  │  Object storage (images)   │
                   │  │  CDN distribution           │
                   │  └───────────────────────────┘
                   └─────────────────┘

MOBILE APPS (distribución separada):
┌──────────────┐    ┌──────────────┐
│  Google Play  │    │  App Store   │
│  (Android)    │    │  (iOS)       │
│  EAS Build    │    │  EAS Build   │
└──────────────┘    └──────────────┘
```

---

## 14. Plan de Mejora Continua

| Acción | Frecuencia | Responsable |
|--------|-----------|-------------|
| `composer audit` + `npm audit` | Cada deploy | CI/CD automatizado |
| `php artisan security:check` | Cada deploy | CI/CD automatizado |
| Actualización de dependencias | Mensual | Desarrollo |
| Revisión de security headers | Post-deploy | securityheaders.com |
| Escaneo OWASP ZAP | Pre-release | Desarrollo |
| Pentesting externo | Anual | Tercero certificado |
| Revisión de logs de seguridad | Semanal | Operaciones |
| Test de restore de backups | Mensual | Operaciones |
| Revisión de accesos y roles | Trimestral | Administración |
| Actualización de este documento | Por release | Desarrollo |

---

## 15. Contacto y Responsables

| Rol | Responsabilidad |
|-----|----------------|
| Desarrollo | Implementación de controles, tests automatizados, fixes |
| Operaciones | Monitoreo, backups, respuesta a incidentes |
| Seguridad (externo) | Pentesting anual, auditoría de hallazgos |

---

_EventOS Compliance de Seguridad v1.1_
_Documento generado: 2026-04-07 | Actualizado: 2026-04-09_
_Actualización: real-time invalidation, 300 tests, integridad datos en vivo_
_Próxima revisión: pre-deploy a producción_
