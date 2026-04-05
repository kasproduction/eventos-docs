-- =============================================================
-- RESET + SEED — EVENTO DEMO MARZO 30 - ABRIL 1, 2026
-- Ejecutar para crear entorno de demo/dry run
-- =============================================================

SET NAMES utf8mb4;
SET foreign_key_checks = 0;

USE checkin_system;

-- ─────────────────────────────────────────────────────────────
-- 1. LIMPIAR TODAS LAS TABLAS (en orden inverso por FK)
-- ─────────────────────────────────────────────────────────────
TRUNCATE TABLE asistencia_calculada;
TRUNCATE TABLE agenda_cambios;
TRUNCATE TABLE movimientos;
TRUNCATE TABLE estado_asistentes;
TRUNCATE TABLE sync_log;
TRUNCATE TABLE asistentes;
TRUNCATE TABLE charlas;
TRUNCATE TABLE dias_evento;
TRUNCATE TABLE totems;
TRUNCATE TABLE salones;
TRUNCATE TABLE operadores;
TRUNCATE TABLE eventos;

-- ─────────────────────────────────────────────────────────────
-- 2. EVENTO
-- ─────────────────────────────────────────────────────────────
INSERT INTO eventos (id, nombre, slug, fecha_inicio, fecha_fin, activo, config_json) VALUES
(1, 'Tech Summit 2026', 'tech-summit-2026', '2026-03-30', '2026-04-01', 1, '{"umbral_default": 15, "buffer_pre_charla": 15, "debounce_segundos": 5}');

-- ─────────────────────────────────────────────────────────────
-- 3. SALONES
-- ─────────────────────────────────────────────────────────────
INSERT INTO salones (id, evento_id, nombre, capacidad, activo) VALUES
(1, 1, 'Auditorio Principal', 300, 1),
(2, 1, 'Sala de Conferencias A', 100, 1),
(3, 1, 'Sala de Conferencias B', 80, 1),
(4, 1, 'Laboratorio Tech', 40, 1);

-- ─────────────────────────────────────────────────────────────
-- 4. DÍAS DEL EVENTO
-- ─────────────────────────────────────────────────────────────
INSERT INTO dias_evento (id, evento_id, fecha, nombre, orden) VALUES
(1, 1, '2026-03-30', 'Día 1 - Apertura', 1),
(2, 1, '2026-03-31', 'Día 2 - Conferencias', 2),
(3, 1, '2026-04-01', 'Día 3 - Cierre', 3);

-- ─────────────────────────────────────────────────────────────
-- 5. CHARLAS (variadas por día y salón)
-- ─────────────────────────────────────────────────────────────
-- DÍA 1 - Lunes 16
INSERT INTO charlas (dia_evento_id, salon_id, titulo, ponente, hora_inicio, hora_fin, hora_inicio_original, hora_fin_original, umbral_min_asistencia, buffer_pre_inicio, cancelada, orden_en_dia) VALUES
-- Auditorium Principal
(1, 1, 'Apertura Tech Summit 2026', 'CEO TechCorp', '2026-03-30 09:00:00', '2026-03-30 10:00:00', '2026-03-30 09:00:00', '2026-03-30 10:00:00', 15, 15, 0, 1),
(1, 1, 'El Futuro de la IA en la Empresa', 'Dra. María García', '2026-03-30 10:30:00', '2026-03-30 11:30:00', '2026-03-30 10:30:00', '2026-03-30 11:30:00', 15, 15, 0, 2),
(1, 1, 'Transformación Digital Post-Pandemia', 'Juan Pérez', '2026-03-30 12:00:00', '2026-03-30 13:00:00', '2026-03-30 12:00:00', '2026-03-30 13:00:00', 15, 15, 0, 3),
-- Sala A
(1, 2, 'Cloud Computing Avanzado', 'Carlos López', '2026-03-30 09:30:00', '2026-03-30 11:00:00', '2026-03-30 09:30:00', '2026-03-30 11:00:00', 15, 15, 0, 1),
(1, 2, 'DevOps en Escala', 'Ana Martínez', '2026-03-30 11:30:00', '2026-03-30 13:00:00', '2026-03-30 11:30:00', '2026-03-30 13:00:00', 15, 15, 0, 2),
-- Sala B
(1, 3, 'Introducción a Kubernetes', 'Pedro Sánchez', '2026-03-30 10:00:00', '2026-03-30 12:00:00', '2026-03-30 10:00:00', '2026-03-30 12:00:00', 15, 15, 0, 1),
-- Laboratorio
(1, 4, 'Workshop: hands-on Machine Learning', 'Laura Torres', '2026-03-30 14:00:00', '2026-03-30 17:00:00', '2026-03-30 14:00:00', '2026-03-30 17:00:00', 15, 15, 0, 1);

-- DÍA 2 - Martes 17
INSERT INTO charlas (dia_evento_id, salon_id, titulo, ponente, hora_inicio, hora_fin, hora_inicio_original, hora_fin_original, umbral_min_asistencia, buffer_pre_inicio, cancelada, orden_en_dia) VALUES
-- Auditorium Principal
(2, 1, 'Keynote: Computación Cuántica', 'Dr. Richard Feynman', '2026-03-31 09:00:00', '2026-03-31 10:30:00', '2026-03-31 09:00:00', '2026-03-31 10:30:00', 15, 15, 0, 1),
(2, 1, 'Seguridad Cibernética 2026', 'Elena Rodríguez', '2026-03-31 11:00:00', '2026-03-31 12:30:00', '2026-03-31 11:00:00', '2026-03-31 12:30:00', 15, 15, 0, 2),
(2, 1, 'Blockchain para Empresas', 'Miguel Ángel', '2026-03-31 14:00:00', '2026-03-31 15:30:00', '2026-03-31 14:00:00', '2026-03-31 15:30:00', 15, 15, 0, 3),
(2, 1, 'Cierre del Día 2 - Networking', NULL, '2026-03-31 16:00:00', '2026-03-31 18:00:00', '2026-03-31 16:00:00', '2026-03-31 18:00:00', 15, 15, 0, 4),
-- Sala A
(2, 2, 'Arquitectura de Microservicios', 'Sofia Hernández', '2026-03-31 09:30:00', '2026-03-31 11:30:00', '2026-03-31 09:30:00', '2026-03-31 11:30:00', 15, 15, 0, 1),
(2, 2, 'Testing Automatizado a Escala', 'Diego Ruiz', '2026-03-31 13:00:00', '2026-03-31 15:00:00', '2026-03-31 13:00:00', '2026-03-31 15:00:00', 15, 15, 0, 2),
-- Sala B
(2, 3, 'Big Data y Analytics', 'Patricia Gómez', '2026-03-31 10:00:00', '2026-03-31 12:00:00', '2026-03-31 10:00:00', '2026-03-31 12:00:00', 15, 15, 0, 1),
(2, 3, 'Data Science Práctico', 'Jorge Vargas', '2026-03-31 14:00:00', '2026-03-31 16:00:00', '2026-03-31 14:00:00', '2026-03-31 16:00:00', 15, 15, 0, 2),
-- Laboratorio
(2, 4, 'Workshop: Docker Avanzado', 'Rosa Flores', '2026-03-31 09:00:00', '2026-03-31 13:00:00', '2026-03-31 09:00:00', '2026-03-31 13:00:00', 15, 15, 0, 1),
(2, 4, 'Workshop: CI/CD con GitHub Actions', 'Tomás Lowe', '2026-03-31 14:00:00', '2026-03-31 18:00:00', '2026-03-31 14:00:00', '2026-03-31 18:00:00', 15, 15, 0, 2);

-- DÍA 3 - Miércoles 18
INSERT INTO charlas (dia_evento_id, salon_id, titulo, ponente, hora_inicio, hora_fin, hora_inicio_original, hora_fin_original, umbral_min_asistencia, buffer_pre_inicio, cancelada, orden_en_dia) VALUES
-- Auditorium Principal
(3, 1, 'Innovación en Startups', 'María Valencia', '2026-04-01 09:00:00', '2026-04-01 10:30:00', '2026-04-01 09:00:00', '2026-04-01 10:30:00', 15, 15, 0, 1),
(3, 1, 'Panel: El Desarrollador del Futuro', 'Varios Ponentes', '2026-04-01 11:00:00', '2026-04-01 12:30:00', '2026-04-01 11:00:00', '2026-04-01 12:30:00', 15, 15, 0, 2),
(3, 1, 'Clausura Tech Summit 2026', 'Comité Organizador', '2026-04-01 15:00:00', '2026-04-01 16:30:00', '2026-04-01 15:00:00', '2026-04-01 16:30:00', 15, 15, 0, 3),
-- Sala A
(3, 2, 'UX/UI Design Systems', 'Carmen Ruiz', '2026-04-01 09:30:00', '2026-04-01 11:30:00', '2026-04-01 09:30:00', '2026-04-01 11:30:00', 15, 15, 0, 1),
(3, 2, 'Accesibilidad Web', 'Fernando Díaz', '2026-04-01 13:00:00', '2026-04-01 15:00:00', '2026-04-01 13:00:00', '2026-04-01 15:00:00', 15, 15, 0, 2),
-- Sala B
(3, 3, 'Inteligencia Artificial Ética', 'Daniela Peña', '2026-04-01 10:00:00', '2026-04-01 12:00:00', '2026-04-01 10:00:00', '2026-04-01 12:00:00', 15, 15, 0, 1),
-- Laboratorio
(3, 4, 'Workshop: Python para Data Science', 'Alberto Castro', '2026-04-01 09:00:00', '2026-04-01 13:00:00', '2026-04-01 09:00:00', '2026-04-01 13:00:00', 15, 15, 0, 1);

-- ─────────────────────────────────────────────────────────────
-- 6. OPERADORES
-- ─────────────────────────────────────────────────────────────
INSERT INTO operadores (evento_id, nombre, pin, rol, activo) VALUES
(1, 'Administrador', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1),
(1, 'Operador Demo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supervisor', 1),
(1, 'Recepción', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'viewer', 1);

-- PIN hasheado = password_hash('1234', PASSWORD_BCRYPT)
-- El mismo hash para todos = PIN "1234"

-- ─────────────────────────────────────────────────────────────
-- 7. ASISTENTES (50 registros de ejemplo)
-- ─────────────────────────────────────────────────────────────
INSERT INTO asistentes (evento_id, uid_qr, nombre, email, empresa, external_id, fuente) VALUES
(1, 'ATT001', 'Alejandro Mendoza', 'alejandro.mendoza@empresa1.com', 'TechCorp', 'ext_001', 'eventbrite'),
(1, 'ATT002', 'Beatriz González', 'beatriz.gonzalez@empresa2.com', 'DataSystems', 'ext_002', 'eventbrite'),
(1, 'ATT003', 'Carlos Rodríguez', 'carlos.r@startup.io', 'StartupLabs', 'ext_003', 'csv'),
(1, 'ATT004', 'Daniela López', 'daniela.l@cloudtech.com', 'CloudTech', 'ext_004', 'eventbrite'),
(1, 'ATT005', 'Eduardo Fernández', 'eduardo.f@bigcorp.com', 'BigCorp', 'ext_005', 'eventbrite'),
(1, 'ATT006', 'Fernanda Silva', 'fernanda.s@devteam.co', 'DevTeam', 'ext_006', 'csv'),
(1, 'ATT007', 'Gabriel Torres', 'gabriel.t@airesoft.com', 'AireSoft', 'ext_007', 'eventbrite'),
(1, 'ATT008', 'Helena Ramírez', 'helena.r@datapro.com', 'DataPro', 'ext_008', 'eventbrite'),
(1, 'ATT009', 'Iván Márquez', 'ivan.m@techsolutions.com', 'TechSolutions', 'ext_009', 'json'),
(1, 'ATT010', 'Julia Herrera', 'julia.h@innovatech.com', 'InnovaTech', 'ext_010', 'eventbrite'),
(1, 'ATT011', 'Kevin Castro', 'kevin.c@webdev.io', 'WebDev', 'ext_011', 'eventbrite'),
(1, 'ATT012', 'Laura Bautista', 'laura.b@appsmobile.com', 'AppsMobile', 'ext_012', 'csv'),
(1, 'ATT013', 'Mario Delgado', 'mario.d@sysadmin.net', 'SysAdmin', 'ext_013', 'eventbrite'),
(1, 'ATT014', 'Natalia Ortiz', 'natalia.o@securityfirst.com', 'SecurityFirst', 'ext_014', 'eventbrite'),
(1, 'ATT015', 'Oscar Paredes', 'oscar.p@codefactory.com', 'CodeFactory', 'ext_015', 'json'),
(1, 'ATT016', 'Paula Aguirre', 'paula.a@digitalagency.com', 'DigitalAgency', 'ext_016', 'eventbrite'),
(1, 'ATT017', 'Quetzalcoatl Reyes', 'quetzal.r@aztectech.com', 'AztecTech', 'ext_017', 'eventbrite'),
(1, 'ATT018', 'Raúl Mendoza', 'raul.m@globaltech.com', 'GlobalTech', 'ext_018', 'csv'),
(1, 'ATT019', 'Sandra Vargas', 'sandra.v@consulting.io', 'TechConsulting', 'ext_019', 'eventbrite'),
(1, 'ATT020', 'Tomás Escobedo', 'tomas.e@futuredev.com', 'FutureDev', 'ext_020', 'eventbrite'),
(1, 'ATT021', 'Úrsula Fuentes', 'ursula.f@airesearch.com', 'AIResearch', 'ext_021', 'eventbrite'),
(1, 'ATT022', 'Víctor Hugo León', 'victor.leon@neuralnet.ai', 'NeuralNet', 'ext_022', 'json'),
(1, 'ATT023', 'Wendy Cruz', 'wendy.c@creativecoders.com', 'CreativeCoders', 'ext_023', 'eventbrite'),
(1, 'ATT024', 'Xavier active', 'xavier.a@techhub.com', 'TechHub', 'ext_024', 'eventbrite'),
(1, 'ATT025', 'Yolanda active', 'yolanda.p@empresax.com', 'EmpresaX', 'ext_025', 'csv'),
(1, 'ATT026', 'Zara active', 'zara.l@startupY.com', 'StartupY', 'ext_026', 'eventbrite'),
(1, 'ATT027', 'Andrés active', 'andres.g@techcorp.mx', 'TechCorp MX', 'ext_027', 'eventbrite'),
(1, 'ATT028', 'Brenda active', 'brenda.r@datatech.com', 'DataTech', 'ext_028', 'eventbrite'),
(1, 'ATT029', 'Claudia active', 'claudia.z@cloudnine.io', 'CloudNine', 'ext_029', 'json'),
(1, 'ATT030', 'David active', 'david.p@securetech.com', 'SecureTech', 'ext_030', 'eventbrite'),
(1, 'ATT031', 'Emilia active', 'emilia.c@webagency.com', 'WebAgency', 'ext_031', 'eventbrite'),
(1, 'ATT032', 'Federico active', 'federico.m@devhouse.co', 'DevHouse', 'ext_032', 'csv'),
(1, 'ATT033', 'Gloria active', 'gloria.t@innovadores.com', 'Innovadores', 'ext_033', 'eventbrite'),
(1, 'ATT034', 'Hugo active', 'hugo.s@codeworks.io', 'CodeWorks', 'ext_034', 'eventbrite'),
(1, 'ATT035', 'Isabel active', 'isabel.r@techretreat.com', 'TechRetreat', 'ext_035', 'eventbrite'),
(1, 'ATT036', 'Jorge active', 'jorge.l@datadriven.ai', 'DataDriven', 'ext_036', 'json'),
(1, 'ATT037', 'Karen active', 'karen.p@softskills.dev', 'SoftSkills', 'ext_037', 'eventbrite'),
(1, 'ATT038', 'Luis active', 'luis.g@enterprisesol.com', 'EnterpriseSol', 'ext_038', 'eventbrite'),
(1, 'ATT039', 'Mónica active', 'monica.z@agiletec.com', 'AgileTec', 'ext_039', 'csv'),
(1, 'ATT040', 'Nicolás active', 'nicolas.b@futurework.io', 'FutureWork', 'ext_040', 'eventbrite'),
(1, 'ATT041', 'Olga active', 'olga.m@techmentors.com', 'TechMentors', 'ext_041', 'eventbrite'),
(1, 'ATT042', 'Pablo active', 'pablo.c@devopspro.net', 'DevOpsPro', 'ext_042', 'eventbrite'),
(1, 'ATT043', 'Quima active', 'quima.a@womenintech.com', 'WomenInTech', 'ext_043', 'eventbrite'),
(1, 'ATT044', 'Roberto active', 'roberto.t@cloudarchitect.io', 'CloudArchitect', 'ext_044', 'json'),
(1, 'ATT045', 'Silvia active', 'silvia.r@dataviz.co', 'DataViz', 'ext_045', 'eventbrite'),
(1, 'ATT046', 'Tadeo active', 'tadeo.p@techstartups.com', 'TechStartups', 'ext_046', 'eventbrite'),
(1, 'ATT047', 'Ulises active', 'ulises.g@machinelearn.io', 'MachineLearn', 'ext_047', 'csv'),
(1, 'ATT048', 'Valeria active', 'valeria.s@techlead.dev', 'TechLead', 'ext_048', 'eventbrite'),
(1, 'ATT049', 'Walter active', 'walter.z@syseng.com', 'SysEng', 'ext_049', 'eventbrite'),
(1, 'ATT050', 'Ximena active', 'ximena.l@digitaltrans.io', 'DigitalTrans', 'ext_050', 'eventbrite');

-- ─────────────────────────────────────────────────────────────
-- 8. TÓTEMS (puntos de check-in)
-- ─────────────────────────────────────────────────────────────
INSERT INTO totems (evento_id, salon_id, nombre, tipo, activo) VALUES
(1, 1, 'Auditorio - Entrada Principal', 'entrada', 1),
(1, 1, 'Auditorio - Salida Principal', 'salida', 1),
(1, 2, 'Sala A - Entrada', 'entrada', 1),
(1, 2, 'Sala A - Salida', 'salida', 1),
(1, 3, 'Sala B - Entrada', 'entrada', 1),
(1, 3, 'Sala B - Salida', 'salida', 1),
(1, 4, 'Laboratorio - Bidireccional', 'bidireccional', 1),
(1, 1, 'Recepción - Bidireccional', 'bidireccional', 1);

SET foreign_key_checks = 1;

-- SELECT '✓ Evento demo creado: Tech Summit 2026 (30 mar - 1 abr)' AS mensaje;
-- SELECT '✓ 4 salones, 3 días, 23 charlas, 50 asistentes, 8 totems' AS detalle;
