-- ============================================================
-- Migración v2: Imagen + Auto-retorno para pantallas de agenda
-- Ejecutar en HeidiSQL DESPUÉS de migrate_agenda_pantallas.sql
-- ============================================================

ALTER TABLE agenda_pantallas
  ADD COLUMN modo        ENUM('agenda','imagen','apagada') NOT NULL DEFAULT 'agenda'  AFTER salon_id,
  ADD COLUMN imagen_path VARCHAR(255)                      NULL                       AFTER modo,
  ADD COLUMN retorno_en  DATETIME                          NULL                       AFTER imagen_path;

INSERT IGNORE INTO configuracion (clave, valor) VALUES
  ('agenda_override_imagen',    NULL),
  ('agenda_override_retorno_en', NULL);
