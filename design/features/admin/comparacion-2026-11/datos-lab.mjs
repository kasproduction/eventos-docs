// Datos del lab "Panel nuevo contra Filament". Pasada de medicion del 2026-11-24 y corrida final del 2026-11-25 (fichas.json,
// despues del bloque de arreglos web fafde62). Las 7 decisiones de Kamilo del 2026-11-24 (decisiones.md) cuentan como decision;
// lo arreglado sale de las secciones y queda en ARREGLADO; los "por confirmar" quedaron resueltos (ninguno perdio un campo).
// clase: defecto | falta | decision. prueba: datos (contratos y cruzadas), uso (e2e-admin), vista (comparada contra Filament).

export const TRANSVERSAL = [
  ["falta", "Notificaciones", "Filament tiene la campana con la bandeja de avisos del panel (databaseNotifications, cada 30 s). El nuevo no tiene ni campana ni bandeja.", "Todo el panel"],
  ["falta", "Buscador global", "Filament busca en todo el panel desde la barra de arriba. El nuevo no.", "Todo el panel"],
  ["falta", "Subir archivos arrastrando", "En Filament cada imagen se sube con un recuadro de arrastrar y soltar al lado de la URL. En el nuevo hay un botón de elegir archivo, sin arrastrar.", "Speakers, Sponsors, Anuncios, Slides, Destacados, Preguntas, Premios (7 formularios)"],
  ["falta", "Íconos en las pestañas", "Las pestañas de Filament llevan ícono; las del nuevo no.", "Agenda, Asistentes, Entrada, Anuncios, Emails, Identidad, Social, Gamificación, Webhooks"],
  ["defecto", "Encabezados de las columnas que ordenan", "Los encabezados ya van en mayúsculas como Filament, menos los de las columnas que ordenan la lista (BUG-573).", "Agenda (Título, Inicio, Fin), Asistentes (Nombre, Registro), Staff (Nombre, Correo)"],
  ["defecto", "Paginación del historial de push", "Todas las listas paginan como Filament (5, 10, 25, 50 o Todos), menos la tabla de notificaciones push del historial de Emails, que muestra todo de una vez (BUG-574).", "Emails, Historial"],
  ["decision", "Miniaturas del mismo tamaño", "Todas las miniaturas de las listas en 64 x 54; Filament usa un tamaño distinto en cada lista (Kamilo 2026-11-24).", "Slides, Destacados, Premios, Fotos, Sponsors"],
  ["decision", "Textos en español con tildes", "Filament dice Titulo, Email, Seccion, Telefono; el nuevo Título, Correo, Sección, Teléfono. Regla de Kamilo: español de Colombia bien escrito.", "Todo el panel"],
  ["decision", "Sin el punto medio como separador", "Filament separa cargo y empresa con un punto medio; el nuevo con coma. Regla de interfaz de Kamilo.", "Speakers y otras listas"],
  ["decision", "Ver contra Editar", "En Filament las filas dicen Ver porque quedó de solo lectura (M2.7.1); el nuevo edita. No cuenta como diferencia.", "Todas las listas"],
];

// Lo que la pasada encontro y la corrida final ya no encuentra (web fafde62, ed2f60b, 9ea6702).
export const ARREGLADO = [
  "Páginas y las plantillas de correo ya abren: antes daban error 500 (BUG-571).",
  "Las 19 listas que mostraban todo ahora paginan como Filament, con la opción Todos.",
  "Encabezados de tabla en mayúsculas pequeñas, sin fondo, como Filament (salvo los que ordenan, BUG-573).",
  "Miniatura de la lista de Fotos de 64 x 54 (salía aplastada a 25 x 60, BUG-572) y fotos de las listas sin aplastar (BUG-570).",
  "Columnas que faltaban: Golden Tickets (correo, motivo, reclamado), Ganados (juego, reclamado el, confirmado por), Soporte (correo de quien pide) y Webhooks (URL).",
  "El menú como Filament: íconos, grupos plegables, medidas, tonos y animaciones (BUG-568).",
];

// Lo que la pasada dio por diferencia y era un error de medicion.
export const ERRORES_DE_LA_PASADA = [
  "Límites: «Leads por día» sí existía (la ficha lo muestra en la pasada y en la corrida final).",
  "Premios: «Quién puede canjear» sí existía, como las casillas Asistente y Vendedor.",
  "Las columnas de Golden Tickets, Ganados, Soporte y Webhooks salían como notas debajo de otra columna; igual se sacaron aparte.",
];

// [seccion, pantallas medidas, diferencias [[clase, texto]], pruebas {datos, uso, vista}]
const V = "Comparada (corrida final)";
export const SECCIONES = [
  ["Escritorio", "1", [["falta", "Filament tiene 4 cifras (registrados, ya llegaron, sesiones hoy, requieren acción) y los accesos de «¿Qué quieres hacer?». El nuevo, una frase."]], { datos: "-", uso: "-", vista: V }],
  ["Tu cuenta (seguridad)", "1", [], { datos: "TuCuenta, SeguridadDeTuCuenta", uso: "seguridad-de-tu-cuenta", vista: V }],
  ["Agenda (Sesiones, Tracks, Tipos)", "9", [["defecto", "La acción Borrar de cada sesión se corta por la derecha a 1440 px (BUG-575; ya pasaba en la pasada)."]], { datos: "Sesiones, TracksYTipos", uso: "agenda, sesiones", vista: V }],
  ["Speakers", "3", [["decision", "Orden por nombre; Filament por creación (Kamilo 2026-11-24)."], ["decision", "La ficha pone las sesiones en una columna al lado (Kamilo 2026-11-24)."], ["falta", "Subir la foto arrastrando."]], { datos: "Speakers", uso: "speakers", vista: V }],
  ["Sponsors", "3", [["decision", "Sin la pestaña Sponsors arriba (Kamilo 2026-11-24)."], ["falta", "Subir logo y banner arrastrando."]], { datos: "Sponsors", uso: "sponsors", vista: V }],
  ["Documentos", "3", [], { datos: "Documentos", uso: "documentos", vista: V }],
  ["Páginas", "3", [], { datos: "Paginas, HtmlDePaginas", uso: "paginas, editor-en-el-servidor", vista: V }],
  ["Asistentes", "3", [["falta", "La lista de Bloqueos (pestaña de Filament) no tiene pantalla; banear se hace desde la ficha."], ["decision", "Foto de la ficha de 40 px; en Filament 56 px (Kamilo 2026-11-24)."], ["decision", "Regenerar recap sigue en Filament hasta M4e.5 (mapa de pantallas)."], ["decision", "«Qué le pasó» es pantalla nueva (M2.9.4a) y cubre el historial de roles y baneos."]], { datos: "Asistentes, PerfilDeAsistente, Baneos, ImportacionDeAsistentes, QueLePaso", uso: "asistentes, que-le-paso", vista: V }],
  ["Entrada", "7", [["decision", "La restricción por email o dominio y la verificación por CSV van dentro de «Modo de registro» (Kamilo 2026-11-24)."], ["decision", "Códigos de acceso tiene «Nuevo» propio (generar en lote, M2.4.5e)."]], { datos: "EntradaDelEvento, CamposDelFormulario, CodigosDeAcceso", uso: "entrada, entrada-del-evento, campos, codigos", vista: V }],
  ["Anuncios (Anuncios, Push, Recordatorios)", "6", [["falta", "Sin buscador en Anuncios y en Push programadas; Filament lo tiene."], ["falta", "Imagen del anuncio sin arrastrar."]], { datos: "Anuncios, PushProgramadas, AjustesDelEvento", uso: "anuncios, push-programadas, ajustes-del-evento", vista: V }],
  ["Emails", "3 + 3 a mano", [["defecto", "El historial no pagina las notificaciones push (BUG-574)."], ["decision", "El historial con los push, una fila por envío (M2.5.4)."], ["falta", "Sin buscador en la lista de plantillas."]], { datos: "PlantillasDeCorreo, EnvioDeLaOrganizacion, HistorialDeEnvios", uso: "emails, editor-en-el-servidor", vista: V }],
  ["Identidad (Branding, Slides, Destacados, Preguntas, Recap)", "13", [["falta", "Subir imágenes arrastrando en Slides, Destacados y Preguntas."], ["decision", "Escarapela y Onboarding son pantallas propias (M2.8.2 y la pestaña Onboarding app)."]], { datos: "Identidad, Branding, Onboarding, Escarapela, Recap", uso: "identidad, branding, onboarding, escarapela, recap", vista: V }],
  ["Módulos", "1", [], { datos: "Modulos", uso: "modulos", vista: V }],
  ["Social (Muro, Fotos, Ajustes)", "5", [["decision", "Foto del detalle más grande y con esquinas (Kamilo 2026-11-24)."]], { datos: "Muro, Fotos, PremiosDeFotos", uso: "social, listas-como-filament", vista: V }],
  ["Gamificación (Ajustes, Golden Tickets, Premios, Ganados)", "7", [["falta", "Imagen del premio sin arrastrar."]], { datos: "Gamificacion, GoldenTickets, CatalogoDePremios", uso: "gamificacion, golden-tickets, premios, premios-ganados", vista: V }],
  ["Salones", "2", [], { datos: "Salones", uso: "salones", vista: V }],
  ["Chat", "1", [], { datos: "AjustesDelEvento", uso: "ajustes-del-evento", vista: V }],
  ["Event Pulse", "1", [], { datos: "EventPulse", uso: "event-pulse", vista: V }],
  ["Soporte", "2", [], { datos: "Soporte", uso: "soporte", vista: V }],
  ["Encuestas", "2", [["falta", "Sin buscador en la lista."]], { datos: "Encuestas, PreguntasDeEncuesta", uso: "encuestas", vista: V }],
  ["Eventos", "2", [], { datos: "EventoNuevo", uso: "eventos", vista: V }],
  ["Staff", "3", [], { datos: "Staff, AccesoDelStaff", uso: "staff", vista: V }],
  ["Límites de uso", "1", [], { datos: "AjustesDelEvento, Limitador", uso: "ajustes-del-evento", vista: V }],
  ["Webhooks (Webhooks, Llaves, Historial)", "6", [], { datos: "Webhooks, LlavesDeEntrada, HistorialDeWebhooks", uso: "webhooks", vista: V }],
  ["Torre de control y Ayuda", "2", [["decision", "Pantallas nuevas sin par en Filament (M2.9.1 y M2.8.4)."]], { datos: "-", uso: "torre, ayuda", vista: V }],
  ["Menú y barra de arriba (todo el panel)", "-", [], { datos: "-", uso: "marco", vista: "Sí (marco.spec.ts)" }],
];
