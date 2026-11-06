# Manual del organizador — EventOS (inventario y reglas)

> Knowledge base (decisión Kamilo 2026-07-19, PENDIENTES.md §2.5).
> **Desde M2.8.4 (Kamilo 2026-11-06) el manual es la Ayuda DENTRO del panel de EventOS**, con el modelo del manual de Masterlub:
> por tareas, paso a paso, con capturas reales del panel y marcas numeradas (forma A del lab `design/features/admin/lab-ayuda.html`).
> El sitio Starlight (`manual/`) se retiró en M2.8.4b; queda en git.
> - Textos: `eventos-web/src/lib/admin/ayuda/capitulos/` (un archivo por capítulo; el índice en `capitulos.ts`).
> - Capturas y marcas: las saca `pnpm ayuda:capturar` (`eventos-web/e2e-ayuda/`, contra el C# de verdad) y quedan en
>   `public/admin/ayuda/{noir,lux}/` y `src/lib/admin/ayuda/capturas.json`.
> - `pnpm ayuda:revisar` (también en el runner del MSI) falla si una pantalla cambió y el capítulo no se tocó.
> Este archivo es el inventario y las reglas de escritura. Al final (M2.8.4d), cada pantalla del panel gana un "¿Cómo funciona?"
> que abre la Ayuda en su paso.

## Reglas de escritura

1. **Esqueleto (hasta M2.8.4a; desde M2.8.4b cada capítulo es una tarea con pasos, ver arriba)** — toda página de módulo usaba las mismas 5 secciones:
   - **Qué es** (2-3 líneas, lenguaje del organizador, cero jerga técnica)
   - **Cómo se configura (admin)** — pasos reales sobre el admin actual
   - **Qué ve el asistente** — app móvil y webapp, por separado si difieren
   - **Lo que se puede y lo que NO** — límites honestos, sin promesas falsas
   - **Gotchas y preguntas frecuentes**
2. **Regla de procedencia**: toda afirmación sale del código o de una
   decisión documentada. En los capítulos de la Ayuda la fuente va en un
   comentario `// Fuentes:` arriba del archivo (textos de la pantalla en
   `messages/es.json` y la e2e que lo prueba).
3. Español de Colombia, tuteo. Cero emojis.
   **Feedback Kamilo 2026-07-19 sobre el piloto: el lenguaje debe ser mas
   natural y humanizado, sin tecnicismos — que lo entienda cualquiera, pero
   sin ser infantil: como lo escribiria un hombre de 30 años. Re-escribir el
   piloto de Encuestas con este tono antes de producir en masa.**
4. Las capturas son del panel real y las saca el script, nunca a mano. Cada marca del texto tiene su marca en el script, en el
   mismo orden (si no coinciden, el script se detiene).
5. **Reescritura sobre el panel de EventOS (M2.8.4, Kamilo 2026-11-06):** "Cómo se configura" describe el panel de EventOS
   (nunca Filament, nunca "admin nuevo"); las fuentes citan el código de C# (`eventos-api-dotnet`) y de la webapp
   (`eventos-web`), y lo de PHP solo donde PHP sigue siendo el dueño.
6. **Marca de actualización:** una página (o sección) que describe algo que cambia en las fases 3 y 4 lleva arriba
   `// actualizar con: M4d.7` (Mission Control), `M4d.8` (Event Pulse) o `M4e.10` (Data Center). La tarea del roadmap
   que haga ese cambio tiene que buscar su marca y actualizar la página.

## Inventario — 1/27

> Blindado 2026-07-19 con barrido exhaustivo de 4 superficies (admin
> Filament completo, rutas webapp, pantallas Expo, kiosko+MC+DC) por
> agentes. Delta del barrido: +3 páginas (Asistente y Soporte, Event
> Pulse, Data Center) y alcance ampliado en salones (staff app) y
> scanner (cluster vendedor completo).

### Primeros pasos — 0/4
- [ ] `primeros-pasos/conceptos.md` — Evento, asistente, superficies (app / webapp / kiosko / admin), módulos y ubicaciones canon
- [ ] `primeros-pasos/crear-evento.md` — Wizard 4 pasos (carril rápido) + dónde se afina cada cosa después
- [ ] `primeros-pasos/escritorio.md` — El Escritorio: stats, "Requieren acción", tareas de intención
- [ ] `primeros-pasos/panel-modulos.md` — Encender/apagar módulos, audiencias, orden, dónde vive cada uno

### Módulos (catálogo canon 12 keys + núcleo) — 1/13
<!-- fuente: eventos-backend app/Support/ModuleCatalog.php:28-137 -->
- [ ] `modulos/agenda.md` — Sesiones, tipos, tracks, salones asignados, Mission Control por sesión
- [ ] `modulos/speakers.md`
- [ ] `modulos/social.md` — Muro + moderación (fotos absorbido aquí)
- [ ] `modulos/sponsors.md` — Marca, stand, equipo, trivia
- [x] `modulos/encuestas.md` — **PILOTO** (en `manual/src/content/docs/`)
- [ ] `modulos/documentos.md`
- [ ] `modulos/networking.md` — Directorio, solicitudes, perfil de otro asistente
- [ ] `modulos/mi-qr.md` — Solo app móvil (credencial que escanean staff y vendedores)
- [ ] `modulos/desafio.md` — Gamificación: acciones y puntos, pasaporte, ranking, canjes, golden tickets
- [ ] `modulos/anuncios.md` — Composer, "Lleva a", push programadas, recordatorios, campana
- [ ] `modulos/en-vivo.md` — Streaming, chat + moderación automática, Q&A, panel custom (chat absorbido aquí; los juegos se operan desde MC)
- [ ] `modulos/scanner.md` — Vendedor completo: Mi Stand, equipo e invitaciones QR, scanner de leads, contactos, estadísticas
- [ ] `modulos/asistente-soporte.md` — FAQ "Asistente" + tickets de soporte (núcleo fijo, no apagable)

### Admin transversal — 0/6
- [ ] `admin/identidad-branding.md` — Branding por superficie, onboarding app, slides login, encuesta de intereses
- [ ] `admin/entrada-registro.md` — Registro, campos, códigos, importar asistentes
- [ ] `admin/asistentes.md` — Perfil, tags, estados, acciones auditadas
- [ ] `admin/emails.md` — Catálogo fijo de 16 correos, fork por evento, imágenes, prueba
- [ ] `admin/salones-kioskos.md` — Check-in presencial completo: salones y ocupación, kiosko lobby + kiosko de salón (QR conectar tablet, cola offline), staff con la app (room check-in, asignar staff), attendance check
- [ ] `admin/staff-permisos.md` — Roles de plataforma (catálogo fijo 8 roles) vs gente del evento

### Operación y sistema — 0/4
- [ ] `operacion/mission-control.md` — Operación en vivo por sesión: tiempos (start/end/delay), modo interactivo, moderar chat/Q&A, lanzar encuestas, trivia, ruleta y jackpot, attendance check / silent disco, proyección a segunda pantalla
- [ ] `operacion/event-pulse.md` — El pulso del evento en tiempo real (grupo En vivo del admin)
- [ ] `operacion/data-center.md` — Reportería: ~45 datasets en 9 tabs, export csv/xlsx/ZIP maestro, metas, reportes programados, embeds por token
- [ ] `operacion/sistema.md` — Eventos (un droplet por cliente, sandbox), webhooks + API keys + logs, límites de uso

> Fuera de alcance v1: manual del asistente final (la app se explica sola),
> docs de desarrollo (viven en `docs/` del hub), y **Páginas** (feature EN
> PAUSA por decisión 2026-07-19 — gana página del manual si F10.6 lo revive).
