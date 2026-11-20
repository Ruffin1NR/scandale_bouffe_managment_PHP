<?php
$currentPage = 'sales';
$pageTitle = 'Suivi des ventes';
$withConfirmModal = true;
?>
<?php require __DIR__ . '/../layout.php'; ?>
<?php
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;
$dateFrom = $_GET['from'] ?? '';
$dateTo = $_GET['to'] ?? '';
$categoryFilter = (int) ($_GET['category'] ?? 0);
$productFilter = (int) ($_GET['product'] ?? 0);
$paymentFilter = (string) ($_GET['payment'] ?? '');
$validPaymentFilters = array_merge(salePaymentMethods(), ['mixed']);
if ($paymentFilter !== '' && !in_array($paymentFilter, $validPaymentFilters, true)) {
    $paymentFilter = '';
}
$where = "WHERE 1=1";
$params = [];
if ($dateFrom !== '') {
    $where .= " AND s.created_at >= ?";
    $params[] = $dateFrom . ' 00:00:00';
}
if ($dateTo !== '') {
    $where .= " AND s.created_at <= ?";
    $params[] = $dateTo . ' 23:59:59';
}
if ($productFilter > 0) {
    $where .= " AND si.product_id = ?";
    $params[] = $productFilter;
}
if ($categoryFilter > 0) {
    $where .= " AND p.category_id = ?";
    $params[] = $categoryFilter;
}
if ($paymentFilter === 'mixed') {
    $where .= " AND (SELECT COUNT(DISTINCT si_pay.pay_method) FROM sale_items si_pay WHERE si_pay.sale_id = s.id) > 1";
} elseif ($paymentFilter !== '') {
    $where .= " AND EXISTS (SELECT 1 FROM sale_items si_pay WHERE si_pay.sale_id = s.id AND si_pay.pay_method = ?)";
    $params[] = $paymentFilter;
}

if ($dateFrom !== '' || $dateTo !== '') {
    $kpiWhere = "WHERE 1=1";
    $kpiParams = [];
    if ($dateFrom !== '') { $kpiWhere .= " AND s.created_at >= ?"; $kpiParams[] = $dateFrom . ' 00:00:00'; }
    if ($dateTo !== '') { $kpiWhere .= " AND s.created_at <= ?"; $kpiParams[] = $dateTo . ' 23:59:59'; }
    // Chaque table a sa colonne jour d'activité (date PHP à la saisie)
    $depositWhere = "WHERE user_id = ?";
    $depositParams = [$_SESSION['user_id']];
    $relaisWhere = "WHERE user_id = ?";
    $relaisParams = [$_SESSION['user_id']];
    $paiementWhere = "WHERE user_id = ?";
    $paiementParams = [$_SESSION['user_id']];
    if ($dateFrom !== '') { $depositWhere .= " AND deposit_date >= ?"; $depositParams[] = $dateFrom; }
    if ($dateTo !== '') { $depositWhere .= " AND deposit_date <= ?"; $depositParams[] = $dateTo; }
    if ($dateFrom !== '') { $relaisWhere .= " AND relais_date >= ?"; $relaisParams[] = $dateFrom; }
    if ($dateTo !== '') { $relaisWhere .= " AND relais_date <= ?"; $relaisParams[] = $dateTo; }
    if ($dateFrom !== '') { $paiementWhere .= " AND paiement_date >= ?"; $paiementParams[] = $dateFrom; }
    if ($dateTo !== '') { $paiementWhere .= " AND paiement_date <= ?"; $paiementParams[] = $dateTo; }
    // Les rapports sont rattachés au jour d'activité (report_date), pas à l'heure de saisie (created_at)
    $reportWhere = "WHERE user_id = ?";
    $reportParams = [$_SESSION['user_id']];
    if ($dateFrom !== '') { $reportWhere .= " AND report_date >= ?"; $reportParams[] = $dateFrom; }
    if ($dateTo !== '') { $reportWhere .= " AND report_date <= ?"; $reportParams[] = $dateTo; }
} else {
    $kpiWhere = "WHERE 1=1";
    $kpiParams = [];
    $depositWhere = "WHERE user_id = ?";
    $depositParams = [$_SESSION['user_id']];
    $relaisWhere = "WHERE user_id = ?";
    $relaisParams = [$_SESSION['user_id']];
    $paiementWhere = "WHERE user_id = ?";
    $paiementParams = [$_SESSION['user_id']];
    $reportWhere = "WHERE user_id = ?";
    $reportParams = [$_SESSION['user_id']];
}
$venteJour = fetchAssoc("SELECT COALESCE(SUM(total), 0) as total FROM sales s $kpiWhere", $kpiParams);
// KPI Versement = dernière saisie de la période (miroir de l'input /pos), pas la somme
$versement = fetchAssoc("SELECT COALESCE((SELECT amount FROM daily_deposits $depositWhere ORDER BY deposit_date DESC, id DESC LIMIT 1), 0) as total", $depositParams);
$trosa = fetchAssoc("SELECT COALESCE(SUM(trosa), 0) as total FROM daily_reports $reportWhere", $reportParams);
// Ventilation du rapport (prépayé + mobile money) sur le même périmètre (jour d'activité)
$reportVent = fetchAssoc("SELECT COALESCE(SUM(prepaye), 0) as prepaye, COALESCE(SUM(nofy), 0) as nofy FROM daily_reports $reportWhere", $reportParams);
// Dépenses "Paiement" et "Relais" : dernières saisies de la période (cohérence avec l'Écart)
$relaisKpi = fetchAssoc("SELECT COALESCE((SELECT amount FROM daily_relais $relaisWhere ORDER BY relais_date DESC, id DESC LIMIT 1), 0) as total", $relaisParams);
$depensePaiement = fetchAssoc("SELECT COALESCE((SELECT amount FROM daily_paiements $paiementWhere ORDER BY paiement_date DESC, id DESC LIMIT 1), 0) as total", $paiementParams);
// Seul un filtre d'un jour unique affiche la dernière saisie (miroir de l'input /pos).
// Dans tous les autres cas (intervalle multi-jours, période ouverte, sans filtre) :
// cumul honnête (somme), pour que l'Écart de la période égale la somme des écarts journaliers.
$isSingleDay = ($dateFrom !== '' && $dateTo !== '' && $dateFrom === $dateTo);
if (!$isSingleDay) {
    $versement = fetchAssoc("SELECT COALESCE(SUM(amount), 0) as total FROM daily_deposits $depositWhere", $depositParams);
    $relaisKpi = fetchAssoc("SELECT COALESCE(SUM(amount), 0) as total FROM daily_relais $relaisWhere", $relaisParams);
    $depensePaiement = fetchAssoc("SELECT COALESCE(SUM(amount), 0) as total FROM daily_paiements $paiementWhere", $paiementParams);
}
// Écart de caisse (formule POS) : versement - vente + trosa + prépayé - paiement - relais + mobile money
// NB : "paiement" ici = dépenses daily_paiements, pas la colonne daily_reports.paiement (Cash)
$ecartValeur = (float) $versement['total'] - (float) $venteJour['total'] + (float) $trosa['total'] + (float) $reportVent['prepaye'] - (float) $depensePaiement['total'] - (float) $relaisKpi['total'] + (float) $reportVent['nofy'];
$totalResult = fetchAssoc("SELECT COUNT(DISTINCT s.id) as count FROM sales s JOIN sale_items si ON si.sale_id = s.id LEFT JOIN products p ON si.product_id = p.id $where", $params);
$total = (int) ($totalResult['count'] ?? 0);
$totalPages = max(1, (int) ceil($total / $perPage));
$sales = fetchAllAssoc("SELECT DISTINCT s.id, s.total, s.payment_method, s.created_at, s.user_id, u.name as vendor_name FROM sales s LEFT JOIN users u ON s.user_id = u.id LEFT JOIN sale_items si ON si.sale_id = s.id LEFT JOIN products p ON si.product_id = p.id $where ORDER BY s.created_at DESC LIMIT $perPage OFFSET $offset", $params);
$saleIds = array_column($sales, 'id');
$saleItems = [];
if (!empty($saleIds)) {
    $placeholders = implode(',', array_fill(0, count($saleIds), '?'));
    $items = fetchAllAssoc("SELECT si.*, p.name AS product_name, c.name as category_name FROM sale_items si LEFT JOIN products p ON si.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id WHERE si.sale_id IN ($placeholders)", $saleIds);
    foreach ($items as $item) {
        $saleItems[$item['sale_id']][] = $item;
    }
}
$byProduct = fetchAllAssoc("SELECT p.name AS product_name, COALESCE(c.name, 'Sans catégorie') as category_name, SUM(si.quantity) as qty, SUM(si.quantity * si.unit_price) as revenue FROM sale_items si JOIN sales s ON si.sale_id = s.id LEFT JOIN products p ON si.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id $where GROUP BY p.name, c.name ORDER BY revenue DESC", $params);
$byCategory = fetchAllAssoc("SELECT COALESCE(c.name, 'Sans catégorie') as name, SUM(si.quantity) as quantity, SUM(si.quantity * si.unit_price) as revenue FROM sale_items si JOIN sales s ON si.sale_id = s.id LEFT JOIN products p ON si.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id $where GROUP BY c.name ORDER BY revenue DESC", $params);
$byDay = fetchAllAssoc("SELECT DATE(s.created_at) as name, COUNT(DISTINCT s.id) as quantity, SUM(si.quantity * si.unit_price) as revenue FROM sale_items si JOIN sales s ON si.sale_id = s.id LEFT JOIN products p ON si.product_id = p.id $where GROUP BY DATE(s.created_at) ORDER BY name DESC LIMIT 7", $params);
$byWeek = fetchAllAssoc("SELECT CONCAT(YEARWEEK(s.created_at, 1), '') as name, COUNT(DISTINCT s.id) as quantity, SUM(si.quantity * si.unit_price) as revenue FROM sale_items si JOIN sales s ON si.sale_id = s.id LEFT JOIN products p ON si.product_id = p.id $where GROUP BY YEARWEEK(s.created_at, 1) ORDER BY name DESC LIMIT 8", $params);
$byMonth = fetchAllAssoc("SELECT DATE_FORMAT(s.created_at, '%Y-%m') as name, COUNT(DISTINCT s.id) as quantity, SUM(si.quantity * si.unit_price) as revenue FROM sale_items si JOIN sales s ON si.sale_id = s.id LEFT JOIN products p ON si.product_id = p.id $where GROUP BY DATE_FORMAT(s.created_at, '%Y-%m') ORDER BY name DESC LIMIT 6", $params);
// Ventes de produits par semaine / par mois (mêmes filtres que le reste de la page)
$byProductWeekRows = fetchAllAssoc("SELECT CONCAT(YEARWEEK(s.created_at, 1), '') as period, p.name AS product_name, COALESCE(c.name, 'Sans catégorie') as category_name, SUM(si.quantity) as qty, SUM(si.quantity * si.unit_price) as revenue FROM sale_items si JOIN sales s ON si.sale_id = s.id LEFT JOIN products p ON si.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id $where GROUP BY YEARWEEK(s.created_at, 1), p.name, c.name ORDER BY period DESC, revenue DESC", $params);
$byProductMonthRows = fetchAllAssoc("SELECT DATE_FORMAT(s.created_at, '%Y-%m') as period, p.name AS product_name, COALESCE(c.name, 'Sans catégorie') as category_name, SUM(si.quantity) as qty, SUM(si.quantity * si.unit_price) as revenue FROM sale_items si JOIN sales s ON si.sale_id = s.id LEFT JOIN products p ON si.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id $where GROUP BY DATE_FORMAT(s.created_at, '%Y-%m'), p.name, c.name ORDER BY period DESC, revenue DESC", $params);
$byProductWeek = [];
foreach ($byProductWeekRows as $row) {
    $byProductWeek[$row['period']][] = $row;
}
$byProductWeek = array_slice($byProductWeek, 0, 8, true);
$byProductMonth = [];
foreach ($byProductMonthRows as $row) {
    $byProductMonth[$row['period']][] = $row;
}
$byProductMonth = array_slice($byProductMonth, 0, 6, true);
$allProducts = fetchAllAssoc("SELECT id, name FROM products WHERE active = 1 ORDER BY name");
$allCategories = fetchAllAssoc("SELECT id, name FROM categories ORDER BY name");
$maxCatRev = !empty($byCategory) ? max(array_column($byCategory, 'revenue')) : 1;
$maxDayRev = !empty($byDay) ? max(array_column($byDay, 'revenue')) : 1;
$qs = '';
if ($dateFrom)
    $qs .= '&from=' . $dateFrom;
if ($dateTo)
    $qs .= '&to=' . $dateTo;
if ($categoryFilter)
    $qs .= '&category=' . $categoryFilter;
if ($productFilter)
    $qs .= '&product=' . $productFilter;
if ($paymentFilter)
    $qs .= '&payment=' . $paymentFilter;

$reportDate = $dateFrom ?: date('Y-m-d');
$savedReport = fetchAssoc("SELECT prepaye, trosa, paiement, nofy FROM daily_reports WHERE user_id = ? AND report_date = ?", [$_SESSION['user_id'], $reportDate]);

// Historique des mouvements de caisse (mêmes filtres de date et même utilisateur que les KPI)
$cashEntrySources = [
    'versement' => ['where' => $depositWhere, 'params' => $depositParams],
    'relais' => ['where' => $relaisWhere, 'params' => $relaisParams],
    'depense' => ['where' => $paiementWhere, 'params' => $paiementParams],
];
$cashEntries = [];
foreach (cashEntryTypes() as $cashEntryType => $cashEntryConfig) {
    $cashEntryWhere = $cashEntrySources[$cashEntryType]['where'];
    $cashEntryParams = $cashEntrySources[$cashEntryType]['params'];
    $cashEntryRows = fetchAllAssoc(
        "SELECT id, amount, {$cashEntryConfig['date_column']} AS entry_date, created_at
         FROM {$cashEntryConfig['table']}
         {$cashEntryWhere}
         ORDER BY {$cashEntryConfig['date_column']} DESC, id DESC",
        $cashEntryParams
    );
    foreach ($cashEntryRows as $cashEntryRow) {
        $cashEntryRow['type'] = $cashEntryType;
        $cashEntries[] = $cashEntryRow;
    }
}
usort($cashEntries, function ($a, $b) {
    $dateCompare = strcmp((string)$b['entry_date'], (string)$a['entry_date']);
    if ($dateCompare !== 0) {
        return $dateCompare;
    }
    return strcmp((string)$b['created_at'], (string)$a['created_at']);
});
$cashEntryTotal = count($cashEntries);
$cashEntries = array_slice($cashEntries, 0, 50);
$currentUrl = 'sales' . ($qs !== '' ? '?' . ltrim($qs, '&') : '');
?>

<div class="py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-[#1b1b18] dark:text-[#FDFDFC]">Suivi des ventes</h2>
            <!-- <a href="<?php echo url('sales/create'); ?>" class="inline-flex items-center gap-1.5 rounded-lg bg-[#8B4513] px-3 py-1.5 text-sm font-medium text-[#FDFDFC] hover:bg-[#A0522D] transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouvelle vente
            </a> -->
        </div>

        <div class="mb-8 rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
            <form method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                <div>
                    <label class="mb-1 block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Du</label>
                    <input type="date" name="from" value="<?= htmlspecialchars($dateFrom) ?>"
                        class="w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] placeholder:text-[#A1A09A] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Au</label>
                    <input type="date" name="to" value="<?= htmlspecialchars($dateTo) ?>"
                        class="w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] placeholder:text-[#A1A09A] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Catégorie</label>
                    <select name="category"
                        class="w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] placeholder:text-[#A1A09A] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
                        <option value="">Toutes</option>
                        <?php foreach ($allCategories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryFilter == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Produit</label>
                    <select name="product"
                        class="w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] placeholder:text-[#A1A09A] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
                        <option value="">Tous</option>
                        <?php foreach ($allProducts as $prod): ?>
                            <option value="<?= $prod['id'] ?>" <?= $productFilter == $prod['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($prod['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="filter-payment" class="mb-1 block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Mode de paiement</label>
                    <select id="filter-payment" name="payment"
                        class="w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] placeholder:text-[#A1A09A] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
                        <option value="">Tous</option>
                        <option value="cash" <?= $paymentFilter === 'cash' ? 'selected' : '' ?>>Cash</option>
                        <option value="prepaye" <?= $paymentFilter === 'prepaye' ? 'selected' : '' ?>>Prépayé</option>
                        <option value="trosa" <?= $paymentFilter === 'trosa' ? 'selected' : '' ?>>Trosa</option>
                        <option value="mobile" <?= $paymentFilter === 'mobile' ? 'selected' : '' ?>>Mobile</option>
                        <option value="mixed" <?= $paymentFilter === 'mixed' ? 'selected' : '' ?>>Mixte</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="flex-1 rounded-lg bg-[#8B4513] px-4 py-2 text-sm font-medium text-[#FDFDFC] transition hover:bg-[#A0522D]">Filtrer</button>
                    <?php if ($dateFrom || $dateTo || $categoryFilter || $productFilter || $paymentFilter): ?>
                        <a href="<?= url('sales') ?>"
                            class="rounded-lg border border-[#e3e3e0] px-4 py-2 text-sm font-medium text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#A1A09A]">Réinitialiser</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="mb-6 flex flex-wrap gap-2">
            <?php
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
            ?>
            <a href="<?php echo url('sales?from=' . $today . '&to=' . $today); ?>"
                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition <?= $isToday ? $activeClass : $inactiveClass ?>">Aujourd'hui</a>
            <a href="<?php echo url('sales?from=' . $weekStart . '&to=' . $today); ?>"
                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition <?= $isWeek ? $activeClass : $inactiveClass ?>">Cette semaine</a>
            <a href="<?php echo url('sales?from=' . $monthStart . '&to=' . $today); ?>"
                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition <?= $isMonth ? $activeClass : $inactiveClass ?>">Ce mois</a>
            <a href="<?php echo url('sales?from=' . $lastMonthStart . '&to=' . $lastMonthEnd); ?>"
                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition <?= $isLastMonth ? $activeClass : $inactiveClass ?>">Mois dernier</a>
            <a href="<?php echo url('sales'); ?>"
                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition <?= $isAll ? $activeClass : $inactiveClass ?>">Tout afficher</a>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
                <p class="text-3xl font-bold text-[#8B4513]">
                    <?= number_format((float) $venteJour['total'], 0, ',', ' ') ?> Ar</p>
                <p class="mt-1 text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Vente du jour</p>
            </div>
            <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
                <p class="text-3xl font-bold text-[#16a34a]">
                    <?= number_format((float) $versement['total'], 0, ',', ' ') ?> Ar</p>
                <p class="mt-1 text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Versement</p>
            </div>
            <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
                <p class="text-3xl font-bold text-[#FF750F]"><?= number_format((float) $trosa['total'], 0, ',', ' ') ?>
                    Ar</p>
                <p class="mt-1 text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Trosa (Crédit)</p>
            </div>
            <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
                <?php
                $ecart = $ecartValeur;
                $ecartColor = $ecart >= 0 ? 'text-[#16a34a]' : 'text-[#dc2626]';
                ?>
                <p class="text-3xl font-bold <?= $ecartColor ?>"><?= number_format($ecart, 0, ',', ' ') ?> Ar</p>
                <p class="mt-1 text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Écart de caisse</p>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
                <h3 class="mb-4 text-lg font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Par catégorie</h3>
                <?php if (empty($byCategory)): ?>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune donnée sur la période.</p>
                <?php else: ?>
                    <ul class="space-y-3">
                        <?php foreach ($byCategory as $row): ?>
                            <?php $pct = $maxCatRev > 0 ? round(($row['revenue'] / $maxCatRev) * 100) : 0; ?>
                            <li>
                                <div class="flex items-center justify-between text-sm">
                                    <span
                                        class="truncate pr-3 font-medium text-[#1b1b18] dark:text-[#FDFDFC]"><?= htmlspecialchars($row['name']) ?></span>
                                    <span
                                        class="shrink-0 text-[#8B4513]"><?= number_format((float) $row['revenue'], 0, ',', ' ') ?>
                                        Ar</span>
                                </div>
                                <div
                                    class="mt-1.5 flex items-center justify-between text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                    <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-[#F3EFE9] dark:bg-[#0a0a0a]">
                                        <div class="h-full rounded-full bg-[#8B4513]" style="width: <?= $pct ?>%"></div>
                                    </div>
                                    <span class="ms-3 shrink-0"><?= (int) $row['quantity'] ?> vendus</span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
                <h3 class="mb-4 text-lg font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Par jour</h3>
                <?php if (empty($byDay)): ?>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune donnée sur la période.</p>
                <?php else: ?>
                    <ul class="space-y-3">
                        <?php foreach ($byDay as $row): ?>
                            <?php $pct = $maxDayRev > 0 ? round(($row['revenue'] / $maxDayRev) * 100) : 0; ?>
                            <li>
                                <div class="flex items-center justify-between text-sm">
                                    <span
                                        class="truncate pr-3 font-medium text-[#1b1b18] dark:text-[#FDFDFC]"><?= htmlspecialchars($row['name']) ?></span>
                                    <span
                                        class="shrink-0 text-[#8B4513]"><?= number_format((float) $row['revenue'], 0, ',', ' ') ?>
                                        Ar</span>
                                </div>
                                <div
                                    class="mt-1.5 flex items-center justify-between text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                    <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-[#F3EFE9] dark:bg-[#0a0a0a]">
                                        <div class="h-full rounded-full bg-[#8B4513]" style="width: <?= $pct ?>%"></div>
                                    </div>
                                    <span class="ms-3 shrink-0"><?= (int) $row['quantity'] ?> ventes</span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <?php
        $maxWeekRev = !empty($byWeek) ? max(array_column($byWeek, 'revenue')) : 1;
        $maxMonthRev = !empty($byMonth) ? max(array_column($byMonth, 'revenue')) : 1;
        function formatWeekLabel($ymw)
        {
            $y = substr($ymw, 0, 4);
            $w = (int) substr($ymw, 4);
            return "S$w $y";
        }
        ?>
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
                <h3 class="mb-4 text-lg font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Par semaine</h3>
                <?php if (empty($byWeek)): ?>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune donnée.</p>
                <?php else: ?>
                    <ul class="space-y-3">
                        <?php foreach ($byWeek as $row): ?>
                            <?php $pct = $maxWeekRev > 0 ? round(($row['revenue'] / $maxWeekRev) * 100) : 0; ?>
                            <li>
                                <div class="flex items-center justify-between text-sm">
                                    <span
                                        class="truncate pr-3 font-medium text-[#1b1b18] dark:text-[#FDFDFC]"><?= htmlspecialchars(formatWeekLabel($row['name'])) ?></span>
                                    <span
                                        class="shrink-0 text-[#8B4513]"><?= number_format((float) $row['revenue'], 0, ',', ' ') ?>
                                        Ar</span>
                                </div>
                                <div
                                    class="mt-1.5 flex items-center justify-between text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                    <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-[#F3EFE9] dark:bg-[#0a0a0a]">
                                        <div class="h-full rounded-full bg-[#FF750F]" style="width: <?= $pct ?>%"></div>
                                    </div>
                                    <span class="ms-3 shrink-0"><?= (int) $row['quantity'] ?> ventes</span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
                <h3 class="mb-4 text-lg font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Par mois</h3>
                <?php if (empty($byMonth)): ?>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune donnée.</p>
                <?php else: ?>
                    <ul class="space-y-3">
                        <?php foreach ($byMonth as $row): ?>
                            <?php $pct = $maxMonthRev > 0 ? round(($row['revenue'] / $maxMonthRev) * 100) : 0; ?>
                            <?php $parts = explode('-', $row['name']); ?>
                            <li>
                                <div class="flex items-center justify-between text-sm">
                                    <span
                                        class="truncate pr-3 font-medium text-[#1b1b18] dark:text-[#FDFDFC]"><?= $parts[1] ?>/<?= $parts[0] ?></span>
                                    <span
                                        class="shrink-0 text-[#8B4513]"><?= number_format((float) $row['revenue'], 0, ',', ' ') ?>
                                        Ar</span>
                                </div>
                                <div
                                    class="mt-1.5 flex items-center justify-between text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                    <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-[#F3EFE9] dark:bg-[#0a0a0a]">
                                        <div class="h-full rounded-full bg-[#1b1b18] dark:bg-[#FDFDFC]"
                                            style="width: <?= $pct ?>%"></div>
                                    </div>
                                    <span class="ms-3 shrink-0"><?= (int) $row['quantity'] ?> ventes</span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-6 rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
            <h3 class="mb-4 text-lg font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Par produit</h3>
            <?php if (empty($byProduct)): ?>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune donnée sur la période.</p>
            <?php else: ?>
                <div class="max-h-96 overflow-x-auto overflow-y-auto pr-1">
                    <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                        <thead class="sticky top-0 bg-white dark:bg-[#161615]">
                            <tr
                                class="text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">
                                <th class="py-2 pr-4">Produit</th>
                                <th class="px-4 py-2">Catégorie</th>
                                <th class="px-4 py-2 text-right">Quantité</th>
                                <th class="py-2 pl-4 text-right">Revenu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                            <?php foreach ($byProduct as $prod): ?>
                                <tr>
                                    <td class="py-3 pr-4 text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC]">
                                        <?= htmlspecialchars($prod['product_name']) ?></td>
                                    <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                        <?= htmlspecialchars($prod['category_name']) ?></td>
                                    <td class="px-4 py-3 text-right text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                        <?= (int) $prod['qty'] ?></td>
                                    <td class="py-3 pl-4 text-right text-sm font-medium text-[#8B4513]">
                                        <?= number_format((float) $prod['revenue'], 0, ',', ' ') ?> Ar</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
                <h3 class="mb-4 text-lg font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Ventes produits par semaine</h3>
                <?php if (empty($byProductWeek)): ?>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune donnée sur la période.</p>
                <?php else: ?>
                    <div class="max-h-96 space-y-5 overflow-x-auto overflow-y-auto pr-1">
                        <?php foreach ($byProductWeek as $period => $items): ?>
                            <div>
                                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-[#8B4513]"><?= htmlspecialchars(formatWeekLabel($period)) ?></p>
                                <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                                    <tbody class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                                        <?php foreach ($items as $prod): ?>
                                            <tr>
                                                <td class="py-2 pr-4 text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC]">
                                                    <?= htmlspecialchars($prod['product_name'] ?? '—') ?>
                                                    <span class="ms-2 text-xs font-normal text-[#706f6c] dark:text-[#A1A09A]"><?= htmlspecialchars($prod['category_name']) ?></span>
                                                </td>
                                                <td class="px-4 py-2 text-right text-sm text-[#706f6c] dark:text-[#A1A09A]"><?= (int) $prod['qty'] ?></td>
                                                <td class="py-2 pl-4 text-right text-sm font-medium text-[#8B4513]"><?= number_format((float) $prod['revenue'], 0, ',', ' ') ?> Ar</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
                <h3 class="mb-4 text-lg font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Ventes produits par mois</h3>
                <?php if (empty($byProductMonth)): ?>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune donnée sur la période.</p>
                <?php else: ?>
                    <div class="max-h-96 space-y-5 overflow-x-auto overflow-y-auto pr-1">
                        <?php foreach ($byProductMonth as $period => $items): ?>
                            <?php $parts = explode('-', $period); ?>
                            <div>
                                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-[#8B4513]"><?= $parts[1] ?>/<?= $parts[0] ?></p>
                                <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                                    <tbody class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                                        <?php foreach ($items as $prod): ?>
                                            <tr>
                                                <td class="py-2 pr-4 text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC]">
                                                    <?= htmlspecialchars($prod['product_name'] ?? '—') ?>
                                                    <span class="ms-2 text-xs font-normal text-[#706f6c] dark:text-[#A1A09A]"><?= htmlspecialchars($prod['category_name']) ?></span>
                                                </td>
                                                <td class="px-4 py-2 text-right text-sm text-[#706f6c] dark:text-[#A1A09A]"><?= (int) $prod['qty'] ?></td>
                                                <td class="py-2 pl-4 text-right text-sm font-medium text-[#8B4513]"><?= number_format((float) $prod['revenue'], 0, ',', ' ') ?> Ar</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-6 rounded-lg bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
            <div class="flex flex-wrap items-center justify-between gap-3 p-6 pb-0">
                <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Transactions</h3>
                <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]"><?= $total ?> vente(s) sur la période</span>
            </div>
            <?php if (empty($sales)): ?>
                <p class="p-6 text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune vente enregistrée.</p>
            <?php else: ?>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                            <thead>
                                <tr class="text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">
                                    <th class="py-2 pr-4">N°</th>
                                    <th class="px-4 py-2">Date</th>
                                    <th class="px-4 py-2">Vendeur</th>
                                    <th class="px-4 py-2 text-center">Modes de paiement</th>
                                    <th class="px-4 py-2">Articles</th>
                                    <th class="py-2 pl-4 text-right">Total</th>
                                    <th class="py-2 pl-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                                <?php foreach ($sales as $sale): ?>
                                    <?php
                                    $items = $saleItems[$sale['id']] ?? [];
                                    $itemCount = 0;
                                    $saleMethods = [];
                                    foreach ($items as $it) {
                                        $itemCount += (int) $it['quantity'];
                                        $itMethod = $it['pay_method'] ?? 'cash';
                                        if (!in_array($itMethod, salePaymentMethods(), true)) {
                                            $itMethod = 'cash';
                                        }
                                        $saleMethods[$itMethod] = true;
                                    }
                                    $saleMethods = array_values(array_intersect(salePaymentMethods(), array_keys($saleMethods)));
                                    $saleLabel = '#' . $sale['id'] . ' — ' . number_format((float) $sale['total'], 0, ',', ' ') . ' Ar';
                                    ?>
                                    <tr class="cursor-pointer transition hover:bg-[#FDFDFC] dark:hover:bg-[#1a1a1a]"
                                        onclick="var d=document.getElementById('detail-<?= $sale['id'] ?>');d.classList.toggle('hidden')">
                                        <td class="py-3 pr-4 text-sm font-medium text-[#8B4513]">#<?= $sale['id'] ?></td>
                                        <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]"><?= date('d/m/Y H:i', strtotime($sale['created_at'])) ?></td>
                                        <td class="px-4 py-3 text-sm text-[#1b1b18] dark:text-[#FDFDFC]"><?= htmlspecialchars($sale['vendor_name'] ?? '—') ?></td>
                                        <td class="px-4 py-3 text-center">
                                            <?php if (empty($saleMethods)): ?>
                                                <span class="text-xs text-[#A1A09A]">—</span>
                                            <?php else: ?>
                                                <div class="flex flex-wrap items-center justify-center gap-1">
                                                    <?php foreach ($saleMethods as $saleMethod): ?>
                                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium <?= salePaymentMethodBadgeClass($saleMethod) ?>">
                                                            <?= salePaymentMethodLabel($saleMethod) ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]"><?= $itemCount ?> article(s)</td>
                                        <td class="py-3 pl-4 text-right text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC]"><?= number_format((float) $sale['total'], 0, ',', ' ') ?> Ar</td>
                                        <td class="py-3 pl-4 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <a href="<?= url('sales/edit?id=' . $sale['id']) ?>"
                                                   onclick="event.stopPropagation()"
                                                   class="rounded-lg border border-[#e3e3e0] px-2.5 py-1.5 text-xs font-medium text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#A1A09A]">
                                                    Modifier
                                                </a>
                                                <button type="button"
                                                        onclick="event.stopPropagation(); confirmDeleteSale(<?= (int) $sale['id'] ?>, <?= htmlspecialchars(json_encode($saleLabel), ENT_QUOTES) ?>)"
                                                        class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 transition hover:border-red-600 hover:bg-red-50 dark:border-red-900/50 dark:text-red-400 dark:hover:bg-red-900/20">
                                                    Supprimer
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr id="detail-<?= $sale['id'] ?>" class="hidden">
                                        <td colspan="7" class="bg-[#FDFDFC] px-6 py-4 dark:bg-[#0a0a0a]">
                                            <ul class="space-y-2">
                                                <?php foreach ($items as $item): ?>
                                                    <li class="flex items-center justify-between text-sm">
                                                        <span class="text-[#1b1b18] dark:text-[#FDFDFC]">
                                                            <?= htmlspecialchars($item['product_name'] ?? 'Produit supprimé') ?>
                                                            <span class="ms-2 text-xs text-[#706f6c] dark:text-[#A1A09A]"><?= htmlspecialchars($item['category_name'] ?? '—') ?></span>
                                                            <?php
                                                            $linePayMethod = $item['pay_method'] ?? 'cash';
                                                            if (!in_array($linePayMethod, salePaymentMethods(), true)) {
                                                                $linePayMethod = 'cash';
                                                            }
                                                            ?>
                                                            <span class="ms-2 inline-flex items-center rounded-full px-1.5 py-0.5 text-[10px] font-medium <?= salePaymentMethodBadgeClass($linePayMethod) ?>"><?= salePaymentMethodLabel($linePayMethod) ?></span>
                                                        </span>
                                                        <span class="text-[#706f6c] dark:text-[#A1A09A]">
                                                            <?= (int) $item['quantity'] ?> &#215; <?= number_format((float) $item['unit_price'], 0, ',', ' ') ?> Ar
                                                            = <span class="font-medium text-[#8B4513]"><?= number_format((float) $item['unit_price'] * $item['quantity'], 0, ',', ' ') ?> Ar</span>
                                                        </span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Page <?= $page ?> sur <?= $totalPages ?></span>
                            <div class="flex gap-2">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?= $page - 1 . $qs ?>"
                                       class="rounded-lg border border-[#e3e3e0] px-3 py-1.5 text-sm text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#A1A09A]">
                                        &laquo; Précédent
                                    </a>
                                <?php endif; ?>
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <a href="?page=<?= $i . $qs ?>"
                                       class="<?= $i === $page ? 'rounded-lg bg-[#8B4513] px-3 py-1.5 text-sm font-medium text-[#FDFDFC]' : 'rounded-lg border border-[#e3e3e0] px-3 py-1.5 text-sm text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#A1A09A]' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?= $page + 1 . $qs ?>"
                                       class="rounded-lg border border-[#e3e3e0] px-3 py-1.5 text-sm text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#A1A09A]">
                                        Suivant &raquo;
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-6 rounded-lg bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
            <div class="flex flex-wrap items-center justify-between gap-3 p-6 pb-0">
                <div>
                    <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Versements, relais et dépenses</h3>
                    <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">Vos saisies de caisse sur la période.</p>
                </div>
                <span class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                    <?= $cashEntryTotal ?> mouvement(s)<?= $cashEntryTotal > 50 ? ' — 50 derniers affichés' : '' ?>
                </span>
            </div>
            <?php if (empty($cashEntries)): ?>
                <p class="p-6 text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucun versement, relais ou dépense sur la période.</p>
            <?php else: ?>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                            <thead>
                                <tr class="text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">
                                    <th class="py-2 pr-4">Type</th>
                                    <th class="px-4 py-2">Date</th>
                                    <th class="px-4 py-2">Saisi le</th>
                                    <th class="py-2 pl-4 text-right">Montant</th>
                                    <th class="py-2 pl-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                                <?php foreach ($cashEntries as $cashEntry): ?>
                                    <?php
                                    $cashEntryType = $cashEntry['type'];
                                    $cashEntryLabel = cashEntryTypeLabel($cashEntryType);
                                    $cashEntryAmountLabel = number_format((float) $cashEntry['amount'], 0, ',', ' ') . ' Ar';
                                    $cashEntryArticle = $cashEntryType === 'depense' ? 'la ' : 'le ';
                                    $cashEntryDescription = $cashEntryArticle . mb_strtolower($cashEntryLabel) . ' de ' . $cashEntryAmountLabel;
                                    ?>
                                    <tr class="transition hover:bg-[#FDFDFC] dark:hover:bg-[#1a1a1a]">
                                        <td class="py-3 pr-4">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium <?= cashEntryTypeBadgeClass($cashEntryType) ?>">
                                                <?= $cashEntryLabel ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                            <?= date('d/m/Y', strtotime($cashEntry['entry_date'])) ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                            <?= date('d/m/Y H:i', strtotime($cashEntry['created_at'])) ?>
                                        </td>
                                        <td class="py-3 pl-4 text-right text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">
                                            <?= $cashEntryAmountLabel ?>
                                        </td>
                                        <td class="py-3 pl-4 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <button type="button"
                                                        onclick="openEntryEdit(<?= htmlspecialchars(json_encode($cashEntryType), ENT_QUOTES) ?>, <?= (int) $cashEntry['id'] ?>, <?= htmlspecialchars(json_encode($cashEntry['entry_date']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode((float) $cashEntry['amount']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($cashEntryLabel), ENT_QUOTES) ?>)"
                                                        class="rounded-lg border border-[#e3e3e0] px-2.5 py-1.5 text-xs font-medium text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#A1A09A]">
                                                    Modifier
                                                </button>
                                                <button type="button"
                                                        onclick="confirmDeleteEntry(<?= htmlspecialchars(json_encode($cashEntryType), ENT_QUOTES) ?>, <?= (int) $cashEntry['id'] ?>, <?= htmlspecialchars(json_encode($cashEntryDescription), ENT_QUOTES) ?>)"
                                                        class="rounded-lg border border-red-200 px-2.5 py-1.5 text-xs font-medium text-red-600 transition hover:border-red-600 hover:bg-red-50 dark:border-red-900/50 dark:text-red-400 dark:hover:bg-red-900/20">
                                                    Supprimer
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <form id="delete-sale-form" method="POST" action="<?= url('sales/delete') ?>" class="hidden">
            <input type="hidden" name="id" id="delete-sale-id" value="">
        </form>

        <form id="delete-entry-form" method="POST" action="<?= url('sales/entry-delete') ?>" class="hidden">
            <input type="hidden" name="type" id="delete-entry-type" value="">
            <input type="hidden" name="id" id="delete-entry-id" value="">
            <input type="hidden" name="return" value="<?= htmlspecialchars($currentUrl) ?>">
        </form>

        <div id="entry-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" onclick="closeEntryModal()"></div>
            <div class="relative w-full max-w-md rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] p-6 shadow-xl dark:border-[#3E3E3A] dark:bg-[#0a0a0a]" style="box-shadow: inset 0px 0px 0px 1px rgba(26,26,0,0.16), 0 25px 50px -12px rgba(0,0,0,0.25);">
                <div class="flex items-start justify-between gap-4">
                    <h3 id="entry-modal-title" class="text-lg font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Modifier le mouvement</h3>
                    <button type="button" class="text-[#A1A09A] transition hover:text-[#1b1b18] dark:hover:text-[#FDFDFC]" onclick="closeEntryModal()">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="<?= url('sales/entry-update') ?>" class="mt-4 space-y-4">
                    <input type="hidden" name="type" id="entry-type" value="">
                    <input type="hidden" name="id" id="entry-id" value="">
                    <input type="hidden" name="return" value="<?= htmlspecialchars($currentUrl) ?>">
                    <div>
                        <label for="entry-date" class="mb-1 block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Date</label>
                        <input type="date" id="entry-date" name="date" required max="<?= date('Y-m-d') ?>"
                            class="w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
                    </div>
                    <div>
                        <label for="entry-amount" class="mb-1 block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Montant (Ar)</label>
                        <input type="number" id="entry-amount" name="amount" min="1" step="0.01" required
                            class="w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" onclick="closeEntryModal()" class="rounded-lg border border-[#e3e3e0] px-4 py-2 text-sm font-medium text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#A1A09A]">Annuler</button>
                        <button type="submit" class="rounded-lg bg-[#8B4513] px-4 py-2 text-sm font-medium text-[#FDFDFC] transition hover:bg-[#A0522D]">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
function confirmDeleteSale(id, label) {
    showConfirmModal('Êtes-vous sûr de vouloir supprimer la vente ' + label + ' ? Les quantités des produits suivis seront remises en stock et le rapport journalier sera ajusté.', function() {
        document.getElementById('delete-sale-id').value = id;
        document.getElementById('delete-sale-form').submit();
    });
}

function openEntryEdit(type, id, date, amount, label) {
    document.getElementById('entry-type').value = type;
    document.getElementById('entry-id').value = id;
    document.getElementById('entry-date').value = date;
    document.getElementById('entry-amount').value = amount;
    document.getElementById('entry-modal-title').textContent = 'Modifier — ' + label;
    document.getElementById('entry-modal').classList.remove('hidden');
    document.getElementById('entry-amount').focus();
}

function closeEntryModal() {
    document.getElementById('entry-modal').classList.add('hidden');
}

function confirmDeleteEntry(type, id, description) {
    showConfirmModal('Êtes-vous sûr de vouloir supprimer ' + description + ' ? L\'écart de caisse sera recalculé.', function() {
        document.getElementById('delete-entry-type').value = type;
        document.getElementById('delete-entry-id').value = id;
        document.getElementById('delete-entry-form').submit();
    });
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeEntryModal();
    }
});
</script>

<?php require __DIR__ . '/../layout_footer.php'; ?>
