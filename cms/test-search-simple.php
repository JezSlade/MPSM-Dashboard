<?php
/**
 * Simple device search test
 */

require __DIR__ . '/config.php';
require __DIR__ . '/functions.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== DEVICE SEARCH TEST ===\n\n";

$pdo = getDatabase();

// Check cache
echo "STEP 1: Cache Status\n";
echo str_repeat("-", 70) . "\n";

$stmt = $pdo->query("SELECT COUNT(*) as count FROM " . DB_PREFIX . "cache_devices");
$cacheCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

echo "Cache devices: {$cacheCount}\n";

if ($cacheCount === 0) {
    echo "❌ CRITICAL: Cache is empty!\n";
    echo "   Search will be slow (falls back to API)\n";
    echo "   Recommendation: Run cache population script\n\n";
} else {
    echo "✓ Cache populated\n\n";
}

// Check API endpoint exists
echo "\nSTEP 2: API Endpoint\n";
echo str_repeat("-", 70) . "\n";

$apiPath = __DIR__ . '/api/search-devices.php';
if (file_exists($apiPath)) {
    echo "✓ search-devices.php exists\n";

    // Check if it's readable
    $content = file_get_contents($apiPath);
    if (strpos($content, 'searchCache') !== false && strpos($content, 'searchUpstream') !== false) {
        echo "✓ Contains both cache and API search functions\n";
    } else {
        echo "⚠️  Missing expected functions\n";
    }
} else {
    echo "❌ search-devices.php NOT FOUND\n";
}

// Check desktop integration
echo "\n\nSTEP 3: Desktop Integration (index.php)\n";
echo str_repeat("-", 70) . "\n";

if (file_exists(__DIR__ . '/index.php')) {
    $html = file_get_contents(__DIR__ . '/index.php');

    $checks = [
        'global-device-search' => 'Search input element',
        'global-search-results' => 'Results dropdown',
        'assets/app.js' => 'JavaScript file reference'
    ];

    foreach ($checks as $needle => $description) {
        if (strpos($html, $needle) !== false) {
            echo "✓ {$description}: Found\n";
        } else {
            echo "❌ {$description}: NOT FOUND\n";
        }
    }
} else {
    echo "❌ index.php not found\n";
}

// Check mobile integration
echo "\n\nSTEP 4: Mobile Integration (mobile.php)\n";
echo str_repeat("-", 70) . "\n";

if (file_exists(__DIR__ . '/mobile.php')) {
    $html = file_get_contents(__DIR__ . '/mobile.php');

    $checks = [
        'mobile-search' => 'Search input element',
        'mobile-search-results' => 'Results container',
        'assets/mobile.js' => 'JavaScript file reference'
    ];

    foreach ($checks as $needle => $description) {
        if (strpos($html, $needle) !== false) {
            echo "✓ {$description}: Found\n";
        } else {
            echo "❌ {$description}: NOT FOUND\n";
        }
    }
} else {
    echo "❌ mobile.php not found\n";
}

// Check JavaScript files
echo "\n\nSTEP 5: JavaScript Files\n";
echo str_repeat("-", 70) . "\n";

$jsChecks = [
    'assets/app.js' => [
        'global-device-search',
        'search-devices.php',
        'global-search-results'
    ],
    'assets/mobile.js' => [
        'mobile-search',
        'search-devices.php',
        'mobile-search-results'
    ]
];

foreach ($jsChecks as $file => $needles) {
    $path = __DIR__ . '/' . $file;
    echo "\n{$file}:\n";

    if (!file_exists($path)) {
        echo "  ❌ File not found\n";
        continue;
    }

    $content = file_get_contents($path);

    foreach ($needles as $needle) {
        if (strpos($content, $needle) !== false) {
            echo "  ✓ Contains '{$needle}'\n";
        } else {
            echo "  ❌ Missing '{$needle}'\n";
        }
    }
}

// Summary
echo "\n\n" . str_repeat("=", 70) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 70) . "\n\n";

$allGood = true;

if ($cacheCount === 0) {
    echo "⚠️  Cache is empty - search will be slow\n";
    $allGood = false;
}

if (!file_exists(__DIR__ . '/api/search-devices.php')) {
    echo "❌ API endpoint missing\n";
    $allGood = false;
}

if (!file_exists(__DIR__ . '/assets/app.js')) {
    echo "❌ Desktop JavaScript missing\n";
    $allGood = false;
}

if (!file_exists(__DIR__ . '/assets/mobile.js')) {
    echo "❌ Mobile JavaScript missing\n";
    $allGood = false;
}

if ($allGood && $cacheCount > 0) {
    echo "✓ All components present and search should work\n\n";
} elseif ($allGood) {
    echo "✓ All components present (search will use API fallback)\n\n";
} else {
    echo "❌ Some components missing - search may not work\n\n";
}

echo "Manual Test Instructions:\n";
echo "1. Visit: https://mpsm.resolutionsbydesign.us/cms/index.php\n";
echo "2. Type into the search box at the top (min 2 characters)\n";
echo "3. You should see a dropdown with matching devices\n";
echo "4. If it's slow, the cache needs to be populated\n\n";

echo "Test on mobile:\n";
echo "1. Visit: https://mpsm.resolutionsbydesign.us/cms/mobile.php\n";
echo "2. Use the search box in the Device Lookup section\n";
echo "3. Results should appear below the search box\n";
