<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès interdit - Scandale Bouffe</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument+sans:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo url('css/tailwind.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('css/styles.css'); ?>">
</head>
<body class="min-h-screen bg-gradient-to-b from-[#FDFDFC] to-[#0a0a0a] text-[#1b1b18] flex items-center justify-center">
    <div class="max-w-md mx-auto px-4 text-center">
        <p class="text-8xl font-bold text-[#FF750F]">403</p>
        <h1 class="mt-4 text-2xl font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">
            Accès interdit
        </h1>
        <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]">
            Vous n'avez pas les droits nécessaires pour accéder à cette page.
        </p>
        <a href="<?php echo url(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'administrateur' ? 'dashboard' : 'vendeur'); ?>"
           class="mt-6 inline-block rounded-lg bg-[#8B4513] py-2 px-4 text-sm font-medium text-[#FDFDFC] hover:bg-[#A0522D] transition-colors">
            Retour à mon espace
        </a>
    </div>
</body>
</html>
