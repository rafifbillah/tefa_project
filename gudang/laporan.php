<?php
require_once '../core/Auth.php';
Auth::requireRole('gudang');
/**
 * Laporan Mutasi Stok — Gudang (Premium Edition)
 */

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/InventoryLogModel.php';

// Auth::checkLogin(); // Pastikan user login sebagai Gudang/Admin

$logModel = new InventoryLogModel();

// Filters
$filters = [
    'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
    'end_date'   => $_GET['end_date'] ?? date('Y-m-d'),
    'tipe'       => $_GET['tipe'] ?? 'Semua',
    'search'     => $_GET['search'] ?? ''
];

$logs  = $logModel->getAll($filters);
$stats = $logModel->getSummaryStats($filters);

$pageTitle = 'Laporan Mutasi Stok';
$page = 'laporan';
include 'includes/header.php';
?>

<div class="mutation-report-container">
    <!-- ═══ STATS CARDS ═══ -->
    <section class="summary-cards">
        <div class="card">
            <div class="card-title">Barang Masuk</div>
            <div class="card-value"><?= number_format($stats['masuk'], 0, ',', '.') ?></div>
            <div class="card-subtitle success-text">Total stok diterima</div>
            <i class="fa-solid fa-arrow-circle-down card-icon success-text"></i>
        </div>
        <div class="card card-pink">
            <div class="card-title">Barang Keluar</div>
            <div class="card-value"><?= number_format($stats['keluar'], 0, ',', '.') ?></div>
            <div class="card-subtitle text-pink">Total stok keluar</div>
            <i class="fa-solid fa-arrow-circle-up card-icon text-pink"></i>
        </div>
        <div class="card card-orange-light">
            <div class="card-title">Barang Rusak</div>
            <div class="card-value"><?= number_format($stats['rusak'], 0, ',', '.') ?></div>
            <div class="card-subtitle text-orange">Kondisi tidak layak</div>
            <i class="fa-solid fa-exclamation-triangle card-icon text-orange"></i>
        </div>
        <div class="card card-orange">
            <div class="card-title">Stok Kritis</div>
            <div class="card-value"><?= number_format($stats['kritis'], 0, ',', '.') ?></div>
            <div class="card-subtitle">Barang di bawah stok min</div>
            <i class="fa-solid fa-box-open card-icon"></i>
        </div>
    </section>

    <!-- ═══ FILTER SECTION ═══ -->
    <section class="filter-card-premium">
        <form action="" method="GET" class="filter-form-premium">
            <div class="form-group-premium">
                <label>Dari Tanggal</label>
                <input type="date" name="start_date" value="<?= $filters['start_date'] ?>">
            </div>
            <div class="form-group-premium">
                <label>Sampai Tanggal</label>
                <input type="date" name="end_date" value="<?= $filters['end_date'] ?>">
            </div>
            <div class="form-group-premium">
                <label>Tipe Mutasi</label>
                <select name="tipe">
                    <option value="Semua" <?= $filters['tipe'] == 'Semua' ? 'selected' : '' ?>>Semua Tipe</option>
                    <option value="masuk" <?= $filters['tipe'] == 'masuk' ? 'selected' : '' ?>>Masuk</option>
                    <option value="keluar" <?= $filters['tipe'] == 'keluar' ? 'selected' : '' ?>>Keluar</option>
                    <option value="rusak" <?= $filters['tipe'] == 'rusak' ? 'selected' : '' ?>>Rusak</option>
                </select>
            </div>
            <div class="form-group-premium search-group">
                <label>Cari Barang</label>
                <div class="search-input-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Nama atau SKU..." value="<?= htmlspecialchars($filters['search']) ?>">
                </div>
            </div>
            <div class="form-actions-premium">
                <button type="submit" class="btn-filter-premium">Tampilkan</button>
                <a href="laporan.php" class="btn-reset-premium">Reset</a>
            </div>
        </form>
    </section>

    <!-- ═══ DATA TABLE ═══ -->
    <section class="table-card-premium">
        <div class="table-header-premium">
            <div class="table-title">
                <h4>Log Mutasi Stok (<?= count($logs) ?>)</h4>
            </div>
            <div class="table-actions">
                <button onclick="window.print()" class="btn-action-premium print">
                    <i class="fas fa-print"></i> Print / PDF
                </button>
                <a href="export_excel.php?<?= http_build_query($_GET) ?>" class="btn-action-premium excel" style="text-decoration:none;">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="custom-table-premium">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Barang</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Saldo</th>
                        <th>Keterangan</th>
                        <th>Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="7" class="empty-state">
                                <div class="empty-content">
                                    <i class="fas fa-clipboard-list"></i>
                                    <p>Tidak ada data mutasi stok ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>
                                    <div class="date-col">
                                        <strong><?= date('d M Y', strtotime($log['created_at'])) ?></strong>
                                        <small><?= date('H:i', strtotime($log['created_at'])) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-col">
                                        <span class="sku-tag"><?= htmlspecialchars($log['sku']) ?></span>
                                        <span class="product-name"><?= htmlspecialchars($log['nama_produk']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-tipe badge-<?= $log['tipe_mutasi'] ?>">
                                        <?= strtoupper($log['tipe_mutasi']) ?>
                                    </span>
                                </td>
                                <td class="text-bold <?= $log['tipe_mutasi'] == 'masuk' ? 'text-green' : 'text-red' ?>">
                                    <?= ($log['tipe_mutasi'] == 'masuk' ? '+' : '-') . $log['jumlah'] ?>
                                </td>
                                <td class="text-bold"><?= $log['stok_sesudah'] ?></td>
                                <td class="text-muted italic"><?= htmlspecialchars($log['keterangan'] ?: '-') ?></td>
                                <td>
                                    <div class="petugas-col">
                                        <i class="fas fa-user-circle"></i>
                                        <?= htmlspecialchars($log['petugas'] ?: 'System') ?>
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

<style>
/* ─── PREMIUM CSS FOR MUTATION REPORT ─── */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

:root {
    --tefa-brown: #2b1b17;
    --tefa-orange: #d4832c;
    --tefa-cream: #f9f6f2;
    --tefa-green: #27ae60;
    --tefa-red: #e74c3c;
    --tefa-gray: #7f8c8d;
    --tefa-border: #e2d5c3;
}

.mutation-report-container {
    padding: 5px;
    min-height: 100vh;
    font-family: 'Poppins', sans-serif;
}

/* Main Container */
.mutation-report-container {
    padding: 25px;
    min-height: 100vh;
    font-family: 'Poppins', sans-serif;
}

/* Filter Card */
.filter-card-premium {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}
.filter-form-premium {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: flex-end;
}
.form-group-premium {
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1;
    min-width: 180px;
}
.form-group-premium.search-group { flex: 2; }
.form-group-premium label { font-size: 13px; font-weight: 600; color: #555; }
.form-group-premium input, .form-group-premium select {
    padding: 10px 15px;
    border: 2px solid var(--tefa-border);
    border-radius: 8px;
    outline: none;
    font-size: 14px;
    transition: 0.3s;
}
.form-group-premium input:focus, .form-group-premium select:focus { border-color: var(--tefa-orange); }

.search-input-wrapper { position: relative; }
.search-input-wrapper i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #aaa; }
.search-input-wrapper input { padding-left: 35px; width: 100%; }

.form-actions-premium { display: flex; gap: 10px; }
.btn-filter-premium {
    background: var(--tefa-brown); color: #fff; border: none; padding: 11px 25px;
    border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s;
}
.btn-filter-premium:hover { background: var(--tefa-orange); transform: translateY(-2px); }
.btn-reset-premium {
    background: #f1f1f1; color: #555; text-decoration: none; padding: 11px 20px;
    border-radius: 8px; font-weight: 600; font-size: 14px; transition: 0.3s;
}

/* Table Section */
.table-card-premium {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    overflow: hidden;
}
.table-header-premium {
    padding: 20px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #f0f0f0;
}
.table-header-premium h4 { margin: 0; color: var(--tefa-brown); font-weight: 600; }
.table-actions { display: flex; gap: 10px; }
.btn-action-premium {
    border: none; padding: 8px 15px; border-radius: 6px; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 8px;
}
.btn-action-premium.print { background: #f8f9fa; color: #333; }
.btn-action-premium.excel { background: #e8f5e9; color: #2e7d32; }
.btn-action-premium.pdf { background: #ffebee; color: #c62828; }
.btn-action-premium:hover { transform: translateY(-2px); opacity: 0.9; }

.custom-table-premium { width: 100%; border-collapse: collapse; }
.custom-table-premium th {
    background: #fdfdfd; padding: 15px; text-align: left; font-size: 13px;
    color: #888; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #f0f0f0;
}
.custom-table-premium td { padding: 15px; border-bottom: 1px solid #f9f9f9; vertical-align: middle; }

.date-col strong { display: block; font-size: 14px; color: #333; }
.date-col small { color: #999; }

.sku-tag {
    background: #f1f1f1; padding: 2px 8px; border-radius: 4px; font-family: monospace;
    font-size: 12px; color: #555; margin-right: 8px;
}
.product-name { font-weight: 600; color: var(--tefa-brown); }

.badge-tipe { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.badge-masuk { background: #e8f5e9; color: var(--tefa-green); }
.badge-keluar { background: #ffebee; color: var(--tefa-red); }
.badge-rusak { background: #fff3e0; color: var(--tefa-orange); }

.text-bold { font-weight: 700; }
.text-green { color: var(--tefa-green); }
.text-red { color: var(--tefa-red); }
.text-muted { color: #999; font-size: 13px; }
.italic { font-style: italic; }

.petugas-col { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #666; }
.petugas-col i { font-size: 16px; color: #ccc; }

.empty-state { text-align: center; padding: 80px !important; }
.empty-content i { font-size: 50px; color: #eee; margin-bottom: 15px; }
.empty-content p { color: #bbb; margin: 0; }

/* Media Print */
@media print {
    aside, .sidebar, .filter-card-premium, .table-actions, .btn-reset-premium { display: none !important; }
    .mutation-report-container { padding: 0; }
    .table-card-premium { box-shadow: none; border: 1px solid #eee; }
    .custom-table-premium th { background: #eee !important; color: #000 !important; }
}
</style>

<?php include 'includes/footer.php'; ?>
