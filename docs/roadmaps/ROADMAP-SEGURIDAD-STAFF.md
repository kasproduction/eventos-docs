# ROADMAP — SEGURIDAD DEL STAFF (2FA + sesiones + accesos) — 0/26

> **Decision Kamilo 2026-07-20**: "prefiero tener todo lo de seguridad en regla
> y no esperar a tener cliente encima con presion". Se hace AHORA, con calma,
> sin deal de por medio. Ventana operativa de este frente.
>
> **DESBLOQUEO CLAVE**: SEC-3.1 estaba aplazado desde 2026-04-07 porque el
> diseño dependia de **WhatsApp Business API** (OTP por email/WhatsApp).
> Kamilo pidio **app autenticadora (TOTP)** — eso elimina la dependencia
> externa por completo: sin WhatsApp, sin SMS, sin depender de que el correo
> llegue. Es codigo + un QR. El diseño viejo de `FASE-SEGURIDAD.md` §SEC-3.1
> (tabla `otp_codes`, config en `events`, endpoints en la API de asistentes)
> queda **SUPERSEDED** por este roadmap.

## Decisiones cerradas (Kamilo 2026-07-20 — no re-preguntar)

1. **Alcance: SOLO staff del admin** (los de `Roles::PANEL_ACCESS`). El
   asistente sigue entrando por magic link — ya es sin contraseña, y exigirle
   una app autenticadora para ver la agenda mataria la adopcion del evento.
   El vendedor jamas entra al admin.
2. **Obligatorio para TODO el staff** (mas estricto que la propuesta inicial;
   decision consciente pensando en vender a un enterprise). Consecuencia
   aceptada: al desplegar, cada persona del staff —**Kamilo incluido**— cae en
   una pantalla de configuracion que no se puede saltar en su siguiente
   ingreso. NO es un bloqueo: es un setup forzado.
3. **Entra tambien**: sesiones activas · confiar en dispositivo 30 dias ·
   registro de accesos · endurecimiento de produccion (este ultimo con DEPLOY).

## Terreno verificado (2026-07-20)

- Filament **3.2** · Laravel **11.31** · Sanctum 4.3 · Spatie Permission 6.25.
- **`bacon/bacon-qr-code` ^3.1 YA instalado** (quedo de los kioskos INT.12) —
  el QR de activacion no necesita dependencia nueva.
- Panel admin: `->login()` default + `->authGuard('web')`; `User::canAccessPanel`
  filtra por `Roles::PANEL_ACCESS`.
- **`SESSION_DRIVER=redis`** → Redis NO permite enumerar sesiones por usuario.
  Por eso S.5 necesita tabla de seguimiento propia (no es capricho).
- Ya hecho de SEC-3 (no rehacer): **lockout** por intentos fallidos
  (`locked_until`, `failed_login_attempts` en users, 6 tests) · socket rate
  limiting Redis · 5 FormRequests. Seguridad global ~90%.
- **UNICA dep nueva**: `pragmarx/google2fa` (PHP puro, sin extensiones).
  OJO Windows: instalar con `--ignore-platform-req=ext-pcntl
  --ignore-platform-req=ext-intl --ignore-platform-req=ext-posix`.

---

## S.0 — Fundacion TOTP — 0/3

- [ ] S.0.1 Dependencia `pragmarx/google2fa` instalada
- [ ] S.0.2 Migracion users: `two_factor_secret` (cifrado),
      `two_factor_confirmed_at`, `two_factor_recovery_codes` (cifrado, json)
- [ ] S.0.3 `TwoFactorService`: generar secreto · URI `otpauth://` para el QR ·
      verificar codigo **con ventana de tolerancia** (los relojes de los
      telefonos se desfasan; sin esto el organizador jura que el codigo esta
      bien y el sistema le dice que no) · generar/consumir codigos de recuperacion

## S.1 — Activacion del segundo factor — 0/3

- [ ] S.1.1 Pagina de activacion (lenguaje Lumina, espejo del login ya
      tematizado): QR grande + **el secreto tambien en texto** (si la camara
      falla, se escribe a mano) + campo de verificacion para confirmar
- [ ] S.1.2 **8 codigos de recuperacion** mostrados UNA sola vez al confirmar,
      con opcion de descargar/copiar y advertencia honesta de guardarlos
- [ ] S.1.3 Regenerar codigos de recuperacion (invalida los anteriores)

## S.2 — Reto y enforcement — 0/3

- [ ] S.2.1 Pagina de reto post-contraseña: 6 digitos
- [ ] S.2.2 El reto acepta tambien un codigo de recuperacion (se consume)
- [ ] S.2.3 Middleware del panel: sin 2FA confirmado → **setup forzado** (no
      lockout, pantalla que no se salta) · con 2FA pero sesion sin verificar →
      reto. Cubrir tambien el arranque de todo el staff existente al desplegar

## S.3 — Recuperacion / rescate (CRITICO por la obligatoriedad) — 0/3

> Si el 2FA es obligatorio y alguien pierde el telefono el dia del montaje, NO
> puede quedarse afuera del admin. Sin estas 3 salidas la obligatoriedad es un
> riesgo operativo, no una mejora de seguridad.

- [ ] S.3.1 Un **super_admin resetea el 2FA de otra persona** desde Staff y
      permisos — accion auditada + correo de aviso al afectado
- [ ] S.3.2 Guarda: el **ultimo super_admin no puede quedar encerrado afuera**
      (espejo del guard anti auto-borrado que ya existe en Staff)
- [ ] S.3.3 Columna/estado "segundo factor" visible en Staff y permisos
      (quien lo tiene activo, quien no)

## S.4 — Confiar en este dispositivo 30 dias — 0/3

- [ ] S.4.1 Tabla de dispositivos de confianza + token **revocable** (cookie
      firmada sola no basta: hay que poder quitarle la confianza a un equipo)
- [ ] S.4.2 Casilla "este es mi equipo, no me pidas el codigo por 30 dias" en
      el reto (reemplaza el "device fingerprinting" del plan viejo con algo
      mas simple y estandar)
- [ ] S.4.3 Revocar dispositivos de confianza desde el perfil

## S.5 — Sesiones y dispositivos activos — 0/3

- [ ] S.5.1 Tabla de seguimiento de sesiones (**obligatoria**: `SESSION_DRIVER=redis`
      no enumera por usuario): session_id, dispositivo, IP, ultimo uso
- [ ] S.5.2 Pantalla en el perfil: lista de sesiones abiertas + "cerrar esta"
- [ ] S.5.3 "Cerrar todas menos esta" (lo que te salva si perdiste un portatil
      o dejaste sesion abierta en un computador prestado)

## S.6 — Registro de accesos al admin — 0/3

- [ ] S.6.1 Tabla de intentos: fecha, IP, dispositivo, resultado
      (exito / contraseña fallida / 2FA fallido)
- [ ] S.6.2 Visible en el perfil propio y en Staff y permisos
- [ ] S.6.3 Enlazado con el **lockout que YA existe** (SEC-3.3) — no duplicar
      el motor, solo darle superficie

## S.7 — Endurecimiento de produccion — 0/2 (va CON el DEPLOY DEMO)

- [ ] S.7.1 Auditar que valida hoy `php artisan security:check` (existe, esta
      citado en `.env.production.example`) y completarlo si falta algo
- [ ] S.7.2 Correr el check contra el `.env` real de produccion: debug apagado,
      secretos generados, HTTPS forzado, cookies seguras, Sentry vigilando

## S.8 — Cierre — 0/3

- [ ] S.8.1 Tests: activacion, reto, codigos de recuperacion (uso unico),
      reset por super_admin, guarda del ultimo super_admin, dispositivo de
      confianza, cierre de sesiones
- [ ] S.8.2 **QA vivo con Chrome** (patron de la sesion del Pulse 2026-07-20):
      activar con un autenticador real, entrar con codigo, gastar un codigo de
      recuperacion, resetear a otro usuario, cerrar una sesion remota
- [ ] S.8.3 Documentar en el manual → `admin/staff-permisos.md`
      (`ROADMAP-MANUAL.md` M5.6) cuando ese frente se retome

---

## Fuera de alcance (decidido)

- **2FA a asistentes**: no. Magic link ya es sin contraseña y el costo de
  adopcion seria brutal.
- **OTP por WhatsApp/SMS**: no. Era la traba del diseño de abril; TOTP lo
  vuelve innecesario. WhatsApp Business API sigue en el backlog seccion 9
  como canal de comunicacion, NO como segundo factor.
- **Llaves fisicas / WebAuthn / passkeys**: no ahora. Si un enterprise lo
  exige, la fundacion de S.0-S.2 ya deja el camino armado.
