<?php
/**
 * Monitor Drill-Down Population Progress
 * Auto-refreshing page that shows real-time progress
 */

require_once dirname(__DIR__, 2) . '/bootstrap.php';
require_once dirname(__DIR__) . '/functions.php';
requireAuth();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Drill-Down Population Monitor</title>
    <meta http-equiv="refresh" content="10">
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #4ec9b0;
            border-bottom: 2px solid #4ec9b0;
            padding-bottom: 10px;
        }
        .stats {
            background: #252526;
            border: 1px solid #3e3e42;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .stat-row {
            display: grid;
            grid-template-columns: 300px 1fr;
            padding: 10px 0;
            border-bottom: 1px solid #3e3e42;
        }
        .stat-row:last-child {
            border-bottom: none;
        }
        .label {
            color: #9cdcfe;
            font-weight: bold;
        }
        .value {
            color: #ce9178;
            font-size: 1.2em;
        }
        .value.good {
            color: #4ec9b0;
        }
        .value.warning {
            color: #dcdcaa;
        }
        .value.error {
            color: #f48771;
        }
        .progress-bar {
            background: #3e3e42;
            border-radius: 10px;
            height: 30px;
            margin: 10px 0;
            overflow: hidden;
        }
        .progress-fill {
            background: linear-gradient(90deg, #4ec9b0, #569cd6);
            height: 100%;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .timestamp {
            color: #858585;
            font-size: 0.9em;
            text-align: right;
            margin-top: 20px;
        }
        .link {
            color: #569cd6;
            text-decoration: none;
        }
        .link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚡ DRILL-DOWN POPULATION MONITOR</h1>
        <p style="color: #858585;">Auto-refreshes every 10 seconds</p>

        <?php
        try {
            $pdo = getDatabase();
            $drilldownTable = DB_PREFIX . 'cache_device_drilldown';
            $devicesTable = DB_PREFIX . 'cache_devices';

            // Get counts
            $stmt = $pdo->query("SELECT COUNT(*) FROM {$devicesTable}");
            $totalDevices = (int)$stmt->fetchColumn();

            $stmt = $pdo->query("SELECT COUNT(*) FROM {$drilldownTable}");
            $drilldownCount = (int)$stmt->fetchColumn();

            // Get device activity (population in progress)
            $stmt = $pdo->query("
                SELECT COUNT(*)
                FROM {$devicesTable}
                WHERE cached_at > datetime('now', '-1 minute')
            ");
            $devicesLastMin = (int)$stmt->fetchColumn();

            $stmt = $pdo->query("
                SELECT MAX(cached_at) FROM {$devicesTable}
            ");
            $lastDeviceCached = $stmt->fetchColumn();

            $missing = $totalDevices - $drilldownCount;
            $coverage = $totalDevices > 0 ? round(($drilldownCount / $totalDevices) * 100, 2) : 0;

            // Get drill-down activity
            $stmt = $pdo->query("
                SELECT COUNT(*)
                FROM {$drilldownTable}
                WHERE cached_at > datetime('now', '-1 minute')
            ");
            $drilldownLastMin = (int)$stmt->fetchColumn();

            $stmt = $pdo->query("
                SELECT COUNT(*)
                FROM {$drilldownTable}
                WHERE cached_at > datetime('now', '-5 minute')
            ");
            $last5Min = (int)$stmt->fetchColumn();

            $stmt = $pdo->query("
                SELECT MAX(cached_at) FROM {$drilldownTable}
            ");
            $lastCached = $stmt->fetchColumn();

            // Calculate rate
            $ratePerMin = $drilldownLastMin;
            $estimatedMinutes = $ratePerMin > 0 ? ceil($missing / $ratePerMin) : 0;

            // Status determination - check BOTH device population and drill-down
            $expectedDevices = 5000; // Expected total from API
            $deviceProgress = $totalDevices > 0 ? round(($totalDevices / $expectedDevices) * 100, 2) : 0;

            $status = 'RUNNING';
            $statusClass = 'good';
            if ($totalDevices < $expectedDevices * 0.5) {
                $status = 'POPULATING DEVICES (' . $deviceProgress . '%)';
                $statusClass = 'warning';
            } elseif ($coverage >= 100) {
                $status = 'COMPLETE ✓';
                $statusClass = 'good';
            } elseif ($drilldownLastMin == 0 && $last5Min == 0) {
                $status = 'STALLED';
                $statusClass = 'error';
            } elseif ($drilldownLastMin == 0) {
                $status = 'SLOW';
                $statusClass = 'warning';
            }

            echo '<div class="stats">';

            echo '<div class="stat-row">';
            echo '<div class="label">STATUS:</div>';
            echo '<div class="value ' . $statusClass . '">' . $status . '</div>';
            echo '</div>';

            echo '<div class="stat-row">';
            echo '<div class="label">Total Devices:</div>';
            echo '<div class="value">' . number_format($totalDevices) . '</div>';
            echo '</div>';

            echo '<div class="stat-row">';
            echo '<div class="label">Devices with Drill-Down:</div>';
            echo '<div class="value good">' . number_format($drilldownCount) . '</div>';
            echo '</div>';

            echo '<div class="stat-row">';
            echo '<div class="label">Missing Drill-Down:</div>';
            echo '<div class="value ' . ($missing > 0 ? 'warning' : 'good') . '">' . number_format($missing) . '</div>';
            echo '</div>';

            echo '<div class="stat-row">';
            echo '<div class="label">Coverage:</div>';
            echo '<div class="value ' . ($coverage >= 95 ? 'good' : ($coverage >= 80 ? 'warning' : 'error')) . '">' . $coverage . '%</div>';
            echo '</div>';

            echo '</div>';

            // Progress bar
            echo '<div class="progress-bar">';
            echo '<div class="progress-fill" style="width: ' . $coverage . '%">' . $coverage . '%</div>';
            echo '</div>';

            // Activity stats
            echo '<div class="stats">';
            echo '<h2 style="color: #4ec9b0; margin-top: 0;">ACTIVITY</h2>';

            echo '<div class="stat-row">';
            echo '<div class="label">Cached in Last Minute:</div>';
            echo '<div class="value ' . ($lastMinute > 0 ? 'good' : 'error') . '">' . $lastMinute . '</div>';
            echo '</div>';

            echo '<div class="stat-row">';
            echo '<div class="label">Cached in Last 5 Minutes:</div>';
            echo '<div class="value">' . $last5Min . '</div>';
            echo '</div>';

            echo '<div class="stat-row">';
            echo '<div class="label">Rate:</div>';
            echo '<div class="value">' . $ratePerMin . ' devices/minute</div>';
            echo '</div>';

            echo '<div class="stat-row">';
            echo '<div class="label">Last Activity:</div>';
            echo '<div class="value">' . ($lastCached ?: 'Never') . '</div>';
            echo '</div>';

            if ($missing > 0 && $ratePerMin > 0) {
                echo '<div class="stat-row">';
                echo '<div class="label">Estimated Time Remaining:</div>';
                echo '<div class="value">' . $estimatedMinutes . ' minutes (' . round($estimatedMinutes / 60, 1) . ' hours)</div>';
                echo '</div>';
            }

            echo '</div>';

            // Actions
            echo '<div class="stats">';
            echo '<h2 style="color: #4ec9b0; margin-top: 0;">ACTIONS</h2>';
            echo '<p><a class="link" href="force-populate-all-drilldowns.php" target="_blank">🚀 Force Populate (New Window)</a></p>';
            echo '<p><a class="link" href="diagnose-cache-issue.php" target="_blank">🔍 Run Diagnostics</a></p>';
            echo '<p><a class="link" href="../show-drilldown-count.php" target="_blank">📊 Simple Count View</a></p>';
            echo '<p><a class="link" href="../system-diagnostics.php" target="_blank">💊 Full System Diagnostics</a></p>';
            echo '</div>';

            echo '<div class="timestamp">Last updated: ' . date('Y-m-d H:i:s') . ' | Auto-refresh: 10s</div>';

        } catch (Exception $e) {
            echo '<div class="stats">';
            echo '<div style="color: #f48771;">ERROR: ' . htmlspecialchars($e->getMessage()) . '</div>';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>
