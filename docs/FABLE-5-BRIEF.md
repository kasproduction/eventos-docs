# Brief para Fable 5 — investigacion exhaustiva W.11 Sockets

> Este archivo es tu mission durante la ventana con Claude Fable 5. Kamilo va a volver a Opus 4.8 despues. Aprovecha el 1M context para hacer TODO el trabajo de investigacion cross-repo AHORA. No vuelvas a codear sin haber verificado esto primero.

---

## MAPA COMPLETO DE CONTEXTO — lee esto primero

### Repos (todos los paths absolutos)

| Repo | Path | Stack | Rol |
|---|---|---|---|
| **Webapp (target)** | `C:\laragon\www\eventos-web\` | Next 16 + React 19 + Tailwind 4, pnpm | LO QUE VAS A MODIFICAR |
| **Expo mobile (source of truth)** | `C:\Users\Kasproduction\Projects\eventos-app\` | Expo SDK 55, React Native, NativeWind | ESPEJO A COPIAR |
| **Backend Laravel** | `C:\laragon\www\eventos-backend\` | Laravel 12 + Filament 3, PostgreSQL | Emite eventos via HTTP → socket server |
| **Socket server** | `C:\laragon\www\eventos-socket\` | Node.js + Socket.IO + Redis DB 2 | Broker central de eventos |
| **Docs hub (aca)** | `C:\laragon\www\APP EVENTOS\` | Markdown | Documentacion + memoria (via .claude/) |
| Kiosk (irrelevante W.11) | `C:\laragon\www\eventos-kiosko\` | Vite + React | Check-in tablets |

### Docs a leer en el hub `APP EVENTOS\`

**Nivel maestro (obligatorios):**
- `docs/NEXT-SESSION.md` — continuidad de la ultima sesion (leelo primero)
- `docs/living/PENDIENTES-WEBAPP.md` — ventana operativa unica con TODOS los W.X y su estado real (12 modulos cerrados 100%, seccion W.11 con detalle de items)
- `docs/webapp/PARITY-MATRIX.md` — fuente unica de verdad Expo↔Webapp↔Backend, cruce endpoint por endpoint
- `docs/webapp/BACKEND-API-MAP.md` — inventario ~197 endpoints backend con shape reference

**Detalle por modulo webapp** (todos en `docs/webapp/W.X-*.md`):
- Especialmente relevantes para W.11: los que ya tienen socket funcionando o esperan RT
- `docs/webapp/W.4-streaming.md`, `W.9-engagement.md`, `W.14-anuncios.md`, `W.17-soporte.md`, `W.6-social.md`, `W.8-networking.md`
- `docs/webapp/PLAN.md` — plan maestro de sprints

**Otros docs:**
- `docs/COMPLETADO.md` — lo que se cerro cronologicamente
- `docs/BUG-LOG.md` — bugs registrados
- `docs/QA-MASTER.md` — QA final Fase 1
- 7 ROADMAP-*.md en `docs/` — planes tacticos por area
- `docs/infra/DISPONIBILIDAD-HA.md` — arquitectura HA prod (Cloudflare R2, etc)

### Memoria persistente Claude Code

**Location:** `C:\Users\Kasproduction\.claude\projects\C--laragon-www-APP-EVENTOS\memory\`

- `MEMORY.md` — index de todas las memorias, se carga AUTOMATICO en cada sesion. Ya lo tenes en el system prompt
- ~90 memorias divididas en:
  - **project_*** — decisiones tecnicas/arquitectura por feature
  - **feedback_*** — reglas DaVinci vivas (~50 memorias con convenciones)
  - **reference_*** — pointers a sistemas externos
  - **sessions_*** — log historico de sesiones
  - **user_*** — perfil de Kamilo

**Memorias criticas para W.11:**
- `project_sockets_realtime_status.md` (NUEVA — leelo, tiene el plan pre-Fable)
- `project_socket_notes.md` — ioredis v5, Redis DB 2, puerto 3001
- `project_socket_architecture.md` — 1 conexion, 4 eventos, debounce, entity keys
- `project_backend_api_map.md` — inventario ~197 endpoints, 0 gaps bloqueantes
- `project_parity_matrix.md` — 117/117 endpoints OK
- `project_scaling_10k.md` — Cache Redis, PM2 cluster
- `project_w4_streaming_implementation.md` — como funciona el socket streaming actual webapp
- `project_realtime_notes.md` — focusManager, socket invalidation, push contingencia

**Memorias DaVinci criticas** (feedback vivo):
- `feedback_davinci.md`, `feedback_davinci_workflow.md`, `feedback_quality_first.md`
- `feedback_no_extra_sockets.md` (1 socket rooms aislados)
- `feedback_webapp_mirror_expo.md` (webapp = espejo Expo en comportamiento)
- `feedback_no_emoji_icons_ui.md`, `feedback_no_pulsing_dots.md`
- `feedback_webapp_fonts.md` (Plus Jakarta + Urbanist)
- `feedback_no_date_now_in_usestate.md` (SSR-safe patterns)
- `feedback_no_fabricate.md` — no inventar cosas
- `feedback_analyze_before_code.md`
- `feedback_no_plan_mode.md`

### Design/prototipos aprobados

`design/` — HTMLs de referencia aprobados por Kamilo:
- `design/W.18-perfil/index.html` — perfil demo (referencia patron reciente)
- `design/features/webapp/Login/iteraciones/login-v7-davinci-FINAL.html` — login incluye rotate overlay
- `design/features/webapp/W0-spatial/home-v2-C-cinematic-MUTE.html` — shell base
- Muchos otros por feature

### Historia — 12 modulos ya cerrados 100% (patterns replicables)

Estos ya tienen split layout wall+detail que W.11 debe respetar (no romper):
- **W.7 Sponsors** (23/23) — `project_w7_sponsors_webapp.md`
- **W.9 Engagement** (35/35) — `project_w9_engagement_webapp.md`
- **W.13 Documents** (15/15) — `project_w13_documents.md`
- **W.14 Anuncios+Cartel** (17/17) — `project_w14_cartel_digital.md`
- **W.17 Soporte** (13/13) — subflow del Asistente
- **W.18 Perfil** (19/19) — `project_w18_hub_personal_blueprint.md`

Todos usan mismo shell W.0 (`CanvasCard` + `SpatialShell` + `SidebarPill`). El hook global W.11 tiene que montar en `(app)/layout.tsx` que envuelve TODOS ellos sin romper.

### Estado git actual

- `eventos-web` main: `f28b4ff` — feature link identity + E2E ampliado
- `eventos-backend` feature/magic-link-auth: `2849fdc` — validator flexible profile
- `APP EVENTOS` main: `74285a0` — este brief + memoria sockets + docs cierre 12 modulos

Todo pusheado. Working tree limpio salvo ruido (screenshots ERRORES + .claude/settings.local.json).

### Perfil del usuario (Kamilo)

- Solo founder + dev + diseñador
- Modo DaVinci — artesano, esencia antes que forma
- Odia: emojis en UI, dots pulsantes, `useState(Date.now())` en SSR, JSZip cliente, fabricar features
- Prefiere: espejo Expo literal, cero inventos, cards visuales agrupando
- Comunicacion: directa, sin rodeos. No preguntar cada 3 pasos. Aprobar propuesta antes de codear pero no micro-pedir permisos

---

## Contexto: por que Fable 5

Kamilo cambio temporalmente a Fable 5 porque Opus 4.7/4.8 no tiene suficiente context para leer TODO el codebase cross-repo (6 repos, muchos archivos por leer). Fable 5 con 1M tokens puede leer:

- Expo mobile completo (hooks + screens + lib + stores)
- Webapp Next.js completo (src + tests + e2e)
- Socket server Node.js completo
- Backend Laravel controllers + observers + jobs relacionados con socket
- Documentacion vieja + memoria

**De una sola pasada.** Sin fragmentar en agents. Sin perder detalles cross-referenciales.

Lo que se necesita: **evitar errores de asumir** que costaron tiempo en la sesion Opus previa (asumir features de Expo, portar eventos que no existian, plan inicial equivocado). Con Fable + 1M context: verificar todo, reportar honesto, plan literal antes de codear.

---

## Lo que YA se sabe (no re-descubrir, verificar solo)

1. **Webapp abre socket SOLO al entrar a `/session-stream/{id}`.** No hay socket global. Fuente: `eventos-web/src/hooks/streaming/*` (4 hooks) + `lib/streaming/socket.ts`.

2. **Expo abre socket al login + join:event inmediato.** Verificado en vivo 2026-07-04: `[socket] connected user=3 attendee=2 role=attendee event=summit-empresarial-2026 conns=1` + `joined event:1`.

3. **Backend socket server emite en produccion** (verificado en `eventos-socket/src/index.ts`):
   - `data:invalidate` (linea 221)
   - `wall:post` + `wall:comment` (linea 193/196)
   - `ban:enforced` (linea 304)
   - `networking:notify` (linea 338)

4. **Expo `hooks/useDataInvalidation.ts`** (467 lineas) escucha 19 listeners cross-modulo. De esos 19:
   - **5 aplican Fase 1 webapp:** data:invalidate, ban:enforced, networking:notify, wall:post, wall:comment
   - **8 son staff:*** — W.15 Vendor (opcional Fase 1)
   - **5 son game:*** + attendance:check — W.16 SKIP webapp
   - **2 nice-to-have Fase 2:** agenda:delayed, checkin:update

5. **Divergencia tecnica:** Expo usa TanStack Query + `queryClient.invalidateQueries`. Webapp usa SSR + `router.refresh()` (patron Next 16). NO portar TanStack Query en bloque — es refactor invasivo.

**Repos y paths:**
- Webapp: `C:\laragon\www\eventos-web\`
- Expo: `C:\Users\Kasproduction\Projects\eventos-app\`
- Socket server: `C:\laragon\www\eventos-socket\`
- Backend Laravel: `C:\laragon\www\eventos-backend\`
- Docs hub: `C:\laragon\www\APP EVENTOS\` (aca estas)

---

## Que TIENES que investigar (en orden de prioridad)

### 1. Auditar los 5 listeners criticos EXACTOS — codigo literal

Para cada uno, extraer del codigo REAL (no descripcion):

**a) `data:invalidate`**
- Expo: leer `eventos-app/hooks/useDataInvalidation.ts` linea 236-259. Copiar handler literal.
- Backend Laravel: grep `internal/data/invalidate` para ver qué disparadores lo emiten (observers, jobs, controllers). Cual entity emite en qué momento
- Socket server: leer `eventos-socket/src/index.ts` linea 221 y el handler completo del internal endpoint
- ENTITY_KEYS map exacto (Expo linea 96-106): qué entidades cubre, qué query keys mapea
- **Payload confirmado:** `{ entity: string }` — sin ID

**b) `ban:enforced`**
- Expo: leer handler completo useDataInvalidation.
- Backend: grep `internal/ban/enforce` para ver qué controlador banea (ModerationController? UserController?). Timing.
- Efecto en cliente: qué guarda en authStore, cual redirect. Verificar si Expo hace clear MMKV.

**c) `networking:notify`**
- Expo: handler literal batched 1500ms. Copiar la logica de batch exacta.
- Backend: grep `NetworkingController` u observador `ContactRequestObserver`. Qué eventos disparan qué type (`request_received` / `request_accepted`).
- Verificar si Expo hace refetch de ['received-requests'] con TanStack o pattern equivalente.

**d) `wall:post` + `wall:comment`**
- Estos NO estan en useDataInvalidation Expo. Estan en `eventos-app/hooks/useWall.ts`. Leer los 2 handlers literales.
- Dedup: Expo usa server ID, NO tempId. Verificar codigo exacto.
- Backend: grep `internal/wall/broadcast` — cuando emite `wall:post` vs `wall:comment`.
- **Cuidado:** el webapp actual W.6 usa `router.refresh()` para agregar posts. Verificar si el hook global necesita hacer dedup local + no ejecutar refresh si el post es del propio user (evitar duplicado con optimistic).

### 2. Verificar contratos backend real

Grep en `eventos-backend/app/`:
- `axios.post.*SOCKET_INTERNAL_URL` o `axios.post.*/internal/` — para saber TODOS los emit endpoints que el backend dispara
- Cual observer / job / controller dispara qué

**Objetivo:** confirmar que TODO lo que el hook webapp va a escuchar tiene un disparador real backend. Si algo no se dispara en produccion, no tiene sentido escucharlo.

### 3. Analizar el `getSocket()` singleton actual del webapp

Leer `eventos-web/src/lib/streaming/socket.ts` COMPLETO. Verificar:
- **Race conditions:** ¿si el hook global monta al mismo tiempo que useChat entra, se dedupa la conexion? El promise cache dice que si, verificar.
- **Reconnect en background:** ¿que pasa si el tab pierde foco? ¿Reconnect on visibility? Testear.
- **Token refresh:** si el bearer Sanctum expira mientras socket abierto, ¿el socket se cae? ¿Se reconecta con token nuevo? ¿O hay que dispose + reconnect manual?
- **Cleanup en logout:** el `disposeSocket()` existe. Verificar donde se llama actualmente.

### 4. E2E strategy para socket

Buscar en `eventos-web/e2e/`:
- ¿Hay algun test que ya use socket real? Probablemente no
- ¿Hay algun mock del socket server? Probablemente no  
- El `e2e/_helpers/mockBackend.mjs` sirve el backend REST. Para socket habria que mockear diferente

**Propuesta:** para tests E2E de W.11, o (a) montar un socket server real en el port 3001 solo para tests, o (b) mockear con spy sobre `getSocket()`. Decidir cual.

### 5. Verificar que el layout `(app)/layout.tsx` puede montar el hook global

Leer el layout actual completo. Verificar:
- Es Server Component, no puede llamar `useEffect`. El hook global TIENE que ser Client Component
- Como envolver: crear `<GlobalSocketProvider>` client component que envuelve al SpatialShell? O al Stage children?
- El eventId ya se obtiene via `fetchPublicEvent(DEFAULT_SLUG)` server-side. Pasarlo como prop al provider

### 6. Comparar cross-repo el modelo mental de "eventId"

En Expo el `eventId` viene del `activeEvent` store. En webapp viene del `PublicEvent` fetchado con `DEFAULT_SLUG`. Verificar que sean el mismo — no vaya a ser que sean cosas distintas.

---

## Que DEBES producir al final

**1 archivo nuevo** en `C:\laragon\www\APP EVENTOS\docs\W.11-SOCKETS-PLAN.md` con:

### Seccion A: Mapa real de eventos verificado

Tabla con: evento | shape payload literal | disparador backend | consumidor Expo (codigo copiado) | consumidor webapp propuesto (codigo copiado) | efecto UX real | riesgo

Con snippets code, no descripciones vagas.

### Seccion B: Codigo listo-para-pegar

**Archivo 1:** `eventos-web/src/hooks/useGlobalSocket.tsx` — el hook completo escrito, no pseudo-code. Con imports correctos, tipos, handlers de los 5 eventos.

**Archivo 2:** `eventos-web/src/app/[locale]/(app)/layout.tsx` — el diff exacto (que agregar, que envolver).

**Archivo 3 (si aplica):** `eventos-web/src/lib/streaming/socket.ts` — cambios menores si hicieran falta (dispose on visibility change, cleanup on logout, etc).

### Seccion C: Tests

**Vitest** para el hook (mock socket, verificar handlers).
**E2E strategy** decidida y documentada (con codigo si aplica).

### Seccion D: Deuda tecnica identificada

Cosas que se descubrieron pero NO se resuelven en este sprint. Ejemplos:
- `join:event` duplicado en `useChat` (queda idempotente pero es feo)
- Token refresh mientras socket abierto (posible issue si sesion pasa 24h)
- Optimistic updates + wall:post: el user propio va a ver su post 2 veces (optimistic + socket)

Con nota de cual afecta comportamiento vs cual es solo higiene.

### Seccion E: Handoff a Opus 4.8

Cuando Kamilo vuelva a Opus 4.8, el archivo `W.11-SOCKETS-PLAN.md` debe ser LO SUFICIENTEMENTE COMPLETO para que Opus solo tenga que:
1. Leer el archivo
2. Copiar el codigo listo
3. Correr tests
4. Commit + push

Sin re-investigar. Sin decidir arquitectura. Sin preguntar shapes.

---

## Reglas de trabajo Fable

1. **NO codear directamente en el webapp durante la investigacion.** Solo producir el archivo `W.11-SOCKETS-PLAN.md`. Kamilo va a validar el plan primero, y despues Opus (o Fable mismo si Kamilo quiere) ejecuta.

2. **Verificar todo en el codigo real.** Fable tiene 1M context — no hay excusa para asumir.

3. **Escribir en modo DaVinci** — refs Expo literal, no invenciones. Cada handler webapp debe ser espejo funcional del Expo, no re-imaginado.

4. **Cero emojis en codigo o docs.**

5. **Iconografia lucide-react** (no lucide-react-native).

6. **Fuentes webapp:** Plus Jakarta Sans display + Urbanist body.

7. **No romper los 4 hooks streaming existentes** (`useChat`, `useQnA`, `useAnnouncementOverlay`, `useSessionLiveConfig`).

8. **Guardar hallazgos importantes en memoria** — si descubres algo que futuras sesiones deben saber (ej: "el backend NO emite X en produccion aunque el socket server tenga handler"), agregar memoria en `.claude/projects/.../memory/`.

---

## Al terminar

1. Commit + push del `docs/W.11-SOCKETS-PLAN.md`
2. Actualizar `NEXT-SESSION.md` con "Fable termino investigacion, plan listo, siguiente sesion Opus ejecuta"
3. Actualizar memoria si aplica
4. Reportar a Kamilo el resumen del hallazgo + tiempo estimado real de implementacion

**Tiempo esperado de investigacion:** 30-60 min de lectura + escritura. El codeo real se hace despues en Opus 4.8.
