# Docs — Indice maestro

> Toda la documentacion del proyecto EventOS, organizada por categoria.
>
> **Ultima reorganizacion:** 2026-05-01.

---

## `living/` — Docs vivos (se actualizan)

| Doc | Contenido |
|---|---|
| [PENDIENTES.md](living/PENDIENTES.md) | UNICA fuente de verdad de lo que falta. Organizado por area tecnica. |
| [COMPLETADO.md](living/COMPLETADO.md) | Historico completo de modulos cerrados. |
| [BUG-LOG.md](living/BUG-LOG.md) | Bugs historicos, abiertos y resueltos. |
| [QA-MASTER.md](living/QA-MASTER.md) | Barrido de endpoints, tests y QA general. |
| [MODULOS.md](living/MODULOS.md) | Lista de 15 modulos + 6 sistemas + admin (v1.0). |
| [EventOS_Roadmap.md](living/EventOS_Roadmap.md) | Roadmap maestro EventOS (v5.1). Fases, sesiones, timeline. |

---

## `roadmaps/` — Plans por feature

| Doc | Estado | Notas |
|---|---|---|
| [ROADMAP-DATA-CENTER.md](roadmaps/ROADMAP-DATA-CENTER.md) | Cerrado | Analytics modulo, 44 exports, SPA standalone |
| [ROADMAP-EVENT-PULSE.md](roadmaps/ROADMAP-EVENT-PULSE.md) | Cerrado | Dashboard live standalone |
| [ROADMAP-FILAMENT-PULIDO.md](roadmaps/ROADMAP-FILAMENT-PULIDO.md) | Pendiente | Event Switcher global + UI Enterprise |
| [ROADMAP-KIOSK.md](roadmaps/ROADMAP-KIOSK.md) | En progreso | Kiosko + Staff check-in |
| [ROADMAP-LIGHTMODE.md](roadmaps/ROADMAP-LIGHTMODE.md) | Cerrado | Light mode Fases 1-8 |
| [ROADMAP-LIVE-MOMENTS.md](roadmaps/ROADMAP-LIVE-MOMENTS.md) | Cerrado | Ruleta + Sorteo + Trivia |
| [ROADMAP-LUX-V2.md](roadmaps/ROADMAP-LUX-V2.md) | Cerrado | Lux v2 The Gallery |
| [ROADMAP-MISSION-CONTROL.md](roadmaps/ROADMAP-MISSION-CONTROL.md) | Cerrado | Mission Control v4 |
| [ROADMAP-MANUAL.md](roadmaps/ROADMAP-MANUAL.md) | En pausa 5/35 | Knowledge base / manual del organizador (sitio Starlight en `manual/`) |
| [ROADMAP-RECAP.md](roadmaps/ROADMAP-RECAP.md) | Implementado | Recap post-evento (validacion visual pendiente) |
| [ROADMAP-SEGURIDAD-STAFF.md](roadmaps/ROADMAP-SEGURIDAD-STAFF.md) | **ACTIVO 0/26** | 2FA TOTP staff + sesiones + accesos (supersede SEC-3.1/3.2) |
| [ROADMAP-UIUX-LANDING.md](roadmaps/ROADMAP-UIUX-LANDING.md) | Pendiente | Landing publica + UIUX |
| [ROADMAP-WEBHOOKS.md](roadmaps/ROADMAP-WEBHOOKS.md) | Cerrado | Webhooks integracion partners |

---

## `webapp/` — Web App planeacion completa

| Doc | Contenido |
|---|---|
| [PLAN.md](webapp/PLAN.md) | Master plan webapp (vision, stack, scope, estimacion ~132h) |
| [DECISIONS.md](webapp/DECISIONS.md) | 20 ADRs (auth, deploy, PWA, i18n, streaming, responsive) |
| [AUTH-SPEC.md](webapp/AUTH-SPEC.md) | Magic link + Bearer Sanctum + refresh + multi-device |
| [RESPONSIVE-SPEC.md](webapp/RESPONSIVE-SPEC.md) | 3 disenios dedicados por viewport |
| [DESIGN-SYSTEM.md](webapp/DESIGN-SYSTEM.md) | Tokens Lumina Noir + Lux portados |
| [W.0-spatial-ui.md](webapp/W.0-spatial-ui.md) | PanelManager, PillBar, presets, command palette |
| [W.1-setup-auth.md](webapp/W.1-setup-auth.md) | Next.js setup + auth + tour |
| [W.2-home.md](webapp/W.2-home.md) | Hero, GamificationHud, recap banner, anuncios |
| [W.3-agenda.md](webapp/W.3-agenda.md) | Lista, lifecycle, room-checkin, .ics, ratings |
| [W.4-streaming.md](webapp/W.4-streaming.md) | Vimeo + Q&A + chat + polls + Trivia + replay |
| [W.5-speakers.md](webapp/W.5-speakers.md) | Directorio, ratings, perfil, favoritos |
| [W.6-social-wall.md](webapp/W.6-social-wall.md) | Feed, Stories, Hashtags, Photo Contest |
| [W.7-sponsors.md](webapp/W.7-sponsors.md) | Brand Wall, lead capture, trivia |
| [W.8-networking.md](webapp/W.8-networking.md) | Matchmaking, perfiles, chat 1:1, bookmarks |
| [W.9-encuestas-gamification.md](webapp/W.9-encuestas-gamification.md) | Engagement: leaderboard, passport, rewards |
| [W.18-hub-personal.md](webapp/W.18-hub-personal.md) | Hub personal: Mi QR, Mis... (renombrado desde W.10 el 2026-06-20 — W.10 paso a Live Hub) |
| [W.11-sockets-rt.md](webapp/W.11-sockets-rt.md) | Socket.IO + dedup + 4 capas RT |
| [W.12-polish.md](webapp/W.12-polish.md) | Polish + E2E + PWA |
| [W.13-faq-documentos-pages.md](webapp/W.13-faq-documentos-pages.md) | FAQ + Documentos + Pages dinamicas |
| [W.14-anuncios-boletines.md](webapp/W.14-anuncios-boletines.md) | Anuncios + Banners + Highlights |
| [W.15-vendor-dashboard.md](webapp/W.15-vendor-dashboard.md) | Mi Stand + Leads (OPCIONAL) |
| [W.16-live-moments.md](webapp/W.16-live-moments.md) | Trivia + Sorteo + Concurso Fotos display |
| [W.17-soporte.md](webapp/W.17-soporte.md) | Tickets + chat staff |

---

## `analysis/` — Docs de analisis

| Doc | Contenido |
|---|---|
| [ANALISIS-COMPETITIVO.md](analysis/ANALISIS-COMPETITIVO.md) | Cisco, ICE360, Bizzabo. Cotizaciones, gaps, pricing |
| [ANALISIS-LIGHTMODE.md](analysis/ANALISIS-LIGHTMODE.md) | Audit light mode pre-implementacion |
| [CODEBASE-MAP.md](analysis/CODEBASE-MAP.md) | Mapeo 3 repos: 150+ endpoints, socket events, observers |
| [GAPS-ANALYSIS.md](analysis/GAPS-ANALYSIS.md) | Gaps detallados, dedup socket, coordinacion REST+socket |
| [OPTIMISTIC-UI-AUDIT.md](analysis/OPTIMISTIC-UI-AUDIT.md) | 30 acciones auditadas, optimistic state |
| [OPTIMISTIC-UI-PLAN.md](analysis/OPTIMISTIC-UI-PLAN.md) | Plan 3 semanas, 9 PRs, metricas |

---

## `infra/` — Deploy, seguridad, stress

| Doc | Contenido |
|---|---|
| [COMPLIANCE-SEGURIDAD.md](infra/COMPLIANCE-SEGURIDAD.md) | Compliance enterprise (Bancolombia) |
| [FASE-SEGURIDAD.md](infra/FASE-SEGURIDAD.md) | Auditoria OWASP, SEC-1 a SEC-6 |
| [DISPONIBILIDAD-HA.md](infra/DISPONIBILIDAD-HA.md) | Arquitectura HA DO sao1, deploy, RT invalidation |
| [PLAN-STRESS-TEST.md](infra/PLAN-STRESS-TEST.md) | Stress test v2.0 Hetzner (referencia historica) |
| [PLAN-STRESS-TESTDO.md](infra/PLAN-STRESS-TESTDO.md) | Stress test v2.1 DO sao1 (definitivo) |
| [WHITE-LABEL.md](infra/WHITE-LABEL.md) | App config dinamico para multi-cliente |

---

## `briefs/` — One-shots, briefs, sesiones QA

| Doc | Contenido |
|---|---|
| [BRIEF-CLAUDE-CODE-OPTIMISTIC-UI.md](briefs/BRIEF-CLAUDE-CODE-OPTIMISTIC-UI.md) | Brief original audit optimistic UI |
| [PLAN-TAGS-MODULOS.md](briefs/PLAN-TAGS-MODULOS.md) | Plan tags + visibilidad modulos |
| [QA-AUTH-ONBOARDING.md](briefs/QA-AUTH-ONBOARDING.md) | QA auth + onboarding |
| [QA-SESION-20260414.md](briefs/QA-SESION-20260414.md) | QA sesion 2026-04-14 |

---

## `archive/` — Legacy, no se borra pero se aparta

| Doc | Razon |
|---|---|
| [ROADMAP-HISTORICO-v3.1.md](archive/ROADMAP-HISTORICO-v3.1.md) | Roadmap viejo, reemplazado por v5.1 en `living/EventOS_Roadmap.md` |
| [WEB-APP-PLAN.md](archive/WEB-APP-PLAN.md) | Stub legacy, redirige a `webapp/PLAN.md` |

---

## Convenciones

- **Living docs**: si el contenido cambia con cada sesion → va en `living/`
- **Roadmaps**: plans por feature, abrir/cerrar segun avance → `roadmaps/`
- **Specs webapp**: planeacion DaVinci modular → `webapp/`
- **Analisis**: docs de auditoria, gaps, mapeo → `analysis/`
- **Infra**: todo lo de deploy, seguridad, performance → `infra/`
- **Briefs**: tareas one-shot, no recurrentes → `briefs/`
- **Archive**: nunca borrar, mover aqui cuando se reemplaza algo → `archive/`

## Nota sobre raiz del proyecto

En la raiz `APP EVENTOS/` quedan solo entry points:
- `EventOS_DevSetup.md` — guia setup dev
- `EventOS_ClaudeCode_Prompt_v2.md` — prompt Claude Code
- `README.md` (si existe)
