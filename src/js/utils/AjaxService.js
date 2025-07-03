// src/js/utils/AjaxService.js

/**
 * Sends an AJAX request to a specified PHP endpoint.
 * @param {string} endpoint The URL of the PHP endpoint (e.g., 'api/dashboard.php').
 * @param {string} action The specific action for the PHP script to perform.
 * @param {Object} [data={}] Optional data to send with the request.
 * @returns {Promise<Object>} A promise that resolves with the JSON response from the server.
 */
export async function sendAjaxRequest(endpoint, action, data = {}) {
    console.log(`[AjaxService] Sending AJAX request to ${endpoint} with action: ${action}`, data);

    const formData = new FormData();
    formData.append('ajax_action', action);

    for (const key in data) {
        if (Object.prototype.hasOwnProperty.call(data, key)) {
            // If data[key] is an object or array, stringify it
            if (typeof data[key] === 'object' && data[key] !== null) {
                formData.append(key, JSON.stringify(data[key]));
                console.log(`[AjaxService] Appending JSON stringified data: ${key}=${JSON.stringify(data[key])}`);
            } else {
                formData.append(key, data[key]);
                console.log(`[AjaxService] Appending data: ${key}=${data[key]}`);
            }
        }
    }

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            body: formData
        });

        console.log(`[AjaxService] Received response from ${endpoint}. Status: ${response.status}`);

        if (!response.ok) {
            // If HTTP status is not 2xx, throw an error
            const errorText = await response.text(); // Get raw response text
            console.error(`[AjaxService] HTTP error! Status: ${response.status}, Raw Response: "${errorText}"`);
            throw new Error(`HTTP error! Status: ${response.status}, Response: ${errorText}`);
        }

        const responseText = await response.text(); // Get response as text first
        console.log(`[AjaxService] Raw response text: "${responseText.substring(0, 500)}..."`); // Log first 500 chars

        try {
            const jsonResponse = JSON.parse(responseText); // Attempt to parse as JSON
            console.log('[AjaxService] Response successfully parsed as JSON.', jsonResponse);
            return jsonResponse;
        } catch (jsonError) {
            console.error('[AjaxService] JSON parsing error:', jsonError);
            console.error('[AjaxService] Raw response text that caused error:', responseText);
            // If JSON parsing fails, return an error object with raw text
            return {
                status: 'error',
                message: `Failed to parse server response as JSON. Raw response: "${responseText.substring(0, 200)}..."`,
                rawResponse: responseText
            };
        }

    } catch (error) {
        console.error('[AjaxService] AJAX request failed:', error);
        return {
            status: 'error',
            message: `AJAX request failed: ${error.message}`,
            rawError: error
        };
    }
}
