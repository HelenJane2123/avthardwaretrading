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

-- Dumping data for table avthardwaretrading.categories: ~6 rows (approximately)
DELETE FROM `categories`;
INSERT INTO `categories` (`id`, `name`, `slug`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'Test Category', 'test-category', 1, '2023-12-08 18:44:35', '2023-12-08 18:44:35'),
	(2, 'UPDTD Category', 'updtd-category', 1, '2023-12-12 16:29:23', '2023-12-12 16:29:40'),
	(3, 'Demo Category', 'demo-category', 1, '2023-12-13 00:10:07', '2023-12-13 00:10:07'),
	(4, 'Tools', 'tools', 1, '2025-07-27 18:23:25', '2025-07-27 18:23:25'),
	(5, 'Construction', 'construction', 1, '2025-07-27 18:23:31', '2025-07-27 18:23:31'),
	(6, 'Lock Set', 'lock-set', 1, '2025-07-27 18:25:25', '2025-07-27 18:25:25');

-- Dumping data for table avthardwaretrading.customers: ~7 rows (approximately)
DELETE FROM `customers`;
INSERT INTO `customers` (`id`, `customer_code`, `name`, `mobile`, `address`, `email`, `tax`, `details`, `previous_balance`, `created_at`, `updated_at`) VALUES
	(1, NULL, 'Customer A', '87777777777', '77 Demo Address', 'customera@mail.com', '123-456-897-000', 'qwertyu', '111', '2023-12-08 18:46:55', '2025-07-21 04:30:15'),
	(2, NULL, 'Customer B', '11111111110', '778 Demo Test', 'customerb@mail.com', '456-789-123-000', 'demo demo', '1500000', '2023-12-11 14:22:50', '2025-07-21 04:31:08'),
	(4, NULL, 'Demo Customer', '77777777777', '777 Demo', 'demo@customer.com', NULL, 'asdsadasdasd', '111', '2023-12-12 14:49:05', '2023-12-12 14:49:05'),
	(5, NULL, 'Product Supplier Hardware', '87777777777', 'test Address', 'test@gmail.com', '123456789067', 'test details', '1000000', '2025-07-21 02:45:53', '2025-07-21 03:52:47'),
	(6, NULL, 'Jen Manalo', '87777777777', 'test', 'admin123@mail.com', '1234567890', 'testing', '1000000', '2025-07-21 03:50:50', '2025-07-21 03:52:25'),
	(7, NULL, 'Jen Manalo ABCD', '09696203783', 'test', 'helenjanemanalo@gmail.com', '123-456-789', 'test', '5000000', '2025-07-21 04:22:16', '2025-07-21 04:22:16'),
	(8, NULL, 'Jen Manalo FCDSH', '09696213783', 'test', 'admin0001@mail.com', '124-345-891', 'test', '5000000', '2025-07-21 04:25:12', '2025-07-21 04:25:12');

-- Dumping data for table avthardwaretrading.invoices: ~11 rows (approximately)
DELETE FROM `invoices`;
INSERT INTO `invoices` (`id`, `customer_id`, `total`, `created_at`, `updated_at`) VALUES
	(1, 1, '1000', '2023-12-08 18:53:24', '2023-12-08 18:53:24'),
	(2, 2, '1000', '2023-12-11 14:23:13', '2023-12-11 14:23:13'),
	(4, 1, '1000', '2023-12-12 14:47:12', '2023-12-12 14:47:12'),
	(5, 4, '1000', '2023-12-12 15:05:51', '2023-12-12 15:05:51'),
	(6, 4, '1000', '2023-12-12 19:52:26', '2023-12-12 19:52:26'),
	(8, 4, '1000', '2023-12-12 22:46:24', '2023-12-12 22:46:24'),
	(10, 1, '1000', '2023-12-12 22:48:30', '2023-12-12 22:48:30'),
	(13, 4, '1000', '2023-12-13 00:14:17', '2023-12-13 00:14:17'),
	(14, 1, '1000', '2025-07-21 02:30:53', '2025-07-21 02:30:53'),
	(15, 1, '1000', '2025-07-21 02:32:46', '2025-07-21 02:32:46'),
	(16, 1, '1000', '2025-07-21 02:33:20', '2025-07-21 02:33:20');

-- Dumping data for table avthardwaretrading.migrations: ~12 rows (approximately)
DELETE FROM `migrations`;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '2014_10_12_000000_create_users_table', 1),
	(2, '2014_10_12_100000_create_password_resets_table', 1),
	(3, '2019_09_14_134301_create_categories_table', 1),
	(4, '2019_09_15_053453_create_taxes_table', 1),
	(5, '2019_09_15_055531_create_units_table', 1),
	(6, '2019_09_15_061238_create_suppliers_table', 1),
	(7, '2019_09_15_065207_create_customers_table', 1),
	(8, '2019_09_15_101601_create_products_table', 1),
	(9, '2019_09_17_043116_create_product_suppliers_table', 1),
	(10, '2019_09_18_180122_create_invoices_table', 1),
	(11, '2019_09_24_071816_create_sales_table', 1),
	(12, '2019_09_25_123326_create_purchases_table', 1);

-- Dumping data for table avthardwaretrading.password_resets: ~0 rows (approximately)
DELETE FROM `password_resets`;

-- Dumping data for table avthardwaretrading.products: ~8 rows (approximately)
DELETE FROM `products`;
INSERT INTO `products` (`id`, `name`, `serial_number`, `model`, `category_id`, `sales_price`, `unit_id`, `image`, `tax_id`, `created_at`, `updated_at`) VALUES
	(1, 'Product A', 111011, 'DDDT', 1, '44', 1, '1702439100_657928bc9d95d.png', '1', '2023-12-08 18:52:46', '2023-12-12 16:45:00'),
	(2, 'Sample Product', 100145, 'XYZ', 1, '20', 1, '1702443371_6579396bdf0ae.png', '1', '2023-12-10 13:35:29', '2023-12-12 17:56:11'),
	(3, 'Product C', 410101, 'ASTR0', 1, '26', 1, '1702450226_65795432ae641.png', '1', '2023-12-12 15:02:14', '2023-12-12 19:50:26'),
	(5, 'Product B', 10011, 'ERTYU', 2, '29', 1, '1702449322_657950aaee416.png', '1', '2023-12-12 17:57:10', '2023-12-12 19:35:22'),
	(6, 'Product D', 12345677, 'QWXXQ', 1, '32', 1, '1702450307_65795483a6843.png', '1', '2023-12-12 19:51:20', '2023-12-12 19:51:47'),
	(7, 'Product E', 1010111, 'TYUIO', 2, '20', 1, '1702450464_65795520115ab.png', '1', '2023-12-12 19:54:24', '2023-12-12 19:54:24'),
	(8, 'Product F', 1011117, 'ASTR0', 2, '28', 1, '1702460354_65797bc2eee29.png', '1', '2023-12-12 22:39:14', '2023-12-12 22:39:14'),
	(9, 'Product Testt', 1204444, 'ASTR0', 3, '29', 1, '1702465892_6579916499fee.png', '3', '2023-12-13 00:11:32', '2023-12-13 00:11:32');

-- Dumping data for table avthardwaretrading.product_suppliers: ~9 rows (approximately)
DELETE FROM `product_suppliers`;
INSERT INTO `product_suppliers` (`id`, `product_id`, `supplier_id`, `price`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 33, '2023-12-08 18:52:46', '2023-12-08 18:52:46'),
	(2, 2, 1, 16, '2023-12-10 13:35:29', '2023-12-10 13:35:29'),
	(3, 3, 1, 21, '2023-12-12 15:02:14', '2023-12-12 15:02:14'),
	(5, 5, 1, 24, '2023-12-12 17:57:10', '2023-12-12 17:57:10'),
	(11, 6, 1, 22, '2023-12-12 19:51:20', '2023-12-12 19:51:47'),
	(12, 7, 2, 16, '2023-12-12 19:54:24', '2023-12-12 19:54:24'),
	(13, 8, 2, 21, '2023-12-12 22:39:15', '2023-12-12 22:39:15'),
	(14, 9, 2, 20, '2023-12-13 00:11:33', '2023-12-13 00:11:33'),
	(15, 1, 2, 56, '2025-07-21 00:59:02', '2025-07-21 00:59:02');

-- Dumping data for table avthardwaretrading.purchases: ~0 rows (approximately)
DELETE FROM `purchases`;

-- Dumping data for table avthardwaretrading.sales: ~14 rows (approximately)
DELETE FROM `sales`;
INSERT INTO `sales` (`id`, `invoice_id`, `product_id`, `qty`, `price`, `dis`, `amount`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 2, 44, 0, 88, '2023-12-08 18:53:24', '2023-12-08 18:53:24'),
	(2, 2, 2, 8, 20, 0, 160, '2023-11-05 14:23:13', '2023-12-11 14:23:13'),
	(4, 4, 2, 3, 20, 0, 60, '2023-10-22 16:49:12', '2023-12-12 14:47:12'),
	(5, 5, 3, 2, 26, 0, 52, '2023-12-11 15:05:51', '2023-12-12 15:05:51'),
	(6, 6, 6, 5, 32, 0, 160, '2023-09-12 20:52:26', '2023-12-12 19:52:26'),
	(9, 8, 5, 4, 29, 0, 116, '2023-12-11 22:46:24', '2023-12-12 22:46:24'),
	(11, 10, 1, 3, 44, 0, 132, '2023-11-11 22:48:30', '2023-12-12 22:48:30'),
	(14, 13, 8, 2, 28, 0, 56, '2023-12-13 00:14:17', '2023-12-13 00:14:17'),
	(15, 13, 9, 2, 29, 2, 57, '2023-12-13 00:14:17', '2023-12-13 00:14:17'),
	(16, 13, 5, 2, 29, 2, 57, '2023-12-13 00:14:17', '2023-12-13 00:14:17'),
	(17, 14, 1, 1, 44, 50, 22, '2025-07-21 02:30:53', '2025-07-21 02:30:53'),
	(18, 15, 1, 1, 44, 50, 22, '2025-07-21 02:32:46', '2025-07-21 02:32:46'),
	(19, 16, 1, 1, 44, 50, 22, '2025-07-21 02:33:20', '2025-07-21 02:33:20'),
	(20, 16, 2, 2, 20, 0, 40, '2025-07-21 02:33:20', '2025-07-21 02:33:20');

-- Dumping data for table avthardwaretrading.suppliers: ~5 rows (approximately)
DELETE FROM `suppliers`;
INSERT INTO `suppliers` (`id`, `supplier_code`, `name`, `mobile`, `address`, `details`, `tax`, `email`, `previous_balance`, `created_at`, `updated_at`) VALUES
	(8, 'PRO-244', 'Product Supplier Hardware', '87777777777', 'test', 'test', '123-456-789-000', 'helenjanemanalo@gmail.com', NULL, '2025-07-28 02:45:48', '2025-07-28 02:45:48'),
	(9, 'JEN-137', 'Jen Manalo ABCDE', '09696203793', 'test', 'test', '123-456-799-000', 'test@gmail.com', NULL, '2025-07-28 02:47:29', '2025-07-28 02:47:29'),
	(10, 'TES-191', 'Test Supplier', '09696203793', 'test address', 'details 101', '123-456-889-000', 'test01@gmail.com', NULL, '2025-07-28 03:53:33', '2025-07-28 03:53:33'),
	(11, 'JEN-909', 'Jen Manalo ABCD123', '09696213783', 'test address', 'details 101', '456-789-123-000', 'admin00@mail.com', NULL, '2025-07-28 03:58:31', '2025-07-28 03:58:31'),
	(12, 'PRO-629', 'Product Abcds', '09696203783', 'test', 'test', '123-456-789-001', 'admin00@avthardwaretrading.com', NULL, '2025-07-28 04:03:37', '2025-07-28 04:03:37');

-- Dumping data for table avthardwaretrading.supplier_items: ~8 rows (approximately)
DELETE FROM `supplier_items`;
INSERT INTO `supplier_items` (`id`, `supplier_id`, `item_code`, `category_id`, `item_description`, `item_price`, `item_amount`, `unit_id`, `item_qty`, `item_image`, `created_at`, `updated_at`) VALUES
	(5, 8, 'PRO-244-001', 4, 'Tools 1', 50.00, 2500.00, 4, 50, NULL, '2025-07-28 02:45:48', '2025-07-28 02:45:48'),
	(6, 8, 'PRO-244-002', 4, 'Tools 2', 60.00, 2400.00, 4, 40, NULL, '2025-07-28 02:45:48', '2025-07-28 02:45:48'),
	(7, 9, 'JEN-137-001', 4, 'Tools 1', 60.00, 240.00, 4, 4, NULL, '2025-07-28 02:47:29', '2025-07-28 02:47:29'),
	(8, 10, 'TES-191-001', 6, 'Tools 1123123', 50.00, 3000.00, 4, 60, NULL, '2025-07-28 03:53:33', '2025-07-28 03:53:33'),
	(9, 10, 'TES-191-002', 6, 'Tools 1123123ghrfhf', 40.00, 2400.00, 4, 60, NULL, '2025-07-28 03:53:33', '2025-07-28 03:53:33'),
	(10, 10, 'TES-191-003', 5, 'Tools 1', 30.00, 2100.00, 5, 70, NULL, '2025-07-28 03:53:33', '2025-07-28 03:53:33'),
	(11, 11, 'JEN-909-001', 6, 'Tools 1123123', 60.00, 2400.00, 3, 40, NULL, '2025-07-28 03:58:31', '2025-07-28 03:58:31'),
	(12, 12, 'PRO-629-001', 5, 'Tools 11231236', 40.00, 2000.00, 4, 50, 'items/PRO-629/mjOfHCZeWUMWHbC9hGk4rM1zCgI4YbvQl76uXRYQ.jpg', '2025-07-28 04:03:37', '2025-07-28 04:03:37');

-- Dumping data for table avthardwaretrading.taxes: ~3 rows (approximately)
DELETE FROM `taxes`;
INSERT INTO `taxes` (`id`, `name`, `slug`, `status`, `created_at`, `updated_at`) VALUES
	(1, '5', '5', 1, '2023-12-08 18:44:54', '2023-12-08 18:44:54'),
	(2, '10', '10', 1, '2023-12-12 16:28:06', '2023-12-12 16:28:14'),
	(3, '2', '2', 1, '2023-12-13 00:09:48', '2023-12-13 00:09:48');

-- Dumping data for table avthardwaretrading.units: ~5 rows (approximately)
DELETE FROM `units`;
INSERT INTO `units` (`id`, `name`, `slug`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'TEST', 'test', 1, '2023-12-08 18:45:51', '2023-12-08 18:45:51'),
	(2, 'kg', 'kg', 1, '2025-07-27 18:19:54', '2025-07-27 18:19:54'),
	(3, 'screws', 'screws', 1, '2025-07-27 18:21:30', '2025-07-27 18:21:30'),
	(4, 'each', 'each', 1, '2025-07-27 18:21:34', '2025-07-27 18:21:34'),
	(5, 'pallet', 'pallet', 1, '2025-07-27 18:21:48', '2025-07-27 18:21:48');

-- Dumping data for table avthardwaretrading.users: ~1 rows (approximately)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `f_name`, `l_name`, `email`, `image`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
	(1, 'AVT', 'Hardware', 'admin@avthardwaretrading.com', 'admin-icn.png', NULL, '$2y$10$DSIGFK.FBSgz5v.ePycoIelTS3b2VDRX2VVcMOjDNuWw7gq7GWgTi', NULL, NULL, '2025-07-21 00:34:06');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
