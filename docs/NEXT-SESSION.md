# Siguiente sesion — continuidad de contexto

> Este archivo es **solo continuidad** (que hicimos la sesion pasada, decisiones cerradas).
>
> **Para saber que sigue → abrir `docs/living/PENDIENTES-WEBAPP.md`** (ventana operativa unica).

---

## Ultima sesion

**Fecha:** 2026-06-20 (sesion larga, retomamos despues de 1 mes de pausa)
**Pivote comercial:** Bancolombia se perdio. El producto sigue, ahora generico para el proximo cliente.

**Que se hizo:**

1. **Re-auditoria realidad codigo vs doc** — el PENDIENTES-WEBAPP estaba desactualizado de mes y medio. Counters corregidos: W.5 0%→94%, W.6 0%→45%, W.10 Live Hub nuevo 100%, W.18 Hub Personal renombrado. **Total global 40% → 48.7%** (344/707 items).
2. **Sprint 0 hygiene completo (4/4):** fix tests vitest 194→202 verde (Node 25 localStorage stub), Laragon backend OK, smoke 6 rutas sin 500, screenshot Valorant random borrado.
3. **Sprint 1 cierres y pulido (7/8):** W.5 Speakers cierre formal (94%), W.10 Live Hub cerrado por consenso (100%, validado visual con `LiveHubDemoSeeder`), rename W.10→W.18 con refs actualizadas, **W.8 ConfirmPop DaVinci** (componente reusable patron `rating-pop`, NO shadcn — rechazada v1 generica), **W.8 Bloqueados list** (tercera tab Solicitudes con BlockedRow + handler optimistic), skeleton v2 honesto (despues de iteracion donde v1 sobreprometia), W.6 tabs filtros feed (Recientes/Mas likes/Mis posts con helper pure + 6 tests).
4. **6 bugs nuevos registrados** (BUG-329 a BUG-334): vitest Node 25, grid Personas truncado 1366x768, boton sobredimensionado, boton outline lavado en Lux, skeleton v1 sobreprometia, React key warning BlockedRow tipo wrong.
5. **Memorias actualizadas:** `feedback_una_sola_ventana_operativa.md` ahora incluye **regla de disciplina** (marcar `[x]` antes de commitear, no despues — el doc se vuelve cementerio mentiroso sin esto). `feedback_analyze_before_code.md` con anti-pattern shadcn Dialog generico vs patron custom del proyecto (`rating-pop`/`confirm-pop`). `project_social_unified_notes.md` con **divergencia desktop vs mobile** (desktop unifica `/social`, mobile debe SEPARAR como Expo: `/social` + `/networking`).

### Decisiones cerradas en esta sesion (no preguntar)

- **Producto generico post-Bancolombia** — webapp se sigue construyendo para el proximo cliente, no se reescribe ni se pausa.
- **Webapp modal/dialog pattern**: NUNCA defaultear a shadcn AlertDialog/Dialog generico. Usar custom pattern del proyecto (`rating-pop` / `confirm-pop`) con tokens Lumina Noir/Lux + Plus Jakarta 600+. **shadcn AlertDialog rechazada explicitamente** por generica y por usar fuentes default. Ver `feedback_analyze_before_code.md`.
- **Disciplina doc operativo**: al cerrar implementacion, marcar `[x]` en PENDIENTES-WEBAPP **antes** de commitear, incluido en el mismo commit. Sin esto el doc se vuelve cementerio mentiroso (como nos paso entre mayo 8 y mayo 21).
- **W.10 conflicto resuelto**: codigo usa W.10 Live Hub (mas reciente, commit `0e185e6`). Doc viejo "W.10 Hub Personal" renombrado a W.18. Sin refactor codigo, solo doc.
- **Mobile webapp futuro**: split `/social` vs `/networking` espejo Expo. En desktop se mantiene unificado (canvas + sidebar). Trabajo de migracion futura, no scope actual.
- **Skip docs maestros para modulos cerrados por consenso**: W.10 Live Hub NO tiene `docs/webapp/W.10-live-hub.md` (commit + JSDoc + E2E + visual ya bastan — anti-regadero). Misma regla para futuros modulos donde el codigo + tests cubren bien.
- **Responsive REAL no parche**: usar `clamp()` + `min-width: max-content` + `auto-fill, minmax(min(X, 100%), 1fr)` + flex-wrap. Nada de tiers fijos con N media queries.
- **Tokens theme-aware obligatorio en outline buttons**: `--text-primary` + `--border-strong` + `--surface-low` (se invierten Noir/Lux). NO usar `--slate-light` u otros tokens hardcoded.

### Estado git al cierre

- `APP EVENTOS main` — 4 commits pusheables: `96fed63` (re-auditoria), `905a85c` (docs sync), `96d333f` (skeleton v2 + screenshots), `5792029` (COMPLETADO + BUG-LOG)
- `eventos-web main` — 4 commits pusheables: `d5108ac` (fix tests Node 25), `667a1bd` (ConfirmPop + Bloqueados + skeleton + tabs filtros), `dc9d5c4` (skeleton v2 + responsive social)
- Memorias actualizadas: `feedback_una_sola_ventana_operativa.md`, `feedback_analyze_before_code.md`, `project_social_unified_notes.md`, `project_w5_speakers_v2.md`
- Suite eventos-web: **202/202 verde**, typecheck OK
- Sin push (queda a discrecion del usuario)

---

## Para arrancar la proxima sesion

1. Abrir `docs/living/PENDIENTES-WEBAPP.md` desde donde estes
2. Mirar **"QUE SIGUE"** arriba — tarea concreta lista: **Sprint 1 / Item 8 — W.3 Bulk .ics download** (~30-45 min, boton "Descargar todas" en AgendaHeader tab Mi Agenda)
3. Despues Item 9 — **W.0 Wire modulos top-level a sidebar** (auditar handlers + tooltip "proximamente" en items sin ruta) → cierra Sprint 1
4. Sprint 2 son modulos nuevos completos (Sponsors, Engagement, Anuncios, Soporte, Hub Personal) — 30-37h, 5-6 sesiones DaVinci

**QA pendiente (visual, requiere navegar logueado):**
- Lighthouse Performance ≥85 + Accessibility ≥95 en `/es/speakers`, `/es/social`, `/es/home`
- Validar device real (laptop + tablet + mobile fisicos) en W.5 + W.8 + W.10
- Smoke en tema Lux cross-modulos (checklist en chat de sesion pasada, items 1-34)
- Smoke `/es/live` en Lux y comportamiento navegacion hero/side/upcoming

---

## Convenciones / contexto operativo

- **Working dir principal:** `C:\laragon\www\APP EVENTOS` (este repo, docs+design)
- **Webapp Next.js:** `C:\laragon\www\eventos-web`
- **Mobile Expo:** `C:\Users\Kasproduction\Projects\eventos-app`
- **Backend Laravel:** `C:\laragon\www\eventos-backend` (Laragon)
- **Modo de trabajo:** DaVinci — calidad sobre cantidad, cero emojis. PASO 0 anclado en `/siguiente`
- **E2E:** `pnpm test:e2e` levanta auto mockBackend (8101) + dev (3100). Reusa servers entre runs.
- **Workflow git:** commits cuando usuario diga "commit" / "guardar". Push solo con palabra explicita "push". Nunca skip hooks
- **Usuario:** Kamilo Arias (solo founder), espanol coloquial
- **Fuente operativa unica:** `docs/living/PENDIENTES-WEBAPP.md`
- **Fuente parity:** `docs/webapp/PARITY-MATRIX.md`
- **Bug log:** `docs/living/BUG-LOG.md` (BUG-001 a BUG-334)
- **Completado:** `docs/living/COMPLETADO.md`

---

## Como cierro cada sesion (yo, automaticamente)

Al final de cada sesion productiva:

1. **Actualizo `docs/living/PENDIENTES-WEBAPP.md`** — marcar items hechos `[x]`, actualizar counters, mover "QUE SIGUE" al proximo. **CRITICO: dentro del mismo commit que el codigo** (no despues).
2. **Actualizo `docs/living/COMPLETADO.md`** con los items shippeados (1 fila por feature/cierre).
3. **Registro bugs nuevos en `docs/living/BUG-LOG.md`** con causa raiz + fix + archivos.
4. **Actualizo este archivo (`NEXT-SESSION.md`)** con:
   - Que se hizo (3-5 bullets max)
   - Decisiones cerradas que no se deben preguntar de nuevo
   - Estado git al cierre
5. Actualizo memoria si hay aprendizajes no obvios o decisiones arquitecturales.

Asi tu workflow es: abris PENDIENTES-WEBAPP.md → ves QUE SIGUE → decides. Sin leer 4 docs.
