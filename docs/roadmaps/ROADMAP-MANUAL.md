# ROADMAP — KNOWLEDGE BASE / Manual del organizador — 5/35

> Decision Kamilo 2026-07-19 (PENDIENTES.md §2.5): "Falta un gran instructivo
> de todo". Pegado al frente DEPLOY DEMO: el manual es parte del demo comercial
> y escribirlo caza huecos (ya destapo Paginas; el barrido M0 cazo 4 mas).
>
> **Ventana operativa de este frente.** Fuente unica del contenido:
> `manual/src/content/docs/` (sitio Starlight en `manual/`, `pnpm dev` para
> verlo en HTML). Inventario + reglas de escritura: `docs/manual/README.md`.
>
> **Reglas duras:** esqueleto fijo de 5 secciones (Que es / Como se configura /
> Que ve el asistente / Se puede y NO / Gotchas) · regla de procedencia (toda
> afirmacion cita codigo `<!-- fuente: archivo:linea -->` o decision Kamilo) ·
> es-CO tuteo, cero emojis · capturas del admin real solo en el cierre.

---

## CERRADAS

### M0 — Fundacion — 4/4 (2026-07-19)
- [x] Inventario blindado con barrido exhaustivo de 4 superficies (agentes:
      admin Filament 40+ superficies, ~35 rutas webapp, pantallas Expo con
      roles/deep-links, kiosko 2 modos + MC ~12 capacidades + DC ~45 datasets)
- [x] Esqueleto fijo + reglas de escritura (`docs/manual/README.md`)
- [x] Sitio Starlight compilando (`manual/`: es, buscador Pagefind, portada)
- [x] Piloto de formato: `modulos/encuestas.md` (verificado contra
      PollController/EventObserver/ModuleCatalog) — **pendiente OK de Kamilo
      al formato antes de producir en masa**

---

## M1 — Primeros pasos — 0/4

> Lo que un prospecto lee primero. Orden de lectura = orden de escritura.

- [ ] M1.1 `primeros-pasos/conceptos.md` — Evento, asistente, superficies
      (app / webapp / kiosko / admin), modulos y ubicaciones canon, roles
- [ ] M1.2 `primeros-pasos/crear-evento.md` — Wizard 4 pasos (carril rapido)
      + donde se afina cada pregunta despues (cada una tiene su casa)
- [ ] M1.3 `primeros-pasos/escritorio.md` — Stats, "Requieren accion" con
      accesos por fuente, tareas de intencion
- [ ] M1.4 `primeros-pasos/panel-modulos.md` — Encender/apagar, audiencias
      (Todos / Ya llegaron / A distancia + tags), orden, donde vive cada
      modulo, efecto instantaneo en las superficies

## M2 — Modulos: contenido y comunidad — 0/6

- [ ] M2.1 `modulos/agenda.md` — Sesiones, tipos, tracks, salon asignado,
      favoritos/Mi Agenda, 3 tiempos, puerta a Mission Control
- [ ] M2.2 `modulos/speakers.md`
- [ ] M2.3 `modulos/social.md` — Muro, fotos, moderacion, badges rojos
- [ ] M2.4 `modulos/sponsors.md` — Marca, stand, equipo, trivia de stand
- [ ] M2.5 `modulos/documentos.md`
- [ ] M2.6 `modulos/networking.md` — Directorio, solicitudes, perfil publico

## M3 — Modulos: experiencia y en vivo — 1/6

- [x] M3.1 `modulos/encuestas.md` — **PILOTO** (M0)
- [ ] M3.2 `modulos/desafio.md` — Acciones y puntos (catalogo fijo), pasaporte,
      ranking, premios y canjes, golden tickets
- [ ] M3.3 `modulos/anuncios.md` — Composer + "Lleva a", push programadas,
      recordatorios, campana
- [ ] M3.4 `modulos/en-vivo.md` — Streaming, chat (moderacion automatica,
      slow mode), Q&A, panel custom (Slido/Miro); los juegos se operan en MC
- [ ] M3.5 `modulos/mi-qr.md` — Credencial del asistente (solo app movil);
      que pasa cuando lo escanean staff / vendedores / kiosko
- [ ] M3.6 `modulos/asistente-soporte.md` — FAQ "Asistente" + tickets de
      soporte (nucleo fijo, no apagable)

## M4 — Mundo vendedor — 0/1

- [ ] M4.1 `modulos/scanner.md` — El cluster completo: Mi Stand, equipo e
      invitaciones QR, scanner de leads, contactos, estadisticas; como se
      vuelve vendedor alguien (invitacion stand, jamas entra al admin)

## M5 — Admin transversal — 0/6

- [ ] M5.1 `admin/identidad-branding.md` — Branding por superficie, onboarding
      de la app, slides login, encuesta de intereses, highlights, FAQ, recap
- [ ] M5.2 `admin/entrada-registro.md` — Registro, campos (visibilidad
      onboarding/registro/ambos), codigos de acceso, importar
- [ ] M5.3 `admin/asistentes.md` — Perfil, tags, estados, bloqueos, acciones
      auditadas, dominios admin
- [ ] M5.4 `admin/emails.md` — Catalogo fijo 16 correos, fork por evento,
      apagar es apagar, imagenes, enviar prueba, SMTP propio
- [ ] M5.5 `admin/salones-kioskos.md` — Check-in presencial completo: salones
      y ocupacion, kiosko lobby + kiosko de salon (QR conectar tablet, cola
      offline), staff con la app (room check-in, asignar staff)
- [ ] M5.6 `admin/staff-permisos.md` — Catalogo fijo 8 roles / 13 permisos,
      quien ve que en el admin, super_admin

## M6 — Operacion y sistema — 0/4

- [ ] M6.1 `operacion/mission-control.md` — Tiempos (start/end/delay), modo
      interactivo, moderar chat/Q&A, encuestas de sesion, trivia, ruleta,
      jackpot, attendance check / silent disco, proyeccion a segunda pantalla
- [ ] M6.2 `operacion/event-pulse.md`
- [ ] M6.3 `operacion/data-center.md` — ~45 datasets en 9 tabs, export
      csv/xlsx/ZIP maestro, metas, reportes programados, embeds por token
- [ ] M6.4 `operacion/sistema.md` — Eventos (un droplet por cliente, sandbox),
      webhooks + API keys + logs, limites de uso

## M7 — Cierre v1 — 0/4

- [ ] M7.1 Capturas del admin real en las paginas que lo pidan
- [ ] M7.2 Pase de lectura Kamilo completo (tono, honestidad, huecos)
- [ ] M7.3 Deploy del sitio (junto al DEPLOY DEMO: subdominio p.ej.
      `manual.` o `docs.`, estatico — Nginx sirve `manual/dist/`)
- [ ] M7.4 Link contextual "¿Como funciona?" en cada modulo del admin → su
      pagina del manual (se disena el patron una vez, se aplica a todos)

---

## Huecos de producto cazados por el barrido M0 (decisiones Kamilo, NO se codean solos)

1. **`/encuestas` fuera del rail webapp** aunque ModuleCatalog dice
   `web: 'rail'` — hoy solo se llega desde el Home de evento finalizado
   (SidebarPill no la lista). ¿Se agrega al rail o se corrige el canon?
2. **Enforcement Expo confirmado con detalle**: ModuleMenu hardcodeado a 4
   modulos; documentos, banners, passport standalone, pages/[id] y
   recap/[eventId] son pantallas huerfanas sin entrada. (Ya estaba en
   backlog PENDIENTES; el barrido dejo la lista fina.)
3. **LeadResource del admin totalmente huerfano** (nav oculto, sin grupo) —
   candidato a demoler: DC tiene `leads_master` y la webapp vendor lo cubre.
4. **`/scanner-stand` webapp sin entrada de navegacion localizada** —
   verificar que el flujo vendedor llega (posible boton no cazado por grep).
