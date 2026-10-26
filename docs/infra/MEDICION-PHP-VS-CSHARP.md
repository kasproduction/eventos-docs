# Medicion PHP contra C# (misma maquina)

> Una fila por medicion (M1.5.2 y M1.5.5 del roadmap de la migracion). Se agregan filas, no se reescriben. La
> comparacion grande contra las pruebas de agosto (300 a 1.500 personas en DigitalOcean, `INFORME-STRESS-2026-08-01.md`
> y `DIAGNOSTICO-2026-08-02.md`) va cuando esten en C# las rutas pesadas (agenda, home, networking, fase 4): decision
> de Kamilo 2026-10-25. Estas mediciones en el MSI comparan PHP contra C# entre si; no dicen la capacidad de produccion.

## Como se mide

- **Maquina:** el MSI (Ryzen 3 5300G, 8 nucleos, 5,7 GB visibles, Ubuntu 24.04), por Tailscale.
- **PHP** como en produccion: PHP-FPM 8.4 con el pool por RAM de `deploy.sh` (42 hijos aca) y OPcache + JIT,
  detras de Nginx (127.0.0.1:8091), `APP_ENV=production` con config y rutas en cache.
- **C#** en Release, como servicio, detras del mismo Nginx (127.0.0.1:8092).
- **Los dos** contra la misma base (`eventos_medicion`, sembrada con ContratoSeeder) y el mismo Redis.
- **Carga** (`eventos-api-dotnet/carga/medir-ruta.js`, k6 2.3.0): una rafaga de 300 usuarios pidiendo a la vez (la
  parte HTTP de `e3-agenda-storm.js`: todos refrescan al mismo tiempo) y despues pedidos por segundo subiendo hasta
  400 en 30 s y sosteniendo 400 otros 30 s. Mismo perfil para los dos; antes, 100 pedidos para calentar.
- **CPU por pedido:** la contabilidad de cada servicio de systemd (`cpu.stat` del cgroup), que cuenta tambien los
  hijos de PHP-FPM que se reciclan durante la prueba. Se mide el backend y MySQL por separado.
- **Salvedad:** k6 corre en la misma maquina y le quita CPU a los dos por igual.
- **Repetir:** en el MSI, `scripts/preparar-medicion-msi.sh` (deja todo listo, se puede correr de nuevo) y
  `scripts/medir-cruce.sh "GET /api/v1/ruta"`. Los resultados crudos quedan en `/srv/eventos-medicion/resultados/`.
  Al terminar, `sudo systemctl stop eventos-medicion-api`.

## Resultados

### 2026-10-25: `GET /api/v1/auth/me` (C# `9571b84` y siguientes, backend `28f9daa`)

| | PHP | C# |
|---|---|---|
| p95 de la rafaga (300 a la vez) | 1.771 ms | 588 ms |
| p95 con carga sostenida | 6.246 ms | 5,8 ms |
| p99 con carga sostenida | 6.370 ms | 6,7 ms |
| Pedidos atendidos (de los que mando k6) | 10.345 | 18.448 (todos) |
| Pedidos que no alcanzo a atender | 8.103 | 0 |
| Errores | 0 % | 0 % |
| CPU del backend por pedido | 36,1 ms | 5,3 ms |
| CPU de MySQL por pedido | 8,4 ms | 5,1 ms |

**Lectura:** PHP se satura antes de los 400 pedidos por segundo (la cola sube a segundos y k6 deja de mandar 8.103
pedidos); C# los atiende todos con p95 de 6 ms. Por pedido, C# gasta unas **7 veces menos CPU** que PHP y MySQL
trabaja un 40 % menos. Es una sola ruta, liviana: el numero que importa para crecer sale cuando esten las pesadas.
