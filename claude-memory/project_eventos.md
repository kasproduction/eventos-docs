---
name: EventOS — contexto del proyecto
description: Detalles clave del proyecto EventOS — SaaS de gestión de eventos corporativos
type: project
---

Plataforma SaaS de gestión de eventos corporativos con 4 superficies: app móvil (Expo), web (Next.js), admin panel (Filament) y kiosco de check-in.

**Identidad:**
- Nombre: EventOS
- Empresa: Kasproduction
- Bundle ID: com.kasproduction.eventos
- Idiomas: Español + Inglés (i18n desde Fase 1)
- iOS mínimo: 15+ | Android mínimo: API 29 (Android 10)
- Dark mode: SÍ desde Fase 1

**Stack definitivo:**
- Backend: Laravel 11 + Filament + Sanctum + Spatie + Redis + MySQL 8
- App: Expo SDK 52 + Expo Router + NativeWind v4 + TanStack Query + MMKV + expo-secure-store + FlashList + Zustand
- Real-time: Node.js + Socket.IO + @socket.io/redis-adapter
- Juegos: Unity WebGL (pantalla) + Socket.IO (bridge) + teléfono (control)
- Infra: VPS + Nginx + Forge + Cloudflare R2 + CDN + Sentry + GitHub Actions

**Documento maestro:** EventOS_ClaudeCode_Prompt_v2.md (versión 2.5)
- 38 tablas de base de datos
- 75+ notas críticas de arquitectura
- 70+ endpoints API en /api/v1/
- 10 sesiones de implementación definidas
- Checklist pre-deploy de 20 items

**Facturación:** Manual en Fase 1 (control via Filament). Sistema de licencias con subscription_status en organizations table. Fase 3: Stripe.

**Why:** El documento fue construido sesión por sesión cubriendo todos los escenarios posibles antes de escribir código. Está listo para empezar Fase 0.

**How to apply:** Siempre referenciar el documento maestro al iniciar una sesión de código. Cada feature implementado y probado → pedir confirmación → commit.
