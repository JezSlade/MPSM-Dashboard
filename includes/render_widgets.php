<?php
foreach ($_SESSION['dashboard']['widgets'] as $widget) {
    $file = __DIR__ . '/../widgets/' . $widget['id'] . '_widget.php';
    if (file_exists($file)) {
        include $file;
    }
}
?>
