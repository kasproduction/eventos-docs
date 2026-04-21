# Roadmap — Kiosko Room Check-in

> Interfaz de los totems (iPads) en las puertas de los salones.
> Backend 100% funcional. Este roadmap es solo la interfaz.
> Refs: `design/checkin/Kiosk Display.html` + `design/checkin/stitch_*/`
> Design system: DESIGN.md "The Nocturnal Concierge" (Aether Noir)

---

## Estado actual

Backend completo:
- POST /rooms/scan, /scan/batch, GET /ping (schedule completo)
- Auto-checkout otro salon, debounce, offline batch, heartbeat
- 17 tests, 216+ assertions, stress test 50 personas 17 escenarios

Kiosko React funcional pero **generico** — necesita rediseno premium.

---

## Fase 0 — Demo HTML hibrido (standalone)

> Objetivo: aprobar el diseño antes de implementar en React.
> Archivo: `design/checkin/kiosk-eventos-demo.html`

### Pantalla principal (idle + scanner activo)
- [ ] Stage scaling: 1920x1080 landscape / 1080x1920 portrait (auto-detect o toggle)
- [ ] Header: `ROOM · SALA` eyebrow + nombre salon + reloj en vivo + fecha
- [ ] Indicador scanner: pill con breathing dot emerald + "READY" (sutil, no protagonista)
- [ ] Hero sesion EN VIVO:
  - Titulo GIGANTE (88px landscape / 92px portrait, Plus Jakarta Sans, -0.035em)
  - Badge "LIVE NOW" pill con dot pulse + borde emerald
  - Track tag + idioma
  - Meta grid: Time (14:00 — 14:45), Format (Keynote · 45 min)
  - Progress bar: track 2px + fill gradient emerald + labels (Started / X of Y min)
- [ ] Speaker: foto circular 148px (o iniciales fallback) + nombre 36px + cargo + empresa
- [ ] Siguiente sesion: seccion separada, titulo 30px ink-70, speaker, hora, "In X min"
- [ ] Timeline horizontal: slots con hora + nombre, estados past(opacity 0.35)/live(emerald bg)/upcoming
- [ ] Indicador NOW: linea vertical emerald con dot en la posicion proporcional al tiempo
- [ ] Footer: nombre evento + "Kiosk 01 · Salon" + "Synced HH:MM"
- [ ] Sin bordes visibles (No-Line Rule) — depth via tonal layering
- [ ] Sin iconos — tipografia como unico recurso

### Overlay check-in (4 segundos)
- [ ] Backdrop blur 8px + fondo radial gradient emerald sobre noir
- [ ] Card: border ghost (10% opacity), bg glassmorphic
- [ ] Eyebrow: dot emerald glow + "CHECK-IN · ENTRADA" JetBrains Mono
- [ ] Greeting: "Bienvenido," 44px weight 300
- [ ] Nombre: **104px weight 700** (el nombre DOMINA la pantalla)
- [ ] Sesion: titulo de la charla actual, 22px ink-50
- [ ] Stats grid: Entry time, Badge #, nivel (con labels uppercase mono)
- [ ] Timer: "Closing in Xs" arriba derecha, auto-regresa

### Overlay checkout
- [ ] Radial gradient azul sobre noir
- [ ] "GOODBYE" + "Hasta luego," 44px light
- [ ] Nombre 104px bold
- [ ] Sesion + "You stayed X minutes"
- [ ] Stats: Duration (X min), Arrived (HH:MM), Left (HH:MM)

### Overlay error
- [ ] Radial gradient rojo dramatico
- [ ] "Scan unrecognized" 44px
- [ ] "Badge not found" o mensaje especifico 104px
- [ ] Mensaje: "Please visit the registration desk"
- [ ] Stats: Code (ERR_XXX), Time (HH:MM)

### Tokens (del DESIGN.md Aether Noir adaptados a EventOS)
```
--bg: #0c0c16
--bg-2: #12121f
--ink: #ffffff
--ink-70: rgba(255,255,255,0.70)
--ink-50: rgba(255,255,255,0.50)
--ink-35: rgba(255,255,255,0.35)
--ink-20: rgba(255,255,255,0.20)
--ink-10: rgba(255,255,255,0.10)
--border: rgba(255,255,255,0.06)
--emerald: oklch(0.78 0.17 155)
--emerald-soft: oklch(0.78 0.17 155 / 0.12)
--green-tint: oklch(0.42 0.13 155)
--blue-tint: oklch(0.42 0.13 245)
--red-tint: oklch(0.45 0.17 25)
Font headlines: Plus Jakarta Sans
Font body: Urbanist
Font mono/labels: JetBrains Mono
```

---

## Fase 1 — Implementar en React (Kiosko)

> Reemplazar RoomApp.tsx con el diseño aprobado del demo.

- [ ] Portar CSS del demo a inline styles (o CSS module)
- [ ] Conectar al API: ping cada 10s → schedule completo → render dinamico
- [ ] Scanner QR oculto (camara invisible, deteccion automatica)
- [ ] Cooldown 5s entre scans
- [ ] Overlays animados con transition opacity 260ms
- [ ] Timer countdown en overlay (4s)
- [ ] Progress bar actualiza en tiempo real (% avance sesion)
- [ ] Timeline NOW indicator se mueve cada minuto
- [ ] Reloj en vivo (tick cada segundo)
- [ ] Socket listeners: session:started/ended/cancelled/agenda:updated → re-render
- [ ] Offline queue: IndexedDB + batch sync (ya implementado, mantener)
- [ ] Indicador offline: dot rojo + texto (minimo, no protagonista)

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
- [ ] Cola offline: MMKV (solido, no IndexedDB)
- [ ] Sync: batch al reconectar
- [ ] Contador: "12 checkins / 3 checkouts hoy"

---

## Documentos de referencia

| Doc | Contenido |
|-----|-----------|
| `design/checkin/Kiosk Display.html` | Ref 1: editorial, stage scaling, timeline, overlays |
| `design/checkin/stitch_*/DESIGN.md` | Ref 2: "The Nocturnal Concierge", tokens, reglas No-Line/No-Icon |
| `design/checkin/in.png` | Ref 2: overlay check-in |
| `design/checkin/out.png` | Ref 2: overlay checkout |
| `design/checkin/error.png` | Ref 2: overlay error |
| `design/checkin/stitch_*/kiosk_display_portrait/screen.png` | Ref 2: pantalla principal portrait |
| `design/checkin/stitch_*/scan_success_welcome/screen.png` | Ref 2: welcome scan |
| `design/checkin/stitch_*/scan_success_farewell/screen.png` | Ref 2: farewell scan |
| `docs/COMPLETADO.md` | Backend room check-in completo (seccion 2026-04-20c) |
