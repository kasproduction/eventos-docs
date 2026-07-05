# Auditoria espejo Expo ↔ Webapp — errores concretos + revision de docs

> Fable 5, 2026-07-04 (segunda pasada de la ventana). Pedido de Kamilo: buscar errores
> concretos de codigo que rompan el funcionamiento espejo, y revisar TODOS los docs
> porque los errores encontrados podian venir de mala interpretacion.
>
> **Resultado: el instinto era correcto.** Hay una familia de bugs reales de codigo
> (Clase A), una familia de errores documentales que nombraron eventos socket
> inexistentes y se propagaron a las reclasificaciones (Clase B), y correcciones
> que el propio `W.11-SOCKETS-PLAN.md` necesito por heredar una de esas
> interpretaciones (Clase C — ya aplicadas al plan).
>
> Cada hallazgo tiene veredicto: **BUG REAL** / **DOC ERROR** / **DIVERGENCIA OK**
> (intencional y documentada — no tocar).

---

## Clase A — Bugs reales de codigo webapp (rompen espejo)

### A1. Vistas sordas a `router.refresh()` — el mecanismo W.11 no las alcanza — BUG REAL (el mas importante)

**Patron:** copiar props SSR a `useState(initialX)`. React ignora el initial value en
re-renders, asi que cuando `router.refresh()` re-ejecuta el server component y baja
props frescas, estas vistas **no se enteran**. En Expo el equivalente
(`invalidateQueries` → refetch → re-render con data nueva) SI actualiza. Espejo roto.

Inventario completo (grep `useState(initial` en `src/components/app`):

| Vista | State copiado | Se actualiza con refresh? | Impacto espejo |
|---|---|---|---|
| `AgendaView.tsx:59` | `days ← initialDays` | **NO** | Organizador edita sesion / delay / cancela → Expo actualiza agenda en vivo, webapp queda congelada hasta re-navegar. El propio comentario del codigo (lineas 56-58) esperaba que W.11 lo resolviera "via TanStack" — decision que ya se descarto (SSR + refresh). **Sin fix, el `data:invalidate {entity:agenda}` del plan W.11 no tiene efecto visible en /agenda.** |
| `SponsorsView.tsx:29` | `sponsors ← initialSponsors` **sin setter** (frozen) | **NO** | `data:invalidate {entity:sponsors}` inutil en /sponsors. Ademas `favorites` (linea 43) se seedea una sola vez. |
| `SoporteView.tsx:39` | `tickets ← initialTickets` | **NO** | La respuesta del admin NO aparece si el user esta sentado en /soporte (solo via bell → navegacion → SSR fresco). Ver A2. |
| `SocialView.tsx:93-100` | `posts/attendees/suggested/requests/blocked` | **NO** | Ya detectado en la investigacion W.11 — el plan usa bus cliente para wall/networking. `attendees`/`suggested` quedan sordos: cambian poco (registros nuevos), aceptable Fase 2. |
| `SpeakersView.tsx:54` | `const speakers = initialSpeakers` — **prop directa** | **SI** | Correcto. `myRatings` copiado pero es data propia re-hidratada client-side (linea 80). |
| `AnnouncementsView.tsx:47` | deriva de prop con `useMemo` | **SI** | Correcto. |
| `DesafioView.tsx:67-69` | `overview` prop directa | **SI** (wall cards / puntos) | Correcto. Ver A3 para los panels. |
| `DocumentosView` | prop directa | **SI** | Correcto. |
| `BellPopover.tsx:50` | deriva de prop con `useMemo` | **SI** | Correcto. |
| `PerfilView.tsx:62` | `profile` copiado | N/A | Data propia editandose — copiar es correcto aca. |

**Fix (agregado al plan W.11 como Archivos 8-10):** patron R19 "prop-sync tracker" ya
usado en el codebase (`useChat.ts:128-135`): trackear la referencia de la prop y
re-seedear el state cuando cambia. `router.refresh()` produce referencia nueva;
re-renders locales no.

```tsx
const [tracked, setTracked] = useState(initialDays);
if (initialDays !== tracked) {
  setTracked(initialDays);
  setDays(initialDays);
}
```

Race aceptado (identico semanticamente a Expo): si el refresh llega en la ventana
entre un optimistic local y su POST commit, el snapshot server puede pisar el
optimistic por ~1 render. Expo tiene la misma semantica con invalidateQueries.

### A2. `SoporteView` promete "refetch on window focus" que no existe — BUG REAL (doc-comment miente)

`SoporteView.tsx:33` JSDoc: *"Para 'respuesta del admin' refetch on window focus
(sin polling agresivo)"*. Grep `focus|visibilitychange|refetch` en
`components/app/soporte/`: **cero implementacion** (solo matches CSS `:focus`).

Con W.11 + prop-sync (A1) el punto muere solo: la respuesta del admin crea un
announcement con `published_at` (`EditSupportRequest.php:43-50`) → `AnnouncementObserver`
→ `data:invalidate {entity:announcements}` → refresh → SSR fresco → prop-sync
actualiza tickets en vivo. El fix del plan incluye corregir el JSDoc.

### A3. `DesafioView` lazy panels nunca refetchean — BUG MENOR

`DesafioView.tsx:103,115`: guard `length === 0` — una vez fetchados, ranking/rewards/
stamps no se vuelven a pedir nunca (ni reabriendo el panel, ni tras `data:invalidate
{entity:gamification}`). Expo invalida `['my-points']` y TanStack refetchea al
siguiente mount del panel. Impacto real bajo (los puntos del wall SI refrescan via
overview prop directa). → Deuda D.11 del plan, no bloquea.

---

## Clase B — Errores documentales: eventos socket que NO existen (mala interpretacion propagada)

**Causa raiz:** `docs/webapp/W.11-sockets-rt.md` declara *"`eventos-socket/src/types.ts`
es la LISTA AUTORITATIVA"*. **Falso en ambas direcciones:**

- El endpoint generico `/internal/broadcast` (`index.ts:380-400`) emite CUALQUIER
  nombre de evento que Laravel mande — bypasea types.ts por completo.
- types.ts contiene eventos tipados que NADIE emite (dead types).

Eventos reales emitidos en produccion que **NO estan en types.ts** (verificados con
grep de call sites backend):

| Evento | Emisor backend | Quien lo escucha |
|---|---|---|
| `agenda:delayed` | `SessionConfigController:393` | Expo (`useDataInvalidation.ts:368` — toast "agenda retrasada X min") |
| `agenda:updated` | `SessionConfigController:300` | Nadie (dead emit — la agenda se refresca via data:invalidate paralelo, linea 301) |
| `session:cancelled` | `SessionConfigController:266` | Nadie (idem) |
| `game:launched/question/round-result/finished/result/answer-count/update` | `GameController` via `GameService:177` | Expo (5 de los 7) |
| `attendance:check` / `attendance:check:update` | `RoomCheckinController:704/753` | Expo (solo `attendance:check`) |
| `room:occupancy` | `RoomCheckinService:373` | **NADIE PUEDE** — se emite al room `event:{id}:admin` que no existe en `Rooms.ts` y no tiene join handler. Dead emit total (bug latente backend — probablemente para un dashboard admin futuro). |
| `staff:assignment_request/accepted`, `staff:room_unassigned/room_changed` | `StaffNotificationService` (via emit-to-user/staff notify) | Expo |

Evento tipado en types.ts que **NADIE emite** (dead type):

| Evento | Evidencia |
|---|---|
| `announcement:new` | `types.ts:19` lo tipa con payload completo. Grep `announcement:new` en TODO eventos-backend: 0 emisores. Grep en eventos-socket `index.ts`: ningun handler lo emite. Expo NO lo escucha. El mecanismo real de anuncios RT es `data:invalidate {entity:announcements}` (`AnnouncementObserver:15`). |

### Errores concretos por doc

**B1. `docs/webapp/W.11-sockets-rt.md`** — DOC ERROR (multiple):
- Linea ~152: tacha `agenda:delayed` como inexistente ("Se manejan via data:invalidate") — **se emite en produccion** y Expo lo toastea.
- Linea ~160: tacha `session.cancelled` — `session:cancelled` (colon) **se emite** (`SessionConfigController:266`).
- Catalogo lista `announcement:new` como evento vivo ("Anuncio global del evento") — **dead type, cero emisores**.
- La premisa "types.ts = lista autoritativa" es falsa (ver arriba). La lista autoritativa real = types.ts ∪ call sites de `/internal/broadcast` + `/internal/emit-to-user` + `/internal/staff/notify` en Laravel.

**B2. `docs/living/PENDIENTES-WEBAPP.md`** — DOC ERROR (reclasificaciones con nombres falsos):
- Lineas 40, 103, 570, 682, 693: "Socket `announcement:new` → W.11" — el evento no se emite. Lo que W.11 entrega para anuncios RT es `data:invalidate {entity:announcements}` → bell + lista refrescan. El item queda REALMENTE cubierto por el plan W.11, pero con otro mecanismo. **Corregido quirurgicamente en este cierre.**
- Lineas 43, 821: "Socket `support:new_response` → W.11" — evento inexistente en los 4 repos. Mecanismo real: announcement privado (`EditSupportRequest.php:43`, `published_at` seteado) → `data:invalidate {entity:announcements}`. Cubierto por W.11 + prop-sync de SoporteView. **Corregido.**
- Linea 572: "Listener `auth:ban`" — nombre real `ban:enforced`. **Corregido.**
- Linea 497: "Socket `data:invalidate{entity:passport}` → animacion + toast" — la animacion/toast es invento: Expo solo invalida queries en silencio (coherente con `feedback_no_points_diff_toast`). Espejo = refresh silencioso. **Corregido.**
- Linea 573: "Listener `agenda:updated` (futuro lifecycle)" — el evento existe pero es dead emit; el lifecycle real viaja por `data:invalidate {entity:agenda}`. **Corregido.**

**B3. `docs/webapp/PARITY-MATRIX.md`** — DOC ERROR (desactualizacion severa + contradiccion interna):
- §1.1 dice W.7 = 23/23 CERRADO; §3 dice "W.7 Sponsors (CRITICO, 0% done)". Contradiccion en el mismo doc.
- Dos filas W.14 en §1.1 (una 10/20, otra 0/19).
- Totales: "5 modulos cerrados" / "309 vitest" / W.13, W.17, W.18 "No empezado" — realidad 2026-07-04: 12 modulos cerrados, 391 vitest.
- La memoria `project_parity_matrix.md` lo declara "fuente unica de verdad" — hoy NO lo es; la ventana operativa real es PENDIENTES-WEBAPP.md.
- **No se reescribio en esta pasada** (es un re-audit de 4 fases, merece sesion propia o decision de degradarlo a historico). Pendiente decidir con Kamilo.

**B4. `docs/webapp/BACKEND-API-MAP.md` seccion sockets** — el propio W.11-sockets-rt.md
(linea 288) admite que "lista solo 7 eventos". Sigue pendiente desde 2026-05-07.

### Divergencias que estan BIEN (documentadas — no son errores)

- Webapp singleton 1 socket vs Expo multi-socket → la webapp es la correcta; deuda es de Expo (D.2 del plan).
- `game:*` / `attendance:check` sin listeners webapp → W.16 SKIP documentado.
- `staff:*` sin listeners webapp → W.15 opcional documentado.
- BellPopover como divergencia de navegacion vs Expo → decision 2026-06-29 documentada.
- Redemptions inline sin tab "Mis canjes" → espejo Expo verificado, decision documentada.
- `checkin:update` self-refresh de modules (Expo lo hace) → NO portable hoy: el `AuthUser` webapp (`types/event.ts:80-86`) no expone `attendeeId`, no hay contra que comparar. Fase 2 con cambio de shape en `getCurrentUser`. La clasificacion de la memoria queda validada.

---

## Clase C — Correcciones aplicadas a `docs/W.11-SOCKETS-PLAN.md`

El plan de esta manana heredo la interpretacion "refresh actualiza todo" para las
vistas no-social. Con A1 confirmado, el plan **overprometia**: "organizador edita
sesion/sponsor → la webapp refresca sola" era falso para /agenda y /sponsors.

Cambios aplicados al plan (misma ventana, ya editado):

1. **Seccion 0** — hallazgo A1 generalizado (no era solo SocialView) + "Efecto real" corregido.
2. **Seccion B** — 3 archivos nuevos de prop-sync: AgendaView (Archivo 8), SponsorsView (Archivo 9), SoporteView (Archivo 10, incluye fix del JSDoc mentiroso). Patron R19 tracker.
3. **Seccion C** — nota de verificacion viva ampliada: editar una sesion en Filament con /agenda abierta debe reflejarse sin recargar.
4. **Seccion D** — deuda nueva: D.11 (DesafioView lazy panels sin refetch), D.12 (attendees/suggested sordos en SocialView), D.13 (`room:occupancy` dead emit backend), D.14 (`announcement:new` dead type — remover de types.ts o implementar; hoy confunde).
5. **`agenda:delayed` incluido en W.11** — aprobado por Kamilo 2026-07-04. El plan paso de 5 a 6 listeners (handler espejo Expo literal + test + fila en Seccion A). La memoria lo habia clasificado Fase 2 sobre la premisa erronea de que el evento no existia.

---

## Acciones ejecutadas en esta pasada

- [x] `docs/W.11-SOCKETS-PLAN.md` — parcheado (C1-C4) + `agenda:delayed` como 6to listener (aprobado Kamilo).
- [x] `docs/living/PENDIENTES-WEBAPP.md` — nombres de eventos falsos corregidos quirurgicamente (B2), sin tocar counters ni estructura.
- [x] `docs/webapp/W.11-sockets-rt.md` — Drift v2 agregado + catalogo corregido (B1).
- [x] Memoria `project_sockets_realtime_status.md` — hallazgos A1/B1/B2 sumados.
- [x] PARITY-MATRIX.md — **degradado a DOC HISTORICO** (decision Kamilo 2026-07-04): banner al inicio, fuente operativa unica = PENDIENTES-WEBAPP. Matriz S2 + endpoints S4 conservan valor de referencia. Memoria `project_parity_matrix` actualizada.
- [x] `announcement:new` dead type — **removido de `eventos-socket/src/types.ts`** (decision Kamilo): evento + AnnouncementPayload borrados con nota explicativa. Typecheck verde.
- [x] `room:occupancy` dead emit — queda como **deuda registrada** (D.13 del plan W.11): decidir al tocar Mission Control / dashboard admin. Sin urgencia, nada lo consume.
- [x] `agenda:delayed` — **incluido en W.11** (6to listener, ver arriba).
