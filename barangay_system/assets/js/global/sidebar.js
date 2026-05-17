// Sidebar Interaction Logic
// Handles toggling both the desktop collapsed state and mobile drawer overlay.

document.getElementById('sidebarToggle').addEventListener('click', function (e) {
    if (window.innerWidth <= 768) {
        // Toggle mobile drawer
        document.body.classList.toggle('mobile-open');
        e.stopPropagation();
    } else {
        // Toggle desktop collapsed mode
        document.body.classList.toggle('collapsed');
    }
});

// Close mobile sidebar when clicking outside (on the overlay)
document.addEventListener('click', function (e) {
    if (window.innerWidth <= 768 && document.body.classList.contains('mobile-open')) {
        const sidebar = document.querySelector('.sidebar');
        const toggle = document.getElementById('sidebarToggle');
        
        // Ensure click is outside sidebar and toggle button
        if (sidebar && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
            document.body.classList.remove('mobile-open');
        }
    }
});

