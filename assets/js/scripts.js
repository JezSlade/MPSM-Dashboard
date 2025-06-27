// Initialize dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    // Settings panel toggle
    const settingsToggle = document.getElementById('settings-toggle');
    const settingsPanel = document.getElementById('settings-panel');
    
    if (settingsToggle) {
        settingsToggle.addEventListener('click', function() {
            settingsPanel.classList.add('active');
            document.getElementById('settings-overlay').style.display = 'block';
        });
    }

    // Widget controls
    document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const form = new FormData();
            form.append('remove_widget', this.dataset.index);
            fetch(window.location.href, {
                method: 'POST',
                body: form
            }).then(() => window.location.reload());
        });
    });

    // Drag and drop implementation
    const widgetContainer = document.getElementById('widget-container');
    if (widgetContainer) {
        widgetContainer.addEventListener('dragover', e => {
            e.preventDefault();
            widgetContainer.style.backgroundColor = 'rgba(63, 114, 175, 0.1)';
        });
        
        widgetContainer.addEventListener('drop', e => {
            e.preventDefault();
            const widgetId = e.dataTransfer.getData('text/plain');
            // Handle widget addition...
        });
    }
});