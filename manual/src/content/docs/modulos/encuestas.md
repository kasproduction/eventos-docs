---
title: Encuestas
description: Encuestas en vivo por sesión, generales del evento y la encuesta de satisfacción post-evento.
---

Pregúntale a tu audiencia: encuestas en vivo durante una sesión,
encuestas generales en cualquier momento del evento, y la encuesta de
satisfacción que se activa sola cuando el evento termina.

## Qué es

El módulo de Encuestas cubre tres momentos distintos:

- **En vivo, por sesión**: se lanzan desde Mission Control mientras la
  sesión transcurre y los resultados se ven al instante.
  <!-- fuente: eventos-backend mission-control app.js — scope:'session' hardcoded -->
- **Generales del evento**: viven en el módulo Encuestas de la app y la
  webapp, activas el tiempo que decidas (pulso de expectativa, logística,
  votaciones).
- **Post-evento**: la encuesta de satisfacción. Existe una por evento,
  viene creada de fábrica, y **se activa sola** cuando el evento pasa a
  Finalizado. <!-- fuente: eventos-backend app/Observers/EventObserver.php:24-30 -->

## Cómo se configura (admin)

**Encuestas** en el menú del admin (`/admin/encuestas`):

1. La lista muestra la **post-evento siempre de primera** (con su
   distintivo) y las generales debajo, con columnas de título, tipo,
   estado, preguntas y respuestas (personas distintas).
2. **Nueva encuesta**: botón que pide solo el título — nace como borrador
   vacío y entras directo al constructor.
3. **El constructor**: tabla de preguntas ordenable (arrastra para
   reordenar) + modal por pregunta. Tres tipos de pregunta:
   **opción múltiple** (con opciones arrastrables y toggle de
   multi-respuesta), **texto abierto** y **calificación de estrellas**.
   <!-- fuente: eventos-backend app/Http/Controllers/Api/V1/PollController.php:33 in:multiple_choice,open_text,star_rating -->
   Todo persiste al instante; borrar una pregunta con respuestas te
   advierte cuántas se pierden.
4. **Activar / Cerrar por encuesta**, desde la fila. La post-evento
   conserva su nota: "se activa sola al Finalizado" (también puedes
   activarla antes a mano).
5. La post-evento viene sembrada con 3 preguntas base (estrellas +
   opción múltiple + texto libre) que puedes editar o reemplazar.

Las encuestas **de sesión no se crean aquí**: se lanzan desde Mission
Control durante la sesión (botón Mission Control en cada sesión de la
Agenda).

## Qué ve el asistente

- **Webapp** (`/encuestas`): un deck por slides — cada encuesta activa se
  responde pregunta a pregunta, con voto que se registra al toque y
  cierre de agradecimiento. Lista las generales y la post-evento cuando
  están activas. <!-- fuente: eventos-web src/components/app/encuestas/SurveyDeck.tsx; backend PollController.php:257 whereIn scope event,post_event -->
- **App móvil**: módulo Encuestas en el grid del home, mismo contenido.
  <!-- fuente: ModuleCatalog.php:65-73 app:grid, web:rail -->
- **En vivo**: las encuestas de sesión aparecen dentro de la pantalla del
  streaming (columna interactiva), no en el módulo Encuestas.
- Lo ven **asistentes y vendedores**. <!-- fuente: ModuleCatalog.php:70 roles -->

## Lo que se puede y lo que NO

**Se puede**

- Tantas encuestas generales como quieras, cada una con su ciclo propio
  de borrador → activa → cerrada.
- Editar preguntas y opciones en cualquier momento (los cambios son
  inmediatos).
- Apagar el módulo completo desde el Panel de Módulos: desaparece de la
  navegación de la app y la webapp al instante.

**NO se puede**

- **Eliminar encuestas** — solo cerrarlas. Las respuestas son datos del
  evento y se conservan. <!-- fuente: decisión Kamilo 2026-07-19, INT.11b -->
- Crear tipos de pregunta nuevos: los tres tipos son fijos.
- Tener más de una encuesta post-evento.
- Exportar resultados desde el admin: la reportería vive en el **Data
  Center** (dataset de encuestas post-evento y votos).
  <!-- fuente: decisión INT.11 — Exportar CSV fuera del admin -->

## Gotchas y preguntas frecuentes

- **"Activé la post-evento pero nadie la ve"** — la ven cuando entra al
  módulo Encuestas; además se activa sola al Finalizado, no necesitas
  activarla a mano salvo que quieras adelantarla.
- **Las respuestas de texto abierto en sesiones en vivo nacen sin
  aprobar**: pasan por moderación antes de mostrarse en pantalla.
  <!-- fuente: PollController.php:213 is_approved = type !== open_text -->
- **¿Dónde veo los resultados en vivo de una sesión?** En Mission Control
  mientras la sesión corre; después, en el Data Center.
