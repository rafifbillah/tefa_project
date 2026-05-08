<?php
require_once '../core/Auth.php';
Auth::requireRole('admin');

require_once '../controllers/IntegrationController.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan.']);
    exit;
}

$controller = new IntegrationController();
$result = $controller->sendToGoogleSheets($data['id']);

echo json_encode($result);
