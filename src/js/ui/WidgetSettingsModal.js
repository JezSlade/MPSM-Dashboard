// src/js/ui/WidgetSettingsModal.js

import { showMessageModal } from './MessageModal.js';
import { sendAjaxRequest } from '../utils/AjaxService.js';

const widgetSettingsModalOverlay = document.getElementById('widget-settings-modal-overlay');
const closeWidgetSettingsModalBtn = document.getElementById('close-widget-settings-modal');
const widgetSettingsTitle = document.getElementById('widget-settings-modal-title');
const widgetSettingsIndexInput = document.getElementById('widget-settings-index');
const widgetSettingsWidthInput = document.getElementById('widget-settings-width');
const widgetSettingsHeightInput = document.getElementById('widget-settings-height');
const widgetDimensionsForm = document.getElementById('widget-dimensions-form');

/**
 * Function to show the individual widget settings modal.
 * @param {string} widgetName - The display name of the widget.
 * @param {string} widgetIndex - The index of the widget in the active_widgets array.
 * @param {number} currentWidth - The current width of the widget.
 * @param {number} currentHeight - The current height of the widget.
 */
export function showWidgetSettingsModal(widgetName, widgetIndex, currentWidth, currentHeight) {
    widgetSettingsTitle.textContent = `Settings for "${widgetName}"`;
    widgetSettingsIndexInput.value = widgetIndex;
    widgetSettingsWidthInput.value = currentWidth;
    widgetSettingsHeightInput.value = currentHeight;

    // Check if "Show All Widgets" mode is active and disable inputs if it is
    const showAllWidgetsToggle = document.getElementById('show_all_available_widgets');
    const isDisabled = showAllWidgetsToggle && showAllWidgetsToggle.checked;
    widgetSettingsWidthInput.disabled = isDisabled;
    widgetSettingsHeightInput.disabled = isDisabled;
    widgetDimensionsForm.querySelector('button[type="submit"]').disabled = isDisabled;
    widgetDimensionsForm.querySelector('button[type="submit"]').textContent = isDisabled ? 'Disabled in Show All Mode' : 'Save Dimensions';

    widgetSettingsModalOverlay.classList.add('active');
}

export function initWidgetSettingsModal() {
    // Close individual widget settings modal listeners
    closeWidgetSettingsModalBtn.addEventListener('click', function() {
        widgetSettingsModalOverlay.classList.remove('active');
    });
    widgetSettingsModalOverlay.addEventListener('click', function(e) {
        if (e.target === widgetSettingsModalOverlay) {
            widgetSettingsModalOverlay.classList.remove('active');
        }
    });

    // Handle submission of individual widget dimensions form
    widgetDimensionsForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const widgetIndex = widgetSettingsIndexInput.value;
        const newWidth = parseFloat(widgetSettingsWidthInput.value);
        const newHeight = parseFloat(widgetSettingsHeightInput.value);

        // Check if "Show All Widgets" mode is active before submitting
        const showAllWidgetsToggle = document.getElementById('show_all_available_widgets');
        if (showAllWidgetsToggle && showAllWidgetsToggle.checked) {
            showMessageModal('Information', 'Widget dimension adjustment is disabled in "Show All Widgets" mode.');
            widgetSettingsModalOverlay.classList.remove('active'); // Close settings modal
            return;
        }
        
        const response = await sendAjaxRequest('api/dashboard.php', 'update_single_widget_dimensions', {
            widget_index: widgetIndex,
            new_width: newWidth,
            new_height: newHeight
        });

        if (response.status === 'success') {
            showMessageModal('Success', response.message, () => location.reload()); // Reload on success
        } else {
            showMessageModal('Error', response.message);
        }
        widgetSettingsModalOverlay.classList.remove('active');
    });
}
