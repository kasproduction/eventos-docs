# Pendientes — EventOS

> La UNICA fuente de verdad de lo que falta por hacer.
> Organizado por area de trabajo, no por prioridad.
> Actualizado: 2026-04-18
> Backend: 488+ tests
> Fuentes cruzadas: ROADMAP-UIUX-LANDING.md, WEB-APP-PLAN.md, EventOS_Roadmap.md

---

## App movil

### Bugs reportados (2026-04-18)
- [ ] Bug: reload en Expo manda al onboarding — verificar si es hot reload dev o flujo roto en produccion
- [ ] Bug: notificacion de ban llega aunque usuario no inicio sesion / esta en onboarding — verificar push lifecycle
- [ ] Session detail no tiene el aspecto visual de las otras pantallas (inconsistencia UI)
- [x] Bug: PollSlides estrellas rojas en Lux — fix: color gold fijo #F5B740, sombras eliminadas (2026-04-18)
- [x] Bug: PinnedBanner invisible (surface.medium = transparent) — fix: backgroundElevated + sin socket extra (2026-04-18)
- [x] Bug: app crash RangeError status 0 cuando socket/backend cae — fix: ApiError NETWORK_ERROR + retry inteligente (2026-04-18)

### Features app pendientes
- [ ] Speakers destacados deben rotar (carrusel o random en home)
- [ ] Racha de visitas a la app (streak gamification — dia consecutivo = bonus puntos)

### Orbe FAQ a Skia shader — Media-Alta | 4-6h
- [ ] Reemplazar Reanimated+BlurView por @shopify/react-native-skia (solo componente OrbBlob cambia)
- [ ] Ref visual: design/faq-orb-demo.html


### Venue + Mapa — Media | 4-6h
- [ ] Modulo app: pantalla mapa del venue (plano del recinto, stands, escenarios)
- [ ] Visibility: `checked_in` only
- [ ] Landing web: seccion venue con Google Maps embed
- [ ] Admin Filament: subir imagen plano venue, configurar zonas/labels

### Crop circular dark — Nice to have
- [ ] react-native-image-crop-picker: crop circular dark (ya tiene dev build)

### White-label — Nice to have (post-MVP)
- [ ] Migrar app.json → app.config.js + estructura clients/ (ref: docs/WHITE-LABEL.md)

---

## Bancolombia — Requerimientos especificos

> Contexto: reunion 2026-04-14. Formato: silent disco + multi-pais.

### Silent Disco — Toggle por evento
- [ ] Backend: `silent_disco_enabled`, tabla `session_attendances`, API join/leave
- [ ] Socket: contadores RT. App: boton "Asistir" + cambiar sesion
- [ ] Filament: vista asistencia por sesion. Metricas engagement cruzado

### Multi-location con tracks
- [ ] Crear tracks por ciudad/pais, agenda filtra por track

### Webhooks — Integracion con partners
- [ ] Outbound: attendee.registered/updated/approved con retry
- [ ] Inbound: POST /api/v1/webhooks/checkin con API key
- [ ] API keys por partner, rate limiting

### Mission Control — Pendientes
- [ ] **Games tab** — 5ta tab "Games" o "Interactivo" para ruleta/Kahoot/bingo/Unity. Depende del backend de juegos.
- [ ] **Responsive tablet 1024px** — Pulir iPad landscape (backstage).
- [ ] **Prototipo design/Monitor** — Referencia historica, no mantener.


---

## Backend / Admin

### Sponsors — Log de actividad + estadisticas
- [ ] Log por sponsor: quien escaneo lead, quien exporto, cuando, desde donde
- [ ] Leads estadisticas diarias (no solo totales) — grafico por dia
- [ ] Dashboard al hacer click en un sponsor — metricas engagement

### Sesiones — Estadisticas post-sesion
- [ ] Cuantos ingresaron al streaming, duracion promedio, engagement (chat/Q&A/polls)
- [ ] Vista resumen al finalizar sesion (Filament + posible API)

### Uptime / Monitoreo
- [ ] Health check endpoint, uptime monitoring, alertas cuando cae

### Rendimiento — Verificar carga encuestas
- [ ] Verificar que Q&A + polls no generen polling oculto que afecte rendimiento servidor

### Analytics Dashboard (1.C1)
- [ ] Filament dashboard: ROI, engagement, asistencia, sponsors, leads

### Permisos
- [ ] Permisos granulares Filament — roles admin diferenciados. Spatie ya seedeado, falta wiring.

### Seguridad
- [ ] SEC-3.1: 2FA OTP — codigo 6 digitos por email
- [ ] SEC-3.2: Device fingerprinting — login nuevo fuerza 2FA
- [ ] Magic link login — token un solo uso 15 min
- [ ] Session management — ver/cerrar dispositivos
- [ ] Anomaly detection — alertar admin (Fase 2+)

### Backup/Restore de evento
- [ ] Snapshot JSON completo, importar/restaurar, versionado

### PDFs
- [ ] Certificados asistencia, reporte post-evento, reports exportables

### Comunicacion avanzada
- [ ] WhatsApp Business API, SMS fallback, email builder visual (Fase 2+)

### Branded QR codes
- [ ] QR codes con logo del evento embebido

### Filament UI Enterprise — Pulido admin
- [ ] Nivel 1: columns, labels espanol, secciones con icon/description, custom theme
- [ ] Nivel 2: Tabs por recurso (EventBranding, Gamification, Registration)
- [ ] Nivel 3: Wizards features complejos
- [ ] Nivel 4: Dashboard evento con stats

### Documentacion tecnica
- [ ] Documentar arquitectura socket

---

## Deploy / Infraestructura

- [ ] SEC-4: Docker Compose, VPS Hetzner, Cloudflare, backups
- [ ] SEC-5: Sentry, SecurityLogger, uptime monitoring
- [ ] GitHub Actions CI/CD
- [ ] EAS Build production (Android + iOS)

---

## Landing Web

### Secciones
- [ ] Hero, Sobre el evento, Speakers, Agenda, Sponsors, Venue, Testimonios, Galeria, FAQ, Footer

### Registro embebido
- [ ] Form integrado, progressive profiling, social proof, CAPTCHA, rate limiting

### Post-registro
- [ ] Confirmacion web + QR descarga app

### Endpoints publicos
- [ ] GET /api/public/event/{slug} (datos, speakers, agenda, sponsors, faqs, registration-count)
- [ ] POST /api/public/event/{slug}/register (rate limited + CAPTCHA)

### Stack
- [ ] Next.js SSG/ISR o Astro, Tailwind, Framer Motion/GSAP, SEO/OG, Responsive, Dark/Light

---

## Web App (W.0-W.12)

- [ ] W.0-W.1: Setup Next.js 15 + Spatial UI
- [ ] W.2-W.12: Home, Agenda, Streaming, Speakers, Social, Sponsors, Networking, Encuestas, Notificaciones, Sockets, Polish
- [ ] Command palette, paneles arrastrables, presets

---

## Registro & Acceso avanzado

- [ ] Waitlist, Referral tracking, Social login (Google)

---

## Post-evento

- [ ] Certificado PDF, Networking follow-up, Highlight reel, Event replay

---

## Features opcionales

- [ ] Wallet digital (.pkpass + Google Wallet)
- [ ] Digital signage (pantallas venue)
- [ ] Badge printing fisico
- [ ] Landing builder Filament (Fase 2+)
- [ ] A/B testing emails (Fase 2+)

---

## MVP — Diferenciadores

### 1. Ruleta en vivo
- [ ] Filament activa → app muestra ruleta → asigna premio automatico

### 2. Foto mas votada
- [ ] Top fotos por likes → puntos bonus gamificacion → ranking premiado

### 3. Sorteo en vivo (jackpot)
- [ ] Slot machine con fotos → ganador + confetti + push

### 4. Trivia live tipo Kahoot
- [ ] Timer + rapidez = puntos. Redis INCR server-side (5000 simultaneos)

### 5. Networking Tinder-style
- [ ] Swipe sugeridos → match mutuo → toast + puntos + chat

### 6. Donde esta el patrocinador
- [ ] Logo escondido en imagen, timer, puntos

### 7. Juegos Unity en stands
- [ ] QR → socket → joystick app → Unity TV → score → puntos

---

## Showcase / Demo inversor

- [ ] Panels clickeables, responsive 1920x1080, audio, hints, social wall

---

## Nice to have (post-MVP)

- [ ] Momentos en Vivo branded, Video calls 1:1, Proximity chat, Subasta puntos, Speed-dating

---

## Fase 3 — SaaS + Monetizacion

- [ ] Multi-tenant, Stripe, Data export GDPR, Juegos Unity bridge

---

## Documentos de referencia

| Doc | Contenido |
|-----|-----------|
| `EventOS_Roadmap.md` | Fases, sesiones, timeline |
| `docs/COMPLETADO.md` | Historial completo |
| `docs/PLAN-TAGS-MODULOS.md` | Plan tags + visibilidad modulos |
| `docs/ROADMAP-UIUX-LANDING.md` | Spec diseno landing + UI |
| `docs/WEB-APP-PLAN.md` | Spec web app spatial UI |
| `docs/ANALISIS-COMPETITIVO.md` | Cotizaciones, gaps, pricing |
| `docs/WHITE-LABEL.md` | App config dinamico |
| `docs/FASE-SEGURIDAD.md` | Auditoria OWASP |
| `docs/DISPONIBILIDAD-HA.md` | Arquitectura HA, deploy |
| `docs/BUG-LOG.md` | Bugs historicos |
| `docs/QA-MASTER.md` | Barrido endpoints |
| `docs/ROADMAP-LUX-V2.md` | Light mode completo |
