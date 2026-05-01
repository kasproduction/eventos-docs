# Web App — Responsive Spec

> Regla de oro: **NO hay responsive automatico**. Cada viewport es una experiencia dedicada y coherente.
>
> Critica del usuario que origino este doc: "Event Pulse no era 100% responsive — quedaba mediocre". La webapp NO repite ese error.

---

## 3 viewports, 3 disenios

| Viewport | Rango | Disenio | Pill bar | Paneles |
|---|---|---|---|---|
| **Mobile** | `< 640px` | Stack tradicional | Bottom sheet o tab bar inferior | 1 vista a la vez (full screen) |
| **Tablet portrait** | `640px – 1024px` | Hibrido | Pill bar superior compacto | 1 panel full-width |
| **Desktop** | `>= 1024px` | Spatial completo | Pill bar superior amplio | Max 3 paneles simultaneos con jerarquia |

**Importante**: el "tablet landscape" cae en desktop (>= 1024px). iPad Pro 13" siempre es desktop.

---

## Reglas no negociables

### 1. Cada viewport es un disenio dedicado, no una version comprimida

Cada modulo se diseña en **2-3 vistas separadas** (mobile + desktop minimo, tablet si difiere de los dos). NO es Tailwind `sm:`, `md:`, `lg:` agregando clases hasta que "se ve bien".

Ejemplo: el panel de Speakers en mobile NO es el mismo grid de tarjetas con `grid-cols-1` — es una **lista vertical con cards horizontales** (foto + nombre + role inline). Disenio dedicado.

### 2. Validacion en device real, no DevTools

Cada modulo cierra su QA con prueba en:
- 1 telefono Android (idealmente Pixel 6+ o similar)
- 1 iPhone (iOS 16+)
- 1 iPad
- 1 desktop (Chrome + Edge minimo)
- Conexion 4G real (no throttle simulado de DevTools)

DevTools sirve para iteracion rapida pero NUNCA es la validacion final.

### 3. Estetica coherente entre viewports

Mobile, tablet, desktop deben sentirse de la **misma plataforma**. No es "version movil chiquita" vs "version desktop premium". La identidad Lumina Noir + tokens + tipografia + animaciones se mantienen.

Lo que cambia es la **estructura espacial**, no el lenguaje visual.

### 4. Mobile NO es spatial

En mobile no hay paneles flotantes ni jerarquia. Es navegacion stack tradicional. Bottom tab bar (similar a la app movil pero web).

### 5. Tablet portrait es transicion

Tablet portrait usa pill bar superior pero solo 1 panel a la vez. Si quieres 2 panels (ej. streaming + chat), aparece como tabs internas o el chat se vuelve overlay sheet.

### 6. Desktop es donde el spatial brilla

>= 1024px usa toda la potencia: paneles arrastrables, jerarquia visual, transiciones spring, command palette accesible (Cmd+K).

---

## Breakpoints Tailwind config

```ts
// tailwind.config.ts
theme: {
  screens: {
    'mobile': { 'max': '639px' },     // < 640
    'tablet': { 'min': '640px', 'max': '1023px' },  // 640-1023
    'desktop': { 'min': '1024px' },   // >= 1024
  }
}
```

Uso explicito en clases:
- `mobile:hidden` — oculto en mobile
- `tablet:flex desktop:grid` — flex en tablet, grid en desktop

NO usar `sm:`, `md:`, `lg:` defaults de Tailwind — confunden con el disenio dedicado.

---

## Detection en componentes

```tsx
import { useMediaQuery } from '@/hooks/useMediaQuery';

function MyModule() {
  const isMobile = useMediaQuery('(max-width: 639px)');
  const isTablet = useMediaQuery('(min-width: 640px) and (max-width: 1023px)');
  const isDesktop = useMediaQuery('(min-width: 1024px)');

  if (isMobile) return <MyModuleMobile />;
  if (isTablet) return <MyModuleTablet />;
  return <MyModuleDesktop />;
}
```

**No** hacer:
```tsx
// MAL — version unica con clases responsive
<div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
```

**Si** hacer:
```tsx
// BIEN — componentes dedicados por viewport
<div className="mobile:hidden">{/* desktop spatial layout */}</div>
<div className="tablet:hidden desktop:hidden">{/* mobile stack */}</div>
```

---

## Excepciones aceptables

Hay casos donde la version unica con `responsive utilities` esta bien:
- Tipografia: `text-base mobile:text-sm` — escalado sutil
- Padding/gap: `p-6 mobile:p-4` — ajuste de density
- Iconos: `w-6 h-6 mobile:w-5 mobile:h-5`

Lo que NO se debe responsive-utility-ize:
- Layout estructural (grid, flex direction, posicion absoluta)
- Cantidad de paneles visibles
- Posicion del pill bar
- Navegacion (stack vs spatial)

---

## Checklist por modulo (DaVinci)

Antes de cerrar un modulo, validar en los 3 viewports:

- [ ] **Mobile** (Pixel 6 / iPhone 14 / similar)
  - [ ] Layout coherente con el resto de la webapp en mobile
  - [ ] Bottom tab bar o pill bar funcional
  - [ ] Touch targets >= 44px (Apple HIG)
  - [ ] No scroll horizontal accidental
  - [ ] Loading states visibles
  - [ ] Empty states diseñados
  - [ ] Funciona en 4G real (latencia simulable con throttling pero validar al menos una vez en 4G real)
- [ ] **Tablet portrait** (iPad 10" / similar)
  - [ ] Pill bar superior funcional
  - [ ] 1 panel full-width sin perder informacion
  - [ ] Tabs internas si necesita >1 vista (ej. streaming + chat)
  - [ ] Touch targets >= 44px
- [ ] **Desktop** (1280px / 1440px / 1920px probados)
  - [ ] Pill bar superior amplio
  - [ ] Paneles spatial con jerarquia
  - [ ] Drag & drop funcional
  - [ ] Cmd+K command palette
  - [ ] Hover states correctos
  - [ ] Keyboard navigation (Tab, Esc, Enter)
- [ ] **Animaciones**
  - [ ] Transiciones spring fluidas en los 3 viewports
  - [ ] `prefers-reduced-motion` respetado (transiciones instantaneas)
- [ ] **Accesibilidad**
  - [ ] Lighthouse Accessibility >= 95 en los 3 viewports
  - [ ] Contraste 4.5:1 en texto

---

## Touch targets

Mobile + tablet: minimo **44x44 px** para cualquier elemento clickeable (Apple HIG, alineado con `.btn` size de la app movil).

Botones text-only: `padding: 12px 16px` minimo. Iconos solo: `40x40` con `:active` area extendida via `before` pseudo-element.

---

## Tipografia escalada

| Token | Mobile | Tablet | Desktop |
|---|---|---|---|
| Display 1 | 32px / line 1 | 40px / line 1 | 56px / line 1 |
| Display 2 | 24px / line 1.1 | 32px / line 1.1 | 40px / line 1.1 |
| Body Large | 16px / line 1.5 | 16px / line 1.5 | 18px / line 1.5 |
| Body | 14px / line 1.5 | 15px / line 1.5 | 16px / line 1.5 |
| Caption | 12px / line 1.4 | 12px / line 1.4 | 13px / line 1.4 |

Fuentes: **Plus Jakarta Sans** display + **Urbanist** body. Detalle en `DESIGN-SYSTEM.md`.

---

## Imagenes

`next/image` con `sizes` correcto:

```tsx
<Image
  src="..."
  alt="..."
  sizes="(max-width: 639px) 100vw, (max-width: 1023px) 50vw, 33vw"
  width={...}
  height={...}
/>
```

Avatares: 40px mobile, 48px tablet, 56px desktop. Cards: aspect-ratio 4/5 en mobile (vertical), 16/9 en tablet/desktop.

---

## Pill bar comportamiento

| Viewport | Posicion | Tamano | Items |
|---|---|---|---|
| Mobile | **Bottom** (tab bar) | 60px alto | Iconos solos, sin texto |
| Tablet portrait | **Top** (pill flotante) | 48px alto | Iconos + texto compactos |
| Desktop | **Top** (pill flotante amplio) | 56px alto | Iconos + texto completos + Cmd+K hint |

En mobile, el bottom tab bar reemplaza el pill bar superior. NO se muestran ambos.

---

## Modales y overlays

| Viewport | Comportamiento |
|---|---|
| Mobile | Bottom sheet (slide up desde abajo) ocupando 80vh max, con drag-to-dismiss |
| Tablet portrait | Centered modal con backdrop, max-width 600px |
| Desktop | Centered modal con backdrop, max-width 800px |

Library: shadcn/ui `<Dialog>` con override por viewport o componente `<Sheet>` para mobile.

---

## Loading states

| Viewport | Skeleton |
|---|---|
| Mobile | Skeleton lineal de la lista |
| Tablet | Skeleton del panel activo |
| Desktop | Skeleton de los 3 paneles si todos cargan a la vez |

Skeleton tokens: `noirSkeleton` portado de la app movil (ver `DESIGN-SYSTEM.md`).

---

## Empty states

Cada modulo diseña empty states **dedicados por viewport** cuando difieren:
- Mobile: ilustracion mas pequena + CTA
- Desktop: ilustracion mediana + texto explicativo + CTA

Lenguaje y tono coherentes (Lumina Noir, no friendly emojis).

---

## Performance por viewport

| Viewport | Bundle inicial | TTI target |
|---|---|---|
| Mobile | < 150 KB gz | < 4s en 4G |
| Tablet | < 200 KB gz | < 3s en WiFi |
| Desktop | < 200 KB gz | < 2s en WiFi |

Mobile recibe bundle mas chico via dynamic imports condicionales (`if (isMobile) await import('./MobileLayout')`).

---

## Anti-patrones — NO hacer

1. **Hacer responsive con clases Tailwind sobre el mismo JSX** cuando estructura cambia
2. **Hacer mobile a partir del desktop reduciendo** (siempre disenia mobile primero o paralelo)
3. **Probar solo en DevTools** sin device real
4. **Esconder features en mobile** sin replantear como acceder a ellas
5. **Hover states que no tienen alternativa touch** (todos los hover deben tener equivalente tap)
6. **Modales fullscreen en desktop** (deben ser centered con backdrop)
7. **Bottom sheets en desktop** (no encajan)
8. **Pill bar superior en mobile** (compite con notch + status bar)

---

## Checklist final antes de cerrar W.12

- [ ] Cada modulo W.0-W.10 validado en 3 viewports
- [ ] Lighthouse mobile + desktop >= targets
- [ ] Pruebas en device real (Pixel + iPhone + iPad)
- [ ] Pruebas en navegadores (Chrome + Edge + Safari + Firefox)
- [ ] Pruebas en proxy corporativo (simulacion Bancolombia con headers + firewall)
- [ ] `prefers-reduced-motion` validado
- [ ] Print stylesheet basico (al menos no se rompe)
