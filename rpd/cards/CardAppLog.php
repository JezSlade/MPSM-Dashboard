<?php
header('Content-Type: text/html; charset=utf-8');

// Sample log entries
$logs = [
    ['time' => '14:32:15', 'level' => 'INFO', 'message' => 'User login successful'],
    ['time' => '14:31:42', 'level' => 'WARNING', 'message' => 'High memory usage detected'],
    ['time' => '14:30:18', 'level' => 'ERROR', 'message' => 'Database connection failed'],
    ['time' => '14:29:55', 'level' => 'INFO', 'message' => 'Backup completed successfully'],
    ['time' => '14:28:33', 'level' => 'DEBUG', 'message' => 'Cache cleared'],
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 15px;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #ecf0f1;
            min-height: 280px;
        }
        .log-container {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 12px;
            padding: 15px;
            height: 250px;
            overflow-y: auto;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .log-title {
            text-align: center;
            margin-bottom: 15px;
            color: #ecf0f1;
            font-weight: 600;
            font-family: 'Segoe UI', sans-serif;
        }
        .log-entry {
            display: flex;
            margin-bottom: 8px;
            font-size: 0.85rem;
            line-height: 1.4;
        }
        .log-time {
            color: #95a5a6;
            margin-right: 10px;
            min-width: 60px;
        }
        .log-level {
            margin-right: 10px;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            min-width: 50px;
            text-align: center;
            font-size: 0.75rem;
        }
        .level-INFO { background: #3498db; color: white; }
        .level-WARNING { background: #f39c12; color: white; }
        .level-ERROR { background: #e74c3c; color: white; }
        .level-DEBUG { background: #9b59b6; color: white; }
        
        .log-message {
            flex: 1;
            color: #ecf0f1;
        }
    </style>
</head>
<body>
    <div class="log-container">
        <h3 class="log-title">Application Logs</h3>
        <?php foreach ($logs as $log): ?>
            <div class="log-entry">
                <span class="log-time"><?php echo $log['time']; ?></span>
                <span class="log-level level-<?php echo $log['level']; ?>"><?php echo $log['level']; ?></span>
                <span class="log-message"><?php echo htmlspecialchars($log['message']); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
