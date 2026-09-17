<?php
/**
 * Layout partagé - Scandale Bouffe PHP Pur
 * Usage: ob_start(); require __DIR__ . '/../layout.php'; <contenu> <?php require __DIR__ . '/../layout_footer.php';
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

$currentPage = $currentPage ?? '';
$isAdmin = ($_SESSION['user_role'] ?? '') === 'administrateur';
?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Scandale Bouffe'); ?></title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument+sans:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo url('css/tailwind.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('css/styles.css'); ?>">
</head>
<body class="min-h-screen bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] antialiased">

<?php require __DIR__ . '/partials/toast.php'; ?>
<?php if (isset($withConfirmModal) && $withConfirmModal): ?>
<?php require __DIR__ . '/partials/confirm_modal.php'; ?>
<?php endif; ?>

<nav class="border-b border-[#e3e3e0] dark:border-[#3E3E3A] bg-[#FDFDFC] dark:bg-[#0a0a0a]">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="https://scandalebouffe.netlify.app/images/scandale_logo.jpg" alt="Scandale Bouffe" class="h-9 w-9 rounded-lg object-cover">
                <a href="<?php echo url($isAdmin ? 'dashboard' : 'vendeur'); ?>" class="text-lg font-semibold text-[#8B4513]">Scandale Bouffe</a>
            </div>

            <div class="hidden sm:flex items-center gap-6">
                <?php if ($isAdmin): ?>
                    <a href="<?php echo url('dashboard'); ?>" class="text-sm font-medium <?php echo $currentPage === 'dashboard' ? 'font-semibold text-[#8B4513] border-b-2 border-[#8B4513] pb-1' : 'text-[#706f6c] hover:text-[#8B4513] dark:text-[#A1A09A] dark:hover:text-[#FDFDFC]'; ?>">Tableau de bord</a>
                    <a href="<?php echo url('categories'); ?>" class="text-sm font-medium <?php echo $currentPage === 'categories' ? 'font-semibold text-[#8B4513] border-b-2 border-[#8B4513] pb-1' : 'text-[#706f6c] hover:text-[#8B4513] dark:text-[#A1A09A] dark:hover:text-[#FDFDFC]'; ?>">Catégories</a>
                    <a href="<?php echo url('inventory'); ?>" class="text-sm font-medium <?php echo $currentPage === 'inventory' ? 'font-semibold text-[#8B4513] border-b-2 border-[#8B4513] pb-1' : 'text-[#706f6c] hover:text-[#8B4513] dark:text-[#A1A09A] dark:hover:text-[#FDFDFC]'; ?>">Inventaire</a>
                    <!-- <a href="<?php echo url('vendeurs'); ?>" class="text-sm font-medium <?php echo $currentPage === 'vendeurs' ? 'font-semibold text-[#8B4513] border-b-2 border-[#8B4513] pb-1' : 'text-[#706f6c] hover:text-[#8B4513] dark:text-[#A1A09A] dark:hover:text-[#FDFDFC]'; ?>">Vendeurs</a> -->
                    <a href="<?php echo url('pos'); ?>" class="text-sm font-medium <?php echo $currentPage === 'pos' ? 'font-semibold text-[#8B4513] border-b-2 border-[#8B4513] pb-1' : 'text-[#706f6c] hover:text-[#8B4513] dark:text-[#A1A09A] dark:hover:text-[#FDFDFC]'; ?>">Vérification</a>
                    <a href="<?php echo url('sales'); ?>" class="text-sm font-medium <?php echo $currentPage === 'sales' ? 'font-semibold text-[#8B4513] border-b-2 border-[#8B4513] pb-1' : 'text-[#706f6c] hover:text-[#8B4513] dark:text-[#A1A09A] dark:hover:text-[#FDFDFC]'; ?>">Suivi des ventes</a>
                <?php else: ?>
                    <a href="<?php echo url('vendeur'); ?>" class="text-sm font-medium <?php echo $currentPage === 'vendeur' ? 'font-semibold text-[#8B4513] border-b-2 border-[#8B4513] pb-1' : 'text-[#706f6c] hover:text-[#8B4513] dark:text-[#A1A09A] dark:hover:text-[#FDFDFC]'; ?>">Tableau de bord</a>
                    <a href="<?php echo url('pos'); ?>" class="text-sm font-medium <?php echo $currentPage === 'pos' ? 'font-semibold text-[#8B4513] border-b-2 border-[#8B4513] pb-1' : 'text-[#706f6c] hover:text-[#8B4513] dark:text-[#A1A09A] dark:hover:text-[#FDFDFC]'; ?>">POS / Caisse</a>
                    <a href="<?php echo url('vendeur/historique'); ?>" class="text-sm font-medium <?php echo $currentPage === 'historique' ? 'font-semibold text-[#8B4513] border-b-2 border-[#8B4513] pb-1' : 'text-[#706f6c] hover:text-[#8B4513] dark:text-[#A1A09A] dark:hover:text-[#FDFDFC]'; ?>">Historique</a>
                <?php endif; ?>
                <!-- <a href="<?php echo url('profile'); ?>" class="text-sm font-medium <?php echo $currentPage === 'profile' ? 'font-semibold text-[#8B4513] border-b-2 border-[#8B4513] pb-1' : 'text-[#706f6c] hover:text-[#8B4513] dark:text-[#A1A09A] dark:hover:text-[#FDFDFC]'; ?>">Profil</a> -->
                <form method="POST" action="<?php echo url('logout'); ?>" class="inline">
                    <button type="submit" class="text-sm font-medium text-[#706f6c] hover:text-red-600 dark:text-[#A1A09A] dark:hover:text-red-400">Se déconnecter</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
