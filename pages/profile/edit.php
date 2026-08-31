<?php
$currentPage = 'profile';
$pageTitle = 'Profil';

require __DIR__ . '/../layout.php';

$user = fetchAssoc("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
if (!$user) { header('Location: ' . url('')); exit; }

require __DIR__ . '/../partials/confirm_modal.php';
?>

<div class="py-12">
    <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

        <div class="rounded-lg bg-white p-4 shadow sm:rounded-lg sm:p-8">
            <section class="max-w-xl">
                <header>
                    <h2 class="text-lg font-medium text-gray-900">Informations du compte</h2>
                    <p class="mt-1 text-sm text-gray-600">Mettez à jour les informations de profil et l'adresse e-mail de votre compte.</p>
                </header>
                <form method="POST" action="<?php echo url('profile/update'); ?>" class="mt-6 space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Nom</label>
                        <input id="name" name="name" type="text" value="<?php echo htmlspecialchars($user['name']); ?>" required autocomplete="name"
                               class="mt-1 block w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Email</label>
                        <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($user['email']); ?>" required autocomplete="username"
                               class="mt-1 block w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
                    </div>
                    <div class="flex items-center gap-4">
                        <button type="submit" class="rounded-lg bg-[#8B4513] px-5 py-2.5 text-sm font-medium text-[#FDFDFC] hover:bg-[#A0522D] transition">Enregistrer</button>
                    </div>
                </form>
            </section>
        </div>

        <div class="rounded-lg bg-white p-4 shadow sm:rounded-lg sm:p-8">
            <section class="max-w-xl">
                <header>
                    <h2 class="text-lg font-medium text-gray-900">Changer le mot de passe</h2>
                    <p class="mt-1 text-sm text-gray-600">Assurez-vous que votre compte utilise un mot de passe long et aléatoire pour rester sécurisé.</p>
                </header>
                <form method="POST" action="<?php echo url('profile/password'); ?>" class="mt-6 space-y-6">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Mot de passe actuel</label>
                        <input id="current_password" name="current_password" type="password" required autocomplete="current-password"
                               class="mt-1 block w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Nouveau mot de passe</label>
                        <input id="password" name="password" type="password" required minlength="6" autocomplete="new-password"
                               class="mt-1 block w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-[#706f6c] dark:text-[#A1A09A]">Confirmer le mot de passe</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="6" autocomplete="new-password"
                               class="mt-1 block w-full rounded-lg border border-[#e3e3e0] bg-white px-3 py-2 text-sm shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] focus:border-[#8B4513] focus:ring-1 focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#FDFDFC]">
                    </div>
                    <div class="flex items-center gap-4">
                        <button type="submit" class="rounded-lg bg-[#8B4513] px-5 py-2.5 text-sm font-medium text-[#FDFDFC] hover:bg-[#A0522D] transition">Enregistrer</button>
                    </div>
                </form>
            </section>
        </div>

        <div class="rounded-lg bg-white p-4 shadow sm:rounded-lg sm:p-8">
            <section class="max-w-xl space-y-6">
                <header>
                    <h2 class="text-lg font-medium text-gray-900">Supprimer le compte</h2>
                    <p class="mt-1 text-sm text-gray-600">Une fois votre compte supprimé, toutes ses ressources et ses données seront définitivement effacées. Avant de supprimer votre compte, veuillez télécharger toutes les données ou informations que vous souhaitez conserver.</p>
                </header>
                <button type="button"
                        onclick="showConfirmModal('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible. Veuillez saisir votre mot de passe pour confirmer la suppression définitive de votre compte.', function() { document.getElementById('delete-account-form').submit(); })"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-[#FDFDFC] transition hover:bg-red-700">
                    Supprimer le compte
                </button>
                <form id="delete-account-form" method="POST" action="<?php echo url('profile/delete'); ?>" class="hidden"></form>
            </section>
        </div>

    </div>
</div>

<?php require __DIR__ . '/../layout_footer.php'; ?>
