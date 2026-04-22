# Pendientes — EventOS

> La UNICA fuente de verdad de lo que falta por hacer.
> Organizado por PRIORIDAD DE NEGOCIO, no por area tecnica.
> Filtro: "esto me acerca a cerrar el deal de septiembre con Eventos Efectivos?"
> Actualizado: 2026-04-21
> Backend: 582+ tests, 1664+ assertions
> Ref competencia: Cisco $88K USD, ICE360 $49M COP (docs/ANALISIS-COMPETITIVO.md)

---

## P0 — COMPLETADO

### Webhooks integracion badges — COMPLETADO (2026-04-21)
> 5 fases, 24 tests, 60 assertions, 5 bugs post-audit. Ver docs/ROADMAP-WEBHOOKS.md y COMPLETADO.md.

---

## P1 — Diferenciadores (la competencia NO lo tiene, nosotros SI podemos)

> Esto cierra deals. Ningun competidor ofrece juegos en vivo ni TV dashboard.

### Ruleta en vivo — 4-6h
- [ ] Filament activa ruleta → socket broadcast → app muestra animacion → asigna premio automatico
- [ ] Sponsors pagan por esta activacion
- [ ] Patron: 1 broadcast → todos ven lo mismo → resultado server-side (ref: bingo 4000 personas)

### Trivia live tipo Kahoot — 6-8h
- [ ] Timer + rapidez = puntos. Redis INCR server-side
- [ ] 5000 simultaneos validado por patron bingo
- [ ] Engagement masivo, diferenciador visual para demo

### Sorteo en vivo (jackpot) — 3-4h
- [ ] Slot machine con fotos de attendees → ganador + confetti + push
- [ ] Momento viral del evento, screenshots en redes

### Event Pulse — Dashboard live para CLIENTE (1.C7) — 12-16h
- [ ] Dashboard visual standalone, blanco, clean. NO es Filament, NO es MC, NO es admin
- [ ] TV mode / acuario del evento — el organizador solo mira, no controla
- [ ] Salas como burbujas/circulos — personas fluyendo con animacion al entrar/salir
- [ ] Activity feed visual: "Stand Amazon capturo lead", "Pedro reclamo camiseta"
- [ ] Click en sala → expande: quienes estan, engagement, speaker, estado sesion
- [ ] Metricas vivas (numeros animados, circulos que crecen), NO graficas de barras
- [ ] Stack: web standalone (HTML+GSAP o Next.js), Socket.IO RT, datos Redis+activity_log
- [ ] Backend ya emite 80% de los datos. Falta la capa visual.
- [ ] **"Ningun competidor tiene esto. Cierra deals solo. Viral por screenshot."**

### Foto mas votada — 2-3h
- [ ] Top fotos por likes → puntos bonus gamificacion → ranking premiado
- [ ] Ya existe base en photobooth, solo falta premio automatico

---

## P2 — Post-features (hacer DESPUES de terminar juegos/diferenciadores)

> Estos dependen de que features existan. Cada feature nuevo cambia que datos hay para analytics y que mostrar en el recap.

### Analytics Dashboard — 4-6h
- [ ] Filament dashboard: ROI, engagement, asistencia, sponsors, leads
- [ ] Justifica el precio ante la agencia
- [ ] Datos ya existen: session_stats, gamification, leads, networking, social, checkins
- [ ] **Hacer al final**: cada feature nuevo (ruleta, trivia, sorteo) agrega metricas. Si se hace antes, hay que rehacerlo.

### Recap compartible (reemplaza certificado PDF tradicional) — 6-8h
- [ ] Card/story visual con stats personales del attendee: sesiones, conexiones, puntos, ranking, fotos
- [ ] Diseño para compartir en redes (Instagram story format, LinkedIn post format)
- [ ] Branding del evento integrado (logo, colores, nombre)
- [ ] Boton "Compartir mi experiencia" en pantalla post-evento
- [ ] Opcionalmente incluye certificado de asistencia (horas, sesiones) como dato dentro del recap
- [ ] Mas viral y moderno que un PDF aburrido que nadie comparte

---

## P3 — Web App (Bancolombia virtual)

> Bancolombia pidio webapp. La competencia ya presento una (fea). Sin esto perdemos ese deal.
> Ref: docs/WEB-APP-PLAN.md

- [ ] W.0-W.1: Setup Next.js 15 + Spatial UI (pill nav, paneles max 3, presets)
- [ ] W.2: Home + branding + countdown
- [ ] W.3: Agenda + favoritos
- [ ] W.4: Streaming + chat + Q&A + polls
- [ ] W.5: Speakers
- [ ] W.6: Social wall
- [ ] W.7: Sponsors
- [ ] W.8: Networking
- [ ] W.9: Encuestas
- [ ] W.10: Notificaciones
- [ ] W.11: Sockets RT
- [ ] W.12: Polish
- [ ] Command palette, paneles arrastrables, presets

---

## P4 — Admin Filament polish (el cliente lo va a usar)

> El organizador de Eventos Efectivos opera desde Filament. Si esta en ingles o desordenado, se ve amateur.

### Filament UI Enterprise
- [ ] Nivel 1: columns, labels espanol, secciones con icon/description, custom theme
- [ ] Nivel 2: Tabs por recurso (EventBranding, Gamification, Registration)
- [ ] Nivel 3: Wizards features complejos
- [ ] Nivel 4: Dashboard evento con stats (conecta con Analytics Dashboard de P0)

---

## P5 — Seguridad pre-produccion

> No bloquea demo pero si bloquea Bancolombia (enterprise = compliance).

- [ ] SEC-3.1: 2FA OTP — codigo 6 digitos por email
- [ ] SEC-3.2: Device fingerprinting — login nuevo fuerza 2FA
- [ ] Magic link login — token un solo uso 15 min
- [ ] Session management — ver/cerrar dispositivos

---

## P6 — Deploy + Infra + Stress

> Bloquea testing real pero no bloquea desarrollo de features.

- [ ] SEC-4: Docker Compose, VPS Hetzner, Cloudflare, backups
- [ ] SEC-5: Sentry, SecurityLogger, uptime monitoring
- [ ] GitHub Actions CI/CD
- [ ] EAS Build production (Android + iOS)
- [ ] Stress test 10K: ver `docs/PLAN-STRESS-TEST.md`
- [ ] QA integridad funcional: smoke tests E2E + chaos testing (ver PLAN-STRESS-TEST.md)

---

## P7 — Landing Web (registro publico)

> Ultimo porque el registro puede hacerse por CSV/import hasta tener landing.

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

## Completados recientes (referencia, no pendientes)

### Session Lifecycle + Silent Disco + MC — COMPLETADO (2026-04-21)
> 23 tests, 59 assertions, 9 bugs (BUG-175 a BUG-183). Ver COMPLETADO.md.

### Room Check-in — Kiosko + Staff — COMPLETADO (Fases 0-4)
> Pendientes menores:
- [ ] Kiosko: verificar scan endpoint < 100ms en VPS real (Linux)
- [ ] Staff app: cola offline MMKV + batch sync (nice-to-have)
- [ ] Silent disco push notification — verificar con dev build real

### Mission Control — Pendiente menor
- [ ] **Games tab** — 5ta tab para ruleta/Kahoot/bingo/Unity. Depende de P1 juegos.

---

## Nice to have (NO hacer antes de cerrar deal septiembre)

> Mover a activo solo si un cliente lo pide explicitamente.

### App movil — cosmetico/incremental
- [ ] Racha de visitas a la app (streak gamification — dia consecutivo = bonus puntos)
- [ ] Orbe FAQ a Skia shader (reemplazar Reanimated+BlurView, solo cosmetic)
- [ ] Venue + Mapa (depende de si el evento tiene plano)
- [ ] Networking Tinder-style swipe (ya funciona con cards)
- [ ] react-native-image-crop-picker: crop circular dark

### Registro & Acceso avanzado
- [ ] Waitlist (cuando max_attendees se llena)
- [ ] Referral tracking
- [ ] Social login (Google)

### Comunicacion avanzada
- [ ] WhatsApp Business API (ICE360 lo tiene por $850K COP — evaluar si vale la pena)
- [ ] SMS fallback
- [ ] Email builder visual (Fase 2+)

### Post-evento
- [ ] Networking follow-up ("Conectaste con X personas")
- [ ] Highlight reel (collage automatico fotos)
- [ ] Event replay (grabaciones post-evento)

### Seguridad avanzada
- [ ] Anomaly detection — alertar admin (Fase 2+)
- [ ] Backup/Restore de evento (snapshot JSON)

### Platform Health — Dashboard interno
- [ ] Dashboard interno: salud plataforma RT
- [ ] Health por modulo: API, Socket, Redis, MySQL, Queue
- [ ] Metricas: requests/sec, latencia, memoria
- [ ] Stack: Laravel Pulse + Sentry

### Documentacion
- [ ] Documentar arquitectura socket
- [ ] White-label: migrar app.json → app.config.js + estructura clients/

### Features opcionales
- [ ] Wallet digital (.pkpass + Google Wallet)
- [ ] Digital signage (pantallas venue)
- [ ] Badge printing fisico
- [ ] Landing builder Filament (Fase 2+)
- [ ] A/B testing emails (Fase 2+)
- [ ] Donde esta el patrocinador (logo escondido, mini-game)
- [ ] Juegos Unity en stands (requiere dev Unity separado)

### Showcase / Demo inversor
- [ ] Panels clickeables, responsive 1920x1080, audio, hints, social wall

### Fase 3 — SaaS + Monetizacion
- [ ] Multi-tenant, Stripe, Data export GDPR, Juegos Unity bridge
- [ ] Multi-location con tracks (Bancolombia: Colombia + Panama)

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
| `docs/PLAN-STRESS-TEST.md` | Stress test 10K + QA integridad + chaos testing + calendario pre-prod |
| `docs/ROADMAP-LUX-V2.md` | Light mode completo |
