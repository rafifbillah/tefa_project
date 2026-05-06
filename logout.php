<?php
/**
 * Logout System — TEFA Bakery & Coffee
 */
require_once 'core/Auth.php';

// Auth::logout() sudah menangani penutupan shift otomatis untuk kasir
Auth::logout();

// Redirect ke login dengan pesan sukses
header('Location: login.php');
exit;
