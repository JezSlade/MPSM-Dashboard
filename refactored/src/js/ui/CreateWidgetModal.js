// src/js/ui/CreateWidgetModal.js

import { showMessageModal } from './MessageModal.js';
import { sendAjaxRequest } from '../utils/AjaxService.js';

const createWidgetModalOverlay = document.getElementById('create-widget-modal-overlay');
const closeCreateWidgetModalBtn = document.getElementById('close-create-widget-modal');
const openCreateWidgetModalBtn = document.getElementById('open-create-widget-modal');
const createWidgetForm = document.getElementById('create-widget-form');

export function initCreateWidgetModal() {
    if (openCreateWidgetModalBtn) {
        openCreateWidgetModalBtn.addEventListener('click', function() {
            createWidgetModalOverlay.classList.add('active');
            // Reset form fields
            createWidgetForm.reset();
            // Set default icon and dimensions (as floats)
            document.getElementById('new-widget-icon').value = 'cube';
            document.getElementById('new-widget-width').value = '1.0';
            document.getElementById('new-widget-height').value = '1.0';
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

            const response = await sendAjaxRequest('api/widget_creation.php', 'create_new_widget_template', widgetData);

            if (response.status === 'success') {
                showMessageModal('Success', response.message, () => location.reload(true)); // Reload to show new widget in library
            } else {
                showMessageModal('Error', response.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-plus"></i> Create Widget Template';
            }
        });
    }
}
