<?php
$stats = fetchAssoc("
    SELECT
        (SELECT COUNT(*) FROM products WHERE active = 1) as product_count,
        (SELECT COUNT(*) FROM users u JOIN roles_users ru ON ru.user_id = u.id JOIN roles r ON r.id = ru.role_id AND r.name = 'vendeur') as vendor_count,
        (SELECT COALESCE(SUM(total), 0) FROM sales) as total_sales
");

$isConnected = isset($_SESSION['user_id']);
$isAdmin = $isConnected && ($_SESSION['user_role'] ?? '') === 'administrateur';
$isVendor = $isConnected && ($_SESSION['user_role'] ?? '') === 'vendeur';
?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scandale Bouffe - Gestion de Stock</title>
    <link rel="stylesheet" href="<?php echo url('css/fonts.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('css/tailwind.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('css/styles.css'); ?>">
</head>
<body>
    <div class="relative flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] antialiased dark:bg-[#0a0a0a] dark:text-[#A1A09A]">

        <!-- Background animé -->
        <div class="pointer-events-none absolute inset-0 z-0 overflow-hidden">
            <div class="animate-drift absolute -top-24 -left-24 h-96 w-96 rounded-full bg-[#8B4513]/10 blur-3xl dark:bg-[#8B4513]/20"></div>
            <div class="animate-drift absolute top-1/3 -right-24 h-80 w-80 rounded-full bg-[#FF750F]/10 blur-3xl [animation-delay:-6s] dark:bg-[#FF750F]/15"></div>
            <div class="animate-drift absolute bottom-0 left-1/4 h-72 w-72 rounded-full bg-[#8B4513]/5 blur-3xl [animation-delay:-11s] dark:bg-[#8B4513]/10"></div>
        </div>

        <!-- Nav sticky -->
        <nav class="sticky top-0 z-50 border-b border-[#e3e3e0]/60 bg-[#FDFDFC]/70 backdrop-blur-xl dark:border-[#3E3E3A]/50 dark:bg-[#0a0a0a]/70">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <img src="<?php echo url('public/scandale_logo.jpg'); ?>" alt="Scandale Bouffe" class="h-10 w-10 rounded-lg object-cover">
                    <span class="text-lg font-semibold text-[#8B4513]">Scandale Bouffe</span>
                </div>

                <div class="hidden items-center gap-8 text-sm font-medium text-[#706f6c] md:flex dark:text-[#A1A09A]">
                    <a href="#accueil" class="transition hover:text-[#8B4513]">Accueil</a>
                    <a href="#fonctionnalites" class="transition hover:text-[#8B4513]">Fonctionnalités</a>
                    <a href="#apropos" class="transition hover:text-[#8B4513]">À propos</a>
                </div>

                <div class="flex items-center gap-4">
                    <?php if ($isConnected): ?>
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]"><?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></span>
                        <a href="<?php echo url($isAdmin ? 'dashboard' : 'vendeur'); ?>" class="rounded-lg bg-[#8B4513] px-4 py-2 text-sm font-medium text-[#FDFDFC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] transition hover:bg-[#A0522D]">
                            Tableau de bord
                        </a>
                        <form method="POST" action="<?php echo url('logout'); ?>" class="inline">
                            <button type="submit" class="text-sm text-[#706f6c] underline dark:text-[#A1A09A]">Déconnexion</button>
                        </form>
                    <?php else: ?>
                        <button type="button" onclick="openAuthModal('login')" class="text-sm text-[#706f6c] underline hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#FDFDFC]">Connexion</button>
                        <button type="button" onclick="openAuthModal('register')" class="rounded-lg bg-[#8B4513] px-4 py-2 text-sm font-medium text-[#FDFDFC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] transition hover:bg-[#A0522D]">S'inscrire</button>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <!-- Main -->
        <main class="relative z-10 flex flex-1 flex-col items-center justify-center px-6 py-20">

            <!-- HERO -->
            <section id="accueil" class="w-full max-w-2xl scroll-mt-24 text-center lg:max-w-4xl">
                <div class="animate-fade-up mb-10 flex justify-center">
                    <img src="<?php echo url('public/scandale_logo.jpg'); ?>" alt="Scandale Bouffe"
                         class="animate-float w-40 rounded-bl-lg rounded-br-lg rounded-t-lg object-cover shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:w-56">
                </div>

                <div class="space-y-4">
                    <span class="animate-fade-up inline-flex items-center rounded-full border border-[#8B4513]/30 bg-[#8B4513]/5 px-3 py-1 text-xs font-medium text-[#8B4513] dark:border-[#FF750F]/30 dark:bg-[#FF750F]/10 dark:text-[#FF750F]" style="animation-delay:0.1s">
                        Antsenakely, Antsirabe, Madagascar
                    </span>

                    <h1 class="animate-fade-up text-4xl font-bold tracking-tight text-[#1b1b18] dark:text-[#FDFDFC] sm:text-6xl" style="animation-delay:0.2s">
                        <span class="text-[#8B4513]">Scandale</span> Bouffe
                    </h1>

                    <p class="animate-fade-up mx-auto mt-4 max-w-xl text-lg text-[#706f6c] dark:text-[#A1A09A]" style="animation-delay:0.35s">
                        Système de gestion de stock pour votre fast-food. Suivez vos
                        produits, votre caisse et vos ventes en toute simplicité.
                    </p>

                    <div class="animate-fade-up flex flex-wrap items-center justify-center gap-3 pt-4" style="animation-delay:0.5s">
                        <?php if ($isConnected): ?>
                        <a href="<?php echo url($isAdmin ? 'dashboard' : 'vendeur'); ?>"
                           class="rounded-lg bg-[#8B4513] px-6 py-3 text-sm font-medium text-[#FDFDFC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] transition duration-200 hover:-translate-y-0.5 hover:bg-[#A0522D]">
                            Accéder à mon espace
                        </a>
                        <?php else: ?>
                        <button type="button" onclick="openAuthModal('login')"
                           class="rounded-lg bg-[#8B4513] px-6 py-3 text-sm font-medium text-[#FDFDFC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] transition duration-200 hover:-translate-y-0.5 hover:bg-[#A0522D]">
                            Accéder à mon espace
                        </button>
                        <button type="button" onclick="openAuthModal('register')"
                           class="rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] px-6 py-3 text-sm font-medium text-[#706f6c] transition duration-200 hover:-translate-y-0.5 hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#A1A09A]">
                            Créer un compte
                        </button>
                        <?php endif; ?>
                    </div>

                    <div class="animate-fade-up mx-auto mt-10 grid max-w-xl grid-cols-1 gap-4 text-center sm:grid-cols-3" style="animation-delay:0.65s">
                        <div class="rounded-lg border border-[#e3e3e0]/70 bg-[#FDFDFC]/60 px-3 py-4 dark:border-[#3E3E3A]/70 dark:bg-[#0a0a0a]/40">
                            <p class="text-2xl font-bold text-[#8B4513]"><?php echo (int)($stats['product_count'] ?? 0); ?>+</p>
                            <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">Produits gérés</p>
                        </div>
                        <div class="rounded-lg border border-[#e3e3e0]/70 bg-[#FDFDFC]/60 px-3 py-4 dark:border-[#3E3E3A]/70 dark:bg-[#0a0a0a]/40">
                            <p class="text-2xl font-bold text-[#8B4513]">2</p>
                            <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">Profils d'accès</p>
                        </div>
                        <div class="rounded-lg border border-[#e3e3e0]/70 bg-[#FDFDFC]/60 px-3 py-4 dark:border-[#3E3E3A]/70 dark:bg-[#0a0a0a]/40">
                            <p class="text-2xl font-bold text-[#8B4513]">24/7</p>
                            <p class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]">Suivi des ventes</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FONCTIONNALITÉS -->
            <section id="fonctionnalites" class="mt-24 w-full max-w-4xl scroll-mt-24">
                <div class="animate-fade-up mb-8 text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-[#1b1b18] dark:text-[#FDFDFC]">Fonctionnalités</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-[#706f6c] dark:text-[#A1A09A]">
                        Tout ce qu'il faut pour piloter votre fast-food : stock, caisse et
                        ventes réunis dans une seule application.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <a href="<?php echo url('inventory'); ?>"
                       class="animate-fade-up group rounded-lg border border-[#e3e3e0] bg-[#FDFDFC]/80 p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] backdrop-blur transition duration-200 hover:-translate-y-1 hover:border-[#8B4513] hover:shadow-[inset_0px_0px_0px_1px_rgba(139,69,19,0.4)] dark:border-[#3E3E3A] dark:bg-[#0a0a0a]/60"
                       style="animation-delay:0.15s">
                        <h2 class="text-lg font-semibold text-[#8B4513]">Inventaire</h2>
                        <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            Consultez et gérez les produits, les quantités et les alertes de stock.
                        </p>
                        <span class="mt-4 inline-block text-sm font-medium text-[#8B4513] opacity-0 transition group-hover:opacity-100">
                            Découvrir →
                        </span>
                    </a>

                    <?php if ($isConnected): ?>
                    <a href="<?php echo url($isAdmin ? 'dashboard' : 'vendeur'); ?>"
                       class="animate-fade-up group rounded-lg border border-[#e3e3e0] bg-[#FDFDFC]/80 p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] backdrop-blur transition duration-200 hover:-translate-y-1 hover:border-[#8B4513] hover:shadow-[inset_0px_0px_0px_1px_rgba(139,69,19,0.4)] dark:border-[#3E3E3A] dark:bg-[#0a0a0a]/60"
                       style="animation-delay:0.3s">
                    <?php else: ?>
                    <button type="button" onclick="openAuthModal('login')"
                       class="animate-fade-up group rounded-lg border border-[#e3e3e0] bg-[#FDFDFC]/80 p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] backdrop-blur transition duration-200 hover:-translate-y-1 hover:border-[#8B4513] hover:shadow-[inset_0px_0px_0px_1px_rgba(139,69,19,0.4)] dark:border-[#3E3E3A] dark:bg-[#0a0a0a]/60"
                       style="animation-delay:0.3s">
                    <?php endif; ?>
                        <h2 class="text-lg font-semibold text-[#8B4513]">Tableau de bord</h2>
                        <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            Vue d'ensemble de votre activité et accès rapide à vos outils.
                        </p>
                        <span class="mt-4 inline-block text-sm font-medium text-[#8B4513] opacity-0 transition group-hover:opacity-100">
                            Découvrir →
                        </span>
                    <?php if ($isConnected): ?>
                    </a>
                    <?php else: ?>
                    </button>
                    <?php endif; ?>

                    <?php if ($isConnected): ?>
                    <a href="<?php echo url($isAdmin ? 'sales' : 'pos'); ?>"
                       class="animate-fade-up group rounded-lg border border-[#e3e3e0] bg-[#FDFDFC]/80 p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] backdrop-blur transition duration-200 hover:-translate-y-1 hover:border-[#8B4513] hover:shadow-[inset_0px_0px_0px_1px_rgba(139,69,19,0.4)] dark:border-[#3E3E3A] dark:bg-[#0a0a0a]/60"
                       style="animation-delay:0.45s">
                    <?php else: ?>
                    <button type="button" onclick="openAuthModal('login')"
                       class="animate-fade-up group rounded-lg border border-[#e3e3e0] bg-[#FDFDFC]/80 p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] backdrop-blur transition duration-200 hover:-translate-y-1 hover:border-[#8B4513] hover:shadow-[inset_0px_0px_0px_1px_rgba(139,69,19,0.4)] dark:border-[#3E3E3A] dark:bg-[#0a0a0a]/60"
                       style="animation-delay:0.45s">
                    <?php endif; ?>
                        <h2 class="text-lg font-semibold text-[#8B4513]">Suivi des ventes</h2>
                        <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            Caisse, historique et résumé des ventes par période et par produit.
                        </p>
                        <span class="mt-4 inline-block text-sm font-medium text-[#8B4513] opacity-0 transition group-hover:opacity-100">
                            Découvrir →
                        </span>
                    <?php if ($isConnected): ?>
                    </a>
                    <?php else: ?>
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Checklist features -->
                <div class="animate-fade-up mt-6 grid gap-4 sm:grid-cols-2" style="animation-delay:0.6s">
                    <div class="flex items-start gap-3 rounded-lg border border-[#e3e3e0]/70 bg-[#FDFDFC]/60 px-4 py-3 dark:border-[#3E3E3A]/70 dark:bg-[#0a0a0a]/40">
                        <span class="mt-0.5 text-[#8B4513]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Caisse enregistreuse (POS) pour les vendeurs</p>
                    </div>
                    <div class="flex items-start gap-3 rounded-lg border border-[#e3e3e0]/70 bg-[#FDFDFC]/60 px-4 py-3 dark:border-[#3E3E3A]/70 dark:bg-[#0a0a0a]/40">
                        <span class="mt-0.5 text-[#8B4513]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Alertes automatiques de stock bas</p>
                    </div>
                    <div class="flex items-start gap-3 rounded-lg border border-[#e3e3e0]/70 bg-[#FDFDFC]/60 px-4 py-3 dark:border-[#3E3E3A]/70 dark:bg-[#0a0a0a]/40">
                        <span class="mt-0.5 text-[#8B4513]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Catégories de produits (ingrédients, boissons, desserts…)</p>
                    </div>
                    <div class="flex items-start gap-3 rounded-lg border border-[#e3e3e0]/70 bg-[#FDFDFC]/60 px-4 py-3 dark:border-[#3E3E3A]/70 dark:bg-[#0a0a0a]/40">
                        <span class="mt-0.5 text-[#8B4513]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Historique des ventes par jour, catégorie et produit</p>
                    </div>
                    <div class="flex items-start gap-3 rounded-lg border border-[#e3e3e0]/70 bg-[#FDFDFC]/60 px-4 py-3 dark:border-[#3E3E3A]/70 dark:bg-[#0a0a0a]/40">
                        <span class="mt-0.5 text-[#8B4513]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Contrôle d'accès par rôle (administrateur / vendeur)</p>
                    </div>
                    <div class="flex items-start gap-3 rounded-lg border border-[#e3e3e0]/70 bg-[#FDFDFC]/60 px-4 py-3 dark:border-[#3E3E3A]/70 dark:bg-[#0a0a0a]/40">
                        <span class="mt-0.5 text-[#8B4513]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Prix en Ariary (Ar), unités et stock minimal configurables</p>
                    </div>
                </div>
            </section>

            <!-- À PROPOS -->
            <section id="apropos" class="mt-24 w-full max-w-4xl scroll-mt-24">
                <div class="animate-fade-up rounded-lg border border-[#e3e3e0] bg-[#FDFDFC]/80 p-8 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] backdrop-blur dark:border-[#3E3E3A] dark:bg-[#0a0a0a]/60 sm:p-10">
                    <div class="grid gap-8 sm:grid-cols-2">
                        <div>
                            <h2 class="text-3xl font-bold tracking-tight text-[#1b1b18] dark:text-[#FDFDFC]">À propos</h2>
                            <p class="mt-4 text-sm leading-relaxed text-[#706f6c] dark:text-[#A1A09A]">
                                <span class="font-semibold text-[#8B4513]">Scandale Bouffe</span> est un
                                fast-food situé à Antsenakely, Antsirabe, Madagascar. Cette application a été
                                conçue pour simplifier la gestion quotidienne : un œil sur le stock, une caisse
                                efficace et un suivi clair des ventes pour prendre de meilleures décisions.
                            </p>
                            <p class="mt-3 text-sm leading-relaxed text-[#706f6c] dark:text-[#A1A09A]">
                                L'administrateur pilote l'inventaire et les rapports ; le vendeur encaisse les
                                commandes en quelques clics grâce à la caisse intégrée.
                            </p>
                        </div>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 text-[#8B4513]">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </span>
                                <p class="text-[#706f6c] dark:text-[#A1A09A]">Antsenakely, Antsirabe, Madagascar</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 text-[#8B4513]">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </span>
                                <p class="text-[#706f6c] dark:text-[#A1A09A]">Gestion de stock, caisse et ventes en temps réel</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 text-[#8B4513]">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </span>
                                <p class="text-[#706f6c] dark:text-[#A1A09A]">Deux profils : administrateur & vendeur</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <?php include __DIR__ . '/partials/auth_modal.php'; ?>
    <?php require __DIR__ . '/partials/back_to_top.php'; ?>
</body>
</html>
