# EventOS — Guía de configuración del entorno de desarrollo
*Paso a paso completo para Windows 11 + Laragon*

---

## Resumen de lo que vamos a instalar

```
Laragon (PHP 8.2 + MySQL 8 + Redis + Nginx)  ← probablemente ya tienes
Node.js LTS 22                                ← runtime para Socket.IO
Git + GitHub CLI                              ← control de versiones
Composer                                      ← gestor de paquetes PHP
EAS CLI                                       ← builds de Expo
VS Code + extensiones                         ← editor
Expo Go (en tu teléfono)                      ← pruebas rápidas iniciales
```

---

## PASO 1 — Verificar Laragon

### 1.1 Abrir Laragon y verificar versión
- Laragon debe ser **versión 6.x o superior**
- Si tienes versión anterior: descargar Laragon 6 desde laragon.org (instalación limpia o actualizar)

### 1.2 Cambiar PHP a versión 8.2
En la bandeja del sistema → clic derecho en Laragon → **PHP → 8.2.x**
> Si no aparece PHP 8.2: Laragon → menú → Tools → Quick Add → PHP 8.2

Verificar en la terminal de Laragon:
```
php -v
```
Debe mostrar: `PHP 8.2.x`

### 1.3 Habilitar extensiones de PHP requeridas
Laragon → clic derecho → **PHP → PHP extensions** → activar:
- ✅ `redis`
- ✅ `gd`
- ✅ `zip`
- ✅ `intl`
- ✅ `bcmath`
- ✅ `sodium`
- ✅ `fileinfo`
- ✅ `mbstring` (normalmente ya activa)
- ✅ `pdo_mysql` (normalmente ya activa)

Reiniciar Laragon después de activar extensiones.

### 1.4 Verificar Redis en Laragon
Laragon → clic derecho → verificar que **Redis** aparezca en el menú y esté corriendo.
- Si no aparece: Laragon → menú → Tools → Quick Add → Redis

Verificar que Redis responde:
```
redis-cli ping
```
Debe responder: `PONG`

### 1.5 Verificar MySQL
Laragon → clic derecho → MySQL debe estar corriendo (punto verde).
- Usuario por defecto: `root`
- Contraseña por defecto: *(vacía)*
- Puerto: `3306`

---

## PASO 2 — Instalar Node.js LTS 22

1. Ir a **nodejs.org** → descargar **Node.js 22 LTS** (versión recomendada)
2. Ejecutar el instalador `.msi` → Next → Next → Install
3. **Reiniciar el PC** después de instalar

Verificar en terminal:
```
node -v     → debe mostrar v22.x.x
npm -v      → debe mostrar 10.x.x
```

### 2.1 Instalar nvm-windows (recomendado para manejar versiones)
Alternativa más robusta: instalar **nvm-windows** desde github.com/coreybutler/nvm-windows
Esto permite cambiar de versión de Node fácilmente si un proyecto lo requiere.

```
nvm install 22
nvm use 22
nvm alias default 22
```

---

## PASO 3 — Instalar Git

### 3.1 Descargar e instalar Git
- Ir a **git-scm.com** → descargar Git para Windows
- En el instalador, opciones importantes:
  - Editor: seleccionar **Visual Studio Code**
  - Default branch: cambiar a **main**
  - Adjusting PATH: **Git from the command line and also from 3rd-party software**
  - Line endings: **Checkout Windows-style, commit Unix-style**

Verificar:
```
git --version   → git version 2.x.x
```

### 3.2 Configurar identidad Git (una sola vez)
```
git config --global user.name "Tu Nombre"
git config --global user.email "tu@email.com"
git config --global init.defaultBranch main
git config --global core.autocrlf true
```

### 3.3 Instalar GitHub CLI
- Ir a **cli.github.com** → descargar e instalar
- Autenticarse:
```
gh auth login
```
Seguir el flujo: GitHub.com → HTTPS → autenticar via browser.

### 3.4 Crear SSH key para GitHub
```
ssh-keygen -t ed25519 -C "tu@email.com"
```
Presionar Enter en todo (sin passphrase para desarrollo local).

Agregar la clave pública a GitHub:
```
gh ssh-key add ~/.ssh/id_ed25519.pub --title "Laptop Dev"
```

Verificar conexión:
```
ssh -T git@github.com
```
Debe mostrar: `Hi Kasproduction! You've successfully authenticated`

---

## PASO 4 — Instalar Composer

Laragon 6 incluye Composer. Verificar:
```
composer --version   → Composer version 2.x.x
```

Si no está disponible: descargar desde **getcomposer.org** → Composer-Setup.exe

---

## PASO 5 — Instalar herramientas globales de Node

Abrir terminal y ejecutar:
```
npm install -g eas-cli
npm install -g pm2
```

Verificar:
```
eas --version    → eas-cli/x.x.x
pm2 --version    → x.x.x
```

> `npx expo` no requiere instalación global — se usa con npx directamente.

---

## PASO 6 — Instalar Visual Studio Code + extensiones

### 6.1 Descargar VS Code
- **code.visualstudio.com** → descargar para Windows

### 6.2 Extensiones recomendadas para EventOS

**PHP / Laravel:**
- `PHP Intelephense` — autocomplete PHP
- `Laravel Blade Snippets` — sintaxis Blade
- `Laravel Artisan` — comandos artisan desde VS Code
- `Laravel Extra Intellisense` — rutas, modelos, configs
- `PHP CS Fixer` — formato automático (Laravel Pint)
- `Pest Snippets` — snippets para tests

**JavaScript / TypeScript / React Native:**
- `ESLint` — linting
- `Prettier - Code formatter` — formato automático
- `React Native Tools` — debug y utilidades RN
- `Expo Tools` — soporte oficial Expo
- `Tailwind CSS IntelliSense` — autocomplete clases NativeWind

**Git:**
- `GitLens` — visualizar historial y blame en el editor
- `Git Graph` — ver el árbol de branches gráficamente

**General:**
- `Thunder Client` — cliente HTTP integrado (para probar la API sin Postman)
- `DotENV` — sintaxis .env
- `Error Lens` — muestra errores inline
- `Todo Tree` — visualiza todos los TODO del proyecto

### 6.3 Configurar format on save en VS Code
Archivo → Preferencias → Configuración → buscar "format on save" → activar ✅

---

## PASO 7 — Crear repositorios en GitHub

EventOS usa **3 repositorios separados**:

| Repo | Contenido | Visibilidad |
|---|---|---|
| `eventos-backend` | Laravel 11 + Filament | Privado |
| `eventos-app` | Expo React Native | Privado |
| `eventos-socket` | Node.js + Socket.IO | Privado |

### 7.1 Crear los repos con GitHub CLI
```
gh repo create kasproduction/eventos-backend --private --description "EventOS — Laravel API + Filament Admin"
gh repo create kasproduction/eventos-app --private --description "EventOS — Expo React Native App"
gh repo create kasproduction/eventos-socket --private --description "EventOS — Node.js Socket.IO Server"
```

---

## PASO 8 — Crear estructura de carpetas

Estructura dentro de Laragon:
```
C:\laragon\www\
    APP EVENTOS\                ← documentación (ya existe)
        EventOS_ClaudeCode_Prompt_v2.md
        EventOS_DevSetup.md
        EventOS_Roadmap.md
    eventos-backend\            ← se crea al instalar Laravel (Sesión 1)
    eventos-socket\             ← se crea al iniciar Node.js (Sesión 4 de backend)
```

El proyecto Expo va fuera de Laragon (no necesita servidor web local):
```
C:\Users\Kasproduction\Projects\
    eventos-app\                ← se crea al iniciar Expo (Sesión 4)
```

---

## PASO 9 — Crear bases de datos en MySQL

Abrir **phpMyAdmin** desde Laragon → ir a phpMyAdmin → Nueva base de datos:

| Base de datos | Cotejamiento |
|---|---|
| `eventos_db` | `utf8mb4_unicode_ci` |
| `eventos_testing` | `utf8mb4_unicode_ci` |

> **Importante:** usar siempre `utf8mb4_unicode_ci` — soporta emojis en el chat.

O desde terminal MySQL:
```sql
CREATE DATABASE eventos_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE eventos_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## PASO 10 — Crear virtual host en Laragon

Laragon crea virtual hosts automáticamente por carpeta. Al crear `C:\laragon\www\eventos-backend\`, el virtual host `eventos-backend.test` quedará disponible automáticamente.

Verificar en `C:\Windows\System32\drivers\etc\hosts` que Laragon haya agregado:
```
127.0.0.1    eventos-backend.test
```
(Laragon lo hace automáticamente al detectar la carpeta)

---

## PASO 11 — Instalar Expo Go en tu teléfono

- **iOS:** App Store → buscar "Expo Go"
- **Android:** Play Store → buscar "Expo Go"

> Expo Go sirve para pruebas básicas iniciales. Para usar NativeWind v4 + MMKV + expo-camera necesitarás un **development build** (lo configuramos en la Sesión 4 del app).

---

## PASO 12 — Crear cuenta EAS (Expo Application Services)

1. Ir a **expo.dev** → Sign Up → crear cuenta con el email de Kasproduction
2. En terminal:
```
eas login
```
3. Crear el proyecto EAS (lo hacemos cuando iniciemos el proyecto Expo en Sesión 4)

---

## PASO 13 — Verificación final del entorno

Abrir la terminal de Laragon y ejecutar cada línea. Todas deben responder sin error:

```bash
# PHP
php -v                    → PHP 8.2.x

# PHP extensiones críticas
php -m | grep redis       → redis
php -m | grep gd          → gd
php -m | grep zip         → zip

# MySQL
mysql -u root -e "SELECT VERSION();"   → 8.x.x

# Redis
redis-cli ping            → PONG

# Node.js
node -v                   → v22.x.x
npm -v                    → 10.x.x

# Git
git --version             → git version 2.x.x

# GitHub CLI
gh auth status            → Logged in to github.com as Kasproduction

# Composer
composer --version        → Composer version 2.x.x

# EAS CLI
eas --version             → eas-cli/x.x.x
```

Si todos responden correctamente → **entorno listo** ✅

---

## PASO 14 — Configurar .gitignore global (Windows)

Crear archivo `C:\Users\Kasproduction\.gitignore_global`:
```
# Windows
Thumbs.db
Desktop.ini
$RECYCLE.BIN/

# VS Code
.vscode/settings.json
.vscode/launch.json

# Node
node_modules/

# Laravel
.env
/vendor/

# Expo
.expo/
node_modules/

# OS
.DS_Store
*.log
```

Registrarlo en git:
```
git config --global core.excludesfile "C:/Users/Kasproduction/.gitignore_global"
```

---

## PASO 15 — Flujo de trabajo con Git (leer antes de codear)

### Estrategia de branches

```
main          ← producción — solo recibe merges desde develop tras pruebas
develop       ← staging — integración de features completos
feature/xxx   ← desarrollo de cada módulo/sesión
hotfix/xxx    ← correcciones urgentes en producción
```

### Formato de commits (Conventional Commits)

```
feat: agregar módulo de agenda con favoritos
fix: corregir validación de QR token expirado
chore: configurar Redis como driver de caché
docs: actualizar instrucciones de setup
refactor: extraer lógica de ban a BanService
test: agregar tests de feature para check-in
```

### Ciclo de trabajo por sesión

```
1. Crear branch desde develop:
   git checkout develop
   git pull origin develop
   git checkout -b feature/sesion-1-laravel-setup

2. Desarrollar y testear

3. Cuando funciona → decirle a Claude "confirmo, funciona"

4. Claude hace commit:
   git add [archivos específicos]
   git commit -m "feat: [descripción]"

5. Al terminar la sesión completa → merge a develop:
   git checkout develop
   git merge feature/sesion-1-laravel-setup
   git push origin develop

6. Cuando develop está estable y probado → merge a main:
   git checkout main
   git merge develop
   git push origin main
```

### Nunca hacer
- ❌ `git push --force` en main o develop
- ❌ Commit de archivos `.env` con credenciales reales
- ❌ Commit sin mensaje descriptivo (`git commit -m "fix"`)
- ❌ Trabajar directamente en main

---

*EventOS DevSetup v1.0 — Kasproduction*
*Siguiente paso: EventOS_Roadmap.md → ver fases y sesiones de implementación*
