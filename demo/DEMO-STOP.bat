@echo off
title EventOS DEMO - apagar
chcp 65001 >nul

REM ============================================================
REM  EventOS - apaga los servicios que abrio DEMO-START.bat
REM  (webapp :3000, socket :3001, kiosko :5173, expo :8081)
REM  Laragon (Apache/MySQL/Redis) se deja corriendo a proposito.
REM  Manda Ctrl+C real a cada consola antes de forzar: un kill
REM  forzado a "next dev" corrompe la cache .next.
REM ============================================================

echo.
echo  Apagando servicios EventOS...
echo.
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0demo-stop.ps1"
echo.
echo  Soltando caches del backend ^(artisan optimize:clear^) para volver a desarrollo...
"C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe" "C:\laragon\www\eventos-backend\artisan" optimize:clear
echo.
pause
