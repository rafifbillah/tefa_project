<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Inventory.php';
// Auth::checkLogin();

// Logic tambah stok
if (isset($_POST['submit'])) {
    $inventory = new Inventory();
    if ($inventory->tambahStok($_POST)) {
        header("Location: barang.php?status=success");
    } else {
        header("Location: barang.php?status=error");
    }
    exit;
}

$page = 'inventory';
$pageTitle = 'Manajemen Inventori';
include 'includes/header.php';
?>
<section class="inventory-page">
    
    <div class="summary-cards">
        <div class="card card-orange">
            <div class="card-title">Total Produk</div>
            <div class="card-value">5</div>
            <div class="card-subtitle">Barang terdaftar</div>
            <i class="fa-solid fa-box-open card-icon"></i>
        </div>
        <div class="card">
            <div class="card-title">TOTAL STOK</div>
            <div class="card-value">160</div>
            <i class="fa-solid fa-box card-icon"></i>
        </div>
        <div class="card card-pink">
            <div class="card-title">EXPIRED</div>
            <div class="card-value">0 Barang</div>
            <div class="card-subtitle text-pink">Perlu segera dikeluarkan barang</div>
            <i class="fa-solid fa-warning card-icon text-pink"></i>
        </div>
        <div class="card card-orange-light">
            <div class="card-title">STOK MENIPIS</div>
            <div class="card-value">2 Barang</div>
            <div class="card-subtitle text-orange">Perlu segera restok</div>
            <i class="fa-solid fa-arrow-trend-down card-icon text-orange"></i>
        </div>
    </div>

    <div class="table-container">
        <!-- <div class="table-controls">
            <div class="filter-dropdown">
                <span>Semua Kategori</span>
                <i class="fa-solid fa-caret-down"></i>
            </div>
        </div> -->

        <table class="inventory-table">
            <thead>
                <tr>
                    <th class="col-foto">FOTO</th>
                    <th class="col-nama">NAMA BARANG & KODE BARANG</th>
                    <th class="col-kategori">KATEGORI</th>
                    <th class="col-stok">JUMLAH STOK</th>
                    <th class="col-status">STATUS</th>
                    <th class="col-exp">exp</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Data simulasi (nanti bisa diambil dari database)
                $inventoryData = [
                    ['foto' => 'kopi.jpg', 'nama' => 'Biji Kopi Espresso', 'kode' => 'KODE: 1', 'kategori' => 'KOPI', 'stok' => 100, 'status' => 'OPTIMAL', 'exp' => '06-02-2027'],
                    ['foto' => 'roti.jpg', 'nama' => 'Roti Sobek', 'kode' => 'KODE: 2', 'kategori' => 'ROTI', 'stok' => 20, 'status' => 'PERBARUAN', 'exp' => '05-02-2027'],
                    ['foto' => 'gembong.jpg', 'nama' => 'Roti Gembong', 'kode' => 'KODE: 3', 'kategori' => 'ROTI', 'stok' => 10, 'status' => 'STOK MENIPIS', 'exp' => '07-02-2027'],
                    ['foto' => 'tawar.jpg', 'nama' => 'Roti Tawar', 'kode' => 'KODE: 4', 'kategori' => 'ROTI', 'stok' => 0, 'status' => 'HABIS', 'exp' => '03-02-2027'],
                    ['foto' => 'bubuk.jpg', 'nama' => 'Bubuk Kopi Arabika', 'kode' => 'KODE: 5', 'kategori' => 'KOPI', 'stok' => 30, 'status' => 'PERBARUAN', 'exp' => '10-02-2027'],
                    ['foto' => 'bubuk.jpg', 'nama' => 'Bubuk Kopi Arabika', 'kode' => 'KODE: 5', 'kategori' => 'KOPI', 'stok' => 30, 'status' => 'PERBARUAN', 'exp' => '10-02-2027'],
                ];

                // Logika Pagination
                $limit = 5; // Maksimal 5 baris per halaman
                $total_items = count($inventoryData);
                $total_pages = ceil($total_items / $limit);
                
                // Ambil halaman dari parameter URL ?p=... (default: 1)
                $current_p = isset($_GET['p']) ? (int)$_GET['p'] : 1;
                if ($current_p < 1) $current_p = 1;
                if ($current_p > $total_pages && $total_pages > 0) $current_p = $total_pages;

                $offset = ($current_p - 1) * $limit;
                
                // Potong array data simulasi hanya untuk halaman ini
                $items_to_display = array_slice($inventoryData, $offset, $limit);

                foreach ($items_to_display as $row) {
                    $statusClass = '';
                    switch ($row['status']) {
                        case 'OPTIMAL': $statusClass = 'status-green'; break;
                        case 'STOK MENIPIS': $statusClass = 'status-orange'; break;
                        case 'HABIS': $statusClass = 'status-red'; break;
                        case 'PERBARUAN': $statusClass = 'status-grey'; break;
                    }
                    ?>
                    <tr>
                        <td class="col-foto"><div class="foto-placeholder">No Image</div></td>
                        <td class="col-nama">
                            <span class="product-nama"><?php echo $row['nama']; ?></span>
                            <span class="product-kode"><?php echo $row['kode']; ?></span>
                        </td>
                        <td class="col-kategori"><span class="kategori-tag"><?php echo $row['kategori']; ?></span></td>
                        <td class="col-stok"><?php echo $row['stok']; ?></td>
                        <td class="col-status"><span class="status-label <?php echo $statusClass; ?>"><?php echo $row['status']; ?></span></td>
                        <td class="col-exp">
                            <?php echo $row['exp']; ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="table-footer">
            <button class="add-btn" id="openAddModalBtn"><i class="fa-solid fa-plus"></i> Tambah Stok</button>
            <button class="print-btn"><i class="fa-solid fa-print"></i> Cetak Laporan</button> 
            <!-- onclick="window.print()" -->
            <div class="pagination">
                <?php
                $start_item = $offset + 1;
                $end_item = min($offset + $limit, $total_items);
                if ($total_items == 0) { $start_item = 0; $end_item = 0; }
                ?>
                <span class="pagination-info">Menampilkan <?php echo $start_item; ?>-<?php echo $end_item; ?> dari <?php echo $total_items; ?> barang</span>
                
                <button class="page-prev" <?php echo ($current_p > 1) ? 'onclick="window.location.href=\'barang.php?p='.($current_p - 1).'\'"' : 'style="opacity: 0.5; cursor: default;"'; ?>><i class="fa-solid fa-chevron-left"></i></button>
                
                <button class="page-num active"><?php echo $current_p; ?></button>
                
                <button class="page-next" <?php echo ($current_p < $total_pages) ? 'onclick="window.location.href=\'barang.php?p='.($current_p + 1).'\'"' : 'style="opacity: 0.5; cursor: default;"'; ?>><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Stok -->
    <div class="modal-overlay" id="addModal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2>Tambah Stok Barang</h2>
                <button class="close-modal-btn" id="closeModalBtn"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <form action="barang.php" method="POST">
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label>Pilih Barang</label>
                        <select name="kode_barang" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                            <option value="">-- Pilih Barang --</option>
                            <?php foreach ($inventoryData as $item): ?>
                                <option value="<?php echo $item['kode']; ?>"><?php echo $item['nama'] . ' (' . $item['kode'] . ')'; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label>Jumlah Stok Ditambahkan</label>
                        <input type="number" name="jumlah_stok" placeholder="Contoh: 50" min="1" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label>Perbarui Tanggal EXP</label>
                        <input type="date" name="exp" required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="submit" class="btn-submit-modal" style="width: 100%;">+ Tambah Stok</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const addModal = document.getElementById('addModal');
            const openAddModalBtn = document.getElementById('openAddModalBtn');
            const closeModalBtn = document.getElementById('closeModalBtn');

            // Open modal
            openAddModalBtn.addEventListener('click', () => {
                addModal.classList.add('active');
            });

            // Close modal by closing button
            closeModalBtn.addEventListener('click', () => {
                addModal.classList.remove('active');
            });

            // Close modal if clicking outside modal content
            addModal.addEventListener('click', (e) => {
                if (e.target === addModal) {
                    addModal.classList.remove('active');
                }
            });


        });
    </script>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            alert('Barang berhasil ditambahkan!');
            // Membersihkan parameter URL agar alert tidak muncul lagi saat halaman di-refresh
            const url = new URL(window.location);
            url.searchParams.delete('status');
            window.history.replaceState(null, null, url);
        });
    </script>
    <?php endif; ?>
</section>
<?php include 'includes/footer.php'; ?>