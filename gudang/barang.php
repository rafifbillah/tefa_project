<?php
require_once __DIR__ . '/../core/Auth.php';
Auth::requireRole('gudang');

require_once __DIR__ . '/../models/BarangModel.php';
require_once __DIR__ . '/../models/InventoryLogModel.php';
require_once __DIR__ . '/../core/Flash.php';

$barangModel = new BarangModel();
$db = Database::getConnection();

// Logic perbarui stok
if (isset($_POST['submit'])) {
    // Gunakan hidden input jika select di-disable (saat edit per baris)
    // atau gunakan select langsung jika mode tambah umum
    $id = !empty($_POST['id_produk_hidden']) ? $_POST['id_produk_hidden'] : ($_POST['id_produk'] ?? null);
    
    $jumlah = (int)$_POST['jumlah_stok'];
    $tipe = $_POST['tipe_mutasi'];
    $ket = $_POST['keterangan'];
    $exp = $_POST['exp'];
    
    $today = date('Y-m-d');
    
    if ($jumlah <= 0) {
        Flash::set('error', 'Jumlah stok harus lebih dari 0.');
    } elseif (!empty($exp) && $exp < $today) {
        Flash::set('error', 'Tanggal kadaluarsa tidak boleh di masa lalu.');
    } else {
        $product = $barangModel->getById($id);
        if ($product) {
            $stok_sebelum = $product['stok'];
            
            try {
                $db->beginTransaction();
                
                $stok_sesudah = $stok_sebelum;
                $success = false;

                if ($tipe === 'masuk') {
                    $success = $barangModel->addBatch($id, $jumlah, $exp);
                    $stok_sesudah = $stok_sebelum + $jumlah;
                } else {
                    // Keluar / Rusak (Gunakan logika Gudang untuk memotong expired dulu)
                    $success = $barangModel->deductStockGudang($id, $jumlah);
                    if (!$success) {
                        Flash::set('error', 'Gagal! Total fisik barang di gudang tidak mencukupi untuk dikeluarkan.');
                        $db->rollBack();
                        header("Location: barang.php");
                        exit;
                    }
                    $stok_sesudah = $stok_sebelum - $jumlah;
                }

                if ($success) {
                    // 2. Insert Log
                    $logStmt = $db->prepare("INSERT INTO inventory_logs (id_produk, id_user, tipe_mutasi, jumlah, stok_sebelum, stok_sesudah, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $userId = !empty($_SESSION['id_user']) ? (int)$_SESSION['id_user'] : 1;
                    $logStmt->execute([$id, $userId, $tipe, $jumlah, $stok_sebelum, $stok_sesudah, $ket]);
                    
                    $db->commit();
                    Flash::set('success', 'Stok berhasil diperbarui.');
                }
            } catch (Exception $e) {
                $db->rollBack();
                Flash::set('error', 'Terjadi kesalahan sistem.');
            }
        }
    }
    header("Location: barang.php");
    exit;
}

// Logic Stock Opname
if (isset($_POST['submit_opname'])) {
    $id = $_POST['opname_id_produk'];
    $stok_fisik = (int)$_POST['stok_fisik'];
    $keterangan = $_POST['opname_keterangan'] ?: 'Penyesuaian Opname';
    
    if ($stok_fisik < 0) {
        Flash::set('error', 'Stok fisik tidak boleh negatif.');
    } else {
        $product = $barangModel->getById($id);
        if ($product) {
            $stok_sistem = (int)$product['stok'];
            $selisih = $stok_fisik - $stok_sistem;
            
            if ($selisih == 0) {
                Flash::set('info', 'Stok fisik sesuai dengan sistem. Tidak ada perubahan.');
            } else {
                $tipe = ($selisih > 0) ? 'masuk' : 'keluar';
                $jumlah = abs($selisih);
                $detail_ket = $keterangan . " (Sistem: $stok_sistem, Fisik: $stok_fisik)";
                
                try {
                    $db->beginTransaction();
                    $success = false;

                    if ($tipe === 'masuk') {
                        // Jika lebih, buat batch baru sebagai Penyesuaian
                        $success = $barangModel->addBatch($id, $jumlah, date('Y-m-d', strtotime('+7 days'))); // Default exp +7 days for found stock
                    } else {
                        // Jika kurang, deduct dengan memprioritaskan membuang batch expired terlebih dahulu
                        $success = $barangModel->deductStockGudang($id, $jumlah);
                        if (!$success) {
                            Flash::set('error', 'Gagal Opname! Jumlah pengurangan lebih besar dari total stok fisik yang tercatat.');
                            $db->rollBack();
                            header("Location: barang.php");
                            exit;
                        }
                    }

                    if ($success) {
                        // Insert Log
                        $logStmt = $db->prepare("INSERT INTO inventory_logs (id_produk, id_user, tipe_mutasi, jumlah, stok_sebelum, stok_sesudah, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $userIdLog = !empty($_SESSION['id_user']) ? (int)$_SESSION['id_user'] : 1;
                        $logStmt->execute([$id, $userIdLog, $tipe, $jumlah, $stok_sistem, $stok_fisik, $detail_ket]);
                        
                        $db->commit();
                        Flash::set('success', "Opname berhasil. Stok disesuaikan dari $stok_sistem menjadi $stok_fisik.");
                    }
                } catch (Exception $e) {
                    $db->rollBack();
                    Flash::set('error', 'Terjadi kesalahan sistem saat opname.');
                }
            }
        }
    }
    header("Location: barang.php");
    exit;
}

// Stats real
$totalProduk = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalStok = $db->query("SELECT SUM(stok) FROM products")->fetchColumn();
$totalExpired = $db->query("SELECT COUNT(*) FROM products WHERE exp_date < CURDATE() AND exp_date IS NOT NULL")->fetchColumn();
$totalKritis = $db->query("SELECT COUNT(*) FROM products WHERE stok < 10")->fetchColumn();

// Get categories & active products for modal dropdown
$categories = $barangModel->getCategories();
$dropdownProducts = $barangModel->getAllActive();

$page = 'inventory';
$pageTitle = 'Manajemen Inventori';
include 'includes/header.php';
?>

<section class="inventory-page">
    <?= Flash::render() ?>

    <div class="summary-cards">
        <div class="card card-orange">
            <div class="card-title">Total Produk</div>
            <div class="card-value"><?= $totalProduk ?></div>
            <div class="card-subtitle">Barang terdaftar</div>
            <i class="fa-solid fa-box-open card-icon"></i>
        </div>
        <div class="card">
            <div class="card-title">TOTAL STOK</div>
            <div class="card-value"><?= number_format($totalStok, 0, ',', '.') ?></div>
            <i class="fa-solid fa-box card-icon"></i>
        </div>
        <div class="card card-pink">
            <div class="card-title">EXPIRED</div>
            <div class="card-value"><?= $totalExpired ?> Barang</div>
            <div class="card-subtitle text-pink">Perlu segera dikeluarkan</div>
            <i class="fa-solid fa-warning card-icon text-pink"></i>
        </div>
        <div class="card card-orange-light">
            <div class="card-title">STOK MENIPIS</div>
            <div class="card-value"><?= $totalKritis ?> Barang</div>
            <div class="card-subtitle text-orange">Perlu segera restok</div>
            <i class="fa-solid fa-arrow-trend-down card-icon text-orange"></i>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <?php
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $category_filter = isset($_GET['category']) ? $_GET['category'] : '';
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    ?>
    <div class="filter-bar" style="background: white; padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); display: flex; gap: 15px; align-items: center; justify-content: space-between; flex-wrap: wrap;">
        <form action="barang.php" method="GET" style="display: flex; gap: 12px; flex: 1; align-items: center; flex-wrap: wrap; margin: 0;">
            <!-- Search Input -->
            <div class="search-wrapper" style="position: relative; flex: 2; min-width: 250px;">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama barang atau SKU..." style="width: 100%; padding: 10px 10px 10px 35px; border-radius: 6px; border: 1px solid #cbd5e1; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#cbd5e1'">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
            </div>
            
            <!-- Category Filter -->
            <div class="filter-group" style="flex: 1; min-width: 160px;">
                <select name="category" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; outline: none; cursor: pointer; background: white;">
                    <option value="">-- Semua Kategori --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id_kategori'] ?>" <?= ($category_filter == $cat['id_kategori']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nama_kategori']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Status Filter -->
            <div class="filter-group" style="flex: 1; min-width: 160px;">
                <select name="status" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1; outline: none; cursor: pointer; background: white;">
                    <option value="">-- Semua Status --</option>
                    <option value="tersedia" <?= ($status_filter === 'tersedia') ? 'selected' : '' ?>>Tersedia (Optimal)</option>
                    <option value="menipis" <?= ($status_filter === 'menipis') ? 'selected' : '' ?>>Stok Menipis (< 10)</option>
                    <option value="habis" <?= ($status_filter === 'habis') ? 'selected' : '' ?>>Habis / Expired</option>
                </select>
            </div>
            
            <!-- Buttons -->
            <div style="display: flex; gap: 8px;">
                <button type="submit" style="background: #d4832c; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; transition: background 0.2s;" onmouseover="this.style.background='#2b1b17'" onmouseout="this.style.background='#d4832c'">Filter</button>
                <?php if ($search !== '' || $category_filter !== '' || $status_filter !== ''): ?>
                    <a href="barang.php" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 10px 15px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; justify-content: center; transition: background 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-container">
        <table class="inventory-table">
            <thead>
                <tr>
                    <th class="col-foto">FOTO</th>
                    <th class="col-nama">NAMA BARANG & SKU</th>
                    <th class="col-kategori">KATEGORI</th>
                    <th class="col-stok">STOK LAYAK</th>
                    <th class="col-stok-fisik">TOTAL FISIK</th>
                    <th class="col-satuan">SATUAN</th>
                    <th class="col-status">STATUS</th>
                    <th class="col-exp">EXP DATE</th>
                    <th class="col-aksi">AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $inventoryData = $barangModel->getAll();
                
                // Terapkan Filter di tingkat PHP
                if ($search !== '') {
                    $inventoryData = array_filter($inventoryData, function($row) use ($search) {
                        return stripos($row['nama_produk'], $search) !== false || stripos($row['sku'], $search) !== false;
                    });
                }
                if ($category_filter !== '') {
                    $inventoryData = array_filter($inventoryData, function($row) use ($category_filter) {
                        return $row['id_kategori'] == $category_filter;
                    });
                }
                if ($status_filter !== '') {
                    $inventoryData = array_filter($inventoryData, function($row) use ($status_filter) {
                        if ($status_filter === 'habis') {
                            return $row['stok_layak'] == 0;
                        } elseif ($status_filter === 'menipis') {
                            return $row['stok_layak'] > 0 && $row['stok_layak'] < 10;
                        } elseif ($status_filter === 'tersedia') {
                            return $row['stok_layak'] >= 10;
                        }
                        return true;
                    });
                }

                // Pagination
                $limit = 5;
                $total_items = count($inventoryData);
                $total_pages = ceil($total_items / $limit);
                $current_p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
                if ($current_p < 1) $current_p = 1;
                if ($current_p > $total_pages && $total_pages > 0) $current_p = $total_pages;
                $offset = ($current_p - 1) * $limit;
                $items_to_display = array_slice($inventoryData, $offset, $limit);

                if (empty($items_to_display)):
                ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 30px; color: #94a3b8;">
                            <i class="fa-solid fa-box-open" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                            Tidak ada barang ditemukan dengan kriteria filter tersebut.
                        </td>
                    </tr>
                <?php
                endif;

                foreach ($items_to_display as $row):
                    $statusClass = '';
                    $statusLabel = 'OPTIMAL';
                    if ($row['stok_layak'] == 0) {
                        $statusClass = 'status-red';
                        $statusLabel = 'HABIS / EXPIRED';
                    } elseif ($row['stok_layak'] < 10) {
                        $statusClass = 'status-orange';
                        $statusLabel = 'MENIPIS';
                    } else {
                        $statusClass = 'status-green';
                    }
                ?>
                    <tr>
                        <td class="col-foto">
                            <div class="product-img-wrapper">
                                <?php if (!empty($row['image']) && file_exists(__DIR__ . '/../assets/img/products/' . $row['image'])): ?>
                                    <img src="../assets/img/products/<?= $row['image'] ?>" alt="Product">
                                <?php else: ?>
                                    <div class="foto-placeholder-small">
                                        <i class="fa-solid fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="col-nama">
                            <span class="product-nama"><?= htmlspecialchars($row['nama_produk']) ?></span>
                            <span class="product-kode"><?= htmlspecialchars($row['sku']) ?></span>
                        </td>
                        <td class="col-kategori"><span class="kategori-tag"><?= htmlspecialchars($row['nama_kategori'] ?: 'Umum') ?></span></td>
                        <td class="col-stok" style="font-weight:bold; color: #10b981; font-size: 16px;"><?= $row['stok_layak'] ?></td>
                        <td class="col-stok-fisik" style="font-weight:bold; color: #64748b; font-size: 12px;"><?= $row['stok'] ?></td>
                        <td class="col-satuan" style="color:#666;"><?= htmlspecialchars($row['satuan'] ?: 'Pcs') ?></td>
                        <td class="col-status"><span class="status-label <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                        <td class="col-exp" style="font-size:12px;"><?= $row['exp_date'] ? date('d/m/Y', strtotime($row['exp_date'])) : '-' ?></td>
                        <td class="col-aksi">
                            <div class="action-buttons-wrapper">
                                <button class="history-btn-small" onclick='openHistoryModal(<?= $row['id_produk'] ?>, "<?= htmlspecialchars($row['nama_produk']) ?>")' title="Lihat Riwayat">
                                    <i class="fa-solid fa-history"></i>
                                </button>
                                <button class="batch-btn-small" onclick='openBatchesModal(<?= $row['id_produk'] ?>, "<?= htmlspecialchars($row['nama_produk']) ?>")' title="Lihat Detail Batch">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                </button>
                                <button class="opname-btn-small" onclick='openOpnameModal(<?= json_encode($row) ?>)' title="Stock Opname">
                                    <i class="fa-solid fa-clipboard-check"></i>
                                </button>
                                <button class="edit-btn-small" onclick='openEditStokModal(<?= json_encode($row) ?>)' title="Mutasi Stok (+/-)">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="table-footer">
            <button class="add-btn" id="openAddModalBtn"><i class="fa-solid fa-plus"></i> Update Stok</button>
            <button class="print-btn" onclick="window.location.href='laporan.php'"><i class="fa-solid fa-file-lines"></i> Laporan Mutasi</button>
            <div class="pagination">
                <?php
                $start_item = $offset + 1;
                $end_item = min($offset + $limit, $total_items);
                if ($total_items == 0) { $start_item = 0; $end_item = 0; }
                
                // Buat query string untuk pagination agar filter tidak hilang
                $query_params = $_GET;
                unset($query_params['p']); // hapus page parameter agar tidak bentrok
                $queryString = http_build_query($query_params);
                $queryString = $queryString ? '&' . $queryString : '';
                ?>
                <span class="pagination-info">Menampilkan <?= $start_item ?>-<?= $end_item ?> dari <?= $total_items ?> barang</span>
                <button class="page-prev" <?= ($current_p > 1) ? 'onclick="window.location.href=\'barang.php?p='.($current_p - 1) . $queryString . '\'"' : 'style="opacity: 0.5; cursor: default;"'; ?>><i class="fa-solid fa-chevron-left"></i></button>
                <button class="page-num active"><?= $current_p ?></button>
                <button class="page-next" <?= ($current_p < $total_pages) ? 'onclick="window.location.href=\'barang.php?p='.($current_p + 1) . $queryString . '\'"' : 'style="opacity: 0.5; cursor: default;"'; ?>><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>
    </div>

    <!-- Modal Update Stok -->
    <div class="modal-overlay" id="addModal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2 id="modalTitle">Update Stok Barang</h2>
                <button class="close-modal-btn" id="closeModalBtn"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form action="barang.php" method="POST" id="stokForm">
                    <!-- Hidden input untuk ID saat mode edit per baris -->
                    <input type="hidden" name="id_produk_hidden" id="modal_id_produk">
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label>Pilih Barang</label>
                        <select name="id_produk" id="modal_product_select" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                            <option value="">-- Pilih Barang --</option>
                            <?php foreach ($dropdownProducts as $item): ?>
                                <option value="<?= $item['id_produk'] ?>"><?= htmlspecialchars($item['nama_produk']) ?> (<?= $item['sku'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="display:flex; gap:10px; margin-bottom: 1rem;">
                        <div class="form-group" style="flex:1;">
                            <label>Tipe</label>
                            <select name="tipe_mutasi" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                                <option value="masuk">Barang Masuk (+)</option>
                                <option value="keluar">Barang Keluar (-)</option>
                                <option value="rusak">Barang Rusak (-)</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Jumlah</label>
                            <input type="number" name="jumlah_stok" min="1" required placeholder="0" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label>Keterangan / Alasan</label>
                        <select name="keterangan" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                            <option value="Restock dari Supplier">Restock dari Supplier</option>
                            <option value="Penjualan / Pengeluaran">Penjualan / Pengeluaran</option>
                            <option value="Barang Rusak / Expired">Barang Rusak / Expired</option>
                            <option value="Retur Pelanggan">Retur Pelanggan</option>
                            <option value="Penyesuaian Stok">Penyesuaian Stok</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label>Tanggal Kadaluarsa (Baru/Tetap)</label>
                        <input type="date" name="exp" id="modal_exp" required min="<?= date('Y-m-d') ?>" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="submit" class="btn-submit-modal" style="width: 100%;">Simpan Perubahan Stok</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Stock Opname -->
    <div class="modal-overlay" id="opnameModal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2 id="opnameTitle">Stock Opname</h2>
                <button class="close-modal-btn" onclick="closeOpnameModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form action="barang.php" method="POST" id="opnameForm">
                    <input type="hidden" name="opname_id_produk" id="opname_id_produk">
                    
                    <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #e2e8f0;">
                        <p style="margin: 0; font-size: 13px; color: #64748b;">Stok di Sistem (Komputer):</p>
                        <p style="margin: 5px 0 0 0; font-size: 24px; font-weight: bold; color: #334155;" id="opname_stok_sistem">0</p>
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="font-weight: bold; color: #D97706;">Stok Fisik (Hasil Hitung Asli di Rak) <span style="color:red;">*</span></label>
                        <input type="number" name="stok_fisik" id="opname_stok_fisik" min="0" required placeholder="0" style="width: 100%; padding: 12px; border-radius: 5px; border: 2px solid #D97706; font-size: 18px; font-weight: bold;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label>Alasan / Keterangan Penyesuaian</label>
                        <input type="text" name="opname_keterangan" placeholder="Misal: Roti rusak, tikus, atau salah hitung kemarin" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="submit_opname" class="btn-submit-modal" style="width: 100%; background: #10b981;">Sesuaikan Stok</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Quick History -->
    <div class="modal-overlay" id="historyModal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 id="historyTitle">Riwayat Stok</h2>
                <button class="close-modal-btn" onclick="closeHistoryModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" id="historyBody">
                <p style="text-align:center; padding:20px;">Memuat data...</p>
            </div>
        </div>
    </div>

    <!-- Modal Lihat Detail Batch -->
    <div class="modal-overlay" id="batchesModal">
        <div class="modal-content" style="max-width: 650px;">
            <div class="modal-header">
                <h2 id="batchesTitle">Daftar Batch Produksi</h2>
                <button class="close-modal-btn" onclick="closeBatchesModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" id="batchesBody" style="max-height: 400px; overflow-y: auto;">
                <p style="text-align:center; padding:20px;">Memuat data batch...</p>
            </div>
        </div>
    </div>

    <style>
        .product-img-wrapper {
            width: 45px;
            height: 45px;
            background: #f1f5f9;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
        }
        .product-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .foto-placeholder-small {
            color: #cbd5e1;
            font-size: 1.2rem;
        }
        .action-buttons-wrapper {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .history-btn-small, .edit-btn-small {
            background: white; 
            border: 1px solid #e2e8f0; 
            padding: 6px 10px; 
            border-radius: 6px; 
            cursor: pointer; 
            color: #64748b; 
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .history-btn-small:hover { 
            background: #f0f9ff; 
            color: #0369a1; 
            border-color: #0369a1;
            transform: translateY(-1px);
        }
        .batch-btn-small {
            background: white; 
            border: 1px solid #e2e8f0; 
            padding: 6px 10px; 
            border-radius: 6px; 
            cursor: pointer; 
            color: #6366f1; 
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .batch-btn-small:hover { 
            background: #e0e7ff; 
            color: #4f46e5; 
            border-color: #4f46e5;
            transform: translateY(-1px);
        }
        .opname-btn-small {
            background: white; 
            border: 1px solid #e2e8f0; 
            padding: 6px 10px; 
            border-radius: 6px; 
            cursor: pointer; 
            color: #10b981; 
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .opname-btn-small:hover { 
            background: #ecfdf5; 
            color: #059669; 
            border-color: #059669;
            transform: translateY(-1px);
        }
        .edit-btn-small:hover { 
            background: #fff7ed; 
            color: #ea580c; 
            border-color: #ea580c;
            transform: translateY(-1px);
        }
        .status-label { font-size: 10px; padding: 3px 10px; border-radius: 12px; color: white; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-green { background-color: #10b981; }
        .status-orange { background-color: #f59e0b; }
        .status-red { background-color: #ef4444; }
        .btn-submit-modal { background: #d4832c; color: white; border: none; padding: 14px; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .btn-submit-modal:hover { background: #2b1b17; }
        
        /* Fix Table Alignment */
        .inventory-table td { vertical-align: middle; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const addModal = document.getElementById('addModal');
            const openAddModalBtn = document.getElementById('openAddModalBtn');
            const closeModalBtn = document.getElementById('closeModalBtn');

            openAddModalBtn.addEventListener('click', () => {
                document.getElementById('modalTitle').innerText = 'Update Stok Barang';
                document.getElementById('modal_id_produk').value = '';
                const select = document.getElementById('modal_product_select');
                select.disabled = false;
                select.value = '';
                addModal.classList.add('active');
            });

            closeModalBtn.addEventListener('click', () => addModal.classList.remove('active'));
            addModal.addEventListener('click', (e) => { if (e.target === addModal) addModal.classList.remove('active'); });

            // Pastikan saat submit, jika select di-disable, ID tetap terkirim via hidden input
            document.getElementById('stokForm').addEventListener('submit', function(e) {
                const select = document.getElementById('modal_product_select');
                const expDate = document.getElementById('modal_exp').value;
                const today = new Date().toISOString().split('T')[0];

                if (expDate && expDate < today) {
                    e.preventDefault();
                    alert('Maaf, tanggal kadaluarsa tidak boleh di masa lalu.');
                    return false;
                }

                if (select.disabled) {
                    // Jika disabled, pastikan hidden input sudah punya nilainya
                } else {
                    document.getElementById('modal_id_produk').value = select.value;
                }
            });
        });

        function openEditStokModal(data) {
            const addModal = document.getElementById('addModal');
            document.getElementById('modalTitle').innerText = 'Update Stok: ' + data.nama_produk;
            document.getElementById('modal_id_produk').value = data.id_produk;
            
            const select = document.getElementById('modal_product_select');
            select.value = data.id_produk;
            select.disabled = true; // Disable agar tidak dirubah saat edit per baris
            
            document.getElementById('modal_exp').value = data.exp_date || '';
            addModal.classList.add('active');
        }

        async function openHistoryModal(id, name) {
            const modal = document.getElementById('historyModal');
            document.getElementById('historyTitle').innerText = '5 Riwayat Terakhir: ' + name;
            modal.classList.add('active');
            
            try {
                const response = await fetch(`get_history.php?id=${id}`);
                const html = await response.text();
                document.getElementById('historyBody').innerHTML = html;
            } catch (err) {
                document.getElementById('historyBody').innerHTML = '<p style="color:red; text-align:center;">Gagal memuat data.</p>';
            }
        }

        function closeHistoryModal() {
            document.getElementById('historyModal').classList.remove('active');
        }

        // OPNAME JS
        function openOpnameModal(data) {
            document.getElementById('opnameTitle').innerText = 'Opname: ' + data.nama_produk;
            document.getElementById('opname_id_produk').value = data.id_produk;
            document.getElementById('opname_stok_sistem').innerText = data.stok + ' ' + (data.satuan || 'Pcs');
            document.getElementById('opname_stok_fisik').value = data.stok;
            
            document.getElementById('opnameModal').classList.add('active');
        }

        function closeOpnameModal() {
            document.getElementById('opnameModal').classList.remove('active');
        }

        // Close modal when clicking outside
        document.getElementById('opnameModal').addEventListener('click', (e) => { 
            if (e.target === document.getElementById('opnameModal')) closeOpnameModal(); 
        });

        // BATCHES JS
        async function openBatchesModal(id, name) {
            const modal = document.getElementById('batchesModal');
            document.getElementById('batchesTitle').innerText = 'Daftar Batch: ' + name;
            modal.classList.add('active');
            
            try {
                const response = await fetch(`get_batches.php?id=${id}`);
                const html = await response.text();
                document.getElementById('batchesBody').innerHTML = html;
            } catch (err) {
                document.getElementById('batchesBody').innerHTML = '<p style="color:red; text-align:center;">Gagal memuat data batch.</p>';
            }
        }

        function closeBatchesModal() {
            document.getElementById('batchesModal').classList.remove('active');
        }

        // Close batches modal when clicking outside
        document.getElementById('batchesModal').addEventListener('click', (e) => { 
            if (e.target === document.getElementById('batchesModal')) closeBatchesModal(); 
        });

    </script>
</section>
<?php include 'includes/footer.php'; ?>