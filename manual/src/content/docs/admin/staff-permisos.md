---
title: Staff y permisos
description: Quién entra al admin, qué puede hacer cada rol y cómo se protege cada cuenta del equipo.
---

Aquí decides quién de tu equipo entra al admin y con qué alcance. También
es donde cuidas esas cuentas: cada persona entra con su contraseña y un
código de su teléfono, y tú puedes ayudarla si pierde el acceso.

## Qué es

Hay dos tipos de personas en un evento, y no se mezclan:

- **Tu equipo (staff)**: quienes operan el evento desde el admin. Se
  manejan en **Staff y permisos**, dentro del grupo Sistema.
- **La gente del evento**: asistentes, vendedores y quienes operan la
  puerta. Se manejan en Asistentes.
  <!-- fuente: eventos-backend app/Support/Roles.php:10-15 -->

Cada persona del staff tiene uno o varios roles. Los roles son un
catálogo fijo: no se crean ni se editan, solo se asignan.
<!-- fuente: eventos-backend app/Support/Roles.php:40 SPATIE_LABELS; database/seeders/RoleSeeder.php:38-45 -->

| Rol | Para quién es |
|---|---|
| Super admin | Todo, incluido el grupo Sistema. Es quien rescata a los demás. |
| Admin de organización | Eventos, contenido y personas. |
| Admin de evento | Opera el evento. No crea eventos. |
| Moderador | Modera contenido y apoya el check-in. |
| Staff de check-in | Solo escanear y atender la puerta. No entra al admin. |

Al admin entran los cuatro primeros. El staff de check-in trabaja desde
la app, no desde el admin.
<!-- fuente: eventos-backend app/Support/Roles.php:27 PANEL_ACCESS; app/Models/User.php:61 canAccessPanel -->

## Cómo se configura (admin)

### Agregar a alguien del equipo

1. Entra a **Staff y permisos** y crea la persona con su nombre, correo y
   una contraseña inicial.
2. Marca sus roles en **Qué puede hacer**.
3. Deja **Cuenta activa** encendida. Si la apagas, la persona queda
   afuera de inmediato, aunque tenga el admin abierto.
   <!-- fuente: eventos-backend app/Models/User.php:61 canAccessPanel (is_active); app/Models/User.php:36 cierra sesiones y equipos -->

### El primer ingreso: la verificación en dos pasos

La verificación en dos pasos es obligatoria para todo el que entra al
admin. La primera vez que alguien entra, el sistema no lo deja pasar
hasta activarla:

1. Escanea el código QR con una app autenticadora (Google Authenticator,
   Microsoft Authenticator, Authy o 1Password). Si la cámara falla, la
   clave también aparece escrita para copiarla a mano.
2. Escribe el código de 6 dígitos que muestra la app.
3. Guarda los **8 códigos de recuperación**. Se muestran una sola vez.
   Cada uno sirve para entrar una vez si no tienes el teléfono a la mano.
   <!-- fuente: eventos-backend app/Filament/Auth/TwoFactorSetup.php:60-99; app/Services/TwoFactorService.php:45 RECOVERY_CODES -->

Al terminar, la persona recibe un correo avisándole que se activó. Si no
fue ella, ese correo es la señal de que alguien más tiene su contraseña.
<!-- fuente: decision Kamilo D.2 2026-09-27; app/Services/TwoFactorService.php confirm() -->

### Entrar todos los días

Contraseña, y después el código de la app. En la pantalla del código
puedes marcar **Confiar en este equipo por 30 días**: en ese computador
dejas de escribir el código durante 30 días, pero la contraseña se sigue
pidiendo siempre. No la marques en un computador prestado.
<!-- fuente: eventos-backend app/Models/TrustedDevice.php:23 DAYS; app/Filament/Auth/Login.php (TrustedDevice::match) -->

### Seguridad de tu cuenta

En el menú de tu avatar, arriba a la derecha, está **Seguridad de tu
cuenta**. Es tu página, no la del evento:
<!-- fuente: eventos-backend app/Providers/Filament/AdminPanelProvider.php:49; app/Filament/Pages/AccountSecurity.php -->

- **Cómo entras**: cuántos códigos de recuperación te quedan y el botón
  para generar nuevos. Para generarlos te pide el código de la app, y los
  anteriores dejan de servir.
  <!-- fuente: eventos-backend app/Services/TwoFactorService.php:171 regenerateWithCode -->
- **Sesiones abiertas**: dónde tienes el admin abierto ahora mismo.
  Puedes cerrar una o cerrar todas las demás. En ese equipo, el siguiente
  clic lleva al inicio de sesión.
  <!-- fuente: eventos-backend app/Models/StaffSession.php:107 closeOne, :130 closeAllFor -->
- **Equipos de confianza**: los computadores donde marcaste la casilla de
  30 días. Puedes quitarle la confianza a cualquiera.
- **Actividad reciente**: tus entradas y los cambios de seguridad de los
  últimos 90 días, incluidos los intentos fallidos.
  <!-- fuente: eventos-backend app/Support/SecurityActivity.php:24 DAYS -->

### Cuando alguien pierde el acceso

Esto lo hace un **super admin**, desde la ficha de la persona en Staff y
permisos (botón Editar):

- **Restablecer segundo factor**: para quien perdió el teléfono y sus
  códigos. La app que tenía deja de servir, se cierran sus sesiones y en
  su siguiente ingreso activa la verificación de nuevo. La persona recibe
  un correo y queda registrado quién lo hizo.
  <!-- fuente: eventos-backend app/Filament/Resources/UserResource/Pages/EditUser.php:45; app/Services/TwoFactorService.php:246 resetFor -->
- **Desbloquear**: aparece solo si la cuenta está bloqueada, junto a un
  aviso que explica por qué.
  <!-- fuente: eventos-backend app/Filament/Resources/UserResource/Pages/EditUser.php:30 -->
- **Actividad de acceso**: al final de la ficha ves lo mismo que la
  persona ve en su página.

## Qué ve el asistente

Nada de esto. La verificación en dos pasos es solo para el equipo que
entra al admin; los asistentes siguen entrando a la app y a la webapp
con su enlace de acceso, sin códigos.
<!-- fuente: ROADMAP-SEGURIDAD-STAFF "Fuera de alcance": 2FA a asistentes no -->

Una cuenta del staff no sirve para entrar a la app ni a la webapp como
asistente: su única puerta es el admin. Si lo intenta, la app le dice
que entre por el panel de administración.
<!-- fuente: decision Kamilo D.1 2026-09-27; eventos-backend app/Http/Controllers/Api/V1/AuthController.php:238 -->

## Lo que se puede y lo que NO

**Se puede**

- Tener varias personas con el mismo rol, y varios roles en una persona.
- Tener el admin abierto en varios equipos a la vez y cerrarlos a
  distancia.
- Entrar con un código de recuperación cuando no tienes el teléfono.
- Rescatar a cualquier persona del equipo sin ayuda técnica, siempre que
  haya un super admin disponible.

**No se puede**

- Entrar al admin sin la verificación en dos pasos. No hay forma de
  apagarla ni de saltarla.
- Crear roles nuevos ni cambiar lo que hace cada rol.
- Dejar el admin sin super admin: el sistema no te deja eliminar,
  desactivar ni quitarle el rol al único que queda.
  <!-- fuente: eventos-backend app/Support/StaffGuard.php:25 -->
- Que un super admin restablezca su propio segundo factor desde el
  panel. Se lo hace otro super admin.
- Usar "Recordarme" en el inicio de sesión. Se quitó a propósito: para
  eso está la casilla de confiar en el equipo, que sí se puede revocar.
- Apagar los correos de seguridad (activación, restablecimiento y
  bloqueo). Son esenciales.
  <!-- fuente: eventos-backend app/Support/EmailCatalog.php essential => true -->

## Gotchas y preguntas frecuentes

**Mi cuenta dice que está bloqueada.**
Después de 5 contraseñas incorrectas seguidas, la cuenta se bloquea 30
minutos. Pasa lo mismo con 5 códigos incorrectos seguidos, y en ese caso
además te llega un correo: si alguien falló el código es porque ya tenía
tu contraseña, así que cámbiala. El bloqueo se levanta solo, o antes si
un super admin te desbloquea.
<!-- fuente: eventos-backend app/Support/StaffLockout.php:30 MAX_ATTEMPTS, :32 MINUTES -->

**El código de la app no me funciona.**
Casi siempre es la hora del teléfono. Ponla en automático y espera el
siguiente código. Cada código sirve una sola vez.
<!-- fuente: eventos-backend app/Services/TwoFactorService.php:43 WINDOW; consumeTotp -->

**Escribí bien la contraseña y tardé en poner el código. Me devolvió al
inicio.**
Tienes 10 minutos entre la contraseña y el código. Pasado ese tiempo
empiezas de nuevo.
<!-- fuente: eventos-backend app/Support/TwoFactorLogin.php:35 PENDING_TTL -->

**Marqué "Confiar en este equipo" y me volvió a pedir el código.**
La confianza dura 30 días exactos desde que la marcas; usar el equipo no
la renueva. También se pierde si cambias tu contraseña, si te
restablecen el segundo factor o si desactivan tu cuenta.
<!-- fuente: eventos-backend app/Models/TrustedDevice.php:109 revokeAllFor; app/Models/User.php:36 -->

**Entré con un código de recuperación y no me dejó confiar en el equipo.**
Es a propósito. Usar un código de recuperación significa que no tienes
el teléfono, y ese no es el momento de confiar en un equipo nuevo.
<!-- fuente: eventos-backend app/Filament/Auth/TwoFactorChallenge.php:119 -->

**Cerré una sesión y el equipo volvió a entrar sin código.**
Cerrar la sesión y quitar la confianza son dos cosas distintas. Si ese
equipo era de confianza, quítasela también: la fila de la sesión te lo
avisa y te da el enlace.

**El único super admin perdió el teléfono y los códigos.**
No hay nadie que lo rescate desde el panel. El equipo técnico lo
restablece desde el servidor con `php artisan eventos:restablecer-2fa`
y el correo de la persona. Queda registrado igual.
<!-- fuente: eventos-backend app/Console/Commands (eventos:restablecer-2fa) -->

**Le cambié la contraseña a alguien del equipo.**
Se le cierran las sesiones que tenía abiertas y sus equipos dejan de ser
de confianza. En su siguiente ingreso escribe la contraseña nueva y el
código.
<!-- fuente: eventos-backend app/Models/User.php:36 booted() updated -->
