# Bug Log — EventOS

> Registro completo de bugs encontrados y corregidos. Ordenado por fecha, mas reciente primero.
> Severidades: CRITICA (seguridad/crash/data) | ALTA (feature roto) | MEDIA (visual/UX) | BAJA (cosmetic/warning)

## 2026-04-25 — Data Center (8 bugs)

### BUG-286: CSV renombrado a .xlsx genera error en Excel (RESUELTO)
- **Severidad:** ALTA — BaseExportJob generaba CSV con fputcsv pero ponia extension .xlsx cuando el usuario elegia formato Excel
- **Sintoma:** Excel muestra error de formato corrupto al abrir el archivo
- **Fix:** Forzar siempre extension .csv. XLSX real requiere maatwebsite/excel (pendiente P2)

### BUG-285: R2 bucket vacio causa 500 en cada export (RESUELTO)
- **Severidad:** CRITICA — `shouldUploadToR2()` verificaba `config('filesystems.disks.r2.key')` pero en dev la key existe y el bucket esta vacio
- **Sintoma:** `The GetObject operation requires non-empty parameter: Bucket` en cada export
- **Fix:** Verificar `bucket` en vez de `key`, skip completo en environment testing

### BUG-284: readonly + SerializesModels causa 500 al dispatch (RESUELTO)
- **Severidad:** CRITICA — BaseExportJob usaba `public readonly int $eventId` en constructor. `SerializesModels` trait intenta reinicializar propiedades readonly al deserializar el job
- **Sintoma:** `Cannot initialize readonly property from scope ExportAttendeesMasterJob`
- **Fix:** Quitar `readonly` de las 4 propiedades del constructor en BaseExportJob y ExportMasterZipJob

### BUG-283: ExportService stats query — ChatMessage sin relacion session() (RESUELTO)
- **Severidad:** ALTA — `ExportService::computeStats()` usaba `ChatMessage::whereHas('session', ...)` pero ChatMessage no tiene relacion `session()`, tiene campo `room` (string) y relacion `sender()`
- **Sintoma:** 500 en endpoint `/api/v1/data-center/{event}/stats`
- **Fix:** Cambiado a `ChatMessage::where('event_id', $eventId)->count()`. Mismo fix para LiveGame

### BUG-282: N+1 queries en ExportGamesSummaryJob (RESUELTO)
- **Severidad:** MEDIA — Dentro del `cursor()->map()`, ejecutaba `LiveGameParticipant::where()->count()` y `->with()->get()` por cada juego
- **Sintoma:** Performance degradada, potencial timeout con muchos juegos
- **Fix:** Preload `participantCounts` y `triviaWinners` antes del cursor con queries agrupadas

### BUG-281: ExportAttendeesMasterJob — cursor() con HasMany no eager load (RESUELTO)
- **Severidad:** MEDIA — `cursor()` con `with('registrationFieldValues')` (HasMany) no hace eager loading. Cada attendee ejecuta query individual para sus field values
- **Sintoma:** N+1 queries silencioso, lento con muchos asistentes
- **Fix:** Cambiado de `cursor()` a `get()` para que el eager loading funcione correctamente

### BUG-280: ExportSpinResultsJob — NPE en sector null (RESUELTO)
- **Severidad:** MEDIA — `$sector['label']` cuando `$sector` es null (sector index invalido o config corrupta)
- **Sintoma:** Potential TypeError en PHP 7.x (PHP 8 permite null['key'] → null, pero no es defensivo)
- **Fix:** Default `$sector` a `[]` en vez de `null` cuando el index no se encuentra

### BUG-279: Selector de evento faltante en SPA (RESUELTO)
- **Severidad:** ALTA — SPA solo funcionaba si el URL tenia el event ID (`/data-center/1`). No habia forma de seleccionar evento desde la interfaz
- **Sintoma:** Mensaje "Selecciona un evento desde Filament" sin poder hacer nada
- **Fix:** Agregado endpoint `GET /api/v1/data-center/events`, dropdown en header de la SPA, preseleccion por URL

## 2026-04-24 — Concurso de Fotos + Golden Ticket (10 bugs)

### BUG-278: Archivos prize support no commiteados (RESUELTO)
- **Severidad:** ALTA — Announcement.php, Reward.php, bootstrap/app.php sin pushear de sesiones anteriores
- **Fix:** Commit separado con archivos pendientes

### BUG-277: action_url sin scheme eventos:// (RESUELTO)
- **Severidad:** ALTA — Announcements creados con `action_url: 'gamification/rewards'` sin scheme
- **Sintoma:** Android crashea: "No Activity found to handle Intent"
- **Fix:** Cambiar a `eventos://gamification/rewards` en todos los Announcement::create

### BUG-276: Announcement sin published_at (RESUELTO)
- **Severidad:** ALTA — Golden Tickets creaban announcements con `published_at: null`
- **Sintoma:** Announcements no aparecian en la app (endpoint filtra `whereNotNull('published_at')`)
- **Fix:** Agregar `'published_at' => now()` en los 4 Announcement::create de Filament

### BUG-275: LikesMilestoneService crash en fotos oficiales (RESUELTO)
- **Severidad:** CRITICA — `totalLikesForAttendee()` recibia null cuando `attendee_id` es null en fotos oficiales
- **Fix:** Guard `if ($photo->attendee_id !== null)` antes de llamar milestone service

### BUG-274: Self-like check crasheaba en fotos oficiales (RESUELTO)
- **Severidad:** MEDIA — Comparaba `null === $attendee->id` en fotos con attendee_id=null
- **Fix:** Guard `$photo->attendee_id !== null &&` antes de la comparacion

### BUG-273: Route /photos/contest despues de /{photoId} (RESUELTO)
- **Severidad:** MEDIA — Ruta literal despues de parametrizada, fragil ante cambios
- **Fix:** Mover GET /photos/contest antes de las rutas con {photoId}

### BUG-272: PhotoGrid viewer mostraba foto invertida (RESUELTO)
- **Severidad:** MEDIA — Sort por likes en MemoriasContent pero PhotoViewer usaba array original
- **Fix:** Mover sort al parent con useMemo, pasar mismo array sorted a grid y viewer

### BUG-271: Contest entry multiple por attendee (RESUELTO)
- **Severidad:** ALTA — Cada foto subida durante concurso se marcaba como contest entry
- **Fix:** Check `exists()` + `lockForUpdate()` — solo 1 entry por attendee

### BUG-270: ContestBanner crash "cannot read subtle" (RESUELTO)
- **Severidad:** CRITICA — `border.subtle` no existe en theme tokens, `border` es string plano
- **Fix:** Fallback `typeof border === 'string' ? border : border?.subtle`

### BUG-269: console.error en upload foto muestra red screen Expo (RESUELTO)
- **Severidad:** BAJA — `console.error` en catch mostraba error rojo en Expo debug ademas del toast
- **Fix:** Cambiar a `console.warn` solo en `__DEV__`

---

### BUG-268: Filament searchable selects no cargan opciones (PENDIENTE)
- **Severidad**: ALTA
- **Fecha**: 2026-04-24
- **Donde**: Filament v3.3.49 — TODOS los Select con `->searchable()` en forms y actions
- **Sintoma**: Escribes texto en el campo de busqueda y no retorna resultados. Afecta: Totems (salon), Golden Tickets (asistente), Patrocinadores, y cualquier Select searchable
- **Causa probable**: Combinacion de `->options(closure)` + `->searchable()` sin `->preload()` en Filament v3. En actions/modals el closure no se re-evalua. `filament:assets` y `optimize:clear` no lo resuelven
- **Workaround parcial**: Agregar `->preload()` a cada Select afectado, o cambiar a `->options()` estaticas sin `->searchable()`
- **Fix pendiente**: Auditar TODOS los Select searchable en `app/Filament/Resources/` y agregar `->preload()` donde falte. Considerar upgrade Filament si el bug persiste

---

## 2026-04-24 — Event Pulse Auditoria completa (30 bugs)

### BUG-267: Race condition sessionViewers undefined (RESUELTO)
- **Severidad:** CRITICA — sections.js accedia EP.sessionViewers antes de que socket.js lo inicializara
- **Fix:** Inicializar EP.sessionViewers={} y EP.sessionViewersList={} en data.js (carga primero)

### BUG-266: Memory leak leadsScrollTimer nunca limpiado (RESUELTO)
- **Severidad:** CRITICA — cada vez que se entraba a Leads se creaba un nuevo setInterval sin limpiar el anterior
- **Fix:** clearInterval(leadsScrollTimer) en showSection() al cambiar de seccion

### BUG-265: Null check updateDetailPanel sin guard (RESUELTO)
- **Severidad:** CRITICA — si rooms era null/undefined, el for loop crasheaba
- **Fix:** Guard `if (!rooms || !rooms.length) return` al inicio

### BUG-264: Null check speakers en selectSession (RESUELTO)
- **Severidad:** CRITICA — si speaker era null, acceder a spk.photo_url crasheaba
- **Fix:** Filter `speakers.filter(sp => sp && sp.name)` antes de iterar

### BUG-263: insertSocialPost sin validacion de data (RESUELTO)
- **Severidad:** CRITICA — si socket enviaba data sin body, el DOM insert fallaba
- **Fix:** Guard `if (!data || !data.body) return` al inicio de la funcion

### BUG-262: initials() crashea con nombre vacio o single char (RESUELTO)
- **Severidad:** ALTA — `name.split(' ')` con string vacio o solo espacios producida undefined[0]
- **Fix:** trim() + filter(p => p.length > 0) + guard !parts.length

### BUG-261: N+1 query en PulseController leads() (RESUELTO)
- **Severidad:** ALTA — query de Lead por cada sponsor dentro del map (N+1)
- **Fix:** 1 query con eager load + groupBy en PHP collection

### BUG-260: N+1 query en PulseController leaderboard() (RESUELTO)
- **Severidad:** ALTA — 2 queries por attendee (find + lastAction) dentro del map
- **Fix:** 3 queries batch: topRows, attendees whereIn, lastActions unique

### BUG-259: refreshBootstrap sin retry ni null check (RESUELTO)
- **Severidad:** ALTA — si el API fallaba post-reconnect, counters quedaban stale sin reintentar
- **Fix:** Guard `!data || !data.stats`, retry hasta 3 veces con backoff 5s

### BUG-258: Bootstrap response sin validacion (RESUELTO)
- **Severidad:** ALTA — si API devuelve respuesta incompleta, app.js crasheaba accediendo data.event
- **Fix:** `if (!data || !data.event || !data.stats) throw new Error()`

### BUG-257: API fetch sin timeout (RESUELTO)
- **Severidad:** ALTA — fetch() esperaba indefinidamente si el server no respondia
- **Fix:** AbortController con timeout de 8 segundos

### BUG-256: Charlas auto-select race condition (RESUELTO)
- **Severidad:** MEDIA — auto-select podia seleccionar sesion que ya no estaba live entre fetch y render
- **Fix:** Verificar `.live` en charlasData antes de llamar selectSession

### BUG-255: Session cleanup sin logging en socket server (RESUELTO)
- **Severidad:** MEDIA — errores de limpieza de viewers se silenciaban completamente
- **Fix:** console.warn en catch block del cleanup

### BUG-254: Token pulse sin validacion de longitud (RESUELTO)
- **Severidad:** MEDIA — tokens malformados `ep_x` pasaban al validatePulseToken
- **Fix:** Check `token.length >= 10` ademas de startsWith('ep_')

### BUG-253: Timeline hour edge case null (RESUELTO)
- **Severidad:** MEDIA — si checked_in_at producia hour null en SQLite, groupBy fallaba
- **Fix:** COALESCE en query + filter null keys

### BUG-252: Inline color detail-virt hardcoded (RESUELTO)
- **Severidad:** MEDIA — style="color:var(--blue)" inline en JS, no respetaba cascada CSS
- **Fix:** Clase CSS `.detail-aud-virt` con color: var(--blue)

### BUG-251: CSS orphan .agenda-time .min (RESUELTO)
- **Severidad:** BAJA — selector CSS sin elemento HTML correspondiente
- **Fix:** Eliminado

### BUG-250: CSS orphan m-lead y m-match sin builders JS (RESUELTO)
- **Severidad:** BAJA — estilos CSS completos para moment types sin implementar en JS
- **Fix:** Marcados con comentario "(CSS ready, JS builder pendiente)"

### BUG-249: Toggle CSS especificidad fragil (RESUELTO)
- **Severidad:** BAJA — .toggle-sun/.toggle-moon podian ser sobreescritos por selectores mas amplios
- **Fix:** Scope `.theme-toggle .toggle-*` para mayor especificidad

### BUG-248: Moments interval nunca limpiado (RESUELTO)
- **Severidad:** MEDIA — setInterval de 15s seguia corriendo aunque moment data se recargara
- **Fix:** Guardar en momentPlayInterval, clearInterval antes de crear nuevo

### BUG-247: Magic numbers en moments.js (RESUELTO)
- **Severidad:** BAJA — 5000, 15000, 3000, 20 hardcoded sin contexto
- **Fix:** Constantes MOMENT_DURATION, MOMENT_INTERVAL, MOMENT_FIRST_DELAY, MOMENT_POOL_MAX

### BUG-246: prefers-reduced-motion no respetado (RESUELTO)
- **Severidad:** BAJA — usuarios con accesibilidad reducida veian todas las animaciones
- **Fix:** Media query global que reduce duration a 0.01ms

### BUG-245: Charlas no diferencia sin sesiones vs todas finalizadas (RESUELTO)
- **Severidad:** BAJA — mismo mensaje generico para ambos casos
- **Fix:** "No hay sesiones programadas" vs "Todas las sesiones han finalizado"

### BUG-244: Bootstrap fail sin forma de reintentar (RESUELTO)
- **Severidad:** BAJA — usuario veia "Error cargando datos" sin opcion de retry
- **Fix:** Link "Reintentar" con location.reload()

### BUG-243: Typo "Ubicacion" sin tilde (RESUELTO)
- **Severidad:** BAJA — falta acento en HTML
- **Fix:** `Ubicaci&oacute;n`

### BUG-242: Dead HTML ep-bot-info nunca poblado (RESUELTO)
- **Severidad:** BAJA — div vacio en frame bottom sin JS que lo llene
- **Fix:** Eliminado HTML + CSS bot-left/bot-right

### BUG-241: last_action muestra slug interno (game_spin) en vez de label (RESUELTO)
- **Severidad:** MEDIA — leaderboard mostraba "game_spin" en vez de "Ruleta en vivo"
- **Fix:** PulseController usa PointsService::getConfig() para traducir action key a label

### BUG-240: Nombre evento duplicado en idle (RESUELTO)
- **Severidad:** BAJA — nombre aparecia arriba izquierda (brand) Y abajo izquierda (bot-left)
- **Fix:** Eliminado bot-left, nombre solo en brand-name + ambient center

### BUG-239: Dot verde doble en agenda charlas (RESUELTO)
- **Severidad:** BAJA — sesion live tenia agenda-dot verde + agenda-live-tag con otro dot
- **Fix:** Eliminado agenda-live-tag, solo queda agenda-dot

### BUG-238: Teal usado fuera de gamificacion (RESUELTO)
- **Severidad:** MEDIA — teal (#0D9488) se usaba en eyebrows, badges, timelines, networking. Solo deberia ser gamificacion
- **Fix:** Reemplazo sistematico: live→green, leads→platinum, networking→platinum, eyebrows→ink-50, bars→ink-50

### BUG-237: Pip status conexion siempre verde (RESUELTO)
- **Severidad:** MEDIA — pip del nav arrancaba verde por CSS default aunque socket no estuviera conectado
- **Fix:** Default rojo (offline), JS agrega .online (verde) o .reconnecting (amarillo). Fix especificidad nav.css

---

## 2026-04-24 — Event Pulse RT + Performance 10K (4 bugs)

### BUG-236: session:audience fan-out a 10K sockets (RESUELTO)
- **Severidad:** CRITICA — con 10K usuarios y 10 salones, broadcastAudience generaba ~200K msgs/sec al event room que nadie usaba
- **Causa:** broadcastAudience emitia session:audience a Rooms.event() (todos los usuarios) en vez de un room aislado
- **Fix:** Rooms.pulse(eventId) exclusivo para dashboards Pulse (1-2 sockets). MC sigue via Rooms.session(). App no recibe session:audience

### BUG-235: Sesiones fantasma "live" en Event Pulse (RESUELTO)
- **Severidad:** ALTA — sesiones que terminaron ayer seguian mostrando como "En vivo" en Pulse
- **Causa:** PulseController rooms() solo verificaba actual_end_at === null, sin considerar que publicEnd() ya habia pasado. Si el moderador nunca presiono "Terminar", la sesion quedaba live para siempre
- **Fix:** Agregar check `publicEnd()->isAfter(now())` en la condicion de live. Eliminar fallback a "most recent session"

### BUG-234: viewers[] sin limite en payload session:audience (RESUELTO)
- **Severidad:** MEDIA — con 1000 personas en un salon, el payload tenia 1000 objetos {id, name} (~40KB) enviados a todos
- **Causa:** broadcastAudience recolectaba TODOS los viewers sin limite
- **Fix:** Limitar a max 20 viewers. Pulse muestra max 8 burbujas + badge "+N"

### BUG-233: Counter m-on (En linea) solo se actualizaba cada 5 min (RESUELTO)
- **Severidad:** MEDIA — el counter "En linea" en Pulse solo cambiaba con el bootstrap refresh (5 min)
- **Causa:** socket.js no sumaba EP.sessionViewers al recibir session:audience. Solo el bootstrap cada 5min actualizaba m-on
- **Fix:** Calcular totalOnline sumando todos los EP.sessionViewers al recibir session:audience

---

## 2026-04-23 — Sesion Performance + Async Jobs (5 bugs)

### BUG-232: Redis del wildcard no limpia keys en tests (RESUELTO)
- **Severidad:** MEDIA — tests con cache stale daban falsos positivos
- **Causa:** Redis del con wildcard no limpia keys en tests
- **Fix:** Redis::flushdb()

### BUG-231: points_log sin unique constraint — duplicados posibles en retry (RESUELTO)
- **Severidad:** ALTA — retries podian crear entradas duplicadas de puntos
- **Causa:** points_log sin unique constraint
- **Fix:** Migration unique index + insertOrIgnore

### BUG-230: Trivia answer race condition — check + create sin proteccion (RESUELTO)
- **Severidad:** CRITICA — dos respuestas simultaneas podian ambas pasar el check
- **Causa:** check + create sin proteccion atomica
- **Fix:** firstOrCreate + wasRecentlyCreated

### BUG-229: Trivia closeRound 10K timeout — foreach 10K con awardPoints individual (RESUELTO)
- **Severidad:** CRITICA — closeRound con 10K participantes causaba timeout por foreach con awardPoints individual
- **Causa:** foreach 10K con awardPoints individual
- **Fix:** ProcessTriviaRewardsJob bulk async

### BUG-228: Spin 10K timeout — foreach 10K con 2 queries por participante (RESUELTO)
- **Severidad:** CRITICA — spin con 10K participantes causaba timeout por foreach con 2 queries por participante
- **Causa:** foreach 10K con 2 queries por participante
- **Fix:** ProcessSpinRewardsJob bulk async

---

## 2026-04-23 — Sesion Trivia Kahoot-style (10 bugs)

### BUG-227: loadTrivia llamada antes de definirse — ReferenceError (RESUELTO)
- **Severidad:** MEDIA — setActiveFeature llamaba loadTrivia() que se definia en IIFE posterior
- **Causa:** Orden de ejecucion: setActiveFeature se define antes que initTrivia IIFE
- **Fix:** Guard typeof loadTrivia === 'function'
- **Archivo:** mission-control/app.js

### BUG-226: renderGameTrivia usaba standbyTimer y displayContent inexistentes (RESUELTO)
- **Severidad:** CRITICA — display crasheaba al recibir proyeccion de trivia
- **Causa:** Variables copiadas mal: standbyTimer→_standbyTimer, displayContent→content
- **Fix:** Corregir nombres de variables
- **Archivo:** public/display/session.html

### BUG-225: initGames tenia handlers de trivia huerfanos — crash null addEventListener (RESUELTO)
- **Severidad:** CRITICA — gameTriviaNextBtn/gameTriviaCloseBtn/gameAddQuestion ya no existian en HTML pero los listeners seguian en JS, crash al cargar MC
- **Causa:** Migracion incompleta de trivia de Games a su propio tab
- **Fix:** Eliminar todos los handlers y elementos de trivia del IIFE de initGames
- **Archivo:** mission-control/app.js, mission-control/index.html

### BUG-224: Boton trivia en Games estaba disabled con titulo "Proximamente" (RESUELTO)
- **Severidad:** BAJA — boton de trivia en panel Games tenia disabled y title="Proximamente"
- **Causa:** Placeholder viejo nunca removido
- **Fix:** Removido — trivia ahora vive en su propio tab MC
- **Archivo:** mission-control/index.html

### BUG-223: Toast game:launched decia "Ruleta en curso" para trivia (RESUELTO)
- **Severidad:** BAJA — el label solo chequeaba jackpot, todo lo demas era "Ruleta"
- **Causa:** Ternario simple sin caso trivia
- **Fix:** Record con labels por tipo: spin/jackpot/trivia
- **Archivo:** hooks/useDataInvalidation.ts

### BUG-222: Countdown trivia no se detenia al responder (RESUELTO)
- **Severidad:** MEDIA — despues de responder una pregunta, el timer seguia contando en la app
- **Causa:** useEffect del countdown no observaba myAnswer
- **Fix:** Agregar useEffect separado que limpia el interval cuando myAnswer cambia
- **Archivo:** components/screens/TriviaPanel.tsx

### BUG-221: GameController errores usaban 'error' en vez de 'message' — MC no mostraba mensajes (RESUELTO)
- **Severidad:** MEDIA — apiFetch busca d.message pero backend enviaba d.error, toasts de error vacios
- **Causa:** Inconsistencia en formato de respuesta de error
- **Fix:** Reemplazar todos response()->json(['error' =>]) por ['message' =>]
- **Archivo:** GameController.php (26 ocurrencias)

### BUG-220: configPayload no devolvia feature flags individuales (RESUELTO)
- **Severidad:** ALTA — API devolvia interactive_mode pero no chat_enabled/qna_enabled/polls_enabled/trivia_enabled, frontend no podia derivar activePanel
- **Causa:** configPayload solo retornaba interactive_mode raw
- **Fix:** Derivar flags de interactive_mode en configPayload + derivar activePanel de interactive_mode directamente
- **Archivo:** SessionConfigController.php, useSessionConfig.ts

### BUG-219: effectiveMode mapping no incluia trivia — panel siempre 'none' (RESUELTO)
- **Severidad:** CRITICA — session-stream linea 302 tenia mapping manual hardcodeado que no incluia 'trivia', siempre caia en 'none' mostrando "Interaccion desactivada"
- **Causa:** Se usaba effectiveMode con ternarios en vez de activePanel del config
- **Fix:** Reemplazar mapping manual por activePanel directo de useSessionConfig
- **Archivo:** app/(app)/session-stream/[id].tsx

### BUG-218: publicShow recibia eventId como sessionId — config de sesion incorrecta (RESUELTO)
- **Severidad:** CRITICA — GET /events/{eventId}/sessions/{sessionId}/live-config siempre devolvia config de sesion 1 (eventId) en vez de la sesion real
- **Causa:** publicShow(int $sessionId) solo recibia 1 parametro, Laravel inyectaba el primer parametro de ruta (eventId)
- **Fix:** publicShow(int $eventId, int $sessionId) — recibir ambos parametros
- **Archivo:** SessionConfigController.php

---

## 2026-04-22 — Golden Ticket + Prize Fixes (5 bugs)

### BUG-217: Announcements privados visibles a todos los attendees del mismo rol (RESUELTO)
- **Severidad:** ALTA — announcements con target_attendee_id (ej: "Ganaste el sorteo") se mostraban a todos los attendees del mismo rol, no solo al target
- **Causa:** AnnouncementController filtraba por roles pero no por target_attendee_id
- **Fix:** Separar query: publicos (cacheados por rol, whereNull target_attendee_id) + privados (sin cache, where target_attendee_id = attendee actual)
- **Archivo:** app/Http/Controllers/Api/V1/AnnouncementController.php

### BUG-216: RewardController::myRedemptions() response crash expires_at null (RESUELTO)
- **Severidad:** CRITICA — listar redemptions con prizes (expires_at=null) crasheaba al mapear la respuesta
- **Causa:** `$r->expires_at->toIso8601String()` sin nullsafe
- **Fix:** `$r->expires_at?->toIso8601String()`
- **Archivo:** app/Http/Controllers/Api/V1/RewardController.php

### BUG-215: RewardController::redeem() response crash expires_at null (RESUELTO)
- **Severidad:** MEDIA — aunque redeem() solo crea redemptions con expires_at, la respuesta no usaba nullsafe
- **Causa:** `->toIso8601String()` en campo que podria ser null en futuras extensiones
- **Fix:** `$redemption->expires_at?->toIso8601String()`
- **Archivo:** app/Http/Controllers/Api/V1/RewardController.php

### BUG-214: RewardService::confirm() crash con expires_at null (RESUELTO)
- **Severidad:** CRITICA — confirmar un premio de jackpot (expires_at=null) crasheaba con "Call to a member function isPast() on null"
- **Causa:** abort_if no verificaba null antes de ->isPast()
- **Fix:** `abort_if($redemption->expires_at && $redemption->expires_at->isPast(), 410, ...)`
- **Archivo:** app/Services/RewardService.php

### BUG-213: RewardRedemption::isExpired() crash con expires_at null (RESUELTO)
- **Severidad:** CRITICA — premios tipo prize (jackpot) tienen expires_at=null, isExpired() llamaba ->isPast() en null
- **Causa:** Metodo no contemplaba que prize redemptions no expiran
- **Fix:** Nullsafe operator `$this->expires_at?->isPast()`
- **Archivo:** app/Models/RewardRedemption.php

---

## 2026-04-22 — Agenda end_datetime no se actualizaba en RT

### BUG-212: Hora de fin no se reflejaba en agenda despues de editar en Filament (RESUELTO)
- **Severidad:** ALTA — admin cambiaba hora de fin y la app seguia mostrando la vieja
- **Causa:** `publicEnd()` retorna `adjusted_end_at ?? end_datetime`. Si en algun momento se hizo un delay desde MC, `adjusted_end_at` se seteaba y tenia prioridad permanente sobre `end_datetime`. Al editar `end_datetime` en Filament, la API seguia devolviendo el `adjusted_end_at` viejo.
- **Fix:** `EventSessionObserver::updated()` ahora limpia `adjusted_end_at` via `updateQuietly()` cuando `end_datetime` cambia. Asi `publicEnd()` refleja el nuevo horario.

---

## 2026-04-22 — Auditoria sistema invalidacion RT (10 bugs)

### BUG-211: HTTP response del socket nunca se validaba (RESUELTO)
- **Severidad:** MEDIA — respuestas 4xx/5xx del socket se trataban como exito
- **Fix:** Validar `$response->successful()`, log warning si no es 2xx

### BUG-210: Throttle key se setea ANTES del broadcast — fallo silencioso sin retry (RESUELTO)
- **Severidad:** MEDIA — si HTTP falla, throttle bloquea reintento por 1 segundo
- **Causa:** `Cache::put(throttleKey)` se ejecutaba antes del HTTP POST. Si el POST fallaba, el throttle ya estaba activo.
- **Fix:** Throttle key solo se setea DESPUES de response exitoso. Si falla, retry posible inmediatamente.

### BUG-209: EventObserver cache key modules sin sufijo de rol — no limpia nada (RESUELTO)
- **Severidad:** MEDIA — `Cache::forget("event:{id}:modules")` no matcheaba keys reales `event:{id}:modules:attendee`
- **Fix:** Iterar roles (attendee/vendedor/guest) + vendedor_extra, igual que ModuleObserver

### BUG-208: Docblock InvalidationService dice 200ms, implementacion es 1s (RESUELTO)
- **Severidad:** MEDIA — documentacion inconsistente con implementacion
- **Fix:** Docblock corregido a "1 second"

### BUG-207: EventRoom sin observer — cambios de sala invisibles (RESUELTO)
- **Severidad:** ALTA — renombrar sala no se reflejaba en agenda de la app
- **Causa:** No existia observer para `EventRoom`
- **Fix:** `EventRoomObserver` creado: cache clear agenda + broadcast

### BUG-206: SessionTrack sin observer — cambios de track invisibles (RESUELTO)
- **Severidad:** ALTA — renombrar track o cambiar color no se reflejaba en la app
- **Causa:** No existia observer para `SessionTrack`
- **Fix:** `SessionTrackObserver` creado: cache clear agenda + broadcast

### BUG-205: Speaker y Announcement observers incompletos — uno limpia cache, otro broadcast (RESUELTO)
- **Severidad:** ALTA — doble observer con responsabilidades partidas
- **Causa:** `SpeakerObserver` solo broadcasteaba, `ContentObserver` solo limpiaba cache. Idem `AnnouncementObserver`
- **Fix:** Cada observer dedicado ahora hace cache clear + broadcast. ContentObserver como respaldo

### BUG-204: ModuleObserver limpia cache pero nunca broadcast — app no se entera (RESUELTO)
- **Severidad:** ALTA — cambios de modulos (habilitar/deshabilitar) no llegaban a la app en RT
- **Causa:** Observer solo llamaba `Cache::forget()` + push silencioso, nunca `InvalidationService::broadcast()`
- **Fix:** Agregar `InvalidationService::broadcast($eventId, 'modules')` en created/updated/deleted

### BUG-203: EventObserver isDirty() siempre false en saved() — polls post-evento nunca se activan (RESUELTO)
- **Severidad:** CRITICA — encuestas post-evento nunca se activaban automaticamente
- **Causa:** `isDirty()` en hook `saved()` siempre retorna false porque el modelo ya sincronizo atributos con la DB
- **Fix:** Cambiar a `wasChanged('status')` que funciona correctamente en `saved()`

### BUG-202: broadcastToAttendee llama endpoint inexistente en socket server (RESUELTO)
- **Severidad:** CRITICA — toda invalidacion per-attendee fallaba silenciosamente (404)
- **Causa:** `InvalidationService::broadcastToAttendee()` POSTeaba a `/internal/emit-to-user` que no existia en el socket server
- **Fix:** Endpoint agregado en socket server, usa `attendeeConnections` map para emitir a sockets especificos

---

## 2026-04-22 — Sorteo Ceremony: Rewrite + Bug Fixes (10 bugs)

### BUG-201: Photo src vacio no dispara onerror — imagen rota invisible (RESUELTO)
- **Severidad:** MEDIA — si `photo_url` es null/empty, `src=""` no dispara onerror en todos los browsers
- **Causa:** Celdas del reel usaban `src="${photo_url || ''}"` que es un src vacio
- **Fix:** Si no hay `photo_url`, usar beam directamente como src: `p.photo_url || beamFallback(p.name)`

### BUG-200: Winner beam avatar pixelado — usaba tamaño del reel (RESUELTO)
- **Severidad:** BAJA — beam en reveal se veia borroso
- **Causa:** `beamFallback` usaba `imgSize` del reel (~386px) pero la foto del winner es 320px max. En realidad el beam se pedia a tamaño reel cuando debia ser 320.
- **Fix:** `beamFallback` ahora acepta parametro `size`, winner usa `beamFallback(name, 320)`

### BUG-199: Participants vacio causa crash en ceremony (RESUELTO)
- **Severidad:** ALTA — si backend manda `participants:[]`, `others[random]` da undefined
- **Causa:** No habia fallback cuando la lista de participantes venia vacia
- **Fix:** `if (!others.length) others = participants.length ? participants : [winner]`

### BUG-198: Glow ring desalineado del reel — fotos fuera del borde dorado (RESUELTO)
- **Severidad:** MEDIA — borde dorado no contenia las fotos
- **Causa:** Glow era un `div` separado con tamaño fijo en CSS que no matcheaba el reel responsivo (`min(45vh,420px)`)
- **Fix:** Eliminar div glow separado, usar `outline` directo en el reel con `outline-offset:8px`. Siempre rodea el reel exacto.

### BUG-197: Premio y titulo montados encima del reel (RESUELTO)
- **Severidad:** MEDIA — texto se superponia con las fotos del reel
- **Causa:** `.d-ceremony-info` estaba en el flow normal con `margin-bottom` insuficiente. Al crecer el reel con `min(45vh,420px)`, el espacio no alcanzaba.
- **Fix:** Mover info a `position:absolute; top:0; right:0` — queda al nivel del header, nunca se monta

### BUG-196: Fase participacion mostraba "0 PARTICIPANTES" sin sentido (RESUELTO)
- **Severidad:** MEDIA — confuso para el publico
- **Causa:** El counter de participacion y countdown se mostraban en el display publico. El pool se calcula automaticamente, no hay nada que "unirse".
- **Fix:** Reemplazar counter/countdown por "Preparate..." pulsante. El MC ya tiene el eligible count para el moderador.

### BUG-195: Nombre duplicado en reel — overlay + label debajo (RESUELTO)
- **Severidad:** BAJA — nombre aparecia dentro de la foto Y debajo del reel
- **Causa:** Se renderizaba `.cell-name` overlay dentro de cada celda + `#reelName` debajo del reel simultaneamente
- **Fix:** Quitar overlay de las celdas, dejar solo el label debajo

### BUG-194: Winner name no aparecia en reveal — letters stagger vacias (RESUELTO)
- **Severidad:** ALTA — nombre del ganador no se mostraba
- **Causa:** `gsap.to('.winner-letter', ...)` se agendaba al construir el timeline, pero las letras se creaban dinamicamente en un `call()` posterior. GSAP no encontraba elementos.
- **Fix:** Mover stagger dentro de un `call()` para que ejecute cuando las letras ya existan en DOM

### BUG-193: Confetti no aparecia en winner reveal (RESUELTO)
- **Severidad:** MEDIA — confetti invisible despues del reveal
- **Causa:** CSS `.d-confetti-piece` tenia `animation:confettiFall linear forwards` que competia con GSAP. La CSS animation forzaba opacity a 0, pisando los valores de GSAP.
- **Fix:** Quitar CSS keyframes de confetti, dejar solo GSAP para animar las piezas

### BUG-192: Slot machine 3 reels — feo, demasiado espacio negro, casino feel (RESUELTO)
- **Severidad:** MEDIA — UX/diseño inaceptable para pantalla de evento premium
- **Causa:** Implementacion original usaba 3 columnas de fotos girando (slot machine literal). Se veian 3 puntos focales compitiendo, mucho negro entre reels, aspecto de casino.
- **Fix:** Rewrite completo → "Photo Cascade Ceremony": strip vertical unico con GSAP `power4.out`, 20 celdas max, shockwave, winner reveal con nombre grande (80px) + confetti amber

---

## 2026-04-22 — Live Moments Phase 2: Pool RT + Spin/Jackpot Fixes (15 bugs)

### BUG-191: Standby timer no se limpia en disconnect (RESUELTO)
- **Severidad:** MEDIA — timer seguia corriendo tras desconexion del socket
- **Causa:** clearTimeout + clearInterval no se llamaban al desconectar
- **Fix:** clearTimeout + clearInterval en disconnect handler

### BUG-190: Idle message stuck on error (RESUELTO)
- **Severidad:** MEDIA — mensaje idle quedaba visible despues de un error
- **Causa:** Estado no se restauraba a idle tras un error
- **Fix:** Restore idle state en error handler

### BUG-189: < 2 sectores = pantalla blanca (RESUELTO)
- **Severidad:** ALTA — crear spin con menos de 2 sectores dejaba pantalla en blanco
- **Causa:** spin-wheel lib requiere minimo 2 sectores
- **Fix:** Validation message antes de lanzar

### BUG-188: Spin-wheel module race condition (RESUELTO)
- **Severidad:** MEDIA — modulo spin-wheel fallaba intermitentemente al montar
- **Causa:** Timing issue en inicializacion del canvas
- **Fix:** retry con setTimeout

### BUG-187: CSV formula injection (RESUELTO)
- **Severidad:** ALTA (seguridad) — campos con = + - @ podian ejecutar formulas en Excel
- **Causa:** Export CSV sin sanitizacion de prefijos de formula
- **Fix:** Sanitize prefixes (= + - @) en export

### BUG-186: Jackpot srandmember null → attendee_id=0 (RESUELTO)
- **Severidad:** ALTA — si Redis SRANDMEMBER retornaba null, se asignaba attendee_id=0
- **Causa:** No se verificaba null del resultado de srandmember
- **Fix:** Null check antes de asignar winner

### BUG-185: mt_rand crash con sectors vacio (RESUELTO)
- **Severidad:** CRITICA — mt_rand(0, -1) crasheaba PHP
- **Causa:** sectors array vacio pasaba count()-1 como max a mt_rand
- **Fix:** Early return si sectors vacio

### BUG-184: Race condition double draw — atomic WHERE status=active (RESUELTO)
- **Severidad:** ALTA — dos draws simultaneos podian ejecutarse
- **Causa:** Sin proteccion atomica en cambio de estado
- **Fix:** atomic WHERE status=active en UPDATE

### BUG-183: Race condition double spin — atomic WHERE status=active (RESUELTO)
- **Severidad:** ALTA — dos spins simultaneos podian ejecutarse
- **Causa:** Sin proteccion atomica en cambio de estado
- **Fix:** atomic WHERE status=active en UPDATE

### BUG-182: Race condition double launch — atomic WHERE status=draft (RESUELTO)
- **Severidad:** ALTA — dos launches simultaneos podian crear juego duplicado
- **Causa:** Sin proteccion atomica en cambio de estado
- **Fix:** atomic WHERE status=draft en UPDATE

### BUG-181: Gamificacion invisible — game_spin already in config (RESUELTO)
- **Severidad:** MEDIA — accion de gamificacion no aparecia
- **Causa:** game_spin ya estaba registrado en config
- **Fix:** Verificar duplicados antes de agregar

### BUG-180: Rueda cuadrada (RESUELTO)
- **Severidad:** MEDIA — la rueda se renderizaba como cuadrado
- **Causa:** overflow hidden sin border-radius
- **Fix:** border-radius 50% + overflow hidden

### BUG-179: Pool virtual stale — Redis zombies (RESUELTO)
- **Severidad:** ALTA — participantes desconectados seguian en el pool
- **Causa:** Socket RT no limpiaba pool al desconectar
- **Fix:** Limpiar pool en disconnect handler del socket

### BUG-178: Sectores sin color/weight → rueda gris (RESUELTO)
- **Severidad:** MEDIA — sectores sin configurar se renderizaban grises
- **Causa:** Backend no asignaba defaults de color y weight
- **Fix:** Backend defaults para color y weight en sectores

### BUG-177: Angulo visual no corresponde al sector (RESUELTO)
- **Severidad:** ALTA — la rueda apuntaba a un sector diferente al ganador
- **Causa:** Bug en libreria spin-wheel con calculo de angulos
- **Fix:** Fix en spin-wheel lib para angulos correctos

---

## 2026-04-21 — Kiosk + Staff Check-in (13 bugs)

### BUG-176: HMAC incompatible en StaffCheckinController.resolveAttendeeFromQr (RESUELTO)
- **Severidad:** CRITICA — assign staff siempre fallaba con "QR no valido" para tokens dinamicos
- **Causa:** Implementacion propia usaba formato `{id}.{window}` con sig 12 chars y ventana 30s, pero CheckinService genera `{id}|{event_id}|{window}` con sig 32 chars y ventana 60s
- **Fix:** Delegar a `CheckinService::validateDynamicToken()` como fuente unica de verdad
- **Archivo:** StaffCheckinController.php (resolveAttendeeFromQr)

### BUG-175: assign-staff doble submit al seleccionar room (RESUELTO)
- **Severidad:** MEDIA — tap rapido en room picker podia enviar dos assigns del mismo QR al mismo room
- **Causa:** `handleRoomSelect` no verificaba si ya estaba en estado `assigning`
- **Fix:** Guard `if (scanState.status === 'assigning') return` al inicio
- **Archivo:** app/(app)/assign-staff.tsx

### BUG-174: auto-flush useEffect con stale closure de flush (RESUELTO)
- **Severidad:** MEDIA — el effect usaba `flush` directo como dependencia, que cambiaba en cada render y causaba re-runs innecesarios
- **Causa:** `flush` es un `useCallback` pero sus dependencias cambian, creando nueva referencia cada vez
- **Fix:** `flushRef.current` pattern para evitar dependencia directa en el effect
- **Archivo:** app/(app)/staff-checkin.tsx

### BUG-173: Cola offline descartaba scans fallidos en flush (RESUELTO)
- **Severidad:** ALTA — si scanBatch retornaba errores parciales (ej. NOT_ASSIGNED), flush borraba toda la cola incluyendo scans que podrian reintentarse
- **Causa:** `saveQueue([])` incondicional despues de scanBatch, sin distinguir entre errores permanentes (DEBOUNCE, QR_INVALID) y transitorios
- **Fix:** Solo descartar scans con errores permanentes, re-encolar el resto
- **Archivo:** hooks/useOfflineScanQueue.ts

### BUG-172: acceptAssignment race condition — dos accepts concurrentes (RESUELTO)
- **Severidad:** ALTA — dos requests simultaneos podian ambos leer accepted_at=NULL y ejecutar el update
- **Causa:** Query sin lock, permitia lecturas concurrentes del mismo row
- **Fix:** `lockForUpdate()` en la query de assignment
- **Archivo:** StaffCheckinController.php (acceptAssignment)

### BUG-171: reassign no validaba from_room del mismo evento (RESUELTO)
- **Severidad:** ALTA — admin de evento A podria desactivar assignment de evento B
- **Causa:** `reassign` solo validaba `to_room_id` con `event_id`, pero `from_room_id` se usaba sin verificar pertenencia al evento
- **Fix:** Agregar `firstOrFail()` con `event_id` para from_room antes de desactivar
- **Archivo:** StaffCheckinController.php (reassign)

### BUG-170: RoomAssignmentModal doble tap en aceptar/rechazar (RESUELTO)
- **Severidad:** ALTA — dos taps rapidos enviaban dos API calls, podian activar role dos veces
- **Causa:** `handleAccept` no verificaba `loading` state antes de ejecutar. Entre el tap y el setState async, el segundo tap pasaba el guard.
- **Fix:** Agregar `if (!pending || loading) return` en accept y reject
- **Archivo:** components/ui/RoomAssignmentModal.tsx

### BUG-169: StaffHappeningNow y staff-checkin no respetan tema Lux (RESUELTO)
- **Severidad:** MEDIA — colores hardcoded Noir, BottomSheet invisible en tema Lux
- **Causa:** Componentes creados con tokens Noir estaticos en vez de useTheme()
- **Fix:** Migrado a useTheme() tokens duales, BottomSheet textos/botones theme-aware, boton "Escanear otro" ink #1A1A1A en Lux
- **Archivo:** StaffHappeningNow.tsx, staff-checkin.tsx

### BUG-168: Staff check-in layout aislado en vez de modulo (RESUELTO)
- **Severidad:** MEDIA — staff perdia acceso a agenda, networking, etc.
- **Causa:** Implementacion inicial redirigia a Stack separado sin tabs. El usuario esperaba la misma experiencia que el vendedor (app completa + modulo extra).
- **Fix:** Revertir redirect, agregar como modulo en ModuleMenu y StaffHappeningNow card en home
- **Archivo:** _layout.tsx, ModuleMenu.tsx, index.tsx

### BUG-167: roomCheckinApi usaba api como funcion callable (RESUELTO)
- **Severidad:** ALTA — staff-checkin mostraba "Sin salones asignados" siempre
- **Causa:** `api` es un objeto con `.get()/.post()`, no una funcion. `api<T>(path)` fallaba silenciosamente.
- **Fix:** Cambiar a `api.get<T>(path)` y `api.post<T>(path, body)`
- **Archivo:** lib/roomCheckinApi.ts

### BUG-166: Kiosko socket WebSocket error en consola (RESUELTO)
- **Severidad:** BAJA — error en consola, no afecta funcionalidad
- **Causa:** Kiosko enviaba totem_token al socket server que espera Bearer Sanctum token. Socket rechazaba con 401.
- **Fix:** Eliminar conexion socket del kiosko. Ping HTTP cada 10s es suficiente para actualizaciones.
- **Archivo:** useRoomTotem.ts (socket removed)

### BUG-165: Manifest endpoint crash — undefined variable $now (RESUELTO)
- **Severidad:** ALTA — manifest seguia con 500 despues del fix de BUG-164
- **Causa:** Variable `$now` fue eliminada en el refactor pero seguia usandose en `generated_at`
- **Fix:** Cambiar `$now->toISOString()` a `now()->toISOString()`
- **Archivo:** RoomCheckinController.php (manifest method)

### BUG-164: Manifest endpoint crash — is_banned column not found (RESUELTO)
- **Severidad:** ALTA — manifest retornaba 500, kiosko no cargaba cache de nombres
- **Causa:** Query usaba `get(['id','user_id','checked_in_at','is_banned'])` pero attendees no tiene columna `is_banned`, el ban esta en tabla separada `attendee_bans`
- **Fix:** Remover `is_banned` del select, query separada a `AttendeeBan` con flip() para lookup rapido
- **Archivo:** RoomCheckinController.php (manifest method)

---

## 2026-04-21 — Session Lifecycle + Mission Control + Agenda RT (9 bugs)

### BUG-163: Kiosk ping ignora adjusted_end_at — muestra hora original tras delay (RESUELTO)
- **Severidad:** ALTA — kiosk mostraba hora de fin sin reflejar el retraso
- **Consecuencia:** Moderador retrasa sesion 15 min desde MC, pero el totem en la puerta del salon sigue mostrando la hora original. Asistentes que miran la pantalla creen que la sesion termina antes. Progress bar tambien incorrecta.
- **Causa:** `RoomCheckinController::ping()` usaba `$s->actual_end_at ?? $s->end_datetime` ignorando `adjusted_end_at` (el campo que guarda el delay)
- **Fix:** Cambiar a `$s->actual_end_at ?? $s->publicEnd()` — `publicEnd()` ya prioriza adjusted sobre original
- **Archivo:** RoomCheckinController.php:95

### BUG-162: Push de delay incluia asistentes de sesiones pasadas (RESUELTO)
- **Severidad:** BAJA — notificacion innecesaria a gente que favorito sesiones de hace meses
- **Consecuencia:** Si un salon tuvo un evento hace 6 meses y se reusa, los asistentes del evento viejo que favoritaron sesiones en ese salon recibian push "Agenda retrasada X min" sin contexto.
- **Causa:** Query de favoritos no filtraba por fecha, incluia todas las sesiones del salon sin importar cuando fueron
- **Fix:** Filtro `where('start_datetime', '>=', now()->subHours(2))` para solo incluir sesiones recientes
- **Archivo:** SessionConfigController.php:382

### BUG-161: MC segundo moderador no recibia timer ni stop al cambiar estado (RESUELTO)
- **Severidad:** MEDIA — si dos moderadores abrian MC, el segundo no veia el cronometro correcto
- **Consecuencia:** Moderador 1 inicia sesion. Moderador 2 recibe evento socket `session:started` pero no iniciaba el timer — mostraba "En vivo" sin cronometro. Al finalizar, timer del moderador 2 seguia corriendo.
- **Causa:** Socket listeners para session:started/ended/cancelled solo cambiaban `sessionState` y llamaban `updateControlUI()`, sin llamar `startLiveTimer()`/`stopLiveTimer()` ni usar `actual_start_at` del payload
- **Fix:** Iniciar timer con `actual_start_at` del server al recibir started, detener en ended/cancelled
- **Archivo:** public/mission-control/app.js:1607-1609

### BUG-160: Agenda app no actualizaba horarios en tiempo real (RESUELTO)
- **Severidad:** ALTA — moderador cambiaba horario, asistentes veian hora vieja hasta reabrir app
- **Consecuencia:** Titulo, descripcion y otros campos SI se actualizaban RT. Pero start/end NO. El asistente veia "10:00 AM" cuando la sesion ya estaba movida a "10:30 AM". Llegaba tarde o se confundia.
- **Causa:** 3 problemas simultaneos:
  1. MMKV disk cache (`initialData`) restauraba datos stale al re-montar componentes
  2. FlashList `extraData` no incluia la data de sesiones, no forzaba re-render al cambiar horarios
  3. `ENTITY_KEYS['agenda']` solo invalidaba `['agenda']`, no `['mi-agenda']` — favoritos nunca se refrescaban
- **Fix:** (1) `deleteCached()` del MMKV al recibir invalidation socket, (2) agregar `sessionsForDay` a extraData, (3) agregar `'mi-agenda'` a ENTITY_KEYS
- **Archivos:** useDataInvalidation.ts, AgendaScreen.tsx

### BUG-159: Cancelar sesion retrasada no revertia el delay en la siguiente (RESUELTO)
- **Severidad:** ALTA — la siguiente sesion quedaba desplazada sin razon
- **Consecuencia:** Si sesion A tenia +15 min de delay (empujando sesion B), y luego A se cancelaba, sesion B seguia desplazada +15 min innecesariamente. En un dia con 8 sesiones, esto podia acumular 30+ min de gap fantasma.
- **Causa:** `cancel()` solo seteaba `cancelled_at` sin verificar si habia un delay activo que revertir
- **Fix:** Si la sesion cancelada tenia `adjusted_end_at`, calcular el delay y revertirlo en la siguiente sesion
- **Archivo:** SessionConfigController.php:242-249
- **Test nuevo:** `test_cancel_reverts_delay_on_next_session`

### BUG-158: start() permitia iniciar sesion finalizada o cancelada (RESUELTO)
- **Severidad:** ALTA — moderador podia "reiniciar" una sesion terminada, corrompiendo datos de asistencia
- **Consecuencia:** Si moderador clickeaba "Iniciar" en una sesion ya finalizada (por error de UI o segundo MC), se sobreescribia `actual_start_at`. Todas las metricas de asistencia (duracion, check-in/out) quedaban invalidas.
- **Causa:** Solo se validaba `actual_start_at` (ya iniciada), sin verificar `actual_end_at` ni `cancelled_at`
- **Fix:** Guards para ALREADY_ENDED y CANCELLED antes del check de ALREADY_STARTED
- **Archivo:** SessionConfigController.php:148
- **Tests nuevos:** `test_start_rejects_already_ended`, `test_start_rejects_cancelled`

### BUG-157: Delay podia mover sesiones ya iniciadas (RESUELTO)
- **Severidad:** ALTA — sesion en vivo se movia en la agenda, confundiendo asistentes
- **Consecuencia:** Si la sesion B ya estaba en vivo y el moderador retrasaba sesion A, sesion B se movia en la agenda publica. Asistentes en B veian "Sesion en vivo" con horario futuro, inconsistencia critica.
- **Causa:** `adjustNextSession()` buscaba la siguiente sesion por `start_datetime` sin excluir las que ya tienen `actual_start_at`
- **Fix:** Agregar `->whereNull('actual_start_at')` al query
- **Archivo:** SessionConfigController.php:334
- **Test nuevo:** `test_delay_does_not_move_already_started_next`

### BUG-156: Archivo .ics de calendario ignora delays — hora original (RESUELTO)
- **Severidad:** ALTA — usuario descarga sesion al calendario y la hora no refleja el retraso
- **Consecuencia:** Si una sesion fue retrasada 30 min, el .ics seguia mostrando la hora original. El asistente llegaba 30 min antes al salon vacio, o perdia el inicio real.
- **Causa:** Endpoint calendar usaba `$session->end_datetime` (hora original) en vez de `$session->publicEnd()` (hora ajustada con delay)
- **Fix:** Cambiar a `publicEnd()` que prioriza `adjusted_end_at` sobre `end_datetime`
- **Archivo:** AgendaController.php:63

### BUG-155: Carbon mutation corrompe duracion de siguiente sesion al retrasar (RESUELTO)
- **Severidad:** CRITICA — la siguiente sesion en el salon quedaba con duracion 0 minutos
- **Consecuencia:** Si Salon A tiene sesion de 60 min y se retrasa 10 min, la siguiente sesion pasaba de 60 min a 0 min de duracion (start y end iguales). En la agenda publica la sesion aparecia como instantanea, imposible de entrar al stream.
- **Causa:** `addMinutes()` de Carbon MUTA el objeto en lugar de crear uno nuevo. PHP evalua los valores del array secuencialmente: el primer valor muta el Carbon, el segundo usa el ya-mutado. Ambos campos (`start_datetime`, `end_datetime`) terminan apuntando al mismo valor.
- **Fix:** `->copy()->addMinutes()` para crear instancias independientes
- **Archivo:** SessionConfigController.php:350-354 (adjustNextSession)
- **Test nuevo:** `test_delay_preserves_next_session_duration`

---

## 2026-04-20 — Mission Control: Config Streaming + About Fix (7 bugs)

### BUG-154: Session stats crash — null component en infolist schema (RESUELTO)
- **Severidad:** ALTA — pagina /admin/event-sessions/{id}/stats crasheaba completamente
- **Causa:** Secciones condicionales (top question, polls) retornaban `null` en el array del schema cuando no habia datos. Filament no acepta null como componente.
- **Fix:** Spread operator `...($condition ? [Section] : [])` en vez de ternario con null
- **Archivo:** ViewSessionStats.php
- **Commit:** e9eee0a

### BUG-153: MC tareas texto poco legible — color gris medio (RESUELTO)
- **Severidad:** BAJA — texto de tareas usaba var(--t2) gris, poca legibilidad
- **Fix:** Cambiado a var(--t) blanco principal
- **Archivo:** styles.css

### BUG-152: MC about section margenes vacios sin speakers/descripcion (RESUELTO)
- **Severidad:** BAJA — divs vacios dejaban margin-bottom:10px sin contenido visible
- **Causa:** aboutSpeaker y aboutDesc se renderizaban como elementos vacios
- **Fix:** display:none cuando no hay contenido
- **Archivo:** app.js

### BUG-151: MC stream_iframe acepta HTML arbitrario — vector XSS (RESUELTO)
- **Severidad:** ALTA — admin podia inyectar scripts via campo stream_iframe
- **Causa:** Validacion solo era nullable|string|max:2000, sin sanitizacion
- **Fix:** strip_tags() solo permite tag iframe
- **Archivo:** SessionConfigController.php

### BUG-150: MC apiFetch crasheaba con respuestas error no-JSON (RESUELTO)
- **Severidad:** MEDIA — si el server devuelve HTML en error 500, r.json() lanzaba uncaught exception
- **Causa:** apiFetch asumia que todas las respuestas error eran JSON
- **Fix:** Verificar content-type antes de parsear JSON en errores
- **Archivo:** app.js

### BUG-149: MC custom_url en modal config causaba error 422 (RESUELTO)
- **Severidad:** ALTA — al guardar config streaming, custom_url pasaba por validacion de dominio whitelist y fallaba
- **Causa:** El modal enviaba custom_url que se valida con validateEmbedDomain. Campo duplicado con tab Custom
- **Fix:** Eliminado custom_url del modal config (ya se gestiona en tab Custom)
- **Archivo:** app.js, index.html

### BUG-148: MC boton config streaming invisible en desktop (RESUELTO)
- **Severidad:** MEDIA — boton usaba clase mc-drawer-btn que tiene display:none en desktop
- **Causa:** Clase compartida con el boton hamburguesa del sidebar, oculto hasta tablet
- **Fix:** Nueva clase mc-header-btn con display:flex siempre visible
- **Archivo:** index.html, styles.css

---

## 2026-04-20 — Sesion Quick Wins + Stand Stats + QA (7 bugs)

### BUG-147: Tests BanTest fallaban por activated_at null (RESUELTO)
- **Severidad:** BAJA — solo tests, no produccion
- **Causa:** BanController ahora requiere `activated_at` para enviar push, pero el test creaba users sin ese campo
- **Fix:** Agregar `['activated_at' => now()]` al factory del target user
- **Archivo:** `tests/Feature/Admin/BanTest.php:30`
- **Commit:** 53a535c

### BUG-146: handleRefresh sin error handling — UI congelada si refetch falla (RESUELTO)
- **Severidad:** MEDIA — pull-to-refresh se queda girando infinitamente si hay error de red
- **Causa:** `await refetch()` sin try/catch, `setRefreshing(false)` nunca se ejecutaba en caso de error
- **Fix:** Wrap en `try { await refetch(); } finally { setRefreshing(false); }`
- **Archivos:** `stand-stats.tsx:137`, `stand-contacts.tsx:146`
- **Commit:** ed1f83a

### BUG-145: resolveAvatarUrl llamada con firma incorrecta en stand-contacts (RESUELTO)
- **Severidad:** MEDIA — fotos de solicitudes de contacto se veian en blanco
- **Causa:** `resolveAvatarUrl(att.name)` pasaba nombre como primer argumento (photoUrl), funcion interpretaba el nombre como URL y `fixStorageUrl("Laura Martinez")` retornaba URL invalida
- **Fix:** Cambiar a `resolveAvatarUrl(att.photo_url, att.name)`
- **Archivo:** `app/(app)/stand-contacts.tsx:52`
- **Commit:** ed1f83a

### BUG-144: Polling innecesario en encuestas, gamification y passport (RESUELTO)
- **Severidad:** MEDIA — 6,000 req/min con 500 usuarios sin necesidad
- **Causa:** `refetchInterval` de 15-30s en 3 hooks aunque encuestas ya tenia socket y gamification/passport no necesitaban polling
- **Fix:** Eliminado refetchInterval, agregado socket invalidation targeted via `broadcastToAttendee()`. Leaderboard usa `staleTime: 60s` + `refetchOnWindowFocus`
- **Archivos:** `encuestas.tsx:37`, `useGamification.ts:58,71`, `usePassport.ts:32`, `useDataInvalidation.ts:14`, `PointsService.php:104`, `LeadController.php:159`
- **Commit:** 53a535c + ed1f83a

### BUG-143: Session detail UI inconsistente — colores hardcodeados (RESUELTO)
- **Severidad:** MEDIA — pantalla se veia diferente al resto en dark/light mode
- **Causa:** Card de hora/ubicacion usaba `rgba(255,255,255,0.03)`, botones usaban `#FFF0F3`, `#F0F2F5`, fotos con `#151515` hardcoded en vez de tokens del theme
- **Fix:** Reemplazado por GlassCard, GlassButton, `surface.backgroundElevated`, `surface.low`
- **Archivo:** `app/(app)/session/[id].tsx`
- **Commit:** ed1f83a

### BUG-142: Push ban llega aunque usuario no inicio sesion / esta en onboarding (RESUELTO)
- **Severidad:** ALTA — notificacion de ban en onboarding, UX confuso
- **Causa:** 3 problemas: (1) push token se registraba durante onboarding antes de completar, (2) BanController enviaba push sin verificar `activated_at`, (3) NotificationController no filtraba usuarios baneados en notificaciones masivas
- **Fix:** (1) Guard `onboarding_seen` en useNotifications, (2) check `activated_at` en BanController, (3) `whereDoesntHave('activeBan')` en NotificationController
- **Archivos:** `hooks/useNotifications.ts:102`, `BanController.php:69`, `NotificationController.php:80`
- **Commit:** 53a535c + ed1f83a

### BUG-141: Reload Expo manda al onboarding — race condition hydrate (RESUELTO)
- **Severidad:** ALTA — usuario autenticado pierde sesion al recargar
- **Causa:** `(app)/_layout.tsx` evaluaba `if (!token)` antes de que `hydrate()` terminara de leer de SecureStore. Token era `null` por defecto, no porque el user no estuviera autenticado.
- **Fix:** Agregar `if (!isHydrated) return null` antes del check de token
- **Archivo:** `app/(app)/_layout.tsx:55`
- **Commit:** ed1f83a

---

## 2026-04-20 — QA Hallazgos (no bugs, mejoras pendientes)

- **QA-01:** Colores hardcodeados trending — RESUELTO: constante STATUS_COLORS en stand-stats.tsx
- **QA-02:** require('expo-router') dinamico — RESUELTO: import estatico `import { router } from 'expo-router'`
- **QA-03:** debounceTimers leak — RESUELTO: cleanup en return del useEffect (clearTimeout + batchTimer)

---

## 2026-04-19 — Sesion Mission Control Display + Metricas + Moderacion (13 bugs)

### BUG-140: results endpoint crash por first_name en attendees (RESUELTO)
- **Severidad:** ALTA — pending_answers query fallaba con "Unknown column first_name"
- **Causa:** Query usaba `with('attendee:id,first_name,last_name')` pero attendees no tiene esos campos, el nombre viene de User
- **Fix:** Cambiar a `with('attendee.user:id,name')`
- **Archivo:** PollController.php (results method)

### BUG-139: open_text answers sin moderacion en display publico (RESUELTO)
- **Severidad:** CRITICA — cualquier texto escrito por asistentes salia directamente en pantalla LED sin filtro
- **Causa:** No existia sistema de moderacion para respuestas de texto abierto
- **Fix:** Campo `is_approved` en live_poll_votes (default false para open_text), modal de moderacion en MC, endpoints approve/reject/batch, display solo muestra aprobadas
- **Archivo:** Migracion, LivePollVote model, PollController, app.js, styles.css, index.html

### BUG-138: YouTube iframe overlay bloqueaba controles (RESUELTO)
- **Severidad:** MEDIA — no se podia subir volumen ni interactuar con player YouTube
- **Causa:** `.mc-stream-top` con z-index:2 cubria toda la parte superior del iframe
- **Fix:** `pointer-events:none` en el overlay, `pointer-events:auto` solo en los hijos (chip, fullscreen btn)
- **Archivo:** public/mission-control/styles.css

### BUG-137: navigator.clipboard undefined en HTTP (RESUELTO)
- **Severidad:** MEDIA — boton copiar enlace LED no funcionaba en desarrollo (HTTP)
- **Causa:** `navigator.clipboard` solo existe en contextos seguros (HTTPS/localhost)
- **Fix:** Fallback con `textarea + execCommand('copy')` para HTTP
- **Archivo:** public/mission-control/app.js (copyText function)

### BUG-136: Keyboard shortcuts activos en TEXTAREA (RESUELTO)
- **Severidad:** BAJA — escribir "1" en pin modal cambiaba al tab Chat
- **Causa:** Filter solo excluia INPUT y SELECT, no TEXTAREA
- **Fix:** Agregar TEXTAREA al filtro de keydown
- **Archivo:** public/mission-control/app.js

### BUG-135: apiFetch crash en DELETE sin JSON body (RESUELTO)
- **Severidad:** MEDIA — rechazar respuesta podia crashear si backend devolvia 204 o body vacio
- **Causa:** `r.json()` siempre se llamaba sin verificar content-type
- **Fix:** Verificar content-type contiene `application/json` y status !== 204 antes de parsear
- **Archivo:** public/mission-control/app.js (apiFetch)

### BUG-134: chat:history sobreescribia msgCount de Redis (RESUELTO)
- **Severidad:** MEDIA — counter de mensajes bajaba de 500 a 20 al reconectar
- **Causa:** `msgCount = msgs.length` (historial max 20) reemplazaba el count real de Redis
- **Fix:** Solo actualizar msgCount desde historial si Redis no ha enviado un count mayor
- **Archivo:** public/mission-control/app.js

### BUG-133: Duplicate socket.on('poll:updated') listener en MC (RESUELTO)
- **Severidad:** BAJA — doble fetch de resultados en cada update, race condition potencial
- **Causa:** Un listener para handlePollUpdate (linea 474) y otro para refreshModList (linea 877)
- **Fix:** Integrar refresh del modal dentro de handlePollUpdate, eliminar listener duplicado
- **Archivo:** public/mission-control/app.js

### BUG-132: Respuestas aprobadas en batch salen de golpe en display (RESUELTO)
- **Severidad:** MEDIA — 10 respuestas aprobadas simultaneamente aparecian todas de golpe, no se leian
- **Causa:** Display renderizaba todas las nuevas inmediatamente sin delay
- **Fix:** Cola de presentacion client-side (textQueue): despacha 1 respuesta cada 1.8s, el moderador aprueba cuando quiera
- **Archivo:** public/display/session.html (textQueue, drainTextQueue, showNextText)

### BUG-131: Aprobar todas dispara rate limit 429 (RESUELTO)
- **Severidad:** ALTA — boton "Aprobar todas" en MC lanzaba N requests simultaneos, Laravel rate limiter los bloqueaba
- **Causa:** `Promise.all(ids.map(id => apiFetch(...)))` — un POST por voto
- **Fix:** Nuevo endpoint `POST /admin/polls/votes/approve-batch` que acepta array vote_ids, 1 sola query
- **Archivo:** PollController.php (approveVoteBatch), routes/api/admin.php, app.js

### BUG-130: Open text empuja titulo fuera de pantalla (RESUELTO)
- **Severidad:** ALTA — respuestas crecian infinito, titulo desaparecia del viewport
- **Causa:** Container sin altura fija ni overflow hidden, items se acumulaban sin limite
- **Fix:** `.d-poll` con flex column height:100%, `.d-text-scroll` con flex:1 overflow:hidden, mask-image gradient top, max 20 items DOM
- **Archivo:** public/display/session.html (CSS + JS)

### BUG-129: Animaciones display se resetean en cada voto (RESUELTO)
- **Severidad:** MEDIA — barras parpadean y se reinician con cada poll:updated
- **Causa:** `renderPollResults` usaba `innerHTML` para reconstruir todo el DOM en cada update, reiniciando CSS animations
- **Fix:** Separar `renderPollFull` (primera carga con animaciones) de `updatePollInPlace` (solo cambia widths y counters via CSS transition)
- **Archivo:** public/display/session.html

### BUG-128: Display LED pierde proyeccion al refrescar (RESUELTO)
- **Severidad:** ALTA — operador de pantalla pierde contenido al recargar browser
- **Causa:** Estado de proyeccion (que poll se muestra) solo existia como evento socket transitorio, sin persistencia
- **Fix:** Guardar en Redis `display:active:session:{id}` (TTL 4h) al proyectar. En join:session, socket envia estado guardado al display
- **Archivo:** eventos-socket/src/chat.ts (display:project handler + join:session)

---

## 2026-04-18 — Sesion Mission Control QA Live (28 bugs)

### BUG-127: Mission Control metricas se pierden al refrescar (PENDIENTE)
- **Severidad:** MEDIA — msgCount vuelve a 0 al recargar pagina
- **Causa:** Counter es variable JS client-side. Redis guarda ultimos 20 mensajes pero no el count total.
- **Fix pendiente:** Redis INCR `chat:count:session:{id}` por mensaje. Leer al cargar MC.

### BUG-126: Toast emoji engana — dice "activado" sin guardar (RESUELTO)
- **Severidad:** MEDIA — moderador cree que emoji only esta activo pero no se guardo
- **Causa:** Toggle mostraba toast inmediato pero el cambio solo se aplicaba al dar "Guardar" manualmente
- **Fix:** Auto-save al togglear emoji only y slow mode (llama `saveConfig()` directamente)
- **Archivo:** `public/mission-control/app.js`

### BUG-125: Mission Control poll form muestra opciones en star_rating/open_text (RESUELTO)
- **Severidad:** MEDIA — UX confusa, inputs de opciones visibles cuando no aplican
- **Causa:** Al cambiar tipo de encuesta en el selector, los inputs de "Opcion 1/2" no se ocultaban
- **Fix:** `updatePollFormType()` oculta `#pollOptsContainer` y `#pollAddOpt` cuando tipo no es multiple_choice
- **Archivo:** `public/mission-control/app.js`

### BUG-124: App crash RangeError status 0 cuando backend/socket cae (RESUELTO)
- **Severidad:** CRITICA — app crashea con `RangeError: Failed to construct 'Response': status 0`
- **Causa:** `fetch()` lanza error crudo cuando servidor no responde (network error, timeout). React Native intenta construir `Response` con status 0 (fuera del rango 200-599). React Query reintentaba el error crudo.
- **Fix:** api.ts: catch en fetch → `ApiError(0, null, msg, 'NETWORK_ERROR')` tanto en `request()` como `upload()`. React Query retry inteligente: no reintenta 401/403/404/422, si reintenta network errors y 5xx hasta 2 veces.
- **Archivos:** `lib/api.ts`, `app/_layout.tsx`

### BUG-123: PinnedBanner invisible en ambos temas (RESUELTO)
- **Severidad:** ALTA — mensaje anclado no se ve, feature roto visualmente
- **Causa:** `surface.medium` es `rgba(255,255,255,0.04)` en Noir y `rgba(0,0,0,0.04)` en Lux — ambos invisibles. Ademas PinnedBanner creaba su PROPIO socket (conexion duplicada innecesaria).
- **Fix:** Reescrito: recibe message/author como props (sin socket propio). Usa `surface.backgroundElevated` (visible). Integrado dentro de ChatPanel.tsx y session-chat/[id].tsx. Eliminado de session-stream/[id].tsx.
- **Archivos:** `components/ui/PinnedBanner.tsx`, `components/screens/ChatPanel.tsx`, `app/(app)/session-chat/[id].tsx`, `app/(app)/session-stream/[id].tsx`

### BUG-122: PollSlides estrellas rojas en Lux — accent del evento como color estrella (RESUELTO)
- **Severidad:** MEDIA — estrellas se ven rojas/feas cuando accent del evento es rojo
- **Causa:** StarRating usaba `accent` del evento como color de estrellas. Si accent=#ff0000, estrellas rojas. Ademas sombras `theme.shadow.sm` en Lux creaban cuadrados blancos feos.
- **Fix:** Color gold fijo `#F5B740` para estrellas (no depende de accent). Sombras eliminadas de options y container. Blur Lux reducido de 60 a 10, fondo 95% opaco.
- **Archivo:** `components/screens/PollSlides.tsx`

### BUG-121: cooldown no se limpia cuando slowModeSeconds cambia a 0 (RESUELTO)
- **Severidad:** MEDIA — usuario queda con countdown activo aunque admin desactivo slow mode
- **Fix:** useEffect con dependencia slowModeSeconds, clearInterval si <= 0
- **Archivo:** ChatPanel.tsx

### BUG-120: response unwrapping incorrecto en useSessionConfig (RESUELTO)
- **Severidad:** ALTA — fallback resp podria mergear shape incorrecto al config
- **Fix:** Usar resp?.data con validacion typeof object
- **Archivo:** useSessionConfig.ts

### BUG-119: token flood en personal_access_tokens (RESUELTO)
- **Severidad:** MEDIA — cada visita al monitor creaba token sin limpiar
- **Fix:** Eliminar tokens MC anteriores de la misma sesion antes de crear nuevo
- **Archivo:** routes/web.php

### BUG-118: sesiones soft-deleted no excluidas en ruta monitor (RESUELTO)
- **Severidad:** ALTA — podria servir monitor de sesion eliminada
- **Fix:** Usar withoutTrashed() explicito en findOrFail
- **Archivo:** routes/web.php

### BUG-117: evento inactivo permite abrir monitor (RESUELTO)
- **Severidad:** MEDIA — monitor funciona en eventos pausados/cancelados
- **Fix:** Verificar event.is_active antes de generar token
- **Archivo:** routes/web.php

### BUG-116: custom_enabled + interactive_mode coexisten sin validacion (RESUELTO)
- **Severidad:** BAJA — estado inconsistente en BD
- **Fix:** Si custom_enabled=true, forzar interactive_mode=none en controller
- **Archivo:** SessionConfigController.php

### BUG-115: Poll results sin validacion de shape (RESUELTO)
- **Severidad:** MEDIA — si r.questions no existe, rendering crashea
- **Fix:** Verificar r && r.questions antes de cachear
- **Archivo:** mission-control/app.js

### BUG-114: API response sin validacion de shape en config load (RESUELTO)
- **Severidad:** MEDIA — si API retorna shape inesperado, falla silenciosamente
- **Fix:** Validar r.data existe y es object antes de usar
- **Archivo:** mission-control/app.js, useSessionConfig.ts

### BUG-113: onclick inline en botones Q&A moderacion (RESUELTO)
- **Severidad:** MEDIA — onclick con q.id y status en strings HTML
- **Fix:** Event delegation con data-qid y data-status en qnaList
- **Archivo:** mission-control/app.js

### BUG-112: onclick inline en lista baneados del monitor (RESUELTO)
- **Severidad:** MEDIA — anti-pattern XSS, onclick con datos dinamicos
- **Fix:** Event delegation con data-aid en bannedList
- **Archivo:** mission-control/app.js

### BUG-111: PollSlides colores hardcoded no coinciden con tema (PENDIENTE PARCIAL)
- **Severidad:** MEDIA — encuesta se ve con accent rojo, fondo blanco inconsistente
- **Causa:** PollSlides usaba rgba dark hardcoded. Migrado a useTheme() pero el accent del evento estaba en #ff0000.
- **Fix parcial:** Migrar a useTheme(). Accent cambiado a #1A1A1A. Falta pulido visual.
- **Archivo:** PollSlides.tsx, PollPanel.tsx, QnAPanel.tsx

### BUG-110: poll sync — PollSlides mantenia state del poll anterior (RESUELTO)
- **Severidad:** ALTA — al lanzar nueva encuesta, app mostraba "Gracias por responder" del poll cerrado
- **Causa:** React reutilizaba el componente PollSlides sin reiniciar `useState(currentIdx)`. El currentIdx quedaba en totalQ del poll anterior.
- **Fix:** Agregar `key={activePoll.id}` en PollPanel para forzar remount
- **Archivo:** PollPanel.tsx, useChat.ts
- **Commit:** ca590f9

### BUG-109: emitConfigUpdate usaba secret incorrecto (RESUELTO)
- **Severidad:** ALTA — socket server rechazaba config updates del backend (401)
- **Causa:** Controller usaba `config('services.socket.secret')` que era vacio. El correcto es `config('services.socket.internal_secret')`
- **Fix:** Cambiar a `internal_secret`
- **Archivo:** SessionConfigController.php
- **Commit:** bfeccf1

### BUG-108: abort(422) retornaba HTML en API JSON (RESUELTO)
- **Severidad:** MEDIA — clientes recibían HTML en vez de JSON al enviar URL no permitida
- **Causa:** `abort(422, 'mensaje')` genera respuesta HTML por defecto en Laravel
- **Fix:** Usar `ValidationException::withMessages()` que retorna JSON
- **Archivo:** SessionConfigController.php
- **Commit:** 4e8caaf

### BUG-107: '' literal not terminated — script tag roto por HTML en string JS (RESUELTO)
- **Severidad:** CRITICA — monitor no cargaba, todo el JS fallaba
- **Causa:** HTML embebido en strings JS (srcdoc con `</style></head>`) rompia el parser HTML del browser
- **Fix:** Separar en archivos: mission-control/index.html + styles.css + app.js
- **Archivo:** public/mission-control/*
- **Commit:** 201aeb1

### BUG-106: cookies same-origin causaban 403 en fetch (RESUELTO)
- **Severidad:** CRITICA — PATCH/GET desde monitor daba 403 siempre en browser
- **Causa:** Browser envia cookies automaticamente en same-origin fetch (default). Laravel statefulApi priorizaba cookie sesion web (usuario anonimo) sobre Bearer token.
- **Fix:** `credentials: 'omit'` explicito en fetch — Bearer token only
- **Archivo:** mission-control/app.js
- **Commit:** 87427a6

### BUG-105: XSS en onclick inline del chat monitor (RESUELTO)
- **Severidad:** CRITICA — inyeccion de codigo via mensajes de chat con comillas
- **Causa:** `escAttr()` con comillas simples en onclick inline no era seguro. HTML entities se decodifican dentro de atributos.
- **Fix:** Event delegation con data attributes + msgDataMap (no mas onclick inline)
- **Archivo:** chat-monitor.html
- **Commit:** 4e8caaf

### BUG-104: panel switching no funcionaba en monitor HTML (RESUELTO)
- **Severidad:** ALTA — click en tabs Q&A/Polls/Custom no cambiaba el panel visible
- **Causa:** CSS clase `.panel` era demasiado generica (conflicto con panel-header del sidebar). Luego display:none/flex peleaba con clase .visible.
- **Fix:** Usar IDs directos con style.display + setTimeout para clase .active
- **Archivo:** chat-monitor.html (luego mission-control/app.js)
- **Commit:** varios

### BUG-103: CSRF 419 en PATCH desde Mission Control monitor (RESUELTO)
- **Severidad:** ALTA — no se podia guardar cambios desde el monitor
- **Causa:** HTML servido por ruta web Laravel seteaba cookies de sesion. fetch enviaba cookies (same-origin). Laravel statefulApi aplicaba CSRF verification al detectar cookie.
- **Fix:** `credentials: 'omit'` en fetch + `validateCsrfTokens(except: ['api/*'])`
- **Archivo:** mission-control/app.js, bootstrap/app.php
- **Commit:** 87427a6

### BUG-102: conflicto interactive_mode vs chat_enabled/qna_enabled (RESUELTO)
- **Severidad:** CRITICA — dos sistemas paralelos controlaban lo mismo, app no cambiaba panel
- **Causa:** Mission Control usaba campos boolean (chat_enabled, qna_enabled, polls_enabled) mientras Filament usaba interactive_mode. Ambos emitian eventos socket diferentes. App confundida.
- **Fix:** Eliminar booleans del config. Monitor usa interactive_mode (mismo que Filament). Observer emite session:mode_changed automaticamente.
- **Archivo:** SessionConfigController.php, session-stream/[id].tsx, useSessionMode.ts
- **Commit:** 97d968e

### BUG-101: HMAC route usaba whereHas('roles') inexistente (RESUELTO)
- **Severidad:** ALTA — ruta /monitor/{id} siempre daba 500 "No hay administrador"
- **Causa:** La query usaba `whereHas('roles', ...)` pero Attendee no tiene relacion `roles`, usa campo `role` directo
- **Fix:** Cambiar a `whereIn('role', ['admin', 'organizer', 'moderator'])`
- **Archivo:** `routes/web.php` ruta /monitor
- **Commit:** c18fcc5

### BUG-100: role 'admin' no reconocido en requireModerator Q&A (RESUELTO)
- **Severidad:** ALTA — admin no podia moderar preguntas Q&A
- **Causa:** `requireModerator` en QuestionController solo aceptaba `organizer` y `moderator`, no `admin`
- **Fix:** Agregar `admin` al array de roles permitidos
- **Archivo:** `QuestionController.php:194`, `RatingController.php:117`
- **Commit:** c18fcc5

---

## 2026-04-16 — Sesion Light Mode + Migracion Tokens

### BUG-099: Networking sendRequest crashea "Cannot read property map of undefined" (RESUELTO)
- **Severidad:** ALTA — feature completamente roto, no se podia enviar solicitud de contacto
- **Causa:** `useSendContactRequest` onMutate optimistic update asumia estructura plana (`old.data.map()`) pero `useDirectory` usa `useInfiniteQuery` con estructura `{ pages: [{ data: [...] }] }`. Al acceder `old.data` en infinite query, era `undefined` → `.map()` crasheaba
- **Origen:** Bug pre-existente desde el revert del swipe networking (2026-04-15). Al implementar infinite query en directorio, el onMutate no se actualizo
- **Fix:** Cambiar `old.data.map(...)` por `old.pages.map(page => ({ ...page, data: page.data.map(...) }))`
- **Archivo:** `hooks/useNetworking.ts` linea 73-84
- **Commit:** 6124f0f

---

## 2026-04-14 — Sesion Onboarding Replay + Session Detail + Bancolombia (21 bugs)

### BUG-098: activate endpoint 500 con token ya usado (RESUELTO)
- **Severidad:** MEDIA — excepcion 500 en vez de error limpio 422
- **Causa:** `firstOrFail()` lanza ModelNotFoundException cuando token ya consumido
- **Fix:** Usar `first()` + ValidationException con mensaje claro
- **Archivo:** `AuthService.php:activateAccount()`

### BUG-097: invitation_token expuesto sin verificacion (RESUELTO)
- **Severidad:** MEDIA — token entregado a cualquiera que conozca el email
- **Causa:** check-email devolvia token directo sin verificar identidad
- **Fix:** Si evento tiene `activation_verify_field`, check-email no devuelve token. Nuevo endpoint `POST /auth/verify-identity` valida dato personal antes de entregar token.
- **Archivos:** `AuthController.php`, `AuthStep.tsx`, `authApi.ts`, `RegistrationSettingsResource.php`

### BUG-096: Mensajes API en ingles (RESUELTO)
- **Severidad:** BAJA — "Profile updated" y "Photo removed" aparecian en ingles
- **Fix:** Traducir a "Perfil actualizado." y "Foto eliminada."
- **Archivo:** `ProfileController.php`

### BUG-095: Access code no sanitiza espacios al pegar (RESUELTO)
- **Severidad:** MEDIA — usuario pega codigo con espacio, falla validacion
- **Causa:** Ni el backend ni la app eliminaban whitespace del codigo pegado
- **Fix:** Backend: `preg_replace('/\s+/', '', $value)` + merge limpio. App: `.replace(/\s/g, '')` antes de enviar.
- **Archivos:** `RegisterRequest.php`, `AuthStep.tsx`

### BUG-094: AccessCode race condition en registerUse (RESUELTO)
- **Severidad:** MEDIA — dos registros simultaneos podian exceder max_uses
- **Causa:** `increment('uses_count')` no verificaba max_uses atomicamente
- **Fix:** WHERE atomico con `uses_count < max_uses` en el UPDATE, retorna bool
- **Archivo:** `AccessCode.php:registerUse()`

### BUG-093: invitation_token reutilizable tras intercepcion (RESUELTO)
- **Severidad:** MEDIA — token interceptado podia usarse despues
- **Causa:** check-email devolvia el mismo token siempre, tokens viejos seguian siendo validos
- **Fix:** Regenerar token en cada check-email request. Token anterior se invalida automaticamente.
- **Archivo:** `AuthController.php`

### BUG-092: Session Detail favorite sin error handling (RESUELTO)
- **Severidad:** BAJA — toggle falla sin informar al usuario
- **Causa:** `toggleFavorite.mutate()` sin onError callback
- **Fix:** Agregar `onError` con toast de error
- **Archivo:** `session/[id].tsx`

### BUG-091: FormStep pre-fill falla silenciosamente (RESUELTO)
- **Severidad:** BAJA — usuario no sabe por que campos estan vacios en replay
- **Causa:** `.catch(() => {})` sin feedback al usuario
- **Fix:** Toast de error "No se pudieron cargar tus datos previos"
- **Archivo:** `FormStep.tsx`

### BUG-090: InterestsStep replay — IDs huerfanos no validados (RESUELTO)
- **Severidad:** MEDIA — IDs de opciones eliminadas quedaban seleccionados invisiblemente
- **Causa:** `selected_ids` del API se cargaban sin verificar que existieran en opciones actuales
- **Fix:** Filtrar selected_ids contra IDs validos de options. Esperar a que options carguen antes de fetch.
- **Archivo:** `InterestsStep.tsx`

### BUG-089: PhotoStep replay — foto aparece sin animacion (RESUELTO)
- **Severidad:** BAJA — cosmetic, foto aparece de golpe sin fade-in
- **Causa:** `photoOpacity` se inicializaba a 1 si photoUri existia, pero en replay se seteaba despues del init
- **Fix:** Inicializar opacity a 0, animar a 1 cuando se pre-carga la foto en replay
- **Archivo:** `PhotoStep.tsx`

### BUG-088: registrationApprovedAt fallback sobreescribe dato fresco (RESUELTO)
- **Severidad:** ALTA — usuario con aprobacion revocada podia entrar con cache viejo
- **Causa:** `att?.registration_approved_at ?? user?.registrationApprovedAt` — si servidor devuelve null (pendiente), `??` salta al cache que podia tener 'auto'
- **Fix:** Priorizar dato del attendee fresco; fallback al cache solo cuando no hay attendee
- **Archivo:** `app/index.tsx`

### BUG-087: dynamicOptions stale al cambiar pais en FormStep (RESUELTO)
- **Severidad:** MEDIA — ciudades del pais anterior persisten si API falla
- **Causa:** Al cambiar parent field, se reseteaba el valor del child pero no se limpiaban las opciones dinamicas en `dynamicOptions` state
- **Fix:** Clear `dynamicOptions[field.key]` junto con el reset del child value
- **Archivo:** `FormStep.tsx`

### BUG-086: isReplay/postActivation se pierden en re-renders (RESUELTO)
- **Severidad:** ALTA — replay no funciona (confetti, campos vacios)
- **Causa:** Flags leidos con `getCached()` en cuerpo del componente y borrados inmediatamente. En re-renders, valor era false.
- **Fix:** Usar `useState(() => ...)` con lazy initializer para persistir valor inicial
- **Archivo:** `OnboardingContext.tsx`

### BUG-085: Onboarding custom fields (country/city) no se guardan (RESUELTO)
- **Severidad:** MEDIA — datos de ubicacion se perdian
- **Causa:** FormStep enviaba string keys ("country") a `PUT /me/registration-fields`, pero backend esperaba numeric field IDs
- **Fix:** Nueva columna `onboarding_data` JSON en attendees + endpoints `GET/PUT /me/onboarding-data`
- **Archivos:** Migration, `Attendee.php`, `ProfileController.php`, `api.php`, `FormStep.tsx`

### BUG-084: Onboarding replay — campos vacios, confetti, puntos dobles (RESUELTO)
- **Severidad:** ALTA — 8 sub-bugs en flujo "Ver introduccion de nuevo"
- **Causa:** No habia distincion entre primera vez y replay. Mismo flag `post_activation_onboarding` para ambos flujos
- **Sub-bugs:**
  1. FormStep campos siempre vacios (no pre-fill de profile)
  2. InterestsStep selecciones vacias (no carga my-interests)
  3. PhotoStep no muestra foto actual del usuario
  4. Confetti aparece siempre (sin sentido en replay)
  5. Puntos se otorgan de nuevo (gamificacion inflada)
  6. Titulo dice "Bienvenido" en vez de "Datos actualizados"
  7. Hints repiten info obvia
  8. "Saltar" enganoso (campos vacios sugieren datos perdidos)
- **Fix:** Flag separado `replay_onboarding`, `isReplay` en contexto, pre-fill desde APIs existentes
- **Archivos:** `ProfileScreen.tsx`, `OnboardingContext.tsx`, `PhotoStep.tsx`, `FormStep.tsx`, `InterestsStep.tsx`, `DoneStep.tsx`

### BUG-083: Skip button cuenta campos ocultos (depends_on) (RESUELTO)
- **Severidad:** MEDIA — UX confusa, boton skip desaparece incorrectamente
- **Causa:** `config.fields.some(f => f.required)` incluia campos hidden por depends_on
- **Fix:** Usar `visibleFields` en vez de `config.fields`
- **Archivo:** `FormStep.tsx`

### BUG-082: Fetch cities en cada keystroke de FormStep (RESUELTO)
- **Severidad:** MEDIA — llamadas API innecesarias, rendimiento
- **Causa:** useEffect para cities dependia de `[values]` completo, se ejecutaba en cada cambio de cualquier campo
- **Fix:** Unificar effects, solo ejecutar cuando parent value realmente cambia via prevParentValues ref
- **Archivo:** `FormStep.tsx`

### BUG-081: index.tsx registrationApprovedAt null sin fallback (RESUELTO)
- **Severidad:** ALTA — redirige a pending-approval incorrectamente
- **Causa:** Si `/auth/me` retorna attendee null, `registrationApprovedAt` queda null y redirige a pending-approval
- **Fix:** Fallback a `user?.registrationApprovedAt` del cache cuando attendee es null
- **Archivo:** `app/index.tsx:80`

### BUG-080: index.tsx fetch sin timeout (RESUELTO)
- **Severidad:** ALTA — pantalla negra indefinida si backend no responde
- **Causa:** `fetch('/auth/me')` sin AbortController, emulador con red lenta cuelga forever
- **Fix:** Agregar AbortController con timeout 6s + clearTimeout en finally
- **Archivo:** `app/index.tsx:34`

### BUG-079: Login no pasa event_slug al backend (RESUELTO)
- **Severidad:** ALTA — eventId queda null, home carga vacia
- **Causa:** `authApi.login()` no incluia `event_slug`, backend adivinaba el evento por `is_active` fallback
- **Fix:** Agregar `event_slug: DEFAULT_EVENT_SLUG` al payload de login en AuthStep
- **Archivo:** `AuthStep.tsx:213`

### BUG-078: API onboarding crashea con array_flip en preset cities (RESUELTO)
- **Severidad:** CRITICA — API devuelve 500, onboarding no carga
- **Causa:** `array_flip()` en `resolveStepsConfigUrls()` falla con preset `cities` que es array anidado (country→array de ciudades)
- **Fix:** Skip campos con `depends_on` en la resolucion + verificar `is_string` antes de `array_flip`
- **Archivo:** `OnboardingController.php:170`

---

## 2026-04-12 — Sesion Moderacion + Auth + Error Handling (16 bugs)

### BUG-077: Imagenes Filament no se muestran en app (RESUELTO)
- **Severidad:** ALTA (feature roto)
- **Causa:** Filament guarda path relativo (`onboarding/archivo.png`), app necesita URL completa. Ademas hostname `eventos-backend.test` no resuelve desde dispositivo.
- **Fix:** `resolveStepsConfigUrls()` en API convierte a URL con `asset()`. App aplica `fixStorageUrl()` para reemplazar hostname por IP.
- **Nota:** En produccion con Cloudflare R2, las URLs seran absolutas y este fix no sera necesario. Documentar en DISPONIBILIDAD-HA.md al migrar a R2.

### BUG-076: Titulo/subtitulo visible cuando hero image cubre todo (RESUELTO)
- **Severidad:** MEDIA (UX/diseño)
- **Causa:** No habia forma de ocultar textos cuando el key visual hero ya comunicaba todo.
- **Fix:** Toggle `show_text` en Filament. Si false, solo muestra botones.

### BUG-075: Textos de botones welcome no editables (RESUELTO)
- **Severidad:** MEDIA (config)
- **Causa:** "Crear cuenta" y "Ya tengo cuenta" hardcoded en WelcomeStep.
- **Fix:** Campos `register_button_text` y `login_button_text` en Filament.

### BUG-074: Titulo welcome "Bienvenido a" no editable (RESUELTO)
- **Severidad:** MEDIA (config)
- **Causa:** `title_prefix` hardcoded en WelcomeStep. No habia campo en Filament.
- **Fix:** Campo `title_prefix` en Filament + lectura desde config con fallback.

### BUG-073: "No tienes cuenta? Registrate" visible sin registro habilitado (RESUELTO)
- **Severidad:** MEDIA (UX)
- **Causa:** AuthStep mostraba link toggle login/register sin verificar `show_register_button`.
- **Fix:** Condicional `(isRegister || showRegisterOption)` oculta el link si registro desactivado.

### BUG-072: ConnectionError boton se estira verticalmente (RESUELTO)
- **Severidad:** MEDIA (visual)
- **Causa:** Componente ConnectionError no tenia layout split (content center + boton bottom).
- **Fix:** Reestructurado identico a banned.tsx: content centrado + boton full-width abajo con SafeArea.

### BUG-071: Post-activation onboarding detecta token viejo (RESUELTO)
- **Severidad:** ALTA (flujo roto)
- **Causa:** OnboardingContext usaba `hasToken` para detectar post-activacion. Cualquier token viejo activaba el salto a photo.
- **Fix:** Flag especifico `post_activation_onboarding` que solo activate-account setea, se consume una vez.

### BUG-070: Activate-account sin token deja pantalla sin salida (RESUELTO)
- **Severidad:** MEDIA (edge case)
- **Causa:** Deep link sin parametro `token` mostraba pantalla con campos pero sin poder enviar.
- **Fix:** Redirige a `/onboarding` inmediatamente si no hay token.

### BUG-069: Attendee null causa loop pending-approval (RESUELTO)
- **Severidad:** ALTA (edge case)
- **Causa:** Si backend devuelve `attendee: null`, `registrationApprovedAt` queda `null` y usuario queda atrapado.
- **Fix:** Si no hay attendee, `registrationApprovedAt = 'no_attendee'` (no bloquea).

### BUG-068: onboarding_seen no se limpia al logout (RESUELTO)
- **Severidad:** CRITICA (multi-usuario)
- **Causa:** `clearAuth()` no borraba el flag `onboarding_seen`. Segundo usuario en mismo dispositivo saltaba welcome.
- **Fix:** `clearAuth()` ahora limpia `onboarding_seen` y `post_activation_onboarding`.

### BUG-067: Onboarding fetch sin timeout (RESUELTO)
- **Severidad:** ALTA (UX)
- **Causa:** `onboardingApi.get()` usaba `fetch()` sin AbortController. Si Laravel esta caido, pantalla negra 30-120s.
- **Fix:** AbortController con timeout 6s + ConnectionError screen con boton reintentar.

### BUG-066: registrationApprovedAt null bloquea usuarios sin approval (RESUELTO)
- **Severidad:** CRITICA (flujo roto)
- **Causa:** `AttendeeResource` devuelve `registration_approved_at: null` cuando el evento NO requiere approval. App interpreta null como "no aprobado" y manda a pending-approval.
- **Fix:** Backend devuelve `'auto'` cuando `registration_requires_approval = false`.

### BUG-065: Login no verifica approval antes de mostrar QR (RESUELTO)
- **Severidad:** ALTA (flujo roto)
- **Causa:** AuthStep login iba directo a `goTo('done')` sin verificar `registrationApprovedAt`. Usuario no aprobado veia QR.
- **Fix:** Agregado check `registrationApprovedAt === null` en login flow + index.tsx.

### BUG-064: Toast vacio en pending-approval (RESUELTO)
- **Severidad:** MEDIA (UX)
- **Causa:** `toast.show('texto', 'variant')` — firma incorrecta, espera objeto `{ message, variant }`.
- **Fix:** Corregido en pending-approval.tsx y activate-account.tsx.

### BUG-063: Ban no se valida server-side (RESUELTO)
- **Severidad:** ALTA (seguridad)
- **Causa:** No habia middleware que chequeara ban en cada API call.
- **Fix:** Middleware `CheckBan` aplicado a todas las rutas autenticadas (excepto auth/me, auth/logout).

### BUG-062: Token registro 30d hardcoded (RESUELTO)
- **Severidad:** MEDIA (seguridad)
- **Causa:** `AuthService.php` register y activate usaban `addDays(30)` hardcoded.
- **Fix:** Reemplazado por `config('sanctum.expiration')` en ambos metodos.

### Bugs no criticos detectados (no corregidos — code smells)
- **CS-001:** Race condition en token refresh deduplication — multiples 401 pueden llamar clearAuth() dos veces. Sin impacto real.
- **CS-002:** Flag `post_activation_onboarding` se consume al montar provider — fragil si re-monta, pero no re-monta en flujo normal.
- **CS-003:** Email verified state se resetea al cambiar login/register — UX minor, email queda escrito.

---

## 2026-04-12 — Auditoria de Flujos Auth (39 escenarios + 10 edge cases)

### Escenarios verificados

**Flujo normal (10):** Primera vez UP/DOWN/retry, usuario regresa, token guardado, token expirado refresh/fail, logout.
**Login inteligente (7):** checkEmail not_found/pending_activation/active, password correcto/incorrecto, cuenta bloqueada, error red.
**Registro (5):** Exitoso sin/con approval, email existente, error red, logout+re-registro.
**Activate account (4):** Token valido, sin token, token usado, activacion completa.
**Ban (6):** Socket RT, HTTP 403, reabrir baneado, ban temporal expira, desbanear, ban desde chat.
**Pending approval (4):** Pantalla, verificar pendiente, verificar aprobado, reabrir pendiente.
**Edge cases (3):** Evento sin approval, attendee null, servidor cae mid-onboarding.

**Resultado:** 39/39 escenarios cubiertos. 9 bugs encontrados y corregidos (BUG-064 a BUG-072). 3 code smells documentados.

---

## 2026-04-11 — Sesion Onboarding Steps + Auth + Seguridad (5 bugs)

### BUG-061: Back arrow inconsistente (chevron-left vs arrow-left) (RESUELTO)
- **Severidad:** BAJA
- **Causa:** Onboarding usaba `chevron-left`, resto del app usa `arrow-left`.
- **Fix:** `77a85a4` — unificado a `arrow-left`.

### BUG-060: Logout redirige a login viejo NativeWind (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** `index.tsx` y `_layout.tsx` redirigian a `/(auth)/login` que fue eliminado.
- **Fix:** `9e47000` + `1bfc6c6` — redirige a `/onboarding`. Si `onboarding_seen=true`, empieza en auth step directo.

### BUG-059: Cambiar foto perfil no actualizaba MiQR (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** `useQrToken` tiene `staleTime: Infinity`. Al cambiar foto en perfil, MiQR seguia con la vieja.
- **Fix:** `9965312` — `invalidateQueries(['qr-token'])` al subir/eliminar foto.

### BUG-058: Foto upload no actualizaba authStore (RESUELTO)
- **Severidad:** ALTA (feature roto)
- **Causa:** `api.upload('/me/photo')` en PhotoStep no capturaba la respuesta. El store quedaba con `photoUrl: null`. Mi QR y Perfil mostraban beam avatar.
- **Fix:** `9e47000` — captura `photo_url` de respuesta y actualiza authStore.

### BUG-057: Password input autoCapitalize mayuscula en Android (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Campos password sin `autoCapitalize="none"` ni `autoCorrect={false}`. Android pone primera letra mayuscula.
- **Fix:** `585c8c4` — agregado en 7 inputs (AuthStep, login, register, activate-account).

---

## 2026-04-10 — Sesion Vendedor + Mi Stand + Liquid Glass (3 bugs)

### BUG-056: Teal color en Mi Stand card (solo gamification usa teal) (RESUELTO)
- **Severidad:** BAJA
- **Causa:** Mi Stand card usaba colores teal que estan reservados para gamification.
- **Fix:** `e04b62e` — colores unificados blancos, teal solo en GamificationHud.

### BUG-055: VendorHappeningNow ratio roto en flex (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** flex porcentual no daba anchos correctos para carousel + Mi Stand card.
- **Fix:** `e04b62e` — anchos fijos en pixeles (65%/32%/3% gap) en vez de flex.

### BUG-054: Rules of hooks en VendorHappeningNow (RESUELTO)
- **Severidad:** ALTA (crash)
- **Causa:** Hooks despues de early return violaba reglas de React.
- **Fix:** `e04b62e` — mover todos los hooks antes del early return.

---

## 2026-04-10 — Sesion UI + Rewards (8 bugs)

### BUG-053: Select sponsor vacio en Filament (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** options() con session variable null.
- **Fix:** `1ac843c` — relationship() con preload().

### BUG-052: FlashList v2 estimatedItemSize deprecated (RESUELTO)
- **Severidad:** BAJA
- **Causa:** FlashList v2 calcula tamaño automaticamente.
- **Fix:** `f3ed54a` — prop eliminada de social.tsx.

### BUG-051: Mutation de canje perdia contexto (RESUELTO)
- **Severidad:** ALTA
- **Causa:** setRedeemConfirm(null) cerraba sheet ANTES de mutation.
- **Fix:** `38cd0f5` — mutation primero, cerrar despues, setTimeout 400ms para QR.

### BUG-050: QR Modal de canje detras de BottomSheets (RESUELTO)
- **Severidad:** ALTA
- **Causa:** Overlay con absoluteFillObject dentro del View principal.
- **Fix:** `38cd0f5` — Modal real de RN con transparent + animationType fade.

### BUG-049: QR de canje salia redondo (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Usaba RgbRing (circular) en vez de componente rectangular.
- **Fix:** `6cb955f` — creado RgbRect con borderRadius: 16.

### BUG-048: RewardService usaba campo 'metadata' inexistente (RESUELTO)
- **Severidad:** ALTA
- **Causa:** points_log no tiene columna metadata, usa reference_type/reference_id.
- **Fix:** `4787934` — cambiado a reference_type: 'reward' + reference_id.

### BUG-047: points_log.points era unsigned — no aceptaba descuentos (RESUELTO)
- **Severidad:** ALTA
- **Causa:** `unsignedSmallInteger('points')` rechazaba -50 para canje.
- **Fix:** `4787934` — migration cambia a `smallInteger` (signed). award() permite negativos.

### BUG-046: SQL query expuesto al usuario en error de canje (RESUELTO)
- **Severidad:** CRITICA
- **Causa:** Endpoint `POST /rewards/{id}/redeem` sin try-catch. MySQL error propagaba query raw al cliente.
- **Fix:** `a4f5afc` — try-catch en RewardController. Throwable genera mensaje generico. report() loguea.

---

## 2026-04-10 — Responsive Audit (5 bugs)

### BUG-045: ModuleMenu/HappeningNow/HomeHero tamanos fijos (RESUELTO)
- **Severidad:** BAJA
- **Causa:** Heights y fontSizes hardcoded no se adaptan.
- **Fix:** `5cb8c4e` — proporcionales al screenWidth.

### BUG-044: Leads header 3 botones overflow (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** 3 botones en 1 fila no caben en 360dp.
- **Fix:** `5cb8c4e` — 2 filas.

### BUG-043: Login sin ScrollView (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Formulario se cortaba en pantallas pequenas.
- **Fix:** `5cb8c4e` — ScrollView agregado.

### BUG-042: 12 pantallas SafeArea inconsistente (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** pt-14/pt-6 hardcoded en vez de insets.
- **Fix:** `5cb8c4e` — useSafeAreaInsets().top + 16 en todas.

### BUG-041: Skeletons overflow en pantallas 360dp (x3) (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Width fijo en vez de flex:1.
- **Fix:** `5cb8c4e` — flex:1 en skeletons.

---

## 2026-04-09e — Profile + Branding (3 bugs)

### BUG-040: Flecha en Mi Agenda (es tab, no stack) (RESUELTO)
- **Severidad:** BAJA
- **Causa:** Mi Agenda tenia header de stack con flecha.
- **Fix:** Quitar flecha cuando favoritesOnly=true.

### BUG-039: SVG avatar no renderizaba en social (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** react-native Image no soporta SVG.
- **Fix:** Cambiar a expo-image.

### BUG-038: Beam avatar fallback no funcionaba (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Falta de fallback en Photo component.
- **Fix:** avatar_url accessor con beam fallback en todos los endpoints.

---

## 2026-04-09b — Sponsors (3 bugs)

### BUG-037: Pixel fraccionario en grid 4col (RESUELTO)
- **Severidad:** BAJA
- **Causa:** Calculo sin Math.floor().
- **Fix:** Math.floor() en dimensiones.

### BUG-036: Loop infinito con sponsors = [] (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Comparacion de referencia [] crea nuevo objeto cada render.
- **Fix:** Constante EMPTY_SPONSORS.

### BUG-035: Trivia nunca se mostraba (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** visitStand() no se ejecutaba automaticamente al montar.
- **Fix:** Ejecutar implicitamente en mount.

---

## 2026-04-09 — Social Unificado (7 bugs)

### BUG-034: ~20 bugs menores social (gesture, layout, optimistic updates) (RESUELTO)
- **Severidad:** MEDIA/BAJA
- **Fix:** Sesion dedicada, 30 bugs resueltos en total.

### BUG-033: PhotoViewer initialIndex out of bounds (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** initialIndex > array length.
- **Fix:** Validar indice.

### BUG-032: Upload foto network error con archivos grandes (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** upload_max_filesize 1M en PHP.
- **Fix:** Quality 0.5 + upload_max_filesize 2M.

### BUG-031: MediaTypeOptions deprecated en expo-image-picker (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** API cambio en nueva version.
- **Fix:** Usar ['images'] array directamente.

### BUG-030: Comentarios no scrolleaban en BottomSheet (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** GestureDetector + FlatList conflicto.
- **Fix:** Gesture solo en handle, nestedScrollEnabled=true.

### BUG-029: Stale closures en timer de stories (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Closure captura valores iniciales del state.
- **Fix:** useRef para referencia mutable.

### BUG-028: GestureDetector sin GestureHandlerRootView (x2) (RESUELTO)
- **Severidad:** ALTA (crash)
- **Causa:** GestureDetector requiere GestureHandlerRootView como ancestor.
- **Fix:** Agregar wrapping en layout.

---

## 2026-04-08 — UI Masiva (Agenda, Speakers, Streaming) (9 bugs)

### BUG-027: debounceTimer con useState (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** useState se reinicializa cada render.
- **Fix:** useRef.

### BUG-026: Highlight re-trigger al favoritar (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** State no diferenciaba user tap vs programmatic.
- **Fix:** didHighlight ref.

### BUG-025: BreathingCarousel spring brusco (RESUELTO)
- **Severidad:** BAJA
- **Causa:** Spring behavior en Reanimated.
- **Fix:** withTiming suave + Easing.out(cubic).

### BUG-024: Hero titulo cortado (RESUELTO)
- **Severidad:** BAJA
- **Causa:** numberOfLines 2 + lineHeight incorrecto.
- **Fix:** numberOfLines 3, lineHeight 46.

### BUG-023: Emoji SVG crash en Filament (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Filament no soporta emojis en icon fields.
- **Fix:** Heroicons.

### BUG-022: LayoutAnimation no funciona con FlashList (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** FlashList virtualiza items.
- **Fix:** Key remount para animaciones (DaySlide).

### BUG-021: Animated.View absolute dentro de Pressable rompe Android (RESUELTO)
- **Severidad:** ALTA
- **Causa:** Android stacking context issue con overflow:hidden.
- **Fix:** Remover overlay, animar solo contenedor.

### BUG-020: interpolate template literal crash en worklet (RESUELTO)
- **Severidad:** ALTA (crash)
- **Causa:** Template literals no soportados en Reanimated worklets.
- **Fix:** Remover template literal.

### BUG-019: Flash blanco en Android al cambiar pantalla (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Activity background default blanco.
- **Fix:** backgroundColor #0e0e0e en app.json + ScreenWrapper.

---

## 2026-04-07 — Home + API (3 bugs)

### BUG-018: favoritedBy field name incorrecto (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** API retorna favoritedBy, no favorites.
- **Fix:** Corregir en controller y tipos TS.

### BUG-017: Pressable function style no aplica backgroundColor en Android (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Limitacion de React Native en Android.
- **Fix:** Separar View (bg) + Pressable (gesture).

### BUG-016: start_datetime/end_datetime API mismatch (RESUELTO)
- **Severidad:** ALTA
- **Causa:** Codigo esperaba start_datetime, API retorna start/end.
- **Fix:** Cambiar toda la app a start/end.

---

## 2026-04-07 — Security Audit (SEC-1/2/3)

### BUG-005 a BUG-015: 11 vulnerabilidades de seguridad (RESUELTO)
- **Severidad:** CRITICA
- Socket room auth, HTMLPurifier 8 modelos, token expiration, security headers, CORS, HTTPS, account lockout, Redis rate limiting, FormRequests, .env.production, security:check
- **Fix:** 42 tests, 11 fixes. Documentado en docs/FASE-SEGURIDAD.md.

---

## 2026-04-07 — Photobooth/Social/Gamification (2 bugs)

### BUG-004: Error de limite fotos silenciado (RESUELTO)
- **Severidad:** MEDIA
- **Causa:** Mensaje del backend se perdia en error handling.
- **Fix:** Mostrar mensaje real.

### BUG-003: useSessionRating no persistia en Expo Go (RESUELTO)
- **Severidad:** ALTA
- **Causa:** MMKV no sincroniza entre pantallas en Expo Go.
- **Fix:** Reescribir con react-query.

---

## 2026-04-06 — Agenda/Favorites (2 bugs)

### BUG-002: Filament SelectFilter dot notation crash (RESUELTO)
- **Severidad:** ALTA
- **Causa:** Filament no soporta relaciones anidadas en SelectFilter.
- **Fix:** Query manual.

### BUG-001: Favoritos Mi Agenda no sincronizaban (RESUELTO)
- **Severidad:** ALTA
- **Causa:** onMutate del toggleFavorite fallaba sin cache previa de mi-agenda.
- **Fix:** extractFavorites() — mi-agenda siempre derivada de agenda.

---

## Resumen acumulado

| Severidad | Count | Resueltos | Pendientes |
|-----------|-------|-----------|------------|
| CRITICA | 28 | 28 | 0 |
| ALTA | 72 | 72 | 0 |
| MEDIA | 93 | 91 | 2 (BUG-111, BUG-127) |
| BAJA | 17 | 17 | 0 |
| **Total** | **210+** | **208+** | **2** |

> Nota: BUG-005 a BUG-015 cuentan como 11 bugs individuales en una sola entrada.

Bugs pendientes: BUG-127 (metricas MC persist), BUG-111 (PollSlides accent parcial).

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
9. **Carbon mutation** — `addMinutes()`/`addHours()` MUTAN el objeto. SIEMPRE usar `->copy()->addMinutes()` cuando se necesita el valor original intacto
10. **Race conditions** — SIEMPRE usar atomic WHERE o lockForUpdate() para cambios de estado concurrentes
11. **Bulk operations** — NUNCA foreach con query individual para 1000+ registros. Jobs async con bulk insert/update
