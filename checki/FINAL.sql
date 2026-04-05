-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.4.3 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para checkin_system
CREATE DATABASE IF NOT EXISTS `checkin_system` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `checkin_system`;

-- Volcando estructura para tabla checkin_system.admins
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pass_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.admins: ~0 rows (aproximadamente)

-- Volcando estructura para tabla checkin_system.agenda_cambios
CREATE TABLE IF NOT EXISTS `agenda_cambios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `charla_id` int NOT NULL,
  `campo_modificado` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'hora_inicio | hora_fin | salon_id | cancelada | titulo',
  `valor_anterior` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor_nuevo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `motivo` text COLLATE utf8mb4_unicode_ci,
  `operador_id` int NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `recalculo_requerido` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_agenda_cambios_charla` (`charla_id`),
  KEY `idx_agenda_cambios_operador` (`operador_id`),
  KEY `idx_agenda_cambios_recalculo` (`recalculo_requerido`),
  CONSTRAINT `fk_agenda_cambios_charla` FOREIGN KEY (`charla_id`) REFERENCES `charlas` (`id`),
  CONSTRAINT `fk_agenda_cambios_operador` FOREIGN KEY (`operador_id`) REFERENCES `operadores` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.agenda_cambios: ~2 rows (aproximadamente)
INSERT INTO `agenda_cambios` (`id`, `charla_id`, `campo_modificado`, `valor_anterior`, `valor_nuevo`, `motivo`, `operador_id`, `timestamp`, `recalculo_requerido`) VALUES
	(82, 28, 'hora_inicio', '2026-03-16 09:00:00', '2026-03-11 09:00:00', '', 3, '2026-03-11 20:11:31', 1),
	(83, 28, 'hora_inicio', '2026-03-11 09:00:00', '2026-03-16 09:00:00', '', 3, '2026-03-11 20:29:28', 1);

-- Volcando estructura para tabla checkin_system.agenda_pantallas
CREATE TABLE IF NOT EXISTS `agenda_pantallas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `salon_id` int DEFAULT NULL,
  `modo` enum('agenda','imagen','video','apagada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'agenda',
  `imagen_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loop_video` tinyint(1) NOT NULL DEFAULT '1',
  `video_fit` enum('contain','cover') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'contain',
  `retorno_en` datetime DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_ap_salon` (`salon_id`),
  CONSTRAINT `fk_ap_salon` FOREIGN KEY (`salon_id`) REFERENCES `salones` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.agenda_pantallas: ~2 rows (aproximadamente)
INSERT INTO `agenda_pantallas` (`id`, `nombre`, `salon_id`, `modo`, `imagen_path`, `video_path`, `loop_video`, `video_fit`, `retorno_en`, `activa`) VALUES
	(8, 'prueba', 8, 'agenda', NULL, NULL, 1, 'contain', NULL, 1),
	(9, 'tester', 9, 'agenda', NULL, NULL, 1, 'contain', NULL, 1);

-- Volcando estructura para tabla checkin_system.asistencia_calculada
CREATE TABLE IF NOT EXISTS `asistencia_calculada` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `asistente_id` int NOT NULL,
  `charla_id` int NOT NULL,
  `dia_evento_id` int NOT NULL,
  `checkin_real` datetime(3) NOT NULL,
  `checkout_real` datetime(3) DEFAULT NULL COMMENT 'NULL si no hizo checkout — se usa hora_fin_charla',
  `minutos_presentes` int NOT NULL,
  `cuenta_asistencia` tinyint(1) NOT NULL COMMENT '1 si minutos_presentes >= umbral_min_asistencia',
  `calidad_dato` enum('real','inferido','corregido') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'real',
  `calculado_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_asistencia_asistente_charla` (`asistente_id`,`charla_id`) COMMENT 'un registro por asistente por charla',
  KEY `idx_asistencia_evento` (`evento_id`),
  KEY `idx_asistencia_charla` (`charla_id`),
  KEY `idx_asistencia_dia` (`dia_evento_id`),
  CONSTRAINT `fk_asist_calc_asistente` FOREIGN KEY (`asistente_id`) REFERENCES `asistentes` (`id`),
  CONSTRAINT `fk_asist_calc_charla` FOREIGN KEY (`charla_id`) REFERENCES `charlas` (`id`),
  CONSTRAINT `fk_asist_calc_dia` FOREIGN KEY (`dia_evento_id`) REFERENCES `dias_evento` (`id`),
  CONSTRAINT `fk_asist_calc_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='REGENERABLE — no es fuente de verdad, es resultado de cálculo. Se puede borrar y recalcular.';

-- Volcando datos para la tabla checkin_system.asistencia_calculada: ~0 rows (aproximadamente)

-- Volcando estructura para tabla checkin_system.asistentes
CREATE TABLE IF NOT EXISTS `asistentes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `uid_qr` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'string exacto que emite el lector QR',
  `nombre` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID en el sistema de origen',
  `fuente` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'eventbrite | csv | json | manual',
  `metadata_json` json DEFAULT NULL COMMENT 'campos extra del sistema origen — el core los ignora',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_asistentes_uid` (`evento_id`,`uid_qr`) COMMENT 'búsqueda O(1) en cada scan',
  KEY `idx_asistentes_email` (`evento_id`,`email`),
  KEY `idx_asistentes_external_id` (`evento_id`,`external_id`),
  CONSTRAINT `fk_asistentes_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.asistentes: ~30 rows (aproximadamente)
INSERT INTO `asistentes` (`id`, `evento_id`, `uid_qr`, `nombre`, `email`, `empresa`, `external_id`, `fuente`, `metadata_json`, `activo`, `created_at`, `updated_at`) VALUES
	(11, 1, 'TS2026-001', 'Ana García Reyes', 'ana.garcia@techcorp.mx', 'TechCorp México', 'DEMO-001', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(12, 1, 'TS2026-002', 'Luis Martínez Soto', 'lmartinez@innovagroup.com', 'InnovaGroup', 'DEMO-002', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(13, 1, 'TS2026-003', 'María López Gutiérrez', 'mlopez@startuplab.io', 'StartupLab', 'DEMO-003', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(14, 1, 'TS2026-004', 'Carlos Ruiz Mendoza', 'cruiz@digitalhouse.com', 'Digital House', 'DEMO-004', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(15, 1, 'TS2026-005', 'Sofía Torres Acosta', 'storres@bigcorp.com.ar', 'BigCorp Argentina', 'DEMO-005', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(16, 1, 'TS2026-006', 'Pedro Sánchez Villa', 'pedro.s@cloudnative.dev', 'CloudNative Dev', 'DEMO-006', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(17, 1, 'TS2026-007', 'Laura Jiménez Ríos', 'ljimenez@fintech.pe', 'FinTech Perú', 'DEMO-007', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(18, 1, 'TS2026-008', 'Diego Fernández Mora', 'dfernandez@agile.co', 'Agile Co.', 'DEMO-008', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(19, 1, 'TS2026-009', 'Valentina Cruz Pérez', 'vcruz@unicornaio.com', 'Unicorn.io', 'DEMO-009', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(20, 1, 'TS2026-010', 'Roberto Díaz Flores', 'rdiaz@datacenter.cl', 'DataCenter Chile', 'DEMO-010', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(21, 1, 'TS2026-011', 'Alejandra Vega Salas', 'avega@aiventures.com', 'AI Ventures', 'DEMO-011', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(22, 1, 'TS2026-012', 'Héctor Morales Cano', 'hmorales@cloudpeak.mx', 'CloudPeak', 'DEMO-012', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(23, 1, 'TS2026-013', 'Isabella Romero Niño', 'iromero@proptech.co', 'PropTech Co', 'DEMO-013', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(24, 1, 'TS2026-014', 'Andrés Vargas Oquendo', 'avargas@devstudio.net', 'Dev Studio', 'DEMO-014', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(25, 1, 'TS2026-015', 'Camila Herrera Suárez', 'cherrera@mlengine.ai', 'ML Engine', 'DEMO-015', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(26, 1, 'TS2026-016', 'Javier Castillo Rojas', 'jcastillo@securenet.com', 'SecureNet', 'DEMO-016', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(27, 1, 'TS2026-017', 'Natalia Guerrero Paz', 'nguerrero@hrtech.co', 'HR Tech', 'DEMO-017', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(28, 1, 'TS2026-018', 'Felipe Ortiz Ramos', 'fortiz@opendata.org', 'OpenData Org', 'DEMO-018', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(29, 1, 'TS2026-019', 'Daniela Castro Mora', 'dcastro@saasbuilder.io', 'SaaS Builder', 'DEMO-019', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(30, 1, 'TS2026-020', 'Gonzalo Silva Cabrera', 'gsilva@devops.cl', 'DevOps Chile', 'DEMO-020', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(31, 1, 'TS2026-021', 'Mariana Fuentes Alba', 'mfuentes@edtech.mx', 'EdTech MX', 'DEMO-021', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(32, 1, 'TS2026-022', 'Emilio Navarro Cruz', 'enavarro@iotlab.com', 'IoT Lab', 'DEMO-022', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(33, 1, 'TS2026-023', 'Cristina Delgado Vela', 'cdelgado@legaltech.pe', 'LegalTech Perú', 'DEMO-023', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(34, 1, 'TS2026-024', 'Sebastián Mora Pinto', 'smora@automates.io', 'Automates.io', 'DEMO-024', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(35, 1, 'TS2026-025', 'Paulina Rivas Espejo', 'privas@greentech.com', 'GreenTech', 'DEMO-025', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(36, 1, 'TS2026-026', 'Tomás Ibáñez Correa', 'tibanez@medialab.co', 'MediaLab', 'DEMO-026', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(37, 1, 'TS2026-027', 'Verónica Salinas Peña', 'vsalinas@ecommerce.cl', 'eCommerce Chile', 'DEMO-027', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(38, 1, 'TS2026-028', 'Maximiliano Torres Rey', 'mtorres@blockchain.ar', 'Blockchain AR', 'DEMO-028', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(39, 1, 'TS2026-029', 'Patricia Espinoza Hoz', 'pespinoza@cybersec.mx', 'CyberSec MX', 'DEMO-029', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50'),
	(40, 1, 'TS2026-030', 'Ricardo Álvarez Mena', 'ralvarez@openai-partner.com', 'AI Partner Corp', 'DEMO-030', 'manual', NULL, 1, '2026-03-11 18:55:50', '2026-03-11 18:55:50');

-- Volcando estructura para tabla checkin_system.charlas
CREATE TABLE IF NOT EXISTS `charlas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dia_evento_id` int NOT NULL,
  `salon_id` int NOT NULL,
  `charla_padre_id` int DEFAULT NULL COMMENT 'para salas espejo — apunta a la charla principal',
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ponente` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hora_inicio` datetime NOT NULL COMMENT 'se actualiza si hay cambios de agenda',
  `hora_fin` datetime NOT NULL COMMENT 'se actualiza si hay cambios de agenda',
  `hora_inicio_original` datetime NOT NULL COMMENT 'NUNCA SE MODIFICA — trazabilidad',
  `hora_fin_original` datetime NOT NULL COMMENT 'NUNCA SE MODIFICA — trazabilidad',
  `umbral_min_asistencia` int NOT NULL DEFAULT '15' COMMENT 'minutos mínimos para contar asistencia',
  `buffer_pre_inicio` int NOT NULL DEFAULT '15' COMMENT 'minutos antes del inicio que cuentan para esta charla',
  `cancelada` tinyint(1) NOT NULL DEFAULT '0',
  `orden_en_dia` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_charlas_dia` (`dia_evento_id`),
  KEY `idx_charlas_salon` (`salon_id`),
  KEY `idx_charlas_padre` (`charla_padre_id`),
  KEY `idx_charlas_horario` (`salon_id`,`hora_inicio`,`hora_fin`) COMMENT 'para buscar charla activa en cada scan',
  CONSTRAINT `fk_charlas_dia` FOREIGN KEY (`dia_evento_id`) REFERENCES `dias_evento` (`id`),
  CONSTRAINT `fk_charlas_padre` FOREIGN KEY (`charla_padre_id`) REFERENCES `charlas` (`id`),
  CONSTRAINT `fk_charlas_salon` FOREIGN KEY (`salon_id`) REFERENCES `salones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.charlas: ~32 rows (aproximadamente)
INSERT INTO `charlas` (`id`, `dia_evento_id`, `salon_id`, `charla_padre_id`, `titulo`, `ponente`, `hora_inicio`, `hora_fin`, `hora_inicio_original`, `hora_fin_original`, `umbral_min_asistencia`, `buffer_pre_inicio`, `cancelada`, `orden_en_dia`) VALUES
	(28, 10, 8, NULL, 'Inauguración y bienvenida institucional', 'Comité Organizador', '2026-03-16 09:00:00', '2026-03-16 10:00:00', '2026-03-16 09:00:00', '2026-03-16 10:00:00', 15, 15, 0, 1),
	(29, 10, 8, NULL, 'Keynote: Inteligencia Artificial en la empresa real', 'Dra. Valeria Ríos', '2026-03-16 10:30:00', '2026-03-16 11:30:00', '2026-03-16 10:30:00', '2026-03-16 11:30:00', 15, 15, 0, 2),
	(30, 10, 8, NULL, 'Panel: El rol del CTO en 2026', 'Varios ponentes', '2026-03-16 14:00:00', '2026-03-16 15:30:00', '2026-03-16 14:00:00', '2026-03-16 15:30:00', 15, 15, 0, 3),
	(31, 10, 8, NULL, 'Ciberseguridad sin excusas', 'Ing. Marcos Delgado', '2026-03-16 16:00:00', '2026-03-16 17:00:00', '2026-03-16 16:00:00', '2026-03-16 17:00:00', 15, 15, 0, 4),
	(32, 10, 9, NULL, 'Workshop: Introducción práctica a Machine Learning', 'Lic. Camila Herrera', '2026-03-16 09:00:00', '2026-03-16 12:00:00', '2026-03-16 09:00:00', '2026-03-16 12:00:00', 15, 15, 0, 1),
	(33, 10, 9, NULL, 'Demostración de productos disruptivos', 'Startups invitadas', '2026-03-16 14:00:00', '2026-03-16 15:00:00', '2026-03-16 14:00:00', '2026-03-16 15:00:00', 15, 15, 0, 2),
	(34, 10, 9, NULL, 'Mesa redonda: Ecosistema startup en LATAM', 'Moderador: Arq. Fonseca', '2026-03-16 15:30:00', '2026-03-16 17:00:00', '2026-03-16 15:30:00', '2026-03-16 17:00:00', 15, 15, 0, 3),
	(35, 10, 10, NULL, 'Speed networking — primera ronda', 'Facilitador', '2026-03-16 09:30:00', '2026-03-16 11:00:00', '2026-03-16 09:30:00', '2026-03-16 11:00:00', 15, 15, 0, 1),
	(36, 10, 10, NULL, 'Café y conexiones libres', 'Libre', '2026-03-16 11:00:00', '2026-03-16 12:00:00', '2026-03-16 11:00:00', '2026-03-16 12:00:00', 15, 15, 0, 2),
	(37, 10, 11, NULL, 'Taller: Metodologías ágiles aplicadas', 'Scrum Master Certificado', '2026-03-16 10:00:00', '2026-03-16 12:00:00', '2026-03-16 10:00:00', '2026-03-16 12:00:00', 15, 15, 0, 1),
	(38, 10, 11, NULL, 'Taller: DevOps y entrega continua', 'Ing. Sebastián Mora', '2026-03-16 14:00:00', '2026-03-16 16:00:00', '2026-03-16 14:00:00', '2026-03-16 16:00:00', 15, 15, 0, 2),
	(39, 11, 8, NULL, 'Cloud nativo: más allá de los VMs', 'Arq. Daniela Castro', '2026-03-17 09:00:00', '2026-03-17 10:00:00', '2026-03-17 09:00:00', '2026-03-17 10:00:00', 15, 15, 0, 1),
	(40, 11, 8, NULL, 'Big Data para tomadores de decisión', 'Dr. Ernesto Vega', '2026-03-17 10:30:00', '2026-03-17 11:30:00', '2026-03-17 10:30:00', '2026-03-17 11:30:00', 15, 15, 0, 2),
	(41, 11, 8, NULL, 'Panel: Talento digital — cómo atraerlo y retenerlo', 'RRHH Tech Leaders', '2026-03-17 14:00:00', '2026-03-17 15:00:00', '2026-03-17 14:00:00', '2026-03-17 15:00:00', 15, 15, 0, 3),
	(42, 11, 8, NULL, 'Casos de éxito: transformación en 12 meses', '3 empresas presentan', '2026-03-17 15:30:00', '2026-03-17 16:30:00', '2026-03-17 15:30:00', '2026-03-17 16:30:00', 15, 15, 0, 4),
	(43, 11, 8, NULL, 'Premiación: Innovación del Año', 'Jurado Tech Summit', '2026-03-17 17:00:00', '2026-03-17 18:00:00', '2026-03-17 17:00:00', '2026-03-17 18:00:00', 15, 15, 0, 5),
	(44, 11, 9, NULL, 'Workshop: React y Next.js en producción', 'Ing. Paula Montoya', '2026-03-17 09:00:00', '2026-03-17 12:00:00', '2026-03-17 09:00:00', '2026-03-17 12:00:00', 15, 15, 0, 1),
	(45, 11, 9, NULL, 'Desarrollo con IA: Claude, Cursor y Copilot', 'Kas Production', '2026-03-17 14:00:00', '2026-03-17 16:00:00', '2026-03-17 14:00:00', '2026-03-17 16:00:00', 15, 15, 0, 2),
	(46, 11, 10, NULL, 'Breakfast networking', 'Libre', '2026-03-17 08:30:00', '2026-03-17 09:30:00', '2026-03-17 08:30:00', '2026-03-17 09:30:00', 15, 15, 0, 1),
	(47, 11, 10, NULL, 'Reuniones 1:1 concertadas', 'Participantes', '2026-03-17 11:00:00', '2026-03-17 13:00:00', '2026-03-17 11:00:00', '2026-03-17 13:00:00', 15, 15, 0, 2),
	(48, 11, 10, NULL, 'Lunch networking patrocinado', 'Patrocinador Platinum', '2026-03-17 13:00:00', '2026-03-17 14:00:00', '2026-03-17 13:00:00', '2026-03-17 14:00:00', 15, 15, 0, 3),
	(49, 11, 11, NULL, 'Kubernetes hands-on desde cero', 'Ing. Ricardo Salinas', '2026-03-17 10:00:00', '2026-03-17 12:00:00', '2026-03-17 10:00:00', '2026-03-17 12:00:00', 15, 15, 0, 1),
	(50, 11, 11, NULL, 'Terraform e Infraestructura como Código', 'Sr. DevOps Álvarez', '2026-03-17 14:00:00', '2026-03-17 16:30:00', '2026-03-17 14:00:00', '2026-03-17 16:30:00', 15, 15, 0, 2),
	(51, 12, 8, NULL, 'El futuro del trabajo híbrido', 'Psicóloga Org. Fabiola Neves', '2026-03-18 09:00:00', '2026-03-18 10:00:00', '2026-03-18 09:00:00', '2026-03-18 10:00:00', 15, 15, 0, 1),
	(52, 12, 8, NULL, 'Liderazgo en la era de la automatización', 'MBA Hugo Contreras', '2026-03-18 10:30:00', '2026-03-18 11:30:00', '2026-03-18 10:30:00', '2026-03-18 11:30:00', 15, 15, 0, 2),
	(53, 12, 8, NULL, 'Clausura: aprendizajes y camino a seguir', 'Comité Organizador', '2026-03-18 16:00:00', '2026-03-18 17:30:00', '2026-03-18 16:00:00', '2026-03-18 17:30:00', 15, 15, 0, 3),
	(54, 12, 8, NULL, 'Ceremonia de cierre y despedida', 'Todos los asistentes', '2026-03-18 17:30:00', '2026-03-18 18:30:00', '2026-03-18 17:30:00', '2026-03-18 18:30:00', 15, 15, 0, 4),
	(55, 12, 9, NULL, 'Retrospectiva colectiva del evento', 'Facilitadora: Lic. Silvia Paz', '2026-03-18 09:00:00', '2026-03-18 11:00:00', '2026-03-18 09:00:00', '2026-03-18 11:00:00', 15, 15, 0, 1),
	(56, 12, 9, NULL, 'Presentación de proyectos nacidos en el evento', 'Equipos participantes', '2026-03-18 14:00:00', '2026-03-18 16:00:00', '2026-03-18 14:00:00', '2026-03-18 16:00:00', 15, 15, 0, 2),
	(57, 12, 10, NULL, 'Brunch de cierre patrocinado', 'Patrocinador Gold', '2026-03-18 11:30:00', '2026-03-18 13:00:00', '2026-03-18 11:30:00', '2026-03-18 13:00:00', 15, 15, 0, 1),
	(58, 12, 11, NULL, 'Mini Hackathon: prototipa en 3 horas', 'Mentores disponibles', '2026-03-18 09:00:00', '2026-03-18 12:00:00', '2026-03-18 09:00:00', '2026-03-18 12:00:00', 15, 15, 0, 1),
	(59, 12, 11, NULL, 'Presentación de resultados del Hackathon', 'Equipos + Jurado', '2026-03-18 14:00:00', '2026-03-18 15:30:00', '2026-03-18 14:00:00', '2026-03-18 15:30:00', 15, 15, 0, 2);

-- Volcando estructura para tabla checkin_system.configuracion
CREATE TABLE IF NOT EXISTS `configuracion` (
  `clave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.configuracion: ~6 rows (aproximadamente)
INSERT INTO `configuracion` (`clave`, `valor`) VALUES
	('agenda_override', 'none'),
	('agenda_override_imagen', ''),
	('agenda_override_loop_video', '1'),
	('agenda_override_retorno_en', NULL),
	('agenda_override_video', 'video.mp4'),
	('agenda_override_video_fit', 'contain');

-- Volcando estructura para tabla checkin_system.dias_evento
CREATE TABLE IF NOT EXISTS `dias_evento` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `fecha` date NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ej: Día 1 — Apertura',
  `orden` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dias_evento_fecha` (`evento_id`,`fecha`),
  KEY `idx_dias_evento` (`evento_id`),
  CONSTRAINT `fk_dias_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.dias_evento: ~3 rows (aproximadamente)
INSERT INTO `dias_evento` (`id`, `evento_id`, `fecha`, `nombre`, `orden`) VALUES
	(10, 1, '2026-03-16', 'Día 1 — Apertura e Innovación', 1),
	(11, 1, '2026-03-17', 'Día 2 — Transformación Digital', 2),
	(12, 1, '2026-03-18', 'Día 3 — Cierre y Networking', 3);

-- Volcando estructura para tabla checkin_system.email_log
CREATE TABLE IF NOT EXISTS `email_log` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `event_id` int DEFAULT NULL,
  `participant_id` int DEFAULT NULL,
  `to_email` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('SENT','FAILED') COLLATE utf8mb4_unicode_ci NOT NULL,
  `error_text` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mail_event` (`event_id`),
  KEY `idx_mail_part` (`participant_id`),
  CONSTRAINT `fk_mail_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_mail_part` FOREIGN KEY (`participant_id`) REFERENCES `participants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.email_log: ~0 rows (aproximadamente)

-- Volcando estructura para tabla checkin_system.estado_asistentes
CREATE TABLE IF NOT EXISTS `estado_asistentes` (
  `asistente_id` int NOT NULL,
  `salon_id` int NOT NULL,
  `estado` enum('dentro','fuera') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ultimo_movimiento_id` bigint DEFAULT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`asistente_id`,`salon_id`),
  KEY `idx_estado_salon` (`salon_id`),
  KEY `fk_estado_movimiento` (`ultimo_movimiento_id`),
  CONSTRAINT `fk_estado_asistente` FOREIGN KEY (`asistente_id`) REFERENCES `asistentes` (`id`),
  CONSTRAINT `fk_estado_movimiento` FOREIGN KEY (`ultimo_movimiento_id`) REFERENCES `movimientos` (`id`),
  CONSTRAINT `fk_estado_salon` FOREIGN KEY (`salon_id`) REFERENCES `salones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Verdad actual — reconstruible desde movimientos si el servidor se reinicia';

-- Volcando datos para la tabla checkin_system.estado_asistentes: ~1 rows (aproximadamente)
INSERT INTO `estado_asistentes` (`asistente_id`, `salon_id`, `estado`, `ultimo_movimiento_id`, `updated_at`) VALUES
	(21, 8, 'fuera', 53, '2026-03-11 21:40:03');

-- Volcando estructura para tabla checkin_system.eventos
CREATE TABLE IF NOT EXISTS `eventos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `config_json` json DEFAULT NULL COMMENT 'umbral_default, buffer_pre_charla, debounce_segundos, etc.',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_eventos_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.eventos: ~1 rows (aproximadamente)
INSERT INTO `eventos` (`id`, `nombre`, `slug`, `fecha_inicio`, `fecha_fin`, `activo`, `config_json`, `created_at`) VALUES
	(1, 'Tech Summit 2026', 'tech-summit-2026', '2026-03-16', '2026-03-18', 1, '{"umbral_default": 15, "buffer_pre_charla": 10, "debounce_segundos": 5}', '2026-02-24 22:02:21');

-- Volcando estructura para tabla checkin_system.events
CREATE TABLE IF NOT EXISTS `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `slug` varchar(96) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `budget` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reveal_date` date DEFAULT NULL,
  `illustration` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.events: ~1 rows (aproximadamente)
INSERT INTO `events` (`id`, `slug`, `name`, `budget`, `reveal_date`, `illustration`, `created_at`) VALUES
	(1, 'navidad2025', 'Amigo Secreto Navidad 2025', '$50.000 - $80.000', '2025-12-20', NULL, '2026-02-28 04:48:39');

-- Volcando estructura para tabla checkin_system.exclusions
CREATE TABLE IF NOT EXISTS `exclusions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `a_id` int NOT NULL,
  `b_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_ex` (`event_id`,`a_id`,`b_id`),
  KEY `idx_ex_event` (`event_id`),
  KEY `idx_ex_a` (`a_id`),
  KEY `idx_ex_b` (`b_id`),
  CONSTRAINT `fk_ex_a` FOREIGN KEY (`a_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ex_b` FOREIGN KEY (`b_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ex_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_ex_diff` CHECK ((`a_id` <> `b_id`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.exclusions: ~0 rows (aproximadamente)

-- Volcando estructura para tabla checkin_system.movimientos
CREATE TABLE IF NOT EXISTS `movimientos` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `asistente_id` int NOT NULL,
  `totem_id` int DEFAULT NULL COMMENT 'NULL cuando metodo = manual sin tótem asignado',
  `salon_id` int NOT NULL COMMENT 'redundante — optimiza reportes sin JOIN',
  `tipo` enum('checkin','checkout') COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` datetime(3) NOT NULL COMMENT 'timestamp del SERVIDOR — fuente de verdad, inmutable',
  `timestamp_totem` datetime(3) DEFAULT NULL COMMENT 'timestamp del dispositivo — solo referencia',
  `metodo` enum('qr_lector','manual','auto_cambio_salon','auto_fin_jornada','auto_fin_charla') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'qr_lector',
  `operador_id` int DEFAULT NULL COMMENT 'solo cuando metodo = manual',
  `flags` set('fuera_horario','checkout_sin_checkin','cambio_salon','inferido','corregido','cola_offline') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notas` text COLLATE utf8mb4_unicode_ci COMMENT 'solo para correcciones manuales',
  PRIMARY KEY (`id`),
  KEY `idx_mov_asistente_salon_ts` (`asistente_id`,`salon_id`,`timestamp`) COMMENT 'para toggle y cálculos',
  KEY `idx_mov_evento_ts` (`evento_id`,`timestamp`) COMMENT 'para reportes por evento',
  KEY `idx_mov_totem_ts` (`totem_id`,`timestamp`) COMMENT 'para diagnóstico por dispositivo',
  KEY `fk_mov_salon` (`salon_id`),
  KEY `fk_mov_operador` (`operador_id`),
  CONSTRAINT `fk_mov_asistente` FOREIGN KEY (`asistente_id`) REFERENCES `asistentes` (`id`),
  CONSTRAINT `fk_mov_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`),
  CONSTRAINT `fk_mov_operador` FOREIGN KEY (`operador_id`) REFERENCES `operadores` (`id`),
  CONSTRAINT `fk_mov_salon` FOREIGN KEY (`salon_id`) REFERENCES `salones` (`id`),
  CONSTRAINT `fk_mov_totem` FOREIGN KEY (`totem_id`) REFERENCES `totems` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.movimientos: ~2 rows (aproximadamente)
INSERT INTO `movimientos` (`id`, `evento_id`, `asistente_id`, `totem_id`, `salon_id`, `tipo`, `timestamp`, `timestamp_totem`, `metodo`, `operador_id`, `flags`, `notas`) VALUES
	(52, 1, 21, 6, 8, 'checkin', '2026-03-11 16:39:00.824', '2026-03-11 21:39:00.000', 'qr_lector', NULL, 'fuera_horario', NULL),
	(53, 1, 21, 6, 8, 'checkout', '2026-03-11 16:40:03.160', '2026-03-11 21:40:03.000', 'qr_lector', NULL, 'fuera_horario', NULL);

-- Volcando estructura para tabla checkin_system.operadores
CREATE TABLE IF NOT EXISTS `operadores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'hash bcrypt',
  `rol` enum('admin','supervisor','viewer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'supervisor',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_operadores_evento` (`evento_id`),
  CONSTRAINT `fk_operadores_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.operadores: ~2 rows (aproximadamente)
INSERT INTO `operadores` (`id`, `evento_id`, `nombre`, `pin`, `rol`, `activo`) VALUES
	(3, 1, 'Admin Demo', '$2y$10$5uYh689vGRQPuicA/y57Qey1OnNJs7KlC4cowCVJvwaLPeoNqiw0G', 'admin', 1),
	(4, 1, 'Supervisor Demo', '$2y$10$ydWbSftmXEVe1yBFiptD5.tY.ZEUDYB1YT17UXjQQhUuRgQXkh21q', 'viewer', 1);

-- Volcando estructura para tabla checkin_system.pairs
CREATE TABLE IF NOT EXISTS `pairs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `giver_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `portal_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `viewed_at` datetime DEFAULT NULL,
  `view_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_pair_giver` (`event_id`,`giver_id`),
  UNIQUE KEY `uniq_pair_receiver` (`event_id`,`receiver_id`),
  UNIQUE KEY `portal_token` (`portal_token`),
  KEY `idx_pairs_event` (`event_id`),
  KEY `idx_pairs_receiver` (`receiver_id`),
  KEY `idx_pairs_giver` (`giver_id`),
  CONSTRAINT `fk_pair_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pair_giver` FOREIGN KEY (`giver_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pair_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `participants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_giver_receiver` CHECK ((`giver_id` <> `receiver_id`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.pairs: ~0 rows (aproximadamente)

-- Volcando estructura para tabla checkin_system.participants
CREATE TABLE IF NOT EXISTS `participants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `full_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wishlist` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_event_email` (`event_id`,`email`),
  KEY `idx_part_event` (`event_id`),
  KEY `idx_part_email` (`email`),
  CONSTRAINT `fk_part_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.participants: ~0 rows (aproximadamente)

-- Volcando estructura para tabla checkin_system.salones
CREATE TABLE IF NOT EXISTS `salones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacidad` int DEFAULT NULL COMMENT 'NULL = sin límite',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_salones_evento` (`evento_id`),
  CONSTRAINT `fk_salones_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.salones: ~4 rows (aproximadamente)
INSERT INTO `salones` (`id`, `evento_id`, `nombre`, `capacidad`, `activo`) VALUES
	(8, 1, 'Auditorio Principal', 400, 1),
	(9, 1, 'Sala Innovación', 150, 1),
	(10, 1, 'Sala Networking', 80, 1),
	(11, 1, 'Workshop A', 40, 1);

-- Volcando estructura para tabla checkin_system.sync_log
CREATE TABLE IF NOT EXISTS `sync_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `fuente` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'eventbrite | csv | json | manual',
  `tipo` enum('asistentes','agenda','completo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `registros` int NOT NULL DEFAULT '0',
  `errores` int NOT NULL DEFAULT '0',
  `log_json` json DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sync_log_evento` (`evento_id`),
  CONSTRAINT `fk_sync_log_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.sync_log: ~0 rows (aproximadamente)

-- Volcando estructura para tabla checkin_system.totems
CREATE TABLE IF NOT EXISTS `totems` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `salon_id` int NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ej: Salón A - Entrada',
  `tipo` enum('entrada','salida','bidireccional') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bidireccional',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `ip_local` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'para diagnóstico en red local',
  `ultimo_ping` datetime(3) DEFAULT NULL COMMENT 'Última vez que el tótem hizo ping a /api/ping — NULL si nunca ha pingado',
  PRIMARY KEY (`id`),
  KEY `idx_totems_evento` (`evento_id`),
  KEY `idx_totems_salon` (`salon_id`),
  CONSTRAINT `fk_totems_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`),
  CONSTRAINT `fk_totems_salon` FOREIGN KEY (`salon_id`) REFERENCES `salones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.totems: ~4 rows (aproximadamente)
INSERT INTO `totems` (`id`, `evento_id`, `salon_id`, `nombre`, `tipo`, `activo`, `ip_local`, `ultimo_ping`) VALUES
	(6, 1, 8, 'Auditorio — Check-in', 'bidireccional', 1, '192.168.1.101', '2026-03-11 16:39:55.443'),
	(7, 1, 9, 'Sala Innovación — Check-in', 'bidireccional', 1, '192.168.1.102', NULL),
	(8, 1, 10, 'Sala Networking — Check-in', 'bidireccional', 1, '192.168.1.103', NULL),
	(9, 1, 11, 'Workshop A — Check-in', 'bidireccional', 1, '192.168.1.104', NULL);

-- Volcando estructura para vista checkin_system.v_charla_activa
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_charla_activa` (
	`id` INT NOT NULL,
	`salon_id` INT NOT NULL,
	`titulo` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`ponente` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`hora_inicio` DATETIME NOT NULL COMMENT 'se actualiza si hay cambios de agenda',
	`hora_fin` DATETIME NOT NULL COMMENT 'se actualiza si hay cambios de agenda',
	`umbral_min_asistencia` INT NOT NULL COMMENT 'minutos mínimos para contar asistencia',
	`buffer_pre_inicio` INT NOT NULL COMMENT 'minutos antes del inicio que cuentan para esta charla',
	`evento_id` INT NOT NULL,
	`dia_fecha` DATE NOT NULL
) ENGINE=MyISAM;

-- Volcando estructura para vista checkin_system.v_personas_en_salon
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_personas_en_salon` (
	`salon_id` INT NOT NULL,
	`salon_nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`personas_dentro` BIGINT NOT NULL
) ENGINE=MyISAM;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_charla_activa`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_charla_activa` AS select `c`.`id` AS `id`,`c`.`salon_id` AS `salon_id`,`c`.`titulo` AS `titulo`,`c`.`ponente` AS `ponente`,`c`.`hora_inicio` AS `hora_inicio`,`c`.`hora_fin` AS `hora_fin`,`c`.`umbral_min_asistencia` AS `umbral_min_asistencia`,`c`.`buffer_pre_inicio` AS `buffer_pre_inicio`,`d`.`evento_id` AS `evento_id`,`d`.`fecha` AS `dia_fecha` from (`charlas` `c` join `dias_evento` `d` on((`d`.`id` = `c`.`dia_evento_id`))) where ((`c`.`cancelada` = 0) and (`c`.`hora_inicio` <= now()) and (`c`.`hora_fin` >= now()));

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_personas_en_salon`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_personas_en_salon` AS select `ea`.`salon_id` AS `salon_id`,`s`.`nombre` AS `salon_nombre`,count(0) AS `personas_dentro` from (`estado_asistentes` `ea` join `salones` `s` on((`s`.`id` = `ea`.`salon_id`))) where (`ea`.`estado` = 'dentro') group by `ea`.`salon_id`,`s`.`nombre`;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
