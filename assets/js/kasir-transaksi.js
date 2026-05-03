let cart = [];
let currentCategory = "semua"; // Track kategori yang dipilih

function filterByCategory(category) {
  currentCategory = category;
  const productCards = document.querySelectorAll(".product-card");
  const categoryButtons = document.querySelectorAll(".category-btn");

  // Update button styling
  categoryButtons.forEach((btn) => {
    if (btn.dataset.category === category) {
      btn.classList.remove("bg-gray-200", "text-gray-500", "hover:bg-gray-300");
      btn.classList.add("bg-gray-300", "text-gray-700");
    } else {
      btn.classList.remove("bg-gray-300", "text-gray-700");
      btn.classList.add("bg-gray-200", "text-gray-500", "hover:bg-gray-300");
    }
  });

  // Filter produk berdasarkan kategori
  productCards.forEach((card) => {
    if (category === "semua" || card.dataset.kategori === category) {
      card.style.display = "";
    } else {
      card.style.display = "none";
    }
  });
}

function addToCart(id, nama, harga) {
  const existingItem = cart.find((item) => item.id === id);

  if (existingItem) {
    existingItem.quantity += 1;
  } else {
    cart.push({ id, nama, harga, quantity: 1 });
  }
  renderCart();
}

function updateQuantity(id, delta) {
  const item = cart.find((item) => item.id === id);
  if (item) {
    item.quantity += delta;
    if (item.quantity <= 0) {
      cart = cart.filter((i) => i.id !== id);
    }
  }
  renderCart();
}

function renderCart() {
  const cartContainer = document.getElementById("cart-items");
  const footer = document.getElementById("cart-footer");
  const totalPriceElement = document.getElementById("total-price");

  if (cart.length === 0) {
    cartContainer.innerHTML = `
            <div id="empty-cart-msg" class="text-center py-10">
                <i class="fa-solid fa-cart-shopping text-gray-200 text-5xl mb-3"></i>
                <p class="text-gray-400 text-sm">Belum ada produk dipilih</p>
            </div>`;
    footer.classList.add("hidden");
    return;
  }

  footer.classList.remove("hidden");

  cartContainer.innerHTML = cart
    .map(
      (item) => `
        <div class="flex items-center gap-4 animate-fadeIn">
            <div class="w-16 h-16 bg-gray-200 rounded-xl flex-shrink-0"></div>
            <div class="flex-1">
                <h5 class="font-bold text-sm text-gray-800 leading-tight">${item.nama}</h5>
                <div class="flex items-center gap-3 mt-2">
                    <button onclick="updateQuantity(${item.id}, -1)" class="w-6 h-6 rounded-full border border-gray-300 flex items-center justify-center text-gray-500 hover:bg-gray-100 text-xs">-</button>
                    <span class="font-bold text-sm">${item.quantity}</span>
                    <button onclick="updateQuantity(${item.id}, 1)" class="w-6 h-6 rounded-full border border-gray-300 flex items-center justify-center text-gray-500 hover:bg-gray-100 text-xs">+</button>
                </div>
            </div>
            <div class="text-right">
                <p class="font-bold text-gray-800">$${(item.harga * item.quantity).toFixed(2)}</p>
            </div>
        </div>
    `,
    )
    .join("");

  const total = cart.reduce((sum, item) => sum + item.harga * item.quantity, 0);
  totalPriceElement.innerText = `$${total.toFixed(2)}`;
}
