# Demo local de EventOS (sin desplegar)

Scripts para levantar TODO el stack en esta maquina y mostrarlo a alguien.
Doble clic y listo. Verificado el 2026-09-07 con los cinco repos en su ultimo commit.

| Script | Para que |
|---|---|
| `DEMO-BUILD.bat` | Compila todo en modo produccion. Correr UNA vez, y otra vez cada vez que cambie codigo. Tarda 2-5 min (el lento es `next build`). |
| `DEMO-START-PROD.bat` | Arranque rapido para la demo: Next compilado, socket compilado, kiosko estatico, Expo con bundle minificado, backend con caches de artisan. Requiere el BUILD previo. |
| `DEMO-START.bat` | Arranque en modo desarrollo (HMR, sin build). Mas lento en cada pantalla, pero no requiere compilar. |
| `DEMO-STOP.bat` | Apaga los cuatro servicios (por puerto) y suelta las caches del backend para volver a desarrollar. Laragon se deja corriendo. |

## Que levanta

| Servicio | URL | Modo dev | Modo prod |
|---|---|---|---|
| Admin Filament | http://eventos-backend.test/admin | Apache (Laragon) | Apache + `artisan optimize` |
| Webapp asistente | http://localhost:3000 | `pnpm dev` | `pnpm start` (next start) |
| Kiosko check-in | http://localhost:5173 | `vite` | `vite preview` de `dist/` |
| Event Pulse | http://eventos-backend.test/event-pulse/?slug=...&token=... | Apache | Apache |
| Socket | ws://localhost:3001 | `nodemon` + ts-node | `node dist/index.js` |
| App movil | QR en la ventana "EventOS Expo" | `expo start --lan` | `expo start --lan --no-dev --minify` |
| Mailpit | http://localhost:8025 | Laragon | Laragon |

El token de Event Pulse se lee de MySQL (`events.pulse_token` del slug `summit-empresarial-2026`)
y la pestana se abre sola. Si el evento no tiene token, generarlo en Admin > Event Pulse.

## Sembrar el evento demo de 5 dias (agenda fresca desde HOY)

```
cd C:\laragon\www\eventos-backend
C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe artisan db:seed --class=Demo5DiasSeeder --force
```

Re-ejecutable. Deja el evento `summit-empresarial-2026` de hoy a hoy+4 (08:00 a 19:00),
50 sesiones en 5 dias con salon, speakers y track, una charla EN VIVO ahora en cada
salon (si la hora cae en un hueco corre la siguiente del Auditorio), 80% de asistentes
con check-in del dia, gente adentro de los salones para los kioskos, documentos y
juegos draft re-enlazados, y corre DemoCompletoSeeder (streams, portadas, FAQ, premios).
Al final imprime las URLs de los kioskos:

- **Kiosko de salon** (`mode=room`): usa el `auth_token` del totem (tabla `room_totems`).
  Los START abren solos el del Auditorio Principal.
- **Kiosko lobby** (check-in del evento): `?event_id=1&token=<Sanctum>`. El seeder crea
  el token `kiosk-demo` de `admin@eventos.test` y lo imprime; cambia en cada corrida.
  Si lo perdiste: vuelve a correr el seeder (no duplica nada).

Correrlo el mismo dia de la demo (o la manana anterior): "hoy" es el dia 1.

## App movil (Expo Go)

- Celular y PC en el mismo WiFi. La app apunta a `http://192.168.50.142/api/v1`
  (`eventos-app/.env`); Apache ya tiene un vhost para esa IP.
- Si la IP de la PC cambia: actualizar `LAN_IP` en los .bat, el `.env` de la app
  y el vhost `auto.eventos-backend.test.conf` de Laragon.
- Celular en otra red: `set EXPO_MODE=tunnel` antes de correr el .bat (usa ngrok).

## Detalles que conviene saber

- Los .bat son idempotentes: si un puerto ya esta ocupado, ese servicio no se toca.
- Windows Terminal no expone titulos de ventana, por eso el STOP busca por puerto.
- `DEMO-STOP.bat` borra `eventos-web\.next\dev` si la webapp estaba en modo dev
  (un kill forzado a `next dev` deja la cache a medias). El primer `pnpm dev`
  despues tarda un poco mas. En modo prod no aplica.
- `DEMO-STOP.bat` corre `artisan optimize:clear`: ademas de config/rutas/vistas,
  limpia el cache de aplicacion (Redis, prefijo `eventos_`).
- El kiosko en `vite preview --host` tambien queda accesible desde la LAN en
  http://192.168.50.142:5173, pero su `.env` apunta a `eventos-backend.test`,
  que una tablet no resuelve. Para kiosko en tablet: cambiar `VITE_API_URL` a la IP
  y recompilar.
