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
<link rel="stylesheet" href="../assets/css/gudang-laporan.css">

<section class="laporan-page">
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
</section>

<?php include 'includes/footer.php'; ?>
