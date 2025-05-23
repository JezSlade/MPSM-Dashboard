<?php
/**
 * Static Widget Type for MPSM Dashboard
 * Displays static or simple dynamic content
 */
require_once 'core/widget.php';

/**
 * @reusable
 */
class StaticWidget extends Widget {
    protected $content;
    
    /**
     * @reusable
     */
    public function __construct($config = []) {
        parent::__construct($config);
        $this->content = $config['content'] ?? '';
    }
    
    /**
     * Fetch data for the widget
     * @return bool
     */
    public function fetch_data() {
        // Static widgets don't fetch external data
        return true;
    }
    
    /**
     * Render the widget
     * @return string HTML
     */
    public function render() {
        return '
        <div class="widget static-widget" id="widget-' . $this->id . '">
            <div class="widget-header">
                <h3>' . htmlspecialchars($this->config['title'] ?? 'Static Widget') . '</h3>
            </div>
            <div class="widget-content">
                ' . $this->content . '
            </div>
        </div>';
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
                <input type="text" id="title" name="title" value="' . htmlspecialchars($this->config['title'] ?? '') . '">
            </div>
            <div class="form-group">
                <label for="content">Content:</label>
                <textarea id="content" name="content" rows="5">' . htmlspecialchars($this->content) . '</textarea>
                <small>HTML is allowed</small>
            </div>
        </form>';
    }
    
    /**
     * Save settings for the widget
     * @param array $settings
     * @return bool
     */
    public function save_settings($settings) {
        if (isset($settings['content'])) {
            $this->content = $settings['content'];
        }
        
        return parent::save_settings($settings);
    }
}
