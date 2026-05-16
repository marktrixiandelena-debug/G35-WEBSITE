/**
 * Announcement Management UI Interactions
 * Handles the display and population of the announcement creation/editing modal.
 * Facilitates switching between 'create' and 'update' modes for advisory posts.
 */

const modal = document.getElementById('announcementModal');

function openModal() {
    document.getElementById('modalTitle').innerText = 'New Announcement';
    document.getElementById('formAction').value = 'create';
    document.getElementById('announcementId').value = '';
    document.getElementById('title').value = '';
    document.getElementById('type').value = 'General';
    document.getElementById('status').value = 'Active';
    document.getElementById('content').value = '';
    modal.style.display = 'flex';
}

function editModal(data) {
    document.getElementById('modalTitle').innerText = 'Edit Announcement';
    document.getElementById('formAction').value = 'update';
    document.getElementById('announcementId').value = data.id;
    document.getElementById('title').value = data.title;
    document.getElementById('type').value = data.type;
    document.getElementById('status').value = data.status;
    document.getElementById('content').value = data.content;
    modal.style.display = 'flex';
}

function closeModal() {
    modal.style.display = 'none';
}

window.onclick = function (event) {
    if (event.target == modal) {
        closeModal();
    }
};
