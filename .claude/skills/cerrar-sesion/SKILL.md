---
name: cerrar-sesion
description: Cierra la sesion actualizando docs/NEXT-SESSION.md con lo que se hizo + commits + proxima tarea. Usar cuando el usuario diga "cerrar sesion", "cerremos", "hasta aqui", "fin del dia", "guardar", o cuando este claramente terminando el trabajo del dia. NO confundir con "guardar DaVinci" que ademas hace push y actualiza memoria.
user-invocable: true
allowed-tools:
  - Read
  - Edit
  - Write
  - Bash(git status)
  - Bash(git log *)
  - Bash(git add *)
  - Bash(git commit *)
---

# /cerrar-sesion — Cierre de sesion productiva

Actualiza el punto de entrada unico (`docs/NEXT-SESSION.md`) para que la
proxima sesion arranque limpia. Ejecutar al final de cada bloque de
trabajo significativo.

## Procedimiento

### 1. Snapshot del estado

Verificar que se hizo en la sesion:

```bash
git status                     # cambios staged/unstaged en este repo
cd "C:/laragon/www/eventos-web" && git status   # idem otros repos relevantes
git log --oneline -10           # commits recientes para extraer hashes
```

### 2. Confirmar con el usuario que hay que commitear

NO hago commits sin que el usuario diga explicito "commit" / "guardar"
en la conversacion. Si veo cambios sin commitear y no me lo pidieron,
los listo y pregunto:

> "Veo X cambios sin commitear. ¿Los committeo o los dejas en working
> dir?"

### 3. Si commit aprobado: stagear archivos relevantes

NO usar `git add -A` ni `git add .` (puede arrastrar settings.local.json,
secrets, screenshots viejos, etc). Stagear especifico:

```bash
git add path/to/files-de-esta-sesion
```

Mensaje convencional siguiendo el log existente:
```
feat(W.X): titulo corto
docs(W.X): titulo corto
fix(W.X): titulo corto
```

Body con bullets de cambios concretos, terminando con
`Co-Authored-By: Claude Opus 4.7 <noreply@anthropic.com>`.

### 4. Actualizar `docs/NEXT-SESSION.md`

Editar el archivo (NO reescribir entero — hacer Edits puntuales):

- **Fecha** de "Ultima sesion": cambiar a hoy
- **Que se hizo:** 3-5 bullets nuevos (lo de hoy)
- **Commits:** agregar los hashes nuevos. Marcar "no pusheado" o
  "pusheado a remote" segun corresponda
- **Proxima sesion → Tarea principal:** si la tarea actual quedo
  cerrada, cambiar a la siguiente del backlog. Si quedo a medias,
  mantenerla con sub-bullets de "lo que falta de esta tarea"
- **Pendientes paralelos:** marcar lo que se cumplio con (✅ aplicado).
  Agregar pendientes nuevos detectados durante la sesion
- **Decisiones cerradas:** agregar las nuevas si aplica (no preguntar
  proxima sesion)

### 5. Confirmar al usuario

Resumen final:
> "Sesion cerrada. Commits: [hashes]. Proxima tarea: [X]. NEXT-SESSION.md
> actualizado."

## Reglas duras

- **NUNCA push automatico.** El usuario lo pide explicito si quiere.
  Mencionar en el resumen "no pusheado" si hay commits sin remote.
- **NO actualizar NEXT-SESSION.md sin commits.** El archivo refleja el
  estado COMMITEADO. Si hay cambios sin commitear, los menciono al
  usuario antes de tocar el doc.
- **NO inventar pendientes.** Solo agrego items que salieron en la
  conversacion. No hago listas especulativas.
- **NO me adelanto al usuario.** Si dice "guardar" pero no aclaro
  push, no pusheo. Si dice "commit", commit pero NO push.

## Diferencia con "guardar DaVinci" (memoria feedback_guardar_workflow)

`/cerrar-sesion` es el **cierre tecnico minimo:** commits + actualizar
NEXT-SESSION.md.

`Guardar DaVinci` (workflow de 11 pasos en memoria) es mas amplio:
incluye memoria, roadmap, push, etc. Si el usuario dice "Guardar
DaVinci", seguir ese workflow. Si dice "cerremos" / "guardar" sin el
"DaVinci" — usar este skill.
