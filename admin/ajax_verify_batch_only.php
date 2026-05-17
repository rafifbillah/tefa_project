<?php
require_once '../core/Auth.php';
Auth::requireRole('admin');
require_once '../models/LaporanModel.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

if (!Auth::verifyCsrfToken($csrf)) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

if (!isset($data['id_user']) || !isset($data['tanggal'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
    exit;
}

$model = new LaporanModel();
$success = $model->verifyBatch($data['id_user'], $data['tanggal']);

echo json_encode(['success' => $success, 'message' => $success ? 'Rekap shift berhasil diverifikasi.' : 'Gagal memverifikasi.']);
