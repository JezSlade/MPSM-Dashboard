<?php
// helpers.php

// Ensure available_widgets is accessible
require_once 'config.php';

// Function to render widget content
function render_widget($widget_id) {
    // This would normally be in separate files for larger projects
    // For this demo, we'll include the content directly
    $widget_content = [
        'stats' => '
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">1,254</div>
                <div class="stat-label">Visitors</div>
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-card">
                <div class="stat-value">$7,842</div>
                <div class="stat-label">Revenue</div>
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-card">
                <div class="stat-value">64%</div>
                <div class="stat-label">Conversion</div>
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-card">
                <div class="stat-value">312</div>
                <div class="stat-label">Orders</div>
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>',

        'tasks' => '
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
        </div>',

        'calendar' => '
        <div class="calendar-header">
            <button><i class="fas fa-chevron-left"></i></button>
            <h3>'.date('F Y').'</h3>
            <button><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="calendar-grid">
            <div class="day-name">Sun</div>
            <div class="day-name">Mon</div>
            <div class="day-name">Tue</div>
            <div class="day-name">Wed</div>
            <div class="day-name">Thu</div>
            <div class="day-name">Fri</div>
            <div class="day-name">Sat</div>

            '.generate_calendar().'
        </div>',

        'notes' => '
        <div class="note active">
            <div class="note-header">
                <h4>Meeting Notes</h4>
                <div class="note-date">Today, '.date('g:i A').'</div>
            </div>
            <div class="note-content">
                Discussed dashboard features and widget implementation.
                Decided to go with a dark glass theme with neomorphic elements.
                Need to finalize the settings panel by Friday.
            </div>
        </div>
        <div class="note">
            <div class="note-header">
                <h4>To-Do List</h4>
                <div class="note-date">Yesterday</div>
            </div>
            <div class="note-content">
                - Research neomorphism trends<br>
                - Create widget templates<br>
                - Implement drag and drop<br>
                - Test responsiveness
            </div>
        </div>
        <div class="new-note">
            <textarea placeholder="Start typing a new note..."></textarea>
            <button>Save Note</button>
        </div>',

        'activity' => '
        <div class="activity-list">
            <div class="activity-item">
                <i class="fas fa-user-plus"></i>
                <div class="activity-content">
                    <div class="activity-title">New user registered</div>
                    <div class="activity-time">2 minutes ago</div>
                </div>
            </div>
            <div class="activity-item">
                <i class="fas fa-chart-line"></i>
                <div class="activity-content">
                    <div class="activity-title">Monthly report generated</div>
                    <div class="activity-time">15 minutes ago</div>
                </div>
            </div>
            <div class="activity-item">
                <i class="fas fa-wrench"></i>
                <div class="activity-content">
                    <div class="activity-title">System maintenance completed</div>
                    <div class="activity-time">1 hour ago</div>
                </div>
            </div>
            <div class="activity-item">
                <i class="fas fa-tasks"></i>
                <div class="activity-content">
                    <div class="activity-title">Task "Design settings" completed</div>
                    <div class="activity-time">3 hours ago</div>
                </div>
            </div>
            <div class="activity-item">
                <i class="fas fa-download"></i>
                <div class="activity-content">
                    <div class="activity-title">New update available</div>
                    <div class="activity-time">5 hours ago</div>
                </div>
            </div>
        </div>'
    ];

    return $widget_content[$widget_id] ?? '<div class="widget-content">Widget content will appear here</div>';
}

// Helper function to generate calendar
function generate_calendar() {
    $days_in_month = date('t');
    $first_day = date('w', strtotime(date('Y-m-01')));

    $html = '';

    // Empty days for the first week
    for ($i = 0; $i < $first_day; $i++) {
        $html .= '<div class="day empty"></div>';
    }

    // Days of the month
    for ($day = 1; $day <= $days_in_month; $day++) {
        $today = ($day == date('j')) ? ' today' : '';
        $event = (in_array($day, [7, 10, 17, 24])) ? ' event' : '';
        $html .= '<div class="day'.$today.$event.'">'.$day.'</div>';

        // New row every 7 days
        if (($day + $first_day) % 7 == 0 && $day < $days_in_month) {
            $html .= ''; // Removed the closing and opening div for calendar-grid as it's already structured in index.php
        }
    }

    // Fill remaining empty days
    $remaining = 42 - $days_in_month - $first_day;
    for ($i = 0; $i < $remaining; $i++) {
        $html .= '<div class="day empty"></div>';
    }

    return $html;
}
