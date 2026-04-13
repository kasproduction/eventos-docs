# Pendientes — EventOS

> La UNICA fuente de verdad de lo que falta por hacer.
> Organizado por area de trabajo, no por prioridad.
> Actualizado: 2026-04-13

---

## App movil

### Tags + Modulos + Layout unificado (SESION DEDICADA ~6-8h)
Plan completo en `docs/PLAN-TAGS-MODULOS.md`. Resumen:
- [ ] Eliminar roles presencial/virtual, todos son "attendee"
- [ ] Tags JSON en attendee (vip, prensa, etc.) — admin por CSV
- [ ] Modulos con visibility_presence (all/checked_in/not_checked_in) + visibility_tags
- [ ] Check-in QR = trigger automatico, modulos aparecen via socket
- [ ] Pre-registro CSV: nombre+email+tags → invitacion → crear password → onboarding
- [ ] Layout app unificado (eliminar presencial/virtual tabs separados)
- [ ] QR para todos (identidad, no solo ticket)

### Estados del evento
- [ ] 1.x-D: Lifecycle — registration_only/published/live/ended + countdown DaVinci + modo archivo post-evento. Config en Filament.

### Onboarding
- [ ] 1.x-E-D: Campos condicionales — depends_on en config JSON. Ej: pais=Colombia → ciudades CO. Endpoint cities/{code} ya existe.

### Registro avanzado
- [ ] 1.x-G: Registro por codigo de acceso — admin genera codigos en Filament, campo validacion.
- [ ] 1.x-H: Staff invite push + cambio de rol — push + socket + layout vendedor.

### Cleanup / Dev build
- [ ] QA visual multi-device (ZTE 360dp + Medium 411dp)
- [ ] Crop circular dark (react-native-image-crop-picker, requiere dev build)
- [ ] Push reminders probar en dev build (codigo listo)
- [ ] Push invalidation probar en dev build (codigo listo)
- [ ] Mensaje anclado chat tipo Twitch (nice to have)
- [ ] White-label: migrar app.json → app.config.js + estructura clients/ (ref: docs/WHITE-LABEL.md)

---

## Backend / Admin

### Analytics Dashboard (1.C1)
- [ ] Filament dashboard: ROI, engagement, asistencia por sesion, sponsors performance, leads por stand
- [ ] API endpoints para alimentar web app
- Ambos competidores lo tienen. Justifica el precio ante el cliente.

### Permisos
- [ ] 1.23: Permisos granulares Filament — roles admin diferenciados (org_admin, event_admin, moderator). Spatie ya instalado.

### Seguridad
- [ ] SEC-3.1: 2FA OTP (email/WhatsApp) — requiere WhatsApp Business API + pantalla app
- [ ] SEC-3.2: Device fingerprinting — depende de 2FA

### PDFs
- [ ] Certificados asistencia
- [ ] Reporte post-evento
- [ ] Reports exportables detallados

---

## Deploy / Infraestructura

- [ ] SEC-4: Docker Compose 6 servicios, VPS Hetzner, Cloudflare, backups. IMPORTANTE: al migrar a R2, revisar resolveStepsConfigUrls() y fixStorageUrl().
- [ ] SEC-5: Sentry, SecurityLogger, uptime monitoring
- [ ] GitHub Actions CI/CD: push a main → build → deploy
- [ ] EAS Build production profile (Android + iOS)
- Ref: docs/DISPONIBILIDAD-HA.md

---

## Web App (W.0–W.12)

- [ ] Setup Next.js 15 + Tailwind + shadcn/ui + Socket.IO client
- [ ] Spatial UI: paneles flotantes, pill bar, presets layout — NO sidebar
- [ ] 13 sesiones: setup, home, agenda, streaming+Q&A+chat, speakers, social, sponsors, networking, encuestas+gamification, notificaciones, sockets, polish
- Ref: docs/WEB-APP-PLAN.md

---

## Landing Web

- [ ] Landing premium: hero video, speakers, agenda, sponsors, registro embebido
- [ ] FAQ asistente: orbe animado + preguntas curadas (concepto aprobado)
- [ ] Endpoints publicos: /api/public/event/{slug}/speakers, agenda, sponsors, register
- Ref: docs/ROADMAP-UIUX-LANDING.md

---

## Features opcionales

- [ ] 1.C2: Wallet digital — Apple Wallet (.pkpass) + Google Wallet (JWT)
- [ ] 1.C4: Digital signage — pantallas venue. Base: proyecto checki.
- [ ] 1.C6: Badge printing fisico — impresora termica, add-on.
- [ ] Light mode — refactor colores a theme provider. Si cliente lo pide.

---

## Showcase / Demo inversor

- [ ] Panels finales clickeables (z-index blocking)
- [ ] Responsive (disenar a 1920x1080 base)
- [ ] Audio/sonido (cinematic riser + impacts)
- [ ] Hints/labels solapados
- [ ] Social wall mejor explicacion visual

---

## Fase 2 — Features complejos (cuando haya cliente)

### Experiencias interactivas
- [ ] Photo/Caption Contest — galeria con votos. Depende de social wall.
- [ ] Trivia live tipo Kahoot — preguntas en tiempo real, ranking por velocidad.
- [ ] Ruleta en vivo — backend detecta conectados, asigna puntos solo a presentes.
- [ ] Sorteo en vivo (jackpot) — slot machine, ganador + confetti. Requiere display.
- [ ] Momentos en Vivo branded — admin configura, publicar → push + socket + social + display.

### Comunicacion avanzada
- [ ] Video calls 1:1 (LiveKit) — sala efimera dentro del networking.
- [ ] Proximity chat (spatial audio) — tipo Gather. Solo web. Depende de web + LiveKit.
- [ ] Networking speed-dating virtual — match aleatorio, timer 3 min.

### Gamificacion avanzada
- [ ] Subasta de puntos — premios en tiempo real, timer 60s, bids via socket.
- [ ] Donde esta el patrocinador — juego visual, primeros 10 ganan puntos.

### Juegos / Integraciones
- [ ] Juegos Unity en stands — app como control (joystick). Lead automatico + puntos.
- [ ] Game Bridge (Unity <> App) — bridge socket, juegos ya existen.

---

## Fase 3 — SaaS + Monetizacion (cuando haya segundo cliente)

- [ ] 3.1: Multi-tenant + aislamiento de recursos
- [ ] 3.2: Stripe + facturacion (laravel/cashier)
- [ ] 3.3: Data export (Ley 1581/GDPR)
- [ ] 3.4: Juegos Unity + Socket.IO bridge

---

## Documentos de referencia

| Doc | Contenido |
|-----|-----------|
| `EventOS_Roadmap.md` | Fases, sesiones, timeline, dependencias |
| `docs/COMPLETADO.md` | Historial completo de todo lo hecho |
| `docs/PLAN-TAGS-MODULOS.md` | Plan tags + visibilidad modulos + layout unificado |
| `docs/ROADMAP-UIUX-LANDING.md` | Spec diseno: landing, estados evento, design system |
| `docs/WEB-APP-PLAN.md` | Spec web app: spatial UI, W.0-W.12, stack tecnico |
| `docs/ANALISIS-COMPETITIVO.md` | Cotizaciones reales, gaps, pricing, escala |
| `docs/WHITE-LABEL.md` | App config dinamico, clients/, EAS build |
| `docs/FASE-SEGURIDAD.md` | Auditoria OWASP, SEC-1 a SEC-5 |
| `docs/DISPONIBILIDAD-HA.md` | Arquitectura HA, deploy, costos |
| `docs/BUG-LOG.md` | Bugs historicos BUG-001 a BUG-078 |
| `docs/QA-MASTER.md` | Barrido 70+ endpoints, 21 modulos, 3 roles |
