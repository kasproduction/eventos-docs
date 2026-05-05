# W.X — WelcomeShowcase (cinematic onboarding)

> Trailer cinematico post-login que muestra los 6 features principales del evento usando **componentes reales en miniatura** (no placeholders).
>
> **Estimacion:** ~3.5h.
> **Estado:** **BLOQUEADO** — espera cierre de W.3, W.4, W.5, W.7, W.8, W.9. No empezar antes (ADR-025).
> **Bloqueado por:** W.3 (Agenda), W.4 (Streaming), W.5 (Speakers), W.7 (Sponsors), W.8 (Networking), W.9 (Gamification).
> **Decision base:** ADR-025 en `DECISIONS.md`.

---

## Por que W.X y no W.1

El showcase es un trailer del producto: cada beat usa un modulo real. Si se construye con placeholders en W.1, queda desfasado visual y conceptualmente cuando los modulos reales lleguen. Reusar componentes reales en miniatura garantiza coherencia + cero codigo de descarte (ADR-025).

Mientras tanto, post-login va directo a `/home` sin showcase. El flag `localStorage.onboarding_completed` queda no-op hasta que esta W llegue.

---

## Beats del showcase (6)

| # | Beat | Componente fuente |
|---|---|---|
| 1 | SPEAKERS | W.5 (cards directorio + perfil) |
| 2 | AGENDA | W.3 (lista + lifecycle states) |
| 3 | STREAMING | W.4 (player + chat mini) |
| 4 | CONNECT | W.8 + W.6 (networking + social wall) |
| 5 | GAMIFICATION | W.9 (leaderboard + badges + passport) |
| 6 | SPONSORS | W.7 (brand wall + brand profile) |

Cada beat = 4-6s, transicion spring entre beats, skip en cualquier momento.

---

## Lectura obligatoria (cuando llegue)

- `PLAN.md`, `W.0-spatial-ui.md` (canvas raiz)
- `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- ADR-019, ADR-025 en `DECISIONS.md`
- Referencia funcional (NO visual): `design/showcase-onboarding-v6.html`
- Feedback `feedback_keyvisual_not_typography` (no tipografia CSS gigante, usar keyvisual del cliente)

---

## Reglas de diseno (heredadas)

- Tokens Lumina Noir + accent dinamico del evento (igual que W.0)
- Componentes en miniatura DEBEN ser los reales de W.3/W.4/W.5/W.7/W.8/W.9 (no copias divergentes)
- Datos demo si aplica, pero markup identico al modulo real
- Skip button visible siempre
- Solo aparece en primer login (`localStorage.onboarding_completed`)
- `prefers-reduced-motion`: degradar a serie de screenshots estaticos con boton "Siguiente"

---

## Estimacion

| Item | Horas |
|---|---|
| Engine (timeline + transitions + skip + reduced motion) | 1h |
| 6 beats con miniaturas (~10min c/u) | 1h |
| Tests Vitest engine + Playwright happy path | 0.5h |
| Integracion en flujo post-login + flag localStorage | 0.5h |
| QA en 3 viewports + cierre | 0.5h |
| **Total** | **~3.5h** |

---

## Cierre

- [ ] Engine funcional con 6 beats reales
- [ ] Skippable + reduced motion validado
- [ ] Solo aparece primera vez por user-evento
- [ ] Tests verde
- [ ] Memoria + PENDIENTES.md
