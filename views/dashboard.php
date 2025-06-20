<?php declare(strict_types=1);
// /views/dashboard.php — dashboard partial

require_once __DIR__ . '/../includes/debug.php';
require_once __DIR__ . '/../includes/api_functions.php';

// Determine customer code (cookie → default)
$customerCode = $_GET['customer']         ??        // URL override
                $_COOKIE['customer']      ??        // previously chosen
                getenv('DEALER_CODE')     ?? '';    // fallback from .env

error_log("Dashboard view for customer: {$customerCode}");

echo '<main class="dashboard-view">';
echo '<h1>Dashboard — Customer: ' . htmlspecialchars($customerCode, ENT_QUOTES) . '</h1>';
echo '<div class="cards-grid">';

// Include the cards you need
include __DIR__ . '/../cards/card_devices.php';
include __DIR__ . '/../cards/card_device_counters.php';
// …add or remove cards here as required…

echo '</div>';
echo '</main>';
