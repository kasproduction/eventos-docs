# Design — Indice maestro

> Todos los demos, refs visuales, screenshots y prototipos del proyecto.
> Organizado por feature + iteraciones para no perder historico.
>
> **Ultima reorganizacion:** 2026-05-01.

---

## Estructura

```
design/
├── _index.md                  # Este archivo
├── refs/                      # Referencias externas (Dribbble, competencia, inspiration)
├── features/                  # Disenios por feature con iteraciones
├── system/                    # Estilos generales no atados a feature
├── screenshots/               # Screenshots fechados sueltos
├── ERRORES/                   # Bugs visuales documentados
└── layout/                    # 60+ screenshots de la app en desarrollo
```

---

## `refs/` — Referencias externas

| Carpeta | Contenido |
|---|---|
| `refs/dribbble/` | Imagenes originales descargadas de Dribbble (.webp, hash en nombre) |
| `refs/competencia/` | Capturas de Cisco Webex, ICE360, Bizzabo, Hopin |
| `refs/inspiration/` | Otras refs visuales sueltas (UI Kit Templates, etc.) |

---

## `features/` — Disenios por feature

### `features/recap/`
Recap post-evento estilo certificado coleccionable.

| Subcarpeta | Contenido |
|---|---|
| `_APROBADO/v6/` | **Disenio aprobado v6**: Recap.html + recap.css + recap.jsx + ios-frame.jsx |
| `iteraciones/` | recap-clean*, recap-fix*, recap-fix2..fix6* — versiones intermedias |
| `debug/` | recap-debug-*, recap-roto-* — capturas de bugs visuales en desarrollo |
| `refs/originals/` | 1.webp, originals Dribbble, Recap referencia/ |

**Estado:** Implementado. Disenio v6 aprobado.

---

### `features/onboarding/`
Onboarding pre-login + tour de bienvenida webapp.

| Subcarpeta | Contenido |
|---|---|
| `_APROBADO/` | (vacio — onboarding webapp pendiente de aprobacion final) |
| `iteraciones/` | showcase-onboarding-v2..v6.html, onboarding-prototype.html, onboarding-v3.html, onboarding-steps-preview.html |
| `refs/` | Refs originales de Dribbble + onboarding/app/* + onboarding/REF/ + onboarding/faq/ |
| `early/` | onboarding-demo.html, eventos-demo-v3.html — prototipos primarios de marzo |

**Estado:** App movil onboarding implementado. Tour webapp pendiente W.1.

---

### `features/event-pulse/`
Dashboard live del evento.

| Subcarpeta | Contenido |
|---|---|
| `_APROBADO/` | event-pulse-FINAL.html — version cerrada |
| `iteraciones/` | event-pulse-demo, opcion A/B/C, v3..v7-sections — 9 versiones |
| `refs/` | Refs originales + DASHBOARD-LIVE/ |

**Estado:** Cerrado. Stack Noir, responsive, agenda, leads, RT.

---

### `features/data-center/`
Analytics modulo standalone.

| Archivo / carpeta | Contenido |
|---|---|
| `data-center-demo.html` | Demo aprobado |
| `data-center-stitch-prompt.md` | Prompt para Stitch |
| `DASHBOARD/` | Refs + output Stitch |

**Estado:** Cerrado. 9 tabs, 44 exports, SPA standalone.

---

### `features/speakers/`
Directorio + perfil speakers.

| Subcarpeta | Contenido |
|---|---|
| `iteraciones/` | speakers_preview.html v1..v3 |
| `screenshots/` | Speakers1.png, Speakers2.png, Agenda.png |

**Estado:** Implementado. App movil + webapp pendiente W.5.

---

### `features/faq/`
FAQ + experimentaciones visuales.

| Archivo | Contenido |
|---|---|
| `faq-demo.html` | FAQ demo |
| `faq-orb-demo.html` | Experimento FAQ orb (animacion Reanimated/BlurView) |
| `robot-faq-demo.html` | Experimento robot FAQ |

**Estado:** Webapp pendiente W.13.

---

### `features/kiosk/`
Kiosko de check-in presencial.

Contenido movido desde `design/checkin/` original: Kiosk Display.html + assets PNG (in.png, out.png, error.png) + stitch_enterprise_conference_kiosk_screen/.

**Estado:** Implementado. Hardware dedicado, scanner USB.

---

### `features/mission-control/`
Mission Control v4 (organizador).

Contenido movido desde `design/Monitor/`. HTML/CSS/JS del prototipo + uploads/.

**Estado:** Cerrado. v4 completo, LED, metricas, moderacion, Q&A.

---

### `features/live-moments/`
Spin wheel, sorteo, trivia, golden ticket.

| Archivo / carpeta | Contenido |
|---|---|
| `sorteo-ceremony-demo.html` | Animacion sorteo GSAP |
| `golden-ticket-demo.html` | Reveal golden ticket |
| `sorteo-refs/` | Refs Dribbble del sorteo |

**Estado:** Cerrado. Ruleta + Sorteo + Trivia + Concurso fotos + Golden ticket.

---

### `features/webapp/`
Refs visuales para la webapp (visionOS spatial UI).

| Subcarpeta | Contenido |
|---|---|
| `LANDING/` | 7 webp imagenes spatial UI: futbol AR, meeting panels, blog reader, shopping, etc. |

**Nota:** la carpeta original `design/LANDING/` se renombro a `features/webapp/LANDING/` porque es ref visual para webapp, no landing publica.

**Estado:** Webapp en planeacion. Ver `docs/webapp/`.

---

### `features/lux/`
Lumina Lux v2 (light mode).

| Archivo / carpeta | Contenido |
|---|---|
| `lux-v2-preview.html` | Preview Lux v2 |
| `noir-vs-lux-comparison.html` | Comparacion Noir vs Lux |
| `noir-vs-lux-v2.html` | Iteracion v2 |
| `LIGHT/` | Refs UI patterns light mode |

**Estado:** Cerrado. Lux v2 The Gallery aprobado 2026-04-17.

---

## `system/` — Estilos generales

| Archivo / carpeta | Contenido |
|---|---|
| `avatar-palettes.html` | Paletas de avatares |
| `collapsing-header-prototype.html` | Prototipo header colapsable |
| `gold-palette-demo.html` | Paleta dorada premium |
| `session-stats-demo.html` | Stats de sesion (KPIs visualization) |
| `event-lifecycle-demo.html` | Lifecycle eventos draft/registration/published/live/ended |
| `gsap-skills/` | GSAP utilities y skills (animaciones complejas) |
| `stitch/` | Outputs de Stitch IA |

---

## `screenshots/` — Screenshots fechados sueltos

| Archivo | Fecha |
|---|---|
| Screenshot 2026-03-30 *.png | Marzo 30 — primeros mocks |
| Screenshot 2026-03-31 *.png | Marzo 31 — variantes |
| Screenshot 2026-04-07 *.png | Abril 7 — UI fundamentos |
| mapa.png | Mapa del evento |

---

## `ERRORES/` — Bugs visuales

Screenshots de errores documentados. **No tocar** — referencias para BUG-LOG.md.

---

## `layout/` — Screenshots app en desarrollo

60+ screenshots de la app movil en distintos momentos del desarrollo. **No tocar** — referencias historicas.

---

## Convenciones

- **`_APROBADO/`**: Solo el disenio final aprobado por usuario. NO sobreescribir, agregar version (v6, v7, etc.) si hay nueva aprobacion.
- **`iteraciones/`**: Versiones intermedias (v2, v3, etc.). Se conservan como historia.
- **`debug/`**: Capturas de bugs visuales durante desarrollo. Se conservan como referencia.
- **`refs/`**: Referencias externas (Dribbble, screenshots competencia). NO se modifican.
- **`early/`**: Prototipos primarios pre-aprobacion (los primeros HTMLs sueltos).

## Que NO va en `design/`

- Codigo de implementacion (vive en repos `eventos-app/`, `eventos-backend/`, etc.)
- Refs publicas (van en sus propios repos si aplica)
- Builds (gitignored)
