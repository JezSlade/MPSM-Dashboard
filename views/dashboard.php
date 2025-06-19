<?php declare(strict_types=1);
// /views/dashboard.php

// — DEBUG BLOCK —
error_reporting(E_ALL);
ini_set('display_errors','1');
ini_set('log_errors','1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');

// 0) Helpers + config
require_once __DIR__ . '/../includes/api_functions.php';
$config = parse_env_file(__DIR__ . '/../.env');

// 1) Customer context
$customerCode = $_GET['customer'] ?? $_COOKIE['customer'] ?? $config['DEALER_CODE'] ?? '';
if (isset($_GET['customer'])) {
    setcookie('customer', $customerCode, time() + 31536000, '/');
}
$customerName = $customerCode ?: 'All Customers';
try {
    $resp = call_api($config, 'POST', 'Customer/GetCustomers', [
        'DealerCode' => $config['DEALER_CODE'] ?? '',
        'PageNumber' => 1,
        'PageRows'   => 2147483647,
        'SortColumn' => 'Description',
        'SortOrder'  => 'Asc',
    ]);
    foreach ($resp['Result'] ?? [] as $c) {
        if (($c['Code'] ?? '') === $customerCode) {
            $customerName = $c['Description'] ?? $c['Name'] ?? $customerCode;
            break;
        }
    }
} catch (\Throwable $e) {
    // Silently ignore lookup errors
}

// 2) Cards visibility (only card_*.php)
$cardsDir = __DIR__ . '/../cards/';
$allFiles = scandir($cardsDir);
$allCards = array_filter($allFiles, fn($f) =>
    pathinfo($f, PATHINFO_EXTENSION) === 'php'
    && str_starts_with($f, 'card_')
);

if (isset($_COOKIE['visible_cards'])) {
    $selection    = array_filter(explode(',', $_COOKIE['visible_cards']), 'strlen');
    $visibleCards = array_values(array_intersect($selection, $allCards));
} else {
    $visibleCards = $allCards;
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <title>Dashboard – <?= htmlspecialchars($customerName) ?></title>
    <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body class="flex flex-col h-full bg-gray-900 text-gray-100">

    <?php include __DIR__ . '/../components/preferences-modal.php'; ?>

    <header class="dashboard-header flex items-center justify-between px-6 py-3 bg-gray-800 bg-opacity-50 backdrop-blur-sm">
        <h1 class="text-xl font-semibold"><?= htmlspecialchars($customerName) ?></h1>
        <button
            class="gear-icon p-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-400"
            onclick="togglePreferencesModal(true)"
            title="Preferences"
        >
            <!-- Gear icon SVG… -->
        </button>
    </header>

    <main class="flex-1 overflow-auto p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($visibleCards as $card): ?>
                <?php include $cardsDir . $card; ?>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
    function togglePreferencesModal(show) {
        document.getElementById('preferences-modal').classList.toggle('hidden', !show);
    }
    </script>
</body>
</html>
