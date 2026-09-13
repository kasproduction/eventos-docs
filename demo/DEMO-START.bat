@echo off
setlocal EnableDelayedExpansion
title EventOS DEMO - arranque
chcp 65001 >nul

REM ============================================================
REM  EventOS - arranque completo LOCAL para demo (sin desplegar)
REM  Levanta: Laragon (Apache+MySQL+Redis) / Socket / Webapp /
REM           Kiosko / Expo y abre Admin + Webapp + Kiosko + Pulse
REM  Doble clic y esperar ~60 s. Para apagar: DEMO-STOP.bat
REM ============================================================

set "LARAGON=C:\laragon\laragon.exe"
set "REDIS_CLI=C:\laragon\bin\redis\redis-x64-5.0.14.1\redis-cli.exe"
set "MYSQL=C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe"
set "PHP=C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe"

set "BACKEND=C:\laragon\www\eventos-backend"
set "WEB=C:\laragon\www\eventos-web"
set "SOCKET=C:\laragon\www\eventos-socket"
set "KIOSK=C:\laragon\www\eventos-kiosko"
set "APP=C:\Users\Kasproduction\Projects\eventos-app"

set "SLUG=summit-empresarial-2026"
set "LAN_IP=192.168.50.142"

REM EXPO_MODE=lan    -> celular en el mismo WiFi (rapido, por defecto)
REM EXPO_MODE=tunnel -> celular en otra red (usa ngrok, mas lento)
if "%EXPO_MODE%"=="" set "EXPO_MODE=lan"

echo.
echo  ============================================
echo   EventOS DEMO - arrancando todo en local
echo  ============================================
echo.

REM ---------- 1. Laragon: Apache + MySQL + Redis ----------
set "HAS_MYSQL="
set "HAS_APACHE="
set "HAS_REDIS="
call :svc_running mysqld.exe       && set "HAS_MYSQL=1"
call :svc_running httpd.exe        && set "HAS_APACHE=1"
call :svc_running redis-server.exe && set "HAS_REDIS=1"

if defined HAS_MYSQL if defined HAS_APACHE if defined HAS_REDIS (
    echo  [1/7] Laragon ya esta corriendo ^(Apache + MySQL + Redis^)
    goto :laragon_ok
)

echo  [1/7] Laragon no esta completo. Abriendo Laragon...
start "" "%LARAGON%"
set /a n=0
:wait_laragon
ping -n 4 127.0.0.1 >nul
set /a n+=1
call :svc_running mysqld.exe
if not errorlevel 1 call :svc_running httpd.exe
if not errorlevel 1 call :svc_running redis-server.exe
if not errorlevel 1 goto :laragon_ok
if !n! geq 20 (
    echo.
    echo  [!] Laragon no levanto todos los servicios en 60 s.
    echo      Abre Laragon, pulsa "Iniciar todo" y vuelve a correr este .bat
    echo.
    pause
    exit /b 1
)
echo        esperando servicios de Laragon ^(!n!/20^)...
goto :wait_laragon

:laragon_ok
"%REDIS_CLI%" ping 2>nul | find "PONG" >nul
if errorlevel 1 (
    echo  [!] Redis no responde PONG. El socket y el cache lo necesitan.
    pause
    exit /b 1
)
echo        Redis responde PONG

REM ---------- 1b. Backend: sin caches de produccion (modo dev) ----------
echo        Backend: artisan optimize:clear ^(por si quedo cache del modo prod^)
"%PHP%" "%BACKEND%\artisan" optimize:clear >nul 2>&1

REM ---------- 2. Socket server :3001 ----------
call :port_open 3001
if not errorlevel 1 (
    echo  [2/7] Socket ya corre en :3001 ^(no se toca^)
) else (
    echo  [2/7] Socket server  -^>  ws://localhost:3001
    start "EventOS Socket :3001" cmd /k "cd /d "%SOCKET%" && npm run dev"
)

REM ---------- 3. Webapp Next :3000 ----------
call :port_open 3000
if not errorlevel 1 (
    echo  [3/7] Webapp ya corre en :3000 ^(Next 16 es single-instance, no se toca^)
) else (
    echo  [3/7] Webapp Next.js  -^>  http://localhost:3000
    start "EventOS Webapp :3000" cmd /k "cd /d "%WEB%" && pnpm dev"
)

REM ---------- 4. Kiosko Vite :5173 ----------
call :port_open 5173
if not errorlevel 1 (
    echo  [4/7] Kiosko ya corre en :5173 ^(no se toca^)
) else (
    echo  [4/7] Kiosko Vite     -^>  http://localhost:5173
    start "EventOS Kiosko :5173" cmd /k "cd /d "%KIOSK%" && npm run dev -- --host"
)

REM ---------- 5. Expo (app movil) :8081 ----------
call :port_open 8081
if not errorlevel 1 (
    echo  [5/7] Expo ya corre en :8081 ^(no se toca^)
) else (
    if /i "%EXPO_MODE%"=="tunnel" (
        echo  [5/7] Expo ^(tunnel^)  -^>  escanea el QR con Expo Go
        start "EventOS Expo :8081" cmd /k "cd /d "%APP%" && npx expo start --tunnel"
    ) else (
        echo  [5/7] Expo ^(LAN %LAN_IP%^)  -^>  escanea el QR con Expo Go
        start "EventOS Expo :8081" cmd /k "cd /d "%APP%" && npx expo start --lan"
    )
)

REM ---------- 6. Token de Event Pulse ----------
set "PULSE_TOKEN="
"%MYSQL%" -uroot -N -s -e "select pulse_token from events where slug='%SLUG%' limit 1" eventos_db > "%TEMP%\eventos_pulse_token.txt" 2>nul
set /p PULSE_TOKEN=<"%TEMP%\eventos_pulse_token.txt"
del "%TEMP%\eventos_pulse_token.txt" 2>nul
if "%PULSE_TOKEN%"=="NULL" set "PULSE_TOKEN="
set "TOTEM_TOKEN="
"%MYSQL%" -uroot -N -s -e "select auth_token from room_totems where event_id=1 and name like 'Auditorio%%' and is_active=1 limit 1" eventos_db > "%TEMP%\eventos_totem_token.txt" 2>nul
set /p TOTEM_TOKEN=<"%TEMP%\eventos_totem_token.txt"
erase "%TEMP%\eventos_totem_token.txt" 2>nul
if "%TOTEM_TOKEN%"=="NULL" set "TOTEM_TOKEN="
if defined PULSE_TOKEN (
    echo  [6/7] Event Pulse token OK
) else (
    echo  [6/7] Evento '%SLUG%' sin pulse_token. Generalo en Admin ^> Event Pulse
)

REM ---------- 7. Esperar puertos y abrir navegador ----------
echo  [7/7] Esperando que socket / webapp / kiosko respondan...
call :wait_port 3001 Socket
call :wait_port 3000 Webapp
call :wait_port 5173 Kiosko
call :wait_port 8081 Expo

echo.
echo        Abriendo pestanas...
start "" "http://eventos-backend.test/admin"
ping -n 2 127.0.0.1 >nul
start "" "http://localhost:3000"
ping -n 2 127.0.0.1 >nul
if defined TOTEM_TOKEN (start "" "http://localhost:5173/?mode=room&totem_token=%TOTEM_TOKEN%&room_name=Auditorio%%20Principal") else (start "" "http://localhost:5173")
ping -n 2 127.0.0.1 >nul
if defined PULSE_TOKEN start "" "http://eventos-backend.test/event-pulse/?slug=%SLUG%&token=%PULSE_TOKEN%"

echo.
echo  ============================================
echo   TODO ARRIBA
echo  ============================================
echo   Admin ^(Filament^) : http://eventos-backend.test/admin
echo   Webapp asistente : http://localhost:3000
echo   Kiosko salon     : http://localhost:5173/?mode=room^&totem_token=...  ^(Auditorio, se abre solo^)
echo   Kiosko lobby     : http://localhost:5173/?event_id=1^&token=SANCTUM  ^(token: correr Demo5DiasSeeder^)
if defined PULSE_TOKEN (
    echo   Event Pulse      : http://eventos-backend.test/event-pulse/?slug=%SLUG%^&token=%PULSE_TOKEN%
) else (
    echo   Event Pulse      : sin token - generarlo en Admin ^> Event Pulse
)
echo   App movil        : Expo Go en el celular -^> escanear el QR de la ventana "EventOS Expo"
echo                      ^(celular y PC en el mismo WiFi; API en http://%LAN_IP%/api/v1^)
echo   Socket           : ws://localhost:3001
echo   Mailpit          : http://localhost:8025
echo.
echo   Para apagar todo: DEMO-STOP.bat
echo  ============================================
echo.
pause
exit /b 0

REM ================= helpers =================

:svc_running
REM errorlevel 0 si el proceso %1 existe
tasklist /FI "IMAGENAME eq %~1" 2>nul | find /i "%~1" >nul
exit /b %errorlevel%

:port_open
REM errorlevel 0 si algo escucha en 127.0.0.1:%1
powershell -NoProfile -Command "try{$c=New-Object Net.Sockets.TcpClient;$c.Connect('127.0.0.1',%~1);$c.Close();exit 0}catch{exit 1}"
exit /b %errorlevel%

:wait_port
REM espera hasta 90 s a que %1 escuche; %2 = nombre para mostrar
set /a w=0
:wait_port_loop
call :port_open %~1
if not errorlevel 1 (
    echo        %~2 listo en :%~1
    exit /b 0
)
set /a w+=1
if !w! geq 45 (
    echo        [!] %~2 no respondio en :%~1 tras 90 s - revisa su ventana
    exit /b 1
)
ping -n 3 127.0.0.1 >nul
goto :wait_port_loop
