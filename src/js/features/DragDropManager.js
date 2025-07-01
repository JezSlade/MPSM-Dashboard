// src/js/features/DragDropManager.js

import { showMessageModal } from '../ui/MessageModal.js';
import { sendAjaxRequest } from '../utils/AjaxService.js';

const mainContent = document.getElementById('widget-container');
let draggedWidget = null; // Global variable to store the currently dragged widget on the dashboard

// Function to save the current order of widgets on the dashboard
async function saveWidgetOrder() {
    const orderedWidgetIds = Array.from(mainContent.children)
                                .filter(child => child.classList.contains('widget'))
                                .map(widget => widget.dataset.widgetId);
    
    if (orderedWidgetIds.length > 0) {
        const response = await sendAjaxRequest('api/dashboard.php', 'update_widget_order', {
            order: JSON.stringify(orderedWidgetIds) // Send as JSON string
        });

        if (response.status === 'success') {
            console.log('Widget order saved successfully.');
        } else {
            console.error('Failed to save widget order:', response.message);
            showMessageModal('Error', 'Failed to save widget order: ' + response.message);
        }
    }
}

export function initDragDropManager() {
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
    mainContent.addEventListener('drop', async function(e) {
        e.preventDefault();
        this.style.backgroundColor = ''; // Remove highlight for adding
        // Clear all reordering highlights
        mainContent.querySelectorAll('.widget').forEach(widget => {
            widget.style.borderTop = '';
            widget.style.borderBottom = '';
        });

        const widgetId = e.dataTransfer.getData('text/plain');
        
        if (e.dataTransfer.effectAllowed === 'copy') {
            // This is an "add new widget" drop (from sidebar library)
            const response = await sendAjaxRequest('api/dashboard.php', 'add_widget', { widget_id: widgetId });
            if (response.status === 'success') {
                showMessageModal('Success', response.message, () => location.reload(true));
            } else {
                showMessageModal('Error', response.message);
            }
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
}
