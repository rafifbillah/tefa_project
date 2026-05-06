<?php
require_once '../core/Auth.php';
Auth::requireRole('admin');
/**
 * Laporan Management — Admin (Dynamic Edition)
 */

require_once __DIR__ . '/../core/Flash.php';
require_once __DIR__ . '/../models/LaporanModel.php';

$laporanModel = new LaporanModel();

// Get filters from GET
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate   = $_GET['end_date'] ?? date('Y-m-d');
$method    = $_GET['method'] ?? 'Semua';

// Fetch Data
$transactions = $laporanModel->getTransactions($startDate, $endDate, $method);
$stats        = $laporanModel->getSummaryStats($startDate, $endDate, $method);
$bestSellers  = $laporanModel->getBestSellers(5);

// Specific Method Stats for Cards
$tunaiStats    = $laporanModel->getSummaryStats($startDate, $endDate, 'tunai');
$nonTunaiStats = $laporanModel->getSummaryStats($startDate, $endDate, 'transfer'); // Simplified for example
$qrisStats     = $laporanModel->getSummaryStats($startDate, $endDate, 'qris');

$totalNonTunai = ($nonTunaiStats['total_pendapatan'] ?? 0) + ($qrisStats['total_pendapatan'] ?? 0);

$pageTitle    = 'Laporan';
$dashboardPage = true;
$pageHeading  = 'Laporan Transaksi';
$additionalCSS = '../assets/css/admin-style_laporan.css';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

    <div class="report-container">
    <!-- ═══ FILTER SECTION ═══ -->
    <section class="filter-card">
        <form class="filter-form" method="GET" action="">
            <div class="input-group">
                <label>Dari Tanggal</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
            </div>
            <div class="input-group">
                <label>Sampai Tanggal</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
            </div>
            <div class="input-group">
                <label>Metode</label>
                <select name="method">
                    <option value="Semua" <?= $method === 'Semua' ? 'selected' : '' ?>>Semua</option>
                    <option value="tunai" <?= $method === 'tunai' ? 'selected' : '' ?>>Tunai</option>
                    <option value="transfer" <?= $method === 'transfer' ? 'selected' : '' ?>>Transfer</option>
                    <option value="qris" <?= $method === 'qris' ? 'selected' : '' ?>>QRIS</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Tampilkan</button>
            <a href="laporan.php" class="btn-reset">Reset</a>
        </form>
    </section>

    <!-- ═══ STATS CARDS ═══ -->
    <section class="stats-grid">
        <div class="card-stat border-gray">
            <p>TOTAL TRANSAKSI</p>
            <h3><?= number_format($stats['total_transaksi'] ?? 0, 0, ',', '.') ?></h3>
        </div>
        <div class="card-stat border-green">
            <p>GRAND TOTAL</p>
            <h3>Rp <?= number_format($stats['total_pendapatan'] ?? 0, 0, ',', '.') ?></h3>
        </div>
        <div class="card-stat border-orange">
            <p>TUNAI</p>
            <h3>Rp <?= number_format($tunaiStats['total_pendapatan'] ?? 0, 0, ',', '.') ?></h3>
        </div>
        <div class="card-stat border-blue">
            <p>TRANSFER + QRIS</p>
            <h3>Rp <?= number_format($totalNonTunai, 0, ',', '.') ?></h3>
        </div>
    </section>

    <!-- ═══ DETAILS & BEST SELLERS ═══ -->
    <div class="report-grid">
        <section class="table-card-report">
            <div class="card-header-flex">
                <h4>Detail Transaksi (<?= count($transactions) ?>)</h4>
                <div class="export-btns" style="display:flex; gap:10px;">
                    <a href="export_excel.php?<?= http_build_query($_GET) ?>" class="btn-export btn-excel-new">
                        <i class="fas fa-file-excel"></i>
                        <span>Export Excel</span>
                    </a>
                    <button class="btn-export btn-export-excel" onclick="window.print()">
                        <i class="fas fa-print"></i>
                        <span>Cetak Laporan</span>
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>KODE</th>
                            <th>TANGGAL</th>
                            <th>KASIR</th>
                            <th>METODE</th>
                            <th>TOTAL</th>
                            <th style="text-align: center;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data transaksi pada periode ini.</td>
                        </tr>
                        <?php else: ?>
                            <?php $i = 1; foreach($transactions as $t): 
                                $isVoid = ($t['status'] ?? '') === 'void';
                                $rowStyle = $isVoid ? 'opacity: 0.5; text-decoration: line-through; background: #fdf2f2;' : '';
                            ?>
                            <tr style="<?= $rowStyle ?>" id="row-<?= $t['id'] ?>">
                                <td><?= $i++ ?></td>
                                <td><span class="trx-code"><?= htmlspecialchars($t['transaction_id'] ?? '') ?></span></td>
                                <td><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
                                <td><?= htmlspecialchars($t['kasir_nama'] ?? ($t['kasir_username'] ?? 'System')) ?></td>
                                <td>
                                    <span class="badge <?= strtolower($t['metode_bayar']) ?>">
                                        <?= strtoupper($t['metode_bayar']) ?>
                                    </span>
                                </td>
                                <td><strong>Rp <?= number_format($t['total_harga'], 0, ',', '.') ?></strong></td>
                                <td style="text-align: center;">
                                    <?php if ($isVoid): ?>
                                        <span style="color: #dc2626; font-weight: bold; font-size: 12px; border: 1px solid #fca5a5; padding: 2px 8px; border-radius: 4px; background: #fee2e2;">Dibatalkan</span>
                                    <?php else: ?>
                                        <button type="button" onclick="voidTransaction(<?= $t['id'] ?>)" class="btn-void" title="Batalkan Transaksi">
                                            <i class="fa-solid fa-ban"></i> Void
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="best-seller-card">
            <h4>Produk Terlaris</h4>
            <ul class="product-list">
                <?php if (empty($bestSellers)): ?>
                    <li class="empty">Belum ada data penjualan produk.</li>
                <?php else: ?>
                    <?php $rank = 1; foreach($bestSellers as $ps): ?>
                    <li>
                        <span class="rank"><?= $rank++ ?></span>
                        <div class="p-info">
                            <strong><?= htmlspecialchars($ps['nama_produk']) ?></strong>
                            <small>Sales: Rp <?= number_format($ps['total_sales'], 0, ',', '.') ?></small>
                        </div>
                        <span class="p-count"><?= $ps['total_qty'] ?> pcs</span>
                    </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </aside>
    </div>
</div>

<style>
.btn-reset {
    text-decoration: none;
    background: #f1f1f1;
    color: #333;
    padding: 10px 15px;
    border-radius: 6px;
    font-size: 13px;
    margin-left: 5px;
}
.trx-code {
    font-family: monospace;
    font-weight: 600;
    color: #444;
}
.badge.qris { background: #e0f2fe; color: #0369a1; }
.text-center { text-align: center; padding: 20px !important; color: #999; }

.btn-excel-new {
    text-decoration: none;
    background: #107c41;
    color: #fff;
    border: none;
    padding: 8px 15px;
    border-radius: 6px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: 0.3s;
}
.btn-excel-new:hover {
    background: #0a5a2f;
    transform: translateY(-2px);
}
.btn-void {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fca5a5;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.btn-void:hover {
    background: #dc2626;
    color: white;
}
</style>

<script>
async function voidTransaction(id) {
    const confirmation = window.confirm("Peringatan: Apakah Anda yakin ingin membatalkan transaksi ini?\nStok barang akan dikembalikan otomatis ke gudang, dan pendapatan akan dikoreksi.");
    
    if (!confirmation) return;

    try {
        const response = await fetch('ajax_void.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });

        const result = await response.json();

        if (result.success) {
            alert("Sukses: " + result.message);
            window.location.reload(); // Refresh untuk mengupdate status UI dan nominal secara bersih
        } else {
            alert("Gagal: " + result.message);
        }
    } catch (error) {
        alert("Terjadi kesalahan sistem saat menghubungi server.");
        console.error(error);
    }
}
</script>
        
      </main>
    </div>

<?php include 'includes/footer.php'; ?>
