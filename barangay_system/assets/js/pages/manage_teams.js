/**
 * Response Team Management UI Interactions
 * Manages the activation and state of modals for creating and updating emergency response units.
 * Includes event listeners for keyboard accessibility and outside-click dismissal.
 */

const teamModal     = document.getElementById('teamModal');
const editTeamModal = document.getElementById('editTeamModal');

/* ---- Create modal ---- */
function openTeamModal() {
    teamModal.style.display = 'flex';
}

function closeTeamModal() {
    teamModal.style.display = 'none';
}

/* ---- Edit modal ---- */
function openEditModal(id, name, leader, contact, status) {
    document.getElementById('editTeamId').value      = id;
    document.getElementById('editTeamName').value    = name;
    document.getElementById('editTeamLeader').value  = leader;
    document.getElementById('editTeamContact').value = contact;

    const sel = document.getElementById('editTeamStatus');
    for (let opt of sel.options) {
        opt.selected = (opt.value === status);
    }

    editTeamModal.style.display = 'flex';
}

function closeEditModal() {
    editTeamModal.style.display = 'none';
}

/* ---- Close on outside click ---- */
window.addEventListener('click', function (e) {
    if (e.target === teamModal)     closeTeamModal();
    if (e.target === editTeamModal) closeEditModal();
});

/* ---- Close on Escape ---- */
window.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeTeamModal();
        closeEditModal();
    }
});
