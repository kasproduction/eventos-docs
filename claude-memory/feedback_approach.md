---
name: EventOS — preferencias de trabajo con Claude
description: Cómo el usuario quiere que Claude trabaje en este proyecto
type: feedback
---

## No sobreescribir memorias como log

Tratar las memorias como un log acumulativo. Siempre crear archivos nuevos o
agregar al final — nunca sobreescribir información existente que sigue siendo válida.

**Why:** El usuario quiere poder reconstruir el contexto completo del proyecto
leyendo las memorias. Si se sobreescriben, se pierde la historia de decisiones.

**How to apply:** Antes de editar un archivo de memoria, evaluar si la información
antigua sigue siendo válida. Si sí → agregar al final o crear sección nueva.
Si no → marcar como "supersedido" con fecha antes de reemplazar.

---

## Cubrir todos los escenarios antes de codear

Antes de escribir código, asegurarse de que el documento maestro contempla
todos los casos de uso, errores, roles y flujos posibles.

**Why:** El usuario quiere evitar reescribir código por haber olvidado escenarios.
La planificación es la fase más importante del proyecto.

**How to apply:** Si durante una sesión de código aparece un escenario no contemplado
en el documento maestro, pausar y actualizar el documento antes de codear.

---

## Dependencias progresivas

No instalar todas las librerías al inicio del proyecto. Instalar solo lo que
necesita cada sesión.

**Why:** Reduce variables al debuggear. Cada sesión es autocontenida.

**How to apply:** Al inicio de cada sesión, consultar project_dependencies.md
e instalar solo lo listado para esa sesión.

---

## Git: confirmar antes de commit

Después de implementar y verificar que algo funciona, preguntar:
"¿Confirmas que funciona? Lo hago commit."

**Why:** Proyecto con muchos módulos — sin commits frecuentes es difícil hacer rollback.

**How to apply:** Nunca hacer commit sin confirmación explícita del usuario.
Commits granulares por hito (no un commit gigante por sesión).
