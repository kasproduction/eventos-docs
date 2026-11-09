# Torre de control: el reporte de los vigilantes (version 1)

M2.9.1 y M2.9.2 (alcance de Kamilo 2026-11-08). Cada servidor corre un vigilante que cada 10 s deja su reporte en Redis. La torre
(`GET /api/admin/v1/torre`, solo super_admin) los junta. El formato manda desde C#: `eventos-api-dotnet/src/Contracts/Torre/Torre.cs`.
Los vigilantes de Node (`eventos-socket/src/vigilante.ts`) y de Next (`eventos-web/src/lib/torre/vigilante.ts`) escriben lo mismo, y
hay una prueba de cada lado con el JSON exacto (`TorreDeControlTest.Lee_un_reporte_escrito_por_Node`, `vigilante.test.ts`,
`torre-vigilante.test.ts`).

## Donde

Todo en la base de Redis del socket (`Redis:BaseSocket` en C#, `REDIS_DB` en el socket, la base de `TORRE_REDIS_URL` en Next).

| Llave | Que guarda | Vida |
|---|---|---|
| `torre:reporte:{tipo}:{servidor}:{proceso}` | el reporte (JSON) | 30 s |
| `torre:servidores` (hash) | `{tipo}:{servidor}:{proceso}` -> hora del ultimo reporte (ms); asi la torre sabe quien quedo sin señal | 1 dia |
| `torre:historia:{tipo}:{servidor}:{proceso}` (lista) | los ultimos 90 puntos (15 minutos), el mas nuevo primero | 20 min |
| `torre:compartido:historia` (lista) | colas de Hangfire y Horizon, MySQL y Redis; la escribe UN servidor de la API por vuelta (candado `torre:compartido:candado`, 9 s) | 20 min |

`tipo` es `api`, `sockets` o `webapp`. `servidor` es el nombre de la maquina (o `Torre:Servidor` / `TORRE_SERVIDOR`). `proceso` es
`api` en C#, el `pm_id` de pm2 en Node y Next.

## El reporte

```json
{
  "version": 1, "tipo": "sockets", "servidor": "sock-1", "proceso": "0", "hora": 1762600000000,
  "maquina": { "procesador": 22.4, "ram": 44, "disco": 30, "carga": 0.42, "prendida_segundos": 3600 },
  "procesos": [{ "nombre": "Node (proceso 0)", "procesador": 5.1, "ram_mb": 90, "ram": 2.2, "prendido_segundos": 1800,
                 "ocupados": null, "maximo": null, "conectados": 812 }],
  "trafico": null,
  "sockets": { "conectados": 812, "reconexiones": 3 }
}
```

- Porcentajes de 0 a 100. `procesador` de un proceso es su parte de toda la maquina. Lo que no se sabe leer va en `null`.
- `trafico` (solo la API): `pedidos_por_segundo`, `p95_ms`, `errores` (% de 5xx), `logins_fallidos`, `demasiados` (429) y las 30 `rutas`
  mas pedidas del ultimo minuto, leidas del registro de Nginx (formato `eventos_rutas`).
- `ocupados` y `maximo`: los trabajadores de PHP-FPM (ocupado = gasto procesador desde la vuelta anterior; maximo = `pm.max_children`).

## El punto de historia

`{ hora, procesador, ram, pedidos, p95_ms, errores, conectados, reconexiones, logins_fallidos, bloqueos }`: el de la maquina y,
segun el tipo, lo de trafico o de sockets; lo demas en `null`.

## Umbrales

`eventos-api-dotnet/src/Api/Torre/umbrales.json`, al lado del binario; se lee en cada pedido (se recalibra sin recompilar ni
reiniciar). La pantalla no repite los umbrales: llegan en la respuesta de la torre.
