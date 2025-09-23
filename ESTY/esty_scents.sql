-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 23, 2025 at 08:26 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`) VALUES
(1, 'admin', '5e9d11a14ad1c8dd77e98ef9b53fd1ba');

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
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `email`, `address`, `payment_method`, `total`, `created_at`, `status`) VALUES
(1, 'ALBERTO CONSUELO RILI', 'mannypacquiao@gmail.com', '74 GENCON', 'cod', 299.00, '2025-09-22 17:34:15', 'pending'),
(2, 'ALBERT ALCANTARA RILI', 'mannypacquiao@gmail.com', '74 GENCON', 'cod', 299.00, '2025-09-22 17:35:58', 'processing'),
(3, 'ALBERT ALCANTARA RILI', 'mannypacquiao@gmail.com', '7831 TOKYO JAPAN, TONDO', 'cod', 299.00, '2025-09-22 17:37:50', 'pending'),
(4, 'ALBERT ALCANTARA RILI', 'mannypacquiao@gmail.com', '7831 TOKYO JAPAN, TONDO', 'gcash', 598.00, '2025-09-22 20:16:26', 'completed'),
(5, 'juan dela cruz', 'juandela@gmail.com', 'BGC Tondo, Cavite', 'cod', 299.00, '2025-09-22 20:39:01', 'pending');

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
(5, 5, 'Fouger Marine Car Diffuser', 299.00, 1, 299.00);

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
  `featured` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image`, `created_at`, `featured`) VALUES
(1, 'Fouger Marine Car Diffuser', 'A refreshing blend inspired by ocean breezes and cool coastal air.', 299.00, 'images/featprod1.jpg', '2025-09-22 07:27:39', 1),
(2, 'Fresh Bamboo Car Diffuser', 'Light, green, and effortlessly calming. Fresh Bamboo captures the essence of nature with its crisp, airy scent.', 299.00, 'images/featprod2.jpg', '2025-09-22 07:27:39', 1),
(3, 'English Lavender Car Diffuser', 'Timeless, soothing, and elegant. English Lavender fills your car with a gentle floral aroma that eases stress and promotes relaxation on even journey', 299.00, 'images/featprod3.jpg', '2025-09-22 07:27:39', 1),
(4, 'Skyfall Lavender Car Diffuser', 'A sophisticated blend of airy freshness and subtle warmth.', 299.00, 'images/skyfall.jpg', '2025-09-22 15:26:26', 0),
(5, 'Sweet Cherry Car Diffuser', 'Playful, juicy, and irresistibly sweet. Sweet Cherry fills your car with a burst of fruity freshness.', 299.00, 'images/scherry.jpg', '2025-09-22 15:26:26', 0),
(6, 'Coffee Cup Car Diffuser', 'Rich, warm, and comforting—just like your favorite brew. Coffee Cup surrounds your car with the inviting aroma of freshly brewed coffee.', 299.00, 'images/ccup.jpg', '2025-09-22 15:26:26', 0),
(7, 'Fresh Bamboo Helmet Deodorizer', 'Crisp, green, and naturally refreshing. Fresh Bamboo is designed to keep your helmet smelling clean and cool, even after long rides.', 199.00, 'images/fbhelmet.jpg', '2025-09-22 15:26:26', 0),
(8, 'English Lavender Helmet Deodorizer', 'Soothing, floral, and timeless. English Lavender gently refreshes your helmet with a calming aroma that eases stress and neutralizes unwanted odors.', 199.00, 'images/elhelmet.jpg', '2025-09-22 15:26:26', 0);

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
(1, 'juandela@gmail.com', '2025-09-23 06:25:15');

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
(1, 'juandela', 'juandela@gmail.com', '$2y$10$yiD/xKLuky/QcDrvS3oIkuwxCftsiW45Qb5x9sdE.8z4ml5b7yh1i', '2025-09-23 06:19:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

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
  ADD UNIQUE KEY `email` (`email`);

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
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
