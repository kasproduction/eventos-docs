# CLAUDE.md — eventos-api-dotnet

> Va a la raiz del repo cuando exista (M1.1.1). Reglas completas, excepciones JSON, formas de Laravel y comandos:
> `APP EVENTOS/docs/BUENAS-PRACTICAS-MIGRACION.md`. Decisiones: `docs/decisiones.md`. Plan: `docs/roadmaps/ROADMAP-MIGRACION-DOTNET.md`.
> C# responde lo que respondia PHP donde lo lee la webapp, la app o el kiosko: ese contrato son los snapshots de `eventos-backend/tests/Contratos/`.
> Lo externo sin consumidor (webhooks, exportaciones, API de terceros) y el admin nuevo se arreglan en C# y se anotan (ver "Idéntico o arreglado").
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

## Idéntico o arreglado (Kamilo 2026-10-30)

Antes de copiar de PHP algo que parece un defecto, pregunta quién lo consume (grep en `eventos-web` fuera de `src/app/admin`, `eventos-app`
y `eventos-kiosko`; lo que no confirmes no cuenta como consumido):

- **Lo consume la webapp, la app o el kiosko:** idéntico, defectos incluidos. Cambiarlo lo decide Kamilo.
- **Es externo sin consumidor (webhooks, exportaciones, API de terceros) o es del admin nuevo:** se arregla en C#, con un BUG-NNN "resuelto
  en C#" y una línea en el Anexo B ("sin consumidores externos al momento del cambio").
- **Lo que un tercero verifica o parsea** (firma, cabeceras, orden y escape del JSON, ULID, forma del log de un webhook): siempre idéntico.
- Arreglar lo interno (reintentos, logs que faltaban, avisos al socket que PHP olvidó) no cambia el contrato: se hace y se anota.
- En duda, anótalo como "por decidir" y pregunta; no lo copies por inercia.

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
- Mejorar un contrato que consume la webapp, la app o el kiosko (renombrar campos, quitar duplicados, corregir textos o codigos): si esta mal, lo decide Kamilo y se anota en `decisiones.md` y se cambia en C# (PHP esta congelado).
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
