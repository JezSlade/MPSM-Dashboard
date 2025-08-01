// src/js/ui/WidgetSettingsModal.js

import { showMessageModal } from './MessageModal.js';
import { sendAjaxRequest } from '../utils/AjaxService.js';

const widgetSettingsModalOverlay = document.getElementById('widget-settings-modal-overlay');
const closeWidgetSettingsModalBtn = document.getElementById('close-widget-settings-modal');
const widgetSettingsModalTitle = document.getElementById('widget-settings-modal-title');
const widgetSettingsIdInput = document.getElementById('widget-settings-id');
const widgetSettingsWidthInput = document.getElementById('widget-settings-width');
const widgetSettingsHeightInput = document.getElementById('widget-settings-height');
const widgetDimensionsForm = document.getElementById('widget-dimensions-form');
const openIdeButton = document.getElementById('open-ide-button'); // Get the IDE button

/**
 * Displays the widget settings modal for a specific widget.
 * @param {string} widgetId - The ID of the widget being configured.
 * @param {string} widgetName - The display name of the widget.
 * @param {number} currentWidth - The current width of the widget.
 * @param {number} currentHeight - The current height of the widget.
 */
export function showWidgetSettingsModal(widgetId, widgetName, currentWidth, currentHeight) {
    console.log(`[WidgetSettingsModal] showWidgetSettingsModal called for widget: ${widgetId}, name: ${widgetName}`);

    if (!widgetSettingsModalTitle || !widgetSettingsIdInput || !widgetSettingsWidthInput || !widgetSettingsHeightInput) {
        console.error('[WidgetSettingsModal] One or more essential modal elements not found.');
        showMessageModal('Error', 'Widget settings modal elements are missing. Cannot display settings.');
        return;
    }

    widgetSettingsModalTitle.textContent = `${widgetName} Settings`;
    widgetSettingsIdInput.value = widgetId; // Set the widget ID
    widgetSettingsWidthInput.value = currentWidth;
    widgetSettingsHeightInput.value = currentHeight;

    // Check if "Show All Available Widgets" is active and disable inputs if so
    const showAllWidgetsToggle = document.getElementById('show_all_available_widgets');
    const isDisabled = showAllWidgetsToggle && showAllWidgetsToggle.checked;

    widgetSettingsWidthInput.disabled = isDisabled;
    widgetSettingsHeightInput.disabled = isDisabled;
    const submitBtn = widgetDimensionsForm.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = isDisabled;
        submitBtn.textContent = isDisabled ? 'Disabled in Show All Mode' : 'Save Dimensions';
    }

    // Set up the Open IDE button
    if (openIdeButton) {
        console.log('[WidgetSettingsModal] Open IDE button found. Setting up click handler.');
        // Construct the path to the widget file
        const widgetFilePath = `widgets/${widgetId}.php`;
        // Encode the path to be safely passed as a URL parameter
        const encodedFilePath = encodeURIComponent(widgetFilePath);
        // Set the href for the button to open the IDE in a new tab
        openIdeButton.onclick = () => {
            console.log(`[WidgetSettingsModal] Opening IDE for file: ${widgetFilePath}`);
            window.open(`ide.php?file=${encodedFilePath}`, '_blank');
        };
    } else {
        console.warn('[WidgetSettingsModal] Open IDE button (id="open-ide-button") not found.');
    }

    if (widgetSettingsModalOverlay) {
        widgetSettingsModalOverlay.classList.add('active');
        console.log('[WidgetSettingsModal] Modal overlay activated.');
    } else {
        console.error('[WidgetSettingsModal] Modal overlay element not found.');
    }
}

export function initWidgetSettingsModal() {
    console.log('[WidgetSettingsModal] Initializing widget settings modal listeners.');

    if (closeWidgetSettingsModalBtn) {
        closeWidgetSettingsModalBtn.addEventListener('click', function() {
            console.log('[WidgetSettingsModal] Close button clicked. Deactivating modal overlay.');
            widgetSettingsModalOverlay.classList.remove('active');
        });
    } else {
        console.warn('[WidgetSettingsModal] Close widget settings modal button not found.');
    }

    if (widgetDimensionsForm) {
        widgetDimensionsForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('[WidgetSettingsModal] Dimensions form submitted.');

            const widgetId = widgetSettingsIdInput.value; // Get widget ID
            const newWidth = parseFloat(widgetSettingsWidthInput.value);
            const newHeight = parseFloat(widgetSettingsHeightInput.value);

            if (isNaN(newWidth) || isNaN(newHeight)) {
                console.error('[WidgetSettingsModal] Validation Error: Invalid width or height entered.', {newWidth, newHeight});
                showMessageModal('Validation Error', 'Please enter valid numbers for width and height.');
                return;
            }

            const submitBtn = widgetDimensionsForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                console.log('[WidgetSettingsModal] Save button disabled, showing spinner.');
            }

            try {
                const response = await sendAjaxRequest('api/dashboard.php', 'update_single_widget_dimensions', {
                    widget_id: widgetId, // Send widget_id
                    new_width: newWidth,
                    new_height: newHeight
                });

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Save Dimensions';
                    console.log('[WidgetSettingsModal] Save button re-enabled.');
                }

                if (response.status === 'success') {
                    console.log('[WidgetSettingsModal] Dimensions updated successfully:', response.message);
                    showMessageModal('Success', response.message, () => location.reload(true)); // Reload to apply changes
                } else {
                    console.error('[WidgetSettingsModal] Error updating dimensions:', response.message, response.rawResponse);
                    showMessageModal('Error', response.message);
                }
            } catch (error) {
                console.error('[WidgetSettingsModal] AJAX request failed during dimension update:', error);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Save Dimensions';
                }
                showMessageModal('Error', 'An unexpected error occurred while saving dimensions.');
            }
        });
    } else {
        console.warn('[WidgetSettingsModal] Widget dimensions form not found.');
    }
}
