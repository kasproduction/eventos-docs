# ROADMAP — LUMINA ADMIN (Filament usable + bonito)

> **PRIORIDAD 1** (decision Kamilo 2026-07-14). Ventana operativa de este frente.
> Filament NO se reemplaza: se pinta y reorganiza manteniendo el motor.
>
> **ARQUITECTURA APROBADA 2026-07-18 — el Arbol de Features:** la unidad del admin
> es la FEATURE, no el resource. Cada feature = UNA entrada de nav con todo lo suyo
> adentro (tabs via Filament Clusters, v3.3.49 nativo). De ~45 entradas → **18**.
> 27 entradas mueren absorbidas. Decisiones Kamilo: Soporte en En vivo ·
> "Gamificación" como nombre · Highlights dentro de Identidad · Leads FUERA del
> nav (vive en Data Center + webapp vendor) · Sponsors en Contenido.
>
> **Referencias canon:** estetica DC (`public/data-center/assets/app.css`) ·
> panel Modulos = demo v6.2 (artifact f3ebd7a8) · memorias
> `project_lumina_admin_modulos_design`, `feedback_admin_noir_lux_no_accent`,
> `project_filament_perf_caches` (¡clear-cached-components al crear clusters!).

---

## CERRADAS

### F0 — Fundacion: contexto de evento — 6/6 (commit `823b8a8`)
EventContext + middleware + switcher invisible + scoping 44 resources + fixes
badges/attach + eventos de prueba borrados + pase rendimiento dev.

### F1 — Tema Lumina base — 6/6 (commit `5ea8373`)
Paletas sistemicas (gray Lumina, semanticos DC muted) + theme.css (White Chrome,
glow Noir, densidad, Jakarta titulos) + login + toggles ink + QA Kamilo.
Lux mas oscuro DIFERIDO (pase global app, no admin).

### F2 — Redistribucion intermedia — SUPERSEDED
Los 6 grupos + 30 renames es-CO + iconos sin duplicar quedaron aplicados y
sirven de estado intermedio, pero la reorganizacion REAL es el arbol de
features (F3-F8). Sin commit propio — se commitea con cada cluster.

### F-NAV — Sub-nav de clusters como tabs superiores — CERRADA (commit `a8452cc`)
Correccion Kamilo 2026-07-18: la columna de hermanos del cluster quitaba ancho.
`SubNavigationPosition::Top` en 38 resources + 3 pages → tabs sobre el contenido,
contenedor espejo `.mc-tabs` de Mission Control (card + hairline + activo violeta
suave, 13.5px). GOTCHA: Git Bash se come backslashes en replacements perl
(`use FilamentPagesSubNavigationPosition` paso php -l — verificar con ojos).

---

## EL ARBOL (18 entradas · grupos: Contenido / Personas / Comunicacion /
## Experiencia / En vivo / Sistema-colapsado)

> **Principio "el admin administra, el DC reporta" (Kamilo 2026-07-18):** toda
> tabla read-only de metricas que el Data Center ya cubre SALE del nav.
> Aplicado: Evaluaciones (session/speaker_ratings) · Live Moments
> (trivia/spin/jackpot/games_summary) · Historial de emails (emails/push) ·
> Logs de webhooks (webhooks) · Leads (leads_master). Se quedan las que tienen
> ACCION operativa: Canjes (confirmar), Social/Fotos (moderar), Bloqueos (gestionar).

## F3 — Clusters CONTENIDO — 2/3

- [x] F3.1 **Agenda** (piloto de cluster): Sesiones · Tracks · Tipos ·
      Evaluaciones — valida el patron Cluster para todo lo demas
- [x] F3.2 **Sponsors**: Patrocinadores · Equipo de stands (seccion movida desde
      Branding → PENDIENTE, va con F6.1 pulido) + **Leads fuera del nav** (hecho)
- [ ] F3.3 QA vivo + commit (Speakers/Documentos/Paginas quedan solos, ya ok)

## F4 — Clusters PERSONAS — 2/3

- [x] F4.1 **Asistentes**: Asistentes · Bloqueos
- [x] F4.2 **Entrada**: Registro · Campos del formulario · Codigos de acceso
      (ajuste Kamilo 2026-07-18: Onboarding/Slides login/Encuesta intereses son
      LAYOUT → movidos a Identidad)
- [ ] F4.3 QA + commit

## F5 — Clusters COMUNICACION — 2/3

- [x] F5.1 **Anuncios**: Anuncios · Push programadas · Recordatorios
- [x] F5.2 **Emails**: Plantillas · Historial · SMTP
- [ ] F5.3 QA + commit

## F6 — Clusters EXPERIENCIA — 3/4

- [x] F6.1 **Identidad**: Branding · Onboarding de la app · Slides del login ·
      Encuesta de intereses · Highlights del home · FAQ del asistente · Recap —
      TODO el layout/tema de la app en una puerta (7 piezas)
- [x] F6.2 **Social**: Publicaciones · Fotos · Ajustes (mueren las 2 entradas
      de ajustes; evaluar fusionar los 2 settings en un solo form)
- [x] F6.3 **Gamificación**: Ajustes · Premios · Golden Tickets · Canjes ·
      Pasaporte (5 entradas → 1)
- [ ] F6.4 QA + commit

## F7 — Clusters EN VIVO — 4/5

- [x] F7.1 **En vivo**: Chat · Q&A · Juegos (ChatSettings + 2 pages)
- [x] F7.2 **Encuestas**: En vivo · Post-evento
- [x] F7.3 **Salas**: Salones · Totems
- [x] F7.4 Soporte solo + links Event Pulse / Data Center ordenados
- [ ] F7.5 QA + commit

## F8 — SISTEMA — 1/2

- [x] F8.1 **Webhooks**: Webhooks · API Keys · Logs (cluster) + grupo colapsado
      final con Staff y permisos + Limites de uso
- [ ] F8.2 QA + commit

## F-INT — Interiores por feature — 1/15

> Los clusters ordenaron las PUERTAS; esta fase rediseña lo de ADENTRO:
> forms genericos de Filament → interiores DaVinci por feature (secciones con
> jerarquia, tabs donde aplique, vistas pensadas, cero "form crudo").
> Flujo por feature: leer el interior actual → proponer composicion → QA Kamilo.

- [x] INT.1 Agenda — form de sesion CERRADO 2026-07-18 (commit `440d9ca`, demo
      aprobado artifact cc53725b): Editorial 2+1 · speakers chips+popup instantaneo
      · card Mission Control en rail · pickers fecha+hora · header Eliminar/Guardar/X
      · FUERA seccion MC (debug), Preguntas/Q&A y Resumen (borrado como feature,
      DC reporta). Fix bug real: AttachAction reventaba (inverseRelationship).
- [ ] INT.1b Tipos de sesion + Tracks (pase ligero, mismo lenguaje del form sesion)
- [ ] INT.2 Identidad (Branding por superficie: Identidad comun / App / Webapp
      — aqui aterriza tambien F11.1 conceptualmente)
- [ ] INT.3 Entrada (registro + campos + codigos)
- [ ] INT.4 Asistentes (perfil del asistente digno, tabs datos/actividad)
- [ ] INT.5 Sponsors (+ mover seccion Equipo de stands desde Branding)
- [ ] INT.6 Social (fusionar los 2 forms de ajustes en uno)
- [ ] INT.7 Gamificación (5 caras coherentes)
- [ ] INT.8 Anuncios (composer de anuncio decente, no textarea cruda)
- [ ] INT.9 Emails (editor de plantillas digno)
- [ ] INT.10 En vivo (chat/Q&A moderacion comoda)
- [ ] INT.11 Encuestas (builder de preguntas claro)
- [ ] INT.12 Salas (+ tótems con su QR visible)
- [ ] INT.13 Standalone: Speakers · Documentos · Páginas · Soporte
- [ ] INT.14 QA integral de interiores + commit

## F9 — Dashboard "¿Que quieres hacer?" — 0/4

- [ ] F9.1 Accesos por tarea (Cambiar logo o colores / Editar agenda / Enviar
      anuncio / Modulos / Asistentes / Mission Control)
- [ ] F9.2 4 stats del evento activo
- [ ] F9.3 Widgets Lumina (cardIn, tokens DC)
- [ ] F9.4 QA + commit

## F10 — Panel Modulos (demo v6.2) — 0/6

- [ ] F10.1 Migracion keys: patrocinadores→sponsors, fotos→config social,
      chat→config live, nacen desafio/live, formalizar scanner
- [ ] F10.2 Page custom Livewire: lista fija + toggles + drag&drop + audiencias
      humanas (Todos / Ya llegaron / A distancia + grupos)
- [ ] F10.3 Preview vivo (rail webapp + telefono, ubicaciones canon, alturas fijas)
- [ ] F10.4 Saltos "Administrar contenido" → cluster de cada feature
- [ ] F10.5 Matar el CRUD viejo de Modulos
- [ ] F10.6 QA vivo + commit

## F11 — Wizard de creacion v2 — 0/5

- [ ] F11.1 Apariencia POR SUPERFICIE: Identidad → App movil (hero vertical,
      preview) → Webapp (card 16:9 + organizador, preview)
- [ ] F11.2 Paso Registro: siembra campos base + defaults recordatorios —
      el evento nace FUNCIONANDO
- [ ] F11.3 Paso Modulos usa el catalogo canon (componente F10)
- [ ] F11.4 organizer_logo gana UI · banner_url muere (columna huerfana)
- [ ] F11.5 QA crear evento de cero + commit

## F12 — Cierre — 0/3

- [ ] F12.1 QA integral Kamilo (recorrido completo Noir y Lux)
- [ ] F12.2 ROADMAP-FILAMENT-PULIDO marcado superseded
- [ ] F12.3 Guardar DaVinci (commit + push + memoria + NEXT-SESSION)

---

## TOTAL: 27/65 (F-INT gano INT.1b; F-NAV extra cerrada fuera de numeracion)

**Orden:** F3-F8 clusters (HECHOS, falta QA integral+commit... commit hecho en
guardar 2026-07-18) → **F-INT interiores** (lo que sigue — feature por feature
con QA) → F9 dashboard → F10 panel Modulos → F11 wizard → F12 cierre.
Estimacion honesta: F-INT ≈ 2-3 sesiones · F9-F10 ≈ 1-2 · F11 ≈ 1.

**Fuera de alcance:** paridad superficies (despues del admin) · multi-tenancy
(nunca) · vendedor en el admin (jamas) · tonos Lux (pase global app aparte).
