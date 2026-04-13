# Pendientes Consolidados — EventOS

> Indice unico de TODO lo pendiente. Cada item apunta al documento donde esta el detalle.
> Actualizado: 2026-04-12 (final de sesion)

---

## Onboarding & Auth

| Estado | Item | Detalle en |
|--------|------|-----------|
| ✅ | Welcome, Auth, Photo, About, Interests, Done | `EventOS_Roadmap.md` Apendice I |
| ✅ | Gamificacion (AnimatedPts, SkipModal, 80pts) | `EventOS_Roadmap.md` Apendice I |
| ✅ | Banned screen Lumina Noir | `EventOS_Roadmap.md` Apendice I |
| ✅ | Auth legacy eliminado (login, register, forgot) | `ROADMAP-UIUX-LANDING.md` Paso 5 |
| ✅ | Pending-approval → Lumina Noir | sesion 2026-04-12 |
| ✅ | Activate-account → Lumina Noir | sesion 2026-04-12 |
| ✅ | Login inteligente 2 pasos (check-email → password) | sesion 2026-04-12 |
| ✅ | Activate-account redirige a onboarding photo step | sesion 2026-04-12 |
| ✅ | Onboarding admin Filament (1.x-B3): steps config JSON, Filament UI 7 secciones, FormStep generico, steps dinamicos, colores master/slave, real-time polling 30s + socket | sesion 2026-04-12 |
| ✅ | Campos dinamicos onboarding (1.x-E parcial): FormStep con campos del config, tipos text/tel/email/number/url/select, puntos por campo, guardado en registration_field_values | sesion 2026-04-12 |
| ⏳ | **1.x-E-A**: Campos FormStep tipos basicos: select BottomSheet, checkbox toggle, textarea multiline, validacion required | pendiente |
| ⏳ | **1.x-E-B**: Campos FormStep tipos avanzados: searchable_select + preset (paises/ciudades), checkbox_group multi-select, date picker. Requiere cambios Filament + API presets + app | pendiente (hacer con landing o antes) |

## Registro — Flujos futuros

| Estado | Item | Detalle en |
|--------|------|-----------|
| ⏳ | Roles presencial/virtual/hibrido (1.x-C) | `EventOS_Roadmap.md` Apendice I |
| ⏳ | Estados evento lifecycle (1.x-D) | `EventOS_Roadmap.md` Apendice I |
| ⏳ | Registro cerrado por lista emails (1.x-F) | `EventOS_Roadmap.md` tabla principal |
| ⏳ | Registro por codigo de acceso (1.x-G) | `EventOS_Roadmap.md` tabla principal |
| ⏳ | Staff invite push + cambio rol (1.x-H) | `EventOS_Roadmap.md` tabla principal |

## Seguridad

| Estado | Item | Detalle en |
|--------|------|-----------|
| ✅ | SEC-1: Room auth socket, XSS, token expiration | `FASE-SEGURIDAD.md` SEC-1 |
| ✅ | SEC-2: Headers, CORS, HTTPS, security:check | `FASE-SEGURIDAD.md` SEC-2 |
| ✅ | SEC-3: Lockout, rate limiting, FormRequests | `FASE-SEGURIDAD.md` SEC-3 |
| ✅ | SEC-3b.1: Token register → configurable (sanctum.expiration) | sesion 2026-04-12 |
| ✅ | SEC-3b.3: Middleware CheckBan server-side | sesion 2026-04-12 |
| ✅ | SEC-3b.5: Ban en tiempo real via socket | sesion 2026-04-12 |
| ⏳ | SEC-3b.2: Validar token al startup (GET /me) | `FASE-SEGURIDAD.md` SEC-3b |
| ⏳ | SEC-3b.4: Middleware approval server-side | `FASE-SEGURIDAD.md` SEC-3b |
| ⏳ | SEC-3.1: 2FA (OTP por email/WhatsApp) | `FASE-SEGURIDAD.md` SEC-3 |
| ⏳ | SEC-3.2: Device fingerprinting | `FASE-SEGURIDAD.md` SEC-3 |
| ⏳ | SEC-4: Docker, server hardening, Cloudflare, backups | `FASE-SEGURIDAD.md` SEC-4 |
| ⏳ | SEC-5: SecurityLogger, Sentry, uptime | `FASE-SEGURIDAD.md` SEC-5 |

## Moderacion & Chat

| Estado | Item | Detalle en |
|--------|------|-----------|
| ✅ | Sistema completo: ban RT, palabras bloqueadas, chat delete, monitor, slow mode, pause, config Filament, rendimiento (cache+pooling+batch) | sesion 2026-04-12 |
| ⏳ | Mensaje anclado tipo Twitch (nice to have) | `ROADMAP-UIUX-LANDING.md` sec 4.2 |

## UI/UX App

| Estado | Item | Detalle en |
|--------|------|-----------|
| ✅ | Paso 0-5: Fundamentos → Barrido visual completo | `ROADMAP-UIUX-LANDING.md` |
| ⏳ | QA visual multi-device | `ROADMAP-UIUX-LANDING.md` Paso 5 |
| ⏳ | Paso 6: Admin Premium (Filament dashboard) | `ROADMAP-UIUX-LANDING.md` Paso 6 |

## Features competitivos

| Estado | Item | Detalle en |
|--------|------|-----------|
| ⏳ | 1.C1: Analytics dashboard (PRIORIDAD MAXIMA) | `EventOS_Roadmap.md` tabla principal |
| ⏳ | 1.C2: Wallet digital (Apple/Google) | `EventOS_Roadmap.md` tabla principal |
| ⏳ | 1.C3: QR dinamico rotativo | `EventOS_Roadmap.md` tabla principal |
| ⏳ | 1.C4: Digital signage (pantallas venue) | `EventOS_Roadmap.md` tabla principal |
| ⏳ | 1.C5: Calendar sync (.ics) | `EventOS_Roadmap.md` tabla principal |
| ⏳ | 1.C6: Badge printing fisico | `EventOS_Roadmap.md` tabla principal |
| ⏳ | 1.23: Permisos granulares Filament | `EventOS_Roadmap.md` tabla principal |
| ⏳ | Rewards: Redencion de puntos | `EventOS_Roadmap.md` tabla principal |

## Web App

| Estado | Item | Detalle en |
|--------|------|-----------|
| ⏳ | W.1-W.12: Web app completa Next.js | `EventOS_Roadmap.md` Apendice F |

## Landing Web

| Estado | Item | Detalle en |
|--------|------|-----------|
| ⏳ | Landing premium (hero, speakers, agenda, registro) | `ROADMAP-UIUX-LANDING.md` seccion 1 |
| ⏳ | FAQ asistente: orbe animado + preguntas curadas | `ROADMAP-UIUX-LANDING.md` seccion 7 |
| ⏳ | FAQ backend: tabla event_faqs + CRUD Filament + API | `ROADMAP-UIUX-LANDING.md` seccion 7 |
| ⏳ | FAQ app: FAB flotante + BottomSheet + categorias | `ROADMAP-UIUX-LANDING.md` seccion 7 |

## Infraestructura & Deploy

| Estado | Item | Detalle en |
|--------|------|-----------|
| ⏳ | Dev build EAS (push, crop, animaciones) | `EventOS_Roadmap.md` checklist setup |
| ⏳ | Deploy VPS (backend + web + socket) | `DISPONIBILIDAD-HA.md` |
| ⏳ | Cloudflare R2 (storage produccion) | `DISPONIBILIDAD-HA.md` — **IMPORTANTE:** Al migrar, revisar `resolveStepsConfigUrls()` en OnboardingController y `fixStorageUrl()` en app. Con R2 las URLs seran absolutas. Ver BUG-078. |

## Error Handling

| Estado | Item | Detalle en |
|--------|------|-----------|
| ✅ | ConnectionError component + onboarding + Home error handling | sesion 2026-04-12 |

## Auditoria Auth (2026-04-12)

| Estado | Item | Detalle en |
|--------|------|-----------|
| ✅ | 39 escenarios + 10 edge cases verificados | `BUG-LOG.md` 2026-04-12 |
| ✅ | 14 bugs encontrados y corregidos (BUG-065 a BUG-078) | `BUG-LOG.md` 2026-04-12 |
| ⚠️ | CS-001/002/003: code smells no criticos | `BUG-LOG.md` code smells |

## Bugs abiertos

Ninguno critico. 3 code smells documentados (CS-001/002/003).

---

## Documentos de referencia

| Documento | Que contiene |
|-----------|-------------|
| `EventOS_Roadmap.md` | Roadmap maestro + apendices A-J |
| `docs/ROADMAP-UIUX-LANDING.md` | UI/UX pasos 0-6 + landing + estados evento |
| `docs/FASE-SEGURIDAD.md` | Auditoria OWASP + SEC-1 a SEC-5 + SEC-3b |
| `docs/BUG-LOG.md` | Bugs historicos BUG-001 a BUG-078 |
| `docs/COMPLIANCE-SEGURIDAD.md` | Compliance legal/GDPR |
| `docs/DISPONIBILIDAD-HA.md` | Alta disponibilidad + deploy strategy |
