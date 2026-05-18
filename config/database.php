<?php
/**
 * Database Configuration
 * Central database credentials for the TEFA Bakery & Coffee system.
 * Modify these values to match your environment.
 */

// Deteksi otomatis apakah berjalan di localhost (Laragon) atau production (InfinityFree)
$is_local = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', 'tefa-kasir2.test', 'tefa_kasir2.test']) 
            || (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] === '127.0.0.1')
            || (php_sapi_name() === 'cli');

if ($is_local) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'tefa_bakery');
} else {
    // Kredensial Database Live di InfinityFree
    define('DB_HOST', 'sql212.infinityfree.com');
    define('DB_USER', 'if0_41944623');
    define('DB_PASS', 'yTHZVL1WPan');
    define('DB_NAME', 'if0_41944623_tefa_bakery');
}

define('DB_CHARSET', 'utf8mb4');

// Set timezone to Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');
