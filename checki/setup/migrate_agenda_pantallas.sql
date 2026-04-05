-- ─────────────────────────────────────────────────────────────────────────────
-- Migración: Pantallas de agenda y configuración global
-- Ejecutar UNA vez en HeidiSQL (o equivalente).
-- ─────────────────────────────────────────────────────────────────────────────

-- Pantallas de agenda (TVs/monitores en las salas)
CREATE TABLE IF NOT EXISTS agenda_pantallas (
    id        INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre    VARCHAR(100) NOT NULL,
    salon_id  INT          NULL,
    activa    TINYINT(1)   NOT NULL DEFAULT 1,
    CONSTRAINT fk_ap_salon FOREIGN KEY (salon_id)
        REFERENCES salones(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla genérica de configuración clave→valor
CREATE TABLE IF NOT EXISTS configuracion (
    clave  VARCHAR(100) NOT NULL PRIMARY KEY,
    valor  TEXT         NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Valor inicial del override global:
--   'none'  → cada pantalla muestra su salón configurado
--   'off'   → todas las pantallas apagadas (pantalla negra)
--   '1','2' → todas las pantallas muestran ese salón_id
INSERT IGNORE INTO configuracion (clave, valor)
VALUES ('agenda_override', 'none');
