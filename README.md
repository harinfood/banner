# HARINFOOD POS & Kasir Online (PHP & MySQL Version)

Aplikasi POS Kasir & Pemesanan Online berbasis **PHP 8+** dan **Database MySQL / MariaDB**.

## 🚀 Fitur Utama
1. **Multi-Role**: Kasir, Pelanggan, Dapur, Owner.
2. **Katalog & Stok Real-Time**: Otomatis memotong stok saat transaksi kasir disetujui.
3. **Pemesanan Pelanggan Mandiri**: Pelanggan bisa memesan dari HP/meja dan mengirim langsung ke Kasir.
4. **Cetak Struk & QRIS**: Dukungan cetak struk kasir.
5. **Ekspor Database SQL**: Dilengkapi file `database.sql` yang siap di-import ke phpMyAdmin.

---

## 💻 Cara Install di XAMPP / Laragon (Localhost Windows/Mac/Linux)

1. **Jalankan Apache & MySQL** di XAMPP Control Panel.
2. **Ekstrak File ZIP**:
   - Salin seluruh isi folder ini ke dalam direktori `htdocs` (contoh: `C:/xampp/htdocs/harinfood/`).
3. **Buat Database MySQL**:
   - Buka browser dan akses **`http://localhost/phpmyadmin`**.
   - Buat database baru bernama **`harinfood_db`**.
   - Klik tab **Import**, lalu pilih file **`database.sql`** yang ada di folder project ini, lalu klik **Go / Kirim**.
4. **Akses Aplikasi**:
   - Buka browser dan ketik: **`http://localhost/harinfood`**
