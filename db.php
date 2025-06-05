<?php
// db.php
function load_env($path) {
    $env = [];
    $env_file = $path . '.env';
    if (file_exists($env_file)) {
        $lines = explode("\n", file_get_contents($env_file));
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line && strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                [$key, $value] = explode('=', $line, 2);
                $env[$key] = $value;
            }
        }
    }
    return $env;
}

function connect_db() {
    $env = load_env(__DIR__);
    $host = $env['DB_HOST'] ?? 'localhost';
    $user = $env['DB_USER'] ?? '';
    $pass = $env['DB_PASS'] ?? '';
    $db = $env['DB_NAME'] ?? '';
    return new mysqli($host, $user, $pass, $db);
}