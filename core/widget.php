<?php
/**
 * Base Widget class for MPSM Dashboard
 */
require_once 'core/event_bus.php';

abstract class Widget {
    protected $id;
    protected $config;
    protected $data = [];
    protected $event_bus;
    
    public function __construct($config = []) {
        $this->config = $config;
        $this->id = $config['widget_id'] ?? uniqid('widget_');
        $this->event_bus = EventBus::getInstance();
        
        // Subscribe to events if specified in config
        if (isset($config['subscribe_to']) && is_array($config['subscribe_to'])) {
            foreach ($config['subscribe_to'] as $event) {
                $this->event_bus->subscribe($event, [$this, 'handle_event']);
            }
        }
    }
    
    /**
     * Get widget ID
     * @return string
     */
    public function get_id() {
        return $this->id;
    }
    
    /**
     * Set widget data
     * @param string $key
     * @param mixed $value
     */
    public function set_data($key, $value) {
        $this->data[$key] = $value;
        
        // Publish event when data is set
        $this->event_bus->publish("widget.data.{$this->id}.$key", $value);
    }
    
    /**
     * Get widget data
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get_data($key, $default = null) {
        return $this->data[$key] ?? $default;
    }
    
    /**
     * Handle events from the event bus
     * @param string $event_name
     * @param mixed $data
     */
    public function handle_event($event_name, $data) {
        // Default implementation does nothing
        // Override in child classes to handle events
    }
    
    /**
     * Fetch data for the widget
     * @return mixed
     */
    abstract public function fetch_data();
    
    /**
     * Render the widget
     * @return string HTML
     */
    abstract public function render();
    
    /**
     * Get settings form for the widget
     * @return string HTML
     */
    public function get_settings_form() {
        return '';
    }
    
    /**
     * Save settings for the widget
     * @param array $settings
     * @return bool
     */
    public function save_settings($settings) {
        global $db;
        
        // Update widget config in database
        $stmt = $db->prepare("
            UPDATE widgets 
            SET config = ? 
            WHERE widget_id = ?
        ");
        
        $this->config = array_merge($this->config, $settings);
        return $stmt->execute([json_encode($this->config), $this->id]);
    }
}
