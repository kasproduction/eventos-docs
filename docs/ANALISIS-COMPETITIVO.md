# Analisis Competitivo — EventOS

> Cotizaciones reales analizadas 2026-04-09. Gaps identificados, pricing, escalabilidad.

---

## Cotizaciones reales

Se obtuvieron dos cotizaciones dirigidas a **Eventos Efectivos y Producciones S.A.S.** para el evento **Bintec Bancolombia** (~14,000 asistentes):

| Proveedor | Plataforma | Total | Moneda | En USD |
|---|---|---|---|---|
| **Axity / Cisco** | Webex Events (ex-Socio) | $88,697 | USD | $88,697 |
| **IPServices** | ICE360 Premium | $49,340,364 | COP | ~$11,748 |
| **EventOS** (proyectado) | EventOS SaaS | $200-400/mes infra | USD | $200-400 |

## Por que Cisco cobra $88,697 USD

- 95%+ es margen, no infraestructura
- Marca ("nadie pierde su empleo por comprar Cisco")
- SLA contractual 99.9% con respaldo legal
- Compliance (SOC2, ISO 27001)
- Cadena de intermediarios (Bancolombia → Eventos Efectivos → Axity → Cisco)
- Enterprise sales (abogados, procurement, licitaciones)
- Soporte dedicado ("War Room" con ingenieros)
- Webex Events fue una startup (Socio) comprada por Cisco en 2022. Misma arquitectura: servers + DB + WebSockets + CDN + app movil.

## Features donde EventOS YA supera a ambos

1. Gamificacion (13 acciones + passport stamps + leaderboard) — ni Cisco ni ICE360
2. Social Wall (feed + memorias + momentos) — Cisco basico, ICE360 nada
3. Photobooth moderado — ninguno
4. Ratings de sesiones — ninguno
5. Matchmaking por intereses con overlap — superior al "Shake & Connect" de Cisco
6. Push nativo (no WhatsApp $850K extra, no web-only)
7. Real-time invalidation 4 capas (socket + focusManager + reconnect + staleTime)
8. Brand Wall/Profile con lead capture cualificado

## Gaps cerrados con sesiones 1.C1-1.C6

- **1.C1 Analytics** — ambos competidores lo tienen, es tablestakes
- **1.C2 Wallet digital** — reemplaza badge printing como default moderno
- **1.C3 QR dinamico** — reemplaza reconocimiento facial (mas seguro, $0 hardware)
- **1.C4 Digital signage** — proyecto checki ya existia, integrar a EventOS
- **1.C5 Calendar .ics** — ICE360 tenia sync, nosotros archivo .ics universal
- **1.C6 Badge printing** — add-on para clientes nostalgicos

## Mercado objetivo y pricing

| Segmento | Precio | Por que nos eligen |
|---|---|---|
| Eventos corporativos medianos (500-5k) | $3,000-8,000 USD/evento | Mas features que ICE360, 10x mas barato que Cisco |
| Agencias de eventos (white-label) | $1,500-3,000 USD/mes | Plataforma que venden como propia |
| Empresas con 3-4 eventos/ano | $800-1,500 USD/mes SaaS | Mas barato que contratar por evento |

## Escalabilidad horizontal validada

Arquitectura actual (Laravel stateless + Socket.IO/Redis adapter + Cloudflare CDN) escala sin reescribir codigo:

| Escala | Infra | Costo/mes | Equipo |
|---|---|---|---|
| 1k-5k | 1 VPS gordo | ~$80-120 | Solo |
| 5k-10k | 2-3 VPS + LB | ~$200-400 | Solo |
| 10k-50k | 5-8 VPS + Redis cluster + MySQL replica | ~$800-1,500 | 1 DevOps |
| 50k-100k | 10-15 VPS + DB cluster | ~$3,000-5,000 | DevOps + backend + soporte |
| 100k+ | Kubernetes, auto-scaling, multi-region | ~$8,000-15,000 | Equipo completo |

A 100k+ el negocio factura $500k-1M USD/ano y el equipo se paga solo.
