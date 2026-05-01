# Roadmap Recap Compartible — Post-Evento

> Reemplazo del PDF de certificado tradicional. Tarjeta editorial pop, **certificable y verificable**, con flip 3D al reverso (detalle de sesiones). Sistema 100% configurable desde Filament reusando arquitectura del onboarding.
>
> **Estado:** PENDIENTE. Disenio aprobado v6 — implementacion no iniciada.
>
> **Fecha aprobacion disenio:** 2026-04-26.

---

## ⚠️ AVISO CRITICO — sobre los demos visuales

En `design/recap-v[1-6]/` hay 6 demos HTML iterativos. **Son SOLO REFERENCIA VISUAL** para alinear estetica antes de implementar. NO copiar/pegar codigo. NO importar CSS ni JSX.

El demo final aprobado es **v6** (`design/recap-v6/Recap.html`). Sirve para:
- Ver la composicion final de cover + card
- Validar comportamiento del tier badge, flip 3D, color signature, lockup
- Probar perfiles edge case (sin foto, 1 charla, sin actividad)
- Mostrar al cliente sin levantar el stack real

Al implementar **RE-CREAR todo en el stack correspondiente**:

| Pieza | Stack real |
|---|---|
| App movil (cover + card preview + flip) | React Native + Expo + Reanimated |
| Filament resource + preview | Blade + Livewire (NO React) |
| PNG generado server-side | Spatie Browsershot + Blade template + Chromium |
| Halos / blur radial | Solo en backend PNG (Chromium soporta). En RN: gradients lineales + sun rays SVG via `react-native-svg` (sin Skia) |

**Reglas de implementacion (no negociables):**
- Botones siguen linea grafica del **tema activo Luz / Noir** (NO inventar gradients propios)
- **Safe areas siempre**: `useSafeAreaInsets().top + 16` arriba, `bottom + 16` abajo en mobile
- **Solo 2 fuentes**: `Plus Jakarta Sans` (display) + `Urbanist` (body). NO JetBrains Mono. Lo "mono" se simula con Urbanist 600 + tracking 0.18em + uppercase
- Glass solo en piezas premium (max 3-4 por pantalla)
- NO duplicar el sistema de halos del demo en RN — usar la implementacion adaptada (sun rays SVG estaticos)

---

## Decisiones de producto (validadas)

### Filosofia
- **Reemplaza el PDF de asistencia tradicional**. Es certificado verificable, no resumen viral
- **2 pantallas swipeables** (no scroll vertical Wrapped) — cover + card final
- **Sin "sesion favorita"** — vanidad individual, no certifica permanencia
- **Sin stats agregadas del evento** ("47 sesiones, 1247 asistentes") — ruido
- **Sin ranking comparativo** ("Top 12%") — competencia silenciosa que duele
- **Solo datos auditables**: tiempo en vivo + sesiones + dias + tier (calculado por tiempo)
- **Threshold**: si attendee tiene 0 sesiones Y 0 horas → NO se genera recap (ni email)

### Tiers (sistema nuevo, validado)
Sistema de 3 niveles por horas asistidas:

| Tier | Horas | Color | Roman |
|---|---|---|---|
| **Insider** | `< 3h` | cyan `#22D3EE` | I |
| **Activo** | `3 — 8h` | magenta `#FF2E93` | II |
| **Headliner** | `≥ 8h` | amber `#F59E0B` | III |

- Naming validado por usuario (no bronze/silver/gold, no semaforo)
- Insignia con clip-path asimetrico tipo escudo + glyph romano + label + halo glow del color del tier
- Color del tier solo afecta el badge, NO el resto del recap (que sigue paleta del evento)

### Card final compartible — composicion
- **Header IG style**: avatar circle + handle + role + "···"
- **Protagonista**:
  - Si attendee tiene foto → foto circular 118px + nombre 32px debajo
  - Si NO tiene foto (default) → nombre 56px display 800 protagonista + iniciales watermark 280px detras opacidad 0.06
- **Tier insignia** asimetrica
- **Bloque CERTIFICADO**: tiempo en vivo + sesiones + dias (3 stats grid)
- **Bloque EVENTO**: nombre del evento (texto O lockup imagen) + fecha. Anclado al fondo del card
- **Footer**: handle + serial number + URL verificacion
- **Detalles editoriales**:
  - Esquinas asimetricas tipo ticket: `border-radius: 24px 6px 24px 6px`
  - Borde holografico (gradient diagonal con mask composite)
  - Pattern de seguridad sutil (lineas finas opacidad 0.05)
  - Watermark iniciales 280px opacidad 0.06

### Card flip 3D (reverso del certificado)
Tap a la card → `rotateY(180deg)` con spring `cubic-bezier(0.4, 0.0, 0.2, 1)` 0.9s.

Reverso muestra:
- Header "Detalle" + sello "Auténtico"
- Lista de sesiones registradas con timestamps (max 8 visible, "+N mas" si excede)
- Numero de serie grande: `FUTURESTACK·2026·#0142`
- QR detallado 60×60 + URL verificacion en texto
- Pattern lineas diagonales sutil de fondo

En RN: `Animated.View` con `transform: [{ perspective }, { rotateY }]` controlado por Reanimated `useSharedValue` + `withSpring`.

### Cover (pantalla 1)
- Background gradient radial con c1+c2 del evento
- Sun rays SVG 2 capas rotando opuestas (60s y 90s)
- Sparkles flotando suavemente (transform Y -8px loop)
- Iniciales watermark 360px opacidad 0.04 detras de todo
- Pill arriba con `eventName` (texto) o **mini lockup** (si modo imagen)
- Si attendee tiene foto: foto circular grande + nombre debajo (estilo card)
- Si NO tiene foto: nombre 88px display 800 protagonista
- Footer: dias + boton CTA "Ver mi recap" pill blanco con shine sweep al hover

### Color signature personal (opcional)
Toggle por evento en `recap_config`:
- **Modo "Color del evento"**: organizador define c1+c2 — todos los attendees ven misma paleta
- **Modo "Signature personal"**: hash deterministico del nombre del attendee → 2 colores HSL unicos. Mismo attendee = misma paleta evento a evento

Helper:
```js
function colorSignature(name) {
  const h = hashName(name); // hash simple JS
  return {
    c1: `hsl(${h % 360}, 80%, 58%)`,
    c2: `hsl(${(h * 7 + 47) % 360}, 70%, 42%)`,
  };
}
```

### Lockup del titulo evento (configurable)
3 modos:
- **Texto** (default): nombre evento Plus Jakarta Sans 800 36px (card) / pill blanco 10px (cover)
- **Imagen lockup horizontal**: PNG transparente subido por organizador, max 800×400px
- **Imagen lockup poster vertical**: PNG transparente, max 600×800px

Aplica en card final (grande) y cover (mini). Fallback automatico a texto si no hay imagen.

### Distribucion Fase 1 (recortado)
- **Email universal con CTA** — unico canal Fase 1
- **URL firmada HMAC**: `eventos.app/r/{verifyCode}` — vive 1 año
- **Sin push notifications** Fase 1
- **Sin pantalla "Mis Recaps"** en perfil Fase 1
- **Sin banner Home post-evento** Fase 1
- **Sin variantes 1080×1080 LinkedIn** Fase 1

---

## Patron arquitectonico — reusa onboarding S1.x-B3

El recap usa **la MISMA arquitectura del onboarding configurable**. Esto ahorra ~40% del backend.

| Pieza onboarding (existente) | Adaptacion al recap |
|---|---|
| `events.onboarding_steps_config` (JSON) | `events.recap_config` (JSON) |
| `EventOnboardingResource` (Filament tabs) | `EventRecapResource` (Filament tabs) |
| `OnboardingPreview` page split-view | `RecapPreview` page split-view |
| `OnboardingController::resolveStepsConfigUrls()` | `RecapController::resolveConfigUrls()` (clonar helper) |
| Master/slave colores | Mismo patron |
| Fallback hardcoded si JSON null | Mismos defaults (los del demo v6) |
| `OnboardingSeeder` template | `RecapConfigSeeder` |
| API publica con resolve URLs | Misma firma |

### Estructura `events.recap_config` JSON

```json
{
  "enabled": true,
  "branding": {
    "title": {
      "type": "text",
      "image_url": null,
      "text_fallback": "FUTURESTACK"
    },
    "color_primary": "#FF2E93",
    "color_secondary": "#7C3AED",
    "use_signature_per_attendee": false
  },
  "tiers": {
    "show": true,
    "thresholds": { "insider_max_h": 3, "activo_max_h": 8 },
    "labels": {
      "insider": "Insider",
      "activo": "Activo",
      "headliner": "Headliner"
    }
  },
  "blocks": {
    "header_ig": { "show": true, "show_role": true },
    "protagonist": { "auto_photo_or_name": true },
    "certificate": {
      "show": true,
      "label": "Certificado de asistencia",
      "stats": ["time", "sessions", "days"]
    },
    "footer": { "show_serial": true, "show_verify_url": true },
    "back_side": { "show_sessions": true, "show_qr": true }
  },
  "cover": { "cta_text": "Ver mi recap" },
  "share": { "ig_button_text": "Compartir en Stories" }
}
```

### Variables HARDCODED (vienen del attendee, NO se configuran)

- `name, handle, role, photo_url`
- `hours, minutes, sessions_count, days_count`
- `tier` (calculado por horas)
- `serial`, `verify_code` (generados por hash)
- `sessions_list` (para reverso flip)

---

## Fase 0: Setup arquitectura base (~2h) — 0/6

> Reusar al maximo el patron del onboarding. NO inventar.

### 0.1 Migrations — 0/2
- [ ] `add_recap_config_to_events_table.php` — campo `recap_config` JSON nullable
- [ ] `add_recap_fields_to_attendees_table.php` — `recap_data` JSON, `recap_image_url` string, `recap_serial` string, `recap_verify_code` string indexed, `recap_generated_at` timestamp

### 0.2 Service base — 0/2
- [ ] `app/Services/RecapConfigResolver.php` — clonar firma de `OnboardingController::resolveStepsConfigUrls()`. Resuelve paths relativos a URLs absolutas (asset()), aplica defaults
- [ ] Tests Pest del resolver con config null, parcial, completo

### 0.3 Seeder — 0/2
- [ ] `database/seeders/RecapConfigSeeder.php` con template completo (los defaults del demo v6)
- [ ] Aplicar a evento demo

---

## Fase 1: Backend datos + tier service (~3h) — 0/9

### 1.1 RecapService — 0/4
- [ ] `app/Services/RecapService.php`
- [ ] `generateForAttendee(Attendee $a, Event $e): array` — devuelve estructura JSON completa
- [ ] Submetodos: `getTimeStats()`, `getSessionsCount()`, `getDaysCount()`, `getSessionsList()`
- [ ] Cache resultado en Redis 24h, key `recap:{event_id}:{attendee_id}`

### 1.2 Tier calculation — 0/2
- [ ] `calculateTier(int $hours, array $thresholds): string` — devuelve "insider"|"activo"|"headliner"
- [ ] Lee thresholds desde `recap_config.tiers.thresholds` (con fallback 3/8)

### 1.3 Hash determinístico — 0/1
- [ ] `generateSerial(Attendee $a, Event $e): string` — formato `{EVENT_NAME}·{YEAR}·#{0000}` desde hash nombre+evento. Mismo attendee = mismo serial siempre

### 1.4 Endpoint API — 0/1
- [ ] `GET /api/v1/event/{event}/my-recap` — devuelve JSON completo (config resuelta + datos calculados). Auth: solo el propio attendee (RecapPolicy)

### 1.5 Tests — 0/1
- [ ] `tests/Feature/Recap/RecapServiceTest.php` — Pest, ~12 tests cubriendo cada metodo + edge cases (1 charla, sin actividad, threshold)

---

## Fase 2: Backend imagen PNG + storage (~3h) — 0/8

### 2.1 Setup Browsershot — 0/2
- [ ] `composer require spatie/browsershot` (verificar Puppeteer/Chromium en VPS)
- [ ] Test render simple HTML → PNG en local

### 2.2 Template Blade compartible — 0/3
- [ ] `resources/views/recap/share-card.blade.php` — vertical 1080×1920 (Instagram Story)
- [ ] Re-crear composicion v6 en Blade (NO importar el HTML del demo). Componentes:
  - Header IG, Photo/Name protagonista, Tier insignia, Cert block, Event block, Footer (serial + verify)
- [ ] Fonts WOFF2 bundled en `public/fonts/`: Plus Jakarta Sans variable + Urbanist variable

### 2.3 Job + upload R2 — 0/2
- [ ] `app/Jobs/GenerateRecapImageJob.php` — recibe attendee_id + event_id, llama RecapService, render Blade, Browsershot a PNG, sube a R2 (`recaps/{event_id}/{attendee_id}.png`)
- [ ] **Idempotencia**: skip si `recap_generated_at != null` salvo `force=true`. Guarda URL en `attendees.recap_image_url`

### 2.4 Tests — 0/1
- [ ] `tests/Feature/Recap/GenerateRecapImageJobTest.php` — mock Browsershot, verifica subida R2 (Storage::fake), guarda URL, idempotencia

---

## Fase 3: Filament EventRecapResource + Preview (~3h) — 0/8

> Clona estructura `EventOnboardingResource` y `OnboardingPreview`.

### 3.1 EventRecapResource — 0/4
- [ ] `app/Filament/Resources/EventRecapResource.php` con tabs:
  - **General**: enabled toggle, modo color (evento/signature), color picker primary+secondary
  - **Branding**: titulo (texto/imagen) — FileUpload + texto fallback, keyvisual upload
  - **Tiers**: thresholds insider_max + activo_max, labels custom
  - **Bloques**: cada bloque enabled toggle + opciones (cert label, stats array, footer show_serial, back_side toggles)
  - **Textos**: cover.cta_text, share.ig_button_text
- [ ] Validacion: keyvisual debe ser PNG/SVG <2MB
- [ ] Disclaimer en uploads: "PNG transparente recomendado, color claro o tu marca"
- [ ] Permiso solo a admin / organizador del evento

### 3.2 RecapPreview page — 0/2
- [ ] `app/Filament/Pages/RecapPreview.php` — split editor + mockup live (clona OnboardingPreview)
- [ ] wire:model.live para reflejar cambios en tiempo real en el mockup
- [ ] Mockup usa attendee dummy "Maria Salgado" para preview

### 3.3 AttendeeResource action — 0/2
- [ ] Boton "Regenerar recap" en `AttendeeResource` (action) — dispara `GenerateRecapImageJob` con `force=true`
- [ ] Toast confirmation + link al PNG resultante

---

## Fase 4: Email + URL firmada + trigger (~2h) — 0/9

### 4.1 RecapMail — 0/2
- [ ] `app/Mail/RecapReadyMail.php` con view `emails.recap-ready`
- [ ] Template HTML branded con `primary_color`, logo, nombre evento, stats teaser, CTA "Ver mi Recap"

### 4.2 EmailTemplate type — 0/2
- [ ] Agregar opcion `recap_ready` al Filament Select de `EmailTemplateResource` (NO es enum, es string libre con `EmailTemplate::resolve()`)
- [ ] Seeder default template con variables documentadas (`{{attendee_name}}`, `{{event_name}}`, `{{recap_url}}`, `{{tier}}`)

### 4.3 Endpoint URL publica firmada — 0/2
- [ ] `GET /r/{code}` — controller valida verify_code, redirect a deeplink app o fallback web. Throttle `60,1` por IP
- [ ] Fase 2 (webapp): mismo endpoint sirve vista HTML del recap directamente

### 4.4 Observer + orquestador — 0/3
- [ ] `EventObserver::updated()` detecta `status=ended` → dispatch `GenerateRecapsForEventJob` (solo eventos que finalicen DESPUES del deploy)
- [ ] `GenerateRecapsForEventJob` itera attendees, encadena `GenerateRecapImageJob` (chunk 50, throttle queue)
- [ ] Al completar imagen: dispatch `SendRecapReadyEmailJob`
- [ ] `EventObserver::deleted()` cleanup PNG R2 huerfanos

---

## Fase 5: App movil — 2 pantallas + flip 3D (~4h) — 0/12

> RN + Expo. Cero copiar del HTML demo. RE-implementar con react-native-svg, Reanimated, expo-linear-gradient.

### 5.1 Hook + types — 0/2
- [ ] `hooks/useRecap.ts` — TanStack Query, fetch `/event/{id}/my-recap`, cache MMKV 24h
- [ ] Types TypeScript completos para `RecapData`, `RecapConfig`, `Tier`

### 5.2 RecapScreen — 0/2
- [ ] `app/(app)/recap/[eventId].tsx` — pantalla con horizontal `FlatList` (snap, paginacion 2 paginas)
- [ ] **Safe areas**: `useSafeAreaInsets().top + 16` en topbar, `bottom + 24` en botones inferiores

### 5.3 Componentes — 0/5
- [ ] `components/recap/RecapCover.tsx` — pantalla 1
- [ ] `components/recap/RecapCard.tsx` — pantalla 2 con flip 3D usando Reanimated `rotateY` + `useSharedValue`
- [ ] `components/recap/RecapTier.tsx` — insignia con clip-path simulado via `MaskedView` o SVG
- [ ] `components/recap/RecapLockup.tsx` — decide texto vs `<Image>` segun config
- [ ] `components/recap/RecapBack.tsx` — reverso del flip con sesiones list

### 5.4 Botones (linea grafica del tema) — 0/1
- [ ] CTAs y botones de accion siguen el tema activo Luz/Noir. NO inventar gradients propios. Usar tokens existentes (`useTheme()`)
- [ ] Boton primary "Compartir en Stories" usa colores del evento (c1+c2) en gradient lineal nativo via `expo-linear-gradient`

### 5.5 Animaciones — 0/2
- [ ] Sun rays SVG 2 capas con `Animated.View` rotando (no usar Skia)
- [ ] Sparkles flotando con Reanimated `withRepeat(withSequence(...))`
- [ ] Counter up de horas en cert (`useDerivedValue`)

---

## Fase 6: QA + Edge cases (~1h) — 0/8

### 6.1 Edge cases backend — 0/3
- [ ] Attendee sin foto: usar nombre como protagonista (no avatar generico)
- [ ] Attendee con 1 sesion 0h 52m: card muestra "52m · 1 sesion · 1 dia"
- [ ] Attendee con 0/0: NO se genera recap (test que verifica que no hay PNG ni email)

### 6.2 Edge cases frontend — 0/3
- [ ] Imagen R2 no carga (offline): fallback a regenerar version simplificada local con react-native-view-shot
- [ ] Token expirado/manipulado: pantalla error amistosa
- [ ] **Nombres con descenders (g/p/q/y) NO se cortan**: line-height 0.95 + padding-bottom 0.08em (ya validado en demo v6)

### 6.3 Responsive — 0/1
- [ ] 360dp (ZTE) y 411dp (Pixel): nombres largos, lockup imagen, foto + nombre, sin foto

### 6.4 E2E manual — 0/1
- [ ] Crear evento demo, simular 5 attendees con datasets distintos, dispara `event.status=ended`, verificar emails recibidos + recap accesible + flip funcional

---

## Estimacion total

| Fase | Horas | Bloqueante |
|---|---|---|
| 0. Setup arquitectura | 2h | Si |
| 1. Backend datos + tier | 3h | Si |
| 2. Backend imagen PNG | 3h | Si |
| 3. Filament resource + preview | 3h | Si |
| 4. Email + URL + trigger | 2h | Si |
| 5. App 2 pantallas + flip | 4h | Si |
| 6. QA + edge cases | 1h | Si |
| **Total** | **~18h** | **3 dias work** |

> **Nota**: el demo v6 cubre 8.5h de "solo visual". La diferencia (~10h) es: setup arquitectura configurable estilo onboarding, Filament resource + preview, jobs/observers, email, tests, QA real. Todo eso NO esta en el demo y es indispensable.

---

## Datos backend requeridos — auditoria

| Stat | Tabla / fuente | Estado |
|---|---|---|
| Sesiones asistidas | `session_attendances` | OK existe |
| Horas en evento | calculado de attendances | OK derivable |
| Dias presente | DISTINCT date(checked_in_at) | OK derivable |
| Lista sesiones (reverso flip) | join sessions + attendances | OK derivable |
| Foto attendee | `attendees.avatar_url` | VERIFICAR campo exacto |
| Role/cargo | `attendees.job_title` + `company` | OK existe |
| Handle | derivar de email (nombre antes de @) | OK |

---

## Decisiones tecnicas archivadas

- **NO Skia client-side** — typography difiere iOS/Android, fonts custom dolorosos. Halos solo en backend PNG (Browsershot Chrome). En RN: SVG estatico via react-native-svg
- **NO musica de fondo** — distrae, conflicto con accesibilidad
- **NO Spotify Wrapped style** — opcion B descartada, edge cases dolorosos. 2 pantallas swipeables cumple
- **Imagen final 1080×1920 Instagram Story** prioritaria. Cuadrada 1080×1080 LinkedIn queda para Fase 2
- **Cookie/cache MMKV en app** — recap se descarga una vez, queda offline
- **R2 storage forever** — costo despreciable, sin servidor activo necesario
- **HMAC tokens reutiliza patron auth/staff** — verify_code es hash determinístico del nombre+evento+secret
- **Foto attendee en produccion**: `avatar_url` con cutout via Cloudinary `c_fill,g_face` o remove-bg. Fallback: nombre protagonista (no avatar geometrico generico)
- **Sistema configurable obligatorio**: NO hardcodear textos ni colores en la app. Todo lee de `recap_config` con defaults

---

## Riesgos

1. **Browsershot en VPS Linux**: Chromium consume RAM. Batch 1000 attendees x 15-25s = 4-7h, controlable con queue worker dedicado y throttle
2. **Datos faltantes en eventos viejos**: solo dispara recap para eventos que finalicen DESPUES del deploy de esta feature
3. **Tipografia en HTML server-side**: Plus Jakarta Sans + Urbanist deben estar bundled WOFF2 en `public/fonts/`. Sin esto, fallback feo
4. **R2 caida**: backend mantiene `recap_data` JSON, app puede regenerar version simplificada local con react-native-view-shot
5. **Lockup PNG mal formado**: validacion estricta en FileUpload Filament (max 2MB, PNG/SVG, transparencia)

---

## Que NO entra en Fase 1 (queda para Fase 2 webapp)

- Vista web HTML del recap (`eventos.app/r/{id}` solo redirect/deeplink Fase 1)
- Variante imagen cuadrada 1080×1080 LinkedIn
- Variante imagen 16:9 Twitter
- Multilenguaje (Fase 1 solo espanol)
- Push notifications
- Pantalla "Mis Recaps" en perfil
- Banner Home post-evento
- Editor avanzado de tiers custom (solo defaults validados)

---

## Referencia visual aprobada

**Solo `design/recap-v6/Recap.html`** es referencia oficial. Iteraciones intermedias (v1-v5) descartadas.

Carpeta `design/recap/` contiene refs visuales originales (Dribbble, Wrapped) + prototipo previo `Recap.html` con paletas Aurora/Ember/Mono — archivo historico, no usar como base.

### Iteracion del disenio (resumen historico, abr 2026)

1. **v1 spec inicial** — 7 secciones scroll Wrapped style, 26h. Descartado: edge cases dolorosos
2. **v2 scope reducido** — 5 secciones, accent platino. Descartado: estetica viejo burgues
3. **v3 editorial enterprise** — 2 pantallas sin halos, accent azul electrico. Descartado: "screenshot generico", sin personalidad
4. **v4 pop Wrapped** — halos, foto, colores vibrantes. Refs reales del usuario incorporadas. Aproximacion correcta pero faltaba protagonismo
5. **v5 certificado tier** — sistema Insider/Activo/Headliner por horas, sale "sesion favorita", entra certificacion. Casi final
6. **v6 DaVinci aprobado** — flip 3D al reverso, color signature opcional, lockup configurable (texto/imagen), nombres descenders fix, layout balanceado con/sin foto, evento anclado al fondo

Lecciones aprendidas:
- Estetica enterprise no significa frio editorial — significa coleccionable + verificable
- Eventos enterprise SI usan colores vibrantes (Linear violeta, Stripe magenta)
- La card final reemplaza al PDF, no es solo viral — debe certificar
- Tiers eliminan competencia silenciosa que duele (Spotify problem)
- Tiempo + sesiones + dias son los unicos datos auditables que importan

---

## Referencias externas

- Spotify Wrapped: https://www.spotify.com/wrapped/
- Strava Year in Review: https://blog.strava.com/year-in-sport/
- Spatie Browsershot: https://github.com/spatie/browsershot
- React Native Share: https://github.com/react-native-share/react-native-share
- React Native View Shot: https://github.com/gre/react-native-view-shot
- Patron arquitectonico onboarding: `app/Filament/Resources/EventOnboardingResource.php` + `app/Http/Controllers/Api/V1/OnboardingController.php`
