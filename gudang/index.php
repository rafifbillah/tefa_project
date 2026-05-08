<?php
require_once '../core/Auth.php';
Auth::requireRole('gudang');

require_once '../models/BarangModel.php';
require_once '../models/InventoryLogModel.php';
require_once '../core/Database.php';

$barangModel = new BarangModel();
$inventoryModel = new InventoryLogModel();
$db = Database::getConnection();

// 1. Ambil Semua Produk
$allProducts = $barangModel->getAll();

$totalProducts = count($allProducts);
$totalStock = 0;
$expiredSoon = 0;
$lowStockProducts = [];

$today = new DateTime();
$today->setTime(0, 0, 0);
$sevenDaysLater = (clone $today)->modify('+7 days');

foreach ($allProducts as $p) {
    $totalStock += $p['stok'];
    
    // Stok menipis
    if ($p['stok'] < 10) {
        $lowStockProducts[] = $p;
    }
    
    // Expired soon
    if (!empty($p['exp_date'])) {
        $expDate = new DateTime($p['exp_date']);
        $expDate->setTime(0, 0, 0);
        if ($expDate >= $today && $expDate <= $sevenDaysLater) {
            $expiredSoon++;
        }
    }
}
$lowStockCount = count($lowStockProducts);

// 2. Aktivitas Terbaru (Log Mutasi)
$recentActivities = $inventoryModel->getAll();
$recentActivities = array_slice($recentActivities, 0, 5); // Limit 5

// 3. Tren Stok Bulanan (Misalnya: volume barang masuk per bulan selama 6 bulan terakhir)
$monthlyTrend = [];
for ($i = 5; $i >= 0; $i--) {
    $monthDate = date('Y-m', strtotime("-$i months"));
    $monthLabel = date('M', strtotime("-$i months"));
    
    $stmt = $db->prepare("SELECT SUM(jumlah) FROM inventory_logs WHERE tipe_mutasi = 'masuk' AND DATE_FORMAT(created_at, '%Y-%m') = ?");
    $stmt->execute([$monthDate]);
    $masuk = (int)$stmt->fetchColumn();
    
    $monthlyTrend[] = [
        'label' => $monthLabel,
        'value' => $masuk
    ];
}

$maxTrendValue = max(array_column($monthlyTrend, 'value'));
if ($maxTrendValue == 0) $maxTrendValue = 1; // Mencegah division by zero

// 4. Distribusi Kategori
$categoryStats = [];
$totalCategoryStock = 0;
foreach ($allProducts as $p) {
    $cat = !empty($p['nama_kategori']) ? $p['nama_kategori'] : 'Lainnya';
    if (!isset($categoryStats[$cat])) {
        $categoryStats[$cat] = 0;
    }
    $categoryStats[$cat] += $p['stok'];
    $totalCategoryStock += $p['stok'];
}

$donutColors = ['var(--orange)', 'var(--orange-memek)', 'var(--grey-mid)', '#10B981', '#3B82F6', '#EF4444'];
$donutGradientParts = [];
$donutLegendHTML = '';
$jsGradientMap = [];
$currentPercent = 0;
$colorIndex = 0;

foreach ($categoryStats as $name => $stock) {
    if ($totalCategoryStock > 0 && $stock > 0) {
        $percentage = round(($stock / $totalCategoryStock) * 100);
        // Pastikan persentase pas 100 di item terakhir jika mau sempurna, tapi round sudah cukup.
        if ($percentage == 0) continue;
        
        $nextPercent = $currentPercent + $percentage;
        $color = $donutColors[$colorIndex % count($donutColors)];
        
        $donutGradientParts[] = "$color $currentPercent% $nextPercent%";
        $donutLegendHTML .= "<div class=\"legend-item\"><span class=\"dot\" style=\"background-color: $color;\"></span><span class=\"label\">" . htmlspecialchars($name) . "</span><span class=\"value\">$percentage%</span></div>";
        
        $jsGradientMap[] = [
            'label' => $name,
            'value' => "$percentage%",
            'color' => $color,
            'maxPct' => $nextPercent
        ];
        
        $currentPercent = $nextPercent;
        $colorIndex++;
    }
}
$conicGradient = !empty($donutGradientParts) ? "conic-gradient(" . implode(", ", $donutGradientParts) . ")" : "conic-gradient(#eee 0% 100%)";


/**
 * Dashboard Gudang — TEFA Bakery & Coffee
 */

$page        = 'dashboard';
$pageTitle   = 'Dashboard Inventori';

include 'includes/header.php';
?>
<section class="dashboard-page">
    
    <div class="summary-cards">
        <div class="card card-orange">
            <div class="card-title">Total Produk</div>
            <div class="card-value"><?= $totalProducts ?></div>
            <div class="card-subtitle">Barang terdaftar</div>
            <i class="fa-solid fa-box-open card-icon"></i>
        </div>
        <div class="card">
            <div class="card-title">Total Stok</div>
            <div class="card-value"><?= number_format($totalStock, 0, ',', '.') ?></div>
            <div class="card-subtitle success-text">Sisa stok gudang</div>
            <i class="fa-solid fa-boxes-stacked card-icon success-text"></i>
        </div>
        <div class="card card-pink">
            <div class="card-title">Expired Soon</div>
            <div class="card-value"><?= $expiredSoon ?></div>
            <div class="card-subtitle text-pink">Dalam 7 hari</div>
            <i class="fa-solid fa-warning card-icon text-pink"></i>
        </div>
        <div class="card card-orange-light">
            <div class="card-title">Stok Menipis</div>
            <div class="card-value"><?= $lowStockCount ?></div>
            <div class="card-subtitle text-orange">Perlu segera restok</div>
            <i class="fa-solid fa-arrow-trend-down card-icon text-orange"></i>
        </div>
    </div>

    <div class="graphs-row">
        <div class="graph-card">
            <h3 class="graph-title">Tren Stok Masuk Bulanan</h3>
            <div class="graph-container bar-chart">
                <div class="chart-y-axis">
                    <span><?= ceil($maxTrendValue) ?></span>
                    <span><?= ceil($maxTrendValue * 0.75) ?></span>
                    <span><?= ceil($maxTrendValue * 0.5) ?></span>
                    <span><?= ceil($maxTrendValue * 0.25) ?></span>
                    <span>0</span>
                </div>
                <div class="chart-bars">
                    <?php foreach ($monthlyTrend as $trend): ?>
                        <?php $heightPct = min(100, round(($trend['value'] / $maxTrendValue) * 100)); ?>
                        <div class="bar" style="height: <?= $heightPct ?>%" data-value="<?= $trend['value'] ?>"><span class="label"><?= $trend['label'] ?></span></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="graph-card">
            <h3 class="graph-title">Distribusi Kategori</h3>
            <div class="graph-container donut-chart">
                <div class="donut-display" style="background: <?= $conicGradient ?>;">
                    <div class="donut-center"></div>
                </div>
                <div class="donut-legend">
                    <?= $donutLegendHTML ?: '<div class="legend-item"><span class="label">Belum Ada Data</span></div>' ?>
                </div>
            </div>
        </div>
    </div>

    <div class="lists-row">
        <div class="list-card">
            <h3 class="list-title">Aktivitas Terbaru</h3>
            <ul class="activity-list">
                <?php if (empty($recentActivities)): ?>
                    <li class="activity-item"><span class="activity-marker grey"></span> Belum ada aktivitas mutasi.</li>
                <?php else: ?>
                    <?php foreach ($recentActivities as $act): ?>
                        <?php 
                        $markerClass = 'grey';
                        $actionText = 'Disesuaikan';
                        if ($act['tipe_mutasi'] == 'masuk') { $markerClass = 'green'; $actionText = 'Ditambahkan'; }
                        elseif ($act['tipe_mutasi'] == 'keluar') { $markerClass = 'orange'; $actionText = 'Dikeluarkan'; }
                        elseif ($act['tipe_mutasi'] == 'rusak') { $markerClass = 'red'; $actionText = 'Dilaporkan rusak'; }
                        elseif ($act['tipe_mutasi'] == 'retur') { $markerClass = 'blue'; $actionText = 'Diretur'; }
                        
                        $timeAgo = strtotime($act['created_at']);
                        $diff = time() - $timeAgo;
                        if ($diff < 60) $timeStr = "Baru saja";
                        elseif ($diff < 3600) $timeStr = floor($diff/60) . " menit lalu";
                        elseif ($diff < 86400) $timeStr = floor($diff/3600) . " jam lalu";
                        else $timeStr = floor($diff/86400) . " hari lalu";
                        ?>
                        <li class="activity-item">
                            <span class="activity-marker <?= $markerClass ?>"></span> 
                            <strong><?= htmlspecialchars($act['nama_produk']) ?></strong>. <?= $actionText ?> (<?= $act['jumlah'] ?> unit). 
                            <span class="time"><?= $timeStr ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

        <div class="list-card">
            <h3 class="list-title">Peringatan Stok Rendah</h3>
            <div class="warning-table">
                <table>
                    <tbody>
                        <?php if (empty($lowStockProducts)): ?>
                            <tr><td colspan="4" style="text-align: center; color: #888; padding: 20px;">Semua stok aman.</td></tr>
                        <?php else: ?>
                            <?php foreach ($lowStockProducts as $lp): ?>
                            <tr>
                                <td><?= htmlspecialchars($lp['nama_produk']) ?></td>
                                <td class="category">Kategori: <?= htmlspecialchars($lp['nama_kategori'] ?? 'Lainnya') ?></td>
                                <td class="stock-warning"><?= $lp['stok'] ?> unit</td>
                                <td class="min-stock">Min: 10</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        /* Animasi Card Muncul / Refresh */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .card, .graph-card, .list-card {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
        }
        
        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        .card:nth-child(4) { animation-delay: 0.4s; }
        .graphs-row .graph-card:nth-child(1) { animation-delay: 0.3s; }
        .graphs-row .graph-card:nth-child(2) { animation-delay: 0.4s; }
        .lists-row .list-card:nth-child(1) { animation-delay: 0.4s; }
        .lists-row .list-card:nth-child(2) { animation-delay: 0.5s; }

        /* Donut Chart Animation */
        @keyframes popIn {
            from { opacity: 0; transform: scale(0.5); }
            to { opacity: 1; transform: scale(1); }
        }

        .donut-display {
            opacity: 0;
            animation: popIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            animation-delay: 0.6s;
        }
    </style>

    <script>
        window.donutMap = <?= json_encode($jsGradientMap) ?>;

        document.addEventListener('DOMContentLoaded', () => {
            const bars = document.querySelectorAll('.chart-bars .bar');
            
            const tooltip = document.createElement('div');
            tooltip.className = 'bar-tooltip';
            tooltip.style.position = 'absolute';
            tooltip.style.backgroundColor = '#333';
            tooltip.style.color = '#fff';
            tooltip.style.padding = '8px 12px';
            tooltip.style.borderRadius = '6px';
            tooltip.style.fontSize = '0.85rem';
            tooltip.style.fontWeight = 'bold';
            tooltip.style.pointerEvents = 'none';
            tooltip.style.opacity = '0';
            tooltip.style.transition = 'opacity 0.2s';
            tooltip.style.zIndex = '1000';
            tooltip.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
            document.body.appendChild(tooltip);

            bars.forEach(bar => {
                // Animasi saat load / refresh untuk Bar Chart
                const targetHeight = bar.style.height;
                bar.style.height = '0%';
                setTimeout(() => {
                    bar.style.transition = 'height 1s ease-out';
                    bar.style.height = targetHeight;
                }, 500);

                // Tooltip Informasinya (Bar Chart)
                bar.addEventListener('mouseenter', (e) => {
                    const value = bar.getAttribute('data-value') || '';
                    const label = bar.querySelector('.label').textContent || '';
                    tooltip.innerHTML = `<span style="color: #ffd166;">Bulan: ${label}</span><br/>Barang Masuk: ${value} unit`;
                    tooltip.style.opacity = '1';
                });

                bar.addEventListener('mousemove', (e) => {
                    tooltip.style.left = (e.pageX + 15) + 'px';
                    tooltip.style.top = (e.pageY - 40) + 'px';
                });

                bar.addEventListener('mouseleave', () => {
                    tooltip.style.opacity = '0';
                });
                
                // Hover effect scaling on bars
                bar.addEventListener('mouseover', () => {
                    bar.style.filter = 'brightness(1.1)';
                });
                bar.addEventListener('mouseout', () => {
                    bar.style.filter = 'brightness(1)';
                });
            });

            // Tooltip untuk Distribusi Kategori (Donut Chart)
            const donutDisplay = document.querySelector('.donut-display');
            if (donutDisplay && window.donutMap && window.donutMap.length > 0) {
                donutDisplay.addEventListener('mousemove', (e) => {
                    const rect = donutDisplay.getBoundingClientRect();
                    const centerX = rect.left + rect.width / 2;
                    const centerY = rect.top + rect.height / 2;
                    const x = e.clientX - centerX;
                    const y = e.clientY - centerY;
                    
                    // Jarak kursor dari pusat (untuk menghindari tooltip muncul saat kursor di lubang tengah donut)
                    const distance = Math.sqrt(x*x + y*y);
                    const radius = rect.width / 2;
                    
                    if (distance < radius * 0.4) {
                        tooltip.style.opacity = '0';
                        return;
                    }

                    // Menghitung derajat/sudut kursor (0 - 360), 0 derajat ada di arah jam 12
                    let angle = Math.atan2(y, x) * 180 / Math.PI;
                    angle += 90; // geser nol derajat ke atas
                    if (angle < 0) angle += 360;
                    
                    const pct = (angle / 360) * 100;

                    let label = 'Lainnya';
                    let value = '0%';
                    let color = '#ccc';

                    for (let i = 0; i < window.donutMap.length; i++) {
                        if (pct <= window.donutMap[i].maxPct) {
                            label = window.donutMap[i].label;
                            value = window.donutMap[i].value;
                            color = window.donutMap[i].color;
                            break;
                        }
                    }

                    tooltip.innerHTML = `<span style="font-size: 1.1em; color: ${color};">&#9679;</span> <span style="color: #fff;">${label}</span><br/><span style="color: #bbb; font-weight: normal; font-size:0.8rem;">Persentase:</span> ${value}`;
                    tooltip.style.opacity = '1';
                    tooltip.style.left = (e.pageX + 15) + 'px';
                    tooltip.style.top = (e.pageY - 40) + 'px';
                });

                donutDisplay.addEventListener('mouseleave', () => {
                    tooltip.style.opacity = '0';
                });
            }

            // Tambahkan animasi hover efek untuk semua card pendukung visual yg dinamis
            const cards = document.querySelectorAll('.card, .graph-card, .list-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-5px)';
                    card.style.boxShadow = '0 6px 12px rgba(0,0,0,0.1)';
                    card.style.transition = 'all 0.3s ease';
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = '';
                    card.style.boxShadow = '';
                });
            });
        });
    </script>
</section>
<?php include 'includes/footer.php'; ?>