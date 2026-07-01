# Siguiente sesion — continuidad de contexto

> Este archivo es **solo continuidad** (que hicimos la sesion pasada, decisiones cerradas).
>
> **Para saber que sigue → abrir `docs/living/PENDIENTES-WEBAPP.md`** (ventana operativa unica).

---

## Ultima sesion (cierre 2026-06-30 tarde — W.13 Fase B Documents + arquitectura ZIP escalable)

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
- **`APP EVENTOS` main:** pusheado con NEXT-SESSION + PENDIENTES actualizado + 2 memorias nuevas + 2 memorias updated

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
