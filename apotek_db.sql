-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 01, 2026 at 06:14 AM
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
-- Database: `apotek_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id` int NOT NULL,
  `pesanan_id` int NOT NULL,
  `obat_id` int NOT NULL,
  `nama_obat` varchar(200) NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `jumlah` int NOT NULL,
  `subtotal` decimal(14,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id`, `pesanan_id`, `obat_id`, `nama_obat`, `harga_satuan`, `jumlah`, `subtotal`) VALUES
(23, 10, 6, 'Betadine 30ml', '14999.00', 1, '14999.00'),
(24, 10, 3, 'Vitamin C 1000mg', '3500.00', 4, '14000.00'),
(25, 11, 8, 'Bodrex', '2000.00', 5, '10000.00'),
(26, 12, 7, 'OBH Combi', '12000.00', 5, '60000.00');

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id` int NOT NULL,
  `transaksi_id` int NOT NULL,
  `obat_id` int NOT NULL,
  `nama_obat` varchar(200) NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `jumlah` int NOT NULL,
  `subtotal` decimal(14,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id`, `transaksi_id`, `obat_id`, `nama_obat`, `harga_satuan`, `jumlah`, `subtotal`) VALUES
(1, 1, 6, 'Betadine 30ml', '14999.00', 1, '14999.00'),
(2, 2, 8, 'Bodrex', '2000.00', 3, '6000.00'),
(3, 2, 3, 'Vitamin C 1000mg', '3500.00', 1, '3500.00'),
(4, 3, 10, 'Diapet', '4000.00', 3, '12000.00'),
(5, 3, 9, 'Sangobion', '6000.00', 1, '6000.00'),
(6, 3, 8, 'Bodrex', '2000.00', 5, '10000.00'),
(12, 8, 6, 'Betadine 30ml', '14999.00', 1, '14999.00'),
(13, 8, 3, 'Vitamin C 1000mg', '3500.00', 4, '14000.00'),
(14, 9, 8, 'Bodrex', '2000.00', 5, '10000.00'),
(15, 10, 7, 'OBH Combi', '12000.00', 5, '60000.00'),
(16, 11, 1, 'Amoxicilin 500mg', '2500.00', 1, '2500.00');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_obat`
--

CREATE TABLE `kategori_obat` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori_obat`
--

INSERT INTO `kategori_obat` (`id`, `nama`, `created_at`) VALUES
(1, 'Antibiotik', '2026-04-20 07:54:18'),
(2, 'Analgesik', '2026-04-20 07:54:18'),
(3, 'Vitamin & Suplemen', '2026-04-20 07:54:18'),
(4, 'Antasida', '2026-04-20 07:54:18'),
(5, 'Antihistamin', '2026-04-20 07:54:18'),
(6, 'Obat Luar', '2026-04-20 07:54:18'),
(7, 'Obat Batuk', '2026-04-20 09:10:45'),
(8, 'Obat Diare', '2026-04-20 09:24:35');

-- --------------------------------------------------------

--
-- Table structure for table `obat`
--

CREATE TABLE `obat` (
  `id` int NOT NULL,
  `kode_obat` varchar(20) NOT NULL,
  `nama` varchar(200) NOT NULL,
  `kategori_id` int DEFAULT NULL,
  `satuan` varchar(30) NOT NULL DEFAULT 'tablet',
  `harga_beli` decimal(12,2) NOT NULL DEFAULT '0.00',
  `harga_jual` decimal(12,2) NOT NULL DEFAULT '0.00',
  `stok` int NOT NULL DEFAULT '0',
  `stok_minimum` int NOT NULL DEFAULT '10',
  `tanggal_expired` date DEFAULT NULL,
  `deskripsi` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `obat`
--

INSERT INTO `obat` (`id`, `kode_obat`, `nama`, `kategori_id`, `satuan`, `harga_beli`, `harga_jual`, `stok`, `stok_minimum`, `tanggal_expired`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1, 'OBT-001', 'Amoxicilin 500mg', 1, 'kapsul', '1200.00', '2500.00', 7, 20, '2026-12-31', 'Antibiotik spektrum luas', '2026-04-20 08:45:21', '2026-05-01 05:35:19'),
(2, 'OBT-002', 'Paracetamol 500mg', 2, 'tablet', '300.00', '800.00', 500, 50, '2027-06-30', 'Analgestik dan antipiretik', '2026-04-20 08:46:33', '2026-04-21 14:10:28'),
(3, 'OBT-003', 'Vitamin C 1000mg', 3, 'tablet', '1500.00', '3500.00', 193, 30, '2027-01-31', 'Suplemen imun harian', '2026-04-20 08:48:42', '2026-05-01 03:36:16'),
(4, 'OBT-004', 'Antasida Doen', 4, 'tablet', '500.00', '1200.00', 6, 15, '2026-09-30', 'Obat mag dan asam lambung', '2026-04-20 08:50:45', '2026-04-28 07:46:13'),
(5, 'OBT-005', 'Cetirizine 10mg', 5, 'tablet', '800.00', '2000.00', 100, 20, '2026-11-30', 'Antihistamin untuk alergi', '2026-04-20 08:52:20', '2026-04-21 14:10:28'),
(6, 'OBT-006', 'Betadine 30ml', 6, 'botol', '8000.00', '14999.00', 41, 10, '2027-03-31', 'Antiseptik luka luar', '2026-04-20 08:53:10', '2026-05-01 03:36:16'),
(7, 'OBT-007', 'OBH Combi', 7, 'botol', '8000.00', '12000.00', 120, 10, '2026-06-19', 'Sirup untuk meredakan batuk berdahak', '2026-04-20 09:12:07', '2026-05-01 05:29:24'),
(8, 'OBT-008', 'Bodrex', 2, 'tablet', '1000.00', '2000.00', 32, 20, '2026-06-30', 'Obat sakit kepala dan demam', '2026-04-20 09:14:48', '2026-05-01 03:40:13'),
(9, 'OBT-009', 'Sangobion', 3, 'kapsul', '3000.00', '6000.00', 94, 30, '2026-08-24', 'Suplemen penambah darah', '2026-04-20 09:23:51', '2026-04-27 16:47:33'),
(10, 'OBT-010', 'Diapet', 8, 'tablet', '2000.00', '4000.00', 114, 50, '2027-06-30', 'Obat untuk mengatasi diare', '2026-04-20 09:25:35', '2026-04-27 16:47:33');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int NOT NULL,
  `kode_pesanan` varchar(30) NOT NULL,
  `user_id` int NOT NULL,
  `metode_bayar` enum('transfer','cash') NOT NULL DEFAULT 'cash',
  `status` enum('menunggu','diproses','siap_diambil','selesai','dibatalkan') DEFAULT 'menunggu',
  `total_harga` decimal(14,2) NOT NULL DEFAULT '0.00',
  `catatan` text,
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id`, `kode_pesanan`, `user_id`, `metode_bayar`, `status`, `total_harga`, `catatan`, `bukti_transfer`, `created_at`, `updated_at`) VALUES
(10, 'PSN-20260501-0B297', 3, 'transfer', 'selesai', '28999.00', '', NULL, '2026-05-01 03:36:16', '2026-05-01 03:37:47'),
(11, 'PSN-20260501-DC848', 3, 'cash', 'selesai', '10000.00', '', NULL, '2026-05-01 03:40:13', '2026-05-01 03:43:57'),
(12, 'PSN-20260501-92EE5', 4, 'transfer', 'selesai', '60000.00', '', NULL, '2026-05-01 05:25:45', '2026-05-01 05:27:14');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int NOT NULL,
  `kode_transaksi` varchar(30) NOT NULL,
  `nama_pembeli` varchar(100) NOT NULL,
  `total_harga` decimal(14,2) NOT NULL DEFAULT '0.00',
  `bayar` decimal(14,2) NOT NULL DEFAULT '0.00',
  `kembalian` decimal(14,2) NOT NULL DEFAULT '0.00',
  `catatan` text,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `kode_transaksi`, `nama_pembeli`, `total_harga`, `bayar`, `kembalian`, `catatan`, `user_id`, `created_at`) VALUES
(1, 'TRX-20260420-E2925', 'Ibnu Aziiz', '14999.00', '20000.00', '5001.00', '', 1, '2026-04-20 08:56:30'),
(2, 'TRX-20260420-CEBA1', 'Therta', '9500.00', '10000.00', '500.00', '', 2, '2026-04-20 09:19:08'),
(3, 'TRX-20260420-298AF', 'Bintang', '28000.00', '50000.00', '22000.00', 'Diminum setelah makan', 2, '2026-04-20 09:27:14'),
(8, 'TRX-20260501-B8816', 'Taqi Hamizan', '28999.00', '28999.00', '0.00', 'Pesanan Online: PSN-20260501-0B297', 2, '2026-05-01 03:37:47'),
(9, 'TRX-20260501-D8330', 'Taqi Hamizan', '10000.00', '10000.00', '0.00', 'Pesanan Online: PSN-20260501-DC848', 2, '2026-05-01 03:43:57'),
(10, 'TRX-20260501-26FA4', 'Haikal', '60000.00', '60000.00', '0.00', 'Pesanan Online: PSN-20260501-92EE5', 2, '2026-05-01 05:27:14'),
(11, 'TRX-20260501-7A605', 'Irsyad', '2500.00', '5000.00', '2500.00', '', 2, '2026-05-01 05:35:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','apoteker','customer') DEFAULT 'apoteker',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `no_hp`, `password`, `role`, `created_at`) VALUES
(1, 'Admin Apotek', 'admin@apotek.com', NULL, 'admin123', 'admin', '2026-04-20 08:31:57'),
(2, 'Agustinus Janssen', 'agustinusjanssen@gmail.com', NULL, 'Adminjans24', 'apoteker', '2026-04-20 08:35:31'),
(3, 'Taqi Hamizan', 'taqi@gmail.com', '0812345678', 'taqi123', 'customer', '2026-04-21 10:33:45'),
(4, 'Haikal', 'haikal@gmail.com', '08987654321', 'haikal123', 'customer', '2026-04-27 16:48:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesanan_id` (`pesanan_id`),
  ADD KEY `obat_id` (`obat_id`);

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `obat_id` (`obat_id`);

--
-- Indexes for table `kategori_obat`
--
ALTER TABLE `kategori_obat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `obat`
--
ALTER TABLE `obat`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_obat` (`kode_obat`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_pesanan` (`kode_pesanan`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `kategori_obat`
--
ALTER TABLE `kategori_obat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `obat`
--
ALTER TABLE `obat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`obat_id`) REFERENCES `obat` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`obat_id`) REFERENCES `obat` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `obat`
--
ALTER TABLE `obat`
  ADD CONSTRAINT `obat_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_obat` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
