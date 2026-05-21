<?php
require dirname(__DIR__, 2) . '/cms/config.php';
require dirname(__DIR__, 2) . '/cms/functions.php';
$pdo = getDatabase();
$cache = DB_PREFIX . 'cache_devices';
$drill = DB_PREFIX . 'cache_device_drilldown';
$cacheCount = $pdo->query("SELECT COUNT(*) FROM {$cache}")->fetchColumn();
$drillCount = $pdo->query("SELECT COUNT(*) FROM {$drill}")->fetchColumn();
echo "cache_devices={$cacheCount}, drilldown={$drillCount}\n";
