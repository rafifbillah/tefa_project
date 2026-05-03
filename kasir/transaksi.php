<?php 
$page_title = "Transaksi";
$page_subtitle = "RINGKASAN AKTIVITAS TOKO";
include 'includes/header.php'; 
?>

<link rel="stylesheet" href="../assets/css/kasir-transaksi.css">

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 flex flex-col bg-[#F8FAFC] overflow-hidden">
    <?php include 'includes/topbar.php'; ?>

    <div class="flex-1 flex overflow-hidden">
        
        <div class="flex-1 overflow-y-auto px-8 pb-8">
            <div class="flex gap-4 mb-8 flex-wrap">
                <button onclick="filterByCategory('semua')" class="category-btn px-8 py-2 bg-gray-300 text-gray-700 rounded-full font-semibold" data-category="semua">Semua</button>
                <button onclick="filterByCategory('minuman-panas')" class="category-btn px-8 py-2 bg-gray-200 text-gray-500 rounded-full font-semibold hover:bg-gray-300 transition" data-category="minuman-panas">Minuman Panas</button>
                <button onclick="filterByCategory('minuman-dingin')" class="category-btn px-8 py-2 bg-gray-200 text-gray-500 rounded-full font-semibold hover:bg-gray-300 transition" data-category="minuman-dingin">Minuman Dingin</button>
                <button onclick="filterByCategory('bread')" class="category-btn px-8 py-2 bg-gray-200 text-gray-500 rounded-full font-semibold hover:bg-gray-300 transition" data-category="bread">Bread</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="products-grid">
                <?php 
                // Data dummy produk dengan kategori
                $products = [
                    // Minuman Panas
                    ['id' => 1, 'nama' => 'Kopi Susu Panas', 'harga' => 4.50, 'kategori' => 'minuman-panas'],
                    ['id' => 2, 'nama' => 'Hot Cappucino', 'harga' => 5.00, 'kategori' => 'minuman-panas'],
                    
                    // Minuman Dingin
                    ['id' => 3, 'nama' => 'Es Cappucino', 'harga' => 5.50, 'kategori' => 'minuman-dingin'],
                    ['id' => 4, 'nama' => 'Es Kopi Soda', 'harga' => 4.00, 'kategori' => 'minuman-dingin'],
                    ['id' => 5, 'nama' => 'Es Kopi Susu', 'harga' => 4.50, 'kategori' => 'minuman-dingin'],
                    
                    // Bread
                    ['id' => 6, 'nama' => 'Roti Sobek', 'harga' => 3.00, 'kategori' => 'bread'],
                    ['id' => 7, 'nama' => 'Roti Pizza', 'harga' => 6.00, 'kategori' => 'bread'],
                    ['id' => 8, 'nama' => 'Roti Pisang Coklat', 'harga' => 3.50, 'kategori' => 'bread'],
                    ['id' => 9, 'nama' => 'Roti Boy', 'harga' => 2.50, 'kategori' => 'bread'],
                    ['id' => 10, 'nama' => 'Roti Abon Gulung', 'harga' => 4.00, 'kategori' => 'bread'],
                    ['id' => 11, 'nama' => 'Roti Isi Daging', 'harga' => 5.00, 'kategori' => 'bread'],
                ];
                
                foreach($products as $p): 
                ?>
                <div class="product-card bg-white p-4 rounded-3xl border border-gray-100 shadow-sm hover:shadow-md transition cursor-pointer active:scale-95 group" data-kategori="<?= $p['kategori'] ?>" onclick="addToCart(<?= $p['id'] ?>, '<?= $p['nama'] ?>', <?= $p['harga'] ?>)">
                    <div class="bg-gray-200 aspect-square rounded-2xl mb-4 overflow-hidden">
                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                            <i class="fa-regular fa-image text-3xl"></i>
                        </div>
                    </div>
                    <h4 class="font-bold text-gray-800 text-lg"><?= $p['nama'] ?></h4>
                    <p class="text-[#D97706] font-bold text-xl mt-1">$<?= number_format($p['harga'], 2) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="w-96 bg-white border-l border-gray-200 flex flex-col">
            <div class="p-6">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" placeholder="Cari Produk..." class="w-full pl-12 pr-4 py-3 bg-gray-100 rounded-full border-none focus:ring-2 focus:ring-[#D97706] outline-none">
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-6">
                <h3 class="font-bold text-xl mb-4">Order</h3>
                
                <div id="cart-items" class="space-y-6">
                    <div id="empty-cart-msg" class="text-center py-10">
                        <i class="fa-solid fa-cart-shopping text-gray-200 text-5xl mb-3"></i>
                        <p class="text-gray-400 text-sm">Belum ada produk dipilih</p>
                    </div>
                </div>
            </div>

            <div id="cart-footer" class="p-6 border-t border-gray-100 shadow-[0_-4px_20px_rgba(0,0,0,0.05)] hidden">
                <div class="flex justify-between items-center mb-6">
                    <span class="text-2xl font-bold text-gray-900">Total</span>
                    <span id="total-price" class="text-2xl font-bold text-gray-900">$0.00</span>
                </div>
                
                <p class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-3">METODE PEMBAYARAN</p>
                <div class="flex gap-4 mb-8">
                    <div class="w-16 h-12 bg-gray-200 rounded-lg cursor-pointer hover:border-[#D97706] border-2 border-transparent transition"></div>
                    <div class="w-16 h-12 bg-gray-200 rounded-lg cursor-pointer hover:border-[#D97706] border-2 border-transparent transition"></div>
                </div>

                <button class="w-full bg-[#2D1A11] text-white py-4 rounded-2xl font-bold text-lg flex items-center justify-center gap-3 hover:bg-[#1f120c] transition active:scale-[0.98]">
                    <i class="fa-solid fa-cart-shopping"></i> Bayar
                </button>
            </div>
        </div>

    </div>
</main>

<script src="../assets/js/kasir-transaksi.js"></script>
<?php include 'includes/footer.php'; ?>