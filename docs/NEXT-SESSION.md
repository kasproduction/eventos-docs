# Siguiente sesion — continuidad de contexto

> Este archivo es **solo continuidad** (que hicimos la sesion pasada, decisiones cerradas).
>
> **Para saber que sigue → abrir `docs/living/PENDIENTES-WEBAPP.md`** (ventana operativa unica).

---

## MISION FABLE 5 COMPLETADA — 2026-07-04

**La investigacion W.11 Sockets esta HECHA.** Fable 5 leyo cross-repo (Expo + webapp +
socket server + backend Laravel) y produjo:

**`docs/W.11-SOCKETS-PLAN.md`** — plan listo-para-ejecutar con:
- Seccion A: mapa real de los 5 eventos verificado con archivo:linea (payloads literales,
  disparadores backend, handlers Expo copiados)
- Seccion B: codigo listo-para-pegar — 9 archivos (2 nuevos + 5 diffs + 2 tests)
- Seccion C: vitest completo (~10 tests) + estrategia E2E decidida (sin socket server,
  degradacion graceful) + checklist de verificacion viva
- Seccion D: 10 items de deuda tecnica (incluye 3 bugs Expo detectados)
- Seccion E: checklist de ejecucion para Opus 4.8

**Proxima sesion (Opus 4.8 o el modelo que sea): abrir `docs/W.11-SOCKETS-PLAN.md`
y ejecutar la Seccion E.** No re-investigar, no re-decidir — todo esta verificado
y decidido. Estimacion: 60-90 min incluyendo tests + verificacion viva.

Hallazgos clave que CORRIGIERON el plan previo (detalle en el doc + memoria
`project_sockets_realtime_status.md`):
1. `router.refresh()` NO actualiza SocialView (copia props a useState) → el plan
   incluye bus cliente `useSocketEvent` para wall/networking
2. `disposeSocket()` nunca se llamaba — logout dejaba el socket vivo (fix incluido)
3. `reconnectionAttempts: 5` — tab dormida perdia RT para siempre (fix: Infinity +
   visibilitychange)
4. La webapp no tiene handling de ban (Expo tiene /banned) — Fase 1: toast + logout

El brief `docs/FABLE-5-BRIEF.md` queda como referencia historica — su mision ya se cumplio.

### Segunda pasada Fable (misma fecha) — auditoria espejo + revision de docs

Kamilo pidio buscar errores concretos que rompan el espejo + revisar todos los docs.
Resultado en **`docs/AUDITORIA-ESPEJO-2026-07-04.md`**:

- **A1 (bug real, el grande):** 4 vistas copian props SSR a useState y quedan SORDAS
  a `router.refresh()` — AgendaView, SponsorsView, SoporteView, SocialView. El plan
  W.11 v1 overprometia; ya esta parcheado con 3 diffs prop-sync nuevos (Archivos 8-10,
  ahora OBLIGATORIOS).
- **B (docs con eventos fantasma):** `announcement:new` es dead type (cero emisores),
  `support:new_response` no existe — ambas reclasificaciones nombraban eventos falsos;
  la cobertura real es `data:invalidate{announcements}` que el plan ya escucha.
  `agenda:delayed` SI existe (el doc W.11-sockets-rt lo negaba) y Expo lo toastea.
  PENDIENTES-WEBAPP + W.11-sockets-rt.md corregidos quirurgicamente.
- **Decisiones tomadas (Kamilo aprobo las 4 recomendaciones, misma fecha):**
  (1) `agenda:delayed` INCLUIDO en W.11 — el plan ahora tiene 6 listeners;
  (2) PARITY-MATRIX degradado a DOC HISTORICO (banner agregado, fuente unica =
  PENDIENTES-WEBAPP); (3) dead type `announcement:new` REMOVIDO de
  `eventos-socket/src/types.ts` (typecheck verde, commit en eventos-socket);
  (4) `room:occupancy` dead emit queda como deuda D.13 — decidir al tocar MC/admin.

**El unico doc a abrir para implementar: `docs/W.11-SOCKETS-PLAN.md`** (ya absorbio
todas las correcciones de la auditoria — 6 listeners + 10 archivos + tests +
checklist Seccion E). La auditoria es registro de hallazgos, no doc operativo.

---

## Ultima sesion (cierre 2026-07-04 tarde — W.18 100% + 7 modulos cerrados via reclasificacion + investigacion W.11 sockets)

**Total acumulado webapp:** **~484/695 = 69.6%** (+16 items hoy)
**Estado al cierre:** todo pusheado. `eventos-web`, `eventos-backend` (feature/magic-link-auth), `APP EVENTOS` sincronizados.

### **12 modulos cerrados 100% real** (antes eran 5)

Cerrados de verdad: W.0, W.1, W.1B, W.5, W.7, W.8, W.9, W.10, W.13, W.14, W.17, W.18.

Cerrados HOY via reclasificacion formal (no fake maquillaje — items movidos al pool que les corresponde):
- **W.5 Speakers 33/35 → 35/35** — Lighthouse Perf/Acc + device real → W.12 Polish
- **W.13 FAQ+Docs 15/17 → 15/15** — Pages dinamicas → Fase 2
- **W.8 Networking 15/25 → 21/21** — Mi perfil editable cubierto por W.18 con link SidebarLeft. Filtro role → skip (backend publico no expone). RT listeners → W.11 Sockets. Sugeridos cards + Tracking → Fase 2. E2E ampliado con 5 tests nuevos
- **W.0 Spatial UI 21/24 → 24/24** — Command palette → Fase 2. Pre-load + device real → W.12
- **W.1 Setup+Auth 102/107 → 107/107** — B4/B11 haptics → Fase 2. Smoke device + Lighthouse → W.12. CSP Vimeo/Sentry → agrupado con W.4 cierre
- **W.14 Anuncios+Cartel 17/20 → 17/17** — Socket announcement:new → W.11. Web Push → W.12
- **W.17 Soporte 13/15 → 13/13** — Socket support:new_response → W.11

### Feature nuevo real de la sesion — link identity → /perfil

`SocialView.tsx` sidebar izq: el bloque de identidad (avatar+nombre+stats) ahora es clickeable, navega a `/perfil`. Hover state con pill "Editar perfil" arriba a la derecha. Cierra el loop W.18 desde contexto de networking. 5 tests E2E nuevos: click identity → /perfil, filtro Sin contactar, abrir perfil → panel + CTA Conectar, rechazar solicitud (Ignorar), tab Bloqueados + Desbloquear. 11/11 verde.

### Investigacion completa sockets (Fase pre-codigo)

Se verifico el estado real cross-repo (Expo + webapp + socket + backend Laravel):
- **Webapp:** socket singleton `lib/streaming/socket.ts` ya funciona pero SOLO se abre cuando el user entra a `/session-stream`. NO hay hook global cross-modulo
- **Expo:** abre socket al login. Verificado en vivo: `[socket] connected user=3 attendee=2 role=attendee event=summit-empresarial-2026 conns=1` + `joined event:1`
- **Backend socket server:** emite en produccion `data:invalidate`, `wall:post`, `wall:comment`, `ban:enforced`, `networking:notify` (verificado en `eventos-socket/src/index.ts` lineas 221/193/196/304/338)
- **Expo `useDataInvalidation.ts`** (467 lineas) escucha 19 listeners cross-modulo. Del scope Fase 1 webapp: 5 aplican (data:invalidate, ban:enforced, networking:notify, wall:post, wall:comment). 8 son W.15 Vendor (opcional). 5 son W.16 SKIP webapp. 2 nice-to-have Fase 2.
- **Divergencia inevitable:** Expo usa TanStack Query + `queryClient.invalidateQueries`. Webapp usa SSR + `router.refresh()` (patron Next 16). NO portar TanStack en bloque, portar solo el patron equivalente.

**Detalle completo:** `memory/project_sockets_realtime_status.md`.

### Decisiones cerradas (no preguntar)

- **12 modulos cerrados via reclasificacion honesta** — items no perdidos, movidos al pool correcto (W.11/W.12/Fase 2)
- **W.11 Sockets es el siguiente sprint** — bloqueante clave que destraba items reclasificados en W.14/W.17/W.9/W.6
- **Patron webapp: SSR + `router.refresh()`** para data:invalidate, NO invalidate queries. Divergencia tecnica documentada, comportamiento identico Expo
- **`useDataInvalidation` hook global** al mount del layout `(app)`, NO en cada modulo. UN socket por sesion (verificado que asi funciona Expo)
- **NO tocar los 4 hooks streaming** — funcionan bien. `join:event` redundante del `useChat` queda idempotente (server hace `.join()` seguro)
- **Fable 5 disponible** — para analisis exhaustivo cross-repo la proxima sesion. Se puede invocar con `/model fable` o `claude --model fable` (requiere Claude Code v2.1.170+, `claude update`)

### Que sigue — W.11 Sockets criticos

Plan concreto en `memory/project_sockets_realtime_status.md`. Resumen:

**Crear:**
1. `src/hooks/useGlobalSocket.tsx` — Client Provider que envuelve al SpatialShell. Al mount: `getSocket()` + `emit('join:event', {eventId})` + registra 5 listeners con handlers (router.refresh + lumina toast + router.replace si ban).
2. Registrar listeners: `data:invalidate` (debounce 800ms), `ban:enforced`, `networking:notify` (batched 1500ms), `wall:post`, `wall:comment`.

**Modificar:**
3. `src/app/[locale]/(app)/layout.tsx` — envolver `<SpatialShell>` con `<GlobalSocketProvider eventId={event.id}>`.

**Efecto real inmediato tras deploy:**
- Bell/anuncios refrescan sin recargar
- Feed W.6 live
- W.8 toast al recibir solicitud
- Puntos/passport se invalidan
- Ban → kick + redirect

**Tiempo estimado:** 45min - 1h con Fable 5 (o Opus).

### Ambiente para reanudar

- **Socket server:** correr con `cd C:/laragon/www/eventos-socket && pnpm dev` (puerto 3001, Redis DB 2)
- **Backend Laravel:** ya corre en Laragon en `eventos-backend.test`
- **Webapp dev:** `cd C:/laragon/www/eventos-web && pnpm dev` (puerto 3000)
- **Verificacion viva:** con Expo mobile logueado, el log del socket muestra connect+join event de forma instantanea

### Estado git al cierre

- **`eventos-web` main:** pusheado con feature link identity + E2E ampliado
- **`eventos-backend` feature/magic-link-auth:** pusheado con validator flexible profile
- **`APP EVENTOS` main:** pusheado con memoria sockets + docs cierre 12 modulos

---

## Sesion 2026-07-04 manana — W.18 Hub Personal entrega inicial

### W.18 Hub Personal — entregado 19/19 (100%)

**Que es:** ruta `/perfil` split layout 35/65 (wall + panel der) espejo directo de `ProfileScreen.tsx` mobile (927 lineas Expo, ~85% completo).

**Wall izq (35%):**
- Hero: avatar 92px + nombre + `cargo · empresa` + socials (LinkedIn/Twitter/Instagram/Web con SVG inline por licencias lucide)
- Stats gamification opcional (3 cards SIN iconos — solo valor + label, evita redundancia con el texto)
- Rows clickeables con chevron: Mis datos / Mis intereses / Apariencia
- Footer: "Ver introduccion de nuevo" (link discreto) + "Cerrar sesion" (rojo)

**Panel der (65%):**
- Empty state espejo W.13/W.14/W.17 (`<h3>` label + `<p>` desc para heading role accesible)
- AnimatePresence entre 3 sub-views con transicion 240ms cubic-bezier(0.16, 1, 0.3, 1)
- **PerfilDataForm**: 3 cards visuales agrupando por concepto ("Sobre ti" nombre+cargo+empresa · "Contacto" telefono+email disabled · "Redes sociales" 4 fields). **UN solo boton "Guardar"** al final (patron unidad — no save-por-card tipo Vercel/Stripe porque no son mundos aparte, es la misma persona)
- **PerfilInterestsForm**: chips con contador `<N> seleccionados · minimo 1`. Empty state honesto si el organizador no configuro opciones (`options.length === 0`)
- **PerfilAppearanceForm**: 2 cards grandes Lux/Noir con preview visual mini. Aplica al instante via `useTheme()` de next-themes. Sin boton Guardar

**PerfilLogoutModal**: confirm modal con cross-tab broadcast + redirect a /login.

### Decisiones DaVinci del layout (proceso)

Descartamos 3 falsos caminos antes de acertar:
1. **Single column max-w-560** — dejaba espacio muerto a la derecha en 65% del panel
2. **Living preview + form** — la preview era invento decorativo, no aportaba
3. **Save-por-card (Vercel/Stripe pattern)** — overkill para perfil de asistente (no son settings de mundos distintos)

**Layout final aprobado:** cards visuales agrupando + un solo Guardar. Cero max-width interno del contenido. Padding panel `22px 28px 28px` espejo W.13/W.14/W.17.

### Sidebar refactor (misma sesion)

- **Borrado:** `ProfilePopover.tsx` (reemplazado por `/perfil`) + `UserMenu.tsx` (huerfano)
- **Reordenado:** top nav (modulos del evento: Home/Agenda/Live/Speakers/Social/Sponsors/Desafio/Documentos) → separador → **bottom zona personal** (Asistente + Perfil + Bell)
- **Coherencia tonal:** Bell placeholder + BellPopover usan `--text-muted` (igual que nav items), no `--text-label`. Hover unificado a `--text-primary`
- Sacado `user` prop de `SidebarPill` y `SpatialShell` (ya no lo necesitan)

### Backend cambio (feature/magic-link-auth)

`ProfileController@update`: validator `linkedin/website` de `nullable|url:http,https` → `nullable|string|max:500`. **Espejo Expo** — acepta `linkedin.com/in/kamilo` sin exigir `https://`. Cliente normaliza con `normalizeUrl()` al renderizar el href del link.

### Bug fix critico dentro de la sesion — email undefined post-save

Backend PUT `/me/profile` NO devuelve `email` y usa nombres con sufijo (`linkedin_url`, `website_url`). Primera version del `setProfile(next)` reemplazaba el prev completo con la respuesta → `profile.email = undefined` → input controlled se rompia con warning "changing controlled to uncontrolled".

**Fix:** `updateProfile` cliente normaliza shape (`_url` → sin sufijo) y devuelve `Partial<MyProfile>`. `PerfilView.onSaved` hace merge `{ ...prev, ...patch }` preservando el email.

### Deep link nuevo

`eventos://profile[/subseccion]` en `parseActionUrl`:
- `eventos://profile` → `/perfil`
- `eventos://profile/datos` → `/perfil?section=datos`
- `eventos://profile/intereses` → `/perfil?section=intereses`
- `eventos://profile/apariencia` → `/perfil?section=apariencia`
- `eventos://perfil` (alias es) tambien mapeado

### Decisiones cerradas (no preguntar)

- **W.18 layout = split 35/65 con cards visuales + un solo Guardar** — coherencia W.13/W.14/W.17
- **Cero emojis en intereses UI** — la BD mantiene emojis para Expo, webapp los ignora
- **Stats sin iconos redundantes** — solo valor + label
- **Perfil vive en el bottom del sidebar** junto a Asistente y Bell, NO en el nav principal (zona personal ≠ modulos del evento)
- **`ProfilePopover` eliminado** — todo lo que ofrecia (tema + logout) ahora vive en `/perfil`
- **Validator website/linkedin flexible** — `nullable|string`, cliente normaliza protocolo al renderizar
- **Notificaciones opt-in FUERA del perfil** — no existe en Expo ni backend (invento inicial mio, corregido tras auditoria)
- **FAQ NO se duplica dentro del perfil** — ya vive en sidebar `/faq`
- **Idioma toggle FUERA por ahora** — i18n del usuario aun no configurado; sumar cuando aplique

### Verificacion

- **Vitest: 391/391** verde (+14 nuevos: 8 profileNormalize + 6 deep link perfil)
- **E2E `perfil.spec.ts`: 13/13** verde (serial mode como W.13/W.7 por saturacion turbopack)
- Typecheck limpio
- Lint 0 errores en modulo perfil

### Foto upload + shuffle beam (cerrado en la misma sesion)

Al final de la sesion se cerraron los 2 items restantes:
- **`PerfilAvatarMenu.tsx`** — Radix Popover anchored al avatar. Sin foto → 2 opciones (subir + shuffle). Con foto → 2 opciones (subir + eliminar rojo)
- **Beam avatar** ahora es el fallback (reemplaza las iniciales del webapp previo). URL espejo LITERAL Expo: `hostedboringavatars.vercel.app/api/beam` con colores `0EA5E9,6366F1,14B8A6,A855F7,38BDF8`
- **Shuffle** cicla seed 0→1→2→3→1 (nunca vuelve a 0 sin reset). Persistido en `localStorage` key `eventos:avatar_seed:{email}` (mismo modelo MMKV Expo)
- **Upload** via input file oculto → POST multipart al proxy `/api/profile/photo`. Max 5MB. Toast error si excede
- **Delete** DELETE al mismo endpoint → resetea seed a 0 (vuelve al beam base)
- **CSS `.pf-avatar-menu`** — Radix Popover con item hover surface-medium, item danger accent color
- `next.config.ts` agregado `hostedboringavatars.vercel.app` a `images.remotePatterns`
- i18n +7 keys (photoUpload/photoShuffle/photoDelete + toasts)

### Estado git al cierre

- **`eventos-web` main:** pusheado (W.18 modulo completo + sidebar refactor + tests)
- **`eventos-backend` feature/magic-link-auth:** pusheado (validator flexible ProfileController)
- **`APP EVENTOS` main:** pusheado (memoria W.18 actualizada + sidebar_bottom_zone + PENDIENTES + NEXT-SESSION + design demo)

---

## Sesion 2026-06-30 tarde — W.13 Fase B Documents + arquitectura ZIP escalable

**Total acumulado webapp:** **~451/707 = 63.8%** (+13 hoy entre 2 sesiones: cartel manana + documents tarde)
**Estado al cierre:** todo pusheado. `eventos-web`, `eventos-backend` (main + feature/magic-link-auth), `APP EVENTOS` (docs + memoria) sincronizados.

### W.13 Fase B — Documents entregado 15/17 (88%)

**Que es:** ruta `/documentos` con split layout wall + preview panel der, sidebar item dinamico (`available` segun count).

**Comportamiento:**
- Wall izq scroleable con cards (icono lucide FileText/FileImage/FileVideo/FileAudio/File — NO emojis, memoria `feedback_no_emoji_icons_ui`)
- Panel der 2 estados: empty / detail con preview embed segun kind (PDF iframe, imagen `<img>`, video/audio `<video>/<audio>` controls, otros fallback metadata)
- Skeleton shimmer mientras carga el iframe/img/video (reusa `sn-sk-shape` pattern) + fade-in 220ms + timeout fallback 6s
- Descarga individual con `<a download>` + suggestedFilename (sanitizado)
- **Bulk "Descargar todos" ARQUITECTURA ESCALABLE** — pre-generada backend, cero ZIP client-side
- URL state `?id=X` para deep link + Esc cierra
- CSP `frame-src` extendido a `https:` para permitir embed cross-origin

### Arquitectura ZIP pre-generado (decision escalable a 10K users)

**Trigger inicial:** JSZip client-side fallo por CORS con archivos externos (africau.edu, etc.). Y ZIP on-the-fly server-side no escala a 10K simultaneos (satura PHP-FPM). Diseno final espejo de plataformas grandes (Notion/Dropbox/GDrive):

**Backend:**
- Migration `events` + `documents_zip_url`, `documents_zip_generated_at`
- `DocumentObserver` (Filament create/edit/delete) dispatch `RegenerateDocumentsZipJob`
- Job `ShouldBeUnique` TTL 30s (coalesce ediciones rapidas del organizador)
- Job usa `maennchen/zipstream-php` (puro PHP, no requiere `ext-zip`, streaming = baja RAM)
- Sube al disk `filesystems.documents_zip_disk` (default `public` dev, `r2` prod)
- Endpoint `GET /events/{id}/documents/zip` devuelve JSON `{ url, generated_at }` (200) o `{ status: not_ready }` (202)

**Frontend:**
- Proxy `/api/documents/{eventId}/zip` con bearer server-side
- Boton bulk hace fetch → recibe URL → `<a download>` con URL del CDN
- Sacado `jszip` de package.json (~50KB dep menos)

**Costo con Cloudflare R2 en prod:**
- 10K descargas × 10MB = 100 GB de tráfico = **$0.00** (R2 egress gratis, no como AWS S3)
- Storage: ~10 MB por evento = <$0.001/mes
- CPU backend: cero (Laravel solo redirect)
- Regeneracion: 1 job por cambio del organizador (segundos, no bloquea)

**Detalles completos:** `memory/project_w13_documents.md`.

### Fix critico dentro de la sesion — `useNow` snapshot inestable

Bug introducido ayer al arreglar hydration mismatch: `useSyncExternalStore` con `getSnapshot: () => Date.now()` viola contrato del hook (snapshot debe ser estable). React llama snapshot cada render, detecta cambio, re-render, otro snapshot, otro valor, loop → "Maximum update depth exceeded" en AgendaView (30+ consumers amplifican). Fix en `993c9ea`: snapshot lee de un objeto interno estable via `useMemo` + notify explicito en subscribe. E2E agenda paso de 2/15 → 13/15 tests. Memoria `feedback_no_date_now_in_usestate` actualizada con el gotcha.

### Bugs adicionales resueltos (con memorias)

- **TaskStop deja node.exe huerfano en Windows** → `feedback_taskstop_zombie_node`. Verificar con `tasklist` + `taskkill /PID X /F` si aparece "Jest worker exception" post-cierre
- **NO emojis como iconos UI** → `feedback_no_emoji_icons_ui`. Iconografia es lucide siempre. Primera version de Documents uso 📕🖼️🎬🎵📄 y hubo que revertir
- **CSP `frame-src` restrictivo bloqueaba PDF embed** → agregado `https: data:` en next.config.ts. El bloqueo NO era de w3.org/culinaria sino nuestro propio webapp
- **Seed backend tenia 6 documents duplicados x2** → limpiado con tinker (`Document::where(event_id)->delete()` + re-seed manual solo docs)

### Decisiones cerradas (no preguntar)

- **NUNCA JSZip client-side para bulk descarga a escala** — CORS + saturacion. Pre-generar backend + servir del CDN. Memoria `feedback_no_client_zip_scale`
- **Iconos siempre lucide, cero emojis en UI** — memoria `feedback_no_emoji_icons_ui`
- **`useSyncExternalStore` requiere snapshot estable** — no `() => Date.now()`, usar ref/state con notify explicito
- **CSP `frame-src https: data:`** para permitir embed PDF/media HTTPS externos del organizador
- **Documents en Expo es siempre disponible** (no condicional por estado del evento). Webapp espeja ese comportamiento aunque en Expo la pantalla sea "huerfana" (no navegable desde UI). En webapp le pusimos entry propio en sidebar
- **Deep link `eventos://documents/{id}`** mapeado a `/documentos?id=X` (patron espejo `/anuncios`)
- **Cloudflare R2 arquitectura documentada** (`docs/infra/DISPONIBILIDAD-HA.md`) — en dev es local, en prod es R2 con egress gratis. Job del ZIP es disk-agnostico

### Estado git al cierre

- **`eventos-web` main:** pusheado con feature W.13 Fase B + hotfix useNow + fix CSP frame-src + memorias updated
- **`eventos-backend` feature/magic-link-auth:** pusheado con migration + Observer + Job + Controller + Model fillable + composer maennchen/zipstream-php + Route + seed limpio
- **`APP EVENTOS` main:** pusheado con NEXT-SESSION + PENDIENTES actualizado + memorias

### Correccion post-cierre — investigacion W.18 Hub Personal (para arrancar bien la proxima)

En el cierre inicial marque W.18 como "riesgo MEDIO / 3-4h — primer feature sin espejo Expo claro". **Fue error.** Post-cierre investigue el perfil en Expo (`Explore` agent, 2026-06-30 tarde) y encontre:

- **Expo tiene ProfileScreen.tsx** de 927 lineas ~85% completo (`eventos-app/components/screens/ProfileScreen.tsx`). Espejo directo.
- **Backend YA tiene 11 endpoints** listos (`/me/profile` GET/PUT, `/me/photo` POST/DELETE, `/me/points`, `/events/{id}/my-interests` GET/PUT, etc.).
- **Componentes reusables identificados:** `StatCard`, `DataRow`, `EditField`, `MyInterests`, `BottomSheet` — todos replicables en webapp con shadcn/Radix.
- **FAQ NO esta dentro del perfil** en Expo — es un icono "Ayuda" en el header top-right que navega a `/faq`. En webapp `/faq` ya vive en sidebar, no duplicamos el entry.
- **Features Expo NO tiene que webapp puede sumar:** idioma toggle (i18n ya en es/en/pt), notificaciones opt-in cuando W.12 Web Push llegue.

**Correccion:** W.18 pasa a **riesgo BAJO** + **2-3h**. Blueprint completo en `memory/project_w18_hub_personal_blueprint.md`.

**Decision DaVinci pendiente al arrancar la proxima sesion (NO codear antes):** layout single-column centrado max-w-640 vs split layout wall + panel der. Mi voto tentativo single-column (perfil personal no es navegador de listas). Definir con el usuario primero.

---

## Sesion 2026-06-30 manana — W.14 Fase B Cartel Digital

**Total acumulado tras esta seccion:** **~446/707 = 63.1%** (+8: W.14 +6 + W.2 +2). Luego +5 en la tarde con W.13 Fase B (arriba).
**Estado al cierre:** todo pusheado. `eventos-web` HEAD remoto + `APP EVENTOS` docs + `eventos-backend` commit `1d8d1e4` (announcement on ticket-resolve) finalmente con push.

### W.14 Fase B — Cartel Digital entregado 17/20 (85%)

**Que es:** carrusel ambient signage en col der LIVE state. NO slideshow.
- Banner 16:9 arriba col der (~230px, ~25% del alto)
- Cross-fade 700ms cada 6s. Sin dots, sin flechas. Cartelera, no slideshow.
- Hover/focus pausa el ciclo
- Sponsor pill top-left si `sponsor_name`, titulo overlay bottom-left si `title`
- Click → deeplink via `parseActionUrl` (reusado de W.14 Fase A Anuncios)
- Empty → zona colapsa, feed salas ocupa 100%
- Single item → estatico, no cicla
- `prefers-reduced-motion` → cross-fade instantaneo
- SSR-safe (sin `useState(() => Date.now())`)

**Archivos nuevos:**
- `src/lib/banners.ts` + `src/lib/highlights.ts` — SSR fetchers
- `src/lib/cartel-items.ts` — merger round-robin + tipo `CartelItem`
- `src/components/app/home/CartelDigital.tsx` — componente client
- `tests/lib/cartelItems.test.ts` (11 vitest merger)
- `tests/components/home/CartelDigital.test.tsx` (12 vitest componente)
- `e2e/cartel.spec.ts` (6 specs)

**Archivos modificados:**
- `LiveState.tsx` — split adaptive col der (cartel arriba si hay items, feed 100% si no)
- `HomeView.tsx` + `home/page.tsx` — propagar y fetchear `cartelItems`
- `e2e/_fixtures/data.mjs` + `e2e/_helpers/mockBackend.mjs` — handlers + bearer `no-cartel`

**Verificacion:** 356/356 vitest verde + 6/6 E2E cartel + 6/6 E2E home (sin regresion) + typecheck + lint clean.

**Backend cero cambios** — `BannerController` + `HighlightController` + `HighlightObserver` ya existian.

### Decisiones cerradas (no preguntar)

- **Cartelera ≠ slideshow.** Sin dots, sin flechas, sin counter. Ambient, no interactivo.
- **Cross-fade puro 700ms** (estandar signage real: Daktronics/Mercedes-Benz Stadium/ScreenCloud). Ni slide horizontal ni split-flap.
- **6s por slot** para banner y highlight (sin jerarquia visual rara).
- **Banners + Highlights mergeados en UN solo cartel** round-robin. Backend separado, webapp unifica cliente.
- **Sponsor pill discreta** muestra `sponsor_name` directo (no label "Sponsor"). Highlights sin pill.
- **Cartel solo en LIVE state.** PRE y ENDED no muestran cartel (sponsor premium quiere visibilidad cuando hay eyeballs reales).
- **Backend reusado cero cambios.** Si organizador no configura banners/highlights, cartel ausente — feed salas ocupa 100%.

### Re-estimacion sprints (analisis honesto post-entrega)

Velocidad real ~50% mas rapida por reuso de patrones (parseActionUrl, lumina, useNow, framer split layout, mockBackend bearer scenarios). Roadmap original 22h → realista **11-14h en 3 sesiones DaVinci**.

| Sprint | Cierre 2026-06-29 | Realista 2026-06-30 |
|---|---|---|
| W.13 Fase B Documents | ~1h | **30-40 min** |
| W.18 Hub Personal | ~5-6h CRITICO | **3-4h** riesgo MEDIO (sin espejo Expo) |
| W.8 Networking | ~3h | **1.5-2h** |
| W.4 Replay + in-stream | ~5h | **3h** riesgo MEDIO (depende backend) |
| W.11 sockets criticos | ~2h | **1-1.5h** riesgo ALTO (E2E flaky) |
| W.12 Web Push + Sentry | ~4h | **2-3h** |

Detalle en `memory/project_velocity_analysis_2026_06_30.md`.

### Estado git al cierre

- **`eventos-web` main:** pusheado (cartel digital + 23 tests + E2E + fixtures + mockBackend)
- **`APP EVENTOS` main:** pusheado (PENDIENTES + NEXT-SESSION + memorias)
- **`eventos-backend` feature/magic-link-auth:** `1d8d1e4` finalmente pusheado (announcement on ticket-resolve)

---

## Sesion 2026-06-29 nocturna — W.13 + W.17 + bug SSR hydration

**Total acumulado webapp:** ~436/707 = **62%** (+22 entre 2 sesiones)
**Estado al cierre:** todo en `main` de `eventos-web` (HEAD `c33e794`).

### Modulos avanzados/cerrados HOY (sesion nocturna)

1. **W.13 FAQ "Asistente" con Orb Siri-style — Fase A ~10/17 (~59%)**
   - Split layout literal espejo W.7/W.9/W.14 (wall izq con header+chips+lista questions, panel der con orb+3 estados browsing/thinking/answering)
   - `OrbBlob.tsx` + `orb.css` CSS-puro 4 radial gradients (cyan/pink/purple/core) con morph keyframes y soporte Lux (filter saturate 1.6 brightness 0.95 + colores saturados teal/magenta/indigo, sin "spot dark" feo)
   - `AsistenteView.tsx` con thinking timer 800ms, cambio de pregunta on-the-fly sin disabled state (haptics+optimistic en lugar de bloqueo)
   - Wired a `/soporte?new=true` (CTAs siempre visibles en panel der)
   - Fase B pendiente: Documents (~1h) — `feedback_una_sola_ventana_operativa` aconseja Pages a Fase 2

2. **W.17 Soporte — Casi completo ~13/15 (~87%)**
   - Split layout espejo W.14 (wall izq tickets, panel der 3 estados empty/detail/new)
   - `/api/support` proxy con manejo 403/422/429
   - `lib/support-client.ts` separado de `lib/support.ts` para no importar `next/headers` en cliente
   - Framer-motion stagger + haptics enterprise + AnimatePresence
   - **Soporte vive DENTRO del Asistente** — nav sidebar reducido (8 items, sin item "Soporte"). Entry vive en CTAs del FAQ. X del panel devuelve a `/faq` (subflow)
   - Backend integrado: `EditSupportRequest.php` ahora crea **announcement privado** `eventos://my-support` cuando admin responde un ticket (sin esto el webapp no se enteraba, no recibe push Expo)

3. **W.14 +1 backend integration (11/20)** — el announcement privado del punto anterior

### Bugs detectados y arreglados (cronicos en CI)

1. **`sponsors:123` flake cronico** — root cause profundo: `SponsorsView.tsx:35` usaba `useState(() => Date.now())` para `shuffleSeed`. SSR generaba seed A, hidratacion generaba seed B → orden de cards distinto SSR vs cliente → React dispara `Hydration failed` → Next dev overlay tapa la pagina → Playwright no podia clickear. Fix: seed inicial `0` (estable SSR), el primer interval tick a los 7s arranca el shuffle vivo (commit `bbff874`).

2. **5 componentes mas con el mismo patron SSR-unsafe** — `AgendaView`, `LiveState`, `PreState`, `GoldenTicketPanel`, `RedeemModal`. Creado nuevo hook `src/hooks/useNow.ts` (basado en `useSyncExternalStore`, snapshot SSR=0). Refactor de los 5 con useState anchor + `// eslint-disable-next-line react-hooks/set-state-in-effect` (patron ya usado en el codebase). Commit `c33e794`.

### Polish UI hoy

- `86c433e` — quitar overline (`text-transform: uppercase`) en cards (anuncios/faq/soporte). Solo se mantiene en inputs y badges (espejo Expo).
- `b074259` — quitar accents rojos en `.selected` (`.sp-card.selected`, `.an-card.selected` usaban `var(--accent)` rojo del cliente) + quitar lineas verdes decorativas (`.faq-response-bar`, `.sp-message-bar`). El icono verde semantico `CheckCircle2` en "Respuesta del organizador" se mantiene (comunica significado).

### Decisiones cerradas (no preguntar)

- **Soporte es subflow del Asistente, NO modulo independiente del sidebar.** Sin item "Soporte" en nav. Entry via CTAs del FAQ. X devuelve a `/faq`.
- **Cuando admin responde un ticket → backend crea announcement privado.** Sin esto el usuario webapp no se enteraba (no recibe push Expo). El bell baja el badge cuando lo abren.
- **NO overline en cards.** Solo inputs/badges (espejo Expo).
- **NO accent (rojo del cliente) en `.selected` state.** Usar `--border-strong` neutral.
- **NO barras verticales decorativas** (teal/verde) al lado de response cards.
- **`useState(() => Date.now())` es banned** en componentes SSR-rendered. Usar `useNow` hook nuevo o `useState(0) + useEffect` con eslint-disable comment.

### Conversacion del cierre — recortes de scope pendientes

El usuario pidio status general y le di breakdown DaVinci de todos los modulos pendientes con recomendacion de skips. Resumen para confirmar PROXIMA sesion:

- **W.6 Stories + Photo Contest** → skip (Stories son mobile-first, Photo Contest nicho). Cerrar W.6 en 18/40 como "MVP cerrado, extensiones Fase 2". **Ahorra ~3-4h.**
- **W.16 Live Moments entero** → skip en webapp (sorteos/trivia/golden ticket reveal son mobile-first; los usuarios sacan el celu, no abren laptop). Webapp solo muestra resultados historico. **Ahorra ~6h.**
- **W.2 Sponsors band + lifecycle states home** → skip (ya hay /sponsors, cinematic ya esta). **Ahorra ~5h.**
- **W.11 Sockets RT consolidacion** → recortar de 42 items a ~6 criticos (invalidations para announcements/ratings/photobooth/matchmaking). **Ahorra ~4h.**
- **W.12 Polish + PWA + E2E full** → recortar a Web Push real + Sentry. PWA installable + offline + Lighthouse 100 + E2E full → Fase 1.5 o skip. **Ahorra ~6h.**

**Recount realista sin bloat:** ~22h totales (3-4 sesiones DaVinci) en lugar de 70-80h del roadmap original.

Sprints inmediatos recomendados (orden):
1. W.14 Fase B (Banners + Highlights + RT socket, ~2h)
2. W.13 Fase B (solo Documents, ~1h — Pages a Fase 2)
3. W.18 Hub Personal (perfil editable + settings, ~5-6h — CRITICO)
4. W.8 Networking completar (~3h)
5. W.4 Replay + anuncios in-stream (~5h)
6. W.11 sockets criticos (~2h)
7. W.12 Web Push + Sentry (~4h)

### Estado git al cierre

- **`eventos-web` main HEAD remoto: `c33e794`** — todo pusheado. CI `gh run 28420089131` corriendo, verificar al abrir.
- **`APP EVENTOS` main:** sin commits desde `b266448` (este NEXT-SESSION va a ir como nuevo commit + sumar PENDIENTES-WEBAPP actualizado si quieres).
- **`eventos-backend` feature/magic-link-auth:** commit local `1d8d1e4` (EditSupportRequest crea announcement) **SIN PUSH**. Necesita autorizacion del usuario para push.

### Pendiente actualizar PENDIENTES-WEBAPP.md (counters desactualizados)

- W.13: 0/17 → 10/17 (~59%)
- W.17: 0/15 → 13/15 (~87%)
- W.14: 10/20 → 11/20 (backend announcement on ticket-resolve)
- Total: 414/707 → ~436/707 (~62%)

No lo hice porque el usuario quiso cerrar primero — pedir actualizar al abrir si vale.

---

## Sesion 2 del 2026-06-29 (diurna previa) — Sprint 2.B + 2.C Fase A

**W.14 Fase A entregada 10/20 (50%):**

1. **`lib/announcement-deeplink.ts`** — helper puro `parseActionUrl()` con 13 mappings `eventos://*` → rutas webapp. Verificado contra grep backend: `GameController:685` + `GoldenTicketResource:144,232` + `EventPhotoResource:237,364` ya generan `action_url: eventos://gamification/rewards` cuando alguien gana — webapp lo mapea a `/desafio` directo. Backlog W.13/W.15/W.17 caen a `internal-future` con toast amable. Externos a `window.open('_blank', noopener,noreferrer)`. Desconocidos a `console.warn` sin romper UI. 23 vitest.
2. **`lib/announcements.ts`** — SSR fetcher con apiFetch + bearer cookie. Backend mezcla publicos por rol + privados `target_attendee_id`.
3. **`lib/announcements-unread.ts`** — helpers puros `countUnread` / `lastSeenKey` / `timeAgo` con `now` inyectable. 16 vitest.
4. **Ruta `/[locale]/(app)/anuncios/page.tsx`** SSR + `AnnouncementsView` cliente con lista vertical espejo Expo. Cards expandibles inline si tienen action_url. Marca `lastSeenAt` al montar para bajar badge bell.
5. **`BellPopover`** reemplaza `<span>` placeholder en `SidebarPill` (W.0). Preview 5 mas recientes + footer "Ver todos" → `/anuncios`. Badge unread via `localStorage:eventos:announcements:lastSeenAt:{eventId}` con sync cross-tab via `storage` event. Lazy init useState (no setState-in-effect). Divergencia intencional vs Expo (mobile bottom tabs vs sidebar desktop, popover ahorra navegacion).
6. **Sidebar nav item Anuncios** (icono Megaphone, available:true).
7. **i18n** namespace `anuncios.*` en es/en/pt (title/subtitle/empty/popover/futureToast/openCta).
8. **Layout integration** `app/[locale]/(app)/layout.tsx` fetch announcements server-side, pasa a SpatialShell → SidebarPill.
9. **E2E `anuncios.spec.ts` 10/10 verde** (15s estable, serial mode): auth gate / SSR sorted by fecha / click eventos://gamification/rewards → /desafio / click sin action_url no nav / click internal-future toast / empty state via bearer "no-announcements" / Bell badge unread cae a 0 al abrir / popover footer Ver todos / popover card golden ticket cierra+nav / sidebar item Anuncios nav.
10. **309/309 vitest verde** (+39 nuevos: 23 deeplink + 16 unread), typecheck OK, lint W.14 clean, build OK.

**W.9 Engagement CERRADO 35/35:**

- Redemptions INLINE en catalogo (eliminado tab "Mis canjes" por espejo Expo)
- `handleShowExistingQR` reusa token sin POST (cero riesgo cobrar puntos doble)
- Bloque "Canjes activos sin catalogo" para rewards retirados
- E2E `desafio.spec.ts` 11/11 verde: 8 funcionales + 3 viewports (desktop 1600 / tablet H 1130 / mobile 390 sin overflow horizontal)
- PARITY-MATRIX sync: W.9 0/35→35/35 + W.7 0/23→23/23 + totales (modulos cerrados 2→5, vitest 194 fail→270 verde, E2E 9→11 specs)

### Decisiones cerradas (no preguntar)

- **Sin tabs "Todos/No leidos"** en anuncios — backend no persiste read_at, tabs serian cosmeticas confusas.
- **Sin modal/panel detail anuncios** — entran completos en card.
- **localStorage:lastSeenAt scopeado por eventId** — multi-evento aislado, sobrevive recarga.
- **BellPopover divergencia intencional** vs contador in-memory de Expo — sidebar lateral webapp ahorra navegacion.
- **Web Push real → W.12 Polish** (no W.14). 8 tipos de push enum documentados.
- **RT socket `announcement:new` → W.14 Fase B** (depende W.11 sockets 20%).
- **Banners + Highlights → W.14 Fase B** (tocan W.2 home).
- **Tab "Mis canjes" descartado** — Expo NO tiene tab. Redemptions inline en catalogo.
- **`test.describe.configure({ mode: "serial" })`** para specs con SSR pesado (5+ fetches) que saturaban dev server con 8 workers paralelos.

---

## Sesiones anteriores

Resumen movido a `memory/sessions_index.md` para no inflar este archivo. Cargar manualmente cuando se necesite contexto historico.
