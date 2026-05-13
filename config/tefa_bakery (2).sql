-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 11, 2026 at 09:55 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tefa_bakery`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `nama_kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `nama_kategori`) VALUES
(1, 'Minuman Panas'),
(2, 'Minuman Dingin'),
(3, 'Bread');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `user_id` int NOT NULL,
  `tipe_mutasi` enum('masuk','keluar','rusak','retur') NOT NULL,
  `jumlah` int NOT NULL,
  `stok_sebelum` int NOT NULL,
  `stok_sesudah` int NOT NULL,
  `keterangan` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `inventory_logs`
--

INSERT INTO `inventory_logs` (`id`, `product_id`, `user_id`, `tipe_mutasi`, `jumlah`, `stok_sebelum`, `stok_sesudah`, `keterangan`, `created_at`) VALUES
(4, 1, 1, 'masuk', 12, 21, 33, 'Restock dari Supplier', '2026-05-03 10:16:49'),
(5, 1, 1, 'keluar', 1, 33, 32, 'Penjualan Kasir — No. Invoice: INV-20260503-D2E9EC', '2026-05-03 11:25:49'),
(6, 1, 1, 'keluar', 1, 32, 31, 'Penjualan Kasir — No. Invoice: INV-20260503-691806', '2026-05-03 11:43:50'),
(7, 1, 1, 'keluar', 2, 31, 29, 'Penjualan Kasir — No. Invoice: INV-20260503-38EBCC', '2026-05-03 11:50:43'),
(8, 1, 1, 'keluar', 10, 29, 19, 'Penjualan Kasir — No. Invoice: INV-20260503-90FEF8', '2026-05-03 11:54:01'),
(9, 5, 1, 'keluar', 2, 12, 10, 'Penjualan Kasir — No. Invoice: INV-20260503-31C30A', '2026-05-03 12:00:51'),
(10, 8, 1, 'keluar', 1, 35, 34, 'Penjualan Kasir — No. Invoice: INV-20260503-31C30A', '2026-05-03 12:00:51'),
(11, 9, 1, 'keluar', 1, 20, 19, 'Penjualan Kasir — No. Invoice: INV-20260503-31C30A', '2026-05-03 12:00:51'),
(12, 10, 1, 'keluar', 1, 25, 24, 'Penjualan Kasir — No. Invoice: INV-20260503-31C30A', '2026-05-03 12:00:51'),
(13, 7, 1, 'keluar', 1, 40, 39, 'Penjualan Kasir — No. Invoice: INV-20260503-31C30A', '2026-05-03 12:00:51'),
(14, 6, 1, 'keluar', 1, 50, 49, 'Penjualan Kasir — No. Invoice: INV-20260503-31C30A', '2026-05-03 12:00:51'),
(15, 1, 1, 'keluar', 1, 19, 18, 'Penjualan Kasir — No. Invoice: INV-20260503-31C30A', '2026-05-03 12:00:51'),
(16, 9, 1, 'keluar', 1, 19, 18, 'Penjualan Kasir — No. Invoice: INV-20260503-ACDC9D', '2026-05-03 12:15:54'),
(17, 8, 1, 'keluar', 1, 34, 33, 'Penjualan Kasir — No. Invoice: INV-20260504-63A031', '2026-05-04 13:33:42'),
(18, 9, 1, 'keluar', 1, 18, 17, 'Penjualan Kasir — No. Invoice: INV-20260504-63A031', '2026-05-04 13:33:42'),
(19, 10, 1, 'keluar', 1, 24, 23, 'Penjualan Kasir — No. Invoice: INV-20260504-63A031', '2026-05-04 13:33:42'),
(20, 1, 1, 'keluar', 1, 18, 17, 'Penjualan Kasir — No. Invoice: INV-20260504-63A031', '2026-05-04 13:33:42'),
(21, 5, 1, 'keluar', 1, 10, 9, 'Penjualan Kasir — No. Invoice: INV-20260504-63A031', '2026-05-04 13:33:42'),
(22, 6, 1, 'keluar', 1, 49, 48, 'Penjualan Kasir — No. Invoice: INV-20260504-63A031', '2026-05-04 13:33:42'),
(23, 10, 1, 'keluar', 1, 23, 22, 'Penjualan Kasir — No. Invoice: INV-20260504-C62EC7', '2026-05-04 13:53:16'),
(24, 9, 1, 'keluar', 1, 17, 16, 'Penjualan Kasir — No. Invoice: INV-20260504-5BE4EC', '2026-05-04 21:00:37'),
(25, 8, 1, 'keluar', 1, 33, 32, 'Penjualan Kasir — No. Invoice: INV-20260504-5BE4EC', '2026-05-04 21:00:37'),
(26, 9, 1, 'keluar', 1, 16, 15, 'Penjualan Kasir — No. Invoice: INV-20260504-BBDF12', '2026-05-04 21:00:59'),
(27, 10, 1, 'keluar', 1, 22, 21, 'Penjualan Kasir — No. Invoice: INV-20260504-BBDF12', '2026-05-04 21:00:59'),
(28, 1, 1, 'keluar', 1, 17, 16, 'Penjualan Kasir — No. Invoice: INV-20260504-494E4B', '2026-05-04 21:05:40'),
(29, 1, 2, 'keluar', 1, 16, 15, 'Penjualan Kasir — No. Invoice: INV-20260505-8BD2D0', '2026-05-05 02:08:40'),
(30, 5, 2, 'keluar', 1, 9, 8, 'Penjualan Kasir — No. Invoice: INV-20260505-8BD2D0', '2026-05-05 02:08:40'),
(31, 9, 2, 'keluar', 1, 15, 14, 'Penjualan Kasir — No. Invoice: INV-20260505-8BD2D0', '2026-05-05 02:08:40'),
(34, 1, 2, 'keluar', 1, 15, 14, 'Penjualan Kasir — No. Invoice: INV-20260505-EB0498', '2026-05-05 02:51:42'),
(35, 10, 2, 'keluar', 1, 21, 20, 'Penjualan Kasir — No. Invoice: INV-20260505-EB0498', '2026-05-05 02:51:42'),
(36, 9, 2, 'keluar', 3, 14, 11, 'Penjualan Kasir — No. Invoice: INV-20260505-EB0498', '2026-05-05 02:51:42'),
(37, 10, 2, 'keluar', 1, 20, 19, 'Penjualan Kasir — No. Invoice: INV-20260505-B683C3', '2026-05-05 02:52:11'),
(38, 10, 5, 'masuk', 1, 19, 20, 'VOID Transaksi ID: 17', '2026-05-05 03:01:04'),
(39, 1, 5, 'masuk', 1, 14, 15, 'VOID Transaksi ID: 16', '2026-05-05 03:01:22'),
(40, 10, 5, 'masuk', 1, 20, 21, 'VOID Transaksi ID: 16', '2026-05-05 03:01:22'),
(41, 9, 5, 'masuk', 3, 11, 14, 'VOID Transaksi ID: 16', '2026-05-05 03:01:22'),
(42, 9, 2, 'keluar', 1, 14, 13, 'Penjualan Kasir — No. Invoice: INV-20260505-A20C3E', '2026-05-05 03:02:34'),
(43, 8, 2, 'keluar', 1, 32, 31, 'Penjualan Kasir — No. Invoice: INV-20260505-551C54', '2026-05-05 03:03:33'),
(44, 8, 2, 'keluar', 1, 31, 30, 'Penjualan Kasir — No. Invoice: INV-20260505-3DB99E', '2026-05-05 03:12:03'),
(45, 10, 3, 'masuk', 2, 21, 23, 'Restock dari Supplier', '2026-05-05 03:18:04'),
(46, 8, 2, 'keluar', 4, 30, 26, 'Penjualan Kasir — No. Invoice: INV-20260506-01EC4F', '2026-05-06 00:47:44'),
(47, 9, 2, 'keluar', 2, 13, 11, 'Penjualan Kasir — No. Invoice: INV-20260506-01EC4F', '2026-05-06 00:47:44'),
(48, 7, 2, 'keluar', 2, 39, 37, 'Penjualan Kasir — No. Invoice: INV-20260506-A63BEE', '2026-05-06 01:03:06'),
(49, 6, 2, 'keluar', 1, 48, 47, 'Penjualan Kasir — No. Invoice: INV-20260506-A63BEE', '2026-05-06 01:03:06'),
(50, 10, 2, 'keluar', 20, 23, 3, 'Penjualan Kasir — No. Invoice: INV-20260506-C6F3E7', '2026-05-06 01:15:56'),
(51, 9, 2, 'keluar', 1, 11, 10, 'Penjualan Kasir — No. Invoice: INV-20260506-923E44', '2026-05-06 01:44:25'),
(52, 8, 2, 'keluar', 1, 26, 25, 'Penjualan Kasir — No. Invoice: INV-20260506-923E44', '2026-05-06 01:44:25'),
(53, 10, 7, 'keluar', 1, 3, 2, 'Penjualan Kasir — No. Invoice: INV-20260508-35A66C', '2026-05-08 03:10:59'),
(54, 9, 7, 'keluar', 2, 10, 8, 'Penjualan Kasir — No. Invoice: INV-20260508-35A66C', '2026-05-08 03:10:59'),
(55, 8, 7, 'keluar', 1, 25, 24, 'Penjualan Kasir — No. Invoice: INV-20260508-35A66C', '2026-05-08 03:10:59'),
(56, 8, 7, 'keluar', 1, 24, 23, 'Penjualan Kasir — No. Invoice: INV-20260508-1DC7C4', '2026-05-08 03:58:57'),
(57, 5, 7, 'keluar', 1, 8, 7, 'Penjualan Kasir — No. Invoice: INV-20260508-1DC7C4', '2026-05-08 03:58:57'),
(58, 6, 7, 'keluar', 1, 47, 46, 'Penjualan Kasir — No. Invoice: INV-20260508-1DC7C4', '2026-05-08 03:58:57'),
(59, 7, 7, 'keluar', 1, 37, 36, 'Penjualan Kasir — No. Invoice: INV-20260508-1DC7C4', '2026-05-08 03:58:57'),
(60, 1, 7, 'keluar', 1, 15, 14, 'Penjualan Kasir — No. Invoice: INV-20260508-1DC7C4', '2026-05-08 03:58:57'),
(61, 9, 7, 'keluar', 1, 8, 7, 'Penjualan Kasir — No. Invoice: INV-20260508-1DC7C4', '2026-05-08 03:58:57'),
(62, 9, 7, 'keluar', 2, 7, 5, 'Penjualan Kasir — No. Invoice: INV-20260508-E6D875', '2026-05-08 06:06:38'),
(63, 8, 7, 'keluar', 1, 23, 22, 'Penjualan Kasir — No. Invoice: INV-20260508-E6D875', '2026-05-08 06:06:38'),
(64, 10, 7, 'keluar', 2, 2, 0, 'Penjualan Kasir — No. Invoice: INV-20260508-E6D875', '2026-05-08 06:06:38'),
(65, 7, 7, 'keluar', 1, 36, 35, 'Penjualan Kasir — No. Invoice: INV-20260508-E6D875', '2026-05-08 06:06:38'),
(66, 6, 7, 'keluar', 1, 46, 45, 'Penjualan Kasir — No. Invoice: INV-20260508-E6D875', '2026-05-08 06:06:38'),
(67, 8, 7, 'keluar', 2, 22, 20, 'Penjualan Kasir — No. Invoice: INV-20260508-2084FE', '2026-05-08 06:09:06'),
(68, 5, 7, 'keluar', 1, 7, 6, 'Penjualan Kasir — No. Invoice: INV-20260508-2084FE', '2026-05-08 06:09:06'),
(69, 6, 7, 'keluar', 13, 45, 32, 'Penjualan Kasir — No. Invoice: INV-20260508-2084FE', '2026-05-08 06:09:06'),
(70, 6, 7, 'keluar', 10, 32, 22, 'Penjualan Kasir — No. Invoice: INV-20260508-93A109', '2026-05-08 06:12:41'),
(71, 7, 7, 'keluar', 5, 35, 30, 'Penjualan Kasir — No. Invoice: INV-20260508-93A109', '2026-05-08 06:12:41'),
(72, 8, 7, 'keluar', 5, 20, 15, 'Penjualan Kasir — No. Invoice: INV-20260508-93A109', '2026-05-08 06:12:41'),
(73, 9, 7, 'keluar', 5, 5, 0, 'Penjualan Kasir — No. Invoice: INV-20260508-3C8203', '2026-05-08 06:26:43'),
(74, 8, 7, 'keluar', 10, 15, 5, 'Penjualan Kasir — No. Invoice: INV-20260508-3C8203', '2026-05-08 06:26:43'),
(75, 5, 7, 'keluar', 1, 6, 5, 'Penjualan Kasir — No. Invoice: INV-20260508-3C8203', '2026-05-08 06:26:43'),
(76, 6, 7, 'keluar', 6, 22, 16, 'Penjualan Kasir — No. Invoice: INV-20260508-3C8203', '2026-05-08 06:26:43'),
(77, 7, 7, 'keluar', 6, 30, 24, 'Penjualan Kasir — No. Invoice: INV-20260508-3C8203', '2026-05-08 06:26:43'),
(78, 8, 7, 'keluar', 5, 5, 0, 'Penjualan Kasir — No. Invoice: INV-20260508-6272D4', '2026-05-08 09:05:26'),
(79, 5, 7, 'keluar', 2, 5, 3, 'Penjualan Kasir — No. Invoice: INV-20260508-6272D4', '2026-05-08 09:05:26'),
(80, 6, 7, 'keluar', 3, 16, 13, 'Penjualan Kasir — No. Invoice: INV-20260508-6272D4', '2026-05-08 09:05:26'),
(81, 7, 7, 'keluar', 2, 24, 22, 'Penjualan Kasir — No. Invoice: INV-20260508-6272D4', '2026-05-08 09:05:26'),
(82, 6, 7, 'keluar', 1, 13, 12, 'Penjualan Kasir — No. Invoice: INV-20260511-6B5034', '2026-05-11 00:20:06'),
(83, 5, 7, 'keluar', 1, 3, 2, 'Penjualan Kasir — No. Invoice: INV-20260511-6B5034', '2026-05-11 00:20:06'),
(84, 5, 7, 'keluar', 1, 2, 1, 'Penjualan Kasir — No. Invoice: INV-20260511-C0AC87', '2026-05-11 00:20:28'),
(85, 6, 7, 'keluar', 1, 12, 11, 'Penjualan Kasir — No. Invoice: INV-20260511-C0AC87', '2026-05-11 00:20:28'),
(86, 1, 7, 'keluar', 1, 14, 13, 'Penjualan Kasir — No. Invoice: INV-20260511-7DB8C0', '2026-05-11 00:20:39'),
(87, 5, 7, 'keluar', 1, 1, 0, 'Penjualan Kasir — No. Invoice: INV-20260511-7DB8C0', '2026-05-11 00:20:39'),
(88, 1, 1, 'masuk', 1, 13, 14, 'VOID Transaksi ID: 34', '2026-05-11 13:19:59'),
(89, 5, 1, 'masuk', 1, 0, 1, 'VOID Transaksi ID: 34', '2026-05-11 13:19:59'),
(90, 5, 7, 'keluar', 1, 1, 0, 'Penjualan Kasir — No. Invoice: INV-20260511-7E269F', '2026-05-11 13:20:55'),
(91, 6, 7, 'keluar', 2, 11, 9, 'Penjualan Kasir — No. Invoice: INV-20260511-7E269F', '2026-05-11 13:20:55'),
(92, 7, 7, 'keluar', 2, 22, 20, 'Penjualan Kasir — No. Invoice: INV-20260511-7E269F', '2026-05-11 13:20:55'),
(93, 1, 7, 'keluar', 1, 14, 13, 'Penjualan Kasir — No. Invoice: INV-20260511-7E269F', '2026-05-11 13:20:55'),
(94, 6, 7, 'keluar', 1, 9, 8, 'Penjualan Kasir — No. Invoice: INV-20260511-095A92', '2026-05-11 13:24:16'),
(95, 6, 7, 'keluar', 2, 8, 6, 'Penjualan Kasir — No. Invoice: INV-20260511-C6C096', '2026-05-11 13:24:44'),
(96, 7, 7, 'keluar', 2, 20, 18, 'Penjualan Kasir — No. Invoice: INV-20260511-C6C096', '2026-05-11 13:24:44'),
(97, 6, 7, 'keluar', 1, 6, 5, 'Penjualan Kasir — No. Invoice: INV-20260511-A362F0', '2026-05-11 13:26:02'),
(98, 7, 7, 'keluar', 1, 18, 17, 'Penjualan Kasir — No. Invoice: INV-20260511-A362F0', '2026-05-11 13:26:02'),
(99, 6, 7, 'keluar', 1, 5, 4, 'Penjualan Kasir — No. Invoice: INV-20260511-CCE377', '2026-05-11 13:26:20'),
(100, 6, 7, 'keluar', 1, 4, 3, 'Penjualan Kasir — No. Invoice: INV-20260511-59046B', '2026-05-11 13:36:05'),
(101, 7, 7, 'keluar', 1, 17, 16, 'Penjualan Kasir — No. Invoice: INV-20260511-59046B', '2026-05-11 13:36:05');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `sku` varchar(20) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `category_id` int DEFAULT NULL,
  `harga` decimal(10,2) NOT NULL,
  `stok` int DEFAULT '0',
  `satuan` varchar(20) DEFAULT 'Pcs',
  `image` varchar(255) DEFAULT 'default.jpg',
  `exp_date` date DEFAULT NULL,
  `status` enum('aktif','non-aktif') DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `sku`, `nama_produk`, `category_id`, `harga`, `stok`, `satuan`, `image`, `exp_date`, `status`, `created_at`) VALUES
(1, 'kkkk', 'kopii', 2, '21000.00', 13, 'Pcs', 'default.jpg', '2026-05-30', 'aktif', '2026-05-03 05:33:25'),
(5, 'Kue-21', 'Kue Coklat', 3, '17000.00', 0, 'Pcs', '1777809467_Chocolate-Surprice-3.jpg', '2026-05-16', 'aktif', '2026-05-03 11:57:47'),
(6, 'COF-001', 'Americano', 2, '15000.00', 3, 'Pcs', 'americano.jpg', '2026-12-31', 'aktif', '2026-05-03 11:59:45'),
(7, 'COF-002', 'Cappuccino', 2, '18000.00', 16, 'Pcs', 'cappuccino.jpg', '2026-12-31', 'aktif', '2026-05-03 11:59:45'),
(8, 'COF-003', 'Latte', 2, '20000.00', 0, 'Pcs', 'latte.jpg', '2026-12-31', 'aktif', '2026-05-03 11:59:45'),
(9, 'PAS-001', 'Croissant', 3, '18000.00', 0, 'Pcs', 'croissant.jpg', '2026-05-10', 'aktif', '2026-05-03 11:59:45'),
(10, 'BRD-001', 'Roti Cokelat', 1, '12000.00', 0, 'Pcs', 'roti_cokelat.jpg', '2026-05-30', 'aktif', '2026-05-03 11:59:45');

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `end_time` timestamp NULL DEFAULT NULL,
  `starting_cash` decimal(10,2) DEFAULT '0.00',
  `total_tunai` decimal(10,2) DEFAULT '0.00',
  `total_non_tunai` decimal(10,2) DEFAULT '0.00',
  `actual_cash` decimal(10,2) DEFAULT '0.00',
  `variance` decimal(10,2) DEFAULT '0.00',
  `notes` text,
  `status` enum('open','closed') DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`id`, `user_id`, `start_time`, `end_time`, `starting_cash`, `total_tunai`, `total_non_tunai`, `actual_cash`, `variance`, `notes`, `status`) VALUES
(1, 2, '2026-05-05 01:55:13', '2026-05-05 02:00:16', '0.00', '0.00', '0.00', '0.00', '0.00', 'Auto-closed via logout', 'closed'),
(2, 2, '2026-05-05 02:08:19', '2026-05-05 02:09:15', '0.00', '0.00', '0.00', '0.00', '0.00', 'Auto-closed via logout', 'closed'),
(3, 2, '2026-05-05 02:48:16', '2026-05-05 02:54:21', '0.00', '0.00', '0.00', '0.00', '0.00', 'Auto-closed via logout', 'closed'),
(4, 2, '2026-05-05 03:02:17', '2026-05-05 03:03:58', '0.00', '0.00', '0.00', '0.00', '0.00', 'Auto-closed via logout', 'closed'),
(5, 2, '2026-05-05 03:11:20', '2026-05-05 03:16:01', '0.00', '0.00', '0.00', '0.00', '0.00', 'Auto-closed via logout', 'closed'),
(6, 2, '2026-05-06 00:36:05', '2026-05-06 01:19:35', '0.00', '0.00', '0.00', '0.00', '0.00', 'Auto-closed via logout', 'closed'),
(7, 2, '2026-05-06 01:44:02', '2026-05-08 01:35:06', '0.00', '0.00', '0.00', '0.00', '0.00', 'Auto-closed via logout', 'closed'),
(8, 7, '2026-05-08 01:33:23', '2026-05-08 01:33:53', '0.00', '0.00', '0.00', '0.00', '0.00', 'Auto-closed via logout', 'closed'),
(9, 7, '2026-05-08 01:55:58', '2026-05-08 10:04:29', '0.00', '0.00', '0.00', '0.00', '0.00', 'Auto-closed via logout', 'closed'),
(10, 7, '2026-05-08 10:04:36', '2026-05-08 10:18:19', '0.00', '0.00', '0.00', '0.00', '0.00', 'Auto-closed via logout', 'closed'),
(11, 7, '2026-05-08 10:52:22', '2026-05-08 10:55:42', '0.00', '0.00', '0.00', '0.00', '0.00', 'Auto-closed via logout', 'closed'),
(12, 7, '2026-05-08 22:41:56', '2026-05-08 22:42:14', '0.00', '0.00', '0.00', '0.00', '0.00', 'Auto-closed via logout', 'closed'),
(13, 7, '2026-05-11 00:17:16', '2026-05-11 00:21:11', '0.00', '0.00', '0.00', '0.00', '0.00', 'Auto-closed via logout', 'closed'),
(14, 7, '2026-05-11 13:20:23', NULL, '0.00', '0.00', '0.00', '0.00', '0.00', NULL, 'open');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `transaction_id` varchar(20) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `shift_id` int DEFAULT NULL,
  `total_harga` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'success',
  `status_verifikasi` enum('pending','requested','verified','synced') DEFAULT 'pending',
  `bayar` decimal(10,2) DEFAULT NULL,
  `kembali` decimal(10,2) DEFAULT NULL,
  `metode_bayar` enum('tunai','qris','transfer') DEFAULT 'tunai',
  `catatan` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `transaction_id`, `user_id`, `shift_id`, `total_harga`, `status`, `status_verifikasi`, `bayar`, `kembali`, `metode_bayar`, `catatan`, `created_at`) VALUES
(1, 'INV-20260503-D2E9EC', 1, NULL, '21000.00', 'success', 'pending', '30000.00', '9000.00', 'tunai', '', '2026-05-03 11:25:49'),
(2, 'INV-20260503-691806', 1, NULL, '21000.00', 'success', 'pending', '50000.00', '29000.00', 'tunai', '', '2026-05-03 11:43:50'),
(3, 'INV-20260503-38EBCC', 1, NULL, '42000.00', 'success', 'pending', '50000.00', '8000.00', 'tunai', '', '2026-05-03 11:50:43'),
(4, 'INV-20260503-90FEF8', 1, NULL, '210000.00', 'success', 'pending', '250000.00', '40000.00', 'tunai', 'lnsdc', '2026-05-03 11:54:01'),
(5, 'INV-20260503-31C30A', 1, NULL, '138000.00', 'success', 'pending', '138000.00', '0.00', 'qris', '', '2026-05-03 12:00:51'),
(8, 'INV-20260503-ACDC9D', 1, NULL, '18000.00', 'success', 'pending', '20000.00', '2000.00', 'tunai', '', '2026-05-03 12:15:54'),
(9, 'INV-20260504-63A031', 1, NULL, '103000.00', 'success', 'pending', '150000.00', '47000.00', 'tunai', 'okok', '2026-05-04 13:33:42'),
(10, 'INV-20260504-C62EC7', 1, NULL, '12000.00', 'success', 'pending', '20000.00', '8000.00', 'tunai', '', '2026-05-04 13:53:16'),
(11, 'INV-20260504-5BE4EC', 1, NULL, '38000.00', 'success', 'pending', '50000.00', '12000.00', 'tunai', '', '2026-05-04 21:00:37'),
(12, 'INV-20260504-BBDF12', 1, NULL, '30000.00', 'success', 'pending', '50000.00', '20000.00', 'tunai', '', '2026-05-04 21:00:59'),
(13, 'INV-20260504-494E4B', 1, NULL, '21000.00', 'success', 'pending', '25000.00', '4000.00', 'tunai', '', '2026-05-04 21:05:40'),
(14, 'INV-20260505-8BD2D0', 2, 2, '56000.00', 'success', 'pending', '100000.00', '44000.00', 'tunai', '', '2026-05-05 02:08:40'),
(16, 'INV-20260505-EB0498', 2, 3, '87000.00', 'void', 'pending', '87000.00', '0.00', 'qris', 'kiw', '2026-05-05 02:51:42'),
(17, 'INV-20260505-B683C3', 2, 3, '12000.00', 'void', 'pending', '20000.00', '8000.00', 'tunai', '', '2026-05-05 02:52:11'),
(18, 'INV-20260505-A20C3E', 2, 4, '18000.00', 'success', 'pending', '18000.00', '0.00', 'transfer', '', '2026-05-05 03:02:34'),
(19, 'INV-20260505-551C54', 2, 4, '20000.00', 'success', 'pending', '20000.00', '0.00', 'transfer', '', '2026-05-05 03:03:33'),
(20, 'INV-20260505-3DB99E', 2, 5, '20000.00', 'success', 'pending', '20000.00', '0.00', 'transfer', '', '2026-05-05 03:12:03'),
(21, 'INV-20260506-01EC4F', 2, 6, '116000.00', 'success', 'pending', '120000.00', '4000.00', 'tunai', '', '2026-05-06 00:47:44'),
(22, 'INV-20260506-A63BEE', 2, 6, '51000.00', 'success', 'pending', '55000.00', '4000.00', 'tunai', '', '2026-05-06 01:03:06'),
(23, 'INV-20260506-C6F3E7', 2, 6, '240000.00', 'success', 'synced', '250000.00', '10000.00', 'tunai', '', '2026-05-06 01:15:56'),
(24, 'INV-20260506-923E44', 2, 7, '38000.00', 'success', 'synced', '50000.00', '12000.00', 'tunai', '', '2026-05-06 01:44:25'),
(25, 'INV-20260508-35A66C', 7, 9, '68000.00', 'success', 'synced', '100000.00', '32000.00', 'tunai', '', '2026-05-08 03:10:59'),
(26, 'INV-20260508-1DC7C4', 7, 9, '109000.00', 'success', 'synced', '110000.00', '1000.00', 'tunai', '', '2026-05-08 03:58:57'),
(27, 'INV-20260508-E6D875', 7, 9, '113000.00', 'success', 'synced', '113000.00', '0.00', 'qris', '', '2026-05-08 06:06:38'),
(28, 'INV-20260508-2084FE', 7, 9, '252000.00', 'success', 'synced', '252000.00', '0.00', 'transfer', '', '2026-05-08 06:09:06'),
(29, 'INV-20260508-93A109', 7, 9, '340000.00', 'success', 'synced', '340000.00', '0.00', 'transfer', '', '2026-05-08 06:12:41'),
(30, 'INV-20260508-3C8203', 7, 9, '505000.00', 'success', 'synced', '505000.00', '0.00', 'transfer', '', '2026-05-08 06:26:43'),
(31, 'INV-20260508-6272D4', 7, 9, '215000.00', 'success', 'synced', '215000.00', '0.00', 'transfer', '', '2026-05-08 09:05:26'),
(32, 'INV-20260511-6B5034', 7, 13, '32000.00', 'success', 'synced', '50000.00', '18000.00', 'tunai', '', '2026-05-11 00:20:06'),
(33, 'INV-20260511-C0AC87', 7, 13, '32000.00', 'success', 'synced', '32000.00', '0.00', 'transfer', '', '2026-05-11 00:20:28'),
(34, 'INV-20260511-7DB8C0', 7, 13, '38000.00', 'void', 'synced', '38000.00', '0.00', 'transfer', '', '2026-05-11 00:20:39'),
(35, 'INV-20260511-7E269F', 7, 14, '104000.00', 'success', 'requested', '104000.00', '0.00', 'transfer', '', '2026-05-11 13:20:55'),
(36, 'INV-20260511-095A92', 7, 14, '15000.00', 'success', 'requested', '15000.00', '0.00', 'transfer', '', '2026-05-11 13:24:16'),
(37, 'INV-20260511-C6C096', 7, 14, '66000.00', 'success', 'pending', '66000.00', '0.00', 'transfer', '', '2026-05-11 13:24:44'),
(38, 'INV-20260511-A362F0', 7, 14, '33000.00', 'success', 'pending', '33000.00', '0.00', 'transfer', '', '2026-05-11 13:26:02'),
(39, 'INV-20260511-CCE377', 7, 14, '15000.00', 'success', 'pending', '15000.00', '0.00', 'transfer', '', '2026-05-11 13:26:20'),
(40, 'INV-20260511-59046B', 7, 14, '33000.00', 'success', 'pending', '33000.00', '0.00', 'transfer', '', '2026-05-11 13:36:05');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_details`
--

CREATE TABLE `transaction_details` (
  `id` int NOT NULL,
  `transaction_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `jumlah` int DEFAULT NULL,
  `harga_satuan` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaction_details`
--

INSERT INTO `transaction_details` (`id`, `transaction_id`, `product_id`, `jumlah`, `harga_satuan`) VALUES
(1, 1, 1, 1, '21000.00'),
(2, 2, 1, 1, '21000.00'),
(3, 3, 1, 2, '21000.00'),
(4, 4, 1, 10, '21000.00'),
(5, 5, 5, 2, '17000.00'),
(6, 5, 8, 1, '20000.00'),
(7, 5, 9, 1, '18000.00'),
(8, 5, 10, 1, '12000.00'),
(9, 5, 7, 1, '18000.00'),
(10, 5, 6, 1, '15000.00'),
(11, 5, 1, 1, '21000.00'),
(12, 8, 9, 1, '18000.00'),
(13, 9, 8, 1, '20000.00'),
(14, 9, 9, 1, '18000.00'),
(15, 9, 10, 1, '12000.00'),
(16, 9, 1, 1, '21000.00'),
(17, 9, 5, 1, '17000.00'),
(18, 9, 6, 1, '15000.00'),
(19, 10, 10, 1, '12000.00'),
(20, 11, 9, 1, '18000.00'),
(21, 11, 8, 1, '20000.00'),
(22, 12, 9, 1, '18000.00'),
(23, 12, 10, 1, '12000.00'),
(24, 13, 1, 1, '21000.00'),
(25, 14, 1, 1, '21000.00'),
(26, 14, 5, 1, '17000.00'),
(27, 14, 9, 1, '18000.00'),
(30, 16, 1, 1, '21000.00'),
(31, 16, 10, 1, '12000.00'),
(32, 16, 9, 3, '18000.00'),
(33, 17, 10, 1, '12000.00'),
(34, 18, 9, 1, '18000.00'),
(35, 19, 8, 1, '20000.00'),
(36, 20, 8, 1, '20000.00'),
(37, 21, 8, 4, '20000.00'),
(38, 21, 9, 2, '18000.00'),
(39, 22, 7, 2, '18000.00'),
(40, 22, 6, 1, '15000.00'),
(41, 23, 10, 20, '12000.00'),
(42, 24, 9, 1, '18000.00'),
(43, 24, 8, 1, '20000.00'),
(44, 25, 10, 1, '12000.00'),
(45, 25, 9, 2, '18000.00'),
(46, 25, 8, 1, '20000.00'),
(47, 26, 8, 1, '20000.00'),
(48, 26, 5, 1, '17000.00'),
(49, 26, 6, 1, '15000.00'),
(50, 26, 7, 1, '18000.00'),
(51, 26, 1, 1, '21000.00'),
(52, 26, 9, 1, '18000.00'),
(53, 27, 9, 2, '18000.00'),
(54, 27, 8, 1, '20000.00'),
(55, 27, 10, 2, '12000.00'),
(56, 27, 7, 1, '18000.00'),
(57, 27, 6, 1, '15000.00'),
(58, 28, 8, 2, '20000.00'),
(59, 28, 5, 1, '17000.00'),
(60, 28, 6, 13, '15000.00'),
(61, 29, 6, 10, '15000.00'),
(62, 29, 7, 5, '18000.00'),
(63, 29, 8, 5, '20000.00'),
(64, 30, 9, 5, '18000.00'),
(65, 30, 8, 10, '20000.00'),
(66, 30, 5, 1, '17000.00'),
(67, 30, 6, 6, '15000.00'),
(68, 30, 7, 6, '18000.00'),
(69, 31, 8, 5, '20000.00'),
(70, 31, 5, 2, '17000.00'),
(71, 31, 6, 3, '15000.00'),
(72, 31, 7, 2, '18000.00'),
(73, 32, 6, 1, '15000.00'),
(74, 32, 5, 1, '17000.00'),
(75, 33, 5, 1, '17000.00'),
(76, 33, 6, 1, '15000.00'),
(77, 34, 1, 1, '21000.00'),
(78, 34, 5, 1, '17000.00'),
(79, 35, 5, 1, '17000.00'),
(80, 35, 6, 2, '15000.00'),
(81, 35, 7, 2, '18000.00'),
(82, 35, 1, 1, '21000.00'),
(83, 36, 6, 1, '15000.00'),
(84, 37, 6, 2, '15000.00'),
(85, 37, 7, 2, '18000.00'),
(86, 38, 6, 1, '15000.00'),
(87, 38, 7, 1, '18000.00'),
(88, 39, 6, 1, '15000.00'),
(89, 40, 6, 1, '15000.00'),
(90, 40, 7, 1, '18000.00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `role` enum('admin','kasir','gudang') NOT NULL,
  `status` enum('aktif','non-aktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `status`) VALUES
(1, 'admin', '$2y$10$Rw388A6t6eS1kQe0QjeRL..Wth830vUz4fZk.bH9kZFddaZIVRFmG', 'Administrator TEFAaa', 'admin', 'aktif'),
(2, 'kasir_tefa', '$2y$10$Rw388A6t6eS1kQe0QjeRL..Wth830vUz4fZk.bH9kZFddaZIVRFmG', 'Kasir TEFA Bakery', 'kasir', 'aktif'),
(3, 'kepala_gudang', '$2y$10$Rw388A6t6eS1kQe0QjeRL..Wth830vUz4fZk.bH9kZFddaZIVRFmG', 'Bapak Gudang', 'gudang', 'aktif'),
(5, 'admin_tefa', '$2y$10$Rw388A6t6eS1kQe0QjeRL..Wth830vUz4fZk.bH9kZFddaZIVRFmG', 'Administrator Utama', 'admin', 'aktif'),
(6, 'ADMIN_RAFIF', '$2y$10$c5EOUg.tgMR6h5MrKk8iD.Q3.7w6scK/ZAE0Ws8NVATw3OCg4RWNa', 'm Aziz', 'admin', 'aktif'),
(7, 'kasir', '$2y$10$0/BehJOYIgtkV3i6/oDbOueNvE8qsSSFYlaHr16PgzUu.qsn88IVO', 'kasir tefa', 'kasir', 'aktif');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `transaction_details`
--
ALTER TABLE `transaction_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD CONSTRAINT `inventory_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
