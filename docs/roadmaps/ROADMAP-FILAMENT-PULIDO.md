# Roadmap Filament Pulido — Event Switcher + UX Enterprise

> Plan para llevar el admin Filament de "funciona pero feo" a "production-ready enterprise".
> Estado: PENDIENTE. No iniciar antes de cerrar features de negocio criticas (Web App, Landing).
> Prioridad dentro de Filament Pulido: Event Switcher es el unico bloqueante real de UX. El resto es polish.

---

## Por que existe este doc

El admin Filament hoy obliga al usuario a seleccionar un evento en CADA form que toca:
- Crear un Totem → Select Evento + Select Salon
- Otorgar Golden Ticket → Select Evento + Select Asistente + Select Patrocinador
- Crear Speaker → Select Evento
- ... 26+ Resources con el mismo patron repetido

El cliente (organizador de Eventos Efectivos) trabaja sobre UN evento a la vez. Cada vez que entra a Filament tendria que reseleccionar el evento en cada modulo. UX horrible.

`session('filament_event_id')` existe en el codigo (5 archivos lo leen) pero NUNCA se setea. Era la idea original — quedo a medio implementar.

**Solucion:** Event Switcher global en el topbar de Filament. Al seleccionar evento, TODA la UI se filtra a ese contexto. Los forms ya no preguntan "que evento" — lo asumen del switcher.

---

## Fase 1: Event Switcher Widget (~1.5h)

### 1.1 — Render hook topbar
- Crear `app/Filament/Widgets/EventSwitcher.php` o usar render hook `panels::topbar.start`
- Componente Livewire con Select de eventos (lista de `Event::orderByDesc('start_date')->get()`)
- Etiqueta visible: "Trabajando en: {nombre_evento}"
- Badge con estado del evento (live/draft/published)

### 1.2 — Persistencia y default
- Al cambiar Select → `session(['filament_event_id' => $value])` + redirect a misma URL para refrescar
- Middleware `SetFilamentEventContext` que se ejecuta en cada request del panel:
  - Si no hay session, default = primer evento activo (`Event::where('is_active', true)->orderByDesc('start_date')->first()`)
  - Si session apunta a evento eliminado, fallback al activo
- Persistir en cookie larga vida (7 dias) para sobrevivir logout

### 1.3 — Auth check
- Si user no es super_admin: solo mostrar eventos de SU organizacion (`whereIn('organization_id', $user->organizations->pluck('id'))`)

---

## Fase 2: Auditoria de Resources (~1.5h)

### 2.1 — Categorizar 34 Resources

**A. Event-scoped** (filtrar lista + remover Select Evento de form): Announcement, AccessCode, Banner, ChatSettings, CustomPage, Document, EmailLog, EmailTemplate, EventFaq, EventOnboarding, EventPhoto, EventPhotoSettings, EventRoom, EventSession, GoldenTicket, Highlight, Lead, LivePoll, Module, OnboardingSurveyOption, PassportSettings, PostEventSurvey, RegistrationField, RegistrationSettings, ReminderSettings, RewardResource, RoomTotem, ScheduledNotification, SessionRating, SessionTrack, SessionType, Speaker, Sponsor, SupportRequest, AttendeeAdmin, AttendeeBan, EventBranding, EventPulse, GamificationSettings, RateLimitSettings, EventOnboarding

**B. Org/system-level** (NO event-scoped): EventResource (es donde se crean los eventos), UserResource (staff/admins del sistema), OrganizationEmailSettings, WebhookEndpoint, WebhookApiKey

**C. Pages**: LiveGameResults, PrizeRedemptions, Data Center

### 2.2 — Patron a aplicar a cada Resource event-scoped

```php
public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
{
    return parent::getEloquentQuery()
        ->where('event_id', session('filament_event_id'));
}

public static function form(Form $form): Form
{
    return $form->schema([
        // QUITAR el Select Evento — viene del switcher
        // Hidden field para asignar al guardar:
        Forms\Components\Hidden::make('event_id')
            ->default(fn () => session('filament_event_id')),
        // ... resto de campos
    ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            // QUITAR la columna event.name (todas las filas son del mismo evento)
        ])
        ->filters([
            // QUITAR el SelectFilter event_id
        ]);
}
```

### 2.3 — Pre-flight check
- Si `session('filament_event_id')` esta vacia, mostrar empty state: "Selecciona un evento para empezar" + link al switcher
- Si user no tiene permiso sobre el evento activo, redirect

---

## Fase 3: Limpieza forms duplicados (~1h)

Quitar Selects "Evento" de TODOS los forms migrados. Auditar:

- AnnouncementResource form line 30-31
- BannerResource form line 30-35
- CustomPageResource form line 29-30
- DocumentResource form line 30-31
- EmailTemplateResource form line 32-37 (mantener nullable para system templates)
- EventFaqResource form line 29-33
- EventRoomResource form line 29-33
- HighlightResource form line 32-36
- LivePollResource form line 37-42
- ModuleResource form line 54-58
- OnboardingSurveyOptionResource form line 29-34
- PostEventSurveyResource form line 34-38
- RegistrationFieldResource form line 31-36
- RoomTotemResource form line 29-35 (event_id) y line 36-40 (room_id queda — event scoped pero room dentro)
- ScheduledNotificationResource form line 32-36
- SessionTrackResource form line 28-32
- SessionTypeResource form line 30-34
- SpeakerResource form line 30-31
- SponsorResource form line 31-36
- AttendeeAdminResource form line 39-43
- EventOnboardingResource form line 240+ (field_ids)
- EventSessionResource form line 34-38

Tambien quitar de actions/headerActions:
- GoldenTicketResource action create_ticket (recien agregado por workaround — puede revertirse cuando exista switcher)
- AttendeeAdminResource\Pages\ListAttendeeAdmins (3 actions: plantilla, importar, exportar)
- EventPhotoResource headerActions (award_top_contest, upload_official)

---

## Fase 4: Breadcrumb contextual (~30min)

Render hook `panels::breadcrumbs.start` que prepende el nombre del evento activo:

```
Summit Empresarial 2026 / Asistentes / Editar Juan Perez
```

Refuerza el contexto y le recuerda al usuario sobre que evento esta operando.

---

## Fase 5: Filament UI Enterprise (Niveles 1-3) (~4-6h)

> Solo despues de cerrar Event Switcher. Es polish, no funcionalidad.

### Nivel 1 — Columns y labels
- Auditar todos los Resources: labels espanol, plural model labels correctos
- Secciones con icon + description
- Custom theme: tipografia, paleta amber consistente con app
- Iconos heroicons consistentes

### Nivel 2 — Tabs por recurso
- EventBrandingResource: tabs Colores / Hero / Links / Tema
- GamificationSettingsResource: tabs Puntos / Reglas / Premios / Concurso
- RegistrationFieldResource: tabs por show_in
- AttendeeAdminResource: tabs Datos / Tags / Historial / Lifecycle

### Nivel 3 — Wizards features complejos
- Crear evento: ya existe wizard (CreateEvent.php) — confirmar que esta completo
- Crear LivePoll con preguntas: actualmente Repeater anidado — convertir a wizard si se vuelve confuso

---

## Estimacion total

| Fase | Horas | Bloqueante |
|------|-------|------------|
| 1. Event Switcher Widget | 1.5h | Si |
| 2. Auditoria Resources | 1.5h | Si |
| 3. Limpieza forms duplicados | 1h | Si |
| 4. Breadcrumb contextual | 0.5h | No |
| 5. UI Enterprise Niveles 1-3 | 4-6h | No (polish) |
| **Total core** | **~4.5h** | Switcher operativo |
| **Total con polish** | **~10h** | Filament production-ready |

---

## Decisiones tomadas

- **NO usar Filament Tenancy nativo** (`->tenant(Event::class)`) — cambia URLs (`/admin/events/{event}/totems`), breaking change masivo, pelea con auth Spatie. Veredicto: la opcion B (session-based switcher) es mas pragmatica a 5 meses del deal.
- **Default automatico al primer evento activo** — evita pantalla vacia al login.
- **Cookie persistente 7 dias** — usuario no pierde contexto entre sesiones.
- **EventResource (donde se crean eventos) NO se filtra** — es el unico lugar donde ves todos los eventos.
- **GoldenTicket modal con event_id Select** (workaround actual) se puede revertir cuando exista switcher — pero se mantiene como fallback si user es super_admin sin evento activo.

---

## Riesgos

1. **Breaking change para usuarios existentes** — su workflow actual asume seleccion en cada form. Mitigacion: tutorial in-app, banner explicativo primer login post-deploy.
2. **Eventos compartidos entre orgs** — modelo actual tiene `organization_id`, hay que respetarlo en switcher (filtrar eventos por org del user).
3. **Tests funcionales** — hay 769 tests que asumen creacion via form sin switcher. Auditar tests post-implementacion.
4. **Recursos con event_id nullable** — EmailTemplate puede tener `event_id = null` (template sistema). Manejar caso especial.

---

## Referencias

- Filament v3 render hooks: https://filamentphp.com/docs/3.x/support/render-hooks
- Filament tenancy (NO usaremos): https://filamentphp.com/docs/3.x/panels/tenancy
- Codigo actual con `filament_event_id`: GoldenTicketResource, RewardResource, EventPhotoResource, LiveGameResults, PrizeRedemptions
