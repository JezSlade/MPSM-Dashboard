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

    // Helper function to submit POST forms dynamically
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
});
