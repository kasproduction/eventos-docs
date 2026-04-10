# Roadmap UI/UX + Landing + Registro Premium

> Documento de planificación para la fase UI/UX de EventOS.
> Fecha: 2026-04-07 | Actualizado: 2026-04-10 | Estado: En Progreso (Paso 5 — 80% completado)

---

## 1. Landing Web (Pública)

La landing es la primera impresión del evento. No es un simple formulario — es una experiencia premium que vende el evento.

### 1.1 Secciones de la Landing

| Sección | Descripción | Prioridad |
|---------|-------------|-----------|
| **Hero** | Video ambient loop de fondo + countdown al evento + CTA "Regístrate" | MUST |
| **Sobre el evento** | Descripción, fecha, ubicación, highlights | MUST |
| **Speakers** | Carrusel/grid con foto, nombre, cargo. Hover con bio expandible | MUST |
| **Agenda resumida** | Vista por tracks/días, no detalle completo. Teaser que genera curiosidad | MUST |
| **Sponsors/Partners** | Logos de patrocinadores con links. Sección configurable desde admin | MUST |
| **Venue + Mapa** | Foto del venue, mapa interactivo (Google Maps embed), indicaciones | SHOULD |
| **Testimonios** | Quotes de ediciones pasadas o de speakers confirmados | SHOULD |
| **Galería** | Fotos/videos de ediciones anteriores | SHOULD |
| **FAQ** | Preguntas frecuentes colapsables (accordion) | SHOULD |
| **Footer** | Redes sociales del evento, links legales, contacto | MUST |

### 1.2 Form de Registro (embebido en landing)

- El form está integrado en la landing (modal o sección dedicada)
- Campos dinámicos configurados desde Filament (ya implementado en S1.22)
- **Progressive profiling**: En la landing solo se piden datos esenciales (nombre, email, teléfono). Los extras (intereses, foto, empresa) se completan en la app al primer login
- Social proof: Contador en vivo "X personas ya registradas"
- CAPTCHA/reCAPTCHA invisible para evitar bots
- Rate limiting: max 5 registros por IP en 15 minutos

### 1.3 Aspectos Técnicos Landing

- **Stack**: HTML/CSS/JS estático o Next.js SSG (rendimiento máximo)
- **SEO/OG tags**: Open Graph meta tags para que al compartir en WhatsApp, Twitter, LinkedIn se vea imagen + título + descripción del evento
- **Responsive**: Mobile-first, 100% adaptable
- **Performance**: Lighthouse score > 90
- **Accesibilidad**: WCAG 2.1 AA compliant
- **Animaciones**: Parallax scroll, fade-in de secciones, smooth transitions. NO decorativas gratuitas — cada animación debe guiar la atención

### 1.4 Separación Login vs Registro

| Acción | Dónde |
|--------|-------|
| **Registro** | Landing web (público) |
| **Login** | Solo en la app móvil |
| **Verificar registro** | Landing web (con email) — "Ya estás registrado, descarga la app" |

---

## 2. Estados del Evento (Lifecycle)

El evento tiene 3 estados que controlan qué ve el usuario. El admin cambia el estado desde Filament.

### 2.1 Definición de estados

```
┌─────────────┐     ┌──────────┐     ┌─────────────┐
│  pre_event  │ ──> │  active   │ ──> │ post_event  │
└─────────────┘     └──────────┘     └─────────────┘
```

| Estado | Registro | Login App | Qué ve en la app |
|--------|----------|-----------|-------------------|
| `pre_event` | Abierto (landing) | Permitido | Countdown + teaser (nombre, fecha, venue, speakers confirmados). Sin agenda completa, sin features interactivas |
| `active` | Abierto o cerrado | Permitido | Acceso completo: agenda, networking, Q&A, social wall, gamificación, etc. |
| `post_event` | Cerrado | Permitido (read-only) | Modo archivo: grabaciones, fotos, contactos hechos, certificados, encuesta NPS |

### 2.2 Pantalla Countdown (pre_event)

Cuando el usuario hace login pero el evento no está activo:

- **Countdown animado** calculado desde `event.starts_at` (días, horas, minutos, segundos)
- **Teaser content**: Logo del evento, nombre, fecha, venue
- **Speakers confirmados**: Grid/carrusel de speakers ya publicados
- **"Completa tu perfil"**: CTA para que llene intereses, foto, datos extras (progressive profiling)
- **Notificaciones habilitadas**: Prompt para activar push notifications
- **NO muestra**: Agenda vacía, salas vacías, features sin contenido

### 2.3 Pantalla Post-Event (post_event)

- Galería del photobooth + social wall
- Grabaciones de sesiones (si streaming estuvo habilitado)
- Lista de contactos realizados (networking/matchmaking)
- Certificado de asistencia descargable
- Encuesta NPS automática (si no la completó)
- Resumen de gamificación (posición final, puntos, badges)
- Mensaje de agradecimiento personalizado del organizador

---

## 3. Flujo Post-Registro (Confirmación + Descarga)

Después de un registro exitoso en la landing:

### 3.1 Pantalla de Confirmación Web

```
┌──────────────────────────────────────┐
│                                      │
│     [Logo Evento]                    │
│                                      │
│     Te registraste exitosamente      │
│     ──────────────────────────       │
│     Nombre: Juan Pérez               │
│     Email: juan@email.com            │
│                                      │
│     Descarga la app para acceder:    │
│                                      │
│     [QR Code - detecta OS]           │
│                                      │
│     [App Store]  [Google Play]       │
│                                      │
│     Revisa tu correo para más info   │
│                                      │
└──────────────────────────────────────┘
```

### 3.2 Canales de Comunicación (configurables por evento desde admin)

| Canal | Contenido | Configurable |
|-------|-----------|-------------|
| **Email** | Confirmación + datos del evento + QR descarga app + .ics calendar invite + enlace "Ver mi registro" | Siempre activo |
| **WhatsApp** | Mensaje template con deep link a la app + datos básicos del evento | Toggle on/off desde admin |
| **SMS** | Mensaje corto con link de descarga (fallback si no tiene WhatsApp) | Toggle on/off desde admin |

### 3.3 Email de Confirmación — Contenido

- Logo + branding del evento
- "Hola [nombre], tu registro está confirmado"
- Fecha, hora, lugar del evento
- QR code personalizado (para check-in el día del evento)
- Botones: App Store / Google Play
- Archivo .ics adjunto (Add to Calendar)
- Link "Ver mi registro" → landing con estado del registro
- Footer con redes sociales del evento

### 3.4 WhatsApp Template (ejemplo)

```
Hola {{nombre}}! Tu registro para {{evento}} está confirmado.

Fecha: {{fecha}}
Lugar: {{venue}}

Descarga la app para acceder a toda la info:
{{deep_link}}

Nos vemos!
```

---


## 4. Seguridad

### 4.1 MUST HAVE (Fase UI/UX)

| Feature | Descripción | Implementación |
|---------|-------------|----------------|
| **Rate limiting** | Max 5 intentos login en 15 min. Max 5 registros por IP en 15 min | Laravel throttle middleware |
| **CAPTCHA** | reCAPTCHA v3 invisible en form de registro web | Google reCAPTCHA |
| **2FA (OTP)** | Código de 6 dígitos por email o WhatsApp al hacer login. Configurable por evento desde admin | Tabla `otp_codes`, expiración 5 min |
| **Account lockout** | Después de 5 intentos fallidos, bloquear 30 min + notificar por email al usuario | Campo `locked_until` en users |
| **Device fingerprinting** | Login desde dispositivo nuevo → fuerza 2FA automáticamente sin importar config del admin | Tabla `user_devices`, hash de device info |
| **HTTPS obligatorio** | Landing y API solo por HTTPS | Nginx/Caddy config |

### 4.2 NICE TO HAVE (diferenciadores)

| Feature | Descripción |
|---------|-------------|
| **Magic link login** | Alternativa a contraseña: click en enlace del email y entras directamente. Reduce fricción. Token de un solo uso, expira en 15 min |
| **QR dinámico (TOTP)** | El QR de check-in rota cada 30 segundos (como Google Authenticator). Imposible compartir screenshots |
| **Session management** | El usuario ve desde qué dispositivos está logueado. Puede cerrar sesiones remotas desde su perfil |
| **Anomaly detection** | Alertar al admin si detecta patrones inusuales: múltiples logins desde diferentes países, registros masivos desde misma IP |

---

## 5. Toques Premium — Micro-interacciones

### 5.1 App Móvil

| Toque | Descripción | Referencia Design |
|-------|-------------|-------------------|
| **Skeleton loading** | En vez de spinners, esqueletos animados que reflejan la estructura del contenido | Wireframes en `design/` |
| **Haptic feedback** | Vibraciones sutiles al: favoritar sesión, dar like, recibir match, scan QR | iOS Taptic Engine / Android Vibration API |
| **Shared element transitions** | Al tocar una card de speaker → la foto se anima hacia la pantalla de detalle | React Navigation shared transitions |
| **Pull-to-refresh custom** | Animación de refresh con el branding del evento (logo que gira/pulsa), no el spinner genérico | Custom RefreshControl |
| **Glassmorphism navbar** | Bottom nav flotante con blur de fondo (ya en el design system Lumina Noir) | `design/stitch/` |
| **Neon glow en elementos live** | Indicador "EN VIVO" con glow pulsante en sesiones activas | Agenda Noir Refined |
| **Countdown animado** | Flip-clock style o digits que transicionan suavemente | Pantalla pre_event |
| **Empty states ilustrados** | Cuando no hay contenido, ilustraciones custom en vez de texto plano "No hay datos" | — |
| **Toast notifications premium** | Toasts con glassmorphism, no los genéricos del sistema | Lumina Noir system |
| **Onboarding slides** | 3-4 slides con animaciones Lottie mostrando features principales | `design/onboarding/` |

### 5.2 Landing Web

| Toque | Descripción |
|-------|-------------|
| **Parallax scroll** | Hero y secciones con efecto de profundidad al hacer scroll |
| **Fade-in on scroll** | Elementos aparecen suavemente al entrar en viewport |
| **Cursor custom** | Cursor personalizado con el accent color del evento (solo desktop) |
| **Hover states premium** | Cards de speakers con efecto "lift" (translate + shadow) al hover |
| **Texto que se revela** | Títulos que aparecen letra por letra o palabra por palabra |
| **Partículas/confetti** | Efecto sutil de partículas en el hero (no exagerado) |
| **Dark/Light toggle** | Opción de ver la landing en modo claro (Lumina Lux) o modo oscuro (Lumina Noir) |

---

## 6. Features Adicionales Premium

### 6.1 Registro & Acceso

| Feature | Descripción | Prioridad |
|---------|-------------|-----------|
| **Invitation codes** | Modo "solo invitados": solo emails/códigos pre-aprobados pueden registrarse. Diferente a approval (que es post-registro) | SHOULD |
| **Waitlist** | Cuando `max_attendees` se llena → opción de lista de espera con notificación automática cuando haya cupo | SHOULD |
| **Referral tracking** | "Comparte tu link personal" → trackear quién invitó a quién. Integración con gamificación (S1.20) | COULD |
| **Apple/Google Wallet** | El pase de acceso como tarjeta en el wallet del teléfono. QR incluido. | COULD |
| **Social login (Google)** | "Regístrate con Google" como opción adicional al email | SHOULD |

### 6.2 Comunicación

| Feature | Descripción | Prioridad |
|---------|-------------|-----------|
| **WhatsApp Business API** | Templates configurables desde admin. Envío masivo para: confirmación, recordatorios, cambios de última hora | MUST |
| **Push reminders configurables** | "Tu sesión empieza en 15 min", "Nuevo speaker confirmado", "Encuesta disponible" — todo configurable desde admin | MUST (S1.14 parcial) |
| **Email builder** | Editor visual en Filament para personalizar templates de email sin tocar código | COULD |
| **Calendar invite (.ics)** | Adjunto en email de confirmación. Un click → evento en Google Calendar/Outlook | MUST |

### 6.3 Post-Evento

| Feature | Descripción | Prioridad |
|---------|-------------|-----------|
| **Certificado de asistencia** | PDF con branding del evento + nombre + horas. Descargable y compartible | SHOULD |
| **Networking follow-up** | Resumen "Conectaste con X personas" + botón follow-up (email/LinkedIn) | SHOULD |
| **Highlight reel** | Collage automático de fotos del photobooth + social wall | COULD |
| **NPS survey** | Encuesta de satisfacción automática post-evento (1-10 + comentario) | SHOULD |
| **Event replay** | Grabaciones de sesiones disponibles en modo `post_event` | COULD |

### 6.4 Admin (Filament)

| Feature | Descripción | Prioridad |
|---------|-------------|-----------|
| **Analytics dashboard** | Registros real-time, demografía, engagement, funnel de conversión. Inspirado en Airmeet (`design/Screenshots Airmeet`) | SHOULD |
| **Branded QR codes** | QR codes con logo del evento embebido en el centro | COULD |
| **Landing builder** | Seleccionar qué secciones mostrar, reordenar, cambiar textos/imágenes desde Filament | COULD (Fase 2) |
| **A/B testing emails** | Probar diferentes subject lines, medir open rate | COULD (Fase 2) |

---

## 7. Design System — Lumina Noir / Lumina Lux

El sistema de diseño ya está definido en `design/stitch/`. Resumen ejecutivo:

### Lumina Noir (Dark — Principal)
- Background: `#0e0e0e` / `#0a0a0a`
- Accent: `#FFFFFF` por defecto, configurable por evento via `primary_color`
- Fuentes: Plus Jakarta Sans (headlines) + Urbanist (body) — NO Inter, NO Space Grotesk
- Bordes: NO visibles. Separación por cambio tonal (rgba white 0.03-0.06)
- Elevación: Glassmorphism + backdrop blur, no drop shadows
- Corner radius: 22px cards, 14px inputs/buttons, 9999px pills
- Regla clave: "Space is luxury" — si la pantalla se siente llena, agregar más espacio

### Lumina Lux (Light — Alternativo)
- Background: `#FFFFFF`
- Accent: configurable por evento
- Mismas reglas de "No-Line", glassmorphism adaptado a light
- Uso: Landing web (toggle), modo accesibilidad

### Referencias de diseño recopiladas
- `design/stitch/` — Prototipos HTML de home, agenda, intro (Lumina Noir)
- `design/onboarding/` — Flujo registro/verificación/intereses + landing web
- `design/17fbde*.webp` — Referencia Fever (dark + yellow/lime accent)
- `design/original-2f6a81*.webp` — Event cards con accent colors
- `design/Contacts-App*.jpg` — Directorio de contactos dark/light
- `design/Agenda.png` — Agenda checkout screen con accent verde
- `design/Screenshot*Airmeet` — Dashboard analytics admin
- `design/original-452f26*.webp` — Wireframes estructura base
- `design/product_shot3*.jpg` — App roja, referencia de layout
- `design/Screenshot*232001.png` — Eventox blue, event discovery + seat selection
- `design/Screenshot*232006.png` — Eventox onboarding slides

---

## 8. Prioridades de Implementación

### Fase UI/UX — Orden sugerido

```
PASO 1: Fundamentos ✅ COMPLETADO (2026-04-07/08)
├── [x] Design system tokens en React Native (colores, tipografía, spacing)
├── [x] ThemeProvider + fuentes PlusJakartaSans/Inter cargadas
├── [x] FloatingTabBar glassmorphism con blur + safe area
├── [x] Transiciones animadas entre tabs (lift animation, labels, haptic)
├── [x] LuminaToast system (glass card, spring, auto-dismiss, variantes)
├── [x] ScreenWrapper para stack screens (sin flash blanco Android)
├── [x] app.json backgroundColor #0e0e0e (window nativo Android)
├── [x] Todos los layouts con contentStyle + sceneStyle dark
├── [ ] Componentes base reutilizables: Button, Card, Input (pendiente extraer)
└── [ ] Skeleton loading components

PASO 2: Landing Web
├── Estructura HTML/CSS (o Next.js)
├── Hero con video + countdown
├── Secciones: speakers, agenda, sponsors, venue, FAQ
├── Form de registro embebido (con CAPTCHA)
├── OG tags + SEO
├── Parallax + scroll animations
└── Responsive + performance

PASO 3: Flujos de Registro & Auth
├── Pantalla confirmación post-registro
├── Email de confirmación (con .ics + QR)
├── WhatsApp template de confirmación
├── 2FA (OTP por email/WhatsApp)
├── Rate limiting + account lockout
├── Device fingerprinting
└── Progressive profiling en app

PASO 4: Estados del Evento
├── Pantalla countdown (pre_event)
├── Transición pre_event → active
├── Modo archivo (post_event)
├── Certificados de asistencia
└── NPS survey post-evento

PASO 5: Barrido Visual App — ~80% COMPLETADO
├── ✅ Home (2026-04-07):
│   ├── [x] Header configurable (logo/text desde branding API)
│   ├── [x] Hero text/image modes, HappeningNow crossfade 6s
│   ├── [x] GamificationHud RGB border + carousel integrado
│   ├── [x] Mi QR tab premium, ModuleMenu 4 fijos con cascade animation
│   └── [x] Pull-to-refresh
├── ✅ Agenda (2026-04-08):
│   ├── [x] Lumina Noir, day strip Fever-style, timeline glass cards
│   ├── [x] Track filter, session states (live/finished/upcoming)
│   ├── [x] Corazon animado con particulas, toast favoritos
│   ├── [x] Calendario nativo (expo-calendar) + fallback Google Calendar
│   └── [x] DaySlide direccional, finished cards opacity solo info
├── ✅ Speakers (2026-04-08):
│   ├── [x] Carousel Destacados (breathing animation), search, lista Todos
│   ├── [x] Detail: hero photo, rating cristales diamond, LinkedIn, bio, session cards
│   ├── [x] Speaker → Agenda navegacion scroll-to + highlight sutil
│   └── [x] Master seeder: 18 speakers HD, 5 tracks, 29 sesiones 3 dias
├── ✅ Streaming (2026-04-08):
│   ├── [x] Header premium (titulo, speaker, empresa, separador)
│   ├── [x] YouTube embed (react-native-youtube-iframe, fix URLs largas)
│   └── [x] Boton UNIRTE solo live, Ver grabacion solo finished+recording
├── ✅ Social (2026-04-09):
│   ├── [x] Unificado: Feed + Memorias + Momentos en una pantalla
│   ├── [x] PostCard Lumina Noir, likes animados, comments bottom sheet 55%
│   ├── [x] Memorias grid 3col, fotos oficiales 2col + badge OFICIAL
│   ├── [x] Momentos: stories simplificados (img, 24h, auto-advance 5s)
│   ├── [x] BottomSheet reutilizable, SegmentedControl glass pill
│   ├── [x] PhotoViewer + MomentosViewer fullscreen
│   ├── [x] CreatePostModal dark + SocialFAB contextual
│   └── [x] ~30 bugs resueltos (gesture, stale closures, Android)
├── ✅ Sponsors (2026-04-09):
│   ├── [x] Brand Wall: grid adaptativo por tier, living shuffle 7s, stagger reveal
│   ├── [x] Brand Profile: logo hero, floating nav blur, servicios, trivia A/B/C/D
│   └── [x] 15 tests sponsor, 300 total
├── ✅ Profile (2026-04-09):
│   ├── [x] Lumina Noir, beam Ocean avatar, foto editable
│   ├── [x] Social links, stats, modal editar, pull-to-refresh
│   └── [x] ProfileController + migration twitter/instagram/website
├── ✅ Encuestas / PollSlides (2026-04-10):
│   └── [x] Rediseno completo Lumina Noir (slides por pregunta, MultipleChoice/Star/OpenText)
├── ✅ Chat sesion (2026-04-10):
│   └── [x] Emojis animados + cooldown, Enter=enviar, error handling
├── ✅ Transversales completados:
│   ├── [x] FloatingTabBar: lift, labels, sin circulos, QR central, 5 tabs uniformes, haptic
│   ├── [x] Notificaciones: badge rojo, shake 5s, MMKV persistence
│   ├── [x] RatingModal: cristales diamond, bounce, haptic, accent
│   ├── [x] Headers uniformes: arrow-left + titulo izquierda
│   ├── [x] Responsive 360dp: 31 archivos, 12 pantallas SafeArea, proporcional
│   ├── [x] Logout BottomSheet, tab bar ajustado
│   ├── [x] Urbanist body + PlusJakartaSans headlines en toda la app
│   ├── [x] Flash blanco Android eliminado (app.json + layouts + ScreenWrapper)
│   ├── [x] Breathing carousel (Easing.out cubic), comments optimistic
│   └── [x] Background #1a1919 → #141414, console.log cleanup
├── ⏳ Pantallas pendientes:
│   ├── [ ] Networking (directorio + contactos + solicitudes) ← UI ya funcional, falta Lumina Noir polish
│   ├── [ ] Gamificacion / Leaderboard ← UI funcional, falta polish visual
│   ├── [ ] Matchmaking ← UI funcional, falta polish visual
│   ├── [ ] Auth screens (login / registro) ← rediseno visual
│   └── [ ] Onboarding visual (bugs z-index Android, requiere dev build)
├── ⏳ Items transversales pendientes:
│   ├── [ ] Skeleton loading — componente creado (SkeletonGroup + Bone Context),
│   │       integracion revertida (crash 25+ shared values). Reintentar uno por uno.
│   ├── [ ] EmptyState — componente creado, integracion revertida con skeleton.
│   ├── [ ] Pull-to-refresh custom (LuminaRefresh.tsx) — creado, integracion revertida.
│   ├── [ ] Empty states ilustrados en todas las pantallas
│   ├── [ ] Iconografia consistente (reemplazar emojis ModuleMenu por icon set real)
│   └── [ ] QA visual completo (multi-device)

PASO 6: Admin Premium
├── Dashboard analytics
├── Configuración canales (email/WhatsApp/SMS toggles)
├── Gestión estados del evento
├── Preview landing desde admin
└── Branded QR codes
```

---

## 9. Stack Técnico Landing

| Aspecto | Decisión | Razón |
|---------|----------|-------|
| **Framework** | Next.js (SSG/ISR) o Astro | Performance, SEO, OG tags server-side |
| **Styling** | Tailwind CSS (mismo que Stitch prototypes) | Consistencia con design system |
| **Animaciones** | Framer Motion o GSAP | Parallax, scroll reveal, transitions |
| **Forms** | React Hook Form + Zod | Validación client-side |
| **CAPTCHA** | reCAPTCHA v3 invisible | No interrumpe UX |
| **Hosting** | Vercel o mismo VPS | Deploy automático |
| **Datos** | API Laravel existente (endpoints públicos para landing) | Reusar backend |

---

## 10. Endpoints API Necesarios (Landing)

| Endpoint | Método | Auth | Descripción |
|----------|--------|------|-------------|
| `GET /api/public/event/{slug}` | GET | No | Datos públicos del evento (nombre, fecha, venue, estado, countdown) |
| `GET /api/public/event/{slug}/speakers` | GET | No | Speakers publicados |
| `GET /api/public/event/{slug}/agenda` | GET | No | Agenda resumida (solo tracks + títulos) |
| `GET /api/public/event/{slug}/sponsors` | GET | No | Sponsors con logo + link |
| `POST /api/public/event/{slug}/register` | POST | No | Registro desde landing (rate limited + CAPTCHA) |
| `GET /api/public/event/{slug}/registration-count` | GET | No | Contador de registrados (social proof) |
| `POST /api/auth/verify-otp` | POST | No | Verificar código 2FA |
| `POST /api/auth/request-magic-link` | POST | No | Solicitar magic link login |

---

*Documento vivo — se actualiza conforme avanza la implementación.*
