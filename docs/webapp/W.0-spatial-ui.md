# W.0 — Spatial UI System

> **Cimiento de toda la webapp.** PanelManager, PillBar, presets, command palette, drag de paneles. Sin esto los modulos no tienen donde vivir.
>
> **Estimacion:** ~12h (2 dias). Bloqueante de W.2-W.10.
> **Dependencias:** W.1 ya cerrado (auth + layout shell).
> **Estado:** Pendiente.

---

## Lectura obligatoria

- `PLAN.md` (vision spatial)
- `DECISIONS.md` (ADR-009 responsive, ADR-010 stack, ADR-011 tokens)
- `RESPONSIVE-SPEC.md` (3 disenios dedicados)
- `DESIGN-SYSTEM.md` (tokens, animaciones spring)
- `design/LANDING/` — refs visuales spatial UI (futbol AR pill bar, meeting panels, blog reader)

---

## Refs visuales

- `design/LANDING/original-866f...webp` — futbol AR: pill bar superior + paneles flotantes con stats overlay
- `design/LANDING/original-5e82...webp` — meeting: panel principal video + panel chat lateral
- `design/LANDING/original-8317...webp` — blog reader: panel centro + nav izq + chat der
- `design/LANDING/original-04f6...webp` — shopping: cards glass flotando

NO copiar visualmente, leer como inspiracion para layout y jerarquia. Estilo final = Lumina Noir aprobado.

---

## Alcance — que se construye

1. Sistema de paneles con jerarquia (max 3 simultaneos en desktop)
2. PillBar superior (desktop/tablet) + bottom tab bar (mobile)
3. Presets de layout ("Conferencia", "Networking", "Explorar")
4. Command palette (Cmd+K / Ctrl+K)
5. Drag & drop de paneles (`@dnd-kit/core`)
6. Persistencia de layout preferido (localStorage)
7. Responsive: 3 disenios dedicados (mobile stack, tablet 1 panel, desktop spatial)

---

## NO entra (queda para Fase 2)

- Editor visual de paneles (drag-to-create custom layout)
- Multi-evento simultaneo
- Panel resize manual (paneles tienen tamanos fijos por jerarquia primaria/secundaria)

---

## Fase 0 — Setup base (~1.5h) — 0/4

### 0.1 Estructura — 0/2
- [ ] Crear estructura `src/components/spatial/` con archivos vacios:
  - `PanelManager.tsx`
  - `Panel.tsx`
  - `PillBar.tsx`
  - `MobileTabBar.tsx`
  - `CommandPalette.tsx`
  - `LayoutPresets.tsx`
- [ ] Crear `src/hooks/` con archivos vacios:
  - `usePanelLayout.ts`
  - `useLayoutPreset.ts`
  - `useMediaQuery.ts`
  - `useCommandPalette.ts`

### 0.2 State management — 0/2
- [ ] Definir Zustand store `src/stores/panelStore.ts`:
  - `panels: Panel[]` (max 3 desktop, 1 tablet, 1 mobile)
  - `primaryPanelId: string | null`
  - `addPanel(panel)`, `removePanel(id)`, `setPrimary(id)`, `swapPanels(id1, id2)`
- [ ] Definir tipos en `src/types/spatial.ts`:
  - `Panel` (id, type, props, jerarquia, position)
  - `LayoutPreset`, `PillItem`

---

## Fase 1 — PillBar desktop/tablet (~2h) — 0/6

### 1.1 Componente base — 0/3
- [ ] `PillBar.tsx` con 7 items (Home, Speakers, Agenda, Streaming, Connect, Gamification, Sponsors)
- [ ] Pill activa = surface high + accent border + accent text
- [ ] Hover state con surface medium + spring scale 1.02

### 1.2 Posicion + estilo — 0/2
- [ ] Posicion: `top-6` flotante centrado, `backdrop-blur-2xl`, `bg-noir-sheet`, `border border-white/[0.10]`, `rounded-full`
- [ ] Tablet portrait: pill bar mas compacto (icono + texto pequeno)

### 1.3 Iconos — 0/1
- [ ] lucide-react picks: `Home`, `Mic2`, `Calendar`, `PlayCircle`, `Users`, `Trophy`, `Briefcase`

---

## Fase 2 — Mobile bottom tab bar (~1.5h) — 0/4

### 2.1 Componente — 0/2
- [ ] `MobileTabBar.tsx` con 5 items principales (Home, Agenda, Connect, Gamification, Profile)
- [ ] Posicion fixed bottom, height 60px, safe area inset bottom

### 2.2 Detection — 0/2
- [ ] `useMediaQuery('(max-width: 639px)')` para mostrar tab bar y ocultar pill bar
- [ ] Si en mobile, ocultar todos los paneles spatial → renderizar `<PageStack />` con full screen

---

## Fase 3 — PanelManager (~3h) — 0/8

### 3.1 Layout primary + secondary — 0/3
- [ ] `PanelManager.tsx` recibe `panels: Panel[]` del store
- [ ] Desktop layout grid: 1 primary (60%) + 2 secondary (40%/2 = 20% c/u)
- [ ] Si solo 1 panel: 100% width. Si 2: 60/40. Si 3: 60/20/20

### 3.2 Transicion al abrir/cerrar — 0/3
- [ ] Framer Motion `<AnimatePresence>` con `layout` prop
- [ ] `springStiff` para enter, `springGentle` para exit
- [ ] Si abrir cuarto panel → primero hace exit del de menor prioridad, despues enter del nuevo

### 3.3 Empty state — 0/2
- [ ] Si no hay paneles abiertos: mostrar Home (Welcome card + atajos a features)
- [ ] Si todos estan cerrados manualmente: empty state con CTA "Abre un panel del menu"

---

## Fase 4 — Drag & drop de paneles (~2h) — 0/5

### 4.1 Setup `@dnd-kit/core` — 0/2
- [ ] `<DndContext>` envuelve `PanelManager`
- [ ] `useDraggable` + `useDroppable` en cada `Panel`

### 4.2 Logica swap — 0/2
- [ ] Drag panel A sobre panel B → swap positions (A toma la posicion de B y viceversa)
- [ ] Visual feedback: ghost del panel + drop zone highlight

### 4.3 Persistencia — 0/1
- [ ] Al swap, guardar layout en localStorage (`panel_layout_user_{user_id}`)

---

## Fase 5 — Command palette (~1.5h) — 0/4

### 5.1 Component — 0/2
- [ ] `CommandPalette.tsx` con shadcn/ui `<Command>` + `<Dialog>`
- [ ] Trigger: `Cmd+K` (mac) / `Ctrl+K` (win/linux) globalmente

### 5.2 Items — 0/2
- [ ] Items por defecto: Navegar a Home, Speakers, Agenda, Streaming, Connect, Gamification, Sponsors, Notificaciones, Perfil, Logout
- [ ] Search fuzzy (cmdk default behavior)

---

## Fase 6 — Presets de layout (~1h) — 0/3

### 6.1 Definir presets — 0/2
- [ ] `LayoutPresets.tsx` con 3 botones:
  - "Conferencia" → abre Streaming primary + Chat secondary
  - "Networking" → abre Connect primary + Social Wall secondary + Profile secondary
  - "Explorar" → abre Agenda primary + Speakers secondary + Sponsors secondary
- [ ] `useLayoutPreset.ts` aplica preset al store

### 6.2 UI — 0/1
- [ ] Botones en pill bar dropdown "Layouts" o como item separado

---

## Fase 7 — Persistencia + memory (~1h) — 0/3

### 7.1 localStorage — 0/2
- [ ] Al cambiar layout (manual o preset), guardar `panel_layout_user_{user_id}` con paneles + posiciones
- [ ] Al hacer login, restaurar layout previo o empezar con Home

### 7.2 Reset — 0/1
- [ ] Boton "Restablecer layout" en command palette → borra localStorage + reset a Home

---

## Fase 8 — Responsive + reduced motion (~1h) — 0/4

### 8.1 Mobile stack — 0/2
- [ ] En mobile, `PanelManager` se vuelve `PageStack` — 1 ruta = 1 pagina full screen, navegacion con bottom tab bar
- [ ] Transiciones entre pages: slide horizontal (mobile feel)

### 8.2 Reduced motion — 0/2
- [ ] `useReducedMotion()` hook de Framer Motion
- [ ] Si true → todas las transiciones spring se vuelven instant (`duration: 0`)

---

## Fase 9 — QA + tests (~1h) — 0/4

### 9.1 Unit tests (Vitest) — 0/2
- [ ] `panelStore` — addPanel, removePanel, swapPanels, setPrimary
- [ ] `useLayoutPreset` — aplicar preset correcto

### 9.2 E2E tests (Playwright) — 0/2
- [ ] Happy path desktop: abrir 3 paneles + swap + cerrar uno + abrir cuarto → cuarto reemplaza al de menor prioridad
- [ ] Happy path mobile: navegacion stack via bottom tab bar

---

## Edge cases

- [ ] Refresh con localStorage corrupto → fallback a Home + log warning
- [ ] User cambia de mobile → desktop (resize ventana) → recalcular layout, NO romper estado
- [ ] User cambia de desktop → mobile → cerrar paneles secundarios automatico, mantener primary
- [ ] Drag con touch en tablet → debe funcionar (no solo mouse)
- [ ] Cmd+K en mobile → no aplicable, command palette solo desktop/tablet
- [ ] Panel intenta abrir cuando ya esta abierto → focus al panel existente, no duplicar

---

## Tests pre-existentes a no romper

W.0 es la base — no rompe nada porque no hay nada antes. Pero deja los siguientes invariantes para los siguientes modulos:

- `panelStore` API estable
- `<Panel>` component recibe props standardizadas: `{ id, type, primary, children }`
- Tokens Lumina Noir aplicados consistentemente

---

## Archivos creados

```
src/
  components/spatial/
    PanelManager.tsx
    Panel.tsx
    PillBar.tsx
    MobileTabBar.tsx
    CommandPalette.tsx
    LayoutPresets.tsx
    PageStack.tsx
  hooks/
    usePanelLayout.ts
    useLayoutPreset.ts
    useMediaQuery.ts
    useCommandPalette.ts
  stores/
    panelStore.ts
  types/
    spatial.ts
  presets/
    spring.ts
```

---

## Cierre de modulo

- [ ] Tests Vitest verde
- [ ] Tests Playwright verde
- [ ] Validado en device real: Pixel 6 + iPad + desktop Chrome/Edge
- [ ] Lighthouse Performance >= 85 desktop, >= 75 mobile
- [ ] Lighthouse Accessibility >= 95
- [ ] Commit DaVinci con mensaje descriptivo
- [ ] Memoria sesion guardada
- [ ] PENDIENTES.md actualizado (W.0 cerrado, contar 0/N → N/N)
