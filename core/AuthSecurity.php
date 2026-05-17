<?php
/**
 * AuthSecurity Class
 * Menangani keamanan seperti CSRF, Rate Limiting, dan pengecekan IP/User-Agent.
 */
require_once __DIR__ . '/Flash.php';

class AuthSecurity
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 900;

    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(string $token): bool
    {
        $stored = $_SESSION['csrf_token'] ?? '';
        if (empty($stored) || !hash_equals($stored, $token)) {
            return false;
        }
        // Jangan unset token di sini agar bisa digunakan kembali oleh AJAX 
        // tanpa harus refresh halaman penuh.
        return true;
    }

    public static function checkRateLimit(string $username): bool
    {
        $key = self::getRateLimitKey($username);
        if (!isset($_SESSION['_rate'][$key])) {
            return true;
        }
        $data = $_SESSION['_rate'][$key];
        if (time() > $data['locked_until']) {
            unset($_SESSION['_rate'][$key]);
            return true;
        }
        if ($data['attempts'] >= self::MAX_ATTEMPTS) {
            return false;
        }
        return true;
    }

    public static function recordFailedAttempt(string $username): void
    {
        $key = self::getRateLimitKey($username);
        if (!isset($_SESSION['_rate'][$key])) {
            $_SESSION['_rate'][$key] = [
                'attempts'      => 0,
                'locked_until'  => 0,
                'first_attempt' => time(),
            ];
        }
        $_SESSION['_rate'][$key]['attempts']++;
        if ($_SESSION['_rate'][$key]['attempts'] >= self::MAX_ATTEMPTS) {
            $_SESSION['_rate'][$key]['locked_until'] = time() + self::LOCKOUT_SECONDS;
        }
    }

    public static function getLockoutRemaining(string $username): int
    {
        $key = self::getRateLimitKey($username);
        $until = $_SESSION['_rate'][$key]['locked_until'] ?? 0;
        $seconds = max(0, $until - time());
        return (int) ceil($seconds / 60);
    }

    public static function clearRateLimit(string $username): void
    {
        $key = self::getRateLimitKey($username);
        unset($_SESSION['_rate'][$key]);
    }

    private static function getRateLimitKey(string $username): string
    {
        $ip = self::getClientIp();
        return md5($username . '|' . $ip);
    }

    public static function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
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
