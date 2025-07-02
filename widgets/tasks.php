<?php
// widgets/tasks.php

// Widget Name: Task Manager
// Widget Icon: fas fa-tasks
// Widget Width: 1.0
// Widget Height: 2.0
?>
<div class="compact-content">
    <div class="neomorphic-card p-4 h-full flex flex-col">
        <h4 class="text-xl font-bold text-[var(--accent)] mb-4">My Tasks</h4>
        <ul class="list-none p-0 m-0 flex-grow">
            <li class="flex items-center mb-2 text-[var(--text-primary)]">
                <i class="fas fa-check-circle mr-2 text-[var(--success)]"></i> Complete report
            </li>
            <li class="flex items-center mb-2 text-[var(--text-primary)]">
                <i class="fas fa-hourglass-half mr-2 text-[var(--warning)]"></i> Review Q3 budget
            </li>
            <li class="flex items-center mb-2 text-[var(--text-primary)]">
                <i class="fas fa-times-circle mr-2 text-[var(--danger)]"></i> Call John Doe
            </li>
        </ul>
        <button class="btn btn-sm btn-secondary ripple-effect w-full mt-4">View All Tasks</button>
    </div>
</div>
<div class="expanded-content">
    <div class="neomorphic-card p-4 h-full flex flex-col">
        <h4 class="text-xl font-bold text-[var(--accent)] mb-4">Full Task List</h4>
        <p class="text-sm text-[var(--text-secondary)] mb-4">This expanded view would show a comprehensive list of tasks with filters, due dates, and assignments.</p>
        <ul class="list-none p-0 m-0 flex-grow">
            <li class="mb-3 text-[var(--text-primary)]">
                <strong>Complete Marketing Report</strong> <span class="float-right text-[var(--text-secondary)]">Due: 2024-07-15</span><br>
                <small class="text-[var(--text-secondary)]">Assigned to: Jane Doe</small>
            </li>
            <li class="mb-3 text-[var(--text-primary)]">
                <strong>Review Q3 Budget Projections</strong> <span class="float-right text-[var(--text-secondary)]">Due: 2024-07-20</span><br>
                <small class="text-[var(--text-secondary)]">Assigned to: Self</small>
            </li>
            <li class="mb-3 text-[var(--text-primary)]">
                <strong>Follow up with John Doe (Client)</strong> <span class="float-right text-[var(--text-secondary)]">Due: 2024-07-10</span><br>
                <small class="text-[var(--text-secondary)]">Assigned to: Self</small>
            </li>
        </ul>
    </div>
</div>
