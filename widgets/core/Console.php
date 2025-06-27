<div class="console-widget">
    <div class="widget-header">
        <h3>System Console</h3>
        <div class="widget-actions">
            <button class="refresh-btn">⟳</button>
        </div>
    </div>
    <div class="console-output">
        <?php
        $logFile = DEBUG_LOG_FILE;
        if (file_exists($logFile)) {
            $logs = array_slice(file($logFile), -50);
            foreach ($logs as $log) {
                echo '<div class="log-entry">' . htmlspecialchars($log) . '</div>';
            }
        } else {
            echo '<div class="no-logs">No log entries found</div>';
        }
        ?>
    </div>
    <style>
        .console-output {
            max-height: 400px;
            overflow-y: auto;
            font-family: monospace;
            background: rgba(0,0,0,0.3);
            padding: 10px;
            border-radius: 5px;
        }
        .log-entry {
            margin-bottom: 5px;
            padding-bottom: 5px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
    </style>
</div>