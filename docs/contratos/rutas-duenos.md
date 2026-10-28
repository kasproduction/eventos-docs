# Dueños de ruta (M1.5.8)

> **Generado** por `eventos-api-dotnet/scripts/cruzar.sh` y `descruzar.sh` desde `proxy/rutas-csharp.tsv`. No se
> edita a mano (regla 28). Lo que no esta en la tabla lo atiende PHP. Nginx manda por metodo y ruta exacta
> (`proxy/rutas-csharp.conf`); cada respuesta dice quien contesto en `X-Backend: php|dotnet`.

| Metodo | Ruta | Dueño | Cruzada | Medicion |
|---|---|---|---|---|
| GET | `/api/v1/health` | C# | 2026-10-25 | sin medir (M1.5.2) |
| GET | `/api/v1/version` | C# | 2026-10-25 | sin medir (M1.5.2) |
| GET | `/api/v1/auth/me` | C# | 2026-10-25 | MSI 2026-10-25: p95 sostenida PHP 6246 ms / C# 6 ms; CPU por pedido PHP 36,1 ms / C# 5,3 ms (docs/infra/MEDICION-PHP-VS-CSHARP.md) |
| POST | `/api/v1/admin/sessions/{sessionId}/start` | C# | 2026-10-27 | sin medir |
| POST | `/api/v1/admin/sessions/{sessionId}/end` | C# | 2026-10-27 | sin medir |
| POST | `/api/v1/admin/sessions/{sessionId}/cancel` | C# | 2026-10-27 | sin medir |
| POST | `/api/v1/admin/sessions/{sessionId}/delay` | C# | 2026-10-27 | sin medir |

Ademas, todo `/api/admin/v1/*` es de C# (API del admin nuevo, M2.1.4): Nginx manda el prefijo entero
(`proxy/nginx-api.conf`), sin pasar por esta tabla.

Todo lo demas: PHP.
