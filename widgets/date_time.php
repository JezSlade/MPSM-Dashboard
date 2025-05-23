<?php
/**
 * Date & Time Widget for MPSM Dashboard
 * Displays the current date and time
 */
require_once 'widgets/types/static_widget.php';

/**
 * @reusable
 */
class DateTimeWidget extends StaticWidget {
    protected $format;
    
    /**
     * @reusable
     */
    public function __construct($config = []) {
        parent::__construct($config);
        $this->format = $config['format'] ?? 'F j, Y g:i A';
        $this->update_content();
    }
    
    /**
     * Update the widget content with current date/time
     */
    private function update_content() {
        $current_time = date($this->format);
        $timestamp = time();
        
        $this->content = '
        <div class="date-time-display">
            <div class="current-time">' . $current_time . '</div>
            <div class="date-time-refresh">
                <button class="refresh-time" onclick="refreshDateTime(\'' . $this->id . '\', \'' . $this->format . '\')">
                    <i class="refresh-icon">↻</i>
                </button>
            </div>
        </div>
        <script>
            /**
             * @reusable
             */
            function refreshDateTime(widgetId, format) {
                const now = new Date();
                fetch("ajax/widget_data.php?action=get_time&format=" + encodeURIComponent(format))
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelector("#widget-" + widgetId + " .current-time").textContent = data.time;
                        }
                    });
            }
            
            // Auto-refresh every minute
            setInterval(function() {
                refreshDateTime("' . $this->id . '", "' . $this->format . '");
            }, 60000);
        </script>';
    }
    
    /**
     * Fetch data for the widget
     * @return bool
     */
    public function fetch_data() {
        $this->update_content();
        return true;
    }
    
    /**
     * Get settings form for the widget
     * @return string HTML
     */
    public function get_settings_form() {
        return '
        <form class="widget-settings-form">
            <div class="form-group">
                <label for="title">Widget Title:</label>
                <input type="text" id="title" name="title" value="' . htmlspecialchars($this->config['title'] ?? 'Date & Time') . '">
            </div>
            <div class="form-group">
                <label for="format">Date/Time Format:</label>
                <input type="text" id="format" name="format" value="' . htmlspecialchars($this->format) . '">
                <small>PHP date format (e.g., F j, Y g:i A)</small>
            </div>
        </form>';
    }
    
    /**
     * Save settings for the widget
     * @param array $settings
     * @return bool
     */
    public function save_settings($settings) {
        if (isset($settings['format'])) {
            $this->format = $settings['format'];
            $this->update_content();
        }
        
        return parent::save_settings($settings);
    }
}
