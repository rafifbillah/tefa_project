<?php
require_once '../core/Auth.php';
Auth::requireRole('kasir');

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
                <p class="text-4xl font-black text-gray-900 mb-2 relative z-10">$999.999</p>
                <p class="text-sm text-green-500 font-bold flex items-center justify-center gap-1 relative z-10"><i class="fa-solid fa-arrow-trend-up"></i> 99% Dari Kemarin</p>
            </div>
            
            <div class="bg-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 border border-gray-100 text-center relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-orange-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <h3 class="text-gray-500 text-xs font-bold mb-3 uppercase tracking-widest relative z-10">Total Terjual</h3>
                <p class="text-4xl font-black text-gray-900 mb-2 relative z-10">999</p>
                <p class="text-sm text-[#D97706] font-bold flex items-center justify-center gap-1 relative z-10"><i class="fa-solid fa-arrow-trend-up"></i> 99% Dari Kemarin</p>
            </div>
            
            <div class="bg-gradient-to-br from-red-50 to-white p-6 rounded-2xl shadow-sm hover:shadow-md transition-shadow duration-300 border border-red-100 text-center relative overflow-hidden group cursor-pointer">
                <h3 class="text-red-500 text-xs font-bold mb-3 uppercase tracking-widest relative z-10">Peringatan Restock</h3>
                <p class="text-4xl font-black text-red-600 mb-2 relative z-10">7</p>
                <p class="text-sm text-red-600 font-bold flex items-center justify-center gap-1 relative z-10"><i class="fa-solid fa-triangle-exclamation"></i> Menu Menipis</p>
            </div>
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
                    <?php for($i=0; $i<4; $i++): ?>
                    <div class="flex justify-between items-center p-4 bg-gray-50/50 hover:bg-orange-50/50 transition-colors rounded-xl border border-gray-100 cursor-pointer group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gray-200 group-hover:bg-orange-200 transition-colors flex items-center justify-center text-xl font-bold text-gray-400 group-hover:text-orange-600">R</div>
                            <div>
                                <p class="font-bold text-gray-900">Roti Sip <?= $i+1 ?></p>
                                <p class="text-xs font-medium text-gray-500">Kategori Roti</p>
                            </div>
                        </div>
                        <span class="bg-red-100 text-red-600 px-3 py-1 rounded-md font-bold text-xs">Sisa 3</span>
                    </div>
                    <?php endfor; ?>
                </div>
                <button class="w-full mt-6 py-3 rounded-xl font-bold text-[#D97706] bg-orange-50 hover:bg-orange-100 transition-colors text-sm">Lihat Semua Data</button>
            </div>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col lg:flex-row items-center gap-12 lg:gap-24">
            <div class="w-64 h-64 relative flex-shrink-0">
                <canvas id="donutChart"></canvas>
                <div class="absolute inset-0 flex items-center justify-center flex-col">
                    <span class="text-5xl font-black text-gray-900 tracking-tighter">99%</span>
                    <span class="text-xs font-bold text-gray-400 mt-1">TOTAL</span>
                </div>
            </div>
            <div class="flex-1 w-full">
                <h3 class="text-3xl font-black text-gray-900 mb-2 tracking-tight">Distribusi Kategori Menu</h3>
                <p class="text-gray-500 font-medium mb-10">Persentase kontribusi penjualan berdasarkan kategori produk minggu ini.</p>
                
                <div class="space-y-8">
                    <div class="group cursor-pointer">
                        <div class="flex justify-between text-sm font-bold mb-3">
                            <span class="text-gray-700 group-hover:text-[#382216] transition-colors flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-[#382216]"></div> KOPI</span>
                            <span class="text-gray-900 text-lg">9%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                            <div class="bg-[#382216] h-3 rounded-full group-hover:scale-y-110 transition-transform origin-left" style="width: 9%"></div>
                        </div>
                    </div>
                    <div class="group cursor-pointer">
                        <div class="flex justify-between text-sm font-bold mb-3">
                            <span class="text-gray-700 group-hover:text-[#D97706] transition-colors flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-[#D97706]"></div> ROTI</span>
                            <span class="text-gray-900 text-lg">90%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                            <div class="bg-gradient-to-r from-[#D97706] to-[#f59e0b] h-3 rounded-full group-hover:scale-y-110 transition-transform origin-left shadow-[0_0_10px_rgba(217,119,6,0.5)]" style="width: 90%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<script>
    // Konfigurasi Bar Chart
    const ctxBar = document.getElementById('barChart').getContext('2d');
    let gradient = ctxBar.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(217, 119, 6, 1)'); 
    gradient.addColorStop(1, 'rgba(217, 119, 6, 0.1)');

    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            datasets: [{
                label: 'Pendapatan',
                data: [250, 500, 400, 200, 250, 300, 1000],
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
                        label: function(context) { return 'Rp ' + context.raw + 'k'; }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 1000,
                    ticks: { 
                        callback: function(value) { return value + 'k'; },
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
            labels: ['Roti', 'Kopi'],
            datasets: [{
                data: [90, 9],
                backgroundColor: ['#D97706', '#382216'],
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
</script>
<?php include 'includes/footer.php'; ?>