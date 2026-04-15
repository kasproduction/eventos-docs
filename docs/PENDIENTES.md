# Pendientes — EventOS

> La UNICA fuente de verdad de lo que falta por hacer.
> Organizado por area de trabajo, no por prioridad.
> Actualizado: 2026-04-15
> Backend: 397 tests, 1009 assertions
> Fuentes cruzadas: ROADMAP-UIUX-LANDING.md, WEB-APP-PLAN.md, EventOS_Roadmap.md

---

## App movil

### Setup wizard evento (Filament) — COMPLETADO (2026-04-15)
- [x] Wizard 5 pasos: basicos, fechas, registro, apariencia, modulos seleccionables + confirmar
- [x] Modulos con checkboxes (pre-seleccion por template, toggle individual)
- [x] Auto-crea modulos con roles correctos (attendee, no presencial/virtual)
- [x] Accesible desde boton "Nuevo evento" en Branding (no nav separado)
- [x] Fix: ModuleTemplate roles legacy presencial/virtual → attendee (27 templates corregidos)

### Filament UI Enterprise — Pulido completo admin
El panel Filament actual es funcional pero generico. Campos numericos full-width, sin jerarquia visual, helpers con jerga dev, idioma mezclado. Necesita pulido para presentar a clientes.

**Nivel 1 — Barrido layout (1 dia):**
- [ ] Todas las secciones `->columns(2)` o `->columns(3)` — NUNCA campos full-width
- [ ] Campos numericos/toggles `->columnSpan(1)`
- [ ] Labels y helpers 100% espanol humano (no "passport_enabled", no "0 = todos los stands")
- [ ] Breadcrumbs y titulos en espanol
- [ ] Secciones con `->icon()` y `->description()`
- [ ] Custom theme: color azul/slate en vez de naranja default

**Nivel 2 — Reorganizar con Tabs (1 dia):**
- [ ] EventBranding → tabs: General | Apariencia | Hero | Equipo
- [ ] GamificationSettings → tabs: Acciones | Premios | Reglas
- [ ] RegistrationSettings → tabs: Modo | Restricciones | Campos | Aprobacion
- [ ] Recursos grandes sin scroll infinito

**Nivel 3 — Wizards features complejos (2 dias):**
- [ ] Wizard gamificacion: acciones+puntos → premios → reglas/limites → activar
- [ ] Wizard registro: modo → restricciones → campos → aprobacion
- [ ] Wizard comunicacion: canales → templates → programados

**Nivel 4 — Dashboard evento (1 dia):**
- [ ] Widget stats en edit page: asistentes, sesiones, speakers, sponsors
- [ ] Estado del evento visible, cards de acceso rapido a config
- [ ] Contexto inmediato al entrar (que evento es, cuantos registrados)

### Venue + Mapa
- [ ] Modulo app: pantalla mapa del venue (plano del recinto, stands, escenarios). Imagen estatica + zonas tappables o Google Maps embed
- [ ] Visibility: `checked_in` only (solo presenciales). Ya soportado por motor de modulos
- [ ] Landing web: seccion venue con Google Maps embed + indicaciones
- [ ] Admin Filament: subir imagen plano venue, configurar zonas/labels

### Light Mode / Theming — CRITICO para clientes
Hay marcas que no son afines al negro. Un evento de Bancolombia, una marca de salud, educacion, etc. necesita fondo claro. Tambien accesibilidad (dificultad visual).

- [ ] **Refactor ThemeProvider**: extraer TODOS los colores hardcodeados a tokens semanticos (background, surface, text-primary, text-secondary, border, etc.)
- [ ] **573 archivos** tienen `rgba(255,255,255,0.x)`, `#0e0e0e`, `#0a0a0f` directo. Migrar a `theme.colors.x`
- [ ] **Lumina Noir** (dark, actual) como tema default
- [ ] **Lumina Lux** (light) como segundo tema
- [ ] **Toggle** en config del evento (Filament): tema default del evento. El asistente puede overridear desde perfil.
- [ ] **High contrast mode**: opacidades minimas 0.5+ para texto secundario
- [ ] **Font scaling**: respetar Dynamic Type / preferencias del sistema
- Estimacion: 2-3 dias refactor completo (573 archivos)

### Documentacion tecnica
- [ ] Documentar arquitectura socket: una conexion, eventos, rendimiento, escalabilidad. Ref: project_socket_architecture.md

### Cleanup / Dev build
- [ ] **Upgrade orbe FAQ a Skia shader** — reemplazar Reanimated+BlurView por @shopify/react-native-skia (solo componente OrbBlob cambia). Ref visual: design/faq-orb-demo.html
- [ ] QA visual multi-device (ZTE 360dp + Medium 411dp)
- [ ] Crop circular dark (react-native-image-crop-picker, requiere dev build)
- [x] Push notifications probadas en dev build fisico (wireless debugging, 2026-04-15)
- [x] Push tap navigation probada (support_resolved, announcement, agenda_reminder)
- [ ] Mensaje anclado chat tipo Twitch — admin ancla mensaje, socket `chat:pinned`, banner fijo app, auto-expira
- [ ] White-label: migrar app.json → app.config.js + estructura clients/ (ref: docs/WHITE-LABEL.md)
- [ ] Fix Reanimated warning: StatCard transform + layout animation overlap (cosmetic, no funcional)
- [ ] Extraer componentes base reutilizables (Button, Card, Input) — paso 1 ROADMAP-UIUX pendiente

---

## Bancolombia — Requerimientos especificos

> Contexto: reunion 2026-04-14. Competencia mostro webapp (fea, 60 dias).
> Bancolombia necesita flujo completo webapp + landing + experiencia.
> Formato especial: "sonido silencioso" (silent disco) — un salon, audifonos, dos charlas simultaneas.
> Eventos multi-pais (Colombia + Panama) y multi-ciudad (Bogota + Medellin).

### Silent Disco — Toggle por evento
Feature toggle: solo se activa cuando el evento usa sistema de audifonos con canales.

- [ ] Backend: `silent_disco_enabled` en config del evento (Filament toggle)
- [ ] Backend: tabla `session_attendances` (event_session_id, attendee_id, token UUID, joined_at, left_at)
- [ ] Backend: API `POST /sessions/{id}/join` + `DELETE /sessions/{id}/leave`
- [ ] Backend: Scoping — Q&A, polls, ratings filtran por attendees que hicieron join a ESA sesion
- [ ] Socket: `session:joined` / `session:left` → contadores en tiempo real
- [ ] App: Agenda agrupa sesiones simultaneas (mismo room + mismo horario)
- [ ] App: Boton "Asistir" + cambiar de sesion + indicador visual "Estas en esta charla"
- [ ] Filament: Vista asistencia por sesion (quien estuvo en cual, duracion)
- [ ] Metricas: engagement por charla cruzado con asistencia

### Multi-location con tracks
Tracks ya implementados (S1.12). Solo requiere uso correcto:

- [ ] Crear tracks por ciudad/pais: "Bogota", "Medellin", "Panama"
- [ ] Agenda filtra por track → asistente ve solo charlas de su ubicacion
- [ ] Sesiones simultaneas sin filtro: aparecen stacked con TrackBadge diferenciando

### Webhooks — Integracion con partners de registro/badges

**Outbound (EventOS → Partner):**
- [ ] Webhook configurable por evento en Filament (URL destino + secret key)
- [ ] Eventos: `attendee.registered`, `attendee.updated`, `attendee.approved`
- [ ] Payload: id, name, email, company, role, tags, photo_url, qr_identifier
- [ ] Retry con backoff (3 intentos) via queue job
- [ ] Alternativa pull: API key para partner → `GET /api/v1/events/{id}/attendees`

**Inbound (Partner → EventOS):**
- [ ] `POST /api/v1/webhooks/checkin` autenticado con API key por partner
- [ ] Backend: actualiza `checked_in_at` → dispara socket → modulos se actualizan
- [ ] Reutiliza: misma logica que el kiosco (S1.4), solo cambia el origen

**Seguridad:**
- [ ] API keys por partner (generadas en Filament, hash en BD)
- [ ] Rate limiting por API key

### Mission Control — Display en vivo (evento + pitch)
Solo escucha socket — CERO carga extra al server. Doble uso: venue + demo pitch.

- [ ] Web page standalone (HTML + Socket.IO client + GSAP/CSS animations)
- [ ] Contador en vivo: presenciales + virtuales + total
- [ ] Feed de actividad + feed gamificacion con animaciones
- [ ] Sesion activa + encuesta/trivia barras RT + ruleta + leaderboard
- [ ] Auth: token admin o URL con secret
- [ ] Responsive: 1920x1080 (display venue) + 1280x720 (laptop pitch)

---

## Backend / Admin

### Analytics Dashboard (1.C1)
- [ ] Filament dashboard: ROI, engagement, asistencia por sesion, sponsors performance, leads por stand
- [ ] API endpoints para alimentar web app
- Ambos competidores lo tienen. Justifica el precio ante el cliente.

### Permisos
- [ ] 1.23: Permisos granulares Filament — roles admin diferenciados (org_admin, event_admin, moderator). Spatie YA tiene 7 roles + 13 permisos seedeados, falta wiring policies + Shield plugin.

### Seguridad
- [x] SEC-6.1: Rate limit networking — 100/evento, 30/dia. 3 tests. COMPLETADO 2026-04-15.
- [ ] **SEC-6.2: Rate limit endpoints de escritura — CRITICO pre-deploy.** Copiar patron networking a: wall posts (10/dia), comments (30/dia), Q&A questions (10/dia/sesion), support (5/dia), photos (20/dia), leads (60/min throttle). ~2-3 horas. Ref: docs/FASE-SEGURIDAD.md SEC-6.
- [ ] SEC-3.1: 2FA OTP — codigo 6 digitos por email (5 min TTL). WhatsApp Business API opcional.
- [ ] SEC-3.2: Device fingerprinting — login desde dispositivo nuevo fuerza 2FA. Tabla `user_devices`. Depende de 2FA.
- [ ] Magic link login — click enlace email, token un solo uso 15 min. Alternativa a contrasena.
- [ ] Session management — usuario ve dispositivos logueados, puede cerrar sesiones remotas.
- [ ] Anomaly detection — alertar admin si logins sospechosos, registros masivos misma IP. (Fase 2+)

### Backup/Restore de evento (tipo Minecraft world save)
- [ ] Snapshot completo: onboarding, registration_fields, modules, sponsors, agenda, gamification
- [ ] Exportar como JSON descargable + Importar/restaurar desde JSON
- [ ] Versionado con label y descripcion (v1, v2, v3...)
- [ ] Modulo Filament dedicado

### PDFs
- [ ] Certificados asistencia (PDF branding + nombre + horas). Requiere barryvdh/laravel-dompdf.
- [ ] Reporte post-evento (resumen metricas)
- [ ] Reports exportables detallados

### Comunicacion avanzada
- [ ] Configuracion canales por evento (email/WhatsApp/SMS toggles en Filament)
- [ ] WhatsApp Business API — templates configurables, envio masivo (confirmacion, recordatorios)
- [ ] SMS fallback — mensaje corto con link descarga app
- [ ] Email builder visual Filament — editor templates sin codigo. (Fase 2+)
- [ ] Push reminders configurables — "Tu sesion empieza en 15 min". Admin configura en Filament. (codigo base listo S1.14)
- [ ] Calendar invite .ics en email confirmacion — adjunto automatico. Backend ICS ya existe en AgendaController.

### Branded QR codes
- [ ] QR codes con logo del evento embebido en el centro. Generacion server-side.

---

## Deploy / Infraestructura

- [ ] SEC-4: Docker Compose 6 servicios, VPS Hetzner, Cloudflare, backups. IMPORTANTE: al migrar a R2, revisar resolveStepsConfigUrls() y fixStorageUrl().
- [ ] SEC-5: Sentry, SecurityLogger, uptime monitoring
- [ ] GitHub Actions CI/CD: push a main → build → deploy
- [ ] EAS Build production profile (Android + iOS). iOS requiere Mac + Apple Developer ($99/ano).
- Ref: docs/DISPONIBILIDAD-HA.md

---

## Landing Web
Ref: docs/ROADMAP-UIUX-LANDING.md (secciones 1, 3, 9, 10)

### Secciones de la landing
- [ ] **Hero**: video ambient loop + countdown + CTA "Registrate"
- [ ] **Sobre el evento**: descripcion, fecha, ubicacion, highlights
- [ ] **Speakers**: carrusel/grid con foto, nombre, cargo. Hover bio expandible
- [ ] **Agenda resumida**: vista por tracks/dias, teaser
- [ ] **Sponsors/Partners**: logos con links, configurable
- [ ] **Venue + Mapa**: foto venue + Google Maps embed + indicaciones
- [ ] **Testimonios**: quotes de ediciones pasadas o speakers confirmados
- [ ] **Galeria**: fotos/videos de ediciones anteriores
- [ ] **FAQ**: orbe animado + acordeon preguntas (misma tabla event_faqs)
- [ ] **Footer**: redes sociales, links legales, contacto

### Registro embebido en landing
- [ ] Form integrado (modal o seccion), campos dinamicos desde Filament (ya implementado S1.22)
- [ ] Progressive profiling: landing solo pide esenciales. Extras en app al login
- [ ] Social proof: contador en vivo "X personas ya registradas"
- [ ] CAPTCHA: reCAPTCHA v3 invisible
- [ ] Rate limiting: max 5 registros por IP en 15 min

### Pantalla confirmacion post-registro
- [ ] Web: logo evento + "Te registraste exitosamente" + datos + QR descarga app (detecta OS) + links stores
- [ ] "Revisa tu correo para mas info"

### Endpoints publicos necesarios (API)
- [ ] `GET /api/public/event/{slug}` — datos publicos evento
- [ ] `GET /api/public/event/{slug}/speakers` — speakers publicados
- [ ] `GET /api/public/event/{slug}/agenda` — agenda resumida
- [ ] `GET /api/public/event/{slug}/sponsors` — sponsors con logo+link
- [ ] `POST /api/public/event/{slug}/register` — registro (rate limited + CAPTCHA)
- [ ] `GET /api/public/event/{slug}/registration-count` — contador social proof
- [ ] `GET /api/public/event/{slug}/faqs` — preguntas frecuentes

### Aspectos tecnicos landing
- [ ] Stack: Next.js SSG/ISR o Astro. Tailwind CSS. Framer Motion o GSAP.
- [ ] SEO/OG tags: Open Graph meta para WhatsApp, Twitter, LinkedIn
- [ ] Responsive mobile-first, Lighthouse >90, WCAG 2.1 AA
- [ ] Parallax scroll, fade-in secciones, hover states premium
- [ ] Dark/Light toggle (Lumina Noir / Lumina Lux)

---

## Web App (W.0-W.12)
Ref: docs/WEB-APP-PLAN.md

- [ ] W.0-W.1: Setup Next.js 15 + Tailwind + shadcn/ui + Socket.IO + Spatial UI (paneles, pill bar, presets)
- [ ] W.2: Home + Happening Now persistente
- [ ] W.3: Agenda + filtros + Mi Agenda
- [ ] W.4: Streaming + Q&A + Chat (core virtual)
- [ ] W.5: Speakers
- [ ] W.6: Social (feed + memorias)
- [ ] W.7: Sponsors
- [ ] W.8: Networking
- [ ] W.9: Encuestas + Gamification
- [ ] W.10: Notificaciones
- [ ] W.11: Sockets real-time
- [ ] W.12: Polish + responsive + performance
- [ ] Command palette (Cmd+K) para power users
- [ ] Paneles arrastrables + memoria layout por usuario
- [ ] Presets: "Modo conferencia", "Modo networking", "Modo explorar" (agenda+speakers+mapa)

---

## Registro & Acceso avanzado

### Pendientes
- [ ] Waitlist — cuando max_attendees se llena, lista de espera. Notificacion automatica cuando hay cupo.
- [ ] Referral tracking — "Comparte tu link personal" → trackear quien invito a quien. Integracion gamificacion.
- [ ] Social login (Google) — "Registrate con Google" como opcion adicional. OAuth2.

---

## Post-evento

### Pendientes
- [ ] Certificado asistencia (PDF) — branding evento + nombre + horas. Descargable y compartible.
- [ ] Networking follow-up — resumen "Conectaste con X personas" + boton follow-up (email/LinkedIn).
- [ ] Highlight reel — collage automatico fotos photobooth + social wall.
- [ ] Event replay — grabaciones de sesiones disponibles en modo `ended`. Si URL recording existe, mostrar.

---

## Features opcionales

- [ ] 1.C2: Wallet digital — Apple Wallet (.pkpass) + Google Wallet (JWT). Pase con QR incluido.
- [ ] 1.C4: Digital signage — pantallas venue. Base: proyecto checki.
- [ ] 1.C5: Calendar sync (.ics por sesion) — backend funcional en AgendaController, falta boton visible en app.
- [ ] 1.C6: Badge printing fisico — impresora termica, add-on.
- [ ] Landing builder Filament — seleccionar secciones, reordenar, cambiar textos/imagenes. (Fase 2+)
- [ ] A/B testing emails — subject lines, open rate. (Fase 2+)
- [ ] Preview landing desde admin — ver como queda sin publicar.

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
- [ ] Presentador activa desde Filament → app muestra ruleta girando a todos
- [ ] Participantes automaticos: checked_in + socket room. Zero botones.
- [ ] Cae en premio → asigna puntos/premio automatico → push al ganador
- [ ] Display venue + app

### 2. Foto mas votada (gamificacion social)
- [ ] Top fotos por likes en social wall → puntos bonus gamificacion
- [ ] Ranking por periodo (dia/evento), top 3 premiadas automaticamente
- [ ] Reutiliza wall_post_likes + gamificacion existente

### 3. Sorteo en vivo (jackpot)
- [ ] Participantes automaticos. Admin activa → countdown → slot machine con fotos
- [ ] Ganador: foto grande + confetti + push. Filtro por tags (VIP).

### 4. Trivia live tipo Kahoot
- [ ] Admin lanza pregunta → timer → respuesta correcta + rapidez = puntos
- [ ] Ranking RT en display + app. Base: encuestas S1.10, refactor competitivo
- [ ] RENDIMIENTO: 5000 respuestas simultaneas. Redis INCR server-side.

### 5. Networking Tinder-style
- [ ] Vista deslizable con sugeridos (matchmaking ya existe)
- [ ] Swipe derecha = conectar, izquierda = pasar. Match mutuo → toast + puntos + chat

### 6. Donde esta el patrocinador
- [ ] Juego visual: logo sponsor escondido en imagen evento
- [ ] Timer: primeros 10 ganan puntos. Sponsor paga por visibilidad.

### 7. Juegos Unity en stands + Sponsor Game API
- [ ] Vendedor escanea QR → socket Unity "jugador listo" + app "modo joystick ON"
- [ ] Joystick envia inputs via socket → Unity en TV
- [ ] Unity termina → score → puntos gamificacion. Lead capturado automatico.
- [ ] **Sponsor Game API**: POST /api/v1/games/{game_id}/score con API key

---

## Nice to have (post-MVP)

- [ ] Momentos en Vivo branded — sponsor, tipo, titulo, ganador. Push + socket + social wall + display
- [ ] Video calls 1:1 (LiveKit) — sala efimera networking. Requiere media server ($$$).
- [ ] Proximity chat (spatial audio) — tipo Gather. Solo web app. Depende de web + LiveKit.
- [ ] Subasta de puntos — premios RT, timer 60s, bids via socket.
- [ ] Networking speed-dating — timer por conversacion, match automatico. Depende de videollamada.

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
| `docs/ROADMAP-UIUX-LANDING.md` | Spec diseno: landing, estados evento, design system, seguridad, features premium |
| `docs/WEB-APP-PLAN.md` | Spec web app: spatial UI, W.0-W.12, stack tecnico |
| `docs/ANALISIS-COMPETITIVO.md` | Cotizaciones reales, gaps, pricing, escala |
| `docs/WHITE-LABEL.md` | App config dinamico, clients/, EAS build |
| `docs/FASE-SEGURIDAD.md` | Auditoria OWASP, SEC-1 a SEC-5 |
| `docs/DISPONIBILIDAD-HA.md` | Arquitectura HA, deploy, costos |
| `docs/BUG-LOG.md` | Bugs historicos BUG-001 a BUG-103 |
| `docs/QA-MASTER.md` | Barrido 70+ endpoints, 21 modulos, 3 roles |
