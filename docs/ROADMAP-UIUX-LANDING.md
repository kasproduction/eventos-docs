# Roadmap UI/UX + Landing + Registro Premium

> Spec de diseno UI/UX de EventOS — landing, estados evento, design system, pasos.
> Fecha: 2026-04-07 | Actualizado: 2026-04-15 | Estado: Paso 5 COMPLETADO + Lifecycle COMPLETADO + FAQ COMPLETADO
>
> **Status tracking:** `docs/PENDIENTES.md` (lo que falta) + `docs/COMPLETADO.md` (lo que se hizo)

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
| **FAQ** | Asistente FAQ con orbe animado (blob organico multicolor tipo Siri). Nucleo luminoso que muta + ondas concentricas. Preguntas curadas por el organizador agrupadas por categoria, NO es AI. Demo: `faq-orb-demo.html`. Ref visual: `design/onboarding/faq/1.gif` | SHOULD |
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

## 2. Estados del Evento (Lifecycle) — IMPLEMENTADO 2026-04-14

El evento tiene 4 estados que controlan qué ve el usuario. El admin cambia el estado desde Filament (Evento > Branding & Hero > Estado del evento).

### 2.1 Definición de estados

```
┌──────────────┐     ┌───────────┐     ┌──────┐     ┌────────┐
│ registration │ ──> │ published │ ──> │ live │ ──> │ ended  │
└──────────────┘     └───────────┘     └──────┘     └────────┘
       (+ draft como estado admin interno)
```

| Estado | Home muestra | Tabs | Diferencia con Live |
|--------|-------------|------|---------------------|
| `draft` | Hero + Countdown + InfoCard | Solo Home | Admin configurando |
| `registration` | Hero + Countdown + InfoCard + About (opcional) | Solo Home | Registro abierto, sin modulos |
| `published` | Hero + Countdown compact + HappeningNow + ModuleMenu compact | Todos | App completa, countdown visible |
| `live` | Hero + HappeningNow + ModuleMenu | Todos | Identico a la app actual |
| `ended` | Hero + Banner finalizado + Stats + Archive links | Solo Home | Read-only, links a contenido |

### 2.2 Componentes implementados

| Componente | Archivo | Donde se usa |
|-----------|---------|-------------|
| `CountdownTimer` | `components/ui/CountdownTimer.tsx` | registration (normal), published (compact) |
| `EventInfoCard` | `components/ui/EventInfoCard.tsx` | registration — modalidad, venue, registrados, cierre |
| `EventArchive` | `components/ui/EventArchive.tsx` | ended — stats + links a agenda/memorias/gamification/speakers |
| `About` | `app/(app)/about.tsx` | registration — pantalla dedicada, imagen+texto+links |
| `ModuleMenu compact` | prop `compact` en ModuleMenu | published — cards 56px |

### 2.3 Modalidad del evento

Campo `modality` en events: `presencial`, `virtual`, `hibrido`. Se muestra como badge en la EventInfoCard (registration).

| Modalidad | Color | Icono |
|-----------|-------|-------|
| Presencial | #34d399 (verde) | map-marker |
| Virtual | #60a5fa (azul) | monitor |
| Hibrido | #c084fc (violeta) | swap-horizontal |

### 2.4 About pre-evento

Modulo opcional habilitado por toggle en Filament. Visible solo en draft/registration.
- Imagen banner 16:9 (opcional — si no hay, se adapta)
- Texto descriptivo
- Links externos (web, redes, mapa) que abren browser
- Card en Home registration → tap abre pantalla /(app)/about

### 2.5 Countdown

- **Normal** (registration): boxes 62x70px, 4 unidades (dias/horas/min/seg)
- **Compact** (published): boxes 50x50px, padding reducido
- **Expired**: badge "El evento comienza hoy" con dot pulsante
- Tick cada 1 segundo, detiene interval al expirar

### 2.6 Pantalla Ended (archivo)

- Banner "Evento finalizado" con fechas
- Stats: asistentes, sesiones, fotos (datos reales de la API)
- Archive links: Agenda+Grabaciones, Memorias (social), Gamification, Speakers
- Cada link navega a la pantalla existente

### 2.7 Configuracion Filament

Seccion "Estado del evento" en Evento > Branding & Hero:
- Select status (5 opciones)
- Select modalidad (3 opciones)
- DateTimePicker fecha inicio/fin
- Venue, capacidad maxima
- Seccion "About" condicional (solo draft/registration) con toggle + imagen + texto + repeater links

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
| **Rate limiting** | Max 5 intentos login en 15 min. Max 5 registros por IP en 15 min. Networking 100/evento 30/dia. | ✅ Laravel throttle middleware. SEC-6.1 completado 2026-04-15 |
| **CAPTCHA** | reCAPTCHA v3 invisible en form de registro web | Google reCAPTCHA |
| **2FA (OTP)** | Código de 6 dígitos por email o WhatsApp al hacer login. Configurable por evento desde admin | Tabla `otp_codes`, expiración 5 min |
| **Account lockout** | Después de 5 intentos fallidos, bloquear 30 min + mensaje "X intentos restantes" | ✅ SEC-3.3 completado. Backend 423 + app muestra intentos restantes (2026-04-15) |
| **Device fingerprinting** | Login desde dispositivo nuevo → fuerza 2FA automáticamente sin importar config del admin | Tabla `user_devices`, hash de device info. Depende de 2FA |
| **HTTPS obligatorio** | Landing y API solo por HTTPS | Nginx/Caddy config |

### 4.2 NICE TO HAVE (diferenciadores)

| Feature | Descripción |
|---------|-------------|
| **Magic link login** | Alternativa a contraseña: click en enlace del email y entras directamente. Reduce fricción. Token de un solo uso, expira en 15 min |
| **QR dinámico (TOTP)** | El QR de check-in rota cada 30 segundos (como Google Authenticator). Imposible compartir screenshots |
| **Session management** | El usuario ve desde qué dispositivos está logueado. Puede cerrar sesiones remotas desde su perfil |
| **Anomaly detection** | Alertar al admin si detecta patrones inusuales: múltiples logins desde diferentes países, registros masivos desde misma IP |
| **Mensaje anclado chat (tipo Twitch)** | Admin ancla un mensaje importante arriba del chat de sesion. Evento socket `chat:pinned`, banner fijo en app, auto-expira o se quita manual. Util para anuncios puntuales del moderador |

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
| **Invitation codes** | Modo "solo invitados": emails/códigos pre-aprobados. Diferente a approval (post-registro) | ✅ COMPLETADO: registro cerrado (email_list/domain/both) + access codes + invite_only (2026-04-15) |
| **Waitlist** | Cuando `max_attendees` se llena → opción de lista de espera con notificación automática cuando haya cupo | SHOULD |
| **Referral tracking** | "Comparte tu link personal" → trackear quién invitó a quién. Integración con gamificación (S1.20) | COULD |
| **Apple/Google Wallet** | El pase de acceso como tarjeta en el wallet del teléfono. QR incluido. | COULD |
| **Social login (Google)** | "Regístrate con Google" como opción adicional al email | SHOULD |

### 6.2 Comunicación

| Feature | Descripción | Prioridad |
|---------|-------------|-----------|
| **WhatsApp Business API** | Templates configurables desde admin. Envío masivo para: confirmación, recordatorios, cambios de última hora | MUST |
| **Push reminders configurables** | "Tu sesión empieza en 15 min". Windows dinamicos, toggle on/off, push cambio de hora. | ✅ COMPLETADO: Filament "Recordatorios" (Comunicacion), config por evento, 19 tests (2026-04-15) |
| **Email builder** | Editor visual en Filament para personalizar templates de email sin tocar código | COULD |
| **Calendar invite (.ics)** | Adjunto en email de confirmación. Un click → evento en Google Calendar/Outlook | ✅ COMPLETADO: WelcomeMail adjunta .ics evento (METHOD:REQUEST). Verificado Mailpit (2026-04-16) |

### 6.3 Post-Evento

| Feature | Descripción | Prioridad |
|---------|-------------|-----------|
| **Certificado de asistencia** | PDF con branding del evento + nombre + horas. Descargable y compartible | SHOULD |
| **Networking follow-up** | Resumen "Conectaste con X personas" + botón follow-up (email/LinkedIn) | SHOULD |
| **Highlight reel** | Collage automático de fotos del photobooth + social wall | COULD |
| **Encuesta post-evento** | Encuesta de satisfacción post-evento, auto-activada al pasar a `ended` | ✅ COMPLETADO: scope post_event, PostEventSurveyResource Filament, card EventArchive, 9 tests (2026-04-15) |
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
- Fuentes: Plus Jakarta Sans (headlines) + Urbanist (body). NUNCA Inter ni Space Grotesk.
- Bordes: NO visibles. Separación por cambio tonal (rgba white 0.03-0.06)
- Elevación: Glassmorphism + backdrop blur, no drop shadows
- Corner radius: 22px cards, 14px inputs/buttons, 9999px pills
- Regla clave: "Space is luxury" — si la pantalla se siente llena, agregar más espacio

### Lumina Lux (Light — Alternativo)
- Background: `#FFFFFF`
- Accent: configurable por evento
- Mismas reglas de "No-Line", glassmorphism adaptado a light
- Uso: Landing web (toggle), modo accesibilidad

### FAQ Asistente — ✅ IMPLEMENTADO (2026-04-15)

Orbe animado hibrido (nucleo solido que muta + ondas concentricas) como interfaz para FAQ curadas por el organizador.

- **Visual**: Blob organico multicolor (cyan, pink, purple, core blanco) con blur pesado, fondo negro puro. Referencia: Siri orb iOS 18 / HomePod visualizer
- **3 estados**: idle (respira lento), active (acelera + crece al seleccionar pregunta), settled (calmo al mostrar respuesta)
- **Interaccion**: Categorias en chips horizontales + preguntas tappables, sin input de texto
- **NO es AI**: Respuestas curadas por el organizador desde Filament, cero latencia, cero alucinaciones
- **Integracion app**: Acceso desde Perfil → icono Ayuda → ruta /(app)/faq
- **Backend**: Tabla `event_faqs` (event_id, section, question, answer_text, answer_action_url, answer_image_url, sort_order, is_active)
- **Backend**: FaqController GET /events/{id}/faqs (publica, agrupada por seccion) + FaqResource Filament CRUD + FaqSeeder (4 categorias, 12 preguntas) + 6 tests
- **App**: Pantalla FAQ con OrbBlob (3 estados animados), categorias stagger, accordion Reanimated, busqueda visual
- **Demo HTML**: `faq-orb-demo.html`
- **Referencia visual**: `design/onboarding/faq/1.gif`
- **Pendiente**: Upgrade orbe a Skia shader (reemplazar Reanimated+BlurView por @shopify/react-native-skia)

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
├── [x] Design system tokens en React Native (colores, tipografia, spacing)
├── [x] ThemeProvider + fuentes PlusJakartaSans/Urbanist cargadas
├── [x] FloatingTabBar liquid glass: sliding bubble spring, bounce on tap, 5 tabs reales
├── [x] Transiciones animadas entre tabs (lift animation, labels, haptic)
├── [x] LuminaToast system (glass card, spring, auto-dismiss, variantes)
├── [x] ScreenWrapper para stack screens (sin flash blanco Android)
├── [x] app.json backgroundColor #0e0e0e (window nativo Android)
├── [x] Todos los layouts con contentStyle + sceneStyle dark
├── [x] Componentes base: GlassCard, GlassButton, GlassInput, SectionLabel + theme tokens (2026-04-16)
└── [x] Skeleton loading components (HomeSkeleton, AgendaSkeleton, SpeakerDetailSkeleton, SponsorsSkeleton, NetworkingSkeleton, ContentListSkeleton)

PASO 2: Landing Web
├── Estructura HTML/CSS (o Next.js)
├── Hero con video + countdown
├── Secciones: speakers, agenda, sponsors, venue, FAQ
├── Form de registro embebido (con CAPTCHA)
├── OG tags + SEO
├── Parallax + scroll animations
└── Responsive + performance

PASO 3: Flujos de Registro & Auth — ⏳ PARCIAL
├── [ ] Pantalla confirmación post-registro (landing web)
├── [ ] Email de confirmación (con .ics adjunto + QR)
├── [ ] WhatsApp template de confirmación
├── [ ] 2FA (OTP por email/WhatsApp)
├── [x] Rate limiting login (throttle 5 intentos) + networking (100/evento, 30/dia) — SEC-6.1
├── [x] Account lockout (5 intentos → 30 min + mensaje "X intentos restantes") — SEC-3.3
├── [ ] Device fingerprinting (depende de 2FA)
├── [x] Progressive profiling: onboarding configurable con campos show_in (registration/onboarding/both)
├── [x] Registro cerrado: email_list/domain/both + access codes + invite_only — 1.x-F
├── [x] Pre-registro CSV: import → InvitationMail → deep link → activacion
├── [x] Staff invite: QR+busqueda+email+link, socket RT — 1.x-H
└── [x] Verificacion identidad: campo configurable, POST /auth/verify-identity

PASO 4: Estados del Evento — ✅ COMPLETADO (2026-04-14/15)
├── [x] Pantalla countdown (registration + published compact + "comienza hoy")
├── [x] Transiciones entre 5 estados (draft/registration/published/live/ended)
├── [x] Modo archivo (ended): banner + stats + archive links
├── [x] About pre-evento: imagen+texto+links, toggle Filament
├── [x] Encuesta post-evento: auto-activada al ended, PostEventSurveyResource Filament, 9 tests
├── [ ] Certificados de asistencia (PDF branding + nombre + horas)

PASO 5: Barrido Visual App — ✅ COMPLETADO (2026-04-15)
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
├── ✅ Social (2026-04-09 + 2026-04-11):
│   ├── [x] Unificado: Feed + Memorias + Momentos en una pantalla
│   ├── [x] PostCard Lumina Noir, likes animados, comments bottom sheet 55%
│   ├── [x] Memorias grid 3col, fotos oficiales 2col + badge OFICIAL
│   ├── [x] Momentos: stories simplificados (img, 24h, auto-advance 5s)
│   ├── [x] BottomSheet reutilizable, SegmentedControl glass pill
│   ├── [x] PhotoViewer + MomentosViewer fullscreen
│   ├── [x] CreatePostModal dark + SocialFAB contextual
│   ├── [x] ~30 bugs resueltos (gesture, stale closures, Android)
│   ├── [x] Header anclado con blur + Momentos sticky + orden Social>Momentos>Tabs
│   ├── [x] LuminaToast reemplaza Alert.alert en todo Social
│   └── [x] Momentos siempre visible (boton + sin guard stories.length)
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
├── ✅ Mi QR (2026-04-10):
│   ├── [x] Tab real (presencial+virtual), no stack push. Sin flecha
│   ├── [x] Badge digital: evento nombre+fecha, QR con RGB wave pastel 5px, identidad
│   ├── [x] Tap QR abre fullscreen modal con RGB border
│   ├── [x] Boton Wallet (coming soon, preparado para 1.C2)
│   ├── [x] Proporcional 360-411dp, dashed lines, role pill accent
│   └── [x] FloatingTabBar simplificado: Mi QR es tab real, no fake Pressable
├── ✅ Gamificacion / Desafio (2026-04-10):
│   ├── [x] GamificationHud: teal/cyan (#39d2c0), RGB wave pastel 2px, barra segmentada
│   ├── [x] HUD en carousel: 2da posicion (despues de 1 sesion), 10s duracion
│   ├── [x] Pantalla Desafio unificada: hero HUD + ranking inline + portal cards
│   ├── [x] Hero: rank + puntos + barra segmentada con % + mini ranking top 3
│   ├── [x] RGB wave border en hero cuando top 3, RGB ring en avatar #1
│   ├── [x] Portal cards verticales: Retos (count+barra+proximo) + Pasaporte (stamps dimmed)
│   ├── [x] Premios card: catalogo con estados (canjear/ver ticket/canjeado/agotado)
│   ├── [x] BottomSheets: ranking (podio+confetti), retos (sort completados), pasaporte, premios
│   ├── [x] QR canje temporal 5min con RGB rect + countdown
│   ├── [x] Reglas: boton info en header, BottomSheet con reglas + tabla puntos
│   ├── [x] Frase motivacional contextual (banner teal)
│   ├── [x] FadeInSection animaciones escalonadas
│   ├── [x] Toast LuminaToast en vez de Alert (success/error/info)
│   └── [x] returnKeyType en 14 archivos (search/next/go/send/done)
├── ✅ Transversales completados:
│   ├── [x] FloatingTabBar: lift, labels, sin circulos, 5 tabs reales, haptic
│   ├── [x] Notificaciones: badge rojo, shake 5s, MMKV persistence
│   ├── [x] RatingModal: cristales diamond, bounce, haptic, accent
│   ├── [x] Headers uniformes: arrow-left + titulo izquierda
│   ├── [x] Responsive 360dp: 31 archivos, 12 pantallas SafeArea, proporcional
│   ├── [x] Logout BottomSheet (36%), tab bar ajustado
│   ├── [x] Urbanist body + PlusJakartaSans headlines en toda la app
│   ├── [x] Flash blanco Android eliminado (app.json + layouts + ScreenWrapper)
│   ├── [x] Breathing carousel (Easing.out cubic), comments optimistic
│   └── [x] Background #1a1919 → #141414, console.log cleanup
├── ✅ Vendedor unificado (2026-04-10):
│   ├── [x] Vendedor usa tabs presencial (5 tabs iguales, directorio vendedor eliminado)
│   ├── [x] VendorHappeningNow: carousel 65% + Mi Stand card 28% + 3% gap (anchos fijos px)
│   ├── [x] Mi Stand card: breathing QR, leads pill, centrado, skeleton loading
│   ├── [x] VendorGamificationHud: HUD compacto con RGB border para espacio reducido
│   ├── [x] Routing: 6 archivos cambiados, vendedor → presencial
│   ├── [x] Backend: role vendedor agregado a todos los modulos presencial
│   └── [x] Backend: /me/stand devuelve logo_url, description, tier, website_url
├── ✅ Mi Stand premium (2026-04-10):
│   ├── [x] Hero card: logo sponsor squircle + nombre + descripcion completa + tier pill + role pill
│   ├── [x] Stats centrados: Total leads (tappable), Hoy (barra progreso), Equipo (slots/cupos + gestionar)
│   ├── [x] Export card + Ver todos: 2 cards glass simetricas con iconos 48px
│   ├── [x] Export BottomSheet: grid 1x4 (correo, WhatsApp, guardar, compartir) con confirmacion
│   ├── [x] Equipo BottomSheet 85%: add member input, activos con menu [...], pendientes amber
│   ├── [x] FAB scanner 72px: scan line animation + breathing + shadow
│   ├── [x] Leads recientes: solo nombre + cargo + tier pill + time-ago, dashed separators
│   ├── [x] Empty state: icono + texto + flecha al FAB
│   └── [x] Toasts LuminaToast en vez de Alert.alert
├── ✅ Lead detail + Leads list Lumina Noir (2026-04-10):
│   ├── [x] Lead detail: profile glass, tier icons (fire/sun/snow), notas glass, historial
│   ├── [x] Leads list: agrupados por fecha (Hoy/Ayer/fecha), glass cards, dashed separators
│   └── [x] Time-ago, pull-to-refresh, toasts, ScreenWrapper
├── ✅ FloatingTabBar Liquid Glass (2026-04-10):
│   ├── [x] Sliding bubble glass que se desliza entre tabs con spring (damping 22, stiffness 340)
│   ├── [x] Bounce on tap: scale 1.03 + translateY -2 (overshoot→settle, ref: iOS 26 AppStore)
│   ├── [x] Bubble medida con onLayout real, no calculos manuales
│   ├── [x] Bar transparente (bg 0.55), bubble sutil (bg 0.07, border 0.15), highlight superior
│   └── [x] Referencia: github.com/veersr9/AppStore-Tabbar (iOS 26 Liquid Glass)
├── ✅ Networking contactos (2026-04-10):
│   ├── [x] presentContactForm: Intent Android (sin permisos) + presentFormAsync iOS
│   ├── [x] Boton individual "Guardar en telefono" por contacto + toast
│   └── [x] Export masivo .vcf como opcion secundaria
├── ✅ Pantallas adicionales completadas:
│   ├── [x] Networking (directorio + contactos + solicitudes + guardar en telefono)
│   ├── [x] Matchmaking (sugeridos carousel + conectar + intereses comunes)
│   ├── [x] Auth screens → reemplazados por onboarding (login.tsx, register.tsx eliminados)
│   ├── [x] Onboarding visual → completado (layout fix, badge MiQR, gamificacion)
│   ├── [x] Session Detail: badges, titulo, rating, time/location, speakers tappables, botones accion (2026-04-14)
│   ├── [x] FAQ Asistente: orbe animado OrbBlob, categorias stagger, accordion Reanimated (2026-04-15)
│   ├── [x] Soporte: formulario asunto+mensaje, mis consultas con status badges (2026-04-15)
│   └── [x] Staff invite: mi-equipo, scanner-invite, join-team, StaffInvitationModal (2026-04-15)
├── ✅ Micro-interacciones (2026-04-11):
│   ├── [x] ScalePress: tap feedback scale 0.96 + haptic light (ModuleMenu, Speakers, Agenda, Networking)
│   ├── [x] Image reveal: transition={300} en 17 archivos expo-image (fade suave al cargar)
│   ├── [x] ContentFade: opacity 0→1 (400ms) cuando skeleton→contenido (sponsors, anuncios, speaker detail)
│   ├── [x] FadeInItem: stagger wave top→bottom para entradas coordinadas
│   ├── [x] AnimatedBadge: scale pop suave (damping 14) en HomeHeader + Networking badges
│   ├── [x] Haptics audit: leaderboard portal cards, profile edit/logout, social back, bell press
│   ├── [x] Screen transitions: slide_from_right en Stack (session-stream mantiene slide_from_bottom)
│   ├── [x] Gamification FadeInSection: stagger secuencial top→bottom, withTiming sin bounce
│   └── [x] Home wave entrance: HomeSkeleton → FadeInItem coordinado (Hero/HappeningNow/Modules)
├── ✅ Social overhaul (2026-04-11):
│   ├── [x] Header anclado con blur (titulo + Momentos + SegmentedControl fijos)
│   ├── [x] Momentos siempre visible (boton + disponible sin stories)
│   ├── [x] Orden: Social > Momentos > Feed/Memorias
│   ├── [x] Alert.alert → LuminaToast (post creado, foto subida, errores)
│   ├── [x] CreatePostModal: sin crop forzado, estado se limpia al publicar
│   ├── [x] EmptyState premium en feed y memorias vacios
│   └── [x] ContentFade en feed y memorias
├── ✅ Transversales resueltos (2026-04-11):
│   ├── [x] Skeleton loading: activo en 5+ pantallas (sponsors, anuncios, speaker, networking, home)
│   ├── [x] EmptyState: integrado en social feed, social memorias, speaker detail
│   ├── [x] Iconografia: ModuleMenu usa MaterialCommunityIcons (no emojis)
│   ├── [x] useNotifications: conditional require() — no crashea en Expo Go
│   └── [x] onboarding: useEffect antes de early returns — Rules of Hooks fix
├── ✅ Onboarding completo (2026-04-11):
│   ├── [x] Welcome DaVinci (5 pills, 5 backgrounds, primaryColor)
│   ├── [x] Auth (login/register animado, ForgotSheet BottomSheet, validacion español)
│   ├── [x] PhotoStep (avatar 180px, camara/galeria, upload + authStore update)
│   ├── [x] AboutStep (preview card live, cargo/empresa, foto del context)
│   ├── [x] InterestsStep (chips wrap, min 3, haptic, survey MMKV)
│   ├── [x] DoneStep (badge identico MiQR, QR real, tap fullscreen, confetti registro)
│   ├── [x] Gamificacion (AnimatedPts scale+particulas, SkipModal BottomSheet, 80pts max)
│   ├── [x] Banned screen Lumina Noir (motivo + expiracion + boton → onboarding)
│   ├── [x] Auth legacy eliminado (login.tsx, register.tsx, forgot-password.tsx borrados)
│   ├── [x] Password inputs corregidos (autoCapitalize none, 7 inputs)
│   ├── [x] Back arrow consistente (arrow-left, no chevron-left)
│   ├── [x] primary_text_color configurable desde Filament
│   ├── [x] contrastTextColor auto para botones
│   ├── [x] Foto persiste en context + invalida cache qr-token/my-profile
│   └── [x] Logout → onboarding auth directo (no login viejo)
├── ✅ Sesion 2026-04-12:
│   ├── [x] Pending-approval screen → Lumina Noir (clock icon, info blocks, ScalePress, LuminaToast)
│   ├── [x] Activate-account screen → Lumina Noir (StyleSheet puro, inputs glass, estados boton)
│   ├── [x] Login inteligente 2 pasos (check-email → password animado)
│   ├── [x] Activate-account redirige a onboarding photo step (flag post_activation)
│   ├── [x] ConnectionError screen reutilizable (wifi-off, reintentar, layout banned)
│   ├── [x] Error handling onboarding (6s timeout, spinner, ConnectionError)
│   ├── [x] Error handling Home presencial + virtual
│   ├── [x] Auditoria completa 39 escenarios + 10 edge cases
│   ├── [x] 9 bugs corregidos (BUG-065 a BUG-073)
│   └── [x] Fix registrationApprovedAt: 'auto' cuando evento no requiere approval
├── ✅ Moderacion chat completa (2026-04-12):
│   ├── [x] Ban real-time via socket (endpoint + listener + interceptor 403)
│   ├── [x] Middleware CheckBan server-side en todas las rutas
│   ├── [x] Palabras bloqueadas chat + Q&A (silent drop, config Filament)
│   ├── [x] Chat delete admin (app long press + chat monitor)
│   ├── [x] Ban desde chat (app long press → API ban → socket kick)
│   ├── [x] Chat monitor real-time por sesion (HTML standalone desde Filament)
│   ├── [x] Velocidad monitor (cola mensajes, directo/0.5s/1s/2s)
│   ├── [x] Slow mode + pause/resume configurable
│   ├── [x] Cache auth tokens 15min + connection pooling + message batching
│   └── [x] Filament: ChatSettingsResource + boton Chat en sesiones
├── ✅ Onboarding configurable Filament (2026-04-12):
│   ├── [x] JSON onboarding_steps_config en events (migration + model + API)
│   ├── [x] Filament UI: 7 secciones (Welcome, Auth, Photo, Forms, Survey, Done, Orden)
│   ├── [x] Welcome: pills dinamicas, hero image, show_text, title_prefix, textos botones
│   ├── [x] Auth: show_title/subtitle/stats, titulos, stats dinamicas, hide register link
│   ├── [x] Photo: titulo, subtitulo, puntos desde config
│   ├── [x] FormStep generico: campos dinamicos, tipos text/tel/email/number/url/select
│   ├── [x] InterestsStep: puntos + min_selections desde config
│   ├── [x] DoneStep: show_qr (virtual sin QR), show_hints, titulos, cta_text
│   ├── [x] Colores master/slave (ColorPicker en Filament, useStepColors helper)
│   ├── [x] Steps dinamicos en OnboardingContext (step_order, forms multiples)
│   ├── [x] Real-time: polling 30s pre-login + socket invalidation post-login
│   ├── [x] Seeder con config completa
│   ├── [x] Retrocompatibilidad: si config null, fallback a hardcoded
│   ├── [x] URLs imagenes: resolveStepsConfigUrls + fixStorageUrl
│   └── [x] Eliminados: OnboardingPreview, OnboardingBackground, OnboardingSlideResource
├── ✅ Tarea 1.x-E-A COMPLETADA (2026-04-12b):
│   ├── [x] select: SelectSheet BottomSheet radio con opciones, accent color, haptic
│   ├── [x] checkbox: Switch toggle inline, glass row, haptic
│   ├── [x] textarea: TextInput multiline 4 lineas, textAlignVertical top
│   ├── [x] validacion required: borde rojo + toast + skip oculto si required
│   ├── [x] PreviewCard live cuando form tiene job_title/company
│   ├── [x] opacidades subidas (labels 0.45, placeholders 0.3, subtitle 0.5)
│   ├── [x] Filament: checkbox + textarea en selector tipo, maxItems 4
│   ├── [x] Seeder: 2 forms (about 3 campos + extra 2 campos)
│   ├── [x] 13 bugs auth corregidos (ver QA-AUTH-ONBOARDING.md)
│   └── [ ] QA visual completo (multi-device)
├── ✅ Tarea 1.x-E-B COMPLETADA (2026-04-13):
│   ├── [x] searchable_select: SearchableSheet BottomSheet con busqueda + filtro local
│   ├── [x] checkbox_group: CheckboxGroupSheet multi-select con checkboxes + "Confirmar (N)"
│   ├── [x] date: DateSheet custom Lumina Noir (3 columnas scroll dia/mes/ano, no nativo)
│   ├── [x] Filament: 11 tipos en selector + campo preset para searchable_select
│   ├── [x] API presets: GET /presets/{type} (53 paises, 20 industrias, ciudades 9 paises)
│   ├── [x] OnboardingController: preset → preset_options inyectadas al servir config
│   └── [x] Seeder actualizado con ejemplos de los 3 tipos
├── ✅ Tarea 1.x-E-D COMPLETADA (2026-04-14):
│   ├── [x] Campos condicionales: depends_on en config JSON (pais → ciudades dinamicas)
│   ├── [x] preset_value_map para resolver codigos pais en campos asociativos
│   ├── [x] Fetch dinamico cities via /presets/cities/{code}
│   ├── [x] Reset child al cambiar parent + clear dynamicOptions
│   └── [x] Replay pre-fill: foto, profile, custom fields, intereses
├── ✅ Campos unificados registration_fields (2026-04-15):
│   ├── [x] registration_fields como fuente unica (elimina campos inline en onboarding_steps_config)
│   ├── [x] show_in (registration/onboarding/both) + depends_on avanzado (campo, campo:val, campo:!val)
│   ├── [x] field_ids en onboarding config, GET /events/{id}/onboarding-fields
│   └── [x] Deep merge Filament + legacy backward compat
├── ✅ Dev build + Push (2026-04-15):
│   ├── [x] Dev build Android nativo (wireless debugging adb)
│   ├── [x] Push notifications verificadas en dispositivo real
│   └── [x] Push tap navigation: support_resolved, announcement, agenda_reminder
├── 💤 Nice to have (no bloquea):
│   └── [ ] react-native-image-crop-picker: crop circular dark (requiere dev build, no Expo Go)

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

---

## 11. Web App — Spatial UI System (visionOS-inspired)

> Documentacion completa en `EventOS_Roadmap.md` → Apendice F → seccion W.0

La web app del asistente virtual NO usa sidebar corporativa. Usa un **sistema de paneles spatial** inspirado en visionOS, adaptado a Lumina Noir:

- **Pill bar flotante** arriba (no sidebar)
- **Max 3 paneles simultaneos** con jerarquia (primario + 2 secundarios)
- **Paneles arrastrables** + presets de layout (conferencia, networking, explorar)
- **Command palette** (Cmd+K) para power users
- **Memoria de layout** por usuario
- **Transiciones spring/damping**, nunca lineales
- En mobile (< 768px) colapsa a navegacion stack tradicional

Este sistema es la base sobre la que corren todos los features W.1–W.12. Se implementa en W.0 + W.1 (setup).

Referencias visuales: `design/LANDING/`

---

## 12. Showcase Onboarding (Web App — Demo Guiado)

> Prototipo HTML: `design/showcase-onboarding-v6.html` (version actual)
> Versiones anteriores: v1–v5 en `design/`

### Concepto

NO es un wizard paso-a-paso. Es un **demo guiado cinematico** que muestra las features del evento antes de entrar al home. Cada feature se demuestra con micro-interacciones (cursor fantasma, favoritos, match, typing) y al terminar se **minimiza hacia el pill bar**, activando su icono.

### Secuencia (8 beats + opening + finale)

| Beat | Feature | Que muestra | Micro-interaccion |
|------|---------|-------------|-------------------|
| Opening | Tech Summit 2026 | Texto cinematico (letras caen con peso, accent, parallax) | Stage shake al impactar |
| 1 | Speakers | 3 cards con fotos reales (pravatar), 3D entrance Eduard Bodak | Cursor toca estrella → "Agrega tus favoritos" |
| 2 | Agenda | Ventana con tabs Dia 1/2 + Mi Agenda. Sessions slide por dia | Cursor favorita sesion → cambia a Mi Agenda → boton streaming |
| 3 | Streaming | Player con wipe + chat con typing indicator + poll en vivo | Connected desde agenda (pill portal) |
| 4 | Networking + Social | Match cards 87% + connect + toast + social wall en un frame | Cursor toca "Conectar" → toast "Solicitud enviada" |
| 5 | Gamificacion | Leaderboard TEAL + puntos volando + stamp coin flip | Tu fila resaltada |
| 6 | Sponsors | Brand Wall: Platinum glass / Gold border / Silver circulos | Tiers animados escalonados |
| TNT | Finale | Pill bar tiembla (3 fases) → explota → flash → particulas | BIENVENIDO (nombre) impacto + pill bar renace |

### Efecto minimize-to-pill

Cada feature al terminar se encoge hacia su icono del pill bar usando `transformOrigin` apuntando al pill. El contenido converge visualmente en el icono. Al llegar: ring pulse + "+1" badge + elastic bounce. El pill queda "lit" (iluminado).

### Pill bar como progreso

- 6 iconos ghost (stroke tenue) al inicio
- Cada feature completa enciende su icono (stroke blanco + glow + dot)
- 6 progress dots debajo del bar
- Al llenar todos → TNT (temblor → explosion)

### Feature title (top-right)

Cada beat muestra categoria + nombre grande arriba-derecha (ej: "DISCOVER / Speakers"). Se va cuando la feature se minimiza.

### Tecnologia

- **GSAP 3** (core + CustomEase)
- **Canvas 2D** para particulas
- **CSS clip-path** para reveals
- **Fotos reales** via pravatar API (speakers, networking)
- **Estilos del app real**: cards rgba(38,38,38,.4), border-radius 14/18/20, Urbanist + Plus Jakarta Sans

### Pendientes del prototipo

- [ ] Panels interactivos al final (click para abrir/cerrar) — z-index blocking no resuelto
- [ ] Audio/sonido (requiere boton de inicio para desbloquear autoplay)
- [ ] Responsive (1200x720 fijo, no escala sin blur en pantallas grandes)
- [ ] Labels/hints posicionamiento fino
- [ ] Social wall mejor explicado visualmente

### Referencias tecnicas

- Eduard Bodak portfolio (Codrops julio 2025): 3D card flips, keyframes multi-step, elastic.out(1,0.75)
- GSAP Skills repo: `design/gsap-skills/` (core, timeline, plugins, performance)
- FIFA Ultimate Team card reveals (entrada dramatica)

---

---

## Estado actual (2026-04-15)

| Metrica | Valor |
|---------|-------|
| Backend tests | 465 tests, 1168 assertions |
| Bugs resueltos | BUG-001 a BUG-103 |
| Pantallas app completadas | 25+ (todas Lumina Noir) |
| Paso 1 (Fundamentos) | ✅ Completado |
| Paso 2 (Landing Web) | Pendiente |
| Paso 3 (Registro & Auth) | ⏳ Parcial (rate limit, lockout, registro cerrado, staff invite, CSV) |
| Paso 4 (Estados Evento) | ✅ Completado |
| Paso 5 (Barrido Visual) | ✅ Completado |
| Paso 6 (Admin Premium) | Pendiente |
| FAQ Asistente | ✅ Implementado |
| Lifecycle 5 estados | ✅ Implementado |
| Onboarding configurable | ✅ Implementado |
| Dev build Android | ✅ Push probado |

*Documento vivo — se actualiza conforme avanza la implementación.*
