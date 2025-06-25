<?php declare(strict_types=1);
// /components/debug-log.php

// Only allow when logged in via dashboard (prevent direct API interference)
if (basename($_SERVER['SCRIPT_NAME']) !== 'debug-log.php') {
    header('HTTP/1.1 403 Forbidden');
    exit('Forbidden');
}

$logFile = __DIR__ . '/../logs/debug.log';
if (!is_readable($logFile)) {
    http_response_code(404);
    exit('Log not found.');
}

header('Content-Type: text/plain; charset=UTF-8');
readfile($logFile);
