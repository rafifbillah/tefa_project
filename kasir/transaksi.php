<?php 
require_once '../core/Auth.php';
Auth::requireRole('kasir');
require_once '../core/Database.php';
require_once '../models/BarangModel.php';

$barangModel = new BarangModel();
$productsFromDB = $barangModel->getAll();

$page_title = "Transaksi";
$page_subtitle = "RINGKASAN AKTIVITAS TOKO";
include 'includes/header.php'; 
?>

<link rel="stylesheet" href="../assets/css/kasir-transaksi.css">
<style>
    .payment-method.active {
        border-color: #D97706;
        background-color: #FEF3C7;
    }
</style>

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 flex flex-col bg-[#F8FAFC] overflow-hidden">
    <?php include 'includes/topbar.php'; ?>

    <div class="flex-1 flex overflow-hidden">
        
        <!-- Area Produk (Kiri) -->
        <div class="flex-1 overflow-y-auto px-8 pb-8">
            <!-- ─── Filter & Search Bar ─── -->
            <div class="sticky top-0 z-10 bg-[#F8FAFC]/80 backdrop-blur-md py-4 mb-6">
                <div class="flex gap-4 flex-wrap items-center">
                    <!-- Search bar -->
                    <div class="relative w-full md:w-80 group">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm group-focus-within:text-[#D97706] transition-colors"></i>
                        <input type="text" id="searchInput" placeholder="Cari produk di sini..."
                            class="w-full pl-11 pr-4 py-3 bg-white rounded-2xl border border-gray-100 focus:border-[#D97706] focus:ring-4 focus:ring-amber-50 outline-none text-sm transition-all shadow-sm">
                    </div>

                    <div class="h-8 w-[1px] bg-gray-200 hidden md:block mx-2"></div>

                    <!-- Categories -->
                    <div class="flex gap-3 overflow-x-auto no-scrollbar pb-1">
                        <button onclick="filterByCategory('semua')" 
                            class="category-btn px-6 py-2.5 bg-[#D97706] text-white rounded-xl font-bold text-sm shadow-md shadow-amber-100 border border-transparent transition-all whitespace-nowrap" 
                            data-category="semua">
                            Semua
                        </button>
                        <?php 
                        $categories = $barangModel->getCategories();
                        foreach($categories as $cat):
                        ?>
                        <button onclick="filterByCategory('<?= $cat['id'] ?>')" 
                            class="category-btn px-6 py-2.5 bg-white text-gray-500 rounded-xl font-bold text-sm border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-all whitespace-nowrap" 
                            data-category="<?= $cat['id'] ?>">
                            <?= $cat['nama_kategori'] ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="products-grid">
                <?php foreach($productsFromDB as $p): ?>
                <div class="product-card bg-white p-4 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md transition cursor-pointer active:scale-95 group" 
                     data-kategori="<?= $p['category_id'] ?>" 
                     onclick="addToCart(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nama_produk']) ?>', <?= $p['harga'] ?>)">
                    <div class="bg-gray-100 aspect-square rounded-2xl mb-4 overflow-hidden relative flex items-center justify-center border border-gray-100">
                        <?php if (!empty($p['image']) && file_exists(__DIR__ . '/../assets/img/products/' . $p['image'])): ?>
                            <img src="../assets/img/products/<?= $p['image'] ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="text-gray-300 flex flex-col items-center">
                                <i class="fa-solid fa-image text-4xl mb-2"></i>
                                <span class="text-[10px] font-bold uppercase tracking-widest">No Image</span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($p['stok'] < 1): ?>
                        <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px] flex items-center justify-center">
                            <span class="text-white font-bold px-4 py-2 bg-red-600 rounded-full text-xs shadow-lg uppercase tracking-wider">Habis</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <h4 class="font-bold text-gray-800 text-lg group-hover:text-[#D97706] transition"><?= htmlspecialchars($p['nama_produk']) ?></h4>
                    <div class="flex justify-between items-center mt-1">
                        <p class="text-[#D97706] font-bold text-xl">Rp <?= number_format($p['harga'], 0, ',', '.') ?></p>
                        <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-1 rounded-md font-bold">STOK: <?= $p['stok'] ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ═══ SIDEBAR KERANJANG (KANAN) ═══ -->
        <!-- overflow-hidden + flex-col + h-full = tidak distorsi saat item banyak -->
        <div class="w-96 bg-white border-l border-gray-100 flex flex-col overflow-hidden" style="height:100%;">

            <!-- HEADER — fixed, tidak ikut scroll -->
            <div class="bg-[#1e1b2e] px-5 py-4 flex items-center gap-3 flex-shrink-0">
                <i class="fa-solid fa-cart-shopping text-white text-lg"></i>
                <h3 class="text-white font-bold text-base" id="cart-count-header">Keranjang — 0 item</h3>
            </div>


            <!-- ITEM LIST — scroll bebas, min-h-0 penting agar flex tidak overflow -->
            <div class="flex-1 overflow-y-auto px-4 py-3 min-h-0">
                <div id="cart-items" class="divide-y divide-gray-50">
                    <div id="empty-cart-msg" class="text-center py-16">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fa-solid fa-cart-plus text-gray-200 text-2xl"></i>
                        </div>
                        <p class="text-gray-400 text-sm font-medium">Keranjang masih kosong</p>
                    </div>
                </div>
            </div>

            <!-- CHECKOUT FORM — scrollable sendiri, max 62vh agar tidak distorsi -->
            <div id="cart-footer" class="flex-shrink-0 border-t-2 border-gray-100 hidden" style="max-height:62vh; overflow-y:auto;">
                <div class="px-5 pt-4 pb-5 space-y-4">

                    <!-- Total -->
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-800">TOTAL</span>
                        <span id="total-price" class="text-lg font-bold text-[#1e1b2e]">Rp 0</span>
                    </div>

                    <!-- Metode Pembayaran -->
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2 block">
                            Metode Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <button onclick="selectPayment('tunai')" id="pay-tunai"
                                class="payment-method py-2.5 bg-white border border-gray-200 rounded-xl flex flex-col items-center justify-center gap-1 active transition-all shadow-sm">
                                <i class="fa-solid fa-money-bill-wave text-green-600 text-sm"></i>
                                <span class="text-[10px] font-bold text-gray-600">Tunai</span>
                            </button>
                            <button onclick="selectPayment('qris')" id="pay-qris"
                                class="payment-method py-2.5 bg-white border border-gray-200 rounded-xl flex flex-col items-center justify-center gap-1 transition-all shadow-sm">
                                <i class="fa-solid fa-qrcode text-blue-600 text-sm"></i>
                                <span class="text-[10px] font-bold text-gray-600">QRIS</span>
                            </button>
                            <button onclick="selectPayment('transfer')" id="pay-transfer"
                                class="payment-method py-2.5 bg-white border border-gray-200 rounded-xl flex flex-col items-center justify-center gap-1 transition-all shadow-sm">
                                <i class="fa-solid fa-building-columns text-purple-600 text-sm"></i>
                                <span class="text-[10px] font-bold text-gray-600">Transfer</span>
                            </button>
                        </div>
                    </div>

                    <!-- Input Tunai & Kembalian -->
                    <div id="cash-input-group">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Jumlah Bayar (Tunai)</label>
                        <input type="number" id="cash-amount"
                            class="w-full bg-white border border-gray-200 rounded-xl py-2.5 px-4 text-lg font-bold text-gray-700 focus:ring-2 focus:ring-[#D97706] outline-none"
                            placeholder="0">
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-xs text-gray-400 font-medium">Kembalian</span>
                            <span id="change-amount" class="text-sm font-bold text-red-500">Rp 0</span>
                        </div>
                    </div>

                    <!-- Rekening Transfer -->
                    <div id="transfer-info-group" class="hidden">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Rekening Tujuan Kampus</label>
                        <div class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm font-semibold text-gray-700">
                            BRI – 0123456789 a/n TEFA Polije
                        </div>
                    </div>

                    <!-- Bukti Pembayaran (QRIS & Transfer) -->
                    <div id="bukti-group" class="hidden">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Bukti Pembayaran</label>
                        <div class="bg-white border border-gray-200 rounded-xl px-3 py-2.5 flex items-center gap-3">
                            <label class="cursor-pointer flex-shrink-0">
                                <span class="px-3 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-700 font-bold text-xs rounded-lg transition border border-gray-300">Pilih File</span>
                                <input type="file" id="bukti-bayar" accept="image/jpg,image/jpeg,image/png,image/webp" class="hidden">
                            </label>
                            <span id="bukti-filename" class="text-xs text-gray-400 truncate">Tidak ada file yang dipilih</span>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1.5">JPG/PNG/WEBP, maks 5 MB</p>
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1.5 block">Catatan (opsional)</label>
                        <textarea id="order-notes" rows="2"
                            class="w-full bg-white border border-gray-200 rounded-xl py-2.5 px-4 text-sm focus:ring-2 focus:ring-[#D97706] outline-none resize-none"
                            placeholder="Catatan..."></textarea>
                    </div>

                    <!-- Tombol Submit -->
                    <button id="confirm-payment"
                        class="w-full bg-[#c49261] hover:bg-[#a67c52] text-white py-3.5 rounded-2xl font-bold text-base flex items-center justify-center gap-2.5 transition-all shadow-lg active:scale-95">
                        <i class="fa-solid fa-check-circle"></i> Simpan &amp; Cetak Struk
                    </button>

                </div>
            </div>

        </div><!-- /.sidebar -->
    </div>
</main>

<script src="../assets/js/kasir-transaksi.js"></script>
<?php include 'includes/footer.php'; ?>