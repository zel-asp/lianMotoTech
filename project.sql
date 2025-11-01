-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Nov 01, 2025 at 07:37 PM
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
-- Database: `project1`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `full_name` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `preferred_date` date NOT NULL,
  `preferred_time` time NOT NULL,
  `service_type` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `full_name`, `phone_number`, `email`, `preferred_date`, `preferred_time`, `service_type`, `created_at`, `status`) VALUES
(1, 4, 'hello admin', '09123456789', 'admin1@gmail.com', '2025-11-06', '05:34:00', 'repair', '2025-11-01 18:31:58', 'Confirm');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_cost` decimal(10,2) NOT NULL,
  `order_status` varchar(50) DEFAULT 'On hold',
  `user_id` int(11) NOT NULL,
  `user_phone` varchar(20) NOT NULL,
  `user_city` varchar(100) NOT NULL,
  `user_address` varchar(255) NOT NULL,
  `product_note` text DEFAULT NULL,
  `order_date` datetime NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(150) NOT NULL,
  `reference` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` varchar(255) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_image` varchar(255) DEFAULT 'default.jpg',
  `product_price` decimal(12,2) NOT NULL,
  `product_quantity` int(11) DEFAULT 1,
  `user_id` int(11) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_description` text DEFAULT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_stock` int(11) NOT NULL DEFAULT 0,
  `product_image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_name`, `product_description`, `product_price`, `product_stock`, `product_image`) VALUES
(1, 'Motor Part 1', 'High-quality motor part for performance and durability.', 1500.00, 10, 'product1.jpg'),
(2, 'Motor Part 2', 'Reliable motor part for everyday use.', 1200.00, 13, 'product2.jpg'),
(3, 'Motor Part 3', 'Durable replacement part with premium build.', 1800.00, 7, 'product3.jpg'),
(4, 'Motor Part 4', 'Essential motor accessory for smooth rides.', 950.00, 20, 'product4.jpg'),
(5, 'Motor Part 5', 'Performance upgrade part for speed enthusiasts.', 2100.00, 5, 'product5.jpg'),
(6, 'Motor Part 6', 'Affordable and reliable motor part for commuters.', 800.00, 12, 'product6.jpg'),
(7, 'Motor Part 7', 'Sturdy motor component with long lifespan.', 1700.00, 9, 'product7.jpg'),
(8, 'Motor Part 8', 'Premium quality part with excellent fit.', 2000.00, 7, 'product8.jpg'),
(11, 'Marco', '', 55.00, 0, 'logo.jpg.png');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `user_rating` varchar(10) NOT NULL,
  `user_review` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `user_name`, `product_name`, `user_rating`, `user_review`, `created_at`) VALUES
(2, 4, 'just me', 'Motor Part 2', '4', 'hello', '2025-11-01 18:29:44');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_fee`
--

CREATE TABLE `shipping_fee` (
  `fee_id` int(11) NOT NULL,
  `fee` decimal(10,2) NOT NULL,
  `gcash_name` varchar(100) NOT NULL,
  `gcash_number` varchar(20) NOT NULL,
  `method` varchar(100) DEFAULT 'Standard',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_fee`
--

INSERT INTO `shipping_fee` (`fee_id`, `fee`, `gcash_name`, `gcash_number`, `method`, `created_at`) VALUES
(1, 100.00, 'Juan Dela Cruz', '09171234567', 'Standard', '2025-08-21 07:53:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(150) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `user_email`, `user_password`, `created_at`) VALUES
(1, 'admin', 'admin1@gmail.com', '$2y$10$KU9oukgwGbwnrVzGER1zr.bGAAufc/URC/M0QA3GYN/9IcMaQxRS.', '2025-11-01 18:25:08'),
(4, 'Janzel Dolo', 'user1@gmail.com', '$2y$10$B9m.sMdzEY4BsECmEyDtWeg3FEC0PKxYRg84.x1Tlo0HaN8QgJAo2', '2025-11-01 18:29:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shipping_fee`
--
ALTER TABLE `shipping_fee`
  ADD PRIMARY KEY (`fee_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_email` (`user_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `shipping_fee`
--
ALTER TABLE `shipping_fee`
  MODIFY `fee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
