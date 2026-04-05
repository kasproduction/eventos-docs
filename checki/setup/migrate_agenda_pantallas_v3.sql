-- ============================================================
-- Migración v3: Soporte de Video para pantallas de agenda
-- Ejecutar en HeidiSQL DESPUÉS de migrate_agenda_pantallas_v2.sql
-- ============================================================

ALTER TABLE agenda_pantallas
  MODIFY COLUMN modo       ENUM('agenda','imagen','video','apagada') NOT NULL DEFAULT 'agenda',
  ADD    COLUMN video_path VARCHAR(255)                NULL          AFTER imagen_path,
  ADD    COLUMN loop_video TINYINT(1)   NOT NULL DEFAULT 1           AFTER video_path,
  ADD    COLUMN video_fit  ENUM('contain','cover') NOT NULL DEFAULT 'contain' AFTER loop_video;

INSERT IGNORE INTO configuracion (clave, valor) VALUES
  ('agenda_override_video',      NULL),
  ('agenda_override_loop_video', '1'),
  ('agenda_override_video_fit',  'contain');
