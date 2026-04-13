# Pendientes — EventOS

> La UNICA fuente de verdad de lo que falta por hacer.
> Cada item es auto-contenido — no necesitas abrir otro archivo para entender que hacer.
> Actualizado: 2026-04-13 (1.x-E-B completado)

---

## PRIORIDAD ALTA — bloquea pitch junio 2026

### Analytics Dashboard (1.C1)
- Filament dashboard: ROI, engagement, asistencia por sesion, sponsors performance, leads por stand
- API endpoints para alimentar web app
- Ambos competidores (Cisco $88K, ICE360 $49M COP) lo tienen. Justifica el precio ante el cliente.
- Ref: docs/ANALISIS-COMPETITIVO.md

### Web App completa (W.0–W.12)
- Next.js 15 + Tailwind + shadcn/ui + Socket.IO client
- Spatial UI (paneles flotantes, pill bar, presets layout — NO sidebar)
- 13 sesiones: setup, home, agenda, streaming+Q&A+chat, speakers, social, sponsors, networking, encuestas+gamification, notificaciones, sockets, polish
- Es la experiencia core para asistentes virtuales. Sin esto no hay venta.
- Ref: docs/WEB-APP-PLAN.md

### Deploy produccion (SEC-4 + SEC-5)
- Docker Compose 6 servicios (app, queue, scheduler, socket, mysql, redis)
- VPS Hetzner CX22 (~$5/mes), Cloudflare LB + R2 storage
- Sentry, SecurityLogger, uptime monitoring
- GitHub Actions CI/CD: push a main → build → deploy
- EAS Build production profile (Android + iOS)
- IMPORTANTE: al migrar a R2, revisar resolveStepsConfigUrls() y fixStorageUrl() — URLs seran absolutas
- Ref: docs/DISPONIBILIDAD-HA.md, EventOS_Roadmap.md seccion Deploy

---

## PRIORIDAD MEDIA — fortalece la venta

### Seguridad Auth (SEC-3b pendientes)
- [x] ~~SEC-3b.2: Validar token al startup~~ — COMPLETADO 2026-04-13. GET /me al abrir, 401→clearAuth, ban→banned, approval→pending. Fallback red.
- [x] ~~SEC-3b.4: Middleware approval server-side~~ — COMPLETADO 2026-04-13. CheckApproval.php, excluido de auth/profile/onboarding. 403 si no aprobado.

### Features competitivos
- [x] ~~1.C5: Calendar sync~~ — YA EXISTIA. addSessionToCalendar con expo-calendar nativo + fallback Google Calendar. Endpoint .ics para landing/email.
- [x] ~~1.C3: QR dinamico rotativo~~ — COMPLETADO 2026-04-13. HMAC-SHA256 cada 30s, refetch 25s app, checkin valida estatico+dinamico, 309 tests.
- [ ] 1.23: Permisos granulares Filament — roles admin diferenciados (org_admin, event_admin, moderator). Spatie permissions ya instalado, falta wiring en Filament resources.

### Onboarding pendientes
- [x] ~~1.x-E-B: FormStep tipos avanzados~~ — COMPLETADO 2026-04-13. SearchableSheet, CheckboxGroupSheet, DateTimePicker, Presets API, Filament 11 tipos.
- [x] ~~1.x-E-C: DateSheet custom Lumina Noir~~ — COMPLETADO 2026-04-13. BottomSheet 3 columnas scroll, haptic, preview, accent color. Desinstalado @react-native-community/datetimepicker.
- [ ] 1.x-E-D: Campos condicionales — depends_on en config JSON. Ej: pais=Colombia → mostrar ciudades CO. Requiere: depends_on en Filament, FormStep evalua condiciones, fetch dinamico opciones. Endpoint cities/{code} ya existe. Campos NO hardcodeados — admin define dependencias.
- [ ] 1.x-C: Roles asistente — si evento hibrido, step nuevo entre Auth y Photo: "Como participaras?" (presencial/virtual). Virtual=sin QR, directo al home. Config en Filament: tipo_participacion (presencial/virtual/hibrido).
- [ ] 1.x-D: Estados evento lifecycle — 4 estados: registration_only → published → live → ended. Pantalla espera DaVinci con countdown (calculado desde event.starts_at). Push notification al cambiar estado. Config en Filament.

---

## PRIORIDAD BAJA — post-venta o si lo piden

### Registro avanzado
- [ ] 1.x-F: Registro cerrado (lista invitados) — admin sube CSV/emails en Filament. Onboarding AuthStep valida email contra lista. Si no esta: toast "No estas en la lista".
- [ ] 1.x-G: Registro por codigo de acceso — admin genera codigos en Filament (unicos o grupales, con limite de usos). Campo "Codigo de acceso" antes del registro. Valida contra backend.
- [ ] 1.x-H: Staff invite push + cambio de rol — admin invita desde Filament → push "Fuiste invitado al equipo" → app cambia layout a vendedor (tabs: Mi Stand, Leads, etc.). Requiere socket o push + cambio rol en authStore.

### Features opcionales
- [ ] 1.C2: Wallet digital — badge en Apple Wallet (.pkpass) + Google Wallet (JWT). Reemplaza badge printing fisico como default.
- [ ] 1.C4: Digital signage — pantallas venue con agenda en vivo, sesion actual/siguiente, leaderboard, social wall. Base: proyecto checki (106 archivos ref, commit 5e2f867).
- [ ] 1.C6: Badge printing fisico — impresora termica (Zebra/Brother) + credencial personalizada. Add-on. Logica check-in ya existe.
- [ ] SEC-3.1: 2FA OTP (email/WhatsApp) — requiere WhatsApp Business API + pantalla app
- [ ] SEC-3.2: Device fingerprinting — depende de 2FA
- [ ] Light mode — refactor colores hardcoded a theme provider. Sesion dedicada si cliente lo pide.
- [ ] PDFs: certificados asistencia + reporte post-evento + reports exportables detallados

### Cleanup app
- [ ] QA visual multi-device (1.x-E-A completado, falta QA en dispositivos)
- [ ] Crop circular dark (react-native-image-crop-picker, requiere dev build)
- [ ] Push reminders probar en dev build (codigo listo, expo-notifications no funciona en Expo Go)
- [ ] Push invalidation probar en dev build (codigo listo)
- [ ] Login duplicado: extraer LoginForm compartido o confirmar que login.tsx ya fue eliminado
- [ ] Debug logs en onboardingApi.ts quitar antes de produccion
- [ ] Mensaje anclado chat tipo Twitch (nice to have)
- [ ] White-label: migrar app.json → app.config.js + estructura clients/ (ref: docs/WHITE-LABEL.md)

### Showcase / Demo inversor
- [ ] Panels finales clickeables (z-index blocking, click no llega)
- [ ] Responsive (disenar a 1920x1080 base, actualmente 1200x720 fijo)
- [ ] Audio/sonido (cinematic riser + impacts)
- [ ] Hints/labels solapados
- [ ] Social wall mejor explicacion visual
- [ ] CSS box-shadow parsing error linea ~250

---

## Documentos de referencia

| Doc | Contenido |
|-----|-----------|
| `EventOS_Roadmap.md` | Fases, sesiones, timeline, dependencias |
| `docs/COMPLETADO.md` | Historial completo de todo lo hecho |
| `docs/ROADMAP-UIUX-LANDING.md` | Spec diseno: landing, estados evento, design system |
| `docs/WEB-APP-PLAN.md` | Spec web app: spatial UI, W.0-W.12, stack tecnico |
| `docs/ANALISIS-COMPETITIVO.md` | Cotizaciones reales, gaps, pricing, escala |
| `docs/WHITE-LABEL.md` | App config dinamico, clients/, EAS build |
| `docs/FASE-SEGURIDAD.md` | Auditoria OWASP, SEC-1 a SEC-5 |
| `docs/DISPONIBILIDAD-HA.md` | Arquitectura HA, deploy, costos |
| `docs/BUG-LOG.md` | Bugs historicos BUG-001 a BUG-078 |
| `docs/QA-MASTER.md` | Barrido 60+ endpoints, 20 modulos, 3 roles |
| `docs/QA-AUTH-ONBOARDING.md` | QA auth/onboarding, 30+ escenarios |
