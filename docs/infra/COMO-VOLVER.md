# Cómo volver a levantar todo

> Escrito el 2026-08-02 al cerrar la primera sesión de producción real.
> Responde una sola pregunta: **si destruyo los droplets, ¿cuánto cuesta volver?**

---

## Lo primero: apagar NO detiene el cobro

DigitalOcean cobra igual con el droplet apagado, porque el disco y la IP
siguen reservados. **Para dejar de pagar hay que destruirlo.**

| | Cobro |
|---|---|
| Droplet encendido | completo |
| Droplet **apagado** | **completo — no ahorra nada** |
| Droplet destruido | cero |
| Snapshot guardado | ~$0,06/GB/mes → **~$0,40/mes** con ~6 GB usados |

## La forma recomendada de cerrar

1. **Apagar** el droplet (el snapshot queda más consistente con la máquina detenida)
2. **Snapshots → Take Snapshot** — guarda sistema, configuración, base de datos, todo
3. **Destroy** el droplet

Volver es: **Snapshot → Create Droplet from snapshot**. Arranca con todo
adentro, en minutos. Solo hay que reapuntar el DNS a la IP nueva.

Y si no hay snapshot tampoco se pierde nada: `deploy.sh` remonta desde cero
en ~15 minutos.

---

## Qué NO se pierde nunca

**Todo el código está en git y pusheado.** Los droplets son desechables por
diseño; lo único que vive en ellos es el montaje.

Fuera de los droplets, y que sobrevive a todo:

| | Dónde |
|---|---|
| Código (4 repos) | GitHub |
| Archivos subidos | Cloudflare R2 · `cdn.killjoy.pro` |
| Dominio y DNS | Cloudflare |
| Correo | Resend, dominio verificado |
| Todo lo aprendido | `DIAGNOSTICO-2026-08-02.md` |

Lo único que se pierde al destruir sin snapshot: la **base de datos** (evento
demo + 5.050 asistentes de prueba) y las credenciales generadas
(`/root/CREDENCIALES.txt`). Ambas se regeneran con los seeders.

---

## Volver desde cero — la secuencia

```bash
# 1. Crear droplet: 4 vCPU / 8 GB · Ubuntu 24.04 LTS · con llave SSH
# 2. Apuntar api / app / socket al nuevo IP en Cloudflare (nube GRIS)
# 3. Montar el sistema
scp docs/infra/deploy.sh root@IP:/root/
ssh root@IP "DOMINIO=killjoy.pro bash /root/deploy.sh"

# 4. Subir el código (los repos son privados)
cd eventos-backend && git archive --format=tar HEAD | ssh root@IP "tar -xf - -C /var/www/backend"
cd eventos-socket  && git archive --format=tar HEAD | ssh root@IP "tar -xf - -C /var/www/socket"
cd eventos-web     && git archive --format=tar HEAD | ssh root@IP "tar -xf - -C /var/www/web"
```

Después, en el servidor, los pasos que imprime `deploy.sh` al terminar.
**Los datos del demo:**

```bash
php artisan db:seed --force                                    # base
php artisan db:seed --class=DemoMediaSeeder --force            # imágenes a R2
php artisan db:seed --class=DemoCompletoSeeder --force         # slides, FAQ, premios, Q&A
php artisan db:seed --class=LoadTestAttendeeSeeder --force     # 5.000 asistentes (solo para pruebas)
```

---

## Variables que cuestan una hora cada una si se olvidan

Todas verificadas rompiendo algo real el 2026-08-02:

| Variable | Si falta |
|---|---|
| `APP_URL` con el dominio real | las imágenes de correos apuntan al dominio viejo |
| `WEBAPP_URL` | el magic link apunta a la API y **nadie puede entrar** |
| `LARAVEL_API_URL` **con `/api/v1`** | ninguna conexión de socket autentica |
| `CLOUDFLARE_R2_PUBLIC_URL` | las URLs de archivos salen rotas |
| `QUEUE_CONNECTION=redis` | en desarrollo es `sync` y las colas no corren |
| `SANCTUM_TOKEN_EXPIRATION` | (ya tiene cast a int; antes rompía el login con contraseña) |

Y dos pasos que no son variables pero rompen todo igual:

- **`pnpm run build` en el backend** — sin eso el panel Filament entero da
  "Error del servidor" y el Data Center no abre
- **`worker_connections 16384`** en nginx — el default de Ubuntu (768) capa en
  ~1.536 WebSockets sin importar el hardware

---

## Los accesos del demo

| | |
|---|---|
| App | `app.killjoy.pro` · kamilo@killjoy.pro / Summit2026 |
| Admin | `api.killjoy.pro/admin` · mismas credenciales (rol super_admin) |
| Data Center | `api.killjoy.pro/data-center` |
| Event Pulse | `api.killjoy.pro/event-pulse/?slug=summit-empresarial-2026&token=<pulse_token>` |
| Mission Control | `api.killjoy.pro/monitor/{sesion}?token=<hmac>` |

El token del Pulse vive en `events.pulse_token`. El del Mission Control se
calcula: `hash_hmac('sha256', "{sessionId}|{eventId}", config('app.qr_secret'))`.

---

## Pendiente al cerrar

- **Rotar las credenciales de R2** — quedaron escritas en la conversación del
  2026-08-02. R2 no se destruye como los droplets.
- **Cloudflare en gris.** Para exponerlo a alguien, pasarlo a naranja: hoy el
  servidor está desnudo (ya se registraron 125 intentos de bots buscando
  `/.env` desde 7 IPs).
