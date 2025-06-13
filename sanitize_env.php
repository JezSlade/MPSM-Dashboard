<?php
// sanitize_env.php — Loads and normalizes .env values

function loadEnv(string $path): array {
    $env = [];

    if (!file_exists($path)) {
        return $env;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0 || !strpos($line, '=')) continue;
        list($key, $val) = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val);

        // Normalize common edge cases
        $val = trim($val, "\"'");         // Strip quotes
        $val = preg_replace('/\s+/', ' ', $val); // Collapse whitespace
        $val = rtrim($val, '/');             // Remove trailing slashes from URLs

        $env[$key] = $val;
    }

    return $env;
}
