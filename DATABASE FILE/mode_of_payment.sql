-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table avthardwaretrading.mode_of_payment
CREATE TABLE IF NOT EXISTS `mode_of_payment` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `Term` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `description` text,
  `is_active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table avthardwaretrading.mode_of_payment: ~5 rows (approximately)
DELETE FROM `mode_of_payment`;
INSERT INTO `mode_of_payment` (`id`, `name`, `Term`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'Cash', NULL, 'Walk-in over-the-counter', 1, '2025-07-29 02:35:28', '2025-07-29 19:00:52'),
	(2, 'PDC/Check', NULL, 'Post-dated or regular checks', 1, '2025-07-29 02:45:23', '2025-07-29 02:45:23'),
	(3, 'GCash', NULL, 'GCash transfer payments', 1, '2025-07-29 02:46:06', '2025-07-29 02:46:06'),
	(5, 'Online Bank', NULL, 'Online banking transactions', 1, '2025-07-29 18:38:14', '2025-07-29 18:38:14'),
	(6, 'PDC/Check', NULL, 'Post-dated or regular checks', 1, '2025-07-29 18:54:42', '2025-07-29 18:54:42');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
