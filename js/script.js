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

        // Function to set the theme based on local storage or system preference
        function applyTheme(theme) {
            console.log('Applying theme:', theme);
            if (theme === 'dark') {
                body.classList.remove('theme-light');
                body.classList.add('theme-dark');
            } else {
                body.classList.remove('theme-dark');
                body.classList.add('theme-light');
            }
            localStorage.setItem('mpsm_theme', theme); // Save preference
            console.log('Theme applied and saved to localStorage:', theme);
        }

        // Check for saved theme preference or system preference on load
        const savedTheme = localStorage.getItem('mpsm_theme');
        if (savedTheme) {
            applyTheme(savedTheme);
            console.log('Loaded theme from localStorage:', savedTheme);
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            // Check for system dark mode preference
            applyTheme('dark');
            console.log('Applying system dark mode preference.');
        } else {
            // Default to light theme if no preference found
            applyTheme('light'); // Explicitly set light as default if no dark preference
            console.log('Defaulting to light theme.');
        }

        // Add event listener for theme toggle button
        themeToggleBtn.addEventListener('click', function() {
            const currentTheme = body.classList.contains('theme-dark') ? 'dark' : 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            applyTheme(newTheme);
            console.log('Theme toggled from', currentTheme, 'to', newTheme);
        });
    } else {
        console.warn('Theme toggle button or body element not found. Theme functionality skipped.');
    }

    // --- Customer Selection Dropdown with Search Functionality ---
    const customerSelect = document.getElementById('customer-select');
    const customerSearchInput = document.getElementById('customer-search');
    const applyCustomerFilterBtn = document.getElementById('apply-customer-filter');

    if (customerSelect && customerSearchInput && applyCustomerFilterBtn) {
        console.log('Customer selection elements found. Initializing customer filter.');

        // Hide the dropdown and show the search input initially
        customerSelect.style.display = 'none';
        customerSearchInput.classList.add('active');
        console.log('Customer dropdown hidden, search input shown.');

        // Cache the original options for filtering
        const originalOptions = Array.from(customerSelect.options).map(option => ({
            value: option.value,
            text: option.textContent
        }));
        console.log('Original customer options cached:', originalOptions);

        customerSearchInput.addEventListener('input', function() {
            const searchTerm = customerSearchInput.value.toLowerCase();
            console.log('Customer search input changed. Search term:', searchTerm);

            // Clear current options
            customerSelect.innerHTML = '';
            customerSelect.style.display = 'block'; // Temporarily show to update options

            let matchFound = false;
            let firstMatchValue = null;

            // Add a default "Select Customer" option
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = '-- Select Customer --';
            customerSelect.appendChild(defaultOption);

            originalOptions.forEach(option => {
                if (option.value === '') return; // Skip the initial default option

                if (option.text.toLowerCase().includes(searchTerm)) {
                    const newOption = document.createElement('option');
                    newOption.value = option.value;
                    newOption.textContent = option.text;
                    customerSelect.appendChild(newOption);
                    if (!matchFound) {
                        firstMatchValue = option.value;
                        matchFound = true;
                    }
                    console.log('Found match for customer:', option.text);
                }
            });

            // If a single exact match or first fuzzy match is found, select it
            if (firstMatchValue) {
                customerSelect.value = firstMatchValue;
                console.log('Automatically selected first matching customer:', firstMatchValue);
            } else {
                customerSelect.value = ''; // No match, reset selection
                console.log('No customer match found for search term.');
            }

            customerSelect.style.display = 'none'; // Hide dropdown again after updating
        });

        // Event listener for the "Apply Filter" button
        applyCustomerFilterBtn.addEventListener('click', function() {
            const selectedCustomerId = customerSelect.value; // This will hold the value from the filtered dropdown
            const currentView = new URLSearchParams(window.location.search).get('view') || 'dashboard'; // Get current view

            console.log('Apply Filter button clicked. Selected Customer ID:', selectedCustomerId);

            if (selectedCustomerId) {
                // Redirect with the selected customer ID and current view
                window.location.href = `?view=${encodeURIComponent(currentView)}&customer_id=${encodeURIComponent(selectedCustomerId)}`;
                console.log('Redirecting to:', `?view=${encodeURIComponent(currentView)}&customer_id=${encodeURIComponent(selectedCustomerId)}`);
            } else {
                // If no customer is selected, remove customer_id from URL
                const url = new URL(window.location.href);
                url.searchParams.delete('customer_id');
                window.location.href = url.toString();
                console.log('No customer selected, redirecting to remove customer_id from URL:', url.toString());
            }
        });

        // Initialize search input value if customer_id is already in URL
        const initialCustomerId = new URLSearchParams(window.location.search).get('customer_id');
        if (initialCustomerId) {
            const selectedOption = originalOptions.find(option => option.value == initialCustomerId);
            if (selectedOption) {
                customerSearchInput.value = selectedOption.text;
                console.log('Pre-filled customer search input with:', selectedOption.text);
            }
        }
    } else {
        console.warn('Customer selection elements not fully found. Customer filter functionality skipped.');
    }

    // --- Debug Panel Functionality ---
    const debugPanel = document.getElementById('debug-panel');
    const debugToggleVisibilityBtn = document.getElementById('debug-toggle-visibility');
    const debugClearLogBtn = document.getElementById('debug-clear-log');
    const debugLogOutput = document.getElementById('debug-log-output');

    if (debugPanel && debugToggleVisibilityBtn && debugClearLogBtn && debugLogOutput) {
        console.log('Debug panel elements found. Initializing debug panel functionality.');

        // Toggle Visibility
        debugToggleVisibilityBtn.addEventListener('click', function() {
            debugPanel.classList.toggle('hidden');
            console.log('Debug panel visibility toggled. Hidden status:', debugPanel.classList.contains('hidden'));
        });

        // Clear Log
        debugClearLogBtn.addEventListener('click', function() {
            debugLogOutput.innerHTML = '[INFO] Log cleared by user.\n';
            console.log('Debug log cleared.');
        });

        // Drag Functionality (Basic)
        let isDragging = false;
        let offsetX, offsetY;

        const debugHeader = debugPanel.querySelector('.debug-header');
        if (debugHeader) {
            debugHeader.addEventListener('mousedown', (e) => {
                isDragging = true;
                offsetX = e.clientX - debugPanel.getBoundingClientRect().left;
                offsetY = e.clientY - debugPanel.getBoundingClientRect().top;
                debugPanel.style.cursor = 'grabbing';
                console.log('Debug panel dragging started.');
            });

            document.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                debugPanel.style.left = (e.clientX - offsetX) + 'px';
                debugPanel.style.top = (e.clientY - offsetY) + 'px';
                // Prevent panel from going off-screen (basic boundary check)
                const panelRect = debugPanel.getBoundingClientRect();
                if (panelRect.left < 0) debugPanel.style.left = '0px';
                if (panelRect.top < 0) debugPanel.style.top = '0px';
                if (panelRect.right > window.innerWidth) debugPanel.style.left = (window.innerWidth - panelRect.width) + 'px';
                if (panelRect.bottom > window.innerHeight) debugPanel.style.top = (window.innerHeight - panelRect.height) + 'px';
            });

            document.addEventListener('mouseup', () => {
                if (isDragging) {
                    isDragging = false;
                    debugPanel.style.cursor = 'grab';
                    console.log('Debug panel dragging ended.');
                }
            });
        } else {
            console.warn('Debug panel header not found. Drag functionality skipped.');
        }

        // Scroll debug log to bottom on load
        debugLogOutput.scrollTop = debugLogOutput.scrollHeight;
        console.log('Debug log scrolled to bottom on load.');

    } else {
        console.warn('Debug panel elements not fully found. Debug panel functionality skipped.');
    }

    console.log('JavaScript initialization complete.');
});