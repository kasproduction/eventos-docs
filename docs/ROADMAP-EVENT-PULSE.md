# Roadmap — Event Pulse (Dashboard Live para Cliente)

> Dashboard visual standalone que el organizador mira en una pantalla/TV.
> Canvas 1920x1080. NO es admin, NO es Filament, NO es Mission Control.
> El organizador solo mira, no controla. Es el acuario del evento.
> Prioridad: P1 (diferenciador — ningun competidor tiene esto)
> Diseno aprobado: `design/event-pulse-FINAL.html`
> Estado: **EN PROGRESO** — backend completo, socket parcial, frontend con bugs RT
> Actualizado: 2026-04-23

---

## Resumen sesion 2026-04-23

### COMPLETADO

**Backend (20 tests, 72 assertions):**
- PulseController: 8 endpoints, todos OK
- Rooms endpoint reescrito: agrupa por room_id (1 card por room, no 1 por sesion)
- Rooms incluye `people[]` (avatares), `session_id`, presencial/virtual separados
- Bootstrap: `event.id` incluido, `messages` cuenta solo `status='published'`
- Checkins: filtra attendees con user valido
- PulseSeeder: PointsLog, sesiones live, pulse_token auto
- Migration: `type` enum (presencial/virtual) en `room_attendee_states`
- Endpoint `/api/v1/pulse/validate` para socket auth
- Endpoint `/api/v1/internal/room/virtual` para presencia virtual
- 20 tests cubriendo 8/8 endpoints + rooms agrupados + people + next session

**Socket server:**
- Auth `pulse_token`: tokens `ep_*` validados via Laravel endpoint
- `broadcastAudience` emite `session:audience` al event room (no solo session room)
- `broadcastAudience` incluye `viewers[]` con nombre (para burbujas)
- `broadcastAudience` acepta `fallbackEventId` para emitir cuando room queda vacio
- Connection handler maneja pulse users (connKey negativo, sin attendeeId)

**Frontend:**
- Fuentes locales (PlusJakartaSans + Urbanist, 10 woff2, sin CDN)
- JetBrains Mono eliminada, `--f-mono` usa PlusJakartaSans
- Responsive: stage flex center, canvas scale Math.min, letterboxing
- Moments reactivados: 5 tipos (checkin, salon, rating, points, social) con data del API
- Moments suaves: velo blanco 65% + backdrop-filter blur 8px, fade 5.5s, ambient dimming opacity 0.06 + blur 12px
- Intervalo moments: 15s (no 6s)
- Socket.IO client local (sin CDN)
- Socket conecta con pulse_token, join:event, recibe eventos RT
- `checkin:update` → actualiza counter + invalida seccion + dispara moment
- `data:invalidate` → invalida seccion afectada + re-fetch stat
- `session:audience` → trackea virtual viewers por session
- `wall:post` → actualiza counter + moment
- Secciones se re-renderizan en vivo si estan activas (crossfade)
- Charlas: updateCharlasLive() — numero anima (scale), barra transiciona, personas fade rebuild
- Cards compactas (align-content: start, no height: 100%)
- Bootstrap refresh cada 5 min como fallback

### BUGS RESUELTOS (EP-4, EP-5, EP-6, EP-7, EP-8, EP-10, EP-11, EP-13)

- **EP-11 RESUELTO:** Virtual viewers ahora funcionan via `Rooms.pulse()`. `broadcastAudience` emite a pulse room aislado (1-2 sockets, no 10K). MC usa `join:session` y el count sube/baja en Pulse en tiempo real.
- **EP-13 RESUELTO:** Sesiones fantasma "live" — sesiones que el moderador nunca cerro (actual_end_at=null) se mostraban como live para siempre. Fix: PulseController verifica que `publicEnd()` no haya pasado.

### BUGS PENDIENTES

#### BUG-EP-1: Responsive parcial
Letterboxing funciona pero no verificado en todas las resoluciones (1280x720, 2560x1440, tablet).

#### BUG-EP-3: Tema Noir no implementado
Solo tema Lux. Falta tokens duales + clase CSS segun `default_theme`.

#### BUG-EP-9: Weather no implementado
Decidir: quitar o campo configurable en Filament.

#### BUG-EP-12: Espacio vacio en cards Charlas
Avatares mas grandes o mas info para llenar el espacio.

---

## Lo que falta por implementar (sesiones futuras)

### Prioridad 1 — Visual

1. **Tema Noir** — tokens duales en tokens.css, clase CSS segun bootstrap `default_theme`
2. **Responsive QA** — verificar 1280x720, 1366x768, 1920x1080, 2560x1440
3. **Charlas cards** — reducir espacio vacio, avatares mas grandes
4. **Weather** — campo configurable en Filament o quitar

### Prioridad 2 — Produccion

5. **Error handling** — estado offline si API/socket falla, reintentar con indicador visual
6. **Moments con data socket real** — moments de rating/leaderboard deberian dispararse con data:invalidate, no solo con fetch inicial
7. **Test E2E con PulseSimulate** — probar flujo completo con el comando artisan

---

## Archivos modificados en esta sesion

### Backend (eventos-backend)
| Archivo | Cambio |
|---------|--------|
| `app/Http/Controllers/PulseController.php` | Rooms agrupado por room, people[], session_id, presencial/virtual, messages=published, checkins whereHas user |
| `database/seeders/PulseSeeder.php` | PointsLog, sesiones live, pulse_token, status published |
| `database/migrations/2026_04_23_220056_add_type_to_room_attendee_states.php` | Campo `type` enum presencial/virtual |
| `app/Models/RoomAttendeeState.php` | `type` en fillable |
| `routes/api.php` | Endpoint pulse/validate, internal/room/virtual |
| `tests/Feature/Pulse/PulseTest.php` | 20 tests (rooms groups, people, next session, social published, ratings, bootstrap messages) |
| `public/event-pulse/` | Frontend completo (ver abajo) |

### Socket server (eventos-socket)
| Archivo | Cambio |
|---------|--------|
| `src/auth.ts` | `validatePulseToken()` — auth para tokens ep_* |
| `src/index.ts` | Import validatePulseToken, middleware acepta ep_*, connKey para pulse, fallbackEventId en disconnect |
| `src/chat.ts` | `broadcastAudience` emite al event room, incluye viewers[], acepta fallbackEventId |

### Frontend (eventos-backend/public/event-pulse/)
| Archivo | Cambio |
|---------|--------|
| `index.html` | Fuentes locales, socket.io.min.js, socket.js, version bust |
| `css/tokens.css` | --f-mono = PlusJakartaSans (no JetBrains) |
| `css/layout.css` | Stage flex center, canvas relative, ambient dimming fuerte (opacity 0.06, blur 12px) |
| `css/sections.css` | Cards compactas (align-content start), room-people margin, room-av.virtual borde azul |
| `css/moments.css` | Velo blanco 65% + backdrop-filter, animacion 5.5s suave, m-avatar-xl wrapper |
| `js/app.js` | Scale limpio Math.min |
| `js/data.js` | Sin cambios (ya estaba limpio) |
| `js/sections.js` | invalidateSectionCache con live re-render, updateCharlasLive (fade rebuild), charlas sin cache, buildCharlas con data-room-id y session_id |
| `js/moments.js` | Reactivado con data API: 5 tipos, weighted random, 15s intervalo |
| `js/socket.js` | NUEVO: conecta socket, checkin:update, data:invalidate, wall:post, session:audience, bootstrap refresh 5min |
| `js/counters.js` | Vacio (datos via socket) |
| `fonts/` | NUEVO: 10 woff2 + fonts.css (PlusJakartaSans + Urbanist) |

---

## Principios de diseno (NO NEGOCIABLES)

1. **Tipografia**: PlusJakartaSans (display) + Urbanist (body). Servir localmente. NUNCA Inter, Space Grotesk, ni JetBrains Mono.
2. **Tema**: Noir y Lux. El tema se aplica segun el `default_theme` del evento.
3. **Avatares grandes**: minimo 48px para listas, 100px+ para protagonistas. NUNCA burbujitas de 28px.
4. **Sin data falsa**: todo viene del API o socket. Si no hay datos, mostrar estado vacio elegante.
5. **Sin counters simulados**: los numeros solo cambian cuando llega un socket event real.
6. **Sin brand-mark**: no hay icono cuadrado negro. Solo texto del nombre del evento.
7. **Responsive**: letterboxing, verificar multiples resoluciones.
8. **Sin collage de fotos flotantes**: eliminado por rendimiento.
9. **Sin auto-rotacion de secciones**: el cliente navega manual con los pills.
10. **Zero polling**: todo via socket. RefetchOnWindowFocus como unico mecanismo de refresco pasivo.

---

## API Endpoints

Base: `GET /api/v1/pulse/{slug}/{endpoint}?token={pulse_token}`

| Endpoint | Respuesta |
|----------|-----------|
| `bootstrap` | event{id,name,slug,venue,dates,color,logo}, sections[], stats{checkins,online,leads,connections,ratings,messages,points} |
| `rooms` | rooms[] agrupados por room (1 por room, session live o proxima, people[], session_id, presencial, virtual) |
| `checkins` | recent[] (con user valido), total, registered, timeline{hour:count} |
| `leads` | sponsors[] (sponsor, logo, total, recent[]) |
| `connections` | total, accept_rate, recent[] |
| `social` | posts[] (solo status=published) |
| `leaderboard` | top[] (name, photo, points, last_action), total_points |
| `ratings` | sessions[] (title, score, count, speaker), event_avg |

## Socket Events (Pulse escucha)

| Evento | Fuente | Que hace Pulse |
|--------|--------|---------------|
| `checkin:update` | Laravel POST /internal/checkin | Actualiza counter check-ins, invalida seccion, dispara moment |
| `data:invalidate` | Laravel observers | Invalida cache de la seccion afectada, re-fetch stat |
| `wall:post` | Laravel POST /internal/broadcast | Actualiza counter social, dispara moment |
| `session:audience` | Socket broadcastAudience | Trackea virtual viewers por session, invalida charlas |
| `session:config_updated` | Socket | Invalida charlas |
