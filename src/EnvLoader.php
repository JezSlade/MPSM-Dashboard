<?php
// src/EnvLoader.php

class EnvLoader {
    public static function load(string $path) {
        if (!file_exists($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$name, $val] = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($val);
        }
    }
}
