// dashboard.js

document.addEventListener('DOMContentLoaded', function() {
    // --- Settings Panel Toggle ---
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

    // --- Message Modal Functions (to replace alerts) ---
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


    // --- Widget Actions ---
    // Listener on document.body so it can capture clicks on widgets
    // whether they are in mainContent or expandedOverlay.
    const mainContent = document.getElementById('widget-container');
    const expandedOverlay = document.getElementById('widget-expanded-overlay'); // The new overlay

    document.body.addEventListener('click', function(e) {
        const target = e.target.closest('.widget-action'); // Find the clicked action button

        if (target) {
            const widget = target.closest('.widget'); // Get the parent widget element
            // If the clicked action is not part of a widget, or if the widget is null, do nothing
            if (!widget) return;

            // Handle Settings Action (Cog Icon)
            if (target.classList.contains('action-settings')) {
                const widgetName = widget.querySelector('.widget-title span').textContent;
                showMessageModal('Widget Settings', `Settings for "${widgetName}" widget.`);
            }
            // Handle Expand/Shrink Action (Expand Icon)
            else if (target.classList.contains('action-expand')) {
                toggleWidgetExpansion(widget); // Call a dedicated function for consistency
            }
            // Handle Remove Widget Action (Times Icon)
            else if (target.classList.contains('remove-widget')) {
                // If the remove button is disabled, do nothing
                if (target.classList.contains('disabled')) {
                    showMessageModal('Information', 'This widget cannot be removed in "Show All Widgets" mode.');
                    return;
                }

                // Get the widget index from the data-index attribute on the action button
                const widgetIndex = target.getAttribute('data-index');

                console.log("Remove button clicked. Widget index:", widgetIndex);

                if (widget.classList.contains('maximized')) {
                    // If maximized, clicking 'X' should minimize/close the modal
                    console.log("Widget is maximized, minimizing instead of removing.");
                    toggleWidgetExpansion(widget); // Restore to original position
                } else if (widgetIndex !== null && widgetIndex !== undefined) {
                    // If not maximized and a valid index is found, proceed with removal confirmation
                    console.log("Widget is minimized, prompting for removal confirmation.");
                    showMessageModal(
                        'Confirm Removal',
                        'Are you sure you want to remove this widget from the dashboard?',
                        function() {
                            console.log("Confirmed removal for widget index:", widgetIndex);
                            submitActionForm('remove_widget', { widget_index: widgetIndex });
                        }
                    );
                } else {
                    // Fallback for cases where data-index might be missing (shouldn't happen with current HTML)
                    console.error("Error: Could not determine widget index for removal on a non-maximized widget.");
                    showMessageModal('Error', 'Could not determine which widget to remove.');
                }
            }
        }
    });

    // Helper function to toggle widget expansion state
    function toggleWidgetExpansion(widget) {
        const widgetPlaceholder = widget.querySelector('.widget-placeholder');
        const expandIcon = widget.querySelector('.action-expand i'); // Get the specific expand icon for this widget

        if (!widget.classList.contains('maximized')) {
            // MAXIMIZE Logic:
            // Store original position references on the placeholder
            widgetPlaceholder.dataset.originalParentId = widget.parentNode.id;
            widgetPlaceholder.dataset.originalIndex = Array.from(widget.parentNode.children).indexOf(widget);

            // Add classes for styling
            widget.classList.add('maximized');
            document.body.classList.add('expanded-active');
            expandedOverlay.classList.add('active');

            // Move the widget into the overlay
            expandedOverlay.appendChild(widget);

            // Make the placeholder visible in the original spot to maintain grid flow
            widgetPlaceholder.style.display = 'block';

            // Change icon
            if (expandIcon) expandIcon.classList.replace('fa-expand', 'fa-compress');

        } else {
            // MINIMIZE Logic:
            const originalParent = document.getElementById(widgetPlaceholder.dataset.originalParentId);
            const originalIndex = parseInt(widgetPlaceholder.dataset.originalIndex);

            // Move the widget back to its original parent
            if (originalParent && originalParent.children[originalIndex]) {
                originalParent.insertBefore(widget, originalParent.children[originalIndex]);
            } else if (originalParent) {
                originalParent.appendChild(widget);
            } else {
                console.error("Original parent not found for widget ID:", widget.id);
                mainContent.appendChild(widget); // Fallback to mainContent
            }

            // Remove classes
            widget.classList.remove('maximized');
            document.body.classList.remove('expanded-active');
            expandedOverlay.classList.remove('active');

            // Hide the placeholder
            widgetPlaceholder.style.display = 'none';

            // Change icon
            if (expandIcon) expandIcon.classList.replace('fa-compress', 'fa-expand');
        }
    }


    // Close expanded widget when clicking on the expanded overlay
    expandedOverlay.addEventListener('click', function(e) {
        // Only close if the click is directly on the overlay, not on the widget itself
        if (e.target === expandedOverlay) {
            const activeMaximizedWidget = document.querySelector('.widget.maximized');
            if (activeMaximizedWidget) {
                toggleWidgetExpansion(activeMaximizedWidget); // Use the helper function to minimize
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
        this.style.backgroundColor = 'rgba(63, 114, 175, 0.1)'; // Visual feedback for drag
    });

    mainContent.addEventListener('dragleave', function() {
        this.style.backgroundColor = ''; // Remove visual feedback
    });

    mainContent.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.backgroundColor = '';

        const widgetId = e.dataTransfer.getData('text/plain');
        // Check if 'Add Widget' is disabled by 'Show All Widgets' mode
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
        form.style.display = 'none'; // Keep hidden

        // Add the action_type input
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action_type';
        actionInput.value = actionType;
        form.appendChild(actionInput);

        // Add other data parameters
        for (const key in data) {
            if (data.hasOwnProperty(key)) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = key;
                hiddenInput.value = data[key];
                form.appendChild(hiddenInput);
            }
        }

        document.body.appendChild(form); // Append to body
        
        // Log the form data *before* submission for debugging
        const formData = new FormData(form);
        console.log(`Submitting form for action: ${actionType}`);
        for (let pair of formData.entries()) {
            console.log(`  ${pair[0]}: ${pair[1]}`);
        }

        form.submit(); // Submit the form
    }


    // --- Other Global Buttons ---
    // Refresh button
    document.getElementById('refresh-btn').addEventListener('click', function() {
        location.reload();
    });

    // Theme settings button (this one already opened settings panel)
    document.getElementById('theme-settings-btn').addEventListener('click', function() {
        settingsPanel.classList.add('active');
        settingsOverlay.style.display = 'block';
    });

    // Handle form submission for update_settings (from settings panel)
    const settingsForm = settingsPanel.querySelector('form');
    if (settingsForm) {
        settingsForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission to handle it via JS

            const formData = new FormData(settingsForm);
            const dataToSubmit = {};
            for (const [key, value] of formData.entries()) {
                // Special handling for checkboxes: if unchecked, they are not in formData.
                // We need to explicitly set them to '0' if they are known boolean settings.
                if (key === 'enable_animations' || key === 'show_all_available_widgets') { // Added new setting here
                    dataToSubmit[key] = settingsForm.elements[key].checked ? '1' : '0'; // Send '1' or '0'
                } else {
                    dataToSubmit[key] = value;
                }
            }
            submitActionForm('update_settings', dataToSubmit);
        });
    }

    // New: Disable/Enable Add Widget button based on 'Show All Widgets' state
    const showAllWidgetsToggle = document.getElementById('show_all_available_widgets');
    const newWidgetBtn = document.getElementById('new-widget-btn');
    const widgetSelect = document.getElementById('widget_select'); // The select inside settings panel
    const addWidgetToDashboardBtn = settingsPanel.querySelector('button[name="add_widget"]'); // The button in settings panel

    function updateAddRemoveButtonStates() {
        if (showAllWidgetsToggle && newWidgetBtn && widgetSelect && addWidgetToDashboardBtn) {
            if (showAllWidgetsToggle.checked) {
                newWidgetBtn.classList.add('disabled');
                newWidgetBtn.disabled = true;
                widgetSelect.disabled = true;
                addWidgetToDashboardBtn.disabled = true;
            } else {
                newWidgetBtn.classList.remove('disabled');
                newWidgetBtn.disabled = false;
                widgetSelect.disabled = false;
                addWidgetToDashboardBtn.disabled = false;
            }
        }
    }

    // Attach listener to the new toggle
    if (showAllWidgetsToggle) {
        showAllWidgetsToggle.addEventListener('change', updateAddRemoveButtonStates);
    }

    // Initial state update on load
    updateAddRemoveButtonStates();
});
