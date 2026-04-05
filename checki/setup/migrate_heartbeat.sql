-- Migración: Heartbeat de tótems
-- Ejecutar una sola vez en la BD existente.
-- Añade ultimo_ping a la tabla totems para rastrear cuándo fue el último ping de cada dispositivo.

ALTER TABLE totems
  ADD COLUMN ultimo_ping DATETIME(3) NULL DEFAULT NULL
    COMMENT 'Última vez que el tótem hizo ping a /api/ping — NULL si nunca ha pingado'
  AFTER ip_local;
