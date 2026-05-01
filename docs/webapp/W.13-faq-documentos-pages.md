# W.13 — FAQ + Documentos + Pages

> Tres pantallas de contenido configurable por organizador: FAQ del evento, Documentos descargables (PDFs, guias), Pages dinamicas custom (paginas creadas por organizador en Filament).
>
> **Estimacion:** ~3h.
> **Dependencias:** W.0, W.1.
> **Estado:** Pendiente.

---

## Lectura obligatoria

- `PLAN.md`, `DESIGN-SYSTEM.md`, `RESPONSIVE-SPEC.md`
- App movil: `app/(app)/faq.tsx`, `app/(app)/documentos.tsx`, `app/(app)/pages/[id].tsx`
- Hooks app: `useFaqs`, `useDocuments`, `usePages`

---

## Alcance

1. **FAQ**: lista de preguntas/respuestas con accordion expand
2. **Documentos**: lista de archivos descargables (PDF, DOCX, etc.) con preview opcional
3. **Pages dinamicas**: paginas custom creadas por organizador (HTML safe-rendered, configurables Filament)

Las 3 pantallas son contenido controlado por organizador via Filament. Webapp solo consume y renderiza.

---

## Refs visuales

- App movil FAQ: accordion con search
- Documentos: lista cards con icono tipo archivo + size + boton download
- Pages: similar a articulo blog (titulo + cover + body)

---

## Endpoints (verificar)

- `GET /api/v1/event/{id}/faqs` — lista FAQ
- `GET /api/v1/event/{id}/documents` — lista documentos
- `GET /api/v1/event/{id}/pages` — lista pages disponibles
- `GET /api/v1/pages/{id}` — pagina individual con content HTML

---

## Fase 0 — Hooks (~30min) — 0/3

- [ ] `useFaqs(eventId)` — TanStack Query
- [ ] `useDocuments(eventId)`
- [ ] `usePages(eventId)` y `usePage(pageId)`

---

## Fase 1 — FAQ (~1h) — 0/4

### 1.1 Componente — 0/2
- [ ] `<FAQList />` con accordion shadcn `<Accordion type="single" collapsible>`
- [ ] Search input con debounce 300ms para filtrar preguntas

### 1.2 UX — 0/2
- [ ] Categorias FAQ con tabs (si organizador las definio)
- [ ] Empty state: "No hay preguntas frecuentes aun"

---

## Fase 2 — Documentos (~45min) — 0/3

### 2.1 Lista — 0/2
- [ ] `<DocumentsList />` con cards (icono tipo archivo + nombre + size + descripcion + boton download)
- [ ] Iconos diferenciados por extension (PDF rojo, DOCX azul, XLSX verde, etc.)

### 2.2 Download — 0/1
- [ ] Click descarga via `<a href download>` o nuevo tab segun tipo

---

## Fase 3 — Pages dinamicas (~45min) — 0/3

### 3.1 Listado — 0/1
- [ ] `<PagesList />` con cards (titulo + cover + extracto)

### 3.2 Detalle — 0/2
- [ ] `<PageDetail />` renderiza HTML del backend con `dangerouslySetInnerHTML` **purificado** con DOMPurify
- [ ] Tipografia + tokens Lumina Noir aplicados al contenido (prose-style)

---

## Fase 4 — Tests (~30min) — 0/3

### 4.1 Vitest — 0/1
- [ ] DOMPurify limpia scripts maliciosos

### 4.2 Playwright — 0/2
- [ ] Happy path: ver FAQ + descargar documento + abrir pagina dinamica
- [ ] Edge case: FAQ sin resultados de search muestra empty correcto

---

## Edge cases

- [ ] FAQ vacia → empty state, no rompe layout
- [ ] Documento >50MB → warning antes de descargar (puede saturar movil)
- [ ] Page con HTML malicioso (intento XSS) → DOMPurify lo sanitiza
- [ ] Page sin cover → usa color accent del evento como background
- [ ] Documento link roto → toast error al click
- [ ] FAQ con HTML en respuesta → renderizar safe (DOMPurify)
- [ ] Search FAQ con tildes/acentos → normalizar antes de comparar

---

## Acceso desde la app

- En pill bar: dropdown "Mas..." con FAQ + Documentos + Pages dinamicas
- En mobile bottom tab bar: dentro de tab "Mas..." overflow

---

## Cierre

- [ ] Tests verde
- [ ] Validado 3 viewports
- [ ] Lighthouse OK
- [ ] Commit DaVinci + memoria + PENDIENTES.md
