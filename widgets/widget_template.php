<?php
// Define widget metadata
$_widget_config = [
    'name' => 'Widget Name',
    'icon' => 'font-awesome-icon-class', // e.g., 'chart-bar'
    'width' => 1, // Default grid width
    'height' => 1 // Default grid height
];

// --- Widget HTML Content Starts Below ---
?>
<div class="your-widget-specific-class">
    <!-- Your widget's HTML content goes here -->
    <p>This is the Widget Template</p>
    <?php
    // You can include PHP logic here, e.g.,
    // if (isset($data_from_parent)) { echo $data_from_parent; }
    ?>
</div>