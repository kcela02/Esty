-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2025 at 12:38 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.1.12

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
(3, 'admin', 'Login', NULL, '::1', '2025-10-26 10:37:48');

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
(5, 5, 3, 1, '2025-10-26 09:23:37'),
(6, 5, 10, 2, '2025-10-26 09:39:42'),
(7, 5, 6, 1, '2025-10-26 10:09:46');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `email`, `address`, `payment_method`, `total`, `created_at`, `status`, `completed_at`) VALUES
(1, 'ALBERTO CONSUELO RILI', 'mannypacquiao@gmail.com', '74 GENCON', 'cod', '299.00', '2025-09-22 17:34:15', 'completed', '2025-10-23 17:57:50'),
(2, 'ALBERT ALCANTARA RILI', 'mannypacquiao@gmail.com', '74 GENCON', 'cod', '299.00', '2025-09-22 17:35:58', 'completed', '2025-10-23 17:57:48'),
(3, 'ALBERT ALCANTARA RILI', 'mannypacquiao@gmail.com', '7831 TOKYO JAPAN, TONDO', 'cod', '299.00', '2025-09-22 17:37:50', 'completed', '2025-10-23 17:57:46'),
(4, 'ALBERT ALCANTARA RILI', 'mannypacquiao@gmail.com', '7831 TOKYO JAPAN, TONDO', 'gcash', '598.00', '2025-09-22 20:16:26', 'completed', '2025-10-23 17:57:44'),
(5, 'juan dela cruz', 'juandela@gmail.com', 'BGC Tondo, Cavite', 'cod', '299.00', '2025-09-22 20:39:01', 'completed', '2025-10-23 17:57:42'),
(6, 'Alice Santos', NULL, NULL, NULL, '1200.00', '2024-09-02 02:00:00', 'completed', '2024-09-02 10:00:00'),
(7, 'Mark Dela Cruz', NULL, NULL, NULL, '1500.00', '2024-09-10 06:30:00', 'processing', NULL),
(8, 'Jenna Reyes', NULL, NULL, NULL, '950.00', '2024-09-15 03:10:00', 'completed', NULL),
(9, 'Luis Torres', NULL, NULL, NULL, '1800.00', '2024-09-20 01:00:00', 'completed', '2024-09-20 09:00:00'),
(10, 'Cindy Flores', NULL, NULL, NULL, '2000.00', '2024-09-25 07:00:00', 'completed', '2024-09-26 08:30:00'),
(11, 'SincerelyYours', 'jinjuju@gmail.com', 'Caloocan City', 'cod', '299.00', '2025-10-23 08:40:17', 'processing', NULL),
(12, 'Rama', 'jinjuju@gmail.com', 'Egypt', 'cod', '20930.00', '2025-10-23 10:14:06', 'completed', NULL),
(13, 'social', 'jinjuju@gmail.com', 'Albania', 'gcash', '2490.00', '2025-10-23 10:15:12', 'pending', '2025-10-26 15:49:32'),
(14, 'John Lennon', 'johnlennon@yoohoo.com', 'Penny Lane', 'gcash', '6279.00', '2025-10-26 09:24:07', 'completed', NULL);

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
(1, 1, 'Fresh Bamboo Car Diffuser', '299.00', 1, '299.00'),
(2, 2, 'Fouger Marine Car Diffuser', '299.00', 1, '299.00'),
(3, 3, 'Fouger Marine Car Diffuser', '299.00', 1, '299.00'),
(4, 4, 'English Lavender Car Diffuser', '299.00', 2, '598.00'),
(5, 5, 'Fouger Marine Car Diffuser', '299.00', 1, '299.00'),
(6, 11, 'Fouger Marine Car Diffuser', '299.00', 1, '299.00'),
(7, 12, 'Fouger Marine Car Diffuser', '299.00', 70, '20930.00'),
(8, 13, 'English Lavender Car Diffuser', '299.00', 1, '299.00'),
(9, 13, 'Fresh Bamboo Car Diffuser', '299.00', 1, '299.00'),
(10, 13, 'English Lavender Helmet Deodorizer', '199.00', 2, '398.00'),
(11, 13, 'Fouger Marine Car Diffuser', '299.00', 1, '299.00'),
(12, 13, 'Coffee Cup Car Diffuser', '299.00', 1, '299.00'),
(13, 13, 'Fresh Bamboo Helmet Deodorizer', '199.00', 1, '199.00'),
(14, 13, 'Sukuna Scent', '99.00', 1, '99.00'),
(15, 13, 'Sweet Cherry Car Diffuser', '299.00', 1, '299.00'),
(16, 13, 'Skyfall Lavender Car Diffuser', '299.00', 1, '299.00'),
(17, 14, 'English Lavender Car Diffuser', '299.00', 21, '6279.00');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT 'default.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `created_at`, `featured`, `stock`) VALUES
(1, 'Fouger Marine Car Diffuser', 'A refreshing blend inspired by ocean breezes and cool coastal air.', '299.00', 'images/featprod1.jpg', '2025-09-22 07:27:39', 1, 0),
(2, 'Fresh Bamboo Car Diffuser', 'Light, green, and effortlessly calming. Fresh Bamboo captures the essence of nature with its crisp, airy scent.', '299.00', 'images/featprod2.jpg', '2025-09-22 07:27:39', 1, 0),
(3, 'English Lavender Car Diffuser', 'Timeless, soothing, and elegant. English Lavender fills your car with a gentle floral aroma that eases stress and promotes relaxation on even journey', '299.00', 'images/featprod3.jpg', '2025-09-22 07:27:39', 1, 0),
(4, 'Skyfall Lavender Car Diffuser', 'A sophisticated blend of airy freshness and subtle warmth.', '299.00', 'images/skyfall.jpg', '2025-09-22 15:26:26', 0, 1),
(5, 'Sweet Cherry Car Diffuser', 'Playful, juicy, and irresistibly sweet. Sweet Cherry fills your car with a burst of fruity freshness.', '299.00', 'images/scherry.jpg', '2025-09-22 15:26:26', 0, 5),
(6, 'Coffee Cup Car Diffuser', 'Rich, warm, and comforting—just like your favorite brew. Coffee Cup surrounds your car with the inviting aroma of freshly brewed coffee.', '299.00', 'images/ccup.jpg', '2025-09-22 15:26:26', 0, 50),
(7, 'Fresh Bamboo Helmet Deodorizer', 'Crisp, green, and naturally refreshing. Fresh Bamboo is designed to keep your helmet smelling clean and cool, even after long rides.', '199.00', 'images/fbhelmet.jpg', '2025-09-22 15:26:26', 0, 0),
(8, 'English Lavender Helmet Deodorizer', 'Soothing, floral, and timeless. English Lavender gently refreshes your helmet with a calming aroma that eases stress and neutralizes unwanted odors.', '199.00', 'images/elhelmet.jpg', '2025-09-22 15:26:26', 1, 0),
(10, 'Sukuna Scent', 'Sukuna', '99.00', 'images/1761127592_Screenshot 2024-05-01 132819.png', '2025-10-22 10:06:32', 0, 5);

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
(20, 'jinjuju@gmail.com', '2025-09-23 13:02:01');

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
(1, 'juandela', 'juandela@gmail.com', '$2y$10$yiD/xKLuky/QcDrvS3oIkuwxCftsiW45Qb5x9sdE.8z4ml5b7yh1i', '2025-09-23 06:19:03'),
(4, 'SoloMumbling4', 'jinjuju@gmail.com', '$2y$10$XW8zr9on99edGL8nZSJEw.VTtH2pXDR5BWY1hTVlc0gtG0V/fBH7m', '2025-10-19 12:57:05'),
(5, 'SoloMumbling6', 'johnlennon@yoohoo.com', '$2y$10$zJeJbL5ztsmS8MIND/gv1.Mb6n9PUhk1NPsvBG0gkysjsCJBzBadu', '2025-10-26 06:57:46');

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
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
