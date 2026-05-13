<?php
require_once __DIR__ . '/../core/Auth.php';
Auth::requireRole('gudang');

require_once __DIR__ . '/../models/BarangModel.php';
require_once __DIR__ . '/../models/InventoryLogModel.php';
require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../core/Paginator.php';

$barangModel = new BarangModel();
$db = Database::getConnection();

// Logic perbarui stok
if (isset($_POST['submit'])) {
    // Gunakan hidden input jika select di-disable (saat edit per baris)
    // atau gunakan select langsung jika mode tambah umum
    $id = !empty($_POST['product_id_hidden']) ? $_POST['product_id_hidden'] : ($_POST['product_id'] ?? null);
    
    $jumlah = (int)$_POST['jumlah_stok'];
    $tipe = $_POST['tipe_mutasi'];
    $ket = $_POST['keterangan'];
    $exp = $_POST['exp'];
    $today = date('Y-m-d');

    if ($jumlah <= 0) {
        Flash::set('error', 'Jumlah stok harus lebih dari 0.');
    } elseif (!empty($exp) && $exp < $today) {
        Flash::set('error', 'Tanggal kadaluarsa tidak boleh berada di masa lalu.');
    } else {
        $product = $barangModel->getById($id);
        if ($product) {
            $stok_sebelum = $product['stok'];
            $stok_sesudah = ($tipe === 'masuk') ? ($stok_sebelum + $jumlah) : ($stok_sebelum - $jumlah);
            
            if ($stok_sesudah < 0) {
                Flash::set('error', 'Gagal! Stok tidak boleh minus.');
            } else {
                try {
                    $db->beginTransaction();
                    
                    // 1. Update Product
                    $stmt = $db->prepare("UPDATE products SET stok = ?, exp_date = ? WHERE id = ?");
                    $stmt->execute([$stok_sesudah, $exp, $id]);
                    
                    // 2. Insert Log
                    $logStmt = $db->prepare("INSERT INTO inventory_logs (product_id, user_id, tipe_mutasi, jumlah, stok_sebelum, stok_sesudah, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $logStmt->execute([$id, $_SESSION['user_id'] ?? 1, $tipe, $jumlah, $stok_sebelum, $stok_sesudah, $ket]);
                    
                    $db->commit();
                    Flash::set('success', 'Stok berhasil diperbarui.');
                } catch (Exception $e) {
                    $db->rollBack();
                    Flash::set('error', 'Terjadi kesalahan sistem.');
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

    <div class="table-container">
        <table class="inventory-table">
            <thead>
                <tr>
                    <th class="col-foto">FOTO</th>
                    <th class="col-nama">NAMA BARANG & SKU</th>
                    <th class="col-kategori">KATEGORI</th>
                    <th class="col-stok">STOK</th>
                    <th class="col-satuan">SATUAN</th>
                    <th class="col-status">STATUS</th>
                    <th class="col-exp">EXP DATE</th>
                    <th class="col-aksi">AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $inventoryData = $barangModel->getAll();
                $paginator = new Paginator($inventoryData, 5, 'p');

                foreach ($paginator->getItems() as $row):
                    $statusClass = '';
                    $statusLabel = 'OPTIMAL';
                    if ($row['stok'] == 0) {
                        $statusClass = 'status-red';
                        $statusLabel = 'HABIS';
                    } elseif ($row['stok'] < 10) {
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
                        <td class="col-stok" style="font-weight:bold;"><?= $row['stok'] ?></td>
                        <td class="col-satuan" style="color:#666;"><?= htmlspecialchars($row['satuan'] ?: 'Pcs') ?></td>
                        <td class="col-status"><span class="status-label <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                        <td class="col-exp" style="font-size:12px;"><?= $row['exp_date'] ? date('d/m/Y', strtotime($row['exp_date'])) : '-' ?></td>
                        <td class="col-aksi">
                            <div class="action-buttons-wrapper">
                                <button class="history-btn-small" onclick='openHistoryModal(<?= $row['id'] ?>, "<?= htmlspecialchars($row['nama_produk']) ?>")' title="Lihat Riwayat">
                                    <i class="fa-solid fa-history"></i>
                                </button>
                                <button class="edit-btn-small" onclick='openEditStokModal(<?= json_encode($row) ?>)' title="Update Stok">
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
            <?= $paginator->render('barang.php', 'barang') ?>
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
                    <input type="hidden" name="product_id_hidden" id="modal_product_id">
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label>Pilih Barang</label>
                        <select name="product_id" id="modal_product_select" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                            <option value="">-- Pilih Barang --</option>
                            <?php foreach ($inventoryData as $item): ?>
                                <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nama_produk']) ?> (<?= $item['sku'] ?>)</option>
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
                        <input type="date" name="exp" id="modal_exp" required
                               min="<?= date('Y-m-d') ?>"
                               style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="submit" class="btn-submit-modal" style="width: 100%;">Simpan Perubahan Stok</button>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const addModal = document.getElementById('addModal');
            const openAddModalBtn = document.getElementById('openAddModalBtn');
            const closeModalBtn = document.getElementById('closeModalBtn');

            openAddModalBtn.addEventListener('click', () => {
                document.getElementById('modalTitle').innerText = 'Update Stok Barang';
                document.getElementById('modal_product_id').value = '';
                const select = document.getElementById('modal_product_select');
                select.disabled = false;
                select.value = '';
                addModal.classList.add('active');
            });

            closeModalBtn.addEventListener('click', () => addModal.classList.remove('active'));
            addModal.addEventListener('click', (e) => { if (e.target === addModal) addModal.classList.remove('active'); });

            // Pastikan saat submit, jika select di-disable, ID tetap terkirim via hidden input
            document.getElementById('stokForm').addEventListener('submit', function() {
                const select = document.getElementById('modal_product_select');
                if (select.disabled) {
                    // Jika disabled, pastikan hidden input sudah punya nilainya
                    // (sudah diatur di openEditStokModal)
                } else {
                    // Jika tidak disabled, ambil nilai dari select ke hidden input sebagai backup
                    document.getElementById('modal_product_id').value = select.value;
                }
            });
        });

        function openEditStokModal(data) {
            const addModal = document.getElementById('addModal');
            document.getElementById('modalTitle').innerText = 'Update Stok: ' + data.nama_produk;
            document.getElementById('modal_product_id').value = data.id;
            
            const select = document.getElementById('modal_product_select');
            select.value = data.id;
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
    </script>
</section>
<?php include 'includes/footer.php'; ?>