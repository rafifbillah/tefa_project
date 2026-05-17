<?php
require_once '../core/Auth.php';
Auth::requireRole('admin');
/**
 * Barang Management — Admin (Premium Edition)
 */

require_once __DIR__ . '/../models/BarangModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Flash.php';

$barangModel = new BarangModel();
$categoryModel = new CategoryModel();
$barangList  = $barangModel->getAll();
$categories  = $categoryModel->getAll();
$csrfToken   = Auth::generateCsrfToken();

$pageTitle    = 'Data Barang';
$dashboardPage = true;
$pageHeading  = 'Data Inventori Barang';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

    <!-- Flash Messages -->
    <?= Flash::render() ?>

    <div class="page-content">
        <!-- ═══ HEADER & SEARCH AREA ═══ -->
        <div class="content-header-premium">
            <div class="header-left" style="display:flex; gap:10px;">
                <button class="btn-primary-premium" onclick="openTambahModal()">
                    <i class="fas fa-plus-circle"></i> Tambah Barang
                </button>
                <button class="btn-secondary-premium" onclick="openCategoryModal()">
                    <i class="fas fa-tags"></i> Lihat Kategori
                </button>
            </div>
            <div class="header-right">
                <div class="search-box-premium">
                    <i class="fas fa-search"></i>
                    <input type="text" id="tableSearch" placeholder="Cari SKU atau Nama..." onkeyup="filterTable()">
                </div>
                <div class="filter-box-premium">
                    <select id="categoryFilter" onchange="filterTable()">
                        <option value="">Semua Kategori</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['nama_kategori']) ?>"><?= htmlspecialchars($cat['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- ═══ DATA TABLE ═══ -->
        <section class="table-card-premium">
            <div class="table-responsive">
                <table class="custom-table-premium" id="barangTable">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Exp. Date</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($barangList)): ?>
                        <tr>
                            <td colspan="9" class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <p>Belum ada data barang tersedia.</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($barangList as $b): ?>
                        <tr>
                            <td><span class="sku-badge"><?= htmlspecialchars($b['sku']) ?></span></td>
                            <td>
                                <div class="table-img-wrapper">
                                    <?php if (!empty($b['image']) && file_exists(__DIR__ . '/../assets/img/products/' . $b['image'])): ?>
                                        <img src="../assets/img/products/<?= $b['image'] ?>" alt="<?= htmlspecialchars($b['nama_produk']) ?>">
                                    <?php else: ?>
                                        <div class="img-placeholder-admin">
                                            <i class="fas fa-bread-slice"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><strong><?= htmlspecialchars($b['nama_produk']) ?></strong></td>
                            <td><?= htmlspecialchars($b['nama_kategori'] ?? 'Tanpa Kategori') ?></td>
                            <td>
                                <span class="<?= (int)$b['stok'] < 10 ? 'stock-low' : 'stock-normal' ?>">
                                    <?= (int)$b['stok'] ?> Unit
                                </span>
                            </td>
                            <td><?= $b['exp_date'] ? date('d/m/Y', strtotime($b['exp_date'])) : '<span class="text-muted">—</span>' ?></td>
                            <td class="price-col">Rp <?= number_format($b['harga'], 0, ',', '.') ?></td>
                            <td>
                                <span class="badge-status-<?= $b['status'] === 'aktif' ? 'aktif' : 'non-aktif' ?>">
                                    <?= ucfirst($b['status']) ?>
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <div class="action-btns">
                                    <form method="POST" action="../controllers/BarangController.php" style="display:inline;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="barang_id" value="<?= $b['id_produk'] ?>">
                                        <input type="hidden" name="new_status" value="<?= $b['status'] === 'aktif' ? 'non-aktif' : 'aktif' ?>">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <button type="submit" class="btn-toggle-action <?= $b['status'] === 'aktif' ? 'deactivate' : 'activate' ?>" title="<?= $b['status'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                            <i class="fas <?= $b['status'] === 'aktif' ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                        </button>
                                    </form>
                                    <button class="btn-edit-action" onclick="openEditModal(<?= htmlspecialchars(json_encode($b)) ?>)" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="../controllers/BarangController.php" onsubmit="return confirm('Hapus barang ini?')" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_barang">
                                        <input type="hidden" name="barang_id" value="<?= $b['id_produk'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <button type="submit" class="btn-delete-action" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- ═══ MODAL: TAMBAH/EDIT BARANG ═══ -->
    <div id="modalBarang" class="modal-premium-overlay" style="display:none;">
        <div class="modal-premium-box">
            <div class="modal-premium-header">
                <h3 id="modalBarangTitle">Tambah Barang Baru</h3>
                <button class="close-btn" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="../controllers/BarangController.php" method="POST" id="formBarang" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="create_barang">
                <input type="hidden" name="barang_id" id="barang_id">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="form-grid-premium">
                    <div class="form-group" id="group_sku">
                        <label>SKU Produk <span class="req">*</span></label>
                        <input type="text" name="sku" id="input_sku" required placeholder="Contoh: CAKE-001" onblur="validateUnique('sku', this)">
                        <span class="validation-msg" id="msg_sku">SKU sudah digunakan.</span>
                    </div>
                    <div class="form-group" id="group_nama">
                        <label>Nama Produk <span class="req">*</span></label>
                        <input type="text" name="nama_produk" id="input_nama" required placeholder="Nama barang" onblur="validateUnique('name', this)">
                        <span class="validation-msg" id="msg_nama">Nama produk sudah ada.</span>
                    </div>
                    <div class="form-group">
                        <label>Kategori <span class="req">*</span></label>
                        <select name="id_kategori" id="input_category" required>
                            <option value="">Pilih Kategori</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id_kategori'] ?>"><?= htmlspecialchars($cat['nama_kategori']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Harga Jual (Rp) <span class="req">*</span></label>
                        <input type="text" name="harga" id="input_harga" required onkeyup="formatRupiah(this)">
                    </div>
                    <div class="form-group">
                        <label>Stok Awal <span class="req">*</span></label>
                        <input type="number" name="stok" id="input_stok" required min="0">
                    </div>
                    <div class="form-group">
                        <label>Exp. Date</label>
                        <input type="date" name="exp_date" id="input_exp_date" min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Status <span class="req">*</span></label>
                        <select name="status" id="input_status" required>
                            <option value="aktif">Aktif</option>
                            <option value="non-aktif">Non-aktif</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label>Foto Produk <span class="req">*</span></label>
                        <div class="file-input-wrapper">
                            <div class="btn-upload-placeholder">
                                <i class="fas fa-upload"></i>
                                <span id="fileNameDisplay">Pilih Foto Produk...</span>
                            </div>
                            <input type="file" name="image" id="input_image" accept="image/*" onchange="previewImage(this)">
                        </div>
                        <div class="image-preview-wrapper" id="imagePreview">
                            <i class="fas fa-image"></i>
                            <img src="" alt="Preview" style="display:none;">
                        </div>
                        <small>Format: JPG, JPEG, PNG. Maks: 2MB</small>
                    </div>
                </div>

                <div class="modal-footer-premium">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn-save-premium">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ═══ MODAL: MANAJEMEN KATEGORI ═══ -->
    <div id="modalCategory" class="modal-premium-overlay" style="display:none;">
        <div class="modal-premium-box" style="width: 500px;">
            <div class="modal-premium-header">
                <h3 id="modalCategoryTitle">Manajemen Kategori</h3>
                <button class="close-btn" onclick="closeCategoryModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="categoryListView" style="padding: 25px;">
                <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h4 style="margin:0; color: var(--tefa-brown);">Daftar Kategori</h4>
                    <button class="btn-add-small" onclick="showCategoryForm()">
                        <i class="fas fa-plus"></i> Tambah Kategori
                    </button>
                </div>
                <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="custom-table-premium">
                        <thead>
                            <tr style="background: #f8f9fa; color: #333;">
                                <th style="padding: 10px; font-size: 12px;">Nama Kategori</th>
                                <th style="padding: 10px; font-size: 12px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($categories as $cat): ?>
                            <tr>
                                <td style="padding: 10px;"><?= htmlspecialchars($cat['nama_kategori']) ?></td>
                                <td style="padding: 10px; text-align: center;">
                                    <div style="display:flex; gap:5px; justify-content: center;">
                                        <button class="btn-edit-action" onclick="editCategory(<?= $cat['id_kategori'] ?>, '<?= htmlspecialchars($cat['nama_kategori'], ENT_QUOTES) ?>')">
                                            <i class="fas fa-edit" style="font-size: 12px;"></i>
                                        </button>
                                        <form method="POST" action="../controllers/CategoryController.php" onsubmit="return confirm('Hapus kategori ini?')">
                                            <input type="hidden" name="action" value="delete_category">
                                            <input type="hidden" name="id_kategori" value="<?= $cat['id_kategori'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <button type="submit" class="btn-delete-action">
                                                <i class="fas fa-trash-alt" style="font-size: 12px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="categoryFormView" style="display:none; padding: 25px;">
                <h4 id="formCategoryTitle" style="margin-bottom: 15px; color: var(--tefa-brown);">Tambah Kategori Baru</h4>
                <form action="../controllers/CategoryController.php" method="POST">
                    <input type="hidden" name="action" id="categoryFormAction" value="create_category">
                    <input type="hidden" name="id_kategori" id="id_kategori_input">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    
                    <div class="form-group">
                        <label>Nama Kategori <span class="req">*</span></label>
                        <input type="text" name="nama_kategori" id="input_nama_kategori" required placeholder="Contoh: Kue Kering">
                    </div>
                    
                    <div style="display:flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="button" class="btn-cancel" onclick="showCategoryList()">Kembali</button>
                        <button type="submit" class="btn-save-premium">Simpan Kategori</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<style>
/* ─── PREMIUM CSS FOR BARANG ─── */
:root {
    --tefa-brown: #2b1b17;
    --tefa-orange: #d4832c;
    --tefa-cream: #f9f6f2;
    --tefa-border: #e2d5c3;
    --tefa-red: #e74c3c;
    --tefa-green: #27ae60;
    --tefa-gray: #7f8c8d;
}

.page-content { padding: 5px; background: var(--tefa-cream); min-height: 100vh; }

/* Header Area */
.content-header-premium {
    display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;
    background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}
.btn-primary-premium {
    background: var(--tefa-brown); color: #fff; border: none; padding: 12px 24px; border-radius: 8px;
    font-weight: 600; cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 10px;
}
.btn-primary-premium:hover { background: var(--tefa-orange); transform: translateY(-2px); }

.btn-secondary-premium {
    background: #fff; color: var(--tefa-brown); border: 2px solid var(--tefa-brown); padding: 12px 24px; border-radius: 8px;
    font-weight: 600; cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 10px;
}
.btn-secondary-premium:hover { background: var(--tefa-brown); color: #fff; transform: translateY(-2px); }

.btn-add-small {
    background: var(--tefa-orange); color: #fff; border: none; padding: 6px 12px; border-radius: 6px;
    font-size: 12px; font-weight: 600; cursor: pointer; transition: 0.2s;
}
.btn-add-small:hover { background: var(--tefa-brown); }

.header-right { display: flex; gap: 15px; }
.search-box-premium { position: relative; }
.search-box-premium i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999; }
.search-box-premium input {
    padding: 10px 15px 10px 35px; border: 2px solid var(--tefa-border); border-radius: 8px; outline: none; width: 250px;
}
.filter-box-premium select { padding: 10px 15px; border: 2px solid var(--tefa-border); border-radius: 8px; outline: none; }

/* Table Styling */
.table-card-premium { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; }
.custom-table-premium { width: 100%; border-collapse: collapse; }
.custom-table-premium thead tr { background: var(--tefa-brown); color: #fff; }
.custom-table-premium th { padding: 15px; text-align: left; font-weight: 600; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }
.custom-table-premium td { padding: 15px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; transition: 0.2s; }
.custom-table-premium tbody tr:hover { background: #fffdf9; cursor: default; }

.sku-badge { background: #f0f0f0; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-family: monospace; color: var(--tefa-brown); }
.table-img-wrapper { width: 50px; height: 50px; border-radius: 8px; overflow: hidden; border: 1px solid var(--tefa-border); }
.table-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }

.stock-low { color: var(--tefa-red); font-weight: 700; }
.stock-normal { color: var(--tefa-brown); font-weight: 500; }
.price-col { font-weight: 600; color: var(--tefa-orange); }

.badge-status-aktif { background: #e8f8f0; color: var(--tefa-green); padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.badge-status-non-aktif { background: #f4f6f7; color: var(--tefa-gray); padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }

.action-btns { display: flex; gap: 8px; justify-content: center; }
.btn-edit-action { background: #eef2ff; color: #4338ca; border: none; padding: 8px; border-radius: 6px; cursor: pointer; transition: 0.2s; }
.btn-edit-action:hover { background: #4338ca; color: #fff; }
.btn-delete-action { background: #fff1f2; color: #be123c; border: none; padding: 8px; border-radius: 6px; cursor: pointer; transition: 0.2s; }
.btn-delete-action:hover { background: #be123c; color: #fff; }

.btn-toggle-action {
    border: none; padding: 8px; border-radius: 6px; cursor: pointer; transition: 0.2s;
}
.btn-toggle-action.deactivate { background: #fff7ed; color: #ea580c; }
.btn-toggle-action.deactivate:hover { background: #ea580c; color: #fff; }
.btn-toggle-action.activate { background: #f0fdf4; color: #16a34a; }
.btn-toggle-action.activate:hover { background: #16a34a; color: #fff; }

.empty-state { text-align: center; padding: 60px !important; color: #999; }
.empty-state i { font-size: 48px; margin-bottom: 15px; display: block; opacity: 0.3; }

/* Modal Premium */
.modal-premium-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 9999;
    display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);
}
.modal-premium-box {
    background: #fff; width: 650px; border-radius: 16px; overflow: hidden; animation: slideDown 0.3s ease-out;
}
@keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.modal-premium-header {
    background: var(--tefa-brown); color: #fff; padding: 20px 25px; display: flex; justify-content: space-between; align-items: center;
}
.modal-premium-header h3 { margin: 0; font-size: 18px; }
.close-btn { background: none; border: none; color: #fff; font-size: 20px; cursor: pointer; opacity: 0.7; }
.close-btn:hover { opacity: 1; }

.form-grid-premium { padding: 25px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-group.full-width { grid-column: span 2; }
.form-group label { font-weight: 600; color: #555; font-size: 13px; }
.form-group input, .form-group select {
    padding: 12px; border: 2px solid var(--tefa-border); border-radius: 8px; outline: none; transition: 0.3s;
}
.form-group input:focus, .form-group select:focus { border-color: var(--tefa-orange); }
.req { color: var(--tefa-red); }

.modal-footer-premium {
    padding: 20px 25px; background: #f9f9f9; display: flex; justify-content: flex-end; gap: 12px;
}
.btn-cancel { background: #eee; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }
.btn-save-premium { background: var(--tefa-orange); color: #fff; border: none; padding: 10px 30px; border-radius: 8px; cursor: pointer; font-weight: 600; }

@media (max-width: 768px) {
    .content-header-premium { flex-direction: column; gap: 15px; }
    .header-right { flex-direction: column; width: 100%; }
    .search-box-premium input { width: 100%; }
    .modal-premium-box { width: 90%; }
    .form-grid-premium { grid-template-columns: 1fr; }
    .form-group.full-width { grid-column: span 1; }
}

/* Image Upload Preview */
.image-preview-wrapper {
    margin-top: 10px; width: 100px; height: 100px; border: 2px dashed var(--tefa-border);
    border-radius: 12px; display: flex; align-items: center; justify-content: center;
    overflow: hidden; background: #fdfdfd;
}
.image-preview-wrapper img { width: 100%; height: 100%; object-fit: cover; }
.image-preview-wrapper i { font-size: 24px; color: #ccc; }

.file-input-wrapper { position: relative; display: flex; align-items: center; gap: 10px; }
.file-input-wrapper input[type="file"] { position: absolute; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
.btn-upload-placeholder {
    background: #f8f9fa; border: 2px solid var(--tefa-border); padding: 10px 15px;
    border-radius: 8px; color: #555; font-size: 13px; display: flex; align-items: center; gap: 8px; width: 100%;
}

.img-placeholder-admin {
    width: 100%;
    height: 100%;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
    font-size: 1.2rem;
}

/* Validation Styles */
.form-group.error input, .form-group.error select {
    border-color: var(--tefa-red) !important;
}
.validation-msg {
    font-size: 11px;
    color: var(--tefa-red);
    margin-top: 4px;
    display: none;
}
.form-group.error .validation-msg {
    display: block;
}
.input-loading {
    position: relative;
}
.input-loading::after {
    content: "\f110";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    position: absolute;
    right: 12px;
    top: 38px;
    color: var(--tefa-orange);
    animation: fa-spin 1s infinite linear;
}
</style>

<script>
function openTambahModal() {
    document.getElementById('modalBarangTitle').innerText = 'Tambah Barang Baru';
    document.getElementById('formAction').value = 'create_barang';
    document.getElementById('barang_id').value = '';
    document.getElementById('formBarang').reset();
    
    // Reset Preview
    const preview = document.getElementById('imagePreview');
    preview.querySelector('img').style.display = 'none';
    preview.querySelector('i').style.display = 'block';
    document.getElementById('fileNameDisplay').innerText = 'Pilih Foto Produk...';
    
    // Clear Validation Errors
    document.querySelectorAll('.form-group').forEach(g => g.classList.remove('error'));
    
    document.getElementById('modalBarang').style.display = 'flex';
}

function openEditModal(data) {
    document.getElementById('modalBarangTitle').innerText = 'Edit Data Barang';
    document.getElementById('formAction').value = 'update_barang';
    document.getElementById('barang_id').value = data.id_produk;
    
    document.getElementById('input_sku').value = data.sku;
    document.getElementById('input_nama').value = data.nama_produk;
    document.getElementById('input_category').value = data.id_kategori;
    document.getElementById('input_harga').value = formatMoney(Math.round(data.harga));
    document.getElementById('input_stok').value = data.stok;
    document.getElementById('input_exp_date').value = data.exp_date;
    document.getElementById('input_status').value = data.status;

    // Set Preview for Edit
    const preview = document.getElementById('imagePreview');
    const previewImg = preview.querySelector('img');
    const previewIcon = preview.querySelector('i');
    
    if (data.image && data.image !== 'default_product.jpg') {
        previewImg.src = '../assets/img/products/' + data.image;
        previewImg.style.display = 'block';
        previewIcon.style.display = 'none';
        document.getElementById('fileNameDisplay').innerText = data.image;
    } else {
        previewImg.src = '';
        previewImg.style.display = 'none';
        previewIcon.style.display = 'block';
        document.getElementById('fileNameDisplay').innerText = 'Pilih Foto Produk...';
    }

    // Clear Validation Errors
    document.querySelectorAll('.form-group').forEach(g => g.classList.remove('error'));

    document.getElementById('modalBarang').style.display = 'flex';
}

function closeModal() {
    document.getElementById('modalBarang').style.display = 'none';
}

function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = preview.querySelector('img');
    const previewIcon = preview.querySelector('i');
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    
    if (input.files && input.files[0]) {
        // Validasi ukuran file (2MB)
        if (input.files[0].size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar! Maksimal 2MB.');
            input.value = '';
            fileNameDisplay.innerText = 'Pilih Foto Produk...';
            return;
        }

        fileNameDisplay.innerText = input.files[0].name;
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewImg.style.display = 'block';
            previewIcon.style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Real-time Validation
let isSubmitting = false;

async function validateUnique(type, input) {
    const value = input.value.trim();
    if (!value) return;

    const group = document.getElementById('group_' + type);
    const msg = document.getElementById('msg_' + type);
    const excludeId = document.getElementById('barang_id').value;

    group.classList.add('input-loading');
    
    try {
        const response = await fetch(`ajax_check_product.php?type=${type}&value=${encodeURIComponent(value)}&exclude_id=${excludeId}`);
        const data = await response.json();
        
        if (data.exists) {
            group.classList.add('error');
            msg.innerText = data.message;
        } else {
            group.classList.remove('error');
        }
    } catch (error) {
        console.error('Validation error:', error);
    } finally {
        group.classList.remove('input-loading');
    }
}

document.getElementById('formBarang').onsubmit = function(e) {
    const harga = document.getElementById('input_harga').value.replace(/\./g, '');
    const stok = document.getElementById('input_stok').value;
    const errors = document.querySelectorAll('.form-group.error');

    if (errors.length > 0) {
        e.preventDefault();
        alert('Mohon perbaiki kesalahan pada form sebelum menyimpan.');
        return false;
    }

    if (parseInt(harga) <= 0) {
        e.preventDefault();
        alert('Harga harus lebih besar dari 0.');
        return false;
    }

    if (parseInt(stok) < 0) {
        e.preventDefault();
        alert('Stok tidak boleh negatif.');
        return false;
    }

    const expDate = document.getElementById('input_exp_date').value;
    if (expDate) {
        const today = new Date();
        today.setHours(0,0,0,0);
        const selectedDate = new Date(expDate);
        if (selectedDate < today) {
            e.preventDefault();
            alert('Tanggal kadaluarsa tidak boleh tanggal yang sudah lewat.');
            return false;
        }
    }
    
    return true;
};

/* Category Modal Logic */
function openCategoryModal() {
    showCategoryList();
    document.getElementById('modalCategory').style.display = 'flex';
}

function closeCategoryModal() {
    document.getElementById('modalCategory').style.display = 'none';
}

function showCategoryList() {
    document.getElementById('categoryListView').style.display = 'block';
    document.getElementById('categoryFormView').style.display = 'none';
    document.getElementById('modalCategoryTitle').innerText = 'Manajemen Kategori';
}

function showCategoryForm() {
    document.getElementById('categoryListView').style.display = 'none';
    document.getElementById('categoryFormView').style.display = 'block';
    document.getElementById('formCategoryTitle').innerText = 'Tambah Kategori Baru';
    document.getElementById('categoryFormAction').value = 'create_category';
    document.getElementById('id_kategori_input').value = '';
    document.getElementById('input_nama_kategori').value = '';
}

function editCategory(id, nama) {
    document.getElementById('categoryListView').style.display = 'none';
    document.getElementById('categoryFormView').style.display = 'block';
    document.getElementById('formCategoryTitle').innerText = 'Edit Kategori';
    document.getElementById('categoryFormAction').value = 'update_category';
    document.getElementById('id_kategori_input').value = id;
    document.getElementById('input_nama_kategori').value = nama;
}

function filterTable() {
    const searchVal = document.getElementById('tableSearch').value.toLowerCase();
    const catVal = document.getElementById('categoryFilter').value.toLowerCase();
    const rows = document.querySelectorAll('#barangTable tbody tr');

    rows.forEach(row => {
        if (row.classList.contains('empty-state')) return;
        
        const sku = row.cells[0].innerText.toLowerCase();
        const nama = row.cells[2].innerText.toLowerCase();
        const cat = row.cells[3].innerText.toLowerCase();
        
        const matchesSearch = sku.includes(searchVal) || nama.includes(searchVal);
        const matchesCat = catVal === "" || cat === catVal;

        if (matchesSearch && matchesCat) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function formatRupiah(el) {
    let val = el.value.replace(/[^0-9]/g, '');
    el.value = formatMoney(val);
}

function formatMoney(n) {
    if (!n) return '0';
    let val = Math.round(n).toString();
    return val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Auto-hide flash messages
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(a => a.style.display = 'none');
}, 5000);
</script>

<?php include 'includes/footer.php'; ?>
