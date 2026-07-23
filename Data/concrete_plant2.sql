-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 23, 2026 at 12:43 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `concrete_plant2`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `normal_hours` decimal(5,2) NOT NULL DEFAULT '0.00',
  `overtime_hours` decimal(5,2) NOT NULL DEFAULT '0.00',
  `status` enum('present','absent','half_day') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'present',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `concrete_mixes`
--

CREATE TABLE `concrete_mixes` (
  `id` bigint UNSIGNED NOT NULL,
  `strength` int NOT NULL,
  `cement_per_m3` decimal(8,3) NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `concrete_mixes`
--

INSERT INTO `concrete_mixes` (`id`, `strength`, `cement_per_m3`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 180, 250.000, 'خرسانة 180 - 250 كغ/م³', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09'),
(2, 250, 350.000, 'خرسانة 250 - 350 كغ/م³', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09'),
(3, 300, 350.000, 'خرسانة 300 - 350 كغ/م³', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09');

-- --------------------------------------------------------

--
-- Table structure for table `contributors`
--

CREATE TABLE `contributors` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `share_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `share_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `national_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contributors`
--

INSERT INTO `contributors` (`id`, `name`, `phone`, `share_percentage`, `share_amount`, `national_id`, `address`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'بلال يحيى', NULL, 0.00, 0.00, NULL, NULL, NULL, 1, '2026-07-23 12:29:15', '2026-07-23 12:29:15'),
(2, 'سيد حسن', NULL, 0.00, 0.00, NULL, NULL, NULL, 1, '2026-07-23 12:29:26', '2026-07-23 12:29:26'),
(3, 'احمد سعد', NULL, 0.00, 0.00, NULL, NULL, NULL, 1, '2026-07-23 12:29:38', '2026-07-23 12:29:38');

-- --------------------------------------------------------

--
-- Table structure for table `contributor_payments`
--

CREATE TABLE `contributor_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `contributor_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('cash','bank_transfer','check') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `treasury_transaction_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `credits`
--

CREATE TABLE `credits` (
  `id` bigint UNSIGNED NOT NULL,
  `creditable_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `creditable_id` bigint UNSIGNED NOT NULL,
  `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint UNSIGNED DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `due_date` date NOT NULL,
  `paid_date` date DEFAULT NULL,
  `status` enum('pending','paid','overdue') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `credits`
--

INSERT INTO `credits` (`id`, `creditable_type`, `creditable_id`, `reference_type`, `reference_id`, `amount`, `due_date`, `paid_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'supplier', 1, 'purchase', 1, 14400.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(2, 'supplier', 1, 'purchase', 2, 21900.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(3, 'supplier', 1, 'purchase', 3, 21900.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(4, 'supplier', 2, 'purchase', 4, 22800.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(5, 'supplier', 2, 'purchase', 5, 22800.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(6, 'supplier', 2, 'purchase', 6, 25900.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(7, 'supplier', 3, 'purchase', 7, 275440.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(8, 'supplier', 4, 'purchase', 8, 54000.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(9, 'supplier', 2, 'purchase', 9, 11040.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(10, 'supplier', 2, 'purchase', 10, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(11, 'supplier', 1, 'purchase', 11, 21900.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(12, 'supplier', 1, 'purchase', 12, 21900.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(13, 'supplier', 1, 'purchase', 13, 14400.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(14, 'supplier', 1, 'purchase', 14, 21900.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(15, 'supplier', 1, 'purchase', 15, 14400.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(16, 'supplier', 1, 'purchase', 16, 21900.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(17, 'supplier', 2, 'purchase', 17, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(18, 'supplier', 2, 'purchase', 18, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(19, 'supplier', 2, 'purchase', 19, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(20, 'supplier', 4, 'purchase', 20, 64000.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(21, 'supplier', 2, 'purchase', 21, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(22, 'supplier', 2, 'purchase', 22, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(23, 'supplier', 2, 'purchase', 23, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(24, 'supplier', 2, 'purchase', 24, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(25, 'supplier', 2, 'purchase', 25, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(26, 'supplier', 2, 'purchase', 26, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(27, 'supplier', 3, 'purchase', 27, 235200.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(28, 'supplier', 5, 'purchase', 28, 21900.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(29, 'supplier', 5, 'purchase', 29, 22995.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(30, 'supplier', 2, 'purchase', 30, 22630.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(31, 'supplier', 2, 'purchase', 31, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(32, 'supplier', 2, 'purchase', 32, 22265.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(33, 'supplier', 2, 'purchase', 33, 22265.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(34, 'supplier', 2, 'purchase', 34, 22630.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(35, 'supplier', 2, 'purchase', 35, 13570.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(36, 'supplier', 2, 'purchase', 36, 13570.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(37, 'supplier', 2, 'purchase', 37, 13570.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(38, 'supplier', 2, 'purchase', 38, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(39, 'supplier', 2, 'purchase', 39, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(40, 'supplier', 2, 'purchase', 40, 14030.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(41, 'supplier', 2, 'purchase', 41, 14030.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(42, 'supplier', 2, 'purchase', 42, 22630.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(43, 'supplier', 2, 'purchase', 43, 22630.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(44, 'supplier', 3, 'purchase', 44, 272308.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(45, 'supplier', 2, 'purchase', 45, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(46, 'supplier', 2, 'purchase', 46, 22265.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(47, 'supplier', 2, 'purchase', 47, 14160.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(48, 'supplier', 2, 'purchase', 48, 14640.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(49, 'supplier', 2, 'purchase', 49, 14640.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(50, 'supplier', 2, 'purchase', 50, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(51, 'supplier', 2, 'purchase', 51, 22265.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(52, 'supplier', 3, 'purchase', 52, 262352.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(53, 'supplier', 3, 'purchase', 53, 262010.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(54, 'supplier', 2, 'purchase', 54, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(55, 'supplier', 2, 'purchase', 55, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(56, 'supplier', 2, 'purchase', 56, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(57, 'supplier', 2, 'purchase', 57, 14640.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(58, 'supplier', 2, 'purchase', 58, 24090.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(59, 'supplier', 2, 'purchase', 59, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(60, 'supplier', 2, 'purchase', 60, 1748.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(61, 'supplier', 2, 'purchase', 61, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(62, 'supplier', 2, 'purchase', 62, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(63, 'supplier', 2, 'purchase', 63, 14640.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(64, 'supplier', 2, 'purchase', 64, 22265.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(65, 'supplier', 2, 'purchase', 65, 14160.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(66, 'supplier', 2, 'purchase', 66, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(67, 'supplier', 2, 'purchase', 67, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(68, 'supplier', 2, 'purchase', 68, 22265.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(69, 'supplier', 2, 'purchase', 69, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(70, 'supplier', 2, 'purchase', 70, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(71, 'supplier', 2, 'purchase', 71, 14160.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(72, 'supplier', 2, 'purchase', 72, 14160.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(73, 'supplier', 2, 'purchase', 73, 14640.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(74, 'supplier', 2, 'purchase', 74, 22082.50, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(75, 'supplier', 2, 'purchase', 75, 220825.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(76, 'supplier', 2, 'purchase', 76, 22630.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(77, 'supplier', 2, 'purchase', 77, 22630.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(78, 'supplier', 2, 'purchase', 78, 14640.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(79, 'supplier', 2, 'purchase', 79, 22265.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(80, 'supplier', 2, 'purchase', 80, 13440.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(81, 'supplier', 2, 'purchase', 81, 21535.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(82, 'supplier', 2, 'purchase', 82, 15120.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(83, 'supplier', 2, 'purchase', 83, 1748.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(84, 'supplier', 2, 'purchase', 84, 1748.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(85, 'supplier', 2, 'purchase', 85, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(86, 'supplier', 2, 'purchase', 86, 22265.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(87, 'supplier', 2, 'purchase', 87, 22630.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(88, 'supplier', 2, 'purchase', 88, 13440.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(89, 'supplier', 2, 'purchase', 89, 23725.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(90, 'supplier', 2, 'purchase', 90, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(91, 'supplier', 2, 'purchase', 91, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(92, 'supplier', 4, 'purchase', 92, 80000.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(93, 'supplier', 2, 'purchase', 93, 1748.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(94, 'supplier', 2, 'purchase', 94, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(95, 'supplier', 2, 'purchase', 95, 13680.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(96, 'supplier', 2, 'purchase', 96, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(97, 'supplier', 2, 'purchase', 97, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(98, 'supplier', 2, 'purchase', 98, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(99, 'supplier', 2, 'purchase', 99, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(100, 'supplier', 2, 'purchase', 100, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(101, 'supplier', 1, 'purchase', 101, 17995.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(102, 'supplier', 2, 'purchase', 102, 1748.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(103, 'supplier', 2, 'purchase', 103, 1748.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(104, 'supplier', 2, 'purchase', 104, 21170.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(105, 'supplier', 2, 'purchase', 105, 21535.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(106, 'supplier', 2, 'purchase', 106, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(107, 'supplier', 2, 'purchase', 107, 22630.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(108, 'supplier', 2, 'purchase', 108, 21900.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(109, 'supplier', 2, 'purchase', 109, 22265.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(110, 'supplier', 2, 'purchase', 110, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(111, 'supplier', 2, 'purchase', 111, 22082.50, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(112, 'supplier', 2, 'purchase', 112, 14400.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(113, 'supplier', 3, 'purchase', 113, 230727.20, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(114, 'supplier', 2, 'purchase', 114, 21535.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(115, 'supplier', 2, 'purchase', 115, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(116, 'supplier', 2, 'purchase', 116, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(117, 'supplier', 2, 'purchase', 117, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(118, 'supplier', 2, 'purchase', 118, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(119, 'supplier', 2, 'purchase', 119, 22265.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(120, 'supplier', 2, 'purchase', 120, 1748.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(121, 'supplier', 2, 'purchase', 121, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(122, 'supplier', 2, 'purchase', 122, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(123, 'supplier', 2, 'purchase', 123, 22265.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(124, 'supplier', 2, 'purchase', 124, 22630.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(125, 'supplier', 2, 'purchase', 125, 14400.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(126, 'supplier', 2, 'purchase', 126, 15120.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(127, 'supplier', 2, 'purchase', 127, 22082.50, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(128, 'supplier', 2, 'purchase', 128, 14400.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(129, 'supplier', 2, 'purchase', 129, 22082.50, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(130, 'supplier', 2, 'purchase', 130, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(131, 'supplier', 3, 'purchase', 131, 228723.20, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(132, 'supplier', 2, 'purchase', 132, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(133, 'supplier', 2, 'purchase', 133, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(134, 'supplier', 2, 'purchase', 134, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(135, 'supplier', 2, 'purchase', 135, 22265.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(136, 'supplier', 2, 'purchase', 136, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(137, 'supplier', 2, 'purchase', 137, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(138, 'supplier', 2, 'purchase', 138, 20805.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(139, 'supplier', 2, 'purchase', 139, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(140, 'supplier', 2, 'purchase', 140, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(141, 'supplier', 2, 'purchase', 141, 14400.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(142, 'supplier', 2, 'purchase', 142, 15120.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(143, 'supplier', 2, 'purchase', 143, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(144, 'supplier', 2, 'purchase', 144, 22630.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(145, 'supplier', 2, 'purchase', 145, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(146, 'supplier', 3, 'purchase', 146, 233784.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(147, 'supplier', 2, 'purchase', 147, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(148, 'supplier', 2, 'purchase', 148, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(149, 'supplier', 2, 'purchase', 149, 14400.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(150, 'supplier', 2, 'purchase', 150, 15120.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(151, 'supplier', 2, 'purchase', 151, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(152, 'supplier', 2, 'purchase', 152, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(153, 'supplier', 2, 'purchase', 153, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(154, 'supplier', 2, 'purchase', 154, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(155, 'supplier', 2, 'purchase', 155, 22630.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(156, 'supplier', 2, 'purchase', 156, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(157, 'supplier', 2, 'purchase', 157, 3721.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(158, 'supplier', 2, 'purchase', 158, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(159, 'supplier', 2, 'purchase', 159, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(160, 'supplier', 2, 'purchase', 160, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(161, 'supplier', 2, 'purchase', 161, 22265.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(162, 'supplier', 2, 'purchase', 162, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(163, 'supplier', 2, 'purchase', 163, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(164, 'supplier', 2, 'purchase', 164, 22630.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(165, 'supplier', 2, 'purchase', 165, 22265.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(166, 'supplier', 2, 'purchase', 166, 22447.50, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(167, 'supplier', 2, 'purchase', 167, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(168, 'supplier', 2, 'purchase', 168, 22265.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(169, 'supplier', 2, 'purchase', 169, 22447.50, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(170, 'supplier', 2, 'purchase', 170, 22265.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(171, 'supplier', 2, 'purchase', 171, 22447.50, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(172, 'supplier', 2, 'purchase', 172, 22447.50, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(173, 'supplier', 2, 'purchase', 173, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(174, 'supplier', 2, 'purchase', 174, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(175, 'supplier', 2, 'purchase', 175, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(176, 'supplier', 2, 'purchase', 176, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(177, 'supplier', 2, 'purchase', 177, 22630.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(178, 'supplier', 2, 'purchase', 178, 14640.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(179, 'supplier', 2, 'purchase', 179, 14640.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(180, 'supplier', 2, 'purchase', 180, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(181, 'supplier', 2, 'purchase', 181, 14400.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(182, 'supplier', 2, 'purchase', 182, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(183, 'supplier', 2, 'purchase', 183, 14640.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(184, 'supplier', 2, 'purchase', 184, 14640.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(185, 'supplier', 2, 'purchase', 185, 15120.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(186, 'supplier', 2, 'purchase', 186, 14400.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(187, 'supplier', 2, 'purchase', 187, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(188, 'supplier', 2, 'purchase', 188, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(189, 'supplier', 2, 'purchase', 189, 1656.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(190, 'supplier', 2, 'purchase', 190, 1840.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(191, 'supplier', 4, 'purchase', 191, 96000.00, '2026-08-22', NULL, 'pending', NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint UNSIGNED NOT NULL,
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
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_payments`
--

CREATE TABLE `customer_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `payment_method` enum('cash','bank_transfer','check') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `base_salary` decimal(12,2) NOT NULL,
  `overtime_rate` decimal(8,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_borrows`
--

CREATE TABLE `employee_borrows` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `remaining_amount` decimal(12,2) NOT NULL,
  `borrow_date` date NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_borrow_deductions`
--

CREATE TABLE `employee_borrow_deductions` (
  `id` bigint UNSIGNED NOT NULL,
  `borrow_id` bigint UNSIGNED NOT NULL,
  `payroll_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `deduction_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_deductions`
--

CREATE TABLE `employee_deductions` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `deduction_date` date NOT NULL,
  `type` enum('absence','late_arrival','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` bigint UNSIGNED NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_fuel_logs`
--

CREATE TABLE `equipment_fuel_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `equipment_id` bigint UNSIGNED NOT NULL,
  `log_date` date NOT NULL,
  `liters` decimal(10,2) NOT NULL,
  `unit_cost` decimal(8,2) NOT NULL,
  `total_cost` decimal(12,2) NOT NULL,
  `hours_logged` decimal(10,2) DEFAULT NULL COMMENT 'Hours logged for this fuel entry',
  `days_logged` int DEFAULT NULL COMMENT 'Days logged for this fuel entry',
  `deduct_from_inventory` tinyint(1) NOT NULL DEFAULT '0',
  `inventory_item_id` bigint UNSIGNED DEFAULT NULL,
  `inventory_movement_id` bigint UNSIGNED DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_maintenance`
--

CREATE TABLE `equipment_maintenance` (
  `id` bigint UNSIGNED NOT NULL,
  `equipment_id` bigint UNSIGNED NOT NULL,
  `maintenance_date` date NOT NULL,
  `type` enum('routine','repair','spare_part') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost` decimal(12,2) NOT NULL,
  `hours_at_maintenance` decimal(10,2) DEFAULT NULL COMMENT 'Total hours when maintenance was done',
  `days_at_maintenance` int DEFAULT NULL COMMENT 'Total days when maintenance was done',
  `supplier_id` bigint UNSIGNED DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_tools`
--

CREATE TABLE `equipment_tools` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(15,2) NOT NULL DEFAULT '0.00',
  `price_per_unit` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_value` decimal(15,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_tool_movements`
--

CREATE TABLE `equipment_tool_movements` (
  `id` bigint UNSIGNED NOT NULL,
  `equipment_tool_id` bigint UNSIGNED NOT NULL,
  `type` enum('in','out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `price_per_unit` decimal(15,2) NOT NULL,
  `total_cost` decimal(15,2) NOT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `treasury_transaction_id` bigint UNSIGNED DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `movement_date` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` bigint UNSIGNED NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `expense_date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint UNSIGNED DEFAULT NULL,
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `contributor_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expense_categories`
--

CREATE TABLE `expense_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'custom',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expense_categories`
--

INSERT INTO `expense_categories` (`id`, `name`, `type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'وقود', 'default', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09'),
(2, 'صيانة', 'default', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09'),
(3, 'مواد', 'default', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09'),
(4, 'رواتب', 'default', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09'),
(5, 'إداري', 'default', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09'),
(6, '(أخرى) مخصص ضرائب', 'default', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09'),
(7, '(أخرى) مساهمين', 'default', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09'),
(8, '(أخرى) توزيع ارباح', 'default', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09'),
(9, '(أخرى) الصدقه', 'default', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09'),
(10, 'تأمين للغير', 'default', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09'),
(11, 'تكاليف عمليات', 'default', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09'),
(12, 'مخصص طوارئ', 'default', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09'),
(13, 'مصاريف تشغيل', 'default', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09'),
(14, 'مشروعات تحت التنفيذ ( محطه )', 'default', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09'),
(15, 'مصروفات عمومية', 'default', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09'),
(16, 'اصول ثابتة', 'default', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09'),
(17, 'ايرادات اخري', 'default', 1, '2026-07-23 12:06:09', '2026-07-23 12:06:09');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alert_threshold` decimal(10,3) NOT NULL DEFAULT '0.000',
  `current_stock` decimal(12,3) NOT NULL DEFAULT '0.000',
  `price_per_unit` decimal(12,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`id`, `name`, `name_ar`, `unit`, `alert_threshold`, `current_stock`, `price_per_unit`, `created_at`, `updated_at`) VALUES
(1, 'Cement', 'اسمنت', 'طن', 40.000, 543.630, 150.00, '2026-07-23 12:06:09', '2026-07-23 12:26:16'),
(2, 'Sand', 'رمل', 'م³', 60.000, 2368.000, 25.00, '2026-07-23 12:06:09', '2026-07-23 12:26:17'),
(3, 'Gravel1', 'سن 1', 'م³', 60.000, 1826.500, 30.00, '2026-07-23 12:06:09', '2026-07-23 12:26:17'),
(4, 'Gravel2', 'سن 2', 'م³', 60.000, 1652.000, 30.00, '2026-07-23 12:06:09', '2026-07-23 12:26:17'),
(5, 'Additives', 'مادة', 'لتر', 0.000, 17000.000, 5.00, '2026-07-23 12:06:09', '2026-07-23 12:26:17'),
(6, 'Water', 'ماء', 'م³', 50.000, 1770.000, 2.00, '2026-07-23 12:06:09', '2026-07-23 12:26:17');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `id` bigint UNSIGNED NOT NULL,
  `inventory_item_id` bigint UNSIGNED NOT NULL,
  `type` enum('in','out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `balance_after` decimal(12,3) NOT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `total_cost` decimal(14,2) DEFAULT NULL,
  `supplier_id` bigint UNSIGNED DEFAULT NULL,
  `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint UNSIGNED DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `movement_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_movements`
--

INSERT INTO `inventory_movements` (`id`, `inventory_item_id`, `type`, `quantity`, `balance_after`, `unit_cost`, `total_cost`, `supplier_id`, `reference_type`, `reference_id`, `notes`, `recorded_by`, `movement_date`, `created_at`, `updated_at`) VALUES
(1, 2, 'in', 60.000, 60.000, 240.00, 14400.00, 1, 'purchase', 1, NULL, NULL, '2026-04-29', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(2, 3, 'in', 60.000, 60.000, 365.00, 21900.00, 1, 'purchase', 2, NULL, NULL, '2026-04-29', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(3, 4, 'in', 60.000, 60.000, 365.00, 21900.00, 1, 'purchase', 3, NULL, NULL, '2026-04-29', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(4, 3, 'in', 60.000, 120.000, 380.00, 22800.00, 2, 'purchase', 4, NULL, NULL, '2026-07-23', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(5, 4, 'in', 60.000, 120.000, 380.00, 22800.00, 2, 'purchase', 5, NULL, NULL, '2026-04-18', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(6, 2, 'in', 140.000, 200.000, 185.00, 25900.00, 2, 'purchase', 6, NULL, NULL, '2026-03-06', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(7, 1, 'in', 68.860, 68.860, 4000.00, 275440.00, 3, 'purchase', 7, NULL, NULL, '2026-04-19', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(8, 5, 'in', 1000.000, 1000.000, 16.00, 16000.00, 4, 'purchase', 8, NULL, NULL, '2026-04-20', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(9, 5, 'in', 1000.000, 2000.000, 38.00, 38000.00, 4, 'purchase', 8, NULL, NULL, '2026-04-20', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(10, 6, 'in', 120.000, 120.000, 92.00, 11040.00, 2, 'purchase', 9, NULL, NULL, '2026-05-03', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(11, 6, 'in', 20.000, 140.000, 92.00, 1840.00, 2, 'purchase', 10, NULL, NULL, '2026-05-06', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(12, 3, 'in', 60.000, 180.000, 365.00, 21900.00, 1, 'purchase', 11, NULL, NULL, '2026-05-11', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(13, 4, 'in', 60.000, 180.000, 365.00, 21900.00, 1, 'purchase', 12, NULL, NULL, '2026-05-11', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(14, 2, 'in', 60.000, 260.000, 240.00, 14400.00, 1, 'purchase', 13, NULL, NULL, '2026-05-11', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(15, 3, 'in', 60.000, 240.000, 365.00, 21900.00, 1, 'purchase', 14, NULL, NULL, '2026-06-13', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(16, 2, 'in', 60.000, 320.000, 240.00, 14400.00, 1, 'purchase', 15, NULL, NULL, '2026-05-13', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(17, 3, 'in', 60.000, 300.000, 365.00, 21900.00, 1, 'purchase', 16, NULL, NULL, '1900-01-12', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(18, 6, 'in', 18.000, 158.000, 92.00, 1656.00, 2, 'purchase', 17, NULL, NULL, '2026-05-17', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(19, 6, 'in', 20.000, 178.000, 92.00, 1840.00, 2, 'purchase', 18, NULL, NULL, '2026-05-17', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(20, 6, 'in', 20.000, 198.000, 92.00, 1840.00, 2, 'purchase', 19, NULL, NULL, '2026-05-18', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(21, 5, 'in', 4000.000, 6000.000, 16.00, 64000.00, 4, 'purchase', 20, NULL, NULL, '2026-06-14', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(22, 6, 'in', 20.000, 218.000, 92.00, 1840.00, 2, 'purchase', 21, NULL, NULL, '2026-06-17', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(23, 6, 'in', 20.000, 238.000, 92.00, 1840.00, 2, 'purchase', 22, NULL, NULL, '2026-06-17', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(24, 6, 'in', 20.000, 258.000, 92.00, 1840.00, 2, 'purchase', 23, NULL, NULL, '2026-06-17', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(25, 6, 'in', 20.000, 278.000, 92.00, 1840.00, 2, 'purchase', 24, NULL, NULL, '2026-06-17', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(26, 6, 'in', 18.000, 296.000, 92.00, 1656.00, 2, 'purchase', 25, NULL, NULL, '2026-06-20', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(27, 6, 'in', 18.000, 314.000, 92.00, 1656.00, 2, 'purchase', 26, NULL, NULL, '2026-06-20', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(28, 1, 'in', 58.800, 127.660, 4000.00, 235200.00, 3, 'purchase', 27, NULL, NULL, '2026-06-20', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(29, 3, 'in', 60.000, 360.000, 365.00, 21900.00, 5, 'purchase', 28, NULL, NULL, '2026-06-20', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(30, 4, 'in', 63.000, 243.000, 365.00, 22995.00, 5, 'purchase', 29, NULL, NULL, '2026-06-20', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(31, 3, 'in', 62.000, 422.000, 365.00, 22630.00, 2, 'purchase', 30, NULL, NULL, '2026-06-20', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(32, 6, 'in', 20.000, 334.000, 92.00, 1840.00, 2, 'purchase', 31, NULL, NULL, '2026-06-21', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(33, 3, 'in', 61.000, 483.000, 365.00, 22265.00, 2, 'purchase', 32, NULL, NULL, '2026-06-20', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(34, 3, 'in', 61.000, 544.000, 365.00, 22265.00, 2, 'purchase', 33, NULL, NULL, '2026-06-21', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(35, 3, 'in', 62.000, 606.000, 365.00, 22630.00, 2, 'purchase', 34, NULL, NULL, '2026-06-21', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(36, 2, 'in', 59.000, 379.000, 230.00, 13570.00, 2, 'purchase', 35, NULL, NULL, '2026-06-21', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(37, 2, 'in', 59.000, 438.000, 230.00, 13570.00, 2, 'purchase', 36, NULL, NULL, '2026-06-21', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(38, 2, 'in', 59.000, 497.000, 230.00, 13570.00, 2, 'purchase', 37, NULL, NULL, '2026-06-21', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(39, 6, 'in', 18.000, 352.000, 92.00, 1656.00, 2, 'purchase', 38, NULL, NULL, '2026-06-21', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(40, 6, 'in', 20.000, 372.000, 92.00, 1840.00, 2, 'purchase', 39, NULL, NULL, '2026-06-21', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(41, 2, 'in', 61.000, 558.000, 230.00, 14030.00, 2, 'purchase', 40, NULL, NULL, '2026-06-21', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(42, 2, 'in', 61.000, 619.000, 230.00, 14030.00, 2, 'purchase', 41, NULL, NULL, '2026-06-21', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(43, 4, 'in', 62.000, 305.000, 365.00, 22630.00, 2, 'purchase', 42, NULL, NULL, '2026-06-21', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(44, 4, 'in', 62.000, 367.000, 365.00, 22630.00, 2, 'purchase', 43, NULL, NULL, '2026-06-21', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(45, 1, 'in', 71.660, 199.320, 3800.00, 272308.00, 3, 'purchase', 44, NULL, NULL, '2026-06-22', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(46, 6, 'in', 20.000, 392.000, 92.00, 1840.00, 2, 'purchase', 45, NULL, NULL, '2026-06-22', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(47, 4, 'in', 61.000, 428.000, 365.00, 22265.00, 2, 'purchase', 46, NULL, NULL, '2026-06-22', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(48, 2, 'in', 59.000, 678.000, 240.00, 14160.00, 2, 'purchase', 47, NULL, NULL, '2026-06-23', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(49, 2, 'in', 61.000, 739.000, 240.00, 14640.00, 2, 'purchase', 48, NULL, NULL, '2026-06-23', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(50, 2, 'in', 61.000, 800.000, 240.00, 14640.00, 2, 'purchase', 49, NULL, NULL, '2026-06-23', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(51, 6, 'in', 18.000, 410.000, 92.00, 1656.00, 2, 'purchase', 50, NULL, NULL, '2026-06-23', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(52, 4, 'in', 61.000, 489.000, 365.00, 22265.00, 2, 'purchase', 51, NULL, NULL, '2026-06-23', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(53, 1, 'in', 69.040, 268.360, 3800.00, 262352.00, 3, 'purchase', 52, NULL, NULL, '2026-06-23', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(54, 1, 'in', 68.950, 337.310, 3800.00, 262010.00, 3, 'purchase', 53, NULL, NULL, '2026-06-23', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(55, 6, 'in', 18.000, 428.000, 92.00, 1656.00, 2, 'purchase', 54, NULL, NULL, '2026-06-23', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(56, 6, 'in', 20.000, 448.000, 92.00, 1840.00, 2, 'purchase', 55, NULL, NULL, '2026-06-23', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(57, 6, 'in', 20.000, 468.000, 92.00, 1840.00, 2, 'purchase', 56, NULL, NULL, '2026-06-23', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(58, 2, 'in', 61.000, 861.000, 240.00, 14640.00, 2, 'purchase', 57, NULL, NULL, '2026-06-23', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(59, 4, 'in', 66.000, 555.000, 365.00, 24090.00, 2, 'purchase', 58, NULL, NULL, '2026-06-23', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(60, 6, 'in', 20.000, 488.000, 92.00, 1840.00, 2, 'purchase', 59, NULL, NULL, '2026-06-24', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(61, 6, 'in', 19.000, 507.000, 92.00, 1748.00, 2, 'purchase', 60, NULL, NULL, '2026-06-25', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(62, 6, 'in', 20.000, 527.000, 92.00, 1840.00, 2, 'purchase', 61, NULL, NULL, '2026-06-24', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(63, 6, 'in', 18.000, 545.000, 92.00, 1656.00, 2, 'purchase', 62, NULL, NULL, '2026-06-25', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(64, 2, 'in', 61.000, 922.000, 240.00, 14640.00, 2, 'purchase', 63, NULL, NULL, '2026-06-25', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(65, 3, 'in', 61.000, 667.000, 365.00, 22265.00, 2, 'purchase', 64, NULL, NULL, '2026-06-25', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(66, 2, 'in', 59.000, 981.000, 240.00, 14160.00, 2, 'purchase', 65, NULL, NULL, '2026-06-25', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(67, 6, 'in', 18.000, 563.000, 92.00, 1656.00, 2, 'purchase', 66, NULL, NULL, '2026-06-25', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(68, 6, 'in', 18.000, 581.000, 92.00, 1656.00, 2, 'purchase', 67, NULL, NULL, '2026-06-27', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(69, 4, 'in', 61.000, 616.000, 365.00, 22265.00, 2, 'purchase', 68, NULL, NULL, '2026-06-27', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(70, 6, 'in', 20.000, 601.000, 92.00, 1840.00, 2, 'purchase', 69, NULL, NULL, '2026-06-27', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(71, 6, 'in', 20.000, 621.000, 92.00, 1840.00, 2, 'purchase', 70, NULL, NULL, '2026-06-27', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(72, 2, 'in', 59.000, 1040.000, 240.00, 14160.00, 2, 'purchase', 71, NULL, NULL, '2026-06-27', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(73, 2, 'in', 59.000, 1099.000, 240.00, 14160.00, 2, 'purchase', 72, NULL, NULL, '2026-06-27', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(74, 2, 'in', 61.000, 1160.000, 240.00, 14640.00, 2, 'purchase', 73, NULL, NULL, '2026-06-27', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(75, 3, 'in', 60.500, 727.500, 365.00, 22082.50, 2, 'purchase', 74, NULL, NULL, '2026-06-28', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(76, 3, 'in', 60.500, 788.000, 3650.00, 220825.00, 2, 'purchase', 75, NULL, NULL, '2026-06-28', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(77, 4, 'in', 62.000, 678.000, 365.00, 22630.00, 2, 'purchase', 76, NULL, NULL, '2026-06-28', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(78, 3, 'in', 62.000, 850.000, 365.00, 22630.00, 2, 'purchase', 77, NULL, NULL, '2026-06-28', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(79, 2, 'in', 61.000, 1221.000, 240.00, 14640.00, 2, 'purchase', 78, NULL, NULL, '2026-06-28', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(80, 3, 'in', 61.000, 911.000, 365.00, 22265.00, 2, 'purchase', 79, NULL, NULL, '2026-06-28', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(81, 2, 'in', 56.000, 1277.000, 240.00, 13440.00, 2, 'purchase', 80, NULL, NULL, '2026-06-28', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(82, 4, 'in', 59.000, 737.000, 365.00, 21535.00, 2, 'purchase', 81, NULL, NULL, '2026-06-29', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(83, 2, 'in', 63.000, 1340.000, 240.00, 15120.00, 2, 'purchase', 82, NULL, NULL, '2026-06-28', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(84, 6, 'in', 19.000, 640.000, 92.00, 1748.00, 2, 'purchase', 83, NULL, NULL, '2026-06-29', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(85, 6, 'in', 19.000, 659.000, 92.00, 1748.00, 2, 'purchase', 84, NULL, NULL, '2026-06-29', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(86, 6, 'in', 20.000, 679.000, 92.00, 1840.00, 2, 'purchase', 85, NULL, NULL, '2026-06-29', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(87, 3, 'in', 61.000, 972.000, 365.00, 22265.00, 2, 'purchase', 86, NULL, NULL, '2026-06-29', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(88, 3, 'in', 62.000, 1034.000, 365.00, 22630.00, 2, 'purchase', 87, NULL, NULL, '2026-06-29', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(89, 2, 'in', 56.000, 1396.000, 240.00, 13440.00, 2, 'purchase', 88, NULL, NULL, '2026-06-30', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(90, 4, 'in', 65.000, 802.000, 365.00, 23725.00, 2, 'purchase', 89, NULL, NULL, '2026-06-30', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(91, 6, 'in', 20.000, 699.000, 92.00, 1840.00, 2, 'purchase', 90, NULL, NULL, '2026-06-30', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(92, 6, 'in', 20.000, 719.000, 92.00, 1840.00, 2, 'purchase', 91, NULL, NULL, '2026-06-30', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(93, 5, 'in', 5000.000, 11000.000, 16.00, 80000.00, 4, 'purchase', 92, NULL, NULL, '2026-06-30', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(94, 6, 'in', 19.000, 738.000, 92.00, 1748.00, 2, 'purchase', 93, NULL, NULL, '2026-07-01', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(95, 6, 'in', 20.000, 758.000, 92.00, 1840.00, 2, 'purchase', 94, NULL, NULL, '2026-07-01', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(96, 2, 'in', 57.000, 1453.000, 240.00, 13680.00, 2, 'purchase', 95, NULL, NULL, '2026-07-02', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(97, 6, 'in', 20.000, 778.000, 92.00, 1840.00, 2, 'purchase', 96, NULL, NULL, '2026-07-05', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(98, 6, 'in', 20.000, 798.000, 92.00, 1840.00, 2, 'purchase', 97, NULL, NULL, '2026-07-05', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(99, 6, 'in', 20.000, 818.000, 92.00, 1840.00, 2, 'purchase', 98, NULL, NULL, '2026-07-06', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(100, 6, 'in', 20.000, 838.000, 92.00, 1840.00, 2, 'purchase', 99, NULL, NULL, '2026-07-08', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(101, 6, 'in', 18.000, 856.000, 92.00, 1656.00, 2, 'purchase', 100, NULL, NULL, '2026-07-08', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(102, 3, 'in', 61.000, 1095.000, 295.00, 17995.00, 1, 'purchase', 101, NULL, NULL, '2026-07-09', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(103, 6, 'in', 19.000, 875.000, 92.00, 1748.00, 2, 'purchase', 102, NULL, NULL, '2026-07-09', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(104, 6, 'in', 19.000, 894.000, 92.00, 1748.00, 2, 'purchase', 103, NULL, NULL, '2026-07-09', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(105, 3, 'in', 58.000, 1153.000, 365.00, 21170.00, 2, 'purchase', 104, NULL, NULL, '2026-07-09', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(106, 4, 'in', 59.000, 861.000, 365.00, 21535.00, 2, 'purchase', 105, NULL, NULL, '2026-07-09', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(107, 6, 'in', 18.000, 912.000, 92.00, 1656.00, 2, 'purchase', 106, NULL, NULL, '2026-07-11', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(108, 3, 'in', 62.000, 1215.000, 365.00, 22630.00, 2, 'purchase', 107, NULL, NULL, '2026-07-11', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(109, 2, 'in', 60.000, 1513.000, 365.00, 21900.00, 2, 'purchase', 108, NULL, NULL, '2026-07-11', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(110, 3, 'in', 61.000, 1276.000, 365.00, 22265.00, 2, 'purchase', 109, NULL, NULL, '2026-07-11', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(111, 6, 'in', 20.000, 932.000, 92.00, 1840.00, 2, 'purchase', 110, NULL, NULL, '2026-07-11', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(112, 3, 'in', 60.500, 1336.500, 365.00, 22082.50, 2, 'purchase', 111, NULL, NULL, '2026-07-11', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(113, 2, 'in', 60.000, 1573.000, 240.00, 14400.00, 2, 'purchase', 112, NULL, NULL, '2026-07-11', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(114, 1, 'in', 69.080, 406.390, 3340.00, 230727.20, 3, 'purchase', 113, NULL, NULL, '2026-07-12', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(115, 3, 'in', 59.000, 1395.500, 365.00, 21535.00, 2, 'purchase', 114, NULL, NULL, '2026-07-12', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(116, 6, 'in', 20.000, 952.000, 92.00, 1840.00, 2, 'purchase', 115, NULL, NULL, '2026-07-12', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(117, 6, 'in', 18.000, 970.000, 92.00, 1656.00, 2, 'purchase', 116, NULL, NULL, '2026-07-12', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(118, 6, 'in', 20.000, 990.000, 92.00, 1840.00, 2, 'purchase', 117, NULL, NULL, '2026-07-12', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(119, 6, 'in', 20.000, 1010.000, 92.00, 1840.00, 2, 'purchase', 118, NULL, NULL, '2026-07-12', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(120, 3, 'in', 61.000, 1456.500, 365.00, 22265.00, 2, 'purchase', 119, NULL, NULL, '2026-07-12', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(121, 6, 'in', 19.000, 1029.000, 92.00, 1748.00, 2, 'purchase', 120, NULL, NULL, '2026-07-12', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(122, 6, 'in', 20.000, 1049.000, 92.00, 1840.00, 2, 'purchase', 121, NULL, NULL, '2026-07-13', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(123, 6, 'in', 20.000, 1069.000, 92.00, 1840.00, 2, 'purchase', 122, NULL, NULL, '2026-07-13', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(124, 4, 'in', 61.000, 922.000, 365.00, 22265.00, 2, 'purchase', 123, NULL, NULL, '2026-07-13', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(125, 3, 'in', 62.000, 1518.500, 365.00, 22630.00, 2, 'purchase', 124, NULL, NULL, '2026-07-13', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(126, 2, 'in', 60.000, 1633.000, 240.00, 14400.00, 2, 'purchase', 125, NULL, NULL, '2026-07-13', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(127, 2, 'in', 63.000, 1696.000, 240.00, 15120.00, 2, 'purchase', 126, NULL, NULL, '2026-07-13', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(128, 4, 'in', 60.500, 982.500, 365.00, 22082.50, 2, 'purchase', 127, NULL, NULL, '2026-07-13', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(129, 2, 'in', 60.000, 1756.000, 240.00, 14400.00, 2, 'purchase', 128, NULL, NULL, '2026-07-13', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(130, 4, 'in', 60.500, 1043.000, 365.00, 22082.50, 2, 'purchase', 129, NULL, NULL, '2026-07-13', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(131, 6, 'in', 18.000, 1087.000, 92.00, 1656.00, 2, 'purchase', 130, NULL, NULL, '2026-07-14', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(132, 1, 'in', 68.480, 474.870, 3340.00, 228723.20, 3, 'purchase', 131, NULL, NULL, '2026-07-14', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(133, 6, 'in', 20.000, 1107.000, 92.00, 1840.00, 2, 'purchase', 132, NULL, NULL, '2026-07-14', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(134, 6, 'in', 20.000, 1127.000, 92.00, 1840.00, 2, 'purchase', 133, NULL, NULL, '2026-07-14', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(135, 6, 'in', 18.000, 1145.000, 92.00, 1656.00, 2, 'purchase', 134, NULL, NULL, '2026-07-14', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(136, 4, 'in', 61.000, 1104.000, 365.00, 22265.00, 2, 'purchase', 135, NULL, NULL, '2026-07-14', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(137, 6, 'in', 20.000, 1165.000, 92.00, 1840.00, 2, 'purchase', 136, NULL, NULL, '2026-07-15', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(138, 6, 'in', 20.000, 1185.000, 92.00, 1840.00, 2, 'purchase', 137, NULL, NULL, '2026-07-15', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(139, 4, 'in', 57.000, 1161.000, 365.00, 20805.00, 2, 'purchase', 138, NULL, NULL, '2026-07-15', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(140, 6, 'in', 20.000, 1205.000, 92.00, 1840.00, 2, 'purchase', 139, NULL, NULL, '2026-07-15', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(141, 6, 'in', 20.000, 1225.000, 92.00, 1840.00, 2, 'purchase', 140, NULL, NULL, '2026-07-15', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(142, 2, 'in', 60.000, 1816.000, 240.00, 14400.00, 2, 'purchase', 141, NULL, NULL, '2026-07-15', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(143, 2, 'in', 63.000, 1879.000, 240.00, 15120.00, 2, 'purchase', 142, NULL, NULL, '2026-07-15', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(144, 6, 'in', 20.000, 1245.000, 92.00, 1840.00, 2, 'purchase', 143, NULL, NULL, '2026-07-15', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(145, 4, 'in', 62.000, 1223.000, 365.00, 22630.00, 2, 'purchase', 144, NULL, NULL, '2026-07-15', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(146, 6, 'in', 20.000, 1265.000, 92.00, 1840.00, 2, 'purchase', 145, NULL, NULL, '2026-07-15', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(147, 1, 'in', 68.760, 543.630, 3400.00, 233784.00, 3, 'purchase', 146, NULL, NULL, '2026-07-15', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(148, 6, 'in', 18.000, 1283.000, 92.00, 1656.00, 2, 'purchase', 147, NULL, NULL, '2026-07-15', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(149, 6, 'in', 18.000, 1301.000, 92.00, 1656.00, 2, 'purchase', 148, NULL, NULL, '2026-07-16', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(150, 2, 'in', 60.000, 1939.000, 240.00, 14400.00, 2, 'purchase', 149, NULL, NULL, '2026-07-16', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(151, 2, 'in', 63.000, 2002.000, 240.00, 15120.00, 2, 'purchase', 150, NULL, NULL, '2026-07-16', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(152, 6, 'in', 20.000, 1321.000, 92.00, 1840.00, 2, 'purchase', 151, NULL, NULL, '2026-07-16', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(153, 6, 'in', 20.000, 1341.000, 92.00, 1840.00, 2, 'purchase', 152, NULL, NULL, '2026-07-16', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(154, 6, 'in', 20.000, 1361.000, 92.00, 1840.00, 2, 'purchase', 153, NULL, NULL, '2026-07-16', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(155, 6, 'in', 20.000, 1381.000, 92.00, 1840.00, 2, 'purchase', 154, NULL, NULL, '2026-07-16', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(156, 3, 'in', 62.000, 1580.500, 365.00, 22630.00, 2, 'purchase', 155, NULL, NULL, '2026-07-16', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(157, 6, 'in', 18.000, 1399.000, 92.00, 1656.00, 2, 'purchase', 156, NULL, NULL, '2026-07-16', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(158, 4, 'in', 61.000, 1284.000, 61.00, 3721.00, 2, 'purchase', 157, NULL, NULL, '2026-07-16', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(159, 6, 'in', 20.000, 1419.000, 92.00, 1840.00, 2, 'purchase', 158, NULL, NULL, '2026-07-16', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(160, 6, 'in', 18.000, 1437.000, 92.00, 1656.00, 2, 'purchase', 159, NULL, NULL, '2026-07-16', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(161, 6, 'in', 18.000, 1455.000, 92.00, 1656.00, 2, 'purchase', 160, NULL, NULL, '2026-07-16', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(162, 4, 'in', 61.000, 1345.000, 365.00, 22265.00, 2, 'purchase', 161, NULL, NULL, '2026-07-16', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(163, 6, 'in', 20.000, 1475.000, 92.00, 1840.00, 2, 'purchase', 162, NULL, NULL, '2026-07-16', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(164, 6, 'in', 20.000, 1495.000, 92.00, 1840.00, 2, 'purchase', 163, NULL, NULL, '2026-07-18', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(165, 4, 'in', 62.000, 1407.000, 365.00, 22630.00, 2, 'purchase', 164, NULL, NULL, '2026-07-18', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(166, 4, 'in', 61.000, 1468.000, 365.00, 22265.00, 2, 'purchase', 165, NULL, NULL, '2026-07-18', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(167, 3, 'in', 61.500, 1642.000, 365.00, 22447.50, 2, 'purchase', 166, NULL, NULL, '2026-07-18', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(168, 6, 'in', 20.000, 1515.000, 92.00, 1840.00, 2, 'purchase', 167, NULL, NULL, '2026-07-18', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(169, 3, 'in', 61.000, 1703.000, 365.00, 22265.00, 2, 'purchase', 168, NULL, NULL, '2026-07-18', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(170, 3, 'in', 61.500, 1764.500, 365.00, 22447.50, 2, 'purchase', 169, NULL, NULL, '2026-07-18', '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(171, 4, 'in', 61.000, 1529.000, 365.00, 22265.00, 2, 'purchase', 170, NULL, NULL, '2026-07-18', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(172, 4, 'in', 61.500, 1590.500, 365.00, 22447.50, 2, 'purchase', 171, NULL, NULL, '2026-07-18', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(173, 4, 'in', 61.500, 1652.000, 365.00, 22447.50, 2, 'purchase', 172, NULL, NULL, '2026-07-18', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(174, 6, 'in', 20.000, 1535.000, 92.00, 1840.00, 2, 'purchase', 173, NULL, NULL, '2026-07-19', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(175, 6, 'in', 20.000, 1555.000, 92.00, 1840.00, 2, 'purchase', 174, NULL, NULL, '2026-07-19', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(176, 6, 'in', 20.000, 1575.000, 92.00, 1840.00, 2, 'purchase', 175, NULL, NULL, '2026-07-19', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(177, 6, 'in', 20.000, 1595.000, 92.00, 1840.00, 2, 'purchase', 176, NULL, NULL, '2026-07-19', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(178, 3, 'in', 62.000, 1826.500, 365.00, 22630.00, 2, 'purchase', 177, NULL, NULL, '2026-07-19', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(179, 2, 'in', 61.000, 2063.000, 240.00, 14640.00, 2, 'purchase', 178, NULL, NULL, '2026-07-19', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(180, 2, 'in', 61.000, 2124.000, 240.00, 14640.00, 2, 'purchase', 179, NULL, NULL, '2026-07-19', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(181, 6, 'in', 20.000, 1615.000, 92.00, 1840.00, 2, 'purchase', 180, NULL, NULL, '2026-07-19', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(182, 2, 'in', 60.000, 2184.000, 240.00, 14400.00, 2, 'purchase', 181, NULL, NULL, '2026-07-19', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(183, 6, 'in', 18.000, 1633.000, 92.00, 1656.00, 2, 'purchase', 182, NULL, NULL, '2026-07-19', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(184, 6, 'in', 61.000, 1694.000, 240.00, 14640.00, 2, 'purchase', 183, NULL, NULL, '2026-07-19', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(185, 2, 'in', 61.000, 2245.000, 240.00, 14640.00, 2, 'purchase', 184, NULL, NULL, '2026-07-19', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(186, 2, 'in', 63.000, 2308.000, 240.00, 15120.00, 2, 'purchase', 185, NULL, NULL, '2026-07-19', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(187, 2, 'in', 60.000, 2368.000, 240.00, 14400.00, 2, 'purchase', 186, NULL, NULL, '2026-07-19', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(188, 6, 'in', 20.000, 1714.000, 92.00, 1840.00, 2, 'purchase', 187, NULL, NULL, '2026-07-20', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(189, 6, 'in', 18.000, 1732.000, 92.00, 1656.00, 2, 'purchase', 188, NULL, NULL, '2026-07-20', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(190, 6, 'in', 18.000, 1750.000, 92.00, 1656.00, 2, 'purchase', 189, NULL, NULL, '2026-07-20', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(191, 6, 'in', 20.000, 1770.000, 92.00, 1840.00, 2, 'purchase', 190, NULL, NULL, '2026-07-20', '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(192, 5, 'in', 6000.000, 17000.000, 16.00, 96000.00, 4, 'purchase', 191, NULL, NULL, '2026-07-20', '2026-07-23 12:26:17', '2026-07-23 12:26:17');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

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
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `land_rents`
--

CREATE TABLE `land_rents` (
  `id` bigint UNSIGNED NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `annual_amount` decimal(12,2) NOT NULL,
  `due_date` date NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `land_rent_payments`
--

CREATE TABLE `land_rent_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `land_rent_id` bigint UNSIGNED NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `material_densities`
--

CREATE TABLE `material_densities` (
  `id` bigint UNSIGNED NOT NULL,
  `material_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'اسم المادة',
  `material_name_ar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'اسم المادة بالعربي',
  `density_kg_per_m3` decimal(10,3) NOT NULL COMMENT 'الكثافة (كجم/م³)',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'ملاحظات',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `material_densities`
--

INSERT INTO `material_densities` (`id`, `material_name`, `material_name_ar`, `density_kg_per_m3`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'Sand', 'رمل', 1378.000, 'كثافة الرمل المستخدمة في التحويل من كجم إلى م³', NULL, NULL),
(2, 'Gravel1', 'سن 1', 1258.000, 'كثافة الحصى 1 المستخدمة في التحويل من كجم إلى م³', NULL, NULL),
(3, 'Gravel2', 'سن 2', 1254.000, 'كثافة الحصى 2 المستخدمة في التحويل من كجم إلى م³', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_01_15_000001_create_mix_recipes_table', 1),
(5, '2024_01_15_000002_create_material_densities_table', 1),
(6, '2025_01_01_000002_create_customers_table', 1),
(7, '2025_01_01_000003_create_concrete_mixes_table', 1),
(8, '2025_01_01_000004_create_suppliers_table', 1),
(9, '2025_01_01_000005_create_inventory_items_table', 1),
(10, '2025_01_01_000006_create_inventory_movements_table', 1),
(11, '2025_01_01_000007_create_orders_table', 1),
(12, '2025_01_01_000008_create_equipment_tables', 1),
(13, '2025_01_01_000009_create_rental_tables', 1),
(14, '2025_01_01_000010_create_hr_tables', 1),
(15, '2025_01_01_000011_create_expenses_tables', 1),
(16, '2025_01_01_000012_create_schedule_tables', 1),
(17, '2025_01_01_000013_create_supplier_financial_tables', 1),
(18, '2025_01_01_000014_create_financial_tables', 1),
(19, '2025_01_01_000015_create_receipts_audit_tables', 1),
(20, '2025_01_01_000016_add_voucher_fields_to_receipts', 1),
(21, '2025_01_01_000017_add_status_to_receipts', 1),
(22, '2025_01_01_000018_add_invoice_image_to_supplier_purchases', 1),
(23, '2025_01_01_000019_add_price_per_unit_to_inventory_items', 1),
(24, '2025_01_01_000020_create_employee_borrows_table', 1),
(25, '2025_01_01_000021_add_borrow_deductions_to_payroll', 1),
(26, '2026_06_25_140211_create_contributors_table', 1),
(27, '2026_06_25_140407_create_contributor_payments_table', 1),
(28, '2026_06_27_001839_add_inventory_deduction_to_equipment_fuel_logs_table', 1),
(29, '2026_06_30_000001_create_equipment_tools_table', 1),
(30, '2026_06_30_000002_change_additives_unit_to_liters', 1),
(31, '2026_06_30_000003_add_shift_fields_to_rental_contracts', 1),
(32, '2026_06_30_000004_create_rental_shifts_table', 1),
(33, '2026_07_06_140322_add_contributor_id_to_expenses_table', 1),
(34, '2026_07_06_213717_create_expense_categories_table', 1),
(35, '2026_07_06_215300_change_expenses_category_to_string', 1),
(36, '2026_07_14_000001_create_order_expenses_table', 1),
(37, '2026_07_14_110738_add_payment_type_to_supplier_payments_table', 1),
(38, '2026_07_14_120000_create_neighboring_stations_table', 1),
(39, '2026_07_15_000001_add_maintenance_tracking_to_equipment', 1),
(40, '2026_07_15_075006_add_generator_to_equipment_type_enum', 1),
(41, '2026_07_16_001536_add_material_prices_to_orders_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `mix_recipes`
--

CREATE TABLE `mix_recipes` (
  `id` bigint UNSIGNED NOT NULL,
  `cement_per_m3` int NOT NULL COMMENT 'كمية الاسمنت لكل متر مكعب (كجم)',
  `sand_kg` decimal(10,3) NOT NULL COMMENT 'كمية الرمل بالكيلوغرام',
  `gravel1_kg` decimal(10,3) NOT NULL COMMENT 'كمية الحصى 1 بالكيلوغرام',
  `gravel2_kg` decimal(10,3) NOT NULL COMMENT 'كمية الحصى 2 بالكيلوغرام',
  `cement_kg` decimal(10,3) NOT NULL COMMENT 'كمية الاسمنت بالكيلوغرام',
  `water_m3` decimal(10,3) NOT NULL COMMENT 'كمية الماء بالمتر المكعب',
  `additives_liter` decimal(10,3) NOT NULL COMMENT 'كمية المضافات باللتر',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'ملاحظات',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mix_recipes`
--

INSERT INTO `mix_recipes` (`id`, `cement_per_m3`, `sand_kg`, `gravel1_kg`, `gravel2_kg`, `cement_kg`, `water_m3`, `additives_liter`, `notes`, `created_at`, `updated_at`) VALUES
(1, 350, 720.000, 440.000, 660.000, 350.000, 0.200, 4.500, 'خلطة قياسية 350', NULL, NULL),
(2, 250, 820.000, 440.000, 660.000, 250.000, 0.190, 3.000, 'خلطة قياسية 250', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `neighboring_stations`
--

CREATE TABLE `neighboring_stations` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `neighboring_station_transactions`
--

CREATE TABLE `neighboring_station_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `neighboring_station_id` bigint UNSIGNED NOT NULL,
  `transaction_type` enum('rent_equipment','rent_vehicle','borrow_material','borrow_inventory','sell_concrete','service') COLLATE utf8mb4_unicode_ci NOT NULL,
  `direction` enum('incoming','outgoing') COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `payment_status` enum('paid','pending','partial') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `paid_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `concrete_mix_id` bigint UNSIGNED DEFAULT NULL,
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
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_expenses`
--

CREATE TABLE `order_expenses` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `expense_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
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
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `id` bigint UNSIGNED NOT NULL,
  `type` enum('in','out') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in',
  `amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','done') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `signed_image_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier_id` bigint UNSIGNED DEFAULT NULL,
  `receipt_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receipt_date` date NOT NULL,
  `total_amount` decimal(14,2) NOT NULL,
  `image_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `receipt_items`
--

CREATE TABLE `receipt_items` (
  `id` bigint UNSIGNED NOT NULL,
  `receipt_id` bigint UNSIGNED NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(10,3) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rental_contracts`
--

CREATE TABLE `rental_contracts` (
  `id` bigint UNSIGNED NOT NULL,
  `equipment_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `car_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `driver_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hourly_price` decimal(12,2) DEFAULT NULL,
  `driver_allowance` decimal(12,2) DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `supplier_id` bigint UNSIGNED DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `monthly_fee` decimal(12,2) DEFAULT NULL,
  `total_fee` decimal(14,2) DEFAULT NULL,
  `payment_type` enum('cash','credit','mixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `status` enum('active','expired','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rental_maintenance`
--

CREATE TABLE `rental_maintenance` (
  `id` bigint UNSIGNED NOT NULL,
  `rental_contract_id` bigint UNSIGNED NOT NULL,
  `maintenance_date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost` decimal(12,2) NOT NULL,
  `deducted_from_rent` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rental_shifts`
--

CREATE TABLE `rental_shifts` (
  `id` bigint UNSIGNED NOT NULL,
  `rental_contract_id` bigint UNSIGNED NOT NULL,
  `shift_date` date NOT NULL,
  `hours` decimal(8,2) NOT NULL DEFAULT '0.00',
  `hourly_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `hours_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `gratuities` decimal(12,2) NOT NULL DEFAULT '0.00',
  `cards_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `driver_allowance` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_cost` decimal(14,2) NOT NULL DEFAULT '0.00',
  `fuel_liters` decimal(8,3) DEFAULT NULL,
  `fuel_inventory_item_id` bigint UNSIGNED DEFAULT NULL,
  `fuel_inventory_movement_id` bigint UNSIGNED DEFAULT NULL,
  `fuel_cost` decimal(12,2) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `materials` json DEFAULT NULL,
  `payment_type` enum('cash','credit','mixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `balance` decimal(14,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `phone`, `address`, `materials`, `payment_type`, `balance`, `notes`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'عمر ساري', NULL, NULL, '[\"رمل وسن\"]', 'credit', 192595.00, NULL, 1, '2026-07-23 12:09:33', '2026-07-23 12:26:16'),
(2, 'سلام', NULL, NULL, '[\"سن ورمل ومياه\"]', 'credit', 1946889.00, NULL, 1, '2026-07-23 12:10:01', '2026-07-23 12:26:17'),
(3, 'جولدن يونيتد بلال ابو الدهب', NULL, NULL, '[\"اسمنت\"]', 'credit', 2000544.40, NULL, 1, '2026-07-23 12:10:15', '2026-07-23 12:26:16'),
(4, 'هاي كيم', NULL, NULL, '[\"مادة\"]', 'credit', 294000.00, NULL, 1, '2026-07-23 12:10:24', '2026-07-23 12:26:17'),
(5, 'محمد فتحي', NULL, NULL, '[\"سن\"]', 'credit', 44895.00, NULL, 1, '2026-07-23 12:11:09', '2026-07-23 12:26:16');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_payments`
--

CREATE TABLE `supplier_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `supplier_id` bigint UNSIGNED NOT NULL,
  `supplier_purchase_id` bigint UNSIGNED DEFAULT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `payment_method` enum('cash','bank_transfer','check') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `payment_type` enum('payment','deduction') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'payment',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_purchases`
--

CREATE TABLE `supplier_purchases` (
  `id` bigint UNSIGNED NOT NULL,
  `supplier_id` bigint UNSIGNED NOT NULL,
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
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier_purchases`
--

INSERT INTO `supplier_purchases` (`id`, `supplier_id`, `invoice_number`, `purchase_date`, `total_amount`, `payment_type`, `cash_amount`, `credit_amount`, `due_date`, `status`, `notes`, `invoice_image_path`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, '1', '2026-04-29', 14400.00, 'credit', 0.00, 14400.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(2, 1, '2', '2026-04-29', 21900.00, 'credit', 0.00, 21900.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(3, 1, '3', '2026-04-29', 21900.00, 'credit', 0.00, 21900.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(4, 2, '4', '2026-07-23', 22800.00, 'credit', 0.00, 22800.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(5, 2, '5', '2026-04-18', 22800.00, 'credit', 0.00, 22800.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(6, 2, '6', '2026-03-06', 25900.00, 'credit', 0.00, 25900.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(7, 3, '7', '2026-04-19', 275440.00, 'credit', 0.00, 275440.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(8, 4, '8', '2026-04-20', 54000.00, 'credit', 0.00, 54000.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(9, 2, '9', '2026-05-03', 11040.00, 'credit', 0.00, 11040.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(10, 2, '10', '2026-05-06', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(11, 1, '11', '2026-05-11', 21900.00, 'credit', 0.00, 21900.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(12, 1, '12', '2026-05-11', 21900.00, 'credit', 0.00, 21900.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(13, 1, '13', '2026-05-11', 14400.00, 'credit', 0.00, 14400.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(14, 1, '14', '2026-06-13', 21900.00, 'credit', 0.00, 21900.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(15, 1, '15', '2026-05-13', 14400.00, 'credit', 0.00, 14400.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(16, 1, '16', '1900-01-12', 21900.00, 'credit', 0.00, 21900.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(17, 2, '17', '2026-05-17', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(18, 2, '18', '2026-05-17', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(19, 2, '19', '2026-05-18', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(20, 4, '20', '2026-06-14', 64000.00, 'credit', 0.00, 64000.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(21, 2, '21', '2026-06-17', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(22, 2, '22', '2026-06-17', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(23, 2, '23', '2026-06-17', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(24, 2, '24', '2026-06-17', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(25, 2, '25', '2026-06-20', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(26, 2, '26', '2026-06-20', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(27, 3, '27', '2026-06-20', 235200.00, 'credit', 0.00, 235200.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(28, 5, '31', '2026-06-20', 21900.00, 'credit', 0.00, 21900.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(29, 5, '32', '2026-06-20', 22995.00, 'credit', 0.00, 22995.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(30, 2, '33', '2026-06-20', 22630.00, 'credit', 0.00, 22630.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(31, 2, '34', '2026-06-21', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(32, 2, '35', '2026-06-20', 22265.00, 'credit', 0.00, 22265.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(33, 2, '36', '2026-06-21', 22265.00, 'credit', 0.00, 22265.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(34, 2, '37', '2026-06-21', 22630.00, 'credit', 0.00, 22630.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(35, 2, '38', '2026-06-21', 13570.00, 'credit', 0.00, 13570.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(36, 2, '39', '2026-06-21', 13570.00, 'credit', 0.00, 13570.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(37, 2, '40', '2026-06-21', 13570.00, 'credit', 0.00, 13570.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(38, 2, '41', '2026-06-21', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(39, 2, '44', '2026-06-21', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(40, 2, '45', '2026-06-21', 14030.00, 'credit', 0.00, 14030.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(41, 2, '46', '2026-06-21', 14030.00, 'credit', 0.00, 14030.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(42, 2, '47', '2026-06-21', 22630.00, 'credit', 0.00, 22630.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(43, 2, '48', '2026-06-21', 22630.00, 'credit', 0.00, 22630.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(44, 3, '49', '2026-06-22', 272308.00, 'credit', 0.00, 272308.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(45, 2, '51', '2026-06-22', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(46, 2, '52', '2026-06-22', 22265.00, 'credit', 0.00, 22265.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(47, 2, '53', '2026-06-23', 14160.00, 'credit', 0.00, 14160.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(48, 2, '54', '2026-06-23', 14640.00, 'credit', 0.00, 14640.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(49, 2, '55', '2026-06-23', 14640.00, 'credit', 0.00, 14640.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(50, 2, '56', '2026-06-23', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(51, 2, '57', '2026-06-23', 22265.00, 'credit', 0.00, 22265.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(52, 3, '58', '2026-06-23', 262352.00, 'credit', 0.00, 262352.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(53, 3, '59', '2026-06-23', 262010.00, 'credit', 0.00, 262010.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(54, 2, '60', '2026-06-23', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(55, 2, '61', '2026-06-23', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(56, 2, '62', '2026-06-23', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(57, 2, '63', '2026-06-23', 14640.00, 'credit', 0.00, 14640.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(58, 2, '64', '2026-06-23', 24090.00, 'credit', 0.00, 24090.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(59, 2, '66', '2026-06-24', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(60, 2, '67', '2026-06-25', 1748.00, 'credit', 0.00, 1748.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(61, 2, '69', '2026-06-24', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(62, 2, '71', '2026-06-25', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(63, 2, '72', '2026-06-25', 14640.00, 'credit', 0.00, 14640.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(64, 2, '73', '2026-06-25', 22265.00, 'credit', 0.00, 22265.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(65, 2, '74', '2026-06-25', 14160.00, 'credit', 0.00, 14160.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(66, 2, '76', '2026-06-25', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(67, 2, '77', '2026-06-27', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(68, 2, '78', '2026-06-27', 22265.00, 'credit', 0.00, 22265.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(69, 2, '79', '2026-06-27', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(70, 2, '80', '2026-06-27', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(71, 2, '81', '2026-06-27', 14160.00, 'credit', 0.00, 14160.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(72, 2, '82', '2026-06-27', 14160.00, 'credit', 0.00, 14160.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(73, 2, '83', '2026-06-27', 14640.00, 'credit', 0.00, 14640.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(74, 2, '84', '2026-06-28', 22082.50, 'credit', 0.00, 22082.50, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(75, 2, '85', '2026-06-28', 220825.00, 'credit', 0.00, 220825.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(76, 2, '86', '2026-06-28', 22630.00, 'credit', 0.00, 22630.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(77, 2, '87', '2026-06-28', 22630.00, 'credit', 0.00, 22630.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(78, 2, '88', '2026-06-28', 14640.00, 'credit', 0.00, 14640.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(79, 2, '89', '2026-06-28', 22265.00, 'credit', 0.00, 22265.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(80, 2, '90', '2026-06-28', 13440.00, 'credit', 0.00, 13440.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(81, 2, '91', '2026-06-29', 21535.00, 'credit', 0.00, 21535.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(82, 2, '92', '2026-06-28', 15120.00, 'credit', 0.00, 15120.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(83, 2, '93', '2026-06-29', 1748.00, 'credit', 0.00, 1748.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(84, 2, '94', '2026-06-29', 1748.00, 'credit', 0.00, 1748.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(85, 2, '95', '2026-06-29', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(86, 2, '96', '2026-06-29', 22265.00, 'credit', 0.00, 22265.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(87, 2, '97', '2026-06-29', 22630.00, 'credit', 0.00, 22630.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(88, 2, '99', '2026-06-30', 13440.00, 'credit', 0.00, 13440.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(89, 2, '100', '2026-06-30', 23725.00, 'credit', 0.00, 23725.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(90, 2, '101', '2026-06-30', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(91, 2, '102', '2026-06-30', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(92, 4, '103', '2026-06-30', 80000.00, 'credit', 0.00, 80000.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(93, 2, '104', '2026-07-01', 1748.00, 'credit', 0.00, 1748.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(94, 2, '105', '2026-07-01', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(95, 2, '106', '2026-07-02', 13680.00, 'credit', 0.00, 13680.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(96, 2, '107', '2026-07-05', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(97, 2, '108', '2026-07-05', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(98, 2, '109', '2026-07-06', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(99, 2, '110', '2026-07-08', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(100, 2, '111', '2026-07-08', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(101, 1, '112', '2026-07-09', 17995.00, 'credit', 0.00, 17995.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(102, 2, '114', '2026-07-09', 1748.00, 'credit', 0.00, 1748.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(103, 2, '115', '2026-07-09', 1748.00, 'credit', 0.00, 1748.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(104, 2, '116', '2026-07-09', 21170.00, 'credit', 0.00, 21170.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(105, 2, '117', '2026-07-09', 21535.00, 'credit', 0.00, 21535.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(106, 2, '120', '2026-07-11', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(107, 2, '121', '2026-07-11', 22630.00, 'credit', 0.00, 22630.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(108, 2, '123', '2026-07-11', 21900.00, 'credit', 0.00, 21900.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(109, 2, '124', '2026-07-11', 22265.00, 'credit', 0.00, 22265.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(110, 2, '125', '2026-07-11', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(111, 2, '126', '2026-07-11', 22082.50, 'credit', 0.00, 22082.50, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(112, 2, '127', '2026-07-11', 14400.00, 'credit', 0.00, 14400.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(113, 3, '128', '2026-07-12', 230727.20, 'credit', 0.00, 230727.20, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(114, 2, '129', '2026-07-12', 21535.00, 'credit', 0.00, 21535.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(115, 2, '131', '2026-07-12', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(116, 2, '132', '2026-07-12', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(117, 2, '133', '2026-07-12', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(118, 2, '134', '2026-07-12', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(119, 2, '135', '2026-07-12', 22265.00, 'credit', 0.00, 22265.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(120, 2, '136', '2026-07-12', 1748.00, 'credit', 0.00, 1748.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(121, 2, '137', '2026-07-13', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(122, 2, '138', '2026-07-13', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(123, 2, '139', '2026-07-13', 22265.00, 'credit', 0.00, 22265.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(124, 2, '140', '2026-07-13', 22630.00, 'credit', 0.00, 22630.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(125, 2, '141', '2026-07-13', 14400.00, 'credit', 0.00, 14400.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(126, 2, '142', '2026-07-13', 15120.00, 'credit', 0.00, 15120.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(127, 2, '143', '2026-07-13', 22082.50, 'credit', 0.00, 22082.50, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(128, 2, '144', '2026-07-13', 14400.00, 'credit', 0.00, 14400.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(129, 2, '145', '2026-07-13', 22082.50, 'credit', 0.00, 22082.50, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(130, 2, '146', '2026-07-14', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(131, 3, '147', '2026-07-14', 228723.20, 'credit', 0.00, 228723.20, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(132, 2, '148', '2026-07-14', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(133, 2, '149', '2026-07-14', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(134, 2, '150', '2026-07-14', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(135, 2, '151', '2026-07-14', 22265.00, 'credit', 0.00, 22265.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(136, 2, '152', '2026-07-15', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(137, 2, '153', '2026-07-15', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(138, 2, '154', '2026-07-15', 20805.00, 'credit', 0.00, 20805.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(139, 2, '155', '2026-07-15', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(140, 2, '156', '2026-07-15', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(141, 2, '157', '2026-07-15', 14400.00, 'credit', 0.00, 14400.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(142, 2, '158', '2026-07-15', 15120.00, 'credit', 0.00, 15120.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(143, 2, '159', '2026-07-15', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(144, 2, '160', '2026-07-15', 22630.00, 'credit', 0.00, 22630.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(145, 2, '161', '2026-07-15', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(146, 3, '162', '2026-07-15', 233784.00, 'credit', 0.00, 233784.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(147, 2, '163', '2026-07-15', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(148, 2, '164', '2026-07-16', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(149, 2, '166', '2026-07-16', 14400.00, 'credit', 0.00, 14400.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(150, 2, '167', '2026-07-16', 15120.00, 'credit', 0.00, 15120.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(151, 2, '168', '2026-07-16', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(152, 2, '169', '2026-07-16', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(153, 2, '170', '2026-07-16', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(154, 2, '171', '2026-07-16', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(155, 2, '172', '2026-07-16', 22630.00, 'credit', 0.00, 22630.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(156, 2, '173', '2026-07-16', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(157, 2, '174', '2026-07-16', 3721.00, 'credit', 0.00, 3721.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(158, 2, '175', '2026-07-16', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(159, 2, '176', '2026-07-16', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(160, 2, '177', '2026-07-16', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(161, 2, '178', '2026-07-16', 22265.00, 'credit', 0.00, 22265.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(162, 2, '179', '2026-07-16', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(163, 2, '180', '2026-07-18', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(164, 2, '182', '2026-07-18', 22630.00, 'credit', 0.00, 22630.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(165, 2, '183', '2026-07-18', 22265.00, 'credit', 0.00, 22265.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(166, 2, '184', '2026-07-18', 22447.50, 'credit', 0.00, 22447.50, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(167, 2, '185', '2026-07-18', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(168, 2, '186', '2026-07-18', 22265.00, 'credit', 0.00, 22265.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(169, 2, '187', '2026-07-18', 22447.50, 'credit', 0.00, 22447.50, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(170, 2, '188', '2026-07-18', 22265.00, 'credit', 0.00, 22265.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(171, 2, '189', '2026-07-18', 22447.50, 'credit', 0.00, 22447.50, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(172, 2, '190', '2026-07-18', 22447.50, 'credit', 0.00, 22447.50, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(173, 2, '191', '2026-07-19', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(174, 2, '192', '2026-07-19', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(175, 2, '193', '2026-07-19', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(176, 2, '194', '2026-07-19', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(177, 2, '195', '2026-07-19', 22630.00, 'credit', 0.00, 22630.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(178, 2, '196', '2026-07-19', 14640.00, 'credit', 0.00, 14640.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(179, 2, '197', '2026-07-19', 14640.00, 'credit', 0.00, 14640.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(180, 2, '198', '2026-07-19', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(181, 2, '199', '2026-07-19', 14400.00, 'credit', 0.00, 14400.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(182, 2, '200', '2026-07-19', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(183, 2, '201', '2026-07-19', 14640.00, 'credit', 0.00, 14640.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(184, 2, '202', '2026-07-19', 14640.00, 'credit', 0.00, 14640.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(185, 2, '203', '2026-07-19', 15120.00, 'credit', 0.00, 15120.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(186, 2, '204', '2026-07-19', 14400.00, 'credit', 0.00, 14400.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(187, 2, '205', '2026-07-20', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(188, 2, '207', '2026-07-20', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(189, 2, '208', '2026-07-20', 1656.00, 'credit', 0.00, 1656.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(190, 2, '209', '2026-07-20', 1840.00, 'credit', 0.00, 1840.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(191, 4, '210', '2026-07-20', 96000.00, 'credit', 0.00, 96000.00, '2026-08-22', 'pending', NULL, NULL, 1, '2026-07-23 12:26:17', '2026-07-23 12:26:17');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_purchase_items`
--

CREATE TABLE `supplier_purchase_items` (
  `id` bigint UNSIGNED NOT NULL,
  `supplier_purchase_id` bigint UNSIGNED NOT NULL,
  `inventory_item_id` bigint UNSIGNED DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier_purchase_items`
--

INSERT INTO `supplier_purchase_items` (`id`, `supplier_purchase_id`, `inventory_item_id`, `description`, `quantity`, `unit`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES
(8, 11, 2, 'رمل - سيارة 9973-7558', 60.000, 'م', 240.00, 14400.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(9, 12, 3, 'سن1 - سيارة 9973-7558', 60.000, 'م', 365.00, 21900.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(10, 13, 4, 'سن2 - سيارة 9973-9973', 60.000, 'م', 365.00, 21900.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(11, 14, 3, 'سن1 - سيارة 7818-1738', 60.000, 'م', 380.00, 22800.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(12, 15, 4, 'سن2 - سيارة 7818-1738', 60.000, 'م', 380.00, 22800.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(13, 16, 2, 'رمل - سيارة 3724', 140.000, 'م', 185.00, 25900.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(14, 17, 1, 'اسمنت - سيارة 68860-758', 68.860, 'طن', 4000.00, 275440.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(15, 18, 5, 'ماده - سيارة 6749', 1000.000, 'لتر', 16.00, 16000.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(16, 18, 5, 'ماده - سيارة 6749', 1000.000, 'لتر', 38.00, 38000.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(17, 19, 6, 'مياه', 120.000, 'م', 92.00, 11040.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(18, 20, 6, 'مياه - سيارة 7695', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(19, 21, 3, 'سن1 - سيارة 7558-9973', 60.000, 'م', 365.00, 21900.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(20, 22, 4, 'سن2 - سيارة 7558-9973', 60.000, 'م', 365.00, 21900.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(21, 23, 2, 'رمل - سيارة 7558-9973', 60.000, 'م', 240.00, 14400.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(22, 24, 3, 'سن1 - سيارة 7558-9973', 60.000, 'م', 365.00, 21900.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(23, 25, 2, 'رمل - سيارة 9973-7558', 60.000, 'م', 240.00, 14400.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(24, 26, 3, 'سن1 - سيارة 9973-7558', 60.000, 'م', 365.00, 21900.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(25, 27, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(26, 28, 6, 'مياه - سيارة 222', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(27, 29, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(28, 30, 5, 'ماده - سيارة 6749', 4000.000, 'لتر', 16.00, 64000.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(29, 31, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(30, 32, 6, 'مياه - سيارة 7695', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(31, 33, 6, 'مياه - سيارة 1322', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(32, 34, 6, 'مياه - سيارة 1322', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(33, 35, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(34, 36, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(35, 37, 1, 'اسمنت - سيارة 2612-549', 58.800, 'طن', 4000.00, 235200.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(36, 38, 3, 'سن1 - سيارة 5767-2217', 60.000, 'م', 365.00, 21900.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(37, 39, 4, 'سن2 - سيارة 9936-5635', 63.000, 'م', 365.00, 22995.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(38, 40, 3, 'سن1 - سيارة 7818-1738', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(39, 41, 6, 'مياه - سيارة 7695', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(40, 42, 3, 'سن1 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(41, 43, 3, 'سن1 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(42, 44, 3, 'سن1 - سيارة 7818-1783', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(43, 45, 2, 'رمل - سيارة 6299-6299', 59.000, 'م', 230.00, 13570.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(44, 46, 2, 'رمل - سيارة 1821-6299', 59.000, 'م', 230.00, 13570.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(45, 47, 2, 'رمل - سيارة 1821-6299', 59.000, 'م', 230.00, 13570.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(46, 48, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(47, 49, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(48, 50, 2, 'رمل - سيارة 1563-7691', 61.000, 'م', 230.00, 14030.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(49, 51, 2, 'رمل - سيارة 1563-7691', 61.000, 'م', 230.00, 14030.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(50, 52, 4, 'سن2 - سيارة 1738-7818', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(51, 53, 4, 'سن2 - سيارة 7818-1783', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(52, 54, 1, 'اسمنت - سيارة 3673-5628', 71.660, 'طن', 3800.00, 272308.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(53, 55, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(54, 56, 4, 'سن2 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(55, 57, 2, 'رمل - سيارة 1821-6299', 59.000, 'م', 240.00, 14160.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(56, 58, 2, 'رمل - سيارة 1563-6791', 61.000, 'م', 240.00, 14640.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(57, 59, 2, 'رمل - سيارة 1563-6791', 61.000, 'م', 240.00, 14640.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(58, 60, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(59, 61, 4, 'سن2 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(60, 62, 1, 'اسمنت - سيارة 8365-758', 69.040, 'طن', 3800.00, 262352.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(61, 63, 1, 'اسمنت - سيارة 8315-758', 68.950, 'طن', 3800.00, 262010.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(62, 64, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(63, 65, 6, 'مياه - سيارة 7695', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(64, 66, 6, 'مياه - سيارة 8615', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(65, 67, 2, 'رمل - سيارة 1563-7691', 61.000, 'م', 240.00, 14640.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(66, 68, 4, 'سن2 - سيارة 9182-4541', 66.000, 'م', 365.00, 24090.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(67, 69, 6, 'مياه - سيارة 2792', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(68, 70, 6, 'مياه - سيارة 8318', 19.000, 'م', 92.00, 1748.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(69, 71, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(70, 72, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(71, 73, 2, 'رمل - سيارة 8854-9948', 61.000, 'م', 240.00, 14640.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(72, 74, 3, 'سن1 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(73, 75, 2, 'رمل - سيارة 6299-1821', 59.000, 'م', 240.00, 14160.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(74, 76, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(75, 77, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(76, 78, 4, 'سن2 - سيارة 7818-1738', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(77, 79, 6, 'مياه - سيارة 9821', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(78, 80, 6, 'مياه - سيارة 7695', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(79, 81, 2, 'رمل - سيارة 6299', 59.000, 'م', 240.00, 14160.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(80, 82, 2, 'رمل - سيارة 6299-1821', 59.000, 'م', 240.00, 14160.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(81, 83, 2, 'رمل - سيارة 7691-1563', 61.000, 'م', 240.00, 14640.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(82, 84, 3, 'سن1 - سيارة 1489-7864', 60.500, 'م', 365.00, 22082.50, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(83, 85, 2, 'رمل - سيارة 1489-7864', 60.500, 'م', 240.00, 14520.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(84, 86, 4, 'سن2 - سيارة 7818-1738', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(85, 87, 3, 'سن1 - سيارة 7818-1738', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(86, 88, 2, 'رمل - سيارة 7691-1563', 61.000, 'م', 240.00, 14640.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(87, 89, 3, 'سن1 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(88, 90, 2, 'رمل - سيارة 3797-7925', 56.000, 'م', 240.00, 13440.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(89, 91, 4, 'سن2 - سيارة 7818-1738', 59.000, 'م', 365.00, 21535.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(90, 92, 2, 'رمل - سيارة 1126-7174', 63.000, 'م', 240.00, 15120.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(91, 93, 6, 'مياه - سيارة 8318', 19.000, 'م', 92.00, 1748.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(92, 94, 6, 'مياه - سيارة 8318', 19.000, 'م', 92.00, 1748.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(93, 95, 6, 'مياه - سيارة 7695', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(94, 96, 3, 'سن1 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(95, 97, 3, 'سن1 - سيارة 7818/1738', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(96, 98, 2, 'رمل - سيارة 3797-7925', 56.000, 'م', 240.00, 13440.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(97, 99, 4, 'سن2 - سيارة 3551-9582', 65.000, 'م', 365.00, 23725.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(98, 100, 6, 'مياه - سيارة 8697', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(99, 101, 6, 'مياه - سيارة 5478', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(100, 102, 5, 'ماده - سيارة 4356', 6000.000, 'لتر', 16.00, 96000.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(101, 103, 6, 'مياه - سيارة 2562', 19.000, 'م', 92.00, 1748.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(102, 104, 6, 'مياه - سيارة 9821', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(103, 105, 2, 'رمل - سيارة 926-1773', 57.000, 'م', 240.00, 13680.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(104, 106, 6, 'مياه - سيارة 7695', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(105, 107, 6, 'مياه - سيارة 5478', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(106, 108, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(107, 109, 6, 'مياه - سيارة 5478', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(108, 110, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(109, 111, 3, 'سن1 - سيارة 5735-7662', 61.000, 'م', 295.00, 17995.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(110, 112, 6, 'مياه - سيارة 2562', 19.000, 'م', 92.00, 1748.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(111, 113, 6, 'مياه - سيارة 2562', 19.000, 'م', 92.00, 1748.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(112, 114, 3, 'سن1 - سيارة 7818-3455', 58.000, 'م', 365.00, 21170.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(113, 115, 4, 'سن2 - سيارة 7818-1738', 59.000, 'م', 365.00, 21535.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(114, 116, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(115, 117, 3, 'سن1 - سيارة 7818-1738', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(116, 118, 2, 'رمل - سيارة 3874-5213', 60.000, 'م', 365.00, 21900.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(117, 119, 3, 'سن1 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(118, 120, 6, 'مياه - سيارة 5478', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(119, 121, 3, 'سن1 - سيارة 1489-7864', 60.500, 'م', 365.00, 22082.50, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(120, 122, 2, 'رمل - سيارة 5213-3874', 60.000, 'م', 240.00, 14400.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(121, 123, 1, 'اسمنت - سيارة 8365-758', 69.080, 'م', 3340.00, 230727.20, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(122, 124, 3, 'سن1 - سيارة 7818-1738', 59.000, 'م', 365.00, 21535.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(123, 125, 6, 'مياه - سيارة 5478', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(124, 126, 6, 'مياه - سيارة 3614', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(125, 127, 6, 'مياه - سيارة 7695', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(126, 128, 6, 'مياه - سيارة 7695', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(127, 129, 3, 'سن1 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(128, 130, 6, 'مياه - سيارة 2562', 19.000, 'م', 92.00, 1748.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(129, 131, 6, 'مياه - سيارة 5478', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(130, 132, 6, 'مياه - سيارة 7685', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(131, 133, 4, 'سن2 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(132, 134, 3, 'سن1 - سيارة 7818-1738', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(133, 135, 2, 'رمل - سيارة 3874-5213', 60.000, 'م', 240.00, 14400.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(134, 136, 2, 'رمل - سيارة 8625-6549', 63.000, 'م', 240.00, 15120.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(135, 137, 4, 'سن2 - سيارة 1489-7864', 60.500, 'م', 365.00, 22082.50, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(136, 138, 2, 'رمل - سيارة 3874-5213', 60.000, 'م', 240.00, 14400.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(137, 139, 4, 'سن2 - سيارة 1489-7864', 60.500, 'م', 365.00, 22082.50, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(138, 140, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(139, 141, 1, 'اسمنت - سيارة 3673-5628', 68.480, 'طن', 3340.00, 228723.20, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(140, 142, 6, 'مياه - سيارة 7685', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(141, 143, 6, 'مياه - سيارة 6567', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(142, 144, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(143, 145, 4, 'سن2 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(144, 146, 6, 'مياه - سيارة 6567', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(145, 147, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(146, 148, 4, 'سن2 - سيارة 9879-1559', 59.000, 'م', 365.00, 21535.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(147, 149, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(148, 150, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(149, 151, 2, 'رمل - سيارة 3874-5213', 60.000, 'م', 240.00, 14400.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(150, 152, 2, 'رمل - سيارة 8625-6549', 63.000, 'م', 240.00, 15120.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(151, 153, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(152, 154, 4, 'سن2 - سيارة 7818-1738', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(153, 155, 6, 'مياه - سيارة 2562', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(154, 156, 1, 'اسمنت - سيارة 8365-758', 68.760, 'طن', 3400.00, 233784.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(155, 157, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(156, 158, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(157, 159, 2, 'رمل - سيارة 3874-5213', 60.000, 'م', 240.00, 14400.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(158, 160, 2, 'رمل - سيارة 8625-6549', 63.000, 'م', 240.00, 15120.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(159, 161, 6, 'مياه - سيارة 2562', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(160, 162, 6, 'مياه - سيارة 2562', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:17:26', '2026-07-23 12:17:26'),
(161, 1, 2, 'رمل - سيارة 9973-7558', 60.000, 'م', 240.00, 14400.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(162, 2, 3, 'سن1 - سيارة 9973-7558', 60.000, 'م', 365.00, 21900.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(163, 3, 4, 'سن2 - سيارة 9973-9973', 60.000, 'م', 365.00, 21900.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(164, 4, 3, 'سن1 - سيارة 7818-1738', 60.000, 'م', 380.00, 22800.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(165, 5, 4, 'سن2 - سيارة 7818-1738', 60.000, 'م', 380.00, 22800.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(166, 6, 2, 'رمل - سيارة 3724', 140.000, 'م', 185.00, 25900.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(167, 7, 1, 'اسمنت - سيارة 68860-758', 68.860, 'طن', 4000.00, 275440.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(168, 8, 5, 'ماده - سيارة 6749', 1000.000, 'لتر', 16.00, 16000.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(169, 8, 5, 'ماده - سيارة 6749', 1000.000, 'لتر', 38.00, 38000.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(170, 9, 6, 'مياه', 120.000, 'م', 92.00, 11040.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(171, 10, 6, 'مياه - سيارة 7695', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(172, 11, 3, 'سن1 - سيارة 7558-9973', 60.000, 'م', 365.00, 21900.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(173, 12, 4, 'سن2 - سيارة 7558-9973', 60.000, 'م', 365.00, 21900.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(174, 13, 2, 'رمل - سيارة 7558-9973', 60.000, 'م', 240.00, 14400.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(175, 14, 3, 'سن1 - سيارة 7558-9973', 60.000, 'م', 365.00, 21900.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(176, 15, 2, 'رمل - سيارة 9973-7558', 60.000, 'م', 240.00, 14400.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(177, 16, 3, 'سن1 - سيارة 9973-7558', 60.000, 'م', 365.00, 21900.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(178, 17, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(179, 18, 6, 'مياه - سيارة 222', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(180, 19, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(181, 20, 5, 'ماده - سيارة 6749', 4000.000, 'لتر', 16.00, 64000.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(182, 21, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(183, 22, 6, 'مياه - سيارة 7695', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(184, 23, 6, 'مياه - سيارة 1322', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(185, 24, 6, 'مياه - سيارة 1322', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(186, 25, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(187, 26, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(188, 27, 1, 'اسمنت - سيارة 2612-549', 58.800, 'طن', 4000.00, 235200.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(189, 28, 3, 'سن1 - سيارة 5767-2217', 60.000, 'م', 365.00, 21900.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(190, 29, 4, 'سن2 - سيارة 9936-5635', 63.000, 'م', 365.00, 22995.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(191, 30, 3, 'سن1 - سيارة 7818-1738', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(192, 31, 6, 'مياه - سيارة 7695', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(193, 32, 3, 'سن1 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(194, 33, 3, 'سن1 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(195, 34, 3, 'سن1 - سيارة 7818-1783', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(196, 35, 2, 'رمل - سيارة 6299-6299', 59.000, 'م', 230.00, 13570.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(197, 36, 2, 'رمل - سيارة 1821-6299', 59.000, 'م', 230.00, 13570.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(198, 37, 2, 'رمل - سيارة 1821-6299', 59.000, 'م', 230.00, 13570.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(199, 38, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(200, 39, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(201, 40, 2, 'رمل - سيارة 1563-7691', 61.000, 'م', 230.00, 14030.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(202, 41, 2, 'رمل - سيارة 1563-7691', 61.000, 'م', 230.00, 14030.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(203, 42, 4, 'سن2 - سيارة 1738-7818', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(204, 43, 4, 'سن2 - سيارة 7818-1783', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(205, 44, 1, 'اسمنت - سيارة 3673-5628', 71.660, 'طن', 3800.00, 272308.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(206, 45, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(207, 46, 4, 'سن2 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(208, 47, 2, 'رمل - سيارة 1821-6299', 59.000, 'م', 240.00, 14160.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(209, 48, 2, 'رمل - سيارة 1563-6791', 61.000, 'م', 240.00, 14640.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(210, 49, 2, 'رمل - سيارة 1563-6791', 61.000, 'م', 240.00, 14640.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(211, 50, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(212, 51, 4, 'سن2 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(213, 52, 1, 'اسمنت - سيارة 8365-758', 69.040, 'طن', 3800.00, 262352.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(214, 53, 1, 'اسمنت - سيارة 8315-758', 68.950, 'طن', 3800.00, 262010.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(215, 54, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(216, 55, 6, 'مياه - سيارة 7695', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(217, 56, 6, 'مياه - سيارة 8615', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(218, 57, 2, 'رمل - سيارة 1563-7691', 61.000, 'م', 240.00, 14640.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(219, 58, 4, 'سن2 - سيارة 9182-4541', 66.000, 'م', 365.00, 24090.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(220, 59, 6, 'مياه - سيارة 2792', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(221, 60, 6, 'مياه - سيارة 8318', 19.000, 'م', 92.00, 1748.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(222, 61, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(223, 62, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(224, 63, 2, 'رمل - سيارة 8854-9948', 61.000, 'م', 240.00, 14640.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(225, 64, 3, 'سن1 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(226, 65, 2, 'رمل - سيارة 6299-1821', 59.000, 'م', 240.00, 14160.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(227, 66, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(228, 67, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(229, 68, 4, 'سن2 - سيارة 7818-1738', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(230, 69, 6, 'مياه - سيارة 9821', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(231, 70, 6, 'مياه - سيارة 7695', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(232, 71, 2, 'رمل - سيارة 6299', 59.000, 'م', 240.00, 14160.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(233, 72, 2, 'رمل - سيارة 6299-1821', 59.000, 'م', 240.00, 14160.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(234, 73, 2, 'رمل - سيارة 7691-1563', 61.000, 'م', 240.00, 14640.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(235, 74, 3, 'سن1 - سيارة 1489-7864', 60.500, 'م', 365.00, 22082.50, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(236, 75, 3, 'سن1 - سيارة 1489-7864', 60.500, 'م', 3650.00, 220825.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(237, 76, 4, 'سن2 - سيارة 7818-1738', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(238, 77, 3, 'سن1 - سيارة 7818-1738', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(239, 78, 2, 'رمل - سيارة 7691-1563', 61.000, 'م', 240.00, 14640.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(240, 79, 3, 'سن1 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(241, 80, 2, 'رمل - سيارة 3797-7925', 56.000, 'م', 240.00, 13440.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(242, 81, 4, 'سن2 - سيارة 7818-1738', 59.000, 'م', 365.00, 21535.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(243, 82, 2, 'رمل - سيارة 1126-7174', 63.000, 'م', 240.00, 15120.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(244, 83, 6, 'مياه - سيارة 8318', 19.000, 'م', 92.00, 1748.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(245, 84, 6, 'مياه - سيارة 8318', 19.000, 'م', 92.00, 1748.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(246, 85, 6, 'مياه - سيارة 7695', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(247, 86, 3, 'سن1 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(248, 87, 3, 'سن1 - سيارة 7818/1738', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(249, 88, 2, 'رمل - سيارة 3797-7925', 56.000, 'م', 240.00, 13440.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(250, 89, 4, 'سن2 - سيارة 3551-9582', 65.000, 'م', 365.00, 23725.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(251, 90, 6, 'مياه - سيارة 8697', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(252, 91, 6, 'مياه - سيارة 5478', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(253, 92, 5, 'ماده - سيارة 4356', 5000.000, 'لتر', 16.00, 80000.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(254, 93, 6, 'مياه - سيارة 2562', 19.000, 'م', 92.00, 1748.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(255, 94, 6, 'مياه - سيارة 9821', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(256, 95, 2, 'رمل - سيارة 926-1773', 57.000, 'م', 240.00, 13680.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(257, 96, 6, 'مياه - سيارة 7695', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(258, 97, 6, 'مياه - سيارة 5478', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(259, 98, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(260, 99, 6, 'مياه - سيارة 5478', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(261, 100, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(262, 101, 3, 'سن1 - سيارة 5735-7662', 61.000, 'م', 295.00, 17995.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(263, 102, 6, 'مياه - سيارة 2562', 19.000, 'م', 92.00, 1748.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(264, 103, 6, 'مياه - سيارة 2562', 19.000, 'م', 92.00, 1748.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(265, 104, 3, 'سن1 - سيارة 7818-3455', 58.000, 'م', 365.00, 21170.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(266, 105, 4, 'سن2 - سيارة 7818-1738', 59.000, 'م', 365.00, 21535.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(267, 106, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(268, 107, 3, 'سن1 - سيارة 7818-1738', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(269, 108, 2, 'رمل - سيارة 3874-5213', 60.000, 'م', 365.00, 21900.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(270, 109, 3, 'سن1 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(271, 110, 6, 'مياه - سيارة 5478', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(272, 111, 3, 'سن1 - سيارة 1489-7864', 60.500, 'م', 365.00, 22082.50, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(273, 112, 2, 'رمل - سيارة 5213-3874', 60.000, 'م', 240.00, 14400.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(274, 113, 1, 'اسمنت - سيارة 8365-758', 69.080, 'طن', 3340.00, 230727.20, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(275, 114, 3, 'سن1 - سيارة 7818-1738', 59.000, 'م', 365.00, 21535.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(276, 115, 6, 'مياه - سيارة 5478', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(277, 116, 6, 'مياه - سيارة 3614', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(278, 117, 6, 'مياه - سيارة 7695', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(279, 118, 6, 'مياه - سيارة 7695', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(280, 119, 3, 'سن1 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(281, 120, 6, 'مياه - سيارة 2562', 19.000, 'م', 92.00, 1748.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(282, 121, 6, 'مياه - سيارة 5478', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(283, 122, 6, 'مياه - سيارة 7685', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(284, 123, 4, 'سن2 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(285, 124, 3, 'سن1 - سيارة 7818-1738', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(286, 125, 2, 'رمل - سيارة 3874-5213', 60.000, 'م', 240.00, 14400.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(287, 126, 2, 'رمل - سيارة 8625-6549', 63.000, 'م', 240.00, 15120.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(288, 127, 4, 'سن2 - سيارة 1489-7864', 60.500, 'م', 365.00, 22082.50, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(289, 128, 2, 'رمل - سيارة 3874-5213', 60.000, 'م', 240.00, 14400.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(290, 129, 4, 'سن2 - سيارة 1489-7864', 60.500, 'م', 365.00, 22082.50, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(291, 130, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(292, 131, 1, 'اسمنت - سيارة 3673-5628', 68.480, 'طن', 3340.00, 228723.20, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(293, 132, 6, 'مياه - سيارة 7685', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(294, 133, 6, 'مياه - سيارة 6567', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(295, 134, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(296, 135, 4, 'سن2 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(297, 136, 6, 'مياه - سيارة 6567', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(298, 137, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(299, 138, 4, 'سن2 - سيارة 9879-1559', 57.000, 'م', 365.00, 20805.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(300, 139, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(301, 140, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(302, 141, 2, 'رمل - سيارة 3874-5213', 60.000, 'م', 240.00, 14400.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(303, 142, 2, 'رمل - سيارة 8625-6549', 63.000, 'م', 240.00, 15120.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(304, 143, 6, 'مياه - سيارة 1327', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(305, 144, 4, 'سن2 - سيارة 7818-1738', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(306, 145, 6, 'مياه - سيارة 2562', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(307, 146, 1, 'اسمنت - سيارة 8365-758', 68.760, 'طن', 3400.00, 233784.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(308, 147, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(309, 148, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(310, 149, 2, 'رمل - سيارة 3874-5213', 60.000, 'م', 240.00, 14400.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(311, 150, 2, 'رمل - سيارة 8625-6549', 63.000, 'م', 240.00, 15120.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(312, 151, 6, 'مياه - سيارة 2562', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(313, 152, 6, 'مياه - سيارة 2562', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(314, 153, 6, 'مياه - سيارة 2792', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(315, 154, 6, 'مياه - سيارة 2792', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(316, 155, 3, 'سن1 - سيارة 7818-1738', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(317, 156, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(318, 157, 4, 'سن2 - سيارة 7818-3455', 61.000, 'م', 61.00, 3721.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(319, 158, 6, 'مياه - سيارة 2792', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(320, 159, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(321, 160, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(322, 161, 4, 'سن2 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(323, 162, 6, 'مياه - سيارة 213', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(324, 163, 6, 'مياه - سيارة 213', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(325, 164, 4, 'سن2 - سيارة 7818-1738', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(326, 165, 4, 'سن2 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(327, 166, 3, 'سن1 - سيارة 9661-3431', 61.500, 'م', 365.00, 22447.50, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(328, 167, 6, 'مياه - سيارة 213', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(329, 168, 3, 'سن1 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(330, 169, 3, 'سن1 - سيارة 9661-3431', 61.500, 'م', 365.00, 22447.50, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(331, 170, 4, 'سن2 - سيارة 7818-3455', 61.000, 'م', 365.00, 22265.00, '2026-07-23 12:26:16', '2026-07-23 12:26:16'),
(332, 171, 4, 'سن2 - سيارة 9661-3431', 61.500, 'م', 365.00, 22447.50, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(333, 172, 4, 'سن2 - سيارة 9661-3431', 61.500, 'م', 365.00, 22447.50, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(334, 173, 6, 'مياه - سيارة 5478', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(335, 174, 6, 'مياه - سيارة 213', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(336, 175, 6, 'مياه - سيارة 2792', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(337, 176, 6, 'مياه - سيارة 5478', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(338, 177, 3, 'سن1 - سيارة 7818-1738', 62.000, 'م', 365.00, 22630.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(339, 178, 2, 'رمل - سيارة 7691-1563', 61.000, 'م', 240.00, 14640.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(340, 179, 2, 'رمل - سيارة 7549-8977', 61.000, 'م', 240.00, 14640.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(341, 180, 6, 'مياه - سيارة 6567', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(342, 181, 2, 'رمل - سيارة 3874-5213', 60.000, 'م', 240.00, 14400.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(343, 182, 6, 'مياه - سيارة 222', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(344, 183, 6, 'مياه - سيارة 7691-1563', 61.000, 'م', 240.00, 14640.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(345, 184, 2, 'رمل - سيارة 7549-8977', 61.000, 'م', 240.00, 14640.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(346, 185, 2, 'رمل - سيارة 8625-6549', 63.000, 'م', 240.00, 15120.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(347, 186, 2, 'رمل - سيارة 3874-5213', 60.000, 'م', 240.00, 14400.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(348, 187, 6, 'مياه - سيارة 2838', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(349, 188, 6, 'مياه - سيارة 6567', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(350, 189, 6, 'مياه - سيارة 213', 18.000, 'م', 92.00, 1656.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(351, 190, 6, 'مياه - سيارة 3828', 20.000, 'م', 92.00, 1840.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17'),
(352, 191, 5, 'ماده - سيارة 4356', 6000.000, 'م', 16.00, 96000.00, '2026-07-23 12:26:17', '2026-07-23 12:26:17');

-- --------------------------------------------------------

--
-- Table structure for table `treasury_transactions`
--

CREATE TABLE `treasury_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `type` enum('in','out') COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `balance_after` decimal(14,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint UNSIGNED DEFAULT NULL,
  `recorded_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','accountant','engineer','inventory_officer','inventory_manager') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'engineer',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `phone`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Omar - Inventory Manager', 'omar@newsolidup.com', NULL, '$2y$12$liTg48KsM.Jkkx30A15w..bRVcqfhyvtimaEnHjgN23INujgbfK.y', 'inventory_manager', NULL, 1, NULL, '2026-07-23 12:06:10', '2026-07-23 12:06:10'),
(2, 'Mohamed - Accountant', 'mohamed@newsolidup.com', NULL, '$2y$12$ZRZpn6I8WKYidS.O8OJxNu4WQV6E0yw712.8O5i8SX/FSndjdRqfq', 'accountant', NULL, 1, NULL, '2026-07-23 12:06:10', '2026-07-23 12:06:10');

-- --------------------------------------------------------

--
-- Table structure for table `weekly_schedules`
--

CREATE TABLE `weekly_schedules` (
  `id` bigint UNSIGNED NOT NULL,
  `week_start` date NOT NULL,
  `week_end` date NOT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `status` enum('draft','published','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `weekly_schedule_entries`
--

CREATE TABLE `weekly_schedule_entries` (
  `id` bigint UNSIGNED NOT NULL,
  `weekly_schedule_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `site_location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity_m3` decimal(10,3) NOT NULL,
  `delivery_date` date NOT NULL,
  `delivery_time` time DEFAULT NULL,
  `engineer_notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendance_employee_id_date_unique` (`employee_id`,`date`),
  ADD KEY `attendance_recorded_by_foreign` (`recorded_by`),
  ADD KEY `attendance_date_index` (`date`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_user_id_foreign` (`user_id`),
  ADD KEY `audit_logs_action_index` (`action`),
  ADD KEY `audit_logs_model_model_id_index` (`model`,`model_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `concrete_mixes`
--
ALTER TABLE `concrete_mixes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contributors`
--
ALTER TABLE `contributors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contributor_payments`
--
ALTER TABLE `contributor_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contributor_payments_contributor_id_foreign` (`contributor_id`),
  ADD KEY `contributor_payments_treasury_transaction_id_foreign` (`treasury_transaction_id`);

--
-- Indexes for table `credits`
--
ALTER TABLE `credits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `credits_created_by_foreign` (`created_by`),
  ADD KEY `credits_creditable_type_creditable_id_index` (`creditable_type`,`creditable_id`),
  ADD KEY `credits_due_date_index` (`due_date`),
  ADD KEY `credits_status_index` (`status`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customers_created_by_foreign` (`created_by`),
  ADD KEY `customers_concrete_type_index` (`concrete_type`),
  ADD KEY `customers_is_active_index` (`is_active`);

--
-- Indexes for table `customer_payments`
--
ALTER TABLE `customer_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_payments_customer_id_foreign` (`customer_id`),
  ADD KEY `customer_payments_order_id_foreign` (`order_id`),
  ADD KEY `customer_payments_recorded_by_foreign` (`recorded_by`),
  ADD KEY `customer_payments_payment_date_index` (`payment_date`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_borrows`
--
ALTER TABLE `employee_borrows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_borrows_recorded_by_foreign` (`recorded_by`),
  ADD KEY `employee_borrows_employee_id_status_index` (`employee_id`,`status`),
  ADD KEY `employee_borrows_borrow_date_index` (`borrow_date`);

--
-- Indexes for table `employee_borrow_deductions`
--
ALTER TABLE `employee_borrow_deductions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_borrow_deductions_borrow_id_foreign` (`borrow_id`),
  ADD KEY `employee_borrow_deductions_payroll_id_foreign` (`payroll_id`);

--
-- Indexes for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_deductions_employee_id_foreign` (`employee_id`),
  ADD KEY `employee_deductions_recorded_by_foreign` (`recorded_by`),
  ADD KEY `employee_deductions_deduction_date_index` (`deduction_date`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `equipment_fuel_logs`
--
ALTER TABLE `equipment_fuel_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_fuel_logs_recorded_by_foreign` (`recorded_by`),
  ADD KEY `equipment_fuel_logs_equipment_id_log_date_index` (`equipment_id`,`log_date`),
  ADD KEY `equipment_fuel_logs_inventory_item_id_foreign` (`inventory_item_id`),
  ADD KEY `equipment_fuel_logs_inventory_movement_id_foreign` (`inventory_movement_id`);

--
-- Indexes for table `equipment_maintenance`
--
ALTER TABLE `equipment_maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_maintenance_supplier_id_foreign` (`supplier_id`),
  ADD KEY `equipment_maintenance_recorded_by_foreign` (`recorded_by`),
  ADD KEY `equipment_maintenance_equipment_id_maintenance_date_index` (`equipment_id`,`maintenance_date`);

--
-- Indexes for table `equipment_tools`
--
ALTER TABLE `equipment_tools`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `equipment_tool_movements`
--
ALTER TABLE `equipment_tool_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_tool_movements_equipment_tool_id_foreign` (`equipment_tool_id`),
  ADD KEY `equipment_tool_movements_treasury_transaction_id_foreign` (`treasury_transaction_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expenses_recorded_by_foreign` (`recorded_by`),
  ADD KEY `expenses_expense_date_index` (`expense_date`),
  ADD KEY `expenses_category_index` (`category`),
  ADD KEY `expenses_contributor_id_foreign` (`contributor_id`);

--
-- Indexes for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_movements_supplier_id_foreign` (`supplier_id`),
  ADD KEY `inventory_movements_recorded_by_foreign` (`recorded_by`),
  ADD KEY `inventory_movements_movement_date_index` (`movement_date`),
  ADD KEY `inventory_movements_inventory_item_id_movement_date_index` (`inventory_item_id`,`movement_date`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `land_rents`
--
ALTER TABLE `land_rents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `land_rent_payments`
--
ALTER TABLE `land_rent_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `land_rent_payments_land_rent_id_foreign` (`land_rent_id`),
  ADD KEY `land_rent_payments_recorded_by_foreign` (`recorded_by`);

--
-- Indexes for table `material_densities`
--
ALTER TABLE `material_densities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `material_densities_material_name_unique` (`material_name`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mix_recipes`
--
ALTER TABLE `mix_recipes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mix_recipes_cement_per_m3_unique` (`cement_per_m3`);

--
-- Indexes for table `neighboring_stations`
--
ALTER TABLE `neighboring_stations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `neighboring_station_transactions`
--
ALTER TABLE `neighboring_station_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `neighboring_station_transactions_neighboring_station_id_foreign` (`neighboring_station_id`),
  ADD KEY `neighboring_station_transactions_recorded_by_foreign` (`recorded_by`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_concrete_mix_id_foreign` (`concrete_mix_id`),
  ADD KEY `orders_created_by_foreign` (`created_by`),
  ADD KEY `orders_delivery_date_index` (`delivery_date`),
  ADD KEY `orders_status_index` (`status`),
  ADD KEY `orders_customer_id_index` (`customer_id`);

--
-- Indexes for table `order_expenses`
--
ALTER TABLE `order_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_expenses_order_id_foreign` (`order_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payroll_employee_id_period_month_period_year_unique` (`employee_id`,`period_month`,`period_year`),
  ADD KEY `payroll_created_by_foreign` (`created_by`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receipts_supplier_id_foreign` (`supplier_id`),
  ADD KEY `receipts_recorded_by_foreign` (`recorded_by`),
  ADD KEY `receipts_receipt_date_index` (`receipt_date`);

--
-- Indexes for table `receipt_items`
--
ALTER TABLE `receipt_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receipt_items_receipt_id_foreign` (`receipt_id`);

--
-- Indexes for table `rental_contracts`
--
ALTER TABLE `rental_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rental_contracts_supplier_id_foreign` (`supplier_id`);

--
-- Indexes for table `rental_maintenance`
--
ALTER TABLE `rental_maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rental_maintenance_rental_contract_id_foreign` (`rental_contract_id`),
  ADD KEY `rental_maintenance_recorded_by_foreign` (`recorded_by`);

--
-- Indexes for table `rental_shifts`
--
ALTER TABLE `rental_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rental_shifts_rental_contract_id_foreign` (`rental_contract_id`),
  ADD KEY `rental_shifts_fuel_inventory_item_id_foreign` (`fuel_inventory_item_id`),
  ADD KEY `rental_shifts_fuel_inventory_movement_id_foreign` (`fuel_inventory_movement_id`),
  ADD KEY `rental_shifts_recorded_by_foreign` (`recorded_by`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier_payments`
--
ALTER TABLE `supplier_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_payments_supplier_id_foreign` (`supplier_id`),
  ADD KEY `supplier_payments_supplier_purchase_id_foreign` (`supplier_purchase_id`),
  ADD KEY `supplier_payments_recorded_by_foreign` (`recorded_by`),
  ADD KEY `supplier_payments_payment_date_index` (`payment_date`);

--
-- Indexes for table `supplier_purchases`
--
ALTER TABLE `supplier_purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_purchases_created_by_foreign` (`created_by`),
  ADD KEY `supplier_purchases_purchase_date_index` (`purchase_date`),
  ADD KEY `supplier_purchases_supplier_id_index` (`supplier_id`);

--
-- Indexes for table `supplier_purchase_items`
--
ALTER TABLE `supplier_purchase_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_purchase_items_supplier_purchase_id_foreign` (`supplier_purchase_id`),
  ADD KEY `supplier_purchase_items_inventory_item_id_foreign` (`inventory_item_id`);

--
-- Indexes for table `treasury_transactions`
--
ALTER TABLE `treasury_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `treasury_transactions_recorded_by_foreign` (`recorded_by`),
  ADD KEY `treasury_transactions_transaction_date_index` (`transaction_date`),
  ADD KEY `treasury_transactions_type_index` (`type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `weekly_schedules`
--
ALTER TABLE `weekly_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `weekly_schedules_created_by_foreign` (`created_by`),
  ADD KEY `weekly_schedules_week_start_index` (`week_start`);

--
-- Indexes for table `weekly_schedule_entries`
--
ALTER TABLE `weekly_schedule_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `weekly_schedule_entries_weekly_schedule_id_foreign` (`weekly_schedule_id`),
  ADD KEY `weekly_schedule_entries_order_id_foreign` (`order_id`),
  ADD KEY `weekly_schedule_entries_customer_id_foreign` (`customer_id`),
  ADD KEY `weekly_schedule_entries_delivery_date_index` (`delivery_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `concrete_mixes`
--
ALTER TABLE `concrete_mixes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contributors`
--
ALTER TABLE `contributors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contributor_payments`
--
ALTER TABLE `contributor_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `credits`
--
ALTER TABLE `credits`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=192;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_payments`
--
ALTER TABLE `customer_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_borrows`
--
ALTER TABLE `employee_borrows`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_borrow_deductions`
--
ALTER TABLE `employee_borrow_deductions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment_fuel_logs`
--
ALTER TABLE `equipment_fuel_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment_maintenance`
--
ALTER TABLE `equipment_maintenance`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment_tools`
--
ALTER TABLE `equipment_tools`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment_tool_movements`
--
ALTER TABLE `equipment_tool_movements`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=193;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `land_rents`
--
ALTER TABLE `land_rents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `land_rent_payments`
--
ALTER TABLE `land_rent_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `material_densities`
--
ALTER TABLE `material_densities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `mix_recipes`
--
ALTER TABLE `mix_recipes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `neighboring_stations`
--
ALTER TABLE `neighboring_stations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `neighboring_station_transactions`
--
ALTER TABLE `neighboring_station_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_expenses`
--
ALTER TABLE `order_expenses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `receipt_items`
--
ALTER TABLE `receipt_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rental_contracts`
--
ALTER TABLE `rental_contracts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rental_maintenance`
--
ALTER TABLE `rental_maintenance`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rental_shifts`
--
ALTER TABLE `rental_shifts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `supplier_payments`
--
ALTER TABLE `supplier_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_purchases`
--
ALTER TABLE `supplier_purchases`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=192;

--
-- AUTO_INCREMENT for table `supplier_purchase_items`
--
ALTER TABLE `supplier_purchase_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=353;

--
-- AUTO_INCREMENT for table `treasury_transactions`
--
ALTER TABLE `treasury_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `weekly_schedules`
--
ALTER TABLE `weekly_schedules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `weekly_schedule_entries`
--
ALTER TABLE `weekly_schedule_entries`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `contributor_payments`
--
ALTER TABLE `contributor_payments`
  ADD CONSTRAINT `contributor_payments_contributor_id_foreign` FOREIGN KEY (`contributor_id`) REFERENCES `contributors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contributor_payments_treasury_transaction_id_foreign` FOREIGN KEY (`treasury_transaction_id`) REFERENCES `treasury_transactions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `credits`
--
ALTER TABLE `credits`
  ADD CONSTRAINT `credits_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customer_payments`
--
ALTER TABLE `customer_payments`
  ADD CONSTRAINT `customer_payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `customer_payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_payments_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employee_borrows`
--
ALTER TABLE `employee_borrows`
  ADD CONSTRAINT `employee_borrows_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_borrows_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employee_borrow_deductions`
--
ALTER TABLE `employee_borrow_deductions`
  ADD CONSTRAINT `employee_borrow_deductions_borrow_id_foreign` FOREIGN KEY (`borrow_id`) REFERENCES `employee_borrows` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_borrow_deductions_payroll_id_foreign` FOREIGN KEY (`payroll_id`) REFERENCES `payroll` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  ADD CONSTRAINT `employee_deductions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_deductions_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `equipment_fuel_logs`
--
ALTER TABLE `equipment_fuel_logs`
  ADD CONSTRAINT `equipment_fuel_logs_equipment_id_foreign` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_fuel_logs_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_fuel_logs_inventory_movement_id_foreign` FOREIGN KEY (`inventory_movement_id`) REFERENCES `inventory_movements` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_fuel_logs_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `equipment_maintenance`
--
ALTER TABLE `equipment_maintenance`
  ADD CONSTRAINT `equipment_maintenance_equipment_id_foreign` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_maintenance_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_maintenance_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `equipment_tool_movements`
--
ALTER TABLE `equipment_tool_movements`
  ADD CONSTRAINT `equipment_tool_movements_equipment_tool_id_foreign` FOREIGN KEY (`equipment_tool_id`) REFERENCES `equipment_tools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_tool_movements_treasury_transaction_id_foreign` FOREIGN KEY (`treasury_transaction_id`) REFERENCES `treasury_transactions` (`id`);

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_contributor_id_foreign` FOREIGN KEY (`contributor_id`) REFERENCES `contributors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expenses_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD CONSTRAINT `inventory_movements_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_movements_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_movements_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `land_rent_payments`
--
ALTER TABLE `land_rent_payments`
  ADD CONSTRAINT `land_rent_payments_land_rent_id_foreign` FOREIGN KEY (`land_rent_id`) REFERENCES `land_rents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `land_rent_payments_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `neighboring_station_transactions`
--
ALTER TABLE `neighboring_station_transactions`
  ADD CONSTRAINT `neighboring_station_transactions_neighboring_station_id_foreign` FOREIGN KEY (`neighboring_station_id`) REFERENCES `neighboring_stations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `neighboring_station_transactions_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_concrete_mix_id_foreign` FOREIGN KEY (`concrete_mix_id`) REFERENCES `concrete_mixes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `order_expenses`
--
ALTER TABLE `order_expenses`
  ADD CONSTRAINT `order_expenses_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payroll_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `receipts`
--
ALTER TABLE `receipts`
  ADD CONSTRAINT `receipts_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `receipts_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `receipt_items`
--
ALTER TABLE `receipt_items`
  ADD CONSTRAINT `receipt_items_receipt_id_foreign` FOREIGN KEY (`receipt_id`) REFERENCES `receipts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rental_contracts`
--
ALTER TABLE `rental_contracts`
  ADD CONSTRAINT `rental_contracts_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `rental_maintenance`
--
ALTER TABLE `rental_maintenance`
  ADD CONSTRAINT `rental_maintenance_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rental_maintenance_rental_contract_id_foreign` FOREIGN KEY (`rental_contract_id`) REFERENCES `rental_contracts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rental_shifts`
--
ALTER TABLE `rental_shifts`
  ADD CONSTRAINT `rental_shifts_fuel_inventory_item_id_foreign` FOREIGN KEY (`fuel_inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rental_shifts_fuel_inventory_movement_id_foreign` FOREIGN KEY (`fuel_inventory_movement_id`) REFERENCES `inventory_movements` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rental_shifts_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `rental_shifts_rental_contract_id_foreign` FOREIGN KEY (`rental_contract_id`) REFERENCES `rental_contracts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_payments`
--
ALTER TABLE `supplier_payments`
  ADD CONSTRAINT `supplier_payments_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supplier_payments_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `supplier_payments_supplier_purchase_id_foreign` FOREIGN KEY (`supplier_purchase_id`) REFERENCES `supplier_purchases` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `supplier_purchases`
--
ALTER TABLE `supplier_purchases`
  ADD CONSTRAINT `supplier_purchases_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supplier_purchases_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `supplier_purchase_items`
--
ALTER TABLE `supplier_purchase_items`
  ADD CONSTRAINT `supplier_purchase_items_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supplier_purchase_items_supplier_purchase_id_foreign` FOREIGN KEY (`supplier_purchase_id`) REFERENCES `supplier_purchases` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `treasury_transactions`
--
ALTER TABLE `treasury_transactions`
  ADD CONSTRAINT `treasury_transactions_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `weekly_schedules`
--
ALTER TABLE `weekly_schedules`
  ADD CONSTRAINT `weekly_schedules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `weekly_schedule_entries`
--
ALTER TABLE `weekly_schedule_entries`
  ADD CONSTRAINT `weekly_schedule_entries_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `weekly_schedule_entries_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `weekly_schedule_entries_weekly_schedule_id_foreign` FOREIGN KEY (`weekly_schedule_id`) REFERENCES `weekly_schedules` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
