<?php
$settings = getDashboardSettings();
?>
<div class="settings-panel" id="settings-panel">
    <div class="settings-header">
        <h2>Dashboard Settings</h2>
        <button class="btn" id="close-settings">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <form method="post" class="settings-form">
        <div class="settings-group">
            <h3 class="settings-title">General Settings</h3>
            
            <div class="form-group">
                <label for="dashboard_title">Dashboard Title</label>
                <input type="text" id="dashboard_title" name="dashboard_title" 
                    class="form-control" value="<?= htmlspecialchars($settings['title']) ?>">
            </div>
            
            <div class="form-group">
                <label for="accent_color">Accent Color</label>
                <input type="color" id="accent_color" name="accent_color" 
                    class="form-control" value="<?= $settings['accent_color'] ?>" style="height: 50px;">
            </div>
        </div>
        
        <button type="submit" name="update_settings" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Settings
        </button>
    </form>
</div>