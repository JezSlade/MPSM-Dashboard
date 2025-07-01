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

// New elements for settings tabs
const settingsTabButtons = document.querySelectorAll('.settings-tab-btn');
const settingsSections = document.querySelectorAll('.settings-section');
const generalSettingsNavItem = document.getElementById('general-settings-nav-item');
const layoutSettingsNavItem = document.getElementById('layout-settings-nav-item');
const advancedSettingsNavItem = document.getElementById('advanced-settings-nav-item');

// New elements for advanced settings
const outputSettingsJsonBtn = document.getElementById('output-settings-json-btn');
const outputActiveWidgetsJsonBtn = document.getElementById('output-active-widgets-json-btn'); // New button
const exportSettingsBtn = document.getElementById('export-settings-btn');
const importSettingsFileInput = document.getElementById('import-settings-file-input');
const importSettingsBtn = document.getElementById('import-settings-btn');


/**
 * Activates a specific settings section (tab) and deactivates others.
 * @param {string} targetSectionId - The ID of the section to activate.
 */
function activateSettingsSection(targetSectionId) {
    settingsSections.forEach(section => {
        if (section.id === targetSectionId) {
            section.classList.add('active');
        } else {
            section.classList.remove('active');
        }
    });

    settingsTabButtons.forEach(button => {
        if (button.dataset.target === targetSectionId) {
            button.classList.add('active');
        } else {
            button.classList.remove('active');
        }
    });
}

export function initSettingsPanel() {
    // --- Global Settings Panel Toggle ---
    settingsToggle.addEventListener('click', function() {
        settingsPanel.classList.add('active');
        settingsOverlay.style.display = 'block';
        activateSettingsSection('general-settings-section'); // Default to General tab
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

    // --- Settings Tab Navigation ---
    settingsTabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetSectionId = this.dataset.target;
            activateSettingsSection(targetSectionId);
        });
    });

    // --- Sidebar Navigation Items to open specific settings tabs ---
    if (generalSettingsNavItem) {
        generalSettingsNavItem.addEventListener('click', function() {
            settingsPanel.classList.add('active');
            settingsOverlay.style.display = 'block';
            activateSettingsSection('general-settings-section');
        });
    }
    if (layoutSettingsNavItem) {
        layoutSettingsNavItem.addEventListener('click', function() {
            settingsPanel.classList.add('active');
            settingsOverlay.style.display = 'block';
            activateSettingsSection('layout-settings-section');
        });
    }
    if (advancedSettingsNavItem) {
        advancedSettingsNavItem.addEventListener('click', function() {
            settingsPanel.classList.add('active');
            settingsOverlay.style.display = 'block';
            activateSettingsSection('advanced-settings-section');
        });
    }


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
            const submitter = e.submitter;
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

    // --- Output Current Settings JSON Button Logic ---
    if (outputSettingsJsonBtn) {
        outputSettingsJsonBtn.addEventListener('click', async function() {
            // Fetch the current settings from the backend to ensure it's up-to-date
            const response = await sendAjaxRequest('api/dashboard.php', 'get_current_settings');
            
            if (response.status === 'success' && response.settings) {
                const settingsJson = JSON.stringify(response.settings, null, 2); // Pretty print JSON
                showMessageModal('Current Dashboard Settings', `<pre style="white-space: pre-wrap; word-break: break-all; max-height: 400px; overflow-y: auto; border: 1px solid var(--glass-border); padding: 10px; border-radius: var(--border-radius); background-color: rgba(0,0,0,0.2);">${settingsJson}</pre>`);
            } else {
                showMessageModal('Error', 'Failed to retrieve current settings: ' + response.message);
            }
        });
    }

    // --- Output Active Widgets JSON Button Logic (NEW) ---
    if (outputActiveWidgetsJsonBtn) {
        outputActiveWidgetsJsonBtn.addEventListener('click', async function() {
            console.log('Show Active Widgets button clicked!'); // Debugging log
            const response = await sendAjaxRequest('api/dashboard.php', 'get_current_settings');
            
            if (response.status === 'success' && response.settings && Array.isArray(response.settings.active_widgets)) {
                const activeWidgetsJson = JSON.stringify(response.settings.active_widgets, null, 2); // Pretty print JSON
                showMessageModal('Active Widgets List', `<pre style="white-space: pre-wrap; word-break: break-all; max-height: 400px; overflow-y: auto; border: 1px solid var(--glass-border); padding: 10px; border-radius: var(--border-radius); background-color: rgba(0,0,0,0.2);">${activeWidgetsJson}</pre>`);
            } else {
                // More specific error message if active_widgets is not an array or missing
                const errorMessage = response.message || 'Response did not contain valid active widgets data.';
                showMessageModal('Error', 'Failed to retrieve active widgets: ' + errorMessage);
            }
        });
    }

    // --- Export Settings Button Logic ---
    if (exportSettingsBtn) {
        exportSettingsBtn.addEventListener('click', async function() {
            const response = await sendAjaxRequest('api/dashboard.php', 'get_current_settings');
            if (response.status === 'success' && response.settings) {
                const settingsJson = JSON.stringify(response.settings, null, 2);
                const blob = new Blob([settingsJson], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'dashboard_settings.json';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
                showMessageModal('Success', 'Dashboard settings downloaded.');
            } else {
                showMessageModal('Error', 'Failed to export settings: ' + response.message);
            }
        });
    }

    // --- Import Settings Button Logic ---
    if (importSettingsBtn && importSettingsFileInput) {
        importSettingsBtn.addEventListener('click', async function() {
            const file = importSettingsFileInput.files[0];
            if (!file) {
                showMessageModal('Error', 'Please select a JSON file to import.');
                return;
            }

            const reader = new FileReader();
            reader.onload = async function(e) {
                try {
                    const importedSettings = JSON.parse(e.target.result);
                    // Send the imported settings to the backend to save
                    const response = await sendAjaxRequest('api/dashboard.php', 'import_settings', { settings_data: JSON.stringify(importedSettings) });
                    
                    if (response.status === 'success') {
                        showMessageModal('Success', response.message + ' Reloading dashboard...', () => location.reload(true));
                    } else {
                        showMessageModal('Error', 'Failed to import settings: ' + response.message);
                    }
                } catch (error) {
                    showMessageModal('Error', 'Invalid JSON file. Please upload a valid dashboard settings JSON file.');
                    console.error('Error parsing imported file:', error);
                }
            };
            reader.readAsText(file);
        });
    }
}
