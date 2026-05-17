<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/BarangModel.php';

// Hanya izinkan akses jika sudah login
if (!Auth::check()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$barangModel = new BarangModel();

$type = $_GET['type'] ?? '';
$value = $_GET['value'] ?? '';
$excludeId = $_GET['exclude_id'] ?? null;

header('Content-Type: application/json');

if ($type === 'name') {
    $exists = $barangModel->isNameExists($value, $excludeId);
    echo json_encode(['exists' => $exists, 'message' => $exists ? 'Nama produk sudah ada.' : '']);
} elseif ($type === 'sku') {
    if (empty($value)) {
        echo json_encode(['exists' => false]);
        exit;
    }
    $exists = $barangModel->isSkuExists($value, $excludeId);
    echo json_encode(['exists' => $exists, 'message' => $exists ? 'SKU sudah digunakan.' : '']);
} else {
    echo json_encode(['error' => 'Invalid type']);
}
