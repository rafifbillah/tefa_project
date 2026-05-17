<?php
/**
 * AJAX Endpoint for All Best Sellers
 */
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/LaporanModel.php';

Auth::requireRole('admin');

$laporanModel = new LaporanModel();
$allBestSellers = $laporanModel->getBestSellers(100); // Get up to 100 products

header('Content-Type: application/json');
echo json_encode($allBestSellers);
exit;
