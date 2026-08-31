<?php
$currentPage = 'vendeur';
$pageTitle = 'Tableau de bord';

require_once __DIR__ . '/../../config/db.php';
$userId = $_SESSION['user_id'];

$stats = fetchAssoc("
    SELECT
        COALESCE(SUM(s.total), 0) AS today_revenue,
        COUNT(s.id) AS today_sale_count
    FROM sales s
    WHERE s.user_id = ? AND DATE(s.created_at) = CURDATE()
", [$userId]);

$stats['today_items_sold'] = fetchAssoc("
    SELECT COALESCE(SUM(si.quantity), 0) AS qty
    FROM sale_items si
    JOIN sales s ON s.id = si.sale_id
    WHERE s.user_id = ? AND DATE(s.created_at) = CURDATE()
", [$userId])['qty'] ?? 0;

$stats['low_stock'] = fetchAssoc("SELECT COUNT(*) AS qty FROM products WHERE active = 1 AND quantity <= min_stock")['qty'] ?? 0;

$recentSales = fetchAllAssoc("
    SELECT s.id, s.total, s.created_at,
           (SELECT COALESCE(SUM(si.quantity), 0) FROM sale_items si WHERE si.sale_id = s.id) AS item_count
    FROM sales s
    WHERE s.user_id = ?
    ORDER BY s.created_at DESC
    LIMIT 5
", [$userId]);

require __DIR__ . '/../layout.php';
?>

<div class="py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
                <p class="text-3xl font-bold text-[#8B4513]"><?php echo number_format((float)$stats['today_revenue'], 0, ',', ' '); ?> Ar</p>
                <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">Ventes du jour</p>
            </div>
            <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
                <p class="text-3xl font-bold text-[#1b1b18] dark:text-[#FDFDFC]"><?php echo (int)$stats['today_sale_count']; ?></p>
                <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">Transactions du jour</p>
            </div>
            <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
                <p class="text-3xl font-bold text-[#8B4513]"><?php echo (int)$stats['today_items_sold']; ?></p>
                <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">Articles vendus aujourd'hui</p>
            </div>
            <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
                <p class="text-3xl font-bold text-[#FF750F]"><?php echo (int)$stats['low_stock']; ?></p>
                <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">Produits en stock faible</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex flex-col gap-4 sm:flex-row">
            <a href="<?php echo url('pos'); ?>" class="flex-1 rounded-lg bg-[#8B4513] px-6 py-4 text-center text-sm font-medium text-[#FDFDFC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] transition hover:bg-[#A0522D]">
                Ouvrir la caisse
            </a>
            <a href="<?php echo url('vendeur/historique'); ?>" class="flex-1 rounded-lg border border-[#e3e3e0] px-6 py-4 text-center text-sm font-medium text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#A1A09A]">
                Mes ventes
            </a>
        </div>

        <!-- Ventes Récentes -->
        <div class="mt-8">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Ventes récentes</h3>
                <a href="<?php echo url('vendeur/historique'); ?>" class="text-sm font-medium text-[#8B4513] hover:underline">Tout voir →</a>
            </div>

            <div class="mt-4 overflow-hidden rounded-lg bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
                <?php if (empty($recentSales)): ?>
                    <p class="p-6 text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune vente enregistrée pour le moment.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                            <thead class="bg-[#FDFDFC] dark:bg-[#1a1a1a]">
                                <tr class="text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">
                                    <th class="px-6 py-3">N°</th>
                                    <th class="px-6 py-3">Date</th>
                                    <th class="px-6 py-3">Articles</th>
                                    <th class="px-6 py-3 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                                <?php foreach ($recentSales as $sale): ?>
                                    <tr class="transition hover:bg-[#FDFDFC] dark:hover:bg-[#1a1a1a]">
                                        <td class="px-6 py-4 text-sm font-medium text-[#8B4513]">#<?php echo (int)$sale['id']; ?></td>
                                        <td class="px-6 py-4 text-sm text-[#706f6c] dark:text-[#A1A09A]"><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($sale['created_at']))); ?></td>
                                        <td class="px-6 py-4 text-sm text-[#706f6c] dark:text-[#A1A09A]"><?php echo (int)$sale['item_count']; ?> article(s)</td>
                                        <td class="px-6 py-4 text-right text-sm font-semibold text-[#1b1b18] dark:text-[#FDFDFC]"><?php echo number_format((float)$sale['total'], 0, ',', ' '); ?> Ar</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php require __DIR__ . '/../layout_footer.php'; ?>
