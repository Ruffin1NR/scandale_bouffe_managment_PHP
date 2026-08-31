<?php
$currentPage = 'pos';
$pageTitle = 'Caisse';

$search = trim($_GET['search'] ?? '');
$where = "WHERE p.active = 1";
$params = [];
if ($search !== '') {
    $where .= " AND p.name LIKE ?";
    $params[] = "%$search%";
}

$products = fetchAllAssoc("
    SELECT p.id, p.name, p.price, p.quantity, p.unit, p.image, (p.quantity <= p.min_stock) AS low_stock
    FROM products p
    $where
    ORDER BY p.name
", $params);

$todaySales = fetchAssoc("
    SELECT
        COALESCE(SUM(s.total), 0) AS revenue,
        COUNT(DISTINCT s.id) AS sale_count,
        COALESCE((SELECT SUM(si.quantity) FROM sale_items si WHERE si.sale_id IN (SELECT s2.id FROM sales s2 WHERE s2.user_id = ? AND DATE(s2.created_at) = CURDATE())), 0) AS items_sold
    FROM sales s
    WHERE s.user_id = ? AND DATE(s.created_at) = CURDATE()
", [$_SESSION['user_id'], $_SESSION['user_id']]) ?? ['revenue' => 0, 'sale_count' => 0, 'items_sold' => 0];

require __DIR__ . '/../layout.php';
?>

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <p class="text-2xl font-bold text-[#8B4513]"><?php echo number_format((float)$todaySales['revenue'], 0, ',', ' '); ?> Ar</p>
        <p class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]">Ventes du jour</p>
    </div>
    <div class="rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#FDFDFC]"><?php echo (int)$todaySales['sale_count']; ?></p>
        <p class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]">Transactions du jour</p>
    </div>
    <div class="rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <p class="text-2xl font-bold text-[#8B4513]"><?php echo (int)$todaySales['items_sold']; ?></p>
        <p class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]">Articles vendus aujourd'hui</p>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <div class="mb-4">
            <input
                type="text"
                id="search-input"
                placeholder="Rechercher un produit…"
                class="w-full rounded-lg border border-[#e3e3e0] bg-white px-4 py-2.5 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] placeholder:text-[#A1A09A] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]"
            />
        </div>

        <div id="product-grid" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($products as $product): ?>
            <div class="product-card group relative flex items-center gap-4 rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] transition hover:border-[#8B4513] disabled:cursor-not-allowed disabled:opacity-50 dark:bg-[#161615]"
                 data-product-id="<?php echo $product['id']; ?>"
                 data-name="<?php echo htmlspecialchars($product['name']); ?>"
                 data-price="<?php echo $product['price']; ?>"
                 data-max="<?php echo $product['quantity']; ?>"
                 data-unit="<?php echo htmlspecialchars($product['unit']); ?>"
                 <?php echo $product['quantity'] <= 0 ? 'data-disabled="1"' : ''; ?>>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-[#FDF3EA] dark:bg-[#0a0a0a]">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="h-full w-full object-cover" />
                    <?php else: ?>
                        <span class="text-lg font-bold text-[#8B4513]"><?php echo htmlspecialchars(mb_strtoupper(mb_substr($product['name'], 0, 1))); ?></span>
                    <?php endif; ?>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC]"><?php echo htmlspecialchars($product['name']); ?></p>
                    <p class="mt-0.5 text-sm font-medium text-[#8B4513]"><?php echo number_format((float)$product['price'], 0, ',', ' '); ?> Ar</p>
                    <p class="<?php echo $product['low_stock'] ? 'mt-0.5 text-xs font-medium text-[#FF750F]' : 'mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]'; ?>">
                        <?php echo (int)$product['quantity']; ?> <?php echo htmlspecialchars($product['unit']); ?> en stock
                    </p>
                </div>
                <div class="qty-controls hidden shrink-0 items-center gap-1">
                    <button type="button" onclick="gridUpdateQty(<?php echo $product['id']; ?>, -1)" class="flex h-7 w-7 items-center justify-center rounded-lg border border-[#e3e3e0] text-sm text-[#1b1b18] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#FDFDFC]">-</button>
                    <span class="grid-qty w-8 text-center text-sm font-bold text-[#8B4513]">0</span>
                    <button type="button" onclick="gridUpdateQty(<?php echo $product['id']; ?>, 1)" class="grid-plus flex h-7 w-7 items-center justify-center rounded-lg border border-[#e3e3e0] text-sm text-[#1b1b18] transition hover:border-[#8B4513] hover:text-[#8B4513] disabled:opacity-40 dark:border-[#3E3E3A] dark:text-[#FDFDFC]">+</button>
                </div>
                <span class="grid-add-icon flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-[#8B4513] text-[#8B4513] transition group-hover:opacity-100 <?php echo $product['quantity'] <= 0 ? '' : 'opacity-0'; ?>">+</span>
            </div>
            <?php endforeach; ?>
        </div>

        <p id="no-products" class="mt-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]" style="display:none;">Aucun produit trouvé.</p>
    </div>

    <div class="lg:sticky lg:top-8 lg:self-start">
        <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
            <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Panier</h3>

            <p id="cart-empty" class="mt-4 text-sm text-[#706f6c] dark:text-[#A1A09A]">Cliquez sur un produit pour l'ajouter au panier.</p>

            <ul id="cart-items" class="mt-4 space-y-3" style="display:none;"></ul>

            <div id="cart-total" class="mt-6 border-t border-[#e3e3e0] pt-4 dark:border-[#3E3E3A]" style="display:none;">
                <div class="flex items-center justify-between">
                    <span id="cart-count" class="text-sm text-[#706f6c] dark:text-[#A1A09A]"></span>
                    <span id="cart-total-value" class="text-xl font-bold text-[#1b1b18] dark:text-[#FDFDFC]"></span>
                </div>

                <form id="checkout-form" method="POST" action="<?php echo url('pos/checkout'); ?>" class="mt-4">
                    <div id="cart-hidden-inputs"></div>
                    <div class="mb-4">
                        <label class="mb-2 block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Mode de paiement</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="payment-option flex cursor-pointer items-center justify-center gap-1.5 rounded-lg border-2 border-[#8B4513] bg-[#8B4513]/10 px-2 py-2 text-xs font-medium text-[#8B4513] transition">
                                <input type="radio" name="payment_method" value="cash" checked class="sr-only">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Cash
                            </label>
                            <label class="payment-option flex cursor-pointer items-center justify-center gap-1.5 rounded-lg border-2 border-[#e3e3e0] bg-white px-2 py-2 text-xs font-medium text-[#706f6c] transition hover:border-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#A1A09A]">
                                <input type="radio" name="payment_method" value="mobile_money" class="sr-only">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                Mobile money
                            </label>
                            <label class="payment-option flex cursor-pointer items-center justify-center gap-1.5 rounded-lg border-2 border-[#e3e3e0] bg-white px-2 py-2 text-xs font-medium text-[#706f6c] transition hover:border-[#FF750F] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#A1A09A]">
                                <input type="radio" name="payment_method" value="credit" class="sr-only">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Crédit
                            </label>
                        </div>
                    </div>
                    <button type="submit" id="checkout-btn" class="w-full rounded-lg bg-[#8B4513] py-3 px-4 text-sm font-semibold text-[#FDFDFC] hover:bg-[#A0522D] transition shadow-sm">
                        Encaisser la vente
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const allProducts = <?php echo json_encode($products); ?>;
let cart = {};

function formatCurrency(value) {
    return value.toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' Ar';
}

function gridUpdateQty(productId, delta) {
    const card = document.querySelector(`.product-card[data-product-id="${productId}"]`);
    if (!card || card.dataset.disabled === '1') return;
    const max = parseInt(card.dataset.max);
    const current = cart[productId] ? cart[productId].quantity : 0;
    const newQty = current + delta;
    if (newQty < 0) return;
    if (newQty > max) return;
    if (newQty === 0) {
        delete cart[productId];
    } else {
        cart[productId] = {
            id: productId,
            name: card.dataset.name,
            price: parseFloat(card.dataset.price),
            max_quantity: max,
            unit: card.dataset.unit,
            quantity: newQty
        };
    }
    syncAll();
}

function addToCart(product) {
    if (product.quantity <= 0) return;
    const id = product.id;
    if (!cart[id]) {
        cart[id] = {
            id: product.id,
            name: product.name,
            price: parseFloat(product.price),
            max_quantity: parseInt(product.quantity),
            unit: product.unit,
            quantity: 1
        };
    } else {
        if (cart[id].quantity >= cart[id].max_quantity) return;
        cart[id].quantity++;
    }
    syncAll();
}

function removeFromCart(productId) {
    delete cart[productId];
    syncAll();
}

function updateQuantity(productId, delta) {
    if (!cart[productId]) return;
    cart[productId].quantity += delta;
    if (cart[productId].quantity <= 0) {
        removeFromCart(productId);
        return;
    }
    if (cart[productId].quantity > cart[productId].max_quantity) {
        cart[productId].quantity = cart[productId].max_quantity;
    }
    syncAll();
}

function syncAll() {
    syncGridCards();
    renderCart();
}

function syncGridCards() {
    document.querySelectorAll('.product-card').forEach(card => {
        const id = parseInt(card.dataset.productId);
        const qtyControls = card.querySelector('.qty-controls');
        const addIcon = card.querySelector('.grid-add-icon');
        const qtySpan = card.querySelector('.grid-qty');
        const plusBtn = card.querySelector('.grid-plus');
        const max = parseInt(card.dataset.max);
        if (cart[id]) {
            qtyControls.classList.remove('hidden');
            qtyControls.classList.add('flex');
            addIcon.classList.add('hidden');
            qtySpan.textContent = cart[id].quantity;
            plusBtn.disabled = cart[id].quantity >= max;
        } else {
            qtyControls.classList.add('hidden');
            qtyControls.classList.remove('flex');
            addIcon.classList.remove('hidden');
            qtySpan.textContent = '0';
            plusBtn.disabled = false;
        }
    });
}

function renderCart() {
    const cartEmpty = document.getElementById('cart-empty');
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');
    const cartCount = document.getElementById('cart-count');
    const cartTotalValue = document.getElementById('cart-total-value');
    const cartHiddenInputs = document.getElementById('cart-hidden-inputs');

    const entries = Object.values(cart);

    if (entries.length === 0) {
        cartEmpty.style.display = '';
        cartItems.style.display = 'none';
        cartTotal.style.display = 'none';
        return;
    }

    cartEmpty.style.display = 'none';
    cartItems.style.display = '';
    cartTotal.style.display = '';

    let total = 0;
    let itemCount = 0;
    let html = '';
    let inputs = '';

    entries.forEach(item => {
        const lineTotal = item.price * item.quantity;
        total += lineTotal;
        itemCount += item.quantity;
        html += '<li class="flex items-center justify-between gap-3">' +
            '<div class="min-w-0">' +
                '<p class="truncate text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC]">' + item.name + '</p>' +
                '<p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">' + formatCurrency(item.price) + ' / ' + item.unit + '</p>' +
            '</div>' +
            '<div class="flex items-center gap-1">' +
                '<button type="button" onclick="updateQuantity(' + item.id + ', -1)" class="flex h-7 w-7 items-center justify-center rounded-lg border border-[#e3e3e0] text-sm text-[#1b1b18] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#FDFDFC]">\u2212</button>' +
                '<span class="w-8 text-center text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">' + item.quantity + '</span>' +
                '<button type="button" onclick="updateQuantity(' + item.id + ', 1)" ' + (item.quantity >= item.max_quantity ? 'disabled' : '') + ' class="flex h-7 w-7 items-center justify-center rounded-lg border border-[#e3e3e0] text-sm text-[#1b1b18] transition hover:border-[#8B4513] hover:text-[#8B4513] disabled:opacity-40 dark:border-[#3E3E3A] dark:text-[#FDFDFC]">+</button>' +
            '</div>' +
            '<button type="button" onclick="removeFromCart(' + item.id + ')" class="ml-2 text-sm text-red-500 hover:text-red-700 transition">\u2715</button>' +
        '</li>';
        inputs += '<input type="hidden" name="items[' + item.id + '][product_id]" value="' + item.id + '">' +
                   '<input type="hidden" name="items[' + item.id + '][quantity]" value="' + item.quantity + '">';
    });

    cartItems.innerHTML = html;
    cartCount.textContent = itemCount + ' article(s)';
    cartTotalValue.textContent = formatCurrency(total);
    cartHiddenInputs.innerHTML = inputs;
}

document.querySelectorAll('.product-card').forEach(card => {
    if (card.dataset.disabled === '1') return;
    card.addEventListener('click', function(e) {
        if (e.target.closest('button')) return;
        addToCart({
            id: parseInt(this.dataset.productId),
            name: this.dataset.name,
            price: this.dataset.price,
            quantity: parseInt(this.dataset.max),
            unit: this.dataset.unit
        });
    });
});

document.getElementById('search-input').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    const grid = document.getElementById('product-grid');
    const noProducts = document.getElementById('no-products');
    const cards = grid.querySelectorAll('.product-card');
    let visible = 0;
    cards.forEach(card => {
        const name = card.dataset.name.toLowerCase();
        if (name.includes(q)) {
            card.style.display = '';
            visible++;
        } else {
            card.style.display = 'none';
        }
    });
    noProducts.style.display = visible === 0 ? '' : 'none';
});

document.querySelectorAll('.payment-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.payment-option').forEach(label => {
            const input = label.querySelector('input[type="radio"]');
            if (input.checked) {
                label.className = label.className.replace('border-[#e3e3e0] bg-white text-[#706f6c] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#A1A09A]', '').trim();
                if (input.value === 'credit') {
                    label.className += ' border-[#FF750F] bg-[#FF750F]/10 text-[#FF750F]';
                } else {
                    label.className += ' border-[#8B4513] bg-[#8B4513]/10 text-[#8B4513]';
                }
            } else {
                label.className = label.className.replace(/border-\[#(8B4513|FF750F)\]\s+bg-\[#(8B4513|FF750F)\]\/10\s+text-\[#(8B4513|FF750F)\]/g, '').trim();
                if (!label.className.includes('border-[#e3e3e0]')) {
                    label.className += ' border-[#e3e3e0] bg-white text-[#706f6c] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#A1A09A]';
                }
            }
        });
        const btn = document.getElementById('checkout-btn');
        if (this.value === 'credit') {
            btn.textContent = 'Enregistrer le crédit';
            btn.className = btn.className.replace('bg-[#8B4513] hover:bg-[#A0522D]', 'bg-[#FF750F] hover:bg-[#e66a0d]');
        } else {
            btn.textContent = 'Encaisser la vente';
            btn.className = btn.className.replace('bg-[#FF750F] hover:bg-[#e66a0d]', 'bg-[#8B4513] hover:bg-[#A0522D]');
        }
    });
});
</script>

<?php require __DIR__ . '/../layout_footer.php'; ?>
