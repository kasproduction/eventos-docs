# QA — Auth & Onboarding

> Checklist de pruebas manuales. Marcar [x] al verificar.
> Ultima actualizacion: 2026-04-12

## Preparacion

- Filament: http://eventos-backend.test/admin (`superadmin@eventos.test` / `password`)
- Evento: Summit Empresarial 2026 (slug: `summit-empresarial-2026`)
- Approval: verificar si `registration_requires_approval` esta ON/OFF segun el test
- Usuario de prueba: `prueba@test.com` / `12345678`

### Limpiar usuario de prueba (tinker)

```php
$u = App\Models\User::where('email','prueba@test.com')->first();
if ($u) {
    App\Models\Attendee::where('user_id', $u->id)->delete();
    $u->tokens()->delete();
    $u->delete();
}
```

---

## 1. Registro SIN approval

**Config:** Filament > Evento > `registration_requires_approval` = OFF

| # | Escenario | Pasos | Esperado | OK |
|---|-----------|-------|----------|----|
| 1.1 | Registro nuevo | Onboarding > Crear cuenta > nombre, email, password | Va a step photo | [ ] |
| 1.2 | Flujo completo | Photo > Sobre ti (3 campos) > Un poco mas (2 campos) > Intereses > Done | Cada step muestra los campos correctos, llega a Done con badge QR | [ ] |
| 1.3 | Puntos se acumulan | Llenar campos en cada step | AnimatedPts suma por cada campo llenado | [ ] |
| 1.4 | Done navega a home | Presionar CTA en Done | Va a home presencial o virtual segun rol | [ ] |
| 1.5 | Login despues de registro | Logout > Onboarding > Ya tengo cuenta > email + password | Va a Done directo (skip photo/forms) | [ ] |

## 2. Registro CON approval

**Config:** Filament > Evento > `registration_requires_approval` = ON

| # | Escenario | Pasos | Esperado | OK |
|---|-----------|-------|----------|----|
| 2.1 | Registro requiere approval | Crear cuenta con approval ON | Va a pantalla "Registro en revision" | [ ] |
| 2.2 | Verificar estado (no aprobado) | Presionar "Verificar estado" sin aprobar | Toast "Tu registro sigue en revision" | [ ] |
| 2.3 | Aprobar desde Filament | Admin aprueba al asistente | Cambio en DB (registration_approved_at != null) | [ ] |
| 2.4 | Verificar estado (aprobado) | Presionar "Verificar estado" despues de aprobar | Va al onboarding (photo > forms > done), NO directo al home | [ ] |
| 2.5 | Completar onboarding post-approval | Pasar por photo > forms > intereses > done | Llega a Done, presionar CTA va a home | [ ] |
| 2.6 | Cerrar sesion desde pending | Presionar "Cerrar sesion" | Va al onboarding welcome | [ ] |

## 3. Select, Checkbox, Textarea (FormStep)

| # | Escenario | Pasos | Esperado | OK |
|---|-----------|-------|----------|----|
| 3.1 | Select abre BottomSheet | Tap en campo Industria | BottomSheet con 7 opciones, radio buttons | [ ] |
| 3.2 | Select confirmar | Seleccionar opcion > Confirmar | Sheet se cierra, campo muestra valor seleccionado | [ ] |
| 3.3 | Select cambiar valor | Tap de nuevo > seleccionar otra opcion | Valor cambia correctamente | [ ] |
| 3.4 | Textarea multiline | Escribir en Expectativas | Texto arriba (no centrado), multiples lineas funcionan | [ ] |
| 3.5 | Checkbox toggle | Tap en switch Certificado | Toggle cambia, haptic feedback | [ ] |
| 3.6 | Validacion required vacio | Dejar Cargo e Industria vacios > Continuar | Borde rojo en ambos, toast error, NO avanza | [ ] |
| 3.7 | Validacion required lleno | Llenar Cargo e Industria > Continuar | Avanza al siguiente step | [ ] |
| 3.8 | Error se limpia | Campo con error > escribir algo | Borde rojo desaparece | [ ] |
| 3.9 | PreviewCard live | Escribir Cargo y Empresa | Card arriba se actualiza en vivo (nombre + cargo . empresa) | [ ] |
| 3.10 | Puntos por campo | Llenar cada campo y salir del campo | Puntos se suman (15 por campo en "Sobre ti", 10 en "Un poco mas") | [ ] |
| 3.11 | Skip con puntos pendientes | Presionar "Saltar por ahora" con campos vacios | SkipModal aparece con puntos que pierde | [ ] |
| 3.12 | Step 2 sin preview | Llegar a "Un poco mas" | NO muestra PreviewCard (solo tiene textarea y checkbox) | [ ] |

## 4. Login inteligente

| # | Escenario | Pasos | Esperado | OK |
|---|-----------|-------|----------|----|
| 4.1 | Email no registrado | Ingresar email nuevo > Continuar | Toast "No encontramos una cuenta con ese email" o va a registro | [ ] |
| 4.2 | Email registrado | Ingresar email existente > Continuar | Aparece campo password (animado) | [ ] |
| 4.3 | Password incorrecto | Email correcto + password malo | Toast error, no avanza | [ ] |
| 4.4 | Login exitoso | Email + password correctos | Va a Done | [ ] |
| 4.5 | Cuenta pendiente activacion | Email de cuenta no activada | Redirige a activate-account | [ ] |

## 5. Activate account

| # | Escenario | Pasos | Esperado | OK |
|---|-----------|-------|----------|----|
| 5.1 | Activar cuenta | Ingresar codigo de activacion | Activa y va al onboarding (photo step, no welcome/auth) | [ ] |
| 5.2 | Codigo invalido | Ingresar codigo incorrecto | Toast error | [ ] |

## 6. Perfil — Ver introduccion

| # | Escenario | Pasos | Esperado | OK |
|---|-----------|-------|----------|----|
| 6.1 | Ver introduccion de nuevo | Perfil > "Ver introduccion de nuevo" | Va al onboarding en photo step (NO login/welcome) | [ ] |
| 6.2 | Completar replay | Pasar por photo > forms > survey > done | Vuelve a home normalmente | [ ] |

## 7. Banned

| # | Escenario | Pasos | Esperado | OK |
|---|-----------|-------|----------|----|
| 7.1 | Login baneado | Login con usuario baneado | Va a pantalla banned | [ ] |
| 7.2 | Ban durante uso | Admin banea mientras usuario esta en home | Interceptor 403, va a banned | [ ] |
| 7.3 | Ban en _layout guard | Abrir app con usuario baneado en storage | Redirect a /banned desde _layout | [ ] |

## 8. Edge cases

| # | Escenario | Pasos | Esperado | OK |
|---|-----------|-------|----------|----|
| 8.1 | App kill durante onboarding | Registrar > llegar a photo > matar app > reabrir | Deberia ir a onboarding (tiene token + onboarding_seen) | [ ] |
| 8.2 | Token expirado en pending | Esperar que token expire en pending-approval > Verificar estado | Redirect a onboarding (login), no error infinito | [ ] |
| 8.3 | Doble registro mismo email | Intentar registrar email ya existente | Toast "Este email ya esta registrado" | [ ] |
| 8.4 | Sin conexion en auth | Registrar/login sin internet | ConnectionError screen o toast error con retry | [ ] |
| 8.5 | Sin conexion en onboarding load | Abrir app sin internet (config no carga) | ConnectionError con boton reintentar | [ ] |
| 8.6 | Approval + ban simultaneo | Admin banea usuario pendiente de approval | Al verificar estado, deberia ir a banned | [ ] |

## 9. Datos guardados correctamente

| # | Escenario | Verificar | OK |
|---|-----------|----------|----|
| 9.1 | Profile fields | Cargo y Empresa se guardan en `/me/profile` | [ ] |
| 9.2 | Custom fields | Industria, Expectativas, Certificado se guardan en `/me/registration-fields` | [ ] |
| 9.3 | Survey/intereses | Intereses seleccionados se guardan | [ ] |
| 9.4 | Foto | Foto subida persiste en perfil | [ ] |

---

## Matriz de flags

| Flag | Quien lo setea | Quien lo borra | Efecto |
|------|---------------|----------------|--------|
| `onboarding_seen` | AuthStep (al submit), DoneStep, activate-account | clearAuth (logout) | Si true: onboarding arranca en auth, no welcome |
| `post_activation_onboarding` | pending-approval (al aprobar), activate-account, ProfileScreen ("Ver intro") | OnboardingProvider (al montar) | Si true: onboarding salta welcome+auth, va directo a photo |

## Bugs encontrados y corregidos (2026-04-12)

| Bug | Fix | Archivo |
|-----|-----|---------|
| registrationApprovedAt undefined vs null | `== null` en vez de `=== null` | index.tsx, _layout.tsx, AuthStep.tsx |
| DoneStep sin guard ban/approval | Check antes de navegar a home | DoneStep.tsx |
| _layout sin guard ban | Redirect a /banned | _layout.tsx |
| pending-approval no maneja 401 | Catch 401 → clearAuth → onboarding | pending-approval.tsx |
| pending-approval no guarda registrationApprovedAt | Agrega campo al updated object | pending-approval.tsx |
| pending-approval iba directo a home | Manda a onboarding (photo→forms→done) | pending-approval.tsx |
| "Ver introduccion" iba a login | Usa post_activation flag | ProfileScreen.tsx |
| InterestsStep Continuar no avanzaba | setTimeout directo, no depender de callback | InterestsStep.tsx |
| FormStep skip con required | Ocultar "Saltar" si hay campos required | FormStep.tsx |
| index.tsx useEffect solo dependia de token | Agrega user a deps | index.tsx |
| activate-account no seteaba registrationApprovedAt | Agrega campo al user object | activate-account.tsx |
| authApi 'no_attendee' rompia checks == null | Cambia a 'auto' (string truthy) | authApi.ts |
| SelectSheet no sincronizaba selected prop | useEffect sync al abrir | SelectSheet.tsx |

## Notas

- Limpiar usuario de prueba entre tests de registro
- Verificar en Filament que approval este ON/OFF segun el test
- Los puntos por campo son configurables por form en el config (points_per_field)
- maxItems por form: 4 campos (Filament lo limita)
