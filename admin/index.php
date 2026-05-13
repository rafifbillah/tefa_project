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

// Persiapkan data untuk Line Chart (7 hari terakhir)
$salesData = [];
$salesLabels = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $salesLabels[] = date('d M', strtotime($date));
    $salesData[$date] = 0;
}

foreach ($transactions as $trx) {
    // Abaikan jika ada status void
    if (isset($trx['status']) && $trx['status'] === 'void') continue;
    
    $date = date('Y-m-d', strtotime($trx['created_at']));
    if (isset($salesData[$date])) {
        $salesData[$date] += (float)$trx['total_harga'];
    }
}
$chartData = array_values($salesData);

$dashboardData = [
    'paymentMethods' => [
        'labels' => $paymentLabels,
        'data' => $paymentData
    ],
    'salesChart' => [
        'labels' => $salesLabels,
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
                Rp <span class="counter" data-target="<?= htmlspecialchars($totalPendapatan) ?>">0</span>
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
                      <div class="prod-img-wrapper">
                        <img src="https://images.unsplash.com/photo-1558303035-d41300b031b6?w=80&h=80&fit=crop" alt="<?= htmlspecialchars($product['nama_produk']) ?>" loading="lazy" />
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

<script>
  window.dashboardData = <?= json_encode($dashboardData) ?>;
</script>
<?php include 'includes/footer.php'; ?>
