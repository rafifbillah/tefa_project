<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../core/Flash.php';

$userModel = new UserModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    $isAjax = !empty($_POST['ajax']);

    if (!function_exists('finishRequest')) {
        function finishRequest($status, $msg, $isAjax, $redirect = "../admin/user.php") {
            if (!$isAjax || $status === 'success') {
                Flash::set($status, $msg);
            }
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => $status, 'message' => $msg]);
                exit;
            } else {
                header("Location: $redirect");
                exit;
            }
        }
    }

    // Proteksi CSRF
    if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        finishRequest('error', 'Token CSRF tidak valid atau kedaluwarsa.', $isAjax);
    }

    switch ($action) {
        case 'register':
            // Ambil data input
            $username = trim($_POST['username'] ?? '');
            $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            $role = $_POST['role'] ?? '';
            $status = $_POST['status'] ?? 'aktif';

            // Validasi Input Kosong
            if (empty($username) || empty($nama_lengkap) || empty($password)) {
                finishRequest('error', 'Username, Nama Lengkap, dan Password wajib diisi!', $isAjax);
            }

            // Validasi Format Username (Hanya huruf, angka, underscore, min 3 karakter)
            if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
                finishRequest('error', 'Username minimal 3 karakter dan hanya boleh berisi huruf, angka, atau underscore tanpa spasi!', $isAjax);
            }

            // Validasi Panjang Password
            if (strlen($password) < 6) {
                finishRequest('error', 'Password minimal harus 6 karakter!', $isAjax);
            }

            // Validasi Password Match
            if ($password !== $confirm) {
                finishRequest('error', 'Konfirmasi password tidak cocok!', $isAjax);
            }
            
            // Validasi Role & Status
            $valid_roles = ['admin', 'kasir', 'gudang'];
            $valid_statuses = ['aktif', 'non-aktif'];

            if (!in_array($role, $valid_roles) || !in_array($status, $valid_statuses)) {
                finishRequest('error', 'Role atau Status tidak valid!', $isAjax);
            }
            
            try {
                if ($userModel->create($_POST)) {
                    finishRequest('success', 'User ' . htmlspecialchars($username) . ' berhasil ditambahkan.', $isAjax);
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    finishRequest('error', 'Username sudah terdaftar! Silakan gunakan username lain.', $isAjax);
                } else {
                    finishRequest('error', 'Gagal menambahkan user: ' . $e->getMessage(), $isAjax);
                }
            }
            break;

        case 'update_user':
            $id = $_POST['id_user'];
            $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            $role = $_POST['role'] ?? '';
            $status = $_POST['status'] ?? 'aktif';

            // Validasi Input Kosong
            if (empty($nama_lengkap)) {
                finishRequest('error', 'Nama Lengkap wajib diisi!', $isAjax);
            }

            // Validasi Password Baru (Opsional)
            if (!empty($password)) {
                if (strlen($password) < 6) {
                    finishRequest('error', 'Password baru minimal harus 6 karakter!', $isAjax);
                }
                if ($password !== $confirm) {
                    finishRequest('error', 'Konfirmasi password baru tidak cocok!', $isAjax);
                }
            }
            
            // Validasi Role & Status
            $valid_roles = ['admin', 'kasir', 'gudang'];
            $valid_statuses = ['aktif', 'non-aktif'];

            if (!in_array($role, $valid_roles) || !in_array($status, $valid_statuses)) {
                finishRequest('error', 'Role atau Status tidak valid!', $isAjax);
            }

            try {
                if ($userModel->update($id, $_POST)) {
                    finishRequest('success', 'Data user berhasil diperbarui.', $isAjax);
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    finishRequest('error', 'Username sudah terdaftar!', $isAjax);
                } else {
                    finishRequest('error', 'Gagal memperbarui user: ' . $e->getMessage(), $isAjax);
                }
            }
            break;

        case 'delete_user':
            $id = $_POST['id_user'];
            try {
                if ($userModel->delete($id)) {
                    finishRequest('success', 'User telah dihapus.', $isAjax);
                }
            } catch (PDOException $e) {
                finishRequest('error', 'Gagal menghapus user: ' . $e->getMessage(), $isAjax);
            }
            break;
    }
    finishRequest('error', 'Aksi tidak valid.', $isAjax);
}