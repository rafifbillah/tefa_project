<?php
/**
 * Logout System — TEFA Bakery & Coffee
 */
require_once 'core/Auth.php';
require_once 'core/Flash.php';

// Auth::logout() sudah menangani penutupan shift otomatis untuk kasir
Auth::logout();

// Set flash message untuk konfirmasi
Flash::set('success', 'Anda telah berhasil logout.');

// Redirect ke login dengan pesan sukses
header('Location: login.php');
exit;

