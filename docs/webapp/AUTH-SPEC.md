# Web App — Auth Spec

> Especificacion completa de autenticacion. Bearer token Sanctum + magic link como flujo principal + email/password fallback + refresh + logout multi-device + session timeout.
>
> Backend ya tiene Sanctum. Endpoints existentes se reutilizan. Cualquier endpoint nuevo se documenta abajo y entra como bloqueante de W.1.

---

## Flujos de login

### Flujo 1 — Magic link (principal)

```
1. Usuario ingresa email en /login
2. POST /api/v1/auth/magic-link { email }
3. Backend genera token (un solo uso, 15min TTL)
4. Backend envia email con link: app.eventos.app/auth/verify?token=XXX
5. Usuario clickea link en su email
6. GET /auth/verify?token=XXX → frontend POST /api/v1/auth/verify-magic-link
7. Backend valida token, marca como usado, devuelve Bearer token Sanctum
8. Frontend guarda token en httpOnly cookie via API route Next
9. Redirect a /home
```

**Detalles tecnicos**:
- Token magic link: 32 chars hex aleatorios, TTL 15min
- Tabla `magic_link_tokens`: `id, user_id, token_hash, expires_at, used_at, created_at, ip_address, user_agent`
- Token se hashea en DB (no se guarda raw)
- Rate limit: 3 magic links por email por hora
- Si email no existe: respuesta generica "Si tu email esta registrado, recibiras un link" (anti-enumeration)
- Si token usado o expirado: redirect a `/login?error=token_invalid`

**Endpoint nuevo backend**: `POST /api/v1/auth/magic-link` y `POST /api/v1/auth/verify-magic-link`. Bloqueante de W.1.

### Flujo 2 — Email + password (fallback)

```
1. Usuario ingresa email + password en /login
2. POST /api/v1/auth/login { email, password, device_name: 'web-XXX' }
3. Backend valida credenciales
4. Backend devuelve Bearer token Sanctum
5. Frontend guarda token en httpOnly cookie via API route Next
6. Redirect a /home
```

**Detalles tecnicos**:
- Endpoint existente backend (mismo que app movil)
- `device_name`: `web-{userAgent_hash}-{timestamp}` para identificar sesion
- Rate limit existente: 5 intentos por email por 15min
- Si credenciales mal: error generico "Credenciales invalidas"
- Si password no esta seteado (usuario solo ha usado magic link): muestra modal "Configura tu contrasena por primera vez" → flujo set-password

### Flujo 3 — Set password (primera vez o reset)

```
1. Usuario en /set-password (con token de magic link valido o reset)
2. Ingresa password + confirm
3. POST /api/v1/auth/set-password { token, password }
4. Backend valida token, hashea password, guarda
5. Devuelve Bearer token, login automatico
```

---

## Token storage

**Decision**: Bearer token Sanctum guardado en **httpOnly cookie via API route Next**.

```
Frontend: fetch('/api/auth/login', { ... })
         ↓
Next API route: forward POST /api/v1/auth/login a backend
         ↓
Backend devuelve { token: 'XXX' }
         ↓
Next API route: response.headers['Set-Cookie'] = 'auth=XXX; HttpOnly; Secure; SameSite=Lax; Path=/; Max-Age=86400'
         ↓
Frontend cliente NO ve el token (httpOnly), pero lo envia automaticamente en cada fetch
```

**Razones**:
- httpOnly previene XSS-extraction
- Secure obliga HTTPS
- SameSite=Lax previene CSRF basico
- Max-Age 24h (refresh refleja TTL backend)

**Para llamadas a backend desde cliente**:
- Todas las llamadas API del cliente pasan por Next API routes (`/api/proxy/*`)
- Next API route lee la cookie + adjunta `Authorization: Bearer XXX` al request al backend
- El cliente nunca ve el token raw

---

## Refresh token rotation

**Problema**: Sanctum no rota tokens por default. Bancolombia compliance va a pedir rotacion.

**Solucion**:
- Token Sanctum TTL: **24 horas activo** (`sanctum.expiration` en `config/sanctum.php`)
- Refresh: si el token tiene < 1 hora restante, se renueva automaticamente
- Refresh endpoint: `POST /api/v1/auth/refresh` (existente o se agrega) — usa el token actual + ip + user-agent para validar y emitir nuevo
- El nuevo token reemplaza el anterior en la cookie (Set-Cookie en respuesta)
- Si el token expiro: redirect a `/login?reason=expired`

**Endpoint nuevo backend si no existe**: `POST /api/v1/auth/refresh`. Bloqueante de W.1.

**Long-lived sessions**:
- "Recordarme" (checkbox en login): genera token con TTL 30 dias
- Sin "Recordarme": TTL 24h estricto
- En ambos casos rota cada vez que < 1h restante

---

## Session timeout

**Inactividad**:
- Frontend tracker: `useIdleTimer` con 30min de inactividad
- Al cumplir 30min: modal "Tu sesion va a expirar" con boton "Continuar" (renueva token) o "Cerrar sesion"
- 60min sin respuesta: logout forzado

**Bancolombia compliance**:
- 30min idle = warning
- 60min idle = logout
- Configurable por evento: `events.session_idle_minutes` (default 30) y `events.session_max_minutes` (default 480 = 8h)

---

## Logout

### Logout simple (sesion actual)

```
POST /api/v1/auth/logout
Headers: Authorization: Bearer XXX
```

Backend revoca el token actual. Frontend borra cookie.

### Logout multi-device (todas las sesiones)

```
POST /api/v1/auth/logout-all
Headers: Authorization: Bearer XXX
```

Backend revoca **todos** los tokens del usuario. Frontend borra cookie.

UI: en `/perfil/seguridad` boton "Cerrar sesion en todos los dispositivos".

### Logout por inactividad

Frontend detecta idle 60min → llama logout simple → redirect `/login?reason=timeout`.

---

## Listado de sesiones activas

UI en `/perfil/seguridad`:

```
[Computadora actual] — Chrome on Windows — Bogota — Activa ahora
[Otra sesion] — Safari on iPhone — Bogota — Hace 2 horas — [Cerrar]
[Otra sesion] — Edge on Windows — Medellin — Hace 1 dia — [Cerrar]

[Cerrar todas las otras sesiones]
```

Endpoint existente backend: `GET /api/v1/auth/sessions` (ya implementado para app movil).

---

## Rate limits

| Endpoint | Limite |
|---|---|
| `POST /api/v1/auth/magic-link` | 3 por email por hora |
| `POST /api/v1/auth/login` | 5 intentos por email por 15min |
| `POST /api/v1/auth/verify-magic-link` | 10 por IP por hora |
| `POST /api/v1/auth/refresh` | 60 por usuario por hora |
| Resto autenticado | 200 req/min por IP |

Backend ya tiene `RateLimiter` middleware. Frontend muestra mensaje generico si recibe 429.

---

## Email magic link — template

**Subject**: `Tu link de acceso a {event_name}`

**Body** (text + HTML):
```
Hola {name},

Para acceder a {event_name}, hace click en este link:

{magic_link_url}

Este link expira en 15 minutos y solo puede usarse una vez. Si no solicitaste este email, ignoralo.

— Equipo {event_name}
```

**Configuracion DKIM/SPF/DMARC** en `eventos.app` — bloqueante de produccion (no de dev).

**Spam protection**:
- Bancolombia + DKIM/SPF/DMARC validados antes de produccion
- Probar en Mailpit local + Litmus/Mail-Tester antes de deploy

---

## Onboarding webapp (despues del primer login)

Despues de la primera autenticacion exitosa:
1. Frontend lee `localStorage.getItem('onboarding_completed')`
2. Si null → muestra `<WelcomeTour />` overlay
3. Tour de 4-6 escenas (~30-60s) con cursor guiado, skippable
4. Al completar o skip: `localStorage.setItem('onboarding_completed', '1')`
5. Redirect a `/home`

Detalle visual en `W.1-setup-auth.md`. Conceptualmente inspirado en `design/showcase-onboarding-v6.html` (idea funcional, no estetica).

---

## Errores y mensajes

| Error | Mensaje usuario | Action |
|---|---|---|
| Email no registrado (magic link) | "Si tu email esta registrado, recibiras un link" | (no revelar) |
| Magic link expirado | "Este link expiro. Solicita uno nuevo." | Boton "Reenviar" |
| Magic link usado | "Este link ya fue usado." | Redirect login |
| Credenciales invalidas | "Credenciales invalidas." | (no especificar campo) |
| Rate limit excedido | "Demasiados intentos. Intenta de nuevo en {minutes} min." | Mostrar countdown |
| Token sesion expirado | "Tu sesion expiro. Inicia sesion de nuevo." | Redirect login |
| 2FA requerido (Fase futura) | "Ingresa el codigo de 6 digitos" | Modal OTP |

---

## Componentes Next.js — estructura

```
src/
  app/
    (auth)/
      login/page.tsx           // Form magic link + email/password
      verify/page.tsx          // Recibe ?token=XXX, valida
      set-password/page.tsx    // Setea password primera vez
      logout/page.tsx          // Cierra sesion (server action)
    api/
      auth/
        login/route.ts         // Proxy POST /api/v1/auth/login
        magic-link/route.ts    // Proxy POST /api/v1/auth/magic-link
        verify/route.ts        // Proxy POST /api/v1/auth/verify-magic-link
        refresh/route.ts       // Proxy POST /api/v1/auth/refresh
        logout/route.ts        // Proxy POST /api/v1/auth/logout
        sessions/route.ts      // Proxy GET /api/v1/auth/sessions
      proxy/
        [...path]/route.ts     // Proxy generico para resto de endpoints (adjunta Bearer)
  hooks/
    useAuth.ts                 // useUser, login, logout, isAuthenticated
    useIdleTimer.ts            // Detecta inactividad
  components/
    auth/
      LoginForm.tsx
      MagicLinkSentScreen.tsx
      WelcomeTour.tsx          // Mini tour onboarding
  middleware.ts                // Verifica cookie auth en rutas protegidas
```

---

## Middleware Next.js

`src/middleware.ts` se ejecuta en TODAS las rutas. Logica:

1. Si ruta = `/login` o `/auth/*`: permitir sin auth
2. Si ruta = `/api/auth/*`: permitir sin auth (ahi se hace login)
3. Si ruta = otra: leer cookie `auth`
   - Si no hay cookie → redirect `/login?next=...`
   - Si hay cookie → permitir (validacion final ocurre en cada API call al backend)

NO valida el token en cada request del middleware (lento). Validacion real ocurre en backend en cada API call.

---

## Tests

### Unit (Vitest)

- `useAuth` hook — login, logout, refresh logic
- `useIdleTimer` — triggers correctos
- API proxy routes — adjuntan Bearer correctamente

### E2E (Playwright)

- **Happy path magic link**: login con email → recibir email (Mailpit) → click link → llegar a /home
- **Edge case**: token expirado → mensaje correcto + boton reenviar
- **Edge case**: rate limit → mensaje + countdown
- **Logout multi-device**: 2 sesiones → logout-all en una → la otra recibe 401 al siguiente request

---

## Pendientes / Fase futura

- 2FA OTP por email (SEC-3.1) — Fase 2
- 2FA TOTP (Google Authenticator) — Fase 2
- SSO Google/Microsoft — Fase 2
- Device fingerprinting + alerta login nuevo — SEC-3.2 Fase 2
- Anomaly detection (login desde geo nueva) — Fase 2

---

## Endpoints — resumen

| Endpoint | Metodo | Existente backend? | Notas |
|---|---|---|---|
| `/api/v1/auth/magic-link` | POST | **Nuevo (bloqueante W.1)** | Genera token, envia email |
| `/api/v1/auth/verify-magic-link` | POST | **Nuevo (bloqueante W.1)** | Valida token, devuelve Bearer |
| `/api/v1/auth/login` | POST | Si | Email + password |
| `/api/v1/auth/refresh` | POST | **Verificar (puede ser nuevo)** | Rota Bearer si < 1h restante |
| `/api/v1/auth/logout` | POST | Si | Revoca token actual |
| `/api/v1/auth/logout-all` | POST | Si | Revoca todos los tokens |
| `/api/v1/auth/sessions` | GET | Si | Lista sesiones activas |
| `/api/v1/auth/set-password` | POST | Verificar | Setea password primera vez o reset |

**Backend bloqueantes de W.1**: 2-3 endpoints nuevos. Estimacion: ~3-4h backend (incluye tests Pest). Se hace en sesion separada o como subseccion de W.1.
