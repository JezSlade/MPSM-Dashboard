<div class="floating-module p-6">
    <h2 class="text-2xl text-cyan-neon mb-6">Developer Tools</h2>

    <div class="devtools-control-group mb-6">
        <h3 class="text-xl text-yellow-neon mb-4">Role Switcher</h3>
        <form method="POST" action="" class="flex items-center space-x-4">
            <label for="role-switcher" class="text-default">Current Role:</label>
            <select id="role-switcher" name="role" onchange="this.form.submit()" class="flex-1 max-w-xs">
                <?php
                // This $role variable comes from index.php where it's already defined
                $current_role = $_POST['role'] ?? $_COOKIE['user_role'] ?? 'Guest';
                foreach (['Developer', 'Admin', 'Service', 'Sales', 'Guest'] as $r):
                ?>
                    <option value="<?php echo $r; ?>" <?php echo $current_role === $r ? 'selected' : ''; ?>>
                        <?php echo $r; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <p class="text-sm text-default mt-2">Changing your role will refresh the page and update accessible modules.</p>
    </div>

    <div class="devtools-control-group mb-6">
        <h3 class="text-xl text-yellow-neon mb-4">Theme Customization</h3>
        <div id="theme-controls">
            <div class="devtools-row">
                <label for="bg-primary">Primary Background:</label>
                <input type="color" id="bg-primary-color" data-css-var="--bg-primary">
                <input type="text" id="bg-primary-hex" value="" class="devtools-value">
            </div>
            <div class="devtools-row">
                <label for="text-default">Default Text Color:</label>
                <input type="color" id="text-default-color" data-css-var="--text-default">
                <input type="text" id="text-default-hex" value="" class="devtools-value">
            </div>
            <div class="devtools-row">
                <label for="bg-glass">Glass Background:</label>
                <input type="color" id="bg-glass-color" data-css-var="--bg-glass">
                <input type="text" id="bg-glass-hex" value="" class="devtools-value">
            </div>
            <div class="devtools-row">
                <label for="neon-cyan">Neon Cyan:</label>
                <input type="color" id="neon-cyan-color" data-css-var="--neon-cyan">
                <input type="text" id="neon-cyan-hex" value="" class="devtools-value">
            </div>
            <div class="devtools-row">
                <label for="neon-magenta">Neon Magenta:</label>
                <input type="color" id="neon-magenta-color" data-css-var="--neon-magenta">
                <input type="text" id="neon-magenta-hex" value="" class="devtools-value">
            </div>
            <div class="devtools-row">
                <label for="neon-yellow">Neon Yellow:</label>
                <input type="color" id="neon-yellow-color" data-css-var="--neon-yellow">
                <input type="text" id="neon-yellow-hex" value="" class="devtools-value">
            </div>
        </div>
        <div class="devtools-actions">
            <button id="save-theme-settings" class="devtools-button">Save Theme</button>
            <button id="reset-theme-settings" class="devtools-button reset">Reset Theme</button>
        </div>
    </div>

    <div class="devtools-control-group">
        <h3 class="text-xl text-magenta-neon mb-4">Application Reset</h3>
        <p class="text-default mb-4">Use this to clear all user-specific settings, including role and custom themes. This action cannot be undone.</p>
        <form method="GET" action="" onsubmit="return confirm('Are you sure you want to reset all settings? This will log you out.');" class="flex justify-end">
            <button type="submit" name="reset" class="devtools-button reset">Reset All Settings</button>
        </form>
    </div>

    <script>
        const root = document.documentElement;
        const themeControls = document.getElementById('theme-controls');
        const saveThemeButton = document.getElementById('save-theme-settings');
        const resetThemeButton = document.getElementById('reset-theme-settings');

        // Function to convert RGB(A) to Hex (for display in text inputs)
        function rgbToHex(rgb) {
            if (!rgb) return ''; // Handle cases where rgb might be null/empty
            const parts = rgb.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d*\.?\d+))?\)$/);
            if (!parts) return rgb; // Return original if not a recognized RGB format (e.g., already hex)
            const r = parseInt(parts[1]);
            const g = parseInt(parts[2]);
            const b = parseInt(parts[3]);
            return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase();
        }

        // Function to update CSS variable and corresponding text input
        function updateCssVarAndInput(inputElement) {
            const cssVar = inputElement.dataset.cssVar;
            const hexValue = inputElement.value;
            root.style.setProperty(cssVar, hexValue);

            const hexInput = inputElement.nextElementSibling; // Get the text input next to color picker
            if (hexInput) {
                hexInput.value = hexValue.toUpperCase();
            }
        }

        // Initialize color pickers and text inputs with current CSS variable values
        function initializeColorPickers() {
            document.querySelectorAll('#theme-controls input[type="color"]').forEach(input => {
                const cssVar = input.dataset.cssVar;
                // Get computed style for the current theme (light/dark)
                const computedColor = getComputedStyle(root).getPropertyValue(cssVar).trim();
                input.value = rgbToHex(computedColor); // Set color picker value

                const hexInput = input.nextElementSibling;
                if (hexInput) {
                    hexInput.value = rgbToHex(computedColor).toUpperCase();
                }
            });
        }

        // Event listener for color picker changes
        themeControls.addEventListener('input', (event) => {
            if (event.target.type === 'color') {
                updateCssVarAndInput(event.target);
            }
        });

        // Event listener for text input (hex) changes
        themeControls.addEventListener('change', (event) => {
            if (event.target.type === 'text' && event.target.classList.contains('devtools-value')) {
                const colorInput = event.target.previousElementSibling; // Get the color input
                if (colorInput && colorInput.type === 'color' && colorInput.dataset.cssVar) {
                    let hex = event.target.value;
                    if (!hex.startsWith('#')) {
                        hex = '#' + hex; // Add hash if missing
                    }
                    if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(hex)) { // Validate hex format
                        colorInput.value = hex; // Update color picker
                        updateCssVarAndInput(colorInput); // Apply to CSS and ensure consistent hex format
                    } else {
                        // Revert to current value or show error
                        event.target.value = rgbToHex(getComputedStyle(root).getPropertyValue(colorInput.dataset.css-var).trim()).toUpperCase();
                        alert('Invalid hex color format. Please use #RRGGBB or #RGB.');
                    }
                }
            }
        });


        // Save custom theme settings to localStorage
        saveThemeButton.addEventListener('click', () => {
            const customSettings = {};
            document.querySelectorAll('#theme-controls input[type="color"]').forEach(input => {
                customSettings[input.dataset.cssVar] = input.value;
            });
            localStorage.setItem('customThemeSettings', JSON.stringify(customSettings));
            alert('Custom theme settings saved!');
        });

        // Reset theme settings to default and clear from localStorage
        resetThemeButton.addEventListener('click', () => {
            localStorage.removeItem('customThemeSettings');
            // Remove custom styles applied directly to root, so original CSS takes over
            document.querySelectorAll('#theme-controls input[type="color"]').forEach(input => {
                root.style.removeProperty(input.dataset.cssVar);
            });
            // Re-initialize color pickers to reflect the default computed styles
            initializeColorPickers();
            alert('Theme settings reset to default!');
            // Re-apply theme to ensure default light/dark mode CSS variables are re-evaluated
            // This is important because removeProperty won't trigger re-evaluation of computed styles if they are based on :root / .dark
            // A quick refresh or re-setting the theme might be more robust
            location.reload(); // Simplest way to ensure full re-evaluation of CSS variables
        });

        // Initialize when module is loaded
        initializeColorPickers();
    </script>
</div>