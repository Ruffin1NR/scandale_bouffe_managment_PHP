<?php
$vendorId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($vendorId <= 0) {
    header('Location: ' . url('vendeurs'));
    exit;
}

$vendor = fetchAssoc("SELECT u.* FROM users u JOIN roles_users ru ON ru.user_id = u.id JOIN roles r ON r.id = ru.role_id AND r.name = 'vendeur' WHERE u.id = ?", [$vendorId]);

if (!$vendor) {
    header('Location: ' . url('vendeurs'));
    exit;
}

$stats = fetchAssoc("
    SELECT COUNT(s.id) as sale_count,
           COALESCE(SUM(s.total), 0) as total_sales,
           COALESCE(SUM(si.quantity), 0) as items_sold
    FROM sales s
    LEFT JOIN sale_items si ON si.sale_id = s.id
    WHERE s.user_id = ?
", [$vendorId]);

$sales = fetchAllAssoc("
    SELECT s.id, s.total, s.created_at, GROUP_CONCAT(p.name SEPARATOR ', ') as products
    FROM sales s
    LEFT JOIN sale_items i ON i.sale_id = s.id
    LEFT JOIN products p ON p.id = i.product_id
    WHERE s.user_id = ?
    GROUP BY s.id
    ORDER BY s.created_at DESC
    LIMIT 10
", [$vendorId]);
?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails vendeur - Scandale Bouffe</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument+sans:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo url('css/tailwind.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('css/styles.css'); ?>">
</head>
<body class="min-h-screen bg-gradient-to-b from-[#FDFDFC] to-[#0a0a0a] text-[#1b1b18]">

    <?php require __DIR__ . '/../../auth/check.php'; ?>
    <?php require __DIR__ . '/../partials/toast.php'; ?>

    <nav class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] bg-[#FDFDFC] dark:bg-[#0a0a0a]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="https://scandalebouffe.netlify.app/images/scandale_logo.jpg" alt="Scandale Bouffe" class="h-9 w-9 rounded-lg object-cover">
                    <a href="<?php echo url('dashboard'); ?>" class="text-lg font-semibold text-[#8B4513]">Scandale Bouffe</a>
                </div>
                <div class="hidden sm:flex items-center gap-6">
                    <a href="<?php echo url('inventory'); ?>" class="text-sm font-medium text-[#706f6c] hover:text-[#8B4513] dark:text-[#A1A09A] dark:hover:text-[#FDFDFC]">Inventaire</a>
                    <a href="<?php echo url('vendeurs'); ?>" class="text-sm font-semibold text-[#8B4513] border-b-2 border-[#8B4513] pb-1">Vendeurs</a>
                    <a href="<?php echo url('sales'); ?>" class="text-sm font-medium text-[#706f6c] hover:text-[#8B4513] dark:text-[#A1A09A] dark:hover:text-[#FDFDFC]">Ventes</a>
                    <a href="<?php echo url('dashboard'); ?>" class="text-sm font-medium text-[#706f6c] hover:text-[#8B4513] dark:text-[#A1A09A] dark:hover:text-[#FDFDFC]">Dashboard</a>
                    <a href="<?php echo url('profile'); ?>" class="text-sm font-medium text-[#706f6c] hover:text-[#8B4513] dark:text-[#A1A09A] dark:hover:text-[#FDFDFC]">Profil</a>
                    <form method="POST" action="<?php echo url('logout'); ?>" class="inline">
                        <button type="submit" class="text-sm font-medium text-[#706f6c] hover:text-red-600 dark:text-[#A1A09A] dark:hover:text-red-400">Déconnexion</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto">
            <div class="border border-[#e3e3e0] bg-[#FDFDFC] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] rounded-lg shadow-card p-6">
                <div class="flex items-center gap-6">
                    <div>
                        <h1 class="text-3xl font-bold text-[#1b1b18] dark:text-[#FDFDFC]">
                            <?php echo htmlspecialchars($vendor['name']); ?>
                        </h1>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            <?php echo htmlspecialchars($vendor['email']); ?>
                        </p>
                    </div>
                    <div class="w-1/2">
                        <div class="rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] p-4 shadow-card mb-4">
                            <p class="text-2xl font-bold text-[#8B4513]"><?php echo number_format((float)($stats['total_sales'] ?? 0), 0, ',', ' ') . ' Ar'; ?></p>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Chiffre d'affaires</p>
                        </div>
                        <div class="rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] p-4 shadow-card mb-4">
                            <p class="text-2xl font-bold text-[#1b1b18] dark:text-[#FDFDFC]"><?php echo (int)($stats['sale_count'] ?? 0); ?></p>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Ventes</p>
                        </div>
                        <div class="rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] p-4 shadow-card">
                            <p class="text-2xl font-bold text-[#FF750F]"><?php echo (int)($stats['items_sold'] ?? 0); ?></p>
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Articles</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] shadow-card p-6">
                <h3 class="mb-4 text-lg font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Historique des ventes</h3>
                <?php if(empty($sales)): ?>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Aucune vente enregistrée pour ce vendeur.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table>
                            <thead>
                                <tr class="text-left text-xs font-medium uppercase tracking-wider text-[#706f6c] dark:text-[#A1A09A]">
                                    <th class="py-2 pr-4">Date</th>
                                    <th class="px-4 py-2">Articles</th>
                                    <th class="px-4 py-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sales as $sale): ?>
                                <tr class="border-b dark:border-[#3E3E3A]">
                                    <td class="py-3 pr-4 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                        <?php echo date('d/m/Y H:i', strtotime($sale['created_at'])); ?>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                                        <?php echo htmlspecialchars($sale['products'] ?? '—'); ?>
                                    </td>
                                    <td class="px-4 py-2 text-right text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC]">
                                        <?php echo number_format((float)$sale['total'], 0, ',', ' ') . ' Ar'; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

<footer class="border-t border-[#e3e3e0] dark:border-[#3E3E3A] mt-12 py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">&copy; <?php echo date('Y'); ?> Scandale Bouffe &mdash; Antsenakely, Antsirabe, Madagascar</p>
    </div>
</footer>

</body>
</html>
