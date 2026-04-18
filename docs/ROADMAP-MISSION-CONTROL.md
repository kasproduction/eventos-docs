# ROADMAP — Mission Control (Moderador)

**Fecha:** 2026-04-17
**Estado:** Backend completo, monitor + app pendientes
**Objetivo:** Panel web donde el moderador controla toda la interaccion en vivo de una sesion

---

## Contexto

### Que existe hoy
- **chat-monitor.html** (475 lineas): panel standalone para moderar chat de una sesion. Muestra mensajes RT, eliminar, banear usuario, anclar mensaje, slow mode, velocidad de cola. Sidebar con lista de baneados. Conecta via Socket.IO con auth por token admin.
- **Filament**: gestionar polls (crear, lanzar, cerrar), Q&A (aprobar/rechazar preguntas), chat config (palabras bloqueadas, slow mode global). Todo disperso en diferentes recursos.
- **App**: chat, Q&A y polls como tabs en pantalla streaming. Cada uno tiene su panel (ChatPanel, QnAPanel, PollPanel). Slow mode global por evento (`chat_slow_mode_seconds`).
- **Socket**: endpoints internos para chat delete, ban, pin/unpin, poll broadcast, Q&A broadcast, data invalidation, networking notify, staff notify.

### El problema
El moderador tiene que usar 3 herramientas diferentes para controlar una sesion en vivo:
1. Chat monitor (HTML) para chat
2. Filament para lanzar/cerrar polls
3. Filament para aprobar preguntas Q&A

No puede apagar/prender features en tiempo real. Si el chat se descontrola, no hay boton "apagar chat" — tiene que banear uno por uno o pausar desde config del evento (afecta TODAS las sesiones).

### La solucion
Un solo panel "Mission Control" por sesion con:
- Todo lo del chat monitor actual
- Q&A: aprobar/rechazar en RT (sin abrir Filament)
- Polls: lanzar/cerrar desde ahi
- Toggles: apagar/prender chat, Q&A, polls, emoji-only, slow mode — por sesion, en RT
- Acceso con token HMAC — moderador abre desde cualquier browser sin login

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
                    │  [Chat] [Q&A] [Polls]   │
                    │  [Config toggles]       │
                    │  [Asistentes: 234]      │
                    └──────────┬──────────────┘
                               |
                    Socket.IO (session:config_updated)
                               |
                    ┌──────────▼──────────────┐
                    │      APP MOVIL          │
                    │                         │
                    │  chat_enabled=false →    │
                    │  oculta input            │
                    │                         │
                    │  emoji_only=true →       │
                    │  solo emojis             │
                    └─────────────────────────┘
```

---

## Completado

### Backend (2026-04-17)
- [x] Migration: `chat_enabled`, `qna_enabled`, `polls_enabled`, `emoji_only`, `slow_mode_seconds` en `event_sessions`
- [x] Model: fillable + casts boolean/integer
- [x] `SessionConfigController`: GET/PATCH admin + GET public
- [x] Rutas: `PATCH /admin/sessions/{id}/live-config` + `GET /events/{id}/sessions/{id}/live-config`
- [x] Socket: `/internal/session-config` → broadcast `session:config_updated` a todo el event room
- [x] 12 tests (defaults, toggles, validation, auth, public read) — 31 assertions

---

## Pendiente

### Fase 1 — Monitor Web (~4-6h)

#### 1.1 Tab system
- [ ] Agregar tabs al chat-monitor.html: Chat | Q&A | Polls | Config
- [ ] Chat existente se mueve al tab Chat (ya funciona)
- [ ] Estado activo visual en tab seleccionado

#### 1.2 Tab Q&A
- [ ] Socket: escuchar `question:new`, `question:approved`, `question:answered`
- [ ] Lista de preguntas con estado (pendiente/aprobada/respondida)
- [ ] Botones: aprobar, rechazar, marcar respondida
- [ ] API calls: `POST /admin/questions/{id}/approve`, `POST /admin/questions/{id}/dismiss`
- [ ] Contador preguntas pendientes en tab badge

#### 1.3 Tab Polls
- [ ] Mostrar encuesta activa con barras de resultados RT
- [ ] Boton lanzar encuesta (lista de polls disponibles)
- [ ] Boton cerrar encuesta activa
- [ ] API calls: `POST /admin/polls/{id}/start`, `POST /admin/polls/{id}/close`

#### 1.4 Tab Config (toggles)
- [ ] Toggle Chat ON/OFF → `PATCH /admin/sessions/{id}/live-config { chat_enabled }`
- [ ] Toggle Q&A ON/OFF → `PATCH { qna_enabled }`
- [ ] Toggle Polls ON/OFF → `PATCH { polls_enabled }`
- [ ] Toggle Emoji Only → `PATCH { emoji_only }`
- [ ] Slider/buttons Slow Mode (0/5/10/30s) → `PATCH { slow_mode_seconds }`
- [ ] Estado visual: toggle verde=ON, rojo=OFF
- [ ] Cada toggle emite socket → app reacciona en RT

#### 1.5 Header mejorado
- [ ] Contador asistentes conectados (socket room count)
- [ ] Nombre sesion + estado (live/upcoming/ended)
- [ ] Indicador conexion socket (dot verde/rojo)

#### 1.6 Acceso con token HMAC
- [ ] Generar token: `hmac_sha256(sessionId + eventId, APP_QR_SECRET)`
- [ ] Ruta publica: `GET /monitor/{sessionId}?token={hmac}` → sirve HTML si valido
- [ ] Sin login Filament necesario — moderador abre desde cualquier browser
- [ ] Token valido solo para esa sesion

---

### Fase 2 — App reacciona a toggles (~2-3h)

#### 2.1 Socket listener
- [ ] `useChat` hook: escuchar `session:config_updated`
- [ ] Guardar config en estado local del hook
- [ ] Fetch inicial: `GET /events/{id}/sessions/{id}/live-config` al montar

#### 2.2 Chat toggle
- [ ] `chat_enabled=false` → ocultar TextInput, mostrar banner "Chat desactivado por el moderador"
- [ ] `chat_enabled=true` → restaurar input normal

#### 2.3 Emoji only
- [ ] `emoji_only=true` → input desaparece, solo strip de emojis visible
- [ ] `emoji_only=false` → input + emojis normal

#### 2.4 Q&A toggle
- [ ] `qna_enabled=false` → ocultar tab Q&A en streaming, mostrar "Preguntas cerradas"
- [ ] `qna_enabled=true` → tab Q&A visible

#### 2.5 Polls toggle
- [ ] `polls_enabled=false` → ocultar tab Polls, no mostrar encuestas
- [ ] `polls_enabled=true` → tab Polls visible

#### 2.6 Slow mode
- [ ] Leer `slow_mode_seconds` del config en vez del config global del evento
- [ ] Mostrar countdown en input si slow mode activo

---

### Fase 3 — Filament integration (~1h)

#### 3.1 SessionResource
- [ ] Campos live config en edit page (toggles)
- [ ] Solo visible cuando sesion tiene streaming

#### 3.2 Boton monitor
- [ ] Accion "Abrir monitor" en tabla de sesiones
- [ ] Genera URL con token HMAC y abre en nueva ventana
- [ ] Tooltip: "Abre el panel de control en vivo para esta sesion"

---

## Estimacion total

| Fase | Esfuerzo | Dependencia |
|------|----------|-------------|
| Backend | COMPLETADO | — |
| Fase 1 — Monitor Web | 4-6h | Backend (listo) |
| Fase 2 — App toggles | 2-3h | Backend + Socket (listos) |
| Fase 3 — Filament | 1h | Backend (listo) |
| **Total restante** | **7-10h** | — |

---

## Notas tecnicas

- El monitor es HTML standalone — no React, no build. Socket.IO client + fetch API + CSS puro.
- Los toggles afectan SOLO la sesion especifica, no todo el evento.
- Si el socket esta offline, la config se guarda en BD y la app la lee en el siguiente fetch.
- El chat monitor existente (475 lineas) es la base — se expande, no se reescribe.
- `slow_mode_seconds` por sesion sobreescribe `chat_slow_mode_seconds` del evento.
