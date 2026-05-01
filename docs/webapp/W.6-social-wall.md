# W.6 — Social Wall + Stories + Photo Contest + Hashtags

> Feed social unificado: posts con texto, fotos, mixto + Stories/Momentos (efimeros) + Photo Contest display + Hashtags (#tags clickeables) + Memorias (consolidado).
>
> **Estimacion:** ~10h (expandida de 7h por Stories + Photo Contest + Hashtags).
> **Dependencias:** W.0, W.1, W.16 (Photo Contest detalle).
> **Estado:** Pendiente.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- `W.0-spatial-ui.md`
- App movil: `screens/social/` — Feed unificado (Feed + Memorias + Momentos)
- Memoria proyecto: `project_social_unified_notes.md`

---

## Alcance

1. Feed scrollable con posts (texto, fotos, mixto)
2. Like (heart) optimistic con dedup socket broadcast
3. Comentarios (sub-thread por post)
4. Crear post: texto + foto upload + tags
5. Filtros: Recientes / Mas likes / Mis posts / **Por hashtag**
6. Moderacion (banear post, reportar)
7. RT updates: nuevo post, nuevo comentario, like
8. **Stories/Momentos**: posts efimeros 24h con foto/video corto, lineales arriba del feed (estilo IG Stories)
9. **Photo Contest display**: cuando hay concurso activo, banner + link al modulo W.16
10. **Hashtags**: parseados automaticamente al crear post (#networking, #keynote, etc.) + clickeable filtro
11. **Memorias**: archivo de stories pasadas (consolidado al evento — solo accessible post-evento)

---

## Refs visuales

- App movil social (`features/Screenshot 2026-...`)
- `design/showcase-onboarding-v6.html` `sw-card` — concepto post card
- 30 bugs fixed historicos en social (memoria `project_session_20260409.md`)

---

## Endpoints (verificar)

- `GET /api/v1/event/{id}/wall?cursor&hashtag` — feed paginado, filtro por hashtag
- `POST /api/v1/event/{id}/wall` — crear post (multipart si foto)
- `POST /api/v1/wall/{postId}/like` — like (toggle)
- `GET /api/v1/wall/{postId}/comments` — comentarios
- `POST /api/v1/wall/{postId}/comments` — comentar
- `POST /api/v1/wall/{postId}/report` — reportar
- `GET /api/v1/event/{id}/stories?active=true` — stories efimeros activos (24h)
- `POST /api/v1/event/{id}/stories` — crear story
- `POST /api/v1/stories/{id}/view` — marcar visto (opcional)
- `GET /api/v1/event/{id}/memorias` — archivo de stories pasados (post-evento)
- `GET /api/v1/event/{id}/hashtags?trending=true` — hashtags trending
- `GET /api/v1/event/{id}/photo-contest/active` — saber si hay concurso activo

Socket events:
- `wall.post.new`, `wall.post.banned`
- `wall.comment.new`
- `wall.like.updated`
- `wall.story.new`, `wall.story.expired`

---

## Fase 0 — Hooks (~30min) — 0/3

- [ ] `useSocialFeed(eventId, filters)` — infinite query
- [ ] `usePostComments(postId)`
- [ ] `useCreatePost()` mutation con optimistic

---

## Fase 1 — Feed (~2h) — 0/4

### 1.1 Lista — 0/2
- [ ] `<SocialFeed />` con infinite scroll
- [ ] `<PostCard />` con header (avatar + nombre + tiempo) + texto + imagen + acciones

### 1.2 Skeleton — 0/2
- [ ] Skeleton mientras carga
- [ ] Empty state: "Aun no hay publicaciones. Se el primero!"

---

## Fase 2 — Acciones (~2h) — 0/5

### 2.1 Like — 0/3
- [ ] Heart optimistic con dedup (W.11 implementa dedup completo, aqui solo placeholder)
- [ ] Animacion heart al click (Framer Motion scale + color)
- [ ] Counter incremental con anti-flash

### 2.2 Comentarios — 0/2
- [ ] Click "X comentarios" → expande sub-thread inline
- [ ] Input crear comentario al final del thread

---

## Fase 3 — Crear post (~1.5h) — 0/4

### 3.1 Composer — 0/3
- [ ] `<PostComposer />` modal con textarea (max 500 chars) + imagen upload (max 5MB)
- [ ] Preview imagen antes de enviar
- [ ] Tags opcionales (max 3)

### 3.2 Submit — 0/1
- [ ] Optimistic post aparece en feed con estado "enviando" → "publicado" o "fallido"

---

## Fase 4 — Moderacion (~30min) — 0/2

### 4.1 Reportar — 0/2
- [ ] Boton "..." en post → menu con "Reportar"
- [ ] Razones: spam, inapropiado, otro

---

## Fase 4.5 — Stories/Momentos (~1.5h) — 0/5

### 4.5.1 Componente — 0/3
- [ ] `<StoriesBar />` arriba del feed con avatares circulares (rings de color si tiene story)
- [ ] Click → modal `<StoryViewer />` full screen con foto/video + progress bars + tap para next
- [ ] Auto-advance 5s por story

### 4.5.2 Crear story — 0/2
- [ ] Boton "+" en `<StoriesBar />` para crear nuevo story (foto/video corto upload)
- [ ] Story expira 24h automaticamente (backend cleanup)

---

## Fase 4.6 — Hashtags (~1h) — 0/4

### 4.6.1 Parser — 0/2
- [ ] Al crear post, detectar `#palabra` y guardarlas como hashtags asociados al post
- [ ] Render: hashtag clickeable accent color en posts

### 4.6.2 Filtro por hashtag — 0/2
- [ ] Click hashtag en post → filtra feed por ese hashtag
- [ ] Trending hashtags chips arriba del feed (top 5)

---

## Fase 4.7 — Photo Contest banner (~30min) — 0/2

### 4.7.1 Banner — 0/2
- [ ] Si hay concurso activo → banner sticky arriba del feed con CTA "Ver concurso" → abre W.16
- [ ] Si user es elegible: badge "Sube tu foto al concurso"

---

## Fase 4.8 — Memorias (~30min) — 0/2

### 4.8.1 Tab post-evento — 0/2
- [ ] Si `event.status === 'ended'` → tab "Memorias" con archivo de stories de todo el evento
- [ ] Grid masonry con thumbnails + click → viewer fullscreen

---

## Fase 5 — Filtros (~30min) — 0/2

- [ ] Tabs: Recientes / Mas likes / Mis posts / Por hashtag
- [ ] URL state para shareable

---

## Fase 6 — Tests (~30min) — 0/3

### 6.1 Vitest — 0/1
- [ ] Optimistic like + dedup

### 6.2 Playwright — 0/2
- [ ] Happy path: crear post + like + comentar
- [ ] Edge case: post fallido → retry

---

## Edge cases

- [ ] Post sin imagen → render solo texto
- [ ] Post solo imagen sin texto → render solo imagen
- [ ] Imagen pesada (>5MB) → error pre-upload
- [ ] User banneado → input creator deshabilitado + toast
- [ ] Post baneado por moderador (RT event) → fade out + remove de lista
- [ ] Scroll infinito sin mas posts → "No hay mas publicaciones"
- [ ] Comentario optimista que falla → mostrar error + retry button
- [ ] Like double-click rapido → debounce, solo 1 mutation
- [ ] Photo broken url → placeholder
- [ ] Story expirado mientras user lo veia → mensaje "Este story expiro"
- [ ] User sin stories visibles → no mostrar `<StoriesBar />`
- [ ] Hashtag con caracteres especiales (#nochegrand€) → solo letras+numeros+_ permitidos
- [ ] Hashtag muy largo (>50 chars) → truncar
- [ ] Photo contest sin fotos aun → banner con CTA "Sube la primera"
- [ ] Memorias en evento que apenas termino → procesando, "Memorias disponibles en breve"

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports
- [ ] Commit DaVinci + memoria + PENDIENTES.md
