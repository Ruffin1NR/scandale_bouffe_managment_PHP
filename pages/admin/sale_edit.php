<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/db.php';

if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'administrateur') {
    header('Location: ' . url('login'));
    exit;
}

$currentPage = 'sales';
$pageTitle = 'Modifier la vente';

$saleId = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
if ($saleId <= 0) {
    $_SESSION['error'] = "Vente introuvable.";
    header('Location: ' . url('sales'));
    exit;
}

$sale = fetchAssoc("
    SELECT s.*, u.name AS vendor_name
    FROM sales s
    LEFT JOIN users u ON u.id = s.user_id
    WHERE s.id = ?
", [$saleId]);

if (empty($sale)) {
    $_SESSION['error'] = "Vente introuvable.";
    header('Location: ' . url('sales'));
    exit;
}

$products = fetchAllAssoc("
    SELECT p.id, p.name, p.price, p.quantity, p.unit, p.active, COALESCE(c.track_stock, 0) AS track_stock
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    ORDER BY p.active DESC, p.name
");
$productsById = [];
foreach ($products as $product) {
    $productsById[(int)$product['id']] = $product;
}

$dbItems = fetchAllAssoc("SELECT * FROM sale_items WHERE sale_id = ? ORDER BY id", [$saleId]);
$items = $dbItems;

$createdAtValue = date('Y-m-d\TH:i', strtotime($sale['created_at']));
$formError = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $createdAtInput = trim((string)($_POST['created_at'] ?? ''));
    $createdAtValue = $createdAtInput;

    $dt = DateTime::createFromFormat('Y-m-d\TH:i', $createdAtInput);
    $inputFormat = 'Y-m-d\TH:i';
    if ($dt === false) {
        $dt = DateTime::createFromFormat('Y-m-d\TH:i:s', $createdAtInput);
        $inputFormat = 'Y-m-d\TH:i:s';
    }
    $lastErrors = DateTime::getLastErrors();
    if (
        $dt === false
        || ($lastErrors !== false && ($lastErrors['warning_count'] > 0 || $lastErrors['error_count'] > 0))
        || $dt->format($inputFormat) !== $createdAtInput
    ) {
        $formError = "Date et heure invalides.";
    } elseif ($dt->format('Y-m-d') > date('Y-m-d')) {
        $formError = "La date de la vente ne peut pas être dans le futur.";
    }

    $rawItems = $_POST['items'] ?? [];
    if (!is_array($rawItems)) {
        $rawItems = [];
    }

    $postedItems = [];
    $newQtyByProduct = [];
    foreach ($rawItems as $row) {
        if (!is_array($row)) {
            continue;
        }
        $productId = (int)($row['product_id'] ?? 0);
        $quantity = (int)($row['quantity'] ?? 0);
        $unitPrice = (float)($row['unit_price'] ?? 0);
        $payMethod = in_array($row['pay_method'] ?? '', salePaymentMethods(), true)
            ? $row['pay_method']
            : 'cash';

        if ($productId <= 0 || $quantity <= 0 || !isset($productsById[$productId])) {
            continue;
        }
        if ($unitPrice < 0) {
            $unitPrice = 0;
        }

        $newQtyByProduct[$productId] = ($newQtyByProduct[$productId] ?? 0) + $quantity;
        $postedItems[] = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'pay_method' => $payMethod,
        ];
    }

    $items = $postedItems;
    $salePaymentMethod = deriveSalePaymentMethod(array_column($postedItems, 'pay_method'));
    if ($formError === '' && empty($postedItems)) {
        $formError = "La vente doit contenir au moins un article valide.";
    }

    if ($formError === '') {
        $pdo = $GLOBALS['pdo'];
        $pdo->beginTransaction();
        try {
            $lockedSale = fetchAssoc("SELECT id FROM sales WHERE id = ? FOR UPDATE", [$saleId]);
            if (empty($lockedSale)) {
                throw new Exception("Vente introuvable.");
            }

            $oldItems = fetchAllAssoc("SELECT product_id, quantity FROM sale_items WHERE sale_id = ?", [$saleId]);
            $oldQtyByProduct = [];
            foreach ($oldItems as $oldItem) {
                $oldProductId = (int)$oldItem['product_id'];
                $oldQtyByProduct[$oldProductId] = ($oldQtyByProduct[$oldProductId] ?? 0) + (int)$oldItem['quantity'];
            }

            $allProductIds = array_values(array_unique(array_merge(
                array_keys($oldQtyByProduct),
                array_keys($newQtyByProduct)
            )));

            $lockedProducts = [];
            if (!empty($allProductIds)) {
                $placeholders = implode(',', array_fill(0, count($allProductIds), '?'));
                $lockedRows = fetchAllAssoc("
                    SELECT p.id, p.name, p.quantity, COALESCE(c.track_stock, 0) AS track_stock
                    FROM products p
                    LEFT JOIN categories c ON c.id = p.category_id
                    WHERE p.id IN ($placeholders)
                    FOR UPDATE
                ", $allProductIds);
                foreach ($lockedRows as $lockedRow) {
                    $lockedProducts[(int)$lockedRow['id']] = $lockedRow;
                }
            }

            // Le stock disponible pour cette vente inclut les quantités déjà vendues
            // par cette même vente (elles vont être réintégrées avant la nouvelle déduction).
            // Seuls les produits des catégories suivies sont contrôlés.
            foreach ($allProductIds as $productId) {
                if ((int)($lockedProducts[$productId]['track_stock'] ?? 0) !== 1) {
                    continue;
                }
                $currentStock = (int)($lockedProducts[$productId]['quantity'] ?? 0);
                $available = $currentStock + (int)($oldQtyByProduct[$productId] ?? 0);
                $needed = (int)($newQtyByProduct[$productId] ?? 0);
                if ($needed > $available) {
                    $productName = $lockedProducts[$productId]['name'] ?? ('#' . $productId);
                    throw new Exception("Stock insuffisant pour \"$productName\" (disponible pour cette vente : $available, demandé : $needed).");
                }
            }

            $stockUserId = (int)($_SESSION['user_id'] ?? 0) ?: null;

            foreach ($allProductIds as $productId) {
                if ((int)($lockedProducts[$productId]['track_stock'] ?? 0) !== 1) {
                    continue;
                }
                $delta = (int)($oldQtyByProduct[$productId] ?? 0) - (int)($newQtyByProduct[$productId] ?? 0);
                if ($delta !== 0) {
                    ensureInitialStockMovement($productId, (int)($lockedProducts[$productId]['quantity'] ?? 0), 'Stock initial', $stockUserId);
                    query("UPDATE products SET quantity = quantity + ? WHERE id = ?", [$delta, $productId]);
                    if ($delta > 0) {
                        addStockMovement($productId, 'in', $delta, 'sale_edit', 'Modification vente #' . $saleId, $saleId, $stockUserId);
                    } else {
                        addStockMovement($productId, 'out', abs($delta), 'sale_edit', 'Modification vente #' . $saleId, $saleId, $stockUserId);
                    }
                }
            }

            query("DELETE FROM sale_items WHERE sale_id = ?", [$saleId]);

            $newTotal = 0;
            foreach ($postedItems as $item) {
                $newTotal += $item['quantity'] * $item['unit_price'];
                query(
                    "INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, pay_method) VALUES (?, ?, ?, ?, ?)",
                    [$saleId, $item['product_id'], $item['quantity'], $item['unit_price'], $item['pay_method']]
                );
            }
            $newTotal = round($newTotal, 2);

            query(
                "UPDATE sales SET total = ?, payment_method = ?, created_at = ?, updated_at = NOW() WHERE id = ?",
                [$newTotal, $salePaymentMethod, $dt->format('Y-m-d H:i:s'), $saleId]
            );

            $pdo->commit();
            $_SESSION['success'] = "Vente #$saleId modifiée avec succès.";
            header('Location: ' . url('sales'));
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $formError = $e->getMessage();
        }
    }
}

if (empty($items)) {
    $items = [['product_id' => 0, 'quantity' => 1, 'unit_price' => 0, 'pay_method' => 'cash']];
}

$summaryMethods = [];
foreach ($items as $summaryItem) {
    $summaryMethod = $summaryItem['pay_method'] ?? 'cash';
    if (in_array($summaryMethod, salePaymentMethods(), true)) {
        $summaryMethods[$summaryMethod] = true;
    }
}
$summaryMethods = array_values(array_intersect(salePaymentMethods(), array_keys($summaryMethods)));

$nextItemIndex = 0;
foreach (array_keys($items) as $itemKey) {
    $nextItemIndex = max($nextItemIndex, (int)$itemKey + 1);
}
$productOptions = '';
foreach ($products as $product) {
    $productOptions .= '<option value="' . (int)$product['id'] . '" data-price="' . (float)$product['price'] . '">'
        . htmlspecialchars($product['name'])
        . ' - ' . number_format((float)$product['price'], 0, ',', ' ') . ' Ar'
        . ((int)$product['track_stock'] === 1
            ? ' (stock: ' . (int)$product['quantity'] . ')'
            : ' (stock non suivi)')
        . ((int)$product['active'] === 1 ? '' : ' (inactif)')
        . '</option>';
}
?>
<?php require __DIR__ . '/../layout.php'; ?>

<div class="mx-auto max-w-4xl">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-[#1b1b18] dark:text-[#FDFDFC]">Modifier la vente #<?= (int)$saleId ?></h2>
            <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Vendeur : <?= htmlspecialchars($sale['vendor_name'] ?? '—') ?>
                · Enregistrée le <?= date('d/m/Y H:i', strtotime($sale['created_at'])) ?>
            </p>
        </div>
        <a href="<?= url('sales') ?>" class="text-sm text-[#706f6c] transition hover:text-[#8B4513] dark:text-[#A1A09A]">Retour aux ventes</a>
    </div>

    <?php if ($formError !== ''): ?>
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300">
            <?= htmlspecialchars($formError) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= url('sales/edit?id=' . $saleId) ?>" id="sale-edit-form" class="space-y-6">
        <input type="hidden" name="id" value="<?= (int)$saleId ?>">

        <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
            <h3 class="mb-4 text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Informations de la vente</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="created_at" class="mb-1 block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Date et heure</label>
                    <input
                        type="datetime-local"
                        id="created_at"
                        name="created_at"
                        value="<?= htmlspecialchars($createdAtValue) ?>"
                        required
                        class="w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]"
                    >
                </div>
                <div>
                    <span class="mb-1 block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Modes de paiement</span>
                    <div id="payment-summary" class="flex min-h-[42px] flex-wrap items-center gap-1.5 rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]">
                        <?php if (empty($summaryMethods)): ?>
                            <span class="text-sm text-[#A1A09A]">—</span>
                        <?php else: ?>
                            <?php foreach ($summaryMethods as $summaryMethod): ?>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium <?= salePaymentMethodBadgeClass($summaryMethod) ?>">
                                    <?= salePaymentMethodLabel($summaryMethod) ?>
                                </span>
                            <?php endforeach; ?>
                            <?php if (count($summaryMethods) > 1): ?>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium <?= salePaymentMethodBadgeClass('mixed') ?>">Mixte</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <p class="mt-1 text-[11px] text-[#706f6c] dark:text-[#A1A09A]">Définis pour chaque article ci-dessous.</p>
                </div>
            </div>
        </div>

        <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
            <h3 class="mb-4 text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Articles</h3>

            <div id="items-container" class="space-y-3">
                <?php foreach ($items as $index => $item): ?>
                    <?php
                    $itemProductId = (int)($item['product_id'] ?? 0);
                    $itemProduct = $productsById[$itemProductId] ?? null;
                    ?>
                    <div class="sale-item flex flex-wrap items-center gap-3">
                        <select name="items[<?= (int)$index ?>][product_id]" class="product-select min-w-[220px] flex-1 rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
                            <option value="">Choisir un produit</option>
                            <?php if ($itemProduct === null && $itemProductId > 0): ?>
                                <option value="<?= $itemProductId ?>" data-price="<?= (float)($item['unit_price'] ?? 0) ?>" selected>Produit #<?= $itemProductId ?> (indisponible)</option>
                            <?php endif; ?>
                            <?php foreach ($products as $product): ?>
                                <option
                                    value="<?= (int)$product['id'] ?>"
                                    data-price="<?= (float)$product['price'] ?>"
                                    <?= $itemProductId === (int)$product['id'] ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($product['name']) ?>
                                    — <?= number_format((float)$product['price'], 0, ',', ' ') ?> Ar
                                    <?php if ((int)$product['track_stock'] === 1): ?>
                                        (stock: <?= (int)$product['quantity'] ?>)
                                    <?php else: ?>
                                        (stock non suivi)
                                    <?php endif; ?>
                                    <?= (int)$product['active'] === 1 ? '' : ' (inactif)' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <div class="flex items-center gap-1">
                            <label class="sr-only" for="qty-<?= (int)$index ?>">Quantité</label>
                            <input
                                type="number"
                                id="qty-<?= (int)$index ?>"
                                name="items[<?= (int)$index ?>][quantity]"
                                min="1"
                                step="1"
                                value="<?= (int)($item['quantity'] ?? 1) ?>"
                                required
                                class="qty-input w-20 rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-center text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]"
                                placeholder="Qté"
                            >
                        </div>

                        <div class="flex items-center gap-1">
                            <label class="sr-only" for="price-<?= (int)$index ?>">Prix unitaire</label>
                            <input
                                type="number"
                                id="price-<?= (int)$index ?>"
                                name="items[<?= (int)$index ?>][unit_price]"
                                min="0"
                                step="0.01"
                                value="<?= htmlspecialchars((string)($item['unit_price'] ?? 0)) ?>"
                                required
                                class="price-input w-28 rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-right text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]"
                                placeholder="Prix"
                            >
                            <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Ar</span>
                        </div>

                        <span class="item-total w-28 text-right text-sm font-semibold text-[#8B4513]">0 Ar</span>

                        <div class="flex items-center gap-1">
                            <label class="sr-only" for="pay-<?= (int)$index ?>">Mode de paiement</label>
                            <?php
                            $itemPayMethod = $item['pay_method'] ?? 'cash';
                            if (!in_array($itemPayMethod, salePaymentMethods(), true)) {
                                $itemPayMethod = 'cash';
                            }
                            ?>
                            <select
                                id="pay-<?= (int)$index ?>"
                                name="items[<?= (int)$index ?>][pay_method]"
                                class="pay-method-select w-28 rounded-lg border border-[#e3e3e0] bg-white px-2 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]"
                            >
                                <?php foreach (salePaymentMethods() as $payMethodOption): ?>
                                    <option value="<?= $payMethodOption ?>" <?= $itemPayMethod === $payMethodOption ? 'selected' : '' ?>>
                                        <?= salePaymentMethodLabel($payMethodOption) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="button" onclick="removeItem(this)" class="rounded-lg p-1.5 text-[#706f6c] transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20" title="Retirer">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" onclick="addItem()" class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-[#8B4513] transition hover:text-[#A0522D]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Ajouter un article
            </button>
        </div>

        <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
            <div class="flex items-center justify-between">
                <span class="text-lg font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Total</span>
                <span id="grand-total" class="text-2xl font-bold text-[#8B4513]">0 Ar</span>
            </div>
            <p class="mt-2 text-xs text-[#706f6c] dark:text-[#A1A09A]">Le stock des produits suivis sera réajusté automatiquement. Le rapport journalier n'est pas recalculé par cette édition.</p>
            <div class="mt-5 flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-[#8B4513] px-5 py-2.5 text-sm font-medium text-[#FDFDFC] transition hover:bg-[#A0522D]">Enregistrer les modifications</button>
                <a href="<?= url('sales') ?>" class="text-sm text-[#706f6c] transition hover:text-[#8B4513] dark:text-[#A1A09A]">Annuler</a>
            </div>
        </div>
    </form>

    <select id="product-options-source" class="hidden">
        <?= $productOptions ?>
    </select>
</div>

<script>
let nextItemIndex = <?= (int)$nextItemIndex ?>;

function formatAmount(value) {
    return value.toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' Ar';
}

function updateTotals() {
    let grandTotal = 0;
    document.querySelectorAll('.sale-item').forEach(function(row) {
        const qty = parseInt(row.querySelector('.qty-input').value, 10) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const lineTotal = qty * price;
        grandTotal += lineTotal;
        row.querySelector('.item-total').textContent = formatAmount(lineTotal);
    });
    document.getElementById('grand-total').textContent = formatAmount(grandTotal);
}

function updatePaymentSummary() {
    const container = document.getElementById('payment-summary');
    if (!container) return;

    const labels = { cash: 'Cash', prepaye: 'Prépayé', trosa: 'Trosa', mobile: 'Mobile' };
    const classes = {
        cash: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        prepaye: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        trosa: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
        mobile: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        mixed: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
    };

    const methods = [];
    document.querySelectorAll('.pay-method-select').forEach(function(select) {
        if (select.value && !methods.includes(select.value)) {
            methods.push(select.value);
        }
    });

    if (methods.length === 0) {
        container.innerHTML = '<span class="text-sm text-[#A1A09A]">—</span>';
        return;
    }

    container.innerHTML = methods.map(function(method) {
        const label = labels[method] || method;
        const badgeClass = classes[method] || classes.cash;
        return '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium ' + badgeClass + '">' + label + '</span>';
    }).join('');

    if (methods.length > 1) {
        container.insertAdjacentHTML(
            'beforeend',
            '<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium ' + classes.mixed + '">Mixte</span>'
        );
    }
}

function onProductChange(select) {
    const option = select.options[select.selectedIndex];
    const price = parseFloat(option.dataset.price) || 0;
    const row = select.closest('.sale-item');
    row.querySelector('.price-input').value = price;
    updateTotals();
}

function addItem() {
    const container = document.getElementById('items-container');
    const options = document.getElementById('product-options-source').innerHTML;
    const index = nextItemIndex++;
    const div = document.createElement('div');
    div.className = 'sale-item flex flex-wrap items-center gap-3';
    div.innerHTML = `
        <select name="items[${index}][product_id]" class="product-select min-w-[220px] flex-1 rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
            <option value="">Choisir un produit</option>
            ${options}
        </select>
        <div class="flex items-center gap-1">
            <label class="sr-only" for="qty-${index}">Quantité</label>
            <input type="number" id="qty-${index}" name="items[${index}][quantity]" min="1" step="1" value="1" required class="qty-input w-20 rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-center text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]" placeholder="Qté">
        </div>
        <div class="flex items-center gap-1">
            <label class="sr-only" for="price-${index}">Prix unitaire</label>
            <input type="number" id="price-${index}" name="items[${index}][unit_price]" min="0" step="0.01" value="0" required class="price-input w-28 rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-right text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]" placeholder="Prix">
            <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Ar</span>
        </div>
        <span class="item-total w-28 text-right text-sm font-semibold text-[#8B4513]">0 Ar</span>
        <div class="flex items-center gap-1">
            <label class="sr-only" for="pay-${index}">Mode de paiement</label>
            <select id="pay-${index}" name="items[${index}][pay_method]" class="pay-method-select w-28 rounded-lg border border-[#e3e3e0] bg-white px-2 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
                <option value="cash">Cash</option>
                <option value="prepaye">Prépayé</option>
                <option value="trosa">Trosa</option>
                <option value="mobile">Mobile</option>
            </select>
        </div>
        <button type="button" onclick="removeItem(this)" class="rounded-lg p-1.5 text-[#706f6c] transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20" title="Retirer">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    `;
    container.appendChild(div);
    updateTotals();
    updatePaymentSummary();
}

function removeItem(button) {
    const rows = document.querySelectorAll('.sale-item');
    if (rows.length <= 1) return;
    button.closest('.sale-item').remove();
    updateTotals();
    updatePaymentSummary();
}

document.addEventListener('change', function(event) {
    if (event.target.matches('.product-select')) {
        onProductChange(event.target);
    }
    if (event.target.matches('.pay-method-select')) {
        updatePaymentSummary();
    }
});

document.addEventListener('input', function(event) {
    if (event.target.matches('.qty-input, .price-input')) {
        updateTotals();
    }
});

updateTotals();
updatePaymentSummary();
</script>

<?php require __DIR__ . '/../layout_footer.php'; ?>
