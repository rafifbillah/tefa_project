<?php
/**
 * AuthService Class
 * Menangani proses otentikasi login, logout, dan data user.
 */
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Flash.php';
require_once __DIR__ . '/AuthSecurity.php';
require_once __DIR__ . '/AccessControl.php';

class AuthService
{
    public static function login(string $username, string $password): bool
    {
        $username = trim($username);
        $password = trim($password);

        if (empty($username) || empty($password)) {
            Flash::set('error', 'Username dan password tidak boleh kosong.');
            return false;
        }

        if (!AuthSecurity::checkRateLimit($username)) {
            $remaining = AuthSecurity::getLockoutRemaining($username);
            Flash::set('error', "Terlalu banyak percobaan gagal. Coba lagi dalam {$remaining} menit.");
            return false;
        }

        try {
            $db   = Database::getConnection();
            $stmt = $db->prepare(
                'SELECT id, username, password, nama_lengkap, role, status
                 FROM users
                 WHERE username = :username
                 LIMIT 1'
            );
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();
        } catch (RuntimeException | PDOException $e) {
            error_log('[AuthService::login] DB Error: ' . $e->getMessage());
            Flash::set('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
            return false;
        }

        if (!$user) {
            AuthSecurity::recordFailedAttempt($username);
            Flash::set('error', 'Username atau password salah.');
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            AuthSecurity::recordFailedAttempt($username);
            Flash::set('error', 'Username atau password salah.');
            return false;
        }

        if ($user['status'] !== 'aktif') {
            Flash::set('error', 'Akun Anda tidak aktif. Hubungi administrator.');
            return false;
        }

        session_regenerate_id(true);

        $_SESSION['user_id']      = (int) $user['id'];
        $_SESSION['username']     = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['role']         = $user['role'];
        $_SESSION['logged_in']    = true;
        $_SESSION['last_activity'] = time();

        $_SESSION['user_agent']   = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['ip_address']   = AuthSecurity::getClientIp();

        AuthSecurity::clearRateLimit($username);

        if ($_SESSION['role'] === 'kasir') {
            require_once __DIR__ . '/ShiftManager.php';
            $shiftManager = new ShiftManager();
            $shiftManager->startShift($_SESSION['user_id'], 0);
        }

        return true;
    }

    public static function logout(): void
    {
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'kasir' && isset($_SESSION['user_id'])) {
            require_once __DIR__ . '/ShiftManager.php';
            $shiftManager = new ShiftManager();
            $activeShift = $shiftManager->getActiveShift($_SESSION['user_id']);
            if ($activeShift) {
                $shiftManager->endShift((int)$activeShift['id'], 0, 'Auto-closed via logout');
            }
        }

        $_SESSION = [];

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

        session_destroy();
    }

    public static function user(): array
    {
        if (!AccessControl::isLoggedIn()) {
            return [];
        }
        return [
            'id'           => $_SESSION['user_id']      ?? null,
            'username'     => $_SESSION['username']     ?? '',
            'nama_lengkap' => $_SESSION['nama_lengkap'] ?? '',
            'role'         => $_SESSION['role']         ?? '',
        ];
    }
}
