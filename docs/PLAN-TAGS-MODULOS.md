# Plan: Tags + Visibilidad Modulos + Layout Unificado

> Arquitectura nueva para reemplazar roles presencial/virtual por tags + check-in.
> Fecha: 2026-04-13 | Estado: Aprobado, pendiente implementar

---

## Problema

- Roles presencial/virtual son estaticos y no reflejan la realidad
- Un virtual puede llegar al venue (se convierte en presencial)
- Un presencial puede ver streaming desde casa
- Se necesitan grupos arbitrarios (VIP, prensa, speaker) para contenido exclusivo
- Dos layouts duplicados (presencial tabs / virtual tabs) es mantenimiento doble
- El admin no deberia cambiar roles manualmente para 5000 personas

## Solucion

Eliminar presencial/virtual como roles. Usar:
- **`checked_in_at`** (ya existe) → determina si la persona llego al venue
- **`tags`** JSON en attendee → grupos arbitrarios que el admin define (vip, prensa, etc.)
- **Visibilidad por modulo** → cada modulo dice: que presencia requiere + que tags

## Modelo de datos

### Attendee (cambios)

```
tags           JSON     default '[]'     -- ej: ["vip", "prensa"]
role           enum     'attendee'       -- ya no 'presencial'/'virtual'
                                         -- solo: attendee, vendedor, admin, admin_*
checked_in_at  datetime null             -- YA EXISTE, no cambia
```

### Event Modules (cambios)

```
visibility_presence  enum    'all'        -- all | checked_in | not_checked_in
visibility_tags      JSON    null         -- null = todos | ["vip"] = solo esos tags
```

### Logica de filtrado API

```
GET /events/{id}/modules

Para cada modulo:
1. Si visibility_presence = 'checked_in'     AND attendee.checked_in_at IS NULL → ocultar
2. Si visibility_presence = 'not_checked_in'  AND attendee.checked_in_at IS NOT NULL → ocultar
3. Si visibility_tags != null                 AND attendee.tags no intersecta → ocultar
4. Si pasa todo → mostrar
```

## Roles simplificados

| Rol | Para que | Layout |
|-----|----------|--------|
| `attendee` | Todos los asistentes (ex-presencial, ex-virtual) | Layout unico |
| `vendedor` | Mi Stand, Leads, Scanner | Layout vendedor |
| `admin` variants | Filament, moderacion | Admin |

## Flujos de usuario

### Registro normal (sin pre-registro)

1. Landing → registra → app → onboarding → Home
2. tags: [], checked_in: null
3. Ve todos los modulos con visibility_presence=all y visibility_tags=null
4. Si llega al venue → check-in → modulos checked_in aparecen via socket

### Pre-registro VIP (CSV import)

1. Admin sube CSV: nombre, email, tag(s)
2. Backend crea User sin password + Attendee con tags ["vip"]
3. VIP recibe email invitacion
4. Abre app → email detectado como pre-registrado → "Crea tu contrasena"
5. Onboarding → Home → ya ve modulos con tag "vip" (ej: Zona VIP)
6. Si llega al venue → check-in → ve modulos checked_in + vip (ej: Dress Code)

### Empleado Bancolombia (virtual que se convierte en presencial)

1. Se registra normal → tags: [], checked_in: null
2. Abre app desde escritorio → ve streaming, agenda, social, networking
3. Baja al evento → staff escanea QR → checked_in_at = now()
4. Socket → app refetch modulos → Mapa Venue aparece
5. Automatico, zero intervencion admin

## Ejemplo configuracion modulos (Bintec Bancolombia)

| Modulo | visibility_presence | visibility_tags | Quien lo ve |
|--------|--------------------|-----------------| ------------|
| Agenda | all | null | Todos |
| Speakers | all | null | Todos |
| Streaming | all | null | Todos |
| Social Wall | all | null | Todos |
| Networking | all | null | Todos |
| Chat | all | null | Todos |
| Gamificacion | all | null | Todos |
| Mi QR | all | null | Todos (QR es identidad, no solo ticket) |
| Mapa Venue | checked_in | null | Los que llegaron al venue |
| Dress Code | checked_in | ["vip"] | VIP que llegaron |
| Zona VIP | all | ["vip"] | VIP siempre (aun remoto) |
| Sala Prensa | all | ["prensa"] | Solo prensa |
| Info Stands | checked_in | null | Los que estan en el venue |

## Real-time (socket)

Cuando staff escanea QR de Pepito:
1. Backend: checked_in_at = now()
2. Backend: POST /internal/checkin → Socket.IO
3. Socket emite a room event:{id}: attendee:checkin { attendee_id: 42 }
4. App de Pepito: escucha, compara con su attendee_id
5. Si match → invalidateQueries(['modules']) + actualiza store
6. Modulos nuevos aparecen en 1-2 segundos sin reabrir app

## Analytics / ROI (derivado, sin campos nuevos)

```
Dashboard:
├── Registrados: COUNT(attendees)
├── Presenciales: checked_in_at IS NOT NULL
├── Streaming: activity_log action=stream_join sin check-in
├── Hibridos: check-in + stream_join
├── Solo registro: ni check-in ni stream
├── Por tag: GROUP BY tags
├── VIP presenciales: tag vip + checked_in_at
└── Engagement por modulo: activity_log por module_slug
```

Todo derivado de datos existentes (checked_in_at + activity_log + tags).

## Implementacion (orden)

### Paso 1: Backend — Tags + Visibilidad (1-2h)

- [ ] Migration: agregar `tags` JSON a attendees (default '[]')
- [ ] Migration: agregar `visibility_presence` y `visibility_tags` a event_modules
- [ ] Model Attendee: cast tags como array, helper hasTags()
- [ ] Model EventModule: cast visibility_tags como array
- [ ] API modules: filtrar por checked_in_at + tags del attendee
- [ ] Filament EventModuleResource: selectores de visibility
- [ ] Filament AttendeeResource: campo tags editable
- [ ] Tests: modulo visible/oculto segun presencia + tags

### Paso 2: Backend — Pre-registro CSV (1h)

- [ ] Import CSV en Filament: nombre, email, tags
- [ ] Crear User sin password + Attendee con tags
- [ ] Email invitacion con deep link
- [ ] Auth check-email: detectar pre-registrado → flujo "Crea tu contrasena"
- [ ] Activate account: crear password + onboarding

### Paso 3: Backend — Socket check-in trigger (30min)

- [ ] Socket attendee:checkin incluir attendee_id en payload (verificar)
- [ ] App: useDataInvalidation escucha attendee:checkin para el propio usuario
- [ ] Al match: invalidar modules + actualizar authStore.checkedInAt

### Paso 4: App — Layout unificado (2-3h)

- [ ] Crear /(app)/(tabs)/ unificado (merge presencial + virtual)
- [ ] Home: ModuleMenu dinamico segun modulos visibles del API
- [ ] Mi QR: visible para todos (QR = identidad)
- [ ] Eliminar /(app)/(presencial)/(tabs)/ y /(app)/(virtual)/(tabs)/
- [ ] index.tsx: rutar a /(app)/(tabs)/ para todos los attendees
- [ ] Vendedor mantiene su layout separado

### Paso 5: Cleanup (30min)

- [ ] Eliminar role 'presencial' y 'virtual' del enum (migration)
- [ ] Actualizar AuthService: role default = 'attendee'
- [ ] Actualizar authApi.ts y authStore types
- [ ] Actualizar check.approval si filtra por role
- [ ] Quitar restriccion de role en GET /me/qr (todos tienen QR)

## Estimacion total: ~6-8 horas (1 sesion completa)

## Riesgos

- Refactor layout es el paso mas grande — tocar tabs, routing, _layout.tsx
- Tests que dependen de role='presencial' van a fallar — actualizar en batch
- El vendedor mantiene layout propio, no se toca

## Decision clave

> El check-in es el trigger automatico. El admin solo define tags por CSV.
> No hay tipos de evento. No hay roles presencial/virtual. Solo modulos inteligentes.
