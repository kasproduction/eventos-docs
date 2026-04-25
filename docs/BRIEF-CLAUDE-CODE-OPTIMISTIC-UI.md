# Brief para Claude Code — Audit de Latencia Percibida + Optimistic UI

> Documento de contexto para que Claude Code analice el codebase completo de EventOS (mobile + Laravel + Node socket server) y produzca un plan de implementacion de optimistic UI ajustado a la arquitectura real.
> Generado: 2026-04-25
> Origen: conversacion sobre HA/stress test que revelo gap entre uptime tecnico y experiencia percibida del usuario.

---

## 1. Contexto del problema

### Lo que se descubrio

Durante el diseno de la arquitectura HA y el plan de stress test (v2.1 con stack DigitalOcean sao1 consolidado), se identifico que el problema central NO es uptime tecnico (99.9% es alcanzable con la arquitectura propuesta) sino **latencia percibida** por el usuario final.

El usuario que toca un boton y espera 600-800ms para ver respuesta NO percibe "enterprise". Percibe "lento". Esto aplica aunque el backend sea perfecto, los SLAs sean cumplidos, y los servidores esten arriba.

### El gap conceptual

Los SLAs y thresholds que veniamos manejando estaban orientados a metricas de backend (p95, p99, error rate, throughput). Esas metricas son necesarias pero **no suficientes** para que la app se sienta enterprise. La metrica que importa al usuario es:

**Click-to-visual-feedback**: tiempo desde que el dedo toca la pantalla hasta que el usuario ve cambio visual.

### El threshold real para sentirse enterprise

Regla clasica de UX (Jakob Nielsen, validada hace 30 anos):

- 0-100ms: instantaneo
- 100-300ms: responsivo (objetivo)
- 300-1000ms: aceptable pero "la maquina pensando"
- 1000ms+: frustracion

Apps benchmark de la industria (Instagram, WhatsApp, Slack, Spotify, Notion) logran <100ms percibidos en la mayoria de acciones a pesar de tener servidores con latencia HTTP de 200-600ms. El truco no es infraestructura, es **optimistic UI**.

---

## 2. Lo que NO se tuvo en cuenta en la arquitectura actual

### 2.1 Optimistic UI sistemica

La arquitectura HA y los planes de stress test asumen el patron clasico de "esperar al servidor". Cada accion del usuario:

1. Tap en boton
2. Spinner / loading state
3. HTTP request (200-600ms)
4. Server response
5. Update UI

Este patron es lo que hace que productos como Webex Events se sientan lentos aunque su infraestructura sea solida. EventOS NO debe repetir este error.

**Lo que falta:** un patron consistente de optimistic UI aplicado a todas las acciones del usuario donde sea seguro hacerlo.

### 2.2 Diferencia entre latencia de servidor y latencia percibida

Los thresholds que estamos usando para el stress test (p95 < 800ms, p99 < 2s) miden latencia HTTP completa. Pero con optimistic UI bien implementado:

- p95 backend de 600ms → percibido como 16-50ms
- p99 backend de 2s → percibido como 16-50ms si la accion eventualmente exitosa
- Solo se nota si hay rollback, y los rollbacks deben ser <0.1% de las acciones

**Lo que falta:** metricas de cliente (TTI, click-to-feedback, rollback rate) que midan lo que el usuario realmente experimenta.

### 2.3 Coordinacion REST + Socket en optimistic updates

EventOS tiene split arquitectonico:
- **Laravel REST** para acciones de datos (favorito, like, upvote, networking, rating, login, gamification)
- **Node.js Socket.IO** para acciones realtime (chat, polls, Q&A, audience tracking, ban realtime, MC commands)

El optimistic UI tiene que jugar bien con ambos canales y considerar que algunas acciones REST disparan invalidaciones por socket (data:invalidate). Esto crea casos donde el optimistic update local podria conflictuar con un refetch disparado por socket invalidation.

**Lo que falta:** estrategia clara de optimistic UI por canal con manejo de invalidaciones cruzadas para evitar parpadeos o estados inconsistentes.

### 2.4 Manejo de duplicados en broadcast socket

En el patron actual, cuando un usuario emite `chat:send`:
1. Server recibe, persiste, broadcastea a `Rooms.session()`
2. La sala incluye al propio sender
3. El sender recibe su propio mensaje de vuelta

Sin un mecanismo de deduplicacion (tempId), si se implementa optimistic UI ingenuamente:
- Mensaje aparece local en 16ms (optimistic)
- Server confirma en 300ms
- Broadcast llega de vuelta en 350ms
- Mensaje se renderiza por segunda vez

**Lo que falta:** patron tempId-based para chat y Q&A donde el cliente genera id temporal, server lo respeta en ack y broadcast, cliente reconcilia.

### 2.5 Feedback haptico

iOS y Android soportan vibraciones suaves de 10-20ms que refuerzan neurologicamente la sensacion de "accion completada". Apps premium (Instagram, Twitter, WhatsApp) las usan en cada accion. EventOS no las tiene como estandar.

**Lo que falta:** patron consistente de Haptics.impactAsync() en todas las acciones del usuario.

### 2.6 Estados de mensaje progresivos (chat WhatsApp-style)

WhatsApp logra que el chat se sienta instantaneo aun en redes lentas mostrando 3 estados visuales:
- Enviando (gris claro, sin checkmark)
- Enviado al servidor (✓ gris)
- Entregado a otros (✓✓ azul)

EventOS chat actualmente es binario (existe / no existe). El usuario no tiene feedback progresivo de que el sistema esta trabajando.

**Lo que falta:** diseno de estados de mensaje + UI de cada estado + logica de transicion.

### 2.7 Retry silencioso para errores de red transitorios

Cuando hay falla de red breve (failover de Cloudflare LB, blip de WiFi del venue, microcorte 4G), las apps actuales muestran error inmediato al usuario. Las apps premium reintentan automaticamente 1-2 veces antes de mostrar error.

**Lo que falta:** capa de retry automatico con jitter para errores 5xx y timeouts, integrada con optimistic UI.

### 2.8 Niveles de optimismo segun riesgo de la accion

No todas las acciones deben ser optimistic. Hay 3 niveles:

**Nivel 1 - Full optimistic** (acciones reversibles, bajo riesgo):
- Like, favoritar, upvote, rating
- Sin loading, rollback silencioso si falla

**Nivel 2 - Optimistic con estado** (acciones medias):
- Chat, comentarios, networking request
- Optimistic con indicador sutil de progreso

**Nivel 3 - NO optimistic** (acciones criticas):
- Login, check-in con QR, canje de rewards, pagos
- Loading explicito, espera al servidor

**Lo que falta:** clasificacion explicita de cada accion del producto en uno de los 3 niveles.

---

## 3. Tarea para Claude Code

### 3.1 Audit del codebase

Analizar el codebase completo (mobile React Native + Laravel REST + Node.js Socket.IO server) y producir un inventario de TODAS las acciones del usuario que modifican estado, clasificadas por:

| Campo | Valores |
|-------|---------|
| Accion | Nombre humano (ej. "Favoritar sesion") |
| Pantalla | Donde vive en la app (ej. AgendaScreen, SessionDetail) |
| Canal | REST / Socket / Hibrido |
| Endpoint o Event | POST /favorites o socket.emit('chat:send') |
| Frecuencia esperada por usuario por evento | Alta (50+) / Media (5-50) / Baja (1-5) |
| Reversibilidad | Reversible / Irreversible |
| Riesgo si falla | Bajo / Medio / Alto |
| Tiene optimistic UI hoy | Si / No / Parcial |
| Nivel recomendado | 1 / 2 / 3 |
| Patron recomendado | REST useMutation / Socket TempId / Socket Skip-self / Socket Server-authoritative / No aplica |

### 3.2 Identificar gaps especificos

Para cada accion del inventario donde "Tiene optimistic UI hoy" sea "No" o "Parcial", documentar:

- **Comportamiento actual**: que pasa cuando el usuario toca? (spinner? bloqueo? nada visible?)
- **Comportamiento deseado**: como deberia comportarse con optimistic UI segun el patron recomendado?
- **Archivos a tocar**: lista de archivos del codebase que necesitan modificacion
- **Riesgos de la migracion**: que podria romperse al cambiar el patron?

### 3.3 Identificar problemas de duplicacion en socket

Auditar todos los `socket.on(...)` listeners en el mobile y verificar:

- Cuales pueden causar duplicados si se implementa optimistic UI ingenuo?
- Cuales ya tienen logica de dedup (por id, tempId, senderId)?
- Cuales necesitan ajuste en el server (Node.js) para soportar tempId/ack?

### 3.4 Identificar coordinacion REST + Socket invalidation

Listar las acciones REST que disparan invalidacion via socket (data:invalidate). Para cada una:

- El optimistic update local podria conflictuar con el refetch?
- Como evitar parpadeo cuando llega la invalidacion despues del optimistic update exitoso?
- Hay que ajustar el debounce o jitter de la invalidacion para estos casos?

### 3.5 Auditar el socket server (Node.js)

Verificar en el codebase del socket server:

- Cuantos `socket.on(...)` handlers tienen ack callback hoy?
- Cuales de los emits del server usan `socket.to(room)` (skip-self) vs `io.to(room)` (todos)?
- Hay soporte de tempId en los payloads de chat / Q&A?
- Hay rate limiting per-socket que pueda afectar el patron de retry silencioso?

### 3.6 Producir plan de implementacion priorizado

Output esperado: documento `OPTIMISTIC-UI-PLAN.md` con:

**Seccion A - Inventario completo**: tabla con todas las acciones clasificadas (3.1)

**Seccion B - Quick wins (Semana 1)**: 5-7 acciones REST de alto impacto, bajo riesgo, donde el patron es trivial. Incluir codigo listo para pegar.

**Seccion C - Socket simple (Semana 2)**: acciones socket que usan Patron 2 (skip-self) o Patron 3 (server-authoritative). Incluir cambios necesarios en el server.

**Seccion D - Socket complejo (Semana 3)**: chat y Q&A con Patron 1 (tempId). Cambios mayores en server + cliente. Incluir migracion progresiva (retrocompatible).

**Seccion E - Cross-cutting concerns**:
- Wrapper estandarizado sobre useMutation con optimistic + retry + haptics
- Hook custom para socket emits con ack + timeout + tempId
- Sistema de toast unificado para errores de rollback (no espantoso)
- Helper de Haptics.impactAsync() para invocar consistente

**Seccion F - Metricas a instrumentar**: que medir en Sentry Performance / Firebase para validar que el optimistic UI esta funcionando (rollback rate, click-to-feedback timing, retry success rate).

**Seccion G - Riesgos y mitigaciones**: que puede salir mal en cada migracion y como detectarlo (regression tests, beta testing, feature flags).

---

## 4. Patrones de referencia

### 4.1 Patron REST con React Query (acciones tipo favoritar/like)

```javascript
const favoriteMutation = useMutation({
  mutationFn: (sessionId) => api.favorite(sessionId),
  
  onMutate: async (sessionId) => {
    await queryClient.cancelQueries(['favorites'])
    const previous = queryClient.getQueryData(['favorites'])
    queryClient.setQueryData(['favorites'], old => [...old, sessionId])
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light)
    return { previous }
  },
  
  onError: (err, sessionId, context) => {
    queryClient.setQueryData(['favorites'], context.previous)
    toast.show('No se pudo guardar el favorito', { variant: 'subtle' })
  },
  
  onSettled: () => {
    queryClient.invalidateQueries(['favorites'])
  },
})
```

### 4.2 Patron Socket con TempId (chat / Q&A)

```javascript
// Cliente
const sendMessage = (text) => {
  const tempId = `temp_${Date.now()}_${nanoid(6)}`
  
  setMessages(prev => [...prev, {
    id: tempId, tempId, text, status: 'sending',
    senderId: currentUser.id, createdAt: new Date(),
  }])
  
  socket.timeout(5000).emit('chat:send', { text, tempId, sessionId }, (err, response) => {
    if (err) {
      setMessages(prev => prev.map(m =>
        m.tempId === tempId ? { ...m, status: 'failed' } : m
      ))
      return
    }
    if (response.error) {
      setMessages(prev => prev.filter(m => m.tempId !== tempId))
      toast.error(response.error)
      return
    }
    setMessages(prev => prev.map(m =>
      m.tempId === tempId ? { ...response.message, status: 'sent', tempId } : m
    ))
  })
}

socket.on('chat:message', (message) => {
  setMessages(prev => {
    if (prev.some(m => m.tempId === message.tempId)) return prev
    return [...prev, { ...message, status: 'sent' }]
  })
})

// Server (Node.js)
socket.on('chat:send', async ({ text, tempId, sessionId }, ack) => {
  try {
    const validation = await validateMessage(socket.userId, text, sessionId)
    if (!validation.ok) return ack({ error: validation.reason })
    
    const message = await persistMessage({ userId: socket.userId, text, sessionId, tempId })
    
    ack(null, { message })
    
    io.to(Rooms.session(sessionId)).emit('chat:message', { ...message, tempId })
  } catch (err) {
    ack({ error: 'Server error' })
  }
})
```

### 4.3 Patron Socket Skip-self (poll vote)

```javascript
// Server: socket.to() en lugar de io.to() para excluir al sender
socket.on('poll:vote', async ({ pollId, optionId }, ack) => {
  const result = await persistVote(socket.userId, pollId, optionId)
  if (!result.ok) return ack({ error: result.reason })
  
  ack(null, { success: true })
  
  socket.to(Rooms.session(pollId)).emit('poll:counts-update', {
    pollId, counts: result.counts,
  })
})
```

### 4.4 Patron Socket Server-authoritative (Q&A upvote)

```javascript
// Cliente: optimistic local, listener pisa el contador con valor del server
const upvoteQuestion = (questionId) => {
  setQuestions(prev => prev.map(q => 
    q.id === questionId 
      ? { ...q, upvotes: q.upvotes + 1, myUpvote: true }
      : q
  ))
  
  socket.timeout(3000).emit('qa:upvote', { questionId }, (err, response) => {
    if (err || response.error) {
      setQuestions(prev => prev.map(q => 
        q.id === questionId 
          ? { ...q, upvotes: q.upvotes - 1, myUpvote: false }
          : q
      ))
    }
  })
}

socket.on('qa:upvote-update', ({ questionId, upvotes }) => {
  setQuestions(prev => prev.map(q =>
    q.id === questionId ? { ...q, upvotes } : q
  ))
})
```

---

## 5. Decisiones tomadas en la conversacion previa

Para no re-discutir cosas ya cerradas:

- **Cloud provider**: DigitalOcean en sao1 (Sao Paulo) consolidado, NO Hetzner ni AWS. Ver `PLAN-STRESS-TEST.md` v2.1 para racional.
- **Storage**: Cloudflare R2 se mantiene (egress gratis).
- **Cloudflare adelante**: WAF + DNS + CDN + DDoS, NO se reemplaza por DO LB.
- **Stack portable**: MySQL standard, Redis standard, Laravel, Node.js. No Aurora, no DynamoDB, no servicios propietarios.
- **Stress test**: ya tiene plan v2.1, se ejecuta DESPUES del optimistic UI para medir el sistema completo, no antes.
- **Orden de implementacion**: Optimistic UI primero (1-2 semanas), luego deploy DO sao1 staging, luego stress test.

---

## 6. Output esperado de Claude Code

1. **`OPTIMISTIC-UI-AUDIT.md`** — inventario completo de acciones (seccion 3.1) con clasificacion
2. **`OPTIMISTIC-UI-PLAN.md`** — plan priorizado de implementacion (seccion 3.6)
3. **PRs sugeridas** — para Semana 1 (quick wins REST), con codigo concreto listo para review

NO implementar codigo aun. Solo audit + plan + PRs sugeridas. La implementacion la haremos en sesiones siguientes con feature flags y rollout progresivo.

---

## 7. Checklist de cosas a NO olvidar

- [ ] Considerar coexistencia con `data:invalidate` socket events (no causar refetch que pisen optimistic update)
- [ ] Considerar el sistema de jitter 0-2s actual para invalidaciones (puede afectar timing de rollbacks)
- [ ] Considerar el cache MMKV (offline-first) — los optimistic updates deben sobrevivir a app restart si el request quedo en queue?
- [ ] Considerar Mission Control (admin) — sus acciones tambien necesitan optimistic UI? Si banean a un usuario, ven confirmacion inmediata?
- [ ] Considerar el sistema de notificaciones campana (queue Laravel + push Expo) — el optimistic UI de exports no es trivial
- [ ] Considerar que el bingo ya tiene un patron server-authoritative implementado — usarlo como referencia, no reinventar
- [ ] Considerar que las acciones criticas (check-in con QR, canje de puntos, login) NO deben ser optimistic
- [ ] Considerar Sentry Performance instrumentation desde el dia 1 para medir impacto

---

_Brief generado en conversacion 2026-04-25 sobre arquitectura HA, latencia percibida, y plan de optimistic UI. Para audit completo del codebase EventOS por Claude Code._
