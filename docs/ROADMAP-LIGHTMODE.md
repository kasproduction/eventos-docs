# Roadmap Light Mode — Conversion Noir → Lux

> Plan de ejecucion paso a paso. Cada fase es independiente y verificable.
> Basado en auditoria de 113 archivos (docs/ANALISIS-LIGHTMODE.md)
> Fecha: 2026-04-16 | Aprobado visualmente: HTML v2 con 7 pantallas
>
> **Decisiones aprobadas:**
> - Accent dual: `primary_color` (Noir) + `primary_color_light` (Lux) desde Filament
> - Fallback: si accent Lux tiene luminance > 0.7, textos accent usan `#1A1A1A`
> - Fondo Lux: `#F0F0ED` (beige calido, no blanco puro)
> - Cards Lux: `#FFFFFF` con shadow Airbnb (0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.08))
> - Texto Lux: `#1A1A1A` primario (nunca negro puro), `rgba(44,47,48,0.45)` secundario
> - Excepciones dark: MomentosViewer, PhotoViewer, QR modal, Scanners, Streaming video

---

## Orden de trabajo

```
FASE 1: Migrar pantallas a componentes base (solo Noir, tokens fijos)
FASE 2: Tokens dinamicos + useTheme() hook
FASE 3: Componentes compartidos theme-aware (14 archivos → impacta 85 pantallas)
FASE 4: Pantallas principales (Home, Agenda, Profile, Networking, Social)
FASE 5: Pantallas secundarias
FASE 6: Onboarding (gradientes + fondos animados)
FASE 7: Backend + Filament (accent dual, toggle tema)
FASE 8: QA visual completo
```

**Por que este orden:**
- Fase 1 primero: unificar TODO bajo componentes base. Si no, al hacer tokens dinamicos tocamos 85 archivos con rgba sueltos.
- Fase 2 despues: los tokens se vuelven dinamicos. Los componentes base se adaptan solos.
- Fase 3-6: migrar lo que queda pantalla por pantalla.
- Fase 7-8: backend y QA al final.

---

## FASE 1: Migrar pantallas a componentes base — solo Noir (~6h)

> Objetivo: que toda la app use GlassCard, GlassButton, GlassInput, SectionLabel y tokens de theme.ts.
> Esto elimina los 573 rgba hardcodeados y centraliza todo en 1 archivo.

### 1.1 — Home + sub-componentes (~1.5h)
- HomeHeader: logo, bell → usar tokens (surface, text, fonts)
- HomeHero: titulo, skeleton → tokens
- HappeningNow: card inner → GlassCard, location text → text tokens
- ModuleMenu: card inner → GlassCard, icon/label → text tokens
- ModuleMenuCompact: pill → GlassCard variant, icon/label → tokens
- CountdownTimer: boxes → GlassCard, nums/labels → text tokens
- EventInfoCard: card → GlassCard, rows → tokens
- EventArchive: cards → GlassCard, textos → tokens
- About card inline → GlassCard

### 1.2 — Agenda (~1.5h)
- Container bg → surface.background
- Header, back btn → tokens
- Day pills bg → surface.backgroundElevated
- Track pills → tokens
- Session cards → GlassCard + tokens
- Timeline connector/dots → tokens
- Action pills → tokens
- Heart unfilled → text.muted

### 1.3 — Profile (~1h)
- Container → surface.background
- Help btn → GlassButton variant glass
- Avatar border/badge → surface tokens
- Social icons → surface tokens (brand colors mantener)
- Stat cards → GlassCard
- Data card → GlassCard
- Edit fields → GlassInput
- Secondary/logout btns → GlassButton variants
- Edit modal → BottomSheet ya existe

### 1.4 — Networking (~1h)
- Container, tabs → tokens
- Search input → GlassInput
- Suggested/contact/request cards → GlassCard + tokens

### 1.5 — Social (~1h)
- PostCard → GlassCard + tokens
- CommentsSheet → tokens
- CreatePostModal → tokens
- MomentosRow → tokens
- PhotoGrid → tokens
- SegmentedControl → tokens

**Verificacion Fase 1**: Toda la app se ve IDENTICA en Noir. Cero cambios visuales. Pero internamente todo usa tokens centralizados.

---

## FASE 2: Tokens dinamicos + useTheme() (~2h)

### 2.1 — theme-noir.ts
Mover valores actuales de theme.ts (surface, text como estan).

### 2.2 — theme-lux.ts
Valores aprobados del HTML v2:
```
surface.background = '#F0F0ED'
surface.backgroundElevated = '#FFFFFF'
surface.low = '#FFFFFF'  (cards — con shadow)
surface.medium = '#FFFFFF'
surface.high = 'rgba(0,0,0,0.04)'
surface.border = 'rgba(0,0,0,0.06)'  (ghost border, solo si necesario)
surface.borderLight = 'rgba(0,0,0,0.1)'

text.primary = '#1A1A1A'
text.secondary = 'rgba(44,47,48,0.5)'
text.muted = 'rgba(44,47,48,0.35)'
text.label = 'rgba(44,47,48,0.25)'
text.placeholder = 'rgba(44,47,48,0.2)'
text.white = '#1A1A1A'  (invertido — "high contrast" text)

shadow.card = { shadowColor:'#000', shadowOffset:{width:0,height:2}, shadowOpacity:0.06, shadowRadius:8 }
shadow.elevated = { shadowColor:'#000', shadowOffset:{width:0,height:4}, shadowOpacity:0.1, shadowRadius:16 }
shadow.none = {}  (Noir no usa sombras)

blur.tint = 'light'  (vs 'dark' en Noir)
```

### 2.3 — Refactor theme.ts
- `useTheme()` hook → lee themeMode de themeStore, devuelve noir o lux
- Mantener exports estaticos como aliases de noir (backward compat temporal)
- radius, spacing, fonts NO cambian

### 2.4 — themeStore.ts
- Agregar `themeMode: 'noir' | 'lux'` + `setTheme()`
- Agregar `accentLight: string` (accent Lux)
- Persistir en MMKV `@app/theme-mode`
- `getAccent()` → devuelve accent correcto segun tema activo
- Fallback: si accent Lux luminance > 0.7, textos accent = `#1A1A1A`

### 2.5 — Layouts root
- `app/_layout.tsx`: StatusBar style dinamico
- `app/(app)/_layout.tsx`: contentStyle bg de token
- `app/(app)/(tabs)/_layout.tsx`: sceneStyle bg de token

**Verificacion Fase 2**: Toggle manual Noir/Lux. Fondos base cambian. Componentes base (GlassCard etc) se adaptan automaticamente. Pantallas migradas en Fase 1 se ven correctas en ambos temas.

---

## FASE 3: Componentes compartidos theme-aware (~3h)

### 3.1 — FloatingTabBar.tsx (TODA la app)
- BlurView tint → `theme.blur.tint`
- Bar bg → token (Noir: rgba oscuro, Lux: rgba blanco)
- Bubble bg/border → tokens
- Inactive icons → `text.muted`
- Shadow → `theme.shadow.elevated` (solo Lux)

### 3.2 — BottomSheet.tsx (8+ modales)
- Sheet bg → `surface.backgroundElevated`
- Handle → `text.muted`

### 3.3 — Skeleton.tsx (6 layouts)
- Bone color → `text.primary` con misma opacity pulsante
- (En Noir: blanco al 6-14%. En Lux: negro al 6-14%)

### 3.4 — EmptyState.tsx (8+ pantallas)
- Icon bg/color, titulo, subtitulo, action btn → tokens

### 3.5 — LuminaRefresh.tsx (8+ pantallas)
- tintColor → `text.muted`
- progressBackgroundColor → `surface.backgroundElevated`

### 3.6 — LuminaToast.tsx (toda la app)
- BlurView tint → `theme.blur.tint`
- Toast bg → token
- Icon colors semanticos → NO cambiar

### 3.7 — ConnectionError.tsx
- Container, icon, textos, boton → tokens

### 3.8 — GlassCard update para Lux
- En Lux: `backgroundColor: '#FFFFFF'` + shadow ambient (no rgba transparente)
- En Noir: mantener rgba actual
- `bordered` prop: default true en Noir, false en Lux (shadow reemplaza border)

### 3.9 — Corregir morados hardcodeados
- MyInterests.tsx → accent del themeStore
- MyRegistrationFields.tsx → accent del themeStore
- ProfileScreen.tsx (editBtn, modalSave) → accent
- CountdownTimer.tsx default → accent
- faq.tsx teal → accent

**Verificacion Fase 3**: Abrir app en Lux. Tab bar, modales, skeletons, empty states, toasts, refresh visible y correcto en ambos temas.

---

## FASE 4: Pantallas principales theme-aware (~3h)

Todo lo que NO se migro en Fase 1 (colores que no son surface/text basicos):

### 4.1 — Home
- HappeningNow: BlurView tint → token
- ModuleMenu: BlurView tint → token
- GamificationHud: inner bg → surface.background
- VendorHappeningNow: MiStandCard bg, BlurView → tokens
- RefreshControl → ya cubierto en Fase 3

### 4.2 — Agenda
- Nada extra (todo cubierto en Fase 1)

### 4.3 — Profile
- Nada extra (todo cubierto en Fase 1 + 3)

### 4.4 — Networking
- Suggested cards BlurView tint → token

### 4.5 — Social
- Header sticky BlurView tint → token
- CreatePostModal bg → token

**Verificacion Fase 4**: Las 5 pantallas principales perfectas en ambos temas.

---

## FASE 5: Pantallas secundarias (~3h)

### 5.1 — Speakers + Speaker detail
- Container, cards, BlurView featured → tokens
- Stars gold, social colors → mantener

### 5.2 — Sponsors + Brand Profile
- BlurView tints → tokens
- Tier colors → mantener
- Trivia gradients → tokens

### 5.3 — Gamification + Leaderboard
- Container → tokens
- Inner bg cards → surface.background
- TEAL/CYAN/GOLD → mantener

### 5.4 — Mi QR
- Container, badge card → tokens
- QR code → OK (ya negro/blanco)
- RGB wave → OK (pasteles)

### 5.5 — Mi Stand + Leads + Lead detail
- Containers → tokens
- Tier colors → mantener

### 5.6 — FAQ + OrbBlob
- Container, categorias, accordion → tokens
- OrbBlob: colores vibrantes OK en ambos, halo tenue → aceptable

### 5.7 — Streaming
- Mantener video area oscura
- PollSlides BlurView tint → token
- PinnedBanner → tokens (ya en Fase 3)

### 5.8 — Soporte, About, Anuncios, Mi Equipo, join-team
- Containers + textos → tokens

### 5.9 — Auth (activate-account, pending-approval, banned)
- Containers, inputs, info blocks → tokens

**Verificacion Fase 5**: Barrido completo todas las pantallas secundarias en ambos temas.

---

## FASE 6: Onboarding (~2h)

Caso especial — gradientes + fondos animados + primaryColor dinamico.

### 6.1 — Fondos animados (OnboardingShared.tsx)
5 tipos: particles, constellation, mesh, bubbles, minimal.
- Todos usan `primaryColor` del evento como color base → ya son dinamicos
- En Noir: particulas/lineas claras sobre fondo negro → OK
- En Lux: particulas/lineas en accent sobre fondo claro
- **Cambiar**: color base de particulas a accent, fondo de `#08080a` → `surface.background`
- **Nota**: constellation y mesh usan lineas tenues. En Lux pueden necesitar mas opacidad para ser visibles sobre fondo claro. Probar.

### 6.2 — WelcomeStep
- Gradient: `transparent → rgba(8,8,10,0.6) → #08080a` → cambiar a `transparent → rgba(lux-bg,0.6) → lux-bg`
- Pills flotantes (BannerWide, StatSquare): bg → GlassCard / surface token
- Titulo, subtitle → text tokens
- Botones → OnboardingButton ya usa primaryColor

### 6.3 — AuthStep
- Sheet bg `rgba(14,14,16,0.92)` → Lux: `rgba(255,255,255,0.95)` con shadow
- Gradient fade → adaptar a tema
- Inputs → tokens
- StatCards → usan pcA (primaryColor alpha) → OK en ambos

### 6.4 — PhotoStep
- bg → surface.background
- Ghost avatar border → usa pcA → OK
- Ghost avatar icon `rgba(255,255,255,0.3)` → text.muted

### 6.5 — FormStep
- Overlay bg → adaptar a tema
- Inputs, switch → tokens

### 6.6 — InterestsStep
- Chips default: `rgba(255,255,255,0.04)` → surface.low
- Chips selected: usa pcA → OK en ambos

### 6.7 — DoneStep
- bg → surface.background
- QR → OK (negro/blanco)
- RGB wave → OK (pasteles)
- Confetti → OK (pasteles vibrantes)
- Gold `#FFD700` → OK

### 6.8 — Shared (ProgressDots, BackButton, OnboardingButton)
- ProgressDots inactive → surface tokens
- BackButton bg/border/icon → tokens
- OnboardingButton glass variant → tokens

**Verificacion Fase 6**: Flujo completo welcome → done en ambos temas. Fondos animados visibles y esteticos en Lux.

---

## FASE 7: Backend + Filament toggle (~1.5h)

### 7.1 — Migration
- `primary_color_light` string nullable en events (accent Lux)
- `default_theme` enum('noir','lux') default 'noir' en events

### 7.2 — Filament EventBranding
- Seccion "Apariencia":
  - Select tema default (Noir/Lux)
  - ColorPicker accent Noir (existente `primary_color`)
  - ColorPicker accent Lux (`primary_color_light`)
  - Si Lux vacio → auto-calcular version oscurecida del Noir accent
  - Preview: muestra dot con ambos colores

### 7.3 — API branding
- Incluir `default_theme` y `primary_color_light` en respuesta

### 7.4 — App
- Leer `branding.default_theme` → aplicar al cargar evento
- Leer `branding.primary_color_light` → guardar en themeStore
- `getAccent()` → devuelve color correcto segun tema

### 7.5 — Profile toggle
- Switch "Modo claro / Modo oscuro" en pantalla perfil
- Override personal sobre default del evento
- Persistir en MMKV

**Verificacion Fase 7**: Admin cambia tema → app refleja. Usuario overridea en perfil → persiste.

---

## FASE 8: QA visual completo (~3h)

1. Recorrer TODA la app en Noir → verificar que nada cambio vs baseline
2. Recorrer TODA la app en Lux → identificar fixes
3. Toggle dinamico (perfil) → app cambia sin reiniciar
4. Probar accents:
   - Blanco (default Noir) → verificar fallback en Lux
   - Azul corporativo (Bancolombia)
   - Rojo, verde, violeta
5. ZTE 360dp + Medium 411dp en ambos temas
6. Android: verificar BlurView fallback (no soporta blur, usa rgba)
7. Colores semanticos (rojo error, verde exito, gold puntos) legibles en fondo claro
8. Fondos animados onboarding en Lux — visibilidad particulas/constellation
9. Documentar bugs visuales encontrados

---

## Estimacion total

| Fase | Horas | Archivos | Impacto |
|------|-------|----------|---------|
| 1. Migrar a componentes base (Noir) | 6h | ~35 | Centraliza todo, cero cambio visual |
| 2. Tokens dinamicos | 2h | 5 nuevos | Base del theming |
| 3. Componentes compartidos | 3h | 14 | Impacta 85+ pantallas |
| 4. Pantallas principales | 3h | ~10 | Home, Agenda, Profile, Networking, Social |
| 5. Pantallas secundarias | 3h | ~25 | Todo lo demas |
| 6. Onboarding | 2h | ~10 | Gradientes + fondos animados |
| 7. Backend + Filament | 1.5h | 4 | Accent dual + toggle |
| 8. QA visual | 3h | 0 | Solo testing |
| **Total** | **~23.5h** | **~78 archivos** | App production-ready Noir + Lux |

---

## Excepciones — SIEMPRE dark

| Pantalla | Razon |
|----------|-------|
| MomentosViewer | Fullscreen media |
| PhotoViewer | Fullscreen media |
| QR fullscreen modal | Contraste con QR |
| Scanner cameras | Viewfinder overlay |
| Streaming video area | Experiencia cine |

---

## Colores que NO cambian por tema

**Semanticos**: `#EF4444` error, `#22c55e` exito, `#FFD700` gold, `#FBBF24` rating
**Social**: LinkedIn `#0A66C2`, Twitter `#1DA1F2`, Instagram `#E4405F`, WhatsApp `#25d366`
**Gamification**: TEAL `#39d2c0`, CYAN `#5eead4`, WAVE_COLORS, confetti pasteles
**Tiers**: platinum, gold, silver, bronze, media
**Modality**: presencial verde, virtual azul, hibrido violeta

---

## Referencias visuales aprobadas

- HTML comparativo: `design/noir-vs-lux-v2.html` (7 pantallas, accent dinamico)
- Analisis auditoria: `docs/ANALISIS-LIGHTMODE.md` (113 archivos)
- Design spec original: `design/onboarding/app/lumina_lux/DESIGN.md`
- Referencias externas: Airbnb (shadows), Linear (warm gray), Luma (event app), Apple iOS (surface hierarchy)
