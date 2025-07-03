// src/js/main.js

// Removed the import for initSettingsPanel from './ui/SettingsPanel.js'
// as the function is now defined directly in this file.
import { initMessageModal } from './ui/MessageModal.js';
import { initWidgetManagementModal } from './ui/WidgetManagementModal.js';
import { initCreateWidgetModal } from './ui/CreateWidgetModal.js';
import { initDragDrop } from './features/DragDropManager.js';
import { initWidgetActions } from './features/WidgetActions.js';
import { initWidgetSettingsModal } from './ui/WidgetSettingsModal.js';

/**
 * Initializes the settings panel functionality, including opening/closing
 * and tab navigation.
 */
function initSettingsPanel() {
    const settingsPanel = document.getElementById('settings-panel');
    const settingsToggleBtn = document.getElementById('settings-toggle');
    const closeSettingsBtn = document.getElementById('close-settings');
    const settingsOverlay = document.getElementById('settings-overlay');
    const settingsTabButtons = document.querySelectorAll('.settings-tab-btn');
    const settingsSections = document.querySelectorAll('.settings-section');

    // Function to open the settings panel
    const openSettingsPanel = () => {
        settingsPanel.classList.add('open');
        settingsOverlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent scrolling body when panel is open
    };

    // Function to close the settings panel
    const closeSettingsPanel = () => {
        settingsPanel.classList.remove('open');
        settingsOverlay.classList.remove('active');
        document.body.style.overflow = ''; // Restore body scrolling
    };

    // Event listeners for opening and closing the panel
    if (settingsToggleBtn) {
        settingsToggleBtn.addEventListener('click', openSettingsPanel);
    }
    if (closeSettingsBtn) {
        closeSettingsBtn.addEventListener('click', closeSettingsPanel);
    }
    if (settingsOverlay) {
        settingsOverlay.addEventListener('click', closeSettingsPanel);
    }

    // Handle settings tab navigation
    settingsTabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove 'active' from all tab buttons and sections
            settingsTabButtons.forEach(btn => btn.classList.remove('active'));
            settingsSections.forEach(section => section.classList.remove('active'));

            // Add 'active' to the clicked tab button
            button.classList.add('active');

            // Show the corresponding section
            const targetId = button.dataset.target;
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.classList.add('active');
            }
        });
    });

    // Initialize the first tab as active on load
    // Find the currently active tab button (if any)
    const initialActiveTab = document.querySelector('.settings-tab-btn.active');
    if (initialActiveTab) {
        const targetId = initialActiveTab.dataset.target;
        const targetSection = document.getElementById(targetId);
        if (targetSection) {
            targetSection.classList.add('active');
        }
    } else if (settingsTabButtons.length > 0) {
        // If no active tab is set, activate the first one
        settingsTabButtons[0].classList.add('active');
        const targetId = settingsTabButtons[0].dataset.target;
        const targetSection = document.getElementById(targetId);
        if (targetSection) {
            targetSection.classList.add('active');
        }
    }
}


document.addEventListener('DOMContentLoaded', function() {
    // Initialize all UI components and features here
    initMessageModal(); // Initialize the message modal first as others might use it
    initSettingsPanel(); // Now calls the function defined in this file
    initWidgetManagementModal();
    initCreateWidgetModal(); // Ensure this function exists and is correctly implemented
    initDragDrop();
    initWidgetActions();
    initWidgetSettingsModal(); // Initialize the widget settings modal

    // Version display logic (from previous request)
    if (window.appVersion) {
        const versionDisplay = document.getElementById('version-display');
        if (versionDisplay) {
            // The PHP-generated version is already displayed.
            // This JS block was previously attempting to parse a different version string.
            // If window.appVersion (from version.js) is also needed, its display
            // logic would need to be integrated here, perhaps for a secondary version display.
            // For now, it remains as is, not directly affecting the primary PHP-generated version.
            // const raw = window.appVersion.split(".").pop();
            // const verInt = parseInt(raw);
            // const v1 = Math.floor(verInt / 100);
            // const v2 = Math.floor((verInt % 100) / 10);
            // const v3 = verInt % 10 + ((verInt % 100) >= 10 ? 0 : (verInt % 100));
            // Example of how you might display it if needed:
            // versionDisplay.innerHTML += ` (JS Build: ${window.appVersion})`;
        }
    }
});

// Refresh button functionality
const refreshBtn = document.getElementById('refresh-btn');
if (refreshBtn) {
    refreshBtn.addEventListener('click', () => {
        location.reload(true); // Force a hard reload
    });
}
