# ROADMAP — Lumina Lux v2 "The Gallery"
## Cambios por pantalla y componente

**Fecha:** 2026-04-17
**Base:** Capturas en `design/LIGHT/capturas/`
**Tokens aprobados:** `project_lux_v2_design.md`

---

## REGLAS DE DISENO

1. **MATAR EL CYAN** — cero `#00FFFF` en Lux
2. **DARK ISLANDS** — HappeningNow, GamificationHud, Mi QR badge siempre negros (#0a0a0a solido, NO transparente)
3. **SOMBRAS EN TODO** — cards blancas flotan con shadow.sm/md/lg
4. **GOLD ADAPTATIVO** — Noir #FFD700, Lux #B8860B (via semantic.gold)
5. **BACK BUTTON** — siempre `#FFFFFF` + shadow.md en Lux (NO surface.backgroundElevated)
6. **TEXTOS** — usar `textTokens.primary` (no .white), `textTokens.secondary` (no rgba)
7. **CARDS** — `#FFFFFF` + shadow.sm en Lux (no surface.low + surface.border)
8. **INPUTS** — bg `#F0F2F5` o `#FFFFFF` + shadow.sm (no surface.medium)
9. **BUTTONS** — fondo `#F0F2F5` + borde `#E5E7EB` (no surface.high + surface.borderLight)
10. **GLASS** — solo en featured/premium: BlurView intensity=30 tint="light" + bg rgba(255,255,255,0.55) + sombra exterior
11. **SESSION TYPES** — colores desde API (Filament configurable), no hardcodeados
12. **SESSION CARDS UNIFICADAS** — hora izq + type badge color + titulo + location + chevron (speaker, sponsor, attendee)

---

## ESPECIFICACIONES TECNICAS

### Dark Islands (fondo solido, NO transparente)
```
backgroundColor: '#0a0a0a'  // SIEMPRE solido
// NUNCA rgba(255,255,255,0.04) — se ve a traves en Lux
// En Lux agregar: ...shadow.lg para flotar
```

### Glass Effect (solo premium)
```
// Wrapper externo: sombra SIN overflow:hidden
<View style={{ borderRadius: 22, backgroundColor: '#FFFFFF', ...shadow.lg }}>
  // Wrapper interno: clip + blur
  <View style={{ borderRadius: 22, overflow: 'hidden', borderWidth: 1, borderColor: 'rgba(255,255,255,0.75)' }}>
    <BlurView intensity={30} tint="light">
      <View style={{ backgroundColor: 'rgba(255,255,255,0.55)' }}>
        {content}
      </View>
    </BlurView>
  </View>
</View>
// Si solo se usa View sin blur: no funciona el glass
// Si se pone shadow en el overflow:hidden: crea recuadro blanco visible
```

### Card Pattern Lux
```
isDark
  ? { backgroundColor: surface.low, borderWidth: 1, borderColor: surface.border }
  : { backgroundColor: '#FFFFFF', ...shadow.sm }
```

### Back Button Pattern
```
isDark
  ? { backgroundColor: surface.backgroundElevated }
  : { backgroundColor: '#FFFFFF', ...shadow.md }
// Icono: color={textTokens.primary}
```

### Session Type Badge (desde API)
```
const color = session.session_type?.color ?? '#999';
const typeLabel = session.session_type?.name ?? null;
// Ya NO se usa TYPE_COLORS hardcodeado
```

### Tab Bar Liquid Glass
```
// Lux:
barBg: rgba(245,246,248,0.40)
blur: 35
bubbleBg: rgba(255,255,255,0.50)
bubbleBorder: rgba(255,255,255,0.75)
highlightBg: rgba(255,255,255,0.85)
inactiveColor: icon.secondary (#6B6D72)
shadowOpacity: 0.10
```

---

## PROGRESO POR FASE

| Fase | Pantalla | Estado |
|------|----------|--------|
| 0 | Tokens (theme-lux, theme-noir, theme, themeStore) | COMPLETADO |
| 1 | Componentes compartidos (TabBar, StatusBar, BottomSheet, EmptyState, Skeleton) | COMPLETADO |
| 2 | Onboarding (InterestsStep, AuthStep, OnboardingContext, Gold) | COMPLETADO |
| 3 | Home (Header, Hero, HappeningNow dark island, GamificationHud, ModuleMenu) | COMPLETADO |
| 4 | Agenda (day pills, session cards, track filter, timeline) | COMPLETADO |
| 5 | Session Detail (titulo, metadata card, action buttons, speakers, GlassCard, GlassButton) | COMPLETADO |
| 6 | Speakers (featured glass, list items, search, speaker detail) | COMPLETADO |
| 7 | Mi QR (badge dark island, hint/wallet cards) | COMPLETADO |
| 8 | Social (PostCard, CommentsSheet, header) | COMPLETADO |
| 9 | Sponsors (brand wall cards, search, sponsor detail, sesiones unificadas, trivia) | COMPLETADO |
| 10 | Networking (suggestion cards glass, directory, contactos, solicitudes, attendee detail) | COMPLETADO |
| 11 | Leaderboard (back button, header) | COMPLETADO (parcial) |
| 12 | About (back button) | COMPLETADO (parcial) |

### Pendientes menores
- [ ] Back buttons en 7 pantallas restantes (FAQ, Support, Anuncios, Mi Stand, Leads, Lead Detail, Scanner)
- [ ] Perfil screen — migrar cards y textos
- [ ] NativeWind residuales (~13 archivos con className bg-)
- [ ] Tab Bar polish con @callstack/liquid-glass cuando soporte Expo

---

## COMPONENTES DARK ISLAND (NO tocar en Lux)

| Componente | Razon | Fondo |
|---|---|---|
| HappeningNow carousel | Contraste dramatico | #0a0a0a solido |
| VendorHappeningNow | Version compacta + Mi Stand | #0a0a0a solido |
| GamificationHud | RGB border + teal | #0a0a0a via overrides |
| VendorGamificationHud | Version compacta | #0a0a0a via overrides |
| Mi QR badge card | Credencial digital | #0a0a0a solido |
| MomentosViewer | Fullscreen media | Siempre dark |
| PhotoViewer | Fullscreen media | Siempre dark |
| QR fullscreen modal | Contraste con QR | rgba(0,0,0,0.88) |
| Scanner camera | Viewfinder | Siempre dark |
| Streaming video | Cinema | Siempre dark |

---

## FEATURE: Session Types configurables (2026-04-17)

**Backend:**
- Tabla `session_types`: id, event_id, name, slug, color, order
- FK `session_type_id` en `event_sessions`
- Filament: SessionTypeResource con ColorPicker
- API: devuelve `session_type: { name, color }` en agenda, speakers, sponsors, attendee

**App:**
- Tipos en `useAgenda.ts`, `useSpeakers.ts`, `networkingApi.ts`, `sponsorsApi.ts`
- Badges leen `session.session_type?.color` de la API
- Zero hardcoded TYPE_COLORS
