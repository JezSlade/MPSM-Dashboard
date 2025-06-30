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
    const cancelMessageModalBtn = document.getElementById('cancel-message-modal'); // New cancel button

    function showMessageModal(title, message, confirmCallback = null, cancelCallback = null) {
        messageModalTitle.innerHTML = title; // Use innerHTML for potential icons in title
        messageModalContent.innerHTML = message; // Use innerHTML for potential HTML in message
        messageModalOverlay.classList.add('active');

        // Show/hide confirm/cancel buttons based on callbacks
        confirmMessageModalBtn.style.display = confirmCallback ? 'inline-block' : 'none';
        cancelMessageModalBtn.style.display = cancelCallback ? 'inline-block' : 'none';

        // Clear previous event listeners to prevent multiple calls
        const newConfirmBtn = confirmMessageModalBtn.cloneNode(true);
        confirmMessageModalBtn.parentNode.replaceChild(newConfirmBtn, confirmMessageModalBtn);
        const newCancelBtn = cancelMessageModalBtn.cloneNode(true);
        cancelMessageModalBtn.parentNode.replaceChild(newCancelBtn, cancelMessageModalBtn);
        const newCloseBtn = closeMessageModalBtn.cloneNode(true);
        closeMessageModalBtn.parentNode.replaceChild(newCloseBtn, closeMessageModalBtn);

        newConfirmBtn.addEventListener('click', function() {
            messageModalOverlay.classList.remove('active');
            if (confirmCallback) {
                confirmCallback();
            }
        });

        newCancelBtn.addEventListener('click', function() {
            messageModalOverlay.classList.remove('active');
            if (cancelCallback) {
                cancelCallback();
            }
        });

        newCloseBtn.addEventListener('click', function() {
            messageModalOverlay.classList.remove('active');
        });
    }

    // --- Widget Settings Modal Elements (for individual widget settings from its header) ---
    // This modal is now deprecated in favor of the consolidated Widget Management Modal
    // Keeping the elements and functions for now, but they won't be actively used by the UI.
    const widgetSettingsModalOverlay = document.getElementById('widget-settings-modal-overlay');
    const closeWidgetSettingsModalBtn = document.getElementById('close-widget-settings-modal');
    const widgetSettingsTitle = document.getElementById('widget-settings-modal-title');
    const widgetSettingsIndexInput = document.getElementById('widget-settings-index');
    const widgetSettingsWidthInput = document.getElementById('widget-settings-width');
    const widgetSettingsHeightInput = document.getElementById('widget-settings-height');
    const widgetDimensionsForm = document.getElementById('widget-dimensions-form');

    // Function to show the individual widget settings modal (now largely unused)
    function showWidgetSettingsModal(widgetName, widgetIndex, currentWidth, currentHeight) {
        widgetSettingsTitle.textContent = `Settings for "${widgetName}"`;
        widgetSettingsIndexInput.value = widgetIndex;
        widgetSettingsWidthInput.value = currentWidth;
        widgetSettingsHeightInput.value = currentHeight;

        const showAllWidgetsToggle = document.getElementById('show-all-available-widgets');
        const isDisabled = showAllWidgetsToggle && showAllWidgetsToggle.checked;
        widgetSettingsWidthInput.disabled = isDisabled;
        widgetSettingsHeightInput.disabled = isDisabled;
        widgetDimensionsForm.querySelector('button[type="submit"]').disabled = isDisabled;
        widgetSettingsModalOverlay.classList.add('active');
    }

    // Close individual widget settings modal listeners
    if (closeWidgetSettingsModalBtn) {
        closeWidgetSettingsModalBtn.addEventListener('click', function() {
            widgetSettingsModalOverlay.classList.remove('active');
        });
    }
    if (widgetSettingsModalOverlay) {
        widgetSettingsModalOverlay.addEventListener('click', function(e) {
            if (e.target === widgetSettingsModalOverlay) {
                widgetSettingsModalOverlay.classList.remove('active');
            }
        });
    }

    // Handle submission of individual widget dimensions form (now largely unused)
    if (widgetDimensionsForm) {
        widgetDimensionsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const widgetIndex = widgetSettingsIndexInput.value;
            const newWidth = parseFloat(widgetSettingsWidthInput.value);
            const newHeight = parseFloat(widgetSettingsHeightInput.value);

            const showAllWidgetsToggle = document.getElementById('show-all-available-widgets');
            if (showAllWidgetsToggle && showAllWidgetsToggle.checked) {
                showMessageModal('Information', 'Widget dimension adjustment is disabled in "Show All Widgets" mode.');
                widgetSettingsModalOverlay.classList.remove('active');
                return;
            }
            
            sendAjaxRequest('update_single_widget_dimensions', {
                widget_index: widgetIndex,
                new_width: newWidth,
                new_height: newHeight
            }).then(response => {
                if (response.status === 'success') {
                    showMessageModal('Success', response.message, () => location.reload());
                } else {
                    showMessageModal('Error', response.message);
                }
                widgetSettingsModalOverlay.classList.remove('active');
            });
        });
    }


    // --- Widget Actions (delegated listener on document.body) ---
    const mainContent = document.querySelector('.grid-stack'); // Changed to .grid-stack
    const expandedOverlay = document.getElementById('widget-expanded-overlay');

    document.body.addEventListener('click', function(e) {
        const target = e.target.closest('.widget-action');

        if (target) {
            const widget = target.closest('.widget');
            if (!widget) return;

            // Handle Expand/Shrink Action (Expand Icon)
            if (target.classList.contains('action-expand')) {
                toggleWidgetExpansion(widget);
            }
        }
    });

    // Handle remove button click on the widget header (new direct button)
    document.body.addEventListener('click', function(e) {
        const removeBtn = e.target.closest('.remove-widget-btn');
        if (removeBtn && removeBtn.closest('.grid-stack-item-content')) {
            const widget = removeBtn.closest('.grid-stack-item-content');
            const widgetId = widget.dataset.gsId; // Use data-gs-id for GridStack items
            const widgetTitle = widget.querySelector('.widget-title span').textContent.trim(); // Get title from span

            showMessageModal(
                'Confirm Removal',
                `Are you sure you want to remove "${widgetTitle}" from the dashboard?`,
                function() {
                    // Remove from GridStack first
                    grid.removeWidget(widget.parentNode); // Pass the grid-stack-item element
                    // Then send AJAX request to persist removal
                    sendAjaxRequest('remove_widget', { widget_id: widgetId }).then(response => {
                        if (response.status === 'success') {
                            showMessageModal('Success', response.message + ' Reloading dashboard...', () => location.reload(true));
                        } else {
                            showMessageModal('Error', response.message);
                        }
                    });
                },
                function() { /* Cancel callback */ }
            );
        }
    });


    // Helper function to toggle widget expansion state (for IDE, etc.)
    function toggleWidgetExpansion(widget) {
        if (!widget) return;

        const gridStackItem = widget.parentNode; // The .grid-stack-item is the actual GridStack element

        if (!widget.classList.contains('maximized')) {
            // MAXIMIZE Logic:
            // Store original grid position and size
            gridStackItem.dataset.originalX = gridStackItem.dataset.gsX;
            gridStackItem.dataset.originalY = gridStackItem.dataset.gsY;
            gridStackItem.dataset.originalW = gridStackItem.dataset.gsW;
            gridStackItem.dataset.originalH = gridStackItem.dataset.gsH;

            // Remove from grid without destroying DOM element
            grid.removeWidget(gridStackItem, false);

            widget.classList.add('maximized');
            document.body.classList.add('expanded-active');
            expandedOverlay.classList.add('active');
            expandedOverlay.appendChild(gridStackItem); // Move grid-stack-item to overlay
            
            // If the expanded widget is the IDE, initialize/refresh its file tree
            if (widget.dataset.widgetId === 'ide') {
                initializeIdeWidget(widget);
            }

        } else {
            // MINIMIZE Logic:
            const originalX = parseInt(gridStackItem.dataset.originalX);
            const originalY = parseInt(gridStackItem.dataset.originalY);
            const originalW = parseInt(gridStackItem.dataset.originalW);
            const originalH = parseInt(gridStackItem.dataset.originalH);

            // Re-add to grid at original position
            grid.addWidget(gridStackItem, originalX, originalY, originalW, originalH);

            widget.classList.remove('maximized');
            document.body.classList.remove('expanded-active');
            expandedOverlay.classList.remove('active');
        }
    }

    // Close expanded widget when clicking on the expanded overlay
    if (expandedOverlay) {
        expandedOverlay.addEventListener('click', function(e) {
            if (e.target === expandedOverlay) {
                const activeMaximizedWidget = document.querySelector('.widget.maximized');
                if (activeMaximizedWidget) {
                    toggleWidgetExpansion(activeMaximizedWidget);
                }
            }
        });
    }

    // --- GridStack.js Initialization and Event Handling ---
    let grid = null; // Declare grid variable globally or in a scope accessible by event listeners

    function initializeGridStack() {
        if (grid) {
            grid.destroy(false); // Destroy existing grid if it exists, don't remove DOM elements
        }

        grid = GridStack.init({
            float: true,
            column: 12, // Use a 12-column grid for finer control (e.g., 0.5 units = 6 columns)
            cellHeight: '100px', // Base height for a 1.0 height unit
            margin: 20,
            disableResize: false,
            disableDrag: false,
            handle: '.widget-header', // Drag only by header
            resizeHandles: 'all' // Resize from all sides
        });

        // Event listener for when widgets are added, removed, or moved
        grid.on('change', function(event, items) {
            console.log('GridStack change event:', items);
            saveWidgetLayout(items);
        });

        // Add handler for adding widgets from the sidebar to the grid
        document.querySelectorAll('.widget-item').forEach(item => {
            item.draggable = true; // Ensure sidebar items are draggable
        });

        const gridStackEl = document.querySelector('.grid-stack');
        gridStackEl.addEventListener('dragover', function(e) {
            e.preventDefault(); // Allow drop
            e.dataTransfer.dropEffect = 'copy'; // Indicate a copy operation
            this.classList.add('grid-stack-drag-over'); // Visual feedback
        });

        gridStackEl.addEventListener('dragleave', function() {
            this.classList.remove('grid-stack-drag-over');
        });

        gridStackEl.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('grid-stack-drag-over');

            const widgetId = e.dataTransfer.getData('text/plain');
            if (widgetId) {
                // Check if the widget is already active on the grid
                const existingWidget = grid.engine.nodes.find(node => node.id === widgetId);
                if (existingWidget) {
                    showMessageModal('Info', `"${widgetId}" is already on the dashboard.`);
                    return;
                }

                // Get default dimensions from the data attributes of the dragged item from the sidebar
                const draggedItem = document.querySelector(`.widget-item[data-widget-id="${widgetId}"]`);
                const defaultWidth = parseFloat(draggedItem.dataset.gsW || 1);
                const defaultHeight = parseFloat(draggedItem.dataset.gsH || 1);

                // Add widget to the grid. GridStack will try to place it at the drop location,
                // or find the next available space.
                const newWidgetNode = grid.addWidget(
                    `<div class="grid-stack-item" data-gs-id="${widgetId}">
                        <div class="grid-stack-item-content widget" data-widget-id="${widgetId}">
                            <div class="widget-header">
                                <h4 class="widget-title">
                                    <i class="${draggedItem.querySelector('i').className}"></i>
                                    <span>${draggedItem.querySelector('.widget-name').textContent}</span>
                                </h4>
                                <div class="widget-actions">
                                    <button class="remove-widget-btn" data-widget-id="${widgetId}" title="Remove from Dashboard"><i class="fas fa-times"></i></button>
                                </div>
                            </div>
                            <div class="widget-content">
                                <!-- Content will be loaded by PHP on page reload -->
                                <div style="text-align: center; padding: 20px;">
                                    <i class="fas fa-spinner fa-spin"></i> Loading widget content...
                                </div>
                            </div>
                        </div>
                    </div>`,
                    { w: defaultWidth, h: defaultHeight }
                );

                // Send AJAX request to PHP to add the widget to the active list persistently
                sendAjaxRequest('add_widget', { widget_id: widgetId }).then(response => {
                    if (response.status === 'success') {
                        // Reload the page to get the actual widget content rendered by PHP
                        showMessageModal('Success', response.message + ' Reloading dashboard...', () => location.reload(true));
                    } else {
                        // If backend fails to add, remove from GridStack frontend
                        grid.removeWidget(newWidgetNode);
                        showMessageModal('Error', response.message);
                    }
                });
            }
        });
    }

    // Call initializeGridStack when DOM is ready
    initializeGridStack();


    // Function to save the current layout of widgets on the dashboard
    async function saveWidgetLayout(items) {
        const layoutData = [];
        grid.engine.nodes.forEach(node => {
            layoutData.push({
                id: node.id,
                x: node.x,
                y: node.y,
                width: node.w,
                height: node.h
            });
        });

        if (layoutData.length > 0) {
            const response = await sendAjaxRequest('update_widget_layout', {
                layout: JSON.stringify(layoutData)
            });

            if (response.status === 'success') {
                console.log('Widget layout saved successfully.');
            } else {
                console.error('Failed to save widget layout:', response.message);
                showMessageModal('Error', 'Failed to save widget layout: ' + response.message);
            }
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
            // Use submitActionForm for full page reload after settings update
            submitActionForm('update_settings', dataToSubmit);
        });
    }

    // Reset Theme to Default button
    const resetThemeBtn = document.getElementById('reset-theme-btn');
    if (resetThemeBtn) {
        resetThemeBtn.addEventListener('click', function() {
            showMessageModal(
                'Confirm Theme Reset',
                'Are you sure you want to reset all theme settings to default?',
                async function() {
                    // This will reset title, accent color, glass intensity, blur, animations, header icon
                    // by deleting the settings JSON and reloading.
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


    // Disable/Enable Add Widget button based on 'Show All Widgets' state
    const showAllWidgetsToggle = document.getElementById('show-all-available-widgets');
    const widgetSelect = document.getElementById('widget_select');
    const addWidgetToDashboardBtn = settingsPanel.querySelector('button[name="add_widget"]');

    function updateAddRemoveButtonStates() {
        if (showAllWidgetsToggle && widgetSelect && addWidgetToDashboardBtn) {
            const isDisabled = showAllWidgetsToggle.checked;
            widgetSelect.disabled = isDisabled;
            addWidgetToDashboardBtn.disabled = isDisabled;

            // Also update widget settings modal's inputs if it's open (deprecated modal)
            if (widgetSettingsModalOverlay && widgetSettingsModalOverlay.classList.contains('active')) {
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
    const manageWidgetsBtn = document.getElementById('manage-widgets-btn'); // Changed from nav-item to direct button
    const widgetManagementModalOverlay = document.getElementById('message-modal-overlay'); // Reusing general message modal
    const closeWidgetManagementModalBtn = document.getElementById('close-message-modal'); // Reusing general message modal close button
    const widgetManagementTableBody = document.getElementById('message-modal-content'); // Reusing content area
    const saveWidgetManagementChangesBtn = document.getElementById('confirm-message-modal'); // Reusing confirm button

    if (manageWidgetsBtn) {
        manageWidgetsBtn.addEventListener('click', async function() {
            // Use the general message modal for widget management
            showMessageModal(
                'Widget Management',
                '<div style="max-height: 400px; overflow-y: auto;"><table class="widget-management-table"><thead><tr><th>ID</th><th>Name</th><th>Icon</th><th>W</th><th>H</th><th>Actions</th></tr></thead><tbody id="widget-management-table-body"><tr><td colspan="6" style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading widgets...</td></tr></tbody></table><div class="available-widgets-list" style="margin-top: 20px;"><h4>Available Widgets (Inactive)</h4><p style="text-align: center;">Loading...</p></div></div>',
                null, // No direct confirm callback for the modal itself, save is handled by specific button
                null // No direct cancel callback
            );
            // Update modal title and buttons for management
            document.getElementById('message-modal-title').innerHTML = '<i class="fas fa-cogs"></i> Widget Management';
            document.getElementById('confirm-message-modal').style.display = 'inline-block'; // Show "Save All" button
            document.getElementById('confirm-message-modal').textContent = 'Save All Changes';
            document.getElementById('confirm-message-modal').id = 'save-widget-management-changes-btn'; // Change ID temporarily
            document.getElementById('cancel-message-modal').style.display = 'inline-block'; // Show "Cancel" button
            document.getElementById('cancel-message-modal').textContent = 'Close';
            document.getElementById('cancel-message-modal').id = 'close-widget-management-modal-temp'; // Change ID temporarily

            // Re-attach listeners for the new buttons
            const tempSaveBtn = document.getElementById('save-widget-management-changes-btn');
            const tempCloseBtn = document.getElementById('close-widget-management-modal-temp');

            if (tempSaveBtn) {
                tempSaveBtn.onclick = async function() {
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

                    const rows = document.querySelectorAll('#widget-management-table-body tr');
                    let allSuccess = true;
                    let messages = [];

                    const layoutUpdates = [];

                    for (const row of rows) {
                        const widgetId = row.dataset.widgetId;
                        const nameInput = row.querySelector('.widget-setting-name');
                        const iconInput = row.querySelector('.widget-setting-icon');
                        const widthInput = row.querySelector('.widget-setting-width');
                        const heightInput = row.querySelector('.widget-setting-height');
                        // const statusSpan = row.querySelector('.widget-management-status'); // Removed status span

                        const newName = nameInput.value;
                        const newIcon = iconInput.value;
                        const newWidth = parseFloat(widthInput.value);
                        const newHeight = parseFloat(heightInput.value);

                        // Collect data for update
                        layoutUpdates.push({
                            id: widgetId,
                            name: newName,
                            icon: newIcon,
                            width: newWidth,
                            height: newHeight
                        });
                    }

                    // Send a single AJAX request to update all widget details
                    const response = await sendAjaxRequest('update_widget_details_batch', {
                        updates: JSON.stringify(layoutUpdates)
                    });

                    if (response.status === 'success') {
                        showMessageModal('Success', response.message + ' Reloading dashboard...', () => location.reload(true));
                    } else {
                        showMessageModal('Error', `Failed to save all changes: ${response.message}`);
                        this.disabled = false;
                        this.innerHTML = 'Save All Changes';
                    }
                };
            }

            if (tempCloseBtn) {
                tempCloseBtn.onclick = function() {
                    widgetManagementModalOverlay.classList.remove('active');
                    // Restore original IDs for general message modal buttons
                    document.getElementById('save-widget-management-changes-btn').id = 'confirm-message-modal';
                    document.getElementById('close-widget-management-modal-temp').id = 'cancel-message-modal';
                };
            }

            await loadWidgetManagementTable(); // Load table content after modal is shown
        });
    }

    async function loadWidgetManagementTable() {
        const tableBody = document.getElementById('widget-management-table-body');
        const availableWidgetsListDiv = document.querySelector('.available-widgets-list p');

        tableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading active widgets...</td></tr>';
        availableWidgetsListDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading inactive widgets...';

        const response = await sendAjaxRequest('display_widget_settings_modal'); // This AJAX action now returns full HTML
        
        if (response.status === 'success' && response.html) {
            // Insert the generated HTML directly into the message-modal-content
            document.getElementById('message-modal-content').innerHTML = response.html;

            // Re-get the table body and available widgets list after HTML insertion
            const newTableBody = document.getElementById('widget-management-table-body');
            const newAvailableWidgetsListDiv = document.querySelector('.available-widgets-list');

            // Add event listeners for inputs in the newly loaded table
            newTableBody.querySelectorAll('.widget-setting-name, .widget-setting-icon, .widget-setting-width, .widget-setting-height').forEach(input => {
                input.addEventListener('input', function() {
                    // No need for individual status spans, rely on main save button
                    document.getElementById('save-widget-management-changes-btn').disabled = false;
                });
            });

            // Add event listeners for 'Remove' buttons in the table
            newTableBody.querySelectorAll('.remove-widget-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const widgetIdToDeactivate = this.dataset.widgetId;
                    showMessageModal(
                        'Confirm Deactivation',
                        `Are you sure you want to deactivate "${widgetIdToDeactivate}"? It will be removed from your dashboard.`,
                        async () => {
                            const response = await sendAjaxRequest('remove_widget', { widget_id: widgetIdToDeactivate });
                            if (response.status === 'success') {
                                showMessageModal('Success', response.message + ' Reloading dashboard...', () => location.reload(true));
                            } else {
                                showMessageModal('Error', response.message);
                            }
                        },
                        function() { /* Cancel callback */ }
                    );
                });
            });

            // Add event listeners for 'Save' buttons in the table (for individual widget updates)
            newTableBody.querySelectorAll('.update-widget-details-btn').forEach(button => {
                button.addEventListener('click', async function() {
                    const widgetId = this.dataset.widgetId;
                    const row = this.closest('tr');
                    const newName = row.querySelector('.widget-setting-name').value;
                    const newIcon = row.querySelector('.widget-setting-icon').value;
                    const newWidth = parseFloat(row.querySelector('.widget-setting-width').value);
                    const newHeight = parseFloat(row.querySelector('.widget-setting-height').value);

                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

                    const response = await sendAjaxRequest('update_widget_details', {
                        widget_id: widgetId,
                        name: newName,
                        icon: newIcon,
                        width: newWidth,
                        height: newHeight
                    });

                    if (response.status === 'success') {
                        showMessageModal('Success', response.message + ' Reloading dashboard...', () => location.reload(true));
                    } else {
                        showMessageModal('Error', response.message);
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-save"></i> Save';
                    }
                });
            });


            // Add event listeners for 'Add' buttons in the available widgets list
            newAvailableWidgetsListDiv.querySelectorAll('.add-widget-btn').forEach(button => {
                button.addEventListener('click', async function() {
                    const widgetIdToAdd = this.dataset.widgetId;
                    const response = await sendAjaxRequest('add_widget', { widget_id: widgetIdToAdd });
                    if (response.status === 'success') {
                        showMessageModal('Success', response.message + ' Reloading dashboard...', () => location.reload(true));
                    } else {
                        showMessageModal('Error', response.message);
                    }
                });
            });

            document.getElementById('save-widget-management-changes-btn').disabled = true; // Initially disable save all
        } else {
            tableBody.innerHTML = `<tr><td colspan="6" style="text-align: center; color: var(--danger); padding: 20px;">Error loading widgets: ${response.message}</td></tr>`;
            availableWidgetsListDiv.innerHTML = `<p style="text-align: center; color: var(--danger);">Error loading available widgets: ${response.message}</p>`;
            showMessageModal('Error', `Failed to load widget data for management: ${response.message}`);
        }
    }


    // --- NEW WIDGET CREATION MODAL LOGIC ---
    const createWidgetModalOverlay = document.getElementById('create-widget-modal-overlay');
    const closeCreateWidgetModalBtn = document.getElementById('close-create-widget-modal');
    const openCreateWidgetModalBtn = document.getElementById('open-create-widget-modal');
    const createWidgetForm = document.getElementById('create-widget-form');

    if (openCreateWidgetModalBtn) {
        openCreateWidgetModalBtn.addEventListener('click', function() {
            createWidgetModalOverlay.classList.add('active');
            // Reset form fields
            createWidgetForm.reset();
            // Set default icon and dimensions (as floats)
            document.getElementById('new-widget-icon').value = 'fas fa-cube'; // Default icon
            document.getElementById('new-widget-width').value = '1'; // Default width
            document.getElementById('new-widget-height').value = '1'; // Default height
        });
    }

    if (closeCreateWidgetModalBtn) {
        closeCreateWidgetModalBtn.addEventListener('click', function() {
            createWidgetModalOverlay.classList.remove('active');
        });
    }

    if (createWidgetModalOverlay) {
        createWidgetModalOverlay.addEventListener('click', function(e) {
            if (e.target === createWidgetModalOverlay) {
                createWidgetModalOverlay.classList.remove('active');
            }
        });
    }

    if (createWidgetForm) {
        createWidgetForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const widgetData = {};
            for (const [key, value] of formData.entries()) {
                // Parse width and height as floats
                if (key === 'width' || key === 'height') {
                    widgetData[key] = parseFloat(value);
                } else {
                    widgetData[key] = value;
                }
            }

            // Basic client-side validation for widget ID format
            const widgetIdInput = document.getElementById('new-widget-id');
            if (!widgetIdInput.checkValidity()) {
                showMessageModal('Validation Error', widgetIdInput.title || 'Please ensure Widget ID contains only lowercase letters, numbers, and underscores.');
                return;
            }

            // Disable button and show loading
            const submitBtn = createWidgetForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';

            const response = await sendAjaxRequest('create_new_widget_template', widgetData);

            if (response.status === 'success') {
                showMessageModal('Success', response.message + ' Reloading dashboard...', () => location.reload(true)); // Reload to show new widget in library
            } else {
                showMessageModal('Error', response.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-plus"></i> Create Widget Template';
            }
        });
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
            // If data[key] is an object (like the layout array), stringify it
            if (typeof data[key] === 'object' && data[key] !== null) {
                formData.append(key, JSON.stringify(data[key]));
            } else {
                formData.append(key, data[key]);
            }
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

});
