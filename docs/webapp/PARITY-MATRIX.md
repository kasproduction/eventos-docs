# PARITY MATRIX — Expo ↔ Webapp ↔ Backend

> **Fuente unica de verdad** para responder: ¿que falta en webapp para que un evento corra 100% sin app mobile?
>
> Cruza cada feature/pantalla del Expo con su modulo webapp planeado (W.X), su estado real implementado en `eventos-web`, los endpoints backend que usa, y los gaps concretos.
>
> **Generado:** 2026-05-21 (auditoria de 4 fases — Expo screens, docs W.X, eventos-web codigo, backend Laravel)
> **Re-auditado:** 2026-06-20 — corregido desfase doc vs codigo (W.5/W.6/W.10 estaban mal contados)
> **Backend:** 117/117 endpoints criticos verificados, 0 gaps bloqueantes
> **Solo mobile** (no aplica web): `mi-qr`, `scanner-invite`, `scanner-stand`, `staff-checkin` — interacciones presenciales (camara, escaneo fisico)
> **Vista parcial en web:** `passport` — ver stamps si aplica, ganarlos requiere QR fisico (mobile)

---

## 1. Resumen ejecutivo

### 1.1 Cobertura por modulo (% items checkbox documentados vs implementados)

| Modulo | Doc W.X | Counter docs | Implementado real | % | Bloquea "solo webapp"? |
|---|---|---|---|---|---|
| W.0 Spatial UI base | shell + sidebar + canvas + home cinematic | 20/24 | Implementado base | 83% | Si (cimiento) |
| W.1 Setup + Auth + UI Foundation | magic link + slideshow + tokens + i18n + Sentry + tests | 102/107 | Implementado | 95% | Si (cimiento) |
| W.1B Backend magic link | endpoints + Filament slides | 10/10 | Cerrado | 100% | No (bloqueante interno) |
| W.2 Home | hero cinematic + happening-now + recap + sponsors band + GamificationHud + anuncios mini + surveys + archive | 10/20 | Cinematic + happening + recap base | 50% | Si |
| W.3 Agenda | lista + filtros + favoritos + detalle + .ics + ratings + RT + lifecycle | 24/30 | Core funcional + ratings | 80% | Si |
| W.4 Streaming | detector source + player + Q&A + chat + polls + trivia + anuncios + replay + layout spatial | 83/111 | Socket + player + Q&A + chat + polls + layout | 75% | Si |
| W.5 Speakers | directorio + featured + perfil + rating + deep link | **33/35** | **CERRADO maximo posible (lista + featured + perfil + rating + deep link + 27 vitest + 13 E2E + memoria + counter)** | **94%** (faltan Lighthouse + device fisico) | Si |
| W.6 Social Wall | feed + posts + comentarios + likes + stories + photo contest + hashtags + filtros | **17/40** | Feed + composer + foto upload + likes optimistic + comentarios editorial + mis posts + 3 tests | **42%** (faltan stories, contest, hashtags, tabs explicitas) | Si |
| W.7 Sponsors | brand wall + profile + favorite + contact + trivia | 0/23 | **No empezado** | 0% | Si |
| W.8 Networking | directorio + suggested + perfil + solicitudes + bloqueados + mi perfil | 15/25 | Directorio + perfil in-slot + solicitudes + vCard/WhatsApp/Email (sin bloqueados ni mi perfil ni filtro role) | ~60% | Si |
| W.9 Engagement | encuestas + leaderboard + logros + passport + rewards + prizes | 0/35 | **No empezado** | 0% | Si |
| **W.10 Live Hub** | hero + side + upcoming + 4 estados visuales + nav | **16/16** | **CERRADO por consenso 2026-06-20** (commit `0e185e6` + E2E 8 escenarios + validado visual con seeder) | **100%** | Si |
| W.11 Sockets RT | client + rooms + listeners + invalidation + fallback | 0/42 | Singleton usado en W.4 streaming | ~20% | Si |
| W.12 Polish + E2E + PWA | responsive + a11y + perf + PWA + Sentry validation | 0/43 | No empezado | 0% | Si (cierre Fase 1) |
| W.13 FAQ + Documentos + Pages | accordion + lista + WebView | 0/17 | **No empezado** | 0% | Si |
| W.14 Anuncios + Boletines + Bell | feed + banners + highlights + BellPopover | 0/19 + bonus | **No empezado** | 0% | Si |
| W.15 Vendor Dashboard | Mi Stand + Leads + Stats + Team | 0/35 | **No empezado** | 0% | **Opcional Fase 1** |
| W.16 Live Moments | trivia + sorteo + photo contest display + golden ticket reveal | 0/23 | **No empezado** | 0% | Si |
| W.17 Soporte | tickets simples + mis consultas | 0/15 | **No empezado** | 0% | Si |
| **W.18 Hub Personal** | perfil editable + Mi QR (solo mobile) + settings + menu user | 2/19 | UserMenu + ThemeToggle parciales | ~10% | Si |
| W.X Welcome Showcase | cinematic 6 beats | 0/7 | **Bloqueado** | 0% | No (tour opcional) |

### 1.2 Totales (post-recount 2026-06-20)

| Indicador | Valor |
|---|---|
| Pantallas Expo (sin solo-mobile) | 33 |
| Modulos webapp planeados | 22 (W.0-W.X + W.18 renombrado) |
| Modulos 100% cerrados | 2 (W.1, W.1B) |
| Modulos casi cerrados (>=75%) | 4 (W.0, W.1, W.5, W.10) |
| Modulos parcialmente implementados | 9 (W.0, W.2, W.3, W.4, W.5, W.6, W.8, W.10, W.11, W.18) |
| Modulos 0% empezados | 10 (W.7, W.9, W.12-W.17, W.X) |
| Endpoints backend cubren features | 117/117 (100%) |
| Endpoints backend bonus disponibles | 34 (Pulse organizer, webhooks, presets, etc) |
| Tests vitest (estado actual) | **194/194 fallando** (drift por pausa de mes — bloqueante cierres formales) |
| Tests Playwright E2E | 9 specs (agenda, auth-gate, home, live, login-form, social, speakers, streaming, verify-page) |

---

## 2. Matriz detallada por pantalla Expo

> Estado real columna: **Done** = implementado en eventos-web. **Parcial** = algunos sub-features. **0%** = no empezado en webapp. **N/A** = solo mobile (presencial). **VIEW** = solo lectura en web.
>
> Bloqueante columna: **CRITICO** = sin esto un evento solo-webapp no corre. **IMPORTANTE** = sin esto el evento funciona pero degradado. **OPCIONAL** = vendor/staff side. **N/A** = exclusivo presencial.

| Pantalla Expo | Feature | Modulo W.X | Estado webapp | Endpoints backend | Gaps concretos | Bloqueante? |
|---|---|---|---|---|---|---|
| `(tabs)/index.tsx` (Home) | Hero + anuncios + agenda viva + GamificationHud + módulos dinamicos | W.2 | Parcial (50%) | branding, modules, happening-now, leaderboard, my-points, passport, highlights | Sponsors band, GamificationHud preview LIVE, anuncios mini, surveys prompt, EventArchive, multi-sede pill | CRITICO |
| `(tabs)/mi-agenda.tsx` | Mi Agenda (favoritos) | W.3 | Done (delegada a AgendaView con prop) | (agenda) | Tab "Mi Agenda" en webapp ya implementada | — |
| `(tabs)/networking.tsx` | Red contactos | W.8 | Parcial (60%) | attendees, suggested-contacts, contacts/request, me/contacts | Bloqueados list + Mi perfil editable + filtros role | CRITICO |
| `(tabs)/profile.tsx` | Perfil usuario | W.10 | Parcial (~10%) | me, me/profile, me/photo, me/onboarding-data, me/registration-fields | Form editable nombre/foto/bio/intereses, settings idioma/tema, cerrar sesion, user menu dropdown completo | CRITICO |
| `about.tsx` | Acerca evento | W.10 o W.13 | 0% | events/{id}/branding (about_text, about_image_url, about_links) | No existe vista en webapp. Considerar agregar a docs W.10 o W.13 | IMPORTANTE |
| `agenda.tsx` | Lista agenda + filtros + favoritos + .ics + rating | W.3 | Done (80%) | agenda, sessions/{id}/favorite, sessions/{id}/rate, my-ratings, calendar.ics | Lifecycle badges (ORIGINAL/AJUSTADA/CANCELADA), conflictos, room check-in boton, recordatorio 10min, RT socket invalidation | IMPORTANTE (resto diferido a W.11, W.12) |
| `anuncios.tsx` | Lista anuncios + deep links | W.14 | 0% | events/{id}/announcements | Lista + detalle + socket RT + deep link handler `eventos://` | CRITICO |
| `assign-staff.tsx` | QR scan asignar staff | N/A | N/A | rooms/admin, rooms/assign | Solo mobile (requiere camara). No portar | N/A |
| `attendee/[id].tsx` | Perfil otro asistente | W.8 | Done (in-slot panel) | attendees/{id}/profile, contacts/request, contacts/block | Implementado como panel der MUTA — verificar tests E2E | — |
| `banners.tsx` | Carousel banners patrocinadores | W.14 | 0% | events/{id}/banners | BannersCarousel auto 5s + imagen/video | IMPORTANTE |
| `documentos.tsx` | Lista documentos descargables | W.13 | 0% | events/{id}/documents | Lista cards icono MIME + size + download | IMPORTANTE |
| `encuestas.tsx` | Encuestas vivas + polls | W.9 | 0% | events/{id}/surveys, polls/{id}/vote | Lista activas/cerradas + voting + socket poll:new/closed | CRITICO |
| `faq.tsx` | FAQ + contact support | W.13 | 0% | events/{id}/faqs, events/support/mine | Accordion 3 estados conversacionales (browsing/thinking/answering) + counter "Mis consultas" | IMPORTANTE |
| `join-team/[token].tsx` | Acepta invitación stand | W.15 | 0% | stands/invitations/{token}, accept, reject | Vista publica sin-login + actualiza hasVendorAccess | OPCIONAL (W.15) |
| `lead-detail.tsx` | Detalle lead | W.15 | 0% | leads/{id}, leads/{id}/edits | Detail drawer notas/tier editable + historial ediciones | OPCIONAL (W.15) |
| `leaderboard.tsx` | Ranking + challenges + premios + Golden Ticket | W.9 + W.16 | 0% | leaderboard, my-points, passport, gamification, me/prizes, me/redemptions, rewards, rewards/redeem, rewards/confirm | Top 10 + my_position + 3 tabs (Leaderboard/Challenges/Rewards) + GoldenTicketModal QR + share rank | CRITICO |
| `leads.tsx` | Lista leads scanned (vendor) | W.15 | 0% | leads/{eventId}, leads/export | FlashList grouped por fecha + tier badges + FAB scanner (no aplica web — solo lista) | OPCIONAL (W.15) |
| `mi-equipo.tsx` | Team management vendor | W.15 | 0% | me/stand, stands/members/invite-by-*, stands/members/{id}/remove, stands/members/{id}/transfer, stands/search-attendees, stands/share-link | Slots + Activos + Pendientes + invitar 3-vias + transfer ownership + remove + share link modal | OPCIONAL (W.15) |
| `mi-stand.tsx` | Stand dashboard vendor | W.15 | 0% | stands/{eventId}, leads/{eventId} | Hero sponsor + stats clickables + scanner FAB (mobile only) | OPCIONAL (W.15) |
| `my-support.tsx` | Mis consultas soporte | W.17 | 0% | events/support/mine | Lista cards status + admin_response green bar | CRITICO |
| `pages/[id].tsx` | Pagina CMS dinamica | W.13 | 0% | events/{id}/pages, pages/{id} | WebView iframe/HTML body purificado DOMPurify | IMPORTANTE |
| `passport.tsx` | Passport stamps stands visitados | W.9 | 0% (solo VIEW) | events/{id}/my-passport | Grid stamps + progreso "X/Y stands" + completed banner. Earning requiere QR fisico (mobile-only) | IMPORTANTE (VIEW only) |
| `recap/[eventId].tsx` | Recap post-evento swipe | W.10 (Mi Recap link) | 0% | events/{eventId}/recap, events/{eventId}/my-recap | FlatList horizontal paging cover + cards. Estados: error, disabled, threshold not met. **No esta documentado en docs/webapp/W.X** — gap doc | IMPORTANTE |
| `session/[id].tsx` | Detalle sesion + speakers + rate | W.3 | Done (DetailPanel) | agenda, sessions/{id}/favorite, sessions/{id}/rate, track | Verificar speakers grid clickable → speaker detail + add-to-calendar variants | — |
| `session-chat/[id].tsx` | Chat sesion live + emojis flotantes + polls | W.4 | Parcial | sessions/{id}/chat, polls/{id}/vote, socket chat:* | Emojis flotantes animados (FloatingEmojiItem bounce + fade) — falta en webapp. Pinned message banner. | IMPORTANTE |
| `session-stream/[id].tsx` | Stream multiformat + panels Q&A/chat/polls/trivia | W.4 | Parcial (75%) | sessions/{id}/live-config, stream-url, questions, polls, track | Trivia panel (delegado W.16), anuncios in-stream pinned, replay detection auto-rating, mobile/tablet layout | CRITICO |
| `social.tsx` | Wall + Memorias + Stories + Photo Contest + FAB | W.6 | Parcial (~25% — solo feed + posts editorial) | wall, photos, photos/contest, stories | **Stories bar 24h, Photo Contest banner (countdown + podio top 3), Galeria fotos grid, Photo viewer swipe, Hashtags client-side, Filtros Recientes/Mas likes** | CRITICO |
| `speaker/[id].tsx` | Detalle speaker + rating | W.5 | 0% | speakers/{id}, speakers/{id}/rate | Hero foto + bio + sesiones + LinkedIn + rating modal | CRITICO |
| `speakers.tsx` | Directorio speakers + featured | W.5 | 0% | events/{id}/speakers | Featured carousel + search debounce + alfabetico + deep link | CRITICO |
| `sponsor/[id].tsx` | Detalle sponsor + servicios + trivia + favorito | W.7 | 0% | sponsors/{id}, sponsors/{id}/view, sponsors/{id}/contact, sponsors/{id}/trivia, trivia/{id}/answer, visit-stand | Hero logo + descripcion + sesiones + servicios chips + trivia modal con countdown (auto-trigger on visit) + heart animation + tracking view | CRITICO |
| `sponsor-contact.tsx` | Contacto sponsor (servicios + mensaje) | W.7 | 0% | sponsors/{id}/contact | Chips servicios multiselect + textarea opcional | CRITICO |
| `sponsors.tsx` | Brand Wall por tier + shuffle | W.7 | 0% | sponsors/{eventId} | Grouped tier (platinum 2c, gold 3c, compact 4c) + shuffle 7s + stagger reveal + favoritos | CRITICO |
| `stand-contacts.tsx` | Solicitudes recibidas en stand | W.15 | 0% | stands/{eventId}/contacts | Cards leads que contactaron + acciones whatsapp/email/llamar | OPCIONAL (W.15) |
| `stand-stats.tsx` | KPIs stand vendor | W.15 | 0% | stands/{eventId}/stats, me/leads/export | StatRow pairs + tier bar + member bar + top services + export CSV | OPCIONAL (W.15) |
| `support-contact.tsx` | Formulario crear ticket soporte | W.17 | 0% | events/support | Subject + message textarea + counter | CRITICO |

---

## 3. Tabla por modulo W.X — gaps concretos por implementar

### W.0 — Spatial UI (CRITICO, 83% done)
- [ ] Modulos navegables top-level desde sidebar (hoy solo W.2/W.3 wired)
- [ ] Command palette ⌘K funcional
- [ ] Pre-load vecinos
- [ ] Validar device real iPad portrait/landscape

### W.2 — Home (CRITICO, 50% done)
- [ ] Sponsors logo band sutil
- [ ] GamificationHud preview LIVE
- [ ] Anuncios mini (count badge)
- [ ] Post-event survey prompt
- [ ] EventArchive link estado ENDED
- [ ] Multi-sede pill (si aplica)

### W.3 — Agenda (IMPORTANTE, 80% done)
- [ ] Lifecycle badges ORIGINAL/AJUSTADA/CANCELADA (W.11 dependencia)
- [ ] Conflict detector visual (W.12)
- [ ] Room check-in boton (diferido — endpoint backend `room-checkin/*` existe)
- [ ] Bulk .ics download
- [ ] Recordatorio push 10min antes (W.10)
- [ ] Session-specific chat global vs por-sesion (W.4)
- [ ] RT socket invalidation (W.11)
- [ ] Playwright E2E happy path + edge

### W.4 — Streaming (CRITICO, 75% done)
- [ ] Trivia integration panel (delegado W.16)
- [ ] Anuncios in-stream pinned/announcement/display overlay
- [ ] Custom panel iframe
- [ ] Replay detection + rating modal auto post-stream
- [ ] Mobile/tablet layout (hoy solo desktop 60/20/20)
- [ ] Playwright E2E

### W.5 — Speakers (CRITICO, 94% done — CERRADO al maximo posible)
- [x] Hooks fetchSpeakers + fetchMySpeakerRatings + rateSpeakerRequest
- [x] Featured carousel derivation (BreathingCarousel + getFeatured)
- [x] Lista alfabetica + search 400ms debounce
- [x] DetailPanel slide-in con race protection
- [x] Rating 1-5 UNIQUE con comment 280 chars + 409 silencioso
- [x] Deep link `?id=X` auto-open + `?highlight=sId` agenda
- [x] Tests Vitest 27 (speakersDerive + speakersClient) + Playwright 13 escenarios — verde 2026-06-20
- [x] Memoria `project_w5_speakers_v2.md` con cierre formal 2026-06-20
- [x] Counter PARITY-MATRIX + PENDIENTES-WEBAPP actualizado
- [ ] Lighthouse Performance >=85 + Accessibility >=95 (requiere usuario logueado + devtools)
- [ ] Validar device real laptop + tablet + mobile (requiere dispositivos fisicos)

### W.6 — Social Wall (CRITICO, 42% done — feed editorial implementado, faltan Stories+Contest+Hashtags)
- [x] Feed paginated SSR + Composer max 500 + foto upload via File API
- [x] Heart optimistic + comments inline + mis posts filter
- [x] Tests Vitest (socialDerive + AttendeeProfilePanel) + Playwright (5 escenarios)
- [ ] **Stories bar 24h** (avatares + ring + click → modal viewer progress bars)
- [ ] **Photo Contest banner** (status active/ended, countdown timer en vivo, podio top 3 con medallas + corona, "X fotos participando")
- [ ] **Galeria fotos** grid con reorder por likes durante contest active
- [ ] Photo viewer swipe + like + caption
- [ ] Hashtags client-side parser
- [ ] Filtros Recientes / Mas likes / Mis posts (tabs explicitas en vista Feed)
- [ ] Paginacion UI (SSR carga primera, falta load-more / infinite scroll)

### W.10 — Live Hub (CRITICO, 100% — CERRADO por consenso 2026-06-20)
- [x] SSR fetchHappeningNow + fetchUpNext (deriva agenda + limit 8)
- [x] LiveHubView + LiveHero + LiveSideCard + UpcomingCard
- [x] 4 estados visuales (default 2+N, 1 solo, 0 lives + N upcoming, empty)
- [x] Navegacion contextual (has_stream → /session-stream, sino → /agenda?highlight)
- [x] Slate Mono tokens globales + Lux overrides
- [x] Tests Vitest (live.ts) + Playwright (8 escenarios)
- [x] Validacion visual con LiveHubDemoSeeder (3 lives + 6 upcoming + 4 past)
- [x] **Skip vitest componente y doc maestro** — info redundante con commit `0e185e6` + JSDoc + E2E (anti-regadero)

### W.7 — Sponsors (CRITICO, 0% done)
- [ ] Hooks useSponsors + useFavorite + useContact
- [ ] Brand Wall grid agrupado tier (platinum 2c, gold 3c, compact 4c) + shuffle 7s
- [ ] Brand Profile tabs (Acerca/Servicios/Sesiones/Contactar)
- [ ] Favorite toggle optimistic + heart animation
- [ ] Contact form servicios multiselect + tracking view fire-forget
- [ ] Trivia integration scope sponsor (auto-trigger on visit-stand)
- [ ] Tests

### W.8 — Networking (CRITICO, ~60% done)
- [ ] **Bloqueados list** + boton desbloquear
- [ ] **Mi perfil editable** (form avatar + bio + intereses multi-select + redes)
- [ ] **Filtro por role** (dropdown attendee/speaker/sponsor)
- [ ] AlertDialog DaVinci en lugar de window.confirm
- [ ] Skeleton mas amigable en AttendeeProfilePanel
- [ ] Playwright E2E (abrir perfil → conectar → solicitud)

### W.9 — Engagement (CRITICO, 0% done — incluye gamification del feedback usuario)
- [ ] **Surveys** lista + votacion con socket poll:new/closed
- [ ] **Leaderboard** top 50 + my_position sticky + share rank
- [ ] **Mis Logros** grid actions (NO badges como entidad)
- [ ] **Passport stamps** VIEW grid + progress (earning requiere mobile)
- [ ] **Rewards** catalogo + redeem flow + my-prizes + Golden Ticket modal con QR
- [ ] Toast +X puntos via diff tracking (no hay socket dedicado)

### W.18 — Hub Personal (CRITICO, ~10% done — renombrado desde W.10 viejo el 2026-06-20)
- [ ] Form perfil editable (nombre + redes + foto + bio)
- [ ] Onboarding bio + intereses + registration-fields editables
- [ ] Settings idioma es-CO/en/pt-BR + tema Noir/Lux
- [ ] Cerrar sesion completo
- [ ] User menu dropdown condicional (vendor/premios/canjes/soporte links)
- [ ] Mi Recap link
- [x] ~~Renombrar `docs/webapp/W.10-notificaciones-perfil.md` → `docs/webapp/W.18-hub-personal.md`~~ (hecho 2026-06-20)

### W.11 — Sockets RT (CRITICO, ~20% done)
- [ ] Socket singleton + connection management exponential backoff
- [ ] useSocketRoom join/leave hook
- [ ] Listeners para 28 eventos S→C catalogados (chat/Q&A/polls/wall/networking/anuncios/stand/gamification/auth-ban)
- [ ] Dedup tempId chat + skip-self
- [ ] Long-polling fallback
- [ ] Tests

### W.12 — Polish + E2E + PWA (CRITICO cierre, 0% done)
- [ ] Audit responsive 3 viewports device real
- [ ] Skeletons + empty states consistentes
- [ ] Accesibilidad WCAG AA + keyboard nav
- [ ] Performance bundle <200KB + Lighthouse >=85 desktop / >=75 mobile
- [ ] SEO meta OG
- [ ] PWA manifest + SW + install prompt condicional
- [ ] E2E smoke test critical paths
- [ ] Sentry validation prod
- [ ] Migracion SSR → TanStack Query infinite cache + socket invalidation (post-W.11)

### W.13 — FAQ + Documentos + Pages (IMPORTANTE, 0% done)
- [ ] FAQ accordion + search 300ms
- [ ] Documentos lista cards icono MIME + size + download
- [ ] Pages dinamicas detalle HTML purificado DOMPurify

### W.14 — Anuncios + Boletines + Bell (IMPORTANTE, 0% done)
- [ ] AnnouncementsList + detalle + socket RT
- [ ] BannersCarousel auto 5s
- [ ] HighlightsList editorial
- [ ] BellPopover radix con badge count + localStorage `lastSeenAt`

### W.15 — Vendor Dashboard (OPCIONAL Fase 1, 0% done)
- [ ] Mi Stand dashboard hero + tabs
- [ ] Mis Leads lista + detail drawer notas/tier editable
- [ ] Visitantes stand (contacts)
- [ ] Stats KPIs + sparkline + donut
- [ ] Team management 3-vias (QR mobile / search / email)
- [ ] Recibir invitaciones (pagina publica `/staff-invite/{token}`)
- [ ] Export CSV

### W.16 — Live Moments (CRITICO, 0% done)
- [ ] Trivia panel (4 opciones + countdown + scoreboard via display:project)
- [ ] Sorteo/Spin/Jackpot ceremony GSAP full-screen modal
- [ ] Photo contest feed + likes (overlap W.6, posiblemente reuso)
- [ ] **Golden Ticket reveal** announcement-driven modal confetti (vista premium)

### W.17 — Soporte (CRITICO, 0% done)
- [ ] CreateTicketForm asunto 200 + mensaje 2000 + counters
- [ ] TicketsList ordenada + status + admin_response
- [ ] TicketDetail read-only
- [ ] refetchOnWindowFocus 60s fallback

---

## 4. Endpoints backend — uso por feature

> Resultado de Fase 4: **117/117 endpoints criticos verificados, 0 gaps bloqueantes.**
>
> Backend 100% listo. Cualquier modulo webapp puede shippearse sin esperar trabajo backend.

### 4.1 Distribución por categoría

| Categoria | Endpoints OK | Notas |
|---|---|---|
| Auth | 6/6 | + 8 extras (check-email, register, activate, forgot-password, etc) |
| Eventos / Branding | 4/4 | |
| Agenda / Sesiones | 5/5 | + tracks endpoint extra |
| Speakers | 4/4 | |
| Social Wall / Stories / Photos | 12/12 | |
| Networking / Contactos | 11/11 | |
| Sponsors / Stands | 5/5 | |
| Gamification / Rewards | 11/11 | |
| Q&A Streaming | 4/4 | + chat moderation extra |
| Hub Personal / Perfil | 9/9 | |
| Vendor / Mi Stand | 12/12 | |
| Anuncios / Banners / FAQ / Docs | 7/7 | |
| Recap | 1/1 | my-recap (events/{id}/recap separado existe) |
| Soporte | 2/2 | |
| Live Moments / Games | 3/3 | game/active + join + answer modelo unificado |
| Room Check-in / Staff | 16/16 | room-checkin existe completo |
| Misc | 4/4 | track, health, version |
| **Total** | **117/117** | |

### 4.2 Endpoints bonus (no usados aun, disponibles)

| Categoria | Endpoints | Uso futuro |
|---|---|---|
| Auth flow extendido | check-email, register, activate, verify-identity, forgot-password, reset-password, verify-email, expo-token | Landing publica, registro abierto |
| Eventos lookup | by-slug, onboarding, registration-fields, onboarding-fields | Pre-login slide selector |
| Presets | type, cities/{countryCode} | Forms estaticos |
| Tracks / Intereses | tracks, my-interests (GET/PUT) | Filtros agenda + onboarding |
| Q&A moderation | questions/pending, questions/{id}/moderate (PATCH) | Admin/staff role |
| Pulse organizer dashboard | bootstrap, rooms, checkins, leads, connections, social, leaderboard, ratings | Vista organizer live (NO asistente) |
| Data Center | events, multi | Vista organizer historico (NO asistente) |
| Webhooks | checkin (single + batch) | Integracion external partners |

---

## 5. Bloqueantes priorizados — "evento solo-webapp asistente"

> Lista ordenada por criticidad. Si vendemos webapp standalone a un cliente que NO usa Expo mobile, esto es lo MINIMO que tenemos que cerrar antes de demo:

### CRITICO — sin esto el evento no es funcional en webapp (post-recount 2026-06-20)

> W.5 ya no esta aqui — esta implementado 86%, solo necesita cierre formal (Sprint 1).

1. **W.7 Sponsors (7h)** — Brand Wall + Brand Profile + contact + favoritos. Sin esto los patrocinadores no aparecen.
2. **W.9 Engagement (10h)** — encuestas + leaderboard + passport (VIEW) + rewards + Golden Ticket. Sin esto no hay gamificacion.
3. **W.6 Social completar (3-4h)** — agregar Stories + Photo Contest + Galeria fotos + Hashtags + tabs filtros al feed editorial existente.
4. **W.14 Anuncios + Banners + Bell (3-4h)** — comunicacion en vivo del evento. Sin esto el organizador no puede notificar.
5. **W.18 Hub Personal (5-6h)** — perfil editable + settings + user menu. Sin esto el usuario no puede modificar sus datos.
6. **W.16 Live Moments (6h)** — Trivia + Sorteo + Golden Ticket reveal. Vivacidad del evento.
7. **W.17 Soporte (3h)** — sin esto no hay canal para escalar problemas.

**Total CRITICO post-recount: ~37h** (5 sesiones DaVinci, baja desde 42h por W.5 cosechado)

### IMPORTANTE — sin esto degradado pero funciona

9. **W.2 Home completar (3h)** — sponsors band + GamificationHud + anuncios mini + survey prompt + archive
10. **W.3 Agenda completar (4h)** — lifecycle badges + conflictos + room check-in + recordatorios
11. **W.4 Streaming completar (4h)** — Trivia panel + anuncios in-stream + replay + mobile layout
12. **W.8 Networking completar (3h)** — bloqueados list + mi perfil editable + filtros role
13. **W.13 FAQ + Documentos + Pages (3h)** — ayuda al asistente, contenido informativo
14. **W.11 Sockets RT (6h)** — invalidation push en toda la web. Performance + UX live
15. **Recap webapp (3h)** — recap/[eventId] no esta en docs W.X. Agregar como W.10.X o W.2.recap

**Total IMPORTANTE: ~26h**

### OPCIONAL — solo si el cliente lo pide

16. **W.15 Vendor Dashboard (6h)** — solo si patrocinadores acceden por web
17. **W.X Welcome Showcase (3.5h)** — tour cinematic, no critico
18. **W.12 Polish + PWA + E2E (8h)** — cierre/QA final Fase 1

**Total OPCIONAL: ~17.5h**

---

## 6. Recomendaciones de orden de ataque

Si el objetivo es vender webapp standalone lo antes posible (post-recount 2026-06-20):

**Sprint 0 — Hygiene urgente (2-3h)**
- Reparar suite vitest 194/194 fallando
- Verificar Laragon backend arriba
- Smoke test 6 rutas implementadas
- Decidir screenshot pendiente `design/ERRORES/`

**Sprint 1 — Cierres formales (2-3h)**
- W.5 Speakers cierre (Lighthouse + memoria + counter)
- W.10 Live Hub cierre (vitest componente + doc + counter)
- W.10→W.18 renombrar doc
- W.8 AlertDialog + Skeleton (Tier 1 correcciones)
- W.6 tabs filtros + W.3 bulk .ics + W.0 wire sidebar

**Sprint 2 — Comercial + Gamificacion base (17h)**
- W.7 Sponsors (7h)
- W.9 Engagement (10h)

**Sprint 3 — Comunicacion + Soporte (10h)**
- W.14 Anuncios + Banners + Bell (3-4h)
- W.17 Soporte (3h)
- W.18 Hub Personal (3-4h primera fase)

**Sprint 4 — W.6 completar (3-4h)**
- Stories + Photo Contest + Hashtags + tabs explicitas

**Sprint 5 — Live experience (6h)**
- W.16 Live Moments (Trivia + Sorteo + Golden Ticket reveal)

**Sprint 6 — Contenido informativo (3h)**
- W.13 FAQ + Documentos + Pages

**Sprint 7 — Networking completar (3h)**
- W.8 Bloqueados + Mi perfil editable + Filtro role

**Sprint 8 — Cierre Fase 1 (16-18h)**
- W.11 Sockets RT consolidacion (6h)
- W.12 Polish + PWA + E2E (10-12h)

**Total para vender webapp standalone (post-recount):** ~60-65h (10-12 sesiones DaVinci).
**Con W.15 vendor:** +6h.

---

## 7. Pendientes documentales (gaps del propio doc)

1. **Recap webapp** — `recap/[eventId].tsx` del Expo no tiene contraparte en ningun W.X. Decidir: agregar como W.10.Recap o W.2.Recap o crear W.18 dedicado.
2. **About event** — `about.tsx` del Expo (texto + imagen + links sociales) no tiene W.X explicito. Agregar a W.10 Hub o W.13 Contenido.
3. **Banners home vs Banners modal** — `banners.tsx` del Expo es vista dedicada (uso landing/onboarding?). Decidir si webapp lo usa o si solo va el carousel embebido en home (W.2/W.14).
4. **session-chat estandalone** vs **session-stream con chat panel** — Expo separa pantallas, webapp los integra en streaming. Documentar la decision en W.4.

---

## 8. Como mantener este doc

- **Actualizar al cerrar cada modulo W.X** — marcar estado real, no solo el counter de docs
- **Al detectar gap nuevo** — agregar fila en Tabla 2 con bloqueante CRITICO/IMPORTANTE/OPCIONAL
- **Al verificar backend** — si aparece endpoint nuevo o se renombra, actualizar Tabla 4
- **Al cerrar un Sprint** — mover items de "Bloqueantes priorizados" a "Modulos cerrados"

---

## Referencias

- `docs/webapp/PLAN.md` — master plan modulos
- `docs/webapp/BACKEND-API-MAP.md` — inventario endpoints backend completo
- `docs/webapp/W.X-*.md` — detalle por modulo
- `docs/living/PENDIENTES.md` — pendientes globales del proyecto
- Expo: `C:/Users/Kasproduction/Projects/eventos-app/`
- Webapp: `C:/laragon/www/eventos-web/`
- Backend: `C:/laragon/www/eventos-backend/`
