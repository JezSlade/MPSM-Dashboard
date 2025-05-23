<?php
/**
 * Event Bus for MPSM Dashboard
 * Enables communication between widgets
 */
class EventBus {
    private static $instance = null;
    private $subscribers = [];
    
    /**
     * @reusable
     */
    private function __construct() {
        // Private constructor to enforce singleton pattern
    }
    
    /**
     * Get singleton instance
     * @return EventBus
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new EventBus();
        }
        return self::$instance;
    }
    
    /**
     * Subscribe to an event
     * @param string $event_name
     * @param callable $callback
     */
    public function subscribe($event_name, $callback) {
        if (!isset($this->subscribers[$event_name])) {
            $this->subscribers[$event_name] = [];
        }
        
        $this->subscribers[$event_name][] = $callback;
    }
    
    /**
     * Unsubscribe from an event
     * @param string $event_name
     * @param callable $callback
     */
    public function unsubscribe($event_name, $callback) {
        if (!isset($this->subscribers[$event_name])) {
            return;
        }
        
        $key = array_search($callback, $this->subscribers[$event_name], true);
        
        if ($key !== false) {
            unset($this->subscribers[$event_name][$key]);
        }
    }
    
    /**
     * Publish an event
     * @param string $event_name
     * @param mixed $data
     */
    public function publish($event_name, $data) {
        // Direct subscribers
        if (isset($this->subscribers[$event_name])) {
            foreach ($this->subscribers[$event_name] as $callback) {
                call_user_func($callback, $event_name, $data);
            }
        }
        
        // Wildcard subscribers
        foreach ($this->subscribers as $pattern => $callbacks) {
            if ($pattern === $event_name) {
                continue; // Already processed
            }
            
            if ($this->match_pattern($pattern, $event_name)) {
                foreach ($callbacks as $callback) {
                    call_user_func($callback, $event_name, $data);
                }
            }
        }
    }
    
    /**
     * Match event name against pattern with wildcards
     * @param string $pattern
     * @param string $event_name
     * @return bool
     */
    private function match_pattern($pattern, $event_name) {
        // Convert pattern to regex
        $regex = str_replace('.', '\.', $pattern);
        $regex = str_replace('*', '[^.]+', $regex);
        $regex = '/^' . $regex . '$/';
        
        return preg_match($regex, $event_name) === 1;
    }
}
