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
    // Delegated event listeners for efficiency and future widgets
    const mainContent = document.getElementById('widget-container');

    mainContent.addEventListener('click', function(e) {
        const target = e.target.closest('.widget-action'); // Find the clicked action button

        if (target) {
            const widget = target.closest('.widget'); // Get the parent widget element
            const widgetId = target.dataset.widgetId; // Get widget ID if applicable

            // Handle Settings Action (Cog Icon)
            if (target.classList.contains('action-settings')) {
                // You could open a specific settings panel for the widget here
                // For now, a generic message:
                const widgetName = widget.querySelector('.widget-title span').textContent;
                showMessageModal('Widget Settings', `Settings for "${widgetName}" widget. (ID: ${widgetId || 'N/A'})`);
            }
            // Handle Expand/Shrink Action (Expand Icon)
            else if (target.classList.contains('action-expand')) {
                widget.classList.toggle('maximized');
                // Change icon based on state
                if (widget.classList.contains('maximized')) {
                    target.querySelector('i').classList.replace('fa-expand', 'fa-compress');
                } else {
                    target.querySelector('i').classList.replace('fa-compress', 'fa-expand');
                }
            }
            // Handle Remove Widget Action (Times Icon)
            else if (target.classList.contains('remove-widget')) {
                const widgetIndex = target.getAttribute('data-index');

                // Use the custom modal for confirmation
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


    // --- Drag and drop functionality ---
    const widgetItems = document.querySelectorAll('.widget-item');

    widgetItems.forEach(item => {
        item.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('text/plain', this.dataset.widgetId);
        });
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
        form.appendChild(widgetInput);
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
