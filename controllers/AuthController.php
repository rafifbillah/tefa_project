<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../core/Flash.php';

$userModel = new UserModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Proteksi CSRF
    if (!Auth::verifyCsrfToken($_POST['csrf_token'])) {
        header("Location: ../admin/user.php");
        exit;
    }

    switch ($action) {
        case 'register':
            // Validasi Password Match
            if ($_POST['password'] !== $_POST['confirm_password']) {
                Flash::set('error', 'Konfirmasi password tidak cocok!');
                header("Location: ../admin/user.php");
                exit;
            }
            
            if ($userModel->create($_POST)) {
                Flash::set('success', 'User ' . $_POST['username'] . ' berhasil ditambahkan.');
            }
            break;

        case 'update_user':
            $id = $_POST['user_id'];
            if ($userModel->update($id, $_POST)) {
                Flash::set('success', 'Data user diperbarui.');
            }
            break;

        case 'delete_user':
            $id = $_POST['user_id'];
            if ($userModel->delete($id)) {
                Flash::set('success', 'User telah dihapus.');
            }
            break;
    }
    header("Location: ../admin/user.php");
    exit;
}