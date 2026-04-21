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

### Pendiente — Pre-produccion (critico)
- [x] ~~Sonido de confirmacion~~ — descartado: ruido ambiental en eventos hace inutil cualquier beep
- [x] **Contador check-ins** — "142 check-ins today" en footer emerald. Backend cuenta en ping, +1 optimista local tras scan exitoso
- [x] **Test de carga 5000 attendees** — 682 KB full / 99 bytes delta / 584ms Windows (est. ~150ms Linux). Sin problema de memoria

### Pendiente — Pulido
- [ ] **Scan endpoint < 100ms** — en produccion Linux sera ~50ms, verificar con VPS real
- [x] Indicador visual de "cache cargado" — footer muestra "Online · 1,086 cached · 23:45"
- [x] Portrait: bottom zone con clase `.compact` cuando no hay next session
- [x] Overlay: foto del attendee desde cache (avatar_url en manifest + lookupAvatar)
- [x] Footer: "Online · X cached · HH:MM" en landscape y portrait

### Decisiones de arquitectura
- **Cache solo nombres** (Opcion 2): no predice checkin/checkout, API decide.
  Sin riesgo de estado inconsistente entre totems. Cache es solo para velocidad visual.
- **USB scanner > camara**: los lectores HID son 100x mas confiables que ZXing.
  Detecta patron keystroke rapido (< 80ms gap) + Enter.
- **Scan non-blocking**: lock `Cache::lock()->get()` falla inmediato en vez de
  `block(2)` que esperaba hasta 2 segundos.

---

## Fase 2 — Mission Control navegacion — COMPLETADA

> Resolver: moderador no deberia ir a Filament para abrir MC de otra sesion.

- [x] MC sidebar agenda con sesiones agrupadas por salon (multi-room headers)
- [x] Click en sesion → navega con HMAC pre-generado (zero API calls)
- [x] Indicador visual: dot verde animado en sesiones "live"
- [x] Sesion actual: borde left blanco + font-weight bold
- [x] Sesiones pasadas: opacity reducida
- [x] Hover state en links (background tonal shift)
- [x] Agrupacion por dia (multi-dia: "lun 21 abr", "mar 22 abr")
- [x] Dentro de cada dia, agrupacion por room (headers separadores)
- [x] Filtro por track: pills compactas (All / Platform / Workshop / etc.)
- [x] Backend inyecta track + day en all_sessions
- [x] Max-height 240px con scroll, auto-scroll a sesion actual
- [x] Fallback: si no hay all_sessions inyectadas, carga desde API

---

## Fase 3 — Silent disco UI — EN PROGRESO

### Completado
- [x] MC tab Asistencia: tab dedicado (solo visible si silent_disco_group_id)
- [x] MC: boton "Iniciar check" con countdown RT + contadores por sesion
- [x] MC: historial permanente con barras proporcionales (scroll, ultimos 20)
- [x] MC: resume countdown al recargar pagina (GET /active)
- [x] MC: estilos Noir completos (confirm modal, botones, status, countdown)
- [x] MC: zero emojis — Material Icons en confirm modal
- [x] MC: socket join:event fix (fallback si ya conectado)
- [x] App: `AttendanceCheckModal` 85% pantalla, countdown RT, selector sesion
- [x] App: socket `attendance:check` → modal automatico
- [x] App: foreground resume → consulta pending check
- [x] App: `attendanceApi` — pending (con event_id + room_id), confirm
- [x] App: `useAttendanceCheckStore` zustand global
- [x] Backend: `/internal/broadcast` endpoint generico en socket server
- [x] Backend: GET /active, GET /history, pending incluye room_id
- [x] Backend: EventSessionResource incluye room_id + silent_disco_group_id
- [x] Tests: 23 tests, 96 assertions (mass confirm 20 users, history, active, expired)

### Pulido completado
- [x] MC: personas en salon ("21 personas en el salon") antes de disparar
- [x] App: filtro por room — verifica via API pending antes de mostrar modal
- [x] MC: TTL configurable — pills 30s/60s/90s/2min, se envia al backend
- [x] Backend: trigger acepta `ttl` opcional (15-300s)
- [x] MC: barra de progreso visual en countdown
- [x] MC: porcentaje + ganador (verde) en contadores RT y historial
- [x] MC: historial con % por sesion y barra ganadora resaltada
- [x] App: doble vibracion al recibir check (Warning + Heavy)
- [x] App: modal no se cierra sin responder (toast advertencia)
- [x] MC: boton "Exportar CSV" — descarga asistencia por sesion
- [x] MC: socket join:event fix (fallback si ya conectado)
- [x] Backend: fix avatar_url → photo_url en report endpoint
- [ ] Push notification — pendiente verificar con dev build real

---

## Fase 4 — App movil: rol staff_checkin — COMPLETADA

> Alternativa al totem fijo — staff con telefono en la puerta.
> Staff/admin escanea QR de un attendee → crea asignacion pendiente → target aprueba → UI cambia RT.

### Backend
- [x] Nuevo rol `staff_checkin` en enum attendees
- [x] Tabla `staff_room_assignments` (attendee_id, room_id, assigned_by, is_active, accepted_at)
- [x] `POST /staff-checkin/assign` — crea asignacion **pendiente** + socket al target
- [x] `POST /staff-checkin/accept-assignment` — target acepta, activa role
- [x] `POST /staff-checkin/reject-assignment` — target rechaza, elimina assignment
- [x] `POST /staff-checkin/reassign` — mover staff de un salon a otro (inmediato)
- [x] `POST /staff-checkin/unassign` — remover staff (revierte rol si no quedan)
- [x] `POST /staff-checkin/scan` — staff escanea QR en puerta (Bearer auth, reutiliza RoomCheckinService)
- [x] `GET /staff-checkin/my-rooms` — rooms asignados al staff con stats
- [x] `GET /staff-checkin/rooms` — ve todos los rooms con staff asignados
- [x] `GET /staff-checkin/pending-assignment` — consulta asignacion pendiente
- [x] `canAssignStaff()` permite admin + staff_checkin
- [x] `isBanned()` eliminado de processScan (presencial only)
- [x] Cache requireStaff + assignment check 60s
- [x] HMAC fix: resolveAttendeeFromQr delega a CheckinService
- [x] Socket: assignment_request, assignment_accepted, room_changed, room_unassigned
- [x] 10 tests, 35 assertions

### App movil
- [x] Hub tipo Mi Stand: hero room, stats, FAB scanner flotante, soporte Lux
- [x] StaffHappeningNow card limpia (icon + titulo + pill + "Abrir >")
- [x] `assign-staff.tsx` — scanner QR + room picker + resultado "Solicitud enviada"
- [x] `RoomAssignmentModal` — modal global aprobacion via socket RT (aceptar/rechazar)
- [x] Gestion staff: seccion dedicada "Gestionar staff (N)" con lista, mover, remover
- [x] Cambiar salon: pantalla selector para reasignar staff a otro room
- [x] BottomSheet scan result theme-aware (Noir/Lux)
- [x] Toasts en todo el feature: scan, assign, accept, reject, remove, move
- [x] Zero polling: invalidacion via socket + mutaciones locales
- [x] ModuleMenu: centrado impar para modulos extra

### Pendiente
- [ ] Cola offline: MMKV + batch sync
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
