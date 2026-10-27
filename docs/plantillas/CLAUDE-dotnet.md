# CLAUDE.md — eventos-api-dotnet

> Va a la raiz del repo cuando exista (M1.1.1). Reglas completas, excepciones JSON, formas de Laravel y comandos:
> `APP EVENTOS/docs/BUENAS-PRACTICAS-MIGRACION.md`. Decisiones: `docs/decisiones.md`. Plan: `docs/roadmaps/ROADMAP-MIGRACION-DOTNET.md`.
> C# responde exactamente lo que respondia PHP: el contrato son los snapshots de `eventos-backend/tests/Contratos/`.
> `Api`, `Worker`, `Hub`, `Contracts`, `Tests` son procesos que se despliegan, no capas; dentro de cada uno, por modulo.


## PHP congelado (regla 6 del roadmap, reforzada por Kamilo 2026-10-27)

**No se escribe ni una linea mas de PHP, ni puentes, ni jobs, ni excepciones.** Lo que C# necesite de PHP se adelanta
a C#, aunque sea de la fase 4 (un job, un envio de push, un correo). Las dos excepciones ya hechas, el outbox de correos
(M2.2.5) y el rol organizador (M2.2.6), son las ultimas; el outbox se reemplaza en cuanto C# mande correo, no en la 4c.
PHP solo sigue sirviendo lo que todavia no se ha portado. Cuando un trabajo pasa a C#, sale de PHP en el mismo
despliegue (regla 4).

Las pruebas si se siguen escribiendo en PHP (pruebas cruzadas, escenarios de `ContratoSeeder`): son la forma de
comprobar que PHP lee lo que C# escribe. El producto nunca. Un `ORDER BY` que falte en PHP ya no se agrega alla: lo
define C# y se anota como diferencia en `decisiones.md` (Kamilo 2026-10-27).

## Portar un endpoint (en este orden)

1. **Leer el snapshot** de la ruta (`tests/.pest/snapshots/Contratos/`): estado, cabeceras, cuerpo, errores.
2. **Leer el PHP y sus comentarios largos** para entender reglas y efectos (cache, socket, jobs), no para traducir linea a linea.
3. **Escribir en el modulo:** endpoint fino, servicio con nombre, DTO con `Select` + `AsNoTracking()`, efectos en `XxxEffects`.
4. **`ORDER BY` explicito** en toda lista, igual al de PHP (si PHP no lo tiene, lo define C# y se anota como diferencia: PHP no se toca).
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
- Leer o escribir algo de un evento sin el filtro por evento (regla 26): el evento sale de la ruta, la membresia se
  exige en la ruta y un id de otro evento es 404.
- Un endpoint de la API admin nueva sin descripcion en su OpenAPI (regla 28).
- Documentar a mano lo que se puede generar desde el codigo (regla 28): rutas, formas y dueños salen del OpenAPI, los
  snapshots y `contratos/rutas-duenos.md`.
- "Aprovecho y tambien": un bug se registra y se arregla solo si bloquea o si Kamilo lo decide.
