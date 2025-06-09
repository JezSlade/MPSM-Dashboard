<?php
class ThemeController {
    public function show(): void {
        $current = Database::getSetting('theme');
        include ADMIN_PATH . '/views/theme.php';
    }
    public function save(): void {
        Database::setSetting('theme', $_POST['theme']);
        header('Location: ' . APP_BASE . '/?path=admin/theme');
        exit;
    }
}
