# Web App — Experiencia Virtual Completa

> Spec tecnica para la web app de asistentes virtuales.
> Stack: Next.js 15 + Tailwind + shadcn/ui + Socket.IO

---

## Por que web app (no solo landing)

El publico virtual es el target principal del producto. Un asistente remoto esta sentado frente a su computadora — el celular lo usa para otras cosas. La experiencia del evento en el browser es la experiencia primaria para asistentes virtuales. Ambos competidores (Cisco Webex Events, ICE360) son web-first.

**Decision:** La web app NO es un complemento de la app movil. Es la experiencia completa para el publico virtual. La app movil agrega las interacciones presenciales (QR check-in, escaneo de leads, passport stamps).

## Stack tecnico

- **Framework:** Next.js 15 (App Router)
- **Styling:** Tailwind CSS + shadcn/ui (componentes base)
- **Theme:** Lumina Noir (dark mode, mismo design system que la app movil)
- **Fonts:** Urbanist (body) + Plus Jakarta Sans (headlines)
- **Real-time:** Socket.IO client
- **State:** React Query (TanStack Query) — misma estrategia que la app movil
- **Auth:** Token-based, misma API de Laravel
- **Repo:** Nuevo repo separado (eventos-web)

## W.0 — Spatial UI System (paradigma visionOS)

La web app NO usa sidebar corporativa (Cisco, Hopin, ICE360). Usa un sistema de paneles spatial inspirado en visionOS pero adaptado a Lumina Noir.

### Concepto core

- **Pill bar flotante** minimalista arriba (no sidebar lateral) — Urbanist + accent del evento
- **Sistema de paneles con jerarquia**: max 3 paneles simultaneos (1 primario + 2 secundarios)
- Abrir un panel nuevo desplaza al de menor jerarquia con animacion spring/damping
- Layout se redistribuye automaticamente segun contexto
- Ejemplo: click "unirse a sesion" → agenda se contrae, aparece player + chat

### Reglas de diseno

| Regla | Detalle |
|-------|---------|
| Max 3 ventanas | Si abres una cuarta, la de menor prioridad desaparece con transicion |
| Jerarquia clara | El panel primario domina el espacio, secundarios se adaptan |
| Transiciones spring | Animaciones con spring/damping, nunca lineales |
| No sidebar | Nunca sidebar lateral tradicional |
| No PiP | No picture-in-picture (se siente como parche) |

### Features del sistema spatial

| Feature | Detalle |
|---------|---------|
| **Paneles arrastrables** | El usuario reacomoda paneles a su gusto |
| **Presets de layout** | "Modo conferencia" (player+chat), "Modo networking" (chat+perfiles+matches), "Modo explorar" (agenda+speakers+mapa) |
| **Memoria de layout** | Recuerda el combo preferido del usuario (localStorage) |
| **Happening Now persistente** | Siempre visible, pulsa sutil cuando sesion por empezar |
| **Command palette** | Cmd+K / Ctrl+K para power users — navegar a cualquier seccion |

### Estilo visual

- Lumina Noir solido como fondo, NO blur de entorno real
- Paneles con bordes sutiles y opacidad controlada, NO glassmorphism full
- Pill bar con Urbanist, accent del evento, transiciones suaves
- Inspiracion spatial UI (visionOS) pero adaptado a nuestra linea grafica
- Referencias visuales: `design/LANDING/` (pill bar, meeting panels, floating nav)

### Implementacion tecnica

```
src/
  components/
    spatial/
      PanelManager.tsx       // Orquesta max 3 paneles, jerarquia, transiciones
      Panel.tsx               // Contenedor individual (draggable, resizable)
      PillBar.tsx             // Navegacion flotante superior
      CommandPalette.tsx      // Cmd+K overlay
      LayoutPresets.tsx       // Modos predefinidos
  hooks/
    usePanelLayout.ts         // Estado de paneles, persistencia localStorage
    useLayoutPreset.ts        // Aplicar/guardar presets
```

- Animaciones: Framer Motion (spring, layout animations, AnimatePresence)
- Drag: `@dnd-kit/core` o Framer Motion drag
- Persistencia: localStorage para layout preferido del usuario
- Responsive: en mobile (< 768px) los paneles colapsan a navegacion stack tradicional

### NO hacer

- NO copiar glass pesado de referencias — nuestro estilo es Lumina Noir solido
- NO sidebar tradicional
- NO picture-in-picture
- NO forzar spatial en pantallas < 768px — ahi va stack normal

---

## Sesiones de desarrollo

| Sesion | Feature | Detalle |
|--------|---------|---------|
| W.0 | Spatial UI System | Paneles flotantes, pill bar, presets, command palette, drag |
| W.1 | Setup + Auth + Layout | Next.js + Tailwind + shadcn/ui + login/register + layout shell spatial |
| W.2 | Home | Hero configurable, happening now, highlights carousel, sponsors |
| W.3 | Agenda | Lista completa, filtros por dia/track/tipo, favoritos, detalle sesion |
| W.4 | Streaming + Q&A + Chat | Experiencia virtual core — video embed + preguntas + chat en vivo |
| W.5 | Speakers | Directorio, perfil detallado, ratings post-sesion |
| W.6 | Social Wall | Feed, posts con fotos, comentarios, likes, memorias |
| W.7 | Sponsors | Brand Wall (grid por tier), Brand Profile, lead capture, trivia |
| W.8 | Networking | Perfiles, solicitudes de contacto, chat 1:1 |
| W.9 | Encuestas + Gamification | Encuestas en vivo, leaderboard, badges, puntos |
| W.10 | Notificaciones + Perfil | Web Notifications API, editar perfil, settings |
| W.11 | Socket.IO | Real-time sync en toda la web (agenda, Q&A, chat, social, encuestas) |
| W.12 | Polish | Responsive, Lumina Noir completo, transiciones, loading states |

## Features SOLO movil (no aplica en web)

- Kiosco de check-in (hardware dedicado)
- QR badge fisico (entrada presencial)
- Escaneo de leads con camara (presencial)
- Passport stamps con QR (presencial)
- Push notifications nativas (web usa Web Notifications API)

## Soporte tablets e iPads

No se requiere trabajo adicional:

| Dispositivo | Experiencia | Por que |
|-------------|-------------|---------|
| iPhone / Android phone | App nativa | Interacciones presenciales (QR, check-in, leads, passport) |
| iPad / tablet Android | Web app o app nativa | Web responsive aprovecha pantalla; app nativa funciona sin cambios |
| Laptop / desktop | Web app | Experiencia virtual principal |

## Ventaja tecnica

El backend no se toca. La API esta 100% lista. Los 309+ tests validan todo. La web es puramente frontend consumiendo los mismos endpoints. React (Next.js) comparte patrones con React Native.

## Estimacion

~22-28 dias de desarrollo (~7 semanas).
