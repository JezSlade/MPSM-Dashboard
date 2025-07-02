// src/js/ui/WidgetSettingsModal.js

import AjaxService from '../utils/AjaxService.js';
import MessageModal from './MessageModal.js';

/**
 * Manages the modal for adjusting individual widget dimensions and editing code.
 */
class WidgetSettingsModal {
    constructor(overlayId, modalId, titleId, closeBtnId, formId, messageModal) {
        this.overlay = document.getElementById(overlayId);
        this.modal = document.getElementById(modalId);
        this.titleElement = document.getElementById(titleId);
        this.closeButton = document.getElementById(closeBtnId);
        this.form = document.getElementById(formId);
        this.messageModal = messageModal; // Instance of MessageModal

        this.widgetIdInput = document.getElementById('widget-settings-id');
        this.widthInput = document.getElementById('widget-settings-width');
        this.heightInput = document.getElementById('widget-settings-height');

        // NEW: Code editor elements
        this.codeEditorSection = this.modal.querySelector('.widget-code-editor-section');
        this.codeFileNameDisplay = document.getElementById('widget-code-file-name');
        this.codeEditorTextarea = document.getElementById('widget-code-editor');
        this.loadCodeButton = document.getElementById('load-widget-code-btn');
        this.saveCodeButton = document.getElementById('save-widget-code-btn');
        this.codeStatusDisplay = document.getElementById('widget-code-status');

        this.currentWidgetId = null; // Stores the ID of the widget currently being edited
        this.originalCodeContent = ''; // Stores content when file is loaded for dirty checking

        if (!this.overlay || !this.modal || !this.titleElement || !this.closeButton || !this.form || !this.widgetIdInput || !this.widthInput || !this.heightInput || !this.codeEditorSection || !this.codeEditorTextarea || !this.loadCodeButton || !this.saveCodeButton || !this.codeStatusDisplay) {
            console.error("WidgetSettingsModal: One or more required elements not found for dimensions or code editor.");
            return;
        }

        this._addEventListeners();
    }

    _addEventListeners() {
        this.closeButton.addEventListener('click', () => this.hide());
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay) {
                this.hide();
            }
        });
        this.form.addEventListener('submit', this._handleDimensionsFormSubmit.bind(this));

        // NEW: Code editor event listeners
        this.loadCodeButton.addEventListener('click', this._loadWidgetCode.bind(this));
        this.saveCodeButton.addEventListener('click', this._saveWidgetCode.bind(this));
        this.codeEditorTextarea.addEventListener('input', this._handleCodeEditorInput.bind(this));
    }

    /**
     * Shows the widget settings modal and populates it with current widget data.
     * @param {string} widgetId The ID of the widget being configured.
     * @param {string} widgetName The display name of the widget.
     * @param {number} currentWidth The current width of the widget.
     * @param {number} currentHeight The current height of the widget.
     */
    show(widgetId, widgetName, currentWidth, currentHeight) {
        this.currentWidgetId = widgetId; // Store current widget ID
        this.titleElement.textContent = `Settings for "${widgetName}"`;
        this.widgetIdInput.value = widgetId;
        this.widthInput.value = currentWidth;
        this.heightInput.value = currentHeight;

        // Reset code editor state when opening for a new widget
        this.codeEditorSection.classList.add('hidden'); // Hide editor initially
        this.codeEditorTextarea.value = '';
        this.codeFileNameDisplay.textContent = '';
        this.saveCodeButton.disabled = true;
        this.codeStatusDisplay.textContent = '';
        this.originalCodeContent = '';

        // Only show code editor for actual widget files (not special ones like 'ide' itself)
        // You might need a more robust check here if some widgets are not PHP files
        if (widgetId !== 'ide' && widgetId !== 'font_awesome') { // Exclude IDE and Font Awesome as they are not simple widgets
             this.codeEditorSection.classList.remove('hidden');
             this.codeFileNameDisplay.textContent = `widgets/${widgetId}.php`;
             this._loadWidgetCode(); // Automatically load code when modal opens
        }


        this.overlay.classList.add('show');
    }

    /**
     * Hides the widget settings modal.
     */
    hide() {
        // Check for unsaved code changes before hiding
        if (this.currentWidgetId && this.codeEditorTextarea.value !== this.originalCodeContent && !this.codeEditorSection.classList.contains('hidden')) {
            this.messageModal.show(
                'Unsaved Code Changes',
                'You have unsaved changes in the code editor. Please save or discard them before closing.',
                false
            );
            return; // Prevent closing the modal
        }
        this.overlay.classList.remove('show');
    }

    /**
     * Handles the form submission for updating widget dimensions.
     * @param {Event} event The form submit event.
     */
    async _handleDimensionsFormSubmit(event) {
        event.preventDefault();

        const widgetId = this.widgetIdInput.value;
        const newWidth = parseFloat(this.widthInput.value);
        const newHeight = parseFloat(this.heightInput.value);

        if (isNaN(newWidth) || isNaN(newHeight) || newWidth <= 0 || newHeight <= 0) {
            this.messageModal.show('Input Error', 'Please enter valid numbers for width and height.', false);
            return;
        }

        try {
            const response = await AjaxService.request('api/dashboard.php', 'POST', {
                ajax_action: 'update_widget_dimensions',
                widget_id: widgetId,
                new_width: newWidth,
                new_height: newHeight
            });

            if (response.status === 'success') {
                this.messageModal.show('Widget Updated', response.message || 'Widget dimensions updated successfully. Reloading page to apply changes.');
                this.hide();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                this.messageModal.show('Error', response.message || 'Failed to update widget dimensions.', false);
            }
        } catch (error) {
            this.messageModal.show('Error', `An unexpected error occurred: ${error.message}`, false);
        }
    }

    /**
     * NEW: Loads the code for the currently selected widget into the editor.
     */
    async _loadWidgetCode() {
        if (!this.currentWidgetId) {
            this.codeStatusDisplay.textContent = 'No widget selected.';
            return;
        }

        const filePath = `widgets/${this.currentWidgetId}.php`;
        this.codeEditorTextarea.value = 'Loading code...';
        this.codeEditorTextarea.readOnly = true;
        this.saveCodeButton.disabled = true;
        this.codeStatusDisplay.textContent = 'Loading...';
        this.codeStatusDisplay.style.color = 'var(--text-secondary)';

        try {
            const response = await AjaxService.request('api/ide.php', 'POST', {
                ajax_action: 'load_file',
                file_path: filePath
            });

            if (response.status === 'success' && response.content !== undefined) {
                this.codeEditorTextarea.value = response.content;
                this.originalCodeContent = response.content; // Store original for dirty check
                this.codeEditorTextarea.readOnly = false;
                this.saveCodeButton.disabled = true; // No changes yet
                this.codeStatusDisplay.textContent = 'Loaded';
                this.codeStatusDisplay.style.color = 'var(--success)';
            } else {
                this.codeEditorTextarea.value = `Error loading code: ${response.message || 'Unknown error'}`;
                this.codeEditorTextarea.readOnly = true;
                this.codeStatusDisplay.textContent = 'Load failed';
                this.codeStatusDisplay.style.color = 'var(--danger)';
                this.messageModal.show('Code Load Error', `Failed to load code for ${filePath}: ${response.message || 'Unknown error'}`, false);
            }
        } catch (error) {
            this.codeEditorTextarea.value = `Error: ${error.message}`;
            this.codeEditorTextarea.readOnly = true;
            this.codeStatusDisplay.textContent = 'Load failed';
            this.codeStatusDisplay.style.color = 'var(--danger)';
            this.messageModal.show('Code Load Error', `An unexpected error occurred while loading code: ${error.message}`, false);
        }
    }

    /**
     * NEW: Saves the code from the editor for the currently selected widget.
     */
    async _saveWidgetCode() {
        if (!this.currentWidgetId) {
            this.messageModal.show('Save Error', 'No widget selected to save code for.', false);
            return;
        }

        const filePath = `widgets/${this.currentWidgetId}.php`;
        const newContent = this.codeEditorTextarea.value;

        this.saveCodeButton.disabled = true; // Disable while saving
        this.codeStatusDisplay.textContent = 'Saving...';
        this.codeStatusDisplay.style.color = 'var(--warning)';

        try {
            const response = await AjaxService.request('api/ide.php', 'POST', {
                ajax_action: 'save_file',
                file_path: filePath,
                content: newContent
            });

            if (response.status === 'success') {
                this.originalCodeContent = newContent; // Update original content after successful save
                this.saveCodeButton.disabled = true;
                this.codeStatusDisplay.textContent = 'Saved';
                this.codeStatusDisplay.style.color = 'var(--success)';
                this.messageModal.show('Code Saved', response.message || 'Widget code saved successfully.', false);
            } else {
                this.saveCodeButton.disabled = false; // Re-enable if save failed
                this.codeStatusDisplay.textContent = 'Save failed';
                this.codeStatusDisplay.style.color = 'var(--danger)';
                this.messageModal.show('Code Save Error', `Failed to save code for ${filePath}: ${response.message || 'Unknown error'}`, false);
            }
        } catch (error) {
            this.saveCodeButton.disabled = false;
            this.codeStatusDisplay.textContent = 'Save failed';
            this.codeStatusDisplay.style.color = 'var(--danger)';
            this.messageModal.show('Code Save Error', `An unexpected error occurred while saving code: ${error.message}`, false);
        }
    }

    /**
     * NEW: Handles input in the code editor to check for unsaved changes.
     */
    _handleCodeEditorInput() {
        if (this.codeEditorTextarea.value !== this.originalCodeContent) {
            this.saveCodeButton.disabled = false;
            this.codeStatusDisplay.textContent = 'Unsaved Changes';
            this.codeStatusDisplay.style.color = 'var(--warning)';
        } else {
            this.saveCodeButton.disabled = true;
            this.codeStatusDisplay.textContent = 'Saved';
            this.codeStatusDisplay.style.color = 'var(--success)';
        }
    }
}

export default WidgetSettingsModal;
