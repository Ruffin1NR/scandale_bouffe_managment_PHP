<?php
$pageTitle = 'Continuité de stock - Scandale Bouffe';
$currentPage = 'inventory';
require __DIR__ . '/../../auth/check.php';
require __DIR__ . '/../layout.php';

// ------------------------------------------------------------------
// Filtres de date (comme dans /sales)
// ------------------------------------------------------------------
$dateFrom = trim($_GET['from'] ?? '');
$dateTo = trim($_GET['to'] ?? '');

$isValidDate = static function (string $date): bool {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }
    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
};

if ($dateFrom !== '' && !$isValidDate($dateFrom)) {
    $dateFrom = '';
}
if ($dateTo !== '' && !$isValidDate($dateTo)) {
    $dateTo = '';
}
if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
    [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
}

// ------------------------------------------------------------------
// Récupérer tous les produits actifs (toutes catégories confondues)
// ------------------------------------------------------------------
$products = fetchAllAssoc("
    SELECT p.id, p.name, c.name AS category_name, p.quantity, p.min_stock, p.unit,
           COALESCE(c.track_stock, 0) AS track_stock
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    WHERE p.active = 1
    ORDER BY p.name
");

// ------------------------------------------------------------------
// Construire le tableau des totaux grâce à la fonction helper
// ------------------------------------------------------------------
$rows = [];
foreach ($products as $product) {
    $totals = stockMovementTotals(
        (int)$product['id'],
        $dateFrom !== '' ? $dateFrom : null,
        $dateTo !== '' ? $dateTo : null
    );
    $rows[] = [
        'id'            => (int)$product['id'],
        'name'          => $product['name'],
        'category'      => $product['category_name'] ?? '—',
        'track_stock'   => (int)$product['track_stock'],
        'unit'          => $product['unit'] ?? 'pcs',
        'initial'       => $totals['initial'],
        'in'            => $totals['in'],
        'out'           => $totals['out'],
        'final'         => $totals['final'],
    ];
}

$showTrackedOnly = (int)($_GET['tracked'] ?? 0) === 1;
$displayedRows = $showTrackedOnly
    ? array_values(array_filter($rows, static fn(array $row): bool => $row['track_stock'] === 1))
    : $rows;

// ------------------------------------------------------------------
// Liens rapides en conservant les autres filtres
// ------------------------------------------------------------------
$filterParams = [];
if ($dateFrom !== '') {
    $filterParams['from'] = $dateFrom;
}
if ($dateTo !== '') {
    $filterParams['to'] = $dateTo;
}
if ($showTrackedOnly) {
    $filterParams['tracked'] = 1;
}

$quickUrl = static function (string $from, string $to) use ($showTrackedOnly): string {
    $params = [];
    if ($from !== '') {
        $params['from'] = $from;
    }
    if ($to !== '') {
        $params['to'] = $to;
    }
    if ($showTrackedOnly) {
        $params['tracked'] = 1;
    }
    return url('inventory/movements' . (!empty($params) ? '?' . http_build_query($params) : ''));
};

$trackedParams = $filterParams;
$trackedParams['tracked'] = 1;
$trackedUrl = url('inventory/movements' . (!empty($trackedParams) ? '?' . http_build_query($trackedParams) : ''));

$today = date('Y-m-d');
$weekStart = date('Y-m-d', strtotime('monday this week'));
$monthStart = date('Y-m-01');
$lastMonthStart = date('Y-m-d', strtotime('first day of last month'));
$lastMonthEnd = date('Y-m-d', strtotime('last day of last month'));
$isToday = ($dateFrom === $today && $dateTo === $today);
$isWeek = ($dateFrom === $weekStart && $dateTo === $today);
$isMonth = ($dateFrom === $monthStart && $dateTo === $today);
$isLastMonth = ($dateFrom === $lastMonthStart && $dateTo === $lastMonthEnd);
$isAll = ($dateFrom === '' && $dateTo === '');
$activeClass = 'border-[#8B4513] bg-[#8B4513]/10 text-[#8B4513] font-semibold';
$inactiveClass = 'border-[#e3e3e0] bg-white text-[#706f6c] hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#A1A09A]';

$periodLabel = 'Toutes les périodes';
if ($dateFrom !== '' && $dateTo !== '') {
    $periodLabel = 'Du ' . date('d/m/Y', strtotime($dateFrom)) . ' au ' . date('d/m/Y', strtotime($dateTo));
} elseif ($dateFrom !== '') {
    $periodLabel = 'Depuis le ' . date('d/m/Y', strtotime($dateFrom));
} elseif ($dateTo !== '') {
    $periodLabel = 'Jusqu\'au ' . date('d/m/Y', strtotime($dateTo));
}
?>
<div class="mx-auto max-w-7xl">
    <div class="mb-6 rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">
                    Continuité de stock
                </h2>
                <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    <?php echo htmlspecialchars($periodLabel); ?>
                    · <?php echo count($displayedRows); ?> produit(s)
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="<?php echo url('inventory'); ?>"
                   class="inline-flex items-center rounded-lg border border-[#e3e3e0] px-4 py-2 text-sm font-medium text-[#706f6c] hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:hover:bg-[#161615] dark:hover:text-[#A1A09A] transition">
                    ← Retour à l'inventaire
                </a>
                <?php if ($showTrackedOnly): ?>
                    <a href="<?php echo $quickUrl($dateFrom, $dateTo); ?>"
                       class="inline-flex items-center rounded-lg bg-[#8B4513] px-3 py-1.5 text-xs font-medium text-[#FDFDFC] hover:bg-[#A0522D] transition">
                        Montrer tous
                    </a>
                <?php else: ?>
                    <a href="<?php echo $trackedUrl; ?>"
                       class="inline-flex items-center rounded-lg bg-[#10b981] px-3 py-1.5 text-xs font-medium text-[#fff] hover:bg-[#059669] transition">
                        Montrer uniquement les produits suivis
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="mb-6 rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <form method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <?php if ($showTrackedOnly): ?>
                <input type="hidden" name="tracked" value="1">
            <?php endif; ?>
            <div>
                <label for="movements-from" class="mb-1 block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Du</label>
                <input type="date" id="movements-from" name="from" value="<?php echo htmlspecialchars($dateFrom); ?>"
                       class="w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] placeholder:text-[#A1A09A] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
            </div>
            <div>
                <label for="movements-to" class="mb-1 block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Au</label>
                <input type="date" id="movements-to" name="to" value="<?php echo htmlspecialchars($dateTo); ?>"
                       class="w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] placeholder:text-[#A1A09A] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
            </div>
            <div class="flex items-end gap-2 lg:col-span-2">
                <button type="submit"
                        class="rounded-lg bg-[#8B4513] px-4 py-2 text-sm font-medium text-[#FDFDFC] transition hover:bg-[#A0522D]">Filtrer</button>
                <?php if ($dateFrom !== '' || $dateTo !== ''): ?>
                    <a href="<?php echo url('inventory/movements' . ($showTrackedOnly ? '?tracked=1' : '')); ?>"
                       class="rounded-lg border border-[#e3e3e0] px-4 py-2 text-sm font-medium text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#A1A09A]">Réinitialiser</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="mt-4 flex flex-wrap gap-2">
            <a href="<?php echo $quickUrl($today, $today); ?>"
               class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition <?= $isToday ? $activeClass : $inactiveClass ?>">Aujourd'hui</a>
            <a href="<?php echo $quickUrl($weekStart, $today); ?>"
               class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition <?= $isWeek ? $activeClass : $inactiveClass ?>">Cette semaine</a>
            <a href="<?php echo $quickUrl($monthStart, $today); ?>"
               class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition <?= $isMonth ? $activeClass : $inactiveClass ?>">Ce mois</a>
            <a href="<?php echo $quickUrl($lastMonthStart, $lastMonthEnd); ?>"
               class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition <?= $isLastMonth ? $activeClass : $inactiveClass ?>">Mois dernier</a>
            <a href="<?php echo $quickUrl('', ''); ?>"
               class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition <?= $isAll ? $activeClass : $inactiveClass ?>">Tout afficher</a>
        </div>

        <p class="mt-3 text-xs text-[#706f6c] dark:text-[#A1A09A]">
            Les entrées et les sorties sont filtrées sur la période. Le stock initial inclut les mouvements antérieurs à la période ; le stock final en découle.
        </p>
    </div>

    <?php if (empty($displayedRows)): ?>
        <div class="rounded-lg bg-white p-12 text-center shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
            <p class="text-lg font-medium text-[#1b1b18] dark:text-[#FDFDFC]">Aucun produit trouvé.</p>
            <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Modifiez la période ou vérifiez que des produits sont actifs.
            </p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto rounded-lg bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
            <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                <thead class="bg-[#FDFDFC] dark:bg-[#1a1a1a]">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Produit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Catégorie</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Stock initial</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Entrées</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Sorties</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Stock final</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                    <?php foreach ($displayedRows as $row): ?>
                        <tr class="hover:bg-[#FDFDFC] dark:hover:bg-[#1a1a1a]">
                            <td class="px-6 py-4 font-medium text-[#1b1b18] dark:text-[#FDFDFC]">
                                <a href="<?php echo url('inventory/show?id=' . $row['id']); ?>"
                                   class="text-[#8B4513] hover:underline">
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?php echo htmlspecialchars($row['category']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">
                                <?php echo (int)$row['initial']; ?> <?php echo htmlspecialchars($row['unit']); ?>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-green-600 dark:text-green-400">
                                <?php echo (int)$row['in']; ?>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-red-600 dark:text-red-400">
                                <?php echo (int)$row['out']; ?>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">
                                <?php echo (int)$row['final']; ?> <?php echo htmlspecialchars($row['unit']); ?>
                            </td>
                            <td class="px-6 py-4 text-right text-sm">
                                <a href="<?php echo url('inventory/show?id=' . $row['id']); ?>"
                                   class="text-[#8B4513] hover:underline text-xs">Détail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="mt-6 px-6 pb-6 text-right">
                <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Total affiché : <?php echo count($displayedRows); ?> produit(s)
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>
