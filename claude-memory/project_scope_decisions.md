---
name: EventOS — decisiones de scope y fases
description: Qué módulos están en Fase 1 (MVP), cuáles aplazados a Fase 2 y Fase 3, y por qué
type: project
---

Decisiones tomadas en sesión de planificación (2026-03-28).

## Fase 1 — MVP activo (13 sesiones: Fase 0 + Fase 1)

**Sesiones Fase 0 (setup):**
0.1 Entorno local | 0.2 Laravel base | 0.3 Expo base | 0.4 Socket.IO base

**Sesiones Fase 1 (features):**
1.1 Auth + roles + tracking
1.2 Motor de módulos dinámicos
1.3a Contenido — Backend (agenda, speakers, docs, anuncios, custom pages)
1.3b Contenido — App (screens, cache MMKV, favoritos)
1.4 QR personal + check-in + kiosco
1.5 Leads (vendedor scanner — separado del networking)
1.6 Directorio de Patrocinadores / Stands Virtuales — NUEVO (2026-03-29)
1.7 Networking con solicitudes de contacto + aprobación — NUEVO (2026-03-29)
1.8 Gestión usuarios + bans (admin) — ban es por evento (no global). Login siempre funciona; app detecta ban en respuesta y muestra BannedScreen. Desbanear posible en cualquier momento. Ban temporal expira con Scheduler.
1.9 Chat en tiempo real por sesión (Socket.IO) — renumerado desde 1.7. Chat individual por sesión de agenda, NO chat general del evento. Room: `chat:session:{sessionId}`. Referencia: `C:\laragon\www\PROYECTOS\eventos`.
1.10 Encuestas en vivo — renumerado desde 1.8
1.11 Push notifications — renumerado desde 1.9
1.12 Tracks + Session types — enum type (lecture/workshop/break) + capacity en workshops + tabla session_tracks con color hex. ~0.5 sesión. NUEVO (2026-04-04)
1.13 Automated emails — invitación, bienvenida, recordatorio 24h/1h, ban, cancelación. Laravel Mail nativo + Queue. NUEVO (2026-04-04)

**MVP mínimo viable:** hasta sesión 1.4 (auth + contenido + check-in + kiosco).

## Fase 2 — ACTIVA (parte del plan de 2.5 meses, es el diferencial del producto)

**Decisión 2026-03-30:** Fase 2 NO está aplazada — es el diferencial competitivo y se construye dentro del mismo plazo.

- 2.1 Web Next.js + Streaming (iframe embed de URL del organizador)
- 2.2 App streaming nativo (WebView con stream_url — NO SDK de video propio)
- 2.3 Preguntas al speaker
- 2.4 Evaluación de sesiones
- 2.5 Photobooth / Memorias
- 2.6 Certificados PDF
- 2.7 Reporte post-evento PDF
- 2.8 Analytics avanzado
- 2.9 Matchmaking por intereses
- 2.10 Social wall — feed de posts/fotos del evento con likes y moderación (Socket.IO ya existe)
- 2.11 Gamification + Leaderboard — puntos por actividad, ranking en home
- 2.12 Passport Contest — stamps al escanear QR de stands (sobre S1.4 + S1.6)
- 2.13 Photo/Caption Contest — galería con votos (sobre 2.10 Social wall)
- 2.14 Video calls 1:1 — Livekit, salas efímeras en networking (app + web)
- 2.15 Floor plan del venue — imagen + stands posicionados, zoom/pan
- 2.16 Reports exportables detallados — extiende 2.8, CSV/PDF por tipo de actividad
- 2.17 Proximity chat web — SOLO Next.js. Livekit spatial audio + canvas con avatares móviles. Volumen por distancia. Deps: livekit-server-sdk (backend), livekit-client + @livekit/components-react (web). HACER AL FINAL de Fase 2 (3-4 sesiones).

**Nota 2026-03-29:** Networking básico (directorio + solicitudes con aprobación) movido a Fase 1 como sesiones 1.6 (Patrocinadores) y 1.7 (Networking). Networking avanzado (matchmaking por intereses) permanece en Fase 2.

## Fase 3 — Aplazada (cuando haya segundo cliente o plan de monetización)
- Juegos Unity + Socket.IO bridge
- Stripe + facturación
- Multi-tenant completo
- Data export Ley 1581/GDPR

**Why:** El usuario tiene 2.5 meses antes de renunciar — necesita producto completo y vendible, no solo MVP. Fase 2 es el diferencial real frente a competidores.

**How to apply:** Completar Fase 1 restante → UI completo → Fase 2 completa → Deploy. No aplazar nada de Fase 2 salvo que el tiempo lo exija.
