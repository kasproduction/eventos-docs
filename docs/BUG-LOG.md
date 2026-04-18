# Bug Log — EventOS

> Registro completo de bugs encontrados y corregidos. Ordenado por fecha, mas reciente primero.
> Severidades: CRITICA (seguridad/crash/data) | ALTA (feature roto) | MEDIA (visual/UX) | BAJA (cosmetic/warning)

---

## 2026-04-18 — Sesion Mission Control QA Live

### BUG-105: role 'admin' no reconocido en requireModerator Q&A (RESUELTO)
- **Severidad:** ALTA — admin no podia moderar preguntas Q&A
- **Causa:** `requireModerator` en QuestionController solo aceptaba `organizer` y `moderator`, no `admin`
- **Fix:** Agregar `admin` al array de roles permitidos
- **Archivo:** `QuestionController.php:194`, `RatingController.php:117`
- **Commit:** c18fcc5

### BUG-106: HMAC route usaba whereHas('roles') inexistente (RESUELTO)
- **Severidad:** ALTA — ruta /monitor/{id} siempre daba 500 "No hay administrador"
- **Causa:** La query usaba `whereHas('roles', ...)` pero Attendee no tiene relacion `roles`, usa campo `role` directo
- **Fix:** Cambiar a `whereIn('role', ['admin', 'organizer', 'moderator'])`
- **Archivo:** `routes/web.php` ruta /monitor
- **Commit:** c18fcc5

### BUG-107: conflicto interactive_mode vs chat_enabled/qna_enabled (RESUELTO)
- **Severidad:** CRITICA — dos sistemas paralelos controlaban lo mismo, app no cambiaba panel
- **Causa:** Mission Control usaba campos boolean (chat_enabled, qna_enabled, polls_enabled) mientras Filament usaba interactive_mode. Ambos emitian eventos socket diferentes. App confundida.
- **Fix:** Eliminar booleans del config. Monitor usa interactive_mode (mismo que Filament). Observer emite session:mode_changed automaticamente.
- **Archivo:** SessionConfigController.php, session-stream/[id].tsx, useSessionMode.ts
- **Commit:** 97d968e

### BUG-108: CSRF 419 en PATCH desde Mission Control monitor (RESUELTO)
- **Severidad:** ALTA — no se podia guardar cambios desde el monitor
- **Causa:** HTML servido por ruta web Laravel seteaba cookies de sesion. fetch enviaba cookies (same-origin). Laravel statefulApi aplicaba CSRF verification al detectar cookie.
- **Fix:** `credentials: 'omit'` en fetch + `validateCsrfTokens(except: ['api/*'])`
- **Archivo:** mission-control/app.js, bootstrap/app.php
- **Commit:** 87427a6

### BUG-109: panel switching no funcionaba en monitor HTML (RESUELTO)
- **Severidad:** ALTA — click en tabs Q&A/Polls/Custom no cambiaba el panel visible
- **Causa:** CSS clase `.panel` era demasiado generica (conflicto con panel-header del sidebar). Luego display:none/flex peleaba con clase .visible.
- **Fix:** Usar IDs directos con style.display + setTimeout para clase .active
- **Archivo:** chat-monitor.html (luego mission-control/app.js)
- **Commit:** varios

### BUG-110: XSS en onclick inline del chat monitor (RESUELTO)
- **Severidad:** CRITICA — inyeccion de codigo via mensajes de chat con comillas
- **Causa:** `escAttr()` con comillas simples en onclick inline no era seguro. HTML entities se decodifican dentro de atributos.
- **Fix:** Event delegation con data attributes + msgDataMap (no mas onclick inline)
- **Archivo:** chat-monitor.html
- **Commit:** 4e8caaf

### BUG-111: abort(422) retornaba HTML en API JSON (RESUELTO)
- **Severidad:** MEDIA — clientes recibían HTML en vez de JSON al enviar URL no permitida
- **Causa:** `abort(422, 'mensaje')` genera respuesta HTML por defecto en Laravel
- **Fix:** Usar `ValidationException::withMessages()` que retorna JSON
- **Archivo:** SessionConfigController.php
- **Commit:** 4e8caaf

### BUG-112: emitConfigUpdate usaba secret incorrecto (RESUELTO)
- **Severidad:** ALTA — socket server rechazaba config updates del backend (401)
- **Causa:** Controller usaba `config('services.socket.secret')` que era vacio. El correcto es `config('services.socket.internal_secret')`
- **Fix:** Cambiar a `internal_secret`
- **Archivo:** SessionConfigController.php
- **Commit:** bfeccf1

### BUG-113: poll sync — PollSlides mantenia state del poll anterior (RESUELTO)
- **Severidad:** ALTA — al lanzar nueva encuesta, app mostraba "Gracias por responder" del poll cerrado
- **Causa:** React reutilizaba el componente PollSlides sin reiniciar `useState(currentIdx)`. El currentIdx quedaba en totalQ del poll anterior.
- **Fix:** Agregar `key={activePoll.id}` en PollPanel para forzar remount
- **Archivo:** PollPanel.tsx, useChat.ts
- **Commit:** ca590f9

### BUG-114: '' literal not terminated — script tag roto por HTML en string JS (RESUELTO)
- **Severidad:** CRITICA — monitor no cargaba, todo el JS fallaba
- **Causa:** HTML embebido en strings JS (srcdoc con `</style></head>`) rompia el parser HTML del browser
- **Fix:** Separar en archivos: mission-control/index.html + styles.css + app.js
- **Archivo:** public/mission-control/*
- **Commit:** 201aeb1

### BUG-115: cookies same-origin causaban 403 en fetch (RESUELTO)
- **Severidad:** CRITICA — PATCH/GET desde monitor daba 403 siempre en browser
- **Causa:** Browser envia cookies automaticamente en same-origin fetch (default). Laravel statefulApi priorizaba cookie sesion web (usuario anonimo) sobre Bearer token.
- **Fix:** `credentials: 'omit'` explicito en fetch — Bearer token only
- **Archivo:** mission-control/app.js
- **Commit:** 87427a6

### BUG-116: PollSlides colores hardcoded no coinciden con tema (PENDIENTE PARCIAL)
- **Severidad:** MEDIA — encuesta se ve con accent rojo, fondo blanco inconsistente
- **Causa:** PollSlides usaba rgba dark hardcoded. Migrado a useTheme() pero el accent del evento estaba en #ff0000.
- **Fix parcial:** Migrar a useTheme(). Accent cambiado a #1A1A1A. Falta pulido visual.
- **Archivo:** PollSlides.tsx, PollPanel.tsx, QnAPanel.tsx

### BUG-117: reload en Expo manda al onboarding (PENDIENTE)
- **Severidad:** MEDIA — por investigar si es solo hot reload dev o afecta produccion
- **Causa:** Desconocida. Posible que el token/session se pierda en reload.

### BUG-118: push de ban llega aunque usuario no inicio sesion (PENDIENTE)
- **Severidad:** MEDIA — usuario en onboarding recibe notificacion de ban
- **Causa:** Desconocida. Push se envia antes de limpiar token? O llega delayed?

---

## 2026-04-16 — Sesion Light Mode + Migracion Tokens

### BUG-104: Networking sendRequest crashea "Cannot read property map of undefined" (RESUELTO)
- **Severidad:** ALTA — feature completamente roto, no se podia enviar solicitud de contacto
- **Causa:** `useSendContactRequest` onMutate optimistic update asumia estructura plana (`old.data.map()`) pero `useDirectory` usa `useInfiniteQuery` con estructura `{ pages: [{ data: [...] }] }`. Al acceder `old.data` en infinite query, era `undefined` → `.map()` crasheaba
- **Origen:** Bug pre-existente desde el revert del swipe networking (2026-04-15). Al implementar infinite query en directorio, el onMutate no se actualizo
- **Fix:** Cambiar `old.data.map(...)` por `old.pages.map(page => ({ ...page, data: page.data.map(...) }))`
- **Archivo:** `hooks/useNetworking.ts` linea 73-84
- **Commit:** 6124f0f

---

## 2026-04-14 — Sesion Onboarding Replay + Session Detail + Bancolombia

### BUG-079: API onboarding crashea con array_flip en preset cities (RESUELTO)
- **Severidad:** CRITICA — API devuelve 500, onboarding no carga
- **Causa:** `array_flip()` en `resolveStepsConfigUrls()` falla con preset `cities` que es array anidado (country→array de ciudades)
- **Fix:** Skip campos con `depends_on` en la resolucion + verificar `is_string` antes de `array_flip`
- **Archivo:** `OnboardingController.php:170`

### BUG-080: Login no pasa event_slug al backend (RESUELTO)
- **Severidad:** ALTA — eventId queda null, home carga vacia
- **Causa:** `authApi.login()` no incluia `event_slug`, backend adivinaba el evento por `is_active` fallback
- **Fix:** Agregar `event_slug: DEFAULT_EVENT_SLUG` al payload de login en AuthStep
- **Archivo:** `AuthStep.tsx:213`

### BUG-081: index.tsx fetch sin timeout (RESUELTO)
- **Severidad:** ALTA — pantalla negra indefinida si backend no responde
- **Causa:** `fetch('/auth/me')` sin AbortController, emulador con red lenta cuelga forever
- **Fix:** Agregar AbortController con timeout 6s + clearTimeout en finally
- **Archivo:** `app/index.tsx:34`

### BUG-082: index.tsx registrationApprovedAt null sin fallback (RESUELTO)
- **Severidad:** ALTA — redirige a pending-approval incorrectamente
- **Causa:** Si `/auth/me` retorna attendee null, `registrationApprovedAt` queda null y redirige a pending-approval
- **Fix:** Fallback a `user?.registrationApprovedAt` del cache cuando attendee es null
- **Archivo:** `app/index.tsx:80`

### BUG-083: Fetch cities en cada keystroke de FormStep (RESUELTO)
- **Severidad:** MEDIA — llamadas API innecesarias, rendimiento
- **Causa:** useEffect para cities dependia de `[values]` completo, se ejecutaba en cada cambio de cualquier campo
- **Fix:** Unificar effects, solo ejecutar cuando parent value realmente cambia via prevParentValues ref
- **Archivo:** `FormStep.tsx`

### BUG-084: Skip button cuenta campos ocultos (depends_on) (RESUELTO)
- **Severidad:** MEDIA — UX confusa, boton skip desaparece incorrectamente
- **Causa:** `config.fields.some(f => f.required)` incluia campos hidden por depends_on
- **Fix:** Usar `visibleFields` en vez de `config.fields`
- **Archivo:** `FormStep.tsx`

### BUG-085: Onboarding replay — campos vacios, confetti, puntos dobles (RESUELTO)
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

### BUG-086: Onboarding custom fields (country/city) no se guardan (RESUELTO)
- **Severidad:** MEDIA — datos de ubicacion se perdian
- **Causa:** FormStep enviaba string keys ("country") a `PUT /me/registration-fields`, pero backend esperaba numeric field IDs
- **Fix:** Nueva columna `onboarding_data` JSON en attendees + endpoints `GET/PUT /me/onboarding-data`
- **Archivos:** Migration, `Attendee.php`, `ProfileController.php`, `api.php`, `FormStep.tsx`

### BUG-099: activate endpoint 500 con token ya usado (RESUELTO)
- **Severidad:** MEDIA — excepcion 500 en vez de error limpio 422
- **Causa:** `firstOrFail()` lanza ModelNotFoundException cuando token ya consumido
- **Fix:** Usar `first()` + ValidationException con mensaje claro
- **Archivo:** `AuthService.php:activateAccount()`

### BUG-098: invitation_token expuesto sin verificacion (RESUELTO)
- **Severidad:** MEDIA — token entregado a cualquiera que conozca el email
- **Causa:** check-email devolvia token directo sin verificar identidad
- **Fix:** Si evento tiene `activation_verify_field`, check-email no devuelve token. Nuevo endpoint `POST /auth/verify-identity` valida dato personal antes de entregar token.
- **Archivos:** `AuthController.php`, `AuthStep.tsx`, `authApi.ts`, `RegistrationSettingsResource.php`

### BUG-096: Access code no sanitiza espacios al pegar (RESUELTO)
- **Severidad:** MEDIA — usuario pega codigo con espacio, falla validacion
- **Causa:** Ni el backend ni la app eliminaban whitespace del codigo pegado
- **Fix:** Backend: `preg_replace('/\s+/', '', $value)` + merge limpio. App: `.replace(/\s/g, '')` antes de enviar.
- **Archivos:** `RegisterRequest.php`, `AuthStep.tsx`

### BUG-097: Mensajes API en ingles (RESUELTO)
- **Severidad:** BAJA — "Profile updated" y "Photo removed" aparecian en ingles
- **Fix:** Traducir a "Perfil actualizado." y "Foto eliminada."
- **Archivo:** `ProfileController.php`

### BUG-095: AccessCode race condition en registerUse (RESUELTO)
- **Severidad:** MEDIA — dos registros simultaneos podian exceder max_uses
- **Causa:** `increment('uses_count')` no verificaba max_uses atomicamente
- **Fix:** WHERE atomico con `uses_count < max_uses` en el UPDATE, retorna bool
- **Archivo:** `AccessCode.php:registerUse()`

### BUG-090: PhotoStep replay — foto aparece sin animacion (RESUELTO)
- **Severidad:** BAJA — cosmetic, foto aparece de golpe sin fade-in
- **Causa:** `photoOpacity` se inicializaba a 1 si photoUri existia, pero en replay se seteaba despues del init
- **Fix:** Inicializar opacity a 0, animar a 1 cuando se pre-carga la foto en replay
- **Archivo:** `PhotoStep.tsx`

### BUG-091: InterestsStep replay — IDs huerfanos no validados (RESUELTO)
- **Severidad:** MEDIA — IDs de opciones eliminadas quedaban seleccionados invisiblemente
- **Causa:** `selected_ids` del API se cargaban sin verificar que existieran en opciones actuales
- **Fix:** Filtrar selected_ids contra IDs validos de options. Esperar a que options carguen antes de fetch.
- **Archivo:** `InterestsStep.tsx`

### BUG-092: FormStep pre-fill falla silenciosamente (RESUELTO)
- **Severidad:** BAJA — usuario no sabe por que campos estan vacios en replay
- **Causa:** `.catch(() => {})` sin feedback al usuario
- **Fix:** Toast de error "No se pudieron cargar tus datos previos"
- **Archivo:** `FormStep.tsx`

### BUG-093: Session Detail favorite sin error handling (RESUELTO)
- **Severidad:** BAJA — toggle falla sin informar al usuario
- **Causa:** `toggleFavorite.mutate()` sin onError callback
- **Fix:** Agregar `onError` con toast de error
- **Archivo:** `session/[id].tsx`

### BUG-094: invitation_token reutilizable tras intercepcion (RESUELTO)
- **Severidad:** MEDIA — token interceptado podia usarse despues
- **Causa:** check-email devolvia el mismo token siempre, tokens viejos seguian siendo validos
- **Fix:** Regenerar token en cada check-email request. Token anterior se invalida automaticamente.
- **Archivo:** `AuthController.php`

### BUG-088: dynamicOptions stale al cambiar pais en FormStep (RESUELTO)
- **Severidad:** MEDIA — ciudades del pais anterior persisten si API falla
- **Causa:** Al cambiar parent field, se reseteaba el valor del child pero no se limpiaban las opciones dinamicas en `dynamicOptions` state
- **Fix:** Clear `dynamicOptions[field.key]` junto con el reset del child value
- **Archivo:** `FormStep.tsx`

### BUG-089: registrationApprovedAt fallback sobreescribe dato fresco (RESUELTO)
- **Severidad:** ALTA — usuario con aprobacion revocada podia entrar con cache viejo
- **Causa:** `att?.registration_approved_at ?? user?.registrationApprovedAt` — si servidor devuelve null (pendiente), `??` salta al cache que podia tener 'auto'
- **Fix:** Priorizar dato del attendee fresco; fallback al cache solo cuando no hay attendee
- **Archivo:** `app/index.tsx`

### BUG-087: isReplay/postActivation se pierden en re-renders (RESUELTO)
- **Severidad:** ALTA — replay no funciona (confetti, campos vacios)
- **Causa:** Flags leidos con `getCached()` en cuerpo del componente y borrados inmediatamente. En re-renders, valor era false.
- **Fix:** Usar `useState(() => ...)` con lazy initializer para persistir valor inicial
- **Archivo:** `OnboardingContext.tsx`

---

## 2026-04-12 — Sesion Moderacion + Auth + Error Handling

### BUG-063: Token registro 30d hardcoded (RESUELTO)
- **Severidad:** MEDIA (seguridad)
- **Causa:** `AuthService.php` register y activate usaban `addDays(30)` hardcoded.
- **Fix:** Reemplazado por `config('sanctum.expiration')` en ambos metodos.

### BUG-064: Ban no se valida server-side (RESUELTO)
- **Severidad:** ALTA (seguridad)
- **Causa:** No habia middleware que chequeara ban en cada API call.
- **Fix:** Middleware `CheckBan` aplicado a todas las rutas autenticadas (excepto auth/me, auth/logout).

### BUG-065: Toast vacio en pending-approval
- **Severidad:** MEDIA (UX)
- **Causa:** `toast.show('texto', 'variant')` — firma incorrecta, espera objeto `{ message, variant }`.
- **Fix:** Corregido en pending-approval.tsx y activate-account.tsx.

### BUG-066: Login no verifica approval antes de mostrar QR
- **Severidad:** ALTA (flujo roto)
- **Causa:** AuthStep login iba directo a `goTo('done')` sin verificar `registrationApprovedAt`. Usuario no aprobado veia QR.
- **Fix:** Agregado check `registrationApprovedAt === null` en login flow + index.tsx.

### BUG-067: registrationApprovedAt null bloquea usuarios sin approval
- **Severidad:** CRITICA (flujo roto)
- **Causa:** `AttendeeResource` devuelve `registration_approved_at: null` cuando el evento NO requiere approval. App interpreta null como "no aprobado" y manda a pending-approval.
- **Fix:** Backend devuelve `'auto'` cuando `registration_requires_approval = false`.

### BUG-068: Onboarding fetch sin timeout
- **Severidad:** ALTA (UX)
- **Causa:** `onboardingApi.get()` usaba `fetch()` sin AbortController. Si Laravel esta caido, pantalla negra 30-120s.
- **Fix:** AbortController con timeout 6s + ConnectionError screen con boton reintentar.

### BUG-069: onboarding_seen no se limpia al logout
- **Severidad:** CRITICA (multi-usuario)
- **Causa:** `clearAuth()` no borraba el flag `onboarding_seen`. Segundo usuario en mismo dispositivo saltaba welcome.
- **Fix:** `clearAuth()` ahora limpia `onboarding_seen` y `post_activation_onboarding`.

### BUG-070: Attendee null causa loop pending-approval
- **Severidad:** ALTA (edge case)
- **Causa:** Si backend devuelve `attendee: null`, `registrationApprovedAt` queda `null` y usuario queda atrapado.
- **Fix:** Si no hay attendee, `registrationApprovedAt = 'no_attendee'` (no bloquea).

### BUG-071: Activate-account sin token deja pantalla sin salida
- **Severidad:** MEDIA (edge case)
- **Causa:** Deep link sin parametro `token` mostraba pantalla con campos pero sin poder enviar.
- **Fix:** Redirige a `/onboarding` inmediatamente si no hay token.

### BUG-072: Post-activation onboarding detecta token viejo
- **Severidad:** ALTA (flujo roto)
- **Causa:** OnboardingContext usaba `hasToken` para detectar post-activacion. Cualquier token viejo activaba el salto a photo.
- **Fix:** Flag especifico `post_activation_onboarding` que solo activate-account setea, se consume una vez.

### BUG-073: ConnectionError boton se estira verticalmente
- **Severidad:** MEDIA (visual)
- **Causa:** Componente ConnectionError no tenia layout split (content center + boton bottom).
- **Fix:** Reestructurado identico a banned.tsx: content centrado + boton full-width abajo con SafeArea.

### BUG-074: "No tienes cuenta? Registrate" visible sin registro habilitado
- **Severidad:** MEDIA (UX)
- **Causa:** AuthStep mostraba link toggle login/register sin verificar `show_register_button`.
- **Fix:** Condicional `(isRegister || showRegisterOption)` oculta el link si registro desactivado.

### BUG-075: Titulo welcome "Bienvenido a" no editable
- **Severidad:** MEDIA (config)
- **Causa:** `title_prefix` hardcoded en WelcomeStep. No habia campo en Filament.
- **Fix:** Campo `title_prefix` en Filament + lectura desde config con fallback.

### BUG-076: Textos de botones welcome no editables
- **Severidad:** MEDIA (config)
- **Causa:** "Crear cuenta" y "Ya tengo cuenta" hardcoded en WelcomeStep.
- **Fix:** Campos `register_button_text` y `login_button_text` en Filament.

### BUG-077: Titulo/subtitulo visible cuando hero image cubre todo
- **Severidad:** MEDIA (UX/diseño)
- **Causa:** No habia forma de ocultar textos cuando el key visual hero ya comunicaba todo.
- **Fix:** Toggle `show_text` en Filament. Si false, solo muestra botones.

### BUG-078: Imagenes Filament no se muestran en app
- **Severidad:** ALTA (feature roto)
- **Causa:** Filament guarda path relativo (`onboarding/archivo.png`), app necesita URL completa. Ademas hostname `eventos-backend.test` no resuelve desde dispositivo.
- **Fix:** `resolveStepsConfigUrls()` en API convierte a URL con `asset()`. App aplica `fixStorageUrl()` para reemplazar hostname por IP.
- **Nota:** En produccion con Cloudflare R2, las URLs seran absolutas y este fix no sera necesario. Documentar en DISPONIBILIDAD-HA.md al migrar a R2.

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

**Resultado:** 39/39 escenarios cubiertos. 9 bugs encontrados y corregidos (BUG-065 a BUG-073). 3 code smells documentados.

---

## 2026-04-11 — Sesion Onboarding Steps + Auth + Seguridad

### BUG-058: Password input autoCapitalize mayuscula en Android
- **Severidad:** MEDIA
- **Causa:** Campos password sin `autoCapitalize="none"` ni `autoCorrect={false}`. Android pone primera letra mayuscula.
- **Fix:** `585c8c4` — agregado en 7 inputs (AuthStep, login, register, activate-account).

### BUG-059: Foto upload no actualizaba authStore
- **Severidad:** ALTA (feature roto)
- **Causa:** `api.upload('/me/photo')` en PhotoStep no capturaba la respuesta. El store quedaba con `photoUrl: null`. Mi QR y Perfil mostraban beam avatar.
- **Fix:** `9e47000` — captura `photo_url` de respuesta y actualiza authStore.

### BUG-060: Cambiar foto perfil no actualizaba MiQR
- **Severidad:** MEDIA
- **Causa:** `useQrToken` tiene `staleTime: Infinity`. Al cambiar foto en perfil, MiQR seguia con la vieja.
- **Fix:** `9965312` — `invalidateQueries(['qr-token'])` al subir/eliminar foto.

### BUG-061: Logout redirige a login viejo NativeWind
- **Severidad:** MEDIA
- **Causa:** `index.tsx` y `_layout.tsx` redirigian a `/(auth)/login` que fue eliminado.
- **Fix:** `9e47000` + `1bfc6c6` — redirige a `/onboarding`. Si `onboarding_seen=true`, empieza en auth step directo.

### BUG-062: Back arrow inconsistente (chevron-left vs arrow-left)
- **Severidad:** BAJA
- **Causa:** Onboarding usaba `chevron-left`, resto del app usa `arrow-left`.
- **Fix:** `77a85a4` — unificado a `arrow-left`.

### BUG-063: Token registro 30d hardcoded (inconsistencia con login 7d)
- **Severidad:** MEDIA (seguridad)
- **Causa:** `AuthService.php:113` usa `addDays(30)` hardcoded. Login usa `config('sanctum.expiration')` = 7d.
- **Fix:** Pendiente (SEC-3b.1).

### BUG-064: Ban no se valida server-side por request
- **Severidad:** ALTA (seguridad)
- **Causa:** No hay middleware que chequee ban en cada API call. Usuario baneado sigue usando la app hasta 7 dias.
- **Fix:** Pendiente (SEC-3b.3).

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
