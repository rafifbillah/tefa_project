<?php
/**
 * Session Configuration — TEFA Bakery & Coffee
 * -----------------------------------------------
 * Konfigurasi session yang aman sesuai OWASP best practice.
 * File ini HARUS di-include SEBELUM session_start() dipanggil.
 *
 * @security: Lindungi dari session hijacking, fixation, dan XSS.
 */

// ─── SECURITY: Jangan jalankan jika session sudah aktif ──────────────────────
if (session_status() === PHP_SESSION_ACTIVE) {
    return; // Session sudah berjalan, skip konfigurasi ulang
}

// ─── SECURITY: Cookie tidak bisa diakses via JavaScript (cegah XSS) ─────────
ini_set('session.cookie_httponly', 1);

// ─── SECURITY: Cookie hanya dikirim via HTTPS
//   Set ke 1 jika production HTTPS, 0 untuk local dev (HTTP) ─────────────────
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
ini_set('session.cookie_secure', $isSecure ? 1 : 0);

// ─── SECURITY: SameSite=Strict mencegah CSRF via cookie ──────────────────────
ini_set('session.cookie_samesite', 'Strict');

// ─── SECURITY: Tolak session ID yang tidak dibuat oleh server (strict mode) ──
ini_set('session.use_strict_mode', 1);

// ─── SECURITY: Hanya gunakan cookie untuk session (bukan URL parameter) ──────
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid', 0);

// ─── TIMEOUT: Session tidak aktif expired setelah 30 menit ───────────────────
define('SESSION_TIMEOUT', 1800); // 30 menit dalam detik
ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);

// ─── SECURITY: Nama session khusus (jangan gunakan PHPSESSID default) ────────
session_name('TEFA_SESS');

// ─── START SESSION ────────────────────────────────────────────────────────────
session_start();

// ─── SECURITY: Cek timeout aktifitas terakhir ────────────────────────────────
if (isset($_SESSION['last_activity'])) {
    $idle = time() - $_SESSION['last_activity'];
    if ($idle > SESSION_TIMEOUT) {
        // Session kadaluarsa — bersihkan dan paksa login ulang
        session_unset();
        session_destroy();
        session_start();
        session_name('TEFA_SESS');
        $_SESSION['_flash']['error'] = 'Sesi Anda telah berakhir. Silakan login kembali.';
        header('Location: ' . _base_url() . 'index.php');
        exit;
    }
}

// Update waktu aktivitas terakhir setiap request
$_SESSION['last_activity'] = time();

/**
 * Helper: dapatkan base URL proyek secara dinamis.
 * Digunakan untuk redirect yang benar di berbagai kedalaman folder.
 */
function _base_url(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script   = $_SERVER['SCRIPT_NAME'] ?? '';

    // Cari root proyek (folder yang mengandung index.php utama)
    // Asumsikan project root ada di /tefa_kasir2/ atau sejenisnya
    $path = dirname($script);

    // Naik ke root proyek (maksimal 3 level)
    $parts = explode('/', trim($path, '/'));
    // Ambil path sampai folder proyek (elemen pertama setelah domain)
    $projectRoot = '/' . ($parts[0] ?? '');

    return $protocol . '://' . $host . $projectRoot . '/';
}
