-- =============================================================
-- DATOS DE PRUEBA — Sistema Checkin/Checkout
-- Ejecutar DESPUÉS de schema.sql
-- =============================================================

USE checkin_system;

-- ─── Evento ──────────────────────────────────────────────
INSERT INTO eventos (nombre, slug, fecha_inicio, fecha_fin, activo, config_json) VALUES
('Evento Demo 2025', 'evento-demo-2025', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 1,
 '{"umbral_default": 15, "buffer_pre_charla": 15, "debounce_segundos": 5}');

SET @evento_id = LAST_INSERT_ID();

-- ─── Salones ─────────────────────────────────────────────
INSERT INTO salones (evento_id, nombre, capacidad) VALUES
(@evento_id, 'Salón A', 500),
(@evento_id, 'Salón B', 300);

SET @salon_a = (SELECT id FROM salones WHERE nombre = 'Salón A' AND evento_id = @evento_id);
SET @salon_b = (SELECT id FROM salones WHERE nombre = 'Salón B' AND evento_id = @evento_id);

-- ─── Tótems ──────────────────────────────────────────────
INSERT INTO totems (evento_id, salon_id, nombre, tipo, ip_local) VALUES
(@evento_id, @salon_a, 'Salón A — Entrada',  'entrada',         '192.168.1.101'),
(@evento_id, @salon_a, 'Salón A — Salida',   'salida',          '192.168.1.102'),
(@evento_id, @salon_b, 'Salón B — Entrada',  'entrada',         '192.168.1.103'),
(@evento_id, @salon_b, 'Salón B — Salida',   'salida',          '192.168.1.104');

-- ─── Días del evento ─────────────────────────────────────
INSERT INTO dias_evento (evento_id, fecha, nombre, orden) VALUES
(@evento_id, CURDATE(),                             'Día 1 — Apertura',  1),
(@evento_id, DATE_ADD(CURDATE(), INTERVAL 1 DAY),  'Día 2',             2),
(@evento_id, DATE_ADD(CURDATE(), INTERVAL 2 DAY),  'Día 3',             3),
(@evento_id, DATE_ADD(CURDATE(), INTERVAL 3 DAY),  'Día 4 — Cierre',    4);

SET @dia1 = (SELECT id FROM dias_evento WHERE orden = 1 AND evento_id = @evento_id);

-- ─── Charlas de HOY ──────────────────────────────────────
-- Salón A
INSERT INTO charlas (dia_evento_id, salon_id, titulo, ponente, hora_inicio, hora_fin,
                     hora_inicio_original, hora_fin_original, orden_en_dia) VALUES
(@dia1, @salon_a, 'Bienvenida e Inauguración',  'Dir. General',
 CONCAT(CURDATE(), ' 08:00:00'), CONCAT(CURDATE(), ' 09:00:00'),
 CONCAT(CURDATE(), ' 08:00:00'), CONCAT(CURDATE(), ' 09:00:00'), 1),

(@dia1, @salon_a, 'Innovación en IA',            'Dr. García',
 CONCAT(CURDATE(), ' 09:00:00'), CONCAT(CURDATE(), ' 10:00:00'),
 CONCAT(CURDATE(), ' 09:00:00'), CONCAT(CURDATE(), ' 10:00:00'), 2),

(@dia1, @salon_a, 'Transformación Digital',      'Ing. López',
 CONCAT(CURDATE(), ' 10:30:00'), CONCAT(CURDATE(), ' 11:30:00'),
 CONCAT(CURDATE(), ' 10:30:00'), CONCAT(CURDATE(), ' 11:30:00'), 3),

(@dia1, @salon_a, 'Panel: Futuro del Trabajo',   NULL,
 CONCAT(CURDATE(), ' 14:00:00'), CONCAT(CURDATE(), ' 15:00:00'),
 CONCAT(CURDATE(), ' 14:00:00'), CONCAT(CURDATE(), ' 15:00:00'), 4),

(@dia1, @salon_a, 'Cierre Día 1',                'Dir. General',
 CONCAT(CURDATE(), ' 17:00:00'), CONCAT(CURDATE(), ' 18:00:00'),
 CONCAT(CURDATE(), ' 17:00:00'), CONCAT(CURDATE(), ' 18:00:00'), 5);

-- Salón B
INSERT INTO charlas (dia_evento_id, salon_id, titulo, ponente, hora_inicio, hora_fin,
                     hora_inicio_original, hora_fin_original, orden_en_dia) VALUES
(@dia1, @salon_b, 'Workshop: Liderazgo Ágil',   'Lic. Pérez',
 CONCAT(CURDATE(), ' 09:00:00'), CONCAT(CURDATE(), ' 11:00:00'),
 CONCAT(CURDATE(), ' 09:00:00'), CONCAT(CURDATE(), ' 11:00:00'), 1),

(@dia1, @salon_b, 'Taller: Design Thinking',     'Arq. Martínez',
 CONCAT(CURDATE(), ' 14:00:00'), CONCAT(CURDATE(), ' 16:00:00'),
 CONCAT(CURDATE(), ' 14:00:00'), CONCAT(CURDATE(), ' 16:00:00'), 2);

-- ─── Asistentes de prueba ─────────────────────────────────
INSERT INTO asistentes (evento_id, uid_qr, nombre, email, empresa, external_id, fuente) VALUES
(@evento_id, 'QR-TEST-001', 'Ana García',      'ana.garcia@demo.com',   'Demo Corp', '1001', 'manual'),
(@evento_id, 'QR-TEST-002', 'Luis Martínez',   'luis.m@demo.com',       'Demo Corp', '1002', 'manual'),
(@evento_id, 'QR-TEST-003', 'María López',     'maria.l@empresa.com',   'Empresa SA','1003', 'manual'),
(@evento_id, 'QR-TEST-004', 'Carlos Ruiz',     'carlos.r@empresa.com',  'Empresa SA','1004', 'manual'),
(@evento_id, 'QR-TEST-005', 'Sofía Torres',    'sofia.t@startup.io',    'Startup IO','1005', 'manual'),
(@evento_id, 'QR-TEST-006', 'Pedro Sánchez',   'pedro.s@startup.io',    'Startup IO','1006', 'manual'),
(@evento_id, 'QR-TEST-007', 'Laura Jiménez',   'laura.j@demo.com',      'Demo Corp', '1007', 'manual'),
(@evento_id, 'QR-TEST-008', 'Diego Fernández', 'diego.f@empresa.com',   'Empresa SA','1008', 'manual'),
(@evento_id, 'QR-TEST-009', 'Valentina Cruz',  'vale.c@startup.io',     'Startup IO','1009', 'manual'),
(@evento_id, 'QR-TEST-010', 'Roberto Díaz',    'roberto.d@demo.com',    'Demo Corp', '1010', 'manual');

SELECT CONCAT('✓ Evento creado con ID: ', @evento_id) AS resultado;
SELECT CONCAT('✓ Salón A ID: ', @salon_a, ' | Salón B ID: ', @salon_b) AS salones;
SELECT CONCAT('✓ Día 1 ID: ', @dia1) AS dia;
SELECT '✓ 10 asistentes de prueba insertados' AS asistentes;
SELECT '✓ Ejecuta setup/setup.php para crear el operador admin' AS siguiente_paso;
