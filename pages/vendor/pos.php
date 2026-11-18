<?php
require_once __DIR__ . '/../../config/db.php';

$currentPage = 'pos';
$pageTitle = 'Caisse';

$todayDate = date('Y-m-d');
$selectedDate = normalizeDate($_GET['date'] ?? null, $todayDate);
// On ne peut pas enregistrer une vente dans le futur
if ($selectedDate > $todayDate) {
    $selectedDate = $todayDate;
}
$isToday = ($selectedDate === $todayDate);
$dateLabel = $isToday ? 'du jour' : 'du ' . date('d/m/Y', strtotime($selectedDate));

$search = trim($_GET['search'] ?? '');
$where = "WHERE p.active = 1";
$params = [];
if ($search !== '') {
    $where .= " AND p.name LIKE ?";
    $params[] = "%$search%";
}

$products = fetchAllAssoc("
    SELECT p.id, p.name, p.price, p.quantity, p.unit, p.image,
           COALESCE(c.track_stock, 0) AS track_stock,
           (c.track_stock = 1 AND p.quantity <= p.min_stock) AS low_stock
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    $where
    ORDER BY p.name
", $params);

$daySales = fetchAssoc("
    SELECT
        COALESCE(SUM(s.total), 0) AS revenue,
        COUNT(DISTINCT s.id) AS sale_count,
        COALESCE((SELECT SUM(si.quantity) FROM sale_items si WHERE si.sale_id IN (SELECT s2.id FROM sales s2 WHERE s2.user_id = ? AND DATE(s2.created_at) = ?)), 0) AS items_sold
    FROM sales s
    WHERE s.user_id = ? AND DATE(s.created_at) = ?
", [$_SESSION['user_id'], $selectedDate, $_SESSION['user_id'], $selectedDate]) ?? ['revenue' => 0, 'sale_count' => 0, 'items_sold' => 0];

$savedReport = fetchAssoc("SELECT prepaye, trosa, paiement, nofy FROM daily_reports WHERE user_id = ? AND report_date = ?", [$_SESSION['user_id'], $selectedDate]);
// Dernières valeurs du jour sélectionné pour garder les inputs pré-remplis après actualisation
$lastVersement = fetchAssoc("SELECT amount FROM daily_deposits WHERE user_id = ? AND deposit_date = ? ORDER BY id DESC LIMIT 1", [$_SESSION['user_id'], $selectedDate]);
try {
    $lastRelais = fetchAssoc("SELECT amount FROM daily_relais WHERE user_id = ? AND relais_date = ? ORDER BY id DESC LIMIT 1", [$_SESSION['user_id'], $selectedDate]);
} catch (Exception $e) { $lastRelais = []; }
try {
    $lastDepense = fetchAssoc("SELECT amount FROM daily_paiements WHERE user_id = ? AND paiement_date = ? ORDER BY id DESC LIMIT 1", [$_SESSION['user_id'], $selectedDate]);
} catch (Exception $e) { $lastDepense = []; }
// Ecart de caisse initial (miroir des inputs : dernières valeurs saisies) :
// versement - vente du jour + trosa + prepaye - paiement - relais + mobile money
$ecartInitial = (float)($lastVersement['amount'] ?? 0) - (float)($daySales['revenue'] ?? 0) + (float)($savedReport['trosa'] ?? 0) + (float)($savedReport['prepaye'] ?? 0) - (float)($lastDepense['amount'] ?? 0) - (float)($lastRelais['amount'] ?? 0) + (float)($savedReport['nofy'] ?? 0);

require __DIR__ . '/../layout.php';
?>

<div class="mb-6 rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <form method="GET" action="<?php echo url('pos'); ?>" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="pos-date" class="mb-1 block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Date d'enregistrement des ventes</label>
                <input
                    type="date"
                    id="pos-date"
                    name="date"
                    value="<?php echo htmlspecialchars($selectedDate); ?>"
                    min="1970-01-01"
                    max="<?php echo $todayDate; ?>"
                    onchange="this.form.submit()"
                    class="w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]"
                >
            </div>
            <button type="submit" class="rounded-lg border border-[#e3e3e0] px-4 py-2 text-sm font-medium text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#A1A09A]">Afficher</button>
            <?php if (!$isToday): ?>
                <a href="<?php echo url('pos'); ?>" class="rounded-lg bg-[#8B4513] px-4 py-2 text-sm font-medium text-[#FDFDFC] transition hover:bg-[#A0522D]">Aujourd'hui</a>
            <?php endif; ?>
        </form>
        <p class="text-xs font-medium <?php echo $isToday ? 'text-[#706f6c] dark:text-[#A1A09A]' : 'text-[#FF750F]'; ?>">
            <?php if ($isToday): ?>
                Les ventes seront enregistrées à la date du jour.
            <?php else: ?>
                Enregistrement pour le <?php echo date('d/m/Y', strtotime($selectedDate)); ?>.
            <?php endif; ?>
        </p>
    </div>
</div>

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <p class="text-2xl font-bold text-[#8B4513]"><?php echo number_format((float)$daySales['revenue'], 0, ',', ' '); ?> Ar</p>
        <p class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]">Ventes <?php echo $dateLabel; ?></p>
    </div>
    <div class="rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <p id="ecart-value" class="text-2xl font-bold text-[#8B4513]"><?php echo number_format($ecartInitial, 0, ',', ' '); ?> Ar</p>
        <p class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]">Ecart de caisse</p>
    </div>
    <div class="rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#FDFDFC]"><?php echo (int)$daySales['sale_count']; ?></p>
        <p class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]">Transactions <?php echo $dateLabel; ?></p>
    </div>
    <div class="rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <p class="text-2xl font-bold text-[#8B4513]"><?php echo (int)$daySales['items_sold']; ?></p>
        <p class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]">Articles vendus <?php echo $isToday ? "aujourd'hui" : 'le ' . date('d/m/Y', strtotime($selectedDate)); ?></p>
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

        <div class="mb-4 space-y-3">
            <label class="block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Rapport <?php echo $dateLabel; ?></label>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-[10px] font-medium text-[#706f6c] dark:text-[#A1A09A]">Cash</label>
                    <div class="flex items-center rounded-lg border border-[#e3e3e0] bg-[#fafaf9] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:border-[#3E3E3A] dark:bg-[#161615]">
                        <div id="paiement" data-value="<?= htmlspecialchars($savedReport['paiement'] ?? '0') ?>" class="w-full px-3 py-2 text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC]"><?= number_format((float)($savedReport['paiement'] ?? 0), 0, ',', ' ') ?></div>
                        <span class="shrink-0 border-l border-[#e3e3e0] px-2 py-2 text-[10px] font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]">Ar</span>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-medium text-[#706f6c] dark:text-[#A1A09A]">Prepayé</label>
                    <div class="flex items-center rounded-lg border border-[#e3e3e0] bg-[#fafaf9] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:border-[#3E3E3A] dark:bg-[#161615]">
                        <div id="prepaye" data-value="<?= htmlspecialchars($savedReport['prepaye'] ?? '0') ?>" class="w-full px-3 py-2 text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC]"><?= number_format((float)($savedReport['prepaye'] ?? 0), 0, ',', ' ') ?></div>
                        <span class="shrink-0 border-l border-[#e3e3e0] px-2 py-2 text-[10px] font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]">Ar</span>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-medium text-[#706f6c] dark:text-[#A1A09A]">Trosa</label>
                    <div class="flex items-center rounded-lg border border-[#e3e3e0] bg-[#fafaf9] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:border-[#3E3E3A] dark:bg-[#161615]">
                        <div id="trosa-input" data-value="<?= htmlspecialchars($savedReport['trosa'] ?? '0') ?>" class="w-full px-3 py-2 text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC]"><?= number_format((float)($savedReport['trosa'] ?? 0), 0, ',', ' ') ?></div>
                        <span class="shrink-0 border-l border-[#e3e3e0] px-2 py-2 text-[10px] font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]">Ar</span>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-medium text-[#706f6c] dark:text-[#A1A09A]">Mobile money</label>
                    <div class="flex items-center rounded-lg border border-[#e3e3e0] bg-[#fafaf9] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:border-[#3E3E3A] dark:bg-[#161615]">
                        <div id="nofy" data-value="<?= htmlspecialchars($savedReport['nofy'] ?? '0') ?>" class="w-full px-3 py-2 text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC]"><?= number_format((float)($savedReport['nofy'] ?? 0), 0, ',', ' ') ?></div>
                        <span class="shrink-0 border-l border-[#e3e3e0] px-2 py-2 text-[10px] font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]">Ar</span>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div>
                    <label class="mb-1 block text-[10px] font-medium text-[#706f6c] dark:text-[#A1A09A]">Versement du jour</label>
                    <div class="flex items-center rounded-lg border border-[#e3e3e0] bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus-within:border-[#8B4513] focus-within:ring-1 focus-within:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615]">
                        <input type="number" id="versement" name="versement" min="0" placeholder="0" value="<?= htmlspecialchars($lastVersement['amount'] ?? '') ?>" data-initial="<?= htmlspecialchars($lastVersement['amount'] ?? '') ?>" class="w-full bg-transparent px-3 py-2 text-sm text-[#1b1b18] placeholder:text-[#A1A09A] focus:outline-none dark:text-[#FDFDFC]">
                        <span class="shrink-0 border-l border-[#e3e3e0] px-2 py-2 text-[10px] font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]">Ar</span>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-medium text-[#706f6c] dark:text-[#A1A09A]">Paiement</label>
                    <div class="flex items-center rounded-lg border border-[#e3e3e0] bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus-within:border-[#8B4513] focus-within:ring-1 focus-within:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615]">
                        <input type="number" id="depense-paiement" name="depense_paiement" min="0" placeholder="0" value="<?= htmlspecialchars($lastDepense['amount'] ?? '') ?>" data-initial="<?= htmlspecialchars($lastDepense['amount'] ?? '') ?>" class="w-full bg-transparent px-3 py-2 text-sm text-[#1b1b18] placeholder:text-[#A1A09A] focus:outline-none dark:text-[#FDFDFC]">
                        <span class="shrink-0 border-l border-[#e3e3e0] px-2 py-2 text-[10px] font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]">Ar</span>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-medium text-[#706f6c] dark:text-[#A1A09A]">Relais</label>
                    <div class="flex items-center rounded-lg border border-[#e3e3e0] bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus-within:border-[#8B4513] focus-within:ring-1 focus-within:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615]">
                        <input type="number" id="relais" name="relais" min="0" placeholder="0" value="<?= htmlspecialchars($lastRelais['amount'] ?? '') ?>" data-initial="<?= htmlspecialchars($lastRelais['amount'] ?? '') ?>" class="w-full bg-transparent px-3 py-2 text-sm text-[#1b1b18] placeholder:text-[#A1A09A] focus:outline-none dark:text-[#FDFDFC]">
                        <span class="shrink-0 border-l border-[#e3e3e0] px-2 py-2 text-[10px] font-medium text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]">Ar</span>
                    </div>
                </div>
            </div>
            <button type="button" id="save-report" class="w-full rounded-lg bg-[#8B4513] px-3 py-2 text-xs font-semibold text-[#FDFDFC] transition hover:bg-[#A0522D]">Enregistrer</button>
            <p id="report-status" class="text-[10px]"></p>
        </div>
        

        <div id="product-grid" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-2">
            <?php foreach ($products as $product): ?>
            <?php
                $trackStock = (int)$product['track_stock'] === 1;
                $maxQty = $trackStock ? (int)$product['quantity'] : 999999999;
            ?>
            <div class="product-card group relative flex flex-wrap items-center gap-4 rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] transition hover:border-[#8B4513] disabled:cursor-not-allowed disabled:opacity-50 dark:bg-[#161615]"
                 data-product-id="<?php echo $product['id']; ?>"
                 data-name="<?php echo htmlspecialchars($product['name']); ?>"
                 data-price="<?php echo $product['price']; ?>"
                 data-max="<?php echo $maxQty; ?>"
                 data-track-stock="<?php echo $trackStock ? 1 : 0; ?>"
                 data-unit="<?php echo htmlspecialchars($product['unit']); ?>"
                 <?php echo ($trackStock && $product['quantity'] <= 0) ? 'data-disabled="1"' : ''; ?>>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-[#FDF3EA] dark:bg-[#0a0a0a]">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?php echo url('uploads/' . htmlspecialchars($product['image'])); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="h-full w-full object-cover" />
                    <?php else: ?>
                        <span class="text-lg font-bold text-[#8B4513]"><?php echo htmlspecialchars(mb_strtoupper(mb_substr($product['name'], 0, 1))); ?></span>
                    <?php endif; ?>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC]"><?php echo htmlspecialchars($product['name']); ?></p>
                    <p class="mt-0.5 text-sm font-medium text-[#8B4513]"><?php echo number_format((float)$product['price'], 0, ',', ' '); ?> Ar</p>
                    <p class="<?php echo ($trackStock && $product['low_stock']) ? 'mt-0.5 text-xs font-medium text-[#FF750F]' : 'mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]'; ?>">
                        <?php if ($trackStock): ?>
                            <?php echo (int)$product['quantity']; ?> <?php echo htmlspecialchars($product['unit']); ?> en stock
                        <?php else: ?>
                            Stock non suivi
                        <?php endif; ?>
                    </p>
                </div>
                <div class="qty-controls hidden w-[112px] shrink-0 flex-wrap items-center justify-center gap-1" style="width: 50%;">
                    <button type="button" onclick="gridUpdateQty(<?php echo $product['id']; ?>, -1)" class="flex h-7 w-7 items-center justify-center rounded-lg border border-[#e3e3e0] text-sm text-[#1b1b18] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#FDFDFC]">-</button>
                    <span class="grid-qty w-8 text-center text-sm font-bold text-[#8B4513]">0</span>
                    <button type="button" onclick="gridUpdateQty(<?php echo $product['id']; ?>, 1)" class="grid-plus flex h-7 w-7 items-center justify-center rounded-lg border border-[#e3e3e0] text-sm text-[#1b1b18] transition hover:border-[#8B4513] hover:text-[#8B4513] disabled:opacity-40 dark:border-[#3E3E3A] dark:text-[#FDFDFC]">+</button>
                    <span class="grid-total w-full text-center text-xs font-semibold text-[#8B4513]"></span>
                    <div class="pay-methods mt-1 grid w-full grid-cols-2 gap-1">
                        <button type="button" data-pay="cash" title="Cash" onclick="setPayMethod(<?php echo $product['id']; ?>, 'cash')" class="pay-btn rounded-md border border-[#8B4513] bg-[#8B4513] px-1 py-1 text-[10px] font-medium leading-tight text-[#FDFDFC] transition">Cash</button>
                        <button type="button" data-pay="prepaye" title="Prépayé" onclick="setPayMethod(<?php echo $product['id']; ?>, 'prepaye')" class="pay-btn rounded-md border border-[#e3e3e0] bg-white px-1 py-1 text-[10px] font-medium leading-tight text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:bg-transparent dark:text-[#A1A09A]">Prépayé</button>
                        <button type="button" data-pay="trosa" title="Trosa" onclick="setPayMethod(<?php echo $product['id']; ?>, 'trosa')" class="pay-btn rounded-md border border-[#e3e3e0] bg-white px-1 py-1 text-[10px] font-medium leading-tight text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:bg-transparent dark:text-[#A1A09A]">Trosa</button>
                        <button type="button" data-pay="mobile" title="Mobile Money" onclick="setPayMethod(<?php echo $product['id']; ?>, 'mobile')" class="pay-btn rounded-md border border-[#e3e3e0] bg-white px-1 py-1 text-[10px] font-medium leading-tight text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:bg-transparent dark:text-[#A1A09A]">Mobile</button>
                    </div>
                </div>
                <span class="grid-add-icon flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-[#8B4513] text-[#8B4513] transition group-hover:opacity-100 <?php echo ($trackStock && $product['quantity'] <= 0) ? '' : 'opacity-0'; ?>">+</span>
                <?php if ($trackStock && ($_SESSION['user_role'] ?? '') === 'administrateur'): ?>
                    <button type="button"
                            class="stock-add-btn flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#22c55e] text-white opacity-0 transition group-hover:opacity-100 hover:bg-[#16a34a] focus:opacity-100"
                            data-product-id="<?php echo (int)$product['id']; ?>"
                            data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                            data-initial-stock="<?php echo (int)$product['quantity']; ?>"
                            data-unit="<?php echo htmlspecialchars($product['unit']); ?>"
                            title="Ajouter du stock"
                            aria-label="Ajouter du stock pour <?php echo htmlspecialchars($product['name']); ?>">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <p id="no-products" class="mt-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]" style="display:none;">Aucun produit trouvé.</p>
    </div>

    <div class="order-first lg:order-last lg:sticky lg:top-24 lg:self-start">
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
                    <input type="hidden" name="sale_date" value="<?php echo htmlspecialchars($selectedDate); ?>">
                    <div id="cart-hidden-inputs"></div>
                    <input type="hidden" id="versement-hidden" name="versement" value="">
                    <button type="submit" id="checkout-btn" class="w-full rounded-lg bg-[#8B4513] py-3 px-4 text-sm font-semibold text-[#FDFDFC] hover:bg-[#A0522D] transition shadow-sm">
                        Enregistrer la vérification
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const allProducts = <?php echo json_encode($products); ?>;
let cart = {};
// Une ligne de panier = un couple (produit, moyen de paiement), ex. Café ×3 cash + ×2 prépayé.
// Méthode sélectionnée par carte (pour les ajouts / ajustements) : 'cash' par défaut.
let cardSel = {};

// Date d'activité sélectionnée (jour des ventes saisies)
const SALE_DATE = <?php echo json_encode($selectedDate); ?>;

// Vente du jour sélectionné (PHP, figée au chargement ; MAJ au rechargement après checkout)
const VENTE_DATE = <?php echo (float)($daySales['revenue'] ?? 0); ?>;

const DEFAULT_PAY = 'cash';
const PAY_LABELS = { cash: 'Cash', prepaye: 'Prépayé', trosa: 'Trosa', mobile: 'Mobile' };
const PAY_BTN_BASE = 'pay-btn rounded-md border px-1 py-1 text-[10px] font-medium leading-tight transition';
const PAY_BTN_ACTIVE = ' border-[#8B4513] bg-[#8B4513] text-[#FDFDFC]';
const PAY_BTN_INACTIVE = ' border-[#e3e3e0] bg-white text-[#706f6c] hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:bg-transparent dark:text-[#A1A09A]';

function lineKey(productId, method) {
    return productId + '_' + method;
}

function selectedMethod(productId) {
    const m = cardSel[productId];
    return PAY_LABELS[m] ? m : DEFAULT_PAY;
}

function productLines(productId) {
    return Object.values(cart).filter(item => item.id === productId);
}

function productTotalQty(productId) {
    return productLines(productId).reduce((sum, item) => sum + item.quantity, 0);
}

function setPayMethod(productId, method) {
    if (!PAY_LABELS[method]) return;
    cardSel[productId] = method;
    syncAll();
}

function syncPayButtons(card, productId) {
    const sel = selectedMethod(productId);
    const qtyByMethod = {};
    productLines(productId).forEach(item => { qtyByMethod[item.pay_method] = item.quantity; });
    card.querySelectorAll('.pay-methods button[data-pay]').forEach(btn => {
        const m = btn.dataset.pay;
        btn.className = PAY_BTN_BASE + (m === sel ? PAY_BTN_ACTIVE : PAY_BTN_INACTIVE);
        btn.textContent = PAY_LABELS[m] + (qtyByMethod[m] > 0 ? ' · ' + qtyByMethod[m] : '');
    });
}

function formatCurrency(value) {
    return value.toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' Ar';
}

function gridUpdateQty(productId, delta) {
    const card = document.querySelector(`.product-card[data-product-id="${productId}"]`);
    if (!card || card.dataset.disabled === '1') return;
    const max = parseInt(card.dataset.max);
    const method = selectedMethod(productId);
    const key = lineKey(productId, method);
    const current = cart[key] ? cart[key].quantity : 0;
    const newQty = current + delta;
    if (newQty < 0) return;
    if (productTotalQty(productId) - current + newQty > max) return;
    if (newQty === 0) {
        delete cart[key];
    } else {
        cart[key] = {
            key: key,
            id: productId,
            name: card.dataset.name,
            price: parseFloat(card.dataset.price),
            max_quantity: max,
            unit: card.dataset.unit,
            quantity: newQty,
            pay_method: method
        };
    }
    syncAll();
}

function addToCart(product) {
    if (product.quantity <= 0) return;
    const id = product.id;
    const max = parseInt(product.quantity);
    if (productTotalQty(id) >= max) return;
    const method = selectedMethod(id);
    const key = lineKey(id, method);
    if (!cart[key]) {
        cart[key] = {
            key: key,
            id: product.id,
            name: product.name,
            price: parseFloat(product.price),
            max_quantity: max,
            unit: product.unit,
            quantity: 1,
            pay_method: method
        };
    } else {
        if (productTotalQty(id) >= cart[key].max_quantity) return;
        cart[key].quantity++;
    }
    syncAll();
}

function removeFromCart(key) {
    delete cart[key];
    syncAll();
}

function updateQuantity(key, delta) {
    const item = cart[key];
    if (!item) return;
    const newQty = item.quantity + delta;
    if (newQty < 0) return;
    if (productTotalQty(item.id) - item.quantity + newQty > item.max_quantity) return;
    if (newQty === 0) {
        removeFromCart(key);
        return;
    }
    item.quantity = newQty;
    syncAll();
}

function syncAll() {
    syncGridCards();
    renderCart();
    updateEcart();
}

function syncGridCards() {
    document.querySelectorAll('.product-card').forEach(card => {
        const id = parseInt(card.dataset.productId);
        const qtyControls = card.querySelector('.qty-controls');
        const addIcon = card.querySelector('.grid-add-icon');
        const qtySpan = card.querySelector('.grid-qty');
        const plusBtn = card.querySelector('.grid-plus');
        const totalSpan = card.querySelector('.grid-total');
        const price = parseFloat(card.dataset.price);
        const max = parseInt(card.dataset.max);
        const totalQty = productTotalQty(id);
        const selKey = lineKey(id, selectedMethod(id));
        if (totalQty > 0) {
            qtyControls.classList.remove('hidden');
            qtyControls.classList.add('flex');
            addIcon.classList.add('hidden');
            qtySpan.textContent = cart[selKey] ? cart[selKey].quantity : 0;
            qtySpan.title = 'Total toutes méthodes : ' + totalQty;
            totalSpan.textContent = formatCurrency(price * totalQty);
            plusBtn.disabled = totalQty >= max;
            syncPayButtons(card, id);
        } else {
            qtyControls.classList.add('hidden');
            qtyControls.classList.remove('flex');
            addIcon.classList.remove('hidden');
            qtySpan.textContent = '0';
            qtySpan.title = '';
            totalSpan.textContent = '';
            plusBtn.disabled = false;
            syncPayButtons(card, id);
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
        const payMethod = PAY_LABELS[item.pay_method] ? item.pay_method : DEFAULT_PAY;
        const payLabel = PAY_LABELS[payMethod];
        const prodTot = productTotalQty(item.id);
        html += '<li class="flex items-center justify-between gap-3">' +
            '<div class="min-w-0">' +
                '<p class="truncate text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC]">' + item.name + '</p>' +
                '<p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">' + formatCurrency(item.price) + ' / ' + item.unit + ' = ' + formatCurrency(lineTotal) + '</p>' +
                '<span class="mt-1 inline-block rounded-md border border-[#8B4513] bg-[#FDF3EA] px-1.5 py-0.5 text-[10px] font-semibold text-[#8B4513] dark:bg-transparent">' + payLabel + '</span>' +
            '</div>' +
            '<div class="flex items-center gap-1">' +
                '<button type="button" onclick="updateQuantity(\'' + item.key + '\', -1)" class="flex h-7 w-7 items-center justify-center rounded-lg border border-[#e3e3e0] text-sm text-[#1b1b18] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#FDFDFC]">\u2212</button>' +
                '<span class="w-8 text-center text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">' + item.quantity + '</span>' +
                '<button type="button" onclick="updateQuantity(\'' + item.key + '\', 1)" ' + (prodTot >= item.max_quantity ? 'disabled' : '') + ' class="flex h-7 w-7 items-center justify-center rounded-lg border border-[#e3e3e0] text-sm text-[#1b1b18] transition hover:border-[#8B4513] hover:text-[#8B4513] disabled:opacity-40 dark:border-[#3E3E3A] dark:text-[#FDFDFC]">+</button>' +
            '</div>' +
            '<button type="button" onclick="removeFromCart(\'' + item.key + '\')" class="ml-2 text-sm text-red-500 hover:text-red-700 transition">\u2715</button>' +
        '</li>';
        inputs += '<input type="hidden" name="items[' + item.key + '][product_id]" value="' + item.id + '">' +
                   '<input type="hidden" name="items[' + item.key + '][quantity]" value="' + item.quantity + '">' +
                   '<input type="hidden" name="items[' + item.key + '][pay_method]" value="' + payMethod + '">';
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

syncAll();

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

function computePayTotals() {
    const totals = { cash: 0, prepaye: 0, trosa: 0, mobile: 0 };
    Object.values(cart).forEach(item => {
        const method = PAY_LABELS[item.pay_method] ? item.pay_method : DEFAULT_PAY;
        totals[method] += (parseFloat(item.price) || 0) * (parseInt(item.quantity) || 0);
    });
    return totals;
}

function getReportValue(id) {
    const el = document.getElementById(id);
    if (!el) return 0;
    let raw;
    if (el.tagName === 'INPUT' && 'value' in el) {
        raw = el.value;
    } else if (el.dataset && el.dataset.value !== undefined && el.dataset.value !== '') {
        raw = el.dataset.value;
    } else {
        raw = (el.textContent || '').replace(/[^0-9.,-]/g, '').replace(/\s/g, '').replace(',', '.');
    }
    const v = parseFloat(raw);
    return isNaN(v) ? 0 : Math.max(0, v);
}

function setReportValue(id, val) {
    const el = document.getElementById(id);
    if (!el) return;
    const clean = Math.max(0, parseFloat(val) || 0);
    if (el.tagName === 'INPUT' && 'value' in el) {
        el.value = clean;
    } else {
        el.dataset.value = clean;
        el.textContent = clean.toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }
}

function readAmount(id) {
    return getReportValue(id);
}

function buildReportBody() {
    return 'date=' + encodeURIComponent(SALE_DATE) +
        '&prepaye=' + encodeURIComponent(getReportValue('prepaye')) +
        '&trosa=' + encodeURIComponent(getReportValue('trosa-input')) +
        '&paiement=' + encodeURIComponent(getReportValue('paiement')) +
        '&nofy=' + encodeURIComponent(getReportValue('nofy'));
}

function readInputAmount(id) {
    const el = document.getElementById(id);
    if (!el) return 0;
    const v = parseFloat(el.value);
    return isNaN(v) ? 0 : Math.max(0, v);
}

// Vrai si l'utilisateur a modifié l'input depuis le chargement (protège des doublons :
// les valeurs pré-remplies depuis la DB ne doivent pas être ré-enregistrées telles quelles)
function inputChanged(id) {
    const el = document.getElementById(id);
    if (!el) return false;
    return (el.value || '') !== (el.dataset.initial || '');
}

function markInputsSaved(ids) {
    ids.forEach(function(id) {
        const el = document.getElementById(id);
        if (el) el.dataset.initial = el.value || '';
    });
}

// Ecart de caisse = versement - vente du jour + trosa + prépayé - paiement - relais + mobile money
// (miroir des inputs : dernières valeurs saisies, comme le KPI /sales)
function updateEcart() {
    const el = document.getElementById('ecart-value');
    if (!el) return;
    const ecart = readInputAmount('versement')
        - VENTE_DATE
        + getReportValue('trosa-input')
        + getReportValue('prepaye')
        - readInputAmount('depense-paiement')
        - readInputAmount('relais')
        + getReportValue('nofy');
    el.textContent = ecart.toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' Ar';
    el.classList.remove('text-[#8B4513]', 'text-[#16a34a]', 'text-[#dc2626]');
    el.classList.add(ecart > 0 ? 'text-[#16a34a]' : (ecart < 0 ? 'text-[#dc2626]' : 'text-[#8B4513]'));
}

['versement', 'depense-paiement', 'relais'].forEach(function(id) {
    const input = document.getElementById(id);
    if (input) input.addEventListener('input', updateEcart);
});

document.getElementById('checkout-form').addEventListener('submit', function() {
    // Versement pré-rempli non modifié => déjà enregistré, on ne le ré-enregistre pas
    document.getElementById('versement-hidden').value = inputChanged('versement') ? (document.getElementById('versement').value || '0') : '0';

    if (Object.keys(cart).length === 0) return;

    // Mise à jour optimiste de l'affichage (la persistance réelle est faite côté
    // serveur dans pos/checkout, en additif ; le rechargement affichera les valeurs DB).
    // Mapping : Cash -> Paiement, Mobile -> Nofy, Prépayé -> Prépayé, Trosa -> Trosa
    const totals = computePayTotals();
    setReportValue('prepaye', getReportValue('prepaye') + totals.prepaye);
    setReportValue('trosa-input', getReportValue('trosa-input') + totals.trosa);
    setReportValue('paiement', getReportValue('paiement') + totals.cash);
    setReportValue('nofy', getReportValue('nofy') + totals.mobile);
});

document.getElementById('save-report').addEventListener('click', function() {
    const btn = this;
    const status = document.getElementById('report-status');
    btn.disabled = true;
    btn.textContent = 'Enregistrement…';

    const reportData = buildReportBody();

    // Seules les valeurs modifiées depuis le chargement sont enregistrées (anti-doublon)
    const versementAmount = inputChanged('versement') ? readInputAmount('versement') : 0;
    const relaisAmount = inputChanged('relais') ? readInputAmount('relais') : 0;
    const depensePaiementAmount = inputChanged('depense-paiement') ? readInputAmount('depense-paiement') : 0;

    const requests = [
        fetch('<?php echo url('sales/save-report'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: reportData
        }).then(r => r.json())
    ];
    // On n'enregistre versement/relais/paiement que s'ils sont renseignés (montant > 0)
    requests.push(versementAmount > 0
        ? fetch('<?php echo url('pos/save-versement'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'amount=' + encodeURIComponent(versementAmount) + '&date=' + encodeURIComponent(SALE_DATE)
        }).then(r => r.json())
        : Promise.resolve({ success: true }));
    requests.push(relaisAmount > 0
        ? fetch('<?php echo url('pos/save-relais'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'amount=' + encodeURIComponent(relaisAmount) + '&date=' + encodeURIComponent(SALE_DATE)
        }).then(r => r.json())
        : Promise.resolve({ success: true }));
    requests.push(depensePaiementAmount > 0
        ? fetch('<?php echo url('pos/save-paiement'); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'amount=' + encodeURIComponent(depensePaiementAmount) + '&date=' + encodeURIComponent(SALE_DATE)
        }).then(r => r.json())
        : Promise.resolve({ success: true }));

    Promise.all(requests).then(([reportResult, versementResult, relaisResult, paiementResult]) => {
        btn.disabled = false;
        btn.textContent = 'Enregistrer';
        if (reportResult.success && versementResult.success && relaisResult.success && paiementResult.success) {
            markInputsSaved(['versement', 'relais', 'depense-paiement']);
            updateEcart();
            status.textContent = 'Tout enregistré.';
            status.className = 'text-[10px] text-[#16a34a]';
        } else {
            status.textContent = reportResult.message || versementResult.message || relaisResult.message || paiementResult.message || 'Erreur';
            status.className = 'text-[10px] text-[#dc2626]';
        }
    }).catch(() => {
        btn.disabled = false;
        btn.textContent = 'Enregistrer';
        status.textContent = 'Erreur de connexion';
        status.className = 'text-[10px] text-[#dc2626]';
    });
});
</script>

<div id="stock-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-2xl dark:bg-[#161615]">
        <h3 class="mb-4 text-lg font-bold text-[#1b1b18] dark:text-[#FDFDFC]">Ajouter du stock</h3>
        <p class="mb-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
            Produit : <span id="stock-modal-product" class="font-medium text-[#1b1b18] dark:text-[#FDFDFC]"></span>
        </p>
        <p class="mb-4 text-sm text-[#706f6c] dark:text-[#A1A09A]">
            Stock initial : <span id="stock-modal-initial" class="font-semibold text-[#1b1b18] dark:text-[#FDFDFC]"></span>
            <span id="stock-modal-unit"></span>
        </p>
        <form method="POST" action="<?php echo url('pos/add_stock'); ?>">
            <input type="hidden" name="product_id" id="stock-modal-product-id" value="">
            <label for="stock-modal-quantity" class="mb-1 block text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC]">Quantité à ajouter</label>
            <input type="number" id="stock-modal-quantity" name="quantity" min="1" step="1" value="1" required
                   class="w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-[#1b1b18] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#FDFDFC]">
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" id="stock-modal-cancel"
                        class="rounded-lg border border-[#e3e3e0] px-4 py-2 text-sm font-medium text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#A1A09A]">
                    Annuler
                </button>
                <button type="submit"
                        class="rounded-lg bg-[#22c55e] px-4 py-2 text-sm font-medium text-white transition hover:bg-[#16a34a]">
                    Ajouter
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    var modal = document.getElementById('stock-modal');
    if (!modal) return;

    var productEl = document.getElementById('stock-modal-product');
    var initialEl = document.getElementById('stock-modal-initial');
    var unitEl = document.getElementById('stock-modal-unit');
    var productIdInput = document.getElementById('stock-modal-product-id');
    var quantityInput = document.getElementById('stock-modal-quantity');

    function openModal(button) {
        productEl.textContent = button.dataset.productName || '';
        initialEl.textContent = button.dataset.initialStock || '0';
        unitEl.textContent = button.dataset.unit || '';
        productIdInput.value = button.dataset.productId || '';
        quantityInput.value = '1';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        quantityInput.focus();
        quantityInput.select();
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.querySelectorAll('.stock-add-btn').forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            openModal(button);
        });
    });

    document.getElementById('stock-modal-cancel').addEventListener('click', closeModal);
    modal.addEventListener('click', function(event) {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') closeModal();
    });
})();
</script>
<?php require __DIR__ . '/../layout_footer.php'; ?>
