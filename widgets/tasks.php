<?php
// widgets/tasks.php

// Widget configuration
$_widget_config = [
    'name' => 'Task Manager',
    'icon' => 'tasks',
    'width' => 1,
    'height' => 2
];
?>
<div class="compact-content">
    <h4 style="color: var(--accent); margin-bottom: 10px;">My Tasks</h4>
    <ul style="list-style: none; padding: 0;">
        <li style="margin-bottom: 5px; color: var(--text-primary);"><i class="fas fa-check-circle" style="color: var(--success);"></i> Complete report</li>
        <li style="margin-bottom: 5px; color: var(--text-primary);"><i class="fas fa-hourglass-half" style="color: var(--warning);"></i> Review Q3 budget</li>
        <li style="margin-bottom: 5px; color: var(--text-primary);"><i class="fas fa-times-circle" style="color: var(--danger);"></i> Call John Doe</li>
    </ul>
    <button class="btn btn-sm" style="width: 100%; margin-top: 10px;">View All Tasks</button>
</div>
<div class="expanded-content">
    <h4 style="color: var(--accent); margin-bottom: 15px;">Full Task List</h4>
    <p>This expanded view would show a comprehensive list of tasks with filters, due dates, and assignments.</p>
    <ul style="list-style: none; padding: 0;">
        <li style="margin-bottom: 8px; color: var(--text-primary);">
            <strong>Complete Marketing Report</strong> <span style="float: right; color: var(--text-secondary);">Due: 2024-07-15</span><br>
            <small style="color: var(--text-secondary);">Assigned to: Jane Doe</small>
        </li>
        <li style="margin-bottom: 8px; color: var(--text-primary);">
            <strong>Review Q3 Budget Projections</strong> <span style="float: right; color: var(--text-secondary);">Due: 2024-07-20</span><br>
            <small style="color: var(--text-secondary);">Assigned to: Self</small>
        </li>
        <li style="margin-bottom: 8px; color: var(--text-primary);">
            <strong>Follow up with John Doe (Client)</strong> <span style="float: right; color: var(--text-secondary);">Due: 2024-07-10</span><br>
            <small style="color: var(--text-secondary);">Assigned to: Self</small>
        </li>
    </ul>
</div>
