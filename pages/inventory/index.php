<?php
$pageTitle = 'Inventaire - Scandale Bouffe';
$currentPage = 'inventory';
$withConfirmModal = true;

require __DIR__ . '/../../auth/check.php';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryFilter = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

$where = 'WHERE 1=1';
$params = [];

if ($search !== '') {
    $where .= ' AND p.name LIKE ?';
    $params[] = '%' . $search . '%';
}
if ($categoryFilter > 0) {
    $where .= ' AND p.category_id = ?';
    $params[] = $categoryFilter;
}

$countRow = fetchAssoc("SELECT COUNT(*) AS cnt FROM products p $where", $params);
$totalItems = (int)($countRow['cnt'] ?? 0);
$totalPages = max(1, ceil($totalItems / $perPage));

$products = fetchAllAssoc(
    "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id $where ORDER BY p.name ASC LIMIT $perPage OFFSET $offset",
    $params
);

$stats = fetchAssoc("SELECT COUNT(*) AS total, SUM(CASE WHEN quantity <= min_stock THEN 1 ELSE 0 END) AS low_stock, SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) AS active, COALESCE(SUM(CASE WHEN active = 1 THEN quantity * price ELSE 0 END), 0) AS total_value FROM products");

$categories = fetchAllAssoc("SELECT id, name FROM categories ORDER BY name ASC");

require __DIR__ . '/../layout.php';
?>

<div class="space-y-6">

    <!-- Header -->
    <header class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#FDFDFC]">Gestion de Stock</h1>
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Suivez et gérez vos produits en temps réel.</p>
        </div>
        <a href="<?php echo url('inventory/create'); ?>"
           class="inline-flex items-center rounded-lg bg-[#8B4513] px-4 py-2 text-sm font-medium text-[#FDFDFC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] transition hover:bg-[#A0522D]">
            <svg class="me-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14"/>
            </svg>
            Ajouter un produit
        </a>
    </header>

    <!-- Stats Summary -->
    <div class="grid grid-cols-4 gap-4">
        <div class="rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
            <div class="text-3xl font-bold text-[#8B4513]"><?php echo (int)($stats['total'] ?? 0); ?></div>
            <div class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">Produits au total</div>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
            <div class="text-3xl font-bold text-[#FF750F]"><?php echo (int)($stats['low_stock'] ?? 0); ?></div>
            <div class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">Stock faible</div>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
            <div class="text-3xl font-bold text-[#1b1b18] dark:text-[#FDFDFC]"><?php echo (int)($stats['active'] ?? 0); ?></div>
            <div class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">Stock actif</div>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
            <div class="text-3xl font-bold text-[#8B4513]"><?php echo number_format((float)($stats['total_value'] ?? 0), 0, ',', ' '); ?> Ar</div>
            <div class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">Valeur totale</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="rounded-lg bg-white p-4 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <form method="GET" action="<?php echo url('inventory'); ?>" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Rechercher un produit…"
                   class="flex-1 rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] placeholder-[#A1A09A] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#FDFDFC]">
            <select name="category_id"
                    class="rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-transparent focus:outline-none focus:ring-2 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#FDFDFC]">
                <option value="0">Toutes les catégories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $categoryFilter == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg bg-[#8B4513] px-4 py-2 text-sm font-medium text-[#FDFDFC] transition hover:bg-[#A0522D]">
                Filtrer
            </button>
            <?php if ($search !== '' || $categoryFilter > 0): ?>
                <a href="<?php echo url('inventory'); ?>"
                   class="inline-flex items-center justify-center rounded-lg border border-[#e3e3e0] px-4 py-2 text-sm font-medium text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#A1A09A]">
                    Réinitialiser
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Products Table -->
    <div class="overflow-hidden rounded-lg bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <?php if (empty($products)): ?>
            <div class="p-12 text-center">
                <p class="text-lg font-medium text-[#1b1b18] dark:text-[#FDFDFC]">Aucun produit en stock</p>
                <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Commencez par ajouter votre premier produit.
                </p>
                <a href="<?php echo url('inventory/create'); ?>"
                   class="mt-6 inline-flex items-center rounded-lg bg-[#8B4513] px-4 py-2 text-sm font-medium text-[#FDFDFC] transition hover:bg-[#A0522D]">
                    Ajouter un produit
                </a>
            </div>
        <?php else: ?>
        <div class="flex items-center justify-between border-b border-[#e3e3e0] px-6 py-3 dark:border-[#3E3E3A]">
            <form method="POST" action="<?php echo url('inventory/bulk-destroy'); ?>" id="bulk-delete-form">
                <button type="submit" id="bulk-delete-btn" disabled
                        class="inline-flex items-center rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-red-900/30 dark:hover:bg-red-900/20">
                    Supprimer la sélection (<span id="selected-count">0</span>)
                </button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                <thead class="bg-[#FDFDFC] dark:bg-[#1a1a1a]">
                    <tr>
                        <th class="w-12 px-4 py-3">
                            <input type="checkbox" id="select-all"
                                   class="h-4 w-4 rounded border-[#e3e3e0] text-[#8B4513] focus:ring-[#8B4513] dark:border-[#3E3E3A]">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Catégorie</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Prix</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Quantité</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                    <?php foreach ($products as $product): ?>
                    <tr class="transition hover:bg-[#FDFDFC] dark:hover:bg-[#1a1a1a]">
                        <td class="px-4 py-4">
                            <input type="checkbox" name="ids[]" value="<?php echo $product['id']; ?>"
                                   form="bulk-delete-form"
                                   class="row-checkbox h-4 w-4 rounded border-[#e3e3e0] text-[#8B4513] focus:ring-[#8B4513] dark:border-[#3E3E3A]">
                        </td>
                        <td class="px-6 py-4 font-medium text-[#1b1b18] dark:text-[#FDFDFC]">
                            <a href="<?php echo url('inventory/show?id=' . $product['id']); ?>" class="text-[#8B4513] hover:underline">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php if ($product['category_name']): ?>
                                <span class="inline-flex items-center rounded-full bg-[#FDF3EA] px-2.5 py-1 text-xs font-medium text-[#8B4513] dark:bg-[#0a0a0a]">
                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-sm text-[#A1A09A]">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC]">
                            <?php echo number_format((float)$product['price'], 0, ',', ' '); ?> Ar
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php if ((int)$product['quantity'] <= (int)$product['min_stock']): ?>
                                <span class="inline-flex items-center rounded-full bg-[#FF750F]/10 px-2.5 py-1 text-xs font-medium text-[#FF750F]">
                                    <?php echo (int)$product['quantity']; ?> <?php echo htmlspecialchars($product['unit']); ?> · stock bas
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center rounded-full bg-[#8B4513]/10 px-2.5 py-1 text-xs font-medium text-[#8B4513]">
                                    <?php echo (int)$product['quantity']; ?> <?php echo htmlspecialchars($product['unit']); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium <?php echo $product['active'] ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'; ?>">
                                <?php echo $product['active'] ? 'Actif' : 'Inactif'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <div class="flex justify-end gap-3">
                                <a href="<?php echo url('inventory/edit?id=' . $product['id']); ?>" class="text-[#8B4513] hover:underline">Modifier</a>
                                <button type="button"
                                        onclick="openDeleteModal(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>')"
                                        class="text-red-600 hover:underline">Supprimer</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="border-t border-[#e3e3e0] px-6 py-4 dark:border-[#3E3E3A]">
            <?php if ($totalPages > 1): ?>
            <div class="flex items-center justify-center gap-1">
                <?php if ($page > 1): ?>
                    <a href="<?php echo url('inventory?page=' . ($page - 1) . '&search=' . urlencode($search) . '&category_id=' . $categoryFilter); ?>" class="px-3 py-2 rounded-lg text-sm font-medium border border-[#e3e3e0] text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A] transition hover:bg-[#FDFDFC] dark:hover:bg-[#1a1a1a]">&laquo;</a>
                <?php endif; ?>
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="px-3 py-2 rounded-lg text-sm font-medium border border-[#8B4513] bg-[#8B4513] text-[#FDFDFC]"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="<?php echo url('inventory?page=' . $i . '&search=' . urlencode($search) . '&category_id=' . $categoryFilter); ?>" class="px-3 py-2 rounded-lg text-sm font-medium border border-[#e3e3e0] text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A] transition hover:bg-[#FDFDFC] dark:hover:bg-[#1a1a1a]"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="<?php echo url('inventory?page=' . ($page + 1) . '&search=' . urlencode($search) . '&category_id=' . $categoryFilter); ?>" class="px-3 py-2 rounded-lg text-sm font-medium border border-[#e3e3e0] text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A] transition hover:bg-[#FDFDFC] dark:hover:bg-[#1a1a1a]">&raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
const selectAll = document.getElementById('select-all');
const checkboxes = document.querySelectorAll('.row-checkbox');
const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
const selectedCount = document.getElementById('selected-count');

function updateBulkUI() {
    const checked = document.querySelectorAll('.row-checkbox:checked').length;
    selectedCount.textContent = checked;
    bulkDeleteBtn.disabled = checked === 0;
}

selectAll.addEventListener('change', function() {
    checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
    updateBulkUI();
});

checkboxes.forEach(cb => {
    cb.addEventListener('change', function() {
        selectAll.checked = checkboxes.length > 0 && [...checkboxes].every(c => c.checked);
        updateBulkUI();
    });
});

updateBulkUI();

function openDeleteModal(id, name) {
    window._deleteId = id;
    window._deleteName = name;
    const modal = document.getElementById('confirm-modal');
    if (modal) {
        document.getElementById('confirm-modal-message').textContent = 'Êtes-vous sûr de vouloir supprimer "' + name + '" ?';
        document.getElementById('confirm-modal-ok').onclick = function() {
            modal.classList.add('hidden');
            window.location.href = '<?php echo url("inventory/delete?id="); ?>' + id;
        };
        modal.classList.remove('hidden');
    }
}
</script>

<?php require __DIR__ . '/../layout_footer.php'; ?>
