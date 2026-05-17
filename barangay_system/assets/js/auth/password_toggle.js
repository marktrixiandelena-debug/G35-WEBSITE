/**
 * Password Visibility Utility
 * Toggles the 'type' attribute of password input fields between 'password' and 'text'.
 * Used across login and registration forms for improved user experience.
 */

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);
}
