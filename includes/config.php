<?php
declare(strict_types=1);

// ─── DEBUG BLOCK (Always at Top) ─────────────────────────────
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/debug.log');
// ──────────────────────────────────────────────────────────────

/**
 * Parse a simple .env-style file into an associative array.
 *
 * @param string $path Full or relative path to the .env file
 * @return array<string,string> Key/value pairs
 */
if (!function_exists('parse_env_file')) {
    function parse_env_file(string $path): array
    {
        if (!is_readable($path)) {
            return [];
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $env   = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            [$key, $val] = array_map('trim', explode('=', $line, 2) + [1 => '']);
            $env[$key] = $val;
        }
        return $env;
    }
}

/**
 * Renders a PHP view file, extracting $params into its scope.
 *
 * @param string $view   Path relative to project root, e.g. 'views/dashboard.php'
 * @param array  $params Variables to make available in the view
 */
if (!function_exists('render_view')) {
    function render_view(string $view, array $params = []): void
    {
        extract($params, EXTR_SKIP);
        $file = __DIR__ . '/../' . ltrim($view, '/');
        if (!is_readable($file)) {
            throw new RuntimeException("View not found or not readable: {$file}");
        }
        include $file;
    }
}
