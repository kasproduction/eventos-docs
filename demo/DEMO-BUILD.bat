@echo off
setlocal
title EventOS DEMO - build produccion
chcp 65001 >nul

REM ============================================================
REM  EventOS - compila TODO en modo produccion (correr UNA vez,
REM  y otra vez cada vez que cambie codigo). Tarda varios minutos.
REM  Despues: DEMO-START-PROD.bat
REM
REM  Que hace:
REM   - Webapp  : next build            (bundle optimizado, SSR rapido)
REM   - Socket  : tsc -> dist/          (JS compilado, sin ts-node)
REM   - Kiosko  : vite build -> dist/   (estatico minificado)
REM   - Backend : artisan optimize      (config/rutas/vistas cacheadas)
REM   - Expo    : no se compila aqui; START-PROD lo sirve con
REM               --no-dev --minify (bundle de produccion en Expo Go)
REM ============================================================

set "PHP=C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe"
set "BACKEND=C:\laragon\www\eventos-backend"
set "WEB=C:\laragon\www\eventos-web"
set "SOCKET=C:\laragon\www\eventos-socket"
set "KIOSK=C:\laragon\www\eventos-kiosko"

echo.
echo  ============================================
echo   EventOS DEMO - build de produccion
echo  ============================================
echo.

echo  [0/4] Apagando servicios vivos (next build no convive con next dev)...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0demo-stop.ps1"
echo.

echo  [1/4] Socket: tsc -^> dist/
cd /d "%SOCKET%"
call npm run build
if errorlevel 1 goto :fail
echo.

echo  [2/4] Kiosko: vite build -^> dist/
cd /d "%KIOSK%"
call npm run build
if errorlevel 1 goto :fail
echo.

echo  [3/4] Webapp: next build (este es el lento, 2-5 min)...
cd /d "%WEB%"
call pnpm build
if errorlevel 1 goto :fail
echo.

echo  [4/4] Backend: artisan optimize (config + rutas + vistas + Filament)
cd /d "%BACKEND%"
"%PHP%" artisan optimize
if errorlevel 1 goto :fail
"%PHP%" artisan filament:cache-components
echo.

echo  ============================================
echo   BUILD LISTO. Ahora: DEMO-START-PROD.bat
echo  ============================================
echo.
echo   OJO al volver a desarrollar en el backend: DEMO-STOP.bat corre
echo   "artisan optimize:clear" para soltar las caches. Si no lo corres,
echo   cambios en .env / rutas / config NO se veran hasta limpiarlas.
echo.
pause
exit /b 0

:fail
echo.
echo  [!] Fallo el build. Lee el error de arriba, corrige y vuelve a correr.
echo.
pause
exit /b 1
