-- CBPMS Database Backup
-- Generated: 2026-07-21 13:09:05

-- Table: attendance
DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `normal_hours` decimal(5,2) NOT NULL DEFAULT '0.00',
  `overtime_hours` decimal(5,2) NOT NULL DEFAULT '0.00',
  `status` enum('present','absent','half_day') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'present',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendance_employee_id_date_unique` (`employee_id`,`date`),
  KEY `attendance_recorded_by_foreign` (`recorded_by`),
  KEY `attendance_date_index` (`date`),
  CONSTRAINT `attendance_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: audit_logs
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_action_index` (`action`),
  KEY `audit_logs_model_model_id_index` (`model`,`model_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cache
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cache_locks
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: concrete_mixes
DROP TABLE IF EXISTS `concrete_mixes`;
CREATE TABLE `concrete_mixes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `strength` int NOT NULL,
  `cement_per_m3` decimal(8,3) NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: concrete_mixes
INSERT INTO `concrete_mixes` (`id`, `strength`, `cement_per_m3`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('1', '180', '250.000', 'خرسانة 180 - 250 كغ/م³', '1', '2026-07-16 15:21:30', '2026-07-16 15:21:30');
INSERT INTO `concrete_mixes` (`id`, `strength`, `cement_per_m3`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('2', '250', '350.000', 'خرسانة 250 - 350 كغ/م³', '1', '2026-07-16 15:21:30', '2026-07-16 15:21:30');
INSERT INTO `concrete_mixes` (`id`, `strength`, `cement_per_m3`, `description`, `is_active`, `created_at`, `updated_at`) VALUES ('3', '300', '350.000', 'خرسانة 300 - 350 كغ/م³', '1', '2026-07-16 15:21:30', '2026-07-16 15:21:30');

-- Table: contributor_payments
DROP TABLE IF EXISTS `contributor_payments`;
CREATE TABLE `contributor_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `contributor_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('cash','bank_transfer','check') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `treasury_transaction_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contributor_payments_contributor_id_foreign` (`contributor_id`),
  KEY `contributor_payments_treasury_transaction_id_foreign` (`treasury_transaction_id`),
  CONSTRAINT `contributor_payments_contributor_id_foreign` FOREIGN KEY (`contributor_id`) REFERENCES `contributors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contributor_payments_treasury_transaction_id_foreign` FOREIGN KEY (`treasury_transaction_id`) REFERENCES `treasury_transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: contributors
DROP TABLE IF EXISTS `contributors`;
CREATE TABLE `contributors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `share_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `share_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `national_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: credits
DROP TABLE IF EXISTS `credits`;
CREATE TABLE `credits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `creditable_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `creditable_id` bigint unsigned NOT NULL,
  `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `due_date` date NOT NULL,
  `paid_date` date DEFAULT NULL,
  `status` enum('pending','paid','overdue') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `credits_created_by_foreign` (`created_by`),
  KEY `credits_creditable_type_creditable_id_index` (`creditable_type`,`creditable_id`),
  KEY `credits_due_date_index` (`due_date`),
  KEY `credits_status_index` (`status`),
  CONSTRAINT `credits_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: credits
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('1', 'supplier', '5', 'purchase', '1', '14400.00', '2026-08-15', '2026-07-16', 'paid', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 16:03:56');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('2', 'supplier', '5', 'purchase', '2', '21900.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('3', 'supplier', '5', 'purchase', '3', '21900.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('4', 'supplier', '3', 'purchase', '4', '22800.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('5', 'supplier', '3', 'purchase', '5', '22800.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('6', 'supplier', '3', 'purchase', '6', '25900.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('7', 'supplier', '2', 'purchase', '7', '275440.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('8', 'supplier', '9', 'purchase', '8', '54000.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('9', 'supplier', '3', 'purchase', '9', '11040.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('10', 'supplier', '3', 'purchase', '10', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('11', 'supplier', '5', 'purchase', '11', '21900.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('12', 'supplier', '5', 'purchase', '12', '21900.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('13', 'supplier', '5', 'purchase', '13', '14400.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('14', 'supplier', '5', 'purchase', '14', '21900.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('15', 'supplier', '5', 'purchase', '15', '14400.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('16', 'supplier', '5', 'purchase', '16', '21900.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('17', 'supplier', '3', 'purchase', '17', '1656.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('18', 'supplier', '3', 'purchase', '18', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('19', 'supplier', '3', 'purchase', '19', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('20', 'supplier', '9', 'purchase', '20', '64000.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('21', 'supplier', '3', 'purchase', '21', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('22', 'supplier', '3', 'purchase', '22', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('23', 'supplier', '3', 'purchase', '23', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('24', 'supplier', '3', 'purchase', '24', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('25', 'supplier', '3', 'purchase', '25', '1656.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('26', 'supplier', '3', 'purchase', '26', '1656.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('27', 'supplier', '2', 'purchase', '27', '235200.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('28', 'supplier', '6', 'purchase', '28', '21900.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('29', 'supplier', '6', 'purchase', '29', '22995.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('30', 'supplier', '3', 'purchase', '30', '22630.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('31', 'supplier', '3', 'purchase', '31', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('32', 'supplier', '3', 'purchase', '32', '22265.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('33', 'supplier', '3', 'purchase', '33', '22265.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('34', 'supplier', '3', 'purchase', '34', '22630.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('35', 'supplier', '3', 'purchase', '35', '13570.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('36', 'supplier', '3', 'purchase', '36', '13570.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('37', 'supplier', '3', 'purchase', '37', '13570.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('38', 'supplier', '3', 'purchase', '38', '1656.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('39', 'supplier', '3', 'purchase', '39', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('40', 'supplier', '3', 'purchase', '40', '14030.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('41', 'supplier', '3', 'purchase', '41', '14030.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('42', 'supplier', '3', 'purchase', '42', '22630.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('43', 'supplier', '3', 'purchase', '43', '22630.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('44', 'supplier', '2', 'purchase', '44', '272308.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('45', 'supplier', '3', 'purchase', '45', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('46', 'supplier', '3', 'purchase', '46', '22265.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('47', 'supplier', '3', 'purchase', '47', '14160.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('48', 'supplier', '3', 'purchase', '48', '14640.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('49', 'supplier', '3', 'purchase', '49', '14640.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('50', 'supplier', '3', 'purchase', '50', '1656.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('51', 'supplier', '3', 'purchase', '51', '22265.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('52', 'supplier', '2', 'purchase', '52', '262352.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('53', 'supplier', '2', 'purchase', '53', '262010.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('54', 'supplier', '3', 'purchase', '54', '1656.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('55', 'supplier', '3', 'purchase', '55', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('56', 'supplier', '3', 'purchase', '56', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('57', 'supplier', '3', 'purchase', '57', '14640.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('58', 'supplier', '3', 'purchase', '58', '24090.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('59', 'supplier', '3', 'purchase', '59', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('60', 'supplier', '3', 'purchase', '60', '1748.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('61', 'supplier', '3', 'purchase', '61', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('62', 'supplier', '3', 'purchase', '62', '1656.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('63', 'supplier', '3', 'purchase', '63', '14640.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('64', 'supplier', '3', 'purchase', '64', '22265.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('65', 'supplier', '3', 'purchase', '65', '14160.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('66', 'supplier', '3', 'purchase', '66', '1656.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('67', 'supplier', '3', 'purchase', '67', '1656.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('68', 'supplier', '3', 'purchase', '68', '22265.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('69', 'supplier', '3', 'purchase', '69', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('70', 'supplier', '3', 'purchase', '70', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('71', 'supplier', '3', 'purchase', '71', '14160.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('72', 'supplier', '3', 'purchase', '72', '14160.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('73', 'supplier', '3', 'purchase', '73', '14640.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('74', 'supplier', '3', 'purchase', '74', '22082.50', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('75', 'supplier', '3', 'purchase', '75', '14520.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('76', 'supplier', '3', 'purchase', '76', '22630.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('77', 'supplier', '3', 'purchase', '77', '22630.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('78', 'supplier', '3', 'purchase', '78', '14640.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('79', 'supplier', '3', 'purchase', '79', '22265.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('80', 'supplier', '3', 'purchase', '80', '13440.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('81', 'supplier', '3', 'purchase', '81', '21535.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('82', 'supplier', '3', 'purchase', '82', '15120.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('83', 'supplier', '3', 'purchase', '83', '1748.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('84', 'supplier', '3', 'purchase', '84', '1748.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('85', 'supplier', '3', 'purchase', '85', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('86', 'supplier', '3', 'purchase', '86', '22265.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('87', 'supplier', '3', 'purchase', '87', '22630.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('88', 'supplier', '3', 'purchase', '88', '13440.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('89', 'supplier', '3', 'purchase', '89', '23725.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('90', 'supplier', '3', 'purchase', '90', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('91', 'supplier', '3', 'purchase', '91', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('92', 'supplier', '9', 'purchase', '92', '96000.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('93', 'supplier', '3', 'purchase', '93', '1748.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('94', 'supplier', '3', 'purchase', '94', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('95', 'supplier', '3', 'purchase', '95', '13680.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('96', 'supplier', '3', 'purchase', '96', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('97', 'supplier', '3', 'purchase', '97', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('98', 'supplier', '3', 'purchase', '98', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('99', 'supplier', '3', 'purchase', '99', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('100', 'supplier', '3', 'purchase', '100', '1656.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('101', 'supplier', '5', 'purchase', '101', '17995.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('102', 'supplier', '3', 'purchase', '102', '1748.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('103', 'supplier', '3', 'purchase', '103', '1748.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('104', 'supplier', '3', 'purchase', '104', '21170.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('105', 'supplier', '3', 'purchase', '105', '21535.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('106', 'supplier', '3', 'purchase', '106', '1656.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('107', 'supplier', '3', 'purchase', '107', '22630.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('108', 'supplier', '3', 'purchase', '108', '21900.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('109', 'supplier', '3', 'purchase', '109', '22265.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('110', 'supplier', '3', 'purchase', '110', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('111', 'supplier', '3', 'purchase', '111', '22082.50', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('112', 'supplier', '3', 'purchase', '112', '14400.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('113', 'supplier', '2', 'purchase', '113', '230727.20', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('114', 'supplier', '3', 'purchase', '114', '21535.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('115', 'supplier', '3', 'purchase', '115', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('116', 'supplier', '3', 'purchase', '116', '1656.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('117', 'supplier', '3', 'purchase', '117', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('118', 'supplier', '3', 'purchase', '118', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('119', 'supplier', '3', 'purchase', '119', '22265.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('120', 'supplier', '3', 'purchase', '120', '1748.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('121', 'supplier', '3', 'purchase', '121', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('122', 'supplier', '3', 'purchase', '122', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('123', 'supplier', '3', 'purchase', '123', '22265.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('124', 'supplier', '3', 'purchase', '124', '22630.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('125', 'supplier', '3', 'purchase', '125', '14400.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('126', 'supplier', '3', 'purchase', '126', '15120.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('127', 'supplier', '3', 'purchase', '127', '22082.50', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('128', 'supplier', '3', 'purchase', '128', '14400.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('129', 'supplier', '3', 'purchase', '129', '22082.50', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('130', 'supplier', '3', 'purchase', '130', '1656.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('131', 'supplier', '2', 'purchase', '131', '228723.20', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('132', 'supplier', '3', 'purchase', '132', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('133', 'supplier', '3', 'purchase', '133', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('134', 'supplier', '3', 'purchase', '134', '1656.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('135', 'supplier', '3', 'purchase', '135', '22265.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('136', 'supplier', '3', 'purchase', '136', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('137', 'supplier', '3', 'purchase', '137', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('138', 'supplier', '3', 'purchase', '138', '21535.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('139', 'supplier', '3', 'purchase', '139', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('140', 'supplier', '3', 'purchase', '140', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('141', 'supplier', '3', 'purchase', '141', '14400.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('142', 'supplier', '3', 'purchase', '142', '15120.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('143', 'supplier', '3', 'purchase', '143', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('144', 'supplier', '3', 'purchase', '144', '22630.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('145', 'supplier', '3', 'purchase', '145', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('146', 'supplier', '2', 'purchase', '146', '233784.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('147', 'supplier', '3', 'purchase', '147', '1656.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('148', 'supplier', '3', 'purchase', '148', '1656.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('149', 'supplier', '3', 'purchase', '149', '14400.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('150', 'supplier', '3', 'purchase', '150', '15120.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('151', 'supplier', '3', 'purchase', '151', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES ('152', 'supplier', '3', 'purchase', '152', '1840.00', '2026-08-15', NULL, 'pending', NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');

-- Table: customer_payments
DROP TABLE IF EXISTS `customer_payments`;
CREATE TABLE `customer_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned NOT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `payment_method` enum('cash','bank_transfer','check') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_payments_customer_id_foreign` (`customer_id`),
  KEY `customer_payments_order_id_foreign` (`order_id`),
  KEY `customer_payments_recorded_by_foreign` (`recorded_by`),
  KEY `customer_payments_payment_date_index` (`payment_date`),
  CONSTRAINT `customer_payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `customer_payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customer_payments_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: customers
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `concrete_type` enum('operational','complete') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'operational',
  `payment_type` enum('cash','credit','mixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `cement_balance` decimal(12,3) NOT NULL DEFAULT '0.000',
  `concrete_strength` int DEFAULT NULL,
  `cement_content` decimal(8,3) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customers_created_by_foreign` (`created_by`),
  KEY `customers_concrete_type_index` (`concrete_type`),
  KEY `customers_is_active_index` (`is_active`),
  CONSTRAINT `customers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: employee_borrow_deductions
DROP TABLE IF EXISTS `employee_borrow_deductions`;
CREATE TABLE `employee_borrow_deductions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `borrow_id` bigint unsigned NOT NULL,
  `payroll_id` bigint unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `deduction_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_borrow_deductions_borrow_id_foreign` (`borrow_id`),
  KEY `employee_borrow_deductions_payroll_id_foreign` (`payroll_id`),
  CONSTRAINT `employee_borrow_deductions_borrow_id_foreign` FOREIGN KEY (`borrow_id`) REFERENCES `employee_borrows` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_borrow_deductions_payroll_id_foreign` FOREIGN KEY (`payroll_id`) REFERENCES `payroll` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: employee_borrows
DROP TABLE IF EXISTS `employee_borrows`;
CREATE TABLE `employee_borrows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `remaining_amount` decimal(12,2) NOT NULL,
  `borrow_date` date NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `recorded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_borrows_recorded_by_foreign` (`recorded_by`),
  KEY `employee_borrows_employee_id_status_index` (`employee_id`,`status`),
  KEY `employee_borrows_borrow_date_index` (`borrow_date`),
  CONSTRAINT `employee_borrows_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_borrows_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: employee_deductions
DROP TABLE IF EXISTS `employee_deductions`;
CREATE TABLE `employee_deductions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `deduction_date` date NOT NULL,
  `type` enum('absence','late_arrival','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_deductions_employee_id_foreign` (`employee_id`),
  KEY `employee_deductions_recorded_by_foreign` (`recorded_by`),
  KEY `employee_deductions_deduction_date_index` (`deduction_date`),
  CONSTRAINT `employee_deductions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_deductions_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: employees
DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `base_salary` decimal(12,2) NOT NULL,
  `overtime_rate` decimal(8,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: equipment
DROP TABLE IF EXISTS `equipment`;
CREATE TABLE `equipment` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('loader','mixer','service_vehicle','pump','generator') COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_cost` decimal(14,2) DEFAULT NULL,
  `status` enum('active','maintenance','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `tracking_type` enum('hours','days') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'days',
  `maintenance_threshold` int DEFAULT NULL COMMENT 'Maintenance threshold in hours or days',
  `current_hours` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Current accumulated hours',
  `current_days` int NOT NULL DEFAULT '0' COMMENT 'Current accumulated days',
  `last_maintenance_at_hours` decimal(10,2) DEFAULT NULL COMMENT 'Hours value at last maintenance',
  `last_maintenance_at_days` int DEFAULT NULL COMMENT 'Days value at last maintenance',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: equipment_fuel_logs
DROP TABLE IF EXISTS `equipment_fuel_logs`;
CREATE TABLE `equipment_fuel_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `equipment_id` bigint unsigned NOT NULL,
  `log_date` date NOT NULL,
  `liters` decimal(10,2) NOT NULL,
  `unit_cost` decimal(8,2) NOT NULL,
  `total_cost` decimal(12,2) NOT NULL,
  `hours_logged` decimal(10,2) DEFAULT NULL COMMENT 'Hours logged for this fuel entry',
  `days_logged` int DEFAULT NULL COMMENT 'Days logged for this fuel entry',
  `deduct_from_inventory` tinyint(1) NOT NULL DEFAULT '0',
  `inventory_item_id` bigint unsigned DEFAULT NULL,
  `inventory_movement_id` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `equipment_fuel_logs_recorded_by_foreign` (`recorded_by`),
  KEY `equipment_fuel_logs_equipment_id_log_date_index` (`equipment_id`,`log_date`),
  KEY `equipment_fuel_logs_inventory_item_id_foreign` (`inventory_item_id`),
  KEY `equipment_fuel_logs_inventory_movement_id_foreign` (`inventory_movement_id`),
  CONSTRAINT `equipment_fuel_logs_equipment_id_foreign` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `equipment_fuel_logs_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `equipment_fuel_logs_inventory_movement_id_foreign` FOREIGN KEY (`inventory_movement_id`) REFERENCES `inventory_movements` (`id`) ON DELETE SET NULL,
  CONSTRAINT `equipment_fuel_logs_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: equipment_maintenance
DROP TABLE IF EXISTS `equipment_maintenance`;
CREATE TABLE `equipment_maintenance` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `equipment_id` bigint unsigned NOT NULL,
  `maintenance_date` date NOT NULL,
  `type` enum('routine','repair','spare_part') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost` decimal(12,2) NOT NULL,
  `hours_at_maintenance` decimal(10,2) DEFAULT NULL COMMENT 'Total hours when maintenance was done',
  `days_at_maintenance` int DEFAULT NULL COMMENT 'Total days when maintenance was done',
  `supplier_id` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `equipment_maintenance_supplier_id_foreign` (`supplier_id`),
  KEY `equipment_maintenance_recorded_by_foreign` (`recorded_by`),
  KEY `equipment_maintenance_equipment_id_maintenance_date_index` (`equipment_id`,`maintenance_date`),
  CONSTRAINT `equipment_maintenance_equipment_id_foreign` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `equipment_maintenance_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `equipment_maintenance_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: equipment_tool_movements
DROP TABLE IF EXISTS `equipment_tool_movements`;
CREATE TABLE `equipment_tool_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `equipment_tool_id` bigint unsigned NOT NULL,
  `type` enum('in','out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `price_per_unit` decimal(15,2) NOT NULL,
  `total_cost` decimal(15,2) NOT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `treasury_transaction_id` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `movement_date` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `equipment_tool_movements_equipment_tool_id_foreign` (`equipment_tool_id`),
  KEY `equipment_tool_movements_treasury_transaction_id_foreign` (`treasury_transaction_id`),
  CONSTRAINT `equipment_tool_movements_equipment_tool_id_foreign` FOREIGN KEY (`equipment_tool_id`) REFERENCES `equipment_tools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `equipment_tool_movements_treasury_transaction_id_foreign` FOREIGN KEY (`treasury_transaction_id`) REFERENCES `treasury_transactions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: equipment_tool_movements
INSERT INTO `equipment_tool_movements` (`id`, `equipment_tool_id`, `type`, `quantity`, `price_per_unit`, `total_cost`, `balance_after`, `treasury_transaction_id`, `notes`, `movement_date`, `created_at`, `updated_at`) VALUES ('1', '1', 'in', '1.00', '1050.00', '1050.00', '1.00', '2', 'رصيد افتتاحي', '2026-07-16 16:23:16', '2026-07-16 16:23:16', '2026-07-16 16:23:16');
INSERT INTO `equipment_tool_movements` (`id`, `equipment_tool_id`, `type`, `quantity`, `price_per_unit`, `total_cost`, `balance_after`, `treasury_transaction_id`, `notes`, `movement_date`, `created_at`, `updated_at`) VALUES ('2', '1', 'out', '1.00', '1050.00', '1050.00', '0.00', '3', NULL, '2026-07-16 00:00:00', '2026-07-16 16:23:47', '2026-07-16 16:23:47');

-- Table: equipment_tools
DROP TABLE IF EXISTS `equipment_tools`;
CREATE TABLE `equipment_tools` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(15,2) NOT NULL DEFAULT '0.00',
  `price_per_unit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_value` decimal(15,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: equipment_tools
INSERT INTO `equipment_tools` (`id`, `name`, `unit`, `quantity`, `price_per_unit`, `total_value`, `notes`, `created_at`, `updated_at`) VALUES ('1', 'شحم', 'بستلة', '0.00', '1050.00', '0.00', NULL, '2026-07-16 16:23:16', '2026-07-16 16:23:47');

-- Table: expense_categories
DROP TABLE IF EXISTS `expense_categories`;
CREATE TABLE `expense_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'custom',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: expense_categories
INSERT INTO `expense_categories` (`id`, `name`, `type`, `is_active`, `created_at`, `updated_at`) VALUES ('1', 'وقود', 'default', '1', '2026-07-16 15:21:31', '2026-07-16 15:21:31');
INSERT INTO `expense_categories` (`id`, `name`, `type`, `is_active`, `created_at`, `updated_at`) VALUES ('2', 'صيانة', 'default', '1', '2026-07-16 15:21:31', '2026-07-16 15:21:31');
INSERT INTO `expense_categories` (`id`, `name`, `type`, `is_active`, `created_at`, `updated_at`) VALUES ('3', 'مواد', 'default', '1', '2026-07-16 15:21:31', '2026-07-16 15:21:31');
INSERT INTO `expense_categories` (`id`, `name`, `type`, `is_active`, `created_at`, `updated_at`) VALUES ('4', 'رواتب', 'default', '1', '2026-07-16 15:21:31', '2026-07-16 15:21:31');
INSERT INTO `expense_categories` (`id`, `name`, `type`, `is_active`, `created_at`, `updated_at`) VALUES ('5', 'إداري', 'default', '1', '2026-07-16 15:21:31', '2026-07-16 15:21:31');
INSERT INTO `expense_categories` (`id`, `name`, `type`, `is_active`, `created_at`, `updated_at`) VALUES ('6', '(أخرى) مخصص ضرائب', 'default', '1', '2026-07-16 15:21:31', '2026-07-16 15:21:31');
INSERT INTO `expense_categories` (`id`, `name`, `type`, `is_active`, `created_at`, `updated_at`) VALUES ('7', '(أخرى) مساهمين', 'default', '1', '2026-07-16 15:21:31', '2026-07-16 15:21:31');
INSERT INTO `expense_categories` (`id`, `name`, `type`, `is_active`, `created_at`, `updated_at`) VALUES ('8', '(أخرى) توزيع ارباح', 'default', '1', '2026-07-16 15:21:31', '2026-07-16 15:21:31');
INSERT INTO `expense_categories` (`id`, `name`, `type`, `is_active`, `created_at`, `updated_at`) VALUES ('9', '(أخرى) الصدقه', 'default', '1', '2026-07-16 15:21:31', '2026-07-16 15:21:31');
INSERT INTO `expense_categories` (`id`, `name`, `type`, `is_active`, `created_at`, `updated_at`) VALUES ('10', 'تأمين للغير', 'default', '1', '2026-07-16 15:21:31', '2026-07-16 15:21:31');
INSERT INTO `expense_categories` (`id`, `name`, `type`, `is_active`, `created_at`, `updated_at`) VALUES ('11', 'تكاليف عمليات', 'default', '1', '2026-07-16 15:21:31', '2026-07-16 15:21:31');
INSERT INTO `expense_categories` (`id`, `name`, `type`, `is_active`, `created_at`, `updated_at`) VALUES ('12', 'مخصص طوارئ', 'default', '1', '2026-07-16 15:21:31', '2026-07-16 15:21:31');
INSERT INTO `expense_categories` (`id`, `name`, `type`, `is_active`, `created_at`, `updated_at`) VALUES ('13', 'مصاريف تشغيل', 'default', '1', '2026-07-16 15:21:31', '2026-07-16 15:21:31');
INSERT INTO `expense_categories` (`id`, `name`, `type`, `is_active`, `created_at`, `updated_at`) VALUES ('14', 'مشروعات تحت التنفيذ ( محطه )', 'default', '1', '2026-07-16 15:21:31', '2026-07-16 15:21:31');
INSERT INTO `expense_categories` (`id`, `name`, `type`, `is_active`, `created_at`, `updated_at`) VALUES ('15', 'مصروفات عمومية', 'default', '1', '2026-07-16 15:21:31', '2026-07-16 15:21:31');
INSERT INTO `expense_categories` (`id`, `name`, `type`, `is_active`, `created_at`, `updated_at`) VALUES ('16', 'اصول ثابتة', 'default', '1', '2026-07-16 15:21:31', '2026-07-16 15:21:31');
INSERT INTO `expense_categories` (`id`, `name`, `type`, `is_active`, `created_at`, `updated_at`) VALUES ('17', 'ايرادات اخري', 'default', '1', '2026-07-16 15:21:31', '2026-07-16 15:21:31');

-- Table: expenses
DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `expense_date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `contributor_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_recorded_by_foreign` (`recorded_by`),
  KEY `expenses_expense_date_index` (`expense_date`),
  KEY `expenses_category_index` (`category`),
  KEY `expenses_contributor_id_foreign` (`contributor_id`),
  CONSTRAINT `expenses_contributor_id_foreign` FOREIGN KEY (`contributor_id`) REFERENCES `contributors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expenses_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: failed_jobs
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: inventory_items
DROP TABLE IF EXISTS `inventory_items`;
CREATE TABLE `inventory_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alert_threshold` decimal(10,3) NOT NULL DEFAULT '0.000',
  `current_stock` decimal(12,3) NOT NULL DEFAULT '0.000',
  `price_per_unit` decimal(12,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: inventory_items
INSERT INTO `inventory_items` (`id`, `name`, `name_ar`, `unit`, `alert_threshold`, `current_stock`, `price_per_unit`, `created_at`, `updated_at`) VALUES ('1', 'Cement', 'اسمنت', 'طن', '40.000', '543.630', '150.00', '2026-07-16 15:21:30', '2026-07-16 15:23:26');
INSERT INTO `inventory_items` (`id`, `name`, `name_ar`, `unit`, `alert_threshold`, `current_stock`, `price_per_unit`, `created_at`, `updated_at`) VALUES ('2', 'Sand', 'رمل', 'م³', '60.000', '2062.500', '25.00', '2026-07-16 15:21:30', '2026-07-16 15:23:26');
INSERT INTO `inventory_items` (`id`, `name`, `name_ar`, `unit`, `alert_threshold`, `current_stock`, `price_per_unit`, `created_at`, `updated_at`) VALUES ('3', 'Gravel1', 'سن 1', 'م³', '60.000', '1458.000', '30.00', '2026-07-16 15:21:30', '2026-07-16 15:23:26');
INSERT INTO `inventory_items` (`id`, `name`, `name_ar`, `unit`, `alert_threshold`, `current_stock`, `price_per_unit`, `created_at`, `updated_at`) VALUES ('4', 'Gravel2', 'سن 2', 'م³', '60.000', '1225.000', '30.00', '2026-07-16 15:21:30', '2026-07-16 15:23:26');
INSERT INTO `inventory_items` (`id`, `name`, `name_ar`, `unit`, `alert_threshold`, `current_stock`, `price_per_unit`, `created_at`, `updated_at`) VALUES ('5', 'Additives', 'مادة', 'لتر', '0.000', '12000.000', '5.00', '2026-07-16 15:21:30', '2026-07-16 15:23:26');
INSERT INTO `inventory_items` (`id`, `name`, `name_ar`, `unit`, `alert_threshold`, `current_stock`, `price_per_unit`, `created_at`, `updated_at`) VALUES ('6', 'Water', 'ماء', 'م³', '50.000', '1341.000', '2.00', '2026-07-16 15:21:30', '2026-07-16 15:23:26');

-- Table: inventory_movements
DROP TABLE IF EXISTS `inventory_movements`;
CREATE TABLE `inventory_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_item_id` bigint unsigned NOT NULL,
  `type` enum('in','out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `balance_after` decimal(12,3) NOT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `total_cost` decimal(14,2) DEFAULT NULL,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `movement_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_movements_supplier_id_foreign` (`supplier_id`),
  KEY `inventory_movements_recorded_by_foreign` (`recorded_by`),
  KEY `inventory_movements_movement_date_index` (`movement_date`),
  KEY `inventory_movements_inventory_item_id_movement_date_index` (`inventory_item_id`,`movement_date`),
  CONSTRAINT `inventory_movements_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_movements_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_movements_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=154 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: inventory_movements
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('1', '2', 'in', '60.000', '60.000', '240.00', '14400.00', '5', 'purchase', '1', NULL, NULL, '2026-04-29', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('2', '3', 'in', '60.000', '60.000', '365.00', '21900.00', '5', 'purchase', '2', NULL, NULL, '2026-04-29', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('3', '4', 'in', '60.000', '60.000', '365.00', '21900.00', '5', 'purchase', '3', NULL, NULL, '2026-04-29', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('4', '3', 'in', '60.000', '120.000', '380.00', '22800.00', '3', 'purchase', '4', NULL, NULL, '2026-04-18', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('5', '4', 'in', '60.000', '120.000', '380.00', '22800.00', '3', 'purchase', '5', NULL, NULL, '2026-04-18', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('6', '2', 'in', '140.000', '200.000', '185.00', '25900.00', '3', 'purchase', '6', NULL, NULL, '2026-03-06', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('7', '1', 'in', '68.860', '68.860', '4000.00', '275440.00', '2', 'purchase', '7', NULL, NULL, '2026-04-19', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('8', '5', 'in', '1000.000', '1000.000', '16.00', '16000.00', '9', 'purchase', '8', NULL, NULL, '2026-04-20', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('9', '5', 'in', '1000.000', '2000.000', '38.00', '38000.00', '9', 'purchase', '8', NULL, NULL, '2026-04-20', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('10', '6', 'in', '120.000', '120.000', '92.00', '11040.00', '3', 'purchase', '9', NULL, NULL, '2026-05-03', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('11', '6', 'in', '20.000', '140.000', '92.00', '1840.00', '3', 'purchase', '10', NULL, NULL, '2026-05-06', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('12', '3', 'in', '60.000', '180.000', '365.00', '21900.00', '5', 'purchase', '11', NULL, NULL, '2026-05-11', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('13', '4', 'in', '60.000', '180.000', '365.00', '21900.00', '5', 'purchase', '12', NULL, NULL, '2026-05-11', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('14', '2', 'in', '60.000', '260.000', '240.00', '14400.00', '5', 'purchase', '13', NULL, NULL, '2026-05-11', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('15', '3', 'in', '60.000', '240.000', '365.00', '21900.00', '5', 'purchase', '14', NULL, NULL, '2026-06-13', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('16', '2', 'in', '60.000', '320.000', '240.00', '14400.00', '5', 'purchase', '15', NULL, NULL, '2026-05-13', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('17', '3', 'in', '60.000', '300.000', '365.00', '21900.00', '5', 'purchase', '16', NULL, NULL, '1900-01-12', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('18', '6', 'in', '18.000', '158.000', '92.00', '1656.00', '3', 'purchase', '17', NULL, NULL, '2026-05-17', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('19', '6', 'in', '20.000', '178.000', '92.00', '1840.00', '3', 'purchase', '18', NULL, NULL, '2026-05-17', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('20', '6', 'in', '20.000', '198.000', '92.00', '1840.00', '3', 'purchase', '19', NULL, NULL, '2026-05-18', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('21', '5', 'in', '4000.000', '6000.000', '16.00', '64000.00', '9', 'purchase', '20', NULL, NULL, '2026-06-14', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('22', '6', 'in', '20.000', '218.000', '92.00', '1840.00', '3', 'purchase', '21', NULL, NULL, '2026-06-17', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('23', '6', 'in', '20.000', '238.000', '92.00', '1840.00', '3', 'purchase', '22', NULL, NULL, '2026-06-17', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('24', '6', 'in', '20.000', '258.000', '92.00', '1840.00', '3', 'purchase', '23', NULL, NULL, '2026-06-17', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('25', '6', 'in', '20.000', '278.000', '92.00', '1840.00', '3', 'purchase', '24', NULL, NULL, '2026-06-17', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('26', '6', 'in', '18.000', '296.000', '92.00', '1656.00', '3', 'purchase', '25', NULL, NULL, '2026-06-20', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('27', '6', 'in', '18.000', '314.000', '92.00', '1656.00', '3', 'purchase', '26', NULL, NULL, '2026-06-20', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('28', '1', 'in', '58.800', '127.660', '4000.00', '235200.00', '2', 'purchase', '27', NULL, NULL, '2026-06-20', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('29', '3', 'in', '60.000', '360.000', '365.00', '21900.00', '6', 'purchase', '28', NULL, NULL, '2026-06-20', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('30', '4', 'in', '63.000', '243.000', '365.00', '22995.00', '6', 'purchase', '29', NULL, NULL, '2026-06-20', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('31', '3', 'in', '62.000', '422.000', '365.00', '22630.00', '3', 'purchase', '30', NULL, NULL, '2026-06-20', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('32', '6', 'in', '20.000', '334.000', '92.00', '1840.00', '3', 'purchase', '31', NULL, NULL, '2026-06-21', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('33', '3', 'in', '61.000', '483.000', '365.00', '22265.00', '3', 'purchase', '32', NULL, NULL, '2026-06-20', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('34', '3', 'in', '61.000', '544.000', '365.00', '22265.00', '3', 'purchase', '33', NULL, NULL, '2026-06-21', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('35', '3', 'in', '62.000', '606.000', '365.00', '22630.00', '3', 'purchase', '34', NULL, NULL, '2026-06-21', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('36', '2', 'in', '59.000', '379.000', '230.00', '13570.00', '3', 'purchase', '35', NULL, NULL, '2026-06-21', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('37', '2', 'in', '59.000', '438.000', '230.00', '13570.00', '3', 'purchase', '36', NULL, NULL, '2026-06-21', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('38', '2', 'in', '59.000', '497.000', '230.00', '13570.00', '3', 'purchase', '37', NULL, NULL, '2026-06-21', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('39', '6', 'in', '18.000', '352.000', '92.00', '1656.00', '3', 'purchase', '38', NULL, NULL, '2026-06-21', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('40', '6', 'in', '20.000', '372.000', '92.00', '1840.00', '3', 'purchase', '39', NULL, NULL, '2026-06-21', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('41', '2', 'in', '61.000', '558.000', '230.00', '14030.00', '3', 'purchase', '40', NULL, NULL, '2026-06-21', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('42', '2', 'in', '61.000', '619.000', '230.00', '14030.00', '3', 'purchase', '41', NULL, NULL, '2026-06-21', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('43', '4', 'in', '62.000', '305.000', '365.00', '22630.00', '3', 'purchase', '42', NULL, NULL, '2026-06-21', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('44', '4', 'in', '62.000', '367.000', '365.00', '22630.00', '3', 'purchase', '43', NULL, NULL, '2026-06-21', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('45', '1', 'in', '71.660', '199.320', '3800.00', '272308.00', '2', 'purchase', '44', NULL, NULL, '2026-06-22', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('46', '6', 'in', '20.000', '392.000', '92.00', '1840.00', '3', 'purchase', '45', NULL, NULL, '2026-06-22', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('47', '4', 'in', '61.000', '428.000', '365.00', '22265.00', '3', 'purchase', '46', NULL, NULL, '2026-06-22', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('48', '2', 'in', '59.000', '678.000', '240.00', '14160.00', '3', 'purchase', '47', NULL, NULL, '2026-06-23', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('49', '2', 'in', '61.000', '739.000', '240.00', '14640.00', '3', 'purchase', '48', NULL, NULL, '2026-06-23', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('50', '2', 'in', '61.000', '800.000', '240.00', '14640.00', '3', 'purchase', '49', NULL, NULL, '2026-06-23', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('51', '6', 'in', '18.000', '410.000', '92.00', '1656.00', '3', 'purchase', '50', NULL, NULL, '2026-06-23', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('52', '4', 'in', '61.000', '489.000', '365.00', '22265.00', '3', 'purchase', '51', NULL, NULL, '2026-06-23', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('53', '1', 'in', '69.040', '268.360', '3800.00', '262352.00', '2', 'purchase', '52', NULL, NULL, '2026-06-23', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('54', '1', 'in', '68.950', '337.310', '3800.00', '262010.00', '2', 'purchase', '53', NULL, NULL, '2026-06-23', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('55', '6', 'in', '18.000', '428.000', '92.00', '1656.00', '3', 'purchase', '54', NULL, NULL, '2026-06-23', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('56', '6', 'in', '20.000', '448.000', '92.00', '1840.00', '3', 'purchase', '55', NULL, NULL, '2026-06-23', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('57', '6', 'in', '20.000', '468.000', '92.00', '1840.00', '3', 'purchase', '56', NULL, NULL, '2026-06-23', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('58', '2', 'in', '61.000', '861.000', '240.00', '14640.00', '3', 'purchase', '57', NULL, NULL, '2026-06-23', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('59', '4', 'in', '66.000', '555.000', '365.00', '24090.00', '3', 'purchase', '58', NULL, NULL, '2026-06-23', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('60', '6', 'in', '20.000', '488.000', '92.00', '1840.00', '3', 'purchase', '59', NULL, NULL, '2026-06-24', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('61', '6', 'in', '19.000', '507.000', '92.00', '1748.00', '3', 'purchase', '60', NULL, NULL, '2026-06-25', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('62', '6', 'in', '20.000', '527.000', '92.00', '1840.00', '3', 'purchase', '61', NULL, NULL, '2026-06-24', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('63', '6', 'in', '18.000', '545.000', '92.00', '1656.00', '3', 'purchase', '62', NULL, NULL, '2026-06-25', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('64', '2', 'in', '61.000', '922.000', '240.00', '14640.00', '3', 'purchase', '63', NULL, NULL, '2026-06-25', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('65', '3', 'in', '61.000', '667.000', '365.00', '22265.00', '3', 'purchase', '64', NULL, NULL, '2026-06-25', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('66', '2', 'in', '59.000', '981.000', '240.00', '14160.00', '3', 'purchase', '65', NULL, NULL, '2026-06-25', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('67', '6', 'in', '18.000', '563.000', '92.00', '1656.00', '3', 'purchase', '66', NULL, NULL, '2026-06-25', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('68', '6', 'in', '18.000', '581.000', '92.00', '1656.00', '3', 'purchase', '67', NULL, NULL, '2026-06-27', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('69', '4', 'in', '61.000', '616.000', '365.00', '22265.00', '3', 'purchase', '68', NULL, NULL, '2026-06-27', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('70', '6', 'in', '20.000', '601.000', '92.00', '1840.00', '3', 'purchase', '69', NULL, NULL, '2026-06-27', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('71', '6', 'in', '20.000', '621.000', '92.00', '1840.00', '3', 'purchase', '70', NULL, NULL, '2026-06-27', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('72', '2', 'in', '59.000', '1040.000', '240.00', '14160.00', '3', 'purchase', '71', NULL, NULL, '2026-06-27', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('73', '2', 'in', '59.000', '1099.000', '240.00', '14160.00', '3', 'purchase', '72', NULL, NULL, '2026-06-27', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('74', '2', 'in', '61.000', '1160.000', '240.00', '14640.00', '3', 'purchase', '73', NULL, NULL, '2026-06-27', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('75', '3', 'in', '60.500', '727.500', '365.00', '22082.50', '3', 'purchase', '74', NULL, NULL, '2026-06-28', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('76', '2', 'in', '60.500', '1220.500', '240.00', '14520.00', '3', 'purchase', '75', NULL, NULL, '2026-06-28', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('77', '4', 'in', '62.000', '678.000', '365.00', '22630.00', '3', 'purchase', '76', NULL, NULL, '2026-06-28', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('78', '3', 'in', '62.000', '789.500', '365.00', '22630.00', '3', 'purchase', '77', NULL, NULL, '2026-06-28', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('79', '2', 'in', '61.000', '1281.500', '240.00', '14640.00', '3', 'purchase', '78', NULL, NULL, '2026-06-28', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('80', '3', 'in', '61.000', '850.500', '365.00', '22265.00', '3', 'purchase', '79', NULL, NULL, '2026-06-28', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('81', '2', 'in', '56.000', '1337.500', '240.00', '13440.00', '3', 'purchase', '80', NULL, NULL, '2026-06-28', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('82', '4', 'in', '59.000', '737.000', '365.00', '21535.00', '3', 'purchase', '81', NULL, NULL, '2026-06-29', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('83', '2', 'in', '63.000', '1400.500', '240.00', '15120.00', '3', 'purchase', '82', NULL, NULL, '2026-06-28', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('84', '6', 'in', '19.000', '640.000', '92.00', '1748.00', '3', 'purchase', '83', NULL, NULL, '2026-06-29', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('85', '6', 'in', '19.000', '659.000', '92.00', '1748.00', '3', 'purchase', '84', NULL, NULL, '2026-06-29', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('86', '6', 'in', '20.000', '679.000', '92.00', '1840.00', '3', 'purchase', '85', NULL, NULL, '2026-06-29', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('87', '3', 'in', '61.000', '911.500', '365.00', '22265.00', '3', 'purchase', '86', NULL, NULL, '2026-06-29', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('88', '3', 'in', '62.000', '973.500', '365.00', '22630.00', '3', 'purchase', '87', NULL, NULL, '2026-06-29', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('89', '2', 'in', '56.000', '1456.500', '240.00', '13440.00', '3', 'purchase', '88', NULL, NULL, '2026-06-30', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('90', '4', 'in', '65.000', '802.000', '365.00', '23725.00', '3', 'purchase', '89', NULL, NULL, '2026-06-30', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('91', '6', 'in', '20.000', '699.000', '92.00', '1840.00', '3', 'purchase', '90', NULL, NULL, '2026-06-30', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('92', '6', 'in', '20.000', '719.000', '92.00', '1840.00', '3', 'purchase', '91', NULL, NULL, '2026-06-30', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('93', '5', 'in', '6000.000', '12000.000', '16.00', '96000.00', '9', 'purchase', '92', NULL, NULL, '2026-06-30', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('94', '6', 'in', '19.000', '738.000', '92.00', '1748.00', '3', 'purchase', '93', NULL, NULL, '2026-07-01', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('95', '6', 'in', '20.000', '758.000', '92.00', '1840.00', '3', 'purchase', '94', NULL, NULL, '2026-07-01', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('96', '2', 'in', '57.000', '1513.500', '240.00', '13680.00', '3', 'purchase', '95', NULL, NULL, '2026-07-02', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('97', '6', 'in', '20.000', '778.000', '92.00', '1840.00', '3', 'purchase', '96', NULL, NULL, '2026-07-05', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('98', '6', 'in', '20.000', '798.000', '92.00', '1840.00', '3', 'purchase', '97', NULL, NULL, '2026-07-05', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('99', '6', 'in', '20.000', '818.000', '92.00', '1840.00', '3', 'purchase', '98', NULL, NULL, '2026-07-06', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('100', '6', 'in', '20.000', '838.000', '92.00', '1840.00', '3', 'purchase', '99', NULL, NULL, '2026-07-08', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('101', '6', 'in', '18.000', '856.000', '92.00', '1656.00', '3', 'purchase', '100', NULL, NULL, '2026-07-08', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('102', '3', 'in', '61.000', '1034.500', '295.00', '17995.00', '5', 'purchase', '101', NULL, NULL, '2026-07-09', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('103', '6', 'in', '19.000', '875.000', '92.00', '1748.00', '3', 'purchase', '102', NULL, NULL, '2026-07-09', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('104', '6', 'in', '19.000', '894.000', '92.00', '1748.00', '3', 'purchase', '103', NULL, NULL, '2026-07-09', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('105', '3', 'in', '58.000', '1092.500', '365.00', '21170.00', '3', 'purchase', '104', NULL, NULL, '2026-07-09', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('106', '4', 'in', '59.000', '861.000', '365.00', '21535.00', '3', 'purchase', '105', NULL, NULL, '2026-07-09', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('107', '6', 'in', '18.000', '912.000', '92.00', '1656.00', '3', 'purchase', '106', NULL, NULL, '2026-07-11', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('108', '3', 'in', '62.000', '1154.500', '365.00', '22630.00', '3', 'purchase', '107', NULL, NULL, '2026-07-11', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('109', '2', 'in', '60.000', '1573.500', '365.00', '21900.00', '3', 'purchase', '108', NULL, NULL, '2026-07-11', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('110', '3', 'in', '61.000', '1215.500', '365.00', '22265.00', '3', 'purchase', '109', NULL, NULL, '2026-07-11', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('111', '6', 'in', '20.000', '932.000', '92.00', '1840.00', '3', 'purchase', '110', NULL, NULL, '2026-07-11', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('112', '3', 'in', '60.500', '1276.000', '365.00', '22082.50', '3', 'purchase', '111', NULL, NULL, '2026-07-11', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('113', '2', 'in', '60.000', '1633.500', '240.00', '14400.00', '3', 'purchase', '112', NULL, NULL, '2026-07-11', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('114', '1', 'in', '69.080', '406.390', '3340.00', '230727.20', '2', 'purchase', '113', NULL, NULL, '2026-07-12', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('115', '3', 'in', '59.000', '1335.000', '365.00', '21535.00', '3', 'purchase', '114', NULL, NULL, '2026-07-12', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('116', '6', 'in', '20.000', '952.000', '92.00', '1840.00', '3', 'purchase', '115', NULL, NULL, '2026-07-12', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('117', '6', 'in', '18.000', '970.000', '92.00', '1656.00', '3', 'purchase', '116', NULL, NULL, '2026-07-12', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('118', '6', 'in', '20.000', '990.000', '92.00', '1840.00', '3', 'purchase', '117', NULL, NULL, '2026-07-12', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('119', '6', 'in', '20.000', '1010.000', '92.00', '1840.00', '3', 'purchase', '118', NULL, NULL, '2026-07-12', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('120', '3', 'in', '61.000', '1396.000', '365.00', '22265.00', '3', 'purchase', '119', NULL, NULL, '2026-07-12', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('121', '6', 'in', '19.000', '1029.000', '92.00', '1748.00', '3', 'purchase', '120', NULL, NULL, '2026-07-12', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('122', '6', 'in', '20.000', '1049.000', '92.00', '1840.00', '3', 'purchase', '121', NULL, NULL, '2026-07-13', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('123', '6', 'in', '20.000', '1069.000', '92.00', '1840.00', '3', 'purchase', '122', NULL, NULL, '2026-07-13', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('124', '4', 'in', '61.000', '922.000', '365.00', '22265.00', '3', 'purchase', '123', NULL, NULL, '2026-07-13', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('125', '3', 'in', '62.000', '1458.000', '365.00', '22630.00', '3', 'purchase', '124', NULL, NULL, '2026-07-13', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('126', '2', 'in', '60.000', '1693.500', '240.00', '14400.00', '3', 'purchase', '125', NULL, NULL, '2026-07-13', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('127', '2', 'in', '63.000', '1756.500', '240.00', '15120.00', '3', 'purchase', '126', NULL, NULL, '2026-07-13', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('128', '4', 'in', '60.500', '982.500', '365.00', '22082.50', '3', 'purchase', '127', NULL, NULL, '2026-07-13', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('129', '2', 'in', '60.000', '1816.500', '240.00', '14400.00', '3', 'purchase', '128', NULL, NULL, '2026-07-13', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('130', '4', 'in', '60.500', '1043.000', '365.00', '22082.50', '3', 'purchase', '129', NULL, NULL, '2026-07-13', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('131', '6', 'in', '18.000', '1087.000', '92.00', '1656.00', '3', 'purchase', '130', NULL, NULL, '2026-07-14', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('132', '1', 'in', '68.480', '474.870', '3340.00', '228723.20', '2', 'purchase', '131', NULL, NULL, '2026-07-14', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('133', '6', 'in', '20.000', '1107.000', '92.00', '1840.00', '3', 'purchase', '132', NULL, NULL, '2026-07-14', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('134', '6', 'in', '20.000', '1127.000', '92.00', '1840.00', '3', 'purchase', '133', NULL, NULL, '2026-07-14', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('135', '6', 'in', '18.000', '1145.000', '92.00', '1656.00', '3', 'purchase', '134', NULL, NULL, '2026-07-14', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('136', '4', 'in', '61.000', '1104.000', '365.00', '22265.00', '3', 'purchase', '135', NULL, NULL, '2026-07-14', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('137', '6', 'in', '20.000', '1165.000', '92.00', '1840.00', '3', 'purchase', '136', NULL, NULL, '2026-07-15', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('138', '6', 'in', '20.000', '1185.000', '92.00', '1840.00', '3', 'purchase', '137', NULL, NULL, '2026-07-15', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('139', '4', 'in', '59.000', '1163.000', '365.00', '21535.00', '3', 'purchase', '138', NULL, NULL, '2026-07-15', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('140', '6', 'in', '20.000', '1205.000', '92.00', '1840.00', '3', 'purchase', '139', NULL, NULL, '2026-07-15', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('141', '6', 'in', '20.000', '1225.000', '92.00', '1840.00', '3', 'purchase', '140', NULL, NULL, '2026-07-15', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('142', '2', 'in', '60.000', '1876.500', '240.00', '14400.00', '3', 'purchase', '141', NULL, NULL, '2026-07-15', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('143', '2', 'in', '63.000', '1939.500', '240.00', '15120.00', '3', 'purchase', '142', NULL, NULL, '2026-07-15', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('144', '6', 'in', '20.000', '1245.000', '92.00', '1840.00', '3', 'purchase', '143', NULL, NULL, '2026-07-15', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('145', '4', 'in', '62.000', '1225.000', '365.00', '22630.00', '3', 'purchase', '144', NULL, NULL, '2026-07-15', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('146', '6', 'in', '20.000', '1265.000', '92.00', '1840.00', '3', 'purchase', '145', NULL, NULL, '2026-07-15', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('147', '1', 'in', '68.760', '543.630', '3400.00', '233784.00', '2', 'purchase', '146', NULL, NULL, '2026-07-15', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('148', '6', 'in', '18.000', '1283.000', '92.00', '1656.00', '3', 'purchase', '147', NULL, NULL, '2026-07-15', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('149', '6', 'in', '18.000', '1301.000', '92.00', '1656.00', '3', 'purchase', '148', NULL, NULL, '2026-07-16', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('150', '2', 'in', '60.000', '1999.500', '240.00', '14400.00', '3', 'purchase', '149', NULL, NULL, '2026-07-16', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('151', '2', 'in', '63.000', '2062.500', '240.00', '15120.00', '3', 'purchase', '150', NULL, NULL, '2026-07-16', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('152', '6', 'in', '20.000', '1321.000', '92.00', '1840.00', '3', 'purchase', '151', NULL, NULL, '2026-07-16', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES ('153', '6', 'in', '20.000', '1341.000', '92.00', '1840.00', '3', 'purchase', '152', NULL, NULL, '2026-07-16', '2026-07-16 15:23:26', '2026-07-16 15:23:26');

-- Table: job_batches
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: jobs
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: land_rent_payments
DROP TABLE IF EXISTS `land_rent_payments`;
CREATE TABLE `land_rent_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `land_rent_id` bigint unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `land_rent_payments_land_rent_id_foreign` (`land_rent_id`),
  KEY `land_rent_payments_recorded_by_foreign` (`recorded_by`),
  CONSTRAINT `land_rent_payments_land_rent_id_foreign` FOREIGN KEY (`land_rent_id`) REFERENCES `land_rents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `land_rent_payments_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: land_rents
DROP TABLE IF EXISTS `land_rents`;
CREATE TABLE `land_rents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `annual_amount` decimal(12,2) NOT NULL,
  `due_date` date NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: material_densities
DROP TABLE IF EXISTS `material_densities`;
CREATE TABLE `material_densities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `material_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'اسم المادة',
  `material_name_ar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'اسم المادة بالعربي',
  `density_kg_per_m3` decimal(10,3) NOT NULL COMMENT 'الكثافة (كجم/م³)',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'ملاحظات',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `material_densities_material_name_unique` (`material_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: material_densities
INSERT INTO `material_densities` (`id`, `material_name`, `material_name_ar`, `density_kg_per_m3`, `notes`, `created_at`, `updated_at`) VALUES ('1', 'Sand', 'رمل', '1378.000', 'كثافة الرمل المستخدمة في التحويل من كجم إلى م³', NULL, NULL);
INSERT INTO `material_densities` (`id`, `material_name`, `material_name_ar`, `density_kg_per_m3`, `notes`, `created_at`, `updated_at`) VALUES ('2', 'Gravel1', 'سن 1', '1258.000', 'كثافة الحصى 1 المستخدمة في التحويل من كجم إلى م³', NULL, NULL);
INSERT INTO `material_densities` (`id`, `material_name`, `material_name_ar`, `density_kg_per_m3`, `notes`, `created_at`, `updated_at`) VALUES ('3', 'Gravel2', 'سن 2', '1254.000', 'كثافة الحصى 2 المستخدمة في التحويل من كجم إلى م³', NULL, NULL);

-- Table: migrations
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: migrations
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('1', '0001_01_01_000000_create_users_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('2', '0001_01_01_000001_create_cache_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('3', '0001_01_01_000002_create_jobs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('4', '2024_01_15_000001_create_mix_recipes_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('5', '2024_01_15_000002_create_material_densities_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('6', '2025_01_01_000002_create_customers_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('7', '2025_01_01_000003_create_concrete_mixes_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('8', '2025_01_01_000004_create_suppliers_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('9', '2025_01_01_000005_create_inventory_items_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('10', '2025_01_01_000006_create_inventory_movements_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('11', '2025_01_01_000007_create_orders_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('12', '2025_01_01_000008_create_equipment_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('13', '2025_01_01_000009_create_rental_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('14', '2025_01_01_000010_create_hr_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('15', '2025_01_01_000011_create_expenses_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('16', '2025_01_01_000012_create_schedule_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('17', '2025_01_01_000013_create_supplier_financial_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('18', '2025_01_01_000014_create_financial_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('19', '2025_01_01_000015_create_receipts_audit_tables', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('20', '2025_01_01_000016_add_voucher_fields_to_receipts', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('21', '2025_01_01_000017_add_status_to_receipts', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('22', '2025_01_01_000018_add_invoice_image_to_supplier_purchases', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('23', '2025_01_01_000019_add_price_per_unit_to_inventory_items', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('24', '2025_01_01_000020_create_employee_borrows_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('25', '2025_01_01_000021_add_borrow_deductions_to_payroll', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('26', '2026_06_25_140211_create_contributors_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('27', '2026_06_25_140407_create_contributor_payments_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('28', '2026_06_27_001839_add_inventory_deduction_to_equipment_fuel_logs_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('29', '2026_06_30_000001_create_equipment_tools_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('30', '2026_06_30_000002_change_additives_unit_to_liters', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('31', '2026_06_30_000003_add_shift_fields_to_rental_contracts', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('32', '2026_06_30_000004_create_rental_shifts_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('33', '2026_07_06_140322_add_contributor_id_to_expenses_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('34', '2026_07_06_213717_create_expense_categories_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('35', '2026_07_06_215300_change_expenses_category_to_string', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('36', '2026_07_14_000001_create_order_expenses_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('37', '2026_07_14_110738_add_payment_type_to_supplier_payments_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('38', '2026_07_14_120000_create_neighboring_stations_table', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('39', '2026_07_15_000001_add_maintenance_tracking_to_equipment', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('40', '2026_07_15_075006_add_generator_to_equipment_type_enum', '1');
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES ('41', '2026_07_16_001536_add_material_prices_to_orders_table', '1');

-- Table: mix_recipes
DROP TABLE IF EXISTS `mix_recipes`;
CREATE TABLE `mix_recipes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cement_per_m3` int NOT NULL COMMENT 'كمية الاسمنت لكل متر مكعب (كجم)',
  `sand_kg` decimal(10,3) NOT NULL COMMENT 'كمية الرمل بالكيلوغرام',
  `gravel1_kg` decimal(10,3) NOT NULL COMMENT 'كمية الحصى 1 بالكيلوغرام',
  `gravel2_kg` decimal(10,3) NOT NULL COMMENT 'كمية الحصى 2 بالكيلوغرام',
  `cement_kg` decimal(10,3) NOT NULL COMMENT 'كمية الاسمنت بالكيلوغرام',
  `water_m3` decimal(10,3) NOT NULL COMMENT 'كمية الماء بالمتر المكعب',
  `additives_liter` decimal(10,3) NOT NULL COMMENT 'كمية المضافات باللتر',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'ملاحظات',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mix_recipes_cement_per_m3_unique` (`cement_per_m3`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: mix_recipes
INSERT INTO `mix_recipes` (`id`, `cement_per_m3`, `sand_kg`, `gravel1_kg`, `gravel2_kg`, `cement_kg`, `water_m3`, `additives_liter`, `notes`, `created_at`, `updated_at`) VALUES ('1', '350', '720.000', '440.000', '660.000', '350.000', '0.200', '4.500', 'خلطة قياسية 350', NULL, NULL);
INSERT INTO `mix_recipes` (`id`, `cement_per_m3`, `sand_kg`, `gravel1_kg`, `gravel2_kg`, `cement_kg`, `water_m3`, `additives_liter`, `notes`, `created_at`, `updated_at`) VALUES ('2', '250', '820.000', '440.000', '660.000', '250.000', '0.190', '3.000', 'خلطة قياسية 250', NULL, NULL);

-- Table: neighboring_station_transactions
DROP TABLE IF EXISTS `neighboring_station_transactions`;
CREATE TABLE `neighboring_station_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `neighboring_station_id` bigint unsigned NOT NULL,
  `transaction_type` enum('rent_equipment','rent_vehicle','borrow_material','borrow_inventory','sell_concrete','service') COLLATE utf8mb4_unicode_ci NOT NULL,
  `direction` enum('incoming','outgoing') COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `payment_status` enum('paid','pending','partial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `paid_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `recorded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `neighboring_station_transactions_neighboring_station_id_foreign` (`neighboring_station_id`),
  KEY `neighboring_station_transactions_recorded_by_foreign` (`recorded_by`),
  CONSTRAINT `neighboring_station_transactions_neighboring_station_id_foreign` FOREIGN KEY (`neighboring_station_id`) REFERENCES `neighboring_stations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `neighboring_station_transactions_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: neighboring_stations
DROP TABLE IF EXISTS `neighboring_stations`;
CREATE TABLE `neighboring_stations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: order_expenses
DROP TABLE IF EXISTS `order_expenses`;
CREATE TABLE `order_expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `expense_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_expenses_order_id_foreign` (`order_id`),
  CONSTRAINT `order_expenses_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: orders
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned NOT NULL,
  `concrete_mix_id` bigint unsigned DEFAULT NULL,
  `concrete_type` enum('operational','complete') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity_m3` decimal(10,3) NOT NULL,
  `cement_deducted` decimal(10,3) DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_date` date NOT NULL,
  `delivery_time` time DEFAULT NULL,
  `status` enum('pending','scheduled','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(14,2) DEFAULT NULL,
  `payment_type` enum('cash','credit','mixed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cash_amount` decimal(14,2) DEFAULT NULL,
  `credit_amount` decimal(14,2) DEFAULT NULL,
  `credit_due_date` date DEFAULT NULL,
  `material_prices` json DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_concrete_mix_id_foreign` (`concrete_mix_id`),
  KEY `orders_created_by_foreign` (`created_by`),
  KEY `orders_delivery_date_index` (`delivery_date`),
  KEY `orders_status_index` (`status`),
  KEY `orders_customer_id_index` (`customer_id`),
  CONSTRAINT `orders_concrete_mix_id_foreign` FOREIGN KEY (`concrete_mix_id`) REFERENCES `concrete_mixes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: password_reset_tokens
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: payroll
DROP TABLE IF EXISTS `payroll`;
CREATE TABLE `payroll` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `period_month` tinyint NOT NULL,
  `period_year` year NOT NULL,
  `base_salary` decimal(12,2) NOT NULL,
  `overtime_pay` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_deductions` decimal(12,2) NOT NULL DEFAULT '0.00',
  `borrow_deductions` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net_salary` decimal(12,2) NOT NULL,
  `payment_date` date DEFAULT NULL,
  `status` enum('pending','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payroll_employee_id_period_month_period_year_unique` (`employee_id`,`period_month`,`period_year`),
  KEY `payroll_created_by_foreign` (`created_by`),
  CONSTRAINT `payroll_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: receipt_items
DROP TABLE IF EXISTS `receipt_items`;
CREATE TABLE `receipt_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `receipt_id` bigint unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(10,3) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `receipt_items_receipt_id_foreign` (`receipt_id`),
  CONSTRAINT `receipt_items_receipt_id_foreign` FOREIGN KEY (`receipt_id`) REFERENCES `receipts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: receipts
DROP TABLE IF EXISTS `receipts`;
CREATE TABLE `receipts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('in','out') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in',
  `amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','done') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `signed_image_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `receipt_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receipt_date` date NOT NULL,
  `total_amount` decimal(14,2) NOT NULL,
  `image_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `receipts_supplier_id_foreign` (`supplier_id`),
  KEY `receipts_recorded_by_foreign` (`recorded_by`),
  KEY `receipts_receipt_date_index` (`receipt_date`),
  CONSTRAINT `receipts_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `receipts_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rental_contracts
DROP TABLE IF EXISTS `rental_contracts`;
CREATE TABLE `rental_contracts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `equipment_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `car_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `driver_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hourly_price` decimal(12,2) DEFAULT NULL,
  `driver_allowance` decimal(12,2) DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `monthly_fee` decimal(12,2) DEFAULT NULL,
  `total_fee` decimal(14,2) DEFAULT NULL,
  `payment_type` enum('cash','credit','mixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `status` enum('active','expired','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rental_contracts_supplier_id_foreign` (`supplier_id`),
  CONSTRAINT `rental_contracts_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rental_maintenance
DROP TABLE IF EXISTS `rental_maintenance`;
CREATE TABLE `rental_maintenance` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rental_contract_id` bigint unsigned NOT NULL,
  `maintenance_date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost` decimal(12,2) NOT NULL,
  `deducted_from_rent` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rental_maintenance_rental_contract_id_foreign` (`rental_contract_id`),
  KEY `rental_maintenance_recorded_by_foreign` (`recorded_by`),
  CONSTRAINT `rental_maintenance_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rental_maintenance_rental_contract_id_foreign` FOREIGN KEY (`rental_contract_id`) REFERENCES `rental_contracts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rental_shifts
DROP TABLE IF EXISTS `rental_shifts`;
CREATE TABLE `rental_shifts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rental_contract_id` bigint unsigned NOT NULL,
  `shift_date` date NOT NULL,
  `hours` decimal(8,2) NOT NULL DEFAULT '0.00',
  `hourly_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `hours_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `gratuities` decimal(12,2) NOT NULL DEFAULT '0.00',
  `cards_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `driver_allowance` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_cost` decimal(14,2) NOT NULL DEFAULT '0.00',
  `fuel_liters` decimal(8,3) DEFAULT NULL,
  `fuel_inventory_item_id` bigint unsigned DEFAULT NULL,
  `fuel_inventory_movement_id` bigint unsigned DEFAULT NULL,
  `fuel_cost` decimal(12,2) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rental_shifts_rental_contract_id_foreign` (`rental_contract_id`),
  KEY `rental_shifts_fuel_inventory_item_id_foreign` (`fuel_inventory_item_id`),
  KEY `rental_shifts_fuel_inventory_movement_id_foreign` (`fuel_inventory_movement_id`),
  KEY `rental_shifts_recorded_by_foreign` (`recorded_by`),
  CONSTRAINT `rental_shifts_fuel_inventory_item_id_foreign` FOREIGN KEY (`fuel_inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rental_shifts_fuel_inventory_movement_id_foreign` FOREIGN KEY (`fuel_inventory_movement_id`) REFERENCES `inventory_movements` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rental_shifts_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rental_shifts_rental_contract_id_foreign` FOREIGN KEY (`rental_contract_id`) REFERENCES `rental_contracts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: sessions
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: supplier_payments
DROP TABLE IF EXISTS `supplier_payments`;
CREATE TABLE `supplier_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint unsigned NOT NULL,
  `supplier_purchase_id` bigint unsigned DEFAULT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `payment_method` enum('cash','bank_transfer','check') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `payment_type` enum('payment','deduction') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'payment',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_payments_supplier_id_foreign` (`supplier_id`),
  KEY `supplier_payments_supplier_purchase_id_foreign` (`supplier_purchase_id`),
  KEY `supplier_payments_recorded_by_foreign` (`recorded_by`),
  KEY `supplier_payments_payment_date_index` (`payment_date`),
  CONSTRAINT `supplier_payments_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supplier_payments_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `supplier_payments_supplier_purchase_id_foreign` FOREIGN KEY (`supplier_purchase_id`) REFERENCES `supplier_purchases` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: supplier_purchase_items
DROP TABLE IF EXISTS `supplier_purchase_items`;
CREATE TABLE `supplier_purchase_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `supplier_purchase_id` bigint unsigned NOT NULL,
  `inventory_item_id` bigint unsigned DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_purchase_items_supplier_purchase_id_foreign` (`supplier_purchase_id`),
  KEY `supplier_purchase_items_inventory_item_id_foreign` (`inventory_item_id`),
  CONSTRAINT `supplier_purchase_items_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supplier_purchase_items_supplier_purchase_id_foreign` FOREIGN KEY (`supplier_purchase_id`) REFERENCES `supplier_purchases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=154 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: supplier_purchase_items
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('1', '1', '2', 'رمل - سيارة 9973-7558', '60.000', 'م', '240.00', '14400.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('2', '2', '3', 'سن1 - سيارة 9973-7558', '60.000', 'م', '365.00', '21900.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('3', '3', '4', 'سن2 - سيارة 9973-9973', '60.000', 'م', '365.00', '21900.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('4', '4', '3', 'سن1 - سيارة 7818-1738', '60.000', 'م', '380.00', '22800.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('5', '5', '4', 'سن2 - سيارة 7818-1738', '60.000', 'م', '380.00', '22800.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('6', '6', '2', 'رمل - سيارة 3724', '140.000', 'م', '185.00', '25900.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('7', '7', '1', 'اسمنت - سيارة 68860-758', '68.860', 'طن', '4000.00', '275440.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('8', '8', '5', 'ماده - سيارة 6749', '1000.000', 'لتر', '16.00', '16000.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('9', '8', '5', 'ماده - سيارة 6749', '1000.000', 'لتر', '38.00', '38000.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('10', '9', '6', 'مياه', '120.000', 'م', '92.00', '11040.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('11', '10', '6', 'مياه - سيارة 7695', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('12', '11', '3', 'سن1 - سيارة 7558-9973', '60.000', 'م', '365.00', '21900.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('13', '12', '4', 'سن2 - سيارة 7558-9973', '60.000', 'م', '365.00', '21900.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('14', '13', '2', 'رمل - سيارة 7558-9973', '60.000', 'م', '240.00', '14400.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('15', '14', '3', 'سن1 - سيارة 7558-9973', '60.000', 'م', '365.00', '21900.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('16', '15', '2', 'رمل - سيارة 9973-7558', '60.000', 'م', '240.00', '14400.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('17', '16', '3', 'سن1 - سيارة 9973-7558', '60.000', 'م', '365.00', '21900.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('18', '17', '6', 'مياه - سيارة 222', '18.000', 'م', '92.00', '1656.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('19', '18', '6', 'مياه - سيارة 222', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('20', '19', '6', 'مياه - سيارة 1327', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('21', '20', '5', 'ماده - سيارة 6749', '4000.000', 'لتر', '16.00', '64000.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('22', '21', '6', 'مياه - سيارة 1327', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('23', '22', '6', 'مياه - سيارة 7695', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('24', '23', '6', 'مياه - سيارة 1322', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('25', '24', '6', 'مياه - سيارة 1322', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('26', '25', '6', 'مياه - سيارة 222', '18.000', 'م', '92.00', '1656.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('27', '26', '6', 'مياه - سيارة 222', '18.000', 'م', '92.00', '1656.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('28', '27', '1', 'اسمنت - سيارة 2612-549', '58.800', 'طن', '4000.00', '235200.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('29', '28', '3', 'سن1 - سيارة 5767-2217', '60.000', 'م', '365.00', '21900.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('30', '29', '4', 'سن2 - سيارة 9936-5635', '63.000', 'م', '365.00', '22995.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('31', '30', '3', 'سن1 - سيارة 7818-1738', '62.000', 'م', '365.00', '22630.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('32', '31', '6', 'مياه - سيارة 7695', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('33', '32', '3', 'سن1 - سيارة 7818-3455', '61.000', 'م', '365.00', '22265.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('34', '33', '3', 'سن1 - سيارة 7818-3455', '61.000', 'م', '365.00', '22265.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('35', '34', '3', 'سن1 - سيارة 7818-1783', '62.000', 'م', '365.00', '22630.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('36', '35', '2', 'رمل - سيارة 6299-6299', '59.000', 'م', '230.00', '13570.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('37', '36', '2', 'رمل - سيارة 1821-6299', '59.000', 'م', '230.00', '13570.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('38', '37', '2', 'رمل - سيارة 1821-6299', '59.000', 'م', '230.00', '13570.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('39', '38', '6', 'مياه - سيارة 222', '18.000', 'م', '92.00', '1656.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('40', '39', '6', 'مياه - سيارة 1327', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('41', '40', '2', 'رمل - سيارة 1563-7691', '61.000', 'م', '230.00', '14030.00', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('42', '41', '2', 'رمل - سيارة 1563-7691', '61.000', 'م', '230.00', '14030.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('43', '42', '4', 'سن2 - سيارة 1738-7818', '62.000', 'م', '365.00', '22630.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('44', '43', '4', 'سن2 - سيارة 7818-1783', '62.000', 'م', '365.00', '22630.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('45', '44', '1', 'اسمنت - سيارة 3673-5628', '71.660', 'طن', '3800.00', '272308.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('46', '45', '6', 'مياه - سيارة 1327', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('47', '46', '4', 'سن2 - سيارة 7818-3455', '61.000', 'م', '365.00', '22265.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('48', '47', '2', 'رمل - سيارة 1821-6299', '59.000', 'م', '240.00', '14160.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('49', '48', '2', 'رمل - سيارة 1563-6791', '61.000', 'م', '240.00', '14640.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('50', '49', '2', 'رمل - سيارة 1563-6791', '61.000', 'م', '240.00', '14640.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('51', '50', '6', 'مياه - سيارة 222', '18.000', 'م', '92.00', '1656.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('52', '51', '4', 'سن2 - سيارة 7818-3455', '61.000', 'م', '365.00', '22265.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('53', '52', '1', 'اسمنت - سيارة 8365-758', '69.040', 'طن', '3800.00', '262352.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('54', '53', '1', 'اسمنت - سيارة 8315-758', '68.950', 'طن', '3800.00', '262010.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('55', '54', '6', 'مياه - سيارة 222', '18.000', 'م', '92.00', '1656.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('56', '55', '6', 'مياه - سيارة 7695', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('57', '56', '6', 'مياه - سيارة 8615', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('58', '57', '2', 'رمل - سيارة 1563-7691', '61.000', 'م', '240.00', '14640.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('59', '58', '4', 'سن2 - سيارة 9182-4541', '66.000', 'م', '365.00', '24090.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('60', '59', '6', 'مياه - سيارة 2792', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('61', '60', '6', 'مياه - سيارة 8318', '19.000', 'م', '92.00', '1748.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('62', '61', '6', 'مياه - سيارة 1327', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('63', '62', '6', 'مياه - سيارة 222', '18.000', 'م', '92.00', '1656.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('64', '63', '2', 'رمل - سيارة 8854-9948', '61.000', 'م', '240.00', '14640.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('65', '64', '3', 'سن1 - سيارة 7818-3455', '61.000', 'م', '365.00', '22265.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('66', '65', '2', 'رمل - سيارة 6299-1821', '59.000', 'م', '240.00', '14160.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('67', '66', '6', 'مياه - سيارة 222', '18.000', 'م', '92.00', '1656.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('68', '67', '6', 'مياه - سيارة 222', '18.000', 'م', '92.00', '1656.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('69', '68', '4', 'سن2 - سيارة 7818-1738', '61.000', 'م', '365.00', '22265.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('70', '69', '6', 'مياه - سيارة 9821', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('71', '70', '6', 'مياه - سيارة 7695', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('72', '71', '2', 'رمل - سيارة 6299', '59.000', 'م', '240.00', '14160.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('73', '72', '2', 'رمل - سيارة 6299-1821', '59.000', 'م', '240.00', '14160.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('74', '73', '2', 'رمل - سيارة 7691-1563', '61.000', 'م', '240.00', '14640.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('75', '74', '3', 'سن1 - سيارة 1489-7864', '60.500', 'م', '365.00', '22082.50', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('76', '75', '2', 'رمل - سيارة 1489-7864', '60.500', 'م', '240.00', '14520.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('77', '76', '4', 'سن2 - سيارة 7818-1738', '62.000', 'م', '365.00', '22630.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('78', '77', '3', 'سن1 - سيارة 7818-1738', '62.000', 'م', '365.00', '22630.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('79', '78', '2', 'رمل - سيارة 7691-1563', '61.000', 'م', '240.00', '14640.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('80', '79', '3', 'سن1 - سيارة 7818-3455', '61.000', 'م', '365.00', '22265.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('81', '80', '2', 'رمل - سيارة 3797-7925', '56.000', 'م', '240.00', '13440.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('82', '81', '4', 'سن2 - سيارة 7818-1738', '59.000', 'م', '365.00', '21535.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('83', '82', '2', 'رمل - سيارة 1126-7174', '63.000', 'م', '240.00', '15120.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('84', '83', '6', 'مياه - سيارة 8318', '19.000', 'م', '92.00', '1748.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('85', '84', '6', 'مياه - سيارة 8318', '19.000', 'م', '92.00', '1748.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('86', '85', '6', 'مياه - سيارة 7695', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('87', '86', '3', 'سن1 - سيارة 7818-3455', '61.000', 'م', '365.00', '22265.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('88', '87', '3', 'سن1 - سيارة 7818/1738', '62.000', 'م', '365.00', '22630.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('89', '88', '2', 'رمل - سيارة 3797-7925', '56.000', 'م', '240.00', '13440.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('90', '89', '4', 'سن2 - سيارة 3551-9582', '65.000', 'م', '365.00', '23725.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('91', '90', '6', 'مياه - سيارة 8697', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('92', '91', '6', 'مياه - سيارة 5478', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('93', '92', '5', 'ماده - سيارة 4356', '6000.000', 'لتر', '16.00', '96000.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('94', '93', '6', 'مياه - سيارة 2562', '19.000', 'م', '92.00', '1748.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('95', '94', '6', 'مياه - سيارة 9821', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('96', '95', '2', 'رمل - سيارة 926-1773', '57.000', 'م', '240.00', '13680.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('97', '96', '6', 'مياه - سيارة 7695', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('98', '97', '6', 'مياه - سيارة 5478', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('99', '98', '6', 'مياه - سيارة 1327', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('100', '99', '6', 'مياه - سيارة 5478', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('101', '100', '6', 'مياه - سيارة 222', '18.000', 'م', '92.00', '1656.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('102', '101', '3', 'سن1 - سيارة 5735-7662', '61.000', 'م', '295.00', '17995.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('103', '102', '6', 'مياه - سيارة 2562', '19.000', 'م', '92.00', '1748.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('104', '103', '6', 'مياه - سيارة 2562', '19.000', 'م', '92.00', '1748.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('105', '104', '3', 'سن1 - سيارة 7818-3455', '58.000', 'م', '365.00', '21170.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('106', '105', '4', 'سن2 - سيارة 7818-1738', '59.000', 'م', '365.00', '21535.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('107', '106', '6', 'مياه - سيارة 222', '18.000', 'م', '92.00', '1656.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('108', '107', '3', 'سن1 - سيارة 7818-1738', '62.000', 'م', '365.00', '22630.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('109', '108', '2', 'رمل - سيارة 3874-5213', '60.000', 'م', '365.00', '21900.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('110', '109', '3', 'سن1 - سيارة 7818-3455', '61.000', 'م', '365.00', '22265.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('111', '110', '6', 'مياه - سيارة 5478', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('112', '111', '3', 'سن1 - سيارة 1489-7864', '60.500', 'م', '365.00', '22082.50', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('113', '112', '2', 'رمل - سيارة 5213-3874', '60.000', 'م', '240.00', '14400.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('114', '113', '1', 'اسمنت - سيارة 8365-758', '69.080', 'م', '3340.00', '230727.20', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('115', '114', '3', 'سن1 - سيارة 7818-1738', '59.000', 'م', '365.00', '21535.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('116', '115', '6', 'مياه - سيارة 5478', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('117', '116', '6', 'مياه - سيارة 3614', '18.000', 'م', '92.00', '1656.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('118', '117', '6', 'مياه - سيارة 7695', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('119', '118', '6', 'مياه - سيارة 7695', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('120', '119', '3', 'سن1 - سيارة 7818-3455', '61.000', 'م', '365.00', '22265.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('121', '120', '6', 'مياه - سيارة 2562', '19.000', 'م', '92.00', '1748.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('122', '121', '6', 'مياه - سيارة 5478', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('123', '122', '6', 'مياه - سيارة 7685', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('124', '123', '4', 'سن2 - سيارة 7818-3455', '61.000', 'م', '365.00', '22265.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('125', '124', '3', 'سن1 - سيارة 7818-1738', '62.000', 'م', '365.00', '22630.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('126', '125', '2', 'رمل - سيارة 3874-5213', '60.000', 'م', '240.00', '14400.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('127', '126', '2', 'رمل - سيارة 8625-6549', '63.000', 'م', '240.00', '15120.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('128', '127', '4', 'سن2 - سيارة 1489-7864', '60.500', 'م', '365.00', '22082.50', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('129', '128', '2', 'رمل - سيارة 3874-5213', '60.000', 'م', '240.00', '14400.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('130', '129', '4', 'سن2 - سيارة 1489-7864', '60.500', 'م', '365.00', '22082.50', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('131', '130', '6', 'مياه - سيارة 222', '18.000', 'م', '92.00', '1656.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('132', '131', '1', 'اسمنت - سيارة 3673-5628', '68.480', 'طن', '3340.00', '228723.20', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('133', '132', '6', 'مياه - سيارة 7685', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('134', '133', '6', 'مياه - سيارة 6567', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('135', '134', '6', 'مياه - سيارة 222', '18.000', 'م', '92.00', '1656.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('136', '135', '4', 'سن2 - سيارة 7818-3455', '61.000', 'م', '365.00', '22265.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('137', '136', '6', 'مياه - سيارة 6567', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('138', '137', '6', 'مياه - سيارة 1327', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('139', '138', '4', 'سن2 - سيارة 9879-1559', '59.000', 'م', '365.00', '21535.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('140', '139', '6', 'مياه - سيارة 1327', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('141', '140', '6', 'مياه - سيارة 1327', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('142', '141', '2', 'رمل - سيارة 3874-5213', '60.000', 'م', '240.00', '14400.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('143', '142', '2', 'رمل - سيارة 8625-6549', '63.000', 'م', '240.00', '15120.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('144', '143', '6', 'مياه - سيارة 1327', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('145', '144', '4', 'سن2 - سيارة 7818-1738', '62.000', 'م', '365.00', '22630.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('146', '145', '6', 'مياه - سيارة 2562', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('147', '146', '1', 'اسمنت - سيارة 8365-758', '68.760', 'طن', '3400.00', '233784.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('148', '147', '6', 'مياه - سيارة 222', '18.000', 'م', '92.00', '1656.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('149', '148', '6', 'مياه - سيارة 222', '18.000', 'م', '92.00', '1656.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('150', '149', '2', 'رمل - سيارة 3874-5213', '60.000', 'م', '240.00', '14400.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('151', '150', '2', 'رمل - سيارة 8625-6549', '63.000', 'م', '240.00', '15120.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('152', '151', '6', 'مياه - سيارة 2562', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES ('153', '152', '6', 'مياه - سيارة 2562', '20.000', 'م', '92.00', '1840.00', '2026-07-16 15:23:26', '2026-07-16 15:23:26');

-- Table: supplier_purchases
DROP TABLE IF EXISTS `supplier_purchases`;
CREATE TABLE `supplier_purchases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint unsigned NOT NULL,
  `invoice_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_date` date NOT NULL,
  `total_amount` decimal(14,2) NOT NULL,
  `payment_type` enum('cash','credit','mixed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cash_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `credit_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `due_date` date DEFAULT NULL,
  `status` enum('pending','partial','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `invoice_image_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supplier_purchases_created_by_foreign` (`created_by`),
  KEY `supplier_purchases_purchase_date_index` (`purchase_date`),
  KEY `supplier_purchases_supplier_id_index` (`supplier_id`),
  CONSTRAINT `supplier_purchases_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `supplier_purchases_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: supplier_purchases
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('1', '5', '1', '2026-04-29', '14400.00', 'credit', '0.00', '14400.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('2', '5', '2', '2026-04-29', '21900.00', 'credit', '0.00', '21900.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('3', '5', '3', '2026-04-29', '21900.00', 'credit', '0.00', '21900.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('4', '3', '4', '2026-04-18', '22800.00', 'credit', '0.00', '22800.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('5', '3', '5', '2026-04-18', '22800.00', 'credit', '0.00', '22800.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('6', '3', '6', '2026-03-06', '25900.00', 'credit', '0.00', '25900.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('7', '2', '7', '2026-04-19', '275440.00', 'credit', '0.00', '275440.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('8', '9', '8', '2026-04-20', '54000.00', 'credit', '0.00', '54000.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('9', '3', '9', '2026-05-03', '11040.00', 'credit', '0.00', '11040.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('10', '3', '10', '2026-05-06', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('11', '5', '11', '2026-05-11', '21900.00', 'credit', '0.00', '21900.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('12', '5', '12', '2026-05-11', '21900.00', 'credit', '0.00', '21900.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('13', '5', '13', '2026-05-11', '14400.00', 'credit', '0.00', '14400.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('14', '5', '14', '2026-06-13', '21900.00', 'credit', '0.00', '21900.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('15', '5', '15', '2026-05-13', '14400.00', 'credit', '0.00', '14400.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 16:05:52');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('16', '5', '16', '2026-01-12', '21900.00', 'credit', '0.00', '21900.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('17', '3', '17', '2026-05-17', '1656.00', 'credit', '0.00', '1656.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('18', '3', '18', '2026-05-17', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('19', '3', '19', '2026-05-18', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('20', '9', '20', '2026-06-14', '64000.00', 'credit', '0.00', '64000.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('21', '3', '21', '2026-06-17', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('22', '3', '22', '2026-06-17', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('23', '3', '23', '2026-06-17', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('24', '3', '24', '2026-06-17', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('25', '3', '25', '2026-06-20', '1656.00', 'credit', '0.00', '1656.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('26', '3', '26', '2026-06-20', '1656.00', 'credit', '0.00', '1656.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('27', '2', '27', '2026-06-20', '235200.00', 'credit', '0.00', '235200.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('28', '6', '31', '2026-06-20', '21900.00', 'credit', '0.00', '21900.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('29', '6', '32', '2026-06-20', '22995.00', 'credit', '0.00', '22995.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('30', '3', '33', '2026-06-20', '22630.00', 'credit', '0.00', '22630.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('31', '3', '34', '2026-06-21', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('32', '3', '35', '2026-06-20', '22265.00', 'credit', '0.00', '22265.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('33', '3', '36', '2026-06-21', '22265.00', 'credit', '0.00', '22265.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('34', '3', '37', '2026-06-21', '22630.00', 'credit', '0.00', '22630.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('35', '3', '38', '2026-06-21', '13570.00', 'credit', '0.00', '13570.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('36', '3', '39', '2026-06-21', '13570.00', 'credit', '0.00', '13570.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('37', '3', '40', '2026-06-21', '13570.00', 'credit', '0.00', '13570.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('38', '3', '41', '2026-06-21', '1656.00', 'credit', '0.00', '1656.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('39', '3', '44', '2026-06-21', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('40', '3', '45', '2026-06-21', '14030.00', 'credit', '0.00', '14030.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:25', '2026-07-16 15:23:25');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('41', '3', '46', '2026-06-21', '14030.00', 'credit', '0.00', '14030.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('42', '3', '47', '2026-06-21', '22630.00', 'credit', '0.00', '22630.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('43', '3', '48', '2026-06-21', '22630.00', 'credit', '0.00', '22630.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('44', '2', '49', '2026-06-22', '272308.00', 'credit', '0.00', '272308.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('45', '3', '51', '2026-06-22', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('46', '3', '52', '2026-06-22', '22265.00', 'credit', '0.00', '22265.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('47', '3', '53', '2026-06-23', '14160.00', 'credit', '0.00', '14160.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('48', '3', '54', '2026-06-23', '14640.00', 'credit', '0.00', '14640.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('49', '3', '55', '2026-06-23', '14640.00', 'credit', '0.00', '14640.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('50', '3', '56', '2026-06-23', '1656.00', 'credit', '0.00', '1656.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('51', '3', '57', '2026-06-23', '22265.00', 'credit', '0.00', '22265.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('52', '2', '58', '2026-06-23', '262352.00', 'credit', '0.00', '262352.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('53', '2', '59', '2026-06-23', '262010.00', 'credit', '0.00', '262010.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('54', '3', '60', '2026-06-23', '1656.00', 'credit', '0.00', '1656.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('55', '3', '61', '2026-06-23', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('56', '3', '62', '2026-06-23', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('57', '3', '63', '2026-06-23', '14640.00', 'credit', '0.00', '14640.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('58', '3', '64', '2026-06-23', '24090.00', 'credit', '0.00', '24090.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('59', '3', '66', '2026-06-24', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('60', '3', '67', '2026-06-25', '1748.00', 'credit', '0.00', '1748.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('61', '3', '69', '2026-06-24', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('62', '3', '71', '2026-06-25', '1656.00', 'credit', '0.00', '1656.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('63', '3', '72', '2026-06-25', '14640.00', 'credit', '0.00', '14640.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('64', '3', '73', '2026-06-25', '22265.00', 'credit', '0.00', '22265.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('65', '3', '74', '2026-06-25', '14160.00', 'credit', '0.00', '14160.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('66', '3', '76', '2026-06-25', '1656.00', 'credit', '0.00', '1656.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('67', '3', '77', '2026-06-27', '1656.00', 'credit', '0.00', '1656.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('68', '3', '78', '2026-06-27', '22265.00', 'credit', '0.00', '22265.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('69', '3', '79', '2026-06-27', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('70', '3', '80', '2026-06-27', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('71', '3', '81', '2026-06-27', '14160.00', 'credit', '0.00', '14160.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('72', '3', '82', '2026-06-27', '14160.00', 'credit', '0.00', '14160.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('73', '3', '83', '2026-06-27', '14640.00', 'credit', '0.00', '14640.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('74', '3', '84', '2026-06-28', '22082.50', 'credit', '0.00', '22082.50', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('75', '3', '85', '2026-06-28', '14520.00', 'credit', '0.00', '14520.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('76', '3', '86', '2026-06-28', '22630.00', 'credit', '0.00', '22630.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('77', '3', '87', '2026-06-28', '22630.00', 'credit', '0.00', '22630.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('78', '3', '88', '2026-06-28', '14640.00', 'credit', '0.00', '14640.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('79', '3', '89', '2026-06-28', '22265.00', 'credit', '0.00', '22265.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('80', '3', '90', '2026-06-28', '13440.00', 'credit', '0.00', '13440.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('81', '3', '91', '2026-06-29', '21535.00', 'credit', '0.00', '21535.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('82', '3', '92', '2026-06-28', '15120.00', 'credit', '0.00', '15120.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('83', '3', '93', '2026-06-29', '1748.00', 'credit', '0.00', '1748.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('84', '3', '94', '2026-06-29', '1748.00', 'credit', '0.00', '1748.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('85', '3', '95', '2026-06-29', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('86', '3', '96', '2026-06-29', '22265.00', 'credit', '0.00', '22265.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('87', '3', '97', '2026-06-29', '22630.00', 'credit', '0.00', '22630.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('88', '3', '99', '2026-06-30', '13440.00', 'credit', '0.00', '13440.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('89', '3', '100', '2026-06-30', '23725.00', 'credit', '0.00', '23725.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('90', '3', '101', '2026-06-30', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('91', '3', '102', '2026-06-30', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('92', '9', '103', '2026-06-30', '96000.00', 'credit', '0.00', '96000.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('93', '3', '104', '2026-07-01', '1748.00', 'credit', '0.00', '1748.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('94', '3', '105', '2026-07-01', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('95', '3', '106', '2026-07-02', '13680.00', 'credit', '0.00', '13680.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('96', '3', '107', '2026-07-05', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('97', '3', '108', '2026-07-05', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('98', '3', '109', '2026-07-06', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('99', '3', '110', '2026-07-08', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('100', '3', '111', '2026-07-08', '1656.00', 'credit', '0.00', '1656.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('101', '5', '112', '2026-07-09', '17995.00', 'credit', '0.00', '17995.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('102', '3', '114', '2026-07-09', '1748.00', 'credit', '0.00', '1748.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('103', '3', '115', '2026-07-09', '1748.00', 'credit', '0.00', '1748.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('104', '3', '116', '2026-07-09', '21170.00', 'credit', '0.00', '21170.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('105', '3', '117', '2026-07-09', '21535.00', 'credit', '0.00', '21535.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('106', '3', '120', '2026-07-11', '1656.00', 'credit', '0.00', '1656.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('107', '3', '121', '2026-07-11', '22630.00', 'credit', '0.00', '22630.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('108', '3', '123', '2026-07-11', '21900.00', 'credit', '0.00', '21900.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('109', '3', '124', '2026-07-11', '22265.00', 'credit', '0.00', '22265.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('110', '3', '125', '2026-07-11', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('111', '3', '126', '2026-07-11', '22082.50', 'credit', '0.00', '22082.50', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('112', '3', '127', '2026-07-11', '14400.00', 'credit', '0.00', '14400.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('113', '2', '128', '2026-07-12', '230727.20', 'credit', '0.00', '230727.20', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('114', '3', '129', '2026-07-12', '21535.00', 'credit', '0.00', '21535.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('115', '3', '131', '2026-07-12', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('116', '3', '132', '2026-07-12', '1656.00', 'credit', '0.00', '1656.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('117', '3', '133', '2026-07-12', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('118', '3', '134', '2026-07-12', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('119', '3', '135', '2026-07-12', '22265.00', 'credit', '0.00', '22265.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('120', '3', '136', '2026-07-12', '1748.00', 'credit', '0.00', '1748.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('121', '3', '137', '2026-07-13', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('122', '3', '138', '2026-07-13', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('123', '3', '139', '2026-07-13', '22265.00', 'credit', '0.00', '22265.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('124', '3', '140', '2026-07-13', '22630.00', 'credit', '0.00', '22630.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('125', '3', '141', '2026-07-13', '14400.00', 'credit', '0.00', '14400.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('126', '3', '142', '2026-07-13', '15120.00', 'credit', '0.00', '15120.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('127', '3', '143', '2026-07-13', '22082.50', 'credit', '0.00', '22082.50', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('128', '3', '144', '2026-07-13', '14400.00', 'credit', '0.00', '14400.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('129', '3', '145', '2026-07-13', '22082.50', 'credit', '0.00', '22082.50', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('130', '3', '146', '2026-07-14', '1656.00', 'credit', '0.00', '1656.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('131', '2', '147', '2026-07-14', '228723.20', 'credit', '0.00', '228723.20', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('132', '3', '148', '2026-07-14', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('133', '3', '149', '2026-07-14', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('134', '3', '150', '2026-07-14', '1656.00', 'credit', '0.00', '1656.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('135', '3', '151', '2026-07-14', '22265.00', 'credit', '0.00', '22265.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('136', '3', '152', '2026-07-15', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('137', '3', '153', '2026-07-15', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('138', '3', '154', '2026-07-15', '21535.00', 'credit', '0.00', '21535.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('139', '3', '155', '2026-07-15', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('140', '3', '156', '2026-07-15', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('141', '3', '157', '2026-07-15', '14400.00', 'credit', '0.00', '14400.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('142', '3', '158', '2026-07-15', '15120.00', 'credit', '0.00', '15120.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('143', '3', '159', '2026-07-15', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('144', '3', '160', '2026-07-15', '22630.00', 'credit', '0.00', '22630.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('145', '3', '161', '2026-07-15', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('146', '2', '162', '2026-07-15', '233784.00', 'credit', '0.00', '233784.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('147', '3', '163', '2026-07-15', '1656.00', 'credit', '0.00', '1656.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('148', '3', '164', '2026-07-16', '1656.00', 'credit', '0.00', '1656.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('149', '3', '166', '2026-07-16', '14400.00', 'credit', '0.00', '14400.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('150', '3', '167', '2026-07-16', '15120.00', 'credit', '0.00', '15120.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('151', '3', '168', '2026-07-16', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');
INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES ('152', '3', '169', '2026-07-16', '1840.00', 'credit', '0.00', '1840.00', '2026-08-15', 'pending', NULL, NULL, '1', '2026-07-16 15:23:26', '2026-07-16 15:23:26');

-- Table: suppliers
DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `materials` json DEFAULT NULL,
  `payment_type` enum('cash','credit','mixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `balance` decimal(14,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: suppliers
INSERT INTO `suppliers` (`id`, `name`, `phone`, `address`, `materials`, `payment_type`, `balance`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES ('2', 'جولدن يونيتد بلال ابو الدهب', NULL, NULL, '["اسمنت"]', 'credit', '2000544.40', NULL, '1', '2026-07-16 12:38:21', '2026-07-16 15:23:26');
INSERT INTO `suppliers` (`id`, `name`, `phone`, `address`, `materials`, `payment_type`, `balance`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES ('3', 'سلام', NULL, NULL, '[null]', 'credit', '1354517.00', NULL, '1', '2026-07-16 12:38:39', '2026-07-16 15:23:26');
INSERT INTO `suppliers` (`id`, `name`, `phone`, `address`, `materials`, `payment_type`, `balance`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES ('5', 'عمر ساري', NULL, NULL, '["سن ، رملة"]', 'credit', '178195.00', NULL, '1', '2026-07-16 12:39:11', '2026-07-16 16:03:56');
INSERT INTO `suppliers` (`id`, `name`, `phone`, `address`, `materials`, `payment_type`, `balance`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES ('6', 'محمد فتحي', NULL, NULL, '["سن ، رملة"]', 'credit', '44895.00', NULL, '1', '2026-07-16 12:39:24', '2026-07-16 15:23:25');
INSERT INTO `suppliers` (`id`, `name`, `phone`, `address`, `materials`, `payment_type`, `balance`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES ('9', 'هاي كيم', NULL, NULL, '["مادة"]', 'credit', '214000.00', NULL, '1', '2026-07-16 12:40:03', '2026-07-16 15:23:26');

-- Table: treasury_transactions
DROP TABLE IF EXISTS `treasury_transactions`;
CREATE TABLE `treasury_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('in','out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `balance_after` decimal(14,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `treasury_transactions_recorded_by_foreign` (`recorded_by`),
  KEY `treasury_transactions_transaction_date_index` (`transaction_date`),
  KEY `treasury_transactions_type_index` (`type`),
  CONSTRAINT `treasury_transactions_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: treasury_transactions
INSERT INTO `treasury_transactions` (`id`, `type`, `category`, `amount`, `balance_after`, `transaction_date`, `description`, `reference_type`, `reference_id`, `recorded_by`, `created_at`, `updated_at`) VALUES ('1', 'out', 'supplier_payment', '14400.00', '-14400.00', '2026-07-16', 'تسديد مستحقات للمورد: عمر ساري', 'credit', '1', '2', '2026-07-16 16:03:56', '2026-07-16 16:03:56');
INSERT INTO `treasury_transactions` (`id`, `type`, `category`, `amount`, `balance_after`, `transaction_date`, `description`, `reference_type`, `reference_id`, `recorded_by`, `created_at`, `updated_at`) VALUES ('2', 'out', 'مشتريات مخزون المعدات', '1050.00', '-15450.00', '2026-07-16', 'رصيد افتتاحي: 1 بستلة من شحم', NULL, NULL, NULL, '2026-07-16 16:23:16', '2026-07-16 16:23:16');
INSERT INTO `treasury_transactions` (`id`, `type`, `category`, `amount`, `balance_after`, `transaction_date`, `description`, `reference_type`, `reference_id`, `recorded_by`, `created_at`, `updated_at`) VALUES ('3', 'in', 'استهلاك مخزون المعدات', '1050.00', '-16500.00', '2026-07-16', 'استهلاك 1 بستلة من شحم', NULL, NULL, NULL, '2026-07-16 16:23:47', '2026-07-16 16:23:47');

-- Table: users
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','accountant','engineer','inventory_officer','inventory_manager') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'engineer',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table: users
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `phone`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES ('1', 'Omar - Inventory Manager', 'omar@newsolidup.com', NULL, '$2y$12$9AtYyeDO9y0dODmRXPnGQuxhGaun2SyEyoK/oSkEnEjmLxRlDS3Q6', 'inventory_manager', NULL, '1', NULL, '2026-07-16 15:21:30', '2026-07-16 15:21:30');
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `phone`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES ('2', 'Mohamed - Accountant', 'mohamed@newsolidup.com', NULL, '$2y$12$g8SL3aKvWRRf5QkAhzcX5OxqWBOytMgxD61nuY04bm7PLvbMKHy2q', 'accountant', NULL, '1', NULL, '2026-07-16 15:21:31', '2026-07-16 15:21:31');

-- Table: weekly_schedule_entries
DROP TABLE IF EXISTS `weekly_schedule_entries`;
CREATE TABLE `weekly_schedule_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `weekly_schedule_id` bigint unsigned NOT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `site_location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity_m3` decimal(10,3) NOT NULL,
  `delivery_date` date NOT NULL,
  `delivery_time` time DEFAULT NULL,
  `engineer_notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `weekly_schedule_entries_weekly_schedule_id_foreign` (`weekly_schedule_id`),
  KEY `weekly_schedule_entries_order_id_foreign` (`order_id`),
  KEY `weekly_schedule_entries_customer_id_foreign` (`customer_id`),
  KEY `weekly_schedule_entries_delivery_date_index` (`delivery_date`),
  CONSTRAINT `weekly_schedule_entries_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `weekly_schedule_entries_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `weekly_schedule_entries_weekly_schedule_id_foreign` FOREIGN KEY (`weekly_schedule_id`) REFERENCES `weekly_schedules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: weekly_schedules
DROP TABLE IF EXISTS `weekly_schedules`;
CREATE TABLE `weekly_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `week_start` date NOT NULL,
  `week_end` date NOT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `status` enum('draft','published','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `weekly_schedules_created_by_foreign` (`created_by`),
  KEY `weekly_schedules_week_start_index` (`week_start`),
  CONSTRAINT `weekly_schedules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

