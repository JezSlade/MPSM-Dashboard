<?php
$_widget_config = [
    'name' => 'Task Manager',
    'icon' => 'tasks',
    'width' => 1,
    'height' => 2
];
?>
<div class="task-list">
    <div class="task-item completed">
        <input type="checkbox" checked>
        <label>Create dashboard layout</label>
    </div>
    <div class="task-item">
        <input type="checkbox">
        <label>Implement widget system</label>
    </div>
    <div class="task-item">
        <input type="checkbox">
        <label>Design settings panel</label>
    </div>
    <div class="task-item urgent">
        <input type="checkbox">
        <label>Fix responsive issues</label>
    </div>
    <div class="task-item">
        <input type="checkbox">
        <label>Add user documentation</label>
    </div>
</div>
<div class="task-input">
    <input type="text" placeholder="Add new task...">
    <button><i class="fas fa-plus"></i></button>
</div>