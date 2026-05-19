/**
 * kasir-transaksi.js — TEFA Bakery Kasir
 * ========================================
 * Vanilla JavaScript untuk logika keranjang & pembayaran transaksi.
 * Tanpa library eksternal. Semua state disimpan dalam variabel modul.
 */

// ─── State ────────────────────────────────────────────────────────────────────
let cart = []; // Array item di keranjang
let paymentMethod = "tunai"; // Metode bayar aktif
let currentCategory = "semua"; // Kategori filter aktif
let isMobileCartOpen = false; // State keranjang di mobile

// ─── Utilities ────────────────────────────────────────────────────────────────

/**
 * Format angka ke format Rupiah Indonesia.
 * @param {number} num
 * @returns {string} contoh: "15.000"
 */
function formatRp(num) {
  return new Intl.NumberFormat("id-ID").format(num);
}

/**
 * Hitung total harga seluruh item di keranjang.
 * @returns {number}
 */
function getCartTotal() {
  return cart.reduce((sum, item) => sum + item.harga * item.quantity, 0);
}

/**
 * Toggle keranjang belanja di tampilan mobile.
 */
function toggleMobileCart() {
  const sidebar = document.getElementById("cart-sidebar");
  if (!sidebar) return;

  isMobileCartOpen = !isMobileCartOpen;
  if (isMobileCartOpen) {
    sidebar.classList.remove("translate-x-full");
    document.body.classList.add("overflow-hidden");
  } else {
    sidebar.classList.add("translate-x-full");
    document.body.classList.remove("overflow-hidden");
  }
}

/**
 * Kosongkan seluruh keranjang.
 */
function clearCart() {
  if (confirm("Kosongkan keranjang belanja?")) {
    cart = [];
    renderCart();
    if (window.innerWidth < 1024) toggleMobileCart();
  }
}

// ─── Filter & Pencarian ───────────────────────────────────────────────────────

/**
 * Filter produk berdasarkan kategori.
 * Dipanggil dari atribut onclick di button kategori (HTML).
 * @param {string} category - ID kategori atau 'semua'
 */
function filterByCategory(category) {
  currentCategory = category;

  // Update tampilan tombol kategori
  document.querySelectorAll(".category-btn").forEach((btn) => {
    const isActive = btn.dataset.category === category;

    // Active classes
    btn.classList.toggle("bg-[#D97706]", isActive);
    btn.classList.toggle("text-white", isActive);
    btn.classList.toggle("shadow-md", isActive);
    btn.classList.toggle("shadow-amber-100", isActive);

    // Inactive classes
    btn.classList.toggle("bg-white", !isActive);
    btn.classList.toggle("text-gray-500", !isActive);
    btn.classList.toggle("border-gray-200", !isActive);
    btn.classList.toggle("hover:bg-gray-50", !isActive);
  });

  // Tampil/sembunyikan kartu produk
  document.querySelectorAll(".product-card").forEach((card) => {
    const visible =
      category === "semua" || card.dataset.kategori === String(category);
    card.style.display = visible ? "" : "none";
  });
}

// Real-time pencarian produk
document.getElementById("searchInput")?.addEventListener("input", function () {
  const keyword = this.value.toLowerCase().trim();
  document.querySelectorAll(".product-card").forEach((card) => {
    const name = card.querySelector("h4")?.innerText.toLowerCase() ?? "";
    const matchSearch = name.includes(keyword);
    const matchCat =
      currentCategory === "semua" ||
      card.dataset.kategori === String(currentCategory);
    card.style.display = matchSearch && matchCat ? "" : "none";
  });
});

// ─── Manajemen Keranjang ──────────────────────────────────────────────────────

/**
 * Tambah produk ke keranjang. Dipanggil dari onclick kartu produk.
 * @param {number} id
 * @param {string} nama
 * @param {number} harga
 * @param {number} stok
 */
function addToCart(id, nama, harga, stok) {
  if (stok <= 0) {
    showToast("Stok telah habis", "error");
    return;
  }

  const existing = cart.find((item) => item.id === id);
  if (existing) {
    if (existing.quantity >= stok) {
      showToast("Stok telah habis", "error");
      return;
    }
    existing.quantity += 1;
  } else {
    cart.push({ id, nama, harga, quantity: 1, stok: stok });
  }
  renderCart();
}

/**
 * Ubah kuantitas item. Jika kuantitas ≤ 0, hapus dari keranjang.
 * @param {number} id
 * @param {number} delta - +1 atau -1
 */
function updateQuantity(id, delta) {
  const item = cart.find((i) => i.id === id);
  if (!item) return;

  if (delta > 0 && item.quantity >= item.stok) {
    showToast("Stok telah habis", "error");
    return;
  }

  item.quantity += delta;
  if (item.quantity <= 0) {
    cart = cart.filter((i) => i.id !== id);
  }
  renderCart();
}

/**
 * Set kuantitas item secara langsung dari input.
 * @param {number} id
 * @param {number} value
 */
function setQuantity(id, value) {
  const item = cart.find((i) => i.id === id);
  if (!item) return;

  let qty = parseInt(value, 10) || 0;

  if (qty > item.stok) {
    showToast(`Stok tidak mencukupi (Maks: ${item.stok})`, "error");
    qty = item.stok;
  }

  if (qty <= 0) {
    cart = cart.filter((i) => i.id !== id);
  } else {
    item.quantity = qty;
  }
  renderCart();
}

// ─── Render Keranjang ─────────────────────────────────────────────────────────

/**
 * Render ulang tampilan keranjang dan hitung ulang total.
 */
function renderCart() {
  const container = document.getElementById("cart-items");
  const footer = document.getElementById("cart-footer");
  const totalEl = document.getElementById("total-price");
  const headerEl = document.getElementById("cart-count-header");

  const totalItems = cart.reduce((sum, i) => sum + i.quantity, 0);
  if (headerEl) headerEl.innerText = `Keranjang — ${totalItems} item`;

  // Update mobile badge
  const badgeMobile = document.getElementById("cart-badge-mobile");
  if (badgeMobile) {
    badgeMobile.innerText = totalItems;
    badgeMobile.classList.toggle("hidden", totalItems === 0);
  }

  // Kosong
  if (cart.length === 0) {
    container.innerHTML = `
            <div class="text-center py-20">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-cart-plus text-gray-200 text-3xl"></i>
                </div>
                <p class="text-gray-400 font-medium">Keranjang masih kosong</p>
            </div>`;
    footer?.classList.add("hidden");
    updateProductCardsVisuals();
    checkPaymentRules();
    return;
  }

  footer?.classList.remove("hidden");

  // Render item rows
  container.innerHTML = cart
    .map(
      (item) => `
        <div class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
            <div class="flex-1 min-w-0">
                <h5 class="font-bold text-[13px] text-gray-700 leading-tight mb-0.5 truncate">${item.nama}</h5>
                <p class="text-[11px] text-gray-400">Rp ${formatRp(item.harga)} / pcs</p>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
                <button onclick="updateQuantity(${item.id}, -1)"
                    class="w-7 h-7 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-red-50 hover:border-red-200 hover:text-red-500 transition shadow-sm font-bold">−</button>
                <input type="number" value="${item.quantity}" min="1" max="${item.stok}"
                    onchange="setQuantity(${item.id}, this.value)"
                    class="font-bold text-sm text-gray-700 w-10 text-center bg-gray-50 rounded-lg border-none focus:ring-1 focus:ring-amber-500 py-1 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                <button onclick="updateQuantity(${item.id}, 1)"
                    class="w-7 h-7 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-green-50 hover:border-green-200 hover:text-green-600 transition shadow-sm font-bold">+</button>
            </div>
            <div class="w-20 text-right flex-shrink-0">
                <p class="font-bold text-gray-700 text-sm">Rp ${formatRp(item.harga * item.quantity)}</p>
            </div>
        </div>
    `,
    )
    .join("");

  // Update total
  const total = getCartTotal();
  if (totalEl) totalEl.innerText = `Rp ${formatRp(total)}`;

  // Update kembalian jika input sudah ada
  updateChange();
  updateProductCardsVisuals();
  checkPaymentRules();
}

/**
 * Update visual state of all product cards based on remaining available stock.
 */
function updateProductCardsVisuals() {
  document.querySelectorAll(".product-card").forEach((card) => {
    const productId = parseInt(card.id.replace("product-card-", ""), 10);
    if (isNaN(productId)) return;

    const stokAsal = parseInt(card.dataset.stokAsal, 10) || 0;
    const cartItem = cart.find((item) => item.id === productId);
    const qtyInCart = cartItem ? cartItem.quantity : 0;
    const remainingStock = stokAsal - qtyInCart;

    // Update stock label
    const stockLabel = card.querySelector(".stock-label");
    if (stockLabel) {
      stockLabel.innerText = `STOK: ${remainingStock}`;
    }

    // Toggle disabled class and badge-habis
    const badgeHabis = card.querySelector(".badge-habis");
    if (remainingStock <= 0) {
      card.classList.add("pointer-events-none", "opacity-60");
      if (badgeHabis) badgeHabis.classList.remove("hidden");
    } else {
      card.classList.remove("pointer-events-none", "opacity-60");
      if (badgeHabis) badgeHabis.classList.add("hidden");
    }
  });
}

// ─── Pembayaran ───────────────────────────────────────────────────────────────

/**
 * Cek aturan bisnis pembayaran.
 */
function checkPaymentRules() {
  const total = getCartTotal();
  const qrisBtn = document.getElementById("pay-qris");

  if (total > 500000) {
    if (paymentMethod === "qris") {
      selectPayment("transfer");
    }
    if (qrisBtn) {
      qrisBtn.classList.add("opacity-50", "cursor-not-allowed");
      qrisBtn.disabled = true;
    }
  } else {
    if (qrisBtn) {
      qrisBtn.classList.remove("opacity-50", "cursor-not-allowed");
      qrisBtn.disabled = false;
    }
  }
}

/**
 * Pilih metode pembayaran. Dipanggil dari onclick tombol metode.
 * @param {string} method - 'tunai' | 'qris' | 'transfer'
 */
function selectPayment(method) {
  const total = getCartTotal();
  if (total > 500000 && method === 'qris') {
    showToast('Transaksi di atas Rp 500.000 tidak bisa menggunakan QRIS', 'error');
    return;
  }

  paymentMethod = method;

  // Update styling tombol
  ["tunai", "qris", "transfer"].forEach((m) => {
    const btn = document.getElementById(`pay-${m}`);
    if (!btn) return;
    btn.classList.toggle("active", m === method);
    btn.classList.toggle("border-[#D97706]", m === method);
    btn.classList.toggle("bg-[#FEF3C7]", m === method);
    btn.classList.toggle("border-gray-200", m !== method);
  });

  // Tunai: tampilkan input nominal, sembunyikan bukti & rekening
  const cashGroup = document.getElementById("cash-input-group");
  const buktiGroup = document.getElementById("bukti-group");
  const transferInfo = document.getElementById("transfer-info-group");
  const cashInput = document.getElementById("cash-amount");

  if (cashGroup)
    cashGroup.style.display = method === "tunai" ? "block" : "none";
  if (buktiGroup) buktiGroup.classList.toggle("hidden", method === "tunai");
  if (transferInfo)
    transferInfo.classList.toggle("hidden", method !== "transfer");

  if (cashInput && method !== "tunai") cashInput.value = "";
  updateChange();
}

// Tampilkan nama file yang dipilih untuk bukti pembayaran
document.getElementById("bukti-bayar")?.addEventListener("change", function () {
  const label = document.getElementById("bukti-filename");
  if (label)
    label.textContent = this.files[0]?.name ?? "Tidak ada file yang dipilih";
});

function updateChange() {
  const total = getCartTotal();
  const cashInput = document.getElementById("cash-amount");
  const changeEl = document.getElementById("change-amount");
  if (!changeEl) return;

  const bayar = parseInt(cashInput?.value ?? "0", 10) || 0;
  const change = bayar - total;

  const submitBtn = document.getElementById("confirm-payment");
  const isTunaiValid = paymentMethod === "tunai" ? change >= 0 : true;
  const isCartNotEmpty = cart.length > 0;

  if (submitBtn) {
    if (isTunaiValid && isCartNotEmpty) {
      submitBtn.disabled = false;
      submitBtn.className = "w-full bg-[#D97706] hover:bg-[#B45309] text-white py-3.5 rounded-2xl font-bold text-base flex items-center justify-center gap-2.5 transition-all shadow-lg active:scale-95";
    } else {
      submitBtn.disabled = true;
      submitBtn.className = "w-full bg-gray-300 text-gray-500 py-3.5 rounded-2xl font-bold text-base flex items-center justify-center gap-2.5 transition-all cursor-not-allowed";
    }
  }

  // Jika QRIS/Transfer, tidak perlu tampilkan kembalian
  if (paymentMethod !== "tunai") {
    changeEl.innerText = "Rp 0";
    changeEl.className = "text-lg font-bold text-gray-400";
    return;
  }

  changeEl.innerText = `Rp ${formatRp(Math.max(0, change))}`;
  if (change >= 0) {
    changeEl.className = "text-lg font-bold text-green-600";
  } else {
    changeEl.className = "text-lg font-bold text-red-500";
  }
}

// Bind input event pada input nominal tunai
document.getElementById("cash-amount")?.addEventListener("input", updateChange);

// ─── Submit Transaksi ─────────────────────────────────────────────────────────

document
  .getElementById("confirm-payment")
  ?.addEventListener("click", async function () {
    // ── Validasi frontend ──────────────────────────────────────────
    if (cart.length === 0) {
      showToast("Keranjang masih kosong!", "error");
      return;
    }

    const total = getCartTotal();
    const bayar =
      parseInt(document.getElementById("cash-amount")?.value ?? "0", 10) || 0;
    const catatan = document.getElementById("order-notes")?.value.trim() ?? "";

    if (paymentMethod === "tunai" && bayar < total) {
      showToast(`Jumlah bayar kurang Rp ${formatRp(total - bayar)}`, "error");
      return;
    }

    // ── Ubah tombol ke loading state ───────────────────────────────
    const btn = this;
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML =
      '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i>Memproses...';

    // ── Persiapkan payload (Gunakan FormData untuk mendukung upload file) ────
    const formData = new FormData();
    formData.append('items', JSON.stringify(cart.map(({ id, nama, harga, quantity }) => ({
      id_produk: id,
      nama,
      harga,
      quantity,
    }))));
    formData.append('total', total);
    formData.append('bayar', paymentMethod === "tunai" ? bayar : total);
    formData.append('kembali', paymentMethod === "tunai" ? Math.max(0, bayar - total) : 0);
    formData.append('metode', paymentMethod);
    formData.append('catatan', catatan);

    // Ambil file bukti jika ada
    const fileInput = document.getElementById('bukti-bayar');
    if (fileInput && fileInput.files[0]) {
      formData.append('bukti_pembayaran', fileInput.files[0]);
    }

    try {
      const response = await fetch("process_transaction.php", {
        method: "POST",
        body: formData, // Browser otomatis set Content-Type: multipart/form-data
      });

      // Tangani respons non-JSON (misal error PHP 500)
      const text = await response.text();
      let result;
      try {
        result = JSON.parse(text);
      } catch {
        throw new Error(
          "Response server bukan JSON: " + text.substring(0, 120),
        );
      }

      if (result.success) {
        // Pindah ke halaman struk di tab yang sama
        window.location.href = `cetak_struk.php?id=${result.transaction_id}`;
      } else {
        throw new Error(result.message || "Transaksi gagal.");
      }
    } catch (err) {
      console.error("[Transaction Error]", err);
      showToast("Gagal: " + err.message, "error");
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalHTML;
    }
  });

// ─── Toast Notification ───────────────────────────────────────────────────────

/**
 * Tampilkan notifikasi kecil di pojok layar.
 * @param {string} message
 * @param {'success'|'error'} type
 */
function showToast(message, type = "success") {
  // Hapus toast sebelumnya jika ada
  document.getElementById("app-toast")?.remove();

  const colors =
    type === "success" ? "bg-green-600 text-white" : "bg-red-600 text-white";

  const toast = document.createElement("div");
  toast.id = "app-toast";
  // Posisi di top-right dengan initial state geser ke kanan (translate-x)
  toast.className = `fixed top-6 right-6 z-[999] px-5 py-3 rounded-2xl shadow-2xl text-sm font-bold flex items-center gap-3 ${colors} transition-all duration-500 transform translate-x-[120%]`;
  toast.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation'}"></i> ${message}`;

  document.body.appendChild(toast);

  // Trigger masuk (slide in)
  setTimeout(() => {
    toast.classList.remove("translate-x-[120%]");
  }, 10);

  // Auto-remove setelah 3.5 detik
  setTimeout(() => {
    toast.classList.add("translate-x-[120%]");
    toast.style.opacity = "0";
    setTimeout(() => toast.remove(), 500);
  }, 3500);
}
