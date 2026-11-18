<?php
$pageTitle = 'Continuité de stock - Scandale Bouffe';
$currentPage = 'inventory';
require __DIR__ . '/../../auth/check.php';
require __DIR__ . '/../layout.php';

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
    // La fonction stockMovementTotals() est définie dans config/db.php
    // (incluse via layout.php). Elle retourne ['initial'=>..., 'in'=>..., 'out'=>..., 'final'=>...]
    $totals = stockMovementTotals($product['id']);
    $rows[] = [
        'id'            => $product['id'],
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

// ------------------------------------------------------------------
// Petite aide : si on veut n'afficher que les produits suivis
// ------------------------------------------------------------------
$showTrackedOnly = (int)($_GET['tracked'] ?? 0);

?>
<div class="max-w-7xl mx-auto">
    <div class="bg-white shadow-md mb-6">
        <div class="p-6 flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">
                Continuité de stock
            </h2>
            <a href="<?php echo url('inventory'); ?>"
               class="inline-flex items-center rounded-lg border border-[#e3e3e0] px-4 py-2 text-sm font-medium text-[#706f6c] hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:hover:bg-[#161615] dark:hover:text-[#A1A09A] transition">
                ← Retour à l'inventaire
            </a>

            <?php if ($showTrackedOnly): ?>
                <a href="<?php echo url('inventory/movements'); ?>"
                   class="ml-2 inline-flex items-center rounded-lg bg-[#8B4513] px-3 py-1.5 text-xs font-medium text-[#FDFDFC] hover:bg-[#A0522D] transition">
                    Montrer tous
                </a>
            <?php else: ?>
                <a href="<?php echo url('inventory/movements?tracked=1'); ?>"
                   class="ml-2 inline-flex items-center rounded-lg bg-[#10b981] px-3 py-1.5 text-xs font-medium text-[#fff] hover:bg-[#059669] transition">
                    Montrer uniquement les produits suivis
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($rows)): ?>
        <div class="p-12 text-center">
            <p class="text-lg font-medium text-[#1b1b18] dark:text-[#FDFDFC]">Aucun produit trouvé.</p>
            <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Commencez par ajouter des produits ou activez le suivi de stock.
            </p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
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
                    <?php foreach ($rows as $row): ?>
                        <?php if ($showTrackedOnly && $row['track_stock'] !== 1) continue; ?>
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

            <div class="mt-6 text-right">
                <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Total affiché : <?php echo count($rows); ?> produit(s)
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>