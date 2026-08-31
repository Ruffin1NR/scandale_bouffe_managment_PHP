<?php
$pageTitle = 'Détail produit - Scandale Bouffe';
$currentPage = 'inventory';
$withConfirmModal = true;
require __DIR__ . '/../../auth/check.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: ' . url('inventory')); exit; }

$product = fetchAssoc("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?", [$id]);
if (!$product) { header('Location: ' . url('inventory')); exit; }

$recentSales = fetchAllAssoc(
    "SELECT si.quantity, si.unit_price, s.created_at, u.name as vendor_name
     FROM sale_items si
     JOIN sales s ON si.sale_id = s.id
     LEFT JOIN users u ON s.user_id = u.id
     WHERE si.product_id = ?
     ORDER BY s.created_at DESC LIMIT 5",
    [$id]
);

require __DIR__ . '/../layout.php';
?>

<div class="mb-6">
    <a href="<?php echo url('inventory'); ?>" class="inline-flex items-center gap-1.5 text-sm text-[#706f6c] hover:text-[#8B4513] dark:text-[#A1A09A] transition">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Retour à l'inventaire
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] shadow-card p-6">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#FDFDFC]"><?php echo htmlspecialchars($product['name']); ?></h1>
                <?php if ($product['category_name']): ?>
                <span class="mt-2 inline-flex items-center rounded-full bg-[#8B4513]/10 px-2.5 py-0.5 text-xs font-medium text-[#8B4513]"><?php echo htmlspecialchars($product['category_name']); ?></span>
                <?php endif; ?>
            </div>
            <div class="flex gap-2">
                <a href="<?php echo url('inventory/edit?id=' . $product['id']); ?>" class="inline-flex items-center gap-1.5 rounded-lg border border-[#e3e3e0] px-3 py-1.5 text-sm font-medium text-[#706f6c] hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] transition">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Modifier
                </a>
            </div>
        </div>

        <?php if (!empty($product['image'])): ?>
        <div class="mb-6">
            <img src="<?php echo url('uploads/' . htmlspecialchars($product['image'])); ?>" alt="Image du produit" class="h-32 w-32 rounded-lg object-cover shadow-sm">
        </div>
        <?php endif; ?>

        <?php if (!empty($product['description'])): ?>
        <div class="mb-6 text-sm text-[#706f6c] dark:text-[#A1A09A] leading-relaxed">
            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
        </div>
        <?php endif; ?>

        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="rounded-lg bg-[#f8f8f7] dark:bg-[#1a1a1a] p-4">
                <dt class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Prix</dt>
                <dd class="mt-1 text-xl font-semibold text-[#1b1b18] dark:text-[#FDFDFC]"><?php echo number_format((float)$product['price'], 0, ',', ' '); ?> <span class="text-sm font-normal">Ar</span></dd>
            </div>
            <div class="rounded-lg bg-[#f8f8f7] dark:bg-[#1a1a1a] p-4">
                <dt class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Stock actuel</dt>
                <dd class="mt-1 text-xl font-semibold <?php echo ($product['quantity'] <= $product['min_stock']) ? 'text-red-600' : 'text-[#1b1b18] dark:text-[#FDFDFC]'; ?>">
                    <?php echo number_format((float)$product['quantity'], 0, ',', ' '); ?>
                    <span class="text-sm font-normal"><?php echo htmlspecialchars($product['unit']); ?></span>
                </dd>
            </div>
            <div class="rounded-lg bg-[#f8f8f7] dark:bg-[#1a1a1a] p-4">
                <dt class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Stock min</dt>
                <dd class="mt-1 text-xl font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">
                    <?php echo number_format((float)$product['min_stock'], 0, ',', ' '); ?>
                    <span class="text-sm font-normal"><?php echo htmlspecialchars($product['unit']); ?></span>
                </dd>
            </div>
            <div class="rounded-lg bg-[#f8f8f7] dark:bg-[#1a1a1a] p-4">
                <dt class="text-xs font-medium text-[#706f6c] dark:text-[#A1A09A]">Statut</dt>
                <dd class="mt-1">
                    <?php if ($product['quantity'] <= 0): ?>
                    <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-900/30 px-2 py-0.5 text-xs font-medium text-red-700 dark:text-red-400">Rupture</span>
                    <?php elseif ($product['quantity'] <= $product['min_stock']): ?>
                    <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-900/30 px-2 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-400">Stock bas</span>
                    <?php else: ?>
                    <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900/30 px-2 py-0.5 text-xs font-medium text-green-700 dark:text-green-400">OK</span>
                    <?php endif; ?>
                </dd>
            </div>
        </dl>

        <?php if ($product['quantity'] > 0 && $product['price'] > 0): ?>
        <div class="mt-6 rounded-lg bg-[#8B4513]/5 border border-[#8B4513]/20 p-4">
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Valeur du stock</p>
            <p class="mt-1 text-2xl font-semibold text-[#8B4513]">
                <?php echo number_format((float)$product['quantity'] * (float)$product['price'], 0, ',', ' '); ?> Ar
            </p>
        </div>
        <?php endif; ?>
    </div>

    <div class="rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] shadow-card p-6">
        <h2 class="text-lg font-semibold text-[#1b1b18] dark:text-[#FDFDFC] mb-4">Ventes récentes</h2>
        <?php if (empty($recentSales)): ?>
        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune vente enregistrée.</p>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($recentSales as $sale): ?>
            <div class="flex items-center justify-between border-b border-[#e3e3e0] dark:border-[#3E3E3A] pb-3 last:border-0 last:pb-0">
                <div>
                    <p class="text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC]"><?php echo (int)$sale['quantity']; ?> x <?php echo htmlspecialchars($product['name']); ?></p>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]"><?php echo date('d/m/Y H:i', strtotime($sale['created_at'])); ?></p>
                </div>
                <p class="text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]"><?php echo number_format((float)$sale['quantity'] * (float)$sale['unit_price'], 0, ',', ' '); ?> Ar</p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layout_footer.php'; ?>
