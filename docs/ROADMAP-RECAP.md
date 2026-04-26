# Roadmap Recap Compartible — Post-Evento

> Reemplazo del certificado PDF tradicional. Experiencia tipo Spotify Wrapped pero adaptada a eventos: stats personales en cards visuales scrolleables, imagen final compartible en redes.
>
> **Scope Fase 1 (este roadmap):** App movil (Expo) + backend universal listo para webapp.
> **Scope Fase 2 (futuro, post Web App Bancolombia):** Webapp consume el mismo backend, agrega vista web HTML del recap.
>
> **Estado:** PENDIENTE. Iniciar despues de cerrar BUG-268 (hecho 2026-04-26).
>
> **Filosofia:** orgullo y viralidad. Una persona que termina el evento y recibe su recap deberia querer mostrarlo en redes. Si no causa esa reaccion, no esta listo.

---

## Decisiones tomadas (validadas con usuario 2026-04-26)

- **Formato:** opcion C hibrida — pantalla con scroll vertical de 7 secciones + boton final que descarga UNA imagen compartible (estilo "Year in Review" de Strava, no slides tipo Wrapped)
- **Notificacion universal:** email con CTA. Push extra solo para usuarios con app.
- **URL firmada HMAC:** `eventos.app/recap/{event}?token={hmac}` — token vive 1 año, funciona sin login. Mismo patron que kiosko/MC/display.
- **Generacion imagen:** server-side con Spatie Browsershot (Chromium). Imagen vive en R2 forever. Servidor puede apagarse despues.
- **Solo Lumina Noir** (un evento finaliza, el momento es noir). Lux variant queda para Fase 2.
- **Sonido:** sin musica de fondo (no queremos pelea con accesibilidad ni uso de audio en lugares publicos).
- **Trigger:** `event.status = ended` dispara observer → job batch genera recaps de todos los attendees → email + push.
- **Persistencia:** recap accesible siempre desde "Mis Recaps" en perfil. Se genera UNA vez, frozen forever.

---

## Fase 0: Investigacion y referencias (~2h) — 0/8

> Antes de cualquier linea de codigo, mood board y decisiones visuales.

### 0.1 Buscar referencias web — 0/4
- [ ] Spotify Wrapped 2024/2025 — capturas de cada slide, paleta, tipografia, animaciones
- [ ] Strava Year in Review — formato vertical largo, exportable como imagen
- [ ] Apple Year in Review (Memories) — narrativa visual
- [ ] LinkedIn end-of-year — stats profesionales, tono adulto

### 0.2 Mood board — 0/2
- [ ] Crear `design/recap/REFS/` con screenshots numeradas y notas
- [ ] Documentar `design/recap/DESIGN.md` con paleta + tipografia + animaciones aprobadas

### 0.3 Decisiones de diseno — 0/2
- [ ] Decidir paleta: degradados oscuros (Lumina Noir base) + accent del evento (`primary_color`)
- [ ] Validar fonts: Urbanist body + PlusJakartaSans headlines (existentes)

---

## Fase 1: Backend — Datos del recap (~4h) — 0/12

> Calcular las stats del attendee. Este es el corazon: sin datos correctos, todo lo demas es polish vacio.

### 1.1 Auditoria de datos disponibles — 0/3
- [ ] Verificar tabla `attendee_connections` existe y tiene fecha + count
- [ ] Verificar `wall_post_likes` o equivalente para "post mas reaccionado"
- [ ] Si falta "conexion mas fuerte" (mas mensajes/contactos), decidir: calcular o omitir slide

### 1.2 RecapService (~2h) — 0/5
- [ ] Crear `app/Services/RecapService.php`
- [ ] Metodo `generateForAttendee(Attendee $a, Event $e): array` — devuelve estructura JSON completa
- [ ] Submetodos: `getSessionStats()`, `getTopSession()`, `getConnections()`, `getPoints()`, `getRanking()`, `getTopPhoto()`, `getRewards()`
- [ ] Cache resultado en Redis 24h, key `recap:{event_id}:{attendee_id}`
- [ ] Manejar edge cases: attendee sin foto, sin sesiones, sin conexiones, sin posts (devolver placeholders)

### 1.3 Endpoint API (~30min) — 0/2
- [ ] `GET /api/v1/event/{event}/my-recap` — devuelve JSON con todas las stats
- [ ] Auth: solo el propio attendee (policy `RecapPolicy@view`)

### 1.4 Tests (~1.5h) — 0/2
- [ ] `tests/Feature/Recap/RecapServiceTest.php` — Pest, ~10 tests cubriendo cada metodo + edge cases con seed minimal
- [ ] `tests/Feature/Recap/RecapApiTest.php` — endpoint, auth, cache, attendee no autorizado

---

## Fase 2: Backend — Generacion imagen + storage (~4h) — 0/10

> La imagen final compartible. Server-side, una sola pieza visual de alta calidad.

### 2.1 Setup Browsershot — 0/2
- [ ] `composer require spatie/browsershot` (verificar Puppeteer/Chromium ya instalado en VPS)
- [ ] Test simple: render HTML "Hola mundo" a PNG en local

### 2.2 Template HTML compartible (~2h) — 0/4
- [ ] Crear `resources/views/recap/share-card.blade.php` — vertical 1080x1920 (Instagram Story)
- [ ] Composicion: foto attendee + nombre + nombre evento + 4-5 stats principales + branding (`primary_color`, `logo_url`)
- [ ] Variante secundaria 1080x1080 (Instagram feed, LinkedIn) — pero **solo generamos la 1080x1920 en Fase 1**, la cuadrada queda para Fase 2 si usuario lo pide
- [ ] Tipografia: Inter o PlusJakartaSans bundled como `@font-face` en el template

### 2.3 Job generacion + upload (~1h) — 0/2
- [ ] `app/Jobs/GenerateRecapImageJob.php` — recibe attendee_id + event_id, llama RecapService, render HTML, Browsershot a PNG, sube a R2 (`recaps/{event_id}/{attendee_id}.png`)
- [ ] Guarda URL en `attendees.recap_image_url`, datos en `attendees.recap_data` (JSON)

### 2.4 Tests (~1h) — 0/2
- [ ] `tests/Feature/Recap/GenerateRecapImageJobTest.php` — mock Browsershot, verifica subida R2 (Storage::fake), guarda URL
- [ ] Test de regresion visual manual: comparar PNG generado contra mockup aprobado en Fase 0

---

## Fase 3: Backend — Email + URL firmada + trigger (~4h) — 0/13

> La distribucion. Email es el canal universal — garantiza que el 100% de attendees recibe el aviso.

### 3.1 Migration — 0/3
- [ ] Migration agregar a `attendees`: `recap_data` (JSON), `recap_image_url` (string), `recap_token` (string), `recap_generated_at` (timestamp)
- [ ] Token: HMAC-SHA256 firmando `event_id|attendee_id|expires_at`, expires 1 año
- [ ] Index sobre `recap_token` para lookups O(1)

### 3.2 RecapMail (~1h) — 0/3
- [ ] Crear `app/Mail/RecapReadyMail.php` con view `emails.recap-ready`
- [ ] Template HTML branded con `primary_color`, logo, nombre evento, stats teaser, CTA grande "Ver mi Recap"
- [ ] CTA URL: `eventos.app/recap/{event_id}?token={recap_token}`

### 3.3 EmailTemplate type — 0/2
- [ ] Agregar `recap_ready` al enum de `EmailTemplate.type`
- [ ] Filament EmailTemplateResource: agregar al Select (label "Recap post-evento")

### 3.4 Endpoint URL publica firmada — 0/2
- [ ] `GET /recap/{event_id}` — controller valida token HMAC, redirect a deeplink app (`eventos://recap/{event_id}?token={...}`) o fallback web "Descarga la app"
- [ ] Fase 2 (webapp): mismo endpoint sirve vista HTML del recap directamente

### 3.5 Observer + orquestador (~1h) — 0/3
- [ ] `EventObserver::updated()` detecta cambio a `status=ended` → dispatch `GenerateRecapsForEventJob`
- [ ] `GenerateRecapsForEventJob` itera attendees del evento, encadena `GenerateRecapImageJob` para cada uno (chunk 50, throttle queue)
- [ ] Al completar imagen: dispatch `SendRecapReadyEmailJob` + `SendRecapPushJob` (este ultimo solo si attendee tiene `expo_push_token`)

### 3.6 Tests (~30min) — 0/3
- [ ] `tests/Feature/Recap/EventEndedTriggerTest.php` — cambio a status=ended dispara job
- [ ] `tests/Feature/Recap/RecapMailTest.php` — Mail::fake, verifica template + variables + recipient
- [ ] `tests/Feature/Recap/RecapTokenTest.php` — token valido, expirado, manipulado

---

## Fase 4: App movil — Pantalla recap nativa (~7h) — 0/15

> Aqui se siente el orgullo. Scroll vertical premium, animaciones suaves, datos personales reales.

### 4.1 Hook useRecap (~30min) — 0/2
- [ ] `hooks/useRecap.ts` — TanStack Query, fetch `/event/{id}/my-recap`, cache MMKV 24h
- [ ] Tipos TypeScript completos para la estructura del recap

### 4.2 RecapScreen — estructura base (~1h) — 0/3
- [ ] `app/(app)/recap/[eventId].tsx` — pantalla full screen, ScrollView vertical
- [ ] Header transparente con close button + share button sticky arriba
- [ ] Background degradado oscuro animado (similar onboarding `mesh` pero adaptado)

### 4.3 Seccion Hero (~1h) — 0/2
- [ ] Foto attendee circular grande (140dp) + nombre + nombre evento + fecha
- [ ] Animacion entrada: FadeInUp + scale, parallax suave al scroll

### 4.4 Seccion Tiempo en evento (~30min) — 0/1
- [ ] Numero grande de horas + barra horizontal con sesiones favoritas marcadas

### 4.5 Seccion Top sesion (~30min) — 0/1
- [ ] Card con titulo sesion + speaker + tu rating en estrellas + thumbnail si existe

### 4.6 Seccion Conexiones (~30min) — 0/1
- [ ] Numero conexiones + foto de conexion mas fuerte (si existe) + "Conociste a X personas"

### 4.7 Seccion Tu huella (~30min) — 0/1
- [ ] Puntos totales (counter animado) + posicion ranking ("Top 5%" o "Posicion 23/450")

### 4.8 Seccion Mejor momento (~30min) — 0/1
- [ ] Tu foto mas liked O tu post con mas reacciones — fullscreen card con likes count

### 4.9 Seccion Card final + boton compartir (~1h) — 0/2
- [ ] Composicion resumen visual (igual al PNG generado por backend)
- [ ] Boton primary "Compartir mi recap" — invoca `react-native-share` con la URL R2

### 4.10 Animaciones globales (~1.5h) — 0/1
- [ ] Reanimated: cada seccion entra al entrar al viewport (Intersection-style con `useAnimatedScrollHandler`)

---

## Fase 5: App movil — Distribucion (~2.5h) — 0/8

> Recibir, abrir, compartir. El loop de viralidad.

### 5.1 Push handler (~30min) — 0/2
- [ ] Tipo `recap_ready` en handler de push notifications
- [ ] Tap en notificacion → navega a `/recap/{eventId}` con token

### 5.2 Deep link handler (~30min) — 0/2
- [ ] `eventos://recap/{eventId}?token={...}` registrado en `app.json`
- [ ] Validar token cliente-side antes de fetch (verificar firma local seria ideal pero podemos hacer fallback al backend)

### 5.3 Banner Home post-evento (~30min) — 0/1
- [ ] Si evento finalizado y attendee tiene `recap_image_url`, banner sticky en Home: "Tu Recap te espera" + CTA → `/recap/{id}`

### 5.4 Pantalla "Mis Recaps" (~1h) — 0/3
- [ ] `app/(app)/profile/recaps.tsx` — lista de eventos pasados con recap disponible
- [ ] Cada card: nombre evento + fecha + thumbnail recap + tap navega
- [ ] Endpoint `GET /api/v1/me/recaps` devuelve lista paginada

---

## Fase 6: QA visual + Polish + Edge cases (~3h) — 0/9

> Reliability first: 3 features perfectos > 10 con fallo. Si esto no se ve impecable, no se siente como orgullo.

### 6.1 Edge cases backend — 0/3
- [ ] Attendee sin foto: usar placeholder con iniciales sobre `primary_color`
- [ ] Attendee con 0 sesiones: omitir esa seccion graciosamente, no mostrar "0 horas"
- [ ] Evento sin sesiones rated: omitir slide top sesion

### 6.2 Edge cases frontend — 0/2
- [ ] Imagen R2 no carga (offline): fallback a generacion local de card simple con datos del JSON
- [ ] Token expirado/manipulado: pantalla error amistosa "Link expirado, inicia sesion para verlo"

### 6.3 Responsive — 0/2
- [ ] 360dp (ZTE) — todos los textos y cards legibles
- [ ] 411dp (Pixel) — sin desperdicio de espacio

### 6.4 Tests (~1h) — 0/2
- [ ] Test E2E manual: crear evento demo, simular attendees, dispara `event.status=ended`, verificar email recibido + recap accesible
- [ ] Tests RN basicos: hook useRecap mockeado, RecapScreen renderiza con datos fake

---

## Estimacion total

| Fase                                | Horas    | Bloqueante         |
| ----------------------------------- | -------- | ------------------ |
| 0. Refs + decisiones diseno         | 2h       | Si — no skipear    |
| 1. Backend datos                    | 4h       | Si                 |
| 2. Backend imagen + storage         | 4h       | Si                 |
| 3. Backend email + URL + trigger    | 4h       | Si                 |
| 4. App pantalla recap               | 7h       | Si                 |
| 5. App distribucion                 | 2.5h     | Si                 |
| 6. QA + polish                      | 3h       | Si                 |
| **Total**                           | **~26h** | **3-4 dias work** |

---

## Datos backend requeridos — auditoria pre-Fase 1

Antes de comenzar Fase 1, validar contra el schema actual:

| Stat                     | Tabla / fuente                        | Estado a verificar                     |
| ------------------------ | ------------------------------------- | -------------------------------------- |
| Sesiones asistidas       | `session_attendances`                 | OK — existe                            |
| Horas en evento          | calculado de attendances              | OK — derivado                          |
| Top sesion (mi rating)   | `session_ratings`                     | OK — existe                            |
| Sesiones favoritas       | `attendee_session_favorites`          | OK — existe                            |
| Conexiones               | `attendee_connections` o equivalente  | **VERIFICAR** antes de Fase 1.1        |
| Conexion mas fuerte      | mensajes chat / contactos             | **VERIFICAR** — puede omitirse         |
| Puntos totales           | `attendee_points` / `point_ledger`    | OK — existe                            |
| Ranking                  | calculo agregado en RecapService      | OK — calculable                        |
| Foto mas liked           | `event_photos.likes_count`            | OK — existe                            |
| Premios ganados          | `reward_redemptions`                  | OK — existe                            |
| Encuestas respondidas    | `live_poll_votes`                     | OK — existe                            |
| Posts en wall            | `wall_posts`                          | OK — existe                            |

---

## Decisiones tecnicas archivadas

- **NO Skia/RN-Skia client-side** para imagen final — typography difiere iOS/Android, fonts custom dolorosos. Server-side Browsershot es mas confiable.
- **NO musica de fondo** — distrae, conflicto con accesibilidad y uso publico.
- **NO slides tipo Spotify Wrapped** — opcion B descartada, complejidad 3x sin justificar viralidad incremental. Scroll vertical largo cumple igual.
- **Imagen final 1080x1920 Instagram Story** prioritaria. Cuadrada 1080x1080 LinkedIn queda para Fase 2 si cliente lo pide.
- **Cookie/cache MMKV en app** — recap se descarga una vez, queda offline.
- **R2 storage forever** — costo despreciable (~$0.075/mes para 10 eventos × 1000 attendees), sin servidor activo necesario.

---

## Riesgos

1. **Browsershot en VPS Linux**: Chromium consume RAM. En el batch de 1000 attendees x 10s = 2-3h, controlable con queue worker dedicado y throttle.
2. **Datos faltantes en eventos viejos**: si activamos esta feature post-deploy y hay eventos historicos sin attendances tracked, el recap saldra pobre. Mitigacion: solo dispara recap para eventos que finalicen DESPUES del deploy de esta feature.
3. **Tipografia en HTML server-side**: si Urbanist/PlusJakartaSans no estan bundled correctamente en el template, font fallback feo. Mitigacion: `@font-face` con WOFF2 en `public/fonts/`.
4. **R2 caida**: imagen no accesible. Mitigacion: backend mantiene `recap_data` JSON, app puede regenerar version simplificada local con react-native-view-shot como fallback.

---

## Que NO entra en Fase 1 (queda para Fase 2 webapp)

- Vista web HTML del recap (eventos.app/recap/{id}) — solo deeplink/redirect en Fase 1
- Variante imagen cuadrada 1080x1080 LinkedIn
- Variante imagen 16:9 Twitter
- Lumina Lux variant
- Personalizar paleta y composicion por evento (Fase 1 usa solo `primary_color` + estructura fija)
- Multilenguaje (Fase 1 solo espanol; ingles puede agregarse despues)

---

## Referencias

- Spotify Wrapped: https://www.spotify.com/wrapped/
- Strava Year in Review: https://blog.strava.com/year-in-sport/
- Apple Memories: feature en Photos.app iOS
- Spatie Browsershot: https://github.com/spatie/browsershot
- React Native Share: https://github.com/react-native-share/react-native-share
- React Native View Shot: https://github.com/gre/react-native-view-shot
- HMAC tokens en EventOS: ver `app/Services/HmacTokenService.php` (existe ya, se reutiliza)
