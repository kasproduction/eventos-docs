# Bug Log — EventOS

> Registro de bugs encontrados y corregidos durante desarrollo. Ordenado por fecha, mas reciente primero.

---

## 2026-04-10

### BUG-001: SQL query expuesto al usuario en error de canje
- **Severidad:** CRITICA (seguridad)
- **Donde:** App → Canjear premio → error mostraba query SQL completa en Alert de Android
- **Causa:** El endpoint `POST /rewards/{id}/redeem` no tenia try-catch. El `abort_if()` con errores de DB propagaba el mensaje raw de MySQL al cliente.
- **Fix:** Backend `a4f5afc` — try-catch en RewardController::redeem. `Throwable` genera mensaje generico "No se pudo procesar el canje". `HttpException` (abort) pasa el mensaje controlado. `report($e)` loguea internamente.
- **Leccion:** NUNCA confiar en que abort/exception genera mensajes seguros. Siempre try-catch en endpoints que tocan DB con transacciones.

### BUG-002: points_log.points era unsigned — no aceptaba descuentos
- **Severidad:** ALTA (bloquea feature)
- **Donde:** Backend → RewardService::redeem → PointsLog::create con points negativo
- **Causa:** Migration original usaba `unsignedSmallInteger('points')`. Al insertar -50 para descuento de canje, MySQL rechazaba con "Numeric value out of range".
- **Fix:** Backend `4787934` — migration cambia a `smallInteger('points')` (signed). PointsService::award cambiado de `$points <= 0` a `$points === 0` para permitir negativos.
- **Leccion:** Si un campo puede tener valores negativos en el futuro (descuentos, refunds), usar signed desde el inicio.

### BUG-003: RewardService usaba campo 'metadata' inexistente
- **Severidad:** ALTA (bloquea feature)
- **Donde:** Backend → RewardService::redeem y expirePending
- **Causa:** El service creaba PointsLog con `'metadata' => json_encode(...)` pero la tabla points_log no tiene columna metadata. Usaba `reference_type` y `reference_id` para ese proposito.
- **Fix:** Backend `4787934` — cambiado `metadata` por `reference_type: 'reward'` + `reference_id: $reward->id`.
- **Leccion:** Verificar el schema de la tabla antes de escribir el service. No asumir que un campo existe.

### BUG-004: QR de canje salia redondo en vez de cuadrado
- **Severidad:** MEDIA (visual)
- **Donde:** App → Pantalla Desafio → Canjear premio → QR Modal
- **Causa:** El RedeemQrModal usaba `RgbRing` (componente circular para avatares del ranking) con `borderRadius: size/2`. El QR necesitaba un componente rectangular.
- **Fix:** App `6cb955f` — creado `RgbRect` con `borderRadius: 16` (rounded corners, no circulo). Mismo RGB wave pastel.
- **Leccion:** No reutilizar componentes circulares para contenido cuadrado sin verificar el borderRadius.

### BUG-005: QR Modal de canje quedaba detras de BottomSheets
- **Severidad:** ALTA (UX roto)
- **Donde:** App → Confirmar canje → QR no aparecia
- **Causa:** El QR modal usaba `StyleSheet.absoluteFillObject` como overlay dentro del View principal. Los BottomSheets se renderizaban encima.
- **Fix:** App `38cd0f5` — cambiado a `<Modal>` real de React Native con `transparent` y `animationType="fade"`. Se renderiza sobre todo.
- **Leccion:** Para overlays que deben estar encima de todo (incluyendo BottomSheets), usar Modal nativo, no position absolute.

### BUG-006: Mutation de canje perdia contexto al cerrar BottomSheet
- **Severidad:** ALTA (UX roto)
- **Donde:** App → Confirmar canje → nada pasaba
- **Causa:** `handleRedeem` hacia `setRedeemConfirm(null)` (cerraba el sheet) ANTES de ejecutar la mutation. El componente se desmontaba y la mutation perdia contexto.
- **Fix:** App `38cd0f5` — mutation se ejecuta primero (`await mutateAsync`), despues cierra el sheet, espera 400ms, luego abre el QR Modal.
- **Leccion:** Ejecutar operaciones async ANTES de desmontar el componente que las contiene.

### BUG-007: FlashList v2 estimatedItemSize deprecated
- **Severidad:** BAJA (warning/build)
- **Donde:** App → social.tsx → FlashList con `estimatedItemSize={280}`
- **Causa:** FlashList v2 calcula el tamaño automaticamente. La prop ya no existe en el tipo.
- **Fix:** App `f3ed54a` — eliminada la prop.
- **Leccion:** Al actualizar dependencias, verificar breaking changes en los tipos.

### BUG-008: Select de sponsor vacio en Filament RewardResource
- **Severidad:** MEDIA (admin)
- **Donde:** Filament → Crear premio → Select patrocinador no cargaba opciones
- **Causa:** Usaba `options()` con query manual filtrando por `$eventId` de session. La variable podia ser null.
- **Fix:** Backend `1ac843c` — cambiado a `relationship('sponsor', 'name')` con `preload()` y filtro en el query.
- **Leccion:** En Filament, preferir `relationship()` sobre `options()` manual cuando la relacion existe en el modelo.

---

## Convenciones

- **CRITICA:** Seguridad, datos expuestos, vulnerabilidad
- **ALTA:** Feature roto, UX bloqueado, data corruption
- **MEDIA:** Visual incorrecto, admin inconveniente
- **BAJA:** Warning, deprecation, cosmetic
