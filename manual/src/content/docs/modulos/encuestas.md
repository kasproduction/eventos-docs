---
title: Encuestas
description: Preguntarle algo a tu público durante el evento y saber qué le pareció cuando termina.
---

<!-- M2.8.4a (2026-11-06): piloto reescrito sobre el panel de EventOS con el tono pedido por Kamilo (2026-07-19). -->

Con Encuestas le preguntas algo a tu público mientras pasa el evento, y al final sabes qué le pareció. Las armas tú, pregunta por pregunta, y decides cuándo las ve la gente.

## Qué es

Hay tres clases de encuesta, y cada una tiene su momento:

- **Generales.** Las que creas cuando quieras: qué charla esperan más, cómo llegaron, a qué hora prefieren el almuerzo. Las activas cuando estén listas y las cierras cuando ya no necesites respuestas.
- **La post-evento.** La de satisfacción. Hay una sola por evento y se abre sola en el momento en que el evento pasa a Finalizado, así que no tienes que acordarte de nada.
  <!-- fuente: eventos-api-dotnet src/Api/Admin/Eventos/EfectosDelEvento.cs:41 (al pasar a ended activa las post-evento en borrador) -->
- **Las de una charla en vivo.** Esas no se arman aquí: las lanza quien opera la sesión desde Mission Control, mientras la charla está pasando.
  <!-- fuente: eventos-backend public/mission-control/app.js:785 (scope 'session') -->

## Cómo se configura

En el panel de EventOS entras a **En vivo > Encuestas**.

1. Arriba siempre está la post-evento, y debajo tus encuestas generales. De cada una ves si es general o post-evento, en qué estado va (Borrador, Activa o Cerrada), cuántas preguntas tiene y cuántas personas ya respondieron.
   <!-- fuente: eventos-api-dotnet src/Api/Admin/EnVivo/Encuestas.cs:27-40 (post-evento primero; respuestas = personas distintas) -->
2. Para una nueva, toca **Nueva encuesta**, ponle un título y listo: nace en Borrador, así que nadie la ve mientras la armas.
3. Adentro agregas las preguntas con **Nueva pregunta**. Hay tres tipos:
   - **Opción múltiple**, con al menos dos opciones. Si quieres que la gente pueda marcar varias, activa "Se pueden elegir varias".
   - **Texto libre**, para que escriban lo que quieran.
   - **Estrellas de 1 a 5**.
   <!-- fuente: eventos-api-dotnet src/Api/Admin/EnVivo/PreguntasDeEncuesta.cs:24 (tipos) y :240 (al menos 2 opciones) -->
   Con **Ordenar** cambias el orden de las preguntas.
4. Cuando esté lista, toca **Activar**. Desde ese momento la gente puede responderla, y a quien tenga la app o la webapp abierta le aparece sin tener que refrescar.
   <!-- fuente: eventos-api-dotnet src/Api/Admin/EnVivo/Encuestas.cs:141 (activar avisa poll:new) -->
5. Cuando ya no quieras más respuestas, toca **Cerrar**. Lo que ya respondieron se queda guardado.
   <!-- fuente: eventos-api-dotnet src/Api/Admin/EnVivo/Encuestas.cs:143 -->

La post-evento ya viene con tres preguntas de base que puedes cambiar a tu gusto. Si por alguna razón tu evento no la tiene, la lista te lo avisa y te deja crearla con un botón.
<!-- fuente: eventos-api-dotnet src/Api/Admin/EnVivo/Encuestas.cs:17-18 (la lista no la crea sola; POST post-event con las 3 preguntas base) -->

**Para que la gente llegue a una encuesta**, mándale un anuncio: en **Comunicación > Anuncios**, en "Lleva a", elige **Encuestas** y, si quieres, cuál encuesta en particular. Quien toque el anuncio cae directo en ella.
<!-- fuente: eventos-api-dotnet src/Api/Admin/Comunicacion/Anuncios.cs:267-270 (destino encuestas con id opcional) -->

## Qué ve el asistente

- Encuestas **no tiene un ícono fijo** en el menú de la app ni de la webapp. La gente llega por el anuncio que mandas o, cuando el evento ya terminó, por la tarjeta del inicio que la invita a calificarlo.
  <!-- fuente: eventos-api-dotnet src/Api/Admin/Identidad/CatalogoDeModulos.cs:32 (app y web: "aviso"); eventos-web src/components/app/home/EventArchive.tsx:148 -->
- Al entrar ve las encuestas activas. Si hay una sola, se abre de una; si hay varias, escoge cuál responder. Las cerradas aparecen como informativas.
  <!-- fuente: eventos-web src/components/app/encuestas/SurveysView.tsx:24-30 -->
- Las de una charla en vivo aparecen dentro de la pantalla de la transmisión, al lado del video, no en Encuestas.
- La ven los asistentes y los vendedores de los stands.
  <!-- fuente: eventos-api-dotnet src/Api/Admin/Identidad/CatalogoDeModulos.cs:32 (roles attendee y vendedor) -->

## Lo que se puede y lo que no

**Se puede**

- Tener todas las encuestas generales que quieras, cada una con su propio ciclo: Borrador, Activa, Cerrada.
- Cambiar el texto de las preguntas y las opciones cuando quieras. Si la encuesta está activa, el cambio les llega al momento a quienes la tienen abierta.
  <!-- fuente: eventos-api-dotnet src/Api/Admin/EnVivo/PreguntasDeEncuesta.cs:11 (poll:updated) -->
- Activar la post-evento antes de tiempo, si quieres empezar a recoger opiniones antes del cierre.

**No se puede**

- **Borrar una encuesta.** Solo se cierra: las respuestas son datos de tu evento y se conservan.
  <!-- fuente: eventos-api-dotnet src/Api/Admin/EnVivo/Encuestas.cs:13 (no se borran, solo se cierran) -->
- **Volver a abrir una encuesta cerrada.** Cerrar es definitivo; si necesitas más respuestas, crea otra.
  <!-- fuente: eventos-api-dotnet src/Api/Admin/EnVivo/Encuestas.cs:141-153 (activar solo desde Borrador) -->
- **Cambiar el tipo de una pregunta que ya tiene respuestas**, ni quitar una opción que ya alguien escogió. El panel te dice cuántas respuestas tiene para que sepas por qué.
  <!-- fuente: eventos-api-dotnet src/Api/Admin/EnVivo/PreguntasDeEncuesta.cs:73 y :105 -->
- Tener más de una post-evento por evento.
- Ver aquí el detalle de los resultados. Eso está en el **Data Center**, donde también los descargas.

## Preguntas frecuentes

- **Activé una encuesta y nadie la responde.** Lo más seguro es que nadie sepa que existe: como no tiene ícono en el menú, mándales un anuncio que lleve a ella.
- **¿Tengo que activar la post-evento?** No. Se abre sola cuando pasas el evento a Finalizado. Actívala a mano solo si quieres adelantarla.
- **Borré una pregunta por error.** Al borrarla se van también sus respuestas, y eso no se puede deshacer. Por eso el panel te pregunta antes y te dice cuántas respuestas se pierden.
  <!-- fuente: eventos-web messages/es.json admin.encuestas.eliminarConVotos -->
- **Las respuestas de texto libre de una charla en vivo no salen en pantalla.** Pasan primero por moderación: quien opera Mission Control decide cuáles se muestran.
  <!-- fuente: eventos-backend app/Http/Controllers/Api/V1/PollController.php:220 (texto libre nace sin aprobar) -->
