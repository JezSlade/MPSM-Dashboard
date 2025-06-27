// dashboard.js

document.addEventListener('DOMContentLoaded', function() {
    // Settings panel toggle
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

    // Widget removal
    document.querySelectorAll('.remove-widget').forEach(button => {
        button.addEventListener('click', function() {
            const widgetIndex = this.getAttribute('data-index');
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
        });
    });

    // Drag and drop functionality
    const widgetItems = document.querySelectorAll('.widget-item');
    const mainContent = document.getElementById('widget-container');

    widgetItems.forEach(item => {
        item.addEventListener('dragstart', function(e) {
            e.dataTransfer.setData('text/plain', this.dataset.widgetId);
        });
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

    // Refresh button
    document.getElementById('refresh-btn').addEventListener('click', function() {
        location.reload();
    });

    // Theme settings button
    document.getElementById('theme-settings-btn').addEventListener('click', function() {
        settingsPanel.classList.add('active');
        settingsOverlay.style.display = 'block';
    });
});
