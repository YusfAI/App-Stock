-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 30, 2025 at 04:35 AM
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
-- Database: `el_buteur`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `size` varchar(20) DEFAULT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `date_added` date DEFAULT curdate(),
  `quantity` int(11) DEFAULT 0,
  `buy_price` decimal(10,2) NOT NULL,
  `sell_price` decimal(10,2) NOT NULL,
  `profit_per_unit` decimal(10,2) GENERATED ALWAYS AS (`sell_price` - `buy_price`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category`, `name`, `size`, `supplier`, `date_added`, `quantity`, `buy_price`, `sell_price`) VALUES
(13, '00000000', '0000000', '000000000', '000000', '2025-08-29', 0, 0.00, 0.00),
(14, 'Crampon Nike ', 'Nike Air Zoom Rose/Noir', '40', 'Crampon tunisie', '2025-08-29', 1, 175.00, 220.00),
(15, 'Crampon Nike ', 'Nike Air Zoom Mauve', '40', 'Crampon tunisie', '2025-08-29', 1, 175.00, 220.00),
(16, 'Crampon Nike ', 'Nike Air Zoom Jaune fluo/Noir', '40', 'Crampon tunisie', '2025-08-29', 1, 175.00, 220.00),
(17, 'Crampon Nike ', 'Nike mauve mbappe', '41', 'Crampon tunisie', '2025-08-29', 1, 175.00, 220.00),
(18, 'Crampon Nike ', 'Nike Air Zoom Jaune Fluo / Noir', '41', 'Crampon tunisie', '2025-08-29', 1, 175.00, 220.00),
(19, 'Crampon Adidas', 'Adidas CrazyFast Rouge/Blanc', '41 1/3', 'Crampon tunisie', '2025-08-29', 1, 175.00, 220.00),
(20, 'Crampon Nike ', 'Nike Mauve Mbappe', '42', 'Crampon tunisie', '2025-08-29', 1, 175.00, 220.00),
(21, 'Kit 24/25', 'Real Gris 24/25', 'S', 'Crampon tunisie', '2025-08-29', 1, 35.00, 60.00),
(22, 'Kit 24/25', 'Ajax 24/25', 'S', 'Crampon tunisie', '2025-08-29', 1, 35.00, 60.00),
(23, 'Kit 25/26', 'Barca 25/26', 'S', 'Crampon tunisie', '2025-08-29', 1, 38.00, 65.00),
(24, 'Kit 25/26', 'psg 25/26', 'S', 'Crampon tunisie', '2025-08-29', 1, 38.00, 60.00),
(25, 'Kit 25/26', 'Real Madrid 25/26', 'S', 'Crampon tunisie', '2025-08-29', 1, 38.00, 60.00),
(26, 'Kit 24/25', 'Inter Jaune 24/25', 'S', 'Crampon tunisie', '2025-08-29', 1, 35.00, 60.00),
(27, 'Kit 24/25', 'Tunisie Rouge 24/25', 'S', 'Crampon tunisie', '2025-08-29', 1, 38.00, 60.00),
(28, 'Kit 25/26', 'PSG 25/26', 'M', 'Crampon tunisie', '2025-08-30', 1, 38.00, 60.00),
(29, 'Kit 25/26', 'Real Madrid 25/26', 'M', 'Crampon tunisie', '2025-08-30', 1, 38.00, 65.00),
(30, 'Kit 24/25', 'Arsenal 24/25', 'M', 'Crampon tunisie', '2025-08-30', 1, 35.00, 60.00),
(31, 'Kit 24/25', 'Tunisie Bleu', 'M', 'Crampon tunisie', '2025-08-30', 1, 35.00, 60.00),
(32, 'Kit 24/25', 'Real Madrid blanc 24/25', 'L', 'Crampon tunisie', '2025-08-30', 1, 35.00, 60.00),
(33, 'Kit 25/26', 'Real Madrid 25/26', 'L', 'Crampon tunisie', '2025-08-30', 1, 38.00, 65.00),
(34, 'Kit 24/25', 'Chelsea 24/25', 'L', 'Crampon tunisie', '2025-08-30', 1, 35.00, 60.00),
(35, 'Kit 24/25', 'Tunisie Blanc L', 'L', 'Crampon tunisie', '2025-08-30', 1, 38.00, 60.00),
(36, 'Kit 24/25', 'Tunisie Rouge 24/25', 'L', 'Crampon tunisie', '2025-08-30', 1, 38.00, 60.00),
(37, 'Kit 24/25', 'Barca Noir 24/25', 'L', 'Crampon tunisie', '2025-08-30', 1, 40.00, 60.00),
(38, 'Kit 24/25', 'Liverpool 24/25', 'XL', 'Crampon tunisie', '2025-08-30', 1, 35.00, 55.00),
(39, 'Kit 25/26', 'Real Madrid 25/26', 'XL', 'Crampon tunisie', '2025-08-30', 1, 38.00, 65.00),
(40, 'Kit 24/25', 'Real Madrid Blanc 24/25', 'XXL', 'Crampon tunisie', '2025-08-30', 1, 38.00, 60.00),
(41, 'Kit 24/25', 'Barca Noir 24/25', 'M', 'Crampon tunisie', '2025-08-30', 1, 40.00, 60.00);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `customer_firstname` varchar(50) NOT NULL,
  `customer_lastname` varchar(50) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `size` varchar(20) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `buy_price` decimal(10,2) NOT NULL,
  `sell_price` decimal(10,2) NOT NULL,
  `total_profit` decimal(10,2) GENERATED ALWAYS AS ((`sell_price` - `buy_price`) * `quantity`) STORED,
  `sale_date` date DEFAULT curdate(),
  `shipping_method` enum('Aramex','Direct') NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`sale_id`, `customer_firstname`, `customer_lastname`, `phone`, `address`, `product_id`, `product_name`, `size`, `quantity`, `buy_price`, `sell_price`, `sale_date`, `shipping_method`, `notes`) VALUES
(9, 'Rayen', 'Ebdilli', '95 690 207', 'hay tadhamon', 34, 'Chelsea 24/25 (L) - TND 60.00', 'L', 1, 35.00, 60.00, '2025-08-30', 'Aramex', 'impression Ebdilli 10');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id_supp` int(11) NOT NULL,
  `num_fact` varchar(50) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `page` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `date` date DEFAULT curdate(),
  `product` varchar(100) NOT NULL,
  `sizes` varchar(50) DEFAULT NULL,
  `buy_price` decimal(10,2) NOT NULL,
  `payment_method` enum('Cash','Card','Transfer') NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) GENERATED ALWAYS AS (`buy_price` * `quantity`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id_supp`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id_supp` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
