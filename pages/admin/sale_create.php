<?php
$currentPage = 'sales';
$pageTitle = 'Nouvelle vente';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $items = $_POST['items'] ?? [];
    $total = 0;
    $validItems = [];
    $qtyByProduct = [];

    foreach ($items as $item) {
        $productId = (int)($item['product_id'] ?? 0);
        $qty = (int)($item['quantity'] ?? 0);
        if ($productId > 0 && $qty > 0) {
            $qtyByProduct[$productId] = ($qtyByProduct[$productId] ?? 0) + $qty;
        }
    }

    foreach ($items as $item) {
        $product_id = (int)($item['product_id'] ?? 0);
        $qty = (int)($item['quantity'] ?? 0);
        if ($product_id <= 0 || $qty <= 0) continue;

        $product = fetchAssoc("
            SELECT p.id, p.name, p.price, p.quantity, p.active, COALESCE(c.track_stock, 0) AS track_stock
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.id = ?
        ", [$product_id]);
        if (!$product || !$product['active']) continue;
        if ((int)$product['track_stock'] === 1 && $product['quantity'] < $qtyByProduct[$product_id]) {
            $_SESSION['error'] = "Stock insuffisant pour \"" . $product['name'] . "\" (demandé : " . $qtyByProduct[$product_id] . ", dispo: " . $product['quantity'] . ").";
            header('Location: ' . url('sales/create'));
            exit;
        }
        $lineTotal = $product['price'] * $qty;
        $total += $lineTotal;
        $validItems[] = [
            'product_id' => $product['id'],
            'product_name' => $product['name'],
            'quantity' => $qty,
            'unit_price' => $product['price'],
            'line_total' => $lineTotal,
            'track_stock' => (int)$product['track_stock'],
        ];
    }

    if (empty($validItems)) {
        $_SESSION['error'] = "Aucun article valide.";
        header('Location: ' . url('sales/create'));
        exit;
    }

    $paymentMethod = in_array($_POST['payment_method'] ?? '', salePaymentMethods(), true)
        ? $_POST['payment_method']
        : 'cash';
    $pdo = $GLOBALS['pdo'];
    $pdo->beginTransaction();
    try {
        query("INSERT INTO sales (user_id, total, payment_method, created_at) VALUES (?, ?, ?, NOW())", [$_SESSION['user_id'], $total, $paymentMethod]);
        $saleId = $pdo->lastInsertId();

        $stockUserId = (int)($_SESSION['user_id'] ?? 0) ?: null;

        foreach ($validItems as $item) {
            query("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, pay_method) VALUES (?, ?, ?, ?, ?)", [$saleId, $item['product_id'], $item['quantity'], $item['unit_price'], $paymentMethod]);
            if ((int)($item['track_stock'] ?? 0) === 1) {
                $currentProduct = fetchAssoc("SELECT quantity FROM products WHERE id = ? FOR UPDATE", [$item['product_id']]);
                ensureInitialStockMovement((int)$item['product_id'], (int)($currentProduct['quantity'] ?? 0), 'Stock initial', $stockUserId);
                query("UPDATE products SET quantity = quantity - ? WHERE id = ?", [$item['quantity'], $item['product_id']]);
                addStockMovement((int)$item['product_id'], 'out', (int)$item['quantity'], 'sale', 'Vente admin #' . $saleId, (int)$saleId, $stockUserId);
            }
        }

        $pdo->commit();
        $methodLabel = salePaymentMethodLabel($paymentMethod);
        $_SESSION['success'] = "Vente enregistrée (" . number_format($total, 0, ',', ' ') . " Ar — $methodLabel).";
        header('Location: ' . url('sales'));
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur lors de l'enregistrement.";
        header('Location: ' . url('sales/create'));
        exit;
    }
}
?>
<?php require __DIR__ . '/../layout.php'; ?>

<?php
$products = fetchAllAssoc("
    SELECT p.id, p.name, p.price, p.quantity, p.unit, COALESCE(c.track_stock, 0) AS track_stock
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.active = 1
    ORDER BY p.name
");
?>

<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-xl font-semibold leading-tight text-[#1b1b18] dark:text-[#FDFDFC]">Nouvelle vente</h2>
        <a href="<?php echo url('sales'); ?>" class="text-sm text-[#706f6c] hover:text-[#8B4513] dark:text-[#A1A09A] transition">Retour aux ventes</a>
    </div>

    <form method="POST" id="sale-form" class="space-y-6">
        <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
            <h3 class="text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC] mb-4">Articles</h3>

            <div id="items-container" class="space-y-3">
                <div class="sale-item flex items-center gap-3">
                    <select name="items[0][product_id]" required class="flex-1 rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
                        <option value="">Choisir un produit</option>
                        <?php foreach ($products as $p): ?>
                        <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['price']; ?>" data-stock="<?php echo (int)$p['track_stock'] === 1 ? $p['quantity'] : ''; ?>" data-track-stock="<?php echo (int)$p['track_stock']; ?>" data-unit="<?php echo htmlspecialchars($p['unit']); ?>">
                            <?php echo htmlspecialchars($p['name']); ?> — <?php echo number_format($p['price'], 0, ',', ' '); ?> Ar / <?php echo $p['unit']; ?>
                            <?php if ((int)$p['track_stock'] === 1): ?>
                                (stock: <?php echo $p['quantity']; ?>)
                            <?php else: ?>
                                (stock non suivi)
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="items[0][quantity]" min="1" value="1" required class="w-20 rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-center text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]" placeholder="Qté">
                    <span class="item-total text-sm font-medium text-[#8B4513] w-28 text-right">0 Ar</span>
                    <button type="button" onclick="removeItem(this)" class="p-1.5 rounded-lg text-[#706f6c] hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition" title="Retirer">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <button type="button" onclick="addItem()" class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-[#8B4513] hover:text-[#A0522D] transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Ajouter un article
            </button>
        </div>

        <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
            <div class="flex items-center justify-between">
                <span class="text-lg font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Total</span>
                <span id="grand-total" class="text-2xl font-bold text-[#8B4513]">0 Ar</span>
            </div>
            <div class="mt-4 mb-4">
                <label class="mb-2 block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Mode de paiement</label>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <label class="flex cursor-pointer items-center justify-center gap-1.5 rounded-lg border-2 border-[#8B4513] bg-[#8B4513]/10 px-3 py-2 text-sm font-medium text-[#8B4513] transition">
                        <input type="radio" name="payment_method" value="cash" checked class="sr-only">
                        Cash
                    </label>
                    <label class="flex cursor-pointer items-center justify-center gap-1.5 rounded-lg border-2 border-[#e3e3e0] bg-white px-3 py-2 text-sm font-medium text-[#706f6c] transition hover:border-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#A1A09A]">
                        <input type="radio" name="payment_method" value="prepaye" class="sr-only">
                        Prépayé
                    </label>
                    <label class="flex cursor-pointer items-center justify-center gap-1.5 rounded-lg border-2 border-[#e3e3e0] bg-white px-3 py-2 text-sm font-medium text-[#706f6c] transition hover:border-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#A1A09A]">
                        <input type="radio" name="payment_method" value="trosa" class="sr-only">
                        Trosa
                    </label>
                    <label class="flex cursor-pointer items-center justify-center gap-1.5 rounded-lg border-2 border-[#e3e3e0] bg-white px-3 py-2 text-sm font-medium text-[#706f6c] transition hover:border-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#A1A09A]">
                        <input type="radio" name="payment_method" value="mobile" class="sr-only">
                        Mobile
                    </label>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" id="submit-btn" class="rounded-lg bg-[#8B4513] px-5 py-2.5 text-sm font-medium text-[#FDFDFC] hover:bg-[#A0522D] transition">Enregistrer la vente</button>
                <a href="<?php echo url('sales'); ?>" class="text-sm text-[#706f6c] hover:text-[#8B4513] dark:text-[#A1A09A] transition">Annuler</a>
            </div>
        </div>
    </form>
</div>

<script>
let itemIndex = 1;

function addItem() {
    const container = document.getElementById('items-container');
    const products = document.querySelector('.sale-item select').innerHTML;
    const div = document.createElement('div');
    div.className = 'sale-item flex items-center gap-3';
    div.innerHTML = `
        <select name="items[${itemIndex}][product_id]" required class="flex-1 rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
            ${products}
        </select>
        <input type="number" name="items[${itemIndex}][quantity]" min="1" value="1" required class="w-20 rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-center text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]" placeholder="Qté">
        <span class="item-total text-sm font-medium text-[#8B4513] w-28 text-right">0 Ar</span>
        <button type="button" onclick="removeItem(this)" class="p-1.5 rounded-lg text-[#706f6c] hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition" title="Retirer">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    `;
    container.appendChild(div);
    itemIndex++;
    div.querySelector('select').addEventListener('change', updateTotals);
    div.querySelector('input[type="number"]').addEventListener('input', updateTotals);
    updateTotals();
}

function removeItem(btn) {
    const items = document.querySelectorAll('.sale-item');
    if (items.length <= 1) return;
    btn.closest('.sale-item').remove();
    updateTotals();
}

function updateTotals() {
    let grandTotal = 0;
    document.querySelectorAll('.sale-item').forEach(row => {
        const select = row.querySelector('select');
        const qty = parseInt(row.querySelector('input[type="number"]').value) || 0;
        const opt = select.options[select.selectedIndex];
        const price = parseFloat(opt.dataset.price) || 0;
        const lineTotal = price * qty;
        grandTotal += lineTotal;
        row.querySelector('.item-total').textContent = lineTotal.toLocaleString('fr-FR') + ' Ar';
    });
    document.getElementById('grand-total').textContent = grandTotal.toLocaleString('fr-FR') + ' Ar';
}

document.querySelectorAll('.sale-item select').forEach(s => s.addEventListener('change', updateTotals));
document.querySelectorAll('.sale-item input[type="number"]').forEach(i => i.addEventListener('input', updateTotals));

document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('input[name="payment_method"]').forEach(r => {
            const label = r.closest('label');
            if (r.checked) {
                label.className = label.className.replace(/border-\[#(e3e3e0|3E3E3A)\]/g, '').trim();
                if (r.value === 'trosa') {
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
        const btn = document.getElementById('submit-btn');
        if (this.value === 'trosa') {
            btn.textContent = 'Enregistrer le crédit';
            btn.className = btn.className.replace('bg-[#8B4513] hover:bg-[#A0522D]', 'bg-[#FF750F] hover:bg-[#e66a0d]');
        } else {
            btn.textContent = 'Enregistrer la vente';
            btn.className = btn.className.replace('bg-[#FF750F] hover:bg-[#e66a0d]', 'bg-[#8B4513] hover:bg-[#A0522D]');
        }
    });
});
</script>

<?php require __DIR__ . '/../layout_footer.php'; ?>
