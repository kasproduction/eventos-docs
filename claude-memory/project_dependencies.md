---
name: EventOS — dependencias progresivas por sesión
description: Qué librería se instala en qué sesión — nunca instalar todo al inicio
type: project
---

Principio: instalar dependencias solo cuando la sesión que las necesita comienza.
No instalar todo en el setup inicial.

## Backend (Laravel) — por sesión

| Sesión | Paquete |
|---|---|
| 0.2 | laravel/sanctum |
| 0.2 | spatie/laravel-permission |
| 0.2 | filament/filament |
| 0.2 | laravel/horizon |
| 0.2 | laravel/telescope (dev only) |
| 0.2 | sentry/sentry-laravel |
| 1.3 | ezyang/htmlpurifier |
| 1.9 | kreait/laravel-firebase |
| 2.5 | spatie/browsershot (PDFs — con queue pdf, maxProcesses:1) |
| 3.2 | laravel/cashier (Stripe) |

## App (Expo) — por sesión

| Sesión | Paquete |
|---|---|
| 0.3 | expo-router |
| 0.3 | nativewind + tailwindcss |
| 0.3 | react-native-reanimated |
| 0.3 | expo-secure-store |
| 0.3 | react-native-mmkv |
| 0.3 | @tanstack/react-query |
| 0.3 | zustand |
| 0.3 | expo-localization + i18n-js |
| 1.3 | expo-image |
| 1.3 | expo-file-system |
| 1.3 | @shopify/flash-list |
| 1.4 | expo-camera |
| 1.4 | expo-keep-awake |
| 1.7 | socket.io-client |
| 1.9 | expo-notifications |
| 2.1 | expo-av (streaming) |
| 2.4 | expo-image-picker (photobooth) |

## Socket.IO (Node.js) — por sesión

| Sesión | Paquete |
|---|---|
| 0.4 | socket.io |
| 0.4 | @socket.io/redis-adapter |
| 0.4 | ioredis |
| 0.4 | axios |
| 0.4 | dotenv |

**Why:** Instalar todo al inicio hace que la Sesión 0 sea demasiado frágil — si algo falla hay demasiadas variables. Progresivo = más fácil de debuggear y hace cada sesión autocontenida.

**How to apply:** Al iniciar cada sesión, revisar esta lista e instalar solo lo que corresponde a esa sesión.
