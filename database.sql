-- ============================================================
-- DATABASE HARINFOOD POS LITE (FULL FITUR)
-- ============================================================

CREATE DATABASE IF NOT EXISTS `harinfood_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `harinfood_db`;

-- 1. TABEL KATEGORI (Makanan & Minuman)
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `categories` (`id`, `nama`) VALUES
(1, 'Makanan'),
(2, 'Minuman');

-- 2. TABEL PRODUK / MENU
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kategori_id` INT NOT NULL,
  `nama` VARCHAR(150) NOT NULL,
  `harga` INT NOT NULL DEFAULT 0,
  `harga_modal` INT NOT NULL DEFAULT 0,
  `stok` INT NOT NULL DEFAULT 0,
  `foto` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`kategori_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `products` (`id`, `kategori_id`, `nama`, `harga`, `harga_modal`, `stok`, `foto`) VALUES
(1, 1, 'Nasi Goreng Spesial Seafood', 25000, 15000, 45, 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=500&q=80'),
(2, 1, 'Ayam Geprek Sambal Bawang', 20000, 13000, 30, 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=500&q=80'),
(3, 1, 'Risoles Daging Ayam (Isi 3)', 15000, 8000, 20, 'https://images.unsplash.com/photo-1528825871115-3581a5387919?auto=format&fit=crop&w=500&q=80'),
(4, 2, 'Es Teh Manis Jumbo', 5000, 2000, 100, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=500&q=80'),
(5, 2, 'Es Kopi Susu Aren', 15000, 8000, 50, 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=500&q=80');

-- 3. TABEL TRANSAKSI KASIR
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_code` VARCHAR(50) NOT NULL UNIQUE,
  `tanggal` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `nama_pelanggan` VARCHAR(100) DEFAULT 'Pelanggan',
  `nomor_meja` VARCHAR(50) DEFAULT 'Takeaway',
  `total_harga` INT NOT NULL DEFAULT 0,
  `diskon` INT NOT NULL DEFAULT 0,
  `total_akhir` INT NOT NULL DEFAULT 0,
  `nominal_bayar` INT NOT NULL DEFAULT 0,
  `kembalian` INT NOT NULL DEFAULT 0,
  `kasir_nama` VARCHAR(100) DEFAULT 'Kasir Utama'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `transaction_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `transaction_id` INT NOT NULL,
  `product_id` INT DEFAULT NULL,
  `product_nama` VARCHAR(150) NOT NULL,
  `harga` INT NOT NULL DEFAULT 0,
  `harga_modal` INT NOT NULL DEFAULT 0,
  `qty` INT NOT NULL DEFAULT 1,
  `subtotal` INT NOT NULL DEFAULT 0,
  FOREIGN KEY (`transaction_id`) REFERENCES `transactions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. TABEL PESANAN ONLINE PELANGGAN
CREATE TABLE IF NOT EXISTS `customer_orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_code` VARCHAR(50) NOT NULL UNIQUE,
  `nama_pelanggan` VARCHAR(100) NOT NULL,
  `nomor_meja` VARCHAR(50) DEFAULT 'Meja 1',
  `status` ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'PENDING',
  `total_akhir` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `customer_order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT DEFAULT NULL,
  `product_nama` VARCHAR(150) NOT NULL,
  `harga` INT NOT NULL DEFAULT 0,
  `qty` INT NOT NULL DEFAULT 1,
  `subtotal` INT NOT NULL DEFAULT 0,
  FOREIGN KEY (`order_id`) REFERENCES `customer_orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;