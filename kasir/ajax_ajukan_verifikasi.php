<?php
require_once '../core/Auth.php';
Auth::requireRole('kasir');

require_once '../models/LaporanModel.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

if (!Auth::verifyCsrfToken($csrf)) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

if (!isset($data['tanggal'])) {
    echo json_encode(['success' => false, 'message' => 'Tanggal tidak ditemukan.']);
    exit;
}

$userId = !empty($_SESSION['id_user']) ? (int) $_SESSION['id_user'] : 1;
$tanggal = $data['tanggal'];

$laporanModel = new LaporanModel();
$success = $laporanModel->requestBatchVerification($userId, $tanggal);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Rekap shift harian berhasil diajukan ke Admin.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Tidak ada transaksi pending yang bisa diajukan.']);
}
