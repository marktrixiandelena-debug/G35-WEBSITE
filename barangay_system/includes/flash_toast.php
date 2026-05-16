<?php
// Flash Toast Notification Engine
// Retrieves and renders session-based status messages system-wide.

$flashes = [];

if (isset($_SESSION['flash_success'])) {
    $flashes[] = ['type' => 'success', 'icon' => 'fa-circle-check', 'msg' => $_SESSION['flash_success']];
    unset($_SESSION['flash_success']);
}
if (isset($_SESSION['flash_error'])) {
    $flashes[] = ['type' => 'error', 'icon' => 'fa-circle-exclamation', 'msg' => $_SESSION['flash_error']];
    unset($_SESSION['flash_error']);
}
if (isset($_SESSION['flash_warning'])) {
    $flashes[] = ['type' => 'warning', 'icon' => 'fa-triangle-exclamation', 'msg' => $_SESSION['flash_warning']];
    unset($_SESSION['flash_warning']);
}
if (isset($_SESSION['flash_info'])) {
    $flashes[] = ['type' => 'info', 'icon' => 'fa-circle-info', 'msg' => $_SESSION['flash_info']];
    unset($_SESSION['flash_info']);
}

if (!empty($flashes)):
?>
    <div id="toast-container" style="position: fixed; top: 1.5rem; right: 1.5rem; z-index: 10000; display: flex; flex-direction: column; gap: 0.75rem; pointer-events: none;">
        <?php foreach ($flashes as $index => $flash): ?>
            <div class="flash-toast flash-toast-<?php echo $flash['type']; ?> flash-toast-item" style="pointer-events: auto; animation-delay: <?php echo $index * 0.15; ?>s;">
                <i class="fa-solid <?php echo $flash['icon']; ?>"></i>
                <span><?php echo htmlspecialchars($flash['msg']); ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var toasts = document.querySelectorAll('.flash-toast-item');
            
            // Limit Stack Size
            if (toasts.length > 3) {
                for (let i = 0; i < toasts.length - 3; i++) {
                    toasts[i].remove();
                }
            }

            toasts.forEach(function(toast) {
                // Auto-Dismiss Routine
                setTimeout(function() {
                    toast.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-10px)';
                    setTimeout(function() {
                        if (toast.parentNode) {
                            toast.remove();
                        }
                    }, 500); // Wait for transition to finish
                }, 4000);
            });
        });
    </script>
<?php endif; ?>
