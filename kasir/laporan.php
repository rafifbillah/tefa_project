<?php 
$page_title = "Laporan Shift";
$page_subtitle = "RIWAYAT TRANSAKSI";
include 'includes/header.php'; 
?>

<!-- <link rel="stylesheet" href="../assets/css/kasir-laporan.css"> -->

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 overflow-y-auto bg-[#F8FAFC]">
    
    <?php include 'includes/topbar.php'; ?>

    <div class="max-w-7xl mx-auto px-8 pb-8 mt-6">

        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Laporan Shift</h2>
                <p class="text-gray-500 font-medium mt-1">Kelola dan pantau seluruh riwayat transaksi hari ini.</p>
            </div>
            <button id="print-btn" class="bg-[#2D1A11] text-white px-6 py-3 rounded-xl font-bold text-sm flex items-center gap-3 hover:bg-[#D97706] hover:-translate-y-1 hover:shadow-lg transition-all duration-300 cursor-pointer">
                <i class="fa-solid fa-print"></i> CETAK LAPORAN
            </button>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            
            <div class="bg-gradient-to-r from-orange-50 to-white p-6 flex flex-col md:flex-row justify-between items-center gap-6 border-b border-gray-100">
                
                <div class="flex flex-wrap items-center gap-4 md:gap-8">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-[#D97706] text-xl">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-gray-900">Rincian Pesanan</h3>
                            <p class="text-sm text-gray-500 font-medium">12 Transaksi Hari Ini</p>
                        </div>
                    </div>
                    
                    <div class="hidden md:block w-px h-10 bg-gray-200"></div>

                    <div>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">KASIR AKTIF</p>
                        <p class="text-gray-900 font-bold flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            Andhika <span class="text-[#D97706]">(Shift Pagi)</span>
                        </p>
                    </div>

                    <div class="hidden md:block w-px h-10 bg-gray-200"></div>

                    <div>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">TOTAL KOTOR</p>
                        <p class="text-2xl font-black text-gray-900">Rp 214.000</p>
                    </div>
                </div>

                <div class="relative w-full md:w-auto">
                    <select id="payment-filter" class="w-full md:w-auto appearance-none bg-white border border-gray-200 text-gray-700 py-3 pl-5 pr-12 rounded-xl font-bold focus:outline-none focus:ring-2 focus:ring-[#D97706] cursor-pointer shadow-sm hover:border-[#D97706] transition-colors">
                        <option value="semua">Semua Metode Pembayaran</option>
                        <option value="tunai">Hanya TUNAI</option>
                        <option value="qris">Hanya QRIS</option>
                    </select>
                    <i class="fa-solid fa-caret-down absolute right-5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="py-4 px-6 text-gray-400 font-bold uppercase tracking-widest text-xs">Waktu</th>
                            <th class="py-4 px-6 text-gray-400 font-bold uppercase tracking-widest text-xs">ID Pesanan</th>
                            <th class="py-4 px-6 text-gray-400 font-bold uppercase tracking-widest text-xs">Detail Item</th>
                            <th class="py-4 px-6 text-gray-400 font-bold uppercase tracking-widest text-xs">Metode</th>
                            <th class="py-4 px-6 text-gray-400 font-bold uppercase tracking-widest text-xs text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        
                        <tr class="hover:bg-orange-50/30 transition-colors group">
                            <td class="py-5 px-6 font-bold text-gray-400 group-hover:text-[#D97706] transition-colors">14:15</td>
                            <td class="py-5 px-6 font-black text-gray-900">#ORD-0044</td>
                            <td class="py-5 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-orange-100 text-[#D97706] flex items-center justify-center text-xs"><i class="fa-solid fa-mug-hot"></i></div>
                                    <p class="font-bold text-gray-900">1x Cappuccino (Hot)</p>
                                </div>
                            </td>
                            <td class="py-5 px-6">
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-md text-xs font-black tracking-wide">TUNAI</span>
                            </td>
                            <td class="py-5 px-6 font-black text-gray-900 text-right">Rp 25.000</td>
                        </tr>

                        <tr class="hover:bg-orange-50/30 transition-colors group">
                            <td class="py-5 px-6 font-bold text-gray-400 group-hover:text-[#D97706] transition-colors">14:15</td>
                            <td class="py-5 px-6 font-black text-gray-900">#ORD-0042</td>
                            <td class="py-5 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-[#382216]/10 text-[#382216] flex items-center justify-center text-xs"><i class="fa-solid fa-bread-slice"></i></div>
                                    <p class="font-bold text-gray-900">2x Sourdough Loaf</p>
                                </div>
                            </td>
                            <td class="py-5 px-6">
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-md text-xs font-black tracking-wide">TUNAI</span>
                            </td>
                            <td class="py-5 px-6 font-black text-gray-900 text-right">Rp 50.000</td>
                        </tr>

                        <tr class="hover:bg-orange-50/30 transition-colors group">
                            <td class="py-5 px-6 font-bold text-gray-400 group-hover:text-[#D97706] transition-colors">14:30</td>
                            <td class="py-5 px-6 font-black text-gray-900">#ORD-0045</td>
                            <td class="py-5 px-6">
                                <p class="font-bold text-gray-900 flex items-center gap-2"><i class="fa-solid fa-mug-hot text-gray-400 text-xs"></i> 2x Americano (Ice)</p>
                                <p class="font-medium text-gray-500 mt-1 flex items-center gap-2"><i class="fa-solid fa-bread-slice text-gray-300 text-xs"></i> 1x Sourdough Loaf</p>
                            </td>
                            <td class="py-5 px-6">
                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-md text-xs font-black tracking-wide">QRIS</span>
                            </td>
                            <td class="py-5 px-6 font-black text-gray-900 text-right">Rp 55.000</td>
                        </tr>

                        <tr class="hover:bg-orange-50/30 transition-colors group">
                            <td class="py-5 px-6 font-bold text-gray-400 group-hover:text-[#D97706] transition-colors">14:30</td>
                            <td class="py-5 px-6 font-black text-gray-900">#ORD-0043</td>
                            <td class="py-5 px-6">
                                <p class="font-bold text-gray-900 flex items-center gap-2"><i class="fa-solid fa-bread-slice text-gray-400 text-xs"></i> 3x Croissant Butter</p>
                                <p class="font-medium text-gray-500 mt-1 flex items-center gap-2"><i class="fa-solid fa-mug-hot text-gray-300 text-xs"></i> 1x Kopi Macengar</p>
                            </td>
                            <td class="py-5 px-6">
                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-md text-xs font-black tracking-wide">QRIS</span>
                            </td>
                            <td class="py-5 px-6 font-black text-gray-900 text-right">Rp 84.000</td>
                        </tr>

                    </tbody>
                </table>
            </div>
            
            <div class="bg-gray-50 p-4 border-t border-gray-100 flex items-center justify-between text-sm">
                <span class="text-gray-500 font-medium">Menampilkan <span class="font-bold text-gray-900">1</span> sampai <span class="font-bold text-gray-900">4</span> dari <span class="font-bold text-gray-900">12</span> data</span>
                <div class="flex gap-1">
                    <button class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 hover:bg-white hover:text-gray-900 transition-colors disabled:opacity-50"><i class="fa-solid fa-chevron-left"></i></button>
                    <button class="w-8 h-8 rounded-lg bg-[#D97706] flex items-center justify-center text-white font-bold shadow-sm">1</button>
                    <button class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-white hover:text-gray-900 transition-colors">2</button>
                    <button class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-white hover:text-gray-900 transition-colors">3</button>
                    <button class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 hover:bg-white hover:text-gray-900 transition-colors"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>

        </div>
    </div>
</main>

<!-- <script src="../assets/js/kasir-laporan.js"></script> -->

<?php include 'includes/footer.php'; ?>