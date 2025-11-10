<?php
/**
 * Simple Database Inspector
 * Emits table counts and metadata to help track cache progress.
 * Access: authenticated (uses requireAuth).
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

requireAuth();

header('Content-Type: text/html; charset=utf-8');

$pdo = getDatabase();
$prefix = DB_PREFIX;

try {
    $tables = [];
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
} catch (Exception $e) {
    $tables = [];
    $error = $e->getMessage();
}

function tableCount(PDO $pdo, string $table): int {
    $stmt = $pdo->query("SELECT COUNT(*) FROM {$table}");
    return (int)$stmt->fetchColumn();
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="30">
    <title>DB Inspector</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; background:#f7f9fc; color:#1b263b; }
        table { border-collapse: collapse; width:100%; max-width:1000px; margin-bottom:2rem; }
        th,td { padding:0.65rem; border:1px solid #d1d7e0; text-align:left; }
        th { background:#1c3faa; color:#fff; }
        .meta { margin-bottom:1rem; }
        .error { color:#c62828; font-weight:bold; }
    </style>
</head>
<body>
    <h1>Database Inspector</h1>
    <p class="meta">Auto-refreshes every 30 seconds. Cached drilldown path: <strong>mpsm_cache_device_drilldown</strong>.</p>
    <?php if (!empty($error)): ?>
        <p class="error">Error: <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>Table</th>
                <th>Row Count</th>
                <th>Latest Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tables as $table): ?>
                <?php
                    $count = tableCount($pdo, $table);
                    $timestamp = null;
                    $tsStmt = $pdo->query("
                        SELECT MAX(created_at) AS latest
                        FROM {$table}
                        WHERE created_at IS NOT NULL
                    ");
                    $tsRow = $tsStmt->fetch(PDO::FETCH_ASSOC);
                    $timestamp = $tsRow['latest'] ?? null;
                ?>
                <tr>
                    <td><?= htmlspecialchars($table) ?></td>
                    <td><?= number_format($count) ?></td>
                    <td><?= $timestamp ? htmlspecialchars($timestamp) : 'n/a' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p>Use this report to check cache + drilldown table population.</p>
</body>
</html>
