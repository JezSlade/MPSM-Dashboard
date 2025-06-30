// dashboard.js

document.addEventListener('DOMContentLoaded', function() {
    // --- Global Settings Panel Toggle ---
    const settingsToggle = document.getElementById('settings-toggle');
    const closeSettings = document.getElementById('close-settings');
    const settingsPanel = document.getElementById('settings-panel');
    const settingsOverlay = document.getElementById('settings-overlay');

    settingsToggle.addEventListener('click', function() {
        settingsPanel.classList.add('active');
        settingsOverlay.style.display = 'block';
    });

    closeSettings.addEventListener('click', function() {
        settingsPanel.classList.remove('active');
        settingsOverlay.style.display = 'none';
    });

    settingsOverlay.addEventListener('click', function() {
        settingsPanel.classList.remove('active');
        this.style.display = 'none';
    });

    // --- Message Modal Functions (for general confirmations/alerts) ---
    const messageModalOverlay = document.getElementById('message-modal-overlay');
    const messageModalTitle = document.getElementById('message-modal-title');
    const messageModalContent = document.getElementById('message-modal-content');
    const closeMessageModalBtn = document.getElementById('close-message-modal');
    const confirmMessageModalBtn = document.getElementById('confirm-message-modal');

    function showMessageModal(title, message, confirmCallback = null) {
        messageModalTitle.textContent = title;
        messageModalContent.textContent = message;
        messageModalOverlay.classList.add('active');

        // Clear previous event listeners to prevent multiple calls
        const newConfirmBtn = confirmMessageModalBtn.cloneNode(true);
        confirmMessageModalBtn.parentNode.replaceChild(newConfirmBtn, confirmMessageModalBtn);

        newConfirmBtn.addEventListener('click', function() {
            messageModalOverlay.classList.remove('active');
            if (confirmCallback) {
                confirmCallback();
            }
        });

        const newCloseBtn = closeMessageModalBtn.cloneNode(true);
        closeMessageModalBtn.parentNode.replaceChild(newCloseBtn, closeMessageModalBtn);
        newCloseBtn.addEventListener('click', function() {
            messageModalOverlay.classList.remove('active');
        });
    }


    // --- Widget Settings Modal Elements ---
    const widgetSettingsModalOverlay = document.getElementById('widget-settings-modal-overlay');
    const widgetSettingsModal = document.getElementById('widget-settings-modal');
    const closeWidgetSettingsModalBtn = document.getElementById('close-widget-settings-modal');
    const widgetSettingsTitle = document.getElementById('widget-settings-modal-title');
    const widgetSettingsIndexInput = document.getElementById('widget-settings-index');
    const widgetSettingsWidthInput = document.getElementById('widget-settings-width');
    const widgetSettingsHeightInput = document.getElementById('widget-settings-height');
    const widgetDimensionsForm = document.getElementById('widget-dimensions-form');

    // Function to show the widget settings modal
    function showWidgetSettingsModal(widgetName, widgetIndex, currentWidth, currentHeight) {
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

    // Close widget settings modal listeners
    closeWidgetSettingsModalBtn.addEventListener('click', function() {
        widgetSettingsModalOverlay.classList.remove('active');
    });
    widgetSettingsModalOverlay.addEventListener('click', function(e) {
        if (e.target === widgetSettingsModalOverlay) {
            widgetSettingsModalOverlay.classList.remove('active');
        }
    });

    // Handle submission of widget dimensions form
    widgetDimensionsForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const widgetIndex = widgetSettingsIndexInput.value;
        const newWidth = widgetSettingsWidthInput.value;
        const newHeight = widgetSettingsHeightInput.value;

        // Check if "Show All Widgets" mode is active before submitting
        const showAllWidgetsToggle = document.getElementById('show_all_available_widgets');
        if (showAllWidgetsToggle && showAllWidgetsToggle.checked) {
            showMessageModal('Information', 'Widget dimension adjustment is disabled in "Show All Widgets" mode.');
            widgetSettingsModalOverlay.classList.remove('active'); // Close settings modal
            return;
        }

        submitActionForm('update_widget_dimensions', {
            widget_index: widgetIndex,
            new_width: newWidth,
            new_height: newHeight
        });
    });


    // --- Widget Actions (delegated listener on document.body) ---
    const mainContent = document.getElementById('widget-container');
    const expandedOverlay = document.getElementById('widget-expanded-overlay');

    document.body.addEventListener('click', function(e) {
        const target = e.target.closest('.widget-action');

        if (target) {
            const widget = target.closest('.widget');
            if (!widget) return;

            // Handle Settings Action (Cog Icon)
            if (target.classList.contains('action-settings')) {
                const widgetName = widget.querySelector('.widget-title span').textContent;
                const widgetIndex = widget.dataset.widgetIndex;
                const currentWidth = widget.dataset.currentWidth;
                const currentHeight = widget.dataset.currentHeight;

                showWidgetSettingsModal(widgetName, widgetIndex, currentWidth, currentHeight);

            }
            // Handle Expand/Shrink Action (Expand Icon)
            else if (target.classList.contains('action-expand')) {
                toggleWidgetExpansion(widget);
            }
            // Handle Remove Widget Action (Times Icon)
            else if (target.classList.contains('remove-widget')) {
                // If the remove button is disabled (due to 'Show All Widgets' mode), do nothing
                if (target.classList.contains('disabled')) {
                    showMessageModal('Information', 'This widget cannot be removed in "Show All Widgets" mode.');
                    return;
                }

                const widgetIndex = target.getAttribute('data-index');
                if (widget.classList.contains('maximized')) {
                    toggleWidgetExpansion(widget); // Minimize if maximized
                } else if (widgetIndex !== null && widgetIndex !== undefined) {
                    showMessageModal(
                        'Confirm Removal',
                        'Are you sure you want to remove this widget from the dashboard?',
                        function() {
                            submitActionForm('remove_widget', { widget_index: widgetIndex });
                        }
                    );
                }
            }
        }
    });

    // Helper function to toggle widget expansion state
    function toggleWidgetExpansion(widget) {
        const widgetPlaceholder = widget.querySelector('.widget-placeholder');
        const expandIcon = widget.querySelector('.action-expand i');

        if (!widget.classList.contains('maximized')) {
            // MAXIMIZE Logic:
            widgetPlaceholder.dataset.originalParentId = widget.parentNode.id;
            widgetPlaceholder.dataset.originalIndex = Array.from(widget.parentNode.children).indexOf(widget);
            widget.classList.add('maximized');
            document.body.classList.add('expanded-active');
            expandedOverlay.classList.add('active');
            expandedOverlay.appendChild(widget);
            widgetPlaceholder.style.display = 'block';
            if (expandIcon) expandIcon.classList.replace('fa-expand', 'fa-compress');

            // NEW: If the expanded widget is the IDE, initialize/refresh its file tree
            if (widget.dataset.widgetId === 'ide') {
                initializeIdeWidget(widget);
            }

        } else {
            // MINIMIZE Logic:
            const originalParent = document.getElementById(widgetPlaceholder.dataset.originalParentId);
            const originalIndex = parseInt(widgetPlaceholder.dataset.originalIndex);

            if (originalParent && originalParent.children[originalIndex]) {
                originalParent.insertBefore(widget, originalParent.children[originalIndex]);
            } else if (originalParent) {
                originalParent.appendChild(widget);
            } else {
                console.error("Original parent not found for widget ID:", widget.id);
                mainContent.appendChild(widget);
            }

            widget.classList.remove('maximized');
            document.body.classList.remove('expanded-active');
            expandedOverlay.classList.remove('active');
            widgetPlaceholder.style.display = 'none';
            if (expandIcon) expandIcon.classList.replace('fa-compress', 'fa-expand');
        }
    }

    // Close expanded widget when clicking on the expanded overlay
    expandedOverlay.addEventListener('click', function(e) {
        if (e.target === expandedOverlay) {
            const activeMaximizedWidget = document.querySelector('.widget.maximized');
            if (activeMaximizedWidget) {
                toggleWidgetExpansion(activeMaximizedWidget);
            }
        }
    });

    // --- Drag and drop functionality ---
    document.body.addEventListener('dragstart', function(e) {
        const target = e.target.closest('.widget-item');
        if (target) {
            e.dataTransfer.setData('text/plain', target.dataset.widgetId);
        }
    });

    mainContent.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.backgroundColor = 'rgba(63, 114, 175, 0.1)';
    });

    mainContent.addEventListener('dragleave', function() {
        this.style.backgroundColor = '';
    });

    mainContent.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.backgroundColor = '';

        const widgetId = e.dataTransfer.getData('text/plain');
        const newWidgetBtn = document.getElementById('new-widget-btn');
        if (newWidgetBtn && newWidgetBtn.classList.contains('disabled')) {
            showMessageModal('Information', 'Adding widgets is disabled in "Show All Widgets" mode.');
            return;
        }
        submitActionForm('add_widget', { widget_id: widgetId });
    });

    // Helper function to submit POST forms dynamically (for full page reloads)
    function submitActionForm(actionType, data = {}) {
        const form = document.createElement('form');
        form.method = 'post';
        form.style.display = 'none';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action_type';
        actionInput.value = actionType;
        form.appendChild(actionInput);

        for (const key in data) {
            if (data.hasOwnProperty(key)) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = key;
                hiddenInput.value = data[key];
                form.appendChild(hiddenInput);
            }
        }

        document.body.appendChild(form);
        console.log(`Submitting form for action: ${actionType}`);
        for (let pair of new FormData(form).entries()) {
            console.log(`  ${pair[0]}: ${pair[1]}`);
        }
        form.submit();
    }

    // --- NEW: AJAX Request Helper ---
    /**
     * Sends an AJAX POST request to the server.
     * @param {string} ajaxAction - The specific action for the PHP AJAX handler.
     * @param {Object} data - Data to send with the request.
     * @returns {Promise<Object>} A promise that resolves with the JSON response.
     */
    async function sendAjaxRequest(ajaxAction, data = {}) {
        const formData = new FormData();
        formData.append('ajax_action', ajaxAction);
        for (const key in data) {
            formData.append(key, data[key]);
        }

        try {
            const response = await fetch('index.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // Custom header to identify AJAX requests in PHP
                },
                body: formData
            });
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
            }
            return await response.json();
        } catch (error) {
            console.error('AJAX Error:', error);
            showMessageModal('Error', `AJAX request failed: ${error.message}`);
            return { status: 'error', message: error.message };
        }
    }


    // --- IDE Widget Specific Logic ---
    let currentIdePath = '.'; // Current directory being viewed in the IDE
    let currentEditingFile = null; // Current file being edited

    const ideFileTree = document.getElementById('ide-file-tree');
    const ideCurrentPathDisplay = document.getElementById('ide-current-path');
    const ideCodeEditor = document.getElementById('ide-code-editor');
    const ideFileNameDisplay = document.getElementById('ide-current-file-name');
    const ideFileStatus = document.getElementById('ide-file-status');
    const ideSaveBtn = document.getElementById('ide-save-btn');

    // Function to update file status (Saved, Unsaved)
    function setFileStatus(status) {
        // Guard against IDE elements not being present (e.g., if IDE widget isn't on dashboard)
        if (!ideFileStatus) return; 

        ideFileStatus.classList.remove('ide-status-saved', 'ide-status-unsaved');
        if (status === 'saved') {
            ideFileStatus.textContent = 'Saved';
            ideFileStatus.classList.add('ide-status-saved');
        } else if (status === 'unsaved') {
            ideFileStatus.textContent = 'Unsaved Changes';
            ideFileStatus.classList.add('ide-status-unsaved');
        } else {
            ideFileStatus.textContent = ''; // Clear status
        }
    }

    // Initialize IDE on widget expansion
    function initializeIdeWidget(widget) {
        // Ensure elements exist before trying to use them
        // The IDE elements are only available when the IDE widget is expanded.
        if (!widget.querySelector('#ide-file-tree') || !widget.querySelector('#ide-code-editor') || !widget.querySelector('#ide-save-btn')) {
            console.warn("IDE elements not found inside the expanded widget. Skipping IDE initialization.");
            return;
        }
        
        // Re-assign local consts to elements within the expanded widget
        // This is crucial because when the widget is moved to expandedOverlay,
        // the original element references might become stale or point to elements
        // that are no longer in the active DOM part.
        const currentIdeFileTree = widget.querySelector('#ide-file-tree');
        const currentIdeCurrentPathDisplay = widget.querySelector('#ide-current-path');
        const currentIdeCodeEditor = widget.querySelector('#ide-code-editor');
        const currentIdeFileNameDisplay = widget.querySelector('#ide-current-file-name');
        const currentIdeFileStatus = widget.querySelector('#ide-file-status');
        const currentIdeSaveBtn = widget.querySelector('#ide-save-btn');

        // Reset editor state
        currentIdeCodeEditor.value = '';
        currentIdeCodeEditor.readOnly = true;
        currentIdeFileNameDisplay.textContent = 'No file selected';
        currentIdeSaveBtn.disabled = true;
        setFileStatus('');
        currentEditingFile = null;

        // Load root directory initially
        // Use the local reference to loadIdeFileTree and currentIdePathDisplay
        loadIdeFileTreeInternal(currentIdeFileTree, currentIdeCurrentPathDisplay, currentIdeCodeEditor, currentIdeFileNameDisplay, currentIdeSaveBtn, currentIdeFileStatus, '.');
    }

    // This internal function helps with re-scoping elements after widget expansion
    async function loadIdeFileTreeInternal(fileTreeEl, pathDisplayEl, codeEditorEl, fileNameDisplayEl, saveBtnEl, fileStatusEl, path) {
        fileTreeEl.innerHTML = '<li class="ide-loading-indicator"><i class="fas fa-spinner fa-spin"></i> Loading files...</li>';
        pathDisplayEl.textContent = path === '' ? '/' : `/${path}`; // Display root as /

        const response = await sendAjaxRequest('ide_list_files', { path: path });

        if (response.status === 'success' && response.files) {
            currentIdePath = response.current_path; // Update current path from server response
            pathDisplayEl.textContent = currentIdePath === '' ? '/' : `/${currentIdePath}`;

            fileTreeEl.innerHTML = ''; // Clear loading indicator
            response.files.forEach(item => {
                const li = document.createElement('li');
                li.classList.add('ide-file-tree-item');
                li.dataset.path = item.path;
                li.dataset.type = item.type;
                li.dataset.name = item.name; // Store name for display later

                let icon = '';
                if (item.type === 'dir') {
                    icon = '<i class="fas fa-folder"></i>';
                } else {
                    icon = '<i class="fas fa-file"></i>';
                }

                li.innerHTML = `${icon} <span>${item.name}</span>`;
                if (!item.is_writable) {
                     li.classList.add('ide-item-read-only');
                     li.title = 'Not writable';
                }

                fileTreeEl.appendChild(li);
            });
        } else {
            fileTreeEl.innerHTML = `<li class="ide-error-indicator"><i class="fas fa-exclamation-triangle"></i> Error: ${response.message}</li>`;
            showMessageModal('IDE Error', `Failed to load files: ${response.message}`);
        }
    }

    // Handle file tree clicks (delegated to document.body because IDE widget is dynamic)
    document.body.addEventListener('click', async function(e) {
        const item = e.target.closest('.ide-file-tree-item');
        // Ensure the clicked item is part of an expanded IDE widget
        if (!item || !item.closest('.widget.maximized[data-widget-id="ide"]')) return;

        // Re-get elements within the context of the active IDE widget
        const currentIdeCodeEditor = item.closest('.ide-container').querySelector('#ide-code-editor');
        const currentIdeFileNameDisplay = item.closest('.ide-container').querySelector('#ide-current-file-name');
        const currentIdeSaveBtn = item.closest('.ide-container').querySelector('#ide-save-btn');
        const currentIdeFileStatus = item.closest('.ide-container').querySelector('#ide-file-status');
        const currentIdeFileTree = item.closest('.ide-container').querySelector('#ide-file-tree');
        const currentIdeCurrentPathDisplay = item.closest('.ide-container').querySelector('#ide-current-path');

        const path = item.dataset.path;
        const type = item.dataset.type;
        const isWritable = !item.classList.contains('ide-item-read-only');

        if (type === 'dir') {
            loadIdeFileTreeInternal(currentIdeFileTree, currentIdeCurrentPathDisplay, currentIdeCodeEditor, currentIdeFileNameDisplay, currentIdeSaveBtn, currentIdeFileStatus, path);
            currentEditingFile = null; // Clear current file when navigating directories
            currentIdeCodeEditor.value = '';
            currentIdeCodeEditor.readOnly = true;
            currentIdeFileNameDisplay.textContent = 'No file selected';
            currentIdeSaveBtn.disabled = true;
            setFileStatus.call({ideFileStatus: currentIdeFileStatus}, ''); // Call setFileStatus with correct context
        } else if (type === 'file') {
            if (!isWritable) {
                showMessageModal('Read-Only File', `"${item.dataset.name}" is not writable.`);
                currentIdeCodeEditor.readOnly = true;
                currentIdeSaveBtn.disabled = true;
            } else {
                currentIdeCodeEditor.readOnly = false;
                currentIdeSaveBtn.disabled = false;
            }

            currentIdeFileNameDisplay.textContent = item.dataset.name;
            setFileStatus.call({ideFileStatus: currentIdeFileStatus}, 'saved'); // Assume saved until changes are made

            const response = await sendAjaxRequest('ide_read_file', { path: path });
            if (response.status === 'success' && typeof response.content === 'string') {
                currentIdeCodeEditor.value = response.content;
                currentEditingFile = path;
            } else {
                currentIdeCodeEditor.value = `Error loading file: ${response.message}`;
                currentEditingFile = null;
                currentIdeCodeEditor.readOnly = true;
                currentIdeSaveBtn.disabled = true;
                setFileStatus.call({ideFileStatus: currentIdeFileStatus}, '');
                showMessageModal('IDE Error', `Failed to read file: ${response.message}`);
            }
        }
    });


    // Handle editor content changes (mark as unsaved) - delegated
    document.body.addEventListener('input', function(e) {
        const editor = e.target.closest('.ide-code-editor');
        if (editor && editor.closest('.widget.maximized[data-widget-id="ide"]')) {
            const currentIdeFileStatus = editor.closest('.ide-container').querySelector('#ide-file-status');
            const currentIdeSaveBtn = editor.closest('.ide-container').querySelector('#ide-save-btn');
            
            setFileStatus.call({ideFileStatus: currentIdeFileStatus}, 'unsaved');
            if (currentIdeSaveBtn) currentIdeSaveBtn.disabled = editor.readOnly; // Re-enable save if it wasn't already
        }
    });


    // Handle save button click - delegated
    document.body.addEventListener('click', async function(e) {
        const saveBtn = e.target.closest('#ide-save-btn');
        if (!saveBtn || !saveBtn.closest('.widget.maximized[data-widget-id="ide"]')) return;

        const currentIdeCodeEditor = saveBtn.closest('.ide-container').querySelector('#ide-code-editor');
        const currentIdeFileStatus = saveBtn.closest('.ide-container').querySelector('#ide-file-status');

        if (!currentEditingFile || currentIdeCodeEditor.readOnly) {
            showMessageModal('Action Not Allowed', 'No file selected or file is read-only.');
            return;
        }

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        const response = await sendAjaxRequest('ide_save_file', {
            path: currentEditingFile,
            content: currentIdeCodeEditor.value
        });

        if (response.status === 'success') {
            setFileStatus.call({ideFileStatus: currentIdeFileStatus}, 'saved');
            showMessageModal('Success', response.message);
        } else {
            setFileStatus.call({ideFileStatus: currentIdeFileStatus}, 'unsaved'); // Remain unsaved if save failed
            showMessageModal('Error', `Save failed: ${response.message}`);
        }
        saveBtn.innerHTML = '<i class="fas fa-save"></i> Save';
        saveBtn.disabled = currentIdeCodeEditor.readOnly; // Keep disabled if read-only
    });


    // --- Other Global Buttons ---
    document.getElementById('refresh-btn').addEventListener('click', function() {
        location.reload();
    });

    document.getElementById('theme-settings-btn').addEventListener('click', function() {
        settingsPanel.classList.add('active');
        settingsOverlay.style.display = 'block';
    });

    // Handle form submission for global update_settings (from settings panel)
    const settingsForm = settingsPanel.querySelector('form');
    if (settingsForm) {
        settingsForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(settingsForm);
            const dataToSubmit = {};
            for (const [key, value] of formData.entries()) {
                if (key === 'enable_animations' || key === 'show_all_available_widgets') {
                    dataToSubmit[key] = settingsForm.elements[key].checked ? '1' : '0';
                } else {
                    dataToSubmit[key] = value;
                }
            }
            submitActionForm('update_settings', dataToSubmit);
        });
    }

    // Disable/Enable Add Widget button based on 'Show All Widgets' state
    const showAllWidgetsToggle = document.getElementById('show_all_available_widgets');
    const newWidgetBtn = document.getElementById('new-widget-btn');
    const widgetSelect = document.getElementById('widget_select');
    const addWidgetToDashboardBtn = settingsPanel.querySelector('button[name="add_widget"]');

    function updateAddRemoveButtonStates() {
        if (showAllWidgetsToggle && newWidgetBtn && widgetSelect && addWidgetToDashboardBtn) {
            const isDisabled = showAllWidgetsToggle.checked;
            newWidgetBtn.classList.toggle('disabled', isDisabled);
            newWidgetBtn.disabled = isDisabled;
            widgetSelect.disabled = isDisabled;
            addWidgetToDashboardBtn.disabled = isDisabled;

            // Also update widget settings modal's inputs if it's open
            if (widgetSettingsModalOverlay.classList.contains('active')) {
                widgetSettingsWidthInput.disabled = isDisabled;
                widgetSettingsHeightInput.disabled = isDisabled;
                widgetDimensionsForm.querySelector('button[type="submit"]').disabled = isDisabled;
                widgetDimensionsForm.querySelector('button[type="submit"]').textContent = isDisabled ? 'Disabled in Show All Mode' : 'Save Dimensions';
            }
        }
    }

    if (showAllWidgetsToggle) {
        showAllWidgetsToggle.addEventListener('change', updateAddRemoveButtonStates);
    }
    updateAddRemoveButtonStates(); // Initial state update on load

    // --- NEW: Delete Settings JSON Button Logic ---
    const deleteSettingsJsonBtn = document.getElementById('delete-settings-json-btn');
    if (deleteSettingsJsonBtn) {
        deleteSettingsJsonBtn.addEventListener('click', function() {
            showMessageModal(
                'Confirm Reset',
                'Are you sure you want to delete all dashboard settings and reset to default? This cannot be undone.',
                async function() {
                    const response = await sendAjaxRequest('delete_settings_json');
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
});
