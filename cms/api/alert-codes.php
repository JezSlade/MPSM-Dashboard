<?php
/**
 * Alert Code Descriptions API
 * Manage human-readable descriptions for alert codes
 */

require '../config.php';
require '../functions.php';

requireAuth();

define('MPS_ENGINE_ACCESS', true);
require_once __DIR__ . '/../../mps-api/callbacks/command-center-schema.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$pdo = getDatabase();

// Ensure table exists
ensureAlertCodeDescriptionsTable($pdo);

header('Content-Type: application/json');

try {
    switch ($action) {
        case 'list':
            listAlertCodes($pdo);
            break;

        case 'get':
            getAlertCode($pdo);
            break;

        case 'update':
            updateAlertCode($pdo);
            break;

        case 'seed':
            seedAlertCodes($pdo);
            break;

        case 'lookup':
            lookupAlertCode($pdo);
            break;

        default:
            throw new Exception('Invalid action. Valid actions: list, get, update, seed, lookup');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function listAlertCodes(PDO $pdo): void
{
    $table = DB_PREFIX . 'alert_code_descriptions';
    $category = $_GET['category'] ?? null;
    $activeOnly = isset($_GET['activeOnly']) && $_GET['activeOnly'] === 'true';

    $sql = "SELECT * FROM {$table}";
    $conditions = [];
    $params = [];

    if ($category) {
        $conditions[] = "category = :category";
        $params[':category'] = $category;
    }

    if ($activeOnly) {
        $conditions[] = "is_active = 1";
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    $sql .= " ORDER BY CAST(alert_code AS UNSIGNED) ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $codes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get categories for filtering
    $catStmt = $pdo->query("SELECT DISTINCT category FROM {$table} WHERE category IS NOT NULL ORDER BY category");
    $categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'codes' => $codes,
        'count' => count($codes),
        'categories' => $categories
    ]);
}

function getAlertCode(PDO $pdo): void
{
    $alertCode = $_GET['code'] ?? null;
    if (!$alertCode) {
        throw new Exception('Alert code required');
    }

    $table = DB_PREFIX . 'alert_code_descriptions';
    $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE alert_code = :code");
    $stmt->execute([':code' => $alertCode]);
    $code = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$code) {
        throw new Exception('Alert code not found');
    }

    echo json_encode([
        'success' => true,
        'code' => $code
    ]);
}

function updateAlertCode(PDO $pdo): void
{
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['alert_code'])) {
        throw new Exception('Alert code required');
    }

    $table = DB_PREFIX . 'alert_code_descriptions';

    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE alert_code = :code");
    $stmt->execute([':code' => $data['alert_code']]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Update
        $sql = "UPDATE {$table} SET
                description = :description,
                custom_message = :custom_message,
                category = :category,
                severity_hint = :severity_hint,
                is_active = :is_active
                WHERE alert_code = :code";
    } else {
        // Insert
        $sql = "INSERT INTO {$table}
                (alert_code, description, custom_message, category, severity_hint, is_active)
                VALUES (:code, :description, :custom_message, :category, :severity_hint, :is_active)";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':code' => $data['alert_code'],
        ':description' => $data['description'] ?? 'Unknown Alert',
        ':custom_message' => $data['custom_message'] ?? null,
        ':category' => $data['category'] ?? null,
        ':severity_hint' => $data['severity_hint'] ?? null,
        ':is_active' => $data['is_active'] ?? 1
    ]);

    echo json_encode([
        'success' => true,
        'message' => $existing ? 'Alert code updated' : 'Alert code created'
    ]);
}

function lookupAlertCode(PDO $pdo): void
{
    $alertCode = $_GET['code'] ?? null;
    if (!$alertCode) {
        throw new Exception('Alert code required');
    }

    $description = getAlertCodeDescription($pdo, $alertCode);

    echo json_encode([
        'success' => true,
        'alert_code' => $alertCode,
        'description' => $description
    ]);
}

function seedAlertCodes(PDO $pdo): void
{
    $table = DB_PREFIX . 'alert_code_descriptions';

    // Standard MPS alert codes from specification
    $codes = [
        // General
        ['1', 'Other', 'General'],
        ['2', 'Unknown', 'General'],
        ['3', 'Cover Open', 'General'],
        ['4', 'Cover Closed', 'General'],
        ['5', 'Interlock Open', 'General'],
        ['6', 'Interlock Closed', 'General'],
        ['7', 'Configuration Change', 'General'],
        ['8', 'Jam', 'General'],
        ['9', 'Subunit Missing', 'General'],
        ['10', 'Subunit Life Almost Over', 'General'],
        ['11', 'Subunit Life Over', 'General'],
        ['12', 'Subunit Almost Empty', 'General'],
        ['13', 'Subunit Empty', 'General'],
        ['14', 'Subunit Almost Full', 'General'],
        ['15', 'Subunit Full', 'General'],
        ['16', 'Subunit Near Limit', 'General'],
        ['17', 'Subunit At Limit', 'General'],
        ['18', 'Subunit Opened', 'General'],
        ['19', 'Subunit Closed', 'General'],
        ['20', 'Subunit Turned On', 'General'],
        ['21', 'Subunit Turned Off', 'General'],
        ['22', 'Subunit Offline', 'General'],
        ['23', 'Subunit Power Saver', 'General'],
        ['24', 'Subunit Warming Up', 'General'],
        ['25', 'Subunit Added', 'General'],
        ['26', 'Subunit Removed', 'General'],
        ['27', 'Subunit Resource Added', 'General'],
        ['28', 'Subunit Resource Removed', 'General'],
        ['29', 'Subunit Recoverable Failure', 'General'],
        ['30', 'Subunit Unrecoverable Failure', 'General'],
        ['31', 'Subunit Recoverable Storage Error', 'General'],
        ['32', 'Subunit Unrecoverable Storage Error', 'General'],
        ['33', 'Subunit Motor Failure', 'General'],
        ['34', 'Subunit Memory Exhausted', 'General'],
        ['35', 'Subunit Under Temperature', 'General'],
        ['36', 'Subunit Over Temperature', 'General'],
        ['37', 'Subunit Timing Failure', 'General'],
        ['38', 'Subunit Thermistor Failure', 'General'],

        // Door/Power (501-507)
        ['501', 'Door Open', 'Door'],
        ['502', 'Door Closed', 'Door'],
        ['503', 'Power Up', 'Power'],
        ['504', 'Power Down', 'Power'],
        ['505', 'Printer NMS Reset', 'Power'],
        ['506', 'Printer Manual Reset', 'Power'],
        ['507', 'Printer Ready To Print', 'Power'],

        // Input (801-813)
        ['801', 'Input Media Tray Missing', 'Input'],
        ['802', 'Input Media Size Change', 'Input'],
        ['803', 'Input Media Weight Change', 'Input'],
        ['804', 'Input Media Type Change', 'Input'],
        ['805', 'Input Media Color Change', 'Input'],
        ['806', 'Input Media Form Parts Change', 'Input'],
        ['807', 'Input Media Supply Low', 'Input'],
        ['808', 'Input Media Supply Empty', 'Input'],
        ['809', 'Input Media Change Request', 'Input'],
        ['810', 'Input Manual Input Request', 'Input'],
        ['811', 'Input Tray Position Failure', 'Input'],
        ['812', 'Input Tray Elevation Failure', 'Input'],
        ['813', 'Input Cannot Feed Size Selected', 'Input'],

        // Output (901-904)
        ['901', 'Output Media Tray Missing', 'Output'],
        ['902', 'Output Media Tray Almost Full', 'Output'],
        ['903', 'Output Media Tray Full', 'Output'],
        ['904', 'Output Mailbox Select Failure', 'Output'],

        // Marker/Fuser (1001-1005)
        ['1001', 'Marker Fuser Under Temperature', 'Marker'],
        ['1002', 'Marker Fuser Over Temperature', 'Marker'],
        ['1003', 'Marker Fuser Timing Failure', 'Marker'],
        ['1004', 'Marker Fuser Thermistor Failure', 'Marker'],
        ['1005', 'Marker Adjusting Print Quality', 'Marker'],

        // Marker/Toner (1101-1115)
        ['1101', 'Marker Toner Empty', 'Marker'],
        ['1102', 'Marker Ink Empty', 'Marker'],
        ['1103', 'Marker Print Ribbon Empty', 'Marker'],
        ['1104', 'Marker Toner Almost Empty', 'Marker'],
        ['1105', 'Marker Ink Almost Empty', 'Marker'],
        ['1106', 'Marker Print Ribbon Almost Empty', 'Marker'],
        ['1107', 'Marker Waste Toner Receptacle Almost Full', 'Marker'],
        ['1108', 'Marker Waste Ink Receptacle Almost Full', 'Marker'],
        ['1109', 'Marker Waste Toner Receptacle Full', 'Marker'],
        ['1110', 'Marker Waste Ink Receptacle Full', 'Marker'],
        ['1111', 'Marker OPC Life Almost Over', 'Marker'],
        ['1112', 'Marker OPC Life Over', 'Marker'],
        ['1113', 'Marker Developer Almost Empty', 'Marker'],
        ['1114', 'Marker Developer Empty', 'Marker'],
        ['1115', 'Marker Toner Cartridge Missing', 'Marker'],

        // Media Path (1301-1304)
        ['1301', 'Media Path Media Tray Missing', 'Media Path'],
        ['1302', 'Media Path Media Tray Almost Full', 'Media Path'],
        ['1303', 'Media Path Media Tray Full', 'Media Path'],
        ['1304', 'Media Path Cannot Duplex Media Selected', 'Media Path'],

        // Interpreter (1501-1509)
        ['1501', 'Interpreter Memory Increase', 'Interpreter'],
        ['1502', 'Interpreter Memory Decrease', 'Interpreter'],
        ['1503', 'Interpreter Cartridge Added', 'Interpreter'],
        ['1504', 'Interpreter Cartridge Deleted', 'Interpreter'],
        ['1505', 'Interpreter Resource Added', 'Interpreter'],
        ['1506', 'Interpreter Resource Deleted', 'Interpreter'],
        ['1507', 'Interpreter Resource Unavailable', 'Interpreter'],
        ['1509', 'Interpreter Complex Page Encountered', 'Interpreter'],

        // Alert (1801)
        ['1801', 'Alert Removal Of Binary Change Entry', 'Alert'],
    ];

    $inserted = 0;
    $skipped = 0;

    $checkStmt = $pdo->prepare("SELECT id FROM {$table} WHERE alert_code = :code");
    $insertStmt = $pdo->prepare("
        INSERT INTO {$table} (alert_code, description, category)
        VALUES (:code, :description, :category)
    ");

    foreach ($codes as [$code, $description, $category]) {
        $checkStmt->execute([':code' => $code]);
        if ($checkStmt->fetch()) {
            $skipped++;
            continue;
        }

        $insertStmt->execute([
            ':code' => $code,
            ':description' => $description,
            ':category' => $category
        ]);
        $inserted++;
    }

    echo json_encode([
        'success' => true,
        'message' => "Seeded alert codes",
        'inserted' => $inserted,
        'skipped' => $skipped,
        'total_codes' => count($codes)
    ]);
}

/**
 * Get human-readable description for an alert code
 * Used by the notification engine
 */
function getAlertCodeDescription(PDO $pdo, string $alertCode): string
{
    static $cache = [];

    if (isset($cache[$alertCode])) {
        return $cache[$alertCode];
    }

    $table = DB_PREFIX . 'alert_code_descriptions';
    $stmt = $pdo->prepare("SELECT description FROM {$table} WHERE alert_code = :code AND is_active = 1");
    $stmt->execute([':code' => $alertCode]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $description = $result ? $result['description'] : "Alert {$alertCode}";
    $cache[$alertCode] = $description;

    return $description;
}
