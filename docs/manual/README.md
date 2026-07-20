# Manual del organizador — EventOS (inventario y reglas)

> Knowledge base (decisión Kamilo 2026-07-19, PENDIENTES.md §2.5).
> **La fuente única del contenido vive en el sitio Starlight:
> `manual/src/content/docs/`** (este repo, carpeta `manual/` — `pnpm dev`
> para verlo en HTML). Este archivo es solo el inventario/tracker y las
> reglas de escritura. Al final, cada módulo del admin gana un link
> contextual "¿Cómo funciona?" hacia su página.

## Reglas de escritura

1. **Esqueleto fijo** — toda página de módulo usa las mismas 5 secciones:
   - **Qué es** (2-3 líneas, lenguaje del organizador, cero jerga técnica)
   - **Cómo se configura (admin)** — pasos reales sobre el admin actual
   - **Qué ve el asistente** — app móvil y webapp, por separado si difieren
   - **Lo que se puede y lo que NO** — límites honestos, sin promesas falsas
   - **Gotchas y preguntas frecuentes**
2. **Regla de procedencia**: toda afirmación sale del código o de una
   decisión documentada. La fuente va en comentario HTML junto a la
   afirmación (`<!-- fuente: archivo:línea -->`) — no se renderiza en el
   sitio pero permite auditar y actualizar.
3. Español de Colombia, tuteo. Cero emojis.
   **Feedback Kamilo 2026-07-19 sobre el piloto: el lenguaje debe ser mas
   natural y humanizado, sin tecnicismos — que lo entienda cualquiera, pero
   sin ser infantil: como lo escribiria un hombre de 30 años. Re-escribir el
   piloto de Encuestas con este tono antes de producir en masa.**
4. Las capturas del admin real se agregan en un pase final (v1 puede
   salir sin capturas; el texto no depende de ellas).

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
