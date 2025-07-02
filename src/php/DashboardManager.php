<?php
// src/php/DashboardManager.php

class DashboardManager {
    private $dashboardSettingsFile;
    private $availableWidgets; // This now comes from dynamic discovery
    private $defaultDashboardState;

    public function __construct($dashboardSettingsFile, $availableWidgets) {
        $this->dashboardSettingsFile = $dashboardSettingsFile;
        $this->availableWidgets = $availableWidgets; // Dynamically discovered widgets

        // Define default dashboard state
        $this->defaultDashboardState = [
            'title' => 'MPS Monitor Dashboard',
            'site_icon' => 'fas fa-gem', // Default site icon now includes 'fas'
            'accent_color' => '#6366f1',
            'glass_intensity' => 0.6,
            'blur_amount' => '10px',
            'enable_animations' => true,
            'show_all_available_widgets' => false,
            'widgets_state' => [] // This will now hold the state of ALL available widgets (active/deactivated)
        ];
    }

    /**
     * Loads dashboard settings and active/deactivated widget states from the JSON file.
     * It also synchronizes with newly discovered widgets.
     *
     * @return array Loaded settings including widgets_state or default state.
     */
    public function loadDashboardState(): array {
        $loaded_state = [];
        if (file_exists($this->dashboardSettingsFile)) {
            $json_data = file_get_contents($this->dashboardSettingsFile);
            $decoded_state = json_decode($json_data, true);
            if (is_array($decoded_state)) {
                $loaded_state = $decoded_state;
            }
        }

        // Merge loaded state with defaults to ensure all keys are present
        $final_state = array_replace_recursive($this->defaultDashboardState, $loaded_state);

        // --- Synchronize widgets_state with currently discovered widgets ---
        $synced_widgets_state = [];
        $current_position = 0; // To maintain consistent ordering for active widgets

        foreach ($this->availableWidgets as $widget_id => $metadata) {
            $existing_state = $final_state['widgets_state'][$widget_id] ?? null;

            $widget_entry = [
                'id' => $widget_id,
                'name' => $metadata['name'],
                'icon' => $metadata['icon'],
                // Use existing state if available, otherwise default from metadata
                'width' => max(0.5, min(3.0, (float)($existing_state['width'] ?? $metadata['width']))),
                'height' => max(0.5, min(4.0, (float)($existing_state['height'] ?? $metadata['height']))),
                'is_active' => (bool)($existing_state['is_active'] ?? true), // Default to active if new
                'position' => (int)($existing_state['position'] ?? $current_position) // Keep existing position or assign new
            ];
            $synced_widgets_state[$widget_id] = $widget_entry;
            $current_position++;
        }

        // Remove any widgets from loaded state that are no longer discovered
        $final_state['widgets_state'] = array_intersect_key($synced_widgets_state, $this->availableWidgets);

        // Re-sort the widgets_state by position for consistent rendering order
        uasort($final_state['widgets_state'], function($a, $b) {
            return $a['position'] <=> $b['position'];
        });

        return $final_state;
    }

    /**
     * Saves the entire dashboard state (settings + widget states) to the JSON file.
     *
     * @param array $state The complete dashboard state array to save.
     * @return bool True on success, false on failure.
     */
    public function saveDashboardState(array $state): bool {
        $json_data = json_encode($state, JSON_PRETTY_PRINT);
        if ($json_data === false) {
            error_log("ERROR: saveDashboardState - Failed to encode dashboard state to JSON: " . json_last_error_msg());
            return false;
        }
        $result = file_put_contents($this->dashboardSettingsFile, $json_data);
        if ($result === false) {
            $error_message = "ERROR: saveDashboardState - Failed to write dashboard state to file: " . $this->dashboardSettingsFile;
            if (!is_writable(dirname($this->dashboardSettingsFile))) {
                 $error_message .= " - Directory not writable: " . dirname($this->dashboardSettingsFile);
            } else if (file_exists($this->dashboardSettingsFile) && !is_writable($this->dashboardSettingsFile)) {
                 $error_message .= " - File exists but is not writable: " . $this->dashboardSettingsFile;
            } else {
                $error_message .= " - Unknown write error.";
            }
            error_log($error_message);
        }
        return $result !== false;
    }

    /**
     * Updates the 'is_active' status of a widget.
     *
     * @param string $widget_id The ID of the widget to update.
     * @param bool $is_active The new active status.
     * @param array $current_widgets_state The current array of all widget states.
     * @return array The updated array of widget states.
     */
    public function updateWidgetActiveStatus(string $widget_id, bool $is_active, array $current_widgets_state): array {
        if (isset($current_widgets_state[$widget_id])) {
            $current_widgets_state[$widget_id]['is_active'] = $is_active;
        }
        return $current_widgets_state;
    }

    /**
     * Updates the dimensions of a single widget.
     * @param string $widget_id The ID of the widget to update.
     * @param float $new_width The new width.
     * @param float $new_height The new height.
     * @param array $current_widgets_state The current array of all widget states.
     * @return array The updated array of widget states.
     */
    public function updateWidgetDimensions(string $widget_id, float $new_width, float $new_height, array $current_widgets_state): array {
        // Clamp values between 0.5 and 3.0 for width, 0.5 and 4.0 for height
        $new_width = max(0.5, min(3.0, $new_width));
        $new_height = max(0.5, min(4.0, $new_height));

        if (isset($current_widgets_state[$widget_id])) {
            $current_widgets_state[$widget_id]['width'] = $new_width;
            $current_widgets_state[$widget_id]['height'] = $new_height;
        }
        return $current_widgets_state;
    }

    /**
     * Updates the order of active widgets.
     * This function now re-assigns 'position' for all widgets, active or not.
     *
     * @param array $new_order_ids An array of widget IDs in the new desired order (only active ones are typically passed).
     * @param array $current_widgets_state The current array of all widget states.
     * @return array The updated array of widget states.
     */
    public function updateWidgetOrder(array $new_order_ids, array $current_widgets_state): array {
        $ordered_active_widgets = [];
        $deactivated_widgets = [];

        // Separate active widgets based on new order, and collect deactivated ones
        foreach ($new_order_ids as $ordered_id) {
            if (isset($current_widgets_state[$ordered_id]) && $current_widgets_state[$ordered_id]['is_active']) {
                $ordered_active_widgets[$ordered_id] = $current_widgets_state[$ordered_id];
            }
        }
        foreach ($current_widgets_state as $widget_id => $widget_data) {
            if (!$widget_data['is_active']) {
                $deactivated_widgets[$widget_id] = $widget_data;
            }
        }

        $final_widgets_state = [];
        $position = 0;

        // Assign positions to ordered active widgets
        foreach ($ordered_active_widgets as $widget_id => $widget_data) {
            $widget_data['position'] = $position++;
            $final_widgets_state[$widget_id] = $widget_data;
        }

        // Assign positions to deactivated widgets (append them, maintaining their relative order if possible)
        // Or simply append them at the end if order isn't critical for deactivated ones
        foreach ($deactivated_widgets as $widget_id => $widget_data) {
            $widget_data['position'] = $position++;
            $final_widgets_state[$widget_id] = $widget_data;
        }
        
        // Re-sort the final state by position to ensure consistency
        uasort($final_widgets_state, function($a, $b) {
            return $a['position'] <=> $b['position'];
        });

        return $final_widgets_state;
    }

    /**
     * Adds a new widget template file and updates the dynamic_widgets.json (no longer used for discovery).
     * This method is now primarily for creating the PHP file. The discovery will pick it up automatically.
     *
     * @param string $widget_id The ID of the widget.
     * @param string $widget_name The display name.
     * @param string $widget_icon The Font Awesome icon.
     * @param float $widget_width Default width.
     * @param float $widget_height Default height.
     * @return bool True on success, false on failure.
     */
    public function createWidgetTemplateFile(string $widget_id, string $widget_name, string $widget_icon, float $widget_width, float $widget_height): bool {
        $file_path = APP_ROOT . '/widgets/' . $widget_id . '.php';

        if (file_exists($file_path)) {
            error_log("ERROR: Widget template creation failed - File already exists: " . $file_path);
            return false;
        }

        // MODIFIED: Template content now includes the full Font Awesome class
        // and uses the new standardized compact-content and expanded-content structure
        $template_content = <<<PHP
<?php
// widgets/{$widget_id}.php

// Widget Name: {$widget_name}
// Widget Icon: {$widget_icon}
// Widget Width: {$widget_width}
// Widget Height: {$widget_height}
?>
<div class="compact-content">
    <div class="neomorphic-card p-4 text-center">
        <h3 class="text-xl font-bold text-[var(--accent)] mb-2">{$widget_name}</h3>
        <p class="text-sm text-[var(--text-secondary)]">This is the compact view of your new widget. Expand for more details.</p>
        <p class="text-xs text-[var(--text-secondary)] mt-2">Current time: <?= date('H:i:s') ?></p>
    </div>
</div>
<div class="expanded-content">
    <div class="neomorphic-card p-4 h-full flex flex-col">
        <h3 class="text-xl font-bold text-[var(--accent)] mb-2">{$widget_name} (Expanded View)</h3>
        <p class="text-sm text-[var(--text-secondary)] mb-4">This is the expanded view of your new widget. You can add more detailed content here.</p>
        <p class="text-xs text-[var(--text-secondary)] mb-4">This widget is located at: <code>widgets/{$widget_id}.php</code></p>
        <p class="text-sm text-[var(--text-secondary)] flex-grow">Feel free to add dynamic data, charts, or any other PHP/HTML content.</p>
        <p class="text-xs text-[var(--text-secondary)] mt-auto">Current time: <?= date('Y-m-d H:i:s') ?></p>
    </div>
</div>
PHP;

        $result = file_put_contents($file_path, $template_content);
        if ($result === false) {
            error_log("ERROR: Failed to write new widget template file: " . $file_path);
            return false;
        }
        return true;
    }
}
