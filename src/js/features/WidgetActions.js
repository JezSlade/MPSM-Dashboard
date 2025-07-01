// src/js/features/WidgetActions.js

import { showMessageModal } from '../ui/MessageModal.js';
import { sendAjaxRequest } from '../utils/AjaxService.js';
import { showWidgetSettingsModal } from '../ui/WidgetSettingsModal.js';

export function initWidgetActions() {
    // Delegated event listener for widget actions
    document.body.addEventListener('click', async function(e) {
        const target = e.target.closest('.widget-action');

        if (!target) return;

        const widgetElement = target.closest('.widget');
        if (!widgetElement) return;

        const widgetId = widgetElement.dataset.widgetId;
        const widgetName = widgetElement.querySelector('.widget-title span').textContent; // Get name from displayed title

        if (target.classList.contains('action-settings')) {
            const currentWidth = parseFloat(widgetElement.dataset.currentWidth);
            const currentHeight = parseFloat(widgetElement.dataset.currentHeight);
            showWidgetSettingsModal(widgetId, widgetName, currentWidth, currentHeight);
        } else if (target.classList.contains('action-expand')) {
            const overlay = document.getElementById('widget-expanded-overlay');
            if (widgetElement.classList.contains('maximized')) {
                // Collapse
                widgetElement.classList.remove('maximized');
                overlay.classList.remove('active');
                overlay.style.display = 'none';
                // Move widget back to its original position (handled by DragDropManager's placeholder)
                const originalPlaceholder = document.querySelector(`.widget-placeholder[data-original-id="${widgetId}"]`);
                if (originalPlaceholder && originalPlaceholder.parentNode) {
                    originalPlaceholder.parentNode.insertBefore(widgetElement, originalPlaceholder);
                }
            } else {
                // Maximize
                overlay.style.display = 'flex'; // Ensure overlay is visible before transition
                overlay.classList.add('active');
                widgetElement.classList.add('maximized');
                // Temporarily append to body to break out of grid flow for fixed positioning
                document.body.appendChild(widgetElement);

                // If it's the IDE widget, initialize its content
                if (widgetId === 'ide') {
                    // Import IdeWidget dynamically to avoid circular dependencies and only load when needed
                    const { initIdeWidget } = await import('../features/IdeWidget.js');
                    initIdeWidget(widgetElement);
                }
            }
        } else if (target.classList.contains('remove-widget')) {
            // This button now deactivates the widget
            showMessageModal(
                'Confirm Deactivation',
                `Are you sure you want to deactivate the widget "${widgetName}"? It will be hidden from the dashboard but can be re-activated via Widget Management.`,
                async function() {
                    target.disabled = true; // Disable button during request
                    target.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                    const response = await sendAjaxRequest('api/dashboard.php', 'toggle_widget_active_status', {
                        widget_id: widgetId,
                        is_active: '0' // Set to inactive
                    });

                    target.disabled = false;
                    target.innerHTML = '<i class="fas fa-times"></i>';

                    if (response.status === 'success') {
                        showMessageModal('Success', response.message + ' Reloading dashboard...', () => location.reload(true));
                    } else {
                        showMessageModal('Error', response.message);
                    }
                }
            );
        }
    });

    // Event listener for clicking on the expanded overlay to collapse the widget
    const widgetExpandedOverlay = document.getElementById('widget-expanded-overlay');
    if (widgetExpandedOverlay) {
        widgetExpandedOverlay.addEventListener('click', function(e) {
            if (e.target === widgetExpandedOverlay) { // Only close if clicking the overlay itself, not the widget
                const maximizedWidget = document.querySelector('.widget.maximized');
                if (maximizedWidget) {
                    maximizedWidget.classList.remove('maximized');
                    widgetExpandedOverlay.classList.remove('active');
                    widgetExpandedOverlay.style.display = 'none';

                    // Move widget back to its original position (handled by DragDropManager's placeholder)
                    const originalId = maximizedWidget.dataset.widgetId;
                    const originalPlaceholder = document.querySelector(`.widget-placeholder[data-original-id="${originalId}"]`);
                    if (originalPlaceholder && originalPlaceholder.parentNode) {
                        originalPlaceholder.parentNode.insertBefore(maximizedWidget, originalPlaceholder);
                    }
                }
            }
        });
    }
}
