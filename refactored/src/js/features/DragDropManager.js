// src/js/features/DragDropManager.js

import { sendAjaxRequest } from '../utils/AjaxService.js';
import { showMessageModal } from '../ui/MessageModal.js';

let draggedWidget = null;
let placeholder = null;
let currentDropTarget = null; // The element over which the draggedWidget is currently hovering

export function initDragDrop() {
    console.log('[DragDropManager] Initializing drag and drop functionality.');
    const widgetContainer = document.getElementById('widget-container');
    if (!widgetContainer) {
        console.error("[DragDropManager] Widget container (id='widget-container') not found. Drag and drop will not be initialized.");
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
            console.log(`[DragDropManager] Drag started for widget: ${draggedWidget.dataset.widgetId}`);
        } else if (targetWidget && targetWidget.classList.contains('maximized')) {
            console.log('[DragDropManager] Attempted to drag a maximized widget. Dragging disabled.');
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
                // console.log('[DragDropManager] Placeholder moved.'); // Too verbose for continuous logging
            }
        }
    });

    widgetContainer.addEventListener('dragleave', function(e) {
        // Optional: handle visual feedback when leaving container
        // console.log('[DragDropManager] Drag leave event.'); // Can be too verbose
    });

    widgetContainer.addEventListener('drop', function(e) {
        e.preventDefault();
        console.log('[DragDropManager] Drop event occurred.');
        if (draggedWidget && placeholder) {
            draggedWidget.style.display = 'flex'; // Show the dragged widget
            widgetContainer.replaceChild(draggedWidget, placeholder); // Replace placeholder with actual widget
            draggedWidget.classList.remove('dragging');
            saveWidgetOrder(); // Save the new order
            console.log(`[DragDropManager] Widget ${draggedWidget.dataset.widgetId} dropped and order saved.`);
        } else {
            console.warn('[DragDropManager] Drop event: draggedWidget or placeholder was null.');
        }
        resetDragState();
    });

    widgetContainer.addEventListener('dragend', function(e) {
        console.log('[DragDropManager] Drag end event occurred.');
        if (draggedWidget) {
            // If drop didn't happen in a valid drop zone, return widget to original spot
            if (e.dataTransfer.dropEffect === 'none' && placeholder) {
                console.log('[DragDropManager] Drop effect was "none", attempting to return widget to original position.');
                draggedWidget.style.display = 'flex';
                // Try to put it back where the placeholder was, or append if placeholder is gone
                if (placeholder.parentNode === widgetContainer) {
                    widgetContainer.replaceChild(draggedWidget, placeholder);
                } else {
                    widgetContainer.appendChild(draggedWidget); // Fallback
                    console.warn('[DragDropManager] Placeholder parent not widgetContainer, appending as fallback.');
                }
            } else if (draggedWidget.style.display === 'none') {
                 // If it's still hidden (e.g., dropped outside valid zone, but we want it visible)
                 console.log('[DragDropManager] Widget still hidden after dragend, forcing display.');
                 draggedWidget.style.display = 'flex';
                 if (placeholder.parentNode === widgetContainer) {
                    widgetContainer.replaceChild(draggedWidget, placeholder);
                } else {
                    widgetContainer.appendChild(draggedWidget); // Fallback
                    console.warn('[DragDropManager] Placeholder parent not widgetContainer, appending as fallback.');
                }
            }
            draggedWidget.classList.remove('dragging');
        }
        resetDragState();
    });

    // --- Dragging from Sidebar Widget Library ---
    const widgetLibrary = document.querySelector('.widget-list');
    if (widgetLibrary) {
        console.log('[DragDropManager] Widget library found. Setting up dragstart for new widgets.');
        widgetLibrary.addEventListener('dragstart', function(e) {
            const widgetItem = e.target.closest('.widget-item');
            if (widgetItem) {
                e.dataTransfer.effectAllowed = 'copy';
                e.dataTransfer.setData('text/plain', widgetItem.dataset.widgetId);
                console.log(`[DragDropManager] Drag started from library for widget ID: ${widgetItem.dataset.widgetId}`);
            }
        });
    } else {
        console.warn('[DragDropManager] Widget library (.widget-list) not found.');
    }

    widgetContainer.addEventListener('dragenter', function(e) {
        e.preventDefault(); // Allow drop
        if (e.dataTransfer.types.includes('text/plain')) {
            widgetContainer.classList.add('drag-over'); // Visual feedback
            // console.log('[DragDropManager] Drag enter on widget container.'); // Too verbose
        }
    });

    widgetContainer.addEventListener('dragleave', function(e) {
        if (e.target === widgetContainer) { // Only remove class if leaving the container itself
            widgetContainer.classList.remove('drag-over');
            // console.log('[DragDropManager] Drag leave on widget container.'); // Too verbose
        }
    });

    widgetContainer.addEventListener('drop', async function(e) {
        e.preventDefault();
        console.log('[DragDropManager] Drop event on widget container (from library or internal).');
        widgetContainer.classList.remove('drag-over');

        const widgetId = e.dataTransfer.getData('text/plain');
        if (widgetId && !draggedWidget) { // Ensure it's a new widget from library, not an internal drag
            console.log(`[DragDropManager] Attempting to add new widget from library: ${widgetId}`);
            // Check if widget already exists on the dashboard (is active)
            const existingWidget = document.querySelector(`.widget[data-widget-id="${widgetId}"]`);
            if (existingWidget) {
                console.warn(`[DragDropManager] Widget ${widgetId} already exists on dashboard.`);
                showMessageModal('Widget Exists', `"${widgetId}" is already on your dashboard. You can manage its status in Widget Management.`);
                return;
            }

            // Send AJAX request to add widget
            try {
                const response = await sendAjaxRequest('api/dashboard.php', 'toggle_widget_active_status', {
                    widget_id: widgetId,
                    is_active: '1' // Set to active
                });

                if (response.status === 'success') {
                    console.log(`[DragDropManager] Widget ${widgetId} activated successfully. Reloading...`);
                    showMessageModal('Success', response.message + ' Reloading dashboard to add new widget...', () => location.reload(true));
                } else {
                    console.error('[DragDropManager] Error activating widget from library:', response.message, response.rawResponse);
                    showMessageModal('Error', response.message);
                }
            } catch (error) {
                console.error('[DragDropManager] AJAX request failed when activating widget from library:', error);
                showMessageModal('Error', 'An unexpected error occurred while adding the widget.');
            }
        } else if (draggedWidget) {
            console.log('[DragDropManager] Internal widget drag completed (handled by dragend).');
        } else {
            console.warn('[DragDropManager] Drop event occurred without valid widgetId or draggedWidget.');
        }
        resetDragState();
    });
}

function resetDragState() {
    console.log('[DragDropManager] Resetting drag state.');
    if (draggedWidget) {
        draggedWidget.classList.remove('dragging');
        draggedWidget.style.display = 'flex'; // Ensure it's visible
    }
    if (placeholder && placeholder.parentNode) {
        placeholder.parentNode.removeChild(placeholder);
        console.log('[DragDropManager] Placeholder removed.');
    }
    draggedWidget = null;
    placeholder = null;
    currentDropTarget = null;
    console.log('[DragDropManager] Drag state cleared.');
}

/**
 * Saves the current order of widgets on the dashboard.
 * This function now sends the order of all *rendered* widgets.
 */
async function saveWidgetOrder() {
    console.log('[DragDropManager] Saving widget order.');
    const widgets = Array.from(document.querySelectorAll('#widget-container .widget'));
    const orderedWidgetIds = widgets.map(widget => widget.dataset.widgetId);

    if (orderedWidgetIds.length === 0) {
        console.log("[DragDropManager] No widgets to save order for.");
        return;
    }

    try {
        const response = await sendAjaxRequest('api/dashboard.php', 'update_widget_order', {
            order: JSON.stringify(orderedWidgetIds)
        });

        if (response.status === 'success') {
            console.log("[DragDropManager] Widget order saved successfully.");
            // showMessageModal('Success', 'Widget order saved.'); // Optional, can be annoying after every drag
        } else {
            console.error('[DragDropManager] Failed to save widget order:', response.message, response.rawResponse);
            showMessageModal('Error', 'Failed to save widget order: ' + response.message);
        }
    } catch (error) {
        console.error('[DragDropManager] AJAX request failed during widget order save:', error);
        showMessageModal('Error', 'An unexpected error occurred while saving widget order.');
    }
}
