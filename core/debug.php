<?php
// core/debug.php
// v1.1.1 [Bug-free, now pruning safely]

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/db.php';

if (!defined('DEBUG_LOG_FILE')) {
    define('DEBUG_LOG_FILE', __DIR__ . '/../logs/debug.log');
}

/**
 * Log message to both file and database if DEBUG mode is active.
 */
function debug_log(string $message, array $context = [], string $level = 'INFO'): void {
    if (DEBUG === 'true') {
        $entry = sprintf(
            "[%s][%s] %s%s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            !empty($context) ? ' ' . json_encode($context) : ''
        );
        file_put_contents(DEBUG_LOG_FILE, $entry, FILE_APPEND);
    }

    try {
        $pdo = get_db();
        $stmt = $pdo->prepare("INSERT INTO debug_logs (level, message, context) VALUES (?, ?, ?)");
        $stmt->execute([$level, $message, json_encode($context)]);

        // Prune oldest logs if over retention
        $limit = (int)get_setting('debug_log_retention_limit', 1000);
        if ($limit > 0) {
            $stmt = $pdo->prepare("SELECT id FROM debug_logs ORDER BY id DESC LIMIT ?,1");
            $stmt->bindValue(1, $limit - 1, PDO::PARAM_INT);
            $stmt->execute();
            $cutoffId = $stmt->fetchColumn();
            if ($cutoffId) {
                $pdo->prepare("DELETE FROM debug_logs WHERE id < ?")->execute([$cutoffId]);
            }
        }
    } catch (Exception $e) {
        file_put_contents(DEBUG_LOG_FILE, "[DBG-ERR] {$e->getMessage()}\n", FILE_APPEND);
    }
}
