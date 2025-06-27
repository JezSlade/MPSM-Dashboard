<?php
session_start();

// Initialize dashboard if not set
if (!isset($_SESSION['dashboard'])) {
    $_SESSION['dashboard'] = [
        'settings' => [
            'title' => 'Glass Dashboard',
            'accent_color' => '#6366f1',
            'glass_intensity' => 0.6,
            'blur_amount' => '10px',
            'enable_animations' => true
        ],
        'widgets' => [
            [
                'id' => 'stats',
                'position' => 1,
                'title' => 'Key Metrics',
                'expanded' => false,
                'settings' => ['show_icons' => true]
            ],
            [
                'id' => 'tasks',
                'position' => 2,
                'title' => 'Task Manager',
                'expanded' => false,
                'settings' => ['show_completed' => true]
            ],
            [
                'id' => 'calendar',
                'position' => 3,
                'title' => 'Calendar',
                'expanded' => false,
                'settings' => []
            ],
            [
                'id' => 'activity',
                'position' => 4,
                'title' => 'Recent Activity',
                'expanded' => false,
                'settings' => []
            ],
            [
                'id' => 'notes',
                'position' => 5,
                'title' => 'Notes',
                'expanded' => false,
                'settings' => []
            ]
        ]
    ];
}
?>
