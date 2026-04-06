# EventOS — Prompt de contexto completo para Claude Code v2.0
*Versión actualizada con métricas, módulos faltantes, analytics y correcciones de arquitectura*

> Copia este documento completo al inicio de tu sesión en Claude Code.
> Es el contexto maestro del proyecto. Referéncialo en cada sesión nueva.

---

## 1. Descripción del producto

**EventOS** es una plataforma SaaS de gestión de eventos corporativos compuesta por cuatro superficies:

1. **App móvil** (Expo / React Native) — para asistentes y vendedores
2. **Web** (Next.js) — para usuarios virtuales y acceso sin app
3. **Admin panel** (Laravel Filament) — para el organizador del evento
4. **Kiosco de check-in** (app web local) — tablet fija en la entrada del evento

El modelo de negocio es cobrar por evento más por módulos activados. La app es el mismo binario para todos los clientes — lo que cambia es el branding y los módulos habilitados desde el admin.

---

## 2. Stack técnico definitivo

| Capa | Tecnología | Razón |
|---|---|---|
| App móvil | Expo (React Native) + Expo Router | iOS + Android desde un codebase |
| Estilos app | NativeWind (Tailwind para RN) | Familiar, rápido |
| Animaciones | Reanimated 3 + Lottie | Equivalente a GSAP en poder nativo |
| Web (virtual + admin light) | Next.js (React) | Comparte lógica con Expo |
| API REST | Laravel 11 | Auth, roles, queues, push, CRUD |
| Admin panel | Laravel Filament | Panel autogenerado sobre Laravel |
| Tiempo real | Node.js + Socket.IO | Solo chat y votación en vivo |
| Base de datos | MySQL 8 | Stack relacional, soporta 10,000+ usuarios con caché |
| Caché + Queues | Redis | Doble rol: caché de API (ms response) + driver de colas |
| Queue monitoring | Laravel Horizon | Monitorear jobs de push, CSV, emails en producción |
| Storage | Cloudflare R2 | Compatible S3, sin egress fees |
| CDN | Cloudflare | Cache de assets + proxy para la API (endpoints read-only) |
| Push notifications | Expo Push API | Laravel hace HTTP POST, Expo entrega |
| Autenticación | Laravel Sanctum | Tokens opacos SHA-256, revocables, expiran en 30 días |
| Roles y permisos | Spatie Laravel Permission | Estándar del ecosistema Laravel |
| Hash contraseñas | Argon2id (Laravel nativo) | Más resistente a GPU que Bcrypt |
| Deploy | VPS + Nginx + Laravel Forge | Laravel :8000, Node :3001 |
| Build app | EAS Build (Expo) | Compila iOS en nube sin necesitar Mac |
| OTA updates | EAS Update (Expo) | Hotfixes sin pasar por el store |
| Badges físicos | App web local + Zebra/Brother | Mini PC en red del evento |
| Analytics | Tabla activity_log propia en MySQL | No depende de terceros |
| Email transaccional | Mailgun (o SMTP fallback) | Invitaciones CSV, reset password, confirmaciones |
| Generación PDF | Browsershot (headless Chrome, Laravel) | PDFs pixel-perfect para reportes y certificados |
| Error tracking | Sentry | Monitoreo de errores en producción (Laravel + Expo) |
| CI/CD | GitHub Actions + Laravel Forge | Auto-deploy en push a main, staging automático |
| Deep links | Expo Linking + Universal Links | Links de invitación y reset abren la app directamente |
| Sanitización HTML | HTMLPurifier (Laravel) | Prevención XSS en custom pages con HTML del admin |
| Server state app | TanStack Query v5 (React Query) | Caché, background refetch, stale-while-revalidate automático |
| Storage app rápido | MMKV (react-native-mmkv) | 10x más rápido que AsyncStorage — para caché de módulos/evento |
| Storage app seguro | expo-secure-store | Token Sanctum — Keychain (iOS) / Keystore (Android). NUNCA AsyncStorage |
| Listas grandes | @shopify/flash-list | Reemplaza FlatList — C++, sin jank con 1000+ items |
| Imágenes app | expo-image | Disk cache automático, blur placeholder, mejor que Image nativo |
| Estado local app | Zustand | Estado UI (modals, filtros, tema) — sin boilerplate |
| Archivos locales | expo-file-system | Descarga y caché local de documentos PDF |
| Socket.IO escala | @socket.io/redis-adapter | Requerido para múltiples instancias Node — usuarios en distintos servidores se comunican |
| TypeScript | strict mode en todo el proyecto | tsconfig strict: true — requerido, no opcional |
| Calidad código | ESLint + Prettier (Expo) / Laravel Pint (PHP) | Estilo consistente en todo el codebase |
| Testing | Jest + Detox (E2E móvil) / Pest (Laravel) | Cobertura mínima: auth, QR, permisos, pagos |
| i18n | expo-localization + i18n-js | Español + Inglés desde Fase 1. Agregar idiomas después = reescribir toda la UI |
| Unity bridge | socket.io-unity (paquete Unity) | Juegos interactivos: Unity = pantalla del TV, teléfono = control |
| Dev tools | Laravel Telescope (solo local/staging) | Debug de requests, queries, jobs. NUNCA instalar en producción |
| Proceso Node prod | pm2 | Process manager: reinicio automático, cluster mode, logs |
| Proceso workers | Supervisor | Mantiene vivos los queue workers de Laravel en producción |
| Licencias | Campo subscription en organizations | Control de acceso por plan sin depender de Stripe en Fase 1 |
| Legal docs | Iubenda (~$27/año) | Privacy Policy + Terms of Service — obligatorios para App Store y Ley 1581 |

---

## 3. Arquitectura de performance y escalabilidad

### Por qué MySQL sigue siendo correcto

MySQL 8 con Redis como capa de caché aguanta 10,000+ usuarios concurrentes sin problema. El cuello de botella nunca es el motor de base de datos — es la falta de caché. Instagram y Airbnb corren en MySQL/PostgreSQL. La solución es siempre: caché + índices correctos, no cambiar de base de datos.

### Diagrama de flujo de una request de alto tráfico

```
App (10,000 usuarios)
  → Cloudflare CDN (cache edge para endpoints GET públicos)
    → Nginx
      → Laravel
        → Redis Cache (hit: ~1ms) ← 99% de las requests
        → MySQL (miss: ~5ms) ← solo si Redis no tiene el dato
```

### Estrategia de caché por endpoint

| Endpoint | TTL Redis | Invalidación |
|---|---|---|
| GET /api/events/{id}/sessions | 60s | Al guardar cualquier sesión del evento |
| GET /api/events/{id}/speakers | 5min | Al guardar cualquier speaker del evento |
| GET /api/events/{id}/modules | 30s | Al cambiar enabled/config de un módulo |
| GET /api/events/{id}/announcements | 30s | Al publicar un nuevo anuncio |
| GET /api/events/{id}/banners | 5min | Al cambiar banners |
| GET /api/events/{id}/sponsors | 5min | Al guardar/editar cualquier patrocinador del evento |
| GET /api/events/{id}/attendees | 30s | Al cambiar networking_visible de cualquier attendee |
| GET /api/events/{id}/documents | 5min | Al subir/eliminar un documento |
| GET /api/events/{id}/custom-pages | 5min | Al editar una página personalizada |
| GET /api/events/{slug} | 5min | Al editar datos del evento |

Clave Redis: `event:{id}:{resource}:{role}` — diferenciada por rol para devolver solo lo que corresponde.

### Aforo en tiempo real (check-in storm)

500 personas haciendo check-in al mismo tiempo = 500 escrituras concurrentes. **NO usar MySQL para el contador en tiempo real.**

```
Check-in request → validar QR (MySQL read, cacheado)
                → INCR event:{id}:aforo (Redis, atómico, microsegundos)
                → INSERT check_in en MySQL (async via Job)
                → broadcast aforo actualizado via Socket.IO al kiosco/admin
```

Redis `INCR` es atómico — imposible race condition. El valor real en MySQL se sincroniza cada 10 segundos via Job.

### Actualización de contenido (admin cambia horario de sesión)

```
Admin guarda → MySQL update
            → Redis cache invalidado (del(event:{id}:sessions:*))
            → Silent push a todos los asistentes del evento
            → Apps reciben push en background → fetch /api/events/{id}/sessions
            → Laravel sirve desde Redis recién populado
            → 10,000 apps ven el cambio en ~2-3 segundos
```

### Qué necesita WebSocket vs qué no

| Feature | Estrategia | Latencia aceptable |
|---|---|---|
| Agenda, speakers, docs, anuncios | Silent push → fetch Redis | 2-3 segundos OK |
| Aforo kiosco | Redis counter + polling 3s | 3 segundos OK |
| Chat entre asistentes | Socket.IO (Node) | Real-time obligatorio |
| Votación en vivo | Socket.IO (Node) | Real-time obligatorio |
| Dashboard admin stats | Polling 30s | 30 segundos OK |
| Módulos habilitados/deshabilitados | Silent push → fetch | 2-3 segundos OK |

### Estrategia de carga del app móvil (stale-while-revalidate)

**NO descargar todo al abrir la app.** Estrategia por tipo de dato:

| Dato | Cuándo cargar | Dónde guardar | TTL / invalidación |
|---|---|---|---|
| Config evento + branding | Al login | MMKV (síncrono) | Silent push al cambiar |
| Módulos del rol | Al login | MMKV | Silent push al cambiar |
| Token Sanctum | Al login | expo-secure-store | 30 días |
| Sesiones/Agenda | Al abrir tab Agenda | TanStack Query (disk cache MMKV) | 60s TTL + silent push |
| Speakers | Al abrir tab Speakers | TanStack Query | 5min TTL + silent push |
| Anuncios | Al abrir tab Anuncios | TanStack Query | 30s TTL + silent push |
| Documentos (lista) | Al abrir tab Docs | TanStack Query | 5min TTL |
| Documento (archivo PDF) | Al tocar "Descargar" | expo-file-system (disco local) | Permanente hasta reinstalar |
| Fotos/imágenes | Lazy, al renderizar | expo-image (disk cache automático) | 7 días |
| Mensajes de chat | Al abrir chat | TanStack Query + Socket.IO | En tiempo real |

**Flujo de pantalla con TanStack Query:**
1. Usuario abre Agenda → TanStack Query devuelve caché de MMKV **inmediatamente** (sin spinner)
2. En background: fetch a `/api/v1/events/{id}/sessions`
3. Si datos cambiaron → actualiza UI automáticamente
4. Si silent push llega → `queryClient.invalidateQueries(['sessions', eventId])` → refetch

**Invalidación por silent push:**
```
Backend envía: { type: 'cache_invalidate', resource: 'sessions', event_id: 1 }
App recibe en background → queryClient.invalidateQueries(['sessions', 1])
→ próxima vez que el usuario ve la pantalla: datos frescos
```

### Node.js + Socket.IO — arquitectura para 10,000 usuarios

```
                    ┌─────────────────────────────┐
10,000 apps ────────►  Node.js instancia 1 (:3001) │
                    │  + @socket.io/redis-adapter   │
                    └──────────────┬───────────────┘
                                   │
                              Redis Pub/Sub
                                   │
                    ┌──────────────┴───────────────┐
                    │  Node.js instancia 2 (:3002)  │  (si se necesita escalar)
                    │  + @socket.io/redis-adapter   │
                    └─────────────────────────────-─┘
```

**Rooms definidas:**
- `event:{id}` — anuncios broadcast a todos los asistentes del evento
- `chat:{id}` — sala de chat general del evento
- `dm:{minId}_{maxId}` — chat directo entre dos usuarios (IDs ordenados para evitar duplicados)
- `poll:{id}` — resultado de votación en vivo
- `checkin:{id}` — aforo en tiempo real para kiosco/admin
- `session:{id}` — preguntas al speaker en una sesión

**Al conectar:** Socket.IO valida token Sanctum llamando `GET /api/v1/auth/me` con el Bearer token → Laravel verifica el hash SHA-256 → retorna el user. Node NUNCA consulta la tabla de tokens directamente (los tokens están hasheados, Node no puede verificarlos sin reimplementar Laravel). Tras validación exitosa, une al usuario a los rooms de su evento y rol.

**Rate limiting en socket events:** máximo 1 mensaje/segundo por usuario en chat. Si excede → desconectar socket temporalmente (30s). Implementar con Redis: `INCR socket:{socketId}:rate` con TTL de 1s.

**Presencia a escala (quién está online):**
- `SADD event:{id}:online attendee:{id}` al conectar
- `SREM event:{id}:online attendee:{id}` al desconectar
- TTL de 35s con heartbeat cada 20s (si el cliente no hace heartbeat, Redis lo expira automáticamente)
- `SCARD event:{id}:online` = número de usuarios online (O(1))

**Mensaje perdido (usuario offline):**
- Socket.IO intenta entregar → usuario desconectado → mensaje se guarda en `chat_messages` (MySQL)
- Si es DM: Laravel envía push notification con preview del mensaje
- Al reconectar: app carga historial pendiente desde `GET /api/v1/events/{id}/chat/messages`

### Prevención de N+1 queries

Todas las respuestas de lista deben usar eager loading explícito:
```php
// MAL: genera N+1 queries
Session::where('event_id', $id)->get(); // + N queries para speakers

// BIEN: 2 queries totales
Session::where('event_id', $id)->with(['speakers', 'documents'])->get();
```

Regla: **nunca acceder a relaciones dentro de un loop**. Siempre `with()` en el query inicial.

### Paginación obligatoria en todos los endpoints de lista

```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 847,
    "last_page": 43
  }
}
```

Parámetros: `?page=1&per_page=20`. Default: 20 items. Máximo: 100.

### Specs de VPS recomendadas por tamaño de evento

| Evento | Asistentes | VPS mínimo |
|---|---|---|
| Pequeño | < 500 | 2 vCPU, 4GB RAM, 50GB SSD |
| Mediano | 500–2,000 | 4 vCPU, 8GB RAM, 100GB SSD |
| Grande | 2,000–10,000 | 8 vCPU, 16GB RAM, 200GB SSD + Read Replica MySQL |
| Enterprise | 10,000+ | Load balancer + 2 app servers + Redis cluster |

Redis corre en el mismo VPS hasta eventos > 5,000 asistentes. A partir de ahí, Redis dedicado.

---

## 4. Perfiles de usuario

### Asistente Presencial
- Asiste físicamente, tiene QR personal para presentar en stands
- Ve todos los módulos presenciales
- Recibe notificaciones físicas (almuerzo, sala, logística)
- NO las recibe el virtual
- Accede vía app móvil principalmente

### Asistente Virtual
- Asiste en línea. Su módulo core es la Transmisión embebida
- NO recibe notificaciones físicas
- Comparte con presencial: agenda, speakers, docs, anuncios, encuestas, chat
- Accede vía app móvil o web

### Vendedor / Stand
- Escanea QR de asistentes para capturar leads
- Ve datos del perfil al escanear
- Lista de leads con exportación CSV
- NO ve módulos de contenido del evento
- Accede vía app móvil

### Admin organizador (acceso total)
- Gestiona todo: contenido, módulos, notificaciones, asistentes, analytics
- Crea el evento, configura branding, invita admins limitados
- Accede vía web (Filament)

### Admin limitado (nuevo en v2)
- Roles específicos: solo contenido / solo notificaciones / solo analytics
- No puede cambiar configuración del evento ni módulos
- Accede vía web (Filament con permisos restringidos)

---

## 4. Seguridad — decisiones definitivas

```
Autenticación:   Laravel Sanctum — tokens opacos SHA-256, revocables
Hash passwords:  Argon2id → config/hashing.php: driver = argon2id
QR token:        HMAC-SHA256(user_id|event_id|created_at, APP_QR_SECRET)
                 Incluye event_id → expira entre eventos
                 Guardado en tabla qr_tokens para lookup
Roles:           Spatie → middleware role:vendedor / role:presencial|virtual
Rate limiting:   5/min login por IP, 60/min API general
Password reset:  Token firmado SHA-256, expira 60 min, tabla password_reset_tokens (nativa Laravel)
Socket auth:     Node llama GET /api/v1/auth/me con Bearer token → Laravel verifica → retorna user. NUNCA consultar tabla tokens directo desde Node (tokens hasheados SHA-256)
HTTPS:           Let's Encrypt, HSTS, X-Frame-Options, CSP básico
Ley 1581:        Consentimiento en registro, consent_logs, derecho a borrado
```

---

## 5. Sistema de métricas — activity_log

Toda la actividad del usuario se registra en una tabla append-only.
El frontend llama `POST /api/track` automáticamente vía el hook `useTracker`.

```sql
CREATE TABLE activity_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    attendee_id BIGINT NOT NULL,
    event_id BIGINT NOT NULL,
    module_slug VARCHAR(60),
    action VARCHAR(60),        -- open, click, close, scan, play, pause, scroll
    target_id BIGINT NULL,
    target_type VARCHAR(60),   -- session, banner, speaker, document, announcement
    duration_seconds INT NULL,
    metadata JSON NULL,        -- minuto abandono streaming, dispositivo, etc.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event (event_id),
    INDEX idx_attendee (attendee_id),
    INDEX idx_module (module_slug),
    INDEX idx_action (action),
    INDEX idx_created (created_at),
    FOREIGN KEY (attendee_id) REFERENCES attendees(id),
    FOREIGN KEY (event_id) REFERENCES events(id)
);
```

### Métricas que genera

**Paridad con Socio/Webex Events:**
- Total usuarios registrados vs activos
- Minutos en app por usuario y total
- Login por plataforma (iOS/Android/Web)
- Engagement total (clicks + tiempo + acciones)
- Clicks por módulo — ranking de uso
- Sentimiento del asistente (rating sesiones)

**Donde EventOS supera a Socio:**
- Tiempo real por módulo (minutos, no solo clicks)
- Drop-off streaming: en qué minuto exacto abandonó cada usuario
- Peak concurrencia simultánea por sesión
- Favoritos vs asistencia real
- Open rate y click-through por notificación push
- Métricas comparadas por rol (presencial vs virtual)

**Exclusivo EventOS:**
- Leads por stand + ranking de stands
- Perfil del lead promedio (empresa, cargo, sector)
- Mapa de calor de stands por visitas
- Hora pico de scans en muestra comercial
- Comparativa entre eventos del mismo cliente (Fase 3)

---

## 6. Arquitectura de módulos

```sql
CREATE TABLE modules (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL,
    slug VARCHAR(60) NOT NULL,     -- 'agenda', 'chat', 'votacion-vivo'
    name VARCHAR(120) NOT NULL,
    icon VARCHAR(60),
    enabled BOOLEAN DEFAULT true,
    roles JSON,                    -- ["presencial","virtual"]
    config JSON,                   -- configuración específica del módulo
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (event_id, slug),
    FOREIGN KEY (event_id) REFERENCES events(id)
);
```

Cuando admin cambia `enabled` o `config` → Laravel dispara push silenciosa
→ app recibe en background → llama `GET /api/events/{id}/modules`
→ menú se actualiza sin que el usuario toque nada.

---

## 7. Lo nuevo en v2 — módulos que faltaban

1. **activity_log tracking** — tabla base de todo el analytics
2. **Importación masiva CSV** — admin sube CSV → sistema envía invitaciones
3. **Invitación por link/QR** — link único por evento + QR del evento para registro
4. **Zonas horarias** — agenda en hora local, guardar UTC en DB, convertir en frontend
5. **Modo sandbox/demo** — evento ficticio para que el cliente pruebe antes de contratar
6. **Templates de evento** — Congreso/Feria/Lanzamiento con módulos preconfigurados
7. **Múltiples admins por evento** — roles: total/contenido/notifs/analytics
8. **Modo kiosco check-in** — app web tablet pantalla completa, no se cierra accidentalmente
9. **Onboarding del cliente nuevo** — flujo: crear evento → branding → módulos → invitar equipo
10. **Soporte en plataforma** — canal de contacto integrado durante el evento (Fase 3)

---

## 8. Roadmap completo por fases

> **Estrategia actualizada (2026-04-06):** Todo lo funcional primero, UI como barrido final. Las sesiones que estaban en Fase 2 (streaming, Q&A, evaluaciones, photobooth, certificados, reportes, analytics, matchmaking, social wall, gamification, passport, floor plan) se movieron a Fase 1. La sesión UI se hace una sola vez al final, sobre código estable y arquitectura validada con stress testing. Solo quedan en Fase 2: Web Next.js, video calls LiveKit, y proximity chat. Ver `EventOS_Roadmap.md` para el orden de implementación detallado.

---

### FASE 0 — Infraestructura base (Sem 1–2)
**Bloqueante. Todo lo demás depende de esto. No se salta.**

| # | Módulo | Descripción | Superficie |
|---|---|---|---|
| 0.1 | Proyecto Laravel 11 | Sanctum, Spatie Permissions, Filament, Argon2id, estructura modular | Backend |
| 0.2 | MySQL + migraciones | Schema completo incluyendo activity_log y consent_logs | Backend |
| 0.3 | Proyecto Expo | Expo Router, NativeWind, tema configurable, estructura por perfil | App |
| 0.4 | Proyecto Node/Socket.IO | Servicio independiente, validación token Sanctum, salas por evento | Backend |
| 0.5 | VPS + Nginx + Forge | SSL, subdominios, pipeline GitHub, variables de entorno | Infra |
| 0.6 | EAS Build + stores | Apple Developer ($99/año), Google Play ($25 único), primer build de prueba | Infra |
| 0.7 | Cloudflare R2 | Bucket imágenes/docs, CDN, Laravel Filesystem apuntando a R2 | Infra |

---

### FASE 1 — MVP completo para primer evento real (Sem 3–11)
**Todo lo necesario para salir a producción con un cliente.**

#### Auth y acceso
| # | Módulo | Descripción | Perfiles |
|---|---|---|---|
| 1.1 | Auth + Registro | Login, campos dinámicos por evento, Sanctum, perfiles, Argon2id, consent_logs | Todos |
| 1.2 | Motor de módulos | Tabla modules, config JSON, roles, order, menú dinámico en app | Todos |
| 1.3 | Templates de evento | Congreso/Feria/Lanzamiento con módulos preconfigurados | Admin |
| 1.4 | Múltiples admins | Roles: admin total / solo contenido / solo notifs / solo analytics | Admin |
| 1.5 | Importación CSV | Admin sube CSV con emails → sistema envía invitaciones automáticamente | Admin |
| 1.6 | Invitación por link/QR | Link único por evento + QR imprimible para el acceso al registro | Todos |

#### Onboarding y UX
| # | Módulo | Descripción | Perfiles |
|---|---|---|---|
| 1.7 | Onboarding asistente | 3 pantallas al primer login: QR, módulos, notificaciones. Saltable. | Todos |
| 1.8 | Onboarding cliente | Flujo creación primer evento: branding → módulos → invitar equipo | Admin |
| 1.9 | Perfil del asistente | Foto, nombre, empresa, cargo, redes. Editable. Base del QR y networking. | Presencial, Virtual |
| 1.10 | Modo offline explícito | Banner sin conexión, última sync, indicador por módulo | Todos |
| 1.11 | Zonas horarias | Agenda en hora local, guardar UTC en DB, convertir en frontend | Todos |

#### Contenido del evento
| # | Módulo | Descripción | Perfiles |
|---|---|---|---|
| 1.12 | Home + branding | Banner hero, logo, colores del cliente desde API, punto live pulsante | Todos |
| 1.13 | Agenda + favoritos | Días, sesiones, sala, speaker. Favoritos. Push 10 min antes de sesión marcada. | Presencial, Virtual |
| 1.14 | Speakers | Listado, perfil, foto, bio, sesiones asociadas. Cache offline. | Presencial, Virtual |
| 1.15 | Transmisión embebida | Player YouTube Live/Vimeo dentro de la app. Crítico para el virtual. | Presencial, Virtual |
| 1.16 | Documentos / presentaciones | Upload admin, descarga app, cache offline, agrupados por sesión. | Presencial, Virtual |
| 1.17 | Anuncios | Feed del evento, push silenciosa al publicar, offline. | Presencial, Virtual |
| 1.18 | Dresscode / info logística | Contenido estático rico — imágenes + texto. Editor en admin. | Presencial |
| 1.19 | Encuesta simple | Opción múltiple, una respuesta por usuario, resultados en admin. | Presencial, Virtual |
| 1.20 | Banners (slideshow) | Slideshow de imágenes en home, intervalo configurable, link externo desde admin. | Presencial, Virtual |
| 1.20b | Directorio de Patrocinadores | Stand virtual por patrocinador: logo, banner hero, descripción, servicios, botón de contacto con form popup, like/favorito siempre visible. Solo muestra los campos no vacíos — si solo entregaron logo y nombre, solo eso se ve. Tier para orden automático (Platinum > Gold > Silver > Bronze > Community). | Presencial, Virtual |

#### QR, leads y acreditación
| # | Módulo | Descripción | Perfiles |
|---|---|---|---|
| 1.21 | Mi QR personal | Token HMAC-SHA256 por evento. No expone ID. Contador stands visitados. | Presencial |
| 1.22 | Check-in + control acceso | Tablet en puerta, valida QR, registra ingreso, aforo en tiempo real. | Admin |
| 1.23 | Modo kiosco check-in | App web tablet pantalla completa, no se puede cerrar accidentalmente. | Admin |
| 1.24 | Badge físico impreso | App web local + Zebra/Brother. Nombre, empresa, foto, rol, QR. En red del evento. | Admin |
| 1.25 | App vendedor — scanner | Cámara, escaneo QR, captura lead, datos del perfil, lista leads, nota, exportar CSV. | Vendedor |

#### Networking
| # | Módulo | Descripción | Perfiles |
|---|---|---|---|
| 1.25b | Directorio de asistentes + Networking | Directorio opt-in de asistentes del evento. Solicitudes de contacto con aprobación del receptor. Status 'ignored' sin notificación al emisor (no drama). Bloqueo silencioso de usuarios. Push al recibir solicitud pendiente. | Presencial, Virtual |

#### Notificaciones
| # | Módulo | Descripción | Perfiles |
|---|---|---|---|
| 1.26 | Push notifications | Registro tokens Expo, envío desde admin, filtro por rol, historial. | Todos |
| 1.27 | Notificaciones programadas | Admin agenda pushes para hora exacta. Laravel Queue + Scheduler. | Admin |

#### Analytics Fase 1
| # | Módulo | Descripción | Superficie |
|---|---|---|---|
| 1.28 | Tracking automático | Hook useTracker — registra open/close/duration en cada pantalla automáticamente | App |
| 1.29 | POST /api/track | Endpoint que recibe eventos del activity_log desde la app | Backend |
| 1.30 | Dashboard básico admin | Usuarios activos, módulos más usados, leads por stand, notifs enviadas, aforo live | Admin |

---

### FASE 2 — Módulos avanzados (Sem 12–17)
**Diferenciadores competitivos. Segundo evento en adelante.**

#### Tiempo real y engagement
| # | Módulo | Descripción |
|---|---|---|
| 2.1 | Chat entre asistentes | Salas por evento, 1 a 1, Socket.IO, push si app cerrada |
| 2.2 | Votación en vivo | Pregunta activa, votos Socket.IO, resultados animados en tiempo real |
| 2.3 | Preguntas al speaker | Público envía, moderador aprueba, speaker ve en pantalla |
| 2.4 | Evaluación de sesiones | Push al terminar sesión, stars + comentario, reporte en admin |

#### Networking y contenido
| # | Módulo | Descripción |
|---|---|---|
| 2.5 | *(movido a Fase 1 — ver módulo 1.25b)* | Directorio de asistentes + solicitudes de contacto con aprobación | — |
| 2.6 | Matchmaking por intereses | Sugerencias de contacto basadas en empresa/cargo/sector. Requiere datos reales acumulados de Fase 1. | — |
| 2.7 | Gamificación / puntos | Puntos por asistir, escanear stands, responder encuestas. Leaderboard. |
| 2.8 | Mapa del venue | Imagen con puntos de interés, salas, stands. Editable en admin. |
| 2.9 | Memorias / photobooth | Galería, subida de fotos, moderación antes de publicar. |
| 2.10 | Certificados digitales | PDF generado Laravel, QR de validación, descargable desde app. |
| 2.11 | Modo sandbox / demo | Evento ficticio para que el cliente potencial pruebe antes de contratar. |

#### Analytics Fase 2 — donde superas a Socio
| # | Módulo | Descripción |
|---|---|---|
| 2.12 | Streaming analytics | Drop-off por minuto exacto, peak concurrencia, Live vs Replay |
| 2.13 | Módulos por rol | Presencial vs Virtual — qué usa cada perfil diferente |
| 2.14 | Notificaciones analytics | Open rate, click-through por notif, entrega por rol |
| 2.15 | Leads analytics | Ranking stands, perfil del lead promedio, hora pico de scans, mapa de calor |
| 2.16 | Reporte post-evento PDF | Generado automáticamente al cerrar el evento — el cliente lo lleva a su junta |

---

### FASE 3 — Producto SaaS maduro (Sem 18–24)
**Con 2–3 clientes recurrentes.**

| # | Módulo | Descripción |
|---|---|---|
| 3.1 | Multi-evento / multi-tenant | N eventos por cliente, aislamiento de datos, subdominios por organización |
| 3.2 | White label | Nombre, ícono y colores del cliente en stores. EAS build por cliente. |
| 3.3 | App del organizador | Admin móvil: aforo en vivo, push, aprobar fotos, ver leads. Para el día del evento. |
| 3.4 | Analytics comparativo | Comparar métricas entre eventos del mismo cliente |
| 3.5 | Integración CRM | Leads a Salesforce/HubSpot automático. Webhook configurable. |
| 3.6 | Marketplace de módulos | Catálogo, activación por cliente, facturación automática por módulo activo. |
| 3.7 | IA — recomendaciones | Sesiones sugeridas por perfil, contactos afines, contenido personalizado. |
| 3.8 | Soporte en plataforma | Canal de soporte integrado en el admin panel durante el evento. |

---

## 9. Esquema de base de datos completo

```sql
-- ORGANIZACIONES
CREATE TABLE organizations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    logo_url VARCHAR(500),
    plan ENUM('starter','pro','enterprise') DEFAULT 'starter',
    subscription_status ENUM('active','grace','suspended','cancelled') DEFAULT 'active',
    subscription_expires_at TIMESTAMP NULL,  -- NULL = sin expiración (cuenta demo/interna)
    -- Flujo: active → grace (día 0 expirado, 7 días de gracia) → suspended (solo lectura) → cancelled
    -- Laravel middleware verifica esto en cada request de la API
    privacy_policy_url VARCHAR(500) NULL,    -- URL de la política de privacidad del organizador
    terms_url VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- EVENTOS
CREATE TABLE events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    organization_id BIGINT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    template ENUM('congreso','feria','lanzamiento','custom') DEFAULT 'custom',
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    timezone VARCHAR(60) DEFAULT 'America/Bogota',
    venue VARCHAR(255),
    primary_color VARCHAR(7) DEFAULT '#E8FF47',
    logo_url VARCHAR(500),
    banner_url VARCHAR(500),
    registration_url VARCHAR(500),
    max_attendees INT NULL,                -- NULL = sin límite
    is_published BOOLEAN DEFAULT false,    -- false = borrador, invisible para asistentes
    registration_opens_at TIMESTAMP NULL,  -- NULL = abierto desde ya
    registration_closes_at TIMESTAMP NULL, -- NULL = cierra cuando starts
    registration_requires_approval BOOLEAN DEFAULT false, -- auto-aprobado o manual
    is_active BOOLEAN DEFAULT true,
    is_sandbox BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organization_id) REFERENCES organizations(id)
);

-- USUARIOS
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(30),
    company VARCHAR(255),
    job_title VARCHAR(255),
    photo_url VARCHAR(500),
    linkedin_url VARCHAR(500),
    email_verified_at TIMESTAMP NULL,      -- NULL = no verificado aún
    consent_accepted_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT true,        -- false = cuenta desactivada globalmente
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- CONSENTIMIENTO LEY 1581
CREATE TABLE consent_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    event_id BIGINT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    accepted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- ASISTENTES POR EVENTO
CREATE TABLE attendees (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    event_id BIGINT NOT NULL,
    role ENUM('presencial','virtual','vendedor','admin','admin_content','admin_notifs','admin_analytics') NOT NULL,
    stand_name VARCHAR(255) NULL,
    checked_in_at TIMESTAMP NULL,
    badge_printed_at TIMESTAMP NULL,
    expo_push_token VARCHAR(255),
    invited_via ENUM('csv','link','qr','manual') DEFAULT 'link',
    registration_approved_at TIMESTAMP NULL,
    networking_visible BOOLEAN DEFAULT true,
    lead_tier ENUM('hot','warm','cold') NULL,
    has_vendor_access BOOLEAN DEFAULT false,  -- true si es colaborador de un stand (sin cambiar rol)
    sponsor_id BIGINT NULL,                   -- stand al que pertenece como colaborador
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, event_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (event_id) REFERENCES events(id)
    -- sponsor_id FK se agrega después de crear sponsors (alter migration en S1.6)
);

-- QR TOKENS
CREATE TABLE qr_tokens (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    attendee_id BIGINT NOT NULL UNIQUE,
    token VARCHAR(64) NOT NULL UNIQUE,
    scanned_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attendee_id) REFERENCES attendees(id)
);

-- MÓDULOS
CREATE TABLE modules (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL,
    slug VARCHAR(60) NOT NULL,
    name VARCHAR(120) NOT NULL,
    icon VARCHAR(60),
    enabled BOOLEAN DEFAULT true,
    roles JSON,
    config JSON,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (event_id, slug),
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- TEMPLATES DE MÓDULOS
CREATE TABLE module_templates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    template_name ENUM('congreso','feria','lanzamiento') NOT NULL,
    slug VARCHAR(60) NOT NULL,
    name VARCHAR(120) NOT NULL,
    icon VARCHAR(60),
    roles JSON,
    config JSON,
    sort_order INT DEFAULT 0
);

-- AGENDA
CREATE TABLE sessions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    location VARCHAR(255),
    session_type ENUM('keynote','panel','workshop','break','networking') DEFAULT 'keynote',
    status ENUM('scheduled','live','cancelled','finished') DEFAULT 'scheduled',
    capacity INT NULL,                     -- NULL = sin límite de aforo por sala
    stream_url VARCHAR(500),
    recording_url VARCHAR(500),
    deleted_at TIMESTAMP NULL,             -- soft delete
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_status (event_id, status),
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- SPEAKERS
CREATE TABLE speakers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL,
    attendee_id BIGINT NULL,               -- NULL si el speaker no tiene cuenta de asistente
    name VARCHAR(255) NOT NULL,
    bio TEXT,
    company VARCHAR(255),
    job_title VARCHAR(255),
    photo_url VARCHAR(500),
    linkedin_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (attendee_id) REFERENCES attendees(id)
);

-- RELACIÓN SPEAKER <-> SESSION
CREATE TABLE session_speaker (
    session_id BIGINT NOT NULL,
    speaker_id BIGINT NOT NULL,
    PRIMARY KEY (session_id, speaker_id),
    FOREIGN KEY (session_id) REFERENCES sessions(id),
    FOREIGN KEY (speaker_id) REFERENCES speakers(id)
);

-- FAVORITOS
CREATE TABLE session_favorites (
    attendee_id BIGINT NOT NULL,
    session_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (attendee_id, session_id),
    FOREIGN KEY (attendee_id) REFERENCES attendees(id),
    FOREIGN KEY (session_id) REFERENCES sessions(id)
);

-- DOCUMENTOS
CREATE TABLE documents (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL,
    session_id BIGINT NULL,
    title VARCHAR(255) NOT NULL,
    file_url VARCHAR(500) NOT NULL,
    file_size INT,
    mime_type VARCHAR(100),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (session_id) REFERENCES sessions(id)
);

-- ANUNCIOS
CREATE TABLE announcements (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    roles JSON NULL,
    push_sent BOOLEAN DEFAULT false,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- NOTIFICACIONES PROGRAMADAS
CREATE TABLE scheduled_notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    roles JSON,
    send_at TIMESTAMP NOT NULL,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- LINKS DE INVITACIÓN POR EVENTO
CREATE TABLE event_invitation_links (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,   -- UUID generado al crear el evento
    role ENUM('presencial','virtual') DEFAULT 'presencial',
    max_uses INT NULL,                   -- NULL = ilimitado
    uses_count INT DEFAULT 0,
    expires_at TIMESTAMP NULL,           -- NULL = sin expiración
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- PÁGINAS PERSONALIZADAS (reemplaza static_content — más genérico y poderoso)
-- Cada página puede ser HTML enriquecido, iframe embebido, o ambos
-- Aparece como su propio módulo en el menú. Admin crea N páginas por evento.
CREATE TABLE custom_pages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    icon VARCHAR(60) DEFAULT 'document-text',
    content_type ENUM('html','iframe','mixed') DEFAULT 'html',
    body LONGTEXT NULL,                   -- HTML sanitizado (HTMLPurifier) para content_type html/mixed
    iframe_url VARCHAR(500) NULL,         -- URL del embed: Google Maps, YouTube, form externo, etc.
    iframe_height INT DEFAULT 600,        -- altura del iframe en px
    allow_fullscreen BOOLEAN DEFAULT true,
    roles JSON NULL,                      -- NULL = todos los roles
    sort_order INT DEFAULT 0,
    enabled BOOLEAN DEFAULT true,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id)
);
-- Nota: al crear una custom_page se inserta automáticamente un registro en modules
-- con slug='custom-page-{id}' y config JSON {page_id: X}. El motor de módulos lo renderiza.

-- REGISTRO DE ENTREGA Y APERTURA DE PUSH NOTIFICATIONS
CREATE TABLE push_notification_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    notification_id BIGINT NOT NULL,     -- FK a scheduled_notifications o announcements
    notification_type ENUM('announcement','scheduled','module_update') NOT NULL,
    attendee_id BIGINT NOT NULL,
    expo_ticket_id VARCHAR(255) NULL,    -- ID de ticket de Expo al enviar
    status ENUM('pending','delivered','failed','opened') DEFAULT 'pending',
    delivered_at TIMESTAMP NULL,
    opened_at TIMESTAMP NULL,            -- se actualiza vía POST /api/track con action='push_open'
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notification (notification_id, notification_type),
    FOREIGN KEY (attendee_id) REFERENCES attendees(id)
);

-- LEADS
CREATE TABLE leads (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    vendor_attendee_id BIGINT NOT NULL,    -- quién escaneó
    scanned_attendee_id BIGINT NOT NULL,
    event_id BIGINT NOT NULL,
    sponsor_id BIGINT NULL,                -- FK sponsors — pool del stand (NULL = lead personal sin stand)
    tier ENUM('hot','warm','cold') NULL,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- UNIQUE por stand cuando hay sponsor_id; por vendedor cuando no hay
    UNIQUE KEY unique_lead_stand (sponsor_id, scanned_attendee_id),   -- un contacto por stand
    UNIQUE KEY unique_lead_personal (vendor_attendee_id, scanned_attendee_id), -- un contacto por vendedor sin stand
    FOREIGN KEY (vendor_attendee_id) REFERENCES attendees(id),
    FOREIGN KEY (scanned_attendee_id) REFERENCES attendees(id),
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (sponsor_id) REFERENCES sponsors(id)
);
-- Pool de leads: si sponsor_id != NULL → todos los miembros del stand comparten el pool
-- Error 409 ALREADY_IN_STAND_POOL al duplicar, devuelve lead existente + nombre de quien lo capturó

-- LOG DE EDICIONES DE LEADS
CREATE TABLE lead_edits (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    lead_id BIGINT NOT NULL,
    attendee_id BIGINT NOT NULL,           -- quién editó
    field_changed VARCHAR(60) NOT NULL,    -- 'tier' o 'note'
    old_value TEXT NULL,
    new_value TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (attendee_id) REFERENCES attendees(id)
);

-- BANNERS (slideshow del home — diferente a la tabla sponsors)
CREATE TABLE banners (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    link_url VARCHAR(500),
    sort_order INT DEFAULT 0,
    enabled BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- PATROCINADORES / STANDS VIRTUALES
-- El sponsor ES el stand. Tiene directorio público (para asistentes) + equipo interno (para leads).
CREATE TABLE sponsors (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    tier ENUM('platinum','gold','silver','bronze','community') DEFAULT 'community',
    logo_url VARCHAR(500) NULL,           -- NULL = no se muestra en la app
    banner_url VARCHAR(500) NULL,         -- imagen hero del stand (opcional)
    description TEXT NULL,               -- descripción del patrocinador (opcional)
    website_url VARCHAR(500) NULL,        -- URL del sitio web (opcional)
    contact_name VARCHAR(255) NULL,       -- nombre del contacto (opcional)
    contact_email VARCHAR(255) NULL,      -- email al que llegan los leads (requerido si show_contact_button=true)
    contact_phone VARCHAR(30) NULL,       -- teléfono del contacto (opcional)
    show_contact_button BOOLEAN DEFAULT false,
    owner_attendee_id BIGINT NULL,         -- vendedor responsable del stand (gestiona colaboradores)
    max_collaborators INT DEFAULT 1,       -- cupos totales de equipo (lo pone el admin en Filament)
    sort_order INT DEFAULT 0,
    enabled BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id)
);
-- Solo se muestran en la app los campos que NO sean NULL.

-- MIEMBROS DEL STAND (equipo del sponsor)
CREATE TABLE stand_members (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sponsor_id BIGINT NOT NULL,
    attendee_id BIGINT NULL,               -- NULL si aún no se ha registrado al evento
    invited_email VARCHAR(255) NOT NULL,   -- email con el que fue invitado
    invited_by_attendee_id BIGINT NOT NULL,
    status ENUM('pending','active','removed') DEFAULT 'pending',
    joined_at TIMESTAMP NULL,              -- cuándo aceptó / se registró
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (sponsor_id, invited_email),
    FOREIGN KEY (sponsor_id) REFERENCES sponsors(id),
    FOREIGN KEY (attendee_id) REFERENCES attendees(id) ON DELETE SET NULL,
    FOREIGN KEY (invited_by_attendee_id) REFERENCES attendees(id)
);
-- Flujo: owner agrega email → pending + email invitación
-- Si ya registrado en evento → active inmediatamente, attendee.has_vendor_access=true
-- Al registrarse: AuthController detecta pending invites por email → activa automáticamente
-- Remover miembro: status=removed, attendee.has_vendor_access=false, attendee.sponsor_id=NULL
-- Orden automático: platinum → gold → silver → bronze → community, luego sort_order.
-- show_contact_button solo funciona si contact_email está configurado.

CREATE TABLE sponsor_services (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sponsor_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (sponsor_id) REFERENCES sponsors(id)
);
-- Lista de servicios del patrocinador.
-- Aparecen como checkboxes en el popup "¿En qué servicios estás interesado?".

CREATE TABLE sponsor_leads (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sponsor_id BIGINT NOT NULL,
    attendee_id BIGINT NOT NULL,
    event_id BIGINT NOT NULL,
    interested_services JSON NULL,       -- array de IDs de sponsor_services seleccionados
    message TEXT NULL,                   -- mensaje libre opcional del asistente
    notified_sponsor_at TIMESTAMP NULL,  -- NULL = email pendiente o no aplica
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (sponsor_id, attendee_id), -- un formulario por asistente por patrocinador
    FOREIGN KEY (sponsor_id) REFERENCES sponsors(id),
    FOREIGN KEY (attendee_id) REFERENCES attendees(id),
    FOREIGN KEY (event_id) REFERENCES events(id)
);
-- Al crear: dispatchar NotifySponsorLeadJob que envía email con datos del asistente + servicios.
-- HTTP 409 SPONSOR_ALREADY_CONTACTED si el asistente ya envió formulario a este patrocinador.
-- El admin ve todos los leads por patrocinador en el panel de ROI de Filament.

CREATE TABLE sponsor_favorites (
    attendee_id BIGINT NOT NULL,
    sponsor_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (attendee_id, sponsor_id),
    FOREIGN KEY (attendee_id) REFERENCES attendees(id),
    FOREIGN KEY (sponsor_id) REFERENCES sponsors(id)
);
-- El botón like/favorito SIEMPRE está visible (independientemente de otros campos opcionales).
-- Permite medir interés aunque el asistente no llene el formulario de contacto.

-- SOLICITUDES DE CONTACTO (networking con aprobación)
CREATE TABLE contact_requests (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sender_attendee_id BIGINT NOT NULL,
    receiver_attendee_id BIGINT NOT NULL,
    event_id BIGINT NOT NULL,
    status ENUM('pending','accepted','ignored') DEFAULT 'pending',
    message VARCHAR(500) NULL,           -- mensaje breve opcional al enviar solicitud
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (sender_attendee_id, receiver_attendee_id, event_id),
    FOREIGN KEY (sender_attendee_id) REFERENCES attendees(id),
    FOREIGN KEY (receiver_attendee_id) REFERENCES attendees(id),
    FOREIGN KEY (event_id) REFERENCES events(id)
);
-- 'ignored' en lugar de 'rejected': el emisor NO recibe notificación de rechazo. Menos drama social.
-- Solo cuando el receptor acepta → ambos se ven como contactos mutuos.
-- Push al receptor al recibir solicitud pendiente.
-- HTTP 409 CONTACT_ALREADY_SENT si ya existe solicitud pending/accepted.

CREATE TABLE contact_blocks (
    blocker_attendee_id BIGINT NOT NULL,
    blocked_attendee_id BIGINT NOT NULL,
    event_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (blocker_attendee_id, blocked_attendee_id, event_id),
    FOREIGN KEY (blocker_attendee_id) REFERENCES attendees(id),
    FOREIGN KEY (blocked_attendee_id) REFERENCES attendees(id),
    FOREIGN KEY (event_id) REFERENCES events(id)
);
-- Bloqueado: no aparece en el directorio del que bloqueó.
-- No puede enviar nuevas solicitudes al que lo bloqueó (HTTP 409 CONTACT_BLOCKED).
-- El bloqueo es silencioso — el bloqueado no sabe que fue bloqueado.

-- ENCUESTAS
CREATE TABLE surveys (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id)
);

CREATE TABLE survey_questions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    survey_id BIGINT NOT NULL,
    question TEXT NOT NULL,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (survey_id) REFERENCES surveys(id)
);

CREATE TABLE survey_options (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    question_id BIGINT NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES survey_questions(id)
);

CREATE TABLE survey_answers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    attendee_id BIGINT NOT NULL,
    question_id BIGINT NOT NULL,
    option_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (attendee_id, question_id),
    FOREIGN KEY (attendee_id) REFERENCES attendees(id),
    FOREIGN KEY (question_id) REFERENCES survey_questions(id),
    FOREIGN KEY (option_id) REFERENCES survey_options(id)
);

-- EVALUACIONES DE SESIÓN
CREATE TABLE session_ratings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    attendee_id BIGINT NOT NULL,
    session_id BIGINT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (attendee_id, session_id),
    FOREIGN KEY (attendee_id) REFERENCES attendees(id),
    FOREIGN KEY (session_id) REFERENCES sessions(id)
);

-- CAMPOS DE REGISTRO DINÁMICOS
CREATE TABLE registration_fields (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL,
    label VARCHAR(255) NOT NULL,
    field_type ENUM('text','email','phone','select','checkbox') NOT NULL,
    options JSON NULL,
    required BOOLEAN DEFAULT false,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (event_id) REFERENCES events(id)
);

CREATE TABLE registration_field_values (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    attendee_id BIGINT NOT NULL,
    field_id BIGINT NOT NULL,
    value TEXT,
    UNIQUE KEY (attendee_id, field_id),
    FOREIGN KEY (attendee_id) REFERENCES attendees(id),
    FOREIGN KEY (field_id) REFERENCES registration_fields(id)
);

-- BANS DE ASISTENTES
CREATE TABLE attendee_bans (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    attendee_id BIGINT NOT NULL,
    banned_by BIGINT NOT NULL,           -- FK al user admin que ejecutó el ban
    reason TEXT NULL,
    expires_at TIMESTAMP NULL,           -- NULL = ban permanente
    lifted_at TIMESTAMP NULL,            -- NULL = ban activo
    lifted_by BIGINT NULL,               -- FK al admin que levantó el ban
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_attendee_ban (attendee_id),
    FOREIGN KEY (attendee_id) REFERENCES attendees(id),
    FOREIGN KEY (banned_by) REFERENCES users(id),
    FOREIGN KEY (lifted_by) REFERENCES users(id)
);
-- Un asistente está baneado si tiene registro con lifted_at IS NULL y (expires_at IS NULL OR expires_at > NOW())
-- Al banear: revocar todos los tokens Sanctum del user para ese evento

-- PREFERENCIAS DE NOTIFICACIÓN POR ASISTENTE
CREATE TABLE notification_preferences (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    attendee_id BIGINT NOT NULL UNIQUE,
    push_session_reminders BOOLEAN DEFAULT true,   -- 10 min antes de sesión favorita
    push_announcements BOOLEAN DEFAULT true,        -- anuncios del evento
    push_logistics BOOLEAN DEFAULT true,            -- logística (solo presencial)
    push_marketing BOOLEAN DEFAULT false,           -- patrocinadores/banners
    email_event_reminders BOOLEAN DEFAULT true,     -- recordatorio 24h antes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (attendee_id) REFERENCES attendees(id)
);

-- AUDIT LOG DE ACCIONES ADMIN
CREATE TABLE admin_audit_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,               -- admin que ejecutó la acción
    event_id BIGINT NULL,
    action VARCHAR(100) NOT NULL,          -- 'session.updated', 'attendee.banned', 'module.toggled'
    target_type VARCHAR(60) NULL,          -- 'Session', 'Attendee', 'Module'
    target_id BIGINT NULL,
    old_values JSON NULL,                  -- estado anterior
    new_values JSON NULL,                  -- estado nuevo
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_audit (event_id),
    INDEX idx_user_audit (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- REGISTRO DE ARCHIVOS SUBIDOS
CREATE TABLE file_uploads (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uploaded_by BIGINT NOT NULL,           -- user_id del admin
    event_id BIGINT NULL,
    r2_key VARCHAR(500) NOT NULL,          -- clave en R2 (ruta del archivo)
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size_bytes INT NOT NULL,
    module VARCHAR(60) NULL,               -- 'documents', 'speakers', 'banners', 'custom_pages'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- HISTORIAL DE CAMBIOS DE ROL DE ASISTENTE
CREATE TABLE attendee_role_changes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    attendee_id BIGINT NOT NULL,
    changed_by BIGINT NOT NULL,            -- admin que hizo el cambio
    from_role VARCHAR(60) NOT NULL,
    to_role VARCHAR(60) NOT NULL,
    reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attendee_id) REFERENCES attendees(id),
    FOREIGN KEY (changed_by) REFERENCES users(id)
);

-- LEADS (actualizado con tier de clasificación)
-- tier se guarda por lead individual (hot/warm/cold que el vendedor asigna al escanear o después)

-- MENSAJES DE CHAT (persistencia — Socket.IO es solo el canal de entrega)
CREATE TABLE chat_messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL,
    sender_attendee_id BIGINT NOT NULL,
    recipient_attendee_id BIGINT NULL,     -- NULL = mensaje a sala general del evento
    room VARCHAR(100) NOT NULL,            -- 'event:{id}' | 'dm:{id1}_{id2}' (ids ordenados menor-mayor)
    body TEXT NOT NULL,
    type ENUM('text','image','system') DEFAULT 'text',
    read_at TIMESTAMP NULL,                -- para DMs: cuando el receptor lo leyó
    deleted_at TIMESTAMP NULL,             -- soft delete (el usuario borra su mensaje)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_room (room, created_at),
    INDEX idx_event_chat (event_id),
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (sender_attendee_id) REFERENCES attendees(id)
);
-- Socket.IO entrega el mensaje en tiempo real Y Laravel lo persiste en esta tabla
-- Historial: GET /api/events/{id}/chat/messages?room=event:1&before_id=500&limit=50

-- VOTACIONES EN VIVO (Fase 2 — schema definido en Fase 1)
CREATE TABLE live_polls (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL,
    session_id BIGINT NULL,                -- NULL = votación general del evento
    question TEXT NOT NULL,
    status ENUM('draft','active','closed') DEFAULT 'draft',
    show_results BOOLEAN DEFAULT false,    -- admin controla cuándo mostrar resultados
    activated_at TIMESTAMP NULL,
    closed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (session_id) REFERENCES sessions(id)
);

CREATE TABLE live_poll_options (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    poll_id BIGINT NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (poll_id) REFERENCES live_polls(id)
);

CREATE TABLE live_poll_votes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    poll_id BIGINT NOT NULL,
    option_id BIGINT NOT NULL,
    attendee_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (poll_id, attendee_id),     -- un voto por persona por poll
    FOREIGN KEY (poll_id) REFERENCES live_polls(id),
    FOREIGN KEY (option_id) REFERENCES live_poll_options(id),
    FOREIGN KEY (attendee_id) REFERENCES attendees(id)
);
-- Conteo en tiempo real: Redis HASH poll:{id}:votes {option_id: count}
-- Sincronizar a live_poll_votes en MySQL al cerrar la votación

-- PREGUNTAS AL SPEAKER (Fase 2)
CREATE TABLE speaker_questions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    session_id BIGINT NOT NULL,
    attendee_id BIGINT NOT NULL,
    question TEXT NOT NULL,
    status ENUM('pending','approved','rejected','answered') DEFAULT 'pending',
    upvotes INT DEFAULT 0,                 -- otros asistentes pueden votar la pregunta
    answered_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id),
    FOREIGN KEY (attendee_id) REFERENCES attendees(id)
);

-- JUEGOS INTERACTIVOS (Fase 3 — Unity + Socket.IO)
-- Unity WebGL corre en TV/proyector, teléfono es el control vía Socket.IO
CREATE TABLE game_sessions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL,
    room_id VARCHAR(64) UNIQUE NOT NULL,     -- UUID para el QR de acceso
    game_type VARCHAR(60) NOT NULL,           -- 'trivia','reaction','drawing','racing','custom'
    unity_scene VARCHAR(100) NULL,            -- nombre de la escena de Unity a cargar
    status ENUM('waiting','countdown','active','paused','finished') DEFAULT 'waiting',
    config JSON NULL,                         -- preguntas, tiempo límite, max jugadores, etc.
    max_players INT DEFAULT 100,
    started_at TIMESTAMP NULL,
    finished_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id)
);

CREATE TABLE game_players (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    game_session_id BIGINT NOT NULL,
    attendee_id BIGINT NOT NULL,
    nickname VARCHAR(60) NULL,               -- nombre visible en la pantalla de Unity
    avatar_url VARCHAR(500) NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (game_session_id, attendee_id),
    FOREIGN KEY (game_session_id) REFERENCES game_sessions(id),
    FOREIGN KEY (attendee_id) REFERENCES attendees(id)
);

CREATE TABLE game_results (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    game_session_id BIGINT NOT NULL,
    attendee_id BIGINT NOT NULL,
    score INT DEFAULT 0,
    rank INT NULL,
    data JSON NULL,                           -- respuestas, tiempos de reacción, trazos, etc.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_session_id) REFERENCES game_sessions(id),
    FOREIGN KEY (attendee_id) REFERENCES attendees(id)
);
-- Socket.IO rooms para juegos:
-- game:{roomId}         → todos los jugadores + Unity host
-- game:{roomId}:host    → solo Unity (recibe inputs de jugadores)
-- game:{roomId}:players → solo teléfonos (reciben estado del juego desde Unity)
-- Protocolo: Unity emite 'game:state' → teléfonos actualizan controles
--            Teléfono emite 'player:input' → Unity recibe y procesa
-- Node.js NO entiende la lógica del juego — solo enruta mensajes entre Unity y teléfonos

-- ACTIVITY LOG — BASE DE TODO EL ANALYTICS
CREATE TABLE activity_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    attendee_id BIGINT NOT NULL,
    event_id BIGINT NOT NULL,
    module_slug VARCHAR(60),
    action VARCHAR(60),
    target_id BIGINT NULL,
    target_type VARCHAR(60) NULL,
    duration_seconds INT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event (event_id),
    INDEX idx_attendee (attendee_id),
    INDEX idx_module (module_slug),
    INDEX idx_action (action),
    INDEX idx_created (created_at),
    FOREIGN KEY (attendee_id) REFERENCES attendees(id),
    FOREIGN KEY (event_id) REFERENCES events(id)
);
```

---

## 10. Endpoints principales del API

```
# AUTH
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/auth/me
PUT    /api/auth/profile
POST   /api/auth/profile/photo        -- upload foto: obtiene presigned URL de R2 y guarda
DELETE /api/auth/account              -- borrado de cuenta (derecho Ley 1581)
POST   /api/auth/expo-token
POST   /api/auth/forgot-password      -- envía email con link de reset
POST   /api/auth/reset-password       -- recibe token + nueva contraseña, invalida token tras uso
POST   /api/auth/verify-email         -- reenviar email de verificación
GET    /api/auth/verify-email/{token} -- click desde email → verifica + redirige a app via deep link
POST   /api/events/{id}/join          -- registrarse por link/QR (token en event_invitation_links)
GET    /api/me/notification-preferences
PUT    /api/me/notification-preferences
POST   /api/files/presigned-url       -- obtener URL firmada R2 para upload directo desde app/web
POST   /api/files/confirm             -- confirmar que upload a R2 completó, guardar en file_uploads

# EVENTO Y MÓDULOS
GET    /api/events/{slug}
GET    /api/events/{id}/modules
GET    /api/events/{id}/banners

# PATROCINADORES
GET    /api/v1/events/{id}/sponsors                  -- lista ordenada por tier + sort_order, cache 5min
GET    /api/v1/sponsors/{id}                         -- detalle del stand + lista de servicios
POST   /api/v1/sponsors/{id}/favorite                -- guardar patrocinador (like)
DELETE /api/v1/sponsors/{id}/favorite                -- quitar favorito
POST   /api/v1/sponsors/{id}/contact                 -- formulario de interés (idempotent por asistente)
-- Body: { interested_services: [id1, id2], message?: "..." }
-- Nombre, empresa y cargo del asistente se toman del perfil, no hay que reenviarlos.

# AGENDA
GET    /api/events/{id}/sessions
POST   /api/sessions/{id}/favorite
DELETE /api/sessions/{id}/favorite
GET    /api/me/favorites
POST   /api/sessions/{id}/rating

# QR Y CHECK-IN
GET    /api/me/qr
POST   /api/checkin
GET    /api/events/{id}/attendance

# LEADS
POST   /api/leads/scan
GET    /api/me/leads
PUT    /api/leads/{id}                         -- actualizar note y/o tier (hot/warm/cold)
GET    /api/me/leads/export                    -- CSV: name,email,company,job_title,phone,tier,note,scanned_at

# NETWORKING
GET    /api/v1/events/{id}/attendees           -- directorio opt-in (excluye bloqueados por el viewer)
GET    /api/v1/attendees/{id}/profile          -- perfil público del asistente
POST   /api/v1/contacts/request               -- enviar solicitud (body: receiver_attendee_id, message?)
PUT    /api/v1/contacts/request/{id}          -- responder: { status: 'accepted'|'ignored' }
GET    /api/v1/me/contacts                    -- contactos aceptados mutuamente
GET    /api/v1/me/contact-requests            -- solicitudes recibidas pendientes
GET    /api/v1/me/contact-requests/sent       -- solicitudes enviadas pendientes
POST   /api/v1/contacts/block/{attendeeId}    -- bloquear usuario
DELETE /api/v1/contacts/block/{attendeeId}    -- desbloquear usuario

# CONTENIDO
GET    /api/events/{id}/speakers
GET    /api/speakers/{id}
GET    /api/events/{id}/sessions
GET    /api/events/{id}/sessions/{id}
GET    /api/events/{id}/documents
GET    /api/events/{id}/announcements
GET    /api/events/{id}/custom-pages           -- lista páginas personalizadas (reemplaza static-content)
GET    /api/events/{id}/custom-pages/{pageId}  -- detalle (HTML + iframe_url)
GET    /api/events/{id}/surveys/active
POST   /api/surveys/{id}/answer

# TRACKING — activity_log
POST   /api/track
Body: { module_slug, action, target_id, target_type, duration_seconds, metadata }
-- Acciones especiales: action='push_open' target_type='notification' → actualiza push_notification_logs
-- Acciones especiales: action='banner_click' target_id=banner.id → tracking sponsor ROI
-- Acciones especiales: action='calendar_export' target_id=session.id → tracking interés

# CHAT (Fase 2 — endpoints definidos en Fase 1 para no romper el schema)
GET    /api/v1/events/{id}/chat/messages       -- historial: ?room=event:1&before_id=500&limit=50
DELETE /api/v1/chat/messages/{id}              -- soft delete (solo autor o admin)

# VOTACIONES EN VIVO (Fase 2)
GET    /api/v1/events/{id}/polls/active
POST   /api/v1/polls/{id}/vote
GET    /api/v1/polls/{id}/results              -- solo si show_results=true

# PREGUNTAS AL SPEAKER (Fase 2)
POST   /api/v1/sessions/{id}/questions
POST   /api/v1/questions/{id}/upvote
GET    /api/v1/sessions/{id}/questions
PUT    /api/v1/admin/questions/{id}/status     -- aprobar / rechazar / respondida

# JUEGOS INTERACTIVOS (Fase 3)
POST   /api/v1/admin/games                       -- crear sesión de juego (genera roomId + QR)
GET    /api/v1/games/{roomId}                    -- info del juego (para que la app se una)
POST   /api/v1/games/{roomId}/join               -- unirse como jugador
GET    /api/v1/games/{roomId}/players            -- lista de jugadores conectados
POST   /api/v1/admin/games/{id}/start            -- iniciar juego
GET    /api/v1/events/{id}/game-results          -- resultados históricos de juegos del evento

# I18N
GET    /api/v1/translations/{locale}             -- obtener traducciones (es|en) — cacheado en Redis 1h

# LICENCIAS
GET    /api/v1/admin/organizations/{id}/subscription   -- estado actual de suscripción
PUT    /api/v1/admin/organizations/{id}/subscription   -- actualizar plan/expiración (super admin)

# PORTABILIDAD DE DATOS (Ley 1581)
GET    /api/v1/me/data-export                    -- exportar todos los datos del usuario en JSON

# SISTEMA
GET    /api/v1/health                          -- status del servidor (DB, Redis, Node socket)
GET    /api/v1/version                         -- versión mínima requerida de la app
GET    /api/v1/me/events                       -- eventos del usuario autenticado (selector multi-evento)

# ADMIN
POST   /api/v1/admin/events                      -- crear evento
PUT    /api/admin/events/{id}                    -- editar evento
POST   /api/admin/events/{id}/clone              -- clonar evento (estructura sin asistentes)
PUT    /api/admin/events/{id}/publish            -- publicar evento (is_published = true)
POST   /api/admin/events/{id}/invitation-links   -- crear link de invitación
GET    /api/admin/events/{id}/invitation-links   -- listar links activos
PUT    /api/admin/attendees/{id}/role            -- cambiar rol de asistente (guarda en attendee_role_changes)

POST   /api/admin/notifications/send
POST   /api/admin/notifications/schedule
PUT    /api/admin/modules/{id}

GET    /api/admin/events/{id}/stats
GET    /api/admin/events/{id}/checkins           -- detalle de ingresos + aforo
GET    /api/admin/events/{id}/leads
GET    /api/admin/events/{id}/analytics
GET    /api/admin/events/{id}/report

GET    /api/v1/admin/events/{id}/sponsors              -- lista con métricas (views, favorites, leads)
POST   /api/v1/admin/events/{id}/sponsors              -- crear patrocinador
PUT    /api/v1/admin/sponsors/{id}                     -- editar
DELETE /api/v1/admin/sponsors/{id}                     -- eliminar (soft delete)
GET    /api/v1/admin/sponsors/{id}/leads               -- leads generados por este patrocinador
POST   /api/v1/admin/sponsors/{id}/services            -- agregar servicio
PUT    /api/v1/admin/sponsors/services/{id}            -- editar servicio
DELETE /api/v1/admin/sponsors/services/{id}            -- eliminar servicio

POST   /api/admin/attendees/import-csv
POST   /api/admin/attendees/{id}/ban             -- banear asistente (body: reason, expires_at)
POST   /api/admin/attendees/{id}/unban           -- levantar ban
GET    /api/admin/events/{id}/banned             -- lista de asistentes baneados
```

---

## 11. Instrucciones para Claude Code

### Orden de sesiones recomendado

**Sesión 1 — Setup Laravel:**
```
Crea Laravel 11 con:
- Sanctum, Spatie, Filament, Argon2id en config/hashing.php
- Redis: CACHE_DRIVER=redis, QUEUE_CONNECTION=redis, SESSION_DRIVER=redis
- Laravel Horizon instalado y configurado (dashboard en /horizon protegido por auth)
- Sentry SDK instalado, DSN en .env, captura excepciones y performance
- HTMLPurifier instalado para sanitización de HTML en custom_pages
- Browsershot instalado para generación de PDFs (certificados, reportes)
- Todas las migraciones del esquema de este documento (incluyendo soft deletes)
- Seeders: 1 organización, 1 evento real (is_published:true, is_sandbox:false),
  1 evento demo (is_sandbox:true), 1 evento borrador (is_published:false),
  usuarios de cada rol, módulos por defecto, module_templates
- CORS para Expo, localhost y el dominio de producción
- Rate limiting: 5/min login por IP, 60/min API general, 10/min upload
- Estructura de carpetas: Controllers/Api/V1/, Resources/V1/, Requests/
- Ruta base: /api/v1/ para todos los endpoints
- GET /api/health y GET /api/version funcionales desde el inicio

Comandos artisan a ejecutar en orden:
  php artisan key:generate
  php artisan storage:link
  php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
  php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
  php artisan filament:install --panels
  php artisan horizon:install
  php artisan telescope:install          ← solo en entorno local (APP_ENV=local)
  php artisan sentry:publish --dsn
  php artisan migrate --seed
  php artisan horizon:publish            ← assets del dashboard Horizon
  php artisan filament:make-user         ← crear primer super-admin
```

**Sesión 2 — Auth + Roles + Tracking:**
```
Implementa:
- Registro con campos dinámicos (registration_fields)
- Login → token Sanctum + config evento activo + módulos del rol
- QR token HMAC-SHA256 al registrar
- Middleware de roles con Spatie
- consent_logs al registrar (Ley 1581)
- POST /api/track para activity_log (append-only)
```

**Sesión 3 — Motor de módulos + Templates:**
```
Implementa:
- GET /api/events/{id}/modules filtrando por rol del usuario autenticado
- Seeder con los 30 módulos de Fase 1 y sus configs JSON
- Seeder module_templates para Congreso/Feria/Lanzamiento
- Filament resource para gestionar módulos
- Push silenciosa al cambiar enabled o config de un módulo
```

**Sesión 4 — Proyecto Expo:**
```
Crea Expo SDK 52 con Expo Router v4 (New Architecture habilitada):
- Estructura: (auth)/, (app)/(presencial)/, (app)/(virtual)/, (app)/(vendedor)/
- Token Sanctum en expo-secure-store (Keychain iOS / Keystore Android — NUNCA AsyncStorage)
- Context de auth usando expo-secure-store
- Cache general (agenda, módulos, datos no sensibles) en MMKV (no AsyncStorage)
- Context de módulos (carga desde API, cachea en MMKV)
- Hook useTracker — registra open/close/duration automáticamente en cada screen
- Splash screen con loading bar animada
- Home dinámico: módulos del API, next session card, sponsor scroll
- i18n desde el inicio: expo-localization + i18n-js, archivos locales/es.json y locales/en.json

Archivos de configuración a crear en la raíz del proyecto Expo:

app.json: (ver sección de configuración de producción en este documento)

babel.config.js:
  module.exports = function (api) {
    api.cache(true);
    return {
      presets: [
        ['babel-preset-expo', { jsxImportSource: 'nativewind' }],
      ],
      plugins: [
        'react-native-reanimated/plugin',   ← SIEMPRE al final
      ],
    };
  };

metro.config.js:
  const { getDefaultConfig } = require('expo/metro-config');
  const { withNativeWind } = require('nativewind/metro');
  const config = getDefaultConfig(__dirname);
  module.exports = withNativeWind(config, { input: './global.css' });

tailwind.config.js:
  /** @type {import('tailwindcss').Config} */
  module.exports = {
    content: ['./app/**/*.{js,jsx,ts,tsx}', './components/**/*.{js,jsx,ts,tsx}'],
    presets: [require('nativewind/preset')],
    theme: { extend: {} },
    plugins: [],
  };

global.css (raíz del proyecto):
  @tailwind base;
  @tailwind components;
  @tailwind utilities;

nativewind-env.d.ts (raíz del proyecto):
  /// <reference types="nativewind/types" />
```

**Sesión 5 — Contenido del evento (Laravel + Expo):**
```
Laravel:
- CRUD sessions, speakers, session_speaker, documents, announcements
- CRUD static_content (dresscode, logística)
- Filament resources para cada uno
Expo:
- Screens: Agenda (días + favoritos), Speaker detail, Documents, Announcements
- Static content screen (Dresscode / Logística)
- Cache offline en AsyncStorage para agenda, speakers, docs, anuncios
- Push 10 min antes de sesión marcada en favoritos
```

**Sesión 6 — QR personal + Check-in + Kiosco:**
```
Laravel:
- GET /api/me/qr — genera/devuelve HMAC-SHA256 token
- POST /api/checkin — valida QR, registra checked_in_at, actualiza aforo en tiempo real (broadcast)
- GET /api/admin/events/{id}/checkins — aforo live
Expo:
- Pantalla Mi QR (presencial) con QR generado
- App kiosco web: pantalla completa, escaneo con cámara, muestra foto+nombre al validar
- Modo quiosco: deshabilitar gestos de navegación, pantalla siempre activa
```

**Sesión 7 — Módulo Vendedor (Scanner + Leads):**
```
Laravel:
- POST /api/leads/scan — valida QR del asistente, guarda lead, devuelve perfil
- GET /api/me/leads — lista leads del vendedor autenticado (nunca de otros)
- PUT /api/leads/{id}/note
- GET /api/me/leads/export — CSV
Expo:
- App vendedor: pantalla scanner cámara, resultado del scan con foto y datos
- Lista de leads, editar nota, botón exportar CSV
```

**Sesión 8 — Notificaciones Push:**
```
Laravel:
- Job SendExpoPushNotification (async, con reintentos)
- POST /api/admin/notifications/send — envío inmediato por rol
- POST /api/admin/notifications/schedule — agenda con Laravel Scheduler + Queue
- Guardar registros en push_notification_logs (status, expo_ticket_id)
- Cron job para verificar delivery con Expo receipts API
Expo:
- Registro de expo_push_token al login → POST /api/auth/expo-token
- Listener en foreground para notificaciones
- Al tocar una notif → POST /api/track con action='push_open', target_type='notification'
```

**Sesión 9 — Encuestas + Banners + Links de invitación:**
```
Laravel:
- CRUD surveys, survey_questions, survey_options, survey_answers
- POST /api/admin/events/{id}/invitation-links — genera token UUID
- Filament resources
Expo:
- Pantalla encuesta activa (opción múltiple, una respuesta)
- Pantalla banners/sponsors (slideshow con intervalo configurable)
- Flow de registro vía link: GET /join/{token} → registro + auto-join al evento
```

**Sesión 10 — Admin Filament completo + Dashboard Analytics:**
```
Filament Resources (todos bajo panel admin con Spatie permissions):

GESTIÓN DEL EVENTO
- Resource Organizaciones: crear/editar organización, logo, plan
- Resource Eventos: crear/editar, branding (logo, banner, colores), template, timezone,
  sandbox toggle, fechas, venue, is_active
- Resource Campos de Registro: campos dinámicos por evento (label, tipo, opciones, required)
- Resource Links de Invitación: crear links por rol, límite de usos, expiración, activar/desactivar

CONTENIDO
- Resource Agenda (Sessions): crear/editar sesiones, sala, tipo, speakers asociados,
  stream_url, horarios (con picker de timezone)
- Resource Speakers: crear/editar, foto (upload R2), bio, empresa, cargo, linkedin,
  sesiones asociadas
- Resource Documentos: upload a R2, asignar a sesión o general, orden
- Resource Anuncios: crear/editar, filtro por rol, publicar (dispara push silenciosa)
- Resource Banners/Sponsors: upload imagen R2, link, orden, toggle enabled
- Resource Encuestas: crear survey, preguntas, opciones, activar/desactivar, ver resultados
- Resource Contenido Estático: editor Tiptap para Dresscode/Logística/Info, upload imágenes

ASISTENTES Y MODERACIÓN
- Resource Asistentes: lista completa, filtros por rol/check-in/ban, check-in manual,
  ver perfil, cambiar rol, importar CSV
  → Acción: Banear (modal: razón + fecha expiración opcional)
  → Acción: Desbanear
  → Acción: Revocar tokens (fuerza logout)
- Resource Bans: historial completo de bans por evento (quién, cuándo, razón, duración, levantado por)

NOTIFICACIONES
- Resource Notificaciones: enviar inmediata (filtro por rol), programar (datetime picker),
  historial con status de delivery y open rate

MÓDULOS
- Resource Módulos: toggle enabled, editar config JSON, drag-and-drop reordenar,
  filtro por rol habilitado

ANALYTICS Y REPORTES
- Widget Dashboard: usuarios activos hoy, módulos más usados (ranking), aforo live,
  leads por stand, notifs enviadas vs abiertas
- Página Analytics: métricas completas de Fase 1 con gráficas
- Botón exportar reporte PDF post-evento

PERMISOS POR ROL DE ADMIN
- admin_total: accede a todo
- admin_content: solo Agenda, Speakers, Documentos, Anuncios, Banners, Encuestas, Static Content
- admin_notifs: solo Notificaciones
- admin_analytics: solo Dashboard + Analytics (read-only)
```

### Convenciones de código

```
Laravel:
- Controladores en /app/Http/Controllers/Api/
- Form Requests para validación
- Resources para responses
- Jobs para push notifications (async)
- Policies para autorización granular

Expo:
- Screens en /app/(grupo)/
- Components en /components/
- Hooks en /hooks/
  useAuth.ts       — token, usuario, logout
  useModules.ts    — módulos del evento, cache offline
  useOffline.ts    — estado de conexión, banner
  useTracker.ts    — registra activity_log automáticamente
- API services en /services/api.ts
- Types en /types/index.ts
- Constants en /constants/ (slugs de módulos, colores)

Node/Socket.IO:
- Validación Sanctum al conectar (middleware de socket)
- Salas: event:{event_id}, chat:{event_id}, dm:{id1}:{id2}
- Solo maneja chat y votación — todo lo demás por Laravel
```

### Configuración Expo — app.json / app.config.js

```json
{
  "expo": {
    "name": "EventOS",
    "slug": "eventos",
    "scheme": "eventos",
    "version": "1.0.0",
    "sdkVersion": "52.0.0",
    "platforms": ["ios", "android"],
    "orientation": "portrait",
    "userInterfaceStyle": "automatic",   // "automatic" = respeta preferencia del sistema (dark/light)
    "ios": {
      "bundleIdentifier": "com.kasproduction.eventos",
      "minimumOsVersion": "15.0",
      "supportsTablet": true,
      "infoPlist": {
        "UIBackgroundModes": ["remote-notification"],
        "NSCameraUsageDescription": "Necesitamos acceso a tu cámara para escanear códigos QR",
        "NSPhotoLibraryUsageDescription": "Para subir tu foto de perfil"
      },
      "entitlements": {
        "com.apple.developer.associated-domains": ["applinks:api.tudominio.com"]
      },
      "privacyManifests": {
        "NSPrivacyAccessedAPITypes": [
          { "NSPrivacyAccessedAPIType": "NSPrivacyAccessedAPICategoryUserDefaults",
            "NSPrivacyAccessedAPITypeReasons": ["CA92.1"] }
        ]
      }
    },
    "android": {
      "package": "com.kasproduction.eventos",
      "minSdkVersion": 29,
      "targetSdkVersion": 34,
      "permissions": ["CAMERA", "POST_NOTIFICATIONS", "VIBRATE"],
      "adaptiveIcon": {
        "foregroundImage": "./assets/icon-foreground.png",
        "backgroundColor": "#E8FF47"
      }
    },
    "plugins": [
      "expo-router",
      "expo-secure-store",
      ["expo-camera", { "cameraPermission": "Para escanear QR" }],
      ["expo-notifications", { "icon": "./assets/notification-icon.png" }],
      "expo-localization"
    ],
    "newArchEnabled": true
  }
}
```

### Configuración EAS — eas.json

```json
{
  "cli": { "version": ">= 10.0.0" },
  "build": {
    "development": {
      "developmentClient": true,
      "distribution": "internal",
      "env": { "EXPO_PUBLIC_API_URL": "http://localhost:8000/api/v1" }
    },
    "preview": {
      "distribution": "internal",
      "android": { "buildType": "apk" },
      "env": { "EXPO_PUBLIC_API_URL": "https://staging.tudominio.com/api/v1" }
    },
    "production": {
      "autoIncrement": true,
      "env": { "EXPO_PUBLIC_API_URL": "https://api.tudominio.com/api/v1" }
    }
  },
  "submit": {
    "production": {
      "ios": { "appleId": "tu@email.com", "ascAppId": "PENDIENTE" },
      "android": { "serviceAccountKeyPath": "./google-service-account.json" }
    }
  }
}
```

**IMPORTANTE — Development builds obligatorios:**
Expo Go NO soporta NativeWind v4 + Reanimated 3 + MMKV + expo-camera combinados.
Desde el día 1 usar: `eas build --profile development --platform android` (más rápido para dev).

### Variables de entorno

```env
# Laravel .env
APP_QR_SECRET=secret_muy_largo_aleatorio_minimo_32_chars
EXPO_PUSH_URL=https://exp.host/--/api/v2/push/send
CLOUDFLARE_R2_KEY=
CLOUDFLARE_R2_SECRET=
CLOUDFLARE_R2_BUCKET=
CLOUDFLARE_R2_ENDPOINT=

# Email transaccional (Mailgun recomendado, o usar SMTP)
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=
MAILGUN_SECRET=
MAIL_FROM_ADDRESS=noreply@eventos.app
MAIL_FROM_NAME=EventOS

# Redis (queue driver + API cache)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Sentry
SENTRY_LARAVEL_DSN=
SENTRY_TRACES_SAMPLE_RATE=0.1

# App versioning
APP_MIN_VERSION=1.0.0

# Node .env
LARAVEL_DB_HOST=
LARAVEL_DB_USER=
LARAVEL_DB_PASS=
LARAVEL_DB_NAME=
PORT=3001

# Expo .env
EXPO_PUBLIC_API_URL=http://localhost:8000/api
EXPO_PUBLIC_SOCKET_URL=http://localhost:3001
```

---

## 12. Emails transaccionales

Todos los emails usan el branding del evento (logo + primary_color). Laravel Mailable por cada tipo.

| Template | Trigger | Destinatario |
|---|---|---|
| `WelcomeEmail` | Registro exitoso en un evento | Asistente |
| `EmailVerificationEmail` | Al crear cuenta | Usuario |
| `EventInvitationEmail` | Importación CSV o envío manual | Asistente (invitado) |
| `PasswordResetEmail` | POST /api/auth/forgot-password | Usuario |
| `EventReminderEmail` | Cron 24h antes de start_date | Todos los asistentes |
| `SessionReminderEmail` | Cron 1h antes de sesión (fallback al push) | Asistentes con favorito |
| `BanNotificationEmail` | Al ejecutar ban | Asistente baneado |
| `CsvImportCompletedEmail` | Al terminar Job de importación | Admin que subió el CSV |
| `ReportReadyEmail` | Al generar PDF post-evento | Admin del evento |
| `RegistrationApprovalEmail` | Al aprobar manualmente un registro | Asistente pendiente |
| `RegistrationPendingEmail` | Al registrarse si requires_approval=true | Asistente |
| `SponsorLeadEmail` | Al recibir formulario de interés de un asistente | Contacto del patrocinador (contact_email) |
| `ContactRequestEmail` | Al recibir solicitud de contacto en networking | Asistente receptor (push + email opcional) |

---

## 13. Notas críticas de arquitectura

1. **Menú dinámico siempre desde API** — nunca hardcodeado en la app. Si el módulo no está en la config del rol, la ruta no existe en el menú.

2. **QR nunca expone el ID del usuario** — siempre HMAC-SHA256 firmado con APP_QR_SECRET incluyendo event_id. Sin el secret no se puede forjar.

3. **Offline primero en Fase 1** — agenda, speakers, docs y anuncios se cachean en AsyncStorage al login. Si no hay conexión, la app muestra el cache con banner de última sync.

4. **Tracking automático vía hook** — `useTracker` registra open/close automáticamente en cada screen al montarse/desmontarse. El developer no tiene que recordar llamarlo en cada módulo. Sin esto hay huecos en el analytics.

5. **activity_log es append-only** — nunca UPDATE, solo INSERT. Para duration: registrar `open` y `close` por separado, calcular `duration_seconds` en la query de analytics.

6. **Node solo maneja chat y votación** — todo lo demás pasa por Laravel. Node valida el token Sanctum de Laravel antes de cualquier operación de socket.

7. **Leads filtrados por vendedor** — query siempre con `vendor_attendee_id = auth()->user()->attendee->id`. Nunca exponer leads de otros vendedores.

8. **Multi-tenant desde día 1** — tabla organizations existe desde la Fase 0. Cada evento tiene organization_id. Aunque en Fase 1 solo hay una organización, el schema ya está listo.

9. **Zonas horarias** — guardar siempre en UTC en la DB. Convertir a la timezone del evento en el frontend. El campo `timezone` en la tabla events define la zona del evento.

10. **Sandbox mode** — evento con `is_sandbox: true` tiene datos ficticios para demos. No aparece en analytics reales. El cliente potencial puede probarlo sin contaminar métricas.

11. **Importación CSV** — el admin sube el archivo, Laravel lo procesa en un Job en background (Queue), envía invitaciones por email con link único por asistente. No bloquea el request.

12. **Templates de módulos** — al crear un evento con template 'congreso', el sistema copia los registros de `module_templates` a `modules` para ese evento. El admin puede personalizar después.

13. **Links de invitación** — cada evento tiene N links en `event_invitation_links`, cada uno con un token UUID. `POST /api/events/{id}/join` recibe el token, valida que esté activo y no vencido, registra al usuario como attendee con el rol definido en el link. Incrementa `uses_count`.

14. **Push open tracking** — cuando el usuario toca una notificación push, Expo llama `POST /api/track` con `action='push_open'` y `target_type='notification'`. El backend busca el registro en `push_notification_logs` y actualiza `opened_at`. Así se calcula el open rate.

15. **Password reset** — usa la tabla nativa `password_reset_tokens` de Laravel. Flujo: `POST /api/auth/forgot-password` → envía email con link firmado → usuario hace click → `POST /api/auth/reset-password` con token + nueva contraseña. Token expira en 60 min.

16. **Borrado de cuenta (Ley 1581)** — `DELETE /api/auth/account` anonimiza el usuario: name="Eliminado", email=UUID@deleted, borra photo, phone, company, linkedin. Conserva attendee records y activity_log por integridad referencial pero sin datos personales.

17. **Contenido estático** — los módulos como Dresscode y Logística guardan su contenido HTML en `static_content` por slug. El admin edita con Filament Tiptap editor y sube imágenes a R2. La app carga y cachea en AsyncStorage como los demás módulos.

18. **Sistema de bans** — un asistente está baneado si tiene registro en `attendee_bans` con `lifted_at IS NULL` y (`expires_at IS NULL` OR `expires_at > NOW()`). Al banear: revocar inmediatamente todos los tokens Sanctum del usuario. El middleware de auth verifica ban activo antes de cada request a la API. El ban es por evento (attendee_id), no global. El historial de bans se conserva siempre para auditoría.

19. **CRUD admin completo** — cada entidad del sistema tiene su Filament Resource. Nada se gestiona solo por API o directamente en DB. El admin panel es la única interfaz de gestión para el organizador.

20. **Presigned URLs para uploads** — nunca enviar archivos a través del servidor Laravel. Flujo: app pide `POST /api/files/presigned-url` → Laravel genera URL firmada de R2 (expira en 5 min) → app sube directo a R2 → app llama `POST /api/files/confirm` → Laravel guarda en `file_uploads`. Esto evita saturar el servidor con archivos binarios.

21. **Versionado de API** — todos los endpoints bajo `/api/v1/`. Al lanzar cambios breaking, crear `/api/v2/` manteniendo v1 activa hasta que todas las apps en producción migren. `GET /api/version` devuelve `{ min_version: "1.0.0", current_version: "1.2.0" }`. La app compara con su versión y muestra pantalla de "actualiza la app" si está por debajo del mínimo.

22. **Paginación estándar** — todos los endpoints de lista usan `?page=1&per_page=20`. Default: 20. Máximo: 100. Respuesta siempre incluye `meta: { current_page, per_page, total, last_page }`. Sin paginación, un evento con 5,000 asistentes mataría el servidor en una sola request.

23. **Evento borrador y registro** — evento con `is_published: false` no aparece en `GET /api/me/events` ni acepta registros. Solo admins pueden verlo. `registration_opens_at` y `registration_closes_at` controlan la ventana de registro independientemente de `is_published`. Si `registration_requires_approval: true`, los asistentes quedan en estado pendiente hasta que el admin aprueba.

24. **Estado post-evento** — cuando `end_date` pasa: la app muestra banner "Evento finalizado", acceso de solo lectura a agenda/speakers/docs/anuncios, QR desactivado, check-in desactivado, encuestas cerradas. Si el evento tiene `recording_url` en sesiones, esas pantallas siguen accesibles. Módulos como chat y votación se desactivan automáticamente.

25. **Universal Links y deferred deep links** — links de invitación y reset de contraseña deben abrir la app (no el browser). Configurar Apple Associated Domains y Android App Links apuntando al dominio de la API. Si el usuario no tiene la app instalada, se redirige al store y tras instalar, la app retoma el contexto del link original (Expo Linking + Branch.io o expo-linking `getInitialURL`).

26. **Permiso push — cuándo pedirlo** — iOS rechaza el 60% de los permisos si se piden al lanzar. Pedir contextualmente: cuando el asistente marca su primera sesión favorita, mostrar bottom sheet "¿Quieres que te avisemos 10 minutos antes?" — si acepta, ahí se lanza el prompt nativo de iOS/Android.

27. **Sanitización HTML** — todo HTML que el admin escriba en custom_pages debe pasar por HTMLPurifier antes de guardarse. Lista blanca de tags permitidos: `p, h1-h4, strong, em, ul, ol, li, a, img, table, tr, td, th, iframe`. El `iframe_url` se guarda separado y se embebe directamente sin pasar por el editor de HTML.

28. **Expiración de tokens Sanctum** — los tokens expiran en 30 días. La app debe detectar HTTP 401, hacer silent refresh si tiene un refresh token, o redirigir a login. Configurar en `config/sanctum.php`: `expiration: 43200` (minutos = 30 días).

29. **2FA para Filament admin** — habilitar 2FA (Google Authenticator) para todos los admins con rol `admin` o `admin_total`. Paquete: `filament/filament` ya incluye soporte. Obligatorio para cuentas con acceso a datos de asistentes.

30. **Soft deletes en entidades clave** — `sessions`, `speakers`, `documents`, `custom_pages`, `announcements` usan `deleted_at` (soft delete). Nunca borrar físicamente — permite recuperación accidental y mantiene integridad del `activity_log` que referencia estas entidades.

31. **Redis caché por rol** — las respuestas de módulos y contenido se cachean por `event_id + role` porque cada rol ve datos distintos. Clave: `v1:event:{id}:sessions:presencial`. Al invalidar, borrar todas las variantes del evento: `del v1:event:{id}:*`.

32. **Multi-evento: selector en app** — `GET /api/me/events` devuelve todos los eventos del usuario. Si tiene 1 evento activo, va directo. Si tiene varios, muestra pantalla de selección con nombre, logo y fecha de cada uno. El evento seleccionado se guarda en AsyncStorage como `activeEventId`. Al cambiar de evento, se limpia el caché local.

33. **Clonación de evento** — `POST /api/admin/events/{id}/clone` copia: módulos, sesiones, speakers, documentos, banners, sponsors (con sus servicios), encuestas, custom_pages, campos de registro. NO copia: asistentes, activity_log, check-ins, leads, sponsor_leads, contact_requests, notificaciones enviadas. El evento clonado queda en `is_published: false` para que el admin ajuste fechas antes de publicar.

34. **QR token rotation** — el admin puede regenerar el QR de un asistente desde Filament (en caso de screenshot compartido). Crea nuevo registro en `qr_tokens`, invalida el anterior. El asistente recibe push notificando que su QR fue regenerado.

35. **Kiosco offline** — si la tablet pierde internet: modo degradado donde muestra "Sin conexión — verificando localmente". Los últimos 500 QR validados se guardan en `localStorage` del kiosco. Check-ins offline se encolan en `localStorage` y sincronizan automáticamente al recuperar conexión.

36. **Sesión cancelada** — cuando `sessions.status = 'cancelled'`: push a todos los asistentes que la tienen en favoritos, la sesión aparece con badge "Cancelada" en la agenda, no se envía el recordatorio de 10 minutos.

37. **Click en banner → activity_log** — `action='banner_click'`, `target_id=banner.id`, `target_type='banner'`. Para el slideshow del home. Tracking separado al de patrocinadores.

37b. **Tracking de patrocinadores → activity_log** — Tres acciones registradas vía `useTracker`:
- `action='sponsor_view'`, `target_type='sponsor'`, `target_id=sponsor.id` — al abrir el stand
- `action='sponsor_favorite'`, `target_type='sponsor'`, `target_id=sponsor.id` — al dar like
- `action='sponsor_contact_form'`, `target_type='sponsor'`, `target_id=sponsor.id` — al enviar formulario
Estos eventos alimentan el panel de ROI por patrocinador en Filament: vistas únicas, favoritos acumulados, formularios enviados, ratio contacto/vista. El patrocinador puede pedir este reporte al organizador.

38. **Compartir sesión al calendario** — desde el detalle de sesión, botón "Agregar al calendario" genera un archivo `.ics` (iCal) con título, descripción, horario en timezone del evento, y `LOCATION`. Compatible con Google Calendar, Apple Calendar y Outlook.

39. **Export de leads — columnas definidas** — CSV exportado por el vendedor: `nombre, empresa, cargo, email, teléfono, tier (hot/warm/cold), nota, fecha_scan`. Ordenado por fecha descendente.

40. **Health check endpoint** — `GET /api/v1/health` verifica: conexión MySQL, conexión Redis, conexión R2, queue workers activos, Node Socket.IO reachable. Devuelve `{ status: "ok"|"degraded"|"down", checks: {...} }`. El kiosco lo llama cada 30s.

41. **Caché del app — MMKV + TanStack Query, NUNCA AsyncStorage para datos sensibles** — Token Sanctum en `expo-secure-store` (Keychain/Keystore). Datos de sesión/speakers/módulos en MMKV via TanStack Query persister. AsyncStorage solo para preferencias no sensibles (último tab abierto, onboarding visto).

42. **FlashList obligatorio para listas** — cualquier lista con posibilidad de superar 50 items usa `@shopify/flash-list` en lugar de `FlatList`. Esto incluye: agenda de sesiones, directorio de asistentes, lista de leads, mensajes de chat, anuncios.

43. **expo-image para todas las imágenes** — nunca usar `<Image>` nativo de React Native. `expo-image` tiene disk cache automático, placeholder blur hash mientras carga, y recicla memoria correctamente. Las URLs de R2 se sirven con parámetros de Cloudflare Image Resizing: `?width=400&quality=80&format=webp`.

44. **R2: bucket público vs privado** — logos, banners y fotos de speakers en bucket PÚBLICO (CDN Cloudflare, URLs permanentes, no necesitan auth). Documentos/presentaciones en bucket PRIVADO — se acceden solo con presigned URLs de lectura que expiran en 1 hora. Esto protege IP del organizador.

45. **Chat persistido en MySQL** — Socket.IO entrega el mensaje en tiempo real Y el backend lo persiste en `chat_messages`. Al abrir el chat por primera vez, la app carga los últimos 50 mensajes vía `GET /api/v1/events/{id}/chat/messages`. Paginación "load more" hacia atrás (scroll infinito hacia arriba).

46. **Votación en tiempo real — Redis como fuente de verdad** — durante una votación activa, los votos se acumulan en `HASH poll:{id}:votes {option_id: count}` en Redis (atómico, sin race conditions). Socket.IO hace broadcast del conteo actualizado cada vez que llega un voto. Al cerrar la votación, se vuelca a `live_poll_votes` en MySQL para el reporte final.

47. **Presencia en chat — heartbeat Redis** — al conectar socket: `SADD event:{id}:online attendee:{id}`. Heartbeat cada 20s: `EXPIRE event:{id}:online:{attendeeId} 35`. Al desconectar: `SREM`. El conteo de online se incluye en el broadcast de check-in aforo. `SCARD = O(1)`, escala sin problema.

48. **OTA updates — qué se puede y no se puede actualizar** — EAS Update solo actualiza el bundle JS/TS. NO puede actualizar: código nativo (nuevas librerías nativas, permisos nuevos, cambios en app.json). Si el cambio requiere código nativo, necesitas nueva versión en el store. Usar OTA solo para bugfixes y cambios de UI/lógica.

49. **TypeScript strict en todo el proyecto** — `tsconfig.json` con `"strict": true`. Esto activa: noImplicitAny, strictNullChecks, strictFunctionTypes. Sin esto, TypeScript no detecta el 70% de los bugs en tiempo de compilación. No negociable.

50. **Testing mínimo requerido** — con Pest (Laravel): tests de feature para auth (login, registro, reset password), QR (generar, validar, duplicado), bans, permisos de rol. Con Jest + Detox (Expo): smoke tests del flujo login → home → agenda → QR. Sin tests, los cambios futuros son ciegos.

51. **i18n desde Fase 1 — nunca después** — usar `expo-localization` + `i18n-js`. Archivos de traducción en `/locales/es.json` y `/locales/en.json`. Cada string en la app usa `t('key')`. Agregar idioma después = buscar y reemplazar cientos de strings hardcodeados en toda la app. Costo ahora: 2 horas. Costo después: semanas.

52. **Formato de error estándar** — todas las respuestas de error devuelven:
```json
{ "message": "Descripción legible", "code": "SNAKE_CASE_CODE", "errors": { "field": ["detalle"] } }
```
Códigos custom definidos: `ATTENDEE_BANNED`, `QR_INVALID`, `QR_ALREADY_USED`, `EVENT_NOT_PUBLISHED`, `EVENT_FULL`, `REGISTRATION_CLOSED`, `SUBSCRIPTION_SUSPENDED`, `DUPLICATE_LEAD`, `SESSION_CANCELLED`, `TOKEN_EXPIRED`, `VALIDATION_ERROR`, `SPONSOR_ALREADY_CONTACTED`, `SPONSOR_NO_CONTACT_EMAIL`, `CONTACT_ALREADY_SENT`, `CONTACT_BLOCKED`. La app maneja estos códigos para mostrar mensajes específicos al usuario.

53. **Transacciones de base de datos obligatorias** — estas operaciones DEBEN estar en `DB::transaction()`: check-in (insert + Redis INCR + Job), ban (insert ban + revoke tokens), lead scan (insert lead + activity_log), rotación de QR (delete + insert), importación CSV (múltiples inserts), cambio de rol (insert role_change + update attendee). Sin transacción, un fallo parcial deja el sistema inconsistente.

54. **Idempotency keys** — endpoints que crean recursos deben aceptar header `Idempotency-Key: {uuid}`. Laravel guarda la key en Redis con TTL 24h y el response. Si llega la misma key, devuelve el response guardado sin re-ejecutar. Obligatorio para: `POST /checkin`, `POST /leads/scan`, `POST /track`, `POST /expo-token`. Las redes móviles reintentan requests silenciosamente.

55. **Capa de servicios (Service Classes)** — la lógica de negocio no vive en Controllers ni Models. Usar Service Classes: `CheckInService`, `QrTokenService`, `BanService`, `NotificationService`, `AnalyticsService`. El Controller solo valida la request y llama al Service. El Service orquesta: DB + caché + Jobs + Events.

56. **Laravel Events/Listeners para desacoplamiento** — cuando datos cambian, lanzar eventos de Laravel que disparan listeners independientes:
- `SessionUpdated` → `InvalidateSessionCache` + `SendSilentPushToAttendees`
- `AttendeeCheckedIn` → `IncrementRedisAforo` + `LogActivity`
- `AnnouncementPublished` → `InvalidateCache` + `SendPushToAllAttendees`
Esto evita que los controllers sean monstruos de 200 líneas.

57. **SSRF en iframe_url** — validar que `iframe_url` no apunte a IPs privadas (192.168.x.x, 10.x.x.x, 172.16-31.x.x, localhost, 127.0.0.1). Un admin malicioso podría escanear la red interna del servidor. Usar librería `symfony/validator` con constraint de URL pública.

58. **charset utf8mb4 obligatorio** — TODOS los CREATE TABLE deben incluir `CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci`. Sin esto, los emojis en el chat generan "Incorrect string value" error. Configurar también en `config/database.php`: `charset: 'utf8mb4'`, `collation: 'utf8mb4_unicode_ci'`.

59. **Redis — dos bases de datos separadas** — Redis DB 0 para caché API (`CACHE_STORE=redis`, `maxmemory-policy allkeys-lru`). Redis DB 1 para queues (`QUEUE_CONNECTION=redis`, `maxmemory-policy noeviction`). Si comparten DB, una operación de queue puede ser expulsada cuando Redis llena caché. Configurar en `.env`: `REDIS_CACHE_DB=0`, `REDIS_QUEUE_DB=1`.

60. **pm2 para Node.js en producción** — configuración en `ecosystem.config.js`:
```js
module.exports = {
  apps: [{
    name: 'eventos-socket',
    script: 'server.js',
    instances: 'max',        // usa todos los cores del CPU
    exec_mode: 'cluster',
    max_memory_restart: '500M',
    env_production: { NODE_ENV: 'production', PORT: 3001 }
  }]
}
```
Instalar con: `pm2 startup` para que arranque al reiniciar el VPS.

61. **Supervisor para Laravel Queue Workers** — archivo `/etc/supervisor/conf.d/eventos-worker.conf`:
```
[program:eventos-worker]
command=php /var/www/eventos/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
numprocs=3
autostart=true
autorestart=true
```
Sin Supervisor, los workers mueren silenciosamente y los emails/pushes dejan de enviarse.

62. **PHP-FPM + opcache tuning** — en `/etc/php/8.2/fpm/pool.d/www.conf`: `pm.max_children = 20` (para 4GB RAM). En `php.ini`: `opcache.enable=1`, `opcache.validate_timestamps=0`, `opcache.memory_consumption=256`. Esto dobla el throughput de Laravel sin cambiar código.

63. **MySQL tuning mínimo** — en `my.cnf`: `innodb_buffer_pool_size = 2G` (50% RAM en VPS de 4GB), `max_connections = 300`, `query_cache_size = 0` (deprecated en MySQL 8). Sin esto, MySQL usa defaults conservadores y desperdicia RAM.

64. **VPS hardening básico** — UFW: solo puertos 22, 80, 443, 3001 abiertos. `sudo ufw enable`. SSH: deshabilitar password auth en `/etc/ssh/sshd_config` → `PasswordAuthentication no`. fail2ban para SSH y Nginx. Cambiar puerto SSH a algo distinto de 22 (opcional pero recomendado).

65. **Laravel Telescope — solo en local/staging** — instalar con `--dev`. En `config/telescope.php`: `'enabled' => env('TELESCOPE_ENABLED', false)`. NUNCA `TELESCOPE_ENABLED=true` en producción — expone todas las requests, queries y datos sensibles.

66. **APP_DEBUG=false en producción** — si `APP_DEBUG=true` en prod, Laravel devuelve stack traces completos incluyendo variables de entorno al usuario. Verificar en deployment checklist.

67. **Zero-downtime deploys durante eventos en vivo** — usar `php artisan down --retry=60` solo si es necesario. Para cambios normales: Forge hace el deploy sin `artisan down`. Para migraciones que modifican tablas grandes: usar migraciones compatible-backward (add column, luego remove old column en deploy posterior). Nunca cambiar tipo de columna en producción durante un evento.

68. **Graceful shutdown de Node.js** — en el servidor Node, escuchar `SIGTERM` y `SIGINT`:
```js
process.on('SIGTERM', () => {
  io.close(() => { server.close(() => process.exit(0)); });
});
```
Sin esto, pm2 mata el proceso abruptamente y 10,000 usuarios se desconectan con error.

69. **Unity + Socket.IO — protocolo de mensajes** — definir el contrato entre Unity y Node:
- Unity → Node: `{ type: 'game:state', payload: { screen, timer, scores } }` → Node broadcast a teléfonos
- Teléfono → Node: `{ type: 'player:input', payload: { action, value } }` → Node forward a Unity
- Unity → Node: `{ type: 'game:end', payload: { results: [...] } }` → Node guarda en game_results → POST a Laravel
Node.js NO interpreta los payloads — solo los enruta. Toda la lógica del juego vive en Unity.

70. **Sistema de licencias — middleware Laravel** — `CheckSubscription` middleware en todas las rutas de la API:
```
active      → request pasa normal
grace       → request pasa + header X-Subscription-Warning: "Vence en 3 días"
suspended   → HTTP 402 con code: SUBSCRIPTION_SUSPENDED
cancelled   → HTTP 402 con code: SUBSCRIPTION_CANCELLED
```
Filament super-admin puede cambiar el status manualmente. En Fase 3, Stripe webhook lo actualiza automáticamente.

71. **Funnel de registro — métrica clave para el organizador** — derivar del activity_log + attendees:
- Invitados (event_invitation_links.uses_count o CSV importados)
- Registrados (attendees count por evento)
- Check-in realizados (checked_in_at NOT NULL)
- Activos (activity_log en últimas 2 horas)
Esta métrica es la más valiosa del dashboard: "De 500 invitados, 423 se registraron (85%), 387 hicieron check-in (77%), 312 están activos ahora (62%)."

72. **Retención de datos — política definida** — por Ley 1581 debes tener una política documentada:
- Datos personales de asistentes: 2 años post-evento, luego anonimización
- activity_log: 1 año, luego archive a cold storage (R2 como JSON comprimido)
- Logs del sistema (Nginx/Laravel): 90 días
- push_notification_logs: 6 meses
- admin_audit_log: 3 años (auditoría)
Agregar Job mensual `DataRetentionJob` que ejecuta las limpiezas automáticamente.

73. **Privacy Policy y Terms of Service — obligatorios antes del App Store** — usar Iubenda (~$27/año) o Termly (~$10/mes) para generar documentos conformes con GDPR + Ley 1581. Apple y Google requieren URL pública antes de aprobación. Guardar la URL en `organizations.privacy_policy_url`. La app muestra el link en el registro y en la pantalla de perfil.

74. **Data portability — Ley 1581 / GDPR** — `GET /api/v1/me/data-export` genera un JSON con: datos del perfil, historial de eventos, activity_log propio, leads capturados. Job en background, email cuando está listo. Plazo máximo de entrega: 30 días según la ley.

75b. **Dark mode — implementación con NativeWind** — `userInterfaceStyle: "automatic"` en app.json. NativeWind v4 usa prefijo `dark:` igual que Tailwind web: `className="bg-white dark:bg-gray-900 text-gray-900 dark:text-white"`. Definir design tokens desde el inicio: `colors.background`, `colors.surface`, `colors.text`, `colors.border` en modo claro y oscuro. El `primary_color` del branding del cliente se aplica igual en ambos modos. Usar `useColorScheme()` de React Native para leer la preferencia del sistema.

75. **Expo SDK fijado — política de upgrades** — fijar a SDK 52. No actualizar el SDK hasta que: (a) EAS Build lo marque como required, (b) todas las librerías del proyecto tengan compatibilidad confirmada con el nuevo SDK, (c) hayas testeado en un branch separado. Un upgrade de SDK sin pruebas rompe builds de producción.

76. **Browsershot — generación de PDFs en cola con concurrencia limitada** — Browsershot levanta un proceso Chrome headless por cada PDF. En un VPS de 2-4 GB RAM, un render puede consumir 300-500 MB momentáneamente. Nunca generar PDFs de forma síncrona en un request HTTP. Siempre despachar un Job (`GeneratePdfJob`) con la vista Blade y los datos necesarios. Configurar en `config/horizon.php` una queue dedicada `pdf` con `maxProcesses: 1` y `tries: 3`. Al terminar el job, subir el PDF a Cloudflare R2 y notificar al usuario por email o push con el link presigned. Esto evita que la generación concurrente de PDFs mate el servidor bajo carga.

77. **Patrocinadores — campos opcionales ocultos** — En la app, cada sección del stand virtual (banner hero, descripción, servicios, datos de contacto) solo se renderiza si el campo no es NULL. Nunca mostrar secciones vacías ni placeholders. El botón "Contactar" solo aparece si `show_contact_button=true` AND `contact_email IS NOT NULL` — sin email de destino no hay a dónde enviar el lead. El admin activa los campos disponibles en Filament como toggles individuales por patrocinador. El botón like/favorito es el único elemento siempre visible sin importar qué campos tenga el patrocinador.

78. **Networking — privacidad y anti-acoso** — Tres capas de protección: (1) `networking_visible=false`: el asistente desaparece del directorio completamente — no aparece para nadie. (2) Status `ignored`: el emisor no recibe notificación de rechazo, simplemente su solicitud queda sin respuesta visible. (3) `contact_blocks`: el bloqueado no aparece en el directorio del que bloqueó y no puede enviar nuevas solicitudes. El bloqueo es silencioso — el bloqueado no sabe. Query base del directorio: `WHERE networking_visible=true AND attendee_id NOT IN (SELECT blocked_attendee_id FROM contact_blocks WHERE blocker_attendee_id=:viewer) AND attendee_id NOT IN (SELECT blocker_attendee_id FROM contact_blocks WHERE blocked_attendee_id=:viewer)`.

79. **Email de lead al patrocinador** — Al crear un `sponsor_lead`, si `contact_email` está configurado, dispatchar `NotifySponsorLeadJob` con: nombre del asistente, empresa, cargo, teléfono (si tiene), servicios seleccionados, mensaje opcional. Registrar `notified_sponsor_at` al éxito. Reintentar 3 veces con backoff exponencial. El admin ve todos los leads en el panel de ROI aunque el email falle — el job es best-effort, no bloquea la confirmación al asistente.

80. **Contactos mutuos en networking** — Un asistente A y B son contactos si existe `contact_request` con `status='accepted'` donde A=sender y B=receiver, O donde B=sender y A=receiver. La aceptación es unidireccional (B acepta la solicitud de A) pero el resultado es bidireccional (ambos se ven como contactos). Query para `GET /me/contacts`: `WHERE (sender_attendee_id=:me AND status='accepted') OR (receiver_attendee_id=:me AND status='accepted')`, luego JOIN con attendees para los datos del otro extremo.

81. **Panel ROI de patrocinadores en Filament** — Widget por patrocinador con: (1) vistas únicas del stand, (2) favoritos acumulados, (3) formularios de interés enviados, (4) ratio contacto/vista en %, (5) servicios más demandados (ranking). Los datos salen de `activity_log` (vistas/favoritos) y `sponsor_leads` (formularios). Este reporte es el argumento de venta para que el patrocinador renueve al siguiente evento.

82. **S1.6 — Tablas sponsors son completamente nuevas** — `sponsors`, `sponsor_services`, `sponsor_leads` y `sponsor_favorites` no existen en ninguna migración del backend (verificado 2026-03-29). Se crean como migración nueva en S1.6. `banners.sponsor_name` es un string libre independiente de la tabla `sponsors` — los banners del slideshow del home y los stands virtuales son entidades distintas, sin relación entre sí. El `ContentObserver` genérico deberá extenderse para invalidar caché al cambiar el modelo `Sponsor`.

84. **S1.6 — Stand Teams: el sponsor ES el stand** — `sponsors` tiene `owner_attendee_id` (vendedor responsable) y `max_collaborators` (cupos, configurado por el admin en Filament). El owner gestiona su equipo desde la app. Los colaboradores NO cambian de rol; reciben `has_vendor_access=true` en `attendees` y `sponsor_id` apuntando al stand. El módulo de leads/scanner se activa para ellos en `GET /events/{id}/modules` si `has_vendor_access=true`.

85. **S1.6 — Invitación por email a stand** — Al agregar un colaborador por email: (1) si ya tiene attendee en el evento → `has_vendor_access=true` inmediato, `stand_members.status=active`; (2) si no existe → `stand_members.status=pending` + email con link de registro. Al registrarse o hacer login, `AuthController` busca `stand_members` pendientes por email y los activa automáticamente. Este hook va en `AuthController@login` y `AuthController@register`.

86. **S1.6 — Pool de leads por stand** — `leads.sponsor_id` define el pool. UNIQUE constraint: `(sponsor_id, scanned_attendee_id)` cuando hay sponsor_id, `(vendor_attendee_id, scanned_attendee_id)` cuando no. En `GET /api/v1/leads`: si el vendedor tiene `sponsor_id` → query por todos los `stand_members` activos del stand. Duplicado dentro del pool → 409 `ALREADY_IN_STAND_POOL` con nombre de quien lo capturó. `PUT /api/v1/leads/{id}` cualquier miembro puede editar → registrar en `lead_edits` (field, old_value, new_value).

87. **S1.5 — Stand Teams: cambios retroactivos necesarios** — Los leads capturados en S1.5 tienen `sponsor_id=NULL` (leads personales). Cuando un vendedor se une a un stand en S1.6, sus leads anteriores NO se migran al pool automáticamente — quedan como personales. Solo los nuevos scans van al pool. Esto es intencional: evita sorpresas con leads ya capturados.

83. **S1.7 — Reutilizar tablas existentes para networking** — `contact_requests` y `contact_blocks` son tablas nuevas (S1.7). Sin embargo, `attendees.networking_visible` ya existe desde S0.2 — no hay que tocar la migración de attendees. Los datos del perfil público del directorio (name, company, job_title, photo_url, linkedin_url) vienen directamente de la tabla `users`, no hay tabla `attendee_profiles` separada. Query para `GET /me/contacts`: `WHERE (sender_attendee_id=:me OR receiver_attendee_id=:me) AND status='accepted'`, luego JOIN con attendees+users para obtener datos del otro extremo.

88. **S1.12 — Tracks de sesión (implementado 2026-04-04)** — Tabla `session_tracks`: `id`, `event_id` FK cascadeDelete, `name` varchar(100), `color` varchar(7) default `#6366f1`, `description` text nullable, `sort_order` unsignedSmallInt, timestamps. Index en `(event_id, sort_order)`. Columna `track_id` nullable FK en `event_sessions`, nullOnDelete. Los tracks son un módulo de datos puro — no hay entrada en `module_templates`. En la app, los chips de filtro solo aparecen si `availableTracks.length > 0` (data-driven, no bandera). `GET /api/v1/events/{id}/tracks` devuelve `[{id, name, color, description}]`. El response de sesiones incluye `track: {id, name, color}` via `whenLoaded`.

89. **S1.12 — Optimización Filament (implementado 2026-04-04)** — OPcache habilitado con 256MB (`zend_extension=opcache`, `opcache.enable=1`, `memory_consumption=256`, `interned_strings_buffer=16`, `max_accelerated_files=20000`, `validate_timestamps=1`, `revalidate_freq=0`). SPA mode activado con `->spa()` en `AdminPanelProvider` (elimina recarga de assets JS/CSS en navegación). `->deferLoading()` en **todos** los resources Filament (página carga inmediata, tabla carga lazy). En dev: `revalidate_freq=0` para ver cambios; en producción cambiar a `validate_timestamps=0`.

90. **S1.12 — Fix API 401 JSON** — Middleware `AuthenticateApi` extiende `Illuminate\Auth\Middleware\Authenticate` y sobreescribe `redirectTo()` retornando `null`. Además, en `bootstrap/app.php` se añade handler explícito: `$exceptions->render(function (AuthenticationException $e, $request) { if ($request->is('api/*')) { return response()->json(['message' => 'Unauthenticated.'], 401); } })`. Ambas capas son necesarias: el middleware evita buscar la ruta `login`, y el handler garantiza JSON en rutas API. Todas las rutas API usan `App\Http\Middleware\AuthenticateApi:sanctum` en lugar de `auth:sanctum`.

---

## 14. Configuración de infraestructura de producción

### Checklist pre-deploy a producción

```
□ APP_DEBUG=false en .env producción
□ APP_ENV=production
□ TELESCOPE_ENABLED=false
□ Redis DB 0 para caché, DB 1 para queues configurados
□ opcache.validate_timestamps=0 en PHP
□ pm2 corriendo con ecosystem.config.js
□ Supervisor corriendo con 3 workers de queue
□ Cron de Laravel Scheduler configurado
□ UFW activo con solo puertos necesarios
□ SSH password auth deshabilitado
□ fail2ban activo
□ SSL Let's Encrypt con auto-renovación
□ Log rotation configurado
□ Monitoring de CPU/RAM/Disco activo (UptimeRobot o similar)
□ Sentry DSN configurado en Laravel y Expo
□ composer audit sin vulnerabilidades críticas
□ npm audit sin vulnerabilidades críticas
□ GitHub Actions pipeline funcionando (staging deploy automático)
□ Backup de MySQL configurado (diario mínimo)
□ R2 bucket privado para documentos con presigned URLs
```

### pm2 ecosystem.config.js

```javascript
module.exports = {
  apps: [{
    name: 'eventos-socket',
    script: './server.js',
    instances: 'max',
    exec_mode: 'cluster',
    max_memory_restart: '500M',
    error_file: './logs/pm2-error.log',
    out_file: './logs/pm2-out.log',
    env_production: {
      NODE_ENV: 'production',
      PORT: 3001,
      REDIS_URL: 'redis://127.0.0.1:6379/2'
    }
  }]
};
```

### Supervisor /etc/supervisor/conf.d/eventos.conf

```ini
[program:eventos-worker]
command=php /var/www/eventos/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --queue=default,notifications,emails
directory=/var/www/eventos
numprocs=3
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/eventos/storage/logs/worker.log
```

---

*Documento v2.7 — Contexto maestro EventOS para Claude Code*
*Actualización v2.7 (2026-04-06): Estrategia de fases reorganizada — todo funcional primero (Fase 1 = 1.1→1.26 + Pulido + Stress Test), UI como barrido final una sola vez. Sessions 1.14–1.26 absorben lo que era Fase 2 (streaming, Q&A, evaluaciones, photobooth, certificados, reportes, analytics, matchmaking, social wall, gamification, passport, floor plan, reports detallados). Fase 2 reducida a: Web Next.js, Photo Contest, Video calls LiveKit, Proximity chat. Ver EventOS_Roadmap.md.*
*Sesiones Fase 1 completadas (2026-04-06): 0.1–0.4, 1.1–1.13b, 1.x(Storage), 1.x(Banners), 1.x-A(Onboarding), 1.x-B(Onboarding animaciones) | Próximas: 1.14 (Streaming + Mi Agenda)*
*✅ Todo confirmado: Bundle ID = com.kasproduction.eventos | Nombre app = "EventOS" | Dark mode = SÍ | iOS 15+ | Android API 29+ | ES + EN | Facturación manual Fase 1*
