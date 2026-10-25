# White-Label + Rebranding — EventOS

> Marca del cliente con el mismo codigo: app propia en las tiendas (solo desde la cuenta de developer del cliente) o
> webapp con su dominio y su marca (sin tienda). Desde la cuenta de KasProduction solo va la app generica.
> Corregido 2026-10-25 (decisiones.md): reglas 4.2.6 y 4.3 de Apple.

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

## Flujo para un cliente nuevo (opcion A, en la cuenta del cliente)

```
Antes: el cliente tiene su cuenta de developer y nos invita como developer
Dia 1: Cliente entrega logo + colores + nombre del evento
Dia 1: Crear carpeta assets/clients/nombre/ + config (2-3 horas)
Dia 1: CLIENT=nombre eas build --auto-submit (20 min build en nube)
Dia 2-3: Apple aprueba (~24-48h)
Dia 2: Google aprueba (~horas)
Dia 3: App en las tiendas con la marca del cliente
```

**Tiempo total por cliente nuevo: 3 dias (la mayoria es espera de aprobacion).**

## Cuentas de developer

| Que app | Cuenta | Costo |
|---------|--------|-------|
| App generica EventOS (opcion B) | Cuenta de KasProduction | $99/ano Apple + $25 una vez Google |
| App propia de un cliente (opcion A) | Solo la cuenta de developer del cliente | El cliente paga su cuenta y nos invita como developer |

**Regla (2026-10-25):** desde la cuenta de KasProduction solo se publica la app generica. Apple no acepta apps hechas
desde una plantilla comercial publicadas por el proveedor de la plantilla en nombre de cada cliente (regla 4.2.6: las
publica el dueño del contenido, desde su cuenta) ni varias apps casi iguales desde una misma cuenta (regla 4.3, spam).
Antes este documento ponia a los clientes pequeños y medianos con app propia en la cuenta de KasProduction: no es posible.

## Modelo de negocio

| Opcion | Descripcion | Para quien |
|--------|-------------|------------|
| **A. App propia por cliente** | "Bintec 2026" en las tiendas, marca 100% del cliente. **Solo desde la cuenta de developer del cliente** | Agencias, corporativos ($5K+ USD) con cuenta propia |
| **B. App generica EventOS** | Usuario abre EventOS → selecciona su evento. Desde la cuenta de KasProduction | Clientes pequenos ($800-1.5K/mes SaaS) |
| **C. Webapp con la marca del cliente** | Su dominio (`eventos.cliente.com`), sus colores y su logo; sin tienda ni cuenta de developer | Marca propia sin app en tiendas (quien no tiene cuenta de developer) |
| **D. Combinacion** | A o C para quien quiere marca propia + B para el resto | Escala con multiples segmentos |

## Web app white-label (opcion C)

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
