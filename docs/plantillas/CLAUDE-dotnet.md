# CLAUDE.md — eventos-api-dotnet

> Va a la raiz del repo cuando exista (M1.1.1). Reglas completas, excepciones JSON, formas de Laravel y comandos:
> `APP EVENTOS/docs/BUENAS-PRACTICAS-MIGRACION.md`. Decisiones: `docs/decisiones.md`. Plan: `docs/roadmaps/ROADMAP-MIGRACION-DOTNET.md`.
> C# responde exactamente lo que respondia PHP: el contrato son los snapshots de `eventos-backend/tests/Contratos/`.
> `Api`, `Worker`, `Hub`, `Contracts`, `Tests` son procesos que se despliegan, no capas; dentro de cada uno, por modulo.

## Portar un endpoint (en este orden)

1. **Leer el snapshot** de la ruta (`tests/.pest/snapshots/Contratos/`): estado, cabeceras, cuerpo, errores.
2. **Leer el PHP y sus comentarios largos** para entender reglas y efectos (cache, socket, jobs), no para traducir linea a linea.
3. **Escribir en el modulo:** endpoint fino, servicio con nombre, DTO con `Select` + `AsNoTracking()`, efectos en `XxxEffects`.
4. **`ORDER BY` explicito** en toda lista, igual al de PHP (si PHP no lo tiene, se agrega primero en PHP).
5. **Correr el snapshot contra C#** (`CONTRATO_BASE_URL`); si escribe, prueba cruzada: escribe C#, lee PHP.
6. **Medir en el MSI** (k6 de agosto, PHP contra C#) y anotar el numero en el roadmap. Recien ahi se cruza.

## Prohibido

- `.Result` y `.Wait()`.
- `DateTime.Now`: fechas con `DateTimeOffset` en el offset de la zona de la app, sin fracciones.
- Abstracciones nuevas: repositorios, unit of work, mediadores, capas, interfaces de una sola implementacion.
- Mejorar el contrato: renombrar campos, quitar duplicados, corregir textos o codigos. Si esta mal, lo decide Kamilo y
  se cambia primero en PHP.
- Migraciones de EF Core antes de la fase 5 (el esquema lo manda Laravel).
- Tocar Next o Expo fuera de la capa `realtime` (fase 3) y del admin nuevo (fase 2).
- Sobreescribir la politica JSON en un endpoint, o sumar una excepcion sin decision en `decisiones.md`.
- Leer claves de Redis de PHP (solo borrarlas por nombre).
- "Aprovecho y tambien": un bug se registra y se arregla solo si bloquea o si Kamilo lo decide.
