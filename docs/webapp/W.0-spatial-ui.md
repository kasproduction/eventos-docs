# W.0 — Spatial Shell (DaVinci redesign 2026-05-02 → 2026-05-03)

> **Cimiento estructural de toda la webapp.** Define como se ven, navegan y conviven los modulos. Filosofia visionOS-inspired adaptada a 2D web, NO drag manual ni paneles arrastrables.
>
> **Estimacion:** ~10h (revisada — antes 12h). Bloqueante de W.2-W.17.
> **Dependencias:** W.1 cerrado.
> **Estado:** **IMPLEMENTADO** en `eventos-web` (Next 16 + React 19 + Tailwind 4) el 2026-05-04.
>
> **Actualizacion 2026-05-04:** Shell W.0 IMPLEMENTADO en codigo:
> - `src/components/shell/SpatialShell.tsx` (wrapper raiz)
> - `src/components/shell/SidebarPill.tsx` (con bell + perfil adentro)
> - `src/components/shell/CanvasCard.tsx` (16/9 con clamp — base universal)
> - `src/components/shell/AmbientBackground.tsx`, `Stage.tsx`, `EventThemeProvider.tsx`, `ThemeTogglePill.tsx`, `ProfilePopover.tsx`
> - Bell INTEGRADO en sidebar (decision final), no flotante separado.
> - Sistema de accent dinamico per-tema (Noir + Lux) con primary_color + primary_color_light desde Filament.

---

## Filosofia (consenso post-auditoria 2026-05-02)

EventOS shell se siente como **un solo lugar continuo** donde abres/cierras modulos sobre un escenario persistente. Modelo jerarquico simple:

- **Nivel 1 (sidebar pill flotante izq)**: navegacion entre modulos top-level. Click cambia el escenario completo.
- **Nivel 2 (paneles aux dentro del modulo)**: detalle/sub-paneles que conviven con el contenido principal del modulo activo.
- **Cambiar de modulo top-level cierra TODO** (principal + aux). El nuevo emerge.
- **Overlays no-destructivos** (Mi perfil, Notificaciones, Search): abren sobre el modulo actual sin destruirlo. Cierras y vuelves donde estabas.

**NO replicamos visionOS literal.** Adoptamos su feeling spatial (paneles flotantes, jerarquia clara, transiciones suaves) sin caer en lo VR-specific (glass forzado en todo, drag manual, multi-window infinito).

---

## Refs visuales analizados (12 referentes en `design/features/webapp/LANDING/`)

| Patron | Frecuencia | Adoptamos? |
|---|---|---|
| Sidebar pill izquierda con iconos verticales | 8/12 | Si — patron dominante |
| Tab bar bottom contextual (filtros intra-modulo) | 5/12 | Si — para filtros dentro de modulo |
| Multi-panel cuando tarea lo justifica | 4/12 | Si — pero solo cuando aplica (sesion live, networking) |
| Top pill bar secundario | 3/12 | Quizas — para sub-secciones de modulos grandes |
| Glass/blur background | 12/12 | NO — refs son visionOS sobre fondo real (sala). En nuestro `bg-sunken` negro NO contrasta. Lumina noir solid principalmente |
| Browser bar visionOS | 3/12 | NO — irrelevante para web |
| Drag manual paneles | (visionOS native) | NO — fricción mouse sin valor |

Refs cita ver: `design/features/webapp/LANDING/` (DuaLuxe, Simons, Smart Home, Uber Eats visionOS, Cinema Pro, Riot LoL, Reading App, Sports AR, Football streaming, Meeting, Shopping spatial, Blog reader).

---

## Decisiones DaVinci (lo que ELIMINAMOS del plan original)

ELIMINADO:
- ❌ Drag con `@dnd-kit/core` (fricción sin valor)
- ❌ "Max 3 paneles simultaneos" como regla rigida (variable segun modulo)
- ❌ Paneles arrastrables que el usuario reacomoda
- ❌ Memoria de layout localStorage (sin drag no hay layout que recordar)
- ❌ "Spatial real visionOS" como filosofia (es 2D web, no VR)
- ❌ PillBar superior (decidimos IZQUIERDA flotante segun refs dominantes)
- ❌ Glass/blur en todo (Lumina noir solid + glass solo donde brilla 3-4 piezas premium)
- ❌ ModuleMenu en Home (sidebar/tabbar ya cubre nav)
- ❌ Header global con breadcrumb (innecesario, jerarquia visible por sidebar activo + aux)

MANTENEMOS:
- ✅ Sidebar pill flotante (movido a izquierda)
- ✅ Command palette `Cmd+K` (atajo opcional + boton visible "Buscar")
- ✅ Happening Now persistente (badge en sidebar, contenido en home Live)
- ✅ Spring transitions al cambiar modulo
- ✅ Multi-panel donde tarea lo justifica (sesion live: player + chat + trivia)

---

## Arquitectura del shell

### Desktop / Tablet H

```
[BG sunken full screen + atmosfera radial sutil]

  [Sidebar pill]    [Card central flotante]
  ┌─────┐           ┌────────────────────────────────────┐
  │ E   │           │                            [🔔]    │ ← bell flotante top-right
  │ ⌂   │           │                                    │
  │ 📅  │           │   Workspace                        │
  │ ●live│          │   (modulo activo)                  │
  │ 👥  │           │                                    │
  │ 💬  │           │                                    │
  │ 🏢  │           ├──────────────────────┬─────────────┤
  │ 📰  │           │   Modulo principal   │  Aux panel  │ ← cuando aplica
  │     │           │                      │  (detalle,  │
  │ ⋯   │           │                      │   chat 1:1) │
  │ 👤  │           └──────────────────────┴─────────────┘
  └─────┘
  flotante                card flotante max-width 1200px
```

- Sidebar: `position: fixed`, ~52px ancho, border + shadow + radius 28px (pill alargada vertical), iconos verticales, accent rail al lado del activo
- **Card central (canvas raiz):** ver seccion "Patron del card raiz universal" abajo. Formulas exactas con `clamp` + aspect 16/9 + centrado vertical real.
- Aux panel: dentro de la card, slide-in desde derecha (~360px), border-left
- **Bell-pill** top-right: rounded-rect (border-radius 16, NO circulo) con padding 6 e icono 38x38 adentro. Vive como "app solita", surface glass igual al sidebar. Siempre visible aunque cambies modulo

### Mobile (replica patron app movil — `eventos-app/app/(app)/(tabs)/index.tsx`)

```
[Full viewport sin card, sin sidebar, sin atmosfera]

  ┌────────────────────────────┐
  │  Hero / Contenido modulo   │
  │  (full width)              │
  │                            │
  │                            │
  ├────────────────────────────┤
  │  [Inicio][Agenda][●][Conec│][Más]
  └────────────────────────────┘
   tab bar bottom 5 items, replica app movil
```

- Sin card flotante (full screen)
- Sin sidebar (tab bar bottom reemplaza)
- Tab bar 5 items: Inicio / Agenda / En vivo (con dot pulsante) / Conectar / Más
- Aux panel = full-screen overlay con back button
- Notif/Mi perfil = bottom sheets full-width

**Razon mobile = app movil:** ahorra trabajo enorme de UX. La distribucion movil ya esta validada en la app. Solo trasladar.

---

## Patron del card raiz universal (validado 2026-05-03)

**Por que importa:** cuando el usuario navega Home → Agenda → Networking → Sponsors, **el canvas no debe saltar**. Misma forma, mismo tamano, misma posicion. Solo cambia lo que hay adentro. Esto es lo que hace que el shell se sienta spatial/visionOS y no una coleccion de paginas inconexas.

### Reglas del card raiz (DEFINITIVAS — 2026-05-04)

```css
.card {
  width: min(
    1600px,                          /* cap absoluto: legibilidad >1600 sufre */
    calc(100vw - 150px),             /* margen lateral (sidebar 88 + 36 right + holgura) */
    calc((100vh - 80px) * 16 / 9)   /* altura disponible (stage padding 28+28 + chrome) */
  );
  aspect-ratio: 16 / 9;
  border-radius: 26px;
  overflow: hidden;
  background: var(--bg-elevated);    /* Noir #141414 / Lux #ffffff — opaco */
  border: 1px solid var(--border);
  box-shadow: var(--canvas-card-shadow); /* adapta theme */
}

.stage {
  min-height: 100vh;
  display: flex; align-items: center; justify-content: center;
  padding: 28px 36px 28px 88px;     /* vert 28/28 minimal, left 88 sidebar, right 36 */
}
```

### Dimensiones verificadas (estandar para TODOS los modulos)

| Viewport     | Card        | Aspect | Notas                        |
|--------------|-------------|--------|------------------------------|
| 1280×720     | ~1130×636   | 16/9   |                              |
| 1366×768     | **1216×684**| 16/9   | resolucion base mas comun    |
| 1920×1080    | 1600×900    | 16/9   | hits cap absoluto            |
| 2560×1440+   | 1600×900    | 16/9   | cap mantiene legibilidad     |
| <900 px wide | stack vert  | auto   | break a vertical, scroll OK  |

**Estas dimensiones son ESTANDAR.** Cualquier nuevo modulo (Agenda, Networking, Sponsors, etc.) reusa el `<CanvasCard>` componente — NO se ajustan formulas. La consistencia visual entre modulos depende de eso.

### Tipografia escalable

Usar `clamp()` para que escale con el viewport, NO media queries duras:
- Display protagonista: `clamp(32px, 3.4vw, 60px)`
- Numerico grande (countdown, timers): `clamp(42px, 4.2vw, 78px)`
- Stat cards num: `clamp(22px, 2.2vw, 36px)`
- Titulo de seccion: `clamp(18px, 1.6vw, 28px)`
- Padding del contenedor: `clamp(26px, 2.6vw, 56px)`

### Como aplicarlo a otro modulo

1. Copia el shell (sidebar + bell-pill + stage) tal cual.
2. Reemplaza el contenido del `.card` con tu modulo. El card es el contenedor; tu modulo es lo de adentro.
3. Si tu modulo necesita scroll interno (lista larga de Agenda, grid de Networking), pon `overflow-y: auto` dentro del card, NO en el card mismo.
4. Si necesitas split (poster + sidebar interno), usa grid `7fr 3fr` o `6fr 4fr` (probados en LIVE/ENDED del home).
5. Si tu modulo es un grid (Networking de personas, Sponsors), usa grid responsive con `clamp` en gaps y card sizes.

### Reglas duras del shell (VisionOS)

- Nada flota sobre el ambient sin surface (regla VisionOS — ver `feedback_glass_rule`)
- Bell NO va en circulo aislado (se ve "anadido")
- Perfil va en sidebar, NO duplicado en toolbar
- Sin strip footers sueltos debajo del card
- Si necesitas info adicional, va dentro del card o en una pill propia con surface

**Demo de referencia activa:** `design/features/webapp/W0-spatial/home-v2-C-cinematic-MUTE.html`

---

## Estrategia responsive — 3 viewports activos + 1 bloqueado

### Los 3 viewports que SI disenamos

**1. Desktop + Laptop (≥1100 px wide)**
- Patron completo del shell W.0 (canvas raiz + sidebar pill + bell-pill)
- Tipografia con `clamp()` escala 1280→1920+
- Cap absoluto card 1600×900

**2. Tablet horizontal (≈900-1100 px wide)**
- Mismo patron del shell W.0 con ajustes menores
- Sidebar pill puede comprimirse (52→44 px)
- Card formula sigue funcionando (escala automatica)
- Splits internos pueden pasar de 7/3 a 6/4 segun necesidad

**3. Mobile / celular (<640 px wide)**
- **Adaptamos lo de la app nativa Expo, NO inventamos layout web propio**
- Bottom tab bar (no sidebar pill)
- Stack vertical, sin splits laterales
- Cards full-width con paddings mobile (16-20 px)
- Mismo lenguaje visual de la app: tabs, modales, sheets
- Aqui SI va el QR del asistente (ver `feedback_qr_only_mobile`)

### El viewport BLOQUEADO

**Tablet vertical (portrait, 640-900 px wide)**
**NO se disena.** Bloqueado con overlay "Voltee la tablet". Patron ya implementado en login (`design/features/webapp/Login/iteraciones/login-v7-davinci-FINAL.html` lineas 772-806).

Componente reusable:

```css
.rotate-overlay {
  display: none;
  position: fixed; inset: 0;
  z-index: 999;
  background: var(--bg-sunken);
  align-items: center; justify-content: center;
  flex-direction: column;
  gap: 20px; padding: 40px;
  text-align: center;
}
[data-viewport="tablet-portrait"] .rotate-overlay { display: flex; }
[data-viewport="tablet-portrait"] .stage { display: none; }
```

Con SVG de tablet rotando (animacion `rotateBack 2s` toggle 0deg ↔ -90deg) + h2 "Voltea tu tablet" + p explicativo.

**Por que:** las cards 16/9 + el sidebar lateral + los splits requieren width. Tablet portrait (768 wide) es demasiado angosto para la composicion desktop pero demasiado ancho para la composicion mobile. Forzar al usuario a horizontal es mejor que servir un layout malo en ambos sentidos.

### Flujo de trabajo por modulo

1. **PRIMERO** disenar para **Desktop + Tablet H** usando el patron canvas raiz. Aqui se valida la composicion principal y la estetica cinematic/spatial.
2. **DESPUES** adaptar para **Mobile** tomando como referencia la app nativa Expo (`apps/eventos-app/`). El layout web mobile debe sentirse igual que la app — mismas tabs, mismas cards, mismo flujo. NO inventar un mobile-web propio.
3. **Tablet portrait** queda automaticamente cubierto con el overlay reusable.

### Deteccion de viewport en prod

Hoy en demos usamos `data-viewport` attribute manual (botones de testing). En prod necesitamos:
- Hook `useViewport()` que detecte width + orientation
- Retorna: `'mobile' | 'tablet-portrait' | 'tablet-landscape' | 'desktop'`
- Aplica el `data-viewport` al body
- En tablet-portrait: monta el `<RotateOverlay />` y oculta el resto

---

## Sidebar pill — items y comportamientos

```
[E] Logo evento (link a Home)
─────────────────
[⌂] Inicio
[📅] Agenda
[●] En vivo (solo activo si hay sesion live, dot rojo pulsante + tooltip nombre sesion)
[👥] Speakers
[💬] Networking (badge con N mensajes nuevos)
[🏢] Sponsors
[📰] Social
[⋯] Más (FAQ/Documentos/Pages — overflow)
─────────────────
[👤] Mi perfil (overlay con avatar, mis stands, mis prizes, mi recap, settings, logout)
```

**Nota — campana SI va en sidebar (decision 2026-05-04):** vive **dentro del sidebar pill** abajo del separador, antes del perfil. Misma forma que los demas items (size-9 rounded-xl). Badge accent cuando hay unread. Click → notif panel slide derecho.

Iteracion descartada: bell como pill flotante separada top-right ("se sentia improvisado, anadido al canvas"). Razon final: el sidebar es LA zona de control del shell — bell + perfil + nav todos juntos = una sola pieza coherente. Top-right queda limpio para que el card respire.

**Nota — "Mi perfil" NO "Mi QR":** el QR del asistente solo aplica en mobile (ver `feedback_qr_only_mobile.md`). En desktop/tablet H el overlay "Mi perfil" tiene avatar + datos + mis stands/prizes/recap + settings + logout, SIN QR.

---

## Estados de "modo shell"

| Modo | Cuando | Comportamiento |
|---|---|---|
| **Browse** | Sin sesion activa o navegando modulos generales | Layout estandar. 1 panel principal. Cambio de modulo = slide horizontal direccional |
| **Live** | Hay sesion streaming activa | Player es center stage. Panels auxiliares (chat/Q&A/trivia/polls) se abren al lado. Si navego a otro modulo → todo cierra |
| **Conectar** | Networking | Hasta 3 paneles convivientes (lista + perfil + chat 1:1) |

**Sub-paneles auxiliares por modulo (ejemplos):**

- Agenda → Detalle sesion → Speakers de la sesion
- Speakers → Perfil speaker → Sesiones del speaker
- Sponsors → Brand profile → Lead form
- Networking → Perfil persona → Chat 1:1
- Streaming → Chat sesion + Q&A + Trivia + Polls + Agenda mini (todos opcionales abiertos al tiempo)

---

## Top 5 mejoras innovadoras

1. **Campana global con badge** (dot rojo + counter) — siempre visible top-right, no spam intrusivo
2. **Quick switcher Cmd+K + boton visible** — search global cross-modulo (sesiones, personas, sponsors)
3. **Pill "En vivo" pulsante** — solo activo cuando hay sesion live, tooltip al hover muestra nombre sesion
4. **Badges de actividad por modulo** — networking (N mensajes), social (anuncio), mi perfil (QR caduca)
5. **Pre-carga de modulos vecinos** — al estar en Agenda, prefetchear Speakers en background. Switch instantaneo (TanStack Query `prefetchQuery`)

Bonus: estado del evento cambia atmosfera del shell (pre-event gradient cyan respira lento, live pulso rojo, ended gold quieto).

---

## Animaciones por tipo de transicion

| Tipo | Animacion | Duracion |
|---|---|---|
| Cambio modulo top-level | Slide horizontal direccional (anterior sale izq, nuevo entra der) | 350ms spring (damping 25, stiffness 220) |
| Abrir panel auxiliar | Scale-in spring desde edge correspondiente | 280ms spring soft |
| Cerrar panel auxiliar | Scale-out + fade | 200ms ease-out |
| Overlay modal (Mi perfil/Notif) | Slide-up + backdrop blur al fondo | 300ms spring |
| Cerrar overlay | Reverso slide-down + backdrop fade out | 200ms ease |
| Tab bar item active mobile | Color shift sin animacion grande | sync |

Reduced motion respetado en TODOS via `useReducedMotionPref()`.

---

## Home — diseno por estado del evento

(Detalle en `design/features/webapp/W0-spatial/home.html` — 3 estados navegables)

### Pre-event (draft / registration / published)
- Hero gigante: imagen evento + "Bienvenido, [nombre]" + titulo evento 52px + countdown integrado abajo
- Row 2 cards: "Sobre el evento" (info grid 2x2: lugar, modalidad, horario, asistentes) + "Sesion inaugural" (preview con hora destacada)
- Anuncio banner del organizador
- Sponsors logo band horizontal sutil

### Live
- Hero compacto: "Hola, [nombre]" + badge `EN VIVO` pulsante + card sesion activa con CTA "Unirme al stream"
- Row 60/40: HappeningNow grande (badge live, progress bar, proxima sesion) + GamificationHud (RGB border rotativo 6s, puntos, badges, sellos pasaporte, progress proxima badge)
- Anuncio banner (trivia abierta, sponsor activity, etc)
- Sponsors logo band

### Ended
- Hero gold subtle + "Evento finalizado" + titulo evento + recap CTA grande con preview cover (NO flip 3D — vive en modulo Mi Recap)
- Stats grid 4 cols (asistentes / sesiones / dias / conexiones)
- Encuesta post-evento card si aplica
- Memorias card con grid 6 cols

### Decisiones aplicadas en home.html

- "Bienvenido [nombre]" solo pre-event (live es "Hola"; ended sin saludo personal)
- Sponsors logo band sutil abajo (no canibaliza W.7)
- Mi Recap CTA con preview cover sin flip 3D (vive en modulo)
- "Mi agenda" NO aparece en Home (vive en modulo Agenda)
- "Tu pase con QR mini" NO aparece en desktop (QR solo mobile)
- Hero usa imagen evento `event.cover_image` con overlay oscuro (en demo es gradient placeholder)

---

## Demos HTML de referencia (validados con usuario 2026-05-02 → 2026-05-03)

`design/features/webapp/W0-spatial/`:

- **`index.html`** — Demo del shell completo (sidebar flotante + card central + 7 modulos navegables + Streaming con player + chat + trivia + Mi perfil overlay + Notif panel + Search Cmd+K). Mobile responsive con tab bar bottom replica app
- **`home-v2-C-cinematic-MUTE.html`** — **Demo activo del Home + base validada del canvas raiz** (mute por estado: PRE/LIVE/ENDED). Aqui viven las formulas exactas del card raiz, bell-pill, sidebar pill, tipografia clamp. Punto de partida para codear cualquier modulo nuevo.
- **`home-v2-C-cinematic-OPTIONS.html`** — Historico de comparacion de las 4 opciones de layout (split / overlay drawer / picture-in-picture / mute por estado). Mute fue la elegida.
- ~~`home.html`~~ — Demo original 3 estados, eliminado por usuario el 2026-05-03 (replicaba la app movil escalada, no aprovechaba desktop).

Todos son HTML+CSS+JS vanilla (sin React) — referencia visual para codear el W.0 React real con confianza.

---

## Pendientes para implementar W.0 React

1. **Componentes shell:**
   - `<SpatialShell>` wrapper top-level con estado global (modulo activo, panel aux, overlay)
   - `<SidebarPill>` con items configurables + tooltip + badges + live dot
   - `<WorkspaceCard>` central con animaciones de entrada/salida modulos
   - `<AuxPanel>` lateral derecho con slide-in spring + close button
   - `<BellFloating>` top-right card siempre visible
   - `<TabBarMobile>` 5 items + safe area bottom

2. **Overlays:**
   - `<MyProfileOverlay>` (avatar + stands + prizes + recap + settings + logout)
   - `<NotificationsPanel>` slide-right con feed de notifs
   - `<SearchPalette>` Cmd+K + boton visible (cross-modulo search)

3. **Estado global:** Zustand store con activeModule, auxPanelOpen, overlayOpen, etc

4. **Routing:** cada modulo top-level es una ruta `[locale]/(app)/[modulo]/`. Aux panels NO son rutas (state interno). Overlays NO son rutas.

5. **Pendiente UI/UX para ajustar despues del primer build:**
   - ~~**Consistencia tamano paneles entre estados Home**~~ — RESUELTO 2026-05-03 con mute por estado dentro del canvas raiz (las 3 vistas miden identico, solo cambia composicion interna)
   - Hero background: integracion con `event.cover_image` real (no gradient placeholder en demo)
   - Pre-carga modulos vecinos via TanStack Query `prefetchQuery`
   - Atmosfera del shell cambia segun estado evento (sutil)
   - Hook `useViewport()` para detectar viewport + montar `<RotateOverlay />` en tablet portrait

6. **Modulos pendientes para aplicar el patron canvas raiz:**
   - W.2 Home — DONE (cinematic + mute por estado)
   - W.3 Agenda — pendiente (lista interna scrollable dentro del card)
   - W.8 Networking — pendiente (grid 3-4 cols con cards de personas dentro del card)
   - W.7 Sponsors, W.6 Social, W.4 Streaming, W.5 Speakers, etc. — pendientes

---

## Estimacion

| Componente | Horas |
|---|---|
| SpatialShell + state global Zustand | 2h |
| SidebarPill + items + tooltips + badges | 1.5h |
| WorkspaceCard + module transitions (slide direccional) | 1.5h |
| AuxPanel + animations + close behavior | 1h |
| BellFloating + integracion Notif panel | 0.5h |
| TabBarMobile + responsive switch | 1h |
| MyProfileOverlay (con condicional QR mobile-only) | 1h |
| SearchPalette Cmd+K + boton visible | 1h |
| Routing per modulo + pre-load vecinos | 0.5h |
| **Total** | **~10h** |

---

## Cierre de modulo

- [ ] Shell completo desktop + tablet H + mobile validado en device real
- [ ] Cada modulo top-level navegable con slide direccional
- [ ] Bell + Mi perfil + Search overlays funcionando
- [ ] Streaming con player + apertura/cierre paneles aux validado
- [ ] Networking con perfil + chat 1:1 conviviendo
- [ ] Reduced motion respetado en todas las animaciones
- [ ] TanStack Query prefetch vecinos
- [ ] PENDIENTES.md actualizado al cerrar
