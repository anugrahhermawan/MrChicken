-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table mrchicken.detail_transaksi
CREATE TABLE IF NOT EXISTS `detail_transaksi` (
  `id_detail` int NOT NULL AUTO_INCREMENT,
  `id_transaksi` int NOT NULL,
  `id_produk` int NOT NULL,
  `jumlah_berat_kg` decimal(5,2) NOT NULL,
  `harga_satuan` int NOT NULL,
  `subtotal` int NOT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `id_transaksi` (`id_transaksi`),
  KEY `id_produk` (`id_produk`),
  CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE,
  CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table mrchicken.detail_transaksi: ~21 rows (approximately)
INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `id_produk`, `jumlah_berat_kg`, `harga_satuan`, `subtotal`) VALUES
	(1, 1, 1, 1.00, 50000, 50000),
	(2, 1, 2, 2.00, 48000, 96000),
	(3, 1, 2, 0.50, 48000, 24000),
	(4, 2, 1, 0.50, 50000, 25000),
	(22, 19, 2, 1.50, 48000, 72000),
	(23, 20, 2, 4.00, 48000, 192000),
	(24, 20, 3, 2.30, 35000, 80500),
	(25, 21, 1, 7.00, 50000, 350000),
	(26, 22, 4, 6.00, 25000, 150000),
	(27, 22, 1, 9.00, 50000, 450000),
	(28, 23, 4, 4.00, 25000, 100000),
	(29, 24, 4, 10.00, 25000, 250000),
	(30, 25, 3, 6.00, 35000, 210000),
	(31, 26, 2, 5.00, 48000, 240000),
	(32, 27, 2, 30.00, 48000, 1440000),
	(33, 28, 1, 3.00, 50000, 150000),
	(34, 28, 4, 2.00, 25000, 50000),
	(35, 28, 1, 20.00, 50000, 1000000),
	(36, 29, 1, 4.00, 50000, 200000),
	(37, 29, 4, 4.00, 25000, 100000),
	(38, 30, 2, 3.00, 48000, 144000);

-- Dumping structure for table mrchicken.hutang
CREATE TABLE IF NOT EXISTS `hutang` (
  `id_hutang` int NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int NOT NULL,
  `id_transaksi` int NOT NULL,
  `jumlah_hutang` int NOT NULL,
  `sisa_hutang` int NOT NULL,
  `status` enum('Aktif','Lunas','Write-Off') DEFAULT 'Aktif',
  `tanggal_hutang` date NOT NULL,
  `due_date` date DEFAULT NULL,
  PRIMARY KEY (`id_hutang`),
  KEY `id_pelanggan` (`id_pelanggan`),
  KEY `id_transaksi` (`id_transaksi`),
  CONSTRAINT `hutang_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`),
  CONSTRAINT `hutang_ibfk_2` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table mrchicken.hutang: ~5 rows (approximately)
INSERT INTO `hutang` (`id_hutang`, `id_pelanggan`, `id_transaksi`, `jumlah_hutang`, `sisa_hutang`, `tanggal_hutang`) VALUES
	(1, 3, 1, 170000, 0, '2026-07-07'),
	(5, 1, 22, 600000, 600000, '2026-07-07'),
	(6, 2, 27, 1440000, 1440000, '2026-07-08'),
	(7, 3, 29, 300000, 300000, '2026-07-08'),
	(8, 4, 30, 144000, 144000, '2026-07-13');

-- Dumping structure for table mrchicken.pelanggan
CREATE TABLE IF NOT EXISTS `pelanggan` (
  `id_pelanggan` int NOT NULL AUTO_INCREMENT,
  `nama_pelanggan` varchar(150) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `saldo_hutang` int DEFAULT '0',
  `credit_limit` bigint DEFAULT '0',
  PRIMARY KEY (`id_pelanggan`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping structure for table mrchicken.pembayaran_hutang
CREATE TABLE IF NOT EXISTS `pembayaran_hutang` (
  `id_pembayaran` INT AUTO_INCREMENT PRIMARY KEY,
  `id_hutang` INT NOT NULL,
  `nominal_bayar` INT NOT NULL,
  `tanggal_bayar` DATETIME NOT NULL,
  `created_by` INT NOT NULL,
  CONSTRAINT `fk_pembayaran_hutang_hutang` FOREIGN KEY (`id_hutang`) REFERENCES `hutang`(`id_hutang`) ON DELETE CASCADE,
  CONSTRAINT `fk_pembayaran_hutang_users` FOREIGN KEY (`created_by`) REFERENCES `users`(`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table mrchicken.pelanggan: ~4 rows (approximately)
INSERT INTO `pelanggan` (`id_pelanggan`, `nama_pelanggan`, `no_hp`, `alamat`, `saldo_hutang`) VALUES
	(1, 'Budi (Pelanggan A)', '08123456789', 'Jl. Merdeka No. 1, Kota A', 600000),
	(2, 'Ani (Pelanggan B)', '08987654321', 'Jl. Sudirman No. 2, Kota B', 1440000),
	(3, 'acop', '08222193631982', 'jl garuda sakti', 300000),
	(4, 'farrel', '08213233145', 'jl garuda sakti', 144000);

-- Dumping structure for table mrchicken.produk
CREATE TABLE IF NOT EXISTS `produk` (
  `id_produk` int NOT NULL AUTO_INCREMENT,
  `nama_produk` varchar(100) NOT NULL,
  `harga_per_kg` int NOT NULL,
  `satuan` varchar(10) DEFAULT 'Kg',
  PRIMARY KEY (`id_produk`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table mrchicken.produk: ~4 rows (approximately)
INSERT INTO `produk` (`id_produk`, `nama_produk`, `harga_per_kg`, `satuan`) VALUES
	(1, 'Dada Fillet', 50000, 'Kg'),
	(2, 'Paha Fillet', 48000, 'Kg'),
	(3, 'Sayap Fillet', 35000, 'Kg'),
	(4, 'Ceker Fillet', 25000, 'Kg');

-- Dumping structure for table mrchicken.stok
CREATE TABLE IF NOT EXISTS `stok` (
  `id_stok` int NOT NULL AUTO_INCREMENT,
  `id_produk` int NOT NULL,
  `jumlah_kg` decimal(7,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id_stok`),
  KEY `id_produk` (`id_produk`),
  CONSTRAINT `stok_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table mrchicken.stok: ~4 rows (approximately)
INSERT INTO `stok` (`id_stok`, `id_produk`, `jumlah_kg`) VALUES
	(1, 1, 54.50),
	(2, 2, 51.50),
	(3, 3, 41.70),
	(4, 4, 4.00);

-- Dumping structure for table mrchicken.transaksi
CREATE TABLE IF NOT EXISTS `transaksi` (
  `id_transaksi` int NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int NOT NULL,
  `id_user` int NOT NULL,
  `tanggal` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `waktu` time NOT NULL,
  `slot_waktu` enum('Pagi','Sore') NOT NULL,
  `total_berat_akumulatif` decimal(6,2) NOT NULL,
  `metode_pembayaran` enum('Lunas','Hutang') NOT NULL,
  `status_pengiriman` enum('Pending','Selesai','Pre-Order') NOT NULL DEFAULT 'Pending',
  `total_harga` int NOT NULL,
  PRIMARY KEY (`id_transaksi`),
  KEY `id_pelanggan` (`id_pelanggan`),
  KEY `id_user` (`id_user`),
  CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`),
  CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table mrchicken.transaksi: ~14 rows (approximately)
INSERT INTO `transaksi` (`id_transaksi`, `id_pelanggan`, `id_user`, `tanggal`, `waktu`, `slot_waktu`, `total_berat_akumulatif`, `metode_pembayaran`, `status_pengiriman`, `total_harga`) VALUES
	(1, 3, 1, '2026-07-07', '02:05:45', 'Pagi', 3.50, 'Hutang', 'Selesai', 170000),
	(2, 3, 2, '2026-07-07', '02:07:48', 'Pagi', 0.50, 'Lunas', 'Selesai', 25000),
	(19, 1, 1, '2026-07-07', '04:12:05', 'Pagi', 1.50, 'Lunas', 'Selesai', 72000),
	(20, 2, 1, '2026-07-07', '04:51:09', 'Sore', 6.30, 'Lunas', 'Selesai', 272500),
	(21, 2, 1, '2026-07-07', '04:51:17', 'Pagi', 7.00, 'Lunas', 'Selesai', 350000),
	(22, 1, 1, '2026-07-07', '05:26:41', 'Sore', 15.00, 'Hutang', 'Selesai', 600000),
	(23, 2, 1, '2026-07-07', '05:35:35', 'Pagi', 4.00, 'Lunas', 'Selesai', 100000),
	(24, 3, 1, '2026-07-07', '05:42:32', 'Pagi', 10.00, 'Lunas', 'Selesai', 250000),
	(25, 1, 1, '2026-07-07', '05:42:42', 'Pagi', 6.00, 'Lunas', 'Selesai', 210000),
	(26, 1, 1, '2026-07-07', '05:42:51', 'Pagi', 5.00, 'Lunas', 'Selesai', 240000),
	(27, 2, 1, '2026-07-08', '09:19:02', 'Sore', 30.00, 'Hutang', 'Selesai', 1440000),
	(28, 1, 1, '2026-07-08', '09:20:12', 'Pagi', 25.00, 'Lunas', 'Selesai', 1200000),
	(29, 3, 1, '2026-07-08', '09:21:18', 'Pagi', 8.00, 'Hutang', 'Selesai', 300000),
	(30, 4, 1, '2026-07-13', '04:35:56', 'Sore', 3.00, 'Hutang', 'Selesai', 144000);

-- Dumping structure for table mrchicken.users
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_pengguna` varchar(100) NOT NULL,
  `role` enum('Owner','Karyawan') NOT NULL,
  `status_aktif` tinyint DEFAULT '1',
  `is_deleted` tinyint DEFAULT '0',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table mrchicken.users: ~3 rows (approximately)
INSERT INTO `users` (`id_user`, `username`, `password`, `nama_pengguna`, `role`, `status_aktif`, `is_deleted`) VALUES
	(1, 'owner', '$2y$10$FwU/.VXvv35NZ8IR5ADm8.qYBNQKX9JctQVv7KBKDDyQmR5HYp5I.', 'Owner', 'Owner', 1, 0),
	(2, 'kasir', '$2y$10$mXzPI9xXFvl4HNHnx8BjCOHRHMw5dCz45rScUXyRwbVlmZmgttHBK', 'Siti Kasir', 'Karyawan', 1, 1),
	(3, 'kasir1', '$2y$10$h98pdYcPPZ0EelexBxrXO.69hzBx4LgSwzuh8ae2MitI2L8kYIoGu', 'acop', 'Karyawan', 1, 0);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
