<?php
$currentPage = 'categories';
$pageTitle = 'Categories';
?>
<?php require __DIR__ . '/../layout.php'; ?>

<?php
$categories = fetchAllAssoc("
    SELECT c.*, COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.active = 1
    GROUP BY c.id
    ORDER BY c.name ASC
");
?>

<div class="flex items-center justify-between mb-8">
    <h2 class="text-xl font-semibold leading-tight text-[#1b1b18] dark:text-[#FDFDFC]">Categories</h2>
    <a href="<?php echo url('categories/create'); ?>" class="inline-flex items-center gap-1.5 rounded-lg bg-[#8B4513] px-3 py-1.5 text-sm font-medium text-[#FDFDFC] hover:bg-[#A0522D] transition">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Ajouter
    </a>
</div>

<div class="overflow-hidden rounded-lg bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
            <thead class="bg-[#FDFDFC] dark:bg-[#1a1a1a]">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Nom</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Description</th>
                    <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Produits</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune categorie.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                <tr class="transition hover:bg-[#FDFDFC] dark:hover:bg-[#1a1a1a]">
                    <td class="px-6 py-4 font-medium text-[#1b1b18] dark:text-[#FDFDFC]"><?php echo htmlspecialchars($cat['name']); ?></td>
                    <td class="px-6 py-4 text-sm text-[#706f6c] dark:text-[#A1A09A]"><?php echo htmlspecialchars($cat['description'] ?? '-'); ?></td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center rounded-full bg-[#8B4513]/10 px-2.5 py-1 text-xs font-medium text-[#8B4513]"><?php echo (int)$cat['product_count']; ?> produit(s)</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="<?php echo url('categories/edit?id=' . $cat['id']); ?>" class="p-1.5 rounded-lg text-[#706f6c] hover:text-[#8B4513] hover:bg-[#8B4513]/10 transition" title="Modifier">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <?php if ($cat['product_count'] == 0): ?>
                            <form method="POST" action="<?php echo url('categories/delete'); ?>" class="inline" onsubmit="return confirm('Supprimer cette categorie ?')">
                                <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                <button type="submit" class="p-1.5 rounded-lg text-[#706f6c] hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition" title="Supprimer">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layout_footer.php'; ?>
