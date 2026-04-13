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

### FAQ asistente
- [ ] Backend: tabla event_faqs (event_id, section, question, answer_text, answer_action_url, answer_image_url, sort_order, is_active)
- [ ] Backend: CRUD Filament + API GET /events/{id}/faqs (publica, agrupada por seccion)
- [ ] App: FAB flotante con icono orbe → BottomSheet ~70% → categorias + preguntas tappables. FAQ contextual por pantalla.
- [ ] Orbe animado: blob organico 3 estados (idle/active/settled). Concepto aprobado, demo: faq-orb-demo.html
- Ref: docs/ROADMAP-UIUX-LANDING.md seccion 7

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
- [ ] FAQ landing: seccion con orbe animado + acordeon preguntas (misma tabla event_faqs)
- [ ] Endpoints publicos: /api/public/event/{slug}/speakers, agenda, sponsors, register, faqs
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

## MVP — Diferenciadores (implementar ANTES del pitch)

Estos features son los que nos separan de Cisco/ICE360. Sin ellos somos iguales.

### 1. Ruleta en vivo
- [ ] Presentador activa desde Filament → app muestra ruleta girando a todos los conectados
- [ ] Participantes automaticos: presenciales (checked_in_at) + virtuales (socket room). Zero botones.
- [ ] Cae en premio → asigna puntos/premio automaticamente → push al ganador
- [ ] Display venue: ruleta grande en pantalla. App: ruleta en el celular de cada asistente.
- [ ] Incentiva que la gente este atenta al celular durante el evento

### 2. Foto mas votada (gamificacion social)
- [ ] La foto con mas likes en el social wall genera puntos bonus de gamificacion
- [ ] Ranking de fotos por likes (periodo configurable: por dia, por evento)
- [ ] Top 3 fotos premiadas automaticamente (puntos o premio configurable)
- [ ] Se integra con social wall existente (wall_post_likes ya existe) + gamificacion existente
- [ ] NO es caption contest — es engagement organico con contenido real del evento

### 3. Sorteo en vivo (jackpot)
- [ ] Participantes automaticos: presenciales (checked_in_at) + virtuales (socket conectados). Zero botones.
- [ ] Admin activa desde Filament → countdown en app + display
- [ ] Slot machine con fotos de participantes girando en display venue + app
- [ ] Ganador: foto grande + confetti + push notification
- [ ] Puede filtrar por tags (ej: sorteo solo para VIP)

### 4. Trivia live tipo Kahoot
- [ ] Speaker/admin lanza pregunta desde Filament → aparece en app de todos los conectados
- [ ] Timer por pregunta (10-30s configurable). Respuesta correcta + rapidez = mas puntos.
- [ ] Ranking en tiempo real visible en display venue + app
- [ ] Base: sistema de encuestas S1.10 ya existe, refactor a modo competitivo
- [ ] RENDIMIENTO: evaluar cuidadosamente — 5000 respuestas simultaneas via socket. Posible solucion: batch responses, Redis counter, resultado calculado server-side.

### 5. Networking Tinder-style
- [ ] Dentro de networking, vista deslizable con sugeridos (matchmaking por intereses ya existe)
- [ ] Home networking muestra 2 cards sugeridos. "Ver todos" abre interfaz tipo Tinder.
- [ ] Swipe derecha = conectar, swipe izquierda = pasar. Sin timer (no es speed-dating forzado).
- [ ] Match mutuo → toast + puntos gamificacion + chat habilitado
- [ ] Reutiliza: suggested-contacts API, matchmaking por intereses, networking requests

### 6. Donde esta el patrocinador
- [ ] Juego visual: logo de un sponsor se "esconde" en una imagen del evento
- [ ] Aparece en app (push/modal) + display venue
- [ ] Timer: primeros 10 en encontrarlo ganan puntos
- [ ] Sponsor paga por la visibilidad — monetizacion directa
- [ ] Configurable desde Filament: sponsor, imagen, duracion, puntos

### 7. Juegos Unity en stands + Sponsor Game API
Flujo completo:
- [ ] Vendedor escanea QR del asistente → socket a Unity "jugador listo" + socket a app "modo joystick ON"
- [ ] App: nuevo slide contextual en gamification carousel "Estas jugando en [Stand]" → tap abre joystick overlay
- [ ] Joystick envia inputs (X/Y, botones) via socket → Unity en TV del stand los recibe
- [ ] Unity termina → socket "game:finished {score: 850}" → backend guarda score + puntos gamificacion
- [ ] App: slide desaparece, toast "Ganaste 50pts", lead capturado automatico
- [ ] Stand con feature "juego" activado en Filament → aparece opcion en Mi Stand
- [ ] Game Bridge: solo un relay socket — nosotros somos el cable, Unity maneja el estado del juego
- [ ] **Sponsor Game API**: sponsor con juego propio integra POST /api/v1/games/{game_id}/score con API key → score se suma a gamificacion + leaderboard. Una linea de codigo, su juego ya es parte del ecosistema EventOS.

---

## Nice to have (post-MVP)

### Momentos en Vivo branded
- [ ] Admin configura desde Filament: sponsor, tipo, titulo, ganador, logo
- [ ] Publicar → push + socket + social wall + display venue
- [ ] Un componente, infinitos usos: sorteos, reconocimientos, anuncios, hackatones

### Video calls 1:1 (LiveKit)
- [ ] Sala efimera dentro del networking. Requiere infra media server.

### Proximity chat (spatial audio)
- [ ] Tipo Gather. Solo web app. Depende de web + LiveKit.

### Subasta de puntos
- [ ] Premios en tiempo real, timer 60s, bids via socket.

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
