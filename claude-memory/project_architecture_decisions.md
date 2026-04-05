---
name: EventOS — decisiones de arquitectura
description: Decisiones técnicas clave tomadas y sus razones — no cambiar sin revisar esto
type: project
---

Decisiones arquitectónicas tomadas durante la planificación (2026-03-28).
Cada decisión tiene su razón para no revertirla por accidente.

## Auth y tokens

- **Token Sanctum en expo-secure-store** (Keychain iOS / Keystore Android). NUNCA en AsyncStorage (texto plano en Android).
- **Argon2id** como driver de hashing (config/hashing.php). Más seguro que bcrypt contra ataques de GPU.
- **QR token = HMAC-SHA256** del attendee_id + event_id + secret. No es el token Sanctum — es un token separado para check-in.
- **Node.js valida auth** llamando a `GET /api/v1/auth/me` con el token del handshake. Node NO toca MySQL directamente porque los tokens están hasheados con SHA-256.

## Storage / Cache

- **MMKV** para cache general (agenda, módulos, datos no sensibles). No AsyncStorage.
- **expo-secure-store** solo para el token de autenticación.
- **Redis DB 0**: cache de la API (stale-while-revalidate).
- **Redis DB 1**: queues de Laravel.
- **Redis DB 2**: pub/sub de Socket.IO (@socket.io/redis-adapter).
- **Aforo**: Redis INCR atómico al hacer check-in. No actualizar en MySQL síncronamente.
- **Votos de encuesta**: Redis HASH `poll:{id}:votes {option_id: count}` durante votación activa. Sync a MySQL al cerrar.

## Real-time

- **Node.js + Socket.IO** (NO Laravel Reverb). Razón: a 10,000 usuarios concurrentes, PHP es menos eficiente en memoria (~50KB/conexión vs Node). Node mantiene conexiones persistentes mejor.
- **Chat**: Socket.IO entrega en tiempo real + Job Laravel persiste en chat_messages. Al abrir chat por primera vez, carga últimos 50 mensajes vía API.
- **Presencia en chat**: Redis SADD/SREM + heartbeat de 20s con EXPIRE 35s.

## Streaming (decisión 2026-03-30)

- **NO hay SDK de video propio** (no Jitsi, no Agora, no expo-av para streaming).
- El campo `stream_url` en `event_sessions` contiene la URL del organizador (YouTube Live, Vimeo, Streamyard, cualquier servicio).
- **App (S2.2)**: `react-native-webview` embebe la URL — es un WebView, no decodificación de video.
- **Web (S2.1)**: `<iframe>` con la URL del stream.
- **Tiempo de visualización**: timer propio (entrada/salida de pantalla) → evento `session_stream_view` en tabla `tracking` con duración en segundos. Suficiente para reporte post-evento por usuario y sesión.
- **Por qué**: el cliente ya tiene su servicio de streaming (muchos tienen YouTube o Vimeo Pro). Cero costo adicional, cero dependencia nueva, máxima flexibilidad.

## Archivos / Media

- **Cloudflare R2** (bucket público para imágenes, privado para documentos).
- **Presigned URLs** para uploads. Los archivos NUNCA pasan por el servidor Laravel.
- **expo-image** para imágenes en app (disk cache automático, blur placeholder).
- **expo-file-system** para descarga de PDFs a disco local.

## PDFs

- **Browsershot** (headless Chrome) — NUNCA síncrono. Siempre como Job en queue dedicada `pdf` con maxProcesses:1 en Horizon. Un render puede consumir 300-500MB de RAM.

## API

- Prefijo `/api/v1/` en todos los endpoints desde el inicio.
- Idempotency keys para POST críticos.
- Paginación por cursor en listas grandes (chat, activity_log).
- Errores custom: ATTENDEE_BANNED, QR_INVALID, QR_ALREADY_CHECKIN, EVENT_FULL, SUBSCRIPTION_SUSPENDED.
- Rate limiting: 5/min login por IP, 60/min API general, 10/min upload.

## App

- **Expo SDK 52 fijado**. No actualizar hasta que EAS lo marque required y todas las libs sean compatibles.
- **New Architecture habilitada** desde el inicio.
- **TypeScript strict mode** en todo el proyecto.
- **i18n desde Sesión 0.3**: expo-localization + i18n-js. ES + EN. Nunca strings hardcodeados.
- **Dark mode**: NativeWind v4 con prefijo `dark:`. userInterfaceStyle: "automatic" en app.json.
- **Menú home siempre desde API** — nunca hardcodeado en la app.
- **FlashList** reemplaza FlatList en todas las listas largas.

## Módulos

- Los módulos son dinámicos y se filtran por rol del usuario desde el API.
- Config JSON por módulo editable desde Filament.
- Al cambiar módulo: push silenciosa al app.

## Infra

- **pm2** para Node.js (proceso persistente, auto-restart).
- **Supervisor** para Laravel queue workers.
- **Horizon** para monitoreo de queues.
- **Telescope** solo en local (APP_ENV=local).
- **Sentry** en producción (Laravel + Expo).
- **GitHub Actions + Forge** para CI/CD.
