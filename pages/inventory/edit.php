<?php
$pageTitle = 'Modifier produit - Scandale Bouffe';
$currentPage = 'inventory';
require __DIR__ . '/../../auth/check.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . url('inventory'));
    exit;
}

$product = fetchAssoc("SELECT p.*, c.name AS category_name, COALESCE(c.track_stock, 0) AS track_stock FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.active = 1", [$id]);
if (!$product) {
    header('Location: ' . url('inventory'));
    exit;
}
// POST traité par le routeur public/index.php
require __DIR__ . '/../layout.php';
?>

<div class="max-w-md mx-auto">
    <div class="border border-[#e3e3e0] bg-[#FDFDFC] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] rounded-lg p-6 shadow-card">
        <h2 class="mb-4 text-2xl font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">
            Modifier : <?php echo htmlspecialchars($product['name']); ?>
        </h2>

        <?php if (!empty($product['image'])): ?>
        <div class="mb-4">
            <img src="<?php echo url('uploads/' . htmlspecialchars($product['image'])); ?>" alt="Image actuelle" class="h-24 w-24 rounded-lg object-cover shadow-sm">
            <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">Image actuelle</p>
        </div>
        <?php endif; ?>

        <form action="<?php echo url('inventory/update'); ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
            <?php if (!empty($product['image'])): ?>
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($product['image']); ?>">
            <?php endif; ?>

            <div>
                <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Nom</label>
                <input type="text" name="name" required value="<?php echo htmlspecialchars($product['name']); ?>" class="w-full input-field">
            </div>

            <div>
                <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Description</label>
                <textarea name="description" rows="3" class="w-full input-field"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Prix (Ar)</label>
                <input type="number" name="price" min="0" step="100" required value="<?php echo htmlspecialchars($product['price']); ?>" class="w-full input-field">
            </div>

            <div class="sm:grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Catégorie</label>
                    <select name="category_id" required class="w-full input-field">
                        <option value="">Sélectionner une catégorie</option>
                        <?php
                        $allCategories = fetchAllAssoc("SELECT id, name, track_stock FROM categories ORDER BY name");
                        foreach ($allCategories as $cat):
                        ?>
                        <option value="<?php echo $cat['id']; ?>" data-track-stock="<?php echo (int)$cat['track_stock']; ?>" <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?><?php echo $cat['track_stock'] ? ' (stock suivi)' : ''; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Unité</label>
                    <select name="unit" class="w-full input-field">
                        <option value="pcs" <?php echo $product['unit'] == 'pcs' ? 'selected' : ''; ?>>pcs</option>
                        <option value="pièce" <?php echo $product['unit'] == 'pièce' ? 'selected' : ''; ?>>pièce</option>
                        <option value="kg" <?php echo $product['unit'] == 'kg' ? 'selected' : ''; ?>>kg</option>
                        <option value="g" <?php echo $product['unit'] == 'g' ? 'selected' : ''; ?>>g</option>
                    </select>
                </div>
            </div>

            <div class="hidden" data-stock-field>
                <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Quantité en stock</label>
                <input type="number" name="quantity" min="0" step="0.01" value="<?php echo htmlspecialchars($product['quantity']); ?>" class="w-full input-field" disabled>
            </div>

            <div class="hidden" data-stock-field>
                <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Stock minimum</label>
                <input type="number" name="min_stock" min="0" value="<?php echo htmlspecialchars($product['min_stock']); ?>" class="w-full input-field" disabled>
            </div>

            <div>
                <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Nouvelle image</label>
                <input type="file" name="image" accept="image/*"
                    class="w-full text-sm text-[#706f6c] dark:text-[#A1A09A] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#8B4513] file:text-[#FDFDFC] hover:file:bg-[#A0522D] file:cursor-pointer">
                <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">Laisser vide pour garder l'image actuelle.</p>
            </div>

            <button type="submit" class="w-full rounded-lg bg-[#8B4513] py-2 px-4 text-sm font-medium text-[#FDFDFC] hover:bg-[#A0522D] transition-colors">
                Enregistrer les modifications
            </button>

            <a href="<?php echo url('inventory'); ?>" class="mt-2 block text-sm text-[#8B4513] hover:underline text-center">
                Annuler
            </a>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.querySelector('select[name="category_id"]');
    const stockFields = document.querySelectorAll('[data-stock-field]');
    if (!categorySelect) return;

    function syncStockFields() {
        const option = categorySelect.options[categorySelect.selectedIndex];
        const tracked = option && option.dataset.trackStock === '1';
        stockFields.forEach(function (field) {
            field.classList.toggle('hidden', !tracked);
            field.querySelectorAll('input').forEach(function (input) {
                input.disabled = !tracked;
            });
        });
    }

    categorySelect.addEventListener('change', syncStockFields);
    syncStockFields();
});
</script>

<?php require __DIR__ . '/../layout_footer.php'; ?>
