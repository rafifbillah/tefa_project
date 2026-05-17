<?php
/**
 * AJAX Endpoint for Sales Statistics Chart
 * Returns JSON data for the dashboard chart based on filter (day, week, month)
 */
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/LaporanModel.php';

// Security check
if (!Auth::isLoggedIn() || Auth::getRole() !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$filter = $_GET['filter'] ?? 'week';
$laporanModel = new LaporanModel();

try {
    $result = $laporanModel->getChartStats($filter);
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['error' => $e->getMessage()]);
}
exit;
