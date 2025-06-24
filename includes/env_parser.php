<?php
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    throw new RuntimeException('.env file not found');
}
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, '=') !== false) {
        list($key, $val) = explode('=', $line, 2);
        if (!defined($key=trim($key))) define($key, trim($val));
    }
}
$required = ['CLIENT_ID','CLIENT_SECRET','USERNAME','PASSWORD','SCOPE','TOKEN_URL','API_BASE_URL','DEALER_CODE'];
foreach ($required as $c) if (!defined($c)) throw new RuntimeException("Missing .env key: $c");
