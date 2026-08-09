# HARINFOOD POS & Kasir Online v2 (PHP & MySQL Version)

Aplikasi POS Kasir & Pemesanan Online berbasis **PHP 8+** dan **Database MySQL / MariaDB** dengan fitur-fitur lengkap untuk manajemen restoran modern.

## 🎯 Fitur Utama v2

### 1. **Keranjang Pesanan Mengambang dengan Efek Glow** 🎆
- ✅ Tombol keranjang dipindahkan ke bawah layar dengan animasi glow yang menyala
- ✅ Menampilkan total harga pesanan secara real-time
- ✅ Animasi pulsing untuk menarik perhatian pelanggan

### 2. **Quantity Editing via Keyboard** ⌨️
- ✅ Bisa langsung ketik jumlah quantity di input number
- ✅ Update otomatis saat memasukkan nilai
- ✅ Support increment/decrement dengan tombol +/-

### 3. **Fitur Delivery & Alamat** 🚚
- ✅ Pilihan metode: **Ambil Sendiri** atau **Delivery**
- ✅ Input alamat/nomor meja yang jelas
- ✅ Catatan pesanan detail untuk instruksi khusus
- ✅ Semua informasi tersimpan di database transaksi

### 4. **Kasir - Manual Harga & Produk Custom** 💰
- ✅ Kasir bisa mengatur harga manual tanpa mengubah database produk
- ✅ Tambah produk baru tanpa masuk ke database produk
- ✅ Semua item custom hanya tersimpan di `transaction_items` table
- ✅ Support metode pembayaran tunai dengan hitung kembalian otomatis

### 5. **Laporan Transaksi Lengkap** 📊
- ✅ Tampilan ringkasan: **Total Pendapatan** + **Jumlah Transaksi**
- ✅ Daftar lengkap semua transaksi kasir dan online pelanggan
- ✅ Filter berdasarkan tipe transaksi, metode, dan waktu
- ✅ Export-ready untuk analisis lebih lanjut

---

## 💻 Cara Install di XAMPP / Laragon

### **Step 1: Jalankan Apache & MySQL**
Buka **XAMPP Control Panel** → Klik **Start** untuk Apache dan MySQL.

### **Step 2: Ekstrak File ke htdocs**
```bash
# Windows
C:\xampp\htdocs\harinfood\

# macOS/Linux
/Applications/XAMPP/xamppfiles/htdocs/harinfood/
```

### **Step 3: Buat Database MySQL**
1. Buka browser → `http://localhost/phpmyadmin`
2. Klik **New** → Buat database baru bernama **`harinfood_db`**
3. Klik tab **Import** → Pilih file **`database.sql`** dari folder project
4. Klik **Go / Kirim**

### **Step 4: Akses Aplikasi**
```
http://localhost/harinfood
```

---

## 🔑 Akses Kasir (PIN Default)

Gunakan salah satu PIN berikut untuk login sebagai kasir:
- **313121**
- **1234**
- **kasir123**

---

## 📁 Struktur File

```
harinfood/
├── index.php           # UI Utama (Vue.js Frontend)
├── api.php            # Backend API (Semua endpoint)
├── config.php         # Konfigurasi Database & Helper Functions
├── database.sql       # Schema Database (Import di phpMyAdmin)
├── README.md          # Dokumentasi ini
└── access_log         # Log akses server
```

---

## 🎯 Fitur Per Role

### 👥 Mode Pelanggan
- ✅ Lihat katalog makanan & minuman
- ✅ Tambah ke keranjang dengan quantity keyboard
- ✅ Pilih metode pesanan: Ambil Sendiri / Delivery
- ✅ Input alamat & catatan pesanan detail
- ✅ Kirim pesanan ke kasir
- ✅ Tunggu konfirmasi kasir

### 💰 Mode Kasir
- ✅ **Konfirmasi pesanan pelanggan** (dengan pemotongan stok otomatis)
- ✅ **Buat transaksi kasir** dengan harga manual (tanpa ubah DB)
- ✅ **Tambah produk custom** tanpa masuk database produk
- ✅ **Kelola stok manual** produk yang ada
- ✅ **Proses pembayaran** dengan hitung kembalian otomatis
- ✅ **Cetak struk pembayaran** dengan detail lengkap
- ✅ **Lihat laporan** pendapatan & semua transaksi

---

## 📊 Database Schema

### Tabel `transactions` (Unified untuk Kasir + Online)
```sql
CREATE TABLE transactions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  invoice_code VARCHAR(50) UNIQUE,
  tanggal DATETIME DEFAULT CURRENT_TIMESTAMP,
  nama_pelanggan VARCHAR(100),
  alamat VARCHAR(255),
  metode_pemesanan ENUM('Ambil Sendiri', 'Delivery'),
  catatan_pesanan TEXT,
  total_harga INT,
  diskon INT,
  total_akhir INT,
  nominal_bayar INT,
  kembalian INT,
  kasir_nama VARCHAR(100),
  tipe_transaksi ENUM('Kasir', 'Online Pelanggan'),
  status ENUM('Pending', 'Approved', 'Rejected', 'Selesai')
);
```

### Tabel `transaction_items`
```sql
CREATE TABLE transaction_items (
  id INT PRIMARY KEY AUTO_INCREMENT,
  transaction_id INT,
  product_id INT,
  product_nama VARCHAR(150),
  harga INT,
  harga_modal INT,
  qty INT,
  subtotal INT,
  is_custom TINYINT DEFAULT 0,
  FOREIGN KEY (transaction_id) REFERENCES transactions(id)
);
```

### Tabel `products`
```sql
CREATE TABLE products (
  id INT PRIMARY KEY AUTO_INCREMENT,
  kategori_id INT,
  nama VARCHAR(150),
  harga INT,
  harga_modal INT,
  stok INT,
  foto TEXT,
  created_at TIMESTAMP
);
```

### Tabel `categories`
```sql
CREATE TABLE categories (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nama VARCHAR(50) UNIQUE
);
```

### Tabel `customer_orders` (Legacy - untuk backward compatibility)
```sql
CREATE TABLE customer_orders (
  id INT PRIMARY KEY AUTO_INCREMENT,
  order_code VARCHAR(50) UNIQUE,
  nama_pelanggan VARCHAR(100),
  nomor_meja VARCHAR(50),
  status ENUM('PENDING', 'APPROVED', 'REJECTED'),
  total_akhir INT,
  created_at TIMESTAMP
);
```

---

## 🔌 API Endpoints

Semua endpoint ada di `api.php?action=...`

### Authentication
- `login_kasir` - Login kasir dengan PIN

### Products
- `get_products` - Ambil semua produk dengan kategori
- `save_product` - Tambah/update produk (hanya owner)
- `update_stock_manual` - Update stok manual

### Orders (Pelanggan Online)
- `submit_customer_order` - Submit pesanan pelanggan
- `get_customer_orders` - Ambil pesanan pelanggan yang pending
- `update_order_status` - Konfirmasi/tolak pesanan

### Transactions (Kasir)
- `checkout_kasir` - Proses transaksi kasir (dengan item custom)

### Reports
- `get_reports` - Laporan lengkap dengan ringkasan pendapatan & jumlah transaksi

---

## 🎨 UI/UX Features

### Desain
- **Dark Theme** dengan gradien cyan-blue untuk modern look
- **Glassmorphism** dengan backdrop blur untuk aesthetic
- **Responsive** untuk mobile, tablet, dan desktop
- **Smooth animations** dan transitions

### Interaktif
- **Toast notifications** untuk feedback user
- **Modal popup** untuk form input
- **Real-time cart update** dengan floating button
- **Live order polling** setiap 5 detik otomatis

### Print
- **Struk pembayaran** siap cetak
- **Format terformat** rapi dengan border dashed
- **Support thermal printer** 80mm

---

## 📱 Changelog v2 - Fitur Baru

| # | Fitur | Status | Detail |
|---|-------|--------|--------|
| 1 | Floating Cart Button | ✅ | Keranjang mengambang di bawah dengan efek glow |
| 2 | Quantity Input Field | ✅ | Bisa ketik langsung jumlah quantity |
| 3 | Delivery Method | ✅ | Pilih Ambil Sendiri atau Delivery |
| 4 | Address Field | ✅ | Ganti nomor meja jadi field alamat |
| 5 | Order Notes | ✅ | Tambah catatan pesanan untuk instruksi khusus |
| 6 | Custom Products | ✅ | Kasir tambah item tanpa ubah database |
| 7 | Manual Pricing | ✅ | Kasir set harga custom per transaksi |
| 8 | Unified Database | ✅ | Satu table untuk semua transaksi |
| 9 | Revenue Summary | ✅ | Laporan total pendapatan & jumlah transaksi |
| 10 | Enhanced Receipt | ✅ | Struk dengan metode, alamat, dan catatan |

---

## 🔒 Keamanan

### Authentication
- PIN-based access untuk kasir
- Session management dengan PHP native
- Input validation di backend & frontend

### Database
- **Parameterized queries** (prepared statements)
- **PDO** untuk koneksi database
- **Transaction support** untuk data consistency
- **Error handling** yang proper

### Data Protection
- Sanitasi input dengan `htmlspecialchars()`
- SQL injection prevention
- CSRF token ready (bisa ditambahkan)

---

## 🐛 Troubleshooting

### Koneksi Database Gagal
```
Error: SQLSTATE[HY000]
```
**Solusi:**
- Pastikan MySQL sudah running di XAMPP
- Cek kredensial di `config.php` (default: root, no password)
- Pastikan database `harinfood_db` sudah dibuat

### Stok Tidak Terpotong Saat Pesanan Dikonfirmasi
```
Stock not decreasing after order approval
```
**Solusi:**
- Pastikan order status di-set ke **Approved**
- Cek permissions pada table products
- Buka browser console (F12) untuk debug

### Struk Tidak Bisa Cetak
```
Print dialog tidak muncul
```
**Solusi:**
- Gunakan browser Chrome/Firefox/Edge
- Pastikan `window.print()` tidak di-block
- Cek print preview sebelum cetak final
- Gunakan printer thermal untuk hasil optimal

### Login Kasir Gagal
```
PIN Salah! muncul terus
```
**Solusi:**
- Gunakan PIN yang benar: **313121**, **1234**, atau **kasir123**
- Bisa edit PIN di `config.php` variable `$VALID_PINS`
- Pastikan tidak ada spasi di awal/akhir PIN

---

## 📈 Cara Menggunakan

### Sebagai Pelanggan (Customer)
```
1. Buka http://localhost/harinfood
2. Pilih kategori Makanan atau Minuman
3. Cari produk atau scroll
4. Klik produk untuk tambah ke keranjang
5. Ubah quantity:
   - Dengan tombol +/- di kartu produk
   - Atau ketik langsung di field quantity
6. Klik keranjang floating di bawah kanan
7. Isi data:
   - Nama pemesan
   - Alamat / Nomor meja
   - Pilih metode: Ambil Sendiri / Delivery
   - Catatan pesanan (opsional, untuk instruksi khusus)
8. Klik "Kirim Pesanan ke Kasir"
9. Tunggu kasir konfirmasi pesanan
```

### Sebagai Kasir (Cashier)
```
1. Buka http://localhost/harinfood
2. Klik tombol login di atas
3. Isi nama kasir & PIN (contoh: 313121)
4. Klik "Masuk Kasir"

DASHBOARD KASIR:
├── 🔔 Pesanan Masuk
│  └── Lihat & konfirmasi pesanan pelanggan
│  └── Klik "Konfirmasi" untuk potong stok
│  └── Klik "Tolak" untuk menolak pesanan
├── 📦 Kelola Stok
│  └── Edit stok produk secara manual
├── ＋ Tambah Produk
│  └── Tambah item custom (hanya untuk transaksi ini)
│  └── Tidak tersimpan di database produk
└── 📈 Laporan
   └── Lihat ringkasan pendapatan
   └── Lihat detail semua transaksi

TRANSAKSI KASIR:
1. Tambah menu dari katalog (atau tambah custom item)
2. Edit quantity dengan keyboard atau tombol
3. Klik keranjang floating di bawah
4. Isi data pelanggan:
   - Nama pemesan
   - Alamat / Nomor meja (opsional)
   - Metode pemesanan
   - Catatan (opsional)
5. Set diskon (jika ada)
6. Input nominal pembayaran tunai
7. Sistem otomatis hitung kembalian
8. Klik "Proses Pembayaran Kasir"
9. Preview struk
10. Klik "Cetak Struk" untuk print
```

---

## 🎓 Tips & Tricks

### Customer Tips
- 📌 **Bookmark halaman** untuk akses cepat ke menu
- 📱 **Optimized untuk mobile** - bisa order via HP
- 🔔 **Pesanan real-time** - kasir bisa langsung lihat pesanan baru
- 💬 **Gunakan catatan** untuk instruksi delivery spesial

### Kasir Tips
- ⌨️ **Keyboard shortcuts**: 
  - Tab = pindah input field
  - Enter = submit form
- 📊 **Laporan realtime** - update otomatis setelah transaksi
- 🖨️ **Print optimal** - gunakan thermal printer 80mm
- 💾 **Data tersimpan** - semua transaksi ada di database
- 🔄 **Quick add** - tombol "Tambah Produk" untuk item custom

### Developer Tips
- 🔧 **Edit PIN kasir** di `config.php` line ~65
- 🗄️ **Backup database** regular untuk safety
- 📝 **Customize struk** di modal receipt di `index.php`
- 🎨 **Edit warna** tema di `tailwind.config` di `index.php`

---

## 📞 Support & Troubleshooting

### Common Issues & Solutions

**Q: Database connection error**
- A: Jalankan MySQL di XAMPP, cek credentials di config.php

**Q: Port 80 sudah terpakai**
- A: Ubah port di XAMPP Settings, akses via `http://localhost:8080`

**Q: Struk tidak cetak**
- A: Gunakan Chrome/Firefox, cek popup blocker

**Q: Stok tidak berkurang**
- A: Pastikan klik "Konfirmasi" di pesanan masuk, bukan "Tolak"

**Q: Pesanan tidak muncul di kasir**
- A: Refresh halaman atau tunggu polling 5 detik

---

## 🎓 Customization Guide

### Mengubah PIN Kasir
Edit file `config.php` line ~65:
```php
$VALID_PINS = ['313121', '1234', 'kasir123', 'PIN_BARU'];
```

### Mengubah Database Credentials
Edit file `config.php` line 7-10:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'username');
define('DB_PASS', 'password');
define('DB_NAME', 'nama_database');
```

### Mengubah Warna Theme
Edit file `index.php` di section `<style>`:
```css
.glass { 
  background: rgba(22, 25, 48, 0.9); /* Ubah warna di sini */
}
```

### Menambah Kategori Produk
Tambahkan di database via phpMyAdmin:
```sql
INSERT INTO categories (nama) VALUES ('Dessert');
INSERT INTO categories (nama) VALUES ('Snack');
```

---

## 📄 File Configuration

### config.php - Database & Constants
```php
// Database
DB_HOST = localhost
DB_USER = root
DB_PASS = (kosong)
DB_NAME = harinfood_db

// PIN Kasir (bisa edit)
$VALID_PINS = ['313121', '1234', 'kasir123']

// Helper Functions
- format_rupiah()
- sanitize()
- generate_invoice_code()
- is_kasir_logged_in()
- get_kasir_name()
```

---

## ✅ Checklist Deployment

- [ ] Download repository dari GitHub
- [ ] Extract ke `htdocs/harinfood/`
- [ ] Create database `harinfood_db` di phpMyAdmin
- [ ] Import file `database.sql`
- [ ] Buka `http://localhost/harinfood`
- [ ] Test sebagai Pelanggan (pesankan menu)
- [ ] Test sebagai Kasir (login + konfirmasi)
- [ ] Test cetak struk
- [ ] Cek laporan pendapatan
- [ ] ✅ Done! Aplikasi ready untuk production

---

## 📄 Lisensi

Aplikasi ini bebas digunakan untuk keperluan komersial maupun personal.
Silakan modifikasi sesuai kebutuhan bisnis Anda.

---

## 🙏 Acknowledgments

Dibuat dengan ❤️ untuk restoran modern Indonesia

**Tech Stack:**
- Frontend: Vue.js 3.3, Tailwind CSS
- Backend: PHP 8+, PDO
- Database: MySQL / MariaDB
- Server: Apache (XAMPP)

---

**Version:** 2.0  
**Last Updated:** August 2026  
**Status:** ✅ Production Ready

---

**Happy Selling! 🎉**

Untuk support lebih lanjut, cek terminal/console browser (F12) untuk error messages.
