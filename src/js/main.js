// src/js/main.js

import { initSettingsPanel } from './ui/SettingsPanel.js';
import { initWidgetSettingsModal } from './ui/WidgetSettingsModal.js';
import { initWidgetManagementModal } from './ui/WidgetManagementModal.js';
import { initCreateWidgetModal } from './ui/CreateWidgetModal.js';
import { initWidgetActions } from './features/WidgetActions.js';
import { initDragDropManager } from './features/DragDropManager.js';
import { initIdeEventListeners } from './features/IdeWidget.js';

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modular components
    initSettingsPanel();
    initWidgetSettingsModal();
    initWidgetManagementModal();
    initCreateWidgetModal();
    initWidgetActions();
    initDragDropManager();
    initIdeEventListeners();

    // --- Other Global Buttons ---
    document.getElementById('refresh-btn').addEventListener('click', function() {
        location.reload();
    });

    document.getElementById('theme-settings-btn').addEventListener('click', function() {
        // This button now just acts as a shortcut to open the settings panel
        document.getElementById('settings-panel').classList.add('active');
        document.getElementById('settings-overlay').style.display = 'block';
    });
});
