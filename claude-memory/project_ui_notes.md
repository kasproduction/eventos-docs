---
name: EventOS — referencias de diseño UI
description: Dirección visual definida para la app Expo — dark mode, cards, accent color del evento
type: project
---

Referencias guardadas en `C:\laragon\www\APP EVENTOS\design\` (3 imágenes webp).

## Dirección visual acordada

- **Fondo:** dark mode (#0D0D0D o similar) como base — ya planeado desde Fase 1
- **Accent:** `primary_color` del evento — configurable por admin en Filament, se aplica dinámicamente
- **Tipografía:** grande y bold en headers, limpia en body
- **Cards:** redondeadas (rounded-2xl), sombra sutil, colores vibrantes por categoría/track en agenda
- **Bottom tab:** minimalista, íconos simples
- **Estilo:** corporativo-moderno — referencia Fever (dark + accent amarillo/neón) + cards agenda de apps de clases

## Cuándo aplicar

Sesión UI independiente — **después de que Fase 1 esté funcional completa**, antes de lanzar con cliente real.
No bloquea el desarrollo de Fase 1. Las pantallas actuales son funcionales pero sin pulido visual final.

## Why

"Todo entra por los ojos" — el usuario quiere un nivel visual sólido antes de presentar a clientes reales, pero sin bloquearse en diseño durante el MVP.

## How to apply

En Fase 1 hacer UI funcional y presentable (no rota, spacing correcto, colores consistentes).
El rediseño profundo va en la sesión UI dedicada post-Fase 1.

## Tareas UI pendientes (acumuladas)

### Display proyectable `/display/polls/{id}`
- Actualmente: lista con scroll, todas las preguntas apiladas
- Mejorar a: vista tipo **slides** — una pregunta ocupa toda la pantalla, transición animada
- Full-screen, sin scroll, resultados en grande, con animación de barras al actualizar
- Auto-avance entre preguntas o control manual desde el admin
- Archivo: `resources/views/display/poll.blade.php`
