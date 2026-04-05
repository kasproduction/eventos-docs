---
name: EventOS — notas técnicas del motor de módulos (Sesión 1.2)
description: Gotchas y decisiones técnicas de la sesión 1.2 — módulos dinámicos
type: project
---

Notas de la Sesión 1.2 (2026-03-28).

## phpunit.xml — SQLite habilitado

Las líneas de SQLite en phpunit.xml estaban comentadas. Se descomentaron en esta sesión:
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

**How to apply:** En todas las sesiones siguientes los tests usan SQLite en memoria. No volver a comentarlas.

---

## OrganizationFactory — plan debe ser 'starter'

El enum de `plan` en organizations es: `starter`, `pro`, `enterprise`.
El factory usa `'starter'` como default.

**Why:** SQLite aplica los CHECK constraints de enums igual que MySQL. 'basic' falla el constraint.

---

## AuthUser incluye eventId

`AuthUser` en `stores/authStore.ts` ahora tiene `eventId: number | null`.
Se mapea desde `attendee.event_id` en `authApi.ts → toAuthUser()`.

**How to apply:** En hooks que necesiten el event ID del usuario activo, usar `useAuthStore(s => s.user?.eventId)`.

---

## Cache key de módulos

Clave Redis: `event:{id}:modules:{role}` donde role es `presencial`, `virtual` o `vendedor`.
TTL: 30 segundos.
Invalidación: `ModuleObserver` borra las 3 claves al cambiar `enabled`, `config` o `sort_order`.

---

## Estructura de archivos nuevos (backend)

- `app/Http/Controllers/Api/V1/ModuleController.php`
- `app/Http/Resources/V1/ModuleResource.php`
- `app/Filament/Resources/ModuleResource.php` + Pages/
- `app/Jobs/SendSilentPushJob.php`
- `app/Observers/ModuleObserver.php`
- `routes/api/events.php` — rutas de eventos (se irán agregando por sesión)
- `database/factories/OrganizationFactory.php`
- `database/factories/EventFactory.php`
- `tests/Feature/Modules/ModulesTest.php`

## Estructura de archivos nuevos (app)

- `hooks/useModules.ts` — TanStack Query + MMKV
- `components/ui/ModuleMenu.tsx` — grilla de módulos reutilizable
