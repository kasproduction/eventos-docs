# W.3 Agenda — QA Checklist

> Validacion manual del modulo W.3 implementado 2026-05-06.
> Ejecutar antes de promote a staging/produccion.

## Pre-requisitos

- [ ] Backend Laravel arriba (`http://eventos-backend.test`)
- [ ] Webapp Next.js arriba (`pnpm dev` en `eventos-web/`)
- [ ] Sesion iniciada via magic link en `/es/login`
- [ ] Evento con al menos 5-10 sesiones con tracks, speakers y descripciones
- [ ] Al menos 1 sesion `live`, 1 `past`, 1 `upcoming` para cubrir estados

---

## Tests automatizados (CI gate)

### Vitest unit (33 tests)
- [x] `pnpm test` → `tests/components/agenda/agendaDerive.test.ts` 33/33 verde
  - buildDayStrip: 5 escenarios (vacio, 1 dia, 8 dias, intermedios empty, label es-CO)
  - firstDayWithSessions: vacio + cronologico
  - deriveUiState: 6 escenarios (live/past/scheduled past/scheduled future/scheduled live/cancelled)
  - trackSlug: 11 mappings + fallback + null
  - formatTime: 2 (sin sufijo am/pm + invalido)
  - collectTracks: 2 (vacio + dedupe)
  - totalSessions: 3 (con/sin filter favoritesOnly)

### Typecheck + lint
- [x] `pnpm check` → typecheck + lint OK (0 errors, 0 warnings)

### E2E Playwright (PENDIENTE)
- [ ] `pnpm test:e2e` → happy path filtrar+favoritar+detalle
- [ ] `pnpm test:e2e` → edge case search sin resultados muestra empty correcto

---

## QA visual — 3 viewports

### Desktop (>= 1024px)
- [ ] Header + DayStrip + ChipFilters + agenda-card alineados al 60% width left
- [ ] Pills daystrip: el dia seleccionado queda centrado horizontalmente
- [ ] Pills empty (sin sesiones): visible con opacidad reducida, no clickeables
- [ ] Detail panel aparece floating right cuando seleccionas sesion (slide-in suave)
- [ ] Empty hint visible cuando no hay session seleccionada

### Tablet horizontal (640-1023px)
- [ ] Canvas raiz se adapta al alto disponible (sin 35% espacio muerto)
- [ ] Pills daystrip scrolleable horizontalmente con auto-center
- [ ] Detail panel se monta en el lado derecho sin tapar la lista
- [ ] Heart, action buttons y tabs touchables (>= 32px area)

### Tablet vertical
- [ ] Bloqueada con overlay "voltea la tablet" (heredado del shell W.0)

### Mobile (< 640px)
- [ ] Webapp en mobile redirige al app movil (heredado del shell W.0)

---

## QA funcional — flujos clave

### F1: Cambio de dia + tab
- [ ] Click en pill de otro dia → day-slide animation (out izq + in der)
- [ ] Cambio de tab Agenda <-> Mi Agenda → contador actualiza
- [ ] Si panel detalle abierto al cambiar tab → cierra suave primero
- [ ] Tab "Mi Agenda" sin favoritos → muestra empty state correcto

### F2: Favoritos (CRITICO — wired al backend)
- [ ] Click heart en card → fill cambia a rosa heart inmediato (optimistic)
- [ ] Toast "Guardado en Mi Agenda" aparece
- [ ] Recargar pagina → favorito persiste (backend POST exitoso)
- [ ] Click heart en sesion ya favorita → toast "Eliminado de Mi Agenda"
- [ ] Backend down → toast "No pudimos guardar tu favorito" + estado revierte
- [ ] Click "Favorita" en DetailPanel → mismo comportamiento sin animar la card

### F3: Filtros
- [ ] Click track chip → filtra a solo sesiones de ese track
- [ ] Multi-select chips (varios tracks) → OR entre tracks
- [ ] Click "Todos" → limpia filtros
- [ ] Si solo el evento usa 2 tracks → no aparecen los 5 chips, solo 2

### F4: Buscar
- [ ] Click lupa → search input se expande, oculta tabs/Todas/lupa
- [ ] Escribir texto → filtra en tiempo real (sin debounce — OK con poca data)
- [ ] Cmd+K (Mac) / Ctrl+K (Win) → abre buscador
- [ ] Esc → cierra buscador y limpia query
- [ ] Click X dentro del input → cierra y limpia
- [ ] Search sin resultados → empty state correcto

### F5: Detail panel
- [ ] Click card → panel slide-in desde derecha (480ms spring)
- [ ] Click otra card con panel abierto → swap-out izq (260ms) + slide-in der
- [ ] Click X / Esc / click misma card → panel slide-out + selectedId = null
- [ ] Flechas ↑↓ navegan entre cards visibles + scroll-into-view si fuera de viewport
- [ ] Detail muestra: badges (LIVE/Track) + title + meta (date/time/location/capacity/interesados) + acciones + about + asistencia + speakers

### F6: Calendario .ics (CRITICO — wired al backend)
- [ ] Click "Calendario" en card upcoming → descarga `.ics` con filename `sesion-{slug}.ics`
- [ ] Click "Calendario" en DetailPanel → mismo flujo
- [ ] Toast "Descargando .ics..." aparece
- [ ] Abrir .ics en Google Calendar / Outlook → evento creado correctamente con timezone

### F7: Rating (CRITICO — wired al backend)
- [ ] Sesion `past` sin rating → boton "Evaluar" dorado en card y detail
- [ ] Click "Evaluar" → modal aparece con scale + slide-up
- [ ] Click estrella → label dinamico ("Muy mala" → "Excelente"), pop animation
- [ ] Submit con rating + comment → POST exitoso, toast "Gracias por tu evaluacion"
- [ ] Recargar pagina → estrellas readonly aparecen en card past
- [ ] DetailPanel de sesion ya calificada → muestra `dp-rated` con estrellas + "Tu evaluacion"
- [ ] Boton "Evaluar" del detail desaparece cuando ya calificada
- [ ] Click estrellas readonly en card → NO hace nada (no boton, es span)
- [ ] Esc cierra modal sin enviar
- [ ] Click backdrop cierra modal

### F8: Live tick (auto-refresh estados)
- [ ] Esperar ~30s → estados de sesiones se reevaluan (ej. una `upcoming` cuyo `start_datetime` paso → se vuelve `live` con badge rojo)

### F9: Asistencia (oculto)
- [ ] DetailPanel NO muestra seccion "Asistencia" con avatares (correctamente oculta)
- [ ] Si `favorites_count > 0` → meta-card muestra "X interesados"

### F10: Atajos de teclado
- [ ] Cmd/Ctrl + K → abre buscador
- [ ] Esc → cierra capas en orden: rating → attendees → panel → search
- [ ] ↑↓ → navega sesiones (solo cuando panel detalle abierto)

---

## QA Lux (theme switch)

- [ ] Toggle Lux/Noir desde el theme switcher del shell
- [ ] Lux: cards con fondo `#FFFFFF` + shadow elevation (no border)
- [ ] Lux: track tags pastel correctos (tech blue, business green, innovation orange, leadership pink, culture purple)
- [ ] Lux: pills daystrip empty con `bg rgba(255,255,255,0.6)` (no opacity 0.32)
- [ ] Lux: gold rating `#8E8170` (mas oscuro que noir `#B5A68B`)
- [ ] Detail panel + RatingModal heredan tema correctamente

---

## QA Performance

- [ ] First load `/es/agenda` < 2s en dev (medir con DevTools Network)
- [ ] Day-slide animation 60fps (sin jank visible)
- [ ] Detail swap entre 2 sesiones se siente fluido
- [ ] Search con 100+ sesiones en memoria → filtrado < 50ms (filter en cliente)
- [ ] Sin warnings React 19 strict en console (verificado con `lumina toast` fix)

---

## QA Accesibilidad

- [ ] Tab navigation cycle: header tools → tabs → daypills → chips → cards → heart → action buttons
- [ ] Focus-visible outline visible en todos los focusables (sutil del border-strong, no accent grande)
- [ ] aria-labels en heart ("Agregar/Quitar de Mi Agenda")
- [ ] aria-label en estrellas readonly ("Tu calificacion: N estrellas")
- [ ] aria-label en daypills empty (`aria-disabled`)
- [ ] role=tablist en tabs y daystrip
- [ ] role=dialog + aria-modal en RatingModal y AttendeesPop
- [ ] `prefers-reduced-motion` apaga animaciones (heredado de globals.css)

---

## Estado actual

- ✅ **Tests automatizados:** 33 unit + 22 existentes = 55 verde
- ✅ **Typecheck + lint:** verde
- ✅ **Curl backend proxies:** verificado (favoritos, rating, calendar, my-ratings)
- ⏳ **QA visual 3 viewports:** pendiente sesion DaVinci con browser real
- ⏳ **QA funcional flujos F1-F10:** pendiente sesion manual
- ⏳ **E2E Playwright:** pendiente — requiere mock backend o seed dedicado
- ⏳ **QA Lux/Noir:** pendiente browser real
- ⏳ **QA Performance + a11y:** pendiente

---

## Bugs encontrados durante el barrido (RESUELTOS)

Ver `docs/living/BUG-LOG.md` BUG-318 a BUG-322:
- BUG-318: lumina toast en setState updater (CRITICA, fixed)
- BUG-319: focus-visible ring rojo accent (MEDIA, fixed)
- BUG-320: heart anim cross-state (MEDIA, fixed)
- BUG-321: asistencia mock con speakers (MEDIA, fixed)
- BUG-322: re-rate sin manejo 409 (ALTA, fixed)
