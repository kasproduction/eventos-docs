# ROADMAP — Mission Control (Moderador)

**Fecha:** 2026-04-19
**Estado:** v4 COMPLETO — Display LED, metricas RT, moderacion, Q&A proyectable, herramientas moderador, responsive.
**Objetivo:** Panel web donde el moderador controla toda la interaccion en vivo de una sesion

---

## Contexto

### Que existe hoy
- **Mission Control HTML** (chat-monitor.html): panel unificado con control bar (Chat/Q&A/Polls/Custom), toggles independientes (Emoji Only, Slow Mode), boton GUARDAR. Diseno Lumina Noir con PlusJakartaSans + Urbanist, accent TEAL #39d2c0.
- **Backend**: SessionConfigController con 7 campos live config (chat_enabled, qna_enabled, polls_enabled, emoji_only, slow_mode_seconds, custom_enabled, custom_url). Socket broadcast RT.
- **HMAC access**: `/monitor/{sessionId}?token={hmac}` genera token temporal y redirige al monitor. Sin login Filament.

### El problema resuelto
El moderador tenia que usar 3 herramientas diferentes. Ahora Mission Control unifica todo en un solo panel con toggles radio (Chat OR Q&A OR Polls OR Custom) + boton GUARDAR que persiste y emite socket RT.

### Seguridad Custom Embed
- Solo HTTPS
- Whitelist de dominios por defecto (slides.com, docs.google.com, miro.com, mentimeter.com, slido.com, youtube.com, vimeo.com, figma.com, canva.com, pitch.com, prezi.com, kahoot.it, ahaslides.com)
- Dominios custom por evento (allowed_embed_domains en events table)
- iframe con sandbox="allow-scripts allow-popups allow-forms" + referrerpolicy="no-referrer"

---

## Arquitectura

```
Filament (admin)
    |
    v
[Boton "Abrir monitor"] → /monitor/{sessionId}?token={hmac}
                                |
                                v
                    ┌─────────────────────────┐
                    │    MISSION CONTROL       │
                    │                         │
                    │  Control Bar:            │
                    │  [Chat][Q&A][Polls]      │
                    │  [Custom][Emoji][Slow]   │
                    │         [GUARDAR]        │
                    │                         │
                    │  Panel activo abajo      │
                    └──────────┬──────────────┘
                               |
                    PATCH /admin/sessions/{id}/live-config
                               |
                    Socket.IO (session:config_updated)
                               |
                    ┌──────────▼──────────────┐
                    │      APP MOVIL          │
                    │                         │
                    │  chat_enabled=false →    │
                    │  oculta input            │
                    │  custom_enabled=true →   │
                    │  muestra iframe          │
                    └─────────────────────────┘
```

---

## Completado

### Backend (2026-04-17)
- [x] Migration: `chat_enabled`, `qna_enabled`, `polls_enabled`, `emoji_only`, `slow_mode_seconds` en `event_sessions`
- [x] Migration: `custom_enabled`, `custom_url` en `event_sessions` + `allowed_embed_domains` en `events`
- [x] Model: fillable + casts boolean/integer
- [x] `SessionConfigController`: GET/PATCH admin + GET public + custom_url validation + domain whitelist
- [x] Rutas: `PATCH /admin/sessions/{id}/live-config` + `GET /events/{id}/sessions/{id}/live-config`
- [x] Socket: `/internal/session-config` → broadcast `session:config_updated` (spread generico, propaga todos los campos)
- [x] 20 tests (defaults, toggles, validation, auth, public read, custom URL, domain whitelist) — 46 assertions

### Monitor Web (2026-04-17)
- [x] Restructurado como Mission Control con control bar + panels
- [x] Toggle radio: Chat / Q&A / Polls / Custom (uno activo a la vez)
- [x] Toggles independientes: Emoji Only (switch) + Slow Mode (select)
- [x] Boton GUARDAR → PATCH live-config → socket broadcast
- [x] Panel Chat: mensajes RT, ban, delete, pin/unpin, velocidad cola, sidebar baneados
- [x] Panel Q&A: fetch pending, socket RT (question:submitted/approved/answered), filtros pending/approved/answered, botones aprobar/descartar/respondida
- [x] Panel Polls: fetch por evento, filtro session_id, barras porcentaje RT (poll:updated), lanzar draft, cerrar activa, ver resultados, soporta multiple_choice + star_rating + open_text
- [x] Panel Custom: input URL HTTPS, preview iframe con sandbox, validacion visual
- [x] Header Lumina Noir: MISSION CONTROL brand, session name, stats, pill conexion
- [x] Diseno: PlusJakartaSans + Urbanist, accent TEAL, dark mode, CSS variables
- [x] Fetch inicial config al cargar (restaura estado actual)

### HMAC Access (2026-04-17)
- [x] Ruta: `GET /monitor/{sessionId}?token={hmac}` en web.php
- [x] HMAC: `hash_hmac('sha256', sessionId|eventId, APP_QR_SECRET)`
- [x] Genera token Sanctum temporal (12h) para API calls
- [x] Redirige a chat-monitor.html con todos los params
- [x] Sin login Filament necesario

---

### Fase 2 — App reacciona a toggles (2026-04-17) COMPLETADO

- [x] `useSessionConfig` hook: fetch GET /live-config + socket `session:config_updated`
- [x] Panel activo derivado del config RT (chat/Q&A/polls/custom/none)
- [x] `chat_enabled=false` → DisabledBanner "Interaccion desactivada por el moderador"
- [x] `emoji_only=true` → ChatPanel muestra solo strip emojis, oculta TextInput
- [x] `custom_enabled=true` → CustomPanel WebView con URL embebida
- [x] PinnedBanner solo visible cuando chat activo
- [x] Fallback a initial `interactive_mode` si config no ha cargado

### Fase 3 — Filament integration (2026-04-17) COMPLETADO

- [x] Seccion "Mission Control — Config en vivo" en edit page: toggles chat/Q&A/polls/custom, emoji_only, slow_mode, custom_url
- [x] Boton "Monitor" en tabla con URL HMAC (reemplaza antiguo boton "Chat")
- [x] Abre Mission Control en nueva ventana sin crear tokens innecesarios

---

## Estimacion total

| Fase | Esfuerzo | Estado |
|------|----------|--------|
| Backend | COMPLETADO | 20 tests, 46 assertions |
| Monitor Web | COMPLETADO | ~600 lineas HTML/CSS/JS |
| HMAC Access | COMPLETADO | Ruta publica con token temporal |
| Fase 2 — App toggles | COMPLETADO | useSessionConfig + panels RT |
| Fase 3 — Filament | COMPLETADO | Boton HMAC + live config section |
| Bug fixes | COMPLETADO | 7 bugs corregidos (XSS, race, socket) |

## Pendiente

### Pulido visual premium — COMPLETADO (2026-04-18)
- [x] Rediseno completo Lumina Noir (#0A0A0A, accent blanco)
- [x] Metricas con color (azul/verde/amber/teal), chat con badges/zebra/timestamps
- [x] Hover states, toast top-right, timeline client-side, ban confirm modal
- [x] Polls barras animadas con deferred rAF, counter animado, ranking

### Games tab
- [ ] 5ta tab "Games" o "Interactivo" para ruleta/Kahoot/bingo/Unity
- [ ] Depende del backend de juegos (Fase 2)

| **Total restante** | **2-3h pulido** | — |

---

## Notas tecnicas

- El monitor son 3 archivos separados: mission-control/index.html + styles.css + app.js
- La ruta /monitor/{sessionId} inyecta __MC_CONFIG__ con token Sanctum en el HTML
- Los toggles radio afectan SOLO la sesion especifica, no todo el evento.
- Si el socket esta offline, la config se guarda en BD y la app la lee en el siguiente fetch.
- `slow_mode_seconds` por sesion sobreescribe `chat_slow_mode_seconds` del evento.
- Custom embed: solo HTTPS, whitelist dominios, iframe sandboxed.
- HMAC token es one-way (no se puede derivar el secret), timing-safe comparison con hash_equals.
