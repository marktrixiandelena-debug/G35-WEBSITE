/**
 * Report Encoding Interaction Logic
 * Manages dynamic UI behavior for manual report entry, including:
 * - Report source toggling (Online vs Offline/Walk-in)
 * - Resident database filtering and search
 * - Reporter context switching (Registered vs Guest)
 */

// Show/hide offline batch fields and note
const radios = document.querySelectorAll('input[name="report_source"]');
const offlineNote = document.getElementById('offlineBatchNote');
const originalTimeGroup = document.getElementById('originalTimeGroup');
const originalTimeInput = document.getElementById('originalTimeInput');

radios.forEach(radio => {
    radio.addEventListener('change', function () {
        const isOffline = this.value === 'Offline Batch';
        offlineNote.classList.toggle('visible', isOffline);
        originalTimeGroup.style.display = isOffline ? 'block' : 'none';

        // Toggle required attribute for the offline time
        if (isOffline) {
            originalTimeInput.setAttribute('required', 'required');
        } else {
            originalTimeInput.removeAttribute('required');
            originalTimeInput.value = ''; // clear if switching away
        }
    });
});

// Resident search filter
const searchInput = document.getElementById('residentSearch');
const residentSelect = document.getElementById('resident_id');
const allOptions = Array.from(residentSelect.options);

searchInput.addEventListener('input', function () {
    const query = this.value.toLowerCase().trim();
    residentSelect.innerHTML = '';
    const filtered = allOptions.filter(opt => opt.dataset.name && opt.dataset.name.includes(query));
    if (filtered.length > 0) {
        filtered.forEach(opt => residentSelect.appendChild(opt.cloneNode(true)));
    } else {
        const empty = document.createElement('option');
        empty.disabled = true;
        empty.textContent = 'No residents match your search.';
        residentSelect.appendChild(empty);
    }
});

// Reporter type toggle
const reporterTypeRadios = document.querySelectorAll('input[name="reporter_type"]');
const registeredFields = document.getElementById('registered-user-fields');
const guestFields = document.getElementById('guest-user-fields');
const residentSelectInput = document.getElementById('resident_id');

reporterTypeRadios.forEach(radio => {
    radio.addEventListener('change', function () {
        if (this.value === 'guest') {
            registeredFields.style.display = 'none';
            residentSelectInput.removeAttribute('required');
            guestFields.style.display = 'block';
            guestFields.querySelector('input[name="guest_name"]').setAttribute('required', 'required');
        } else {
            guestFields.style.display = 'none';
            guestFields.querySelector('input[name="guest_name"]').removeAttribute('required');
            registeredFields.style.display = 'block';
            residentSelectInput.setAttribute('required', 'required');
        }
    });
});
