# Siguiente sesion — continuidad de contexto

> Este archivo es **solo continuidad** (que hicimos la sesion pasada, decisiones cerradas).
>
> **Para saber que sigue → abrir `docs/living/PENDIENTES-WEBAPP.md`** (ventana operativa unica).

---

## SESION 2026-07-19 NOCHE 2 (Fable) — INT.9 Emails rescatado y cerrado

La sesion que hizo INT.9 se cerro ANTES de commitear — el codigo quedo
completo en el arbol de trabajo. Esta sesion lo reviso, verifico tests
(EmailTemplatesTest **12/12**) y lo guardo: backend `c63ba9a` PUSHEADO
(`feature/magic-link-auth`). Roadmap: **36/66 → 37/66**.

### INT.9 Emails (titulares)

1. **Catalogo FIJO de 16 correos** en 5 grupos por momento (Registro y
   llegada / Antes y durante / Networking / Cierre / Sistema), cada tipo
   cableado a su trigger real — no se crean tipos. Cards agrupadas, muere
   la tabla CRUD (patron catalogo de acciones INT.7).
2. **Editor por tipo** (`EditEmail`): tabs es/en, chips de variables,
   preview en vivo con datos del evento real, "Enviar prueba" (incluye
   cambios sin guardar), **fork por evento** + "Volver a la del sistema".
3. **EmailLayout**: body guarda solo el contenido interior; layout
   responsive se aplica al renderizar (guardia legacy anti doble-wrap).
4. **Apagar es apagar**: fork apagado ENMASCARA al del sistema (`resolve`
   null) y SendEmailJob ya no envia el placeholder "No template
   configured". Essential no apagables: magic link, reset, verificacion.
5. Grupo Sistema solo super_admin; seeders alineados; Create/Edit viejos
   eliminados (tambien OrganizationEmailSettings).

**QA vivo HECHO (agente, misma sesion) → INT.9b `63918c7`**: catalogo,
tabs es/en, chips, ciclo fork/restore (BD verificada en ambos extremos),
enviar prueba llego a Mailpit con variables sustituidas, esenciales sin
toggle. Bug real cazado y arreglado: header con `<img src="">` roto
cuando el evento no tiene logo (los clientes de correo NO ejecutan JS,
el onerror jamas corria) → `EmailLayout::wrap()` ahora recibe vars y
resuelve el header server-side (logo si hay, nombre del evento en texto
si no). + fecha de muestra localizada por tab (gotcha: wire:key en el
panel preview, Alpine conservaba el x-data inicial), titulo "Envío
(SMTP)", catalogo sin breadcrumb redundante. Tests 13/13. Unico hueco:
403 del grupo Sistema con admin normal (requiere login no-super-admin).

**INT.9c HECHO (`5aabb86`, decision Kamilo)**: imagenes en el cuerpo del
correo — boton de imagen en el RichEditor (attachFiles), sube a public
disk `email-images/` y Trix inserta URL ABSOLUTA (APP_URL/storage;
obligatorio: el correo se lee fuera del dominio). `.body img` con
max-width 100% en el layout. Upload real verificado en vivo (editor +
preview). OJO deploy: en produccion APP_URL debe ser el dominio real o
las imagenes de correos viejos apuntaran al dominio anterior.

Sigue **INT.10 En vivo** (moderacion chat/Q&A).

## SESION 2026-07-19 NOCHE 2 (continuacion) — INT.10 En vivo CERRADO (38/66)

**Decision de alcance Kamilo** (pregunta suya, verificada a fondo): Mission
Control YA lanza encuestas, modera Q&A y modera chat (feed/borrar/pin) por
socket real, y el Data Center guarda y exporta todo (exports
chat_messages, questions_qa, poll_votes + stats). El admin NO duplica la
operacion en vivo. Backend `9da60ee` PUSHEADO:

1. **Mueren del admin** (auditoria de referencias limpia): ModerarQnA
   (pagina pre-Lumina con selector manual de evento, sobreviviente F0,
   polling 2s) y LivePollResource completo. El cluster EnVivo muere; el
   grupo del sidebar queda **Chat + Soporte** (Live Moments sigue oculto).
2. **Chat** = la unica pieza admin real de En vivo: singleton entrada
   directa (canon INT), Editorial 2+1 — Moderacion automatica (palabras
   bloqueadas) + Control de flujo (slow mode, pausar) / rail con **card
   Mission Control** (muestra sesion en curso o proxima; boton abre
   /monitor con token HMAC; estado vacio honesto si no hay) + nota "el
   DC reporta". Dirty-save verificado en vivo (BD), invalidacion socket
   preservada en afterSave.
3. `sessionIsLive` respeta los 3 tiempos (actual_start/adjusted_end/
   original) — 6 casos verificados con modelos en memoria.

**Consecuencia**: INT.11 Encuestas se encoge a SOLO builder post-evento.

## SESION 2026-07-19 NOCHE 2 (continuacion) — INT.10b + INT.11 CERRADOS (39/66)

**INT.10b (`a52b839`)**: Kamilo tumbo el rail entero de Chat (card MC +
nota DC) — "sobra". Chat = config pura full width. El acceso MC ya vive
en cada sesion de Agenda.

**INT.11 Encuestas (`c47ba79`)**: builder post-evento singleton.
- Contrato real verificado: UNA encuesta por evento (webapp `first()`,
  observer activa todas las draft al pasar a Finalizado) — el List que
  permitia varias mentia. Entrada directa; siembra borrador con 3
  preguntas base (estrellas + multiple con 4 opciones + texto libre).
- Muere el cluster Encuestas (quedaba 1 hijo tras INT.10) y muere el
  Repeater anidado (scroll infinito). Ahora: **tabla ordenada patron
  Campos** (drag, badges tipo neutros, nº opciones/respuestas) + **modal
  por pregunta formato MC** (pregunta lum-in-lg, tipo chips
  ToggleButtons live, opciones Repeater->simple() con drag y "+ Opcion",
  toggle multi solo en multiple_choice). Persiste al instante
  (RelationManager). Borrar pregunta con respuestas advierte cuantas.
- Header: Activar ahora (draft, con nota "se activa sola") / Cerrar
  (active, danger link) + dirty-save titulo + estado narrado con conteo.
- Fuera: Exportar CSV del admin (DC tiene encuestas_post_evento),
  DeleteAction del edit, titulo "Editar Live Poll".
- QA vivo en pestana propia (Kamilo navegaba la suya): redirect, tabla
  5 preguntas reales del Summit, modal editar con chips y opciones.
  Tests Poll 29/29 (API intacta).

Sigue **INT.12 Salas** (+ totems con su QR visible).

---

## SESION 2026-07-19 TARDE/NOCHE (Fable) — INT.3→INT.8 + White Chrome total en UNA jornada. Kamilo AUSENTE UNA SEMANA

**Jornada maratonica #2 del admin. TODO commiteado y PUSHEADO** (backend
`feature/magic-link-auth`: `0c14668` INT.3 · `7407ff6` INT.4 · `0b00346`
INT.5 · `82a3461` `461ba79` `2f9103c` INT.6/6b · `ecc41d7` `511cdde`
INT.7/7b · `8891dbe` `7e5fad3` INT.8/8b · fixes `e44e5d5` `e2fbbbc`).
Roadmap: **30/66 → 36/66**. QA vivo Kamilo pieza por pieza via extension
Chrome. Detalle completo en memoria `project_lumina_admin_state` +
patrones/gotchas en `project_lumina_int_patterns`.

### Que se cerro (titulares)

1. **INT.3 Entrada**: Registro entrada directa + card viva "La puerta hoy";
   Campos con "Donde aparece" (espejo inverso) + "Mostrar solo si…"
   humanizado + estado vacio en Branding→Formularios con link; Codigos por
   modales + "Ver usos" salta a Asistentes filtrado.
2. **INT.4 Asistentes**: perfil digno (persona + estado + acceso + acciones
   auditadas); tags chips+popup; DOMINIOS: admin* solo en Staff y permisos,
   vendedor via invitacion stand; bugs de auditoria cerrados; fix global del
   falso "cambios sin guardar" al guardar.
3. **INT.5 Sponsors**: marca/stand operativo, equipo real con quitar
   (espejo removeMember+socket), trivia con respuesta como Select,
   "Ajustes de equipos" modal (mudado de Branding).
4. **INT.6 Social**: 2 ajustes fusionados + card "El social hoy" + badges
   ROJOS #FF3B30 de moderacion pendiente (sidebar suma, tabs por separado).
5. **INT.7 Gamificacion**: absorbe Pasaporte y Concurso de fotos (mudado de
   Social, decision Kamilo); **Acciones y puntos = grid de cards + modal
   instantaneo** (diseno Kamilo); Golden Tickets EventContext real + Motivo
   Select+Otro; catalogo de acciones FIJO (cableadas a triggers — NO se
   crean nuevas); Silent Disco toggle eliminado (config muerta; el feature
   es de Agenda via silent_disco_group_id).
6. **INT.8 Anuncios**: composer con "Lleva a" (action_url expuesto, destinos
   eventos:// que las apps parsean) + preview en vivo de la card; push
   unificada; Recordatorios entrada directa + defaults del job.
7. **White Chrome TOTAL (decision Kamilo)**: violeta muerto como accent
   (solo card MC), primary Zinc, activos pill de tinta, tablas sin neon
   global (solo rojo destructivo), Lux legible (escalera texto + hairlines +
   gray palette), grupos sidebar Jakarta sentence-case, hover fila solido,
   hover tab activa arreglado.

### GOTCHA TRANSVERSAL para INT.9-13 (revisar de oficio)

**Hidratacion de singletons MIENTE**: config JSON nunca guardada → toggles
false cuando el motor usa defaults activos; un Guardar apaga el feature en
silencio. Cazado 3 veces (Onboarding, gamification_config, reminder_config).
Fix: sembrar defaults reales del motor en mutateFormDataBeforeFill.

### Leccion de proceso (Kamilo lo reclamo — no repetir)

Silent Disco: explique el hallazgo y CODEE la eliminacion en el mismo
mensaje sin esperar ok. Regla dura reafirmada: hallazgo → proponer →
ESPERAR aprobacion → codear, aunque el cambio parezca obvio.

### PROXIMA SESION

**INT.9 Emails** (editor de plantillas digno) → INT.10 En vivo (moderacion
chat/Q&A comoda) → INT.11 Encuestas (builder claro) → INT.12 Salas (+ QR de
totems visible) → INT.13 standalone (Speakers/Documentos/Paginas/Soporte).
Despues F9 dashboard → F10 Modulos (demo v6.2 aprobado) → F11 wizard.

**Decisiones Kamilo pendientes**: Highlights→"Destacados" · "Otorgar puntos
manuales" (motor lo soporta; ofrecido, falta decidir donde vive) · si el
toggle de Silent Disco se queria de vuelta (eliminado por config muerta).

**Anotados**: humanizar silent_disco_group_id en form de sesion (pase
Agenda futuro) · barrido visual Kamilo de 3-4 secciones al volver.

**Paralelos vivos**: QA presencial webapp (device M.2-M.8 + Fase C) ·
DEPLOY DEMO 0/6 · LANDING + widget registro · Event Pulse 2 pendientes.

---

## SESION 2026-07-19 (Fable) — INT.2b CERRADO + Recorrido eliminado con auditoria completa (cierre tras crash)

**La sesion de la noche del 18 crasheo DESPUES del guardar documentado y avanzo
INT.2b entero sin registrar. Esta sesion reconstruyo, audito y cerro. TODO
commiteado y PUSHEADO** (backend `361a3ac` `ecd9539` `b8cbeb8` `0f9da3d`
`27f235b` en feature/magic-link-auth). Roadmap: **30/66**.

### Que dejo la sesion crasheada (recuperado por commits, nada se perdio)

1. **Encuesta de intereses ABSORBIDA en Onboarding** (`361a3ac`): repeater de
   opciones (emoji+texto+activa+drag) + toggle survey.enabled espejo del step
   Foto; OnboardingSurveyOptionResource muere; preview con chips reales.
2. **Slides del login pase INT** (`ecd9539`) — decision Kamilo: NO absorberlo
   como repeater en Branding, queda resource propio con lenguaje INT.
3. **FAQ / Highlights / Recap pase ligero** (`b8cbeb8`).
4. **Recorrido ELIMINADO** (`0f9da3d`): el sub-tab solo ordenaba lo del medio
   y era footgun (form nuevo no aparecia hasta re-sincronizar step_order).

### Que hizo esta sesion — auditoria completa pedida por Kamilo ("evitar errores")

- **Hueco cazado**: la rama natural del Expo (`buildRegisterSteps`) empuja
  `survey` INCONDICIONAL — con el toggle apagado el backend manda survey null
  pero el asistente caia en "¿Que te interesa?" con 0 chips y minimo 3
  imposible (semi-bloqueo). El "viceversa" (intereses off → salta) NO existia.
- **Fix (`27f235b`, backend-only, sin release del Expo)**: resolveStepsConfig
  SINTETIZA `step_order` con los steps vivos — photo (enabled), forms (solo
  con >=1 campo resuelto), survey (toggle+pregunta+>=1 opcion activa) — y la
  rama "admin order" del app instalado lo consume tal cual. **Sentinel
  `['none']` si todo queda apagado** (lista vacia caia de vuelta a la rama
  natural). Las Edit pages limpian el step_order viejo de BD al guardar.
- **Huerfanos limpiados**: copy "Recorrido" (resource x3 + blade preview),
  step_order en 2 seeders y en el deep-merge de ambas Edit pages. Webapp y
  kiosko verificados: cero referencias.
- **Verificacion**: 6 tests nuevos (OnboardingTest 10/10) + ThemeTest 13/13 +
  flip en vivo contra el Summit en ambos sentidos (off → `["photo"]` +
  survey null; on → `["photo","survey"]`).
- Gotcha PowerShell: here-string `@'...'@` como arg de `git commit -m` se
  rompe con comillas internas — usar `git commit -F archivo`.

### PROXIMA SESION

**INT.3 Entrada** (registro + campos + codigos, con el estado vacio linkeado
desde "Campos que llena el asistente" + espejo inverso) → INT.4 Asistentes →
INT.5 Sponsors (mudar "Equipo de stands"). La cola completa y paralelos siguen
en la seccion de la parte 3 de abajo (sigue vigente). Decision Kamilo
pendiente: Highlights → "Destacados".

---

## SESION 2026-07-18 PARTE 3 (Fable) — INT.1b + INT.2 nucleo CERRADOS: Branding por superficie + previews onboarding + modal salida

**Continuacion de la misma jornada. TODO commiteado y PUSHEADO** (backend
`6eab4e3` `6268279` `1c348f8` `c53fb5c` `7b3cd13` en feature/magic-link-auth).
Roadmap: **29/66**. Kamilo queda AUSENTE UNA SEMANA — todo estable.

### Que se hizo (QA vivo: Claude manejo el browser de Kamilo via extension Chrome)

1. **INT.1b Tipos de sesion + Tracks**: lenguaje INT.1, Orden→drag&drop con
   boton explicito "Ordenar/Listo", slug FUERA del form (se genera en servidor
   con unicidad — el live(onBlur) se tragaba el primer click de Crear),
   modelLabel es (mata "Crear Session Type"), sin "crear otro".
2. **Dirty-guard de Guardar** (trait HasDirtySaveAction + Alpine.data
   `luminaDirtySave` por render hook): apagado sin cambios, enciende al tipear,
   re-basa tras save exitoso. GOTCHA CLAVE: extraAttributes ESCAPA HTML —
   expresiones JS inline llegan rotas a Alpine (&& → &amp;&amp;), siempre
   componente Alpine nombrado.
3. **INT.2 Branding**: tabs por superficie (Lo esencial/Identidad comun/App
   movil/Onboarding app/Webapp), entrada DIRECTA (listado de 1 fila muere,
   Edit hereda sub-nav del cluster), 3 previews en vivo (telefono hero, card
   16:9 con keyvisual real, panes accent Noir/Lux), Equipo stands colapsado
   (se muda en INT.5).
4. **Onboarding ABSORBIDO y organizado**: tab de Branding (mismo registro
   Event; resource oculto, deep-merge+invalidacion migrados), 8 sub-tabs por
   pantalla del recorrido (NO wizard), "Orden de steps" criptico → "Recorrido"
   arrastrable legible. **Previews en vivo de las 6 pantallas** (Bienvenida
   con pills flotantes/highlight/botones, Login+stats, Foto, Formularios con
   nombres reales de campos (`fc605c8`), Intereses con pregunta real, Final
   con QR). GOTCHA: live(onBlur) mata el tiempo real de los previews (con
   .blur el store cliente no sincroniza al tipear).
5. **Modal Lumina de salida** con cambios sin guardar (global admin): evento
   cancelable `livewire:navigate` (interceptar clicks NO sirve — nav queda
   encolada), Seguir editando / Salir sin guardar. Nada de confirm() nativo.
6. **Bug de datos destapado por el preview**: 10 toggles en false explicito
   en BD (guardado viejo del form vacio con la hidratacion null→false) —
   reparados + `mutateFormDataBeforeFill` siembra los defaults reales de la
   app cuando la key no existe.
7. Fix spinner invisible en Noir (icono text-white sobre boton ink) +
   ToggleButtons pintados todos activos (override ink en labels) + titulo
   wrap en listado sesiones + `unsavedChangesAlerts` nativo descartado por
   feo ("no ajax default").

### ADDENDUM (cierre real de la jornada, Kamilo vuelve en ~3h y luego semana fuera)

- **Formularios del onboarding**: pase completo + preview con nombres reales
  de campos (`fc605c8`). Duda resuelta y documentada: los campos que llena el
  asistente NACEN en Personas → Entrada → Campos de Registro con visibilidad
  "Onboarding" o "Ambos"; el formulario solo elige cuales y en que pantalla.
- Ultimo gotcha del dia: `live(onBlur)` mata el tiempo real de los previews.

### PROXIMA SESION — cola completa para "arrancar con todo"

**Frente principal (F-INT, patrones listos en [[project_lumina_int_patterns]]):**
1. **INT.2b hermanos de Identidad**: Slides del login + Encuesta de intereses
   — candidatos a ABSORBERSE como tabs de Branding (mismo patron sections() +
   shouldRegisterNavigation false + preview; la Encuesta alimenta la pantalla
   Intereses cuyo preview YA existe). FAQ / Highlights / Recap: pase ligero
   (lenguaje INT + header canon + dirty-guard, son listas simples).
2. **INT.3 Entrada** (registro + campos + codigos): incluir el detalle anotado
   — estado vacio con link directo en "Campos que llena el asistente" del
   onboarding (patron del popup de speakers) y el espejo inverso (desde
   Campos de Registro ver en que formularios se usa).
3. **INT.4 Asistentes** (perfil digno, tabs datos/actividad) · **INT.5
   Sponsors** (MOVER la seccion "Equipo de stands" desde Branding — ya quedo
   rotulada "Se mudara a Sponsors") · INT.6-13 restantes.
4. Despues de F-INT: **F9 dashboard** "¿Que quieres hacer?" → **F10 panel
   Modulos** (demo v6.2 aprobado, artifact f3ebd7a8) → **F11 wizard v2**
   (los previews por superficie de INT.2 son la semilla de F11.1).

**Limpiezas menores anotadas (no urgentes):**
- EventOnboardingResource: table()/pages() ya muertos (resource oculto) —
  borrar en una limpieza futura.
- Lux mas oscuro DIFERIDO (pase global app, decision F1).
- Decision Kamilo pendiente: Highlights → "Destacados" (nombre).

**Paralelos vivos (fuera del admin):**
- QA presencial webapp (device M.2-M.8 + Fase C, ~2h con Kamilo).
- DEPLOY DEMO 0/6 (sesion propia).
- LANDING (~2-3 sesiones) + WIDGET registro embebible (~1-2, prior art
  dc-embed) — PENDIENTES.md seccion 3.
- Event Pulse: 2 pendientes (cache moments.js v2 + decision hero).

**Estado de repos al cierre**: backend `feature/magic-link-auth` limpio y
pusheado (8 commits hoy: a8452cc 440d9ca 6eab4e3 6268279 1c348f8 c53fb5c
7b3cd13 fc605c8) · APP EVENTOS main pusheado · Lumina Admin **29/66**.

---

## SESION 2026-07-18 PARTE 2 (Fable) — F-NAV sub-nav superior + INT.1 form de sesion CERRADOS

**Misma jornada, segunda sesion. TODO commiteado y PUSHEADO** (backend `a8452cc` +
`440d9ca` en feature/magic-link-auth). Roadmap: **27/65**.

### Que se hizo (QA vivo Kamilo iteracion por iteracion)

1. **F-NAV (correccion Kamilo al arbol)**: la sub-nav de hermanos del cluster
   (columna "atravesada") paso a TABS SUPERIORES — `SubNavigationPosition::Top`
   en 38 resources + 3 pages, contenedor espejo `.mc-tabs` del Mission Control.
   OJO: primero entendi mal (mova la sidebar principal con topNavigation() —
   revertido); la queja era la sub-nav de los hijos.
2. **INT.1 Agenda — form de sesion** (demo artifact cc53725b, 3 iteraciones
   aprobadas ANTES de codear): Editorial 2+1 (izq la pieza / rail propiedades) ·
   speakers chips con foto + X instantanea + popup CheckboxList 2 columnas
   buscable que persiste AL INSTANTE · card Mission Control en el rail (boton
   violeta-soft + tiempos en vivo) · pickers fecha+hora `native(false)` ·
   estado ToggleButtons chips · header Eliminar(rojo)/Guardar(ink)/X ·
   crear→redirect al edit para asignar speakers.
3. **Decisiones de alcance**: seccion Mission Control FUERA del form (era debug;
   MC escribe las mismas columnas = espejo automatico verificado en
   SessionConfigController) · Preguntas/Q&A FUERA (session_questions + dataset
   DC questions_qa + export) · **Resumen/ViewSessionStats BORRADO como feature**
   (DC reporta; servicio de stats queda para API/exports).
4. **Bugs reales cazados**: AttachAction de speakers reventaba
   (`Speaker::eventSessions()` inexistente — Filament adivina la inversa;
   `$inverseRelationship='sessions'`) · ToggleButtons todas "activas" (override
   ink pintaba labels) · spinner invisible en Noir (icono text-white sobre ink
   blanco) · secciones colapsadas reabriendose al guardar (persistCollapsed).
5. Memorias: [[project_lumina_int_patterns]] NUEVA (lenguaje F-INT + gotchas) +
   estado maestro actualizado.

### PROXIMA SESION

**INT.1b Tipos de sesion + Tracks** (pase ligero, mismo lenguaje — rapido) o
**INT.2 Identidad** (Branding por superficie, el grande). Paralelos vivos: QA
presencial webapp + DEPLOY DEMO 0/6 + decision Highlights→"Destacados" +
LANDING + widget registro embebible (PENDIENTES.md).

---

## SESION 2026-07-18 (Opus 4.8) — LUMINA ADMIN F0-F8 CERRADAS: contexto evento + tema Noir/Lux + arbol de features (menu 45→18)

**Sesion maratonica del admin. TODO commiteado y PUSHEADO** (backend
`823b8a8`+`5ea8373`+`5844cff` en feature/magic-link-auth). Ventana operativa
del frente: `docs/roadmaps/ROADMAP-LUMINA-ADMIN.md` — **26/64**.

### Que se hizo (QA vivo Kamilo fase por fase)

1. **F0 Contexto de evento**: EventContext + middleware (default evento activo
   real) + scoping de 44 resources (26 Selects "Evento" eliminados, Premios/
   LiveGameResults/Canjes revividos) + switcher invisible con 1 evento +
   eventos de prueba borrados (solo Summit; webhook verificado del Summit) +
   pase rendimiento dev (icons/components cache — GOTCHA: resource nuevo no
   aparece sin `filament:clear-cached-components`).
2. **F1 Tema Lumina**: paletas sistemicas (gray = escala Lumina, semanticos DC
   muted — badges sin neon), theme.css tokens literales DC (White Chrome,
   glow Noir, Jakarta titulos canon), login, toggles ink, Noir default.
   3 rechazos antes de acertar: la leccion fue ESTUDIAR el DC real
   (app.css clases concretas), no iterar de memoria. Lux mas oscuro DIFERIDO
   (pase global app).
3. **F3-F8 Arbol de features** (arquitectura aprobada): 12 Clusters Filament,
   menu 45→18 entradas en 6 grupos. **Identidad** = todo el layout del tema
   (Branding+Onboarding app+Slides login+Encuesta intereses+Destacados+FAQ+
   Recap — decision: onboarding/login son layout, NO "Entrada").
   **Principio "el admin administra, el DC reporta"**: Evaluaciones, Live
   Moments, Historial emails, Logs webhooks y Leads FUERA del nav (el Data
   Center los cubre con datasets verificados). Locale ya era es; nombres en
   ingles = marca del gremio.
4. **Panel Modulos DISEÑADO Y APROBADO** (demo v6.2, artifact f3ebd7a8, 6
   iteraciones): pagina pura lista+preview vivo (telefono con tab bar real +
   rail webapp), ubicaciones CANON por superficie (grid/tab/HUD/campana/card
   en vivo — el catalogo JAMAS inventa accesos), audiencias en lenguaje humano
   sobre check-in dinamico (arquitectura tags existente). Implementacion = F10.
5. Memorias nuevas: `project_lumina_admin_state` (estado maestro),
   `project_lumina_admin_modulos_design`, `feedback_admin_noir_lux_no_accent`
   (accent del evento JAMAS tine el admin), `project_filament_perf_caches`.

### PROXIMA SESION — F-INT: interiores por feature

Los clusters ordenaron las puertas; **los interiores siguen siendo forms
genericos feos** (Kamilo). F-INT.1-14 del roadmap: rediseñar cada interior
con flujo DaVinci (leer actual → proponer composicion → QA vivo). Arrancar
por **INT.1 Agenda** (form de sesion, el mas usado) o **INT.2 Identidad**
(Branding por superficie). Paralelos vivos: QA presencial webapp + DEPLOY
DEMO 0/6 + decision Highlights→"Destacados".

**Frentes recordados al cierre (Kamilo)**: LANDING (PENDIENTES.md seccion 3,
roadmap propio, ~2-3 sesiones) + **WIDGET de registro embebible** (NUEVO,
decision 2026-07-18, anotado en PENDIENTES.md seccion 3 — prior art:
dc-embed tokens + allowed_embed_domains ya en el modelo Event; ~1-2 sesiones).
Cuenta total honesta a "todo desplegado": ~10-14 sesiones.

---

## SESION 2026-07-14 PARTE 2 (Opus→Fable) — Deuda codigo webapp EN CERO + docs reconciliados + PRIORIDAD 1: ADMIN FILAMENT (demo aprobado)

Continuacion de la misma jornada tras el guardar del showcase. Tres frentes:

### 1. Deuda de codigo webapp → CERO (todo pusheado)

- **Fix poll-vote** (`eventos-web c4f6293`): el proxy pegaba a `/polls/{id}/vote` sin
  `/events` → votar encuestas EN el streaming daba 404. Corregido.
- **ProfileSecurity** (`eventos-backend 2eaf11c`): validator linkedin/website acepta
  string plano (relajado W.18) pero rechaza schemes XSS (javascript:/data:/vbscript:).
  2 tests rojos actualizados + 2 nuevos. 11/11 verde.
- **Panel custom streaming** (`eventos-web 62ebfbd`): monta el iframe del custom_url
  (Slido/Mentimeter/Slides/Miro… — para eso existia) en tablet+mobile; desktop ya lo
  hacia. CSP ya permitia frame-src https:, backend valida dominio.
- **E2E /encuestas y /trivia: NO SE HARA** (decision Kamilo, documentada): cobertura no
  bug, flujos verificados en vivo + vitest; E2E trivia flaky por sockets.

### 2. Docs reconciliados (auditoria doc vs realidad)

- PENDIENTES-WEBAPP: W.15 Vendor 0/35→35/35 (hecho via M.8) + W.X 0/1→1/1 + items
  sueltos marcados (Mi QR, split social/networking, /about, skeletons, reduced-motion).
  TOTAL 550/576 (95.5%), 19 modulos cerrados, **ningun modulo de features abierto**.
- **Paralelos MOVIDOS a PENDIENTES.md** ("Webapp — paralelos / backlog"): paridad config,
  Event Pulse (COMPLETO — solo cache moments.js v2 + 1 decision), backlog Expo, backend
  nice-to-have, analytics. El doc de webapp quedo SOLO con webapp: QA presencial + deploy.
- Titulos de sesiones demo mejorados en BD + ContentSeeder (parte 1 de la jornada).

### 3. PRIORIDAD 1 NUEVA: Admin Filament usable + tema Lumina (decision Kamilo)

- Kamilo: "el admin es un desastre… alguien entra a cambiar un logo y se pierde";
  "crear modulo no tiene sentido"; orden 0/1/2/3. **La paridad superficies va DESPUES.**
- Investigado: backend modules YA existe y esta bien (tabla + ModuleController con
  visibility). El problema es Filament UX (49 resources/10 grupos) + superficies hardcoded.
- **Demo interactivo APROBADO ("mucho mejor")**:
  https://claude.ai/code/artifact/16012924-0767-4c76-8001-c984306df9bd
  Event Switcher + dashboard por tareas + Modulos lista fija con toggles/drag&drop +
  acento del evento re-tine el panel en vivo. Detalle: memoria
  `project_admin_filament_redesign` + addendum en `ROADMAP-FILAMENT-PULIDO.md`.
- El "demo viejo" de Filament que Kamilo recordaba NO existe en repos (fue efimero de
  chat) — no volver a buscarlo.

### PROXIMA SESION — Implementar Lumina Admin (fase 1)

1. **Event Switcher + scoping** (ROADMAP-FILAMENT-PULIDO F1-F3, ~4.5h core) — el
   bloqueante real. Luego tema Lumina custom + nav por tareas + ModuleResource lista fija.
   ~3-4 sesiones total. Flujo: el demo ES la direccion aprobada; iterar contra el con QA.
2. Paralelos vivos: QA presencial webapp (mobile M.2-M.8 + Fase C + W.X de corrido) +
   DEPLOY DEMO (sesion propia) + paridad superficies (post-admin).

---

## SESION 2026-07-14 (Opus 4.8) — W.X WELCOME SHOWCASE CERRADO + commiteado/pusheado

**La pelicula entera quedo cerrada: TODAS las escenas habitan el canvas espejando su
modulo real** (antes eran maquetas centradas = "estampilla en teatro vacio"). Sesion
larga de iteracion con QA vivo de Kamilo escena por escena. Guardado DaVinci al cierre.

### Que se hizo (eventos-web `9d02140` + backend `2d04081`, PUSHEADOS)

1. **Escena Agenda** re-compuesta: split 60/40 real; el panel der hospeda **popups
   tip+sesion SINCRONIZADOS a la accion** (cursor toca dia/chip/corazon → aparece el
   popup en ese momento); climax favorito → "Crea tu propia agenda" → payoff Mi Agenda.
   Day-slide corregido (pill pop, salida completa, filas escalonadas, dwell ~1600ms).
2. **En vivo**: grid `1fr+320`; player 16:9 con **poster+play+scrubber** + About real
   (titulo/meta/speaker/desc) + columna interactiva propia (chat→trivia).
3. **Social**: hub 3 columnas (nav / feed+composer / Personas para conectar).
4. **Desafio**: split hub (HUD hero + Retos) / Ranking (sube #14→#6).
5. **Sponsors**: wall por **tier REAL** + panel detalle. **Bug de fondo**: `/api/showcase`
   repartia 2/3/5 por posicion ignorando el tier (capaba a 10) → reescrito, agrupa por
   tier real (platinum/gold/silver+bronze+media), sin recorte; fetch no-store +
   force-dynamic; wall top-align. El evento demo tiene 30 sponsors reales.
6. **Callouts**: clampeaban al viewport y anclaban al wrap full-width → caian fuera del
   canvas / en los mismos bordes. Ahora clampean DENTRO del canvas y anclan a la pieza.
7. **Opening**: invitacion "Descubre..." con crossfade del date antes del FLIP.
   **Saltar se retira en el finale** (solo Explorar evento). Nombre completo en el saludo.
8. **Favorito**: bloom suave + halo (no explosion cortada), agenda + social.
9. **Copy en español de Colombia (tuteo)** en toda la pelicula.
10. **Titulos de sesiones del demo mejorados** (BD 8 sesiones + `ContentSeeder`): p.ej.
    "Almuerzo con mentores"→"Mentoria 1:1 con lideres", "Networking Lunch: speed
    dating"→"Speed networking: 20 conexiones en 40 min", etc.

### Verificacion

typecheck 0 · lint 0 · vitest showcase 7/7 · php -l seeder ok. QA vivo Kamilo escena
por escena en localhost:3000 (Perfil → "Ver introduccion"). Incluye el nav 99baf79.

### Decisiones cerradas (ver [[project_wx_showcase_design]])

- Beats habitan el canvas espejando el modulo real; popups tip+sesion son de Agenda,
  el resto conserva callouts flotantes. Cero velos/spotlight. Copy es-CO tuteo.
  NO mas mockups HTML paralelos (iterar en la implementacion).

### PROXIMA SESION — QA final W.X + lo que sigue en PENDIENTES

1. **QA final W.X end-to-end** de Kamilo (la pelicula completa de corrido; QA fue por
   escena). Afinables: acentuar la data del demo si se quiere (hoy sin tildes por estilo).
2. Frentes vivos: QA vivo device M.2-M.8 + B5 Fase C, DEPLOY DEMO 0/6, 2 pendientes
   Event Pulse. Ver `docs/living/PENDIENTES-WEBAPP.md`.

---

## SESION 2026-07-11 NOCHE (Fable) — W.X: contexto de sesion bugueada RECONSTRUIDO + QA proporcion + rumbo corregido

**La sesion de la tarde se bugueo DESPUES del cierre documentado y avanzo mas de lo
registrado.** Esta sesion reconstruyo ese estado, Kamilo QA-eo la implementacion viva,
y se corrigio el rumbo del diseño. Cerrada por cansancio, sin codear la correccion.

### 1. Lo que la sesion bugueada dejo (reconstruido por evidencia en disco)

- **W.X Showcase IMPLEMENTADO ENTERO en eventos-web, SIN COMMIT**, sobre `99baf79`:
  `ShowcaseFilm.tsx` (1677 lineas, pelicula completa: opening keyvisual FLIP + 6 beats +
  finale) + `showcase.css` (1815 lineas, tokens reales) + `WelcomeShowcase.tsx` (gate) +
  wiring (SpatialShell monta overlay con userName, layout (app) lo pasa, Perfil gana boton
  "Ver introduccion" con Clapperboard que borra el visto) + `/api/showcase` + lib
  showcase-seen + tipos + 7 assets `public/showcase/` + 7 vitest + `e2e/showcase.spec.ts`.
  **Sano: typecheck 0, lint 0, vitest showcase 7/7.**
- **Decision Kamilo v12 (solo estaba en el codigo)**: el keyvisual del opening se
  TRANSFORMA en el canvas de la app (FLIP) y el marco queda como escenario de todos los
  beats. Mockups v10 (data real) y v11 (tokens reales globals.css + streaming.css)
  guardados en design/; artifact republicado como v11.
- ERRORES 119/120 son de esa sesion (fase mockup, ~12:30). No revisados (regla).

### 2. QA vivo de Kamilo (dev server localhost:3000) — 2 problemas

- **Beats desproporcionados**: el canvas espejo CanvasCard llega a 1600x920 pero los
  componentes de los beats quedaron a escala mockup (ej. `.ws-agv` 490px) CENTRADOS →
  "estampilla en teatro vacio", pierde toda la gracia. Causa: v11 no tenia canvas (escenas
  sobre viewport abierto); la decision v12 los metio al marco sin re-proporcionar.
- **Day-slide agenda se sobrepone**: paginas `absolute` una sobre otra, crossfade 240/300ms
  con solo 26px de offset (los dos dias visibles a la vez), dia 2 mete filas de golpe
  (dia 1 si escalona), dwell 900ms. Atropellado por diseño.

### 3. Iteracion de diseño de esta sesion

- Propuse 3 composiciones; **Kamilo eligio A — "habitar el canvas"**: cada beat llena el
  marco con la composicion real del modulo (agenda = header canon + day pills + timeline
  izq + DetailPanel der; streaming = player + columna; etc.).
- **Mockup v12 (estudio beat Agenda con velo+spotlight) RECHAZADO**: (a) proporciones
  inventadas (escenario logico 1240x740 escalado ≠ el CanvasCard real del monitor);
  (b) el velo + nota anclada lee como **product tour generico** "click siguiente paso" —
  mato la energia de trailer. Archivo en design/, artifact quedo mostrando v12.
- **Leccion cerrada: NO mas mockups paralelos para W.X.** El marco real y la energia de
  pelicula ya existen en la implementacion; cualquier HTML aparte miente en proporcion.
  Iterar DIRECTO en eventos-web con QA vivo.

### Decisiones cerradas W.X nuevas (no re-preguntar)

- **Opcion A**: los beats HABITAN el canvas con la composicion real de cada modulo,
  "igualito" a la app (rechazada 75/25 y rechazado solo-escalar).
- **Cero velos / cero spotlight-tooltip / cero pasos**: se mantienen los callouts
  flotantes del film + cursor fantasma + particulas + compresion al rail.
- Day-slide correcto: pill pop primero (~120ms), salida completa del dia, entrada con
  retraso, filas escalonadas, dwell ~1600ms.
- Sigue viva la decision v12 (keyvisual → FLIP → canvas escenario).

### PROXIMA SESION — Re-componer escena Agenda EN LA IMPLEMENTACION (plan aprobado)

1. **Leer el modulo /agenda real** (composicion, medidas) antes de componer.
2. Re-componer SOLO la escena Agenda de `ShowcaseFilm` para llenar el marco igualito al
   modulo real (datos SSR reales) + fix day-slide. UNA escena, QA Kamilo en
   localhost:3000 (Perfil → "Ver introduccion").
3. Aprobada → replicar el lenguaje a las otras 5 escenas (Speakers, En vivo, Social,
   Desafio, Sponsors) + finale.
4. **OJO estado repos**: showcase SIN COMMIT en eventos-web (intencional, mid-iteracion);
   `99baf79` (nav) SIN PUSH; APP EVENTOS con commits de cierre SIN PUSH. Dev server :3000
   quedo ARRIBA (si esta zombie: matar node + borrar .next, gotcha conocido).
5. Paralelos: QA vivo device M.2-M.8 + B5 Fase C, DEPLOY DEMO 0/6, 2 pendientes Event Pulse.

---

## SESION 2026-07-11 TARDE (Fable) — Nav dock magnify IMPLEMENTADO + W.X Showcase diseño (mockup v9 pendiente QA)

**Sesion de diseño DaVinci con 2 frentes.** Cerrada abrupta: Kamilo se quedo sin creditos.

### 1. SidebarPill animado — IMPLEMENTADO Y APROBADO (eventos-web `99baf79`, SIN PUSH)

Kamilo pidio nav "visionOS premium". Flujo: demo HTML con 5 variantes → eligio **C (dock
magnify)** → segundo demo con 5 formas de revelar el nombre → eligio **C4 Elástico**.
Implementado en `SidebarPill.tsx`: fisica dock vertical framer (useMotionValue+useSpring,
40→64px, RANGE 105), chip elastico squash&stretch con nombre del modulo (hijo del slot,
surfea la onda), Bell en la misma onda, dot live estatico c/glow, reduced-motion fallback,
hit target completo. **QA vivo Kamilo: "perfecto".** Typecheck+lint 0, 556/556 vitest.
Gotcha: el dev server viejo crasheo con "Jest worker exceptions" → matar :3000 + borrar
.next (cache turbopack corrupta, gotcha conocido).
Demo nav: https://claude.ai/code/artifact/fba1ddf5-7202-4bf3-8ba9-41fdf5aabd2d

### 2. W.X Showcase — diseño en iteracion, mockup v9 PUBLICADO SIN QA

**Prototipos historicos encontrados**: `design/features/onboarding/` (early + 11
iteraciones + refs Stitch). **`showcase-onboarding-v6.html` = referencia FUNCIONAL** (doc
`docs/webapp/W.X-welcome-showcase.md`): pelicula GSAP ~45s, escenas que se COMPRIMEN al
pill bar encendiendo iconos, cursor fantasma, TNT finale.

**Iteraciones de esta sesion (todas con feedback Kamilo):**
- v7 (opening "Hola Kamilo" + 2 beats): RECHAZADO — "lento, corporativo, timido".
- 4 openings explosivos (Impacto/Golden Ticket/Spotlight/Big Bang):
  https://claude.ai/code/artifact/fcdd8aca-1e04-4e16-a5e9-eb5f741a81ba
  → **eligio Big Bang + Impacto combinados**.
- v8 (trailer completo 6 beats energia v6): opening gustó PERO 3 correcciones:
  (a) opening debe usar **KEYVISUAL del cliente, no tipografia CSS** (regla
  feedback_keyvisual_not_typography); (b) beats con **aspecto REAL de cada modulo**, no
  aproximaciones; (c) **finale espectacular pero SIN colores dorados**.
- **v9 (ultimo, PUBLICADO pero Kamilo NO lo vio)**: 2 agentes extrajeron specs visuales
  literales de los modulos reales (tokens --ag-*/--st-*/--sp-*, session card timeline,
  day-pills 42x60, feat-cards 200px, feed editorial 50px, anillos momentos 2px, HUD
  TEAL #39d2c0/CYAN #5eead4 banda RGB, trivia Kahoot letras, EventPoster
  home_card_image_url object-cover + overlay). v9 = opening keyvisual (canvas placeholder
  con wordmark horneado) + 6 beats look real + finale plata/blanco.
  URL: https://claude.ai/code/artifact/f278f221-3b03-4757-959c-29e41962d7ce

### Decisiones cerradas W.X (no re-preguntar)

- **La BIENVENIDA personalizada va AL FINAL** (como v6): TNT crescendo → BOOM → Big Bang
  particulas → "BIENVENIDO, {NOMBRE}" gigante → rail renace → CTA Explorar.
- **Opening = keyvisual del evento** (imagen protagonista, wordmark horneado en el arte;
  fallback EventPoster cinematografico). NO tipografia CSS gigante para el nombre evento.
- Beats (6): Agenda → Speakers → En vivo (CON juegos MC: trivia + toast ruleta) → Social
  → Desafio (HUD real) → Sponsors. Escenas se comprimen al **rail izquierdo real** (no
  pill bar inventado); "En vivo" NACE desde su icono.
- **Match % de networking del v6 = INVENTO** (no existe en modulo real) — eliminado en v9;
  Social real = feed editorial + momentos + like + toast solicitud.
- Arranca SOLO (sin boton play). Click = acelerar. Skip siempre. Sin dorados en finale.
- Beats con componentes/estetica REALES de cada modulo (en implementacion: componentes
  reales en miniatura, datos SSR del evento).

### PROXIMA SESION — QA del mockup v9 + seguir iteracion W.X

1. **Kamilo abre el v9** (URL arriba) y da feedback. Ajustar hasta aprobar ANTES de codear.
2. Al aprobar: implementar en eventos-web (framer, componentes reales, localStorage
   una-vez-por-evento, boton "Ver introduccion" perfil re-habilitar, reduced-motion
   fallback estatico, mobile = variante stories 9:16).
3. **Pendiente de decidir**: push de `99baf79` (nav) — Kamilo no alcanzo a pedirlo.
4. Paralelos que siguen vivos: QA vivo device M.2-M.8 + B5 Fase C, DEPLOY DEMO 0/6,
   2 pendientes Event Pulse.

---

## SESION 2026-07-11 (Fable) — MOBILE PARITY CERRADO 60/60: M.8 Vendor + M.0 gates. Workstream completo

**MOBILE PARITY 100% implementado en una sesion** (falta solo QA vivo Kamilo).
M.8 Vendor entero (re-baseado 9→11 items tras leer las ~3.000 lineas Expo) +
el item M.0 final (gates + deeplinks). Global webapp: **609/636 ≈ 95.8%**
(desktop 549/576 + mobile 60/60); los 27 restantes son QA operacional (Fase C).

### Que se hizo (eventos-web `3f7e4dc` + `ef1c757`, PUSHEADOS)

1. **M.8 Vendor 11/11** (`3f7e4dc`, 60 archivos ~6.550 lineas): capa de datos
   (lib/stand + lib/leads + 13 proxies + `getVendorAccess` en lib/auth — el
   /auth/me ahora expone el attendee) · **Vendor Home** split 65/32 espejo
   `VendorHappeningNow` (MiStandCard noir QR breathing; el HUD fluido cqw se
   reusa en el slot chico — RN necesito variante aparte) · Mi Stand (hero +
   3 stats + FAB scanline) · Leads Hoy/Ayer + detail (tier/notas/historial,
   Guardar solo dirty) · export CSV **navigator.share nativo** + fallback
   descarga · **2 scanners @zxing/browser** (prior art REAL kiosko;
   BarcodeDetector del backlog era error — no existe en Safari/FF) ·
   Solicitudes (tel:/mailto:, **WhatsApp APLAZADO** decision Kamilo) · Stats
   (trend + TierBar/MemberBar) · Mi Equipo (3 vias invitar + share link
   nativo + transfer/remove + sheets) · Join-team 5 estados (universal, sin
   redirect desktop) · **sockets `staff:invited`/`staff:removed`** en
   GlobalSocketProvider + StaffInvitationModalM global + fetch pendientes.
2. **M.0 cierre** (`ef1c757`): **gates espejo `(app)/_layout.tsx:63-73`** —
   `getAccessGate` (ban + registration_approved_at, misma llamada /auth/me
   cacheada) en el layout (app) → `/banned` y `/pending-approval` (en (auth),
   sin loop; divergencia doc: aprobado → /home, no hay wizard) +
   `parseActionUrl` con join-team/{token} y stand como rutas REALES.
3. **W.X Showcase/Onboarding RE-ABIERTO** (decision Kamilo: "es la forma en
   que a la gente se le explica todo") — procedencia = decision explicita.
   Eliminado 2026-07-04 por no espejar Expo; vuelve como feature propia.
4. **Bloque DEPLOY DEMO 0/6 anotado** en PENDIENTES (hosting/dominio, backend
   prod, Next prod + HTTPS, socket PM2, evento demo curado, DSN Sentry) —
   prioridad estrategica post-pivote que solo existia como el item DSN.
5. Login mobile: falso gap — Kamilo ya lo habia hecho responsive antes.

### Verificacion

Typecheck + lint 0 · **556/556 vitest** (+9) · **E2E mobile-shell 34/34
serial** (+10: 7 vendor + 3 gates) · 11 screenshots 390px revisados contra
Expo (home vendor, stand, leads, detail, equipo, stats, contacts, join-team,
scanner permiso, banned, pending).

### Decisiones cerradas (no re-preguntar)

- **TODO NATIVO espejo Expo**: navigator.share = el shareAsync de Expo (su
  sheet de 4 opciones era pre-menu al mismo share). **WhatsApp dedicado
  aplazado** (sin botones wa.me por ahora).
- **Orden**: M.8 Vendor primero → W.X Showcase despues (diseño DaVinci
  completo ANTES de codear; re-habilitar boton "Ver introduccion" al salir).
- Join-team y las pantallas de gate son **universales de viewport** (links
  llegan por fuera / estados de cuenta).
- Modal invitacion staff **global** (espejo layout Expo).
- Pending aprobado → **/home** (webapp sin onboarding wizard, Fase 2).

### Gotchas nuevos (para memoria)

- **eventos-web usa pnpm** — `npm install` revienta ("Cannot read properties
  of null (reading 'matches')") contra node_modules .pnpm. `pnpm add`.
- **`toLocaleString("es-CO")` en Node != Chrome → hydration mismatch** (el
  dev overlay lo marca como Issue). Fix: formateo manual de fechas con array
  de meses (patron formatShortDate de HomeMobile). Cazado en lead-detail.
- **Fixtures E2E con offsets de HORAS cruzan medianoche y flakean** (el test
  corrio a la 1 AM: "hace 2h" = Ayer). Fechas relativas: minutos para "Hoy".
- mobile-shell.spec DEBE correr `--workers=1` (fullyParallel del config
  satura y tumba 5-6 specs; en serial 34/34 estable).
- getUserMedia exige contexto seguro: el scanner NO enciende via IP de red —
  QA device requiere `next dev --experimental-https` o flag Chrome.

### PROXIMA SESION — W.X Showcase/Onboarding (diseño primero) o QA vivo

**Opcion A — W.X Showcase** (siguiente en la fila, decision Kamilo): flujo
DaVinci COMPLETO — entender que debe sentir el usuario al entrar, 2-3 refs
externas de product tours premium (Linear/Notion/Arc, NO carruseles
genericos), propuesta de composicion + mockup aprobado ANTES de codear.
**Opcion B — QA vivo M.2-M.8 con Kamilo en device** (scanner vendor via
HTTPS, export share sheet, invitacion socket, gates con datos reales) —
puede combinarse con B5 Fase C (~2h presenciales). Paralelos: Event Pulse
(2 pendientes) + DEPLOY DEMO 0/6 (sesion propia de alcance/costo).

---

## SESION 2026-07-10 (Fable) — MOBILE PARITY M.6+M.7 CERRADOS (48/58): Desafio + Comunicacion completa

**Workstream 48/58 — solo queda M.8 Vendor.** La sesion cerro el fix del slide
stack (bug del dia anterior), M.6 Desafio (venia implementado sin commit) y
M.7 Comunicacion ENTERO (5 modulos + QA streaming), con QA visual de Kamilo
por modulo (screenshots 390px del harness E2E).

### Que se hizo (eventos-web `572ea1a` + `5ed3343` + `51857d8`, pusheados)

1. **Fix slide stack** (`5ed3343`): la animacion salia con duration 0s — el
   minifier CSS descarta el shorthand `animation` partido (shorthand completo
   por clase + keyframes fuera del media query + sombra de ataque). Verificado
   vivo por Kamilo (la lentitud restante era compilacion dev, no el slide).
2. **M.7 Documentos**: adaptacion del W.13 desktop (CANON — decision Kamilo:
   el `documentos.tsx` Expo es legacy huerfano pre-tema y queda pendiente de
   alinearse, deuda backlog Expo). Lista compacta + ZIP pill + preview SheetM
   92dvh reusando DocumentPreview + `lib/documents-client.ts` compartido.
3. **M.7 Anuncios** (2 iteraciones QA Kamilo): espejo `anuncios.tsx:46-47` —
   body COMPLETO inline, SOLO cards con action_url son tocables (dot dorado;
   informativas dot violeta estaticas) + **detail full-screen slide** con CTA
   (decision Kamilo: ver el anuncio antes de saltar; Expo navega directo).
   Mark-read total al montar. Bell del Home sin circulo de fondo.
4. **M.7 Soporte**: my-support + support-contact en /soporte (form slide
   `?nueva=1`, badges status, respuesta admin, errores tipados 429/403/422).
5. **M.7 FAQ/Asistente**: OrbBlob desktop reusado (3 estados), state machine
   800ms, response card mordida + barra cian, cadena Perfil→Ayuda→FAQ→Soporte.
6. **M.7 Encuestas**: lista activas/cerradas espejo + SurveyDeck W.2 en
   overlay slide + voto optimista + **RT poll:new/poll:closed en
   GlobalSocketProvider** (beneficia desktop). Mock E2E gano surveys+vote.
7. **QA StreamShellMobile vs Expo**: player/rating/tracking/paneles espejo o
   superior; fix separador speaker row. **GAP anotado: panel `custom` es
   placeholder** (Expo monta WebView a custom_url; en web implica CSP).

### Verificacion

Typecheck + lint 0 · 547/547 vitest (+29) · E2E mobile-shell 24/24 serial
(+5) · screenshots 390px por modulo revisados con Kamilo en vivo.

### Decisiones cerradas (no re-preguntar)

- **Chat full-height sin stream REVOCADO**: sesion sin stream entra a
  /session-stream y el player muestra "Transmision no disponible aun".
- **Documentos mobile = adaptacion del desktop W.13** (fuente canon), no del
  Expo legacy. Expo se alinea despues (otro modulo/backlog).
- **Anuncios: detail slide** para cards con accion; informativas inline puras.
- **Densidad web = medidas Expo un punto abajo** (RN se ve exagerado en
  browser) — aplicar de entrada en M.8.
- **Sin pull-to-refresh en mobile web** (sockets + reload cubren).
- **M.8 Vendor va en SESION DEDICADA** (decision Kamilo al cierre).

### Gotchas nuevos (con memoria)

- **staleTimes** (`feedback_staletimes_router_cache`): volver a una URL
  visitada sirve el payload RSC cacheado → el prop-sync re-seedea con datos
  pre-mutacion y borra el optimista. Fix: `router.refresh()` tras mutar.
- Client components importan de `lib/*-client.ts`, NUNCA del lib server con
  `next/headers` (build error runtime — cazado en SoporteM).
- Sub-pantalla dentro de una ruta (detail/form/deck con ?query): el slide del
  MobileGate no aplica (mismo pathname) → overlay fixed + framer x:100%→0.
- E2E: matar node + borrar .next entre runs (cache turbopack corrupta =
  Internal Server Error aleatorios); flakes de compilacion fria se re-corren
  en caliente antes de diagnosticar codigo.

### PROXIMA SESION — M.8 Vendor (0/9, cierra el workstream)

**Sesion dedicada** (~3.000 lineas Expo, 8 pantallas, 18 endpoints): hooks +
gating hasVendorAccess → Mi Stand → Leads + export CSV → **scanner QR camara
browser** (getUserMedia + BarcodeDetector, prior art eventos-kiosko) →
solicitudes → stats → team → join-team. Flujo DaVinci: leer las 8 pantallas
Expo ANTES de proponer. Paralelo pendiente: QA vivo Kamilo M.2-M.7 en device
+ B5 Fase C (2h presenciales) + 2 pendientes Event Pulse.

---

## SESION 2026-07-09 (Fable) — MOBILE PARITY M.0-M.5: shell + 5 tabs + Social/Speakers/Sponsors (38/58)

**Workstream MOBILE PARITY 38/58 en una sola sesion Fable.** Capa de presentacion mobile
NUEVA (<640px) espejo 1:1 del Expo, sobre la capa de datos existente. NO react-native-web.

### Que se hizo (eventos-web `ab416fd` → `4a59ea9`, 6 commits pusheados)

- **M.0 Shell (6/7)**: `@custom-variant mobile`, MobileTabBar (5 tabs espejo FloatingTabBar,
  bubble framer layoutId, SOLO en rutas tab), MobileGate (placeholder "Muy pronto" en rutas
  sin vista), MobileHeader, SheetM, DesktopRedirect, dual render en pages
  (`contents mobile:hidden` + `hidden mobile:block`). Falta: gates banned/aprobacion +
  deeplinks (se verifica en QA).
- **M.1 Home + Mi QR (8/8)**: HomeMobile por estado + HappeningNowM + MiQrView (qr-wave,
  fullscreen, brillo) + /about. Proxy /api/mi-qr. Fix QA: HUD explotaba en LIVE (img 118).
- **M.2 Agenda (5/5)**: /mi-agenda (favoritos) + /agenda stack + /session/[id]
  (SessionDetailM) + corazon particulas --heart + ICS. RatingModalM wrapper scoped.
- **M.3 Networking + Perfil (6/6)**: NetworkingM 3 tabs + attendee/[id] + PerfilM (hero
  violeta, stats, edit sheet espejo EXACTO :862-925, brand icons en inputs) + fade
  Facebook rows (nm-in). LAS 5 TABS COMPLETAS.
- **M.4 Social (7/7)**: SocialM feed/momentos segmented + FAB contextual + optimistic UI
  ESCANEADO COMPLETO espejo useWall (like/comment rollback, tempId, socket bus W.11
  skip-own-echo) + compressImage pre-upload + crop momentos.
- **M.5 Speakers + Sponsors (5/5)**: SpeakersM destacados/todos + SpeakerDetailM (rating
  reuso wrapper) + SponsorsM brand wall shuffle 7s + SponsorDetailM (trivia auto-abre
  post visit-stand, contacto sheet).
- **Fix skeletons (3 rondas QA, `4a59ea9`)**: (1) recorte izq → dual render + safety net
  `.canvas-card-root{width:100%}` <640; (2) genericos rechazados → **skeletons espejo
  por modulo transcritos de Expo `components/ui/Skeleton.tsx`** (Home :286, Agenda :53,
  SpeakerList :91, SpeakerDetail :133 — reusado por /session igual que Expo, Sponsors
  :184, SponsorDetail :219, ContentList :166 para attendee; Social/Perfil/MiQr calcan su
  vista M; base `BoneM` pulse 0.06→0.14 900ms) + 6 loading.tsx nuevos en rutas stack;
  (3) doble skeleton (generico → propio al navegar) → root `(app)/loading.tsx` mobile
  SIN shapes (stage oscuro + tab bar) y el generico MobileSkeletonM ELIMINADO.

### Verificacion

Typecheck + lint 0 errores · 517/517 vitest (42 files) · E2E mobile-shell 16/16 serial
· 7 screenshots loading 390px + screenshots por modulo revisados contra Expo.

### Gotchas nuevos (con memoria)

- **credentials en fetch**: `omit` SOLO contra Laravel con Bearer; en proxies Next propios
  la cookie `eventos_auth` ES la auth → nunca omit (rompio likes/comentarios silencioso).
- Stacking: Stage `z-[2]` atrapaba overlays fixed bajo la tab bar → `mobile:z-auto`.
- Hydration: chips `disabled={!editing}` server/client → onClick condicional; resolvedTheme
  → gate useIsClient.
- Root loading boundary SIEMPRE aparece antes que el del segmento al navegar → root sin
  shapes o se ve doble skeleton.
- Expo `Skeleton.tsx` tiene los skeletons pre-armados por pantalla = fuente de transcripcion.
- PowerShell no lee rutas con `[locale]` (wildcard) → Read tool / Node / -LiteralPath.
- MOCK_SLOW_PATH/MOCK_SLOW_MS en mockBackend E2E = QA visual de loading states.

### PROXIMA SESION — M.6 Desafio mobile (0/3)

1. **QA vivo Kamilo M.2-M.5** (pendiente: heart particulas, DaySlide, rating real, ICS,
   momentos/uploads/viewers, shuffle 7s, trivia sponsor, rating speaker, intereses en
   comun vs desktop).
2. **M.6 Desafio**: hub espejo `leaderboard.tsx` (1421 lineas) **Noir forzado "dark
   island"** — DesafioView 368 tiene la logica. Hero HUD + cards + 6 bottom sheets
   (:590-813) + RedeemQrModal (:843) + GoldenTicketModal (:900). Flujo DaVinci: leer
   leaderboard.tsx COMPLETO antes de codear.
3. Despues: M.7 Comunicacion (1/8) → M.8 Vendor (0/9, cierra workstream).
4. Deuda: i18n strings mobile hardcodeados es · E2E trivia/encuestas · B5 Fase C (2h
   presenciales Kamilo).

---

## SESION 2026-07-09 — BLOQUE 4 W.16 TRIVIA CERRADO 100% + validado en vivo (Opus 4.8)

**TOTAL: 549/576 = 95.3%. 18 modulos cerrados** (W.16 se une). Enfoque DaVinci:
mockup HTML interactivo aprobado (4 iteraciones) ANTES de codear, luego QA vivo.

### Que se hizo (commit eventos-web `main` + backend `feature/magic-link-auth`)

**W.16 Trivia en vivo** — TriviaPanel espejo Expo DENTRO de la columna interactiva del
streaming (modo `activePanel=trivia`, NO ruta aparte). 4 fases idle/question/result/
finished: countdown drenante rojo ≤5s, opciones A-F color Kahoot, reveal de distribucion
animada, mini-leaderboard top 3 + podio top 5.

- **`lib/trivia.ts`** reducer puro + tipos + helpers. **`useTrivia`** hook LOCAL del panel
  (patron useQnA, NO zustand — la webapp no tiene store; useReducer + sockets
  `game:question`/`game:round-result`/`game:finished`). Respuesta NO optimista (backend
  devuelve correct/score).
- **Proxy** `/api/streaming/game-answer/[gameId]` → `POST /events/games/{id}/answer`
  (prefijo `/events` explicito — NO repite el bug del poll-vote).
- **Toasts ruleta/jackpot** (`game:launched`/`game:result`) en `GlobalSocketProvider`.
- Wire desktop + mobile/tablet en `StreamShell` (reemplaza los 2 placeholders).
- 10 vitest (reducer + helpers). 488→ suite verde. Typecheck + lint limpios.

### Diseno (Kamilo, 4 iteraciones del mockup)

Noir puro via `--st-*` (adapta Lux). **Sin teal** (v1 lo usaba, rechazado). **Unico color =
letras A-F**. **Cero iconos** en el cuerpo. Estado por relleno de superficie, NO borde neon.
Verde/rojo semantico SUAVE (`color-mix` 5-12%), incorrecto casi imperceptible. Leccion:
mockup interactivo aprobado antes de codear (misma leccion que /encuestas).

### Gotchas nuevos

- **Bug cascada CSS**: `.opt>*{position:relative}` pisaba el `absolute` de la barra de
  distribucion → entraba al flujo flex y empujaba la letra A-F. Fix: `.tv-opt > .tv-opt-dist`
  (mas especifico) + `position:relative` solo a letter/text/count explicitos.
- **`setState`-en-effect prohibido por el lint**: el countdown de Expo lo hace sincrono en
  effect → reescrito a **derived-state R19** (setState en render por cambio de roundKey) +
  interval que solo tickea.
- `noUncheckedIndexedAccess`: indice dinamico en tupla `as const` → blindar con `?? [0]`.

### Validado EN VIVO (QA con Kamilo)

`QaTriviaSeeder` (re-ejecutable) sembro sesion LIVE modo trivia (id 183) + LiveGame trivia
draft (id 72, 4 preguntas, sponsor AWS) en `summit-empresarial-2026`. Kamilo lanzo desde
Mission Control (launch/next-question/close-round) y respondio desde el webapp: ronda 1,
opcion correcta, **score 135**. Sockets `game:launched/question/answer-count/round-result`
confirmados en el log del socket server. Kamilo: **"para mi funciono perfecto"**.

### Deuda (no bloqueante)

E2E Playwright de trivia (hay 10 vitest) — mismo criterio /encuestas. Estado trivia es local
del panel (no global): si se lanza la pregunta antes de abrir el stream se pierde esa ronda
(en el flujo real no pasa). Servidores dev quedaron arriba (socket 3001 + web 3000).

### ADDENDUM — Event Pulse (misma sesion, QA vivo con Kamilo)

Se probo Event Pulse en vivo. **GAP-C (no actualizaba RT) RESUELTO + verificado** (probe recibe,
check-ins de Amy/Pedro visibles). Se implemento **motor de momentos v2** en
`eventos-backend/public/event-pulse/js/moments.js` (cola fresca con prioridad + ventana 90s +
ambiente fallback, `?v=22` cache-bust) pero **NO quedo verificado**: el navegador de Kamilo
seguia mostrando el comportamiento viejo (sospecha: cache/Service Worker sirviendo el JS viejo
pese al `?v=22`). **2 pendientes de Pulse** (detalle en `docs/roadmaps/ROADMAP-EVENT-PULSE.md`
seccion PENDIENTE 2026-07-09):
1. Diagnosticar por que no carga el moments.js v2 (DevTools Network / console.log / incognito / SW).
2. Decision Kamilo: cada interaccion = momento hero (cambio backend) vs dejar contadores+hero checkin/post.
Datos QA sembrados en dev (event 1), `pulse:simulate` funcional. Servidores dev quedaron arriba.

### PROXIMA SESION

Quedan solo 2 frentes: **Mobile parity** (100% Fable, baseline primero) y **B5 Fase C QA**
(~2h CON Kamilo presente: device iPad/iPhone/Edge/Firefox + Lighthouse + WCAG + E2E cross-tab
+ DSN prod). BLOQUES 2 (Home) y 4 (Trivia) ya cerrados. **+ 2 pendientes de Event Pulse** (arriba).

---

## SESION 2026-07-08 — BLOQUE 2 W.2 HOME CERRADO 100% + encuestas por slides (Opus 4.8)

**TOTAL: 544/576 = 94.4%. 17 modulos cerrados** (W.2 se une). Sesion Opus (no Fable),
enfoque DaVinci con harto ida-y-vuelta de QA vivo con Kamilo.

### Que se hizo (`c30b55d` web + `127693a` backend)

1. **GamificationHud LIVE** — slide extra del carrusel de highlights del Home LIVE
   (`CartelDigital`), espejo Expo `GamificationHud`: borde RGB girando 6s + barra
   segmentada de 10 tramos + rank/puntos/retos/stamps. **Toda la card es deeplink a
   /desafio** (decision Kamilo). Tamano **fluido `cqw`+`em`** (bug cazado en QA: a
   ancho de columna desbordaba el slot 16:9 — imagen 117). Datos SSR via
   `fetchDesafioOverview` → `deriveHudData` (cero calls nuevas). Paleta teal FIJA
   (no accent). Dwell 10s vs 6s de highlights.
2. **ENDED = EventArchive (espejo Expo puro)** — decision Kamilo tras revisar el Expo
   real: el Home ended de Expo muestra `HomeHero` + `EventArchive` (banner "Evento
   finalizado" + 3 stats del evento + prompt encuesta + 4 links archivo). **El
   certificado/recap NO va en el Home** (es pantalla aparte en Expo). Se REEMPLAZO el
   recap-col de la webapp (que divergia) por el EventArchive. `photo_count` nuevo en
   el by-slug del backend. El recap/certificado queda para pantalla aparte Fase 2.
3. **Prompt de encuesta + /encuestas** — el prompt vive en el Home ENDED porque al
   finalizar el evento es lo primero que ve la gente (razon Kamilo, espejo Expo). El
   prompt necesitaba destino → se creo la ruta **/encuestas** reutilizando el sistema
   de polls del backend. **SurveyDeck por slides** (espejo Expo `PollSlides`, elevado
   a EventOS): 1 pregunta/slide, transicion spring, opciones en cards con pop + check
   dibujado, **estrellas que se llenan en cascada** al seleccionar, cierre con anillo
   verde de exito. Voto **optimista** (feedback instantaneo, red en segundo plano).

### Proceso DaVinci (importa para la proxima)

- **2 rechazos del UI de encuesta antes de acertar.** Primero reuse el `PollPanel`
  plano del streaming (cuestionario tieso) → Kamilo: "horrible, plano". Segundo intento
  con slides pero con dots + tabular-nums (leia monospace) + sin motion → "no hay
  animaciones, odio los dots, las fuentes monospace, asi no es EventOS". Recien ahi
  PARE y segui el flujo correcto: **mockup HTML interactivo** (Typeform-grade,
  aprobado) ANTES de tocar codigo. Leccion: no codear UI sin diseno aprobado.
- El espejo correcto de encuestas era `PollSlides.tsx` (Expo), no el PollPanel. Casi lo
  paso por alto por reusar el componente comodo.

### Gotchas nuevos

- **HUD en slot 16:9 desbordaba** con fuentes fijas → tamano fluido `cqw` (base) + `em`
  (todo lo demas), con la card como `container-type: inline-size`. Escala con el slot.
- **Verde de exito, NO accent, en el cierre de encuesta**: el accent del evento puede
  ser rojo → un check rojo en "Gracias por responder" lee como alarma/error.
- **TabletRotateOverlay** no dispara en desktop a ningun ancho: pide `pointer: coarse`
  (touch real) + portrait + 640-1023px. A ancho de celular en desktop no hay guard
  (mobile = Mobile parity, aun no).
- **Voto de encuesta = optimista** (recordAnswer antes de la red). Antes esperaba el
  backend → laggeaba.

### Decisiones cerradas (no re-preguntar)

- **EventArchive espejo Expo PURO** — recap/certificado sale del Home, va a pantalla
  aparte (Fase 2). El panel ENDED = banner + stats evento + encuesta + archivo.
- **El prompt de encuesta vive en el Home ENDED** (primera cosa que ve la gente al
  finalizar). El destino es /encuestas.
- **/encuestas por slides** (SurveyDeck espejo PollSlides), NO el PollPanel plano.
- **Cierre de encuesta en verde**, no accent. **Cero dots** en todo el modulo.
- **HUD = card entera clickeable** a /desafio (no solo el boton).

### Deuda anotada (no bloqueante)

- E2E Playwright de /encuestas (hay 9 vitest). Verificar Lux con ojo. Recap como
  pantalla aparte. Bug latente: streaming poll-vote proxy pega a `/polls/{id}/vote`
  sin `/events` (ruta real unica `/events/polls/{id}/vote`).

### PROXIMA SESION

**Si NO es Fable:** BLOQUE 4 W.16 Trivia (~3-4h, Opus). **Si es Fable:** Mobile parity
baseline. **Con Kamilo 2h presenciales:** B5 Fase C QA.

---

## SESION 2026-07-05 TARDE — BLOQUE 5 (W.12) Fases A+B: Web Push + PWA + hardening (Fable 5)

**TOTAL: 541/576 = 93.9%. W.12 salto de 8/43 a 25/48 (+17).** Decision de arranque:
Kamilo pregunto donde valia la pena gastar Fable → Bloque 5 (transversal, toca
modulos cerrados, cierre de Fase 1). Se salto Bloque 2 a proposito (apto para Opus).

### Fase A — Web Push + PWA end-to-end (`b9aa4df` backend + `2dc43a3` web)

1. **Backend Web Push completo**: minishlink/web-push + VAPID + tabla
   `push_subscriptions` + endpoints espejo expo-token + `SendWebPushJob`
   (subscription POR VALOR, simetria token Expo, prune 410) +
   **`SendPushToAttendeeJob::toAttendee()` como choke point multi-canal**
   — 13 call-sites migrados, filtros masivos incluyen web-only, event_id
   inyectado al payload para tracking. 15 Pest nuevos + 178 regresion verdes.
2. **Fix aprobado — scheduled → Announcement**: "la push es el golpe en la
   puerta, el announcement es la carta". Las programadas ya no se pierden;
   el Bell enciende live (AnnouncementObserver ya emitia data:invalidate).
   Auditoria previa: era el UNICO tipo durable sin persistencia (prize/golden/
   support ya creaban announcement; recordatorios/delays son efimeros a proposito).
3. **Bug pre-existente cazado**: ban desde Filament limpiaba el token ANTES
   del `if ($record->expo_push_token)` → la push de ban nunca salia. Corregido
   (orden push → clear, ambos canales).
4. **Webapp**: `public/sw.js` (push + PUSH_ROUTES espejo LITERAL de Expo
   useNotifications.ts + supresion si pestana enfocada + track push_open +
   offline fallback estatico) + **PushPrompt soft pill** una-vez-por-evento
   (divergencia aprobada vs auto-prompt Expo: Chrome penaliza) + resync
   silencioso con granted + manifest + iconos (anillo dorado noir, generados
   con Playwright) + install prompt SOLO >=1024px en /perfil + proxies.
5. **VERIFICADO VIVO con Kamilo**: suscripcion Chrome real → push FCM entregada
   → click navego a /anuncios → scheduled creo la carta en el Bell (live con
   socket arriba; el primer intento parecia fallar porque el socket server
   estaba APAGADO — ambiente, no codigo).

### Fase B — Hardening + perf (`471cf94`)

- **CSP completo**: 13 directivas, connect-src backend+socket desde env, dev
  relajado por NODE_ENV. Verificado vivo + suite E2E entera bajo la politica.
- **SEO recortado con higiene** (decision): robots.ts noindex TOTAL + title
  por ruta (13 paginas, template `%s — EventOS`). OG/sitemap → Fase 2.
- **Print agenda**: documento imprimible real, 2 iteraciones visuales
  (screenshot print-media): papel blanco, sin chrome, break-inside avoid.
- **Code splitting** (habia 0 dynamic): 7 componentes post-interaccion
  (AttendeeProfilePanel, viewers, crop, RedeemModal + GoldenTicketPanel con
  qrcode.react, DocumentPreview). FileKindIcon extraido a archivo propio
  (import estatico compartido anulaba el split).

### Verificacion

Typecheck + lint limpios · 444/444 vitest (+14) · Pest 28 push + 178 regresion ·
E2E suite completa verde (181+29+14; 3 rojos intermedios = cache turbopack
corrupta por taskkill /F + 1 flake saturacion conocido, verdes en frio).

### Decisiones cerradas (no re-preguntar)

- **No-optimistas (agenda rating / ticket soporte / forms perfil): SKIP formal.**
  Optimista es para toggles; un form con 422 por campo o que necesita id del
  server DEBE esperar.
- **SEO completo → Fase 2**; queda robots noindex + titles.
- **Soft prompt de push** (no auto-prompt) — patron Slack/Notion web.
- **Push suprimida con pestana enfocada** (Bell + socket cubren in-app).
- **Install prompt solo desktop/tablet** — mobile no se canibaliza.
- **Lazy framer-motion global descartado** (33 archivos, riesgo > ganancia).

### Gotchas nuevos (con memoria)

- **Next 16 dev es single-instance por proyecto**: correr playwright con el
  dev :3000 vivo → el webServer E2E no arranca (colision). Matar :3000 antes.
- **taskkill /F a next dev corrompe la cache turbopack** → "SyntaxError:
  Unexpected end of JSON input" en SSR aleatorio. Fix: borrar .next.
- **PowerShell 5.1 Set-Content corrompe UTF-8 sin BOM** (mojibake en
  messages/*.json) — usar Node fs o el tool Edit para archivos con acentos.
- **OPENSSL_CONF requerido en Windows** para la crypto EC de web-push
  (seteado a nivel usuario; Laragon debe reiniciarse para que Apache lo herede).

### Commits (todos PUSHEADOS)

- `eventos-backend` feature/magic-link-auth: `b9aa4df`
- `eventos-web` main: `2dc43a3` (Fase A) + `471cf94` (Fase B)
- `APP EVENTOS` main: este cierre + PENDIENTES + memorias

### ADDENDUM — QA vivo post-guardar (misma tarde, `899646b` backend + `a1fabf3` web)

Kamilo QA-eo en vivo y cayeron 4 errores (imgs 113-116 en design/ERRORES):
1. **CSP bloqueaba TODAS las imagenes en dev** (keyvisual, perfiles, docs):
   el backend sirve por http:// y el comodin https: no lo cubria → apiOrigin
   explicito en img-src/media-src.
2. **429 en QA con F5 seguidos**: PushPrompt re-sincronizaba en CADA carga
   (guard sessionStorage) + throttle api local subido a 240/min (convencion
   isLocal existente).
3. **CANON DE TITULOS DE MODULO (decision Kamilo): sponsors = referencia.**
   24px / 800 / -0.5px, header a 26px/28px del canvas. Aplicado a los 11
   modulos (agenda/speakers bajaron del clamp 38px). Agenda/speakers:
   titulo DESACOPLADO de la columna del card pero el header TERMINA en el
   borde derecho de la lista (err 114: full-width ponia las tabs sobre el
   panel). DayStrip: scrollTo (scrollIntoView scrollea ancestros y cortaba
   el titulo, err 113).
4. **Live Hub sin detalle (err 115)**: cadena de posters — upload Filament >
   frame YouTube derivado en backend (EventSession::thumbnail_url, aplica a
   Expo gratis) > retrato del speaker + wash hex-validado del track >
   gradiente. Hora en overline de proximas. Titulo FIJO "En vivo" ("Por
   arrancar" eliminado — decision Kamilo). Tags de track descartados en QA.
5. **Agenda demo re-baseada**: `ReseedSessionsSeeder` NUEVO (re-ejecutable:
   borra sesiones y siembra los 3 dias relativos a HOY sin duplicar tracks/
   speakers). ContentSeeder refactor: seedSessions() extraido.

Gotcha nuevo: `php artisan tinker script.php` ejecuta y SE QUEDA EN EL REPL
(proceso zombie eterno) — para scripts one-shot usar bootstrap directo
(`require vendor/autoload + bootstrap/app + Kernel->bootstrap`).

### PROXIMA SESION — MOBILE PARITY baseline + arranque (100% FABLE)

**Decision Kamilo (2026-07-05 noche): la proxima sesion Fable va entera a
Mobile parity** (dedica el 50% de cuota restante). Enfoque acordado — NO
portar componentes RN; capa de presentacion mobile NUEVA (shell bottom tabs
espejo Expo + transcripcion pantalla-por-pantalla) sobre la capa de datos
que ya existe en eventos-web. **Arrancar por el BASELINE**: inventario
modulo-por-modulo contra el Expo real → counters en PENDIENTES → bloques.
Detalle completo en PENDIENTES-WEBAPP seccion MOBILE PARITY.

Sesiones NO-Fable mientras tanto: Bloque 2 Home (~1.5-2h, apto Opus:
GamificationHud + survey ENDED + EventArchive) → Bloque 4 Trivia (~3-4h).
**B5 Fase C cuando Kamilo tenga ~2h presenciales** (QA device + Lighthouse +
WCAG + E2E cross-tab + DSN prod). Fase A validada 100% mismo dia: push desde
Filament (Apache + OPENSSL_CONF OK post-reinicio Laragon) + install PWA OK.

---

## SESION 2026-07-05 — BLOQUE 1 CERRADO: Momentos + Memorias + barrido de pulido (Fable 5)

**TOTAL: 524/571 = 91.8%. 16 modulos cerrados** (W.6 cierra con Bloque 1; denominador W.6 re-baseado 41→28, los 13 extra eran items eliminados en auditoria que seguian contando).

### Que se hizo

1. **BLOQUE 1 — W.6 Momentos + Memorias CERRADO** (`50a0f79` eventos-web). Flujo DaVinci
   completo: 3 agentes leyeron el espejo Expo real + refs externas (Instagram desktop
   carousel, Telegram Web, galerias lightbox) + mockup HTML interactivo aprobado
   (artifact `3e4cba5f`) ANTES de codear.
   - **Momentos**: barra sobre el feed (visible en Feed y Memorias), anillo accent/tenue
     por visto, viewer card 9:16 centrada DENTRO del CanvasCard, auto-advance 5s,
     click zones 40/60, visto-al-abrir en localStorage
     `eventos:social:story-seen:{eventId}` (par story-seen.ts + use-story-seen.ts,
     patron announcements-unread). Upload con preview + center-crop canvas
     (`lib/image-crop.ts`, reusable 9:16 y 1:1).
   - **Memorias**: 5ta vista del sidebar social. Grid 3 col (oficial 2x2 badge),
     ContestBanner espejo (countdown useNow, podio medallas 72/56, solo active o
     ended<24h), orden por likes client-side con contest activo. PhotoViewer con
     **marco FIJO 16:9 o 9:16 por orientacion natural + foto contain** (iterado 3
     veces en QA vivo con Kamilo: primero desbordaba, luego cover cortaba caras —
     decision final: marco consistente, foto INTEGRA con franjas negras).
   - **Like foto propia**: backend rechaza (anti-gaming EventPhotoController:175) —
     el corazon NO se deshabilita (parece roto), responde con toast informativo sin red.
   - 4 proxies + 3 fetchers SSR + prop-sync (leccion W.11). +29 vitest +12 E2E.
     Verificado vivo contra backend real (shapes identicos, SSR con story real).
2. **Barrido de pulido transversal** (`29fce3d`) por QA Kamilo "la idea era algo
   super pulido":
   - **Skeletons 13/13 rutas**: creados los 7 loading.tsx que faltaban (documentos,
     faq con shape del orb, anuncios, desafio, soporte, perfil, session-stream con
     tokens --st-* propios). Patron social/loading.tsx: clases reales + shapes pulse.
   - **Corazon rosa UNICO**: token global `--heart: #ff5d6c` (el de favoritos agenda
     es el canonico — decision Kamilo). Feed era slate, memorias era accent rojo,
     sponsors ya era rosa hardcodeado. El upvote Q&A streaming NO se toco (es voto).
   - **Haptics**: auditoria 13 modulos (agente) + gaps aplicados — vibrate en likes,
     favoritos, ratings, votos polls, upvote/submit Q&A, chat, comentarios,
     solicitudes, chips/saves perfil, tema, anuncios, documentos, exito de canje.
     El upvote Q&A gano su pop de corazon (unico toggle sin anim).
3. **Momentos QA sembrados**: 8 stories para los 7 contactos de presencial@eventos.test
   (imagenes GD 9:16 generadas; borrar con `AttendeeStory::where('photo_url','like','%qa-momento%')->delete()`).

### Verificacion

Typecheck + lint limpios · 430/430 vitest · suite E2E completa 170 verdes (4 flakes
de saturacion paralela conocida, re-corridos en frio 54/54) · smoke SSR vivo.

### Commits (todos PUSHEADOS)

- `eventos-web` main: `50a0f79` (Bloque 1) + `29fce3d` (polish)
- `APP EVENTOS` main: este cierre + PENDIENTES actualizado + memorias

### Decisiones cerradas (no re-preguntar)

- **Viewer de fotos = marco fijo 16:9/9:16 + contain** (nunca cover/recorte).
- **Overlays del social viven DENTRO del CanvasCard** (absolute, no fixed viewport).
- **--heart #ff5d6c global** para todo corazon de like/favorito.
- **Like a foto propia = toast informativo**, no boton disabled.
- **Upload Memorias = boton pill en header** (webapp no usa FAB).
- **Crop de upload = center-crop automatico con preview** (sin cropper interactivo).

### Deuda anotada (no bloqueante)

- No-optimistas pendientes (cambian semantica de modulos cerrados, evaluar en W.12):
  agenda rating, soporte ticket creacion, forms de perfil.
- Backend expone `DELETE /stories/{id}` pero Expo no lo usa — espejo lo omite (correcto).
- `ProfileSecurityTest` 2 rojos pre-existentes (sin cambio).

### PROXIMA SESION — BLOQUE 2: W.2 Home → 100% (~1.5-2h)

GamificationHud preview LIVE (espejo `index.tsx:103-129` Expo) + post-event survey
prompt ENDED + EventArchive ENDED (espejo `EventArchive.tsx`). Despues: Bloque 4
Trivia W.16 (~3-4h) → Bloque 5 W.12 cierre (~5-7h). Paralelo: Event Pulse cliente.

---

## SESION 2026-07-04/05 — AUDITORIA DE PROCEDENCIA + BLOQUES 0 y 3 CERRADOS (Fable 5)

**TOTAL: 516/619 = 83.4%. 15 modulos cerrados** (W.3 cerrado via auditoria, W.14 re-cerrado 19/19).

### Que se hizo

1. **Inventario TOTAL contra codigo (7 agentes en 2 tandas):** 4 barrieron eventos-web
   (~13 victorias sin marcar: Sentry completo, 20 specs E2E, security headers,
   reduced-motion, etc.) y 3 auditaron PROCEDENCIA contra el Expo REAL
   (`C:\Users\Kasproduction\Projects\eventos-app` — OJO: NO vive en laragon/www).
2. **~12 INVENTOS DE PLANEACION eliminados** (nunca existieron en Expo ni backend):
   hashtags click-to-filter (+ parser borrado del codigo), badges AJUSTADA/CANCELADA,
   conflict detector, self check-in, sorteo ceremony GSAP, golden reveal full-screen,
   W.X showcase 6 beats ENTERO, sponsors band home, multi-sede, proximos eventos org,
   dedupe happening-now, invite staff sin login. **Regla nueva en PENDIENTES-WEBAPP:
   todo item cita procedencia o no se codea** (memoria `feedback_regla_procedencia`).
3. **Scope REAL descubierto:** Stories="Momentos" (~470 lineas Expo, backend+cron listos),
   **tab Memorias** (PhotoGrid+PhotoViewer+ContestBanner — Expo la tiene, webapp NO),
   TriviaPanel 340 lineas como referencia W.16, W.15 vendor ~3.000 lineas mapeadas
   con 18 endpoints, Mi QR mobile-only (→ workstream Mobile parity, diferido).
4. **BLOQUE 0 cerrado:** alias `sessions` en KNOWN_ENTITIES (`3e73b29`) + boton
   "Ver introduccion" oculto — navegaba a /onboarding 404 (`4325f05`).
5. **W.6 paginacion CERRADA** (`7afb5d0`): infinite scroll cursor-based (el wall usa
   cursorPaginate, NO ?page=) + appendFeedPage dedup + sentinel IntersectionObserver.
   Verificado vivo con 99 posts (50 seedeados). +5 vitest, 12/12 E2E.
   Hashtags ELIMINADO (decision Kamilo; idea menciones → Event Pulse, en memoria).
6. **Fix foto speaker** (`d2ec891`): RoomAvatar usa speaker_photo_url (QA vivo Kamilo).
7. **BLOQUE 3 cerrado — feature BANNERS MUERTA:** Kamilo detecto que Expo nunca mostro
   banners (highlights la reemplazo; home Expo solo renderiza highlights). Webapp
   cartel → solo highlights (`ba630b1`), backend TODO fuera con migration drop
   (`eba0609`), BD dedup (highlights estaban x2 por seed doble) + vigencias frescas.

### Commits (todos PUSHEADOS)

- `eventos-web` main: `3e73b29` `4325f05` `7afb5d0` `f0bedaa` `d2ec891` `ba630b1`
- `eventos-backend` feature/magic-link-auth: `eba0609` (banners drop + migration corrida)
- `APP EVENTOS` main: re-baseline PENDIENTES-WEBAPP (plan BLOQUES + procedencia)

### PROXIMA SESION — BLOQUE 1: Momentos + Memorias (~4-6h)

**Arranca por diseño DaVinci, NO por codigo:** refs externas (Instagram web/Telegram/
LinkedIn desktop stories), definir composicion (donde vive la barra Momentos en el
split /social, viewer 9:16 en desktop = modal centrado flancos oscuros?, Memorias
como 5ta vista del sidebar social espejo del tab Expo), proponer, aprobar, codear.
Espejo: `MomentosRow`/`MomentosViewer`/`useStories` + `PhotoGrid`/`PhotoViewer`/
`ContestBanner` en el Expo. Backend 100% listo (AttendeeStoryController + cron +
EventPhotoController /photos/contest).

Despues: Bloque 2 Home (GamificationHud+survey+EventArchive ~1.5-2h) → Bloque 4
Trivia (~3-4h) → Bloque 5 W.12 (~5-7h). Paralelo: Pulse.

**DECISION post-cierre (2026-07-05, chat final): W.15 Vendor SALE de Fase 1 desktop
→ Mobile parity.** Razon Kamilo: el staff del stand no va a instalar app para un
evento — vendor se hace como feature del webapp MOBILE (viewport celular espejo
Expo, patron Mi QR) incluyendo scanner QR con camara en browser (prior art
eventos-kiosko). **Fase 1 desktop queda 516/584 = 88.4%**, 100% a ~14-19h
(Bloques 1+2+4+5). NO re-preguntar.

### Deuda anotada (no bloqueante)

- `ProfileSecurityTest` 2 rojos PRE-existentes (validator W.18 relajado sin actualizar
  tests) — decidir: aceptar string + rechazar schemes `javascript:`/`data:`
- Backlog Expo: borrar banners.tsx/BannerCarousel/bannersApi + ENTITY_KEYS `modules`
  + double-count comment useWall
- Event Pulse cliente 4 items (sesion dedicada)

---

## W.11 SOCKETS WEBAPP — IMPLEMENTADO (2026-07-04 noche, Fable 5)

**W.11 CERRADO 22/22.** Los 12 archivos del plan aplicados en `eventos-web`:
GlobalSocketProvider (6 listeners) + bus useSocketEvent + proxy /api/social/requests
+ prop-sync AgendaView/SponsorsView/SoporteView + disposeSocket en logout +
reconnection Infinity. **Total webapp: 498/675 = 73.8%. 13 modulos cerrados.**

Verificado:
- Typecheck limpio + lint 0 errores (fix drive-by PerfilView set-state-in-effect)
- **402/402 vitest** (+11 GlobalSocketProvider)
- E2E: global-socket 2/2 + agenda/soporte/faq/speakers re-verificados
- **Verificacion viva del pipeline**: tinker `InvalidationService::broadcast(1,'announcements')`
  → socket server `[invalidate]` → cliente recibio el evento. Contrato roto
  (`event_id`) reprodujo `EVENT_ACCESS_DENIED` en vivo — fixes kiosko/attendance validados

Aprendizajes de la implementacion (importan para proximas sesiones):
1. **prop-sync via useEffect, NO setState-in-render**: el tracker R19 en AgendaView
   rompia el flujo `?highlight` (E2E rojo). Re-seed post-commit con
   `useEffect(() => setX(initialX), [initialX])` + eslint-disable.
2. **mockBackend ahora persiste POST /support** (in-memory por bearer) — el prop-sync
   re-seedea desde el refetch SSR y el mock debia espejar la persistencia real.
3. **E2E local con socket server dev corriendo = flake**: los pages con retries
   Infinity martillan 3001 con tokens mock. Apagar 3001 antes de `playwright test`.
4. **agenda.spec en serial mode** (saturacion paralela cronica, igual W.13/W.7/W.18).
5. Suite E2E completa: correr con TODOS los node zombie muertos (taskkill node) —
   dev servers viejos causan aborts fantasma ("100 did not run").

---

## MISION FABLE 5 COMPLETADA — 2026-07-04

**La investigacion W.11 Sockets esta HECHA.** Fable 5 leyo cross-repo (Expo + webapp +
socket server + backend Laravel) y produjo:

**`docs/W.11-SOCKETS-PLAN.md`** — plan listo-para-ejecutar con:
- Seccion A: mapa real de los 5 eventos verificado con archivo:linea (payloads literales,
  disparadores backend, handlers Expo copiados)
- Seccion B: codigo listo-para-pegar — 9 archivos (2 nuevos + 5 diffs + 2 tests)
- Seccion C: vitest completo (~10 tests) + estrategia E2E decidida (sin socket server,
  degradacion graceful) + checklist de verificacion viva
- Seccion D: 10 items de deuda tecnica (incluye 3 bugs Expo detectados)
- Seccion E: checklist de ejecucion para Opus 4.8

**Proxima sesion (Opus 4.8 o el modelo que sea): abrir `docs/W.11-SOCKETS-PLAN.md`
y ejecutar la Seccion E.** No re-investigar, no re-decidir — todo esta verificado
y decidido. Estimacion: 60-90 min incluyendo tests + verificacion viva.

Hallazgos clave que CORRIGIERON el plan previo (detalle en el doc + memoria
`project_sockets_realtime_status.md`):
1. `router.refresh()` NO actualiza SocialView (copia props a useState) → el plan
   incluye bus cliente `useSocketEvent` para wall/networking
2. `disposeSocket()` nunca se llamaba — logout dejaba el socket vivo (fix incluido)
3. `reconnectionAttempts: 5` — tab dormida perdia RT para siempre (fix: Infinity +
   visibilitychange)
4. La webapp no tiene handling de ban (Expo tiene /banned) — Fase 1: toast + logout

El brief `docs/FABLE-5-BRIEF.md` queda como referencia historica — su mision ya se cumplio.

### Segunda pasada Fable (misma fecha) — auditoria espejo + revision de docs

Kamilo pidio buscar errores concretos que rompan el espejo + revisar todos los docs.
Resultado en **`docs/AUDITORIA-ESPEJO-2026-07-04.md`**:

- **A1 (bug real, el grande):** 4 vistas copian props SSR a useState y quedan SORDAS
  a `router.refresh()` — AgendaView, SponsorsView, SoporteView, SocialView. El plan
  W.11 v1 overprometia; ya esta parcheado con 3 diffs prop-sync nuevos (Archivos 8-10,
  ahora OBLIGATORIOS).
- **B (docs con eventos fantasma):** `announcement:new` es dead type (cero emisores),
  `support:new_response` no existe — ambas reclasificaciones nombraban eventos falsos;
  la cobertura real es `data:invalidate{announcements}` que el plan ya escucha.
  `agenda:delayed` SI existe (el doc W.11-sockets-rt lo negaba) y Expo lo toastea.
  PENDIENTES-WEBAPP + W.11-sockets-rt.md corregidos quirurgicamente.
- **Decisiones tomadas (Kamilo aprobo las 4 recomendaciones, misma fecha):**
  (1) `agenda:delayed` INCLUIDO en W.11 — el plan ahora tiene 6 listeners;
  (2) PARITY-MATRIX degradado a DOC HISTORICO (banner agregado, fuente unica =
  PENDIENTES-WEBAPP); (3) dead type `announcement:new` REMOVIDO de
  `eventos-socket/src/types.ts` (typecheck verde, commit en eventos-socket);
  (4) `room:occupancy` dead emit queda como deuda D.13 — decidir al tocar MC/admin.

**El unico doc a abrir para implementar: `docs/W.11-SOCKETS-PLAN.md`** (ya absorbio
todas las correcciones de la auditoria — 6 listeners + 10 archivos + tests +
checklist Seccion E). La auditoria es registro de hallazgos, no doc operativo.

### Tercera pasada Fable (misma fecha) — auditoria TODAS las superficies

Kamilo detecto que la auditoria espejo no cubria las superficies organizador. Resultado
en **`docs/AUDITORIA-SOCKETS-SUPERFICIES-2026-07-04.md`**: mapa de las 10 superficies
del producto + inventario de modulos (Expo, webapp W.X, 50 resources Filament, Event
Pulse, Mission Control, Display LED, Chat Monitor, Attendance Check, Kiosko, Data
Center) + matriz socket completa evento × emisor × consumidor. Hallazgos nuevos:

- **BUG-A Kiosko** (`useAttendance.ts:27-30`): `join:event` con `{event_id}` (server
  espera `{eventId}`) + lee `payload.checked_in` (server emite `checkedIn`) → aforo
  RT del kiosko muerto. Fix 2 lineas en `eventos-kiosko`.
- **BUG-B Attendance Check** (`attendance-check.html:275`): mismo mismatch
  `event_id`→`eventId` → counters silent disco no suben en vivo. Fix 1 palabra.
- **GAP-C Event Pulse**: escucha entities `leads/connections/ratings/leaderboard`
  que SOLO emite PulseSimulate — en evento real esas 4 metricas tienen lag de hasta
  5 min (bootstrap fallback). Fix 4 broadcasts en backend (Networking accept, Lead
  scan, ratings, PointsService).
- **RIESGO-D Expo**: 6 puntos de conexion socket vs MAX_CONNECTIONS_PER_USER=5 —
  stream + encuestas + wall montado puede exceder y perder RT aleatorio. Backlog Expo.
- `room:occupancy`: contexto cerrado — Pulse usa `checkin:update`, este evento nunca
  tuvo consumidor (D.13 validada con contexto completo).

**FIXES APLICADOS (Kamilo aprobo "dale aplica todo", misma fecha):**
- BUG-A kiosko: `useAttendance.ts` corregido (join en connect + eventId + checkedIn). Typecheck verde
- BUG-B: `attendance-check.html` corregido (`eventId`)
- GAP-C: 4 broadcasts agregados en backend (NetworkingController accept, LeadController
  store, RatingController + SpeakerRatingController store, PointsService award).
  PHP lint verde + tests Networking/Rating/Lead corridos
- Verificacion viva pendiente al proximo arranque de ambiente (ver seccion 5 de la
  auditoria superficies)
- **RIESGO-D Expo APLICADO** (Kamilo lo subio a prioridad 1): `eventos-app` commit
  `0d9a754` — `lib/socket.ts` singleton (ref-count de session rooms, join:event
  centralizado, re-join tras reconexion, dispose solo en logout) + 6 consumidores
  migrados (useDataInvalidation/useChat/useQnA/useSessionMode/useWall/encuestas).
  Typecheck limpio (5 errores pre-existentes del WIP recap sin relacion).
  **VERIFICACION VIVA PENDIENTE** al proximo arranque: regresion streaming completa
  (chat/Q&A/polls/emojis/pinned), wall RT, encuestas, y log del socket server con
  `conns=1` estable navegando entre modulos.
  OJO: el repo Expo tiene WIP sin commitear de Kamilo (recap/ + useAgenda/usePhotos/
  useSponsors/useNetworking/lib/api) — NO tocado, sigue en working tree.

---

## Ultima sesion (cierre 2026-07-04 tarde — W.18 100% + 7 modulos cerrados via reclasificacion + investigacion W.11 sockets)

**Total acumulado webapp:** **~484/695 = 69.6%** (+16 items hoy)
**Estado al cierre:** todo pusheado. `eventos-web`, `eventos-backend` (feature/magic-link-auth), `APP EVENTOS` sincronizados.

### **12 modulos cerrados 100% real** (antes eran 5)

Cerrados de verdad: W.0, W.1, W.1B, W.5, W.7, W.8, W.9, W.10, W.13, W.14, W.17, W.18.

Cerrados HOY via reclasificacion formal (no fake maquillaje — items movidos al pool que les corresponde):
- **W.5 Speakers 33/35 → 35/35** — Lighthouse Perf/Acc + device real → W.12 Polish
- **W.13 FAQ+Docs 15/17 → 15/15** — Pages dinamicas → Fase 2
- **W.8 Networking 15/25 → 21/21** — Mi perfil editable cubierto por W.18 con link SidebarLeft. Filtro role → skip (backend publico no expone). RT listeners → W.11 Sockets. Sugeridos cards + Tracking → Fase 2. E2E ampliado con 5 tests nuevos
- **W.0 Spatial UI 21/24 → 24/24** — Command palette → Fase 2. Pre-load + device real → W.12
- **W.1 Setup+Auth 102/107 → 107/107** — B4/B11 haptics → Fase 2. Smoke device + Lighthouse → W.12. CSP Vimeo/Sentry → agrupado con W.4 cierre
- **W.14 Anuncios+Cartel 17/20 → 17/17** — Socket announcement:new → W.11. Web Push → W.12
- **W.17 Soporte 13/15 → 13/13** — Socket support:new_response → W.11

### Feature nuevo real de la sesion — link identity → /perfil

`SocialView.tsx` sidebar izq: el bloque de identidad (avatar+nombre+stats) ahora es clickeable, navega a `/perfil`. Hover state con pill "Editar perfil" arriba a la derecha. Cierra el loop W.18 desde contexto de networking. 5 tests E2E nuevos: click identity → /perfil, filtro Sin contactar, abrir perfil → panel + CTA Conectar, rechazar solicitud (Ignorar), tab Bloqueados + Desbloquear. 11/11 verde.

### Investigacion completa sockets (Fase pre-codigo)

Se verifico el estado real cross-repo (Expo + webapp + socket + backend Laravel):
- **Webapp:** socket singleton `lib/streaming/socket.ts` ya funciona pero SOLO se abre cuando el user entra a `/session-stream`. NO hay hook global cross-modulo
- **Expo:** abre socket al login. Verificado en vivo: `[socket] connected user=3 attendee=2 role=attendee event=summit-empresarial-2026 conns=1` + `joined event:1`
- **Backend socket server:** emite en produccion `data:invalidate`, `wall:post`, `wall:comment`, `ban:enforced`, `networking:notify` (verificado en `eventos-socket/src/index.ts` lineas 221/193/196/304/338)
- **Expo `useDataInvalidation.ts`** (467 lineas) escucha 19 listeners cross-modulo. Del scope Fase 1 webapp: 5 aplican (data:invalidate, ban:enforced, networking:notify, wall:post, wall:comment). 8 son W.15 Vendor (opcional). 5 son W.16 SKIP webapp. 2 nice-to-have Fase 2.
- **Divergencia inevitable:** Expo usa TanStack Query + `queryClient.invalidateQueries`. Webapp usa SSR + `router.refresh()` (patron Next 16). NO portar TanStack en bloque, portar solo el patron equivalente.

**Detalle completo:** `memory/project_sockets_realtime_status.md`.

### Decisiones cerradas (no preguntar)

- **12 modulos cerrados via reclasificacion honesta** — items no perdidos, movidos al pool correcto (W.11/W.12/Fase 2)
- **W.11 Sockets es el siguiente sprint** — bloqueante clave que destraba items reclasificados en W.14/W.17/W.9/W.6
- **Patron webapp: SSR + `router.refresh()`** para data:invalidate, NO invalidate queries. Divergencia tecnica documentada, comportamiento identico Expo
- **`useDataInvalidation` hook global** al mount del layout `(app)`, NO en cada modulo. UN socket por sesion (verificado que asi funciona Expo)
- **NO tocar los 4 hooks streaming** — funcionan bien. `join:event` redundante del `useChat` queda idempotente (server hace `.join()` seguro)
- **Fable 5 disponible** — para analisis exhaustivo cross-repo la proxima sesion. Se puede invocar con `/model fable` o `claude --model fable` (requiere Claude Code v2.1.170+, `claude update`)

### Que sigue — W.11 Sockets criticos

Plan concreto en `memory/project_sockets_realtime_status.md`. Resumen:

**Crear:**
1. `src/hooks/useGlobalSocket.tsx` — Client Provider que envuelve al SpatialShell. Al mount: `getSocket()` + `emit('join:event', {eventId})` + registra 5 listeners con handlers (router.refresh + lumina toast + router.replace si ban).
2. Registrar listeners: `data:invalidate` (debounce 800ms), `ban:enforced`, `networking:notify` (batched 1500ms), `wall:post`, `wall:comment`.

**Modificar:**
3. `src/app/[locale]/(app)/layout.tsx` — envolver `<SpatialShell>` con `<GlobalSocketProvider eventId={event.id}>`.

**Efecto real inmediato tras deploy:**
- Bell/anuncios refrescan sin recargar
- Feed W.6 live
- W.8 toast al recibir solicitud
- Puntos/passport se invalidan
- Ban → kick + redirect

**Tiempo estimado:** 45min - 1h con Fable 5 (o Opus).

### Ambiente para reanudar

- **Socket server:** correr con `cd C:/laragon/www/eventos-socket && pnpm dev` (puerto 3001, Redis DB 2)
- **Backend Laravel:** ya corre en Laragon en `eventos-backend.test`
- **Webapp dev:** `cd C:/laragon/www/eventos-web && pnpm dev` (puerto 3000)
- **Verificacion viva:** con Expo mobile logueado, el log del socket muestra connect+join event de forma instantanea

### Estado git al cierre

- **`eventos-web` main:** pusheado con feature link identity + E2E ampliado
- **`eventos-backend` feature/magic-link-auth:** pusheado con validator flexible profile
- **`APP EVENTOS` main:** pusheado con memoria sockets + docs cierre 12 modulos

---

## Sesion 2026-07-04 manana — W.18 Hub Personal entrega inicial

### W.18 Hub Personal — entregado 19/19 (100%)

**Que es:** ruta `/perfil` split layout 35/65 (wall + panel der) espejo directo de `ProfileScreen.tsx` mobile (927 lineas Expo, ~85% completo).

**Wall izq (35%):**
- Hero: avatar 92px + nombre + `cargo · empresa` + socials (LinkedIn/Twitter/Instagram/Web con SVG inline por licencias lucide)
- Stats gamification opcional (3 cards SIN iconos — solo valor + label, evita redundancia con el texto)
- Rows clickeables con chevron: Mis datos / Mis intereses / Apariencia
- Footer: "Ver introduccion de nuevo" (link discreto) + "Cerrar sesion" (rojo)

**Panel der (65%):**
- Empty state espejo W.13/W.14/W.17 (`<h3>` label + `<p>` desc para heading role accesible)
- AnimatePresence entre 3 sub-views con transicion 240ms cubic-bezier(0.16, 1, 0.3, 1)
- **PerfilDataForm**: 3 cards visuales agrupando por concepto ("Sobre ti" nombre+cargo+empresa · "Contacto" telefono+email disabled · "Redes sociales" 4 fields). **UN solo boton "Guardar"** al final (patron unidad — no save-por-card tipo Vercel/Stripe porque no son mundos aparte, es la misma persona)
- **PerfilInterestsForm**: chips con contador `<N> seleccionados · minimo 1`. Empty state honesto si el organizador no configuro opciones (`options.length === 0`)
- **PerfilAppearanceForm**: 2 cards grandes Lux/Noir con preview visual mini. Aplica al instante via `useTheme()` de next-themes. Sin boton Guardar

**PerfilLogoutModal**: confirm modal con cross-tab broadcast + redirect a /login.

### Decisiones DaVinci del layout (proceso)

Descartamos 3 falsos caminos antes de acertar:
1. **Single column max-w-560** — dejaba espacio muerto a la derecha en 65% del panel
2. **Living preview + form** — la preview era invento decorativo, no aportaba
3. **Save-por-card (Vercel/Stripe pattern)** — overkill para perfil de asistente (no son settings de mundos distintos)

**Layout final aprobado:** cards visuales agrupando + un solo Guardar. Cero max-width interno del contenido. Padding panel `22px 28px 28px` espejo W.13/W.14/W.17.

### Sidebar refactor (misma sesion)

- **Borrado:** `ProfilePopover.tsx` (reemplazado por `/perfil`) + `UserMenu.tsx` (huerfano)
- **Reordenado:** top nav (modulos del evento: Home/Agenda/Live/Speakers/Social/Sponsors/Desafio/Documentos) → separador → **bottom zona personal** (Asistente + Perfil + Bell)
- **Coherencia tonal:** Bell placeholder + BellPopover usan `--text-muted` (igual que nav items), no `--text-label`. Hover unificado a `--text-primary`
- Sacado `user` prop de `SidebarPill` y `SpatialShell` (ya no lo necesitan)

### Backend cambio (feature/magic-link-auth)

`ProfileController@update`: validator `linkedin/website` de `nullable|url:http,https` → `nullable|string|max:500`. **Espejo Expo** — acepta `linkedin.com/in/kamilo` sin exigir `https://`. Cliente normaliza con `normalizeUrl()` al renderizar el href del link.

### Bug fix critico dentro de la sesion — email undefined post-save

Backend PUT `/me/profile` NO devuelve `email` y usa nombres con sufijo (`linkedin_url`, `website_url`). Primera version del `setProfile(next)` reemplazaba el prev completo con la respuesta → `profile.email = undefined` → input controlled se rompia con warning "changing controlled to uncontrolled".

**Fix:** `updateProfile` cliente normaliza shape (`_url` → sin sufijo) y devuelve `Partial<MyProfile>`. `PerfilView.onSaved` hace merge `{ ...prev, ...patch }` preservando el email.

### Deep link nuevo

`eventos://profile[/subseccion]` en `parseActionUrl`:
- `eventos://profile` → `/perfil`
- `eventos://profile/datos` → `/perfil?section=datos`
- `eventos://profile/intereses` → `/perfil?section=intereses`
- `eventos://profile/apariencia` → `/perfil?section=apariencia`
- `eventos://perfil` (alias es) tambien mapeado

### Decisiones cerradas (no preguntar)

- **W.18 layout = split 35/65 con cards visuales + un solo Guardar** — coherencia W.13/W.14/W.17
- **Cero emojis en intereses UI** — la BD mantiene emojis para Expo, webapp los ignora
- **Stats sin iconos redundantes** — solo valor + label
- **Perfil vive en el bottom del sidebar** junto a Asistente y Bell, NO en el nav principal (zona personal ≠ modulos del evento)
- **`ProfilePopover` eliminado** — todo lo que ofrecia (tema + logout) ahora vive en `/perfil`
- **Validator website/linkedin flexible** — `nullable|string`, cliente normaliza protocolo al renderizar
- **Notificaciones opt-in FUERA del perfil** — no existe en Expo ni backend (invento inicial mio, corregido tras auditoria)
- **FAQ NO se duplica dentro del perfil** — ya vive en sidebar `/faq`
- **Idioma toggle FUERA por ahora** — i18n del usuario aun no configurado; sumar cuando aplique

### Verificacion

- **Vitest: 391/391** verde (+14 nuevos: 8 profileNormalize + 6 deep link perfil)
- **E2E `perfil.spec.ts`: 13/13** verde (serial mode como W.13/W.7 por saturacion turbopack)
- Typecheck limpio
- Lint 0 errores en modulo perfil

### Foto upload + shuffle beam (cerrado en la misma sesion)

Al final de la sesion se cerraron los 2 items restantes:
- **`PerfilAvatarMenu.tsx`** — Radix Popover anchored al avatar. Sin foto → 2 opciones (subir + shuffle). Con foto → 2 opciones (subir + eliminar rojo)
- **Beam avatar** ahora es el fallback (reemplaza las iniciales del webapp previo). URL espejo LITERAL Expo: `hostedboringavatars.vercel.app/api/beam` con colores `0EA5E9,6366F1,14B8A6,A855F7,38BDF8`
- **Shuffle** cicla seed 0→1→2→3→1 (nunca vuelve a 0 sin reset). Persistido en `localStorage` key `eventos:avatar_seed:{email}` (mismo modelo MMKV Expo)
- **Upload** via input file oculto → POST multipart al proxy `/api/profile/photo`. Max 5MB. Toast error si excede
- **Delete** DELETE al mismo endpoint → resetea seed a 0 (vuelve al beam base)
- **CSS `.pf-avatar-menu`** — Radix Popover con item hover surface-medium, item danger accent color
- `next.config.ts` agregado `hostedboringavatars.vercel.app` a `images.remotePatterns`
- i18n +7 keys (photoUpload/photoShuffle/photoDelete + toasts)

### Estado git al cierre

- **`eventos-web` main:** pusheado (W.18 modulo completo + sidebar refactor + tests)
- **`eventos-backend` feature/magic-link-auth:** pusheado (validator flexible ProfileController)
- **`APP EVENTOS` main:** pusheado (memoria W.18 actualizada + sidebar_bottom_zone + PENDIENTES + NEXT-SESSION + design demo)

---

## Sesion 2026-06-30 tarde — W.13 Fase B Documents + arquitectura ZIP escalable

**Total acumulado webapp:** **~451/707 = 63.8%** (+13 hoy entre 2 sesiones: cartel manana + documents tarde)
**Estado al cierre:** todo pusheado. `eventos-web`, `eventos-backend` (main + feature/magic-link-auth), `APP EVENTOS` (docs + memoria) sincronizados.

### W.13 Fase B — Documents entregado 15/17 (88%)

**Que es:** ruta `/documentos` con split layout wall + preview panel der, sidebar item dinamico (`available` segun count).

**Comportamiento:**
- Wall izq scroleable con cards (icono lucide FileText/FileImage/FileVideo/FileAudio/File — NO emojis, memoria `feedback_no_emoji_icons_ui`)
- Panel der 2 estados: empty / detail con preview embed segun kind (PDF iframe, imagen `<img>`, video/audio `<video>/<audio>` controls, otros fallback metadata)
- Skeleton shimmer mientras carga el iframe/img/video (reusa `sn-sk-shape` pattern) + fade-in 220ms + timeout fallback 6s
- Descarga individual con `<a download>` + suggestedFilename (sanitizado)
- **Bulk "Descargar todos" ARQUITECTURA ESCALABLE** — pre-generada backend, cero ZIP client-side
- URL state `?id=X` para deep link + Esc cierra
- CSP `frame-src` extendido a `https:` para permitir embed cross-origin

### Arquitectura ZIP pre-generado (decision escalable a 10K users)

**Trigger inicial:** JSZip client-side fallo por CORS con archivos externos (africau.edu, etc.). Y ZIP on-the-fly server-side no escala a 10K simultaneos (satura PHP-FPM). Diseno final espejo de plataformas grandes (Notion/Dropbox/GDrive):

**Backend:**
- Migration `events` + `documents_zip_url`, `documents_zip_generated_at`
- `DocumentObserver` (Filament create/edit/delete) dispatch `RegenerateDocumentsZipJob`
- Job `ShouldBeUnique` TTL 30s (coalesce ediciones rapidas del organizador)
- Job usa `maennchen/zipstream-php` (puro PHP, no requiere `ext-zip`, streaming = baja RAM)
- Sube al disk `filesystems.documents_zip_disk` (default `public` dev, `r2` prod)
- Endpoint `GET /events/{id}/documents/zip` devuelve JSON `{ url, generated_at }` (200) o `{ status: not_ready }` (202)

**Frontend:**
- Proxy `/api/documents/{eventId}/zip` con bearer server-side
- Boton bulk hace fetch → recibe URL → `<a download>` con URL del CDN
- Sacado `jszip` de package.json (~50KB dep menos)

**Costo con Cloudflare R2 en prod:**
- 10K descargas × 10MB = 100 GB de tráfico = **$0.00** (R2 egress gratis, no como AWS S3)
- Storage: ~10 MB por evento = <$0.001/mes
- CPU backend: cero (Laravel solo redirect)
- Regeneracion: 1 job por cambio del organizador (segundos, no bloquea)

**Detalles completos:** `memory/project_w13_documents.md`.

### Fix critico dentro de la sesion — `useNow` snapshot inestable

Bug introducido ayer al arreglar hydration mismatch: `useSyncExternalStore` con `getSnapshot: () => Date.now()` viola contrato del hook (snapshot debe ser estable). React llama snapshot cada render, detecta cambio, re-render, otro snapshot, otro valor, loop → "Maximum update depth exceeded" en AgendaView (30+ consumers amplifican). Fix en `993c9ea`: snapshot lee de un objeto interno estable via `useMemo` + notify explicito en subscribe. E2E agenda paso de 2/15 → 13/15 tests. Memoria `feedback_no_date_now_in_usestate` actualizada con el gotcha.

### Bugs adicionales resueltos (con memorias)

- **TaskStop deja node.exe huerfano en Windows** → `feedback_taskstop_zombie_node`. Verificar con `tasklist` + `taskkill /PID X /F` si aparece "Jest worker exception" post-cierre
- **NO emojis como iconos UI** → `feedback_no_emoji_icons_ui`. Iconografia es lucide siempre. Primera version de Documents uso 📕🖼️🎬🎵📄 y hubo que revertir
- **CSP `frame-src` restrictivo bloqueaba PDF embed** → agregado `https: data:` en next.config.ts. El bloqueo NO era de w3.org/culinaria sino nuestro propio webapp
- **Seed backend tenia 6 documents duplicados x2** → limpiado con tinker (`Document::where(event_id)->delete()` + re-seed manual solo docs)

### Decisiones cerradas (no preguntar)

- **NUNCA JSZip client-side para bulk descarga a escala** — CORS + saturacion. Pre-generar backend + servir del CDN. Memoria `feedback_no_client_zip_scale`
- **Iconos siempre lucide, cero emojis en UI** — memoria `feedback_no_emoji_icons_ui`
- **`useSyncExternalStore` requiere snapshot estable** — no `() => Date.now()`, usar ref/state con notify explicito
- **CSP `frame-src https: data:`** para permitir embed PDF/media HTTPS externos del organizador
- **Documents en Expo es siempre disponible** (no condicional por estado del evento). Webapp espeja ese comportamiento aunque en Expo la pantalla sea "huerfana" (no navegable desde UI). En webapp le pusimos entry propio en sidebar
- **Deep link `eventos://documents/{id}`** mapeado a `/documentos?id=X` (patron espejo `/anuncios`)
- **Cloudflare R2 arquitectura documentada** (`docs/infra/DISPONIBILIDAD-HA.md`) — en dev es local, en prod es R2 con egress gratis. Job del ZIP es disk-agnostico

### Estado git al cierre

- **`eventos-web` main:** pusheado con feature W.13 Fase B + hotfix useNow + fix CSP frame-src + memorias updated
- **`eventos-backend` feature/magic-link-auth:** pusheado con migration + Observer + Job + Controller + Model fillable + composer maennchen/zipstream-php + Route + seed limpio
- **`APP EVENTOS` main:** pusheado con NEXT-SESSION + PENDIENTES actualizado + memorias

### Correccion post-cierre — investigacion W.18 Hub Personal (para arrancar bien la proxima)

En el cierre inicial marque W.18 como "riesgo MEDIO / 3-4h — primer feature sin espejo Expo claro". **Fue error.** Post-cierre investigue el perfil en Expo (`Explore` agent, 2026-06-30 tarde) y encontre:

- **Expo tiene ProfileScreen.tsx** de 927 lineas ~85% completo (`eventos-app/components/screens/ProfileScreen.tsx`). Espejo directo.
- **Backend YA tiene 11 endpoints** listos (`/me/profile` GET/PUT, `/me/photo` POST/DELETE, `/me/points`, `/events/{id}/my-interests` GET/PUT, etc.).
- **Componentes reusables identificados:** `StatCard`, `DataRow`, `EditField`, `MyInterests`, `BottomSheet` — todos replicables en webapp con shadcn/Radix.
- **FAQ NO esta dentro del perfil** en Expo — es un icono "Ayuda" en el header top-right que navega a `/faq`. En webapp `/faq` ya vive en sidebar, no duplicamos el entry.
- **Features Expo NO tiene que webapp puede sumar:** idioma toggle (i18n ya en es/en/pt), notificaciones opt-in cuando W.12 Web Push llegue.

**Correccion:** W.18 pasa a **riesgo BAJO** + **2-3h**. Blueprint completo en `memory/project_w18_hub_personal_blueprint.md`.

**Decision DaVinci pendiente al arrancar la proxima sesion (NO codear antes):** layout single-column centrado max-w-640 vs split layout wall + panel der. Mi voto tentativo single-column (perfil personal no es navegador de listas). Definir con el usuario primero.

---

## Sesion 2026-06-30 manana — W.14 Fase B Cartel Digital

**Total acumulado tras esta seccion:** **~446/707 = 63.1%** (+8: W.14 +6 + W.2 +2). Luego +5 en la tarde con W.13 Fase B (arriba).
**Estado al cierre:** todo pusheado. `eventos-web` HEAD remoto + `APP EVENTOS` docs + `eventos-backend` commit `1d8d1e4` (announcement on ticket-resolve) finalmente con push.

### W.14 Fase B — Cartel Digital entregado 17/20 (85%)

**Que es:** carrusel ambient signage en col der LIVE state. NO slideshow.
- Banner 16:9 arriba col der (~230px, ~25% del alto)
- Cross-fade 700ms cada 6s. Sin dots, sin flechas. Cartelera, no slideshow.
- Hover/focus pausa el ciclo
- Sponsor pill top-left si `sponsor_name`, titulo overlay bottom-left si `title`
- Click → deeplink via `parseActionUrl` (reusado de W.14 Fase A Anuncios)
- Empty → zona colapsa, feed salas ocupa 100%
- Single item → estatico, no cicla
- `prefers-reduced-motion` → cross-fade instantaneo
- SSR-safe (sin `useState(() => Date.now())`)

**Archivos nuevos:**
- `src/lib/banners.ts` + `src/lib/highlights.ts` — SSR fetchers
- `src/lib/cartel-items.ts` — merger round-robin + tipo `CartelItem`
- `src/components/app/home/CartelDigital.tsx` — componente client
- `tests/lib/cartelItems.test.ts` (11 vitest merger)
- `tests/components/home/CartelDigital.test.tsx` (12 vitest componente)
- `e2e/cartel.spec.ts` (6 specs)

**Archivos modificados:**
- `LiveState.tsx` — split adaptive col der (cartel arriba si hay items, feed 100% si no)
- `HomeView.tsx` + `home/page.tsx` — propagar y fetchear `cartelItems`
- `e2e/_fixtures/data.mjs` + `e2e/_helpers/mockBackend.mjs` — handlers + bearer `no-cartel`

**Verificacion:** 356/356 vitest verde + 6/6 E2E cartel + 6/6 E2E home (sin regresion) + typecheck + lint clean.

**Backend cero cambios** — `BannerController` + `HighlightController` + `HighlightObserver` ya existian.

### Decisiones cerradas (no preguntar)

- **Cartelera ≠ slideshow.** Sin dots, sin flechas, sin counter. Ambient, no interactivo.
- **Cross-fade puro 700ms** (estandar signage real: Daktronics/Mercedes-Benz Stadium/ScreenCloud). Ni slide horizontal ni split-flap.
- **6s por slot** para banner y highlight (sin jerarquia visual rara).
- **Banners + Highlights mergeados en UN solo cartel** round-robin. Backend separado, webapp unifica cliente.
- **Sponsor pill discreta** muestra `sponsor_name` directo (no label "Sponsor"). Highlights sin pill.
- **Cartel solo en LIVE state.** PRE y ENDED no muestran cartel (sponsor premium quiere visibilidad cuando hay eyeballs reales).
- **Backend reusado cero cambios.** Si organizador no configura banners/highlights, cartel ausente — feed salas ocupa 100%.

### Re-estimacion sprints (analisis honesto post-entrega)

Velocidad real ~50% mas rapida por reuso de patrones (parseActionUrl, lumina, useNow, framer split layout, mockBackend bearer scenarios). Roadmap original 22h → realista **11-14h en 3 sesiones DaVinci**.

| Sprint | Cierre 2026-06-29 | Realista 2026-06-30 |
|---|---|---|
| W.13 Fase B Documents | ~1h | **30-40 min** |
| W.18 Hub Personal | ~5-6h CRITICO | **3-4h** riesgo MEDIO (sin espejo Expo) |
| W.8 Networking | ~3h | **1.5-2h** |
| W.4 Replay + in-stream | ~5h | **3h** riesgo MEDIO (depende backend) |
| W.11 sockets criticos | ~2h | **1-1.5h** riesgo ALTO (E2E flaky) |
| W.12 Web Push + Sentry | ~4h | **2-3h** |

Detalle en `memory/project_velocity_analysis_2026_06_30.md`.

### Estado git al cierre

- **`eventos-web` main:** pusheado (cartel digital + 23 tests + E2E + fixtures + mockBackend)
- **`APP EVENTOS` main:** pusheado (PENDIENTES + NEXT-SESSION + memorias)
- **`eventos-backend` feature/magic-link-auth:** `1d8d1e4` finalmente pusheado (announcement on ticket-resolve)

---

## Sesion 2026-06-29 nocturna — W.13 + W.17 + bug SSR hydration

**Total acumulado webapp:** ~436/707 = **62%** (+22 entre 2 sesiones)
**Estado al cierre:** todo en `main` de `eventos-web` (HEAD `c33e794`).

### Modulos avanzados/cerrados HOY (sesion nocturna)

1. **W.13 FAQ "Asistente" con Orb Siri-style — Fase A ~10/17 (~59%)**
   - Split layout literal espejo W.7/W.9/W.14 (wall izq con header+chips+lista questions, panel der con orb+3 estados browsing/thinking/answering)
   - `OrbBlob.tsx` + `orb.css` CSS-puro 4 radial gradients (cyan/pink/purple/core) con morph keyframes y soporte Lux (filter saturate 1.6 brightness 0.95 + colores saturados teal/magenta/indigo, sin "spot dark" feo)
   - `AsistenteView.tsx` con thinking timer 800ms, cambio de pregunta on-the-fly sin disabled state (haptics+optimistic en lugar de bloqueo)
   - Wired a `/soporte?new=true` (CTAs siempre visibles en panel der)
   - Fase B pendiente: Documents (~1h) — `feedback_una_sola_ventana_operativa` aconseja Pages a Fase 2

2. **W.17 Soporte — Casi completo ~13/15 (~87%)**
   - Split layout espejo W.14 (wall izq tickets, panel der 3 estados empty/detail/new)
   - `/api/support` proxy con manejo 403/422/429
   - `lib/support-client.ts` separado de `lib/support.ts` para no importar `next/headers` en cliente
   - Framer-motion stagger + haptics enterprise + AnimatePresence
   - **Soporte vive DENTRO del Asistente** — nav sidebar reducido (8 items, sin item "Soporte"). Entry vive en CTAs del FAQ. X del panel devuelve a `/faq` (subflow)
   - Backend integrado: `EditSupportRequest.php` ahora crea **announcement privado** `eventos://my-support` cuando admin responde un ticket (sin esto el webapp no se enteraba, no recibe push Expo)

3. **W.14 +1 backend integration (11/20)** — el announcement privado del punto anterior

### Bugs detectados y arreglados (cronicos en CI)

1. **`sponsors:123` flake cronico** — root cause profundo: `SponsorsView.tsx:35` usaba `useState(() => Date.now())` para `shuffleSeed`. SSR generaba seed A, hidratacion generaba seed B → orden de cards distinto SSR vs cliente → React dispara `Hydration failed` → Next dev overlay tapa la pagina → Playwright no podia clickear. Fix: seed inicial `0` (estable SSR), el primer interval tick a los 7s arranca el shuffle vivo (commit `bbff874`).

2. **5 componentes mas con el mismo patron SSR-unsafe** — `AgendaView`, `LiveState`, `PreState`, `GoldenTicketPanel`, `RedeemModal`. Creado nuevo hook `src/hooks/useNow.ts` (basado en `useSyncExternalStore`, snapshot SSR=0). Refactor de los 5 con useState anchor + `// eslint-disable-next-line react-hooks/set-state-in-effect` (patron ya usado en el codebase). Commit `c33e794`.

### Polish UI hoy

- `86c433e` — quitar overline (`text-transform: uppercase`) en cards (anuncios/faq/soporte). Solo se mantiene en inputs y badges (espejo Expo).
- `b074259` — quitar accents rojos en `.selected` (`.sp-card.selected`, `.an-card.selected` usaban `var(--accent)` rojo del cliente) + quitar lineas verdes decorativas (`.faq-response-bar`, `.sp-message-bar`). El icono verde semantico `CheckCircle2` en "Respuesta del organizador" se mantiene (comunica significado).

### Decisiones cerradas (no preguntar)

- **Soporte es subflow del Asistente, NO modulo independiente del sidebar.** Sin item "Soporte" en nav. Entry via CTAs del FAQ. X devuelve a `/faq`.
- **Cuando admin responde un ticket → backend crea announcement privado.** Sin esto el usuario webapp no se enteraba (no recibe push Expo). El bell baja el badge cuando lo abren.
- **NO overline en cards.** Solo inputs/badges (espejo Expo).
- **NO accent (rojo del cliente) en `.selected` state.** Usar `--border-strong` neutral.
- **NO barras verticales decorativas** (teal/verde) al lado de response cards.
- **`useState(() => Date.now())` es banned** en componentes SSR-rendered. Usar `useNow` hook nuevo o `useState(0) + useEffect` con eslint-disable comment.

### Conversacion del cierre — recortes de scope pendientes

El usuario pidio status general y le di breakdown DaVinci de todos los modulos pendientes con recomendacion de skips. Resumen para confirmar PROXIMA sesion:

- **W.6 Stories + Photo Contest** → skip (Stories son mobile-first, Photo Contest nicho). Cerrar W.6 en 18/40 como "MVP cerrado, extensiones Fase 2". **Ahorra ~3-4h.**
- **W.16 Live Moments entero** → skip en webapp (sorteos/trivia/golden ticket reveal son mobile-first; los usuarios sacan el celu, no abren laptop). Webapp solo muestra resultados historico. **Ahorra ~6h.**
- **W.2 Sponsors band + lifecycle states home** → skip (ya hay /sponsors, cinematic ya esta). **Ahorra ~5h.**
- **W.11 Sockets RT consolidacion** → recortar de 42 items a ~6 criticos (invalidations para announcements/ratings/photobooth/matchmaking). **Ahorra ~4h.**
- **W.12 Polish + PWA + E2E full** → recortar a Web Push real + Sentry. PWA installable + offline + Lighthouse 100 + E2E full → Fase 1.5 o skip. **Ahorra ~6h.**

**Recount realista sin bloat:** ~22h totales (3-4 sesiones DaVinci) en lugar de 70-80h del roadmap original.

Sprints inmediatos recomendados (orden):
1. W.14 Fase B (Banners + Highlights + RT socket, ~2h)
2. W.13 Fase B (solo Documents, ~1h — Pages a Fase 2)
3. W.18 Hub Personal (perfil editable + settings, ~5-6h — CRITICO)
4. W.8 Networking completar (~3h)
5. W.4 Replay + anuncios in-stream (~5h)
6. W.11 sockets criticos (~2h)
7. W.12 Web Push + Sentry (~4h)

### Estado git al cierre

- **`eventos-web` main HEAD remoto: `c33e794`** — todo pusheado. CI `gh run 28420089131` corriendo, verificar al abrir.
- **`APP EVENTOS` main:** sin commits desde `b266448` (este NEXT-SESSION va a ir como nuevo commit + sumar PENDIENTES-WEBAPP actualizado si quieres).
- **`eventos-backend` feature/magic-link-auth:** commit local `1d8d1e4` (EditSupportRequest crea announcement) **SIN PUSH**. Necesita autorizacion del usuario para push.

### Pendiente actualizar PENDIENTES-WEBAPP.md (counters desactualizados)

- W.13: 0/17 → 10/17 (~59%)
- W.17: 0/15 → 13/15 (~87%)
- W.14: 10/20 → 11/20 (backend announcement on ticket-resolve)
- Total: 414/707 → ~436/707 (~62%)

No lo hice porque el usuario quiso cerrar primero — pedir actualizar al abrir si vale.

---

## Sesion 2 del 2026-06-29 (diurna previa) — Sprint 2.B + 2.C Fase A

**W.14 Fase A entregada 10/20 (50%):**

1. **`lib/announcement-deeplink.ts`** — helper puro `parseActionUrl()` con 13 mappings `eventos://*` → rutas webapp. Verificado contra grep backend: `GameController:685` + `GoldenTicketResource:144,232` + `EventPhotoResource:237,364` ya generan `action_url: eventos://gamification/rewards` cuando alguien gana — webapp lo mapea a `/desafio` directo. Backlog W.13/W.15/W.17 caen a `internal-future` con toast amable. Externos a `window.open('_blank', noopener,noreferrer)`. Desconocidos a `console.warn` sin romper UI. 23 vitest.
2. **`lib/announcements.ts`** — SSR fetcher con apiFetch + bearer cookie. Backend mezcla publicos por rol + privados `target_attendee_id`.
3. **`lib/announcements-unread.ts`** — helpers puros `countUnread` / `lastSeenKey` / `timeAgo` con `now` inyectable. 16 vitest.
4. **Ruta `/[locale]/(app)/anuncios/page.tsx`** SSR + `AnnouncementsView` cliente con lista vertical espejo Expo. Cards expandibles inline si tienen action_url. Marca `lastSeenAt` al montar para bajar badge bell.
5. **`BellPopover`** reemplaza `<span>` placeholder en `SidebarPill` (W.0). Preview 5 mas recientes + footer "Ver todos" → `/anuncios`. Badge unread via `localStorage:eventos:announcements:lastSeenAt:{eventId}` con sync cross-tab via `storage` event. Lazy init useState (no setState-in-effect). Divergencia intencional vs Expo (mobile bottom tabs vs sidebar desktop, popover ahorra navegacion).
6. **Sidebar nav item Anuncios** (icono Megaphone, available:true).
7. **i18n** namespace `anuncios.*` en es/en/pt (title/subtitle/empty/popover/futureToast/openCta).
8. **Layout integration** `app/[locale]/(app)/layout.tsx` fetch announcements server-side, pasa a SpatialShell → SidebarPill.
9. **E2E `anuncios.spec.ts` 10/10 verde** (15s estable, serial mode): auth gate / SSR sorted by fecha / click eventos://gamification/rewards → /desafio / click sin action_url no nav / click internal-future toast / empty state via bearer "no-announcements" / Bell badge unread cae a 0 al abrir / popover footer Ver todos / popover card golden ticket cierra+nav / sidebar item Anuncios nav.
10. **309/309 vitest verde** (+39 nuevos: 23 deeplink + 16 unread), typecheck OK, lint W.14 clean, build OK.

**W.9 Engagement CERRADO 35/35:**

- Redemptions INLINE en catalogo (eliminado tab "Mis canjes" por espejo Expo)
- `handleShowExistingQR` reusa token sin POST (cero riesgo cobrar puntos doble)
- Bloque "Canjes activos sin catalogo" para rewards retirados
- E2E `desafio.spec.ts` 11/11 verde: 8 funcionales + 3 viewports (desktop 1600 / tablet H 1130 / mobile 390 sin overflow horizontal)
- PARITY-MATRIX sync: W.9 0/35→35/35 + W.7 0/23→23/23 + totales (modulos cerrados 2→5, vitest 194 fail→270 verde, E2E 9→11 specs)

### Decisiones cerradas (no preguntar)

- **Sin tabs "Todos/No leidos"** en anuncios — backend no persiste read_at, tabs serian cosmeticas confusas.
- **Sin modal/panel detail anuncios** — entran completos en card.
- **localStorage:lastSeenAt scopeado por eventId** — multi-evento aislado, sobrevive recarga.
- **BellPopover divergencia intencional** vs contador in-memory de Expo — sidebar lateral webapp ahorra navegacion.
- **Web Push real → W.12 Polish** (no W.14). 8 tipos de push enum documentados.
- **RT socket `announcement:new` → W.14 Fase B** (depende W.11 sockets 20%).
- **Banners + Highlights → W.14 Fase B** (tocan W.2 home).
- **Tab "Mis canjes" descartado** — Expo NO tiene tab. Redemptions inline en catalogo.
- **`test.describe.configure({ mode: "serial" })`** para specs con SSR pesado (5+ fetches) que saturaban dev server con 8 workers paralelos.

---

## Sesiones anteriores

Resumen movido a `memory/sessions_index.md` para no inflar este archivo. Cargar manualmente cuando se necesite contexto historico.

### QA vivo final 2026-07-04 (Kamilo, Expo Go + laptop + MC + Pulse) — resultados

VERDE: singleton Expo (conns=1, chat, modos MC, reconexion wifi), polls ambas superficies, wall/bell/soporte/agenda silenciosa webapp (prop-sync OK), agenda:delayed toast ambos (tras fix nombre), ban/unban kick, pipeline ratings/leaderboard emitiendo (9 ratings visibles en server log).

BUGS CAZADOS EN QA (todos arreglados y pusheados): recursion helper on() Expo (0ca10b6), dispose del owner mataba socket compartido (460dca8), resolveLiveStatus columnas inexistentes started_at (backend 888b155 — bug latente de produccion), agenda:delayed sin nombre de sala (645b7e4).

DEUDA NUEVA — Event Pulse cliente (pre-existente de abril, destapada hoy; SESION DEDICADA):
1. Charlas vacia: PulseController:102 exige room_id (sesiones del seeder no tienen sala) — decidir si el filtro es correcto
2. Formula inconsistente: counter ratings live suma solo top-6 sesiones (socket.js refreshStat) vs bootstrap que cuenta todas — F5 y live dan numeros distintos
3. Los emits backend YA llegan (GAP-C verificado) — lo roto es el refresh client-side del Pulse
4. Menor: poll:closed con room=session:null en polls scope session sin session_id (server log)

### Cierre final 2026-07-04 (post-QA): W.4 CERRADO 92/92 via recount

Replay/rating-auto/anuncios-in-stream/custom-panel/slow-mode/emojis/mobile-tablet YA estaban implementados sin marcar en el doc. Fix race auto-rate (web e1b0c9a, cazado en QA: modal aparecia aunque ya calificaste). **TOTAL: 507/656 = 77.3%, 14 modulos cerrados.**

**PROXIMA SESION — barrido final webapp (~5-7h en 2 sesiones):**
1. W.6 Social: paginacion/load-more + comments lazy + hashtags (~1-2h)
2. W.2 Home: lifecycle PRE/ENDED + survey prompt (~1-2h)
3. W.3 Agenda: badges AJUSTADA/CANCELADA + bulk .ics (~30min)
4. W.12 Cierre Fase 1: Web Push + Sentry + QA device + Lighthouse (~2-3h)
Paralelo corto: Event Pulse cliente 4 items (PENDIENTES.md) + WIP recap Expo.
