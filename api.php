<?php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
if (!$pdo && !in_array($action, ['login_kasir'])) {
    json_response('error', 'Koneksi database tidak aktif.');
}

switch ($action) {
    case 'login_kasir':
        $pin = $_POST['pin'] ?? '';
        $nama = $_POST['nama'] ?? 'Kasir';
        if (in_array(trim($pin), ['313121', '1234', 'kasir123'])) {
            $_SESSION['user_role'] = 'kasir';
            $_SESSION['kasir_nama'] = $nama;
            json_response('success', 'Login berhasil', ['nama' => $nama]);
        } else {
            json_response('error', 'PIN Salah!');
        }
        break;

    case 'get_products':
        $stmt = $pdo->query("SELECT p.*, c.nama as kategori_nama FROM products p LEFT JOIN categories c ON p.kategori_id = c.id ORDER BY p.id ASC");
        json_response('success', 'OK', $stmt->fetchAll());
        break;

    case 'save_product':
        $id = intval($_POST['id'] ?? 0);
        $nama = $_POST['nama'] ?? '';
        $kategori_id = intval($_POST['kategori_id'] ?? 1);
        $harga = intval($_POST['harga'] ?? 0);
        $harga_modal = intval($_POST['harga_modal'] ?? 0);
        $stok = intval($_POST['stok'] ?? 0);
        $foto = $_POST['foto'] ?? '';

        if ($id > 0) {
            $st = $pdo->prepare("UPDATE products SET nama=?, kategori_id=?, harga=?, harga_modal=?, stok=?, foto=? WHERE id=?");
            $st->execute([$nama, $kategori_id, $harga, $harga_modal, $stok, $foto, $id]);
        } else {
            $st = $pdo->prepare("INSERT INTO products (nama, kategori_id, harga, harga_modal, stok, foto) VALUES (?, ?, ?, ?, ?, ?)");
            $st->execute([$nama, $kategori_id, $harga, $harga_modal, $stok, $foto]);
        }
        json_response('success', 'Produk berhasil disimpan');
        break;

    case 'update_stock_manual':
        $id = intval($_POST['id'] ?? 0);
        $stok = intval($_POST['stok'] ?? 0);
        $pdo->prepare("UPDATE products SET stok = ? WHERE id = ?")->execute([$stok, $id]);
        json_response('success', 'Stok diperbarui');
        break;

    case 'submit_customer_order':
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!$data || empty($data['items'])) json_response('error', 'Keranjang kosong');

        $pdo->beginTransaction();
        try {
            $code = 'ORD-' . date('Ymd') . '-' . rand(10000, 99999);
            $alamat = $data['alamat'] ?? 'Meja 1';
            $metode = $data['metode_pemesanan'] ?? 'Ambil Sendiri';
            $catatan = $data['catatan_pesanan'] ?? '';

            $st = $pdo->prepare("INSERT INTO transactions (invoice_code, nama_pelanggan, alamat, metode_pemesanan, catatan_pesanan, total_harga, total_akhir, tipe_transaksi, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Online Pelanggan', 'Pending')");
            $st->execute([$code, $data['nama'], $alamat, $metode, $catatan, $data['total'], $data['total']]);
            $order_id = $pdo->lastInsertId();

            $sti = $pdo->prepare("INSERT INTO transaction_items (transaction_id, product_id, product_nama, harga, qty, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($data['items'] as $item) {
                $sti->execute([$order_id, $item['product_id'], $item['nama'], $item['harga'], $item['qty'], $item['subtotal']]);
            }
            $pdo->commit();
            json_response('success', 'Pesanan berhasil dikirim', ['order_code' => $code]);
        } catch (Exception $e) {
            $pdo->rollBack();
            json_response('error', $e->getMessage());
        }
        break;

    case 'get_customer_orders':
        $st = $pdo->query("SELECT * FROM transactions WHERE tipe_transaksi = 'Online Pelanggan' ORDER BY id DESC");
        $orders = $st->fetchAll();
        foreach ($orders as &$ord) {
            $sti = $pdo->prepare("SELECT * FROM transaction_items WHERE transaction_id = ?");
            $sti->execute([$ord['id']]);
            $ord['items'] = $sti->fetchAll();
            $ord['order_code'] = $ord['invoice_code'];
        }
        json_response('success', 'OK', $orders);
        break;

    case 'update_order_status':
        $id = intval($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'Approved';

        $pdo->beginTransaction();
        try {
            $st = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
            $st->execute([$status, $id]);

            if ($status === 'Approved') {
                $items = $pdo->prepare("SELECT * FROM transaction_items WHERE transaction_id = ?");
                $items->execute([$id]);
                foreach ($items->fetchAll() as $it) {
                    if ($it['product_id']) {
                        $pdo->prepare("UPDATE products SET stok = GREATEST(0, stok - ?) WHERE id = ?")->execute([$it['qty'], $it['product_id']]);
                    }
                }
            }
            $pdo->commit();
            json_response('success', 'Status diperbarui');
        } catch (Exception $e) {
            $pdo->rollBack();
            json_response('error', $e->getMessage());
        }
        break;

    case 'checkout_kasir':
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!$data || empty($data['items'])) json_response('error', 'Kosong');

        $pdo->beginTransaction();
        try {
            $code = 'TRX-' . date('Ymd') . '-' . rand(10000, 99999);
            $alamat = $data['alamat'] ?? 'Kasir';
            $metode = $data['metode_pemesanan'] ?? 'Ambil Sendiri';
            $catatan = $data['catatan_pesanan'] ?? '';

            $st = $pdo->prepare("INSERT INTO transactions (invoice_code, nama_pelanggan, alamat, metode_pemesanan, catatan_pesanan, total_harga, diskon, total_akhir, nominal_bayar, kembalian, kasir_nama, tipe_transaksi, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Kasir', 'Selesai')");
            $st->execute([$code, $data['nama_pelanggan'], $alamat, $metode, $catatan, $data['total_harga'], $data['diskon'], $data['total_akhir'], $data['nominal_bayar'], $data['kembalian'], $data['kasir_nama']]);
            $tx_id = $pdo->lastInsertId();

            $sti = $pdo->prepare("INSERT INTO transaction_items (transaction_id, product_id, product_nama, harga, harga_modal, qty, subtotal, is_custom) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($data['items'] as $it) {
                $is_custom = $it['is_custom'] ?? 0;
                $sti->execute([$tx_id, $it['product_id'] ?? null, $it['product_nama'], $it['harga'], $it['harga_modal'] ?? 0, $it['qty'], $it['subtotal'], $is_custom]);
                
                // Update stok hanya jika produk ada di database
                if ($it['product_id'] && !$is_custom) {
                    $pdo->prepare("UPDATE products SET stok = GREATEST(0, stok - ?) WHERE id = ?")->execute([$it['qty'], $it['product_id']]);
                }
            }
            $pdo->commit();
            json_response('success', 'OK', ['invoice' => $code]);
        } catch (Exception $e) {
            $pdo->rollBack();
            json_response('error', $e->getMessage());
        }
        break;

    case 'get_reports':
        $st = $pdo->query("SELECT t.*, (SELECT SUM(ti.harga_modal * ti.qty) FROM transaction_items ti WHERE ti.transaction_id = t.id) as total_modal FROM transactions t WHERE status IN ('Selesai', 'Approved') ORDER BY t.tanggal DESC");
        $txs = $st->fetchAll();
        foreach ($txs as &$t) {
            $items = $pdo->prepare("SELECT * FROM transaction_items WHERE transaction_id = ?");
            $items->execute([$t['id']]);
            $t['items'] = $items->fetchAll();
        }

        // Hitung total pendapatan dan jumlah transaksi
        $sum_stmt = $pdo->query("SELECT SUM(total_akhir) as total_pendapatan, COUNT(*) as jumlah_transaksi FROM transactions WHERE status IN ('Selesai', 'Approved')");
        $summary = $sum_stmt->fetch();

        json_response('success', 'OK', [
            'transactions' => $txs,
            'summary' => [
                'total_pendapatan' => $summary['total_pendapatan'] ?? 0,
                'jumlah_transaksi' => $summary['jumlah_transaksi'] ?? 0
            ]
        ]);
        break;

    default:
        json_response('error', 'Invalid action');
        break;
}
?>
