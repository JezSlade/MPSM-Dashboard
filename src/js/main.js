// src/js/main.js

import { initSettingsPanel } from './ui/SettingsPanel.js';
import { initMessageModal } from './ui/MessageModal.js'; // Ensure this is imported if it has an init function
import { initWidgetManagementModal } from './ui/WidgetManagementModal.js';
import { initCreateWidgetModal } from './ui/CreateWidgetModal.js'; // Assuming you have this module
import { initDragDrop } from './features/DragDropManager.js';
import { initWidgetActions } from './features/WidgetActions.js';
import { initWidgetSettingsModal } from './ui/WidgetSettingsModal.js'; // New: Initialize widget settings modal

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all UI components and features here
    initMessageModal(); // Initialize the message modal first as others might use it
    initSettingsPanel();
    initWidgetManagementModal();
    initCreateWidgetModal(); // Ensure this function exists and is correctly implemented
    initDragDrop();
    initWidgetActions();
    initWidgetSettingsModal(); // Initialize the widget settings modal
});

// Refresh button functionality
const refreshBtn = document.getElementById('refresh-btn');
if (refreshBtn) {
    refreshBtn.addEventListener('click', () => {
        location.reload(true); // Force a hard reload
    });
}
