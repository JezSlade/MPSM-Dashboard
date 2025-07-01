// src/js/utils/AjaxService.js

/**
 * Sends an AJAX POST request to a specified endpoint.
 * @param {string} endpoint - The URL of the API endpoint (e.g., 'api/dashboard.php').
 * @param {string} ajaxAction - The specific action for the PHP AJAX handler.
 * @param {Object} data - Data to send with the request.
 * @returns {Promise<Object>} A promise that resolves with the JSON response.
 */
export async function sendAjaxRequest(endpoint, ajaxAction, data = {}) {
    const formData = new FormData();
    formData.append('ajax_action', ajaxAction);
    for (const key in data) {
        formData.append(key, data[key]);
    }

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest' // Custom header to identify AJAX requests in PHP
            },
            body: formData
        });

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
        }
        return await response.json();
    } catch (error) {
        console.error('AJAX Error:', error);
        // Using a global showMessageModal, assuming it's available or imported
        // For now, let's assume it's imported or passed.
        // If not, you might need to re-evaluate how errors are displayed.
        // For this refactor, we'll import it.
        const MessageModal = await import('../ui/MessageModal.js');
        MessageModal.showMessageModal('Error', `AJAX request failed: ${error.message}`);
        return { status: 'error', message: error.message };
    }
}
