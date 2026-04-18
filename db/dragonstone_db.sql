-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 18, 2026 at 03:00 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dragonstone`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` varchar(10) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `first_name`, `last_name`, `email`, `password`, `created_at`) VALUES
('ADM00001', 'jaydyn', 'Greyling', 'jaydyng12@outlook.com', '$2y$10$2RhR7HfjLdJ8OHECyLtu1uVLpwZ.CV.w8i1ihfY1JqywRlAXEuzh6', '2025-10-30 09:41:18'),
('ADM00002', 'Johannes', 'Greyling', 'johannesgreyling@dragonstone.com', '$2y$10$3QCKpcIJlqwLRyKU8g371OaB5fwNFBmxLCSG8dk3.h8uWx5mJaoTe', '2025-10-31 14:00:07');

-- --------------------------------------------------------

--
-- Table structure for table `community_posts`
--

CREATE TABLE `community_posts` (
  `post_id` varchar(10) NOT NULL,
  `user_id` varchar(10) NOT NULL,
  `post_title` varchar(255) NOT NULL,
  `post_caption` text DEFAULT NULL,
  `post_link` varchar(255) DEFAULT NULL,
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `community_posts`
--

INSERT INTO `community_posts` (`post_id`, `user_id`, `post_title`, `post_caption`, `post_link`, `posted_at`) VALUES
('PST00007', 'USR00001', 'Eco friendly potplants', 'Check out my youtube video on how to recycle old coca-cola bottles to new pots for plants', 'https://youtu.be/zLHGTM4-rEQ?si=x1eDo54ivdlgmE3b', '2025-10-29 10:52:34'),
('PST00008', 'USR00001', 'G5ee', 'Fsw6', '', '2025-11-02 11:29:17'),
('PST00009', 'USR00001', 'They/them', 'Cheesecake slaps', '', '2025-11-05 09:13:08');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `order_ref` varchar(20) NOT NULL,
  `users_id` varchar(10) NOT NULL,
  `status` enum('Pending','Out for Delivery','Delivered') DEFAULT 'Pending',
  `subtotal` decimal(10,2) NOT NULL,
  `shipping` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ecopoints_used` int(11) DEFAULT 0,
  `ecopoints_value` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivered_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_ref`, `users_id`, `status`, `subtotal`, `shipping`, `ecopoints_used`, `ecopoints_value`, `total`, `created_at`, `delivered_at`) VALUES
(1, 'ORD000001', 'USR00001', 'Delivered', 459.96, 0.00, 0, 0.00, 459.96, '2025-10-21 15:30:39', '2025-10-30 13:08:01'),
(2, 'ORD000002', 'USR00001', 'Out for Delivery', 239.98, 0.00, 459, 4.00, 235.39, '2025-10-21 16:49:19', NULL),
(3, 'ORD000003', 'USR00001', 'Delivered', 629.94, 0.00, 0, 0.00, 629.94, '2025-10-21 17:54:53', '2025-10-30 13:07:59'),
(4, 'ORD000004', 'USR00002', 'Out for Delivery', 669.94, 0.00, 0, 0.00, 669.94, '2025-10-21 18:33:15', NULL),
(5, 'ORD000005', 'USR00004', 'Out for Delivery', 339.97, 0.00, 0, 0.00, 339.97, '2025-10-21 18:34:33', NULL),
(6, 'ORD000006', 'USR00004', 'Delivered', 89.99, 0.00, 0, 0.00, 89.99, '2025-10-21 18:37:31', '2025-10-31 13:15:03'),
(7, 'ORD000007', 'USR00001', 'Out for Delivery', 369.96, 0.00, 0, 0.00, 369.96, '2025-10-25 07:36:44', NULL),
(8, 'ORD000008', 'USR00001', 'Delivered', 1499.87, 0.00, 1253, 12.00, 1487.34, '2025-10-28 14:02:32', '2025-10-30 13:08:18'),
(9, 'ORD000009', 'USR00001', 'Out for Delivery', 319.97, 0.00, 0, 0.00, 319.97, '2025-10-29 10:03:14', NULL),
(10, 'ORD000010', 'USR00001', 'Out for Delivery', 89.99, 0.00, 0, 0.00, 89.99, '2025-10-30 19:53:37', NULL),
(11, 'ORD000011', 'USR00001', 'Delivered', 1009.91, 0.00, 0, 0.00, 1009.91, '2025-10-30 19:58:03', '2025-10-31 13:36:20'),
(12, 'ORD000012', 'USR00001', 'Delivered', 469.96, 47.00, 0, 0.00, 516.96, '2025-10-31 09:53:26', '2025-10-31 13:14:36'),
(13, 'ORD000013', 'USR00001', 'Delivered', 1899.95, 190.00, 3420, 34.20, 2055.75, '2025-10-31 09:54:36', '2025-10-31 13:14:32'),
(14, 'ORD000014', 'USR00001', 'Delivered', 369.97, 37.00, 0, 0.00, 406.97, '2025-10-31 10:23:24', '2025-10-31 13:09:44'),
(15, 'ORD000015', 'USR00001', 'Pending', 579.97, 58.00, 0, 0.00, 637.97, '2025-11-02 11:27:48', NULL),
(16, 'ORD000016', 'USR00001', 'Pending', 749.94, 74.99, 3098, 30.98, 793.95, '2025-11-05 09:24:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` varchar(20) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `subtotal`) VALUES
(1, 1, 'ITM00001', 'Compostable Cleaning Pods', 89.99, 2, 179.00),
(2, 1, 'ITM00002', 'Refillable Glass Spray Bottles', 149.99, 1, 149.00),
(3, 1, 'ITM00003', 'Plant-Based Laundry Detergent Sheets', 129.99, 1, 129.00),
(4, 2, 'ITM00001', 'Compostable Cleaning Pods', 89.99, 1, 89.00),
(5, 2, 'ITM00002', 'Refillable Glass Spray Bottles', 149.99, 1, 149.00),
(6, 3, 'ITM00001', 'Compostable Cleaning Pods', 89.99, 3, 269.00),
(7, 3, 'ITM00003', 'Plant-Based Laundry Detergent Sheets', 129.99, 2, 259.00),
(8, 3, 'ITM00004', 'Wool Dryer Balls', 99.99, 1, 99.00),
(9, 4, 'ITM00001', 'Compostable Cleaning Pods', 89.99, 1, 89.00),
(10, 4, 'ITM00002', 'Refillable Glass Spray Bottles', 149.99, 1, 149.00),
(11, 4, 'ITM00003', 'Plant-Based Laundry Detergent Sheets', 129.99, 1, 129.00),
(12, 4, 'ITM00004', 'Wool Dryer Balls', 99.99, 1, 99.00),
(13, 4, 'ITM00005', 'Biodegradable Trash Bags', 79.99, 1, 79.00),
(14, 4, 'ITM00006', 'Natural Air Purifying Charcoal Bags', 119.99, 1, 119.00),
(15, 5, 'ITM00001', 'Compostable Cleaning Pods', 89.99, 1, 89.00),
(16, 5, 'ITM00002', 'Refillable Glass Spray Bottles', 149.99, 1, 149.00),
(17, 5, 'ITM00004', 'Wool Dryer Balls', 99.99, 1, 99.00),
(18, 6, 'ITM00001', 'Compostable Cleaning Pods', 89.99, 1, 89.00),
(19, 7, 'ITM00001', 'Compostable Cleaning Pods', 89.99, 3, 269.00),
(20, 7, 'ITM00004', 'Wool Dryer Balls', 99.99, 1, 99.00),
(21, 8, 'ITM00001', 'Compostable Cleaning Pods', 89.99, 2, 179.00),
(22, 8, 'ITM00002', 'Refillable Glass Spray Bottles', 149.99, 3, 449.00),
(23, 8, 'ITM00003', 'Plant-Based Laundry Detergent Sheets', 129.99, 3, 389.00),
(24, 8, 'ITM00004', 'Wool Dryer Balls', 99.99, 4, 399.00),
(25, 8, 'ITM00005', 'Biodegradable Trash Bags', 79.99, 1, 79.00),
(26, 9, 'ITM00001', 'Compostable Cleaning Pods', 89.99, 1, 89.00),
(27, 9, 'ITM00003', 'Plant-Based Laundry Detergent Sheets', 129.99, 1, 129.00),
(28, 9, 'ITM00004', 'Wool Dryer Balls', 99.99, 1, 99.00),
(29, 10, 'ITM00001', 'Compostable Cleaning Pods', 89.99, 1, 89.00),
(30, 11, 'ITM00001', 'Compostable Cleaning Pods', 89.99, 3, 269.00),
(31, 11, 'ITM00002', 'Refillable Glass Spray Bottles', 149.99, 2, 299.00),
(32, 11, 'ITM00004', 'Wool Dryer Balls', 99.99, 2, 199.00),
(33, 11, 'ITM00006', 'Natural Air Purifying Charcoal Bags', 119.99, 2, 239.00),
(34, 12, 'ITM00001', 'Compostable Cleaning Pods', 89.99, 1, 89.00),
(35, 12, 'ITM00002', 'Refillable Glass Spray Bottles', 149.99, 1, 149.00),
(36, 12, 'ITM00003', 'Plant-Based Laundry Detergent Sheets', 129.99, 1, 129.00),
(37, 12, 'ITM00004', 'Wool Dryer Balls', 99.99, 1, 99.00),
(38, 13, 'ITM00015', 'Upcycled Wood Wall Art and Shelving', 499.99, 2, 999.00),
(39, 13, 'ITM00016', 'Organic Cotton Throw Blankets and Cushion Covers', 299.99, 3, 899.00),
(40, 14, 'ITM00001', 'Compostable Cleaning Pods', 89.99, 1, 89.00),
(41, 14, 'ITM00002', 'Refillable Glass Spray Bottles', 149.99, 1, 149.00),
(42, 14, 'ITM00003', 'Plant-Based Laundry Detergent Sheets', 129.99, 1, 129.00),
(43, 15, 'ITM00003', 'Plant-Based Laundry Detergent Sheets', 129.99, 1, 129.00),
(44, 15, 'ITM00004', 'Wool Dryer Balls', 99.99, 1, 99.00),
(45, 15, 'ITM00023', 'Organic Cotton Towels and Bathrobes', 349.99, 1, 349.00),
(46, 16, 'ITM00001', 'Compostable Cleaning Pods', 89.99, 1, 89.00),
(47, 16, 'ITM00002', 'Refillable Glass Spray Bottles', 149.99, 2, 299.00),
(48, 16, 'ITM00003', 'Plant-Based Laundry Detergent Sheets', 129.99, 2, 259.00),
(49, 16, 'ITM00004', 'Wool Dryer Balls', 99.99, 1, 99.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` varchar(8) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_description` text NOT NULL,
  `manufacturer` varchar(100) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_emissions` float NOT NULL,
  `image_url` varchar(255) DEFAULT 'assets/images/placeholder.jpg',
  `category` varchar(50) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 50
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `product_description`, `manufacturer`, `product_price`, `product_emissions`, `image_url`, `category`, `stock_quantity`) VALUES
('ITM00001', 'Compostable Cleaning Pods', 'Eco-friendly cleaning pods made from compostable materials for multiple surfaces.', 'GreenClean Co.', 89.99, 0.45, 'assets/images/cleaning_pods.jpg', 'Cleaning & Household Supplies', 307),
('ITM00002', 'Refillable Glass Spray Bottles', 'Stylish and reusable glass bottles for your cleaning refills.', 'EcoSpray', 149.99, 0.35, 'assets/images/glass_spray_bottle.jpg', 'Cleaning & Household Supplies', 995),
('ITM00003', 'Plant-Based Laundry Detergent Sheets', 'Concentrated detergent sheets that dissolve completely in water.', 'PureWash', 129.99, 0.6, 'assets/images/laundry_sheets.jpg', 'Cleaning & Household Supplies', 993),
('ITM00004', 'Wool Dryer Balls', 'Natural wool balls that reduce drying time and static.', 'WoolWorks', 99.99, 0.2, 'assets/images/wool_dryer_balls.jpg', 'Cleaning & Household Supplies', 989),
('ITM00005', 'Biodegradable Trash Bags', 'Strong and leak-proof trash bags that decompose naturally.', 'GreenBin', 79.99, 0.5, 'assets/images/biodegradable_bags.jpg', 'Cleaning & Household Supplies', 1001),
('ITM00006', 'Natural Air Purifying Charcoal Bags', 'Charcoal-based deodorizing bags that absorb moisture and odors.', 'PureAir', 119.99, 0.4, 'assets/images/charcoal_bags.jpg', 'Cleaning & Household Supplies', 997),
('ITM00007', 'Bamboo Kitchen Utensils and Cutting Boards', 'Durable, antimicrobial bamboo utensils and cutting boards.', 'EcoKitchen', 249.99, 0.55, 'assets/images/bamboo_utensils.jpg', 'Kitchen & Dining', 1000),
('ITM00008', 'Reusable Silicone Food Storage Bags', 'Leak-proof, freezer-safe silicone bags that replace single-use plastic.', 'ReUseIt', 189.99, 0.4, 'assets/images/silicone_bags.jpg', 'Kitchen & Dining', 1001),
('ITM00009', 'Beeswax Food Wraps', 'Reusable wraps made from organic cotton and beeswax.', 'BeeWrap', 129.99, 0.35, 'assets/images/beeswax_wraps.jpg', 'Kitchen & Dining', 1000),
('ITM00010', 'Compostable Dish Sponges', 'Biodegradable sponges made from plant-based fibers.', 'BioClean', 59.99, 0.25, 'assets/images/compostable_sponges.jpg', 'Kitchen & Dining', 1000),
('ITM00011', 'Stainless Steel Straws and Straw Cleaners', 'Reusable straws with cleaning brush included.', 'SteelSip', 79.99, 0.1, 'assets/images/stainless_straws.jpg', 'Kitchen & Dining', 1000),
('ITM00012', 'Recycled Glass Storage Jars', 'Stylish jars made from 100% recycled glass.', 'GlassCycle', 149.99, 0.3, 'assets/images/glass_jars.jpg', 'Kitchen & Dining', 1000),
('ITM00013', 'Organic Cotton Dish Towels', 'Soft, durable towels made from organic cotton.', 'EcoTextiles', 99.99, 0.25, 'assets/images/dish_towels.jpg', 'Kitchen & Dining', 1000),
('ITM00014', 'Recycled Glass Vases and Candle Holders', 'Elegant home décor pieces crafted from recycled glass.', 'GlassCycle', 249.99, 0.35, 'assets/images/glass_vases.jpg', 'Home Décor & Living', 1000),
('ITM00015', 'Upcycled Wood Wall Art and Shelving', 'Unique wall art made from reclaimed wood.', 'ReWood', 499.99, 0.7, 'assets/images/wood_shelves.jpg', 'Home Décor & Living', 1000),
('ITM00016', 'Organic Cotton Throw Blankets and Cushion Covers', 'Comfortable textiles made from sustainable organic cotton.', 'EcoTextiles', 299.99, 0.5, 'assets/images/throw_blankets.jpg', 'Home Décor & Living', 1000),
('ITM00017', 'Soy Wax Candles with Natural Essential Oils', 'Hand-poured soy candles with calming essential oils.', 'GlowNatural', 179.99, 0.25, 'assets/images/soy_candles.jpg', 'Home Décor & Living', 999),
('ITM00018', 'Indoor Plants with Biodegradable Pots', 'Air-purifying plants grown in compostable pots.', 'GreenLife', 159.99, 0.2, 'assets/images/indoor_plants.jpg', 'Home Décor & Living', 1000),
('ITM00019', 'Eco-Friendly Paint and Finishes', 'Non-toxic, low-VOC paints for sustainable home décor.', 'EcoCoat', 399.99, 0.8, 'assets/images/eco_paint.jpg', 'Home Décor & Living', 1000),
('ITM00020', 'Refillable Shampoo and Conditioner Bottles', 'Glass pump bottles for refillable hair care.', 'PureWash', 149.99, 0.35, 'assets/images/refillable_bottles.jpg', 'Bathroom & Personal Care', 1000),
('ITM00021', 'Bamboo Toothbrushes and Holders', 'Biodegradable bamboo toothbrush set with holder.', 'EcoSmile', 89.99, 0.1, 'assets/images/bamboo_toothbrush.jpg', 'Bathroom & Personal Care', 1000),
('ITM00022', 'Compostable Floss and Packaging', 'Plastic-free floss in compostable packaging.', 'GreenDental', 79.99, 0.05, 'assets/images/compostable_floss.jpg', 'Bathroom & Personal Care', 1000),
('ITM00023', 'Organic Cotton Towels and Bathrobes', 'Luxurious towels and robes made from organic cotton.', 'EcoTextiles', 349.99, 0.6, 'assets/images/cotton_towels.jpg', 'Bathroom & Personal Care', 1000),
('ITM00024', 'Natural Loofahs and Exfoliating Mitts', 'Gentle exfoliating loofahs made from natural fibers.', 'PureBody', 99.99, 0.25, 'assets/images/loofah_mitt.jpg', 'Bathroom & Personal Care', 1000),
('ITM00025', 'Plastic-Free Deodorant and Skincare Products', 'Vegan, zero-waste deodorant and skincare range.', 'EcoGlow', 199.99, 0.4, 'assets/images/deodorant_skincare.jpg', 'Bathroom & Personal Care', 1000),
('ITM00026', 'Reusable Water Bottles Made from Recycled Materials', 'Insulated bottles made with 100% recycled steel.', 'GreenHydrate', 249.99, 0.3, 'assets/images/recycled_bottle.jpg', 'Lifestyle & Wellness', 997),
('ITM00027', 'Eco-Friendly Yoga Mats and Accessories', 'Durable non-slip yoga mats from sustainable rubber.', 'ZenLife', 399.99, 0.5, 'assets/images/yoga_mat.jpg', 'Lifestyle & Wellness', 1000),
('ITM00028', 'Sustainable Journals and Stationery', 'Stationery made from recycled paper and soy ink.', 'PaperRoot', 159.99, 0.15, 'assets/images/sustainable_journal.jpg', 'Lifestyle & Wellness', 1000),
('ITM00029', 'Solar-Powered Lanterns and Chargers', 'Portable solar lighting and charging devices.', 'SunLite', 299.99, 0.45, 'assets/images/solar_lantern.jpg', 'Lifestyle & Wellness', 1000),
('ITM00030', 'Organic Herbal Teas in Compostable Packaging', 'Hand-blended herbal teas in biodegradable packs.', 'NatureBrew', 129.99, 0.2, 'assets/images/herbal_tea.jpg', 'Lifestyle & Wellness', 1000),
('ITM00031', 'Mindfulness Kits with Sustainably Sourced Incense', 'A calming set with incense, guide, and candles.', 'ZenLife', 189.99, 0.25, 'assets/images/mindfulness_kit.jpg', 'Lifestyle & Wellness', 1000),
('ITM00032', 'Wooden Toys Made from FSC-Certified Wood', 'Safe, durable wooden toys made sustainably.', 'PlayGreen', 299.99, 0.35, 'assets/images/wooden_toys.jpg', 'Kids & Pets', 1000),
('ITM00033', 'Organic Cotton Baby Clothes and Blankets', 'Soft babywear made from certified organic cotton.', 'EcoTextiles', 249.99, 0.2, 'assets/images/baby_clothes.jpg', 'Kids & Pets', 1000),
('ITM00034', 'Reusable Cloth Diapers', 'Adjustable and washable cloth diapers.', 'TinySteps', 199.99, 0.25, 'assets/images/cloth_diapers.jpg', 'Kids & Pets', 1000),
('ITM00035', 'Natural Pet Grooming Products', 'Pet-safe shampoo and conditioner with natural oils.', 'PurePaws', 149.99, 0.3, 'assets/images/pet_grooming.jpg', 'Kids & Pets', 1000),
('ITM00036', 'Eco-Friendly Pet Toys and Biodegradable Waste Bags', 'Fun, eco-conscious pet toys and compostable bags.', 'GreenPaws', 119.99, 0.4, 'assets/images/pet_poop.jpg', 'Kids & Pets', 1000),
('ITM00037', 'Compost Bins and Worm Farms', 'Indoor/outdoor compost bins and worm systems.', 'EarthCycle', 399.99, 0.6, 'assets/images/compost_bin.jpg', 'Outdoor & Garden', 1000),
('ITM00038', 'Rainwater Harvesting Kits', 'DIY systems for collecting and reusing rainwater.', 'EcoFlow', 499.99, 0.7, 'assets/images/rainwater_kit.jpg', 'Outdoor & Garden', 1000),
('ITM00039', 'Seed Starter Kits with Heirloom Seeds', 'Grow organic vegetables and herbs easily.', 'GrowEasy', 179.99, 0.3, 'assets/images/seed_kit.jpg', 'Outdoor & Garden', 1000),
('ITM00040', 'Solar-Powered Garden Lights', 'Energy-efficient lights that charge during the day.', 'SunLite', 219.99, 0.25, 'assets/images/garden_lights.jpg', 'Outdoor & Garden', 1000),
('ITM00041', 'Recycled Plastic Planters', 'Durable planters made from 100% recycled plastics.', 'ReGrow', 149.99, 0.2, 'assets/images/recycled_planters.jpg', 'Outdoor & Garden', 1000),
('ITM00042', 'Organic Fertilizers and Pest Repellents', 'Chemical-free fertilizers and plant-safe repellents.', 'PureGrow', 129.99, 0.3, 'assets/images/organic_fertilizer.jpg', 'Outdoor & Garden', 1000);

--
-- Triggers `products`
--
DELIMITER $$
CREATE TRIGGER `before_insert_products` BEFORE INSERT ON `products` FOR EACH ROW BEGIN
  DECLARE next_id INT;
  SET next_id = (SELECT IFNULL(MAX(CAST(SUBSTRING(product_id,4) AS UNSIGNED)),0) + 1 FROM products);
  SET NEW.product_id = CONCAT('ITM', LPAD(next_id,5,'0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `product_id` varchar(20) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `required_quantity` int(11) DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `product_id`, `product_name`, `image_url`, `product_price`, `required_quantity`, `added_at`) VALUES
(1, 'ITM00052', 'Eco Dish Soap Refill', 'assets/products/dish_soap_refill.jpg', 89.99, 1, '2025-10-26 15:07:11'),
(2, 'ITM00053', 'Organic Hand Wash', 'assets/products/handwash.jpg', 79.99, 1, '2025-10-26 15:07:11'),
(3, 'ITM00054', 'Biodegradable Trash Bags (20 Pack)', 'assets/products/trash_bags.jpg', 99.99, 1, '2025-10-26 15:07:11'),
(4, 'ITM00056', 'Eco-Friendly Laundry Detergent (1L)', 'assets/products/laundry_detergent.jpg', 129.99, 1, '2025-10-26 15:07:11'),
(5, 'ITM00057', 'Plant-Based Body Soap', 'assets/products/body_soap.jpg', 59.99, 1, '2025-10-26 15:07:11'),
(6, 'ITM00058', 'Natural Deodorant Stick', 'assets/products/deodorant.jpg', 89.99, 1, '2025-10-26 15:07:11');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `users_id` varchar(10) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `cell_number` varchar(13) NOT NULL,
  `password` varchar(255) NOT NULL,
  `street_address` varchar(255) NOT NULL,
  `suburb` varchar(150) NOT NULL,
  `city` varchar(150) NOT NULL,
  `province` varchar(150) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `ecopoints` int(11) DEFAULT 0,
  `subscription_status` enum('subscribed','notsubscribed') NOT NULL DEFAULT 'notsubscribed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`users_id`, `first_name`, `last_name`, `email`, `cell_number`, `password`, `street_address`, `suburb`, `city`, `province`, `postal_code`, `ecopoints`, `subscription_status`, `created_at`) VALUES
('USR00001', 'Jaydyn', 'Greyling', 'jaydyng12@outlook.com', '824328141', '$2y$10$G0gLuLj/3Y7dEaXMJOwlt.x68EdEWnVIqDIE0kdcKAE5VuZyzM6Ua', '48 Richard avenue, Homestead', 'Germiston', 'JHB', 'Gauteng', '1401', 793, 'subscribed', '2025-10-21 11:39:37'),
('USR00002', 'Gabby', 'Versfeld', 'gabby@mail.com', '0123456789', '$2y$10$JulxcBYQ5i.btwY0ohaaT.ExpFOm0Du6NRwJHmV0q.UHJwugvAwyy', '44 Alsatian Rd, Glen Austin AH', 'Midrand', 'Midrand', 'Gauteng', '1685', 669, 'notsubscribed', '2025-10-21 15:54:37'),
('USR00004', 'Heidi', 'Greyling', 'heidi@basf.com', '27795051334', '$2y$10$BSpBa1fH6c41mZXXoOlx3uSbKX9J5RViFbuSshmjOuW4bvzvUu0hK', 'Cnr Nywerheid Street', 'Elandsfontein', 'Germiston', 'Gauteng', '1406', 428, 'notsubscribed', '2025-10-21 18:31:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `community_posts`
--
ALTER TABLE `community_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_ref` (`order_ref`),
  ADD KEY `users_id` (`users_id`);

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
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`users_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `community_posts`
--
ALTER TABLE `community_posts`
  ADD CONSTRAINT `community_posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`users_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `users` (`users_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
