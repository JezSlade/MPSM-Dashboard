<?php
// src/DebugPanel.php
// -------------------------------------
// Collects debug messages (PHP) and renders
// a fixed “Debug Panel” at the bottom of the page.
// -------------------------------------

class DebugPanel
{
    /** @var string[] $logs Accumulated debug lines */
    private static array $logs = [];

    /**
     * Add a message to the debug log
     *
     * @param string $msg
     */
    public static function log(string $msg): void
    {
        if (!DEBUG_MODE) {
            return;
        }
        $timestamp = date('Y-m-d H:i:s');
        self::$logs[] = "[$timestamp] $msg";
    }

    /**
     * Output the debug panel HTML (to be called in body)
     */
    public static function output(): void
    {
        if (!DEBUG_MODE) {
            return;
        }
        echo '<div id="debug-panel">';
        echo '<strong>Debug Panel</strong><br>';
        foreach (self::$logs as $line) {
            // escape to avoid XSS
            echo htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '<br>';
        }
        echo '</div>';
    }
}
