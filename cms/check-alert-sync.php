<?php
/**
 * CHECK: Alert system synchronization across all layers
 * Rules → Alerts → Descriptions → Database → User Definitions
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

header('Content-Type: text/plain; charset=utf-8');

$pdo = getDatabase();

echo "=== ALERT SYSTEM SYNCHRONIZATION CHECK ===\n\n";

// Layer 1: Notification Rules
echo "LAYER 1: NOTIFICATION RULES\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->query("
    SELECT id, name, alert_code_pattern, enabled
    FROM " . DB_PREFIX . "notification_rules
    ORDER BY enabled DESC, id
");

$rules = $stmt->fetchAll(PDO::FETCH_ASSOC);
$rulePatterns = [];

foreach ($rules as $rule) {
    $status = $rule['enabled'] ? 'ACTIVE' : 'DISABLED';
    echo "Rule {$rule['id']}: {$rule['name']}\n";
    echo "  Pattern: {$rule['alert_code_pattern']}\n";
    echo "  Status: {$status}\n\n";

    if ($rule['enabled']) {
        $rulePatterns[] = $rule['alert_code_pattern'];
    }
}

// Layer 2: Alert Codes in Panel Messages
echo "\nLAYER 2: ACTUAL ALERT CODES (in panel_messages)\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->query("
    SELECT DISTINCT maintenance_alert_code as code, COUNT(*) as count
    FROM " . DB_PREFIX . "panel_messages
    WHERE maintenance_alert_code IS NOT NULL
    GROUP BY maintenance_alert_code
    ORDER BY count DESC
    LIMIT 20
");

$actualCodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($actualCodes as $code) {
    echo "Code: {$code['code']} ({$code['count']} occurrences)\n";
}

// Layer 3: Alert Definitions (user-defined mappings)
echo "\n\nLAYER 3: ALERT DEFINITIONS (user-defined names)\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->query("
    SELECT id, alert_code, display_name, description, category, severity_override
    FROM " . DB_PREFIX . "alert_definitions
    ORDER BY alert_code
");

$definitions = $stmt->fetchAll(PDO::FETCH_ASSOC);
$definedCodes = [];

if (empty($definitions)) {
    echo "⚠️  NO ALERT DEFINITIONS FOUND\n";
    echo "This table is empty - user-defined names not configured\n";
} else {
    foreach ($definitions as $def) {
        echo "Code: {$def['alert_code']}\n";
        echo "  Name: {$def['display_name']}\n";
        echo "  Description: " . substr($def['description'], 0, 60) . "...\n";
        echo "  Category: {$def['category']}\n";
        echo "  Severity Override: " . ($def['severity_override'] ?: 'none') . "\n\n";

        $definedCodes[] = $def['alert_code'];
    }
}

// Layer 4: MPSM Code Descriptions (documentation)
echo "\n\nLAYER 4: DOCUMENTATION (MPSM_Code_Descriptions.md)\n";
echo str_repeat("-", 50) . "\n";

$docFile = __DIR__ . '/../docs/MPSM_Code_Descriptions.md';
if (file_exists($docFile)) {
    $content = file_get_contents($docFile);

    // Extract codes from documentation
    preg_match_all('/^\|\s*(\d+)\s*\|/m', $content, $matches);
    $docCodes = array_unique($matches[1]);

    echo "Found " . count($docCodes) . " alert codes in documentation\n";
    echo "Sample codes: " . implode(', ', array_slice($docCodes, 0, 10)) . "\n";
} else {
    echo "⚠️  Documentation file not found: {$docFile}\n";
}

// SYNCHRONIZATION ANALYSIS
echo "\n\n" . str_repeat("=", 50) . "\n";
echo "SYNCHRONIZATION ANALYSIS\n";
echo str_repeat("=", 50) . "\n\n";

// Check 1: Do rule patterns match actual alert codes?
echo "CHECK 1: Rules vs Actual Codes\n";
echo str_repeat("-", 50) . "\n";

foreach ($rulePatterns as $pattern) {
    $matchCount = 0;
    $examples = [];

    foreach ($actualCodes as $code) {
        // Simple pattern matching (exact or % wildcard)
        if ($pattern === $code['code']) {
            $matchCount++;
            $examples[] = $code['code'];
        } elseif (strpos($pattern, '%') !== false) {
            $regexPattern = '/^' . str_replace('%', '.*', preg_quote($pattern, '/')) . '$/i';
            if (preg_match($regexPattern, $code['code'])) {
                $matchCount++;
                if (count($examples) < 3) {
                    $examples[] = $code['code'];
                }
            }
        }
    }

    if ($matchCount > 0) {
        echo "✓ Pattern '{$pattern}': {$matchCount} matches\n";
        if (!empty($examples)) {
            echo "  Examples: " . implode(', ', $examples) . "\n";
        }
    } else {
        echo "✗ Pattern '{$pattern}': NO MATCHES\n";
    }
}

// Check 2: Are actual codes defined in alert_definitions?
echo "\n\nCHECK 2: Actual Codes vs User Definitions\n";
echo str_repeat("-", 50) . "\n";

$undefinedCodes = [];
$definedCount = 0;

foreach ($actualCodes as $code) {
    if (in_array($code['code'], $definedCodes)) {
        $definedCount++;
    } else {
        $undefinedCodes[] = $code['code'];
    }
}

echo "Defined: {$definedCount} / " . count($actualCodes) . " top codes\n";

if (!empty($undefinedCodes)) {
    echo "\n⚠️  UNDEFINED CODES (no user-defined name):\n";
    foreach (array_slice($undefinedCodes, 0, 10) as $code) {
        echo "  - {$code}\n";
    }
    if (count($undefinedCodes) > 10) {
        echo "  ... and " . (count($undefinedCodes) - 10) . " more\n";
    }
}

// Check 3: Notifications display
echo "\n\nCHECK 3: Notifications Display\n";
echo str_repeat("-", 50) . "\n";

$stmt = $pdo->query("
    SELECT n.id, n.alert_code, n.device_serial, n.title, n.status,
           d.display_name as user_name
    FROM " . DB_PREFIX . "dashboard_notifications n
    LEFT JOIN " . DB_PREFIX . "alert_definitions d ON n.alert_code = d.alert_code
    WHERE n.status = 'active'
    LIMIT 5
");

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($notifications)) {
    echo "⚠️  No active notifications to check\n";
} else {
    foreach ($notifications as $notif) {
        echo "Notification {$notif['id']}:\n";
        echo "  Alert Code: {$notif['alert_code']}\n";
        echo "  Title: {$notif['title']}\n";
        echo "  User-Defined Name: " . ($notif['user_name'] ?: '(none)') . "\n";
        echo "  Status: Will display as '{$notif['title']}' " .
             ($notif['user_name'] ? "with alias '{$notif['user_name']}'" : "without alias") . "\n\n";
    }
}

// RECOMMENDATIONS
echo "\n\n" . str_repeat("=", 50) . "\n";
echo "RECOMMENDATIONS\n";
echo str_repeat("=", 50) . "\n\n";

$issues = [];

// Issue 1: Rules don't match actual codes
$unmatchedRules = 0;
foreach ($rulePatterns as $pattern) {
    $hasMatch = false;
    foreach ($actualCodes as $code) {
        if ($pattern === $code['code'] ||
            (strpos($pattern, '%') !== false &&
             preg_match('/^' . str_replace('%', '.*', preg_quote($pattern, '/')) . '$/i', $code['code']))) {
            $hasMatch = true;
            break;
        }
    }
    if (!$hasMatch) {
        $unmatchedRules++;
    }
}

if ($unmatchedRules > 0) {
    $issues[] = "CRITICAL: {$unmatchedRules} rule pattern(s) don't match any actual alert codes";
}

// Issue 2: Codes without definitions
if (!empty($undefinedCodes)) {
    $issues[] = "WARNING: " . count($undefinedCodes) . " alert codes have no user-defined names";
}

// Issue 3: Empty definitions table
if (empty($definitions)) {
    $issues[] = "CRITICAL: alert_definitions table is empty - no user-defined names configured";
}

if (empty($issues)) {
    echo "✅ SYSTEM IN SYNC\n\n";
    echo "All layers are synchronized:\n";
    echo "  - Rules match actual codes\n";
    echo "  - Codes have user-defined names\n";
    echo "  - Notifications display correctly\n";
} else {
    echo "⚠️  SYNCHRONIZATION ISSUES FOUND\n\n";
    foreach ($issues as $i => $issue) {
        echo ($i + 1) . ". {$issue}\n";
    }

    echo "\n\nRECOMMENDED ACTIONS:\n\n";

    if ($unmatchedRules > 0) {
        echo "1. Update rule patterns to match actual numeric codes:\n";
        echo "   - Review LAYER 2 output for actual codes\n";
        echo "   - Update rules via Command Center or SQL\n\n";
    }

    if (empty($definitions)) {
        echo "2. Populate alert_definitions table:\n";
        echo "   - Use Command Center → Alert Labels tab\n";
        echo "   - Or import from MPSM_Code_Descriptions.md\n";
        echo "   - Maps codes like '808' to names like 'Paper Jam'\n\n";
    }

    if (!empty($undefinedCodes)) {
        echo "3. Add missing alert definitions for codes:\n";
        echo "   " . implode(', ', array_slice($undefinedCodes, 0, 5)) . "\n";
        echo "   Via Command Center → Alert Labels\n\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "END OF SYNCHRONIZATION CHECK\n";
echo str_repeat("=", 50) . "\n";
