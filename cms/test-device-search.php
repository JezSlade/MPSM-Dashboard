<?php
/**
 * Test device search functionality
 * Verifies both cache and API search work correctly
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== DEVICE SEARCH FUNCTIONALITY TEST ===\n\n";

$pdo = getDatabase();

// Test queries
$testQueries = [
    'A61E',      // Partial serial number
    '808',       // Alert code (should not match devices)
    '3118',      // Serial prefix
    'CAPE',      // Customer name
    'W9OPXL0YDK' // Customer code
];

echo "STEP 1: Check Cache Table Exists\n";
echo str_repeat("-", 70) . "\n";

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM " . DB_PREFIX . "cache_devices");
    $cacheCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "✓ Cache table exists: {$cacheCount} devices\n\n";

    if ($cacheCount === 0) {
        echo "⚠️  WARNING: Cache is empty! Search will fall back to API\n";
        echo "   Run cache population to improve search speed\n\n";
    }

} catch (PDOException $e) {
    echo "❌ Cache table missing or inaccessible\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
    echo "   Search will use API only (slower)\n\n";
}

echo "\nSTEP 2: Test Cache Search Function\n";
echo str_repeat("-", 70) . "\n\n";

foreach ($testQueries as $query) {
    echo "Query: '{$query}'\n";

    $like = '%' . $query . '%';
    $sql = "
        SELECT
            device_data,
            serial_number,
            customer_code
        FROM " . DB_PREFIX . "cache_devices
        WHERE serial_number LIKE :like
           OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.EquipmentId')) LIKE :like
           OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.EquipmentID')) LIKE :like
           OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Product.Model')) LIKE :like
        LIMIT 5
    ";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':like', $like, PDO::PARAM_STR);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($results)) {
            echo "  → No matches in cache\n\n";
        } else {
            echo "  → Found " . count($results) . " matches:\n";
            foreach ($results as $row) {
                $data = json_decode($row['device_data'], true);
                $equipId = $data['EquipmentId'] ?? $data['EquipmentID'] ?? 'N/A';
                $model = $data['Product']['Model'] ?? $data['ProductModel'] ?? 'Unknown';
                echo "    - {$row['serial_number']} | {$equipId} | {$model}\n";
            }
            echo "\n";
        }

    } catch (PDOException $e) {
        echo "  ❌ Query failed: " . $e->getMessage() . "\n\n";
    }
}

echo "\nSTEP 3: Test API Endpoint\n";
echo str_repeat("-", 70) . "\n\n";

// Simulate the API call
foreach (array_slice($testQueries, 0, 3) as $query) {
    echo "Testing: api/search-devices.php?query={$query}\n";

    $url = "https://mpsm.resolutionsbydesign.us/cms/api/search-devices.php?query=" . urlencode($query);

    // We can't actually test authenticated endpoint without session
    // But we can verify the file exists and check the logic

    echo "  Endpoint file exists: ";
    if (file_exists(__DIR__ . '/api/search-devices.php')) {
        echo "✓ Yes\n";
    } else {
        echo "❌ No\n";
    }
    echo "\n";
}

echo "\nSTEP 4: Check Search Performance\n";
echo str_repeat("-", 70) . "\n\n";

$perfQuery = '3118';
echo "Performance test with query: '{$perfQuery}'\n\n";

$like = '%' . $perfQuery . '%';
$sql = "
    SELECT COUNT(*) as count
    FROM " . DB_PREFIX . "cache_devices
    WHERE serial_number LIKE :like
       OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.EquipmentId')) LIKE :like
       OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.EquipmentID')) LIKE :like
       OR JSON_UNQUOTE(JSON_EXTRACT(device_data, '$.Product.Model')) LIKE :like
";

$start = microtime(true);
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':like', $like, PDO::PARAM_STR);
$stmt->execute();
$matchCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
$duration = round((microtime(true) - $start) * 1000, 2);

echo "Matches: {$matchCount}\n";
echo "Time: {$duration}ms\n\n";

if ($duration < 100) {
    echo "✓ Excellent performance (<100ms)\n";
} elseif ($duration < 500) {
    echo "✓ Good performance (<500ms)\n";
} elseif ($duration < 1000) {
    echo "⚠️  Acceptable performance (<1s)\n";
} else {
    echo "❌ Slow performance (>1s) - Consider adding indexes\n";
}

echo "\n\nSTEP 5: Verify JavaScript Integration\n";
echo str_repeat("-", 70) . "\n\n";

// Check that search inputs exist in HTML
$desktopIndexExists = file_exists(__DIR__ . '/index.php');
$mobileExists = file_exists(__DIR__ . '/mobile.php');

echo "Desktop (index.php):\n";
if ($desktopIndexExists) {
    $content = file_get_contents(__DIR__ . '/index.php');
    if (strpos($content, 'global-device-search') !== false) {
        echo "  ✓ Search input found: id='global-device-search'\n";
    } else {
        echo "  ❌ Search input NOT found\n";
    }

    if (strpos($content, 'global-search-results') !== false) {
        echo "  ✓ Results container found: id='global-search-results'\n";
    } else {
        echo "  ❌ Results container NOT found\n";
    }
} else {
    echo "  ❌ index.php not found\n";
}

echo "\nMobile (mobile.php):\n";
if ($mobileExists) {
    $content = file_get_contents(__DIR__ . '/mobile.php');
    if (strpos($content, 'mobile-search') !== false) {
        echo "  ✓ Search input found: id='mobile-search'\n";
    } else {
        echo "  ❌ Search input NOT found\n";
    }

    if (strpos($content, 'mobile-search-results') !== false) {
        echo "  ✓ Results container found: id='mobile-search-results'\n";
    } else {
        echo "  ❌ Results container NOT found\n";
    }
} else {
    echo "  ❌ mobile.php not found\n";
}

// Check JavaScript files
echo "\nJavaScript Files:\n";
$appJsExists = file_exists(__DIR__ . '/assets/app.js');
$mobileJsExists = file_exists(__DIR__ . '/assets/mobile.js');

if ($appJsExists) {
    $content = file_get_contents(__DIR__ . '/assets/app.js');
    if (strpos($content, 'global-device-search') !== false) {
        echo "  ✓ Desktop search handler found in app.js\n";
    } else {
        echo "  ❌ Desktop search handler NOT found in app.js\n";
    }
}

if ($mobileJsExists) {
    $content = file_get_contents(__DIR__ . '/assets/mobile.js');
    if (strpos($content, 'mobile-search') !== false) {
        echo "  ✓ Mobile search handler found in mobile.js\n";
    } else {
        echo "  ❌ Mobile search handler NOT found in mobile.js\n";
    }
}

echo "\n\n" . str_repeat("=", 70) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 70) . "\n\n";

echo "Components Status:\n";
echo "  Cache table: " . ($cacheCount > 0 ? "✓ {$cacheCount} devices" : "❌ Empty") . "\n";
echo "  API endpoint: " . (file_exists(__DIR__ . '/api/search-devices.php') ? "✓ Exists" : "❌ Missing") . "\n";
echo "  Desktop HTML: " . ($desktopIndexExists ? "✓ Exists" : "❌ Missing") . "\n";
echo "  Mobile HTML: " . ($mobileExists ? "✓ Exists" : "❌ Missing") . "\n";
echo "  Desktop JS: " . ($appJsExists ? "✓ Exists" : "❌ Missing") . "\n";
echo "  Mobile JS: " . ($mobileJsExists ? "✓ Exists" : "❌ Missing") . "\n\n";

echo "To test search manually:\n";
echo "1. Desktop: https://mpsm.resolutionsbydesign.us/cms/index.php\n";
echo "   - Type in the search box at the top of the page\n";
echo "   - Should show dropdown with results after 2+ characters\n\n";

echo "2. Mobile: https://mpsm.resolutionsbydesign.us/cms/mobile.php\n";
echo "   - Type in the search box\n";
echo "   - Should show results below the search box\n\n";

echo "3. API Direct: https://mpsm.resolutionsbydesign.us/cms/api/search-devices.php?query=3118\n";
echo "   - Should return JSON with matching devices\n";
echo "   - Requires authentication (session)\n";
