# Pendientes — EventOS

> La UNICA fuente de verdad de lo que falta por hacer.
> Organizado por area de trabajo, no por prioridad.
> Actualizado: 2026-04-14

---

## App movil

### Tags + Modulos + Layout unificado
Plan completo en `docs/PLAN-TAGS-MODULOS.md`.

**Backend COMPLETADO** (commit 30ce854, 2026-04-14):
- [x] Eliminar roles presencial/virtual, todos son "attendee"
- [x] Tags JSON en attendee (vip, prensa, etc.) — admin editable en Filament
- [x] Modulos con visibility_presence (all/checked_in/not_checked_in) + visibility_tags
- [x] QR para todos (identidad, no solo ticket)
- [x] API ModuleController filtra por role + presencia + tags
- [x] 48 archivos, 314 tests passing

**App COMPLETADO** (commit 810cc89, 2026-04-14):
- [x] Layout app unificado (merge presencial/virtual tabs → tabs unico)
- [x] Cleanup app: authStore types, homeRouteForRole, eliminar (presencial)/(virtual)
- [x] 14 archivos eliminados, 6 creados, 9 actualizados. TS ok, bundle ok.

**CSV con tags COMPLETADO** (commit 4165130):
- [x] CSV import soporta columna tags (auto-detect: tags/etiquetas/grupos)

**Socket checkin COMPLETADO** (commit 267ec45):
- [x] App escucha checkin:update → invalida modules del propio usuario
- [x] Debounce 800ms para invalidaciones socket (commit 167c72c)

**Pre-registro CSV — COMPLETADO (2026-04-14):**
- [x] CSV import crea User+Attendee+QR con invitation_token
- [x] InvitationMail con deep link /join/{token}
- [x] Deep link redirect → app activate-account (produccion)
- [x] Fallback sin deep link: check-email devuelve token → app redirige directo a activacion
- [x] Pantalla activate-account Lumina Noir (crear contrasena)
- [x] Post-activacion → onboarding (foto, forms, intereses)
- [x] Feature toggle: password_mode configurable (invitation_link | phone_as_password)

### Estados del evento — COMPLETADO (2026-04-14)
- [x] 1.x-D: Lifecycle — 4 estados: draft/registration/published/live/ended
- [x] Countdown DaVinci (normal + compact) + "El evento comienza hoy" cuando expira
- [x] Modo archivo post-evento: banner + stats + links a agenda/social/gamification/speakers
- [x] Modalidad badge: presencial/virtual/hibrido en InfoCard
- [x] EventInfoCard: modalidad, venue, registrados, cierre registro
- [x] About pre-evento: modulo opcional, toggle habilitado, imagen+texto+links, pantalla dedicada
- [x] ModuleMenu compact en published (cards 56px)
- [x] Config en Filament: status, modality, fechas, venue, capacidad, about condicional
- [x] Admins ven todos los modulos (skip role filter)
- [x] Migration roles en modules JSON (presencial→attendee)

### Session Detail — COMPLETADO (2026-04-14)
- [x] Pantalla detalle de sesion (espejo de speaker detail)
- [x] Badges (EN VIVO, tipo, track), titulo, rating, time/location, capacidad
- [x] Botones: Favorita, Calendario, Evaluar, UNIRTE / Ver grabacion
- [x] Speakers tappables → /speaker/[id]
- [x] Navegacion circular: Agenda → Session → Speaker → Session
- [x] Agenda card tap → session detail (antes iba directo a stream)

### Onboarding — COMPLETADO (2026-04-14)
- [x] 1.x-E-D: Campos condicionales — depends_on en config JSON. Pais→ciudades dinamicas
- [x] Replay diferenciado: pre-fill foto/profile/custom/intereses, sin confetti, sin puntos dobles
- [x] onboarding_data JSON en attendees para campos custom (country, city, etc.)
- [x] 9 bugs resueltos (BUG-079 a BUG-087)

### FAQ asistente
- [ ] Backend: tabla event_faqs (event_id, section, question, answer_text, answer_action_url, answer_image_url, sort_order, is_active)
- [ ] Backend: CRUD Filament + API GET /events/{id}/faqs (publica, agrupada por seccion)
- [ ] App: FAB flotante con icono orbe → BottomSheet ~70% → categorias + preguntas tappables. FAQ contextual por pantalla.
- [ ] Orbe animado: blob organico 3 estados (idle/active/settled). Concepto aprobado, demo: faq-orb-demo.html
- Ref: docs/ROADMAP-UIUX-LANDING.md seccion 7

### Registro avanzado
- [x] 1.x-G: Registro por codigo de acceso — AccessCode model, Filament CRUD + lote, toggle por evento, campo en AuthStep, validacion atomica, tracking usos.
- [x] 1.x-G.1: Verificacion identidad CSV — campo personalizable (telefono/etc), verify-identity endpoint, fallback si deep link no funciona.
- [ ] 1.x-H: Staff invite push + cambio de rol — push + socket + layout vendedor.

### Setup wizard evento (Filament)
- [ ] Al crear evento nuevo: wizard por pasos (slides) — nombre, fecha/hora, lugar, logo, modalidad, template modulos
- [ ] Paso 1: Nombre + slug + descripcion
- [ ] Paso 2: Fechas (inicio, fin, registro abre/cierra)
- [ ] Paso 3: Lugar + modalidad (presencial/virtual/hibrido) + capacidad
- [ ] Paso 4: Logo + colores + hero
- [ ] Paso 5: Template de modulos (congreso/feria/lanzamiento) → crea modulos automatico
- [ ] Resultado: evento listo con toda la config basica en 2 minutos

### Filament cleanup — COMPLETADO (2026-04-14, commit d2a9e86)
- [x] Reorganizar grupos de navegacion (11→7 grupos coherentes)
- [x] Unificar tildes inconsistentes (Comunicacion vs Comunicaciones, etc.)
- [x] Sort order secuencial sin duplicados
- [x] 26 archivos actualizados

### Documentacion tecnica
- [ ] Documentar arquitectura socket: una conexion, eventos, rendimiento, escalabilidad. Ref: project_socket_architecture.md

### Cleanup / Dev build
- [ ] QA visual multi-device (ZTE 360dp + Medium 411dp)
- [ ] Crop circular dark (react-native-image-crop-picker, requiere dev build)
- [ ] Push reminders probar en dev build (codigo listo)
- [ ] Push invalidation probar en dev build (codigo listo)
- [ ] Mensaje anclado chat tipo Twitch (nice to have)
- [ ] White-label: migrar app.json → app.config.js + estructura clients/ (ref: docs/WHITE-LABEL.md)

---

## Bancolombia — Requerimientos específicos

> Contexto: reunión 2026-04-14. Competencia mostró webapp (fea, 60 días).
> Bancolombia necesita flujo completo webapp + landing + experiencia.
> Formato especial: "sonido silencioso" (silent disco) — un salón, audífonos, dos charlas simultáneas.
> Eventos multi-país (Colombia + Panamá) y multi-ciudad (Bogotá + Medellín).

### Silent Disco — Toggle por evento
Feature toggle: solo se activa cuando el evento usa sistema de audífonos con canales.

- [ ] Backend: `silent_disco_enabled` en config del evento (Filament toggle)
- [ ] Backend: tabla `session_attendances` (event_session_id, attendee_id, token UUID, joined_at, left_at)
- [ ] Backend: API `POST /sessions/{id}/join` → genera token único, registra asistencia
- [ ] Backend: API `DELETE /sessions/{id}/leave` → cierra asistencia (left_at = now)
- [ ] Backend: Scoping — Q&A, polls, ratings filtran por attendees que hicieron join a ESA sesión
- [ ] Socket: `session:joined` / `session:left` → actualizar contadores en tiempo real
- [ ] App: Agenda detecta sesiones simultáneas (mismo room + mismo horario) → agrupar visualmente
- [ ] App: Botón "Asistir" en cada sesión simultánea → llama join API → estado activo
- [ ] App: Puede cambiar de sesión en cualquier momento (leave + join)
- [ ] App: Indicador visual "Estás en esta charla" con token visible
- [ ] Filament: Vista asistencia por sesión (quién estuvo en cuál, duración)
- [ ] Métricas: engagement por charla (polls respondidos, Q&A, ratings) cruzado con asistencia

### Multi-location con tracks
Tracks ya implementados (S1.12). Solo requiere uso correcto:

- [ ] Crear tracks por ciudad/país: "Bogotá", "Medellín", "Panamá"
- [ ] Agenda filtra por track → asistente ve solo charlas de su ubicación
- [ ] Sesiones simultáneas sin filtro: aparecen stacked con TrackBadge diferenciando

### Webhooks — Integración con partners de registro/badges
Empresas especializadas en registro presencial (impresoras de escarapelas, hardware propio).
EventOS les entrega data, ellos devuelven el check-in.

**Outbound (EventOS → Partner):**
- [ ] Webhook configurable por evento en Filament (URL destino + secret key)
- [ ] Eventos: `attendee.registered`, `attendee.updated`, `attendee.approved`
- [ ] Payload: id, name, email, company, role, tags, photo_url, qr_identifier
- [ ] Retry con backoff (3 intentos) via queue job
- [ ] Alternativa pull: API key para partner → `GET /api/v1/events/{id}/attendees` (paginado, filtrable)

**Inbound (Partner → EventOS):**
- [ ] `POST /api/v1/webhooks/checkin` autenticado con API key por partner
- [ ] Payload: `{ identifier: "email o qr_code", event_id, checked_in_at? }`
- [ ] Backend: actualiza `checked_in_at` → dispara socket `attendee:checkin` → módulos se actualizan
- [ ] Reutiliza: misma lógica que el kiosco (S1.4), solo cambia el origen
- [ ] Log: registrar quién hizo el check-in (source: "partner:badge_company_name")

**Seguridad:**
- [ ] API keys por partner (generadas en Filament, hash en BD)
- [ ] Rate limiting por API key
- [ ] Validar que el attendee pertenece al evento del partner

### Mission Control — Display en vivo (evento + pitch)
Vista "mission control" web para pantalla grande o demo. Solo escucha socket — CERO carga extra al server.
Se activa cuando el evento está en estado `live`.

- [ ] Web page standalone (HTML + Socket.IO client + GSAP/CSS animations)
- [ ] Contador en vivo: presenciales (checked_in_at) + virtuales (socket room.size) + total
- [ ] Feed de actividad: "Pedro Pérez ingresó" con avatar difuminado, "María se conectó", "Juan salió"
- [ ] Feed gamificación: "Kamilo redimió 50 puntos" con animación partículas
- [ ] Sesión activa actual + cuántos asistentes en ella ahora mismo
- [ ] Encuesta/trivia: barras que crecen en tiempo real (consume poll:vote)
- [ ] Ruleta: animación pantalla grande cuando se activa (consume roulette:spin)
- [ ] Leaderboard: posiciones moviéndose con transiciones suaves
- [ ] Auth: token admin o URL con secret para acceder
- [ ] Responsive: optimizado para 1920x1080 (display venue) + 1280x720 (laptop pitch)
- [ ] Konva.js candidato para canvas animado (ref: https://konvajs.org/docs/sandbox.html)
- Reutiliza: TODOS los eventos socket existentes. No requiere endpoints nuevos.
- Doble uso: display durante el evento EN VIVO + herramienta de demo para pitch a clientes.

### Login — mostrar intentos restantes
- [ ] App: en error de contraseña, mostrar "X intentos restantes" (rate limiting SEC-3 ya existe en backend, falta UX)
- [ ] Backend: devolver `remaining_attempts` en response 422 del login

### Encuesta post-evento automática
- [ ] Backend: trigger cuando event.status cambia a `ended` → activar módulo encuesta
- [ ] App: al detectar estado `ended`, mostrar modal/redirect a encuesta de satisfacción
- [ ] Reutiliza: live_poll_questions (S1.10) con tipo "post_event_survey"

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

### Backup/Restore de evento (tipo Minecraft world save)
- [ ] Snapshot completo del evento: onboarding, registration_fields, modules, sponsors, agenda, gamification
- [ ] Exportar como JSON descargable
- [ ] Importar/restaurar desde JSON
- [ ] Versionado con label y descripcion (v1, v2, v3...)
- [ ] Modulo Filament dedicado (no parche en onboarding)

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
