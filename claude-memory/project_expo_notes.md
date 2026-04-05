---
name: EventOS — notas técnicas del Expo app (Sesión 0.3)
description: Gotchas y decisiones técnicas de la app Expo SDK 53
type: project
---

Notas de la Sesión 0.3 (2026-03-28).

## SDK 55 (actualizado desde 53 — 2026-03-29)

El proyecto fue actualizado a Expo SDK 55. React Native 0.83.4, React 19.2.0.

**Deps nuevas requeridas por el upgrade:**
- `react-native-worklets` (peer dep de reanimated v4 — el plugin de babel sigue siendo `react-native-reanimated/plugin` que internamente delega)
- `react-native-safe-area-context` (peer dep de expo-router, no estaba instalado)
- `react-native-screens` (peer dep de expo-router, no estaba instalado)
- `react-dom` (requerido por @expo/log-box en SDK 55)
- `react-native-nitro-modules` (peer dep de react-native-mmkv v4)
- `expo-linking` (peer dep de expo-router)

**Cambios en app.json para SDK 55:**
- `newArchEnabled` eliminado del root (New Arch es default en SDK 55)
- `ios.deploymentTarget` eliminado del config directo (solo en expo-build-properties)
- `android.minSdkVersion` eliminado del config directo (solo en expo-build-properties)
- `adaptiveIcon.backgroundColor` reemplazado con `backgroundImage: ./assets/android-icon-background.png`

**react-native-reanimated 4.x:**
- Plugin de babel: `react-native-reanimated/plugin` (sin cambios, internamente usa react-native-worklets/plugin)
- API compatible con 3.x para los hooks usados en el proyecto (useSharedValue, useAnimatedStyle, etc.)

---

## SDK 53 (anterior)

Se instaló Expo SDK 53 (no 52) porque es el latest stable. El documento maestro decía SDK 52 pero 53 ya estaba estable.

## expo-localization sin dist-tag sdk-53

`expo-localization` no tiene dist-tags `sdk-52` ni `sdk-53`. Solo existe `sdk-50`, `sdk-51`, `canary`, `next`, `latest` (que apunta a canary).

**Solución:** Usar `npx expo install expo-localization` que auto-resuelve la versión compatible con el SDK instalado. Instaló `16.1.6`.

**Why:** Instalar con `npm install expo-localization@latest` instala canary (versión inestable).

## react-native-mmkv v4 — API nueva

`react-native-mmkv` v4 cambió la API completamente:
- ANTES: `new MMKV({ id: 'my-storage' })`
- AHORA: `createMMKV({ id: 'my-storage' })` (import named)
- `.delete(key)` ya no existe → usar `.remove(key)`

**How to apply:** En cualquier uso de MMKV en el proyecto, usar `createMMKV` y `.remove()`.

## index.ts debe ser expo-router/entry

Con Expo Router, `index.ts` debe contener solo:
```ts
import 'expo-router/entry';
```
No usar `registerRootComponent(App)`.

## App.tsx eliminado

Expo Router maneja el entry point. `App.tsx` fue eliminado. El root layout está en `app/_layout.tsx`.

## Estructura de rutas

```
app/
  _layout.tsx          ← QueryClient + auth hydration + i18n
  index.tsx            ← Redirect a (auth)/login o (app)/...
  (auth)/
    _layout.tsx
    login.tsx
  (app)/
    (presencial)/
      _layout.tsx
      (tabs)/          ← inicio, agenda, networking, profile
    (virtual)/
      _layout.tsx
      (tabs)/          ← inicio, agenda, profile
    (vendedor)/
      _layout.tsx
      (tabs)/          ← index (leads), scanner, profile
```

## Auth store hydration

El auth store (Zustand) se hidrata en el root layout con `hydrate()` que lee desde `expo-secure-store`. Mientras no está hidratado, se muestra `SplashLoader` con barra de carga animada.

**Why:** Evita flash de la pantalla de login antes de saber si hay sesión activa.

## Ruta del proyecto

`C:\Users\Kasproduction\Projects\eventos-app`

---

## Notas S1.3b — Contenido app

### FlashList v2 — sin estimatedItemSize
`@shopify/flash-list` v2 (instalado: 2.3.1) eliminó el prop `estimatedItemSize`. No pasarlo. En v1 era requerido, en v2 no existe.

### app/(app)/_layout.tsx como Stack compartido
Para pantallas de contenido accesibles desde cualquier rol (speakers, docs, anuncios, pages), se creó `app/(app)/_layout.tsx` como Stack navigator. Las pantallas se ponen en `app/(app)/` y se empujan via `router.navigate()`.

### router.navigate() vs router.push() en ModuleMenu
- `push()`: siempre añade al historial (puede duplicar tabs)
- `navigate()`: si ya es el tab activo, lo activa; si es stack screen, lo empuja
- **Usar `navigate()` en el ModuleMenu para evitar duplicados en tabs**

### react-native-webview requiere iOS 15.1+
`react-native-webview` exige `ios.deploymentTarget >= 15.1`. Actualizado de 15.0 a 15.1 en app.json (ambos en `ios.deploymentTarget` y en el plugin `expo-build-properties`).

### Componentes compartidos entre roles
`components/screens/AgendaScreen.tsx` → compartido entre presencial y virtual via re-export:
```tsx
// app/(app)/(presencial)/(tabs)/agenda.tsx
import { AgendaScreen } from '@/components/screens/AgendaScreen';
export default AgendaScreen;
```

---

## Notas de testing en dispositivo físico (post S1.3b)

### Expo Go en Android — SDK mismatch
Expo Go en Play Store instala la versión más nueva (SDK 54). El proyecto usa SDK 53. Son incompatibles directamente: al intentar abrir la app aparece un error indicando que el SDK del proyecto no coincide con el de Expo Go.

**Pendiente de resolver:** Decidir entre:
1. Actualizar proyecto a SDK 54 (upgrade limpio)
2. Usar development build via EAS (más correcto para producción)

**Recomendación:** Hacer upgrade a SDK 54 en la próxima sesión disponible.

### Expo Go en iOS
La app de Cámara nativa de iOS no siempre muestra la opción de abrir en Expo Go. Solución: abrir Expo Go directamente y usar "Scan QR code" desde adentro de la app.

### Error "failed download remote update"
El celular no podía alcanzar la PC en la misma red. Solución: usar `npx expo start --tunnel` (usa ngrok, crea URL pública, no depende de la red local). Útil para pruebas cuando PC y celular no están en la misma subred.

### Abrir localhost en navegador del teléfono
Si se abre `localhost:8082` en el navegador del teléfono aparece la página web del servidor Expo (dice "npx expo start"). Eso no es la app — hay que usar Expo Go y escanear el QR.

---

## Notas S1.5 — Leads (Vendedor Scanner)

### expo-file-system SDK 55 — API legacy
`cacheDirectory` y `downloadAsync` ya NO existen en el namespace principal de `expo-file-system`. Están en el sub-módulo legacy:
```ts
import { cacheDirectory, downloadAsync } from 'expo-file-system/legacy';
```
Usar `import * as FileSystem from 'expo-file-system'` con `FileSystem.cacheDirectory` da error de tipos.

### expo-camera SDK 55 — CameraView
Para escanear QR usar `CameraView` con `barcodeScannerSettings={{ barcodeTypes: ['qr'] }}` y `onBarcodeScanned`. Pasar `undefined` en `onBarcodeScanned` cuando ya se está procesando (previene múltiples scans simultáneos).

### Rutas tipadas Expo Router — pantallas nuevas
Al crear una pantalla nueva, las rutas tipadas de Expo Router no se actualizan hasta correr `npx expo start`. Temporalmente usar `'/(app)/lead-detail' as any` en `router.push`. Se resuelve solo al iniciar el servidor de desarrollo.

### expo-camera y expo-sharing no estaban instalados
El roadmap S1.5 decía "ninguna" dependencia nueva, pero `expo-camera` y `expo-sharing` no estaban instalados. Se instalaron con `npx expo install expo-camera expo-sharing`.

---

## Notas S1.6 — Sponsors + Stand Teams (completo, incluyendo hotfixes)

### react-native-css-interop bug — Navigation context crash
`react-native-css-interop` v0.2.3 tiene un bug en `printUpgradeWarning`: su función `stringify` itera sobre todos los React contexts del fiber con `Object.entries()`, lo que dispara el getter de `NavigationStateContext` de React Navigation, el cual lanza `"Couldn't find a navigation context"`.

**Fix aplicado:** Patch directo en `node_modules/react-native-css-interop/dist/runtime/native/render-component.js` — se envuelve el `Object.entries(value)` y el loop en `try-catch`. El patch se re-aplica en cada `npm install` via `scripts/patch-css-interop.js` + `postinstall` en `package.json`.

**No usar `style={{...}}` inline en componentes con `className`** — aunque el patch mitiga el crash, es buena práctica usar `StyleSheet.create` para estilos en componentes con NativeWind.

### Módulos de DB — slugs reales
Los slugs en la tabla `modules` para el evento demo son:
- `banners` → Patrocinadores (no `patrocinadores`)
- `escaner` → Escanear QR
- `mi-stand` → Mi Stand
- `leads` → Mis Leads

`ModuleMenu.tsx` debe mapear `banners` y `patrocinadores` ambos a `/sponsors`.

### Colaboradores con has_vendor_access
Usuarios con `has_vendor_access=true` y rol no-vendedor ven módulos extra (leads, escaner) via `ModuleController`. Acceden a leads/scanner via pantallas standalone (`leads.tsx`, `scanner-stand.tsx`), NO via tabs del vendedor. Botón **👥 Equipo** en header de leads navega a `mi-stand`.

### ModuleController — slug vs key
El campo se llama `slug`, no `key`. Usar `whereIn('slug', [...])` y `pluck('slug')` en todos los filtros de módulos.

### api.ts — timeout con AbortController
`api.ts` cancela requests colgados después de 15s via `AbortController`. Sin esto, `isLoading` en TanStack Query se queda `true` indefinidamente si la red del dispositivo no responde.

---

## Notas S1.7 — Networking

### expo-contacts — race condition Android primer permiso
`expo-contacts addContactAsync` falla en Android justo después de otorgar el permiso por primera vez: el OS retorna `'granted'` pero aún no propagó el permiso al proceso. Fix: retry una vez con 400ms de delay:
```ts
for (let attempt = 0; attempt < 2; attempt++) {
  try { await Contacts.addContactAsync(contact); return 'saved'; }
  catch { if (attempt === 0) await new Promise(r => setTimeout(r, 400)); }
}
return 'error';
```

### useSendContactRequest — optimistic en onMutate (no onSuccess)
Si se actualiza el cache en `onSuccess`, hay un flash visible: `isPending=false` antes de que llegue el refetch del perfil → botón muestra "Conectar" brevemente antes de "Solicitud enviada". Fix: usar `onMutate` para actualizar perfil y directorio al instante.

### Módulos y roles de networking
El módulo `networking` en DB tiene `roles: ["presencial","virtual"]`. Si se resetea/reimporta el seeder, verificar que ambos roles estén presentes. Sanctum tokens se borran al reiniciar MySQL (Laragon restart) — el usuario necesita re-login.

---

---

## Notas S1.9b — Chat app screen

### FlashList v2 no tiene `inverted`
`@shopify/flash-list` v2 eliminó el prop `inverted`. Para chat invertido usar `FlatList` de react-native (que sí lo soporta). FlatList es suficiente para listas de chat (50-200 mensajes).

### socket.io-client — nueva dep en S1.9b
Instalado `socket.io-client@4` (compatible con servidor socket.io@4.8.3). Instancia por hook, desconecta en cleanup del useEffect.

### EXPO_PUBLIC_SOCKET_URL
Agregado al `.env`: `EXPO_PUBLIC_SOCKET_URL=http://192.168.50.142:3001`. Mismo IP que la API, puerto 3001.

### IDs API vs Socket son formatos distintos
- API (`/sessions/{id}/chat/messages`): IDs numéricos convertidos a string → "1", "2"
- Socket (`chat:message` / `chat:history`): tempIds formato "sessionId-attendeeId-timestamp" → "3-5-1711789200000"
- Dedup: `socketMsgIds Set` para socket; API history y socket history no colisionan por formato distinto de IDs.
- `chat:history` del socket solo se agrega si `sentAt > lastApiSentAt` (mensajes muy recientes no persistidos aún).

---

## Notas S1.11 — Push Notifications (Android dev build)

### expo-notifications en Expo Go — crash
`setNotificationHandler` NO puede estar al nivel de módulo (fuera de componente). Causa crash inmediato en Expo Go porque el módulo se evalúa al importar. Fix: moverlo dentro de `useEffect`.
Expo Go no soporta push remotas desde SDK 53. Guardar con `const isExpoGo = Constants.appOwnership === 'expo'` y hacer early return en todas las funciones push.

### FCM V1 — NO usar kreait/laravel-firebase
El plan original decía usar `kreait/laravel-firebase`. En la práctica se usa la **Expo Push API** (`https://exp.host/--/api/v2/push/send`). Expo maneja FCM internamente. Solo hay que subir las credenciales FCM V1 a Expo una vez.

### Subir credenciales FCM V1 a Expo — GraphQL API
`eas credentials` es interactivo, no funciona en shell no-TTY. Subir vía GraphQL API de Expo con Node.js:
```js
const { data } = await axios.post('https://api.expo.dev/graphql', {
  query: `mutation UploadAndroidGCMServiceAccountKey(...) { ... }`,
  variables: { accountName, projectName, serviceAccountKeyJsonString }
}, { headers: { Authorization: `Bearer ${EXPO_TOKEN}` } });
```
Credencial: archivo JSON de service account de Firebase (`eventos-e7f94-firebase-adminsdk-*.json`).

### google-services plugin — se pierde en prebuild --clean
`npx expo prebuild --clean` resetea `android/build.gradle` y `android/app/build.gradle`. Hay que re-agregar manualmente después:
- `android/build.gradle` → `classpath('com.google.gms:google-services:4.4.2')` en buildscript.dependencies
- `android/app/build.gradle` → `apply plugin: "com.google.gms.google-services"` al inicio

### google-services.json — se pierde en prebuild --clean
`android/app/google-services.json` se elimina en clean prebuild. Guardar copia en Desktop y copiar de vuelta.
SHA-1 del debug keystore registrado en Firebase: `E1:81:2A:84:C1:21:0D:DD:DD:22:17:99:14:10:42:CD:DD:27:1A:8F`

### Gradle version — mínimo 8.13
AGP (Android Gradle Plugin) del proyecto requiere Gradle ≥ 8.13. Expo prebuild pone 9.0.0 que es incompatible (error `IBM_SEMERU` field removed). Fijar en `android/gradle/wrapper/gradle-wrapper.properties`:
```
distributionUrl=https\://services.gradle.org/distributions/gradle-8.13-bin.zip
```
Este archivo también se resetea en prebuild --clean.

### local.properties — se pierde en prebuild --clean
`android/local.properties` se borra en clean prebuild. Recrear con:
```
sdk.dir=C\:\\Users\\Kasproduction\\AppData\\Local\\Android\\Sdk
```

### SSL curl.cainfo — CLI PHP ≠ Laragon PHP
En Windows con Laragon + WinGet PHP, el PHP de la CLI (`php artisan`) es el de WinGet (8.4), NO el de Laragon (8.3). Hay que arreglar `curl.cainfo` en AMBOS:
- Laragon: `C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.ini`
- WinGet CLI: `C:\Users\Kasproduction\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe\php.ini`
Valor: `curl.cainfo="C:\laragon\etc\ssl\cacert.pem"`

### ADB WiFi debugging — Android 11+
Si USB no funciona, usar Wireless debugging (Android 11+):
1. Settings → Developer options → Wireless debugging → Pair device with pairing code
2. `adb pair <IP>:<PORT> <CODE>` → luego `adb connect <IP>:<PORT2>`
3. `adb devices` debe mostrar el dispositivo.

### iOS — push más simple que Android
iOS no requiere Firebase/FCM. Expo maneja APNs (Apple Push Notification service) directamente via `eas credentials`. Solo necesita:
1. `npx eas credentials` para configurar APNs key (una vez, en Apple Developer Portal)
2. `npx expo run:ios` para dev build
Todo lo demás es idéntico al flujo Android.

### TanStack Query — retry: false en queries secundarias
Por defecto TanStack Query reintenta 3 veces con backoff exponencial. En queries donde el fallo silencioso es aceptable (ej: historial de edits), usar `retry: false`.

### useState inicializa solo una vez — sincronizar con useEffect
Si un componente inicializa `useState` desde datos que vienen de otro `useQuery` (ej: `const lead = leads.find(...)`), y ese query aún está cargando en el primer render, el estado queda en el valor por defecto (`null`/`''`) aunque los datos lleguen después.

**Fix:** `useEffect` con la dependencia del ID del objeto:
```tsx
useEffect(() => {
  if (lead && !dirty) {
    setTier(lead.tier ?? null);
    setNote(lead.note ?? '');
  }
}, [lead?.id]);
```
`!dirty` evita pisar cambios que el usuario ya está haciendo.
