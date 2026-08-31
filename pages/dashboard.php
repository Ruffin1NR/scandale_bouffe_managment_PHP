<?php
$currentPage = 'dashboard';
$pageTitle = 'Tableau de bord';
$withConfirmModal = false;
?>
<?php require __DIR__ . '/layout.php'; ?>

<?php
$stats = fetchAssoc("SELECT
    (SELECT COUNT(*) FROM products WHERE active = 1) as total,
    (SELECT COUNT(*) FROM products WHERE active = 1 AND quantity <= min_stock) as low_stock,
    (SELECT COUNT(*) FROM products WHERE active = 1 AND quantity > min_stock) as active,
    (SELECT COALESCE(SUM(p.price * p.quantity), 0) FROM products p WHERE p.active = 1) as total_value
");

$recentProducts = fetchAllAssoc("
    SELECT p.id, p.name, p.price, p.quantity, p.unit, p.active, p.min_stock
    FROM products p WHERE p.active = 1
    ORDER BY p.created_at DESC LIMIT 5
");

$lowStockProducts = fetchAllAssoc("SELECT name, quantity, min_stock, unit FROM products WHERE active = 1 AND quantity <= min_stock ORDER BY quantity ASC LIMIT 5");

// --- CHART DATA ---

// 1. Ventes 7 derniers jours
$dailySales = fetchAllAssoc("
    SELECT DATE(s.created_at) as day, COALESCE(SUM(s.total), 0) as revenue, COUNT(DISTINCT s.id) as nb_sales
    FROM sales s
    WHERE s.created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(s.created_at)
    ORDER BY day ASC
");
$chartDays = [];
$chartRevenue = [];
$chartSalesCount = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chartDays[] = date('d/m', strtotime($d));
    $found = false;
    foreach ($dailySales as $ds) {
        if ($ds['day'] === $d) {
            $chartRevenue[] = (float)$ds['revenue'];
            $chartSalesCount[] = (int)$ds['nb_sales'];
            $found = true;
            break;
        }
    }
    if (!$found) { $chartRevenue[] = 0; $chartSalesCount[] = 0; }
}

// 2. Top vendeurs
$vendorSales = fetchAllAssoc("
    SELECT u.name, COALESCE(SUM(s.total), 0) as total_sales
    FROM users u
    LEFT JOIN sales s ON s.user_id = u.id
    WHERE u.role = 'vendeur'
    GROUP BY u.id
    ORDER BY total_sales DESC
    LIMIT 5
");

// 3. Stock par catégorie
$stockByCategory = fetchAllAssoc("
    SELECT COALESCE(c.name, 'Sans categorie') as cat_name, COUNT(p.id) as product_count,
           COALESCE(SUM(p.quantity * p.price), 0) as cat_value
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.active = 1
    GROUP BY c.id, c.name
    ORDER BY product_count DESC
");

// 4. Top 5 produits vendus
$topProducts = fetchAllAssoc("
    SELECT si.product_name, SUM(si.quantity) as total_qty, SUM(si.quantity * si.unit_price) as total_revenue
    FROM sale_items si
    JOIN sales s ON si.sale_id = s.id
    WHERE s.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY si.product_name
    ORDER BY total_qty DESC
    LIMIT 5
");
?>

<h2 class="text-xl font-semibold leading-tight text-[#1b1b18] dark:text-[#FDFDFC] mb-8">Tableau de bord</h2>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
    <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <p class="text-3xl font-bold text-[#8B4513]"><?php echo $stats['total'] ?? 0; ?></p>
        <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">Produits au total</p>
    </div>
    <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <p class="text-3xl font-bold text-[#FF750F]"><?php echo $stats['low_stock'] ?? 0; ?></p>
        <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">Stock faible</p>
    </div>
    <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <p class="text-3xl font-bold text-[#1b1b18] dark:text-[#FDFDFC]"><?php echo $stats['active'] ?? 0; ?></p>
        <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">Stock actif</p>
    </div>
    <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <p class="text-3xl font-bold text-[#8B4513]"><?php echo number_format((float)($stats['total_value'] ?? 0), 0, ',', ' ') . ' Ar'; ?></p>
        <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">Valeur totale</p>
    </div>
</div>

<?php
$salesToday = fetchAssoc("SELECT COUNT(*) as nb, COALESCE(SUM(total), 0) as revenue FROM sales WHERE DATE(created_at) = CURDATE()");
$salesWeek = fetchAssoc("SELECT COUNT(*) as nb, COALESCE(SUM(total), 0) as revenue FROM sales WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)");
$salesMonth = fetchAssoc("SELECT COUNT(*) as nb, COALESCE(SUM(total), 0) as revenue FROM sales WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())");

$breakdownToday = fetchAllAssoc("SELECT payment_method, COALESCE(SUM(total), 0) as revenue FROM sales WHERE DATE(created_at) = CURDATE() GROUP BY payment_method");
$breakdownWeek = fetchAllAssoc("SELECT payment_method, COALESCE(SUM(total), 0) as revenue FROM sales WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) GROUP BY payment_method");
$breakdownMonth = fetchAllAssoc("SELECT payment_method, COALESCE(SUM(total), 0) as revenue FROM sales WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) GROUP BY payment_method");

function getPaymentBreakdown($data) {
    $map = [];
    foreach ($data as $row) { $map[$row['payment_method']] = (float)$row['revenue']; }
    return $map;
}
$bt = getPaymentBreakdown($breakdownToday);
$bw = getPaymentBreakdown($breakdownWeek);
$bm = getPaymentBreakdown($breakdownMonth);
?>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-8">
    <a href="<?php echo url('sales?from=' . date('Y-m-d') . '&to=' . date('Y-m-d')); ?>" class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] transition hover:border-[#8B4513] cursor-pointer group">
        <p class="text-xs font-medium uppercase tracking-wider text-[#A1A09A] mb-2">Aujourd'hui</p>
        <p class="text-2xl font-bold text-[#8B4513]"><?php echo number_format((float)$salesToday['revenue'], 0, ',', ' ') . ' Ar'; ?></p>
        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1"><?php echo $salesToday['nb']; ?> vente(s)</p>
        <div class="mt-3 flex flex-wrap gap-1.5">
            <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">Cash <?php echo number_format($bt['cash'] ?? 0, 0, ',', ' '); ?> Ar</span>
            <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Mobile <?php echo number_format($bt['mobile_money'] ?? 0, 0, ',', ' '); ?> Ar</span>
            <span class="inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-medium text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">Crédit <?php echo number_format($bt['credit'] ?? 0, 0, ',', ' '); ?> Ar</span>
        </div>
    </a>
    <a href="<?php echo url('sales?from=' . date('Y-m-d', strtotime('monday this week')) . '&to=' . date('Y-m-d')); ?>" class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] transition hover:border-[#8B4513] cursor-pointer group">
        <p class="text-xs font-medium uppercase tracking-wider text-[#A1A09A] mb-2">Cette semaine</p>
        <p class="text-2xl font-bold text-[#8B4513]"><?php echo number_format((float)$salesWeek['revenue'], 0, ',', ' ') . ' Ar'; ?></p>
        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1"><?php echo $salesWeek['nb']; ?> vente(s)</p>
        <div class="mt-3 flex flex-wrap gap-1.5">
            <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">Cash <?php echo number_format($bw['cash'] ?? 0, 0, ',', ' '); ?> Ar</span>
            <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Mobile <?php echo number_format($bw['mobile_money'] ?? 0, 0, ',', ' '); ?> Ar</span>
            <span class="inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-medium text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">Crédit <?php echo number_format($bw['credit'] ?? 0, 0, ',', ' '); ?> Ar</span>
        </div>
    </a>
    <a href="<?php echo url('sales?from=' . date('Y-m-01') . '&to=' . date('Y-m-d')); ?>" class="rounded-lg bg-white p-5 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] transition hover:border-[#8B4513] cursor-pointer group">
        <p class="text-xs font-medium uppercase tracking-wider text-[#A1A09A] mb-2">Ce mois</p>
        <p class="text-2xl font-bold text-[#8B4513]"><?php echo number_format((float)$salesMonth['revenue'], 0, ',', ' ') . ' Ar'; ?></p>
        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1"><?php echo $salesMonth['nb']; ?> vente(s)</p>
        <div class="mt-3 flex flex-wrap gap-1.5">
            <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">Cash <?php echo number_format($bm['cash'] ?? 0, 0, ',', ' '); ?> Ar</span>
            <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Mobile <?php echo number_format($bm['mobile_money'] ?? 0, 0, ',', ' '); ?> Ar</span>
            <span class="inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-medium text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">Crédit <?php echo number_format($bm['credit'] ?? 0, 0, ',', ' '); ?> Ar</span>
        </div>
    </a>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-8">
    <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <h3 class="text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC] mb-4">Ventes des 7 derniers jours</h3>
        <div class="relative" style="height: 260px;">
            <canvas id="chart-daily-sales"></canvas>
        </div>
    </div>
    <!-- <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <h3 class="text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC] mb-4">Top vendeurs</h3>
        <div class="relative" style="height: 260px;">
            <canvas id="chart-vendors"></canvas>
        </div>
    </div> -->
    <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <h3 class="text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC] mb-4">Stock par categorie</h3>
        <div class="relative flex items-center justify-center" style="height: 260px;">
            <canvas id="chart-stock-category"></canvas>
        </div>
    </div>
    <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <h3 class="text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC] mb-4">Top 5 produits vendus (30j)</h3>
        <div class="relative" style="height: 260px;">
            <canvas id="chart-top-products"></canvas>
        </div>
    </div>
</div>

<!-- Alertes stock -->
<?php if (!empty($lowStockProducts)): ?>
<div class="mb-8 rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
    <h3 class="text-sm font-semibold text-[#FF750F] mb-4 flex items-center gap-2">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        Alertes stock
    </h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <?php foreach ($lowStockProducts as $p): ?>
        <div class="flex items-center justify-between rounded-lg bg-[#FF750F]/5 border border-[#FF750F]/20 px-4 py-3">
            <span class="text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC]"><?php echo htmlspecialchars($p['name']); ?></span>
            <span class="text-xs font-medium text-[#FF750F]"><?php echo (float)$p['quantity']; ?> <?php echo htmlspecialchars($p['unit']); ?> / min <?php echo $p['min_stock']; ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Produits récents -->
<div class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Produits récents</h3>
        <a href="<?php echo url('inventory'); ?>" class="text-sm font-medium text-[#8B4513] hover:underline">Tout voir &rarr;</a>
    </div>
    <div class="overflow-hidden rounded-lg bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                <thead class="bg-[#FDFDFC] dark:bg-[#1a1a1a]">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Prix</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Quantite</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                    <?php if (empty($recentProducts)): ?>
                    <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucun produit en stock.</td></tr>
                    <?php else: ?>
                    <?php foreach ($recentProducts as $product): ?>
                    <tr class="transition hover:bg-[#FDFDFC] dark:hover:bg-[#1a1a1a]">
                        <td class="px-6 py-4 font-medium text-[#1b1b18] dark:text-[#FDFDFC]">
                            <a href="<?php echo url('inventory/show?id=' . $product['id']); ?>" class="text-[#8B4513] hover:underline"><?php echo htmlspecialchars($product['name']); ?></a>
                        </td>
                        <td class="px-6 py-4 text-sm text-[#1b1b18] dark:text-[#FDFDFC]"><?php echo number_format((float)$product['price'], 0, ',', ' ') . ' Ar'; ?></td>
                        <td class="px-6 py-4 text-sm">
                            <?php if ($product['quantity'] <= $product['min_stock']): ?>
                            <span class="inline-flex items-center rounded-full bg-[#FF750F]/10 px-2.5 py-1 text-xs font-medium text-[#FF750F]"><?php echo $product['quantity']; ?> <?php echo htmlspecialchars($product['unit']); ?> &middot; stock bas</span>
                            <?php else: ?>
                            <span class="inline-flex items-center rounded-full bg-[#8B4513]/10 px-2.5 py-1 text-xs font-medium text-[#8B4513]"><?php echo $product['quantity']; ?> <?php echo htmlspecialchars($product['unit']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php if ($product['active']): ?>
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Actif</span>
                            <?php else: ?>
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Inactif</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const palette = ['#8B4513', '#FF750F', '#A0522D', '#D2691E', '#CD853F'];
    const gridColor = 'rgba(112, 111, 108, 0.1)';
    const defaults = Chart.defaults;
    defaults.font.family = "'Instrument Sans', sans-serif";
    defaults.font.size = 12;
    defaults.color = '#706f6c';

    // 1. Ventes 7 jours
    new Chart(document.getElementById('chart-daily-sales'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chartDays); ?>,
            datasets: [{
                label: 'Revenu (Ar)',
                data: <?php echo json_encode($chartRevenue); ?>,
                backgroundColor: palette[0],
                borderRadius: 6,
                borderSkipped: false,
                barThickness: 24
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { callback: v => v.toLocaleString() + ' Ar' } },
                x: { grid: { display: false } }
            }
        }
    });

    // 2. Top vendeurs
    new Chart(document.getElementById('chart-vendors'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($vendorSales, 'name')); ?>,
            datasets: [{
                label: 'Ventes (Ar)',
                data: <?php echo json_encode(array_map('floatval', array_column($vendorSales, 'total_sales'))); ?>,
                backgroundColor: palette.slice(0, <?php echo count($vendorSales); ?>),
                borderRadius: 6,
                borderSkipped: false,
                barThickness: 24
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { color: gridColor }, ticks: { callback: v => v.toLocaleString() + ' Ar' } },
                y: { grid: { display: false } }
            }
        }
    });

    // 3. Stock par catégorie (doughnut)
    new Chart(document.getElementById('chart-stock-category'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($stockByCategory, 'cat_name')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_map('intval', array_column($stockByCategory, 'product_count'))); ?>,
                backgroundColor: palette,
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, pointStyle: 'circle' } }
            }
        }
    });

    // 4. Top 5 produits vendus
    new Chart(document.getElementById('chart-top-products'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($topProducts, 'product_name')); ?>,
            datasets: [{
                label: 'Qte vendue',
                data: <?php echo json_encode(array_map('intval', array_column($topProducts, 'total_qty'))); ?>,
                backgroundColor: palette.slice(0, <?php echo count($topProducts); ?>),
                borderRadius: 6,
                borderSkipped: false,
                barThickness: 24
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { color: gridColor } },
                y: { grid: { display: false } }
            }
        }
    });
});
</script>

<?php require __DIR__ . '/layout_footer.php'; ?>
