<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/LaporanModel.php';

header('Content-Type: application/json');

// Pastikan hanya admin yang bisa mengakses
if (!Auth::isLoggedIn() || Auth::getRole() !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya Administrator yang dapat membatalkan transaksi.']);
    exit;
}

// Ambil raw input (dari fetch API)
$input = json_decode(file_get_contents('php://input'), true);
$transactionId = $input['id'] ?? null;

if (!$transactionId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID transaksi tidak valid.']);
    exit;
}

try {
    $model = new LaporanModel();
    // Gunakan user_id dari sesi saat ini untuk log audit
    $adminId = $_SESSION['user_id'] ?? 1;

    $result = $model->voidTransaction($transactionId, $adminId);

    if ($result['success']) {
        echo json_encode(['success' => true, 'message' => $result['message']]);
    } else {
        http_response_code(400); // Bad Request jika sudah divoid atau gagal logis
        echo json_encode(['success' => false, 'message' => $result['message']]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Kesalahan server internal: ' . $e->getMessage()]);
}
