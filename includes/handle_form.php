<?php
if (isset($_POST['remove_widget'])) {
    $index = $_POST['remove_widget'];
    $widgets = $_SESSION['active_widgets'];
    if (isset($widgets[$index])) {
        array_splice($widgets, $index, 1);
        $_SESSION['active_widgets'] = array_values($widgets);
    }
}

if (isset($_POST['toggle_expand'])) {
    $index = $_POST['widget_index'];
    if (isset($_SESSION['active_widgets'][$index])) {
        $_SESSION['active_widgets'][$index]['expanded'] = 
            !$_SESSION['active_widgets'][$index]['expanded'];
    }
}

// Other form handling logic...
?>