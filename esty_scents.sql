-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 31, 2025 at 05:45 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `esty_scents`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_activity_logs`
--

CREATE TABLE `admin_activity_logs` (
  `id` int(11) NOT NULL,
  `admin_username` varchar(50) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_activity_logs`
--

INSERT INTO `admin_activity_logs` (`id`, `admin_username`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 'admin', 'Logout', NULL, '::1', '2025-10-26 10:31:59'),
(2, 'admin', 'Login', NULL, '::1', '2025-10-26 10:32:06'),
(3, 'admin', 'Login', NULL, '::1', '2025-10-26 10:37:48'),
(4, 'admin', 'Login', NULL, '::1', '2025-10-26 14:33:31'),
(5, 'admin', 'Logout', NULL, '::1', '2025-10-26 14:43:05'),
(6, 'admin', 'Login', NULL, '::1', '2025-10-26 15:03:22'),
(7, 'admin', 'Login', NULL, '::1', '2025-10-26 18:11:08'),
(8, 'admin', 'Logout', NULL, '::1', '2025-10-26 18:11:10'),
(9, 'admin', 'Login', NULL, '::1', '2025-10-26 19:44:46'),
(10, 'admin', 'Login', NULL, '::1', '2025-10-26 19:44:58'),
(11, 'admin', 'Login', NULL, '::1', '2025-10-26 20:28:58'),
(12, 'admin', 'Login', NULL, '::1', '2025-10-26 20:29:18'),
(13, 'admin', 'Login', NULL, '::1', '2025-10-26 20:30:32'),
(14, 'admin', 'Login', NULL, '::1', '2025-10-26 20:34:56'),
(15, 'admin', 'Login', NULL, '::1', '2025-10-26 20:37:08'),
(16, 'admin', 'Login', NULL, '::1', '2025-10-26 21:09:56'),
(17, 'admin', 'Login', NULL, '::1', '2025-10-26 21:14:44'),
(18, 'admin', 'Logout', NULL, '::1', '2025-10-26 21:15:49'),
(19, 'admin', 'Login', NULL, '::1', '2025-10-27 04:03:06'),
(20, 'admin', 'Login', NULL, '::1', '2025-10-27 14:59:58'),
(21, 'admin', 'Login', NULL, '::1', '2025-10-27 15:07:24'),
(22, 'admin', 'Login', NULL, '::1', '2025-10-27 17:07:06'),
(23, 'admin', 'Logout', NULL, '::1', '2025-10-27 17:23:33'),
(24, 'admin', 'Logout', NULL, '::1', '2025-10-28 07:09:21'),
(25, 'admin', 'Login', NULL, '::1', '2025-10-28 07:09:27'),
(26, 'admin', 'Login', NULL, '::1', '2025-10-28 07:54:23'),
(27, 'admin', 'Logout', NULL, '::1', '2025-10-28 08:15:02'),
(28, 'admin', 'Login', NULL, '::1', '2025-10-28 08:15:11'),
(29, 'admin', 'Logout', NULL, '::1', '2025-10-28 10:18:03'),
(30, 'admin', 'Login', NULL, '::1', '2025-10-28 10:18:22'),
(31, 'admin', 'Logout', NULL, '::1', '2025-10-28 10:38:18'),
(32, 'admin', 'Login', NULL, '::1', '2025-10-28 10:38:24'),
(33, 'admin', 'Logout', NULL, '::1', '2025-10-28 11:32:36'),
(34, 'admin', 'Login', NULL, '::1', '2025-10-28 11:32:41'),
(35, 'admin', 'Login', NULL, '::1', '2025-10-28 17:14:59'),
(36, 'admin', 'Login', NULL, '::1', '2025-10-28 17:38:29'),
(37, 'admin', 'Login', NULL, '::1', '2025-10-28 18:18:54'),
(38, 'admin', 'Login', NULL, '::1', '2025-10-28 21:04:59'),
(39, 'admin', 'Logout', NULL, '::1', '2025-10-28 21:32:30'),
(40, 'admin', 'Login', NULL, '::1', '2025-10-29 06:37:07'),
(41, 'admin', 'Login', NULL, '::1', '2025-10-29 08:51:15'),
(42, 'admin', 'Login', NULL, '::1', '2025-10-29 18:15:04'),
(43, 'admin', 'Login', NULL, '::1', '2025-10-31 16:20:30'),
(44, 'admin', 'Logout', NULL, '::1', '2025-10-31 16:24:57');

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `admin_username` varchar(100) NOT NULL,
  `action` varchar(150) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_username`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 'admin', 'product_deleted', 'Deleted product #11 (Unknown)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 06:26:33'),
(2, 'admin', 'admin_logout', 'Administrator logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 06:30:55'),
(3, 'admin', 'admin_login_failed', 'Failed admin login attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 06:31:01'),
(4, 'admin', 'admin_login_failed', 'Failed admin login attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 06:31:07'),
(5, 'admin', 'admin_login_success', 'Successful admin login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 06:31:15'),
(6, 'admin', 'admin_logout', 'Administrator logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 06:32:40'),
(7, 'admin', 'admin_login_failed', 'Failed admin login attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 06:32:47'),
(8, 'admin', 'admin_login_success', 'Successful admin login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 06:32:54'),
(9, 'admin', 'admin_logout', 'Administrator logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 06:48:16'),
(10, 'admin', 'admin_login_failed', 'Failed admin login attempt', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 06:48:25'),
(11, 'admin', 'admin_login_success', 'Successful admin login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 06:48:32'),
(12, 'admin', 'admin_logout', 'Administrator logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 06:51:39'),
(13, 'admin', 'admin_login_success', 'Successful admin login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 06:57:02'),
(14, 'admin', 'admin_logout', 'Administrator logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 07:02:13'),
(15, 'admin', 'admin_login_success', 'Successful admin login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 07:02:26'),
(16, 'admin', 'admin_logout', 'Administrator logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 07:21:33'),
(17, 'admin', 'admin_login_success', 'Successful admin login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 07:21:46'),
(18, 'admin', 'admin_logout', 'Administrator logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 07:47:29'),
(19, 'admin', 'admin_login_success', 'Successful admin login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-26 07:47:37');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$4JZrktiFij92/KytXGOlw.TCV1AV9SMXfBGFchQIRFl7kWSul2He2', '2025-10-26 16:54:45');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'sample', 'sample brand', '2025-10-26 21:13:07');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `product_id`, `quantity`, `created_at`) VALUES
(54, 9, 8, 1, '2025-10-28 13:38:13');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(2, 'sample categories', 'sample categories description', '2025-10-26 21:13:44');

-- --------------------------------------------------------

--
-- Table structure for table `compare_products`
--

CREATE TABLE `compare_products` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_otp_verifications`
--

CREATE TABLE `login_otp_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `attempts` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT (current_timestamp() + interval 10 minute)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_otp_verifications`
--

INSERT INTO `login_otp_verifications` (`id`, `user_id`, `email`, `otp`, `attempts`, `created_at`, `expires_at`) VALUES
(66, 1, 'juandela@gmail.com', '276501', 0, '2025-10-30 17:25:52', '2025-10-30 17:35:52');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `receipt_sent_at` timestamp NULL DEFAULT NULL,
  `paymongo_ref` varchar(255) DEFAULT NULL,
  `paymongo_payment_ref` varchar(255) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `email`, `address`, `payment_method`, `total`, `created_at`, `status`, `receipt_sent_at`, `paymongo_ref`, `paymongo_payment_ref`, `completed_at`) VALUES
(1, NULL, 'ALBERTO CONSUELO RILI', 'mannypacquiao@gmail.com', '74 GENCON', 'cod', 299.00, '2025-09-22 17:34:15', 'completed', NULL, NULL, NULL, '2025-10-23 17:57:50'),
(2, NULL, 'ALBERT ALCANTARA RILI', 'mannypacquiao@gmail.com', '74 GENCON', 'cod', 299.00, '2025-09-22 17:35:58', 'completed', NULL, NULL, NULL, '2025-10-23 17:57:48'),
(3, NULL, 'ALBERT ALCANTARA RILI', 'mannypacquiao@gmail.com', '7831 TOKYO JAPAN, TONDO', 'cod', 299.00, '2025-09-22 17:37:50', 'completed', NULL, NULL, NULL, '2025-10-23 17:57:46'),
(4, NULL, 'ALBERT ALCANTARA RILI', 'mannypacquiao@gmail.com', '7831 TOKYO JAPAN, TONDO', 'gcash', 598.00, '2025-09-22 20:16:26', 'completed', NULL, NULL, NULL, '2025-10-23 17:57:44'),
(5, NULL, 'juan dela cruz', 'juandela@gmail.com', 'BGC Tondo, Cavite', 'cod', 299.00, '2025-09-22 20:39:01', 'completed', NULL, NULL, NULL, '2025-10-23 17:57:42'),
(6, NULL, 'Alice Santos', NULL, NULL, NULL, 1200.00, '2024-09-02 02:00:00', 'completed', NULL, NULL, NULL, '2024-09-02 10:00:00'),
(8, NULL, 'Jenna Reyes', NULL, NULL, NULL, 950.00, '2024-09-15 03:10:00', 'completed', NULL, NULL, NULL, NULL),
(9, NULL, 'Luis Torres', NULL, NULL, NULL, 1800.00, '2024-09-20 01:00:00', 'completed', NULL, NULL, NULL, '2024-09-20 09:00:00'),
(10, NULL, 'Cindy Flores', NULL, NULL, NULL, 2000.00, '2024-09-25 07:00:00', 'completed', NULL, NULL, NULL, '2024-09-26 08:30:00'),
(11, NULL, 'SincerelyYours', 'jinjuju@gmail.com', 'Caloocan City', 'cod', 299.00, '2025-10-23 08:40:17', 'processing', NULL, NULL, NULL, NULL),
(12, NULL, 'Rama', 'jinjuju@gmail.com', 'Egypt', 'cod', 20930.00, '2025-10-23 10:14:06', 'completed', NULL, NULL, NULL, NULL),
(14, NULL, 'John Lennon', 'johnlennon@yoohoo.com', 'Penny Lane', 'gcash', 6279.00, '2025-10-26 09:24:07', 'processing', NULL, NULL, NULL, NULL),
(15, NULL, 'GOJO SATORU', 'gojosatoru@gmail.com', 'PRISON REALM CALOOCAN CITY', 'gcash', 99.00, '2025-10-26 14:52:18', 'cancelled', NULL, NULL, NULL, NULL),
(28, 5, 'wadw', 'johnlennon@yoohoo.com', 'awsdw', 'gcash', 299.00, '2025-10-28 09:58:36', 'pending', NULL, 'src_ngQjuULSxACyD433TKpciXMK', NULL, NULL),
(34, 9, 'wadw', 'ronanaleckgatmaitan@gmail.com', 'adwd', 'gcash', 199.00, '2025-10-28 13:29:20', 'pending', NULL, 'src_Hh43LxsGBSyB3FGQY9TdtFQ7', 'pay_b6jSur6M7SELe5wFFdDEpLFv', NULL),
(35, NULL, 'sssdwa', 'johnlennon@yoohoo.com', 'asdw', 'gcash', 299.00, '2025-10-28 13:29:45', 'pending', NULL, 'src_xH13CsvCVv1A8dBNJv3MLZq6', 'pay_zsXrTdspzgo8FCG8yR6mxG2p', NULL),
(36, 5, 'wewe', 'johnlennon@yoohoo.com', 'wdasdw', 'gcash', 299.00, '2025-10-28 13:30:05', 'processing', NULL, 'src_4Lw7mbLgbCNRVEbdvUcp8aHR', 'pay_z4FWxGjvx73gSNckxYpj2W9z', NULL),
(37, 5, 'John Lennon', 'johnlennon@yoohoo.com', 'Penny Lane', 'gcash', 299.00, '2025-10-28 13:30:12', 'processing', NULL, 'src_8bpQDCrzJEPKrw5pqNPJTD2v', 'pay_B9LgPq5YCVU7mh5JGBFSvt2Z', NULL),
(38, 9, 'Ronan', 'ronanaleckgatmaitan@gmail.com', 'Caloocan City', 'gcash', 299.00, '2025-10-28 13:30:28', 'pending', NULL, 'src_UayxgyREkq4Nc4bbTgdPbrNj', 'pay_3FzQ9Mf6mvMKm8wNz7HmgSVw', NULL),
(40, 9, 'aswrw', 'ronanaleckgatmaitan@gmail.com', 'wasdw', 'gcash', 199.00, '2025-10-28 13:30:41', 'processing', NULL, 'src_MvQqegERVCTqxceGGnJLkr3P', 'pay_c9fCb6PBoMXghu1N3rDQv1C9', NULL),
(41, 9, 'wasdw', 'ronanaleckgatmaitan@gmail.com', 'wasdw', 'gcash', 299.00, '2025-10-28 13:37:56', 'completed', NULL, 'src_XLYXQUiFws9y6z4P33XeBbwz', 'pay_7XJjvHGgYW2CSxmY2MfdZo6t', NULL),
(42, 8, 'adasadadad', 'dumpacri@gmail.com', 'adsa', 'cod', 299.00, '2025-10-31 16:16:55', 'processing', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_cart_backups`
--

CREATE TABLE `order_cart_backups` (
  `order_id` int(11) NOT NULL,
  `cart_json` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_cart_backups`
--

INSERT INTO `order_cart_backups` (`order_id`, `cart_json`, `created_at`) VALUES
(42, '[{\"id\":2,\"name\":\"Fresh Bamboo Car Diffuser\",\"price\":\"299.00\",\"quantity\":1,\"image\":\"images\\/featprod2.jpg\"}]', '2025-11-01 00:16:55');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_name`, `price`, `quantity`, `subtotal`) VALUES
(1, 1, 'Fresh Bamboo Car Diffuser', 299.00, 1, 299.00),
(2, 2, 'Fouger Marine Car Diffuser', 299.00, 1, 299.00),
(3, 3, 'Fouger Marine Car Diffuser', 299.00, 1, 299.00),
(4, 4, 'English Lavender Car Diffuser', 299.00, 2, 598.00),
(5, 5, 'Fouger Marine Car Diffuser', 299.00, 1, 299.00),
(6, 11, 'Fouger Marine Car Diffuser', 299.00, 1, 299.00),
(7, 12, 'Fouger Marine Car Diffuser', 299.00, 70, 20930.00),
(17, 14, 'English Lavender Car Diffuser', 299.00, 21, 6279.00),
(18, 15, 'Sukuna Scent', 99.00, 1, 99.00),
(36, 28, 'Fouger Marine Car Diffuser', 299.00, 1, 299.00),
(42, 34, 'English Lavender Helmet Deodorizer', 199.00, 1, 199.00),
(43, 35, 'English Lavender Car Diffuser', 299.00, 1, 299.00),
(44, 36, 'English Lavender Car Diffuser', 299.00, 1, 299.00),
(45, 37, 'English Lavender Car Diffuser', 299.00, 1, 299.00),
(46, 38, 'English Lavender Car Diffuser', 299.00, 1, 299.00),
(48, 40, 'English Lavender Helmet Deodorizer', 199.00, 1, 199.00),
(49, 41, 'English Lavender Car Diffuser', 299.00, 1, 299.00),
(50, 42, 'Fresh Bamboo Car Diffuser', 299.00, 1, 299.00);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `reset_token` varchar(64) NOT NULL,
  `reset_otp` varchar(6) NOT NULL,
  `attempts` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT (current_timestamp() + interval 30 minute),
  `verified_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `email`, `reset_token`, `reset_otp`, `attempts`, `created_at`, `expires_at`, `verified_at`) VALUES
(1, 8, 'dumpacri@gmail.com', 'f80f23daeaa0119e86d6616a178600a78ff298abb04f28d413654dc7aa5ba6a4', '592438', 0, '2025-10-26 18:21:13', '2025-10-26 18:51:13', NULL),
(2, 4, 'jinjuju@gmail.com', '22c8557bb472858ca4a21faca30259aa419ca100a10bc5f9043b5bc9cfb84c27', '543527', 0, '2025-10-28 07:54:08', '2025-10-28 08:24:08', NULL),
(3, 8, 'dumpacri@gmail.com', '2cc2630b965ae591b38f93f96ab0c6b546dd3437eb299d45f0573cd36fdfecf8', '235206', 0, '2025-10-28 07:54:39', '2025-10-28 08:24:39', NULL),
(4, 8, 'dumpacri@gmail.com', '27d70f1f93e839d1b4861707c66fcd4cd36dd9fec169d4ad1a6ea0072acce0fc', '965198', 0, '2025-10-30 16:42:14', '2025-10-30 17:12:14', NULL),
(5, 8, 'dumpacri@gmail.com', '51a7a2e01223d57a47f1090477b550d7f0d028a57e0125101ecb0970400adb89', '754672', 0, '2025-10-30 16:47:13', '2025-10-30 17:17:13', NULL),
(6, 8, 'dumpacri@gmail.com', '104f0a4d1c7f7a1bb1c75a62548c83f64da26b1ef28bedcc8a681d35d24d97a1', '604444', 0, '2025-10-30 17:46:26', '2025-10-30 18:16:26', NULL),
(7, 8, 'dumpacri@gmail.com', 'b6454635c67d78f866fcc19b525afbecec0ffdf5df2c88ed01fd237cc8810200', '241146', 0, '2025-10-30 17:47:13', '2025-10-30 18:17:13', NULL),
(8, 8, 'dumpacri@gmail.com', 'b53f3b334290d1a2139db49e4849db1ec9abdd90fa1e4dfafdd6c5d560ef485b', '927039', 0, '2025-10-30 17:49:11', '2025-10-30 18:19:11', NULL),
(9, 8, 'dumpacri@gmail.com', '616ad5143849f96820765fc25d7af9513eb29b828b7aa4650d5cf5bf2b594622', '636596', 0, '2025-10-30 17:56:33', '2025-10-30 18:26:33', NULL),
(10, 8, 'dumpacri@gmail.com', '2dfbd9fe07f749e6f62d06edbeca80912c8b52326b0a9a4ca515420aafbd641d', '374608', 0, '2025-10-30 17:58:56', '2025-10-30 18:28:56', NULL),
(11, 8, 'dumpacri@gmail.com', 'c5b4b5bfeaed058e7f463d139e499255dac8c835f87d611733572026a9ee4681', '615290', 0, '2025-10-30 18:01:06', '2025-10-30 18:31:06', NULL),
(12, 8, 'dumpacri@gmail.com', '1f173267bd14dab4977fe308b738dd0f2f702048e9f589dc60395465f659cee3', '857959', 0, '2025-10-30 18:07:54', '2025-10-30 18:37:54', NULL),
(13, 8, 'dumpacri@gmail.com', '8425e76b63c4c9486b0cccf9a03062f0b30a389ae0168ae0b8bcbf658973522a', '555053', 0, '2025-10-30 18:08:51', '2025-10-30 18:38:51', NULL),
(14, 8, 'dumpacri@gmail.com', 'fc56141b652116e1f53d0bd6ad94b97e6ab20e8c5694f556eb5f4881ea414892', '675919', 0, '2025-10-30 18:14:03', '2025-10-30 18:44:03', NULL),
(15, 8, 'dumpacri@gmail.com', 'fd41f8bd6a249d9d26c8aab583a2444dd0134004455659b36dcd56de65ba1da5', '617806', 0, '2025-10-30 18:19:28', '2025-10-30 18:49:28', NULL),
(16, 8, 'dumpacri@gmail.com', 'c523791b24d2c00b949239b3d2ca444ee889b17301feeb7a0581272c481e8f24', '040110', 0, '2025-10-30 18:26:12', '2025-10-30 18:56:12', NULL),
(19, 1, 'juandela@gmail.com', 'bebc471b74a295f50c759fe01ecfca16579d3991fe84117adb06fb2eb3dc43e0', '383419', 0, '2025-10-30 19:19:29', '2025-10-30 19:49:29', NULL),
(20, 1, 'juandela@gmail.com', '6a23451e5ac73e07831b2e5844bb587ff3979764623106472eb8aa745a5002cb', '736025', 0, '2025-10-30 19:20:12', '2025-10-30 19:50:12', NULL),
(21, 8, 'dumpacri@gmail.com', '8a79bd6a5e658f540e640e6563733247aacb3b03575156e35ac493477625bb08', '609447', 0, '2025-10-30 19:21:21', '2025-10-30 19:51:21', NULL),
(22, 8, 'dumpacri@gmail.com', 'b43e531789b0fee168079a0811d7f6efa5752d9692ab80dffd6174ce4cab446d', '131076', 0, '2025-10-30 19:33:15', '2025-10-30 20:03:15', NULL),
(23, 8, 'dumpacri@gmail.com', '02a9d5acbf72c4bd2389201d27a53687d1f11986382a61793358f3b8815a6729', '086947', 0, '2025-10-30 19:41:57', '2025-10-30 20:11:57', NULL),
(24, 8, 'dumpacri@gmail.com', '1b1975e77ad226b9b5d88167c62895d21380a6bebbba3d619d737d05d1d67ad6', '163413', 0, '2025-10-30 19:59:50', '2025-10-30 20:29:50', NULL),
(25, 8, 'dumpacri@gmail.com', '277e5e9f25ffb9167bbb752c5165f5f5dda54c640b05f68997e1b88f05cf1121', '285169', 0, '2025-10-30 20:28:10', '2025-10-30 20:58:10', '2025-10-30 20:31:24'),
(26, 8, 'dumpacri@gmail.com', '169ee039afb4bf3b46d0b09d484ee29ef0df8076bd1b6721760b5a1d2d9230ef', '880893', 0, '2025-10-30 20:45:10', '2025-10-30 21:15:10', NULL),
(30, 8, 'dumpacri@gmail.com', '882d864adaa6b51afa53c00c5e6ed8598d8b179b2e66ac40cdd54e48f3030914', '353054', 0, '2025-10-31 13:24:26', '2025-10-31 13:54:26', NULL),
(34, 8, 'dumpacri@gmail.com', '1529d8c6a1b048bd1aa28cd4c9fb0e2b1923009bc2bbae36bfb8c169d6809a50', '687549', 0, '2025-10-31 14:26:28', '2025-10-31 14:56:28', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pending_orders`
--

CREATE TABLE `pending_orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `cart_json` longtext DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `paymongo_ref` varchar(255) DEFAULT NULL,
  `paymongo_payment_ref` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pending_orders`
--

INSERT INTO `pending_orders` (`id`, `customer_name`, `email`, `address`, `payment_method`, `total`, `cart_json`, `user_id`, `paymongo_ref`, `paymongo_payment_ref`, `created_at`) VALUES
(13, 'wasdw', 'ronanaleckgatmaitan@gmail.com', 'wasdw', 'gcash', 299.00, '[{\"id\":3,\"name\":\"English Lavender Car Diffuser\",\"price\":\"299.00\",\"quantity\":1,\"image\":\"images\\/featprod3.jpg\"}]', 9, 'src_3Z12k5zPs7gx6LLgXFj1T2pL', NULL, '2025-10-28 13:37:48'),
(15, 'juan dela cruz', 'juandela@gmail.com', 'assssssdwa', 'gcash', 299.00, '[{\"id\":2,\"name\":\"Fresh Bamboo Car Diffuser\",\"price\":\"299.00\",\"quantity\":1,\"image\":\"images\\/featprod2.jpg\"}]', 1, NULL, NULL, '2025-10-28 18:22:52'),
(16, 'juan dela cruz', 'juandela@gmail.com', 'assssssdwa', 'gcash', 299.00, '[{\"id\":2,\"name\":\"Fresh Bamboo Car Diffuser\",\"price\":\"299.00\",\"quantity\":1,\"image\":\"images\\/featprod2.jpg\"}]', 1, NULL, NULL, '2025-10-28 18:22:57'),
(17, 'juan cruz dela', 'juandela@gmail.com', 'caloocan, ncr', 'gcash', 598.00, '[{\"id\":3,\"name\":\"English Lavender Car Diffuser\",\"price\":\"299.00\",\"quantity\":2,\"image\":\"images\\/featprod3.jpg\"}]', 1, 'src_UxBazbHjs3UrC7qNf827Xsy3', NULL, '2025-10-29 08:49:30'),
(18, 'ac pogi', 'dumpacri@gmail.com', 'tondo caloocan', 'gcash', 299.00, '[{\"id\":2,\"name\":\"Fresh Bamboo Car Diffuser\",\"price\":\"299.00\",\"quantity\":1,\"image\":\"images\\/featprod2.jpg\"}]', 8, 'src_ToAdAyD6gMPar7LHXsE34pFK', NULL, '2025-10-31 07:41:50');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT 'default.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `stock` int(11) NOT NULL DEFAULT 0,
  `popularity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `brand_id`, `name`, `description`, `price`, `image`, `created_at`, `featured`, `stock`, `popularity`) VALUES
(1, NULL, NULL, 'Fouger Marine Car Diffuser', 'A refreshing blend inspired by ocean breezes and cool coastal air.', 299.00, 'images/featprod1.jpg', '2025-09-22 07:27:39', 1, 88, 0),
(2, NULL, NULL, 'Fresh Bamboo Car Diffuser', 'Light, green, and effortlessly calming. Fresh Bamboo captures the essence of nature with its crisp, airy scent.', 299.00, 'images/featprod2.jpg', '2025-09-22 07:27:39', 1, 87, 0),
(3, 2, NULL, 'English Lavender Car Diffuser', 'Timeless, soothing, and elegant. English Lavender fills your car with a gentle floral aroma that eases stress and promotes relaxation on even journey', 299.00, 'images/featprod3.jpg', '2025-09-22 07:27:39', 1, 89, 1),
(4, NULL, NULL, 'Skyfall Lavender Car Diffuser', 'A sophisticated blend of airy freshness and subtle warmth.', 299.00, 'images/skyfall.jpg', '2025-09-22 15:26:26', 0, 0, 0),
(5, NULL, NULL, 'Sweet Cherry Car Diffuser', 'Playful, juicy, and irresistibly sweet. Sweet Cherry fills your car with a burst of fruity freshness.', 299.00, 'images/scherry.jpg', '2025-09-22 15:26:26', 0, 4, 0),
(6, NULL, NULL, 'Coffee Cup Car Diffuser', 'Rich, warm, and comforting—just like your favorite brew. Coffee Cup surrounds your car with the inviting aroma of freshly brewed coffee.', 299.00, 'images/ccup.jpg', '2025-09-22 15:26:26', 0, 50, 0),
(7, NULL, NULL, 'Fresh Bamboo Helmet Deodorizer', 'Crisp, green, and naturally refreshing. Fresh Bamboo is designed to keep your helmet smelling clean and cool, even after long rides.', 199.00, 'images/fbhelmet.jpg', '2025-09-22 15:26:26', 0, 7, 0),
(8, NULL, NULL, 'English Lavender Helmet Deodorizer', 'Soothing, floral, and timeless. English Lavender gently refreshes your helmet with a calming aroma that eases stress and neutralizes unwanted odors.', 199.00, 'images/elhelmet.jpg', '2025-09-22 15:26:26', 1, 97, 0),
(10, NULL, NULL, 'Sukuna Scent', 'Sukuna', 99.00, 'images/1761127592_Screenshot 2024-05-01 132819.png', '2025-10-22 10:06:32', 0, 96, 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_ratings`
--

CREATE TABLE `product_ratings` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `average_rating` decimal(3,2) DEFAULT 0.00,
  `review_count` int(11) DEFAULT 0,
  `total_rating_value` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_ratings`
--

INSERT INTO `product_ratings` (`id`, `product_id`, `average_rating`, `review_count`, `total_rating_value`, `updated_at`) VALUES
(1, 3, 5.00, 1, 5, '2025-10-28 13:03:39');

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `title` varchar(100) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `helpful_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `product_id`, `user_id`, `rating`, `title`, `comment`, `helpful_count`, `created_at`, `updated_at`) VALUES
(2, 1, 5, 5, '0', 'Apparently this smells great! and apparently it is cheap. Apparently this is recommended', 0, '2025-10-28 07:21:14', '2025-10-28 07:21:14'),
(3, 2, 5, 5, '0', 'Helps you relax ggs 1234556778', 0, '2025-10-28 12:26:59', '2025-10-28 12:26:59'),
(4, 3, 9, 5, '0', 'aswdwdaadsdwdasgrgrsdgf', 0, '2025-10-28 13:03:39', '2025-10-28 13:03:39');

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`id`, `order_id`, `email`, `reason`, `status`, `created_at`) VALUES
(1, 5, 'johnlennon@yoohoo.com', 'wddw', 'pending', '2025-10-28 07:59:07'),
(2, 23, 'johnlennon@yoohoo.com', 'wdw', 'pending', '2025-10-28 07:59:30');

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscribers`
--

INSERT INTO `subscribers` (`id`, `email`, `created_at`) VALUES
(1, 'juandela@gmail.com', '2025-09-23 06:25:15'),
(20, 'jinjuju@gmail.com', '2025-09-23 13:02:01'),
(21, 'gojosatoru@gmail.com', '2025-10-26 15:03:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `PASSWORD` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `PASSWORD`, `created_at`) VALUES
(5, 'SoloMumbling6', 'johnlennon@yoohoo.com', '$2y$10$zJeJbL5ztsmS8MIND/gv1.Mb6n9PUhk1NPsvBG0gkysjsCJBzBadu', '2025-10-26 06:57:46'),
(8, 'acridump', 'dumpacri@gmail.com', '$2y$10$0lKchrVKXSiQ9ljbujbSOOqRBH.UjvH/bdPdHgG86/Tnp0hkIIGsG', '2025-10-26 18:14:12'),
(9, 'S-Normal', 'ronanaleckgatmaitan@gmail.com', '$2y$10$uERmQ9MvbM7NOF0OF.zdyO5UJSwQTPi2dgnxBIl5fpCFjSCSOrQlG', '2025-10-28 12:44:06'),
(10, 'albertodev', 'magnoalberto.bscs@gmail.com', '$2y$10$MstY5Fv3kIaCD4cV9xP4gujlpvR8iWl34J9rX7wSvl.s2qpvTvM4S', '2025-10-31 16:26:43');

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_logs`
--

CREATE TABLE `user_activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(60) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_activity_logs`
--

INSERT INTO `user_activity_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 5, 'logout', 'User signed out.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-28 12:34:35'),
(2, 5, 'login', 'Two-factor login verified.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-28 12:34:52'),
(3, 5, 'logout', 'User signed out.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-28 12:43:09'),
(4, 9, 'account_created', 'Email verified and account created.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-28 12:44:06'),
(5, 9, 'login', 'Automatic login after registration.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-28 12:44:06'),
(6, 9, 'order_pending_payment', 'Initiated GCash payment for pending order #6 (₱299.00).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-28 12:44:21'),
(7, 9, 'order_pending_payment', 'Initiated GCash payment for pending order #7 (₱299.00).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-28 12:44:41'),
(8, 9, 'logout', 'User signed out.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-28 12:47:10'),
(9, 5, 'login', 'Two-factor login verified.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-28 12:47:27'),
(10, 5, 'logout', 'User signed out.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-28 12:49:33'),
(11, 9, 'login', 'Two-factor login verified.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-28 12:49:54'),
(12, 9, 'order_pending_payment', 'Initiated GCash payment for pending order #8 (₱199.00).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-28 12:53:27'),
(13, 9, 'order_pending_payment', 'Initiated GCash payment for pending order #9 (₱199.00).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-28 12:58:11'),
(14, 9, 'order_pending_payment', 'Initiated GCash payment for pending order #10 (₱299.00).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-28 12:58:57'),
(15, 9, 'order_pending_payment', 'Initiated GCash payment for pending order #11 (₱199.00).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-28 13:02:48'),
(16, 9, 'order_pending_payment', 'Initiated GCash payment for pending order #12 (₱199.00).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-28 13:10:07'),
(17, 9, 'order_pending_payment', 'Initiated GCash payment for pending order #13 (₱299.00).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-28 13:37:48'),
(18, 9, 'order_pending_payment', 'Initiated GCash payment for pending order #14 (₱299.00).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0', '2025-10-28 13:37:49'),
(46, 8, 'profile_updated', 'Password reset via secure link.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 19:03:57'),
(47, 8, 'profile_updated', 'Password reset via secure link.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 19:07:44'),
(48, 8, 'login', 'Two-factor login verified.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 19:13:59'),
(49, 8, 'logout', 'User signed out.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 19:19:06'),
(50, 8, 'profile_updated', 'Password reset via secure link.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 20:53:10'),
(51, 8, 'login', 'Two-factor login verified.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 21:09:13'),
(52, 8, 'logout', 'User signed out.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 21:09:47'),
(53, 8, 'profile_updated', 'Password reset via secure link.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-30 21:15:41'),
(54, 8, 'login', 'Two-factor login verified.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:09:58'),
(55, 8, 'order_pending_payment', 'Initiated GCash payment for pending order #18 (₱299.00).', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:41:50'),
(56, 8, 'logout', 'User signed out.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:42:53'),
(57, 8, 'profile_updated', 'Password reset via secure link.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 07:44:18'),
(58, 8, 'login', 'Two-factor login verified.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 13:05:20'),
(59, 8, 'logout', 'User signed out.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 13:23:53'),
(60, 8, 'profile_updated', 'Password reset via secure link.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 13:26:59'),
(61, 8, 'login', 'Two-factor login verified.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 13:27:31'),
(62, 8, 'logout', 'User signed out.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 13:58:03'),
(63, 8, 'profile_updated', 'Password reset via secure link.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 13:59:22'),
(64, 8, 'profile_updated', 'Password reset via secure link.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 14:27:10'),
(65, 8, 'login', 'Two-factor login verified.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 14:28:21'),
(66, 8, 'order_placed', 'Order #42 placed via Cod for ₱299.00.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 16:16:55'),
(67, 8, 'logout', 'User signed out.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 16:18:45'),
(68, 10, 'account_created', 'Email verified and account created.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 16:26:43'),
(69, 10, 'login', 'Automatic login after registration.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 16:26:43'),
(70, 10, 'logout', 'User signed out.', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 Edg/141.0.0.0', '2025-10-31 16:27:35');

-- --------------------------------------------------------

--
-- Table structure for table `user_otp_verifications`
--

CREATE TABLE `user_otp_verifications` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `attempts` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT (current_timestamp() + interval 10 minute)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_username` (`admin_username`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `compare_products`
--
ALTER TABLE `compare_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `compare_unique` (`user_id`,`product_id`,`session_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `login_otp_verifications`
--
ALTER TABLE `login_otp_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_cart_backups`
--
ALTER TABLE `order_cart_backups`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reset_token` (`reset_token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Indexes for table `pending_orders`
--
ALTER TABLE `pending_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `brand_id` (`brand_id`);

--
-- Indexes for table `product_ratings`
--
ALTER TABLE `product_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_unique` (`product_id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_2` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_created_at` (`user_id`,`created_at`);

--
-- Indexes for table `user_otp_verifications`
--
ALTER TABLE `user_otp_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_activity_logs`
--
ALTER TABLE `admin_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `compare_products`
--
ALTER TABLE `compare_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `login_otp_verifications`
--
ALTER TABLE `login_otp_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `pending_orders`
--
ALTER TABLE `pending_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `product_ratings`
--
ALTER TABLE `product_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `user_otp_verifications`
--
ALTER TABLE `user_otp_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `compare_products`
--
ALTER TABLE `compare_products`
  ADD CONSTRAINT `compare_products_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_ratings`
--
ALTER TABLE `product_ratings`
  ADD CONSTRAINT `product_ratings_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD CONSTRAINT `fk_user_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
