# ROADMAP — SEGURIDAD DEL STAFF (2FA + sesiones + accesos) — 25/26

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
      del enum `2026_09_27_100000` (corrida en la BD dev) + el tipo en la
      migracion BASE de `email_templates` (SQLite e instalacion limpia usan ese
      CHECK; sin eso `eventos:instalar` revienta) + `EmailTemplateSeeder`.
      **QA vivo 2026-09-27:** activacion en Chrome → bitacora con IP y
      navegador → correo en Mailpit correcto.
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

## S.1 — Activacion del segundo factor — 3/3 (S.1.3 HECHO 2026-09-29)

- [x] S.1.1 Pagina de activacion (`app/Filament/Auth/TwoFactorSetup.php`, ruta
      `/admin/dos-pasos/activar`; conserva el secreto pendiente si la persona recarga) (lenguaje Lumina, espejo del login ya
      tematizado): QR grande + **el secreto tambien en texto** (si la camara
      falla, se escribe a mano) + campo de verificacion para confirmar
- [x] S.1.2 **8 codigos de recuperacion** mostrados UNA sola vez al confirmar,
      con opcion de descargar/copiar y advertencia honesta de guardarlos
- [x] S.1.3 Regenerar codigos de recuperacion (invalida los anteriores).
      **HECHO 2026-09-29** en "Seguridad de tu cuenta": "Generar nuevos" →
      modal que pide el codigo de la app (sudo mode de GitHub,
      `TwoFactorService::regenerateWithCode`, mismo freno de 5/min) → modal
      con los 8 codigos una sola vez (no se cierra por clic afuera; Copiar con
      respaldo para HTTP, Descargar, "Los guarde"). Bitacora
      `recovery_codes_regenerated`. Aviso en ambar con 2 o menos.

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

## S.4 — Confiar en este dispositivo 30 dias — 3/3 (HECHO 2026-09-29, 19 + 13 tests)

> Referencias (pedido de Kamilo: mirar como lo hacen otros): Google "No volver
> a preguntar en este equipo", Microsoft "No preguntar durante 30 dias",
> GitHub. En los tres la confianza SOLO evita el codigo, nunca la contraseña;
> es revocable y vence.

- [x] S.4.1 Tabla `trusted_devices` + modelo `TrustedDevice`. Cookie cifrada
      `eventos_trusted_device` = `id|secreto` (HttpOnly, SameSite=Lax, path `/`
      porque el login corre por `/livewire/update`); en la base solo el sha256
      del secreto. **30 dias fijos** (usarlo no renueva). El login
      (`App\Filament\Auth\Login`) con equipo valido de ESA persona pasa por el
      mismo `TwoFactorLogin::complete()` que el reto. Cookie vencida, revocada
      o ajena → se borra y va al reto. **Revocacion de todos sus equipos** al
      restablecer el 2FA (`TwoFactorService::resetFor`), al cambiar la
      contraseña y al desactivar la cuenta (hook `updated` en `User`: cubre
      todos los caminos). Bitacora `trusted_device_added` /
      `trusted_devices_revoked` (meta: motivo y cuantos). Nombre legible
      "Chrome en Windows" (`labelFor`) para S.4.3. Efecto conocido: si Laravel
      re-hashea la contraseña al entrar, cuenta como cambio y pide el codigo
      una vez mas.
- [x] S.4.2 Casilla "Confiar en este equipo por 30 dias" en el reto, **arriba
      del campo** (el codigo se envia solo al completar 6 digitos: abajo
      llegaria tarde), con aviso "no la marques en un computador prestado o
      compartido". **No se ofrece ni se respeta con codigo de recuperacion**
      (usarlo = perdi el telefono). Reusa `lum-2fa-check`.

**QA vivo en Chrome 2026-09-29 (cuenta desechable `qa-equipo`, borrada al
final), 0 bugs:** reto con la casilla → marcada + codigo real → panel; equipo
"Chrome en Windows" + bitacora `trusted_device_added` · salir y entrar → solo
contraseña, directo al panel, `last_used_at` anotado · restablecer su 2FA →
bitacora `trusted_devices_revoked` (2fa_reset, 1) + sesion abierta expulsada
al login + entrar de nuevo cae en la activacion, no en el panel.
- [x] S.4.3 **HECHO 2026-09-29.** Pagina "Seguridad de tu cuenta"
      (`App\Filament\Pages\AccountSecurity`, `/admin/mi-seguridad`), desde el
      menu del avatar. Lab aprobado por Kamilo:
      `design/features/admin-2fa/lab-seguridad-cuenta.html` (refs Google Cuenta
      → Seguridad, GitHub, Stripe/Linear). Cards "Como entras" + "Equipos de
      confianza" (solo los propios y vigentes, "Este equipo" por la cookie,
      Quitar instantaneo sin modal, "Quitar todos" con 2+; quitar el actual
      borra la cookie). Bitacora `trusted_devices_revoked` reason
      `self`/`self_all`. 13 tests en `AccountSecurityTest`.
      **QA Chrome 2026-09-29 — 1 bug cazado y corregido:** el campo se envia
      solo a los 6 digitos + Enter = doble envio; el segundo salia "ya se uso"
      y el error TAPABA los codigos recien generados (viejos muertos, nuevos
      sin ver). Guard en `regenerate()` + test de regresion. Verificado en
      vivo despues del fix.

## S.5 — Sesiones y dispositivos activos — 3/3 (HECHO 2026-09-29, 13 tests en `StaffSessionTest`)

> Lab aprobado por Kamilo 2026-09-29 (seccion S.5 de `lab-seguridad-cuenta.html`).

- [x] S.5.1 Tabla `staff_sessions` + modelo `StaffSession`: id de sesion
      CIFRADO (con el en claro se secuestra la sesion) + sha256 para buscar la
      actual. Se anota en `RequireTwoFactor` (unico punto por donde pasan panel,
      Data Center y exportes con el 2FA pasado), actividad como mucho 1 vez por
      minuto, las vencidas (> `SESSION_LIFETIME`) se limpian solas. Liga
      `trusted_device_id` si se entro por un equipo de confianza o se marco.
      "Salir" borra la fila (listener de `Logout`).
- [x] S.5.2 Card "Sesiones abiertas" en "Seguridad de tu cuenta", ANTES de
      Equipos: navegador, ultima actividad ("hace un momento" bajo 1 min), IP.
      "Esta sesion" sin Cerrar (para eso esta Salir). Cerrar = destruir en el
      manejador de sesiones (Redis) + borrar fila → ese equipo cae al login en
      su siguiente clic. Si la sesion es de un equipo de confianza, lo dice y
      ofrece "Quitar la confianza" (cerrar no la quita, como Google).
- [x] S.5.3 "Cerrar las demas". NO usa `Auth::logoutOtherDevices()` (pide
      la contraseña y la re-hashea → quitaria la confianza de todos los
      equipos). Bitacora `sessions_closed` (reason self/others).
      **Ademas:** restablecer el 2FA, cambiar la contraseña y desactivar la
      cuenta cierran TODAS sus sesiones al instante (reason en la bitacora).
      **Hueco cerrado de paso (hallazgo 2026-09-29):** una cuenta DESACTIVADA
      seguia entrando al admin — `canAccessPanel` solo miraba el rol y el login
      de Filament no revisa `is_active`. Ahora `is_active !== false` en
      `canAccessPanel` (Filament lo revisa en el login y en cada peticion).

**QA vivo 2026-09-29 (cuenta desechable, borrada), con una segunda sesion
REAL en Redis + curl con su cookie:** curl abre el admin (200) → "Cerrar"
desde la pagina → curl cae al login (302), bitacora `self`. Desactivar la
cuenta: sus 2 sesiones (Chrome y la de curl) cerradas y la confianza quitada;
Chrome redirige al login; la contraseña correcta ya no entra (mensaje
generico, no delata). Detalle de copy corregido: "hace 0 segundos" → "hace un
momento".

## S.6 — Registro de accesos al admin — 3/3 (HECHO 2026-09-29, 14 tests en `StaffLockoutTest`)

> Lab aprobado por Kamilo 2026-09-29 (seccion S.6 de `lab-seguridad-cuenta.html`),
> incluido el bloqueo por CODIGOS con correo (propuesta nueva).

- [x] S.6.1 Sin tabla nueva: los intentos van a `staff_security_events` (ya
      tenia IP, navegador, meta). Tipos `login_ok` (meta.via: code, recovery,
      trusted_device, activation), `login_failed_password`,
      `login_failed_code`, `account_locked` (reason password|code),
      `account_unlocked`. "Entro" se anota en UN solo lugar:
      `TwoFactorLogin::complete()`. Solo cuentas de staff (las de asistentes
      no se cuentan desde el admin). Los ingresos se borran a los 90 dias
      (tarea `staff-security:prune-logins`, 2:30am); los cambios de seguridad
      se quedan como auditoria.
- [x] S.6.2 "Actividad reciente" al final de "Seguridad de tu cuenta" (en
      segunda persona, 10 + "Mostrar mas") y "Actividad de acceso" en la ficha
      de Staff y permisos (tercera persona, 20). Una sola linea de tiempo con
      entradas y cambios de seguridad (`App\Support\SecurityActivity`); lo
      fallido en ambar, los bloqueos en rojo.
- [x] S.6.3 Bloqueo en el login del admin con el MISMO motor de la API
      (`failed_login_attempts` + `locked_until`, `App\Support\StaffLockout`):
      5 contraseñas incorrectas seguidas = 30 min; **5 codigos incorrectos
      seguidos = 30 min + correo esencial `staff_account_locked`** ("alguien
      tiene tu contraseña"; columna nueva `two_factor_failed_attempts`). El
      mismo codigo repetido (envio automatico + Enter) cuenta una vez. Antes
      del bloqueo, el mensaje generico de siempre. Bloqueada: mensaje con los
      minutos y "pidele a un super_admin". El bloqueo del admin lo ve tambien
      la API (423). "Desbloquear" en Editar (solo super_admin, solo si esta
      bloqueada) + aviso rojo arriba con el porque.

**QA vivo 2026-09-29 (dos cuentas desechables, borradas):** 5 codigos
incorrectos → bloqueada + bitacora + correo en Mailpit "Bloqueamos tu cuenta:
alguien tiene tu contraseña" + de vuelta al login con el aviso; la contraseña
correcta ya no entra (mensaje con los minutos); el super_admin ve en la ficha
el aviso rojo, "Desbloquear" y la actividad en tercera persona; Desbloquear →
aviso y boton desaparecen, bitacora `account_unlocked`. 0 bugs.

## S.7 — Endurecimiento de produccion — 1/2 (S.7.2 va CON el montaje, B2: hoy no hay produccion)

- [x] S.7.1 **HECHO 2026-09-29** (15 tests en `SecurityCheckCommandTest`, 7
      nuevos). Auditoria: ya validaba debug, APP_KEY, secretos con
      placeholder, clave de la base, vencimiento de tokens, clave de Redis
      remoto y rastros del demo. **Faltaba y se agrego:**
      · CRITICO (bloquea el despliegue): cookie de sesion no segura (era
        aviso; con el 2FA la cookie ES la llave del admin) · `SESSION_HTTP_ONLY`
        apagado · `SESSION_DRIVER` cookie/array (no se podrian cerrar sesiones
        a distancia, S.5) · `APP_URL` sin https · `APP_ENV=local` (registra
        Telescope, abierto a cualquiera en local) · `MAIL_MAILER` log/array
        (no saldrian los correos esenciales) · hay staff pero ningun
        super_admin activo (nadie podria rescatar ni desbloquear).
      · AVISO (no bloquea): Sentry sin DSN · LOG_LEVEL=debug ·
        SESSION_DRIVER=file · APP_ENV distinto de production · staff sin 2FA
        todavia · base sin staff (falta `eventos:instalar`).
      Corrido contra el `.env` local: bloquea con 10 criticos, como debe.
- [ ] S.7.2 Correr el check contra el `.env` real de produccion: debug apagado,
      secretos generados, HTTPS forzado, cookies seguras, Sentry vigilando

## S.8 — Cierre — 3/3 (HECHO 2026-09-29)

- [x] S.8.1 Repaso de cobertura: los 7 puntos tienen test (activacion, reto,
      codigos de un solo uso, reset por super_admin, guarda del ultimo
      super_admin, equipo de confianza, cierre de sesiones). El repaso
      encontro 3 casos de S.6 sin cubrir y se agregaron: los codigos de
      recuperacion incorrectos cuentan para el bloqueo, una cuenta bloqueada
      con el reto abierto vuelve al login, y el correo de bloqueo es esencial
      con plantilla es/en. Total del frente: 133 tests en 9 archivos.
- [x] S.8.2 QA vivo en Chrome de punta a punta (cuentas desechables,
      borradas): activacion forzada con QR y clave + codigo real + los 8
      codigos + Copiar (por el respaldo: en `.test` no hay
      `navigator.clipboard`) + Continuar al admin · generar codigos nuevos ·
      entrar con un codigo de recuperacion en minusculas y con espacio (aviso
      "te quedan 7", sin casilla de confianza) · super_admin restablece el 2FA
      desde la ficha (modal, aviso, bitacora, correo en Mailpit) · cerrar una
      sesion remota (S.5). **1 bug cazado y corregido:** si el navegador
      traia restos de otra cuenta (eliminada con la sesion abierta), la
      huella de contraseña vieja hacia que `AuthenticateSession` expulsara a
      la persona nueva en su primer clic despues de entrar. Fix en
      `TwoFactorLogin::complete()` + test de regresion (falla sin el fix).
      **Pendiente de Kamilo:** ver con los ojos los modales de Filament
      (la pestaña de automatizacion no los pinta bien) con su cuenta real.
- [x] S.8.3 Pagina del manual escrita:
      `manual/src/content/docs/admin/staff-permisos.md` (roles + toda la
      seguridad del staff, con procedencia). Grupo "Admin" agregado al
      sidebar; el sitio compila.


**QA de Kamilo con su cuenta real (2026-09-29): modales, restablecer y
"Generar nuevos" verificados con los ojos. Dos ajustes pedidos y hechos:**
- **Crear un miembro del staff vuelve a la lista** con el aviso (quedarse en
  el formulario daba sensacion de error). `CreateUser::getRedirectUrl()` +
  test.
- **Activacion del 2FA v2** (lab aprobado:
  `design/features/admin-2fa/lab-2fa-activacion-v2.html`). La v1 no era
  responsive: 245 px de scroll en portatil 1366x768 y 286 px en celular.
  Ahora: portatil/tablet en dos columnas (QR + 3 pasos de una linea), la
  clave solo si se pide ("¿No puedes escanear?", 2 filas de 4 grupos);
  celular con la clave primero + "Abrir en mi app" (enlace `otpauth://`) y
  el QR a un toque; los 8 codigos en 2 filas de 4 en portatil; una sola
  escala de espacios (4-8-12-16-24-32) tambien en el reto; fuente
  monoespaciada propia (`--lum-fm`, JetBrains Mono: en Windows caia en la
  del sistema). "Copiar clave" ahora tambien funciona en HTTP. Medido en la
  pagina real (HTML pedido por curl con sesion aparte, para no tocar la
  sesion de Kamilo): 0 px de scroll en portatil, tablet y celular, en los
  dos pasos y en el reto. **Kamilo la reviso en el flujo real el
  2026-09-29: se ve bien.**
---

## Fuera de alcance (decidido)

- **2FA a asistentes**: no. Magic link ya es sin contraseña y el costo de
  adopcion seria brutal.
- **OTP por WhatsApp/SMS**: no. Era la traba del diseño de abril; TOTP lo
  vuelve innecesario. WhatsApp Business API sigue en el backlog seccion 9
  como canal de comunicacion, NO como segundo factor.
- **Llaves fisicas / WebAuthn / passkeys**: no ahora. Si un enterprise lo
  exige, la fundacion de S.0-S.2 ya deja el camino armado.
