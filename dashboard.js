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
    // Changed: Listener now on document.body so it can capture clicks on widgets
    // whether they are in mainContent or expandedOverlay.
    const mainContent = document.getElementById('widget-container'); // Still needed for placeholder appendChild
    const expandedOverlay = document.getElementById('widget-expanded-overlay'); // The new overlay

    document.body.addEventListener('click', function(e) { // Listener moved to document.body
        const target = e.target.closest('.widget-action'); // Find the clicked action button

        if (target) {
            const widget = target.closest('.widget'); // Get the parent widget element
            // If the clicked action is not part of a widget, or if the widget is null, do nothing
            if (!widget) return;

            const widgetId = target.dataset.widgetId; // Get widget ID if applicable

            // Handle Settings Action (Cog Icon)
            if (target.classList.contains('action-settings')) {
                const widgetName = widget.querySelector('.widget-title span').textContent;
                showMessageModal('Widget Settings', `Settings for "${widgetName}" widget. (ID: ${widgetId || 'N/A'})`);
            }
            // Handle Expand/Shrink Action (Expand Icon)
            else if (target.classList.contains('action-expand')) {
                if (!widget.classList.contains('maximized')) {
                    // MAXIMIZE Logic:
                    const widgetPlaceholder = widget.querySelector('.widget-placeholder');
                    
                    // Store original position references on the placeholder
                    widgetPlaceholder.dataset.originalParentId = widget.parentNode.id;
                    // Get index relative to its siblings
                    widgetPlaceholder.dataset.originalIndex = Array.from(widget.parentNode.children).indexOf(widget);

                    // Add classes for styling
                    widget.classList.add('maximized');
                    document.body.classList.add('expanded-active'); // This triggers the overlay
                    expandedOverlay.classList.add('active'); // Directly activate the overlay

                    // Move the widget into the overlay
                    expandedOverlay.appendChild(widget);

                    // Make the placeholder visible in the original spot to maintain grid flow
                    widgetPlaceholder.style.display = 'block';

                    // Change icon
                    target.querySelector('i').classList.replace('fa-expand', 'fa-compress');

                } else {
                    // MINIMIZE Logic:
                    const widgetPlaceholder = widget.querySelector('.widget-placeholder');
                    const originalParent = document.getElementById(widgetPlaceholder.dataset.originalParentId);
                    const originalIndex = parseInt(widgetPlaceholder.dataset.originalIndex);

                    // Move the widget back to its original parent
                    if (originalParent && originalParent.children[originalIndex]) {
                        // Insert before the sibling at the original index
                        originalParent.insertBefore(widget, originalParent.children[originalIndex]);
                    } else if (originalParent) {
                        // If no sibling at index (e.g., it was the last child), just append
                        originalParent.appendChild(widget);
                    } else {
                        // Fallback if original parent not found (shouldn't happen with correct IDs)
                        console.error("Original parent not found for widget ID:", widget.id);
                        mainContent.appendChild(widget); // Fallback to mainContent
                    }

                    // Remove classes
                    widget.classList.remove('maximized');
                    document.body.classList.remove('expanded-active');
                    expandedOverlay.classList.remove('active'); // Deactivate the overlay

                    // Hide the placeholder
                    widgetPlaceholder.style.display = 'none';

                    // Change icon
                    target.querySelector('i').classList.replace('fa-compress', 'fa-expand');
                }
            }
            // Handle Remove Widget Action (Times Icon)
            else if (target.classList.contains('remove-widget')) {
                const widgetIndex = target.getAttribute('data-index');

                showMessageModal(
                    'Confirm Removal',
                    'Are you sure you want to remove this widget?',
                    function() {
                        const form = document.createElement('form');
                        form.method = 'post';
                        form.style.display = 'none';

                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'remove_widget';
                        input.value = widgetIndex;

                        form.appendChild(input);
                        document.body.appendChild(form);
                        form.submit();
                    }
                );
            }
        }
    });

    // Close expanded widget when clicking on the expanded overlay
    expandedOverlay.addEventListener('click', function(e) {
        // Only close if the click is directly on the overlay, not on the widget itself
        if (e.target === expandedOverlay) {
            const activeMaximizedWidget = document.querySelector('.widget.maximized');
            if (activeMaximizedWidget) {
                const widgetPlaceholder = activeMaximizedWidget.querySelector('.widget-placeholder');
                const originalParent = document.getElementById(widgetPlaceholder.dataset.originalParentId);
                const originalIndex = parseInt(widgetPlaceholder.dataset.originalIndex);

                // Move the widget back
                if (originalParent && originalParent.children[originalIndex]) {
                    originalParent.insertBefore(activeMaximizedWidget, originalParent.children[originalIndex]);
                } else if (originalParent) {
                    originalParent.appendChild(activeMaximizedWidget);
                } else {
                    mainContent.appendChild(activeMaximizedWidget); // Fallback
                }

                activeMaximizedWidget.classList.remove('maximized');
                document.body.classList.remove('expanded-active');
                expandedOverlay.classList.remove('active');

                widgetPlaceholder.style.display = 'none'; // Hide placeholder

                const expandIcon = activeMaximizedWidget.querySelector('.action-expand i');
                if (expandIcon) {
                    expandIcon.classList.replace('fa-compress', 'fa-expand');
                }
            }
        }
    });


    // --- Drag and drop functionality ---
    // Changed: Listener now on document.body for widget items, as they might be dragged from sidebar.
    // mainContent is still used for drop target, as that's where widgets are dropped.
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
        addWidgetToDashboard(widgetId);
    });

    function addWidgetToDashboard(widgetId) {
        const form = document.createElement('form');
        form.method = 'post';
        form.style.display = 'none';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'add_widget';
        input.value = '1';

        const widgetInput = document.createElement('input');
        widgetInput.type = 'hidden';
        widgetInput.name = 'widget_id';
        widgetInput.value = widgetId;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
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
});
