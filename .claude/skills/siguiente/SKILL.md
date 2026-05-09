---
name: siguiente
description: Resume el trabajo de la sesion anterior leyendo docs/NEXT-SESSION.md. Usar cuando el usuario diga "siguiente", "next", "que sigue", "continuemos", "arrancamos", "recuerda el contexto", o al inicio de una sesion nueva donde el usuario quiere retomar trabajo previo.
user-invocable: true
allowed-tools:
  - Read
  - Bash(git status)
  - Bash(git log *)
---

# /siguiente — Resume de sesion anterior

Punto de entrada unico al arrancar una sesion nueva. Reemplaza tener que
recordar multiples contextos (memoria, docs, roadmaps).

## Procedimiento

1. **Leer `docs/NEXT-SESSION.md`** del repo APP EVENTOS:
   ```
   C:\laragon\www\APP EVENTOS\docs\NEXT-SESSION.md
   ```
   Yo (Claude) lo mantengo actualizado al cierre de cada sesion.

2. **Verificar estado git** del working dir actual + repos relevantes
   (eventos-web suele estar adelantado por commits del dia anterior):
   ```bash
   git status
   git log --oneline -5
   ```

3. **Resumir al usuario en 3-5 bullets:**
   - Que se hizo la ultima sesion
   - Commits pendientes de push (si aplica)
   - Que sigue ahora (proxima tarea concreta)
   - Decisiones cerradas relevantes (no las re-pregunto)

4. **Preguntar luz verde** antes de modificar archivos:
   > "¿Arranco con [proxima tarea]?"

   Si el usuario dice si, voy. Si dice otra cosa (ej: "antes quiero ver X"),
   pivoto.

## Reglas duras

- **NO modifico nada** antes de confirmar con el usuario.
- **NO repito** decisiones cerradas que ya estan en NEXT-SESSION.md
  como "preguntar de nuevo".
- Si NEXT-SESSION.md no existe o esta vacio (primera vez del proyecto),
  decirle al usuario y construirlo conmigo en esta sesion.
- Si el archivo esta desactualizado (fecha vieja vs hoy), igual lo uso
  como punto de partida pero pregunto que cambio.

## Que NO hacer

- No leer toda la memoria al arranque — el indice (MEMORY.md) ya se
  carga automatico, y NEXT-SESSION.md tiene lo critico.
- No abrir un monton de archivos exploratorios "por si acaso". Si el
  archivo dice "siguiente paso es X", confio y arranco con X.
- No hacer commits ni push automaticos al arrancar. Eso es el cierre.
