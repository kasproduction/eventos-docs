#!/bin/bash
#
# EventOS — despliegue completo sobre Ubuntu 24.04 limpio
#
# Escrito el 2026-08-02 desde lo que REALMENTE se hizo en el primer montaje
# (que tomo ~3 horas descubriendo). Con esto son ~15 minutos.
#
# Uso:
#   scp deploy.sh root@IP:/root/
#   ssh root@IP "DOMINIO=midominio.com bash /root/deploy.sh"
#
# Variables (todas opcionales salvo DOMINIO):
#   DOMINIO           midominio.com          (obligatorio)
#   EMAIL_SSL         para Let's Encrypt
#   RAMA_BACKEND      feature/magic-link-auth
#
# NO instala el codigo: los repos son privados. Al final imprime que falta.

set -euo pipefail

DOMINIO="${DOMINIO:?Falta DOMINIO=midominio.com}"
EMAIL_SSL="${EMAIL_SSL:-admin@$DOMINIO}"
API="api.$DOMINIO"
APP="app.$DOMINIO"
SOCKET="socket.$DOMINIO"

echo "══ EventOS · despliegue en $DOMINIO ══"
echo

# ─────────────────────────────────────────────────────────────────────────
# 1. Sistema
# ─────────────────────────────────────────────────────────────────────────
echo "[1/7] Usuario, swap y cortafuegos..."
export DEBIAN_FRONTEND=noninteractive

id eventos >/dev/null 2>&1 || adduser --disabled-password --gecos "" eventos
usermod -aG sudo eventos
mkdir -p /home/eventos/.ssh
[ -f /root/.ssh/authorized_keys ] && cp /root/.ssh/authorized_keys /home/eventos/.ssh/
chown -R eventos:eventos /home/eventos/.ssh && chmod 700 /home/eventos/.ssh

# Swap: el build de Next.js pide mas RAM de la que parece
if [ ! -f /swapfile ]; then
  fallocate -l 2G /swapfile && chmod 600 /swapfile && mkswap /swapfile >/dev/null && swapon /swapfile
  grep -q swapfile /etc/fstab || echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi

ufw allow OpenSSH >/dev/null; ufw allow 80/tcp >/dev/null; ufw allow 443/tcp >/dev/null
ufw --force enable >/dev/null

# ─────────────────────────────────────────────────────────────────────────
# 2. Paquetes
# ─────────────────────────────────────────────────────────────────────────
echo "[2/7] nginx, PHP 8.3, MySQL, Redis, Node 20..."
apt-get update -qq
apt-get install -y -qq nginx mysql-server redis-server git unzip curl jq \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-redis php8.3-mbstring php8.3-xml \
  php8.3-curl php8.3-zip php8.3-gd php8.3-intl php8.3-bcmath \
  certbot python3-certbot-nginx >/dev/null

curl -fsSL https://deb.nodesource.com/setup_20.x | bash - >/dev/null 2>&1
apt-get install -y -qq nodejs >/dev/null
npm install -g pnpm pm2 --silent >/dev/null 2>&1
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer >/dev/null 2>&1

# ─────────────────────────────────────────────────────────────────────────
# 3. Base de datos y Redis
# ─────────────────────────────────────────────────────────────────────────
echo "[3/7] MySQL y Redis con credenciales propias..."
DBPASS=$(openssl rand -base64 24 | tr -d '/+=' | head -c 30)
REDISPASS=$(openssl rand -base64 24 | tr -d '/+=' | head -c 30)

mysql -e "CREATE DATABASE IF NOT EXISTS eventos_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'eventos_user'@'localhost' IDENTIFIED BY '${DBPASS}';"
# ALTER y no solo CREATE IF NOT EXISTS: si el usuario ya existia, CREATE lo
# ignora en silencio y el archivo de credenciales queda mintiendo.
mysql -e "ALTER USER 'eventos_user'@'localhost' IDENTIFIED BY '${DBPASS}';"
mysql -e "GRANT ALL PRIVILEGES ON eventos_db.* TO 'eventos_user'@'localhost'; FLUSH PRIVILEGES;"

sed -i '/^requirepass /d; /^# *requirepass /d' /etc/redis/redis.conf
echo "requirepass ${REDISPASS}" >> /etc/redis/redis.conf
grep -q '^maxclients' /etc/redis/redis.conf || echo 'maxclients 20000' >> /etc/redis/redis.conf
systemctl restart redis-server

printf 'MySQL  db=eventos_db  user=eventos_user  pass=%s\nRedis  pass=%s\n' "$DBPASS" "$REDISPASS" > /root/CREDENCIALES.txt
chmod 600 /root/CREDENCIALES.txt

# ─────────────────────────────────────────────────────────────────────────
# 4. Limites del sistema — sin esto se mide un techo falso
# ─────────────────────────────────────────────────────────────────────────
echo "[4/7] Limites del sistema..."
cat > /etc/sysctl.d/99-eventos.conf <<'EOF'
net.core.somaxconn = 65535
net.core.netdev_max_backlog = 65535
net.ipv4.tcp_max_syn_backlog = 65535
net.ipv4.ip_local_port_range = 10000 65535
net.ipv4.tcp_tw_reuse = 1
net.ipv4.tcp_fin_timeout = 15
fs.file-max = 2097152
EOF
sysctl -p /etc/sysctl.d/99-eventos.conf >/dev/null
printf '* soft nofile 65535\n* hard nofile 65535\nroot soft nofile 65535\nroot hard nofile 65535\n' > /etc/security/limits.d/99-eventos.conf
for svc in nginx php8.3-fpm; do
  mkdir -p /etc/systemd/system/$svc.service.d
  printf '[Service]\nLimitNOFILE=65535\n' > /etc/systemd/system/$svc.service.d/limits.conf
done

# PHP-FPM dimensionado a 8 GB
P=/etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/^pm = .*/pm = dynamic/;s/^pm.max_children = .*/pm.max_children = 60/;s/^pm.start_servers = .*/pm.start_servers = 12/;s/^pm.min_spare_servers = .*/pm.min_spare_servers = 8/;s/^pm.max_spare_servers = .*/pm.max_spare_servers = 24/' $P
grep -q '^pm.max_requests' $P && sed -i 's/^pm.max_requests = .*/pm.max_requests = 500/' $P || echo 'pm.max_requests = 500' >> $P

# OPcache + JIT: +25% de capacidad medido, gratis
#
# TRAMPA DE DESPLIEGUE (verificada el 2026-08-02): `validate_timestamps=0` hace
# que PHP-FPM NUNCA vuelva a mirar los archivos. Subir codigo nuevo, o correr
# config:cache / route:cache, NO TIENE NINGUN EFECTO hasta recargar FPM:
#
#     systemctl reload php8.3-fpm
#
# Pasa en silencio: el servidor sigue respondiendo 200 con la version vieja.
# Se descubrio regenerando la cache de rutas y viendo 404 en rutas que
# `route:list` sí mostraba.
cat > /etc/php/8.3/fpm/conf.d/99-eventos-opcache.ini <<'EOF'
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=32
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.jit=tracing
opcache.jit_buffer_size=128M
EOF

printf '[mysqld]\nmax_connections = 500\n' > /etc/mysql/mysql.conf.d/99-eventos.cnf

# CRITICO: el default de Ubuntu (768) capa en ~1.536 WebSockets sin importar
# el hardware — cada uno con proxy consume DOS conexiones de nginx.
sed -i 's/worker_connections .*/worker_connections 16384;/' /etc/nginx/nginx.conf
grep -q 'worker_rlimit_nofile' /etc/nginx/nginx.conf || sed -i '/^worker_processes/a worker_rlimit_nofile 65535;' /etc/nginx/nginx.conf

systemctl daemon-reload

# ─────────────────────────────────────────────────────────────────────────
# 5. nginx
# ─────────────────────────────────────────────────────────────────────────
echo "[5/7] nginx para $API, $APP y $SOCKET..."
mkdir -p /var/www/backend /var/www/socket /var/www/web

cat > /etc/nginx/sites-available/$API <<EOF
server {
    listen 80;
    server_name $API;
    root /var/www/backend/public;
    index index.php index.html;
    charset utf-8;
    client_max_body_size 50M;
    add_header X-Content-Type-Options "nosniff";

    # `index.html` y `\$uri/index.html` son OBLIGATORIOS: las SPA del backend
    # (event-pulse, mission-control) se sirven como carpetas con index.html.
    # Solo con `index index.php` nginx intenta listar el directorio y responde
    # 403 — los tableros no cargan (verificado 2026-08-02).
    location / {
        index index.php index.html;
        try_files \$uri \$uri/ \$uri/index.html /index.php?\$query_string;
    }
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php\$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 120;
    }
    # El .env vive un nivel ARRIBA de la raiz publica, asi que ya es
    # inalcanzable. Esta regla es la segunda reja.
    location ~ /\.(?!well-known).* { deny all; }
}
EOF

cat > /etc/nginx/sites-available/$APP <<EOF
server {
    listen 80;
    server_name $APP;
    client_max_body_size 50M;
    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        # OBLIGATORIOS: sin estos Next filtra el puerto interno y redirige a
        # app.dominio.com:3000, que desde afuera no existe.
        proxy_set_header X-Forwarded-Host \$host;
        proxy_set_header X-Forwarded-Port 443;
        proxy_cache_bypass \$http_upgrade;
    }
}
EOF

cat > /etc/nginx/sites-available/$SOCKET <<EOF
server {
    listen 80;
    server_name $SOCKET;
    location / {
        proxy_pass http://127.0.0.1:3001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade \$http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_read_timeout 3600s;
        proxy_send_timeout 3600s;
    }
}
EOF

rm -f /etc/nginx/sites-enabled/default
for s in "$API" "$APP" "$SOCKET"; do ln -sf /etc/nginx/sites-available/$s /etc/nginx/sites-enabled/$s; done
nginx -t && systemctl reload nginx

# ─────────────────────────────────────────────────────────────────────────
# 6. Certificados
# ─────────────────────────────────────────────────────────────────────────
echo "[6/7] Certificados SSL..."
certbot --nginx -d "$API" -d "$APP" -d "$SOCKET" \
  --non-interactive --agree-tos -m "$EMAIL_SSL" --redirect >/dev/null 2>&1 \
  && echo "     HTTPS listo" \
  || echo "     OJO: certbot fallo — ¿el DNS ya apunta aca? Reintentar despues."

systemctl restart php8.3-fpm nginx

# ─────────────────────────────────────────────────────────────────────────
# 7. Horizon
# ─────────────────────────────────────────────────────────────────────────
echo "[7/7] Servicio de colas..."
cat > /etc/systemd/system/horizon.service <<'EOF'
[Unit]
Description=Laravel Horizon
After=network.target redis-server.service

[Service]
Type=simple
User=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/backend/artisan horizon
LimitNOFILE=65535

[Install]
WantedBy=multi-user.target
EOF
systemctl daemon-reload && systemctl enable horizon >/dev/null 2>&1

echo
echo "══ Sistema listo. Falta el codigo ══"
echo
cat <<EOF
Los repos son privados, asi que el codigo se sube aparte. Desde tu maquina:

  cd eventos-backend && git archive --format=tar HEAD | ssh root@IP "tar -xf - -C /var/www/backend"
  cd eventos-socket  && git archive --format=tar HEAD | ssh root@IP "tar -xf - -C /var/www/socket"
  cd eventos-web     && git archive --format=tar HEAD | ssh root@IP "tar -xf - -C /var/www/web"

(Para produccion de verdad: llave de despliegue en el servidor y \`git pull\`.)

Despues, en el servidor:

  cd /var/www/backend
  COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader
  cp .env.production.example .env    # y completar TODO
  php artisan key:generate --force
  php artisan migrate --force && php artisan db:seed --force
  php artisan security:check         # DEBE dar verde antes de abrir nada
  php artisan config:cache && php artisan route:cache && php artisan view:cache

  # NO ES OPCIONAL Y NO ES SOLO DEL ADMIN — vale ~8,7 ms en CADA peticion de
  # la API, la use un asistente que jamas abre el panel.
  # Medido el 2026-08-02 sobre el droplet: sin esta cache, el proveedor de
  # Filament arrancaba en 10,47 ms por peticion resolviendo sus iconos de
  # Blade; con ella, 0,51 ms. El piso del framework paso de 19,8 a 11,4 ms.
  # La primera vez que se corrio, el 2026-08-01, se salto justamente porque
  # parecia un paso cosmetico del panel.
  php artisan filament:optimize

  mkdir -p storage/app/purifier && chown -R www-data:www-data storage bootstrap/cache

  # OBLIGATORIO: el panel Filament necesita sus recursos compilados con Vite.
  # Sin esto TODO el admin da "Error del servidor" (Vite manifest not found)
  # y el Data Center, que vive detras del login del admin, tampoco entra.
  # Se descubrio recien al abrirlo en el navegador (2026-08-02).
  pnpm install && pnpm run build
  chown -R www-data:www-data public/build

  # Verificar que la cache quedo escrita — si esta vacia, no se aplico:
  #   find bootstrap/cache/filament -type f   # debe listar panels/admin.php
  #   ls bootstrap/cache/blade-icons.php

  cd /var/www/socket && pnpm install && pnpm build
  pm2 start ecosystem.config.js && pm2 save && pm2 startup systemd -u root --hp /root

  cd /var/www/web && pnpm install
  NODE_OPTIONS="--max-old-space-size=3072" pnpm build
  pm2 start "pnpm start" --name eventos-web --time && pm2 save

  systemctl start horizon

VARIABLES QUE NO SE PUEDEN OLVIDAR (cada una costo una hora de depuracion):

  APP_URL=https://$API          real desde el primer arranque: las imagenes de
                                los correos se guardan con URL absoluta
  WEBAPP_URL=https://$APP       si falta, el magic link apunta a la API y nadie
                                puede entrar
  LARAVEL_API_URL=https://$API/api/v1     el socket lo necesita CON /api/v1
  KIOSK_URL=https://$APP/kiosko
  QUEUE_CONNECTION=redis        en desarrollo es 'sync'
  CLOUDFLARE_R2_PUBLIC_URL      sin esto las URLs de archivos salen rotas

  TRUSTED_PROXIES=127.0.0.1,::1,\$(curl -s http://169.254.169.254/metadata/v1/interfaces/public/0/ipv4/address)
        LA IP PUBLICA DE ESTA MAQUINA, NUNCA QUEMADA A MANO. La webapp le habla
        al backend por https://api.<dominio>, o sea desde la IP publica del
        propio droplet; si esa IP no esta en la lista, Laravel ignora el
        X-Forwarded-For y ve a TODOS los asistentes como una sola IP → los
        limitadores por IP (login, magic link, by-slug) se agotan entre todos.
        Cazado el 2026-08-17: al restaurar el snapshot en un droplet nuevo, el
        .env traia la IP del droplet anterior y la persona 301 recibia 429.
        **Al restaurar un snapshot, ESTA variable hay que revisarla siempre.**

  WEBAPP_INTERNAL_URLS=http://127.0.0.1:3000   (en el .env de Laravel)
  WEBAPP_INTERNAL_SECRET=\$(openssl rand -hex 32)  MISMO valor en el .env de
        Laravel y en /var/www/web/.env.production. Es el aviso con el que el
        backend le dice a la webapp que suelte su cache del marco (anuncios,
        modulos, documentos, solicitudes, evento). Sin el, un anuncio nuevo
        tarda hasta 60 s en la campana.

Credenciales generadas: /root/CREDENCIALES.txt
EOF
