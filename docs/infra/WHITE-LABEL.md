# White-Label + Rebranding — EventOS

> Cada cliente recibe una app con su propia marca. El codigo es el mismo.

---

## Como funciona

Nombre, icono, splash, colores, bundle ID — todo configurable por cliente. Solo cambia la configuracion de build.

## Implementacion tecnica

**Paso 1:** Migrar `app.json` estatico → `app.config.js` dinamico

```js
// app.config.js
const CLIENT = process.env.CLIENT || 'eventos';

const clients = {
  eventos: {
    name: 'EventOS',
    slug: 'eventos',
    bundleId: 'com.eventos.app',
    icon: './assets/clients/eventos/icon.png',
    splash: './assets/clients/eventos/splash.png',
  },
  bintec: {
    name: 'Bintec 2026',
    slug: 'bintec-2026',
    bundleId: 'com.bintec2026.app',
    icon: './assets/clients/bintec/icon.png',
    splash: './assets/clients/bintec/splash.png',
  },
};

const config = clients[CLIENT];
module.exports = { expo: { name: config.name, slug: config.slug, ... } };
```

**Paso 2:** Estructura de assets por cliente

```
assets/clients/
  eventos/       # Default (EventOS generico)
    icon.png
    splash.png
  bintec/        # Bancolombia / Bintec
    icon.png
    splash.png
```

**Paso 3:** Build con un comando

```bash
CLIENT=bintec eas build --profile production --platform all --auto-submit
```

## Tiempos de aprobacion en tiendas

| Tienda | Primera vez | Actualizaciones |
|--------|-------------|-----------------|
| Apple App Store | 24-48 horas | 12-24 horas |
| Google Play | 2-7 dias (primera vez) | Horas (a veces minutos) |

## Que se puede cambiar sin rebuild (desde Filament admin)

- Activar/desactivar modulos (networking, photobooth, gamificacion, etc.)
- Colores del evento (primary_color dinamico)
- Logo y banner del evento
- Configurar agenda, speakers, sponsors
- Contenido completo del evento

## Que requiere rebuild (1 comando + aprobacion tiendas)

- Nombre de la app en las tiendas
- Icono de la app
- Splash screen
- Bundle ID (solo se define una vez)

## Flujo para un cliente nuevo

```
Dia 1: Cliente entrega logo + colores + nombre del evento
Dia 1: Crear carpeta assets/clients/nombre/ + config (2-3 horas)
Dia 1: CLIENT=nombre eas build --auto-submit (20 min build en nube)
Dia 2-3: Apple aprueba (~24-48h)
Dia 2: Google aprueba (~horas)
Dia 3: App en las tiendas con la marca del cliente
```

**Tiempo total por cliente nuevo: 3 dias (la mayoria es espera de aprobacion).**

## Cuentas de developer

| Escenario | Cuenta | Costo |
|-----------|--------|-------|
| Clientes pequenos/medianos | Tu cuenta (Kasproduction) | $99/ano Apple + $25 una vez Google |
| Corporativos grandes | Cuenta del cliente | El cliente paga su cuenta, te invita como developer |

Apple permite hasta 30 apps por cuenta. Google no tiene limite.

## Modelo de negocio

| Opcion | Descripcion | Para quien |
|--------|-------------|------------|
| **A. App propia por cliente** | "Bintec 2026" en las tiendas, marca 100% del cliente | Agencias, corporativos ($5K+ USD) |
| **B. App generica EventOS** | Usuario abre EventOS → selecciona su evento | Clientes pequenos ($800-1.5K/mes SaaS) |
| **C. Ambas** | White-label premium + app generica para el resto | Escala con multiples segmentos |

## Web app white-label

- Dominio del cliente: `eventos.bintec.com.co` → apunta al mismo backend
- CSS variables para colores/logo → cambia por evento automaticamente
- Mismo concepto: un codebase, multiples marcas

## Setup necesario (una sola vez)

| Tarea | Tiempo |
|-------|--------|
| Migrar app.json → app.config.js | ~1 hora |
| Estructura assets/clients/ | ~30 min |
| EAS build profiles por cliente | ~30 min |
| Documentar proceso | ~30 min |
| **Total** | **~2-3 horas** |
