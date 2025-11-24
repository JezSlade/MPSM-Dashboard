<?php
/**
 * Populate Alert Definitions
 * One-time script to add common alert code descriptions
 */

require '../config.php';
require '../functions.php';

requireAuth();

$pdo = getDatabase();
$table = DB_PREFIX . 'alert_definitions';

// Common alert codes with human-readable descriptions
$definitions = [
    ['code' => '808', 'name' => 'Paper Jam', 'description' => 'Paper jam detected in device', 'category' => 'Paper', 'severity' => 'high'],
    ['code' => '024', 'name' => 'Toner Low', 'description' => 'Toner cartridge running low', 'category' => 'Toner', 'severity' => 'warning'],
    ['code' => '025', 'name' => 'Toner Empty', 'description' => 'Toner cartridge is empty', 'category' => 'Toner', 'severity' => 'high'],
    ['code' => '026', 'name' => 'Waste Toner Full', 'description' => 'Waste toner container is full', 'category' => 'Service', 'severity' => 'warning'],
    ['code' => '116', 'name' => 'Service Required', 'description' => 'Device requires service', 'category' => 'Service', 'severity' => 'high'],
    ['code' => '200', 'name' => 'Cover Open', 'description' => 'Device cover is open', 'category' => 'Error', 'severity' => 'warning'],
    ['code' => '400', 'name' => 'Offline', 'description' => 'Device is offline', 'category' => 'Connectivity', 'severity' => 'critical'],
];

$inserted = 0;
$skipped = 0;

foreach ($definitions as $def) {
    // Check if already exists
    $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE alert_code = :code");
    $stmt->execute([':code' => $def['code']]);

    if ($stmt->fetch()) {
        $skipped++;
        continue;
    }

    // Insert new definition
    $stmt = $pdo->prepare("
        INSERT INTO {$table}
        (alert_code, display_name, description, category, severity_override, enabled, source, created_by)
        VALUES (:code, :name, :description, :category, :severity, 1, 'system', :user_id)
    ");

    $stmt->execute([
        ':code' => $def['code'],
        ':name' => $def['name'],
        ':description' => $def['description'],
        ':category' => $def['category'],
        ':severity' => $def['severity'],
        ':user_id' => $_SESSION['user_id']
    ]);

    $inserted++;
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'inserted' => $inserted,
    'skipped' => $skipped,
    'message' => "Inserted {$inserted} alert definitions, skipped {$skipped} existing"
]);
