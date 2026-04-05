---
name: Git workflow preference
description: User wants to use git commits after each verified working feature/fix, with confirmation before committing
type: feedback
---

Siempre usar git en este proyecto. Después de probar algo y que funcione, pedir confirmación al usuario antes de hacer el commit.

**Why:** El proyecto tiene muchos módulos y sin commits frecuentes era difícil hacer rollback cuando algo fallaba.

**How to apply:** Después de implementar y verificar que algo funciona, preguntar al usuario "¿Confirmas que funciona? Lo hago commit." Nunca hacer commit sin confirmación explícita del usuario.
