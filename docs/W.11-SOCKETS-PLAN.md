# W.11 Sockets — Plan de implementacion listo-para-ejecutar

> Producido por Fable 5 (1M context) el 2026-07-04. Investigacion exhaustiva cross-repo:
> Expo (`eventos-app`) + webapp (`eventos-web`) + socket server (`eventos-socket`) +
> backend Laravel (`eventos-backend`). Todo lo que sigue esta verificado en codigo real,
> con archivo y linea. Cero asunciones.
>
> **Como usar este doc (Opus 4.8):** leer Seccion 0 y A, copiar los archivos de
> Seccion B en orden, correr los tests de Seccion C, commit. No hay decisiones
> pendientes — todas estan tomadas y justificadas aca.

---

## 0. Resumen ejecutivo

**Que se construye:** hook global de socket (`GlobalSocketProvider`) montado en
`(app)/layout.tsx` que abre la conexion singleton al entrar a cualquier modulo
post-login (hoy solo se abre en `/session-stream`), hace `join:event`, y escucha
los 6 eventos criticos Fase 1: `data:invalidate`, `ban:enforced`,
`networking:notify`, `wall:post`, `wall:comment`, `agenda:delayed`
(el sexto agregado por decision de Kamilo 2026-07-04 tras la auditoria espejo —
existe en produccion aunque types.ts no lo tipa, y Expo lo toastea).

**Efecto UX real tras deploy:**
- Organizador publica/edita anuncio, sesion, sponsor, speaker, highlight →
  la webapp refresca sola (bell badge incluido) sin recargar
- Solicitud de contacto recibida/aceptada → toast lumina batcheado + lista de
  Solicitudes se actualiza en vivo si el user esta en `/social`
- Post/comment nuevo en el Social Wall → aparece en el feed en vivo
- Admin banea → kick inmediato + logout + redirect a login
- Puntos/passport ganados → SSR invalidado (proximo render trae datos frescos)

**Los 4 hallazgos que corrigen el plan previo** (memoria `project_sockets_realtime_status.md`):

1. **`router.refresh()` NO actualiza las vistas que copian props SSR a `useState`.**
   React ignora el initial value en re-renders. Auditoria completa 2026-07-04
   (`docs/AUDITORIA-ESPEJO-2026-07-04.md` hallazgo A1) — vistas sordas al refresh:
   `SocialView` (`:93-100`), **`AgendaView` (`:59` — el propio comentario del codigo
   esperaba TanStack en W.11)**, **`SponsorsView` (`:29` — copia frozen sin setter)**,
   **`SoporteView` (`:39`)**. Sin fix, `data:invalidate` no tiene efecto visible en
   /agenda, /sponsors ni /soporte. Fix: bus cliente para social (Archivos 4-5) +
   patron R19 prop-sync tracker para las otras 3 (Archivos 8-10). Vistas que SI
   derivan de props y funcionan con refresh: `AnnouncementsView`, `BellPopover`,
   `SpeakersView` (lista), `DesafioView` (overview), `DocumentosView`.
2. **`disposeSocket()` existe pero NADIE lo llama** (grep: 0 call sites). Logout
   deja el socket autenticado vivo. Fix incluido (Archivo 8).
3. **`reconnectionAttempts: 5` en webapp vs `Infinity` en Expo.** Una tab dormida
   >~1 min agota los 5 intentos y pierde RT para siempre (nadie vuelve a llamar
   `getSocket()`). Fix: Infinity + reconnect on `visibilitychange` (Archivos 1 y 7).
4. **La webapp NO tiene pantalla ni handling de ban** (grep "banned": 0 hits en
   `src/`). Expo redirige a `/banned`. Fase 1 webapp: toast + logout + redirect
   `/login`. Pantalla dedicada queda como deuda (Seccion D).

**Estimacion honesta de implementacion:** 75-105 min (copiar archivos 20min,
vitest verde 20min, E2E + regresion 25min, verificacion viva con socket server
dev 20min). Subio ~15min vs la primera version por los 3 prop-sync (Archivos 8-10)
que la auditoria espejo demostro obligatorios.

**Correccion post-auditoria (2026-07-04 tarde):** la primera version de este plan
prometia "organizador edita sesion/sponsor → la webapp refresca sola" — era falso
para /agenda, /sponsors y /soporte (hallazgo A1 de `AUDITORIA-ESPEJO-2026-07-04.md`).
Esta version incluye los prop-sync que lo hacen verdad. Ademas: los docs que
mencionaban `announcement:new` y `support:new_response` como eventos socket
nombraban eventos que NO se emiten en produccion — la cobertura real de ambos es
`data:invalidate {entity:announcements}`, que este plan ya escucha.

---

## A. Mapa real de eventos verificado

### A.1 Tabla resumen

| Evento | Payload literal | Room/directed | Disparador backend (verificado) | Consumidor webapp | Riesgo |
|---|---|---|---|---|---|
| `data:invalidate` | `{ entity: string }` | room `event:{id}` o directed | 9 observers + 2 jobs + PointsService (ver A.2) | debounce 800ms → `router.refresh()` | Bajo |
| `ban:enforced` | `{ reason: string, expires_at: string\|null }` | directed por attendeeId, luego server desconecta | `BanController@store:82` | logout + toast + `router.replace('/login')` | Bajo |
| `networking:notify` | `{ type: 'request_received'\|'request_accepted', fromName: string, fromAttendeeId: number }` | directed | `NetworkingController:308` (request) y `:354` (accept) | batch 1500ms → toast + bus + refresh | Medio (batch logic) |
| `wall:post` | `{ id, body, photo_url, likes_count: 0, comments_count: 0, author, author_photo, created_at }` — SIN `liked`/`is_mine` | room `event:{id}` | `WallController:110` (solo si `status === 'published'`) + `WallPostResource.php:148` (aprobacion Filament) | bus → prepend dedup por id en SocialView | Bajo |
| `wall:comment` | `{ id, post_id, body, author, author_photo, created_at }` | room `event:{id}` | `WallController:208` (siempre — comments no se moderan) | bus → +1 count + refetch comments si abiertos | Medio (double count, ver A.6) |
| `agenda:delayed` | `{ room_name: string, minutes: number, affected_sessions: number }` | room `event:{id}` | `SessionConfigController:393` (delay de agenda desde admin) — via `/internal/broadcast` generico, NO esta en types.ts | toast info espejo Expo literal (la data de agenda llega por el `data:invalidate {entity:agenda}` que se emite en paralelo, linea 301) | Bajo |

### A.2 `data:invalidate` — disparadores backend completos

`InvalidationService::broadcast(eventId, entity)` (`app/Services/InvalidationService.php`)
postea a `/internal/data/invalidate` con **throttle server-side 1 evento/entity/segundo**
(Redis TTL 1s, linea 39). El socket server re-emite al room `event:{id}`
(`eventos-socket/src/index.ts:221`).

Disparadores broadcast (grep verificado):

| Entity | Origen |
|---|---|
| `sponsors` | `SponsorObserver` (created/updated) |
| `speakers` | `SpeakerObserver` |
| `agenda` | `SessionTrackObserver`, `EventSessionObserver`, `EventRoomObserver`, `SessionConfigController:450` |
| `modules` | `ModuleObserver` (created/updated/deleted) |
| `highlights` | `HighlightObserver` |
| `branding` | `EventObserver` |
| `announcements` | `AnnouncementObserver` — **solo si `published_at` en save; siempre en delete** |
| `gamification` | `ProcessTriviaRewardsJob:73`, `ProcessSpinRewardsJob:86` |
| `onboarding` | `EditEventOnboardingSettings::afterSave` |
| `leads`, `connections`, `ratings`, `leaderboard` | Solo `PulseSimulate` (comando dev) — **ignorar** |

Disparadores directed (`broadcastToAttendee` → `/internal/emit-to-user`):

| Entity | Origen |
|---|---|
| `gamification` | `PointsService:106` — cada vez que el attendee gana puntos |
| `passport` | `LeadController:162` — al escanear stand |

**Hallazgo cross-repo:** el `ENTITY_KEYS` de Expo (`useDataInvalidation.ts:96-106`)
NO tiene key `modules` — Expo ignora silenciosamente el `data:invalidate` que
`ModuleObserver` emite (solo cubre modules via entity `branding` →
`['branding','modules']`). La webapp SI incluye `modules` en su set (el sidebar
depende de modules). Anotado como deuda Expo en Seccion D.

Handler Expo literal (fuente de verdad de comportamiento, `useDataInvalidation.ts:236-259`):

```ts
socket.on('data:invalidate', (payload: { entity: string }) => {
  const keys = ENTITY_KEYS[payload.entity];
  if (!keys) return;                                 // entity desconocida → ignorar
  if (debounceTimers[payload.entity]) clearTimeout(debounceTimers[payload.entity]);
  debounceTimers[payload.entity] = setTimeout(() => {
    if (payload.entity === 'agenda' && eventId) {    // MMKV disk cache clear
      deleteCached(`agenda_${eventId}`); deleteCached(`mi_agenda_${eventId}`);
    }
    keys.forEach((key) => {
      queryClient.invalidateQueries({ queryKey: [key, eventId], refetchType: 'all' });
    });
    delete debounceTimers[payload.entity];
  }, 800);
});
```

**Divergencia webapp (decidida, no re-preguntar):** Expo debounce POR entity porque
invalida query keys granulares. La webapp usa `router.refresh()` que es global —
un debounce por entity dispararia N refreshes casi simultaneos si llegan entities
distintas. Por eso: **UN solo timer trailing 800ms compartido** entre todas las
entities conocidas. Entities fuera del set (`leads`, etc.) se ignoran igual que Expo.

Ademas Expo hace **reconnect sync** (`useDataInvalidation.ts:220-229`): al
reconectar (no en el primer connect) invalida queries criticas con jitter
`Math.random() * 2000`. Espejo webapp: `router.refresh()` con el mismo jitter.

### A.3 `ban:enforced`

Socket server (`index.ts:296-311`): emite al attendee, invalida su token cache,
marca `isBanned` y **desconecta el socket server-side** (`s.disconnect(true)`).
No hay reconexion posible (el token queda invalido en el cache de auth del socket).

Handler Expo literal (`useDataInvalidation.ts:307-314`):

```ts
socket.on('ban:enforced', (payload: { reason: string; expires_at: string | null }) => {
  const store = useAuthStore.getState();
  if (store.user) store.setAuth(store.token!, { ...store.user, ban: payload });
  router.replace('/banned');
});
```

**Webapp Fase 1 (decidido):** no hay authStore ni pantalla `/banned`. El handler:
1. `POST /api/auth/logout` (borra cookie httpOnly — best effort)
2. `disposeSocket()`
3. `broadcastCrossTab("eventos:logout")` (las otras tabs se enteran — mismo
   mecanismo que `PerfilLogoutModal`)
4. `lumina.error` con el reason (el `LuminaToastViewport` vive en
   `[locale]/layout.tsx:78` — cubre tambien `(auth)`, el toast sobrevive el
   client-side replace)
5. `router.replace("/login")`

Si el user intenta re-login, el backend lo rechaza (middleware CheckBan). Pantalla
banned dedicada → deuda Seccion D.

### A.4 `networking:notify`

Socket server (`index.ts:324-349`): directed por `attendeeConnections` map.
Backend (`NetworkingController.php`):
- `:308` — al crear contact request → `notifyViaSocket($receiver->id, 'request_received', $me->user->name, $me->id)`
- `:354` — al aceptar → `notifyViaSocket($sender->id, 'request_accepted', ...)`

Handler Expo literal — logica de batch EXACTA (`useDataInvalidation.ts:279-304, 411-421`):

```ts
const pendingRequests: string[] = [];
const pendingAccepted: string[] = [];
let batchTimer: ReturnType<typeof setTimeout> | null = null;

const flushBatch = () => {
  if (pendingRequests.length > 0) {
    const msg = pendingRequests.length === 1
      ? `${pendingRequests[0]} quiere conectar contigo`
      : `${pendingRequests.length} nuevas solicitudes de contacto`;
    toast.show({ message: msg, variant: 'info' });
    queryClient.invalidateQueries({ queryKey: ['received-requests', eventId], refetchType: 'all' });
    queryClient.invalidateQueries({ queryKey: ['networking-directory', eventId], refetchType: 'all' });
    pendingRequests.length = 0;
  }
  if (pendingAccepted.length > 0) {
    const msg = pendingAccepted.length === 1
      ? `${pendingAccepted[0]} acepto tu solicitud`
      : `${pendingAccepted.length} contactos aceptaron tu solicitud`;
    toast.show({ message: msg, variant: 'success' });
    queryClient.invalidateQueries({ queryKey: ['my-contacts', eventId], refetchType: 'all' });
    queryClient.invalidateQueries({ queryKey: ['networking-directory', eventId], refetchType: 'all' });
    queryClient.invalidateQueries({ queryKey: ['suggested-contacts', eventId], refetchType: 'all' });
    pendingAccepted.length = 0;
  }
  batchTimer = null;
};

socket.on('networking:notify', (payload: NetworkingNotify) => {
  if (payload.type === 'request_received') pendingRequests.push(payload.fromName);
  else if (payload.type === 'request_accepted') {
    pendingAccepted.push(payload.fromName);
    useNetworkingBadgeStore.getState().incrementContacts();
  }
  if (batchTimer) clearTimeout(batchTimer);
  batchTimer = setTimeout(flushBatch, 1500);   // BATCH_WINDOW_MS
});
```

**Webapp:** batch identico (mismas ventanas, mismos mensajes es-CO hardcoded —
precedente: los toasts de `SocialView` ya estan en espanol hardcoded). El refetch
equivalente: bus `networking:refresh` → `SocialView` (si esta montado) refetchea
received requests via proxy nuevo `/api/social/requests` (Archivo 4) +
`router.refresh()` para las vistas SSR.

### A.5 `wall:post`

**NO esta en `useDataInvalidation`** — vive en `eventos-app/hooks/useWall.ts:69-74`:

```ts
socket.on('wall:post', (post: any) => {
  queryClient.setQueryData<WallPost[]>(['wall-feed', eventId], (prev) => {
    if (!prev) return [{ ...post, liked: false, is_mine: false }];
    if (prev.some((p) => p.id === post.id)) return prev;   // dedup por server ID
    return [{ ...post, liked: false, is_mine: false }, ...prev];
  });
});
```

Dedup por **server ID, no tempId** — confirmado. En la webapp esto cubre el post
propio: `SocialView.handleCreatePost` prependea con el `created.id` real del POST
(`SocialView.tsx:170-182`), asi que cuando el broadcast llegue con ese mismo id,
el dedup lo descarta. Cero duplicados con optimistic.

Emision backend: SOLO posts `published` — si `wall_auto_approve` esta on emite al
crear (`WallController:104-110`); si moderacion manual, emite cuando el admin
aprueba en Filament (`WallPostResource.php:148`). Posts `pending` jamas se
broadcastean.

### A.6 `wall:comment`

Handler Expo literal (`useWall.ts:77-86`):

```ts
socket.on('wall:comment', (comment: any) => {
  queryClient.setQueryData<WallPost[]>(['wall-feed', eventId], (prev) =>
    prev?.map((p) =>
      p.id === comment.post_id ? { ...p, comments_count: p.comments_count + 1 } : p
    )
  );
  queryClient.invalidateQueries({ queryKey: ['wall-comments', comment.post_id] });
});
```

**Bug heredado de Expo detectado:** `addComment` de Expo incrementa
`comments_count` optimista Y el socket handler lo incrementa de nuevo → el autor
ve su comment contado doble hasta el proximo refetch del feed (staleTime 30s).
La webapp NO hereda el bug: el handler webapp **skipea el increment si
`comment.author === displayUserName`** (el optimistic de `InlineComments →
onCommentAdded` ya conto). El refetch de la lista de comments SI es
incondicional (idempotente — el server ya commiteo antes de broadcastear, asi
que el refetch siempre trae la verdad, incluso en carrera con el optimistic).

### A.6b `agenda:delayed` (agregado por decision Kamilo 2026-07-04)

Handler Expo literal (`useDataInvalidation.ts:368-374`):

```ts
socket.on('agenda:delayed', (payload: { room_name: string; minutes: number }) => {
  toast.show({
    message: `${payload.room_name}: agenda retrasada ${payload.minutes} min`,
    variant: 'info',
  });
});
```

Solo toast — Expo ignora `affected_sessions` del payload y NO invalida agenda aca
(el backend emite `data:invalidate {entity:agenda}` en paralelo,
`SessionConfigController:301`, que ya dispara el refresh). Webapp identico:
`lumina.info` con el mismo mensaje.

### A.7 Contratos de conexion verificados

- **Auth:** bearer Sanctum en `handshake.auth.token`. La webapp lo obtiene de
  `/api/auth/socket-token` (route existente, lee cookie httpOnly). Verificado.
- **`join:event`:** el server valida `eventId === user.eventId` (SEC-1.1,
  `index.ts:583-593`) y hace `socket.join()` — **idempotente**, joins repetidos
  son seguros. El `join:event` que ya emite `useChat` (`useChat.ts:216`) queda
  redundante pero inocuo. NO tocar.
- **eventId semantics:** en Expo viene de `authStore.user.eventId`; en webapp de
  `fetchPublicEvent(DEFAULT_SLUG).id`. **Son el mismo id** — el socket server los
  valida contra el mismo `user.eventId` del token, y la webapp es single-event
  por deployment (DEFAULT_SLUG). Cuidado unico: el placeholder de
  `fetchPublicEvent` en error devuelve `id: 0` → el provider guardea `eventId <= 0`.
- **Limite SEC-3.4:** max 5 conexiones por user. El singleton webapp usa 1.
  (Expo en cambio abre 1 en `useDataInvalidation` + 1 MAS por `useWall` al entrar
  al wall — deuda Expo, Seccion D.)
- **Race provider vs useChat:** `getSocket()` cachea el promise (`pendingPromise`)
  — si el provider y `useChat` montan simultaneo, ambos reciben el mismo socket.
  Verificado en `socket.ts:22-61`. Sin race.

---

## B. Codigo listo-para-pegar

Orden de aplicacion: 1 → 10. Archivos 1-2 nuevos, 3-10 diffs. Los prop-sync
(8-10) son OBLIGATORIOS — sin ellos `data:invalidate` no tiene efecto visible
en /agenda, /sponsors ni /soporte (auditoria espejo A1).

### Archivo 1 (NUEVO) — `eventos-web/src/hooks/useGlobalSocket.tsx`

```tsx
"use client";

import { useEffect, useRef } from "react";
import type { Socket } from "socket.io-client";

import { lumina } from "@/components/ui/lumina-toast";
import { broadcastCrossTab } from "@/hooks/useCrossTabSync";
import { useRouter } from "@/i18n/navigation";
import { disposeSocket, getSocket } from "@/lib/streaming/socket";

/**
 * GlobalSocketProvider — hook global de sockets W.11.
 *
 * Espejo del patron Expo: `useDataInvalidation` (mount-once en el root layout)
 * + los handlers wall de `useWall`. Diferencias tecnicas documentadas en
 * `docs/W.11-SOCKETS-PLAN.md`:
 * - Expo invalida query keys TanStack; la webapp es SSR → `router.refresh()`
 *   con UN debounce global de 800ms (refresh no es granular).
 * - Los eventos que necesitan actualizar state cliente (SocialView) se
 *   re-emiten por un bus module-scope (`useSocketEvent`) — mismo patron
 *   external-store sin Context que `lumina-toast`.
 *
 * NO desconecta el socket en unmount: el singleton lo comparten los 4 hooks
 * de streaming. Solo remueve SUS listeners (mismo patron que useChat).
 */

// ── Payload types (espejo literal WallController + socket server) ────────────

export interface WallPostSocket {
  id: number;
  body: string;
  photo_url: string | null;
  likes_count: number;
  comments_count: number;
  author: string;
  author_photo: string | null;
  created_at: string;
}

export interface WallCommentSocket {
  id: number;
  post_id: number;
  body: string;
  author: string;
  author_photo: string | null;
  created_at: string;
}

interface NetworkingNotifyPayload {
  type: "request_received" | "request_accepted";
  fromName: string;
  fromAttendeeId: number;
}

interface BanEnforcedPayload {
  reason: string;
  expires_at: string | null;
}

/** Resumen de un flush de networking batcheado (para consumers del bus). */
export interface NetworkingRefreshSummary {
  received: number;
  accepted: number;
}

interface SocketBusEvents {
  "wall:post": WallPostSocket;
  "wall:comment": WallCommentSocket;
  "networking:refresh": NetworkingRefreshSummary;
}

// ── Bus module-scope (patron lumina-toast: external store, sin Context) ──────

type BusHandler = (payload: never) => void;
const busListeners = new Map<keyof SocketBusEvents, Set<BusHandler>>();

function emitBus<K extends keyof SocketBusEvents>(
  event: K,
  payload: SocketBusEvents[K],
): void {
  const set = busListeners.get(event);
  if (!set) return;
  for (const fn of set) (fn as (p: SocketBusEvents[K]) => void)(payload);
}

/**
 * Suscribe un componente cliente a un evento re-emitido por el provider.
 * El handler vive en un ref — no hace falta memoizarlo en el caller.
 */
export function useSocketEvent<K extends keyof SocketBusEvents>(
  event: K,
  handler: (payload: SocketBusEvents[K]) => void,
): void {
  const handlerRef = useRef(handler);
  useEffect(() => {
    handlerRef.current = handler;
  });
  useEffect(() => {
    const fn = (payload: SocketBusEvents[K]) => handlerRef.current(payload);
    let set = busListeners.get(event);
    if (!set) {
      set = new Set();
      busListeners.set(event, set);
    }
    set.add(fn as BusHandler);
    return () => {
      set.delete(fn as BusHandler);
    };
  }, [event]);
}

// ── Constantes (espejo Expo useDataInvalidation) ─────────────────────────────

const INVALIDATE_DEBOUNCE_MS = 800;
const BATCH_WINDOW_MS = 1500;
const RECONNECT_JITTER_MS = 2000;
const LOGOUT_BROADCAST_KEY = "eventos:logout";

// Entities que la webapp renderiza. Espejo ENTITY_KEYS Expo + `modules`
// (el sidebar depende de modules; Expo la pierde — deuda suya, no nuestra).
// `leads`/`connections`/`ratings`/`leaderboard` son del Pulse dashboard: ignorar.
const KNOWN_ENTITIES = new Set([
  "agenda",
  "announcements",
  "sponsors",
  "speakers",
  "highlights",
  "branding",
  "modules",
  "onboarding",
  "gamification",
  "passport",
]);

// ── Provider ─────────────────────────────────────────────────────────────────

interface GlobalSocketProviderProps {
  eventId: number;
  children: React.ReactNode;
}

export function GlobalSocketProvider({
  eventId,
  children,
}: GlobalSocketProviderProps) {
  const router = useRouter();
  const hasConnectedOnce = useRef(false);

  useEffect(() => {
    // fetchPublicEvent devuelve placeholder id=0 si el backend fallo
    if (!eventId || eventId <= 0) return;

    let mounted = true;
    let socket: Socket | null = null;
    const handlers: { event: string; fn: (...args: unknown[]) => void }[] = [];

    let invalidateTimer: ReturnType<typeof setTimeout> | null = null;
    let reconnectTimer: ReturnType<typeof setTimeout> | null = null;
    let batchTimer: ReturnType<typeof setTimeout> | null = null;
    const pendingRequests: string[] = [];
    const pendingAccepted: string[] = [];

    const flushBatch = () => {
      const summary: NetworkingRefreshSummary = {
        received: pendingRequests.length,
        accepted: pendingAccepted.length,
      };
      if (pendingRequests.length > 0) {
        const msg =
          pendingRequests.length === 1
            ? `${pendingRequests[0]} quiere conectar contigo`
            : `${pendingRequests.length} nuevas solicitudes de contacto`;
        lumina.info({ message: msg });
        pendingRequests.length = 0;
      }
      if (pendingAccepted.length > 0) {
        const msg =
          pendingAccepted.length === 1
            ? `${pendingAccepted[0]} acepto tu solicitud`
            : `${pendingAccepted.length} contactos aceptaron tu solicitud`;
        lumina.success({ message: msg });
        pendingAccepted.length = 0;
      }
      batchTimer = null;
      emitBus("networking:refresh", summary);
      router.refresh();
    };

    void getSocket()
      .then((s) => {
        if (!mounted) return;
        socket = s;

        const handleConnect = () => {
          s.emit("join:event", { eventId });
          // Reconnect sync (espejo Expo): al reconectar refrescamos con
          // jitter para no estampidar el backend si 10K tabs reconectan.
          if (hasConnectedOnce.current) {
            const jitter = Math.random() * RECONNECT_JITTER_MS;
            reconnectTimer = setTimeout(() => router.refresh(), jitter);
          }
          hasConnectedOnce.current = true;
        };

        const handleInvalidate = (payload: { entity: string }) => {
          if (!KNOWN_ENTITIES.has(payload.entity)) return;
          if (invalidateTimer) clearTimeout(invalidateTimer);
          invalidateTimer = setTimeout(() => {
            invalidateTimer = null;
            router.refresh();
          }, INVALIDATE_DEBOUNCE_MS);
        };

        const handleBan = (payload: BanEnforcedPayload) => {
          void fetch("/api/auth/logout", { method: "POST" }).catch(() => {});
          disposeSocket();
          broadcastCrossTab(LOGOUT_BROADCAST_KEY);
          lumina.error({
            message: payload.reason
              ? `Acceso suspendido: ${payload.reason}`
              : "Tu acceso al evento fue suspendido",
            duration: 6000,
          });
          router.replace("/login");
        };

        const handleNetworking = (payload: NetworkingNotifyPayload) => {
          if (payload.type === "request_received") {
            pendingRequests.push(payload.fromName);
          } else if (payload.type === "request_accepted") {
            pendingAccepted.push(payload.fromName);
          }
          if (batchTimer) clearTimeout(batchTimer);
          batchTimer = setTimeout(flushBatch, BATCH_WINDOW_MS);
        };

        const handleWallPost = (post: WallPostSocket) => {
          emitBus("wall:post", post);
        };

        const handleWallComment = (comment: WallCommentSocket) => {
          emitBus("wall:comment", comment);
        };

        // Espejo Expo literal (useDataInvalidation.ts:368-374). Solo toast:
        // la agenda se refresca por el data:invalidate {entity:agenda} que el
        // backend emite en paralelo. Evento via /internal/broadcast generico
        // (no esta en types.ts del socket server — verificado en produccion).
        const handleAgendaDelayed = (payload: {
          room_name: string;
          minutes: number;
        }) => {
          lumina.info({
            message: `${payload.room_name}: agenda retrasada ${payload.minutes} min`,
          });
        };

        const subs: [string, (...args: unknown[]) => void][] = [
          ["connect", handleConnect as (...a: unknown[]) => void],
          ["data:invalidate", handleInvalidate as (...a: unknown[]) => void],
          ["ban:enforced", handleBan as (...a: unknown[]) => void],
          ["networking:notify", handleNetworking as (...a: unknown[]) => void],
          ["wall:post", handleWallPost as (...a: unknown[]) => void],
          ["wall:comment", handleWallComment as (...a: unknown[]) => void],
          ["agenda:delayed", handleAgendaDelayed as (...a: unknown[]) => void],
        ];
        subs.forEach(([event, fn]) => {
          s.on(event, fn);
          handlers.push({ event, fn });
        });

        // Socket ya conectado al montar (ej: veniamos de /session-stream) —
        // Socket.IO no re-emite `connect` para listeners tardios.
        if (s.connected) handleConnect();
      })
      .catch(() => {
        // socket-token 401 o socket server caido — la webapp degrada a
        // SSR puro sin romper. Mismo comportamiento que los hooks streaming.
      });

    // Tab dormida que agoto reconnection attempts → reintentar al volver.
    // (Expo usa AppState; el equivalente web es visibilitychange.)
    const handleVisibility = () => {
      if (
        document.visibilityState === "visible" &&
        socket &&
        !socket.connected
      ) {
        socket.connect();
      }
    };
    document.addEventListener("visibilitychange", handleVisibility);

    return () => {
      mounted = false;
      if (invalidateTimer) clearTimeout(invalidateTimer);
      if (reconnectTimer) clearTimeout(reconnectTimer);
      if (batchTimer) clearTimeout(batchTimer);
      document.removeEventListener("visibilitychange", handleVisibility);
      if (socket) {
        // SOLO nuestros handlers — el singleton lo comparten los hooks
        // de streaming (mismo patron que useChat.ts cleanup).
        handlers.forEach(({ event, fn }) => socket?.off(event, fn));
      }
      // NO disposeSocket() aca: navegar entre modulos remonta el layout
      // en algunos flows y matariamos la conexion de streaming.
    };
  }, [eventId, router]);

  return <>{children}</>;
}
```

### Archivo 2 (NUEVO) — `eventos-web/src/app/api/social/requests/route.ts`

Proxy GET para que `SocialView` refetchee solicitudes recibidas en vivo
(equivalente webapp del `invalidateQueries(['received-requests'])` de Expo).
Reusa el fetcher server-side existente — `route handlers` corren en server y
tienen acceso a la cookie.

```ts
import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";

import { fetchReceivedRequests } from "@/lib/social";

/**
 * GET /api/social/requests?event_id=X
 *
 * Solicitudes de contacto recibidas (pendientes). Usado por el listener
 * `networking:refresh` del bus socket (W.11) para actualizar la lista en
 * vivo sin recargar. `fetchReceivedRequests` ya degrada a [] sin bearer.
 */
export async function GET(request: NextRequest) {
  const eventId = Number(request.nextUrl.searchParams.get("event_id"));
  if (!Number.isFinite(eventId) || eventId <= 0) {
    return NextResponse.json({ message: "event_id requerido" }, { status: 400 });
  }
  const data = await fetchReceivedRequests(eventId);
  return NextResponse.json({ data });
}
```

### Archivo 3 (DIFF) — `eventos-web/src/app/[locale]/(app)/layout.tsx`

```diff
 import { SpatialShell } from "@/components/shell/SpatialShell";
 import { TabletRotateOverlay } from "@/components/auth/TabletRotateOverlay";
+import { GlobalSocketProvider } from "@/hooks/useGlobalSocket";
 import { routing } from "@/i18n/routing";
```

```diff
   return (
     <div className="min-h-screen bg-[var(--bg)]">
-      <SpatialShell
-        event={event}
-        announcements={announcements}
-        documentsCount={documents.length}
-      >
-        {children}
-      </SpatialShell>
+      <GlobalSocketProvider eventId={event.id}>
+        <SpatialShell
+          event={event}
+          announcements={announcements}
+          documentsCount={documents.length}
+        >
+          {children}
+        </SpatialShell>
+      </GlobalSocketProvider>
       <TabletRotateOverlay />
     </div>
   );
```

(`GlobalSocketProvider` es client component con `children: ReactNode` — el
server content pasa a traves sin des-serverizar nada.)

### Archivo 4 (DIFF) — `eventos-web/src/components/app/social/SocialView.tsx`

Import nuevo:

```diff
 import { CanvasCard } from "@/components/shell/CanvasCard";
 import { lumina } from "@/components/ui/lumina-toast";
+import {
+  useSocketEvent,
+  type WallCommentSocket,
+  type WallPostSocket,
+} from "@/hooks/useGlobalSocket";
 import { Link } from "@/i18n/navigation";
```

Dentro de `SocialView`, despues del bloque de `useState` (tras la linea
`const myPostsCount = ...`), agregar:

```tsx
  /* ============ Socket W.11 (espejo useWall Expo) ============ */

  // wall:post — prepend con dedup por server ID (useWall.ts:69-74 Expo).
  // El post propio ya se prependeo con el id real en handleCreatePost →
  // el dedup lo descarta cuando llega el broadcast. Cero duplicados.
  useSocketEvent("wall:post", (post: WallPostSocket) => {
    setPosts((cur) => {
      if (cur.some((p) => p.id === post.id)) return cur;
      return [{ ...post, liked: false, is_mine: false }, ...cur];
    });
  });

  // wall:comment — +1 comments_count (useWall.ts:77-86 Expo). Skip si el
  // author soy yo: mi optimistic (onCommentAdded) ya conto. Expo tiene ese
  // double-count — no lo heredamos (docs/W.11-SOCKETS-PLAN.md A.6).
  useSocketEvent("wall:comment", (comment: WallCommentSocket) => {
    if (comment.author === displayUserName) return;
    setPosts((cur) =>
      cur.map((p) =>
        p.id === comment.post_id
          ? { ...p, comments_count: p.comments_count + 1 }
          : p,
      ),
    );
  });

  // networking:refresh — el provider ya mostro el toast batcheado; aca
  // refetcheamos las solicitudes recibidas (equivalente del
  // invalidateQueries(['received-requests']) de Expo).
  useSocketEvent("networking:refresh", () => {
    void fetch(`/api/social/requests?event_id=${event.id}`, {
      credentials: "include",
    })
      .then((r) => (r.ok ? r.json() : null))
      .then((res: { data: ContactRequestItem[] } | null) => {
        if (res) setRequests(res.data);
      })
      .catch(() => {});
  });
```

(`ContactRequestItem` ya esta importado en el archivo. `displayUserName` se
declara antes del bloque — verificado, linea 115.)

### Archivo 5 (DIFF) — `eventos-web/src/components/app/social/InlineComments.tsx`

Import nuevo:

```diff
 import { lumina } from "@/components/ui/lumina-toast";
+import {
+  useSocketEvent,
+  type WallCommentSocket,
+} from "@/hooks/useGlobalSocket";
 import {
   SocialClientError,
```

Dentro de `InlineComments`, despues del `useEffect` de carga inicial:

```tsx
  // W.11: comment nuevo en ESTE post → refetch (espejo del
  // invalidateQueries(['wall-comments', post_id]) de Expo). Idempotente:
  // el server commitea antes de broadcastear, asi que el refetch siempre
  // trae la verdad — incluso en carrera con nuestro propio optimistic.
  useSocketEvent("wall:comment", (comment: WallCommentSocket) => {
    if (comment.post_id !== postId) return;
    fetchWallComments(eventId, postId)
      .then(setComments)
      .catch(() => {});
  });
```

### Archivo 6 (DIFF) — `eventos-web/src/lib/streaming/socket.ts`

Espejo Expo (`useDataInvalidation.ts:206-213`): reintentos infinitos con
backoff cap 5s. Con 5 intentos, una tab dormida >1 min perdia RT para siempre.

```diff
     cachedSocket = io(SOCKET_URL, {
       auth: { token: cachedToken },
       transports: ["websocket", "polling"],
-      reconnectionAttempts: 5,
+      reconnectionAttempts: Infinity,
       reconnectionDelay: 1000,
-      reconnectionDelayMax: 30000,
+      reconnectionDelayMax: 5000,
       timeout: 10_000,
     });
```

### Archivo 7 (DIFF) — `eventos-web/src/components/app/perfil/PerfilLogoutModal.tsx`

Fix del leak encontrado en investigacion: logout no cerraba el socket — la
conexion quedaba viva y autenticada con el token viejo.

```diff
 import { broadcastCrossTab } from "@/hooks/useCrossTabSync";
 import { forgetEmail } from "@/hooks/useLastEmail";
 import { useRouter } from "@/i18n/navigation";
+import { disposeSocket } from "@/lib/streaming/socket";
```

```diff
     forgetEmail();
+    disposeSocket();
     broadcastCrossTab(LOGOUT_BROADCAST_KEY);
     lumina.success({ message: t("logoutOk") });
     router.replace("/login");
```

### Archivo 8 (DIFF) — `eventos-web/src/components/app/agenda/AgendaView.tsx`

Prop-sync tracker R19 (mismo patron que `useChat.ts:128-135`): sin esto,
`data:invalidate {entity:agenda}` → refresh baja `initialDays` fresco pero la
vista no se entera (hallazgo A1 auditoria espejo). El comentario original del
codigo (lineas 56-58) esperaba TanStack en W.11 — decision cambiada a SSR+refresh.

```diff
   // Snapshot mutable solo para flips de favoritos optimistas. El SSR es
-  // estatico per-request, asi que solo seedeamos en mount; las invalidaciones
-  // por socket llegan en F5 (W.11) y reemplazaran este state via TanStack.
+  // estatico per-request; los flips optimistas viven en este state. Cuando
+  // W.11 dispara router.refresh() (data:invalidate agenda), el server baja
+  // un initialDays con referencia nueva y el tracker re-seedea (patron R19,
+  // mismo que useChat). Race optimistic-vs-refresh: semantica identica Expo.
   const [days, setDays] = useState<AgendaData>(initialDays);
+  const [trackedDays, setTrackedDays] = useState(initialDays);
+  if (initialDays !== trackedDays) {
+    setTrackedDays(initialDays);
+    setDays(initialDays);
+  }
```

### Archivo 9 (DIFF) — `eventos-web/src/components/app/sponsors/SponsorsView.tsx`

`sponsors` esta copiado SIN setter (frozen) — se reemplaza por prop directa.
`favorites` se re-seedea con la verdad del server cuando llega snapshot nuevo.

```diff
 export function SponsorsView({ event, initialSponsors }: SponsorsViewProps) {
-  const [sponsors] = useState<Sponsor[]>(initialSponsors);
+  const sponsors = initialSponsors;
   const [search, setSearch] = useState("");
```

```diff
   const [favorites, setFavorites] = useState<Set<number>>(
     () => new Set(initialSponsors.filter((s) => s.is_favorite).map((s) => s.id)),
   );
   const [contactsSent, setContactsSent] = useState<Set<number>>(new Set());
+  // W.11 prop-sync: refresh (data:invalidate sponsors) baja snapshot nuevo —
+  // re-seedear favorites con la verdad del server (espejo refetch Expo).
+  const [trackedSponsors, setTrackedSponsors] = useState(initialSponsors);
+  if (initialSponsors !== trackedSponsors) {
+    setTrackedSponsors(initialSponsors);
+    setFavorites(
+      new Set(initialSponsors.filter((s) => s.is_favorite).map((s) => s.id)),
+    );
+  }
```

### Archivo 10 (DIFF) — `eventos-web/src/components/app/soporte/SoporteView.tsx`

Con esto, la respuesta del admin aparece EN VIVO si el user esta sentado en
/soporte: `EditSupportRequest` crea announcement con `published_at` →
`data:invalidate {entity:announcements}` → refresh → `initialTickets` fresco →
tracker re-seedea. Tambien corrige el JSDoc que prometia un "refetch on window
focus" que nunca existio (hallazgo A2 auditoria).

```diff
- * Tickets son read-only en webapp (espejo Expo — backend no soporta
- * multi-mensaje en un mismo ticket). Para "respuesta del admin"
- * refetch on window focus (sin polling agresivo).
+ * Tickets son read-only en webapp (espejo Expo — backend no soporta
+ * multi-mensaje en un mismo ticket). La "respuesta del admin" llega en
+ * vivo via W.11: announcement privado → data:invalidate → router.refresh
+ * → prop-sync re-seedea tickets.
```

```diff
   const [tickets, setTickets] = useState<SupportTicket[]>(initialTickets);
+  // W.11 prop-sync (patron R19): refresh baja snapshot nuevo del SSR.
+  const [trackedTickets, setTrackedTickets] = useState(initialTickets);
+  if (initialTickets !== trackedTickets) {
+    setTrackedTickets(initialTickets);
+    setTickets(initialTickets);
+  }
```

### NO tocar

Los 4 hooks streaming (`useChat`, `useQnA`, `useAnnouncementOverlay`,
`useSessionLiveConfig`) quedan intactos. El `join:event` de `useChat:216` es
redundante con el del provider pero el server hace `.join()` idempotente
(verificado `index.ts:590-592`). Refactor → deuda, Seccion D.

---

## C. Tests

### C.1 Vitest (NUEVO) — `eventos-web/tests/hooks/useGlobalSocket.test.tsx`

Convencion existente: `tests/hooks/streaming/useChat.dedup.test.ts`. Mocks via
`vi.hoisted` (los factories de `vi.mock` se hoistean).

```tsx
import { act, cleanup, render } from "@testing-library/react";
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

import {
  GlobalSocketProvider,
  useSocketEvent,
  type WallPostSocket,
} from "@/hooks/useGlobalSocket";

const mocks = vi.hoisted(() => {
  type Handler = (...args: unknown[]) => void;

  class FakeSocket {
    connected = false;
    private listeners = new Map<string, Set<Handler>>();
    emitted: { event: string; payload: unknown }[] = [];

    on(event: string, fn: Handler) {
      if (!this.listeners.has(event)) this.listeners.set(event, new Set());
      this.listeners.get(event)!.add(fn);
    }
    off(event: string, fn: Handler) {
      this.listeners.get(event)?.delete(fn);
    }
    emit(event: string, payload?: unknown) {
      this.emitted.push({ event, payload });
    }
    connect() {
      this.connected = true;
      this.fire("connect");
    }
    fire(event: string, payload?: unknown) {
      this.listeners.get(event)?.forEach((fn) => fn(payload));
    }
    listenerCount(event: string) {
      return this.listeners.get(event)?.size ?? 0;
    }
    reset() {
      this.connected = false;
      this.listeners.clear();
      this.emitted = [];
    }
  }

  return {
    fakeSocket: new FakeSocket(),
    getSocketMock: vi.fn(),
    disposeMock: vi.fn(),
    refreshMock: vi.fn(),
    replaceMock: vi.fn(),
    luminaInfo: vi.fn(),
    luminaSuccess: vi.fn(),
    luminaError: vi.fn(),
  };
});

vi.mock("@/lib/streaming/socket", () => ({
  getSocket: mocks.getSocketMock,
  disposeSocket: mocks.disposeMock,
}));

vi.mock("@/i18n/navigation", () => ({
  useRouter: () => ({ refresh: mocks.refreshMock, replace: mocks.replaceMock }),
}));

vi.mock("@/components/ui/lumina-toast", () => ({
  lumina: {
    info: mocks.luminaInfo,
    success: mocks.luminaSuccess,
    error: mocks.luminaError,
  },
}));

async function mountProvider(eventId = 7) {
  render(
    <GlobalSocketProvider eventId={eventId}>
      <div data-testid="child" />
    </GlobalSocketProvider>,
  );
  // flush del promise de getSocket
  await act(async () => {
    await Promise.resolve();
  });
}

describe("GlobalSocketProvider", () => {
  beforeEach(() => {
    vi.useFakeTimers();
    mocks.fakeSocket.reset();
    mocks.getSocketMock.mockResolvedValue(mocks.fakeSocket);
    vi.stubGlobal("fetch", vi.fn().mockResolvedValue({ ok: true }));
    vi.clearAllMocks();
    mocks.getSocketMock.mockResolvedValue(mocks.fakeSocket);
  });

  afterEach(() => {
    cleanup();
    vi.useRealTimers();
    vi.unstubAllGlobals();
  });

  it("al conectar emite join:event con el eventId", async () => {
    await mountProvider(7);
    act(() => mocks.fakeSocket.connect());
    expect(mocks.fakeSocket.emitted).toContainEqual({
      event: "join:event",
      payload: { eventId: 7 },
    });
  });

  it("eventId placeholder (0) no abre socket", async () => {
    await mountProvider(0);
    expect(mocks.getSocketMock).not.toHaveBeenCalled();
  });

  it("data:invalidate entity conocida debounce 800ms → UN refresh", async () => {
    await mountProvider();
    act(() => {
      mocks.fakeSocket.fire("data:invalidate", { entity: "announcements" });
      mocks.fakeSocket.fire("data:invalidate", { entity: "agenda" });
      mocks.fakeSocket.fire("data:invalidate", { entity: "sponsors" });
    });
    expect(mocks.refreshMock).not.toHaveBeenCalled();
    act(() => vi.advanceTimersByTime(800));
    expect(mocks.refreshMock).toHaveBeenCalledTimes(1);
  });

  it("data:invalidate entity desconocida (pulse) se ignora", async () => {
    await mountProvider();
    act(() => mocks.fakeSocket.fire("data:invalidate", { entity: "leads" }));
    act(() => vi.advanceTimersByTime(1000));
    expect(mocks.refreshMock).not.toHaveBeenCalled();
  });

  it("reconexion (2do connect) → refresh con jitter, primer connect no", async () => {
    await mountProvider();
    act(() => mocks.fakeSocket.connect());
    act(() => vi.advanceTimersByTime(2100));
    expect(mocks.refreshMock).not.toHaveBeenCalled();
    act(() => mocks.fakeSocket.fire("connect"));
    act(() => vi.advanceTimersByTime(2100));
    expect(mocks.refreshMock).toHaveBeenCalledTimes(1);
  });

  it("ban:enforced → logout + dispose + toast + replace /login", async () => {
    await mountProvider();
    act(() =>
      mocks.fakeSocket.fire("ban:enforced", {
        reason: "Spam",
        expires_at: null,
      }),
    );
    expect(fetch).toHaveBeenCalledWith("/api/auth/logout", { method: "POST" });
    expect(mocks.disposeMock).toHaveBeenCalled();
    expect(mocks.luminaError).toHaveBeenCalledWith(
      expect.objectContaining({ message: "Acceso suspendido: Spam" }),
    );
    expect(mocks.replaceMock).toHaveBeenCalledWith("/login");
  });

  it("networking:notify x2 request_received batchea → 1 toast plural + refresh", async () => {
    await mountProvider();
    act(() => {
      mocks.fakeSocket.fire("networking:notify", {
        type: "request_received",
        fromName: "Ana",
        fromAttendeeId: 1,
      });
      mocks.fakeSocket.fire("networking:notify", {
        type: "request_received",
        fromName: "Luis",
        fromAttendeeId: 2,
      });
    });
    expect(mocks.luminaInfo).not.toHaveBeenCalled();
    act(() => vi.advanceTimersByTime(1500));
    expect(mocks.luminaInfo).toHaveBeenCalledTimes(1);
    expect(mocks.luminaInfo).toHaveBeenCalledWith({
      message: "2 nuevas solicitudes de contacto",
    });
    expect(mocks.refreshMock).toHaveBeenCalledTimes(1);
  });

  it("networking:notify request_accepted singular → toast success con nombre", async () => {
    await mountProvider();
    act(() =>
      mocks.fakeSocket.fire("networking:notify", {
        type: "request_accepted",
        fromName: "Ana",
        fromAttendeeId: 1,
      }),
    );
    act(() => vi.advanceTimersByTime(1500));
    expect(mocks.luminaSuccess).toHaveBeenCalledWith({
      message: "Ana acepto tu solicitud",
    });
  });

  it("agenda:delayed → toast info espejo Expo", async () => {
    await mountProvider();
    act(() =>
      mocks.fakeSocket.fire("agenda:delayed", {
        room_name: "Sala Principal",
        minutes: 15,
      }),
    );
    expect(mocks.luminaInfo).toHaveBeenCalledWith({
      message: "Sala Principal: agenda retrasada 15 min",
    });
  });

  it("wall:post llega a los subscribers de useSocketEvent", async () => {
    const received: WallPostSocket[] = [];
    function Listener() {
      useSocketEvent("wall:post", (p) => received.push(p));
      return null;
    }
    render(
      <GlobalSocketProvider eventId={7}>
        <Listener />
      </GlobalSocketProvider>,
    );
    await act(async () => {
      await Promise.resolve();
    });
    const post = {
      id: 55,
      body: "Hola",
      photo_url: null,
      likes_count: 0,
      comments_count: 0,
      author: "Ana",
      author_photo: null,
      created_at: "2026-07-04T10:00:00Z",
    };
    act(() => mocks.fakeSocket.fire("wall:post", post));
    expect(received).toEqual([post]);
  });

  it("unmount remueve solo sus listeners", async () => {
    const { unmount } = render(
      <GlobalSocketProvider eventId={7}>
        <div />
      </GlobalSocketProvider>,
    );
    await act(async () => {
      await Promise.resolve();
    });
    expect(mocks.fakeSocket.listenerCount("data:invalidate")).toBe(1);
    unmount();
    expect(mocks.fakeSocket.listenerCount("data:invalidate")).toBe(0);
  });
});
```

Nota: si `render` de provider con eventId=0 dispara el guard antes del mock,
el test 2 es sincrono — el `mountProvider(0)` funciona igual.

### C.2 Estrategia E2E — DECIDIDA

**Opcion elegida: (b) degradacion graceful, sin socket server en CI.**

Razones (verificadas):
- `mockBackend.mjs` es REST puro; ya documenta que los paneles socket del
  streaming "requieren socket.io server real" y se testean sin el
  (`streaming.spec.ts:9-12`). Precedente establecido.
- Montar `eventos-socket` real en el `webServer` de Playwright requiere Redis
  DB 2 corriendo → CI fragil, viola `feedback_reliability_first`.
- La logica de handlers queda 100% cubierta por vitest (C.1). Lo que E2E debe
  garantizar es: **el provider global no rompe NINGUN modulo cuando el socket
  no existe** — que es exactamente el entorno E2E actual.

Spec nuevo (NUEVO) — `eventos-web/e2e/global-socket.spec.ts`:

```ts
import { expect, test } from "@playwright/test";

import { setAuthCookie } from "./_helpers/mockAuth";

/**
 * W.11 Global socket — degradacion sin socket server.
 *
 * El GlobalSocketProvider monta en el layout (app) y pide el bearer a
 * /api/auth/socket-token. En E2E no hay socket server en 3001 — la app
 * debe degradar a SSR puro sin overlay de error ni crash.
 */

test.describe("W.11 Global socket", () => {
  test.describe.configure({ mode: "serial" });

  test.beforeEach(async ({ context }) => {
    await setAuthCookie(context);
  });

  test("provider monta (pide socket-token) y la app navega normal", async ({
    page,
  }) => {
    const tokenRequest = page.waitForRequest((r) =>
      r.url().includes("/api/auth/socket-token"),
    );
    await page.goto("/es/home");
    await tokenRequest;
    await expect(page).toHaveURL(/\/es\/home/);
    // navegacion viva post-mount del provider
    await page.goto("/es/agenda");
    await expect(page).toHaveURL(/\/es\/agenda/);
  });

  test("socket-token 401 no rompe la app", async ({ page }) => {
    await page.route("**/api/auth/socket-token", (route) =>
      route.fulfill({
        status: 401,
        json: { message: "Unauthorized" },
      }),
    );
    await page.goto("/es/home");
    await expect(page).toHaveURL(/\/es\/home/);
  });
});
```

**Regresion obligatoria:** correr la suite E2E completa — el provider monta en
TODOS los modulos (home/agenda/social/sponsors/desafio/documentos/perfil/
anuncios/faq). Si algun spec existente se rompe, el provider tiene un side
effect que no debe tener.

**E2E con socket server real** (happy path completo emit → refresh) → W.12 o
sesion dedicada con `eventos-socket` + Redis en el webServer array. Deuda D.7.

### C.3 Verificacion viva (manual, post-tests)

1. `cd C:/laragon/www/eventos-socket && pnpm dev` (puerto 3001, Redis DB 2)
2. `cd C:/laragon/www/eventos-web && pnpm dev` + login
3. El log del socket debe mostrar `[socket] connected user=X ... conns=1` +
   `joined event:1` **al entrar a /home** (antes solo pasaba en streaming)
4. Filament: editar un anuncio → log `[invalidate] entity=announcements` →
   la webapp refresca el bell sin recargar (~800ms despues)
4b. Con `/agenda` abierta: editar horario de una sesion en Filament → la agenda
   se actualiza sin recargar (valida el prop-sync Archivo 8). Repetir con
   `/soporte` abierta + responder ticket desde Filament (Archivo 10).
5. Con Expo logueado como otro attendee: mandar solicitud de contacto → toast
   webapp en <2s; postear en el wall → post aparece en `/social` en vivo
6. Filament: banear al attendee de la webapp → kick + toast + login
7. Verificar `conns=1` estable — navegar entre modulos NO debe sumar conexiones

---

## D. Deuda tecnica identificada (NO se resuelve en este sprint)

| # | Item | Afecta comportamiento? | Detalle |
|---|---|---|---|
| D.1 | `join:event` duplicado en `useChat:216` | No (idempotente) | Higiene. Cuando el provider global este estable, `useChat` puede dejar de emitirlo. |
| D.2 | **Expo abre sockets paralelos** | Si (Expo, no webapp) | `useDataInvalidation` abre 1 socket y `useWall` abre OTRO (`useWall.ts:59`), cada uno con `io()` propio. Entrar al wall consume 2 de las 5 conexiones max (SEC-3.4). Backlog Expo: migrar useWall al socket compartido. |
| D.3 | Backend emite entity `modules` que Expo no mapea | Si (Expo) | `ENTITY_KEYS` no tiene key `modules` → Expo pierde esa invalidation (la cubre parcialmente via `branding`). Fix Expo: agregar `modules: ['modules']`. |
| D.4 | Double-count de comment propio en Expo | Si (Expo, cosmetico) | Optimistic +1 en `addComment` + socket handler +1 de nuevo. Webapp lo evita con author check (A.6). Fix Expo pendiente. |
| D.5 | Token expirado con socket abierto | Potencial | Sanctum sin expiracion por default en este proyecto. Si se activa expiracion: el socket vivo sigue autenticado hasta desconexion; el proximo `connect_error 'Invalid or expired token'` no tiene handler de re-auth (habria que `disposeSocket()` + `getSocket()` fresco). Documentado, sin accion Fase 1. |
| D.6 | `initialContacts`/`initialSentRequests` no son live | Menor | En `SocialView` son props directas (no state) — `request_accepted` no actualiza "Tu red" en vivo, solo el toast. El proximo mount SSR la trae fresca. Fase 2 si molesta. |
| D.7 | E2E con socket server real | No | Happy path completo emit→UI en E2E requiere `eventos-socket`+Redis en webServer. W.12. |
| D.8 | Pantalla `/banned` dedicada webapp | Menor | Expo tiene screen con reason+expiry persistido en authStore. Webapp Fase 1: toast 6s + login (el backend rechaza re-login del baneado via CheckBan). Pantalla dedicada → Fase 2. |
| D.9 | `networking:notify` se pierde si la tab esta cerrada | Por diseno | Igual que Expo con app cerrada (ahi lo cubre push nativo). Webapp: Web Push es W.12. |
| D.10 | Toasts networking hardcoded es-CO | Menor | Espejo literal Expo (tambien hardcoded). Precedente: toasts de SocialView ya estan en espanol sin i18n. Migrar a next-intl cuando se haga el barrido i18n de toasts. |
| D.11 | `DesafioView` lazy panels nunca refetchean | Menor | Guard `length === 0` (`DesafioView.tsx:103,115`) — ranking/rewards/stamps quedan stale tras `data:invalidate {entity:gamification}` incluso reabriendo el panel. El overview (puntos wall) SI refresca via prop directa. Fix futuro: resetear los arrays en el prop-sync del overview. |
| D.12 | `attendees`/`suggested` sordos en SocialView | Menor | State copiado sin sync — el directorio no refleja registros nuevos en vivo. Cambia poco durante un evento; Fase 2. |
| D.13 | `room:occupancy` dead emit backend | No (nadie lo consume) | `RoomCheckinService:373` emite al room `event:{id}:admin` que NO existe en `Rooms.ts` ni tiene join handler — nadie puede recibirlo. Decidir: implementar room admin o remover el emit. |
| D.14 | `announcement:new` dead type en types.ts | No (confunde docs) | Tipado en `types.ts:19` con payload completo pero CERO emisores en todos los repos. Ya confundio 2 reclasificaciones de docs (ver `AUDITORIA-ESPEJO-2026-07-04.md` B2). Decidir: remover el type o implementar el emit. |

---

## E. Handoff a Opus 4.8 — checklist de ejecucion

1. Leer Seccion 0 (hallazgos) y B (codigo). No re-investigar — todo esta
   verificado con archivo:linea. Contexto adicional de la auditoria espejo:
   `docs/AUDITORIA-ESPEJO-2026-07-04.md`.
2. Crear Archivos 1 y 2 (nuevos), aplicar diffs 3-10. Orden: 1 → 10.
   Los prop-sync 8-10 NO son opcionales.
3. Crear tests C.1 (vitest) y C.2 (E2E spec).
4. `pnpm typecheck && pnpm lint` — cero errores nuevos.
5. `pnpm vitest run` — 391 existentes + ~10 nuevos verdes.
6. `pnpm playwright test e2e/global-socket.spec.ts` y despues **suite completa**
   (regresion: el provider monta en todos los modulos).
7. Verificacion viva C.3 con socket server dev corriendo.
8. Actualizar `docs/living/PENDIENTES-WEBAPP.md` (W.11: los 6 criticos done —
   recontar seccion) + memoria `project_sockets_realtime_status.md` (status:
   implementado) + `docs/NEXT-SESSION.md`.
9. Commit `eventos-web`: `feat(sockets): W.11 global socket provider - 6 listeners criticos`
   — push tras confirmacion de Kamilo (`feedback_git_workflow`).

**Decisiones ya tomadas (NO re-preguntar a Kamilo):**
- Bus module-scope sin Context (patron lumina-toast del propio repo)
- UN debounce global 800ms para `data:invalidate` (refresh no es granular)
- Ban Fase 1 = toast + logout + `/login` (sin pantalla banned)
- Toasts networking espejo Expo literal (es-CO hardcoded)
- `reconnectionAttempts: Infinity` + reconnect on visibilitychange
- Skip increment de `wall:comment` propio (no heredar double-count Expo)
- E2E sin socket server (degradacion graceful); happy path RT → W.12
