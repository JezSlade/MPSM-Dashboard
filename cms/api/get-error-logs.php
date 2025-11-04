<?php
/**
 * Get Error Logs API
 * Returns PHP error log entries with filtering and pagination
 * Admin only
 */

require '../config.php';
require '../functions.php';

requireAuth();

// Get parameters
$lines = isset($_GET['lines']) ? intval($_GET['lines']) : 100;
$filter = $_GET['filter'] ?? '';
$level = $_GET['level'] ?? ''; // error, warning, notice, search, etc.

$lines = max(10, min($lines, 1000)); // Between 10 and 1000 lines

try {
    // Find PHP error log location
    $errorLogPath = ini_get('error_log');

    // If not set, try common locations
    if (empty($errorLogPath) || !file_exists($errorLogPath)) {
        $possiblePaths = [
            '/var/log/php_errors.log',
            '/var/log/php/error.log',
            '/var/log/apache2/error.log',
            '/usr/local/var/log/php_errors.log',
            __DIR__ . '/../logs/error.log',
            __DIR__ . '/../../logs/error.log'
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_readable($path)) {
                $errorLogPath = $path;
                break;
            }
        }
    }

    if (empty($errorLogPath) || !file_exists($errorLogPath)) {
        jsonSuccess([
            'logs' => [],
            'total' => 0,
            'message' => 'Error log file not found',
            'php_error_log_setting' => ini_get('error_log'),
            'searched_paths' => $possiblePaths ?? []
        ]);
        exit;
    }

    if (!is_readable($errorLogPath)) {
        throw new Exception("Error log file exists but is not readable: $errorLogPath");
    }

    // Read the last N lines from the log file
    $logLines = [];
    $file = new SplFileObject($errorLogPath, 'r');
    $file->seek(PHP_INT_MAX);
    $totalLines = $file->key();

    // Calculate starting line
    $startLine = max(0, $totalLines - $lines);

    // Read lines
    $file->seek($startLine);
    while (!$file->eof()) {
        $line = $file->current();
        $file->next();

        if (!empty(trim($line))) {
            $logLines[] = trim($line);
        }
    }

    // Reverse to show newest first
    $logLines = array_reverse($logLines);

    // Filter if requested
    if (!empty($filter)) {
        $logLines = array_filter($logLines, function($line) use ($filter) {
            return stripos($line, $filter) !== false;
        });
        $logLines = array_values($logLines); // Re-index
    }

    // Filter by level if requested
    if (!empty($level)) {
        $logLines = array_filter($logLines, function($line) use ($level) {
            switch (strtolower($level)) {
                case 'error':
                    return stripos($line, 'error') !== false || stripos($line, 'fatal') !== false;
                case 'warning':
                    return stripos($line, 'warning') !== false;
                case 'notice':
                    return stripos($line, 'notice') !== false;
                case 'search':
                    return stripos($line, 'Search') !== false;
                case 'api':
                    return stripos($line, 'API') !== false;
                default:
                    return true;
            }
        });
        $logLines = array_values($logLines); // Re-index
    }

    // Parse log entries into structured format
    $parsedLogs = [];
    foreach ($logLines as $line) {
        $entry = [
            'raw' => $line,
            'timestamp' => null,
            'level' => 'info',
            'message' => $line
        ];

        // Try to parse timestamp and level
        // Format: [DD-MMM-YYYY HH:MM:SS TIMEZONE] PHP Warning: ...
        if (preg_match('/^\[(.*?)\]\s+(PHP\s+)?(Error|Warning|Notice|Fatal error):?\s*(.*)/i', $line, $matches)) {
            $entry['timestamp'] = $matches[1];
            $entry['level'] = strtolower(trim($matches[3]));
            $entry['message'] = trim($matches[4]);
        } elseif (preg_match('/^\[(.*?)\]\s+(.*)/i', $line, $matches)) {
            $entry['timestamp'] = $matches[1];
            $entry['message'] = trim($matches[2]);

            // Detect level from message content
            if (stripos($entry['message'], 'error') !== false || stripos($entry['message'], 'failed') !== false) {
                $entry['level'] = 'error';
            } elseif (stripos($entry['message'], 'warning') !== false) {
                $entry['level'] = 'warning';
            } elseif (stripos($entry['message'], 'successful') !== false) {
                $entry['level'] = 'success';
            }
        }

        $parsedLogs[] = $entry;
    }

    jsonSuccess([
        'logs' => $parsedLogs,
        'total' => count($parsedLogs),
        'totalLines' => $totalLines,
        'logFilePath' => $errorLogPath,
        'fileSize' => filesize($errorLogPath),
        'lastModified' => filemtime($errorLogPath)
    ]);

} catch (Exception $e) {
    jsonError("Failed to read error logs: " . $e->getMessage());
}
