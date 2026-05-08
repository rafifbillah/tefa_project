<?php
require_once '../core/Auth.php';
Auth::requireRole('kasir');

require_once '../models/LaporanModel.php';
require_once '../models/BarangModel.php';
require_once '../core/ShiftManager.php';
require_once '../core/Database.php';

$laporanModel = new LaporanModel();
$barangModel = new BarangModel();
$shiftManager = new ShiftManager();
$db = Database::getConnection();

$userId = $_SESSION['user_id'];
$activeShift = $shiftManager->getActiveShift($userId);

// 1. Shift Handling & Summary Stats
$shiftStartTime = $activeShift ? $activeShift['start_time'] : date('Y-m-d 00:00:00');
$todayStats = $laporanModel->getSummaryStats($shiftStartTime, date('Y-m-d 23:59:59'));

$yesterdayDate = date('Y-m-d', strtotime('-1 day', strtotime($shiftStartTime)));
$yesterdayStats = $laporanModel->getSummaryStats($yesterdayDate . ' 00:00:00', $yesterdayDate . ' 23:59:59');

$totalPendapatan = $todayStats['total_pendapatan'];
$totalTerjual = $todayStats['total_transaksi'];

$pendapatanTrend = 0;
if ($yesterdayStats['total_pendapatan'] > 0) {
    $pendapatanTrend = (($totalPendapatan - $yesterdayStats['total_pendapatan']) / $yesterdayStats['total_pendapatan']) * 100;
} elseif ($totalPendapatan > 0) {
    $pendapatanTrend = 100;
}

$terjualTrend = 0;
if ($yesterdayStats['total_transaksi'] > 0) {
    $terjualTrend = (($totalTerjual - $yesterdayStats['total_transaksi']) / $yesterdayStats['total_transaksi']) * 100;
} elseif ($totalTerjual > 0) {
    $terjualTrend = 100;
}

// 2. Low Stock Products (Butuh Restock)
$allProducts = $barangModel->getAll();
$lowStockProducts = [];
foreach ($allProducts as $p) {
    if ($p['stok'] < 10) {
        $lowStockProducts[] = $p;
    }
}
$lowStockCount = count($lowStockProducts);
$lowStockDisplay = array_slice($lowStockProducts, 0, 4);

// 3. Real-Time Chart Integration (7 Days)
$chartLabels = [];
$chartData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $days = ['Sun' => 'Min', 'Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab'];
    $chartLabels[] = $days[date('D', strtotime($date))];
    $dayStats = $laporanModel->getSummaryStats($date . ' 00:00:00', $date . ' 23:59:59');
    $chartData[] = (int)$dayStats['total_pendapatan'];
}

// 4. Donut Chart (Kategori Menu)
$stmt = $db->query("
    SELECT c.nama_kategori, SUM(td.jumlah * td.harga_satuan) as total
    FROM transaction_details td
    JOIN products p ON td.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    JOIN transactions t ON td.transaction_id = t.id
    WHERE t.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND (t.status != 'void' OR t.status IS NULL)
    GROUP BY p.category_id
");
$kategoriSales = $stmt->fetchAll(PDO::FETCH_ASSOC);

$donutLabels = [];
$donutData = [];
$donutColors = ['#D97706', '#382216', '#10B981', '#3B82F6', '#EF4444', '#8B5CF6'];
$totalSalesKategori = 0;
foreach ($kategoriSales as $ks) {
    $totalSalesKategori += $ks['total'];
}

$donutPercentages = [];
foreach ($kategoriSales as $index => $ks) {
    $nama = $ks['nama_kategori'] ?: 'Lainnya';
    $donutLabels[] = $nama;
    $percentage = $totalSalesKategori > 0 ? round(($ks['total'] / $totalSalesKategori) * 100) : 0;
    $donutData[] = $percentage;
    $donutPercentages[] = [
        'nama' => $nama,
        'percentage' => $percentage,
        'color' => $donutColors[$index % count($donutColors)]
    ];
}

if (empty($donutData)) {
    $donutLabels = ['Belum Ada Data'];
    $donutData = [100];
    $donutPercentages[] = ['nama' => 'Belum Ada Data', 'percentage' => 100, 'color' => '#D1D5DB'];
}

/**
 * Dashboard Kasir — TEFA Bakery & Coffee
 */

$page_title     = "Dashboard";
$page_subtitle  = "RINGKASAN AKTIVITAS TOKO";
include 'includes/header.php';
?>

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 overflow-y-auto bg-[#F8FAFC]">
    
    <?php include 'includes/topbar.php'; ?>

    <div class="max-w-7xl mx-auto px-8 pb-8 mt-6">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 border border-gray-100 text-center relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-orange-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <h3 class="text-gray-500 text-xs font-bold mb-3 uppercase tracking-widest relative z-10">Total Pendapatan</h3>
                <p class="text-4xl font-black text-gray-900 mb-2 relative z-10">Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></p>
                <?php $trendColor = $pendapatanTrend >= 0 ? 'text-green-500' : 'text-red-500'; ?>
                <?php $trendIcon = $pendapatanTrend >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'; ?>
                <p class="text-sm <?= $trendColor ?> font-bold flex items-center justify-center gap-1 relative z-10">
                    <i class="fa-solid <?= $trendIcon ?>"></i> <?= abs(round($pendapatanTrend)) ?>% Dari Kemarin
                </p>
            </div>
            
            <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 border border-gray-100 text-center relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-orange-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <h3 class="text-gray-500 text-xs font-bold mb-3 uppercase tracking-widest relative z-10">Total Terjual</h3>
                <p class="text-4xl font-black text-gray-900 mb-2 relative z-10"><?= number_format($totalTerjual, 0, ',', '.') ?></p>
                <?php $trendColorTerjual = $terjualTrend >= 0 ? 'text-[#D97706]' : 'text-red-500'; ?>
                <?php $trendIconTerjual = $terjualTrend >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'; ?>
                <p class="text-sm <?= $trendColorTerjual ?> font-bold flex items-center justify-center gap-1 relative z-10">
                    <i class="fa-solid <?= $trendIconTerjual ?>"></i> <?= abs(round($terjualTrend)) ?>% Dari Kemarin
                </p>
            </div>
            
            <a href="barang.php" class="block bg-gradient-to-br from-red-50 to-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 border border-red-100 text-center relative overflow-hidden group cursor-pointer">
                <h3 class="text-red-500 text-xs font-bold mb-3 uppercase tracking-widest relative z-10">Peringatan Restock</h3>
                <p class="text-4xl font-black text-red-600 mb-2 relative z-10"><?= $lowStockCount ?></p>
                <p class="text-sm text-red-600 font-bold flex items-center justify-center gap-1 relative z-10"><i class="fa-solid fa-triangle-exclamation"></i> Menu Menipis</p>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 lg:col-span-2">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">Grafik Penjualan</h3>
                        <p class="text-sm text-gray-500 font-medium mt-1">Total Pendapatan Kotor</p>
                    </div>
                    <span class="bg-orange-50 text-[#D97706] px-4 py-2 rounded-lg text-sm font-bold border border-orange-100">7 Hari Terakhir</span>
                </div>
                <div class="h-72 relative">
                    <canvas id="barChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                <div class="flex justify-between items-center mb-8">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight">Butuh Restock</h3>
                    <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center">
                        <i class="fa-solid fa-triangle-exclamation text-red-500 text-lg"></i>
                    </div>
                </div>
                <div class="space-y-4 flex-1">
                    <?php if (empty($lowStockDisplay)): ?>
                        <p class="text-center text-gray-500 text-sm mt-10">Semua stok aman.</p>
                    <?php else: ?>
                        <?php foreach($lowStockDisplay as $item): ?>
                        <div class="flex justify-between items-center p-4 bg-gray-50/50 hover:bg-orange-50/50 transition-colors rounded-xl border border-gray-100 cursor-pointer group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gray-200 group-hover:bg-orange-200 transition-colors flex items-center justify-center text-xl font-bold text-gray-400 group-hover:text-orange-600">
                                    <?= strtoupper(substr($item['nama_produk'], 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900"><?= htmlspecialchars($item['nama_produk']) ?></p>
                                    <p class="text-xs font-medium text-gray-500"><?= htmlspecialchars($item['nama_kategori'] ?? 'Tidak ada kategori') ?></p>
                                </div>
                            </div>
                            <span class="bg-red-100 text-red-600 px-3 py-1 rounded-md font-bold text-xs">Sisa <?= $item['stok'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button onclick="openStockModal()" class="w-full mt-6 py-3 rounded-xl font-bold text-[#D97706] bg-orange-50 hover:bg-orange-100 transition-colors text-sm text-center block">Lihat Semua Data</button>
            </div>
        </div>

        <!-- Modal Stok Barang -->
        <div id="stockModal" class="fixed inset-0 z-[60] hidden overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeStockModal()">
                    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-white/20">
                    <div class="bg-white px-8 pt-8 pb-8">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 tracking-tight">Status Stok Inventori</h3>
                                <p class="text-sm text-gray-500 font-medium">Data real-time ketersediaan seluruh produk</p>
                            </div>
                            <button onclick="closeStockModal()" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all">
                                <i class="fa-solid fa-xmark text-xl"></i>
                            </button>
                        </div>
                        <div class="max-h-[60vh] overflow-y-auto rounded-2xl border border-gray-100 custom-scrollbar">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50/50 sticky top-0 backdrop-blur-md z-10">
                                    <tr class="text-gray-500 text-[10px] font-bold uppercase tracking-widest">
                                        <th class="px-6 py-4">Produk & SKU</th>
                                        <th class="px-6 py-4 text-center">Kategori</th>
                                        <th class="px-6 py-4 text-right">Stok Tersedia</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($allProducts as $product): ?>
                                    <tr class="hover:bg-orange-50/30 transition-colors group">
                                        <td class="px-6 py-4">
                                            <p class="font-bold text-gray-900 group-hover:text-[#D97706] transition-colors"><?= htmlspecialchars($product['nama_produk']) ?></p>
                                            <p class="text-[10px] font-mono text-gray-400"><?= htmlspecialchars($product['sku']) ?></p>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-[10px] font-bold uppercase">
                                                <?= htmlspecialchars($product['nama_kategori'] ?? 'Lainnya') ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <?php 
                                            $isLow = $product['stok'] < 10;
                                            $stokClass = $isLow ? 'text-red-600 bg-red-50 ring-1 ring-red-100' : 'text-green-600 bg-green-50 ring-1 ring-green-100';
                                            ?>
                                            <span class="<?= $stokClass ?> px-3 py-1 rounded-lg font-black text-sm inline-flex items-center gap-2">
                                                <?php if($isLow): ?><div class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></div><?php endif; ?>
                                                <?= $product['stok'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <button onclick="closeStockModal()" class="w-full mt-6 py-4 rounded-2xl font-bold bg-gray-900 text-white hover:bg-gray-800 transition-all shadow-lg shadow-gray-200">Tutup Laporan</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col lg:flex-row items-center gap-12 lg:gap-24">
            <div class="w-64 h-64 relative flex-shrink-0">
                <canvas id="donutChart"></canvas>
                <div class="absolute inset-0 flex items-center justify-center flex-col">
                    <span class="text-5xl font-black text-gray-900 tracking-tighter"><?= !empty($donutPercentages) ? $donutPercentages[0]['percentage'] : 0 ?>%</span>
                    <span class="text-xs font-bold text-gray-400 mt-1">TERBESAR</span>
                </div>
            </div>
            <div class="flex-1 w-full">
                <h3 class="text-3xl font-black text-gray-900 mb-2 tracking-tight">Distribusi Kategori Menu</h3>
                <p class="text-gray-500 font-medium mb-10">Persentase kontribusi penjualan berdasarkan kategori produk minggu ini.</p>
                
                <div class="space-y-8">
                    <?php foreach ($donutPercentages as $dp): ?>
                    <div class="group cursor-pointer">
                        <div class="flex justify-between text-sm font-bold mb-3">
                            <span class="text-gray-700 transition-colors flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full" style="background-color: <?= $dp['color'] ?>"></div> <?= strtoupper($dp['nama']) ?>
                            </span>
                            <span class="text-gray-900 text-lg"><?= $dp['percentage'] ?>%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                            <div class="h-3 rounded-full group-hover:scale-y-110 transition-transform origin-left" style="width: <?= $dp['percentage'] ?>%; background-color: <?= $dp['color'] ?>"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>
</main>

<script>
    // Variables from PHP
    window.chartLabels = <?= json_encode($chartLabels) ?>;
    window.chartData = <?= json_encode($chartData) ?>;
    
    window.donutLabels = <?= json_encode($donutLabels) ?>;
    window.donutData = <?= json_encode($donutData) ?>;
    window.donutColors = <?= json_encode(array_column($donutPercentages, 'color') ?: ['#D1D5DB']) ?>;

    // Konfigurasi Bar Chart
    const ctxBar = document.getElementById('barChart').getContext('2d');
    let gradient = ctxBar.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(217, 119, 6, 1)'); 
    gradient.addColorStop(1, 'rgba(217, 119, 6, 0.1)');

    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: window.chartLabels,
            datasets: [{
                label: 'Pendapatan',
                data: window.chartData,
                backgroundColor: gradient,
                borderRadius: 8,
                barThickness: 32,
                hoverBackgroundColor: '#b45f00'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#2D1A11',
                    padding: 12,
                    titleFont: { size: 14, family: 'Inter' },
                    bodyFont: { size: 14, weight: 'bold', family: 'Inter' },
                    displayColors: false,
                    callbacks: {
                        label: function(context) { 
                            return 'Rp ' + context.raw.toLocaleString('id-ID'); 
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { 
                        callback: function(value) { 
                            if (value >= 1000000) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            } else if (value >= 1000) {
                                return 'Rp ' + (value / 1000).toFixed(0) + 'k';
                            }
                            return 'Rp ' + value;
                        },
                        font: { family: 'Inter', weight: '500' },
                        color: '#9ca3af'
                    },
                    border: { display: false },
                    grid: { color: '#f3f4f6', drawBorder: false }
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { family: 'Inter', weight: 'bold' }, color: '#6b7280' },
                    border: { display: false }
                }
            },
            animation: { y: { duration: 1500, easing: 'easeOutElastic' } }
        }
    });

    // Konfigurasi Donut Chart
    const ctxDonut = document.getElementById('donutChart').getContext('2d');
    new Chart(ctxDonut, {
        type: 'doughnut',
        data: {
            labels: window.donutLabels,
            datasets: [{
                data: window.donutData,
                backgroundColor: window.donutColors,
                borderWidth: 6,
                borderColor: '#ffffff',
                hoverOffset: 10
            }]
        },
        options: {
            cutout: '78%',
            responsive: true,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#2D1A11',
                    padding: 12,
                    bodyFont: { size: 16, weight: 'bold', family: 'Inter' },
                    callbacks: {
                        label: function(context) { return ' ' + context.raw + '%'; }
                    }
                }
            },
            animation: { animateScale: true, animateRotate: true, duration: 2000 }
        }
    });
    // Fungsi Modal Stok
    function openStockModal() {
        const modal = document.getElementById('stockModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        // Animasi masuk
        const content = modal.querySelector('.inline-block');
        content.classList.add('animate__animated', 'animate__fadeInUp', 'animate__faster');
    }

    function closeStockModal() {
        const modal = document.getElementById('stockModal');
        const content = modal.querySelector('.inline-block');
        content.classList.remove('animate__fadeInUp');
        content.classList.add('animate__fadeOutDown');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            content.classList.remove('animate__fadeOutDown');
            document.body.style.overflow = 'auto';
        }, 200);
    }
</script>
<?php include 'includes/footer.php'; ?>