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

    settingsOverlay.addEventListener('click', function(e) {
        if (e.target === settingsOverlay) {
            settingsPanel.classList.remove('active');
            this.style.display = 'none';
        }
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


    // --- Widget Settings Modal Elements (for individual widget settings from its header) ---
    const widgetSettingsModalOverlay = document.getElementById('widget-settings-modal-overlay');
    const closeWidgetSettingsModalBtn = document.getElementById('close-widget-settings-modal');
    const widgetSettingsTitle = document.getElementById('widget-settings-modal-title');
    const widgetSettingsIndexInput = document.getElementById('widget-settings-index');
    const widgetSettingsWidthInput = document.getElementById('widget-settings-width');
    const widgetSettingsHeightInput = document.getElementById('widget-settings-height');
    const widgetDimensionsForm = document.getElementById('widget-dimensions-form');

    // Function to show the individual widget settings modal
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
        
        // Use AJAX to update a single widget's dimensions
        sendAjaxRequest('update_single_widget_dimensions', {
            widget_index: widgetIndex,
            new_width: newWidth,
            new_height: newHeight
        }).then(response => {
            if (response.status === 'success') {
                showMessageModal('Success', response.message, () => location.reload()); // Reload on success
            } else {
                showMessageModal('Error', response.message);
            }
            widgetSettingsModalOverlay.classList.remove('active');
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

            // Handle Settings Action (Cog Icon) for individual widget
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
            // Ensure the placeholder is created and correctly positioned relative to its original parent
            if (!widgetPlaceholder) {
                console.error("Widget placeholder not found!");
                return; // Cannot proceed without placeholder
            }
            widgetPlaceholder.dataset.originalParentId = widget.parentNode.id;
            widgetPlaceholder.dataset.originalIndex = Array.from(widget.parentNode.children).indexOf(widget);
            widgetPlaceholder.style.display = 'block'; // Make placeholder visible to hold space

            widget.classList.add('maximized');
            document.body.classList.add('expanded-active');
            expandedOverlay.classList.add('active');
            expandedOverlay.appendChild(widget); // Move widget to overlay
            
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
            if (widgetPlaceholder) { // Check if placeholder exists before trying to hide
                widgetPlaceholder.style.display = 'none';
            }
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

    // --- Drag and drop functionality for adding widgets from sidebar ---
    document.body.addEventListener('dragstart', function(e) {
        const target = e.target.closest('.widget-item'); // From sidebar
        if (target) {
            e.dataTransfer.setData('text/plain', target.dataset.widgetId);
            e.dataTransfer.effectAllowed = 'copy'; // Indicate copy operation
        }
        // Also handle dragstart for reordering existing widgets
        const widgetOnDashboard = e.target.closest('.widget'); // From dashboard
        if (widgetOnDashboard && widgetOnDashboard.parentNode === mainContent) { // Ensure it's a direct child of main-content
            e.dataTransfer.setData('text/plain', widgetOnDashboard.dataset.widgetId);
            e.dataTransfer.effectAllowed = 'move'; // Indicate move operation
            widgetOnDashboard.classList.add('dragging'); // Add visual feedback for dragging
            draggedWidget = widgetOnDashboard; // Store reference to the dragged widget
        }
    });

    // Reset dragging class on dragend
    document.body.addEventListener('dragend', function(e) {
        if (draggedWidget) {
            draggedWidget.classList.remove('dragging');
            draggedWidget = null;
        }
    });


    // Drag over main content area for adding new widgets
    mainContent.addEventListener('dragover', function(e) {
        e.preventDefault(); // Allow drop
        const isAddingNewWidget = e.dataTransfer.types.includes('text/plain') && e.dataTransfer.effectAllowed === 'copy';
        const isReorderingExisting = e.dataTransfer.types.includes('text/plain') && e.dataTransfer.effectAllowed === 'move';

        if (isAddingNewWidget) {
            this.style.backgroundColor = 'rgba(63, 114, 175, 0.1)'; // Highlight for adding
        } else if (isReorderingExisting) {
            // Highlight current target for reordering
            const targetWidget = e.target.closest('.widget');
            if (targetWidget && targetWidget !== draggedWidget) {
                // Determine if dropping before or after the target widget
                const boundingBox = targetWidget.getBoundingClientRect();
                const offset = e.clientY - boundingBox.top;
                if (offset < boundingBox.height / 2) {
                    targetWidget.style.borderTop = '2px solid var(--accent)';
                    targetWidget.style.borderBottom = '';
                } else {
                    targetWidget.style.borderBottom = '2px solid var(--accent)';
                    targetWidget.style.borderTop = '';
                }
            }
            // Clear previous highlights
            mainContent.querySelectorAll('.widget').forEach(widget => {
                if (widget !== targetWidget) {
                    widget.style.borderTop = '';
                    widget.style.borderBottom = '';
                }
            });
        }
    });

    mainContent.addEventListener('dragleave', function() {
        this.style.backgroundColor = ''; // Remove highlight for adding
        // Clear all reordering highlights
        mainContent.querySelectorAll('.widget').forEach(widget => {
            widget.style.borderTop = '';
            widget.style.borderBottom = '';
        });
    });

    // Drop handler for adding new widgets AND reordering existing ones
    let draggedWidget = null; // Global variable to store the currently dragged widget on the dashboard

    mainContent.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.backgroundColor = ''; // Remove highlight for adding
        // Clear all reordering highlights
        mainContent.querySelectorAll('.widget').forEach(widget => {
            widget.style.borderTop = '';
            widget.style.borderBottom = '';
        });

        const widgetId = e.dataTransfer.getData('text/plain');
        const newWidgetBtn = document.getElementById('new-widget-btn');

        // Check if it's an "add new widget" drop
        if (e.dataTransfer.effectAllowed === 'copy' && newWidgetBtn && newWidgetBtn.classList.contains('disabled')) {
            showMessageModal('Information', 'Adding widgets is disabled in "Show All Widgets" mode.');
            return;
        } else if (e.dataTransfer.effectAllowed === 'copy') {
            // This is a drop from the widget library (add new widget)
            submitActionForm('add_widget', { widget_id: widgetId });
        } else if (e.dataTransfer.effectAllowed === 'move' && draggedWidget) {
            // This is a drop for reordering an existing widget
            const targetWidget = e.target.closest('.widget');

            if (targetWidget && targetWidget !== draggedWidget) {
                const boundingBox = targetWidget.getBoundingClientRect();
                const offset = e.clientY - boundingBox.top;

                if (offset < boundingBox.height / 2) {
                    // Drop before targetWidget
                    mainContent.insertBefore(draggedWidget, targetWidget);
                } else {
                    // Drop after targetWidget
                    mainContent.insertBefore(draggedWidget, targetWidget.nextSibling);
                }
                // Save the new order
                saveWidgetOrder();
            } else if (!targetWidget && draggedWidget) {
                // Dropped into empty space or at the end
                mainContent.appendChild(draggedWidget);
                saveWidgetOrder();
            }
        }
    });

    // Function to save the current order of widgets on the dashboard
    async function saveWidgetOrder() {
        const orderedWidgetIds = Array.from(mainContent.children)
                                    .filter(child => child.classList.contains('widget'))
                                    .map(widget => widget.dataset.widgetId);
        
        if (orderedWidgetIds.length > 0) {
            const response = await sendAjaxRequest('update_widget_order', {
                order: JSON.stringify(orderedWidgetIds) // Send as JSON string
            });

            if (response.status === 'success') {
                console.log('Widget order saved successfully.');
                // Optional: Show a small temporary success message on the dashboard
                // showMessageModal('Order Saved', 'Widget order updated successfully.', null, 1500);
            } else {
                console.error('Failed to save widget order:', response.message);
                showMessageModal('Error', 'Failed to save widget order: ' + response.message);
            }
        }
    }


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

    // --- AJAX Request Helper ---
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

    // Function to update file status (Saved, Unsaved)
    // This function needs `this.ideFileStatus` to be bound to the correct element
    function setFileStatus(status) {
        const ideFileStatusEl = this.ideFileStatus || document.getElementById('ide-file-status'); // Fallback for global context if called incorrectly
        if (!ideFileStatusEl) return; 

        ideFileStatusEl.classList.remove('ide-status-saved', 'ide-status-unsaved');
        if (status === 'saved') {
            ideFileStatusEl.textContent = 'Saved';
            ideFileStatusEl.classList.add('ide-status-saved');
        } else if (status === 'unsaved') {
            ideFileStatusEl.textContent = 'Unsaved Changes';
            ideFileStatusEl.classList.add('ide-status-unsaved');
        } else {
            ideFileStatusEl.textContent = ''; // Clear status
        }
    }

    // Initialize IDE on widget expansion
    function initializeIdeWidget(widget) {
        // Get elements within the *specific* expanded widget
        const ideFileTreeEl = widget.querySelector('#ide-file-tree');
        const ideCurrentPathDisplayEl = widget.querySelector('#ide-current-path');
        const ideCodeEditorEl = widget.querySelector('#ide-code-editor');
        const ideFileNameDisplayEl = widget.querySelector('#ide-current-file-name');
        const ideFileStatusEl = widget.querySelector('#ide-file-status');
        const ideSaveBtnEl = widget.querySelector('#ide-save-btn');

        if (!ideFileTreeEl || !ideCodeEditorEl || !ideSaveBtnEl || !ideFileStatusEl || !ideFileNameDisplayEl || !ideCurrentPathDisplayEl) {
            console.warn("IDE elements not found inside the expanded widget. Skipping IDE initialization.");
            return;
        }

        // Reset editor state
        ideCodeEditorEl.value = '';
        ideCodeEditorEl.readOnly = true;
        ideFileNameDisplayEl.textContent = 'No file selected';
        ideSaveBtnEl.disabled = true;
        setFileStatus.call({ideFileStatus: ideFileStatusEl}, ''); // Call setFileStatus with correct context
        currentEditingFile = null;

        // Load root directory initially
        loadIdeFileTreeInternal(ideFileTreeEl, ideCurrentPathDisplayEl, ideCodeEditorEl, ideFileNameDisplayEl, ideSaveBtnEl, ideFileStatusEl, '.');
    }

    // This internal function helps with re-scoping elements after widget expansion
    async function loadIdeFileTreeInternal(fileTreeEl, pathDisplayEl, codeEditorEl, fileNameDisplayEl, saveBtnEl, fileStatusEl, path) {
        fileTreeEl.innerHTML = '<li class="ide-loading-indicator"><i class="fas fa-spinner fa-spin"></i> Loading files...</li>';
        pathDisplayEl.textContent = path === '.' ? '/' : `/${path}`; // Display root as /

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

                // Append the HTML string to the list item
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
        const ideContainer = item.closest('.ide-container');
        const currentIdeCodeEditor = ideContainer.querySelector('#ide-code-editor');
        const currentIdeFileNameDisplay = ideContainer.querySelector('#ide-current-file-name');
        const currentIdeSaveBtn = ideContainer.querySelector('#ide-save-btn');
        const currentIdeFileStatus = ideContainer.querySelector('#ide-file-status');
        const currentIdeFileTree = ideContainer.querySelector('#ide-file-tree');
        const currentIdeCurrentPathDisplay = ideContainer.querySelector('#ide-current-path');

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

    // NEW: Functionality for the "+ New Widget" button in the header
    const newWidgetHeaderBtn = document.getElementById('new-widget-btn');
    if (newWidgetHeaderBtn) {
        newWidgetHeaderBtn.addEventListener('click', function() {
            settingsPanel.classList.add('active'); // Open settings panel
            settingsOverlay.style.display = 'block';
            // Optionally, scroll to the "Add New Widget" section if it has an ID
            const addWidgetSection = settingsPanel.querySelector('.settings-group h3:contains("Add New Widget")');
            if (addWidgetSection) {
                addWidgetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }


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
    // const newWidgetBtn = document.getElementById('new-widget-btn'); // Already defined above
    const widgetSelect = document.getElementById('widget_select');
    const addWidgetToDashboardBtn = settingsPanel.querySelector('button[name="add_widget"]');

    function updateAddRemoveButtonStates() {
        if (showAllWidgetsToggle && newWidgetHeaderBtn && widgetSelect && addWidgetToDashboardBtn) {
            const isDisabled = showAllWidgetsToggle.checked;
            // newWidgetHeaderBtn.classList.toggle('disabled', isDisabled); // This button now opens settings, not adds directly
            // newWidgetHeaderBtn.disabled = isDisabled;
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

    // --- Delete Settings JSON Button Logic ---
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

    // --- Widget Management Panel Logic ---
    const widgetManagementNavItem = document.getElementById('widget-management-nav-item');
    const widgetManagementModalOverlay = document.getElementById('widget-management-modal-overlay');
    const closeWidgetManagementModalBtn = document.getElementById('close-widget-management-modal');
    const widgetManagementTableBody = document.getElementById('widget-management-table-body');
    const saveWidgetManagementChangesBtn = document.getElementById('save-widget-management-changes-btn');

    if (widgetManagementNavItem) {
        widgetManagementNavItem.addEventListener('click', async function() {
            widgetManagementModalOverlay.classList.add('active');
            await loadWidgetManagementTable();
        });
    }

    if (closeWidgetManagementModalBtn) {
        closeWidgetManagementModalBtn.addEventListener('click', function() {
            widgetManagementModalOverlay.classList.remove('active');
        });
    }

    if (widgetManagementModalOverlay) {
        widgetManagementModalOverlay.addEventListener('click', function(e) {
            if (e.target === widgetManagementModalOverlay) {
                widgetManagementModalOverlay.classList.remove('active');
            }
        });
    }

    async function loadWidgetManagementTable() {
        widgetManagementTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading widgets...</td></tr>';
        
        const response = await sendAjaxRequest('get_active_widgets_data');

        if (response.status === 'success' && response.widgets) {
            widgetManagementTableBody.innerHTML = ''; // Clear loading message
            if (response.widgets.length === 0) {
                widgetManagementTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">No active widgets. Add some from Dashboard Settings!</td></tr>';
            } else {
                response.widgets.forEach(widget => {
                    const row = document.createElement('tr');
                    row.dataset.widgetIndex = widget.index; // Store the original index for saving
                    row.dataset.widgetId = widget.id; // Store widget ID for deactivation

                    row.innerHTML = `
                        <td><i class="fas fa-${widget.icon}"></i></td>
                        <td>${widget.name}</td>
                        <td>Active</td> <!-- All listed here are active -->
                        <td>
                            <input type="number" class="widget-width-input form-control-small" value="${widget.width}" min="1" max="3" data-original-width="${widget.width}">
                        </td>
                        <td>
                            <input type="number" class="widget-height-input form-control-small" value="${widget.height}" min="1" max="4" data-original-height="${widget.height}">
                        </td>
                        <td>
                            <span class="widget-management-status">Saved</span>
                        </td>
                        <td>
                            <button class="btn btn-danger btn-deactivate-widget" data-widget-id="${widget.id}">
                                <i class="fas fa-trash-alt"></i> Deactivate
                            </button>
                        </td>
                    `;
                    widgetManagementTableBody.appendChild(row);
                });

                // Add event listeners for input changes to mark as unsaved
                widgetManagementTableBody.querySelectorAll('.widget-width-input, .widget-height-input').forEach(input => {
                    input.addEventListener('input', function() {
                        const row = this.closest('tr');
                        const statusSpan = row.querySelector('.widget-management-status');
                        statusSpan.textContent = 'Unsaved';
                        statusSpan.style.color = 'var(--warning)';
                        saveWidgetManagementChangesBtn.disabled = false;
                    });
                });

                // Add event listeners for deactivate buttons
                widgetManagementTableBody.querySelectorAll('.btn-deactivate-widget').forEach(button => {
                    button.addEventListener('click', function() {
                        const widgetIdToDeactivate = this.dataset.widgetId;
                        showMessageModal(
                            'Confirm Deactivation',
                            `Are you sure you want to deactivate "${widgetIdToDeactivate}"? It will be removed from your dashboard.`,
                            async () => {
                                const response = await sendAjaxRequest('remove_widget_from_management', { widget_id: widgetIdToDeactivate });
                                if (response.status === 'success') {
                                    showMessageModal('Success', response.message + ' Reloading dashboard...', () => location.reload(true));
                                } else {
                                    showMessageModal('Error', response.message);
                                }
                            }
                        );
                    });
                });
            }
        } else {
            widgetManagementTableBody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--danger); padding: 20px;">Error loading widgets: ${response.message}</td></tr>`;
            showMessageModal('Error', `Failed to load widget data for management: ${response.message}`);
        }
        saveWidgetManagementChangesBtn.disabled = true; // Initially disabled
    }

    if (saveWidgetManagementChangesBtn) {
        saveWidgetManagementChangesBtn.addEventListener('click', async function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            const rows = widgetManagementTableBody.querySelectorAll('tr');
            let allSuccess = true;
            let messages = [];

            for (const row of rows) {
                const widgetIndex = row.dataset.widgetIndex;
                const widthInput = row.querySelector('.widget-width-input');
                const heightInput = row.querySelector('.widget-height-input');
                const statusSpan = row.querySelector('.widget-management-status');

                const newWidth = parseInt(widthInput.value, 10);
                const newHeight = parseInt(heightInput.value, 10);
                const originalWidth = parseInt(widthInput.dataset.originalWidth, 10);
                const originalHeight = parseInt(heightInput.dataset.originalHeight, 10);

                // Only save if dimensions have actually changed
                if (newWidth !== originalWidth || newHeight !== originalHeight) {
                    statusSpan.textContent = 'Saving...';
                    statusSpan.style.color = 'var(--info)';

                    const response = await sendAjaxRequest('update_single_widget_dimensions', {
                        widget_index: widgetIndex,
                        new_width: newWidth,
                        new_height: newHeight
                    });

                    if (response.status === 'success') {
                        statusSpan.textContent = 'Saved';
                        statusSpan.style.color = 'var(--success)';
                        widthInput.dataset.originalWidth = newWidth; // Update original data
                        heightInput.dataset.originalHeight = newHeight;
                    } else {
                        statusSpan.textContent = 'Error';
                        statusSpan.style.color = 'var(--danger)';
                        allSuccess = false;
                        messages.push(`Widget at index ${widgetIndex} failed to save: ${response.message}`);
                    }
                } else {
                    statusSpan.textContent = 'Saved';
                    statusSpan.style.color = 'inherit'; // Reset color for unchanged items
                }
            }

            this.innerHTML = '<i class="fas fa-save"></i> Save All Widget Changes';

            if (allSuccess) {
                showMessageModal('Success', 'All changes saved. Reloading dashboard...', () => location.reload(true));
            } else {
                showMessageModal('Partial Success/Error', 'Some changes could not be saved: ' + messages.join('. ') + ' Reloading dashboard...', () => location.reload(true));
            }
        });
    }

});
