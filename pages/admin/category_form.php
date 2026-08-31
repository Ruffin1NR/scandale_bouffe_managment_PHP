<?php
$currentPage = 'categories';
$pageTitle = 'Categorie';

$editId = (int)($_GET['id'] ?? 0);
$category = null;
if ($editId > 0) {
    $category = fetchAssoc("SELECT * FROM categories WHERE id = ?", [$editId]);
    if (!$category) { header('Location: ' . url('categories')); exit; }
    $pageTitle = 'Modifier : ' . $category['name'];
}
?>
<?php require __DIR__ . '/../layout.php'; ?>

<div class="max-w-lg mx-auto">
    <h2 class="text-xl font-semibold leading-tight text-[#1b1b18] dark:text-[#FDFDFC] mb-8">
        <?php echo $category ? 'Modifier la categorie' : 'Nouvelle categorie'; ?>
    </h2>

    <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615]">
        <form method="POST" action="<?php echo url('categories/store'); ?>" class="space-y-4">
            <?php if ($category): ?>
            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
            <?php endif; ?>

            <div>
                <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Nom</label>
                <input type="text" name="name" required value="<?php echo htmlspecialchars($category['name'] ?? ($_GET['name'] ?? '')); ?>" class="w-full rounded-lg border border-[#e3e3e0] bg-white px-4 py-2.5 text-sm shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] placeholder:text-[#A1A09A] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]" placeholder="Nom de la categorie">
            </div>

            <div>
                <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Description</label>
                <input type="text" name="description" value="<?php echo htmlspecialchars($category['description'] ?? ''); ?>" class="w-full rounded-lg border border-[#e3e3e0] bg-white px-4 py-2.5 text-sm shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] placeholder:text-[#A1A09A] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]" placeholder="Description optionnelle">
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-[#8B4513] px-5 py-2.5 text-sm font-medium text-[#FDFDFC] hover:bg-[#A0522D] transition">
                    <?php echo $category ? 'Enregistrer' : 'Creer'; ?>
                </button>
                <a href="<?php echo url('categories'); ?>" class="text-sm text-[#706f6c] hover:text-[#8B4513] dark:text-[#A1A09A] transition">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout_footer.php'; ?>
