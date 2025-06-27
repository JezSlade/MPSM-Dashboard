<?php
class Stats {
    public function render($index, $widget) {
        $expanded = $widget['expanded'] ? 'expanded' : '';
        $show_icons = $widget['settings']['show_icons'] ?? true;
        
        return '
        <div class="widget '.$expanded.'" style="--width: 2; --height: 1">
            <div class="widget-header">
                <div class="widget-title" data-index="'.$index.'">
                    <i class="fas fa-chart-bar"></i>
                    <span>'.$widget['title'].'</span>
                </div>
                '.$this->renderActions($index).'
            </div>
            <div class="widget-content">
                '.$this->renderContent($widget).'
            </div>
        </div>';
    }
    
    protected function renderContent($widget) {
        $show_icons = $widget['settings']['show_icons'] ?? true;
        
        return '
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">1,254</div>
                <div class="stat-label">Visitors</div>
                '.($show_icons ? '<i class="fas fa-users"></i>' : '').'
            </div>
            <!-- Other stat cards... -->
        </div>';
    }
    
    protected function renderActions($index) {
        return '
        <div class="widget-actions">
            <div class="widget-action settings-btn" data-index="'.$index.'">
                <i class="fas fa-cog"></i>
            </div>
            <div class="widget-action expand-btn" data-index="'.$index.'">
                <i class="fas fa-expand"></i>
            </div>
            <div class="widget-action remove-btn" data-index="'.$index.'">
                <i class="fas fa-times"></i>
            </div>
        </div>';
    }
}