# Revisión de lo copiado de PHP que parece un defecto (2026-10-30)

> Pedida por Kamilo al fijar la regla "idéntico o arreglado" (`BUENAS-PRACTICAS-MIGRACION.md`). La armó un revisor de solo lectura sobre
> todo lo portado a C# hasta M2.3.6a; yo verifiqué contra el código las filas marcadas "verificada". Lo que dice "sin confirmar" no lo
> confirmó nadie. Decididas por Kamilo el 2026-10-30; el estado de cada fila está en la sección siguiente.

## Estado tras la decisión de Kamilo (2026-10-30)

| Estado | Filas |
|---|---|
| Arregladas en C# con su prueba y su control (tarea M2.3.10) | 1, 2, 6, 8, 9, 10, 11, 12, 16, 17, 18, 21, 22, 24, 25 |
| A su propia tarea | 15 en M2.4.4 (desbloquear cuenta), 7 en M4c.6 (log de push de cambio de horario y agenda retrasada) |
| Aceptadas en el Anexo B | 3, 4, 5, 13, 19, 20, 23, 26 |
| Idénticas por contrato | A a E |
| Descartada | 14 |

La fila 8 ya validaba 1 a 120 en PHP y en C#; solo faltaba el desempate por id. La fila 1 no tiene acción de "revertir": ver `decisiones.md`.

## Segunda revisión: los seis archivos que faltaban (2026-10-30)

Revisor de solo lectura sobre `Onboarding`, `TracksYTipos`, `HtmlDePaginas`, `DosPasos`, `IngresoDelStaff` y `SesionesDelStaff`. Sin verificar contra PHP, salvo la longitud UTF-16 (ya cubierta por la fila 22). Esperan decisión de Kamilo.

- Ingreso del staff: el bloqueo remoto no tiene desbloqueo (ver fila 15, M2.4.4); la ventana de 5 códigos tiene una carrera y su llave de Redis no vence; `setup` y `confirm` del 2FA no miran el bloqueo; los tokens pendientes y `staff_sessions` no se limpian; con un correo inexistente la respuesta tarda distinto.
- Onboarding: un hero relativo no se puede volver a guardar (422); borrar una opción de Intereses borra en cascada las respuestas de los asistentes; carrera al editar el JSON; `forms[].id` repetido; números null en vez de los defaults; listas `null` que vacían; sin tope de opciones.
- TracksYTipos: reordenar es parcial y sin transacción; los tracks nuevos nacen con orden 0; el slug de un tipo tiene una carrera; el color es obligatorio.
- HtmlDePaginas: `!important` dentro de `span` que HTMLPurifier quita; un `>` dentro de un atributo.

## Resumen

- 26 comportamientos copiados que parecen defecto; 5 contratos que consumen la webapp, la app o el kiosko y siguen idénticos.
- Una fila se descartó al verificarla: la 14 no es defecto (PHP tampoco avisa el bloqueo por contraseña a propósito).
- No se abrieron por completo `Onboarding.cs`, `TracksYTipos`, `HtmlDePaginas`, `DosPasos`, `IngresoDelStaff` ni `SesionesDelStaff`: ahí
  puede haber más. Las rutas de C# que hoy consumen webapp, app o kiosko son solo `/auth/me` y `/events/{id}/documents/zip` (esta última
  confirmada en `eventos-web/src/app/api/documents/[eventId]/zip/route.ts`).

## Lo que se puede arreglar en C# sin tocar un contrato de webapp, app o kiosko

| # | Qué hace y por qué parece defecto | Dónde (C#) | Lo consume | Registrado | Qué hacer |
|---|---|---|---|---|---|
| 15 | "Desbloquear" una cuenta de staff solo existe en Filament; C# no tiene endpoint y el mensaje de bloqueo manda a pedírselo a un super_admin. Al apagar Filament nadie podría. **Verificada** (grep sin resultados). | `Admin/Entrada/SeguridadDelStaff.cs:15,65` | Admin nuevo | No registrado | Sumarlo a M2.4.4 (ya anotado en el roadmap) |
| 2 | Los errores de envío de push se tragan (lote no-2xx, excepción, tickets con `DeviceNotRegistered`); solo log, sin reintento y los tokens muertos nunca se limpian. | `Trabajos/Push/EnvioDePush.cs:43-96` | App Expo (recibe el push) | Comentario | Arreglar: reintentar y limpiar tokens muertos |
| 6 | Regenerar el ZIP: si ningún documento entra, el ZIP viejo sigue sirviendo archivos borrados; si el trabajo falla, el ZIP viejo se sirve hasta el próximo cambio. | `Trabajos/Documentos/RegenerarZipDeDocumentos.cs:46,99-103`; `ZipDeDocumentosEndpoints.cs:33-41` | Webapp (la forma de la respuesta no cambia) | Anexo B cubre solo el debounce | Arreglar |
| 9 | Borrar un sponsor solo avisa `sponsors`; no retira el acceso de vendedor del líder ni del equipo, ni sus tokens (cambiar de líder sí, BUG-437). Sin confirmar contra PHP ni contra la cascada de la base. | `Admin/Contenido/Sponsors.cs:165-175` | App Expo (rol vendedor) | No registrado | Confirmar y arreglar |
| 10 | Si la plantilla de un correo falta o está apagada no sale y no deja fila en `email_logs` (para los no esenciales es un descarte mudo, incluida la invitación). Si el correo salió y falla anotar `sent`, Hangfire reintenta y se duplica. **Verificada** (es lo que escribí en M2.3.6a, copiando PHP). | `Trabajos/Correo/EnviarCorreo.cs:88-96,119` | Externo (quien recibe el correo) | Comentario | Anotar `skipped` y proteger contra el duplicado |
| 11 | El render sustituye los marcadores uno tras otro: un valor que contenga `{{otra}}` (un nombre de asistente) se vuelve a reemplazar. **Verificada** (copia de `str_replace` con arreglos, a propósito). | `Trabajos/Correo/PlantillasDeCorreo.cs:40-58` | Externo (correos) | Comentario; BUG-410 cubre solo el escape | Una sola pasada |
| 7 | Los push de cambio de horario y agenda retrasada no escriben `push_notification_logs`. | `Trabajos/Push/AvisoDeCambioDeHorario.cs`, `AvisoDeAgendaRetrasada.cs` | Nadie (bitácora del admin, M4c.6) | Comentario | Arreglar en M4c.6 |
| 12 | `file_size` y `mime_type` del documento los manda el cliente sin cotejar con el archivo; la lista de tipos acepta `msword` y `ms-powerpoint` aunque la subida los rechaza. Sin confirmar. | `Admin/Contenido/Documentos.cs:27-60,89` | Admin nuevo escribe; webapp muestra | Anexo B cubre solo la subida | Arreglar |
| 13 | Borrar un documento, speaker o sponsor no borra su archivo del almacenamiento; los ZIP viejos tampoco. | `Admin/Contenido/Documentos.cs:24`; `DocumentosEndpoints.cs:71` | Nadie | Comentario | Arreglar |
| 16 | Un iframe de stream que no es https y los permisos `allow` desconocidos se descartan en silencio; el iframe queda vacío sin aviso. | `Comun/EmbedDeStream.cs:13,41`; `Agenda/Sesiones.cs:21` | Admin nuevo escribe; webapp y app muestran | Anexo B cubre solo las URLs | Dar un 422 claro |
| 17 | El aforo de un salón: al crear, 0 se guarda como null (sin límite); al editar, 0 se guarda como 0. Sin confirmar contra PHP. | `Admin/Agenda/Salones.cs:15-16,92,99` | Admin nuevo; el check-in de sala lee la capacidad (sin confirmar si algún cliente la usa) | Comentario | Unificar |
| 18 | Una sesión borrada no se puede restaurar ni abrir (el filtro de papelera existe, la acción no). | `Admin/Agenda/Sesiones.cs:25-26` | Admin nuevo | Comentario | Agregar restaurar |
| 19 | Lo escondido en el formulario no se guarda (listas de correos con la restricción apagada, About, opciones del hero): quedan guardadas pero invisibles. | `Personas/EntradaDelEvento.cs:15-17`; `Identidad/Branding.cs:17-19` | Admin nuevo | Anexo B | Decidir si el admin nuevo hereda la UI de Filament |
| 22 | Los textos se miden con `string.Length` (UTF-16): 255 caracteres con un emoji se rechazan como largos; el título del push se corta por unidades y puede partir un par sustituto. **Verificada** en `Campos.cs`. | `Admin/Campos.cs:29,132`; `Push/AvisoDeCambioDeHorario.cs:39` | Admin nuevo; app Expo (push) | No registrado | Contar caracteres (runas) |
| 23 | Las fechas del admin se truncan al minuto en silencio. | `Admin/Campos.cs:65-66` | Admin nuevo | Comentario | Bajo impacto |
| 24 | La zona horaria del evento solo admite 8 valores fijos copiados de Filament (Branding la reutiliza). | `Admin/Eventos/EventoNuevo.cs:37-41` | Admin nuevo | No registrado | Aceptar cualquier zona IANA |
| 5 | Los efectos (cache, aviso, trabajo en cola) se piden después de guardar, sin outbox: si el proceso se cae entre las dos cosas, se pierden. Aplica a todo `Api/Admin`. | `Admin/*` (`Sponsors.cs:161`, `Documentos.cs:91`) | Webapp y app (ven datos viejos hasta el vencimiento) | Solo el correo (Anexo B) | Arreglar o aceptar y anotar |

## Dudosas: las lee un cliente, pero el arreglo no cambiaría el contrato (decide Kamilo)

| # | Qué hace | Dónde (C#) | Lo consume | Registrado |
|---|---|---|---|---|
| 1 | Cancelar una sesión desde Mission Control solo pone `cancelled_at` y no cambia `status`; webapp y app tratan `status === "cancelled"` como terminada y ninguna lee `cancelled_at`: una sesión cancelada puede verse como programada o en vivo. **Verificada** en el comentario de C# ("PHP tampoco"); sin confirmar si la API de agenda lo deriva. | `EnVivo/CicloDeLaSesion.cs:16,115` | Webapp (`lib/session-visual-status.ts`, `lib/live.ts`), app Expo (`lib/sessionStatus.ts`), Mission Control | Comentario; no es BUG |
| 3 | El socket deja pasar a lo sumo un aviso por segundo y entidad: un segundo cambio dentro de ese segundo no avisa y el cliente queda con el dato viejo; un aviso que falla no se reintenta. | `Comun/AvisosDeCambios.cs:132-180` | Webapp y app | Comentario |
| 4 | Si Redis falla, el borrado de la cache de PHP y el publish de `eventos:invalidate` quedan en el log y el guardado sigue; la cache vieja dura hasta que venza. | `Comun/CacheDePhp.cs`, `AvisosDeCambios.cs:118` | Webapp y app | Comentario |
| 8 | Retraso de agenda: los minutos se truncan, van con signo, con 0 se anuncia "minutes: 0", solo se mueve la sesión siguiente del salón, y la búsqueda de la "siguiente" no desempata por id. | `EnVivo/CicloDeLaSesion.cs:19,153-223` | Mission Control y socket (sin confirmar quién pinta `minutes`) | Comentario |
| 20 | Cada guardado del evento dispara cache y aviso `branding` aunque nada haya cambiado. | `Eventos/EfectosDelEvento.cs`, `Branding.cs:22` | Webapp y app (refrescan sin necesidad) | Comentario |
| 21 | Faltan avisos al socket: FAQ ("sin aviso, como PHP"), activar la encuesta post-evento y reordenar FAQ. | `Identidad/IdentidadEndpoints.cs:55`; `Eventos/EfectosDelEvento.cs:32-39` | Webapp y app | BUG-382 (encuestas), BUG-477 y 484 (reordenar); el aviso de FAQ no |
| 25 | Un módulo de un catálogo viejo no se lista ni se edita (404): si estaba prendido no se puede apagar desde el admin. | `Admin/Identidad/Modulos.cs:12-13,42` | Admin nuevo | Anexo B |
| 26 | Los contadores del limitador son propios de cada backend (`dotnet:limite:`): mientras convivan, el cupo real se duplica. | `Comun/Limitador.cs:14` | Webapp, app y kiosko | Comentario (transitorio, aceptable) |

## Descartada al verificar

| # | Qué decía el revisor | Qué es de verdad |
|---|---|---|
| 14 | El bloqueo por 5 contraseñas incorrectas no avisa por correo y el de 5 códigos del 2FA sí. | **Intencional en PHP.** `StaffLockout::passwordFailed` no manda correo y `codeFailed` sí, porque fallar el código DESPUÉS de acertar la contraseña significa que alguien la tiene (comentario de `StaffAccountLockedMail`). C# hace lo mismo a propósito. No es defecto. |

## Copiado que SÍ consumen la webapp, la app o el kiosko: sigue idéntico por contrato

| # | Qué | Dónde (C#) | Consumidor |
|---|---|---|---|
| A | El token Sanctum vence a los 7 días fijos desde `created_at`; no es deslizante y `last_used_at` no se escribe. | `Autenticacion/TokenDeSanctum.cs:20-21,186-192` | Webapp, Expo y kiosko usan el bearer (sin confirmar que manejen el vencimiento) |
| B | Textos de error de Laravel (`Unauthenticated.`, `Too Many Attempts.`, `No query results for model [...]`). `eventos-web/src/lib/apiErrors.ts` dice que esos textos NO deben llegar al cliente y los mapea por estado; la app Expo no los contiene. Probablemente el texto exacto no es contrato real (sin confirmar en el kiosko). | `Comun/ErroresDeLaravel.cs:22-39` | Webapp (por estado) |
| C | `/auth/me` devuelve solo el registro más nuevo de un evento activo, desempatado por id (BUG-464). | `Auth/MeServicio.cs:40-61` | Webapp y Expo |
| D | Cache de PHP compartida y avisos por socket (`modules`, `pages`...). | `AvisosDeCambios.cs` | Webapp y app, por invalidación |
| E | El cuerpo de las páginas se reescribe con HtmlSanitizer, imitando HTMLPurifier (Anexo B). | `Admin/Contenido/HtmlDePaginas.cs` | Webapp (BUG-469) y Expo |
