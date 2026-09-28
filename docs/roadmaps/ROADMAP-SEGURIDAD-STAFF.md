# ROADMAP — SEGURIDAD DEL STAFF (2FA + sesiones + accesos) — 11/26

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

## DECISIONES DE KAMILO — 4/4 (decididas y hechas 2026-09-27)

> Salieron al implementar S.2 y S.3 (2026-09-26). Kamilo las decidio el
> 2026-09-27 y quedaron implementadas ese dia. No re-preguntar.

- [x] **D.1 El token del staff por la API → opcion (a): el staff NO recibe
      token por la API.** Su puerta es el panel, con contraseña + app
      autenticadora. Un criterio unico, `User::hasPanelAccess()` (los roles de
      `Roles::PANEL_ACCESS`), aplicado en cuatro puntos:
      · login por API con contraseña correcta → 403 `staff_uses_panel` ("Esta
        cuenta es del equipo organizador. Entra por el panel de
        administracion."); con contraseña errada sigue el 422 de siempre (no
        delata el rol);
      · pedir magic link → respuesta generica, no se envia nada; verificar un
        enlace viejo → 403 `staff_uses_panel`;
      · refrescar → 403 (y el token queda revocado);
      · **red en Sanctum** (`Sanctum::authenticateAccessTokensUsing`, en
        `AppServiceProvider`): cualquier token REAL de una cuenta del staff deja
        de autenticar y se borra al primer uso — cubre los emitidos antes del
        despliegue y cualquier puerta de emision futura; al borrarse cae
        tambien del cache de auth del socket. Las sesiones web del admin y los
        `actingAs` de los tests no pasan por ahi.
      **Hallazgo:** el kiosko lobby del demo usaba el token de
      `admin@eventos.test` (event_admin) → habria muerto. El operador de puerta
      NO debe ser cuenta del panel: nuevo `puerta@eventos.test` (rol de
      asistente `admin`, sin rol Spatie del panel) presta el token `kiosk-demo`
      en `Demo5DiasSeeder`. El kiosko de salon usa token de totem, no se toca.
      Tests: `tests/Feature/Security/StaffApiTokenTest.php` (11).
- [x] **D.2 Correo "se activo el segundo factor en tu cuenta" → SI.** Tipo
      `staff_2fa_enabled`, grupo Sistema, ESENCIAL, es/en, con fecha, IP y
      navegador y la linea "si no fuiste tu, avisa al equipo". Se dispara desde
      `TwoFactorService::confirm()` (no desde la pagina: cualquier camino que
      active avisa); la bitacora `2fa_enabled` se movio ahi mismo. Migracion
      del enum `2026_09_27_100000` (**pendiente de correr en la BD dev: MySQL
      estaba apagado el 2026-09-27**) + `EmailTemplateSeeder`.
- [x] **D.3 Cerrar todas las sesiones web al desplegar el 2FA → anotado en el
      runbook** (ROADMAP-INFRAESTRUCTURA, B2 "Al desplegar el 2FA"). Los tokens
      del staff ya no necesitan paso manual: los tumba la red de D.1.
- [x] **D.4 `extension=intl` en el php.ini de la consola → habilitada** (ICU
      75.1, respaldo `php.ini.bak-2026-09-27`). Pest ya puede renderizar tablas
      de Filament en Windows.

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

## S.0 — Fundacion TOTP — 3/3 (HECHO 2026-09-26, 14 tests; Auth+Security+Admin 180 en verde)

- [x] S.0.1 Dependencia `pragmarx/google2fa` instalada (v9.1; solo agrega
      `paragonie/constant_time_encoding`)
- [x] S.0.2 Migracion users: `two_factor_secret` (cifrado),
      `two_factor_confirmed_at`, `two_factor_recovery_codes` (**hasheados**, no
      cifrados: se muestran una vez y solo se comparan) + `two_factor_last_timestep`
      (un codigo sirve UNA vez). Columnas fuera de `$fillable` y en `$hidden`.
      Corrida en la BD dev el 2026-09-26.
- [x] S.0.3 `TwoFactorService` (`app/Services/TwoFactorService.php`,
      test `tests/Feature/Security/TwoFactorServiceTest.php`): generar secreto · URI `otpauth://` para el QR ·
      verificar codigo **con ventana de tolerancia** (los relojes de los
      telefonos se desfasan; sin esto el organizador jura que el codigo esta
      bien y el sistema le dice que no) · generar/consumir codigos de recuperacion

## S.1 — Activacion del segundo factor — 2/3 (HECHO 2026-09-26 S.1.1 y S.1.2)

- [x] S.1.1 Pagina de activacion (`app/Filament/Auth/TwoFactorSetup.php`, ruta
      `/admin/dos-pasos/activar`; conserva el secreto pendiente si la persona recarga) (lenguaje Lumina, espejo del login ya
      tematizado): QR grande + **el secreto tambien en texto** (si la camara
      falla, se escribe a mano) + campo de verificacion para confirmar
- [x] S.1.2 **8 codigos de recuperacion** mostrados UNA sola vez al confirmar,
      con opcion de descargar/copiar y advertencia honesta de guardarlos
- [ ] S.1.3 Regenerar codigos de recuperacion (invalida los anteriores). El
      servicio ya lo hace (`regenerateRecoveryCodes`); falta DONDE: va en la
      pagina "Seguridad de mi cuenta" junto con S.4.3 y S.5.2.

## S.2 — Reto y enforcement — 3/3 (HECHO 2026-09-26, 17 tests en `TwoFactorLoginTest`)

- [x] S.2.1 Pagina de reto post-contraseña: 6 digitos (`TwoFactorChallenge`,
      `/admin/dos-pasos/verificar`; 5 intentos/min POR PERSONA)
- [x] S.2.2 El reto acepta tambien un codigo de recuperacion (se consume; aviso
      persistente con los que quedan)
- [x] S.2.3 Middleware del panel: sin 2FA confirmado → **setup forzado** (no
      lockout, pantalla que no se salta) · con 2FA pero sesion sin verificar →
      reto. Cubrir tambien el arranque de todo el staff existente al desplegar.
      **Como quedo (cambio de diseño con razon):** revisar ruta por ruta NO
      bastaba — la sesion web abre el panel, el Data Center, los exportes y
      TODA la API (`statefulApi()` + Sanctum guard `web`; el Data Center llama
      con `credentials: 'include'`). Asi que la contraseña deja a la persona
      PENDIENTE en la sesion, sin login (`App\Support\TwoFactorLogin`, patron
      Fortify, vence en 10 min), y solo el codigo hace el login real
      (`App\Filament\Auth\Login`). Red de seguridad `RequireTwoFactor`
      (persistente en el panel + Data Center + exportes): expulsa sesiones
      viejas o con el 2FA restablecido. Sin "Recordarme": la cookie reabria la
      sesion sin codigo por meses (confiar en un equipo = S.4).

**QA en Chrome 2026-09-26 (cuenta desechable, borrada al final) — 3 bugs
cazados y corregidos, cada uno con test de regresion:**
- Primer clic en la activacion: `ComponentNotFoundException` (las paginas van
  como rutas sueltas y Filament no las registra en Livewire) → registradas en
  `AdminPanelProvider::boot()`.
- "Continuar al admin" daba 419 "This page has expired" (y luego un 403 al
  recargar): `session()->regenerate()` tira el token CSRF y la activacion se
  queda en la misma pagina → `migrate(true)`: cambia el id, conserva el token.
- Tras un codigo errado el campo quedaba lleno (maxlength 7) y no dejaba
  escribir el siguiente → se vacia al fallar.
Verificado de punta a punta: activar, recargar en los codigos, reto con la app,
codigo errado, codigo de recuperacion en minusculas con espacio (aviso "te
quedan 7"), sesion con 2FA restablecido expulsada. Suites Admin + Security +
Auth + DataCenter: 258 en verde, 1 fallo previo (BUG-348).

## Hallazgos 2026-09-26 (al implementar S.2) — para decidir

Las 3 decisiones que salieron aqui (token por API, correo al activar, cerrar
sesiones al desplegar) se movieron arriba: **DECISIONES PENDIENTES D.1-D.3**.

- Nota (no decision): Mission Control entra por enlace firmado (HMAC), no por sesion: fuera de este
   candado por diseño. `/data-center/` como HTML es estatico del servidor web;
   los datos van por la API.

## S.3 — Recuperacion / rescate (CRITICO por la obligatoriedad) — 3/3 (HECHO 2026-09-26, 13 tests en `TwoFactorRescueTest`)

> Si el 2FA es obligatorio y alguien pierde el telefono el dia del montaje, NO
> puede quedarse afuera del admin. Sin estas 3 salidas la obligatoriedad es un
> riesgo operativo, no una mejora de seguridad.

- [x] S.3.1 Un **super_admin resetea el 2FA de otra persona** desde Staff y
      permisos — accion auditada + correo de aviso al afectado. Boton SOLO
      dentro de Editar (decision Kamilo: en la fila empujaba "Editar" fuera de
      columna) (`TwoFactorService::resetFor`). Bitacora propia
      `staff_security_events` (admin_audit_log exige evento; esta tabla es la
      base de S.6) con actor, via, IP; tambien registra `2fa_enabled`. Correo
      `staff_2fa_reset`, grupo Sistema, ESENCIAL (no se apaga). Solo super_admin,
      nunca sobre si mismo; el servidor rechaza la peticion armada a mano.
- [x] S.3.2 Guarda: el **ultimo super_admin no puede quedar encerrado afuera**
      (espejo del guard anti auto-borrado que ya existe en Staff). `StaffGuard`
      frena eliminar, quitar el rol y desactivar al unico super_admin activo
      (event_admin y org_admin tienen `manage-users`: el riesgo era real).
      Ultima salida si el unico super_admin pierde el telefono:
      `php artisan eventos:restablecer-2fa correo` (auditado, via consola).
- [x] S.3.3 Columna/estado "segundo factor" visible en Staff y permisos
      (quien lo tiene activo, quien no; tooltip con la fecha).

**Hallazgos al implementar S.3 (2026-09-26):**
- **BUG corregido — `Roles::STAFF_LISTING`** nombraba `panel_user`, `admin`,
  `monitor` (no existen en RoleSeeder) y dejaba afuera org_admin, event_admin,
  moderator y staff_checkin: quien se creaba con esos roles desaparecia de
  Staff y permisos, y no se le podia rescatar. Test de regresion.
- **Tests en Windows:** el PHP de consola no tiene la extension `intl` y las
  tablas de Filament no se renderizan en Pest → decision arriba, **D.4**.
- **QA con Chrome:** la pestaña de automatizacion queda oculta y Chrome pausa
  `requestAnimationFrame`: los modales de Filament no terminan de abrir. No es
  bug; la ruta del servidor quedo probada (montar, confirmar, restablecer,
  bitacora, correo). Falta ver el modal con los ojos: Kamilo.

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
      el motor, solo darle superficie. **OJO (verificado 2026-09-26):** ese
      lockout (`locked_until`) vive SOLO en el login de la API
      (`AuthController.php:187`). El login del admin es el `->login()` de
      Filament por defecto, que solo limita 5 intentos/min. Hay que llevar el
      lockout al login del admin, no solo mostrarlo.

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
