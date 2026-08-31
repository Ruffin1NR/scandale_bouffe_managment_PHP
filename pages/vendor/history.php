<?php
$currentPage = 'historique';
$pageTitle = 'Mes ventes';

require __DIR__ . '/../layout.php';

$userId = $_SESSION['user_id'];
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$dateFrom = $_GET['from'] ?? '';
$dateTo = $_GET['to'] ?? '';

$where = "WHERE s.user_id = ?";
$params = [$userId];

if ($dateFrom !== '') { $where .= " AND s.created_at >= ?"; $params[] = $dateFrom . ' 00:00:00'; }
if ($dateTo !== '') { $where .= " AND s.created_at <= ?"; $params[] = $dateTo . ' 23:59:59'; }

$totalResult = fetchAssoc("SELECT COUNT(*) as count FROM sales s $where", $params);
$total = (int)($totalResult['count'] ?? 0);
$totalPages = max(1, (int)ceil($total / $perPage));

$hasFilters = ($dateFrom !== '' || $dateTo !== '');

$baseUrl = url('vendeur/historique');

$sales = fetchAllAssoc("
    SELECT s.id, s.total, s.created_at
    FROM sales s
    $where
    ORDER BY s.created_at DESC
    LIMIT $perPage OFFSET $offset
", $params);

$saleIds = array_column($sales, 'id');
$saleItems = [];
if (!empty($saleIds)) {
    $placeholders = implode(',', array_fill(0, count($saleIds), '?'));
    $items = fetchAllAssoc("
        SELECT si.*, p.name AS product_name, c.name AS category_name
        FROM sale_items si
        LEFT JOIN products p ON p.id = si.product_id
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE si.sale_id IN ($placeholders)
        ORDER BY si.sale_id
    ", $saleIds);
    foreach ($items as $item) {
        $saleItems[$item['sale_id']][] = $item;
    }
}
?>

<div class="py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-xl font-semibold leading-tight text-[#1b1b18] dark:text-[#FDFDFC] mb-6">Mes ventes</h2>

        <div class="mb-6 rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
            <form method="GET" action="<?php echo $baseUrl; ?>">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Du</label>
                        <input
                            type="date"
                            name="from"
                            value="<?php echo htmlspecialchars($dateFrom); ?>"
                            class="w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]"
                        >
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Au</label>
                        <input
                            type="date"
                            name="to"
                            value="<?php echo htmlspecialchars($dateTo); ?>"
                            class="w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]"
                        >
                    </div>
                    <div class="flex items-end gap-2">
                        <button
                            type="submit"
                            class="flex-1 rounded-lg bg-[#8B4513] px-4 py-2 text-sm font-medium text-[#FDFDFC] transition hover:bg-[#A0522D]"
                        >
                            Filtrer
                        </button>
                        <?php if ($hasFilters): ?>
                        <a
                            href="<?php echo $baseUrl; ?>"
                            class="rounded-lg border border-[#e3e3e0] px-4 py-2 text-sm font-medium text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                        >
                            R&eacute;initialiser
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
            <?php if (empty($sales)): ?>
                <p class="p-12 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Aucune vente enregistr&eacute;e.
                </p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                        <thead>
                            <tr class="text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">
                                <th class="py-2 pr-4">N&deg;</th>
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2">Articles</th>
                                <th class="py-2 pl-4 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                            <?php foreach ($sales as $sale):
                                $items = $saleItems[$sale['id']] ?? [];
                                $itemCount = array_sum(array_map(function ($i) { return (int)$i['quantity']; }, $items));
                                $productNames = array_map(function ($i) { return $i['product_name']; }, $items);
                            ?>
                            <tr
                                class="cursor-pointer transition hover:bg-[#FDFDFC] dark:hover:bg-[#1a1a1a]"
                                onclick="var d=document.getElementById('detail-<?php echo $sale['id']; ?>');d.classList.toggle('hidden');"
                            >
                                <td class="py-3 pr-4 text-sm font-medium text-[#8B4513]">
                                    #<?php echo $sale['id']; ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                    <?php echo date('d/m/Y H:i', strtotime($sale['created_at'])); ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                    <span class="font-medium text-[#1b1b18] dark:text-[#FDFDFC]">
                                        <?php echo $itemCount; ?> article(s)
                                    </span>
                                    <span class="ms-1 block text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                        <?php echo htmlspecialchars(implode(', ', $productNames)); ?>
                                    </span>
                                </td>
                                <td class="py-3 pl-4 text-right text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">
                                    <?php echo number_format((float)$sale['total'], 0, ',', ' '); ?> Ar
                                </td>
                            </tr>
                            <tr id="detail-<?php echo $sale['id']; ?>" class="hidden">
                                <td colspan="4" class="bg-[#FDFDFC] px-6 py-4 dark:bg-[#0a0a0a]">
                                    <ul class="space-y-2">
                                        <?php foreach ($items as $item): ?>
                                        <li class="flex items-center justify-between text-sm">
                                            <span class="text-[#1b1b18] dark:text-[#FDFDFC]">
                                                <?php echo htmlspecialchars($item['product_name']); ?>
                                                <span class="ms-2 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                                    <?php echo htmlspecialchars($item['category_name'] ?? '—'); ?>
                                                </span>
                                            </span>
                                            <span class="text-[#706f6c] dark:text-[#A1A09A]">
                                                <?php echo (int)$item['quantity']; ?> × <?php echo number_format((float)$item['unit_price'], 0, ',', ' '); ?> Ar
                                                =
                                                <span class="font-medium text-[#8B4513]">
                                                    <?php echo number_format((float)$item['quantity'] * (float)$item['unit_price'], 0, ',', ' '); ?> Ar
                                                </span>
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
                    <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Page <?php echo $page; ?> sur <?php echo $totalPages; ?>
                    </span>
                    <div class="flex gap-2">
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a
                            href="<?php echo $baseUrl . '?page=' . $i . ($dateFrom ? '&from=' . urlencode($dateFrom) : '') . ($dateTo ? '&to=' . urlencode($dateTo) : ''); ?>"
                            class="<?php echo $i === $page ? 'rounded-lg bg-[#8B4513] px-3 py-1.5 text-sm font-medium text-[#FDFDFC]' : 'rounded-lg border border-[#e3e3e0] px-3 py-1.5 text-sm text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#A1A09A]'; ?>"
                        >
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout_footer.php'; ?>
