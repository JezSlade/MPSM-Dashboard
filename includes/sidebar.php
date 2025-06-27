<aside class="sidebar">
    <div class="sidebar-section">
        <div class="section-title">Navigation</div>
        <div class="nav-item active">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </div>
        <!-- Other nav items... -->
    </div>
    
    <div class="sidebar-section">
        <div class="section-title">Widget Library</div>
        <div class="widget-list">
            <?php foreach (['stats', 'tasks', 'calendar', 'notes', 'activity'] as $widgetId): ?>
            <div class="widget-item" draggable="true" data-widget-id="<?= $widgetId ?>">
                <?php
                $icon = 'history'; // default
                switch ($widgetId) {
                    case 'stats': $icon = 'chart-bar'; break;
                    case 'tasks': $icon = 'tasks'; break;
                    case 'calendar': $icon = 'calendar'; break;
                    case 'notes': $icon = 'sticky-note'; break;
                }
                ?>
                <i class="fas fa-<?= $icon ?>"></i>
                <div class="widget-name"><?= ucfirst($widgetId) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</aside>