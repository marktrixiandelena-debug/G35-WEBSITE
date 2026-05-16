/**
 * User Account Management UI Logic
 * Handles interactive elements of the user management dashboard, including:
 * - Dynamic modal population for new accounts
 * - Algorithmic username suggestion based on full name
 * - Real-time username availability validation via AJAX
 */

// Declare variables at the top to avoid Temporal Dead Zone errors
let userEdited = false;
let checkTimeout = null;
let isUsernameValid = false;

const userModal = document.getElementById('userModal');
const fullNameInput = document.getElementById('full_name');
const usernameInput = document.getElementById('username');
const usernameStatus = document.getElementById('usernameStatus');
const submitUserBtn = document.getElementById('submitUserBtn');

function openUserModal() {
    // Reset form fields
    document.getElementById('full_name').value = '';
    document.getElementById('username').value = '';
    document.getElementById('role').value = '';
    document.getElementById('status').value = 'active';
    document.getElementById('contact_number').value = '';
    document.getElementById('address').value = '';
    document.getElementById('require_change').checked = true;

    clearStatus();
    userEdited = false;

    userModal.style.display = 'flex';
}

function closeUserModal() {
    userModal.style.display = 'none';
}

// Close when clicking outside modal
window.addEventListener('click', function (event) {
    if (event.target == userModal) {
        closeUserModal();
    }
});

/* ---- USERNAME AUTO-SUGGEST FROM FULL NAME ---- */
function toUsername(name) {
    // "Juan Dela Cruz" -> "juan.dc"
    const parts = name.trim().toLowerCase().split(/\s+/);
    if (parts.length < 2) return parts[0] || '';
    const first = parts[0];
    const initials = parts.slice(1).map(p => p[0]).join('');
    return first + '.' + initials;
}

if (fullNameInput && usernameInput) {
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
}

/* ---- LIVE DUPLICATE CHECK ---- */
function checkUsername(val) {
    clearTimeout(checkTimeout);
    val = val.trim();
    if (!val) { clearStatus(); return; }

    usernameStatus.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="color: #6b7280;"></i>';
    submitUserBtn.disabled = true;

    checkTimeout = setTimeout(() => {
        fetch('check_username.php?username=' + encodeURIComponent(val))
            .then(r => r.json())
            .then(data => {
                if (data.available) {
                    usernameStatus.innerHTML = '<i class="fa-solid fa-circle-check" style="color: #16a34a;" title="Available"></i>';
                    isUsernameValid = true;
                    submitUserBtn.disabled = false;
                } else {
                    usernameStatus.innerHTML = '<i class="fa-solid fa-circle-xmark" style="color: #dc2626;" title="Taken"></i>';
                    isUsernameValid = false;
                    submitUserBtn.disabled = true;
                }
            })
            .catch(() => clearStatus());
    }, 450);
}

function clearStatus() {
    if (usernameStatus) usernameStatus.innerHTML = '';
    isUsernameValid = false;
    if (submitUserBtn) submitUserBtn.disabled = false;
}
