# Catalogo de variables de entorno del backend

> ROADMAP-MIGRACION-DOTNET **M0.5.4** (2026-10-22). Sale del `.env` local (71 variables, **solo nombres**, nunca
> valores) cruzado con cada `env('...')` de `config/`, `app/`, `routes/`, `bootstrap/` y `database/`.
> Columna **C#**: **si** = el servicio C# la necesita con el mismo valor; **equiv** = C# tiene la suya propia
> (mismo proposito, otro nombre o formato); **no** = es de Laravel o de algo que se apaga.

## Lo que C# NO puede ignorar

1. **`APP_KEY` es compartida.** Tres columnas estan cifradas con ella (cast `encrypted` de Laravel: AES-256-CBC + HMAC,
   carga JSON en base64): `users.two_factor_secret` (2FA del staff), `organization_email_settings.password` (SMTP por
   organizacion) y `staff_sessions.session_id`. El admin nuevo no valida el 2FA sin leer ese formato con esa llave.
   Va como tarea de la fase 1 (lector compatible en `Contracts`, con prueba contra un valor cifrado por PHP).
2. **`APP_QR_SECRET` firma** los QR de check-in, los enlaces de Mission Control y del display (HMAC-SHA256). C# la
   necesita igual o los QR y enlaces ya impresos dejan de valer.
3. **`SOCKET_INTERNAL_SECRET` y `WEBAPP_INTERNAL_SECRET`** son los secretos entre servicios. Los dos lados del canal
   (socket o hub, webapp) tienen que tener el mismo valor.
4. **Faltan en `.env.example`** (una instalacion copiada del ejemplo arranca sin ellas o con el valor por defecto
   `change_this_secret_in_production`): `APP_QR_SECRET`, `APP_DEEP_LINK_SCHEME`, `SOCKET_SERVER_URL`,
   `SOCKET_INTERNAL_SECRET`, `WEBAPP_INTERNAL_URLS`, `WEBAPP_INTERNAL_SECRET`, `DB_HOST` a `DB_PASSWORD`. Y con
   valor por defecto en codigo, sin estar en ningun `.env`: `TRUSTED_PROXIES`, `RESEND_KEY`, `FRONTEND_URL`,
   `KIOSK_URL`, `LANDING_URL`, `REDIS_SOCKET_DB`, `HASH_DRIVER` y los limites `CUENTA_*`, `ENTRY_CHECK_PER_MINUTE`,
   `LOGIN_IP_FAILURES_PER_MINUTE`, `PUBLIC_READ_PER_MINUTE`. Anotado para la auditoria de seguridad.
5. **En `.env` pero sin uso en el codigo de la app:** `BROADCAST_CONNECTION`, `HORIZON_ENABLED`,
   `PHP_CLI_SERVER_WORKERS`, `VITE_APP_NAME` (sobras de la plantilla). `SENTRY_LARAVEL_DSN` y
   `SENTRY_TRACES_SAMPLE_RATE` las lee el paquete de Sentry por dentro.

## Las 71 variables

| Variable | Donde se lee | C# | Nota |
|---|---|---|---|
| `APP_NAME` | app, cache, database, horizon, session | no | Arma los prefijos de cache y Redis (`claves-redis.md`) |
| `APP_ENV` | app | equiv | `ASPNETCORE_ENVIRONMENT` |
| `APP_KEY` | app | **si** | Ver punto 1 |
| `APP_DEBUG` | app | equiv | |
| `APP_TIMEZONE` | app | **si** | Offset de las fechas de la API (decision 2026-10-21) |
| `APP_URL` | app, filesystems, mail | **si** | URLs absolutas en respuestas y correos |
| `WEBAPP_URL` | app | **si** | Enlaces a la webapp en correos |
| `APP_DEEP_LINK_SCHEME` | app | **si** | `eventos://` en enlaces de correo |
| `APP_LOCALE` | app | equiv | |
| `APP_FALLBACK_LOCALE` | app | equiv | |
| `APP_FAKER_LOCALE` | app | no | Solo seeders |
| `APP_QR_SECRET` | app | **si** | Ver punto 2 |
| `APP_MAINTENANCE_DRIVER` | app | no | |
| `PHP_CLI_SERVER_WORKERS` | - | no | Sin uso |
| `BCRYPT_ROUNDS` | hashing | **si** | C# verifica contraseñas bcrypt de PHP (`$2y$`) |
| `LOG_CHANNEL` | logging | equiv | |
| `LOG_STACK` | logging | equiv | |
| `LOG_DEPRECATIONS_CHANNEL` | logging | no | |
| `LOG_LEVEL` | logging | equiv | |
| `DB_CONNECTION` | database, queue, telescope | equiv | MySQL |
| `DB_HOST` | database | **si** | Misma base durante la convivencia |
| `DB_PORT` | database | **si** | |
| `DB_DATABASE` | database | **si** | |
| `DB_USERNAME` | database | **si** | Idealmente un usuario propio de C# |
| `DB_PASSWORD` | database | **si** | |
| `SESSION_DRIVER` | session | no | Sesion web de Filament |
| `SESSION_LIFETIME` | session | no | |
| `SESSION_ENCRYPT` | session | no | |
| `SESSION_PATH` | session | no | |
| `SESSION_DOMAIN` | session | no | |
| `BROADCAST_CONNECTION` | - | no | Sin uso |
| `FILESYSTEM_DISK` | filesystems | **si** | Donde viven fotos y documentos |
| `QUEUE_CONNECTION` | queue | no | Hangfire tiene la suya |
| `CACHE_STORE` | cache | no | C# usa su prefijo `dotnet:` (M1.4.4) |
| `CACHE_PREFIX` | cache | **si** | Para leer o borrar claves de PHP mientras conviven |
| `HORIZON_ENABLED` | - | no | Sin uso |
| `TELESCOPE_ENABLED` | telescope | no | |
| `SENTRY_LARAVEL_DSN` | paquete Sentry | equiv | DSN propio del servicio C# |
| `SENTRY_TRACES_SAMPLE_RATE` | paquete Sentry | equiv | |
| `MEMCACHED_HOST` | cache | no | Sin uso real |
| `REDIS_CLIENT` | database | no | |
| `REDIS_HOST` | database | **si** | Redis compartido |
| `REDIS_PASSWORD` | database | **si** | |
| `REDIS_PORT` | database | **si** | |
| `MAIL_MAILER` | mail | equiv | |
| `MAIL_SCHEME` | mail | equiv | |
| `MAIL_HOST` | mail | **si** | Mientras C# mande correos |
| `MAIL_PORT` | mail | **si** | |
| `MAIL_USERNAME` | mail | **si** | |
| `MAIL_PASSWORD` | mail | **si** | |
| `MAIL_FROM_ADDRESS` | mail | **si** | |
| `MAIL_FROM_NAME` | mail | **si** | |
| `AWS_ACCESS_KEY_ID` | cache, filesystems, queue, services | no | Plantilla; el almacenamiento real es R2 |
| `AWS_SECRET_ACCESS_KEY` | cache, filesystems, queue, services | no | |
| `AWS_DEFAULT_REGION` | cache, filesystems, queue, services | no | |
| `AWS_BUCKET` | filesystems | no | |
| `AWS_USE_PATH_STYLE_ENDPOINT` | filesystems | no | |
| `CLOUDFLARE_R2_KEY` | filesystems | **si** | Fotos, documentos, exports |
| `CLOUDFLARE_R2_SECRET` | filesystems | **si** | |
| `CLOUDFLARE_R2_BUCKET` | filesystems | **si** | |
| `CLOUDFLARE_R2_ENDPOINT` | filesystems | **si** | |
| `CLOUDFLARE_R2_PUBLIC_URL` | filesystems | **si** | URLs publicas en las respuestas |
| `VITE_APP_NAME` | - | no | Sin uso |
| `SOCKET_SERVER_URL` | services | **si** hasta la fase 3 | Despues el hub es parte de C# |
| `SOCKET_INTERNAL_SECRET` | services | **si** hasta la fase 3 | Ver punto 3 |
| `VAPID_PUBLIC_KEY` | services | **si** | Web push: las suscripciones ya guardadas dependen de esta llave |
| `VAPID_PRIVATE_KEY` | services | **si** | |
| `VAPID_SUBJECT` | services | **si** | |
| `WEBAPP_INTERNAL_URLS` | services | **si** | Soltar el marco de la webapp (`InvalidationService`) |
| `WEBAPP_INTERNAL_SECRET` | services | **si** | Ver punto 3 |
| `TURNSTILE_SECRET_KEY` | services | **si** | Registro publico (hoy llaves de prueba, PENDIENTES §3) |
