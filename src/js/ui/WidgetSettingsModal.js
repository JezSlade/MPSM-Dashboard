// src/js/ui/WidgetSettingsModal.js

import { showMessageModal } from './MessageModal.js';
import { sendAjaxRequest } from '../utils/AjaxService.js';

const widgetSettingsModalOverlay = document.getElementById('widget-settings-modal-overlay');
const closeWidgetSettingsModalBtn = document.getElementById('close-widget-settings-modal');
const widgetSettingsModalTitle = document.getElementById('widget-settings-modal-title');
const widgetSettingsIdInput = document.getElementById('widget-settings-id'); // Changed from widget-settings-index
const widgetSettingsWidthInput = document.getElementById('widget-settings-width');
const widgetSettingsHeightInput = document.getElementById('widget-settings-height');
const widgetDimensionsForm = document.getElementById('widget-dimensions-form');
const openIdeButton = document.getElementById('open-ide-button'); // NEW: Get the IDE button

/**
 * Displays the widget settings modal for a specific widget.
 * @param {string} widgetId - The ID of the widget being configured.
 * @param {string} widgetName - The display name of the widget.
 * @param {number} currentWidth - The current width of the widget.
 * @param {number} currentHeight - The current height of the widget.
 */
export function showWidgetSettingsModal(widgetId, widgetName, currentWidth, currentHeight) {
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

    // NEW: Set up the Open IDE button
    if (openIdeButton) {
        // Construct the path to the widget file
        const widgetFilePath = `widgets/${widgetId}.php`;
        // Encode the path to be safely passed as a URL parameter
        const encodedFilePath = encodeURIComponent(widgetFilePath);
        // Set the href for the button to open the IDE in a new tab
        openIdeButton.onclick = () => {
            window.open(`ide.php?file=${encodedFilePath}`, '_blank');
        };
    }


    widgetSettingsModalOverlay.classList.add('active');
}

export function initWidgetSettingsModal() {
    if (closeWidgetSettingsModalBtn) {
        closeWidgetSettingsModalBtn.addEventListener('click', function() {
            widgetSettingsModalOverlay.classList.remove('active');
        });
    }

    if (widgetDimensionsForm) {
        widgetDimensionsForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const widgetId = widgetSettingsIdInput.value; // Get widget ID
            const newWidth = parseFloat(widgetSettingsWidthInput.value);
            const newHeight = parseFloat(widgetSettingsHeightInput.value);

            if (isNaN(newWidth) || isNaN(newHeight)) {
                showMessageModal('Validation Error', 'Please enter valid numbers for width and height.');
                return;
            }

            const submitBtn = widgetDimensionsForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            const response = await sendAjaxRequest('api/dashboard.php', 'update_single_widget_dimensions', {
                widget_id: widgetId, // Send widget_id
                new_width: newWidth,
                new_height: newHeight
            });

            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Save Dimensions';

            if (response.status === 'success') {
                showMessageModal('Success', response.message, () => location.reload(true)); // Reload to apply changes
            } else {
                showMessageModal('Error', response.message);
            }
        });
    }
}
