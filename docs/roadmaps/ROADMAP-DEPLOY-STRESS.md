# ROADMAP — DEPLOY PRODUCCION + STRESS TEST — 0/24

> **Decision Kamilo 2026-08-01**: se reactiva el stress test (estaba DIFERIDO
> desde el pivote del 2026-07-08) y se define el alcance sin ambiguedad:
>
> **"Si vamos a hacer todo es como si ya fueramos a produccion. No quiero dejar
> cosas a medias, eso solo causa pendientes — 'mas adelante: ay, falto esto'."**
>
> Se monta el stack COMPLETO (dominio + HTTPS + Cloudflare + R2 + SMTP + webapp
> Next), se mide, y se destruyen los droplets el mismo dia.
>
> Ventana operativa de este frente. Al terminar: numeros reales de capacidad +
> una guia de deploy probada que sirve para el demo definitivo.

## Por que importa mas alla del numero

El montaje NO es desechable aunque los droplets si lo sean: es exactamente el
mismo trabajo que el DEPLOY DEMO (0/6). Salimos con el procedimiento probado y
documentado, no solo con una medicion.

---

## Insumos a conseguir (Kamilo) — 0/6

- [ ] Droplet **target**: 4 vCPU / 8 GB · Ubuntu 24.04 LTS · region **sao1** · llave SSH
- [ ] Droplet **generador de carga**: 4 vCPU / 8 GB · Ubuntu 24.04 LTS · **sao1** · llave SSH
- [ ] Dominio `.com` con nameservers apuntando a **Cloudflare** (**EN CURSO 2026-08-01**,
      propagacion ~24h segun el registrador)
- [ ] API token de Cloudflare (DNS + R2)
- [ ] Cuenta SMTP (Resend o Mailgun, plan free) + API key
- [ ] DSN de Sentry (plan free)

**Costo**: los 2 droplets ~$0.07/hora c/u → **~$3 USD el dia entero**. Dominio
~$12/año. Cloudflare, SMTP y Sentry en plan gratuito.

### DNS (los 3 al target)

```
api.tudominio.com      → IP target
app.tudominio.com      → IP target
socket.tudominio.com   → IP target
```

**Por que Ubuntu 24.04 y no la mas nueva**: PHP/nginx/Node estan solidos ahi y
tiene soporte hasta 2029. Una version recien salida es ruleta de paquetes justo
el dia en que se quiere medir, no depurar.

**Por que el generador es igual de grande**: simular miles de conexiones
Socket.IO consume CPU real. Con una maquina chica el cuello de botella es el
generador y se termina midiendo a el.

**Por que los dos en la misma region**: hoy se busca saturar el servidor, no
medir el cable. La latencia real 4G Colombia es otro test (TEST 5 del plan v2.1).

---

## D.0 — Bloqueantes que falsean la medicion — 0/2

> Cazados 2026-08-01 leyendo el codigo. Si no se arreglan ANTES, el numero
> que salga no significa nada.

- [ ] **D.0.1 `eventos-socket/ecosystem.config.js`**: hoy `instances: 1` +
      `exec_mode: 'fork'` + `max_memory_restart: '256M'`. Bajo carga el proceso
      cruza los 256 MB y **PM2 lo reinicia a mitad del test** — se mide una
      caida, no una capacidad. Subir el techo y pasar a cluster mode
      (`instances: 'max'`); el adaptador Redis ya esta implementado, asi que
      compartir estado entre procesos es seguro.
- [ ] **D.0.2 `tests/load/tokens.json` tiene ~10 usuarios** y `helpers.js`
      reparte tokens en rueda (`tokens[vuIndex % tokens.length]`): 5000 VUs
      golpearian las mismas 10 cuentas. Los rate limits por asistente y los
      caches por persona darian un resultado inventado. Sembrar miles de
      asistentes reales y re-generar con `setup-tokens.php`.

---

## D.1 — Target: sistema operativo y servicios — 0/6

- [ ] D.1.1 Usuario no-root + SSH endurecido + UFW (22, 80, 443) + swap 2 GB
- [ ] D.1.2 nginx + PHP 8.2-FPM (+ extensiones: mbstring, xml, curl, zip, gd,
      intl, redis, mysql) — OJO: en Windows se instalaba con
      `--ignore-platform-req`; en Linux van de verdad
- [ ] D.1.3 MySQL 8 (usuario dedicado, NO root) + Redis con password
- [ ] D.1.4 Node 20 + pnpm + PM2
- [ ] D.1.5 Certbot: certificados para los 3 subdominios
- [ ] D.1.6 Cloudflare: DNS proxied + R2 bucket `eventos-prod` + token

---

## D.2 — Target: la aplicacion — 0/6

- [ ] D.2.1 Backend clonado (`feature/magic-link-auth`) + `composer install --no-dev`
      + `.env` desde `.env.production.example` + `key:generate` +
      `APP_QR_SECRET` + `SOCKET_INTERNAL_SECRET`
- [ ] D.2.2 `php artisan security:check` en verde ANTES de abrir nada
- [ ] D.2.3 Migraciones + seeders + un evento demo curado (el Summit)
- [ ] D.2.4 Socket server compilado + PM2 (con D.0.1 aplicado) + systemd resurrect
- [ ] D.2.5 Webapp Next compilada y servida con PM2 (`app.tudominio.com`)
- [ ] D.2.6 Horizon (colas Redis) + supervisor

### Gotchas de deploy ya conocidos (no re-descubrir)

- **`APP_URL` debe ser el dominio real**: las imagenes de los correos se guardan
  con URL absoluta (INT.9c). Si queda mal, los correos viejos apuntan al dominio
  anterior.
- **`KIOSK_URL`** es config nueva en `config/services` (INT.12), default dev
  `localhost:5173` — hay que apuntarla.
- **`SESSION_DRIVER=redis`** ya es el canon (y es la razon por la que el frente
  de Seguridad necesita tabla propia de sesiones).
- El default `127.0.0.1:3001` del socket era un fix de **Windows/IPv6** — en
  Linux revisar que la URL interna sea la correcta.
- `QUEUE_CONNECTION` en dev esta en `sync`; en produccion va `redis`.

---

## D.3 — Generador de carga — 0/2

- [ ] D.3.1 k6 + Node + artillery + `artillery-engine-socketio-v3`
- [ ] D.3.2 `tokens.json` re-generado apuntando al target (no a `.test`) +
      limites del sistema subidos (`ulimit -n`, rango de puertos efimeros) —
      sin esto el generador se queda sin sockets antes que el servidor

---

## D.4 — Las mediciones — 0/5

> Cada una con su numero anotado. El objetivo no es "pasar", es **saber**.

- [ ] D.4.1 Baseline: 50 VUs, sistema en reposo — latencias p50/p95 por endpoint
- [ ] D.4.2 HTTP escalonado (`stress-full.js`): 500 → 1000 → 2500 → 5000 VUs.
      Anotar en que punto p95 se degrada y donde aparecen errores
- [ ] D.4.3 Sockets (`stress-sockets.yml`): conexiones simultaneas maximas,
      latencia de mensaje, memoria por conexion
- [ ] D.4.4 Mixto: HTTP + sockets + `stress-admin.js` a la vez (el admin
      trabajando mientras el evento corre — es el escenario real)
- [ ] D.4.5 Break point: escalar hasta romper y anotar QUE rompe primero
      (PHP-FPM, MySQL, Redis, el socket, la RAM)

---

## D.5 — Cierre — 0/3

- [ ] D.5.1 Informe con los numeros reales: **cuanta gente aguanta un droplet**
      — es el dato que se le cotiza a un cliente
- [ ] D.5.2 Guia de deploy escrita desde lo que REALMENTE se hizo (no desde el
      plan) → alimenta DEPLOY DEMO 0/6
- [ ] D.5.3 **Destruir los dos droplets** y confirmar que dejaron de facturar

---

## Lo que este test NO responde

Un solo droplet no dice nada sobre 10.000 personas. Para eso hace falta la
arquitectura de `docs/infra/DISPONIBILIDAD-HA.md` (2 droplets + MySQL y Redis
manejados + balanceador Cloudflare, ~$150-200/mes). Lo de hoy responde otra
pregunta, mas util ahora mismo: **¿cuanta gente aguanta la maquina que
efectivamente le vendo a un cliente?**
