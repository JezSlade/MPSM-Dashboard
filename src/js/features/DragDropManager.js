// src/js/features/DragDropManager.js

import { sendAjaxRequest } from '../utils/AjaxService.js';
import { showMessageModal } from '../ui/MessageModal.js';

let draggedWidget = null;
let placeholder = null;
let currentDropTarget = null; // The element over which the draggedWidget is currently hovering

export function initDragDrop() {
    const widgetContainer = document.getElementById('widget-container');
    if (!widgetContainer) {
        console.error("Widget container not found. Drag and drop will not be initialized.");
        return;
    }

    // --- Dragging Widgets on the Dashboard ---
    widgetContainer.addEventListener('dragstart', function(e) {
        const targetWidget = e.target.closest('.widget');
        if (targetWidget && !targetWidget.classList.contains('maximized')) { // Only drag if not maximized
            draggedWidget = targetWidget;
            draggedWidget.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', draggedWidget.dataset.widgetId); // Set data for Firefox

            // Create a placeholder
            placeholder = document.createElement('div');
            placeholder.classList.add('widget-placeholder');
            // Inherit dimensions from the dragged widget for correct grid sizing
            placeholder.style.setProperty('--width', draggedWidget.style.getPropertyValue('--width'));
            placeholder.style.setProperty('--height', draggedWidget.style.getPropertyValue('--height'));
            
            // Store original ID on placeholder for returning widget to correct spot after maximize/minimize
            placeholder.dataset.originalId = draggedWidget.dataset.widgetId;

            widgetContainer.insertBefore(placeholder, draggedWidget);
            setTimeout(() => draggedWidget.style.display = 'none', 0); // Hide original after placeholder is in place
        }
    });

    widgetContainer.addEventListener('dragover', function(e) {
        e.preventDefault(); // Allow drop
        e.dataTransfer.dropEffect = 'move';

        if (draggedWidget && placeholder) {
            const target = e.target.closest('.widget');
            const targetPlaceholder = e.target.closest('.widget-placeholder');

            let referenceNode = null;

            if (target && target !== draggedWidget) {
                // Determine if we're dragging before or after the target widget
                const bounding = target.getBoundingClientRect();
                const offset = e.clientY - bounding.top;
                const isBefore = offset < bounding.height / 2;

                if (isBefore) {
                    referenceNode = target;
                } else {
                    referenceNode = target.nextElementSibling;
                }
            } else if (targetPlaceholder && targetPlaceholder !== placeholder) {
                // If dragging over another placeholder
                referenceNode = targetPlaceholder;
            } else if (!target && !targetPlaceholder) {
                // If dragging over empty space in the container, append to end
                referenceNode = null; // Append to end
            }

            if (referenceNode !== placeholder.nextElementSibling && referenceNode !== placeholder) {
                widgetContainer.insertBefore(placeholder, referenceNode);
            }
        }
    });

    widgetContainer.addEventListener('dragleave', function(e) {
        // Optional: handle visual feedback when leaving container
    });

    widgetContainer.addEventListener('drop', function(e) {
        e.preventDefault();
        if (draggedWidget && placeholder) {
            draggedWidget.style.display = 'flex'; // Show the dragged widget
            widgetContainer.replaceChild(draggedWidget, placeholder); // Replace placeholder with actual widget
            draggedWidget.classList.remove('dragging');
            saveWidgetOrder(); // Save the new order
        }
        resetDragState();
    });

    widgetContainer.addEventListener('dragend', function(e) {
        if (draggedWidget) {
            // If drop didn't happen in a valid drop zone, return widget to original spot
            if (e.dataTransfer.dropEffect === 'none' && placeholder) {
                draggedWidget.style.display = 'flex';
                // Try to put it back where the placeholder was, or append if placeholder is gone
                if (placeholder.parentNode === widgetContainer) {
                    widgetContainer.replaceChild(draggedWidget, placeholder);
                } else {
                    widgetContainer.appendChild(draggedWidget); // Fallback
                }
            } else if (draggedWidget.style.display === 'none') {
                 // If it's still hidden (e.g., dropped outside valid zone, but we want it visible)
                 draggedWidget.style.display = 'flex';
                 if (placeholder.parentNode === widgetContainer) {
                    widgetContainer.replaceChild(draggedWidget, placeholder);
                } else {
                    widgetContainer.appendChild(draggedWidget); // Fallback
                }
            }
            draggedWidget.classList.remove('dragging');
        }
        resetDragState();
    });

    // --- Dragging from Sidebar Widget Library ---
    const widgetLibrary = document.querySelector('.widget-list');
    if (widgetLibrary) {
        widgetLibrary.addEventListener('dragstart', function(e) {
            const widgetItem = e.target.closest('.widget-item');
            if (widgetItem) {
                e.dataTransfer.effectAllowed = 'copy';
                e.dataTransfer.setData('text/plain', widgetItem.dataset.widgetId);
            }
        });
    }

    widgetContainer.addEventListener('dragenter', function(e) {
        e.preventDefault(); // Allow drop
        if (e.dataTransfer.types.includes('text/plain')) {
            widgetContainer.classList.add('drag-over'); // Visual feedback
        }
    });

    widgetContainer.addEventListener('dragleave', function(e) {
        if (e.target === widgetContainer) { // Only remove class if leaving the container itself
            widgetContainer.classList.remove('drag-over');
        }
    });

    widgetContainer.addEventListener('drop', async function(e) {
        e.preventDefault();
        widgetContainer.classList.remove('drag-over');

        const widgetId = e.dataTransfer.getData('text/plain');
        if (widgetId && !draggedWidget) { // Ensure it's a new widget from library, not an internal drag
            // Check if widget already exists on the dashboard (is active)
            const existingWidget = document.querySelector(`.widget[data-widget-id="${widgetId}"]`);
            if (existingWidget) {
                showMessageModal('Widget Exists', `"${widgetId}" is already on your dashboard. You can manage its status in Widget Management.`);
                return;
            }

            // Send AJAX request to add widget
            const response = await sendAjaxRequest('api/dashboard.php', 'toggle_widget_active_status', {
                widget_id: widgetId,
                is_active: '1' // Set to active
            });

            if (response.status === 'success') {
                showMessageModal('Success', response.message + ' Reloading dashboard to add new widget...', () => location.reload(true));
            } else {
                showMessageModal('Error', response.message);
            }
        }
        resetDragState();
    });
}

function resetDragState() {
    if (draggedWidget) {
        draggedWidget.classList.remove('dragging');
        draggedWidget.style.display = 'flex'; // Ensure it's visible
    }
    if (placeholder && placeholder.parentNode) {
        placeholder.parentNode.removeChild(placeholder);
    }
    draggedWidget = null;
    placeholder = null;
    currentDropTarget = null;
}

/**
 * Saves the current order of widgets on the dashboard.
 * This function now sends the order of all *rendered* widgets.
 */
async function saveWidgetOrder() {
    const widgets = Array.from(document.querySelectorAll('#widget-container .widget'));
    const orderedWidgetIds = widgets.map(widget => widget.dataset.widgetId);

    if (orderedWidgetIds.length === 0) {
        // If no widgets, perhaps clear order or do nothing
        console.log("No widgets to save order for.");
        return;
    }

    const response = await sendAjaxRequest('api/dashboard.php', 'update_widget_order', {
        order: JSON.stringify(orderedWidgetIds)
    });

    if (response.status === 'success') {
        // No full reload needed if we implement dynamic rendering later
        console.log("Widget order saved successfully.");
        // showMessageModal('Success', 'Widget order saved.'); // Optional, can be annoying after every drag
    } else {
        showMessageModal('Error', 'Failed to save widget order: ' + response.message);
    }
}
