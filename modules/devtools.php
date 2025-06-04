<?php
if (!has_permission('view_devtools')) {
    echo "<p class='error'>Access denied.</p>";
    exit;
}
?>
<h2>DevTools ⚙️</h2>
<p>Developer settings and tools:</p>
<ul>
    <li><a href="create_module.php">➕ Add Module</a> - Create a new module with JSON config.</li>
    <!-- Add more settings links as needed -->
</ul>