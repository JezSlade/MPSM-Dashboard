<?php
// public/src/DebugPanel.php
// -------------------------------------
// Collects PHP debug messages and renders
// a fixed “Debug Panel” at the bottom.
// -------------------------------------

class DebugPanel
{
    private static array $logs = [];

    public static function log(string $msg): void
    {
        if (!DEBUG_MODE) return;
        $ts = date('Y-m-d H:i:s');
        self::$logs[] = "[$ts] $msg";
    }

    public static function output(): void
    {
        if (!DEBUG_MODE) return;
        echo '<div id="php-debug-panel">';
        echo '<strong>PHP Debug</strong><br>';
        foreach (self::$logs as $line) {
            echo htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '<br>';
        }
        echo '</div>';
    }
}
