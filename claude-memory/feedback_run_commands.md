---
name: Ejecutar comandos directamente sin pedirle al usuario
description: El usuario prefiere que Claude ejecute los comandos bash/composer/artisan directamente en vez de pedirle que los corra
type: feedback
---

Ejecutar comandos directamente usando la herramienta Bash en vez de pedirle al usuario que los corra en su terminal.

**Why:** El usuario lo pidió explícitamente ("puedes hacerlo tú?") al ver que le daba comandos para copiar y pegar.

**How to apply:** Siempre que se pueda correr un comando con la herramienta Bash, hacerlo directamente. Solo pedir al usuario que corra algo cuando requiere interacción humana (login, confirmación en browser, permisos de admin en Windows, etc.).
