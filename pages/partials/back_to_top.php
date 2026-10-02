<?php if (!defined('BACK_TO_TOP_INCLUDED')): ?>
<?php define('BACK_TO_TOP_INCLUDED', true); ?>
<button type="button" id="back-to-top" aria-label="Revenir en haut de la page" title="Revenir en haut"
    class="fixed bottom-6 right-6 z-40 hidden h-11 w-11 items-center justify-center rounded-full bg-[#8B4513] text-[#FDFDFC] shadow-lg transition hover:bg-[#A0522D]">
    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
</button>
<script>
(function() {
    var btn = document.getElementById('back-to-top');
    if (!btn) return;
    function toggle() {
        if (window.scrollY > 400) {
            btn.classList.remove('hidden');
            btn.classList.add('flex');
        } else {
            btn.classList.add('hidden');
            btn.classList.remove('flex');
        }
    }
    window.addEventListener('scroll', toggle, { passive: true });
    toggle();
    btn.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();
</script>
<?php endif; ?>
