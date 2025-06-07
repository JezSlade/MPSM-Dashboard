<?php
/**
 * Load and parse a .env file.
 * Throws on failure.
 */
function loadEnv(string $file): array {
    $env = parse_ini_file($file, false, INI_SCANNER_RAW);
    if ($env === false) {
        throw new Exception("Unable to load .env at {$file}");
    }
    return $env;
}

/** HTML‐escape helper */
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
