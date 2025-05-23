<?php
/**
 * Widget Registry for MPSM Dashboard
 * Manages widget registration and retrieval
 */
class WidgetRegistry {
    private static $instance = null;
    private $widgets = [];
    private $db;
    
    /**
     * @reusable
     */
    private function __construct() {
        global $db;
        $this->db = $db;
        $this->load_widgets();
    }
    
    /**
     * Get singleton instance
     * @return WidgetRegistry
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new WidgetRegistry();
        }
        return self::$instance;
    }
    
    /**
     * Load widgets from database
     */
    private function load_widgets() {
        $stmt = $this->db->query("SELECT * FROM widgets WHERE active = 1");
        $widgets = $stmt->fetchAll();
        
        foreach ($widgets as $widget) {
            $this->widgets[$widget['widget_id']] = [
                'widget_id' => $widget['widget_id'],
                'name' => $widget['name'],
                'description' => $widget['description'],
                'type' => $widget['type'],
                'class_name' => $widget['class_name'],
                'file_path' => $widget['file_path'],
                'config' => json_decode($widget['config'], true),
                'required_permissions' => $widget['required_permissions']
            ];
        }
    }
    
    /**
     * Register a new widget
     * @param string $widget_id
     * @param string $name
     * @param string $description
     * @param string $type
     * @param string $class_name
     * @param string $file_path
     * @param array $config
     * @param string $required_permissions
     * @return bool
     */
    public function register_widget($widget_id, $name, $description, $type, $class_name, $file_path, $config = [], $required_permissions = '') {
        // Check if widget already exists
        $stmt = $this->db->prepare("SELECT 1 FROM widgets WHERE widget_id = ?");
        $stmt->execute([$widget_id]);
        
        if ($stmt->fetchColumn()) {
            // Update existing widget
            $stmt = $this->db->prepare("
                UPDATE widgets 
                SET name = ?, description = ?, type = ?, class_name = ?, file_path = ?, config = ?, required_permissions = ? 
                WHERE widget_id = ?
            ");
            
            $result = $stmt->execute([
                $name, 
                $description, 
                $type, 
                $class_name, 
                $file_path, 
                json_encode($config), 
                $required_permissions, 
                $widget_id
            ]);
        } else {
            // Insert new widget
            $stmt = $this->db->prepare("
                INSERT INTO widgets (widget_id, name, description, type, class_name, file_path, config, required_permissions) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $widget_id, 
                $name, 
                $description, 
                $type, 
                $class_name, 
                $file_path, 
                json_encode($config), 
                $required_permissions
            ]);
        }
        
        if ($result) {
            // Update in-memory cache
            $this->widgets[$widget_id] = [
                'widget_id' => $widget_id,
                'name' => $name,
                'description' => $description,
                'type' => $type,
                'class_name' => $class_name,
                'file_path' => $file_path,
                'config' => $config,
                'required_permissions' => $required_permissions
            ];
            return true;
        }
        
        return false;
    }
    
    /**
     * Get a widget instance
     * @param string $widget_id
     * @return Widget|null
     */
    public function get_widget($widget_id) {
        if (!isset($this->widgets[$widget_id])) {
            return null;
        }
        
        $widget_info = $this->widgets[$widget_id];
        
        // Check if file exists
        if (!file_exists($widget_info['file_path'])) {
            Logger::error("Widget file not found: {$widget_info['file_path']}");
            return null;
        }
        
        // Include the file
        require_once $widget_info['file_path'];
        
        // Check if class exists
        if (!class_exists($widget_info['class_name'])) {
            Logger::error("Widget class not found: {$widget_info['class_name']}");
            return null;
        }
        
        // Create widget instance
        $config = array_merge(['widget_id' => $widget_id], $widget_info['config'] ?? []);
        return new $widget_info['class_name']($config);
    }
    
    /**
     * Get all registered widgets
     * @return array
     */
    public function get_all_widgets() {
        return $this->widgets;
    }
    
    /**
     * Get widgets available to a user
     * @param int $user_id
     * @return array
     */
    public function get_user_widgets($user_id) {
        $stmt = $this->db->prepare("
            SELECT w.*, uwp.can_edit 
            FROM widgets w
            JOIN user_widget_permissions uwp ON w.widget_id = uwp.widget_id
            WHERE uwp.user_id = ? AND uwp.can_view = 1 AND w.active = 1
        ");
        
        $stmt->execute([$user_id]);
        $user_widgets = [];
        
        while ($row = $stmt->fetch()) {
            $user_widgets[$row['widget_id']] = [
                'widget_id' => $row['widget_id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'type' => $row['type'],
                'class_name' => $row['class_name'],
                'file_path' => $row['file_path'],
                'config' => json_decode($row['config'], true),
                'required_permissions' => $row['required_permissions'],
                'can_edit' => (bool)$row['can_edit']
            ];
        }
        
        return $user_widgets;
    }
}
