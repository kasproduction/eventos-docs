# Siguiente sesion — punto de entrada unico

> **Como usar este archivo:** al arrancar nueva sesion, simplemente decime
> **"siguiente"** o **"next"** y leo este archivo + retomo donde quedamos.
> Yo lo actualizo al cierre de cada sesion (paso del workflow DaVinci).

---

## Ultima sesion

**Fecha:** 2026-05-17 → 2026-05-18
**Que se hizo:** Tres bloques grandes, todo commiteado (sin push):

1. **DaVinci anclado** — refactor de la skill `/siguiente` para que el filtro DaVinci sea PASO 0 obligatorio (auto-chequeo "diseño antes que codigo", refs externas, no iterar mediocre, mobile/web mismo corazon). Memoria `feedback_davinci.md` ampliada con la leccion concreta del panel attendee rechazado 3 veces (gap B de la sesion anterior).
2. **W.6 Social — perfil attendee in-slot (visionOS)** — la columna derecha del modulo MUTA in-place entre el panel default (Sugeridos/Solicitudes/Tu red) y el perfil del attendee, con transicion cross-fade + grid-template-columns 300→360px. NO ruta nueva, NO drawer/sheet. Implementado segun demo HTML `attendee-profile-v2-slot.html` aprobado por el usuario.
3. **W.6 Social — feed editorial inline** — sacado el patron card por post. Posts y composer ahora son inline editorial (avatares 38px, body con `padding-left: 50px`, divider sutil entre posts, sin border/bg/radius). Composer movido fuera del scroll body → queda fijo arriba.

### Gap A previo (avatar beam) — ya commiteado
- `eventos-web 332b2ef` (sin push) — `feat(W.6): avatar beam fallback espejo Expo (SocialAvatar + lib/avatars)`. Tsc verde, vitest 145 verde, lint 0 errores. 10 archivos: nuevo `lib/avatars.ts` + `SocialAvatar.tsx`, refactor en 8 lugares.

### Gap B — perfil attendee in-slot (NUEVO commit)

**Archivos nuevos:**
- `src/components/app/social/AttendeeProfilePanel.tsx` (componente principal, 4 estados + compose flow inline)
- `src/lib/vcard.ts` (buildVCard 3.0 + downloadVCard espejo Expo presentContactForm)
- `src/app/api/social/attendees/[id]/profile/route.ts` (proxy GET → backend Laravel)
- `src/app/[locale]/(app)/social/loading.tsx` (skeleton DaVinci 3 columnas)

**Modificados:**
- `socialClient.ts` — agregado `fetchAttendeeProfileClient`
- `social.css` — bloque `.sn-canvas.sn-profile-open` con grid animado + `.sn-col-right-pane.*` + 280 lineas de reglas `.sn-pp-*` (panel profile)
- `SocialView.tsx` — state `openedPreview: ProfilePreview | null`, handlers `handleOpenProfile/Close/RelationChanged/Blocked`, SidebarRight refactor con dos panes (default + profile), 5 adapters tipo → ProfilePreview, wire-up clicks en SuggestedMiniRow/RequestMiniRow/ContactMiniRow
- `PersonasView.tsx` + `AttendeeCard.tsx` — props onOpenProfile, click en sn-pp-top
- `SolicitudesView.tsx` — props onOpenSender/onOpenReceiver, click en sn-rqx-row con stopPropagation en botones Aceptar/Ignorar

**Bug encontrado por los tests + corregido:** `handleRespond` tenia logica invertida del requestId (`profile?.relation === "request_received" ? undefined : preview.request_id`). Aceptar/Ignorar nunca llamaba al backend. Fix: `const requestId = preview.request_id;`.

### Feed inline editorial (NUEVO commit)

- `social.css` — `.sn-composer` sin border/bg/radius + flex-shrink: 0 (fuera del scroll); `.sn-post` sin card, padding 18px 0, border-bottom sutil, avatar 38px, body+actions con padding-left: 50px (alineados al avatar, espejo Twitter/Linear feed); `.sn-skeleton` tambien inline.
- `FeedView.tsx` — quitado el Composer del FeedView (vive ahora en SocialView fuera del scroll body). Props simplificados.
- `SocialView.tsx` — Composer montado en sn-col-center directamente, condicionado a `view === "feed"`.

### Tests vitest (NUEVO commit) — 49 nuevos, total 194

- `tests/lib/vcard.test.ts` (12) — buildVCard (escape comas/semi/backslash/newlines, orden vCard 3.0, CRLF) + downloadVCard (Blob MIME + slugify + fallback + revokeObjectURL via fakeTimers)
- `tests/lib/socialClient.test.ts` (10) — fetchAttendeeProfileClient (200/401/404/500), sendContactRequest (con y sin message, 409), respondContactRequest (mapeo accept→accepted), blockAttendee
- `tests/components/social/AttendeeProfilePanel.test.tsx` (27) — hero immediate del preview, fetch + bio/intereses/sesiones, los 4 estados de relacion + compose flow + WhatsApp wa.me + mailto + vCard download + click sesion → router.push + Cerrar + Bloquear (confirm OK/Cancel) + socials condicionales + intereses shared vs no

### Demos HTML iterados (en design/features/webapp/SOCIAL-NETWORKING/)

- `attendee-profile-v1-davinci.html` — vista dedicada full page premium editorial (Linear+Dulik+Brella). **Rechazado** por el usuario: "muy grande y exagerado, todo se convirtio en cards"
- `attendee-profile-v2-slot.html` — slot der muta in-place visionOS. **Aprobado**. Replica el modulo completo con la transicion en vivo. 4 estados de relacion + compose flow inline + dev-toggle para alternar estados.

### Decisiones cerradas en esta sesion (no preguntar)

- **DaVinci PASO 0 anclado en la skill `/siguiente`.** Cualquier sesion nueva arranca recitando los 5 principios + auto-chequeo permanente "diseñando o codeando?". Si una propuesta es rechazada 2+ veces NO ajustar tamaño/padding — cuestionar la ARQUITECTURA del feature. Memoria fortalecida con el caso concreto del panel attendee rechazado 3 veces.
- **Perfil attendee = panel der MUTA, NO ruta nueva.** Mirror visionOS — el slot existente se transforma in-place. Cualquier feature similar (perfil de persona dentro de un modulo) debe seguir este patron.
- **Feed sin cards.** Posts y composer son inline editorial. Patron Twitter/Linear, no Facebook/LinkedIn. Aplicar a futuros feeds del proyecto.
- **Composer fijo fuera del scroll body.** Refactor: el composer no vive en FeedView, vive en SocialView arriba del sn-body. Asi queda quieto cuando scrollean posts.
- **NO existe boton "Enviar mensaje" 1:1 en perfil.** Verificado en Expo (`app/(app)/attendee/[id].tsx`). Chat es por sesion (`chat:session:{id}`), no person-to-person. Mi demo v1 tenia "Enviar mensaje" — era invencion mia, error de fidelidad espejo. Tirado.
- **Connect con mensaje opcional = compose inline** (textarea max 500 dentro de la misma zona de acciones del panel, NO sub-modal). Patron espejo BottomSheet Expo adaptado a slot web.
- **Estados de relacion del perfil:** none → "Conectar con {firstName}" full width → compose | sent → disabled + "Cancelar solicitud" ghost | received → Aceptar + Ignorar 50/50 | contact → 3 contact-rows WhatsApp/Email/vCard (solo si el field existe).
- **Click sesion del perfil → /agenda?highlight={id}** (patron W.5 SpeakersView). Mobile va a /session/{id} pero webapp no tiene esa vista (mirror imperfecto aceptable).
- **Bloquear** sigue usando `window.confirm` por ahora. Pendiente: reemplazar por AlertDialog DaVinci.
- **No actualizar el grid + opacity al mismo tiempo en transiciones simultaneas:** la cancion es animar `grid-template-columns` 280ms (ease-out) + cross-fade + translateX spring-soft con overlap controlado en cada pane.

### Estado git al cierre

- `APP EVENTOS main` — commits pendientes (skill DaVinci + memoria + demos HTML + NEXT-SESSION) — pendiente staging
- `eventos-web main` — 3 commits hechos esta sesion, sincronizado con commit anterior `332b2ef` (gap A). Total 4 commits no pusheados.

### Sesion anterior 2026-05-15/16 — referencia historica

Gap A (avatar beam fallback) commiteado en `332b2ef`. Gap B (perfil attendee) iterado 3 veces como panel flotante y rechazado, que esta sesion convertimos en slot in-place visionOS.

---

## Proxima sesion

### Tarea principal — A decidir entre:

1. **W.6 Gap C — Sugeridos cards grandes** (~45 min). Mover el panel der Sugeridos al centro de Personas como cards grandes con intereses-en-comun resaltados. Mejora UX del flujo "encontrar gente con quien conectar".

2. **W.6 mejoras tecnicas** (1-2 h):
   - Reemplazar `window.confirm` del Bloquear por AlertDialog DaVinci
   - E2E test (Playwright) del flujo: abrir perfil → conectar → confirmar solicitud enviada
   - Skeleton mas amigable dentro del AttendeeProfilePanel (bio + intereses + sesiones placeholder mientras carga, no solo 3 lineas)

3. **W.8 Sponsors** (modulo nuevo). Stand activations, brand profiles, contact forms. Bastante UI premium para diseñar. Memoria `project_sponsors_uiux_notes.md`.

4. **Saltar a otro modulo bloqueante:** W.16 Trivia? Push del backlog? Pregunta abierta.

### Pendientes paralelos

**Backend (cross-team):**
- Featured/keynote como flags reales en DB (pendiente sesion W.5)
- Avg rating threshold ≥3 en lista (W.5)
- Endpoint `/speakers/{id}/sessions/preview` (W.5)

**Mobile parity:**
- Portar "click sesion → agenda highlight" al Expo
- El perfil attendee en mobile sigue siendo screen dedicada `/attendee/[id]` — OK, no se va a tocar (memoria `feedback_webapp_mirror_expo.md`)

**Tracking analytics:**
- Eventos `social.profile_opened`, `social.connection_sent`, `social.connection_message_added`, `social.contact_method_clicked` (whatsapp/email/vcard), `social.profile_closed` (origen)

**Design backlog:**
- Errores 92-99 en `design/ERRORES/` — algunos ya atendidos (98=propuesta panel der muta, 99=scroll composer fijo)
- Auditar resto

---

## Convenciones / contexto operativo

- **Working dir principal:** `C:\laragon\www\APP EVENTOS` (este repo, docs+design)
- **Webapp Next.js:** `C:\laragon\www\eventos-web`
- **Mobile Expo:** `C:\Users\Kasproduction\Projects\eventos-app`
- **Backend Laravel:** `C:\laragon\www\eventos-backend` (vive en Laragon)
- **Modo de trabajo:** DaVinci — calidad sobre cantidad, cero emojis. PASO 0 anclado en `/siguiente`.
- **E2E:** `pnpm test:e2e` levanta auto mockBackend (8101) + dev (3100). Reusa servers entre runs en local. Para reload de fixtures, killear puerto 8101 con `Stop-Process`.
- **Workflow git:** commits cuando usuario diga "commit" / "guardar". Push solo con palabra explicita "push". Nunca skip hooks.
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
