<?php
/**
 * Core Auth Class — TEFA Bakery & Coffee
 * ----------------------------------------
 * Menangani seluruh logika autentikasi:
 *  - Login dengan bcrypt + PDO prepared statement
 *  - CSRF token generation & verification
 *  - Rate limiting sederhana (mencegah brute force)
 *  - Session management yang aman
 *  - Role-based access control
 *
 * @security  Semua query menggunakan prepared statement (PDO)
 * @security  Password di-hash dengan bcrypt (PASSWORD_BCRYPT)
 * @security  Session di-regenerate setelah login
 */
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Flash.php';

class Auth
{
    // ─── KONFIGURASI RATE LIMITER ─────────────────────────────────────────────
    /** Maksimal percobaan login yang diizinkan */
    private const MAX_ATTEMPTS    = 5;

    /** Periode lockout dalam detik (15 menit) */
    private const LOCKOUT_SECONDS = 900;

    // ─── KONFIGURASI REDIRECT PER ROLE ───────────────────────────────────────
    private const ROLE_REDIRECTS = [
        'admin'  => 'admin/index.php',
        'kasir'  => 'kasir/dashboard.php',
        'gudang' => 'gudang/index.php',
    ];

    // ═════════════════════════════════════════════════════════════════════════
    //  LOGIN
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Proses login user.
     *
     * Alur:
     *  1. Validasi input dasar
     *  2. Cek rate limit (brute force protection)
     *  3. Ambil user dari DB via prepared statement
     *  4. Verifikasi password dengan bcrypt
     *  5. Cek status user (harus 'active')
     *  6. Regenerate session ID (cegah session fixation)
     *  7. Set session data
     *
     * @param string $username Username yang diinput
     * @param string $password Password plaintext yang diinput
     * @return bool True jika berhasil
     */
    public static function login(string $username, string $password): bool
    {
        // ─── Step 1: Validasi input dasar ────────────────────────────────
        $username = trim($username);
        $password = trim($password);

        if (empty($username) || empty($password)) {
            Flash::set('error', 'Username dan password tidak boleh kosong.');
            return false;
        }

        // ─── Step 2: Rate limiting — cegah brute force ───────────────────
        if (!self::checkRateLimit($username)) {
            $remaining = self::getLockoutRemaining($username);
            Flash::set('error', "Terlalu banyak percobaan gagal. Coba lagi dalam {$remaining} menit.");
            return false;
        }

        // ─── Step 3: Query user dari database ────────────────────────────
        try {
            $db   = Database::getConnection();
            $stmt = $db->prepare(
                'SELECT id, username, password, nama_lengkap, role, status
                 FROM users
                 WHERE username = :username
                 LIMIT 1'
            );
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch(); // PDO::FETCH_ASSOC (default)
        } catch (RuntimeException | PDOException $e) {
            error_log('[Auth::login] DB Error: ' . $e->getMessage());
            Flash::set('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
            return false;
        }

        // ─── Step 4: Verifikasi user & password ──────────────────────────
        if (!$user) {
            // ─── SECURITY: Pesan generik — jangan beri tahu apakah user ada
            self::recordFailedAttempt($username);
            Flash::set('error', 'Username atau password salah.');
            return false;
        }

        // ─── SECURITY: password_verify() aman terhadap timing attack ────
        if (!password_verify($password, $user['password'])) {
            self::recordFailedAttempt($username);
            Flash::set('error', 'Username atau password salah.');
            return false;
        }

        // ─── Step 5: Cek status user ──────────────────────────────────────
        if ($user['status'] !== 'active') {
            Flash::set('error', 'Akun Anda tidak aktif. Hubungi administrator.');
            return false;
        }

        // ─── Step 6: Regenerate session ID (cegah session fixation) ──────
        // Hapus session lama, buat ID baru, pertahankan data
        session_regenerate_id(true);

        // ─── Step 7: Set session data ─────────────────────────────────────
        $_SESSION['user_id']      = (int) $user['id'];
        $_SESSION['username']     = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['role']         = $user['role'];
        $_SESSION['logged_in']    = true;
        $_SESSION['last_activity'] = time();

        // ─── SECURITY: Simpan fingerprint browser untuk deteksi hijacking ─
        $_SESSION['user_agent']   = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['ip_address']   = self::getClientIp();

        // Reset rate limit setelah login berhasil
        self::clearRateLimit($username);

        return true;
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  LOGOUT
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Logout user — hancurkan session sepenuhnya.
     *
     * @security  Hapus cookie session dan destroy session data.
     */
    public static function logout(): void
    {
        // Hapus semua variabel session
        $_SESSION = [];

        // ─── SECURITY: Hapus cookie session dari browser ──────────────────
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires'  => time() - 42000,
                    'path'     => $params['path'],
                    'domain'   => $params['domain'],
                    'secure'   => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => 'Strict',
                ]
            );
        }

        // Hancurkan session di server
        session_destroy();
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  CEK STATUS LOGIN & ROLE
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Cek apakah user sedang login.
     *
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }

        // ─── SECURITY: Deteksi session hijacking via User-Agent ───────────
        $storedAgent  = $_SESSION['user_agent'] ?? '';
        $currentAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (!empty($storedAgent) && $storedAgent !== $currentAgent) {
            // Kemungkinan session hijack — paksa logout
            self::logout();
            return false;
        }

        return true;
    }

    /**
     * Dapatkan role user yang sedang login.
     *
     * @return string|null Role user atau null jika tidak login
     */
    public static function getRole(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    /**
     * Dapatkan data user yang sedang login sebagai array.
     *
     * @return array<string, mixed>
     */
    public static function user(): array
    {
        if (!self::isLoggedIn()) {
            return [];
        }
        return [
            'id'           => $_SESSION['user_id']      ?? null,
            'username'     => $_SESSION['username']     ?? '',
            'nama_lengkap' => $_SESSION['nama_lengkap'] ?? '',
            'role'         => $_SESSION['role']         ?? '',
        ];
    }

    /**
     * Dapatkan URL dashboard sesuai role.
     *
     * @param string $role
     * @return string Relative URL ke dashboard
     */
    public static function getDashboardUrl(string $role): string
    {
        return self::ROLE_REDIRECTS[$role] ?? 'index.php';
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  CSRF PROTECTION
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Generate CSRF token dan simpan di session.
     * Panggil sekali per halaman form.
     *
     * @return string Token yang harus dimasukkan ke hidden input
     */
    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            // ─── SECURITY: random_bytes() menggunakan CSPRNG ─────────────
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verifikasi CSRF token dari form POST.
     *
     * @param string $token Token dari form
     * @return bool True jika valid
     */
    public static function verifyCsrfToken(string $token): bool
    {
        $stored = $_SESSION['csrf_token'] ?? '';

        // ─── SECURITY: hash_equals() mencegah timing attack ──────────────
        if (!hash_equals($stored, $token)) {
            Flash::set('error', 'Request tidak valid (CSRF). Silakan refresh halaman.');
            return false;
        }

        // Regenerate token setelah diverifikasi (single-use token)
        unset($_SESSION['csrf_token']);

        return true;
    }

    // ═════════════════════════════════════════════════════════════════════════
    //  RATE LIMITING (Brute Force Protection)
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Cek apakah user/IP masih diizinkan untuk mencoba login.
     *
     * Rate limit disimpan di session berdasarkan username + IP.
     * Untuk produksi skala besar, gunakan Redis/Memcached.
     *
     * @param string $username
     * @return bool True jika masih diizinkan
     */
    private static function checkRateLimit(string $username): bool
    {
        $key = self::getRateLimitKey($username);

        if (!isset($_SESSION['_rate'][$key])) {
            return true; // Belum ada percobaan
        }

        $data = $_SESSION['_rate'][$key];

        // Reset jika lockout period sudah lewat
        if (time() > $data['locked_until']) {
            unset($_SESSION['_rate'][$key]);
            return true;
        }

        // Masih dalam periode lockout
        if ($data['attempts'] >= self::MAX_ATTEMPTS) {
            return false;
        }

        return true;
    }

    /**
     * Catat percobaan login gagal.
     *
     * @param string $username
     */
    private static function recordFailedAttempt(string $username): void
    {
        $key = self::getRateLimitKey($username);

        if (!isset($_SESSION['_rate'][$key])) {
            $_SESSION['_rate'][$key] = [
                'attempts'     => 0,
                'locked_until' => 0,
                'first_attempt' => time(),
            ];
        }

        $_SESSION['_rate'][$key]['attempts']++;

        // Aktifkan lockout jika melewati batas
        if ($_SESSION['_rate'][$key]['attempts'] >= self::MAX_ATTEMPTS) {
            $_SESSION['_rate'][$key]['locked_until'] = time() + self::LOCKOUT_SECONDS;
        }
    }

    /**
     * Dapatkan sisa waktu lockout dalam menit (dibulatkan ke atas).
     *
     * @param string $username
     * @return int Menit tersisa
     */
    private static function getLockoutRemaining(string $username): int
    {
        $key     = self::getRateLimitKey($username);
        $until   = $_SESSION['_rate'][$key]['locked_until'] ?? 0;
        $seconds = max(0, $until - time());
        return (int) ceil($seconds / 60);
    }

    /**
     * Hapus rate limit setelah login berhasil.
     *
     * @param string $username
     */
    private static function clearRateLimit(string $username): void
    {
        $key = self::getRateLimitKey($username);
        unset($_SESSION['_rate'][$key]);
    }

    /**
     * Buat key unik per username + IP untuk rate limit.
     *
     * @param string $username
     * @return string
     */
    private static function getRateLimitKey(string $username): string
    {
        // Kombinasikan username + IP untuk rate limit yang lebih ketat
        $ip = self::getClientIp();
        return md5($username . '|' . $ip);
    }

    /**
     * Dapatkan IP client dengan mempertimbangkan proxy.
     *
     * @return string IP address
     */
    private static function getClientIp(): string
    {
        // Cek berbagai header proxy (urutan prioritas)
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = trim(explode(',', $_SERVER[$header])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }
}
