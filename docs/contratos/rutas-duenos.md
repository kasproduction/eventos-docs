# Dueños de ruta (M1.5.8)

> **Generado** por `eventos-api-dotnet/scripts/cruzar.sh` y `descruzar.sh` desde `proxy/rutas-csharp.tsv`. No se
> edita a mano (regla 28). Lo que no esta en la tabla lo atiende PHP. Nginx manda por metodo y ruta exacta
> (`proxy/rutas-csharp.conf`); cada respuesta dice quien contesto en `X-Backend: php|dotnet`.

| Metodo | Ruta | Dueño | Cruzada | Medicion |
|---|---|---|---|---|
| GET | `/api/v1/health` | C# | 2026-10-25 | sin medir (M1.5.2) |
| GET | `/api/v1/version` | C# | 2026-10-25 | sin medir (M1.5.2) |
| GET | `/api/v1/auth/me` | C# | 2026-10-25 | sin medir (M1.5.2) |

Todo lo demas: PHP.
