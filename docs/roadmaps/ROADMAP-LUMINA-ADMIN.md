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

## F-INT — Interiores por feature — 16/17

> Los clusters ordenaron las PUERTAS; esta fase rediseña lo de ADENTRO:
> forms genericos de Filament → interiores DaVinci por feature (secciones con
> jerarquia, tabs donde aplique, vistas pensadas, cero "form crudo").
> Flujo por feature: leer el interior actual → proponer composicion → QA Kamilo.

- [x] INT.1 Agenda — form de sesion CERRADO 2026-07-18 (commit `440d9ca`, demo
      aprobado artifact cc53725b): Editorial 2+1 · speakers chips+popup instantaneo
      · card Mission Control en rail · pickers fecha+hora · header Eliminar/Guardar/X
      · FUERA seccion MC (debug), Preguntas/Q&A y Resumen (borrado como feature,
      DC reporta). Fix bug real: AttachAction reventaba (inverseRelationship).
- [x] INT.1b Tipos de sesion + Tracks — CERRADO 2026-07-18 (commit `6eab4e3`):
      lenguaje INT.1, Orden→drag&drop (trigger "Ordenar/Listo"), slug al servidor
      (fix race live(onBlur) que exigia doble click en Crear), modelLabel es,
      sin "crear otro". + Dirty-guard de Guardar (trait HasDirtySaveAction,
      verificado en vivo browser) + titulo wrap en listado sesiones.
- [x] INT.2 Identidad nucleo — CERRADO 2026-07-18 (commit `6268279`): Branding
      = tabs por superficie (Lo esencial/Identidad comun/App movil/Onboarding
      app/Webapp) con 3 PREVIEWS EN VIVO (telefono hero + card 16:9 + panes
      accent Noir/Lux), entrada directa sin listado, Onboarding ABSORBIDO como
      tab (8 sub-tabs por pantalla del recorrido, "Orden de steps" criptico →
      "Recorrido" arrastrable legible). + Modal Lumina de salida con cambios
      sin guardar (global, no confirm() nativo). QA vivo browser por Claude.
- [x] INT.2b Hermanos de Identidad — CERRADO 2026-07-18/19 (commits `361a3ac`
      `ecd9539` `b8cbeb8` `0f9da3d` `27f235b`): Encuesta de intereses ABSORBIDA
      en Onboarding (repeater de opciones + toggle survey.enabled,
      OnboardingSurveyOptionResource muere) · Slides del login pase INT
      (rechazo a absorberlo como repeater en Branding) · FAQ / Highlights /
      Recap pase ligero · **Recorrido ELIMINADO** (decision Kamilo): el orden
      natural gobierna y el API SINTETIZA step_order con los steps vivos
      (foto enabled / forms con campos / survey viva; sentinel `none`) — apps
      instaladas saltan lo apagado SIN release del Expo; auditoria cazo el
      hueco (survey apagada dejaba pantalla vacia con minimo 3 imposible),
      +6 tests y flip verificado en vivo contra el Summit.
      NOTA: previews de TODAS las pantallas del onboarding YA CERRADOS
      (`1c348f8` Bienvenida + `7b3cd13` Login/Foto/Intereses/Final) + fix
      hidratacion toggles con defaults reales de la app (`c53fb5c`, incl.
      reparacion de 10 falses accidentales en BD dev que el preview destapo)
- [x] INT.3 Entrada (`0c14668` 2026-07-19): Registro entrada directa +
      Editorial 2+1 + card "La puerta hoy" en vivo · Campos Editorial 2+1 +
      "Donde aparece" (espejo inverso onboarding) + "Mostrar solo si..."
      humanizado + orden fuera del form · Codigos por modales + "Ver usos"
      salta a Asistentes filtrado + banner cross-link. EXTRA mismo commit:
      White Chrome sin violeta (primary Zinc, activos pill de tinta),
      tablas sin neon global, Lux legible (escalera texto + gray 300-600),
      grupos sidebar Jakarta sentence-case
- [x] INT.4 Asistentes (`7407ff6` 2026-07-19): perfil digno Editorial 2+1
      (header persona + datos de registro + rail Estado/Acceso/Acciones +
      historial) · tags chips+popup patron speakers (persiste al instante) ·
      DOMINIOS: admin* solo en Staff y permisos, Cambiar rol solo roles del
      evento, vendedor via invitacion stand (equipo → INT.5), stand desde
      Sponsors · bugs: rol sin auditoria, bulk sin audit log, Crear attendee
      roto, falso "cambios sin guardar" al guardar (guard global + no
      redirect en saves INT)
- [x] INT.5 Sponsors (`0b00346` 2026-07-19): Editorial 2+1 (marca izq /
      stand operativo rail) · trivia con toggle al mando + respuesta
      correcta como Select (muere indice criptico) · repeaters colapsados
      (sin scroll infinito) · Equipo del stand con miembros reales +
      quitar (espejo removeMember: revoca acceso + socket) · MUDANZA:
      "Ajustes de equipos" modal en lista Sponsors (sale de Branding) ·
      tabla drag Ordenar/Listo + tiers sin neon. Tests 49/49
- [x] INT.6 Social (`82a3461` 2026-07-19): ajustes FUSIONADOS en uno
      (EventPhotoSettingsResource muere) con entrada directa + Editorial
      2+1 + card viva "El social hoy" · tabs Publicaciones/Fotos/Ajustes ·
      badges de moderacion pendiente en ROJO SOLIDO #FF3B30 (sidebar suma
      posts+fotos, tabs por separado; invisible en 0). Tests 35/35
- [x] INT.7 Gamificación (`ecc41d7` 2026-07-19): settings entrada directa
      + Editorial 2+1 + card viva · ABSORBE Pasaporte (muere resource +
      toggle duplicado) y Concurso de fotos (mudado desde Social — decision
      Kamilo: competencia+premio = gamification; helper explica el Golden
      Ticket) · Golden Tickets scoping EventContext real (muere workaround
      session pre-F0 + filtro Evento + select en Otorgar) · Premios sin
      rewards internos type=prize + drag orden · Silent Disco toggle
      ELIMINADO (config muerta; el feature vive en silent_disco_group_id
      de sesiones — PENDIENTE humanizar ese campo en pase Agenda).
      Tests Rewards 9/9
- [x] INT.8 Anuncios (`8891dbe` 2026-07-19): composer Editorial 2+1 con
      preview en vivo de la card (dot dorado + CTA) · action_url expuesto
      como "Lleva a" (Agenda/sesion/Speakers/premios/URL — round-trip
      eventos:// que las apps parsean) · rail Publicacion narrada + boton
      Publicar ahora en el edit · push unificada + tabla con Estado
- [x] INT.9 Emails (`c63ba9a` 2026-07-19): catalogo FIJO de 16 correos en
      5 grupos por momento (cableados a triggers reales, no se crean
      tipos) — cards, muere la tabla CRUD · editor por tipo: tabs es/en,
      chips de variables, preview en vivo con datos del evento real,
      enviar prueba, fork por evento + "Volver a la del sistema" ·
      EmailLayout al renderizar (body solo interior) · apagar es apagar
      (fork apagado enmascara al sistema, SendEmailJob no envia
      placeholder) · essential no apagables (magic link, reset,
      verificacion) · grupo Sistema solo super_admin. Tests 12/12.
      **INT.9b QA** (`63918c7` 2026-07-19): QA vivo agente — catalogo,
      tabs es/en, chips, fork/restore (BD verificada), prueba a Mailpit.
      Bug cazado: header con <img src=\"\"> roto sin logo (clients no
      ejecutan JS) → wrap() resuelve header server-side (logo o nombre en
      texto) · muestras localizadas por tab (wire:key preview) · titulo
      Envío (SMTP) · catalogo sin breadcrumb. Tests 13/13. Falta solo
      403 grupo Sistema con admin normal (no probado en vivo).
      **INT.9c** (`5aabb86`): imagenes en el cuerpo (attachFiles →
      public disk email-images/, URL absoluta APP_URL/storage — el
      correo se lee fuera del dominio) + .body img max-width movil.
      Upload real verificado en vivo
- [x] INT.10 En vivo (`9da60ee` 2026-07-19): **decision Kamilo — MC opera,
      el admin administra, el DC reporta**. Verificado: Mission Control ya
      lanza encuestas, modera Q&A y chat (borrar/pin) por socket; DC
      guarda y exporta todo (chat_messages, questions_qa, poll_votes).
      MUEREN del admin: ModerarQnA (pre-Lumina, selector manual F0) y
      LivePollResource (launcher duplicado); cluster EnVivo muere, grupo
      queda Chat + Soporte. Chat = singleton entrada directa, config pura
      (Moderacion automatica + Control de flujo). INT.10b (`a52b839`):
      fuera card MC y nota DC del rail (decision Kamilo — el acceso MC ya
      vive en cada sesion de Agenda). Auditoria de referencias limpia
- [x] INT.11 Encuestas (`c47ba79` 2026-07-19) — SOLO builder post-evento:
      **singleton entrada directa** (contrato real: UNA por evento —
      webapp first(), observer activa; siembra borrador con 3 preguntas
      base). Muere cluster Encuestas y el Repeater anidado → **tabla
      ordenada patron Campos + modal por pregunta formato MC** (chips
      tipo, opciones lista simple, persiste al instante; borrar con
      respuestas advierte). Header Activar/Cerrar + estado narrado.
      Fuera Exportar CSV (DC reporta) y titulo "Editar Live Poll".
      QA vivo con 5 preguntas reales. Tests Poll 29/29
- [x] INT.11b CERRADO 2026-07-19 (commit `27647e2`): Encuestas = LISTA
      (post-evento especial siempre primera + generales scope=event con
      crear por modal titulo → builder). Activar/Cerrar por fila con
      copy por scope. Sin eliminar — solo cerrar (decision Kamilo).
      Resource renombrado EventSurveyResource slug /encuestas. QA vivo
      ciclo completo + tests Poll 29/29
- [x] INT.12 Salones CERRADO 2026-07-19 (commits `fe36c45` + `439f8f7`
      renombre Salones/kioskos): entrada directa, salon contiene sus
      kioskos (tabla+modal), "Conectar tablet" = QR de la URL del
      kiosko (bacon-qr-code SVG + KIOSK_URL en services). Config
      muerta fuera de UI: checkin_enabled salon, type/ip_local kiosko.
      Fix navigator.clipboard inexistente en HTTP no-localhost
      (fallback execCommand). QA end-to-end: QR → kiosko ONLINE →
      heartbeat enciende "En linea" en admin. Tests RoomCheckin 23/23
- [x] INT.13 standalone CERRADO 2026-07-19 (commit `4cd5ff9`):
      Speakers (fotos circulares, crear→edit, eliminar advierte
      sesiones) · Documentos (mime/size AUTO del archivo — mueren los
      campos manuales; tipo/tamano humanos; drag) · Paginas (fuera
      icon/iframe_height/fullscreen sin consumidores; chips; HALLAZGO:
      ninguna superficie lista las paginas — acceso real pendiente:
      F10 Modulos / destino Anuncios / listado apps) · Soporte
      (conversacion, auto-Leida al abrir, Marcar resuelta, es).
      Badges sidebar TODOS rojos patron Social (decision Kamilo).
      + fix MC `be4af06`: [object Object] en ubicacion (session->room
      es relacion EventRoom; ahora room?->name ?? location).
      Tests 22/22
- [ ] INT.14 QA integral de interiores + commit

## F9 — Dashboard "¿Que quieres hacer?" — 0/4

- [ ] F9.1 Accesos por tarea (Cambiar logo o colores / Editar agenda / Enviar
      anuncio / Modulos / Asistentes / Mission Control)
- [ ] F9.2 4 stats del evento activo
- [ ] F9.3 Widgets Lumina (cardIn, tokens DC)
- [ ] F9.4 QA + commit

## F10 — Panel Modulos (demo v6.2) — 0/7

- [ ] F10.1 Migracion keys: patrocinadores→sponsors, fotos→config social,
      chat→config live, nacen desafio/live, formalizar scanner
- [ ] F10.2 Page custom Livewire: lista fija + toggles + drag&drop + audiencias
      humanas (Todos / Ya llegaron / A distancia + grupos)
- [ ] F10.3 Preview vivo (rail webapp + telefono, ubicaciones canon, alturas fijas)
- [ ] F10.4 Saltos "Administrar contenido" → cluster de cada feature
- [ ] F10.5 Matar el CRUD viejo de Modulos
- [ ] F10.6 **DECISION Paginas (quedo EN PAUSA en INT.13, commit
      `4cd43d4`)**: hoy visible+deshabilitada en el admin porque NINGUNA
      app la lista (Expo solo detalle pages/[id], webapp sin modulo,
      Anuncios sin destino). Aqui se decide: entra como modulo colocable
      (grid/tab Expo + rail webapp — el feature es contenido embebido:
      YouTube/iframes/HTML) o se demuele entera (resource + API + tabla
      + pantalla Expo, requiere release)
- [ ] F10.7 QA vivo + commit

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

## TOTAL: 42/67 (F-NAV extra cerrada fuera de numeracion; F10 gano el
item de la decision Paginas)

**Orden:** F3-F8 clusters (HECHOS, falta QA integral+commit... commit hecho en
guardar 2026-07-18) → **F-INT interiores** (lo que sigue — feature por feature
con QA) → F9 dashboard → F10 panel Modulos → F11 wizard → F12 cierre.
Estimacion honesta: F-INT ≈ 2-3 sesiones · F9-F10 ≈ 1-2 · F11 ≈ 1.

**Fuera de alcance:** paridad superficies (despues del admin) · multi-tenancy
(nunca) · vendedor en el admin (jamas) · tonos Lux (pase global app aparte).
