---
name: EventOS — notas técnicas del backend implementado
description: Decisiones y problemas encontrados durante la implementación de eventos-backend (Sesión 0.2)
type: project
---

Notas surgidas durante la implementación de la Sesión 0.2 (2026-03-28).

## Tabla event_sessions (no sessions)

La tabla de agenda se llama `event_sessions` (no `sessions`) porque Laravel usa `sessions` para las sesiones HTTP. El modelo es `EventSession` con `protected $table = 'event_sessions'`. Todos los foreign keys usan `constrained('event_sessions')`.

**Why:** Colisión con tabla nativa de Laravel.
**How to apply:** En todas las sesiones futuras usar `event_sessions` y el modelo `EventSession`, nunca `Session`.

---

## PHP en PATH en Windows — usar Laragon

En esta máquina hay dos PHP instalados:
- **WinGet PHP 8.4** — en PATH por defecto, SIN extensiones (zip, fileinfo, redis)
- **Laragon PHP 8.3** — en `C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\`

Siempre usar el de Laragon para composer y artisan:
```bash
export PATH="/c/laragon/bin/php/php-8.3.26-Win32-vs16-x64:$PATH"
```

**How to apply:** Al iniciar cualquier sesión de backend, agregar Laragon PHP al PATH primero.

---

## ext-pcntl / ext-posix no existen en Windows

Horizon y Telescope requieren `ext-pcntl` y `ext-posix` que son Unix-only. Se instalan con:
```bash
composer require laravel/horizon --ignore-platform-req=ext-pcntl --ignore-platform-req=ext-posix
```
Y `"platform-check": false` en `composer.json` para no fallar en cada `composer install`.

En producción (Linux VPS) estas extensiones sí existen — no es un problema real.

---

## Virtual hosts de Laragon

Laragon crea virtual hosts automáticos para carpetas en `C:\laragon\www\`. Si la carpeta fue creada por bash (no por Laragon), necesita un **Reload** en el ícono de Laragon para detectarla. Si no aparece, agregar manualmente en `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1      eventos-backend.test #laragon magic!
```

---

## Usuarios de prueba (seeders)

| Email | Password | Rol en evento |
|---|---|---|
| superadmin@eventos.test | password | super admin (Spatie) |
| admin@eventos.test | password | admin del evento |
| presencial@eventos.test | password | asistente presencial |
| virtual@eventos.test | password | asistente virtual |
| vendedor@eventos.test | password | vendedor/stand |

Evento activo: `summit-empresarial-2026` (slug).

---

## BelongsToMany con foreign keys explícitos (S1.3a)

Laravel infiere el nombre del FK desde el nombre de la clase. `EventSession` → infiere `event_session_id`, pero la pivot `session_speaker` usa `session_id`. Siempre pasar los 4 parámetros:

```php
// En EventSession:
$this->belongsToMany(Speaker::class, 'session_speaker', 'session_id', 'speaker_id');
// En Speaker:
$this->belongsToMany(EventSession::class, 'session_speaker', 'speaker_id', 'session_id');
```

**Why:** Sin FK explícitos, Laravel genera queries con `event_session_id` que no existe y tira error.

---

## htmlpurifier en Windows (S1.3a)

```bash
composer require ezyang/htmlpurifier --ignore-platform-req=ext-pcntl --ignore-platform-req=ext-posix
```

Mismos flags que horizon/telescope.

---

## QR tokens para attendees del seeder (S1.6)

Los attendees creados por seeders no tienen QR tokens automáticamente (solo los que se registran vía `AuthService::register()`). Para generarlos en dev:

```bash
php artisan tinker --execute="
\$service = app(App\Services\AuthService::class);
App\Models\Attendee::whereDoesntHave('qrToken')->get()->each(fn(\$a) => \$service->generateQrToken(\$a));
"
```

**How to apply:** Si en tests manuales el QR del seeder da `QR_INVALID`, es porque falta el token. Correr el comando anterior.

---

## Alter migration con UNIQUE compuesta y FK (S1.6)

MySQL no permite `dropUnique` en un índice que sirve de soporte a un FK. Solución:
1. `dropForeign` en las columnas implicadas
2. `dropUnique` del compuesto
3. Agregar índices simples en cada FK
4. Re-crear los `foreign()` con esos índices

**How to apply:** Siempre que se necesite eliminar un índice único compuesto que incluya columnas FK, seguir este orden.

---

## ContentObserver genérico (S1.3a)

Un solo observer para 5 modelos usando `class_basename($model)` para decidir qué caché invalidar. Evita 5 archivos de observer separados. Registrado en `AppServiceProvider::boot()`.

---

## admin_audit_log — tabla name singular (S1.8)

Laravel pluralizaría `AdminAuditLog` a `admin_audit_logs` automáticamente. El modelo requiere:
```php
protected $table = 'admin_audit_log';
```
**Why:** La tabla se llama `admin_audit_log` (singular) por convención del proyecto, pero Laravel Eloquent pluraliza por defecto.

---

## finfo extension no disponible en Laragon Windows (S1.10)

`Storage::disk('public')->put()` usa `League\Flysystem` que instancia `FinfoMimeTypeDetector`, el cual requiere la extensión PHP `fileinfo` (`finfo` class). En Laragon con WinGet PHP esta extensión no está disponible.

**Fix:** Usar `file_put_contents` directo:
```php
$path = storage_path('app/public/' . $filename);
if (!is_dir(dirname($path))) mkdir(dirname($path), 0777, true);
file_put_contents($path, $content);
$url = url('storage/' . $filename);
```

**How to apply:** Siempre que un Job o Controller necesite escribir archivos al disco `public`, usar `file_put_contents` en vez de `Storage::disk('public')->put()`.

---

## QUEUE_CONNECTION=sync en dev con Laragon (S1.10)

No hay proceso `queue:work` corriendo en Laragon. Si se usa `QUEUE_CONNECTION=redis`, los jobs se encolan pero nunca se procesan hasta correr el worker manualmente.

**Fix para dev:** `QUEUE_CONNECTION=sync` en `.env` — los jobs corren sincrónicos dentro del mismo request HTTP.
**En producción:** usar `redis` con supervisor corriendo `queue:work --daemon`.

**How to apply:** En nuevas features con jobs, documentar que en dev va sync y en prod va redis.

---

## create_moderation_tables — migración canónica (S1.8)

`database/migrations/2026_03_28_200014_create_moderation_tables.php` es la migración canónica para `attendee_bans`, `attendee_role_changes` y `admin_audit_log`. NO crear migraciones separadas para estas tablas — ya existen en esa migración.

El esquema original tenía `lifted_at`/`lifted_by`; actualizado a `unbanned_at`/`unbanned_by` + `event_id` obligatorio en `attendee_bans`.

**How to apply:** Antes de crear una migración para estas tablas, verificar que no ya exista en `create_moderation_tables`.
