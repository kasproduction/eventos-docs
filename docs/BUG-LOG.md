# Bug Log — EventOS

> Registro completo de bugs encontrados y corregidos. Ordenado por fecha, mas reciente primero.
> Severidades: CRITICA (seguridad/crash/data) | ALTA (feature roto) | MEDIA (visual/UX) | BAJA (cosmetic/warning)

---

## 2026-04-10 — Sesion Vendedor + Mi Stand + Liquid Glass

### BUG-054: Rules of hooks en VendorHappeningNow
- **Severidad:** ALTA (crash)
- **Causa:** Hooks despues de early return violaba reglas de React.
- **Fix:** `e04b62e` — mover todos los hooks antes del early return.

### BUG-055: VendorHappeningNow ratio roto en flex
- **Severidad:** MEDIA
- **Causa:** flex porcentual no daba anchos correctos para carousel + Mi Stand card.
- **Fix:** `e04b62e` — anchos fijos en pixeles (65%/32%/3% gap) en vez de flex.

### BUG-056: Teal color en Mi Stand card (solo gamification usa teal)
- **Severidad:** BAJA
- **Causa:** Mi Stand card usaba colores teal que estan reservados para gamification.
- **Fix:** `e04b62e` — colores unificados blancos, teal solo en GamificationHud.

---

## 2026-04-10 — Sesion UI + Rewards

### BUG-001: SQL query expuesto al usuario en error de canje
- **Severidad:** CRITICA
- **Causa:** Endpoint `POST /rewards/{id}/redeem` sin try-catch. MySQL error propagaba query raw al cliente.
- **Fix:** `a4f5afc` — try-catch en RewardController. Throwable genera mensaje generico. report() loguea.

### BUG-002: points_log.points era unsigned — no aceptaba descuentos
- **Severidad:** ALTA
- **Causa:** `unsignedSmallInteger('points')` rechazaba -50 para canje.
- **Fix:** `4787934` — migration cambia a `smallInteger` (signed). award() permite negativos.

### BUG-003: RewardService usaba campo 'metadata' inexistente
- **Severidad:** ALTA
- **Causa:** points_log no tiene columna metadata, usa reference_type/reference_id.
- **Fix:** `4787934` — cambiado a reference_type: 'reward' + reference_id.

### BUG-004: QR de canje salia redondo
- **Severidad:** MEDIA
- **Causa:** Usaba RgbRing (circular) en vez de componente rectangular.
- **Fix:** `6cb955f` — creado RgbRect con borderRadius: 16.

### BUG-005: QR Modal de canje detras de BottomSheets
- **Severidad:** ALTA
- **Causa:** Overlay con absoluteFillObject dentro del View principal.
- **Fix:** `38cd0f5` — Modal real de RN con transparent + animationType fade.

### BUG-006: Mutation de canje perdia contexto
- **Severidad:** ALTA
- **Causa:** setRedeemConfirm(null) cerraba sheet ANTES de mutation.
- **Fix:** `38cd0f5` — mutation primero, cerrar despues, setTimeout 400ms para QR.

### BUG-007: FlashList v2 estimatedItemSize deprecated
- **Severidad:** BAJA
- **Causa:** FlashList v2 calcula tamaño automaticamente.
- **Fix:** `f3ed54a` — prop eliminada de social.tsx.

### BUG-008: Select sponsor vacio en Filament
- **Severidad:** MEDIA
- **Causa:** options() con session variable null.
- **Fix:** `1ac843c` — relationship() con preload().

---

## 2026-04-10 — Responsive Audit

### BUG-009: Skeletons overflow en pantallas 360dp (x3)
- **Severidad:** MEDIA
- **Causa:** Width fijo en vez de flex:1.
- **Fix:** `5cb8c4e` — flex:1 en skeletons.

### BUG-010: 12 pantallas SafeArea inconsistente
- **Severidad:** MEDIA
- **Causa:** pt-14/pt-6 hardcoded en vez de insets.
- **Fix:** `5cb8c4e` — useSafeAreaInsets().top + 16 en todas.

### BUG-011: Login sin ScrollView
- **Severidad:** MEDIA
- **Causa:** Formulario se cortaba en pantallas pequenas.
- **Fix:** `5cb8c4e` — ScrollView agregado.

### BUG-012: Leads header 3 botones overflow
- **Severidad:** MEDIA
- **Causa:** 3 botones en 1 fila no caben en 360dp.
- **Fix:** `5cb8c4e` — 2 filas.

### BUG-013: ModuleMenu/HappeningNow/HomeHero tamanos fijos
- **Severidad:** BAJA
- **Causa:** Heights y fontSizes hardcoded no se adaptan.
- **Fix:** `5cb8c4e` — proporcionales al screenWidth.

---

## 2026-04-09 — Social Unificado

### BUG-014: GestureDetector sin GestureHandlerRootView (x2)
- **Severidad:** ALTA (crash)
- **Causa:** GestureDetector requiere GestureHandlerRootView como ancestor.
- **Fix:** Agregar wrapping en layout.

### BUG-015: Stale closures en timer de stories
- **Severidad:** MEDIA
- **Causa:** Closure captura valores iniciales del state.
- **Fix:** useRef para referencia mutable.

### BUG-016: Comentarios no scrolleaban en BottomSheet
- **Severidad:** MEDIA
- **Causa:** GestureDetector + FlatList conflicto.
- **Fix:** Gesture solo en handle, nestedScrollEnabled=true.

### BUG-017: MediaTypeOptions deprecated en expo-image-picker
- **Severidad:** MEDIA
- **Causa:** API cambio en nueva version.
- **Fix:** Usar ['images'] array directamente.

### BUG-018: Upload foto network error con archivos grandes
- **Severidad:** MEDIA
- **Causa:** upload_max_filesize 1M en PHP.
- **Fix:** Quality 0.5 + upload_max_filesize 2M.

### BUG-019: PhotoViewer initialIndex out of bounds
- **Severidad:** MEDIA
- **Causa:** initialIndex > array length.
- **Fix:** Validar indice.

### BUG-020: ~20 bugs menores social (gesture, layout, optimistic updates)
- **Severidad:** MEDIA/BAJA
- **Fix:** Sesion dedicada, 30 bugs resueltos en total.

---

## 2026-04-09b — Sponsors

### BUG-021: Trivia nunca se mostraba
- **Severidad:** MEDIA
- **Causa:** visitStand() no se ejecutaba automaticamente al montar.
- **Fix:** Ejecutar implicitamente en mount.

### BUG-022: Loop infinito con sponsors = []
- **Severidad:** MEDIA
- **Causa:** Comparacion de referencia [] crea nuevo objeto cada render.
- **Fix:** Constante EMPTY_SPONSORS.

### BUG-023: Pixel fraccionario en grid 4col
- **Severidad:** BAJA
- **Causa:** Calculo sin Math.floor().
- **Fix:** Math.floor() en dimensiones.

---

## 2026-04-09e — Profile + Branding

### BUG-024: Beam avatar fallback no funcionaba
- **Severidad:** MEDIA
- **Causa:** Falta de fallback en Photo component.
- **Fix:** avatar_url accessor con beam fallback en todos los endpoints.

### BUG-025: SVG avatar no renderizaba en social
- **Severidad:** MEDIA
- **Causa:** react-native Image no soporta SVG.
- **Fix:** Cambiar a expo-image.

### BUG-026: Flecha en Mi Agenda (es tab, no stack)
- **Severidad:** BAJA
- **Causa:** Mi Agenda tenia header de stack con flecha.
- **Fix:** Quitar flecha cuando favoritesOnly=true.

---

## 2026-04-08 — UI Masiva (Agenda, Speakers, Streaming)

### BUG-027: Flash blanco en Android al cambiar pantalla
- **Severidad:** MEDIA
- **Causa:** Activity background default blanco.
- **Fix:** backgroundColor #0e0e0e en app.json + ScreenWrapper.

### BUG-028: interpolate template literal crash en worklet
- **Severidad:** ALTA (crash)
- **Causa:** Template literals no soportados en Reanimated worklets.
- **Fix:** Remover template literal.

### BUG-029: Animated.View absolute dentro de Pressable rompe Android
- **Severidad:** ALTA
- **Causa:** Android stacking context issue con overflow:hidden.
- **Fix:** Remover overlay, animar solo contenedor.

### BUG-030: LayoutAnimation no funciona con FlashList
- **Severidad:** MEDIA
- **Causa:** FlashList virtualiza items.
- **Fix:** Key remount para animaciones (DaySlide).

### BUG-031: Emoji SVG crash en Filament
- **Severidad:** MEDIA
- **Causa:** Filament no soporta emojis en icon fields.
- **Fix:** Heroicons.

### BUG-032: Hero titulo cortado
- **Severidad:** BAJA
- **Causa:** numberOfLines 2 + lineHeight incorrecto.
- **Fix:** numberOfLines 3, lineHeight 46.

### BUG-033: BreathingCarousel spring brusco
- **Severidad:** BAJA
- **Causa:** Spring behavior en Reanimated.
- **Fix:** withTiming suave + Easing.out(cubic).

### BUG-034: Highlight re-trigger al favoritar
- **Severidad:** MEDIA
- **Causa:** State no diferenciaba user tap vs programmatic.
- **Fix:** didHighlight ref.

### BUG-035: debounceTimer con useState
- **Severidad:** MEDIA
- **Causa:** useState se reinicializa cada render.
- **Fix:** useRef.

---

## 2026-04-07 — Home + API

### BUG-036: start_datetime/end_datetime API mismatch
- **Severidad:** ALTA
- **Causa:** Codigo esperaba start_datetime, API retorna start/end.
- **Fix:** Cambiar toda la app a start/end.

### BUG-037: Pressable function style no aplica backgroundColor en Android
- **Severidad:** MEDIA
- **Causa:** Limitacion de React Native en Android.
- **Fix:** Separar View (bg) + Pressable (gesture).

### BUG-038: favoritedBy field name incorrecto
- **Severidad:** MEDIA
- **Causa:** API retorna favoritedBy, no favorites.
- **Fix:** Corregir en controller y tipos TS.

---

## 2026-04-07 — Security Audit (SEC-1/2/3)

### BUG-039 a BUG-049: 11 vulnerabilidades de seguridad
- **Severidad:** CRITICA
- Socket room auth, HTMLPurifier 8 modelos, token expiration, security headers, CORS, HTTPS, account lockout, Redis rate limiting, FormRequests, .env.production, security:check
- **Fix:** 42 tests, 11 fixes. Documentado en docs/FASE-SEGURIDAD.md.

---

## 2026-04-06 — Agenda/Favorites

### BUG-050: Favoritos Mi Agenda no sincronizaban
- **Severidad:** ALTA
- **Causa:** onMutate del toggleFavorite fallaba sin cache previa de mi-agenda.
- **Fix:** extractFavorites() — mi-agenda siempre derivada de agenda.

### BUG-051: Filament SelectFilter dot notation crash
- **Severidad:** ALTA
- **Causa:** Filament no soporta relaciones anidadas en SelectFilter.
- **Fix:** Query manual.

---

## 2026-04-07 — Photobooth/Social/Gamification

### BUG-052: useSessionRating no persistia en Expo Go
- **Severidad:** ALTA
- **Causa:** MMKV no sincroniza entre pantallas en Expo Go.
- **Fix:** Reescribir con react-query.

### BUG-053: Error de limite fotos silenciado
- **Severidad:** MEDIA
- **Causa:** Mensaje del backend se perdia en error handling.
- **Fix:** Mostrar mensaje real.

---

## Resumen acumulado

| Severidad | Count | Resueltos |
|-----------|-------|-----------|
| CRITICA | 13 | 13 |
| ALTA | 19 | 19 |
| MEDIA | 36+ | 36+ |
| BAJA | 16+ | 16+ |
| **Total** | **84+** | **84+** |

Todos los bugs listados estan corregidos. Zero bugs abiertos.

---

## Patrones recurrentes (para prevenir)

1. **Android vs iOS** — Pressable, GestureDetector, flash blanco, stacking contexts
2. **Reanimated worklets** — no template literals, no shared values en render
3. **FlashList** — no LayoutAnimation, no absolute positioning, v2 rompe estimatedItemSize
4. **Filament** — no dot notation en SelectFilter, no emojis en icons
5. **Expo SDK** — APIs deprecated entre versiones, verificar changelogs
6. **Integracion** — NUNCA integrar 3+ componentes de golpe, uno a la vez
7. **DB schema** — si un campo puede ser negativo en el futuro, usar signed desde el inicio
8. **Error handling** — NUNCA exponer errores tecnicos al usuario, siempre try-catch en endpoints
