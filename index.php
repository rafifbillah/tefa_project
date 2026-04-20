<?php
/**
 * Dashboard Main Page
 * TEFA Bakery and Coffee Dashboard
 */
$pageTitle = 'Dashboard';
$dashboardPage = true;
$pageHeading = 'Dashboard';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

        <!-- Stats Cards -->
        <section class="stats-container" aria-label="Statistics">
          <article class="stat-card">
            <div class="stat-icon icon-blue">
              <i class="fas fa-shopping-cart" aria-hidden="true"></i>
            </div>
            <div class="stat-details">
              <p>Total Transaksi</p>
              <h3 class="counter" data-target="128">0</h3>
            </div>
          </article>

          <article class="stat-card">
            <div class="stat-icon icon-green">
              <i class="fas fa-money-bill-wave" aria-hidden="true"></i>
            </div>
            <div class="stat-details">
              <p>Total Pendapatan</p>
              <h3>Rp <span class="counter" data-target="4250000">0</span></h3>
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
                <button role="tab" class="filter-btn" data-filter="day">
                  Day
                </button>
                <button role="tab" class="filter-btn active" data-filter="week">
                  Week
                </button>
                <button role="tab" class="filter-btn" data-filter="month">
                  Month
                </button>
              </div>
            </div>
            <div class="chart-content">
              <p class="balance-label">Total Balance</p>
              <h3 class="balance-amount">
                <span id="balanceValue">2,982</span>
                <span class="trend-up"
                  ><i class="fas fa-arrow-up"></i> 2.45%</span
                >
              </h3>
              <div class="chart-wrapper">
                <canvas
                  id="salesLineChart"
                  aria-label="Sales Statistics Chart"
                ></canvas>
              </div>
            </div>
          </div>

          <div class="chart-card pie-chart">
            <div class="card-header">
              <h4>Metode Pembayaran</h4>
            </div>
            <div class="pie-container">
              <canvas
                id="paymentPieChart"
                aria-label="Payment Methods Chart"
              ></canvas>
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
                <tr>
                  <td>
                    <div class="prod-info">
                      <div class="prod-img-wrapper">
                        <img
                          src="https://images.unsplash.com/photo-1558303035-d41300b031b6?w=80&h=80&fit=crop"
                          alt="Blueberry Muffin"
                          loading="lazy"
                        />
                      </div>
                      <span>Blueberry Muffin</span>
                    </div>
                  </td>
                  <td><code class="sku-code">BM-102</code></td>
                  <td><span class="sold-count">x214</span></td>
                </tr>
                <tr>
                  <td>
                    <div class="prod-info">
                      <div class="prod-img-wrapper">
                        <img
                          src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=80&h=80&fit=crop"
                          alt="Choco Cupcake"
                          loading="lazy"
                        />
                      </div>
                      <span>Choco Cupcake</span>
                    </div>
                  </td>
                  <td><code class="sku-code">CC-089</code></td>
                  <td><span class="sold-count">x145</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </main>
    </div>

<?php include 'includes/footer.php'; ?>
