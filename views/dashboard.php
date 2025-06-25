<?php
// Auto-include every card ending in "Card.php"
$cardsDir = __DIR__ . '/../cards/';
foreach (scandir($cardsDir, SCANDIR_SORT_ASCENDING) as $file) {
    if ($file === '.' || $file === '..') continue;
    if (preg_match('/Card\.php$/', $file)) {
        include $cardsDir . $file;
    }
}
