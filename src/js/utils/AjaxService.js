// src/js/utils/AjaxService.js

/**
 * Sends an AJAX request to a specified PHP endpoint.
 * @param {string} endpoint The URL of the PHP endpoint (e.g., 'api/dashboard.php').
 * @param {string} action The specific action for the PHP script to perform.
 * @param {Object} [data={}] Optional data to send with the request.
 * @returns {Promise<Object>} A promise that resolves with the JSON response from the server.
 */
export async function sendAjaxRequest(endpoint, action, data = {}) {
    const formData = new FormData();
    formData.append('ajax_action', action);

    for (const key in data) {
        if (Object.prototype.hasOwnProperty.call(data, key)) {
            formData.append(key, data[key]);
        }
    }

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            // If HTTP status is not 2xx, throw an error
            const errorText = await response.text(); // Get raw response text
            throw new Error(`HTTP error! Status: ${response.status}, Response: ${errorText}`);
        }

        const responseText = await response.text(); // Get response as text first
        
        try {
            const jsonResponse = JSON.parse(responseText); // Attempt to parse as JSON
            return jsonResponse;
        } catch (jsonError) {
            console.error('JSON parsing error:', jsonError);
            console.error('Raw response text:', responseText);
            // If JSON parsing fails, return an error object with raw text
            return {
                status: 'error',
                message: `Failed to parse server response as JSON. Raw response: "${responseText.substring(0, 200)}..."`,
                rawResponse: responseText
            };
        }

    } catch (error) {
        console.error('AJAX request failed:', error);
        return { status: 'error', message: error.message };
    }
}
