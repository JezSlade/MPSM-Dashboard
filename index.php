<?php
// dashboard.php
session_start();

// Initialize active widgets if not set
if (!isset($_SESSION['active_widgets'])) {
    $_SESSION['active_widgets'] = [
        ['id' => 'stats', 'position' => 1],
        ['id' => 'tasks', 'position' => 2],
        ['id' => 'calendar', 'position' => 3],
        ['id' => 'notes', 'position' => 4],
        ['id' => 'activity', 'position' => 5]
    ];
}

// Handle widget management
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_widget']) && !empty($_POST['widget_id'])) {
        // Add new widget
        $new_widget = [
            'id' => $_POST['widget_id'],
            'position' => count($_SESSION['active_widgets']) + 1
        ];
        $_SESSION['active_widgets'][] = $new_widget;
    } elseif (isset($_POST['remove_widget']) && isset($_POST['widget_index'])) {
        // Remove widget
        unset($_SESSION['active_widgets'][$_POST['widget_index']]);
        $_SESSION['active_widgets'] = array_values($_SESSION['active_widgets']);
    } elseif (isset($_POST['update_settings'])) {
        // Update settings
        $_SESSION['dashboard_settings'] = [
            'title' => $_POST['dashboard_title'] ?? 'Glass Dashboard',
            'accent_color' => $_POST['accent_color'] ?? '#6366f1',
            'glass_intensity' => $_POST['glass_intensity'] ?? 0.6,
            'blur_amount' => $_POST['blur_amount'] ?? '10px',
            'enable_animations' => isset($_POST['enable_animations'])
        ];
    }
}

// Load settings
$settings = $_SESSION['dashboard_settings'] ?? [
    'title' => 'Glass Dashboard',
    'accent_color' => '#6366f1',
    'glass_intensity' => 0.6,
    'blur_amount' => '10px',
    'enable_animations' => true
];

// Widget definitions
$available_widgets = [
    'stats' => [
        'name' => 'Statistics',
        'icon' => 'chart-bar',
        'width' => 2,
        'height' => 1
    ],
    'tasks' => [
        'name' => 'Task Manager',
        'icon' => 'tasks',
        'width' => 1,
        'height' => 2
    ],
    'calendar' => [
        'name' => 'Calendar',
        'icon' => 'calendar',
        'width' => 1,
        'height' => 1
    ],
    'notes' => [
        'name' => 'Quick Notes',
        'icon' => 'sticky-note',
        'width' => 1,
        'height' => 1
    ],
    'activity' => [
        'name' => 'Recent Activity',
        'icon' => 'history',
        'width' => 2,
        'height' => 1
    ]
];

// Function to render widget content
function render_widget($widget_id) {
    // This would normally be in separate files
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
            $html .= '</div><div class="calendar-grid">';
        }
    }
    
    // Fill remaining empty days
    $remaining = 42 - $days_in_month - $first_day;
    for ($i = 0; $i < $remaining; $i++) {
        $html .= '<div class="day empty"></div>';
    }
    
    return $html;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['title']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #1a1d24;
            --bg-secondary: #232831;
            --bg-tertiary: #2d333f;
            --text-primary: #e0e0e0;
            --text-secondary: #a0aec0;
            --accent: <?= $settings['accent_color'] ?>;
            --accent-hover: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --glass-bg: rgba(35, 40, 49, <?= $settings['glass_intensity'] ?>);
            --glass-border: rgba(255, 255, 255, 0.08);
            --shadow-light: rgba(0, 0, 0, 0.3);
            --shadow-dark: rgba(0, 0, 0, 0.5);
            --transition: all 0.3s ease;
            --blur-amount: <?= $settings['blur_amount'] ?>;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: var(--text-primary);
            min-height: 100vh;
            padding: 20px;
            background-attachment: fixed;
        }

        .dashboard {
            display: grid;
            grid-template-columns: 280px 1fr;
            grid-template-rows: auto 1fr;
            gap: 20px;
            max-width: 1800px;
            margin: 0 auto;
        }

        /* Header Styles */
        .header {
            grid-column: 1 / -1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            backdrop-filter: blur(var(--blur-amount));
            -webkit-backdrop-filter: blur(var(--blur-amount));
            box-shadow: 
                8px 8px 16px var(--shadow-dark),
                -8px -8px 16px rgba(74, 78, 94, 0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--accent);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 
                4px 4px 8px var(--shadow-dark),
                -4px -4px 8px rgba(74, 78, 94, 0.1);
        }

        .logo-text {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(90deg, var(--accent), var(--info));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            box-shadow: 
                4px 4px 8px var(--shadow-dark),
                -4px -4px 8px rgba(74, 78, 94, 0.1);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 
                6px 6px 12px var(--shadow-dark),
                -6px -6px 12px rgba(74, 78, 94, 0.1);
        }

        .btn-primary {
            background: var(--accent);
            color: white;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
        }

        /* Sidebar Styles */
        .sidebar {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 20px;
            backdrop-filter: blur(var(--blur-amount));
            -webkit-backdrop-filter: blur(var(--blur-amount));
            box-shadow: 
                8px 8px 16px var(--shadow-dark),
                -8px -8px 16px rgba(74, 78, 94, 0.1);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .sidebar-section {
            padding-bottom: 20px;
            border-bottom: 1px solid var(--glass-border);
        }

        .sidebar-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .section-title {
            font-size: 16px;
            color: var(--text-secondary);
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border-radius: 12px;
            cursor: pointer;
            transition: var(--transition);
            margin-bottom: 8px;
        }

        .nav-item:hover, .nav-item.active {
            background: var(--bg-tertiary);
            box-shadow: 
                inset 3px 3px 6px var(--shadow-dark),
                inset -3px -3px 6px rgba(74, 78, 94, 0.1);
        }

        .nav-item i {
            width: 20px;
            text-align: center;
        }

        .widget-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .widget-item {
            background: var(--bg-tertiary);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 15px;
            cursor: grab;
            transition: var(--transition);
            text-align: center;
            box-shadow: 
                4px 4px 8px var(--shadow-dark),
                -4px -4px 8px rgba(74, 78, 94, 0.1);
        }

        .widget-item:hover {
            transform: translateY(-3px);
            box-shadow: 
                6px 6px 12px var(--shadow-dark),
                -6px -6px 12px rgba(74, 78, 94, 0.1);
        }

        .widget-item i {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--accent);
        }

        .widget-item .widget-name {
            font-size: 14px;
            font-weight: 600;
        }

        /* Main Content Styles */
        .main-content {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 20px;
            backdrop-filter: blur(var(--blur-amount));
            -webkit-backdrop-filter: blur(var(--blur-amount));
            box-shadow: 
                8px 8px 16px var(--shadow-dark),
                -8px -8px 16px rgba(74, 78, 94, 0.1);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            grid-auto-rows: minmax(200px, auto);
            gap: 20px;
            align-content: flex-start;
        }

        .widget {
            background: var(--bg-tertiary);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 
                8px 8px 16px var(--shadow-dark),
                -8px -8px 16px rgba(74, 78, 94, 0.1);
            transition: <?= $settings['enable_animations'] ? 'var(--transition)' : 'none' ?>;
            display: flex;
            flex-direction: column;
            grid-column: span var(--width, 1);
            grid-row: span var(--height, 1);
        }

        .widget:hover {
            <?php if ($settings['enable_animations']): ?>
            transform: translateY(-5px);
            box-shadow: 
                12px 12px 24px var(--shadow-dark),
                -12px -12px 24px rgba(74, 78, 94, 0.1);
            <?php endif; ?>
        }

        .widget-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--glass-border);
        }

        .widget-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
            font-weight: 600;
        }

        .widget-actions {
            display: flex;
            gap: 10px;
        }

        .widget-action {
            width: 30px;
            height: 30px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            background: var(--bg-secondary);
            border: 1px solid var(--glass-border);
            color: var(--text-secondary);
            transition: var(--transition);
        }

        .widget-action:hover {
            background: var(--accent);
            color: white;
        }

        .widget-content {
            flex: 1;
            overflow-y: auto;
        }

        /* Widget Content Styles */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .stat-card {
            background: var(--bg-secondary);
            border-radius: 15px;
            padding: 15px;
            position: relative;
            overflow: hidden;
            box-shadow: 
                inset 3px 3px 6px var(--shadow-dark),
                inset -3px -3px 6px rgba(74, 78, 94, 0.1);
        }

        .stat-card i {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 24px;
            opacity: 0.3;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .task-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .task-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 12px;
            background: var(--bg-secondary);
            transition: var(--transition);
        }

        .task-item.urgent {
            border-left: 4px solid var(--danger);
        }

        .task-item.completed label {
            text-decoration: line-through;
            color: var(--text-secondary);
        }

        .task-input {
            display: flex;
            margin-top: 15px;
            gap: 10px;
        }

        .task-input input {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            border: none;
            background: var(--bg-secondary);
            color: var(--text-primary);
            box-shadow: 
                inset 3px 3px 6px var(--shadow-dark),
                inset -3px -3px 6px rgba(74, 78, 94, 0.1);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }

        .day-name, .day {
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }

        .day-name {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 14px;
        }

        .day {
            background: var(--bg-secondary);
            cursor: pointer;
            transition: var(--transition);
        }

        .day.today {
            background: var(--accent);
            color: white;
            font-weight: bold;
        }

        .day:hover:not(.today) {
            background: var(--accent);
        }

        .day.event {
            position: relative;
        }

        .day.event:after {
            content: '';
            position: absolute;
            width: 6px;
            height: 6px;
            background: var(--accent);
            border-radius: 50%;
            bottom: 5px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .note {
            background: var(--bg-secondary);
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            transition: var(--transition);
        }

        .note.active {
            border-left: 4px solid var(--accent);
        }

        .note-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .note-date {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .activity-item {
            display: flex;
            gap: 15px;
        }

        .activity-item i {
            font-size: 20px;
            color: var(--accent);
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 500;
            margin-bottom: 4px;
        }

        .activity-time {
            font-size: 12px;
            color: var(--text-secondary);
        }

        /* Settings Panel */
        .settings-panel {
            position: fixed;
            top: 0;
            right: -400px;
            width: 380px;
            height: 100vh;
            background: var(--bg-tertiary);
            border-left: 1px solid var(--glass-border);
            padding: 30px;
            z-index: 1000;
            transition: var(--transition);
            overflow-y: auto;
            box-shadow: 
                -8px 0 16px var(--shadow-dark);
        }

        .settings-panel.active {
            right: 0;
        }

        .settings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--glass-border);
        }

        .settings-group {
            margin-bottom: 30px;
        }

        .settings-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--accent);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border-radius: 12px;
            background: var(--bg-secondary);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            box-shadow: 
                inset 3px 3px 6px var(--shadow-dark),
                inset -3px -3px 6px rgba(74, 78, 94, 0.1);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--bg-secondary);
            transition: .4s;
            border-radius: 24px;
            box-shadow: 
                inset 2px 2px 4px var(--shadow-dark),
                inset -2px -2px 4px rgba(74, 78, 94, 0.1);
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: var(--text-secondary);
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--accent);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
            background-color: white;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                grid-row: 2;
            }
        }

        @media (max-width: 992px) {
            .main-content {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .widget-list {
                grid-template-columns: 1fr;
            }
            
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 20px;
            }
            
            .settings-panel {
                width: 100%;
                right: -100%;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Dashboard Header -->
        <header class="header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <div class="logo-text"><?= htmlspecialchars($settings['title']) ?></div>
            </div>
            
            <div class="header-actions">
                <button class="btn" id="settings-toggle">
                    <i class="fas fa-cog"></i> Settings
                </button>
                <button class="btn" id="refresh-btn">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <button class="btn btn-primary" id="new-widget-btn">
                    <i class="fas fa-plus"></i> New Widget
                </button>
            </div>
        </header>
        
        <!-- Dashboard Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="section-title">Navigation</div>
                <div class="nav-item active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-chart-pie"></i>
                    <span>Analytics</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </div>
            </div>
            
            <div class="sidebar-section">
                <div class="section-title">Widget Library</div>
                <div class="widget-list">
                    <?php foreach ($available_widgets as $id => $widget): ?>
                    <div class="widget-item" draggable="true" data-widget-id="<?= $id ?>">
                        <i class="fas fa-<?= $widget['icon'] ?>"></i>
                        <div class="widget-name"><?= $widget['name'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="sidebar-section">
                <div class="section-title">Dashboard Settings</div>
                <div class="nav-item" id="theme-settings-btn">
                    <i class="fas fa-palette"></i>
                    <span>Theme Settings</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-columns"></i>
                    <span>Layout Options</span>
                </div>
                <div class="nav-item">
                    <i class="fas fa-sliders-h"></i>
                    <span>Advanced Settings</span>
                </div>
            </div>
        </aside>
        
        <!-- Main Content Area -->
        <main class="main-content" id="widget-container">
            <?php foreach ($_SESSION['active_widgets'] as $index => $widget): 
                $widget_def = $available_widgets[$widget['id']] ?? ['width' => 1, 'height' => 1];
            ?>
            <div class="widget" style="--width: <?= $widget_def['width'] ?>; --height: <?= $widget_def['height'] ?>">
                <div class="widget-header">
                    <div class="widget-title">
                        <i class="fas fa-<?= $widget_def['icon'] ?? 'cube' ?>"></i>
                        <span><?= $widget_def['name'] ?? 'Widget' ?></span>
                    </div>
                    <div class="widget-actions">
                        <div class="widget-action">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="widget-action">
                            <i class="fas fa-expand"></i>
                        </div>
                        <div class="widget-action remove-widget" data-index="<?= $index ?>">
                            <i class="fas fa-times"></i>
                        </div>
                    </div>
                </div>
                <div class="widget-content">
                    <?= render_widget($widget['id']) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </main>
    </div>
    
    <!-- Settings Panel -->
    <div class="overlay" id="settings-overlay"></div>
    <div class="settings-panel" id="settings-panel">
        <div class="settings-header">
            <h2>Dashboard Settings</h2>
            <button class="btn" id="close-settings">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form method="post" class="settings-form">
            <div class="settings-group">
                <h3 class="settings-title">General Settings</h3>
                
                <div class="form-group">
                    <label for="dashboard_title">Dashboard Title</label>
                    <input type="text" id="dashboard_title" name="dashboard_title" 
                        class="form-control" value="<?= htmlspecialchars($settings['title']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="accent_color">Accent Color</label>
                    <input type="color" id="accent_color" name="accent_color" 
                        class="form-control" value="<?= $settings['accent_color'] ?>" style="height: 50px;">
                </div>
                
                <div class="form-group">
                    <label>Enable Animations</label>
                    <label class="toggle-switch">
                        <input type="checkbox" name="enable_animations" 
                            <?= $settings['enable_animations'] ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
            
            <div class="settings-group">
                <h3 class="settings-title">Glass Effect</h3>
                
                <div class="form-group">
                    <label for="glass_intensity">Glass Intensity</label>
                    <input type="range" id="glass_intensity" name="glass_intensity" 
                        class="form-control" min="0.1" max="0.9" step="0.05" 
                        value="<?= $settings['glass_intensity'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="blur_amount">Blur Amount</label>
                    <select id="blur_amount" name="blur_amount" class="form-control">
                        <option value="5px" <?= $settings['blur_amount'] == '5px' ? 'selected' : '' ?>>Subtle (5px)</option>
                        <option value="10px" <?= $settings['blur_amount'] == '10px' ? 'selected' : '' ?>>Standard (10px)</option>
                        <option value="15px" <?= $settings['blur_amount'] == '15px' ? 'selected' : '' ?>>Strong (15px)</option>
                        <option value="20px" <?= $settings['blur_amount'] == '20px' ? 'selected' : '' ?>>Extra Strong (20px)</option>
                    </select>
                </div>
            </div>
            
            <div class="settings-group">
                <h3 class="settings-title">Add New Widget</h3>
                
                <div class="form-group">
                    <label for="widget_select">Select Widget</label>
                    <select id="widget_select" name="widget_id" class="form-control">
                        <?php foreach ($available_widgets as $id => $widget): ?>
                        <option value="<?= $id ?>"><?= $widget['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" name="add_widget" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-plus"></i> Add Widget to Dashboard
                </button>
            </div>
            
            <div class="settings-group">
                <h3 class="settings-title">Advanced</h3>
                <div class="form-group">
                    <label>Export Configuration</label>
                    <button class="btn" style="width: 100%;">
                        <i class="fas fa-download"></i> Download Settings
                    </button>
                </div>
                
                <div class="form-group">
                    <label>Import Configuration</label>
                    <input type="file" class="form-control">
                </div>
            </div>
            
            <button type="submit" name="update_settings" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                <i class="fas fa-save"></i> Save All Settings
            </button>
        </form>
    </div>
    
    <script>
        // Dashboard functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Settings panel toggle
            const settingsToggle = document.getElementById('settings-toggle');
            const closeSettings = document.getElementById('close-settings');
            const settingsPanel = document.getElementById('settings-panel');
            const settingsOverlay = document.getElementById('settings-overlay');
            
            settingsToggle.addEventListener('click', function() {
                settingsPanel.classList.add('active');
                settingsOverlay.style.display = 'block';
            });
            
            closeSettings.addEventListener('click', function() {
                settingsPanel.classList.remove('active');
                settingsOverlay.style.display = 'none';
            });
            
            settingsOverlay.addEventListener('click', function() {
                settingsPanel.classList.remove('active');
                this.style.display = 'none';
            });
            
            // Widget removal
            document.querySelectorAll('.remove-widget').forEach(button => {
                button.addEventListener('click', function() {
                    const widgetIndex = this.getAttribute('data-index');
                    const form = document.createElement('form');
                    form.method = 'post';
                    form.style.display = 'none';
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'remove_widget';
                    input.value = widgetIndex;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                });
            });
            
            // Drag and drop functionality
            const widgetItems = document.querySelectorAll('.widget-item');
            const mainContent = document.getElementById('widget-container');
            
            widgetItems.forEach(item => {
                item.addEventListener('dragstart', function(e) {
                    e.dataTransfer.setData('text/plain', this.dataset.widgetId);
                });
            });
            
            mainContent.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.style.backgroundColor = 'rgba(63, 114, 175, 0.1)';
            });
            
            mainContent.addEventListener('dragleave', function() {
                this.style.backgroundColor = '';
            });
            
            mainContent.addEventListener('drop', function(e) {
                e.preventDefault();
                this.style.backgroundColor = '';
                
                const widgetId = e.dataTransfer.getData('text/plain');
                addWidgetToDashboard(widgetId);
            });
            
            function addWidgetToDashboard(widgetId) {
                const form = document.createElement('form');
                form.method = 'post';
                form.style.display = 'none';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'add_widget';
                input.value = '1';
                
                const widgetInput = document.createElement('input');
                widgetInput.type = 'hidden';
                widgetInput.name = 'widget_id';
                widgetInput.value = widgetId;
                
                form.appendChild(input);
                form.appendChild(widgetInput);
                document.body.appendChild(form);
                form.submit();
            }
            
            // Refresh button
            document.getElementById('refresh-btn').addEventListener('click', function() {
                location.reload();
            });
            
            // Theme settings button
            document.getElementById('theme-settings-btn').addEventListener('click', function() {
                settingsPanel.classList.add('active');
                settingsOverlay.style.display = 'block';
            });
        });
    </script>
</body>
</html>