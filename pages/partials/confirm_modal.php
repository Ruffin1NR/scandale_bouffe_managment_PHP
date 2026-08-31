<div id="confirm-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" id="confirm-modal-backdrop"></div>
    <div class="relative w-full max-w-md rounded-lg border border-[#e3e3e0] bg-[#FDFDFC] dark:border-[#3E3E3A] dark:bg-[#0a0a0a] p-6 shadow-xl" style="box-shadow: inset 0px 0px 0px 1px rgba(26,26,0,0.16), 0 25px 50px -12px rgba(0,0,0,0.25);">
        <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-[#1b1b18] dark:text-[#FDFDFC]">Confirmation</h3>
                <p class="mt-2 text-sm text-[#706f6c] dark:text-[#A1A09A]" id="confirm-modal-message"></p>
            </div>
            <button type="button" class="text-[#A1A09A] hover:text-[#1b1b18] dark:hover:text-[#FDFDFC]" onclick="document.getElementById('confirm-modal').classList.add('hidden')">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" onclick="document.getElementById('confirm-modal').classList.add('hidden')" class="rounded-lg border border-[#e3e3e0] px-4 py-2 text-sm font-medium text-[#706f6c] transition hover:border-[#8B4513] hover:text-[#8B4513] dark:border-[#3E3E3A] dark:text-[#A1A09A]">Annuler</button>
            <button type="button" id="confirm-modal-ok" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-[#FDFDFC] transition hover:bg-red-700">Confirmer</button>
        </div>
    </div>
</div>

<script>
function showConfirmModal(message, onConfirm) {
    var modal = document.getElementById('confirm-modal');
    var msgEl = document.getElementById('confirm-modal-message');
    var okBtn = document.getElementById('confirm-modal-ok');
    msgEl.textContent = message;
    modal.classList.remove('hidden');
    okBtn.onclick = function() {
        modal.classList.add('hidden');
        if (onConfirm) onConfirm();
    };
}
</script>
