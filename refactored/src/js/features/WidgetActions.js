// src/js/features/WidgetActions.js

import { showMessageModal } from '../ui/MessageModal.js';
import { sendAjaxRequest } from '../utils/AjaxService.js';
import { showWidgetSettingsModal } from '../ui/WidgetSettingsModal.js';

export function initWidgetActions() {
    console.log('[WidgetActions] Initializing widget action listeners.');

    // Delegated event listener for widget actions
    document.body.addEventListener('click', async function(e) {
        const target = e.target.closest('.widget-action');

        if (!target) return; // Not a widget action button

        const widgetElement = target.closest('.widget');
        if (!widgetElement) {
            console.warn('[WidgetActions] Clicked widget action, but parent widget element not found.');
            return;
        }

        const widgetId = widgetElement.dataset.widgetId;
        const widgetName = widgetElement.querySelector('.widget-title span').textContent; // Get name from displayed title
        console.log(`[WidgetActions] Action clicked for widget: ${widgetId}, action target: ${target.className}`);

        if (target.classList.contains('action-settings')) {
            console.log(`[WidgetActions] Settings action clicked for widget: ${widgetId}`);
            const currentWidth = parseFloat(widgetElement.dataset.currentWidth);
            const currentHeight = parseFloat(widgetElement.dataset.currentHeight);
            showWidgetSettingsModal(widgetId, widgetName, currentWidth, currentHeight);
        } else if (target.classList.contains('action-expand')) {
            console.log(`[WidgetActions] Expand action clicked for widget: ${widgetId}`);
            const overlay = document.getElementById('widget-expanded-overlay');
            if (!overlay) {
                console.error('[WidgetActions] Widget expanded overlay not found.');
                return;
            }

            if (widgetElement.classList.contains('maximized')) {
                // Collapse
                console.log(`[WidgetActions] Collapsing widget: ${widgetId}`);
                widgetElement.classList.remove('maximized');
                overlay.classList.remove('active');
                overlay.style.display = 'none'; // Hide overlay immediately after transition for better UX

                // Move widget back to its original position (handled by DragDropManager's placeholder)
                const originalId = widgetElement.dataset.widgetId;
                const originalPlaceholder = document.querySelector(`.widget-placeholder[data-original-id=\"${originalId}\"]`);
                if (originalPlaceholder && originalPlaceholder.parentNode) {
                    originalPlaceholder.parentNode.insertBefore(widgetElement, originalPlaceholder);
                    console.log(`[WidgetActions] Widget ${widgetId} moved back to original placeholder position.`);
                } else {
                    console.warn(`[WidgetActions] Original placeholder for widget ${widgetId} not found, cannot restore exact position.`);
                }
            } else {
                // Maximize
                console.log(`[WidgetActions] Maximizing widget: ${widgetId}`);
                // Temporarily hide other widgets to prevent layout shifts during maximization
                document.querySelectorAll('.widget:not(.maximized)').forEach(otherWidget => {
                    otherWidget.style.visibility = 'hidden';
                });
                widgetElement.classList.add('maximized');
                overlay.style.display = 'flex'; // Show overlay
                // Use a timeout to ensure display:flex is applied before opacity transition
                setTimeout(() => {
                    overlay.classList.add('active');
                    console.log('[WidgetActions] Widget maximized and overlay active.');
                }, 10); // Small delay
            }
        } else if (target.classList.contains('remove-widget')) {
            console.log(`[WidgetActions] Remove/Deactivate action clicked for widget: ${widgetId}`);
            // Confirm with user before deactivating
            showMessageModal(
                'Confirm Deactivation',
                `Are you sure you want to deactivate the "${widgetName}" widget? It will be removed from the dashboard but can be re-added from Widget Management.`,
                async () => {
                    console.log(`[WidgetActions] User confirmed deactivation for widget: ${widgetId}`);
                    try {
                        const response = await sendAjaxRequest('api/dashboard.php', 'toggle_widget_active_status', {
                            widget_id: widgetId,
                            is_active: '0' // Set to inactive
                        });

                        if (response.status === 'success') {
                            console.log(`[WidgetActions] Widget ${widgetId} deactivated successfully. Reloading...`);
                            showMessageModal('Success', response.message + ' Reloading dashboard to apply changes...', () => location.reload(true));
                        } else {
                            console.error('[WidgetActions] Error deactivating widget:', response.message, response.rawResponse);
                            showMessageModal('Error', response.message);
                        }
                    } catch (error) {
                        console.error('[WidgetActions] AJAX request failed during widget deactivation:', error);
                        showMessageModal('Error', 'An unexpected error occurred while deactivating the widget.');
                    }
                },
                () => {
                    console.log(`[WidgetActions] User cancelled deactivation for widget: ${widgetId}`);
                }
            );
        }
    });

    // Event listener for clicking on the expanded overlay to collapse the widget
    const widgetExpandedOverlay = document.getElementById('widget-expanded-overlay');
    if (widgetExpandedOverlay) {
        widgetExpandedOverlay.addEventListener('click', function(e) {
            if (e.target === widgetExpandedOverlay) { // Only close if clicking the overlay itself, not the widget
                console.log('[WidgetActions] Expanded overlay clicked. Attempting to collapse maximized widget.');
                const maximizedWidget = document.querySelector('.widget.maximized');
                if (maximizedWidget) {
                    maximizedWidget.classList.remove('maximized');
                    widgetExpandedOverlay.classList.remove('active');
                    // Hide overlay after a short delay to allow transition to complete
                    setTimeout(() => {
                        widgetExpandedOverlay.style.display = 'none';
                        // Restore visibility of other widgets
                        document.querySelectorAll('.widget:not(.maximized)').forEach(otherWidget => {
                            otherWidget.style.visibility = 'visible';
                        });
                        console.log('[WidgetActions] Maximized widget collapsed and overlay hidden.');
                    }, 300); // Match CSS transition duration

                    // Move widget back to its original position (handled by DragDropManager's placeholder)
                    const originalId = maximizedWidget.dataset.widgetId;
                    const originalPlaceholder = document.querySelector(`.widget-placeholder[data-original-id=\"${originalId}\"]`);
                    if (originalPlaceholder && originalPlaceholder.parentNode) {
                        originalPlaceholder.parentNode.insertBefore(maximizedWidget, originalPlaceholder);
                        console.log(`[WidgetActions] Widget ${originalId} moved back to original placeholder position.`);
                    } else {
                        console.warn(`[WidgetActions] Original placeholder for widget ${originalId} not found, cannot restore exact position.`);
                    }
                } else {
                    console.warn('[WidgetActions] Overlay clicked but no maximized widget found.');
                }
            }
        });
    } else {
        console.warn('[WidgetActions] Widget expanded overlay (id="widget-expanded-overlay") not found.');
    }
}
