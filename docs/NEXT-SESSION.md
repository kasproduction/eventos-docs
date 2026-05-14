# Siguiente sesion — punto de entrada unico

> **Como usar este archivo:** al arrancar nueva sesion, simplemente decime
> **"siguiente"** o **"next"** y leo este archivo + retomo donde quedamos.
> Yo lo actualizo al cierre de cada sesion (paso del workflow DaVinci).

---

## Ultima sesion

**Fecha:** 2026-05-13 → 2026-05-14
**Que se hizo:** Modulo W.6 **Social Networking unificado** (combinacion de Networking + Wall en un solo modulo de la webapp — decision 2026-05-13). Demos HTML iterados (v1A/v1B/v2-davinci con foto vertical, comentarios inline, microinteracciones, dark+lux). Implementacion React end-to-end en eventos-web: 4 vistas (Feed/Personas/Solicitudes/Mis posts), composer con foto multipart, comentarios inline Instagram-style, optimistic UI, 14 vitest + 6 E2E pasando. Audit Expo comparado al final → gaps importantes detectados (avatar beam fallback, perfil attendee drawer, sugeridos cards grandes, tab Contactos con vcf, bloqueados, search server-side) — quedan para proxima sesion.

### Webapp (eventos-web, main) — 1 commit pusheado

**`e2c9b4b feat(W.6): modulo Social Networking — feed + directorio + solicitudes + mis posts`**

Implementado fases 1-5:
- Tipos TS alineados al shape REAL del backend (`author: string` plano, `body`, `liked`, `author_photo`, `is_mine`) — descubierto leyendo `WallController::index`. Antes asumi shape anidado y posts decian "Asistente" en todo
- Server fetchers (`lib/social.ts`): wall, directory, suggested, my-contacts, received/sent requests, attendee profile. Fallback `[]` si no hay auth
- Client fetchers (`lib/socialClient.ts`): create post (JSON sin foto, multipart con foto), toggle like, send/respond request, block/unblock, comments
- API proxies Next.js (`/api/social/*`): 5 rutas con cookie auth httpOnly. Multipart pass-through para foto (gamification del backend dispara `awardPoints('wall_post')` igual)
- 10 componentes en `components/app/social/`:
  - `SocialView` (router + state global de posts/attendees/requests para sobrevivir cambios de tab)
  - `FeedView`, `PostCard`, `Composer` (con foto + preview + Cmd+Enter), `FeedSkeleton`
  - `InlineComments` (Instagram-style expandible dentro del post — usuario aprobo over drawer)
  - `PersonasView`, `AttendeeCard`, `SolicitudesView` (tabs Recibidas/Enviadas), `MisPostsView`
  - `socialDerive.ts` (formatRelativeTime, toggleLikeOptimistic, filterMyPosts)
- 409 en sendRequest → `lumina.info` "Ya tienes solicitud" (no error rojo)
- Search universal client-side (feed por body+author, personas por nombre/empresa/cargo)
- 14 vitest + 6 E2E pasando. mockBackend handlers + socialFixture
- `SidebarPill`: slot `/social` activo (icono Users), `/networking` removido del nav (fusionado)

### APP EVENTOS (main) — 1 commit pusheado

**`99136f3 design(W.6): demos social-networking + refs networking`**
- 3 demos HTML iterados en `design/features/webapp/SOCIAL-NETWORKING/`:
  - v1-A (3 columnas) + v1-B (2 cols + aside) — referencia comparativa, usuario escogio A
  - v2-davinci: version final con slate (no gold), elevation, 4 vistas funcionales con JS, lux toggle, foto vertical, comentarios inline, microinteracciones
- 2 refs visuales en `design/features/webapp/NETWORKING/` (Kilogram + Lens.app dashboards)

### Bugs durante la sesion (todos cerrados)
- ~~"Asistente" en todos los posts~~ → fixed alineando shape backend
- ~~Publicar fallaba~~ → fix usando `body` no `content`
- ~~Conectar no persiste al cambiar tab~~ → lift-up de state a SocialView
- ~~Conectar error rojo 409~~ → manejado como info
- ~~MoreHorizontal "3 puntitos" sin menu funcional~~ → removido
- ~~Chips Foto/Hashtag/Sesion fake~~ → quitados (foto reactivada real con multipart)
- ~~Drawer comentarios lateral~~ → reemplazado por inline expandible (Instagram-style)

### Tests
- 146 vitest verde (14 nuevos de socialDerive)
- 6 E2E social.spec.ts verde (auth gate, SSR feed, Personas grid, conectar optimistic, accept request quita row + baja badge, mis-posts empty)

### Commits (todos pusheados a remote)
- `eventos-web e2c9b4b` (main) — feat(W.6): modulo Social Networking
- `APP EVENTOS 99136f3` (main, repo eventos-docs) — design(W.6): demos + refs

---

## Proxima sesion

### Tarea principal — **Cerrar gaps W.6 detectados en audit Expo**

El modulo W.6 esta funcional pero la sesion anterior detecto gaps importantes comparado al Expo. Hay que cerrarlos antes de marcar W.6 como done. Prioridad alta→baja segun impacto:

**A. Avatar beam fallback** (~15 min, critico)
- Expo usa `hostedboringavatars.vercel.app/api/beam?name=X&colors=0EA5E9,6366F1,14B8A6,A855F7,38BDF8` para todos los avatares sin foto. Visual coloreado del nombre, NO iniciales.
- Codigo de referencia: `eventos-app/lib/avatars.ts:5-18` (`beamAvatarUrl` + `resolveAvatarUrl`)
- Webapp actual: rendereo `<div>` con iniciales "JP" + palette local. **Bug visible** ("JuanPerez" muestra JP)
- Fix: crear `eventos-web/src/lib/avatars.ts` espejo. Reemplazar TODOS los avatares del modulo social (PostCard, AttendeeCard, InlineComments, SolicitudesView, SocialView mini-rows) con `<Avatar name photoUrl size />` unico.

**B. Perfil del attendee** (~1.5-2h, critico)
- Sin esto, "Tu red" del panel der no sirve (no hay donde clickear) y los senders de solicitudes tampoco se pueden inspeccionar
- Referencia Expo: `eventos-app/app/(app)/attendee/[id].tsx` (612 lineas):
  - Hero horizontal: avatar 80px + nombre + job + company + CTA inline contextual al `relation`
  - Bio (`profile.bio`)
  - **Intereses con diferenciado visual** is_common vs no
  - **Sus sesiones** clickeables → `/session/[id]` con `session_type.color`
  - Redes sociales (LinkedIn / Twitter / Instagram) - Linking.openURL
  - **Contactar (SOLO si relation==='contact')**: WhatsApp + Email + Guardar contacto
  - Bloquear (BottomSheet confirm)
- En webapp: drawer derecho deslizable dentro del canvas (mejor que pagina dedicada — preserva el contexto del modulo). O modal central. Confirmar UX con usuario al arrancar.
- Backend: endpoint `/attendees/{id}/profile` ya conectado en `social.ts` (`fetchAttendeeProfile`). Solo falta UI + wire-up del click.

**C. Sugeridos cards grandes** (~45 min, alto UX)
- Expo `NetworkingScreen.tsx:337-473`: `BreathingCarousel` con cards 160x260px, avatar 56px centrado, pill "N intereses comun", chips de common_tags, CTA full-width
- Webapp: filas chiquitas en panel der
- Mover sugeridos del panel der a la columna centro de la vista Personas como header de la lista. Hacer click → perfil

**D. Tab Contactos separado** (~1h)
- Hoy Mi red esta en panel der como decoracion sin click. Falta vista dedicada
- Botones: "Guardar en telefono" (vCard download) + "Exportar todos (.vcf)" (blob descarga)
- Bloqueados accordion como footer

**E. Search server-side + pagination** (~45 min)
- Expo: debounce 400ms + query `q=text` al backend + `useInfiniteQuery` (max 100 items)
- Webapp: filter client-side (solo lo cargado SSR). Escala mal con eventos 500+
- Fix: hook con debounce + refetch + scroll infinite + footer "X de Y asistentes"

**F. PostCard pulido** (~30 min)
- Heart animation spring sequence (scale 1.3 → 1) usando `transform` CSS o framer-motion
- "Ver N comentarios" link bajo las acciones que tambien expande InlineComments (extra trigger ademas del icono)

**G. Memorias tab (photos + stories + FAB)** — fase aparte
- 2do segmento del Social Expo (`app/(app)/social.tsx:218-239`): photo gallery con grid + photo contest banner + PhotoViewer fullscreen + Stories 24h expiry + FAB flotante "+" abajo-derecha
- Backend ya tiene `/photos`, `/stories`, `/photo-contest` listos
- Estimar como subfeature de W.6 o modulo separado W.7. Decidir al arrancar.

**Estimado total A-F:** ~5-6h. **A+B+C son los mas criticos** y dan el feature usable en 3h.

**Para arrancar diga:** "siguiente" → arranco con A (avatar) en paralelo con investigacion de UI del perfil (B).

### Otras opciones (si NO quieres cerrar gaps W.6 todavia)

1. **Nuevo modulo W.8 Sponsors** — Brand Wall (grid logos) + Brand Profile + contact form. Memoria `project_sponsors_uiux_notes.md` tiene diseno aprobado. ~3h.
2. **Mobile parity Expo** — click sesion → agenda highlight, verificar status finished, slate tokens si aplica.
3. **Tests avanzados W.4** — paneles socket (requiere socket.io stub) + component happy-DOM tests.
4. **Pendientes design backlog** — errores 82-91 en `design/ERRORES/` (capturas sin revisar) + analytics tracking events speakers/agenda.

### Decisiones cerradas en esta sesion (2026-05-13/14, no preguntar)

- **Social Networking = modulo unificado.** Combina Networking + Wall en uno solo en la webapp (mobile sigue separado por limitacion de espacio). Decision basada en refs LinkedIn 2026 + Whova Social Wall.
- **Slot `/networking` removido del sidebar W.0.** Fusionado dentro de `/social`. Icono `Users` (no MessageSquare).
- **Comentarios INLINE expandibles dentro del post**, no drawer lateral. Aprobado explicitamente over drawer (Instagram-style).
- **Composer SIN chips Foto/Hashtag/Sesion fake.** Solo boton Foto real (multipart upload) + boton Publicar. Foto va en mismo endpoint backend → gamification dispara `awardPoints('wall_post')` igual.
- **409 en sendRequest NO es error.** Backend devuelve 409 si ya existe solicitud pendiente — manejar como `lumina.info` "Ya tienes solicitud" y mantener optimistic.
- **State global de posts/attendees/requests en SocialView** (no en sub-views). Permite que comentarios/likes/relacion persistan al cambiar de tab.
- **Shape backend wall posts es PLANO** (verificado leyendo `WallController::index`): `author: string` (NO objeto anidado), `body` (NO content), `liked` (NO is_liked), `author_photo` separado, `is_mine` true/false, cursor pagination `next_cursor` (NO meta).
- **Avatar fallback NO es iniciales.** Expo usa beam visual coloreado (`hostedboringavatars.vercel.app/api/beam`). Esto queda PENDIENTE de implementar en la webapp (gap A del audit).

### Decisiones cerradas previas (2026-05-10 #2, no preguntar de nuevo)

- **`TabletRotateOverlay` requiere `pointer: coarse`** (no solo viewport
  640-1023 + portrait). Sin ese check, desktop con ventana angosta
  (split-screen 50/50) recibia el overlay erroneamente.
- **Modo `compact-desktop` (640-1023 + pointer:fine)** es el responsive
  intermedio para split-screen. Custom variant en `globals.css`. Sidebar
  44px, padding reducido, CanvasCard sin clamp 1600. Tablets reales
  caen en `tablet` con `pointer:coarse` y reciben el overlay.
- **`deriveUiState` prioriza TIEMPO sobre status backend** (espejo
  Expo `lib/sessionStatus.ts:21`). El backend NO actualiza
  automaticamente `live → finished` cuando expira la ventana — confiar
  en `session.status === "live"` sin verificar tiempo era buggy.
- **Enum `event_sessions.status` backend usa `finished`** (NO `ended`).
  El `ended` es del enum `Event.status`. Confusion historica de la API.
  La webapp acepta ambos por defensiva.
- **W.4 streaming `notFound 404 status code` skip permanente.** Bug
  Next.js streaming + `loading.tsx` (vercel/next.js#76501). El body
  de la pagina 404 se renderiza correctamente — lo que importa para
  UX. Trade-off conocido entre skeletons y status code.
- **mockBackend debe implementar TODOS los endpoints SSR** aunque
  devuelvan vacio. Endpoints 404 en SSR causan retry-en-render +
  log noise + race conditions en E2E parallel. Implementado
  `/login-slides` con `{data: []}`.

### Decisiones cerradas previas (no preguntar de nuevo)

- **Identidad del modulo `/live` = Slate Mono.** Sin acento secundario,
  solo niveles de slate (#475569/#64748b/#94a3b8) + rojo del badge LIVE.
- **Slate como secondary global del sistema** (paralelo a `--accent-pair`).
  Tokens en `globals.css`: `--slate`, `--slate-light`, `--slate-dark`,
  `--slate-deep`. Independiente del `--accent` dinamico del cliente.
- **NO usar dots pulsantes** en ningun modulo. Color/iconografia bastan.
  Memoria: `feedback_no_pulsing_dots.md`.
- **Placeholders de cards = un solo radial-gradient elliptical disuelto**,
  no 3 spots concentrados. Tampoco grain SVG.
- **Loading skeleton (app)/ debe ser MINIMAL** (CanvasCard semi-transp
  sin shapes). Cada modulo mantiene su loading.tsx propio.
- **Carousels en desktop deben tener flechas flotantes** cuando hay
  overflow horizontal. Touch alone no basta para usuarios sin touch.

- E2E corre en puerto 3100 (separado del dev del usuario en 3000)
- mockBackend en 8101 reemplaza Laragon durante tests
- Bearer-tag pattern: cookie con valor "fake-bearer-event-pre", "...-event-ended",
  "...-recap-not-eligible", "...-recap-disabled" para variar status sin mocks separados
- Visibility checks en elementos con CSS opacity transitions: usar
  `toHaveClass(/\bopen\b/)` en lugar de `toBeVisible` (Playwright ignora opacity)
- `getByRole("alert")` ambiguo en dev mode (overlay hydration tambien usa alert)
  → filtrar por `hasText` cuando se asserta toasts especificos
- Counter pattern para `page.route` cuando una llamada cliente-side debe diferir
  entre 1ra (initial state) y 2da+ (post-action state)
- `useSessionLiveConfig` puede correr sin socket — los tests E2E lo aprovechan
  para cubrir el shell sin levantar socket.io server

---

## Pendientes paralelos

**Tests pendientes:**
- W.4 paneles interactivos (chat/Q&A/polls) — requieren socket.io server stub
- Component happy-DOM tests para AgendaView highlight initializer + SpeakersView preopen
- ~~Bug E2E W.4 streaming `notFound 404` → 200~~ ✅ skip permanente con
  `test.fixme` + nuevo test del body (commit 58ba728)

**W.10 Live Hub validacion visual:**
- ~~Subir thumbnails reales desde Filament~~ ✅ usuario subio + render OK

**Bugs detectados (todos cerrados):**
- ~~Hydration mismatch formatTime/formatRange~~ → fixed via `lib/format/time.ts`
- ~~Warning key prop en OuterLayoutRouter~~ → fixed reordenando providers
- ~~Sentry onRouterTransitionStart missing~~ → fixed via instrumentation-client
- ~~"UNIRTE" en sesiones terminadas en /agenda~~ → fixed (560c2ed)
- ~~Split-screen 50/50 disparaba overlay tablet erroneamente~~ → fixed (d9c1700)
- ~~CI rojo desde 2026-05-09~~ → verde ahora (58ba728)

**Backend (cross-team):**
- Featured/keynote como flags reales en DB
- Avg rating threshold ≥3 en lista
- Endpoint `/speakers/{id}/sessions/preview`

**Mobile parity:**
- Portar "click sesion → agenda highlight" al Expo

**Tracking analytics:**
- Eventos `speakers.list_viewed`, `speakers.detail_opened`, `speakers.rated`,
  `speakers.session_clicked`, `speakers.linkedin_clicked`

**Design backlog:**
- Errores 82-91 en `design/ERRORES/` aun sin revisar

---

## Convenciones / contexto operativo

- **Working dir principal:** `C:\laragon\www\APP EVENTOS` (este repo, docs+design)
- **Webapp Next.js:** `C:\laragon\www\eventos-web`
- **Mobile Expo:** `C:\Users\Kasproduction\Projects\eventos-app`
- **Backend Laravel:** `C:\laragon\www\eventos-backend` (vive en Laragon)
- **Modo de trabajo:** DaVinci — calidad sobre cantidad, cero emojis
- **E2E:** `pnpm test:e2e` levanta auto mockBackend (8101) + dev (3100). Reusa
  servers entre runs en local. Para reload de fixtures, killear puerto 8101
  con `Stop-Process`.
- **Workflow git:** commits cuando usuario diga "commit" / "guardar". Push solo
  con palabra explicita "push". Nunca skip hooks.
- **Usuario:** Kamilo Arias (solo founder), idioma espanol coloquial

---

## Como cierro cada sesion (yo, automaticamente)

Al final de cada sesion productiva, actualizo este archivo con:
1. Que se hizo (resumen 3-5 bullets)
2. Commits hechos (hashes)
3. Que sigue (proxima tarea concreta + prompt para arrancar)
4. Decisiones cerradas que no se deben preguntar de nuevo
5. Pendientes paralelos sin bloquear

Asi no tienes que recordar nada — solo abrir esto.
