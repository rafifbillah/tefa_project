<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/BarangModel.php';
require_once __DIR__ . '/../core/Flash.php';

$barangModel = new BarangModel();

/**
 * Fungsi helper untuk upload gambar
 */
function handleUpload($file) {
    if (empty($file['name'])) return 'default_product.jpg';

    $targetDir = __DIR__ . '/../assets/img/products/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = time() . '_' . basename($file['name']);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Validasi Format
    $allowedTypes = ['jpg', 'jpeg', 'png'];
    if (!in_array($imageFileType, $allowedTypes)) {
        Flash::set('error', 'Hanya file JPG, JPEG, & PNG yang diperbolehkan.');
        return false;
    }

    // Validasi Ukuran (2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        Flash::set('error', 'Ukuran file maksimal 2MB.');
        return false;
    }

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        return $fileName;
    }

    Flash::set('error', 'Gagal mengunggah gambar.');
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Validasi CSRF Token
    if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        header("Location: ../admin/barang.php");
        exit;
    }

    switch ($action) {
        case 'create_barang':
            $imageName = handleUpload($_FILES['image'] ?? []);
            if ($imageName === false) {
                header("Location: ../admin/barang.php");
                exit;
            }

            $data = [
                'sku'         => $_POST['sku'] ?? '',
                'nama_produk' => $_POST['nama_produk'] ?? '',
                'category_id' => $_POST['category_id'] ?? null,
                'harga'       => str_replace('.', '', $_POST['harga'] ?? 0),
                'stok'        => $_POST['stok'] ?? 0,
                'exp_date'    => $_POST['exp_date'] ?? null,
                'status'      => $_POST['status'] ?? 'aktif',
                'image'       => $imageName
            ];
            
            if ($barangModel->create($data)) {
                Flash::set('success', 'Barang berhasil ditambahkan.');
            } else {
                Flash::set('error', 'Gagal menambahkan barang.');
            }
            break;

        case 'update_barang':
            $id = $_POST['barang_id'];
            $oldBarang = $barangModel->getById($id);
            
            $imageName = '';
            if (!empty($_FILES['image']['name'])) {
                $imageName = handleUpload($_FILES['image']);
                if ($imageName === false) {
                    header("Location: ../admin/barang.php");
                    exit;
                }
                
                // Hapus gambar lama jika ada gambar baru
                if (!empty($oldBarang['image']) && $oldBarang['image'] !== 'default_product.jpg') {
                    $oldPath = __DIR__ . '/../assets/img/products/' . $oldBarang['image'];
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
            }

            $data = [
                'sku'         => $_POST['sku'] ?? '',
                'nama_produk' => $_POST['nama_produk'] ?? '',
                'category_id' => $_POST['category_id'] ?? null,
                'harga'       => str_replace('.', '', $_POST['harga'] ?? 0),
                'stok'        => $_POST['stok'] ?? 0,
                'exp_date'    => $_POST['exp_date'] ?? null,
                'status'      => $_POST['status'] ?? 'aktif',
                'image'       => $imageName 
            ];

            if ($barangModel->update($id, $data)) {
                Flash::set('success', 'Data barang berhasil diperbarui.');
            } else {
                Flash::set('error', 'Gagal memperbarui data barang.');
            }
            break;

        case 'delete_barang':
            $id = $_POST['barang_id'];
            $barang = $barangModel->getById($id);

            if ($barang) {
                // Hapus file gambar dari server jika bukan default
                if (!empty($barang['image']) && $barang['image'] !== 'default_product.jpg') {
                    $filePath = __DIR__ . '/../assets/img/products/' . $barang['image'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }

                if ($barangModel->delete($id)) {
                    Flash::set('success', 'Barang dan gambar terkait telah dihapus.');
                } else {
                    Flash::set('error', 'Gagal menghapus data barang dari database.');
                }
            } else {
                Flash::set('error', 'Data barang tidak ditemukan.');
            }
            break;

        case 'toggle_status':
            $id = $_POST['barang_id'];
            $newStatus = $_POST['new_status'];
            if ($barangModel->updateStatus($id, $newStatus)) {
                $msg = $newStatus === 'aktif' ? 'Barang telah diaktifkan.' : 'Barang telah dinonaktifkan.';
                Flash::set('success', $msg);
            } else {
                Flash::set('error', 'Gagal memperbarui status barang.');
            }
            break;
    }

    header("Location: ../admin/barang.php");
    exit;
}
