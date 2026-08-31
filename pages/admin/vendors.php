<?php
$currentPage = 'vendeurs';
$pageTitle = 'Vendeurs';
?>
<?php require __DIR__ . '/../layout.php'; ?>

<?php
$vendors = fetchAllAssoc("
    SELECT u.id, u.name, u.email, u.created_at,
           COUNT(s.id) as sale_count,
           COALESCE(SUM(s.total), 0) as total_sales,
           COALESCE(SUM(si.quantity), 0) as items_sold
    FROM users u
    JOIN roles_users ru ON ru.user_id = u.id
    JOIN roles r ON r.id = ru.role_id AND r.name = 'vendeur'
    LEFT JOIN sales s ON s.user_id = u.id
    LEFT JOIN sale_items si ON si.sale_id = s.id
    GROUP BY u.id
    ORDER BY total_sales DESC
");
$maxSales = !empty($vendors) ? max(array_column($vendors, 'total_sales')) : 1;
$totalVendors = count($vendors);
$totalSalesCount = array_sum(array_column($vendors, 'sale_count'));
$totalItemsSold = array_sum(array_column($vendors, 'items_sold'));
$totalRevenue = array_sum(array_column($vendors, 'total_sales'));
?>

<h2 class="text-xl font-semibold leading-tight text-[#1b1b18] dark:text-[#FDFDFC] mb-8">Vendeurs</h2>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <p class="text-3xl font-bold text-[#8B4513]"><?php echo $totalVendors; ?></p>
        <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">Vendeurs</p>
    </div>
    <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <p class="text-3xl font-bold text-[#1b1b18] dark:text-[#FDFDFC]"><?php echo $totalSalesCount; ?></p>
        <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">Ventes totales</p>
    </div>
    <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <p class="text-3xl font-bold text-[#FF750F]"><?php echo $totalItemsSold; ?></p>
        <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">Articles vendus</p>
    </div>
    <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <p class="text-3xl font-bold text-[#8B4513]"><?php echo number_format((float)$totalRevenue, 0, ',', ' ') . ' Ar'; ?></p>
        <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">Chiffre d'affaires</p>
    </div>
</div>

<div class="mt-8 rounded-lg bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
    <?php if (empty($vendors)): ?>
    <p class="p-12 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucun vendeur enregistré.</p>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
            <thead>
                <tr class="text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">
                    <th class="py-3 ps-6 pe-4">Vendeur</th>
                    <th class="px-4 py-3">Ventes</th>
                    <th class="px-4 py-3 text-right">Articles vendus</th>
                    <th class="py-3 ps-4 pe-6 text-right">Total des ventes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                <?php foreach ($vendors as $vendor): ?>
                <?php $pct = $maxSales > 0 ? round(($vendor['total_sales'] / $maxSales) * 100) : 0; ?>
                <tr class="transition hover:bg-[#FDFDFC] dark:hover:bg-[#1a1a1a]">
                    <td class="py-4 ps-6 pe-4">
                        <a href="<?php echo url('vendeurs/' . $vendor['id']); ?>" class="group block">
                            <p class="text-sm font-medium text-[#1b1b18] transition group-hover:text-[#8B4513] dark:text-[#FDFDFC]"><?php echo htmlspecialchars($vendor['name']); ?></p>
                            <p class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]"><?php echo htmlspecialchars($vendor['email']); ?></p>
                        </a>
                    </td>
                    <td class="px-4 py-4 text-sm text-[#706f6c] dark:text-[#A1A09A]"><?php echo (int)$vendor['sale_count']; ?></td>
                    <td class="px-4 py-4 text-right text-sm text-[#706f6c] dark:text-[#A1A09A]"><?php echo (int)$vendor['items_sold']; ?></td>
                    <td class="py-4 ps-4 pe-6">
                        <div class="flex flex-col items-end gap-1.5">
                            <span class="text-sm font-semibold text-[#8B4513]"><?php echo number_format((float)$vendor['total_sales'], 0, ',', ' ') . ' Ar'; ?></span>
                            <div class="h-1.5 w-28 overflow-hidden rounded-full bg-[#F3EFE9] dark:bg-[#0a0a0a]">
                                <div class="h-full rounded-full bg-[#8B4513]" style="width: <?php echo $pct; ?>%"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout_footer.php'; ?>
