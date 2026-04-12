# Pendientes Consolidados — EventOS

> Indice unico de TODO lo pendiente. Cada item apunta al documento donde esta el detalle.
> Actualizado: 2026-04-11 | Proxima sesion: empezar por lo marcado NEXT

---

## NEXT — Proxima sesion

- [ ] Pending-approval screen → Lumina Noir — `ROADMAP-UIUX-LANDING.md` Paso 5
- [ ] Activate-account screen → Lumina Noir — `ROADMAP-UIUX-LANDING.md` Paso 5
- [ ] SEC-3b.1: Token register 30d → config — `FASE-SEGURIDAD.md` SEC-3b
- [ ] SEC-3b.3: Middleware ban server-side — `FASE-SEGURIDAD.md` SEC-3b

---

## Onboarding & Auth

| Estado | Item | Detalle en |
|--------|------|-----------|
| ✅ | Welcome, Auth, Photo, About, Interests, Done | `EventOS_Roadmap.md` Apendice I |
| ✅ | Gamificacion (AnimatedPts, SkipModal, 80pts) | `EventOS_Roadmap.md` Apendice I |
| ✅ | Banned screen Lumina Noir | `EventOS_Roadmap.md` Apendice I |
| ✅ | Auth legacy eliminado (login, register, forgot) | `ROADMAP-UIUX-LANDING.md` Paso 5 |
| ⏳ | Pending-approval → Lumina Noir (1.x-B2) | `EventOS_Roadmap.md` Apendice I |
| ⏳ | Activate-account → Lumina Noir (1.x-B2) | `EventOS_Roadmap.md` Apendice I |
| ⏳ | Onboarding admin Filament: steps, textos, puntos (1.x-B3) | `EventOS_Roadmap.md` Apendice I |
| ⏳ | Campos dinamicos AboutStep (1.x-E) | `EventOS_Roadmap.md` Apendice I |

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
| ✅ parcial | SEC-3: Lockout, rate limiting, FormRequests | `FASE-SEGURIDAD.md` SEC-3 |
| ⏳ | SEC-3.1: 2FA (OTP por email/WhatsApp) | `FASE-SEGURIDAD.md` SEC-3 |
| ⏳ | SEC-3.2: Device fingerprinting | `FASE-SEGURIDAD.md` SEC-3 |
| ⏳ | SEC-3b.1: Token register 30d → config | `FASE-SEGURIDAD.md` SEC-3b |
| ⏳ | SEC-3b.2: Validar token al startup (GET /me) | `FASE-SEGURIDAD.md` SEC-3b |
| ⏳ | SEC-3b.3: Middleware ban server-side | `FASE-SEGURIDAD.md` SEC-3b |
| ⏳ | SEC-3b.4: Middleware approval server-side | `FASE-SEGURIDAD.md` SEC-3b |
| ⏳ | SEC-3b.5: Ban en tiempo real via socket | `FASE-SEGURIDAD.md` SEC-3b |
| ⏳ | SEC-4: Docker, server hardening, Cloudflare, backups | `FASE-SEGURIDAD.md` SEC-4 |
| ⏳ | SEC-5: SecurityLogger, Sentry, uptime | `FASE-SEGURIDAD.md` SEC-5 |

## UI/UX App

| Estado | Item | Detalle en |
|--------|------|-----------|
| ✅ | Paso 0-4: Fundamentos, TabBar, Home, Agenda, Speakers, Social | `ROADMAP-UIUX-LANDING.md` |
| ✅ ~99% | Paso 5: Barrido visual completo | `ROADMAP-UIUX-LANDING.md` Paso 5 |
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

## Infraestructura & Deploy

| Estado | Item | Detalle en |
|--------|------|-----------|
| ⏳ | Dev build EAS (push, crop, animaciones) | `EventOS_Roadmap.md` checklist setup |
| ⏳ | Deploy VPS (backend + web + socket) | `DISPONIBILIDAD-HA.md` |
| ⏳ | Cloudflare R2 (storage produccion) | `DISPONIBILIDAD-HA.md` |

## Bugs abiertos

| Bug | Severidad | Detalle en |
|-----|-----------|-----------|
| BUG-063: Token register 30d hardcoded | MEDIA (seguridad) | `BUG-LOG.md` 2026-04-11 |
| BUG-064: Ban no valida server-side | ALTA (seguridad) | `BUG-LOG.md` 2026-04-11 |

---

## Documentos de referencia

| Documento | Que contiene |
|-----------|-------------|
| `EventOS_Roadmap.md` | Roadmap maestro + apendices A-J |
| `docs/ROADMAP-UIUX-LANDING.md` | UI/UX pasos 0-6 + landing + estados evento |
| `docs/FASE-SEGURIDAD.md` | Auditoria OWASP + SEC-1 a SEC-5 + SEC-3b |
| `docs/BUG-LOG.md` | Bugs historicos BUG-001 a BUG-064 |
| `docs/COMPLIANCE-SEGURIDAD.md` | Compliance legal/GDPR |
| `docs/DISPONIBILIDAD-HA.md` | Alta disponibilidad + deploy strategy |
