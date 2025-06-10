<?php

namespace App\Helpers;

class DebugPanel
{
    private static array $logs = [];

    public static function log(string $msg): void
    {
        if (!env('DEBUG', true)) {
            return;
        }
        $ts = date('Y-m-d H:i:s');
        self::$logs[] = "[$ts] $msg";
    }

    public static function output(): string
    {
        if (!env('DEBUG', true)) {
            return '';
        }
        $html = '<div id="php-debug-panel">';
        $html .= '<strong>PHP Debug</strong><br>';
        foreach (self::$logs as $line) {
            $html .= htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '<br>';
        }
        $html .= '</div>';
        return $html;
    }
}
