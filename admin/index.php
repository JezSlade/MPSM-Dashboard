<?php
$page = explode('/', trim($_GET['path'],'/'))[1] ?? 'dashboard';
switch ($page) {
  case 'modules':      (new ModuleController())->list();    break;
  case 'theme':        (new ThemeController())->show();     break;
  case 'save-theme':   (new ThemeController())->save();     break;
  case 'content':      (new ContentController())->list();   break;
  case 'edit-content': (new ContentController())->edit();   break;
  case 'save-content': (new ContentController())->save();   break;
  default:
    echo "<h2>Admin Dashboard</h2>
      <ul>
        <li><a href='" . APP_BASE . "/?path=admin/modules'>Modules</a>
        <li><a href='" . APP_BASE . "/?path=admin/theme'>Theme</a>
        <li><a href='" . APP_BASE . "/?path=admin/content'>Content</a>
      </ul>";
}
