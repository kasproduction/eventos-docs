# Setup — Sistema Checkin/Checkout

## Requisitos

- **PHP 8.1+** con extensiones: pdo_mysql, mbstring, fileinfo
- **MySQL 8.0+**
- **Apache** con mod_rewrite activado
  (XAMPP, Laragon, o Wamp funcionan en Windows)

---

## Pasos para levantar en local (Windows)

### 1. Instalar Laragon (recomendado) o XAMPP

**Laragon** (más fácil): https://laragon.org/download/
- Versión Full incluye Apache + MySQL + PHP 8

**XAMPP**: https://www.apachefriends.org/

---

### 2. Copiar el proyecto

Poner la carpeta en el directorio raíz del servidor:

**Laragon:**
```
C:\laragon\www\checkin\
```

**XAMPP:**
```
C:\xampp\htdocs\checkin\
```

La estructura debe quedar así:
```
checkin/
├── admin/
├── api/
├── cron/
├── setup/
└── schema.sql
```

---

### 3. Crear la base de datos

Abre phpMyAdmin (`http://localhost/phpmyadmin`) o MySQL Workbench y ejecuta:

```sql
-- Primero el schema (crea la BD y las tablas):
source C:/laragon/www/checkin/schema.sql

-- Luego los datos de prueba:
source C:/laragon/www/checkin/setup/seed.sql
```

O desde terminal:
```bash
mysql -u root -p < schema.sql
mysql -u root -p < setup/seed.sql
```

---

### 4. Configurar credenciales de BD

Editar `api/config/db.php`:

```php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');          // tu password de MySQL
define('DB_NAME', 'checkin_system');
define('EVENTO_ID_ACTIVO', 1);  // ID del evento activo
```

---

### 5. Crear el operador admin

```bash
php setup/setup.php
```

O abrir en el navegador:
```
http://localhost/checkin/setup/setup.php
```

PIN por defecto: **1234**
Edita `setup/setup.php` para cambiarlo antes de ejecutar.

> **⚠ Elimina `setup/setup.php` después de usarlo.**

---

### 6. Configurar Apache (mod_rewrite)

**Laragon** lo hace automático si el archivo `.htaccess` existe.

**XAMPP** — verificar que `httpd.conf` tenga:
```apache
<Directory "C:/xampp/htdocs">
    AllowOverride All    ← debe decir All, no None
</Directory>
```

Y que el módulo esté activo en `httpd.conf`:
```apache
LoadModule rewrite_module modules/mod_rewrite.so
```

---

### 7. Abrir el panel

```
http://localhost/checkin/admin/
```

PIN: **1234**

---

## URLs del sistema

| Servicio | URL |
|---|---|
| Panel admin | `http://localhost/checkin/admin/` |
| API tótems | `http://localhost/checkin/api/` |
| phpMyAdmin | `http://localhost/phpmyadmin` |

---

## Probar el API desde terminal

```bash
# Ping
curl http://localhost/checkin/api/ping

# Simular scan de QR
curl -X POST http://localhost/checkin/api/lectura \
  -H "Content-Type: application/json" \
  -d '{"uid_qr": "QR-TEST-001", "totem_id": 1}'
```

---

## Datos de prueba incluidos

- **1 evento** con 4 días (desde hoy)
- **2 salones**: Salón A (cap. 500) y Salón B (cap. 300)
- **4 tótems**: entrada y salida por salón
- **7 charlas** para hoy
- **10 asistentes** con QRs: `QR-TEST-001` al `QR-TEST-010`
- **1 operador admin** con PIN `1234`

---

## Fases completadas

- [x] Fase 1 — Schema SQL
- [x] Fase 2 — PHP Core API
- [x] Fase 3 — Adaptadores de datos
- [x] Fase 4 — Panel Admin
- [x] Fase 5 — Cálculo de asistencia
- [x] Fase 6 — Exportación CSV/Excel
- [x] Fase 7 — Flujo de importación completo

## Pendiente

- [ ] App Unity (Android tótems) — Fase 8
