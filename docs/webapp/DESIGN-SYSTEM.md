# Web App — Design System

> Tokens, fonts, glass rules, componentes shadcn, animaciones spring. Portado de la app movil (`eventos-app/lib/theme-noir.ts` + `theme-lux.ts`).
>
> **Regla de oro**: NO inventar paleta nueva. NO copiar del demo `showcase-onboarding-v6.html`. Tokens vienen de la app movil aprobada.

---

## Filosofia

- **Lumina Noir** (default dark): el teatro — dramatico, cinematico, luces en oscuridad. Profundidad via bordes luminosos + glass, no shadows.
- **Lumina Lux v2** "The Gallery" (light): galeria — limpio, espacios negativos, shadows reemplazan bordes. Contrast alto WCAG AA.

Ambos coexisten. El usuario alterna via switch. Default por evento configurable (`branding.default_theme`).

---

## Color tokens — Lumina Noir

### Surfaces

```ts
const noirSurface = {
  // Subtle backgrounds (overlay)
  low: 'rgba(255,255,255,0.03)',
  medium: 'rgba(255,255,255,0.04)',
  high: 'rgba(255,255,255,0.08)',

  // Borders — 3 levels
  border: 'rgba(255,255,255,0.06)',
  borderLight: 'rgba(255,255,255,0.12)',
  borderStrong: 'rgba(255,255,255,0.18)',

  // Backgrounds
  background: '#0a0a0a',
  backgroundElevated: '#141414',
  backgroundSunken: '#050505',
  backgroundRaised: '#111111',

  // Surfaces
  card: 'rgba(255,255,255,0.03)',
  input: 'rgba(255,255,255,0.04)',
}
```

### Text

```ts
const noirText = {
  primary: 'rgba(255,255,255,0.85)',
  secondary: 'rgba(255,255,255,0.5)',
  muted: 'rgba(255,255,255,0.35)',
  label: 'rgba(255,255,255,0.25)',
  placeholder: 'rgba(255,255,255,0.15)',
  white: '#FFFFFF',
}
```

### Icons

```ts
const noirIcon = {
  primary: 'rgba(255,255,255,0.85)',
  secondary: 'rgba(255,255,255,0.5)',
  tertiary: 'rgba(255,255,255,0.25)',
}
```

### Glass

```ts
const noirGlass = {
  sheet: 'rgba(20,20,22,0.55)',
  sheetBlur: 20,
  scrim: 'rgba(0,0,0,0.6)',
  scrimBlur: 8,
  tabBar: 'rgba(20,20,22,0.55)',
}
```

### Skeleton

```ts
const noirSkeleton = {
  from: 'rgba(255,255,255,0.03)',
  via: 'rgba(255,255,255,0.06)',
  to: 'rgba(255,255,255,0.03)',
}
```

### Estados interactivos

```ts
const noirStates = {
  hover: 'rgba(255,255,255,0.03)',
  pressed: 'rgba(255,255,255,0.06)',
  disabledBg: 'rgba(255,255,255,0.03)',
  disabledText: 'rgba(255,255,255,0.15)',
}
```

### Categorias (pills sesion)

```ts
const noirCategories = {
  innovacion: { bg: 'rgba(255,255,255,0.06)', text: 'rgba(255,255,255,0.5)' },
  networking: { bg: 'rgba(255,255,255,0.06)', text: 'rgba(255,255,255,0.5)' },
  tecnologia: { bg: 'rgba(255,255,255,0.06)', text: 'rgba(255,255,255,0.5)' },
  liderazgo:  { bg: 'rgba(255,255,255,0.06)', text: 'rgba(255,255,255,0.5)' },
  datos:      { bg: 'rgba(255,255,255,0.06)', text: 'rgba(255,255,255,0.5)' },
}
```

### Semantic — Platinum gold

```ts
const noirSemantic = {
  gold: '#B5A68B',
  goldSoft: 'rgba(181,166,139,0.4)',
}
```

---

## Color tokens — Lumina Lux v2 "The Gallery"

### Surfaces

```ts
const luxSurface = {
  low: 'rgba(0,0,0,0.02)',
  medium: 'rgba(0,0,0,0.04)',
  high: 'rgba(0,0,0,0.06)',

  border: 'rgba(0,0,0,0.08)',
  borderLight: 'rgba(0,0,0,0.04)',
  borderStrong: 'rgba(0,0,0,0.14)',

  background: '#F6F8FA',
  backgroundElevated: '#FFFFFF',
  backgroundSunken: '#EDF0F3',
  backgroundRaised: '#FAFBFC',

  card: '#FFFFFF',
  input: '#F0F2F5',
}
```

### Text (solid hex, NUNCA rgba — pasa WCAG AA)

```ts
const luxText = {
  primary: '#1A1B1E',
  secondary: '#4A4B50',
  muted: '#7C7D82',
  label: '#A8A9AE',
  placeholder: '#A8A9AE',
}
```

### Shadows (reemplazan bordes en Lux)

```ts
const luxShadow = {
  card:     'box-shadow: 0 1px 3px rgba(0,0,0,0.06)',
  elevated: 'box-shadow: 0 4px 12px rgba(0,0,0,0.08)',
  lg:       'box-shadow: 0 8px 24px rgba(0,0,0,0.10)',
  xl:       'box-shadow: 0 16px 48px rgba(0,0,0,0.12)',
}
```

### Categorias

```ts
const luxCategories = {
  innovacion: { bg: '#FFF7ED', text: '#C2410C' },
  networking: { bg: '#FDF2F8', text: '#BE185D' },
  tecnologia: { bg: '#EFF6FF', text: '#1D4ED8' },
  liderazgo:  { bg: '#F0FDF4', text: '#166534' },
  datos:      { bg: '#FDF4FF', text: '#7E22CE' },
}
```

### Semantic — Platinum gold (Lux)

```ts
const luxSemantic = {
  gold: '#8E8170',
  goldSoft: 'rgba(142,129,112,0.5)',
}
```

### Dark Island override

`HappeningNow`, `GamificationHud` mantienen dark island en Lux para contrast dramatico:

```ts
const luxOverrides = {
  darkIsland: {
    background: '#1A1B1E',
    text: '#FFFFFF',
    textSecondary: 'rgba(255,255,255,0.6)',
    textMuted: 'rgba(255,255,255,0.5)',
  },
}
```

---

## Accent dinamico (primary color del evento)

El accent NO es estatico — viene del backend (`branding.primary_color`) y se aplica como CSS custom property.

```ts
// hooks/useTheme.ts
const accent = branding?.primary_color ?? defaultAccentForMode(mode);
const accentText = contrastTextColor(accent);  // white o black segun luminance
const accentSoft = `${accent}1A`;   // 10% opacity
const accentMuted = `${accent}33`;  // 20% opacity
```

```css
/* Aplicado en :root */
:root {
  --accent: var(--branding-primary, #FFFFFF);
  --accent-text: var(--branding-primary-text, #000000);
  --accent-soft: color-mix(in srgb, var(--accent) 10%, transparent);
  --accent-muted: color-mix(in srgb, var(--accent) 20%, transparent);
}
```

Default por modo:
- Noir: `#FFFFFF` (white)
- Lux: `#1A1A1A` (ink black)

---

## Tipografia

**Solo 2 fuentes**: `Plus Jakarta Sans` (display) + `Urbanist` (body). NO JetBrains Mono. Lo "mono" se simula con Urbanist 600 + tracking 0.18em + uppercase.

### Plus Jakarta Sans — Display (titulos, headlines, hero)

- 600 (semibold)
- 700 (bold)
- 800 (extrabold)
- 900 (black)

### Urbanist — Body (texto general, UI labels, mono-style)

- 300 (light)
- 400 (regular)
- 500 (medium)
- 600 (semibold)
- 700 (bold)
- 800 (extrabold)

### Configuracion Next.js

```tsx
// app/layout.tsx
import { Urbanist, Plus_Jakarta_Sans } from 'next/font/google';

const urbanist = Urbanist({
  subsets: ['latin'],
  variable: '--font-urbanist',
  display: 'swap',
});

const jakarta = Plus_Jakarta_Sans({
  subsets: ['latin'],
  weight: ['600', '700', '800', '900'],
  variable: '--font-jakarta',
  display: 'swap',
});
```

### Tipos preset por viewport

| Token | Mobile | Tablet | Desktop | Font | Weight |
|---|---|---|---|---|---|
| Display 1 | 32px | 40px | 56px | Jakarta | 800 |
| Display 2 | 24px | 32px | 40px | Jakarta | 800 |
| Display 3 | 20px | 24px | 28px | Jakarta | 700 |
| Body Large | 16px | 16px | 18px | Urbanist | 500 |
| Body | 14px | 15px | 16px | Urbanist | 400 |
| Caption | 12px | 12px | 13px | Urbanist | 500 |
| Mono | 12px | 12px | 13px | Urbanist 600 + tracking 0.18em + uppercase | 600 |

---

## Glass rules

Glass es **protagonista en Noir** (sheets, tab bar) pero se usa con restraint. **Lux usa glass minimo** (solo bottom sheets, casi opaco).

### Cuando usar glass

- Sheets / modales (Noir: blur 20, Lux: casi opaco 95%)
- Pill bar / tab bar (Noir: blur 20, Lux: solid white)
- Tooltips ricos (Noir: blur 12)
- Floating panels (Noir: blur 16-20)

### Cuando NO usar glass

- Cards de contenido normal (usar surface solid o subtle bg)
- Botones primarios (solid)
- Inputs (solid bg)
- Listas / feeds (solid bg con borders)

### Maximo 3-4 elementos glass por pantalla simultaneos

Si hay mas, el efecto se diluye y la composicion se vuelve "Vista Aero". Glass solo en piezas premium.

### Implementation Tailwind/CSS

```tsx
// Glass sheet noir
<div className="bg-noir-sheet backdrop-blur-2xl border border-white/10 rounded-2xl">
  ...
</div>

// Glass tab bar noir
<nav className="fixed top-6 left-1/2 -translate-x-1/2 bg-noir-sheet backdrop-blur-2xl border border-white/10 rounded-full px-3 py-2">
  ...
</nav>
```

---

## Animaciones

### Spring (Framer Motion)

```ts
// presets/spring.ts
export const springGentle = {
  type: 'spring',
  stiffness: 200,
  damping: 25,
  mass: 1,
} as const;

export const springStiff = {
  type: 'spring',
  stiffness: 300,
  damping: 30,
  mass: 0.8,
} as const;

export const springBouncy = {
  type: 'spring',
  stiffness: 400,
  damping: 20,
  mass: 1,
} as const;
```

### Easing presets (cuando spring no aplica)

```ts
export const easeOut = [0.16, 1, 0.3, 1];     // typical UI
export const easeInOut = [0.4, 0, 0.2, 1];    // material
```

### Reglas de uso

- **Layout transitions**: Framer Motion `layout` prop con `springGentle`
- **Panel open/close**: `springStiff`
- **Fade in/out**: `easeOut` 200ms
- **Modal/sheet enter**: `springStiff`
- **Toast slide in**: `springBouncy` 300ms
- **Hover scale**: `transition: transform 200ms ease-out`, `scale: 1.02`
- **Press**: `scale: 0.97` en mobile (alineado con `noirOverrides.pressedScale`)

### `prefers-reduced-motion`

Cuando el usuario lo solicita, todas las transiciones se vuelven instantaneas (`duration: 0`). Framer Motion `useReducedMotion()` hook.

---

## Componentes shadcn/ui

shadcn/ui da componentes base que se sobrescriben con tokens Lumina Noir. Componentes que vamos a usar (cherry-pick, no install all):

- `Button` — variantes: primary, secondary, ghost, destructive
- `Input` — text, email, password
- `Dialog` — modales
- `Sheet` — bottom sheets mobile
- `DropdownMenu` — menus contextuales
- `Tooltip` — hover info
- `Toast` (via Sonner) — notificaciones
- `Tabs` — navegacion interna paneles
- `Avatar` — fotos usuario
- `Badge` — pills/tags
- `Separator` — dividers
- `Command` — para command palette
- `Popover` — flotantes ligeros
- `Skeleton` — loading states

### Override de tokens

shadcn/ui usa CSS variables. Sobrescribimos en `app/globals.css`:

```css
:root {
  --background: 10 10 10;          /* #0a0a0a Noir */
  --foreground: 255 255 255;
  --card: 20 20 20;
  --primary: var(--accent);
  --primary-foreground: var(--accent-text);
  --border: 255 255 255 / 0.06;
  --radius: 1rem;                   /* 16px default */
}

[data-theme='lux'] {
  --background: 246 248 250;
  --foreground: 26 27 30;
  ...
}
```

### Border radius

| Token | Valor |
|---|---|
| sm | 8px |
| md | 12px |
| lg | 16px (default) |
| xl | 20px |
| 2xl | 24px |
| full | 9999px |

---

## Iconografia

**Lucide React** (`lucide-react`) como libreria principal. Stroke 1.5, size 20-24 por default.

Custom icons (logo del evento, sponsor logos): SVG inline o `next/image` segun caso.

NO emojis en UI. NO icon fonts (FontAwesome, Material Icons).

---

## Botones — variantes

```tsx
<Button variant="primary">   // bg accent, text accent-text
<Button variant="secondary"> // bg surface high, border, text primary
<Button variant="ghost">     // sin bg, text primary, hover surface
<Button variant="destructive"> // text red, hover bg red soft
<Button variant="link">      // sin bg, text accent, underline hover
```

Sizes:
- `sm`: 32px alto, padding 8/12, text-sm
- `md` (default): 40px alto, padding 10/16, text-base
- `lg`: 48px alto, padding 12/20, text-base + bold

---

## Cards

### Card simple (Noir)

```tsx
<div className="bg-noir-card border border-white/[0.06] rounded-2xl p-6">
  ...
</div>
```

### Card elevated (Lux)

```tsx
<div className="bg-white rounded-2xl shadow-lux-card p-6">
  ...
</div>
```

### Card glass (Noir, premium)

```tsx
<div className="bg-noir-sheet backdrop-blur-2xl border border-white/[0.12] rounded-2xl p-6">
  ...
</div>
```

---

## Spacing

Tailwind default scale (4px base). Tokens semanticos:

| Uso | Token |
|---|---|
| Padding card pequena | p-4 (16px) |
| Padding card normal | p-6 (24px) |
| Padding card grande | p-8 (32px) |
| Gap entre items | gap-3 (12px) |
| Gap entre secciones | gap-6 (24px) |
| Gap entre paneles | gap-4 (16px) |

---

## Z-index scale

```css
--z-base: 0;
--z-dropdown: 10;
--z-sticky: 20;
--z-fixed: 30;
--z-modal-backdrop: 40;
--z-modal: 50;
--z-popover: 60;
--z-toast: 70;
--z-tooltip: 80;
--z-cmdk: 90;
```

---

## Reducir overshoot del demo HTML

El demo `showcase-onboarding-v6.html` usa colores **#6C63FF violeta + #00D4AA teal + #FF6B6B rojo + #FFB800 oro**. **Esos NO son nuestros tokens**.

Lumina Noir aprobado:
- Sin "rojo" — los likes/alerts usan `red-400/500` Tailwind con tint Noir
- Sin "oro flashy" — usamos `noirSemantic.gold` = `#B5A68B` (platinum gold)
- Sin "teal vivo" — el secondary accent viene de `branding.secondary_color` o por default es muted
- Sin violeta — el accent es dinamico desde branding del evento, default white en Noir

---

## Decisiones pendientes

- **Iconografia detallada para pill bar** — lucide-react picks definitivos en W.0
- **Logo eventos.app** para auth screens — diseno separado, queda fuera de este doc
- **Print stylesheet** — minimo W.12 (al menos no rompe)
- **Theme custom por evento mas alla de primary color** — Fase 2 no Fase 1

---

## Lectura obligatoria antes de codear cualquier modulo

1. `PLAN.md` — vision + scope
2. `DECISIONS.md` — ADRs
3. `RESPONSIVE-SPEC.md` — 3 disenios dedicados
4. Este doc — tokens + componentes
5. `eventos-app/lib/theme-noir.ts` y `theme-lux.ts` — fuente de verdad
6. El roadmap `W.X-*.md` del modulo a implementar
