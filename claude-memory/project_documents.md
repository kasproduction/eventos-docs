---
name: EventOS — documentos del proyecto y su propósito
description: Qué archivo es qué y dónde encontrarlo
type: reference
---

## Documentos de planificación

Todos en `C:\laragon\www\APP EVENTOS\`

| Archivo | Propósito |
|---|---|
| `EventOS_ClaudeCode_Prompt_v2.md` | **Documento maestro** — stack, 41 tablas DB, 70+ endpoints, 76 notas de arquitectura, configs de producción, sesiones de implementación. Versión actual: v2.5 |
| `EventOS_DevSetup.md` | Guía paso a paso para configurar el entorno en Windows 11 + Laragon. 15 pasos desde PHP hasta EAS. |
| `EventOS_Roadmap.md` | Plan de trabajo definitivo — 13 sesiones activas (Fase 0 + Fase 1), dependencias por sesión, checklists, definición de completado. Versión actual: v2.0 |

## Repositorios GitHub

| Repo | Ruta local | Propósito |
|---|---|---|
| kasproduction/eventos-backend | C:\laragon\www\eventos-backend | Laravel 11 + Filament |
| kasproduction/eventos-app | C:\Users\Kasproduction\Projects\eventos-app | Expo SDK 52 |
| kasproduction/eventos-socket | C:\laragon\www\eventos-socket | Node.js + Socket.IO |
| kasproduction/eventos-kiosko | — | Kiosco QR (S1.4) |

## Estado del código al 2026-04-04

| Sesión | Estado | Branch |
|---|---|---|
| 0.1 | ✅ Completa | — |
| 0.2 | ✅ Completa | mergeado a main |
| 0.3 | ✅ Completa | main (repo nuevo) |
| 0.4 | ✅ Completa | main (repo nuevo) |
| 1.1 | ✅ Completa | mergeado a main |
| 1.2 | ✅ Completa | mergeado a main |
| 1.3a | ✅ Completa | feature/s13a-contenido-backend → mergeado a main |
| 1.3b | ✅ Completa | feature/s13b-contenido-app → mergeado a main |
| 1.4 | ✅ Completa | feature/s14-checkin-backend, s14-mi-qr, s14-checkin-socket → mergeado a main |
| 1.5 | ✅ Completa | feature/s15-leads → mergeado a main |
| 1.5b | ✅ Hotfix | feat(vendedor): tab Inicio con ModuleMenu → mergeado a main |
| 1.6 | ✅ Completa | feat(s1.6): backend + app → mergeado a main. Hotfixes: api.ts timeout, edits query retry:false |
| 1.7 | ✅ Completa | mergeado a main (ambos repos) |
| 1.8 | ✅ Completa | feature/s18-admin-bans → mergeado a main |
| 1.9a | ✅ Completa | feature/s19-chat → mergeado a main (backend + socket) |
| 1.9b | ✅ Completa | feature/s19-chat → mergeado a main (ambos repos) |
| 1.12 | ✅ Completa | feature/s112-tracks-session-types → mergeado a main (backend + app) |
| 1.10 | ✅ Completa (refactor v2) | feature/s110-encuestas-v2 → mergeado a main (2026-04-04) |
| 1.11 | ✅ Completa | mergeado a main (backend + app, 2026-04-04) |
| 1.13 | ⏳ Pendiente | Automated emails |

## Sesiones pendientes de Fase 1

- **S1.13** — Automated emails
- **Sesión UI** — Rediseño visual completo post-Fase 1 (dark mode, Fever style, primary_color dinámico)
- **Sesión Deploy** — Docker Compose + VPS + runbook completo (post S1.13 + UI, antes del primer cliente)

## Requisito explícito para la sesión de deploy

El usuario quiere que la sesión de deploy produzca un `EventOS_DevSetup_Deploy.md` que sea 100% autocontenido — como si el que lo ejecuta no sabe nada del proyecto. Debe cubrir:
- Crear proyecto Firebase + registrar SHA-1 (debug y producción son keystores distintos)
- Dónde descargar `google-services.json` y `GoogleService-Info.plist` y dónde ponerlos
- Cómo subir la FCM V1 service account key a Expo (via GraphQL API — el CLI es interactivo)
- Cómo generar keystore de producción Android
- EAS credentials para iOS (APNs key en Apple Developer Portal)
- Cada variable de entorno con descripción y fuente
- `docker-compose.yml` completo con los 6 servicios
- GitHub Actions CI/CD
- Checklist de verificación post-deploy

## URLs locales de desarrollo

- Backend: http://eventos-backend.test
- Admin Filament: http://eventos-backend.test/admin
- Display proyectable: http://eventos-backend.test/display/polls/{id}
- Socket: ws://localhost:3001
