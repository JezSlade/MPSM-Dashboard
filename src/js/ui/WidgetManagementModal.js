// src/js/ui/WidgetManagementModal.js

import { showMessageModal } from './MessageModal.js';
import { sendAjaxRequest } from '../utils/AjaxService.js';

const widgetManagementNavItem = document.getElementById('widget-management-nav-item');
const widgetManagementModalOverlay = document.getElementById('widget-management-modal-overlay');
const closeWidgetManagementModalBtn = document.getElementById('close-widget-management-modal');
const widgetManagementTableBody = document.getElementById('widget-management-table-body');
const saveWidgetManagementChangesBtn = document.getElementById('save-widget-management-changes-btn');

async function loadWidgetManagementTable() {
    widgetManagementTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading widgets...</td></tr>';
    
    const response = await sendAjaxRequest('api/dashboard.php', 'get_active_widgets_data');

    if (response.status === 'success' && response.widgets) {
        widgetManagementTableBody.innerHTML = ''; // Clear loading message
        if (response.widgets.length === 0) {
            widgetManagementTableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">No active widgets. Add some from Dashboard Settings!</td></tr>';
        } else {
            response.widgets.forEach(widget => {
                const row = document.createElement('tr');
                row.dataset.widgetIndex = widget.index; // Store the original index for saving
                row.dataset.widgetId = widget.id; // Store widget ID for deactivation

                row.innerHTML = `
                    <td><i class="fas fa-${widget.icon}"></i></td>
                    <td>${widget.name}</td>
                    <td>Active</td> <!-- All listed here are active -->
                    <td>
                        <input type="number" class="widget-width-input form-control-small" value="${widget.width}" min="0.5" max="3" step="0.5" data-original-width="${widget.width}">
                    </td>
                    <td>
                        <input type="number" class="widget-height-input form-control-small" value="${widget.height}" min="0.5" max="4" step="0.5" data-original-height="${widget.height}">
                    </td>
                    <td>
                        <span class="widget-management-status">Saved</span>
                    </td>
                    <td>
                        <button class="btn btn-danger btn-deactivate-widget" data-widget-id="${widget.id}">
                            <i class="fas fa-trash-alt"></i> Deactivate
                        </button>
                    </td>
                `;
                widgetManagementTableBody.appendChild(row);
            });

            // Add event listeners for input changes to mark as unsaved
            widgetManagementTableBody.querySelectorAll('.widget-width-input, .widget-height-input').forEach(input => {
                input.addEventListener('input', function() {
                    const row = this.closest('tr');
                    const statusSpan = row.querySelector('.widget-management-status');
                    statusSpan.textContent = 'Unsaved';
                    statusSpan.style.color = 'var(--warning)';
                    saveWidgetManagementChangesBtn.disabled = false;
                });
            });

            // Add event listeners for deactivate buttons
            widgetManagementTableBody.querySelectorAll('.btn-deactivate-widget').forEach(button => {
                button.addEventListener('click', function() {
                    const widgetIdToDeactivate = this.dataset.widgetId;
                    showMessageModal(
                        'Confirm Deactivation',
                        `Are you sure you want to deactivate "${widgetIdToDeactivate}"? It will be removed from your dashboard.`,
                        async () => {
                            const response = await sendAjaxRequest('api/dashboard.php', 'remove_widget_from_management', { widget_id: widgetIdToDeactivate });
                            if (response.status === 'success') {
                                showMessageModal('Success', response.message + ' Reloading dashboard...', () => location.reload(true));
                            } else {
                                showMessageModal('Error', response.message);
                            }
                        }
                    );
                });
            });
        }
    } else {
        widgetManagementTableBody.innerHTML = `<tr><td colspan="7" style="text-align: center; color: var(--danger); padding: 20px;">Error loading widgets: ${response.message}</td></tr>`;
        showMessageModal('Error', `Failed to load widget data for management: ${response.message}`);
    }
    saveWidgetManagementChangesBtn.disabled = true; // Initially disabled
}

export function initWidgetManagementModal() {
    if (widgetManagementNavItem) {
        widgetManagementNavItem.addEventListener('click', async function() {
            widgetManagementModalOverlay.classList.add('active');
            await loadWidgetManagementTable();
        });
    }

    if (closeWidgetManagementModalBtn) {
        closeWidgetManagementModalBtn.addEventListener('click', function() {
            widgetManagementModalOverlay.classList.remove('active');
        });
    }

    if (widgetManagementModalOverlay) {
        widgetManagementModalOverlay.addEventListener('click', function(e) {
            if (e.target === widgetManagementModalOverlay) {
                widgetManagementModalOverlay.classList.remove('active');
            }
        });
    }

    if (saveWidgetManagementChangesBtn) {
        saveWidgetManagementChangesBtn.addEventListener('click', async function() {
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            const rows = widgetManagementTableBody.querySelectorAll('tr');
            let allSuccess = true;
            let messages = [];

            for (const row of rows) {
                const widgetIndex = row.dataset.widgetIndex;
                const widthInput = row.querySelector('.widget-width-input');
                const heightInput = row.querySelector('.widget-height-input');
                const statusSpan = row.querySelector('.widget-management-status');

                const newWidth = parseFloat(widthInput.value);
                const newHeight = parseFloat(heightInput.value);
                const originalWidth = parseFloat(widthInput.dataset.originalWidth);
                const originalHeight = parseFloat(heightInput.dataset.originalHeight);

                // Only save if dimensions have actually changed
                if (newWidth !== originalWidth || newHeight !== originalHeight) {
                    statusSpan.textContent = 'Saving...';
                    statusSpan.style.color = 'var(--info)';

                    const response = await sendAjaxRequest('api/dashboard.php', 'update_single_widget_dimensions', {
                        widget_index: widgetIndex,
                        new_width: newWidth,
                        new_height: newHeight
                    });

                    if (response.status === 'success') {
                        statusSpan.textContent = 'Saved';
                        statusSpan.style.color = 'var(--success)';
                        widthInput.dataset.originalWidth = newWidth; // Update original data
                        heightInput.dataset.originalHeight = newHeight;
                    } else {
                        statusSpan.textContent = 'Error';
                        statusSpan.style.color = 'var(--danger)';
                        allSuccess = false;
                        messages.push(`Widget at index ${widgetIndex} failed to save: ${response.message}`);
                    }
                } else {
                    statusSpan.textContent = 'Saved';
                    statusSpan.style.color = 'inherit'; // Reset color for unchanged items
                }
            }

            this.innerHTML = '<i class="fas fa-save"></i> Save All Widget Changes';

            if (allSuccess) {
                showMessageModal('Success', 'All changes saved. Reloading dashboard...', () => location.reload(true));
            } else {
                showMessageModal('Partial Success/Error', 'Some changes could not be saved: ' + messages.join('. ') + ' Reloading dashboard...', () => location.reload(true));
            }
        });
    }
}
