<?php
require_once '../core/Auth.php';
require_once '../models/LaporanModel.php';
Auth::requireRole('admin');

/**
 * Dashboard Main Page — Admin
 */

$pageTitle    = 'Dashboard';
$dashboardPage = true;
$pageHeading  = 'Dashboard';

$laporanModel = new LaporanModel();
$stats = $laporanModel->getSummaryStats();
$totalTransaksi = $stats['total_transaksi'] ?? 0;
$totalPendapatan = $stats['total_pendapatan'] ?? 0;

$bestSellers = $laporanModel->getBestSellers(5);

$transactions = $laporanModel->getTransactions();
$paymentStats = [
    'tunai' => 0,
    'transfer' => 0,
    'qris' => 0
];

foreach ($transactions as $trx) {
    $method = strtolower($trx['metode_bayar'] ?? '');
    if (array_key_exists($method, $paymentStats)) {
        $paymentStats[$method]++;
    } elseif ($method !== '') {
        $paymentStats[$method] = 1;
    }
}

$totalPayment = array_sum($paymentStats);
$paymentData = [];
$paymentLabels = [];

foreach ($paymentStats as $method => $count) {
    $paymentLabels[] = ucfirst($method);
    $percentage = $totalPayment > 0 ? round(($count / $totalPayment) * 100) : 0;
    $paymentData[] = $percentage;
}

// Ambil data statistik untuk grafik (default: week)
$chartStats = $laporanModel->getChartStats('week');
$chartData = $chartStats['data'];
$chartLabels = $chartStats['labels'];
$totalPendapatanGrafik = $chartStats['total'];

$dashboardData = [
    'paymentMethods' => [
        'labels' => $paymentLabels,
        'data' => $paymentData
    ],
    'salesChart' => [
        'labels' => $chartLabels,
        'data' => $chartData
    ]
];
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

        <!-- Flash Messages -->
        <?= Flash::render() ?>

        <!-- Stats Cards -->
        <section class="stats-container" aria-label="Statistics">
          <article class="stat-card">
            <div class="stat-icon icon-blue">
              <i class="fas fa-shopping-cart" aria-hidden="true"></i>
            </div>
            <div class="stat-details">
              <p>Total Transaksi</p>
              <h3 class="counter" data-target="<?= htmlspecialchars($totalTransaksi) ?>">0</h3>
            </div>
          </article>

          <article class="stat-card">
            <div class="stat-icon icon-green">
              <i class="fas fa-money-bill-wave" aria-hidden="true"></i>
            </div>
            <div class="stat-details">
              <p>Total Pendapatan</p>
              <h3>Rp <span class="counter" data-target="<?= htmlspecialchars($totalPendapatan) ?>">0</span></h3>
            </div>
          </article>

          <article class="stat-card">
            <div class="stat-icon icon-orange">
              <i class="fas fa-bread-slice" aria-hidden="true"></i>
            </div>
            <div class="stat-details">
              <p>Total Item Terjual</p>
              <h3>
                <span class="counter" data-target="560">0</span>
                <small>produk</small>
              </h3>
            </div>
          </article>

          <article class="stat-card">
            <div class="stat-icon icon-red">
              <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
            </div>
            <div class="stat-details">
              <p>Selisih Kas</p>
              <h3 class="text-danger">
                Rp <span class="counter" data-target="15000">0</span>
              </h3>
            </div>
          </article>
        </section>

        <!-- Analytics Grid -->
        <div class="analytics-grid">
          <div class="chart-card main-chart">
            <div class="card-header">
              <h4>Statistik Penjualan</h4>
              <div class="filter-tabs" role="tablist">
                <button role="tab" class="filter-btn" data-filter="day">Day</button>
                <button role="tab" class="filter-btn active" data-filter="week">Week</button>
                <button role="tab" class="filter-btn" data-filter="month">Month</button>
              </div>
            </div>
            <div class="chart-content">
              <p class="balance-label">Total Balance</p>
              <h3 class="balance-amount">
                Rp <span class="counter" data-target="<?= htmlspecialchars($totalPendapatanGrafik) ?>">0</span>
              </h3>
              <div class="chart-wrapper">
                <canvas id="salesLineChart" aria-label="Sales Statistics Chart"></canvas>
              </div>
            </div>
          </div>

          <div class="chart-card pie-chart">
            <div class="card-header">
              <h4>Metode Pembayaran</h4>
            </div>
            <div class="pie-container">
              <canvas id="paymentPieChart" aria-label="Payment Methods Chart"></canvas>
            </div>
          </div>
        </div>

        <!-- Products Table -->
        <section class="table-card">
          <div class="card-header">
            <h4>PRODUK TERLARIS</h4>
            <button class="view-all-btn">
              <span>Lihat Semua</span>
              <i class="fas fa-arrow-right"></i>
            </button>
          </div>
          <div class="table-responsive">
            <table class="custom-table">
              <thead>
                <tr>
                  <th><span class="th-content">Produk</span></th>
                  <th><span class="th-content">SKU</span></th>
                  <th><span class="th-content">Terjual</span></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($bestSellers as $index => $product): ?>
                <tr>
                  <td>
                    <div class="prod-info">
                      <div class="prod-img-wrapper" style="position:relative;">
                        <div class="img-placeholder-admin" style="width:100%; height:100%; font-size:1.2rem; display:flex; align-items:center; justify-content:center;">
                          <i class="fas fa-bread-slice"></i>
                        </div>
                        <?php if (!empty($product['image']) && $product['image'] !== 'default_product.jpg'): ?>
                          <img src="../assets/img/products/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['nama_produk']) ?>" loading="lazy" style="position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover; display:none;" onload="this.style.display='block'; this.previousElementSibling.style.display='none';">
                        <?php endif; ?>
                      </div>
                      <span><?= htmlspecialchars($product['nama_produk']) ?></span>
                    </div>
                  </td>
                  <td><code class="sku-code">SKU-<?= str_pad($index + 1, 3, '0', STR_PAD_LEFT) ?></code></td>
                  <td><span class="sold-count">x<?= htmlspecialchars($product['total_qty']) ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($bestSellers)): ?>
                <tr>
                  <td colspan="3" style="text-align: center; padding: 1rem;">Belum ada data produk terlaris.</td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
      </main>
    </div>

    <!-- ═══ MODAL: Semua Produk Terlaris ═══ -->
    <div id="modalBestSellers" class="modal-overlay" role="dialog" aria-modal="true">
        <div class="modal-box" style="max-width: 700px;">
            <div class="modal-header">
                <h3><i class="fas fa-trophy"></i> Semua Produk Terlaris</h3>
                <button class="modal-close" onclick="document.getElementById('modalBestSellers').style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" style="padding: 20px; max-height: 70vh; overflow-y: auto;">
                <div class="table-responsive">
                    <table class="custom-table" id="tableAllBestSellers">
                        <thead>
                            <tr>
                                <th>Ranking</th>
                                <th>Produk</th>
                                <th>Terjual</th>
                                <th>Total Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<script>
  window.dashboardData = <?= json_encode($dashboardData) ?>;
</script>
<?php include 'includes/footer.php'; ?>
