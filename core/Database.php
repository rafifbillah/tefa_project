<?php
/**
 * Core Database Class — PDO
 * --------------------------
 * Menangani koneksi database menggunakan PDO (PHP Data Objects).
 * PDO lebih aman dari MySQLi karena mendukung named placeholder
 * dan prepared statement yang lebih ekspresif.
 *
 * @security: Gunakan prepared statement SELALU, jangan query raw.
 * @pattern:  Singleton-like — satu koneksi per request.
 */
require_once __DIR__ . '/../config/database.php';

class Database
{
    /** @var PDO|null Instance PDO */
    private static ?PDO $instance = null;

    /**
     * Mencegah instantiasi langsung — gunakan getConnection().
     */
    private function __construct() {}

    /**
     * Dapatkan koneksi PDO (singleton pattern).
     * Koneksi dibuat hanya sekali per request.
     *
     * @return PDO
     * @throws RuntimeException Jika koneksi gagal
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=%s',
                    DB_HOST,
                    DB_NAME,
                    DB_CHARSET
                );

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    // ─── SECURITY: Lempar exception saat query gagal ──────
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

                    // Gunakan fetch associative array secara default
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                    // ─── SECURITY: Emulated prepare OFF — gunakan true
                    //   prepared statement di server MySQL ─────────────────
                    PDO::ATTR_EMULATE_PREPARES   => false,

                    // Aktifkan persistent connection untuk performa
                    // PDO::ATTR_PERSISTENT => true, // (opsional, hati-hati di shared hosting)
                ]);

                // Set timezone MySQL agar sinkron dengan timezone PHP (Asia/Jakarta)
                self::$instance->exec("SET time_zone = '+07:00'");
            } catch (PDOException $e) {
                // ─── SECURITY: Jangan expose detail error ke user ────────
                // Log error ke file, jangan tampilkan ke browser
                error_log('[DB Error] ' . $e->getMessage());

                // Tampilkan pesan generik
                throw new RuntimeException('Koneksi database gagal. Hubungi administrator.');
            }
        }

        return self::$instance;
    }

    /**
     * Reset instance (berguna untuk testing).
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
