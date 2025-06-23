<?php
declare(strict_types=1);

/**
 * Unified Debug Helper
 * ---------------------------------------------------------------
 * • Auto-creates /logs directory if missing
 * • Sets E_ALL, displays errors, logs to /logs/debug.log
 * • To use:   require_once __DIR__ . '/debug.php';
 */

$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);     // create /logs/ recursively
}

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', $logDir . '/debug.log');
