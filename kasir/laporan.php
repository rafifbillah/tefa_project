<?php 
require_once '../core/Auth.php';
Auth::requireRole('kasir');

require_once '../models/LaporanModel.php';

$laporanModel = new LaporanModel();

// Ambil parameter filter
$method = isset($_GET['method']) ? strtolower($_GET['method']) : 'semua';
$methodFilter = ($method === 'semua') ? null : $method;

// Filter Tanggal - Default tampilkan HARI INI
$dateParam = $_GET['date'] ?? null;
if ($dateParam === 'all') {
    $selectedDate = null;
} elseif ($dateParam !== null && $dateParam !== '') {
    $selectedDate = $dateParam;
} else {
    $selectedDate = date('Y-m-d');
}

// Eksekusi query model
$userId = $_SESSION['user_id'];
$transactions = $laporanModel->getTransactions($selectedDate, $selectedDate, $methodFilter, $userId);
$stats = $laporanModel->getSummaryStats($selectedDate, $selectedDate, $methodFilter, $userId);

$totalTransaksi = count($transactions);
$totalPendapatan = $stats['total_pendapatan'] ?? 0;
$rataRata = $stats['rata_rata_transaksi'] ?? 0;

// Menentukan nama kasir yang aktif
$kasirAktif = $_SESSION['nama_lengkap'] ?? 'Kasir';

$page_title = "Laporan Transaksi";
$subtitle_date = $selectedDate ? date('d M Y', strtotime($selectedDate)) : 'Semua Waktu';
$page_subtitle = "Ringkasan performa penjualan dan audit transaksi (" . $subtitle_date . ")";
include 'includes/header.php'; 
?>

<style>
    :root {
        --primary: #D97706;
        --primary-dark: #B45309;
        --secondary: #2D1A11;
        --accent: #F59E0B;
        --surface: #FFFFFF;
        --background: #F8FAFC;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .stat-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    @media print {
        aside, header.main-header, .no-print, #print-btn, .filter-container, .export-btns, button, .bg-blue-600, .bg-[#e8f5e9] {
            display: none !important;
        }
        main {
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
            width: 100% !important;
        }
        .max-w-7xl {
            max-width: 100% !important;
            padding: 0 !important;
        }
        .glass-card {
            border: 1px solid #e2e8f0 !important;
            box-shadow: none !important;
            background: white !important;
            backdrop-filter: none !important;
        }
        .print-only {
            display: block !important;
        }
        table {
            border-collapse: collapse !important;
            width: 100% !important;
        }
        th, td {
            border: 1px solid #e2e8f0 !important;
            font-size: 10px !important;
        }
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }


    /* Modal Styling */
    .modal-overlay {
        position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 1000;
        display: none; align-items: center; justify-content: center; backdrop-filter: blur(8px);
        animation: fadeIn 0.2s ease-out;
    }
    .modal-box {
        background: #fff; width: 90%; max-width: 500px; border-radius: 2rem; overflow: hidden;
        animation: slideUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { transform: translateY(30px) scale(0.95); opacity: 0; } to { transform: translateY(0) scale(1); opacity: 1; } }
    .modal-header { padding: 2rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
    .modal-body { padding: 2rem; max-height: 60vh; overflow-y: auto; }
    .modal-footer { padding: 1.5rem 2rem; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; }
</style>

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 overflow-y-auto bg-[#F8FAFC] font-['Inter']">
    
    <?php include 'includes/topbar.php'; ?>

    <div class="max-w-7xl mx-auto px-6 py-8">


        <!-- Filter & Action Bar -->
        <div class="glass-card rounded-3xl p-6 mb-8 no-print flex flex-col xl:flex-row items-start xl:items-center justify-between gap-6 filter-container">
            <form method="GET" class="flex flex-wrap items-center gap-6">
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Pilih Tanggal</label>
                    <div class="flex items-center gap-2">
                        <input type="date" name="date" value="<?= $selectedDate ?? '' ?>" onchange="this.form.submit()" class="bg-gray-50 border-none text-gray-700 py-3 px-6 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-[#D97706] hover:bg-gray-100 transition-all cursor-pointer">
                        <button type="button" onclick="window.location.href='laporan.php?date=all'" class="bg-gray-50 text-gray-600 px-5 py-3 rounded-2xl font-bold text-sm hover:bg-gray-200 transition-all cursor-pointer" title="Tampilkan Semua Transaksi">Semua</button>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Metode Pembayaran</label>
                    <div class="relative">
                        <select name="method" onchange="this.form.submit()" class="appearance-none bg-gray-50 border-none text-gray-700 py-3 pl-6 pr-14 rounded-2xl font-bold text-sm focus:ring-2 focus:ring-[#D97706] cursor-pointer hover:bg-gray-100 transition-all">
                            <option value="semua" <?= $method === 'semua' ? 'selected' : '' ?>>Semua Metode</option>
                            <option value="tunai" <?= $method === 'tunai' ? 'selected' : '' ?>>💵 Tunai</option>
                            <option value="qris" <?= $method === 'qris' ? 'selected' : '' ?>>📱 QRIS</option>
                            <option value="transfer" <?= $method === 'transfer' ? 'selected' : '' ?>>🏦 Transfer</option>
                        </select>
                        <i class="fa-solid fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none text-xs"></i>
                    </div>
                </div>

                <?php if ($selectedDate !== date('Y-m-d') || $method !== 'semua'): ?>
                <div class="flex items-end h-full pt-6">
                    <a href="laporan.php" class="text-xs font-bold text-[#D97706] hover:underline">Reset Filter</a>
                </div>
                <?php endif; ?>
            </form>

            <div class="flex items-center gap-3">
                <!-- Tombol Tutup Shift -->
                <?php 
                    $actionDate = $selectedDate ?: date('Y-m-d');
                    $hasPending = false;
                    foreach ($transactions as $t) {
                        if (($t['status_verifikasi'] ?? 'pending') === 'pending') {
                            $hasPending = true; break;
                        }
                    }
                ?>
                <?php if ($hasPending && $selectedDate !== null): ?>
                <button onclick="ajukanRekapHarian('<?= $actionDate ?>')" class="bg-blue-600 text-white px-6 py-4 rounded-2xl font-bold text-sm flex items-center gap-3 hover:bg-blue-700 transition-all duration-300 shadow-lg shadow-blue-500/20 active:scale-95">
                    <i class="fa-solid fa-paper-plane"></i> TUTUP SHIFT & AJUKAN
                </button>
                <?php endif; ?>

                <a href="export_excel.php?<?= http_build_query($_GET) ?>" class="bg-[#e8f5e9] text-[#2e7d32] px-6 py-4 rounded-2xl font-bold text-sm flex items-center gap-3 hover:bg-[#c8e6c9] transition-all duration-300 shadow-sm active:scale-95">
                    <i class="fa-solid fa-file-excel"></i> EXCEL
                </a>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="glass-card stat-card p-6 rounded-3xl relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <i class="fa-solid fa-money-bill-trend-up text-6xl"></i>
                </div>
                <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-1">Total Pendapatan</p>
                <h3 class="text-3xl font-black text-gray-900 mb-2">Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></h3>
            </div>

            <div class="glass-card stat-card p-6 rounded-3xl relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <i class="fa-solid fa-receipt text-6xl"></i>
                </div>
                <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-1">Total Transaksi</p>
                <h3 class="text-3xl font-black text-gray-900 mb-2"><?= $totalTransaksi ?></h3>
            </div>

            <div class="glass-card stat-card p-6 rounded-3xl relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <i class="fa-solid fa-chart-line text-6xl"></i>
                </div>
                <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-1">Rata-rata / Order</p>
                <h3 class="text-3xl font-black text-gray-900 mb-2">Rp <?= number_format($rataRata, 0, ',', '.') ?></h3>
            </div>
        </div>

        <!-- Main Content Table Card -->
        <div class="glass-card rounded-[2.5rem] overflow-hidden border-none shadow-xl shadow-black/5">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="py-5 px-8 text-gray-400 font-bold uppercase tracking-widest text-[10px]">Waktu</th>
                            <th class="py-5 px-8 text-gray-400 font-bold uppercase tracking-widest text-[10px]">Reference ID</th>
                            <th class="py-5 px-8 text-gray-400 font-bold uppercase tracking-widest text-[10px]">Item Terjual</th>
                            <th class="py-5 px-8 text-gray-400 font-bold uppercase tracking-widest text-[10px]">Metode</th>
                            <th class="py-5 px-8 text-gray-400 font-bold uppercase tracking-widest text-[10px] text-right">Total Tagihan</th>
                            <th class="py-5 px-8 text-gray-400 font-bold uppercase tracking-widest text-[10px] text-center no-print">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="6" class="py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center text-gray-200 text-3xl">
                                        <i class="fa-solid fa-folder-open"></i>
                                    </div>
                                    <p class="text-gray-400 font-bold italic">Belum ada transaksi di database untuk filter ini.</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $trx): 
                                $details = $laporanModel->getTransactionDetails($trx['id']);
                                $time = date('H:i', strtotime($trx['created_at']));
                                $date = date('d/m/Y', strtotime($trx['created_at']));
                                
                                $metode = strtoupper($trx['metode_bayar']);
                                if ($metode === 'TUNAI') {
                                    $badgeStyle = 'bg-emerald-50 text-emerald-600 border border-emerald-100';
                                } elseif ($metode === 'QRIS') {
                                    $badgeStyle = 'bg-blue-50 text-blue-600 border border-blue-100';
                                } elseif ($metode === 'TRANSFER') {
                                    $badgeStyle = 'bg-amber-50 text-amber-600 border border-amber-100';
                                } else {
                                    $badgeStyle = 'bg-gray-50 text-gray-600 border border-gray-100';
                                }
                            ?>
                            <tr class="hover:bg-orange-50/20 transition-all group">
                                <td class="py-6 px-8">
                                    <div class="flex flex-col">
                                        <span class="font-black text-gray-900"><?= $time ?></span>
                                        <span class="text-[10px] text-gray-400 font-bold uppercase"><?= $date ?></span>
                                    </div>
                                </td>
                                <td class="py-6 px-8 flex flex-col gap-2 items-start">
                                    <span class="font-mono text-sm font-bold text-gray-600 bg-gray-100 px-3 py-1.5 rounded-lg">
                                        <?= htmlspecialchars($trx['transaction_id']) ?>
                                    </span>
                                    <?php 
                                        $statusV = $trx['status_verifikasi'] ?? 'pending';
                                        if ($statusV === 'pending') {
                                            echo '<span class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-gray-100 text-gray-500 border border-gray-200">Pending</span>';
                                        } elseif ($statusV === 'requested') {
                                            echo '<span class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-blue-50 text-blue-600 border border-blue-200">Menunggu</span>';
                                        } elseif ($statusV === 'verified') {
                                            echo '<span class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-emerald-50 text-emerald-600 border border-emerald-200">Verified</span>';
                                        } elseif ($statusV === 'synced') {
                                            echo '<span class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-purple-50 text-purple-600 border border-purple-200"><i class="fa-solid fa-cloud-check"></i> Terkirim</span>';
                                        }
                                    ?>
                                </td>
                                <td class="py-6 px-8">
                                    <div class="flex flex-col gap-1.5">
                                        <?php 
                                        $totalItems = count($details);
                                        if ($totalItems === 0): ?>
                                            <span class="text-xs text-gray-400 italic">Detail tidak tersedia</span>
                                        <?php else: 
                                            $firstItem = $details[0];
                                            ?>
                                            <div class="flex items-center gap-2">
                                                <div class="w-1.5 h-1.5 rounded-full bg-orange-300"></div>
                                                <span class="text-sm font-bold text-gray-800">
                                                    <span class="text-orange-600"><?= $firstItem['jumlah'] ?>x</span> 
                                                    <?= htmlspecialchars($firstItem['nama_produk']) ?>
                                                    <?php if ($totalItems > 1): ?>
                                                        <span class="text-gray-400 font-medium text-[11px] ml-1">
                                                            (+<?= $totalItems - 1 ?> item lainnya)
                                                        </span>
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-6 px-8">
                                    <span class="<?= $badgeStyle ?> px-4 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest whitespace-nowrap shadow-sm">
                                        <?= $metode ?>
                                    </span>
                                </td>
                                <td class="py-6 px-8 text-right">
                                    <span class="text-lg font-black text-gray-900 tracking-tight">
                                        Rp <?= number_format($trx['total_harga'], 0, ',', '.') ?>
                                    </span>
                                </td>
                                <td class="py-6 px-8 text-center no-print">
                                    <div class="flex items-center justify-center gap-2 opacity-50 group-hover:opacity-100 transition-opacity duration-300">
                                        <!-- Tombol Detail -->
                                        <button onclick='openDetailModal("<?= $trx["transaction_id"] ?>", "<?= $time ?>", "<?= $date ?>", "<?= $metode ?>", "<?= number_format($trx["total_harga"], 0, ",", ".") ?>", <?= json_encode($details) ?>, "<?= $badgeStyle ?>")' class="w-9 h-9 rounded-xl bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-900 flex items-center justify-center transition-colors shadow-sm" title="Lihat Rincian">
                                            <i class="fa-solid fa-eye text-sm"></i>
                                        </button>
                                        <!-- Tombol Cetak Ulang -->
                                        <a href="cetak_struk.php?id=<?= $trx['id'] ?>" class="w-9 h-9 rounded-xl bg-[#2D1A11]/10 text-[#2D1A11] hover:bg-[#2D1A11] hover:text-white flex items-center justify-center transition-colors shadow-sm" title="Cetak Ulang Struk">
                                            <i class="fa-solid fa-print text-sm"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Table Footer -->
            <div class="bg-gray-50/50 p-8 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-400">
                        <i class="fa-solid fa-database"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Total Baris</p>
                        <p class="text-sm font-black text-gray-900"><?= $totalTransaksi ?> Entri Ditemukan</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<!-- ═══ MODAL DETAIL TRANSAKSI ═══ -->
<div id="detailModal" class="modal-overlay" onclick="closeDetailModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <h3 class="text-xl font-black text-gray-900" id="modalTrxId">Detail Transaksi</h3>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1" id="modalTrxTime"></p>
            </div>
            <button onclick="closeDetailModal()" class="w-10 h-10 rounded-full bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all flex items-center justify-center">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div id="modalContent">
                <!-- Data item diinjeksi via JS -->
            </div>
            <div class="mt-8 pt-6 border-t border-gray-100">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-gray-400 font-bold text-xs uppercase tracking-widest">Metode Pembayaran</span>
                    <span id="modalPayment" class="px-3 py-1 rounded-lg text-[10px] font-black uppercase"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-900 font-black text-lg">Total Akhir</span>
                    <span id="modalTotal" class="text-2xl font-black text-[#D97706]"></span>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeDetailModal()" class="px-8 py-3 rounded-2xl bg-gray-100 text-gray-600 font-bold text-sm hover:bg-gray-200 transition-all active:scale-95">Tutup Detail</button>
        </div>
    </div>
</div>

<script>
function openDetailModal(trxId, time, date, method, total, items, badgeStyle) {
    document.getElementById('modalTrxId').innerText = '#' + trxId;
    document.getElementById('modalTrxTime').innerText = date + ' • ' + time;
    document.getElementById('modalTotal').innerText = 'Rp ' + total;
    
    const paymentEl = document.getElementById('modalPayment');
    paymentEl.innerText = method;
    paymentEl.className = badgeStyle + ' px-3 py-1 rounded-lg text-[10px] font-black uppercase shadow-sm';

    let html = '<div class="space-y-4">';
    items.forEach(item => {
        html += `
            <div class="flex justify-between items-center py-4 border-b border-gray-50 last:border-0">
                <div class="flex items-center gap-4">
                    <div class="w-11 h-11 rounded-2xl bg-orange-50 text-[#D97706] flex items-center justify-center font-black text-sm shrink-0 border border-orange-100/50">
                        ${item.jumlah}x
                    </div>
                    <div>
                        <p class="font-black text-gray-900 leading-tight">${item.nama_produk}</p>
                        <p class="text-[10px] text-gray-400 font-black uppercase tracking-wider mt-0.5">Rp ${Number(item.harga_satuan).toLocaleString('id-ID')}</p>
                    </div>
                </div>
                <p class="font-black text-gray-900 text-sm">Rp ${Number(item.jumlah * item.harga_satuan).toLocaleString('id-ID')}</p>
            </div>
        `;
    });
    html += '</div>';
    document.getElementById('modalContent').innerHTML = html;

    document.getElementById('detailModal').style.display = 'flex';
}

function closeDetailModal() {
    document.getElementById('detailModal').style.display = 'none';
}

const csrfToken = "<?= Auth::generateCsrfToken() ?>";

// Tutup modal dengan tombol Escape
document.addEventListener('keydown', function(event) {
    if (event.key === "Escape") closeDetailModal();
});

async function ajukanRekapHarian(tanggal) {
    if (!confirm('Apakah Anda yakin ingin menutup shift dan mengajukan seluruh transaksi hari ini ke Admin?')) return;
    
    try {
        const res = await fetch('ajax_ajukan_verifikasi.php', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ tanggal: tanggal })
        });
        const data = await res.json();
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    } catch (e) {
        alert('Terjadi kesalahan koneksi.');
    }
}

</script>

<?php include 'includes/footer.php'; ?>