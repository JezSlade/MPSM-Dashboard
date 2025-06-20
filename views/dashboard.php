<?php declare(strict_types=1);
// /views/dashboard.php — dashboard partial

require_once __DIR__ . '/../includes/debug.php';
require_once __DIR__ . '/../includes/api_functions.php';

// Establish customerCode (URL → cookie → .env default)
$customerCode = $_GET['customer']
             ?? $_COOKIE['customer']
             ?? getenv('DEALER_CODE')
             ?? '';

error_log("Dashboard view for customer: {$customerCode}");

echo '<main class="dashboard-view">';
echo '<h1>Dashboard — Customer: ' . htmlspecialchars($customerCode, ENT_QUOTES) . '</h1>';
echo '<div class="cards-grid">';

// Individual cards
include __DIR__ . '/../cards/card_devices.php';
include __DIR__ . '/../cards/card_device_counters.php';
// …add/remove cards as needed…

echo '</div>';
echo '</main>';
