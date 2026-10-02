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

<nav class="sticky top-0 z-40 border-b border-[#e3e3e0] dark:border-[#3E3E3A] bg-[#FDFDFC] dark:bg-[#0a0a0a]">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="https://scandalebouffe.netlify.app/images/scandale_logo.jpg" alt="Scandale Bouffe" class="h-9 w-9 rounded-lg object-cover">
                <a href="<?php echo url($isAdmin ? 'dashboard' : 'vendeur'); ?>" class="text-lg font-semibold text-[#8B4513]">Scandale Bouffe</a>
            </div>

            <button type="button" id="nav-toggle" aria-label="Menu" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-[#e3e3e0] text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] sm:hidden dark:border-[#3E3E3A] dark:text-[#A1A09A]">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

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
    <div id="mobile-menu" class="hidden border-t border-[#e3e3e0] sm:hidden dark:border-[#3E3E3A]">
        <div class="space-y-1 px-4 py-3">
            <?php if ($isAdmin): ?>
                <a href="<?php echo url('dashboard'); ?>" class="block rounded-lg px-3 py-2.5 text-sm font-medium <?php echo $currentPage === 'dashboard' ? 'text-[#8B4513]' : 'text-[#706f6c] dark:text-[#A1A09A]'; ?>">Tableau de bord</a>
                <a href="<?php echo url('categories'); ?>" class="block rounded-lg px-3 py-2.5 text-sm font-medium <?php echo $currentPage === 'categories' ? 'text-[#8B4513]' : 'text-[#706f6c] dark:text-[#A1A09A]'; ?>">Catégories</a>
                <a href="<?php echo url('inventory'); ?>" class="block rounded-lg px-3 py-2.5 text-sm font-medium <?php echo $currentPage === 'inventory' ? 'text-[#8B4513]' : 'text-[#706f6c] dark:text-[#A1A09A]'; ?>">Inventaire</a>
                <a href="<?php echo url('pos'); ?>" class="block rounded-lg px-3 py-2.5 text-sm font-medium <?php echo $currentPage === 'pos' ? 'text-[#8B4513]' : 'text-[#706f6c] dark:text-[#A1A09A]'; ?>">Vérification</a>
                <a href="<?php echo url('sales'); ?>" class="block rounded-lg px-3 py-2.5 text-sm font-medium <?php echo $currentPage === 'sales' ? 'text-[#8B4513]' : 'text-[#706f6c] dark:text-[#A1A09A]'; ?>">Suivi des ventes</a>
            <?php else: ?>
                <a href="<?php echo url('vendeur'); ?>" class="block rounded-lg px-3 py-2.5 text-sm font-medium <?php echo $currentPage === 'vendeur' ? 'text-[#8B4513]' : 'text-[#706f6c] dark:text-[#A1A09A]'; ?>">Tableau de bord</a>
                <a href="<?php echo url('pos'); ?>" class="block rounded-lg px-3 py-2.5 text-sm font-medium <?php echo $currentPage === 'pos' ? 'text-[#8B4513]' : 'text-[#706f6c] dark:text-[#A1A09A]'; ?>">POS / Caisse</a>
                <a href="<?php echo url('vendeur/historique'); ?>" class="block rounded-lg px-3 py-2.5 text-sm font-medium <?php echo $currentPage === 'historique' ? 'text-[#8B4513]' : 'text-[#706f6c] dark:text-[#A1A09A]'; ?>">Historique</a>
            <?php endif; ?>
            <form method="POST" action="<?php echo url('logout'); ?>">
                <button type="submit" class="block w-full rounded-lg px-3 py-2.5 text-left text-sm font-medium text-[#706f6c] hover:text-red-600 dark:text-[#A1A09A] dark:hover:text-red-400">Se déconnecter</button>
            </form>
        </div>
    </div>
</nav>
<script>
(function() {
    var toggle = document.getElementById('nav-toggle');
    var menu = document.getElementById('mobile-menu');
    if (toggle && menu) {
        toggle.addEventListener('click', function() { menu.classList.toggle('hidden'); });
    }
})();
</script>

<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
