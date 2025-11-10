<?php
/**
 * Create a test notification directly in the database
 */

$pdo = new PDO(
    'mysql:host=localhost;dbname=resolut7_mpsm;charset=utf8mb4',
    'resolut7_mpsm_agent',
    '!C@S@lcd6McFceb8',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$now = date('Y-m-d H:i:s');

$sql = "INSERT INTO mpsm_dashboard_notifications
        (title, message, severity, rule_id, device_serial, alert_code,
         customer_code, trigger_count, created_at_ny, icon, color, priority, status)
        VALUES
        ('TEST: Paper Jam Alert', 'This is a test notification created manually to verify Command Center display.',
         'warning', 1, 'TEST_DEVICE_123', '808', 'TEST_CUSTOMER', 1, ?, 'exclamation-triangle', 'yellow', 50, 'active')";

$stmt = $pdo->prepare($sql);
$stmt->execute([$now]);

$notificationId = $pdo->lastInsertId();

echo "✓ Test notification created:\n";
echo "  ID: {$notificationId}\n";
echo "  Title: TEST: Paper Jam Alert\n";
echo "  Severity: warning\n";
echo "  Status: active\n\n";

echo "Visit Command Center to view:\n";
echo "https://mpsm.resolutionsbydesign.us/cms/command-center.php\n\n";

// Check current notification count
$stmt = $pdo->query("SELECT COUNT(*) as total, severity FROM mpsm_dashboard_notifications WHERE status = 'active' GROUP BY severity");
$counts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Active notifications by severity:\n";
foreach ($counts as $row) {
    echo "  - {$row['severity']}: {$row['total']}\n";
}
