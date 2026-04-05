-- =============================================================
-- SISTEMA DE CHECKIN / CHECKOUT — EVENTOS CORPORATIVOS
-- Schema SQL v1.0
-- MySQL 8.0+
-- =============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

DROP DATABASE IF EXISTS checkin_system;
CREATE DATABASE checkin_system
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE checkin_system;

-- =============================================================
-- GRUPO 1: CONFIGURACIÓN DEL EVENTO
-- =============================================================

CREATE TABLE eventos (
  id                INT           NOT NULL AUTO_INCREMENT,
  nombre            VARCHAR(200)  NOT NULL,
  slug              VARCHAR(100)  NOT NULL,
  fecha_inicio      DATE          NOT NULL,
  fecha_fin         DATE          NOT NULL,
  activo            TINYINT(1)    NOT NULL DEFAULT 1,
  config_json       JSON          NULL COMMENT 'umbral_default, buffer_pre_charla, debounce_segundos, etc.',
  created_at        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_eventos_slug (slug)
) ENGINE=InnoDB;


CREATE TABLE salones (
  id                INT           NOT NULL AUTO_INCREMENT,
  evento_id         INT           NOT NULL,
  nombre            VARCHAR(100)  NOT NULL,
  capacidad         INT           NULL COMMENT 'NULL = sin límite',
  activo            TINYINT(1)    NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY idx_salones_evento (evento_id),
  CONSTRAINT fk_salones_evento FOREIGN KEY (evento_id) REFERENCES eventos (id)
) ENGINE=InnoDB;


CREATE TABLE totems (
  id                INT           NOT NULL AUTO_INCREMENT,
  evento_id         INT           NOT NULL,
  salon_id          INT           NOT NULL,
  nombre            VARCHAR(100)  NOT NULL COMMENT 'ej: Salón A - Entrada',
  tipo              ENUM('entrada','salida','bidireccional') NOT NULL DEFAULT 'bidireccional',
  activo            TINYINT(1)    NOT NULL DEFAULT 1,
  ip_local          VARCHAR(15)   NULL COMMENT 'para diagnóstico en red local',
  PRIMARY KEY (id),
  KEY idx_totems_evento (evento_id),
  KEY idx_totems_salon (salon_id),
  CONSTRAINT fk_totems_evento FOREIGN KEY (evento_id) REFERENCES eventos (id),
  CONSTRAINT fk_totems_salon  FOREIGN KEY (salon_id)  REFERENCES salones (id)
) ENGINE=InnoDB;


CREATE TABLE dias_evento (
  id                INT           NOT NULL AUTO_INCREMENT,
  evento_id         INT           NOT NULL,
  fecha             DATE          NOT NULL,
  nombre            VARCHAR(100)  NULL COMMENT 'ej: Día 1 — Apertura',
  orden             INT           NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_dias_evento_fecha (evento_id, fecha),
  KEY idx_dias_evento (evento_id),
  CONSTRAINT fk_dias_evento FOREIGN KEY (evento_id) REFERENCES eventos (id)
) ENGINE=InnoDB;


CREATE TABLE charlas (
  id                    INT           NOT NULL AUTO_INCREMENT,
  dia_evento_id         INT           NOT NULL,
  salon_id              INT           NOT NULL,
  charla_padre_id       INT           NULL COMMENT 'para salas espejo — apunta a la charla principal',
  titulo                VARCHAR(200)  NOT NULL,
  ponente               VARCHAR(200)  NULL,
  hora_inicio           DATETIME      NOT NULL COMMENT 'se actualiza si hay cambios de agenda',
  hora_fin              DATETIME      NOT NULL COMMENT 'se actualiza si hay cambios de agenda',
  hora_inicio_original  DATETIME      NOT NULL COMMENT 'NUNCA SE MODIFICA — trazabilidad',
  hora_fin_original     DATETIME      NOT NULL COMMENT 'NUNCA SE MODIFICA — trazabilidad',
  umbral_min_asistencia INT           NOT NULL DEFAULT 15 COMMENT 'minutos mínimos para contar asistencia',
  buffer_pre_inicio     INT           NOT NULL DEFAULT 15 COMMENT 'minutos antes del inicio que cuentan para esta charla',
  cancelada             TINYINT(1)    NOT NULL DEFAULT 0,
  orden_en_dia          INT           NOT NULL,
  PRIMARY KEY (id),
  KEY idx_charlas_dia    (dia_evento_id),
  KEY idx_charlas_salon  (salon_id),
  KEY idx_charlas_padre  (charla_padre_id),
  KEY idx_charlas_horario (salon_id, hora_inicio, hora_fin) COMMENT 'para buscar charla activa en cada scan',
  CONSTRAINT fk_charlas_dia    FOREIGN KEY (dia_evento_id)   REFERENCES dias_evento (id),
  CONSTRAINT fk_charlas_salon  FOREIGN KEY (salon_id)        REFERENCES salones (id),
  CONSTRAINT fk_charlas_padre  FOREIGN KEY (charla_padre_id) REFERENCES charlas (id)
) ENGINE=InnoDB;


-- =============================================================
-- GRUPO 2: OPERADORES (va antes de movimientos y agenda_cambios)
-- =============================================================

CREATE TABLE operadores (
  id                INT           NOT NULL AUTO_INCREMENT,
  evento_id         INT           NOT NULL,
  nombre            VARCHAR(100)  NOT NULL,
  pin               VARCHAR(255)  NOT NULL COMMENT 'hash bcrypt',
  rol               ENUM('admin','supervisor','viewer') NOT NULL DEFAULT 'supervisor',
  activo            TINYINT(1)    NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY idx_operadores_evento (evento_id),
  CONSTRAINT fk_operadores_evento FOREIGN KEY (evento_id) REFERENCES eventos (id)
) ENGINE=InnoDB;


-- =============================================================
-- GRUPO 3: ASISTENTES
-- =============================================================

CREATE TABLE asistentes (
  id                INT           NOT NULL AUTO_INCREMENT,
  evento_id         INT           NOT NULL,
  uid_qr            VARCHAR(255)  NOT NULL COMMENT 'string exacto que emite el lector QR',
  nombre            VARCHAR(200)  NOT NULL,
  email             VARCHAR(200)  NULL,
  empresa           VARCHAR(200)  NULL,
  external_id       VARCHAR(100)  NULL COMMENT 'ID en el sistema de origen',
  fuente            VARCHAR(50)   NOT NULL COMMENT 'eventbrite | csv | json | manual',
  metadata_json     JSON          NULL COMMENT 'campos extra del sistema origen — el core los ignora',
  activo            TINYINT(1)    NOT NULL DEFAULT 1,
  created_at        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_asistentes_uid   (evento_id, uid_qr)    COMMENT 'búsqueda O(1) en cada scan',
  KEY idx_asistentes_email       (evento_id, email),
  KEY idx_asistentes_external_id (evento_id, external_id),
  CONSTRAINT fk_asistentes_evento FOREIGN KEY (evento_id) REFERENCES eventos (id)
) ENGINE=InnoDB;


-- =============================================================
-- GRUPO 4: MOVIMIENTOS (corazón operativo)
-- =============================================================

CREATE TABLE movimientos (
  id                BIGINT        NOT NULL AUTO_INCREMENT,
  evento_id         INT           NOT NULL,
  asistente_id      INT           NOT NULL,
  totem_id          INT           NULL       COMMENT 'NULL cuando metodo = manual sin tótem asignado',
  salon_id          INT           NOT NULL COMMENT 'redundante — optimiza reportes sin JOIN',
  tipo              ENUM('checkin','checkout') NOT NULL,
  timestamp         DATETIME(3)   NOT NULL   COMMENT 'timestamp del SERVIDOR — fuente de verdad, inmutable',
  timestamp_totem   DATETIME(3)   NULL       COMMENT 'timestamp del dispositivo — solo referencia',
  metodo            ENUM('qr_lector','manual','auto_cambio_salon','auto_fin_jornada','auto_fin_charla') NOT NULL DEFAULT 'qr_lector',
  operador_id       INT           NULL       COMMENT 'solo cuando metodo = manual',
  flags             SET(
                      'fuera_horario',
                      'checkout_sin_checkin',
                      'cambio_salon',
                      'inferido',
                      'corregido',
                      'cola_offline'
                    ) NULL,
  notas             TEXT          NULL       COMMENT 'solo para correcciones manuales',
  PRIMARY KEY (id),
  KEY idx_mov_asistente_salon_ts  (asistente_id, salon_id, timestamp)  COMMENT 'para toggle y cálculos',
  KEY idx_mov_evento_ts           (evento_id, timestamp)               COMMENT 'para reportes por evento',
  KEY idx_mov_totem_ts            (totem_id, timestamp)                COMMENT 'para diagnóstico por dispositivo — NULL si movimiento manual sin tótem',
  CONSTRAINT fk_mov_evento      FOREIGN KEY (evento_id)    REFERENCES eventos    (id),
  CONSTRAINT fk_mov_asistente   FOREIGN KEY (asistente_id) REFERENCES asistentes (id),
  CONSTRAINT fk_mov_totem       FOREIGN KEY (totem_id)     REFERENCES totems     (id),
  CONSTRAINT fk_mov_salon       FOREIGN KEY (salon_id)     REFERENCES salones    (id),
  CONSTRAINT fk_mov_operador    FOREIGN KEY (operador_id)  REFERENCES operadores (id)
) ENGINE=InnoDB;


CREATE TABLE estado_asistentes (
  asistente_id          INT           NOT NULL,
  salon_id              INT           NOT NULL,
  estado                ENUM('dentro','fuera') NOT NULL,
  ultimo_movimiento_id  BIGINT        NULL,
  updated_at            TIMESTAMP     NOT NULL,
  PRIMARY KEY (asistente_id, salon_id),
  KEY idx_estado_salon (salon_id),
  CONSTRAINT fk_estado_asistente  FOREIGN KEY (asistente_id)         REFERENCES asistentes (id),
  CONSTRAINT fk_estado_salon      FOREIGN KEY (salon_id)             REFERENCES salones    (id),
  CONSTRAINT fk_estado_movimiento FOREIGN KEY (ultimo_movimiento_id) REFERENCES movimientos (id)
) ENGINE=InnoDB COMMENT='Verdad actual — reconstruible desde movimientos si el servidor se reinicia';


-- =============================================================
-- GRUPO 5: ASISTENCIA CALCULADA
-- =============================================================

CREATE TABLE asistencia_calculada (
  id                    INT           NOT NULL AUTO_INCREMENT,
  evento_id             INT           NOT NULL,
  asistente_id          INT           NOT NULL,
  charla_id             INT           NOT NULL,
  dia_evento_id         INT           NOT NULL,
  checkin_real          DATETIME(3)   NOT NULL,
  checkout_real         DATETIME(3)   NULL      COMMENT 'NULL si no hizo checkout — se usa hora_fin_charla',
  minutos_presentes     INT           NOT NULL,
  cuenta_asistencia     TINYINT(1)    NOT NULL  COMMENT '1 si minutos_presentes >= umbral_min_asistencia',
  calidad_dato          ENUM('real','inferido','corregido') NOT NULL DEFAULT 'real',
  calculado_at          TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_asistencia_asistente_charla (asistente_id, charla_id) COMMENT 'un registro por asistente por charla',
  KEY idx_asistencia_evento   (evento_id),
  KEY idx_asistencia_charla   (charla_id),
  KEY idx_asistencia_dia      (dia_evento_id),
  CONSTRAINT fk_asist_calc_evento     FOREIGN KEY (evento_id)    REFERENCES eventos      (id),
  CONSTRAINT fk_asist_calc_asistente  FOREIGN KEY (asistente_id) REFERENCES asistentes   (id),
  CONSTRAINT fk_asist_calc_charla     FOREIGN KEY (charla_id)    REFERENCES charlas       (id),
  CONSTRAINT fk_asist_calc_dia        FOREIGN KEY (dia_evento_id) REFERENCES dias_evento  (id)
) ENGINE=InnoDB COMMENT='REGENERABLE — no es fuente de verdad, es resultado de cálculo. Se puede borrar y recalcular.';


-- =============================================================
-- GRUPO 6: AUDITORÍA
-- =============================================================

CREATE TABLE agenda_cambios (
  id                    INT           NOT NULL AUTO_INCREMENT,
  charla_id             INT           NOT NULL,
  campo_modificado      VARCHAR(50)   NOT NULL COMMENT 'hora_inicio | hora_fin | salon_id | cancelada | titulo',
  valor_anterior        VARCHAR(200)  NOT NULL,
  valor_nuevo           VARCHAR(200)  NOT NULL,
  motivo                TEXT          NULL,
  operador_id           INT           NOT NULL,
  timestamp             TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  recalculo_requerido   TINYINT(1)    NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY idx_agenda_cambios_charla    (charla_id),
  KEY idx_agenda_cambios_operador  (operador_id),
  KEY idx_agenda_cambios_recalculo (recalculo_requerido),
  CONSTRAINT fk_agenda_cambios_charla    FOREIGN KEY (charla_id)   REFERENCES charlas    (id),
  CONSTRAINT fk_agenda_cambios_operador  FOREIGN KEY (operador_id) REFERENCES operadores (id)
) ENGINE=InnoDB;


CREATE TABLE sync_log (
  id                INT           NOT NULL AUTO_INCREMENT,
  evento_id         INT           NOT NULL,
  fuente            VARCHAR(50)   NOT NULL COMMENT 'eventbrite | csv | json | manual',
  tipo              ENUM('asistentes','agenda','completo') NOT NULL,
  registros         INT           NOT NULL DEFAULT 0,
  errores           INT           NOT NULL DEFAULT 0,
  log_json          JSON          NULL,
  timestamp         TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_sync_log_evento (evento_id),
  CONSTRAINT fk_sync_log_evento FOREIGN KEY (evento_id) REFERENCES eventos (id)
) ENGINE=InnoDB;


-- =============================================================
-- VISTAS ÚTILES
-- =============================================================

-- Charla activa por salón en este momento
CREATE OR REPLACE VIEW v_charla_activa AS
SELECT
  c.id,
  c.salon_id,
  c.titulo,
  c.ponente,
  c.hora_inicio,
  c.hora_fin,
  c.umbral_min_asistencia,
  c.buffer_pre_inicio,
  d.evento_id,
  d.fecha AS dia_fecha
FROM charlas c
JOIN dias_evento d ON d.id = c.dia_evento_id
WHERE
  c.cancelada = 0
  AND c.hora_inicio <= NOW()
  AND c.hora_fin    >= NOW();


-- Personas actualmente dentro de cada salón
CREATE OR REPLACE VIEW v_personas_en_salon AS
SELECT
  ea.salon_id,
  s.nombre AS salon_nombre,
  COUNT(*) AS personas_dentro
FROM estado_asistentes ea
JOIN salones s ON s.id = ea.salon_id
WHERE ea.estado = 'dentro'
GROUP BY ea.salon_id, s.nombre;


-- =============================================================
-- REACTIVAR FK
-- =============================================================

SET foreign_key_checks = 1;
