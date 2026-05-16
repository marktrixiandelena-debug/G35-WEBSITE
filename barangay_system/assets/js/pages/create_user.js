/**
 * Account Creation Interaction Logic
 * Provides real-time feedback and automation for the user registration/creation form.
 * Includes auto-suggestion for usernames and AJAX-based duplicate checking.
 */

/* ---- USERNAME AUTO-SUGGEST FROM FULL NAME ---- */
const fullNameInput = document.getElementById('full_name');
const usernameInput = document.getElementById('username');
const usernameStatus = document.getElementById('usernameStatus');
let userEdited = false;
let checkTimeout = null;

function toUsername(name) {
    // "Juan Dela Cruz" → "juan.dc"
    const parts = name.trim().toLowerCase().split(/\s+/);
    if (parts.length < 2) return parts[0] || '';
    const first = parts[0];
    const initials = parts.slice(1).map(p => p[0]).join('');
    return first + '.' + initials;
}

fullNameInput.addEventListener('input', function () {
    if (!userEdited) {
        const suggested = toUsername(this.value);
        usernameInput.value = suggested;
        if (suggested) checkUsername(suggested);
        else clearStatus();
    }
});

usernameInput.addEventListener('input', function () {
    userEdited = true;
    checkUsername(this.value);
});

usernameInput.addEventListener('keydown', function () {
    if (this.value === '') userEdited = false;
});

/* ---- LIVE DUPLICATE CHECK ---- */
function checkUsername(val) {
    clearTimeout(checkTimeout);
    val = val.trim();
    if (!val) { clearStatus(); return; }

    usernameStatus.className = 'username-status checking';
    usernameStatus.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="font-size:0.75rem"></i> Checking...';

    checkTimeout = setTimeout(() => {
        fetch('check_username.php?username=' + encodeURIComponent(val))
            .then(r => r.json())
            .then(data => {
                if (data.available) {
                    usernameStatus.className = 'username-status available';
                    usernameStatus.innerHTML = '<i class="fa-solid fa-circle-check" style="font-size:0.75rem"></i> ' + data.message;
                } else {
                    usernameStatus.className = 'username-status taken';
                    usernameStatus.innerHTML = '<i class="fa-solid fa-circle-xmark" style="font-size:0.75rem"></i> ' + data.message;
                }
            })
            .catch(() => clearStatus());
    }, 450);
}

function clearStatus() {
    usernameStatus.className = 'username-status';
    usernameStatus.innerHTML = '';
}
