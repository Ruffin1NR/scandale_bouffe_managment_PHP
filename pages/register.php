<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

// Redirection si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: ' . url(''));
    exit;
}

// Traitement du formulaire d'inscription (POST from modal or standalone page)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['email'], $_POST['password'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $passwordConfirm = trim($_POST['password_confirm'] ?? '');

    if ($name === '' || $email === '' || $password === '') {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } elseif (strlen($password) < 4) {
        $error = "Le mot de passe doit contenir au moins 4 caractères.";
    } elseif ($password !== $passwordConfirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $existing = fetchAssoc("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            $error = "Un compte avec cet email existe déjà.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $pdo = $GLOBALS['pdo'];
            $pdo->beginTransaction();
            try {
                query("INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())", [$name, $email, $hashed]);
                $userId = $pdo->lastInsertId();
                $vendorRole = fetchAssoc("SELECT id FROM roles WHERE name = 'vendeur'");
                if ($vendorRole) {
                    query("INSERT INTO roles_users (user_id, role_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())", [$userId, $vendorRole['id']]);
                }
                $pdo->commit();
                $_SESSION['success'] = "Compte créé avec succès. Vous pouvez maintenant vous connecter.";
                header('Location: ' . url('?auth=login'));
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Erreur : " . $e->getMessage();
            }
        }
    }

    // On error, redirect back to modal with error
    $_SESSION['auth_error'] = $error;
    header('Location: ' . url('?auth=register'));
    exit;
}

// GET request — standalone register page no longer needed, redirect to modal
header('Location: ' . url('?auth=register'));
exit;
?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Scandale Bouffe</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument+sans:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Styles (Tailwind CSS - comme la version Laravel) -->
    <link rel="stylesheet" href="<?php echo url('css/tailwind.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('css/styles.css'); ?>">
</head>
<body class="min-h-screen bg-gradient-to-b from-[#FDFDFC] to-[#0a0a0a] text-[#1b1b18]">

    <?php require __DIR__ . '/partials/toast.php'; ?>

    <div class="flex min-h-screen items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <img src="https://scandalebouffe.netlify.app/images/scandale_logo.jpg" alt="Scandale Bouffe" class="mx-auto h-16 w-16 rounded-2xl object-cover shadow-card mb-4">
                <h1 class="text-2xl font-semibold text-[#8B4513]">Scandale Bouffe</h1>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mt-1">Creer un compte</p>
            </div>

            <div class="rounded-xl border border-[#e3e3e0] bg-[#FDFDFC] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] p-8 shadow-card" style="box-shadow: inset 0px 0px 0px 1px rgba(26,26,0,0.16), 0 25px 50px -12px rgba(0,0,0,0.25);">
                <?php if ($error): ?>
                <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 text-sm text-red-700 dark:text-red-400 flex items-center gap-2">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-3 text-sm text-green-700 dark:text-green-400 flex items-center gap-2">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo url('register'); ?>" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC] mb-2">Nom</label>
                        <input type="text" name="name" required class="w-full rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] px-4 py-2.5 text-sm dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#FDFDFC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:ring-1 focus:ring-[#8B4513]" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" placeholder="Votre nom">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC] mb-2">Email</label>
                        <input type="email" name="email" required class="w-full rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] px-4 py-2.5 text-sm dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#FDFDFC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:ring-1 focus:ring-[#8B4513]" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="vous@exemple.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC] mb-2">Mot de passe</label>
                        <input type="password" name="password" required class="w-full rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] px-4 py-2.5 text-sm dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#FDFDFC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:ring-1 focus:ring-[#8B4513]" placeholder="Mot de passe">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC] mb-2">Confirmer</label>
                        <input type="password" name="password_confirm" required class="w-full rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] px-4 py-2.5 text-sm dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#FDFDFC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:ring-1 focus:ring-[#8B4513]" placeholder="Confirmer">
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-[#8B4513] py-2.5 px-4 text-sm font-semibold text-[#FDFDFC] hover:bg-[#A0522D] transition shadow-sm">
                        S'inscrire
                    </button>
                </form>

                <p class="text-center text-sm mt-6 text-[#706f6c] dark:text-[#A1A09A]">
                    Deja un compte ? <a href="<?php echo url('login'); ?>" class="text-[#8B4513] font-medium hover:underline">Se connecter</a>
                </p>
            </div>
        </div>
    </div>
    <?php require __DIR__ . '/partials/back_to_top.php'; ?>
</body>
</html>