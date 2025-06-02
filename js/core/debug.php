<?php
// core/debug.php
// v1.1.0 [Unified debug + DB logging + pruning]

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/db.php';

if (!defined('DEBUG_LOG_FILE')) {
    define('DEBUG_LOG_FILE', __DIR__ . '/../logs/debug.log');
}

function debug_log(string $message, array $context = [], string $level = 'INFO'): void {
    // File log
    if (DEBUG === 'true') {
        $time  = date('Y-m-d H:i:s');
        $entry = "[$time][$level] $message"
               . (!empty($context) ? ' ' . json_encode($context) : '')
               . PHP_EOL;
        file_put_contents(DEBUG_LOG_FILE, $entry, FILE_APPEND);
    }

    // DB log
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare("INSERT INTO debug_logs (level, message, context) VALUES (?, ?, ?)");
        $stmt->execute([$level, $message, json_encode($context)]);

        // Prune
        $limit = (int)get_setting('debug_log_retention_limit', 1000);
        if ($limit > 0) {
            $idStmt = $pdo->prepare("SELECT id FROM debug_logs ORDER BY id DESC LIMIT ?");
            $idStmt->bindValue(1, $limit, PDO::PARAM_INT);
            $idStmt->execute();
            $ids = $idStmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($ids)) {
                $minId = min($ids);
                $pdo->prepare("DELETE FROM debug_logs WHERE id < ?")
                    ->execute([$minId]);
            }
        }
    } catch (Exception $e) {
        file_put_contents(DEBUG_LOG_FILE, "[DBG-ERR] {$e->getMessage()}\n", FILE_APPEND);
    }
}
