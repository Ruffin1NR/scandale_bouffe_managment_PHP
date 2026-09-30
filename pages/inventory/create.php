<?php
$pageTitle = 'Nouveau produit - Scandale Bouffe';
$currentPage = 'inventory';
require __DIR__ . '/../../auth/check.php';
require __DIR__ . '/../layout.php';
?>

<div class="rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] shadow-card p-6">
    <h2 class="mb-6 text-2xl font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">
        Nouveau produit
    </h2>

    <form action="<?php echo url('inventory/store'); ?>" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Nom du produit</label>
            <input type="text" name="name" required
                class="w-full input-field">
        </div>

        <div>
            <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Prix (Ar)</label>
            <input type="number" name="price" min="0" step="100" required
                class="w-full input-field">
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Description</label>
            <textarea name="description" rows="3"
                class="w-full input-field"
                placeholder="Description du produit (optionnel)"></textarea>
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Catégorie</label>
            <select name="category_id" required class="w-full input-field">
                <option value="">Sélectionner une catégorie</option>
                <?php
                $allCategories = fetchAllAssoc("SELECT id, name FROM categories ORDER BY name");
                foreach ($allCategories as $cat):
                ?>
                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Unité</label>
            <select name="unit" class="w-full input-field">
                <option value="pcs">pcs</option>
                <option value="pièce">pièce</option>
                <option value="kg">kg</option>
                <option value="g">g</option>
                <option value="L">L</option>
                <option value="cl">cl</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Stock minimum</label>
            <input type="number" name="min_stock" min="0" value="5" required
                class="w-full input-field">
        </div>

        <div>
            <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Quantité initiale</label>
            <input type="number" name="quantity" min="0" step="0.01" value="0"
                class="w-full input-field">
        </div>

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Image du produit</label>
            <input type="file" name="image" accept="image/*"
                class="w-full text-sm text-[#706f6c] dark:text-[#A1A09A] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#8B4513] file:text-[#FDFDFC] hover:file:bg-[#A0522D] file:cursor-pointer">
            <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">Formats acceptés : JPG, PNG, WebP. Taille max : 2 Mo.</p>
        </div>

        <button type="submit"
            class="sm:col-span-2 rounded-lg bg-[#8B4513] py-2 px-4 text-sm font-medium text-[#FDFDFC] mt-4 sm:mt-0 hover:bg-[#A0522D] transition-colors">
            Créer le produit
        </button>
    </form>
</div>

<?php require __DIR__ . '/../layout_footer.php'; ?>
