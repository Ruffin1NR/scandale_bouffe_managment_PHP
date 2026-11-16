<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau vendeur - Scandale Bouffe</title>
    <link rel="stylesheet" href="<?php echo url('css/fonts.css'); ?>">

    <!-- Styles (Tailwind CSS - comme la version Laravel) -->
    <link rel="stylesheet" href="<?php echo url('css/tailwind.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('css/styles.css'); ?>">
</head>
<body class="min-h-screen bg-gradient-to-b from-[#FDFDFC] to-[#0a0a0a] text-[#1b1b18]">

    <?php require __DIR__ . '/../../auth/check.php'; ?>
    <?php require __DIR__ . '/../partials/toast.php'; ?>

    <nav class="sticky top-0 z-40 border-b border-[#e3e3e0] dark:border-[#3E3E3A] bg-[#FDFDFC] dark:bg-[#0a0a0a]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="<?php echo url('public/scandale_logo.jpg'); ?>" alt="Scandale Bouffe" class="h-9 w-9 rounded-lg object-cover">
                    <a href="<?php echo url('dashboard'); ?>" class="text-lg font-semibold text-[#8B4513]">Scandale Bouffe</a>
                </div>
                <button type="button" id="nav-toggle" aria-label="Menu" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-[#e3e3e0] text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] sm:hidden dark:border-[#3E3E3A] dark:text-[#A1A09A]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
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
        <div id="mobile-menu" class="hidden border-t border-[#e3e3e0] sm:hidden dark:border-[#3E3E3A]">
            <div class="space-y-1 px-4 py-3">
                <a href="<?php echo url('inventory'); ?>" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Inventaire</a>
                <a href="<?php echo url('vendeurs'); ?>" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-[#8B4513]">Vendeurs</a>
                <a href="<?php echo url('sales'); ?>" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Ventes</a>
                <a href="<?php echo url('dashboard'); ?>" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Dashboard</a>
                <a href="<?php echo url('profile'); ?>" class="block rounded-lg px-3 py-2.5 text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Profil</a>
                <form method="POST" action="<?php echo url('logout'); ?>">
                    <button type="submit" class="block w-full rounded-lg px-3 py-2.5 text-left text-sm font-medium text-[#706f6c] hover:text-red-600 dark:text-[#A1A09A] dark:hover:text-red-400">Déconnexion</button>
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
        <div class="max-w-md mx-auto">
            <div class="rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] shadow-card p-6">
                <h2 class="mb-6 text-2xl font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">
                    Nouveau vendeur
                </h2>

                <form action="<?php echo url('vendeurs/create'); ?>" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Nom</label>
                        <input type="text" name="name" required
                            class="w-full input-field"
                            placeholder="Nom du vendeur">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Email</label>
                        <input type="email" name="email" required
                            class="w-full input-field"
                            placeholder="vendeur@exemple.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A] mb-2">Mot de passe</label>
                        <input type="password" name="password" required
                            class="w-full input-field"
                            placeholder="password">
                    </div>

                    <button type="submit"
                        class="w-full rounded-lg bg-[#8B4513] py-2 px-4 text-sm font-medium text-[#FDFDFC] hover:bg-[#A0522D] transition-colors">
                        Créer le vendeur
                    </button>

                    <a href="<?php echo url('vendeurs'); ?>"
                       class="mt-2 block text-sm text-[#8B4513] hover:underline">
                        Annuler
                    </a>
                </form>
            </div>
        </div>
    </main>

<footer class="border-t border-[#e3e3e0] dark:border-[#3E3E3A] mt-12 py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">&copy; <?php echo date('Y'); ?> Scandale Bouffe &mdash; Antsenakely, Antsirabe, Madagascar</p>
    </div>
</footer>

    <?php require __DIR__ . '/../partials/back_to_top.php'; ?>
    </body>
</html>