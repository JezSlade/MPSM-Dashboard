// src/js/ui/SettingsPanel.js

import { showMessageModal } from './MessageModal.js';
import { sendAjaxRequest } from '../utils/AjaxService.js';

const settingsToggle = document.getElementById('settings-toggle');
const closeSettings = document.getElementById('close-settings');
const settingsPanel = document.getElementById('settings-panel');
const settingsOverlay = document.getElementById('settings-overlay');
const globalSettingsForm = document.getElementById('global-settings-form');
const showAllWidgetsToggle = document.getElementById('show_all_available_widgets');
const widgetSelect = document.getElementById('widget_select');
const addWidgetToDashboardBtn = document.getElementById('add-widget-to-dashboard-btn');
const deleteSettingsJsonBtn = document.getElementById('delete-settings-json-btn');

export function initSettingsPanel() {
    // --- Global Settings Panel Toggle ---
    settingsToggle.addEventListener('click', function() {
        settingsPanel.classList.add('active');
        settingsOverlay.style.display = 'block';
    });

    closeSettings.addEventListener('click', function() {
        settingsPanel.classList.remove('active');
        settingsOverlay.style.display = 'none';
    });

    settingsOverlay.addEventListener('click', function(e) {
        if (e.target === settingsOverlay) {
            settingsPanel.classList.remove('active');
            this.style.display = 'none';
        }
    });

    // Handle form submission for global update_settings
    if (globalSettingsForm) {
        globalSettingsForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(globalSettingsForm);
            const dataToSubmit = {};
            for (const [key, value] of formData.entries()) {
                if (key === 'enable_animations' || key === 'show_all_available_widgets') {
                    dataToSubmit[key] = globalSettingsForm.elements[key].checked ? '1' : '0';
                } else {
                    dataToSubmit[key] = value;
                }
            }

            // Determine which action to send based on the button clicked
            // This assumes the 'name' attribute of the submit button determines the action
            // For simplicity, we'll assume the form is for 'update_settings' or 'add_widget'
            const submitter = e.submitter; // Get the button that triggered the submit
            let actionType = 'update_settings'; // Default action

            if (submitter && submitter.name === 'add_widget') {
                actionType = 'add_widget';
            }

            const response = await sendAjaxRequest('api/dashboard.php', actionType, dataToSubmit);

            if (response.status === 'success') {
                showMessageModal('Success', response.message, () => location.reload(true)); // Reload to apply settings
            } else {
                showMessageModal('Error', response.message);
            }
        });
    }

    // Disable/Enable Add Widget button based on 'Show All Widgets' state
    function updateAddRemoveButtonStates() {
        if (showAllWidgetsToggle && widgetSelect && addWidgetToDashboardBtn) {
            const isDisabled = showAllWidgetsToggle.checked;
            widgetSelect.disabled = isDisabled;
            addWidgetToDashboardBtn.disabled = isDisabled;

            // Also update widget settings modal's inputs if it's open
            // This part needs to interact with WidgetSettingsModal, so it's better to export a function from there
            // or pass a callback. For now, directly accessing DOM elements.
            const widgetSettingsModalOverlay = document.getElementById('widget-settings-modal-overlay');
            if (widgetSettingsModalOverlay && widgetSettingsModalOverlay.classList.contains('active')) {
                const widgetSettingsWidthInput = document.getElementById('widget-settings-width');
                const widgetSettingsHeightInput = document.getElementById('widget-settings-height');
                const widgetDimensionsForm = document.getElementById('widget-dimensions-form');
                
                if (widgetSettingsWidthInput && widgetSettingsHeightInput && widgetDimensionsForm) {
                    widgetSettingsWidthInput.disabled = isDisabled;
                    widgetSettingsHeightInput.disabled = isDisabled;
                    widgetDimensionsForm.querySelector('button[type="submit"]').disabled = isDisabled;
                    widgetDimensionsForm.querySelector('button[type="submit"]').textContent = isDisabled ? 'Disabled in Show All Mode' : 'Save Dimensions';
                }
            }
        }
    }

    if (showAllWidgetsToggle) {
        showAllWidgetsToggle.addEventListener('change', updateAddRemoveButtonStates);
    }
    updateAddRemoveButtonStates(); // Initial state update on load

    // --- Delete Settings JSON Button Logic ---
    if (deleteSettingsJsonBtn) {
        deleteSettingsJsonBtn.addEventListener('click', function() {
            showMessageModal(
                'Confirm Reset',
                'Are you sure you want to delete all dashboard settings and reset to default? This cannot be undone.',
                async function() {
                    const response = await sendAjaxRequest('api/dashboard.php', 'delete_settings_json');
                    if (response.status === 'success') {
                        showMessageModal('Success', response.message + ' Reloading dashboard...', function() {
                            location.reload(true); // Force a hard reload
                        });
                    } else {
                        showMessageModal('Error', response.message);
                    }
                }
            );
        });
    }
}
