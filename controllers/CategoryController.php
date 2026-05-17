<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../core/Flash.php';

$categoryModel = new CategoryModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Validasi CSRF Token
    if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        header("Location: ../admin/barang.php");
        exit;
    }

    switch ($action) {
        case 'create_category':
            $nama = $_POST['nama_kategori'] ?? '';
            if ($categoryModel->create($nama)) {
                Flash::set('success', 'Kategori berhasil ditambahkan.');
            } else {
                Flash::set('error', 'Gagal menambahkan kategori.');
            }
            break;

        case 'update_category':
            $id = $_POST['id_kategori'];
            $nama = $_POST['nama_kategori'] ?? '';
            if ($categoryModel->update($id, $nama)) {
                Flash::set('success', 'Kategori berhasil diperbarui.');
            } else {
                Flash::set('error', 'Gagal memperbarui kategori.');
            }
            break;

        case 'delete_category':
            $id = $_POST['id_kategori'];
            if ($categoryModel->delete($id)) {
                Flash::set('success', 'Kategori telah dihapus.');
            } else {
                Flash::set('error', 'Gagal menghapus kategori. Pastikan tidak ada barang yang menggunakan kategori ini.');
            }
            break;
    }

    header("Location: ../admin/barang.php");
    exit;
}
