<?php
class EnvLoader
{
    private const ENV_PATH = __DIR__ . '/../.env';

    public static function load(): array
    {
        $env = [];

        if (!file_exists(self::ENV_PATH)) {
            throw new RuntimeException(".env file not found at expected path: " . self::ENV_PATH);
        }

        $lines = file(self::ENV_PATH, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            if (preg_match('/^\s*([\w\.]+)\s*=\s*(.*)?\s*$/', $line, $matches)) {
                $key = $matches[1];
                $value = $matches[2];
                $value = trim($value, "'\"");
                $env[$key] = $value;
            }
        }

        return $env;
    }
}
