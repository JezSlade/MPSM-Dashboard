// src/js/features/IdeWidget.js

import { sendAjaxRequest } from '../utils/AjaxService.js';
import { showMessageModal } from '../ui/MessageModal.js';

const ideBreadcrumb    = document.getElementById('ide-breadcrumb');
const ideFileTree      = document.getElementById('ide-file-tree');
const ideCodeEditor    = document.getElementById('ide-code-editor');
const ideSaveButton    = document.getElementById('ide-save-button');
const ideCurrentFileName = document.getElementById('ide-current-file-name');
const ideFileStatus    = document.getElementById('ide-file-status');

let currentFilePath = '';
let originalFileContent = '';
let isUnsaved = false;

/**
 * Updates the file status indicator (Saved/Unsaved).
 * @param {boolean} unsaved True if there are unsaved changes, false otherwise.
 */
function updateFileStatus(unsaved) {
    isUnsaved = unsaved;
    if (ideFileStatus) {
        if (unsaved) {
            ideFileStatus.textContent = 'Unsaved';
            ideFileStatus.classList.remove('ide-status-saved');
            ideFileStatus.classList.add('ide-status-unsaved');
        } else {
            ideFileStatus.textContent = 'Saved';
            ideFileStatus.classList.remove('ide-status-unsaved');
            ideFileStatus.classList.add('ide-status-saved');
        }
    }
}

/**
 * Loads the content of a file into the editor.
 * @param {string} path The relative path of the file to load.
 */
async function loadFileContent(path) {
    if (isUnsaved && !confirm('You have unsaved changes. Discard them and open new file?')) {
        return; // User chose not to discard unsaved changes
    }

    currentFilePath = path;
    ideCurrentFileName.textContent = path; // Update displayed path
    ideCodeEditor.value = ''; // Clear editor
    updateFileStatus(false); // Reset status to saved initially
    ideCodeEditor.disabled = true; // Disable editor while loading
    ideSaveButton.disabled = true; // Disable save button

    // Show loading indicator
    ideCodeEditor.placeholder = 'Loading file...';
    ideFileStatus.textContent = 'Loading...';
    ideFileStatus.classList.remove('ide-status-saved', 'ide-status-unsaved');

    const response = await sendAjaxRequest('api/ide.php', 'read_file', { file: path });

    ideCodeEditor.disabled = false; // Enable editor

    if (response.status === 'success') {
        originalFileContent = response.data;
        ideCodeEditor.value = response.data;
        updateFileStatus(false); // Set to saved
    } else {
        ideCodeEditor.value = `Error loading file: ${response.message}`;
        ideCodeEditor.placeholder = 'Error loading file.';
        updateFileStatus(true); // Treat as unsaved error state
        showMessageModal('Error', `Failed to load file: ${response.message}`);
    }
}

/**
 * Saves the current content of the editor to the file.
 */
async function saveFileContent() {
    if (!currentFilePath) {
        showMessageModal('Error', 'No file selected to save.');
        return;
    }

    const newContent = ideCodeEditor.value;
    if (newContent === originalFileContent) {
        showMessageModal('Info', 'No changes to save.');
        updateFileStatus(false);
        return;
    }

    ideSaveButton.disabled = true;
    ideSaveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    updateFileStatus(true); // Indicate saving in progress

    const response = await sendAjaxRequest('api/ide.php', 'save_file', {
        file: currentFilePath,
        content: newContent
    });

    ideSaveButton.disabled = false;
    ideSaveButton.innerHTML = '<i class="fas fa-save"></i> Save';

    if (response.status === 'success') {
        originalFileContent = newContent; // Update original content after successful save
        updateFileStatus(false); // Set to saved
        showMessageModal('Success', response.message);
    } else {
        showMessageModal('Error', `Failed to save file: ${response.message}`);
        // Keep status as unsaved if save failed
    }
}

/**
 * Render a clickable breadcrumb trail above the file tree.
 * @param {string} currentPath
 */
function renderBreadcrumb(currentPath) {
    if (!ideBreadcrumb) return;

    // Split path into segments
    const parts = currentPath.split('/').filter(p => p !== '');
    const crumbs = [];
    let cumulative = '';

    // Root crumb
    crumbs.push(`<span class="breadcrumb-item" data-path="">Root</span>`);

    parts.forEach(part => {
        cumulative += (cumulative ? '/' : '') + part;
        crumbs.push(
            `<span class="breadcrumb-separator">/</span>` +
            `<span class="breadcrumb-item" data-path="${cumulative}">${part}</span>`
        );
    });

    ideBreadcrumb.innerHTML = crumbs.join('');

    // Attach click listeners
    ideBreadcrumb.querySelectorAll('.breadcrumb-item').forEach(el => {
        el.addEventListener('click', () => {
            const path = el.dataset.path;
            renderFileTree(path);
        });
    });
}

/**
 * Renders the file tree in the sidebar.
 * @param {string} currentPath The current directory path to list.
 */
async function renderFileTree(currentPath) {
    // Render breadcrumb for navigation
    renderBreadcrumb(currentPath);

    ideFileTree.innerHTML = `<div class="ide-loading-indicator">
        <i class="fas fa-spinner fa-spin"></i> Loading tree...
    </div>`;

    const response = await sendAjaxRequest('api/ide.php', 'list_files', { path: currentPath });

    ideFileTree.innerHTML = ''; // Clear loading indicator

    if (response.status === 'success' && Array.isArray(response.data)) {
        response.data.forEach(item => {
            const li = document.createElement('li');
            li.classList.add('ide-file-tree-item');
            if (item.path === currentFilePath) {
                li.classList.add('active'); // Highlight active file
            }
            if (!item.is_writable && item.type === 'file') {
                li.classList.add('ide-item-read-only');
                li.title = 'Read-only file';
            }

            const iconClass = item.type === 'dir' ? 'fas fa-folder' : 'fas fa-file-code';
            li.innerHTML = `<i class="${iconClass}"></i><span>${item.name}</span>`;
            li.dataset.path = item.path;
            li.dataset.type = item.type;

            li.addEventListener('click', () => {
                if (item.type === 'dir') {
                    renderFileTree(item.path); // Navigate into directory
                } else {
                    if (item.is_writable) {
                        loadFileContent(item.path); // Load file into editor
                    } else {
                        showMessageModal('Info', 'This file is read-only and cannot be edited.');
                        loadFileContent(item.path); // Still load for viewing
                    }
                    // Update active highlight
                    document.querySelectorAll('.ide-file-tree-item.active')
                        .forEach(el => el.classList.remove('active'));
                    li.classList.add('active');
                }
            });

            ideFileTree.appendChild(li);
        });
    } else {
        ideFileTree.innerHTML = `<div class="ide-error-indicator">
            Error loading file tree: ${response.message}
        </div>`;
        showMessageModal('Error', `Failed to load file tree: ${response.message}`);
    }
}

/**
 * Initializes the IDE widget.
 * This function is called when ide.php is loaded.
 */
function initIde() {
    // Save button
    if (ideSaveButton) {
        ideSaveButton.addEventListener('click', saveFileContent);
    }

    // Editor change => unsaved status
    if (ideCodeEditor) {
        ideCodeEditor.addEventListener('input', () => {
            if (ideCodeEditor.value !== originalFileContent) {
                updateFileStatus(true);
            } else {
                updateFileStatus(false);
            }
        });
    }

    // Initial load
    const initialPath = window.initialFilePath || '';
    renderFileTree(initialPath.substring(0, initialPath.lastIndexOf('/')) || '');
    if (initialPath) {
        loadFileContent(initialPath);
    } else {
        ideCodeEditor.value = "// Select a file from the left panel to start editing.";
        ideCodeEditor.disabled = true;
        ideSaveButton.disabled = true;
    }

    // Warn on unsaved changes
    window.addEventListener('beforeunload', (event) => {
        if (isUnsaved) {
            event.preventDefault();
            event.returnValue = '';
            return '';
        }
    });
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initIde);

// Export for external use if needed
export { initIde };
