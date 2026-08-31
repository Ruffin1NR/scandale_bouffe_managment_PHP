<?php if(isset($_SESSION['success'])): ?>
<div class="pointer-events-none fixed inset-x-0 top-4 z-[100] flex justify-center px-4" id="toast-container">
    <div class="pointer-events-auto flex max-w-md items-center gap-3 rounded-lg border border-[#8B4513]/30 bg-[#8B4513] px-4 py-3 text-[#FDFDFC] shadow-lg toast-item">
        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <p class="text-sm font-medium"><?php echo htmlspecialchars($_SESSION['success']); ?></p>
    </div>
</div>
<?php unset($_SESSION['success']); endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<div class="pointer-events-none fixed inset-x-0 top-4 z-[100] flex justify-center px-4">
    <div class="pointer-events-auto flex max-w-md items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 shadow-lg toast-item">
        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <p class="text-sm font-medium"><?php echo htmlspecialchars($_SESSION['error']); ?></p>
    </div>
</div>
<?php unset($_SESSION['error']); endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toast-item').forEach(function(toast) {
        setTimeout(function() {
            toast.style.transition = 'opacity 0.3s ease-out';
            toast.style.opacity = '0';
            setTimeout(function() { toast.parentElement.remove(); }, 300);
        }, 4000);
    });
});
</script>
