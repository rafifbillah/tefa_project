<?php
/**
 * Core Auth Class — TEFA Bakery & Coffee
 * ----------------------------------------
 * Facade untuk modul autentikasi yang sekarang didelegasikan ke:
 *  - AuthService
 *  - AuthSecurity
 *  - AccessControl
 */
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/AuthService.php';
require_once __DIR__ . '/AuthSecurity.php';
require_once __DIR__ . '/AccessControl.php';

class Auth
{
    public static function login(string $username, string $password): bool
    {
        return AuthService::login($username, $password);
    }

    public static function logout(): void
    {
        AuthService::logout();
    }

    public static function requireRole(string $role): void
    {
        AccessControl::requireRole($role);
    }

    public static function isLoggedIn(): bool
    {
        return AccessControl::isLoggedIn();
    }

    public static function check(): bool
    {
        return self::isLoggedIn();
    }

    public static function getRole(): ?string
    {
        return AccessControl::getRole();
    }

    public static function user(): array
    {
        return AuthService::user();
    }

    public static function getDashboardUrl(string $role): string
    {
        return AccessControl::getDashboardUrl($role);
    }

    public static function generateCsrfToken(): string
    {
        return AuthSecurity::generateCsrfToken();
    }

    public static function verifyCsrfToken(string $token): bool
    {
        return AuthSecurity::verifyCsrfToken($token);
    }
}
