<?php
/**
 * Check Dashboard Notifications - Diagnostic Script
 *
 * Shows recent notifications with their device_serial and alert_code values
 */

require '../config.php';
require '../functions.php';

header('Content-Type: application/json');

$pdo = getDatabase();
$prefix = DB_PREFIX;

// Get recent notifications
$sql = "SELECT
            id,
            title,
            message,
            severity,
            device_serial,
            alert_code,
            customer_code,
            status,
            created_at_ny
        FROM {$prefix}dashboard_notifications
        ORDER BY created_at_ny DESC
        LIMIT 10";

$stmt = $pdo->query($sql);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'count' => count($notifications),
    'notifications' => $notifications
], JSON_PRETTY_PRINT);
