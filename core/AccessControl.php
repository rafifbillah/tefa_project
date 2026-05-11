<?php
/**
 * AccessControl Class
 * Menangani peran, kontrol akses halaman, dan status session.
 */
require_once __DIR__ . '/Flash.php';
require_once __DIR__ . '/AuthSecurity.php';
require_once __DIR__ . '/AuthService.php';

class AccessControl
{
    private const ROLE_REDIRECTS = [
        'admin'  => 'admin/index.php',
        'kasir'  => 'kasir/index.php',
        'gudang' => 'gudang/index.php',
    ];

    public static function requireRole(string $role): void
    {
        if (!self::isLoggedIn()) {
            Flash::set('error', 'Silakan login terlebih dahulu.');
            header('Location: ../login.php');
            exit;
        }

        if (self::getRole() !== $role) {
            $dashboard = self::getDashboardUrl(self::getRole());
            Flash::set('error', 'Akses ditolak! Anda tidak memiliki izin untuk membuka halaman tersebut.');
            header('Location: ../' . $dashboard);
            exit;
        }
    }

    public static function isLoggedIn(): bool
    {
        if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }

        $storedAgent  = $_SESSION['user_agent'] ?? '';
        $currentAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (!empty($storedAgent) && $storedAgent !== $currentAgent) {
            AuthService::logout();
            return false;
        }

        return true;
    }

    public static function getRole(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    public static function getDashboardUrl(string $role): string
    {
        return self::ROLE_REDIRECTS[$role] ?? 'index.php';
    }
}
