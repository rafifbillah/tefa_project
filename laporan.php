<?php
/**
 * Users Management Page
 * TEFA Bakery and Coffee Users Management
 */
$pageTitle = 'Laporan';
$dashboardPage = true;
$pageHeading = 'Laporan Management';
$additionalCSS = 'assets/css/style_laporan.css'; // Hubungkan CSS laporan
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

    <div class="report-container">
    <section class="filter-card">
        <form class="filter-form">
            <div class="input-group">
                <label>Dari Tanggal</label>
                <input type="date" value="2026-04-01">
            </div>
            <div class="input-group">
                <label>Sampai Tanggal</label>
                <input type="date" value="2026-04-20">
            </div>
            <div class="input-group">
                <label>Metode</label>
                <select>
                    <option>Semua</option>
                    <option>Tunai</option>
                    <option>Transfer</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Tampilkan</button>
        </form>
    </section>

    <section class="stats-grid">
        <div class="card-stat border-gray">
            <p>TOTAL TRANSAKSI</p>
            <h3>5</h3>
        </div>
        <div class="card-stat border-green">
            <p>GRAND TOTAL</p>
            <h3>Rp 750.000</h3>
        </div>
        <div class="card-stat border-orange">
            <p>TUNAI</p>
            <h3>Rp 174.000</h3>
        </div>
        <div class="card-stat border-blue">
            <p>TRANSFER + QRIS</p>
            <h3>Rp 576.000</h3>
        </div>
    </section>

    <div class="report-grid">
        <section class="table-card-report">
            <div class="card-header-flex">
                <h4>Detail Transaksi (5)</h4>
                <div class="export-btns">
                    <button class="btn-export csv"><i class="fas fa-file-csv"></i> CSV/Excel</button>
                    <button class="btn-export pdf"><i class="fas fa-file-pdf"></i> Cetak PDF</button>
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
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>TRX-20260406-0E1A1</td>
                            <td>06/04/2026</td>
                            <td>Administrator</td>
                            <td><span class="badge tunai">TUNAI</span></td>
                            <td><strong>Rp 32.000</strong></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>TRX-20260406-B9A03</td>
                            <td>06/04/2026</td>
                            <td>Administrator</td>
                            <td><span class="badge transfer">TRANSFER</span></td>
                            <td><strong>Rp 528.000</strong></td>
                        </tr>
                        </tbody>
                </table>
            </div>
        </section>

        <aside class="best-seller-card">
            <h4>Produk Terlaris</h4>
            <ul class="product-list">
                <li>
                    <span class="rank">1</span>
                    <div class="p-info">
                        <strong>Matcha Latte</strong>
                        <small>Rp 660.000</small>
                    </div>
                    <span class="p-count">30 pcs</span>
                </li>
                <li>
                    <span class="rank">2</span>
                    <div class="p-info">
                        <strong>Croissant</strong>
                        <small>Rp 36.000</small>
                    </div>
                    <span class="p-count">2 pcs</span>
                </li>
            </ul>
        </aside>
    </div>
</div>
        
      </main>
    </div>

<?php include 'includes/footer.php'; ?>
