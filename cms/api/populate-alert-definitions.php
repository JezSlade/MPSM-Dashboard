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
// Reference: docs/MPSM_Code_Descriptions.md
$definitions = [
    // Input/Paper alerts (800 series)
    ['code' => '807', 'name' => 'Input Media Supply Low', 'description' => 'Paper tray is running low', 'category' => 'Paper', 'severity' => 'warning'],
    ['code' => '808', 'name' => 'Input Media Supply Empty', 'description' => 'Paper tray is empty', 'category' => 'Paper', 'severity' => 'high'],
    ['code' => '809', 'name' => 'Input Media Change Request', 'description' => 'Different paper type requested', 'category' => 'Paper', 'severity' => 'info'],
    ['code' => '810', 'name' => 'Input Manual Input Request', 'description' => 'Manual feed requested', 'category' => 'Paper', 'severity' => 'info'],

    // General alerts
    ['code' => '8', 'name' => 'Jam', 'description' => 'Paper jam detected in device', 'category' => 'Paper', 'severity' => 'high'],
    ['code' => '3', 'name' => 'Cover Open', 'description' => 'Device cover is open', 'category' => 'Service', 'severity' => 'warning'],
    ['code' => '501', 'name' => 'Door Open', 'description' => 'Device door is open', 'category' => 'Service', 'severity' => 'warning'],

    // Output alerts (900 series)
    ['code' => '902', 'name' => 'Output Media Tray Almost Full', 'description' => 'Output tray is almost full', 'category' => 'Paper', 'severity' => 'warning'],
    ['code' => '903', 'name' => 'Output Media Tray Full', 'description' => 'Output tray is full', 'category' => 'Paper', 'severity' => 'high'],

    // Toner alerts (1100 series)
    ['code' => '1101', 'name' => 'Marker Toner Empty', 'description' => 'Toner cartridge is empty', 'category' => 'Toner', 'severity' => 'critical'],
    ['code' => '1104', 'name' => 'Marker Toner Almost Empty', 'description' => 'Toner cartridge is running low', 'category' => 'Toner', 'severity' => 'warning'],
    ['code' => '1107', 'name' => 'Waste Toner Receptacle Almost Full', 'description' => 'Waste toner container is almost full', 'category' => 'Service', 'severity' => 'warning'],
    ['code' => '1109', 'name' => 'Waste Toner Receptacle Full', 'description' => 'Waste toner container is full', 'category' => 'Service', 'severity' => 'high'],

    // Fuser alerts (1000 series)
    ['code' => '1001', 'name' => 'Marker Fuser Under Temperature', 'description' => 'Fuser temperature too low', 'category' => 'Service', 'severity' => 'high'],
    ['code' => '1002', 'name' => 'Marker Fuser Over Temperature', 'description' => 'Fuser temperature too high', 'category' => 'Service', 'severity' => 'critical'],
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
