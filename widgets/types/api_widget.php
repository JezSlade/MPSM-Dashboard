<?php
/**
 * API Widget Type for MPSM Dashboard
 * Fetches data from API endpoints
 */
require_once 'core/widget.php';

class ApiWidget extends Widget {
    protected $endpoint_id;
    protected $method;
    protected $params;
    protected $cache_duration = 300; // 5 minutes
    protected $last_fetch = 0;
    protected $cached_data = null;
    
    public function __construct($config = []) {
        parent::__construct($config);
        $this->endpoint_id = $config['endpoint_id'] ?? '';
        $this->method = $config['method'] ?? 'get';
        $this->params = $config['params'] ?? [];
        $this->cache_duration = $config['cache_duration'] ?? 300;
    }
    
    /**
     * Fetch data from API endpoint
     * @return mixed
     */
    public function fetch_data() {
        global $api_client;
        
        // Check cache
        if ($this->cached_data !== null && time() - $this->last_fetch < $this->cache_duration) {
            return $this->cached_data;
        }
        
        // Fetch from API
        $data = $api_client->call_api($this->endpoint_id, $this->method, $this->params);
        
        // Update cache
        $this->cached_data = $data;
        $this->last_fetch = time();
        
        return $data;
    }
    
    /**
     * Render the widget
     * @return string HTML
     */
    public function render() {
        $data = $this->fetch_data();
        
        if ($data === null) {
            return $this->render_error();
        }
        
        return $this->render_success($data);
    }
    
    /**
     * Render widget with data
     * @param mixed $data
     * @return string HTML
     */
    protected function render_success($data) {
        // Default implementation - override in child classes
        return '
        <div class="widget api-widget" id="widget-' . $this->id . '">
            <div class="widget-header">
                <h3>' . htmlspecialchars($this->config['title'] ?? 'API Widget') . '</h3>
            </div>
            <div class="widget-content">
                <pre>' . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . '</pre>
            </div>
        </div>';
    }
    
    /**
     * Render widget with error
     * @return string HTML
     */
    protected function render_error() {
        return '
        <div class="widget api-widget" id="widget-' . $this->id . '">
            <div class="widget-header">
                <h3>' . htmlspecialchars($this->config['title'] ?? 'API Widget') . '</h3>
            </div>
            <div class="widget-content">
                <p class="error-message">Failed to load data from API.</p>
            </div>
        </div>';
    }
    
    /**
     * Get settings form for the widget
     * @return string HTML
     */
    public function get_settings_form() {
        global $api_client;
        
        $endpoints = $api_client->get_all_endpoints();
        $endpoint_options = '';
        
        foreach ($endpoints as $endpoint => $methods) {
            $selected = ($endpoint === $this->endpoint_id) ? 'selected' : '';
            $endpoint_options .= '<option value="' . htmlspecialchars($endpoint) . '" ' . $selected . '>' . htmlspecialchars($endpoint) . '</option>';
        }
        
        return '
        <form class="widget-settings-form">
            <div class="form-group">
                <label for="title">Widget Title:</label>
                <input type="text" id="title" name="title" value="' . htmlspecialchars($this->config['title'] ?? '') . '">
            </div>
            <div class="form-group">
                <label for="endpoint_id">API Endpoint:</label>
                <select id="endpoint_id" name="endpoint_id">
                    ' . $endpoint_options . '
                </select>
            </div>
            <div class="form-group">
                <label for="method">HTTP Method:</label>
                <select id="method" name="method">
                    <option value="get" ' . (($this->method === 'get') ? 'selected' : '') . '>GET</option>
                    <option value="post" ' . (($this->method === 'post') ? 'selected' : '') . '>POST</option>
                    <option value="put" ' . (($this->method === 'put') ? 'selected' : '') . '>PUT</option>
                    <option value="delete" ' . (($this->method === 'delete') ? 'selected' : '') . '>DELETE</option>
                </select>
            </div>
            <div class="form-group">
                <label for="cache_duration">Cache Duration (seconds):</label>
                <input type="number" id="cache_duration" name="cache_duration" value="' . htmlspecialchars($this->cache_duration) . '">
            </div>
        </form>';
    }
    
    /**
     * Save settings for the widget
     * @param array $settings
     * @return bool
     */
    public function save_settings($settings) {
        if (isset($settings['endpoint_id'])) {
            $this->endpoint_id = $settings['endpoint_id'];
        }
        
        if (isset($settings['method'])) {
            $this->method = $settings['method'];
        }
        
        if (isset($settings['cache_duration'])) {
            $this->cache_duration = (int)$settings['cache_duration'];
        }
        
        return parent::save_settings($settings);
    }
}
