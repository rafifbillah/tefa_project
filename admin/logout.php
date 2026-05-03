<?php
/**
 * Logout Handler — TEFA Bakery & Coffee
 * ----------------------------------------
 * Dipanggil dari sidebar semua module (admin, kasir, gudang).
 * Menghancurkan session dan redirect ke halaman login.
 */
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Flash.php';

// Lakukan logout
Auth::logout();

// Set flash message untuk konfirmasi
Flash::set('success', 'Anda telah berhasil logout.');

// Redirect ke halaman login utama
// Naik 1 level dari /admin/ ke root project
header('Location: ../index.php');
exit;
