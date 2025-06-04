<?php
$modules = require __DIR__ . '/../../config/modules.php';
echo '<nav class="sidebar"><ul>';
foreach ($modules as $m) {
    if (user_has_permission($m['key'])) {
        $active = ($_GET['module'] ?? 'Dashboard') === $m['key'] ? 'class="active"' : '';
        echo "<li $active><a href="index.php?module={$m['key']}">";
        echo "<i class="fas {$m['icon-class']}"></i> {$m['label']}";
        echo "</a></li>";
    }
}
echo '</ul></nav>';
?>