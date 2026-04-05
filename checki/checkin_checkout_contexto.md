# SISTEMA DE CHECKIN/CHECKOUT — CONTEXTO COMPLETO
> Documento de contexto para continuar desarrollo en Claude Code  
> Próximo paso: **Fase 1 — Schema SQL ejecutable**

---

## 1. QUÉ ES EL PROYECTO

Sistema modular de control de asistencia para eventos corporativos multi-salón. Diseñado para ser reutilizable con cualquier fuente de datos. El primer cliente usa socio.events pero el sistema no depende de ello.

**Problema que resuelve:** El cliente usaba la app de socio.events con iPads operados por personas en cada puerta. No funcionó bien. La solución es tótems autónomos con servidor local que no dependen de internet ni de operadores humanos en cada punto.

**Resultado final que entrega:** Pedro Pérez asistió el Día 1 a la Charla 1 de 08:00-09:00 y se quedó en la Charla 2. Calculado automáticamente, sin intervención manual.

---

## 2. HARDWARE

```
Tótem:
  Kiosk Android, 15.6" touch capacitivo 1920x1080
  Metal cold-rolled, color blanco, tabletop
  Android 10+ | 4GB RAM | 64GB storage
  QR scanner 1D/2D integrado
  NFC integrado
  WiFi + RJ45
  (pendiente confirmar: versión Android exacta, chip CPU, si QR es HID o SDK propietario)

Lector QR externo:
  USB / Type-C HID
  Ya probado en Android — actúa como teclado, envía string + Enter
  Se usa como respaldo o principal si el integrado es SDK

Servidor local:
  Mini PC o laptop dentro del gabinete del tótem o gabinete aparte bajo llave
  PHP + MySQL
  Router WiFi local (red privada, sin internet requerido)
  IP fija ej: 192.168.1.100

Red:
  WiFi local privada 2.4GHz / 5GHz
  RJ45 disponible para conexión cableada
  Sin dependencia de internet durante la operación del evento
```

---

## 3. CONFIGURACIÓN DEL EVENTO

```
Tótems:     4 en total, fijos (no cambian de salón entre días)
Salones:    2 salones, 2 tótems por salón (entrada + salida)
Puertas:    Normalmente entrada/salida definidas
            Pueden abrirse bidireccionales por saturación de personas
Asistentes: ~10,000 en 4 días (~2,500/día)
QR:         Pre-generado, llega por email o impreso en escarapela
Días:       Evento multi-día, charlas diferentes cada día
Charlas:    Por salón, horarios configurables, pueden cambiar en runtime
```

---

## 4. ARQUITECTURA POR CAPAS

```
┌─────────────────────────────────────────────────────┐
│                   FUENTES DE DATOS                   │
│  socio.events  │  Eventbrite  │  CSV/Excel  │  Manual │
└───────┬────────┴──────┬───────┴─────┬───────┴────┬───┘
        └───────────────┴─────────────┴────────────┘
                                │
                    ┌───────────▼───────────┐
                    │      ADAPTADORES      │
                    │  Normalizan al mismo  │
                    │  contrato estándar    │
                    └───────────┬───────────┘
                                │
                    ┌───────────▼───────────┐
                    │      MySQL CORE       │
                    │  eventos, salones     │
                    │  charlas, asistentes  │
                    │  movimientos, estados │
                    └───────┬───────┬───────┘
                            │       │
           ┌────────────────┘       └──────────────────┐
           │                                           │
┌──────────▼──────────┐                   ┌────────────▼─────────┐
│    PHP CORE API     │                   │     PANEL ADMIN      │
│  POST /api/lectura  │                   │     (PHP web)        │
│  GET  /api/ping     │                   │  Agenda en vivo      │
│  GET  /charla-activa│                   │  Monitor asistencia  │
└──────────┬──────────┘                   │  Correcciones manual │
           │ WiFi local                   │  Exportar reportes   │
    ┌──────┴───────────────────┐          └──────────────────────┘
    │        TÓTEMS (x4)       │
    │  Unity + Android         │
    │  Lector QR HID           │
    │  Cola offline local      │
    └──────────────────────────┘
```

**Principio central:** Las capas no se conocen entre sí. Cambiar la fuente de datos no toca nada del core. El core nunca sabe de dónde vienen los asistentes.

---

## 5. LÓGICA DE NEGOCIO

### 5.1 Toggle checkin/checkout

El estado es **por asistente + salón**, no por puerta. Esto permite que ambas puertas de un salón sean bidireccionales sin ambigüedad.

```
estado = fuera  →  siguiente scan = CHECKIN
estado = dentro →  siguiente scan = CHECKOUT

Si está dentro en salón A y escanea en salón B:
  → AUTO-CHECKOUT en A  (metodo: auto_cambio_salon)
  → CHECKIN en B

Debounce: mismo UID+salón en menos de 5 segundos → ignorar silenciosamente
```

### 5.2 Flujo completo de cada scan (debe completarse en <300ms)

```
01. Identificar asistente por uid_qr usando índice → O(1)
    Si no existe → responder error QR_NO_VALIDO, loguear UID desconocido

02. Identificar salón por totem_id → lookup desde caché RAM

03. Verificar debounce
    Si mismo asistente+salón registró hace <5 segundos → ignorar

04. Leer estado actual desde estado_asistentes
    Si no existe registro → asumir estado = fuera

05. Determinar tipo
    estado=fuera   → tipo = checkin
    estado=dentro  → tipo = checkout

06. Verificar conflicto con otro salón
    Si estado=dentro en salón DIFERENTE al actual:
      → INSERT auto-checkout en salón anterior (metodo: auto_cambio_salon)
      → UPDATE estado salón anterior a fuera

07. Identificar charla activa en ese salón
    SELECT donde hora_inicio <= NOW() AND hora_fin >= NOW() AND salon_id = X
    Si no hay charla activa → charla_id = NULL (llegó entre charlas)

08. INSERT en movimientos con timestamp del SERVIDOR (no del dispositivo)

09. UPDATE estado_asistentes (INSERT si no existe)

10. Responder al tótem:
    { tipo, nombre, charla, color: "verde"|"rojo", mensaje }
    El tótem solo muestra, nunca decide
```

### 5.3 Cálculo de asistencia por charla

**NO ocurre en tiempo real.** Se ejecuta al final del día o cuando se solicita. Esto permite recalcular si cambia la agenda.

```
Para cada par (asistente, charla):

inicio_efectivo = MAX(checkin_timestamp, hora_inicio_charla - buffer_pre_inicio)
fin_efectivo    = MIN(checkout_timestamp | hora_fin_charla, hora_fin_charla)
minutos         = TIMESTAMPDIFF(MINUTE, inicio_efectivo, fin_efectivo)
cuenta          = minutos >= umbral_min_asistencia

Si no hay checkout:
  → usar hora_fin_charla como fin efectivo
  → calidad_dato = 'inferido'

Si cambia la agenda:
  → recalcular desde movimientos (que son inmutables)
  → asistencia_calculada se regenera completamente
```

### 5.4 Manejo de cambios de agenda

Los timestamps de movimientos son **inmutables** — son hechos físicos. La agenda es **mutable**. Los reportes siempre se calculan, nunca se guardan como verdad fija.

```
Cuando operador modifica agenda:
  1. Guardar valores actuales en agenda_cambios antes de modificar
  2. Actualizar hora_inicio / hora_fin en charlas
     (hora_inicio_original y hora_fin_original NUNCA se tocan)
  3. Marcar recalculo_requerido = 1
  4. Proceso de cálculo detecta el flag y regenera asistencia_calculada

Botón "Retrasar toda la agenda X minutos":
  → Aplica delta a todas las charlas del salón en el día
  → Un solo click, una sola entrada en agenda_cambios
```

---

## 6. SCHEMA MySQL COMPLETO

### 6.1 Grupo: Configuración del evento

```sql
-- Tabla: eventos
id                  INT           PK AUTOINCREMENT
nombre              VARCHAR(200)  NOT NULL
slug                VARCHAR(100)  NOT NULL UNIQUE
fecha_inicio        DATE          NOT NULL
fecha_fin           DATE          NOT NULL
activo              TINYINT(1)    DEFAULT 1
config_json         JSON          NULL  -- umbral_default, buffer_pre_charla, etc.
created_at          TIMESTAMP     DEFAULT NOW()

-- Tabla: salones
id                  INT           PK AUTOINCREMENT
evento_id           INT           FK → eventos.id
nombre              VARCHAR(100)  NOT NULL
capacidad           INT           NULL  -- NULL = sin límite
activo              TINYINT(1)    DEFAULT 1

-- Tabla: totems
id                  INT           PK AUTOINCREMENT
evento_id           INT           FK → eventos.id
salon_id            INT           FK → salones.id  -- FIJO, no cambia entre días
nombre              VARCHAR(100)  NOT NULL  -- ej: "Salón A - Entrada"
tipo                ENUM('entrada','salida','bidireccional')
activo              TINYINT(1)    DEFAULT 1
ip_local            VARCHAR(15)   NULL  -- para diagnóstico en red local

-- Tabla: dias_evento
id                  INT           PK AUTOINCREMENT
evento_id           INT           FK → eventos.id
fecha               DATE          NOT NULL  -- UNIQUE por evento
nombre              VARCHAR(100)  NULL  -- ej: "Día 1 — Apertura"
orden               INT           NOT NULL  -- 1, 2, 3, 4

-- Tabla: charlas
id                  INT           PK AUTOINCREMENT
dia_evento_id       INT           FK → dias_evento.id
salon_id            INT           FK → salones.id
charla_padre_id     INT           FK → charlas.id  NULL  -- para salas espejo
titulo              VARCHAR(200)  NOT NULL
ponente             VARCHAR(200)  NULL
hora_inicio         DATETIME      NOT NULL  -- se actualiza si hay cambios
hora_fin            DATETIME      NOT NULL  -- se actualiza si hay cambios
hora_inicio_original DATETIME     NOT NULL  -- NUNCA SE MODIFICA
hora_fin_original   DATETIME      NOT NULL  -- NUNCA SE MODIFICA
umbral_min_asistencia INT         DEFAULT 15  -- minutos mínimos, configurable por charla
buffer_pre_inicio   INT           DEFAULT 15  -- minutos antes que cuentan para la charla
cancelada           TINYINT(1)    DEFAULT 0
orden_en_dia        INT           NOT NULL
```

### 6.2 Grupo: Asistentes

```sql
-- Tabla: asistentes
id                  INT           PK AUTOINCREMENT
evento_id           INT           FK → eventos.id
uid_qr              VARCHAR(255)  NOT NULL  -- string exacto que lee el lector
nombre              VARCHAR(200)  NOT NULL
email               VARCHAR(200)  NULL
empresa             VARCHAR(200)  NULL
external_id         VARCHAR(100)  NULL  -- ID en sistema origen
fuente              VARCHAR(50)   NOT NULL  -- socio_events | eventbrite | csv | manual
metadata_json       JSON          NULL  -- campos extra, el core los ignora
activo              TINYINT(1)    DEFAULT 1
created_at          TIMESTAMP     DEFAULT NOW()
updated_at          TIMESTAMP     DEFAULT NOW() ON UPDATE CURRENT_TIMESTAMP

-- ÍNDICES CRÍTICOS:
UNIQUE(evento_id, uid_qr)      -- búsqueda O(1) en cada scan
INDEX(evento_id, email)
INDEX(evento_id, external_id)
```

### 6.3 Grupo: Movimientos (corazón operativo)

```sql
-- Tabla: movimientos
id                  BIGINT        PK AUTOINCREMENT  -- BIGINT por volumen
evento_id           INT           FK → eventos.id
asistente_id        INT           FK → asistentes.id
totem_id            INT           FK → totems.id
salon_id            INT           FK → salones.id  -- redundante, optimiza reportes
tipo                ENUM('checkin','checkout')
timestamp           DATETIME(3)   NOT NULL  -- SERVIDOR, inmutable, milisegundos
timestamp_totem     DATETIME(3)   NULL      -- dispositivo, solo referencia
metodo              ENUM('qr_lector','manual','auto_cambio_salon','auto_fin_jornada','auto_fin_charla')
operador_id         INT           FK → operadores.id  NULL  -- solo si metodo=manual
flags               SET('fuera_horario','checkout_sin_checkin','cambio_salon','inferido','corregido','cola_offline')  NULL
notas               TEXT          NULL  -- solo para correcciones manuales

-- ÍNDICES CRÍTICOS:
INDEX(asistente_id, salon_id, timestamp)  -- para toggle y cálculos
INDEX(evento_id, timestamp)               -- para reportes por evento
INDEX(totem_id, timestamp)                -- para diagnóstico por dispositivo

-- Tabla: estado_asistentes
asistente_id        INT           PK (compuesto) FK → asistentes.id
salon_id            INT           PK (compuesto) FK → salones.id
estado              ENUM('dentro','fuera')  NOT NULL
ultimo_movimiento_id BIGINT       FK → movimientos.id  NULL
updated_at          TIMESTAMP     NOT NULL
-- Esta tabla es la "verdad actual" — se reconstruye desde movimientos si el servidor se reinicia

-- Tabla: asistencia_calculada
id                  INT           PK AUTOINCREMENT
evento_id           INT           FK → eventos.id
asistente_id        INT           FK → asistentes.id
charla_id           INT           FK → charlas.id
dia_evento_id       INT           FK → dias_evento.id
checkin_real        DATETIME(3)   NOT NULL
checkout_real       DATETIME(3)   NULL
minutos_presentes   INT           NOT NULL
cuenta_asistencia   TINYINT(1)    NOT NULL  -- 1 si minutos >= umbral
calidad_dato        ENUM('real','inferido','corregido')
calculado_at        TIMESTAMP     NOT NULL
-- ESTA TABLA ES REGENERABLE. No es fuente de verdad, es resultado de cálculo.
-- Se puede borrar y recalcular completamente en cualquier momento.
UNIQUE(asistente_id, charla_id)  -- un registro por asistente por charla
```

### 6.4 Grupo: Auditoría

```sql
-- Tabla: agenda_cambios
id                  INT           PK AUTOINCREMENT
charla_id           INT           FK → charlas.id
campo_modificado    VARCHAR(50)   NOT NULL  -- hora_inicio | hora_fin | salon_id | cancelada
valor_anterior      VARCHAR(200)  NOT NULL
valor_nuevo         VARCHAR(200)  NOT NULL
motivo              TEXT          NULL
operador_id         INT           FK → operadores.id
timestamp           TIMESTAMP     NOT NULL
recalculo_requerido TINYINT(1)    DEFAULT 1

-- Tabla: operadores
id                  INT           PK AUTOINCREMENT
evento_id           INT           FK → eventos.id
nombre              VARCHAR(100)  NOT NULL
pin                 VARCHAR(255)  NOT NULL  -- hash bcrypt
rol                 ENUM('admin','supervisor','viewer')
activo              TINYINT(1)    DEFAULT 1

-- Tabla: sync_log
id                  INT           PK AUTOINCREMENT
evento_id           INT           FK → eventos.id
fuente              VARCHAR(50)   NOT NULL
tipo                ENUM('asistentes','agenda','completo')
registros           INT           NOT NULL
errores             INT           DEFAULT 0
log_json            JSON          NULL
timestamp           TIMESTAMP     NOT NULL
```

---

## 7. API ENDPOINTS PHP

### 7.1 Endpoints para tótems (Unity)

```
POST /api/lectura
  Body:     { uid_qr, totem_id, timestamp_totem }
  Response: { tipo, nombre, charla, color, mensaje }
  Lógica:   Los 10 pasos del flujo de scan

GET  /api/ping
  Response: { ok: true, timestamp }
  Uso:      El tótem verifica conexión al servidor cada 10s

GET  /api/charla-activa?salon_id=X
  Response: { id, titulo, ponente, hora_inicio, hora_fin }
  Uso:      El tótem muestra en pantalla idle qué charla hay ahora
```

### 7.2 Endpoints para panel admin

```
GET    /admin/monitor
       Estado en tiempo real: personas en cada salón, últimas lecturas, alertas

GET    /admin/asistentes
       Lista paginada con estado actual (dentro/fuera) y último movimiento

POST   /admin/movimiento-manual
       Body: { asistente_id, salon_id, tipo, pin, motivo }
       Checkin/checkout manual con trazabilidad

GET    /admin/agenda
       Agenda del día con estado: próxima, en curso, finalizada, cancelada

PATCH  /admin/charla/{id}
       Body: { hora_inicio?, hora_fin?, titulo?, cancelada? }
       Guarda en agenda_cambios automáticamente antes de modificar

POST   /admin/agenda/retrasar
       Body: { salon_id, dia_id, minutos }
       Mueve TODA la agenda del salón ese día

POST   /admin/recalcular-asistencia
       Body: { evento_id, dia_id? }
       Regenera asistencia_calculada desde movimientos × agenda actual

GET    /admin/reporte/asistente/{id}
       Todas las charlas del asistente con minutos y calidad del dato

GET    /admin/reporte/charla/{id}
       Todos los asistentes de esa charla con minutos

GET    /admin/reporte/dia/{dia_id}
       Resumen: totales por charla, por salón, asistentes únicos

GET    /admin/reporte/exportar?formato=csv&dia_id=X
       Export completo para el cliente

POST   /admin/importar
       Body: multipart con CSV o JSON en contrato estándar
       Llama al adaptador genérico

POST   /admin/totem/{id}/tipo
       Body: { tipo: "bidireccional" }
       Cambiar modo en runtime por saturación
```

---

## 8. CONTRATO ESTÁNDAR DEL ADAPTADOR

Cualquier adaptador debe producir exactamente estos formatos. El core nunca sabe qué adaptador lo llamó.

```json
// Asistente — formato estándar
{
  "uid_qr":     "A3F29B8C",
  "nombre":     "Pedro Pérez",
  "email":      "pedro@email.com",
  "empresa":    "Acme Corp",
  "external_id":"12345",
  "fuente":     "socio_events",
  "metadata":   {}
}

// Charla — formato estándar
{
  "titulo":       "Innovación en IA",
  "salon_nombre": "Salón A",
  "hora_inicio":  "2025-03-10 08:00:00",
  "hora_fin":     "2025-03-10 09:00:00",
  "ponente":      "Dr. García"
}
```

---

## 9. ADAPTADOR EXISTENTE — socio.events

`mini_api.py` ya existe, funciona y está en producción. Es FastAPI + Python que:

- Carga todos los asistentes de socio.events en RAM al arrancar (warmup)
- Busca por `qrData` en O(1) desde índice en memoria
- Refresca en background cada 30 min sin bloquear (patrón SWR)
- Endpoints: `/by-qr`, `/by-external`, `/by-id`, `/attendees/search`, `/cache/warmup`

Para integrar con el sistema checkin:
```
PHP recibe scan → GET http://localhost:8000/by-qr?qr=XXX → obtiene asistente en ~1ms
```

El `qrData` de socio.events es exactamente el string que emite el lector HID. No hay parsing adicional.

---

## 10. APP UNITY (TÓTEMS)

### Captura del lector HID
```csharp
// El lector actúa como teclado — Unity captura en Update()
void Update()
{
    foreach (char c in Input.inputString)
    {
        if (c == '\n' || c == '\r')  // Enter = lectura completa
        {
            ProcesarUID(bufferUID);
            bufferUID = "";
        }
        else
        {
            bufferUID += c;
        }
    }
}
```

### Configuración crítica Android
```csharp
// Al iniciar — evitar que el teclado virtual robe el foco
TouchScreenKeyboard.hideInput = true;
// Ningún InputField debe estar seleccionado en pantallas de escaneo
```

### Cola offline
```
Si POST /api/lectura falla:
  → Guardar lectura en cola local (PlayerPrefs o SQLite local)
  → Reintentar cada 5 segundos
  → Al reconectar, enviar cola en orden cronológico con flag cola_offline
```

### Pantallas
```
SplashScreen   → logo, config inicial (IP servidor, ID tótem)
IdleScreen     → "Escanea tu QR" + animación + charla activa en curso
ScanScreen     → cámara/lector activo
ResultScreen   → éxito (verde, nombre, charla) o error (rojo, mensaje) — 3 segundos
AdminScreen    → IP servidor, qué salón es este tótem — protegida con PIN
```

### Respuesta del servidor al tótem
```json
// Checkin exitoso
{ "tipo": "checkin", "nombre": "Pedro Pérez", "charla": "Charla 1", "color": "verde", "mensaje": "Bienvenido" }

// Checkout
{ "tipo": "checkout", "nombre": "Pedro Pérez", "minutos": 45, "color": "verde", "mensaje": "Hasta luego" }

// Error
{ "tipo": "error", "color": "rojo", "mensaje": "QR no válido" }
```

---

## 11. ESCENARIOS CUBIERTOS (17 casos)

| # | Escenario | Solución | Flag |
|---|-----------|----------|------|
| 01 | No hace checkout | Auto-checkout al fin de jornada + 15min buffer | `inferido` |
| 02 | Se pasa de salón sin checkout | Auto-checkout en salón anterior al detectar checkin en nuevo | `cambio_salon` |
| 03 | Escaneo doble accidental | Debounce 5 segundos por UID+salón | — |
| 04 | Puertas bidireccionales por saturación | Toggle por estado del asistente, no por rol de puerta | — |
| 05 | Llegó tarde a la charla | MAX(checkin, hora_inicio) en cálculo | `fuera_horario` |
| 06 | Se fue antes de terminar | MIN(checkout, hora_fin) en cálculo | — |
| 07 | Charlas consecutivas sin salir | Intervalo cruza múltiples charlas automáticamente | — |
| 08 | QR no registrado | Rechazar, loguear UID desconocido | — |
| 09 | Tótem pierde conexión | Cola local, sincroniza al reconectar en orden | `cola_offline` |
| 10 | Servidor se reinicia | Reconstruir estado_asistentes desde movimientos del día | — |
| 11 | Mismo QR en 2 tótems simultáneo | Lock por UID, primera lectura gana | — |
| 12 | Corrección manual por operador | PIN + motivo obligatorio | `corregido` |
| 13 | Charla empieza antes de lo planeado | buffer_pre_inicio captura quienes ya estaban dentro | — |
| 14 | Charla se retrasa | Botón retrasar agenda, recalcula automático | — |
| 15 | Charla se cancela | Flag cancelada=1, movimientos sin charla asignada | `charla_cancelada` |
| 16 | Charla cambia de salón | Reasignar movimientos del bloque horario | `corregido` |
| 17 | Salón espejo por exceso de personas | charla_padre_id, ambos salones reportan a la misma charla | — |

---

## 12. REPORTE FINAL QUE ENTREGA EL SISTEMA

```
EVENTO XYZ — REPORTE DE ASISTENCIA

Pedro Pérez | pedro@email.com | Acme Corp
├── Día 1 (10 Mar 2025)
│   ├── Salón A — Charla 1: Innovación en IA (08:00-09:00)  ✓  60 min  [real]
│   ├── Salón A — Charla 2: Transformación Digital (09:00-10:00)  ✓  60 min  [real]
│   └── Salón A — Charla 3: Cierre (10:00-11:00)  ✗
└── Día 2 (11 Mar 2025)
    └── Salón B — Charla 1: Workshop (09:00-10:00)  ✓  45 min  [inferido — sin checkout]

RESUMEN: 4 días disponibles | 2 días asistió | 3 charlas completas | 1 parcial
```

---

## 13. STACK TECNOLÓGICO

```
Servidor local:   PHP 8+ | MySQL 8+ | Apache o Nginx
                  Corre dentro del gabinete del tótem en red local

Adaptador actual: Python 3.10+ | FastAPI | webexevents SDK
                  mini_api.py — ya funciona, no tocar

App tótems:       Unity 2022+ | Android 10+ | C#
                  Lector HID via Input.inputString

Panel admin:      PHP + HTML/CSS/JS vanilla o framework ligero
                  Accesible desde cualquier dispositivo en la red local

Base de datos:    MySQL 8 con soporte JSON nativo
```

---

## 14. ROADMAP DE DESARROLLO

```
Fase 1  →  Schema SQL ejecutable con todos los índices         ← PRÓXIMO PASO
Fase 2  →  PHP Core API: POST /api/lectura con lógica completa
Fase 3  →  PHP Panel Admin: monitor en tiempo real + agenda
Fase 4  →  Unity App: HID capture + cola offline + pantallas
Fase 5  →  Proceso de cálculo de asistencia_calculada
Fase 6  →  Exportación CSV/Excel para reportes al cliente
Fase 7  →  Adaptador CSV genérico para clientes sin API externa
```

---

## 15. DECISIONES DE DISEÑO IMPORTANTES

1. **Timestamps del servidor son la verdad absoluta.** Los del dispositivo son solo referencia.
2. **asistencia_calculada es regenerable.** Siempre se puede borrar y recalcular. Nunca es fuente de verdad.
3. **hora_inicio_original nunca se modifica.** Trazabilidad completa de cambios de agenda.
4. **El tótem no decide nada.** Solo escanea y muestra. Toda la lógica vive en el servidor.
5. **El adaptador produce el contrato estándar.** El core no sabe de dónde vienen los datos.
6. **estado_asistentes es caché de la verdad.** Se reconstruye desde movimientos si es necesario.
7. **El cálculo de asistencia no es en tiempo real.** Se ejecuta al final del día o bajo demanda.
8. **Debounce en servidor, no en tótem.** Para cubrir lecturas simultáneas desde diferentes puertas del mismo salón.
