// src/js/main.js

// Import necessary modules
import { initMessageModal } from './ui/MessageModal.js';
import { initWidgetManagementModal } from './ui/WidgetManagementModal.js';
import { initCreateWidgetModal } from './ui/CreateWidgetModal.js';
import { initDragDrop } from './features/DragDropManager.js';
import { initWidgetActions } from './features/WidgetActions.js';
import { initWidgetSettingsModal } from './ui/WidgetSettingsModal.js';
// Note: initSettingsPanel and initSidebarCollapse are defined directly in this file below.

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
    } else {
        console.warn('Settings toggle button (id="settings-toggle") not found.');
    }
    if (closeSettingsBtn) {
        closeSettingsBtn.addEventListener('click', closeSettingsPanel);
    } else {
        console.warn('Close settings button (id="close-settings") not found.');
    }
    if (settingsOverlay) {
        settingsOverlay.addEventListener('click', closeSettingsPanel);
    } else {
        console.warn('Settings overlay (id="settings-overlay") not found.');
    }

    // Handle settings tab navigation
    settingsTabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.dataset.target; // e.g., "general-settings-section"
            const targetSection = document.getElementById(targetId);

            if (targetSection) {
                // Remove 'active' from all tab buttons and sections
                settingsTabButtons.forEach(btn => btn.classList.remove('active'));
                settingsSections.forEach(section => section.classList.remove('active'));

                // Add 'active' to the clicked tab button and its corresponding section
                this.classList.add('active');
                targetSection.classList.add('active');
            } else {
                console.warn(`Target section for tab "${targetId}" not found.`);
            }
        });
    });

    // Initialize the first tab as active on load
    // This ensures one tab is always visible when the panel opens.
    const initialActiveTab = document.querySelector('.settings-tab-btn.active');
    if (initialActiveTab) {
        const targetId = initialActiveTab.dataset.target;
        const targetSection = document.getElementById(targetId);
        if (targetSection) {
            targetSection.classList.add('active');
        } else {
            console.warn(`Initial active tab target section "${targetId}" not found.`);
        }
    } else if (settingsTabButtons.length > 0) {
        // If no active tab is initially set, activate the first one
        settingsTabButtons[0].classList.add('active');
        const targetId = settingsTabButtons[0].dataset.target;
        const targetSection = document.getElementById(targetId);
        if (targetSection) {
            targetSection.classList.add('active');
        } else {
            console.warn(`First tab target section "${targetId}" not found.`);
        }
    } else {
        console.warn('No settings tab buttons found.');
    }
}

/**
 * Initializes the sidebar collapse functionality.
 * Persists the sidebar state using localStorage.
 */
function initSidebarCollapse() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const dashboard = document.querySelector('.dashboard'); // Assuming .dashboard is the parent that controls layout

    if (!sidebar || !sidebarToggle || !dashboard) {
        console.warn('Sidebar, sidebar toggle, or dashboard element not found. Sidebar collapse not initialized.');
        return;
    }

    // Check for saved sidebar state in localStorage
    const savedSidebarState = localStorage.getItem('sidebarCollapsed');
    if (savedSidebarState === 'true') {
        dashboard.classList.add('collapsed');
        sidebar.classList.add('collapsed');
    }

    sidebarToggle.addEventListener('click', () => {
        dashboard.classList.toggle('collapsed');
        sidebar.classList.toggle('collapsed');

        // Save the new state to localStorage
        const isCollapsed = dashboard.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    });
}


document.addEventListener('DOMContentLoaded', function() {
    // Initialize all UI components and features here
    console.log('DOMContentLoaded fired. Initializing dashboard features...');

    try {
        initMessageModal();
        console.log('MessageModal initialized.');
    } catch (error) {
        console.error('Error initializing MessageModal:', error);
    }

    try {
        initSettingsPanel(); // This function is defined locally in main.js
        console.log('SettingsPanel initialized.');
    } catch (error) {
        console.error('Error initializing SettingsPanel:', error);
    }

    try {
        initWidgetManagementModal();
        console.log('WidgetManagementModal initialized.');
    } catch (error) {
        console.error('Error initializing WidgetManagementModal:', error);
    }

    try {
        initCreateWidgetModal();
        console.log('CreateWidgetModal initialized.');
    } catch (error) {
        console.error('Error initializing CreateWidgetModal:', error);
    }

    try {
        initDragDrop();
        console.log('DragDrop initialized.');
    } catch (error) {
        console.error('Error initializing DragDrop:', error);
    }

    try {
        initWidgetActions();
        console.log('WidgetActions initialized.');
    } catch (error) {
        console.error('Error initializing WidgetActions:', error);
    }

    try {
        initWidgetSettingsModal();
        console.log('WidgetSettingsModal initialized.');
    } catch (error) {
        console.error('Error initializing WidgetSettingsModal:', error);
    }

    try {
        initSidebarCollapse(); // This function is defined locally in main.js
        console.log('SidebarCollapse initialized.');
    } catch (error) {
        console.error('Error initializing SidebarCollapse:', error);
    }

    console.log('All dashboard features attempted initialization.');

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
        } else {
            console.warn('Version display element (id="version-display") not found.');
        }
    } else {
        console.warn('window.appVersion not found. Version display from version.js may not be active.');
    }
});

// Refresh button functionality
const refreshBtn = document.getElementById('refresh-btn');
if (refreshBtn) {
    refreshBtn.addEventListener('click', () => {
        console.log('Refresh button clicked. Reloading page...');
        location.reload(true); // Force a hard reload
    });
} else {
    console.warn('Refresh button (id="refresh-btn") not found.');
}
