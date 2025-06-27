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
                <i class="fas fa-<?= $widgetId === 'stats' ? 'chart-bar' : $widgetId === 'tasks' ? 'tasks' : $widgetId === 'calendar' ? 'calendar' : $widgetId === 'notes' ? 'sticky-note' : 'history' ?>"></i>
                <div class="widget-name"><?= ucfirst($widgetId) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</aside>