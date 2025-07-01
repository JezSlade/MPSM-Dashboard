// src/js/ui/WidgetManagementModal.js

import { showMessageModal } from '../ui/MessageModal.js';
import { sendAjaxRequest } from '../utils/AjaxService.js';

const widgetManagementNavItem = document.getElementById('widget-management-nav-item');
const widgetManagementModalOverlay = document.getElementById('widget-management-modal-overlay');
const closeWidgetManagementModalBtn = document.getElementById('close-widget-management-modal');
const widgetManagementTableBody = document.getElementById('widget-management-table-body');
const saveWidgetManagementChangesBtn = document.getElementById('save-widget-management-changes-btn');

// Store current widget states for tracking changes
let currentWidgetStates = {};

/**
 * Loads and displays all widget states in the management table.
 */
async function loadWidgetManagementTable() {
    widgetManagementTableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading widgets...</td></tr>';
    currentWidgetStates = {}; // Reset states

    const response = await sendAjaxRequest('api/dashboard.php', 'get_all_widget_states');

    if (response.status === 'success' && response.widgets_state) {
        widgetManagementTableBody.innerHTML = ''; // Clear loading message
        if (response.widgets_state.length === 0) {
            widgetManagementTableBody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;">No widgets found.</td></tr>';
            return;
        }

        // Sort widgets by their 'position' property for consistent display in the table
        response.widgets_state.sort((a, b) => a.position - b.position);

        response.widgets_state.forEach(widget => {
            const row = document.createElement('tr');
            row.dataset.widgetId = widget.id; // Store widget ID on the row

            // Store initial state for change tracking
            currentWidgetStates[widget.id] = {
                width: widget.width,
                height: widget.height,
                is_active: widget.is_active
            };

            const isActiveChecked = widget.is_active ? 'checked' : '';
            const statusText = widget.is_active ? 'Active' : 'Inactive';
            const statusClass = widget.is_active ? 'text-green-500' : 'text-red-500'; // Tailwind classes for status color (add to dashboard.css if not present)

            row.innerHTML = `
                <td><i class="fas fa-${widget.icon}"></i></td>
                <td>${widget.name}</td>
                <td><span class="${statusClass}">${statusText}</span></td>
                <td><input type="number" class="form-control-small widget-width-input" value="${widget.width}" min="0.5" max="3" step="0.5"></td>
                <td><input type="number" class="form-control-small widget-height-input" value="${widget.height}" min="0.5" max="4" step="0.5"></td>
                <td>
                    <label class="toggle-switch">
                        <input type="checkbox" class="widget-active-toggle" ${isActiveChecked}>
                        <span class="slider"></span>
                    </label>
                </td>
            `;
            widgetManagementTableBody.appendChild(row);
        });
    } else {
        widgetManagementTableBody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 20px; color: var(--danger);">Error loading widgets: ${response.message || 'Unknown error.'}</td></tr>`;
    }
}

/**
 * Saves all changes made in the widget management table.
 */
async function saveAllWidgetChanges() {
    const rows = widgetManagementTableBody.querySelectorAll('tr[data-widget-id]');
    const changesToSave = [];

    rows.forEach(row => {
        const widgetId = row.dataset.widgetId;
        const newWidth = parseFloat(row.querySelector('.widget-width-input').value);
        const newHeight = parseFloat(row.querySelector('.widget-height-input').value);
        const newIsActive = row.querySelector('.widget-active-toggle').checked;

        // Check if values have actually changed before adding to save list
        const originalState = currentWidgetStates[widgetId];
        if (originalState && (originalState.width !== newWidth || originalState.height !== newHeight || originalState.is_active !== newIsActive)) {
            changesToSave.push({
                id: widgetId,
                width: newWidth,
                height: newHeight,
                is_active: newIsActive
            });
        }
    });

    if (changesToSave.length === 0) {
        showMessageModal('No Changes', 'No changes detected to save.');
        return;
    }

    // Disable button and show loading
    saveWidgetManagementChangesBtn.disabled = true;
    saveWidgetManagementChangesBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

    // Send all changes in one go (or iterate if backend expects individual calls)
    // For simplicity and efficiency, we'll send a single request with all changes.
    // The backend will need an action to handle an array of widget updates.
    // For now, let's send individual requests for clarity based on current backend.
    // A more advanced refactor would have a single 'batch_update_widgets' action.

    let successCount = 0;
    let errorCount = 0;
    let errorMessage = '';

    for (const change of changesToSave) {
        let response;
        if (currentWidgetStates[change.id].width !== change.width || currentWidgetStates[change.id].height !== change.height) {
            // Only send dimension update if dimensions changed
            response = await sendAjaxRequest('api/dashboard.php', 'update_single_widget_dimensions', {
                widget_id: change.id,
                new_width: change.width,
                new_height: change.height
            });
            if (response.status === 'success') {
                successCount++;
            } else {
                errorCount++;
                errorMessage += `Failed to update dimensions for ${change.id}: ${response.message}\n`;
            }
        }
        
        if (currentWidgetStates[change.id].is_active !== change.is_active) {
            // Only send active status update if status changed
            response = await sendAjaxRequest('api/dashboard.php', 'toggle_widget_active_status', {
                widget_id: change.id,
                is_active: change.is_active ? '1' : '0' // Send as string '1' or '0' for PHP filter_var
            });
            if (response.status === 'success') {
                successCount++;
            } else {
                errorCount++;
                errorMessage += `Failed to update status for ${change.id}: ${response.message}\n`;
            }
        }
    }

    saveWidgetManagementChangesBtn.disabled = false;
    saveWidgetManagementChangesBtn.innerHTML = 'Save All Widget Changes';

    if (errorCount === 0) {
        showMessageModal('Success', `All ${successCount} changes saved successfully! Reloading dashboard...`, () => location.reload(true));
    } else {
        showMessageModal('Partial Success/Error', `Saved ${successCount} changes, but ${errorCount} failed:\n${errorMessage} Reloading dashboard...`, () => location.reload(true));
    }
}


export function initWidgetManagementModal() {
    if (widgetManagementNavItem) {
        widgetManagementNavItem.addEventListener('click', function() {
            widgetManagementModalOverlay.classList.add('active');
            loadWidgetManagementTable(); // Load data when modal opens
        });
    }

    if (closeWidgetManagementModalBtn) {
        closeWidgetManagementModalBtn.addEventListener('click', function() {
            widgetManagementModalOverlay.classList.remove('active');
        });
    }

    if (saveWidgetManagementChangesBtn) {
        saveWidgetManagementChangesBtn.addEventListener('click', saveAllWidgetChanges);
    }
}
