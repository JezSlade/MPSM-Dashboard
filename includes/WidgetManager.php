<?php
class WidgetManager {
    private $widgets = [];
    
    public function __construct() {
        $this->loadWidgets();
    }
    
    private function loadWidgets() {
        $widgetFiles = glob(__DIR__.'/../widgets/*.php');
        foreach ($widgetFiles as $file) {
            require_once $file;
            $className = basename($file, '.php');
            $this->widgets[strtolower($className)] = new $className();
        }
    }
    
    public function getActiveWidgets() {
        if (!isset($_SESSION['active_widgets'])) {
            $_SESSION['active_widgets'] = $this->initializeDefaultWidgets();
        }
        return $_SESSION['active_widgets'];
    }
    
    private function initializeDefaultWidgets() {
        $config = require __DIR__.'/../config/config.php';
        $widgets = [];
        foreach ($config['default_widgets'] as $position => $widgetId) {
            $widgets[] = [
                'id' => $widgetId,
                'position' => $position + 1,
                'title' => ucfirst($widgetId),
                'expanded' => false,
                'settings' => []
            ];
        }
        return $widgets;
    }
    
    public function renderWidget($widget, $index) {
        if (!isset($this->widgets[$widget['id']])) {
            return '<div class="widget">Invalid widget: '.$widget['id'].'</div>';
        }
        return $this->widgets[$widget['id']]->render($index, $widget);
    }
}