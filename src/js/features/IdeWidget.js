// src/js/features/IdeWidget.js

import { sendAjaxRequest } from '../utils/AjaxService.js';
import { showMessageModal } from '../ui/MessageModal.js';

let currentEditingFile = null; // Current file being edited in the IDE

// Function to update file status (Saved, Unsaved)
// This function needs `this.ideFileStatus` to be bound to the correct element
function setFileStatus(status, ideFileStatusEl) {
    if (!ideFileStatusEl) return; 

    ideFileStatusEl.classList.remove('ide-status-saved', 'ide-status-unsaved');
    if (status === 'saved') {
        ideFileStatusEl.textContent = 'Saved';
        ideFileStatusEl.classList.add('ide-status-saved');
    } else if (status === 'unsaved') {
        ideFileStatusEl.textContent = 'Unsaved Changes';
        ideFileStatusEl.classList.add('ide-status-unsaved');
    } else {
        ideFileStatusEl.textContent = ''; // Clear status
    }
}

// This internal function helps with re-scoping elements after widget expansion
async function loadIdeFileTreeInternal(fileTreeEl, pathDisplayEl, codeEditorEl, fileNameDisplayEl, saveBtnEl, fileStatusEl, path) {
    fileTreeEl.innerHTML = '<li class="ide-loading-indicator"><i class="fas fa-spinner fa-spin"></i> Loading files...</li>';
    pathDisplayEl.textContent = path === '.' ? '/' : `/${path}`; // Display root as /

    const response = await sendAjaxRequest('api/ide.php', 'ide_list_files', { path: path });

    if (response.status === 'success' && response.files) {
        pathDisplayEl.textContent = response.current_path === '' ? '/' : `/${response.current_path}`;

        fileTreeEl.innerHTML = ''; // Clear loading indicator
        response.files.forEach(item => {
            const li = document.createElement('li');
            li.classList.add('ide-file-tree-item');
            li.dataset.path = item.path;
            li.dataset.type = item.type;
            li.dataset.name = item.name; // Store name for display later

            let icon = '';
            if (item.type === 'dir') {
                icon = '<i class="fas fa-folder"></i>';
            } else {
                icon = '<i class="fas fa-file"></i>';
            }

            li.innerHTML = `${icon} <span>${item.name}</span>`;
            if (!item.is_writable) {
                 li.classList.add('ide-item-read-only');
                 li.title = 'Not writable';
            }

            fileTreeEl.appendChild(li);
        });
    } else {
        fileTreeEl.innerHTML = `<li class="ide-error-indicator"><i class="fas fa-exclamation-triangle"></i> Error: ${response.message}</li>`;
        showMessageModal('IDE Error', `Failed to load files: ${response.message}`);
    }
}

// Initialize IDE on widget expansion
export function initIdeWidget(widget) {
    // Get elements within the *specific* expanded widget
    const ideFileTreeEl = widget.querySelector('#ide-file-tree');
    const ideCurrentPathDisplayEl = widget.querySelector('#ide-current-path');
    const ideCodeEditorEl = widget.querySelector('#ide-code-editor');
    const ideFileNameDisplayEl = widget.querySelector('#ide-current-file-name');
    const ideFileStatusEl = widget.querySelector('#ide-file-status');
    const ideSaveBtnEl = widget.querySelector('#ide-save-btn');

    if (!ideFileTreeEl || !ideCodeEditorEl || !ideSaveBtnEl || !ideFileStatusEl || !ideFileNameDisplayEl || !ideCurrentPathDisplayEl) {
        console.warn("IDE elements not found inside the expanded widget. Skipping IDE initialization.");
        return;
    }

    // Reset editor state
    ideCodeEditorEl.value = '';
    ideCodeEditorEl.readOnly = true;
    ideFileNameDisplayEl.textContent = 'No file selected';
    ideSaveBtnEl.disabled = true;
    setFileStatus('', ideFileStatusEl); // Call setFileStatus with correct context
    currentEditingFile = null;

    // Load root directory initially
    loadIdeFileTreeInternal(ideFileTreeEl, ideCurrentPathDisplayEl, ideCodeEditorEl, ideFileNameDisplayEl, ideSaveBtnEl, ideFileStatusEl, '.');
}

export function initIdeEventListeners() {
    // Handle file tree clicks (delegated to document.body because IDE widget is dynamic)
    document.body.addEventListener('click', async function(e) {
        const item = e.target.closest('.ide-file-tree-item');
        // Ensure the clicked item is part of an expanded IDE widget
        if (!item || !item.closest('.widget.maximized[data-widget-id="ide"]')) return;

        // Re-get elements within the context of the active IDE widget
        const ideContainer = item.closest('.ide-container');
        const currentIdeCodeEditor = ideContainer.querySelector('#ide-code-editor');
        const currentIdeFileNameDisplay = ideContainer.querySelector('#ide-current-file-name');
        const currentIdeSaveBtn = ideContainer.querySelector('#ide-save-btn');
        const currentIdeFileStatus = ideContainer.querySelector('#ide-file-status');
        const currentIdeFileTree = ideContainer.querySelector('#ide-file-tree');
        const currentIdeCurrentPathDisplay = ideContainer.querySelector('#ide-current-path');

        const path = item.dataset.path;
        const type = item.dataset.type;
        const isWritable = !item.classList.contains('ide-item-read-only');

        if (type === 'dir') {
            loadIdeFileTreeInternal(currentIdeFileTree, currentIdeCurrentPathDisplay, currentIdeCodeEditor, currentIdeFileNameDisplay, currentIdeSaveBtn, currentIdeFileStatus, path);
            currentEditingFile = null; // Clear current file when navigating directories
            currentIdeCodeEditor.value = '';
            currentIdeCodeEditor.readOnly = true;
            currentIdeFileNameDisplay.textContent = 'No file selected';
            currentIdeSaveBtn.disabled = true;
            setFileStatus('', currentIdeFileStatus);
        } else if (type === 'file') {
            if (!isWritable) {
                showMessageModal('Read-Only File', `"${item.dataset.name}" is not writable.`);
                currentIdeCodeEditor.readOnly = true;
                currentIdeSaveBtn.disabled = true;
            } else {
                currentIdeCodeEditor.readOnly = false;
                currentIdeSaveBtn.disabled = false;
            }

            currentIdeFileNameDisplay.textContent = item.dataset.name;
            setFileStatus('saved', currentIdeFileStatus); // Assume saved until changes are made

            const response = await sendAjaxRequest('api/ide.php', 'ide_read_file', { path: path });
            if (response.status === 'success' && typeof response.content === 'string') {
                currentIdeCodeEditor.value = response.content;
                currentEditingFile = path;
            } else {
                currentIdeCodeEditor.value = `Error loading file: ${response.message}`;
                currentEditingFile = null;
                currentIdeCodeEditor.readOnly = true;
                currentIdeSaveBtn.disabled = true;
                setFileStatus('', currentIdeFileStatus);
                showMessageModal('IDE Error', `Failed to read file: ${response.message}`);
            }
        }
    });

    // Handle editor content changes (mark as unsaved) - delegated
    document.body.addEventListener('input', function(e) {
        const editor = e.target.closest('.ide-code-editor');
        if (editor && editor.closest('.widget.maximized[data-widget-id="ide"]')) {
            const currentIdeFileStatus = editor.closest('.ide-container').querySelector('#ide-file-status');
            const currentIdeSaveBtn = editor.closest('.ide-container').querySelector('#ide-save-btn');
            
            setFileStatus('unsaved', currentIdeFileStatus);
            if (currentIdeSaveBtn) currentIdeSaveBtn.disabled = editor.readOnly;
        }
    });

    // Handle save button click - delegated
    document.body.addEventListener('click', async function(e) {
        const saveBtn = e.target.closest('#ide-save-btn');
        if (!saveBtn || !saveBtn.closest('.widget.maximized[data-widget-id="ide"]')) return;

        const currentIdeCodeEditor = saveBtn.closest('.ide-container').querySelector('#ide-code-editor');
        const currentIdeFileStatus = saveBtn.closest('.ide-container').querySelector('#ide-file-status');

        if (!currentEditingFile || currentIdeCodeEditor.readOnly) {
            showMessageModal('Action Not Allowed', 'No file selected or file is read-only.');
            return;
        }

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        const response = await sendAjaxRequest('api/ide.php', 'ide_save_file', {
            path: currentEditingFile,
            content: currentIdeCodeEditor.value
        });

        if (response.status === 'success') {
            setFileStatus('saved', currentIdeFileStatus);
            showMessageModal('Success', response.message);
        } else {
            setFileStatus('unsaved', currentIdeFileStatus); // Remain unsaved if save failed
            showMessageModal('Error', `Save failed: ${response.message}`);
        }
        saveBtn.innerHTML = '<i class="fas fa-save"></i> Save';
        saveBtn.disabled = currentIdeCodeEditor.readOnly; // Keep disabled if read-only
    });
}
