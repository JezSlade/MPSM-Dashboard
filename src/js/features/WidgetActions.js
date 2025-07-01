// src/js/features/WidgetActions.js

import { showMessageModal } from '../ui/MessageModal.js';
import { showWidgetSettingsModal } from '../ui/WidgetSettingsModal.js';
import { sendAjaxRequest } from '../utils/AjaxService.js';
import { initIdeWidget } from './IdeWidget.js'; // Import IDE initialization

const mainContent = document.getElementById('widget-container');
const expandedOverlay = document.getElementById('widget-expanded-overlay');

// Helper function to toggle widget expansion state
function toggleWidgetExpansion(widget) {
    const widgetPlaceholder = widget.querySelector('.widget-placeholder');
    const expandIcon = widget.querySelector('.action-expand i');

    if (!widget.classList.contains('maximized')) {
        // MAXIMIZE Logic:
        if (!widgetPlaceholder) {
            console.error("Widget placeholder not found!");
            return;
        }
        widgetPlaceholder.dataset.originalParentId = widget.parentNode.id;
        widgetPlaceholder.dataset.originalIndex = Array.from(widget.parentNode.children).indexOf(widget);
        widgetPlaceholder.style.display = 'block'; // Make placeholder visible to hold space

        widget.classList.add('maximized');
        document.body.classList.add('expanded-active');
        expandedOverlay.classList.add('active');
        expandedOverlay.appendChild(widget); // Move widget to overlay
        
        if (expandIcon) expandIcon.classList.replace('fa-expand', 'fa-compress');

        // NEW: If the expanded widget is the IDE, initialize/refresh its file tree
        if (widget.dataset.widgetId === 'ide') {
            initIdeWidget(widget);
        }

    } else {
        // MINIMIZE Logic:
        const originalParent = document.getElementById(widgetPlaceholder.dataset.originalParentId);
        const originalIndex = parseInt(widgetPlaceholder.dataset.originalIndex);

        if (originalParent && originalParent.children[originalIndex]) {
            originalParent.insertBefore(widget, originalParent.children[originalIndex]);
        } else if (originalParent) {
            originalParent.appendChild(widget);
        } else {
            console.error("Original parent not found for widget ID:", widget.id);
            mainContent.appendChild(widget);
        }

        widget.classList.remove('maximized');
        document.body.classList.remove('expanded-active');
        expandedOverlay.classList.remove('active');
        if (widgetPlaceholder) {
            widgetPlaceholder.style.display = 'none';
        }
        if (expandIcon) expandIcon.classList.replace('fa-compress', 'fa-expand');
    }
}

export function initWidgetActions() {
    // --- Widget Actions (delegated listener on document.body) ---
    document.body.addEventListener('click', async function(e) {
        const target = e.target.closest('.widget-action');

        if (target) {
            const widget = target.closest('.widget');
            if (!widget) return;

            // Handle Settings Action (Cog Icon) for individual widget
            if (target.classList.contains('action-settings')) {
                const widgetName = widget.querySelector('.widget-title span').textContent;
                const widgetIndex = widget.dataset.widgetIndex;
                const currentWidth = parseFloat(widget.dataset.currentWidth);
                const currentHeight = parseFloat(widget.dataset.currentHeight);

                showWidgetSettingsModal(widgetName, widgetIndex, currentWidth, currentHeight);

            }
            // Handle Expand/Shrink Action (Expand Icon)
            else if (target.classList.contains('action-expand')) {
                toggleWidgetExpansion(widget);
            }
            // Handle Remove Widget Action (Times Icon)
            else if (target.classList.contains('remove-widget')) {
                // If the remove button is disabled (due to 'Show All Widgets' mode), do nothing
                if (target.classList.contains('disabled')) {
                    showMessageModal('Information', 'This widget cannot be removed in "Show All Widgets" mode.');
                    return;
                }

                const widgetIndex = target.getAttribute('data-index');
                if (widget.classList.contains('maximized')) {
                    toggleWidgetExpansion(widget); // Minimize if maximized
                } else if (widgetIndex !== null && widgetIndex !== undefined) {
                    showMessageModal(
                        'Confirm Removal',
                        'Are you sure you want to remove this widget from the dashboard?',
                        function() {
                            // Use AJAX for removal
                            sendAjaxRequest('api/dashboard.php', 'remove_widget_from_management', { widget_id: widget.dataset.widgetId })
                                .then(response => {
                                    if (response.status === 'success') {
                                        showMessageModal('Success', response.message, () => location.reload(true));
                                    } else {
                                        showMessageModal('Error', response.message);
                                    }
                                });
                        }
                    );
                }
            }
        }
    });

    // Close expanded widget when clicking on the expanded overlay
    expandedOverlay.addEventListener('click', function(e) {
        if (e.target === expandedOverlay) {
            const activeMaximizedWidget = document.querySelector('.widget.maximized');
            if (activeMaximizedWidget) {
                toggleWidgetExpansion(activeMaximizedWidget);
            }
        }
    });
}
