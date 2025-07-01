// src/js/ui/MessageModal.js

const messageModalOverlay = document.getElementById('message-modal-overlay');
const messageModalTitle = document.getElementById('message-modal-title');
const messageModalContent = document.getElementById('message-modal-content'); // This element will now accept HTML
const closeMessageModalBtn = document.getElementById('close-message-modal');
const confirmMessageModalBtn = document.getElementById('confirm-message-modal');

/**
 * Displays a customizable message modal.
 * @param {string} title - The title of the modal.
 * @param {string} message - The content message of the modal. Can now contain HTML.
 * @param {Function} [confirmCallback=null] - Callback function to execute when 'OK' is clicked.
 */
export function showMessageModal(title, message, confirmCallback = null) {
    messageModalTitle.textContent = title;
    // IMPORTANT CHANGE: Use innerHTML to render HTML content
    messageModalContent.innerHTML = message;
    messageModalOverlay.classList.add('active');

    // Clear previous event listeners to prevent multiple calls
    // Clone node to remove all existing event listeners
    const newConfirmBtn = confirmMessageModalBtn.cloneNode(true);
    confirmMessageModalBtn.parentNode.replaceChild(newConfirmBtn, confirmMessageModalBtn);

    newConfirmBtn.addEventListener('click', function() {
        messageModalOverlay.classList.remove('active');
        if (confirmCallback) {
            confirmCallback();
        }
    });

    const newCloseBtn = closeMessageModalBtn.cloneNode(true);
    closeMessageModalBtn.parentNode.replaceChild(newCloseBtn, closeMessageModalBtn);
    newCloseBtn.addEventListener('click', function() {
        messageModalOverlay.classList.remove('active');
    });
}
