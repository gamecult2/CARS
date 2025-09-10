-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 07, 2025 at 10:00 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `car_dealership`
--
CREATE DATABASE IF NOT EXISTS `car_dealership` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `car_dealership`;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$I/h.p7b9.U7.u8.L.R6c6uR4g7c8i9o0k2l3m4n5o6p7q8r9s0t1'); -- password is 'password'

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int(4) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `mileage` int(11) NOT NULL,
  `fuel_type` varchar(50) DEFAULT NULL,
  `transmission` varchar(50) DEFAULT NULL,
  `drivetrain` varchar(50) DEFAULT NULL,
  `body_type` varchar(50) DEFAULT NULL,
  `exterior_color` varchar(50) DEFAULT NULL,
  `color_hex` varchar(7) DEFAULT NULL,
  `steering` varchar(10) DEFAULT NULL,
  `seats` int(2) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `weight` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `accessories` text DEFAULT NULL, -- Comma-separated list
  `images` text DEFAULT NULL, -- Comma-separated list of image filenames
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `brand`, `model`, `year`, `price`, `mileage`, `fuel_type`, `transmission`, `drivetrain`, `body_type`, `exterior_color`, `seats`, `dimensions`, `weight`, `description`, `accessories`, `images`, `is_featured`) VALUES
(1, 'Toyota', 'Camry', 2021, '25000.00', 15000, 'Petrol', 'Automatic', 'FWD', 'Sedan', 'White', 5, '4885x1840x1445', 1530, 'A reliable and fuel-efficient sedan.', 'Power Windows,ABS,Airbags,Bluetooth', 'camry1.jpg,camry2.jpg', 1),
(2, 'Honda', 'Civic', 2022, '22000.00', 12000, 'Petrol', 'CVT', 'FWD', 'Sedan', 'Black', 5, '4674x1802x1415', 1353, 'Sporty and fun to drive.', 'Power Steering,ESP,Parking Radar', 'civic1.jpg,civic2.jpg', 0),
(3, 'Ford', 'Mustang', 2020, '35000.00', 20000, 'Petrol', 'Manual', 'RWD', 'Coupe', 'Red', 4, '4794x1916x1373', 1705, 'An iconic American muscle car.', 'Driver Airbag,Tire Pressure Monitor,Central Locking', 'mustang1.jpg,mustang2.jpg', 1),
(4, 'Tesla', 'Model 3', 2023, '45000.00', 5000, 'Electric', 'Automatic', 'AWD', 'Sedan', 'Blue', 5, '4694x1850x1443', 1847, 'A stylish and high-tech electric car.', 'ESP,Bluetooth,Power Windows,ABS', 'model3_1.jpg,model3_2.jpg', 0),
(5, 'BMW', 'X5', 2019, '55000.00', 30000, 'Diesel', 'Automatic', 'AWD', 'SUV', 'Grey', 5, '4922x2004x1745', 2185, 'A luxurious and spacious SUV.', 'Parking Radar,Passenger Airbag,Spare Tire', 'x5_1.jpg,x5_2.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `car_id` int(11) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `car_id` (`car_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_type` enum('user','admin') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `conversation_id` (`conversation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--
CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--
CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `discount_percent` decimal(5,2) DEFAULT NULL,
  `car_id` int(11) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','moderator') NOT NULL DEFAULT 'user',
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `order_status` enum('Pending','Processing','Documents Requested','Ready for Pickup','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `car_id` (`car_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_history`
--

CREATE TABLE `order_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `actor_id` int(11) NOT NULL,
  `actor_type` enum('user','admin','moderator') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_messages`
--

CREATE TABLE `order_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_type` enum('user','admin','moderator') NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_files`
--

CREATE TABLE `order_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `uploader_id` int(11) NOT NULL,
  `uploader_type` enum('user','admin','moderator') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`) VALUES
(1, 'John Doe', 'john.doe@example.com', '$2y$10$a.b.c.d.e.f.g.h.i.j.k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z12345'), -- password is 'password123'
(2, 'Jane Smith', 'jane.smith@example.com', '$2y$10$z.y.x.w.v.u.t.s.r.q.p.o.n.m.l.k.j.i.h.g.f.e.d.c.b.a54321'); -- password is 'securepassword'

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE SET NULL;

--
-- AUTO_INCREMENT for new tables
--
ALTER TABLE `orders` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `order_history` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `order_messages` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `order_files` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `notifications` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for new tables
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE;

ALTER TABLE `order_history`
  ADD CONSTRAINT `order_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

ALTER TABLE `order_messages`
  ADD CONSTRAINT `order_messages_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

ALTER TABLE `order_files`
  ADD CONSTRAINT `order_files_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Indexes for new tables
--
ALTER TABLE `blog_posts` ADD PRIMARY KEY (`id`), ADD KEY `author_id` (`author_id`);
ALTER TABLE `promotions` ADD PRIMARY KEY (`id`), ADD KEY `car_id` (`car_id`);

--
-- AUTO_INCREMENT for new tables
--
ALTER TABLE `blog_posts` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `promotions` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for new tables
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `admin` (`id`) ON DELETE CASCADE;
ALTER TABLE `promotions`
  ADD CONSTRAINT `promotions_ibfk_1` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE SET NULL;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--
CREATE TABLE `wishlist` (
  `user_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for new tables
--
ALTER TABLE `wishlist` ADD PRIMARY KEY (`user_id`,`car_id`), ADD KEY `car_id` (`car_id`);

--
-- Constraints for new tables
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for new tables
--
ALTER TABLE `reviews` ADD PRIMARY KEY (`id`), ADD KEY `car_id` (`car_id`), ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for new tables
--
ALTER TABLE `reviews` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for new tables
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--
CREATE TABLE `brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`name`, `logo_url`, `is_featured`) VALUES
('BYD', 'https://logo.clearbit.com/byd.com', 1),
('Toyota', 'https://logo.clearbit.com/toyota.com', 1),
('HongQi', 'https://logo.clearbit.com/hongqi.com', 1),
('ChangAn', 'https://logo.clearbit.com/changan.com', 1),
('Nissan', 'https://logo.clearbit.com/nissan.com', 1),
('Mercedes-Benz', 'https://logo.clearbit.com/mercedes-benz.com', 1),
('Chery', 'https://logo.clearbit.com/chery.com', 1),
('MG', 'https://logo.clearbit.com/mg.com', 1),
('Geely', 'https://logo.clearbit.com/geely.com', 1),
('Haval', 'https://logo.clearbit.com/haval.com', 1),
('GAC', 'https://logo.clearbit.com/gac.com', 1),
('Jetour', 'https://logo.clearbit.com/jetour.com', 1),
('Tesla', 'https://logo.clearbit.com/tesla.com', 1),
('NIO', 'https://logo.clearbit.com/nio.com', 1),
('Xpeng', 'https://logo.clearbit.com/xpeng.com', 1),
('Zeekr', 'https://logo.clearbit.com/zeekr.com', 1),
('Volkswagen', 'https://logo.clearbit.com/volkswagen.com', 1),
('BMW', 'https://logo.clearbit.com/bmw.com', 1),
('Audi', 'https://logo.clearbit.com/audi.com', 0),
('Aion', 'https://logo.clearbit.com/aion.com', 0),
('AITO', 'https://logo.clearbit.com/aito.com', 0),
('Avatr', 'https://logo.clearbit.com/avatr.com', 0),
('ARCFOX', 'https://logo.clearbit.com/arcfox.com', 0),
('Alfa Romeo', 'https://logo.clearbit.com/alfaromeo.com', 0),
('Acura', 'https://logo.clearbit.com/acura.com', 0),
('Aston Martin', 'https://logo.clearbit.com/astonmartin.com', 0),
('Aiways', 'https://logo.clearbit.com/aiways.com', 0),
('AUXUN', 'https://logo.clearbit.com/auxun.com', 0),
('ALPINA', 'https://logo.clearbit.com/alpina.com', 0),
('Buick', 'https://logo.clearbit.com/buick.com', 0),
('BaoJun', 'https://logo.clearbit.com/baojun.com', 0),
('Bestune', 'https://logo.clearbit.com/bestune.com', 0),
('Beijing', 'https://logo.clearbit.com/beijing.com', 0),
('BAW', 'https://logo.clearbit.com/baw.com', 0),
('BeiJing Auto', 'https://logo.clearbit.com/beijingauto.com', 0),
('Brilliance Auto', 'https://logo.clearbit.com/brillianceauto.com', 0),
('BAIC BJEV', 'https://logo.clearbit.com/baicbjev.com', 0),
('BAIC Hyosow', 'https://logo.clearbit.com/baichyosow.com', 0),
('Borgward', 'https://logo.clearbit.com/borgward.com', 0),
('BAIC WeiWang', 'https://logo.clearbit.com/baicweiwang.com', 0),
('BAIC ChangHe', 'https://logo.clearbit.com/baicchanghe.com', 0),
('Bentley', 'https://logo.clearbit.com/bentley.com', 0),
('BiSu', 'https://logo.clearbit.com/bisu.com', 0),
('Brabus', 'https://logo.clearbit.com/brabus.com', 0),
('BAIC RuiXiang', 'https://logo.clearbit.com/baicruixiang.com', 0),
('BiKe', 'https://logo.clearbit.com/bike.com', 0),
('BaiZhi', 'https://logo.clearbit.com/baizhi.com', 0),
('BAIC Reach', 'https://logo.clearbit.com/baicreach.com', 0),
('Chevrolet', 'https://logo.clearbit.com/chevrolet.com', 0),
('Cadillac', 'https://logo.clearbit.com/cadillac.com', 0),
('ChangAn QiYuan', 'https://logo.clearbit.com/changanqiyuan.com', 0),
('Citroen', 'https://logo.clearbit.com/citroen.com', 0),
('ChangAn Oshan', 'https://logo.clearbit.com/changanoshan.com', 0),
('ChangAn Kaicene', 'https://logo.clearbit.com/changankaicene.com', 0),
('Chery EV', 'https://logo.clearbit.com/cheryev.com', 0),
('Chrysler', 'https://logo.clearbit.com/chrysler.com', 0),
('ChangAn KuaYue', 'https://logo.clearbit.com/chankuayue.com', 0),
('Ciimo', 'https://logo.clearbit.com/ciimo.com', 0),
('Cyberspace', 'https://logo.clearbit.com/cyberspace.com', 0),
('CHEVOO', 'https://logo.clearbit.com/chevoo.com', 0),
('Century', 'https://logo.clearbit.com/century.com', 0),
('CHTC', 'https://logo.clearbit.com/chtc.com', 0),
('ChengShi', 'https://logo.clearbit.com/chengshi.com', 0),
('Carlsson', 'https://logo.clearbit.com/carlsson.com', 0),
('Denza', 'https://logo.clearbit.com/denza.com', 0),
('DongFeng Aeolus', 'https://logo.clearbit.com/dongfengaeolus.com', 0),
('Deepal', 'https://logo.clearbit.com/deepal.com', 0),
('DongFeng Forthing', 'https://logo.clearbit.com/dongfengforthing.com', 0),
('DongFeng Nammi', 'https://logo.clearbit.com/dongfengnammi.com', 0),
('Dongfeng', 'https://logo.clearbit.com/dongfeng.com', 0),
('DongFeng Fengon', 'https://logo.clearbit.com/dongfengfengon.com', 0),
('DS', 'https://logo.clearbit.com/ds.com', 0),
('DongFeng DFSK', 'https://logo.clearbit.com/dongfengdfsk.com', 0),
('DongFeng DFAC', 'https://logo.clearbit.com/dongfengdfac.com', 0),
('Dodge', 'https://logo.clearbit.com/dodge.com', 0),
('DongFeng FuKang', 'https://logo.clearbit.com/dongfengfukang.com', 0),
('DaYun', 'https://logo.clearbit.com/dayun.com', 0),
('DongFeng FengDu', 'https://logo.clearbit.com/dongfengfengdu.com', 0),
('Dorcen', 'https://logo.clearbit.com/dorcen.com', 0),
('Dearcc', 'https://logo.clearbit.com/dearcc.com', 0),
('Exceed', 'https://logo.clearbit.com/exceed.com', 0),
('Everus', 'https://logo.clearbit.com/everus.com', 0),
('Enovate', 'https://logo.clearbit.com/enovate.com', 0),
('Enranger', 'https://logo.clearbit.com/enranger.com', 0),
('EV House', 'https://logo.clearbit.com/evhouse.com', 0),
('Ford', 'https://logo.clearbit.com/ford.com', 0),
('FangChengBao', 'https://logo.clearbit.com/fangchengbao.com', 0),
('Fulwin', 'https://logo.clearbit.com/fulwin.com', 0),
('Ferrari', 'https://logo.clearbit.com/ferrari.com', 0),
('Foton', 'https://logo.clearbit.com/foton.com', 0),
('FAW', 'https://logo.clearbit.com/faw.com', 0),
('Fiat', 'https://logo.clearbit.com/fiat.com', 0),
('Firefly', 'https://logo.clearbit.com/firefly.com', 0),
('Foday', 'https://logo.clearbit.com/foday.com', 0),
('GAC Trumpchi', 'https://logo.clearbit.com/gactrumpchi.com', 0),
('Geely Galaxy', 'https://logo.clearbit.com/geelygalaxy.com', 0),
('Geometry', 'https://logo.clearbit.com/geometry.com', 0),
('Great Wall', 'https://logo.clearbit.com/greatwall.com', 0),
('Genesis', 'https://logo.clearbit.com/genesis.com', 0),
('Golden Dragon', 'https://logo.clearbit.com/goldendragon.com', 0),
('GMC', 'https://logo.clearbit.com/gmc.com', 0),
('Gleagle', 'https://logo.clearbit.com/gleagle.com', 0),
('GAC Gonow', 'https://logo.clearbit.com/gacgonow.com', 0),
('GuoJi', 'https://logo.clearbit.com/guoji.com', 0),
('George Patton', 'https://logo.clearbit.com/georgepatton.com', 0),
('Honda', 'https://logo.clearbit.com/honda.com', 0),
('Hyundai', 'https://logo.clearbit.com/hyundai.com', 0),
('HIMA', 'https://logo.clearbit.com/hima.com', 0),
('HaiMa', 'https://logo.clearbit.com/haima.com', 0),
('HanTeng', 'https://logo.clearbit.com/hanteng.com', 0),
('HYPTEC', 'https://logo.clearbit.com/hyptec.com', 0),
('HiPhi', 'https://logo.clearbit.com/hiphi.com', 0),
('HawTai', 'https://logo.clearbit.com/hawtai.com', 0),
('Hycan', 'https://logo.clearbit.com/hycan.com', 0),
('Hummer', 'https://logo.clearbit.com/hummer.com', 0),
('Honda eπ', 'https://logo.clearbit.com/hondae.com', 0),
('HuaSong', 'https://logo.clearbit.com/huasong.com', 0),
('HuangHai', 'https://logo.clearbit.com/huanghai.com', 0),
('Hafei Motor', 'https://logo.clearbit.com/hafeimotor.com', 0),
('HuaChenXinRi', 'https://logo.clearbit.com/huachenxinri.com', 0),
('Higer', 'https://logo.clearbit.com/higer.com', 0),
('HuaTai', 'https://logo.clearbit.com/huatai.com', 0),
('Horki', 'https://logo.clearbit.com/horki.com', 0),
('HengChi', 'https://logo.clearbit.com/hengchi.com', 0),
('Infiniti', 'https://logo.clearbit.com/infiniti.com', 0),
('IM', 'https://logo.clearbit.com/im.com', 0),
('Isuzu', 'https://logo.clearbit.com/isuzu.com', 0),
('Iveco', 'https://logo.clearbit.com/iveco.com', 0),
('iCAR', 'https://logo.clearbit.com/icar.com', 0),
('Jeep', 'https://logo.clearbit.com/jeep.com', 0),
('Jetta', 'https://logo.clearbit.com/jetta.com', 0),
('Jaguar', 'https://logo.clearbit.com/jaguar.com', 0),
('JAC', 'https://logo.clearbit.com/jac.com', 0),
('Jetour ShanHai', 'https://logo.clearbit.com/jetourshanhai.com', 0),
('JinBei', 'https://logo.clearbit.com/jinbei.com', 0),
('JMC', 'https://logo.clearbit.com/jmc.com', 0),
('JAC Refine', 'https://logo.clearbit.com/jacrefine.com', 0),
('JMEV', 'https://logo.clearbit.com/jmev.com', 0),
('JunTian', 'https://logo.clearbit.com/juntian.com', 0),
('JiYue', 'https://logo.clearbit.com/jiyue.com', 0),
('JAC EV', 'https://logo.clearbit.com/jacev.com', 0),
('Joylong', 'https://logo.clearbit.com/joylong.com', 0),
('JDMC', 'https://logo.clearbit.com/jdmc.com', 0),
('Jonway', 'https://logo.clearbit.com/jonway.com', 0),
('JinGuan', 'https://logo.clearbit.com/jinguan.com', 0),
('JiangNan', 'https://logo.clearbit.com/jiangnan.com', 0),
('Jenhoo', 'https://logo.clearbit.com/jenhoo.com', 0),
('Kia', 'https://logo.clearbit.com/kia.com', 0),
('KaiYi', 'https://logo.clearbit.com/kaiyi.com', 0),
('Karry', 'https://logo.clearbit.com/karry.com', 0),
('KingLong', 'https://logo.clearbit.com/kinglong.com', 0),
('Kama', 'https://logo.clearbit.com/kama.com', 0),
('Kede', 'https://logo.clearbit.com/kede.com', 0),
('KaSheng', 'https://logo.clearbit.com/kasheng.com', 0),
('Land Rover', 'https://logo.clearbit.com/landrover.com', 0),
('Li', 'https://logo.clearbit.com/li.com', 0),
('LYNK&CO', 'https://logo.clearbit.com/lynkco.com', 0),
('Lincoln', 'https://logo.clearbit.com/lincoln.com', 0),
('Lexus', 'https://logo.clearbit.com/lexus.com', 0),
('Leapmotor', 'https://logo.clearbit.com/leapmotor.com', 0),
('Lamborghini', 'https://logo.clearbit.com/lamborghini.com', 0),
('Livan', 'https://logo.clearbit.com/livan.com', 0),
('Leopaard', 'https://logo.clearbit.com/leopaard.com', 0),
('LiFan', 'https://logo.clearbit.com/lifan.com', 0),
('Luxgen', 'https://logo.clearbit.com/luxgen.com', 0),
('Landwind', 'https://logo.clearbit.com/landwind.com', 0),
('Lotus', 'https://logo.clearbit.com/lotus.com', 0),
('LanDian', 'https://logo.clearbit.com/landian.com', 0),
('LingBox', 'https://logo.clearbit.com/lingbox.com', 0),
('Lorinser', 'https://logo.clearbit.com/lorinser.com', 0),
('Levdeo', 'https://logo.clearbit.com/levdeo.com', 0),
('LinkTour', 'https://logo.clearbit.com/linktour.com', 0),
('LEVC', 'https://logo.clearbit.com/levc.com', 0),
('Linxys', 'https://logo.clearbit.com/linxys.com', 0),
('LITE', 'https://logo.clearbit.com/lite.com', 0),
('Langsi', 'https://logo.clearbit.com/langsi.com', 0),
('Mazda', 'https://logo.clearbit.com/mazda.com', 0),
('MI', 'https://logo.clearbit.com/mi.com', 0),
('MAXUS', 'https://logo.clearbit.com/maxus.com', 0),
('Mitsubishi', 'https://logo.clearbit.com/mitsubishi.com', 0),
('Maserati', 'https://logo.clearbit.com/maserati.com', 0),
('MINI', 'https://logo.clearbit.com/mini.com', 0),
('Mansory', 'https://logo.clearbit.com/mansory.com', 0),
('McLaren', 'https://logo.clearbit.com/mclaren.com', 0),
('M Hero', 'https://logo.clearbit.com/mhero.com', 0),
('Morgan', 'https://logo.clearbit.com/morgan.com', 0),
('Modern Auto', 'https://logo.clearbit.com/modernauto.com', 0),
('Neta', 'https://logo.clearbit.com/neta.com', 0),
('NLM Motor', 'https://logo.clearbit.com/nlmmotor.com', 0),
('New Gonow', 'https://logo.clearbit.com/newgonow.com', 0),
('Ora', 'https://logo.clearbit.com/ora.com', 0),
('ONVO', 'https://logo.clearbit.com/onvo.com', 0),
('Opel', 'https://logo.clearbit.com/opel.com', 0),
('Oley', 'https://logo.clearbit.com/oley.com', 0),
('Peugeot', 'https://logo.clearbit.com/peugeot.com', 0),
('Porsche', 'https://logo.clearbit.com/porsche.com', 0),
('Polestar', 'https://logo.clearbit.com/polestar.com', 0),
('Pocco', 'https://logo.clearbit.com/pocco.com', 0),
('Qoros', 'https://logo.clearbit.com/qoros.com', 0),
('QianTu', 'https://logo.clearbit.com/qiantu.com', 0),
('Roewe', 'https://logo.clearbit.com/roewe.com', 0),
('Rolls-Royce', 'https://logo.clearbit.com/rolls-royce.com', 0),
('Renault', 'https://logo.clearbit.com/renault.com', 0),
('Rising Auto', 'https://logo.clearbit.com/risingauto.com', 0),
('ROXPloeStone', 'https://logo.clearbit.com/roxploestone.com', 0),
('Ruichi Auto', 'https://logo.clearbit.com/ruichiauto.com', 0),
('RAM', 'https://logo.clearbit.com/ram.com', 0),
('Radar', 'https://logo.clearbit.com/radar.com', 0),
('Riich', 'https://logo.clearbit.com/riich.com', 0),
('Reach', 'https://logo.clearbit.com/reach.com', 0),
('Rely', 'https://logo.clearbit.com/rely.com', 0),
('Rhine Auto', 'https://logo.clearbit.com/rhineauto.com', 0),
('Skoda', 'https://logo.clearbit.com/skoda.com', 0),
('Suzuki', 'https://logo.clearbit.com/suzuki.com', 0),
('Subaru', 'https://logo.clearbit.com/subaru.com', 0),
('Smart', 'https://logo.clearbit.com/smart.com', 0),
('ShanHai', 'https://logo.clearbit.com/shanhai.com', 0),
('Soueast', 'https://logo.clearbit.com/soueast.com', 0),
('Sehol', 'https://logo.clearbit.com/sehol.com', 0),
('Skyworth', 'https://logo.clearbit.com/skyworth.com', 0),
('SWM', 'https://logo.clearbit.com/swm.com', 0),
('SERES', 'https://logo.clearbit.com/seres.com', 0),
('ShenZhou', 'https://logo.clearbit.com/shenzhou.com', 0),
('SsangYong', 'https://logo.clearbit.com/ssangyong.com', 0),
('Seat', 'https://logo.clearbit.com/seat.com', 0),
('Saleen', 'https://logo.clearbit.com/saleen.com', 0),
('SRM', 'https://logo.clearbit.com/srm.com', 0),
('SONGSAN MOTORS', 'https://logo.clearbit.com/songsanmotors.com', 0),
('Saab', 'https://logo.clearbit.com/saab.com', 0),
('SunLong', 'https://logo.clearbit.com/sunlong.com', 0),
('Sitech', 'https://logo.clearbit.com/sitech.com', 0),
('Speed Auto', 'https://logo.clearbit.com/speedauto.com', 0),
('SHELBY', 'https://logo.clearbit.com/shelby.com', 0),
('SinoTruck VGV', 'https://logo.clearbit.com/sinotruckvgv.com', 0),
('Skywell', 'https://logo.clearbit.com/skywell.com', 0),
('SionGold', 'https://logo.clearbit.com/siongold.com', 0),
('Shenzer', 'https://logo.clearbit.com/shenzer.com', 0),
('212', 'https://logo.clearbit.com/212.com', 0),
('Tank', 'https://logo.clearbit.com/tank.com', 0),
('Traum', 'https://logo.clearbit.com/traum.com', 0),
('The Durant Guild', 'https://logo.clearbit.com/thedurantguild.com', 0),
('TECHART', 'https://logo.clearbit.com/techart.com', 0),
('Volvo', 'https://logo.clearbit.com/volvo.com', 0),
('Voyah', 'https://logo.clearbit.com/voyah.com', 0),
('Venucia', 'https://logo.clearbit.com/venucia.com', 0),
('VGV', 'https://logo.clearbit.com/vgv.com', 0),
('Victory Auto', 'https://logo.clearbit.com/victoryauto.com', 0),
('Vulcanus', 'https://logo.clearbit.com/vulcanus.com', 0),
('WuLing', 'https://logo.clearbit.com/wuling.com', 0),
('WEY', 'https://logo.clearbit.com/wey.com', 0),
('Weltmeister', 'https://logo.clearbit.com/weltmeister.com', 0),
('WanXiang', 'https://logo.clearbit.com/wanxiang.com', 0),
('XiaoHu EV', 'https://logo.clearbit.com/xiaohuev.com', 0),
('Xinkai', 'https://logo.clearbit.com/xinkai.com', 0),
('YangWang', 'https://logo.clearbit.com/yangwang.com', 0),
('Yudo', 'https://logo.clearbit.com/yudo.com', 0),
('Yema', 'https://logo.clearbit.com/yema.com', 0),
('YuanChen', 'https://logo.clearbit.com/yuanchen.com', 0),
('Youngman Lotus', 'https://logo.clearbit.com/youngmanlotus.com', 0),
('YaSheng', 'https://logo.clearbit.com/yasheng.com', 0),
('Zotye', 'https://logo.clearbit.com/zotye.com', 0),
('ZD', 'https://logo.clearbit.com/zd.com', 0),
('ZX AUTO', 'https://logo.clearbit.com/zxauto.com', 0),
('Zedriv', 'https://logo.clearbit.com/zedriv.com', 0),
('Zinoro', 'https://logo.clearbit.com/zinoro.com', 0);

COMMIT;
