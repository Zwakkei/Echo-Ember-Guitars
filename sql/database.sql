-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 09, 2026 at 04:58 PM
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
-- Database: `echo_ember_guitars`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_amount`, `status`, `shipping_address`, `payment_method`) VALUES
(1, 3, '2026-03-08 12:00:29', 149.99, 'pending', 'California, ChinaTown Nihao', 'Cash on Delivery'),
(2, 4, '2026-03-09 11:25:00', 149.99, 'pending', 'Basak', 'GCash');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `order_detail_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_time` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`order_detail_id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES
(1, 1, 6, 1, 149.99),
(2, 2, 6, 1, 149.99);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `category` varchar(50) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `description`, `price`, `stock`, `category`, `image_path`, `created_at`) VALUES
(1, 'Echo Vintage Acoustic', 'Classic acoustic guitar with rich, warm tone. Perfect for beginners and professionals.', 299.99, 10, 'Acoustic', 'uploads/products/acoustic1.jpg', '2026-03-08 10:52:20'),
(2, 'Ember Electric Pro', 'High-performance electric guitar with dual humbuckers and flamed maple top.', 599.99, 5, 'Electric', 'uploads/products/electric1.jpg', '2026-03-08 10:52:20'),
(3, 'Classical Nylon String', 'Traditional classical guitar with nylon strings, ideal for fingerstyle playing.', 249.99, 8, 'Classical', 'uploads/products/classical1.jpg', '2026-03-08 10:52:20'),
(4, 'Bass Guitar 4-String', 'Professional bass guitar with active electronics for deep, punchy sound.', 399.99, 6, 'Bass', 'uploads/products/bass1.jpg', '2026-03-08 10:52:20'),
(5, 'Acoustic-Electric Cutaway', 'Versatile acoustic-electric guitar with built-in tuner and pickup system.', 449.99, 7, 'Acoustic', 'uploads/products/acoustic2.jpg', '2026-03-08 10:52:20'),
(6, 'Fender Mustang LT25', '25-watt digital amplifier with 20 presets, Bluetooth connectivity, and USB recording.', 149.99, 6, 'Amplifier', 'uploads/products/amp1.jpg', '2026-03-08 11:46:42'),
(7, 'Boss Katana 50', '50-watt 1x12\" combo amplifier with 5 unique amp characters and power control.', 229.99, 5, 'Amplifier', 'uploads/products/amp2.jpg', '2026-03-08 11:46:42'),
(8, 'Marshall Code 25', '25-watt digital combo amp with 14 preamps, 4 power amps, and 8 speaker emulations.', 199.99, 6, 'Amplifier', 'uploads/products/amp3.jpg', '2026-03-08 11:46:42'),
(9, 'Boss DS-1 Distortion', 'Classic distortion pedal with tone control, perfect for rock and metal.', 49.99, 15, 'Pedal', 'uploads/products/pedal1.jpg', '2026-03-08 11:46:42'),
(10, 'Tube Screamer TS9', 'Iconic overdrive pedal with warm, tube-like overdrive character.', 99.99, 12, 'Pedal', 'uploads/products/pedal2.jpg', '2026-03-08 11:46:42'),
(11, 'Wah Wah Pedal', 'Cry Baby classic wah pedal with fasel inductor for rich tone.', 79.99, 10, 'Pedal', 'uploads/products/pedal3.jpg', '2026-03-08 11:46:42'),
(12, 'Guitar Repair Kit', 'Complete 15-piece kit including wrenches, string cutters, and polish cloth.', 29.99, 20, 'Tool Kit', 'uploads/products/tool1.jpg', '2026-03-08 11:46:42'),
(13, 'String Winder & Cutter', '3-in-1 tool: string winder, peg winder, and wire cutter.', 12.99, 30, 'Tool Kit', 'uploads/products/tool2.jpg', '2026-03-08 11:46:42'),
(14, 'Fret Polishing Kit', 'Includes fret protectors, polishing compound, and micro-mesh pads.', 24.99, 15, 'Tool Kit', 'uploads/products/tool3.jpg', '2026-03-08 11:46:42'),
(15, 'Snark SN-8 Tuner', 'Super-tight clip-on tuner with multi-axis swivel and high-definition display.', 19.99, 25, 'Tuner', 'uploads/products/tuner1.jpg', '2026-03-08 11:46:42'),
(16, 'Boss TU-3 Tuner', 'Stage-ready chromatic tuner with 21-segment LED meter and high-brightness mode.', 89.99, 8, 'Tuner', 'uploads/products/tuner2.jpg', '2026-03-08 11:46:42'),
(17, 'TC Electronic Polytune', 'Polyphonic clip-on tuner that tunes all strings at once.', 39.99, 12, 'Tuner', 'uploads/products/tuner3.jpg', '2026-03-08 11:46:42'),
(18, 'Guitar Pick Sampler', 'Assorted pack of 12 picks: various thicknesses and materials.', 5.99, 50, 'Accessory', 'uploads/products/pick1.jpg', '2026-03-08 11:46:42'),
(19, 'Jazz III Picks (6-pack)', 'Premium nylon picks with sharp tip for precision playing.', 7.99, 40, 'Accessory', 'uploads/products/pick2.jpg', '2026-03-08 11:46:42'),
(20, 'Guitar Strap - Leather', 'Genuine leather strap with padded shoulder support.', 24.99, 18, 'Accessory', 'uploads/products/strap1.jpg', '2026-03-08 11:46:42'),
(21, 'Instrument Cable 10ft', 'High-quality shielded cable with gold-plated connectors.', 14.99, 22, 'Accessory', 'uploads/products/cable1.jpg', '2026-03-08 11:46:42'),
(22, 'Guitar Stand', 'Folding A-frame stand suitable for electric and acoustic guitars.', 19.99, 15, 'Accessory', 'uploads/products/stand1.jpg', '2026-03-08 11:46:42'),
(23, 'Gig Bag', 'Padded nylon gig bag with accessory pocket and backpack straps.', 34.99, 10, 'Accessory', 'uploads/products/bag1.jpg', '2026-03-08 11:46:42'),
(24, 'Elixir String', 'Elixir strings are premium, coated guitar strings known for exceptional longevity, consistent tone, and a smooth, comfortable feel. Utilizing proprietary Nanoweb (bright) or Polyweb (warm) coatings, they protect the entire string—including between the windings—from gunk, sweat, and corrosion, lasting 3–5 times longer than traditional strings.', 499.00, 20, 'Electric', 'uploads/products/69aec2bc4581c.jpg', '2026-03-09 12:53:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `full_name`, `address`, `phone`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@echoember.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', NULL, NULL, 'admin', '2026-03-08 10:52:20'),
(2, 'john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', NULL, NULL, 'customer', '2026-03-08 10:52:20'),
(3, 'Abjatu44', 'abduljakultubol@gmail.com', '$2y$10$BQ0gAKB0wi3Nfxap0ObWSeOQZ5rLc2D2EcwwIM2Z2CwbKrfYPBLly', 'Abdul Jakul Tubol', 'California, ChinaTown Nihao', '01234567891', 'customer', '2026-03-08 11:34:05'),
(4, 'papicholo', 'papicholo@samano.com', '$2y$10$TINifsizoy1qxCcpArS1HuM.HYiWBTohGJ3jGq0G0AS2fSGJx.KGa', 'cholo samano', 'Basak', '01234567892', 'customer', '2026-03-09 11:24:26');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
