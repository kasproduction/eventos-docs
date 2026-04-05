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
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.agenda_cambios: ~37 rows (aproximadamente)
INSERT INTO `agenda_cambios` (`id`, `charla_id`, `campo_modificado`, `valor_anterior`, `valor_nuevo`, `motivo`, `operador_id`, `timestamp`, `recalculo_requerido`) VALUES
	(1, 1, 'hora_inicio', '2026-02-24 08:00:00', '2026-02-24 08:15:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:08', 0),
	(2, 1, 'hora_fin', '2026-02-24 09:00:00', '2026-02-24 09:15:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:08', 0),
	(3, 2, 'hora_inicio', '2026-02-24 09:00:00', '2026-02-24 09:15:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:08', 0),
	(4, 2, 'hora_fin', '2026-02-24 10:00:00', '2026-02-24 10:15:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:08', 0),
	(5, 3, 'hora_inicio', '2026-02-24 10:30:00', '2026-02-24 10:45:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:08', 0),
	(6, 3, 'hora_fin', '2026-02-24 11:30:00', '2026-02-24 11:45:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:08', 0),
	(7, 4, 'hora_inicio', '2026-02-24 14:00:00', '2026-02-24 14:15:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:08', 0),
	(8, 4, 'hora_fin', '2026-02-24 15:00:00', '2026-02-24 15:15:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:08', 0),
	(9, 5, 'hora_inicio', '2026-02-24 17:00:00', '2026-02-24 17:15:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:08', 0),
	(10, 5, 'hora_fin', '2026-02-24 18:00:00', '2026-02-24 18:15:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:08', 0),
	(11, 1, 'hora_inicio', '2026-02-24 08:15:00', '2026-02-24 08:30:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:20', 0),
	(12, 1, 'hora_fin', '2026-02-24 09:15:00', '2026-02-24 09:30:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:20', 0),
	(13, 2, 'hora_inicio', '2026-02-24 09:15:00', '2026-02-24 09:30:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:20', 0),
	(14, 2, 'hora_fin', '2026-02-24 10:15:00', '2026-02-24 10:30:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:20', 0),
	(15, 3, 'hora_inicio', '2026-02-24 10:45:00', '2026-02-24 11:00:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:20', 0),
	(16, 3, 'hora_fin', '2026-02-24 11:45:00', '2026-02-24 12:00:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:20', 0),
	(17, 4, 'hora_inicio', '2026-02-24 14:15:00', '2026-02-24 14:30:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:20', 0),
	(18, 4, 'hora_fin', '2026-02-24 15:15:00', '2026-02-24 15:30:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:20', 0),
	(19, 5, 'hora_inicio', '2026-02-24 17:15:00', '2026-02-24 17:30:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:20', 0),
	(20, 5, 'hora_fin', '2026-02-24 18:15:00', '2026-02-24 18:30:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-24 23:22:20', 0),
	(21, 9, 'titulo', 'Charla de prueba', 'Reunion meeting', '', 1, '2026-02-25 17:28:43', 1),
	(22, 9, 'hora_fin', '2026-02-25 12:30:00', '2026-02-25 13:30:00', '', 1, '2026-02-25 17:34:48', 1),
	(23, 9, 'titulo', 'Reunion meeting', 'Reunion meeting Cisco', '', 1, '2026-02-25 17:43:32', 1),
	(24, 10, 'hora_inicio', '2026-02-25 13:00:00', '2026-02-25 13:40:00', '', 1, '2026-02-25 17:53:43', 0),
	(25, 9, 'cancelada', '0', '1', '', 1, '2026-02-25 17:54:28', 1),
	(26, 10, 'hora_fin', '2026-02-25 14:00:00', '2026-02-25 17:00:00', '', 1, '2026-02-25 19:42:02', 0),
	(27, 11, 'titulo', 'test', 'PRUEBA DE REUNION JUANGANTA', '', 1, '2026-02-25 20:39:37', 0),
	(28, 11, 'titulo', 'PRUEBA DE REUNION JUANGANTA', 'PRUEBA DE REUNION ', '', 1, '2026-02-25 20:39:42', 0),
	(29, 11, 'ponente', '', 'JUANGANTA', '', 1, '2026-02-25 20:39:42', 0),
	(30, 13, 'hora_inicio', '2026-02-26 04:00:00', '2026-02-26 05:00:00', '', 1, '2026-02-26 08:55:52', 0),
	(31, 13, 'hora_fin', '2026-02-26 05:00:00', '2026-02-26 06:00:00', '', 1, '2026-02-26 08:55:52', 0),
	(32, 12, 'salon_id', '1', '2', '', 1, '2026-02-26 08:56:19', 0),
	(33, 13, 'salon_id', '1', '2', '', 1, '2026-02-26 08:56:25', 0),
	(34, 12, 'salon_id', '2', '3', '', 1, '2026-02-26 08:56:53', 0),
	(35, 13, 'salon_id', '2', '3', '', 1, '2026-02-26 08:56:57', 0),
	(36, 12, 'salon_id', '3', '2', '', 1, '2026-02-26 08:57:32', 0),
	(37, 13, 'salon_id', '3', '2', '', 1, '2026-02-26 08:57:37', 0),
	(38, 12, 'salon_id', '2', '1', '', 1, '2026-02-26 09:25:07', 1),
	(39, 13, 'salon_id', '2', '1', '', 1, '2026-02-26 09:25:12', 1),
	(42, 13, 'hora_fin', '2026-02-26 06:00:00', '2026-02-26 05:30:00', '', 1, '2026-02-26 10:09:26', 1),
	(43, 13, 'hora_fin', '2026-02-26 05:30:00', '2026-02-26 06:30:00', '', 1, '2026-02-26 10:13:54', 1),
	(44, 13, 'hora_fin', '2026-02-26 06:30:00', '2026-02-26 07:30:00', '', 1, '2026-02-26 10:19:02', 1),
	(45, 13, 'ponente', 'Kas', 'Kaslo', '', 1, '2026-02-26 10:19:02', 1),
	(46, 13, 'hora_fin', '2026-02-26 07:30:00', '2026-02-26 06:30:00', '', 1, '2026-02-26 10:19:34', 1),
	(47, 13, 'titulo', 'Prueba 2', 'Hablemos de los Therians', '', 1, '2026-02-26 10:20:29', 1),
	(48, 13, 'ponente', 'Kaslo', 'Pedro el mas bueno', '', 1, '2026-02-26 10:20:29', 1),
	(49, 12, 'hora_inicio', '2026-02-26 04:00:00', '2026-02-26 04:15:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-26 10:29:03', 1),
	(50, 12, 'hora_fin', '2026-02-26 05:00:00', '2026-02-26 05:15:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-26 10:29:03', 1),
	(51, 13, 'hora_inicio', '2026-02-26 05:00:00', '2026-02-26 05:15:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-26 10:29:03', 1),
	(52, 13, 'hora_fin', '2026-02-26 06:30:00', '2026-02-26 06:45:00', 'Retraso masivo 15 min — salón 1', 1, '2026-02-26 10:29:03', 1),
	(53, 16, 'hora_inicio', '2026-02-26 06:30:00', '2026-02-26 06:10:00', '', 1, '2026-02-26 11:09:14', 1),
	(55, 20, 'salon_id', '1', '4', '', 1, '2026-02-26 15:54:21', 1),
	(56, 12, 'hora_inicio', '2026-02-26 04:15:00', '2026-02-26 04:25:00', 'Retraso masivo 10 min — salón 1', 1, '2026-02-26 16:43:19', 1),
	(57, 12, 'hora_fin', '2026-02-26 05:15:00', '2026-02-26 05:25:00', 'Retraso masivo 10 min — salón 1', 1, '2026-02-26 16:43:19', 1),
	(58, 13, 'hora_inicio', '2026-02-26 05:15:00', '2026-02-26 05:25:00', 'Retraso masivo 10 min — salón 1', 1, '2026-02-26 16:43:19', 1),
	(59, 13, 'hora_fin', '2026-02-26 06:45:00', '2026-02-26 06:55:00', 'Retraso masivo 10 min — salón 1', 1, '2026-02-26 16:43:19', 1),
	(60, 15, 'hora_inicio', '2026-02-26 06:45:00', '2026-02-26 06:55:00', 'Retraso masivo 10 min — salón 1', 1, '2026-02-26 16:43:19', 1),
	(61, 15, 'hora_fin', '2026-02-26 07:00:00', '2026-02-26 07:10:00', 'Retraso masivo 10 min — salón 1', 1, '2026-02-26 16:43:19', 1),
	(62, 18, 'hora_inicio', '2026-02-26 11:00:00', '2026-02-26 11:10:00', 'Retraso masivo 10 min — salón 1', 1, '2026-02-26 16:43:19', 1),
	(63, 18, 'hora_fin', '2026-02-26 12:00:00', '2026-02-26 12:10:00', 'Retraso masivo 10 min — salón 1', 1, '2026-02-26 16:43:19', 1),
	(64, 22, 'hora_inicio', '2026-02-26 12:00:00', '2026-02-26 12:10:00', 'Retraso masivo 10 min — salón 1', 1, '2026-02-26 16:43:19', 1),
	(65, 22, 'hora_fin', '2026-02-26 13:00:00', '2026-02-26 13:10:00', 'Retraso masivo 10 min — salón 1', 1, '2026-02-26 16:43:19', 1),
	(66, 12, 'hora_inicio', '2026-02-26 04:25:00', '2026-02-26 05:11:00', 'Retraso masivo 46 min — salón 1', 1, '2026-02-26 16:43:38', 1),
	(67, 12, 'hora_fin', '2026-02-26 05:25:00', '2026-02-26 06:11:00', 'Retraso masivo 46 min — salón 1', 1, '2026-02-26 16:43:38', 1),
	(68, 13, 'hora_inicio', '2026-02-26 05:25:00', '2026-02-26 06:11:00', 'Retraso masivo 46 min — salón 1', 1, '2026-02-26 16:43:38', 1),
	(69, 13, 'hora_fin', '2026-02-26 06:55:00', '2026-02-26 07:41:00', 'Retraso masivo 46 min — salón 1', 1, '2026-02-26 16:43:38', 1),
	(70, 15, 'hora_inicio', '2026-02-26 06:55:00', '2026-02-26 07:41:00', 'Retraso masivo 46 min — salón 1', 1, '2026-02-26 16:43:38', 1),
	(71, 15, 'hora_fin', '2026-02-26 07:10:00', '2026-02-26 07:56:00', 'Retraso masivo 46 min — salón 1', 1, '2026-02-26 16:43:38', 1),
	(72, 18, 'hora_inicio', '2026-02-26 11:10:00', '2026-02-26 11:56:00', 'Retraso masivo 46 min — salón 1', 1, '2026-02-26 16:43:38', 1),
	(73, 18, 'hora_fin', '2026-02-26 12:10:00', '2026-02-26 12:56:00', 'Retraso masivo 46 min — salón 1', 1, '2026-02-26 16:43:38', 1),
	(74, 22, 'hora_inicio', '2026-02-26 12:10:00', '2026-02-26 12:56:00', 'Retraso masivo 46 min — salón 1', 1, '2026-02-26 16:43:38', 1),
	(75, 22, 'hora_fin', '2026-02-26 13:10:00', '2026-02-26 13:56:00', 'Retraso masivo 46 min — salón 1', 1, '2026-02-26 16:43:38', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.agenda_pantallas: ~0 rows (aproximadamente)
INSERT INTO `agenda_pantallas` (`id`, `nombre`, `salon_id`, `modo`, `imagen_path`, `video_path`, `loop_video`, `video_fit`, `retorno_en`, `activa`) VALUES
	(1, 'ee', 1, 'agenda', NULL, NULL, 1, 'contain', NULL, 1),
	(2, '2', NULL, 'video', 'imagen.jpg', 'video.mp4', 1, 'contain', NULL, 1),
	(3, '3', 4, 'agenda', NULL, NULL, 1, 'contain', NULL, 1);

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
INSERT INTO `asistencia_calculada` (`id`, `evento_id`, `asistente_id`, `charla_id`, `dia_evento_id`, `checkin_real`, `checkout_real`, `minutos_presentes`, `cuenta_asistencia`, `calidad_dato`, `calculado_at`) VALUES
	(1, 1, 10, 10, 2, '2026-02-25 14:42:43.000', '2026-02-25 14:48:59.000', 5, 0, 'real', '2026-02-26 09:13:09'),
	(2, 1, 1, 11, 2, '2026-02-25 15:32:59.000', '2026-02-25 15:48:02.000', 10, 0, 'real', '2026-02-26 09:13:09'),
	(3, 1, 8, 11, 2, '2026-02-25 15:41:01.000', NULL, 48, 1, 'real', '2026-02-26 09:13:09'),
	(4, 1, 10, 11, 2, '2026-02-25 15:16:03.000', NULL, 73, 1, 'inferido', '2026-02-26 09:13:09');

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.asistentes: ~10 rows (aproximadamente)
INSERT INTO `asistentes` (`id`, `evento_id`, `uid_qr`, `nombre`, `email`, `empresa`, `external_id`, `fuente`, `metadata_json`, `activo`, `created_at`, `updated_at`) VALUES
	(1, 1, 'QR-TEST-001', 'Ana García', 'ana.garcia@demo.com', 'Demo Corp', '1001', 'manual', NULL, 1, '2026-02-24 22:02:21', '2026-02-24 22:02:21'),
	(2, 1, 'QR-TEST-002', 'Luis Martínez', 'luis.m@demo.com', 'Demo Corp', '1002', 'manual', NULL, 1, '2026-02-24 22:02:21', '2026-02-24 22:02:21'),
	(3, 1, 'QR-TEST-003', 'María López', 'maria.l@empresa.com', 'Empresa SA', '1003', 'manual', NULL, 1, '2026-02-24 22:02:21', '2026-02-24 22:02:21'),
	(4, 1, 'QR-TEST-004', 'Carlos Ruiz', 'carlos.r@empresa.com', 'Empresa SA', '1004', 'manual', NULL, 1, '2026-02-24 22:02:21', '2026-02-24 22:02:21'),
	(5, 1, 'QR-TEST-005', 'Sofía Torres', 'sofia.t@startup.io', 'Startup IO', '1005', 'manual', NULL, 1, '2026-02-24 22:02:21', '2026-02-24 22:02:21'),
	(6, 1, 'QR-TEST-006', 'Pedro Sánchez', 'pedro.s@startup.io', 'Startup IO', '1006', 'manual', NULL, 1, '2026-02-24 22:02:21', '2026-02-24 22:02:21'),
	(7, 1, 'QR-TEST-007', 'Laura Jiménez', 'laura.j@demo.com', 'Demo Corp', '1007', 'manual', NULL, 1, '2026-02-24 22:02:21', '2026-02-24 22:02:21'),
	(8, 1, 'QR-TEST-008', 'Diego Fernández', 'diego.f@empresa.com', 'Empresa SA', '1008', 'manual', NULL, 1, '2026-02-24 22:02:21', '2026-02-24 22:02:21'),
	(9, 1, 'QR-TEST-009', 'Valentina Cruz', 'vale.c@startup.io', 'Startup IO', '1009', 'manual', NULL, 1, '2026-02-24 22:02:21', '2026-02-24 22:02:21'),
	(10, 1, 'QR-TEST-010', 'Roberto Díaz', 'roberto.d@demo.com', 'Demo Corp', '1010', 'manual', NULL, 1, '2026-02-24 22:02:21', '2026-02-24 22:02:21');

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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.charlas: ~13 rows (aproximadamente)
INSERT INTO `charlas` (`id`, `dia_evento_id`, `salon_id`, `charla_padre_id`, `titulo`, `ponente`, `hora_inicio`, `hora_fin`, `hora_inicio_original`, `hora_fin_original`, `umbral_min_asistencia`, `buffer_pre_inicio`, `cancelada`, `orden_en_dia`) VALUES
	(1, 1, 1, NULL, 'Bienvenida e Inauguración', 'Dir. General', '2026-02-24 08:30:00', '2026-02-24 09:30:00', '2026-02-24 08:00:00', '2026-02-24 09:00:00', 15, 15, 0, 1),
	(2, 1, 1, NULL, 'Innovación en IA', 'Dr. García', '2026-02-24 09:30:00', '2026-02-24 10:30:00', '2026-02-24 09:00:00', '2026-02-24 10:00:00', 15, 15, 0, 2),
	(3, 1, 1, NULL, 'Transformación Digital', 'Ing. López', '2026-02-24 11:00:00', '2026-02-24 12:00:00', '2026-02-24 10:30:00', '2026-02-24 11:30:00', 15, 15, 0, 3),
	(4, 1, 1, NULL, 'Panel: Futuro del Trabajo', NULL, '2026-02-24 14:30:00', '2026-02-24 15:30:00', '2026-02-24 14:00:00', '2026-02-24 15:00:00', 15, 15, 0, 4),
	(5, 1, 1, NULL, 'Cierre Día 1', 'Dir. General', '2026-02-24 17:30:00', '2026-02-24 18:30:00', '2026-02-24 17:00:00', '2026-02-24 18:00:00', 15, 15, 0, 5),
	(6, 1, 2, NULL, 'Workshop: Liderazgo Ágil', 'Lic. Pérez', '2026-02-24 09:00:00', '2026-02-24 11:00:00', '2026-02-24 09:00:00', '2026-02-24 11:00:00', 15, 15, 0, 1),
	(7, 1, 2, NULL, 'Taller: Design Thinking', 'Arq. Martínez', '2026-02-24 14:00:00', '2026-02-24 16:00:00', '2026-02-24 14:00:00', '2026-02-24 16:00:00', 15, 15, 0, 2),
	(8, 2, 3, NULL, 'Prueba', NULL, '2026-02-24 23:30:00', '2026-02-25 00:30:00', '2026-02-24 23:30:00', '2026-02-25 00:30:00', 15, 15, 0, 1),
	(9, 2, 1, NULL, 'Reunion meeting Cisco', 'Kasproduction', '2026-02-25 11:30:00', '2026-02-25 13:30:00', '2026-02-25 11:30:00', '2026-02-25 12:30:00', 15, 15, 1, 1),
	(10, 2, 1, NULL, 'Reunion siguiente', NULL, '2026-02-25 13:40:00', '2026-02-25 17:00:00', '2026-02-25 13:00:00', '2026-02-25 14:00:00', 15, 15, 0, 2),
	(11, 2, 2, NULL, 'PRUEBA DE REUNION ', 'JUANGANTA', '2026-02-25 15:30:00', '2026-02-25 16:30:00', '2026-02-25 15:30:00', '2026-02-25 16:30:00', 15, 15, 0, 1),
	(12, 3, 1, NULL, 'Prueba', 'kas', '2026-02-26 05:11:00', '2026-02-26 06:11:00', '2026-02-26 04:00:00', '2026-02-26 05:00:00', 15, 15, 0, 1),
	(13, 3, 1, NULL, 'Hablemos de los Therians', 'Pedro el mas bueno', '2026-02-26 06:11:00', '2026-02-26 07:41:00', '2026-02-26 04:00:00', '2026-02-26 05:00:00', 15, 15, 0, 2),
	(15, 3, 1, NULL, 'Hablemos de los hijos normales', 'Kasproduction', '2026-02-26 07:41:00', '2026-02-26 07:56:00', '2026-02-26 06:45:00', '2026-02-26 07:00:00', 15, 15, 0, 3),
	(16, 3, 2, NULL, 'Los gatos', NULL, '2026-02-26 06:10:00', '2026-02-26 07:30:00', '2026-02-26 06:30:00', '2026-02-26 07:30:00', 15, 15, 0, 1),
	(18, 3, 1, NULL, 'charla1', NULL, '2026-02-26 11:56:00', '2026-02-26 12:56:00', '2026-02-26 11:00:00', '2026-02-26 12:00:00', 15, 15, 0, 4),
	(20, 3, 4, NULL, 'charla 3', NULL, '2026-02-26 11:00:00', '2026-02-26 12:00:00', '2026-02-26 11:00:00', '2026-02-26 12:00:00', 15, 15, 0, 6),
	(21, 3, 2, NULL, 'charla 4', NULL, '2026-02-26 11:00:00', '2026-02-26 12:00:00', '2026-02-26 11:00:00', '2026-02-26 12:00:00', 15, 15, 0, 2),
	(22, 3, 1, NULL, 'La prueba', 'Shieru henzeiy', '2026-02-26 12:56:00', '2026-02-26 13:56:00', '2026-02-26 12:00:00', '2026-02-26 13:00:00', 15, 15, 0, 5);

-- Volcando estructura para tabla checkin_system.configuracion
CREATE TABLE IF NOT EXISTS `configuracion` (
  `clave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.configuracion: ~1 rows (aproximadamente)
INSERT INTO `configuracion` (`clave`, `valor`) VALUES
	('agenda_override', 'none'),
	('agenda_override_imagen', NULL),
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.dias_evento: ~4 rows (aproximadamente)
INSERT INTO `dias_evento` (`id`, `evento_id`, `fecha`, `nombre`, `orden`) VALUES
	(1, 1, '2026-02-24', 'Día 1 — Apertura', 1),
	(2, 1, '2026-02-25', 'Día 2', 2),
	(3, 1, '2026-02-26', 'Día 3', 3);

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

-- Volcando datos para la tabla checkin_system.estado_asistentes: ~4 rows (aproximadamente)
INSERT INTO `estado_asistentes` (`asistente_id`, `salon_id`, `estado`, `ultimo_movimiento_id`, `updated_at`) VALUES
	(1, 2, 'fuera', 41, '2026-02-25 20:48:02'),
	(8, 2, 'dentro', 40, '2026-02-25 20:41:37'),
	(10, 1, 'fuera', 48, '2026-02-26 16:04:46'),
	(10, 2, 'fuera', 42, '2026-02-26 09:34:56');

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

-- Volcando datos para la tabla checkin_system.eventos: ~0 rows (aproximadamente)
INSERT INTO `eventos` (`id`, `nombre`, `slug`, `fecha_inicio`, `fecha_fin`, `activo`, `config_json`, `created_at`) VALUES
	(1, 'Evento Demo 2025', 'evento-demo-2025', '2026-02-24', '2026-02-27', 1, '{"umbral_default": 15, "buffer_pre_charla": 15, "debounce_segundos": 5}', '2026-02-24 22:02:21');

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
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.movimientos: ~29 rows (aproximadamente)
INSERT INTO `movimientos` (`id`, `evento_id`, `asistente_id`, `totem_id`, `salon_id`, `tipo`, `timestamp`, `timestamp_totem`, `metodo`, `operador_id`, `flags`, `notas`) VALUES
	(10, 1, 10, 1, 1, 'checkin', '2026-02-25 12:24:15.247', '2026-02-25 17:24:15.000', 'qr_lector', NULL, NULL, NULL),
	(11, 1, 10, 1, 1, 'checkout', '2026-02-25 12:24:51.285', '2026-02-25 17:24:51.000', 'qr_lector', NULL, NULL, NULL),
	(12, 1, 10, 1, 1, 'checkin', '2026-02-25 12:25:38.093', '2026-02-25 17:25:38.000', 'qr_lector', NULL, NULL, NULL),
	(13, 1, 10, 1, 1, 'checkout', '2026-02-25 12:26:03.138', '2026-02-25 17:26:03.000', 'qr_lector', NULL, NULL, NULL),
	(14, 1, 10, 1, 1, 'checkin', '2026-02-25 12:27:36.885', '2026-02-25 17:27:36.000', 'qr_lector', NULL, NULL, NULL),
	(15, 1, 10, 1, 1, 'checkout', '2026-02-25 12:27:53.096', '2026-02-25 17:27:53.000', 'qr_lector', NULL, NULL, NULL),
	(16, 1, 10, 1, 1, 'checkin', '2026-02-25 12:44:53.169', '2026-02-25 17:44:53.000', 'qr_lector', NULL, NULL, NULL),
	(17, 1, 10, 1, 1, 'checkout', '2026-02-25 12:55:32.527', '2026-02-25 17:55:32.000', 'qr_lector', NULL, 'fuera_horario', NULL),
	(18, 1, 10, 1, 1, 'checkin', '2026-02-25 14:42:43.751', '2026-02-25 19:42:43.000', 'qr_lector', NULL, NULL, NULL),
	(19, 1, 10, 1, 1, 'checkout', '2026-02-25 14:43:57.012', '2026-02-25 19:43:57.000', 'qr_lector', NULL, NULL, NULL),
	(20, 1, 10, 1, 1, 'checkin', '2026-02-25 14:44:26.665', '2026-02-25 19:44:26.000', 'qr_lector', NULL, NULL, NULL),
	(21, 1, 10, 2, 1, 'checkout', '2026-02-25 14:48:59.040', '2026-02-25 19:48:58.000', 'qr_lector', NULL, NULL, NULL),
	(22, 1, 10, 3, 2, 'checkin', '2026-02-25 15:01:58.017', '2026-02-25 20:01:57.000', 'qr_lector', NULL, 'fuera_horario', NULL),
	(23, 1, 10, 3, 2, 'checkout', '2026-02-25 15:13:16.651', '2026-02-25 20:13:16.000', 'qr_lector', NULL, 'fuera_horario', NULL),
	(24, 1, 10, 3, 2, 'checkin', '2026-02-25 15:13:23.386', '2026-02-25 20:13:23.000', 'qr_lector', NULL, 'fuera_horario', NULL),
	(25, 1, 10, 3, 2, 'checkout', '2026-02-25 15:13:29.215', '2026-02-25 20:13:29.000', 'qr_lector', NULL, 'fuera_horario', NULL),
	(26, 1, 10, 3, 2, 'checkin', '2026-02-25 15:16:03.584', '2026-02-25 20:16:03.000', 'qr_lector', NULL, 'fuera_horario', NULL),
	(27, 1, 1, 3, 2, 'checkin', '2026-02-25 15:32:59.259', '2026-02-25 20:32:59.000', 'qr_lector', NULL, NULL, NULL),
	(28, 1, 1, 3, 2, 'checkout', '2026-02-25 15:33:39.901', '2026-02-25 20:33:39.000', 'qr_lector', NULL, NULL, NULL),
	(29, 1, 1, 3, 2, 'checkin', '2026-02-25 15:33:46.806', '2026-02-25 20:33:46.000', 'qr_lector', NULL, NULL, NULL),
	(30, 1, 1, 3, 2, 'checkout', '2026-02-25 15:34:45.907', '2026-02-25 20:34:45.000', 'qr_lector', NULL, NULL, NULL),
	(31, 1, 1, 3, 2, 'checkin', '2026-02-25 15:36:08.411', '2026-02-25 20:36:08.000', 'qr_lector', NULL, NULL, NULL),
	(32, 1, 1, 3, 2, 'checkout', '2026-02-25 15:36:15.147', '2026-02-25 20:36:15.000', 'qr_lector', NULL, NULL, NULL),
	(33, 1, 1, 3, 2, 'checkin', '2026-02-25 15:36:22.965', '2026-02-25 20:36:22.000', 'qr_lector', NULL, NULL, NULL),
	(34, 1, 1, 3, 2, 'checkout', '2026-02-25 15:36:26.385', '2026-02-25 20:36:26.000', 'qr_lector', NULL, NULL, NULL),
	(35, 1, 1, 3, 2, 'checkin', '2026-02-25 15:36:30.349', '2026-02-25 20:36:30.000', 'qr_lector', NULL, NULL, NULL),
	(36, 1, 1, 3, 2, 'checkout', '2026-02-25 15:37:51.475', '2026-02-25 20:37:51.000', 'qr_lector', NULL, NULL, NULL),
	(37, 1, 1, 3, 2, 'checkin', '2026-02-25 15:38:09.002', '2026-02-25 20:38:08.000', 'qr_lector', NULL, NULL, NULL),
	(38, 1, 8, 3, 2, 'checkin', '2026-02-25 15:41:01.559', '2026-02-25 20:41:01.000', 'qr_lector', NULL, NULL, NULL),
	(39, 1, 8, 3, 2, 'checkout', '2026-02-25 15:41:26.499', '2026-02-25 20:41:26.000', 'qr_lector', NULL, NULL, NULL),
	(40, 1, 8, 3, 2, 'checkin', '2026-02-25 15:41:36.616', '2026-02-25 20:41:36.000', 'qr_lector', NULL, NULL, NULL),
	(41, 1, 1, 3, 2, 'checkout', '2026-02-25 15:48:02.056', '2026-02-25 20:48:02.000', 'qr_lector', NULL, NULL, NULL),
	(42, 1, 10, 3, 2, 'checkout', '2026-02-26 04:34:55.689', NULL, 'auto_cambio_salon', NULL, 'cambio_salon', NULL),
	(43, 1, 10, 1, 1, 'checkin', '2026-02-26 04:34:55.692', '2026-02-26 09:34:55.000', 'qr_lector', NULL, NULL, NULL),
	(44, 1, 10, 1, 1, 'checkout', '2026-02-26 04:42:55.282', '2026-02-26 09:42:55.000', 'qr_lector', NULL, NULL, NULL),
	(45, 1, 10, 1, 1, 'checkin', '2026-02-26 04:43:09.759', '2026-02-26 09:43:09.000', 'qr_lector', NULL, NULL, NULL),
	(46, 1, 10, 1, 1, 'checkout', '2026-02-26 05:06:51.720', '2026-02-26 10:06:51.000', 'qr_lector', NULL, NULL, NULL),
	(47, 1, 10, 1, 1, 'checkin', '2026-02-26 06:21:23.601', '2026-02-26 11:21:23.000', 'qr_lector', NULL, NULL, NULL),
	(48, 1, 10, 1, 1, 'checkout', '2026-02-26 11:04:46.289', '2026-02-26 16:04:46.000', 'qr_lector', NULL, NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.operadores: ~1 rows (aproximadamente)
INSERT INTO `operadores` (`id`, `evento_id`, `nombre`, `pin`, `rol`, `activo`) VALUES
	(1, 1, 'Admin', '$2y$10$8RipWgdbl3OInDho7lneO.kXYI.0uUZ5l0RUB6qOJbZaWyEsil.ou', 'admin', 1),
	(2, 1, 'pedro', '$2y$10$uJKSuJKTTEDO7l5cn41C/.GIFumH.FF.z21YmVKK8WCdwlpa91b2O', 'viewer', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.salones: ~2 rows (aproximadamente)
INSERT INTO `salones` (`id`, `evento_id`, `nombre`, `capacidad`, `activo`) VALUES
	(1, 1, 'Salón A', 500, 1),
	(2, 1, 'Salón B', 300, 1),
	(3, 1, 'Taller', NULL, 0),
	(4, 1, 'Salon c', NULL, 1);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla checkin_system.totems: ~5 rows (aproximadamente)
INSERT INTO `totems` (`id`, `evento_id`, `salon_id`, `nombre`, `tipo`, `activo`, `ip_local`, `ultimo_ping`) VALUES
	(1, 1, 1, 'Salón A — Entrada', 'bidireccional', 1, '192.168.1.101', '2026-02-26 12:18:08.340'),
	(2, 1, 1, 'Salón A — Salida', 'bidireccional', 1, '192.168.1.102', NULL),
	(3, 1, 2, 'Salón B — Entrada', 'entrada', 1, '192.168.1.103', '2026-02-26 04:24:18.192'),
	(4, 1, 2, 'Salón B — Salida', 'salida', 1, '192.168.1.104', NULL),
	(5, 1, 3, '1', 'bidireccional', 1, NULL, NULL);

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
