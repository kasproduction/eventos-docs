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

## Volver al NIVEL 1 (6 nodos + administrados) — desde 2026-08-18

Lo de abajo levanta el **droplet demo** (una maquina, ROL=todo). Para el combo
vendible el procedimiento completo esta en `STACK-PRODUCCION.md` §8 (como se
monto) y §11 (modo registro / modo evento). En corto, todo desde la terminal
con `doctl` (instalado con `winget install DigitalOcean.Doctl`; token en
`doctl auth init`):

```
# 0. Ver que hay encendido
doctl compute droplet list --tag-name eventos-n1
# 1. Administrados (si no existen): MySQL 8.4 x2 y Valkey x2 en la VPC nyc1
doctl databases create eventos-n1-mysql --engine mysql --version 8.4 --size db-amd-1vcpu-2gb --num-nodes 2 --region nyc1 --private-network-uuid <vpc> --tag eventos-n1
doctl databases create eventos-n1-redis --engine valkey --size db-amd-1vcpu-2gb --num-nodes 2 --region nyc1 --private-network-uuid <vpc> --tag eventos-n1
doctl databases firewalls append <id> --rule tag:eventos-n1      # los dos
# 2. Droplets por rol (desde snapshot api/web/sock si existen; si no, ubuntu-24-04-x64 + deploy.sh ROL=)
doctl compute droplet create api-1 api-2 --image <snapshot|ubuntu-24-04-x64> --size s-4vcpu-8gb --region nyc1 --vpc-uuid <vpc> --ssh-keys <id> --tag-names eventos-n1,rol-api
#    idem web-1 web-2 (s-2vcpu-4gb) y sock-1 sock-2 (s-2vcpu-4gb)
# 3. En cada nodo nuevo desde cero: scp deploy.sh origin.pem origin.key do-ca.crt root@IP:/root/
#    ssh root@IP "DOMINIO=killjoy.pro ROL=api bash /root/deploy.sh"   (web | sockets)
#    codigo por rsync desde un nodo ya construido, .env por rol (bloque NIVEL 1 al final de deploy.sh)
# 4. Balanceador: Cloudflare LB (comprar origenes) o nginx round-robin (lb-nginx.conf) en un droplet aparte
# 5. DNS api/app/socket naranja -> LB. Prueba de humo: /up, login, una sesion, la 301 en Chrome.
# 6. Apagar un nodo a proposito: doctl compute droplet-action power-off <id>   (y power-on despues)
# 7. Al cerrar: snapshot de api-1/web-1/sock-1 -> delete los droplets -> databases delete -> rotar tokens
```

**Regla:** los administrados son la verdad y NUNCA se apagan mientras haya un
evento en registro; el droplet de registro (ROL=todo) apunta a ellos, no a un
MySQL local (STACK-PRODUCCION §11).

## Volver desde cero — la secuencia (droplet DEMO, una maquina)

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
