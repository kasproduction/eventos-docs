# Arquitectura de EventOS: PHP y C# conviviendo

> **v1, 2026-10-25** (M1.5.9 del roadmap de la migracion). Estado actual de la convivencia mientras C# reemplaza a
> PHP ruta por ruta. Se actualiza en la retro de cada fase (regla 25); la version final (M5.3.2) es este mismo archivo,
> ya sin PHP ni Node. Lo que se genera desde el codigo no se copia aca (regla 28): quien atiende cada ruta esta en
> [`contratos/rutas-duenos.md`](contratos/rutas-duenos.md), las claves de Redis en
> [`contratos/claves-redis.md`](contratos/claves-redis.md) y las variables en
> [`contratos/variables.md`](contratos/variables.md).

## Como llega un pedido

```
                 Internet
                    │
              ┌─────┴─────┐
              │ Cloudflare │   (en produccion; IP real por cloudflare-realip.conf)
              └─────┬─────┘
                    │
        ┌───────────┴────────────────────────────────────────────┐
        │  Nginx: la unica puerta (80/443)                        │
        │                                                         │
        │  /api/*  ── mapa $dueno_api por metodo y ruta exacta ──┐ │
        │     php    ─► PHP-FPM (Laravel)                        │ │
        │     dotnet ─► C# en 127.0.0.1:8080 (@dotnet)           │ │
        │  webapp  ─► Next (pm2)                                  │
        │  socket  ─► Node / Socket.IO (pm2)                      │
        └───────────────────┬─────────────────────────────────────┘
                            │
        MySQL (una sola base) · Redis (bases 0, 1, 2) · Cloudflare R2
```

- **Nginx decide por metodo y ruta exacta**, no por prefijo (decision 2026-10-25: Nginx, no YARP). El mapa lo
  generan `eventos-api-dotnet/scripts/cruzar.sh` y `descruzar.sh` desde `proxy/rutas-csharp.tsv`; nadie lo edita a
  mano. Una ruta GET de C# tambien atiende HEAD, como en Laravel.
- **Cada respuesta dice quien contesto:** `X-Backend: php` o `X-Backend: dotnet`. El log de Nginx (`eventos_rutas`)
  guarda eso por pedido y `scripts/panel-rutas.sh` arma el panel por ruta y por backend (p95, errores, req/s).
- **C# no se ve desde afuera:** escucha solo en `127.0.0.1:8080`, igual que PHP-FPM detras de su socket.
- **Hoy en produccion todo sigue en PHP:** el `include /etc/nginx/eventos/*.conf` de `infra/deploy.sh` esta dormido
  hasta desplegar C#. Las rutas de la tabla (health, version, `/auth/me`) son las primeras en cruzar.

## Lo que comparten

| Pieza | Como se comparte | Regla |
|---|---|---|
| **MySQL** | Una sola base. El esquema lo manda Laravel (migraciones de PHP); C# lo lee con EF Core (`scaffold.ps1`) | Nada de migraciones de EF antes de la fase 5. Fechas en hora de Bogota |
| **Redis base 2** (sin prefijo) | Node, PHP y C# leen y escriben lo mismo en JSON: fichas del socket (`socketauth:*`), chat, presencia | Mismas claves y misma vida |
| **Redis base 0 y 1** (prefijo de Laravel) | Solo PHP. C# puede borrar claves por nombre, nunca leerlas (van serializadas en PHP) | Ver `claves-redis.md` |
| **Redis `dotnet:`** | Solo C#: limites de pedidos, cache por evento y su version | PHP no las lee |
| **Canal `eventos:invalidate`** | PHP publica `{eventId, entity, op}` en cada cambio; C# sube la version de su cache | Un aviso perdido lo cubre la vida del valor |
| **Tokens de Sanctum** | `personal_access_tokens`: PHP los emite; C# los valida igual (sha256, 7 dias, staff rechazado, operador acotado) | El login sigue en PHP hasta portar `/auth/*` |
| **`APP_KEY`** | Las tres columnas cifradas (2FA, SMTP por organizacion, sesiones del staff) las leen y escriben los dos | `Contracts/Cifrado/CifradoDeLaravel` |

## Lo que sigue en un solo lado

- **Colas y tareas programadas:** Horizon (PHP). El Worker de C# (Hangfire) llega con los modulos que lo necesiten.
- **Tiempo real:** Node / Socket.IO. Pasa a SignalR en la fase 3; mientras tanto C# escribe en Redis lo que Node lee.
- **Panel del staff:** Filament (PHP) y el Data Center (SPA con cookie de sesion en `/api/v1/data-center/*`, D.8).
  El admin nuevo es la fase 2; el Data Center pasa a bearer en M2.7.2.
- **Paginas servidas por PHP** (Mission Control, display, chat-monitor, control de asistencia, Event Pulse): siguen en
  Laravel hasta M4e.7.

## Como se protege cada evento en C#

Filtro por evento de raiz (regla 26): toda ruta del evento termina en `.DelEvento()`; sin evento fijado, las 86 tablas
del evento no devuelven nada; guardar algo de otro evento responde 404. Cruzar eventos a proposito solo con
`CruzandoEventos(CruceDeEventos.X)`, una lista cerrada. Detalle en `BUENAS-PRACTICAS-MIGRACION.md`, regla 26.

## La API del admin nuevo (M2.1.4)

El admin nuevo (Next en `eventos-web` bajo `/admin`) habla con una API propia de C#. No espeja a PHP: es contrato nuevo.

- **Donde vive:** todo bajo `/api/admin/v1`. Nginx manda el prefijo entero a C# (`proxy/nginx-api.conf`), sin la tabla
  de rutas cruzadas. Las 37 rutas de PHP en `/api/v1/admin` (Mission Control, encuestas y juegos en vivo) siguen en PHP
  hasta la fase 4.
- **El evento va en la ruta:** `/api/admin/v1/events/{eventId}/...` con `.DelEvento()`, como toda ruta del evento. Lo que
  cruza eventos a proposito tiene su motivo con nombre en `CruceDeEventos`: `GET /api/admin/v1/events` (super_admin,
  `SuperAdmin`) y `GET /api/admin/v1/organizations/{organizationId}/events` (`AdminDeOrganizacion`).
- **Misma forma que la API publica:** `snake_case`, errores 422 de Laravel, paginacion de Laravel. Una accion con
  efectos es un `POST` propio (`/announcements/{id}/publish`): un solo camino por accion.
- **Limite propio:** `admin`, 1.200 por minuto por persona del staff (2.400 en local, `Puerta:AdminPorMinuto`), aparte
  del de asistentes (`api`, 300). En agosto (E8) un organizador activo se pasaba del limite por persona.
- **Contrato generado:** documento OpenAPI aparte (`/openapi/admin.json`, copiado en `eventos-api-dotnet/openapi/admin.json`).
  `ApiDelAdminTest` revisa que cada documento lleve solo sus rutas, que toda ruta del admin tenga resumen y descripcion
  y que el archivo este al dia. De ese archivo salen los tipos de Next: `pnpm tipos:admin` en `eventos-web` escribe
  `src/lib/admin/api-tipos.ts`, que se commitea (Next compila sin el servidor de C#; `--revisar` falla si quedo viejo).

## Redis en el equipo de desarrollo (Windows)

El servidor usa Redis 7; Laragon trae Redis 5.0.14 (sin `KEEPTTL` y otras de la 6 y la 7). Decision Kamilo 2026-10-25:
en Windows, Redis 7 por apt dentro de WSL2 (ni Docker Desktop ni el Redis del MSI por Tailscale), escuchando en
localhost:6379 como el de Laragon, asi PHP, C# y las pruebas no cambian de configuracion.

1. Instalado: `wsl --install -d Ubuntu-24.04 --no-launch` y `wsl -d Ubuntu-24.04 -u root -- apt-get install redis-server`
   (7.0.15, `bind 127.0.0.1 -::1`, servicio de systemd `redis-server` habilitado).
2. Hecho 2026-10-26 (pide administrador): apagado el servicio de Windows `Redis` de Laragon, que ocupaba el 6379, en
   una PowerShell de administrador: `Stop-Service Redis; Set-Service Redis -StartupType Disabled`. Si Laragon vuelve
   a arrancar su Redis (su boton), regresa el 5.
   Ojo: WSL publica el puerto en Windows cuando Redis arranca. Si en ese momento el `127.0.0.1:6379` esta ocupado,
   queda publicado solo en IPv6 (`::1`) y desde `127.0.0.1` "rechaza la conexion". Arreglo: `wsl --shutdown` y volver
   a encender (`Start-ScheduledTask "EventOS Redis 7 (WSL)"`).
3. Hecho 2026-10-25: Ubuntu se apaga cuando no hay ninguna terminal abierta, y con el Redis. Lo deja encendido al
   iniciar sesion una tarea programada del usuario (no pide administrador):
   `Register-ScheduledTask -TaskName "EventOS Redis 7 (WSL)" -Action (New-ScheduledTaskAction -Execute conhost.exe -Argument "--headless wsl.exe -d Ubuntu-24.04 -u root --exec sleep infinity") -Trigger (New-ScheduledTaskTrigger -AtLogOn -User $env:USERNAME) -Settings (New-ScheduledTaskSettingsSet -ExecutionTimeLimit 0)`.
   Quitarla: `Unregister-ScheduledTask "EventOS Redis 7 (WSL)"`. Sin la tarea, basta abrir `wsl -d Ubuntu-24.04` antes de trabajar.
4. Comprobado 2026-10-26 desde Windows: `C:/laragon/bin/redis/redis-x64-5.0.14.1/redis-cli.exe INFO server` dice `redis_version:7.0.15`.

## Como se cruza una ruta (criterio de la regla 20)

1. Su snapshot pasa contra C# (`scripts/contratos-contra-csharp.sh`) y su OpenAPI coincide con el de PHP
   (`OpenApiContraPhpTest`).
2. Orden explicito en sus listas; si escribe, prueba cruzada (escribe C#, lee PHP) y prueba de cruce entre eventos.
3. Medicion PHP contra C# anotada (M1.5.5) y la ruta probada detras del Nginx real del MSI (regla 29).
4. `scripts/cruzar.sh "METODO /ruta" --docs ... --nginx`: si escribe, antes respalda la base; si algo sale mal,
   `scripts/descruzar.sh` la devuelve a PHP en el acto.

## Historial

| Version | Fecha | Que cambio |
|---|---|---|
| v3 | 2026-10-25 | Redis 7 en el equipo de desarrollo por WSL2 |
| v2 | 2026-10-25 | La API del admin nuevo (M2.1.4): prefijo propio, evento en la ruta, limite propio, OpenAPI y tipos generados |
| v1 | 2026-10-25 | Primera version, al cerrar el bloque 1.5: proxy en Nginx, primeras rutas de C# en la tabla, nada cruzado en produccion |
