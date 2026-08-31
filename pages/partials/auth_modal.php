<?php
$authView = $_GET['auth'] ?? '';
$authError = $_SESSION['auth_error'] ?? $_GET['error'] ?? '';
unset($_SESSION['auth_error']);
$authSuccess = $_SESSION['auth_success'] ?? $_GET['success'] ?? '';
unset($_SESSION['auth_success']);
?>
<div id="auth-modal" style="display:none; position:fixed; inset:0; z-index:9999; align-items:center; justify-content:center; padding:1rem;">
    <div style="position:absolute; inset:0; background:rgba(0,0,0,0.5);" onclick="closeAuthModal()"></div>
    <div style="position:relative; width:100%; max-width:28rem; overflow:hidden; border-radius:0.5rem; background:white; box-shadow: inset 0px 0px 0px 1px rgba(26,26,0,0.16), 0 25px 50px -12px rgba(0,0,0,0.25);">
        <button type="button" onclick="closeAuthModal()" class="absolute top-4 right-4 z-10 text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#FDFDFC] transition">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="bg-[#FDFDFC] p-6 dark:bg-[#0a0a0a] sm:p-8">
            <div class="mb-6 flex items-center gap-3">
                <img src="https://scandalebouffe.netlify.app/images/scandale_logo.jpg" alt="Scandale Bouffe" class="h-10 w-10 rounded-lg object-cover">
                <div>
                    <h2 id="auth-modal-title" class="text-xl font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Connexion</h2>
                    <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Scandale Bouffe</p>
                </div>
            </div>
            <div class="mb-6 grid grid-cols-2 gap-1 rounded-lg bg-[#e3e3e0] p-1 dark:bg-[#3E3E3A]">
                <button type="button" onclick="switchAuthView('login')" id="auth-tab-login" class="auth-tab rounded-md px-4 py-2 text-sm font-medium transition bg-[#FDFDFC] text-[#8B4513] shadow dark:bg-[#0a0a0a]">Connexion</button>
                <button type="button" onclick="switchAuthView('register')" id="auth-tab-register" class="auth-tab rounded-md px-4 py-2 text-sm font-medium transition text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#FDFDFC]">Inscription</button>
            </div>
            <div id="auth-error" class="hidden mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-3 text-sm text-red-700 dark:text-red-400">
                <span id="auth-error-text"></span>
            </div>
            <form id="auth-login-form" method="POST" action="<?php echo url('login'); ?>" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC] mb-1">Email</label>
                    <input type="email" name="email" required autofocus autocomplete="username"
                           class="w-full rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] placeholder-[#A1A09A] focus:border-[#8B4513] focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#FDFDFC]"
                           placeholder="admin@scandalebouffe.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC] mb-1">Mot de passe</label>
                    <input type="password" name="password" required autocomplete="current-password"
                           class="w-full rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] placeholder-[#A1A09A] focus:border-[#8B4513] focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#FDFDFC]"
                           placeholder="Mot de passe">
                </div>
                <div class="flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-[#8B4513] px-4 py-2 text-sm font-semibold text-[#FDFDFC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] transition hover:bg-[#A0522D]">Connexion</button>
                </div>
            </form>
            <form id="auth-register-form" method="POST" action="<?php echo url('register'); ?>" class="space-y-4 hidden">
                <div>
                    <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC] mb-1">Nom</label>
                    <input type="text" name="name" required autocomplete="name"
                           class="w-full rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] placeholder-[#A1A09A] focus:border-[#8B4513] focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#FDFDFC]"
                           placeholder="Votre nom">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC] mb-1">Email</label>
                    <input type="email" name="email" required autocomplete="username"
                           class="w-full rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] placeholder-[#A1A09A] focus:border-[#8B4513] focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#FDFDFC]"
                           placeholder="vous@exemple.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC] mb-1">Mot de passe</label>
                    <input type="password" name="password" required autocomplete="new-password"
                           class="w-full rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] placeholder-[#A1A09A] focus:border-[#8B4513] focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#FDFDFC]"
                           placeholder="Mot de passe">
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1b1b18] dark:text-[#FDFDFC] mb-1">Confirmer</label>
                    <input type="password" name="password_confirm" required autocomplete="new-password"
                           class="w-full rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] placeholder-[#A1A09A] focus:border-[#8B4513] focus:ring-[#8B4513] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] dark:text-[#FDFDFC]"
                           placeholder="Confirmer le mot de passe">
                </div>
                <div class="flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-[#8B4513] px-4 py-2 text-sm font-semibold text-[#FDFDFC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] transition hover:bg-[#A0522D]">Inscription</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function openAuthModal(view) {
    var modal = document.getElementById('auth-modal');
    modal.style.display = 'flex';
    switchAuthView(view || 'login');
}
function closeAuthModal() {
    var modal = document.getElementById('auth-modal');
    modal.style.display = 'none';
}
function switchAuthView(view) {
    var loginForm = document.getElementById('auth-login-form');
    var registerForm = document.getElementById('auth-register-form');
    var title = document.getElementById('auth-modal-title');
    var tabLogin = document.getElementById('auth-tab-login');
    var tabRegister = document.getElementById('auth-tab-register');
    if (view === 'register') {
        loginForm.classList.add('hidden');
        registerForm.classList.remove('hidden');
        title.textContent = 'Inscription';
        tabRegister.className = 'auth-tab rounded-md px-4 py-2 text-sm font-medium transition bg-[#FDFDFC] text-[#8B4513] shadow dark:bg-[#0a0a0a]';
        tabLogin.className = 'auth-tab rounded-md px-4 py-2 text-sm font-medium transition text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#FDFDFC]';
    } else {
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');
        title.textContent = 'Connexion';
        tabLogin.className = 'auth-tab rounded-md px-4 py-2 text-sm font-medium transition bg-[#FDFDFC] text-[#8B4513] shadow dark:bg-[#0a0a0a]';
        tabRegister.className = 'auth-tab rounded-md px-4 py-2 text-sm font-medium transition text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#FDFDFC]';
    }
}
(function() {
    var params = new URLSearchParams(window.location.search);
    var auth = params.get('auth');
    if (auth === 'login' || auth === 'register') {
        openAuthModal(auth);
        window.history.replaceState({}, '', '/');
    }
    <?php if (!empty($authError)): ?>
    openAuthModal('login');
    document.getElementById('auth-error').classList.remove('hidden');
    document.getElementById('auth-error-text').textContent = <?php echo json_encode($authError); ?>;
    <?php endif; ?>
    <?php if (!empty($authSuccess)): ?>
    openAuthModal('login');
    var errEl = document.getElementById('auth-error');
    errEl.classList.remove('hidden');
    errEl.className = 'mb-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-3 text-sm text-green-700 dark:text-green-400';
    document.getElementById('auth-error-text').textContent = <?php echo json_encode($authSuccess); ?>;
    <?php endif; ?>
})();
</script>
