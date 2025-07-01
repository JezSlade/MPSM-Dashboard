<?php
// src/php/DashboardManager.php

class DashboardManager {
    private $dashboardSettingsFile;
    private $dynamicWidgetsFile;
    private $availableWidgets;
    private $defaultDashboardState;

    public function __construct($dashboardSettingsFile, $dynamicWidgetsFile, $availableWidgets) {
        $this->dashboardSettingsFile = $dashboardSettingsFile;
        $this->dynamicWidgetsFile = $dynamicWidgetsFile;
        $this->availableWidgets = $availableWidgets;

        // Define default dashboard state
        $this->defaultDashboardState = [
            'title' => 'Glass Dashboard',
            'accent_color' => '#6366f1',
            'glass_intensity' => 0.6,
            'blur_amount' => '10px',
            'enable_animations' => true,
            'show_all_available_widgets' => false,
            'active_widgets' => [
                // Default widgets with their initial default dimensions (from config.php)
                ['id' => 'stats', 'position' => 1, 'width' => 2.0, 'height' => 1.0],
                ['id' => 'tasks', 'position' => 2, 'width' => 1.0, 'height' => 2.0],
                ['id' => 'calendar', 'position' => 3, 'width' => 1.0, 'height' => 1.0],
                ['id' => 'notes', 'position' => 4, 'width' => 1.0, 'height' => 1.0],
                ['id' => 'activity', 'position' => 5, 'width' => 2.0, 'height' => 1.0],
                ['id' => 'debug_info', 'position' => 6, 'width' => 2.0, 'height' => 2.0],
                ['id' => 'ide', 'position' => 7, 'width' => 3.0, 'height' => 3.0]
            ]
        ];
    }

    /**
     * Loads dashboard settings and active widgets from the JSON file.
     *
     * @return array Loaded settings including active_widgets or default state.
     */
    public function loadDashboardState() {
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

        // Ensure active_widgets entries have width/height, falling back to config defaults
        if (isset($final_state['active_widgets']) && is_array($final_state['active_widgets'])) {
            foreach ($final_state['active_widgets'] as $key => $widget_entry) {
                $widget_id = $widget_entry['id'];
                $default_width = (float)($this->availableWidgets[$widget_id]['width'] ?? 1.0);
                $default_height = (float)($this->availableWidgets[$widget_id]['height'] ?? 1.0);

                $final_state['active_widgets'][$key]['width'] = max(0.5, min(3.0, (float)($widget_entry['width'] ?? $default_width)));
                $final_state['active_widgets'][$key]['height'] = max(0.5, min(4.0, (float)($widget_entry['height'] ?? $default_height)));
            }
        } else {
            // If active_widgets was missing or not an array, use default ones
            $final_state['active_widgets'] = $this->defaultDashboardState['active_widgets'];
        }

        return $final_state;
    }

    /**
     * Saves the entire dashboard state (settings + active widgets) to the JSON file.
     *
     * @param array $state The complete dashboard state array to save.
     * @return bool True on success, false on failure.
     */
    public function saveDashboardState(array $state) {
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
     * Loads dynamically created widget configurations from dynamic_widgets.json.
     * @return array An associative array of dynamic widget configurations.
     */
    public function loadDynamicWidgets() {
        if (file_exists($this->dynamicWidgetsFile)) {
            $json_data = file_get_contents($this->dynamicWidgetsFile);
            $widgets = json_decode($json_data, true);
            return is_array($widgets) ? $widgets : [];
        }
        return [];
    }

    /**
     * Saves dynamically created widget configurations to dynamic_widgets.json.
     * @param array $widgets An associative array of dynamic widget configurations.
     * @return bool True on success, false on failure.
     */
    public function saveDynamicWidgets(array $widgets) {
        $json_data = json_encode($widgets, JSON_PRETTY_PRINT);
        if ($json_data === false) {
            error_log("ERROR: saveDynamicWidgets - Failed to encode dynamic widgets to JSON: " . json_last_error_msg());
            return false;
        }
        $result = file_put_contents($this->dynamicWidgetsFile, $json_data);
        if ($result === false) {
            error_log("ERROR: saveDynamicWidgets - Failed to write dynamic widgets to file: " . $this->dynamicWidgetsFile);
        }
        return $result !== false;
    }

    /**
     * Adds a new widget to the active widgets list.
     * @param string $widget_id The ID of the widget to add.
     * @param array $current_active_widgets The current array of active widgets.
     * @return array The updated array of active widgets.
     */
    public function addWidget($widget_id, array $current_active_widgets) {
        $default_width = (float)($this->availableWidgets[$widget_id]['width'] ?? 1.0);
        $default_height = (float)($this->availableWidgets[$widget_id]['height'] ?? 1.0);

        $new_widget = [
            'id' => $widget_id,
            'position' => count($current_active_widgets) + 1,
            'width' => $default_width,
            'height' => $default_height
        ];
        $current_active_widgets[] = $new_widget;
        return $current_active_widgets;
    }

    /**
     * Removes a widget from the active widgets list by index.
     * @param int $widget_index The index of the widget to remove.
     * @param array $current_active_widgets The current array of active widgets.
     * @return array The updated array of active widgets.
     */
    public function removeWidgetByIndex($widget_index, array $current_active_widgets) {
        if (isset($current_active_widgets[$widget_index])) {
            unset($current_active_widgets[$widget_index]);
            return array_values($current_active_widgets); // Re-index array
        }
        return $current_active_widgets;
    }

    /**
     * Removes a widget from the active widgets list by ID.
     * @param string $widget_id The ID of the widget to remove.
     * @param array $current_active_widgets The current array of active widgets.
     * @return array The updated array of active widgets.
     */
    public function removeWidgetById($widget_id, array $current_active_widgets) {
        $updated_active_widgets = [];
        foreach ($current_active_widgets as $widget_entry) {
            if ($widget_entry['id'] !== $widget_id) {
                $updated_active_widgets[] = $widget_entry;
            }
        }
        return $updated_active_widgets;
    }

    /**
     * Updates the dimensions of a single widget.
     * @param int $widget_index The index of the widget to update.
     * @param float $new_width The new width.
     * @param float $new_height The new height.
     * @param array $current_active_widgets The current array of active widgets.
     * @return array The updated array of active widgets.
     */
    public function updateWidgetDimensions($widget_index, $new_width, $new_height, array $current_active_widgets) {
        // Clamp values between 0.5 and 3.0 for width, 0.5 and 4.0 for height
        $new_width = max(0.5, min(3.0, (float)$new_width));
        $new_height = max(0.5, min(4.0, (float)$new_height));

        if (isset($current_active_widgets[$widget_index])) {
            $current_active_widgets[$widget_index]['width'] = $new_width;
            $current_active_widgets[$widget_index]['height'] = $new_height;
        }
        return $current_active_widgets;
    }

    /**
     * Updates the order of active widgets.
     * @param array $new_order_ids An array of widget IDs in the new desired order.
     * @param array $current_active_widgets The current array of active widgets.
     * @return array The updated array of active widgets.
     */
    public function updateWidgetOrder(array $new_order_ids, array $current_active_widgets) {
        $new_active_widgets = [];
        foreach ($new_order_ids as $ordered_id) {
            foreach ($current_active_widgets as $old_widget) {
                if ($old_widget['id'] === $ordered_id) {
                    $new_active_widgets[] = $old_widget;
                    break;
                }
            }
        }
        return $new_active_widgets;
    }

    /**
     * Sets the active widgets to all available widgets, sorted alphabetically by ID.
     * @return array The new array of active widgets.
     */
    public function setAllAvailableWidgetsAsActive() {
        $new_active_widgets = [];
        foreach ($this->availableWidgets as $id => $def) {
            $new_active_widgets[] = [
                'id' => $id,
                'position' => count($new_active_widgets) + 1,
                'width' => (float)($def['width'] ?? 1.0),
                'height' => (float)($def['height'] ?? 1.0)
            ];
        }
        usort($new_active_widgets, function($a, $b) {
            return strcmp($a['id'], $b['id']);
        });
        return $new_active_widgets;
    }
}
