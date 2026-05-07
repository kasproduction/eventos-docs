# W.6 — Social Wall + Stories + Photo Contest

> Feed social: posts con texto/foto + Stories (efimeros 24h) + Photo Contest display + comentarios + likes. Hashtags, "memorias" y "report user" se documentan como pendientes backend (no existen).
>
> **Estimacion:** ~7h (reducida de 10h tras audit — sin hashtags ni memorias ni report).
> **Dependencias:** W.0, W.1, W.16 (Photo Contest detalle).
> **Estado:** Pendiente — backend audit completado 2026-05-07.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- `W.0-spatial-ui.md`
- App movil: `screens/social/` — Feed unificado
- Backend: `routes/api/events.php` (wall + stories + photos), `app/Http/Controllers/Api/V1/WallController.php`, `EventPhotoController.php`, `AttendeeStoryController.php`
- Memoria: `project_social_unified_notes.md`

---

## Drift corregido (2026-05-07)

Version previa documentaba endpoints + sockets que NO existen:

- ~~`POST /wall/{postId}/report`~~ → no existe. Moderacion la hace admin via Filament.
- ~~`GET /event/{id}/memorias`~~ → no existe. Si producto lo pide, archivo de stories ya filtrable por fecha desde `GET /stories` con flag.
- ~~`GET /event/{id}/hashtags?trending=true`~~ → no existe. Si se quiere, parsear hashtags client-side.
- ~~`GET /event/{id}/photo-contest/active`~~ → real `GET /events/{id}/photos/contest` (filtrado de fotos del concurso).
- ~~`POST /stories/{id}/view`~~ → no existe (no hay tracking de vistas).
- ~~Sockets `wall.post.new`, `wall.post.banned`, `wall.comment.new`, `wall.like.updated`, `wall.story.new`, `wall.story.expired`~~ → reales solo `wall:post` y `wall:comment`. NO hay socket de likes (refetch on focus o `data:invalidate`) ni de stories.

---

## Alcance real

1. **Feed scrollable** posts (texto, foto, mixto) — paginated
2. **Like** optimistic con dedup
3. **Comentarios** sub-thread por post
4. **Crear post** texto + foto opcional
5. **Filtros**: Recientes / Mas likes / Mis posts (cliente computa "mas likes" si backend no expone sort)
6. **Stories**: efimeros 24h (foto/video corto) — bar arriba del feed estilo IG
7. **Photo Contest banner**: si hay fotos en `/photos/contest` y feature flag/dato lo indica, banner CTA → W.16
8. **Hashtags client-side**: parsear `#palabra` al renderizar (NO filtro server-side)

NO entra:
- Reportar post (no hay endpoint user-facing)
- Memorias / archivo dedicado (usar lista de stories filtrada por fecha si producto lo necesita)
- Trending hashtags global (no hay endpoint — calcular client-side si producto lo pide)
- Tracking de vistas de stories

---

## Endpoints reales (verificados 2026-05-07)

```
// Wall posts
GET    /api/v1/events/{eventId}/wall?page=1                         (publico)
POST   /api/v1/events/{eventId}/wall                                (sanctum)
  body: {content: string, photo_url?: string}
POST   /api/v1/events/{eventId}/wall/{postId}/like                  (sanctum)
DELETE /api/v1/events/{eventId}/wall/{postId}/like                  (sanctum)
GET    /api/v1/events/{eventId}/wall/{postId}/comments              (publico)
POST   /api/v1/events/{eventId}/wall/{postId}/comments              (sanctum)
  body: {content: string}

// Stories
GET    /api/v1/events/{eventId}/stories                              (publico)
POST   /api/v1/events/{eventId}/stories                              (sanctum)
DELETE /api/v1/events/{eventId}/stories/{storyId}                    (sanctum, propio)

// Photos / Photo Contest (compartido con W.16)
GET    /api/v1/events/{eventId}/photos                               (publico)
GET    /api/v1/events/{eventId}/photos/mine                          (sanctum)
GET    /api/v1/events/{eventId}/photos/contest                       (publico) — fotos del concurso
POST   /api/v1/events/{eventId}/photos                               (sanctum)
POST   /api/v1/events/{eventId}/photos/{photoId}/like                (sanctum)
DELETE /api/v1/events/{eventId}/photos/{photoId}/like                (sanctum)
```

---

## Eventos socket reales (W.11)

| Evento | Payload | Uso |
|---|---|---|
| `wall:post` | `WallPostPayload {id, body, photo_url, likes_count, comments_count, author, author_photo, created_at}` | Nuevo post → prepend en feed con dedup |
| `wall:comment` | `WallCommentPayload {id, post_id, body, author, author_photo, created_at}` | Nuevo comentario → invalidate `comments(post_id)` |

NO hay sockets para likes (refetch on focus o invalidacion generica) ni stories (refetch al abrir story bar).

---

## Refs visuales

- App movil social (`features/Screenshot 2026-...`)
- `design/showcase-onboarding-v6.html` `sw-card`
- 30 bugs fixed historicos en social (memoria `project_session_20260409.md`)

---

## Fase 0 — Hooks (~30min) — 0/3

- [ ] `useSocialFeed(eventId, filters)` — paginated query
- [ ] `usePostComments(postId)` — comentarios on demand al expandir
- [ ] `useCreatePost()` mutation con optimistic

---

## Fase 1 — Feed (~2h) — 0/4

### 1.1 Lista — 0/2
- [ ] `<SocialFeed />` con paginacion (page-based, no cursor — backend usa `?page=`)
- [ ] `<PostCard />` con header (avatar + nombre + tiempo) + body + image + acciones

### 1.2 Skeleton + empty — 0/2
- [ ] Skeleton mientras carga
- [ ] Empty state: "Aun no hay publicaciones. Se el primero!"

---

## Fase 2 — Acciones (~2h) — 0/5

### 2.1 Like — 0/3
- [ ] Heart optimistic con `setQueryData` para incrementar count + flag `is_liked`
- [ ] POST → en exito, ack. En fallo, revert + toast
- [ ] Animacion heart Framer Motion scale + color

### 2.2 Comentarios — 0/2
- [ ] Click "X comentarios" → expande sub-thread inline (lazy load via `usePostComments`)
- [ ] Input crear comentario al final del thread con optimistic + listener `wall:comment` para mensajes de otros

---

## Fase 3 — Crear post (~1.5h) — 0/4

### 3.1 Composer — 0/3
- [ ] `<PostComposer />` modal con textarea (max 500 chars — verificar limite real backend)
- [ ] Imagen upload via `POST /admin/uploads` o storage del backend (verificar que el endpoint user-facing exista; si no, omitir foto-upload o documentar como pendiente)
- [ ] Preview imagen antes de enviar

### 3.2 Submit — 0/1
- [ ] Optimistic post aparece en feed con estado "enviando" → "publicado" o "fallido"
- [ ] Listener `wall:post` deduplica (si el post propio llega via socket, no duplicar)

---

## Fase 4 — Stories (~1.5h) — 0/4

### 4.1 Componente — 0/2
- [ ] `<StoriesBar />` arriba del feed con avatares circulares (rings color si user posteo)
- [ ] Click → modal `<StoryViewer />` full screen con progress bars + tap next

### 4.2 Crear story — 0/2
- [ ] Boton "+" → upload foto (POST /events/{id}/stories)
- [ ] Story expira 24h automaticamente (backend cleanup, no se necesita evento socket)

---

## Fase 5 — Photo Contest banner (~30min) — 0/2

- [ ] `usePhotoContest(eventId)` que llama `GET /photos/contest` con cache 5min
- [ ] Si retorna fotos → banner sticky arriba del feed con CTA "Ver concurso" → abre W.16
- [ ] Si user no ha subido foto al concurso → CTA secundario "Sube tu foto"

---

## Fase 6 — Hashtags client-side (~30min) — 0/2

- [ ] Parser regex `/#[\w_-]+/g` al renderizar `body` → wrapper `<a>` accent color
- [ ] Click hashtag → filtra feed cliente-side (`posts.filter(p => p.body.includes(`#${tag}`))`)
- [ ] NO se persiste el hashtag en backend ni se hace request al server. Si producto lo pide visible, escalar a backend.

---

## Fase 7 — Filtros (~30min) — 0/2

- [ ] Tabs: Recientes / Mas likes / Mis posts
- [ ] "Mas likes" se computa client-side ordenando por `likes_count` (puede no reflejar realidad si paginate intermedio)
- [ ] URL state shareable

---

## Fase 8 — Tests (~30min) — 0/3

### 8.1 Vitest — 0/1
- [ ] Optimistic like + dedup contra `wall:post` propio

### 8.2 Playwright — 0/2
- [ ] Happy path: crear post + like + comentar (2 tabs, RT cross-tab)
- [ ] Edge case: post fallido → retry

---

## Edge cases

- [ ] Post sin imagen → render solo texto
- [ ] Post solo imagen sin texto → render solo imagen
- [ ] Imagen pesada (>5MB) → error pre-upload
- [ ] User banneado → input creator deshabilitado + toast (`ban:enforced` socket o 403 al POST)
- [ ] Post baneado por admin → backend lo oculta del index la siguiente vez. NO hay RT para esto, refetch on focus
- [ ] Scroll infinito sin mas posts → "No hay mas publicaciones"
- [ ] Comentario optimistic que falla → mostrar error + retry
- [ ] Like double-click rapido → debounce, solo 1 mutation
- [ ] Photo broken url → placeholder
- [ ] Story expirado mientras user lo veia → backend deja de retornarlo, viewer cierra suave
- [ ] User sin stories visibles → no mostrar `<StoriesBar />`
- [ ] Hashtag client-side con caracteres especiales → regex limita a `\w_-`
- [ ] Photo contest sin fotos aun → no mostrar banner

---

## Pendiente backend (nice to have)

- **Reportar post** (`POST /wall/{postId}/report`) si producto lo pide. Hoy moderacion solo Filament.
- **Memorias** dedicadas (archivo de stories pasados consultable post-evento).
- **Trending hashtags** server-side con conteo real.
- **Tracking de vistas** de stories.
- **Sort server-side** en `/wall` (`?sort=likes_count`) para que "Mas likes" no dependa de paginacion cliente.

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports
- [ ] Lighthouse OK
- [ ] Commit DaVinci + memoria + PENDIENTES.md
