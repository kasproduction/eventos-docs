# Buenas practicas de la migracion a .NET

> Reglas para escribir C# en la migracion (`docs/roadmaps/ROADMAP-MIGRACION-DOTNET.md`). Se fijan antes de la primera
> linea de C#. Version 2026-10-22: reglas de Kamilo, revisadas contra el roadmap y contra lo que salio al congelar las
> 247 rutas (backend `90e61f8`), con sus correcciones aprobadas. Una regla que no se pueda vigilar en CI dice como se
> vigila. La version operativa para Claude es `docs/plantillas/CLAUDE-dotnet.md`. Decisiones: `docs/decisiones.md`.

## Portar comportamiento, no forma

1. **El snapshot define que hace el endpoint; el codigo PHP solo explica por que.** PHP se lee para entender reglas, no
   para traducirlo linea a linea. Si el snapshot y el PHP no coinciden, manda el snapshot y se anota el hallazgo.
2. **Organizacion por modulo** (`Agenda/`, `Social/`, `CheckIn/`...), los 16 del inventario de `migracion-dotnet.md`
   §1.1. Cada modulo trae sus endpoints, servicios, DTOs y tests. Nada de carpetas `Controllers/`, `Services/` o
   `Repositories/` globales. Los contratos de PHP hoy van por tema (`tests/Contratos/*Test.php`); al portar un modulo se
   llevan sus pruebas a la carpeta del modulo.
3. **Cero abstraccion preventiva.** EF Core directo, sin repositorios; Minimal APIs o controllers finos; servicios con
   nombre. No Clean Architecture ni proyectos por capa. Los proyectos de la solucion (D.6: `Api`, `Worker`, `Hub`,
   `Contracts`, `Tests`) son uno por proceso que se despliega, no por capa.
4. **Efectos secundarios en un solo `XxxEffects` por entidad**, un metodo por efecto, disparado desde el servicio. Es la
   traduccion de los 14 observers, los 6 hooks `booted` y las closures del provider (M0.5.1 los deja asi en PHP primero).

## Red de seguridad

5. **Los snapshots corren en CI contra PHP y contra C# en cada push**, no solo al cruzar (M1.1.4, M1.1.5):
   - **Endpoint de reinicio solo para pruebas:** vuelve a sembrar `ContratoSeeder` entre pruebas que escriben. Se activa
     por variable de entorno y no existe en produccion (la ruta ni se registra).
   - **Las pruebas que no pueden correr contra C# llevan etiqueta y comparan forma**, no valor: hoy el 429 (repite pedidos
     por dentro), el ZIP "listo" (cambia la base en la prueba) y el Data Center (sesion simulada con `Sanctum::actingAs`).
   - **Ruleta y sorteo:** PHP usa semilla fija (`mt_srand(2026)`); C# acepta una semilla en modo pruebas para comparar el
     valor, no solo la forma.
6. **Cada cruce se revierte en un despliegue:** `cruzar.ps1` y `descruzar.ps1`, probados antes del primer cruce real.
7. **Prueba cruzada de escrituras para todo lo que escribe:** si C# guarda, PHP lee lo mismo que si hubiera guardado
   PHP o Filament (escribir por C#, leer el snapshot por PHP).
8. **`mysqldump` automatico dentro del script de cruce** para las rutas que escriben.
9. **Toda lista con orden explicito y el mismo que PHP.** Donde PHP no pone `ORDER BY`, MySQL decide y C# puede
   devolver otro orden; se agrega el orden en PHP antes de cruzar la ruta.

## .NET

10. **Async de punta a punta:** prohibido `.Result` y `.Wait()`. Lo vigila `Microsoft.VisualStudio.Threading.Analyzers`
    con los avisos como error en CI.
11. **Un solo `ConnectionMultiplexer`** de Redis como singleton; **`HttpClientFactory`** para todo cliente HTTP.
12. **`AsNoTracking()` en lecturas y proyeccion a DTO con `Select`:** nunca cargar la entidad entera para devolver tres
    campos.
13. **Fechas:** `DateTimeOffset` con el **offset de la zona de la app** (`APP_TIMEZONE`, hoy Bogota, -05:00), sin
    fracciones (M0.3.2). Nunca `DateTime.Now`. La zona por evento queda abierta en `decisiones.md`, para despues de la
    fase 5.
    - 22 migraciones tienen columnas con `useCurrent()`: la base pone su propia hora. **C# las escribe siempre
      explicitas.** La zona horaria del MySQL de produccion se verifica en la fase 1 (M1.2.5): si es UTC, esas fechas ya
      estan corridas 5 horas.
14. **Politica JSON global en un solo lugar:** snake_case, nulos incluidos, campos condicionales omitidos. Nunca se
    sobreescribe por endpoint. Las unicas excepciones son por campo, en **una lista cerrada en un solo archivo**
    (`Contracts/ExcepcionesJson.cs`, M1.4.1). **Nada entra a la lista sin una decision en `decisiones.md`.** Hoy son 15
    nombres que llegan a un cliente (detalle en `plantillas/CLAUDE-dotnet.md`): 2 en la API HTTP (`eventId`,
    `sessionId`, encuestas), 1 en datos de push y el resto en el socket, que se confirma en M0.4.1.
15. **Jobs de Hangfire idempotentes.** `ShouldBeUnique` de Laravel NO equivale a `DisableConcurrentExecution` (ese solo
    evita correr dos a la vez; los duplicados igual se encolan). La unicidad es **un filtro propio de Hangfire (o una
    verificacion antes de encolar), escrito una vez en `Worker` y reutilizado** por todo job que en PHP es
    `ShouldBeUnique`, con la misma clave.
16. **`dotnet list package --vulnerable` en CI;** versiones fijas en los `.csproj`.

## Observabilidad

17. **`X-Request-Id`** que atraviesa proxy, PHP, C# y Node, visible en Sentry de los dos lados (M1.1.3).
18. **Panel por ruta y por `X-Backend`** (p95, errores, req/s). Despues de cada cruce se compara la ruta contra su
    propio numero en PHP.
19. **Cada cruce se mide con los k6 de agosto en el MSI** (PHP contra C#, misma maquina) y el numero queda anotado en
    el roadmap junto a la tarea. En el servidor real se mide en los hitos, no en cada cruce.
20. **Criterio de cruce de una ruta:** snapshot igual contra los dos, orden explicito (regla 9), prueba cruzada de
    escrituras si escribe (regla 7), medicion en el MSI anotada (regla 19) y `descruzar.ps1` probado.

## Proceso

21. **Un bloque del roadmap por sesion, un commit por tarea.** Nada de "aprovecho y tambien": un bug que aparece se
    registra en BUG-LOG y se arregla solo si bloquea o si Kamilo lo decide.
22. **Segunda pasada de revision por modulo**, en sesion aparte, con una pregunta: que hace PHP que esto no hace.
23. **Solo la fase activa visible en el roadmap;** las demas plegadas.
24. **Un hallazgo corrige un numero, no abre un documento.** Las decisiones van solo en `docs/decisiones.md`, una
    linea cada una (fecha, que, por que); el roadmap y `PENDIENTES.md` solo enlazan.
25. **Retro al cerrar cada fase:** que se estimo y que costo, que regla falto, que se corrige del roadmap. Va al final de
    la fase en el roadmap, en cinco lineas.
