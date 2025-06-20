<?php declare(strict_types=1);
// /views/dashboard.php — dashboard partial
// (header.php and footer.php own the <html> wrapper)

error_log("▶ Rendering dashboard view for customer: " . ($customerCode ?? 'n/a'));

echo '<main class="dashboard-view">';
echo '<h1>Dashboard — Customer: ' . htmlspecialchars($customerCode ?? '', ENT_QUOTES) . '</h1>';
echo '<div class="cards-grid">';

include __DIR__ . '/../cards/card_devices.php';
include __DIR__ . '/../cards/card_device_counters.php';
// …add other cards here…

echo '</div>';
echo '</main>';
