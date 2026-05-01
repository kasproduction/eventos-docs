# W.7 — Sponsors

> Brand Wall con grid por tier + Brand Profile detallado + Lead Capture + Trivia. Modulo clave para monetizacion del evento.
>
> **Estimacion:** ~7h.
> **Dependencias:** W.0, W.1.
> **Estado:** Pendiente.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- `W.0-spatial-ui.md`
- App movil: `screens/sponsors/` — Brand Wall + Brand Profile premium
- Memorias: `project_sponsors_uiux_notes.md`, `project_s16_notes.md`

---

## Alcance

1. Brand Wall: grid de sponsors agrupados por tier (Platinum > Gold > Silver > Bronze)
2. Brand Profile: pagina detallada por sponsor (logo, descripcion, productos, contact form, leads)
3. Lead Capture: formulario para que asistente envie datos al sponsor
4. Trivia: mini-juego trivia por sponsor con premios
5. Tracking: analytics de visitas/leads/views

---

## Refs visuales

- App movil sponsors (`features/Screenshot 2026-...`)
- `design/showcase-onboarding-v6.html` `brand-wall` — concepto tiers
- Memoria: `project_sponsors_uiux_notes.md` (Brand Wall, Brand Profile, contact form)

---

## Endpoints (verificar)

- `GET /api/v1/event/{id}/sponsors?tier` — lista
- `GET /api/v1/sponsors/{id}` — detalle
- `POST /api/v1/sponsors/{id}/leads` — capturar lead
- `GET /api/v1/sponsors/{id}/trivia` — preguntas trivia
- `POST /api/v1/sponsors/{id}/trivia/answer` — responder

---

## Fase 0 — Hooks (~30min) — 0/3

- [ ] `useSponsors(eventId)` — agrupa por tier
- [ ] `useSponsor(sponsorId)`
- [ ] `useSponsorTrivia(sponsorId)`

---

## Fase 1 — Brand Wall (~2h) — 0/5

### 1.1 Grid por tier — 0/3
- [ ] `<BrandWall />` con secciones: Platinum (cards grandes glass), Gold (medium), Silver (compact), Bronze (smallest)
- [ ] Cada sponsor card tiene su propio color (no todos dorados)
- [ ] Click → abre `<SponsorProfile />`

### 1.2 Layout responsive — 0/2
- [ ] Mobile: lista vertical agrupada por tier
- [ ] Tablet: 2-3 cols Platinum, 4 cols Gold, 6 cols Silver/Bronze
- [ ] Desktop: 4 cols Platinum, 6 cols Gold, 8 cols Silver/Bronze

---

## Fase 2 — Brand Profile (~2.5h) — 0/6

### 2.1 Layout — 0/2
- [ ] Hero con logo + nombre + tier + categoria
- [ ] Tabs: Acerca de / Productos / Contactar / Trivia

### 2.2 Acerca de — 0/1
- [ ] Bio + redes sociales + website

### 2.3 Productos — 0/1
- [ ] Grid de productos/servicios con imagen + descripcion

### 2.4 Contactar — 0/2
- [ ] Form: nombre + email + telefono + interes + mensaje
- [ ] Submit → POST lead + toast "Tu mensaje fue enviado a {sponsor}"

---

## Fase 3 — Trivia (~1.5h) — 0/4

### 3.1 Mini-juego — 0/3
- [ ] Lista preguntas (usualmente 3-5 por sponsor)
- [ ] Multiple choice + boton "Responder"
- [ ] Score acumulado + premio si aciertas todas

### 3.2 Estado — 0/1
- [ ] Ya jugado: mostrar score final + "Ya completaste la trivia"

---

## Fase 4 — Tracking (~30min) — 0/2

- [ ] Track view sponsor profile (impresion para analytics)
- [ ] Track click en producto / lead submit / trivia start

---

## Fase 5 — Tests (~30min) — 0/3

### 5.1 Vitest — 0/1
- [ ] `useSponsors` agrupa por tier correctamente

### 5.2 Playwright — 0/2
- [ ] Happy path: ver wall + abrir profile + enviar lead + jugar trivia
- [ ] Edge case: trivia ya completa muestra score

---

## Edge cases

- [ ] Sponsor sin productos → tab Productos oculto
- [ ] Sponsor sin trivia → tab Trivia oculto
- [ ] Lead form con email invalido → error inline antes de submit
- [ ] Trivia con todas las preguntas respondidas → mensaje "Trivia completa, gracias por participar"
- [ ] Sponsor activo pero sin tier asignado → fallback "Otros"
- [ ] Logo sponsor broken → placeholder con primera letra del nombre
- [ ] User envia lead duplicado → backend dedupe (mensaje "Ya enviaste un mensaje a este sponsor")

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports
- [ ] Commit DaVinci + memoria + PENDIENTES.md
