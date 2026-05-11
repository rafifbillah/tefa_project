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
const csrfToken = window.KASIR_CSRF_TOKEN || "";

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
 */
function addToCart(id, nama, harga) {
  const existing = cart.find((item) => item.id === id);
  if (existing) {
    existing.quantity += 1;
  } else {
    cart.push({ id, nama, harga, quantity: 1 });
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

  item.quantity += delta;
  if (item.quantity <= 0) {
    cart = cart.filter((i) => i.id !== id);
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
            <div class="flex items-center gap-2 flex-shrink-0">
                <button onclick="updateQuantity(${item.id}, -1)"
                    class="w-7 h-7 rounded-full border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-red-50 hover:border-red-200 hover:text-red-500 transition shadow-sm font-bold">−</button>
                <span class="font-bold text-sm text-gray-700 w-5 text-center">${item.quantity}</span>
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
}

// ─── Pembayaran ───────────────────────────────────────────────────────────────

/**
 * Pilih metode pembayaran. Dipanggil dari onclick tombol metode.
 * @param {string} method - 'tunai' | 'qris' | 'transfer'
 */
function selectPayment(method) {
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

/**
 * Hitung dan tampilkan kembalian secara real-time.
 */
function updateChange() {
  const total = getCartTotal();
  const cashInput = document.getElementById("cash-amount");
  const changeEl = document.getElementById("change-amount");
  if (!changeEl) return;

  const bayar = parseInt(cashInput?.value ?? "0", 10) || 0;
  const change = bayar - total;

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

    // ── Kirim payload ke backend OOP ───────────────────────────────
    const payload = {
      items: cart.map(({ id, nama, harga, quantity }) => ({
        id,
        nama,
        harga,
        quantity,
      })),
      total: total,
      bayar: paymentMethod === "tunai" ? bayar : total,
      kembali: paymentMethod === "tunai" ? Math.max(0, bayar - total) : 0,
      metode: paymentMethod,
      catatan: catatan,
    };

    try {
      const response = await fetch("process_transaction.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-Token": csrfToken,
        },
        body: JSON.stringify(payload),
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
        // Buka struk di tab baru, lalu reset halaman kasir agar siap untuk antrean berikutnya
        window.open(`cetak_struk.php?id=${result.transaction_id}`, '_blank');
        window.location.reload();
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
  toast.className = `fixed bottom-6 right-6 z-[999] px-5 py-3 rounded-2xl shadow-xl text-sm font-bold flex items-center gap-3 ${colors} transition-all duration-300`;
  toast.innerHTML = message;

  document.body.appendChild(toast);

  // Auto-remove setelah 3.5 detik
  setTimeout(() => {
    toast.style.opacity = "0";
    toast.style.transform = "translateY(10px)";
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}
