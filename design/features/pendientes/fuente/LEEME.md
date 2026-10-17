# Tablero de pendientes (artifact privado)

Página: https://claude.ai/artifact/UwSGdGXbJCHTeXEb7UpBDL (Lux, C1 lista lateral, lenguaje simple). Decisión de Kamilo 2026-10-16.

Cadena para regenerarla desde `docs/living/PENDIENTES.md` (los scripts usan la ruta del scratchpad de la sesión
2026-10-16; al reusarlos, cambiar `SP`/`P` por esta carpeta):

1. `extraer.py` saca las tareas de PENDIENTES.md (id = sha1 de sección + texto, 12 caracteres).
2. `descripciones.py` completa descripciones cortas; `modulos.py` asigna módulo; `pulir.py` limpia títulos y tildes.
3. `deps.py` agrega dependencias entre tareas y alertas por módulo.
4. Textos en lenguaje simple: `simples.json` (15 aprobados por Kamilo) + `reescribir/g*-salida.json` (200 restantes).
5. `sugerir.py` pone mi propuesta de prioridad, orden y motivo (`sugerencias.json`); la página la muestra como
   "Sugerida por Claude" hasta que Kamilo mueve o confirma la tarea (campo `sug` en la base).
6. `build_final.py` arma `pagina-publicada.html` a partir de `lab-c.tpl.html`.

Prioridades, orden y notas viven en la base del artifact, colección `prioridades` (doc id = id de la tarea; campos
`p` ya|antes|despues|descartar, `o` orden dentro de la celda, `nota`, `at`). Si cambia el texto de una tarea en
PENDIENTES.md cambia su id: antes de regenerar, pasar las prioridades a PENDIENTES.md.
