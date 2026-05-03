<?php
/**
 * Logout Handler — Gudang Module
 * Hancurkan session dan redirect ke halaman login.
 */
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Flash.php';

Auth::logout();
Flash::set('success', 'Anda telah berhasil logout.');

header('Location: ../index.php');
exit;
