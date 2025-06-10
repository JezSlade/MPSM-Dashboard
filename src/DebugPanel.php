<?php
// src/DebugPanel.php
// -------------------------------------
// Collects PHP debug messages and renders
// a fixed “Debug Panel” at the bottom of each page.
// -------------------------------------

class DebugPanel
{
    /** @var string[] $logs */
    private static array $logs = [];

    /**
     * Add a message to the debug log.
     *
     * @param string $msg
     */
    public static function log(string $msg): void
    {
        if (!DEBUG_MODE) {
            return;
        }
        $ts = date('Y-m-d H:i:s');
        self::$logs[] = "[$ts] $msg";
    }

    /**
     * Output the debug panel HTML.
     */
    public static function output(): void
    {
        if (!DEBUG_MODE) {
            return;
        }
        echo '<div id="debug-panel">';
        echo '<strong>Debug Panel</strong><br>';
        foreach (self::$logs as $line) {
            echo '<div class="debug-log-line">'
               . htmlspecialchars($line, ENT_QUOTES, 'UTF-8')
               . '</div>';
        }
        echo '</div>';
    }
}
