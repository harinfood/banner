<?php
// ============================================================
// HARINFOOD POS LITE - KONFIGURASI DATABASE & SERVER
// ============================================================
session_start();

// DATABASE CONFIGURATION
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'harinfood_db');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error Reporting (Development Mode)
// Uncomment untuk production: error_reporting(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DATABASE CONNECTION
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5
        ]
    );
} catch (PDOException $e) {
    $pdo = null;
    // Log error ke file (production)
    // error_log("Database Connection Error: " . $e->getMessage());
}

/**
 * JSON Response Helper
 * Mengirim response dalam format JSON dengan status, message, dan data
 * 
 * @param string $status - Status response (success/error)
 * @param string $message - Message dari response
 * @param array|null $data - Data payload (optional)
 */
function json_response($status, $message, $data = null) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Rupiah Formatter
 * Format angka ke format Rupiah Indonesia
 * 
 * @param int $nominal - Nominal yang akan diformat
 * @return string - Nominal dengan format Rp X.XXX
 */
function format_rupiah($nominal) {
    return 'Rp ' . number_format($nominal, 0, ',', '.');
}

/**
 * Sanitize Input
 * Membersihkan input dari karakter berbahaya
 * 
 * @param string $input - Input yang akan dibersihkan
 * @return string - Input yang sudah bersih
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate Invoice Code
 * Membuat kode invoice unik berdasarkan tanggal dan random number
 * 
 * @param string $prefix - Prefix invoice (default: TRX)
 * @return string - Kode invoice unik
 */
function generate_invoice_code($prefix = 'TRX') {
    return $prefix . '-' . date('Ymd') . '-' . rand(10000, 99999);
}

/**
 * Check if User is Kasir
 * Validasi apakah user sudah login sebagai kasir
 * 
 * @return bool - True jika user adalah kasir, false jika belum
 */
function is_kasir_logged_in() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'kasir';
}

/**
 * Get Kasir Name
 * Ambil nama kasir yang sedang login
 * 
 * @return string - Nama kasir atau 'Kasir Utama' jika belum login
 */
function get_kasir_name() {
    return $_SESSION['kasir_nama'] ?? 'Kasir Utama';
}

// CONSTANTS
define('APP_NAME', 'HARINFOOD POS');
define('APP_VERSION', '2.0');
define('CURRENCY', 'IDR');

// PIN MASTER (Edit sesuai kebutuhan)
$VALID_PINS = ['313121', '1234', 'kasir123'];

// PAYMENT METHODS
$PAYMENT_METHODS = [
    'Ambil Sendiri',
    'Delivery'
];

// TRANSACTION TYPES
$TRANSACTION_TYPES = [
    'Kasir',
    'Online Pelanggan'
];

// TRANSACTION STATUS
$TRANSACTION_STATUS = [
    'Pending',
    'Approved',
    'Rejected',
    'Selesai'
];

?>
