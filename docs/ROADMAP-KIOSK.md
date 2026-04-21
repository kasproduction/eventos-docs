# Roadmap — Kiosko Room Check-in

> Interfaz de los totems (iPads/pantallas) en las puertas de los salones.
> Backend 100% funcional. Este roadmap es solo la interfaz.
> Design system: Lumina Noir (Plus Jakarta Sans + Urbanist, NO JetBrains Mono)
> Actualizado: 2026-04-21

---

## Estado actual

Backend completo:
- POST /rooms/scan, /scan/batch, GET /ping, GET /manifest
- Auto-checkout otro salon, debounce, offline batch, heartbeat
- 17 tests, 216+ assertions, stress test 50 personas 17 escenarios
- Manifest endpoint: all checked-in attendees para cache local kiosko

Kiosko React **Lumina Noir implementado** — Fase 0 + Fase 1 parcial.

---

## Fase 0 — Demo HTML hibrido (standalone) — COMPLETADA

> Archivo: `design/checkin/kiosk-eventos-demo.html`
> Aprobado: 2026-04-20

- [x] Stage scaling: 1920x1080 landscape / 1080x1920 portrait
- [x] Dos layouts separados (no responsive, diseño propio por orientacion)
- [x] Header: Room eyebrow + nombre salon + reloj en vivo + fecha
- [x] Scan pill con breathing dot emerald + "Ready"
- [x] Hero sesion EN VIVO: titulo 96px (landscape) / 82px (portrait), weight 800
- [x] LIVE NOW pill con dot pulse
- [x] Meta grid: Time, Format con labels Urbanist uppercase
- [x] Progress bar: track 2px + fill gradient emerald
- [x] Speaker: foto circular (o iniciales fallback) + nombre + cargo
- [x] Siguiente sesion: seccion separada en bottom zone (bg tonal shift)
- [x] Timeline horizontal: slots past/live/upcoming + indicador NOW
- [x] Footer: nombre evento + kiosk ID + synced time
- [x] Overlays dramaticos: checkin (emerald), checkout (blue), error (red)
  - Nombre 108px weight 800, radial gradients, countdown bar
  - Stats grid: badge, entry time, duration
- [x] No-Line Rule (sin 1px borders, depth via tonal shift #0A0A0A → #0E0E0E)
- [x] Solo 2 fuentes: Plus Jakarta Sans + Urbanist
- [x] Tokens Lumina Noir corregidos (no Aether Noir azulado)
- [x] Tweaks panel para dev (orientation, overlay, clock)

---

## Fase 1 — Implementar en React (Kiosko) — EN PROGRESO

> Reemplazado RoomApp.tsx con diseño Lumina Noir.

### Completado
- [x] Portar CSS del demo a kiosk.css (design system completo)
- [x] Dos layouts separados en React (landscape `k-l` / portrait `k-p`)
- [x] Stage scaling con dimensiones fijas (no DOM-dependent)
- [x] Conectar al API: ping cada 10s → schedule completo → render dinamico
- [x] Ping enriquecido: speaker_photo, speaker_role, track, type
- [x] Scanner USB/HID (useUsbScanner) — reemplaza camara ZXing
- [x] Cooldown con busyRef sincronico (previene double-fire)
- [x] Overlays 4 estados: checkin, checkout, error, offline
- [x] Timer countdown en overlay (2.5s auto-close)
- [x] Progress bar actualiza cada 60s (no cada segundo)
- [x] Timeline NOW indicator calculado
- [x] Reloj en vivo (tick cada segundo, solo para display)
- [x] Socket listeners: session events → re-ping schedule
- [x] Offline queue: IndexedDB + batch sync (existente, mantenido)
- [x] Indicador offline: dot rojo en scan pill
- [x] Wake Lock (mantener pantalla encendida)
- [x] Auto-detect orientacion (viewport o ?orientation= URL param)
- [x] KioskDemoSeeder: room Atlas + totem + 8 sesiones con 1 LIVE ahora
- [x] Cache local nombres (useAttendeeCache): manifest → delta cada 60s
- [x] Nombre instantaneo en overlay antes de que API responda
- [x] Scan optimizado backend: lock non-blocking, Event cacheado

### Pendiente
- [ ] **Scan endpoint < 100ms** — en produccion Linux sera ~50ms, verificar
- [ ] Indicador visual de "cache cargado" (cuantos attendees en memoria)
- [ ] Sonido de confirmacion (beep checkin, tono checkout, buzz error)
- [ ] Portrait: ajustar bottom zone si no hay next session
- [ ] Test con 1000+ attendees en manifest (verificar peso/tiempo)

### Decisiones de arquitectura
- **Cache solo nombres** (Opcion 2): no predice checkin/checkout, API decide.
  Sin riesgo de estado inconsistente entre totems. Cache es solo para velocidad visual.
- **USB scanner > camara**: los lectores HID son 100x mas confiables que ZXing.
  Detecta patron keystroke rapido (< 80ms gap) + Enter.
- **Scan non-blocking**: lock `Cache::lock()->get()` falla inmediato en vez de
  `block(2)` que esperaba hasta 2 segundos.

---

## Fase 2 — Mission Control navegacion

> Resolver: moderador no deberia ir a Filament para abrir MC de otra sesion.

- [ ] MC sidebar/drawer: agenda del evento con sesiones agrupadas por salon/track
- [ ] Click en sesion → genera HMAC token → navega al MC de esa sesion
- [ ] Filtro por track (tabs o select)
- [ ] Indicador visual: cual sesion esta "live" (dot verde)
- [ ] Sesion actual destacada en la lista

---

## Fase 3 — Silent disco UI completa

- [ ] MC: crear sesiones de prueba con silent_disco_group_id para verificar boton
- [ ] MC tab Control: boton "Check Asistencia" funcional con countdown + resultados RT
- [ ] App movil: push notification handler → modal bottom selector sesion → confirm
- [ ] Reporte final: endpoint ya existe, verificar CSV export con datos reales

---

## Fase 4 — App movil: rol staff_checkin

> Alternativa al totem fijo — staff con telefono en la puerta.

- [ ] Nuevo rol: staff_checkin (o permiso adicional en vendedor)
- [ ] Pantalla: selector salon asignado + boton scan QR
- [ ] Resultado: verde/azul/rojo con info del attendee
- [ ] Cola offline: MMKV
- [ ] Sync: batch al reconectar
- [ ] Contador: "12 checkins / 3 checkouts hoy"

---

## Documentos de referencia

| Doc | Contenido |
|-----|-----------|
| `design/checkin/kiosk-eventos-demo.html` | Demo hibrido aprobado (Lumina Noir) |
| `design/checkin/Kiosk Display.html` | Ref 1: editorial, stage scaling, overlays |
| `design/checkin/stitch_*/DESIGN.md` | Ref 2: "The Nocturnal Concierge", Aether Noir |
| `design/checkin/in.png` | Ref check-in overlay |
| `design/checkin/out.png` | Ref checkout overlay |
| `design/checkin/error.png` | Ref error overlay |
| `docs/COMPLETADO.md` | Backend room check-in completo |
