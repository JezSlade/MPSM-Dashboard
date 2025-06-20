<?php declare(strict_types=1);
// /views/dashboard.php — dashboard partial

// PHP-side log
error_log("▶ Rendering dashboard for customer: " . ($customerCode ?? 'n/a'));

// tell the JS console we’re in the view
echo "<script>appendDebug('▶ Dashboard view start (customer: ' + " .
     json_encode($customerCode ?? '') .
     " + ')');</script>";

echo '<main class="dashboard-view">';
echo '<h1>Dashboard — Customer: ' . htmlspecialchars($customerCode ?? '', ENT_QUOTES) . '</h1>';
echo '<div class="cards-grid">';

// include your cards
include __DIR__ . '/../cards/card_devices.php';
include __DIR__ . '/../cards/card_device_counters.php';
// …etc…

echo '</div>';
echo '</main>';

// signal that we’ve finished the dashboard shell
echo "<script>appendDebug('▶ Dashboard view complete');</script>";
