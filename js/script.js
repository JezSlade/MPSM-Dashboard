/**
 * MPSM Dashboard - Main JavaScript File
 *
 * This file contains all the client-side JavaScript for the MPSM Dashboard.
 * It handles interactive elements such as theme switching, the searchable customer dropdown,
 * and the functionality of the debug panel (toggle, clear, drag).
 *
 * Debugging Philosophy:
 * console.log statements are used extensively within this JS file to trace execution,
 * verify element selections, and debug interactive behaviors directly in the browser's
 * developer console. These logs should be comprehensive to aid troubleshooting.
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded. Initializing JavaScript components...');

    // --- Theme Toggle Functionality ---
    const themeToggleBtn = document.getElementById('theme-toggle');
    const body = document.body;

    if (themeToggleBtn && body) {
        console.log('Theme toggle button found. Initializing theme functionality.');
        themeToggleBtn.addEventListener('click', () => {
            body.classList.toggle('light-mode');
            const newTheme = body.classList.contains('light-mode') ? 'light' : 'dark';
            console.log('Theme switched to:', newTheme);
            localStorage.setItem('dashboardTheme', newTheme);
        });

        // Load persisted theme
        const savedTheme = localStorage.getItem('dashboardTheme');
        if (savedTheme === 'light') {
            body.classList.add('light-mode');
            console.log('Applied saved light theme.');
        }
    }


    // --- Customer Selection Dropdown with Search Functionality ---
    const customerSelect       = document.getElementById('customer-select');
    const customerSearchInput  = document.getElementById('customer-search');

    if (customerSelect && customerSearchInput) {
        console.log('Customer selection elements found. Initializing customer filter.');

        // Initially hide the dropdown and show the search input
        customerSelect.style.display = 'none';
        customerSearchInput.classList.add('active');
        console.log('Customer dropdown hidden, search input shown.');

        // Cache the original options for filtering
        const originalOptions = Array.from(customerSelect.options).map(option => ({
            value: option.value,
            text:  option.textContent.trim()
        }));
        console.log('Original customer options cached:', originalOptions);

        // Filter on typing
        customerSearchInput.addEventListener('input', function() {
            const term = customerSearchInput.value.toLowerCase();
            console.log('Customer search input changed. Search term:', term);

            // Clear and rebuild
            customerSelect.innerHTML = '';
            customerSelect.style.display = 'block'; // show to update
            let firstMatch = null;

            originalOptions.forEach(opt => {
                if (opt.text.toLowerCase().includes(term)) {
                    const newOpt = document.createElement('option');
                    newOpt.value = opt.value;
                    newOpt.textContent = opt.text;
                    customerSelect.appendChild(newOpt);
                    if (!firstMatch) {
                        firstMatch = opt.value;
                    }
                }
            });

            // Auto-select the first match if any
            if (firstMatch) {
                customerSelect.value = firstMatch;
                console.log('Auto-selected first match:', firstMatch);
            } else {
                customerSelect.value = '';
                console.log('No match found for term.');
            }

            // Hide dropdown again after rebuild
            customerSelect.style.display = 'none';
        });

        // Prefill search input if customer_id is in URL
        const initCid = new URLSearchParams(window.location.search).get('customer_id');
        if (initCid) {
            const found = originalOptions.find(o => o.value === initCid);
            if (found) {
                customerSearchInput.value = found.text;
                console.log('Prefilled search with:', found.text);
            }
        }

        // **NEW**: apply immediately when the user picks from the dropdown
        customerSelect.addEventListener('change', function() {
            const selectedCustomerId = this.value;
            const currentView = new URLSearchParams(window.location.search).get('view') || 'dashboard';
            console.log('Customer selected. Redirecting:', selectedCustomerId);

            if (selectedCustomerId) {
                window.location.href =
                    `?view=${encodeURIComponent(currentView)}&customer_id=${encodeURIComponent(selectedCustomerId)}`;
            } else {
                // No selection => remove customer_id
                const url = new URL(window.location.href);
                url.searchParams.delete('customer_id');
                window.location.href = url.toString();
            }
        });
    }


    // --- Debug Panel Toggle & Dragging (unchanged) ---
    const debugPanel   = document.getElementById('debug-panel');
    const debugToggle  = document.getElementById('debug-toggle');
    const clearLogBtn  = document.getElementById('clear-log-btn');

    // Toggle visibility
    if (debugToggle && debugPanel) {
        debugToggle.addEventListener('click', () => {
            debugPanel.classList.toggle('hidden');
        });
    }

    // Clear contents
    if (clearLogBtn) {
        clearLogBtn.addEventListener('click', () => {
            const output = debugPanel.querySelector('.debug-log-output');
            if (output) output.textContent = '';
        });
    }

    // Drag functionality
    let isDragging = false, offsetX = 0, offsetY = 0;
    const debugHeader = debugPanel.querySelector('.debug-header');
    if (debugHeader) {
        debugHeader.addEventListener('mousedown', e => {
            isDragging = true;
            offsetX = e.clientX - debugPanel.getBoundingClientRect().left;
            offsetY = e.clientY - debugPanel.getBoundingClientRect().top;
            debugPanel.style.cursor = 'grabbing';
        });
        document.addEventListener('mousemove', e => {
            if (!isDragging) return;
            debugPanel.style.left = `${e.clientX - offsetX}px`;
            debugPanel.style.top  = `${e.clientY - offsetY}px`;
        });
        document.addEventListener('mouseup', () => {
            isDragging = false;
            debugPanel.style.cursor = 'default';
        });
    }

    console.log('JavaScript initialization complete.');
});
