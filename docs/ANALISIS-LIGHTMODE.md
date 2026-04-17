# Analisis Light Mode — Pantalla por Pantalla

> Auditoria completa de 113 archivos .tsx. Cada pantalla evaluada para Lumina Lux.
> Fecha: 2026-04-16
> Estado: BORRADOR — pendiente aprobacion DaVinci

---

## Resumen ejecutivo

| Categoria | Archivos | Estado |
|-----------|----------|--------|
| Ya en light mode (no tocar) | 10 | ChatPanel, QnAPanel, PollPanel, RatingModal, banners, documentos, encuestas, pages, passport, session-chat |
| Usan theme tokens (se adaptan solos) | 5 | GlassCard, GlassButton, GlassInput, SectionLabel, session/[id] |
| Dark hardcodeado (necesitan migracion) | ~85 | Todo el resto |
| BlurView tint="dark" (rompe en Lux) | 12 instancias | FloatingTabBar, ModuleMenu, HappeningNow, sponsors, speakers, social, PollSlides, LuminaToast, OrbBlob, NetworkingScreen, VendorHappeningNow, sponsor/[id] |
| LinearGradient (colores dark) | 9 archivos | Onboarding (3), leaderboard, sponsors, GamificationHud (2), MiQrScreen, sponsor/[id] |
| QR codes | 3 archivos | OK en ambos modos (negro sobre blanco) |

---

## Problemas criticos que rompen la app en Lux

### 1. StatusBar — texto blanco sobre fondo claro
- `app/_layout.tsx`: `<StatusBar style="light" />`
- **Solucion**: cambiar a dinamico segun tema

### 2. Layout backgrounds — negro hardcodeado
- `app/_layout.tsx`: `contentStyle: { backgroundColor: '#0e0e0e' }`
- `app/(app)/_layout.tsx`: misma cosa
- `app/(app)/(tabs)/_layout.tsx`: `sceneStyle: { backgroundColor: '#0e0e0e' }`
- **Solucion**: leer de tokens

### 3. FloatingTabBar — todo invisible
- Fondo: `rgba(20,20,22,0.55)` — casi negro
- Iconos inactivos: `rgba(255,255,255,0.35)` — blanco invisible en Lux
- Bubble: `rgba(255,255,255,0.07)` — invisible
- BlurView `tint="dark"`
- **Solucion**: todos los colores a tokens, tint dinamico

### 4. BottomSheet — modal oscuro en app clara
- Sheet bg: `#141414` hardcodeado
- Handle: `rgba(255,255,255,0.2)`
- Usado por: DateSheet, CheckboxGroupSheet, SearchableSheet, SelectSheet, StaffInvitationModal, ProfileScreen, SkipModal, ForgotSheet
- **Solucion**: bg y handle a tokens

### 5. Skeleton loaders — blanco sobre blanco
- `Bone` bg: `#fff` con opacity 0.06-0.14
- En Lux: completamente invisible
- **Solucion**: color base a token (negro en Lux con misma opacity)

### 6. EmptyState — todo invisible
- Icono: `rgba(255,255,255,0.2)`, titulo: `rgba(255,255,255,0.5)`
- Usado en 8+ pantallas
- **Solucion**: colores a tokens

### 7. RefreshControl — indicador oscuro
- 8+ instancias con `tintColor="rgba(255,255,255,0.25)"` y `progressBackgroundColor="#151515"`
- **Solucion**: centralizar en LuminaRefresh.tsx, leer de tokens

---

## Pantalla por pantalla

### HOME (tabs/index.tsx + sub-componentes)

| Componente | Problema en Lux | Nivel |
|------------|-----------------|-------|
| HomeHeader | Logo `#FFFFFF` invisible, bell bg `rgba(255,255,255,0.06)` invisible, badge border `#0e0e0e` | Critico |
| HomeHero | Titulo `#FFFFFF` invisible, overlay `rgba(0,0,0,0.45)` puede verse bien, skeleton bones invisible | Critico |
| HappeningNow | BlurView dark, card bg `rgba(28,28,30,0.35)` muy oscuro, location `#adaaaa` ok, avatar border dark | Alto |
| ModuleMenu | BlurView dark, card bg `rgba(28,28,30,0.35)`, iconos `rgba(255,255,255,0.55)` invisible, label `#FFFFFF` invisible | Critico |
| ModuleMenuCompact | Pill bg `rgba(255,255,255,0.03)` invisible, icon y label rgba blanco | Alto |
| GamificationHud | Inner bg `#0e0e0e` negro solido, TEAL/CYAN visibles en ambos, WAVE_COLORS pastel ok | Medio |
| VendorHappeningNow | MiStandCard bg `#131313` negro, BlurView dark | Alto |
| CountdownTimer | Box bg `rgba(255,255,255,0.03)` invisible, nums `rgba(255,255,255,0.85)` invisible | Critico |
| EventInfoCard | Card bg invisible, iconos y texto rgba blanco invisible | Critico |
| EventArchive | Todo rgba blanco invisible | Critico |
| About card (inline) | bg `rgba(255,255,255,0.03)` invisible | Alto |
| RefreshControl | Oscuro | Medio |

### AGENDA (AgendaScreen.tsx)

| Elemento | Problema en Lux | Nivel |
|----------|-----------------|-------|
| Container | `#0e0e0e` negro | Critico |
| Header title | `#FFFFFF` invisible | Critico |
| Back btn | bg `rgba(255,255,255,0.06)` invisible | Alto |
| Day pills | bg `#141414` negro, weekday `#adaaaa` ok, number `#FFFFFF` invisible | Critico |
| Track pills | border `#494847` — podria funcionar, text `#adaaaa` ok | Medio |
| Session cards | bg `rgba(38,38,38,0.4)` oscuro, title `#FFFFFF` invisible | Critico |
| Timeline connector | `#494847` con opacity 0.3 — muy tenue en Lux | Medio |
| Timeline dots | finished `#494847`, upcoming `#777575` — visibles en ambos | OK |
| Action pills | bg `rgba(255,255,255,0.06)` invisible, text `#adaaaa` ok | Alto |
| Heart unfilled | `rgba(255,255,255,0.4)` invisible | Alto |
| Skeleton cards | bg `rgba(38,38,38,0.4)` — visible en ambos | OK |
| Calendar btn inline | bg `rgba(255,255,255,0.06)` invisible | Alto |

### MI AGENDA (mi-agenda.tsx)
Wrapper de AgendaScreen con `favoritesOnly=true`. Mismos problemas.

### PROFILE (ProfileScreen.tsx)

| Elemento | Problema en Lux | Nivel |
|----------|-----------------|-------|
| Container | `#0a0a0a` negro | Critico |
| Header title | `#fff` invisible | Critico |
| Help btn | bg `rgba(255,255,255,0.04)` invisible | Alto |
| Avatar border | `rgba(255,255,255,0.08)` invisible | Alto |
| Avatar badge | bg `rgba(255,255,255,0.1)` invisible, border `#0a0a0a` | Alto |
| Username | `#fff` invisible | Critico |
| User subtitle | `rgba(255,255,255,0.55)` invisible | Critico |
| Social icons | bg `rgba(255,255,255,0.04)` invisible | Alto |
| Stat cards | bg `rgba(255,255,255,0.025)` invisible | Critico |
| Data card | bg `rgba(255,255,255,0.025)` invisible, rows invisible | Critico |
| Edit link | `rgba(139,92,246,0.8)` morado hardcodeado — visible en ambos | OK |
| Secondary btn | bg `rgba(255,255,255,0.03)` invisible | Alto |
| Logout btn | border `rgba(239,68,68,0.2)` tenue pero visible | OK |
| Edit modal sheet | bg `#141414` negro | Critico |
| Edit field inputs | bg `rgba(255,255,255,0.05)` invisible | Alto |

### SPEAKERS (speakers.tsx + speaker/[id].tsx)

| Elemento | Problema en Lux | Nivel |
|----------|-----------------|-------|
| Container | `#0a0a0a` negro | Critico |
| Featured carousel | BlurView dark | Alto |
| Speaker cards | Probablemente rgba blanco | Alto |
| Speaker detail | `#0a0a0a`, stars `#FFD700` ok, social colors ok | Critico bg, resto ok |

### SPONSORS (sponsors.tsx + sponsor/[id].tsx)

| Elemento | Problema en Lux | Nivel |
|----------|-----------------|-------|
| Brand Wall | BlurView dark (platinum), LinearGradient gold | Alto |
| Brand Profile | BlurView nav, gradients para servicios, trivia purple | Alto |
| Tier colors | platinum `#e5e4e2`, gold `#ffd700`, silver `#c0c0c0` — visibles en ambos con fondo claro | OK |

### SOCIAL (social.tsx + componentes social/)

| Elemento | Problema en Lux | Nivel |
|----------|-----------------|-------|
| Header sticky | BlurView dark intensity=50 | Alto |
| SegmentedControl | Container `rgba(255,255,255,0.04)` invisible, inactive label invisible | Alto |
| PostCard | Probablemente todo rgba blanco | Critico |
| CommentsSheet | Titulo `#fff`, comment bubble invisible | Alto |
| CreatePostModal | bg `#0e0e0e` negro, overlay oscuro | Critico |
| MomentosRow | Upload circle invisible, avatar ring invisible | Alto |
| MomentosViewer | bg `#000` — intencionalmente oscuro (fullscreen media) — **mantener negro** | Excepcion |
| PhotoViewer | bg `#000` — **mantener negro** (fullscreen media viewer) | Excepcion |
| PhotoGrid | Tile bg invisible, heart inactive invisible | Alto |
| SocialFAB | bg=accent (dinamico) — OK | OK |

### NETWORKING (NetworkingScreen.tsx)

| Elemento | Problema en Lux | Nivel |
|----------|-----------------|-------|
| Container | `#0e0e0e` negro | Critico |
| Tab bar | inactive bg `#141414` negro | Critico |
| Search input | bg `#141414`, border invisible | Critico |
| Suggested cards | BlurView dark, bg oscuro | Alto |
| Contact cards | Probablemente rgba blanco | Alto |
| Request badges | `#ef4444` rojo, `#22c55e` verde — ok en ambos | OK |

### GAMIFICATION / LEADERBOARD (leaderboard.tsx)

| Elemento | Problema en Lux | Nivel |
|----------|-----------------|-------|
| Container | Probablemente `#0e0e0e` | Critico |
| TEAL/CYAN palette | Visibles en ambos modos | OK |
| WAVE_COLORS pastel | Visibles en ambos | OK |
| Inner bg cards | `#0a0a0a` negro | Alto |
| QR redemption | bg `#ffffff`, color `#0a0a0a` — ok | OK |
| Confetti colors | Pasteles — ok en ambos | OK |
| Gold `#FFD700` | Visible en ambos | OK |

### MI QR (MiQrScreen.tsx)

| Elemento | Problema en Lux | Nivel |
|----------|-----------------|-------|
| Container | `#0a0a0a` negro | Critico |
| QR code | Negro sobre blanco — ok | OK |
| RGB wave border | Pasteles — ok en ambos | OK |
| Badge card | bg `rgba(255,255,255,0.03)` invisible, TODA la info invisible | Critico |
| Fullscreen modal | bg `rgba(0,0,0,0.88)` — **mantener negro** (modal QR) | Excepcion |
| Wallet btn | Probablemente rgba blanco invisible | Alto |

### STREAMING (session-stream/[id].tsx)

| Elemento | Problema en Lux | Nivel |
|----------|-----------------|-------|
| Container | `#0e0e0e` negro — pero es pantalla de video | Decision |
| Video area | WebView/YouTube — no afectado | OK |
| Chat/Q&A/Poll panels | ChatPanel y QnAPanel ya son light, PollSlides tiene BlurView dark | Mixto |
| PinnedBanner | bg `rgba(255,255,255,0.04)` invisible | Alto |

**Decision**: La pantalla de streaming podria mantener fondo oscuro (es cine). Los panels ya son light. Solo PollSlides y PinnedBanner necesitan ajuste.

### ONBOARDING (5 steps + shared)

| Step | Problema en Lux | Nivel |
|------|-----------------|-------|
| WelcomeStep | LinearGradient fade a `#08080a`, bg negro | Critico |
| AuthStep | Sheet bg `rgba(14,14,16,0.92)`, LinearGradient dark | Critico |
| PhotoStep | bg `#08080a`, avatar circle invisible | Critico |
| FormStep | Overlay `rgba(8,8,10,0.92)`, inputs invisible | Critico |
| AboutStep | bg `#08080a`, preview card invisible | Critico |
| InterestsStep | bg `#08080a`, chips rgba blanco | Critico |
| DoneStep | bg `#08080a`, QR ok, confetti ok, RGB wave ok | Critico bg |
| OnboardingShared | ProgressDots inactive invisible, BackButton invisible, buttons glass invisible | Alto |
| AnimatedPts | Gold `#FFD700` — visible en ambos | OK |
| PointsPill | Gold — ok | OK |

**Decision**: El onboarding tiene primaryColor dinamico del evento. En Lux, el gradiente y fondo deben cambiar a claro. Los particles/confetti/gold/wave son colores vibrantes que funcionan en ambos.

### AUTH (activate-account, pending-approval, banned)

| Pantalla | Problema en Lux | Nivel |
|----------|-----------------|-------|
| activate-account | `#08080a` negro, inputs invisible | Critico |
| pending-approval | `#08080a` negro, info blocks invisible | Critico |
| banned | `#08080a` negro, icono invisible | Critico |

### FAQ (faq.tsx + OrbBlob.tsx)

| Elemento | Problema en Lux | Nivel |
|----------|-----------------|-------|
| Container | Probablemente negro | Critico |
| OrbBlob | Los colores (cyan, pink, purple, core) son vibrantes y visibles en ambos. El halo `rgba(140,200,220,0.06)` es invisible pero decorativo | Bajo |
| Categorias | Chips probablemente rgba blanco invisible | Alto |
| Accordion | Respuestas probablemente rgba blanco invisible | Alto |
| Teal accent `#5DE4D4` | Hardcodeado, no del themeStore — visible en ambos | OK pero inconsistente |

### SOPORTE (support-contact.tsx + my-support.tsx)

| Pantalla | Problema en Lux | Nivel |
|----------|-----------------|-------|
| support-contact | `#0e0e0e` negro, send btn interesante: bg `rgba(255,255,255,0.9)` text `#0a0a0f` — ya casi light! | Alto |
| my-support | Status badges con colores semanticos — probablemente ok | Medio |

### MI STAND (mi-stand.tsx) + LEADS (leads.tsx, lead-detail.tsx)

| Pantalla | Problema en Lux | Nivel |
|----------|-----------------|-------|
| mi-stand | Negro, tier colors ok, FAB breathing shadow ok | Critico bg |
| leads | Negro, glass cards invisible | Critico |
| lead-detail | Negro, tier badges ok | Critico bg |

### SCANNERS (scanner-invite.tsx, scanner-stand.tsx)

Pantallas de camara — fondo es el viewfinder de la camara. Los overlays pueden mantenerse oscuros (mejor contraste con scan line). **Excepcion — mantener dark.**

### MI EQUIPO (mi-equipo.tsx)

Negro hardcodeado. WhatsApp `#25d366` ok en ambos. Remove btn rojo ok.

### ABOUT (about.tsx)

Negro, info rows invisibles. Modality colors (verde/azul/violeta) ok en ambos.

### ANUNCIOS (anuncios.tsx)

Negro, purple dot `rgba(139,92,246,0.6)` ok en ambos. Text invisible.

---

## Elementos que se MANTIENEN oscuros (excepciones)

Estas pantallas/componentes deben quedarse en dark mode incluso en Lux:

1. **MomentosViewer** — fullscreen media, negro es estandar
2. **PhotoViewer** — fullscreen media
3. **QR fullscreen modal** — negro para contraste con QR
4. **Scanner cameras** — viewfinder necesita overlay oscuro
5. **Streaming video area** — experiencia cine (decision pendiente)

---

## Colores semanticos que NO cambian por tema

| Color | Uso | Cambiar? |
|-------|-----|----------|
| `#EF4444` / `rgba(239,68,68,x)` | Error, eliminar, logout, badges alertas | NO |
| `#22c55e` / `#34d399` | Exito, contacto aceptado, presencial | NO |
| `#60a5fa` | Info, virtual | NO |
| `#c084fc` | Hibrido | NO |
| `#FFD700` | Gold, puntos, estrellas rating | NO |
| `#FBBF24` | Evaluar badge | NO |
| `#39d2c0` / `#5eead4` | Gamification TEAL/CYAN | NO |
| `#0A66C2` | LinkedIn | NO |
| `#1DA1F2` | Twitter | NO |
| `#E4405F` | Instagram | NO |
| `#25d366` | WhatsApp | NO |
| Tier colors (platinum, gold, silver, bronze, media) | Sponsors/leads | NO |
| WAVE_COLORS pastel | RGB borders decorativos | NO |
| CONFETTI_COLORS | Celebraciones | NO |

---

## Colores hardcodeados morados (inconsistencia)

Estos usan morado hardcodeado en vez del accent del themeStore:

| Archivo | Color | Deberia ser |
|---------|-------|-------------|
| MyInterests.tsx | `rgba(139,92,246,0.x)` | accent del themeStore |
| MyRegistrationFields.tsx | `rgba(139,92,246,0.x)` | accent del themeStore |
| ProfileScreen.tsx (editBtn, modalSave) | `rgba(139,92,246,0.8)` | accent del themeStore |
| CountdownTimer.tsx | default `#a78bfa` | accent del themeStore |
| faq.tsx | `#5DE4D4` teal hardcodeado | accent del themeStore |

---

## Resumen de magnitud

| Accion | Archivos afectados |
|--------|-------------------|
| Cambiar fondo container a token | ~45 archivos |
| Cambiar textos rgba(255,255,255,x) a token | ~50 archivos |
| Cambiar BlurView tint a dinamico | 12 instancias |
| Cambiar LinearGradient colores | 9 archivos |
| Cambiar BottomSheet base | 1 archivo (impacta 8+ sheets) |
| Cambiar Skeleton base | 1 archivo (impacta 6 layouts) |
| Cambiar EmptyState | 1 archivo (impacta 8+ pantallas) |
| Cambiar FloatingTabBar | 1 archivo (impacta toda la app) |
| Cambiar RefreshControl | 1 archivo (LuminaRefresh) + 7 inline |
| Cambiar StatusBar | 1 archivo |
| Cambiar layout backgrounds | 3 archivos |
| Corregir morados hardcodeados | 5 archivos |
| NO tocar (excepciones dark) | 5 pantallas |
| NO tocar (ya light) | 10 archivos |
| NO tocar (colores semanticos) | ~15 colores |
