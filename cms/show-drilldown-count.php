<?php
/**
 * Simple Drill-Down Count Display
 * Shows exact device count with drill-down cache
 */

require_once dirname(__DIR__) . '/bootstrap.php';
require_once __DIR__ . '/functions.php';
requireAuth();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Drill-Down Cache Count</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .count {
            font-size: 72px;
            font-weight: bold;
            color: #27ae60;
            text-align: center;
            margin: 20px 0;
        }
        .label {
            text-align: center;
            font-size: 24px;
            color: #666;
            margin-bottom: 30px;
        }
        .stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 30px;
        }
        .stat {
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .stat-label {
            font-size: 14px;
            color: #888;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1 style="text-align: center; color: #333;">Drill-Down Cache Status</h1>

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

            $coverage = $totalDevices > 0 ? round(($drilldownCount / $totalDevices) * 100, 2) : 0;

            // Get freshness
            $stmt = $pdo->query("
                SELECT
                    MIN(cached_at) as oldest,
                    MAX(cached_at) as newest,
                    SUM(CASE WHEN cached_at > NOW() - INTERVAL 1 HOUR THEN 1 ELSE 0 END) as last_hour
                FROM {$drilldownTable}
            ");
            $freshness = $stmt->fetch(PDO::FETCH_ASSOC);

            echo '<div class="count">' . number_format($drilldownCount) . '</div>';
            echo '<div class="label">Devices with Full Drill-Down Cache</div>';

            echo '<div class="stats">';
            echo '<div class="stat">';
            echo '<div class="stat-label">Total Devices</div>';
            echo '<div class="stat-value">' . number_format($totalDevices) . '</div>';
            echo '</div>';

            echo '<div class="stat">';
            echo '<div class="stat-label">Coverage</div>';
            echo '<div class="stat-value" style="color: ' . ($coverage >= 80 ? '#27ae60' : '#f39c12') . '">' . $coverage . '%</div>';
            echo '</div>';

            echo '<div class="stat">';
            echo '<div class="stat-label">Cached in Last Hour</div>';
            echo '<div class="stat-value">' . number_format($freshness['last_hour']) . '</div>';
            echo '</div>';

            echo '<div class="stat">';
            echo '<div class="stat-label">Newest Entry</div>';
            echo '<div class="stat-value" style="font-size: 16px;">' . ($freshness['newest'] ?: 'Never') . '</div>';
            echo '</div>';
            echo '</div>';

        } catch (Exception $e) {
            echo '<p style="color: red; text-align: center;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>

        <p style="text-align: center; margin-top: 30px;">
            <a href="system-diagnostics.php" style="color: #3498db; text-decoration: none;">View Full Diagnostics →</a>
        </p>
    </div>
</body>
</html>
