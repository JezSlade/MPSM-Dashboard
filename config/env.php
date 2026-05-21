<?php
/**
 * Lightweight .env loader used by repository-managed config.
 *
 * It only fills missing process environment values, so server-provided
 * variables keep precedence over local .env files.
 */

if (!function_exists('mpsm_load_env')) {
    function mpsm_load_env(?string $path = null): void
    {
        static $loaded = [];

        $path = $path ?: dirname(__DIR__) . '/.env';
        if (isset($loaded[$path]) || !is_readable($path)) {
            return;
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return;
        }

        try {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                    continue;
                }

                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                if (!preg_match('/^[A-Z_][A-Z0-9_]*$/', $key)) {
                    continue;
                }

                if (
                    strlen($value) >= 2
                    && (($value[0] === '"' && substr($value, -1) === '"')
                        || ($value[0] === "'" && substr($value, -1) === "'"))
                ) {
                    $value = substr($value, 1, -1);
                }

                if (getenv($key) === false) {
                    putenv($key . '=' . $value);
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        } finally {
            fclose($handle);
            $loaded[$path] = true;
        }
    }
}

if (!function_exists('mpsm_env')) {
    function mpsm_env($keys, $default = null)
    {
        foreach ((array) $keys as $key) {
            $value = getenv($key);
            if ($value !== false && $value !== '') {
                return $value;
            }

            if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
                return $_ENV[$key];
            }

            if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
                return $_SERVER[$key];
            }
        }

        return $default;
    }
}
