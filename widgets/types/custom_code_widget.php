<?php
/**
 * Custom Code Widget Type for MPSM Dashboard
 * Allows custom PHP, HTML, JavaScript, and CSS code
 */
require_once 'core/widget.php';

class CustomCodeWidget extends Widget {
    protected $php_code;
    protected $html_template;
    protected $js_code;
    protected $css_code;
    
    public function __construct($config = []) {
        parent::__construct($config);
        $this->php_code = $config['php_code'] ?? '';
        $this->html_template = $config['html_template'] ?? '';
        $this->js_code = $config['js_code'] ?? '';
        $this->css_code = $config['css_code'] ?? '';
    }
    
    /**
     * Fetch data for the widget
     * @return mixed
     */
    public function fetch_data() {
        // Execute PHP code to get data
        if (empty($this->php_code)) {
            return [];
        }
        
        try {
            // Create a function from the PHP code
            $function = create_function('', $this->php_code);
            return $function();
        } catch (Exception $e) {
            Logger::error("Error executing custom widget PHP code: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Render the widget
     * @return string HTML
     */
    public function render() {
        $data = $this->fetch_data();
        
        // Replace variables in HTML template
        $html = $this->html_template;
        
        // Replace {{variable}} with actual data
        preg_match_all('/\{\{([^}]+)\}\}/', $html, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $key) {
                $value = $data[$key] ?? '';
                $html = str_replace("{{$key}}", $value, $html);
            }
        }
        
        // Add CSS and JavaScript
        $css = !empty($this->css_code) ? '<style>' . $this->css_code . '</style>' : '';
        $js = !empty($this->js_code) ? '<script>' . $this->js_code . '</script>' : '';
        
        return '
        <div class="widget custom-widget" id="widget-' . $this->id . '">
            <div class="widget-header">
                <h3>' . htmlspecialchars($this->config['title'] ?? 'Custom Widget') . '</h3>
            </div>
            <div class="widget-content">
                ' . $html . '
            </div>
            ' . $css . $js . '
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
                <label for="php_code">PHP Code:</label>
                <textarea id="php_code" name="php_code" rows="5" class="code-editor">' . htmlspecialchars($this->php_code) . '</textarea>
                <small>PHP code to process data for the widget</small>
            </div>
            <div class="form-group">
                <label for="html_template">HTML Template:</label>
                <textarea id="html_template" name="html_template" rows="5" class="code-editor">' . htmlspecialchars($this->html_template) . '</textarea>
                <small>HTML template for the widget (use {{variable}} for dynamic data)</small>
            </div>
            <div class="form-group">
                <label for="js_code">JavaScript Code:</label>
                <textarea id="js_code" name="js_code" rows="5" class="code-editor">' . htmlspecialchars($this->js_code) . '</textarea>
                <small>JavaScript code for the widget</small>
            </div>
            <div class="form-group">
                <label for="css_code">CSS Code:</label>
                <textarea id="css_code" name="css_code" rows="5" class="code-editor">' . htmlspecialchars($this->css_code) . '</textarea>
                <small>CSS styles for the widget</small>
            </div>
        </form>';
    }
    
    /**
     * Save settings for the widget
     * @param array $settings
     * @return bool
     */
    public function save_settings($settings) {
        if (isset($settings['php_code'])) {
            $this->php_code = $settings['php_code'];
        }
        
        if (isset($settings['html_template'])) {
            $this->html_template = $settings['html_template'];
        }
        
        if (isset($settings['js_code'])) {
            $this->js_code = $settings['js_code'];
        }
        
        if (isset($settings['css_code'])) {
            $this->css_code = $settings['css_code'];
        }
        
        return parent::save_settings($settings);
    }
}
